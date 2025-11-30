<?php
function wwp_recaptcha_initialize() {
	/**
	 * Core recaptcha Integration
	 */
	$settings = get_option( 'wwp_wholesale_registration_options' );

	if ( isset( $settings['enable_recaptcha'] ) && 'yes' == $settings['enable_recaptcha'] ) {
		require_once WWP_PLUGIN_PATH . 'inc/integrations/recaptcha/class-wwp-recaptcha.php';
		( new WWP_ReCAPTCHA( $settings ) );
		$site_key   = isset( $settings['recaptcha_v2_site_key'] ) ? $settings['recaptcha_v2_site_key'] : '' ;
		$theme      = isset( $settings['recaptcha_v2_theme'] ) ? $settings['recaptcha_v2_theme'] : '' ;
		return array( 'site_key' => $site_key, 'theme' => $theme );
	}

	/**
	 * C4wp recaptcha integration
	 * 
	 * V2 Only
	 */
	$settings = ( is_multisite() ) ? get_site_option('c4wp_admin_options') : get_option('c4wp_admin_options');

	if ( function_exists( 'anr_verify_captcha' ) ) {
		if ( isset( $settings['enabled_forms'] ) && in_array( 'wwp_wholesale_recaptcha', $settings['enabled_forms'] ) ) {
			require_once WWP_PLUGIN_PATH . 'inc/integrations/recaptcha/class-wwp-c4wp-recaptcha.php';
			( new WWP_C4WP_ReCAPTCHA( $settings ) );
			$site_key   = isset( $settings['site_key'] ) ? $settings['site_key'] : '';
			$theme      = isset( $settings['theme'] ) ? $settings['theme'] : '';
			return array( 'site_key' => $site_key, 'theme' => $theme );
		}
	}

	/**
	 * I13_woo reCaptcha for WooCommerce V2 and V3 Integration
	 */
	$settings = array(
		'enable'            => get_option( 'wwp_recapcha_enable_on_woo_wholesale' ),
		'error_message'     => get_option( 'i13_recapcha_error_msg_v3_invalid_captcha' ),
		'version'           => get_option('i13_recapcha_version'),
		'v2_site_key'       => get_option('wc_settings_tab_recapcha_site_key'),
		'v2_secret_key'     => get_option('wc_settings_tab_recapcha_secret_key'),
		'v3_site_key'       => get_option('wc_settings_tab_recapcha_site_key_v3'),
		'v3_secret_key'     => get_option('wc_settings_tab_recapcha_secret_key_v3'),
	);

	/**
	* Filter
	*
	* @since 2.4
	*/
	$active_plugins = (array) apply_filters('active_plugins', get_option('active_plugins', array()));

	if ( 'yes' == $settings['enable'] && in_array( 'recaptcha-for-woocommerce/woo-recaptcha.php', $active_plugins ) ) {
		require_once WWP_PLUGIN_PATH . 'inc/integrations/recaptcha/class-wwp-i13-recaptcha.php';
		( new WWP_I13_Woo_ReCAPTCHA( $settings ) );
		return array( 'site_key' => $settings['v2_site_key'], 'theme' => 'light' );
	}

	return false;
}

wwp_recaptcha_initialize();
