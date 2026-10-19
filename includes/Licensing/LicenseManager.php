<?php
/**
 * License manager handling key persistence, masking, and provider queries.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Licensing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LicenseManager
 */
final class LicenseManager {

	public const LICENSE_KEY_OPTION = 'wpcalibrate_checkout_studio_license_key';

	/**
	 * Provider instance.
	 *
	 * @var LicenseProviderInterface
	 */
	private LicenseProviderInterface $provider;

	/**
	 * Constructor.
	 *
	 * @param LicenseProviderInterface|null $provider
	 */
	public function __construct( ?LicenseProviderInterface $provider = null ) {
		$this->provider = $provider ?? new NullLicenseProvider();
	}

	/**
	 * Retrieve the raw stored license key.
	 *
	 * @return string
	 */
	public function get_license_key(): string {
		return (string) get_option( self::LICENSE_KEY_OPTION, '' );
	}

	/**
	 * Retrieve a masked version of the license key for display.
	 *
	 * @return string
	 */
	public function get_masked_key(): string {
		$key = $this->get_license_key();
		if ( empty( $key ) ) {
			return '';
		}

		$length = strlen( $key );
		if ( $length <= 4 ) {
			return str_repeat( '•', $length );
		}

		$visible_suffix = substr( $key, -4 );
		return str_repeat( '•', max( 8, $length - 4 ) ) . $visible_suffix;
	}

	/**
	 * Save a new license key securely.
	 *
	 * @param string $key
	 * @return bool
	 */
	public function save_license_key( string $key ): bool {
		$clean_key = sanitize_text_field( trim( $key ) );

		// Do not overwrite with empty or masked characters if user didn't change it.
		if ( str_contains( $clean_key, '•' ) ) {
			return true;
		}

		return update_option( self::LICENSE_KEY_OPTION, $clean_key, 'no' );
	}

	/**
	 * Get current provider status details.
	 *
	 * @return array<string, mixed>
	 */
	public function get_status(): array {
		$key = $this->get_license_key();
		return $this->provider->get_status( $key );
	}

	/**
	 * Check if a real licensing provider is configured.
	 *
	 * @return bool
	 */
	public function is_provider_configured(): bool {
		return $this->provider->is_configured();
	}
}
