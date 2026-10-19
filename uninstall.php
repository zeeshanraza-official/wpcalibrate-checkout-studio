<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

// Exit if accessed directly or not uninstalling via WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Retrieve settings to check if the user explicitly opted in to data deletion.
$settings = get_option( 'wpcalibrate_checkout_studio_settings', [] );
$delete_data = ! empty( $settings['delete_on_uninstall'] );

if ( $delete_data ) {
	// Remove only this plugin's options.
	delete_option( 'wpcalibrate_checkout_studio_settings' );
	delete_option( 'wpcalibrate_checkout_studio_db_version' );
	delete_option( 'wpcalibrate_checkout_studio_license_key' );
	delete_option( 'wpcalibrate_checkout_studio_license_status' );
	delete_option( 'wpcalibrate_checkout_studio_backup' );

	// Remove plugin-specific transients.
	delete_transient( 'wpcalibrate_checkout_studio_diagnostics_cache' );

	// Note: We deliberately do NOT delete customer orders, posts, or order metadata
	// to preserve historical accounting and legal records.
}
