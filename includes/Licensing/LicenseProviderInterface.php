<?php
/**
 * Interface for license providers.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Licensing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface LicenseProviderInterface
 */
interface LicenseProviderInterface {

	/**
	 * Get the provider name.
	 *
	 * @return string
	 */
	public function get_name(): string;

	/**
	 * Check if provider is configured with valid endpoints and credentials.
	 *
	 * @return bool
	 */
	public function is_configured(): bool;

	/**
	 * Check the current license status.
	 *
	 * @param string $license_key
	 * @return array<string, mixed>
	 */
	public function get_status( string $license_key ): array;

	/**
	 * Attempt license activation.
	 *
	 * @param string $license_key
	 * @return array<string, mixed>
	 */
	public function activate( string $license_key ): array;

	/**
	 * Attempt license deactivation.
	 *
	 * @param string $license_key
	 * @return array<string, mixed>
	 */
	public function deactivate( string $license_key ): array;
}
