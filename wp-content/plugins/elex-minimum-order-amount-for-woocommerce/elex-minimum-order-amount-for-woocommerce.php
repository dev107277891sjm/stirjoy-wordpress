<?php
/*
 * Plugin Name: ELEX Minimum Order Amount for WooCommerce
 * Plugin URI: https://elextensions.com/plugin/elex-minimum-order-amount-for-woocommerce-free/
 * Description: This plugin helps you to configure minimum and maximum order amount based on WordPress user roles.
 * Version: 2.0.6
 * Author: ELEXtensions
 * Author URI: https://elextensions.com
 * Text Domain: elex-wc-checkout-restriction
 * WC requires at least: 2.6
 * WC tested up to: 10.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! defined( 'MINIMUM_ORDER_MAIN_PATH' ) ) {
	define( 'MINIMUM_ORDER_MAIN_PATH', plugin_dir_url( __FILE__ ) );
}

// review component
if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once  ABSPATH . 'wp-admin/includes/plugin.php';
}
include_once __DIR__ . '/review_and_troubleshoot_notify/review-and-troubleshoot-notify-class.php';
$data                      = get_plugin_data( __FILE__, false, false );
$data['name']              = $data['Name'];
$data['basename']          = plugin_basename( __FILE__ );
$data['documentation_url'] = 'https://elextensions.com/knowledge-base/set-minimum-order-amount-woocommerce-elex-minimum-order-amount-for-woocommerce-plugin/';
$data['rating_url']        = 'https://elextensions.com/plugin/elex-minimum-order-amount-for-woocommerce-free/#reviews';
$data['support_url']       = 'https://wordpress.org/support/plugin/elex-minimum-order-amount-for-woocommerce/';

new \Elex_Review_Components( $data );

/**
 * Check if WooCommerce is active.
 *
 * @since 1.0.0
 * This function checks if the WooCommerce plugin is active by examining the list of active plugins.
 * If WooCommerce is not active, the function returns early.
 */
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'elex_wccr_plugin_action_links' );
function elex_wccr_plugin_action_links( $links ) {
	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=elex-wccr' ) . '">' . esc_html__( 'Settings', 'elex-wc-checkout-restriction' ) . '</a>',
		'<a href="https://elextensions.com/support/" target="_blank">' . esc_html__( 'Support', 'elex-wc-checkout-restriction' ) . '</a>',
	);
	return array_merge( $plugin_links, $links );
}

add_action( 'init', 'elex_wccr_admin_menu' );
function elex_wccr_admin_menu() {
	require_once 'includes/elex-wccr-frontend-template.php';
	require_once 'includes/elex-wccr-restrict-logic.php';
}


add_action( 'admin_menu', 'elex_wccr_admin_menu_option' );
$user_adjustment_settings = get_option( 'elex_wccr_checkout_restriction_settings', array() );
if (!empty($user_adjustment_settings)) {
	add_action( 'admin_init', 'migration_of_addon_services');
} else {
	update_option('elex_min_order_migrate', true);
}
function migration_of_addon_services() {
	global $wp_roles;
	$wordpress_roles = $wp_roles->role_names;
	$current_rules = get_option( 'elex_wccr_checkout_restriction_settings');
	if (is_array($current_rules)) {
		foreach ($current_rules as $current_key => $current_rule) {
			if (array_key_exists($current_key, $wordpress_roles)) {
				update_option('elex_min_order_migrate', false);
				break;
			}
		}
	}
	$migration = get_option('elex_min_order_migrate');
	if ($migration) {
		return;
	} else {
		// update_option( 'elex_wccr_checkout_restriction_settings', '');
		$restriction_settings=array();
		$i = 0;
		// Create a custom mapping of role names to custom IDs
		foreach ($current_rules as $current_key => $current_rule ) {
			
			if (!empty($current_rule['error_message'])||!empty($current_rule['min_price'])||!empty($current_rule['max_price'])) {
				
				$restriction_settings[$i]['roles'][] = isset($current_key)?$current_key:'';
				$restriction_settings[$i]['min_price'] = isset($current_rule['min_price'])?$current_rule['min_price']:'';
				$restriction_settings[$i]['max_price'] = isset($current_rule['max_price'])?$current_rule['max_price']:'';
				$restriction_settings[$i]['error_message'] = isset($current_rule['error_message'])?$current_rule['error_message']:'';
				$restriction_settings[$i]['enable_restriction'] = isset($current_rule['enable_restriction'])?$current_rule['enable_restriction']:'';
				$i++;
			} else {
				continue;
			}
		}
		update_option( 'elex_wccr_checkout_restriction_settings', $restriction_settings);
	}
	update_option('elex_min_order_migrate', true);
	
}

function elex_wccr_admin_menu_option() {
	add_submenu_page( 'woocommerce', esc_html__( 'Minimum Order Amount', 'elex-wc-checkout-restriction' ), esc_html__( 'Minimum Order Amount', 'elex-wc-checkout-restriction' ), 'manage_woocommerce', 'admin.php?page=wc-settings&tab=elex-wccr' );
	
}

// High performance order tables compatibility.
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

