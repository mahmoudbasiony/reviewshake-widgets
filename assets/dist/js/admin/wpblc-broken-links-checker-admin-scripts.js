"use strict";

/* jshint esversion: 6 */

/**
 * Back-end scripts.
 *
 * Scripts to run on the WordPress dashboard.
 */

($ => {
  /**
   * Get app state and continue creating the account.
   */
  $(document).ready(function () {
    // if ( ! $('#email_notifications').is(':checked') ) {
    // 	$('#email_addresses').closest('tr').css('display', 'none');
    // }

    // $('#email_notifications').change(function() {
    // 	console.log(this.checked);
    // 	let display = this.checked ? '' : 'none';
    // 	$('#email_addresses').css('display', display).closest('tr').css('display', display);
    // });

    $('#email_notifications').change(function () {
      $('#email_addresses').prop('disabled', !$(this).is(':checked'));
    });
    $('#all_links, #set_number').change(function () {
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
  $(document).on('click', '#wpblc-manual-scan', function (event) {
    event.preventDefault();
    console.log('Manual scan started...');
    var nonce = wpblc_broken_links_checker_params.nonce;
    var ajaxurl = wpblc_broken_links_checker_params.ajaxurl;
    let data = {
      action: 'wpblc_broken_links_manual_scan',
      nonce: nonce
    };
    manualScan(data).then(result => {
      console.log(result);
    });
  });
  const manualScan = async data => {
    let result;
    try {
      result = await $.ajax({
        url: wpblc_broken_links_checker_params.ajaxUrl,
        type: 'POST',
        data: data,
        beforeSend: function () {
          console.log('Sending data...');
        },
        complete: function () {
          console.log('Data sent.');
        }
      });
      return result;
    } catch (error) {
      console.error('Error:', error.statusText);
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
})(jQuery);