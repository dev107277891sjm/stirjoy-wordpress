<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

do_action( 'woocommerce_before_customer_login_form' ); ?>

<div class="stirjoy-login-card">
    <div class="stirjoy-login-card-top">
        <h2 class="stirjoy-login-title"><?php esc_html_e( 'Log in', 'woocommerce' ); ?></h2>

        <form class="woocommerce-form woocommerce-form-login login" method="post" novalidate>

            <?php do_action( 'woocommerce_login_form_start' ); ?>

            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="username"><?php esc_html_e( 'Username or email address', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" placeholder="<?php esc_attr_e( 'Your email', 'woocommerce' ); ?>" value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" /><?php // @codingStandardsIgnoreLine ?>
            </p>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide stirjoy-password-field">
                <label for="password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
                <div class="stirjoy-password-wrapper">
                    <input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" placeholder="**********" required aria-required="true" />
                    <!-- <button type="button" class="stirjoy-toggle-password" aria-label="<?php esc_attr_e( 'Show password', 'woocommerce' ); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="stirjoy-eye-icon">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="stirjoy-eye-off-icon" style="display: none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button> -->
                </div>
            </p>

            <?php do_action( 'woocommerce_login_form' ); ?>

            <p class="woocommerce-LostPassword lost_password">
                <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost your password?', 'woocommerce' ); ?></a>
            </p>
            
            <p class="form-row stirjoy-submit-row">
                <?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
                <button type="submit" class="woocommerce-button button woocommerce-form-login__submit stirjoy-login-button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" style="padding: 12px 0 !important;" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>"><?php esc_html_e( 'LOG IN', 'woocommerce' ); ?></button>
            </p>

            <p class="form-row stirjoy-remember-row">
                <label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
                    <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
                </label>
            </p>

            <?php do_action( 'woocommerce_login_form_end' ); ?>

        </form>

    </div>
    <div class="stirjoy-login-card-bottom">
        
        <?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>
            <!-- <div class="stirjoy-login-divider"></div> -->
            <p class="stirjoy-register-prompt">
                <?php esc_html_e( 'Not a client yet?', 'woocommerce' ); ?> <a href="<?php echo esc_url( add_query_arg( 'action', 'register', wc_get_page_permalink( 'myaccount' ) ) ); ?>" class="stirjoy-register-link"><?php esc_html_e( 'Register', 'woocommerce' ); ?></a>
            </p>
        <?php endif; ?>
    </div>
	
</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>

<script>
jQuery(document).ready(function($) {
	// Toggle password visibility
	$('.stirjoy-toggle-password').on('click', function(e) {
		e.preventDefault();
		var $input = $(this).siblings('input');
		var $eyeIcon = $(this).find('.stirjoy-eye-icon');
		var $eyeOffIcon = $(this).find('.stirjoy-eye-off-icon');
		
		if ($input.attr('type') === 'password') {
			$input.attr('type', 'text');
			$eyeIcon.hide();
			$eyeOffIcon.show();
			$(this).attr('aria-label', '<?php echo esc_js( __( 'Hide password', 'woocommerce' ) ); ?>');
		} else {
			$input.attr('type', 'password');
			$eyeIcon.show();
			$eyeOffIcon.hide();
			$(this).attr('aria-label', '<?php echo esc_js( __( 'Show password', 'woocommerce' ) ); ?>');
		}
	});
});
</script>

