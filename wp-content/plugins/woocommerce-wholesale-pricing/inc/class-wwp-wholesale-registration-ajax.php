<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( ! class_exists( 'CLASS_WWP_WHOLESALE_REGISTRATION_AJAX' ) ) {
	
	/**
	 * CLASS_WWP_WHOLESALE_REGISTRATION_AJAX
	 */
	class CLASS_WWP_WHOLESALE_REGISTRATION_AJAX {       
		/**
		 * Method __construct
		 *
		 * @return void
		 */
		public function __construct() {
			$settings = ! empty( get_option( 'wwp_wholesale_pricing_options' ) ) ? get_option( 'wwp_wholesale_pricing_options', true ) : array();
			if ( isset( $settings['over_right_wholesale_form'] ) && 'yes' == $settings['over_right_wholesale_form'] ) {
				add_action( 'wp_ajax_over_right_wholesale_form', array( $this, 'wooc_extra_register_fields' ) );
				add_action( 'wp_ajax_nopriv_over_right_wholesale_form', array( $this, 'wooc_extra_register_fields' ) );
				add_action( 'woocommerce_after_customer_login_form', array( $this, 'action_woocommerce_after_customer_login_form' ), 10, 1 );
				
				add_action( 'wwp_wholesaler_registration_fields_start', array( $this, 'wwp_wholesaler_registration_fields_start' ), 10, 2 );
				add_filter( 'wwp_wholesale_registration_form_errors', array( $this, 'wwp_wholesale_registration_form_errors' ), 10, 1 );

				add_action( 'template_redirect', array( $this, 'wwp_google_script_my_account' ), 5 );
			}
		}
				
		/**
		 * Method wwp_wholesale_registration_form_errors
		 *
		 * @param array $errors
		 *
		 * @return void
		 */
		public function wwp_wholesale_registration_form_errors( $errors ) {
			$post = wwp_get_post_data();
			$errors = implode(',,', $errors);
			if ( !empty( $errors ) && isset( $post['wwp_wholesale_registration_form_over_right']) ) { 
				setcookie( 'wwp_registration_error', $errors, time() + 86400, '/', '', 0 );
			}
			return $errors;
		}
				
		/**
		 * Method wwp_wholesaler_registration_fields_start
		 *
		 * @param array $registrations
		 * @param array $settings
		 *
		 * @return void
		 */
		public function wwp_wholesaler_registration_fields_start( $registrations, $settings ) { 
			
			if ( isset( $_COOKIE['wwp_registration_error'] ) && ! empty( $_COOKIE['wwp_registration_error'] ) ) {
				$cookie = $_COOKIE;
				$errors = $cookie['wwp_registration_error'];
				$errors = explode( ',,', $errors );
				if ( ! empty( $errors ) ) {
					echo '<ul class="woocommerce-error" role="alert">';
					foreach ( $errors as $key => $error ) { 
						echo '<li>' . wp_kses_post( $error ) . '</li>';
					}
					echo '</ul>';
					unset( $_COOKIE['wwp_registration_error'] );
					setcookie( 'wwp_registration_error', null, 0, '/' );
				}
			}
		}
				
		/**
		 * Method action_woocommerce_after_customer_login_form
		 *
		 * @param array $customer_login_form
		 *
		 * @return void
		 */
		public function action_woocommerce_after_customer_login_form( $customer_login_form ) {
			$settings   = wwp_recaptcha_initialize();
			$lang       = get_bloginfo( 'language' );
			?>
			<script type="text/javascript">
			jQuery(document).ready( function() {     
				jQuery.ajax({
					url : "<?php echo esc_html__( admin_url( 'admin-ajax.php' ) ); ?>",
					type : 'POST',
					data : {
						action : 'over_right_wholesale_form'
					},
					success : function( response ) {
						jQuery('form.woocommerce-form.woocommerce-form-register.register').replaceWith(response);
						
						if( jQuery("#wwp-recaptcha").length ) {
							grecaptcha.render('wwp-recaptcha', {
								'sitekey' 	: '<?php echo esc_attr( $settings['site_key'] ); ?>',
								'theme' 	: '<?php echo esc_attr( $settings['theme'] ); ?>',
								'hl'		: '<?php echo esc_attr( $lang ); ?>'
							});
						}

						if( jQuery("#c4wp_captcha_field_1").length ) {
							grecaptcha.render('c4wp_captcha_field_1', {
								'sitekey' 	: '<?php echo esc_attr( $settings['site_key'] ); ?>',
								'theme' 	: '<?php echo esc_attr( $settings['theme'] ); ?>',
								'hl'		: '<?php echo esc_attr( $lang ); ?>'
							});
						}
						
						jQuery('h2.wholesaler-registration').remove(); 
						jQuery('.wwp_wholesaler_registration_form').append('<input type="hidden" name="wwp_wholesale_registration_form_over_right">');
						jQuery('#wwp_wholesaler_copy_billing_address').change(
						function() { 
						if (!this.checked) {
							jQuery('#wholesaler_shipping_address').fadeIn('slow');
						} else {
							jQuery('#wholesaler_shipping_address').fadeOut('slow');
						}
						}
						);
					}
				});
			});
			</script>
			<style>
				form.woocommerce-form.woocommerce-form-register.register {
					display:none;
				}
			</style>
			<?php
		}
		
		/**
		 * Method wooc_extra_register_fields
		 *
		 * @return void
		 */
		public function wooc_extra_register_fields() {
			$wwp_registration_form = new Wwp_Wholesale_Pricing_Registration();
			echo wp_kses( $wwp_registration_form->wwp_registration_form(), shapeSpace_allowed_html() );
			die;
		}
		
		/**
		 * Method wwp_google_script_my_account
		 *
		 * @return void
		 */
		public function wwp_google_script_my_account() {
			require_once WWP_PLUGIN_PATH . 'inc/integrations/recaptcha/index.php';
			// echo '<pre>';
			// print_r($site_key);
			// echo '</pre>';
			// wp_die();

			if ( get_option('woocommerce_myaccount_page_id') != get_the_ID() ) {
				return;
			}

			$settings = get_option( 'wwp_wholesale_pricing_options', true );
			if ( isset( $settings['over_right_wholesale_form'] ) && 'yes' == $settings['over_right_wholesale_form'] ) {
				wp_enqueue_script( 'wwp_google_recaptcha', '//www.google.com/recaptcha/api.js', array(), '2.4.0', true );
			}
		}
	}
	new CLASS_WWP_WHOLESALE_REGISTRATION_AJAX();
}
