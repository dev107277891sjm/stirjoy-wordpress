<?php
/**
 * Plugin Name:       Multiple Shipping Addresses for WooCommerce (Address Book) - PRO
 * Plugin URI:        https://themehigh.com/product/woocommerce-multiple-addresses-pro/
 * Description:       Lets you save multiple addresses, as well as ship different products to as many multiple addresses in a single purchase.
 * Version:           2.2.2
 * Author:            ThemeHigh
 * Author URI:        https://themehigh.com/
 *
 * Text Domain:       woocommerce-multiple-addresses-pro
 * Domain Path:       /languages
 * Update URI:          https://www.themehigh.com/product/woocommerce-multiple-addresses-pro/
 *
 * WC requires at least: 7.6
 * WC tested up to: 9.7
 */

if(!defined('WPINC')){	die; }

if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    include_once dirname(__FILE__) . '/vendor/autoload.php';
    // include_once dirname(__FILE__) . '/vendor/woocommerce/woocommerce';
    include_once dirname(__FILE__) . '/vendor/wp_namespace_autoloader.php';
    $autoloader = new WP_Namespace_Autoloader( array(    
        'directory'          => __DIR__, 
        'namespace_prefix'   => 'THWMA',
        'classes_dir'        => 'src',
    ) );
    $autoloader->init();
}

if (!function_exists('is_woocommerce_active')){
	function is_woocommerce_active(){
	    $active_plugins = (array) get_option('active_plugins', array());
	    if(is_multisite()){
		   $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
	    }
	    return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins);
	}
}

if(is_woocommerce_active()) {
	define('THWMA_VERSION', '2.2.2');
	!defined('THWMA_SOFTWARE_TITLE') && define('THWMA_SOFTWARE_TITLE', 'WooCommerce Multiple Addresses');
	!defined('THWMA_FILE') && define('THWMA_FILE', __FILE__);
	!defined('THWMA_PATH') && define('THWMA_PATH', plugin_dir_path( __FILE__ ));
	!defined('THWMA_URL') && define('THWMA_URL', plugins_url( '/', __FILE__ ));
	!defined('THWMA_BASE_NAME') && define('THWMA_BASE_NAME', plugin_basename( __FILE__ ));


	// Update mechanisam related constants
	!defined('THWMA_UPDATE_API_URL') && define('THWMA_UPDATE_API_URL', 'https://www.themehigh.com');
	!defined('THWMA_PRODUCT_ID') && define('THWMA_PRODUCT_ID', 26);

	// !Warning: Item identifier is an internal identifier for product. We may update product name later in store & plugin. But item identifier must be same all time after releasing the plugin. It used to save details on customer database, cron event & generate license form short code.
	!defined('THWMA_ITEM_IDENTIFIER') && define('THWMA_ITEM_IDENTIFIER', 'Multiple Addresses for WooCommerce');
	!defined('THWMA_LICENSE_PAGE_URL') && define('THWMA_LICENSE_PAGE_URL', admin_url('admin.php?page=th_multiple_addresses_pro&tab=license_settings'));
	/**
	 * The code that runs during plugin activation.
	 */
	function activate_thwma() {
		Themehigh\WoocommerceMultipleAddressesPro\includes\THWMA_Activator::activate();
	}
	
	/**
	 * The code that runs during plugin deactivation.
	 */
	function deactivate_thwma() {
		Themehigh\WoocommerceMultipleAddressesPro\includes\THWMA_Deactivator::deactivate();
		
	}
	
	register_activation_hook( __FILE__, 'activate_thwma' );
	register_deactivation_hook( __FILE__, 'deactivate_thwma' );
  

	if(!class_exists('THWMA_EDD_Updater_Helper') ) {
		require_once( plugin_dir_path( __FILE__ ) . 'class-thwma-edd-updater-helper.php' );
	}

	if (!class_exists( 'THWMA_EDD_Updater' ) ) {
		require_once( plugin_dir_path( __FILE__ ) . '/class-thwma-edd-updater.php' );
	}

	/**
	 * Initialize plugin updater & updater helper.
	 *
	 * @return void
	 */
	function init_thwma_updater(){

		$helper_data = array(
			'api_url' => THWMA_UPDATE_API_URL,
			'item_id' => THWMA_PRODUCT_ID, 
			'item_identifier' => THWMA_ITEM_IDENTIFIER,
			'license_page_url' => THWMA_LICENSE_PAGE_URL,
		);
		// Setup the updater helper.
		$thwma_updater_helper = new THWMA_EDD_Updater_Helper( __FILE__, $helper_data );

		/**
		 * Initialize the updater. Hooked into `init` to work with the wp_version_check cron job, which allows auto-updates.
		*/
		// To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
		$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
		if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
			return;
		}

		// retrieve our license key from the DB.
		$license_data = $thwma_updater_helper->get_license_data();
		$license_key = isset($license_data['license_key']) ? $license_data['license_key'] : false;

		// setup the updater
		$edd_updater = new THWMA_EDD_Updater(
			THWMA_UPDATE_API_URL,
			__FILE__,
			array(
				'version' => THWMA_VERSION,
				'license' => $license_key,
				'item_id' => THWMA_PRODUCT_ID,
				'author'  => 'ThemeHigh',
				'beta'    => false,
			)
		);
	}
	add_action( 'init', 'init_thwma_updater', 10 );


	if (class_exists('Themehigh\WoocommerceMultipleAddressesPro\\init')) {
        Themehigh\WoocommerceMultipleAddressesPro\Init::register_services();
    }


	add_action( 'before_woocommerce_init', 'thwma_before_woocommerce_init_hpos' ) ;
	function thwma_before_woocommerce_init_hpos() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
}