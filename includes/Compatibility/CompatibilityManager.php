<?php
/**
 * Compatibility manager for High-Performance Order Storage (HPOS) and Checkout Blocks.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Compatibility;

use Automattic\WooCommerce\Utilities\FeaturesUtil;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CompatibilityManager
 */
final class CompatibilityManager {

	/**
	 * Initialize compatibility declarations and monitors.
	 *
	 * @return void
	 */
	public function init(): void {
		// Declare compatibility early if before_woocommerce_init has not yet fired
		if ( class_exists( FeaturesUtil::class ) ) {
			FeaturesUtil::declare_compatibility(
				'custom_order_tables',
				WPCALIBRATE_CHECKOUT_STUDIO_FILE,
				true
			);
			FeaturesUtil::declare_compatibility(
				'cart_checkout_blocks',
				WPCALIBRATE_CHECKOUT_STUDIO_FILE,
				true
			);
		}
	}
}
