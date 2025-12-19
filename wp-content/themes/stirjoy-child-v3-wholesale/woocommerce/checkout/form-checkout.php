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
	// CRITICAL: Check if Stripe scripts are loading properly
	function checkStripeScriptsLoading() {
		var stripeScripts = [
			'script[src*="js.stripe.com"]',
			'script[src*="stripe.com/v3"]',
			'script[src*="stripecdn.com"]'
		];
		
		var scriptsFound = false;
		stripeScripts.forEach(function(selector) {
			if ($(selector).length > 0) {
				scriptsFound = true;
				console.log('Stirjoy: Found Stripe script:', selector);
			}
		});
		
		if (!scriptsFound) {
			console.warn('Stirjoy Warning: No Stripe scripts found on page. Stripe may be blocked.');
		}
		
		// Check if Stripe object is available
		if (typeof Stripe === 'undefined' && typeof stripe === 'undefined' && typeof wc_stripe_form === 'undefined') {
			console.warn('Stirjoy Warning: Stripe object not found. Stripe scripts may not be loaded.');
			
			// Try to detect if scripts are blocked
			setTimeout(function() {
				if (typeof Stripe === 'undefined' && typeof stripe === 'undefined') {
					console.error('Stirjoy Error: Stripe still not loaded after delay. Possible causes:');
					console.error('1. CSP headers blocking Stripe scripts');
					console.error('2. Network/firewall blocking Stripe domains');
					console.error('3. Stripe plugin not active or misconfigured');
					console.error('4. JavaScript errors preventing Stripe initialization');
				}
			}, 3000);
		} else {
			console.log('Stirjoy: Stripe object found:', typeof Stripe !== 'undefined' ? 'Stripe' : (typeof stripe !== 'undefined' ? 'stripe' : 'wc_stripe_form'));
		}
	}
	
	// Check immediately and after delays
	checkStripeScriptsLoading();
	setTimeout(checkStripeScriptsLoading, 1000);
	setTimeout(checkStripeScriptsLoading, 3000);
	setTimeout(checkStripeScriptsLoading, 5000);
	// CRITICAL: Fix sandboxed iframe issues for Stripe payment processing
	// This fixes sandboxed frame errors that prevent payment processing
	function fixStripeIframeSandbox() {
		// Fix ALL iframes in the payment section and Stripe/Google Pay related iframes
		$('iframe, #payment iframe, iframe[src*="stripe"], iframe[src*="js.stripe"], iframe[src*="hooks.stripe"], iframe[src*="pay.google"], iframe[src*="google.com"], iframe[src*="stripecdn.com"], iframe[src*="b.stripecdn.com"], iframe[src*="generate_gpay"]').each(function() {
			var $iframe = $(this);
			var sandbox = $iframe.attr('sandbox');
			var src = $iframe.attr('src') || '';
			
			// CRITICAL: For payment-related iframes (especially Google Pay), be very aggressive
			// Google Pay iframes MUST have allow-scripts or be removed entirely
			if (src.indexOf('pay.google') !== -1 || src.indexOf('generate_gpay') !== -1 || src.indexOf('stripe') !== -1 || src.indexOf('google.com') !== -1 || src.indexOf('stripecdn.com') !== -1) {
				// For Google Pay and payment iframes, CRITICAL: Remove sandbox if it's blocking
				if (sandbox) {
					// Check if sandbox has all required permissions
					var hasAllPermissions = sandbox.indexOf('allow-scripts') !== -1 && 
					                        sandbox.indexOf('allow-same-origin') !== -1 &&
					                        sandbox.indexOf('allow-forms') !== -1;
					
					if (!hasAllPermissions) {
						// Try to fix it by adding all required permissions
						var newSandbox = sandbox;
						if (sandbox.indexOf('allow-scripts') === -1) {
							newSandbox += ' allow-scripts';
						}
						if (sandbox.indexOf('allow-same-origin') === -1) {
							newSandbox += ' allow-same-origin';
						}
						if (sandbox.indexOf('allow-forms') === -1) {
							newSandbox += ' allow-forms';
						}
						if (sandbox.indexOf('allow-popups') === -1) {
							newSandbox += ' allow-popups';
						}
						if (sandbox.indexOf('allow-top-navigation-by-user-activation') === -1) {
							newSandbox += ' allow-top-navigation-by-user-activation';
						}
						
						$iframe.attr('sandbox', newSandbox.trim());
						console.log('Stirjoy: Fixed sandbox on payment iframe:', src.substring(0, 80));
						
						// CRITICAL: For Google Pay iframes, remove sandbox entirely if it's blocking
						// Google Pay iframes MUST be able to execute scripts
						if (src.indexOf('pay.google') !== -1 || src.indexOf('generate_gpay') !== -1) {
							// Remove sandbox entirely for Google Pay - it's required for functionality
							$iframe.removeAttr('sandbox');
							console.log('Stirjoy: Removed sandbox from Google Pay iframe (required for functionality)');
							
							// Also monitor to prevent sandbox from being re-added
							var checkInterval = setInterval(function() {
								if (!$iframe.length || !$iframe.parent().length) {
									clearInterval(checkInterval);
									return;
								}
								var currentSandbox = $iframe.attr('sandbox');
								if (currentSandbox) {
									$iframe.removeAttr('sandbox');
									console.log('Stirjoy: Prevented sandbox from being re-added to Google Pay iframe');
								}
							}, 200);
							
							// Stop monitoring after 10 seconds
							setTimeout(function() {
								clearInterval(checkInterval);
							}, 10000);
						}
					}
				} else {
					// No sandbox is good - ensure it stays that way
					// Monitor to prevent sandbox from being added
				}
			} else {
				// For other iframes, just add missing permissions
				if (sandbox) {
					var newSandbox = sandbox;
					var needsUpdate = false;
					
					if (sandbox.indexOf('allow-scripts') === -1) {
						newSandbox += ' allow-scripts';
						needsUpdate = true;
					}
					if (sandbox.indexOf('allow-same-origin') === -1) {
						newSandbox += ' allow-same-origin';
						needsUpdate = true;
					}
					if (sandbox.indexOf('allow-forms') === -1) {
						newSandbox += ' allow-forms';
						needsUpdate = true;
					}
					
					if (needsUpdate) {
						$iframe.attr('sandbox', newSandbox.trim());
					}
				}
			}
		});
	}
	
	// CRITICAL: Use MutationObserver to catch dynamically created iframes immediately
	// This is essential for Google Pay iframes that are created after page load
	if (window.MutationObserver) {
		var iframeObserver = new MutationObserver(function(mutations) {
			var foundNewIframe = false;
			mutations.forEach(function(mutation) {
				if (mutation.addedNodes) {
					mutation.addedNodes.forEach(function(node) {
						if (node.nodeType === 1) { // Element node
							// Check if it's an iframe or contains iframes
							if (node.tagName === 'IFRAME') {
								foundNewIframe = true;
								// Fix immediately
								setTimeout(function() {
									fixStripeIframeSandbox();
								}, 10);
							} else if (node.querySelectorAll) {
								var iframes = node.querySelectorAll('iframe');
								if (iframes.length > 0) {
									foundNewIframe = true;
									// Fix immediately
									setTimeout(function() {
										fixStripeIframeSandbox();
									}, 10);
								}
							}
						}
					});
				}
			});
		});
		
		// Observe the entire document for new iframes
		iframeObserver.observe(document.body || document.documentElement, {
			childList: true,
			subtree: true
		});
		
		// Also observe the payment section specifically
		if ($('#payment').length) {
			iframeObserver.observe(document.getElementById('payment'), {
				childList: true,
				subtree: true
			});
		}
	}
	
	// Monitor and fix iframe sandbox issues continuously (very frequent for payment iframes)
	setInterval(fixStripeIframeSandbox, 300);
	
	// Also fix immediately on page load and after payment method changes
	$(document).ready(function() {
		fixStripeIframeSandbox();
		// Fix again after a short delay to catch late-loading iframes
		setTimeout(fixStripeIframeSandbox, 100);
		setTimeout(fixStripeIframeSandbox, 500);
		setTimeout(fixStripeIframeSandbox, 1000);
	});
	
	$(document.body).on('updated_checkout', function() {
		fixStripeIframeSandbox();
		// Fix again after checkout updates
		setTimeout(fixStripeIframeSandbox, 100);
		setTimeout(fixStripeIframeSandbox, 500);
	});
	
	// CRITICAL: Force HTTPS for all images to prevent mixed content warnings
	function forceHttpsImages() {
		if (window.location.protocol === 'https:') {
			$('img[src^="http://"]').each(function() {
				var $img = $(this);
				var src = $img.attr('src');
				if (src && src.indexOf('http://') === 0) {
					$img.attr('src', src.replace('http://', 'https://'));
				}
			});
		}
	}
	
	// Fix images on page load and after checkout updates
	forceHttpsImages();
	$(document.body).on('updated_checkout', forceHttpsImages);
	setInterval(forceHttpsImages, 2000);
	
	// Function to remove Stripe Express Checkout elements and WooCommerce Payments Express Checkout
	function removeStripeExpressElements() {
		// Remove Stripe Express Checkout elements
		$('#wc-stripe-express-checkout-element, #wc-stripe-express-checkout-button-separator, #wc-stripe-express-checkout__order-attribution-inputs').remove();
		
		// CRITICAL: Remove WooCommerce Payments Express Checkout wrapper
		$('.wcpay-express-checkout-wrapper, #wcpay-express-checkout-wrapper, wcpay-express-checkout-wrapper').remove();
		$('[class*="wcpay-express"], [id*="wcpay-express"], [class*="express-checkout-wrapper"]').remove();
		$('#wcpay-express-checkout-button-separator, #wcpay-express-checkout__order-attribution-inputs').remove();
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
	
	// CRITICAL: Make Stripe Elements functional by positioning them over display fields
	// Since Stripe Elements are iframes, we position the container divs over display fields
	function positionStripeElementsOverDisplayFields() {
		// Wait for Stripe Elements to be mounted
		var stripeCardElement = $('#stripe-card-element');
		var stripeExpElement = $('#stripe-exp-element');
		var stripeCvcElement = $('#stripe-cvc-element');
		
		if (!stripeCardElement.length) {
			return false;
		}
		
		// Ensure payment section is accessible
		ensurePaymentSectionAccessible();
		
		// Check if Stripe is using inline form (single card element)
		var isInlineForm = !stripeExpElement.length || stripeExpElement.length === 0;
		
		if (isInlineForm) {
			// Inline form: position the single card element container over all display fields
			var $cardContainer = $('.stirjoy-card-container');
			if ($cardContainer.length && stripeCardElement.length) {
				var containerOffset = $cardContainer.offset();
				var containerWidth = $cardContainer.outerWidth();
				var containerHeight = $cardContainer.outerHeight();
				
				// Get the parent container of Stripe card element
				var $stripeContainer = stripeCardElement.closest('.wc-stripe-elements-field, #wc-stripe-cc-form, fieldset');
				if (!$stripeContainer.length) {
					$stripeContainer = stripeCardElement.parent();
				}
				
				// Position Stripe container over the display container
				$stripeContainer.css({
					'position': 'fixed',
					'top': containerOffset.top + 'px',
					'left': containerOffset.left + 'px',
					'width': containerWidth + 'px',
					'height': containerHeight + 'px',
					'opacity': '0.01', // Almost invisible but still functional
					'z-index': '1000',
					'pointer-events': 'auto',
					'background': 'transparent',
					'border': 'none',
					'padding': '0',
					'margin': '0'
				});
				
				// Make Stripe card element fill the container
				stripeCardElement.css({
					'width': '100%',
					'height': '100%',
					'min-height': containerHeight + 'px'
				});
				
				// Make display fields show placeholder but not interfere
				$('.stirjoy-card-input').css({
					'pointer-events': 'none',
					'color': 'transparent',
					'caret-color': 'transparent'
				});
				
				// Listen to Stripe changes and update display fields for visual feedback
				if (typeof stripe_card !== 'undefined' && stripe_card) {
					stripe_card.on('change', function(event) {
						if (event.complete && event.last4) {
							$('#stirjoy_card_number_display').val('**** **** **** ' + event.last4).css('color', '#333');
						} else if (event.empty) {
							$('#stirjoy_card_number_display').val('').css('color', 'transparent');
						}
					});
				}
				
				console.log('Stirjoy: Positioned Stripe inline card element over display fields');
				return true;
			}
		} else {
			// Separate form: position each element over corresponding display field
			var positioned = false;
			
			// Card number
			if (stripeCardElement.length) {
				var $cardNumberDisplay = $('#stirjoy_card_number_display');
				if ($cardNumberDisplay.length) {
					var cardNumberOffset = $cardNumberDisplay.offset();
					var cardNumberWidth = $cardNumberDisplay.outerWidth();
					var cardNumberHeight = $cardNumberDisplay.outerHeight();
					
					var $stripeCardContainer = stripeCardElement.closest('.wc-stripe-elements-field, .stripe-card-group');
					if (!$stripeCardContainer.length) {
						$stripeCardContainer = stripeCardElement.parent();
					}
					
					$stripeCardContainer.css({
						'position': 'fixed',
						'top': cardNumberOffset.top + 'px',
						'left': cardNumberOffset.left + 'px',
						'width': cardNumberWidth + 'px',
						'height': cardNumberHeight + 'px',
						'opacity': '0.01',
						'z-index': '1000',
						'pointer-events': 'auto',
						'background': 'transparent'
					});
					
					$cardNumberDisplay.css({
						'pointer-events': 'none',
						'color': 'transparent',
						'caret-color': 'transparent'
					});
					
					if (typeof stripe_card !== 'undefined' && stripe_card) {
						stripe_card.on('change', function(event) {
							if (event.complete && event.last4) {
								$cardNumberDisplay.val('**** **** **** ' + event.last4).css('color', '#333');
							} else if (event.empty) {
								$cardNumberDisplay.val('').css('color', 'transparent');
							}
						});
					}
					positioned = true;
				}
			}
			
			// Expiry
			if (stripeExpElement.length) {
				var $expiryDisplay = $('#stirjoy_card_expiry_display');
				if ($expiryDisplay.length) {
					var expiryOffset = $expiryDisplay.offset();
					var expiryWidth = $expiryDisplay.outerWidth();
					var expiryHeight = $expiryDisplay.outerHeight();
					
					var $stripeExpContainer = stripeExpElement.closest('.wc-stripe-elements-field');
					if (!$stripeExpContainer.length) {
						$stripeExpContainer = stripeExpElement.parent();
					}
					
					$stripeExpContainer.css({
						'position': 'fixed',
						'top': expiryOffset.top + 'px',
						'left': expiryOffset.left + 'px',
						'width': expiryWidth + 'px',
						'height': expiryHeight + 'px',
						'opacity': '0.01',
						'z-index': '1000',
						'pointer-events': 'auto',
						'background': 'transparent'
					});
					
					$expiryDisplay.css({
						'pointer-events': 'none',
						'color': 'transparent',
						'caret-color': 'transparent'
					});
					
					if (typeof stripe_exp !== 'undefined' && stripe_exp) {
						stripe_exp.on('change', function(event) {
							if (event.complete) {
								$expiryDisplay.val(event.value || '').css('color', '#333');
							} else if (event.empty) {
								$expiryDisplay.val('').css('color', 'transparent');
							}
						});
					}
					positioned = true;
				}
			}
			
			// CVC
			if (stripeCvcElement.length) {
				var $cvcDisplay = $('#stirjoy_card_cvc_display');
				if ($cvcDisplay.length) {
					var cvcOffset = $cvcDisplay.offset();
					var cvcWidth = $cvcDisplay.outerWidth();
					var cvcHeight = $cvcDisplay.outerHeight();
					
					var $stripeCvcContainer = stripeCvcElement.closest('.wc-stripe-elements-field');
					if (!$stripeCvcContainer.length) {
						$stripeCvcContainer = stripeCvcElement.parent();
					}
					
					$stripeCvcContainer.css({
						'position': 'fixed',
						'top': cvcOffset.top + 'px',
						'left': cvcOffset.left + 'px',
						'width': cvcWidth + 'px',
						'height': cvcHeight + 'px',
						'opacity': '0.01',
						'z-index': '1000',
						'pointer-events': 'auto',
						'background': 'transparent'
					});
					
					$cvcDisplay.css({
						'pointer-events': 'none',
						'color': 'transparent',
						'caret-color': 'transparent'
					});
					
					if (typeof stripe_cvc !== 'undefined' && stripe_cvc) {
						stripe_cvc.on('change', function(event) {
							if (event.complete) {
								$cvcDisplay.val('***').css('color', '#333');
							} else if (event.empty) {
								$cvcDisplay.val('').css('color', 'transparent');
							}
						});
					}
					positioned = true;
				}
			}
			
			if (positioned) {
				console.log('Stirjoy: Positioned Stripe elements over display fields');
			}
			return positioned;
		}
	}
	
	// CRITICAL: Continuously try to position Stripe Elements (for SiteGround compatibility)
	function tryPositionStripeElements(retries) {
		retries = retries || 30; // Try for 15 seconds
		
		if (positionStripeElementsOverDisplayFields()) {
			// Success, also update on window resize and scroll
			$(window).on('resize scroll', function() {
				setTimeout(positionStripeElementsOverDisplayFields, 100);
			});
		} else if (retries > 0) {
			setTimeout(function() {
				tryPositionStripeElements(retries - 1);
			}, 500);
		}
	}
	
	// Start trying to position immediately and after delays
	tryPositionStripeElements();
	setTimeout(tryPositionStripeElements, 1000);
	setTimeout(tryPositionStripeElements, 2000);
	setTimeout(tryPositionStripeElements, 3000);
	setTimeout(tryPositionStripeElements, 5000);
	
	// Also try after checkout updates and payment method changes
	$(document.body).on('updated_checkout', function() {
		setTimeout(function() {
			tryPositionStripeElements();
		}, 500);
	});
	
	// Also try when payment method is selected
	$(document.body).on('change', '#payment input[name="payment_method"]', function() {
		setTimeout(function() {
			tryPositionStripeElements();
		}, 1000);
	});
	
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
		// CRITICAL: Fix sandboxed iframe issues for Stripe
		if ($('#payment').length) {
			// Temporarily make payment section visible for initialization
			var $paymentSection = $('#payment');
			$paymentSection.css({
				'position': 'absolute',
				'left': '-9999px',
				'visibility': 'visible',
				'height': 'auto',
				'overflow': 'visible',
				'pointer-events': 'auto'
			});
			
			// CRITICAL: Remove or fix sandbox attributes on iframes that block Stripe
			$('#payment iframe, iframe[src*="stripe"]').each(function() {
				var $iframe = $(this);
				var sandbox = $iframe.attr('sandbox');
				if (sandbox) {
					// Add allow-scripts if missing
					if (sandbox.indexOf('allow-scripts') === -1) {
						$iframe.attr('sandbox', sandbox + ' allow-scripts allow-same-origin allow-forms');
					}
				} else {
					// If no sandbox, ensure it's not added
					$iframe.removeAttr('sandbox');
				}
			});
			
			// Trigger payment gateway initialization events
			if (paymentMethod) {
				$('#payment input[name="payment_method"][value="' + paymentMethod + '"]').trigger('change');
			} else {
				$('#payment input[name="payment_method"]').first().trigger('change');
			}
			
			// Restore after a short delay
			setTimeout(function() {
				$paymentSection.css({
					'position': 'absolute',
					'left': '-9999px',
					'visibility': 'visible', // Keep visible for Stripe to work
					'height': 'auto',
					'overflow': 'visible',
					'pointer-events': 'auto'
				});
			}, 100);
		}
		
		// CRITICAL: Don't prevent default - let payment gateway handle submission
		// Stripe needs to tokenize the card before form submission
		return true;
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
		//var preferredIds = ['card'];
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
				//if (value.indexOf('card') !== -1) {
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
	
	// Function to ensure payment section is accessible for initialization
	// CRITICAL: Payment section must be accessible (even if off-screen) for payment methods to initialize
	// This is especially important on SiteGround where CSS display:none can prevent initialization
	function ensurePaymentSectionAccessible() {
		var $paymentSection = $('#payment');
		if ($paymentSection.length) {
			// Check if payment section is hidden by CSS
			var computedDisplay = window.getComputedStyle($paymentSection[0]).display;
			var isHidden = computedDisplay === 'none' || 
			              $paymentSection.is(':hidden') ||
			              $paymentSection.css('visibility') === 'hidden';
			
			if (isHidden) {
				// Make it accessible but off-screen for initialization
				$paymentSection.css({
					'position': 'absolute',
					'left': '-9999px',
					'visibility': 'visible',
					'height': 'auto',
					'overflow': 'visible',
					'pointer-events': 'auto',
					'display': 'block' // Override display: none !important
				});
				console.log('Stirjoy: Made payment section accessible for initialization');
			}
		}
	}
	
	// Function to wait for payment methods to load and then initialize
	function waitForPaymentMethodsAndInitialize(retries) {
		retries = retries || 20; // Increased retries for SiteGround (10 seconds)
		
		// CRITICAL: Ensure payment section is accessible before checking
		ensurePaymentSectionAccessible();
		
		var status = checkPaymentGatewayStatus(false); // Don't log on every retry
		
		if (!status.paymentMethodsExist && retries > 0) {
			// Payment methods not loaded yet, wait and retry
			// Use shorter interval for faster detection on SiteGround
			setTimeout(function() {
				waitForPaymentMethodsAndInitialize(retries - 1);
			}, 500);
			return;
		}
		
		if (status.paymentMethodsExist) {
			// Payment methods are loaded, set default
			console.log('Stirjoy: Payment methods loaded, setting default...');
			
			// CRITICAL: Ensure payment section is accessible before setting default
			ensurePaymentSectionAccessible();
			
			var setSuccess = setDefaultPaymentMethod();
			
			if (!setSuccess) {
				// If setting failed, retry after a short delay
				setTimeout(function() {
					ensurePaymentSectionAccessible();
					setDefaultPaymentMethod();
				}, 200);
			}
			
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
								ensurePaymentSectionAccessible();
								$('#payment input[name="payment_method"]:checked').trigger('change');
							}
						}, 2000);
					}
				}
			}, 500);
		} else {
			if (retries === 0) {
				console.error('Stirjoy Error: Payment methods not found after maximum retries');
				// Last attempt: try to force initialization
				ensurePaymentSectionAccessible();
				setTimeout(function() {
					setDefaultPaymentMethod();
				}, 500);
			}
		}
	}
	
	// CRITICAL: Initialize payment method selection immediately and with multiple attempts
	// This ensures it works on SiteGround where timing may be different due to caching
	ensurePaymentSectionAccessible();
	
	// Function to force set default payment method (more aggressive for SiteGround)
	function forceSetDefaultPaymentMethod() {
		ensurePaymentSectionAccessible();
		
		if (!$('#payment').length || !$('#payment input[name="payment_method"]').length) {
			return false;
		}
		
		// Check if a payment method is already selected
		var $selected = $('#payment input[name="payment_method"]:checked');
		if ($selected.length) {
			var selectedValue = $selected.val().toLowerCase();
			// If it's already a card/stripe method, we're good
			if (selectedValue.indexOf('stripe') !== -1 || selectedValue.indexOf('card') !== -1) {
			//if (selectedValue.indexOf('card') !== -1) {
				console.log('Stirjoy: Credit card already selected:', selectedValue);
				return true;
			}
		}
		
		// Force set default
		var success = setDefaultPaymentMethod();
		if (success) {
			console.log('Stirjoy: Force set default payment method (SiteGround compatibility)');
		}
		return success;
	}
	
	// Try immediately (for fast loading)
	setTimeout(function() {
		forceSetDefaultPaymentMethod();
	}, 100);
	
	// Also try after a delay (for slower loading on SiteGround)
	setTimeout(function() {
		forceSetDefaultPaymentMethod();
		waitForPaymentMethodsAndInitialize();
	}, 1000);
	
	// And try again after longer delay (SiteGround cache might delay things)
	setTimeout(function() {
		forceSetDefaultPaymentMethod();
		waitForPaymentMethodsAndInitialize();
	}, 3000);
	
	// CRITICAL: Additional attempt after 5 seconds for SiteGround
	setTimeout(function() {
		forceSetDefaultPaymentMethod();
	}, 5000);
	
	// CRITICAL: Monitor continuously for first 10 seconds (SiteGround specific)
	var sitegroundMonitorInterval = setInterval(function() {
		var $selected = $('#payment input[name="payment_method"]:checked');
		if ($selected.length) {
			var selectedValue = $selected.val().toLowerCase();
			if (selectedValue.indexOf('stripe') !== -1 || selectedValue.indexOf('card') !== -1) {
				// Credit card is selected, stop monitoring
				clearInterval(sitegroundMonitorInterval);
				console.log('Stirjoy: Credit card confirmed selected, stopping monitor');
			} else {
				// Not credit card, try to set it
				forceSetDefaultPaymentMethod();
			}
		} else {
			// Nothing selected, try to set it
			forceSetDefaultPaymentMethod();
		}
	}, 500);
	
	// Stop monitoring after 10 seconds
	setTimeout(function() {
		clearInterval(sitegroundMonitorInterval);
	}, 10000);
	
	// Also initialize after checkout updates
	$(document.body).on('updated_checkout', function() {
		setTimeout(function() {
			ensurePaymentSectionAccessible();
			// CRITICAL: Use force function to ensure credit card is selected
			forceSetDefaultPaymentMethod();
			
			var status = checkPaymentGatewayStatus(false);
			if (status.paymentMethodsExist) {
				if (status.paymentMethodSelected) {
					var selectedMethod = $('#payment input[name="payment_method"]:checked').val();
					// Only trigger change if it's not already a credit card method
					if (selectedMethod && (selectedMethod.indexOf('stripe') === -1 && selectedMethod.indexOf('card') === -1)) {
						// Try to switch to credit card
						forceSetDefaultPaymentMethod();
					}
					$('#payment input[name="payment_method"]:checked').trigger('change');
				}
			}
		}, 100);
	});
	
	// Ensure form submission works correctly
	// CRITICAL: Don't prevent default for Stripe - let it handle submission
	$('form.checkout').on('submit', function(e) {
		// Check status before form submission
		var status = checkPaymentGatewayStatus();
		
		// Ensure payment method is set before submission
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
			$('form.checkout input[type="hidden"][name="payment_method"]').remove();
			$('form.checkout').append('<input type="hidden" name="payment_method" value="' + paymentMethod + '" />');
		} else {
			console.error('Stirjoy Error: No payment method available');
			alert('Please select a payment method.');
			e.preventDefault();
			return false;
		}
		
		// For Stripe, CRITICAL: Don't prevent default - let Stripe handle submission
		// Stripe needs to tokenize the card and submit via its own handler
		if (paymentMethod && (paymentMethod.indexOf('stripe') !== -1 || paymentMethod.indexOf('card') !== -1)) {
			// Check if Stripe is loaded and ready
			if (typeof Stripe === 'undefined' && typeof stripe === 'undefined') {
				console.warn('Stirjoy: Stripe not loaded, allowing form submission anyway');
			}
			// Let Stripe handle the submission - don't prevent default
			return true;
		}
		
		// For other payment methods, allow normal submission
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

