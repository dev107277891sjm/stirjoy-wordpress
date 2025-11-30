<?php
/**
 * Pay for order form
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;
$order_totals = $order->get_order_item_totals(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

$payment_method = $order->get_payment_method();
?>
<form id="order_review" method="post">

	<table class="shop_table">
		<thead>
			<tr>
				<th class="product-name"><?php esc_html_e( 'Product', 'woocommerce-subscriptions-pro' ); ?></th>
				<th class="product-quantity"><?php esc_html_e( 'Qty', 'woocommerce-subscriptions-pro' ); ?></th>
				<th class="product-total"><?php esc_html_e( 'Totals', 'woocommerce-subscriptions-pro' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( count( $order->get_items() ) > 0 ) : ?>
				<?php foreach ( $order->get_items() as $item_id => $item ) : ?>
					<?php
					if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
						continue;
					}
					?>
					<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'order_item', $item, $order ) ); ?>">
						<td class="product-name">
							<?php
								echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $item->get_name(), $item, false ) );

								do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false );

								wc_display_item_meta( $item );

								do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false );
							?>
						</td>
						<td class="product-quantity"><?php echo apply_filters( 'woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf( '&times;&nbsp;%s', esc_html( $item->get_quantity() ) ) . '</strong>', $item ); ?></td><?php // @codingStandardsIgnoreLine ?>
						<td class="product-subtotal"><?php echo $order->get_formatted_line_subtotal( $item ); ?></td><?php // @codingStandardsIgnoreLine ?>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<?php if ( $order_totals ) : ?>
				<?php foreach ( $order_totals as $total ) : ?>
					<tr>
						<th scope="row" colspan="2"><?php echo wp_kses_post( $total['label'] ); ?></th><?php // @codingStandardsIgnoreLine ?>
						<td class="product-total"><?php echo wp_kses_post( $total['value'] ); ?></td><?php // @codingStandardsIgnoreLine ?>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tfoot>
	</table>
	<?php if ( 'wps_paypal_subscription' != $payment_method ) : ?>
	<div id="payment">
		<?php if ( $order->needs_payment() ) : ?>
			<ul class="wc_payment_methods payment_methods methods">
				<?php
				if ( ! empty( $available_gateways ) ) {
					foreach ( $available_gateways as $gateway ) {
						wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
					}
				} else {
					echo '<li>';
					wc_print_notice( apply_filters( 'woocommerce_no_available_payment_methods_message', esc_html__( 'Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce-subscriptions-pro' ) ), 'notice' ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
					echo '</li>';
				}
				?>
			</ul>
		<?php endif; ?>
		<div class="form-row">
			<input type="hidden" name="woocommerce_pay" value="1" />

			<?php wc_get_template( 'checkout/terms.php' ); ?>

			<?php do_action( 'woocommerce_pay_order_before_submit' ); ?>

			<?php echo apply_filters( 'woocommerce_pay_order_button_html', '<button type="submit" class="button alt' . esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ) . '" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>' ); // @codingStandardsIgnoreLine ?>

			<?php do_action( 'woocommerce_pay_order_after_submit' ); ?>

			<?php wp_nonce_field( 'woocommerce-pay', 'woocommerce-pay-nonce' ); ?>
		</div>
	</div>
	<?php endif; ?>
</form>
<?php
if ( 'wps_paypal_subscription' === $payment_method && $order->needs_payment() ) {
	wps_paypal_subscription_before_payment( $order );
	wps_paypal_subscription_order_pay( $order );
	// Handling the upgrade/downgrade.
	$subscription_id = wps_wsp_get_meta_data( $order->get_id(), 'wps_subscription_id', true );
	$paypal_id = wps_wsp_get_meta_data( $subscription_id, 'wps_created_paypal_subscription_id', true );
	if ( $paypal_id && class_exists( 'WPS_Paypal_Subscription_Integration_Compatibility' ) ) {
		$sub_comp_obj = new WPS_Paypal_Subscription_Integration_Compatibility();
		$sub_comp_obj->wps_wsp_cancel_wps_paypal_subscription( $subscription_id, 'Cancel' );
	}
}
