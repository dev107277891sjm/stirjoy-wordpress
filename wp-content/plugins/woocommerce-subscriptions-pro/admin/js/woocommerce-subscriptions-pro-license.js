(function( $ ) {
	'use strict';

	/**
	 * License validation.
	 *
	 */

	 $(document).ready(function() {
	 
		// On License form submit.
		jQuery( '#wps_wsp_license_activate' ).on(
			'click',
			function(e) {
				e.preventDefault();
				jQuery( '#wps-wsp-ajax-loading-gif' ).show();
				var wps_wsp_license_key = jQuery( 'input#wps_wsp_license_key' ).val();
				wps_wsp_license_request( wps_wsp_license_key );
			}
		);
		function wps_wsp_license_request( wps_wsp_license_key ) {

			jQuery.ajax({
					type:'POST',
					dataType: 'json',
					url: wsp_admin_param.ajaxurl,
					data: {
						'action': 'wps_wsp_validate_license_key',
						'wps_wsp_license_code': wps_wsp_license_key,
						'wps_wsp_license_nonce': wsp_admin_param.wps_wsp_license_nonce,
					},					
					success:function( data ) {
						jQuery( '#wps-wsp-ajax-loading-gif' ).hide();
						if ( false === data.status ) {
							jQuery( "p#wps_wsp_license_activation_status" ).css( "color", "#ff3333" );
							jQuery( 'p#wps_wsp_license_activation_status' ).html( data.msg );
						} else {
							jQuery( "p#wps_wsp_license_activation_status" ).css( "color", "#42b72a" );
							jQuery( 'p#wps_wsp_license_activation_status' ).html( data.msg );
							if ( true === data.status ) {
								setTimeout(
									function() {
										window.location = wsp_admin_param.reloadurl;
									},
									500
								);
							}
						}
					}
				}
			);
		}
	});

	})( jQuery );
