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
		
		 $('#variable_product_options').on('change','.wps_sfw_variation_enable', function() {
            $( this ).closest( '.woocommerce_variation' ).find( '.wps_sfw_product' ).first().hide();

			if ( $( this ).is( ':checked' ) ) {
				$( this ).closest( '.woocommerce_variation' ).find( '.wps_sfw_product' ).first().show();
				
				var dateToday = new Date(); 
				$(function() {
				$( ".wps_sfw_subscription_start_date" ).datepicker({
					showButtonPanel: true,
					dateFormat: 'yy-mm-dd',
					minDate: dateToday
				});
				});

			}
        });

		
		$( '#woocommerce-product-data' ).on( 'woocommerce_variations_loaded', function(event, needsUpdate) {
			needsUpdate = needsUpdate || false;
			var wrapper = $( '#woocommerce-product-data' );
			if ( ! needsUpdate ) {
				$( 'input.wps_sfw_variation_enable', wrapper ).trigger( 'change' );
			}
			jQuery(document).find('[name^="wps_wsp_variation_enbale_certain_month"]').each(function(index,element){
				var current_selection = $(this).attr('data-attr');
				
				if ( $( this ).is( ':checked' ) ) {
					jQuery(document).find('.wps_wsp_certain_date_enable_wrap'+current_selection).removeClass('wps_active');
				} else {
					jQuery(document).find('.wps_wsp_certain_date_enable_wrap'+current_selection).addClass('wps_active');
				}
				
			});
			// add select2 for multiselect.
			if( $('.wps_learnpress_course').length > 0 ) {
				$('.wps_learnpress_course').select2();
			}
		});

		$('#woocommerce-product-data').on('keyup','[name^="wps_sfw_variation_subscription_expiry_number"]',function() {
			
			var current_loop = $(this).attr('data-attr');
			if (current_loop != '') {
				var subscription_number = $('#wps_sfw_variation_subscription_number'+current_loop ).val();
				$(this).prop('min', subscription_number );
			}

		});
		
		/*Subscription interval set*/
		$('#woocommerce-product-data').on('change','[name^="wps_sfw_variation_subscription_interval"]',function(){
			var current_selection = $(this).val();
			var current_loop = $(this).attr('data-attr');
			var expiry_interval = $('#wps_sfw_variation_subscription_expiry_interval'+current_loop );

            if ( current_selection == 'day' ) {
                 expiry_interval.empty();
                 expiry_interval.append($('<option></option>').attr('value','day').text( wps_wsp_product_param.day ) );
    
            }
            else if ( current_selection == 'week' ) {
                 expiry_interval.empty();
                 expiry_interval.append($('<option></option>').attr('value','week').text( wps_wsp_product_param.week ) );
               
            }
            else if( current_selection == 'month' ) {
                expiry_interval.empty();
                expiry_interval.append($('<option></option>').attr('value','month').text( wps_wsp_product_param.month ) );
                
            }
            else if( current_selection == 'year' ) {
                expiry_interval.empty();
                expiry_interval.append($('<option></option>').attr('value','year').text( wps_wsp_product_param.year ) );
            }

		});

		/*For simple product*/
		jQuery(document).on('change','#wps_wsp_enbale_certain_month',function() {
			
			if ( $( this ).is( ':checked' ) ) {
				jQuery(document).find('.wps_wsp_certain_date_enable_wrap').removeClass('wps_active');
			} else {
				jQuery(document).find('.wps_wsp_certain_date_enable_wrap').addClass('wps_active');
			}
			
		});
		
		jQuery(document).on('change','#wps_sfw_subscription_interval',function() {
			var current_selection = jQuery(this).val();
			if ( current_selection == 'week' ) {
				jQuery(document).find('.wps_wsp_certain_date_enable_wrap').show();
				jQuery(document).find('.wps_wsp_certain_date_enable').show();
				jQuery(document).find('.wps_wsp_certain_date_enable_week').show();
				jQuery(document).find('.wps_wsp_certain_date_enable_month').hide();
				jQuery(document).find('.wps_wsp_certain_date_enable_year').hide();
			} 
			else if( current_selection=='month' ) {
				jQuery(document).find('.wps_wsp_certain_date_enable_wrap').show();
				jQuery(document).find('.wps_wsp_certain_date_enable').show();
				jQuery(document).find('.wps_wsp_certain_date_enable_month').show();
				jQuery(document).find('.wps_wsp_certain_date_enable_week').hide();
				jQuery(document).find('.wps_wsp_certain_date_enable_year').hide();
			}
			else if( current_selection=='year' ) {
				jQuery(document).find('.wps_wsp_certain_date_enable_wrap').show();
				jQuery(document).find('.wps_wsp_certain_date_enable').show();
				jQuery(document).find('.wps_wsp_certain_date_enable_year').show();
				jQuery(document).find('.wps_wsp_certain_date_enable_week').hide();
				jQuery(document).find('.wps_wsp_certain_date_enable_month').hide();
			}
			else{
				jQuery(document).find('.wps_wsp_certain_date_enable_wrap').hide();
				jQuery(document).find('.wps_wsp_certain_date_enable').hide();
			}
		});

		/*For variable product*/
		$('#woocommerce-product-data').on('change','[name^="wps_wsp_variation_enbale_certain_month"]',function(){
			var current_selection = $(this).attr('data-attr');
			
			if ( $( this ).is( ':checked' ) ) {
				jQuery(document).find('.wps_wsp_certain_date_enable_wrap'+current_selection).removeClass('wps_active');
			} else {
				jQuery(document).find('.wps_wsp_certain_date_enable_wrap'+current_selection).addClass('wps_active');
			}
		});

		$('#woocommerce-product-data').on('change','[name^="wps_sfw_variation_subscription_interval"]',function(){
			var current_selection = $(this).val();
			var current_loop = $(this).attr('data-attr');

			if ( current_selection == 'week' ) {
				jQuery(document).find('.wps_wsp_certain_date_enable_wrap'+current_loop).show();
				jQuery(document).find('.wps_wsp_certain_date_enable'+current_loop).show();
				jQuery(document).find('.wps_wsp_certain_date_enable_week'+current_loop).show();
				jQuery(document).find('.wps_wsp_certain_date_enable_month'+current_loop).hide();
				jQuery(document).find('.wps_wsp_certain_date_enable_year'+current_loop).hide();
			} 
			else if( current_selection=='month' ) {
				jQuery(document).find('.wps_wsp_certain_date_enable_wrap'+current_loop).show();
				jQuery(document).find('.wps_wsp_certain_date_enable'+current_loop).show();
				jQuery(document).find('.wps_wsp_certain_date_enable_month'+current_loop).show();
				jQuery(document).find('.wps_wsp_certain_date_enable_week'+current_loop).hide();
				jQuery(document).find('.wps_wsp_certain_date_enable_year'+current_loop).hide();
			}
			else if( current_selection=='year' ) {
				jQuery(document).find('.wps_wsp_certain_date_enable_wrap'+current_loop).show();
				jQuery(document).find('.wps_wsp_certain_date_enable'+current_loop).show();
				jQuery(document).find('.wps_wsp_certain_date_enable_year'+current_loop).show();
				jQuery(document).find('.wps_wsp_certain_date_enable_week'+current_loop).hide();
				jQuery(document).find('.wps_wsp_certain_date_enable_month'+current_loop).hide();
			}
			else{
				jQuery(document).find('.wps_wsp_certain_date_enable_wrap'+current_loop).hide();
				jQuery(document).find('.wps_wsp_certain_date_enable'+current_loop).hide();
			}
		});
	
		//Set variable subscription expiry validation.
		$(document).on( 'keyup', '.wps_sfw_variation_subscription_expiry_number', function() {
			var current_loop = $(this).attr('id').slice(-1);
			var subscription_number = $('#s'+current_loop ).val();
			var subscription_expiry = $(this).val();
			if ( subscription_expiry != '' ) {
				if ( Number( subscription_expiry ) < Number( subscription_number ) ) {
					alert( wsp_wcfm_param.expiry_notice );
				}
			}
		});

		// subscription box modification

 function toggleFields($card){
    var type = $card.find('.wps_sfw_step_type').val();
    $card.find('.wps_sfw_products_field').toggle(type === 'specific_products');
    $card.find('.wps_sfw_categories_field').toggle(type === 'specific_categories');
    // Init only the visible select (avoids width glitches)
    if (type === 'specific_products') {
      ensureEnhanced($card.find('.wc-product-search'));
    } else {
      ensureEnhanced($card.find('.wc-category-search'));
    }
  }

  function ensureEnhanced($els){
        if (!$els || !$els.length) return;

        // First try Woo’s own initializer
        $(document.body).trigger('wc-enhanced-select-init');

        // If still not enhanced (older WC or custom classes), do a manual init
        $els.filter(':not(.enhanced)').each(function(){
        var $sel = $(this);
        if (typeof $.fn.selectWoo !== 'function' && typeof $.fn.select2 !== 'function') return;

        var isProduct   = $sel.hasClass('wc-product-search');
        var action      = $sel.data('action') || (isProduct ? 'woocommerce_json_search_products_and_variations' : 'woocommerce_json_search_categories');
        var nonceKey    = isProduct ? 'search_products_nonce' : 'search_categories_nonce';
        var lib         = $.fn.selectWoo ? 'selectWoo' : 'select2';
            
        var args = {
            allowClear: !!$sel.data('allow_clear'),
            placeholder: $sel.data('placeholder') || '',
            minimumInputLength: 1,
            ajax: {
            url: (window.wc_enhanced_select_params || {}).ajax_url || ajaxurl,
            dataType: 'json', delay: 250,
            data: function(params){
                return {
                term: params.term || '',
                action: action,
                security: (window.wc_enhanced_select_params || {})[nonceKey],
                exclude: $sel.data('exclude') || [],
                include: $sel.data('include') || [],
                limit: $sel.data('limit') || 30
                };
            },
            processResults: function(data){
                // Woo may return { results: [...] } or {id:text} map
                var results = data && (data.results || data);
                if ($.isArray(results)) return { results: results };
                var out = [];
                $.each(results || {}, function(id, text){ out.push({ id:id, text:text }); });
                return { results: out };
            },
            cache: true
            },
            escapeMarkup: function(m){ return m; }
        };

        $sel[lib](args).addClass('enhanced');
        });
    }

  // Add Step
  $('#wps_sfw_add_step').on('click', function(e){
    e.preventDefault();

    var $wrap   = $('#wps_sfw_steps_wrap');
    var count   = $wrap.find('.wps_sfw_step_card').length + 1;
    var stepKey = 'step' + count;

    var raw = $('#tmpl-wps-sfw-step-template').html();
    var html = raw.replace(/{{stepKey}}/g, stepKey)
                  .replace(/{{STEP_TITLE}}/g, stepKey.toUpperCase());
    var $card = $(html);

    // Append first, then init
    $wrap.append($card);

    // Make sure there are zero Select2 artifacts on fresh markup (belt & suspenders)
    stripSelect2Artifacts($card);

    // Initialize what’s visible
    toggleFields($card);
  });

  function stripSelect2Artifacts($scope){
    $scope.find('.select2, .select2-container').remove();
    $scope.find('select').each(function(){
      $(this)
        .removeClass('enhanced select2-hidden-accessible')
        .removeAttr('data-select2-id')
        .off(); // remove any stale handlers if cloned
    });
  }

  function destroyEnhancedIn($scope){
    $scope.find('select').each(function(){
      var $s = $(this);
      try {
        if ($.fn.selectWoo && $s.hasClass('select2-hidden-accessible')) { $s.selectWoo('destroy'); }
        else if ($.fn.select2 && $s.hasClass('select2-hidden-accessible')) { $s.select2('destroy'); }
      } catch(e){}
      $s.removeClass('enhanced select2-hidden-accessible').removeAttr('data-select2-id');
    });
    $scope.find('.select2, .select2-container').remove();
  }

    // reindex remaining cards to step1, step2, …
  function reindexSteps(){
    var $wrap = $('#wps_sfw_steps_wrap');
    destroyEnhancedIn($wrap); // simplest: destroy all, then re-init after renaming

    $wrap.find('.wps_sfw_step_card').each(function(i){
      var $card    = $(this);
      var newKey   = 'step' + (i+1);

      $card.attr('data-step', newKey);
      $card.find('.wps_sfw_step_header strong').text(newKey.toUpperCase());

      // label input
      var $labelInput = $card.find('input[name^="wps_sfw_steps"][name$="[label]"]');
      var $labelLbl   = $labelInput.closest('.form-field').find('label');
      $labelInput.attr({
        name: 'wps_sfw_steps[' + newKey + '][label]',
        id:   'wps_sfw_label_' + newKey
      });
      $labelLbl.attr('for', 'wps_sfw_label_' + newKey);

      // type select
      var $typeSel = $card.find('select.wps_sfw_step_type');
      var $typeLbl = $typeSel.closest('.form-field').find('label');
      $typeSel.attr({
        name: 'wps_sfw_steps[' + newKey + '][type]',
        id:   'wps_sfw_type_' + newKey
      });
      $typeLbl.attr('for', 'wps_sfw_type_' + newKey);

      // products select
      var $prodSel = $card.find('select.wc-product-search');
      var $prodLbl = $prodSel.closest('.form-field').find('label');
      $prodSel.attr({
        name: 'wps_sfw_steps[' + newKey + '][product_ids][]',
        id:   'wps_sfw_products_' + newKey
      });
      $prodLbl.attr('for', 'wps_sfw_products_' + newKey);

      // categories select
      var $catSel = $card.find('select.wc-category-search');
      var $catLbl = $catSel.closest('.form-field').find('label');
	  
      $catSel.attr({
        name: 'wps_sfw_steps[' + newKey + '][category_ids][]',
        id:   'wps_sfw_categories_' + newKey
      });
      $catLbl.attr('for', 'wps_sfw_categories_' + newKey);
    });

    // re-init all selects and fix visibility per card
    $(document.body).trigger('wc-enhanced-select-init');
    $('#wps_sfw_steps_wrap .wps_sfw_step_card').each(function(){ toggleFields($(this)); });
  }

   // ---------- remove step (this is what you were missing) ----------
  $('#wps_sfw_steps_wrap').on('click', '.wps_sfw_remove_step', function(e){
    e.preventDefault();

    var $card = $(this).closest('.wps_sfw_step_card');

    // optional: prevent removing the last card
    // if ($('#wps_sfw_steps_wrap .wps_sfw_step_card').length <= 1) return;

    // cleanly destroy select2/selectWoo inside this card to avoid DOM ghosts
    destroyEnhancedIn($card);

    // remove card
    $card.remove();

    // reindex everything so names are contiguous: step1, step2, …
    reindexSteps();
  });

  // Initial page load: init any existing fields
  $(document.body).trigger('wc-enhanced-select-init');
  $('.wps_sfw_step_card').each(function(){ toggleFields($(this)); });

// subscription box modification

	});
	
	jQuery( window ).load( function() {
		if( jQuery(document).find('#wps_wsp_enbale_certain_month').is( ':checked' ) ) {
			jQuery(document).find('.wps_wsp_certain_date_enable_wrap').removeClass('wps_active');
		}
		else{
			jQuery(document).find('.wps_wsp_certain_date_enable_wrap').addClass('wps_active');
		}
	});

	})( jQuery );