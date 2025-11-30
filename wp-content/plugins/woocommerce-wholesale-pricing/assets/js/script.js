jQuery( document ).ready(
	function ($) {
		function checkFieldType() {
			var checkField = jQuery(".wwp-check-field-type").find(
				'select, input[type="text"]'
			);
			var type = "";

			if (checkField.is("select")) {
				type = "select";
			} else if (checkField.is("input[type='text']")) {
				type = "input";
			} else {
				type = "";
			}

			jQuery('input[name="wwp_state_type"]').remove();

			jQuery("<input>").attr({ type: "hidden", name: "wwp_state_type", value: type }).appendTo(".wwp-check-field-type");
		}

		checkFieldType();

		jQuery(document).on("change", ".wwp-billing-field select", function () {
		checkFieldType();
		});

		$(".show-announcement-popup").on("click", function (e) {
			e.preventDefault();
			var announcementIndex = $(this).data("index");
			var nonce = $(this).data("nonce");

			$.ajax({
				url: wwpscript.ajaxurl,
				type: "POST",
				data: {
					action: "update_announcement_status",
					announcement_index: announcementIndex,
					nonce : nonce,
				},
				success: function ( updateResponse ) {
					if ( updateResponse.success ) {
					
						$.ajax({
							url: wwpscript.ajaxurl,
							type: "POST",
							data: {
								action: "wwp_get_announcement_content",
								announcement_index: announcementIndex,
								nonce : nonce,
							},
							success: function (contentResponse) {
								if (contentResponse.success) {
									$("#popup-subject").text(contentResponse.data.subject);
									$("#popup-content").text(contentResponse.data.content);

									document.getElementById("popup-overlay").style.display = "flex";
								} else {
									alert( "There was an error fetching the announcement content." );
								}
						},
						error: function (xhr, status, error) {
							console.log(error);
							alert( "An AJAX error occurred while fetching content." );
						},
						});
					} else {
						alert("There was an error updating the announcement status.");
					}
				},
				error: function (xhr, status, error) {
					console.log(error);
					alert("An AJAX error occurred while updating status.");
				},
			});
		});

    	// Handle closing of the popup
		$("#close-popup").on("click", function () {
			document.getElementById("popup-overlay").style.display = "none";
			location.reload();
		});

    toggleShippingFieldsValidation();
    jQuery(".variations_form").on(
      "found_variation",
      function (event, variation) {
        // console.log( variation );
      }
    );
    jQuery("#wwp_wholesaler_copy_billing_address").change(function () {
      if (!this.checked) {
        jQuery("#wholesaler_shipping_address").fadeIn("slow");
      } else {
        jQuery("#wholesaler_shipping_address").fadeOut("slow");
      }
    });

	// added ajax to load variation of selected in 2.5
    jQuery(".single_variation_wrap").on( 
		"show_variation", 
		function (event, variation) {
			if ( jQuery("#wwp_tier_load").length ) {
				set_step_quantity(event, variation);
				jQuery('.single_add_to_cart_button').attr('disabled', 'disabled');
				var nonce = jQuery("#wwp_tier_load").val();
				jQuery.ajax({
					url: wwpscript.ajaxurl,
					type: "POST",
					dataType: "json",
					data: {
						action: 'wwp_show_discount_list_product_variation',
						variation_id: variation.variation_id,
						wwp_tier_nonce: nonce
					},
					beforeSend: function() {
						jQuery('#wholesale_tire_price tbody').html('');
					},
					success: function(response) {
						if (response && response.html !== '') {
						
							jQuery('#wholesale_tire_price tbody').html(response.html);
						}
						jQuery('.single_add_to_cart_button').removeAttr('disabled');
					},
					error: function(xhr, status, error) {
						console.error('AJAX Error:', status, error);
					}
				});
			}
		}
    );

	// reset when click on clear button 2.5
	jQuery(".reset_variations").on('click', function () {
        jQuery('#wholesale_tire_price').hide();
    });

	// Fires whenever variation selects are changed
    jQuery(".variations_form").on(
      "woocommerce_variation_select_change",
      function () {
		setTimeout(function(){
			if ( 'hidden' === jQuery(".reset_variations").css('visibility') ) {
				jQuery('#wholesale_tire_price').hide();
			}
		}, 200);		
	});

    jQuery("input.qty").on("change", function () {
      if (wwpscript.product_type == "variable") {
        tire_ajax_call(jQuery(".variation_id").val());
      } else {
        tire_ajax_call(wwpscript.product_id);
      }
    });

    jQuery(".wwp_file_add_more").on("click", function () {
      var html =
        '<p style="margin:0 !important;" class=""><label for="wwp_wholesaler_file_upload">File Upload</label><input style="width: 70%; !important" type="file" name="wwp_wholesaler_file_upload[]" id="wwp_wholesaler_file_upload" ><button type="button" class="wwp_file_delete" style="float: right;"><span class="dashicons dashicons-minus"></span>Delete</button></p>';
      jQuery("#wwp_wholesaler_file_upload").parent().last().append(html);
    });

    jQuery("body").on("click", ".wwp_file_delete", function () {
      jQuery(this).parent().remove();
    });

    if ( wwpscript.min_subtotal_enabled == 1 && wwpscript.check_subtotal == false ) {
      jQuery('form.woocommerce-cart-form [name="update_cart"]').on(
        "click",
        function (e) {
          setTimeout(() => location.reload(), 1000);
        }
      );
    }
  }
);

