<?php
/**
 * Renders administrative views and tab interfaces for Checkout Studio.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Admin;

use WPCalibrate\CheckoutStudio\Settings\SettingsRepository;
use WPCalibrate\CheckoutStudio\Settings\SettingsSchema;
use WPCalibrate\CheckoutStudio\Diagnostics\DiagnosticsManager;
use WPCalibrate\CheckoutStudio\Licensing\LicenseManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AdminViewRenderer
 */
final class AdminViewRenderer {

	private SettingsRepository $repo;
	private string $active_tab;

	/**
	 * Constructor.
	 *
	 * @param SettingsRepository $repo
	 * @param string             $active_tab
	 */
	public function __construct( SettingsRepository $repo, string $active_tab ) {
		$this->repo       = $repo;
		$this->active_tab = $active_tab;
	}

	/**
	 * Render the full admin view.
	 *
	 * @return void
	 */
	public function render(): void {
		$tabs = [
			'overview'      => __( 'Overview', 'wpcalibrate-checkout-studio' ),
			'builder'       => __( 'Checkout Builder', 'wpcalibrate-checkout-studio' ),
			'conditions'    => __( 'Conditional Logic', 'wpcalibrate-checkout-studio' ),
			'appearance'    => __( 'Appearance', 'wpcalibrate-checkout-studio' ),
			'display'       => __( 'Data & Display', 'wpcalibrate-checkout-studio' ),
			'import_export' => __( 'Import / Export', 'wpcalibrate-checkout-studio' ),
			'diagnostics'   => __( 'Diagnostics', 'wpcalibrate-checkout-studio' ),
			'license'       => __( 'License', 'wpcalibrate-checkout-studio' ),
			'support'       => __( 'Support', 'wpcalibrate-checkout-studio' ),
		];

		$logo_url = WPCALIBRATE_CHECKOUT_STUDIO_URL . 'branding/icon-dark.png';
		?>
		<div class="wrap wpcalibrate-studio-wrap">
			<!-- Header -->
			<header class="wpcalibrate-studio-header">
				<div class="wpcalibrate-header-brand">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="WPCalibrate" class="wpcalibrate-brand-logo" width="36" height="36" />
					<div class="wpcalibrate-title-group">
						<h1 class="wpcalibrate-plugin-title"><?php esc_html_e( 'Checkout Studio', 'wpcalibrate-checkout-studio' ); ?></h1>
						<span class="wpcalibrate-version-badge">v<?php echo esc_html( WPCALIBRATE_CHECKOUT_STUDIO_VERSION ); ?></span>
					</div>
				</div>
				<div class="wpcalibrate-header-meta">
					<span class="wpcalibrate-for-woo"><?php esc_html_e( 'for WooCommerce', 'wpcalibrate-checkout-studio' ); ?></span>
				</div>
			</header>

			<!-- Live Notice Banner -->
			<div id="wcs-notification-area" aria-live="polite"></div>

			<!-- Tab Navigation -->
			<nav class="nav-tab-wrapper wpcalibrate-nav-tabs" aria-label="<?php esc_attr_e( 'Checkout Studio Tabs', 'wpcalibrate-checkout-studio' ); ?>">
				<?php foreach ( $tabs as $tab_key => $tab_title ) : ?>
					<?php
					$tab_url = add_query_arg(
						[
							'page' => AdminManager::MENU_SLUG,
							'tab'  => $tab_key,
						],
						admin_url( 'admin.php' )
					);
					$is_active = ( $this->active_tab === $tab_key );
					?>
					<a href="<?php echo esc_url( $tab_url ); ?>" class="nav-tab <?php echo $is_active ? 'nav-tab-active' : ''; ?>" <?php echo $is_active ? 'aria-current="page"' : ''; ?>>
						<?php echo esc_html( $tab_title ); ?>
					</a>
				<?php endforeach; ?>
			</nav>

			<!-- Tab Content Body -->
			<main class="wpcalibrate-tab-content">
				<?php
				switch ( $this->active_tab ) {
					case 'overview':
						$this->render_overview_tab();
						break;
					case 'builder':
						$this->render_builder_tab();
						break;
					case 'conditions':
						$this->render_conditions_tab();
						break;
					case 'appearance':
						$this->render_appearance_tab();
						break;
					case 'display':
						$this->render_display_tab();
						break;
					case 'import_export':
						$this->render_import_export_tab();
						break;
					case 'diagnostics':
						$this->render_diagnostics_tab();
						break;
					case 'license':
						$this->render_license_tab();
						break;
					case 'support':
						$this->render_support_tab();
						break;
				}
				?>
			</main>
		</div>
		<?php
	}

