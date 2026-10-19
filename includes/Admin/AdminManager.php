<?php
/**
 * Admin menu and screen manager for WPCalibrate Checkout Studio.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Admin;

use WPCalibrate\CheckoutStudio\Settings\SettingsRepository;
use WPCalibrate\CheckoutStudio\Licensing\LicenseManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AdminManager
 */
final class AdminManager {

	public const MENU_SLUG = 'wpcalibrate-checkout-studio';
	public const PARENT_SLUG = 'wpcalibrate';
	public const NONCE_ACTION = 'wpcalibrate_checkout_studio_admin_action';
	public const NONCE_NAME = 'wcs_nonce';

	/**
	 * Settings repository.
	 *
	 * @var SettingsRepository
	 */
	private SettingsRepository $repo;

	/**
	 * Screen hook suffix.
	 *
	 * @var string
	 */
	private string $hook_suffix = '';

	/**
	 * Constructor.
	 *
	 * @param SettingsRepository $repo
	 */
	public function __construct( SettingsRepository $repo ) {
		$this->repo = $repo;
	}

	/**
	 * Initialize admin hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'admin_menu', [ $this, 'register_admin_menu' ], 20 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
		add_action( 'admin_head', [ $this, 'render_admin_menu_styles' ] );

		// Register AJAX handler.
		( new AdminAjaxHandler( $this->repo ) )->init();
	}

	/**
	 * Register WPCalibrate parent menu and Checkout Studio submenu.
	 *
	 * @return void
	 */
	public function register_admin_menu(): void {
		global $admin_page_hooks;

		$capability = 'manage_woocommerce';
		$icon_url   = WPCALIBRATE_CHECKOUT_STUDIO_URL . 'branding/icon-white.png';

		// If the shared WPCalibrate top-level menu does not exist, create it.
		if ( ! isset( $admin_page_hooks[ self::PARENT_SLUG ] ) ) {
			add_menu_page(
				__( 'WPCalibrate', 'wpcalibrate-checkout-studio' ),
				__( 'WPCalibrate', 'wpcalibrate-checkout-studio' ),
				$capability,
				self::PARENT_SLUG,
				[ $this, 'render_main_page' ],
				$icon_url,
				58
			);

			// Rename first submenu item so it doesn't say "WPCalibrate".
			add_submenu_page(
				self::PARENT_SLUG,
				__( 'Checkout Studio', 'wpcalibrate-checkout-studio' ),
				__( 'Checkout Studio', 'wpcalibrate-checkout-studio' ),
				$capability,
				self::MENU_SLUG,
				[ $this, 'render_main_page' ]
			);

			// Remove duplicate top-level submenu entry.
			remove_submenu_page( self::PARENT_SLUG, self::PARENT_SLUG );
		} else {
			// Parent menu exists from a sibling plugin; add Checkout Studio submenu.
			$this->hook_suffix = (string) add_submenu_page(
				self::PARENT_SLUG,
				__( 'Checkout Studio', 'wpcalibrate-checkout-studio' ),
				__( 'Checkout Studio', 'wpcalibrate-checkout-studio' ),
				$capability,
				self::MENU_SLUG,
				[ $this, 'render_main_page' ]
			);
		}
	}

