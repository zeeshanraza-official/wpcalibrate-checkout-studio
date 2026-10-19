/**
 * WPCalibrate Checkout Studio - Frontend Checkout Controller
 * Evaluates dynamic conditional visibility on input changes and updated_checkout.
 */

(function($) {
	'use strict';

	const WCSFrontend = {
		fields: [],

		init() {
			if (typeof wcsCheckoutData !== 'undefined' && Array.isArray(wcsCheckoutData.fields)) {
				this.fields = wcsCheckoutData.fields;
			}

			this.bindEvents();
			this.evaluateConditions();
		},

		bindEvents() {
			const self = this;

			// Re-evaluate on input change
			$('form.checkout').on('change input', 'input, select, textarea', function() {
				self.evaluateConditions();
			});

			// Re-evaluate on WooCommerce AJAX checkout update
			$(document.body).on('updated_checkout', function() {
				self.evaluateConditions();
			});
		},

		evaluateConditions() {
			const self = this;

			this.fields.forEach(field => {
				const conditions = field.conditions;
				if (!conditions || !Array.isArray(conditions.rules) || !conditions.rules.length) {
					return;
				}

				const fieldRow = $('#' + field.key + '_field');
				if (!fieldRow.length) return;

				const matches = self.checkRules(conditions);
				const action = conditions.action || 'show';

				let isVisible = true;
				if (action === 'show') {
					isVisible = matches;
				} else if (action === 'hide') {
					isVisible = !matches;
				}

				if (isVisible) {
					fieldRow.slideDown(150);
				} else {
					fieldRow.slideUp(150);
				}
			});
		},

		checkRules(conditions) {
			const rules = conditions.rules || [];
			const matchMode = conditions.match || 'all';

			for (let i = 0; i < rules.length; i++) {
				const rule = rules[i];
				const passed = this.checkSingleRule(rule);

				if (matchMode === 'any' && passed) {
					return true;
				}
				if (matchMode === 'all' && !passed) {
					return false;
				}
			}

			return matchMode === 'all';
		},

		checkSingleRule(rule) {
			const source = rule.source;
			const operator = rule.operator || 'equals';
			const expected = (rule.value || '').toLowerCase();

			let actual = '';

			// Check input elements
			const el = $('[name="' + source + '"]');
			if (el.length) {
				if (el.is(':checkbox')) {
					actual = el.is(':checked') ? '1' : '0';
				} else if (el.is(':radio')) {
					actual = ($('[name="' + source + '"]:checked').val() || '').toLowerCase();
				} else {
					actual = (el.val() || '').toLowerCase();
				}
			}

			switch (operator) {
				case 'equals':
					return actual === expected;
				case 'not_equals':
					return actual !== expected;
				case 'contains':
					return actual.indexOf(expected) !== -1;
				case 'does_not_contain':
					return actual.indexOf(expected) === -1;
				case 'is_empty':
					return !actual || actual.length === 0;
				case 'is_not_empty':
					return actual && actual.length > 0;
				default:
					return true;
			}
		}
	};

	$(document).ready(function() {
		WCSFrontend.init();
	});

})(jQuery);