function tire_ajax_call( variation_id ) {
	
	if ( variation_id != 0 ) {

		quantity = jQuery( 'input.qty' ).val();
		jQuery( '#wholesale_tire_price' ).show();
		jQuery( '#wholesale_tire_price .wrap_' + variation_id ).show();
		jQuery( '#wholesale_tire_price > tbody  > tr' ).each(
			function(index, tr) {
				this_tr = jQuery( this );
				id  = this_tr.data( 'id' );
				min = this_tr.data( 'min' );
				max = this_tr.data( 'max' );

				if (quantity >= min && quantity <= max) {
					jQuery( this_tr ).addClass( "active" );
				} else {
					jQuery( this_tr ).removeClass( "active" );
				}
			}
		);
	}
}

function set_step_quantity( events, variation ) {
	if (variation.is_wholesale == true ) {
		if ( variation.step ) {
			jQuery('.woocommerce-variation-add-to-cart .qty').attr( 'step', variation.step );
			jQuery('.woocommerce-variation-add-to-cart .qty').attr( 'min', variation.input_value );
		} else {
			jQuery('.woocommerce-variation-add-to-cart .qty').attr( 'step', 1 );
			jQuery('.woocommerce-variation-add-to-cart .qty').attr( 'min', 1 );
		}
		if ( variation.input_value ) {
			jQuery('.woocommerce-variation-add-to-cart .qty').val( variation.input_value );
		}
	}
}


