<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wpswings.com/
 * @since             1.0.0
 * @package           Woocommerce_Subscriptions_Pro
 *
 * @wordpress-plugin
 * Plugin Name:       Subscriptions For WooCommerce Pro
 * Plugin URI:        https://wpswings.com/product/subscriptions-for-woocommerce-pro/?utm_source=wpswings-subs-pro&utm_medium=subs-pro-backend&utm_campaign=premium-plugin
 * Description:       <code><strong>Subscriptions for WooCommerce Pro</strong></code> plugin allows to handle subscription-based items and services on your e-store and generate recurring income. <a target="_blank" href="https://wpswings.com/woocommerce-plugins/?utm_source=wpswings-subs-shop&utm_medium=subs-pro-backend&utm_campaign=shop-page">Elevate your e-commerce store by exploring more on WP Swings</a>
 * Version:           2.6.3
 * Author:            WP Swings
 * Author URI:        https://wpswings.com/?utm_source=wpswings-subs-official&utm_medium=subs-pro-backend&utm_campaign=official
 * Text Domain:       woocommerce-subscriptions-pro
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 *
 * Requires at least:        6.7.0
 * Tested up to:             6.8.3
 * WC requires at least:     6.5.0
 * WC tested up to:          10.2.2
 *
 * License:           Software License Agreement
 * License URI:       https://wpswings.com/license-agreement.txt
 * Requires Plugins: woocommerce
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
use Automattic\WooCommerce\Utilities\OrderUtil;
// To Activate plugin only when Subscription for WooCommerce is active.
$activated = false;
// Check if Subscription for WooCommerce is active.
require_once ABSPATH . 'wp-admin/includes/plugin.php';
if ( is_plugin_active( 'subscriptions-for-woocommerce/subscriptions-for-woocommerce.php' ) ) {

	$activated = true;
}
$installed_plugins = get_plugins();
if ( array_key_exists( 'subscriptions-for-woocommerce/subscriptions-for-woocommerce.php', $installed_plugins ) ) {
	$base_plugin = $installed_plugins['subscriptions-for-woocommerce/subscriptions-for-woocommerce.php'];

	if ( $activated && $base_plugin['Version'] < '1.0.1' ) {
		$activated = false;
		add_action( 'admin_init', 'wps_wsp_activation_failure_for_low_version' );
		/**
		 * Deactivate this plugin.
		 *
		 * @name wps_wsp_activation_failure_for_low_version.
		 * @since 1.0.0
		 */
		function wps_wsp_activation_failure_for_low_version() {

			add_action( 'admin_notices', 'wps_wsp_activation_failure_admin_notice_low_version' );
		}

		// Add admin error notice.

		/**
		 * This function is used to display admin error notice when WooCommerce is not active.
		 *
		 * @name wps_wsp_activation_failure_admin_notice
		 * @since 1.0.0
		 */
		function wps_wsp_activation_failure_admin_notice_low_version() {

			// to hide Plugin activated notice.
			unset( $_GET['activate'] );

			deactivate_plugins( plugin_basename( __FILE__ ) );
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php esc_html_e( 'WooCommerce Subscriptions PRO Require latest Subscriptions For Woocommerce', 'woocommerce-subscriptions-pro' ); ?></p>
			</div>
			<?php
		}
	}
}

$wsp_old_org_present   = false;

