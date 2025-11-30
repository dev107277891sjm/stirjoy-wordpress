<?php

/**
 *
 * @link              https://ultiwp.com
 * @since             1.0.0
 * @package           Hs_Doko
 *
 * @wordpress-plugin
 * Plugin Name:       Doko Bundle Builder : The Ultimate dynamic bundle builder for WooCommerce
 * Plugin URI:        https://ultiwp.com/plugins/doko
 * Description:       Increase sales by letting your customers create custom bundles.
 * Version:           1.9
 * Author:            UltiWP
 * Author URI:        https://ultiwp.com
 * License:           GPL-2.0
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       doko-bundle-builder
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Requires Plugins: woocommerce
 * WC requires at least: 9.0.0
 * 
  */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'DOKO_VERSION', '1.9' );
define( 'DOKO_DIR_PATH', plugin_dir_path( __FILE__ ) );

add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

require plugin_dir_path( __FILE__ ) . 'core/premium-loading.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in core/class-hs-doko-activator.php
 */
function doko_run_activation() {
	require_once plugin_dir_path( __FILE__ ) . 'core/class-hs-doko-activator.php';
	HS\Doko\Hs_Doko_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in core/class-hs-doko-deactivator.php
 */
function doko_run_deactivation() {
	require_once plugin_dir_path( __FILE__ ) . 'core/class-hs-doko-deactivator.php';
	HS\Doko\Hs_Doko_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'doko_run_activation' );
register_deactivation_hook( __FILE__, 'doko_run_deactivation' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'core/class-hs-doko.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function doko_run() {

	$plugin = new HS\Doko\Hs_Doko();
	$plugin->run();
}
doko_run();
