<?php
defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Automattic\WooCommerce\Blocks\Payments\PaymentResult;
use Automattic\WooCommerce\Blocks\Payments\PaymentContext;

/**
 * WWP_Payment_Gateway_Handler
 */
final class WWP_Payment_Gateway_Handler extends AbstractPaymentMethodType {

	/**
	 * Name
	 *
	 * @var string
	 */
	protected $name = '';
	
	/**
	 * Method __construct
	 *
	 * @param string $payment_method_name
	 *
	 * @return void
	 */
	public function __construct( $payment_method_name ) {
		$this->name = strtolower( str_replace( ' ', '_', $payment_method_name ) );
	}

	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {
		$payment_gateways_class = WC()->payment_gateways();
		$payment_gateways       = $payment_gateways_class->payment_gateways();
		$this->settings                = get_option( 'woocommerce_' . $this->name . '_settings', array(
			'title'         => isset( $payment_gateways[$this->name] ) ? $payment_gateways[$this->name]->settings['title'] : '',
			'description'   => isset( $payment_gateways[$this->name] ) ? $payment_gateways[$this->name]->settings['description'] : '',
		) );
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active() {
		$payment_gateways_class = WC()->payment_gateways();
		$payment_gateways       = $payment_gateways_class->payment_gateways();
		if ( isset( $payment_gateways[$this->name] ) ) {
			return $payment_gateways[$this->name]->is_available();
		}
		return false;
	}

	/**
	 * Register scripts
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$asset_path   = WWP_PLUGIN_PATH . '/build/payment-gateway/wwp-payment-gateway.asset.php';
		$version      = '2.3.0';
		$dependencies = array();

		if ( file_exists( $asset_path ) ) {
			$asset        = require $asset_path;
			$version      = is_array( $asset ) && isset( $asset['version'] ) ? $asset['version'] : $version;
			$dependencies = is_array( $asset ) && isset( $asset['dependencies'] ) ? $asset['dependencies'] : $dependencies;
		}

		wp_register_script(
			'wwp-payment',
			WWP_PLUGIN_URL . '/build/payment-gateway/wwp-payment-gateway.js',
			$dependencies,
			$version,
			true
		);

		return array( 'wwp-payment' );
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	public function get_payment_method_data() {
		return $this->settings;
	}
}
