<?php
/**
 * The file that defines Gateway during checkout
 *
 * @link       https://wpswings.com
 * @since      1.0.0
 *
 * @package    Wps_Paypal_Subscription_Integration
 * @subpackage Wps_Paypal_Subscription_Integration/includes
 */

use Automattic\WooCommerce\Utilities\OrderUtil;

if ( ! function_exists( 'wps_paypal_subscription_init_gateway_class' ) ) {
	add_action( 'plugins_loaded', 'wps_paypal_subscription_init_gateway_class' );
	/**
	 * Register the WPS Paypal subscription Gateway
	 */
	function wps_paypal_subscription_init_gateway_class() {
		/**
		 *
		 * A class definition that define the WPS Paypal Subscription Gateway
		 *
		 * @link       https://wpswings.com
		 * @since      1.0.0
		 *
		 * @package    Wps_Paypal_Subscription_Integration
		 * @subpackage Wps_Paypal_Subscription_Integration/includes
		 */
		class WPS_Paypal_Subscription_Integration_Gateway extends WC_Payment_Gateway {
			/**
			 * The ID of this plugin.
			 *
			 * @since    1.0.0
			 * @access   private
			 * @var      string $client_id .
			 */
			private $client_id;
			/**
			 * The ID of this plugin.
			 *
			 * @since    1.0.0
			 * @access   private
			 * @var      string $client_secret .
			 */
			private $client_secret;
			/**
			 * The ID of this plugin.
			 *
			 * @since    1.0.0
			 * @access   private
			 * @var      string $testmode .
			 */
			private $testmode;
			/**
			 * Class constructor, more about it in Step 3
			 */
			public function __construct() {

				$this->id                 = 'wps_paypal_subscription'; // Payment gateway plugin ID.
				$this->icon               = ''; // URL of the icon that will be displayed on checkout page near your gateway name.
				$this->has_fields         = false; // In case you need a custom credit card form.
				$this->method_title       = 'WPS Paypal Subscription';
				$this->method_description = 'Description of WPS Paypal Subscription payment gateway'; // Will be displayed on the options page.

				$this->supports = array(
					'products',
				);

				// Method with all the options fields.
				$this->init_form_fields();

				// Load the settings.
				$this->init_settings();
				$this->title       = $this->get_option( 'title' ) ? $this->get_option( 'title' ) : 'WPS Paypal Subscription Gateway';
				$this->description = $this->get_option( 'description' );
				$this->enabled     = $this->get_option( 'enabled' );
				$this->testmode    = 'yes' === $this->get_option( 'testmode' );
				if ( $this->testmode ) {
					$this->client_id     = $this->get_option( 'sandbox_client_id' );
					$this->client_secret = $this->get_option( 'sandbox_client_secret' );
				} else {
					$this->client_id     = $this->get_option( 'client_id' );
					$this->client_secret = $this->get_option( 'client_secret' );
				}
				// This action hook saves the settings.
				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

				// We need custom JavaScript to obtain a token.
				add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
				// register a webhook here.
				add_action( 'woocommerce_api_' . $this->id, array( $this, 'wps_paypal_subscription_webhook' ) );
			}

			/**
			 * Plugin options, we deal with it in Step 3 too
			 */
			public function init_form_fields() {

				$data = get_option( 'woocommerce_wps_paypal_subscription_settings' );
				if ( ! empty( $data ) ) {
					if ( isset( $data['validate'] ) ) {
						$data['validate'] = 'Validate';
					}
					if ( isset( $data['validate_test'] ) ) {
						$data['validate_test'] = 'Validate';
					}
					update_option( 'woocommerce_wps_paypal_subscription_settings', $data );
				}
				$this->form_fields = array(
					'enabled' => array(
						'title'       => __( 'Enable/Disable', 'woocommerce-subscriptions-pro' ),
						'label'       => __( 'Enable WPS Paypal Subscription Gateway', 'woocommerce-subscriptions-pro' ),
						'type'        => 'checkbox',
						'description' => '',
						'default'     => 'no',
					),
					'title' => array(
						'title'       => __( 'Title', 'woocommerce-subscriptions-pro' ),
						'type'        => 'text',
						'description' => __( 'This controls the title which the user sees during checkout', 'woocommerce-subscriptions-pro' ),
						'default'     => __( 'WPS Paypal Subscription', 'woocommerce-subscriptions-pro' ),
						'desc_tip'    => true,
					),
					'description' => array(
						'title'       => __( 'Description', 'woocommerce-subscriptions-pro' ),
						'type'        => 'textarea',
						'description' => __( 'This controls the description which the user sees during checkout', 'woocommerce-subscriptions-pro' ),
						'default'     => __( 'Pay with your credit card via our super-cool payment gateway', 'woocommerce-subscriptions-pro' ),
					),
					'client_id' => array(
						'title'       => __( 'CLIENT ID', 'woocommerce-subscriptions-pro' ),
						'type'        => 'text',
					),
					'client_secret' => array(
						'title'       => __( 'CLIENT SECRET', 'woocommerce-subscriptions-pro' ),
						'type'        => 'password',
					),
					'validate'  => array(
						'title'   => '',
						'type'    => 'button',
						'label'   => __( 'Test Paypal Live Keys', 'woocommerce-subscriptions-pro' ),
						'class'   => 'button wps_paypal_subscription_validate',
						'default' => esc_html__( 'Validate', 'woocommerce-subscriptions-pro' ),
					),
					'testmode' => array(
						'title'       => __( 'Test mode', 'woocommerce-subscriptions-pro' ),
						'label'       => __( 'Enable Test Mode', 'woocommerce-subscriptions-pro' ),
						'type'        => 'checkbox',
						'description' => __( 'Place the payment gateway in test mode using test API keys', 'woocommerce-subscriptions-pro' ),
						'default'     => 'yes',
						'desc_tip'    => true,
					),
					'sandbox_client_id' => array(
						'title'       => __( 'Sandbox CLIENT ID', 'woocommerce-subscriptions-pro' ),
						'type'        => 'text',
					),
					'sandbox_client_secret' => array(
						'title'       => __( 'Sandbox CLIENT SECRET', 'woocommerce-subscriptions-pro' ),
						'type'        => 'password',
					),
					'validate_test' => array(
						'title'       => '',
						'type'        => 'button',
						'label'       => __( 'Test Paypal Test Keys', 'woocommerce-subscriptions-pro' ),
						'class'       => 'button wps_paypal_subscription_validate',
						'default'     => esc_html__( 'Validate', 'woocommerce-subscriptions-pro' ),
					),
					'create_credentials' => array(
						'title'       => __( 'API Credentials ( Client ID and Client Secret )', 'woocommerce-subscriptions-pro' ),
						'type'        => 'title',
						'description' => '<p>Go to the PayPal Developer website (https://developer.paypal.com/) and sign in with your PayPal account. If you don\'t have one, you\'ll need to sign up for a PayPal account first.Once you\'re signed in, navigate to the "Dashboard" or "My Apps & Credentials" section in your developer account. Then, click on "Create App" or "Create REST API App. After creating the app, PayPal will generate a pair of API credentials: a Client ID and a Secret. These credentials are essential for authenticating your requests when using the PayPal REST API.</p>',
					),
					'webhook'           => array(
						'title'       => __( 'Webhook', 'woocommerce-subscriptions-pro' ),
						'type'        => 'title',
						'description' => '<p>Webhook Endpoints
						In order for WPS Paypal Subscription to function completely, you must configure your Instant Notification System.</p><p>Configure Webhooks in Developer Dashboard: Log in to your PayPal Developer account, and under the `My Apps & Credentials` section, find the REST API app you have created for your integration. There should be an option to configure webhooks or manage webhook events for that app</p><b>URL:</b><code>' . esc_attr( site_url() ) . '?wc-api=wps_paypal_subscription</code>',
					),
				);
			}

			/**
			 * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form.
			 *
			 * @return void
			 */
			public function payment_scripts() {
				// we need JavaScript to process a token only on cart/checkout pages, right?
				if ( ! is_cart() && ! is_checkout() && ! isset( $_GET['pay_for_order'] ) ) {
					return;
				}

				// If our payment gateway is disabled, we do not have to enqueue JS too.
				if ( 'no' === $this->enabled ) {
					return;
				}

				// No reason to enqueue JavaScript if API keys are not set.
				if ( empty( $this->client_id ) || empty( $this->client_secret ) ) {
					return;
				}
			}

			/**
			 * We're processing the payments here
			 *
			 * @param mixed $order_id .
			 * @return array
			 */
			public function process_payment( $order_id ) {
				$customer_order = new WC_Order( $order_id );
				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $customer_order ),
				);
			}

			/**
			 * Handling the webhook events
			 *
			 * @return void
			 */
			public function wps_paypal_subscription_webhook() {

				if ( isset( $_GET['wc-api'] ) && $_GET['wc-api'] == $this->id ) {

					$raw_post_data = file_get_contents( 'php://input' );

					$webhook_data = json_decode( $raw_post_data, true );

					$headers = getallheaders();

					// Your PayPal webhook secret.
					$webhook_secret = 'wps_paypal_subscription';

					$webhook_secret = $this->client_secret;

					if ( isset( $webhook_data['event_type'] ) ) {
						$event_type = $webhook_data['event_type'];

						switch ( $event_type ) {
							case 'PAYMENT.SALE.COMPLETED':
								$this->manage_renewal( $webhook_data );
								break;
							case 'BILLING.SUBSCRIPTION.CANCELLED':
								$this->manage_cancellation( $webhook_data );
								break;
							case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
								$this->manage_failed_payment( $webhook_data );
								break;
							default:
						}
					}
				}
			}

			/**
			 * Manage the renewal and creating a renewal for the subscription through webhhook
			 *
			 * @param array() $webhook_data .
			 */
			public function manage_renewal( $webhook_data ) {
				wps_paypal_subscription_log( 'Webhook Renewal' . wc_print_r( $webhook_data, true ) );

				$paypal_subscription_id = $webhook_data['resource']['billing_agreement_id'];

				$subscription_id = $this->fetch_wc_subscription_id( $paypal_subscription_id );

				if ( ! $subscription_id ) {
					return;
				}

				// Do not create renewal if already been created.

				$get_renewal_data = wps_wsp_get_meta_data( $subscription_id, 'wps_wsp_renewal_order_data', true );
				if ( ! empty( $get_renewal_data ) && is_array( $get_renewal_data ) ) {
					$last_renewal_id = end( $get_renewal_data );
					$renewal_order = wc_get_order( $last_renewal_id );

					$order_date_created = $renewal_order->get_date_created();

					if ( $order_date_created ) {
						$today = gmdate( 'Y-m-d' ); // Get today's date.
						$order_created_date = $order_date_created->format( 'Y-m-d' );

						if ( $order_created_date === $today ) {
							return;
						}
					}
				}

				// do not create renewal again, because the event is called once subscription payment getting recived after checkout.
				if ( OrderUtil::custom_orders_table_usage_is_enabled() && class_exists( 'WPS_Subscription' ) ) {
					$subscription = new WPS_Subscription( $subscription_id );
				} else {
					$subscription = wc_get_order( $subscription_id );
				}
				$order_date_created = $subscription->get_date_created();
				if ( $order_date_created ) {
					$today = gmdate( 'Y-m-d' ); // Get today's date.
					$order_created_date = $order_date_created->format( 'Y-m-d' );

					if ( $order_created_date === $today ) {
						return;
					}
				}

				$wps_sfw_pro_plugin_activated = false;
				if ( in_array( 'woocommerce-subscriptions-pro/woocommerce-subscriptions-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
					$wps_sfw_pro_plugin_activated = true;
				}
				$current_time = current_time( 'timestamp' );

				// Recurring code will goes from here.
				if ( OrderUtil::custom_orders_table_usage_is_enabled() && class_exists( 'WPS_Subscription' ) ) {
					$subscription = new WPS_Subscription( $subscription_id );
				} else {
					$subscription = wc_get_order( $subscription_id );
				}
				$parent_order_id = $subscription->get_meta( 'wps_parent_order' );

				if ( function_exists( 'wps_sfw_check_valid_order' ) && ! wps_sfw_check_valid_order( $parent_order_id ) ) {
					return;
				}

				if ( ! $wps_sfw_pro_plugin_activated ) {
					$subp_id = $subscription->get_meta( 'product_id' );
					$check_variable = wps_wsp_get_meta_data( $subp_id, 'wps_sfw_variable_product', true );
					if ( 'yes' === $check_variable ) {
						return;
					}
				}

				$parent_order = wc_get_order( $parent_order_id );
				$billing_details = $parent_order->get_address( 'billing' );
				$shipping_details = $parent_order->get_address( 'shipping' );
				$parent_order_currency = $parent_order->get_currency();
				$new_status = 'wc-pending';
				$user_id = $subscription->get_user_id();
				$product_id = $subscription->get_meta( 'product_id' );
				$product_qty = $subscription->get_meta( 'product_qty' );
				$payment_method = $subscription->get_payment_method();
				$payment_method_title = $subscription->get_payment_method_title();

				$args = array(
					'status'      => $new_status,
					'customer_id' => $user_id,
				);
				$wps_new_order = wc_create_order( $args );
				$wps_new_order->set_currency( $parent_order_currency );

				$line_subtotal = $subscription->get_meta( 'line_subtotal' );
				$line_total = $subscription->get_meta( 'line_total' );

				$line_subtotal_tax = $subscription->get_meta( 'line_subtotal_tax' );
				$line_tax_data = $subscription->get_meta( 'line_tax_data' );
				$line_tax = $subscription->get_meta( 'line_tax' );

				$_product = wc_get_product( $product_id );

				$wps_args = array(
					'variation' => array(),
					'totals'    => array(
						'subtotal'     => $line_subtotal,
						'subtotal_tax' => $line_subtotal_tax,
						'total'        => $line_total,
						'tax'          => $line_tax,
						'tax_data'     => maybe_unserialize( $line_tax_data ),
					),
				);
				$wps_pro_args = apply_filters( 'wps_product_args_for_order', $wps_args );

				$item_id = $wps_new_order->add_product(
					$_product,
					$product_qty,
					$wps_pro_args
				);

				$order_id = $wps_new_order->get_id();

				$wps_new_order->set_payment_method( $payment_method );
				$wps_new_order->set_payment_method_title( $payment_method_title );

				$wps_new_order->set_address( $billing_details, 'billing' );
				$wps_new_order->set_address( $shipping_details, 'shipping' );

				$wps_new_order->update_meta_data( 'wps_sfw_renewal_order', 'yes' );
				$wps_new_order->update_meta_data( 'wps_sfw_subscription', $subscription_id );
				$wps_new_order->update_meta_data( 'wps_sfw_parent_order_id', $parent_order_id );
				$subscription->update_meta_data( 'wps_renewal_subscription_order', $order_id );

				// Billing phone number added.
				$billing_address = wps_wsp_get_meta_data( $parent_order_id, '_billing_address_index', true );
				wps_wsp_update_meta_data( $order_id, '_billing_address_index', $billing_address );

				// Renewal info.
				$wps_no_of_order = wps_wsp_get_meta_data( $subscription_id, 'wps_wsp_no_of_renewal_order', true );
				if ( empty( $wps_no_of_order ) ) {
					$wps_no_of_order = 1;
					$subscription->update_meta_data( 'wps_wsp_no_of_renewal_order', $wps_no_of_order );
				} else {
					$wps_no_of_order = (int) $wps_no_of_order + 1;
					$subscription->update_meta_data( 'wps_wsp_no_of_renewal_order', $wps_no_of_order );

				}
				$wps_renewal_order_data = wps_wsp_get_meta_data( $subscription_id, 'wps_wsp_renewal_order_data', true );
				if ( empty( $wps_renewal_order_data ) ) {
					$wps_renewal_order_data = array( $order_id );
					$subscription->update_meta_data( 'wps_wsp_renewal_order_data', $wps_renewal_order_data );

				} else {
					$wps_renewal_order_data[] = $order_id;
					$subscription->update_meta_data( 'wps_wsp_renewal_order_data', $wps_renewal_order_data );
				}
				$subscription->update_meta_data( 'wps_wsp_last_renewal_order_id', $order_id );

				do_action( 'wps_sfw_renewal_order_creation', $wps_new_order, $subscription_id );

				$wps_new_order->update_taxes();
				$wps_new_order->calculate_totals();
				$wps_new_order->save();

				// Update next payment date.
				$wps_next_payment_date = wps_sfw_next_payment_date( $subscription_id, $current_time, 0 );

				$subscription->update_meta_data( 'wps_next_payment_date', $wps_next_payment_date );

				$subscription->save();

				$wps_new_order->payment_complete();

				$wps_sfw_status = 'pending';
				$wps_link = add_query_arg(
					array(
						'wps_subscription_id'               => $subscription_id,
						'wps_subscription_view_renewal_order'     => $wps_sfw_status,
					),
					admin_url( 'admin.php?page=subscriptions_for_woocommerce_menu&sfw_tab=subscriptions-for-woocommerce-subscriptions-table' )
				);
				$wps_link = wp_nonce_url( $wps_link, $subscription_id . $wps_sfw_status );
				/* translators: %s: subscription id */
				$wps_new_order->add_order_note( sprintf( __( 'This renewal order belongs to Subscription #%s', 'woocommerce-subscriptions-pro' ), '<a href="' . $wps_link . '">' . $subscription_id . '</a>' ) );

				$notes = esc_html__( 'Renewal Order is creating through Paypal Subscription webhook', 'woocommerce-subscriptions-pro' );

				$wps_new_order->add_order_note( $notes );
				do_action( 'wps_sfw_recurring_payment_success', $order_id );

				// Hook for par plugin compatible .
				do_action( 'wps_sfw_compatible_points_and_rewards', $order_id );
			}

			/**
			 * Manage the subscription cancellation through webhhook
			 *
			 * @param array() $webhook_data .
			 */
			public function manage_cancellation( $webhook_data ) {
				wps_paypal_subscription_log( 'Webhook Cancellation' . wc_print_r( $webhook_data, true ) );

				$paypal_subcription_id = $webhook_data['resource']['id'] ?? 0;

				$subscription_id = $this->fetch_wc_subscription_id( $paypal_subcription_id );

				if ( ! $subscription_id ) {
					return;
				}
				if ( OrderUtil::custom_orders_table_usage_is_enabled() && class_exists( 'WPS_Subscription' ) ) {
					$subscription = new WPS_Subscription( $subscription_id );
				} else {
					$subscription = wc_get_order( $subscription_id );
				}
				$notes = esc_html__( 'The Subscription has been cancelled from the paypal', 'woocommerce-subscriptions-pro' );

				$subscription->add_order_note( $notes );
				$subscription->update_meta_data( 'wps_subscription_status', 'cancelled' );
				$subscription->save();
			}
			/**
			 * Manage the failed subscription renewal payment through webhhook
			 *
			 * @param array() $webhook_data .
			 */
			public function manage_failed_payment( $webhook_data ) {
				wps_paypal_subscription_log( 'Webhook Failed Payment' . wc_print_r( $webhook_data, true ) );

				$paypal_subcription_id = $webhook_data['resource']['id'];

				$subscription_id = $this->fetch_wc_subscription_id( $paypal_subcription_id );

				if ( ! $subscription_id ) {
					return;
				}

				if ( OrderUtil::custom_orders_table_usage_is_enabled() && class_exists( 'WPS_Subscription' ) ) {
					$subscription = new WPS_Subscription( $subscription_id );
				} else {
					$subscription = wc_get_order( $subscription_id );
				}
				$notes       = esc_html__( 'The Subscription has on-hold due to failed payment', 'woocommerce-subscriptions-pro' );
				$subscription->add_order_note( $notes );
				$subscription->update_meta_data( 'wps_subscription_status', 'on-hold' );
				$subscription->save();
			}
			/**
			 * Validate the webhook data.
			 *
			 * @param array() $payload .
			 * @param array() $headers .
			 * @param array() $webhook_secret .
			 */
			public function isValidWebhook( $payload, $headers, $webhook_secret ) {

				wps_paypal_subscription_log( 'Webhook Header' . wc_print_r( $headers, true ) );

				$signature       = $headers['PayPal-Transmission-Sig'];
				$transmission_id = $headers['PayPal-Transmission-Id'];

				if ( $signature && $transmission_id ) {
					return true;
				} else {
					return false;
				}

				// Concatenate the transmission ID and payload.
				$data = $transmission_id . '|' . $payload;

				// Calculate the HMAC SHA-256 signature of the data using the webhook secret.
				$calculated_signature = hash_hmac( 'sha256', $data, $webhook_secret );

				// Compare the calculated signature with the received signature.
				return hash_equals( $signature, $calculated_signature );
			}
			/**
			 * Fetch the woo subscription id from the paypal subscription id.
			 *
			 * @param string $paypal_subscription_id .
			 */
			public function fetch_wc_subscription_id( $paypal_subscription_id ) {
				global $wpdb;

				if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
					$query = "SELECT `order_id` FROM {$wpdb->prefix}wc_orders_meta WHERE `meta_key` = 'wps_created_paypal_subscription_id' AND `meta_value` = '$paypal_subscription_id'";
					$result = $wpdb->get_results( $query );
					$orderid = $result[0]->order_id;
				} else {
					$query = "SELECT `post_id` FROM {$wpdb->prefix}postmeta WHERE `meta_key` = 'wps_created_paypal_subscription_id' AND `meta_value` = '$paypal_subscription_id'";
					$result = $wpdb->get_results( $query );
					$orderid = $result[0]->post_id;
				}

				return $orderid;
			}
		}
	}
}
