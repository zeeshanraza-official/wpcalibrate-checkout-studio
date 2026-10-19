<?php
/**
 * Checkout Blocks adapter using WooCommerce Additional Checkout Fields API & Store API.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Checkout\Blocks;

use WPCalibrate\CheckoutStudio\Settings\SettingsRepository;
use WPCalibrate\CheckoutStudio\Fields\FieldTypeRegistry;
use WPCalibrate\CheckoutStudio\Conditions\ConditionEngine;
use WPCalibrate\CheckoutStudio\Validation\ValidationEngine;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BlocksCheckoutAdapter
 */
final class BlocksCheckoutAdapter {

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
	 * Initialize Checkout Blocks hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'woocommerce_init', [ $this, 'register_blocks_fields' ] );
		add_action( 'woocommerce_validate_additional_field', [ $this, 'validate_block_field' ], 10, 3 );
		add_action( 'woocommerce_set_additional_field_value', [ $this, 'save_block_field_value' ], 10, 4 );
	}

	/**
	 * Register custom checkout fields with WooCommerce Additional Checkout Fields API.
	 *
	 * @return void
	 */
	public function register_blocks_fields(): void {
		if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) {
			return;
		}

		$fields = $this->repo->get_fields();

		foreach ( $fields as $field ) {
			// Skip core fields as WooCommerce Blocks handles standard address fields natively
			if ( ! empty( $field['is_core'] ) || empty( $field['enabled'] ) ) {
				continue;
			}

			$key  = (string) ( $field['key'] ?? '' );
			$type = (string) ( $field['type'] ?? 'text' );

			// Map to WooCommerce Blocks supported field type
			$block_type = match ( $type ) {
				'checkbox' => 'checkbox',
				'select'   => 'select',
				default    => 'text',
			};

			// Map to WooCommerce Blocks supported locations: 'order', 'billing', 'shipping'
			$section  = (string) ( $field['section'] ?? 'order' );
			$location = match ( $section ) {
				'billing'  => 'billing',
				'shipping' => 'shipping',
				default    => 'order',
			};

			// Build options list for select fields
			$options = [];
			if ( 'select' === $block_type && ! empty( $field['options'] ) ) {
				foreach ( $field['options'] as $opt ) {
					if ( isset( $opt['value'] ) ) {
						$options[] = [
							'value' => (string) $opt['value'],
							'label' => (string) ( $opt['label'] ?? $opt['value'] ),
						];
					}
				}
			}

			$field_config = [
				'id'       => 'wpcalibrate/' . $key,
				'label'    => (string) ( $field['label'] ?? $key ),
				'location' => $location,
				'type'     => $block_type,
				'required' => ! empty( $field['required'] ),
			];

			if ( ! empty( $options ) ) {
				$field_config['options'] = $options;
			}

			woocommerce_register_additional_checkout_field( $field_config );
		}
	}

	/**
	 * Validate additional field submitted through Store API.
	 *
	 * @param \WP_Error $errors
	 * @param string    $field_key
	 * @param mixed     $field_value
	 * @return \WP_Error
	 */
	public function validate_block_field( \WP_Error $errors, string $field_key, mixed $field_value ): \WP_Error {
		if ( ! str_starts_with( $field_key, 'wpcalibrate/' ) ) {
			return $errors;
		}

		$clean_key = str_replace( 'wpcalibrate/', '', $field_key );
		$field     = $this->repo->get_field( $clean_key );

		if ( ! $field ) {
			return $errors;
		}

		$single_field_errors = ValidationEngine::validate( [ $field ], [ $clean_key => $field_value ] );

		foreach ( $single_field_errors as $err ) {
			// Strip HTML strong tags for Blocks notice rendering
			$clean_message = wp_strip_all_tags( $err );
			$errors->add( 'wcs_validation_error_' . $clean_key, $clean_message );
		}

		return $errors;
	}

	/**
	 * Save Store API additional field value to order metadata.
	 *
	 * @param string    $field_key
	 * @param mixed     $field_value
	 * @param string    $group
	 * @param \WC_Order $order
	 * @return void
	 */
	public function save_block_field_value( string $field_key, mixed $field_value, string $group, \WC_Order $order ): void {
		if ( ! str_starts_with( $field_key, 'wpcalibrate/' ) ) {
			return;
		}

		$clean_key = str_replace( 'wpcalibrate/', '', $field_key );
		$field     = $this->repo->get_field( $clean_key );

		if ( ! $field ) {
			return;
		}

		$type            = (string) ( $field['type'] ?? 'text' );
		$allowed_options = (array) ( $field['options'] ?? [] );
		$sanitized_value = FieldTypeRegistry::sanitize_value( $field_value, $type, $allowed_options );

		$order->update_meta_data( '_wpcalibrate_' . $clean_key, $sanitized_value );
		$order->update_meta_data(
			'_wpcalibrate_label_' . $clean_key,
			[
				'label'   => $field['label'] ?? $clean_key,
				'type'    => $type,
				'display' => $field['display'] ?? [],
			]
		);
	}
}
