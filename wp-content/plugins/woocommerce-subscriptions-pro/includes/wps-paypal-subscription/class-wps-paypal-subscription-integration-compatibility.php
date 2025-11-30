<?php
/**
 * Subscription Renewal Compatibility
 *
 * @link       https://wpswings.com
 * @since      2.4.0
 *
 * @package    Wps_Paypal_Subscription_Integration
 * @subpackage Wps_Paypal_Subscription_Integration/includes
 */

use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 *
 * Subscription Renewal Compatibility
 *
 * @link       https://wpswings.com
 * @since      2.4.0
 *
 * @package    Wps_Paypal_Subscription_Integration
 * @subpackage Wps_Paypal_Subscription_Integration/includes
 */
class WPS_Paypal_Subscription_Integration_Compatibility {
	/**
	 * Class constructor
	 */
	public function __construct() {
		add_filter( 'wps_sfw_supported_payment_gateway_for_woocommerce', array( $this, 'wps_support_paypal_subscription_payment_method' ), 10, 2 );
		add_action( 'woocommerce_available_payment_gateways', array( $this, 'wps_add_paypal_subscription_payment_method' ), 9999 );

		add_action( 'wps_sfw_subscription_cancel', array( $this, 'wps_wsp_cancel_wps_paypal_subscription' ), 10, 2 );
	}

	/**
	 * Add the WPS Paypal Subscription Payment for the Subscription product during checkout with validation.
	 *
	 * @param array() $available_gateways .
	 */
	public function wps_add_paypal_subscription_payment_method( $available_gateways ) {
		if ( is_admin() || ! is_checkout() ) {
			return $available_gateways;
		}
		if ( isset( $available_gateways ) && ! empty( $available_gateways ) && is_array( $available_gateways ) ) {
			if ( isset( $available_gateways['wps_paypal_subscription'] ) && ! $this->wps_validate_payment_gateway() ) {
				unset( $available_gateways['wps_paypal_subscription'] );
			}
		}
		return $available_gateways;
	}

	/**
	 * Add the WPS Paypal Subscription Payment for the Subscription product during checkout.
	 *
	 * @param array() $wps_supported_method array of payment methods.
	 * @param string  $key payment method.
	 */
	public function wps_support_paypal_subscription_payment_method( $wps_supported_method, $key ) {
		$wps_supported_method[] = 'wps_paypal_subscription';
		return $wps_supported_method;
	}

	/** Allow to validate only one subscription product at a time during checkout */
	public function wps_validate_payment_gateway() {
		$number_of_subs  = 0;
		$normal_products = 0;
		$allow           = false;
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$produdct    = $cart_item['data'];

			$bundled_by = isset( $cart_item['bundled_by'] ) ? $cart_item['bundled_by'] : null;
			if ( $bundled_by ) {
				continue;
			}
			$sub_product = wps_sfw_check_product_is_subscription( $produdct );
			if ( $sub_product ) {
				++$number_of_subs;
			} else {
				++$normal_products;
			}
		}
		if ( 1 === $number_of_subs && ! $normal_products ) {
			$allow = true;
		}
		return $allow;
	}
	/**
	 * This function is used to cancel subscriptions.
	 *
	 * @name wps_wsp_cancel_wps_paypal_subscription
	 * @param int    $wps_subscription_id wps_subscription_id.
	 * @param string $status status.
	 * @since 1.0.0
	 * @return void
	 */
	public function wps_wsp_cancel_wps_paypal_subscription( $wps_subscription_id, $status ) {

		if ( OrderUtil::custom_orders_table_usage_is_enabled() && class_exists( 'WPS_Subscription' ) ) {
			$subscription = new WPS_Subscription( $wps_subscription_id );
			$wps_payment_method = $subscription->get_payment_method();
		} else {
			$wps_payment_method = wps_wsp_get_meta_data( $wps_subscription_id, '_payment_method', true );
		}
		if ( 'wps_paypal_subscription' === $wps_payment_method && 'Cancel' == $status && class_exists( 'WPS_Paypal_Subscription_Integration_Request_API' ) ) {
			$api_obj                = new WPS_Paypal_Subscription_Integration_Request_API();
			$paypal_subscription_id = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_created_paypal_subscription_id', true );
			$res                    = $api_obj->wps_paypal_subscription_cancellation( $paypal_subscription_id );
			if ( 'success' === $res['result'] ) {
				wps_sfw_send_email_for_cancel_susbcription( $wps_subscription_id );
				wps_wsp_update_meta_data( $wps_subscription_id, 'wps_subscription_status', 'cancelled' );
			}
		}
	}
}
new WPS_Paypal_Subscription_Integration_Compatibility();
