<?php
/**
 * The admin-specific payment integration functionality of the plugin
 * compatible with WooCommerce Payments for version 9.0.0
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

use WCPay\Payment_Methods\CC_Payment_Gateway;
use WCPay\Database_Cache;
use WCPay\Session_Rate_Limiter;
use WCPay\Logger;
use WCPay\Exceptions\API_Exception;
use WCPay\Constants\Payment_Capture_Type;
use WCPay\Duplicate_Payment_Prevention_Service;
use WCPay\Exceptions\Invalid_Payment_Method_Exception;
use WCPay\Payment_Information;
use WCPay\Constants\Payment_Type;
use WCPay\Constants\Payment_Initiated_By;
use WCPay\Payment_Methods\CC_Payment_Method;
use WCPay\Duplicates_Detection_Service;
use WCPay\Compatibility_Service;


if ( ! class_exists( 'Wps_Subscriptions_Payment_Woocommercepayments_Main' ) ) {
	/**
	 * Define class and module for Eway Method.
	 */
	class Wps_Subscriptions_Payment_Woocommercepayments_Main {
		/**
		 * Constructor
		 */
		const GATEWAY_ID = 'woocommerce_payments';
		/**
		 * Constructor
		 */
		public function __construct() {

			add_filter( 'wps_sfw_supported_payment_gateway_for_woocommerce', array( $this, 'wps_woocommerce_payment_integration_gateway' ), 10, 2 );
			add_action( 'wps_sfw_subscription_cancel', array( $this, 'wps_sfw_cancel_woocommerce_payment_subscription' ), 10, 2 );
			add_action( 'wps_sfw_other_payment_gateway_renewal', array( $this, 'wps_sfw_process_woocommercepayment_payment_renewal' ), 10, 3 );

			add_filter( 'wc_payments_display_save_payment_method_checkbox', array( $this, 'display_save_payment_method_checkbox' ), 10 );

			add_action( 'woocommerce_payment_complete', array( $this, 'add_payment_meta_data_to_subscription' ) );
			add_action( 'woocommerce_order_status_completed', array( $this, 'add_payment_meta_data_to_subscription' ) );
			add_action( 'woocommerce_order_status_processing', array( $this, 'add_payment_meta_data_to_subscription' ) );

		}

		/**
		 * Returns a boolean value indicating whether the save payment checkbox should be
		 * display_checkbox during checkout.
		 *
		 * @param bool $display_checkbox Show or not the save payment_method checkbox.
		 *
		 * @return bool
		 */
		public function display_save_payment_method_checkbox( $display_checkbox ) {

			if ( function_exists( 'wps_sfw_is_cart_has_subscription_product' ) && wps_sfw_is_cart_has_subscription_product() ) {
				$display_checkbox = false;
			}
			return $display_checkbox;
		}

		/**
		 * Save payment information inside the subscription order.
		 *
		 * @param integer $order_id is parent id.
		 */
		public function add_payment_meta_data_to_subscription( $order_id ) {

			$wps_subscription_id = wps_sfw_get_meta_data( $order_id, 'wps_subscription_id', true );

			if ( empty( $wps_subscription_id ) ) {
				return;
			}

			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
				$subscription = new WPS_Subscription( $wps_subscription_id );
				$wps_payment_method = $subscription->get_payment_method();
			} else {
				$wps_payment_method = get_post_meta( $wps_subscription_id, '_payment_method', true );
			}

			if ( 'woocommerce_payments' === $wps_payment_method ) {
				$parent_id         = wps_sfw_get_meta_data( $wps_subscription_id, 'wps_parent_order', true );
				$parent_order      = wc_get_order( $parent_id );
				$order_tokens      = $parent_order->get_payment_tokens();
				$customer_id       = wps_sfw_get_meta_data( $parent_id, '_stripe_customer_id', true );
				$payment_method_id = wps_sfw_get_meta_data( $parent_id, '_payment_method_id', true );
				wps_sfw_update_meta_data( $wps_subscription_id, '_payment_tokens', $order_tokens );
				wps_sfw_update_meta_data( $wps_subscription_id, '_stripe_customer_id', $customer_id );
				wps_sfw_update_meta_data( $wps_subscription_id, '_payment_method_id', $payment_method_id );
			}
		}
		/**
		 * Replace the main gateway with the sources gateway.
		 *
		 * @param array  $supported_payment_method supported_payment_method.
		 * @param string $payment_method payment_method.
		 * @since 2.0.0
		 * @return array
		 */
		public function wps_woocommerce_payment_integration_gateway( $supported_payment_method, $payment_method ) {

			if ( strpos( $payment_method, 'woocommerce_payments' ) !== false ) {
				$supported_payment_method[] = $payment_method;
			}
			return $supported_payment_method;
		}

		/**
		 * This function is used to cancel subscriptions status.
		 *
		 * @name mwb_sfw_cancel_eway_subscription
		 * @param int    $wps_subscription_id mwb_subscription_id.
		 * @param string $status status.
		 * @since    1.0.1
		 */
		public function wps_sfw_cancel_woocommerce_payment_subscription( $wps_subscription_id, $status ) {

			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
				$subscription       = new WPS_Subscription( $wps_subscription_id );
				$wps_payment_method = $subscription->get_payment_method();
			} else {
				$wps_payment_method = get_post_meta( $wps_subscription_id, '_payment_method', true );
			}
			if ( strpos( $wps_payment_method, 'woocommerce_payments' ) !== false ) {
				if ( 'Cancel' == $status ) {
					wps_sfw_send_email_for_cancel_susbcription( $wps_subscription_id );
					wps_wsp_update_meta_data( $wps_subscription_id, 'wps_subscription_status', 'cancelled' );
				}
			}
		}
		/**
		 * Get_payment_token function
		 *
		 * @param array $order as order.
		 * @return array
		 */
		public function get_payment_token( $order ) {
			if ( ! empty( $order ) ) {
				$order_tokens = $order->get_payment_tokens();
				$token_id     = end( $order_tokens );
				return ! $token_id ? null : WC_Payment_Tokens::get( $token_id );
			}
		}

		/**
		 * Wps_sfw_process_woocommercepayment_payment_renewal function
		 *
		 * @param array   $renewal_order as renewal_order.
		 * @param integer $wps_subscription_id as subscription_id.
		 * @param array   $payment_method as payment_method.
		 * @return void
		 */
		public function wps_sfw_process_woocommercepayment_payment_renewal( $renewal_order, $wps_subscription_id, $payment_method ) {

			$order_id = $renewal_order->get_id();

			if ( strpos( $payment_method, 'woocommerce_payments' ) !== false ) {
				if ( ! $renewal_order ) {
					return;
				}
				$payment_method_obj = '';
				$mwb_enabled_gateways = WC()->payment_gateways->get_available_payment_gateways();
				if ( isset( $mwb_enabled_gateways[ $payment_method ] ) ) {
					$payment_method_obj = $mwb_enabled_gateways[ $payment_method ];
				}
				if ( empty( $payment_method_obj ) ) {
					return;
				}
				$wps_sfw_renewal_order = wps_sfw_get_meta_data( $order_id, 'wps_sfw_renewal_order', true );
				if ( 'yes' !== $wps_sfw_renewal_order ) {
					return;
				}
				$parent_id         = wps_sfw_get_meta_data( $wps_subscription_id, 'wps_parent_order', true );
				$parent_order      = wc_get_order( $parent_id );
				$order_tokens      = $parent_order->get_payment_tokens();
				$customer_id       = wps_sfw_get_meta_data( $parent_id, '_stripe_customer_id', true );
				$payment_method_id = wps_sfw_get_meta_data( $parent_id, '_payment_method_id', true );

				wps_sfw_update_meta_data( $order_id, '_payment_tokens', $order_tokens );
				wps_sfw_update_meta_data( $order_id, '_stripe_customer_id', $customer_id );
				wps_sfw_update_meta_data( $order_id, '_payment_method_id', $payment_method_id );
				$token = $this->get_payment_token( $renewal_order );

				if ( is_null( $token ) ) {
					$default = WC_Payment_Tokens::get_customer_default_token( $renewal_order->get_customer_id() );

					if ( $default ) {
						$token = $default;
						$key   = $default->get_id();
						wps_sfw_update_meta_data( $parent_id, '_payment_tokens', array( $key ) );
						wps_sfw_update_meta_data( $wps_subscription_id, '_payment_tokens', array( $key ) );
					} else {
						$tokens = WC_Payment_Tokens::get_customer_tokens( $renewal_order->get_customer_id(), 'woocommerce_payments' );

						if ( $tokens ) {
							foreach ( $tokens as $key => $current_token ) {
								$token = $current_token;
								wps_sfw_update_meta_data( $parent_id, '_payment_tokens', array( $key ) );
								wps_sfw_update_meta_data( $wps_subscription_id, '_payment_tokens', array( $key ) );
								break;
							}
						}
					}
				}
				if ( is_null( $token ) && ! WC_Payments::is_network_saved_cards_enabled() ) {
					/* translators: %s: wps_subscription_id */
					$renewal_order->add_order_note( sprintf( __( 'payment token not found for subscription renewal failed - %s', 'woocommerce-subscriptions-pro' ), $wps_subscription_id ) );
					return;
				}
				try {
					$api_client                           = WC_Payments::create_api_client();
					$account                              = WC_Payments::get_account_service();
					$session_service                      = new WC_Payments_Session_Service( $api_client );
					$order_service                        = new WC_Payments_Order_Service( $api_client );
					$customer_service                     = new WC_Payments_Customer_Service( $api_client, $account, WC_Payments::get_database_cache(), $session_service, $order_service );
					$token_service                        = new WC_Payments_Token_Service( $api_client, $customer_service );
					$compatibility_service                = new Compatibility_Service( $api_client );
					$action_scheduler_service             = new WC_Payments_Action_Scheduler_Service( $api_client, $order_service, $compatibility_service );
					$failed_transaction_rate_limiter      = new WCPay\Session_Rate_Limiter( WCPay\Session_Rate_Limiter::SESSION_KEY_DECLINED_CARD_REGISTRY, 5, 10 * MINUTE_IN_SECONDS );
					$duplicate_payment_prevention_service = new WCPay\Duplicate_Payment_Prevention_Service();
					$localization_service                 = new WC_Payments_Localization_Service();
					$duplicate_detection_service = new Duplicates_Detection_Service();

					$fraud_service = new WC_Payments_Fraud_Service( $api_client, $customer_service, $account, $session_service, WC_Payments::get_database_cache() );

					$payment_method_classes = new WCPay\Payment_Methods\CC_Payment_Method( $token_service );
					$payment_method                               = new $payment_method_classes( $token_service );
					$payment_methods[ $payment_method->get_id() ] = $payment_method;

					$gateway = new WC_Payment_Gateway_WCPay( $api_client, $account, $customer_service, $token_service, $action_scheduler_service, $payment_method, $payment_methods, $order_service, $duplicate_payment_prevention_service, $localization_service, $fraud_service, $duplicate_detection_service, $failed_transaction_rate_limiter );

					$customer_id = $order_service->get_customer_id_for_order( $renewal_order );

					$payment_information = new Payment_Information( '', $renewal_order, Payment_Type::RECURRING(), $token, Payment_Initiated_By::MERCHANT(), null, null, '', $gateway->get_payment_method_to_use_for_intent(), $customer_id );
					$response            = $gateway->process_payment_for_order( null, $payment_information );

					if ( is_array( $response ) && 'success' === $response['result'] ) {
						$renewal_order->update_status( 'processing' );
					}
				} catch ( API_Exception $e ) {
					Logger::error( 'Error processing subscription renewal: ' . $e->getMessage() );
					/* translators: %s: renewal order id order_id */
					$renewal_order->add_order_note( sprintf( __( 'Error processing order - %s', 'woocommerce-subscriptions-pro' ), $order_id ) );

					if ( ! empty( $payment_information ) ) {
						$note = sprintf(
							WC_Payments_Utils::esc_interpolated_html(
							/* translators: %1: the failed payment amount, %2: error message  */
								__(
									'A payment of %1$s <strong>failed</strong> to complete with the following message: <code>%2$s</code>.',
									'woocommerce-subscriptions-pro'
								),
								array(
									'strong' => '<strong>',
									'code'   => '<code>',
								)
							),
							wc_price( $renewal_order->get_total(), array( 'currency' => WC_Payments_Utils::get_order_intent_currency( $renewal_order ) ) ),
							esc_html( rtrim( $e->getMessage(), '.' ) )
						);
						$renewal_order->add_order_note( $note );
					}
				}
			}
		}
	}
}
return new Wps_Subscriptions_Payment_Woocommercepayments_Main();
