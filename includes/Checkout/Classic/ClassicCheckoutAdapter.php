<?php
/**
 * Classic Checkout adapter integrating with WooCommerce form filters and actions.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Checkout\Classic;

use WPCalibrate\CheckoutStudio\Settings\SettingsRepository;
use WPCalibrate\CheckoutStudio\Validation\ValidationEngine;
use WPCalibrate\CheckoutStudio\Fields\FieldTypeRegistry;
use WPCalibrate\CheckoutStudio\Checkout\AppearanceEngine;
use WPCalibrate\CheckoutStudio\Conditions\ConditionEngine;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ClassicCheckoutAdapter
 */
final class ClassicCheckoutAdapter {

	private SettingsRepository $repo;

	/**
	 * Constructor.
	 *
	 * @param SettingsRepository $repo
	 */
	public function __construct( SettingsRepository $repo ) {
		$this->repo = $repo;
	}

	/**
	 * Initialize Classic Checkout hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		// Filter WooCommerce checkout fields.
		add_filter( 'woocommerce_checkout_fields', [ $this, 'filter_checkout_fields' ], 100, 1 );

		// Custom form field renderers.
		add_filter( 'woocommerce_form_field_date', [ $this, 'render_date_field' ], 10, 4 );
		add_filter( 'woocommerce_form_field_time', [ $this, 'render_time_field' ], 10, 4 );
		add_filter( 'woocommerce_form_field_heading', [ $this, 'render_heading_element' ], 10, 4 );

		// Validate submitted fields on checkout submission.
		add_action( 'woocommerce_checkout_process', [ $this, 'validate_checkout_fields' ], 20 );

		// Save custom field metadata to WC_Order on checkout creation.
		add_action( 'woocommerce_checkout_create_order', [ $this, 'save_order_metadata' ], 20, 2 );

		// Enqueue checkout scripts and styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_checkout_assets' ] );
	}

	/**
	 * Filter WooCommerce checkout fields to inject customizations and custom fields.
	 *
	 * @param array<string, array<string, array<string, mixed>>> $wc_fields
	 * @return array<string, array<string, array<string, mixed>>>
	 */
	public function filter_checkout_fields( array $wc_fields ): array {
		$configured_fields = $this->repo->get_fields();
		$context           = ConditionEngine::get_context();

		foreach ( $configured_fields as $field ) {
			$key     = (string) ( $field['key'] ?? '' );
			$section = (string) ( $field['section'] ?? 'billing' );
			$is_core = ! empty( $field['is_core'] );

			// Map section name to WooCommerce array keys
			$wc_section_key = match ( $section ) {
				'shipping' => 'shipping',
				'order'    => 'order',
				default    => 'billing',
			};

			// If disabled in settings, remove field from checkout
			if ( empty( $field['enabled'] ) ) {
				if ( isset( $wc_fields[ $wc_section_key ][ $key ] ) ) {
					unset( $wc_fields[ $wc_section_key ][ $key ] );
				}
				continue;
			}

			// Map width to WooCommerce CSS classes
			$class = [ 'form-row' ];
			switch ( $field['width'] ?? 'full' ) {
				case 'half_left':
					$class[] = 'form-row-first';
					break;
				case 'half_right':
					$class[] = 'form-row-last';
					break;
				case 'third':
					$class[] = 'form-row-third';
					break;
				case 'full':
				default:
					$class[] = 'form-row-wide';
					break;
			}

			if ( ! empty( $field['css_classes'] ) ) {
				$class[] = sanitize_html_class( (string) $field['css_classes'] );
			}

			// Build options array for select, radio, multiselect
			$options = [];
			if ( ! empty( $field['options'] ) && is_array( $field['options'] ) ) {
				foreach ( $field['options'] as $opt ) {
					if ( isset( $opt['value'] ) ) {
						$options[ $opt['value'] ] = $opt['label'] ?? $opt['value'];
					}
				}
			}

			// Determine current conditional requirement
			$required = ConditionEngine::is_field_required( $field, $context );

			// Compose field arguments
			$field_args = [
				'type'              => (string) ( $field['type'] ?? 'text' ),
				'label'             => (string) ( $field['label'] ?? '' ),
				'placeholder'       => (string) ( $field['placeholder'] ?? '' ),
				'description'       => (string) ( $field['description'] ?? '' ),
				'required'          => $required,
				'class'             => $class,
				'priority'          => (int) ( $field['priority'] ?? 10 ),
				'options'           => $options,
				'default'           => (string) ( $field['default_value'] ?? '' ),
				'custom_attributes' => [],
			];

			if ( ! empty( $field['autocomplete'] ) ) {
				$field_args['autocomplete'] = (string) $field['autocomplete'];
			}

			// Merge or set in WooCommerce fields array
			if ( $is_core && isset( $wc_fields[ $wc_section_key ][ $key ] ) ) {
				$wc_fields[ $wc_section_key ][ $key ] = array_merge( $wc_fields[ $wc_section_key ][ $key ], $field_args );
			} else {
				$wc_fields[ $wc_section_key ][ $key ] = $field_args;
			}
		}

		return $wc_fields;
	}

