<?php
/**
 * Appearance and dynamic CSS generator for Checkout Studio.
 * Generates scoped CSS for checkout fields, labels, inputs, and buttons.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Checkout;

use WPCalibrate\CheckoutStudio\Settings\SettingsRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AppearanceEngine
 */
final class AppearanceEngine {

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
	 * Generate scoped CSS rules based on appearance settings.
	 *
	 * @return string Scoped CSS string.
	 */
	public function generate_css(): string {
		$app = $this->repo->get_appearance();
		if ( empty( $app['enabled'] ) ) {
			return '';
		}

		$field_spacing   = esc_attr( $app['field_spacing'] ?? '16px' );
		$border_radius   = esc_attr( $app['input_border_radius'] ?? '4px' );
		$border_color    = esc_attr( $app['input_border_color'] ?? '#d1d5db' );
		$bg_color        = esc_attr( $app['input_bg_color'] ?? '#ffffff' );
		$text_color      = esc_attr( $app['input_text_color'] ?? '#1f2937' );
		$focus_border    = esc_attr( $app['input_focus_border'] ?? '#2563eb' );
		$label_color     = esc_attr( $app['label_text_color'] ?? '#374151' );
		$button_bg       = esc_attr( $app['button_bg_color'] ?? '#2563eb' );
		$button_text     = esc_attr( $app['button_text_color'] ?? '#ffffff' );

		$css = "
		/* WPCalibrate Checkout Studio Scoped Appearance */
		form.woocommerce-checkout .form-row {
			margin-bottom: {$field_spacing};
		}
		form.woocommerce-checkout .form-row label {
			color: {$label_color};
			font-weight: 600;
		}
		form.woocommerce-checkout input.input-text,
		form.woocommerce-checkout textarea,
		form.woocommerce-checkout select {
			border-radius: {$border_radius};
			border: 1px solid {$border_color};
			background-color: {$bg_color};
			color: {$text_color};
			padding: 8px 12px;
		}
		form.woocommerce-checkout input.input-text:focus,
		form.woocommerce-checkout textarea:focus,
		form.woocommerce-checkout select:focus {
			border-color: {$focus_border};
			outline: 2px solid rgba(37, 99, 235, 0.2);
		}
		form.woocommerce-checkout #place_order {
			background-color: {$button_bg};
			color: {$button_text};
			border-radius: {$border_radius};
			border: none;
			padding: 12px 24px;
			font-weight: 600;
			cursor: pointer;
		}
		form.woocommerce-checkout #place_order:hover {
			filter: brightness(0.92);
		}
		";

		return $css;
	}
}
