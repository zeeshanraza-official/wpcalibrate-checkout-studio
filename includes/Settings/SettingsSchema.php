<?php
/**
 * Settings schema definition, defaults, and sanitization.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SettingsSchema
 */
final class SettingsSchema {

	public const CURRENT_SCHEMA_VERSION = '1.0.0';

	/**
	 * Supported field types.
	 */
	public const SUPPORTED_FIELD_TYPES = [
		'text'        => 'Text',
		'textarea'    => 'Textarea',
		'email'       => 'Email',
		'tel'         => 'Telephone',
		'number'      => 'Number',
		'password'    => 'Password',
		'select'      => 'Select Dropdown',
		'multiselect' => 'Multi-Select',
		'radio'       => 'Radio Group',
		'checkbox'    => 'Single Checkbox',
		'date'        => 'Date Picker',
		'time'        => 'Time',
		'hidden'      => 'Hidden Field',
		'heading'     => 'Section Heading / Notice',
	];

	/**
	 * Get default core sections.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_default_sections(): array {
		return [
			[
				'id'                  => 'sec_billing',
				'key'                 => 'billing',
				'title'               => __( 'Billing Details', 'wpcalibrate-checkout-studio' ),
				'description'         => __( 'Standard customer billing information.', 'wpcalibrate-checkout-studio' ),
				'enabled'             => true,
				'priority'            => 10,
				'is_core'             => true,
				'supported_renderers' => [ 'classic', 'blocks' ],
			],
			[
				'id'                  => 'sec_shipping',
				'key'                 => 'shipping',
				'title'               => __( 'Shipping Details', 'wpcalibrate-checkout-studio' ),
				'description'         => __( 'Delivery address information.', 'wpcalibrate-checkout-studio' ),
				'enabled'             => true,
				'priority'            => 20,
				'is_core'             => true,
				'supported_renderers' => [ 'classic', 'blocks' ],
			],
			[
				'id'                  => 'sec_order',
				'key'                 => 'order',
				'title'               => __( 'Additional Information', 'wpcalibrate-checkout-studio' ),
				'description'         => __( 'Order notes and supplementary checkout information.', 'wpcalibrate-checkout-studio' ),
				'enabled'             => true,
				'priority'            => 30,
				'is_core'             => true,
				'supported_renderers' => [ 'classic', 'blocks' ],
			],
		];
	}

	/**
	 * Get default core fields.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_default_fields(): array {
		return [
			// Billing fields
			[
				'id'          => 'core_billing_first_name',
				'key'         => 'billing_first_name',
				'is_core'     => true,
				'type'        => 'text',
				'label'       => __( 'First name', 'wpcalibrate-checkout-studio' ),
				'placeholder' => '',
				'description' => '',
				'section'     => 'billing',
				'enabled'     => true,
				'required'    => true,
				'priority'    => 10,
				'width'       => 'half_left',
				'autocomplete'=> 'given-name',
				'options'     => [],
				'validation'  => [],
				'conditions'  => [],
				'display'     => [ 'admin' => true, 'customer' => true, 'emails' => true ],
			],
			[
				'id'          => 'core_billing_last_name',
				'key'         => 'billing_last_name',
				'is_core'     => true,
				'type'        => 'text',
				'label'       => __( 'Last name', 'wpcalibrate-checkout-studio' ),
				'placeholder' => '',
				'description' => '',
				'section'     => 'billing',
				'enabled'     => true,
				'required'    => true,
				'priority'    => 20,
				'width'       => 'half_right',
				'autocomplete'=> 'family-name',
				'options'     => [],
				'validation'  => [],
				'conditions'  => [],
				'display'     => [ 'admin' => true, 'customer' => true, 'emails' => true ],
			],
			[
				'id'          => 'core_billing_company',
				'key'         => 'billing_company',
				'is_core'     => true,
				'type'        => 'text',
				'label'       => __( 'Company name', 'wpcalibrate-checkout-studio' ),
				'placeholder' => '',
				'description' => '',
				'section'     => 'billing',
				'enabled'     => true,
				'required'    => false,
				'priority'    => 30,
				'width'       => 'full',
				'autocomplete'=> 'organization',
				'options'     => [],
				'validation'  => [],
				'conditions'  => [],
				'display'     => [ 'admin' => true, 'customer' => true, 'emails' => true ],
			],
			[
				'id'          => 'core_billing_country',
				'key'         => 'billing_country',
				'is_core'     => true,
				'type'        => 'select',
				'label'       => __( 'Country / Region', 'wpcalibrate-checkout-studio' ),
				'placeholder' => '',
				'description' => '',
				'section'     => 'billing',
				'enabled'     => true,
				'required'    => true,
				'priority'    => 40,
				'width'       => 'full',
				'autocomplete'=> 'country',
				'options'     => [],
				'validation'  => [],
				'conditions'  => [],
				'display'     => [ 'admin' => true, 'customer' => true, 'emails' => true ],
			],
			[
				'id'          => 'core_billing_address_1',
				'key'         => 'billing_address_1',
				'is_core'     => true,
				'type'        => 'text',
				'label'       => __( 'Street address', 'wpcalibrate-checkout-studio' ),
				'placeholder' => __( 'House number and street name', 'wpcalibrate-checkout-studio' ),
				'description' => '',
				'section'     => 'billing',
				'enabled'     => true,
				'required'    => true,
				'priority'    => 50,
				'width'       => 'full',
				'autocomplete'=> 'address-line1',
				'options'     => [],
				'validation'  => [],
				'conditions'  => [],
				'display'     => [ 'admin' => true, 'customer' => true, 'emails' => true ],
			],
			[
				'id'          => 'core_billing_address_2',
				'key'         => 'billing_address_2',
				'is_core'     => true,
				'type'        => 'text',
				'label'       => __( 'Apartment, suite, unit, etc. (optional)', 'wpcalibrate-checkout-studio' ),
				'placeholder' => __( 'Apartment, suite, unit, etc. (optional)', 'wpcalibrate-checkout-studio' ),
				'description' => '',
				'section'     => 'billing',
				'enabled'     => true,
				'required'    => false,
				'priority'    => 60,
				'width'       => 'full',
				'autocomplete'=> 'address-line2',
				'options'     => [],
				'validation'  => [],
				'conditions'  => [],
				'display'     => [ 'admin' => true, 'customer' => true, 'emails' => true ],
			],
			[
				'id'          => 'core_billing_city',
				'key'         => 'billing_city',
				'is_core'     => true,
				'type'        => 'text',
				'label'       => __( 'Town / City', 'wpcalibrate-checkout-studio' ),
				'placeholder' => '',
				'description' => '',
				'section'     => 'billing',
				'enabled'     => true,
				'required'    => true,
				'priority'    => 70,
				'width'       => 'full',
				'autocomplete'=> 'address-level2',
				'options'     => [],
				'validation'  => [],
				'conditions'  => [],
				'display'     => [ 'admin' => true, 'customer' => true, 'emails' => true ],
			],
			[
				'id'          => 'core_billing_state',
				'key'         => 'billing_state',
				'is_core'     => true,
				'type'        => 'text',
				'label'       => __( 'State / County', 'wpcalibrate-checkout-studio' ),
				'placeholder' => '',
				'description' => '',
				'section'     => 'billing',
				'enabled'     => true,
				'required'    => true,
				'priority'    => 80,
				'width'       => 'half_left',
				'autocomplete'=> 'address-level1',
				'options'     => [],
				'validation'  => [],
				'conditions'  => [],
				'display'     => [ 'admin' => true, 'customer' => true, 'emails' => true ],
			],
			[
				'id'          => 'core_billing_postcode',
				'key'         => 'billing_postcode',
				'is_core'     => true,
				'type'        => 'text',
				'label'       => __( 'Postcode / ZIP', 'wpcalibrate-checkout-studio' ),
				'placeholder' => '',
				'description' => '',
				'section'     => 'billing',
				'enabled'     => true,
				'required'    => true,
				'priority'    => 90,
				'width'       => 'half_right',
				'autocomplete'=> 'postal-code',
				'options'     => [],
				'validation'  => [],
				'conditions'  => [],
				'display'     => [ 'admin' => true, 'customer' => true, 'emails' => true ],
			],
			[
				'id'          => 'core_billing_phone',
				'key'         => 'billing_phone',
				'is_core'     => true,
				'type'        => 'tel',
				'label'       => __( 'Phone', 'wpcalibrate-checkout-studio' ),
				'placeholder' => '',
				'description' => '',
				'section'     => 'billing',
				'enabled'     => true,
				'required'    => true,
				'priority'    => 100,
				'width'       => 'half_left',
				'autocomplete'=> 'tel',
				'options'     => [],
				'validation'  => [],
				'conditions'  => [],
				'display'     => [ 'admin' => true, 'customer' => true, 'emails' => true ],
			],
			[
				'id'          => 'core_billing_email',
				'key'         => 'billing_email',
				'is_core'     => true,
				'type'        => 'email',
				'label'       => __( 'Email address', 'wpcalibrate-checkout-studio' ),
				'placeholder' => '',
				'description' => '',
				'section'     => 'billing',
				'enabled'     => true,
				'required'    => true,
				'priority'    => 110,
				'width'       => 'half_right',
				'autocomplete'=> 'email',
				'options'     => [],
				'validation'  => [],
				'conditions'  => [],
				'display'     => [ 'admin' => true, 'customer' => true, 'emails' => true ],
			],

			// Shipping fields
			[
				'id'          => 'core_shipping_first_name',
				'key'         => 'shipping_first_name',
				'is_core'     => true,
				'type'        => 'text',
				'label'       => __( 'First name', 'wpcalibrate-checkout-studio' ),
				'placeholder' => '',
				'description' => '',
				'section'     => 'shipping',
				'enabled'     => true,
				'required'    => true,
				'priority'    => 10,
				'width'       => 'half_left',
				'autocomplete'=> 'given-name',
				'options'     => [],
				'validation'  => [],
				'conditions'  => [],
				'display'     => [ 'admin' => true, 'customer' => true, 'emails' => true ],
			],
			[
				'id'          => 'core_shipping_last_name',
				'key'         => 'shipping_last_name',
				'is_core'     => true,
				'type'        => 'text',
				'label'       => __( 'Last name', 'wpcalibrate-checkout-studio' ),
				'placeholder' => '',
				'description' => '',
				'section'     => 'shipping',
				'enabled'     => true,
				'required'    => true,
				'priority'    => 20,
				'width'       => 'half_right',
				'autocomplete'=> 'family-name',
				'options'     => [],
				'validation'  => [],
				'conditions'  => [],
				'display'     => [ 'admin' => true, 'customer' => true, 'emails' => true ],
			],
			[
				'id'          => 'core_shipping_company',
				'key'         => 'shipping_company',
				'is_core'     => true,
				'type'        => 'text',
				'label'       => __( 'Company name', 'wpcalibrate-checkout-studio' ),
				'placeholder' => '',
				'description' => '',
				'section'     => 'shipping',
				'enabled'     => true,
				'required'    => false,
				'priority'    => 30,
				'width'       => 'full',
				'autocomplete'=> 'organization',
				'options'     => [],
				'validation'  => [],
				'conditions'  => [],
				'display'     => [ 'admin' => true, 'customer' => true, 'emails' => true ],
			],
			[
				'id'          => 'core_shipping_country',
				'key'         => 'shipping_country',
				'is_core'     => true,
				'type'        => 'select',
				'label'       => __( 'Country / Region', 'wpcalibrate-checkout-studio' ),
				'placeholder' => '',
				'description' => '',
				'section'     => 'shipping',
				'enabled'     => true,
				'required'    => true,
				'priority'    => 40,
				'width'       => 'full',
				'autocomplete'=> 'country',
				'options'     => [],
				'validation'  => [],
				'conditions'  => [],
				'display'     => [ 'admin' => true, 'customer' => true, 'emails' => true ],
			],
			[
				'id'          => 'core_shipping_address_1',
				'key'         => 'shipping_address_1',
				'is_core'     => true,
				'type'        => 'text',
				'label'       => __( 'Street address', 'wpcalibrate-checkout-studio' ),
				'placeholder' => __( 'House number and street name', 'wpcalibrate-checkout-studio' ),
				'description' => '',
				'section'     => 'shipping',
				'enabled'     => true,
				'required'    => true,
				'priority'    => 50,
				'width'       => 'full',
				'autocomplete'=> 'address-line1',
				'options'     => [],
				'validation'  => [],
				'conditions'  => [],
				'display'     => [ 'admin' => true, 'customer' => true, 'emails' => true ],
			],
			[
				'id'          => 'core_shipping_address_2',
				'key'         => 'shipping_address_2',
				'is_core'     => true,
				'type'        => 'text',
				'label'       => __( 'Apartment, suite, unit, etc. (optional)', 'wpcalibrate-checkout-studio' ),
				'placeholder' => __( 'Apartment, suite, unit, etc. (optional)', 'wpcalibrate-checkout-studio' ),
				'description' => '',
				'section'     => 'shipping',
				'enabled'     => true,
				'required'    => false,
				'priority'    => 60,
				'width'       => 'full',
				'autocomplete'=> 'address-line2',
				'options'     => [],
				'validation'  => [],
				'conditions'  => [],
				'display'     => [ 'admin' => true, 'customer' => true, 'emails' => true ],
			],
			[
				'id'          => 'core_shipping_city',
				'key'         => 'shipping_city',
				'is_core'     => true,
				'type'        => 'text',
				'label'       => __( 'Town / City', 'wpcalibrate-checkout-studio' ),
				'placeholder' => '',
				'description' => '',
				'section'     => 'shipping',
				'enabled'     => true,
				'required'    => true,
				'priority'    => 70,
				'width'       => 'full',
				'autocomplete'=> 'address-level2',
				'options'     => [],
				'validation'  => [],
				'conditions'  => [],
				'display'     => [ 'admin' => true, 'customer' => true, 'emails' => true ],
			],
			[
				'id'          => 'core_shipping_state',
				'key'         => 'shipping_state',
				'is_core'     => true,
				'type'        => 'text',
				'label'       => __( 'State / County', 'wpcalibrate-checkout-studio' ),
				'placeholder' => '',
				'description' => '',
				'section'     => 'shipping',
				'enabled'     => true,
				'required'    => true,
				'priority'    => 80,
				'width'       => 'half_left',
				'autocomplete'=> 'address-level1',
				'options'     => [],
				'validation'  => [],
				'conditions'  => [],
				'display'     => [ 'admin' => true, 'customer' => true, 'emails' => true ],
			],
			[
				'id'          => 'core_shipping_postcode',
				'key'         => 'shipping_postcode',
				'is_core'     => true,
				'type'        => 'text',
				'label'       => __( 'Postcode / ZIP', 'wpcalibrate-checkout-studio' ),
				'placeholder' => '',
				'description' => '',
				'section'     => 'shipping',
				'enabled'     => true,
				'required'    => true,
				'priority'    => 90,
				'width'       => 'half_right',
				'autocomplete'=> 'postal-code',
				'options'     => [],
				'validation'  => [],
				'conditions'  => [],
				'display'     => [ 'admin' => true, 'customer' => true, 'emails' => true ],
			],

			// Order field
			[
				'id'          => 'core_order_comments',
				'key'         => 'order_comments',
				'is_core'     => true,
				'type'        => 'textarea',
				'label'       => __( 'Order notes', 'wpcalibrate-checkout-studio' ),
				'placeholder' => __( 'Notes about your order, e.g. special notes for delivery.', 'wpcalibrate-checkout-studio' ),
				'description' => '',
				'section'     => 'order',
				'enabled'     => true,
				'required'    => false,
				'priority'    => 10,
				'width'       => 'full',
				'autocomplete'=> '',
				'options'     => [],
				'validation'  => [],
				'conditions'  => [],
				'display'     => [ 'admin' => true, 'customer' => true, 'emails' => true ],
			],
		];
	}

	/**
	 * Sanitize and validate complete settings payload.
	 *
	 * @param array<string, mixed> $input Raw input.
	 * @return array<string, mixed> Sanitized settings array.
	 */
	public static function sanitize( array $input ): array {
		$sanitized = [];

		$sanitized['schema_version']      = self::CURRENT_SCHEMA_VERSION;
		$sanitized['enabled']             = ! empty( $input['enabled'] );
		$raw_mode                         = $input['renderer_mode'] ?? 'auto';
		$sanitized['renderer_mode']       = in_array( $raw_mode, [ 'auto', 'classic', 'blocks' ], true ) ? $raw_mode : 'auto';
		$sanitized['delete_on_uninstall'] = ! empty( $input['delete_on_uninstall'] );
		$sanitized['debug_logging']       = ! empty( $input['debug_logging'] );

		// Display settings
		$display = $input['display'] ?? [];
		$raw_loc = $display['email_location'] ?? 'order_meta';
		$sanitized['display'] = [
			'show_in_order_admin'   => ! empty( $display['show_in_order_admin'] ),
			'show_in_customer_view' => ! empty( $display['show_in_customer_view'] ),
			'show_in_emails'        => ! empty( $display['show_in_emails'] ),
			'email_location'        => in_array( $raw_loc, [ 'order_meta', 'customer_details' ], true ) ? $raw_loc : 'order_meta',
		];

		// Appearance settings
		$appearance = $input['appearance'] ?? [];
		$sanitized['appearance'] = self::sanitize_appearance( is_array( $appearance ) ? $appearance : [] );

		// Sections
		$sections = $input['sections'] ?? [];
		$sanitized['sections'] = self::sanitize_sections( is_array( $sections ) ? $sections : [] );

		// Fields
		$fields = $input['fields'] ?? [];
		$sanitized['fields'] = self::sanitize_fields( is_array( $fields ) ? $fields : [] );

		// Conditional rules
		$conditional_rules = $input['conditional_rules'] ?? [];
		$sanitized['conditional_rules'] = self::sanitize_conditional_rules( is_array( $conditional_rules ) ? $conditional_rules : [] );

		return $sanitized;
	}

