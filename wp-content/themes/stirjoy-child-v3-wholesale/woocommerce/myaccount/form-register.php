<?php
/**
 * Registration Form - Sign up and order
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-register.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version Custom
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

do_action( 'woocommerce_before_customer_login_form' ); ?>

<div class="stirjoy-register-page">
	<div class="stirjoy-register-container">
		<!-- Page Title -->
		<div class="stirjoy-register-header">
			<h1 class="stirjoy-register-title"><?php esc_html_e( 'Sign up and order', 'woocommerce' ); ?></h1>
			<p class="stirjoy-register-subtitle"><?php esc_html_e( 'You\'ll get to build your first box as soon as your account is created!', 'woocommerce' ); ?></p>
		</div>

		<div class="stirjoy-register-content">
			<!-- Left Column - Form -->
			<div class="stirjoy-register-form-column">
				<form method="post" class="woocommerce-form woocommerce-form-register register stirjoy-register-form" <?php do_action( 'woocommerce_register_form_tag' ); ?>>

					<?php do_action( 'woocommerce_register_form_start' ); ?>

					<!-- Create Your Account Section -->
					<div class="stirjoy-form-section">
						<h2 class="stirjoy-section-title-big"><?php esc_html_e( 'Create Your Account', 'woocommerce' ); ?></h2>
						
						<!-- Social Login Buttons -->
						<div class="stirjoy-social-login">
							<button type="button" class="stirjoy-social-btn stirjoy-google-btn" data-provider="google">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M19.6 10.2273C19.6 9.51818 19.5364 8.83636 19.4182 8.18182H10V11.85H15.3818C15.15 12.95 14.4455 13.85 13.4 14.4V16.8H16.6182C18.5091 15.05 19.6 12.7273 19.6 10.2273Z" fill="#2d5a27"/>
									<path d="M10 20C12.7 20 14.9636 19.1045 16.6182 17.6L13.4 14.4C12.4909 15.0045 11.3455 15.3818 10 15.3818C7.39545 15.3818 5.19091 13.5364 4.40455 11.1H1.06364V13.5909C2.70909 16.8091 6.09091 20 10 20Z" fill="#2d5a27"/>
									<path d="M4.40455 11.1C4.20455 10.5 4.09091 9.86364 4.09091 9.20455C4.09091 8.54545 4.20455 7.90909 4.40455 7.30909V4.81818H1.06364C0.386364 6.17727 0 7.65455 0 9.20455C0 10.7545 0.386364 12.2318 1.06364 13.5909L4.40455 11.1Z" fill="#2d5a27"/>
									<path d="M10 3.97727C11.4682 3.97727 12.7864 4.48182 13.8227 5.37273L16.6909 2.50455C14.9591 0.890909 12.6955 0 10 0C6.09091 0 2.70909 3.19091 1.06364 6.40909L4.40455 8.9C5.19091 6.46364 7.39545 4.61818 10 4.61818V3.97727Z" fill="#2d5a27"/>
								</svg>
								<span><?php esc_html_e( 'Connect with Google', 'woocommerce' ); ?></span>
							</button>
							<button type="button" class="stirjoy-social-btn stirjoy-facebook-btn" data-provider="facebook">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M20 10C20 4.477 15.523 0 10 0S0 4.477 0 10c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V10h2.54V7.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V10h2.773l-.443 2.89h-2.33v6.988C16.343 19.128 20 14.991 20 10z" fill="#2d5a27"/>
								</svg>
								<span><?php esc_html_e( 'Connect with Facebook', 'woocommerce' ); ?></span>
							</button>
							<button type="button" class="stirjoy-social-btn stirjoy-apple-btn" data-provider="apple">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M18.71 19.5C17.88 20.74 17 21.95 15.66 21.97C14.32 22 13.89 21.18 12.37 21.18C10.84 21.18 10.37 21.95 9.09997 22C7.78997 22.05 6.79997 20.68 5.95997 19.47C4.24997 17 2.93997 12.45 4.69997 9.39C5.56997 7.87 7.12997 6.91 8.81997 6.88C10.1 6.86 11.32 7.75 12.11 7.75C12.89 7.75 14.37 6.68 15.92 6.84C16.57 6.87 18.39 7.1 19.56 8.82C19.47 8.88 17.39 10.1 17.41 12.63C17.44 15.65 20.06 16.66 20.09 16.67C20.06 16.74 19.67 18.11 18.71 19.5ZM13 3.5C13.73 2.67 14.94 2.04 15.94 2C16.07 3.17 15.6 4.35 14.9 5.19C14.21 6.04 13.07 6.7 11.95 6.61C11.8 5.46 12.36 4.26 13 3.5Z" fill="#396A26"/>
								</svg>
								<span><?php esc_html_e( 'Connect with Apple', 'woocommerce' ); ?></span>
							</button>
						</div>
						
						<!-- Social Login SDKs -->
						<?php if ( is_account_page() && isset( $_GET['action'] ) && $_GET['action'] === 'register' ) : ?>
							<!-- Google Sign-In SDK -->
							<script src="https://accounts.google.com/gsi/client" async defer></script>
							<!-- Facebook SDK -->
							<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>
							<!-- Apple Sign-In SDK -->
							<script type="text/javascript" src="https://appleid.cdn-apple.com/appleauth/static/jsapi/appleid/1/en_US/appleid.auth.js"></script>
						<?php endif; ?>

						<!-- Divider -->
						<div class="stirjoy-divider">
							<span class="stirjoy-divider-text"><?php esc_html_e( 'Or sign up with email', 'woocommerce' ); ?></span>
						</div>
					</div>

					<!-- Contact Section -->
					<div class="stirjoy-form-section">
						<h2 class="stirjoy-section-title"><?php esc_html_e( 'Contact', 'woocommerce' ); ?></h2>
						
						<!-- First Name and Last Name Fields - Side by Side -->
						<div class="stirjoy-name-row">
							<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
								<label for="reg_first_name"><?php esc_html_e( 'First Name', 'woocommerce' ); ?><!--&nbsp;<span class="required" aria-hidden="true">*</span>--></label>
								<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_first_name" id="reg_first_name" autocomplete="given-name" placeholder="<?php esc_attr_e( 'First name', 'woocommerce' ); ?>" value="<?php echo ( ! empty( $_POST['billing_first_name'] ) ) ? esc_attr( wp_unslash( $_POST['billing_first_name'] ) ) : ''; ?>" required aria-required="true" />
							</p>

							<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
								<label for="reg_last_name"><?php esc_html_e( 'Last Name', 'woocommerce' ); ?><!--&nbsp;<span class="required" aria-hidden="true">*</span>--></label>
								<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_last_name" id="reg_last_name" autocomplete="family-name" placeholder="<?php esc_attr_e( 'Last name', 'woocommerce' ); ?>" value="<?php echo ( ! empty( $_POST['billing_last_name'] ) ) ? esc_attr( wp_unslash( $_POST['billing_last_name'] ) ) : ''; ?>" required aria-required="true" />
							</p>
						</div>
						
						<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
								<label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?><!--&nbsp;<span class="required" aria-hidden="true">*</span>--></label>
								<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" placeholder="<?php esc_attr_e( 'Choose a username', 'woocommerce' ); ?>" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" /><?php // @codingStandardsIgnoreLine ?>
							</p>
						<?php endif; ?>

					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?><!--&nbsp;<span class="required" aria-hidden="true">*</span>--></label>
						<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" placeholder="<?php esc_attr_e( 'Your email', 'woocommerce' ); ?>" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required aria-required="true" /><?php // @codingStandardsIgnoreLine ?>
					</p>

						<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
								<label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?><!--&nbsp;<span class="required" aria-hidden="true">*</span>--></label>
								<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" placeholder="<?php esc_attr_e( 'Choose password', 'woocommerce' ); ?>" required aria-required="true" />
							</p>
						<?php else : ?>
							<p><?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'woocommerce' ); ?></p>
						<?php endif; ?>
						<span class="stirjoy-email-error" id="stirjoy-email-error" role="alert" style="display: none;"></span>
						
						<!-- Newsletter Subscription Checkbox -->
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide stirjoy-newsletter-checkbox-row">
							<div class="stirjoy-newsletter-checkbox-wrapper">
								<label class="checkbox">
									<input type="checkbox" name="newsletter_subscribe" id="newsletter_subscribe" value="1" />
									<span><?php esc_html_e( 'Subscribe to our newsletter', 'woocommerce' ); ?></span>
								</label>
							</div>
						</p>
					</div>

					<?php do_action( 'woocommerce_register_form' ); ?>

					<!-- Submit Button -->
					<p class="form-row stirjoy-submit-row">
						<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
						<button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit stirjoy-register-button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>"><?php esc_html_e( 'CONTINUE TO DELIVERY', 'woocommerce' ); ?></button>
					</p>

					<?php do_action( 'woocommerce_register_form_end' ); ?>

				</form>
			</div>
		</div>
	</div>
</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>

<script>
(function($) {
	var errorProcessed = false;
	
	// Function to hide WooCommerce error notices immediately (but not our custom error)
	function hideWooCommerceNotices() {
		$('.woocommerce-error, .woocommerce .woocommerce-error, .woocommerce-notices-wrapper .woocommerce-error').not('#stirjoy-email-error').css({
			'display': 'none',
			'visibility': 'hidden',
			'opacity': '0',
			'height': '0',
			'margin': '0',
			'padding': '0',
			'overflow': 'hidden'
		});
	}
	
	// Function to check for email-related errors and display them below password input
	function displayEmailError() {
		// Hide notices first to prevent them from showing
		hideWooCommerceNotices();
		
		// Look for WooCommerce error notices
		var $errorNotices = $('.woocommerce-error, .woocommerce .woocommerce-error, .woocommerce-notices-wrapper .woocommerce-error').not('#stirjoy-email-error');
		var emailErrorFound = false;
		var emailErrorText = '';
		
		$errorNotices.each(function() {
			var $notice = $(this);
			var errorText = $notice.text() || $notice.html();
			
			// Check if this is an email-related error
			if (errorText.toLowerCase().indexOf('email') !== -1 || 
			    errorText.toLowerCase().indexOf('already registered') !== -1 ||
			    errorText.toLowerCase().indexOf('account is already') !== -1) {
				emailErrorFound = true;
				// Extract just the error message text (remove "Error:" prefix if present)
				emailErrorText = errorText.replace(/^.*?Error:\s*/i, '').trim();
				// Remove HTML tags but keep text
				emailErrorText = $('<div>').html(emailErrorText).text().trim();
				
				// Hide the original notice immediately
				$notice.css({
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
		
		// Display error in custom container if found
		if (emailErrorFound && emailErrorText) {
			var $emailError = $('#stirjoy-email-error');
			if ($emailError.length) {
				$emailError.text(emailErrorText).css({
					'display': 'block',
					'visibility': 'visible',
					'opacity': '1'
				});
				// Add error class to email input
				$('#reg_email').addClass('error');
				errorProcessed = true;
			}
		} else if (!errorProcessed) {
			// Only hide if we haven't processed an error yet
			$('#stirjoy-email-error').hide();
			$('#reg_email').removeClass('error');
		}
	}
	
	// Run immediately when script loads (before DOM ready)
	if (typeof jQuery !== 'undefined') {
		hideWooCommerceNotices();
	}
	
	// Run when DOM is ready
	$(document).ready(function() {
		hideWooCommerceNotices();
		displayEmailError();
		
		// Watch for dynamically added notices using MutationObserver (with debouncing)
		if (window.MutationObserver) {
			var checkTimeout;
			var observer = new MutationObserver(function(mutations) {
				// Debounce to avoid too many calls
				clearTimeout(checkTimeout);
				checkTimeout = setTimeout(function() {
					hideWooCommerceNotices();
					displayEmailError();
				}, 50);
			});
			
			// Observe the document body for changes
			if (document.body) {
				observer.observe(document.body, {
					childList: true,
					subtree: true
				});
			}
		}
		
		// Check immediately
		displayEmailError();
		
		// Check after a very short delay to catch any notices added right after page load
		setTimeout(function() {
			hideWooCommerceNotices();
			displayEmailError();
		}, 10);
	});
	
	// Clear error when user starts typing in email field
	$(document).on('input', '#reg_email', function() {
		$('#stirjoy-email-error').hide();
		$(this).removeClass('error');
		errorProcessed = false;
	});
	
	// Re-check on form submit (in case of AJAX validation)
	$(document).on('submit', '.stirjoy-register-form', function() {
		errorProcessed = false;
		setTimeout(function() {
			hideWooCommerceNotices();
			displayEmailError();
		}, 50);
	});
})(jQuery);
</script>

