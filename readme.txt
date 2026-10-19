=== WPCalibrate Checkout Studio for WooCommerce ===
Contributors: wpcalibrate
Tags: woocommerce, checkout, checkout fields, checkout manager, checkout studio
Requires at least: 6.5
Tested up to: 7.1.3
Requires PHP: 8.2
Stable tag: 1.0.3
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Visual studio for WooCommerce checkout fields, sections, conditional visibility rules, layout, and appearance.

== Description ==

**WPCalibrate Checkout Studio for WooCommerce** gives store administrators an intuitive, accessible visual workspace to customize WooCommerce checkout fields, sections, rules, layouts, and presentation without writing custom code or modifying core template files.

Preserves native WooCommerce order creation, address processing, tax calculations, payment gateway callbacks, High-Performance Order Storage (HPOS), and the Store API.

### Key Features

* **Visual Field Manager**: Add custom fields, adjust labels, placeholders, widths (full, half, one-third), and default values.
* **Accessible Drag-and-Drop Ordering**: Reorder fields with smooth drag-and-drop or keyboard-accessible move buttons.
* **Core Field Protection**: Relabel and reorder core billing and shipping fields safely without breaking native shipping or address dependencies.
* **Dynamic Conditional Logic**: Show, hide, or require fields dynamically based on cart total, shipping country, user login status, customer role, shipping method, and more.
* **Multi-Format Validation**: Server-enforced validation for emails, phone numbers, minimum/maximum lengths, number bounds, and allowlisted options.
* **Dual Integration**: Works with both classic WooCommerce shortcode checkout and Checkout Blocks via the Additional Checkout Fields API.
* **High-Performance Order Storage (HPOS)**: Full native CRUD order metadata storage without legacy table dependencies.
* **Order & Customer Display**: Display custom values in WooCommerce Admin order details (meta boxes), customer order received / My Account, and notification emails.
* **Appearance Styling**: Scoped styling controls for field spacing, input borders, background colors, focus indicators, and checkout button colors.
* **Safe Configuration Import/Export**: Export versioned configuration JSON to transfer between staging and production. License keys and customer personal data are never exported.
* **Privacy & GDPR Compliance**: Integrated with WordPress Personal Data Exporter and Eraser tools.

### Supported Field Types

1. Text
2. Textarea
3. Email
4. Telephone
5. Number
6. Password
7. Select Dropdown
8. Multi-Select
9. Radio Group
10. Single Checkbox
11. Date Picker
12. Time
13. Hidden Field
14. Section Heading / Notice

== Installation ==

1. Upload the `wpcalibrate-checkout-studio` folder to your `/wp-content/plugins/` directory, or install via the WordPress Plugins menu.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Ensure WooCommerce is active and updated to version 8.8 or higher.
4. Navigate to **WPCalibrate > Checkout Studio** in your WordPress admin menu to configure your checkout fields.

== Frequently Asked Questions ==

= Does this plugin support WooCommerce High-Performance Order Storage (HPOS)? =
Yes. WPCalibrate Checkout Studio is declared compatible with HPOS and utilizes WooCommerce Order CRUD APIs for all order metadata operations.

= Does this plugin work with the new Checkout Block? =
Yes. Custom fields register via the official WooCommerce Additional Checkout Fields API and Store API extension mechanisms for Checkout Blocks.

= Does deactivating the plugin delete my field settings or historical order data? =
No. Deactivation preserves your configurations and completed orders. A deliberate "Delete plugin settings on uninstall" toggle is available in the Data & Display tab if you wish to clean up on uninstallation.

= Can I export my fields to another site? =
Yes. Under the Import / Export tab, you can download a versioned JSON export file and upload it to another store running WPCalibrate Checkout Studio.

== Changelog ==

= 1.0.3 =
* Added dynamic interactive rule card builder for the Conditional Logic tab.
* Added support for multiple criteria per rule with AND/OR logic and live operator value toggling.
* Added automatic synchronization of global conditional rules directly into checkout field configurations.
* Fixed button icon color inheritance ensuring dashicons match white text in primary and secondary hero buttons.
* Standardized flexbox centering across all buttons and dashicons.

= 1.0.2 =
* Fixed broken table cell layout in the Checkout Builder tab where the Enabled column and Action buttons collided.
* Restored native table-cell geometry on action columns and added an inner flex wrapper.
* Reallocated balanced column widths and added responsive horizontal scrolling for narrower viewports.

= 1.0.1 =
* Fixed oversized WPCalibrate admin menu icon in dashboard sidebar; constrained to 20x20px with pure white filter.
* Centered dashicons and text in admin action buttons.
* Added Overview & Guide tab with quick navigation jump cards, technical architecture documentation, and usage instructions.

= 1.0.0 =
* Initial production release.
* Added accessible drag-and-drop checkout builder.
* Added 14 checkout field types.
* Added server-enforced conditional logic engine with AND/OR match modes.
* Added Classic Checkout and Checkout Blocks integration.
* Added HPOS compatibility.
* Added appearance styling engine with scoped CSS.
* Added configuration JSON export and import with automatic database backup.
* Added diagnostics system reporting.
* Added provider-independent licensing architecture.
* Added WordPress personal data exporter and eraser integration.

== Support ==

* Website: https://wpcalibrate.com
* Marketplace: https://marketplace.wpcalibrate.com/
* Support Email: support@wpcalibrate.com
* WhatsApp Support: +447474795976
