<?php
/**
 * Define the internationalization functionality.
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

if(!class_exists('THWMA_i18n')) :

	/**
     * The translation class.
     */
	class THWMA_i18n {
		const TEXT_DOMAIN = 'woocommerce-multiple-addresses-pro';
		const ICL_CONTEXT = 'woocommerce-multiple-addresses-pro';
		const ICL_NAME_PREFIX = "WMAP";
		
		/**
		 * Load the plugin text domain for translation.
		 */
		public function load_plugin_textdomain() {
			$locale = apply_filters('plugin_locale', get_locale(), self::TEXT_DOMAIN);			
			load_textdomain(self::TEXT_DOMAIN, WP_LANG_DIR.'/'.self::TEXT_DOMAIN.'/'.self::TEXT_DOMAIN.'-'.$locale.'.mo');
			load_plugin_textdomain(self::TEXT_DOMAIN, false, dirname(dirname(plugin_basename(__FILE__))) . '/languages/');
		}
		
		/**
		 * Funciton for get locale code.
		 *
		 * @return string
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
		 * Funciton for __t text.
		 *
		 * @param string $text The translation text
		 *
		 * @return string
		 */
		public static function __t($text) {
			if(!empty($text)){	
				$otext = $text;						
				$text = __($text, self::TEXT_DOMAIN);	
				if($text === $otext){
					$text = self::icl_t($text);
					if($text === $otext){	
						$text = __($text, 'woocommerce');
					}
				}
			}
			return $text;
		}
		
		/**
		 * Funciton for _et text.
		 *
		 * @param string $text The translation text
		 *
		 * @return string
		 */
		public static function _et($text){
			if(!empty($text)){	
				$otext = $text;						
				$text = __($text, self::TEXT_DOMAIN);	
				if($text === $otext){
					$text = self::icl_t($text);
					if($text === $otext){		
						$text = __($text, 'woocommerce');
					}
				}
			}
			echo $text;
		}

		/**
		 * Funciton for t text.
		 *
		 * @param string $text The translation text
		 *
		 * @return string
		 */
		public static function t($text) {
			if(!empty($text)){	
				$otext = $text;						
				$text = __($text, self::TEXT_DOMAIN);	
				if($text === $otext) {
					$text = self::icl_t($text);
					if($text === $otext) {	
						$text = __($text, 'woocommerce');
					}
				}
			}
			return $text;
		}
		
		/**
		 * Funciton for et text.
		 *
		 * @param string $text The translation text
		 *
		 * @return string
		 */
		public static function et($text) {
			if(!empty($text)) {	
				$otext = $text;						
				$text = __($text, self::TEXT_DOMAIN);	
				if($text === $otext) {
					$text = self::icl_t($text);
					if($text === $otext) {		
						$text = __($text, 'woocommerce');
					}
				}
			}
			echo $text;
		}
		
		/**
		 * Funciton for esc_attr__t text.
		 *
		 * @param string $text The translation text
		 *
		 * @return string
		 */
		public static function esc_attr__t($text) {
			if(!empty($text)) {	
				$otext = $text;						
				$text = esc_attr__($text, self::TEXT_DOMAIN);	
				if($text === $otext) {
					$text = self::icl_t($text);	
					if($text === $otext) {	
						$text = esc_attr__($text, 'woocommerce');
					}
				}
			}
			return $text;
		}
		
		/**
		 * Funciton for esc_html__t text.
		 *
		 * @param string $text The translation text
		 *
		 * @return string
		 */
		public static function esc_html__t($text) {
			if(!empty($text)) {	
				$otext = $text;						
				$text = esc_html__($text, self::TEXT_DOMAIN);	
				if($text === $otext) {
					$text = self::icl_t($text);	
					if($text === $otext) {	
						$text = esc_html__($text, 'woocommerce');
					}
				}
			}
			return $text;
		}
		
		/**
		 * Funciton for wpml register string(WPML SUPPORT).
		 *
		 * @param string $name The translation name
		 * @param string $value The translation value
		 */
		public static function wpml_register_string($name, $value ) {
			$name = self::ICL_NAME_PREFIX." - ".$value;
			
			if(function_exists('icl_register_string')) {
				icl_register_string(self::ICL_CONTEXT, $name, $value);
			}
		}
		
		/**
		 * Funciton for wpml unregister string(WPML SUPPORT).
		 *
		 * @param string $name The translation name
		 */
		public static function wpml_unregister_string($name) {
			if(function_exists('icl_unregister_string')) {
				icl_unregister_string(self::ICL_CONTEXT, $name);
			}
		}
		
		/**
		 * Funciton for icl_t.
		 *
		 * @param string $value The translation value
		 *
		 * @return string
		 */
		public static function icl_t($value){
	        $name = self::ICL_NAME_PREFIX." - ".$value;
			
			if(function_exists('icl_t')){
				$value = icl_t(self::ICL_CONTEXT, $name, $value);
			}
			return $value;
		}
	}
endif;