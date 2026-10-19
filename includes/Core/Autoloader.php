<?php
/**
 * PSR-4 compliant autoloader for WPCalibrate Checkout Studio.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoloader class.
 */
final class Autoloader {

	/**
	 * Namespace prefix.
	 *
	 * @var string
	 */
	private const PREFIX = 'WPCalibrate\\CheckoutStudio\\';

	/**
	 * Base directory path.
	 *
	 * @var string
	 */
	private static string $base_dir = '';

	/**
	 * Register autoloader with SPL.
	 *
	 * @param string $base_dir Absolute path to the includes directory.
	 * @return void
	 */
	public static function register( string $base_dir ): void {
		self::$base_dir = rtrim( $base_dir, '/\\' ) . DIRECTORY_SEPARATOR;
		spl_autoload_register( [ self::class, 'autoload' ] );
	}

	/**
	 * Autoload a class by mapping its namespace to the file system.
	 *
	 * @param string $class Fully qualified class name.
	 * @return void
	 */
	public static function autoload( string $class ): void {
		if ( ! str_starts_with( $class, self::PREFIX ) ) {
			return;
		}

		$relative_class = substr( $class, strlen( self::PREFIX ) );
		$file = self::$base_dir . str_replace( '\\', DIRECTORY_SEPARATOR, $relative_class ) . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}
