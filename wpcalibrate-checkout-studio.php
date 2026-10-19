<?php
/**
 * Plugin Name:       WPCalibrate Checkout Studio for WooCommerce
 * Plugin URI:        https://marketplace.wpcalibrate.com/
 * Description:       A visual studio to customize WooCommerce checkout fields, sections, conditional visibility rules, layout, and appearance.
 * Version:           1.0.3
 * Requires at least: 6.5
 * Requires PHP:      8.2
 * Tested up to:      7.1.3
 * WC requires at least: 8.8.0
 * WC tested up to:   11.2.1
 * Author:            WPCalibrate
 * Author URI:        https://wpcalibrate.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wpcalibrate-checkout-studio
 * Domain Path:       /languages
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define core constants.
define( 'WPCALIBRATE_CHECKOUT_STUDIO_VERSION', '1.0.3' );
define( 'WPCALIBRATE_CHECKOUT_STUDIO_FILE', __FILE__ );
define( 'WPCALIBRATE_CHECKOUT_STUDIO_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPCALIBRATE_CHECKOUT_STUDIO_URL', plugin_dir_url( __FILE__ ) );
define( 'WPCALIBRATE_CHECKOUT_STUDIO_BASENAME', plugin_basename( __FILE__ ) );

// Autoload classes.
require_once WPCALIBRATE_CHECKOUT_STUDIO_DIR . 'includes/Core/Autoloader.php';
Core\Autoloader::register( WPCALIBRATE_CHECKOUT_STUDIO_DIR . 'includes' );

// Register activation and deactivation hooks.
register_activation_hook( __FILE__, [ Core\Activator::class, 'activate' ] );
register_deactivation_hook( __FILE__, [ Core\Deactivator::class, 'deactivate' ] );

// Declare HPOS and Blocks compatibility early on before_woocommerce_init.
add_action( 'before_woocommerce_init', static function (): void {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables',
			WPCALIBRATE_CHECKOUT_STUDIO_FILE,
			true
		);
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'cart_checkout_blocks',
			WPCALIBRATE_CHECKOUT_STUDIO_FILE,
			true
		);
	}
} );

// Initialize the plugin.
add_action( 'plugins_loaded', static function (): void {
	// Always register text domain.
	load_plugin_textdomain(
		'wpcalibrate-checkout-studio',
		false,
		dirname( WPCALIBRATE_CHECKOUT_STUDIO_BASENAME ) . '/languages'
	);

	// Check dependencies and notify admin if necessary.
	if ( is_admin() ) {
		add_action( 'admin_notices', [ Core\DependencyChecker::class, 'render_admin_notices' ] );
	}

	// Bootstrap central plugin instance.
	Core\Plugin::get_instance()->init();
} );
