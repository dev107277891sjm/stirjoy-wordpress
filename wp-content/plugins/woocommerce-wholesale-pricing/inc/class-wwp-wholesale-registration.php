<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Woo_Wholesale_Registration
 */
if ( ! class_exists( 'Wwp_Wholesale_Pricing_Registration' ) ) {

	class Wwp_Wholesale_Pricing_Registration {

		public $errors = array();

		public function __construct() {
			$this->errors = array();
			add_shortcode( 'wwp_registration_form', array( $this, 'wwp_registration_form' ) );
			add_action( 'woocommerce_register_form_end', array( $this, 'wwp_wholesale_registeration' ) );
			add_action( 'woocommerce_login_form_end', array( $this, 'action_woocommerce_login_form_end' ), 10 );
			add_action( 'woocommerce_checkout_update_customer', array( $this, 'wwp_checkout_update_customer' ), 10, 2 );
			add_action( 'wp_body_open', array( $this, 'wholeslaer_acess_store' ), 10 );
			add_action( 'woocommerce_archive_description', array( $this, 'wc_no_products_found' ), 10 );
			add_action( 'wp_head', array( $this, 'hook_javascript' ) );
			add_action( 'init', array( $this, 'wholesale_registrattion' ) );
			add_action( 'wwp_registration_end', array( $this, 'wwp_registration_end' ) );
			add_filter( 'login_errors', array( $this, 'no_wordpress_errors' ), 100, 1 );
			add_action( 'woocommerce_store_api_checkout_update_order_from_request', array( $this, 'woocommerce_checkout_create_order' ), 10, 2 );
			add_action( 'user_register', array( $this, 'update_user_role_based_on_email' ));
		}

		public function woocommerce_checkout_create_order( $order, $request ) {

			$settings = get_option( 'wwp_wholesale_pricing_options', true );

			if ( 'yes' == $settings['restrict_store_access'] ) {
				if ( isset( $_COOKIE['access_store_id'] ) && ! empty( $_COOKIE['access_store_id'] ) ) {
					$access_store_id      = sanitize_text_field( $_COOKIE['access_store_id'] );
					$request = (array) json_decode( $request->get_body() );
					$user_email = $request['billing_address']->email;
					if ( isset( $request['customer_password'] ) && ! empty( $request['customer_password'] ) ) {
						update_option( $user_email, $access_store_id );
					}
				}
			}
		}

		public function update_user_role_based_on_email( $user_id ) {
			$settings = get_option( 'wwp_wholesale_pricing_options', true );
			if ( 'yes' == $settings['restrict_store_access'] ) {
				if ( isset( $_COOKIE['access_store_id'] ) && ! empty( $_COOKIE['access_store_id'] ) ) {
					$wholesale_user_roles = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
					$u = new WP_User( $user_id );
					if ( ! $u ) {
						return;
					}
					$email = $u->user_email;
					$access_store_id = get_option( $email );
					foreach ( $wholesale_user_roles as  $value ) {
						if ( $value->term_id == $access_store_id ) {
							$user_role_set = $value->slug;
						}
					}
					$wp_roles = new WP_Roles();
					$names    = $wp_roles->get_names();
					foreach ( $names as $key => $value ) {
						$u->remove_role( $key );
					}
					$u->add_role( $user_role_set );
					delete_option( $email ); 
					unset( $_COOKIE['access_store_id'] );
					setcookie( 'access_store_id', null, 0, '/' );
				}
			}
		}
		
		public function wc_no_products_found() {
		}
		public function wwp_registration_end() { 
			?> 
			<script>
				jQuery('#billing_country').trigger('change');
			</script>
			<?php 
		} 
		public function wholeslaer_acess_store() {
			$settings = get_option( 'wwp_wholesale_pricing_options', true );
			if ( isset( $settings['restrict_store_access'] ) && 'yes' == $settings['restrict_store_access'] ) {
				if ( 'yes' == $settings['restrict_store_access'] && isset( $_COOKIE['access_store_id'] ) && ! empty( $_COOKIE['access_store_id'] ) && ! is_user_logged_in() ) {
					
					/**
					* Hooks
					*
					* @since 3.0
					*/
					$back_to_retailer_form = apply_filters( 'back_to_retailer_form_text', 'You are accessing this store as a wholesaler <input type="submit" name="back_to_retailer" value="Exit"/>' ); 
					?>
						<form class="woocommerce-form back_to_retailer_form" method="post">
							<div id="wholeslaer_acess"><?php echo wp_kses( $back_to_retailer_form, shapeSpace_allowed_html() ); ?></div>
						</form>
					<?php 
				}
				if ( ( is_shop() || is_account_page() ) && ! is_user_logged_in() && 'yes' == $settings['restrict_store_access'] && ! isset( $_COOKIE['access_store_id'] ) ) {
					remove_action( 'woocommerce_no_products_found', 'wc_no_products_found', 10 );
					/**
					* Hooks
					*
					* @since 3.0
					*/
					$login = apply_filters( 'stroe_restict_login_text', '' );
					wc_add_notice( __( $settings['restrict_store_access_message'] . $login, 'woocommerce-wholesale-pricing' ), 'notice' );
					// wc_print_notices();
				}
			}
		}
		public function no_wordpress_errors( $error ) {
			$settings = get_option( 'wwp_wholesale_pricing_options', true );
			if ( isset( $settings['restrict_store_access'] ) && 'yes' == $settings['restrict_store_access'] ) {
				$post = wwp_get_post_data( '' );
				if ( isset( $post['access_store_pass'] ) && ! empty( $post['access_store_pass'] ) ) {
					return;
				}
			}

			return $error;
		}

		public function wwp_checkout_update_customer( $customer, $data ) {

			$settings = get_option( 'wwp_wholesale_pricing_options', true );

			if ( 'yes' == $settings['restrict_store_access'] ) {

				if ( isset( $_COOKIE['access_store_id'] ) && ! empty( $_COOKIE['access_store_id'] ) ) {

					$access_store_id      = sanitize_text_field( $_COOKIE['access_store_id'] );
					$user_id              = $customer->get_id();
					$wholesale_user_roles = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );

					$u = new WP_User( $user_id );

					foreach ( $wholesale_user_roles as  $value ) {
						if ( $value->term_id == $access_store_id ) {
							$user_role_set = $value->slug;
						}
					}
					$wp_roles = new WP_Roles();
					$names    = $wp_roles->get_names();
					foreach ( $names as $key => $value ) {
						$u->remove_role( $key );
					}
					$u->add_role( $user_role_set );
					unset( $_COOKIE['access_store_id'] );
					setcookie( 'access_store_id', null, 0, '/' );

				}
			}
		}

		public function action_woocommerce_login_form_end() {
			$settings = get_option( 'wwp_wholesale_pricing_options', true );
			$post     = wwp_get_post_data( '' );
			if ( isset( $settings['restrict_store_access'] ) && 'yes' == $settings['restrict_store_access'] && ! isset( $_COOKIE['access_store_id'] ) ) {
				?>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="access_store"><?php esc_html_e( 'Enter the password to access wholesale store', 'woocommerce-wholesale-pricing' ); ?><span class="required">*</span></label>
				<input type="password" class="woocommerce-Input--text input-text" name="access_store_pass" id="access_store_pass" > 
				<button type="submit" class="woocommerce-button button woocommerce-form-access_store" name="access_store" value="Log in">Enter</button>
				</p>
				<?php
				if ( 'yes' == $settings['enable_registration_page'] ) {
					$page_id   = $settings['registration_page'];
					$page_link = get_permalink( $page_id );
					
					/**
					* Hooks
					*
					* @since 3.0
					*/
					echo '<a href="' . esc_url( $page_link ) . '">' . esc_html( apply_filters( 'wwp_wholesale_registration_text', esc_html__( 'Or Want to Register as a Wholesaler?', 'woocommerce-wholesale-pricing' ) ) ) . '</a>';
				}
			}
		}

		public function wholesale_registrattion() {
			$this->registration_process();
		}

		public function registration_process() {

			if ( isset( $_POST['wwp_register'] ) ) {
				if ( isset( $_POST['wwp_wholesale_registrattion_nonce'] ) && wp_verify_nonce( wc_clean( $_POST['wwp_wholesale_registrattion_nonce'] ), 'wwp_wholesale_registrattion_nonce' ) ) {
					$this->errors = $this->wwp_register_wholesaler();
				} else {
					wp_die( esc_html__( 'Security check', 'wholesale-for-woocommerce' ) );
				}
			}
		}

		public function hook_javascript() {
			wp_enqueue_script('address-i18n');
			wp_enqueue_script('wc-country-select');
			wp_enqueue_script( 'wwp_formrender', plugin_dir_url( __DIR__ ) . 'assets/js/formbuilder/form-render.min.js', array(), '1.0.0' );
		}

		public function wwp_wholesale_registeration() {
			$settings = get_option( 'wwp_wholesale_pricing_options', true );
			if ( isset( $settings['enable_registration_page'] ) && 'yes' == $settings['enable_registration_page'] ) {
				$page_id   = $settings['registration_page'];
				$page_link = get_permalink( $page_id );
				
				/**
				* Hooks
				*
				* @since 3.0
				*/
				echo '<a href="' . esc_url( $page_link ) . '">' . esc_html( apply_filters( 'wwp_wholesale_registration_text', esc_html__( 'Or Want to Register as a Wholesaler?', 'woocommerce-wholesale-pricing' ) ) ) . '</a>';
			}
		}

		public function wwp_registration_form() {

			$settings = get_option( 'wwp_wholesale_pricing_options', true );
			if ( class_exists('WC_USER_REGISTRATION') && isset($settings['advance_registration_form']) && 'yes' == $settings['advance_registration_form'] ) {
				
				echo do_shortcode( '[wc-user-registration-page]' );
				return;
			}
			
			if ( ! is_admin() && is_user_logged_in() ) {
			
				/**
				* Hooks
				*
				* @since 3.0
				*/
				do_action( 'user_already_registered', $this );

				/**
				 * Filter Hooks
				 * CT-478 custom work
				 * Hide default message
				 *
				 * @since 2.6
				 */
				$show_msg = apply_filters( 'wwp_user_is_register', true );

				if ( $show_msg ) {
					return esc_html__( 'You are already registered!', 'woocommerce-wholesale-pricing' );
				} else {
					/**
					 * Filter Hooks
					 * CT-478 custom work
					 * show any custom message
					 *
					 * @since 2.6
					 */
					return apply_filters( 'wwp_user_is_already_register', '' );
				}
			}
			global $woocommerce;
			$countries_obj         = new WC_Countries();
			$countries             = $countries_obj->__get( 'countries' );
			$default_country       = $countries_obj->get_base_country();
			$default_county_states = $countries_obj->get_states( $default_country );
			$errors                = array();
			ob_start();
			if ( isset( $_POST['wwp_register'] ) ) {
				if ( isset( $_POST['wwp_wholesale_registrattion_nonce'] ) && wp_verify_nonce( wc_clean( $_POST['wwp_wholesale_registrattion_nonce'] ), 'wwp_wholesale_registrattion_nonce' ) ) {
					$errors = $this->errors;
				} else {
					wp_die( esc_html__( 'Security check', 'wholesale-for-woocommerce' ) );
				}
			}
			$username = '';
			$email    = '';
			$fname    = '';
			$lname    = '';
			$company  = '';
			$addr1    = '';
			if ( ! empty( $errors ) ) {
				echo '<ul class="woocommerce-error" role="alert">';
				foreach ( (array) $errors as $key => $error ) {
					echo '<li>' . wp_kses_post( $error ) . '</li>';
				}
				echo '</ul>';
			}
			$username                               = isset( $_POST['wwp_wholesaler_username'] ) ? wc_clean( $_POST['wwp_wholesaler_username'] ) : '';
			$email                                  = isset( $_POST['wwp_wholesaler_email'] ) ? wc_clean( $_POST['wwp_wholesaler_email'] ) : '';
			$fname                                  = isset( $_POST['wwp_wholesaler_fname'] ) ? wc_clean( $_POST['wwp_wholesaler_fname'] ) : '';
			$lname                                  = isset( $_POST['wwp_wholesaler_lname'] ) ? wc_clean( $_POST['wwp_wholesaler_lname'] ) : '';
			$company                                = isset( $_POST['wwp_wholesaler_company'] ) ? wc_clean( $_POST['wwp_wholesaler_company'] ) : '';
			$addr1                                  = isset( $_POST['wwp_wholesaler_address_line_1'] ) ? wc_clean( $_POST['wwp_wholesaler_address_line_1'] ) : '';
			$wwp_wholesaler_address_line_2          = isset( $_POST['wwp_wholesaler_address_line_2'] ) ? wc_clean( $_POST['wwp_wholesaler_address_line_2'] ) : '';
			$wwp_wholesaler_city                    = isset( $_POST['wwp_wholesaler_city'] ) ? wc_clean( $_POST['wwp_wholesaler_city'] ) : '';
			$wwp_wholesaler_post_code               = isset( $_POST['wwp_wholesaler_post_code'] ) ? wc_clean( $_POST['wwp_wholesaler_post_code'] ) : '';
			$billing_country                        = isset( $_POST['billing_country'] ) ? wc_clean( $_POST['billing_country'] ) : '';
			$wwp_wholesaler_state                   = isset( $_POST['wwp_wholesaler_state'] ) ? wc_clean( $_POST['wwp_wholesaler_state'] ) : '';
			$wwp_wholesaler_phone                   = isset( $_POST['wwp_wholesaler_phone'] ) ? wc_clean( $_POST['wwp_wholesaler_phone'] ) : '';
			$wwp_wholesaler_shipping_fname          = isset( $_POST['wwp_wholesaler_shipping_fname'] ) ? wc_clean( $_POST['wwp_wholesaler_shipping_fname'] ) : '';
			$wwp_wholesaler_shipping_lname          = isset( $_POST['wwp_wholesaler_shipping_lname'] ) ? wc_clean( $_POST['wwp_wholesaler_shipping_lname'] ) : '';
			$wwp_wholesaler_shipping_company        = isset( $_POST['wwp_wholesaler_shipping_company'] ) ? wc_clean( $_POST['wwp_wholesaler_shipping_company'] ) : '';
			$wwp_wholesaler_shipping_address_line_1 = isset( $_POST['wwp_wholesaler_shipping_address_line_1'] ) ? wc_clean( $_POST['wwp_wholesaler_shipping_address_line_1'] ) : '';
			$wwp_wholesaler_shipping_address_line_2 = isset( $_POST['wwp_wholesaler_shipping_address_line_2'] ) ? wc_clean( $_POST['wwp_wholesaler_shipping_address_line_2'] ) : '';
			$wwp_wholesaler_shipping_city           = isset( $_POST['wwp_wholesaler_shipping_city'] ) ? wc_clean( $_POST['wwp_wholesaler_shipping_city'] ) : '';
			$wwp_wholesaler_shipping_post_code      = isset( $_POST['wwp_wholesaler_shipping_post_code'] ) ? wc_clean( $_POST['wwp_wholesaler_shipping_post_code'] ) : '';
			$wwp_wholesaler_shipping_post_code      = isset( $_POST['wwp_wholesaler_shipping_post_code'] ) ? wc_clean( $_POST['wwp_wholesaler_shipping_post_code'] ) : '';
			$wwp_wholesaler_shipping_state          = isset( $_POST['shipping_state'] ) ? wc_clean( $_POST['shipping_state'] ) : '';

			$wwp_wholesaler_tax_id = isset( $_POST['wwp_wholesaler_tax_id'] ) ? wc_clean( $_POST['wwp_wholesaler_tax_id'] ) : '';
			if ( isset( $_POST['billing_country'] ) ) {
				$default_country = wc_clean( $_POST['billing_country'] );
			}
			if ( isset( $_POST['shipping_country'] ) ) {
				$default_country = wc_clean( $_POST['shipping_country'] );
			}

			$settings      = get_option( 'wwp_wholesale_pricing_options', true );
			$registrations = get_option( 'wwp_wholesale_registration_options' );
			
			/**
			* Hooks
			*
			* @since 3.0
			*/
			apply_filters( 'wwp_wholesale_custom_filters_css', wwp_wholesale_css( $settings ) );
			$registeration = esc_html__( 'Registration', 'woocommerce-wholesale-pricing' );
			?>
			<div class="wwp_wholesaler_registration">
				<h2 class="wholesaler-registration">
					<?php 
						/**
						* Hooks
						*
						* @since 2.6
						*/
						echo esc_html( apply_filters( 'change_wwp_register_text', $registeration ) ); 
					?>
				</h2>
					<?php if ( function_exists( 'wc_print_notices' ) ) : ?>
						<?php wc_print_notices(); ?>
					<?php endif; ?>
				<form method="post" class="wwp_wholesaler_registration_form" action="" enctype="multipart/form-data">
					<?php
						/*
						* custom work CT-478
						* Tim
						* */
					if ( file_exists( WP_PLUGIN_DIR . '/wwp-custom-plugin/wholesale-for-woocommerce/registration-fields.php' ) ) {
						include WP_PLUGIN_DIR . '/wwp-custom-plugin/wholesale-for-woocommerce/registration-fields.php';
					} else if ( file_exists(get_stylesheet_directory() . '/wholesale-for-woocommerce/registration-fields.php') ) {
						include get_stylesheet_directory() . '/wholesale-for-woocommerce/registration-fields.php';
					} else {
						include WWP_PLUGIN_PATH . 'inc/template/registration-fields.php';
					}
					?>
				</form>
			</div>
			<?php
			
			/**
			* Hooks
			*
			* @since 3.0
			*/
			do_action( 'wwp_registration_end', $this );
			return ob_get_clean();
		}
		public function wwp_register_wholesaler() {
			$posted_data = array();
			/**
			* Hooks
			*
			* @since 3.0
			*/
			if ( apply_filters( 'wwp_register_fields_validation', false, $this ) ) {
				/**
				* Hooks
				*
				* @since 3.0
				*/
				wc_print_notice( esc_html__( apply_filters( 'wwp_fields_validation_error_msg', 'Filed Must be required.'), 'woocommerce-wholesale-pricing' ), 'error' );
				return;
			}
			
			if ( ! isset( $_POST['wwp_wholesale_registrattion_nonce'] ) || ( isset( $_POST['wwp_wholesale_registrattion_nonce'] ) && ! wp_verify_nonce( wc_clean( $_POST['wwp_wholesale_registrattion_nonce'] ), 'wwp_wholesale_registrattion_nonce' ) ) ) {
				wp_die( esc_html__( 'Security check', 'wholesale-for-woocommerce' ) );
			}

			$post           = $_POST;
			
			$errors         = array();
			/***
			 * Hooks Filter hook process registration form
			 * 
			 * @since 2.4.0
			 * 
			 * $args array $post
			 */
			$errors         = ( array ) apply_filters( 'wwp_process_registration_form', $errors, $post );
			if ( is_array( $errors ) && count( $errors ) > 0 ) {
				foreach ( $errors as $error ) {
					wc_add_notice( $error, 'error' );
				}
				return false;
			}

			$settings       = get_option( 'wwp_wholesale_pricing_options', true );
			$registrations  = get_option( 'wwp_wholesale_registration_options' );

			if ( isset( $registrations['extra_pass_field'] ) && 'yes' == $registrations['extra_pass_field'] ) {
				if ( isset( $post['wwp_wholesaler_password'] ) && isset( $post['password2'] ) ) {
					if ( strcmp( $post['wwp_wholesaler_password'], $post['password2'] ) !== 0 ) {
						wc_add_notice( esc_html__( 'Passwords do not match.', 'woocommerce-wholesale-pricing' ), 'error' );
						return false;
					}
				}
			}
			if ( empty( $post['wwp_wholesaler_email'] ) ) {
				wc_print_notice( esc_html__( 'Email Required', 'woocommerce-wholesale-pricing' ), 'error' );
				return false;
			}
			
			// first name if required
			if ( isset( $registrations['enable_billing_first_name'], $post['wwp_wholesaler_fname'], $registrations['required_billing_first_name'] ) && 'yes' == $registrations['enable_billing_first_name'] && empty( $post['wwp_wholesaler_fname'] ) && 'yes' == $registrations['required_billing_first_name'] ) {
				// translators: %s first name if required
				wc_print_notice( sprintf( esc_html__( '%s is a required field.', 'woocommerce-wholesale-pricing' ), $registrations['billing_first_name'] ), 'error' );
				return false;
			}

			// last name if required
			if ( isset( $registrations['enable_billing_last_name'], $post['wwp_wholesaler_lname'], $registrations['required_billing_last_name'] ) && 'yes' == $registrations['enable_billing_last_name'] && empty( $post['wwp_wholesaler_lname'] ) && 'yes' == $registrations['required_billing_last_name'] ) {
				// translators: %s last name if required
				wc_print_notice( sprintf( esc_html__( '%s is a required field.', 'woocommerce-wholesale-pricing' ), $registrations['billing_last_name'] ), 'error' );
				return false;
			}

			// company if required
			if ( isset( $registrations['enable_billing_company'], $post['wwp_wholesaler_company'], $registrations['required_billing_company'] ) && 'yes' == $registrations['enable_billing_company'] && empty( $post['wwp_wholesaler_company'] ) && 'yes' == $registrations['required_billing_company'] ) {
				// translators: %s company if required
				wc_print_notice( sprintf( esc_html__( '%s is a required field.', 'woocommerce-wholesale-pricing' ), $registrations['billing_company'] ), 'error' );
				return false;
			}

			// address_1 if required
			if ( isset( $registrations['enable_billing_address_1'], $post['wwp_wholesaler_address_line_1'], $registrations['required_billing_address_1'] ) && 'yes' == $registrations['enable_billing_address_1'] && empty( $post['wwp_wholesaler_address_line_1'] ) && 'yes' == $registrations['required_billing_address_1'] ) {
				// translators: %s address_1 if required
				wc_print_notice( sprintf( esc_html__( '%s is a required field.', 'woocommerce-wholesale-pricing' ), $registrations['billing_address_1'] ), 'error' );
				return false;
			}

			// address_2 if required
			if ( isset( $registrations['enable_billing_address_2'], $post['wwp_wholesaler_address_line_2'], $registrations['required_billing_address_2'] ) && 'yes' == $registrations['enable_billing_address_2'] && empty( $post['wwp_wholesaler_address_line_2'] ) && 'yes' == $registrations['required_billing_address_2'] ) {
				// translators: %s address_2 if required
				wc_print_notice( sprintf( esc_html__( '%s is a required field.', 'woocommerce-wholesale-pricing' ), $registrations['billing_address_2'] ), 'error' );
				return false;
			}

			// City if required
			if ( isset( $registrations['enable_billing_city'], $post['wwp_wholesaler_city'], $registrations['required_billing_city'] ) && 'yes' == $registrations['enable_billing_city'] && empty( $post['wwp_wholesaler_city'] ) && 'yes' == $registrations['required_billing_city'] ) {
				// translators: %s city if required
				wc_print_notice( sprintf( esc_html__( '%s is a required field.', 'woocommerce-wholesale-pricing' ), $registrations['billing_city'] ), 'error' );
				return false;
			}

			// postal code if required
			if ( isset( $registrations['enable_billing_post_code'], $post['wwp_wholesaler_post_code'], $registrations['required_billing_post_code'] ) && 'yes' == $registrations['enable_billing_post_code'] && empty( $post['wwp_wholesaler_post_code'] ) && 'yes' == $registrations['required_billing_post_code'] ) {
				// translators: %s postal code if required
				wc_print_notice( sprintf( esc_html__( '%s is a required field.', 'woocommerce-wholesale-pricing' ), $registrations['billing_post_code'] ), 'error' );
				return false;
			}

			// Phone if required
			if ( isset( $registrations['enable_billing_phone'], $post['wwp_wholesaler_phone'], $registrations['required_billing_phone'] ) && 'yes' == $registrations['enable_billing_phone'] && empty( $post['wwp_wholesaler_phone'] ) && 'yes' == $registrations['required_billing_phone'] ) {
				// translators: %s phone if required
				wc_print_notice( sprintf( esc_html__( '%s is a required field.', 'woocommerce-wholesale-pricing' ), $registrations['billing_phone'] ), 'error' );
				return false;
			}
			
			$form_builder_fields = json_decode( get_option( 'wwp_save_form' ) );
			if ( isset( $post['wwp_form_data_json'] ) ) {
				$posted_data        = json_decode(stripslashes($post['wwp_form_data_json']));
			}

			if ( ! empty( $form_builder_fields ) && isset( $registrations['display_fields_registration'] ) && 'yes' == $registrations['display_fields_registration'] ) {

				foreach ( $form_builder_fields as $index => $field ) {
					$field = (array) $field;

					if ( isset( $posted_data[$index] ) && isset($posted_data[$index]->userData) ) {
						if ( is_array( $posted_data[$index]->userData ) ) {
							$posted_field = reset($posted_data[$index]->userData);
						} else {
							$posted_field = $posted_data[$index]->userData;
						}
					}

					if (  isset( $field['required'] ) && true == $field['required'] && empty( $posted_field ) ) {
						/* translators: %s is a required field */
						wc_print_notice( sprintf( esc_html__( '%s is a required field.', 'woocommerce-wholesale-pricing' ), $field['label'] ), 'error' );
						return false;
					}
				}
			}
			
			$role = get_option( 'default_role' );
			if ( ! isset( $settings['disable_auto_role'] ) || ( isset( $settings['disable_auto_role'] ) && 'no' == $settings['disable_auto_role'] ) ) {

				if ( isset( $settings['wholesale_role'] ) && 'single' == $settings['wholesale_role'] ) {

					$role = 'default_wholesaler';

				} elseif ( isset( $settings['default_multipe_wholesale_roles'] ) ) {
					$role = $settings['default_multipe_wholesale_roles'];
					if ( isset( $_POST['wwp_wholesale_role_request'] ) ) {
						$wwp_wholesaler_role = wc_clean( $_POST['wwp_wholesale_role_request'] );
						$role = $wwp_wholesaler_role;
					}
				}
			}
			
			if ( isset( $registrations['required_billing_country'], $registrations['enable_billing_country'] ) &&  $registrations['enable_billing_country'] && empty( $post['billing_country'] ) ) {
				wc_add_notice( sprintf( '<strong>%s </strong> is a required field.', $registrations['billing_countries'] ), 'error' );
				return false;
			}

			if ( isset( $registrations['required_billing_state'], $registrations['enable_billing_state'] ) && 'yes' ==  $registrations['required_billing_state'] && empty( $post['billing_state'] ) && ! empty( $post['wwp_state_type'] ) ) {
				$valid_states = '';
				if ( 'select' == $post['wwp_state_type'] ) {
					$countries_state = new WC_Countries();
					$valid_states = $countries_state->get_states( $post['billing_country'] );
				}
				
				if ( ! empty( $valid_states ) && in_array( $post['billing_state'], $valid_states ) ) {
					wc_add_notice( sprintf( '<strong>%s </strong> is a required field.', $registrations['billing_state'] ), 'error' );
				} else {
					wc_add_notice( sprintf( '<strong>%s </strong> is a required field.', $registrations['billing_state'] ), 'error' );
				}
				return false;
			}

			if ( isset( $_POST['wwp_wholesaler_copy_billing_address'] ) && 'yes' != wc_clean( $_POST['wwp_wholesaler_copy_billing_address'] ) ) {
				if ( isset( $registrations['required_shipping_country'], $registrations['enable_shipping_country'] ) && 'yes' ==  $registrations['required_shipping_country'] && empty( $post['shipping_country'] ) ) {
					wc_add_notice( sprintf( '<strong>%s </strong> is a required field.', $registrations['shipping_countries'] ), 'error' );
					return false;
				}

				if ( isset( $registrations['required_shipping_state'], $registrations['enable_shipping_state'] ) && 'yes' ==  $registrations['required_shipping_state'] && empty( $post['shipping_state'] ) ) {
					wc_add_notice( sprintf( '<strong>%s </strong> is a required field.', $registrations['shipping_state'] ), 'error' );
					return false;
				}
			}

			$userdata = array(
				'first_name' => isset( $_POST['wwp_wholesaler_fname'] ) ? wc_clean( $_POST['wwp_wholesaler_fname'] ) : '',
				'last_name'  => isset( $_POST['wwp_wholesaler_lname'] ) ? wc_clean( $_POST['wwp_wholesaler_lname'] ) : '',
				'user_login' => isset( $_POST['wwp_wholesaler_username'] ) ? wc_clean( $_POST['wwp_wholesaler_username'] ) : '',
				'user_email' => isset( $_POST['wwp_wholesaler_email'] ) ? wc_clean( $_POST['wwp_wholesaler_email'] ) : '',
				'user_pass'  => isset( $_POST['wwp_wholesaler_password'] ) ? wc_clean( $_POST['wwp_wholesaler_password'] ) : '',
				'role'       => $role,
			);
			$user_id  = wp_insert_user( $userdata );
			if ( ! is_wp_error( $user_id ) ) {

				/**
				 * Action
				 * 
				 * Added for custom work CT-478
				 *
				 * @since 2.5
				 */
				do_action( 'wwp_insert_user_data', $user_id, $post );
			
				$customer = new WC_Customer( $user_id );
				
				// Form builder fields udate in user meta
				form_builder_update_user_meta( $user_id, $post );

				if ( isset( $_POST['wwp_wholesaler_fname'] ) ) {
					$billing_first_name = wc_clean( $_POST['wwp_wholesaler_fname'] );
					//update_user_meta( $user_id, 'billing_first_name', $billing_first_name );
					$customer->set_billing_first_name( $billing_first_name );
				}
				if ( isset( $_POST['wwp_wholesaler_lname'] ) ) {
					$billing_last_name = wc_clean( $_POST['wwp_wholesaler_lname'] );
					//update_user_meta( $user_id, 'billing_last_name', $billing_last_name );
					$customer->set_billing_last_name( $billing_last_name );
				}
				if ( isset( $_POST['wwp_wholesaler_company'] ) ) {
					$billing_company = wc_clean( $_POST['wwp_wholesaler_company'] );
					//update_user_meta( $user_id, 'billing_company', $billing_company );
					$customer->set_billing_company( $billing_company );
				}
				if ( isset( $_POST['wwp_wholesaler_address_line_1'] ) ) {
					$billing_address_1 = wc_clean( $_POST['wwp_wholesaler_address_line_1'] );
					//update_user_meta( $user_id, 'billing_address_1', $billing_address_1 );
					$customer->set_billing_address_1( $billing_address_1 );
				}
				if ( isset( $_POST['wwp_wholesaler_address_line_2'] ) ) {
					$billing_address_2 = wc_clean( $_POST['wwp_wholesaler_address_line_2'] );
					///update_user_meta( $user_id, 'billing_address_2', $billing_address_2 );
					$customer->set_billing_address_2( $billing_address_2 );
				}
				if ( isset( $_POST['wwp_wholesaler_city'] ) ) {
					$billing_city = wc_clean( $_POST['wwp_wholesaler_city'] );
					//update_user_meta( $user_id, 'billing_city', $billing_city );
					$customer->set_billing_city( $billing_city );
				}
				if ( isset( $_POST['billing_state'] ) ) {
					$billing_state = wc_clean( $_POST['billing_state'] );
					//update_user_meta( $user_id, 'billing_state', $billing_state );
					$customer->set_billing_state( $billing_state );
				}
				if ( isset( $_POST['wwp_wholesaler_post_code'] ) ) {
					$billing_postcode = wc_clean( $_POST['wwp_wholesaler_post_code'] );
					//update_user_meta( $user_id, 'billing_postcode', $billing_postcode );
					$customer->set_billing_postcode( $billing_postcode );
				}
				if ( isset( $_POST['billing_country'] ) ) {
					$billing_country = wc_clean( $_POST['billing_country'] );
					//update_user_meta( $user_id, 'billing_country', $billing_country );
					$customer->set_billing_country( $billing_country );
				}
				if ( isset( $_POST['wwp_wholesaler_phone'] ) ) {
					$billing_phone = wc_clean( $_POST['wwp_wholesaler_phone'] );
					//update_user_meta( $user_id, 'billing_phone', $billing_phone );
					$customer->set_billing_phone( $billing_phone );
				}
				if ( isset( $_POST['wwp_wholesaler_tax_id'] ) ) {
					$wwp_wholesaler_tax_id = wc_clean( $_POST['wwp_wholesaler_tax_id'] );
					//update_user_meta( $user_id, 'wwp_wholesaler_tax_id', $wwp_wholesaler_tax_id );
					$customer->update_meta_data( 'wwp_wholesaler_tax_id', $wwp_wholesaler_tax_id );
				}
				if ( isset( $_POST['wwp_wholesale_role_request'] ) ) {
					$wwp_wholesale_role_request = wc_clean( $_POST['wwp_wholesale_role_request'] );
					//update_user_meta( $user_id, 'wwp_wholesale_role_request', $wwp_wholesale_role_request );
					$customer->update_meta_data( 'wwp_wholesale_role_request', $wwp_wholesale_role_request );
				}
				if ( isset( $_POST['wwp_custom_field_1'] ) ) {
					$wwp_custom_field_1 = wc_clean( $_POST['wwp_custom_field_1'] );
					//update_user_meta( $user_id, 'wwp_custom_field_1', $custom_field );
					$customer->update_meta_data( 'wwp_custom_field_1', $wwp_custom_field_1 );
				}
				if ( isset( $_POST['wwp_custom_field_2'] ) ) {
					$wwp_custom_field_2 = wc_clean( $_POST['wwp_custom_field_2'] );
					//update_user_meta( $user_id, 'wwp_custom_field_2', $custom_field );
					$customer->update_meta_data( 'wwp_custom_field_2', $wwp_custom_field_2 );
				}
				if ( isset( $_POST['wwp_custom_field_3'] ) ) {
					$wwp_custom_field_3 = wc_clean( $_POST['wwp_custom_field_3'] );
					//update_user_meta( $user_id, 'wwp_custom_field_3', $custom_field );
					$customer->update_meta_data( 'wwp_custom_field_3', $wwp_custom_field_3 );
				}
				if ( isset( $_POST['wwp_custom_field_4'] ) ) {
					$wwp_custom_field_4 = wc_clean( $_POST['wwp_custom_field_4'] );
					//update_user_meta( $user_id, 'wwp_custom_field_4', $custom_field );
					$customer->update_meta_data( 'wwp_custom_field_4', $wwp_custom_field_4 );
				}
				if ( isset( $_POST['wwp_custom_field_5'] ) ) {
					$wwp_custom_field_5 = wc_clean( $_POST['wwp_custom_field_5'] );
					//update_user_meta( $user_id, 'wwp_custom_field_5', $custom_field );
					$customer->update_meta_data( 'wwp_custom_field_5', $wwp_custom_field_5 );
				}

				if ( isset( $_POST['wwp_form_data_json'] ) ) {
					$wwp_form_data_json = wc_clean( $_POST['wwp_form_data_json'] );
					//update_user_meta( $user_id, 'wwp_form_data_json', $wwp_form_data_json );
					$customer->update_meta_data( 'wwp_form_data_json', $wwp_form_data_json );
				}

				if ( ! empty( $_FILES['wwp_wholesaler_file_upload'] ) ) {
					if ( isset($registrations['multiple_file_upload']) && 'yes' == $registrations['multiple_file_upload'] ) {
						/**
						* Hooks
						*
						* @since 3.0
						*/
						do_action( 'wwp_wholesaler_file_upload_multiple_handle', $_FILES, $user_id );
					} else {
						require_once ABSPATH . 'wp-admin/includes/file.php';
						require_once ABSPATH . 'wp-admin/includes/image.php';
						require_once ABSPATH . 'wp-admin/includes/media.php';
						$attach_id = media_handle_upload( 'wwp_wholesaler_file_upload', $user_id );
						//update_user_meta( $user_id, 'wwp_wholesaler_file_upload', $attach_id );
						$customer->update_meta_data( 'wwp_wholesaler_file_upload', $attach_id );
						
					}
				}

				if ( isset( $_POST['wwp_wholesaler_copy_billing_address'] ) ) {
					$wwp_wholesaler_copy_billing_address = wc_clean( $_POST['wwp_wholesaler_copy_billing_address'] );
				}
				if ( isset( $wwp_wholesaler_copy_billing_address ) ) {
					if ( isset( $_POST['wwp_wholesaler_fname'] ) ) {
						$billing_first_name = wc_clean( $_POST['wwp_wholesaler_fname'] );
						//update_user_meta( $user_id, 'shipping_first_name', $billing_first_name );
						$customer->set_shipping_first_name( $billing_first_name );
					}
					if ( isset( $_POST['wwp_wholesaler_lname'] ) ) {
						$billing_last_name = wc_clean( $_POST['wwp_wholesaler_lname'] );
						//update_user_meta( $user_id, 'shipping_last_name', $billing_last_name );
						$customer->set_shipping_last_name( $billing_last_name );
					}
					if ( isset( $_POST['wwp_wholesaler_company'] ) ) {
						$billing_company = wc_clean( $_POST['wwp_wholesaler_company'] );
						//update_user_meta( $user_id, 'shipping_company', $billing_company );
						$customer->set_shipping_company( $billing_company );
					}
					if ( isset( $_POST['wwp_wholesaler_address_line_1'] ) ) {
						$shipping_address_1 = wc_clean( $_POST['wwp_wholesaler_address_line_1'] );
						//update_user_meta( $user_id, 'shipping_address_1', $shipping_address_1 );
						$customer->set_shipping_address_1( $shipping_address_1 );
					}
					if ( isset( $_POST['wwp_wholesaler_address_line_2'] ) ) {
						$shipping_address_2 = wc_clean( $_POST['wwp_wholesaler_address_line_2'] );
						//update_user_meta( $user_id, 'shipping_address_2', $shipping_address_2 );
						$customer->set_shipping_address_2( $shipping_address_2 );
					}
					if ( isset( $_POST['wwp_wholesaler_city'] ) ) {
						$shipping_city = wc_clean( $_POST['wwp_wholesaler_city'] );
						//update_user_meta( $user_id, 'shipping_city', $shipping_city );
						$customer->set_shipping_city( $shipping_city );
					}
					if ( isset( $_POST['billing_state'] ) ) {
						$billing_state = wc_clean( $_POST['billing_state'] );
						//update_user_meta( $user_id, 'shipping_state', $billing_state );
						$customer->set_shipping_state( $billing_state );
					}
					if ( isset( $_POST['wwp_wholesaler_post_code'] ) ) {
						$shipping_postcode = wc_clean( $_POST['wwp_wholesaler_post_code'] );
						//update_user_meta( $user_id, 'shipping_postcode', $shipping_postcode );
						$customer->set_shipping_postcode( $shipping_postcode );
					}
					if ( isset( $_POST['billing_country'] ) ) {
						$billing_country = wc_clean( $_POST['billing_country'] );
						//update_user_meta( $user_id, 'shipping_country', $billing_country );
						$customer->set_shipping_country( $billing_country );
					}
					if ( isset( $_POST['billing_phone'] ) ) {
						$billing_phone = wc_clean( $_POST['billing_phone'] );
						//update_user_meta( $user_id, 'shipping_country', $billing_country );
						$customer->set_shipping_phone( $billing_phone );
					}
				} else {
					if ( isset( $_POST['wwp_wholesaler_shipping_fname'] ) ) {
						$shipping_first_name = wc_clean( $_POST['wwp_wholesaler_shipping_fname'] );
						//update_user_meta( $user_id, 'shipping_first_name', $shipping_first_name );
						$customer->set_shipping_first_name( $shipping_first_name );
					}
					if ( isset( $_POST['wwp_wholesaler_shipping_lname'] ) ) {
						$shipping_last_name = wc_clean( $_POST['wwp_wholesaler_shipping_lname'] );
						//update_user_meta( $user_id, 'shipping_last_name', $shipping_last_name );
						$customer->set_shipping_last_name( $shipping_last_name );
					}
					if ( isset( $_POST['wwp_wholesaler_shipping_company'] ) ) {
						$shipping_company = wc_clean( $_POST['wwp_wholesaler_shipping_company'] );
						//update_user_meta( $user_id, 'shipping_company', $shipping_company );
						$customer->set_shipping_company( $shipping_company );
					}
					if ( isset( $_POST['wwp_wholesaler_shipping_address_line_1'] ) ) {
						$shipping_address_1 = wc_clean( $_POST['wwp_wholesaler_shipping_address_line_1'] );
						//update_user_meta( $user_id, 'shipping_address_1', $shipping_address_1 );
						$customer->set_shipping_address_1( $shipping_address_1 );
					}
					if ( isset( $_POST['wwp_wholesaler_shipping_address_line_2'] ) ) {
						$shipping_address_2 = wc_clean( $_POST['wwp_wholesaler_shipping_address_line_2'] );
						//update_user_meta( $user_id, 'shipping_address_2', $shipping_address_2 );
						$customer->set_shipping_address_2( $shipping_address_2 );
					}
					if ( isset( $_POST['wwp_wholesaler_shipping_city'] ) ) {
						$shipping_city = wc_clean( $_POST['wwp_wholesaler_shipping_city'] );
						//update_user_meta( $user_id, 'shipping_city', $shipping_city );
						$customer->set_shipping_city( $shipping_city );
					}
					if ( isset( $_POST['shipping_state'] ) ) {
						$shipping_state = wc_clean( $_POST['shipping_state'] );
						//update_user_meta( $user_id, 'shipping_state', $shipping_state );
						$customer->set_shipping_state( $shipping_state );
					}
					if ( isset( $_POST['wwp_wholesaler_shipping_post_code'] ) ) {
						$shipping_postcode = wc_clean( $_POST['wwp_wholesaler_shipping_post_code'] );
						//update_user_meta( $user_id, 'shipping_postcode', $shipping_postcode );
						$customer->set_shipping_postcode( $shipping_postcode );
					}
					if ( isset( $_POST['shipping_country'] ) ) {
						$shipping_country = wc_clean( $_POST['shipping_country'] );
						//update_user_meta( $user_id, 'shipping_country', $shipping_country );
						$customer->set_shipping_country( $shipping_country );
					}
				}
				$id = wp_insert_post(
					array(
						'post_type'   => 'wwp_requests',
						'post_title'  => isset( $_POST['wwp_wholesaler_username'] ) ? wc_clean( $_POST['wwp_wholesaler_username'] ) . ' - ' . esc_attr( $user_id ) : '',
						'post_status' => 'publish',
					)
				);
				if ( ! is_wp_error( $id ) ) {

					update_post_meta( $id, '_user_id', $user_id );
					// if ( !empty($role) && 'default_wholesaler' == $role) {
						  // $term_taxonomy_ids = wp_set_object_terms($id, 'default_wholesaler', 'wholesale_user_roles', true);
					// }
					if ( ! isset( $settings['disable_auto_role'] ) || ( isset( $settings['disable_auto_role'] ) && 'no' == $settings['disable_auto_role'] ) ) {
						update_post_meta( $id, '_user_status', 'active' );
						//update_user_meta( $user_id, '_user_status', 'active' );
						$customer->update_meta_data( '_user_status', 'active' );

						if ( isset( $settings['wholesale_role'] ) && 'single' == $settings['wholesale_role'] ) {

							wp_set_object_terms( $id, 'default_wholesaler', 'wholesale_user_roles', true );

						} elseif ( isset( $settings['default_multipe_wholesale_roles'] ) ) {
							if ( isset( $_POST['wwp_wholesale_role_request'] ) ) {
								$wwp_wholesaler_role = wc_clean( $_POST['wwp_wholesale_role_request'] );
								wp_set_object_terms( $id, $wwp_wholesaler_role , 'wholesale_user_roles', true );
							} else {
								wp_set_object_terms( $id, $settings['default_multipe_wholesale_roles'], 'wholesale_user_roles', true );
							}
						} else {
							wp_set_object_terms( $id, 'default_wholesaler', 'wholesale_user_roles', true );
						}

						if ( ! empty( $role ) ) {
						
							/**
							* Hooks
							*
							* @since 3.0
							*/
							do_action( 'wwp_wholesale_user_request_approved', $user_id );
							update_post_meta( $id, '_approval_notification', 'sent' );
						}
					} else {
						update_post_meta( $id, '_user_status', 'waiting' );
						if ( isset( $_POST['wwp_wholesale_role_request'] ) ) {
							$wwp_wholesaler_role = wc_clean( $_POST['wwp_wholesale_role_request'] );
							update_post_meta( $id, 'user_role_set', $wwp_wholesaler_role );
						} 
						//  update_user_meta( $user_id, '_user_status', 'waiting' );
						$customer->update_meta_data( '_user_status', 'waiting' );
					}
					
					$customer->save();
					/**
					* Hooks
					*
					* @since 3.0
					*/
					do_action( 'wwp_wholesale_new_request_submitted', $user_id );
					/**
					* Hooks
					*
					* @since 3.0
					*/
					do_action( 'wwp_wholesale_new_registered_request', $user_id );

				}
				// On success
				if ( ! is_wp_error( $user_id ) ) {
					/**
					* Hooks
					*
					* @since 3.0
					*/
					$notice = apply_filters( 'wwp_success_msg', esc_html__( 'You are Registered Successfully', 'woocommerce-wholesale-pricing' ) );
					wc_add_notice( esc_html__( $notice, 'woocommerce-wholesale-pricing' ), 'success' );
					$_POST = array();
				} else {
					/**
					* Hooks
					*
					* @since 3.0
					*/
					$notice = apply_filters( 'wwp_error_msg', esc_html__( $user_id->get_error_message(), 'woocommerce-wholesale-pricing' ) );
					wc_add_notice( esc_html__( $notice, 'woocommerce-wholesale-pricing' ), 'error' );
				}

				if ( isset( $settings['register_redirect'] ) && ! empty( get_permalink( $settings['register_redirect'] ) ) && ! is_wp_error( $user_id ) ) {
					// update_option( 'wwp_notice_register', 'yes' );
					$location = get_permalink( $settings['register_redirect'] );
					header( "Location: $location", true );
					exit;
				}
			} else {
				$errors[] = $user_id->get_error_message();
			}
			
			/**
			* Hooks
			*
			* @since 3.0
			*/
			return apply_filters( 'wwp_wholesale_registration_form_errors', $errors );
		}
	}
	new Wwp_Wholesale_Pricing_Registration();
}