jQuery(document).ready(function ($) {
		
	modal = document.getElementById("myModal");
	open_list = document.getElementById("open_list");
	jQuery('.wwp_requisition_list').on('click', '.add_new_list', function () {
		modal.style.display = "block";
	});
	jQuery('#table_id tbody').on('click', '.open_list', function () {
		modal.style.display = "block";
	});
	jQuery(document.body).on('click', '.close', function () {
		modal.style.display = "none";
		jQuery('.modal-body').html('');
	});
	window.onclick = function(event) {
		if (event.target == modal) {
			modal.style.display = "none";
			jQuery('.modal-body').html('');
		}
	}
	  
	jQuery('#table_id').DataTable({
		processing: true,
		serverSide: false,    
		ajax: wwpscript.ajaxurl + '?action=wwp_get_datatable',
		columns: [
			{ data: 'title' },
			{ data: 'count_list' },
			{ 
				data: null,
				render : function( data, type, row ) {
					return '<button id="wwp_edit_'+data.id+'" type="button" class="open_list btn btn-secondary" data-id="'+data.id+'">Open list</button>';
				},
				targets: -1
			},
			{ 
				data: null,
				render : function( data, type, row ) {
					return '<button id="wwp_delete_'+data.id+'" type="button" class="delete_list btn btn-secondary" data-id="'+data.id+'">Delete list</button>';
				},
				targets: -1
			}
		],
	});
	
	jQuery('#table_id tbody').on('click', '.open_list', function () {
		var id = jQuery(this).data('id');
		jQuery.ajax({
				type: "POST",
				data : {action: "requisition_open_list_edit", post_id : id},
				url: wwpscript.ajaxurl,
				beforeSend: function() {
					jQuery('.loader').show();
				},
				success: function (response) {
					jQuery('.modal-body').html(response);
					create_select2(); 
					update_requisition();
				},
				complete: function() {
					jQuery('.loader').hide();
				}
			});
		return false;
	});
		
	jQuery('#myModal').on('click','.dashicons-trash',function(){
		var row = jQuery(this).parents('.wwp_table_row');
		console.log(row );
		row.remove();
		return false;
	});

	jQuery('#table_id tbody').on('click', '.delete_list', function () {
		var id = jQuery(this).data('id');
		jQuery(this).prop('disabled', true);
		jQuery.ajax({
				type: "POST",
				data : {action: "requisition_list_delete", post_id : id},
				url: wwpscript.ajaxurl,
				success: function (response) {
					jQuery('#table_id').DataTable().ajax.reload();
				}
			});
		return false;
	});
		
	jQuery(document.body).on('click','#wwp_add_to_cart',function(event){
		event.preventDefault();
		values = jQuery('#form_requisition_list').serializeArray();
		values.find(input => input.name == 'action').value = 'requisition_list_add_to_cart';
		
		console.log(values);
		
		jQuery.ajax({
				type: 'POST',
				data: values,
				url: wwpscript.ajaxurl,
				beforeSend: function() {
					jQuery('#wwp_add_to_cart').prop('disabled', true);
				},
				success: function (response) {
					modal.style.display = "none";
					alert('Successfully Added');
					jQuery('#wwp_add_to_cart').prop('disabled', false);
					jQuery(document.body).trigger("wc_fragment_refresh");
					window.location.assign(wwpscript.wc_get_cart_url );
				}
			});
		return false;
	});	
	
	
	jQuery(document.body).on('click','#requisition_add_to_list',function(event){
		event.preventDefault();
		jQuery('#requisition_add_to_list').prop('disabled', true);
		list_name_edit = '';
		list_name = prompt( "Please enter requisition list name", list_name_edit );
		if (list_name === null) {
			jQuery('#requisition_add_to_list').prop('disabled', false);
			return false;
		}
		if ( list_name != null || list_name != "" ) {
			values = jQuery('.woocommerce-cart-form').serializeArray();
			var formData = jQuery('.woocommerce-cart-form').serialize();
			formData += '&action=requisition_list_add_cart_page&list_name='+list_name;
			console.log(formData);
			jQuery.ajax({
				type: 'POST',
				data: formData,
				url: wwpscript.ajaxurl,
				success: function (response) {
				alert(response);	
				jQuery('#requisition_add_to_list').prop('disabled', false);
				}
			});
		} 
		return false;
	});	
	
	jQuery('#myModal').on('click', '#wwp_save_list', function (event) {
		event.preventDefault();
		jQuery('#wwp_save_list').prop('disabled', true);
		list_name_edit = '';
		if (jQuery('#wwp_list_name').val() != '') {
			list_name_edit = jQuery('#wwp_list_name').val();
		}
		list_name = prompt( "Please enter requisition list name", list_name_edit );
		if (list_name === null) {
			jQuery('#wwp_save_list').prop('disabled', false);
			return false;
		}
		if ( list_name != null || list_name != "" ) {
			
			jQuery('#wwp_list_name').val(list_name);
			jQuery.ajax({
				type: 'POST',
				data: jQuery('#form_requisition_list').serialize(),
				url: wwpscript.ajaxurl,
				success: function (response) {
					modal.style.display = "none";
					 jQuery('.modal-body').html('');
					 jQuery('#table_id').DataTable().ajax.reload();
				}
			});
		} 
		return false;
	});				

	jQuery('.wwp_requisition_list').on('click', '.add_new_list', function () {
		jQuery.get( wwpscript.plugin_url + "inc/template/requisition-list-template-new.php", function(data) {
			jQuery('.modal-body').html(data);
			create_select2(); 
		});
		return false;
	});
	
	jQuery(document.body).on('click','.wwptotalamount button',function(){
		numItems =jQuery('.wwp_table_row').length;
		numItems++;
		numItems = Math.floor(Math.random() * 11111111) + numItems;
		jQuery('.wwp_requisition_list_table').append('<tr class="wwp_table_row"><td><div class="wwp_product_title"><span class="dashicons dashicons-trash"></span><select class="select2 wwp_requisition_list_price" data-price="0" name="requisition['+numItems+'][wwp_product_id]" style="width:300px;"></select></div></td><td><div class="wwp_product_qty"><input type="number" min="1" class="form-control wwp_requisition_list_qty" value="1" name="requisition['+numItems+'][wwp_product_qty]" ></div></td><td><div class="wwp_product_subtotal"><input type="hidden" class="wwp_product_price wwp_requisition_list_price_hide" name="requisition['+numItems+'][wwp_product_price]"> <span class="wwp_requisition_list_price_display"> $0 </span> </div></td></tr>');
		create_select2(); 
	});

	jQuery(document.body).on('change','.wwp_requisition_list_qty', function() {
		update_requisition ();
	});
	
	jQuery(document.body).on('change','.select2', function() {
		if( typeof jQuery(this).select2('data')[0] != "undefined" ) {
			jQuery(this).attr("data-price", jQuery(this).select2('data')[0].tags);
			update_requisition();
		}
	});

		function update_requisition() {
			var sum = 0;
			jQuery('.wwp_requisition_list_table tr.wwp_table_row').each(function(index, tr) {
				price = parseFloat(jQuery(tr).find('.wwp_requisition_list_price').attr("data-price"));
				qty = parseInt(jQuery(tr).find('.wwp_requisition_list_qty').val());
				price = qty * price;
				sum += price;
				jQuery(tr).find('.wwp_requisition_list_price_hide').val(price);
				jQuery(tr).find('.wwp_requisition_list_price_display').html( wwpscript.currency_symbol + price);
				console.log(sum);
			});
			jQuery('.totalamountpopup .wwp_price').html( wwpscript.currency_symbol + sum);
		}
		
		function create_select2 () {
			$('.select2').select2({
			  ajax: {
					url: wwpscript.ajaxurl, 
					dataType: 'json',
					//delay: 50,
					data: function (params) {
						console.log(params);
						  return {
							q: params.term,
							action: 'select2_get_ajax_callback',
							bytype: jQuery('#wwp_search_by_type').val()
						  };
					},
					processResults: function( data ) {
						var options = [];
						if ( data ) {
							$.each( data, function( index, text ) { 
								options.push( { id: text[0], text: text[1] , tags: text[2]  } );
							});
						}
						return { results: options };
					},
					cache: false
				},
				minimumInputLength: 3,
				//width: 'resolve' ,
				placeholder: "Search a product",
				allowClear: true,
				//tags: true
			});
			
			jQuery('.select2').on('select2:unselect', function (e) {
				jQuery(this).attr("data-price",0);
				update_requisition();
			});	
		}
		jQuery('.wwp-password-toggle').on('click', function() {
    		var input = document.getElementById("wwp_wholesaler_password");
			togglePasswordVisibility(input);
		} );
		jQuery('.wwp-confirm-password-toggle').on('click', function() {
   			var input = document.getElementById("reg_password2");
			togglePasswordVisibility(input);
		} );
	 
});				
 
