<?php
/**
 * Diagnostics and environment compatibility reporting.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Diagnostics;

use WPCalibrate\CheckoutStudio\Core\DependencyChecker;
use WPCalibrate\CheckoutStudio\Licensing\LicenseManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class DiagnosticsManager
 */
final class DiagnosticsManager {

	/**
	 * Compile full diagnostic report array.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_diagnostics(): array {
		global $wp_version;

		$wc_version = defined( 'WC_VERSION' ) ? WC_VERSION : ( DependencyChecker::is_woocommerce_active() ? 'Active (Version Unknown)' : 'Not Active' );
		$license_manager = new LicenseManager();

		// Detect checkout page configuration
		$checkout_page_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'checkout' ) : 0;
		$checkout_mode = 'Unknown';

		if ( $checkout_page_id > 0 && function_exists( 'has_block' ) ) {
			$post = get_post( $checkout_page_id );
			if ( $post ) {
				if ( has_block( 'woocommerce/checkout', $post ) ) {
					$checkout_mode = 'Checkout Blocks (Gutenberg)';
				} elseif ( has_shortcode( $post->post_content, 'woocommerce_checkout' ) ) {
					$checkout_mode = 'Classic Shortcode Checkout ([woocommerce_checkout])';
				} else {
					$checkout_mode = 'Custom Checkout Page Content';
				}
			}
		}

		return [
			'plugin_version'       => WPCALIBRATE_CHECKOUT_STUDIO_VERSION,
			'schema_version'       => get_option( 'wpcalibrate_checkout_studio_db_version', '1.0.0' ),
			'wordpress_version'    => $wp_version ?? 'Unknown',
			'php_version'          => PHP_VERSION,
			'woocommerce_version'  => $wc_version,
			'hpos_enabled'         => DependencyChecker::is_hpos_enabled() ? 'Enabled' : 'Disabled / Traditional Post Storage',
			'blocks_api_supported' => DependencyChecker::is_blocks_api_supported() ? 'Supported (Additional Checkout Fields API)' : 'Unsupported or Legacy WC',
			'checkout_page_mode'   => $checkout_mode,
			'server_software'      => sanitize_text_field( $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ),
			'memory_limit'         => ini_get( 'memory_limit' ) ?: 'Unknown',
			'license_provider'     => $license_manager->is_provider_configured() ? 'Configured' : 'Unconfigured (Awaiting Provider Documentation)',
		];
	}

	/**
	 * Generate safe, anonymized text string for copying to clipboard.
	 *
	 * @return string
	 */
	public static function get_formatted_report(): string {
		$d = self::get_diagnostics();

		$lines = [
			'### WPCalibrate Checkout Studio System Report ###',
			'Generated At: ' . gmdate( 'Y-m-d H:i:s' ) . ' UTC',
			'',
			'-- Core Environment --',
			'Plugin Version:        ' . $d['plugin_version'],
			'Schema DB Version:     ' . $d['schema_version'],
			'WordPress Version:     ' . $d['wordpress_version'],
			'PHP Version:           ' . $d['php_version'],
			'WooCommerce Version:   ' . $d['woocommerce_version'],
			'HPOS Enabled:          ' . $d['hpos_enabled'],
			'Blocks API Available:  ' . $d['blocks_api_supported'],
			'Detected Checkout Mode:' . $d['checkout_page_mode'],
			'',
			'-- Server Configuration --',
			'Server Software:       ' . $d['server_software'],
			'Memory Limit:          ' . $d['memory_limit'],
			'',
			'-- Licensing & Services --',
			'License Provider:      ' . $d['license_provider'],
			'### End System Report ###',
		];

		return implode( "\n", $lines );
	}
}
