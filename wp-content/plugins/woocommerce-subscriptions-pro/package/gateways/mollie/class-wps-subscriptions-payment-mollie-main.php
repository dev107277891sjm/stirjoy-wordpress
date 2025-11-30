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

use Mollie\Api\Exceptions\ApiException;

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

if ( ! class_exists( 'Wps_Subscriptions_Payment_Mollie_Main' ) ) {


	/**
	 * Define class and module for stripe.
	 */
	class Wps_Subscriptions_Payment_Mollie_Main {

		/**
		 * Constructor
		 */
		public function __construct() {
			add_filter( 'wps_sfw_supported_payment_gateway_for_woocommerce', array( $this, 'wps_sfw_mollie_payment_gateway_for_woocommerce' ), 10, 2 );
			add_action( 'wps_sfw_subscription_cancel', array( $this, 'wps_sfw_cancel_mollie_subscription' ), 10, 2 );
			add_filter( 'woocommerce_valid_order_statuses_for_payment_complete', array( $this, 'wps_sfw_add_mollie_order_statuses_for_payment_complete' ), 10, 2 );

			add_filter( 'mollie-payments-for-woocommerce_is_subscription_payment', array( $this, 'wps_sfw_allow_mandate_create' ), 999, 2 );
			add_action( 'mollie-payments-for-woocommerce_after_mandate_created', array( $this, 'wps_sfw_save_mandate_id' ), 999, 4 );

			$installed_plugins = get_plugins();
			$mollie_version    = isset( $installed_plugins['mollie-payments-for-woocommerce/mollie-payments-for-woocommerce.php'] ) ? $installed_plugins['mollie-payments-for-woocommerce/mollie-payments-for-woocommerce.php']['Version'] : false;
			if ( version_compare( $mollie_version, '6.7.0', '>' ) ) {
				add_action( 'wps_sfw_other_payment_gateway_renewal', array( $this, 'wps_sfw_process_mollie_payment_renewal_new_version' ), 10, 3 );
			} else {
				add_action( 'wps_sfw_other_payment_gateway_renewal', array( $this, 'wps_sfw_process_mollie_payment_renewal' ), 10, 3 );
			}
		}

		/**
		 * This function is used to create renewal order.
		 *
		 * @param object $parent_order parent_order.
		 * @since 2.0.0
		 * @return object
		 */
		public function wps_is_test_mode_enabled_for_renewal_order( $parent_order ) {
			$result = false;
			$payment_mode = $parent_order->get_meta( '_mollie_payment_mode', true );
			if ( 'test' === $payment_mode ) {
				$result = true;
			}
			return $result;
		}

		/**
		 * This function is used to get initial order status.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function wps_get_initial_order_status() {
			return 'pending';
		}

		/**
		 * This function is used to get customer id from molie.
		 *
		 * @param object $order order.
		 * @since 2.0.0
		 * @return mixed
		 */
		public function wps_get_order_mollie_customer_id( $order ) {
			return $order->get_meta( '_mollie_customer_id', true );
		}

		/**
		 * This function is used to restore customer id.
		 *
		 * @param int    $mollie_customer_id mollie_customer_id.
		 * @param int    $mollie_payment_id mollie_payment_id.
		 * @param object $subscription subscription.
		 * @since 2.0.0
		 * @return int
		 */
		public function wps_restore_mollie_customer_id_and_mandate( $mollie_customer_id, $mollie_payment_id, $subscription ) {

			try {
				// Get subscription ID.
				$subscription_id = $subscription->get_id();

				// Get full payment object from Mollie API.
				$payment_object_resource = Mollie_WC_Plugin::getPaymentFactoryHelper()->getPaymentObject( $mollie_payment_id );

				// If there is no known customer ID, try to get it from the API.

				if ( empty( $mollie_customer_id ) ) {
					// Try to get the customer ID from the payment object.

					$mollie_customer_id = $payment_object_resource->getMollieCustomerIdFromPaymentObject( $mollie_payment_id );

					if ( empty( $mollie_customer_id ) ) {
						return $mollie_customer_id;
					}
				}

				// Check for valid mandates.

				$settings_helper = Mollie_WC_Plugin::getSettingsHelper();
				$test_mode       = $settings_helper->isTestModeEnabled();

				// Get the WooCommerce payment gateway for this subscription.
				$gateway = wc_get_payment_gateway_by_order( $subscription );

				if ( ! $gateway || ! ( $gateway instanceof Mollie_WC_Gateway_Abstract ) ) {
					return $mollie_customer_id;
				}

				$mollie_method = $gateway->getMollieMethodId();

				// Check that first payment method is related to SEPA Direct Debit and update.
				$methods_needing_update = array(
					'bancontact',
					'belfius',
					'eps',
					'giropay',
					'ideal',
					'kbc',
					'mistercash',
					'sofort',
				);

				if ( in_array( $mollie_method, $methods_needing_update ) != false ) {
					$mollie_method = 'directdebit';
				}

				// Get all mandates for the customer.
				$mandates = Mollie_WC_Plugin::getApiHelper()->getApiClient( $test_mode )->customers->get( $mollie_customer_id );

				// Check credit card payments and mandates.
				if ( 'creditcard' === $mollie_method && ! $mandates->hasValidMandateForMethod( $mollie_method ) ) {

					return $mollie_customer_id;
				}

				// Get a Payment object from Mollie to check for paid status.
				$payment_object = $payment_object_resource->getPaymentObject( $mollie_payment_id );

				// Extra check that first payment was not sequenceType first.
				$sequence_type = $payment_object_resource->getSequenceTypeFromPaymentObject( $mollie_payment_id );

				// Check SEPA Direct Debit payments and mandates.
				if ( 'directdebit' === $mollie_method && ! $mandates->hasValidMandateForMethod( $mollie_method ) && $payment_object->isPaid() && 'oneoff' === $sequence_type ) {

					$options = $payment_object_resource->getMollieCustomerIbanDetailsFromPaymentObject( $mollie_payment_id );

					// consumerName can be empty for Bancontact payments, in that case use the WooCommerce customer name.
					if ( empty( $options['consumerName'] ) ) {

						$billing_first_name = $subscription->get_billing_first_name();
						$billing_last_name  = $subscription->get_billing_last_name();

						$options['consumerName'] = $billing_first_name . ' ' . $billing_last_name;
					}

					// Set method.
					$options['method'] = $mollie_method;

					$customer = Mollie_WC_Plugin::getApiHelper()->getApiClient( $test_mode )->customers->get( $mollie_customer_id );
					Mollie_WC_Plugin::getApiHelper()->getApiClient( $test_mode )->mandates->createFor( $customer, $options );
				}

				return $mollie_customer_id;

			} catch ( Mollie\Api\Exceptions\ApiException $e ) {
				return $mollie_customer_id;
			}
		}

		/**
		 * This function is used to get recurring payment request.
		 *
		 * @param object $order  order.
		 * @param int    $customer_id customer_id.
		 * @param object $payment_method_obj payment_method_obj.
		 * @since 2.0.0
		 * @return array
		 */
		public function wps_get_recurring_payment_request_data( $order, $customer_id, $payment_method_obj ) {

			$settings_helper     = Mollie_WC_Plugin::getSettingsHelper();
			$payment_description = __( 'Order', 'woocommerce-subscriptions-pro' ) . ' ' . $order->get_order_number();
			$payment_locale      = $settings_helper->getPaymentLocale();
			$mollie_method       = $payment_method_obj->getMollieMethodId();
			$selected_issuer     = $payment_method_obj->getSelectedIssuer();
			$return_url          = $payment_method_obj->getReturnUrl( $order );
			$webhook_url         = $payment_method_obj->getWebhookUrl( $order );

			return array_filter(
				array(
					'amount'       => array(
						'currency' => Mollie_WC_Plugin::getDataHelper()->getOrderCurrency( $order ),
						'value'    => Mollie_WC_Plugin::getDataHelper()->formatCurrencyValue( $order->get_total(), Mollie_WC_Plugin::getDataHelper()->getOrderCurrency( $order ) ),
					),
					'description'  => $payment_description,
					'redirectUrl'  => $return_url,
					'webhookUrl'   => $webhook_url,
					'method'       => $mollie_method,
					'issuer'       => $selected_issuer,
					'locale'       => $payment_locale,
					'metadata'     => array(
						'order_id' => $order->get_id(),
					),
					'sequenceType' => 'recurring',
					'customerId'   => $customer_id,
				)
			);
		}

		/**
		 * This fucntion is used to update schdule payment.
		 *
		 * @param object $renewal_order renewal_order.
		 * @param string $initial_order_status initial_order_status.
		 * @param object $payment payment.
		 * @param object $payment_method_obj payment_method_obj.
		 * @since 2.0.0
		 * @return void
		 */
		public function wps_update_scheduled_payment_order( $renewal_order, $initial_order_status, $payment, $payment_method_obj ) {
			$payment_method_obj->updateOrderStatus(
				$renewal_order,
				$initial_order_status,
				__( 'Awaiting payment confirmation.', 'woocommerce-subscriptions-pro' ) . "\n"
			);
			$payment_method_title = '';

			$renewal_order->add_order_note(
				sprintf(
				/* translators: Placeholder 1: Payment method title, placeholder 2: payment ID */
					__( '%1$s payment started (%2$s).', 'woocommerce-subscriptions-pro' ),
					$payment_method_title,
					$payment->id . ( 'test' === $payment->mode ? ( ' - ' . __( 'test mode', 'woocommerce-subscriptions-pro' ) ) : '' )
				)
			);
		}

		/**
		 * This fucntion is used to aadd recurring payment method.
		 *
		 * @param object $renewal_order renewal_order.
		 * @param int    $renewal_order_id renewal_order_id.
		 * @param object $payment payment.
		 * @since 2.0.0
		 * @throws \Mollie\Api\Exceptions\ApiException Throws the exception.
		 * @return void
		 */
		public function wps_update_first_payment_method_to_recurring_payment_method( $renewal_order, $renewal_order_id, $payment ) {

			// Update first payment method to actual recurring payment method used for renewal order, this is.
			// for subscriptions where the first order used methods like iDEAL as first payment and.
			// later renewal orders switch to SEPA Direct Debit.

			$methods_needing_update = array(
				'mollie_wc_gateway_bancontact',
				'mollie_wc_gateway_belfius',
				'mollie_wc_gateway_eps',
				'mollie_wc_gateway_giropay',
				'mollie_wc_gateway_ideal',
				'mollie_wc_gateway_kbc',
				'mollie_wc_gateway_mistercash',
				'mollie_wc_gateway_sofort',
			);

			$current_method = $renewal_order->get_payment_method();
			if ( in_array( $current_method, $methods_needing_update ) && 'directdebit' === $payment->method ) {
				try {
					$renewal_order->set_payment_method( 'mollie_wc_gateway_directdebit' );
					$renewal_order->set_payment_method_title( 'SEPA Direct Debit' );
					$renewal_order->save();
				} catch ( WC_Data_Exception $e ) {
					throw new \Mollie\Api\Exceptions\ApiException( $e->getMessage() );
				}
			}
		}

		/**
		 * This function is used to process payment from mollie gateway.
		 *
		 * @param object $renewal_order renewal_order.
		 * @param int    $subscription_id subscription_id.
		 * @param string $payment_method payment_method.
		 * @since 2.0.0
		 * @throws \Mollie\Api\Exceptions\ApiException Throws the exception.
		 * @throws ApiException Throws the exception.
		 * @return void
		 */
		public function wps_sfw_process_mollie_payment_renewal( $renewal_order, $subscription_id, $payment_method ) {

			if ( strpos( $payment_method, 'mollie_wc_gateway_' ) !== false ) {
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
				$renewal_order_id   = $renewal_order->get_id();
				$parent_order_id  = wps_wsp_get_meta_data( $subscription_id, 'wps_parent_order', true );

				$parent_order = wc_get_order( $parent_order_id );
				// Allow developers to hook into the subscription renewal payment before it processed.
				$initial_order_status = $this->wps_get_initial_order_status();

				$test_mode = $this->wps_is_test_mode_enabled_for_renewal_order( $parent_order );
				// Get Mollie customer ID.
				$customer_id    = $this->wps_get_order_mollie_customer_id( $parent_order );

				// Just need one valid subscription.
				$subscription_mollie_payment_id = $parent_order->get_meta( '_mollie_payment_id' );

				$mandate_id = ! empty( $parent_order ) ? $parent_order->get_meta( '_mollie_mandate_id' ) : null;

				if ( ! empty( $subscription_mollie_payment_id ) && ! empty( $subscription_id ) ) {
					$customer_id = $this->wps_restore_mollie_customer_id_and_mandate( $customer_id, $subscription_mollie_payment_id, $subscription );
				}

				// Get all data for the renewal payment.
				$data = $this->wps_get_recurring_payment_request_data( $renewal_order, $customer_id, $payment_method_obj );

				// Allow filtering the renewal payment data.

				// Create a renewal payment.
				try {

					// $mollie_api_client = Mollie_WC_Plugin::getApiHelper()->getApiClient( $test_mode );
					$mollie_api_client = $data;
					$valid_mandate = false;
					try {
						if ( ! empty( $mandate_id ) ) {
							$mandate = $mollie_api_client->customers->get( $customer_id )->getMandate( $mandate_id );
							if ( 'valid' === $mandate->status ) {
								$data['method'] = $mandate->method;
								$data['mandateId'] = $mandate_id;
								$valid_mandate = true;
							}
						} else {
							// Get all mandates for the customer ID.
							$mandates = $mollie_api_client->customers->get( $customer_id )->mandates();
							$wps_renewal_order_method = $renewal_order->get_payment_method();
							$wps_renewal_order_method = str_replace( 'mollie_wc_gateway_', '', $wps_renewal_order_method );
							foreach ( $mandates as $mandate ) {
								if ( 'valid' === $mandate->status ) {
									$valid_mandate = true;
									$data['method'] = $mandate->method;
									if ( $mandate->method === $wps_renewal_order_method ) {
										$data['method'] = $mandate->method;
										break;
									}
								}
							}
						}
					} catch ( Mollie\Api\Exceptions\ApiException $e ) {
						/* translators: %s: customer id */
						throw new \Mollie\Api\Exceptions\ApiException( sprintf( __( 'The customer (%s) could not be used or found. ', 'woocommerce-subscriptions-pro' ) . $e->getMessage(), $customer_id ) );
					}

					// Check that there is at least one valid mandate.
					try {
						if ( $valid_mandate ) {
							$payment = Mollie_WC_Plugin::getApiHelper()->getApiClient( $test_mode )->payments->create( $data );
						} else {
							/* translators: %s: customer id */
							throw new \Mollie\Api\Exceptions\ApiException( sprintf( __( 'The customer (%s) does not have a valid mandate.', 'woocommerce-subscriptions-pro' ), $customer_id ) );
						}
					} catch ( Mollie\Api\Exceptions\ApiException $e ) {
						throw $e;
					}

					// Unset & set active Mollie payment.
					// Get correct Mollie Payment Object.
					$payment_object = Mollie_WC_Plugin::getPaymentFactoryHelper()->getPaymentObject( $payment );
					$payment_object->unsetActiveMolliePayment( $renewal_order_id );
					$payment_object->setActiveMolliePayment( $renewal_order_id, $payment );

					// Set Mollie customer.
					Mollie_WC_Plugin::getDataHelper()->setUserMollieCustomerIdAtSubscription( $renewal_order_id, $payment_object::$customerId );

					// Update order status and add order note.
					$this->wps_update_scheduled_payment_order( $renewal_order, $initial_order_status, $payment, $payment_method_obj );
					return array(
						'result'   => 'success',
					);
				} catch ( Mollie\Api\Exceptions\ApiException $e ) {
					$renewal_order->update_status( 'failed' );

				}
			}
		}

		/**
		 * This function is add paypal payment gateway.
		 *
		 * @name wps_sfw_mollie_payment_gateway_for_woocommerce
		 * @param array  $supported_payment_method supported_payment_method.
		 * @param string $payment_method payment_method.
		 * @since 2.0.0
		 */
		public function wps_sfw_mollie_payment_gateway_for_woocommerce( $supported_payment_method, $payment_method ) {
			if ( strpos( $payment_method, 'mollie_wc_gateway_creditcard' ) !== false ) {

				$supported_payment_method[] = $payment_method;
			}
			return $supported_payment_method;
		}

		/**
		 * This function is add subscription order status.
		 *
		 * @name wps_sfw_add_mollie_order_statuses_for_payment_complete
		 * @param array  $order_status order_status.
		 * @param object $order order.
		 * @since 2.0.0
		 */
		public function wps_sfw_add_mollie_order_statuses_for_payment_complete( $order_status, $order ) {
			if ( $order && is_object( $order ) ) {

				$order_id = $order->get_id();
				$payment_method = $order->get_payment_method();
				$wps_sfw_renewal_order = wps_wsp_get_meta_data( $order_id, 'wps_sfw_renewal_order', true );
				if ( strpos( $payment_method, 'mollie_wc_gateway_' ) !== false && 'yes' == $wps_sfw_renewal_order ) {
					$order_status[] = 'wps_renewal';

				}
			}
			return apply_filters( 'wps_sfw_add_subscription_order_statuses_for_payment_complete', $order_status, $order );
		}

		/**
		 * This function is used to cancel subscriptions status.
		 *
		 * @name wps_sfw_cancel_mollie_subscription
		 * @param int    $wps_subscription_id wps_subscription_id.
		 * @param string $status status.
		 * @since    1.0.1
		 */
		public function wps_sfw_cancel_mollie_subscription( $wps_subscription_id, $status ) {

			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
				$subscription = new WPS_Subscription( $wps_subscription_id );
				$wps_payment_method = $subscription->get_payment_method();
			} else {
				$wps_payment_method = get_post_meta( $wps_subscription_id, '_payment_method', true );
			}

			if ( strpos( $wps_payment_method, 'mollie_wc_gateway_' ) !== false ) {
				if ( 'Cancel' == $status ) {
					wps_sfw_send_email_for_cancel_susbcription( $wps_subscription_id );
					wps_wsp_update_meta_data( $wps_subscription_id, 'wps_subscription_status', 'cancelled' );
				}
			}
		}

		/**
		 * Allow to create mandate
		 *
		 * @param bool    $is_subscription .
		 * @param integer $order_id .
		 * @return true;
		 */
		public function wps_sfw_allow_mandate_create( $is_subscription, $order_id ) {
			$order_obj      = wc_get_order( $order_id );
			$payment_method = $order_obj->get_payment_method();
			if ( 'mollie_wc_gateway_creditcard' == $payment_method ) {
				$is_subscription = true;
			}
			return $is_subscription;
		}

		/**
		 * Save Mandate id into order post meta
		 *
		 * @param [type] $payment_object .
		 * @param [type] $order .
		 * @param [type] $customer_id .
		 * @param [type] $mandate_id .
		 * @return void
		 */
		public function wps_sfw_save_mandate_id( $payment_object, $order, $customer_id, $mandate_id ) {
			wps_wsp_update_meta_data( $order->get_id(), 'wps_sfw_mollie_mandate_id', $mandate_id );
		}

		/**
		 * Make a recurring payment for greater version mollie payment
		 *
		 * @param [type] $renewal_order .
		 * @param [type] $subscription_id .
		 * @param [type] $payment_method .
		 * @return void
		 */
		public function wps_sfw_process_mollie_payment_renewal_new_version( $renewal_order, $subscription_id, $payment_method ) {

			$renewal_order_id = $renewal_order->get_id();

			$wps_sfw_renewal_order = wps_wsp_get_meta_data( $renewal_order_id, 'wps_sfw_renewal_order', true );
			$wps_parent_order_id = wps_wsp_get_meta_data( $subscription_id, 'wps_parent_order', true );

			$saved_mandate_id = wps_wsp_get_meta_data( $wps_parent_order_id, 'wps_sfw_mollie_mandate_id', true );
			$saved_mandate_id = wps_wsp_get_meta_data( $wps_parent_order_id, '_mollie_mandate_id', true ) ? wps_wsp_get_meta_data( $wps_parent_order_id, '_mollie_mandate_id', true ) : $saved_mandate_id;
			$saved_cust_id    = wps_wsp_get_meta_data( $wps_parent_order_id, '_mollie_customer_id', true );
			if ( strpos( $payment_method, 'mollie_wc_gateway_' ) !== false && 'yes' === $wps_sfw_renewal_order && ! empty( $saved_cust_id ) && ! empty( $saved_mandate_id ) ) {
				$test_mode = get_option( 'mollie-payments-for-woocommerce_test_mode_enabled', 'no' );
				if ( 'yes' === $test_mode ) {
					$api_key = get_option( 'mollie-payments-for-woocommerce_test_api_key', null );
				} else {
					$api_key = get_option( 'mollie-payments-for-woocommerce_live_api_key', null );
				}
				$response = wp_remote_post(
					'https://api.mollie.com/v2/customers/' . $saved_cust_id . '/mandates/' . $saved_mandate_id,
					array(
						'method'      => 'GET',
						'timeout'     => 240,
						'redirection' => 5,
						'httpversion' => '1.0',
						'blocking'    => true,
						'sslverify'   => false,
						'headers'     => array(
							'Authorization' => 'Bearer ' . $api_key,
							'Content-Type'  => 'application/json',
						),
					)
				);
				if ( ! is_wp_error( $response ) ) {
					$get_res_body   = wp_remote_retrieve_body( $response );
					$mandate_status = json_decode( $get_res_body )->status;
					if ( 'valid' === $mandate_status ) {
						$create_payment = wp_remote_post(
							'https://api.mollie.com/v2/payments',
							array(
								'method'      => 'POST',
								'timeout'     => 240,
								'redirection' => 5,
								'httpversion' => '1.0',
								'blocking'    => true,
								'sslverify'   => false,
								'headers'     => array(
									'Authorization' => 'Bearer ' . $api_key,
									'Content-Type'  => 'application/json',
								),
								'body'        => json_encode(
									array(
										'amount' => array(
											'currency' => $renewal_order->get_currency(),
											// 'value'    => $renewal_order->get_total(),
											'value'    => number_format( ( $renewal_order->get_total() ), 2 ),
										),
										'customerId'   => $saved_cust_id,
										'sequenceType' => 'recurring',
										'description'  => 'Order ' . $renewal_order_id,
									),
								),
							),
						);
						if ( ! is_wp_error( $get_res_body ) ) {
							$get_res_body = wp_remote_retrieve_body( $create_payment );
							$get_res_body = json_decode( $get_res_body );
							if ( isset( $get_res_body->resource ) && 'payment' === $get_res_body->resource ) {
								$renewal_order->payment_complete( $get_res_body->id );
								/* translators: %s: Transaction id */
								$renewal_order->add_order_note( sprintf( __( 'Transaction has been created successfully Transaction ID : %s', 'woocommerce-subscriptions-pro' ), $get_res_body->id ) );
								do_action( 'wps_sfw_recurring_payment_success', $renewal_order_id );
							}
						}
					} else {
						do_action( 'wps_sfw_recurring_payment_failed', $renewal_order_id );
						/* translators: %s: Mandate id */
						$renewal_order->update_status( 'failed', sprintf( __( 'Mandate ID %s is not valid to make a recurring payment', 'woocommerce-subscriptions-pro' ), $saved_mandate_id ) );
					}
				} else {
					do_action( 'wps_sfw_recurring_payment_failed', $renewal_order_id );
					$renewal_order->update_status( 'failed', __( 'Can not make a payment for this Order', 'woocommerce-subscriptions-pro' ) );
				}
			} else {
				do_action( 'wps_sfw_recurring_payment_failed', $renewal_order_id );
			}
		}
	}
}
return new Wps_Subscriptions_Payment_Mollie_Main();
