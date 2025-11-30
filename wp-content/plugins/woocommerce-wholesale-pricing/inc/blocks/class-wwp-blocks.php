<?php
/**
 * WWP_Blocks_Compatibility class
 *
 * @package  WooCommerce Wholesale Pricing
 * @since    2.3.0
 */

 // Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Wholesale Pricing.
 *
 * @version 2.3.0
 */
class WWP_Blocks_Compatibility {
		
	/**
	 * Method init
	 *
	 * @return void
	 */
	public static function init() {
		$settings = get_option( 'wwp_wholesale_pricing_options', true );

		if ( ! did_action( 'woocommerce_blocks_loaded' ) ) {
			return;
		}

		require_once WWP_PLUGIN_PATH . 'inc/blocks/class-wwp-checkout-blocks-integration.php' ;

		if ( isset( $settings['requisition_list_cart_page'] ) && 'yes' == $settings['requisition_list_cart_page'] ) {
			add_action(
				'woocommerce_blocks_cart_block_registration',
				function ( $registry ) {
					$registry->register( WWP_Checkout_Block_Integration::instance() );
				}
			);
		}

		add_action( 'woocommerce_blocks_payment_method_type_registration', function ( $payment_method_registry ) {
			require_once WWP_PLUGIN_PATH . 'inc/blocks/class-wwp-payment-gateway-block.php' ;
			$settings = get_option('wwp_wholesale_pricing_options', true);
			if (!isset($settings['enable_custom_payment_method']) || 'yes' != $settings['enable_custom_payment_method'] ) {
				return ;
			}
			if (! is_wholesaler_user(get_current_user_id()) && !is_admin()  ) {
				return ;
			}
			$role = get_current_user_role_id();
			if ($role) {  
				$payment_method_name = get_term_meta($role, 'wwp_wholesale_payment_method_name', true);
			} else {
				$payment_method_name =  $settings['payment_method_name'];
			}
			
			foreach ( (array) $payment_method_name as  $key => $wholesale_custom_gateway ) {
				if (array_key_exists($key, $settings['payment_method_name']) ) {
					$class = 'WWP_Payment_Gateway_Handler';
					if (class_exists($class) ) { 
						$wholesale_custom_gateway_id = strtolower(str_replace(' ', '_', $wholesale_custom_gateway));
						if (is_wholesaler_user(get_current_user_id()) ) {
							if ('yes' == $wholesale_custom_gateway ) {
								$payment_method_registry->register( new WWP_Payment_Gateway_Handler($key) );
							}
						} else {
							$payment_method_registry->register( new WWP_Payment_Gateway_Handler($key) );
						} 
					}
				}
			}
		} );
	}
}

WWP_Blocks_Compatibility::init();
