<?php
/**
 * WWP_Checkout_Block_Integration class
 *
 * @package  WooCommerce Wholesale Pricing
 * @since    2.3.0
 */

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

/**
 * Class for integrating with WooCommerce Blocks scripts.
 *
 * @version  2.3.0
 */
class WWP_Checkout_Block_Integration implements IntegrationInterface {

	/**
	 * Whether the intregration has been initialized.
	 *
	 * @var boolean
	 */
	protected $is_initialized;

	/**
	 * The single instance of the class.
	 *
	 * @var WWP_Checkout_Block_Integration
	 */
	protected static $_instance = null;

	/**
	 * Main WWP_Checkout_Block_Integration instance. Ensures only one instance of WWP_Checkout_Block_Integration is loaded or can be loaded.
	 *
	 * @static
	 * @return WWP_Checkout_Block_Integration
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * The name of the integration.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'woocommerce-wholesale-pricing';
	}

	/**
	 * When called invokes any initialization/setup for the integration.
	 */
	public function initialize() {

		if ( $this->is_initialized ) {
			return;
		}

		$script_asset_path = WWP_PLUGIN_PATH . 'build/requisition-list/woocommerce-wholesale-pricing-frontend.asset.php';
		$script_asset      = file_exists( $script_asset_path )
		? require $script_asset_path
		: array(
			'dependencies' => array(),
			'version'      => '2.3.0',
		);
		
		wp_register_script(
			'wwp-checkout-blocks',
			WWP_PLUGIN_URL . '/build/requisition-list/requisition-list-button-cart.js',
			$script_asset[ 'dependencies' ],
			$script_asset[ 'version' ],
			true
		);

		wp_register_script(
			'wwp-checkout-blocks-frontend',
			WWP_PLUGIN_URL . '/build/requisition-list/requisition-list-button-cart-frontend.js',
			$script_asset[ 'dependencies' ],
			$script_asset[ 'version' ],
			true
		);

		$this->is_initialized = true;
	}

	/**
	 * Returns an array of script handles to enqueue in the frontend context.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {
		return array( 'wwp-checkout-blocks', 'wwp-checkout-blocks-frontend' );
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		return array( 'wwp-checkout-blocks' );
	}

	/**
	 * An array of key, value pairs of data made available to the block on the client side.
	 *
	 * @return array
	 */
	public function get_script_data() {
		return array(
			'woocommerce-wholesale-pricing-checkout-blocks' => 'active',
		);
	}
}
