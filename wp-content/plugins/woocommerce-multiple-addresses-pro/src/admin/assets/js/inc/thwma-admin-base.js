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
