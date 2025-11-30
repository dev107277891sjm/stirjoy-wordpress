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

if ( ! class_exists( 'Wps_Subscriptions_Payment_Woostripe_Main' ) ) {

	/**
	 * Define class and module for woo stripe.
	 */
	class Wps_Subscriptions_Payment_Woostripe_Main {
		/**
		 * Constructor
		 */
		public function __construct() {

			if ( $this->wps_wsp_check_woo_stripe_enable() && wps_sfw_check_plugin_enable() ) {

				add_action( 'wps_sfw_subscription_cancel', array( $this, 'wps_wsp_cancel_woo_stripe_subscription' ), 10, 2 );

				add_filter( 'woocommerce_valid_order_statuses_for_payment_complete', array( $this, 'wps_wsp_add_stripe_order_statuses_for_payment_complete' ), 10, 2 );

				add_filter( 'wps_sfw_supported_payment_gateway_for_woocommerce', array( $this, 'wps_wsp_woo_stripe_payment_gateway_for_woocommerce' ), 10, 2 );
				add_action( 'wps_sfw_other_payment_gateway_renewal', array( $this, 'wps_wsp_woo_stripe_process_subscription_payment' ), 10, 3 );

				add_filter( 'wc_stripe_force_save_payment_method', array( $this, 'wps_wsp_woo_stripe_force_save_payment_method' ), 10, 3 );
				add_action( 'wc_stripe_before_process_payment', array( $this, 'wps_wsp_woo_stripe_should_save_payment_method' ), 10, 3 );

			}
		}

		/**
		 * Allow recurring payment.
		 *
		 * @name wps_wsp_woo_stripe_should_save_payment_method.
		 * @param object $order order.
		 * @param string $payment_method payment_method.
		 * @since 2.0.0
		 * @return void
		 */
		public function wps_wsp_woo_stripe_should_save_payment_method( $order, $payment_method ) {
			 $wps_enabled_gateways = WC()->payment_gateways->get_available_payment_gateways();
			if ( isset( $wps_enabled_gateways[ $payment_method ] ) ) {
				$payment_method_obj = $wps_enabled_gateways[ $payment_method ];
				if ( $payment_method_obj ) {
					if ( ! $payment_method_obj->use_saved_source() ) {
						$order_id = $order->get_id();
						$wps_has_subscription = wps_wsp_get_meta_data( $order_id, 'wps_sfw_order_has_subscription', true );
						if ( 'yes' == $wps_has_subscription && $order->get_total() == 0 ) {
							 $_POST[ "{$payment_method}_save_source_key" ] = 'yes';
						}
					}
				}
			}
		}


		/**
		 * Allow recurring payment.
		 *
		 * @name wps_wsp_woo_stripe_force_save_payment_method.
		 * @param bool   $bool bool.
		 * @param object $order order.
		 * @param string $payment_method payment_method.
		 * @since 2.0.0
		 * @return boolean
		 */
		public function wps_wsp_woo_stripe_force_save_payment_method(  bool $bool, $order, $payment_method = null ) {

			if ( ! $bool && wps_sfw_check_plugin_enable()  ) {
				if( $order ){
					$order_id = $order->get_id();
					$wps_has_subscription = wps_wsp_get_meta_data( $order_id, 'wps_sfw_order_has_subscription', true );
					if ( 'yes' == $wps_has_subscription ) {
						$bool = true;
					}

				}
			}
			return $bool;
		}

		/**
		 * Process subscription payment.
		 *
		 * @name wps_wsp_woo_stripe_process_subscription_payment.
		 * @param object $order order.
		 * @param int    $subscription_id subscription_id.
		 * @param string $payment_method payment_method.
		 * @since 2.0.0
		 * @return void
		 */
		public function wps_wsp_woo_stripe_process_subscription_payment( $order, $subscription_id, $payment_method ) {

			if ( $order && is_object( $order ) ) {
				$order_id = $order->get_id();
				$wps_sfw_renewal_order = wps_wsp_get_meta_data( $order_id, 'wps_sfw_renewal_order', true );
				if ( ! $this->wps_wsp_check_supported_payment_options( $payment_method ) || 'yes' != $wps_sfw_renewal_order ) {
					return;
				}
				$wps_enabled_gateways = WC()->payment_gateways->get_available_payment_gateways();
				if ( isset( $wps_enabled_gateways[ $payment_method ] ) ) {
					$payment_method_obj = $wps_enabled_gateways[ $payment_method ];
				}
				if ( empty( $payment_method_obj ) ) {
					return;
				}

				if ( class_exists( 'WC_Stripe_Payment_Intent' ) ) {

					$order->set_payment_method( $payment_method_obj );
					// Return if order total is zero.
					if ( 0 == $order->get_total() ) {
						$order->payment_complete();
						return;
					}
					$gateway  = new WC_Stripe_Gateway();
					$payment_intent_obj = new WC_Stripe_Payment_Intent( $payment_method_obj, $gateway );

					$result = $this->wps_wsp_process_woo_stripe_renewal_payment( $payment_method_obj, $gateway, $payment_intent_obj, $subscription_id, $order );

					if ( is_wp_error( $result ) ) {
						$order->update_status( 'failed' );
						/* translators: %s: error message */
						$order->add_order_note( sprintf( __( 'Recurring payment for order failed. Reason: %s', 'woocommerce-subscriptions-pro' ), $result->get_error_message() ) );
						do_action( 'wps_sfw_recurring_payment_failed', $order_id );
						return;
					}

					$payment_method_obj->save_order_meta( $order, $result->charge );

					// set the payment method token that was used to process the renewal order.
					$payment_method_obj->payment_method_token = $order->get_meta( WC_Stripe_Constants::PAYMENT_METHOD_TOKEN );

					if ( $result->complete_payment ) {
						if ( $result->charge->captured ) {
							$order->payment_complete( $result->charge->id );
							/* translators: %s: method title */
							$order->add_order_note( sprintf( __( 'Recurring payment captured in Stripe. Payment method: %s', 'woocommerce-subscriptions-pro' ), $order->get_payment_method_title() ) );
							do_action( 'wps_sfw_recurring_payment_success', $order_id );
						} else {
							/* translators: %s: method title */
							$order->update_status( apply_filters( 'wc_stripe_authorized_renewal_order_status', 'on-hold', $order, $this ), sprintf( __( 'Recurring payment authorized in Stripe. Payment method: %s', 'woocommerce-subscriptions-pro' ), $order->get_payment_method_title() ) );
						}
					} else {
						/* translators: %s: method title */
						$order->update_status( 'pending', sprintf( __( 'Customer must manually complete payment for payment method %s', 'woocommerce-subscriptions-pro' ), $order->get_payment_method_title() ) );
					}
				}
			}
		}

		/**
		 * Process recurring payment.
		 *
		 * @name wps_wsp_process_woo_stripe_renewal_payment.
		 * @param object $payment_method_obj payment_method_obj.
		 * @param object $gateway gateway.
		 * @param object $payment_intent_obj payment_intent_obj.
		 * @param int    $subscription_id subscription_id.
		 * @param object $order order.
		 * @since 2.0.0
		 * @return object
		 */
		public function wps_wsp_process_woo_stripe_renewal_payment( $payment_method_obj, $gateway, $payment_intent_obj, $subscription_id, $order ) {
			$args = $payment_intent_obj->get_payment_intent_args( $order );

			$parent_order_id = wps_wsp_get_meta_data( $subscription_id, 'wps_parent_order', true );
			$parent_order = wc_get_order( $parent_order_id );
			$args['payment_method'] = $payment_method_obj->get_order_meta_data( WC_Stripe_Constants::PAYMENT_METHOD_TOKEN, $parent_order );
			$customer = $payment_method_obj->get_order_meta_data( WC_Stripe_Constants::CUSTOMER_ID, $parent_order );
			if ( $customer ) {
				$args['customer'] = $customer;
			}
			$args['confirm']        = true;
			$intent = $gateway->paymentIntents->mode( wc_stripe_order_mode( $parent_order ) )->create( $args );

			if ( is_wp_error( $intent ) ) {
				return $intent;
			} else {
				$order->update_meta_data( WC_Stripe_Constants::PAYMENT_INTENT_ID, $intent->id );

				$charge = $intent->charges->data[0];

				if ( 'succeeded' === $intent->status || 'requires_capture' === $intent->status ) {

					return (object) array(
						'complete_payment' => true,
						'charge'           => $charge,
					);
				} else {
					return (object) array(
						'complete_payment' => false,
						'charge'           => $charge,
					);
				}
			}
		}

		/**
		 * Allow payment method.
		 *
		 * @name wps_wsp_woo_stripe_payment_gateway_for_woocommerce.
		 * @param array  $supported_payment_method supported_payment_method.
		 * @param string $payment_method payment_method.
		 * @since 2.0.0
		 * @return array
		 */
		public function wps_wsp_woo_stripe_payment_gateway_for_woocommerce( $supported_payment_method, $payment_method ) {

			if ( $this->wps_wsp_check_supported_payment_options( $payment_method ) ) {
				$supported_payment_method[] = $payment_method;
			}
			return $supported_payment_method;
		}

		/**
		 * This function is add subscription order status.
		 *
		 * @name wps_wsp_add_stripe_order_statuses_for_payment_complete
		 * @param array  $order_status order_status.
		 * @param object $order order.
		 * @since 2.0.0
		 * @return mixed
		 */
		public function wps_wsp_add_stripe_order_statuses_for_payment_complete( $order_status, $order ) {
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
		 * This function is used to cancel subscriptions status.
		 *
		 * @name wps_wsp_cancel_woo_stripe_subscription
		 * @param int    $wps_subscription_id wps_subscription_id.
		 * @param string $status status.
		 * @since 2.0.0
		 * @return void
		 */
		public function wps_wsp_cancel_woo_stripe_subscription( $wps_subscription_id, $status ) {

			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
				$subscription = new WPS_Subscription( $wps_subscription_id );
				$wps_payment_method = $subscription->get_payment_method();
			} else {
				$wps_payment_method = get_post_meta( $wps_subscription_id, '_payment_method', true );
			}
			if ( $this->wps_wsp_check_supported_payment_options( $wps_payment_method ) ) {
				if ( 'Cancel' == $status ) {
					wps_sfw_send_email_for_cancel_susbcription( $wps_subscription_id );
					wps_wsp_update_meta_data( $wps_subscription_id, 'wps_subscription_status', 'cancelled' );
				}
			}
		}

		/**
		 * Check supported payment method.
		 *
		 * @name wps_wsp_check_supported_payment_options
		 * @param string $payment_method payment_method.
		 * @since 2.0.0
		 * @return boolean
		 */
		public function wps_wsp_check_supported_payment_options( $payment_method ) {
			$result = false;
			if ( strpos( $payment_method, 'stripe_' ) !== false ) {
				$result = true;
			}
			return $result;
		}

		/**
		 * Check woo stripe enable.
		 *
		 * @name wps_wsp_check_woo_stripe_enable
		 * @since 2.0.0
		 * @return boolean
		 */
		public function wps_wsp_check_woo_stripe_enable() {
			$activated = false;
			if ( in_array( 'woo-stripe-payment/stripe-payments.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				$activated = true;
			}
			return $activated;
		}
	}
}
return new Wps_Subscriptions_Payment_Woostripe_Main();
