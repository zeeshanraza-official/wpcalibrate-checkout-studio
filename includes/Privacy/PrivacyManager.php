<?php
/**
 * Privacy and Personal Data management (WordPress GDPR Exporter & Eraser integration).
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Privacy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PrivacyManager
 */
final class PrivacyManager {

	/**
	 * Initialize privacy hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_filter( 'wp_privacy_personal_data_exporters', [ $this, 'register_exporter' ] );
		add_filter( 'wp_privacy_personal_data_erasers', [ $this, 'register_eraser' ] );
		add_action( 'admin_init', [ $this, 'add_privacy_policy_content' ] );
	}

	/**
	 * Register personal data exporter.
	 *
	 * @param array<string, array<string, mixed>> $exporters
	 * @return array<string, array<string, mixed>>
	 */
	public function register_exporter( array $exporters ): array {
		$exporters['wpcalibrate-checkout-studio'] = [
			'exporter_friendly_name' => __( 'WPCalibrate Checkout Studio Custom Fields', 'wpcalibrate-checkout-studio' ),
			'callback'               => [ $this, 'export_customer_data' ],
		];
		return $exporters;
	}

	/**
	 * Register personal data eraser.
	 *
	 * @param array<string, array<string, mixed>> $erasers
	 * @return array<string, array<string, mixed>>
	 */
	public function register_eraser( array $erasers ): array {
		$erasers['wpcalibrate-checkout-studio'] = [
			'eraser_friendly_name' => __( 'WPCalibrate Checkout Studio Custom Fields', 'wpcalibrate-checkout-studio' ),
			'callback'             => [ $this, 'erase_customer_data' ],
		];
		return $erasers;
	}

	/**
	 * Export customer's custom checkout field data.
	 *
	 * @param string $email_address
	 * @param int    $page
	 * @return array<string, mixed>
	 */
	public function export_customer_data( string $email_address, int $page = 1 ): array {
		$data_to_export = [];

		if ( ! function_exists( 'wc_get_orders' ) ) {
			return [
				'data' => [],
				'done' => true,
			];
		}

		$orders = wc_get_orders(
			[
				'billing_email' => $email_address,
				'limit'         => 20,
				'page'          => $page,
			]
		);

		foreach ( $orders as $order ) {
			$item_id     = 'wcs-order-' . $order->get_id();
			$group_id    = 'wpcalibrate_checkout_fields';
			$group_label = __( 'Checkout Studio Custom Fields', 'wpcalibrate-checkout-studio' );

			$order_data = [];
			foreach ( $order->get_meta_data() as $meta ) {
				$key = (string) $meta->key;
				if ( str_starts_with( $key, '_wpcalibrate_' ) && ! str_starts_with( $key, '_wpcalibrate_label_' ) ) {
					$field_key  = str_replace( '_wpcalibrate_', '', $key );
					$label_meta = $order->get_meta( '_wpcalibrate_label_' . $field_key );
					$label      = is_array( $label_meta ) && ! empty( $label_meta['label'] ) ? (string) $label_meta['label'] : $field_key;

					$order_data[] = [
						'name'  => $label,
						'value' => is_array( $meta->value ) ? implode( ', ', $meta->value ) : (string) $meta->value,
					];
				}
			}

			if ( ! empty( $order_data ) ) {
				$data_to_export[] = [
					'group_id'    => $group_id,
					'group_label' => $group_label,
					'item_id'     => $item_id,
					'data'        => $order_data,
				];
			}
		}

		return [
			'data' => $data_to_export,
			'done' => count( $orders ) < 20,
		];
	}

	/**
	 * Erase customer's custom checkout field data upon validated request.
	 *
	 * @param string $email_address
	 * @param int    $page
	 * @return array<string, mixed>
	 */
	public function erase_customer_data( string $email_address, int $page = 1 ): array {
		$items_removed = false;
		$items_retained = false;
		$messages = [];

		if ( ! function_exists( 'wc_get_orders' ) ) {
			return [
				'items_removed'  => false,
				'items_retained' => false,
				'messages'       => [],
				'done'           => true,
			];
		}

		$orders = wc_get_orders(
			[
				'billing_email' => $email_address,
				'limit'         => 20,
				'page'          => $page,
			]
		);

		foreach ( $orders as $order ) {
			foreach ( $order->get_meta_data() as $meta ) {
				$key = (string) $meta->key;
				if ( str_starts_with( $key, '_wpcalibrate_' ) ) {
					$order->delete_meta_data( $key );
					$items_removed = true;
				}
			}
			$order->save();
		}

		return [
			'items_removed'  => $items_removed,
			'items_retained' => $items_retained,
			'messages'       => $messages,
			'done'           => count( $orders ) < 20,
		];
	}

	/**
	 * Add default privacy policy suggestion to WordPress.
	 *
	 * @return void
	 */
	public function add_privacy_policy_content(): void {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}

		$content = __(
			'When you complete a checkout on our store, we may collect additional checkout details configured through WPCalibrate Checkout Studio. This information is stored securely in your order record and is used strictly for processing, fulfilling, and delivering your purchase.',
			'wpcalibrate-checkout-studio'
		);

		wp_add_privacy_policy_content(
			__( 'WPCalibrate Checkout Studio for WooCommerce', 'wpcalibrate-checkout-studio' ),
			wp_kses_post( wpautop( $content ) )
		);
	}
}
