<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Abstract recaptcha package
 * 
 * @package     WWP_Abstract_ReCAPTCHA 
 * @subpackage  reCAPTCHA
 *
 * @since  2.4.0
 */

abstract class WWP_Abstract_ReCAPTCHA {
	
	/**
	 * Method add_recaptcha
	 *
	 * @return void
	 */
	abstract public function add_recaptcha();
	
	/**
	 * Method verify_recaptcha
	 *
	 * @param array $errors
	 * @param array $request
	 *
	 * @return void
	 */
	abstract public function verify_recaptcha( array $errors, array $request );
	
	/**
	 * Method get_ip_address
	 *
	 * @return void
	 */
	public function get_ip_address() {

		if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ipaddress = sanitize_text_field( $_SERVER['HTTP_CLIENT_IP'] );
		} else if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ipaddress = sanitize_text_field( $_SERVER['HTTP_X_FORWARDED_FOR'] );
		} else if ( isset( $_SERVER['HTTP_X_FORWARDED'] ) ) {
			$ipaddress = sanitize_text_field( $_SERVER['HTTP_X_FORWARDED'] );
		} else if ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
			$ipaddress = sanitize_text_field( $_SERVER['HTTP_FORWARDED_FOR'] );
		} else if ( isset( $_SERVER['HTTP_FORWARDED'] ) ) {
			$ipaddress = sanitize_text_field( $_SERVER['HTTP_FORWARDED'] );
		} else if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ipaddress = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
		} else {
			$ipaddress = '127.0.0.1';
		}

		return $ipaddress;
	}
}
