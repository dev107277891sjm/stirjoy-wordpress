<?php
/**
 * The file used to make a request to the paypal server for various api call
 *
 * @link       https://wpswings.com
 * @since      2.4.0
 *
 * @package    Wps_Paypal_Subscription_Integration
 * @subpackage Wps_Paypal_Subscription_Integration/includes
 */

/**
 * The file used to make a request to the paypal server for various api call
 *
 * @link       https://wpswings.com
 * @since      1.0.0
 *
 * @package    Wps_Paypal_Subscription_Integration
 * @subpackage Wps_Paypal_Subscription_Integration/includes
 */
class WPS_Paypal_Subscription_Integration_Request_API {
	/**
	 * Client id.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $client_id;

	/**
	 * Client secret.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $client_secret;

	/**
	 * Testmode.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $testmode;

	/**
	 * Class constructor
	 */
	public function __construct() {
		$saved_data          = $this->get_gateway_settings();
		$this->client_id     = $saved_data['client_id'];
		$this->client_secret = $saved_data['client_secret'];
		$this->testmode      = $saved_data['testmode'];
	}

	/**
	 * Get the access token
	 *
	 * @throws \Exception .
	 * @return array
	 */
	protected function get_access_token() {
		$saved_data = $this->get_gateway_settings();

		$endpoint = $this->testmode ? WPS_PAYPAL_SUBSCRIPTION_INTEGRATION_SANDBOX_URL : WPS_PAYPAL_SUBSCRIPTION_INTEGRATION_LIVE_URL;

		try {
			$response = wp_remote_post(
				$endpoint . '/v1/oauth2/token',
				array(
					'method'      => 'POST',
					'timeout'     => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => array(
						'Accept' => 'application/json',
						'Accept-Language' => 'en_US',
						'Authorization'   => 'Basic ' . base64_encode( $this->client_id . ':' . $this->client_secret ),
					),
					'body' => array(
						'grant_type' => 'client_credentials',
					),
				)
			);
			$response_data = json_decode( wp_remote_retrieve_body( $response ) );
			if ( ! is_wp_error( $response ) && 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
				$response = json_decode( wp_remote_retrieve_body( $response ) );
				return array(
					'result'       => 'success',
					'access_token' => isset( $response->access_token ) ? $response->access_token : '',
					'app_id'       => isset( $response->app_id ) ? $response->app_id : '',
				);
			}
			throw new Exception( $response_data->error_description );

		} catch ( Exception $e ) {
			return array(
				'result' => 'error',
				'response' => $e->getMessage(),
			);
		}
	}

	/**
	 * Get saved setting data
	 *
	 * @return array
	 */
	protected function get_gateway_settings() {
		$gateway_settings = get_option( 'woocommerce_wps_paypal_subscription_settings' );

		$return_data = array(
			'client_id'     => null,
			'client_secret' => null,
			'testmode'      => false,
		);
		if ( ! empty( $gateway_settings ) ) {
			$client_id             = isset( $gateway_settings['client_id'] ) ? $gateway_settings['client_id'] : null;
			$client_secret         = isset( $gateway_settings['client_secret'] ) ? $gateway_settings['client_secret'] : null;
			$test_mode             = isset( $gateway_settings['testmode'] ) ? 'yes' === $gateway_settings['testmode'] : false;
			$sandbox_client_id     = isset( $gateway_settings['sandbox_client_id'] ) ? $gateway_settings['sandbox_client_id'] : null;
			$sandbox_client_secret = isset( $gateway_settings['sandbox_client_secret'] ) ? $gateway_settings['sandbox_client_secret'] : null;
			if ( $test_mode ) {
				$return_data['client_id']     = $sandbox_client_id;
				$return_data['client_secret'] = $sandbox_client_secret;
			} else {
				$return_data['client_id']     = $client_id;
				$return_data['client_secret'] = $client_secret;
			}
			$return_data['testmode'] = $test_mode;
		}
		return $return_data;
	}

