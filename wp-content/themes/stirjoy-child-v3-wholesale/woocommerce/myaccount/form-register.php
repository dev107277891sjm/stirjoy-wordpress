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
						<h2 class="stirjoy-section-title"><?php esc_html_e( 'Create Your Account', 'woocommerce' ); ?></h2>
						
						<!-- Social Login Buttons -->
						<div class="stirjoy-social-login">
							<button type="button" class="stirjoy-social-btn stirjoy-google-btn">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M19.6 10.2273C19.6 9.51818 19.5364 8.83636 19.4182 8.18182H10V11.85H15.3818C15.15 12.95 14.4455 13.85 13.4 14.4V16.8H16.6182C18.5091 15.05 19.6 12.7273 19.6 10.2273Z" fill="#4285F4"/>
									<path d="M10 20C12.7 20 14.9636 19.1045 16.6182 17.6L13.4 14.4C12.4909 15.0045 11.3455 15.3818 10 15.3818C7.39545 15.3818 5.19091 13.5364 4.40455 11.1H1.06364V13.5909C2.70909 16.8091 6.09091 20 10 20Z" fill="#34A853"/>
									<path d="M4.40455 11.1C4.20455 10.5 4.09091 9.86364 4.09091 9.20455C4.09091 8.54545 4.20455 7.90909 4.40455 7.30909V4.81818H1.06364C0.386364 6.17727 0 7.65455 0 9.20455C0 10.7545 0.386364 12.2318 1.06364 13.5909L4.40455 11.1Z" fill="#FBBC05"/>
									<path d="M10 3.97727C11.4682 3.97727 12.7864 4.48182 13.8227 5.37273L16.6909 2.50455C14.9591 0.890909 12.6955 0 10 0C6.09091 0 2.70909 3.19091 1.06364 6.40909L4.40455 8.9C5.19091 6.46364 7.39545 4.61818 10 4.61818V3.97727Z" fill="#EA4335"/>
								</svg>
								<span><?php esc_html_e( 'Continue with Google', 'woocommerce' ); ?></span>
							</button>
							<button type="button" class="stirjoy-social-btn stirjoy-facebook-btn">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M20 10C20 4.477 15.523 0 10 0S0 4.477 0 10c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V10h2.54V7.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V10h2.773l-.443 2.89h-2.33v6.988C16.343 19.128 20 14.991 20 10z" fill="#1877F2"/>
								</svg>
								<span><?php esc_html_e( 'Continue with Facebook', 'woocommerce' ); ?></span>
							</button>
						</div>

						<!-- Divider -->
						<div class="stirjoy-divider">
							<span class="stirjoy-divider-text"><?php esc_html_e( 'Or sign up with email', 'woocommerce' ); ?></span>
						</div>
					</div>

					<!-- Contact Section -->
					<div class="stirjoy-form-section">
						<h2 class="stirjoy-section-title"><?php esc_html_e( 'Contact', 'woocommerce' ); ?></h2>
						
						<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
								<label for="reg_username"><?php esc_html_e( 'Full Name', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
								<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" placeholder="<?php esc_attr_e( 'Your name', 'woocommerce' ); ?>" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" /><?php // @codingStandardsIgnoreLine ?>
							</p>
						<?php endif; ?>

						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
							<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" placeholder="<?php esc_attr_e( 'Your email', 'woocommerce' ); ?>" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required aria-required="true" /><?php // @codingStandardsIgnoreLine ?>
						</p>

						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="reg_phone"><?php esc_html_e( 'Phone Number', 'woocommerce' ); ?></label>
							<input type="tel" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_phone" id="reg_phone" autocomplete="tel" placeholder="<?php esc_attr_e( 'Your phone', 'woocommerce' ); ?>" value="<?php echo ( ! empty( $_POST['billing_phone'] ) ) ? esc_attr( wp_unslash( $_POST['billing_phone'] ) ) : ''; ?>" />
						</p>

						<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
								<label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
								<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" required aria-required="true" />
							</p>
						<?php else : ?>
							<p><?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'woocommerce' ); ?></p>
						<?php endif; ?>
					</div>

					<!-- Delivery Section -->
					<div class="stirjoy-form-section">
						<h2 class="stirjoy-section-title"><?php esc_html_e( 'Delivery', 'woocommerce' ); ?></h2>
						
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="billing_address_1"><?php esc_html_e( 'Address', 'woocommerce' ); ?></label>
							<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_address_1" id="billing_address_1" placeholder="<?php esc_attr_e( 'Street address', 'woocommerce' ); ?>" value="<?php echo ( ! empty( $_POST['billing_address_1'] ) ) ? esc_attr( wp_unslash( $_POST['billing_address_1'] ) ) : ''; ?>" />
						</p>

						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="billing_city"><?php esc_html_e( 'City', 'woocommerce' ); ?></label>
							<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_city" id="billing_city" placeholder="<?php esc_attr_e( 'City', 'woocommerce' ); ?>" value="<?php echo ( ! empty( $_POST['billing_city'] ) ) ? esc_attr( wp_unslash( $_POST['billing_city'] ) ) : ''; ?>" />
						</p>

						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="billing_postcode"><?php esc_html_e( 'Postal Code', 'woocommerce' ); ?></label>
							<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_postcode" id="billing_postcode" placeholder="<?php esc_attr_e( 'Postal Code', 'woocommerce' ); ?>" value="<?php echo ( ! empty( $_POST['billing_postcode'] ) ) ? esc_attr( wp_unslash( $_POST['billing_postcode'] ) ) : ''; ?>" />
						</p>

						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="order_comments"><?php esc_html_e( 'Notes for Driver (Optional)', 'woocommerce' ); ?></label>
							<textarea class="woocommerce-Input woocommerce-Input--textarea input-textarea" name="order_comments" id="order_comments" placeholder="<?php esc_attr_e( 'e.g. Front door, back gate', 'woocommerce' ); ?>" rows="3"></textarea>
						</p>
					</div>

					<!-- Shipping Method Section -->
					<div class="stirjoy-form-section">
						<h2 class="stirjoy-section-title"><?php esc_html_e( 'Shipping method', 'woocommerce' ); ?></h2>
						<div class="stirjoy-info-box">
							<?php esc_html_e( 'Enter your shipping address to view available shipping methods.', 'woocommerce' ); ?>
						</div>
					</div>

					<!-- Payment Section -->
					<div class="stirjoy-form-section">
						<h2 class="stirjoy-section-title"><?php esc_html_e( 'Payment', 'woocommerce' ); ?></h2>
						<p class="stirjoy-security-text"><?php esc_html_e( 'All transactions are secure and encrypted.', 'woocommerce' ); ?></p>
						
						<div class="stirjoy-payment-methods">
							<span class="stirjoy-payment-icons">
								<span class="stirjoy-payment-icon">MC</span>
								<span class="stirjoy-payment-icon">VISA</span>
								<span class="stirjoy-payment-icon">AMEX</span>
								<span class="stirjoy-payment-icon">+ 4</span>
							</span>
						</div>

						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="card_number"><?php esc_html_e( 'Credit card', 'woocommerce' ); ?></label>
							<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="card_number" id="card_number" placeholder="<?php esc_attr_e( 'Card number', 'woocommerce' ); ?>" autocomplete="cc-number" />
						</p>

						<div class="stirjoy-card-details">
							<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
								<label for="card_expiry"><?php esc_html_e( 'Expiration date (MM/YY)', 'woocommerce' ); ?></label>
								<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="card_expiry" id="card_expiry" placeholder="MM/YY" autocomplete="cc-exp" />
							</p>
							<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
								<label for="card_cvc"><?php esc_html_e( 'Security code', 'woocommerce' ); ?></label>
								<div class="stirjoy-cvc-wrapper">
									<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="card_cvc" id="card_cvc" placeholder="CVC" autocomplete="cc-csc" />
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="stirjoy-lock-icon">
										<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
										<path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
									</svg>
								</div>
							</p>
						</div>

						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="card_name"><?php esc_html_e( 'Name on card', 'woocommerce' ); ?></label>
							<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="card_name" id="card_name" placeholder="<?php esc_attr_e( 'Name on card', 'woocommerce' ); ?>" autocomplete="cc-name" />
						</p>

						<p class="form-row form-row-wide">
							<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
								<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" type="checkbox" name="use_shipping_as_billing" id="use_shipping_as_billing" value="1" />
								<span><?php esc_html_e( 'Use shipping address as billing address', 'woocommerce' ); ?></span>
							</label>
						</p>
					</div>

					<!-- Newsletter Opt-in -->
					<p class="form-row form-row-wide">
						<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
							<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" type="checkbox" name="newsletter" id="newsletter" value="1" />
							<span><?php esc_html_e( 'Subscribe to our newsletter', 'woocommerce' ); ?></span>
						</label>
					</p>

					<?php do_action( 'woocommerce_register_form' ); ?>

					<!-- Submit Button -->
					<p class="form-row stirjoy-submit-row">
						<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
						<button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit stirjoy-register-button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>"><?php esc_html_e( 'CONTINUE TO CHECKOUT', 'woocommerce' ); ?></button>
					</p>

					<!-- Legal Text -->
					<p class="stirjoy-legal-text">
						<?php 
						printf(
							esc_html__( 'By continuing I agree to the %1$s and %2$s', 'woocommerce' ),
							'<a href="' . esc_url( wc_get_page_permalink( 'terms' ) ) . '" target="_blank">' . esc_html__( 'Terms of Service', 'woocommerce' ) . '</a>',
							'<a href="' . esc_url( wc_get_page_permalink( 'privacy' ) ) . '" target="_blank">' . esc_html__( 'Privacy Policy', 'woocommerce' ) . '</a>'
						);
						?>
					</p>

					<?php do_action( 'woocommerce_register_form_end' ); ?>

				</form>
			</div>

			<!-- Right Column - Order Summary -->
			<div class="stirjoy-register-sidebar">
				<div class="stirjoy-order-summary">
					<h2 class="stirjoy-sidebar-title"><?php esc_html_e( 'What You Get', 'woocommerce' ); ?></h2>
					
					<div class="stirjoy-order-details">
						<div class="stirjoy-order-item">
							<span class="stirjoy-order-label"><?php esc_html_e( '6-Meal Box', 'woocommerce' ); ?></span>
							<span class="stirjoy-order-value"><?php echo wc_price( 72.00 ); ?></span>
						</div>
						<div class="stirjoy-order-item">
							<span class="stirjoy-order-label"><?php esc_html_e( 'Shipping', 'woocommerce' ); ?></span>
							<span class="stirjoy-order-value stirjoy-shipping-placeholder"><?php esc_html_e( 'Enter shipping address', 'woocommerce' ); ?></span>
						</div>
						<div class="stirjoy-order-item stirjoy-order-total">
							<span class="stirjoy-order-label"><?php esc_html_e( 'Total', 'woocommerce' ); ?></span>
							<span class="stirjoy-order-value"><?php echo wc_price( 72.00 ); ?></span>
						</div>
					</div>

					<div class="stirjoy-delivery-schedule">
						<p class="stirjoy-schedule-text">
							<strong><?php esc_html_e( 'Your box arrives on the 15th of each month', 'woocommerce' ); ?></strong>
						</p>
						<p class="stirjoy-schedule-text">
							<?php esc_html_e( 'Cutoff to customize: 7 days before', 'woocommerce' ); ?>
						</p>
					</div>

					<div class="stirjoy-promise-section">
						<h3 class="stirjoy-promise-title"><?php esc_html_e( 'Our promise', 'woocommerce' ); ?></h3>
						<ul class="stirjoy-promise-list">
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

					<div class="stirjoy-tip-box">
						<p><?php esc_html_e( 'After signing up, you\'ll customize your first box with your favorite meals!', 'woocommerce' ); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>

