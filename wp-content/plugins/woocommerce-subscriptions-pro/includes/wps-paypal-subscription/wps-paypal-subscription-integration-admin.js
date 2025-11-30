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
	$(function() {
		jQuery(document).on( 'click', '#woocommerce_wps_paypal_subscription_validate', function(e){
			e.preventDefault();
			var clientID = jQuery( 'input[name="woocommerce_wps_paypal_subscription_client_id"]' ).val();
			var clientSecret = jQuery( 'input[name="woocommerce_wps_paypal_subscription_client_secret"]' ).val();
			var data = {
				clientID : clientID,
				clientSecret : clientSecret,
				testMode : false,
				nonce: wps_paypal_subscription.auth_nonce,
				action: 'wps_paypal_subscription_integration_keys_validation',
			}
			if ( ! clientID || ! clientSecret ) {
				alert( wps_paypal_subscription.empty_fields );
				return;
			}
			jQuery.ajax({
				type: 'post',
				dataType: 'json',
				url: wps_paypal_subscription.ajaxurl,
				data: data,
				success: function(data) {
					alert( data.msg );
				}
			});
		})

		jQuery(document).on( 'click', '#woocommerce_wps_paypal_subscription_validate_test', function(e){
			e.preventDefault();
			var clientID = jQuery( 'input[name="woocommerce_wps_paypal_subscription_sandbox_client_id"]' ).val();
			var clientSecret = jQuery( 'input[name="woocommerce_wps_paypal_subscription_sandbox_client_secret"]' ).val();
			var data = {
				clientID : clientID,
				clientSecret : clientSecret,
				testMode : true,
				nonce: wps_paypal_subscription.auth_nonce,
				action: 'wps_paypal_subscription_integration_keys_validation',
			}
			if ( ! clientID || ! clientSecret ) {
				alert( wps_paypal_subscription.empty_fields );
				return;
			}
			jQuery.ajax({
				type: 'post',
				dataType: 'json',
				url: wps_paypal_subscription.ajaxurl,
				data: data,
				success: function(data) {
					alert( data.msg );
				}
			});
		})
	});

})( jQuery );
