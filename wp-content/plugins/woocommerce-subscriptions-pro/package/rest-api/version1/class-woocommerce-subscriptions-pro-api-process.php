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

use Automattic\WooCommerce\Utilities\OrderUtil;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'Woocommerce_Subscriptions_Pro_Api_Process' ) ) {

	/**
	 * The plugin API class.
	 *
	 * This is used to define the functions and data manipulation for custom endpoints.
	 *
	 * @since      1.0.0
	 * @package    Woocommerce_Subscriptions_Pro
	 * @subpackage Woocommerce_Subscriptions_Pro/includes
	 * @author     WP Swings <wpswings.com>
	 */
	class Woocommerce_Subscriptions_Pro_Api_Process {

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {
		}
	}
}