	/**
	 * Custom form field renderer: HTML5 Date input.
	 *
	 * @param string               $field
	 * @param string               $key
	 * @param array<string, mixed> $args
	 * @param mixed                $value
	 * @return string
	 */
	public function render_date_field( string $field, string $key, array $args, mixed $value ): string {
		$required_marker = ! empty( $args['required'] ) ? '&nbsp;<abbr class="required" title="required">*</abbr>' : '';
		$label = ! empty( $args['label'] ) ? '<label for="' . esc_attr( $key ) . '">' . esc_html( $args['label'] ) . $required_marker . '</label>' : '';
		$class = ! empty( $args['class'] ) ? implode( ' ', array_map( 'esc_attr', $args['class'] ) ) : 'form-row';

		return sprintf(
			'<p class="%1$s" id="%2$s_field">%3$s<span class="woocommerce-input-wrapper"><input type="date" class="input-text" name="%2$s" id="%2$s" value="%4$s" /></span></p>',
			esc_attr( $class ),
			esc_attr( $key ),
			$label,
			esc_attr( (string) $value )
		);
	}

	/**
	 * Custom form field renderer: HTML5 Time input.
	 *
	 * @param string               $field
	 * @param string               $key
	 * @param array<string, mixed> $args
	 * @param mixed                $value
	 * @return string
	 */
	public function render_time_field( string $field, string $key, array $args, mixed $value ): string {
		$required_marker = ! empty( $args['required'] ) ? '&nbsp;<abbr class="required" title="required">*</abbr>' : '';
		$label = ! empty( $args['label'] ) ? '<label for="' . esc_attr( $key ) . '">' . esc_html( $args['label'] ) . $required_marker . '</label>' : '';
		$class = ! empty( $args['class'] ) ? implode( ' ', array_map( 'esc_attr', $args['class'] ) ) : 'form-row';

		return sprintf(
			'<p class="%1$s" id="%2$s_field">%3$s<span class="woocommerce-input-wrapper"><input type="time" class="input-text" name="%2$s" id="%2$s" value="%4$s" /></span></p>',
			esc_attr( $class ),
			esc_attr( $key ),
			$label,
			esc_attr( (string) $value )
		);
	}

	/**
	 * Custom form field renderer: Non-input Heading Element.
	 *
	 * @param string               $field
	 * @param string               $key
	 * @param array<string, mixed> $args
	 * @param mixed                $value
	 * @return string
	 */
	public function render_heading_element( string $field, string $key, array $args, mixed $value ): string {
		$title = ! empty( $args['label'] ) ? esc_html( $args['label'] ) : '';
		$desc  = ! empty( $args['description'] ) ? '<p class="wcs-heading-desc">' . esc_html( $args['description'] ) . '</p>' : '';
		return sprintf( '<div class="wcs-checkout-heading" id="%1$s_heading"><h3>%2$s</h3>%3$s</div>', esc_attr( $key ), $title, $desc );
	}

	/**
	 * Validate fields during checkout submission.
	 *
	 * @return void
	 */
	public function validate_checkout_fields(): void {
		$fields = $this->repo->get_fields();
		$posted = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$errors = ValidationEngine::validate( $fields, $posted );

		foreach ( $errors as $error_message ) {
			wc_add_notice( $error_message, 'error' );
		}
	}

	/**
	 * Save custom checkout field values into WooCommerce Order CRUD metadata.
	 *
	 * @param \WC_Order            $order
	 * @param array<string, mixed> $data
	 * @return void
	 */
	public function save_order_metadata( \WC_Order $order, array $data ): void {
		$fields = $this->repo->get_fields();
		$posted = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		foreach ( $fields as $field ) {
			// Only save custom fields into order metadata (core fields are saved by Woo native handlers)
			if ( ! empty( $field['is_core'] ) ) {
				continue;
			}

			$key = (string) ( $field['key'] ?? '' );
			if ( ! isset( $posted[ $key ] ) ) {
				continue;
			}

			$type            = (string) ( $field['type'] ?? 'text' );
			$allowed_options = (array) ( $field['options'] ?? [] );
			$sanitized_value = FieldTypeRegistry::sanitize_value( $posted[ $key ], $type, $allowed_options );

			// Save with unique WPCalibrate prefix
			$meta_key = '_wpcalibrate_' . $key;
			$order->update_meta_data( $meta_key, $sanitized_value );

			// Also store human readable field snapshot for order display
			$order->update_meta_data(
				'_wpcalibrate_label_' . $key,
				[
					'label'   => $field['label'] ?? $key,
					'type'    => $type,
					'display' => $field['display'] ?? [],
				]
			);
		}
	}

	/**
	 * Enqueue checkout assets strictly on checkout pages.
	 *
	 * @return void
	 */
	public function enqueue_checkout_assets(): void {
		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_order_received_page() ) {
			return;
		}

		// Enqueue frontend checkout script
		wp_enqueue_script(
			'wpcalibrate-checkout-studio-frontend',
			WPCALIBRATE_CHECKOUT_STUDIO_URL . 'assets/js/checkout.js',
			[ 'jquery' ],
			WPCALIBRATE_CHECKOUT_STUDIO_VERSION,
			true
		);

		// Localize conditional rules for client-side evaluation
		wp_localize_script(
			'wpcalibrate-checkout-studio-frontend',
			'wcsCheckoutData',
			[
				'fields' => $this->repo->get_fields(),
			]
		);

		// Inject scoped appearance CSS if enabled
		$appearance_engine = new AppearanceEngine( $this->repo );
		$scoped_css = $appearance_engine->generate_css();
		if ( ! empty( $scoped_css ) ) {
			wp_add_inline_style( 'woocommerce-layout', $scoped_css );
		}
	}
}
