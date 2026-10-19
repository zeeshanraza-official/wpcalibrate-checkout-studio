/**
 * WPCalibrate Checkout Studio - Admin Interface Controller
 * Provides drag-and-drop ordering, keyboard reordering, modal CRUD, and AJAX operations.
 */

(function($) {
	'use strict';

	const WCSAdmin = {
		fields: [],
		sections: [],
		conditionalRules: [],
		currentField: null,

		init() {
			if (typeof wcsAdminData !== 'undefined') {
				this.fields = wcsAdminData.fields || [];
				this.sections = wcsAdminData.sections || [];
				this.conditionalRules = wcsAdminData.conditional_rules || [];
			}

			this.bindEvents();
			this.setupDragAndDrop();
			this.updateFieldTypeVisibility();
			this.initConditionsTab();
		},

		bindEvents() {
			const self = this;

			// Section filter dropdown
			$('#wcs-section-filter').on('change', function() {
				const selected = $(this).val();
				if (!selected) {
					$('#wcs-fields-list tr').show();
				} else {
					$('#wcs-fields-list tr').each(function() {
						$(this).toggle($(this).data('section') === selected);
					});
				}
			});

			// Keyboard Move Up / Down
			$('#wcs-fields-list').on('click', '.wcs-btn-move-up', function(e) {
				e.preventDefault();
				const row = $(this).closest('tr');
				const prev = row.prev('tr');
				if (prev.length) {
					row.insertBefore(prev);
					$(this).focus();
				}
			});

			$('#wcs-fields-list').on('click', '.wcs-btn-move-down', function(e) {
				e.preventDefault();
				const row = $(this).closest('tr');
				const next = row.next('tr');
				if (next.length) {
					row.insertAfter(next);
					$(this).focus();
				}
			});

			// Add custom field button
			$('#wcs-add-field-btn').on('click', function() {
				self.openFieldModal(null);
			});

			// Edit field button
			$('#wcs-fields-list').on('click', '.wcs-btn-edit', function(e) {
				e.preventDefault();
				const row = $(this).closest('tr');
				const key = row.data('key');
				const field = self.getFieldByKey(key);
				if (field) {
					self.openFieldModal(field);
				}
			});

			// Delete field button
			$('#wcs-fields-list').on('click', '.wcs-btn-delete', function(e) {
				e.preventDefault();
				if (confirm(wcsAdminData.i18n.confirmDelete)) {
					const row = $(this).closest('tr');
					const key = row.data('key');
					self.fields = self.fields.filter(f => f.key !== key);
					row.remove();
				}
			});

			// Field modal close
			$('.wcs-modal-close, .wcs-modal-cancel, .wcs-modal-backdrop').on('click', function() {
				self.closeFieldModal();
			});

			// Field type change (toggle options textarea)
			$('#wcs-field-type').on('change', function() {
				self.updateFieldTypeVisibility();
			});

			// Field modal form submit
			$('#wcs-field-form').on('submit', function(e) {
				e.preventDefault();
				self.saveFieldModal();
			});

			// Save Builder button
			$('#wcs-save-builder-btn').on('click', function(e) {
				e.preventDefault();
				self.saveBuilder();
			});

			// Save Appearance button
			$('#wcs-save-appearance-btn').on('click', function(e) {
				e.preventDefault();
				self.saveAppearance();
			});

			// Save Display button
			$('#wcs-save-display-btn').on('click', function(e) {
				e.preventDefault();
				self.saveDisplay();
			});

			// Save License button
			$('#wcs-license-form').on('submit', function(e) {
				e.preventDefault();
				self.saveLicense();
			});

			// Add Conditional Rule button
			$('#wcs-add-rule-btn').on('click', function(e) {
				e.preventDefault();
				self.addRule(null);
			});

			// Save Conditional Rules button
			$('#wcs-save-rules-btn').on('click', function(e) {
				e.preventDefault();
				self.saveConditions();
			});

			// Delegate: Delete Rule button
			$('#wcs-conditions-container').on('click', '.wcs-delete-rule-btn', function(e) {
				e.preventDefault();
				const msg = (wcsAdminData.i18n && wcsAdminData.i18n.confirmDeleteRule) ? wcsAdminData.i18n.confirmDeleteRule : 'Are you sure you want to delete this conditional rule?';
				if (confirm(msg)) {
					const card = $(this).closest('.wcs-rule-card');
					card.slideUp(180, function() {
						card.remove();
						self.updateConditionsEmptyState();
					});
				}
			});

			// Delegate: Add Subrule / Condition Criterion
			$('#wcs-conditions-container').on('click', '.wcs-add-subrule-btn', function(e) {
				e.preventDefault();
				const list = $(this).closest('.wcs-subrules-wrapper').find('.wcs-subrules-list');
				self.appendSubruleRow(list, null);
			});

			// Delegate: Remove Subrule / Condition Criterion
			$('#wcs-conditions-container').on('click', '.wcs-remove-subrule-btn', function(e) {
				e.preventDefault();
				const list = $(this).closest('.wcs-subrules-list');
				if (list.children('.wcs-subrule-row').length > 1) {
					$(this).closest('.wcs-subrule-row').slideUp(120, function() {
						$(this).remove();
					});
				} else {
					// Clear value if only one remains
					$(this).closest('.wcs-subrule-row').find('.wcs-subrule-value').val('');
				}
			});

			// Delegate: Operator change (toggle value input for empty checks)
			$('#wcs-conditions-container').on('change', '.wcs-subrule-operator', function() {
				const val = $(this).val();
				const valInput = $(this).closest('.wcs-subrule-row').find('.wcs-subrule-value');
				if (val === 'is_empty' || val === 'is_not_empty') {
					valInput.hide().val('');
				} else {
					valInput.show();
				}
			});

			// Reset to defaults button
			$('#wcs-reset-btn').on('click', function(e) {
				e.preventDefault();
				if (confirm(wcsAdminData.i18n.confirmReset)) {
					self.resetDefaults();
				}
			});

			// Export button
			$('#wcs-export-btn').on('click', function(e) {
				e.preventDefault();
				const url = wcsAdminData.ajaxUrl + '?action=wpcalibrate_checkout_studio_export&wcs_nonce=' + wcsAdminData.nonce;
				window.location.href = url;
			});

			// Import form submit
			$('#wcs-import-form').on('submit', function(e) {
				e.preventDefault();
				self.importConfig();
			});

			// Copy diagnostic report
			$('#wcs-copy-diag-btn').on('click', function(e) {
				e.preventDefault();
				const reportEl = document.getElementById('wcs-diag-report');
				if (reportEl) {
					reportEl.select();
					document.execCommand('copy');
					self.notify(wcsAdminData.i18n.copied, 'success');
				}
			});

			// Close modal on Escape key
			$(document).on('keydown', function(e) {
				if (e.key === 'Escape' && $('#wcs-field-modal').is(':visible')) {
					self.closeFieldModal();
				}
			});
		},

		setupDragAndDrop() {
			const tbody = document.getElementById('wcs-fields-list');
			if (!tbody) return;

			let draggedRow = null;

			tbody.addEventListener('dragstart', function(e) {
				const row = e.target.closest('tr');
				if (row) {
					draggedRow = row;
					row.classList.add('dragging');
					e.dataTransfer.effectAllowed = 'move';
				}
			});

			tbody.addEventListener('dragend', function() {
				if (draggedRow) {
					draggedRow.classList.remove('dragging');
					draggedRow = null;
				}
			});

			tbody.addEventListener('dragover', function(e) {
				e.preventDefault();
				const targetRow = e.target.closest('tr');
				if (targetRow && targetRow !== draggedRow) {
					const rect = targetRow.getBoundingClientRect();
					const next = (e.clientY - rect.top) / (rect.bottom - rect.top) > 0.5;
					tbody.insertBefore(draggedRow, next ? targetRow.nextSibling : targetRow);
				}
			});

			// Make rows draggable by handle
			$('#wcs-fields-list tr').attr('draggable', true);
		},

		getFieldByKey(key) {
			return this.fields.find(f => f.key === key);
		},

		updateFieldTypeVisibility() {
			const type = $('#wcs-field-type').val();
			if (['select', 'radio', 'multiselect'].includes(type)) {
				$('#wcs-options-container').show();
			} else {
				$('#wcs-options-container').hide();
			}
		},

		openFieldModal(field) {
			this.currentField = field;
			const isNew = !field;

			$('#wcs-modal-title').text(isNew ? 'Add Custom Field' : 'Edit Checkout Field');
			$('#wcs-field-id').val(field ? field.id : 'wcs_f_' + Date.now());
			$('#wcs-field-is-core').val(field && field.is_core ? '1' : '0');

			$('#wcs-field-key').val(field ? field.key : '').prop('readonly', field && field.is_core);
			$('#wcs-field-type').val(field ? field.type : 'text').prop('disabled', field && field.is_core);
			$('#wcs-field-label').val(field ? field.label : '');
			$('#wcs-field-section').val(field ? field.section : 'billing');
			$('#wcs-field-placeholder').val(field ? field.placeholder : '');
			$('#wcs-field-width').val(field ? field.width : 'full');
			$('#wcs-field-description').val(field ? field.description : '');

			// Options
			if (field && Array.isArray(field.options)) {
				const lines = field.options.map(opt => `${opt.value} | ${opt.label || opt.value}`);
				$('#wcs-field-options-raw').val(lines.join('\n'));
			} else {
				$('#wcs-field-options-raw').val('');
			}

			// Display checkboxes
			const disp = (field && field.display) || { admin: true, customer: true, emails: true };
			$('#wcs-display-admin').prop('checked', !!disp.admin);
			$('#wcs-display-customer').prop('checked', !!disp.customer);
			$('#wcs-display-emails').prop('checked', !!disp.emails);

			this.updateFieldTypeVisibility();
			$('#wcs-field-modal').fadeIn(150);
			$('#wcs-field-label').focus();
		},

		closeFieldModal() {
			$('#wcs-field-modal').fadeOut(150);
			this.currentField = null;
		},

		saveFieldModal() {
			const key = $('#wcs-field-key').val().trim().toLowerCase();
			if (!key || !/^[a-z0-9_]+$/.test(key)) {
				alert(wcsAdminData.i18n.keyRequired);
				return;
			}

			// Check duplicate key
			const existingIndex = this.fields.findIndex(f => f.key === key);
			const id = $('#wcs-field-id').val();
			const isExisting = this.fields.findIndex(f => f.id === id);

			if (existingIndex !== -1 && isExisting === -1) {
				alert(wcsAdminData.i18n.duplicateKey);
				return;
			}

			// Parse options
			const rawOptionsText = $('#wcs-field-options-raw').val().trim();
			const options = [];
			if (rawOptionsText) {
				const lines = rawOptionsText.split('\n');
				lines.forEach(line => {
					const parts = line.split('|');
					if (parts.length >= 2) {
						options.push({ value: parts[0].trim(), label: parts[1].trim() });
					} else if (parts[0].trim()) {
						options.push({ value: parts[0].trim(), label: parts[0].trim() });
					}
				});
			}

			const fieldData = {
				id: id,
				key: key,
				is_core: $('#wcs-field-is-core').val() === '1',
				type: $('#wcs-field-type').val(),
				label: $('#wcs-field-label').val(),
				section: $('#wcs-field-section').val(),
				placeholder: $('#wcs-field-placeholder').val(),
				width: $('#wcs-field-width').val(),
				description: $('#wcs-field-description').val(),
				enabled: this.currentField ? this.currentField.enabled : true,
				required: this.currentField ? this.currentField.required : false,
				priority: this.currentField ? this.currentField.priority : (this.fields.length + 1) * 10,
				options: options,
				validation: this.currentField ? (this.currentField.validation || {}) : {},
				conditions: this.currentField ? (this.currentField.conditions || {}) : {},
				display: {
					admin: $('#wcs-display-admin').is(':checked'),
					customer: $('#wcs-display-customer').is(':checked'),
					emails: $('#wcs-display-emails').is(':checked'),
				}
			};

			if (isExisting !== -1) {
				this.fields[isExisting] = Object.assign({}, this.fields[isExisting], fieldData);
			} else {
				this.fields.push(fieldData);
			}

			this.closeFieldModal();
			this.renderTableRows();
			this.notify('Field updated. Click "Save Changes" to apply.', 'success');
		},

		renderTableRows() {
			// Update existing table or refresh UI
			location.reload(); // Simplest robust re-render reflecting server order
		},

		saveBuilder() {
			const self = this;
			const updatedFields = [];

			$('#wcs-fields-list tr').each(function(index) {
				const key = $(this).data('key');
				const field = self.getFieldByKey(key);
				if (field) {
					field.priority = (index + 1) * 10;
					field.required = $(this).find('.wcs-row-required').is(':checked');
					field.enabled = $(this).find('.wcs-row-enabled').is(':checked');
					updatedFields.push(field);
				}
			});

			this.notify(wcsAdminData.i18n.saving, 'info');

			$.post(wcsAdminData.ajaxUrl, {
				action: 'wpcalibrate_checkout_studio_save_fields',
				wcs_nonce: wcsAdminData.nonce,
				fields: JSON.stringify(updatedFields),
				sections: JSON.stringify(self.sections)
			}).done(function(res) {
				if (res.success) {
					self.notify(wcsAdminData.i18n.saved, 'success');
				} else {
					self.notify(res.data && res.data.message ? res.data.message : wcsAdminData.i18n.error, 'error');
				}
			}).fail(function() {
				self.notify(wcsAdminData.i18n.error, 'error');
			});
		},

		saveAppearance() {
			const self = this;
			const data = {
				enabled: $('#wcs-app-enabled').is(':checked'),
				field_spacing: $('#wcs-app-field-spacing').val(),
				input_border_radius: $('#wcs-app-border-radius').val(),
				input_border_color: $('#wcs-app-border-color').val(),
				input_bg_color: $('#wcs-app-bg-color').val(),
				input_focus_border: $('#wcs-app-focus-border').val(),
				label_text_color: $('#wcs-app-label-color').val(),
				button_bg_color: $('#wcs-app-button-bg').val(),
				button_text_color: $('#wcs-app-button-text').val()
			};

			$.post(wcsAdminData.ajaxUrl, {
				action: 'wpcalibrate_checkout_studio_save_tab',
				tab: 'appearance',
				wcs_nonce: wcsAdminData.nonce,
				appearance: JSON.stringify(data)
			}).done(function(res) {
				if (res.success) {
					self.notify(wcsAdminData.i18n.saved, 'success');
				} else {
					self.notify(wcsAdminData.i18n.error, 'error');
				}
			});
		},

		saveDisplay() {
			const self = this;
			const display = {
				show_in_order_admin: $('#wcs-disp-order-admin').is(':checked'),
				show_in_customer_view: $('#wcs-disp-customer-view').is(':checked'),
				show_in_emails: $('#wcs-disp-emails').is(':checked')
			};

			$.post(wcsAdminData.ajaxUrl, {
				action: 'wpcalibrate_checkout_studio_save_tab',
				tab: 'display',
				wcs_nonce: wcsAdminData.nonce,
				enabled: $('#wcs-master-enabled').is(':checked') ? 1 : 0,
				renderer_mode: $('#wcs-renderer-mode').val(),
				delete_on_uninstall: $('#wcs-delete-on-uninstall').is(':checked') ? 1 : 0,
				debug_logging: $('#wcs-debug-logging').is(':checked') ? 1 : 0,
				display: JSON.stringify(display)
			}).done(function(res) {
				if (res.success) {
					self.notify(wcsAdminData.i18n.saved, 'success');
				} else {
					self.notify(wcsAdminData.i18n.error, 'error');
				}
			});
		},

		saveLicense() {
			const self = this;
			const key = $('#wcs-license-key').val();

			$.post(wcsAdminData.ajaxUrl, {
				action: 'wpcalibrate_checkout_studio_save_tab',
				tab: 'license',
				wcs_nonce: wcsAdminData.nonce,
				license_key: key
			}).done(function(res) {
				if (res.success) {
					self.notify(res.data.message || wcsAdminData.i18n.saved, 'success');
				} else {
					self.notify(wcsAdminData.i18n.error, 'error');
				}
			});
		},

		resetDefaults() {
			const self = this;
			$.post(wcsAdminData.ajaxUrl, {
				action: 'wpcalibrate_checkout_studio_reset',
				wcs_nonce: wcsAdminData.nonce
			}).done(function(res) {
				if (res.success) {
					alert(res.data.message);
					location.reload();
				} else {
					self.notify(wcsAdminData.i18n.error, 'error');
				}
			});
		},

		importConfig() {
			const self = this;
			const fileInput = document.getElementById('wcs-import-file');
			if (!fileInput.files.length) return;

			const formData = new FormData();
			formData.append('action', 'wpcalibrate_checkout_studio_import');
			formData.append('wcs_nonce', wcsAdminData.nonce);
			formData.append('import_file', fileInput.files[0]);

			self.notify('Importing configuration...', 'info');

			$.ajax({
				url: wcsAdminData.ajaxUrl,
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success(res) {
					if (res.success) {
						alert(res.data.message);
						location.reload();
					} else {
						self.notify(res.data.message || wcsAdminData.i18n.error, 'error');
					}
				},
				error() {
					self.notify(wcsAdminData.i18n.error, 'error');
				}
			});
		},

		escapeHtml(str) {
			if (!str && str !== 0) return '';
			return String(str)
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;')
				.replace(/"/g, '&quot;')
				.replace(/'/g, '&#039;');
		},

		getTargetFieldOptionsHtml(selectedField) {
			let html = '<option value="">-- Select Field to Control --</option>';
			if (this.fields && this.fields.length) {
				this.fields.forEach(f => {
					const sel = (f.key === selectedField) ? 'selected' : '';
					html += `<option value="${this.escapeHtml(f.key)}" ${sel}>${this.escapeHtml(f.label)} (${this.escapeHtml(f.key)})</option>`;
				});
			}
			return html;
		},

		getSourceOptionsHtml(selectedSource) {
			let html = '<optgroup label="Cart & Checkout Context">';
			const contexts = [
				{ key: 'billing_country', label: 'Billing Country (e.g. US, GB, CA)' },
				{ key: 'shipping_country', label: 'Shipping Country (e.g. US, GB, CA)' },
				{ key: 'billing_state', label: 'Billing State / County' },
				{ key: 'shipping_state', label: 'Shipping State / County' },
				{ key: 'cart_total', label: 'Cart Total ($)' },
				{ key: 'cart_subtotal', label: 'Cart Subtotal ($)' },
				{ key: 'cart_item_count', label: 'Cart Item Count' },
				{ key: 'chosen_shipping', label: 'Chosen Shipping Method' },
				{ key: 'chosen_payment', label: 'Chosen Payment Gateway' },
				{ key: 'is_user_logged_in', label: 'User Is Logged In (1 or 0)' },
				{ key: 'needs_shipping', label: 'Cart Needs Shipping (1 or 0)' }
			];

			contexts.forEach(c => {
				const sel = (c.key === selectedSource) ? 'selected' : '';
				html += `<option value="${c.key}" ${sel}>${c.label}</option>`;
			});
			html += '</optgroup>';

			if (this.fields && this.fields.length) {
				html += '<optgroup label="Checkout Fields">';
				this.fields.forEach(f => {
					const sel = (f.key === selectedSource) ? 'selected' : '';
					html += `<option value="${this.escapeHtml(f.key)}" ${sel}>${this.escapeHtml(f.label)} (${this.escapeHtml(f.key)})</option>`;
				});
				html += '</optgroup>';
			}

			return html;
		},

		appendSubruleRow(container, subrule) {
			const source = subrule ? subrule.source : 'billing_country';
			const operator = subrule ? subrule.operator : 'equals';
			const value = subrule && typeof subrule.value !== 'undefined' ? subrule.value : '';
			const isValHidden = (operator === 'is_empty' || operator === 'is_not_empty');

			const rowHtml = `
				<div class="wcs-subrule-row">
					<div class="wcs-subrule-col-source">
						<select class="wcs-subrule-source wcs-select">${this.getSourceOptionsHtml(source)}</select>
					</div>
					<div class="wcs-subrule-col-operator">
						<select class="wcs-subrule-operator wcs-select">
							<option value="equals" ${operator === 'equals' ? 'selected' : ''}>is equal to (=)</option>
							<option value="not_equals" ${operator === 'not_equals' ? 'selected' : ''}>is not equal to (!=)</option>
							<option value="contains" ${operator === 'contains' ? 'selected' : ''}>contains</option>
							<option value="does_not_contain" ${operator === 'does_not_contain' ? 'selected' : ''}>does not contain</option>
							<option value="greater_than" ${operator === 'greater_than' ? 'selected' : ''}>greater than (&gt;)</option>
							<option value="less_than" ${operator === 'less_than' ? 'selected' : ''}>less than (&lt;)</option>
							<option value="is_empty" ${operator === 'is_empty' ? 'selected' : ''}>is empty</option>
							<option value="is_not_empty" ${operator === 'is_not_empty' ? 'selected' : ''}>is not empty</option>
							<option value="in_list" ${operator === 'in_list' ? 'selected' : ''}>in list (comma separated)</option>
						</select>
					</div>
					<div class="wcs-subrule-col-value">
						<input type="text" class="wcs-subrule-value regular-text" placeholder="Value (e.g. US, 50)" value="${this.escapeHtml(value)}" ${isValHidden ? 'style="display:none;"' : ''}>
					</div>
					<div class="wcs-subrule-col-remove">
						<button type="button" class="button wcs-remove-subrule-btn" title="Remove condition">&times;</button>
					</div>
				</div>
			`;
			const row = $(rowHtml);
			container.append(row);
			return row;
		},

		addRule(ruleData) {
			const container = $('#wcs-conditions-container');
			if (!container.length) return;

			$('#wcs-no-rules-msg').hide();

			const ruleId = ruleData && ruleData.id ? ruleData.id : 'rule_' + (container.children('.wcs-rule-card').length + 1);
			const targetField = ruleData && ruleData.target_field ? ruleData.target_field : '';
			const action = ruleData && ruleData.action ? ruleData.action : 'show';
			const match = ruleData && ruleData.match ? ruleData.match : 'all';
			const ruleIndex = container.children('.wcs-rule-card').length + 1;

			const cardHtml = `
				<div class="wcs-rule-card" data-rule-id="${this.escapeHtml(ruleId)}">
					<div class="wcs-rule-card-header">
						<div class="wcs-rule-card-title">
							<span class="dashicons dashicons-randomize" aria-hidden="true"></span>
							<strong>Rule #${ruleIndex}</strong>
						</div>
						<button type="button" class="button button-link-delete wcs-delete-rule-btn" title="Delete Rule">
							<span class="dashicons dashicons-trash" aria-hidden="true"></span> Delete Rule
						</button>
					</div>
					<div class="wcs-rule-card-body">
						<div class="wcs-rule-meta-grid">
							<div class="wcs-rule-meta-col">
								<label>Target Field <span class="required">*</span></label>
								<select class="wcs-rule-target wcs-select" required>
									${this.getTargetFieldOptionsHtml(targetField)}
								</select>
							</div>
							<div class="wcs-rule-meta-col">
								<label>Action</label>
								<select class="wcs-rule-action wcs-select">
									<option value="show" ${action === 'show' ? 'selected' : ''}>Show this field</option>
									<option value="hide" ${action === 'hide' ? 'selected' : ''}>Hide this field</option>
									<option value="require" ${action === 'require' ? 'selected' : ''}>Make field required</option>
									<option value="optional" ${action === 'optional' ? 'selected' : ''}>Make field optional</option>
								</select>
							</div>
							<div class="wcs-rule-meta-col">
								<label>Condition Logic</label>
								<select class="wcs-rule-match wcs-select">
									<option value="all" ${match === 'all' ? 'selected' : ''}>If ALL conditions match (AND)</option>
									<option value="any" ${match === 'any' ? 'selected' : ''}>If ANY condition matches (OR)</option>
								</select>
							</div>
						</div>

						<div class="wcs-subrules-wrapper">
							<div class="wcs-subrules-header">When the following criteria are met:</div>
							<div class="wcs-subrules-list"></div>
							<div class="wcs-subrules-actions">
								<button type="button" class="button button-small wcs-add-subrule-btn">
									<span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span> Add Condition Criterion
								</button>
							</div>
						</div>
					</div>
				</div>
			`;

			const card = $(cardHtml);
			const subrulesList = card.find('.wcs-subrules-list');

			if (ruleData && Array.isArray(ruleData.rules) && ruleData.rules.length) {
				ruleData.rules.forEach(r => this.appendSubruleRow(subrulesList, r));
			} else {
				this.appendSubruleRow(subrulesList, null);
			}

			card.hide().appendTo(container).fadeIn(150);
			this.updateConditionsEmptyState();
		},

		initConditionsTab() {
			const container = $('#wcs-conditions-container');
			if (!container.length) return;

			if (this.conditionalRules && this.conditionalRules.length) {
				container.find('.wcs-rule-card').remove();
				this.conditionalRules.forEach(rule => this.addRule(rule));
			}
			this.updateConditionsEmptyState();
		},

		updateConditionsEmptyState() {
			const count = $('#wcs-conditions-container .wcs-rule-card').length;
			if (count === 0) {
				$('#wcs-no-rules-msg').show();
			} else {
				$('#wcs-no-rules-msg').hide();
			}
		},

		saveConditions() {
			const self = this;
			const rules = [];

			$('#wcs-conditions-container .wcs-rule-card').each(function(index) {
				const card = $(this);
				const targetField = card.find('.wcs-rule-target').val();
				if (!targetField) return;

				const action = card.find('.wcs-rule-action').val() || 'show';
				const match = card.find('.wcs-rule-match').val() || 'all';
				const subrules = [];

				card.find('.wcs-subrule-row').each(function() {
					const source = $(this).find('.wcs-subrule-source').val();
					const operator = $(this).find('.wcs-subrule-operator').val() || 'equals';
					const value = $(this).find('.wcs-subrule-value').val().trim();
					if (source) {
						subrules.push({
							source: source,
							operator: operator,
							value: value
						});
					}
				});

				if (subrules.length) {
					rules.push({
						id: 'rule_' + (index + 1),
						target_field: targetField,
						action: action,
						match: match,
						rules: subrules
					});
				}
			});

			this.notify(wcsAdminData.i18n.saving, 'info');

			$.post(wcsAdminData.ajaxUrl, {
				action: 'wpcalibrate_checkout_studio_save_tab',
				tab: 'conditions',
				wcs_nonce: wcsAdminData.nonce,
				conditional_rules: JSON.stringify(rules)
			}).done(function(res) {
				if (res.success) {
					self.conditionalRules = rules;
					self.notify(wcsAdminData.i18n.saved, 'success');
				} else {
					self.notify(res.data && res.data.message ? res.data.message : wcsAdminData.i18n.error, 'error');
				}
			}).fail(function() {
				self.notify(wcsAdminData.i18n.error, 'error');
			});
		},

		notify(msg, type) {
			const area = $('#wcs-notification-area');
			const notice = $(`<div class="wcs-notice wcs-notice-${type}">${msg}</div>`);
			area.empty().append(notice);
			setTimeout(() => notice.fadeOut(300, () => notice.remove()), 4000);
		}
	};

	$(document).ready(function() {
		WCSAdmin.init();
	});

})(jQuery);
