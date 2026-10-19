<?php
/**
 * Main plugin orchestrator.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Core;

use WPCalibrate\CheckoutStudio\Admin\AdminManager;
use WPCalibrate\CheckoutStudio\Checkout\Classic\ClassicCheckoutAdapter;
use WPCalibrate\CheckoutStudio\Checkout\Blocks\BlocksCheckoutAdapter;
use WPCalibrate\CheckoutStudio\Orders\OrderDataManager;
use WPCalibrate\CheckoutStudio\Settings\SettingsRepository;
use WPCalibrate\CheckoutStudio\Privacy\PrivacyManager;
use WPCalibrate\CheckoutStudio\Compatibility\CompatibilityManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Plugin
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Settings repository.
	 *
	 * @var SettingsRepository|null
	 */
	private ?SettingsRepository $settings_repo = null;

	/**
	 * Get singleton instance.
	 *
	 * @return self
	 */
	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		// Initialize settings repository.
		$this->settings_repo = new SettingsRepository();
	}

	/**
	 * Initialize plugin components.
	 *
	 * @return void
	 */
	public function init(): void {
		// Initialize privacy handling regardless of WooCommerce presence.
		( new PrivacyManager() )->init();

		// Initialize admin interface so settings and diagnostics remain accessible.
		if ( is_admin() ) {
			( new AdminManager( $this->settings_repo ) )->init();
		}

		// If WooCommerce is not active or compatible, do not register checkout hooks.
		if ( ! DependencyChecker::is_woocommerce_compatible() ) {
			return;
		}

		// Register compatibility declarations and monitors.
		( new CompatibilityManager() )->init();

		// Check if the studio is globally enabled in settings.
		if ( ! $this->settings_repo->is_enabled() ) {
			return;
		}

		// Initialize Classic Checkout hooks.
		( new ClassicCheckoutAdapter( $this->settings_repo ) )->init();

		// Initialize Checkout Blocks hooks if supported.
		if ( DependencyChecker::is_blocks_api_supported() ) {
			( new BlocksCheckoutAdapter( $this->settings_repo ) )->init();
		}

		// Initialize Order storage, admin meta boxes, emails, and customer account display.
		( new OrderDataManager( $this->settings_repo ) )->init();
	}

	/**
	 * Get the settings repository instance.
	 *
	 * @return SettingsRepository
	 */
	public function get_settings_repository(): SettingsRepository {
		if ( null === $this->settings_repo ) {
			$this->settings_repo = new SettingsRepository();
		}
		return $this->settings_repo;
	}
}
