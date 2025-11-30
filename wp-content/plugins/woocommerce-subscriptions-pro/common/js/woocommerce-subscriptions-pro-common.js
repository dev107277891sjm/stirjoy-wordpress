(function( $ ) {
	'use strict';

	/**
	 * All of the code for your common JavaScript source
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

	$('form.cart').on('submit', function() {
		var type = null;
		if ( $('input[name="wps_type_selection"][value="one_time"]').is(':checked')) {
			type = 'one_time';
		} else if( $('input[name="wps_type_selection"][value="subscribe"]').is(':checked') ) {
			type = 'subscribe';
		}
		$('<input>').attr({
			type: 'hidden',
			name: 'wps_type_selection',
			value: type
		}).appendTo($(this));
	});

	$(document ).on('submit', 'form.cart' ,function() {
		var type = null;
		if ( $('input[name="wps_type_selection"][value="one_time"]').is(':checked')) {
			type = 'one_time';
		} else if( $('input[name="wps_type_selection"][value="subscribe"]').is(':checked') ) {
			type = 'subscribe';
		}
		$('<input>').attr({
			type: 'hidden',
			name: 'wps_type_selection',
			value: type
		}).appendTo($(this));
	});

	$(document).ready(function() {
		if ( $('.wps_sfw_check_simple_cart_subscription_purchase').length > 0 ) {
			$('.wps_sfw_check_simple_cart_subscription_purchase').prop('checked', true);
		} else {
			setTimeout(() => {
				$('.wps_sfw_check_simple_cart_subscription_purchase').prop('checked', true);
			}, 100);
		}
	});
	
	$( document ).on( 'click', '.wps_wsp_get_edit_form', function(e){
		e.preventDefault();
		
		const subscription_id = $(this).data( 'subscription_id' );
		const data = {
			action:'wps_wsp_get_subscription_edit_form',
			nonce : wsp_common_param.nonce,
			subscription_id : subscription_id,
		};
	
		$.ajax(
			{
				url: wsp_pro_public_param.ajaxurl,
				type: "POST",
				data: data,
				success: function(response)
				{
					if ( response ) {
						$( '.subscription-edit-container' ).removeClass( 'wps-not-content' );
						$( '.subscription-edit-container' ).addClass( 'wps-has-content' );
	
						$( '.subscription-edit-container' ).html( response );
					}
				}
			}
		);
	});
	
	$( document ).on('submit', '#wps-wps-subscription-edit-form',function(event) {
		event.preventDefault(); // Prevent the default form submission
		
		// Serialize form data
		var formData = $(this).serialize();
	
		console.log(formData);
		$.ajax({
			url: wsp_pro_public_param.ajaxurl, // Replace with the URL to handle the form submission
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'wps_wsp_update_subscription', // The custom action name
				form_data: formData,
				nonce : wsp_common_param.nonce,
			},
			success: function(response) {
				console.log(response);

				if ( response.status ) {
					alert(wsp_common_param.update_success );
				} else {
					alert(wsp_common_param.update_error );
				}
				window.location.reload();
			},
			error: function(xhr, status, error) {
			  // Handle error
			}
		});
	});

	$( document ).on( 'click', '.wps-wsp-close-popup', function(){
		$( '.subscription-edit-container' ).addClass( 'wps-not-content' );
		$( '.subscription-edit-container' ).removeClass( 'wps-has-content' );
		$( '.subscription-edit-container' ).empty();
	});
})( jQuery );

 