function wwp_add_to_cart_variation_set () {
	variation_seleted ='';
	jQuery.each( jQuery("form.variations_form").find('.wwp_variation_wrap:visible'), function( key, value ) {
		vari_obj = jQuery(this);
		all_variation = vari_obj.attr('data-attr-slug');
		variation_id = vari_obj.attr('date-variation-id');
		variation_qty = jQuery(".get_variation_qty_"+variation_id).val();
		check  =  variation_id + ':' + variation_qty + ',';
		if ( ! variation_seleted.match(check) && variation_qty != '0' ) {
			variation_seleted +=  variation_id + ':' + variation_qty + ',';
		}
	});
	if (variation_seleted != "") {
		if(jQuery("#wwp_variation_add_to_cart").length == 0) {
			jQuery("form.variations_form").append('<input type="hidden" id="wwp_variation_add_to_cart" name="wwp_variation_add_to_cart" value="true" />');
		}
		setTimeout(function(){
			jQuery(".single_add_to_cart_button").removeClass("disabled");
		}, 100);
		
		jQuery("form.variations_form input[name='add-to-cart']").val(variation_seleted);
	}
}

function wwp_variation_update () {
	
    if(jQuery('.variation_id').val() == '0' || jQuery('.variation_id').val() == ''){
		jQuery('.wwp_variation_wrap').show();
        return;
    }
	variation_data = {};
	jQuery(jQuery("form.variations_form").find('select')).each(function() {
		variation_data[jQuery(this).data( 'attribute_name' )]=this.value;
	});
    jQuery.each( jQuery("form.variations_form").find('.wwp_variation_wrap'), function( key, value ) {
        vari_obj = jQuery(this);
        all_variation = vari_obj.attr('data-attr-slug');
        variation_id = vari_obj.attr('date-variation-id');
		vari_obj.show();
		jQuery.each( JSON.parse( all_variation ) , function( key, value ) {
			if (variation_data[key] != value) {
				vari_obj.hide();
			}
		});
    });
    wwp_add_to_cart_variation_set();
 }
	  
 jQuery('form.cart').on( 'click', 'button.plus, button.minus', function() {
  
	var qty = jQuery( this ).closest( '.wwp_variation_wrap' ).find( '.wwp_quantitys' );
	var val = parseFloat(qty.val());
	var max = 1;
	var min = 0;
	var step = 1;

	if ( jQuery( this ).is( '.plus' ) ) {
	   qty.val( val + step );
	} else {
		if ( ( min == val ) ) {
			qty.val( min );
		} else if ( min && ( min >= val ) ) {
			qty.val( min );
		}else {
		  qty.val( val - step );
		}
	}
	wwp_variation_update();     
});
jQuery(".wwp_variation_wrap.wwp_disable_variation .wwp_quantitys").val(0);
jQuery( ".variations_form" ).on( "woocommerce_variation_select_change", function () {
	wwp_variation_update();
} );

jQuery( ".single_variation_wrap" ).on( "show_variation", function ( event, variation ) {
	wwp_variation_update();
} );
 
function toggleShippingFieldsValidation() {
	jQuery("input[name='wwp_wholesaler_copy_billing_address']").on('change', function() {
		jQuery("#wholesaler_shipping_address input, #wholesaler_shipping_address select").attr( "required", !jQuery(this).is(":checked") )
	});
}

function togglePasswordVisibility(input) {
	if (input.type === "password") {
		input.type = "text";
	} else {
		input.type = "password";
	}
}