	/**
	 * Creates product on the paypal portal
	 *
	 * @param mixed $product_id .
	 * @throws \Exception .
	 * @return array
	 */
	public function create_product( $product_id ) {

		if ( empty( $product_id ) ) {
			return array(
				'result'   => 'error',
				'response' => 'Product does not exist',
			);
		}
		$product         = wc_get_product( $product_id );
		$access_response = self::get_access_token();

		if ( 'success' !== $access_response['result'] ) {
			return array(
				'result'   => 'error',
				'response' => $access_response['response'],
			);
		}
		$url      = $this->testmode ? WPS_PAYPAL_SUBSCRIPTION_INTEGRATION_SANDBOX_URL : WPS_PAYPAL_SUBSCRIPTION_INTEGRATION_LIVE_URL;
		$endpoint = $url . '/v1/catalogs/products';
		try {

			$request_data = array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array(
					'Content-Type'      => 'application/json',
					'Authorization'     => 'Bearer ' . $access_response['access_token'],
					'PayPal-Request-Id' => null,
				),
				'body' => wp_json_encode(
					array(
						'name'        => $product->get_name(),
						'description' => $product->get_short_description() ? $product->get_short_description() : 'no description',
						'type'        => $product->is_virtual() ? 'DIGITAL' : 'PHYSICAL',
						'category'    => 'OTHER',
						'image_url'   => null,
						'home_url'    => null,
					)
				),
			);

			wps_paypal_subscription_log( 'Create Product Request Data' . wc_print_r( $request_data, true ) );

			$response = wp_remote_post( $endpoint, $request_data );

			$response_data = json_decode( wp_remote_retrieve_body( $response ) );

			wps_paypal_subscription_log( 'Create Product Body Response Data' . wc_print_r( $response_data, true ) );
			if ( ! is_wp_error( $response ) && 201 === (int) wp_remote_retrieve_response_code( $response ) ) {

				$prod_id = $response_data->id;

				return array(
					'prod_id' => $prod_id,
					'result'  => 'success',
				);
			}
			throw new Exception( $response_data->message );

		} catch ( Exception $e ) {
			return array(
				'result'   => 'error',
				'response' => $e->getMessage(),
			);
		}
	}

	/**
	 * Create a plan and the product to that plan
	 *
	 * @param mixed $product_id .
	 * @param mixed $prod_id .
	 * @param mixed $line_total .
	 * @param mixed $line_tax .
	 * @throws \Exception .
	 * @return array
	 */
	public function create_subscription_plan( $product_id, $prod_id, $line_total, $line_tax ) {
		if ( empty( $product_id ) ) {
			return array(
				'result'   => 'error',
				'response' => 'Product does not exist',
			);
		}

		// Meta.
		$interval        = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_number', true ) ? wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_number', true ) : 0;
		$type            = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_interval', true ) ? wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_interval', true ) : '';
		$trial_interval  = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_free_trial_number', true ) ? wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_free_trial_number', true ) : 0;
		$trial_type      = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_free_trial_interval', true ) ? wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_free_trial_interval', true ) : '';
		$sign_fee        = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_initial_signup_price', true ) ? wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_initial_signup_price', true ) : 0;
		$interval_expiry = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_expiry_number', true ) ? wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_expiry_number', true ) : 0;

		$currency       = get_woocommerce_currency();
		$product        = wc_get_product( $product_id );
		$item_line_total  = number_format( ( (float) $line_total + (float) $line_tax - (float) $sign_fee ), 2, '.', '' );

		// Validate: PayPal requires REGULAR cycle with price > 0.
		if ( floatval( $item_line_total ) <= 0 ) {
			return array(
				'result'   => 'error',
				'response' => 'PayPal requires a REGULAR billing cycle with price greater than 0.',
			);
		}

		// Plan name.
		$plan_name = 'Plan-' . $interval . ' ' . $type;
		if ( $trial_interval ) {
			$plan_name .= ' Trial-' . $trial_interval . ' ' . $trial_type;
		}
		if ( $sign_fee ) {
			$plan_name .= ' Signup Fee-' . $sign_fee;
		}

		// Get access token.
		$access_response = self::get_access_token();

		if ( 'success' !== $access_response['result'] ) {
			return array(
				'result'   => 'error',
				'response' => $access_response['response'],
			);
		}

		$url      = $this->testmode ? WPS_PAYPAL_SUBSCRIPTION_INTEGRATION_SANDBOX_URL : WPS_PAYPAL_SUBSCRIPTION_INTEGRATION_LIVE_URL;
		$endpoint = $url . '/v1/billing/plans';

		// Build billing cycles.
		$billing_cycles = [];

		// Add TRIAL first (sequence 1).
		if ( $trial_interval ) {
			$billing_cycles[] = array(
				'frequency'    => array(
					'interval_unit'  => strtoupper( $trial_type ),
					'interval_count' => (int) $trial_interval,
				),
				'tenure_type'  => 'TRIAL',
				'sequence'     => 1,
				'total_cycles' => 1,
			);
		}

		// Add REGULAR second (sequence 2 if trial exists, else 1).
		$billing_cycles[] = array(
			'frequency'      => array(
				'interval_unit'  => strtoupper( $type ),
				'interval_count' => (int) $interval,
			),
			'tenure_type'    => 'REGULAR',
			'sequence'       => $trial_interval ? 2 : 1,
			'total_cycles'   => (int) $interval_expiry,
			'pricing_scheme' => array(
				'fixed_price' => array(
					'value'         => $item_line_total,
					'currency_code' => $currency,
				),
			),
		);

		$request_body_data = array(
			'product_id'          => $prod_id,
			'name'                => $plan_name,
			'description'         => $plan_name,
			'billing_cycles'      => $billing_cycles,
			'payment_preferences' => array(
				'auto_bill_outstanding'     => true,
				'setup_fee'                 => array(
					'value'         => $sign_fee,
					'currency_code' => $currency,
				),
				'setup_fee_failure_action'  => 'CONTINUE',
				'payment_failure_threshold' => 3,
			),
		);

		// Log request.
		wps_paypal_subscription_log( 'Create Subscription Plan Request Data' . wc_print_r( $request_body_data, true ) );

		$request_data = array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(
				'Accept'            => 'application/json',
				'Accept-Language'   => 'en_US',
				'Authorization'     => 'Bearer ' . $access_response['access_token'],
				'Content-Type'      => 'application/json',
				'PayPal-Request-Id' => null,
			),
			'body' => wp_json_encode( $request_body_data ),
		);

		try {
			$response = wp_remote_post( $endpoint, $request_data );
			$response_data = json_decode( wp_remote_retrieve_body( $response ) );

			wps_paypal_subscription_log( 'Create Subscription Plan Body Response Data' . wc_print_r( $response_data, true ) );

			if ( ! is_wp_error( $response ) && 201 === (int) wp_remote_retrieve_response_code( $response ) ) {
				return array(
					'result'  => 'success',
					'plan_id' => $response_data->id,
				);
			}

			throw new Exception( $response_data->message ?? 'Unknown PayPal error' );

		} catch ( Exception $e ) {
			return array(
				'result'   => 'error',
				'response' => $e->getMessage(),
			);
		}
	}

	/**
	 * This function allow to cancel the paypal subscription.
	 *
	 * @param mixed $paypal_subscription_id is paypal subscription id created on the paypal.
	 * @throws \Exception .
	 * @return array
	 */
	public function wps_paypal_subscription_cancellation( $paypal_subscription_id ) {
		$url             = $this->testmode ? WPS_PAYPAL_SUBSCRIPTION_INTEGRATION_SANDBOX_URL : WPS_PAYPAL_SUBSCRIPTION_INTEGRATION_LIVE_URL;
		$endpoint        = $url . '/v1/billing/subscriptions/' . $paypal_subscription_id . '/cancel';
		$access_response = self::get_access_token();

		if ( 'success' !== $access_response['result'] ) {
			return array(
				'result'   => 'error',
				'response' => $access_response['response'],
			);
		}

		$request_data = array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(
				'Accept'            => 'application/json',
				'Accept-Language'   => 'en_US',
				'Authorization'     => 'Bearer ' . $access_response['access_token'],
				'Content-Type'      => 'application/json',
				'PayPal-Request-Id' => null,
			),
		);

		try {
			$response = wp_remote_post( $endpoint, $request_data );

			$response_data = json_decode( wp_remote_retrieve_body( $response ) );

			wps_paypal_subscription_log( 'Subscription Cancellation Body Response Data' . wc_print_r( $response_data, true ) );
			if ( ! is_wp_error( $response ) && 204 === (int) wp_remote_retrieve_response_code( $response ) ) {

				return array(
					'result'   => 'success',
					'response' => 'Subscription has been cancelled',
				);
			}
			throw new Exception( $response_data->error_description );

		} catch ( Exception $e ) {
			return array(
				'result'   => 'error',
				'response' => $e->getMessage(),
			);
		}
	}
}
new WPS_Paypal_Subscription_Integration_Request_API();
