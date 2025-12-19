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

						<!-- Hidden Required Billing Fields (for validation, auto-populated) -->
						<?php
						// Get current user data
						$current_user = wp_get_current_user();
						$billing_fields = $checkout->get_checkout_fields( 'billing' );
						
						// Email field (required)
						$billing_email = $checkout->get_value( 'billing_email' );
						if ( empty( $billing_email ) && is_user_logged_in() ) {
							$billing_email = $current_user->user_email;
						}
						if ( empty( $billing_email ) ) {
							$billing_email = isset( $_POST['billing_email'] ) ? sanitize_email( $_POST['billing_email'] ) : '';
						}
						
						// First name (required)
						$billing_first_name = $checkout->get_value( 'billing_first_name' );
						if ( empty( $billing_first_name ) && is_user_logged_in() ) {
							$billing_first_name = get_user_meta( $current_user->ID, 'billing_first_name', true );
							if ( empty( $billing_first_name ) ) {
								$billing_first_name = $current_user->first_name;
							}
						}
						if ( empty( $billing_first_name ) ) {
							$billing_first_name = isset( $_POST['billing_first_name'] ) ? sanitize_text_field( $_POST['billing_first_name'] ) : '';
						}
						
						// Last name (required)
						$billing_last_name = $checkout->get_value( 'billing_last_name' );
						if ( empty( $billing_last_name ) && is_user_logged_in() ) {
							$billing_last_name = get_user_meta( $current_user->ID, 'billing_last_name', true );
							if ( empty( $billing_last_name ) ) {
								$billing_last_name = $current_user->last_name;
							}
						}
						if ( empty( $billing_last_name ) ) {
							$billing_last_name = isset( $_POST['billing_last_name'] ) ? sanitize_text_field( $_POST['billing_last_name'] ) : '';
						}
						
						// Country (required) - default to store base country
						$billing_country = $checkout->get_value( 'billing_country' );
						if ( empty( $billing_country ) ) {
							$billing_country = WC()->countries->get_base_country();
						}
						
						// State/Province (may be required depending on country)
						$billing_state = $checkout->get_value( 'billing_state' );
						if ( empty( $billing_state ) && is_user_logged_in() ) {
							$billing_state = get_user_meta( $current_user->ID, 'billing_state', true );
						}
						
						// If state is still empty, check if it's required for the country
						if ( empty( $billing_state ) && ! empty( $billing_country ) && WC()->countries ) {
							$locale = WC()->countries->get_country_locale();
							if ( isset( $locale[ $billing_country ]['state']['required'] ) && $locale[ $billing_country ]['state']['required'] ) {
								// Get states for this country
								$states = WC()->countries->get_states( $billing_country );
								if ( ! empty( $states ) && is_array( $states ) ) {
									// Use first available state as default
									$state_keys = array_keys( $states );
									if ( ! empty( $state_keys ) ) {
										$billing_state = $state_keys[0];
									}
								}
							}
						}
						
						// Output hidden fields - ALWAYS include state field (empty string if not needed)
						// WooCommerce validation will handle if it's actually required
						?>
						<input type="hidden" name="billing_email" id="billing_email" value="<?php echo esc_attr( $billing_email ); ?>" />
						<input type="hidden" name="billing_first_name" id="billing_first_name" value="<?php echo esc_attr( $billing_first_name ); ?>" />
						<input type="hidden" name="billing_last_name" id="billing_last_name" value="<?php echo esc_attr( $billing_last_name ); ?>" />
						<input type="hidden" name="billing_country" id="billing_country" value="<?php echo esc_attr( $billing_country ); ?>" />
						<input type="hidden" name="billing_state" id="billing_state" value="<?php echo esc_attr( $billing_state ); ?>" />

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

							<!-- Notes for Driver Field - Using WooCommerce Standard Field -->
							<div class="stirjoy-notes-section">
								<?php
								// Get order comments field from WooCommerce checkout fields
								$order_fields = $checkout->get_checkout_fields( 'order' );
								if ( isset( $order_fields['order_comments'] ) ) {
									$order_comments_field = $order_fields['order_comments'];
									$order_comments_field['label'] = __( 'Notes for Driver', 'woocommerce' );
									$order_comments_field['placeholder'] = __( 'e.g. Front door, back gate', 'woocommerce' );
									$order_comments_field['type'] = 'text'; // Change from textarea to text for design
									$order_comments_field['class'] = array( 'form-row-wide', 'stirjoy-order-notes' );
									woocommerce_form_field( 'order_comments', $order_comments_field, $checkout->get_value( 'order_comments' ) );
								} else {
									// Fallback if field doesn't exist
									?>
									<p class="form-row form-row-wide stirjoy-order-notes">
										<label for="order_comments" class="stirjoy-field-label"><?php esc_html_e( 'Notes for Driver', 'woocommerce' ); ?></label>
										<input type="text" name="order_comments" class="input-text" id="order_comments" placeholder="<?php esc_attr_e( 'e.g. Front door, back gate', 'woocommerce' ); ?>" value="<?php echo esc_attr( $checkout->get_value( 'order_comments' ) ); ?>">
									</p>
									<?php
								}
								?>
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

							<!-- Credit Card Fields - Regular Input Fields (No Stripe Elements) -->
							<div class="stirjoy-card-fields">
								<!-- These are regular input fields matching Figma design -->
								<!-- Payment method will be created server-side on form submission -->
								<p class="form-row form-row-wide">
									<input type="text" class="input-text stirjoy-card-input" name="number" id="card_number" placeholder="<?php esc_attr_e( 'Card number', 'woocommerce' ); ?>" autocomplete="cc-number" />
								</p>
								
								<div class="stirjoy-card-row">
									<p class="form-row form-row-first">
										<input type="text" class="input-text stirjoy-card-input" name="expiry" id="card_expiry" placeholder="<?php esc_attr_e( 'Expiration date (MM/YY)', 'woocommerce' ); ?>" maxlength="5" autocomplete="cc-exp" />
									</p>
									
									<p class="form-row form-row-last stirjoy-cvc-wrapper">
										<input type="text" class="input-text stirjoy-card-input" name="cvc" id="card_cvc" placeholder="<?php esc_attr_e( 'Security code', 'woocommerce' ); ?>" maxlength="4" autocomplete="cc-csc" />
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="stirjoy-lock-icon">
											<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
											<path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
										</svg>
									</p>
								</div>
								
								<p class="form-row form-row-wide">
									<input type="text" class="input-text stirjoy-card-input" name="card_name" id="card_name" placeholder="<?php esc_attr_e( 'Name on card', 'woocommerce' ); ?>" autocomplete="cc-name" />
								</p>
								
								<?php
								// Use shipping address as billing address checkbox - Standard WooCommerce field
								if ( WC()->cart->needs_shipping() ) :
									?>
									<p class="form-row form-row-wide stirjoy-checkbox-row">
										<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
											<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="use_shipping_as_billing" type="checkbox" name="use_shipping_as_billing" value="1" <?php checked( apply_filters( 'woocommerce_ship_to_different_address_checked', get_option( 'woocommerce_ship_to_different_address' ) === 'yes' ? 0 : 1 ), 1 ); ?> />
											<span><?php esc_html_e( 'Use shipping address as billing address', 'woocommerce' ); ?></span>
										</label>
									</p>
								<?php endif; ?>
							</div>
						</div>

						<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

						<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

						<!-- Payment Gateway Section (Hidden but Required for Processing) -->
						<?php if ( WC()->cart->needs_payment() ) : ?>
							<?php do_action( 'woocommerce_checkout_before_payment' ); ?>
							<div id="payment" class="woocommerce-checkout-payment stirjoy-hidden-payment">
								<?php do_action( 'woocommerce_checkout_payment' ); ?>
							</div>
							<?php do_action( 'woocommerce_checkout_after_payment' ); ?>
						<?php endif; ?>

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

