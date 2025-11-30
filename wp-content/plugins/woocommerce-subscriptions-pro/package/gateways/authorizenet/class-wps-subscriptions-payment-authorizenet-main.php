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

if ( ! class_exists( 'Wps_Subscriptions_Payment_Authorizenet_Main' ) ) {
	/**
	 * Define class and module for Authorize Method.
	 */
	class Wps_Subscriptions_Payment_Authorizenet_Main {
		/**
		 * Constructor
		 */
		public function __construct() {

			add_filter( 'wps_sfw_supported_payment_gateway_for_woocommerce', array( $this, 'wps_authorizenet_integration_gateway' ), 10, 2 );
			add_action( 'wps_sfw_subscription_cancel', array( $this, 'wps_sfw_cancel_authorizenet_subscription' ), 10, 2 );
			add_filter( 'woocommerce_valid_order_statuses_for_payment_complete', array( $this, 'wps_sfw_add_authorizenet_order_statuses_for_payment_complete' ), 10, 2 );
			add_action( 'wps_sfw_other_payment_gateway_renewal', array( $this, 'wps_sfw_process_authorizenet_payment_renewal' ), 10, 3 );
			add_filter( 'wc_authnet_request_args', array( $this, 'wps_sfw_store_data_from_authoizenet_payment' ), 10, 2 );
		}

		/**
		 * Wps_sfw_store_data_from_authoizenet_payment function
		 *
		 * @param array $payment_args as payment_args.
		 * @param array $order as order.
		 * @return array
		 */
		public function wps_sfw_store_data_from_authoizenet_payment( $payment_args, $order ) {

			$order_id = $order->get_id();
			foreach ( $payment_args as $key => $value ) {

				if ( 'x_card_num' == $key ) {
					wps_wsp_update_meta_data( $order_id, 'wps_sfw_auth_card_name', $value );
				}
				if ( 'x_exp_date' == $key ) {
					wps_wsp_update_meta_data( $order_id, 'wps_sfw_auth_exp_date', $value );
				}
				if ( 'x_card_code' == $key ) {
					wps_wsp_update_meta_data( $order_id, 'wps_sfw_auth_card_code', $value );
				}
				if ( 'x_description' == $key ) {
					wps_wsp_update_meta_data( $order_id, 'wps_sfw_auth_description', $value );
				}
				if ( 'x_type' == $key ) {
					wps_wsp_update_meta_data( $order_id, 'wps_sfw_auth_type', $value );
				}
			}
			return $payment_args;
		}

		/**
		 * Replace the main gateway with the sources gateway.
		 *
		 * @param array  $supported_payment_method supported_payment_method.
		 * @param string $payment_method payment_method.
		 * @since 2.0.0
		 * @return array
		 */
		public function wps_authorizenet_integration_gateway( $supported_payment_method, $payment_method ) {

			if ( strpos( $payment_method, 'authnet' ) !== false ) {

				$supported_payment_method[] = $payment_method;
			}
			return $supported_payment_method;
		}
		/**
		 * This function is used to cancel subscriptions status.
		 *
		 * @name wps_sfw_cancel_authorizenet_subscription
		 * @param int    $wps_subscription_id mwb_subscription_id.
		 * @param string $status status.
		 * @since    1.0.1
		 */
		public function wps_sfw_cancel_authorizenet_subscription( $wps_subscription_id, $status ) {

			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
				$subscription = new WPS_Subscription( $wps_subscription_id );
				$wps_payment_method = $subscription->get_payment_method();
			} else {
				$wps_payment_method = get_post_meta( $wps_subscription_id, '_payment_method', true );
			}
			if ( strpos( $wps_payment_method, 'authnet' ) !== false ) {
				if ( 'Cancel' == $status ) {
					wps_sfw_send_email_for_cancel_susbcription( $wps_subscription_id );
					wps_wsp_update_meta_data( $wps_subscription_id, 'wps_subscription_status', 'cancelled' );
				}
			}
		}
		/**
		 * This function is add subscription order status.
		 *
		 * @name wps_sfw_add_authorizenet_order_statuses_for_payment_complete
		 * @param array  $order_status order_status.
		 * @param object $order order.
		 * @since 2.0.0
		 */
		public function wps_sfw_add_authorizenet_order_statuses_for_payment_complete( $order_status, $order ) {
			if ( $order && is_object( $order ) ) {

				$order_id = $order->get_id();
				$payment_method = $order->get_payment_method();
				$mwb_sfw_renewal_order = wps_wsp_get_meta_data( $order_id, 'wps_sfw_renewal_order', true );
				if ( strpos( $payment_method, 'authnet' ) !== false && 'yes' == $mwb_sfw_renewal_order ) {
					$order_status[] = 'wps_renewal';

				}
			}
			return apply_filters( 'wps_sfw_add_subscription_order_statuses_for_payment_complete', $order_status, $order );
		}
		/**
		 * This function is used to process payment from eway gateway.
		 *
		 * @param object $renewal_order renewal_order.
		 * @param int    $subscription_id subscription_id.
		 * @param string $payment_method payment_method.
		 * @since 2.0.0
		 * @throws Exception As handling expception.
		 * @return void
		 */
		public function wps_sfw_process_authorizenet_payment_renewal( $renewal_order, $subscription_id, $payment_method ) {

			if ( strpos( $payment_method, 'authnet' ) !== false ) {
				if ( ! $renewal_order ) {
					return;
				}
				$payment_method_obj = '';
				$wps_enabled_gateways = WC()->payment_gateways->get_available_payment_gateways();
				if ( isset( $wps_enabled_gateways[ $payment_method ] ) ) {
					$payment_method_obj = $wps_enabled_gateways[ $payment_method ];
				}
				if ( empty( $payment_method_obj ) ) {
					return;
				}
				$order_id        = $renewal_order->get_id();
				$parent_order_id = wps_wsp_get_meta_data( $subscription_id, 'wps_parent_order', true );
				$wps_sfw_auth_card_name = wps_wsp_get_meta_data( $parent_order_id, 'wps_sfw_auth_card_name', true );
				$wps_sfw_auth_exp_date = wps_wsp_get_meta_data( $parent_order_id, 'wps_sfw_auth_exp_date', true );
				$wps_sfw_auth_card_code = wps_wsp_get_meta_data( $parent_order_id, 'wps_sfw_auth_card_code', true );
				$wps_sfw_auth_description = wps_wsp_get_meta_data( $parent_order_id, 'wps_sfw_auth_description', true );
				$wps_sfw_auth_type = wps_wsp_get_meta_data( $parent_order_id, 'wps_sfw_auth_type', true );

				$wps_wsp_payment_args = array(
					'x_card_num'            => $wps_sfw_auth_card_name,
					'x_exp_date'            => $wps_sfw_auth_exp_date,
					'x_card_code'           => $wps_sfw_auth_card_code,
					'x_description'         => $wps_sfw_auth_description,
					'x_amount'              => $renewal_order->get_total(),
					'x_type'                => $wps_sfw_auth_type,
					'x_first_name'          => wps_wsp_get_meta_data( $subscription_id, '_billing_first_name', true ),
					'x_last_name'           => wps_wsp_get_meta_data( $subscription_id, '_billing_last_name', true ),
					'x_address'             => wps_wsp_get_meta_data( $subscription_id, '_billing_address_1', true ),
					'x_city'                => wps_wsp_get_meta_data( $subscription_id, '_billing_city', true ),
					'x_state'               => wps_wsp_get_meta_data( $subscription_id, '_billing_state', true ),
					'x_country'             => wps_wsp_get_meta_data( $subscription_id, '_billing_country', true ),
					'x_zip'                 => wps_wsp_get_meta_data( $subscription_id, '_billing_postcode', true ),
					'x_email'               => wps_wsp_get_meta_data( $subscription_id, '_billing_email', true ),
					'x_phone'               => wps_wsp_get_meta_data( $subscription_id, '_billing_phone', true ),
					'x_company'             => wps_wsp_get_meta_data( $subscription_id, '_billing_company', true ),
					'x_invoice_num'         => $renewal_order->get_order_number(),
					'x_trans_id'            => $renewal_order->get_transaction_id(),
					'x_customer_ip'         => WC_Geolocation::get_ip_address(),
					'x_currency_code'       => $renewal_order->get_currency(),
					'x_tax'                 => $renewal_order->get_total_tax(),
					'x_freight'             => $renewal_order->get_shipping_total(),
					'x_email_customer'      => '',

				);

				$line_items = array();
				foreach ( $renewal_order->get_items() as $item ) {
					$product = $item->get_product();
					if ( ! is_object( $product ) ) {
						continue;
					}
					$line_item['id'] = $product->get_id();
					$line_item['name'] = $item->get_name();
					$line_item['description'] = '';
					$line_item['quantity'] = $item->get_quantity();
					$line_item['unit_price'] = $renewal_order->get_item_total( $item );
					$line_item['taxable'] = $product->is_taxable();

					$line_items[] = $line_item;

					if ( count( $line_items ) >= 30 ) {
						break;
					}
				}
				$wps_wsp_payment_args['line_items'] = $line_items;

				$gateway = new WC_Gateway_Authnet();
				try {

					$response = $gateway->authnet_request( $wps_wsp_payment_args );
					if ( is_wp_error( $response ) ) {
						throw new Exception( $response->get_error_message() );
					}
					// Store charge ID.
					$renewal_order->update_meta_data( '_authnet_charge_id', $response['transaction_id'] );
					$renewal_order->update_meta_data( '_authnet_authorization_code', $response['authorization_code'] );

					$renewal_order->set_transaction_id( $response['transaction_id'] );

					if ( 'AUTH_CAPTURE' == $wps_wsp_payment_args['x_type'] && 4 != $response['response_code'] ) {

						// Store captured value.
						$renewal_order->update_meta_data( '_authnet_charge_captured', 'yes' );
						$renewal_order->update_meta_data( 'Authorize.Net Payment ID', $response['transaction_id'] );

						// Payment complete.
						$renewal_order->payment_complete( $response['transaction_id'] );
						do_action( 'wps_sfw_recurring_payment_success', $order_id );

						// Add order note.
						$complete_message = sprintf( __( "Authorize.Net charge complete (Charge ID: %1\$s) \n\nAVS Response: %2\$s \n\nCVV2 Response: %3\$s", 'woocommerce-subscriptions-pro' ), $response['transaction_id'], self::get_avs_message( $response['avs_response'] ), self::get_cvv_message( $response['card_code_response'] ) );
						$renewal_order->add_order_note( $complete_message );

					} else {

						// Store captured value.
						$renewal_order->update_meta_data( '_authnet_charge_captured', 'no' );
						$renewal_order->update_meta_data( '_transaction_id', $response['transaction_id'] );

						if ( 4 == $response['response_code'] ) {
							$renewal_order->update_meta_data( '_authnet_fds_hold', 'yes' );
						}

						if ( $renewal_order->has_status( array( 'pending', 'failed' ) ) ) {
							wc_reduce_stock_levels( $order_id );
						}
					}
				} catch ( Exception $e ) {
					do_action( 'wps_sfw_recurring_payment_failed', $order_id );
					// translators: placeholder subscription id.
					echo esc_html( 'Error processing subscription renewal #: ', 'woocommerce-subscriptions-pro' ) . esc_html( $subscription_id ) . esc_html( 'subscription_payment', 'woocommerce-subscriptions-pro' );
					// translators: placeholder order id.
					$renewal_order->add_order_note( esc_html( 'Error processing order - %s', 'woocommerce-subscriptions-pro' ) . esc_html( $order_id ) );
					// translators: placeholder error message.
					echo esc_html( $e->getMessage() );
				}
			}
		}
		/**
		 * Get_avs_message function.
		 *
		 * @param string $code as code.
		 * @return string
		 */
		public function get_avs_message( $code ) {
			$avs_messages = array(
				'A' => __( 'Street Address: Match -- First 5 Digits of ZIP: No Match', 'woocommerce-subscriptions-pro' ),
				'B' => __( 'Address not provided for AVS check or street address match, postal code could not be verified', 'woocommerce-subscriptions-pro' ),
				'E' => __( 'AVS Error', 'woocommerce-subscriptions-pro' ),
				'G' => __( 'Non U.S. Card Issuing Bank', 'woocommerce-subscriptions-pro' ),
				'N' => __( 'Street Address: No Match -- First 5 Digits of ZIP: No Match', 'woocommerce-subscriptions-pro' ),
				'P' => __( 'AVS not applicable for this transaction', 'woocommerce-subscriptions-pro' ),
				'R' => __( 'Retry, System Is Unavailable', 'woocommerce-subscriptions-pro' ),
				'S' => __( 'AVS Not Supported by Card Issuing Bank', 'woocommerce-subscriptions-pro' ),
				'U' => __( 'Address Information For This Cardholder Is Unavailable', 'woocommerce-subscriptions-pro' ),
				'W' => __( 'Street Address: No Match -- All 9 Digits of ZIP: Match', 'woocommerce-subscriptions-pro' ),
				'X' => __( 'Street Address: Match -- All 9 Digits of ZIP: Match', 'woocommerce-subscriptions-pro' ),
				'Y' => __( 'Street Address: Match - First 5 Digits of ZIP: Match', 'woocommerce-subscriptions-pro' ),
				'Z' => __( 'Street Address: No Match - First 5 Digits of ZIP: Match', 'woocommerce-subscriptions-pro' ),
			);
			if ( array_key_exists( $code, $avs_messages ) ) {
				return $code . ' - ' . $avs_messages[ $code ];
			} else {
				return $code;
			}
		}
		/**
		 * Get_cvv_message function.
		 *
		 * @param string $code as code.
		 * @return string
		 */
		public function get_cvv_message( $code ) {
			$cvv_messages = array(
				'M' => __( 'CVV2/CVC2 Match', 'woocommerce-subscriptions-pro' ),
				'N' => __( 'CVV2 / CVC2 No Match', 'woocommerce-subscriptions-pro' ),
				'P' => __( 'Not Processed', 'woocommerce-subscriptions-pro' ),
				'S' => __( 'Merchant Has Indicated that CVV2 / CVC2 is not present on card', 'woocommerce-subscriptions-pro' ),
				'U' => __( 'Issuer is not certified and/or has not provided visa encryption keys', 'woocommerce-subscriptions-pro' ),
			);
			if ( array_key_exists( $code, $cvv_messages ) ) {
				return $code . ' - ' . $cvv_messages[ $code ];
			} else {
				return $code;
			}
		}
	}

}
return new Wps_Subscriptions_Payment_Authorizenet_Main();
