<?php
/**
 * Provide a common view for the plugin
 *
 * This file is used to markup the common aspects of the plugin.
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    Subscriptions_For_Woocommerce
 * @subpackage Subscriptions_For_Woocommerce/include
 */

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
/**
 * Extend the WPS Paypal Gateway
 */
final class PSI_Block_Support extends AbstractPaymentMethodType {

	/**
	 * The gateway instance.
	 *
	 * @var PSI_Block_Support
	 */
	private $gateway;

	/**
	 * Payment method name/id/slug.
	 *
	 * @var string
	 */
	protected $name = 'wps_paypal_subscription';

	/**
	 * Extend the WPS Paypal Gateway function
	 *
	 * @return void
	 */
	public function initialize() {
		$this->settings = get_option( 'woocommerce_wps_paypal_subscription_settings', array() );
		$this->gateway  = new WPS_Paypal_Subscription_Integration_Gateway();
	}
	/**
	 * Extend the WPS Paypal Gateway function
	 *
	 * @return boolean
	 */
	public function is_active() {
		return $this->gateway->is_available();
	}

	/**
	 * Extend the WPS Paypal Gateway function
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$script_path       = 'includes/wps-paypal-subscription/wc-blocks-compatibility/wps-psi-wc-blocks.js';
		$script_asset_path = WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'includes/wps-paypal-subscription/wc-blocks-compatibility/wps-psi-wc-blocks-asset.php';

		$script_asset      = file_exists( $script_asset_path )
		? require $script_asset_path
		: array(
			'dependencies' => array(),
			'version'      => '1.0.0',
		);
		$script_url        = WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . $script_path;

		wp_register_script(
			'wps-psi-blocks',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
		$gateway_data = get_option( 'woocommerce_wps_paypal_subscription_settings', false );
		wp_localize_script(
			'wps-psi-blocks',
			'wps_psi_blocks_object',
			array(
				'gateway_title' => ( ! empty( $gateway_data ) && isset( $gateway_data['title'] ) ) ? $gateway_data['title'] : esc_html__( 'WPS PayPal', 'woocommerce-subscriptions-pro' ),
			)
		);
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'wps-psi-blocks', 'wps-paypal-subscription-integration', WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_PATH . 'languages/' );
		}

		return array( 'wps-psi-blocks' );
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		return array(
			'title'       => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),
			'supports'    => array_filter( $this->gateway->supports, array( $this->gateway, 'supports' ) ),
		);
	}
}