<?php 
// Get payment error details from session if available
$payment_error_details = WC()->session->get( 'stirjoy_payment_error_details' );
if ( $payment_error_details ) {
	// Clear error from session after retrieving
	WC()->session->__unset( 'stirjoy_payment_error_details' );
}
?>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>

<script>
(function($) {
	'use strict';
	
	// ============================================================================
	// PAYMENT ERROR DETAILS FROM SERVER
	// Display any payment errors that occurred during previous submission
	// ============================================================================
	<?php if ( $payment_error_details ) : ?>
	var stirjoyPaymentErrorDetails = <?php echo json_encode( $payment_error_details ); ?>;
	console.error('=== STIRJOY PAYMENT ERROR FROM SERVER ===');
	console.error('Order ID:', stirjoyPaymentErrorDetails.order_id || 'N/A');
	console.error('Error Message:', stirjoyPaymentErrorDetails.error_message || 'N/A');
	console.error('Order Status:', stirjoyPaymentErrorDetails.order_status || 'N/A');
	console.error('Payment Method:', stirjoyPaymentErrorDetails.payment_method || 'N/A');
	console.error('Full Error Details:', stirjoyPaymentErrorDetails);
	console.error('=========================================');
	
	// Display error alert with details
	$(document).ready(function() {
		var errorMsg = 'Payment failed: ' + (stirjoyPaymentErrorDetails.error_message || 'Unknown error');
		if (stirjoyPaymentErrorDetails.order_id) {
			errorMsg += '\n\nOrder ID: ' + stirjoyPaymentErrorDetails.order_id;
		}
		if (stirjoyPaymentErrorDetails.order_status) {
			errorMsg += '\nOrder Status: ' + stirjoyPaymentErrorDetails.order_status;
		}
		if (stirjoyPaymentErrorDetails.payment_method) {
			errorMsg += '\nPayment Method: ' + stirjoyPaymentErrorDetails.payment_method;
		}
		alert(errorMsg);
	});
	<?php else : ?>
	var stirjoyPaymentErrorDetails = null;
	<?php endif; ?>
	
	// ============================================================================
	// CHECKOUT WITHOUT STRIPE ELEMENTS - SIMPLIFIED VERSION
	// Uses regular input fields matching Figma design
	// Creates payment method server-side on form submission
	// ============================================================================
	
	var StirjoyCheckout = {
		state: {
			formSubmitting: false
		},
		
		init: function() {
			this.hidePaymentErrorsOnLoad();
			this.hideStripeElementsSection();
			this.setStripeAsDefaultPaymentMethod();
			this.initFormSubmission();
			this.initExpressCheckoutRemoval();
		},
		
		// Hide payment errors on page load
		hidePaymentErrorsOnLoad: function() {
			function hidePaymentErrors() {
				$('.woocommerce-error, .woocommerce-NoticeGroup-checkout .woocommerce-error, .wc-block-components-notice-banner.is-error').each(function() {
					var $error = $(this);
					var errorText = $error.text() || $error.html() || '';
					if (errorText.toLowerCase().indexOf('payment') !== -1 || 
					    errorText.toLowerCase().indexOf('processing') !== -1 ||
					    errorText.toLowerCase().indexOf('problem processing') !== -1 ||
					    errorText.toLowerCase().indexOf('error processing') !== -1) {
						$error.css({
							'display': 'none',
							'visibility': 'hidden',
							'opacity': '0',
							'height': '0',
							'margin': '0',
							'padding': '0',
							'overflow': 'hidden'
						});
					}
				});
			}
			
			hidePaymentErrors();
			setTimeout(hidePaymentErrors, 10);
			setTimeout(hidePaymentErrors, 100);
			setTimeout(hidePaymentErrors, 500);
			
			// Show errors after form submission
			$(document.body).on('checkout_error', function() {
				$('.woocommerce-error, .woocommerce-NoticeGroup-checkout .woocommerce-error, .wc-block-components-notice-banner.is-error').addClass('stirjoy-show-payment-error').css({
					'display': '',
					'visibility': '',
					'opacity': '',
					'height': '',
					'margin': '',
					'padding': '',
					'overflow': ''
				});
			});
			
			$('form.checkout').on('submit', function() {
				$('.woocommerce-error, .woocommerce-NoticeGroup-checkout .woocommerce-error, .wc-block-components-notice-banner.is-error').addClass('stirjoy-show-payment-error');
			});
		},
		
		// Hide Stripe Elements section completely
		hideStripeElementsSection: function() {
			$('#payment').css({
				'display': 'none !important',
				'visibility': 'hidden !important',
				'opacity': '0 !important',
				'height': '0 !important',
				'overflow': 'hidden !important',
				'position': 'absolute !important',
				'left': '-9999px !important',
				'pointer-events': 'none !important'
			});
			
			// Also hide Stripe Elements containers
			$('#stripe-card-element, #stripe-exp-element, #stripe-cvc-element, .wc-stripe-elements-field, #wc-stripe-cc-form').css({
				'display': 'none !important',
				'visibility': 'hidden !important'
			});
		},
		
		// Set Stripe as default payment method
		setStripeAsDefaultPaymentMethod: function() {
			var self = this;
			
			function setStripeDefault() {
				if (!$('#payment').length || !$('#payment input[name="payment_method"]').length) {
					return false;
				}
				
				// Priority: stripe, stripe_cc, stripe_credit_card, woocommerce_payments
				var preferredIds = ['stripe', 'stripe_cc', 'stripe_credit_card', 'woocommerce_payments'];
				var $selectedMethod = null;
				
				for (var i = 0; i < preferredIds.length; i++) {
					$selectedMethod = $('#payment input[name="payment_method"][value="' + preferredIds[i] + '"]');
					if ($selectedMethod.length) break;
				}
				
				// Fallback: any gateway with 'stripe' or 'card' in value
				if (!$selectedMethod || !$selectedMethod.length) {
					$('#payment input[name="payment_method"]').each(function() {
						var value = $(this).val().toLowerCase();
						if (value.indexOf('stripe') !== -1 || value.indexOf('card') !== -1) {
							$selectedMethod = $(this);
							return false;
						}
					});
				}
				
				// Final fallback: first available
				if (!$selectedMethod || !$selectedMethod.length) {
					$selectedMethod = $('#payment input[name="payment_method"]').first();
				}
				
				if ($selectedMethod && $selectedMethod.length) {
					$selectedMethod.prop('checked', true).trigger('change');
					var paymentMethod = $selectedMethod.val();
					
					$('form.checkout input[type="hidden"][name="payment_method"]').remove();
					$('form.checkout').append('<input type="hidden" name="payment_method" value="' + paymentMethod + '" />');
					
					return true;
				}
				
				return false;
			}
			
			// Try multiple times
			setTimeout(setStripeDefault, 100);
			setTimeout(setStripeDefault, 500);
			setTimeout(setStripeDefault, 1000);
			setTimeout(setStripeDefault, 2000);
		},
		
		// Remove express checkout elements
		initExpressCheckoutRemoval: function() {
			function removeExpress() {
				$('#wc-stripe-express-checkout-element, #wc-stripe-express-checkout-button-separator, #wc-stripe-express-checkout__order-attribution-inputs').remove();
				$('.wcpay-express-checkout-wrapper, #wcpay-express-checkout-wrapper, [class*="wcpay-express"], [id*="wcpay-express"], [class*="express-checkout-wrapper"]').remove();
				$('#wcpay-express-checkout-button-separator, #wcpay-express-checkout__order-attribution-inputs').remove();
			}
			
			removeExpress();
			setTimeout(removeExpress, 100);
			setTimeout(removeExpress, 500);
			setTimeout(removeExpress, 1000);
		},
		
		// Handle form submission
		initFormSubmission: function() {
			var self = this;
			
			$('form.checkout').on('submit', function(e) {
				return self.handleFormSubmit(e);
			});
			
			$('#place_order').on('click', function() {
				self.ensurePaymentMethodInForm();
			});
		},
		
		// Validate all required fields before submission
		validateRequiredFields: function() {
			var missingFields = [];
			
			// Check billing email
			var billingEmail = $('#billing_email').val();
			if (!billingEmail || billingEmail.trim() === '') {
				missingFields.push('- Email address');
			}
			
			// Check billing first name
			var billingFirstName = $('#billing_first_name').val();
			if (!billingFirstName || billingFirstName.trim() === '') {
				missingFields.push('- First name');
			}
			
			// Check billing last name
			var billingLastName = $('#billing_last_name').val();
			if (!billingLastName || billingLastName.trim() === '') {
				missingFields.push('- Last name');
			}
			
			// Check billing address
			var billingAddress1 = $('#billing_address_1').val();
			if (!billingAddress1 || billingAddress1.trim() === '') {
				missingFields.push('- Street address');
			}
			
			// Check billing city
			var billingCity = $('#billing_city').val();
			if (!billingCity || billingCity.trim() === '') {
				missingFields.push('- City');
			}
			
			// Check billing postcode
			var billingPostcode = $('#billing_postcode').val();
			if (!billingPostcode || billingPostcode.trim() === '') {
				missingFields.push('- Postal code');
			}
			
			// Check billing phone (if required)
			var billingPhone = $('#billing_phone').val();
			var phoneField = $('#billing_phone');
			if (phoneField.length && phoneField.closest('.form-row').hasClass('validate-required') && (!billingPhone || billingPhone.trim() === '')) {
				missingFields.push('- Phone number');
			}
			
			// Check billing address_2 (if required)
			var billingAddress2 = $('#billing_address_2').val();
			var address2Field = $('#billing_address_2');
			if (address2Field.length && address2Field.closest('.form-row').hasClass('validate-required') && (!billingAddress2 || billingAddress2.trim() === '')) {
				missingFields.push('- Address line 2');
			}
			
			// Check terms and conditions (if required)
			var termsField = $('input[name="terms-field"]');
			if (termsField.length && termsField.val() === '1') {
				var termsChecked = $('input[name="terms"]:checked').length > 0;
				if (!termsChecked) {
					missingFields.push('- Terms and conditions');
				}
			}
			
			// Check shipping fields if shipping to different address
			var shipToDifferentAddress = $('input[name="ship_to_different_address"]:checked').length > 0;
			if (shipToDifferentAddress) {
				var shippingAddress1 = $('#shipping_address_1').val();
				if (!shippingAddress1 || shippingAddress1.trim() === '') {
					missingFields.push('- Shipping street address');
				}
				
				var shippingCity = $('#shipping_city').val();
				if (!shippingCity || shippingCity.trim() === '') {
					missingFields.push('- Shipping city');
				}
				
				var shippingPostcode = $('#shipping_postcode').val();
				if (!shippingPostcode || shippingPostcode.trim() === '') {
					missingFields.push('- Shipping postal code');
				}
			}
			
			// Check account creation fields if creating account
			var createAccount = $('input[name="createaccount"]:checked').length > 0;
			if (createAccount) {
				var accountUsername = $('#account_username').val();
				if ($('#account_username').length && (!accountUsername || accountUsername.trim() === '')) {
					missingFields.push('- Account username');
				}
				
				var accountPassword = $('#account_password').val();
				if ($('#account_password').length && (!accountPassword || accountPassword.trim() === '')) {
					missingFields.push('- Account password');
				}
			}
			
			return missingFields;
		},
		
		ensurePaymentMethodInForm: function() {
			var paymentMethod = $('input[name="payment_method"]:checked').val();
			
			if (!paymentMethod) {
				var hiddenPaymentMethod = $('#payment input[name="payment_method"]:checked').val();
				if (hiddenPaymentMethod) {
					paymentMethod = hiddenPaymentMethod;
				} else {
					var firstPaymentMethod = $('#payment input[name="payment_method"]').first();
					if (firstPaymentMethod.length) {
						firstPaymentMethod.prop('checked', true).trigger('change');
						paymentMethod = firstPaymentMethod.val();
					}
				}
			}
			
			if (paymentMethod) {
				$('form.checkout input[type="hidden"][name="payment_method"]').remove();
				$('form.checkout').append('<input type="hidden" name="payment_method" value="' + paymentMethod + '" />');
			}
		},
		
		handleFormSubmit: function(e) {
			var self = this;
			
			if (this.state.formSubmitting) {
				e.preventDefault();
				return false;
			}
			
			// Validate required fields before proceeding
			var missingFields = this.validateRequiredFields();
			if (missingFields.length > 0) {
				alert('Please fill in all required fields:\n\n' + missingFields.join('\n'));
				e.preventDefault();
				return false;
			}
			
			this.ensurePaymentMethodInForm();
			
			var paymentMethod = $('input[name="payment_method"]:checked').val();
			if (!paymentMethod) {
				alert('Please select a payment method.');
				e.preventDefault();
				return false;
			}
			
			// For Stripe, create payment method from card fields
			if (paymentMethod && (paymentMethod.indexOf('stripe') !== -1 || paymentMethod.indexOf('card') !== -1)) {
				var cardNumber = $('#card_number').val().replace(/\s/g, '');
				var expiry = $('#card_expiry').val();
				var cvc = $('#card_cvc').val();
				var cardName = $('#card_name').val();
				
				// Validate card number
				if (!cardNumber || cardNumber.length < 13 || cardNumber.length > 19) {
					alert('Please enter a valid card number.');
					e.preventDefault();
					return false;
				}
				
				// Validate expiry format (MM/YY)
				if (!expiry || expiry.length < 4 || !/^\d{2}\/\d{2}$/.test(expiry)) {
					alert('Please enter a valid expiry date in MM/YY format.');
					e.preventDefault();
					return false;
				}
				
				// Validate CVC
				if (!cvc || cvc.length < 3 || cvc.length > 4) {
					alert('Please enter a valid security code.');
					e.preventDefault();
					return false;
				}
				
				console.log('expiry', expiry);
				console.log('cvc', cvc);
				console.log('cardNumber', cardNumber);
				console.log('cardName', cardName);
				console.log('paymentMethod', paymentMethod);

				// Validate expiry date is not in the past
				var expiryParts = expiry.split('/');
				var expMonth = parseInt(expiryParts[0], 10);
				var expYear = parseInt('20' + expiryParts[1], 10);
				var currentDate = new Date();
				var currentYear = currentDate.getFullYear();
				var currentMonth = currentDate.getMonth() + 1;
				
				if (expYear < currentYear || (expYear === currentYear && expMonth < currentMonth)) {
					alert('The card expiry date is in the past. Please enter a valid expiry date.');
					e.preventDefault();
					return false;
				}
				
				// Check if payment method already exists (check both UPE and legacy field names)
				var stripePaymentMethod = $('form.checkout input[name="wc-stripe-payment-method"]').val() || 
				                          $('form.checkout input[name="stripe_token"]').val() ||
				                          $('form.checkout input[name="stripe_payment_method"]').val();

				console.log('stripePaymentMethod', stripePaymentMethod);
										  
				if (!stripePaymentMethod || stripePaymentMethod.trim() === '') {
					// Need to create payment method before submitting
					e.preventDefault();
					this.state.formSubmitting = true;
					
					var expiryParts = expiry.split('/');
					var expMonth = expiryParts[0] ? expiryParts[0].trim() : '';
					var expYear = expiryParts[1] ? '20' + expiryParts[1].trim() : '';
					
					this.createPaymentMethod(cardNumber, parseInt(expMonth, 10), parseInt(expYear, 10), cvc, cardName, function(success, paymentMethodIdOrError) {
						self.state.formSubmitting = false;
						
						// Get button reference for state management
						var $submitButton = $('#place_order');
						var originalButtonText = $submitButton.data('value') || 'PAY AND SUBSCRIBE';
						
						if (success && paymentMethodIdOrError) {
							// Add payment method to form
							var added = self.addPaymentMethodToForm(paymentMethodIdOrError);
							if (!added) {
								$submitButton.prop('disabled', false).text(originalButtonText);
								alert('Failed to prepare payment. Please try again.');
								return;
							}
							
							// Verify payment method is in form before submitting
							var verifyPaymentMethod = $('form.checkout input[name="wc-stripe-payment-method"]').val() || 
							                           $('form.checkout input[name="stripe_token"]').val();
							if (!verifyPaymentMethod || verifyPaymentMethod.trim() === '') {
								$submitButton.prop('disabled', false).text(originalButtonText);
								console.error('Stirjoy: Payment method not found in form after adding');
								alert('Failed to prepare payment. Please try again.');
								return;
							}
							
							// Ensure payment method is still selected
							self.ensurePaymentMethodInForm();
							
							// Validate required fields one more time before final submission
							var finalMissingFields = self.validateRequiredFields();
							if (finalMissingFields.length > 0) {
								$submitButton.prop('disabled', false).text(originalButtonText);
								alert('Please fill in all required fields:\n\n' + finalMissingFields.join('\n'));
								return;
							}
							
							// Log all form data for debugging
							self.logFormData();
							
							// Small delay to ensure fields are added before submission
							setTimeout(function() {
								console.log('Stirjoy: Submitting checkout form with payment method:', verifyPaymentMethod);
								// Re-disable button to prevent double submission
								$submitButton.prop('disabled', true).text('Processing...');
								
								// Submit form - WooCommerce will handle redirect to order-received page on success
								alert('here');
								$('form.checkout')[0].submit();
							}, 100);
						} else {
							// Restore button state on error
							$submitButton.prop('disabled', false).text(originalButtonText);
							
							var errorMessage = typeof paymentMethodIdOrError === 'string' 
								? paymentMethodIdOrError 
								: 'There was an error processing your card. Please check your card details and try again.';
							alert(errorMessage);
						}
					});
					
					return false;
				}else{
					alert('stripePaymentMethod found');
				}
			}
			
			return true;
		},
		
		createPaymentMethod: function(cardNumber, expMonth, expYear, cvc, cardName, callback) {
			var self = this;
			
			var ajaxUrl = (typeof stirjoy_checkout_params !== 'undefined' && stirjoy_checkout_params.ajax_url) 
				? stirjoy_checkout_params.ajax_url 
				: (typeof wc_checkout_params !== 'undefined' && wc_checkout_params.ajax_url)
					? wc_checkout_params.ajax_url
					: '/wp-admin/admin-ajax.php';
			
			var nonce = (typeof stirjoy_checkout_params !== 'undefined' && stirjoy_checkout_params.stripe_nonce)
				? stirjoy_checkout_params.stripe_nonce
				: '';
			
			if (!nonce) {
				console.error('Stirjoy: Missing nonce for payment method creation');
				callback(false, 'Security token is missing. Please refresh the page and try again.');
				return;
			}
			
			// Test cards
			var testCards = {
				'4242424242424242': true,
				'4000000000000002': true,
				'4000000000009995': true,
				'5555555555554444': true,
				'378282246310005': true,
				'6011111111111117': true
			};
			
			var isTestCard = testCards.hasOwnProperty(cardNumber.replace(/\s/g, ''));
			var testToken = isTestCard ? 'pm_test_' + cardNumber.replace(/\s/g, '').substring(0, 10) : null;
			
			// Show loading state
			var $submitButton = $('#place_order');
			var originalButtonText = $submitButton.text();
			$submitButton.prop('disabled', true).text('Processing...');
			
			$.ajax({
				url: ajaxUrl,
				type: 'POST',
				data: {
					action: 'stirjoy_create_stripe_payment_method',
					nonce: nonce,
					card_number: cardNumber,
					exp_month: expMonth,
					exp_year: expYear,
					cvc: cvc,
					card_name: cardName || '',
					test_token: testToken || '',
					is_test_card: isTestCard ? '1' : '0',
					test_mode: '1'
				},
				dataType: 'json',
				timeout: 30000 // 30 second timeout
			}).then(function(response) {
				if (response && response.success && response.data && response.data.payment_method_id) {
					console.log('Stirjoy: Payment method created successfully:', response.data.payment_method_id);
					// Keep button disabled - will be enabled after form submission or error
					alert('Payment method created successfully:', response.data.payment_method_id);
					callback(true, response.data.payment_method_id);
				} else {
					// Restore button state on error
					$submitButton.prop('disabled', false).text(originalButtonText);
					
					var errorMessage = 'Error processing your card. Please check your card details and try again.';
					var errorDetails = null;
					
					if (response && response.data) {
						if (response.data.message) {
							errorMessage = response.data.message;
						}
						
						// Log detailed error information
						if (response.data.error_details) {
							errorDetails = response.data.error_details;
							console.error('=== STIRJOY PAYMENT METHOD CREATION FAILED ===');
							console.error('Error Type:', errorDetails.type || 'unknown');
							console.error('Error Code:', errorDetails.code || 'unknown');
							console.error('Error Message:', errorDetails.message || errorMessage);
							console.error('Decline Code:', errorDetails.decline_code || 'N/A');
							console.error('Parameter:', errorDetails.param || 'N/A');
							console.error('Full Error Details:', errorDetails);
							console.error('Full Response:', response);
							console.error('===========================================');
							
							// Build detailed error message for user
							if (errorDetails.decline_code) {
								errorMessage += '\n\nDecline Code: ' + errorDetails.decline_code;
							}
							if (errorDetails.code && errorDetails.code !== 'unknown') {
								errorMessage += '\nError Code: ' + errorDetails.code;
							}
						} else {
							console.error('Stirjoy: Payment method creation failed:', response);
						}
					}
					
					callback(false, errorMessage);
				}
			}).catch(function(error) {
				// Restore button state on error
				$submitButton.prop('disabled', false).text(originalButtonText);
				
				// Comprehensive AJAX error logging
				console.error('=== STIRJOY AJAX ERROR CREATING PAYMENT METHOD ===');
				console.error('Error Status:', error.status || 'N/A');
				console.error('Error Status Text:', error.statusText || 'N/A');
				console.error('Error Response:', error.responseJSON || error.responseText || 'N/A');
				console.error('Full Error Object:', error);
				console.error('===================================================');
				
				var errorMessage = 'Network error. Please check your connection and try again.';
				if (error.statusText === 'timeout') {
					errorMessage = 'Request timed out. Please try again.';
				} else if (error.status === 0) {
					errorMessage = 'Unable to connect to server. Please check your internet connection.';
				} else if (error.status >= 500) {
					errorMessage = 'Server error (HTTP ' + error.status + '). Please try again in a few moments.';
				} else if (error.status >= 400) {
					errorMessage = 'Request error (HTTP ' + error.status + '). Please check your card details and try again.';
				}
				
				// Try to extract error message from response
				if (error.responseJSON && error.responseJSON.data && error.responseJSON.data.message) {
					errorMessage = error.responseJSON.data.message;
				}
				
				callback(false, errorMessage);
			});
		},
		
		addPaymentMethodToForm: function(paymentMethodId) {
			if (!paymentMethodId || paymentMethodId.trim() === '') {
				console.error('Stirjoy: Invalid payment method ID');
				return false;
			}
			
			// Remove any existing payment method fields
			$('form.checkout input[name="stripe_payment_method"]').remove();
			$('form.checkout input[name="stripe_token"]').remove();
			$('form.checkout input[name="wc-stripe-payment-method"]').remove();
			$('form.checkout input[name="wc-stripe-payment-method-upe"]').remove();
			$('form.checkout input[name="wc_stripe_selected_upe_payment_type"]').remove();
			
			// Get the selected payment gateway
			var paymentMethodGateway = $('input[name="payment_method"]:checked').val();
			
			// Add payment method fields for both UPE and legacy gateways
			// UPE gateway expects: wc-stripe-payment-method
			$('form.checkout').append('<input type="hidden" name="wc-stripe-payment-method" id="wc-stripe-payment-method" value="' + paymentMethodId + '" />');
			
			// Legacy gateway expects: stripe_token
			$('form.checkout').append('<input type="hidden" name="stripe_token" id="stripe_token" value="' + paymentMethodId + '" />');
			
			// Also add UPE payment type field (card)
			$('form.checkout').append('<input type="hidden" name="wc_stripe_selected_upe_payment_type" value="card" />');
			
			// Also add gateway-specific field if needed
			if (paymentMethodGateway) {
				// Remove any existing gateway-specific payment method field
				$('form.checkout input[name="' + paymentMethodGateway + '_payment_method"]').remove();
				
				// Add gateway-specific field (e.g., stripe_payment_method, stripe_cc_payment_method)
				if (paymentMethodGateway.indexOf('stripe') !== -1) {
					$('form.checkout').append('<input type="hidden" name="' + paymentMethodGateway + '_payment_method" value="' + paymentMethodId + '" />');
				}
			}
			
			// Verify fields were added
			var addedUpeField = $('form.checkout input[name="wc-stripe-payment-method"]').length > 0;
			var addedLegacyField = $('form.checkout input[name="stripe_token"]').length > 0;
			
			if (!addedUpeField || !addedLegacyField) {
				console.error('Stirjoy: Failed to add payment method fields to form');
				return false;
			}
			
			// Log for debugging
			console.log('Stirjoy: Added payment method to form:', paymentMethodId, 'Gateway:', paymentMethodGateway);
			return true;
		},
		
		// Log all form data for debugging
		logFormData: function() {
			var formData = {};
			
			// Collect all form fields
			$('form.checkout').find('input, select, textarea').each(function() {
				var $field = $(this);
				var name = $field.attr('name');
				var type = $field.attr('type');
				
				if (!name) return;
				
				if (type === 'checkbox' || type === 'radio') {
					if ($field.is(':checked')) {
						formData[name] = $field.val();
					}
				} else if (type === 'hidden') {
					formData[name] = $field.val();
				} else {
					formData[name] = $field.val();
				}
			});
			
			// Log required fields status
			console.log('Stirjoy: Form submission data:', formData);
			
			// Check for missing critical fields
			var criticalFields = [
				'billing_email',
				'billing_first_name',
				'billing_last_name',
				'billing_address_1',
				'billing_city',
				'billing_postcode',
				'payment_method'
			];
			
			var missingCritical = [];
			criticalFields.forEach(function(fieldName) {
				if (!formData[fieldName] || formData[fieldName].toString().trim() === '') {
					missingCritical.push(fieldName);
				}
			});
			
			if (missingCritical.length > 0) {
				console.warn('Stirjoy: Missing critical fields:', missingCritical);
			} else {
				console.log('Stirjoy: All critical fields present');
			}
			
			// Check payment method fields
			var paymentFields = [
				'wc-stripe-payment-method',
				'stripe_token',
				'stripe_payment_method'
			];
			
			var hasPaymentMethod = false;
			paymentFields.forEach(function(fieldName) {
				if (formData[fieldName] && formData[fieldName].toString().trim() !== '') {
					hasPaymentMethod = true;
					console.log('Stirjoy: Payment method found in field:', fieldName, formData[fieldName]);
				}
			});
			
			if (!hasPaymentMethod) {
				console.warn('Stirjoy: No payment method ID found in form!');
			}
		}
	};
	
	// Initialize when DOM is ready
	$(document).ready(function() {
		StirjoyCheckout.init();
	});
	
})(jQuery);
</script>
