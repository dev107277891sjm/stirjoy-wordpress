<?php
/**
 * The admin-specific payment integration functionality of the plugin.
 *
 * @link       https://wpswings.com
 * @since      2.0.0
 *
 * @package     Woocommerce_Subscriptions_Pro
 * @subpackage  Woocommerce_Subscriptions_Pro/package
 */

/**
 * The Payment-specific functionality of the plugin admin side.
 *
 * @package     Woocommerce_Subscriptions_Pro
 * @subpackage  Woocommerce_Subscriptions_Pro/package
 * @author      WP Swings <webmaster@wpswings.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Automattic\WooCommerce\Utilities\OrderUtil;

if ( ! class_exists( 'Wps_Subscriptions_Payment_Multisafepay_Main' ) ) {
	/**
	 * Define class and module for multisafepay.
	 */
	class Wps_Subscriptions_Payment_Multisafepay_Main {
		/**
		 * Constructor
		 */
		public function __construct() {
			if ( $this->wps_wsp_check_multisafepay_enable() && wps_sfw_check_plugin_enable() ) {
				add_action( 'wps_sfw_subscription_cancel', array( $this, 'wps_wsp_cancel_multisafepay_subscription' ), 10, 2 );
				add_filter( 'wps_sfw_supported_payment_gateway_for_woocommerce', array( $this, 'wps_wsp_allow_multisafepay_for_subs' ), 10, 2 );
				add_action( 'wps_sfw_other_payment_gateway_renewal', array( $this, 'wps_wsp_multisafepay_renewal_order' ), 20, 3 );
				add_filter( 'woocommerce_valid_order_statuses_for_payment_complete', array( $this, 'wps_wsp_add_multisafepay_order_statuses_for_payment_complete' ), 10, 2 );
			}
		}

		/**
		 * This function is used to check activation of multisafepay.
		 *
		 * @since 2.0.0
		 * @return boolean
		 */
		public function wps_wsp_check_multisafepay_enable() {
			$activated = false;
			if ( in_array( 'multisafepay/multisafepay.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				$activated = true;
			}
			return $activated;
		}


		/**
		 * Function name wps_wsp_multisafepay_renewal_order.
		 * this function is used to create renewal order using multisafepay.
		 *
		 * @param object $wps_new_order order object.
		 * @param int    $subscription_id subscription id.
		 * @param mixed  $payment_method payment method.
		 * @since 2.0.0
		 * @return void
		 */
		public function wps_wsp_multisafepay_renewal_order( $wps_new_order, $subscription_id, $payment_method ) {

			if ( strpos( $payment_method, 'multisafepay_' ) !== false ) {
				$notification_url = get_rest_url( get_current_blog_id(), 'multisafepay/v1/notification' );
				$check_url = $wps_new_order->get_checkout_order_received_url();
				$cancel_url = wp_specialchars_decode( $wps_new_order->get_cancel_order_url() );
				$parent_order_id = wps_wsp_get_meta_data( $subscription_id, 'wps_parent_order', true );
				$parent_order = wc_get_order( $parent_order_id );
				$parent_payment_method = $parent_order->get_payment_method();
				$method_title = $parent_order->get_payment_method_title();

				/* translators: %s: Site name */
				$default_description = sprintf( _x( 'Orders with %s', 'data sent to multisafepay', 'woocommerce-subscriptions-pro' ), get_bloginfo( 'name' ) );
				$current_order_id = $wps_new_order->get_id();
				$payment_options = array(
					'notification_url' => $notification_url,
					'redirect_url' => $check_url,
					'cancel_url'   => $cancel_url,
				);
				if ( get_option( 'multisafepay_testmode', false ) ) {
					$api_url = 'https://testapi.multisafepay.com/v1/json/orders/';
					$api_key = get_option( 'multisafepay_test_api_key', false );
				} else {
					$api_url = 'https://api.multisafepay.com/v1/json/orders/';
					$api_key = get_option( 'multisafepay_api_key', false );
				}
				$url = $api_url . $parent_order_id;
				$request = array(
					'method'      => 'GET',
					'timeout'     => 120,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => true,
					'cookies'     => array(),
					'headers' => array( 'api_key' => $api_key ),
				);
				$main_url = esc_url_raw( $url );
				$response = wp_remote_get( $main_url, $request );
				if ( ! is_wp_error( $response ) ) {
					$response = wp_remote_retrieve_body( $response );
					$previous_payment_details = json_decode( $response, true );
					$recurring_id = $previous_payment_details['data']['payment_details']['recurring_id'];
					$gateway = $previous_payment_details['data']['payment_methods']['type'];
					$customer = array(
						'locale' => $previous_payment_details['data']['customer']['locale'],
						'ip_address' => $wps_new_order->get_customer_ip_address(),
						'forwarded_ip' => $wps_new_order->get_customer_ip_address(),
						'first_name' => $wps_new_order->get_billing_first_name(),
						'last_name' => $wps_new_order->get_billing_last_name(),
						'gender'    => null,
						'birthday' => null,
						'address1' => $wps_new_order->get_billing_address_1(),
						'address2' => $wps_new_order->get_billing_address_2(),
						'house_number' => null,
						'zip_code' => $wps_new_order->get_billing_postcode(),
						'city' => $wps_new_order->get_billing_city(),
						'country' => $wps_new_order->get_billing_country(),
						'phone' => $wps_new_order->get_billing_phone(),
						'email' => $wps_new_order->get_billing_email(),
						'user_agent' => $wps_new_order->get_customer_user_agent(),
						'referrer' => get_site_url(),
						'reference' => null,
					);
					$recurring_param = array(
						'type' => 'direct',
						'gateway' => $gateway,
						'order_id' => $current_order_id,
						'currency'  => $wps_new_order->get_currency(),
						'recurring_model' => 'subscription',
						'recurring_id' => $recurring_id,
						'recurring_flow' => 'token',
						'amount' => ( $wps_new_order->get_total() * 100 ),
						'description' => $default_description,
						'payment_options' => $payment_options,
						'customer' => $customer,
					);
					$recurring_url = $api_url;
					$args = array(
						'method' => 'POST',
						'body' => wp_json_encode( $recurring_param ),
						'headers' => array( 'api_key' => $api_key ),
					);
					$data = wp_remote_post( $recurring_url, $args );
					if ( ! is_wp_error( $data ) ) {
						$data = wp_remote_retrieve_body( $data );
						$final_payment_details = json_decode( $data, true );
						if ( true === $final_payment_details['success'] ) {
							$transaction_id = $final_payment_details['data']['transaction_id'];
							if ( $transaction_id && ( 'completed' === $final_payment_details['data']['status'] ) ) {
								/* translators: %s: search transaction id */
								$order_note = sprintf( __( 'Multisafepay Renewal Transaction Successful (%s)', 'woocommerce-subscriptions-pro' ), $transaction_id );
								$wps_new_order->add_order_note( $order_note );
								$wps_new_order->set_payment_method( $parent_payment_method );
								$wps_new_order->set_payment_method_title( $method_title );
								$wps_new_order->save();
								$wps_new_order->payment_complete( $transaction_id );
								do_action( 'wps_sfw_recurring_payment_success', $current_order_id );
							} else {
								do_action( 'wps_sfw_recurring_payment_failed', $current_order_id );
								do_action( 'wps_sfw_cancel_failed_susbcription', false, $current_order_id, $subscription_id );
							}
						}
					} else {
						wp_mail( get_option( 'admin_email' ), __( 'Recurring payment error', 'woocommerce-subscriptions-pro' ), wp_json_encode( $data ) );
					}
				} else {
					wp_mail( get_option( 'admin_email' ), __( 'Order fetching error', 'woocommerce-subscriptions-pro' ), wp_json_encode( $response ) );
				}
			}
		}

		/**
		 * This function is used to cancel subscriptions status.
		 *
		 * @name wps_wsp_cancel_multisafepay_subscription
		 * @param int    $wps_subscription_id wps_subscription_id.
		 * @param string $status status.
		 * @since 2.0.0
		 */
		public function wps_wsp_cancel_multisafepay_subscription( $wps_subscription_id, $status ) {

			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
				$subscription = new WPS_Subscription( $wps_subscription_id );
				$wps_payment_method = $subscription->get_payment_method();
			} else {
				$wps_payment_method = get_post_meta( $wps_subscription_id, '_payment_method', true );
			}

			if ( strpos( $wps_payment_method, 'multisafepay_' ) !== false ) {

				if ( 'Cancel' === $status ) {
					wps_sfw_send_email_for_cancel_susbcription( $wps_subscription_id );
					wps_wsp_update_meta_data( $wps_subscription_id, 'wps_subscription_status', 'cancelled' );
				}
			}
		}

		/**
		 * This function is add subscription order status.
		 *
		 * @name wps_wsp_add_multisafepay_order_statuses_for_payment_complete
		 *
		 * @param array  $order_status order_status.
		 * @param object $order order.
		 * @since 2.0.0
		 */
		public function wps_wsp_add_multisafepay_order_statuses_for_payment_complete( $order_status, $order ) {
			if ( $order && is_object( $order ) ) {
				$order_id = $order->get_id();
				$payment_method = $order->get_payment_method();
				$wps_sfw_renewal_order = wps_wsp_get_meta_data( $order_id, 'wps_sfw_renewal_order', true );
				if ( strpos( $payment_method, 'multisafepay_' ) !== false && 'yes' === $wps_sfw_renewal_order ) {
					$order_status[] = 'wps_renewal';
				}
			}
			return apply_filters( 'wps_sfw_add_subscription_order_statuses_for_payment_complete', $order_status, $order );
		}

		/**
		 * Function name wps_wsp_allow_multisafepay_for_subs.
		 * this function is used to allow multisafepay for payment
		 *
		 * @param array $wps_supported_method wps_supported_method.
		 * @param sting $payment_method payment_method.
		 * @since 2.0.0
		 * @return array
		 */
		public function wps_wsp_allow_multisafepay_for_subs( $wps_supported_method, $payment_method ) {
			if ( strpos( $payment_method, 'multisafepay_' ) !== false ) {
				$wps_supported_method[] = $payment_method;
			}
			return $wps_supported_method;
		}
	}

}

return new Wps_Subscriptions_Payment_Multisafepay_Main();