	/**
	 * Enqueue admin scripts and styles strictly on Checkout Studio screen.
	 *
	 * @param string $hook
	 * @return void
	 */
	public function enqueue_admin_assets( string $hook ): void {
		// Only load assets on our dedicated plugin page.
		if ( false === strpos( $hook, self::MENU_SLUG ) ) {
			return;
		}

		// Enqueue admin styles.
		wp_enqueue_style(
			'wpcalibrate-checkout-studio-admin',
			WPCALIBRATE_CHECKOUT_STUDIO_URL . 'assets/css/admin.css',
			[],
			WPCALIBRATE_CHECKOUT_STUDIO_VERSION
		);

		// Enqueue admin script.
		wp_enqueue_script(
			'wpcalibrate-checkout-studio-admin',
			WPCALIBRATE_CHECKOUT_STUDIO_URL . 'assets/js/admin.js',
			[ 'jquery', 'wp-i18n' ],
			WPCALIBRATE_CHECKOUT_STUDIO_VERSION,
			true
		);

		// Localize script with configuration, strings, and nonces.
		wp_localize_script(
			'wpcalibrate-checkout-studio-admin',
			'wcsAdminData',
			[
				'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
				'nonce'             => wp_create_nonce( self::NONCE_ACTION ),
				'fields'            => $this->repo->get_fields(),
				'sections'          => $this->repo->get_sections(),
				'conditional_rules' => $this->repo->get_conditional_rules(),
				'i18n'              => [
					'confirmDelete'     => __( 'Are you sure you want to delete this custom field?', 'wpcalibrate-checkout-studio' ),
					'confirmDeleteRule' => __( 'Are you sure you want to delete this conditional rule?', 'wpcalibrate-checkout-studio' ),
					'confirmReset'      => __( 'Are you sure you want to reset all checkout studio settings to default? A backup will be created.', 'wpcalibrate-checkout-studio' ),
					'saving'            => __( 'Saving changes...', 'wpcalibrate-checkout-studio' ),
					'saved'             => __( 'Settings saved successfully.', 'wpcalibrate-checkout-studio' ),
					'error'             => __( 'An error occurred while saving. Please try again.', 'wpcalibrate-checkout-studio' ),
					'duplicateKey'      => __( 'A field with this machine key already exists.', 'wpcalibrate-checkout-studio' ),
					'keyRequired'       => __( 'Machine key is required and must only contain lowercase letters, numbers, and underscores.', 'wpcalibrate-checkout-studio' ),
					'copied'            => __( 'Diagnostic report copied to clipboard!', 'wpcalibrate-checkout-studio' ),
				],
			]
		);
	}

	/**
	 * Output scoped CSS in admin head to ensure the WPCalibrate menu icon is sized
	 * exactly like native WordPress dashboard icons (20x20px) and rendered in pure white.
	 *
	 * @return void
	 */
	public function render_admin_menu_styles(): void {
		?>
		<style id="wpcalibrate-menu-icon-styles">
			#adminmenu .toplevel_page_wpcalibrate .wp-menu-image img,
			#adminmenu #toplevel_page_wpcalibrate .wp-menu-image img,
			#adminmenu a[href*="wpcalibrate"] .wp-menu-image img {
				max-width: 20px !important;
				max-height: 20px !important;
				width: 20px !important;
				height: 20px !important;
				padding: 7px 0 0 0 !important;
				object-fit: contain !important;
				box-sizing: content-box !important;
				filter: brightness(0) invert(1) !important;
				opacity: 0.8 !important;
				transition: opacity 0.15s ease-in-out;
			}
			#adminmenu .toplevel_page_wpcalibrate:hover .wp-menu-image img,
			#adminmenu .toplevel_page_wpcalibrate.wp-has-current-submenu .wp-menu-image img,
			#adminmenu .toplevel_page_wpcalibrate.current .wp-menu-image img,
			#adminmenu #toplevel_page_wpcalibrate.wp-menu-open .wp-menu-image img {
				opacity: 1 !important;
				filter: brightness(0) invert(1) !important;
			}
		</style>
		<?php
	}

	/**
	 * Render the main plugin settings page.
	 *
	 * @return void
	 */
	public function render_main_page(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wpcalibrate-checkout-studio' ) );
		}

		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( (string) $_GET['tab'] ) : 'overview';
		$valid_tabs = [ 'overview', 'builder', 'conditions', 'appearance', 'display', 'import_export', 'diagnostics', 'license', 'support' ];

		if ( ! in_array( $active_tab, $valid_tabs, true ) ) {
			$active_tab = 'overview';
		}

		$renderer = new AdminViewRenderer( $this->repo, $active_tab );
		$renderer->render();
	}
}
