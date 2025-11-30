<?php
/**
 * The file that defines the core plugin api class
 *
 * A class definition that includes api's endpoints and functions used across the plugin
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    Woocommerce_Subscriptions_Pro
 * @subpackage Woocommerce_Subscriptions_Pro/package/rest-api/version1
 */

/**
 * The core plugin  api class.
 *
 * This is used to define internationalization, api-specific hooks, and
 * endpoints for plugin.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woocommerce_Subscriptions_Pro
 * @subpackage Woocommerce_Subscriptions_Pro/package/rest-api/version1
 * @author     WP Swings <webmaster@wpswings.com>
 */
class Woocommerce_Subscriptions_Pro_Rest_Api {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin api.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the merthods, and set the hooks for the api and
	 *
	 * @since    1.0.0
	 * @param   string $plugin_name    Name of the plugin.
	 * @param   string $version        Version of the plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}


	/**
	 * Define endpoints for the plugin.
	 *
	 * Uses the Woocommerce_Subscriptions_Pro_Rest_Api class in order to create the endpoint
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function wps_wsp_add_endpoint() {
		register_rest_route(
			'wsp-route/v1',
			'/wsp-update-subscription/(?P<id>\d+)', // Accepts numeric ID in the URL.
			array(
				'methods'             => 'PUT',
				'callback'            => array( $this, 'wps_wsp_update_subscription_callback' ),
				'permission_callback' => array( $this, 'wps_wsp_subscription_permission_check' ),
			)
		);

		register_rest_route(
			'wsp-route/v1',
			'/view-subscription/(?P<id>\d+)',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'wps_wsp_view_particular_susbcription_callback' ),
				'permission_callback' => array( $this, 'wps_wsp_subscription_permission_check' ),
			)
		);
	}

	/**
	 * Check permission for the subscription.
	 *
	 * @name wps_wsp_subscription_permission_check
	 * @param   object $request  request.
	 * @since    1.0.0
	 */
	public function wps_wsp_subscription_permission_check( $request ) {
		$request_params = $request->get_params();
		$wps_secretkey  = isset( $request_params['consumer_secret'] ) ? $request_params['consumer_secret'] : '';

		return $this->wps_wsp_validate_secretkey( $wps_secretkey );
	}


	/**
	 * Valiadte secret key.
	 *
	 * @name wps_sfw_validate_secretkey
	 * @param   string $wps_secretkey  wps_secretkey.
	 * @since    1.0.0
	 */
	public function wps_wsp_validate_secretkey( $wps_secretkey ) {
		$wps_secret_code = '';

		if ( wps_wsp_check_api_enable() ) {
			$wps_secret_code = wps_wsp_api_get_secret_key();
		}

		if ( '' == $wps_secretkey ) {
			return false;
		} elseif ( trim( $wps_secret_code ) === trim( $wps_secretkey ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Update subscription.
	 *
	 * @name wps_wsp_update_subscription_callback
	 * @param   object $request  request.
	 * @since    1.0.0
	 */
	public function wps_wsp_update_subscription_callback( $request ) {
		$request_params = $request->get_params();
		$action          = isset( $request_params['action'] ) ? sanitize_text_field( $request_params['action'] ) : '';
		$subscription_id = $request['id']; // This comes from the URL.

		if ( empty( $action ) ) {
			return new WP_Error( 'invalid_request', __( 'Invalid request parameters.', 'woocommerce-subscriptions-pro' ), array( 'status' => 400 ) );
		} elseif ( ! wps_sfw_check_valid_subscription( $subscription_id ) || ! wps_wsp_get_meta_data( $subscription_id, 'wps_parent_order', true ) ) {

			return new WP_Error( 'invalid_subscription_id', __( 'Invalid subscription ID.', 'woocommerce-subscriptions-pro' ), array( 'status' => 400 ) );
		}

		// Process the subscription update based on the action.
		switch ( $action ) {
			case 'cancel':
				$wps_subscription_status = wps_wsp_get_meta_data( $subscription_id, 'wps_subscription_status', true );
				if ( 'cancelled' === $wps_subscription_status ) {
					return new WP_Error( 'subscription_already_cancelled', __( 'Subscription is already cancelled.', 'woocommerce-subscriptions-pro' ), array( 'status' => 400 ) );
				} else {
					wps_sfw_update_meta_data( $subscription_id, 'wps_subscription_status', 'cancelled' );
					$order = wc_get_order( $subscription_id );
					$order_notes = esc_html__( 'A subscription has cancelled through api call.', 'woocommerce-subscriptions-pro' );
					$order->add_order_note( $order_notes );
					return new WP_Error( 'subscription_cancel', __( 'Subscription is now cancelled.', 'woocommerce-subscriptions-pro' ), array( 'status' => 200 ) );
				}
				break;
			case 'reactivate':
				$wps_subscription_status = wps_wsp_get_meta_data( $subscription_id, 'wps_subscription_status', true );
				if ( 'active' === $wps_subscription_status ) {
					return new WP_Error( 'subscription_already_active', __( 'Subscription is already active.', 'woocommerce-subscriptions-pro' ), array( 'status' => 400 ) );
				} elseif ( 'paused' === $wps_subscription_status ) {
					wps_sfw_update_meta_data( $subscription_id, 'wps_subscription_status', 'active' );
					$order = wc_get_order( $subscription_id );
					wps_wsp_reactivate_time_calculation( $subscription_id );
					$order_notes = esc_html__( 'A subscription has reactivated through api call.', 'woocommerce-subscriptions-pro' );
					$order->add_order_note( $order_notes );
					return new WP_Error( 'subscription_reactivate', __( 'Subscription is now reactivated.', 'woocommerce-subscriptions-pro' ), array( 'status' => 200 ) );
				} elseif ( 'cancelled' === $wps_subscription_status || 'expired' === $wps_subscription_status ) {
					return new WP_Error( 'subscription_not_reactivatable', __( 'Subscription cannot be reactivated because either it is expired or cancelled.', 'woocommerce-subscriptions-pro' ), array( 'status' => 400 ) );
				} elseif ( 'on-hold' === $wps_subscription_status ) {
					wps_sfw_update_meta_data( $subscription_id, 'wps_subscription_status', 'active' );
					$order = wc_get_order( $subscription_id );
					$order_notes = esc_html__( 'A subscription has reactivated through api call.', 'woocommerce-subscriptions-pro' );
					$order->add_order_note( $order_notes );
					return new WP_Error( 'subscription_reactivate', __( 'Subscription is now reactivated.', 'woocommerce-subscriptions-pro' ), array( 'status' => 200 ) );
				}
				break;
			case 'pause':
				$wps_subscription_status = wps_wsp_get_meta_data( $subscription_id, 'wps_subscription_status', true );
				if ( 'paused' === $wps_subscription_status ) {
					return new WP_Error( 'subscription_already_paused', __( 'Subscription is already paused.', 'woocommerce-subscriptions-pro' ), array( 'status' => 400 ) );
				} elseif ( 'cancelled' === $wps_subscription_status || 'expired' === $wps_subscription_status || 'on-hold' === $wps_subscription_status ) {
					 return new WP_Error( 'subscription_not_paused', __( 'Subscription cannot be paused because either it is cancelled, expired or on-hold.', 'woocommerce-subscriptions-pro' ), array( 'status' => 400 ) );
				} else {
					wps_sfw_update_meta_data( $subscription_id, 'wps_subscription_status', 'paused' );
					$order = wc_get_order( $subscription_id );
					wps_wsp_set_pause_subscription_timestamp( $subscription_id );
					$order_notes = esc_html__( 'A subscription has paused through api call.', 'woocommerce-subscriptions-pro' );
					$order->add_order_note( $order_notes );
					return new WP_Error( 'subscription_pause', __( 'Subscription is now paused.', 'woocommerce-subscriptions-pro' ), array( 'status' => 200 ) );
				}
				break;
			case 'update-payment':
				$wps_subscription_status = wps_wsp_get_meta_data( $subscription_id, 'wps_subscription_status', true );
				if ( 'cancelled' === $wps_subscription_status || 'expired' === $wps_subscription_status || 'on-hold' === $wps_subscription_status || 'paused' === $wps_subscription_status ) {
					return new WP_Error( 'subscription_not_updated', __( 'Subscription cannot be updated because either it is cancelled, expired, on-hold or paused.', 'woocommerce-subscriptions-pro' ), array( 'status' => 400 ) );
				} elseif ( 'active' == $wps_subscription_status ) {
					$next_payment_date_timestamp = isset( $request_params['next_payment_date_timestamp'] ) ? sanitize_text_field( $request_params['next_payment_date_timestamp'] ) : '';

					if ( empty( $next_payment_date_timestamp ) ) {
						return new WP_Error( 'invalid_next_payment_date', __( 'Next payment date timestamp is required.', 'woocommerce-subscriptions-pro' ), array( 'status' => 400 ) );
					} else {
						wps_wsp_update_meta_data( $subscription_id, 'wps_next_payment_date', $next_payment_date_timestamp );
						$order = wc_get_order( $subscription_id );
						$order_notes = esc_html__( 'A subscription payment has updated through api call.', 'woocommerce-subscriptions-pro' );
						$order->add_order_note( $order_notes );
						return new WP_Error( 'subscription_payment_updated', __( 'Subscription payment is now updated.', 'woocommerce-subscriptions-pro' ), array( 'status' => 200 ) );
					}
				}
				break;
			default:
				return new WP_Error( 'invalid_action', __( 'Invalid action specified.', 'woocommerce-subscriptions-pro' ), array( 'status' => 400 ) );
		}
	}

	/**
	 * View particular subscription.
	 *
	 * @name wps_wsp_view_particular_susbcription_callback
	 * @param   object $request  request.
	 * @since    1.0.0
	 */
	public function wps_wsp_view_particular_susbcription_callback( $request ) {
		$subcription_id = $request['id'];
		if ( ! wps_sfw_check_valid_subscription( $subcription_id ) || ! wps_wsp_get_meta_data( $subcription_id, 'wps_parent_order', true ) ) {

			return new WP_Error( 'invalid_subscription_id', __( 'Invalid subscription ID.', 'woocommerce-subscriptions-pro' ), array( 'status' => 400 ) );
		} else {
			$parent_order_id   = wps_sfw_get_meta_data( $subcription_id, 'wps_parent_order', true );
			$wps_subscription_status   = wps_sfw_get_meta_data( $subcription_id, 'wps_subscription_status', true );
			$product_name   = wps_sfw_get_meta_data( $subcription_id, 'product_name', true );
			$wps_recurring_total   = wps_sfw_get_meta_data( $subcription_id, 'wps_recurring_total', true );

			$wps_wsp_number   = wps_sfw_get_meta_data( $subcription_id, 'wps_sfw_subscription_number', true );
			$wps_wsp_interval   = wps_sfw_get_meta_data( $subcription_id, 'wps_sfw_subscription_interval', true );

			$wps_next_payment_date   = wps_sfw_get_meta_data( $subcription_id, 'wps_next_payment_date', true );
			$wps_susbcription_end   = wps_sfw_get_meta_data( $subcription_id, 'wps_susbcription_end', true );

			$wps_customer_id   = wps_sfw_get_meta_data( $subcription_id, 'wps_customer_id', true );
			$user = get_user_by( 'id', $wps_customer_id );

			$user_nicename = isset( $user->user_nicename ) ? $user->user_nicename : '';
			$wps_subscriptions_data[] = array(
				'subscription_id'           => $subcription_id,
				'parent_order_id'           => $parent_order_id,
				'status'                    => $wps_subscription_status,
				'product_name'              => $product_name,
				'recurring_amount'          => $wps_recurring_total,
				'interval_number'        => $wps_wsp_number,
				'interval_type'          => $wps_wsp_interval,
				'user_name'                 => $user_nicename,
				'next_payment_date'         => wps_sfw_get_the_wordpress_date_format( $wps_next_payment_date ),
				'subscriptions_expiry_date' => wps_sfw_get_the_wordpress_date_format( $wps_susbcription_end ),
			);

			$wps_wsp_rest_response['code'] = 200;
			$wps_wsp_rest_response['status'] = 'success';
			$wps_wsp_rest_response['data'] = $wps_subscriptions_data;

			return new WP_REST_Response( $wps_wsp_rest_response );
		}
	}
}
