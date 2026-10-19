<?php
/**
 * Field type registry and validation/sanitization specs for all checkout field types.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FieldTypeRegistry
 */
final class FieldTypeRegistry {

	/**
	 * Get metadata for all supported field types.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function get_all(): array {
		return [
			'text' => [
				'label'            => __( 'Text', 'wpcalibrate-checkout-studio' ),
				'has_options'      => false,
				'has_min_max'      => false,
				'has_length'       => true,
				'has_placeholder'  => true,
				'blocks_supported' => true,
				'blocks_type'      => 'text',
			],
			'textarea' => [
				'label'            => __( 'Textarea', 'wpcalibrate-checkout-studio' ),
				'has_options'      => false,
				'has_min_max'      => false,
				'has_length'       => true,
				'has_placeholder'  => true,
				'blocks_supported' => true,
				'blocks_type'      => 'text',
			],
			'email' => [
				'label'            => __( 'Email', 'wpcalibrate-checkout-studio' ),
				'has_options'      => false,
				'has_min_max'      => false,
				'has_length'       => true,
				'has_placeholder'  => true,
				'blocks_supported' => true,
				'blocks_type'      => 'text',
			],
			'tel' => [
				'label'            => __( 'Telephone', 'wpcalibrate-checkout-studio' ),
				'has_options'      => false,
				'has_min_max'      => false,
				'has_length'       => true,
				'has_placeholder'  => true,
				'blocks_supported' => true,
				'blocks_type'      => 'text',
			],
			'number' => [
				'label'            => __( 'Number', 'wpcalibrate-checkout-studio' ),
				'has_options'      => false,
				'has_min_max'      => true,
				'has_length'       => false,
				'has_placeholder'  => true,
				'blocks_supported' => true,
				'blocks_type'      => 'text',
			],
			'password' => [
				'label'            => __( 'Password', 'wpcalibrate-checkout-studio' ),
				'has_options'      => false,
				'has_min_max'      => false,
				'has_length'       => true,
				'has_placeholder'  => false,
				'blocks_supported' => false,
				'blocks_type'      => null,
			],
			'select' => [
				'label'            => __( 'Select Dropdown', 'wpcalibrate-checkout-studio' ),
				'has_options'      => true,
				'has_min_max'      => false,
				'has_length'       => false,
				'has_placeholder'  => true,
				'blocks_supported' => true,
				'blocks_type'      => 'select',
			],
			'multiselect' => [
				'label'            => __( 'Multi-Select', 'wpcalibrate-checkout-studio' ),
				'has_options'      => true,
				'has_min_max'      => false,
				'has_length'       => false,
				'has_placeholder'  => true,
				'blocks_supported' => false,
				'blocks_type'      => null,
			],
			'radio' => [
				'label'            => __( 'Radio Group', 'wpcalibrate-checkout-studio' ),
				'has_options'      => true,
				'has_min_max'      => false,
				'has_length'       => false,
				'has_placeholder'  => false,
				'blocks_supported' => false,
				'blocks_type'      => null,
			],
			'checkbox' => [
				'label'            => __( 'Single Checkbox', 'wpcalibrate-checkout-studio' ),
				'has_options'      => false,
				'has_min_max'      => false,
				'has_length'       => false,
				'has_placeholder'  => false,
				'blocks_supported' => true,
				'blocks_type'      => 'checkbox',
			],
			'date' => [
				'label'            => __( 'Date Picker', 'wpcalibrate-checkout-studio' ),
				'has_options'      => false,
				'has_min_max'      => true,
				'has_length'       => false,
				'has_placeholder'  => false,
				'blocks_supported' => false,
				'blocks_type'      => null,
			],
			'time' => [
				'label'            => __( 'Time', 'wpcalibrate-checkout-studio' ),
				'has_options'      => false,
				'has_min_max'      => false,
				'has_length'       => false,
				'has_placeholder'  => false,
				'blocks_supported' => false,
				'blocks_type'      => null,
			],
			'hidden' => [
				'label'            => __( 'Hidden Field', 'wpcalibrate-checkout-studio' ),
				'has_options'      => false,
				'has_min_max'      => false,
				'has_length'       => false,
				'has_placeholder'  => false,
				'blocks_supported' => false,
				'blocks_type'      => null,
			],
			'heading' => [
				'label'            => __( 'Section Heading / Notice', 'wpcalibrate-checkout-studio' ),
				'has_options'      => false,
				'has_min_max'      => false,
				'has_length'       => false,
				'has_placeholder'  => false,
				'blocks_supported' => false,
				'blocks_type'      => null,
			],
		];
	}

	/**
	 * Check if a field type is supported.
	 *
	 * @param string $type
	 * @return bool
	 */
	public static function is_valid_type( string $type ): bool {
		return array_key_exists( $type, self::get_all() );
	}

	/**
	 * Sanitize submitted checkout field value according to its type.
	 *
	 * @param mixed  $raw_value
	 * @param string $type
	 * @param array<int, array<string, string>> $allowed_options
	 * @return mixed
	 */
	public static function sanitize_value( mixed $raw_value, string $type, array $allowed_options = [] ): mixed {
		if ( null === $raw_value ) {
			return '';
		}

		switch ( $type ) {
			case 'email':
				return sanitize_email( (string) $raw_value );

			case 'textarea':
				return sanitize_textarea_field( (string) $raw_value );

			case 'number':
				return is_numeric( $raw_value ) ? (float) $raw_value : '';

			case 'checkbox':
				return ! empty( $raw_value ) && ( '1' === (string) $raw_value || 'yes' === (string) $raw_value || true === $raw_value );

			case 'select':
			case 'radio':
				$val = sanitize_text_field( (string) $raw_value );
				if ( ! empty( $allowed_options ) ) {
					$allowed_values = array_column( $allowed_options, 'value' );
					if ( ! in_array( $val, $allowed_values, true ) ) {
						return '';
					}
				}
				return $val;

			case 'multiselect':
				if ( ! is_array( $raw_value ) ) {
					$raw_value = [ $raw_value ];
				}
				$allowed_values = ! empty( $allowed_options ) ? array_column( $allowed_options, 'value' ) : [];
				$sanitized_items = [];
				foreach ( $raw_value as $item ) {
					$cleaned = sanitize_text_field( (string) $item );
					if ( empty( $allowed_values ) || in_array( $cleaned, $allowed_values, true ) ) {
						$sanitized_items[] = $cleaned;
					}
				}
				return $sanitized_items;

			case 'date':
				$val = sanitize_text_field( (string) $raw_value );
				return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $val ) ? $val : '';

			case 'time':
				$val = sanitize_text_field( (string) $raw_value );
				return preg_match( '/^\d{2}:\d{2}(:\d{2})?$/', $val ) ? $val : '';

			case 'heading':
				return ''; // Heading doesn't store input values

			case 'password':
				// Strip tags, do not serialize or retain plain passwords permanently
				return sanitize_text_field( (string) $raw_value );

			case 'tel':
			case 'text':
			case 'hidden':
			default:
				return sanitize_text_field( (string) $raw_value );
		}
	}
}
