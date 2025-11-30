<?php
/**
 * The admin license settings page functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-multiple-addresses-pro
 * @subpackage woocommerce-multiple-addresses-pro/src/admin
 */

namespace Themehigh\WoocommerceMultipleAddressesPro\admin;

if(!defined('WPINC')) {	
	die; 
}

if(!class_exists('THWMA_Admin_Settings_License')) :

	/**
     * The admin license settings class.
     */ 
	class THWMA_Admin_Settings_License extends THWMA_Admin_Settings {
		protected static $_instance = null;		
		public $ame_data_key;
		public $ame_deactivate_checkbox;
		public $ame_activation_tab_key;
		public $ame_deactivation_tab_key;
		public $data_prefix;
		
		/**
         * constructor.
         */
		public function __construct() {
			parent::__construct('license_settings');
			
			$this->page_id = 'license_settings';
			$this->data_prefix = str_ireplace(array(' ', '_', '&', '?'), '_', strtolower(THWMA_SOFTWARE_TITLE));
			$this->data_prefix = str_ireplace('woocommerce', 'th', $this->data_prefix);
			$this->ame_data_key             = $this->data_prefix . '_data';
			$this->ame_deactivate_checkbox  = $this->data_prefix . '_deactivate_checkbox';
			$this->ame_activation_tab_key   = $this->data_prefix . '_license_activate';
			$this->ame_deactivation_tab_key = $this->data_prefix . '_license_deactivate';		
		}
		
		/**
         * Instance.
         */
		public static function instance() {
			if(is_null(self::$_instance)) {
				self::$_instance = new self();
			}
			return self::$_instance;
		} 
		
		/**
         * Function for call a render page.
         */
		public function render_page() {
			settings_errors();
			$this->render_tabs();
			$this->render_content();
		}

		/**
         * Function for call the render content.
         */
		private function render_content() {
			echo do_shortcode('[th_multiple_addresses_for_woocommerce_license_form]');
		}
		
		/**
         * Function for set old render content.
         */
		private function render_content_old() { ?>            
	        <div class="thpladmin-license-settings" style="padding-left: 30px;"> 
				<p class="info-text">
					<?php esc_html_e('You need to have a valid License in order to get upgrades or support for this plugin.', 'woocommerce-multiple-addresses-pro') ?>
				</p>       
				<form action="options.php" method='post'>
					<div class="form-table thpladmin-form-table thpladmin-license-settings-grid">
						<?php settings_fields($this->ame_data_key);
						do_settings_sections($this->ame_activation_tab_key);
						submit_button(esc_html__('Save Changes', 'woocommerce-multiple-addresses-pro')); ?>
					</div>
	            </form>
				<form action="options.php" method='post'>
					<div class="form-table thpladmin-form-table thpladmin-license-settings-grid">
						<?php settings_fields($this->ame_deactivate_checkbox);
						do_settings_sections($this->ame_deactivation_tab_key);
						submit_button(esc_html__('Save Changes', 'woocommerce-multiple-addresses-pro')); ?>
					</div>
				</form>
	    	</div>       
	    <?php }
	}
endif;