<?php
/**
 * Null license provider when no remote licensing provider is configured.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Licensing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NullLicenseProvider
 */
final class NullLicenseProvider implements LicenseProviderInterface {

	public function get_name(): string {
		return 'Unconfigured Provider';
	}

	public function is_configured(): bool {
		return false;
	}

	public function get_status( string $license_key ): array {
		return [
			'status'  => 'unconfigured',
			'valid'   => false,
			'message' => __( 'License provider not configured.', 'wpcalibrate-checkout-studio' ),
		];
	}

	public function activate( string $license_key ): array {
		return [
			'success' => false,
			'status'  => 'unconfigured',
			'message' => __( 'License provider not configured. Remote activation is unavailable.', 'wpcalibrate-checkout-studio' ),
		];
	}

	public function deactivate( string $license_key ): array {
		return [
			'success' => false,
			'status'  => 'unconfigured',
			'message' => __( 'License provider not configured.', 'wpcalibrate-checkout-studio' ),
		];
	}
}