	/**
	 * Sanitize appearance settings safely.
	 *
	 * @param array<string, mixed> $input
	 * @return array<string, mixed>
	 */
	public static function sanitize_appearance( array $input ): array {
		$raw_style  = $input['input_border_style'] ?? 'solid';
		$raw_weight = (string) ( $input['label_font_weight'] ?? '600' );

		return [
			'enabled'              => ! empty( $input['enabled'] ),
			'field_spacing'        => self::sanitize_css_dimension( (string) ( $input['field_spacing'] ?? '16px' ), '16px' ),
			'input_border_radius'  => self::sanitize_css_dimension( (string) ( $input['input_border_radius'] ?? '4px' ), '4px' ),
			'input_border_width'   => self::sanitize_css_dimension( (string) ( $input['input_border_width'] ?? '1px' ), '1px' ),
			'input_border_style'   => in_array( $raw_style, [ 'solid', 'dashed', 'dotted', 'none' ], true ) ? $raw_style : 'solid',
			'input_border_color'   => self::sanitize_hex_color( (string) ( $input['input_border_color'] ?? '#d1d5db' ), '#d1d5db' ),
			'input_bg_color'       => self::sanitize_hex_color( (string) ( $input['input_bg_color'] ?? '#ffffff' ), '#ffffff' ),
			'input_text_color'     => self::sanitize_hex_color( (string) ( $input['input_text_color'] ?? '#1f2937' ), '#1f2937' ),
			'input_focus_border'   => self::sanitize_hex_color( (string) ( $input['input_focus_border'] ?? '#2563eb' ), '#2563eb' ),
			'label_font_size'      => self::sanitize_css_dimension( (string) ( $input['label_font_size'] ?? '14px' ), '14px' ),
			'label_font_weight'    => in_array( $raw_weight, [ '400', '500', '600', '700' ], true ) ? $raw_weight : '600',
			'label_text_color'     => self::sanitize_hex_color( (string) ( $input['label_text_color'] ?? '#374151' ), '#374151' ),
			'section_spacing'      => self::sanitize_css_dimension( (string) ( $input['section_spacing'] ?? '24px' ), '24px' ),
			'button_bg_color'      => self::sanitize_hex_color( (string) ( $input['button_bg_color'] ?? '#2563eb' ), '#2563eb' ),
			'button_text_color'    => self::sanitize_hex_color( (string) ( $input['button_text_color'] ?? '#ffffff' ), '#ffffff' ),
			'button_border_radius' => self::sanitize_css_dimension( (string) ( $input['button_border_radius'] ?? '4px' ), '4px' ),
		];
	}

