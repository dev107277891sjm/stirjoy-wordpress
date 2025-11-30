var thwma_address = (function($, window, document) {
	'use strict';

	select_cart_mutlti_shipping();
	create_multiproduct_product_multi_shipping();
	ship_to_multi_address();
	shipping_method_change();
	update_product_qty_from_multi_ship();
	hide_shipping_custom_fields();
	//default_address_set_first()

	var default_shipping = $('#thwma_checkbox_shipping').val();
	var active = false;

	var bpopup = $("#thwma-billing-tile-field");
	var spopup = $("#thwma-shipping-tile-field");

	var cspopup = $("#thwma-shipping-tile-field");

	var cartpopup = $("#thwma-shipping-tile-field");
	// Address style.
	if($(".address-text").length  > 0){
		var maxHeight = Math.max.apply(null, $(".address-text").map(function (){
   	 		return $(this).outerHeight();
		}).get());

		$(".address-text").outerHeight(maxHeight);
		$(".address-text.wrapper-only").outerHeight(maxHeight+41);
	}

	// Initialise popups.
	if($(window).width()<600){
		var popupwidth = $(window).width() - 20;
		bpopup.dialog({
	       'dialogClass'   	: 'wp-dialog thwma-popup',
	       'title'         	: thwma_public_var.billing_address,
	       'modal'         	: true,
	       'autoOpen'      	: false,
	       'width'       	: popupwidth,
 		});

		spopup.dialog({
           'dialogClass'   : 'wp-dialog thwma-popup',
           'title'         : thwma_public_var.shipping_address,
           'modal'         : true,
           'autoOpen'      : false,
           'width'         : popupwidth,
       	});

 		cspopup.dialog({
           'dialogClass'   : 'wp-dialog thwma-cart-popup',
           'title'         : thwma_public_var.shipping_address,
           'modal'         : true,
           'autoOpen'      : false,
           'width'         : popupwidth,
       	});

       	cartpopup.dialog({
           'dialogClass'   : 'wp-dialog thwma-cart-popup',
           'title'         : thwma_public_var.shipping_address,
           'modal'         : true,
           'autoOpen'      : false,
           'width'         : popupwidth,
       	});

	}else{
		bpopup.dialog({
	       'dialogClass'   	: 'wp-dialog thwma-popup',
	       'title'         	: thwma_public_var.billing_address,
	       'modal'         	: true,
	       'autoOpen'      	: false,
	       'minHeight'		: 400,
	       'maxHeight'     	: 600,
	       'width'       	: 780,
 		});

		spopup.dialog({
           'dialogClass'   : 'wp-dialog thwma-popup',
           'title'         : thwma_public_var.shipping_address,
           'modal'         : true,
           'autoOpen'      : false,
           'minHeight'		: 400,
           'maxHeight'     : 600,
           'width'         : 780,
       	});

		cspopup.dialog({
           'dialogClass'   : 'wp-dialog thwma-cart-popup',
           'title'         : thwma_public_var.shipping_address,
           'modal'         : true,
           'autoOpen'      : false,
           'minHeight'		: 400,
           'maxHeight'     : 600,
           'width'         : 780,
       	});

		cartpopup.dialog({
           'dialogClass'   : 'wp-dialog thwma-cart-popup',
           'title'         : thwma_public_var.shipping_address,
           'modal'         : true,
           'autoOpen'      : false,
           'minHeight'		: 400,
           'maxHeight'     : 600,
           'width'         : 780,
       });
	}

	// Show billing popup.
  	function show_billing_popup(e){
  		e.preventDefault();

  		$('.thwma-btn').removeClass('slctd-adrs');
  		var selected_address = $('#thwma_hidden_field_billing').val();

  		if(selected_address){
  		 	$('.'+selected_address).addClass('slctd-adrs');
  		}

		bpopup.dialog('open');
		active = false;
	}

	// Show shipping popup.
	function show_shipping_popup(e){
  		e.preventDefault();
		spopup.dialog('open');
		active = false;
	}

	// Show custom popup.
	function show_custom_popup(e,section,map_type){
  		e.preventDefault();
  		var cus_popup = $("#thwma-custom-tile-field_"+section);

  		if($(window).width()<600){
			var popupwidth = $(window).width() - 20;

	  		cus_popup.dialog({
	           'dialogClass'   : 'wp-dialog thwma-popup',
	           'title'         : thwma_public_var.addresses,
	           'modal'         : true,
	           'autoOpen'      : false,

	           'width'         : popupwidth ,
	       	});

	    }else{
	    	cus_popup.dialog({
	           'dialogClass'   : 'wp-dialog thwma-popup',
	           'title'         : 'Addresses',
	           'modal'         : true,
	           'autoOpen'      : false,
	           // 'closeOnEscape' : true,
	           'minHeight'		: 400,
	           'maxHeight'     : 600,
	           'width'         : 780,
	       	});
	    }

  		active = false;
		setup_section_address_slider(section,map_type);
		cus_popup.dialog('open');
	}

	// Populate selected addresses on checkout form.
	function populate_selected_address(elm, address_type, key){
        var selected_address_id = key;
        var data = {
            action: 'get_address_with_id',
            security: thwma_public_var.get_address_with_id_nonce,
            selected_address_id: selected_address_id,
            selected_type:address_type,
        };
        $.ajax({
               	url: thwma_public_var.ajax_url,
               	data: data,
               	type: 'POST',
               	success: function (response) {
	                var sell_countries = thwma_public_var.sell_countries;
	                var sell_countries_size = Object.keys(sell_countries).length;
	                var address_fields = [];

	                if(address_type == 'billing'){
	                    address_fields = thwma_public_var.address_fields_billing;
	                }else{
	                    address_fields = thwma_public_var.address_fields_shipping;
	                }
	                $.each( address_fields, function(f_key, f_type) {
	                	var input_elm = '';
	                	if(f_type == 'radio' || f_type == 'checkboxgroup'){
	                    	input_elm = $("input[name="+f_key+"]");
	                    }else{
	                    	//input_elm = $('#'+f_key);
	                    	input_elm = $(thwma_public_var.checkout_page_form_class).find('#'+f_key);
	                    }

	                    var skip = (sell_countries_size == 1 && f_key == address_type+'_country') ? true : false;
	                    if (sell_countries_size == 1){
	                    	if(f_key == 'billing_country'){
	                    		skip = true;
	                    	} else {
	                    		skip = false;
	                    	}
	                    }
	                    if (!skip && input_elm.length) {
	                        var _type = input_elm.getType();
	                        var _value = response[f_key];

	                        if(f_type === 'file'){
	                            _type = 'file';
	                        }
	                        	// $("select#shipping_country.country_to_state").val("AL");
	                       thwma_public_base.set_field_value_by_elm(input_elm, _type, _value, f_key);
	                    }
	                });
               	}
           });

           if(address_type == 'billing'){
               $('#thwma_hidden_field_billing').val(selected_address_id);
               bpopup.dialog('close');
           }else{
               $('#thwma_hidden_field_shipping').val(selected_address_id);
               spopup.dialog('close');
           }
    }

    // Add new addresses from checkout page.
	function add_new_address(elm, address_type){
        var sell_countries = thwma_public_var.sell_countries;
        var sell_countries_size = Object.keys(sell_countries).length;
        var address_fields = [];

        if(address_type == 'billing'){
            address_fields = thwma_public_var.address_fields_billing;
            $('#thwma_hidden_field_billing').val('add_address');
        }else{
            address_fields = thwma_public_var.address_fields_shipping;
            $('#thwma_hidden_field_shipping').val('add_address');
        }

        $.each( address_fields, function(f_key, f_type) {
            var input_elm = '';

            if(f_type == 'radio' || f_type == 'checkboxgroup'){
                input_elm = $("input[name="+f_key+"]");
            }else{
                input_elm = $('#'+f_key);
            }

            var skip = (sell_countries_size == 1 && f_key == address_type+'_country') ? true : false;
            if (sell_countries_size == 1){
                if(f_key == 'billing_country'){
                    skip = true;
                } else {
                    skip = false;
                }
            }
            if (!skip && input_elm.length) {
                var _type = input_elm.getType();
                if(f_type === 'file'){
                    _type = 'file';
                }

                thwma_public_base.thwma_set_field_value_by_elm(input_elm, _type, '');
            }
        });

        bpopup.dialog('close');
        spopup.dialog('close');
    }

    // Delete addresses.
	function delete_address(elm,type,key){
		if(type == 'billing'){
			$("#thwma-billing-tile-field").append('<div class="ajaxBusy"> <i class="fa fa-spinner" aria-hidden="true"></i></div>');
		}else{
			$("#thwma-shipping-tile-field").append('<div class="ajaxBusy"> <i class="fa fa-spinner" aria-hidden="true"></i></div>');
		}
		var selected_address_id = key;
		var selected_type = type;
		var data = {
			action: 'delete_address_with_id',
			security: thwma_public_var.delete_address_with_id_nonce,
        	selected_address_id: selected_address_id,
        	selected_type : selected_type,
		};
		$('.ajaxBusy').show();
		$.ajax({
       		url: thwma_public_var.ajax_url,
       		data: data,
       		type: 'POST',
       		success: function (response) {

				$('#thwma-billing-tile-field').html(response.result_billing);
				$('#thwma-shipping-tile-field').html(response.result_shipping);
				$('.ajaxBusy').hide();

				var selected_address = $('#thwma_hidden_field_'+type).val();

				//if(type == 'billing'){
					setup_billing_address_slider('billing');
				//}else if(type == 'shipping'){
					setup_shipping_address_slider('shipping');
				//}
  				// if(selected_address){
  				// 	$('.'+selected_address).html('<div class="radio-select"</div>');
  				// }
			}
       	});
	}

	// Set to default addresses.
	function set_default_address(elm,type,key){
		if(type == 'billing'){
			$("#thwma-billing-tile-field").append('<div class="ajaxBusy"> <i class="fa fa-spinner" aria-hidden="true"></i></div>');
		}else{
			$("#thwma-shipping-tile-field").append('<div class="ajaxBusy"> <i class="fa fa-spinner" aria-hidden="true"></i></div>');
		}
		var selected_address_id = key;
		var data = {
			action: 'set_default_address',
			security: thwma_public_var.set_default_address_nonce,
        	selected_address_id : selected_address_id,
        	selected_type : type,
		};
		$('.ajaxBusy').show();
		$.ajax({
       		url: thwma_public_var.ajax_url,
       		data: data,
       		type: 'POST',
       		success: function (response) {
				$('#thwma-billing-tile-field').html(response.result_billing);
				$('#thwma-shipping-tile-field').html(response.result_shipping);
				$('.ajaxBusy').hide();
				var selected_address = $('#thwma_hidden_field_'+type).val();
				//if(type == 'billing'){
					setup_billing_address_slider('billing');
				//}else if(type == 'shipping'){
					setup_shipping_address_slider('shipping');
				//}

  				// if(selected_address){
  				// 	$('.'+selected_address).html('<div class="radio-select"</div>');
  				// }
			}
       	});
	}
	
	// Change function for populate addresses on checkout page.
	$("#thwma-billing-alt").change(function(event){
		event.preventDefault();
		var select_type = this.value;
		var type = 'billing';
		var elm = '';

		if(select_type == 'add_address'){
			add_new_address(elm,type);
		}else{
			populate_selected_address(elm,type,select_type);
		}
	});

	$("#thwma-shipping-alt").change(function(event){
		event.preventDefault();
		var select_type = this.value;
		var type = 'shipping';
		var elm = '';
		if(select_type == 'add_address'){
			add_new_address(elm,type);
		}else{
			populate_selected_address(elm,type,select_type);
		}
	});

	// Custom section start.
	var sections = thwma_public_var.custom_sections;
	if(sections){
		$.each(sections,function(key, val){
			var select_id = "#thwma_"+key;
			var map_fields = val['map_fields'];
			$(select_id).change(function(event){
				var select_type = this.value;
				var map_type = val['maped_section'];
				populate_section_address(select_type,key,map_fields,map_type);
			});
		});
	}

	// Populate custom addresses on checkout page.
	function populate_selected_section_address(e,elm,adrs_key,section,map_type){
		var sections = thwma_public_var.custom_sections;
		var map_fields = '';
		$.each(sections,function(key, val){
			if(key == section){
				map_fields = val['map_fields'];
			}
		});
		var select_type = adrs_key;
		populate_section_address(select_type,section,map_fields,map_type);

	}

	// populate custom addresses function.
	function populate_section_address(select_type,key,map_fields,map_type){
		var data = {
			action: 'get_address_with_id',
			security: thwma_public_var.get_address_with_id_nonce,
        	selected_address_id: select_type,
        	selected_type: map_type,
        	section_name: key,
		};
		$.ajax({
       		url: thwma_public_var.ajax_url,
       		data: data,
       		type: 'POST',
       		success: function (response) {
       			$.each( map_fields , function(address_key,section_field){
   					var meta_val = response[address_key];
					var meta_key=$('#'+section_field);

					thwma_public_base.set_field_value_by_elm(meta_key,'select', meta_val, section_field);
   				});
       		}

       	});
       	$(".ui-dialog-content").dialog("close");
	}


	///////////Accordion//////////

	/*$( "#thwma_billing_accordion" ).accordion({
		active:false,
		collapsible:true,
		autoHeight:false,
	});

	$("#thwma_billing_toggle_show").hide();

	$(".thwma_billing_toggle_accordion").click(function(event){
		event.preventDefault();
        $("#thwma_billing_toggle_show").toggle();
    });

    $( "#thwma_shipping_accordion" ).accordion({
		active:false,
		collapsible:true,
		autoHeight:false,
	});

	$("#thwma_shipping_toggle_show").hide();

	$(".thwma_shipping_toggle_accordion").click(function(event){
		event.preventDefault();
        $("#thwma_shipping_toggle_show").toggle();
    });*/


    $('#ship-to-different-address-checkbox').change(function(){
		if ($('#ship-to-different-address-checkbox').is(':checked')) {
			$('#thwma_checkbox_shipping').val('ship_select');
		}else{
			$('#thwma_checkbox_shipping').val(default_shipping);
		}
	});

    /*******************************
    **** Address Slider - START ****
    ********************************/

    function slider_arrow_limits(items_per_view,prev,next,type){
		var get_addr_count = $('.control-buttons-'+type+' .get_addr_count').val();
		var nw_addr_count = get_addr_count;
		var thslider_viewport_width = $(".thwma_my_acnt .thwma-thslider-viewport."+type).width();
		var address_limit = '';
		var default_address = '';
		if(type == 'billing') {
			var address_limit = thwma_public_var.billing_adr_limit;
			var default_address = thwma_public_var.default_bil_address;
		} else {
			var address_limit = thwma_public_var.shipping_adr_limit;
			var default_address = thwma_public_var.default_ship_address;
		}
		if(address_limit != ''){
			if(parseInt(address_limit) < parseInt(nw_addr_count)){
				nw_addr_count = address_limit;
			}
		}
		var adrs_limit = '';
		var adrs_limit_2 = '';
		if(default_address != ''){
			var adrs_limit = '4';
			var adrs_limit_2 = '3';
			var adrs_limit_3 = '2';
		} else {
			var adrs_limit = '3';
			var adrs_limit_2 = '2';
			var adrs_limit_3 = '1';
		}

		if(thslider_viewport_width){
			var prevBtn = $('.control-buttons .thwma-thslider-prev.'+type);
			var nextBtn = $('.control-buttons .thwma-thslider-next.'+type);
    		if(thslider_viewport_width >600){
    			if(parseInt(nw_addr_count)>parseInt(adrs_limit)){
    				prevBtn.css("display", "block");
					nextBtn.css("display", "block");
    			} else{
    				prevBtn.css("display", "none");
					nextBtn.css("display", "none");
    			}
    		} else if((thslider_viewport_width <600)&&(thslider_viewport_width >300)){
    			if(parseInt(nw_addr_count)>parseInt(adrs_limit_2)){
 					prevBtn.css("display", "block");
					nextBtn.css("display", "block");
    			} else{
    				prevBtn.css("display", "none");
					nextBtn.css("display", "none");
    			}
    		} else if(thslider_viewport_width <300){
    			if(parseInt(nw_addr_count)>parseInt(adrs_limit_3)){
    				prevBtn.css("display", "block");
					nextBtn.css("display", "block");
    			} else{
    				prevBtn.css("display", "none");
					nextBtn.css("display", "none");
    			}
    		}
    	}
    }

    // function slider_arrow_limits(items_per_view,prev,next,type){
    // 		var get_addr_count = $('.control-buttons-'+type+' .get_addr_count').val();
    // 		var nw_addr_count = get_addr_count - 1;
    // 		var thslider_viewport_width = $(".thwma_my_acnt .thwma-thslider-viewport."+type).width();
    // 		if(thslider_viewport_width){
    // 			var prevBtn = $('.control-buttons .thwma-thslider-prev.'+type);
    // 			var nextBtn = $('.control-buttons .thwma-thslider-next.'+type);
	   //  		if(thslider_viewport_width >600){
	   //  			if(nw_addr_count>3){
	   //  				prevBtn.css("display", "block");
				// 		nextBtn.css("display", "block");
	   //  			} else{
	   //  				prevBtn.css("display", "none");
				// 		nextBtn.css("display", "none");
	   //  			}
	   //  		} else if((thslider_viewport_width <600)&&(thslider_viewport_width >300)){
	 		// 		if(nw_addr_count>2){
	   //  				prevBtn.css("display", "block");
				// 		nextBtn.css("display", "block");
	   //  			} else{
	   //  				prevBtn.css("display", "none");
				// 		nextBtn.css("display", "none");
	   //  			}
	   //  		} else if(thslider_viewport_width <300){
	   //  			if(nw_addr_count>1){
	   //  				prevBtn.css("display", "block");
				// 		nextBtn.css("display", "block");
	   //  			} else{
	   //  				prevBtn.css("display", "none");
				// 		nextBtn.css("display", "none");
	   //  			}
	   //  		}
	   //  	}
    // }
    function cart_slider_arrow_limits(items_per_view,prev,next,type){
    		var get_addr_count = $('.control-buttons-'+type+' .get_addr_count').val();
    		var nw_addr_count = get_addr_count - 1;
    		var thslider_viewport_width = $(".thwma_my_acnt .thwma-thslider-viewport.multi-"+type).width();
    		if(thslider_viewport_width){
    			var prevBtn = $('.control-buttons .thwma-thslider-prev.multi-'+type);
    			var nextBtn = $('.control-buttons .thwma-thslider-next.multi-'+type);
	    		if(thslider_viewport_width >600){
	    			if(nw_addr_count>3){
	    				prevBtn.css("display", "block");
						nextBtn.css("display", "block");
	    			} else{
	    				prevBtn.css("display", "none");
						nextBtn.css("display", "none");
	    			}
	    		} else if((thslider_viewport_width <600)&&(thslider_viewport_width >300)){
	 				if(nw_addr_count>2){
	    				prevBtn.css("display", "block");
						nextBtn.css("display", "block");
	    			} else{
	    				prevBtn.css("display", "none");
						nextBtn.css("display", "none");
	    			}
	    		} else if(thslider_viewport_width <300){
	    			if(nw_addr_count>1){
	    				prevBtn.css("display", "block");
						nextBtn.css("display", "block");
	    			} else{
	    				prevBtn.css("display", "none");
						nextBtn.css("display", "none");
	    			}
	    		}
	    	}
    }
	function move_slider(slider, prevBtn, nextBtn, leftPos, startPos, endPos, totalItems, itemsPerView){
		var active = true;
		slider.animate(
        	{left: leftPos},
        	{duration:500,
        		complete: function(){
        			enable_disable_prev_next_action(prevBtn, nextBtn, startPos, endPos, totalItems, itemsPerView);
        			active = false;
        		}
        	}
        );
	}

	function enable_disable_prev_next_action(prevBtn, nextBtn, startPos, endPos, totalItems, itemsPerView){
		var disablePrev = false;
		var disableNext = false;

		if(startPos === 0){
			disablePrev = true;
		}
		if(startPos == 1){
			disablePrev = true;
		}
		if(endPos === totalItems){
			disableNext = true;
		}
		prevBtn.removeClass('disabled');
		nextBtn.removeClass('disabled');
		if(disablePrev){
	    	prevBtn.addClass('disabled');
		}
		if(disableNext){
	    	nextBtn.addClass('disabled');
		}
	}

	// Slider function on page load.
	var exist_slider = $('.thwma-thslider-box').length;
	var exist_cart_slider = $('#thwma-cart-shipping-tile-field .thwma-thslider-box').length;
	if(exist_cart_slider>0){
		if($(window).width() > 600){
			var mbox = $('.thwma-thslider-box');
			var m_maxWidth = Math.max.apply(null, (mbox).map(function (){
   	 			return $(this).width();
			}).get());
			if(m_maxWidth < 460 &&  (!(m_maxWidth <=0))){
				var m_items_per_view = 1;
    			(mbox).css("width", "260px");
			}else if(m_maxWidth < 690 &&  (!(m_maxWidth <=0))){
    			var m_items_per_view = 2;
    			(mbox).css("width", "480px");
    		}else{
    			var m_items_per_view = 3;
    		}
			setup_cart_shipping_address_slider('shipping');
		}
	}
	if(exist_slider>0){
		if($(window).width() > 600){
			var box = $('.thwma-thslider-box');
			var maxWidth = Math.max.apply(null, (box).map(function (){
   	 			return $(this).width();
			}).get());
			if(maxWidth < 460 &&  (!(maxWidth <=0))){
				var items_per_view = 1;
    			(box).css("width", "260px");
			}else if(maxWidth < 690 &&  (!(maxWidth <=0))){
    			var items_per_view = 2;
    			(box).css("width", "480px");
    		}else{
    			var items_per_view = 3;
    		}
			setup_shipping_address_slider('shipping');
			setup_billing_address_slider('billing');
		}
	}


	function setup_billing_address_slider(type){
    	var active = false;
    	var viewport = $('.thwma-thslider-viewport');
    	var list = $('.thwma-thslider-list.bill');
    	var prevBtn = $('.control-buttons .thwma-thslider-prev.billing');
    	var nextBtn = $('.control-buttons .thwma-thslider-next.billing');
    	var total_items = $('.thwma-thslider-item.'+type).length;
    	var item_width = 210+20;
    	var total_width = (total_items*item_width);
    	var initialPos = 1;
    	var finalPos = total_items;
    	var leftPos = '';
    	var startPos = initialPos;
    	var endPos = total_items > items_per_view ? items_per_view : finalPos;
    	slider_arrow_limits(items_per_view, prevBtn, nextBtn, 'billing');
		list.css('width', total_width);
		prevBtn.click(function () {
		    if (active === false){
		    	var rem_to_view = startPos-1;
		        var items_next_view = items_per_view;
		        if(rem_to_view < items_per_view){
		        	startPos = initialPos;
		        	endPos = items_per_view;
		        	items_next_view = rem_to_view;
		        }else{
		        	startPos -= items_per_view;
		        	endPos -= items_per_view;
		        }
		        if(rem_to_view > 0){
		        	if(startPos === initialPos){
			            leftPos = initialPos;
			        }else{
			            leftPos = '+='+(item_width * items_next_view);
			        }
			        move_slider(list, prevBtn, nextBtn, leftPos, startPos, endPos, total_items, items_per_view);
			    }
		    }
		});
		nextBtn.click(function() {
		 	if (active === false){
		     	var rem_to_view = total_items - endPos;
		     	var items_next_view = items_per_view;
		        if(rem_to_view < items_per_view){
		        	startPos += rem_to_view;
		        	endPos += rem_to_view;
		        	items_next_view = rem_to_view;
		        }else{
		        	startPos += items_per_view;
		        	endPos += items_per_view;
		        }
		        if(rem_to_view > 0){
			        leftPos = '-='+(item_width * items_next_view);
			        move_slider(list, prevBtn, nextBtn, leftPos, startPos, endPos, total_items, items_per_view);
			    }
		    }
		});
	}

	function setup_shipping_address_slider(type){
    	var active = false;
    	var ship_viewport = $('.thwma-thslider-viewport');
    	var ship_list = $('.thwma-thslider-list.ship');
    	var ship_prevBtn = $('.control-buttons .thwma-thslider-prev.shipping');
    	var ship_nextBtn = $('.control-buttons .thwma-thslider-next.shipping');
    	var ship_total_items = $('.thwma-thslider-item.'+type).length;
    	var item_width = 210+20;
    	var ship_total_width = ship_total_items*item_width;
    	var ship_initialPos = 1;
    	var ship_finalPos = ship_total_items;
    	var ship_leftPos = '';
    	var ship_startPos = ship_initialPos;
    	var ship_endPos = ship_total_items > items_per_view ? items_per_view : ship_finalPos;
    	slider_arrow_limits(items_per_view, ship_prevBtn, ship_nextBtn, 'shipping');
		ship_list.css('width', ship_total_width);
		ship_prevBtn.click(function () {
		    if (active === false){
		    	var rem_to_view = ship_startPos-1;
		        var items_next_view = items_per_view;

		        if(rem_to_view < items_per_view){
		        	ship_startPos = ship_initialPos;
		        	ship_endPos = items_per_view;
		        	items_next_view = rem_to_view;
		        }else{
		        	ship_startPos -= items_per_view;
		        	ship_endPos -= items_per_view;
		        }
		        if(rem_to_view > 0){
		        	if(ship_startPos === ship_initialPos){
			            ship_leftPos = ship_initialPos;
			        }else{
			            ship_leftPos = '+='+(item_width * items_next_view);
			        }
			        move_slider(ship_list,ship_prevBtn,ship_nextBtn, ship_leftPos,ship_startPos, ship_endPos,ship_total_items,items_per_view);
			    }
		    }
		});

		ship_nextBtn.click(function() {
		 	if (active === false){
		     var rem_to_view = ship_total_items - ship_endPos;
		     var items_next_view = items_per_view;
		        if(rem_to_view < items_per_view){
		        	ship_startPos += rem_to_view;
		        	ship_endPos += rem_to_view;
		        	items_next_view = rem_to_view;
		        }else{
		        	ship_startPos += items_per_view;
		        	ship_endPos += items_per_view;
		        }
		        if(rem_to_view > 0){
			        ship_leftPos = '-='+(item_width * items_next_view);
			        move_slider(ship_list, ship_prevBtn,ship_nextBtn,ship_leftPos, ship_startPos,ship_endPos, ship_total_items, items_per_view);
			    }
		    }
		});
	}

	function setup_cart_shipping_address_slider(type){
		var mitem_width = '';
		var mship_total_width = '';
		var mship_total_items = '';
    	var active = false;
    	var mship_viewport = $('.thwma-cart-shipping-tile-field .thwma-thslider-viewport');
    	var mship_list = $('.thwma-cart-shipping-tile-field .thwma-thslider-list-ms.ship');
    	var mship_prevBtn = $('.thwma-cart-shipping-tile-field .control-buttons .thwma-thslider-prev.multi-shipping');
    	var mship_nextBtn = $('.thwma-cart-shipping-tile-field .control-buttons .thwma-thslider-next.multi-shipping');
    	var mship_close = $('.thwma-cart-shipping-tile-field .thwma-cart-modal-close');
    	var mship_addnew = $('.thwma-cart-shipping-tile-field .btn-add-address');
    	//var mship_total_items = $('.thwma-cart-shipping-tile-field .thwma-thslider-item-ms.'+type).length;
    	var mship_total_items = $("input[name=ship_to_multi_address]").attr('data-address_count');
    	var mitem_width = 210+20;
    	var mship_total_width = mship_total_items*mitem_width;
    	var mship_initialPos = 1;
    	var mship_finalPos = mship_total_items;
    	var mship_leftPos = '';
    	var mship_startPos = mship_initialPos;
    	if(m_items_per_view == null) {
    		//new.
    		m_items_per_view = items_per_view();
    		//m_items_per_view = 3;
    	}


    	var mship_endPos = mship_total_items > m_items_per_view ? m_items_per_view : mship_finalPos;
    	cart_slider_arrow_limits(m_items_per_view, mship_prevBtn, mship_nextBtn, 'shipping');

		mship_list.css('width', mship_total_width);
		mship_prevBtn.click(function () {
			var mship_total_items = $("input[name=ship_to_multi_address]").attr('data-address_count');
		    if (active === false){
		    	var mrem_to_view = mship_startPos-1;
		        var mitems_next_view = m_items_per_view;

		        if(mrem_to_view < m_items_per_view){
		        	mship_startPos = mship_initialPos;
		        	mship_endPos = m_items_per_view;
		        	mitems_next_view = mrem_to_view;
		        }else{
		        	mship_startPos -= m_items_per_view;
		        	mship_endPos -= m_items_per_view;
		        }
		        if(mrem_to_view > 0){
		        	if(mship_startPos === mship_initialPos){
			            mship_leftPos = mship_initialPos;
			        }else{
			            mship_leftPos = '+='+(mitem_width * mitems_next_view);
			        }
			        move_slider(mship_list, mship_prevBtn, mship_nextBtn, mship_leftPos, mship_startPos, mship_endPos, mship_total_items, m_items_per_view);
			    }
		    }
		});

		mship_nextBtn.click(function() {
			var mship_total_items = $("input[name=ship_to_multi_address]").attr('data-address_count');
		 	if (active === false){
		     	var mrem_to_view = mship_total_items - mship_endPos;
		     	var mitems_next_view = m_items_per_view;
		        if(mrem_to_view < m_items_per_view){
		        	mship_startPos += mrem_to_view;
		        	mship_endPos += mrem_to_view;
		        	mitems_next_view = mrem_to_view;
		        } else {
		        	mship_startPos += m_items_per_view;
		        	mship_endPos += m_items_per_view;
		        }
		        if(mrem_to_view > 0){
			        mship_leftPos = '-='+(mitem_width * mitems_next_view);
			        move_slider(mship_list, mship_prevBtn, mship_nextBtn, mship_leftPos, mship_startPos, mship_endPos, mship_total_items, m_items_per_view);
			        return false;
			    }
		    }
		});

		mship_close.click(function() {
			mship_leftPos = mship_initialPos;
			move_slider(mship_list, mship_prevBtn, mship_nextBtn, mship_leftPos, mship_startPos, mship_endPos, mship_total_items, m_items_per_view);
		});
		mship_addnew.click(function() {
			mship_leftPos = mship_initialPos;
			move_slider(mship_list, mship_prevBtn, mship_nextBtn, mship_leftPos, mship_startPos, mship_endPos, mship_total_items, m_items_per_view);
		});
	}

	function items_per_view() {
		var exist_cart_slider = $('#thwma-cart-shipping-tile-field .thwma-thslider-box').length;
		if(exist_cart_slider>0){
			if($(window).width() > 600){
				var mbox = $('.thwma-thslider-box');
				var m_maxWidth = Math.max.apply(null, (mbox).map(function (){
	   	 			return $(this).width();
				}).get());
				if(m_maxWidth < 460 &&  (!(m_maxWidth <=0))){
					var m_items_per_view = 1;
	    			(mbox).css("width", "260px");
				}else if(m_maxWidth < 690 &&  (!(m_maxWidth <=0))){
	    			var m_items_per_view = 2;
	    			(mbox).css("width", "480px");
	    		}else{
	    			var m_items_per_view = 3;
	    		}
			}
		}
		return m_items_per_view;
	}

	function setup_section_address_slider(section,map_type){
		if(exist_slider>0){
		if($(window).width() > 600){
			var box = $('.thwma-thslider-box');
			var maxWidth = Math.max.apply(null, (box).map(function (){
   	 			return $(this).width();
			}).get());
			if(maxWidth < 460 &&  (!(maxWidth <=0))){
				var items_per_view = 1;
    			(box).css("width", "260px");
			}else if(maxWidth < 690 &&  (!(maxWidth <=0))){
    			var items_per_view = 2;
    			(box).css("width", "480px");
    		}else{
    			var items_per_view = 3;
    		}
	    	var active = false;
	    	var sectn_parent = $('#thslider-'+section);
	    	var sectn_viewport = $('.thwma-thslider-viewport');
	    	var sectn_list = sectn_parent.find('.thwma-thslider-list.'+section);
	    	var sectn_prevBtn = sectn_parent.find('.control-buttons .thwma-thslider-prev.'+section);
	    	var sectn_nextBtn = sectn_parent.find('.control-buttons .thwma-thslider-next.'+section);
	    	move_slider(sectn_list,sectn_prevBtn,sectn_nextBtn, '',1, 3,3,3);
	    	var sectn_total_items = $('.thwma-thslider-item_c.'+section).length;
	    	var item_width = 210+20;
	    	var sectn_total_width = sectn_total_items*item_width;
	    	//var items_per_view = 3;
	    	//ar left_margin = 0;
	    	var sectn_initialPos = 1;
	    	var sectn_finalPos = sectn_total_items;
	    	var sectn_leftPos = '';
	    	var sectn_startPos = sectn_initialPos;
	    	var sectn_endPos = sectn_total_items > items_per_view ? items_per_view : sectn_finalPos;
			sectn_list.css('width', sectn_total_width);
			//move_slider(list, prevBtn, nextBtn, leftPos, startPos, endPos, total_items, items_per_view);
			//move_slider(sectn_list,sectn_prevBtn,sectn_nextBtn, sectn_leftPos,sectn_startPos, sectn_endPos,sectn_total_items,items_per_view);

			if(sectn_total_items <=3){
				$('.control-buttons.control-buttons'+section).css('display','none');
				sectn_prevBtn.css('display','none');
				sectn_nextBtn.css('display','none');
			}
			sectn_prevBtn.unbind().click(function () {
			    if (active === false){
			    	var rem_to_view = sectn_startPos-1;
			        var items_next_view = items_per_view;
			        if(rem_to_view < items_per_view){
			        	sectn_startPos = sectn_initialPos;
			        	sectn_endPos = items_per_view;
			        	items_next_view = rem_to_view;
			        }else{
			        	sectn_startPos -= items_per_view;
			        	sectn_endPos -= items_per_view;
			        }
			        if(rem_to_view > 0){
			        	if(sectn_startPos === sectn_initialPos){
				            sectn_leftPos = sectn_initialPos;
				        }else{
				            sectn_leftPos = '+='+(item_width * items_next_view);
				        }
				        move_slider(sectn_list,sectn_prevBtn,sectn_nextBtn, sectn_leftPos,sectn_startPos, sectn_endPos,sectn_total_items,items_per_view);
				    }
			    }
			});

			sectn_nextBtn.unbind().click(function() {
			 	if (active === false){
			    	var  rem_to_view = sectn_total_items - sectn_endPos;
			     	var  items_next_view = items_per_view;
			        if(rem_to_view < items_per_view){
			        	sectn_startPos += rem_to_view;
			        	sectn_endPos += rem_to_view;
			        	items_next_view = rem_to_view;

			        }else{
			        	sectn_startPos += items_per_view;
			        	sectn_endPos += items_per_view;
			        }

			        if(rem_to_view > 0){
				       	sectn_leftPos = '-='+(item_width * items_next_view);
				    	move_slider(sectn_list,sectn_prevBtn,sectn_nextBtn,sectn_leftPos, sectn_startPos,sectn_endPos, sectn_total_items, items_per_view);
				    }
			    }

			});
		}}
	}
	/*******************************
    **** Address Slider - END ******
    ********************************/

	theme_base_style_change();
	function theme_base_style_change(){
		var slider_wndo = $('.thwma-thslider-box').width();
		var new_slider_wndo = parseInt(slider_wndo)-parseInt(10);
	 	$(".thwma_twentytwelve_acnt .thwma-thslider-box").width(new_slider_wndo);
		$(".thwma_twentyfourteen_acnt .thwma-thslider-box").width(new_slider_wndo);
		$(".thwma_twentyfifteen_acnt .thwma-thslider-box").width(new_slider_wndo);
		$(".thwma_twentyeleven_acnt .thwma-thslider-box").width(new_slider_wndo);
		$(".thwma_divi_acnt .thwma-thslider-box").width(new_slider_wndo);
	}
	function disable_address_mngmt(){
		$('.thwma_disable_adr_mngt').css("visibility", "hidden");
	}
	function check_country_is_changed(){
		$('select#shipping_country_field').on( 'change', function (){
			$("#shipping_state").select2({
				tags: true,
			    dropdownParent: $("#thwma-shipping-tile-field")
			});
		});
	}
	function initialize_select2(){
		$("select#shipping_country").select2({
			tags: true,
		});

		$("select#shipping_state").select2({
			tags: true,
		});
	}
	function cart_shipping_popup(e){
		e.preventDefault();
		$(".cart-shipping-addresses").empty();
		cspopup.dialog('open');
		active = false;
	}

	function cart_save_address(e) {
		$("#thwma-cart-shipping-form-section").append('<div class="ajaxBusy"> <i class="fa fa-spinner" aria-hidden="true"></i></div>');
   		e.preventDefault();
   		$('.ajaxBusy').show();
   		var cart_shipping = [];
   		var data_arr = [];
   		var hidden_ids =[]

   		$('.thwma-cart-shipping-form-section').find('.thwcfe-disabled-field').each(function(){ 
   			hidden_ids.push($(this).attr('id'));
		});

   		// For checkout page multi-shipping
   		cart_shipping = $('#cart_shipping_form_wrap :input').serialize();

	  	var data = {
			action: 'thwma_save_address',
			security: $( '#cart_ship_form_action' ).val(),
			cart_shipping: cart_shipping,
			cfe_hide_field : hidden_ids
		};

		$.ajax({
       		url: thwma_public_var.ajax_url,
       		data: data,
       		type: 'POST',
       		success: function (response) {
       			if(response.true_check == 'true'){
       				$("input[name=ship_to_multi_address]").attr('data-address_count', response.address_count);
       				var modal2 = document.getElementById("thwma-cart-shipping-form-section");

					$('.ajaxBusy').hide();
					$('#cart_shipping_form').find("input[type=text],select").val("");
       				$('#thwma-cart-shipping-tile-field').html(response.result_shipping);
       				$('.multi-shipping-wrapper').html(response.output_table);
       				//$('.thwma-cart-shipping-options.select ').append(response.address_dropdown);
       				cartpopup.dialog('close');
       				modal2.style.display = "none";
       			} else{
       				$('.thwma_hidden_error_mssgs').addClass('show_msgs');
       				$('.thwma_hidden_error_mssgs.show_msgs').html(response.true_check);
					$('.ajaxBusy').hide();
       			}
       			populate_default_adr();
				setup_cart_shipping_address_slider('shipping');
			}
       	});
	}

	// Delete addresses from cart page.
	function delete_address_cart_page(elm,type,key){
		if(type == 'shipping'){
			$("#thwma-cart-shipping-tile-field").append('<div class="ajaxBusy"> <i class="fa fa-spinner" aria-hidden="true"></i></div>');
		}
		var selected_address_id = key;
		var selected_type = type;
		var data = {
			action: 'delete_address_with_id_cart',
			security: thwma_public_var.delete_address_with_id_cart_nonce,
        	selected_address_id: selected_address_id,
        	selected_type : selected_type,
		};
		$('.ajaxBusy').show();
		$.ajax({
       		url: thwma_public_var.ajax_url,
       		data: data,
       		type: 'POST',
       		success: function (response) {
       			$("input[name=ship_to_multi_address]").attr('data-address_count', response.address_count);
				$('#thwma-cart-shipping-tile-field').html(response.result_shipping);
				$.each($('.thwma-cart-shipping-options.select option'), function(key, value) {
					if($(this).val() == response.address_key) {
						$(this).remove();
					}
				});
				if(response.address_count == 0){
					// $('.control-buttons-shipping .thwma-thslider-prev').css('display', 'none');
					$('.multi-shipping-table').css('display','none');
					$('.thwma_cart_multi_shipping_display').css('display','none');
				}

				$('.ajaxBusy').hide();
				var selected_address = $('#thwma_hidden_field_'+type).val();

				if(type == 'shipping'){
					setup_cart_shipping_address_slider(type);
					$('body').trigger('update_checkout');
				}
			}
       	});
	}

	// Set to default addresses from cart page.
	function set_default_address_cart_page(elm,type,key){
		if(type == 'shipping'){
			$("#thwma-cart-shipping-tile-field").append('<div class="ajaxBusy"> <i class="fa fa-spinner" aria-hidden="true"></i></div>');
		}
		var selected_address_id = key;
		var data = {
			action: 'set_default_address_cart',
			security: thwma_public_var.set_default_address_cart_nonce,
        	selected_address_id : selected_address_id,
        	selected_type : type,
		};
		$('.ajaxBusy').show();
		$.ajax({
       		url: thwma_public_var.ajax_url,
       		data: data,
       		type: 'POST',
       		success: function (response) {
				$('#thwma-cart-shipping-tile-field').html(response.result_shipping);
				$('.multi-shipping-wrapper').html(response.output_table);
				$('.ajaxBusy').hide();
				var selected_address = $('#thwma_hidden_field_'+type).val();

				if(type == 'shipping'){
					setup_cart_shipping_address_slider(type);
					$('body').trigger('update_checkout');
				}
				jQuery("[name='ship_default_adr']").val(response.default_address)
				populate_default_adr();
			}
       	});
	}

	function ship_to_multi_address(){
		var ship_to_multi_address_f = $("input[name=ship_to_multi_address]").val();
		if(ship_to_multi_address_f == 'no') {
			$("[name='ship_to_multi_address']").removeClass('active_multi_ship');
			$('.thwma_cart_shipping_button').css('display','none');
			$('.multi-shipping-table').css('display','none');
			$('.thwma_cart_multi_shipping_display').css('display','none');

			// Multi-shipping.
			$('#shipping_tiles').css('display','block');
			$('.woocommerce-shipping-fields__field-wrapper').css('display','block');
			$('p#thwma-shipping-alt_field').css('display','block');
		} else {
			$("[name='ship_to_multi_address']").addClass('active_multi_ship');
			$('.thwma_cart_shipping_button').css('display','block');
			$('.multi-shipping-table').css('display','block');
			$('.thwma_cart_multi_shipping_display').css('display','block');

			// Multi-shipping.
			$('#shipping_tiles').css('display','none');
			$('.woocommerce-shipping-fields__field-wrapper').css('display','none');
			$('p#thwma-shipping-alt_field').css('display','none');

		}
		var checkout_check = thwma_public_var.is_checkout_page;

		// check enable / disable shipp to different address checkbox.
		$('.woocommerce').on('click', 'input[name=ship_to_different_address]', function() {
			//if ($(this).is(":checked")) {
			if($(this).prop("checked") == false) {
				$("input[name=ship_to_multi_address]").val('no');
				//$("input[name=ship_to_multi_address]").val('yes');
				var value  = 'no';
				var data = {
					action: 'enable_ship_to_multi_address',
					security: thwma_public_var.enable_ship_to_multi_address_nonce,
					value: value,
				};
				$.ajax({
		       		url: thwma_public_var.ajax_url,
		       		data: data,
		       		type: 'POST',
		       		success: function (response) {
		       			$('body').trigger('update_checkout');
					}
		       	});
			} else if($(this).prop("checked") == true){
				//if($("[name='ship_to_multi_address']").hasClass( "active_multi_ship" )) {
				if ($(".active_multi_ship")[0]){
					$("input[name=ship_to_multi_address]").val('yes');
					var value  = 'yes';
					var data = {
						action: 'enable_ship_to_multi_address',
						security: thwma_public_var.enable_ship_to_multi_address_nonce,
						value: value,
					};
					$.ajax({
			       		url: thwma_public_var.ajax_url,
			       		data: data,
			       		type: 'POST',
			       		success: function (response) {
			       			$('body').trigger('update_checkout');
						}
			       	});
			    }
			}
		});
		var shipping_section = $("input[name=ship_to_different_address]").prop("checked");
		//var shipping_section = $("input[name=ship_to_different_address]").val();
		var ship_to_multi_address = $("input[name=ship_to_multi_address]").val();

		if(checkout_check == true) {
			var ship_to_multi_address = $('input[name="ship_to_multi_address"]').val();
			if(ship_to_multi_address != null){
				if(ship_to_multi_address.length){
					if(shipping_section == true) {
						if(ship_to_multi_address == 'yes'){
							$("[name='ship_to_multi_address']").addClass('active_multi_ship');
							$('.thwma_cart_shipping_button').css('display','block');
							$('.multi-shipping-table').css('display','table');
							$('.thwma_cart_multi_shipping_display').css('display','block');
							$('.link_enabled_class').css('display','block');
							$('.link_disabled_class').css('display','none');

							// Multi-shipping.
							$('#shipping_tiles').css('display','none');
							$('.woocommerce-shipping-fields__field-wrapper').css('display','none');
							$('p#thwma-shipping-alt_field').css('display','none');
							var value  = 'yes';
						} else {
							$("[name='ship_to_multi_address']").removeClass('active_multi_ship');
							$('#shipping_tiles').css('display','block');
							$('.woocommerce-shipping-fields__field-wrapper').css('display','block');
							$('p#thwma-shipping-alt_field').css('display','block');
						}
					} else {
						var value  = 'no';
					}
					var data = {
						action: 'enable_ship_to_multi_address',
						security: thwma_public_var.enable_ship_to_multi_address_nonce,
						value: value,
					};
					$.ajax({
			       		url: thwma_public_var.ajax_url,
			       		data: data,
			       		type: 'POST',
			       		success: function (response) {
			       			$('body').trigger('update_checkout');
						}
			       	});
		    	}
		    } else {
				$('#shipping_tiles').css('display','block');
				$('.woocommerce-shipping-fields__field-wrapper').css('display','block');
				$('p#thwma-shipping-alt_field').css('display','block');
		    }
	    }

		// enable / disable multi-shipping checkbox.
		$('.woocommerce').on('click', 'input[name=ship_to_multi_address]', function() {
			if ($(this).is(":checked")) {
				$("[name='ship_to_multi_address']").addClass('active_multi_ship');
				$('.thwma_cart_shipping_button').css('display','block');


				var address_count = $(this).attr('data-address_count');
				if(address_count == 0) {
					$('.multi-shipping-table').css('display','none');
					$('.thwma_cart_multi_shipping_display').css('display','none');
					$('.link_enabled_class').css('display','none');
				} else{
					$('.multi-shipping-table').css('display','table');
					$('.thwma_cart_multi_shipping_display').css('display','block');
					$('.link_enabled_class').css('display','block');
				}
				$('.link_disabled_class').css('display','none');
				$(this).val('yes');

				// Multi-shipping.
				$('#shipping_tiles').css('display','none');
				$('.woocommerce-shipping-fields__field-wrapper').css('display','none');
				$('p#thwma-shipping-alt_field').css('display','none');

				var qunatity = $(".ship_to_diff_adr").attr("data-cart_quantity");
				$(".ship_to_diff_adr").each(function(index) {
					var qunatity = $(this).attr("data-cart_quantity");
					if(qunatity > 1) {
						$(this).css('display','block');
					} else{
						$(this).css('display','none');
					}
				});

			//set_default_adr_to_shipping_fields();
			} else if($(this).prop("checked") == false){

				// Remove the multishipping address form content.
				$("[name='ship_to_multi_address']").removeClass('active_multi_ship');
				$("#thwma-cart-shipping-form-section").html('');
				$('.thwma_cart_shipping_button').css('display','none');
				$('.multi-shipping-table').css('display','none');
				$('.thwma_cart_multi_shipping_display').css('display','none');
				$(this).val('no');

				// multi-shipping.
				$('#shipping_tiles').css('display','block');
				$('.woocommerce-shipping-fields__field-wrapper').css('display','block');
				$('p#thwma-shipping-alt_field').css('display','block');

				// address adding form.
				var modal_data = document.getElementById("thwma-cart-shipping-form-section");
				modal_data.style.display = "none";
			}
			var value  = $(this).val();
			var data = {
				action: 'enable_ship_to_multi_address',
				security: thwma_public_var.enable_ship_to_multi_address_nonce,
				value: value,
			};
			$.ajax({
	       		url: thwma_public_var.ajax_url,
	       		data: data,
	       		type: 'POST',
	       		success: function (response) {
	       			$('body').trigger('update_checkout');
	       			if(response == false) {
						//$('.multi-shipping-table').css('display','none');
	       			}
				}
	       	});
		});
	}

	function shipping_method_change(){
		// Check if cart shipping is enabled
        if (thwma_public_var.cart_shipping_enabled === 'yes') {
			$('body').trigger('update_checkout');

			//do something special
			var data = {};
			var ship_mthd_arry = [];

			var method_id = '';
			var nearest_ul_id = '';
			var ship_cart_key = '';
			var item_name = '';
			var item_qty = '';
			var product_id = '';
			var shipping_adrs = '';
			var shipping_name = '';
			setTimeout(
			 	function() {
					$( 'select.shipping_method, input[name^="shipping_method"][type="radio"]:checked' ).each( function() {
						var method_id = $( this ).val();
						var nearest_ul_id = $( this ).closest('ul').attr('id');
						var ship_cart_key = $( this ).closest('ul').siblings('.ship-cart-key').val();
						var ship_cart_unique_key = $( this ).closest('ul').siblings('.ship-cart-unique-key').val();
						var item_name = $( this ).closest('ul').siblings('.ship-product-name').text();
						var item_qty = $( this ).closest('ul').siblings('.ship-product-qty').val();
						var product_id = $( this ).closest('ul').siblings('.ship-product-id').val();
						var shipping_adrs = $( this ).closest('ul').siblings('.ship-address-formated').val();

						var shipping_name = $( this ).closest('ul').siblings('.ship-address-name').val();
						item_name = item_name.replace(":","");
						var shipping_array = { method_id : method_id, parent_ul_id : nearest_ul_id, cart_key : ship_cart_key, cart_unique_key : ship_cart_unique_key, item_name : item_name, product_id : product_id, shipping_adrs : shipping_adrs, shipping_name : shipping_name, item_qty : item_qty } ;
						ship_mthd_arry.push(shipping_array);
					} );


					var data = {
						action: 'save_shipping_method_details',
						security: thwma_public_var.save_shipping_method_details_nonce,
						ship_method_arr: ship_mthd_arry,
					};
					$.ajax({
			       		url: thwma_public_var.ajax_url,
			       		data: data,
			       		type: 'POST',
			       		success: function (response) {

						}
			       	});

	       	}, 2000);

			var checkout_form =  $( 'form.checkout' );
			var ship_mthd_arrys = [];
			checkout_form.on('change', 'input[name^="shipping_method"]', function(e){
				var method_id = $( this ).val();
				var nearest_ul_id = $( this ).closest('ul').attr('id');
				var ship_cart_key = $( this ).closest('ul').siblings('.ship-cart-key').val();
				var ship_cart_unique_key = $( this ).closest('ul').siblings('.ship-cart-unique-key').val();
				var item_name = $( this ).closest('ul').siblings('.ship-product-name').text();
				var item_qty = $( this ).closest('ul').siblings('.ship-product-qty').val();
				var product_id = $( this ).closest('ul').siblings('.ship-product-id').val();
				var shipping_adrs = $( this ).closest('ul').siblings('.ship-address-formated').val();
				var shipping_name = $( this ).closest('ul').siblings('.ship-address-name').val();
				item_name = item_name.replace(":","");
				var map = Object.create(null);
				ship_mthd_arry.forEach(function(entry) {
					if (entry.cart_key == ship_cart_key) {
				    	entry.method_id = method_id;
					}
				});
				var data = {
					action: 'save_shipping_method_details',
					security: thwma_public_var.save_shipping_method_details_nonce,
					ship_method_arr: ship_mthd_arry,
				};
				$.ajax({
		       		url: thwma_public_var.ajax_url,
		       		data: data,
		       		type: 'POST',
		       		success: function (response) {
					}
		       	});
			});
		}
	}

	// Link click to add multiple item rows(multi-shipping).
	function create_multiproduct_product_multi_shipping(){
	    $(document).on('click', '.ship_to_diff_adr', function (e) {
	    	e.preventDefault();
	    	$(".multi-shipping-table-overlay").append('<div class="ajaxBusy"> <i class="fa fa-spinner" aria-hidden="true"></i></div>');
	    	$(".multi-shipping-table-overlay").css('display', 'block');
	    	$('.ajaxBusy').show();
	    	var $this = $(this),
          	$parent_tr = $this.closest('tr');
          	$this.addClass('multi-ship-link-disabled');
	        var cart_item = $(this).data("cart_item");
			var cart_item_key = $(this).data("cart_item_key");
	     	var $thisbutton = $(this);
            var id = $thisbutton.val();
            var product_qty = $(this).data("cart_quantity");
            var product_id = $(this).data("product_id");
            var variation_id = $(this).data("variation_id");
            var qty_field = $this.closest("tbody").find("tr .pdct-qty-"+cart_item_key);
            var qty_field_val = qty_field.val();
            var new_qty_field_val = product_qty;
	        if(qty_field_val > 1) {
	        	if(qty_field_val == 2) {
		        	$this.css('display','none');
		        	$this.removeClass('link_enabled_class');
		        }

		        // Set main item qty field value.
		        var new_qty_field_val = qty_field_val-1;
		        $(qty_field).val(new_qty_field_val);
				var multi_ship_item = $this.closest("tr").find(".multi-ship-item");
	           	var parent_id = multi_ship_item.data("multi_ship_parent_id");
	           	var updated_qty = multi_ship_item.data("updated_qty");
	           	var sub_row_stage = multi_ship_item.data("sub_row_stage");
	           	if(parent_id == 0) {
	           		var parent_id = multi_ship_item.data("multi_ship_id");
	           	}
		        var data = {
		            action: 'additional_address_management',
		            security: thwma_public_var.additional_address_management_nonce,
		            product_id: product_id,
		            product_sku: '',
		            quantity: product_qty,
		            variation_id: variation_id,
		            cart_item: cart_item,
		            cart_item_key: cart_item_key,
		            parent_item_id: parent_id,
		            sub_row_stage: sub_row_stage,
		            updated_qty: new_qty_field_val
		        };
				$.ajax({
		       		url: thwma_public_var.ajax_url,
		       		data: data,
		       		type: 'POST',
		       		success: function (response) {
		       			$(response).insertAfter($parent_tr);
		       			$this.attr('data-cart_quantity', updated_qty);
		       			$('.ajaxBusy').hide();
		       			$(".multi-shipping-table-overlay").css('display', 'none');
		       			shipping_method_change();
		       			$('body').trigger('update_checkout');
		       		}
		       	});
	        }

	    });
	}

	// Remove multiple item row(multi-shipping).
	function remove_multi_shipping_tr(e,current){
		current.closest("tr").remove();
		$(".multi-shipping-table-overlay").append('<div class="ajaxBusy"> <i class="fa fa-spinner" aria-hidden="true"></i></div>');
    	$(".multi-shipping-table-overlay").css('display', 'block');
    	$('.ajaxBusy').show();
		var ship_to_diff_adr = $(current).closest("tr").find(".ship_to_diff_adr");
		var product_id = ship_to_diff_adr.data("product_id");
		var cart_item_key = ship_to_diff_adr.data("cart_item_key");
		var multi_ship_item = $(current).closest("tr").find(".multi-ship-item");
		var multi_ship_id = multi_ship_item.data("multi_ship_id");
		var data = {
            action: 'remove_multi_shipping_row',
            security: thwma_public_var.remove_multi_shipping_row_nonce,
            product_id: product_id,
            cart_item_key: cart_item_key,
            multi_ship_id: multi_ship_id
        };
		$.ajax({
       		url: thwma_public_var.ajax_url,
       		data: data,
       		type: 'POST',
       		success: function (response) {
	   			$('.ajaxBusy').hide();
	   			$(".multi-shipping-table-overlay").css('display', 'none');
	   			$('body').trigger('update_checkout');
	   			shipping_method_change();
       		}
       	});
	}

	function update_product_qty_from_multi_ship() {
		$(document).on('change', '.multi-ship-pdct-qty', function (e) {
			$(".multi-shipping-table-overlay").append('<div class="ajaxBusy"> <i class="fa fa-spinner" aria-hidden="true"></i></div>');
	    	$(".multi-shipping-table-overlay").css('display', 'block');
	    	if($(this).val() <1){
	    		$(this).closest("tr").hide();
	    	}
	    	$('.ajaxBusy').show();
			var value = $(this).val();
			var $this = $(this);
			var cart_key = $this.data("cart_key");
			var data = {
				action: 'update_multi_shipping_qty_field',
				security: thwma_public_var.update_multi_shipping_qty_field_nonce,
				value: value,
				cart_key: cart_key,
			};
			$.ajax({
	       		url: thwma_public_var.ajax_url,
	       		data: data,
	       		type: 'POST',
	       		success: function (response) {
	       			if($this.hasClass( "main-pdct-qty" )) {
						var value = $this.val();
						var multi_ship_link = $this.closest("tr").find(".ship_to_diff_adr");
						if(value > 1){
							multi_ship_link.css('display', 'block');
							multi_ship_link.replaceWith(response);
						} else {
							multi_ship_link.css('display', 'none');
						}
					}
					$('body').trigger('update_checkout');
		   			$('.ajaxBusy').hide();
		   			$(".multi-shipping-table-overlay").css('display', 'none');
		   			shipping_method_change();
				}
	       	});
		});
	}

	// Set a default address to the shipping field in the case of multi-shipping.
	function set_default_adr_to_shipping_fields() {
		var first_set_adrs = '';
		var first_tr = $('table.multi-shipping-table').find('tr:nth-child(2)');
		var first_set_adrs = first_tr.find("select.thwma-cart-shipping-options").val();
		if((first_set_adrs != null) && (first_set_adrs !='')) {
			var elm = '';
			var type = 'shipping';
			populate_selected_address(elm,type,first_set_adrs);
		} else {
			populate_default_adr();
	    }
	}

	function populate_default_adr(){
		var address_type = 'shipping';
		var ship_default_adr = jQuery("[name='ship_default_adr']").val();
		if(ship_default_adr != null) {
			var response = '';
			if(ship_default_adr != undefined) {
				var response = jQuery.parseJSON(ship_default_adr);
			}
			var sell_countries = thwma_public_var.sell_countries;
	        var sell_countries_size = Object.keys(sell_countries).length;
	        var address_fields = [];
	        if(address_type == 'shipping'){
	            address_fields = thwma_public_var.address_fields_shipping;
	        }
	        $.each( address_fields, function(f_key, f_type) {
	        	var input_elm = '';
	            if(f_type == 'radio' || f_type == 'checkboxgroup'){
	                input_elm = $("input[name="+f_key+"]");
	            }else{
	                input_elm = $('#'+f_key);
	            }
	            var skip = (sell_countries_size == 1 && f_key == address_type+'_country') ? true : false;
	            if (sell_countries_size == 1){
	            	if(f_key == 'billing_country'){
	            		skip = true;
	            	} else {
	            		skip = false;
	            	}
	            }
	            if (!skip && input_elm.length) {
	                var _type = input_elm.getType();
	                var _value = response[f_key];

	                if(f_type === 'file'){
	                    _type = 'file';
	                }
	                thwma_public_base.set_field_value_by_elm(input_elm, _type, _value, f_key);
	            }
	        });
	    }
	}

	// Select multi-shipping address for drop down and save it on cart.
	function select_cart_mutlti_shipping(){
		$('.woocommerce').on('change', 'select.thwma-cart-shipping-options', function() {
			$(".multi-shipping-table-overlay").append('<div class="ajaxBusy"> <i class="fa fa-spinner" aria-hidden="true"></i></div>');
	    	$(".multi-shipping-table-overlay").css('display', 'block');
	    	$('.ajaxBusy').show();
			var value = $(this).val();
			var $this = $(this);
			var product_id = $(this).attr("data-product_id");
			var cart_key = $(this).attr("data-cart_key");
			var check_multi_shipping = $(this).attr("data-exist_multi_adr");
			var multi_ship_item = $this.closest("tr").find(".multi-ship-item");
			var multi_ship_id = multi_ship_item.data("multi_ship_id");
			var type = 'shipping';
			var elm = '';
			var exist_multi_ship = $('.multi-shipping-adr-data').val();
			var multi_ship_data = [];
			if(multi_ship_data != ''){
				if(exist_multi_ship != undefined) {
		    		var multi_ship_data = JSON.parse(exist_multi_ship);
		    	}
				jQuery.each(multi_ship_data, function( index, val ) {
					multi_ship_data[cart_key] = {'product_id': product_id, 'address_name': value};
				});
			} else {
			}
			var new_multi_ship_dta = '';
			if(multi_ship_data != undefined) {
				var new_multi_ship_dta = JSON.stringify(multi_ship_data);
			}
			$('.multi-shipping-adr-data').val(new_multi_ship_dta);

			// Populate the selected address on the hidden checkout form.
			var first_tr = $this.closest('table').find('tr:nth-child(2)');
			var first_set_adrs = first_tr.find("select.thwma-cart-shipping-options").val();

			// For guest users.
			var current_user_id = thwma_public_var.current_user_id;

			if(current_user_id != 0) {
				if((first_set_adrs != null) && (first_set_adrs !='')) {

					//ISSUE.
					populate_selected_address(elm, type, first_set_adrs);
				} else {
					set_default_adr_to_shipping_fields();
				}
			} else {
				// For guest users.
				if((first_set_adrs != null) && (first_set_adrs !='')) {
					populate_selected_address(elm, type, first_set_adrs);
				}
			}

			// For guest users.
			// var current_user_id = thwma_public_var.current_user_id;
			// if(current_user_id == 0) {
			// 	if((first_set_adrs != null) && (first_set_adrs !='')) {
			// 		populate_selected_address(elm,type,first_set_adrs);
			// 	}
			// }
			var data = {
				action: 'save_multi_selected_shipping',
				security: thwma_public_var.save_multi_selected_shipping_nonce,
				value: value,
				product_id: product_id,
				cart_key: cart_key,
				multi_ship_id : multi_ship_id,
			};
			$.ajax({
	       		url: thwma_public_var.ajax_url,
	       		data: data,
	       		type: 'POST',
	       		success: function (response) {
		   			shipping_method_change();
		   			//$('.woocommerce-shipping-totals td .ship-address-data').html(response.first_address);
					$('.ajaxBusy').hide();
					$(".multi-shipping-table-overlay").css('display', 'none');
				}
	       	});
		});
	}

	// Guest Users( Multi-shipping ).
	function guest_users_add_new_shipping_address(e,elm, type){
		e.preventDefault();
		cspopup.dialog('close');
		initialize_select2();
		active = false;
		$("#thwma-cart-shipping-form-section").append('<div class="ajaxBusy"> <i class="fa fa-spinner" aria-hidden="true"></i></div>');
		var data = {
			action: 'guest_users_add_new_shipping_address',
			security: thwma_public_var.guest_users_add_new_shipping_address_nonce,
        	selected_type : type,
		};
		$('.ajaxBusy').show();
		$.ajax({
       		url: thwma_public_var.ajax_url,
       		data: data,
       		type: 'POST',
       		success: function (response) {
       			$("#thwma-cart-shipping-form-section").append('<div class="ajaxBusy"> <i class="fa fa-spinner" aria-hidden="true"></i></div>');
				$('#thwma-cart-shipping-form-section').html(response);

				if(thwma_public_var.enable_autofill == 'yes'){
					thwma_auto_suggest.init_cart_shipping_autocomplete();

					if(document.getElementById("shipping_address_1") != null){
						var adrs_shipping_field = document.getElementById("shipping_address_1");
						google.maps.event.addDomListener(adrs_shipping_field, 'keydown', function(e){
							if (e.keyCode == 13) {
			           			e.preventDefault();
			        		}
						});
					}
				}
				initialize_select2();
				$('.ajaxBusy').hide();
			}
       	});
	}

	function cart_save_guest_address(e){
		$("#thwma-cart-shipping-form-section").append('<div class="ajaxBusy"> <i class="fa fa-spinner" aria-hidden="true"></i></div>');
   		e.preventDefault();
   		$('.ajaxBusy').show();
   		var cart_shipping = [];
   		var data_arr = [];
   		var hidden_ids =[]

   		// check cfe hide field is in the form.
   		$('.thwma-cart-shipping-form-section').find('.thwcfe-disabled-field').each(function(){ 
   			hidden_ids.push($(this).attr('id'));
		});
       	// For checkout page multi-shipping
       	cart_shipping = $('#cart_shipping_form_wrap :input').serialize();

	  	var data = {
			action: 'thwma_save_guest_address',
			security: thwma_public_var.thwma_save_guest_address_nonce,
			cart_shipping: cart_shipping,
			cfe_hide_field : hidden_ids
		};
		$.ajax({
       		url: thwma_public_var.ajax_url,
       		data: data,
       		type: 'POST',
       		success: function (response) {
       			if(response.true_check == 'true'){
       				$("input[name=ship_to_multi_address]").attr('data-address_count', response.address_count);
       				var modal2 = document.getElementById("thwma-cart-shipping-form-section");
					$('.ajaxBusy').hide();
					$('#cart_shipping_form').find("input[type=text],select").val("");
       				if(response.adr_count != '') {
       					$('.thwma-thslider-list-ms.ship').append(response.new_tile);
       					$('.thwma-cart-shipping-options.select ').append(response.address_dropdown);
       					$('.thwma-thslider.thwma-thslider-guest-user').append(response.slider_arrows);
       					if((response.default_address != null) && (response.default_address !='')) {
							var elm = '';
							var type = 'shipping';
							var first_set_adrs = response.default_address;
							populate_selected_address(elm,type,first_set_adrs);
						}
       				} else {
       					$('.thwma-thslider-box').remove();
       					$('.thwma-thslider.thwma-thslider-guest-user').prepend(response.new_tile);
       					$('.multi-shipping-wrapper').html(response.shipping_table);
       					$('select.thwma-cart-shipping-options').find('[value="0"]').remove();
       					$('.thwma-cart-shipping-options.select ').append(response.address_dropdown);
       					$('.th-no-address-msg').remove();
       					if((response.default_address != null) && (response.default_address !='')) {
							var elm = '';
							var type = 'shipping';
							var first_set_adrs = response.default_address;
							populate_selected_address(elm,type,first_set_adrs);
						} else {
						}
       				}
					setup_cart_shipping_address_slider('shipping');
					//cartpopup.dialog('close');
	       			modal2.style.display = "none";
       			} else {
       				setup_cart_shipping_address_slider('shipping');
       				$("input[name=ship_to_multi_address]").attr('data-address_count', response.address_count);
       				$('.thwma_hidden_error_mssgs').addClass('show_msgs');
       				$('.thwma_hidden_error_mssgs.show_msgs').html(response.true_check);
					$('.ajaxBusy').hide();
       			}
       			if(response.address_count == response.address_limit) {
       				$('.thwma-thslider-guest-user .add-address.thwma-add-adr ').css('display', 'none');
       			} else {
       				$('.thwma-thslider-guest-user .add-address.thwma-add-adr ').css('display', 'block');
       			}
			}
       	});
	}

	// Delete addresses from cart page.
	function guest_users_delete_address_cart_page(elm,type,key){
		if(type == 'shipping'){
			$("#thwma-cart-shipping-tile-field").append('<div class="ajaxBusy"> <i class="fa fa-spinner" aria-hidden="true"></i></div>');
		}
		var selected_address_id = key;
		var selected_type = type;
		var data = {
			action: 'delete_address_with_id_cart_guest',
			security: thwma_public_var.delete_address_with_id_cart_guest_nonce,
        	selected_address_id: selected_address_id,
        	selected_type : selected_type,
		};
		$('.ajaxBusy').show();
		$.ajax({
       		url: thwma_public_var.ajax_url,
       		data: data,
       		type: 'POST',
       		success: function (response) {
       			$("input[name=ship_to_multi_address]").attr('data-address_count', response.address_count);
       			$(elm).closest('li.thwma-thslider-item-ms.shipping').html('');
       			$.each($('.thwma-cart-shipping-options.select option'), function(key, value) {
					if($(this).val() == response.address_key) {
						$('option[value='+response.address_key+']').remove();
						//$(this).remove();
					}
				});
				$('.ajaxBusy').hide();
				var selected_address = $('#thwma_hidden_field_'+type).val();

				if(response.address_count > 3){
					if(type == 'shipping'){
						//setup_cart_shipping_address_slider(type);
						$('body').trigger('update_checkout');
					}
				} else {
					$('.control-buttons-shipping .thwma-thslider-next').css('display', 'none');
					var position_left = $('ul.thwma-thslider-list-ms').position().left;
					if(position_left == 1 ){
						$('.control-buttons-shipping .thwma-thslider-prev').css('display', 'none');
					}
				}
				if(response.address_count == 0){
					$('.control-buttons-shipping .thwma-thslider-prev').css('display', 'none');
					$('.multi-shipping-table').css('display','none');
					$('.thwma_cart_multi_shipping_display').css('display','none');
				}
				var add_new_button = response.add_new_button;

				if(add_new_button != null){
					if($('.add-address.thwma-add-adr').length == null){
						$('.thwma-thslider.thwma-thslider-guest-user').append(add_new_button);
					} else {
						$('.thwma-thslider-guest-user .add-address.thwma-add-adr ').css('display', 'block');
					}
				}
			}
       	});
	}

	// Modal Popup.
	function cart_shipping_modal(e){
		e.preventDefault();
		$('.thwma_hidden_error_mssgs').css("display", "none");
	  	var modal = document.getElementById("thwma-cart-shipping-tile-field");
	  	var modal2 = document.getElementById("thwma-cart-shipping-form-section");
	  	modal2.style.display = "none";
	  	modal.style.display = "block";
	}
	function add_new_shipping_address_model(e,elm,type){
		e.preventDefault();
		var checkout_form = $('form.woocommerce-cart-form');
		var modal = document.getElementById("thwma-cart-shipping-tile-field");
		var modal2 = document.getElementById("thwma-cart-shipping-form-section");
		modal.style.display = "none";
		modal2.style.display = "block";
		$(".select2-container.select2-container--default").css("width", "100%");
		$("#thwma-cart-shipping-form-section").append('<div class="ajaxBusy"> <i class="fa fa-spinner" aria-hidden="true"></i></div>');
		var data = {
			action: 'add_new_shipping_address',
			security: thwma_public_var.add_new_shipping_address_nonce,
        	selected_type : type,
		};
		$('.ajaxBusy').show();
		$.ajax({
       		url: thwma_public_var.ajax_url,
       		data: data,
       		type: 'POST',
       		success: function (response) {
       		$("#thwma-cart-shipping-form-section").append('<div class="ajaxBusy"> <i class="fa fa-spinner" aria-hidden="true"></i></div>');
				$('#thwma-cart-shipping-form-section').html(response);
				if(thwma_public_var.thwcfe_is_active == true){
					thwcfe_public_conditions.validate_all_conditions(null);
					thwcfe_public_conditions.prepare_shipping_conitional_fields(null, false);
				}
				if(thwma_public_var.enable_autofill == 'yes'){
					thwma_auto_suggest.init_cart_shipping_autocomplete();
				}
				initialize_select2();
				$('.ajaxBusy').hide();
			}
       	});
	}

	// Guest Users( Multi-shipping ).
	function guest_users_add_new_shipping_address_modal(e,elm, type){
		e.preventDefault();
	  	var modal = document.getElementById("thwma-cart-shipping-tile-field");
	  	var modal2 = document.getElementById("thwma-cart-shipping-form-section");
		modal.style.display = "none";
		modal2.style.display = "block";
		$("#thwma-cart-shipping-form-section").append('<div class="ajaxBusy"> <i class="fa fa-spinner" aria-hidden="true"></i></div>');
		var data = {
			action: 'guest_users_add_new_shipping_address',
			security: thwma_public_var.guest_users_add_new_shipping_address_nonce,
        	selected_type : type,
		};
		$('.ajaxBusy').show();
		$.ajax({
       		url: thwma_public_var.ajax_url,
       		data: data,
       		type: 'POST',
       		success: function (response) {
       			$("#thwma-cart-shipping-form-section").append('<div class="ajaxBusy"> <i class="fa fa-spinner" aria-hidden="true"></i></div>');
				$('#thwma-cart-shipping-form-section').html(response);

				if(thwma_public_var.thwcfe_is_active == true){
					thwcfe_public_conditions.validate_all_conditions(null);
					thwcfe_public_conditions.prepare_shipping_conitional_fields(null, false);
				}

				if(thwma_public_var.enable_autofill == 'yes'){
					thwma_auto_suggest.init_cart_shipping_autocomplete();
				}
				initialize_select2();
				$('.ajaxBusy').hide();
			}
       	});
	}
	function close_cart_adr_list_modal() {
		var modal_data = document.getElementById("thwma-cart-shipping-tile-field");
		var close = document.getElementsByClassName("thwma-cart-modal-close")[0];
		modal_data.style.display = "none";

	}
	function close_cart_add_adr_modal() {
		var modal_data = document.getElementById("thwma-cart-shipping-form-section");
		var close = document.getElementsByClassName("thwma-cart-modal-close2")[0];
		$('.thwma_hidden_error_mssgs').css("display", "none");
		modal_data.style.display = "none";
	}
	function save_multi_ship_adr_btn(e) {
		$('body').trigger('update_checkout');
	}

	function hide_shipping_custom_fields() {
		$('table.woocommerce-table--custom-fields.custom-fields th').each(function() {
		    if($(this).html() == 'Shipping Fields') {
				$(this).closest('table.custom-fields tr').nextAll('tr').remove();
				$(this).closest('table.custom-fields tr').remove();
			}
		});
	}
	// function default_address_set_first() {
	// 	$('.thwma-thslider-item.billing.first').prependTo('.thwma-thslider-list.bill');
	// 	$('.thwma-thslider-item.shipping.first').prependTo('.thwma-thslider-list.ship');
	// }
	return{
    	show_billing_popup:show_billing_popup,
    	show_shipping_popup:show_shipping_popup,
    	populate_selected_address:populate_selected_address,
    	add_new_address : add_new_address,
    	delete_address : delete_address,
    	delete_address_cart_page : delete_address_cart_page,
    	set_default_address : set_default_address,
    	set_default_address_cart_page : set_default_address_cart_page,
    	show_custom_popup : show_custom_popup,
    	populate_selected_section_address : populate_selected_section_address,
    	theme_base_style_change : theme_base_style_change,
    	slider_arrow_limits:slider_arrow_limits,
    	cart_shipping_popup:cart_shipping_popup,
    	cart_shipping_modal : cart_shipping_modal,
    	add_new_shipping_address_model : add_new_shipping_address_model,
    	cart_save_address : cart_save_address,
    	guest_users_add_new_shipping_address : guest_users_add_new_shipping_address,
    	cart_save_guest_address : cart_save_guest_address,
		select_cart_mutlti_shipping : select_cart_mutlti_shipping,
		ship_to_multi_address : ship_to_multi_address,
		guest_users_add_new_shipping_address_modal : guest_users_add_new_shipping_address_modal,
		close_cart_adr_list_modal : close_cart_adr_list_modal,
		close_cart_add_adr_modal : close_cart_add_adr_modal,
		guest_users_delete_address_cart_page : guest_users_delete_address_cart_page,
		remove_multi_shipping_tr : remove_multi_shipping_tr,
		save_multi_ship_adr_btn : save_multi_ship_adr_btn,
		create_multiproduct_product_multi_shipping : create_multiproduct_product_multi_shipping,
		update_product_qty_from_multi_ship : update_product_qty_from_multi_ship,
		shipping_method_change : shipping_method_change,
		hide_shipping_custom_fields : hide_shipping_custom_fields,
		set_default_adr_to_shipping_fields : set_default_adr_to_shipping_fields,
		//default_address_set_first : default_address_set_first,
    };
}(window.jQuery, window, document));


