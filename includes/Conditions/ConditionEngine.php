<?php
/**
 * Condition evaluation engine for checkout field visibility and requirement.
 * Evaluates rules client-side (via localized context) and server-side upon checkout submission.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Conditions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ConditionEngine
 */
final class ConditionEngine {

	/**
	 * Request-scoped cached context.
	 *
	 * @var array<string, mixed>|null
	 */
	private static ?array $cached_context = null;

	/**
	 * Build or retrieve current evaluation context from WooCommerce session/cart/user.
	 *
	 * @param array<string, mixed>|null $posted_data Optional submitted checkout data.
	 * @return array<string, mixed>
	 */
	public static function get_context( ?array $posted_data = null ): array {
		if ( null !== self::$cached_context && null === $posted_data ) {
			return self::$cached_context;
		}

		$context = [
			'cart_subtotal'     => 0.0,
			'cart_total'        => 0.0,
			'cart_item_count'   => 0,
			'product_ids'       => [],
			'category_ids'      => [],
			'is_virtual_only'   => false,
			'needs_shipping'    => false,
			'applied_coupons'   => [],
			'chosen_shipping'   => '',
			'chosen_payment'    => '',
			'billing_country'   => '',
			'shipping_country'  => '',
			'billing_state'     => '',
			'shipping_state'    => '',
			'is_user_logged_in' => is_user_logged_in(),
			'user_roles'        => [],
			'fields'            => is_array( $posted_data ) ? $posted_data : [],
		];

		// User details
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			$context['user_roles'] = (array) $user->roles;
		}

		// WooCommerce Cart details
		if ( function_exists( 'WC' ) && WC()->cart ) {
			$cart = WC()->cart;
			$context['cart_subtotal']   = (float) $cart->get_subtotal();
			$context['cart_total']      = (float) $cart->get_total( 'edit' );
			$context['cart_item_count'] = (int) $cart->get_cart_contents_count();
			$context['needs_shipping']  = (bool) $cart->needs_shipping();
			$context['is_virtual_only'] = (bool) $cart->is_empty() ? false : ! $cart->needs_shipping();
			$context['applied_coupons'] = (array) $cart->get_applied_coupons();

			// Collect products and categories without extra database queries
			$prod_ids = [];
			$cat_ids  = [];
			foreach ( $cart->get_cart() as $cart_item ) {
				$prod_ids[] = (int) $cart_item['product_id'];
				if ( ! empty( $cart_item['variation_id'] ) ) {
					$prod_ids[] = (int) $cart_item['variation_id'];
				}

				/** @var \WC_Product|null $product */
				$product = $cart_item['data'] ?? null;
				if ( $product instanceof \WC_Product ) {
					$terms = $product->get_category_ids();
					if ( ! empty( $terms ) ) {
						$cat_ids = array_merge( $cat_ids, $terms );
					}
				}
			}

			$context['product_ids']  = array_unique( $prod_ids );
			$context['category_ids'] = array_unique( $cat_ids );
		}

		// Shipping & Payment session details
		if ( function_exists( 'WC' ) && WC()->session ) {
			$chosen_methods = WC()->session->get( 'chosen_shipping_methods', [] );
			$context['chosen_shipping'] = is_array( $chosen_methods ) && ! empty( $chosen_methods[0] ) ? (string) $chosen_methods[0] : '';
			$context['chosen_payment']  = (string) WC()->session->get( 'chosen_payment_method', '' );
		}

		// Customer location details
		if ( function_exists( 'WC' ) && WC()->customer ) {
			$cust = WC()->customer;
			$context['billing_country']  = (string) $cust->get_billing_country();
			$context['shipping_country'] = (string) $cust->get_shipping_country();
			$context['billing_state']    = (string) $cust->get_billing_state();
			$context['shipping_state']   = (string) $cust->get_shipping_state();
		}

		// Merge posted fields if supplied
		if ( is_array( $posted_data ) ) {
			if ( ! empty( $posted_data['billing_country'] ) ) {
				$context['billing_country'] = (string) $posted_data['billing_country'];
			}
			if ( ! empty( $posted_data['shipping_country'] ) ) {
				$context['shipping_country'] = (string) $posted_data['shipping_country'];
			}
			if ( ! empty( $posted_data['billing_state'] ) ) {
				$context['billing_state'] = (string) $posted_data['billing_state'];
			}
			if ( ! empty( $posted_data['shipping_state'] ) ) {
				$context['shipping_state'] = (string) $posted_data['shipping_state'];
			}
			if ( ! empty( $posted_data['payment_method'] ) ) {
				$context['chosen_payment'] = (string) $posted_data['payment_method'];
			}
		}

		if ( null === $posted_data ) {
			self::$cached_context = $context;
		}

