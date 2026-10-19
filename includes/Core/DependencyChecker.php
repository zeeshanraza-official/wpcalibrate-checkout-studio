<?php
/**
 * Dependency checker for WordPress, WooCommerce, and PHP runtime.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class DependencyChecker
 */
final class DependencyChecker {

	public const MIN_PHP_VERSION = '8.2.0';
	public const MIN_WP_VERSION  = '6.5.0';
	public const MIN_WC_VERSION  = '8.8.0';

	/**
	 * Check if the PHP version requirement is satisfied.
	 *
	 * @return bool
	 */
	public static function is_php_compatible(): bool {
		return version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '>=' );
	}

	/**
	 * Check if WordPress version requirement is satisfied.
	 *
	 * @return bool
	 */
	public static function is_wp_compatible(): bool {
		global $wp_version;
		return version_compare( $wp_version ?? '0.0.0', self::MIN_WP_VERSION, '>=' );
	}

	/**
	 * Check if WooCommerce is active.
	 *
	 * @return bool
	 */
	public static function is_woocommerce_active(): bool {
		return class_exists( 'WooCommerce' ) || in_array(
			'woocommerce/woocommerce.php',
			apply_filters( 'active_plugins', get_option( 'active_plugins', [] ) ),
			true
		);
	}

	/**
	 * Check if WooCommerce meets minimum version requirement.
	 *
	 * @return bool
	 */
	public static function is_woocommerce_compatible(): bool {
		if ( ! self::is_woocommerce_active() ) {
			return false;
		}

		$wc_version = defined( 'WC_VERSION' ) ? WC_VERSION : get_option( 'woocommerce_version', '0.0.0' );
		return version_compare( (string) $wc_version, self::MIN_WC_VERSION, '>=' );
	}

	/**
	 * Check if HPOS (High-Performance Order Storage) is enabled in WooCommerce.
	 *
	 * @return bool
	 */
	public static function is_hpos_enabled(): bool {
		if ( ! class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
			return false;
		}

		if ( method_exists( '\Automattic\WooCommerce\Utilities\OrderUtil', 'custom_orders_table_usage_is_enabled' ) ) {
			return \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
		}

		return false;
	}

	/**
	 * Check if Checkout Blocks extensibility / Additional Checkout Fields API is available.
	 *
	 * @return bool
	 */
	public static function is_blocks_api_supported(): bool {
		return function_exists( 'woocommerce_register_additional_checkout_field' );
	}

	/**
	 * Check whether all runtime requirements to run the full checkout functionality are met.
	 *
	 * @return bool
	 */
	public static function can_run(): bool {
		return self::is_php_compatible() && self::is_woocommerce_compatible();
	}

	/**
	 * Render administrative notices if dependencies are missing.
	 *
	 * @return void
	 */
	public static function render_admin_notices(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		if ( ! self::is_php_compatible() ) {
			?>
			<div class="notice notice-error">
				<p>
					<strong><?php esc_html_e( 'WPCalibrate Checkout Studio for WooCommerce', 'wpcalibrate-checkout-studio' ); ?></strong>:
					<?php
					printf(
						/* translators: 1: Required PHP version, 2: Current PHP version */
						esc_html__( 'Requires PHP version %1$s or higher. Your server is running PHP version %2$s.', 'wpcalibrate-checkout-studio' ),
						esc_html( self::MIN_PHP_VERSION ),
						esc_html( PHP_VERSION )
					);
					?>
				</p>
			</div>
			<?php
			return;
		}

		if ( ! self::is_woocommerce_active() ) {
			?>
			<div class="notice notice-warning is-dismissible">
				<p>
					<strong><?php esc_html_e( 'WPCalibrate Checkout Studio for WooCommerce', 'wpcalibrate-checkout-studio' ); ?></strong>:
					<?php esc_html_e( 'Requires WooCommerce to be installed and activated.', 'wpcalibrate-checkout-studio' ); ?>
				</p>
			</div>
			<?php
			return;
		}

		if ( ! self::is_woocommerce_compatible() ) {
			$wc_version = defined( 'WC_VERSION' ) ? WC_VERSION : get_option( 'woocommerce_version', '0.0.0' );
			?>
			<div class="notice notice-warning is-dismissible">
				<p>
					<strong><?php esc_html_e( 'WPCalibrate Checkout Studio for WooCommerce', 'wpcalibrate-checkout-studio' ); ?></strong>:
					<?php
					printf(
						/* translators: 1: Required WC version, 2: Current WC version */
						esc_html__( 'Requires WooCommerce version %1$s or higher. Your store is running version %2$s.', 'wpcalibrate-checkout-studio' ),
						esc_html( self::MIN_WC_VERSION ),
						esc_html( (string) $wc_version )
					);
					?>
				</p>
			</div>
			<?php
		}
	}
}