	/**
	 * Sanitize sections array.
	 *
	 * @param array<int, array<string, mixed>> $sections
	 * @return array<int, array<string, mixed>>
	 */
	public static function sanitize_sections( array $sections ): array {
		if ( empty( $sections ) ) {
			return self::get_default_sections();
		}

		$clean = [];
		foreach ( $sections as $sec ) {
			if ( ! is_array( $sec ) || empty( $sec['key'] ) ) {
				continue;
			}
			$key = sanitize_key( (string) $sec['key'] );
			$clean[] = [
				'id'                  => sanitize_key( (string) ( $sec['id'] ?? ( 'sec_' . $key ) ) ),
				'key'                 => $key,
				'title'               => sanitize_text_field( (string) ( $sec['title'] ?? '' ) ),
				'description'         => sanitize_text_field( (string) ( $sec['description'] ?? '' ) ),
				'enabled'             => ! empty( $sec['enabled'] ),
				'priority'            => isset( $sec['priority'] ) ? (int) $sec['priority'] : 10,
				'is_core'             => ! empty( $sec['is_core'] ),
				'supported_renderers' => is_array( $sec['supported_renderers'] ?? null ) ? array_intersect( $sec['supported_renderers'], [ 'classic', 'blocks' ] ) : [ 'classic', 'blocks' ],
			];
		}

		return $clean;
	}

