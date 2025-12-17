<?php
/**
 * Review order table - Custom Design Based on Figma
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version Custom
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="stirjoy-order-summary-wrapper">
	<!-- Promotional Banner -->
	<div class="stirjoy-promo-banner">
		<p class="stirjoy-promo-text"><?php esc_html_e( 'Only $3.00 until you get a gift! Free shipping from $75.00', 'woocommerce' ); ?></p>
		<div class="stirjoy-promo-buttons">
			<button type="button" class="stirjoy-promo-btn stirjoy-promo-btn-active">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="stirjoy-gift-icon">
					<polyline points="20 12 20 22 4 22 4 12"></polyline>
					<rect x="2" y="7" width="20" height="5"></rect>
					<line x1="12" y1="22" x2="12" y2="7"></line>
					<path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
					<path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
				</svg>
				<?php esc_html_e( 'FREE', 'woocommerce' ); ?>
			</button>
			<button type="button" class="stirjoy-promo-btn">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="stirjoy-gift-icon">
					<polyline points="20 12 20 22 4 22 4 12"></polyline>
					<rect x="2" y="7" width="20" height="5"></rect>
					<line x1="12" y1="22" x2="12" y2="7"></line>
					<path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
					<path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
				</svg>
				<?php esc_html_e( 'GIFT', 'woocommerce' ); ?>
			</button>
		</div>
	</div>

	<!-- What You Get Section -->
	<div class="stirjoy-what-you-get">
		<h3 class="stirjoy-what-you-get-title"><?php esc_html_e( 'What you get', 'woocommerce' ); ?></h3>
		<ul class="stirjoy-benefits-list">
			<li>
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="stirjoy-check-icon">
					<polyline points="20 6 9 17 4 12"></polyline>
				</svg>
				<span><?php esc_html_e( '6 meals for two', 'woocommerce' ); ?></span>
			</li>
			<li>
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="stirjoy-check-icon">
					<polyline points="20 6 9 17 4 12"></polyline>
				</svg>
				<span><?php esc_html_e( 'Delivered right to your door every month', 'woocommerce' ); ?></span>
			</li>
			<li>
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="stirjoy-check-icon">
					<polyline points="20 6 9 17 4 12"></polyline>
				</svg>
				<span><?php esc_html_e( 'Flexible subscription (skip or cancel anytime)', 'woocommerce' ); ?></span>
			</li>
		</ul>
	</div>

	<!-- Box of 6 recipes for 2 Section -->
	<div class="stirjoy-box-summary">
		<h3 class="stirjoy-box-title"><?php esc_html_e( 'Box of 6 recipes for 2', 'woocommerce' ); ?></h3>
		
		<table class="shop_table woocommerce-checkout-review-order-table stirjoy-order-table">
			<tbody>
				<?php
				do_action( 'woocommerce_review_order_before_cart_contents' );

				foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
					$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

					if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
						?>
						<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
							<td class="product-name">
								<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?>
							</td>
							<td class="product-total">
								<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</td>
						</tr>
						<?php
					}
				}

				do_action( 'woocommerce_review_order_after_cart_contents' );
				?>
			</tbody>
			<tfoot>
				<tr class="cart-subtotal">
					<th><?php esc_html_e( 'Price per portion (x12)', 'woocommerce' ); ?></th>
					<td><?php wc_cart_totals_subtotal_html(); ?></td>
				</tr>

				<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
					<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>
					<tr class="shipping">
						<th><?php esc_html_e( 'Shipping', 'woocommerce' ); ?></th>
						<td><?php wc_cart_totals_shipping_html(); ?></td>
					</tr>
					<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>
				<?php endif; ?>

				<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
					<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
						<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
						<td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
					</tr>
				<?php endforeach; ?>

				<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
					<tr class="fee">
						<th><?php echo esc_html( $fee->name ); ?></th>
						<td><?php wc_cart_totals_fee_html( $fee ); ?></td>
					</tr>
				<?php endforeach; ?>

				<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
					<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
						<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
							<tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
								<th><?php echo esc_html( $tax->label ); ?></th>
								<td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr class="tax-total">
							<th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
							<td><?php wc_cart_totals_taxes_total_html(); ?></td>
						</tr>
					<?php endif; ?>
				<?php endif; ?>

				<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

				<tr class="order-total">
					<th><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
					<td><?php wc_cart_totals_order_total_html(); ?></td>
				</tr>

				<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
			</tfoot>
		</table>
	</div>

	<!-- Delivery Schedule Box -->
	<div class="stirjoy-delivery-schedule-box">
		<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="stirjoy-truck-icon">
			<path d="M1 3h15v13H1zM16 8h4l3 3v5h-7V8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			<circle cx="5.5" cy="18.5" r="2.5" stroke="currentColor" stroke-width="2"/>
			<circle cx="18.5" cy="18.5" r="2.5" stroke="currentColor" stroke-width="2"/>
		</svg>
		<div class="stirjoy-delivery-schedule-content">
			<p class="stirjoy-delivery-schedule-text">
				<strong><?php esc_html_e( 'Your box arrives on the 15th of each month', 'woocommerce' ); ?></strong><br>
				<?php esc_html_e( 'Cutoff to customize: 7 days before', 'woocommerce' ); ?>
			</p>
		</div>
	</div>
</div>

