
jQuery( document) .ready( function( $ ) {
    //Show, hide fields for simple subscriptions.
    $('#_wps_sfw_product').change(function() {

        if($(this).is(':checked')) {
           
            $('.wps_subscriptions_options').removeClass('wcfm_ele_hide wcfm_block_hide wcfm_head_hide wps_wsp_wcfm_ele_hide');
            resetCollapsHeight($('.collapse-open').next('.wcfm-container').find('.wcfm_ele:not(.wcfm_title):first'));
        } else {
           
            $('.wps_subscriptions_options').addClass('wcfm_ele_hide wcfm_block_hide wcfm_head_hide wps_wsp_wcfm_ele_hide');
        }
    }).change();

    /*Subscription interval set*/
    $('#wps_sfw_subscription_interval').on('change', function() {
        var current_selection = $(this).val();
        var expiry_interval = $('#wps_sfw_subscription_expiry_interval');
        if ( current_selection == 'day' ) {
             expiry_interval.empty();
             expiry_interval.append($('<option></option>').attr('value','day').text( wsp_wcfm_param.day ) );

        }
        else if ( current_selection == 'week' ) {
             expiry_interval.empty();
             expiry_interval.append($('<option></option>').attr('value','week').text( wsp_wcfm_param.week ) );
           
        }
        else if( current_selection == 'month' ) {
            expiry_interval.empty();
            expiry_interval.append($('<option></option>').attr('value','month').text( wsp_wcfm_param.month ) );
            
        }
        else if( current_selection == 'year' ) {
            expiry_interval.empty();
            expiry_interval.append($('<option></option>').attr('value','year').text( wsp_wcfm_param.year ) );
        }
    }).change();

    //Set subscription expiry validation.
    $(document).on( 'keyup', '#wps_sfw_subscription_number', function() {
        var subscription_number = jQuery('#wps_sfw_subscription_number').val();
        $('#wps_sfw_subscription_expiry_number').prop('min', subscription_number );
    });

   //Set subscription expiry validation.
    $(document).on( 'keyup', '#wps_sfw_subscription_expiry_number', function() {
        var subscription_number = $('#wps_sfw_subscription_number').val();
        var subscription_expiry = $('#wps_sfw_subscription_expiry_number').val();
        if ( subscription_expiry != '' ) {
            if ( Number( subscription_expiry ) < Number( subscription_number ) ) {
                alert( wsp_wcfm_param.expiry_notice );
            }
        }

    });

    //Set variable subscription expiry validation.
    $(document).on( 'keyup', '.wps_sfw_variation_subscription_expiry_number', function() {
        var current_loop = $(this).attr('id').slice(-1);
        var subscription_number = $('#variations_wps_sfw_variation_subscription_number_'+current_loop ).val();
        var subscription_expiry = $(this).val();
        if ( subscription_expiry != '' ) {
            if ( Number( subscription_expiry ) < Number( subscription_number ) ) {
                alert( wsp_wcfm_param.expiry_notice );
            }
        }
    });

    //Set variable subscription expiry validation.
    $(document).on( 'change', '.wps_sfw_variation_subscription_interval', function() {
         var current_selection = $(this).val();
         var current_loop = $(this).attr('id').slice(-1);
         
		 var expiry_interval = $('#variations_wps_sfw_variation_subscription_expiry_interval_'+current_loop );
         if ( current_selection == 'day' ) {
              expiry_interval.empty();
              expiry_interval.append($('<option></option>').attr('value','day').text( wsp_wcfm_param.day ) );
 
         }
         else if ( current_selection == 'week' ) {
              expiry_interval.empty();
              expiry_interval.append($('<option></option>').attr('value','week').text( wsp_wcfm_param.week ) );
            
         }
         else if( current_selection == 'month' ) {
             expiry_interval.empty();
             expiry_interval.append($('<option></option>').attr('value','month').text( wsp_wcfm_param.month ) );
             
         }
         else if( current_selection == 'year' ) {
             expiry_interval.empty();
             expiry_interval.append($('<option></option>').attr('value','year').text( wsp_wcfm_param.year ) );
         }
    }).change();
    
    //Show, hide fields for variable subscriptions.
	function wpsSubscriptionVariationShow() {
		$('.wps_sfw_variable_product').each(function() {
			$(this).off('change').on('change', function() {
				if($(this).is(':checked')) {
					$(this).parent().find('.wps_sfw_variation_subscription_number').removeClass('wps_wsp_wcfm_ele_hide');
                    $(this).parent().find('.wps_sfw_variation_subscription_interval').removeClass('wps_wsp_wcfm_ele_hide');
                    $(this).parent().find('.wps_sfw_variation_subscription_expiry_number').removeClass('wps_wsp_wcfm_ele_hide');
                    $(this).parent().find('.wps_sfw_variation_subscription_expiry_interval ').removeClass('wps_wsp_wcfm_ele_hide');
                    $(this).parent().find('.wps_sfw_variation_subscription_initial_signup_price').removeClass('wps_wsp_wcfm_ele_hide');
                    $(this).parent().find('.wps_sfw_variation_subscription_free_trial_number ').removeClass('wps_wsp_wcfm_ele_hide');
                    $(this).parent().find('.wps_sfw_variation_subscription_free_trial_interval').removeClass('wps_wsp_wcfm_ele_hide');
                    
					resetCollapsHeight($('#variations'));
				} else {
					$(this).parent().find('.wps_sfw_variation_subscription_number').addClass('wps_wsp_wcfm_ele_hide');
                    $(this).parent().find('.wps_sfw_variation_subscription_interval').addClass('wps_wsp_wcfm_ele_hide');
                    $(this).parent().find('.wps_sfw_variation_subscription_expiry_number').addClass('wps_wsp_wcfm_ele_hide');
                    $(this).parent().find('.wps_sfw_variation_subscription_expiry_interval ').addClass('wps_wsp_wcfm_ele_hide');
                    $(this).parent().find('.wps_sfw_variation_subscription_initial_signup_price').addClass('wps_wsp_wcfm_ele_hide');
                    $(this).parent().find('.wps_sfw_variation_subscription_free_trial_number ').addClass('wps_wsp_wcfm_ele_hide');
                    $(this).parent().find('.wps_sfw_variation_subscription_free_trial_interval').addClass('wps_wsp_wcfm_ele_hide');
					resetCollapsHeight($('#variations'));
				}
			}).change();
		});
	}
	wpsSubscriptionVariationShow();

});