	/**
	 * Sanitize fields array with strict checks and duplicate key prevention.
	 *
	 * @param array<int, array<string, mixed>> $fields
	 * @return array<int, array<string, mixed>>
	 */
	public static function sanitize_fields( array $fields ): array {
		if ( empty( $fields ) ) {
			return self::get_default_fields();
		}

		$clean = [];
		$seen_keys = [];

		foreach ( $fields as $f ) {
			if ( ! is_array( $f ) || empty( $f['key'] ) ) {
				continue;
			}

			$key = sanitize_key( (string) $f['key'] );
			if ( isset( $seen_keys[ $key ] ) ) {
				// Prevent duplicate machine keys.
				continue;
			}
			$seen_keys[ $key ] = true;

			$type = (string) ( $f['type'] ?? 'text' );
			if ( ! array_key_exists( $type, self::SUPPORTED_FIELD_TYPES ) ) {
				$type = 'text';
			}

			// Clean options array for select, radio, multiselect
			$raw_options = $f['options'] ?? [];
			$clean_options = [];
			if ( is_array( $raw_options ) ) {
				foreach ( $raw_options as $opt ) {
					if ( is_array( $opt ) && isset( $opt['value'] ) ) {
						$clean_options[] = [
							'value' => sanitize_text_field( (string) $opt['value'] ),
							'label' => sanitize_text_field( (string) ( $opt['label'] ?? $opt['value'] ) ),
						];
					} elseif ( is_string( $opt ) ) {
						$clean_options[] = [
							'value' => sanitize_text_field( $opt ),
							'label' => sanitize_text_field( $opt ),
						];
					}
				}
			}

			// Clean validation rules
			$raw_validation = $f['validation'] ?? [];
			$clean_validation = [];
			if ( is_array( $raw_validation ) ) {
				if ( isset( $raw_validation['min'] ) && is_numeric( $raw_validation['min'] ) ) {
					$clean_validation['min'] = (float) $raw_validation['min'];
				}
				if ( isset( $raw_validation['max'] ) && is_numeric( $raw_validation['max'] ) ) {
					$clean_validation['max'] = (float) $raw_validation['max'];
				}
				if ( isset( $raw_validation['min_length'] ) && is_numeric( $raw_validation['min_length'] ) ) {
					$clean_validation['min_length'] = (int) $raw_validation['min_length'];
				}
				if ( isset( $raw_validation['max_length'] ) && is_numeric( $raw_validation['max_length'] ) ) {
					$clean_validation['max_length'] = (int) $raw_validation['max_length'];
				}
				if ( ! empty( $raw_validation['pattern'] ) ) {
					// Validate regex safely: ensure delimiter and valid compilation
					$pattern = trim( (string) $raw_validation['pattern'] );
					if ( self::is_safe_regex( $pattern ) ) {
						$clean_validation['pattern'] = $pattern;
					}
				}
			}

			// Clean conditional logic
			$raw_conditions = $f['conditions'] ?? [];
			$clean_conditions = self::sanitize_field_conditions( is_array( $raw_conditions ) ? $raw_conditions : [] );

			// Clean display flags
			$raw_display = $f['display'] ?? [];
			$clean_display = [
				'admin'    => ! empty( $raw_display['admin'] ),
				'customer' => ! empty( $raw_display['customer'] ),
				'emails'   => ! empty( $raw_display['emails'] ),
			];

			$raw_width = $f['width'] ?? 'full';
			$width = in_array( $raw_width, [ 'full', 'half_left', 'half_right', 'third' ], true ) ? $raw_width : 'full';

			$clean[] = [
				'id'          => sanitize_key( (string) ( $f['id'] ?? ( 'wcs_f_' . $key ) ) ),
				'key'         => $key,
				'is_core'     => ! empty( $f['is_core'] ),
				'type'        => $type,
				'label'       => sanitize_text_field( (string) ( $f['label'] ?? '' ) ),
				'placeholder' => sanitize_text_field( (string) ( $f['placeholder'] ?? '' ) ),
				'description' => sanitize_text_field( (string) ( $f['description'] ?? '' ) ),
				'section'     => sanitize_key( (string) ( $f['section'] ?? 'billing' ) ),
				'enabled'     => ! empty( $f['enabled'] ),
				'required'    => ! empty( $f['required'] ),
				'priority'    => isset( $f['priority'] ) ? (int) $f['priority'] : 10,
				'width'       => $width,
				'css_classes' => sanitize_html_class( (string) ( $f['css_classes'] ?? '' ) ),
				'autocomplete'=> sanitize_text_field( (string) ( $f['autocomplete'] ?? '' ) ),
				'default_value'=> sanitize_text_field( (string) ( $f['default_value'] ?? '' ) ),
				'options'     => $clean_options,
				'validation'  => $clean_validation,
				'conditions'  => $clean_conditions,
				'display'     => $clean_display,
			];
		}

		return $clean;
	}