function  thwma_show_billing_popup(e) {
	thwma_address.show_billing_popup(e);
}

function  thwma_show_shipping_popup(e) {
	thwma_address.show_shipping_popup(e);
}

function thwma_populate_selected_address(e,elm,type,key) {
	e.preventDefault();
 	thwma_address.populate_selected_address(elm,type,key);
}

function thwma_add_new_address(e,elm,type) {
	e.preventDefault();
	thwma_address.add_new_address(elm,type);
}

function thwma_delete_selected_address(elm,type,key) {
	thwma_address.delete_address(elm,type,key);
}

function thwma_delete_selected_address_cart_page(elm,type,key) {
	thwma_address.delete_address_cart_page(elm,type,key);
}
function thwma_guest_users_delete_selected_address_cart_page(elm,type,key) {
	thwma_address.guest_users_delete_address_cart_page(elm,type,key);
}

function thwma_set_default_address(elm,type,key) {
	thwma_address.set_default_address(elm,type,key);
}

function thwma_set_default_address_cart_page(elm,type,key) {
	thwma_address.set_default_address_cart_page(elm,type,key);
}

function thwma_show_custom_popup(e,section,map_type) {

	thwma_address.show_custom_popup(e,section,map_type);
}

function thwma_populate_selected_section_address(e,elm,key,section,map_type) {
	e.preventDefault();
	thwma_address.populate_selected_section_address(e,elm,key,section,map_type);
}
function thwma_add_new_shipping_address(e,elm,type) {
	e.preventDefault();
	thwma_address.add_new_shipping_address_model(e,elm,type);
}
function thwma_cart_shipping_popup(e) {
	thwma_address.cart_shipping_modal(e);
}
function thwma_cart_save_address(event) {
	event.preventDefault();
	thwma_address.cart_save_address(event);
}
function thwma_guest_users_add_new_shipping_address(e,elm,type) {
	e.preventDefault();
	thwma_address.guest_users_add_new_shipping_address_modal(e,elm,type);
}
function thwma_cart_save_guest_address(event) {
	event.preventDefault();
	thwma_address.cart_save_guest_address(event);
}
function thwma_close_cart_adr_list_modal(e) {
	thwma_address.close_cart_adr_list_modal(e);
}
function thwma_close_cart_add_adr_modal(e) {
	thwma_address.close_cart_add_adr_modal(e);
}
function thwma_remove_multi_shipping_tr(e,$this) {
	thwma_address.remove_multi_shipping_tr(e,$this);
}
function thwma_save_multi_ship_adr_btn(e,$this) {
	thwma_address.save_multi_ship_adr_btn(e);
}
