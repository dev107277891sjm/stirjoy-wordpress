<?php
/**
 * Mini-cart
 *
 * Contains the markup for the mini-cart, used by the cart widget.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/mini-cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.0.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_mini_cart' ); ?>

<div class="mini-cart-left-block">

	<?php if ( WC()->cart && ! WC()->cart->is_empty() ) : ?>

		<ul class="woocommerce-mini-cart cart_list product_list_widget <?php echo esc_attr( $args['list_class'] ); ?>">
			<?php
			do_action( 'woocommerce_before_mini_cart_contents' );

			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

				if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
					/**
					 * This filter is documented in woocommerce/templates/cart/cart.php.
					 *
					 * @since 2.1.0
					 */
					$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
					$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'woocommerce_single' ), $cart_item, $cart_item_key );
					$product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
					?>
					<li class="woocommerce-mini-cart-item mini-cart-item-card <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?>">
						<div class="mini-cart-item-image-wrapper">
							<?php
							echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								'woocommerce_cart_item_remove_link',
								sprintf(
									'<a role="button" href="%s" class="remove remove_from_cart_button" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s" data-success_message="%s">×</a>',
									esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
									/* translators: %s is the product name */
									esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ),
									esc_attr( $product_id ),
									esc_attr( $cart_item_key ),
									esc_attr( $_product->get_sku() ),
									/* translators: %s is the product name */
									esc_attr( sprintf( __( '&ldquo;%s&rdquo; has been removed from your cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) )
								),
								$cart_item_key
							);
							?>
							<?php if ( empty( $product_permalink ) ) : ?>
								<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php else : ?>
								<a href="<?php echo esc_url( $product_permalink ); ?>" class="mini-cart-item-image-link">
									<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</a>
							<?php endif; ?>
							<?php if ( ! $thumbnail || strpos( $thumbnail, 'placeholder' ) !== false ) : ?>
								<div class="mini-cart-item-placeholder">
									<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="leaf-icon">
										<path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"></path>
										<path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"></path>
									</svg>
								</div>
							<?php endif; ?>
						</div>
						<div class="mini-cart-item-info">
							<?php if ( empty( $product_permalink ) ) : ?>
								<div class="mini-cart-item-name"><?php echo wp_kses_post( $product_name ); ?></div>
							<?php else : ?>
								<a href="<?php echo esc_url( $product_permalink ); ?>" class="mini-cart-item-name"><?php echo wp_kses_post( $product_name ); ?></a>
							<?php endif; ?>
							<div class="mini-cart-item-price"><?php echo $product_price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						</div>
					</li>
					<?php
				}
			}

			do_action( 'woocommerce_mini_cart_contents' );
			?>
		</ul>

	<?php else : ?>

		<p class="woocommerce-mini-cart__empty-message"><?php esc_html_e( 'No products in the cart.', 'woocommerce' ); ?></p>

	<?php endif; ?>

	<p class="wps_sfw_subscription_box_error_notice" style="display: none;"></p>
	<div class="selected-items"></div>
</div>

<div class="mini-cart-total-section">
	<?php if ( WC()->cart && ! WC()->cart->is_empty() ) : ?>
		<div class="mini-cart-summary-header">
			<a href="#" class="mini-cart-collapse">Collapse</a>
		</div>
		<div class="mini-cart-summary">
			<?php /*<p class="woocommerce-mini-cart__total total">*/ ?>
				<?php
				/**
				 * Hook: woocommerce_widget_shopping_cart_total.
				 *
				 * @hooked woocommerce_widget_shopping_cart_subtotal - 10
				 */
				//do_action( 'woocommerce_widget_shopping_cart_total' );
				?>
			<?php /*</p>*/ ?>

			<p class="mini-cart-subtotal">
				<span class="title">Subtotal</span>
				<span class="price" data-price="<?= WC()->cart->get_subtotal() ?>"><?= WC()->cart->get_cart_subtotal() ?></span>
			</p>
			<p class="mini-cart-shipping">
				<span class="title">Shipping</span>
				<?php WC()->cart->calculate_shipping(); ?>
				<?php if(WC()->cart->get_shipping_total() > 0): ?>
					<span class="price" data-price="<?= WC()->cart->get_shipping_total() ?>"><?= WC()->cart->get_cart_shipping_total() ?></span>
				<?php else: ?>
					<span class="price" data-price="15">$15.00</span>
				<?php endif; ?>
			</p>
			<p class="mini-cart-grandtotal">
				<span class="title">Total</span>
				<?php if(WC()->cart->get_total('edit') > 0): ?>
					<span class="price" data-price="<?= WC()->cart->get_total('edit') ?>"><?= WC()->cart->get_total() ?></span>
				<?php else: ?>
					<span class="price" data-price="15">$15.00</span>
				<?php endif; ?>
			</p>
		</div>

		<div class="cutoff-info">
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
			<div>
				<span>Cutoff: Tonight at 11:59 PM</span>
				<span>Your box will be charged automatically at midnight</span>
			</div>
		</div>
	<?php //endif; ?>

	<?php do_action( 'woocommerce_widget_shopping_cart_before_buttons' ); ?>

	<a href="javascript:;" class="confirm-my-box">
		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-cart w-5 h-5 mr-2" aria-hidden="true"><circle cx="8" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>
		Confirm My Box
	</a>
		<div class="confirm-box-desc">Click to confirm your box selection before the cutoff time</div>
	<?php endif; ?>
</div>

<?php do_action( 'woocommerce_widget_shopping_cart_after_buttons' ); ?>

<?php do_action( 'woocommerce_after_mini_cart' ); ?>
