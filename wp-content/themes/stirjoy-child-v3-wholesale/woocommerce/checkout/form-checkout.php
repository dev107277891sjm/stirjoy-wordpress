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

							<!-- Credit Card Fields - Visual Only (Payment Gateway Handles Actual Processing) -->
							<div class="stirjoy-card-fields">
								<!-- Note: These are visual fields only. Actual card processing is handled by payment gateway (Stripe) -->
								<!-- The payment gateway section below contains the real payment fields -->
								<p class="form-row form-row-wide">
									<input type="text" class="input-text stirjoy-card-input" name="stirjoy_card_number_display" id="stirjoy_card_number_display" placeholder="<?php esc_attr_e( 'Card number', 'woocommerce' ); ?>" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" />
								</p>
								
								<div class="stirjoy-card-row">
									<p class="form-row form-row-first">
										<input type="text" class="input-text stirjoy-card-input" name="stirjoy_card_expiry_display" id="stirjoy_card_expiry_display" placeholder="<?php esc_attr_e( 'Expiration date (MM/YY)', 'woocommerce' ); ?>" maxlength="5" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" />
									</p>
									
									<p class="form-row form-row-last stirjoy-cvc-wrapper">
										<input type="text" class="input-text stirjoy-card-input" name="stirjoy_card_cvc_display" id="stirjoy_card_cvc_display" placeholder="<?php esc_attr_e( 'Security code', 'woocommerce' ); ?>" maxlength="4" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" />
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="stirjoy-lock-icon">
											<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
											<path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
										</svg>
									</p>
								</div>
								
								<p class="form-row form-row-wide">
									<input type="text" class="input-text stirjoy-card-input" name="stirjoy_card_name_display" id="stirjoy_card_name_display" placeholder="<?php esc_attr_e( 'Name on card', 'woocommerce' ); ?>" autocomplete="cc-name" readonly onfocus="this.removeAttribute('readonly');" />
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
							<div id="payment" class="woocommerce-checkout-payment" style="position: absolute; left: -9999px; width: 400px; height: auto; overflow: visible; opacity: 0; visibility: visible; pointer-events: auto;">
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
	
	// Sync display card fields with Stripe Elements (if Stripe is active)
	function syncCardFieldsWithStripe() {
		// Check if Stripe Elements are available
		if (typeof stripe_card !== 'undefined' && stripe_card) {
			// Listen to Stripe card element changes and update display fields
			stripe_card.on('change', function(event) {
				if (event.complete) {
					// Card number is handled by Stripe, we can show masked version
					var cardNumber = event.brand ? '**** **** **** ' + (event.last4 || '') : '';
					$('#stirjoy_card_number_display').val(cardNumber);
				}
			});
		}
		
		// Ensure display fields are interactive (they're just for visual feedback)
		$('.stirjoy-card-input').each(function() {
			var $input = $(this);
			// These are display-only fields, but allow user interaction for visual feedback
			$input.css({
				'pointer-events': 'auto',
				'cursor': 'text',
				'z-index': '10',
				'position': 'relative'
			});
		});
		
		// Ensure card container is interactive
		$('.stirjoy-card-container, .stirjoy-card-fields').css({
			'pointer-events': 'auto',
			'z-index': '1',
			'position': 'relative'
		});
	}
	
	// Initialize card field sync
	setTimeout(syncCardFieldsWithStripe, 1000);
	setTimeout(syncCardFieldsWithStripe, 2000);
	
	// Ensure payment method is selected before form submission
	$('form.checkout').on('checkout_place_order', function() {
		// Check if payment method is selected
		var paymentMethod = $('input[name="payment_method"]:checked').val();
		
		if (!paymentMethod) {
			// Auto-select first available payment method from hidden payment section
			var firstPaymentMethod = $('#payment input[name="payment_method"]').first();
			if (firstPaymentMethod.length) {
				firstPaymentMethod.prop('checked', true).trigger('change');
				paymentMethod = firstPaymentMethod.val();
			}
		}
		
		// Ensure payment method input exists in form
		if (paymentMethod) {
			// Remove any existing hidden payment method input
			$('form.checkout input[type="hidden"][name="payment_method"]').remove();
			// Add payment method to form
			$('form.checkout').append('<input type="hidden" name="payment_method" value="' + paymentMethod + '" />');
		}
	});
	
	// Also ensure payment method is set on button click
	$('#place_order').on('click', function(e) {
		var paymentMethod = $('input[name="payment_method"]:checked').val();
		
		if (!paymentMethod) {
			// Try to get from hidden payment section
			var hiddenPaymentMethod = $('#payment input[name="payment_method"]:checked').val();
			if (hiddenPaymentMethod) {
				paymentMethod = hiddenPaymentMethod;
			} else {
				// Auto-select first available
				var firstPaymentMethod = $('#payment input[name="payment_method"]').first();
				if (firstPaymentMethod.length) {
					firstPaymentMethod.prop('checked', true).trigger('change');
					paymentMethod = firstPaymentMethod.val();
				}
			}
		}
		
		// Ensure payment method is in form
		if (paymentMethod) {
			// Remove any existing hidden payment method input
			$('form.checkout input[type="hidden"][name="payment_method"]').remove();
			// Add payment method to form
			$('form.checkout').append('<input type="hidden" name="payment_method" value="' + paymentMethod + '" />');
		}
		
		// Ensure payment gateway fields are initialized
		// Some gateways need their fields to be "visible" (even if off-screen) to initialize
		if ($('#payment').length) {
			// Temporarily make payment section visible for initialization
			var $paymentSection = $('#payment');
			$paymentSection.css({
				'position': 'absolute',
				'left': '-9999px',
				'visibility': 'visible',
				'height': 'auto',
				'overflow': 'visible'
			});
			
			// Trigger payment gateway initialization events
			$('#payment input[name="payment_method"]').first().trigger('change');
			
			// Restore after a short delay
			setTimeout(function() {
				$paymentSection.css({
					'position': 'absolute',
					'left': '-9999px',
					'visibility': 'hidden',
					'height': '1px',
					'overflow': 'hidden'
				});
			}, 100);
		}
	});
	
	// Function to check payment gateway initialization status
	function checkPaymentGatewayStatus(verbose) {
		verbose = verbose || false; // Only log if explicitly requested
		
		var status = {
			paymentSectionExists: $('#payment').length > 0,
			paymentMethodsExist: $('#payment input[name="payment_method"]').length > 0,
			paymentMethodSelected: $('#payment input[name="payment_method"]:checked').length > 0,
			paymentMethodInForm: $('form.checkout input[name="payment_method"]').length > 0,
			stripeLoaded: typeof Stripe !== 'undefined',
			stripeElementsLoaded: typeof stripe !== 'undefined' || typeof wc_stripe_form !== 'undefined',
			stripeCardElementExists: $('#stripe-card-element').length > 0 || $('#stripe-payment-data').length > 0
		};
		
		// Log status only if verbose or if there are errors
		if (verbose || !status.paymentSectionExists || (!status.paymentMethodsExist && $('#payment').length > 0)) {
			console.log('Stirjoy Payment Gateway Status:', status);
		}
		
		// Check for critical issues (always log errors)
		if (!status.paymentSectionExists) {
			console.error('Stirjoy Error: Payment section (#payment) not found');
		}
		if (!status.paymentMethodsExist && $('#payment').length > 0) {
			// Only log if payment section exists but methods don't (they might be loading)
			if (verbose) {
				console.warn('Stirjoy: Payment methods not loaded yet (may still be loading)');
			}
		}
		if (!status.paymentMethodSelected && status.paymentMethodsExist) {
			if (verbose) {
				console.warn('Stirjoy Warning: No payment method selected');
			}
		}
		if (!status.paymentMethodInForm && status.paymentMethodSelected) {
			if (verbose) {
				console.warn('Stirjoy Warning: Payment method not in form');
			}
		}
		
		return status;
	}
	
	// Function to set Stripe/credit card as default payment method
	function setDefaultPaymentMethod() {
		if (!$('#payment').length || !$('#payment input[name="payment_method"]').length) {
			console.warn('Stirjoy: Payment section not ready yet');
			return false;
		}
		
		// Priority: stripe, stripe_cc, stripe_credit_card, woocommerce_payments, then any with 'stripe' or 'card'
		var preferredIds = ['stripe', 'stripe_cc', 'stripe_credit_card', 'woocommerce_payments'];
		var $selectedMethod = null;
		
		// Try preferred gateways first
		for (var i = 0; i < preferredIds.length; i++) {
			$selectedMethod = $('#payment input[name="payment_method"][value="' + preferredIds[i] + '"]');
			if ($selectedMethod.length) {
				break;
			}
		}
		
		// If not found, look for any gateway with 'stripe' or 'card' in the value
		if (!$selectedMethod || !$selectedMethod.length) {
			$('#payment input[name="payment_method"]').each(function() {
				var value = $(this).val().toLowerCase();
				if (value.indexOf('stripe') !== -1 || value.indexOf('card') !== -1) {
					$selectedMethod = $(this);
					return false; // break
				}
			});
		}
		
		// Fallback to first available
		if (!$selectedMethod || !$selectedMethod.length) {
			$selectedMethod = $('#payment input[name="payment_method"]').first();
		}
		
		if ($selectedMethod && $selectedMethod.length) {
			$selectedMethod.prop('checked', true).trigger('change');
			var paymentMethod = $selectedMethod.val();
			
			// Add to form if not present
			$('form.checkout input[type="hidden"][name="payment_method"]').remove();
			$('form.checkout').append('<input type="hidden" name="payment_method" value="' + paymentMethod + '" />');
			
			console.log('Stirjoy: Set default payment method to ' + paymentMethod);
			return true;
		}
		
		return false;
	}
	
	// Function to wait for payment methods to load and then initialize
	function waitForPaymentMethodsAndInitialize(retries) {
		retries = retries || 15; // Max 15 retries (7.5 seconds)
		
		var status = checkPaymentGatewayStatus(false); // Don't log on every retry
		
		if (!status.paymentMethodsExist && retries > 0) {
			// Payment methods not loaded yet, wait and retry
			setTimeout(function() {
				waitForPaymentMethodsAndInitialize(retries - 1);
			}, 500);
			return;
		}
		
		if (status.paymentMethodsExist) {
			// Payment methods are loaded, set default
			console.log('Stirjoy: Payment methods loaded, setting default...');
			setDefaultPaymentMethod();
			
			// Check Stripe loading after a delay
			setTimeout(function() {
				var finalStatus = checkPaymentGatewayStatus(true); // Log final status
				if (finalStatus.paymentMethodSelected) {
					var selectedMethod = $('#payment input[name="payment_method"]:checked').val();
					console.log('Stirjoy: Selected payment method:', selectedMethod);
					
					if (selectedMethod && (selectedMethod.indexOf('stripe') !== -1 || selectedMethod.indexOf('card') !== -1)) {
						// Wait for Stripe to load
						setTimeout(function() {
							var stripeStatus = checkPaymentGatewayStatus(true);
							if (stripeStatus.stripeLoaded || stripeStatus.stripeElementsLoaded) {
								console.log('Stirjoy: Stripe loaded successfully');
							} else {
								console.warn('Stirjoy: Stripe not loaded after 2 seconds, retrying...');
								// Retry Stripe initialization
								$('#payment input[name="payment_method"]:checked').trigger('change');
							}
						}, 2000);
					}
				}
			}, 500);
		} else {
			console.error('Stirjoy: Payment methods failed to load after multiple retries');
		}
	}
	
	// Initialize payment gateway on page load
	setTimeout(function() {
		waitForPaymentMethodsAndInitialize();
	}, 500);
	
	// Also initialize after checkout updates
	$(document.body).on('updated_checkout', function() {
		setTimeout(function() {
			var status = checkPaymentGatewayStatus(false);
			if (status.paymentMethodsExist) {
				if (!status.paymentMethodSelected) {
					setDefaultPaymentMethod();
				}
				if (status.paymentMethodSelected) {
					$('#payment input[name="payment_method"]:checked').trigger('change');
				}
			}
		}, 100);
	});
	
	// Ensure form submission works correctly
	$('form.checkout').on('submit', function(e) {
		// Check status before form submission
		var status = checkPaymentGatewayStatus();
		
		if (!status.paymentMethodSelected) {
			console.error('Stirjoy Error: No payment method selected before submission');
			if (!setDefaultPaymentMethod()) {
				alert('Please select a payment method.');
				e.preventDefault();
				return false;
			}
		}
		
		if (!status.paymentMethodInForm) {
			var paymentMethod = $('#payment input[name="payment_method"]:checked').val();
			if (paymentMethod) {
				$('form.checkout input[type="hidden"][name="payment_method"]').remove();
				$('form.checkout').append('<input type="hidden" name="payment_method" value="' + paymentMethod + '" />');
			}
		}
		// Ensure payment method is set before submission
		var paymentMethod = $('input[name="payment_method"]:checked').val();
		
		if (!paymentMethod) {
			// Try to get from hidden payment section
			var hiddenPaymentMethod = $('#payment input[name="payment_method"]:checked').val();
			if (hiddenPaymentMethod) {
				paymentMethod = hiddenPaymentMethod;
				// Add to form
				$('form.checkout input[type="hidden"][name="payment_method"]').remove();
				$('form.checkout').append('<input type="hidden" name="payment_method" value="' + paymentMethod + '" />');
			} else {
				// Auto-select first available
				var firstPaymentMethod = $('#payment input[name="payment_method"]').first();
				if (firstPaymentMethod.length) {
					firstPaymentMethod.prop('checked', true).trigger('change');
					paymentMethod = firstPaymentMethod.val();
					$('form.checkout input[type="hidden"][name="payment_method"]').remove();
					$('form.checkout').append('<input type="hidden" name="payment_method" value="' + paymentMethod + '" />');
				}
			}
		} else {
			// Ensure payment method input exists in form
			if (!$('form.checkout input[type="hidden"][name="payment_method"]').length) {
				$('form.checkout').append('<input type="hidden" name="payment_method" value="' + paymentMethod + '" />');
			}
		}
		
		// For Stripe, ensure Elements are initialized
		if (paymentMethod && paymentMethod.indexOf('stripe') !== -1) {
			// Stripe will handle tokenization via its own JavaScript
			// We just need to ensure payment method is set
			// Don't prevent default - let Stripe handle it
			return true;
		}
		
		// For other payment methods, ensure payment method is set
		return true;
	});
	
	// Handle WooCommerce checkout update events (duplicate handler removed - using the one above)
	
	// Handle "use shipping as billing" checkbox
	$('#use_shipping_as_billing').on('change', function() {
		if ($(this).is(':checked')) {
			// Copy shipping fields to billing fields when checked
			// This is handled by WooCommerce, but we ensure it works
			$(document.body).trigger('update_checkout');
		}
	});
});
</script>

