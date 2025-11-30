(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	 
	
	jQuery(document).ready(function() {
		var screen_id = wsp_admin_param.screen_id;

		if( screen_id == 'wps_subscriptions' || screen_id == 'woocommerce_page_wc-orders--wps_subscriptions' ){

			jQuery(document).on('change','#customer_user',function() {
				var user_id = jQuery( '#customer_user' ).val();
				jQuery('#wps_wsp_parent_order_selection').html('');
				jQuery('#wps_wsp_parent_order_selection').append('<option value="">Select an order</option>');
				jQuery('.edit_address').show();
				jQuery('.billing-same-as-shipping').show();
				
				if( user_id ){
					var data = {
						user_id: user_id,
						nonce: wsp_admin_param.wps_auth_nonce,
						action: 'wps_wsp_show_parent_order_for_custom_manual',
					}
					jQuery.ajax({
						type: 'post',
						dataType: 'json',
						url: wsp_admin_param.ajaxurl,
						data: data,
						success: function(response) {
							// console.log(response);
							
							jQuery('#wps_wsp_parent_order_selection').append(response.html);
							
						}
					});
				}
			});
	
			$('.billing-same-as-shipping').on('click', function() {
				// Copy billing fields to shipping fields
				$('#_shipping_first_name').val($('#_billing_first_name').val());
				$('#_shipping_last_name').val($('#_billing_last_name').val());
				$('#_shipping_company').val($('#_billing_company').val());
				$('#_shipping_address_1').val($('#_billing_address_1').val());
				$('#_shipping_address_2').val($('#_billing_address_2').val());
				$('#_shipping_city').val($('#_billing_city').val());
				$('#_shipping_state').val($('#_billing_state').val());
				$('#_shipping_postcode').val($('#_billing_postcode').val());
				$('#_shipping_country').val($('#_billing_country').val());
			});
		}
	
		// Show the popup when the 'Update' button is clicked
		$('.update-subscription').on('click', function(e) {
			e.preventDefault();
			var subscriptionId = $(this).data('subscription_id');
			$('#subscription-id').val(subscriptionId); // Set subscription ID in the hidden field
			$('#update-subscription-popup').fadeIn();
		});
	
		// Close the popup when the 'X' is clicked
		$('.close-popup').on('click', function() {
			$('#update-subscription-popup').fadeOut();
		});

	
		var dateInput = $('#next-payment-date');
	
		// Get today's date in YYYY-MM-DD format
		var today = new Date();
		var day = String(today.getDate()).padStart(2, '0');
		var month = String(today.getMonth() + 1).padStart(2, '0'); // Months are 0-based
		var year = today.getFullYear();
	
		var todayDate = year + '-' + month + '-' + day;
	
		// Set the min attribute to today
		dateInput.attr('min', todayDate);
		
		
	
		// Handle form submission with AJAX
		$(document).on('click','#update-subscription-btn', function(e) {
			e.preventDefault();
	
			var subscriptionId = $('#subscription-id').val();
			var nextPaymentDate = $('#next-payment-date').val();
			var subscriptionPrice = $('#subscription-price').val();

			var today = new Date().toISOString().split('T')[0];

			if (nextPaymentDate !== '' && nextPaymentDate < today) {
				alert(wsp_admin_param.subscription_next_payment_date_error);
				return; // Stop further execution
			}

			if ( subscriptionPrice !== '' && (isNaN(subscriptionPrice) || parseFloat(subscriptionPrice) <= 0)) {
				alert(wsp_admin_param.subscription_price_error);
				return; // Stop further execution
			}
	
			$.ajax({
				url: ajaxurl, // Make sure to localize the AJAX URL in your WordPress setup
				type: 'POST',
				data: {
					action: 'wps_wsp_update_subscription_items',
					subscription_id: subscriptionId,
					next_payment_date: nextPaymentDate,
					subscription_price: subscriptionPrice,
					nonce: wsp_admin_param.wps_auth_nonce
				},
				success: function(response) {
					if(response.success) {
						if(response.data.success) {
							alert(response.data.message); // Display the success message
						} else {
							alert(response.data.message); // Display message for no changes
						}
						window.location.reload();
					} else {
						alert('Error: ' + response.data.message); // Display error if any
					}
				},
				error: function() {
					alert('AJAX request failed. Please try again.');
				}
			});
		});
		$(document).on( 'click', '.wps-wsp-advanced-section-form h3', function(){
			var current = $(this);
			var nextH3 = current.nextAll("h3").first(); // Find the next h3
			var formGroups;

			if (nextH3.length) {
				// Select all .wps-form-group elements between current h3 and next h3
				formGroups = current.nextUntil("h3", ".wps-form-group");
			} else {
				// If there's no next h3, select all .wps-form-group until the end
				formGroups = current.nextAll(".wps-form-group");
			}

			// Toggle visibility
			formGroups.toggleClass('wps_wsp_active_section');
		})

		// export functionality.
			    $('#wps_wsp_open_export_popup').on('click', function(e){
						e.preventDefault();
						$('#wps_wsp_export_modal').fadeIn();
					});

				// Close modal
				$('#wps_wsp_close_modal').on('click', function(){
					$('#wps_wsp_export_modal').fadeOut();
				});

					// Datepicker
				$('.wps_datepicker').datepicker({
					dateFormat: 'yy-mm-dd'
				});

					// Handle export button
				$('#wps_wsp_export_csv').on('click', function(){
					var status = $('#wps_wsp_status').val();
					var start  = $('#wps_wsp_start_date').val();
					var end    = $('#wps_wsp_end_date').val();
					console.log( status, start, end );
					$.ajax({
						url: wsp_admin_param.ajaxurl,
						type: 'POST',
						data: {
							action: 'wps_wsp_generate_csv',
							status: status,
							start_date: start,
							end_date: end,
							nonce: wsp_admin_param.wps_auth_nonce
						},
						xhrFields: {
							responseType: 'blob' // important for file download
						},
						success: function(blob){
							var link = document.createElement('a');
							link.href = window.URL.createObjectURL(blob);

							// Dynamic filename
							var filename = 'wps_wsp_report-' + status;
							if(start) filename += '-' + start;
							if(end) filename += '-' + end;
							filename += '.csv';

							link.download = filename;
							document.body.appendChild(link);
							link.click();
							document.body.removeChild(link);

							$('#wps_wsp_export_modal').fadeOut();
						},
						error: function(){
							alert('Error generating CSV. Please try again.');
						}
					});
				});

		// expoert functionality.
	});
	
	
})( jQuery );

