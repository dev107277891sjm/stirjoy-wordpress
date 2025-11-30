<?php
/**
 * Fired during plugin deactivation.
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

if(!class_exists('THWMA_Deactivator')) :

	/**
     * The deactivator class.
     */
	class THWMA_Deactivator {

		/**
	     * The deactivat function.
	     */
		public static function deactivate() {

		}
	}
endif;