# WPCalibrate Checkout Studio for WooCommerce

[![WordPress 6.5+](https://img.shields.io/badge/WordPress-6.5%2B-blue.svg)](https://wordpress.org/)
[![WooCommerce 8.8+](https://img.shields.io/badge/WooCommerce-8.8%2B-purple.svg)](https://woocommerce.com/)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%2B-indigo.svg)](https://php.net/)
[![HPOS Ready](https://img.shields.io/badge/WooCommerce-HPOS%20Compatible-success.svg)](https://woocommerce.com/)
[![Checkout Blocks](https://img.shields.io/badge/WooCommerce-Blocks%20Compatible-blueviolet.svg)](https://woocommerce.com/)
[![License: GPL-2.0-or-later](https://img.shields.io/badge/License-GPL--2.0--or--later-green.svg)](LICENSE)

An accessible, production-grade visual checkout workspace for WooCommerce. Empowers store owners and developers to customize checkout fields, sections, dynamic conditional visibility rules, layout, and appearance visually without writing custom code or overriding core WooCommerce template files.

---

## 🌟 Key Features

### 1. Visual Field Builder & Accessible Ordering
- **Full Field Customization**: Manage labels, placeholder text, field widths (`Full Width`, `Half Left`, `Half Right`, `One Third`), descriptions, and default values.
- **Accessible Reordering**: Intuitive drag-and-drop handles paired with keyboard-accessible **▲ Move Up** and **▼ Move Down** buttons for WCAG 2.1 compliance.
- **Core Field Protection**: Safely adjust labels, placeholders, and ordering for standard WooCommerce billing and shipping fields without breaking native address resolution or payment gateway integrations.

### 2. 14 Supported Checkout Field Types
1. **Text**: Single-line text input with custom min/max length validation.
2. **Textarea**: Multi-line comment/note field with configurable row sizing.
3. **Email**: Email address field with strict RFC 5322 sanitization.
4. **Telephone**: International and domestic phone input with pattern matching.
5. **Number**: Numeric inputs with configurable `min`, `max`, and `step` attributes.
6. **Password**: Masked secure input field.
7. **Select Dropdown**: Single-choice dropdown with allowlisted option verification.
8. **Multi-Select**: Multiple-choice select control with server-validated options.
9. **Radio**: Single-select radio button list with full keyboard tab navigation.
10. **Checkbox**: Single toggle/agreement checkbox (e.g., age confirmation, terms).
11. **Date**: HTML5 accessible date picker with optional range validation.
12. **Time**: HTML5 time picker for delivery slots and appointment scheduling.
13. **Color**: Visual color picker for custom product preferences.
14. **Heading / Section Divider**: Semantic structural dividers to organize complex checkouts into clear logical steps.

### 3. Dynamic Conditional Logic Engine
- **Client-Side UX + Server-Side Enforcement**: Rules trigger instant smooth visibility transitions in browser checkout while being strictly re-evaluated server-side on submission to prevent tampering.
- **Diverse Condition Triggers**:
  - Cart Total and Subtotal ($)
  - Cart Item Count
  - Shipping Country & State
  - Billing Country & State
  - Chosen Shipping Method
  - Chosen Payment Gateway
  - User Authentication Status (`Logged In` vs `Guest`)
  - Cart Needs Shipping status
  - Any custom checkout field value
- **Logical Match Modes**: Supports `ALL (AND)` or `ANY (OR)` condition matching.
- **Flexible Actions**: `Show`, `Hide`, `Make Required`, or `Make Optional`.
- **Fail-Open Resilience**: Hidden conditional fields are never enforced as required, preventing abandoned checkouts.

### 4. Dual Checkout Integration (Classic & Blocks)
- **Classic Shortcode Checkout**: Hooks cleanly into `woocommerce_checkout_fields` with dedicated HTML5 field renderers.
- **Checkout Blocks Parity**: Integrates via the official WooCommerce **Additional Checkout Fields API** (`woocommerce_register_additional_checkout_field`) and Store API extensibility.

### 5. High-Performance Order Storage (HPOS) & Data Storage
- **100% HPOS Compatible**: Explicitly declares compatibility with WooCommerce `custom_order_tables`.
- **Clean Metadata**: All custom field values are stored in order metadata using native `$order->update_meta_data()` and `$order->get_meta()`, strictly prefixed with `_wpcalibrate_*`.
- **Historical Snapshots**: Preserves field definitions with the order to guarantee historical display accuracy even if fields are later modified or removed.

### 6. Order Details & Notification Email Display
- **WooCommerce Admin**: Custom fields display automatically in dedicated order metaboxes in the WooCommerce Order Edit screen.
- **Customer Facing**: Displayed on Order Received (Thank You) pages and the customer My Account order view.
- **Transactional Emails**: Seamlessly formatted into customer invoice and admin order notification emails.

### 7. Appearance & Styling Engine
- Custom controls for vertical field spacing, border radius, input border colors, background colors, accessible focus rings, and checkout button colors.
- Generates scoped CSS for Classic Checkout and compatible styling tokens for Checkout Blocks without template overrides.

### 8. Import / Export & Database Backups
- Transfer configurations easily between Staging and Production via versioned JSON import/export.
- Automatic database snapshot backups created before any reset or import operation.
- Customer personal data and license keys are strictly excluded from exported files.

### 9. Privacy & GDPR Compliance
- Fully integrated with the native WordPress Personal Data Exporter (`wp_privacy_personal_data_exporters`).
- Fully integrated with the native WordPress Personal Data Eraser (`wp_privacy_personal_data_erasers`).

---

## 📋 System Requirements

| Component | Minimum Version | Tested Up To |
|---|---|---|
| **PHP** | 8.2 | 8.4 |
| **WordPress** | 6.5 | 7.1.3 |
| **WooCommerce** | 8.8.0 | 11.2.1 |
| **MySQL / MariaDB** | 5.7 / 10.4 | 8.0+ |

---

## 🚀 Installation

### Option 1: WordPress Dashboard Upload (Recommended)
1. Download `wpcalibrate-checkout-studio-1.0.3.zip` from the [Releases](https://github.com/zeeshanraza-official/wpcalibrate-checkout-studio/releases) page.
2. In your WordPress admin dashboard, navigate to **Plugins > Add New > Upload Plugin**.
3. Choose the ZIP file and click **Install Now**.
4. Click **Activate Plugin**.

### Option 2: Manual Installation via FTP
1. Extract `wpcalibrate-checkout-studio-1.0.3.zip`.
2. Upload the `wpcalibrate-checkout-studio` folder to `/wp-content/plugins/`.
3. Go to **Plugins > Installed Plugins** in WordPress and activate **WPCalibrate Checkout Studio for WooCommerce**.

---

## 📖 Setup & Usage Guide

### 1. Reordering & Editing Fields
1. Navigate to **WPCalibrate > Checkout Studio > Checkout Builder**.
2. Filter by section (`Billing`, `Shipping`, `Order`) or view all fields.
3. Click and drag the handle icon, or use the **▲** and **▼** buttons to adjust order.
4. Toggle the **Required** switch to make fields mandatory, or toggle the **Enabled** switch to show/hide them.
5. Click **Edit** to modify label, placeholder, width, or description.
6. Click **Save Changes**.

### 2. Adding a Custom Field
1. Click the **+ Add Custom Field** button in the top toolbar.
2. Enter a unique lowercase machine key (e.g., `delivery_instructions` or `tax_id`).
3. Select from 14 field types.
4. Set section assignment, width, and display destinations (Admin, Customer, Emails).
5. Click **Save Field**, then click **Save Changes**.

### 3. Creating Conditional Visibility Rules
1. Navigate to the **Conditional Logic** tab.
2. Click **+ Add Conditional Rule**.
3. Select the target field to show, hide, require, or make optional.
4. Configure condition criteria (e.g., *Billing Country is equal to US* or *Cart Total is greater than 100*).
5. Add multiple criteria if required, selecting `ALL` or `ANY` match logic.
6. Click **Save Rules**.

---

## 🛠️ Developer Hooks & Filters

```php
// Filter fields dynamically before checkout rendering
add_filter( 'wpcalibrate_checkout_studio_fields', function( array $fields, string $context ) {
    // Example: modify field label for VIP customers
    if ( is_user_logged_in() && current_user_can( 'vip_customer' ) ) {
        foreach ( $fields as &$field ) {
            if ( 'billing_company' === $field['key'] ) {
                $field['label'] = __( 'Corporate Entity Name', 'textdomain' );
            }
        }
    }
    return $fields;
}, 10, 2 );

// Hook executed after saving custom order metadata
add_action( 'wpcalibrate_checkout_studio_order_field_saved', function( $order_id, $field_key, $field_value ) {
    // Custom sync logic (e.g. ERP or CRM synchronization)
}, 10, 3 );
```

---

## 📝 Changelog

### 1.0.3 (2026-10-19)
- **Feature**: Added dynamic interactive rule card builder for the Conditional Logic tab.
- **Feature**: Added support for multi-criteria conditional rules with `AND` / `OR` match modes and automatic input field toggling for empty checks.
- **Architecture**: Synchronized global conditional rules directly into field models on AJAX save for zero-latency checkout evaluation.
- **Fix**: Standardized button icon color inheritance ensuring dashicons match white text in primary and secondary hero buttons.
- **UI**: Standardized flexbox centering across all buttons and dashicons.

### 1.0.2 (2026-10-19)
- **Fix**: Resolved broken table cell layout in the Checkout Builder tab where the Enabled toggle and Action buttons overlapped.
- **Fix**: Restored native `table-cell` display on action column and wrapped action buttons inside an inner flexbox container.
- **UI**: Reallocated balanced column widths and enabled responsive horizontal scrolling for narrower screens.

### 1.0.1 (2026-10-19)
- **Fix**: Fixed oversized 400px WPCalibrate admin menu logo in the dashboard sidebar; constrained to 20x20px with pure white filter.
- **UI**: Centered dashicons and text in admin action buttons.
- **Feature**: Added Overview & Guide tab with quick navigation jump cards, technical architecture documentation, and usage instructions.

### 1.0.0 (2026-10-19)
- Initial production release.
- 14 registered checkout field types.
- Accessible drag-and-drop & keyboard reordering.
- Server-side and client-side conditional logic engine.
- Classic Checkout & Checkout Blocks parity.
- High-Performance Order Storage (HPOS) metadata CRUD support.
- Scoped CSS appearance engine.
- Versioned JSON configuration import/export with automated database backup.
- Privacy manager for GDPR personal data export and erasure.

---

## 📄 License & Author

- **Author**: [WPCalibrate](https://wpcalibrate.com)
- **Support**: support@wpcalibrate.com | Hotline/WhatsApp: +447474795976
- **Marketplace**: [marketplace.wpcalibrate.com](https://marketplace.wpcalibrate.com/)
- **License**: GNU General Public License v2.0 or later ([GPL-2.0-or-later](LICENSE))
