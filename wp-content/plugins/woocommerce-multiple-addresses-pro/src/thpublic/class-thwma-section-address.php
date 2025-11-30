<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://themehigh.com
  * @package    woocommerce-multiple-addresses-pro
 * @subpackage woocommerce-multiple-addresses-pro/public
 */

if(!defined('WPINC')) {	
	die; 
}

if(!class_exists('THWMA_SECTION_ADDRESS')) :

	/**
     * Section address class.
     */ 
	class THWMA_SECTION_ADDRESS extends THWMA_Public {

		/**
         * Constructor.
         *
         * @param string $plugin_name The plugin name
         * @param string $version The plugin version number
         */
		public function __construct( $plugin_name, $version ) {
			parent::__construct($plugin_name, $version);	
			add_action('after_setup_theme', array($this, 'define_public_hooks'));
		}
		
		public function define_public_hooks() {
		
		}

		// Custom sections-start.
}
// new THWMA_SECTION_ADDRESS();
endif;