/**
 * Back-end scripts.
 *
 * Scripts to run on the WordPress dashboard.
 */

( $ => {
	/**
	 * Get app state and continue creating the account.
	 */
	$(document).ready(function(){
		const appState = reviewshake_widgets_params.state;
		let tab = appState.tab;
		let sourceName = appState.source_name;
		let sourceUrl = appState.source_url;
		let googlePlaceId = appState.google_place_id;

		console.log(appState);
		if (tab && 'setup' === tab && sourceName && sourceUrl && appState.account_status && appState.source_status && ('pending' === appState.account_status || 'on_hold' === appState.account_status || 'pending' === appState.source_status)) {
			let form = $('#create_review_source_form');
			let isAccountExists = form.data('account-exists');
			let secToSleep = appState.sec_to_sleep;

			console.log('Waiting' + secToSleep +' seconds');

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
	$(document).on('change','.review-sources',function(e){
		let placeholder = $(this).find('option:selected').data('placeholder-url');

		let sourceName = $(this).find('option:selected').val();

		if ('google' === sourceName) {
			$(this).closest('.review-sources-row').find('.review-sources-url-column').hide();
			$(this).closest('.review-sources-row').find('.review-sources-url-column.google-places-select').show();
		} else {
			$(this).closest('.review-sources-row').find('.review-sources-url-column').show();
			$(this).closest('.review-sources-row').find('.review-sources-url-column.google-places-select').hide();
			$(this).closest('.review-sources-row').find('input[type="text"].review-sources-url').attr('placeholder',placeholder);
		}
	});

	/*
	 * Get google places from google maps places API.
	 */
	$(document).ready(function(){
		googlePlaceSelect();
	});

	/*
	 * On submit add review source form.
	 */
	$(document).on('submit','#create_review_source_form',function(e){
		e.preventDefault();

		let form = $(this);

		let sourceUrl = form.find('input[name="source_url"]').val();
		let source = form.find('select[name="source_name"] option:selected').val().toLowerCase();
		let isAccountExists = form.data('account-exists');
		let sourceID = parseInt( form.attr('data-review-source-id') );
		let googlePlaceId = '';

		if ('google' === source) {
			sourceUrl = form.find('select[name="source_url"] option:selected').text();
			googlePlaceId = form.find('select[name="source_url"] option:selected').val();
		}

		console.log(sourceUrl);
		console.log(googlePlaceId);

		// Define errors
		let errors = false;
		// First remove all errors.
		form.find('.error').remove();

		// Source name is required.
		if(source.length <= 0){
			form.find('.review-sources').after('<span class="error">'+reviewshake_widgets_params.translations.required+'</span');
			errors = true;
		}

		// Source URL is required.
		if(sourceUrl.length <= 0 || ('google' === source && ( googlePlaceId == undefined || googlePlaceId.length <= 0)) ){
			form.find('.error').remove();
			form.find('.review-sources-url-column').append('<span class="error">'+reviewshake_widgets_params.translations.required+'</span');
			errors = true;
		}

		// Validate errors
		if(errors){
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
	$(document).on('click','.delete-review-source',function(e){
		e.preventDefault();

		let reviewSourceRow = $(this).closest('.review-sources-row');
		let sourceID = reviewSourceRow.data('review-source-id');
		let id = parseInt( sourceID.replace('rs','') );

		if( confirm(reviewshake_widgets_params.translations.confirm_delete) ) {
			// Show Loader.
			showLoader('reviewshake-widgets-setup-wrap', reviewSourceRow);

			// Delete review source.
			deleteReviewSource(id, reviewSourceRow);
		}
	} );

	/*
	 * On click upgrade account button.
	 */
	$(document).on('click', '#upgrade-setup-link', function(e) {
		e.preventDefault();

		let element = $(this);

		// Open the upgrade link in a new tab.
		let href = element.attr('href');
		window.open( href, '_blank' );

		let data = {
			'action' : 'reviewshake_renders_review_source_form',
			'nonce' : reviewshake_widgets_params.nonce,
		};

		getReviewSourceForm(data).then(result => {
			if(result.success && result.hasOwnProperty('data') && result.data.hasOwnProperty('form')) {
				let form = result.data.form;

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
	$(document).on('click', '.delete-widget', function(e){
		e.preventDefault();

		let widget = $(this).closest('.reviewshake-widgets-widget');
		let widgetID = widget.attr('data-widget-id');

		if( confirm(reviewshake_widgets_params.translations.confirm_delete) ) {
			// Show Loader.
			showLoader('reviewshake-widgets-setup-wrap', widget);

			// Delete widget.
			deleteWidget(widgetID, widget);
		}
	} );

	/*
	 * On change minimun star rating input form.
	 */
	$(document).on('click', '.widget_min_star_rating .star_rating', function(event){
		$('.star_rating').removeClass('selected');

		// Initialize the exStarRating array.
		let exStarRating = [];

		// Get the current clicked star rate.
		let starRate = $(this).data('star-rate');

		for ( let i = 1; i < starRate; i++ ) {
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
	$(document).on( 'submit', '#create_widget_form', function(e) {
		e.preventDefault();

		let form = $(this);

		let widgetID = parseInt(form.attr('data-widget-id'));
		let name = form.find('input[name="name"]').val();
		let type = form.find('select[name="widget_type"] option:selected').val();

		// Define errors
		let errors = false;
		// First remove all errors.
		form.find('.error').remove();

		// Widget name is a required field.
		if(name.length <= 0){
			form.find('input[name="name"]').after('<span class="error">'+reviewshake_widgets_params.translations.required+'</span');
			errors = true;
		}

		// Widget type is a required field.
		if(type.length <= 0){
			form.find('select[name="widget_type"]').after('<span class="error">'+reviewshake_widgets_params.translations.required+'</span');
			errors = true;
		}

		/*
		 * Validation errors.
		 */
		if(errors) {
			$("html, body").animate({ scrollTop: $('.error').first().offset().top - 70 }, 800);
			return false;
		}

		let formData = new FormData(form[0]);

		const plainFormData = Object.fromEntries(formData.entries());

		console.log(plainFormData);

		if( !!widgetID ) {
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
	} );

	/*
	 * On click Add/Edit widget
	 */
	$(document).on('click', '.add-new-widget, .edit-widget', function(e) {
		e.preventDefault();

		let widget   = $(this).closest('.reviewshake-widgets-widget');
		let widgetID = !! widget.attr('data-widget-id') ? widget.attr('data-widget-id') : 0;

		let data = {
			'action' : 'reviewshake_renders_widget_form',
			'nonce' : reviewshake_widgets_params.nonce,
			'widget_id' : widgetID,
		};

		getWidgetForm(data).then( result => {
			if(result.success && result.hasOwnProperty('data') && result.data.hasOwnProperty('form')) {
				let form = result.data.form;

				$('.reviewshake-widgets-setup-wrap').remove();
				postscribe( '#reviewshake-widgets-setup', form );

				$("html, body").animate({ scrollTop: $('#reviewshake-widgets').offset().top - 30 }, 200);
				$( '.color_field' ).wpColorPicker();
			}
		} );
	})

	/*
	 * On submit connect account form
	 */
	$(document).on('submit', '#connect_account_form', function(event) {
		event.preventDefault();

		let form = $(this);
		let subdomain = form.find('input[name="subdomain"]').val();
		let apiKey = form.find('input[name="api_key"]').val();

		// Define errors
		let errors = false;
		// First remove all errors.
		form.find('.error').remove();

		// Account subdomain is a required field.
		if (subdomain.length <= 0) {
			form.find('input[name="subdomain"]').after('<span class="error">'+reviewshake_widgets_params.translations.required+'</span');
			errors = true;
		}

		// Account API key is a required field.
		if (apiKey.length <= 0) {
			form.find('input[name="api_key"]').after('<span class="error">'+reviewshake_widgets_params.translations.required+'</span');
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
	})

	/*
	 * On click add connect another account.
	 */
	$(document).on('click', '.connect-another-account', function(e) {
		e.preventDefault();

		let form = reviewshake_widgets_params.newAccountForm;
		$('.reviewshake-widgets-account-wrap').replaceWith(form);
	} );

	/*
	 * On click claim account button.
	 */
	$(document).on('click', '#claim-account', function(e) {
		e.preventDefault();

		$(this).closest('.account-links-wrap').hide();
		$(this).closest('.claim-account-wrap').find('.claiming-in-progress-wrap').show();

		let href = $(this).data('href');
		window.open( href, '_blank' );
	});

	$(document).ready(function () {
		// Add Color Picker to all inputs that have 'color-field' class
		$( '.color_field' ).wpColorPicker();
	});

	/*
	 * On click popup close button.
	 */
	$(document).on('click', '.popup-close', function(e) {
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
	const getReviewSourceForm = async(data) => {
		let result;
		try {
			result = await $.ajax({
				url: reviewshake_widgets_params.ajax_url,
				type: 'POST',
				data: data
			});

			return result;
		} catch (error) {
			console.error(error);
		}
	};

	/**
	 * Google places autocomplete predictions.
	 *
	 * @return void
	 */
	const googlePlaceSelect = () => {
		$(".review-sources-row .google-places-select select").select2({
			ajax: {
				url: reviewshake_widgets_params.ajax_url,
				dataType: 'json',
				delay: 100,
				data: function (params) {
					return {
						q: params.term,
						action: 'reviewshake_google_places_predictions',
						nonce : reviewshake_widgets_params.nonce,
						input: params.term,
					};
				},
				processResults: function (data, params) {
					return {
						results: data.data,
					};
				},
				cache: true
			},
			placeholder: reviewshake_widgets_params.translations.google_places_placeholder,
			language: {
				noResults : function() {
					return reviewshake_widgets_params.translations.no_places_found;
				}
			},
			allowClear: true,
			minimumInputLength: 1,
			templateResult: formatPlace,
			templateSelection: formatPlaceSelection
		});
	}

	/**
	 * Formats google place results.
	 *
	 * @param {Object} place The google place object.
	 *
	 * @return {String} The formatted result
	 */
	const formatPlace = place => {
		if (place.loading) {
			return place.text;
		}

		var $container = $(
			"<div class='select2-result-places clearfix'>" +
			"<div class='select2-result-places__meta'>" +
				"<div class='select2-result-places__title'></div>" +
			"</div>" +
			"</div>"
		);
		
		$container.find(".select2-result-places__title").text(place.text);

		return $container;
	}

	/**
	 * Formats google place selection.
	 *
	 * @param {Object} place The google place object.
	 *
	 * @return {String} The formatted text
	 */
	const formatPlaceSelection = place => {
		return place.text || place.description;
	}

	/**
	 * Get account info
	 *
	 * @param {string} subdomain The account subdomain.
	 * @param {string} apiKey    The account API key.
	 *
	 * @return void
	 */
	const getAccountInfo = async(subdomain, apiKey) => {
		const getAccountResponse = await fetch(reviewshake_widgets_params.site_url+'/wp-json/reviewshake/v1/account/'+apiKey+'/'+ subdomain, {
			method: 'GET',
			headers: {
				'content-type': 'application/json',
				'X-WP-Nonce' : reviewshake_widgets_params.wp_rest_nonce,
			}
		} );

		const responseJson = await getAccountResponse.json();

		if(responseJson.hasOwnProperty('data') && responseJson.data.hasOwnProperty('attributes')) {
			let html = responseJson.html;
			$('.reviewshake-widgets-account').replaceWith(html);
		}

		// Hide Loader.
		hideLoader();
	};

	/**
	 * Get widget create/edit form
	 *
	 * @param {object} data The object data to send to ajax
	 *
	 * @return {object|WP_Error}
	 */
	const getWidgetForm = async(data) => {
		let result;
		try {
			result = await $.ajax({
				url: reviewshake_widgets_params.ajax_url,
				type: 'POST',
				data: data
			});

			return result;
		} catch (error) {
			console.error(error);
		}
	};

	/**
	 * Update the widget
	 *
	 * @param {element} form     The form element
	 * @param {object}  formData The form data object
	 *
	 * @return void
	 */
	const updateWidget = async (form, formData) => {
		// Show preview loader.
		showPreviewLoader();

		const widgetID = formData.id;

		// Send the update widget request to rest API.
		const updateWidgetResponse = await fetch(reviewshake_widgets_params.site_url+'/wp-json/reviewshake/v1/widgets/'+widgetID, {
			method: 'PUT',
			headers: {
				'content-type' : 'application/json',
				'X-WP-Nonce' : reviewshake_widgets_params.wp_rest_nonce,
			},
			body: JSON.stringify(formData),
		} );

		// Get the update widget response json.
		const responseJson = await updateWidgetResponse.json();

		console.log(responseJson);

		let embed = '';
		if (responseJson.hasOwnProperty('status') && 200 === responseJson.status) {
			embed = responseJson.embed;

			// Add a version to script to prevent broweser caching.
			embed = embed + '?v=' + Date.now();

			// Append the preview widget asynchronously.
			postscribe('#widget_live_preview', '<script src="'+embed+'"></script>', {
				done: function() {
					console.log( 'done' );

					// Hide preview loader
					hidePreviewLoader();
				}
			});
		}

		if (!updateWidgetResponse.ok && responseJson.hasOwnProperty('message') && responseJson.hasOwnProperty('message')) {
			let message = responseJson.message;
			let detail = responseJson.data.detail;
			showPopup( message, detail, true, 'error' );
		}
	};

	/**
	 * Create a new widget
	 *
	 * @param {element} form     The form element
	 * @param {object}  formData The form data object
	 *
	 * @return void
	 */
	const createWidget = async(form, formData) => {
		// Show preview loader.
		showPreviewLoader();

		// Send the create widget request to rest API.
		const createWidgetResponse = await fetch(reviewshake_widgets_params.site_url+'/wp-json/reviewshake/v1/widgets', {
			method: 'POST',
			headers : {
				'content-type': 'application/json',
				'X-WP-Nonce' : reviewshake_widgets_params.wp_rest_nonce,
			},
			body: JSON.stringify(formData),
		} );

		// Get the create widget response json.
		const responseJson = await createWidgetResponse.json();

		console.log(responseJson);

		let embed = '';
		if (responseJson.hasOwnProperty('status') && 200 == responseJson.status) {
			let widgetID = responseJson.id;

			// Add the widget ID data attribute.
			form.attr('data-widget-id', widgetID);

			embed = responseJson.embed;
			// Add a version to script to prevent broweser caching.
			embed = embed + '?v=' + Date.now();

			// Append the preview widget asynchronously.
			postscribe( '#widget_live_preview', '<script src="'+embed+'"></script>', {
				done: function() {
					console.log( 'done' );

					// Hide preview loader
					hidePreviewLoader();
				}
			} );
		}

		if (!createWidgetResponse.ok && responseJson.hasOwnProperty('message') && responseJson.hasOwnProperty('data')) {
			let message = responseJson.message;
			let detail  = responseJson.data.detail;

			showPopup(message, detail, true, 'error');
		}
	};

	/**
	 * Delete Reviewshake widget
	 *
	 * @param {int}     widgetID The target widget ID.
	 * @param {element} widget   The widget element.
	 *
	 * @return void
	 */
	const deleteWidget = async(widgetID, widget) => {
		// Send the delete widget request to rest API
		const deleteWidgetResponse = await fetch(reviewshake_widgets_params.site_url+'/wp-json/reviewshake/v1/widgets/'+widgetID, {
			method : 'DELETE',
			headers : {
				'content-type': 'application/json',
				'X-WP-Nonce' : reviewshake_widgets_params.wp_rest_nonce,
			}
		} );

		// Get the delete widget response json
		const responseJson = await deleteWidgetResponse.json();

		if(deleteWidgetResponse.ok && responseJson.deleted) {
			console.log('Widget deleted successfully');
			let html = responseJson.html;

			$('#reviewshake-widgets-setup').remove();

			postscribe( '#reviewshake-tab-setup', html );
		} else {
			$('.reviewshake-widgets-setup-wrap').show();
		}

		$( '.color_field' ).wpColorPicker();

		// Google place select2
		googlePlaceSelect();

		if (!deleteWidgetResponse.ok && responseJson.hasOwnProperty('message') && responseJson.hasOwnProperty('data')) {
			let message = responseJson.message;
			let detail  = responseJson.data.detail;

			showPopup(message, detail, true, 'error');
		}

		// Hide Loader.
		hideLoader();
	};

	/**
	 * Delete review source.
	 *
	 * @param {int}     id              The target source ID.
	 * @param {element} reviewSourceRow The review source element.
	 *
	 * @return void
	 */
	const deleteReviewSource = async(sourceID, reviewSource) => {
		// Send the delete review source request to rest API.
		const deleteSourceResponse = await fetch(reviewshake_widgets_params.site_url+'/wp-json/reviewshake/v1/review_sources/'+ sourceID, {
			method: 'DELETE',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce'  : reviewshake_widgets_params.wp_rest_nonce,
			}
		} );

		// Get the delete review source response json.
		const responseJson = await deleteSourceResponse.json();

		if ( responseJson.deleted ) {
			console.log('Review source deleted successfully!');
			let html = responseJson.html;

			$('#reviewshake-widgets-setup').remove();

			postscribe( '#reviewshake-tab-setup', html );
		} else {
			console.log(responseJson);
			$('.reviewshake-widgets-setup-wrap').show().find(reviewSource).remove();

			if (!deleteSourceResponse.ok && responseJson.hasOwnProperty('message') && responseJson.hasOwnProperty('data')) {
				let message = responseJson.message;
				let detail  = responseJson.data.detail;
	
				showPopup(message, detail, true, 'error');
			}
		}

		// WP color picker
		$( '.color_field' ).wpColorPicker();

		// Google place select2
		googlePlaceSelect();

		// Hide Loader.
		hideLoader();
	};

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
	const createAccount = async(source, sourceUrl, googlePlaceId, form, isAccountExists, secToSleep = 20) => {
		let sourcesCount = parseInt( form.attr('data-sources-count') );
		let pricingPlan  = form.attr('data-pricing-plan');

		// If is first review source.
		if (sourcesCount === 0 && '' == isAccountExists) {
			form.addClass('first');
			form.closest('#reviewshake-widgets').find('.creating-account-notice').show();
		}

		/*
		 * If account doesn't exist.
		 */
		if (isAccountExists.length <= 0) {
			const body = {
				'source' : source,
				'sourceUrl' : sourceUrl,
				'googlePlaceId' : googlePlaceId,
			};

			// Send the create new account request to rest API 
			const createAccountResponse = await fetch(reviewshake_widgets_params.site_url+'/wp-json/reviewshake/v1/account/', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce' : reviewshake_widgets_params.wp_rest_nonce,
				},
				body: JSON.stringify(body),
			});

			// Get the create account response json.
			const createAccountJson = await createAccountResponse.json();

			console.log('beforeSleep');

			// Wait for x seconds to get account created.
			await new Promise(resolve => isAccountExists == '' ? setTimeout(resolve, secToSleep * 1000) : resolve());

			console.log('afterSleep');

			let attributes = createAccountJson.data.attributes;
			let accountDomain = createAccountJson.data.links.account_domain;
			let email = attributes.email;

			// Set interval every 5 seconds to check account fully created.
			const tryGetAccount = setInterval(async () => {
				// Send get account status request to rest API
				const getAccountResponse = await fetch(reviewshake_widgets_params.site_url+'/wp-json/reviewshake/v1/account/?'+ new URLSearchParams({
					email: email,
				}), {
					method: 'GET',
					headers: {
						'Content-Type' : 'application/json',
						'X-WP-Nonce' : reviewshake_widgets_params.wp_rest_nonce,
					}
				});

				// Get the account status response json.
				const getAccountJson = await getAccountResponse.json();

				if (getAccountJson.hasOwnProperty('data') && getAccountJson.data.hasOwnProperty('attributes')) {
					// Clear the interval.
					clearInterval(tryGetAccount);
	
					let attributes = getAccountJson.data.attributes;
					let id = getAccountJson.data.id;
					let type = getAccountJson.data.type;
					let apiKey    = attributes.api_key;
					accountDomain = attributes.account_domain;

					console.log('Get account created success');
					console.log('before trial api');

					// If account is on trial plan.
					if( '' == pricingPlan || 'trial' === pricingPlan ) {
						// Send the convert trial account to free plan rest API
						const convertToFree = await fetch(reviewshake_widgets_params.site_url+'/wp-json/reviewshake/v1/account/'+apiKey+'/'+ accountDomain, {
							method: 'PUT',
							headers: {
								'Content-Type': 'application/json',
								'X-WP-Nonce' : reviewshake_widgets_params.wp_rest_nonce,
							}
						});
						console.log('Account get a free plan');
					}

					console.log('after trial api');

					const listWidgetsJson = await listWidgets();

					if (listWidgetsJson.hasOwnProperty('rscode') && 200 === listWidgetsJson.rscode) {
						let count = listWidgetsJson.hasOwnProperty('count') ? parseInt(listWidgetsJson.count) : 0;

						console.log('Widgets listed successfuly');
						console.log('You have ' +count+ ' of registered Widgets');

						let body = {
							'apikey' : apiKey,
							'subdomain' : accountDomain,
							'source' : source,
							'sourceUrl' : sourceUrl,
							'googlePlaceId' : googlePlaceId,
						};
	
						/**
						 * Adds the review source to the currently created account.
						 *
						 * @param {object}  body - The request body.
						 * @param {element} form - The create review source form.
						 */
						const addReviewSources = await addReviewSource(body, form);
	
						// Wp Color Picker.
						$( '.color_field' ).wpColorPicker();

						// Google place select2
						googlePlaceSelect();

						// Hide Loader.
						hideLoader();
					}

					console.log( 'Account Domain ' + accountDomain );
				}
			}, 5000);
		} else {
			/**
			 * List the available widgets for the account.
			 */
			const listWidgetsJson = await listWidgets();

			if (listWidgetsJson.hasOwnProperty('rscode') && 200 === listWidgetsJson.rscode) {
				let body = {
					'apikey' : '',
					'subdomain' : '',
					'source' : source,
					'sourceUrl' : sourceUrl,
					'googlePlaceId' : googlePlaceId,
				};

				/**
				 * Adds the review source to the currently created account.
				 *
				 * @param {object}  body - The request body.
				 * @param {element} form - The create review source form.
				 */
				const addReviewSources = await addReviewSource(body, form);

				// Wp Color Picker.
				$( '.color_field' ).wpColorPicker();

				// Google place select2
				googlePlaceSelect();

				// Hide Loader.
				hideLoader();
			}
		}
	}

	/**
	 * Add review source to reviewshake account.
	 *
	 * @param {object}  body - The create review source request body.
	 * @param {element} form - The create review source form.
	 */
	const addReviewSource = async(body, form) => {
		// Send add review source request to rest API
		const addReviewSource = await fetch(reviewshake_widgets_params.site_url+'/wp-json/reviewshake/v1/review_sources/', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce' : reviewshake_widgets_params.wp_rest_nonce,
			},
			body: JSON.stringify(body),
		} );

		// Get the add review source response json.
		const reviewSourceJson = await addReviewSource.json();

		console.log(reviewSourceJson);
		if (reviewSourceJson.hasOwnProperty('rscode') && 200 == reviewSourceJson.rscode && reviewSourceJson.hasOwnProperty('html')) {
			console.log('Review source added successfully!');
			let html = reviewSourceJson.html;

			let successTitle   = reviewshake_widgets_params.translations.add_source_success.title;
			let successMessage = reviewshake_widgets_params.translations.add_source_success.message;

			// Success popup.
			showPopup(successTitle, successMessage);
			setTimeout(hidePopup, 3000);

			$('#reviewshake-widgets-setup').remove();

			postscribe('#reviewshake-tab-setup', html);
		} else {
			// Show the setup tab wrap.
			$('.reviewshake-widgets-setup-wrap').show();

			// Get the requires upgrade data attribute.
			let requireUpgrade = form.attr('data-requires-upgrade');

			// If it is a require upgrade form.
			if (requireUpgrade && '1' === requireUpgrade) {
				form.remove();
				$('.review-sources-upgrade-table').show();
			}

			if (!addReviewSource.ok && reviewSourceJson.hasOwnProperty('message') && reviewSourceJson.hasOwnProperty('data') ) {
				let message = reviewSourceJson.message;
				let detail = reviewSourceJson.data.detail;

				showPopup(message, detail, true, 'error');
			}
		}
	};

	/**
	 * List all widgets for the current reviewshaka account.
	 *
	 * @return {JSON} listWidgetsJson.
	 */
	const listWidgets = async() => {
		// Send the listing widgets request to rest API
		const listWidgets = await fetch(reviewshake_widgets_params.site_url+'/wp-json/reviewshake/v1/widgets/', {
			method: 'GET',
			headers: {
				'content-type': 'application/json',
				'X-WP-Nonce': reviewshake_widgets_params.wp_rest_nonce,
			}
		} );

		// Get the widgets response json
		const listWidgetsJson = await listWidgets.json();

		return listWidgetsJson;
	};

	/**
	 * Show loader.
	 *
	 * @param {string} classToHide  The class name to hide before display loader
	 * @param {string} elemToAppend The element to append loader on
	 *
	 * @return void
	 */
	const showLoader = (classToHide, elemToAppend) => {
		elemToAppend.closest('#reviewshake-widgets').find('.loader').show();

		$('.'+classToHide).hide();
	};

	/**
	 * Hide loader.
	 *
	 * @return void
	 */
	const hideLoader = () => {
		$('.loader').hide();
		$('.creating-account-notice').hide();
	};

	/**
	 * Show widget preview loader logic.
	 *
	 * @return void
	 */
	const showPreviewLoader = () => {
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
	const hidePreviewLoader = () => {
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
	const showPopup = (title, message, dismiss = false, type = 'success' ,className = '') => {
		// First remove previous popup.
		$('.reviewshake-popup-wrap').remove();

		if ($('.reviewshake-popup-wrap').length === 0) {
			let popupWrap = $('<div></div>').attr('class', 'reviewshake-popup-wrap ' + className);
			let popupBox = $('<div></div>').attr('class', 'reviewshake-popup-box transform-in');
			let close = $('<a>&times;</a>').attr('class', 'close-btn popup-close').attr('href', '#');
			let header = $('<h1>'+title+'</h2>').attr('class', 'popup-title');
			let subheader = $('<h2>'+message+'</h3>').attr('class', 'popup-message');
			let icon = $('<img class="response " />').attr('src', reviewshake_widgets_params.successIcon);

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
	const closePopup = () => {
		$('.reviewshake-popup-wrap').fadeOut(500);
		$('.reviewshake-popup-box').removeClass('transform-in').addClass('transform-out');

		// Hide loaders
		hidePreviewLoader();
		hideLoader();

		$('#finish_widget').attr('disabled', true);
	}

	/**
	 * Hide popup overlay
	 */
	const hidePopup = () => {
		$('.reviewshake-popup-wrap').fadeOut(500);
		$('.reviewshake-popup-box').removeClass('transform-in').addClass('transform-out');
	}
} )( jQuery );