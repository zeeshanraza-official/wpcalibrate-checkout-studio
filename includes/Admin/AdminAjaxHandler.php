<?php
/**
 * AJAX Handler for administrative operations in Checkout Studio.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Admin;

use WPCalibrate\CheckoutStudio\Settings\SettingsRepository;
use WPCalibrate\CheckoutStudio\Settings\SettingsSchema;
use WPCalibrate\CheckoutStudio\ImportExport\ImportExportManager;
use WPCalibrate\CheckoutStudio\Licensing\LicenseManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AdminAjaxHandler
 */
final class AdminAjaxHandler {

	/**
	 * Settings repository.
	 *
	 * @var SettingsRepository
	 */
	private SettingsRepository $repo;

	/**
	 * Constructor.
	 *
	 * @param SettingsRepository $repo
	 */
	public function __construct( SettingsRepository $repo ) {
		$this->repo = $repo;
	}

	/**
	 * Register AJAX actions.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'wp_ajax_wpcalibrate_checkout_studio_save_fields', [ $this, 'handle_save_fields' ] );
		add_action( 'wp_ajax_wpcalibrate_checkout_studio_save_tab', [ $this, 'handle_save_tab' ] );
		add_action( 'wp_ajax_wpcalibrate_checkout_studio_reset', [ $this, 'handle_reset' ] );
		add_action( 'wp_ajax_wpcalibrate_checkout_studio_export', [ $this, 'handle_export' ] );
		add_action( 'wp_ajax_wpcalibrate_checkout_studio_import', [ $this, 'handle_import' ] );
	}

	/**
	 * Verify request capability and security nonce.
	 *
	 * @param string $capability
	 * @return void
	 */
	private function verify_security( string $capability = 'manage_woocommerce' ): void {
		if ( ! current_user_can( $capability ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'wpcalibrate-checkout-studio' ) ], 403 );
		}

		check_ajax_referer( AdminManager::NONCE_ACTION, AdminManager::NONCE_NAME );
	}

	/**
	 * Handle saving fields and sections from the Checkout Builder.
	 *
	 * @return void
	 */
	public function handle_save_fields(): void {
		$this->verify_security( 'manage_woocommerce' );

		$raw_fields   = isset( $_POST['fields'] ) ? json_decode( wp_unslash( $_POST['fields'] ), true ) : null;
		$raw_sections = isset( $_POST['sections'] ) ? json_decode( wp_unslash( $_POST['sections'] ), true ) : null;

		if ( ! is_array( $raw_fields ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid fields data.', 'wpcalibrate-checkout-studio' ) ], 400 );
		}

		// Check for duplicate keys in submitted fields.
		$keys = [];
		foreach ( $raw_fields as $f ) {
			if ( empty( $f['key'] ) ) {
				continue;
			}
			$key = sanitize_key( (string) $f['key'] );
			if ( in_array( $key, $keys, true ) ) {
				wp_send_json_error(
					[
						/* translators: %s: Field machine key */
						'message' => sprintf( __( 'Duplicate machine key "%s" detected. Every field must have a unique key.', 'wpcalibrate-checkout-studio' ), esc_html( $key ) ),
					],
					400
				);
			}
			$keys[] = $key;
		}

		$current_settings = $this->repo->get_all();
		$current_settings['fields'] = $raw_fields;
		if ( is_array( $raw_sections ) ) {
			$current_settings['sections'] = $raw_sections;
		}

		$success = $this->repo->save( $current_settings );

		if ( $success ) {
			wp_send_json_success( [ 'message' => __( 'Checkout fields saved successfully.', 'wpcalibrate-checkout-studio' ) ] );
		} else {
			wp_send_json_error( [ 'message' => __( 'Failed to save checkout fields.', 'wpcalibrate-checkout-studio' ) ], 500 );
		}
	}

	/**
	 * Handle saving specific tab settings (Appearance, Data & Display, Conditional Logic, License).
	 *
	 * @return void
	 */
	public function handle_save_tab(): void {
		$tab = isset( $_POST['tab'] ) ? sanitize_key( (string) $_POST['tab'] ) : '';

		// Sensitive operations like license require manage_options.
		$required_capability = ( 'license' === $tab ) ? 'manage_options' : 'manage_woocommerce';
		$this->verify_security( $required_capability );

		$current_settings = $this->repo->get_all();

		switch ( $tab ) {
			case 'appearance':
				$raw_appearance = isset( $_POST['appearance'] ) ? json_decode( wp_unslash( $_POST['appearance'] ), true ) : [];
				$current_settings['appearance'] = is_array( $raw_appearance ) ? $raw_appearance : [];
				break;

			case 'display':
				$raw_display = isset( $_POST['display'] ) ? json_decode( wp_unslash( $_POST['display'] ), true ) : [];
				$current_settings['display'] = is_array( $raw_display ) ? $raw_display : [];
				$current_settings['enabled'] = ! empty( $_POST['enabled'] );
				$current_settings['renderer_mode'] = isset( $_POST['renderer_mode'] ) ? sanitize_key( (string) $_POST['renderer_mode'] ) : 'auto';
				$current_settings['delete_on_uninstall'] = ! empty( $_POST['delete_on_uninstall'] );
				$current_settings['debug_logging'] = ! empty( $_POST['debug_logging'] );
				break;

			case 'conditions':
				$raw_conditions = isset( $_POST['conditional_rules'] ) ? json_decode( wp_unslash( $_POST['conditional_rules'] ), true ) : [];
				$current_settings['conditional_rules'] = is_array( $raw_conditions ) ? $raw_conditions : [];

				// Synchronize conditions directly into field definitions for instant checkout evaluation
				if ( ! empty( $current_settings['fields'] ) && is_array( $current_settings['fields'] ) ) {
					$rules_by_field = [];
					foreach ( $current_settings['conditional_rules'] as $cr ) {
						if ( ! empty( $cr['target_field'] ) ) {
							$rules_by_field[ $cr['target_field'] ] = [
								'action' => $cr['action'] ?? 'show',
								'match'  => $cr['match'] ?? 'all',
								'rules'  => $cr['rules'] ?? [],
							];
						}
					}

					foreach ( $current_settings['fields'] as &$f ) {
						if ( isset( $rules_by_field[ $f['key'] ] ) ) {
							$f['conditions'] = $rules_by_field[ $f['key'] ];
						} else {
							if ( isset( $f['conditions'] ) && ! empty( $f['conditions'] ) ) {
								$f['conditions'] = [];
							}
						}
					}
					unset( $f );
				}
				break;

			case 'license':
				$license_key = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : '';
				( new LicenseManager() )->save_license_key( $license_key );
				wp_send_json_success( [ 'message' => __( 'License key updated.', 'wpcalibrate-checkout-studio' ) ] );
				return;

			default:
				wp_send_json_error( [ 'message' => __( 'Unknown settings tab.', 'wpcalibrate-checkout-studio' ) ], 400 );
		}

		$success = $this->repo->save( $current_settings );

		if ( $success ) {
			wp_send_json_success( [ 'message' => __( 'Settings updated successfully.', 'wpcalibrate-checkout-studio' ) ] );
		} else {
			wp_send_json_error( [ 'message' => __( 'Failed to update settings.', 'wpcalibrate-checkout-studio' ) ], 500 );
		}
	}

	/**
	 * Handle resetting settings back to defaults.
	 *
	 * @return void
	 */
	public function handle_reset(): void {
		$this->verify_security( 'manage_options' );

		$success = $this->repo->reset_to_defaults();

		if ( $success ) {
			wp_send_json_success( [ 'message' => __( 'Checkout Studio settings restored to default. A backup of previous settings was created.', 'wpcalibrate-checkout-studio' ) ] );
		} else {
			wp_send_json_error( [ 'message' => __( 'Failed to reset settings.', 'wpcalibrate-checkout-studio' ) ], 500 );
		}
	}

	/**
	 * Handle exporting configuration JSON file.
	 *
	 * @return void
	 */
	public function handle_export(): void {
		$this->verify_security( 'manage_woocommerce' );

		$impexp = new ImportExportManager( $this->repo );
		$json   = $impexp->export();

		$filename = 'wpcalibrate-checkout-studio-export-' . gmdate( 'Y-m-d' ) . '.json';

		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Handle importing configuration JSON file.
	 *
	 * @return void
	 */
	public function handle_import(): void {
		$this->verify_security( 'manage_options' );

		if ( empty( $_FILES['import_file']['tmp_name'] ) ) {
			wp_send_json_error( [ 'message' => __( 'No file was uploaded.', 'wpcalibrate-checkout-studio' ) ], 400 );
		}

		// Verify file size limit (max 2MB)
		if ( (int) $_FILES['import_file']['size'] > 2 * 1024 * 1024 ) {
			wp_send_json_error( [ 'message' => __( 'File size exceeds maximum allowed limit (2MB).', 'wpcalibrate-checkout-studio' ) ], 400 );
		}

		$file_content = file_get_contents( (string) $_FILES['import_file']['tmp_name'] );
		if ( false === $file_content ) {
			wp_send_json_error( [ 'message' => __( 'Unable to read uploaded file.', 'wpcalibrate-checkout-studio' ) ], 400 );
		}

		$decoded = json_decode( $file_content, true );
		$impexp  = new ImportExportManager( $this->repo );
		$result  = $impexp->validate( $decoded );

		if ( ! $result['valid'] ) {
			wp_send_json_error( [ 'message' => $result['message'] ], 400 );
		}

		$imported = $impexp->import( $decoded );

		if ( $imported ) {
			wp_send_json_success( [ 'message' => __( 'Configuration imported successfully. A backup of the previous configuration was saved.', 'wpcalibrate-checkout-studio' ) ] );
		} else {
			wp_send_json_error( [ 'message' => __( 'Failed to import configuration.', 'wpcalibrate-checkout-studio' ) ], 500 );
		}
	}
}
