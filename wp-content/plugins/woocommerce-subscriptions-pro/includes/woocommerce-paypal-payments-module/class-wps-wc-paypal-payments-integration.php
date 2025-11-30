<?php
/**
 * WPS_WC_PayPal_Payments_Integration integration with WooCommerce PayPal Payments Plugin
 *
 * @class   WPS_WC_PayPal_Payments_Integration
 * @since   2.6.0
 *  @package Woocommerce_Subscriptions_Pro/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
use WooCommerce\PayPalCommerce\WcGateway\Gateway\PayPalGateway;
use WooCommerce\PayPalCommerce\WcGateway\Gateway\CreditCardGateway;
use Automattic\WooCommerce\Utilities\OrderUtil;
/**
 * Compatibility class for WooCommerce PayPal Payments.
 *
 * @extends WPS_WC_PayPal_Payments_Integration
 */
class WPS_WC_PayPal_Payments_Integration {
	/**
	 * Main instance
	 *
	 * @var static|null
	 */
	private static $instance = null;

	/**
	 * Get class single instance.
	 *
	 * @static
	 * @return static
	 */
	public static function get_instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Construct
	 *
	 * @since 2.27
	 */
	protected function __construct() {
		$this->include_files();
		// Register module for paypal payments plugin.
		add_filter( 'woocommerce_paypal_payments_modules', array( $this, 'add_module' ), 10, 1 );

		add_filter( 'wps_sfw_supported_payment_gateway_for_woocommerce', array( $this, 'wps_wsp_wc_paypal_payments_gateway' ), 10, 2 );
		add_filter( 'wps_sfw_supported_data_payment_for_configuration', array( $this, 'wps_wsp_wc_paypal_payments_add_gateway' ), 10, 1 );
		add_action( 'wps_sfw_subscription_cancel', array( $this, 'wps_wsp_wc_paypal_payment_handle_cancel' ), 10, 2 );
	}

	/**
	 * Allow payment method.
	 *
	 * @name wps_wsp_wc_paypal_payments_gateway.
	 * @param array  $supported_payment_method supported_payment_method.
	 * @param string $payment_method payment_method.
	 * @return array
	 */
	public function wps_wsp_wc_paypal_payments_gateway( $supported_payment_method, $payment_method ) {

		if ( PayPalGateway::ID === $payment_method || CreditCardGateway::ID === $payment_method ) {
			$supported_payment_method[] = $payment_method;
		}
		return apply_filters( 'wps_wsp_supported_payment_woocybs', $supported_payment_method, $payment_method );
	}

	/**
	 * Added the compatible Gateways during the multistep setup
	 *
	 * @param array() $gateways .
	 * @return array()
	 */
	public function wps_wsp_wc_paypal_payments_add_gateway( $gateways ) {
		$gateways[] = array(
			'id' => 'paypal',
			'name' => __( 'WooCommerce PayPal Payments', 'woocommerce-subscriptions-pro' ),
			'url' => 'https://wordpress.org/plugins/woocommerce-paypal-payments/',
			'slug' => 'woocommerce-paypal-payments',
			'is_activated' => ! empty( is_plugin_active( 'woocommerce-paypal-payments/woocommerce-paypal-payments.php' ) ) ? true : false,
		);
		return $gateways;
	}

	/**
	 * This function is used to cancel subscriptions status.
	 *
	 * @name mwb_sfw_cancel_eway_subscription
	 * @param int    $wps_subscription_id mwb_subscription_id.
	 * @param string $status status.
	 */
	public function wps_wsp_wc_paypal_payment_handle_cancel( $wps_subscription_id, $status ) {

		if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
			$subscription = new WPS_Subscription( $wps_subscription_id );
			$wps_payment_method = $subscription->get_payment_method();
		} else {
			$wps_payment_method = get_post_meta( $wps_subscription_id, '_payment_method', true );
		}

		if ( PayPalGateway::ID === $wps_payment_method || CreditCardGateway::ID === $wps_payment_method ) {
			if ( 'Cancel' == $status ) {
				wps_sfw_send_email_for_cancel_susbcription( $wps_subscription_id );
				wps_wsp_update_meta_data( $wps_subscription_id, 'wps_subscription_status', 'cancelled' );
			}
		}
	}

	/**
	 * Include required files for gateway integration
	 *
	 * @return void
	 */
	protected function include_files() {

		$pp_version = $this->get_plugin_version();
		if ( empty( $pp_version ) ) {
			return;
		}

		$files_map = array(
			'class-wps-wc-paypal-payments-helper.php'  => 'WPS_WC_PayPal_Payments_Helper',
			'class-wps-wc-paypal-payments-renewal-handler.php' => 'WPS_WC_PayPal_Payments_Renewal_Handler',
			'class-wps-wc-paypal-payments-module.php'  => 'WPS_WC_PayPal_Payments_Module',
		);

		// Conditionally load legacy files to grant backward compatibility.
		$legacy_dir = WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_PATH . 'includes/woocommerce-paypal-payments-module/src/legacy';
		foreach ( scandir( $legacy_dir ) as $legacy_version_dir ) {
            if ( in_array( $legacy_version_dir, array( '.', '..' ), true ) || ! version_compare( $pp_version, $legacy_version_dir, '<=' ) ) {
                continue;
			}
			foreach ( scandir( $legacy_dir . DIRECTORY_SEPARATOR . $legacy_version_dir ) as $file ) {
				if ( in_array( $file, array( '.', '..' ), true ) ) {
					continue;
				}

				// Check class exists to avoid require duplicates.
				if ( empty( $files_map[ $file ] ) || ! class_exists( $files_map[ $file ] ) ) {
					require_once $legacy_dir . DIRECTORY_SEPARATOR . $legacy_version_dir . DIRECTORY_SEPARATOR . $file;
				}
			}
		}

		// include common.
		foreach ( $files_map as $file => $class_name ) {
			if ( ! class_exists( $class_name ) ) {
				require_once WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_PATH . "includes/woocommerce-paypal-payments-module/src/{$file}";
			}
		}
	}

	/**
	 * Add module to the WooCommerce PayPal Payments modules list
	 *
	 * @param array $modules Array of available modules.
	 * @return array
	 */
	public function add_module( $modules ) {
		// Double check class exists.
		if ( class_exists( 'WPS_WC_PayPal_Payments_Module', false ) ) {
			return array_merge(
				$modules,
				array(
					( require 'module.php' )(),
				)
			);
		}

		return $modules;
	}

	/**
	 * Get WooCommerce PayPal Payments plugin version for the backward compatibility.
	 *
	 * @return string|false
	 */
	protected function get_plugin_version() {
		$plugin_metadata = array_filter(
			get_plugins(),
			function ( $plugin_init ) {
				return false !== strpos( $plugin_init, 'woocommerce-paypal-payments.php' ) && is_plugin_active( $plugin_init );
			},
			ARRAY_FILTER_USE_KEY
		);

		if ( empty( $plugin_metadata ) ) {
			return false;
		}

		$plugin_metadata = array_shift( $plugin_metadata );
		return $plugin_metadata['Version'] ?? '1.0.0';
	}
}
