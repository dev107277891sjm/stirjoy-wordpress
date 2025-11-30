<?php
/**
 * The admin-specific payment integration functionality of the plugin.
 *
 * @link       https://wpswings.com
 * @since      2.2.6
 *
 * @package     Subscriptions_For_Woocommerce
 * @subpackage  Subscriptions_For_Woocommerce/package
 */

/**
 * The Payment-specific functionality of the plugin admin side.
 *
 * @package     Subscriptions_For_Woocommerce
 * @subpackage  Subscriptions_For_Woocommerce/package
 * @author      wpswings <webmaster@wpswings.com>
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Automattic\WooCommerce\Utilities\OrderUtil;

if ( ! class_exists( 'Wps_Subscriptions_Payment_Payhere_Main' ) ) {
	/**
	 * Define class and module for woo stripe.
	 */
	class Wps_Subscriptions_Payment_Payhere_Main {
		/**
		 * Constructor
		 */
		public function __construct() {

			if ( $this->wps_sfw_check_woo_payhere_enable() && wps_sfw_check_plugin_enable() ) {
				add_filter( 'wps_sfw_supported_payment_gateway_for_woocommerce', array( $this, 'wps_wsp_payhere_payment_gateway_for_woocommerce' ), 10, 2 );
				add_filter( 'woocommerce_valid_order_statuses_for_payment_complete', array( $this, 'wps_wsp_payhere_payment_order_statuses_for_payment_complete' ), 10, 2 );

				add_action( 'wps_sfw_other_payment_gateway_renewal', array( $this, 'wps_sfw_woo_payhere_process_subscription_payment' ), 10, 3 );

				add_action( 'wps_sfw_subscription_cancel', array( $this, 'wps_sfw_cancel_payhere_subscription' ), 10, 2 );
			}
		}

			/**
			 * Allow payment method.
			 *
			 * @name wps_wsp_payhere_payment_gateway_for_woocommerce.
			 * @param array  $supported_payment_method supported_payment_method.
			 * @param string $payment_method payment_method.
			 * @return array
			 */
		public function wps_wsp_payhere_payment_gateway_for_woocommerce( $supported_payment_method, $payment_method ) {
			if ( $this->wps_wsp_check_supported_payment_options( $payment_method ) ) {
				$supported_payment_method[] = $payment_method;
			}
			return apply_filters( 'wps_wsp_supported_payment_payhere', $supported_payment_method, $payment_method );
		}

		/**
		 * Check supported payment method.
		 *
		 * @name wps_wsp_check_supported_payment_options
		 * @param string $payment_method payment_method.
		 * @return boolean
		 */
		public function wps_wsp_check_supported_payment_options( $payment_method ) {
			$result = false;
			if ( 'payhere' == $payment_method ) {
				$result = true;
			}
			return $result;
		}

		/**
		 * This function is add subscription order status.
		 *
		 * @name wps_wsp_payhere_payment_order_statuses_for_payment_complete
		 * @param array  $order_status order_status.
		 * @param object $order order.
		 * @return mixed
		 */
		public function wps_wsp_payhere_payment_order_statuses_for_payment_complete( $order_status, $order ) {
			if ( $order && is_object( $order ) ) {
				$order_id = $order->get_id();

				$payment_method = $order->get_payment_method();
				$wps_sfw_renewal_order = wps_wsp_get_meta_data( $order_id, 'wps_sfw_renewal_order', true );
				if ( $this->wps_wsp_check_supported_payment_options( $payment_method ) && 'yes' == $wps_sfw_renewal_order ) {
					$order_status[] = 'wps_renewal';
				}
			}
			return apply_filters( 'wps_wsp_add_subscription_order_statuses_for_payment_complete', $order_status, $order );
		}

		/**
		 * Check woo payhere enable.
		 *
		 * @name wps_sfw_check_woo_payhere_enable
		 * @return boolean
		 */
		public function wps_sfw_check_woo_payhere_enable() {
			$activated = false;
			if ( in_array( 'payhere-payment-gateway/payhere-payment-gateway.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				$activated = true;
			}
			return $activated;
		}

		/**
		 * Process subscription payment.
		 *
		 * @name wps_sfw_woo_payhere_process_subscription_payment.
		 * @param object $order order.
		 * @param int    $subscription_id subscription_id.
		 * @param string $payment_method payment_method.
		 * @return void
		 */
		public function wps_sfw_woo_payhere_process_subscription_payment( $order, $subscription_id, $payment_method ) {
			if ( strpos( $payment_method, 'payhere' ) !== false ) {
				if ( ! $order ) {
					return;
				}
				$payment_method_obj   = '';
				$wps_enabled_gateways = WC()->payment_gateways->get_available_payment_gateways();
				if ( isset( $wps_enabled_gateways[ $payment_method ] ) ) {
					$payment_method_obj = $wps_enabled_gateways[ $payment_method ];
				}
				if ( empty( $payment_method_obj ) ) {
					return;
				}
				$user_id = $order->get_customer_id();
				$customer_token = get_user_meta( $user_id, 'payhere_customer_token', true );
				if ( empty( $customer_token ) ) {
					$order->add_order_note( __( 'Payhere Subscription renewal failed. Can not find the token.', 'woocommerce-subscriptions-pro' ) );
					$order->update_status( 'failed' );
					return;
				}
				if ( class_exists( 'WCGatewayPayHere' ) && class_exists( 'ChargePayment' ) ) {

					$gateway = new WCGatewayPayHere();
					$settings = $gateway->settings;

					$effective_app_secret = $settings['app_secret'];
					$effective_app_id     = $settings['app_id'];
					$effective_test_mode  = $settings['test_mode'];

					$charge_obj = new ChargePayment( $effective_app_id, $effective_app_secret, $effective_test_mode );

					$_auth_token_data = $charge_obj->get_authorization_token();

					$auth_token_data  = json_decode( $_auth_token_data );
					if ( isset( $auth_token_data->access_token ) && ! empty( $auth_token_data->access_token ) ) {

						$url = $charge_obj->get_payhere_chargin_api_url();

						$fields = array(
							'type'           => 'PAYMENT',
							'order_id'       => 'WC_' . $order->get_id(),
							'items'          => 'Renewal  Order :' . $order->get_id(),
							'currency'       => get_woocommerce_currency(),
							'amount'         => $order->get_total(),
							'customer_token' => $customer_token,
							'custom_1'       => '',
							'custom_2'       => '',
							'itemList'       => array(),
						);

						$args = array(
							'body'        => wp_json_encode( $fields ),
							'timeout'     => '10',
							'redirection' => '1',
							'httpversion' => '2.0',
							'blocking'    => true,
							'headers'     => array(
								'Authorization' => 'Bearer ' . $auth_token_data->access_token,
								'Content-Type'  => 'application/json',
							),
							'cookies'     => array(),
							'data_format' => 'body',
						);

						$res = wp_remote_post( $url, $args );

						if ( $res instanceof WP_Error ) {
							return false;
						}
						$_charge_response = $res['body'];

						$charge_response = json_decode( $_charge_response );

						if ( '1' === $charge_response->status ) {

							if ( '2' === $charge_response->data->status_code ) {
								$order->payment_complete();
								$order->add_order_note( $charge_response->msg );
								$order->add_order_note( __( 'PayHere payment successful.<br/>PayHere Payment ID: ', 'woocommerce-subscriptions-pro' ) . $charge_response->data->payment_id );
							} else {
								$order->update_status( 'failed' );
								$order->add_order_note( __( 'Payment Un-Successful. Code : ', 'woocommerce-subscriptions-pro' ) . $charge_response->data->payment_id );
							}
						} else {
							$order->update_status( 'failed' );
							$order->add_order_note( __( 'Can\'t make the payment. Payment Charge Request Failed', 'woocommerce-subscriptions-pro' ) . '<br/>' . $charge_response->msg );
						}
					} else {
						$order->update_status( 'failed' );
						$order->add_order_note( __( 'Can\'t make the payment. Can\'t Generate the Authorization Tokens.', 'woocommerce-subscriptions-pro' ) );
					}
				}
			}
		}

		/**
		 * This function is used to cancel subscriptions status.
		 *
		 * @name wps_sfw_cancel_payhere_subscription
		 * @param int    $wps_subscription_id wps_subscription_id.
		 * @param string $status status.
		 * @since    1.0.1
		 */
		public function wps_sfw_cancel_payhere_subscription( $wps_subscription_id, $status ) {

			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
				$subscription = new WPS_Subscription( $wps_subscription_id );
				$wps_payment_method = $subscription->get_payment_method();
			} else {
				$wps_payment_method = get_post_meta( $wps_subscription_id, '_payment_method', true );
			}

			if ( strpos( $wps_payment_method, 'payhere' ) !== false ) {
				if ( 'Cancel' == $status ) {
					wps_sfw_send_email_for_cancel_susbcription( $wps_subscription_id );
					wps_wsp_update_meta_data( $wps_subscription_id, 'wps_subscription_status', 'cancelled' );
				}
			}
		}
	}
}
new Wps_Subscriptions_Payment_Payhere_Main();
