<?php
/**
 * Checkout Form - Custom Design Based on Figma
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version Custom
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

?>

<div class="stirjoy-checkout-page">
	<div class="stirjoy-checkout-container">
		<!-- Page Title -->
		<h1 class="stirjoy-checkout-title"><?php esc_html_e( 'Checkout', 'woocommerce' ); ?></h1>

		<div class="stirjoy-checkout-content">
			<!-- Left Column - Forms -->
			<div class="stirjoy-checkout-forms-column">
				<form name="checkout" method="post" class="checkout woocommerce-checkout stirjoy-checkout-form" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

					<?php if ( $checkout->get_checkout_fields() ) : ?>

						<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

						<!-- Delivery Section -->
						<div class="stirjoy-checkout-section stirjoy-delivery-section">
							<h2 class="stirjoy-section-title"><?php esc_html_e( 'Delivery', 'woocommerce' ); ?></h2>
							
							<?php do_action( 'woocommerce_checkout_billing' ); ?>
							
							<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
								<?php do_action( 'woocommerce_checkout_shipping' ); ?>
							<?php endif; ?>

							<!-- Notes for Driver Field -->
							<p class="form-row form-row-wide stirjoy-order-notes">
								<label for="order_comments" class="stirjoy-field-label"><?php esc_html_e( 'Notes for Driver (Optional)', 'woocommerce' ); ?></label>
								<textarea name="order_comments" class="input-text" id="order_comments" placeholder="<?php esc_attr_e( 'e.g. Front door, back gate', 'woocommerce' ); ?>" rows="2" cols="5"></textarea>
							</p>
						</div>

						<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

					<?php endif; ?>

					<!-- Payment Section -->
					<div class="stirjoy-checkout-section stirjoy-payment-section">
						<h2 class="stirjoy-section-title"><?php esc_html_e( 'Payment', 'woocommerce' ); ?></h2>
						
						<p class="stirjoy-security-text"><?php esc_html_e( 'All transactions are secure and encrypted.', 'woocommerce' ); ?></p>

						<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

						<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

						<div id="payment" class="woocommerce-checkout-payment">
							<?php
							if ( WC()->cart && WC()->cart->needs_payment() ) {
								$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
								if ( ! empty( $available_gateways ) ) {
									?>
									<div class="stirjoy-payment-methods-label">
										<label><?php esc_html_e( 'Credit card', 'woocommerce' ); ?></label>
										<div class="stirjoy-payment-icons">
											<span class="stirjoy-payment-icon">Mastercard</span>
											<span class="stirjoy-payment-icon">Visa</span>
											<span class="stirjoy-payment-icon">Card</span>
											<span class="stirjoy-payment-icon">+ 4</span>
										</div>
									</div>
									<ul class="wc_payment_methods payment_methods methods">
										<?php
										foreach ( $available_gateways as $gateway ) {
											wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
										}
										?>
									</ul>
									<?php
								}
							}
							?>
						</div>

						<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

						<!-- Checkboxes -->
						<div class="stirjoy-checkout-checkboxes">
							<?php
							// Use shipping address as billing address checkbox
							if ( WC()->cart->needs_shipping() ) :
								?>
								<p class="form-row form-row-wide stirjoy-checkbox-row">
									<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
										<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="use_shipping_as_billing" type="checkbox" name="use_shipping_as_billing" value="1" />
										<span><?php esc_html_e( 'Use shipping address as billing address', 'woocommerce' ); ?></span>
									</label>
								</p>
							<?php endif; ?>

							<!-- Newsletter subscription checkbox -->
							<p class="form-row form-row-wide stirjoy-checkbox-row">
								<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
									<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="newsletter_subscribe" type="checkbox" name="newsletter_subscribe" value="1" />
									<span><?php esc_html_e( 'Subscribe to our newsletter', 'woocommerce' ); ?></span>
								</label>
							</p>
						</div>

						<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>

						<!-- Submit Button -->
						<button type="submit" class="button alt wp-element-button stirjoy-checkout-button" name="woocommerce_checkout_place_order" id="place_order" value="<?php esc_attr_e( 'Place order', 'woocommerce' ); ?>" data-value="<?php esc_attr_e( 'Place order', 'woocommerce' ); ?>">
							<?php esc_html_e( 'CONTINUE TO PAYMENT', 'woocommerce' ); ?>
						</button>

						<p class="stirjoy-payment-disclaimer">
							<?php esc_html_e( 'Your card is only charged once your box is ready to ship each month. You have until midnight before shipping day to change or cancel.', 'woocommerce' ); ?>
						</p>
					</div>

				</form>
			</div>

			<!-- Right Column - Order Summary -->
			<div class="stirjoy-checkout-sidebar">
				<?php
				// Include custom order summary
				wc_get_template( 'checkout/review-order.php', array( 'checkout' => $checkout ) );
				?>
			</div>
		</div>
	</div>
</div>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>

