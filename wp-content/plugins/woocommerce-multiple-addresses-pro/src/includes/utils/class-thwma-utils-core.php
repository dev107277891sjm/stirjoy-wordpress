<?php
/**
 * The core utility functionality for the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-multiple-addresses-pro
 * @subpackage woocommerce-multiple-addresses-pro/src/includes/utils
 */
if(!defined('WPINC')) {	
	die; 
}

if(!class_exists('THWMA_Utils_Core')) :

	/**
     * Utils core class.
     */
	class THWMA_Utils_Core {
		static $PATTERN = array(			
				'/d/', '/j/', '/l/', '/z/', '/S/', //day (day of the month, 3 letter name of the day, full name of the day, day of the year,)			
				'/F/', '/M/', '/n/', '/m/', //month (Month name full, Month name short, numeric month no leading zeros, numeric month leading zeros)			
				'/Y/', '/y/' //year (full numeric year, numeric year: 2 digit)
			);
			
		static $REPLACE = array(
				'dd','d','DD','o','',
				'MM','M','m','mm',
				'yy','y'
			);
		
		/**
         * Check the given value is blank.
         *
         * @param string $value The given value
         *
         * @return string/int
         */
		public static function is_blank($value) {
			return empty($value) && !is_numeric($value);
		}
		
		/**
         * Check the array subset.
         *
         * @param string $arr1 The array one
         * @param string $arr2 The array two
         *
         * @return string/int
         */
		public static function is_subset_of($arr1, $arr2) {
			if(!empty($arr2)) {
				if(is_array($arr1) && is_array($arr2)) {
					foreach($arr2 as $value) {
						if(!in_array($value, $arr1)) {
							return false;
						}
					}
				}
			}
			return true;
		}
		
		/**
         * Function for get local code.
         *
         * @return string/int
         */
		public static function get_locale_code() {
			$locale_code = '';
			$locale = get_locale();
			if(!empty($locale)) {
				$locale_arr = explode("_", $locale);
				if(!empty($locale_arr) && is_array($locale_arr)) {
					$locale_code = $locale_arr[0];
				}
			}		
			return empty($locale_code) ? 'en' : $locale_code;
		}
			
		/**
         * Function for get the user roles.
         *
         * string $user The user data
         *
         * @return array
         */
		public static function get_user_roles($user = false) {
			$user = $user ? new WP_User($user) : wp_get_current_user();			
			if(!($user instanceof WP_User))
			   return false;			   
			$roles = $user->roles;
			return $roles;
		}
		
		/**
         * Function for get jquery date format.
         *
         * string $woo_date_format The date format
         *
         * @return void
         */
		public static function get_jquery_date_format($woo_date_format) {				
			$woo_date_format = !empty($woo_date_format) ? $woo_date_format : wc_date_format();
			return preg_replace(self::$PATTERN, self::$REPLACE, $woo_date_format);	
		}
		
		/**
         * Function for convert css class string.
         *
         * string $cssclass The css class info
         *
         * @return string
         */
		public static function convert_cssclass_string($cssclass) {
			if(!is_array($cssclass)) {
				$cssclass = array_map('trim', explode(',', $cssclass));
			}			
			if(is_array($cssclass)) {
				$cssclass = implode(" ",$cssclass);
			}
			return $cssclass;
		}
		
		/**
         * Function for check active woocommerce version.
         *
         * string $version The version info
         *
         * @return void
         */
		public static function woo_version_check($version = '3.0') {
		  	if(function_exists('is_woocommerce_active') && is_woocommerce_active()) {
				global $woocommerce;
				if(version_compare($woocommerce->version, $version, ">=")) {
			  		return true;
				}
		  	}
		  	return false;
		}
	}
endif;