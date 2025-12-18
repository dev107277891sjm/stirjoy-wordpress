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
		<h1 class="stirjoy-checkout-title"><?php esc_html_e( 'Checkout and subscribe', 'woocommerce' ); ?></h1>

		<div class="stirjoy-checkout-content">
			<!-- Left Column - Forms -->
			<div class="stirjoy-checkout-forms-column">
				<form name="checkout" method="post" class="checkout woocommerce-checkout stirjoy-checkout-form" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

					<?php if ( $checkout->get_checkout_fields() ) : ?>

						<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

						<!-- Delivery Section -->
						<div class="stirjoy-checkout-section stirjoy-delivery-section">
							<h2 class="stirjoy-section-title"><?php esc_html_e( 'Delivery', 'woocommerce' ); ?></h2>
							
							<?php 
							// Get billing fields
							$billing_fields = $checkout->get_checkout_fields( 'billing' );
							?>
							
							<!-- Address Section -->
							<div class="stirjoy-address-section">
								<label class="stirjoy-section-label"><?php esc_html_e( 'Address', 'woocommerce' ); ?></label>
								
								<?php 
								// Street address field
								if ( isset( $billing_fields['billing_address_1'] ) ) {
									$address_field = $billing_fields['billing_address_1'];
									$address_field['label'] = ''; // Remove default label
									$address_field['placeholder'] = __( 'Street address', 'woocommerce' );
									$address_field['class'] = array( 'form-row-wide' );
									woocommerce_form_field( 'billing_address_1', $address_field, $checkout->get_value( 'billing_address_1' ) );
								}
								?>
								
								<div class="stirjoy-city-postcode-row">
									<?php 
									// City field
									if ( isset( $billing_fields['billing_city'] ) ) {
										$city_field = $billing_fields['billing_city'];
										$city_field['label'] = ''; // Remove default label
										$city_field['placeholder'] = __( 'City', 'woocommerce' );
										$city_field['class'] = array( 'form-row-first' );
										woocommerce_form_field( 'billing_city', $city_field, $checkout->get_value( 'billing_city' ) );
									}
									
									// Postal code field
									if ( isset( $billing_fields['billing_postcode'] ) ) {
										$postcode_field = $billing_fields['billing_postcode'];
										$postcode_field['label'] = ''; // Remove default label
										$postcode_field['placeholder'] = __( 'Postal Code', 'woocommerce' );
										$postcode_field['class'] = array( 'form-row-last' );
										woocommerce_form_field( 'billing_postcode', $postcode_field, $checkout->get_value( 'billing_postcode' ) );
									}
									?>
								</div>
							</div>
							
							<!-- Phone Number Section -->
							<div class="stirjoy-phone-section">
								<label class="stirjoy-section-label"><?php esc_html_e( 'Phone Number', 'woocommerce' ); ?></label>
								<?php 
								// Display phone number field
								if ( isset( $billing_fields['billing_phone'] ) ) {
									$phone_field = $billing_fields['billing_phone'];
									$phone_field['placeholder'] = __( 'Your phone', 'woocommerce' );
									$phone_field['label'] = __( 'Phone Number', 'woocommerce' );
									$phone_field['class'] = array( 'form-row-wide' );
									woocommerce_form_field( 'billing_phone', $phone_field, $checkout->get_value( 'billing_phone' ) );
								}
								?>
							</div>

							<!-- Notes for Driver Field -->
							<div class="stirjoy-notes-section">
								<p class="form-row form-row-wide stirjoy-order-notes">
									<label for="order_comments" class="stirjoy-field-label"><?php esc_html_e( 'Notes for Driver (Optional)', 'woocommerce' ); ?></label>
									<input type="text" name="order_comments" class="input-text" id="order_comments" placeholder="<?php esc_attr_e( 'e.g. Front door, back gate', 'woocommerce' ); ?>" rows="2" cols="5">
								</p>
							</div>
						</div>

						<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

					<?php endif; ?>

					<!-- Payment Section -->
					<div class="stirjoy-checkout-section stirjoy-payment-section">
						<h2 class="stirjoy-section-title"><?php esc_html_e( 'Payment', 'woocommerce' ); ?></h2>
						
						<p class="stirjoy-security-text"><?php esc_html_e( 'All transactions are secure and encrypted.', 'woocommerce' ); ?></p>

						<!-- Credit Card Container (Light Grey Box) -->
						<div class="stirjoy-card-container">
							<!-- Credit Card Label with Icons -->
							<div class="stirjoy-payment-methods-label">
								<label class="stirjoy-field-label"><?php esc_html_e( 'Credit card', 'woocommerce' ); ?></label>
								<div class="stirjoy-payment-icons">
								<img src="<?php echo esc_url(stirjoy_get_image_url('Frame 215.png')); ?>" alt="Mastercard">
									<span class="stirjoy-payment-icon">+ 4</span>
								</div>
							</div>

							<!-- Credit Card Fields -->
							<div class="stirjoy-card-fields">
								<p class="form-row form-row-wide">
									<input type="text" class="input-text stirjoy-card-input" name="card_number" id="card_number" placeholder="<?php esc_attr_e( 'Card number', 'woocommerce' ); ?>" />
								</p>
								
								<div class="stirjoy-card-row">
									<p class="form-row form-row-first">
										<input type="text" class="input-text stirjoy-card-input" name="card_expiry" id="card_expiry" placeholder="<?php esc_attr_e( 'Expiration date (MM/YY)', 'woocommerce' ); ?>" maxlength="5" />
									</p>
									
									<p class="form-row form-row-last stirjoy-cvc-wrapper">
										<input type="text" class="input-text stirjoy-card-input" name="card_cvc" id="card_cvc" placeholder="<?php esc_attr_e( 'Security code', 'woocommerce' ); ?>" maxlength="4" />
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="stirjoy-lock-icon">
											<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
											<path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
										</svg>
									</p>
								</div>
								
								<p class="form-row form-row-wide">
									<input type="text" class="input-text stirjoy-card-input" name="card_name" id="card_name" placeholder="<?php esc_attr_e( 'Name on card', 'woocommerce' ); ?>" />
								</p>
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
							</div>
						</div>

						<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

						<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>


						<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

						<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>

						<!-- Submit Button -->
						<button type="submit" class="button alt wp-element-button stirjoy-checkout-button" name="woocommerce_checkout_place_order" id="place_order" value="<?php esc_attr_e( 'pay and subscribe', 'woocommerce' ); ?>" data-value="<?php esc_attr_e( 'pay and subscribe', 'woocommerce' ); ?>">
							<?php esc_html_e( 'PAY AND SUBSCRIBE', 'woocommerce' ); ?>
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

<script>
jQuery(document).ready(function($) {
	// Function to remove Stripe Express Checkout elements
	function removeStripeExpressElements() {
		$('#wc-stripe-express-checkout-element, #wc-stripe-express-checkout-button-separator, #wc-stripe-express-checkout__order-attribution-inputs').remove();
	}
	
	// Remove immediately on page load
	removeStripeExpressElements();
	
	// Watch for dynamically added elements using MutationObserver
	if (window.MutationObserver) {
		var observer = new MutationObserver(function(mutations) {
			removeStripeExpressElements();
		});
		
		// Observe the document body for changes
		if (document.body) {
			observer.observe(document.body, {
				childList: true,
				subtree: true
			});
		}
	}
	
	// Also check after short delays to catch elements added after page load
	setTimeout(removeStripeExpressElements, 100);
	setTimeout(removeStripeExpressElements, 500);
	setTimeout(removeStripeExpressElements, 1000);
});
</script>