		return $context;
	}

	/**
	 * Determine if a field should be visible based on its conditional rules.
	 *
	 * @param array<string, mixed> $field Field definition.
	 * @param array<string, mixed> $context Evaluation context.
	 * @return bool True if visible, false if hidden.
	 */
	public static function is_field_visible( array $field, array $context ): bool {
		$conditions = $field['conditions'] ?? [];
		if ( empty( $conditions ) || empty( $conditions['rules'] ) ) {
			return true;
		}

		$action  = (string) ( $conditions['action'] ?? 'show' );
		$matches = self::evaluate_rules( $conditions, $context );

		if ( 'show' === $action ) {
			return $matches;
		}

		if ( 'hide' === $action ) {
			return ! $matches;
		}

		return true;
	}

	/**
	 * Determine if a field is required under current conditions.
	 *
	 * @param array<string, mixed> $field Field definition.
	 * @param array<string, mixed> $context Evaluation context.
	 * @return bool True if required, false if optional.
	 */
	public static function is_field_required( array $field, array $context ): bool {
		if ( ! self::is_field_visible( $field, $context ) ) {
			return false; // Hidden fields are never required to avoid blocking checkout.
		}

		$base_required = ! empty( $field['required'] );
		$conditions    = $field['conditions'] ?? [];

		if ( empty( $conditions ) || empty( $conditions['rules'] ) ) {
			return $base_required;
		}

		$action  = (string) ( $conditions['action'] ?? '' );
		$matches = self::evaluate_rules( $conditions, $context );

		if ( 'require' === $action ) {
			return $matches ? true : $base_required;
		}

		if ( 'optional' === $action ) {
			return $matches ? false : $base_required;
		}

		return $base_required;
	}

	/**
	 * Evaluate a collection of rules against the context (AND / OR logic).
	 *
	 * @param array<string, mixed> $conditions
	 * @param array<string, mixed> $context
	 * @return bool
	 */
	public static function evaluate_rules( array $conditions, array $context ): bool {
		$rules = $conditions['rules'] ?? [];
		if ( empty( $rules ) || ! is_array( $rules ) ) {
			return true;
		}

		$match_mode = (string) ( $conditions['match'] ?? 'all' ); // 'all' (AND) or 'any' (OR)

		foreach ( $rules as $rule ) {
			$passed = self::evaluate_single_rule( $rule, $context );

			if ( 'any' === $match_mode && $passed ) {
				return true;
			}

			if ( 'all' === $match_mode && ! $passed ) {
				return false;
			}
		}

		return 'all' === $match_mode;
	}

	/**
	 * Evaluate a single atomic rule.
	 *
	 * @param array<string, mixed> $rule
	 * @param array<string, mixed> $context
	 * @return bool
	 */
	public static function evaluate_single_rule( array $rule, array $context ): bool {
		$source   = (string) ( $rule['source'] ?? '' );
		$operator = (string) ( $rule['operator'] ?? 'equals' );
		$expected = (string) ( $rule['value'] ?? '' );

		$actual = self::get_source_value( $source, $context );

		switch ( $operator ) {
			case 'equals':
				return is_array( $actual ) ? in_array( $expected, $actual, false ) : (string) $actual === $expected;

			case 'not_equals':
				return is_array( $actual ) ? ! in_array( $expected, $actual, false ) : (string) $actual !== $expected;

			case 'contains':
				return false !== stripos( (string) $actual, $expected );

			case 'does_not_contain':
				return false === stripos( (string) $actual, $expected );

			case 'greater_than':
				return (float) $actual > (float) $expected;

			case 'greater_than_or_equal':
				return (float) $actual >= (float) $expected;

			case 'less_than':
				return (float) $actual < (float) $expected;

			case 'less_than_or_equal':
				return (float) $actual <= (float) $expected;

			case 'is_empty':
				return empty( $actual );

			case 'is_not_empty':
				return ! empty( $actual );

			case 'in_list':
				$list = array_map( 'trim', explode( ',', $expected ) );
				return in_array( (string) $actual, $list, true );

			case 'not_in_list':
				$list = array_map( 'trim', explode( ',', $expected ) );
				return ! in_array( (string) $actual, $list, true );

			default:
				return false;
		}
	}

	/**
	 * Retrieve value for a given source from context.
	 *
	 * @param string $source
	 * @param array<string, mixed> $context
	 * @return mixed
	 */
	private static function get_source_value( string $source, array $context ): mixed {
		if ( array_key_exists( $source, $context ) ) {
			return $context[ $source ];
		}

		// Check inside posted fields or custom fields
		if ( isset( $context['fields'][ $source ] ) ) {
			return $context['fields'][ $source ];
		}

		return '';
	}
}
