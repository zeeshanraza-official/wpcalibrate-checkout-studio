<?php
/**
 * Manages order data display across admin screens, emails, and customer account views.
 * Fully compatible with High-Performance Order Storage (HPOS) and traditional post storage.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Orders;

use WPCalibrate\CheckoutStudio\Settings\SettingsRepository;
use Automattic\WooCommerce\Utilities\OrderUtil;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class OrderDataManager
 */
final class OrderDataManager {

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
	 * Initialize order data display hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		$display_settings = $this->repo->get_display();

		// Admin Order Display (HPOS and traditional)
		if ( ! empty( $display_settings['show_in_order_admin'] ) ) {
			add_action( 'add_meta_boxes', [ $this, 'register_order_meta_box' ], 20, 2 );
		}

		// Customer My Account & Order Received Display
		if ( ! empty( $display_settings['show_in_customer_view'] ) ) {
			add_action( 'woocommerce_order_details_after_order_table', [ $this, 'render_customer_order_details' ], 20, 1 );
		}

		// WooCommerce Email Notifications
		if ( ! empty( $display_settings['show_in_emails'] ) ) {
			add_action( 'woocommerce_email_after_order_table', [ $this, 'render_email_order_meta' ], 20, 4 );
		}
	}

	/**
	 * Register meta box on order edit screen (HPOS compatible).
	 *
	 * @param string   $post_type_or_screen
	 * @param \WP_Post|\WC_Order|null $post_or_order
	 * @return void
	 */
	public function register_order_meta_box( string $post_type_or_screen, mixed $post_or_order ): void {
		$screen = class_exists( OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled()
			? wc_get_page_screen_id( 'shop-order' )
			: 'shop_order';

		add_meta_box(
			'wpcalibrate_checkout_studio_order_fields',
			__( 'Checkout Studio Custom Fields', 'wpcalibrate-checkout-studio' ),
			[ $this, 'render_admin_order_meta_box' ],
			$screen,
			'side',
			'default'
		);
	}

	/**
	 * Render the order meta box in admin order view.
	 *
	 * @param \WP_Post|\WC_Order $post_or_order
	 * @return void
	 */
	public function render_admin_order_meta_box( mixed $post_or_order ): void {
		$order = ( $post_or_order instanceof \WC_Order ) ? $post_or_order : wc_get_order( $post_or_order->ID ?? 0 );
		if ( ! $order instanceof \WC_Order ) {
			return;
		}

		$custom_values = $this->get_order_custom_values( $order, 'admin' );

		if ( empty( $custom_values ) ) {
			echo '<p style="color: #6b7280; font-size: 13px;">' . esc_html__( 'No custom checkout field values recorded for this order.', 'wpcalibrate-checkout-studio' ) . '</p>';
			return;
		}

		echo '<div class="wcs-admin-order-fields">';
		foreach ( $custom_values as $item ) {
			echo '<p style="margin-bottom: 12px; font-size: 13px;">';
			echo '<strong>' . esc_html( $item['label'] ) . ':</strong><br>';
			echo '<span style="color: #1f2937;">' . nl2br( esc_html( $item['formatted_value'] ) ) . '</span>';
			echo '</p>';
		}
		echo '</div>';
	}

	/**
	 * Render custom field values on customer order received and My Account details.
	 *
	 * @param \WC_Order $order
	 * @return void
	 */
	public function render_customer_order_details( \WC_Order $order ): void {
		$custom_values = $this->get_order_custom_values( $order, 'customer' );
		if ( empty( $custom_values ) ) {
			return;
		}

		?>
		<section class="woocommerce-customer-details wcs-customer-fields-section" style="margin-top: 24px;">
			<h2 class="woocommerce-column__title"><?php esc_html_e( 'Additional Order Details', 'wpcalibrate-checkout-studio' ); ?></h2>
			<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
				<tbody>
					<?php foreach ( $custom_values as $item ) : ?>
						<tr>
							<th scope="row" style="text-align: left; width: 40%; font-weight: 600;"><?php echo esc_html( $item['label'] ); ?>:</th>
							<td><?php echo nl2br( esc_html( $item['formatted_value'] ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</section>
		<?php
	}

	/**
	 * Render custom field values in customer and admin order emails.
	 *
	 * @param \WC_Order $order
	 * @param bool      $sent_to_admin
	 * @param bool      $plain_text
	 * @param \WC_Email $email
	 * @return void
	 */
	public function render_email_order_meta( \WC_Order $order, bool $sent_to_admin, bool $plain_text, mixed $email ): void {
		$custom_values = $this->get_order_custom_values( $order, 'emails' );
		if ( empty( $custom_values ) ) {
			return;
		}

		if ( $plain_text ) {
			echo "\n" . esc_html__( 'Additional Order Details:', 'wpcalibrate-checkout-studio' ) . "\n";
			echo "----------------------------------------\n";
			foreach ( $custom_values as $item ) {
				echo esc_html( $item['label'] ) . ': ' . esc_html( $item['formatted_value'] ) . "\n";
			}
			echo "\n";
		} else {
			?>
			<div style="margin-bottom: 24px;">
				<h2 style="font-size: 16px; font-weight: bold; margin-bottom: 12px; color: #374151;">
					<?php esc_html_e( 'Additional Order Details', 'wpcalibrate-checkout-studio' ); ?>
				</h2>
				<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #e5e7eb; border-collapse: collapse;" border="1">
					<tbody>
						<?php foreach ( $custom_values as $item ) : ?>
							<tr>
								<th scope="row" style="text-align: left; border: 1px solid #e5e7eb; padding: 10px; width: 40%; font-weight: 600;">
									<?php echo esc_html( $item['label'] ); ?>:
								</th>
								<td style="text-align: left; border: 1px solid #e5e7eb; padding: 10px;">
									<?php echo nl2br( esc_html( $item['formatted_value'] ) ); ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php
		}
	}

	/**
	 * Extract and format stored custom fields for a specific order and display context.
	 *
	 * @param \WC_Order $order
	 * @param string    $context 'admin', 'customer', or 'emails'
	 * @return array<int, array<string, string>>
	 */
	private function get_order_custom_values( \WC_Order $order, string $context ): array {
		$results = [];

		foreach ( $order->get_meta_data() as $meta ) {
			$meta_key = (string) $meta->key;

			// Look for WPCalibrate custom metadata prefix
			if ( ! str_starts_with( $meta_key, '_wpcalibrate_' ) || str_starts_with( $meta_key, '_wpcalibrate_label_' ) ) {
				continue;
			}

			$field_key  = str_replace( '_wpcalibrate_', '', $meta_key );
			$raw_value  = $meta->value;
			$label_meta = $order->get_meta( '_wpcalibrate_label_' . $field_key );

			$label   = is_array( $label_meta ) && ! empty( $label_meta['label'] ) ? (string) $label_meta['label'] : ucwords( str_replace( '_', ' ', $field_key ) );
			$display = is_array( $label_meta ) && isset( $label_meta['display'] ) ? (array) $label_meta['display'] : [];
			$type    = is_array( $label_meta ) && isset( $label_meta['type'] ) ? (string) $label_meta['type'] : 'text';

			// Verify display permission for this context
			if ( ! empty( $display ) && empty( $display[ $context ] ) ) {
				continue;
			}

			// Format value based on field type
			$formatted = $this->format_field_value( $raw_value, $type );

			if ( '' !== $formatted ) {
				$results[] = [
					'key'             => $field_key,
					'label'           => $label,
					'formatted_value' => $formatted,
				];
			}
		}

		return $results;
	}

	/**
	 * Format value for human display based on its data type.
	 *
	 * @param mixed  $value
	 * @param string $type
	 * @return string
	 */
	private function format_field_value( mixed $value, string $type ): string {
		if ( null === $value || '' === $value ) {
			return '';
		}

		if ( 'checkbox' === $type ) {
			return ( ! empty( $value ) && 'no' !== $value ) ? __( 'Yes', 'wpcalibrate-checkout-studio' ) : __( 'No', 'wpcalibrate-checkout-studio' );
		}

		if ( is_array( $value ) ) {
			return implode( ', ', array_map( 'strval', $value ) );
		}

		return (string) $value;
	}
}
