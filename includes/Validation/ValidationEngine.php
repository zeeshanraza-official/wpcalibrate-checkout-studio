<?php
/**
 * Validation engine applying server-side validation rules to submitted checkout values.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Validation;

use WPCalibrate\CheckoutStudio\Conditions\ConditionEngine;
use WPCalibrate\CheckoutStudio\Fields\FieldTypeRegistry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ValidationEngine
 */
final class ValidationEngine {

	/**
	 * Validate all submitted checkout fields against their configured rules and current conditions.
	 *
	 * @param array<int, array<string, mixed>> $fields All configured fields.
	 * @param array<string, mixed>             $posted_data Raw or sanitized checkout POST data.
	 * @return array<int, string> Array of error messages (empty if all valid).
	 */
	public static function validate( array $fields, array $posted_data ): array {
		$errors  = [];
		$context = ConditionEngine::get_context( $posted_data );

		foreach ( $fields as $field ) {
			if ( empty( $field['enabled'] ) ) {
				continue;
			}

			$key   = (string) ( $field['key'] ?? '' );
			$label = (string) ( $field['label'] ?? $key );
			$value = $posted_data[ $key ] ?? null;

			// If field is hidden by conditional logic, skip validation entirely.
			if ( ! ConditionEngine::is_field_visible( $field, $context ) ) {
				continue;
			}

			$is_required = ConditionEngine::is_field_required( $field, $context );

			// Check required validation
			$is_empty = ( null === $value || '' === $value || ( is_array( $value ) && empty( $value ) ) );
			if ( $is_required && $is_empty ) {
				$errors[] = sprintf(
					/* translators: %s: Field label */
					__( '<strong>%s</strong> is a required field.', 'wpcalibrate-checkout-studio' ),
					esc_html( $label )
				);
				continue;
			}

			// If optional and empty, no further format validation needed
			if ( $is_empty ) {
				continue;
			}

			// Specific Type & Format Validations
			$type = (string) ( $field['type'] ?? 'text' );

			switch ( $type ) {
				case 'email':
					if ( ! is_email( (string) $value ) ) {
						$errors[] = sprintf(
							/* translators: %s: Field label */
							__( '<strong>%s</strong> must be a valid email address.', 'wpcalibrate-checkout-studio' ),
							esc_html( $label )
						);
					}
					break;

				case 'tel':
					$clean_phone = preg_replace( '/[^\d\+\-\(\)\s\.]/', '', (string) $value );
					if ( strlen( (string) $clean_phone ) < 6 ) {
						$errors[] = sprintf(
							/* translators: %s: Field label */
							__( '<strong>%s</strong> must be a valid telephone number.', 'wpcalibrate-checkout-studio' ),
							esc_html( $label )
						);
					}
					break;

				case 'number':
					if ( ! is_numeric( $value ) ) {
						$errors[] = sprintf(
							/* translators: %s: Field label */
							__( '<strong>%s</strong> must be a valid number.', 'wpcalibrate-checkout-studio' ),
							esc_html( $label )
						);
					} else {
						$num = (float) $value;
						$val_rules = $field['validation'] ?? [];
						if ( isset( $val_rules['min'] ) && $num < (float) $val_rules['min'] ) {
							$errors[] = sprintf(
								/* translators: 1: Field label, 2: Minimum value */
								__( '<strong>%1$s</strong> must be at least %2$s.', 'wpcalibrate-checkout-studio' ),
								esc_html( $label ),
								esc_html( (string) $val_rules['min'] )
							);
						}
						if ( isset( $val_rules['max'] ) && $num > (float) $val_rules['max'] ) {
							$errors[] = sprintf(
								/* translators: 1: Field label, 2: Maximum value */
								__( '<strong>%1$s</strong> must not exceed %2$s.', 'wpcalibrate-checkout-studio' ),
								esc_html( $label ),
								esc_html( (string) $val_rules['max'] )
							);
						}
					}
					break;

				case 'select':
				case 'radio':
					$allowed_options = $field['options'] ?? [];
					if ( ! empty( $allowed_options ) ) {
						$valid_values = array_column( $allowed_options, 'value' );
						if ( ! in_array( (string) $value, $valid_values, true ) ) {
							$errors[] = sprintf(
								/* translators: %s: Field label */
								__( '<strong>%s</strong> contains an invalid selection.', 'wpcalibrate-checkout-studio' ),
								esc_html( $label )
							);
						}
					}
					break;

				case 'multiselect':
					$allowed_options = $field['options'] ?? [];
					if ( ! empty( $allowed_options ) ) {
						$valid_values = array_column( $allowed_options, 'value' );
						$submitted_vals = is_array( $value ) ? $value : [ $value ];
						foreach ( $submitted_vals as $sub_val ) {
							if ( ! in_array( (string) $sub_val, $valid_values, true ) ) {
								$errors[] = sprintf(
									/* translators: %s: Field label */
									__( '<strong>%s</strong> contains an invalid choice.', 'wpcalibrate-checkout-studio' ),
									esc_html( $label )
								);
								break;
							}
						}
					}
					break;

				case 'date':
					if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $value ) ) {
						$errors[] = sprintf(
							/* translators: %s: Field label */
							__( '<strong>%s</strong> must be a valid date format (YYYY-MM-DD).', 'wpcalibrate-checkout-studio' ),
							esc_html( $label )
						);
					}
					break;

				case 'time':
					if ( ! preg_match( '/^\d{2}:\d{2}(:\d{2})?$/', (string) $value ) ) {
						$errors[] = sprintf(
							/* translators: %s: Field label */
							__( '<strong>%s</strong> must be a valid time format (HH:MM).', 'wpcalibrate-checkout-studio' ),
							esc_html( $label )
						);
					}
					break;
			}

			// Text length validation
			if ( is_string( $value ) ) {
				$val_rules = $field['validation'] ?? [];
				$str_len   = mb_strlen( $value );

				if ( isset( $val_rules['min_length'] ) && $str_len < (int) $val_rules['min_length'] ) {
					$errors[] = sprintf(
						/* translators: 1: Field label, 2: Minimum character length */
						__( '<strong>%1$s</strong> must be at least %2$d characters long.', 'wpcalibrate-checkout-studio' ),
						esc_html( $label ),
						(int) $val_rules['min_length']
					);
				}

				if ( isset( $val_rules['max_length'] ) && $str_len > (int) $val_rules['max_length'] ) {
					$errors[] = sprintf(
						/* translators: 1: Field label, 2: Maximum character length */
						__( '<strong>%1$s</strong> must not exceed %2$d characters.', 'wpcalibrate-checkout-studio' ),
						esc_html( $label ),
						(int) $val_rules['max_length']
					);
				}

				if ( ! empty( $val_rules['pattern'] ) ) {
					if ( ! @preg_match( (string) $val_rules['pattern'], $value ) ) {
						$errors[] = sprintf(
							/* translators: %s: Field label */
							__( '<strong>%s</strong> is not in the required format.', 'wpcalibrate-checkout-studio' ),
							esc_html( $label )
						);
					}
				}
			}
		}

		return $errors;
	}
}
