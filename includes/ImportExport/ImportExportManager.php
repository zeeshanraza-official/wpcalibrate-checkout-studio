<?php
/**
 * Import and Export manager for configuration payloads.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\ImportExport;

use WPCalibrate\CheckoutStudio\Settings\SettingsRepository;
use WPCalibrate\CheckoutStudio\Settings\SettingsSchema;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ImportExportManager
 */
final class ImportExportManager {

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
	 * Generate clean export JSON containing only settings configuration.
	 * Explicitly excludes license keys, secrets, and order/customer data.
	 *
	 * @return string
	 */
	public function export(): string {
		$all_settings = $this->repo->get_all();

		$payload = [
			'generator'      => 'WPCalibrate Checkout Studio for WooCommerce',
			'slug'           => 'wpcalibrate-checkout-studio',
			'schema_version' => SettingsSchema::CURRENT_SCHEMA_VERSION,
			'exported_at'    => gmdate( 'c' ),
			'configuration'  => [
				'enabled'           => $all_settings['enabled'] ?? true,
				'renderer_mode'     => $all_settings['renderer_mode'] ?? 'auto',
				'display'           => $all_settings['display'] ?? [],
				'appearance'        => $all_settings['appearance'] ?? [],
				'sections'          => $all_settings['sections'] ?? [],
				'fields'            => $all_settings['fields'] ?? [],
				'conditional_rules' => $all_settings['conditional_rules'] ?? [],
			],
		];

		return (string) wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	}

	/**
	 * Validate an imported JSON payload.
	 *
	 * @param mixed $decoded_json
	 * @return array<string, mixed> Result array with 'valid' (bool) and 'message' (string).
	 */
	public function validate( mixed $decoded_json ): array {
		if ( ! is_array( $decoded_json ) ) {
			return [
				'valid'   => false,
				'message' => __( 'Invalid JSON file or unparseable content.', 'wpcalibrate-checkout-studio' ),
			];
		}

		if ( empty( $decoded_json['generator'] ) || empty( $decoded_json['configuration'] ) || ! is_array( $decoded_json['configuration'] ) ) {
			return [
				'valid'   => false,
				'message' => __( 'File is not a recognized WPCalibrate Checkout Studio configuration file.', 'wpcalibrate-checkout-studio' ),
			];
		}

		return [
			'valid'   => true,
			'message' => __( 'Configuration file structure is valid.', 'wpcalibrate-checkout-studio' ),
		];
	}

	/**
	 * Import and apply configuration payload safely.
	 *
	 * @param array<string, mixed> $decoded_json
	 * @return bool
	 */
	public function import( array $decoded_json ): bool {
		$validation = $this->validate( $decoded_json );
		if ( ! $validation['valid'] ) {
			return false;
		}

		// Always create a backup before applying import.
		$this->repo->create_backup();

		$config = $decoded_json['configuration'];
		$sanitized = SettingsSchema::sanitize( $config );

		return $this->repo->save( $sanitized );
	}
}
