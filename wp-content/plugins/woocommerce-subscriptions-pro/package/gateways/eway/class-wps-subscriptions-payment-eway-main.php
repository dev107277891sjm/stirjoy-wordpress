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

if ( ! class_exists( 'Wps_Subscriptions_Payment_Eway_Main' ) ) {

	/**
	 * Define class and module for Eway Method.
	 */
	class Wps_Subscriptions_Payment_Eway_Main {
		/**
		 * Constructor
		 */
		public function __construct() {
			$woocommerce_eway_settings = get_option( 'woocommerce_eway_settings' );
			if ( ! empty( $woocommerce_eway_settings ) && 'yes' == $woocommerce_eway_settings['enabled'] ) {

				add_action( 'wps_sfw_renew_order_saved', array( $this, 'wps_sfw_save_eway_on_renew_order' ), 10, 2 );
				add_filter( 'wps_sfw_supported_payment_gateway_for_woocommerce', array( $this, 'wps_eway_integration_gateway' ), 10, 2 );
				add_action( 'wps_sfw_subscription_cancel', array( $this, 'wps_sfw_cancel_eway_subscription' ), 10, 2 );
				add_filter( 'woocommerce_valid_order_statuses_for_payment_complete', array( $this, 'wps_sfw_add_eway_order_statuses_for_payment_complete' ), 10, 2 );
				add_action( 'wps_sfw_other_payment_gateway_renewal', array( $this, 'wps_sfw_process_eway_payment_renewal' ), 10, 3 );
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
		public function wps_eway_integration_gateway( $supported_payment_method, $payment_method ) {
			if ( 'eway' === $payment_method ) {

				$supported_payment_method[] = $payment_method;
			}
			return $supported_payment_method;
		}
		/**
		 * Copy the eway payment data inside the new order.
		 *
		 * @param WC_Order             $order Renew order.
		 * @param MWb_sfw_Subscription $subscription Subscription.
		 */
		public function wps_sfw_save_eway_on_renew_order( $order, $subscription ) {
			$payment_method = $subscription->get_payment_method();
			if ( $payment_method === $this->id ) {
				$order->update_meta_data( '_eway_token_customer_id', $subscription->get( '_eway_token_customer_id' ) );
				$order->save();
			}
		}
		/**
		 * This function is used to cancel subscriptions status.
		 *
		 * @name mwb_sfw_cancel_eway_subscription
		 * @param int    $wps_subscription_id mwb_subscription_id.
		 * @param string $status status.
		 * @since    1.0.1
		 */
		public function wps_sfw_cancel_eway_subscription( $wps_subscription_id, $status ) {

			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
				$subscription = new WPS_Subscription( $wps_subscription_id );
				$wps_payment_method = $subscription->get_payment_method();
			} else {
				$wps_payment_method = get_post_meta( $wps_subscription_id, '_payment_method', true );
			}

			if ( strpos( $wps_payment_method, 'eway' ) !== false ) {
				if ( 'Cancel' == $status ) {
					wps_sfw_send_email_for_cancel_susbcription( $wps_subscription_id );
					wps_wsp_update_meta_data( $wps_subscription_id, 'wps_subscription_status', 'cancelled' );
				}
			}
		}
		/**
		 * This function is add subscription order status.
		 *
		 * @name mwb_sfw_add_eway_order_statuses_for_payment_complete
		 * @param array  $order_status order_status.
		 * @param object $order order.
		 * @since 2.0.0
		 */
		public function wps_sfw_add_eway_order_statuses_for_payment_complete( $order_status, $order ) {
			if ( $order && is_object( $order ) ) {

				$order_id = $order->get_id();
				$payment_method = $order->get_payment_method();
				$mwb_sfw_renewal_order = wps_wsp_get_meta_data( $order_id, 'wps_sfw_renewal_order', true );
				if ( strpos( $payment_method, 'eway' ) !== false && 'yes' == $mwb_sfw_renewal_order ) {
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
		 * @return void
		 */
		public function wps_sfw_process_eway_payment_renewal( $renewal_order, $subscription_id, $payment_method ) {

			if ( strpos( $payment_method, 'eway' ) !== false ) {
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
				$order_id        = $renewal_order->get_id();
				$parent_order_id = wps_wsp_get_meta_data( $subscription_id, 'wps_parent_order', false );
				$eway_token_customer_id = wps_wsp_get_meta_data( $parent_order_id[0], '_eway_token_customer_id', true );
				// Charge the customer.
				try {
					return $this->process_eway_payment_request( $renewal_order, $renewal_order->get_total(), $eway_token_customer_id );
				} catch ( Exception $e ) {
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
		 * Make a direct payment through the API for an order and handle the result.
		 *
		 * @param WC_Order   $order The order to process.
		 * @param int        $amount_to_charge The amount to charge for the order.
		 * @param int|string $eway_token_customer_id The customer's TokenCustomerID to include in direct payment request.
		 * @return null|object JSON parsed API response on success, or null on failure
		 * @throws Exception If order does not exist or if payment gateway fails.
		 */
		public function process_eway_payment_request( $order, $amount_to_charge, $eway_token_customer_id ) {
			$order_id = $order->get_id();
			if ( ! class_exists( 'WC_Gateway_EWAY' ) ) {
				return;
			}

			self::log( $order_id . ': Processing payment request' );
			$eway_pay = new WC_Gateway_EWAY();
			$result = json_decode( $eway_pay->get_api()->direct_payment( $order, $eway_token_customer_id, $amount_to_charge * 100.00 ) );

			if ( intval( $result->Payment->InvoiceReference ) !== $order_id ) {
				throw new Exception( esc_html__( 'Order does not exist.', 'woocommerce-subscriptions-pro' ) );
			}

			switch ( $result->ResponseMessage ) {
				case 'A2000':
				case 'A2008':
				case 'A2010':
				case 'A2011':
				case 'A2016':
					self::log( $order_id . ': Processing payment completed' );
					// translators: %s Response message.
					$order->add_order_note( sprintf( __( 'Eway token payment completed - %s', 'woocommerce-subscriptions-pro' ), $this->response_message_lookup( $result->ResponseMessage ) ) );
					$this->set_token_customer_id( $order, $eway_token_customer_id );
					$order->payment_complete( $result->TransactionID );
					do_action( 'wps_sfw_recurring_payment_success', $order_id );

					/**
					 * Triggered when a payment with the gateway is completed.
					 *
					 * @param WC_Order        $order The order whose payment failed.
					 * @param stdClass        $result The result from the API call.
					 * @param WC_Gateway_EWAY $gateway The instance of the gateway.
					 */
					do_action( 'woocommerce_api_wc_gateway_eway_payment_completed', $order, $result, $this );
					break;
				default:
					do_action( 'wps_sfw_recurring_payment_failed', $order_id );
					self::log( $order_id . ': Processing payment failed' );
					if ( isset( $result->Errors ) && ! is_null( $result->Errors ) ) {
						$error = $this->response_message_lookup( $result->Errors );
					} else {
						$error = $this->response_message_lookup( $result->ResponseMessage );
					}

					// translators: %s Error message.
					$order->update_status( 'failed', sprintf( __( 'Eway token payment failed - %s', 'woocommerce-subscriptions-pro' ), $error ) );

					/**
					 * Triggered when a payment with the gateway fails.
					 *
					 * @param WC_Order        $order The order whose payment failed.
					 * @param stdClass        $result The result from the API call.
					 * @param string          $error The error message.
					 * @param WC_Gateway_EWAY $gateway The instance of the gateway.
					 */
					do_action( 'woocommerce_api_wc_gateway_eway_payment_failed', $order, $result, $error, $this );

					throw new Exception( $error );
			}

			return $result;
		}

		/**
		 * Get the Eway API object.
		 *
		 * @return object WC_EWAY_API
		 */
		public function get_api() {
			if ( is_object( $this->api ) ) {
				return $this->api;
			}

			require WOOCOMMERCE_GATEWAY_EWAY_PATH . '/includes/class-wc-eway-api.php';

			$this->api = new WC_EWAY_API( $this->customer_api, $this->customer_password, 'yes' === $this->testmode ? 'sandbox' : 'production', $this->debug_mode );

			return $this->api;
		}

		/**
		 * Logger instance.
		 *
		 * @var WC_Logger
		 */
		public static $log = false;

		/**
		 * Logging method.
		 *
		 * @param string $message Message to log.
		 */
		public static function log( $message ) {
			if ( empty( self::$log ) ) {
				self::$log = new WC_Logger();
			}
				self::$log->add( 'eway', $message );
		}
		/**
		 * Save the token customer id on the order being made.
		 *
		 * @param  WC_Order $order The order being made.
		 * @param  int      $token_customer_id The token customer id to associate with order.
		 */
		protected function set_token_customer_id( $order, $token_customer_id ) {

			wps_wsp_update_meta_data( $order->get_id(), '_eway_token_customer_id', $token_customer_id );
		}

		/**
		 * Lookup Response / Error messages based on codes.
		 *
		 * @param  string $response_message Response code from API.
		 * @return string
		 */
		public function response_message_lookup( $response_message ) {
			$messages = array(
				'A2000' => 'Transaction Approved',
				'A2008' => 'Honour With Identification',
				'A2010' => 'Approved For Partial Amount',
				'A2011' => 'Approved, VIP',
				'A2016' => 'Approved, Update Track 3',
				'D4401' => 'Refer to Issuer',
				'D4402' => 'Refer to Issuer, special',
				'D4403' => 'No Merchant',
				'D4404' => 'Pick Up Card',
				'D4405' => 'Do Not Honour',
				'D4406' => 'Error',
				'D4407' => 'Pick Up Card, Special',
				'D4409' => 'Request In Progress',
				'D4412' => 'Invalid Transaction',
				'D4413' => 'Invalid Amount',
				'D4414' => 'Invalid Card Number',
				'D4415' => 'No Issuer',
				'D4419' => 'Re-enter Last Transaction',
				'D4421' => 'No Action Taken',
				'D4422' => 'Suspected Malfunction',
				'D4423' => 'Unacceptable Transaction Fee',
				'D4425' => 'Unable to Locate Record On File',
				'D4430' => 'Format Error',
				'D4431' => 'Bank Not Supported By Switch',
				'D4433' => 'Expired Card, Capture',
				'D4434' => 'Suspected Fraud, Retain Card',
				'D4435' => 'Card Acceptor, Contact Acquirer, Retain Card',
				'D4436' => 'Restricted Card, Retain Card',
				'D4437' => 'Contact Acquirer Security Department, Retain Card',
				'D4438' => 'PIN Tries Exceeded, Capture',
				'D4439' => 'No Credit Account',
				'D4440' => 'Function Not Supported',
				'D4441' => 'Lost Card',
				'D4442' => 'No Universal Account',
				'D4443' => 'Stolen Card',
				'D4444' => 'No Investment Account',
				'D4451' => 'Insufficient Funds',
				'D4452' => 'No Cheque Account',
				'D4453' => 'No Savings Account',
				'D4454' => 'Expired Card',
				'D4455' => 'Incorrect PIN',
				'D4456' => 'No Card Record',
				'D4457' => 'Function Not Permitted to Cardholder',
				'D4458' => 'Function Not Permitted to Terminal',
				'D4459' => 'Suspected Fraud',
				'D4460' => 'Acceptor Contact Acquirer',
				'D4461' => 'Exceeds Withdrawal Limit',
				'D4462' => 'Restricted Card',
				'D4463' => 'Security Violation',
				'D4464' => 'Original Amount Incorrect',
				'D4466' => 'Acceptor Contact Acquirer, Security',
				'D4467' => 'Capture Card',
				'D4475' => 'PIN Tries Exceeded',
				'D4482' => 'CVV Validation Error',
				'D4490' => 'Cut off In Progress',
				'D4491' => 'Card Issuer Unavailable',
				'D4492' => 'Unable To Route Transaction',
				'D4493' => 'Cannot Complete, Violation Of The Law',
				'D4494' => 'Duplicate Transaction',
				'D4496' => 'System Error',
				'D4497' => 'MasterPass Error',
				'D4498' => 'PayPal Create Transaction Error',
				'S5000' => 'System Error',
				'S5085' => 'Started 3dSecure',
				'S5086' => 'Routed 3dSecure',
				'S5087' => 'Completed 3dSecure',
				'S5088' => 'PayPal Transaction Created',
				'S5099' => 'Incomplete (Access Code in progress/incomplete)',
				'S5010' => 'Unknown error returned by gateway',
				'V6000' => 'Validation error',
				'V6001' => 'Invalid CustomerIP',
				'V6002' => 'Invalid DeviceID',
				'V6003' => 'Invalid Request PartnerID',
				'V6004' => 'Invalid Request Method',
				'V6010' => 'Invalid TransactionType, account not certified for eCome only MOTO or Recurring available',
				'V6011' => 'Invalid Payment TotalAmount',
				'V6012' => 'Invalid Payment InvoiceDescription',
				'V6013' => 'Invalid Payment InvoiceNumber',
				'V6014' => 'Invalid Payment InvoiceReference',
				'V6015' => 'Invalid Payment CurrencyCode',
				'V6016' => 'Payment Required',
				'V6017' => 'Payment CurrencyCode Required',
				'V6018' => 'Unknown Payment CurrencyCode',
				'V6021' => 'EWAY_CARDHOLDERNAME Required',
				'V6022' => 'EWAY_CARDNUMBER Required',
				'V6023' => 'EWAY_CARDCVN Required',
				'V6033' => 'Invalid Expiry Date',
				'V6034' => 'Invalid Issue Number',
				'V6035' => 'Invalid Valid From Date',
				'V6040' => 'Invalid TokenCustomerID',
				'V6041' => 'Customer Required',
				'V6042' => 'Customer FirstName Required',
				'V6043' => 'Customer LastName Required',
				'V6044' => 'Customer CountryCode Required',
				'V6045' => 'Customer Title Required',
				'V6046' => 'TokenCustomerID Required',
				'V6047' => 'RedirectURL Required',
				'V6051' => 'Invalid Customer FirstName',
				'V6052' => 'Invalid Customer LastName',
				'V6053' => 'Invalid Customer CountryCode',
				'V6058' => 'Invalid Customer Title',
				'V6059' => 'Invalid RedirectURL',
				'V6060' => 'Invalid TokenCustomerID',
				'V6061' => 'Invalid Customer Reference',
				'V6062' => 'Invalid Customer CompanyName',
				'V6063' => 'Invalid Customer JobDescription',
				'V6064' => 'Invalid Customer Street1',
				'V6065' => 'Invalid Customer Street2',
				'V6066' => 'Invalid Customer City',
				'V6067' => 'Invalid Customer State',
				'V6068' => 'Invalid Customer PostalCode',
				'V6069' => 'Invalid Customer Email',
				'V6070' => 'Invalid Customer Phone',
				'V6071' => 'Invalid Customer Mobile',
				'V6072' => 'Invalid Customer Comments',
				'V6073' => 'Invalid Customer Fax',
				'V6074' => 'Invalid Customer URL',
				'V6075' => 'Invalid ShippingAddress FirstName',
				'V6076' => 'Invalid ShippingAddress LastName',
				'V6077' => 'Invalid ShippingAddress Street1',
				'V6078' => 'Invalid ShippingAddress Street2',
				'V6079' => 'Invalid ShippingAddress City',
				'V6080' => 'Invalid ShippingAddress State',
				'V6081' => 'Invalid ShippingAddress PostalCode',
				'V6082' => 'Invalid ShippingAddress Email',
				'V6083' => 'Invalid ShippingAddress Phone',
				'V6084' => 'Invalid ShippingAddress Country',
				'V6085' => 'Invalid ShippingAddress ShippingMethod',
				'V6086' => 'Invalid ShippingAddress Fax ',
				'V6091' => 'Unknown Customer CountryCode',
				'V6092' => 'Unknown ShippingAddress CountryCode',
				'V6100' => 'Invalid EWAY_CARDNAME',
				'V6101' => 'Invalid EWAY_CARDEXPIRYMONTH',
				'V6102' => 'Invalid EWAY_CARDEXPIRYYEAR',
				'V6103' => 'Invalid EWAY_CARDSTARTMONTH',
				'V6104' => 'Invalid EWAY_CARDSTARTYEAR',
				'V6105' => 'Invalid EWAY_CARDISSUENUMBER',
				'V6106' => 'Invalid EWAY_CARDCVN',
				'V6107' => 'Invalid EWAY_ACCESSCODE',
				'V6108' => 'Invalid CustomerHostAddress',
				'V6109' => 'Invalid UserAgent',
				'V6110' => 'Invalid EWAY_CARDNUMBER',
				'V6111' => 'Unauthorised API Access, Account Not PCI Certified',
			);
			return isset( $messages[ $response_message ] ) ? $messages[ $response_message ] : $response_message;
		}
	}
}
return new Wps_Subscriptions_Payment_Eway_Main();
