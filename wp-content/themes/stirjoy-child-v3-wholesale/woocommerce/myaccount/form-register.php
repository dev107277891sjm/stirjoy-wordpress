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
							<button type="button" class="stirjoy-social-btn stirjoy-google-btn">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M19.6 10.2273C19.6 9.51818 19.5364 8.83636 19.4182 8.18182H10V11.85H15.3818C15.15 12.95 14.4455 13.85 13.4 14.4V16.8H16.6182C18.5091 15.05 19.6 12.7273 19.6 10.2273Z" fill="#4285F4"/>
									<path d="M10 20C12.7 20 14.9636 19.1045 16.6182 17.6L13.4 14.4C12.4909 15.0045 11.3455 15.3818 10 15.3818C7.39545 15.3818 5.19091 13.5364 4.40455 11.1H1.06364V13.5909C2.70909 16.8091 6.09091 20 10 20Z" fill="#34A853"/>
									<path d="M4.40455 11.1C4.20455 10.5 4.09091 9.86364 4.09091 9.20455C4.09091 8.54545 4.20455 7.90909 4.40455 7.30909V4.81818H1.06364C0.386364 6.17727 0 7.65455 0 9.20455C0 10.7545 0.386364 12.2318 1.06364 13.5909L4.40455 11.1Z" fill="#FBBC05"/>
									<path d="M10 3.97727C11.4682 3.97727 12.7864 4.48182 13.8227 5.37273L16.6909 2.50455C14.9591 0.890909 12.6955 0 10 0C6.09091 0 2.70909 3.19091 1.06364 6.40909L4.40455 8.9C5.19091 6.46364 7.39545 4.61818 10 4.61818V3.97727Z" fill="#EA4335"/>
								</svg>
								<span><?php esc_html_e( 'Connect with Google', 'woocommerce' ); ?></span>
							</button>
							<button type="button" class="stirjoy-social-btn stirjoy-facebook-btn">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M20 10C20 4.477 15.523 0 10 0S0 4.477 0 10c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V10h2.54V7.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V10h2.773l-.443 2.89h-2.33v6.988C16.343 19.128 20 14.991 20 10z" fill="#1877F2"/>
								</svg>
								<span><?php esc_html_e( 'Connect with Facebook', 'woocommerce' ); ?></span>
							</button>
							<button type="button" class="stirjoy-social-btn stirjoy-apple-btn">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M15.075 1.667c-.833.833-2.083 1.25-3.333 1.25-.417 0-.833-.083-1.25-.083.417-1.25 1.25-2.5 2.5-3.333.833-.417 1.667-.833 2.5-.833.417 0 .833.083 1.25.083-.417 1.25-1.25 2.083-1.667 2.5zm-1.25 2.5c-2.083 0-3.75 1.667-3.75 4.167 0 3.75 3.333 7.5 3.75 7.5.417 0 .833-.417 1.667-.417.833 0 1.25.417 1.667.417.417 0 1.25-1.25 1.667-2.5-1.25-.417-2.083-1.667-2.083-3.333 0-1.667 1.25-3.333 2.5-4.167-.833-.833-1.667-1.25-2.5-1.25-.417 0-.833.083-1.25.083z" fill="#000000"/>
								</svg>
								<span><?php esc_html_e( 'Connect with Apple', 'woocommerce' ); ?></span>
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
						
						<!-- First Name and Last Name Fields - Side by Side -->
						<div class="stirjoy-name-row">
							<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
								<label for="reg_first_name"><?php esc_html_e( 'First Name', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
								<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_first_name" id="reg_first_name" autocomplete="given-name" placeholder="<?php esc_attr_e( 'First name', 'woocommerce' ); ?>" value="<?php echo ( ! empty( $_POST['billing_first_name'] ) ) ? esc_attr( wp_unslash( $_POST['billing_first_name'] ) ) : ''; ?>" required aria-required="true" />
							</p>

							<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
								<label for="reg_last_name"><?php esc_html_e( 'Last Name', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
								<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_last_name" id="reg_last_name" autocomplete="family-name" placeholder="<?php esc_attr_e( 'Last name', 'woocommerce' ); ?>" value="<?php echo ( ! empty( $_POST['billing_last_name'] ) ) ? esc_attr( wp_unslash( $_POST['billing_last_name'] ) ) : ''; ?>" required aria-required="true" />
							</p>
						</div>
						
						<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
								<label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
								<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" placeholder="<?php esc_attr_e( 'Choose a username', 'woocommerce' ); ?>" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" /><?php // @codingStandardsIgnoreLine ?>
							</p>
						<?php endif; ?>

						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
							<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" placeholder="<?php esc_attr_e( 'Your email', 'woocommerce' ); ?>" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required aria-required="true" /><?php // @codingStandardsIgnoreLine ?>
						</p>

						<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
								<label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
								<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" placeholder="<?php esc_attr_e( 'Choose password', 'woocommerce' ); ?>" required aria-required="true" />
							</p>
						<?php else : ?>
							<p><?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'woocommerce' ); ?></p>
						<?php endif; ?>
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

