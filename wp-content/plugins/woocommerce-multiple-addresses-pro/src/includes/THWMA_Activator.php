<?php
/**
 * Fired during plugin activation.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-multiple-addresses-pro
 * @subpackage woocommerce-multiple-addresses-pro/src/includes
 */

namespace Themehigh\WoocommerceMultipleAddressesPro\includes;
if(!defined('WPINC')) {	
	die; 
}

if(!class_exists('THWMA_Activator')) :
	/**
     * The activator class.
     */
	class THWMA_Activator {

		/**
		 * Copy older version settings if any.
		 *
		 * Use pro version settings if available, if no pro version settings found 
		 * check for free version settings and use it.
		 *
		 * - Check for premium version settings, if found do nothing. 
		 * - If no premium version settings found, then check for free version settings and copy it.
		 */
		public static function activate() {
			self::check_for_premium_settings();
		}
		
		/**
		 * Function for check premium settings.
		 */
		public static function check_for_premium_settings() {
			// $premium_settings = get_option(THWMA_Utils::OPTION_KEY_THWMA_SETTINGS);
			
			// if($premium_settings && is_array($premium_settings)) {			
			// 	return;
			// }else{		
			// 	self::may_copy_free_version_settings();
			// }
		}
		
		/**
		 * Function for ccopy free version settings.
		 */
		public static function may_copy_free_version_settings() {
			$copied = false;
			$field_set_key = 'thmaf_general_settings';
			$field_set = get_option($field_set_key);

			if($field_set && is_array($field_set)) {
				$result = update_option(THWMA_Utils::OPTION_KEY_THWMA_SETTINGS,$field_set);	
				if($result) {
					$copied = true;
					delete_option($field_set_key);
				}
			}
			if(!$copied) {
				$admin_utils->prepare_sections_and_fields();
			}
		}
	}
endif;