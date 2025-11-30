<?php
/**
 * The file that defines the common functions
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wpswings.com
 * @since      1.0.0
 *
 * @package    Wps_Paypal_Subscription_Integration
 * @subpackage Wps_Paypal_Subscription_Integration/includes
 */

if ( ! function_exists( 'get_subscripion_product_id' ) ) {
	/**
	 * Get the subscription's product id.
	 *
	 * @param object $order .
	 */
	function get_subscripion_product_id( $order ) {
		$product_id = 0;
		// Loop through each cart item.
		foreach ( $order->get_items() as $item_id => $item ) {
			$product_id = $item->get_product_id();
			if ( $item->get_variation_id() ) {
				$product_id = $item->get_variation_id();
			}
			$product = wc_get_product( $product_id );
			if ( wps_sfw_check_product_is_subscription( $product ) ) {
				return $product_id;
			}
		}
		return $product_id;
	}
}

if ( ! function_exists( 'wps_paypal_subscription_order_pay' ) ) {
	/**
	 * Generate the paypal button from the paypal server itself
	 *
	 * @param object $order .
	 */
	function wps_paypal_subscription_order_pay( $order ) {
		if ( ! $order->needs_payment() ) {
			return;
		}
		$order_id         = $order->get_id();

		$existing_plan_id = $order->get_meta( '_wps_paypal_subscription_plan_id' );
		$existing_prod_id = $order->get_meta( '_wps_paypal_subscription_prod_id' );

		
		?>
		<div id="paypal-button-container"></div>
		<?php
		$settings = get_option( 'woocommerce_wps_paypal_subscription_settings' );
		$client_id = $settings['client_id'];
		if ( 'yes' == $settings['testmode'] ) {
			$client_id = $settings['sandbox_client_id'];
		}
		?>
		<script src="https://www.paypal.com/sdk/js?client-id=<?php echo esc_attr( $client_id ); ?>&vault=true&intent=subscription"></script>
		<script>
		paypal.Buttons({
			createSubscription: function(data, actions) {
			return actions.subscription.create({
			'plan_id': '<?php echo esc_html( $existing_plan_id ); ?>',
			});
			},
			onApprove: function( data, actions) {
				jQuery.ajax({
					type: 'post',
					dataType: 'json',
					url: '<?php echo esc_html( admin_url( 'admin-ajax.php' ) ); ?>',
					data: {
						order_id : '<?php echo esc_html( $order_id ); ?>',
						subscribed_data : data,					
						action: 'wps_paypal_subscribed_data',
						nonce : '<?php echo esc_html( wp_create_nonce( 'wps_paypal_subscription_nonce' ) ); ?>',
					},
					success: function(data) {
						window.location.href = data.redirect_url;
					}
				});
			}
		}).render('#paypal-button-container'); // Renders the PayPal button
		</script>
		<?php
	}
}

if ( ! function_exists( 'wps_paypal_subscription_before_payment' ) ) {
	/**
	 * Initiate product and plan creation on paypal.
	 *
	 * @param object $order .
	 */
	function wps_paypal_subscription_before_payment( $order ) {
		if ( ! function_exists( 'get_subscripion_product_id' ) ) {
			return;
		}
		$product_id = get_subscripion_product_id( $order );
		
		$session_data = [
			'prod_res' => null,
			'prod_id'  => 0,
			'plan_res' => null,
			'plan_id'  => 0,
		];

		if ( ! class_exists( 'WPS_Paypal_Subscription_Integration_Request_API' ) ) {
			$order->update_meta_data( '_wps_paypal_subscription_data', $session_data );
			$order->save();
			return;
		}

		$api_obj = new WPS_Paypal_Subscription_Integration_Request_API();

		// Create product on PayPal
		$create_product_response  = $api_obj->create_product( $product_id );
		$session_data['prod_res'] = $create_product_response['result'];

		if ( 'success' !== $create_product_response['result'] ) {
			$order->update_meta_data( '_wps_paypal_subscription_data', $session_data );
			$order->save();
			return;
		}

		$prod_id                 = $create_product_response['prod_id'];
		$session_data['prod_id'] = $prod_id;

		$order->update_meta_data( '_wps_paypal_subscription_prod_id', $prod_id );

		// Determine pricing
		$is_coupon_applied = wps_paypal_subscription_has_discount_coupon( $order );
		[ $line_total, $line_taxes ] = wps_paypal_subscription_get_line_totals( $order, $is_coupon_applied );

		// Create plan on PayPal
		$create_plan_response     = $api_obj->create_subscription_plan( $product_id, $prod_id, $line_total, $line_taxes );
		$session_data['plan_res'] = $create_plan_response['result'];


		if ( 'success' === $create_plan_response['result'] ) {
			$plan_id                 = $create_plan_response['plan_id'];
			$session_data['plan_id'] = $plan_id;
			$order->update_meta_data( '_wps_paypal_subscription_plan_id', $plan_id );
		}
		$order->update_meta_data( '_wps_paypal_subscription_data', $session_data );
		$order->save();
	}
}
if ( ! function_exists( 'wps_paypal_subscription_has_discount_coupon' ) ) {
	/**
	 * Check if any discount coupon is applied to the order.
	 *
	 * @param object $order .
	 */
	function wps_paypal_subscription_has_discount_coupon( $order ) {
		$allow_dis_sub = [ 'recurring_product_percent_discount', 'recurring_product_discount' ];
	
		foreach ( $order->get_coupon_codes() as $coupon_code ) {
			$coupon = new WC_Coupon( $coupon_code );
			if ( in_array( $coupon->get_discount_type(), $allow_dis_sub, true ) ) {
				return true;
			}
		}
		return false;
	}
}

if ( ! function_exists( 'wps_paypal_subscription_get_line_totals' ) ) {
	/**
	 * Get subscription product line totals (considering coupons).
	 */
	function wps_paypal_subscription_get_line_totals( $order, $is_coupon_applied ) {
		foreach ( $order->get_items() as $item ) {
			$product_id = $item->get_variation_id() ?: $item->get_product_id();
			$product    = wc_get_product( $product_id );
	
			if ( wps_sfw_check_product_is_subscription( $product ) ) {
				return $is_coupon_applied
					? [ $item->get_total(), $item->get_total_tax() ]
					: [ $item->get_subtotal(), $item->get_subtotal_tax() ];
			}
		}
		return [ 0, 0 ];
	}
}


if ( ! function_exists( 'wps_paypal_subscription_log' ) ) {
	/** Create Logs during the process
	 *
	 * @param string $message .
	 */
	function wps_paypal_subscription_log( $message ) {
		if ( ! class_exists( 'WC_Logger' ) ) {
			return;
		}
		$enable_log = get_option( 'wps_sfw_enable_subscription_log', 'off' );
		if ( 'on' != $enable_log ) {
			return;
		}

		$logger = wc_get_logger();

		$log_entry  = "\n" . '====Log Details: ===' . "\n";
		$log_entry .= '====Start Log====' . "\n" . $message . "\n" . '====End Log====' . "\n\n";

		$logger->debug( $log_entry, array( 'source' => 'wps-sfw-paypal-subscription' ) );
	}
}
