/* jshint esversion: 6 */

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
		// if ( ! $('#email_notifications').is(':checked') ) {
		// 	$('#email_addresses').closest('tr').css('display', 'none');
		// }

		// $('#email_notifications').change(function() {
		// 	console.log(this.checked);
		// 	let display = this.checked ? '' : 'none';
		// 	$('#email_addresses').css('display', display).closest('tr').css('display', display);
		// });
		
		$('#email_notifications').change(function() {
			$('#email_addresses').prop('disabled', ! $(this).is(':checked'));
		});

		$('#all_links, #set_number').change(function() {
			$('#number_of_links').prop('disabled', $('#all_links').is(':checked'));
		});
	});

	// $(document).ready(function() {
	// 	$('#wpblc-manual-scan').on('click', function(event) {
	// 		event.preventDefault();

			
	// 	});
	// });

	let nonce = wpblc_broken_links_checker_params.nonce;
	let ajaxurl = wpblc_broken_links_checker_params.ajaxurl;

	$(document).on('click', '#wpblc-manual-scan', function(event) {
		event.preventDefault();
		console.log('Manual scan started...');

		var nonce = wpblc_broken_links_checker_params.nonce;
		var ajaxurl = wpblc_broken_links_checker_params.ajaxurl;

		let data = {
			action: 'wpblc_broken_links_manual_scan',
			nonce: nonce,
		};

		manualScan(data).then(result => {
			console.log(result);
		});
	});

	$(document).on('click', '#wpblc-mark-as-fixed.not-fixed', function(event) {
		event.preventDefault();
		console.log('Mark as fixed started...');

		var nonce = wpblc_broken_links_checker_params.nonce;
		var currentEl = $(this);
		var link = currentEl.data('link');
		var postId = currentEl.data('post-id');

		let data = {
			action: 'wpblc_broken_links_mark_as_fixed',
			nonce: nonce,
			link: link,
			postId: postId,
			
		};

		markAsFixed(data).then(result => {
			console.log(result);

			if (typeof result === 'object' && result.hasOwnProperty('success') && result.success) {
				if ( typeof result.data === 'boolean' && result.data ) { // Correct typo in 'boolean'
					currentEl.attr( 'title', 'Mark as Broken' );

					if (currentEl.hasClass('fixed')) {
						currentEl.removeClass('fixed').addClass('not-fixed').html('Mark as Fixed');
						currentEl.closest('tr').find('.column-type .status-type').removeClass('fixed').addClass('not-fixed').html('Broken');
					} else {
						currentEl.removeClass('not-fixed').addClass('fixed').html('Mark as Broken');
						currentEl.closest('tr').find('.column-type .status-type').html('Fixed').removeClass('not-fixed').addClass('fixed');
					}
				}
			}
		});
	});

	$(document).on('click', '#wpblc-mark-as-fixed.fixed', function(event) {
		event.preventDefault();
		console.log('Mark as broken started...');

		var nonce = wpblc_broken_links_checker_params.nonce;
		var currentEl = $(this);
		var link = currentEl.data('link');
		var postId = currentEl.data('post-id');

		let data = {
			action: 'wpblc_broken_links_mark_as_broken',
			nonce: nonce,
			link: link,
			postId: postId,
			
		};

		markAsBroken(data).then(result => {
			console.log(result);

			if (typeof result === 'object' && result.hasOwnProperty('success') && result.success) {
				if ( typeof result.data === 'boolean' && result.data ) { // Correct typo in 'boolean'

					if (currentEl.hasClass('fixed')) {
						currentEl.removeClass('fixed').addClass('not-fixed').html('Mark as Fixed');
						currentEl.closest('tr').find('.column-type .status-type').removeClass('fixed').addClass('not-fixed').html('Broken');
					} else {
						currentEl.removeClass('not-fixed').addClass('fixed').html('Mark as Broken');
						currentEl.closest('tr').find('.column-type .status-type').removeClass('not-fixed').addClass('fixed').html('Fixed');
					}
				}
			}
		});
	});


	const markAsBroken = async(data) => {
		let result;

		try {
			result = await $.ajax({
				url: wpblc_broken_links_checker_params.ajaxUrl,
				type: 'POST',
				data: data,
				beforeSend: function() {
					console.log('Sending data...');
					loader('show');
				},
				complete: function() {
					console.log('Data sent.');
					loader('hide');
				}
			});

			// Validate the AJAX response
			if (typeof result === 'object' && result.hasOwnProperty('success') && result.success) {
				
			} else {
				console.error('Error: AJAX request failed', result);
			}

			return result;
		} catch (error) {
			console.error('Error:', error.statusText);
		}
	};

	
	const manualScan = async(data) => {
		let result;

		try {
			result = await $.ajax({
				url: wpblc_broken_links_checker_params.ajaxUrl,
				type: 'POST',
				data: data,
				beforeSend: function() {
					console.log('Sending data...');
					loader('show');
					$('.wpblc-broken-links-checker-links-table').html('');
				},
				complete: function() {
					console.log('Data sent.');
					loader('hide');
				}
			});

			// Validate the AJAX response
			if (typeof result === 'object' && result.hasOwnProperty('success') && result.success) {
				// Check if result.data is a string before using it as HTML
				if (typeof result.data === 'string') {
					// Replace the table's HTML with the new HTML
					$('.wpblc-broken-links-checker-links-table').html(result.data);
				} else {
					console.error('Error: Unexpected AJAX response', result);
				}
			} else {
				console.error('Error: AJAX request failed', result);
			}

			return result;
		} catch (error) {
			console.error('Error:', error.statusText);
		}
	};

	const markAsFixed = async(data) => {
		let result;

		try {
			result = await $.ajax({
				url: wpblc_broken_links_checker_params.ajaxUrl,
				type: 'POST',
				data: data,
				beforeSend: function() {
					console.log('Sending data...');
					loader('show');
				},
				complete: function() {
					console.log('Data sent.');
					loader('hide');
				}
			});

			// Validate the AJAX response
			if (typeof result === 'object' && result.hasOwnProperty('success') && result.success) {
				
			} else {
				console.error('Error: AJAX request failed', result);
			}

			return result;
		} catch (error) {
			console.error('Error:', error.statusText);
		}
	};

	const loader = (action) => {
		let loader = $('.wpblc-is-scanning');
		if (action === 'show') {
			loader.show();
		} else if (action === 'hide') {
			loader.hide();
		}
	};

	
	
	// $(document).ready(function() {
    //     // Hide the number field if "All" is selected when the page loads
    //     if ($('#all_links').is(':checked')) {
    //         $('#number_of_links').closest('tr').hide();
    //     }

    //     // Show/hide the number field when the selected option changes
    //     $('#all_links, #set_number').change(function() {
    //         if ($('#all_links').is(':checked')) {
    //             $('#number_of_links').hide();
    //         } else {
    //             $('#number_of_links').show();
    //         }
    //     });
    // });

} )( jQuery );