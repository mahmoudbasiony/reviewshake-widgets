'use strict';

function _asyncToGenerator(fn) { return function () { var gen = fn.apply(this, arguments); return new Promise(function (resolve, reject) { function step(key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { return Promise.resolve(value).then(function (value) { step("next", value); }, function (err) { step("throw", err); }); } } return step("next"); }); }; }

/**
 * Back-end scripts.
 *
 * Scripts to run on the WordPress dashboard.
 */

(function ($) {
	/**
  * Get app state and continue creating the account.
  */
	$(document).ready(function () {
		var appState = reviewshake_widgets_params.state;
		var tab = appState.tab;
		var sourceName = appState.source_name;
		var sourceUrl = appState.source_url;
		var googlePlaceId = appState.google_place_id;

		console.log(appState);
		if (tab && 'setup' === tab && sourceName && sourceUrl && appState.account_status && appState.source_status && ('pending' === appState.account_status || 'on_hold' === appState.account_status || 'pending' === appState.source_status)) {
			var form = $('#create_review_source_form');
			var isAccountExists = form.data('account-exists');
			var secToSleep = appState.sec_to_sleep;

			console.log('Waiting' + secToSleep + ' seconds');

			// Show Loader.
			showLoader('reviewshake-widgets-setup-wrap', form);

			/**
    * Continue creating the account.
    */
			createAccount(sourceName, sourceUrl, googlePlaceId, form, isAccountExists, secToSleep);
		}
	});

	/*
  * Adds placeholder on change review name select options.
  */
	$(document).on('change', '.review-sources', function (e) {
		var placeholder = $(this).find('option:selected').data('placeholder-url');

		var sourceName = $(this).find('option:selected').val();

		if ('google' === sourceName) {
			$(this).closest('.review-sources-row').find('.review-sources-url-column').hide();
			$(this).closest('.review-sources-row').find('.review-sources-url-column.google-places-select').show();
		} else {
			$(this).closest('.review-sources-row').find('.review-sources-url-column').show();
			$(this).closest('.review-sources-row').find('.review-sources-url-column.google-places-select').hide();
			$(this).closest('.review-sources-row').find('input[type="text"].review-sources-url').attr('placeholder', placeholder);
		}
	});

	/*
  * Get google places from google maps places API.
  */
	$(document).ready(function () {
		googlePlaceSelect();
	});

	/*
  * On submit add review source form.
  */
	$(document).on('submit', '#create_review_source_form', function (e) {
		e.preventDefault();

		var form = $(this);

		var sourceUrl = form.find('input[name="source_url"]').val();
		var source = form.find('select[name="source_name"] option:selected').val().toLowerCase();
		var isAccountExists = form.data('account-exists');
		var sourceID = parseInt(form.attr('data-review-source-id'));
		var googlePlaceId = '';

		if ('google' === source) {
			sourceUrl = form.find('select[name="source_url"] option:selected').text();
			googlePlaceId = form.find('select[name="source_url"] option:selected').val();
		}

		// Define errors
		var errors = false;
		// First remove all errors.
		form.find('.error').remove();

		// Source name is required.
		if (source.length <= 0) {
			form.find('.review-sources').after('<span class="error">' + reviewshake_widgets_params.translations.required + '</span');
			errors = true;
		}

		// Source URL is required.
		if (sourceUrl.length <= 0 || 'google' === source && (googlePlaceId == undefined || googlePlaceId.length <= 0)) {
			form.find('.error').remove();
			form.find('.review-sources-url-column').append('<span class="error">' + reviewshake_widgets_params.translations.required + '</span');
			errors = true;
		}

		// Validate errors
		if (errors) {
			return false;
		}

		// Show Loader.
		showLoader('reviewshake-widgets-setup-wrap', form);

		/**
   * Create Reviewshake account
   *
   * @param {string}
   * @param {string}
   * @param {object}
   * @param {bool}
   */
		createAccount(source, sourceUrl, googlePlaceId, form, isAccountExists);
	});

	/*
  * On click delete review source button.
  */
	$(document).on('click', '.delete-review-source', function (e) {
		e.preventDefault();

		var reviewSourceRow = $(this).closest('.review-sources-row');
		var sourceID = reviewSourceRow.data('review-source-id');
		var id = parseInt(sourceID.replace('rs', ''));

		if (confirm(reviewshake_widgets_params.translations.confirm_delete)) {
			// Show Loader.
			showLoader('reviewshake-widgets-setup-wrap', reviewSourceRow);

			// Delete review source.
			deleteReviewSource(id, reviewSourceRow);
		}
	});

	/*
  * On click upgrade account button.
  */
	$(document).on('click', '#upgrade-setup-link', function (e) {
		e.preventDefault();

		var element = $(this);

		// Open the upgrade link in a new tab.
		var href = element.attr('href');
		window.open(href, '_blank');

		var data = {
			'action': 'reviewshake_renders_review_source_form',
			'nonce': reviewshake_widgets_params.nonce
		};

		getReviewSourceForm(data).then(function (result) {
			if (result.success && result.hasOwnProperty('data') && result.data.hasOwnProperty('form')) {
				var form = result.data.form;

				// Hide the upgrade link and append the review source form.
				element.closest('.review-sources-upgrade-table').hide();
				element.closest('.reviewshake-widgets-review-sources-container').append(form);

				// Set data requires upgrade to 1.
				$('.create-review-source-form').attr('data-requires-upgrade', '1');
			}
		});
	});

	/*
  * On click delete widget button.
  */
	$(document).on('click', '.delete-widget', function (e) {
		e.preventDefault();

		var widget = $(this).closest('.reviewshake-widgets-widget');
		var widgetID = widget.attr('data-widget-id');

		if (confirm(reviewshake_widgets_params.translations.confirm_delete)) {
			// Show Loader.
			showLoader('reviewshake-widgets-setup-wrap', widget);

			// Delete widget.
			deleteWidget(widgetID, widget);
		}
	});

	/*
  * On change minimun star rating input form.
  */
	$(document).on('click', '.widget_min_star_rating .star_rating', function (event) {
		$('.star_rating').removeClass('selected');

		// Initialize the exStarRating array.
		var exStarRating = [];

		// Get the current clicked star rate.
		var starRate = $(this).data('star-rate');

		for (var i = 1; i < starRate; i++) {
			// Push the previous star rates to the array.
			exStarRating.push(i);
		}

		exStarRating = JSON.stringify(exStarRating);

		$(this).addClass('selected');
		$(this).closest('.widget_min_star_rating').find('input[name="ex_star_rating"]').val(exStarRating);
	});

	/**
  * On submit create/update widget form.
  */
	$(document).on('submit', '#create_widget_form', function (e) {
		e.preventDefault();

		var form = $(this);

		var widgetID = parseInt(form.attr('data-widget-id'));
		var name = form.find('input[name="name"]').val();
		var type = form.find('select[name="widget_type"] option:selected').val();

		// Define errors
		var errors = false;
		// First remove all errors.
		form.find('.error').remove();

		// Widget name is a required field.
		if (name.length <= 0) {
			form.find('input[name="name"]').after('<span class="error">' + reviewshake_widgets_params.translations.required + '</span');
			errors = true;
		}

		// Widget type is a required field.
		if (type.length <= 0) {
			form.find('select[name="widget_type"]').after('<span class="error">' + reviewshake_widgets_params.translations.required + '</span');
			errors = true;
		}

		/*
   * Validation errors.
   */
		if (errors) {
			$("html, body").animate({ scrollTop: $('.error').first().offset().top - 70 }, 800);
			return false;
		}

		var formData = new FormData(form[0]);

		var plainFormData = Object.fromEntries(formData.entries());

		console.log(plainFormData);

		if (!!widgetID) {
			widgetID = parseInt(widgetID);
			plainFormData['id'] = widgetID;

			/**
    * Update widget.
    */
			updateWidget(form, plainFormData);
		} else {
			/**
    * Create new widget
    */
			createWidget(form, plainFormData);
		}
	});

	/*
  * On click Add/Edit widget
  */
	$(document).on('click', '.add-new-widget, .edit-widget', function (e) {
		e.preventDefault();

		var widget = $(this).closest('.reviewshake-widgets-widget');
		var widgetID = !!widget.attr('data-widget-id') ? widget.attr('data-widget-id') : 0;

		var data = {
			'action': 'reviewshake_renders_widget_form',
			'nonce': reviewshake_widgets_params.nonce,
			'widget_id': widgetID
		};

		getWidgetForm(data).then(function (result) {
			if (result.success && result.hasOwnProperty('data') && result.data.hasOwnProperty('form')) {
				var form = result.data.form;

				$('.reviewshake-widgets-setup-wrap').remove();
				postscribe('#reviewshake-widgets-setup', form);

				$("html, body").animate({ scrollTop: $('#reviewshake-widgets').offset().top - 30 }, 200);
				$('.color_field').wpColorPicker();
			}
		});
	});

	/*
  * On submit connect account form
  */
	$(document).on('submit', '#connect_account_form', function (event) {
		event.preventDefault();

		var form = $(this);
		var subdomain = form.find('input[name="subdomain"]').val();
		var apiKey = form.find('input[name="api_key"]').val();

		// Define errors
		var errors = false;
		// First remove all errors.
		form.find('.error').remove();

		// Account subdomain is a required field.
		if (subdomain.length <= 0) {
			form.find('input[name="subdomain"]').after('<span class="error">' + reviewshake_widgets_params.translations.required + '</span');
			errors = true;
		}

		// Account API key is a required field.
		if (apiKey.length <= 0) {
			form.find('input[name="api_key"]').after('<span class="error">' + reviewshake_widgets_params.translations.required + '</span');
			errors = true;
		}

		// Validate errors.
		if (errors) {
			return false;
		}

		if (confirm(reviewshake_widgets_params.translations.another_account)) {
			// Show loader
			showLoader('reviewshake-widgets-account', form);

			// Get the account Info
			getAccountInfo(subdomain, apiKey);
		}
	});

	/*
  * On click add connect another account.
  */
	$(document).on('click', '.connect-another-account', function (e) {
		e.preventDefault();

		var form = reviewshake_widgets_params.newAccountForm;
		$('.reviewshake-widgets-account-wrap').replaceWith(form);
	});

	/*
  * On click claim account button.
  */
	$(document).on('click', '#claim-account', function (e) {
		e.preventDefault();

		$(this).closest('.account-links-wrap').hide();
		$(this).closest('.claim-account-wrap').find('.claiming-in-progress-wrap').show();

		var href = $(this).data('href');
		window.open(href, '_blank');
	});

	$(document).ready(function () {
		// Add Color Picker to all inputs that have 'color-field' class
		$('.color_field').wpColorPicker();
	});

	/*
  * On click popup close button.
  */
	$(document).on('click', '.popup-close', function (e) {
		// Close popup
		closePopup();

		e.preventDefault();
	});

	/**
  * Get widget create/edit form
  *
  * @param {object} data The object data to send to ajax
  *
  * @return {object|WP_Error}
  */
	var getReviewSourceForm = function () {
		var _ref = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee(data) {
			var result;
			return regeneratorRuntime.wrap(function _callee$(_context) {
				while (1) {
					switch (_context.prev = _context.next) {
						case 0:
							result = void 0;
							_context.prev = 1;
							_context.next = 4;
							return $.ajax({
								url: reviewshake_widgets_params.ajax_url,
								type: 'POST',
								data: data
							});

						case 4:
							result = _context.sent;
							return _context.abrupt('return', result);

						case 8:
							_context.prev = 8;
							_context.t0 = _context['catch'](1);

							console.error(_context.t0);

						case 11:
						case 'end':
							return _context.stop();
					}
				}
			}, _callee, undefined, [[1, 8]]);
		}));

		return function getReviewSourceForm(_x) {
			return _ref.apply(this, arguments);
		};
	}();

	/**
  * Google places autocomplete predictions.
  *
  * @return void
  */
	var googlePlaceSelect = function googlePlaceSelect() {
		$(".review-sources-row .google-places-select select").select2({
			ajax: {
				url: reviewshake_widgets_params.ajax_url,
				dataType: 'json',
				delay: 100,
				data: function data(params) {
					return {
						q: params.term,
						action: 'reviewshake_google_places_predictions',
						nonce: reviewshake_widgets_params.nonce,
						input: params.term
					};
				},
				processResults: function processResults(data, params) {
					return {
						results: data.data
					};
				},
				cache: true
			},
			placeholder: reviewshake_widgets_params.translations.google_places_placeholder,
			language: {
				noResults: function noResults() {
					return reviewshake_widgets_params.translations.no_places_found;
				}
			},
			allowClear: true,
			minimumInputLength: 1,
			templateResult: formatPlace,
			templateSelection: formatPlaceSelection
		});
	};

	/**
  * Formats google place results.
  *
  * @param {Object} place The google place object.
  *
  * @return {String} The formatted result
  */
	var formatPlace = function formatPlace(place) {
		if (place.loading) {
			return place.text;
		}

		var container = $("<div class='select2-result-places clearfix'>" + "<div class='select2-result-places__meta'>" + "<div class='select2-result-places__title'></div>" + "</div>" + "</div>");

		container.find(".select2-result-places__title").text(place.text);

		if (place.id === 'reviewshake_powered_by_google') {
			container.find(".select2-result-places__title").append('<img src="' + place.image_url + '" />');
		}

		return container;
	};

	/**
  * Formats google place selection.
  *
  * @param {Object} place The google place object.
  *
  * @return {String} The formatted text
  */
	var formatPlaceSelection = function formatPlaceSelection(place) {
		return place.text || place.description;
	};

	/**
  * Get account info
  *
  * @param {string} subdomain The account subdomain.
  * @param {string} apiKey    The account API key.
  *
  * @return void
  */
	var getAccountInfo = function () {
		var _ref2 = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2(subdomain, apiKey) {
			var getAccountResponse, responseJson, html;
			return regeneratorRuntime.wrap(function _callee2$(_context2) {
				while (1) {
					switch (_context2.prev = _context2.next) {
						case 0:
							_context2.next = 2;
							return fetch(reviewshake_widgets_params.site_url + '/wp-json/reviewshake/v1/account/' + apiKey + '/' + subdomain, {
								method: 'GET',
								headers: {
									'content-type': 'application/json',
									'X-WP-Nonce': reviewshake_widgets_params.wp_rest_nonce
								}
							});

						case 2:
							getAccountResponse = _context2.sent;
							_context2.next = 5;
							return getAccountResponse.json();

						case 5:
							responseJson = _context2.sent;


							if (responseJson.hasOwnProperty('data') && responseJson.data.hasOwnProperty('attributes')) {
								html = responseJson.html;

								$('.reviewshake-widgets-account').replaceWith(html);
							}

							// Hide Loader.
							hideLoader();

						case 8:
						case 'end':
							return _context2.stop();
					}
				}
			}, _callee2, undefined);
		}));

		return function getAccountInfo(_x2, _x3) {
			return _ref2.apply(this, arguments);
		};
	}();

	/**
  * Get widget create/edit form
  *
  * @param {object} data The object data to send to ajax
  *
  * @return {object|WP_Error}
  */
	var getWidgetForm = function () {
		var _ref3 = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee3(data) {
			var result;
			return regeneratorRuntime.wrap(function _callee3$(_context3) {
				while (1) {
					switch (_context3.prev = _context3.next) {
						case 0:
							result = void 0;
							_context3.prev = 1;
							_context3.next = 4;
							return $.ajax({
								url: reviewshake_widgets_params.ajax_url,
								type: 'POST',
								data: data
							});

						case 4:
							result = _context3.sent;
							return _context3.abrupt('return', result);

						case 8:
							_context3.prev = 8;
							_context3.t0 = _context3['catch'](1);

							console.error(_context3.t0);

						case 11:
						case 'end':
							return _context3.stop();
					}
				}
			}, _callee3, undefined, [[1, 8]]);
		}));

		return function getWidgetForm(_x4) {
			return _ref3.apply(this, arguments);
		};
	}();

	/**
  * Update the widget
  *
  * @param {element} form     The form element
  * @param {object}  formData The form data object
  *
  * @return void
  */
	var updateWidget = function () {
		var _ref4 = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee4(form, formData) {
			var widgetID, updateWidgetResponse, responseJson, embed, message, detail;
			return regeneratorRuntime.wrap(function _callee4$(_context4) {
				while (1) {
					switch (_context4.prev = _context4.next) {
						case 0:
							// Show preview loader.
							showPreviewLoader();

							widgetID = formData.id;

							// Send the update widget request to rest API.

							_context4.next = 4;
							return fetch(reviewshake_widgets_params.site_url + '/wp-json/reviewshake/v1/widgets/' + widgetID, {
								method: 'PUT',
								headers: {
									'content-type': 'application/json',
									'X-WP-Nonce': reviewshake_widgets_params.wp_rest_nonce
								},
								body: JSON.stringify(formData)
							});

						case 4:
							updateWidgetResponse = _context4.sent;
							_context4.next = 7;
							return updateWidgetResponse.json();

						case 7:
							responseJson = _context4.sent;


							console.log(responseJson);

							embed = '';

							if (responseJson.hasOwnProperty('status') && 200 === responseJson.status) {
								embed = responseJson.embed;

								// Add a version to script to prevent broweser caching.
								embed = embed + '?v=' + Date.now();

								// Append the preview widget asynchronously.
								postscribe('#widget_live_preview', '<script src="' + embed + '"></script>', {
									done: function done() {
										console.log('done');

										// Hide preview loader
										hidePreviewLoader();
									}
								});
							}

							if (!updateWidgetResponse.ok && responseJson.hasOwnProperty('message') && responseJson.hasOwnProperty('message')) {
								message = responseJson.message;
								detail = responseJson.data.detail;

								showPopup(message, detail, true, 'error');
							}

						case 12:
						case 'end':
							return _context4.stop();
					}
				}
			}, _callee4, undefined);
		}));

		return function updateWidget(_x5, _x6) {
			return _ref4.apply(this, arguments);
		};
	}();

	/**
  * Create a new widget
  *
  * @param {element} form     The form element
  * @param {object}  formData The form data object
  *
  * @return void
  */
	var createWidget = function () {
		var _ref5 = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee5(form, formData) {
			var createWidgetResponse, responseJson, embed, widgetID, message, detail;
			return regeneratorRuntime.wrap(function _callee5$(_context5) {
				while (1) {
					switch (_context5.prev = _context5.next) {
						case 0:
							// Show preview loader.
							showPreviewLoader();

							// Send the create widget request to rest API.
							_context5.next = 3;
							return fetch(reviewshake_widgets_params.site_url + '/wp-json/reviewshake/v1/widgets', {
								method: 'POST',
								headers: {
									'content-type': 'application/json',
									'X-WP-Nonce': reviewshake_widgets_params.wp_rest_nonce
								},
								body: JSON.stringify(formData)
							});

						case 3:
							createWidgetResponse = _context5.sent;
							_context5.next = 6;
							return createWidgetResponse.json();

						case 6:
							responseJson = _context5.sent;


							console.log(responseJson);

							embed = '';

							if (responseJson.hasOwnProperty('status') && 200 == responseJson.status) {
								widgetID = responseJson.id;

								// Add the widget ID data attribute.

								form.attr('data-widget-id', widgetID);

								embed = responseJson.embed;
								// Add a version to script to prevent broweser caching.
								embed = embed + '?v=' + Date.now();

								// Append the preview widget asynchronously.
								postscribe('#widget_live_preview', '<script src="' + embed + '"></script>', {
									done: function done() {
										console.log('done');

										// Hide preview loader
										hidePreviewLoader();
									}
								});
							}

							if (!createWidgetResponse.ok && responseJson.hasOwnProperty('message') && responseJson.hasOwnProperty('data')) {
								message = responseJson.message;
								detail = responseJson.data.detail;


								showPopup(message, detail, true, 'error');
							}

						case 11:
						case 'end':
							return _context5.stop();
					}
				}
			}, _callee5, undefined);
		}));

		return function createWidget(_x7, _x8) {
			return _ref5.apply(this, arguments);
		};
	}();

	/**
  * Delete Reviewshake widget
  *
  * @param {int}     widgetID The target widget ID.
  * @param {element} widget   The widget element.
  *
  * @return void
  */
	var deleteWidget = function () {
		var _ref6 = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee6(widgetID, widget) {
			var deleteWidgetResponse, responseJson, html, message, detail;
			return regeneratorRuntime.wrap(function _callee6$(_context6) {
				while (1) {
					switch (_context6.prev = _context6.next) {
						case 0:
							_context6.next = 2;
							return fetch(reviewshake_widgets_params.site_url + '/wp-json/reviewshake/v1/widgets/' + widgetID, {
								method: 'DELETE',
								headers: {
									'content-type': 'application/json',
									'X-WP-Nonce': reviewshake_widgets_params.wp_rest_nonce
								}
							});

						case 2:
							deleteWidgetResponse = _context6.sent;
							_context6.next = 5;
							return deleteWidgetResponse.json();

						case 5:
							responseJson = _context6.sent;


							if (deleteWidgetResponse.ok && responseJson.deleted) {
								console.log('Widget deleted successfully');
								html = responseJson.html;


								$('#reviewshake-widgets-setup').remove();

								postscribe('#reviewshake-tab-setup', html);
							} else {
								$('.reviewshake-widgets-setup-wrap').show();
							}

							$('.color_field').wpColorPicker();

							// Google place select2
							googlePlaceSelect();

							if (!deleteWidgetResponse.ok && responseJson.hasOwnProperty('message') && responseJson.hasOwnProperty('data')) {
								message = responseJson.message;
								detail = responseJson.data.detail;


								showPopup(message, detail, true, 'error');
							}

							// Hide Loader.
							hideLoader();

						case 11:
						case 'end':
							return _context6.stop();
					}
				}
			}, _callee6, undefined);
		}));

		return function deleteWidget(_x9, _x10) {
			return _ref6.apply(this, arguments);
		};
	}();

	/**
  * Delete review source.
  *
  * @param {int}     id              The target source ID.
  * @param {element} reviewSourceRow The review source element.
  *
  * @return void
  */
	var deleteReviewSource = function () {
		var _ref7 = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee7(sourceID, reviewSource) {
			var deleteSourceResponse, responseJson, html, message, detail;
			return regeneratorRuntime.wrap(function _callee7$(_context7) {
				while (1) {
					switch (_context7.prev = _context7.next) {
						case 0:
							_context7.next = 2;
							return fetch(reviewshake_widgets_params.site_url + '/wp-json/reviewshake/v1/review_sources/' + sourceID, {
								method: 'DELETE',
								headers: {
									'Content-Type': 'application/json',
									'X-WP-Nonce': reviewshake_widgets_params.wp_rest_nonce
								}
							});

						case 2:
							deleteSourceResponse = _context7.sent;
							_context7.next = 5;
							return deleteSourceResponse.json();

						case 5:
							responseJson = _context7.sent;


							if (responseJson.deleted) {
								console.log('Review source deleted successfully!');
								html = responseJson.html;


								$('#reviewshake-widgets-setup').remove();

								postscribe('#reviewshake-tab-setup', html);
							} else {
								console.log(responseJson);
								$('.reviewshake-widgets-setup-wrap').show().find(reviewSource).remove();

								if (!deleteSourceResponse.ok && responseJson.hasOwnProperty('message') && responseJson.hasOwnProperty('data')) {
									message = responseJson.message;
									detail = responseJson.data.detail;


									showPopup(message, detail, true, 'error');
								}
							}

							// WP color picker
							$('.color_field').wpColorPicker();

							// Google place select2
							googlePlaceSelect();

							// Hide Loader.
							hideLoader();

						case 10:
						case 'end':
							return _context7.stop();
					}
				}
			}, _callee7, undefined);
		}));

		return function deleteReviewSource(_x11, _x12) {
			return _ref7.apply(this, arguments);
		};
	}();

	/**
  * Create the reviewshake account and add the review sources.
  *
  * @param {string} source The review source name
  * @param {string} sourceUrl The review source url
  * @param {string} googlePlaceId The google place ID
  * @param {element} form The form element
  * @param {boolean} isAccountExists Whether the account is already exist
  *
  * @return void
  */
	var createAccount = function () {
		var _ref8 = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee9(source, sourceUrl, googlePlaceId, form, isAccountExists) {
			var secToSleep = arguments.length > 5 && arguments[5] !== undefined ? arguments[5] : 20;

			var sourcesCount, pricingPlan, body, createAccountResponse, createAccountJson, attributes, accountDomain, email, tryGetAccount, listWidgetsJson, _body2, addReviewSources;

			return regeneratorRuntime.wrap(function _callee9$(_context9) {
				while (1) {
					switch (_context9.prev = _context9.next) {
						case 0:
							sourcesCount = parseInt(form.attr('data-sources-count'));
							pricingPlan = form.attr('data-pricing-plan');

							// If is first review source.

							if (sourcesCount === 0 && '' == isAccountExists) {
								form.addClass('first');
								form.closest('#reviewshake-widgets').find('.creating-account-notice').show();
							}

							/*
        * If account doesn't exist.
        */

							if (!(isAccountExists.length <= 0)) {
								_context9.next = 21;
								break;
							}

							body = {
								'source': source,
								'sourceUrl': sourceUrl,
								'googlePlaceId': googlePlaceId
							};

							// Send the create new account request to rest API 

							_context9.next = 7;
							return fetch(reviewshake_widgets_params.site_url + '/wp-json/reviewshake/v1/account/', {
								method: 'POST',
								headers: {
									'Content-Type': 'application/json',
									'X-WP-Nonce': reviewshake_widgets_params.wp_rest_nonce
								},
								body: JSON.stringify(body)
							});

						case 7:
							createAccountResponse = _context9.sent;
							_context9.next = 10;
							return createAccountResponse.json();

						case 10:
							createAccountJson = _context9.sent;


							console.log('beforeSleep');

							// Wait for x seconds to get account created.
							_context9.next = 14;
							return new Promise(function (resolve) {
								return isAccountExists == '' ? setTimeout(resolve, secToSleep * 1000) : resolve();
							});

						case 14:

							console.log('afterSleep');

							attributes = createAccountJson.data.attributes;
							accountDomain = createAccountJson.data.links.account_domain;
							email = attributes.email;

							// Set interval every 5 seconds to check account fully created.

							tryGetAccount = setInterval(_asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee8() {
								var getAccountResponse, getAccountJson, _attributes, id, type, apiKey, convertToFree, listWidgetsJson, count, _body, addReviewSources;

								return regeneratorRuntime.wrap(function _callee8$(_context8) {
									while (1) {
										switch (_context8.prev = _context8.next) {
											case 0:
												_context8.next = 2;
												return fetch(reviewshake_widgets_params.site_url + '/wp-json/reviewshake/v1/account/?' + new URLSearchParams({
													email: email
												}), {
													method: 'GET',
													headers: {
														'Content-Type': 'application/json',
														'X-WP-Nonce': reviewshake_widgets_params.wp_rest_nonce
													}
												});

											case 2:
												getAccountResponse = _context8.sent;
												_context8.next = 5;
												return getAccountResponse.json();

											case 5:
												getAccountJson = _context8.sent;

												if (!(getAccountJson.hasOwnProperty('data') && getAccountJson.data.hasOwnProperty('attributes'))) {
													_context8.next = 36;
													break;
												}

												// Clear the interval.
												clearInterval(tryGetAccount);

												_attributes = getAccountJson.data.attributes;
												id = getAccountJson.data.id;
												type = getAccountJson.data.type;
												apiKey = _attributes.api_key;

												accountDomain = _attributes.account_domain;

												console.log('Get account created success');
												console.log('before trial api');

												// If account is on trial plan.

												if (!('' == pricingPlan || 'trial' === pricingPlan)) {
													_context8.next = 20;
													break;
												}

												_context8.next = 18;
												return fetch(reviewshake_widgets_params.site_url + '/wp-json/reviewshake/v1/account/' + apiKey + '/' + accountDomain, {
													method: 'PUT',
													headers: {
														'Content-Type': 'application/json',
														'X-WP-Nonce': reviewshake_widgets_params.wp_rest_nonce
													}
												});

											case 18:
												convertToFree = _context8.sent;

												console.log('Account get a free plan');

											case 20:

												console.log('after trial api');

												_context8.next = 23;
												return listWidgets();

											case 23:
												listWidgetsJson = _context8.sent;

												if (!(listWidgetsJson.hasOwnProperty('rscode') && 200 === listWidgetsJson.rscode)) {
													_context8.next = 35;
													break;
												}

												count = listWidgetsJson.hasOwnProperty('count') ? parseInt(listWidgetsJson.count) : 0;


												console.log('Widgets listed successfuly');
												console.log('You have ' + count + ' of registered Widgets');

												_body = {
													'apikey': apiKey,
													'subdomain': accountDomain,
													'source': source,
													'sourceUrl': sourceUrl,
													'googlePlaceId': googlePlaceId
												};

												/**
             * Adds the review source to the currently created account.
             *
             * @param {object}  body - The request body.
             * @param {element} form - The create review source form.
             */

												_context8.next = 31;
												return addReviewSource(_body, form);

											case 31:
												addReviewSources = _context8.sent;


												// Wp Color Picker.
												$('.color_field').wpColorPicker();

												// Google place select2
												googlePlaceSelect();

												// Hide Loader.
												hideLoader();

											case 35:

												console.log('Account Domain ' + accountDomain);

											case 36:
											case 'end':
												return _context8.stop();
										}
									}
								}, _callee8, undefined);
							})), 5000);
							_context9.next = 32;
							break;

						case 21:
							_context9.next = 23;
							return listWidgets();

						case 23:
							listWidgetsJson = _context9.sent;

							if (!(listWidgetsJson.hasOwnProperty('rscode') && 200 === listWidgetsJson.rscode)) {
								_context9.next = 32;
								break;
							}

							_body2 = {
								'apikey': '',
								'subdomain': '',
								'source': source,
								'sourceUrl': sourceUrl,
								'googlePlaceId': googlePlaceId
							};

							/**
        * Adds the review source to the currently created account.
        *
        * @param {object}  body - The request body.
        * @param {element} form - The create review source form.
        */

							_context9.next = 28;
							return addReviewSource(_body2, form);

						case 28:
							addReviewSources = _context9.sent;


							// Wp Color Picker.
							$('.color_field').wpColorPicker();

							// Google place select2
							googlePlaceSelect();

							// Hide Loader.
							hideLoader();

						case 32:
						case 'end':
							return _context9.stop();
					}
				}
			}, _callee9, undefined);
		}));

		return function createAccount(_x14, _x15, _x16, _x17, _x18) {
			return _ref8.apply(this, arguments);
		};
	}();

	/**
  * Add review source to reviewshake account.
  *
  * @param {object}  body - The create review source request body.
  * @param {element} form - The create review source form.
  */
	var addReviewSource = function () {
		var _ref10 = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee10(body, form) {
			var addReviewSource, reviewSourceJson, html, successTitle, successMessage, requireUpgrade, message, detail;
			return regeneratorRuntime.wrap(function _callee10$(_context10) {
				while (1) {
					switch (_context10.prev = _context10.next) {
						case 0:
							_context10.next = 2;
							return fetch(reviewshake_widgets_params.site_url + '/wp-json/reviewshake/v1/review_sources/', {
								method: 'POST',
								headers: {
									'Content-Type': 'application/json',
									'X-WP-Nonce': reviewshake_widgets_params.wp_rest_nonce
								},
								body: JSON.stringify(body)
							});

						case 2:
							addReviewSource = _context10.sent;
							_context10.next = 5;
							return addReviewSource.json();

						case 5:
							reviewSourceJson = _context10.sent;


							console.log(reviewSourceJson);
							if (reviewSourceJson.hasOwnProperty('rscode') && 200 == reviewSourceJson.rscode && reviewSourceJson.hasOwnProperty('html')) {
								console.log('Review source added successfully!');
								html = reviewSourceJson.html;
								successTitle = reviewshake_widgets_params.translations.add_source_success.title;
								successMessage = reviewshake_widgets_params.translations.add_source_success.message;

								// Success popup.

								showPopup(successTitle, successMessage);
								setTimeout(hidePopup, 3000);

								$('#reviewshake-widgets-setup').remove();

								postscribe('#reviewshake-tab-setup', html);
							} else {
								// Show the setup tab wrap.
								$('.reviewshake-widgets-setup-wrap').show();

								// Get the requires upgrade data attribute.
								requireUpgrade = form.attr('data-requires-upgrade');

								// If it is a require upgrade form.

								if (requireUpgrade && '1' === requireUpgrade) {
									form.remove();
									$('.review-sources-upgrade-table').show();
								}

								if (!addReviewSource.ok && reviewSourceJson.hasOwnProperty('message') && reviewSourceJson.hasOwnProperty('data')) {
									message = reviewSourceJson.message;
									detail = reviewSourceJson.data.detail;


									showPopup(message, detail, true, 'error');
								}
							}

						case 8:
						case 'end':
							return _context10.stop();
					}
				}
			}, _callee10, undefined);
		}));

		return function addReviewSource(_x19, _x20) {
			return _ref10.apply(this, arguments);
		};
	}();

	/**
  * List all widgets for the current reviewshaka account.
  *
  * @return {JSON} listWidgetsJson.
  */
	var listWidgets = function () {
		var _ref11 = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee11() {
			var listWidgets, listWidgetsJson;
			return regeneratorRuntime.wrap(function _callee11$(_context11) {
				while (1) {
					switch (_context11.prev = _context11.next) {
						case 0:
							_context11.next = 2;
							return fetch(reviewshake_widgets_params.site_url + '/wp-json/reviewshake/v1/widgets/', {
								method: 'GET',
								headers: {
									'content-type': 'application/json',
									'X-WP-Nonce': reviewshake_widgets_params.wp_rest_nonce
								}
							});

						case 2:
							listWidgets = _context11.sent;
							_context11.next = 5;
							return listWidgets.json();

						case 5:
							listWidgetsJson = _context11.sent;
							return _context11.abrupt('return', listWidgetsJson);

						case 7:
						case 'end':
							return _context11.stop();
					}
				}
			}, _callee11, undefined);
		}));

		return function listWidgets() {
			return _ref11.apply(this, arguments);
		};
	}();

	/**
  * Show loader.
  *
  * @param {string} classToHide  The class name to hide before display loader
  * @param {string} elemToAppend The element to append loader on
  *
  * @return void
  */
	var showLoader = function showLoader(classToHide, elemToAppend) {
		elemToAppend.closest('#reviewshake-widgets').find('.loader').show();

		$('.' + classToHide).hide();
	};

	/**
  * Hide loader.
  *
  * @return void
  */
	var hideLoader = function hideLoader() {
		$('.loader').hide();
		$('.creating-account-notice').hide();
	};

	/**
  * Show widget preview loader logic.
  *
  * @return void
  */
	var showPreviewLoader = function showPreviewLoader() {
		$('#save_preview_widget').attr('disabled', true);

		// Remove the widget preview.
		$('#widget_live_preview').children().remove();

		$("html, body").animate({ scrollTop: $('.reviewshake-widgets-create-wrap').offset().top - 45 }, 800);

		// Show preview loader
		$('.widget_preview_loader').show();
	};

	/**
  * Hide widget preview Loader logic.
  *
  * @return void
  */
	var hidePreviewLoader = function hidePreviewLoader() {
		// Hide preview loader
		$('.widget_preview_loader').hide();

		$('#save_preview_widget').removeAttr('disabled');
		$('#finish_widget').removeAttr('disabled');
	};

	/**
  * Display popup overlay with certain message.
  *
  * @param {string} title     - The title of the popup.
  * @param {string} message   - The message to display.
  * @param {bool}   dismiss   - Whether to include the dismiss button or not.Default: false
  * @param {string} type      - The popup type. Value is 'error' or 'success'. Default: success.
  * @param {string} className - Custom class name to wrap the popup.
  */
	var showPopup = function showPopup(title, message) {
		var dismiss = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
		var type = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 'success';
		var className = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : '';

		// First remove previous popup.
		$('.reviewshake-popup-wrap').remove();

		if ($('.reviewshake-popup-wrap').length === 0) {
			var popupWrap = $('<div></div>').attr('class', 'reviewshake-popup-wrap ' + className);
			var popupBox = $('<div></div>').attr('class', 'reviewshake-popup-box transform-in');
			var close = $('<a>&times;</a>').attr('class', 'close-btn popup-close').attr('href', '#');
			var header = $('<h1>' + title + '</h2>').attr('class', 'popup-title');
			var subheader = $('<h2>' + message + '</h3>').attr('class', 'popup-message');
			var icon = $('<img class="response " />').attr('src', reviewshake_widgets_params.successIcon);

			if (type === 'error') {
				icon = $('<img class="response " />').attr('src', reviewshake_widgets_params.errorIcon);
			}

			$(icon).appendTo(popupBox);
			$(header).appendTo(popupBox);
			$(subheader).appendTo(popupBox);

			if (dismiss) {
				$(close).appendTo(popupBox);
			}

			$(popupBox).appendTo(popupWrap);

			$('body').append(popupWrap);

			$('.reviewshake-popup-wrap').fadeIn(500);
		}
	};

	/**
  * Close popup overlay.
  */
	var closePopup = function closePopup() {
		$('.reviewshake-popup-wrap').fadeOut(500);
		$('.reviewshake-popup-box').removeClass('transform-in').addClass('transform-out');

		// Hide loaders
		hidePreviewLoader();
		hideLoader();

		$('#finish_widget').attr('disabled', true);
	};

	/**
  * Hide popup overlay
  */
	var hidePopup = function hidePopup() {
		$('.reviewshake-popup-wrap').fadeOut(500);
		$('.reviewshake-popup-box').removeClass('transform-in').addClass('transform-out');
	};
})(jQuery);