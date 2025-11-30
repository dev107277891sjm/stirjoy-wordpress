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

if ( ! class_exists( 'Wps_Subscriptions_Payment_Braintree_Main' ) ) {
	/**
	 * Define class and module for Amazon Pay Method.
	 */
	class Wps_Subscriptions_Payment_Braintree_Main {

		/**
		 * Constructor
		 */
		public function __construct() {

			add_filter( 'wps_sfw_supported_payment_gateway_for_woocommerce', array( $this, 'wps_braintree_pay_payment_integration_gateway' ), 10, 2 );
			add_action( 'wps_sfw_subscription_cancel', array( $this, 'wps_sfw_cancel_braintree_subscription' ), 10, 2 );
			add_action( 'wc_payment_gateway_braintree_credit_card_add_transaction_data', array( $this, 'wps_wsp_save_payment_meta' ), 10, 2 );
			add_action( 'wps_sfw_other_payment_gateway_renewal', array( $this, 'wps_sfw_process_braintree_payment_renewal' ), 10, 3 );
			add_filter( 'wc_payment_gateway_braintree_credit_card_tokenization_forced', array( $this, 'wps_sfw_maybe_force_tokenization' ), 10, 1 );
		}

		/**
		 * Function to charge Renewal order payment from braintree.
		 *
		 * @param array $renewal_order as renewal order.
		 * @param int   $subscription_id as subscription id.
		 * @param array $payment_method as payment method.
		 * @return void
		 */
		public function wps_sfw_process_braintree_payment_renewal( $renewal_order, $subscription_id, $payment_method ) {

			if ( strpos( $payment_method, 'braintree_credit_card' ) !== false ) {

				$gateway = $this->wsp_wsp_create_gateway( $renewal_order, $subscription_id, $payment_method );

			}
		}

		/**
		 * Function to charge final payment for renewal order.
		 *
		 * @param array $renewal_order as renewal order.
		 * @param int   $subscription_id as subscription id.
		 * @param array $payment_method as payment method.
		 * @return void
		 */
		public function wsp_wsp_create_gateway( $renewal_order, $subscription_id, $payment_method ) {

			require_once WP_PLUGIN_DIR . '/woocommerce-gateway-paypal-powered-by-braintree/vendor/braintree/braintree_php/lib/Braintree.php';

			$woocommerce_braintree_credit_card_settings = get_option( 'woocommerce_braintree_credit_card_settings' );

			$wps_braintree_environment = $woocommerce_braintree_credit_card_settings['environment'];
			if ( 'sandbox' == $wps_braintree_environment ) {

				$merchant_id = $woocommerce_braintree_credit_card_settings['sandbox_merchant_id'];
				$public_key = $woocommerce_braintree_credit_card_settings['sandbox_public_key'];
				$private_key = $woocommerce_braintree_credit_card_settings['sandbox_private_key'];

			} elseif ( 'production' == $wps_braintree_environment ) {

				$merchant_id = $woocommerce_braintree_credit_card_settings['merchant_id'];
				$public_key = $woocommerce_braintree_credit_card_settings['public_key'];
				$private_key = $woocommerce_braintree_credit_card_settings['private_key'];

			}

			$gateway = new Braintree\Gateway(
				array(

					'environment' => $wps_braintree_environment,
					'merchantId' => $merchant_id,
					'publicKey' => $public_key,
					'privateKey' => $private_key,

				)
			);

			$_wc_braintree_credit_card_payment_token = wps_wsp_get_meta_data( $subscription_id, '_wc_braintree_credit_card_payment_token', true );

			$result = $gateway->transaction()->sale(
				array(
					'amount' => $renewal_order->get_total(),
					'paymentMethodToken' => $_wc_braintree_credit_card_payment_token,
				)
			);

			$result = $gateway->transaction()->submitForSettlement( $result->transaction->id );
			if ( $result->success ) {

				$settledTransaction = $result->transaction;

				Subscriptions_For_Woocommerce_Log::log( 'WPS Renewal For Braintree settledTransaction: ' . wc_print_r( $settledTransaction, true ) );
				/* translators: %s: transaction id */
				$renewal_order->add_order_note( sprintf( __( 'renewal order completed from braintree with transaction id - %s', 'woocommerce-subscriptions-pro' ), $result->transaction->id ) );
				$renewal_order->set_transaction_id( $result->transaction->id );
				$renewal_order->update_status( 'processing' );

			} else {
				Subscriptions_For_Woocommerce_Log::log( 'WPS Renewal For Braintree failed: ' . wc_print_r( $result->errors, true ) );
				/* translators: %s: error */
				$renewal_order->add_order_note( sprintf( __( 'renewal order completed from braintree with transaction id - %s', 'woocommerce-subscriptions-pro' ), $result->errors ) );

			}
		}

		/**
		 * Function to save meta data in subscription order.
		 *
		 * @param array $order as order.
		 * @return void
		 */
		public function wps_wsp_save_payment_meta( $order ) {

			$order_id = $order->get_id();
			$wps_subscription_id = wps_wsp_get_meta_data( $order_id, 'wps_subscription_id', true );

			// payment token.
			if ( ! empty( $order->payment->token ) ) {
				wps_wsp_update_meta_data( $wps_subscription_id, '_wc_braintree_credit_card_payment_token', $order->payment->token );
			}

			// customer ID.
			if ( ! empty( $order->customer_id ) ) {
				wps_wsp_update_meta_data( $wps_subscription_id, '_wc_braintree_credit_card_customer_id', $order->customer_id );
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
		public function wps_braintree_pay_payment_integration_gateway( $supported_payment_method, $payment_method ) {

			if ( strpos( $payment_method, 'braintree_credit_card' ) !== false ) {

					$supported_payment_method[] = $payment_method;

			}
			return $supported_payment_method;
		}

		/**
		 * Function to cancel subscription when payment method is braintree.
		 *
		 * @param int   $wps_subscription_id as subscription id.
		 * @param array $status as status.
		 * @return void
		 */
		public function wps_sfw_cancel_braintree_subscription( $wps_subscription_id, $status ) {

			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
				$subscription = new WPS_Subscription( $wps_subscription_id );
				$wps_payment_method = $subscription->get_payment_method();
			} else {
				$wps_payment_method = get_post_meta( $wps_subscription_id, '_payment_method', true );
			}

			if ( strpos( $wps_payment_method, 'braintree_credit_card' ) !== false ) {
				if ( 'Cancel' == $status ) {
					wps_sfw_send_email_for_cancel_susbcription( $wps_subscription_id );
					wps_wsp_update_meta_data( $wps_subscription_id, 'wps_subscription_status', 'cancelled' );
				}
			}
		}

		/**
		 * Function to save force in case of subscripiton order.
		 *
		 * @param bool $force_tokenization as token bool.
		 * @return bool
		 */
		public function wps_sfw_maybe_force_tokenization( $force_tokenization ) {

			if ( ! empty( WC()->cart->cart_contents ) ) {
				foreach ( WC()->cart->cart_contents as $cart_item ) {
					if ( wps_sfw_check_product_is_subscription( $cart_item['data'] ) ) {
						return true;

					}
				}
			}
			return $force_tokenization;
		}
	}

}
return new Wps_Subscriptions_Payment_Braintree_Main();
