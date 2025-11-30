<?php
/**
 * The subscription helper.
 *
 *  @package Woocommerce_Subscriptions_Pro/includes
 */

declare( strict_types = 1 );

use WooCommerce\PayPalCommerce\WcSubscriptions\Helper\SubscriptionHelper;

/**
 * Class WPS_WC_PayPal_Payments_Helper
 */
class WPS_WC_PayPal_Payments_Helper extends SubscriptionHelper {

	/**
	 * Whether the current product is a subscription.
	 *
	 * @return bool
	 */
	public function current_product_is_subscription(): bool {
		$product = wc_get_product();
		return $product && wps_sfw_check_product_is_subscription( $product );
	}

	/**
	 * Whether the current cart contains subscriptions.
	 *
	 * @return bool
	 */
	public function cart_contains_subscription(): bool {
		return wps_sfw_is_cart_has_subscription_product();
	}

	/**
	 * Checks if order contains subscription.
	 *
	 * @param int $order_id The order ID.
	 * @return boolean Whether order is a subscription or not.
	 */
	public function has_subscription( $order_id ): bool {
		return wps_sfw_order_has_subscription( $order_id );
	}

	/**
	 * Whether only automatic payment gateways are accepted.
	 *
	 * @return bool
	 */
	public function accept_only_automatic_payment_gateways(): bool {
		return wps_sfw_is_cart_has_subscription_product();
	}

	/**
	 * Checks if cart contains only one item.
	 *
	 * @return bool
	 */
	public function cart_contains_only_one_item(): bool {
		$cart = WC()->cart;
		if ( ! $cart || $cart->is_empty() ) {
			return false;
		}

		if ( count( $cart->get_cart() ) > 1 ) {
			return false;
		}

		return true;
	}

	/**
	 * Whether pay for order contains subscriptions.
	 *
	 * @return bool
	 */
	public function order_pay_contains_subscription(): bool {
		if ( ! is_wc_endpoint_url( 'order-pay' ) ) {
			return false;
		}

		global $wp;
		$order_id = (int) $wp->query_vars['order-pay'];
		if ( 0 === $order_id ) {
			return false;
		}

		return $this->has_subscription( $order_id );
	}

	/**
	 * Returns order transaction from the given order.
	 *
	 * @param \WPS_Subscription $order object.
	 * @return string
	 */
	public function get_previous_transaction( $order ): string {
		if ( ! $order ) {
			return '';
		}
		$subcription_renewals = $order->get_meta( 'wps_wsp_renewal_order_data' );
		if ( is_array( $subcription_renewals ) ) {
			foreach( array_reverse( $subcription_renewals ) as $renewal_id ) {
				$renewal_order = wc_get_order( $renewal_id );
				if ( $renewal_order && $renewal_order->is_paid() && $renewal_order->get_transaction_id() ) {
					return $renewal_order->get_transaction_id();
				}
			}
		}
		return '';
	}
}