	/**
	 * Render Overview Tab (Purpose, How to Use Guide, Method, and Quick Jump Links).
	 *
	 * @return void
	 */
	private function render_overview_tab(): void {
		$builder_url    = add_query_arg( [ 'page' => AdminManager::MENU_SLUG, 'tab' => 'builder' ], admin_url( 'admin.php' ) );
		$conditions_url = add_query_arg( [ 'page' => AdminManager::MENU_SLUG, 'tab' => 'conditions' ], admin_url( 'admin.php' ) );
		$appearance_url = add_query_arg( [ 'page' => AdminManager::MENU_SLUG, 'tab' => 'appearance' ], admin_url( 'admin.php' ) );
		$display_url    = add_query_arg( [ 'page' => AdminManager::MENU_SLUG, 'tab' => 'display' ], admin_url( 'admin.php' ) );
		$impexp_url     = add_query_arg( [ 'page' => AdminManager::MENU_SLUG, 'tab' => 'import_export' ], admin_url( 'admin.php' ) );
		$diag_url       = add_query_arg( [ 'page' => AdminManager::MENU_SLUG, 'tab' => 'diagnostics' ], admin_url( 'admin.php' ) );
		?>
		<div class="wcs-tab-pane">
			<!-- Hero Card -->
			<div class="wcs-overview-hero">
				<span class="wcs-hero-badge"><?php esc_html_e( 'Overview & Guide', 'wpcalibrate-checkout-studio' ); ?></span>
				<h2 class="wcs-hero-title"><?php esc_html_e( 'WPCalibrate Checkout Studio for WooCommerce', 'wpcalibrate-checkout-studio' ); ?></h2>
				<p class="wcs-hero-subtitle">
					<?php esc_html_e( 'An accessible, visual studio that empowers store administrators to customize checkout fields, sections, dynamic conditional rules, layout, and appearance without editing code or overriding template files.', 'wpcalibrate-checkout-studio' ); ?>
				</p>
				<div class="wcs-hero-actions">
					<a href="<?php echo esc_url( $builder_url ); ?>" class="button wcs-hero-btn-primary">
						<span class="dashicons dashicons-layout" aria-hidden="true"></span>
						<?php esc_html_e( 'Open Checkout Builder', 'wpcalibrate-checkout-studio' ); ?>
					</a>
					<a href="<?php echo esc_url( $appearance_url ); ?>" class="button wcs-hero-btn-secondary">
						<span class="dashicons dashicons-admin-appearance" aria-hidden="true"></span>
						<?php esc_html_e( 'Customize Appearance', 'wpcalibrate-checkout-studio' ); ?>
					</a>
					<a href="<?php echo esc_url( $diag_url ); ?>" class="button wcs-hero-btn-secondary">
						<span class="dashicons dashicons-dashboard" aria-hidden="true"></span>
						<?php esc_html_e( 'System Diagnostics', 'wpcalibrate-checkout-studio' ); ?>
					</a>
				</div>
			</div>

			<!-- Quick Jump Links -->
			<div class="wcs-overview-section">
				<div class="wcs-section-header">
					<h3><?php esc_html_e( 'Quick Access', 'wpcalibrate-checkout-studio' ); ?></h3>
					<p><?php esc_html_e( 'Jump directly to the section you want to configure:', 'wpcalibrate-checkout-studio' ); ?></p>
				</div>
				<div class="wcs-cards-grid-3">
					<a href="<?php echo esc_url( $builder_url ); ?>" class="wcs-quick-card">
						<div class="wcs-quick-left">
							<span class="dashicons dashicons-forms wcs-quick-icon"></span>
							<div class="wcs-quick-info">
								<strong><?php esc_html_e( 'Checkout Builder', 'wpcalibrate-checkout-studio' ); ?></strong>
								<span><?php esc_html_e( 'Add, edit, and reorder fields', 'wpcalibrate-checkout-studio' ); ?></span>
							</div>
						</div>
						<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
					</a>
					<a href="<?php echo esc_url( $conditions_url ); ?>" class="wcs-quick-card">
						<div class="wcs-quick-left">
							<span class="dashicons dashicons-randomize wcs-quick-icon"></span>
							<div class="wcs-quick-info">
								<strong><?php esc_html_e( 'Conditional Logic', 'wpcalibrate-checkout-studio' ); ?></strong>
								<span><?php esc_html_e( 'Dynamic show/hide rules', 'wpcalibrate-checkout-studio' ); ?></span>
							</div>
						</div>
						<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
					</a>
					<a href="<?php echo esc_url( $appearance_url ); ?>" class="wcs-quick-card">
						<div class="wcs-quick-left">
							<span class="dashicons dashicons-art wcs-quick-icon"></span>
							<div class="wcs-quick-info">
								<strong><?php esc_html_e( 'Appearance', 'wpcalibrate-checkout-studio' ); ?></strong>
								<span><?php esc_html_e( 'Inputs, spacing, and buttons', 'wpcalibrate-checkout-studio' ); ?></span>
							</div>
						</div>
						<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
					</a>
				</div>
			</div>

			<!-- Section 1: Purpose -->
			<div class="wcs-overview-section">
				<div class="wcs-section-header">
					<h3><?php esc_html_e( 'Plugin Purpose & Philosophy', 'wpcalibrate-checkout-studio' ); ?></h3>
					<p><?php esc_html_e( 'Built to fulfill common checkout customization requirements while strictly preserving WooCommerce native integrity.', 'wpcalibrate-checkout-studio' ); ?></p>
				</div>
				<div class="wcs-cards-grid-2">
					<div class="wcs-feature-card">
						<div class="wcs-feature-icon"><span class="dashicons dashicons-shield"></span></div>
						<h4 class="wcs-feature-title"><?php esc_html_e( 'Native Workflow Preservation', 'wpcalibrate-checkout-studio' ); ?></h4>
						<p class="wcs-feature-desc">
							<?php esc_html_e( 'Checkout Studio does not replace WooCommerce with a custom checkout engine. Instead, it utilizes official extension APIs, actions, filters, and Store API mechanisms to preserve native payment gateway handshakes, tax calculations, customer address handling, and High-Performance Order Storage (HPOS).', 'wpcalibrate-checkout-studio' ); ?>
						</p>
					</div>
					<div class="wcs-feature-card">
						<div class="wcs-feature-icon"><span class="dashicons dashicons-block-default"></span></div>
						<h4 class="wcs-feature-title"><?php esc_html_e( 'Dual Renderer Support (Classic & Blocks)', 'wpcalibrate-checkout-studio' ); ?></h4>
						<p class="wcs-feature-desc">
							<?php esc_html_e( 'Whether your store uses the traditional shortcode checkout ([woocommerce_checkout]) or modern WooCommerce Checkout Blocks, Checkout Studio seamlessly adapts to both surfaces using official Additional Checkout Fields APIs and Store API schemas.', 'wpcalibrate-checkout-studio' ); ?>
						</p>
					</div>
					<div class="wcs-feature-card">
						<div class="wcs-feature-icon"><span class="dashicons dashicons-lock"></span></div>
						<h4 class="wcs-feature-title"><?php esc_html_e( 'Server-Enforced Security', 'wpcalibrate-checkout-studio' ); ?></h4>
						<p class="wcs-feature-desc">
							<?php esc_html_e( 'Client-side conditional visibility is treated strictly as UX convenience. All required states, data types, email formats, numerical bounds, and allowlisted options are strictly validated server-side during checkout submission to guarantee data integrity.', 'wpcalibrate-checkout-studio' ); ?>
						</p>
					</div>
					<div class="wcs-feature-card">
						<div class="wcs-feature-icon"><span class="dashicons dashicons-database"></span></div>
						<h4 class="wcs-feature-title"><?php esc_html_e( 'HPOS-Safe Order CRUD Storage', 'wpcalibrate-checkout-studio' ); ?></h4>
						<p class="wcs-feature-desc">
							<?php esc_html_e( 'Custom field data is stored using native WooCommerce Order CRUD APIs in HPOS order meta tables under a dedicated _wpcalibrate_ namespace. Historical order data is preserved even if a field is later disabled or removed from active checkout.', 'wpcalibrate-checkout-studio' ); ?>
						</p>
					</div>
				</div>
			</div>

			<!-- Section 2: How to Use -->
			<div class="wcs-overview-section">
				<div class="wcs-section-header">
					<h3><?php esc_html_e( 'How to Use Checkout Studio (Step-by-Step)', 'wpcalibrate-checkout-studio' ); ?></h3>
					<p><?php esc_html_e( 'Follow these straightforward steps to configure your store checkout:', 'wpcalibrate-checkout-studio' ); ?></p>
				</div>
				<div class="wcs-cards-grid-2">
					<div class="wcs-step-card">
						<div class="wcs-step-num">1</div>
						<div class="wcs-step-content">
							<h4><?php esc_html_e( 'Customize & Reorder Fields', 'wpcalibrate-checkout-studio' ); ?></h4>
							<p><?php esc_html_e( 'Open Checkout Builder. Drag fields to reorder them or use the keyboard Move Up/Down buttons. Click "Add Custom Field" to add any of the 14 supported field types, or click "Edit" on core fields to adjust labels, placeholders, and layout widths (100%, 50%, or 33%).', 'wpcalibrate-checkout-studio' ); ?></p>
							<a href="<?php echo esc_url( $builder_url ); ?>" class="button button-small button-secondary"><?php esc_html_e( 'Go to Builder', 'wpcalibrate-checkout-studio' ); ?> &rarr;</a>
						</div>
					</div>
					<div class="wcs-step-card">
						<div class="wcs-step-num">2</div>
						<div class="wcs-step-content">
							<h4><?php esc_html_e( 'Configure Conditional Visibility', 'wpcalibrate-checkout-studio' ); ?></h4>
							<p><?php esc_html_e( 'Open Conditional Logic to show, hide, or require fields dynamically based on cart subtotal, shipping country, user login status, chosen shipping method, or other field values. Hidden fields are automatically exempted from validation so checkouts are never blocked.', 'wpcalibrate-checkout-studio' ); ?></p>
							<a href="<?php echo esc_url( $conditions_url ); ?>" class="button button-small button-secondary"><?php esc_html_e( 'Go to Conditions', 'wpcalibrate-checkout-studio' ); ?> &rarr;</a>
						</div>
					</div>
					<div class="wcs-step-card">
						<div class="wcs-step-num">3</div>
						<div class="wcs-step-content">
							<h4><?php esc_html_e( 'Style Checkout Appearance', 'wpcalibrate-checkout-studio' ); ?></h4>
							<p><?php esc_html_e( 'Open Appearance to customize input border radiuses, border colors, background colors, accessible focus rings, and button colors. All generated CSS is strictly scoped to the checkout form and does not affect the rest of your theme.', 'wpcalibrate-checkout-studio' ); ?></p>
							<a href="<?php echo esc_url( $appearance_url ); ?>" class="button button-small button-secondary"><?php esc_html_e( 'Go to Appearance', 'wpcalibrate-checkout-studio' ); ?> &rarr;</a>
						</div>
					</div>
					<div class="wcs-step-card">
						<div class="wcs-step-num">4</div>
						<div class="wcs-step-content">
							<h4><?php esc_html_e( 'Configure Display & Staging Backups', 'wpcalibrate-checkout-studio' ); ?></h4>
							<p><?php esc_html_e( 'Use Data & Display to control where custom values appear (HPOS Order Admin, Customer Account details, or Notification Emails). Use Import / Export to download JSON backups before making large adjustments or moving to another store.', 'wpcalibrate-checkout-studio' ); ?></p>
							<a href="<?php echo esc_url( $display_url ); ?>" class="button button-small button-secondary"><?php esc_html_e( 'Go to Display', 'wpcalibrate-checkout-studio' ); ?> &rarr;</a>
						</div>
					</div>
				</div>
			</div>

			<!-- Section 3: Technical Method -->
			<div class="wcs-overview-section">
				<div class="wcs-section-header">
					<h3><?php esc_html_e( 'Architecture & Integration Method', 'wpcalibrate-checkout-studio' ); ?></h3>
					<p><?php esc_html_e( 'Understanding how Checkout Studio works under the hood:', 'wpcalibrate-checkout-studio' ); ?></p>
				</div>
				<div class="wcs-card">
					<table class="wcs-support-table">
						<tr>
							<td style="width: 25%;"><strong><?php esc_html_e( 'Classic Checkout Method:', 'wpcalibrate-checkout-studio' ); ?></strong></td>
							<td><?php esc_html_e( 'Filters woocommerce_checkout_fields to reorder and relabel core fields. Renders custom types via HTML5 field filters. Enforces server-side validation during woocommerce_checkout_process and saves sanitized metadata during woocommerce_checkout_create_order.', 'wpcalibrate-checkout-studio' ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Checkout Blocks Method:', 'wpcalibrate-checkout-studio' ); ?></strong></td>
							<td><?php esc_html_e( 'Registers supported custom fields with woocommerce_register_additional_checkout_field at woocommerce_init. Intercepts Store API validation via woocommerce_validate_additional_field and persists values via woocommerce_set_additional_field_value.', 'wpcalibrate-checkout-studio' ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'High-Performance Order Storage:', 'wpcalibrate-checkout-studio' ); ?></strong></td>
							<td><?php esc_html_e( 'Compatibility declared via Automattic\WooCommerce\Utilities\FeaturesUtil. All metadata operations use native WooCommerce CRUD APIs, supporting both HPOS custom orders tables and legacy post storage without direct table manipulation.', 'wpcalibrate-checkout-studio' ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Fail-Open Reliability:', 'wpcalibrate-checkout-studio' ); ?></strong></td>
							<td><?php esc_html_e( 'If the plugin is deactivated or settings are temporarily disabled, WooCommerce immediately falls back to native standard checkout behavior. Completed customer orders and historical values are always preserved.', 'wpcalibrate-checkout-studio' ); ?></td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Checkout Builder Tab.
	 *
	 * @return void
	 */
	private function render_builder_tab(): void {
		$fields   = $this->repo->get_fields();
		$sections = $this->repo->get_sections();
		$types    = SettingsSchema::SUPPORTED_FIELD_TYPES;
		?>
		<div class="wcs-builder-container">
			<div class="wcs-builder-toolbar">
				<div class="wcs-toolbar-left">
					<label for="wcs-section-filter" class="screen-reader-text"><?php esc_html_e( 'Filter by Section', 'wpcalibrate-checkout-studio' ); ?></label>
					<select id="wcs-section-filter" class="wcs-select">
						<option value=""><?php esc_html_e( 'All Sections', 'wpcalibrate-checkout-studio' ); ?></option>
						<?php foreach ( $sections as $s ) : ?>
							<option value="<?php echo esc_attr( $s['key'] ); ?>"><?php echo esc_html( $s['title'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="wcs-toolbar-right">
					<button type="button" id="wcs-add-field-btn" class="button button-secondary">
						<span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
						<?php esc_html_e( 'Add Custom Field', 'wpcalibrate-checkout-studio' ); ?>
					</button>
					<button type="button" id="wcs-save-builder-btn" class="button button-primary">
						<?php esc_html_e( 'Save Changes', 'wpcalibrate-checkout-studio' ); ?>
					</button>
				</div>
			</div>

			<!-- Fields Table with Accessibility & Drag Handles -->
			<div class="wcs-table-card">
				<table class="wp-list-table widefat fixed striped wcs-fields-table" id="wcs-fields-table">
					<thead>
						<tr>
							<th scope="col" class="wcs-col-drag" style="width: 44px; text-align: center;"><span class="screen-reader-text"><?php esc_html_e( 'Reorder', 'wpcalibrate-checkout-studio' ); ?></span></th>
							<th scope="col" class="wcs-col-label" style="width: 22%;"><?php esc_html_e( 'Field Label', 'wpcalibrate-checkout-studio' ); ?></th>
							<th scope="col" class="wcs-col-key" style="width: 18%;"><?php esc_html_e( 'Machine Key', 'wpcalibrate-checkout-studio' ); ?></th>
							<th scope="col" class="wcs-col-section" style="width: 13%;"><?php esc_html_e( 'Section', 'wpcalibrate-checkout-studio' ); ?></th>
							<th scope="col" class="wcs-col-type" style="width: 11%;"><?php esc_html_e( 'Type', 'wpcalibrate-checkout-studio' ); ?></th>
							<th scope="col" class="wcs-col-status" style="width: 80px; text-align: center;"><?php esc_html_e( 'Required', 'wpcalibrate-checkout-studio' ); ?></th>
							<th scope="col" class="wcs-col-enabled" style="width: 80px; text-align: center;"><?php esc_html_e( 'Enabled', 'wpcalibrate-checkout-studio' ); ?></th>
							<th scope="col" class="wcs-col-actions" style="width: 155px; text-align: right;"><?php esc_html_e( 'Actions', 'wpcalibrate-checkout-studio' ); ?></th>
						</tr>
					</thead>
					<tbody id="wcs-fields-list">
						<?php foreach ( $fields as $f ) : ?>
							<?php
							$is_core = ! empty( $f['is_core'] );
							$key     = esc_attr( $f['key'] );
							$type    = $types[ $f['type'] ] ?? $f['type'];
							?>
							<tr class="wcs-field-row" data-id="<?php echo esc_attr( $f['id'] ); ?>" data-key="<?php echo $key; ?>" data-section="<?php echo esc_attr( $f['section'] ); ?>">
								<td class="wcs-col-drag" style="text-align: center;">
									<button type="button" class="wcs-drag-handle" title="<?php esc_attr_e( 'Drag to reorder', 'wpcalibrate-checkout-studio' ); ?>" aria-label="<?php esc_attr_e( 'Drag to reorder', 'wpcalibrate-checkout-studio' ); ?>">
										<span class="dashicons dashicons-menu" aria-hidden="true"></span>
									</button>
								</td>
								<td class="wcs-col-label">
									<strong><?php echo esc_html( $f['label'] ); ?></strong>
									<?php if ( $is_core ) : ?>
										<span class="wcs-badge wcs-badge-core"><?php esc_html_e( 'Core', 'wpcalibrate-checkout-studio' ); ?></span>
									<?php else : ?>
										<span class="wcs-badge wcs-badge-custom"><?php esc_html_e( 'Custom', 'wpcalibrate-checkout-studio' ); ?></span>
									<?php endif; ?>
									<?php if ( ! empty( $f['conditions'] ) ) : ?>
										<span class="wcs-badge wcs-badge-rule"><?php esc_html_e( 'Conditional', 'wpcalibrate-checkout-studio' ); ?></span>
									<?php endif; ?>
								</td>
								<td class="wcs-col-key">
									<code><?php echo esc_html( $f['key'] ); ?></code>
								</td>
								<td class="wcs-col-section">
									<span class="wcs-section-tag"><?php echo esc_html( ucfirst( $f['section'] ) ); ?></span>
								</td>
								<td class="wcs-col-type">
									<?php echo esc_html( $type ); ?>
								</td>
								<td class="wcs-col-status" style="text-align: center;">
									<label class="wcs-switch">
										<input type="checkbox" class="wcs-row-required" <?php checked( ! empty( $f['required'] ) ); ?>>
										<span class="wcs-slider round"></span>
									</label>
								</td>
								<td class="wcs-col-enabled" style="text-align: center;">
									<label class="wcs-switch">
										<input type="checkbox" class="wcs-row-enabled" <?php checked( ! empty( $f['enabled'] ) ); ?>>
										<span class="wcs-slider round"></span>
									</label>
								</td>
								<td class="wcs-col-actions" style="text-align: right;">
									<div class="wcs-actions-wrap">
										<button type="button" class="button button-small wcs-btn-move-up" title="<?php esc_attr_e( 'Move Up', 'wpcalibrate-checkout-studio' ); ?>" aria-label="<?php esc_attr_e( 'Move Up', 'wpcalibrate-checkout-studio' ); ?>">▲</button>
										<button type="button" class="button button-small wcs-btn-move-down" title="<?php esc_attr_e( 'Move Down', 'wpcalibrate-checkout-studio' ); ?>" aria-label="<?php esc_attr_e( 'Move Down', 'wpcalibrate-checkout-studio' ); ?>">▼</button>
										<button type="button" class="button button-small wcs-btn-edit" title="<?php esc_attr_e( 'Edit Field', 'wpcalibrate-checkout-studio' ); ?>"><?php esc_html_e( 'Edit', 'wpcalibrate-checkout-studio' ); ?></button>
										<?php if ( ! $is_core ) : ?>
											<button type="button" class="button button-small wcs-btn-delete button-link-delete" title="<?php esc_attr_e( 'Delete Custom Field', 'wpcalibrate-checkout-studio' ); ?>">&times;</button>
										<?php endif; ?>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>

		<!-- Field Edit Modal Dialog -->
		<div id="wcs-field-modal" class="wcs-modal" role="dialog" aria-modal="true" aria-labelledby="wcs-modal-title" style="display: none;">
			<div class="wcs-modal-backdrop"></div>
			<div class="wcs-modal-dialog">
				<div class="wcs-modal-header">
					<h2 id="wcs-modal-title"><?php esc_html_e( 'Edit Checkout Field', 'wpcalibrate-checkout-studio' ); ?></h2>
					<button type="button" class="wcs-modal-close" aria-label="<?php esc_attr_e( 'Close', 'wpcalibrate-checkout-studio' ); ?>">&times;</button>
				</div>
				<form id="wcs-field-form" class="wcs-modal-body">
					<input type="hidden" id="wcs-field-id" value="">
					<input type="hidden" id="wcs-field-is-core" value="0">

					<div class="wcs-form-row">
						<div class="wcs-form-col">
							<label for="wcs-field-key"><?php esc_html_e( 'Machine Key', 'wpcalibrate-checkout-studio' ); ?> <span class="required">*</span></label>
							<input type="text" id="wcs-field-key" class="regular-text" required pattern="^[a-z0-9_]+$">
							<p class="description"><?php esc_html_e( 'Unique identifier (e.g. delivery_instructions). Lowercase and underscores only.', 'wpcalibrate-checkout-studio' ); ?></p>
						</div>
						<div class="wcs-form-col">
							<label for="wcs-field-type"><?php esc_html_e( 'Field Type', 'wpcalibrate-checkout-studio' ); ?></label>
							<select id="wcs-field-type" class="wcs-select">
								<?php foreach ( $types as $k => $label ) : ?>
									<option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="wcs-form-row">
						<div class="wcs-form-col">
							<label for="wcs-field-label"><?php esc_html_e( 'Field Label', 'wpcalibrate-checkout-studio' ); ?> <span class="required">*</span></label>
							<input type="text" id="wcs-field-label" class="regular-text" required>
						</div>
						<div class="wcs-form-col">
							<label for="wcs-field-section"><?php esc_html_e( 'Checkout Section', 'wpcalibrate-checkout-studio' ); ?></label>
							<select id="wcs-field-section" class="wcs-select">
								<?php foreach ( $sections as $s ) : ?>
									<option value="<?php echo esc_attr( $s['key'] ); ?>"><?php echo esc_html( $s['title'] ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="wcs-form-row">
						<div class="wcs-form-col">
							<label for="wcs-field-placeholder"><?php esc_html_e( 'Placeholder', 'wpcalibrate-checkout-studio' ); ?></label>
							<input type="text" id="wcs-field-placeholder" class="regular-text">
						</div>
						<div class="wcs-form-col">
							<label for="wcs-field-width"><?php esc_html_e( 'Field Width', 'wpcalibrate-checkout-studio' ); ?></label>
							<select id="wcs-field-width" class="wcs-select">
								<option value="full"><?php esc_html_e( 'Full Width (100%)', 'wpcalibrate-checkout-studio' ); ?></option>
								<option value="half_left"><?php esc_html_e( 'Half Width - Left (50%)', 'wpcalibrate-checkout-studio' ); ?></option>
								<option value="half_right"><?php esc_html_e( 'Half Width - Right (50%)', 'wpcalibrate-checkout-studio' ); ?></option>
								<option value="third"><?php esc_html_e( 'One Third Width (33%)', 'wpcalibrate-checkout-studio' ); ?></option>
							</select>
						</div>
					</div>

					<div class="wcs-form-row">
						<div class="wcs-form-col" style="flex: 1 1 100%;">
							<label for="wcs-field-description"><?php esc_html_e( 'Description / Help Text', 'wpcalibrate-checkout-studio' ); ?></label>
							<input type="text" id="wcs-field-description" class="large-text">
						</div>
					</div>

					<!-- Options editor (for select, radio, multiselect) -->
					<div id="wcs-options-container" class="wcs-form-row" style="display: none;">
						<div class="wcs-form-col" style="flex: 1 1 100%;">
							<label for="wcs-field-options-raw"><?php esc_html_e( 'Options (One per line: value | Label)', 'wpcalibrate-checkout-studio' ); ?></label>
							<textarea id="wcs-field-options-raw" rows="4" class="large-text" placeholder="standard | Standard Shipping&#10;express | Express Courier"></textarea>
						</div>
					</div>

					<!-- Display Locations -->
					<div class="wcs-form-row">
						<fieldset class="wcs-form-col" style="flex: 1 1 100%;">
							<legend><strong><?php esc_html_e( 'Display Locations', 'wpcalibrate-checkout-studio' ); ?></strong></legend>
							<label><input type="checkbox" id="wcs-display-admin" checked> <?php esc_html_e( 'Show in Order Administration', 'wpcalibrate-checkout-studio' ); ?></label><br>
							<label><input type="checkbox" id="wcs-display-customer" checked> <?php esc_html_e( 'Show in Customer My Account & Order Received', 'wpcalibrate-checkout-studio' ); ?></label><br>
							<label><input type="checkbox" id="wcs-display-emails" checked> <?php esc_html_e( 'Show in WooCommerce Emails', 'wpcalibrate-checkout-studio' ); ?></label>
						</fieldset>
					</div>

					<div class="wcs-modal-footer">
						<button type="button" class="button wcs-modal-cancel"><?php esc_html_e( 'Cancel', 'wpcalibrate-checkout-studio' ); ?></button>
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Apply Changes', 'wpcalibrate-checkout-studio' ); ?></button>
					</div>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Conditional Logic Tab.
	 *
	 * @return void
	 */
	private function render_conditions_tab(): void {
		$rules  = $this->repo->get_conditional_rules();
		$fields = $this->repo->get_fields();
		?>
		<div class="wcs-tab-pane">
			<h2><?php esc_html_e( 'Conditional Visibility Rules', 'wpcalibrate-checkout-studio' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Configure dynamic rules to show, hide, or require checkout fields based on cart contents, shipping country, user status, or other field values. Rules are evaluated client-side for smooth UX and enforced strictly server-side upon checkout submission.', 'wpcalibrate-checkout-studio' ); ?>
			</p>

			<div class="wcs-card" style="margin-top: 16px;">
				<div class="wcs-conditions-list" id="wcs-conditions-container">
					<?php if ( empty( $rules ) ) : ?>
						<p class="wcs-empty-state" id="wcs-no-rules-msg"><?php esc_html_e( 'No conditional rules configured yet. Click "Add Rule" below to create one.', 'wpcalibrate-checkout-studio' ); ?></p>
					<?php endif; ?>
				</div>

				<div style="margin-top: 16px;">
					<button type="button" id="wcs-add-rule-btn" class="button button-secondary">
						<span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
						<?php esc_html_e( 'Add Conditional Rule', 'wpcalibrate-checkout-studio' ); ?>
					</button>
					<button type="button" id="wcs-save-rules-btn" class="button button-primary" style="margin-left: 8px;">
						<?php esc_html_e( 'Save Rules', 'wpcalibrate-checkout-studio' ); ?>
					</button>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Appearance Settings Tab.
	 *
	 * @return void
	 */
	private function render_appearance_tab(): void {
		$app = $this->repo->get_appearance();
		?>
		<div class="wcs-tab-pane">
			<h2><?php esc_html_e( 'Checkout Appearance & Styling', 'wpcalibrate-checkout-studio' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Fine-tune checkout field spacing, borders, typography, and button styling. Generates scoped CSS for Classic Checkout and compatible styling tokens for Checkout Blocks without invasive overrides.', 'wpcalibrate-checkout-studio' ); ?>
			</p>

			<form id="wcs-appearance-form" class="wcs-card" style="margin-top: 16px;">
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Enable Custom Styling', 'wpcalibrate-checkout-studio' ); ?></th>
						<td>
							<label class="wcs-switch">
								<input type="checkbox" id="wcs-app-enabled" <?php checked( ! empty( $app['enabled'] ) ); ?>>
								<span class="wcs-slider round"></span>
							</label>
							<p class="description"><?php esc_html_e( 'When disabled, theme defaults are used completely.', 'wpcalibrate-checkout-studio' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Field Vertical Spacing', 'wpcalibrate-checkout-studio' ); ?></th>
						<td>
							<input type="text" id="wcs-app-field-spacing" class="regular-text" value="<?php echo esc_attr( $app['field_spacing'] ?? '16px' ); ?>">
							<p class="description"><?php esc_html_e( 'Distance between checkout fields (e.g. 16px or 1.25rem).', 'wpcalibrate-checkout-studio' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Input Border Radius', 'wpcalibrate-checkout-studio' ); ?></th>
						<td>
							<input type="text" id="wcs-app-border-radius" class="regular-text" value="<?php echo esc_attr( $app['input_border_radius'] ?? '4px' ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Input Border Color', 'wpcalibrate-checkout-studio' ); ?></th>
						<td>
							<input type="color" id="wcs-app-border-color" value="<?php echo esc_attr( $app['input_border_color'] ?? '#d1d5db' ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Input Background Color', 'wpcalibrate-checkout-studio' ); ?></th>
						<td>
							<input type="color" id="wcs-app-bg-color" value="<?php echo esc_attr( $app['input_bg_color'] ?? '#ffffff' ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Input Focus Border Color', 'wpcalibrate-checkout-studio' ); ?></th>
						<td>
							<input type="color" id="wcs-app-focus-border" value="<?php echo esc_attr( $app['input_focus_border'] ?? '#2563eb' ); ?>">
							<p class="description"><?php esc_html_e( 'Accessible focus ring color for keyboard navigation.', 'wpcalibrate-checkout-studio' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Label Text Color', 'wpcalibrate-checkout-studio' ); ?></th>
						<td>
							<input type="color" id="wcs-app-label-color" value="<?php echo esc_attr( $app['label_text_color'] ?? '#374151' ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Primary Button Color', 'wpcalibrate-checkout-studio' ); ?></th>
						<td>
							<input type="color" id="wcs-app-button-bg" value="<?php echo esc_attr( $app['button_bg_color'] ?? '#2563eb' ); ?>">
							<input type="color" id="wcs-app-button-text" value="<?php echo esc_attr( $app['button_text_color'] ?? '#ffffff' ); ?>" style="margin-left: 8px;">
							<p class="description"><?php esc_html_e( 'Place Order button background and text color.', 'wpcalibrate-checkout-studio' ); ?></p>
						</td>
					</tr>
				</table>

				<p class="submit">
					<button type="button" id="wcs-save-appearance-btn" class="button button-primary"><?php esc_html_e( 'Save Appearance', 'wpcalibrate-checkout-studio' ); ?></button>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Render Data & Display Tab.
	 *
	 * @return void
	 */
	private function render_display_tab(): void {
		$all = $this->repo->get_all();
		$display = $all['display'] ?? [];
		?>
		<div class="wcs-tab-pane">
			<h2><?php esc_html_e( 'Data Storage & Display Settings', 'wpcalibrate-checkout-studio' ); ?></h2>

			<form id="wcs-display-form" class="wcs-card" style="margin-top: 16px;">
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Master Plugin Status', 'wpcalibrate-checkout-studio' ); ?></th>
						<td>
							<label class="wcs-switch">
								<input type="checkbox" id="wcs-master-enabled" <?php checked( ! empty( $all['enabled'] ) ); ?>>
								<span class="wcs-slider round"></span>
							</label>
							<p class="description"><?php esc_html_e( 'Quickly toggle custom checkout studio modifications without deactivating the plugin.', 'wpcalibrate-checkout-studio' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Checkout Renderer Mode', 'wpcalibrate-checkout-studio' ); ?></th>
						<td>
							<select id="wcs-renderer-mode" class="wcs-select">
								<option value="auto" <?php selected( $all['renderer_mode'] ?? 'auto', 'auto' ); ?>><?php esc_html_e( 'Auto-Detect (Classic shortcode or Blocks)', 'wpcalibrate-checkout-studio' ); ?></option>
								<option value="classic" <?php selected( $all['renderer_mode'] ?? 'auto', 'classic' ); ?>><?php esc_html_e( 'Force Classic Checkout', 'wpcalibrate-checkout-studio' ); ?></option>
								<option value="blocks" <?php selected( $all['renderer_mode'] ?? 'auto', 'blocks' ); ?>><?php esc_html_e( 'Force Checkout Blocks', 'wpcalibrate-checkout-studio' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Display Locations', 'wpcalibrate-checkout-studio' ); ?></th>
						<td>
							<label><input type="checkbox" id="wcs-disp-order-admin" <?php checked( ! empty( $display['show_in_order_admin'] ) ); ?>> <?php esc_html_e( 'Show custom fields in WooCommerce Order Administration (HPOS meta boxes)', 'wpcalibrate-checkout-studio' ); ?></label><br>
							<label><input type="checkbox" id="wcs-disp-customer-view" <?php checked( ! empty( $display['show_in_customer_view'] ) ); ?>> <?php esc_html_e( 'Show custom fields on Thank You page and Customer My Account order details', 'wpcalibrate-checkout-studio' ); ?></label><br>
							<label><input type="checkbox" id="wcs-disp-emails" <?php checked( ! empty( $display['show_in_emails'] ) ); ?>> <?php esc_html_e( 'Include custom fields in WooCommerce customer & admin notification emails', 'wpcalibrate-checkout-studio' ); ?></label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Data Retention on Uninstall', 'wpcalibrate-checkout-studio' ); ?></th>
						<td>
							<label><input type="checkbox" id="wcs-delete-on-uninstall" <?php checked( ! empty( $all['delete_on_uninstall'] ) ); ?>> <span style="color: #dc2626; font-weight: 600;"><?php esc_html_e( 'Delete all plugin settings on uninstall', 'wpcalibrate-checkout-studio' ); ?></span></label>
							<p class="description"><?php esc_html_e( 'Default is disabled. If enabled, uninstalling the plugin will completely remove all configured fields and settings. Customer orders are always preserved for accounting integrity.', 'wpcalibrate-checkout-studio' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Debug Logging', 'wpcalibrate-checkout-studio' ); ?></th>
						<td>
							<label><input type="checkbox" id="wcs-debug-logging" <?php checked( ! empty( $all['debug_logging'] ) ); ?>> <?php esc_html_e( 'Log detailed checkout processing events to WooCommerce Status > Logs', 'wpcalibrate-checkout-studio' ); ?></label>
						</td>
					</tr>
				</table>

				<p class="submit">
					<button type="button" id="wcs-save-display-btn" class="button button-primary"><?php esc_html_e( 'Save Settings', 'wpcalibrate-checkout-studio' ); ?></button>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Render Import / Export Tab.
	 *
	 * @return void
	 */
	private function render_import_export_tab(): void {
		?>
		<div class="wcs-tab-pane">
			<h2><?php esc_html_e( 'Import / Export & Reset', 'wpcalibrate-checkout-studio' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Export your checkout field architecture and conditional configuration to transfer to staging or production stores. Order history, customer personal data, and licensing keys are never exported.', 'wpcalibrate-checkout-studio' ); ?>
			</p>

			<div class="wcs-grid-two">
				<!-- Export Card -->
				<div class="wcs-card">
					<h3><?php esc_html_e( 'Export Configuration', 'wpcalibrate-checkout-studio' ); ?></h3>
					<p><?php esc_html_e( 'Download a versioned JSON backup of all configured checkout fields, sections, appearance settings, and conditional logic.', 'wpcalibrate-checkout-studio' ); ?></p>
					<button type="button" id="wcs-export-btn" class="button button-primary">
						<span class="dashicons dashicons-download" aria-hidden="true"></span>
						<?php esc_html_e( 'Download Export JSON', 'wpcalibrate-checkout-studio' ); ?>
					</button>
				</div>

				<!-- Import Card -->
				<div class="wcs-card">
					<h3><?php esc_html_e( 'Import Configuration', 'wpcalibrate-checkout-studio' ); ?></h3>
					<p><?php esc_html_e( 'Upload a previously exported JSON configuration file. A backup of current settings is automatically created before applying.', 'wpcalibrate-checkout-studio' ); ?></p>
					<form id="wcs-import-form">
						<input type="file" id="wcs-import-file" accept=".json,application/json" required><br><br>
						<button type="submit" class="button button-secondary">
							<span class="dashicons dashicons-upload" aria-hidden="true"></span>
							<?php esc_html_e( 'Import File', 'wpcalibrate-checkout-studio' ); ?>
						</button>
					</form>
				</div>
			</div>

			<!-- Reset Card -->
			<div class="wcs-card" style="margin-top: 20px; border-inline-start: 4px solid #dc2626;">
				<h3 style="color: #dc2626;"><?php esc_html_e( 'Reset to Defaults', 'wpcalibrate-checkout-studio' ); ?></h3>
				<p><?php esc_html_e( 'Restore all checkout sections and fields back to standard native WooCommerce defaults. An automatic backup of your existing configuration is preserved in the database.', 'wpcalibrate-checkout-studio' ); ?></p>
				<button type="button" id="wcs-reset-btn" class="button button-link-delete">
					<?php esc_html_e( 'Restore Default Settings', 'wpcalibrate-checkout-studio' ); ?>
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Diagnostics Tab.
	 *
	 * @return void
	 */
	private function render_diagnostics_tab(): void {
		$diag = DiagnosticsManager::get_diagnostics();
		$report_text = DiagnosticsManager::get_formatted_report();
		?>
		<div class="wcs-tab-pane">
			<h2><?php esc_html_e( 'System & Environment Diagnostics', 'wpcalibrate-checkout-studio' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Comprehensive technical status of WooCommerce environment, checkout renderer detection, and compatibility checks. This report is sanitized and excludes all sensitive secrets.', 'wpcalibrate-checkout-studio' ); ?>
			</p>

			<div class="wcs-diag-grid" style="margin-top: 16px;">
				<div class="wcs-card">
					<h4><?php esc_html_e( 'Core Versions', 'wpcalibrate-checkout-studio' ); ?></h4>
					<ul>
						<li><strong>WordPress:</strong> <?php echo esc_html( $diag['wordpress_version'] ); ?></li>
						<li><strong>WooCommerce:</strong> <?php echo esc_html( $diag['woocommerce_version'] ); ?></li>
						<li><strong>PHP Runtime:</strong> <?php echo esc_html( $diag['php_version'] ); ?></li>
						<li><strong>Studio Plugin:</strong> v<?php echo esc_html( $diag['plugin_version'] ); ?></li>
					</ul>
				</div>

				<div class="wcs-card">
					<h4><?php esc_html_e( 'Checkout Architecture', 'wpcalibrate-checkout-studio' ); ?></h4>
					<ul>
						<li><strong>HPOS (Custom Orders Table):</strong> <?php echo esc_html( $diag['hpos_enabled'] ); ?></li>
						<li><strong>Blocks Extensibility API:</strong> <?php echo esc_html( $diag['blocks_api_supported'] ); ?></li>
						<li><strong>Active Checkout Page Mode:</strong> <?php echo esc_html( $diag['checkout_page_mode'] ); ?></li>
					</ul>
				</div>
			</div>

			<div class="wcs-card" style="margin-top: 16px;">
				<h3><?php esc_html_e( 'Diagnostic Report', 'wpcalibrate-checkout-studio' ); ?></h3>
				<textarea id="wcs-diag-report" rows="12" class="large-text code" readonly><?php echo esc_textarea( $report_text ); ?></textarea>
				<p style="margin-top: 8px;">
					<button type="button" id="wcs-copy-diag-btn" class="button button-secondary">
						<span class="dashicons dashicons-clipboard" aria-hidden="true"></span>
						<?php esc_html_e( 'Copy System Report', 'wpcalibrate-checkout-studio' ); ?>
					</button>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Render License Tab.
	 *
	 * @return void
	 */
	private function render_license_tab(): void {
		$license_mgr = new LicenseManager();
		$masked_key  = $license_mgr->get_masked_key();
		$status_info = $license_mgr->get_status();
		?>
		<div class="wcs-tab-pane">
			<h2><?php esc_html_e( 'Plugin License', 'wpcalibrate-checkout-studio' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Manage your WPCalibrate Checkout Studio license key for updates and support.', 'wpcalibrate-checkout-studio' ); ?>
			</p>

			<div class="wcs-card" style="margin-top: 16px; max-width: 650px;">
				<div class="notice notice-info inline" style="margin-bottom: 20px;">
					<p>
						<strong><?php esc_html_e( 'Provider Status:', 'wpcalibrate-checkout-studio' ); ?></strong>
						<?php echo esc_html( $status_info['message'] ); ?>
					</p>
					<p class="description">
						<?php esc_html_e( 'Official licensing endpoints will be connected as soon as WPCalibrate documentation is published. Your store checkout functionality will never be interrupted due to licensing server connectivity.', 'wpcalibrate-checkout-studio' ); ?>
					</p>
				</div>

				<form id="wcs-license-form">
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="wcs-license-key"><?php esc_html_e( 'License Key', 'wpcalibrate-checkout-studio' ); ?></label></th>
							<td>
								<input type="password" id="wcs-license-key" class="regular-text" value="<?php echo esc_attr( $masked_key ); ?>" placeholder="<?php esc_attr_e( 'Enter your license key', 'wpcalibrate-checkout-studio' ); ?>">
								<p class="description"><?php esc_html_e( 'Saved keys are automatically masked. Keys are never exposed in HTML attributes or script variables.', 'wpcalibrate-checkout-studio' ); ?></p>
							</td>
						</tr>
					</table>

					<p class="submit">
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Save License Key', 'wpcalibrate-checkout-studio' ); ?></button>
					</p>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Support Tab.
	 *
	 * @return void
	 */
	private function render_support_tab(): void {
		$logo_url = WPCALIBRATE_CHECKOUT_STUDIO_URL . 'branding/icon-dark.png';
		?>
		<div class="wcs-tab-pane">
			<h2><?php esc_html_e( 'WPCalibrate Support & Documentation', 'wpcalibrate-checkout-studio' ); ?></h2>

			<div class="wcs-card" style="margin-top: 16px; max-width: 650px;">
				<div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px;">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="WPCalibrate" width="48" height="48">
					<div>
						<h3 style="margin: 0; font-size: 18px;"><?php esc_html_e( 'WPCalibrate', 'wpcalibrate-checkout-studio' ); ?></h3>
						<p style="margin: 4px 0 0; color: #6b7280;"><?php esc_html_e( 'High-performance WooCommerce tools & extensions', 'wpcalibrate-checkout-studio' ); ?></p>
					</div>
				</div>

				<table class="wcs-support-table">
					<tr>
						<td><strong><?php esc_html_e( 'Support Email:', 'wpcalibrate-checkout-studio' ); ?></strong></td>
						<td><a href="mailto:support@wpcalibrate.com">support@wpcalibrate.com</a></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Official Website:', 'wpcalibrate-checkout-studio' ); ?></strong></td>
						<td><a href="https://wpcalibrate.com" target="_blank" rel="noopener noreferrer">https://wpcalibrate.com</a></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Marketplace:', 'wpcalibrate-checkout-studio' ); ?></strong></td>
						<td><a href="https://marketplace.wpcalibrate.com/" target="_blank" rel="noopener noreferrer">https://marketplace.wpcalibrate.com/</a></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'WhatsApp Support:', 'wpcalibrate-checkout-studio' ); ?></strong></td>
						<td><a href="https://wa.me/447474795976" target="_blank" rel="noopener noreferrer">+447474795976</a></td>
					</tr>
				</table>
			</div>
		</div>
		<?php
	}
}
