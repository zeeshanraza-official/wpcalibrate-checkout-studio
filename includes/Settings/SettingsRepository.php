<?php
/**
 * Settings repository managing persistence, memory caching, and query access.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SettingsRepository
 */
final class SettingsRepository {

	public const OPTION_KEY = 'wpcalibrate_checkout_studio_settings';
	public const BACKUP_OPTION_KEY = 'wpcalibrate_checkout_studio_backup';

	/**
	 * In-memory cache of normalized settings.
	 *
	 * @var array<string, mixed>|null
	 */
	private ?array $cache = null;

	/**
	 * Get all settings normalized.
	 *
	 * @return array<string, mixed>
	 */
	public function get_all(): array {
		if ( null !== $this->cache ) {
			return $this->cache;
		}

		$raw = get_option( self::OPTION_KEY, [] );
		if ( ! is_array( $raw ) || empty( $raw ) ) {
			$raw = [
				'sections' => SettingsSchema::get_default_sections(),
				'fields'   => SettingsSchema::get_default_fields(),
			];
		}

		$this->cache = SettingsSchema::sanitize( $raw );
		return $this->cache;
	}

	/**
	 * Save updated settings.
	 *
	 * @param array<string, mixed> $settings Raw settings payload.
	 * @return bool True on success, false on failure.
	 */
	public function save( array $settings ): bool {
		$sanitized = SettingsSchema::sanitize( $settings );

		// Clear memory cache before updating option.
		$this->cache = $sanitized;

		$updated = update_option( self::OPTION_KEY, $sanitized, 'no' );

		/**
		 * Action triggered after WPCalibrate Checkout Studio settings are saved.
		 *
		 * @param array<string, mixed> $sanitized Sanitized settings array.
		 */
		do_action( 'wpcalibrate_checkout_studio_settings_updated', $sanitized );

		return $updated || get_option( self::OPTION_KEY ) === $sanitized;
	}

	/**
	 * Check if the checkout studio features are globally enabled.
	 *
	 * @return bool
	 */
	public function is_enabled(): bool {
		$settings = $this->get_all();
		return ! empty( $settings['enabled'] );
	}

	/**
	 * Get configured checkout renderer mode (auto, classic, blocks).
	 *
	 * @return string
	 */
	public function get_renderer_mode(): string {
		$settings = $this->get_all();
		return (string) ( $settings['renderer_mode'] ?? 'auto' );
	}

	/**
	 * Get all configured sections ordered by priority.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_sections(): array {
		$settings = $this->get_all();
		$sections = $settings['sections'] ?? [];

		usort( $sections, static function ( array $a, array $b ): int {
			return ( (int) ( $a['priority'] ?? 10 ) ) <=> ( (int) ( $b['priority'] ?? 10 ) );
		} );

		return $sections;
	}

	/**
	 * Get all fields ordered by priority.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_fields(): array {
		$settings = $this->get_all();
		$fields   = $settings['fields'] ?? [];

		usort( $fields, static function ( array $a, array $b ): int {
			return ( (int) ( $a['priority'] ?? 10 ) ) <=> ( (int) ( $b['priority'] ?? 10 ) );
		} );

		return $fields;
	}

	/**
	 * Get a specific field definition by machine key.
	 *
	 * @param string $key Machine key.
	 * @return array<string, mixed>|null
	 */
	public function get_field( string $key ): ?array {
		foreach ( $this->get_fields() as $field ) {
			if ( ( $field['key'] ?? '' ) === $key ) {
				return $field;
			}
		}
		return null;
	}

	/**
	 * Get all active fields for a given section ordered by priority.
	 *
	 * @param string $section_key Section key (e.g. billing, shipping, order).
	 * @return array<int, array<string, mixed>>
	 */
	public function get_fields_by_section( string $section_key ): array {
		$section_fields = [];
		foreach ( $this->get_fields() as $field ) {
			if ( ( $field['section'] ?? '' ) === $section_key ) {
				$section_fields[] = $field;
			}
		}
		return $section_fields;
	}

	/**
	 * Get appearance settings.
	 *
	 * @return array<string, mixed>
	 */
	public function get_appearance(): array {
		$settings = $this->get_all();
		return $settings['appearance'] ?? [];
	}

	/**
	 * Get display settings.
	 *
	 * @return array<string, mixed>
	 */
	public function get_display(): array {
		$settings = $this->get_all();
		return $settings['display'] ?? [];
	}

	/**
	 * Get conditional rules.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_conditional_rules(): array {
		$settings = $this->get_all();
		return $settings['conditional_rules'] ?? [];
	}

	/**
	 * Create a backup of the current settings in the database.
	 *
	 * @return bool
	 */
	public function create_backup(): bool {
		$current = get_option( self::OPTION_KEY, null );
		if ( empty( $current ) || ! is_array( $current ) ) {
			$current = $this->get_all();
		}

		$backup_payload = [
			'timestamp' => time(),
			'version'   => WPCALIBRATE_CHECKOUT_STUDIO_VERSION,
			'data'      => $current,
		];

		return update_option( self::BACKUP_OPTION_KEY, $backup_payload, 'no' );
	}

	/**
	 * Restore the backup settings.
	 *
	 * @return bool
	 */
	public function restore_backup(): bool {
		$backup = get_option( self::BACKUP_OPTION_KEY, null );
		if ( ! is_array( $backup ) || empty( $backup['data'] ) ) {
			return false;
		}

		return $this->save( $backup['data'] );
	}

	/**
	 * Reset all settings back to default.
	 *
	 * @return bool
	 */
	public function reset_to_defaults(): bool {
		// First create a backup of current settings.
		$this->create_backup();

		$defaults = [
			'schema_version'     => SettingsSchema::CURRENT_SCHEMA_VERSION,
			'enabled'            => true,
			'renderer_mode'      => 'auto',
			'delete_on_uninstall'=> false,
			'debug_logging'      => false,
			'display'            => [
				'show_in_order_admin'   => true,
				'show_in_customer_view' => true,
				'show_in_emails'        => true,
				'email_location'        => 'order_meta',
			],
			'appearance'         => [
				'enabled'              => false,
				'field_spacing'        => '16px',
				'input_border_radius'  => '4px',
				'input_border_width'   => '1px',
				'input_border_style'   => 'solid',
				'input_border_color'   => '#d1d5db',
				'input_bg_color'       => '#ffffff',
				'input_text_color'     => '#1f2937',
				'input_focus_border'   => '#2563eb',
				'label_font_size'      => '14px',
				'label_font_weight'    => '600',
				'label_text_color'     => '#374151',
				'section_spacing'      => '24px',
				'button_bg_color'      => '#2563eb',
				'button_text_color'    => '#ffffff',
				'button_border_radius' => '4px',
			],
			'sections'           => SettingsSchema::get_default_sections(),
			'fields'             => SettingsSchema::get_default_fields(),
			'conditional_rules'  => [],
		];

		return $this->save( $defaults );
	}
}