	/**
	 * Sanitize conditional rules structure.
	 *
	 * @param array<string, mixed> $input
	 * @return array<string, mixed>
	 */
	public static function sanitize_field_conditions( array $input ): array {
		if ( empty( $input ) || empty( $input['action'] ) ) {
			return [];
		}

		$action = in_array( $input['action'], [ 'show', 'hide', 'require', 'optional' ], true ) ? $input['action'] : 'show';
		$match  = in_array( $input['match'] ?? 'all', [ 'all', 'any' ], true ) ? $input['match'] : 'all';

		$raw_rules = $input['rules'] ?? [];
		$clean_rules = [];

		if ( is_array( $raw_rules ) ) {
			foreach ( $raw_rules as $r ) {
				if ( ! is_array( $r ) || empty( $r['source'] ) ) {
					continue;
				}
				$source = sanitize_key( (string) $r['source'] );
				$operator = in_array(
					$r['operator'] ?? 'equals',
					[ 'equals', 'not_equals', 'contains', 'does_not_contain', 'greater_than', 'less_than', 'is_empty', 'is_not_empty', 'in_list' ],
					true
				) ? $r['operator'] : 'equals';

				$clean_rules[] = [
					'source'   => $source,
					'operator' => $operator,
					'value'    => sanitize_text_field( (string) ( $r['value'] ?? '' ) ),
				];
			}
		}

		if ( empty( $clean_rules ) ) {
			return [];
		}

		return [
			'action' => $action,
			'match'  => $match,
			'rules'  => $clean_rules,
		];
	}

