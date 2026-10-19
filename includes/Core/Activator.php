<?php
/**
 * Fired during plugin activation.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Activator
 */
final class Activator {

	/**
	 * Run activation tasks.
	 *
	 * @return void
	 */
	public static function activate(): void {
		if ( ! DependencyChecker::is_php_compatible() ) {
			deactivate_plugins( plugin_basename( WPCALIBRATE_CHECKOUT_STUDIO_FILE ) );
			wp_die(
				sprintf(
					/* translators: 1: Required PHP version, 2: Current PHP version */
					esc_html__( 'WPCalibrate Checkout Studio for WooCommerce requires PHP %1$s or higher. Your server is running PHP %2$s.', 'wpcalibrate-checkout-studio' ),
					esc_html( DependencyChecker::MIN_PHP_VERSION ),
					esc_html( PHP_VERSION )
				)
			);
		}

		// Initialize default settings if not already present.
		$existing_settings = get_option( 'wpcalibrate_checkout_studio_settings', null );
		if ( null === $existing_settings ) {
			add_option(
				'wpcalibrate_checkout_studio_settings',
				self::get_default_settings(),
				'',
				'no'
			);
		}

		// Initialize schema version if not set.
		$current_db_version = get_option( 'wpcalibrate_checkout_studio_db_version', null );
		if ( null === $current_db_version ) {
			add_option(
				'wpcalibrate_checkout_studio_db_version',
				WPCALIBRATE_CHECKOUT_STUDIO_VERSION,
				'',
				'no'
			);
		}
	}

	/**
	 * Default plugin settings schema.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_default_settings(): array {
		return [
			'schema_version'     => WPCALIBRATE_CHECKOUT_STUDIO_VERSION,
			'enabled'            => true,
			'renderer_mode'      => 'auto', // auto, classic, blocks
			'delete_on_uninstall'=> false,
			'debug_logging'      => false,
			'display'            => [
				'show_in_order_admin'  => true,
				'show_in_customer_view'=> true,
				'show_in_emails'       => true,
				'email_location'       => 'order_meta', // order_meta or customer_details
			],
			'appearance'         => [
				'enabled'              => false,
				'field_spacing'        => '16px',
				'input_border_radius'  => '4px',
				'input_border_width'   => '1px',
				'input_border_style'   => 'solid',
				'input_border_color'   => '#d1d5db',
				'input_bg_color'       => '#ffffff',
				'input_text_color'     => '#1f2937',
				'input_focus_border'   => '#2563eb',
				'label_font_size'      => '14px',
				'label_font_weight'    => '600',
				'label_text_color'     => '#374151',
				'section_spacing'      => '24px',
				'button_bg_color'      => '#2563eb',
				'button_text_color'    => '#ffffff',
				'button_border_radius' => '4px',
			],
			'sections'           => [],
			'fields'             => [],
			'conditional_rules'  => [],
		];
	}
}
