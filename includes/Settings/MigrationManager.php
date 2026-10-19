<?php
/**
 * Migration and version management.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MigrationManager
 */
final class MigrationManager {

	public const DB_VERSION_OPTION = 'wpcalibrate_checkout_studio_db_version';

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
	 * Run any pending migrations if required.
	 *
	 * @return void
	 */
	public function check_and_migrate(): void {
		$installed_version = (string) get_option( self::DB_VERSION_OPTION, '0.0.0' );

		if ( version_compare( $installed_version, WPCALIBRATE_CHECKOUT_STUDIO_VERSION, '>=' ) ) {
			return;
		}

		// Perform backup before running migrations.
		$this->repo->create_backup();

		// Run migrations sequentially if needed.
		$success = $this->run_migrations( $installed_version );

		if ( $success ) {
			update_option( self::DB_VERSION_OPTION, WPCALIBRATE_CHECKOUT_STUDIO_VERSION, 'no' );
		}
	}

	/**
	 * Run version-specific migration steps.
	 *
	 * @param string $from_version
	 * @return bool
	 */
	private function run_migrations( string $from_version ): bool {
		// Version 1.0.0 initial normalization
		if ( version_compare( $from_version, '1.0.0', '<' ) ) {
			$current = $this->repo->get_all();
			$this->repo->save( $current );
		}

		return true;
	}
}
