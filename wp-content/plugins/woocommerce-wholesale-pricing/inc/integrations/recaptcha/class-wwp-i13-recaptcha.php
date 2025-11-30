<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once WWP_PLUGIN_PATH . 'inc/integrations/recaptcha/class-wwp-abstract-recaptcha.php';

/**
 * I13 reCaptcha
 * 
 * @package     WWP_I13_Woo_ReCAPTCHA 
 * @subpackage  WWP_Abstract_ReCAPTCHA
 *
 * @since  2.4.0
 */

class WWP_I13_Woo_ReCAPTCHA extends WWP_Abstract_ReCAPTCHA {

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

		add_filter('pre_option_i13_recapcha_enable_on_login', array( $this, 'i13_woo_recaptcha_tweaks' ), 999, 2);
	}
	
	/**
	 * Method add_recaptcha
	 *
	 * @return void
	 */
	public function add_recaptcha() {

		$i13_recaptcha = new I13_Woo_Recpatcha();
		echo $i13_recaptcha->add_woo_recaptcha_to_custom_form( '', array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function verify_recaptcha( array $errors, array $request ) {
		if ( 'v2' == $this->settings['version'] ) {
			$grecaptcha_v2_site_key   = esc_attr( $this->settings['v2_site_key'] );
			$grecaptcha_v2_secret_key = esc_attr( $this->settings['v2_secret_key'] );
			
			if ( empty( $grecaptcha_v2_site_key ) || empty( $grecaptcha_v2_secret_key ) ) {
				$errors['invalid_keys'] = esc_html__( 'Google reCaptcha keys not found.', 'woocommerce-wholesale-pricing' );
			}
			
			if ( isset( $request['g-recaptcha-response'] ) && ! empty( $request['g-recaptcha-response'] ) ) {
				$response = wp_remote_post(
					'https://www.google.com/recaptcha/api/siteverify',
					array(
						'body' => array(
							'secret'   => $grecaptcha_v2_secret_key,
							'response' => sanitize_text_field($request['g-recaptcha-response'] ),
						),
					)
				);

				$data = wp_remote_retrieve_body( $response );
				$data = ( array ) json_decode( $data );

				if ( isset( $data['error-codes'] ) && is_array( $data['error-codes'] ) && count( $data['error-codes'] ) ) {
					foreach ( $data['error-codes'] as $index => $error_code ) {
						$errors[$index] = $error_code;
					}
				}

				if ( isset( $data['success'] ) && true === $data['success'] ) {
					return $errors;
				}
			} else {
				$errors['invalid_captcha'] = empty( $this->settings['error_message'] ) ? wp_kses( __( '<strong>ERROR:</strong> Please confirm that you are not a robot.', 'woocommerce-wholesale-pricing' ), array( 'strong' => array() ) ) : $this->settings['error_message'];
			}


		} else if ( 'v3' == $this->settings['version'] ) {
			$grecaptcha_v3_site_key   = esc_attr( $this->settings['v3_site_key'] );
			$grecaptcha_v3_secret_key = esc_attr( $this->settings['v3_secret_key'] );
			$grecaptcha_v3_score      = '0.3';
			
			if ( empty( $grecaptcha_v3_site_key ) || empty( $grecaptcha_v3_secret_key ) ) {
				$errors['invalid_keys'] = esc_html__( 'Google reCaptcha keys not found.', 'woocommerce-wholesale-pricing' );
			}
			
			if ( isset( $request['i13_recaptcha_login_token'] ) && ! empty( $request['i13_recaptcha_login_token'] ) ) {
				$response = wp_remote_post(
					'https://www.google.com/recaptcha/api/siteverify',
					array(
						'body' => array(
							'secret'   => $grecaptcha_v3_secret_key,
							'response' => sanitize_text_field( $request['i13_recaptcha_login_token'] ),
							'remoteip' => $this->get_ip_address(),
						),
					)
				);

				$data = wp_remote_retrieve_body( $response );
				$data = ( array ) json_decode( $data );

				if ( isset( $data['error-codes'] ) && is_array( $data['error-codes'] ) && count( $data['error-codes'] ) ) {
					foreach ( $data['error-codes'] as $index => $error_code ) {
						$errors[$index] = $error_code;
					}
				}

				if ( isset( $data['success'] ) && true === $data['success'] ) {
					$grecaptcha_v3_score = (float) $grecaptcha_v3_score;
					if ( isset( $data['action'] ) && ( 'login' === $data['action'] ) && isset( $data['score'] ) && $data['score'] >= $grecaptcha_v3_score ) {
						return $errors;
					} else {
						$errors[] = wp_kses( __( '<strong>ERROR:</strong> Low Score ', 'woocommerce-wholesale-pricing' ) . ':' . esc_html( $data->score ) , array( 'strong' => array() ) );
					}
				}
			} else {
				$errors['invalid_captcha'] = empty( $this->settings['error_message'] ) ? wp_kses( __( '<strong>ERROR:</strong> Please confirm that you are not a robot.', 'woocommerce-wholesale-pricing' ), array( 'strong' => array() ) ) : $this->settings['error_message'];
			}
		}
		return $errors;
	}

	public function i13_woo_recaptcha_tweaks( $value, $option_name ) {                      
		if ( 'i13_recapcha_enable_on_login' === $option_name ) {
			return 'yes';
		}

		return $value;
	}
}
