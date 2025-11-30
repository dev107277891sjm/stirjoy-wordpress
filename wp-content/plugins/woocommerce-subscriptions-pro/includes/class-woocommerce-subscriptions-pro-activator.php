<?php
/**
 * Fired during plugin activation
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    Woocommerce_Subscriptions_Pro
 * @subpackage Woocommerce_Subscriptions_Pro/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Woocommerce_Subscriptions_Pro
 * @subpackage Woocommerce_Subscriptions_Pro/includes
 * @author     WP Swings <webmaster@wpswings.com>
 */
class Woocommerce_Subscriptions_Pro_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @param String $network_wide as network wide.
	 *
	 * @since    1.0.0
	 */
	public static function woocommerce_subscriptions_pro_activate( $network_wide ) {
		global $wpdb;
		if ( is_multisite() && $network_wide ) {
			// Get all blogs in the network and activate plugins on each one.
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );

				$timestamp = get_option( 'wps_wsp_lcns_thirty_days', 'not_set' );
				if ( 'not_set' === $timestamp ) {
					$current_time = current_time( 'timestamp' );
					$thirty_days = strtotime( '+30 days', $current_time );
					update_option( 'wps_wsp_lcns_thirty_days', $thirty_days );
				}
				// Validate license daily cron.
				if ( ! wp_next_scheduled( 'wps_wsp_check_license_daily' ) ) {
					wp_schedule_event( time(), 'daily', 'wps_wsp_check_license_daily' );
				}

				restore_current_blog();
			}
		} else {
			$timestamp = get_option( 'wps_wsp_lcns_thirty_days', 'not_set' );
			if ( 'not_set' === $timestamp ) {
				$current_time = current_time( 'timestamp' );
				$thirty_days = strtotime( '+30 days', $current_time );
				update_option( 'wps_wsp_lcns_thirty_days', $thirty_days );
			}
			// Validate license daily cron.
			if ( ! wp_next_scheduled( 'wps_wsp_check_license_daily' ) ) {
				wp_schedule_event( time(), 'daily', 'wps_wsp_check_license_daily' );
			}
		}
	}
}
