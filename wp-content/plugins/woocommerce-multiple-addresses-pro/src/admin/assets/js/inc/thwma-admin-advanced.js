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