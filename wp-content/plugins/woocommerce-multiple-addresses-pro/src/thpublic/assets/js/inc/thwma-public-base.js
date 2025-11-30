var thwma_public_base = (function($, window, document) {
	'use strict';
	
	function isEmpty(val){
		return (val === undefined || val == null || val.length <= 0) ? true : false;
	}
			
	$.fn.getType = function(){
        try{
            return this[0].tagName == "INPUT" ? this[0].type.toLowerCase() : this[0].tagName.toLowerCase(); 
        }catch(err) {
            return 'E001';
    	}
    }
    
	/********************************************
	***** CHARACTER COUNT FUNCTIONS - START *****
	********************************************/
	function display_char_count(elm, isCount){
		var fid = elm.prop('id');
        var len = elm.val().length;
		var displayElm = $('#'+fid+"-char-count");
		
		if(isCount){
			displayElm.text('('+len+' characters)');
		}else{
			var maxLen = elm.prop('maxlength');
			var left = maxLen-len;
			displayElm.text('('+left+' characters left)');
			if(rem < 0){
				displayElm.css('color', 'red');
			}
		}
	}
    /******************************************
	***** CHARACTER COUNT FUNCTIONS - END *****
	******************************************/
	
	function set_field_value_by_elm(elm, type, value, f_key){
		var wrapper_selectors = '.woocommerce-billing-fields,' +
			'.woocommerce-shipping-fields,' +
			'.woocommerce-address-fields,' +
			'.woocommerce-shipping-calculator';
		var $wrapper = $( this ).closest( wrapper_selectors );
		switch(type){
			case 'radio':
				elm.val([value]);
				break;
			case 'checkbox':
				if(elm.data('multiple') == 1){
					value = value ? value : [];
					elm.val([value]).change();
				}else{
					elm.val([value]).change();
				}
				break;
			case 'select':
				var options_append = thwma_public_var.select_options;
				if(options_append == true){
					var option_values = [];
					elm.find('option').each(function(option_key,option_val) {
						if($(this).val() != ""){
							option_values[option_key] = $(this).val();
						}
					});
					
					if( $.inArray(value,option_values) != -1){
						if(elm.prop('multiple')){
							elm.val(value);
						}else{
							elm.val([value]).change();
						}
					}else{
						elm.append($("<option></option>").attr("value",value).text(value)); 
						elm.val([value]).change();
					}
				}else{
					if(elm.prop('multiple')){
						elm.val(value);
					}else{

						// For guest users.
						var current_user_id = thwma_public_var.current_user_id;
						if(current_user_id != 0) {
							if($('select#'+f_key).length){

								// Case of multi-shipping address populate.
								$('select#'+f_key).val(value).change();
								//$( document.body ).trigger( 'country_to_state_changed', [value, $wrapper ] );
							} else {

								//Case of custom section address populate.
								elm.val([value]).change();
							}							
						} else {
							$('select#'+f_key).val(value).change();
							$( document.body ).trigger( 'country_to_state_changed', [value, $wrapper ] );
						}
					}
				}
				break;
			case 'multiselect':			
				if(elm.prop('multiple')){
					if(typeof(value) != "undefined"){
						elm.val(value.split(',')).change();
					}
				}else{
					elm.val([value]).change();
				}
				break;
			case 'file':
                elm.val(value).change();
                set_field_value_file(elm, value);
                //elm.trigger("change")
                break;                
			case 'hidden':
				elm.val(value).change();
                break;
			default:
				$('input#'+f_key+'.input-text').val(value).change();
				// elm.val(value).change();
				//elm.trigger("change")

				break;
		}
	}

	function thwma_set_field_value_by_elm(elm, type, value){
		elm.closest('p').removeClass('woocommerce-validated');
		elm.closest('p').removeClass('woocommerce-invalid');
		
		switch(type){
			case 'radio':
				elm.val('');
				break;
			case 'checkbox':
				if(elm.data('multiple') == 1){
					value = value ? value : [];
					elm.val('');
				}else{
					elm.val('');
				}
				break;
			case 'select':

				var options_append = thwma_public_var.select_options;
				if(options_append == true){
					var option_values = [];
					elm.find('option').each(function(option_key,option_val) {
						if($(this).val() != ""){
							option_values[option_key] = $(this).val();
						}
					});
					
					if( $.inArray(value,option_values) != -1){
						if(elm.prop('multiple')){
							elm.val('');
						}else{
							elm.val('');
						}
					}else{
						elm.append($("<option></option>").attr("value",value).text(value)); 
						elm.val('');
					}
				}else{

					if(elm.prop('multiple')){
						elm.val('');
					}else{
						elm.val('').trigger('change.select2');
						// $('option:selected', this).removeAttr('selected');
						// elm.attr('selected', '-1');
					}
				}
				break;
			case 'multiselect':
			
				if(elm.prop('multiple')){
					if(typeof(value) != "undefined"){
						elm.val(value.split(',')).val('');
						
					}
				}else{
					elm.val('');
				}
				break;
			case 'file':
                elm.val('');
                set_field_value_file(elm, value);
                //elm.trigger("change")
                break;                
			case 'hidden':
                break;
			default:
				elm.val('');
				//elm.trigger("change")
				break;
		}
	}

	function set_field_value_file(input, uploaded_str){
        if(thwcfe_public_file_upload){
            var wrapper = input.closest('.thwcfe-input-field-wrapper');

            if(uploaded_str){
                var uploaded = JSON.parse(uploaded_str);
                var file_name = uploaded['name'];

                var prev_html = thwcfe_public_file_upload.prepare_preview_html(uploaded);
                wrapper.find('.thwcfe-upload-preview').html(prev_html);
                
                wrapper.find('.thwcfe-uloaded-files').show();
                wrapper.find('.thwcfe-checkout-file').hide();

                input.trigger("change");
            }else{
                thwcfe_public_file_upload.clean_file_input(wrapper);
                wrapper.find('.thwcfe-upload-preview').html('');
                wrapper.find('.thwcfe-uloaded-files').hide();
                wrapper.find('.thwcfe-checkout-file').show();
            }
            
        }
    }

	
	function get_field_value(type, elm, name){
		var value = '';
		switch(type){
			case 'radio':
				value = $("input[type=radio][name="+name+"]:checked").val();
				break;
			case 'checkbox':
				if(elm.data('multiple') == 1){
					var valueArr = [];
					$("input[type=checkbox][name='"+name+"[]']:checked").each(function(){
					   valueArr.push($(this).val());
					});
					value = valueArr;//.toString();
				}else{
					value = $("input[type=checkbox][name="+name+"]:checked").val();
				}
				break;
			case 'select':
				value = elm.val();
				break;
			case 'multiselect':
				value = elm.val();
				break;
			default:
				value = elm.val();
				break;
		}
		return value;
	}
	
	return {
		display_char_count : display_char_count,
		set_field_value_by_elm : set_field_value_by_elm,
		get_field_value : get_field_value,
		thwma_set_field_value_by_elm: thwma_set_field_value_by_elm,
	};
}(window.jQuery, window, document));
