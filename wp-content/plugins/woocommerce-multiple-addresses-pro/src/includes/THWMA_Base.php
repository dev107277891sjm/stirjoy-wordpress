<?php
/**
 * The file that defines the core plugin class.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-multiple-addresses-pro
 * @subpackage woocommerce-multiple-addresses-pro/src/includes
 */

namespace Themehigh\WoocommerceMultipleAddressesPro\includes;

use \Themehigh\WoocommerceMultipleAddressesPro\admin\THWMA_Admin;
use \Themehigh\WoocommerceMultipleAddressesPro\includes\THWMA_Loader;
use \Themehigh\WoocommerceMultipleAddressesPro\includes\THWMA_i18n;
use Themehigh\WoocommerceMultipleAddressesPro\thpublic\THWMA_Public;
use \Themehigh\WoocommerceMultipleAddressesPro\includes\utils\THWMA_Utils;
use \Themehigh\WoocommerceMultipleAddressesPro\includes\utils\THWMA_Api;

if(!defined('WPINC')) {	
	die; 
}

if(!class_exists('THWMA_Base')) :

	/**
     * Main class of schedule delivery for woocommerce plugin.
     */ 
	class THWMA_Base {
		/**
		 * The loader that's responsible for maintaining and registering all hooks that power
		 * the plugin.
		 *
		 * @access   protected
		 * @var      $loader    Maintains and registers all hooks for the plugin.
		 */
		protected $loader;

		/**
		 * The unique identifier of this plugin.
		 *
		 * @access   protected
		 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
		 */
		protected $plugin_name;

		/**
		 * The current version of the plugin.
		 *
		 * @access   protected
		 * @var      string    $version    The current version of the plugin.
		 */
		protected $version;
		
		/**
		 * Define the core functionality of the plugin.
		 *
		 * Set the plugin name and the plugin version that can be used throughout the plugin.
		 * Load the dependencies, define the locale, and set the hooks for the admin area and
		 * the public-facing side of the site.
		 */
		public function register() {
			if (defined('THWMA_VERSION')) {
				$this->version = THWMA_VERSION;
			} else {
				$this->version = '1.0.0';
			}
			$this->plugin_name = 'woocommerce-multiple-addresses-pro';
			
			$this->load_dependencies();
			$this->set_locale();
			add_action( 'rest_api_init', array($this, 'load_thwma_api') );
		}
		/**
		 * Load the required dependencies for this plugin.
		 *
		 * Include the following files that make up the plugin:
		 *
		 * - THWMA_Loader. Orchestrates the hooks of the plugin.
		 *
		 * Create an instance of the loader which will be used to register the hooks
		 * with WordPress.
		 *
		 * @access   private
		 */
		private function load_dependencies() {

			$this->loader = new THWMA_Loader();
		}

		/**
		 * Define the locale for this plugin for internationalization.
		 *
		 * Uses the THWMA_i18n class in order to set the domain and to register the hook
		 * with WordPress.
		 *
		 * @access   private
		 */
		private function set_locale() {
			$plugin_i18n = new THWMA_i18n(self::get_plugin_name());
			$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain',);
		}
		
		private function init_auto_updater() {
			if(!class_exists('THWMA_Auto_Update_License')) {
				$api_url = 'https://themehigh.com/';
				require_once plugin_dir_path(dirname(__FILE__)) . 'class-thwma-auto-update-license.php';
				THWMA_Auto_Update_License::instance(__FILE__, THWMA_SOFTWARE_TITLE, THWMA_VERSION, 'plugin', $api_url, THWMA_i18n::TEXT_DOMAIN);
			}
		}

		public function load_thwma_api() {
			$api_controller = new THWMA_Api();
			$api_controller->register_routes();
		}


		/**
		 * Register all of the hooks related to the public-facing functionality
		 * of the plugin.
		 *
		 * @access   private
		 */
		private function define_public_hooks() {
			$plugin_public = new THWMA_Public($this->get_plugin_name(), $this->get_version());
			

			$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles_and_scripts');
			$this->loader->add_filter('woocommerce_locate_template', $plugin_public, 'address_template',10,3);
			//$this->loader->add_filter('woocommerce_my_account_edit_address_title', $plugin_public, 'custom_address_title',10,2);
			
		}

		/**
		 * Run the loader to execute all of the hooks with WordPress.
		 */
		public function run() {
			$this->loader->run();
		}

		/**
		 * The name of the plugin used to uniquely identify it within the context of
		 * WordPress and to define internationalization functionality.
		 *
		 * @return    string    The name of the plugin.
		 */
		public function get_plugin_name() {
			return $this->plugin_name;
		}

		/**
		 * The reference to the class that orchestrates the hooks with the plugin.
		 *
		 * @return    Loader Object    Orchestrates the hooks of the plugin.
		 */
		public function get_loader() {
			return $this->loader;
		}

		/**
		 * Retrieve the version number of the plugin.
		 *
		 * @return    string    The version number of the plugin.
		 */
		public function get_version() {
			return $this->version;
		}
	}

endif;