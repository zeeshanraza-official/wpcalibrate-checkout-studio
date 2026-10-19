<?php
/**
 * Fired during plugin deactivation.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Deactivator
 */
final class Deactivator {

	/**
	 * Run deactivation tasks.
	 *
	 * Note: Plugin configuration, order data, and license settings
	 * are preserved upon deactivation according to WordPress standards.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		// Clear plugin-specific transients.
		delete_transient( 'wpcalibrate_checkout_studio_diagnostics_cache' );
	}
}
