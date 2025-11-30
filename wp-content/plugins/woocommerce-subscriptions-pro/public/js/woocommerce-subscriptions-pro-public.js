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

	 $(document).ready(function() {

		jQuery( document ).on('click','.wps_wsp_apply_giftcard_coupon',function( e ) {
			e.preventDefault();
			
			jQuery("#wps-wsp-my-aacount-ajax-loading-gif").show();
			var subscription_id = jQuery(this).attr('data-id');
			var coupon_code = jQuery('#wps_wsp_gift_coupon_'+subscription_id ).val();

			if( '' != subscription_id && '' != coupon_code ) {

				jQuery.ajax({
					url: wsp_pro_public_param.ajaxurl,
					type: "POST",
					dataType :'json',
					data: {
						'action': 'wps_wsp_apply_giftcard_coupon',
						'subscription_id' : subscription_id,
						'coupon_code' : coupon_code,
						'wps_wsp_nonce': wsp_pro_public_param.wps_wsp_nonce
					},
					success:function(response) {
						
			            if ( response.result ) {
			            	jQuery("#wps-wsp-my-aacount-ajax-loading-gif").hide();
			            	jQuery(document).find('.wps_wsp_coupon_error_'+subscription_id).removeClass('wps_wsp_coupon_error_msg');
			            	jQuery(document).find('.wps_wsp_coupon_error_'+subscription_id).addClass('wps_wsp_coupon_success_msg');
			            	jQuery(document).find('.wps_wsp_coupon_error_'+subscription_id).show();
			            	jQuery(document).find('.wps_wsp_coupon_error_'+subscription_id).html(response.msg);
			            	setTimeout(
								function()
								  {
									location.reload();
								},
								2000
							);

			            } else {
			            	jQuery("#wps-wsp-my-aacount-ajax-loading-gif").hide();
			            	jQuery(document).find('.wps_wsp_coupon_error_'+subscription_id).removeClass('wps_wsp_coupon_success_msg');
			            	jQuery(document).find('.wps_wsp_coupon_error_'+subscription_id).addClass('wps_wsp_coupon_error_msg');
			            	jQuery(document).find('.wps_wsp_coupon_error_'+subscription_id).show();
			            	jQuery(document).find('.wps_wsp_coupon_error_'+subscription_id).html(response.msg);
			            }
					}
				});
			}
			else{
				jQuery("#wps-wsp-my-aacount-ajax-loading-gif").hide();
				jQuery(document).find('.wps_wsp_coupon_error_'+subscription_id).removeClass('.wps_wsp_coupon_success_msg');
			    jQuery(document).find('.wps_wsp_coupon_error_'+subscription_id).addClass('.wps_wsp_coupon_error_msg');
				jQuery(document).find('.wps_wsp_coupon_error_'+subscription_id).show();
				jQuery(document).find('.wps_wsp_coupon_error_'+subscription_id).html(wsp_pro_public_param.error_text);
			}
		});
	});
})( jQuery );