	/**
	 * Sanitize global conditional rules array.
	 *
	 * @param array<int, array<string, mixed>> $rules
	 * @return array<int, array<string, mixed>>
	 */
	public static function sanitize_conditional_rules( array $rules ): array {
		$clean = [];
		foreach ( $rules as $r ) {
			if ( ! is_array( $r ) || empty( $r['id'] ) || empty( $r['target_field'] ) ) {
				continue;
			}
			$clean[] = [
				'id'           => sanitize_key( (string) $r['id'] ),
				'target_field' => sanitize_key( (string) $r['target_field'] ),
				'action'       => in_array( $r['action'] ?? 'show', [ 'show', 'hide', 'require', 'optional' ], true ) ? $r['action'] : 'show',
				'match'        => in_array( $r['match'] ?? 'all', [ 'all', 'any' ], true ) ? $r['match'] : 'all',
				'rules'        => self::sanitize_field_conditions( $r )['rules'] ?? [],
			];
		}
		return $clean;
	}

	/**
	 * Safely validate a regex pattern string.
	 *
	 * @param string $pattern
	 * @return bool
	 */
	public static function is_safe_regex( string $pattern ): bool {
		if ( '' === $pattern || strlen( $pattern ) > 255 ) {
			return false;
		}

		// Prevent potentially catastrophic backtracking and dangerous patterns
		if ( preg_match( '/\(\?\{|\(\?\?/', $pattern ) ) {
			return false;
		}

		// Test compile with silence operator
		return false !== @preg_match( $pattern, '' );
	}

	/**
	 * Sanitize hex color with fallback.
	 *
	 * @param string $color
	 * @param string $fallback
	 * @return string
	 */
	public static function sanitize_hex_color( string $color, string $fallback = '#ffffff' ): string {
		$color = trim( $color );
		if ( preg_match( '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color ) ) {
			return $color;
		}
		return $fallback;
	}

	/**
	 * Sanitize CSS dimension (e.g. 16px, 1.5rem, 0).
	 *
	 * @param string $value
	 * @param string $fallback
	 * @return string
	 */
	public static function sanitize_css_dimension( string $value, string $fallback = '16px' ): string {
		$value = trim( $value );
		if ( preg_match( '/^(\d+(\.\d+)?)(px|rem|em|%|vh|vw)?$/', $value ) ) {
			return $value;
		}
		return $fallback;
	}
}
