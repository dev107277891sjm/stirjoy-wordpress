<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once WWP_PLUGIN_PATH . 'inc/integrations/recaptcha/class-wwp-abstract-recaptcha.php';

/**
 * Core reCaptcha
 * 
 * @package     WWP_ReCAPTCHA 
 * @subpackage  WWP_Abstract_ReCAPTCHA
 *
 * @since  2.4.0
 */

class WWP_ReCAPTCHA extends WWP_Abstract_ReCAPTCHA {
	
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

		if ( isset( $this->settings['recaptcha_version'] ) && 'v2' == $this->settings['recaptcha_version'] ) {
			$this->display_recaptcha_v2();
		}

		if ( isset( $this->settings['recaptcha_version'] ) && 'v3' == $this->settings['recaptcha_version'] ) {
			$this->display_recaptcha_v3();
		}
	}
	
	/**
	 * Method display_recaptcha_v2
	 *
	 * @return void
	 */
	public function display_recaptcha_v2() {

		if ( isset( $this->settings['recaptcha_v2_site_key'], $this->settings['recaptcha_v2_secret_key'] ) && ! empty( $this->settings['recaptcha_v2_site_key'] ) && ! empty( $this->settings['recaptcha_v2_secret_key'] ) ) {

			$theme = isset( $this->settings['recaptcha_v2_theme'] ) ? $this->settings['recaptcha_v2_theme'] : 'light';

			wp_enqueue_script( 'wwp-recaptcha-api-v2', esc_url( 'https://www.google.com/recaptcha/api.js' ), array(), null );
			echo '<div 
                    class="g-recaptcha" 
                    id="wwp-recaptcha"
                    data-sitekey="' . esc_attr( $this->settings['recaptcha_v2_site_key'] ) . '" 
                    data-theme="' . esc_attr( $theme ) . '"
                    >
                </div>';
		}
	}
	
	/**
	 * Method display_recaptcha_v3
	 *
	 * @return void
	 */
	public function display_recaptcha_v3() {
		if ( isset( $this->settings['recaptcha_v3_site_key'], $this->settings['recaptcha_v3_secret_key'] ) && ! empty( $this->settings['recaptcha_v3_site_key'] ) && ! empty( $this->settings['recaptcha_v3_secret_key'] ) ) :
		$grecaptcha_v3_site_key = $this->settings['recaptcha_v3_site_key'];
		$grecaptcha_v3_badge    = isset( $this->settings['recaptcha_v3_badge_position'] ) ? esc_attr( $this->settings['recaptcha_v3_badge_position'] ) : 'bottomright';

		$script =
			"if('function' !== typeof wwp_recaptcha) {
				function wwp_recaptcha() {
					grecaptcha.ready(function() {
						[].forEach.call(document.querySelectorAll('.wwp-g-recaptcha'), function(el) {
							const action = el.getAttribute('data-action');
							const form = el.form;
							form.addEventListener('submit', function(e) {
								e.preventDefault();
								grecaptcha.execute('$grecaptcha_v3_site_key', {action: action}).then(function(token) {
									el.setAttribute('value', token);
									const button = form.querySelector(`[type='submit']`);
									if(button) {
										const input = document.createElement('input');
										input.type = 'hidden';
										input.name = button.getAttribute('name');
										input.value = button.value;
										input.classList.add('wwp-submit-input');
										var inputEls = document.querySelectorAll('.wwp-submit-input');
										[].forEach.call(inputEls, function(inputEl) {
											inputEl.remove();
										});
										form.appendChild(input);
									}
									HTMLFormElement.prototype.submit.call(form);
								});
							});
						});
					});
				}
			}";

		wp_enqueue_script( 'wwp-recaptcha-api-v3', ( 'https://www.google.com/recaptcha/api.js?onload=wwp_recaptcha&render=' . esc_attr( $grecaptcha_v3_site_key ) . '&badge=' . esc_attr( $grecaptcha_v3_badge ) ), array(), null );
		wp_add_inline_script( 'wwp-recaptcha-api-v3', $script ); 
			?>
		<input type="hidden" name="g-recaptcha-response" id="wwp-g-recaptcha-response" class="wwp-g-recaptcha" data-action="wwp">
		<?php 
		endif;
	}
	
	/**
	 * Method verify_recaptcha
	 *
	 * @return void
	 */
	public function verify_recaptcha( array $errors, array $request ) {
		if ( isset( $this->settings['recaptcha_version'] ) ) {
			
			if ( 'v2' == $this->settings['recaptcha_version'] ) {
				$grecaptcha_v2_site_key   = isset( $this->settings['recaptcha_v2_site_key'] ) ? esc_attr( $this->settings['recaptcha_v2_site_key'] ) : '';
				$grecaptcha_v2_secret_key = isset( $this->settings['recaptcha_v2_secret_key'] ) ? esc_attr( $this->settings['recaptcha_v2_secret_key'] ) : '';
				
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
					$errors['invalid_captcha'] = wp_kses( __( '<strong>ERROR:</strong> Please confirm that you are not a robot.', 'woocommerce-wholesale-pricing' ), array( 'strong' => array() ) );
				}


			} else if ( 'v3' == $this->settings['recaptcha_version'] ) {
				$grecaptcha_v3_site_key   = isset( $this->settings['recaptcha_v3_site_key'] ) ? esc_attr( $this->settings['recaptcha_v3_site_key'] ) : '';
				$grecaptcha_v3_secret_key = isset( $this->settings['recaptcha_v3_secret_key'] ) ? esc_attr( $this->settings['recaptcha_v3_secret_key'] ) : '';
				$grecaptcha_v3_score      = isset( $this->settings['recaptcha_v3_score'] ) ? esc_attr( $this->settings['recaptcha_v3_score'] ) : '0.3';
				
				if ( empty( $grecaptcha_v3_site_key ) || empty( $grecaptcha_v3_secret_key ) ) {
					$errors['invalid_keys'] = esc_html__( 'Google reCaptcha keys not found.', 'woocommerce-wholesale-pricing' );
				}

				if ( isset( $request['g-recaptcha-response'] ) && ! empty( $request['g-recaptcha-response'] ) ) {
					$response = wp_remote_post(
						'https://www.google.com/recaptcha/api/siteverify',
						array(
							'body' => array(
								'secret'   => $grecaptcha_v3_secret_key,
								'response' => sanitize_text_field( $request['g-recaptcha-response'] ),
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
						if ( isset( $data['action'] ) && ( 'wwp' === $data['action'] ) && isset( $data['score'] ) && $data['score'] >= $grecaptcha_v3_score ) {
							return $errors;
						} else {
							$errors[] = wp_kses( __( '<strong>ERROR:</strong> Low Score ', 'woocommerce-wholesale-pricing' ) . ':' . esc_html( $data->score ) , array( 'strong' => array() ) );
						}
					}
				} else {
					$errors['invalid_captcha'] = wp_kses( __( '<strong>ERROR:</strong> Please confirm that you are not a robot.', 'woocommerce-wholesale-pricing' ), array( 'strong' => array() ) );
				}
			}
		}
		return $errors;
	}
}