if ( array_key_exists( 'subscriptions-for-woocommerce/subscriptions-for-woocommerce.php', $installed_plugins ) ) {
	$base_plugin = $installed_plugins['subscriptions-for-woocommerce/subscriptions-for-woocommerce.php'];
	if ( version_compare( $base_plugin['Version'], '1.4.0', '<' ) ) {
		$wsp_old_org_present = true;
	}
}
if ( $activated && is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

	// Migrate license keys now.
	$wps_wsp_pro_license_key = get_option( 'wps_wsp_license_key', '' );
	$mwb_wsp_pro_license_key = get_option( 'mwb_wsp_license_key', '' );
	$thirty_days               = get_option( 'mwb_wsp_lcns_thirty_days', 0 );
	$license_check             = get_option( 'mwb_wsp_license_key_status', false );

	if ( ! empty( $mwb_wsp_pro_license_key ) && empty( $wps_wsp_pro_license_key ) ) {
		update_option( 'wps_wsp_license_key', $mwb_mfw_pro_license_key );
		update_option( 'wps_wsp_lcns_thirty_days', $thirty_days );
		update_option( 'wps_wsp_license_key_status', $license_check );
		$wps_wsp_pro_license_key = get_option( 'wps_wsp_license_key', '' );
	}

	if ( $wsp_old_org_present ) {
		// Try org update to minimum.
		add_action( 'admin_notices', 'wps_wsp_upgrade_old_plugin' );
		/**
		 * Try org update to minimum.
		 */
		function wps_wsp_upgrade_old_plugin() {
			require_once 'wps-sfw-auto-download-free.php';
			wps_sfw_org_replace_plugin();
		}

		/**
		 * Migration to new domain notice.
		 *
		 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
		 * @param array  $plugin_data An array of plugin data.
		 * @param string $status Status filter currently applied to the plugin list.
		 */
		function wps_sfw_upgrade_notice( $plugin_file, $plugin_data, $status ) {

			?>
		<tr class="plugin-update-tr active notice-warning notice-alt">
			<td colspan="4" class="plugin-update colspanchange">
				<div class="notice notice-error inline update-message notice-alt">
					<p class='wps-notice-title wps-notice-section'>
						<?php esc_html_e( 'Heads up, We highly recommend Also Update Latest Org Plugin. The latest update includes some substantial changes across different areas of the plugin.', 'woocommerce-subscriptions-pro' ); ?>
					</p>
				</div>
			</td>
		</tr>
		<style>
			.wps-notice-section > p:before {
				content: none;
			}
		</style>
			<?php
		}
		add_action( 'after_plugin_row_subscriptions-for-woocommerce/subscriptions-for-woocommerce.php', 'wps_sfw_upgrade_notice', 0, 3 );
		add_action( 'after_plugin_row_' . plugin_basename( __FILE__ ), 'wps_sfw_upgrade_notice', 0, 3 );
		$temp = get_option( 'wps_sfw_update_done_check', 'not done' );
		if ( 'not done' == $temp ) {
			$general_settings_url = admin_url( 'plugins.php' );
			header( 'Location: ' . $general_settings_url );
			update_option( 'wps_sfw_update_done_check', 'done' );
		}
	}
	/**
	 * Define plugin constants.
	 *
	 * @since 1.0.0
	 */
	function define_woocommerce_subscriptions_pro_constants() {

		woocommerce_subscriptions_pro_constants( 'WOOCOMMERCE_SUBSCRIPTIONS_PRO_VERSION', '2.6.3' );
		woocommerce_subscriptions_pro_constants( 'WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_PATH', plugin_dir_path( __FILE__ ) );
		woocommerce_subscriptions_pro_constants( 'WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL', plugin_dir_url( __FILE__ ) );

		woocommerce_subscriptions_pro_constants( 'WPS_PAYPAL_SUBSCRIPTION_INTEGRATION_SANDBOX_URL', 'https://api-m.sandbox.paypal.com' );
		woocommerce_subscriptions_pro_constants( 'WPS_PAYPAL_SUBSCRIPTION_INTEGRATION_LIVE_URL', 'https://api-m.paypal.com' );
	}


	/**
	 * Define plugin constants.
	 *
	 * @since 1.0.0
	 */
	function wps_define_woocommerce_subscriptions_pro_update_and_license_constants() {

		woocommerce_subscriptions_pro_constants( 'WOOCOMMERCE_SUBSCRIPTIONS_PRO_SERVER_URL', 'https://wpswings.com' );
		woocommerce_subscriptions_pro_constants( 'WOOCOMMERCE_SUBSCRIPTIONS_PRO_ITEM_REFERENCE', 'WooCommerce Subscriptions PRO' );
		woocommerce_subscriptions_pro_constants( 'WOOCOMMERCE_SUBSCRIPTIONS_PRO_SPECIAL_SECRET_KEY', '59f32ad2f20102.74284991' );
		woocommerce_subscriptions_pro_constants( 'WOOCOMMERCE_SUBSCRIPTIONS_PRO_PRODUCT_REFERENCE', 'WPSPK-67569' );

		$wps_wsp_license_key = get_option( 'wps_wsp_license_key', '' );
		define( 'WOOCOMMERCE_SUBSCRIPTIONS_PRO_LICENSE_KEY', $wps_wsp_license_key );
		define( 'WOOCOMMERCE_SUBSCRIPTIONS_PRO_BASE_FILE', __FILE__ );

		require_once 'class-woocommerce-subscriptions-pro-update.php';
	}


	/**
	 * Callable function for defining plugin constants.
	 *
	 * @param   String $key    Key for contant.
	 * @param   String $value   value for contant.
	 * @since             1.0.0
	 */
	function woocommerce_subscriptions_pro_constants( $key, $value ) {

		if ( ! defined( $key ) ) {

			define( $key, $value );
		}
	}
	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-woocommerce-subscriptions-pro-activator.php
	 *
	 * @param String $network_wide as network wide.
	 */
	function activate_woocommerce_subscriptions_pro( $network_wide ) {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-subscriptions-pro-activator.php';
		Woocommerce_Subscriptions_Pro_Activator::woocommerce_subscriptions_pro_activate( $network_wide );
		$wps_wsp_active_plugin = get_option( 'wps_all_plugins_active', false );
		if ( is_array( $wps_wsp_active_plugin ) && ! empty( $wps_wsp_active_plugin ) ) {
			$wps_wsp_active_plugin['woocommerce-subscriptions-pro'] = array(
				'plugin_name' => __( 'WooCommerce Subscriptions PRO', 'woocommerce-subscriptions-pro' ),
				'active' => '1',
			);
		} else {
			$wps_wsp_active_plugin = array();
			$wps_wsp_active_plugin['woocommerce-subscriptions-pro'] = array(
				'plugin_name' => __( 'WooCommerce Subscriptions PRO', 'woocommerce-subscriptions-pro' ),
				'active' => '1',
			);
		}
		update_option( 'wps_all_plugins_active', $wps_wsp_active_plugin );
	}
	add_action(
		'before_woocommerce_init',
		function () {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
			}
		}
	);

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-woocommerce-subscriptions-pro-deactivator.php
	 *
	 * @param String $network_wide as network wide.
	 */
	function deactivate_woocommerce_subscriptions_pro( $network_wide ) {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-subscriptions-pro-deactivator.php';
		Woocommerce_Subscriptions_Pro_Deactivator::woocommerce_subscriptions_pro_deactivate( $network_wide );
		$wps_wsp_deactive_plugin = get_option( 'wps_all_plugins_active', false );
		if ( is_array( $wps_wsp_deactive_plugin ) && ! empty( $wps_wsp_deactive_plugin ) ) {
			foreach ( $wps_wsp_deactive_plugin as $wps_wsp_deactive_key => $wps_wsp_deactive ) {
				if ( 'woocommerce-subscriptions-pro' === $wps_wsp_deactive_key ) {
					$wps_wsp_deactive_plugin[ $wps_wsp_deactive_key ]['active'] = '0';
				}
			}
		}
		update_option( 'wps_all_plugins_active', $wps_wsp_deactive_plugin );
	}

	register_activation_hook( __FILE__, 'activate_woocommerce_subscriptions_pro' );
	register_deactivation_hook( __FILE__, 'deactivate_woocommerce_subscriptions_pro' );

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-subscriptions-pro.php';


	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_woocommerce_subscriptions_pro() {
		define_woocommerce_subscriptions_pro_constants();
		wps_define_woocommerce_subscriptions_pro_update_and_license_constants();
		$wsp_wsp_plugin_standard = new Woocommerce_Subscriptions_Pro();
		$wsp_wsp_plugin_standard->wsp_run();
		$GLOBALS['wsp_wps_wsp_obj'] = $wsp_wsp_plugin_standard;
	}
	run_woocommerce_subscriptions_pro();


	// Add settings link on plugin page.
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woocommerce_subscriptions_pro_settings_link' );

	/**
	 * Settings link.
	 *
	 * @since    1.0.0
	 * @param   Array $links    Settings link array.
	 */
	function woocommerce_subscriptions_pro_settings_link( $links ) {

		$my_link = array(
			'<a href="' . admin_url( 'admin.php?page=subscriptions_for_woocommerce_menu' ) . '">' . __( 'Settings', 'woocommerce-subscriptions-pro' ) . '</a>',
		);
		return array_merge( $my_link, $links );
	}


	/**
	 * Adding custom setting links at the plugin activation list.
	 *
	 * @param array  $links_array array containing the links to plugin.
	 * @param string $plugin_file_name plugin file name.
	 * @return array
	 */
	function woocommerce_subscriptions_pro_custom_settings_at_plugin_tab( $links_array, $plugin_file_name ) {
		if ( strpos( $plugin_file_name, basename( __FILE__ ) ) ) {
			$links_array[] = '<a href="https://demo.wpswings.com/subscriptions-for-woocommerce-pro/?utm_source=wpswings-subs-demo&utm_medium=subs-pro-backend&utm_campaign=demo" target="_blank"><img src="' . esc_html( WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL ) . 'admin/image/Demo.svg" class="wps-info-img" alt="Demo image">' . __( 'Demo', 'woocommerce-subscriptions-pro' ) . '</a>';
			$links_array[] = '<a href="https://docs.wpswings.com/subscriptions-for-woocommerce/?utm_source=wpswings-subs-doc&utm_medium=subs-pro-backend&utm_campaign=subscription-doc" target="_blank"><img src="' . esc_html( WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL ) . 'admin/image/Documentation.svg" class="wps-info-img" alt="documentation image">' . __( 'Documentation', 'woocommerce-subscriptions-pro' ) . '</a>';
			$links_array[] = '<a href="https://www.youtube.com/watch?v=IUn8EhnrjQw" target="_blank"><img src="' . esc_html( WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL ) . 'admin/image/YouTube_32px.svg" class="wps-info-img" alt="video image">' . __( 'Video', 'woocommerce-subscriptions-pro' ) . '</a>';
			$links_array[] = '<a href="https://wpswings.com/submit-query/?utm_source=wpswings-subs-support&utm_medium=subs-pro-backend&utm_campaign=support" target="_blank"><img src="' . esc_html( WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL ) . 'admin/image/Support.svg" class="wps-info-img" alt="support image">' . __( 'Support', 'woocommerce-subscriptions-pro' ) . '</a>';
			$links_array[] = '<a href="https://wpswings.com/submit-query/?utm_source=wpswings-subs-support&utm_medium=subs-pro-backend&utm_campaign=support" target="_blank"><img src="' . esc_html( WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL ) . 'admin/image/Services.svg" class="wps-info-img" alt="services image">' . __( 'Services', 'woocommerce-subscriptions-pro' ) . '</a>';
		}
		return $links_array;
	}
	add_filter( 'plugin_row_meta', 'woocommerce_subscriptions_pro_custom_settings_at_plugin_tab', 10, 2 );

	/**
	 *
	 * Get the data from the order table if hpos enabled otherwise default working.
	 *
	 * @param int    $id .
	 * @param string $key .
	 * @param int    $v .
	 */
	function wps_wsp_get_meta_data( $id, $key, $v ) {

		if ( 'shop_order' === OrderUtil::get_order_type( $id ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
			// HPOS usage is enabled.
			$order    = wc_get_order( $id );
			$meta_val = $order->get_meta( $key );
			return $meta_val;
		} elseif ( 'wps_subscriptions' === OrderUtil::get_order_type( $id ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
			// HPOS usage is enabled.
			$order    = new WPS_Subscription( $id );
			$meta_val = $order->get_meta( $key );
			return $meta_val;
		} else {
			// Traditional CPT-based orders are in use.
			$meta_val = get_post_meta( $id, $key, $v );
			return $meta_val;
		}
	}
	/**
	 *
	 * Update the data into the order table if hpos enabled otherwise default working.
	 *
	 * @param int               $id .
	 * @param string            $key .
	 * @param init|array|object $value .
	 */
	function wps_wsp_update_meta_data( $id, $key, $value ) {

		if ( 'shop_order' === OrderUtil::get_order_type( $id ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
			// HPOS usage is enabled.
			$order = wc_get_order( $id );
			$order->update_meta_data( $key, $value );
			$order->save();
		} elseif ( 'wps_subscriptions' === OrderUtil::get_order_type( $id ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
			// HPOS usage is enabled.
			$order = new WPS_Subscription( $id );
			$order->update_meta_data( $key, $value );
			$order->save();
		} else {
			// Traditional CPT-based orders are in use.
			update_post_meta( $id, $key, $value );

		}
	}

	add_action(
		'plugins_loaded',
		function () {
			if ( class_exists( 'WooCommerce\PayPalCommerce\PluginModule' ) ) {
				// Add the Woocommerce Paypal Payments module.
				include_once WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_PATH . 'includes/woocommerce-paypal-payments-module/class-wps-wc-paypal-payments-integration.php';
				WPS_WC_PayPal_Payments_Integration::get_instance();
			}
		},
		9
	);
} else {

	// WooCommerce is not active so deactivate this plugin.
	add_action( 'admin_init', 'wps_wsp_activation_failure' );
	add_action( 'admin_enqueue_scripts', 'wps_wsp_enqueue_activation_script' );
	add_action( 'wp_ajax_wps_wsp_activate_lite_plugin', 'wps_wsp_activate_lite_plugin' );
	add_action( 'wp_ajax_nopriv_wps_wsp_activate_lite_plugin', 'wps_wsp_activate_lite_plugin' );

	/**
	 * This is function handling the ajax request.
	 *
	 * @name wps_wsp_activate_lite_plugin.
	 * @since 1.0.0
	 */
	function wps_wsp_activate_lite_plugin() {

		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		$wps_plugin_name = 'subscriptions-for-woocommerce';
		$wps_plugin_api    = plugins_api(
			'plugin_information',
			array(
				'slug' => $wps_plugin_name,
				'fields' => array( 'sections' => false ),
			)
		);
		if ( isset( $wps_plugin_api->download_link ) ) {
			$wps_ajax_obj = new WP_Ajax_Upgrader_Skin();
			$wps_obj = new Plugin_Upgrader( $wps_ajax_obj );
			$wps_install = $wps_obj->install( $wps_plugin_api->download_link );
			activate_plugin( 'subscriptions-for-woocommerce/subscriptions-for-woocommerce.php' );
		}
		wp_send_json_success();
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 * @name wps_wsp_enqueue_activation_script.
	 */
	function wps_wsp_enqueue_activation_script() {
		$wps_wsp_params = array(
			'ajax_url'      => admin_url( 'admin-ajax.php' ),
			'wps_wsp_nonce' => wp_create_nonce( 'wps-wsp-activation-nonce' ),
		);
		wp_enqueue_script( 'admin-js', plugin_dir_url( __FILE__ ) . '/admin/js/wps-wsp-activation.js', array( 'jquery' ), '1.0.0', false );
		wp_enqueue_style( 'admin-css', plugin_dir_url( __FILE__ ) . '/admin/css/wps-wsp-activation.css', array(), '1.0.0', false );
		wp_localize_script( 'admin-js', 'wps_wsp_activation', $wps_wsp_params );
	}
	/**
	 * Deactivate this plugin.
	 *
	 * @name wps_wsp_activation_failure.
	 * @since 1.0.0
	 */
	function wps_wsp_activation_failure() {

		add_action( 'admin_notices', 'wps_wsp_activation_failure_admin_notice' );
		add_action( 'network_admin_notices', 'wps_wsp_activation_failure_admin_notice' );
	}

	/**
	 * This function is used to display admin error notice when WooCommerce is not active.
	 *
	 * @name wps_wsp_activation_failure_admin_notice
	 * @since 1.0.0
	 */
	function wps_wsp_activation_failure_admin_notice() {

		// to hide Plugin activated notice.
		unset( $_GET['activate'] );

		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php esc_html_e( 'WooCommerce is not activated, Please activate WooCommerce first to activate WooCommerce Subscriptions PRO.', 'woocommerce-subscriptions-pro' ); ?></p>
			</div>

			<?php
		} elseif ( ! is_plugin_active( 'subscriptions-for-woocommerce/subscriptions-for-woocommerce.php' ) ) {
			?>

			<div class="notice notice-error is-dismissible">
				<p><?php esc_html_e( 'Subscriptions For WooCommerce is not activated, Please activate Subscriptions For WooCommerce first to activate WooCommerce Subscriptions PRO. ', 'woocommerce-subscriptions-pro' ); ?>
				</p>
				<?php
				$wps_lite_plugin = 'subscriptions-for-woocommerce/subscriptions-for-woocommerce.php';
				if ( file_exists( WP_PLUGIN_DIR . '/' . $wps_lite_plugin ) && ! is_plugin_active( 'subscriptions-for-woocommerce/subscriptions-for-woocommerce.php' ) ) {
					?>
					 
						<p></p>	
					<?php
				} else {
					?>
					<p>
						<a href = "#" id="wps-wsp-install-lite" class="button button-primary"><?php esc_html_e( 'Install', 'woocommerce-subscriptions-pro' ); ?></a>
						<span style="display: none;" class="wps_wsp_loader_style" id="wps_wsp_notice_loader">
							<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ); ?>admin/images/loader.gif">
						</span>
					</p>
					<?php
				}
				?>
			</div>
			<?php
		}
	}

	add_action( 'admin_enqueue_scripts', 'wps_plugin_page_script' );
	/**
	 * Register the JavaScript for the plugin page area.
	 *
	 * @since    2.2.8
	 * @param    string $hook      The plugin page slug.
	 */
	function wps_plugin_page_script( $hook ) {

		$screen = get_current_screen();

		if ( isset( $screen->id ) && 'plugins' === $screen->id ) {
			wp_register_script( 'woocommerce-subscriptions-pro-activation', plugin_dir_url( __FILE__ ) . 'admin/js/mwb-wsp-activation.js', array( 'jquery' ), '2.2.8', false );
			wp_localize_script(
				'woocommerce-subscriptions-pro-activation',
				'wps_wsp_activation',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'wps_wsp_license_nonce' => wp_create_nonce( 'wps-wsp-nonce-action' ),
				)
			);
			wp_enqueue_script( 'woocommerce-subscriptions-pro-activation' );
		}
	}
}
