var thwma_settings_advanced = (function($, window, document) {
   /*------------------------------------
	*---- ON-LOAD FUNCTIONS - SATRT -----
	*------------------------------------*/
	$(function() {
		var advanced_settings_form = $('#advanced_settings_form');
		if(advanced_settings_form[0]) {
			thwma_base.setupEnhancedMultiSelectWithValue(advanced_settings_form);
		}
	});
   /*------------------------------------
	*---- ON-LOAD FUNCTIONS - END -----
	*------------------------------------*/

}(window.jQuery, window, document));	

var thwma_settings_general = (function($, window, document) {
   /*------------------------------------
	*---- ON-LOAD FUNCTIONS - SATRT -----
	*------------------------------------*/
	$(function() {
		var general_settings_form = $('#thwma_settings_fields_form');
		if(general_settings_form[0]) {
			thwma_base.setupEnhancedMultiSelectWithValue(general_settings_form);
		}
	});
   /*------------------------------------
	*---- ON-LOAD FUNCTIONS - END -----
	*------------------------------------*/

}(window.jQuery, window, document));
var thwma_base = (function($, window, document) {
	'use strict';

	/* convert string to url slug */
	/*function sanitizeStr( str ) {
		return str.toLowerCase().replace(/[^\w ]+/g,'').replace(/ +/g,'_');
	};

	function escapeQuote( str ) {
		str = str.replace( /[']/g, '&#39;' );
		str = str.replace( /["]/g, '&#34;' );
		return str;
	}

	function unEscapeQuote( str ) {
		str = str.replace( '&#39;', "'" );
		str = str.replace( '&#34;', '"' );
		return str;
	}*/

	function escapeHTML(html) {
	   var fn = function(tag) {
		   var charsToReplace = {
			   '&': '&amp;',
			   '<': '&lt;',
			   '>': '&gt;',
			   '"': '&#34;'
		   };
		   return charsToReplace[tag] || tag;
	   }
	   return html.replace(/[&<>"]/g, fn);
	}

	function isHtmlIdValid(id) {
		//var re = /^[a-z]+[a-z0-9\_]*$/;
		var re = /^[a-z\_]+[a-z0-9\_]*$/;
		return re.test(id.trim());
	}

	function isValidHexColor(value) {
		if ( preg_match( '/^#[a-f0-9]{6}$/i', value ) ) { // if user insert a HEX color with #
			return true;
		}
		return false;
	}

	function setup_tiptip_tooltips(){
		var tiptip_args = {
			'attribute': 'data-tip',
			'fadeIn': 50,
			'fadeOut': 50,
			'delay': 200
		};

		$('.tips').tipTip( tiptip_args );
	}

	function setup_enhanced_multi_select(parent){
		parent.find('select.thpladmin-enhanced-multi-select').each(function(){
			if(!$(this).hasClass('enhanced')){
				$(this).select2({
					minimumResultsForSearch: 10,
					allowClear : true,
					placeholder: $(this).data('placeholder')
				}).addClass('enhanced');
			}
		});
	}

	function setup_enhanced_multi_select_with_value(parent){
		parent.find('select.thpladmin-enhanced-multi-select').each(function(){
			if(!$(this).hasClass('enhanced')){
				$(this).select2({
					minimumResultsForSearch: 10,
					allowClear : true,
					placeholder: $(this).data('placeholder')
				}).addClass('enhanced');

				var value = $(this).data('value');
				if(value){
					value = value.split(",");

					$(this).val(value);
					$(this).trigger('change');
				}
			}
		});
	}
	function setup_color_picker(form){
		form.find('.thpladmin-colorpick').iris({
			change: function( event, ui ) {
				$( this ).parent().find( '.thpladmin-colorpickpreview' ).css({ backgroundColor: ui.color.toString() });
			},
			hide: true,
			border: true
		}).click( function() {
			$('.iris-picker').hide();
			$(this ).closest('td').find('.iris-picker').show();
		});

		$('body').click( function() {
			$('.iris-picker').hide();
		});

		$('.thpladmin-colorpick').click( function( event ) {
			event.stopPropagation();
		});

		$( ".thpladmin-colorpick" ).each(function() {
			var value = $(this).val();
			$( this ).parent().find( '.thpladmin-colorpickpreview' ).css({ backgroundColor: value });
		});
	}

	function setup_color_pick_preview(form){
		form.find('.thpladmin-colorpick').each(function(){
			$(this).parent().find('.thpladmin-colorpickpreview').css({ backgroundColor: this.value });
		});
	}

	function setup_popup_tabs(form, selector_prefix){
		$("."+selector_prefix+"-tabs-menu a").click(function(event) {
			event.preventDefault();
			$(this).parent().addClass("current");
			$(this).parent().siblings().removeClass("current");
			var tab = $(this).attr("href");
			$("."+selector_prefix+"-tab-content").not(tab).css("display", "none");
			$(tab).fadeIn();
		});
	}

	function open_form_tab(elm, tab_id, form_type){
		var tabs_container = $("#thwepo-tabs-container_"+form_type);

		$(elm).parent().addClass("current");
		$(elm).parent().siblings().removeClass("current");
		var tab = $("#"+tab_id+"_"+form_type);
		tabs_container.find(".thpladmin-tab-content").not(tab).css("display", "none");
		$(tab).fadeIn();
	}

	function prepare_field_order_indexes(elm) {
		$(elm+" tbody tr").each(function(index, el){
			$('input.f_order', el).val( parseInt( $(el).index(elm+" tbody tr") ) );
		});
	}

	function setup_sortable_table(parent, elm, left){
		parent.find(elm+" tbody").sortable({
			items:'tr',
			cursor:'move',
			axis:'y',
			handle: 'td.sort',
			scrollSensitivity:40,
			helper:function(e,ui){
				ui.children().each(function(){
					$(this).width($(this).width());
				});
				ui.css('left', left);
				return ui;
			}
		});

		$(elm+" tbody").on("sortstart", function( event, ui ){
			ui.item.css('background-color','#f6f6f6');
		});
		$(elm+" tbody").on("sortstop", function( event, ui ){
			ui.item.removeAttr('style');
			prepare_field_order_indexes(elm);
		});
	}

	function get_property_field_value(form, type, name){
		var value = '';

		switch(type) {
			case 'select':
				value = form.find("select[name=i_"+name+"]").val();
				value = value == null ? '' : value;
				break;

			case 'checkbox':
				value = form.find("input[name=i_"+name+"]").prop('checked');
				value = value ? 1 : 0;
				break;

			default:
				value = form.find("input[name=i_"+name+"]").val();
				value = value == null ? '' : value;
		}

		return value;
	}

	function set_property_field_value(form, type, name, value, multiple){
		switch(type) {
			case 'select':
				if(multiple == 1 && typeof(value) === 'string'){
					value = value.split(",");
					name = name+"[]";
				}
				form.find('select[name="i_'+name+'"]').val(value);
				break;

			case 'checkbox':
				value = value == 1 ? true : false;
				form.find("input[name=i_"+name+"]").prop('checked', value);
				break;

			default:
				form.find("input[name=i_"+name+"]").val(value);
		}
	}

	function set_field_value_by_elm(elm, type, value){
		switch(type){
			case 'radio':
				elm.val([value]);
				break;
			case 'checkbox':
				if(elm.data('multiple') == 1){
					value = value ? value : [];
					elm.val([value]);
				}else{

					elm.val([value]);
				}
				break;
			case 'select':
				if(elm.prop('multiple')){
					elm.val(value);
				}else{
					elm.val([value]).change();
				}
				break;
			case 'country':
				elm.val([value]).change();
				break;
			case 'state':
				elm.val([value]).change();
				break;
			case 'multiselect':

				if(elm.prop('multiple')){
					if(typeof(value) != "undefined"){
						elm.val(value.split(',')).change();
					}
				}else{
					elm.val([value]);
				}
				break;
			default:
				elm.val(value);
				break;
		}
	}

	return {
		escapeHTML : escapeHTML,
		isHtmlIdValid : isHtmlIdValid,
		isValidHexColor : isValidHexColor,
		setup_tiptip_tooltips : setup_tiptip_tooltips,
		setupEnhancedMultiSelect : setup_enhanced_multi_select,
		setupEnhancedMultiSelectWithValue : setup_enhanced_multi_select_with_value,
		setupColorPicker : setup_color_picker,
		setup_color_pick_preview : setup_color_pick_preview,
		setupSortableTable : setup_sortable_table,
		setupPopupTabs : setup_popup_tabs,
		openFormTab : open_form_tab,
		get_property_field_value : get_property_field_value,
		set_property_field_value : set_property_field_value,
		set_field_value_by_elm:set_field_value_by_elm,
   	};
}(window.jQuery, window, document));

/* Common Functions */
// function thwepoSetupEnhancedMultiSelectWithValue(elm){
// 	thwma_base.setupEnhancedMultiSelectWithValue(elm);
// }

// function thwepoSetupSortableTable(parent, elm, left){
// 	thwma_base.setupSortableTable(parent, elm, left);
// }

// function thwepoSetupPopupTabs(parent, elm, left){
// 	thwma_base.setupPopupTabs(parent, elm, left);
// }

// function thwepoOpenFormTab(elm, tab_id, form_type){
// 	thwma_base.openFormTab(elm, tab_id, form_type);
// }

var thwma_settings = (function($, window, document) {
	'use strict';
	var MSG_INVALID_NAME = 'NAME/ID must begin with a lowercase letter ([a-z]) and may be followed by any number of lowercase letters, digits ([0-9]) and underscores ("_")';

	/*--------------------------------------------------
	*-- START remove edit order page full colon symbol--
	*-------------------------------------------------*/
	jQuery(document).ready(function($) {
		var is_css_id_exist = $('#thwma-css-textarea');
		if(is_css_id_exist.length > 0){
			wp.codeEditor.initialize($('#thwma-css-textarea'), thwma_settings);
		}
	});
	/*-------------------------------------------------
	*-- END remove edit order page full colo--
	*-------------------------------------------------*/
	
   /*------------------------------------
	*---- ON-LOAD FUNCTIONS - SATRT -----
	*------------------------------------*/
	$(function() {
		var settings_form = $('#thwma_settings_fields_form');
		thwma_base.setup_tiptip_tooltips();
		thwma_base.setupColorPicker(settings_form);
		set_select2();
		set_select_woo();
		disable_fields();
		check_api_autofill();
		add_new_addr_mssg();
		display_handle_fee();
		disable_nd_enable_multi_shipping();
		display_time_field();
		//check_country_is_changed();
		warnig_for_set_time_period();
		hide_edit_button_on_admin_order();
		check_enable_shipping();
		check_enable_multi_shipping();
	});

   /*------------------------------------
	*---- ON-LOAD FUNCTIONS - END -------
	*------------------------------------*/
	function addrow(id){
		var table = document.getElementById(id);
		var rowCount = table.rows.length;
		var row = table.insertRow(rowCount);

		var colCount = table.rows[0].cells.length;

		for(var i=0; i<colCount; i++) {

			var newcell	= row.insertCell(i);

			newcell.innerHTML = table.rows[0].cells[i].innerHTML;
			$('.thwma-def-select-map-option', newcell).select2({
			    placeholder: thwma_var.slt_def_fld
			});
			$('.thwma-sec-select-map-option', newcell).select2({
			    placeholder: thwma_var.slt_cus_fld
			});
			switch(newcell.childNodes[0].type) {
				case "text":
						newcell.childNodes[0].value = "";
						break;
				case "checkbox":
						newcell.childNodes[0].checked = false;
						break;
				case "select-one":
						newcell.childNodes[0].selectedIndex = 0;
						break;
			}
		}
	}

	function deleterow(div,sectn){
		var whichtr = div.closest("tr");
		whichtr.remove();
	}

	function custom_billing_form(e,elm,type,action,key){
		//initialize_select2()
		//var $popup_div = $("#1"+type+"-address");
		var $popup_div = $("#custom-"+type+"-address");
		var additional_adr = '';
		if(type == 'billing') {
			var additional_adr = thwma_var.additional_billing_adr;
		} else if(type == 'shipping') {
			var additional_adr = thwma_var.additional_shipping_adr;
		}
		if(action == 'edit'){
			var popup =  $popup_div.dialog({
				'title': additional_adr,
		        modal: !0,
		        width: 900,
		        dialogClass: 'wp-dialog thwma-popup-admin',
		        resizable: !1,
		        autoOpen: !1,
		        buttons: [{
		            text: thwma_var.cancel,
		            click: function() {
		                $(this).dialog("close")
		            }
		        	},{
		            text: thwma_var.update_address,
		            click: function() {

	           			$("#"+type).submit()
		            }
	       		}]
	   		});
		}else{
			var popup =  $popup_div.dialog({
				'title': additional_adr,
		        modal: !0,
		        width: 900,
		        dialogClass: 'wp-dialog thwma-popup-admin',
		        resizable: !1,
		        autoOpen: !1,
		        buttons: [{
		            text: thwma_var.cancel,
		            click: function() {
		                $(this).dialog("close")
		            }
		        	},{
		            text: thwma_var.add_address,
		            click: function() {
	           			$("#"+type).submit()
		            }
	       		}]
	   		});
		}

		var address_fields = [];
		if(type == 'billing'){
			address_fields = thwma_var.address_fields_billing;
		}else{
			address_fields = thwma_var.address_fields_shipping;
		}

	    if(action =='edit'){
	    	$popup_div.find('input[name = '+type+'_custom_address_key]').val(key);
	    	var address_json = $(".e_adrs_"+type+'_'+key).val();

	    	var $address = JSON.parse(address_json);
	    	if(typeof ($address['billing_heading']) !== 'undefined'){
	    		var meta_val = $address['billing_heading'];
				var meta_key =  $('#custom_billing_heading');
	    		thwma_base.set_field_value_by_elm(meta_key,'text',meta_val);
	    	}
	    	if(typeof ($address['shipping_heading']) !== 'undefined'){
	    		var meta_val = $address['shipping_heading'];
				var meta_key =  $('#custom_shipping_heading');
	    		thwma_base.set_field_value_by_elm(meta_key,'text',meta_val);
	    	}
	    	$.each(address_fields, function(address_key, address_type) {
	    		var meta_val = $address[address_key];
	    		if(address_type == 'radio') {
	    			var meta_key =  $('#custom_'+address_key+'_'+meta_val);
	    		} else {
					var meta_key =  $('#custom_'+address_key);
				}
					thwma_base.set_field_value_by_elm(meta_key,address_type, meta_val);
	    	});
	    }else{
	    	$('#custom_billing_heading').val('');
	    	$('#custom_shipping_heading').val('');
	    	$.each(address_fields, function(address_key, address_type) {
	    		var meta_val = '';
	    		if(address_type == 'radio') {
	    			var meta_key =  $('#custom_'+address_key+'_'+meta_val);
	    		} else {
					var meta_key =  $('#custom_'+address_key);
				}
				thwma_base.set_field_value_by_elm(meta_key,address_type, meta_val);
	    	});
	    }
		popup.dialog('open');
		initialize_select2_country();
		check_country_is_changed();

	}
	function check_country_is_changed(){
		$('select#custom_billing_country, select#custom_billing_country').on( 'change', function (){
			initialize_select2_state();
		});
		$('select#custom_shipping_country, select#custom_shipping_country').on( 'change', function (){
			initialize_select2_state();
		});
	}
	function disable_fields(){
		var check_disable = $('.check_map_disable');
		$.each( check_disable, function( key, value ) {
			var section_name = $(this).closest( "thead" ).siblings("tbody").attr('id');

			if($(this).val() == 'no'){
				var custom_addr_name = $('.thwma_admin_fields_table').find('.custom_addr_name').val();

				$('.thwma_admin_fields_table #'+section_name+' span.select2-selection').unbind("click");
				$('.thwma_admin_fields_table #'+section_name+' span.select2-selection').css({"pointer-events": "none","border":"1px solid #bdbdbd"});
				//$(".thwma_admin_fields_table span.select2-selection").css("border","1px solid #bdbdbd");
				$('.thwma_admin_fields_table #'+section_name+' span.select2-selection .select2-selection__rendered').css("color", "#bdbdbd");
				$('.thwma_admin_fields_table #'+section_name+' .f_add_btn').css({"pointer-events": "none", "background-color":"#86b9d8", "border": "1px solid #2a78a900"});
				$('.thwma_admin_fields_table #'+section_name+' .f_delete_btn').css({"pointer-events": "none", "background-color":"#f39a9a", "border": "1px solid #2a78a900"});
			}
		});

		var disable_adr_mngmt = $('input[name="i_disable_address_management"]');
		var usr_role = $('#advanced_settings_form select[name="i_select_user_role[]"]');

		var selected_usr_role = $('input[name="i_hidden_user_role"]');
		var selcted_user_roles = usr_role.val();
		var hidden_user_role_value = selected_usr_role.val();

		//$(document).on('change',usr_role,function() {
		usr_role.change(function(){
			var role_values = $(this).val();
			selected_usr_role.val(role_values);
		});


		if (disable_adr_mngmt.is(":checked")) {
		   	usr_role.prop("disabled", false);
		} else{
			usr_role.prop("disabled", true);
		}
		$(document).on('click','input[name="i_disable_address_management"]',function() {
			if ($(this).prop("checked")) {
		    	usr_role.prop("disabled", false);
			} else{
				usr_role.prop("disabled", true);
			}
		})
	}
	
	/** 
	 * set up select woo
	 * here only exicute `thwma-exclude-product` class only.
	 * On genaral settings page multiple shipping section `Exclude certain products from multi-shipping`.
	 */
	function set_select_woo(){
		$("select.thwma-exclude-product").selectWoo({
			allowClear : true,
			placeholder: $(this).attr('placeholder'),
			escapeMarkup: function (text) { return text; },
			ajax: {
				type: 'POST',
		        url: thwma_var.ajax_url,
		        data: function(params) {
		            return {
		            	action: 'thwma_load_products',
		                term: params.term || '',
		                page: params.page || 1,
		            }
		        },
		        processResults: function (result, params) {
                    return result.data;
				},
		        cache: true
		    },
		})

		var value = $("select.thwma-exclude-product").data('value');
		if(value){
			value = value.split(",");

			$(this).val(value);
			$(this).trigger('change');
		}
	}

	function set_select2(){
		$($("#myBox").select2("container")).addClass("error");
		$("#thwma_cus_section").select2({
		    placeholder: thwma_var.slt_cus_sec
		});
		$("#thwma_def_section").select2({
		    placeholder: thwma_var.slt_def_sec
		});
		$(".thwma-def-select-map-option1").select2({
		    placeholder: thwma_var.slt_def_fld
		});
		$(".thwma-sec-select-map-option1").select2({
		    placeholder: thwma_var.slt_cus_fld
		});
		$('#advanced_settings_form .thpladmin-enhanced-multi-select').select2({
			placeholder: thwma_var.slt_user_role
		});
		$('#thwma_settings_fields_form .thpladmin-enhanced-multi-select').select2();
	}
	function initialize_select2_country(){
		$("select#custom_shipping_country").select2({
			tags: true,
		    dropdownParent: $("#custom-shipping-address")
		});
		$("select#custom_billing_country").select2({
			tags: true,
		    dropdownParent: $("#custom-billing-address")
		});
	}
	function initialize_select2_state(){
		$("select#custom_shipping_state").select2({
			tags: true,
		    dropdownParent: $("#custom-shipping-address")
		});
		$("select#custom_billing_state").select2({
			tags: true,
		    dropdownParent: $("#custom-billing-address")
		});
	}
	function check_api_autofill(){
		var auto_apikey_field = $('input[name="i_autofill_apikey"]');
        var auto_save = $('input[name="save_settings"]');
        var auto_check = $('input[name="i_enable_autofill"]');
		if(auto_check.prop("checked") == true){
			api_active_deactive(auto_apikey_field,auto_save,auto_check);
			auto_check.change(function(){
				if(auto_check.prop("checked") == true){
					api_active_deactive(auto_apikey_field,auto_save,auto_check);
				} else{
					auto_save.attr('disabled', false);
				}
			});
		} else{
			auto_save.attr('disabled', false);
			auto_check.change(function(){
				if(auto_check.prop("checked") == true){
					api_active_deactive(auto_apikey_field,auto_save,auto_check);
				} else{
					auto_save.attr('disabled', false);
				}
			});
		}


	}
	function api_active_deactive(auto_apikey_field,auto_save,auto_check){
		auto_apikey_field.each(function () {
			if (!$(this).val()) {
			   	auto_save.attr('disabled', true);
			    return false;
			}
		});
		auto_apikey_field.keyup(function () {
			var trigger = false;
			auto_apikey_field.each(function () {
			    if (!$(this).val()) {
			        trigger = true;
			    }
			});
			if(auto_check.prop("checked") == true){
			  	trigger ? auto_save.attr('disabled', true) : auto_save.removeAttr('disabled');
			}
		});
	}
	function add_new_addr_mssg(){
		var s_add_section_map = $('input[name="s_add_section_map"]');
		var cus_section = $('#thwma_cus_section');
		var def_section = $('#thwma_def_section');
		s_add_section_map.click(function(e){
			//e.preventDefault();
			$('.cus_rqd_msg').html('');
			$('.def_rqd_msg').html('');
			if(!cus_section.val()){
				$('.thpladmin_steps_table.thwma_steps_table').append("<p class='cus_rqd_msg'><span class='cust_rqd_msg'>"+thwma_var.err_msg_cus_section+"</span></p>");
				e.preventDefault();
			}
			if(!def_section.val()){
				$('.thpladmin_steps_table.thwma_steps_table').append("<p class='def_rqd_msg'><span class='deft_rqd_msg'>"+thwma_var.err_msg_def_section+"</span></p>");
				e.preventDefault();
			}
		});
	}
	function display_handle_fee(){
		if($('input[name="i_handling_fee"]').is(':checked')){
			$('.handling-fee-data').show();
		} else{
			$('.handling-fee-data').hide();
		}
		$('input[name="i_handling_fee"]').click(function(){
            if($(this).prop("checked") == true){
            	$('.handling-fee-data').show();
            }
            else if($(this).prop("checked") == false){
                $('.handling-fee-data').hide();
            }
        });
	}

	function disable_nd_enable_multi_shipping(){

		// Case of exclude products.
		var exclude_products = $('#thwma_settings_fields_form select[name="i_exclude_products[]"]');
		var selected_ex_pdt = $('input[name="i_hidden_ex_pdts_list"]');
		exclude_products.change(function(){
			var ex_pdt_values = $(this).val();
			selected_ex_pdt.val(ex_pdt_values);
		});

		// Case of exclude categories.
		var exclude_categories = $('#thwma_settings_fields_form select[name="i_exclude_category[]"]');
		var selected_ex_ctg = $('input[name="i_hidden_ex_catg_list"]');
		exclude_categories.change(function(){
			var ex_ctg_values = $(this).val();
			selected_ex_ctg.val(ex_ctg_values);
		});

		var product_variation = $('input[name="i_enable_product_variation"]');
		var order_shipping_status = $('input[name="i_order_shipping_status"]');
		var product_disticty = $('input[name="i_enable_product_disticty"]');
		// $('input[name="i_exclude_products[]"]');
		// $('input[name="i_exclude_products[]"]');
		// $('input[name="i_exclude_products[]"]');

		if($('input[name="i_enable_cart_shipping"]').is(':checked')){
			exclude_products.prop('disabled', false);
			exclude_categories.prop('disabled', false);
			product_variation.removeAttr("disabled");
			order_shipping_status.removeAttr("disabled");
			product_disticty.removeAttr("disabled");


		} else{
			exclude_products.prop('disabled', 'disabled');
			exclude_categories.prop('disabled', 'disabled');
			product_variation.attr("disabled", true);
			order_shipping_status.attr("disabled", true);
			product_disticty.attr("disabled", true);
		}

		$('input[name="i_enable_cart_shipping"]').click(function(){
            if($(this).prop("checked") == true){
            	exclude_products.prop('disabled', false);
            	exclude_categories.prop('disabled', false);
				product_variation.removeAttr("disabled");
				order_shipping_status.removeAttr("disabled");
				product_disticty.removeAttr("disabled");
            }
            else if($(this).prop("checked") == false){
                exclude_products.prop('disabled', 'disabled');
                exclude_categories.prop('disabled', 'disabled');
				product_variation.attr("disabled", true);
				order_shipping_status.attr("disabled", true);
				product_disticty.attr("disabled", true);
            }
        });
	}

	function display_time_field(){
		if($('input[name="i_enable_guest_shipping"]').is(':checked')){
			$('.thwma-set-time').show();
		} else{
			$('.thwma-set-time').hide();
		}
		$('input[name="i_enable_guest_shipping"]').click(function(){
            if($(this).prop("checked") == true){
            	$('input[name="i_enable_guest_shipping"]').val('yes');
            	$('.thwma-set-time').show();
            }
            else if($(this).prop("checked") == false){
            	$('input[name="i_enable_guest_shipping"]').val('no');
                $('.thwma-set-time').hide();
            }
        });
	}
	function warnig_for_set_time_period(){
		$(document).on('click','#thwma_save_settings',function(event) {
			// Guest user.
			var enable_guest_shipping  = $('input[name="i_enable_guest_shipping"]').val();
			var time_duration  = $('input[name="i_set_time_duration"]').val();

			// Case of Page load.
			if(enable_guest_shipping == 'yes'){
				if(time_duration == ''){
					alert_mssg_1_for_time_duration();
				} else{
					if($.isNumeric(time_duration) == false){
						alert_mssg_2_for_time_duration();
					} else{
						$( "#time_duration_alert" ).remove();
					}
				}
			}

			// Case of after toggle checkbox.
			/*if($('input[name="i_enable_guest_shipping"]').is(':checked')){
					if(time_duration == ''){
						alert_mssg_1_for_time_duration();
					} else{
						if($.isNumeric(time_duration) == false){
							alert_mssg_2_for_time_duration();
						} else{
							$( "#time_duration_alert" ).remove();
						}
					}
			}*/
			$('input[name="i_enable_guest_shipping"]').click(function(){
	            if($(this).prop("checked") == true){
	            	if(time_duration == ''){
						alert_mssg_1_for_time_duration();
					} else{
						if($.isNumeric(time_duration) == false){
							alert_mssg_2_for_time_duration();
						} else{
							$( "#time_duration_alert" ).remove();
						}
					}
	            }
	            else if($(this).prop("checked") == false){
	            	$( "#time_duration_alert" ).remove();
	            }
	        });

			// Manage style.
			var multi_address_url  = $('input[name="i_multi_address_url"]').val();
			var multi_address_button = $('input[name="i_multi_address_button"]').val();
			var multi_shipping_checkbox_label = $('input[name="i_multi_shipping_checkbox_label"]').val();
			if(multi_address_url == ''){
				alert_mssg_manage_style_url();
			}
			if(multi_address_button == ''){
				alert_mssg_manage_style_url();
			}
			if(multi_shipping_checkbox_label == ''){
				alert_mssg_manage_style_checkbox_label();
			}
		});
	}
	function alert_mssg_1_for_time_duration(){
		event.preventDefault();
		$('#time_duration_alert').remove();
		$('#thwma_settings_fields_form').prepend('<div id="time_duration_alert" class="error" style="margin: 5px 5px 5px 0px;"><p>Set time duration is a required field.</p></div>');
	}
	function alert_mssg_2_for_time_duration(){
		event.preventDefault();
		$('#time_duration_alert').remove();
		$('#thwma_settings_fields_form').prepend('<div id="time_duration_alert" class="error" style="margin: 5px 5px 5px 0px;"><p>Please enter a valid Day, Hour or Minute.</p></div>');
	}
	function alert_mssg_manage_style_url(){
		event.preventDefault();
		$('#time_duration_alert').remove();
		$('#thwma_settings_fields_form').prepend('<div id="time_duration_alert" class="error" style="margin: 5px 5px 5px 0px;"><p> Label for multiple address picking URL and Label for Add shipping address button fields cannot be left empty.</p></div>');
	}
	function alert_mssg_manage_style_checkbox_label(){
		event.preventDefault();
		$('#time_duration_alert').remove();
		$('#thwma_settings_fields_form').prepend('<div id="time_duration_alert" class="error" style="margin: 5px 5px 5px 0px;"><p> The checkbox label cannot be left empty.</p></div>');
	}
	// function alert_mssg_manage_style_button(){
	// 	event.preventDefault();
	// 	$('#time_duration_alert').remove();
	// 	$('#thwma_settings_fields_form').prepend('<div id="time_duration_alert" class="error" style="margin: 5px 5px 5px 0px;"><p>Please enter the valid label for the button of the add shipping address.</p></div>');
	// }
	function hide_edit_button_on_admin_order(){
		if($('.multi_ship_enabled').val() == 'yes'){
			var parent_elm = $('.load_customer_shipping').parent();
			var edit_button = parent_elm.siblings('.edit_address').hide();
		} else{
			var parent_elm = $('.load_customer_shipping').parent();
			var edit_button = parent_elm.siblings('.edit_address').show();
		}
	}

	function check_enable_shipping() {
		var shipping_status = $('#a_fenable_shipping').val();
		if(shipping_status == 'no') {
			$(".thpladmin-sections li:not(:first-child)").addClass("ms-section-disabled");
				//$('.thpladmin-sections li a.current').addClass('ms-section-disabled');
		}
	}

	function check_enable_multi_shipping() {
		var multi_shipping_status = $('.check_mult_ship_is_enabled').val();
		if(multi_shipping_status == 'no') {
			$(".thpladmin-sections li:nth-child(3)").addClass("ms-section-disabled");
			$(".thpladmin-sections li:nth-child(4)").addClass("ms-section-disabled");
				//$('.thpladmin-sections li a.current').addClass('ms-section-disabled');
		}
	}



	return {
		set_select2 : set_select2,
		addrow : addrow,
		deleterow : deleterow ,
		custom_billing_form : custom_billing_form,
		disable_fields : disable_fields,
		check_api_autofill : check_api_autofill,
		api_active_deactive: api_active_deactive,
		add_new_addr_mssg:add_new_addr_mssg,
		display_handle_fee : display_handle_fee,
		disable_nd_enable_multi_shipping : disable_nd_enable_multi_shipping,
		display_time_field : display_time_field,
		warnig_for_set_time_period: warnig_for_set_time_period,
		hide_edit_button_on_admin_order: hide_edit_button_on_admin_order,
		check_enable_shipping: check_enable_shipping,
		check_enable_multi_shipping: check_enable_multi_shipping,
   	};

}(window.jQuery, window, document));

function thwmaSampleFunction(){
	thwma_settings.sampleFunction();
}

function addRow(id){
	thwma_settings.addrow(id);
}

function thwmadelete(div,sectn){
	thwma_settings.deleterow(div,sectn);
}

function thwma_admin_custom_address_popup(e,elm,type,action,key){
	thwma_settings.custom_billing_form(e,elm,type,action,key);
}