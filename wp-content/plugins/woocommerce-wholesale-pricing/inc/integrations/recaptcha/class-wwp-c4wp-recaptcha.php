<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once WWP_PLUGIN_PATH . 'inc/integrations/recaptcha/class-wwp-abstract-recaptcha.php';

/**
 * C4WP reCaptcha
 * 
 * @package     WWP_C4WP_ReCAPTCHA 
 * @subpackage  WWP_Abstract_ReCAPTCHA
 *
 * @since  2.4.0
 */

class WWP_C4WP_ReCAPTCHA extends WWP_Abstract_ReCAPTCHA {
	/**
	 * Settings
	 *
	 * @var array
	 */
	private $settings = array();
	
	/**
	 * Method __construct
	 *
	 * @return void
	 */
	public function __construct( $settings ) {

		$this->settings = $settings;
	
		add_action( 'wwp_wholesaler_registration_form_fields_end', array( $this, 'add_recaptcha' ) );

		add_filter( 'wwp_process_registration_form', array( $this, 'verify_recaptcha' ), 10, 2 );
	}
	
	/**
	 * Method add_recaptcha
	 *
	 * @return void
	 */
	public function add_recaptcha() {
		if ( isset( $this->settings['captcha_version'] ) && 'v2_checkbox' == $this->settings['captcha_version'] ) {
			$this->display_recaptcha_v2();
		}
	}
	
	/**
	 * Method display_recaptcha_v2
	 *
	 * @return void
	 */
	public function display_recaptcha_v2() {
		if ( isset( $this->settings['site_key'], $this->settings['secret_key'] ) && ! empty( $this->settings['site_key'] ) && ! empty( $this->settings['secret_key'] ) ) {

			if ( defined( 'C4WP_PLUGIN_DIR' ) && class_exists( 'C4WP\C4WP_Functions' ) ) {
				require_once C4WP_PLUGIN_DIR . 'includes/class-c4wp-functions.php';
				C4WP\C4WP_Functions::c4wp_captcha_form_field( true ); 
			}
		}
	}
	
	/**
	 * Method verify_recaptcha
	 *
	 * @return void
	 */
	public function verify_recaptcha( array $errors, array $request ) {
	
		if ( isset( $this->settings['captcha_version'] ) && 'v2_checkbox' == $this->settings['captcha_version'] ) {

			if ( empty( $this->settings['site_key'] ) || empty( $this->settings['secret_key'] ) ) {
				$errors['invalid_keys'] = esc_html__( 'Google reCaptcha keys not found.', 'woocommerce-wholesale-pricing' );
			}
			/**
			 * Hooks: filter recaptcha error message
			 * 
			 * @since 2.4.0
			 */
			$notice_recaptcha = apply_filters( 'wwp_recaptcha_error_msg', isset( $this->settings['error_message'] ) ? $this->settings['error_message'] : esc_html__( 'Robot verification failed, please try again.', 'woocommerce-wholesale-pricing' ) );   
			
			if ( isset( $request['g-recaptcha-response'] ) ) {              
				if ( function_exists( 'anr_verify_captcha' ) ) {
					if ( ! anr_verify_captcha() ) {
						$errors['invalid_captcha'] = $notice_recaptcha;
					}
				}
			}

		}

		return $errors;
	}
}
