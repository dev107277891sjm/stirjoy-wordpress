<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( ! class_exists( 'Wwp_Wholesale_Pricing_Frontend' ) ) {

	class Wwp_Wholesale_Pricing_Frontend {

		public $exclude_ids = array();

		public $upgrade_account;
		
		public $requisition_list_url;

		public function __construct() { 
		
			// add_action('woocommerce_account_content', array($this, 'wwp_account_content_callback'));
			add_action( 'wp_enqueue_scripts', array( $this, 'wwp_script_style' ) );
			add_action( 'init', array( $this, 'wwp_hide_price_add_cart_not_logged_in' ) );
			add_filter( 'authenticate', array( $this, 'authenticate' ), 100, 2 );

			// Form Builder show on order page v 1.4
			add_action( 'woocommerce_after_order_notes', array( $this, 'action_woocommerce_after_order_notes' ), 10, 1 );
			add_action( 'woocommerce_checkout_create_order', array( $this, 'before_checkout_create_order' ), 20, 2 );

			// Tax Class assign user role
			add_filter('woocommerce_product_get_tax_class', array( $this, 'woocommerce_product_get_tax_class' ), 20, 2);
			add_filter('woocommerce_product_variation_get_tax_class', array( $this, 'woocommerce_product_get_tax_class' ), 20, 2);


			// Restrict products from non-wholesaler users. v 1.4
			add_action( 'pre_get_posts', array( $this, 'default_wholesaler_products_only' ), 999, 1 );
			add_filter( 'parse_query', array( $this, 'default_wholesaler_products_hide_from_blocks' ), 10, 1 );
			// //B2B Bundle support
			add_filter( 'wcpt_product_table_args', array( $this, 'wcpt_product_table_args' ), 10, 1 );
			
			// Checkout formbuilder fileds validation v 1.4
			add_action( 'woocommerce_after_checkout_validation', array( $this, 'wwp_after_checkout_validation' ), 10, 2 );
			add_filter( 'woocommerce_email_recipient_new_order', array( $this, 'wholesale_new_order_email_recipient' ), 10, 2 );
			add_action( 'woocommerce_cart_calculate_fees', array( $this, 'discount_based_on_total' ), 10, 1 );
			 
			// Product table compatability hide product
			add_filter( 'product_table_field_after', array( $this, 'product_table_field_after' ), 10, 4 );
			
			add_action( 'woocommerce_before_checkout_billing_form', array( $this, 'wwp_nonce_checkout_field' ), 10 );

			add_filter( 'wccs_hook_priorities', array( $this, 'wccs_hook_priorities_callback' ) );

			$settings = get_option( 'wwp_wholesale_pricing_options', true );
			/**
			* Hooks
			*
			* @since 3.0
			*/
			$this->upgrade_account = apply_filters( 'wholesale_upgrade_account_url', 'upgrade-account' );
			
			if ( isset( $settings['enable_upgrade'] ) && 'yes' == $settings['enable_upgrade'] ) {
				add_action( 'init', array( $this, 'wwp_upgrade_add_rewrite' ) );
				add_filter( 'query_vars', array( $this, 'wwp_upgrade_add_var' ), 10 );
				add_filter( 'woocommerce_account_menu_items', array( $this, 'wwp_upgrade_add_menu_items' ) );
				add_action( "woocommerce_account_{$this->upgrade_account}_endpoint", array( $this, 'wwp_upgrade_content' ) );
				add_action( 'wp_head', array( $this, 'wwp_li_icons' ) );
				add_filter( 'wp_kses_allowed_html', array( $this, 'filter_wp_kses_allowed_html' ), 10, 1 );
			}
			/**
			* Hooks
			*
			* @since 3.0
			*/
			$this->requisition_list_url = apply_filters( 'wholesale_requisition_list_url', 'requisition-list' );
			
			if ( isset( $settings['requisition_list'] ) && 'yes' == $settings['requisition_list'] ) {
				add_action( 'init', array( $this, 'wwp_requisition_list_add_rewrite' ) );
				add_filter( 'query_vars', array( $this, 'wwp_requisition_list_add_var' ), 10 );
				add_filter( 'woocommerce_account_menu_items', array( $this, 'wwp_requisition_list_add_menu_items' ) );
				add_action( "woocommerce_account_{$this->requisition_list_url}_endpoint", array( $this, 'wwp_requisition_list_url_content' ) );
			}
			
			if ( isset( $settings['requisition_list_cart_page'] ) && 'yes' == $settings['requisition_list_cart_page'] ) {
				add_action( 'woocommerce_cart_actions', array( $this, 'woocommerce_cart_actions' ), 10, 0 ); 
			}

			add_action( 'woocommerce_edit_account_form', array( $this, 'woocommerce_edit_account_form_extra_fields' ) );

			add_action( 'woocommerce_save_account_details', array( $this, 'my_account_details_form_extra_fields_saved' ) );

			add_action( 'template_redirect', array( $this, 'reset_wholesale_pricing' ) );
		}

		public function woocommerce_cart_actions() { 
			?>
			<button type="button" id="requisition_add_to_list" class="button requisition_add_to_list" name="requisition_add_to_list"><?php esc_html_e( 'Save as Requisition List', 'woocommerce-wholesale-pricing' ); ?></button>
			<?php
		}
		
		public function woocommerce_product_get_tax_class( $tax_class, $product ) {
			if ( is_wholesaler_user( get_current_user_id() ) ) {
				if ( !empty( is_wholesaler_user( get_current_user_id() ) ) ) {
					$role                       = get_current_user_role_id();
					$user_id                    = get_current_user_id();
					$user_data                  = get_userdata($user_id);
					$wwp_wholesaler_tax_classes = get_term_meta( $role , 'wwp_wholesaler_tax_classes', true);
					if ( !empty( $wwp_wholesaler_tax_classes ) ) {
						return $wwp_wholesaler_tax_classes;
					}
				}
			}
			return $tax_class;
		}
		
		public function discount_based_on_total( $cart ) {

			if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
				return;
			}

			if ( ! is_user_logged_in() && ! is_wholesaler_user( is_user_logged_in() ) ) {
				return;
			}
			$discount         = false;
			$cart_total       = $cart->cart_contents_total;
			$cart_quantity    = $cart->get_cart_contents_count();
			$role             = get_current_user_role_id();
			$data             = get_option( 'wholesale_multi_user_cart_discount' );
			$settings         = get_option( 'cart_tire_preces', true );

			if ( isset( $data[$role]['discount_on'] ) && 'cart_discount_quantity' == $data[$role]['discount_on'] ) {
				$discount_on_cart = $cart_quantity;
				$cart_quantity_discount = true;
			} else {
				$discount_on_cart = $cart_total;
				$cart_quantity_discount = false;

			}
			

			if ( isset( $settings['tier_pricing'][ $role ] ) && isset( $data[ $role ] ) ) {

				/**
				* Custom Filter Hooks
				*
				* @since 3.0
				*/
				$use_quantity   = apply_filters( 'wholesale_discount_use_quantity', false, $cart, $role );
				$compare_value  = $use_quantity || $cart_quantity_discount ? $cart_quantity : $cart_total;
				
				foreach ( $settings['tier_pricing'][ $role ] as $prices ) {
					$min_value = isset( $prices['min'] ) ? (float) $prices['min'] : 0;
					$cart_max_value = isset( $prices['cart_max'] ) ? $prices['cart_max'] : '';
					$max_value = isset( $prices['max'] ) ? $prices['max'] : '';
					$label     = isset( $prices['price'] ) && ! empty( $prices['price'] ) ? $prices['price'] : __( 'Discount', 'woocommerce-wholesale-pricing' );

					// for backword compatibilty
					if ( empty( $cart_max_value ) ) {
						if ( $compare_value >= $min_value && ! empty( $max_value ) ) {
							$discount        = wholesale_type_price( $cart_total, $data[ $role ]['discount_type'], $max_value );
							$wholesale_label = $label;
						}
					} else if ( $compare_value >= $min_value && $compare_value <= $cart_max_value && ! empty( $max_value ) ) {
						$discount        = wholesale_type_price( $cart_total, $data[ $role ]['discount_type'], $max_value );
						$wholesale_label = $label;
					}
				}
				if ( $discount ) {
					/**
					* Hooks
					*
					* @since 3.0
					*/
					$cart->add_fee( __( $wholesale_label, 'woocommerce-wholesale-pricing' ), apply_filters( 'wholesale_discount_based_on_total_amount', -$discount, $cart, $data ) );
				} elseif ( isset( $data[$role]['cart_price'] ) && ! empty( $data[$role]['cart_price'] ) ) {
					if ( empty( $data[$role]['cart_min_subtotal'] ) ) {
						$data[$role]['cart_min_subtotal'] = 1;
					}
					
					if ( $discount_on_cart >= $data[$role]['cart_min_subtotal'] ) {
						$cartdiscount = wholesale_type_price($data[$role]['cart_min_subtotal'], $data[ $role ]['discount_type'], $data[$role]['cart_price'] );
						/**
						 * Filter Hooks
						 *
						 * Label for cart total discount
						 * 
						 * @since 2.7
						*/
						$label = apply_filters(  'wholesale_cart_label' , 'Discount' );
						/**
						 * Filter Hooks
						 *
						 * Discount based on cart
						 * 
						 * @since 2.7
						*/
						$cart->add_fee( __( $label, 'woocommerce-wholesale-pricing' ), apply_filters( 'wholesale_discount_based_on_total_cart', -$cartdiscount, $cart, $data ) );
					}
				}
			}
		}

		public function wholesale_new_order_email_recipient( $recipient, $order ) {
			if ( ! is_a( $order, 'WC_Order' ) ) {
				return $recipient;
			}

			$settings = get_option( 'wwp_wholesale_pricing_options', true );
			if ( isset( $settings['emailuserrole'] ) && ( 'order_email_user_role' == $settings['emailuserrole'] || 'order_email_custom' == $settings['emailuserrole'] ) ) {

				$customer_id = $order->get_customer_id();
				if ( ! is_wholesaler_user( $customer_id ) ) {
					return $recipient;
				}

				if ( 'order_email_user_role' == $settings['emailuserrole'] && '' != $settings['email_user_role_value'] ) {
					$manager_emails = array( $recipient );
					$users          = get_users( array( 'role' => $settings['email_user_role_value'] ) );
					if ( count( $users ) > 0 ) {
						foreach ( $users as $user ) {
							$manager_emails[] = $user->data->user_email;
						}
						if ( count( $manager_emails ) > 0 ) {
							$recipient = implode( ',', $manager_emails );
						}
					}
				} elseif ( 'order_email_custom' == $settings['emailuserrole'] && '' != $settings['order_custom_email_value'] ) {
					$recipient .= ',' . $settings['order_custom_email_value'];
				}
			}
			return $recipient;
		}

		public function wwp_after_checkout_validation( $fields, $errors ) {

			if ( ! isset( $_POST['_wwp_nonce'] ) || ( isset( $_POST['_wwp_nonce'] ) && ! wp_verify_nonce( wc_clean( $_POST['_wwp_nonce'] ), '_wwp_nonce' ) ) ) {
				return;
			}

			if ( ! isset( $_POST['wwp_form_data_json'] ) ) {
				return;
			}

			if ( ! empty( wwp_get_post_data( 'wwp_form_data_json' ) ) ) {
				$formData = stripslashes( wwp_get_post_data( 'wwp_form_data_json' ) );
				$formData = json_decode( $formData );
				if ( is_array( $formData ) ) {
					foreach ( $formData as $formData_key => $formData_value ) {
						if ( isset( $formData_value->required ) && true == $formData_value->required ) {
							if ( empty( $_POST[ $formData_value->name ] ) ) {
								$form_error = __( '<strong> ' . $formData_value->label . ' </strong> is a required field.', 'woocommerce-wholesale-pricing' );
								$errors->add( 'validation', $form_error );
							}
						}
					}
				}
			}
		}

		public function before_checkout_create_order( $order, $data ) {

			if ( ! isset( $_POST['_wwp_nonce'] ) || ( isset( $_POST['_wwp_nonce'] ) && ! wp_verify_nonce( wc_clean( $_POST['_wwp_nonce'] ), '_wwp_nonce' ) ) ) {
				return;
			}

			if ( ! isset( $_POST['wwp_form_data_json'] ) ) {
				return;
			}
			$post = $_POST;

			if ( ! empty( wwp_get_post_data( 'wwp_form_data_json' ) ) ) {
				if ( is_user_logged_in() ) {
					// Form builder fields udate in user meta
					form_builder_update_user_meta( get_current_user_id(), $post );
				}
				$wwp_form_data_json = wwp_get_post_data('wwp_form_data_json');
				$wwp_form_data_json = isset( $wwp_form_data_json ) ? wc_clean($wwp_form_data_json) : '';
				$order->update_meta_data( 'wwp_form_data_json', stripslashes($wwp_form_data_json) );
			}
		}

		public function action_woocommerce_after_order_notes( $wccs_custom_checkout_field_pro ) {
			$registrations = get_option( 'wwp_wholesale_registration_options' );
			if ( isset( $registrations['display_fields_checkout'] ) && 'yes' == $registrations['display_fields_checkout'] ) {
				echo wp_kses_post( (string) render_form_builder('get_option', '') );
			}
		}
		
		public function wcpt_product_table_args( $wcpt_product_table_args ) {
		 
			$settings = get_option( 'wwp_wholesale_pricing_options', true );
			if ( ! is_user_logged_in() && ! current_user_can( 'manage_options' ) && ! is_wholesaler_user( get_current_user_id() ) ) {

				$all_ids = get_posts(
					array(
						'post_type'   => 'product',
						'numberposts' => -1,
						'post_status' => 'publish',
						'meta_query'  => array(
							array(
								'key'   => '_wwp_hide_for_visitor',
								'value' => 'yes',
							),
						),
						'fields'      => 'ids',
					)
				);
				if ( ! empty( $all_ids ) ) {
					$wcpt_product_table_args['post__not_in'] = $all_ids;
				}
			}
			 
			if (is_user_logged_in() && ! is_wholesaler_user( get_current_user_id() ) ) {

				$user = wp_get_current_user();

				if ( in_array( 'customer', (array) $user->roles ) ) {

					$all_ids = get_posts(
						array(
							'post_type'   => 'product',
							'numberposts' => -1,
							'post_status' => 'publish',
							'meta_query'  => array(
								array(
									'key'   => '_wwp_hide_for_customer',
									'value' => 'yes',
								),
							),
							'fields'      => 'ids',
						)
					);
					if ( ! empty( $all_ids ) ) {
						$wcpt_product_table_args['post__not_in'] = $all_ids;
					}
				}
			}
			
			$all_ids = $this->wwp_get_excluded_products();
			if ( ! empty( $all_ids ) ) {
				$wcpt_product_table_args['post__not_in'] = $all_ids;
			}
			 
			return $wcpt_product_table_args;
		}
		
		
		public function wwp_get_excluded_products() {

			if ( ! is_user_logged_in() ) {
				return false;
			}
			$user = get_userdata( get_current_user_id() );
			
			$user_role = implode( ', ', $user->roles );
			if ( ! term_exists( $user_role, 'wholesale_user_roles' ) ) {
				return false;
			}
			return $this->wwp_get_products_multi( $user_role );
		}
		public function wwp_get_products_multi( $role ) {  
			$excluded_products_1 = array();
			$excluded_products_2 = array();
			$args                = array(
				'post_type'      => array( 'product' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'     => 'wholesale_product_visibility_multi',
						'value'   => sprintf( ':"%s";', $role ),
						'compare' => 'LIKE',
					),
				),
			);
			$excluded_products_1 = get_posts( $args );
			$args                = array(
				'hide_empty'     => true,
				'fields'         => 'ids',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => 'wholesale_product_visibility_multi',
						'value'   => sprintf( ':"%s";', $role ),
						'compare' => 'LIKE',
					),
				),
				'taxonomy'       => 'product_cat',
			);
			$terms               = get_terms( $args );
			if ( ! empty( $terms ) ) {
				$args                = array(
					'post_type'      => array( 'product' ),
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'tax_query'      => array(
						array(
							'taxonomy' => 'product_cat',
							'terms'    => $terms,
							'operator' => 'IN',
						),
					),
				);
				$excluded_products_2 = get_posts( $args );
			}
			return array_merge( $excluded_products_1, $excluded_products_2 );
		}
		 
		public function default_wholesaler_products_only( $q ) {
			
			$settings              = get_option( 'wwp_wholesale_pricing_options', true );
			$data                  = '';
			$restrict_store_access = ( isset( $settings['restrict_store_access'] ) && 'yes' == $settings['restrict_store_access'] ) ? 'yes' : 'no';
			if ( ! isset( $_COOKIE['access_store_id'] ) && 'yes' == $restrict_store_access && $q->is_main_query() && ! is_user_logged_in() && ! current_user_can( 'manage_options' ) ) {

				$all_ids = get_products_by_meta();
				
				if ( ! empty( $all_ids ) ) {
					$this->exclude_ids = $all_ids;
					$this->wwp_extra_product_remove( $this->exclude_ids );
					$q->set( 'post__not_in', $this->exclude_ids );
				}
			}
		
			if ( $q->is_main_query() && ! is_user_logged_in() && ! current_user_can( 'manage_options' ) && ! is_wholesaler_user( get_current_user_id() ) ) {
				
				$all_ids = get_products_by_meta( '_wwp_hide_for_visitor', 'yes' );

				if ( ! empty( $all_ids ) ) {
					$this->exclude_ids = array_merge( $this->exclude_ids, $all_ids );
					$this->wwp_extra_product_remove( $this->exclude_ids );
					$q->set( 'post__not_in', $this->exclude_ids );
				}
			}

			if ( $q->is_main_query() && is_user_logged_in() && ! is_wholesaler_user( get_current_user_id() ) ) {
				
				$user = wp_get_current_user();
				
				if ( in_array( 'customer', (array) $user->roles ) ) {
			
					$all_ids = get_products_by_meta( '_wwp_hide_for_customer', 'yes' );

					if ( ! empty( $all_ids ) ) {
						$this->exclude_ids = array_merge( $this->exclude_ids, $all_ids );
						$this->wwp_extra_product_remove( $this->exclude_ids );
						$q->set( 'post__not_in', $this->exclude_ids );
					}
				}
			}
			
			$non_wholesale_product_hide = ( isset( $settings['non_wholesale_product_hide'] ) && 'yes' == $settings['non_wholesale_product_hide'] ) ? 'yes' : 'no';
			if ( 'yes' == $non_wholesale_product_hide && $q->is_main_query() && is_wholesaler_user( get_current_user_id() ) == false && ! current_user_can( 'manage_options' ) ) {
				
				// Global price get
				if ( empty( get_option( 'wholesale_multi_user_pricing' ) ) ) {
					$data = 'no';
				} else {
					$data = '';
				}

				$all_ids = get_products_by_meta();


				if ( 'no' == $data ) {
				
					$total_ids = multi_wholesale_product_ids();

					if ( is_array( $total_ids ) ) {
						
						$exclude_ids       = array_diff( $all_ids, $total_ids );
						$this->exclude_ids = array_merge( $this->exclude_ids, $total_ids );
						$q->set( 'post__not_in', $this->exclude_ids );
						$this->wwp_extra_product_remove( $this->exclude_ids );
					} else {
						$this->exclude_ids = array_merge( $this->exclude_ids, $all_ids );
						$q->set( 'post__not_in', $this->exclude_ids );
						$this->wwp_extra_product_remove( $this->exclude_ids );
					}
				} else {
						$this->exclude_ids = array_merge( $this->exclude_ids, $all_ids );
						$q->set( 'post__not_in', $this->exclude_ids );
						$this->wwp_extra_product_remove( $this->exclude_ids );
				}
			}
		}
		
		/**
		 * Default_wholesaler_products_hide_from_blocks
		 *
		 * @param object $q
		 * @return object
		 */
		public function default_wholesaler_products_hide_from_blocks( $q ) {

			if ( ! function_exists( 'is_user_logged_in' ) ) {
				require_once ABSPATH . 'wp-includes/pluggable.php';
			}
			
			$settings              = get_option( 'wwp_wholesale_pricing_options', true );
			$data                  = '';
			$restrict_store_access = ( isset( $settings['restrict_store_access'] ) && 'yes' == $settings['restrict_store_access'] ) ? 'yes' : 'no';
			
			if ( ! isset( $_COOKIE['access_store_id'] ) && 'yes' == $restrict_store_access && ! is_user_logged_in() && ! current_user_can( 'manage_options' ) ) {

				$all_ids = get_products_by_meta();
				
				if ( ! empty( $all_ids ) ) {
					$this->exclude_ids = $all_ids;
					$q->query_vars['post__not_in'] = $this->exclude_ids;
				}
			}
		
			if ( ! is_user_logged_in() && ! current_user_can( 'manage_options' ) && ! is_wholesaler_user( get_current_user_id() ) ) {
				
				$all_ids = get_products_by_meta( '_wwp_hide_for_visitor', 'yes' );
				

				if ( ! empty( $all_ids ) ) {
					$this->exclude_ids = array_merge( $this->exclude_ids, $all_ids );
					$q->query_vars['post__not_in'] = $this->exclude_ids;
				}
			}

			if ( is_user_logged_in() && ! is_wholesaler_user( get_current_user_id() ) ) {
				
				$user = wp_get_current_user();
				
				if ( in_array( 'customer', (array) $user->roles ) ) {
			
					$all_ids = get_products_by_meta( '_wwp_hide_for_customer', 'yes' );

					if ( ! empty( $all_ids ) ) {
						$this->exclude_ids = array_merge( $this->exclude_ids, $all_ids );
						$q->query_vars['post__not_in'] = $this->exclude_ids;
					}
				}
			}
			
			$non_wholesale_product_hide = ( isset( $settings['non_wholesale_product_hide'] ) && 'yes' == $settings['non_wholesale_product_hide'] ) ? 'yes' : 'no';

			if ( 'yes' == $non_wholesale_product_hide && is_wholesaler_user( get_current_user_id() ) == false && ! current_user_can( 'manage_options' ) ) {
				// Global price get
				if ( empty( get_option( 'wholesale_multi_user_pricing' ) ) ) {
					$data = 'no';
				} else {
					$data = '';
				}

				$all_ids = get_products_by_meta();

 
				if ( 'no' == $data ) {
				
					$total_ids = multi_wholesale_product_ids();

					if ( is_array( $total_ids ) ) {
						
						$exclude_ids       = array_diff( $all_ids, $total_ids );
						$this->exclude_ids = array_merge( $this->exclude_ids, $total_ids );
						$q->query_vars['post__not_in'] = $this->exclude_ids;
					} else {
						$this->exclude_ids = array_merge( $this->exclude_ids, $all_ids );
						$q->query_vars['post__not_in'] = $this->exclude_ids;
					}
				} else {
						$this->exclude_ids = array_merge( $this->exclude_ids, $all_ids );
						$q->query_vars['post__not_in'] = $this->exclude_ids;
				}
			}
			
			return $q;
		}

		public function wwp_extra_product_remove( $all_ids ) {
			add_filter( 'woocommerce_related_products', array( $this, 'exclude_related_products' ), 10, 3 );
			add_filter( 'woocommerce_shortcode_products_query', array( $this, 'woocommerce_shortcode_products_query' ), 99, 3 );
		}

		public function woocommerce_shortcode_products_query( $query_args, $attributes, $type ) {
			if ( isset( $query_args['post__in'] ) && is_array( $query_args['post__in'] ) ) {
				$query_args['post__in'] = array_diff( $query_args['post__in'], $this->exclude_ids );
			}
			$query_args['post__not_in'] = $this->exclude_ids;
			return $query_args;
		}

		public function exclude_related_products( $related_posts, $product_id, $args ) {
			return array_diff( $related_posts, $this->exclude_ids );
		}

		public function authenticate( $user, $username ) {

			if ( ! is_wp_error( $user ) ) {
				$settings  = get_option( 'wwp_wholesale_pricing_options', true );
				$auth_user = get_user_by( 'id', $user->data->ID );
				$user_role = $auth_user->roles;
				if ( ! empty( $settings['wholesaler_login_restriction'] ) && 'yes' == $settings['wholesaler_login_restriction'] ) {
					if ( ! empty( $user_role ) ) {
						foreach ( $user_role as $key => $role ) {
							$args    = array(
								'post_type'  => array( 'wwp_requests' ),
								'order'      => 'ASC',
								'orderby'    => 'id',
								'fields'     => 'ids',
								'meta_query' => array(
									array(
										'key'   => '_user_id',
										'value' => $user->data->ID,
									),
								),
							);
							
							$post_id = get_posts( $args );
							if ( isset( $post_id[0] ) ) {
								$user_status = get_post_meta( $post_id[0], '_user_status', true );
								if ( 'waiting' == $user_status ) {
									if ( empty( $settings['login_message_waiting_user'] ) ) {
										$settings['login_message_waiting_user'] = __('You can not access this store, Your request status is in Pending', 'woocommerce-wholesale-pricing' );
									}
									return new WP_Error( 'authentication_failed', __( $settings['login_message_waiting_user'], 'woocommerce-wholesale-pricing' ) );
								} elseif ( 'rejected' == $user_status ) {
									if ( empty( $settings['login_message_rejected_user'] ) ) {
										$settings['login_message_rejected_user'] = __('You can not access this store, Your request is Rejected by admin', 'woocommerce-wholesale-pricing');
									}
									return new WP_Error( 'authentication_failed', __( $settings['login_message_rejected_user'], 'woocommerce-wholesale-pricing' ) );
								}
							}
						}
					}
				}
			}
			return $user;
		}

		public function filter_wp_kses_allowed_html( $allowedposttags ) {
			if ( is_account_page() ) {
				$allowed_atts                = array(
					'align'      => array(),
					'noscript'      => array(),
					'class'      => array(),
					'id'         => array(),
					'dir'        => array(),
					'lang'       => array(),
					'style'      => array(),
					'xml:lang'   => array(),
					'src'        => array(),
					'alt'        => array(),
					'href'       => array(),
					'rel'        => array(),
					'rev'        => array(),
					'target'     => array(),
					'novalidate' => array(),
					'type'       => array(),
					'name'       => array(),
					'tabindex'   => array(),
					'action'     => array(),
					'method'     => array(),
					'for'        => array(),
					'width'      => array(),
					'height'     => array(),
					'data'       => array(),
					'title'      => array(),
					'value'      => array(),
					'selected'   => array(),
					'enctype'    => array(),
					'disable'    => array(),
					'disabled'   => array(),
				);
				$allowedposttags['form']     = $allowed_atts;
				$allowedposttags['label']    = $allowed_atts;
				$allowedposttags['noscript'] = $allowed_atts;
				$allowedposttags['select']   = $allowed_atts;
				$allowedposttags['option']   = $allowed_atts;
				$allowedposttags['input']    = $allowed_atts;
				$allowedposttags['textarea'] = $allowed_atts;
				$allowedposttags['iframe']   = $allowed_atts;
				$allowedposttags['script']   = $allowed_atts;
				$allowedposttags['style']    = $allowed_atts;
				$allowedposttags['strong']   = $allowed_atts;
				$allowedposttags['small']    = $allowed_atts;
				$allowedposttags['table']    = $allowed_atts;
				$allowedposttags['span']     = $allowed_atts;
				$allowedposttags['abbr']     = $allowed_atts;
				$allowedposttags['code']     = $allowed_atts;
				$allowedposttags['pre']      = $allowed_atts;
				$allowedposttags['div']      = $allowed_atts;
				$allowedposttags['img']      = $allowed_atts;
				$allowedposttags['h1']       = $allowed_atts;
				$allowedposttags['h2']       = $allowed_atts;
				$allowedposttags['h3']       = $allowed_atts;
				$allowedposttags['h4']       = $allowed_atts;
				$allowedposttags['h5']       = $allowed_atts;
				$allowedposttags['h6']       = $allowed_atts;
				$allowedposttags['ol']       = $allowed_atts;
				$allowedposttags['ul']       = $allowed_atts;
				$allowedposttags['li']       = $allowed_atts;
				$allowedposttags['em']       = $allowed_atts;
				$allowedposttags['hr']       = $allowed_atts;
				$allowedposttags['br']       = $allowed_atts;
				$allowedposttags['tr']       = $allowed_atts;
				$allowedposttags['td']       = $allowed_atts;
				$allowedposttags['p']        = $allowed_atts;
				$allowedposttags['a']        = $allowed_atts;
				$allowedposttags['b']        = $allowed_atts;
				$allowedposttags['i']        = $allowed_atts;
			}
			return $allowedposttags;
		}
		public function product_table_field_after( $product_record, $product_table_field, $args, $product_id ) { 
			if ( 'price'  == $product_table_field || 'cart_button' == $product_table_field ) {
				$lastKey                  = count( $product_record )-1;
				$product_record[$lastKey] = $this->wwp_logintoseeprice( $product_record[$lastKey] );
			}
			return $product_record;
		}
		
		public function wwp_logintoseeprice( $product_record ) {
			$settings = get_option( 'wwp_wholesale_pricing_options', true );
			if ( ! is_user_logged_in() && isset( $settings['price_hide'] ) && 'yes' == $settings['price_hide'] ) {
				if ( isset( $settings['display_link_text'] ) && ! empty( $settings['display_link_text'] ) ) {
					$link_text = $settings['display_link_text'];
				} else {
					$link_text = esc_html__( 'Login to see price', 'woocommerce-wholesale-pricing' );
				}
				return '<a class="login-to-upgrade" href="' . esc_url( get_permalink( wc_get_page_id( 'myaccount' ) ) ) . '">' . esc_html__( $link_text, 'woocommerce-wholesale-pricing' ) . '</a>';
			} else {
				return $product_record;
			}
		}

		public function wwp_hide_price_add_cart_not_logged_in() {
			$settings = get_option( 'wwp_wholesale_pricing_options', true );

			if ( ! is_user_logged_in() && isset( $settings['price_hide'] ) && 'yes' == $settings['price_hide'] ) {
				remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 10 );
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
				remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
				remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
				add_action( 'woocommerce_single_variation', array( $this, 'wwp_woocommerce_get_variation_price_html' ) );
				add_action( 'woocommerce_single_product_summary', array( $this, 'wwp_removeretail_prices' ), 10 );
				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'wwp_removeretail_prices' ), 11 );
				add_filter( 'woocommerce_get_price_html', array( $this, 'wwp_woocommerce_get_price_html' ), 10, 2 );
				add_filter( 'woocommerce_is_purchasable', array( $this, 'filter_woocommerce_is_purchasable' ), 10, 2 );
			}
		}

		public function filter_woocommerce_is_purchasable( $this_exists_publish, $instance ) {
			return false;
		}
		
		public function wwp_woocommerce_get_variation_price_html() { 
			return '';
		}

		public function wwp_removeretail_prices() {
			$settings = get_option( 'wwp_wholesale_pricing_options', true );

			if ( isset( $settings['display_link_text'] ) && ! empty( $settings['display_link_text'] ) ) {
				$link_text = $settings['display_link_text'];
			} else {
				$link_text = esc_html__( 'Login to see price', 'woocommerce-wholesale-pricing' );
			}
			echo '<a class="login-to-upgrade" href="' . esc_url( get_permalink( wc_get_page_id( 'myaccount' ) ) ) . '">' . esc_html__( $link_text, 'woocommerce-wholesale-pricing' ) . '</a>';
		}

		public function wwp_woocommerce_get_price_html( $price, $product ) {
			return '';
		}

		public function wwp_li_icons() { 
			?>
				<style>.woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--<?php echo esc_attr( $this->upgrade_account ); ?> a::before {content: '\f1de';}</style> 
			<?php 
		}

		public function wwp_upgrade_add_rewrite() {
			global $wp_rewrite;
			add_rewrite_endpoint( $this->upgrade_account, EP_ROOT | EP_PAGES );
			$wp_rewrite->flush_rules();
		}
		
		public function wwp_requisition_list_add_rewrite() {
			global $wp_rewrite;
			add_rewrite_endpoint( $this->requisition_list_url, EP_ROOT | EP_PAGES );
			$wp_rewrite->flush_rules();
		}
		public function wwp_upgrade_add_var( $vars ) {
			$vars[] = $this->upgrade_account;
			return $vars;
		}
		
		public function wwp_requisition_list_add_var( $vars ) {
			$vars[] = $this->requisition_list_url;
			return $vars;
		}
		

		public function wwp_upgrade_add_menu_items( $items ) {
			$settings = get_option( 'wwp_wholesale_pricing_options', true );

			if ( isset( $settings['upgrade_tab_text'] ) && ! empty( $settings['upgrade_tab_text'] ) ) {
				$items[$this->upgrade_account] = $settings['upgrade_tab_text'];
			} else {
				$items[$this->upgrade_account] = esc_html__( 'Upgrade Account', 'woocommerce-wholesale-pricing' );
			}

			return $items;
		}
		
		public function wwp_requisition_list_add_menu_items( $items ) {
			$settings = get_option( 'wwp_wholesale_pricing_options', true );

			if ( isset( $settings['requisition_list_text'] ) && ! empty( $settings['requisition_list_text'] ) ) {
				$items[$this->requisition_list_url] = $settings['requisition_list_text'];
			} else {
				$items[$this->requisition_list_url] = esc_html__( 'Requisition List', 'woocommerce-wholesale-pricing' );
			}

			return $items;
		}

		public function wwp_upgrade_content() {
			$this->wwp_account_content_callback();
		}
		
		public function wwp_requisition_list_url_content() {
			wp_dequeue_script( 'selectWoo' );
			wp_enqueue_style( 'dashicons' );
			wp_enqueue_style( 'select2css' );
			wp_enqueue_script( 'select2' );
			?>
			<div class="wwp_requisition_list_main">
				<div class="container wwp_requisition_list">
					<h2><?php esc_html_e( 'Shopping lists', 'woocommerce-wholesale-pricing' ); ?></h2>
						<button id="add_new_list" class="add_new_list"> + <?php esc_html_e( 'New List', 'woocommerce-wholesale-pricing' ); ?></button>
						<table id="table_id" class="display">
							<thead>
								<tr>
									<td><?php esc_html_e( 'List name', 'woocommerce-wholesale-pricing' ); ?></td>
									<td><?php esc_html_e( 'Number of items', 'woocommerce-wholesale-pricing' ); ?></td>
									<td><?php esc_html_e( 'Actions', 'woocommerce-wholesale-pricing' ); ?></td>
									<td><?php esc_html_e( 'Delete', 'woocommerce-wholesale-pricing' ); ?></td>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
					<div id="myModal" class="modal">
						<div class="modal-content">
							<div class="modal-header">
								<h3><?php esc_html_e( 'Requisition list', 'woocommerce-wholesale-pricing' ); ?></h3><span class="close">&times;</span>
							</div>
							<center>
								<div class="loader"></div>
							</center>
							<div class="modal-body"></div>
					</div>
				</div>
			</div>
			<?php
		}
		
		public function wwp_account_content_callback() {
			if ( is_user_logged_in() ) {
				$settings      = get_option( 'wwp_wholesale_pricing_options', true );
				$post_data     = wwp_get_post_data();
					$user_id   = get_current_user_id();
					$user_info = get_userdata( $user_id );
					$user_role = $user_info->roles;
					$check     = '';
				if ( ! empty( $user_role ) ) {
					foreach ( $user_role as $key => $role ) {
						if ( term_exists( $role, 'wholesale_user_roles' ) ) {
							$check = 1;
							break;
						}
					}
				}

				if ( 'waiting' == get_user_meta( $user_id, '_user_status', true ) ) {
					/**
					* Hooks
					*
					* @since 3.0
					*/
					$notice = apply_filters( 'wwp_pending_msg', __( 'Your request for upgrade account is pending.', 'woocommerce-wholesale-pricing' ) );
					wc_print_notice( esc_html__( $notice, 'woocommerce-wholesale-pricing' ), 'success' );

				} elseif ( 'rejected' == get_user_meta( $user_id, '_user_status', true ) ) {

					if ( ! isset( $post_data['wwp_register_upgrade'] ) ) {
						wc_print_notice( __( 'Your upgrade request is rejected.', 'woocommerce-wholesale-pricing' ), 'error' );
						$rejected_note = get_user_meta( get_current_user_id(), 'rejected_note', true );
						echo '<p class="rejected_note">' . esc_html__( $rejected_note, 'woocommerce-wholesale-pricing' ) . '</p>';
					}

					if ( isset( $settings['request_again_submit'] ) && 'yes' == $settings['request_again_submit'] ) {
						$this->wwp_registration_insert( $user_id, $check, $settings );
						if ( ! isset( $post_data['wwp_register_upgrade'] ) ) {
							echo wp_kses_post( $this->wwp_registration_form() );
						}
					}
				} elseif ( 'active' == get_user_meta( $user_id, '_user_status', true ) ) {

					wc_print_notice( __( 'Your request is approved.', 'woocommerce-wholesale-pricing' ), 'success' );

				} elseif ( ! term_exists( get_user_meta( $user_id, 'wholesale_role_status', true ), 'wholesale_user_roles' ) ) {
					$this->wwp_registration_insert( $user_id, $check, $settings );
				}

				if ( get_user_meta( $user_id, '_user_status', true ) ) {
					$check = 1;
				}
				if ( empty( $check ) ) {
					wc_print_notice( __( 'Apply here to upgrade your account.', 'woocommerce-wholesale-pricing' ), 'notice' );
					echo wp_kses_post( $this->wwp_registration_form() );
				}
			}
		}

		public function wwp_registration_insert( $user_id, $check, $settings ) {
			if ( ! isset( $_POST['wwp_wholesale_registrattion_nonce'] ) || ( isset( $_POST['wwp_wholesale_registrattion_nonce'] ) && ! wp_verify_nonce( wc_clean( $_POST['wwp_wholesale_registrattion_nonce'] ), 'wwp_wholesale_registrattion_nonce') ) ) {
				return;
			}
			
			if ( ! isset( $_POST['wwp_register_upgrade'] ) ) {
				return;
			}

			$post = $_POST;
			
			if ( isset( $_POST['g-recaptcha-response'] ) ) {
				/**
				* Hooks
				*
				* @since 3.0
				*/
				$notice_recaptcha = apply_filters( 'wwp_recaptcha_error_msg', esc_html__( 'Robot verification failed, please try again.', 'woocommerce-wholesale-pricing' ) );

				if ( empty( $_POST['g-recaptcha-response'] ) ) {
					wc_print_notice( esc_html__( $notice_recaptcha, 'woocommerce-wholesale-pricing' ), 'error' );
					return;
				}

				$secret         = get_option( 'anr_admin_options' )['secret_key'];
				$captcha        = wc_clean( $_POST['g-recaptcha-response'] );
				$verifyResponse = file_get_contents( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $captcha ); // nosemgrep: audit.php.lang.security.file.read-write-delete
				$responseData   = json_decode( $verifyResponse );
				if ( ! $responseData->success ) {
					wc_print_notice( esc_html__( $notice_recaptcha, 'woocommerce-wholesale-pricing' ), 'error' );
					return;
				}
			}
			if ( ! is_wp_error( $user_id ) ) {
			
				// // Get customers WC_Customer instance.
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
					$customer->set_billing_state( $billing_city );
				}
				if ( isset( $_POST['wwp_wholesaler_post_code'] ) ) {
					$billing_postcode = wc_clean( $_POST['wwp_wholesaler_post_code'] );
					//update_user_meta( $user_id, 'billing_postcode', $billing_postcode );
					$customer->set_billing_postcode( $billing_city );
				}
				if ( isset( $_POST['billing_country'] ) ) {
					$billing_country = wc_clean( $_POST['billing_country'] );
					//update_user_meta( $user_id, 'billing_country', $billing_country );
					$customer->set_billing_country( $billing_city );
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
					require_once ABSPATH . 'wp-admin/includes/file.php';
					require_once ABSPATH . 'wp-admin/includes/image.php';
					require_once ABSPATH . 'wp-admin/includes/media.php';
					$attach_id = media_handle_upload( 'wwp_wholesaler_file_upload', $user_id );
					//update_user_meta( $user_id, 'wwp_wholesaler_file_upload', $attach_id );
					$customer->update_meta_data( 'wwp_wholesaler_file_upload', $attach_id );
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
						'post_title'  => get_userdata( get_current_user_id() )->data->user_nicename . ' - ' . get_current_user_id() . ' - Upgrade Request',
						'post_status' => 'publish',
					)
				);
				if ( ! is_wp_error( $id ) ) {
				
					
					update_post_meta( $id, '_user_id', $user_id );
					

					if ( ! isset( $settings['disable_auto_role'] ) || ( isset( $settings['disable_auto_role'] ) && 'no' == $settings['disable_auto_role'] ) ) {
						update_post_meta( $id, '_user_status', 'active' );
						//update_user_meta( $user_id, '_user_status', 'active' );
						$customer->update_meta_data( '_user_status', 'active' );
						$u        = new WP_User( $user_id );
						$wp_roles = new WP_Roles();
						$names    = $wp_roles->get_names();
						foreach ( $names as $key => $value ) {
							$u->remove_role($key);
						}
						if ( isset( $settings['wholesale_role'] ) && 'single' == $settings['wholesale_role'] ) {

							wp_set_object_terms( $id, 'default_wholesaler', 'wholesale_user_roles', true );
							$u->add_role('default_wholesaler');
						} elseif ( isset( $settings['default_multipe_wholesale_roles'] ) ) {
							$u->add_role($settings['default_multipe_wholesale_roles']);
							wp_set_object_terms( $id, $settings['default_multipe_wholesale_roles'], 'wholesale_user_roles', true );
						} else {
							$u->add_role('default_wholesaler');
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
				}
				// On success
				if ( ! is_wp_error( $user_id ) ) {
					/**
					* Hooks
					*
					* @since 3.0
					*/
					$notice = apply_filters( 'wwp_success_msg', esc_html__( 'Your request for upgrade account is submitted.', 'woocommerce-wholesale-pricing' ) );
					wc_print_notice( esc_html__( $notice, 'woocommerce-wholesale-pricing' ), 'success' );
				} else {
					/**
					* Hooks
					*
					* @since 3.0
					*/
					$notice = apply_filters( 'wwp_error_msg', esc_html__( $user_id->get_error_message(), 'woocommerce-wholesale-pricing' ) );
					wc_print_notice( esc_html__( $notice, 'woocommerce-wholesale-pricing' ), 'error' );
				}
				wp_safe_redirect( wp_get_referer() );
			}
			$check = 1;
		}

		public function wwp_registration_form() {

			global $woocommerce;
			$countries_obj         = new WC_Countries();
			$countries             = $countries_obj->__get( 'countries' );
			$default_country       = $countries_obj->get_base_country();
			$default_county_states = $countries_obj->get_states( $default_country );
			$errors                = array();

			wp_enqueue_script('address-i18n');
			wp_enqueue_script('wc-country-select');

			$username      = '';
			$email         = '';
			$fname         = '';
			$lname         = '';
			$company       = '';
			$addr1         = '';
			$settings      = get_option( 'wwp_wholesale_pricing_options', true );
			$registrations = get_option( 'wwp_wholesale_registration_options' );
			ob_start();
			if ( isset( $settings['wholesale_css'] ) ) {
				?>
			<style type="text/css">
				<?php echo wp_kses_post( $settings['wholesale_css'] ); ?>
			</style>
				<?php
			}
			?>
			<div class="wwp_wholesaler_registration">
			
				<form method="post" action="" enctype="multipart/form-data">
					<?php wp_nonce_field( 'wwp_wholesale_registrattion_nonce', 'wwp_wholesale_registrattion_nonce' ); ?>
					<?php
					if ( empty( $registrations ) || ( isset( $registrations['custommer_billing_address'] ) && 'yes' == $registrations['custommer_billing_address'] ) ) {
						?>
						<h2><?php esc_html_e( 'Customer billing address', 'woocommerce-wholesale-pricing' ); ?></h2>
						<?php
						if ( isset( $registrations['enable_billing_first_name'] ) && 'yes' == $registrations['enable_billing_first_name'] ) {
							?>
							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
								<label for="wwp_wholesaler_fname"> <?php echo ! empty( $registrations['billing_first_name'] ) ? esc_html( $registrations['billing_first_name'] ) : esc_html__( 'First Name', 'woocommerce-wholesale-pricing' ); ?> <span class="required">*</span></label>
								<input type="text" name="wwp_wholesaler_fname" id="wwp_wholesaler_fname" value="<?php esc_attr_e( $fname ); ?>" required>
							</p>
							<?php
						}
						if ( isset( $registrations['enable_billing_last_name'] ) && 'yes' == $registrations['enable_billing_last_name'] ) {
							?>
							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
								<label for="wwp_wholesaler_lname"><?php echo ! empty( $registrations['billing_last_name'] ) ? esc_html( $registrations['billing_last_name'] ) : esc_html__( 'Last Name', 'woocommerce-wholesale-pricing' ); ?> <span class="required">*</span></label>
								<input type="text" name="wwp_wholesaler_lname" id="wwp_wholesaler_lname" value="<?php esc_attr_e( $lname ); ?>" required>
							</p>
							<?php
						}
						if ( isset( $registrations['enable_billing_company'] ) && 'yes' == $registrations['enable_billing_company'] ) {
							?>
							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
								<label for="wwp_wholesaler_fname"><?php echo ! empty( $registrations['billing_company'] ) ? esc_html( $registrations['billing_company'] ) : esc_html__( 'Company', 'woocommerce-wholesale-pricing' ); ?> <span class="required">*</span></label>
								<input type="text" name="wwp_wholesaler_company" id="wwp_wholesaler_company" value="<?php esc_attr_e( $company ); ?>"  required>
							</p>
							<?php
						}
						if ( isset( $registrations['enable_billing_address_1'] ) && 'yes' == $registrations['enable_billing_address_1'] ) {
							?>
							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
								<label for="wwp_wholesaler_address_line_1"><?php echo ! empty( $registrations['billing_address_1'] ) ? esc_html( $registrations['billing_address_1'] ) : esc_html__( 'Address line 1', 'woocommerce-wholesale-pricing' ); ?> <span class="required">*</span></label>
								<input type="text" name="wwp_wholesaler_address_line_1" id="wwp_wholesaler_address_line_1" value="<?php esc_attr_e( $addr1 ); ?>" required>
							</p>
							<?php
						}
						if ( isset( $registrations['enable_billing_address_2'] ) && 'yes' == $registrations['enable_billing_address_2'] ) {
							?>
							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
								<label for="wwp_wholesaler_address_line_2"><?php echo ! empty( $registrations['billing_address_2'] ) ? esc_html( $registrations['billing_address_2'] ) : esc_html__( 'Address line 2', 'woocommerce-wholesale-pricing' ); ?></label>
								<input type="text" name="wwp_wholesaler_address_line_2" id="wwp_wholesaler_address_line_2">
							</p>
							<?php
						}
						if ( isset( $registrations['enable_billing_city'] ) && 'yes' == $registrations['enable_billing_city'] ) {
							?>
							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
								<label for="wwp_wholesaler_city"><?php echo ! empty( $registrations['billing_city'] ) ? esc_html( $registrations['billing_city'] ) : esc_html__( 'City', 'woocommerce-wholesale-pricing' ); ?><span class="required">*</span></label>
								<input type="text" name="wwp_wholesaler_city" id="wwp_wholesaler_city" required>
							</p>
							<?php
						}
						if ( isset( $registrations['enable_billing_post_code'] ) && 'yes' == $registrations['enable_billing_post_code'] ) {
							?>
							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
								<label for="wwp_wholesaler_post_code"><?php echo ! empty( $registrations['billing_post_code'] ) ? esc_html( $registrations['billing_post_code'] ) : esc_html__( 'Postcode / ZIP', 'woocommerce-wholesale-pricing' ); ?> <span class="required">*</span></label>
								<input type="text" name="wwp_wholesaler_post_code" id="wwp_wholesaler_post_code" required>
							</p>
							<?php
						}
						?>
						<div class="parent"> 
						<?php
						if ( isset( $registrations['enable_billing_country'] ) && 'yes' == $registrations['enable_billing_country'] ) {
							?>
							<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
							<?php
								woocommerce_form_field(
									'billing_country',
									array(
										'type'        => 'country',
										'class'       => array( 'chzn-drop' ),
										'label'       => !empty( $registrations['billing_countries'] ) ? esc_html__( __( $registrations['billing_countries'] ), 'woocommerce-wholesale-pricing' ) : esc_html__( 'Select billing country', 'woocommerce-wholesale-pricing' ),
										'default'     => $default_country,
										'options'     => $countries,
									)
								);
							?>
							</<?php wwp_elements( 'p' ); ?>>
							
							<?php
						}
						if ( isset( $registrations['enable_billing_state'] ) && 'yes' == $registrations['enable_billing_state'] ) {
							?>
							<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
								<?php 
								woocommerce_form_field(
									'billing_state',
									array(
										'type'       => 'state',
										'class'      => array( 'chzn-drop' ),
										'label'      => !empty( $registrations['billing_state'] ) ? esc_html__( __( $registrations['billing_state'] ), 'woocommerce-wholesale-pricing' ) : esc_html__( 'State / County or state code', 'woocommerce-wholesale-pricing' ),
										'options'     => $default_county_states,
										)
									);
								?>
							</<?php wwp_elements( 'p' ); ?>>
							
							<?php
						}
						?>
							</div> 
						<?php 
						if ( isset( $registrations['enable_billing_phone'] ) && 'yes' == $registrations['enable_billing_phone'] ) {
							?>
							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
								<label for="wwp_wholesaler_phone"><?php echo ! empty( $registrations['billing_phone'] ) ? esc_html( $registrations['billing_phone'] ) : esc_html__( 'Phone', 'woocommerce-wholesale-pricing' ); ?> <span class="required">*</span></label>
								<input type="text" name="wwp_wholesaler_phone" id="wwp_wholesaler_phone" required>
							</p>
							<?php
						}
					}
					if ( empty( $registrations ) || ( isset( $registrations['custommer_shipping_address'] ) && 'yes' == $registrations['custommer_shipping_address'] ) ) {
						?>
						<h2><?php esc_html_e( 'Customer shipping address', 'woocommerce-wholesale-pricing' ); ?></h2>
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="wwp_wholesaler_copy_billing_address"><?php esc_html_e( 'Copy from billing address', 'woocommerce-wholesale-pricing' ); ?></label>
							<input type="checkbox" name="wwp_wholesaler_copy_billing_address" id="wwp_wholesaler_copy_billing_address" value="yes" >
						</p>
						<div id="wholesaler_shipping_address"> 
							<?php
							if ( isset( $registrations['enable_shipping_first_name'] ) && 'yes' == $registrations['enable_shipping_first_name'] ) {
								?>
								<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
									<label for="wwp_wholesaler_shipping_lname"><?php echo ! empty( $registrations['shipping_first_name'] ) ? esc_html( $registrations['shipping_first_name'] ) : esc_html__( 'First Name', 'woocommerce-wholesale-pricing' ); ?> <span class="required">*</span></label>
									<input type="text" name="wwp_wholesaler_shipping_fname" id="wwp_wholesaler_shipping_fname" >
								</p>
								<?php
							}
							if ( isset( $registrations['enable_shipping_last_name'] ) && 'yes' == $registrations['enable_shipping_last_name'] ) {
								?>
								<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
									<label for="wwp_wholesaler_shipping_fname"> <?php echo ! empty( $registrations['shipping_last_name'] ) ? esc_html( $registrations['shipping_last_name'] ) : esc_html__( 'Last Name', 'woocommerce-wholesale-pricing' ); ?> <span class="required">*</span> </label>
									<input type="text" name="wwp_wholesaler_shipping_lname" id="wwp_wholesaler_shipping_lname" >
								</p>
								<?php
							}
							if ( isset( $registrations['enable_shipping_company'] ) && 'yes' == $registrations['enable_shipping_company'] ) {
								?>
								<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
									<label for="wwp_wholesaler_shipping_company"><?php echo ! empty( $registrations['shipping_company'] ) ? esc_html( $registrations['shipping_company'] ) : esc_html__( 'Company', 'woocommerce-wholesale-pricing' ); ?> <span class="required">*</span></label>
									<input type="text" name="wwp_wholesaler_shipping_company" id="wwp_wholesaler_shipping_company" >
								</p>
								<?php
							}
							if ( isset( $registrations['enable_shipping_address_1'] ) && 'yes' == $registrations['enable_shipping_address_1'] ) {
								?>
								<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
									<label for="wwp_wholesaler_shipping_address_line_1"><?php echo ! empty( $registrations['shipping_address_1'] ) ? esc_html( $registrations['shipping_address_1'] ) : esc_html__( 'Address line 1', 'woocommerce-wholesale-pricing' ); ?> <span class="required">*</span></label>
									<input type="text" name="wwp_wholesaler_shipping_address_line_1" id="wwp_wholesaler_shipping_address_line_1" >
								</p>
								<?php
							}
							if ( isset( $registrations['enable_shipping_address_2'] ) && 'yes' == $registrations['enable_shipping_address_2'] ) {
								?>
								<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
									<label for="wwp_wholesaler_shipping_address_line_2"><?php echo ! empty( $registrations['shipping_address_2'] ) ? esc_html( $registrations['shipping_address_2'] ) : esc_html__( 'Address line 2', 'woocommerce-wholesale-pricing' ); ?></label>
									<input type="text" name="wwp_wholesaler_shipping_address_line_2" id="wwp_wholesaler_shipping_address_line_2" >
								</p>
								<?php
							}
							if ( isset( $registrations['enable_shipping_city'] ) && 'yes' == $registrations['enable_shipping_city'] ) {
								?>
								<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
									<label for="wwp_wholesaler_shipping_city"><?php echo ! empty( $registrations['shipping_city'] ) ? esc_html( $registrations['shipping_city'] ) : esc_html__( 'City', 'woocommerce-wholesale-pricing' ); ?> <span class="required">*</span></label>
									<input type="text" name="wwp_wholesaler_shipping_city" id="wwp_wholesaler_shipping_city" >
								</p>
								<?php
							}
							if ( isset( $registrations['enable_shipping_post_code'] ) && 'yes' == $registrations['enable_shipping_post_code'] ) {
								?>
								<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
									<label for="wwp_wholesaler_shipping_post_code"><?php echo ! empty( $registrations['shipping_post_code'] ) ? esc_html( $registrations['shipping_post_code'] ) : esc_html__( 'Postcode / ZIP', 'woocommerce-wholesale-pricing' ); ?> <span class="required">*</span></label>
									<input type="text" name="wwp_wholesaler_shipping_post_code" id="wwp_wholesaler_shipping_post_code">
								</p>
								<?php
							}
							if ( isset( $registrations['enable_shipping_country'] ) && 'yes' == $registrations['enable_shipping_country'] ) {
								?>
								<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
								<?php
									woocommerce_form_field(
										'shipping_country',
										array(
											'type'        => 'country',
											'class'       => array( 'chzn-drop' ),
											'label'       => esc_html__( 'Select shipping country', 'woocommerce-wholesale-pricing' ),
											'placeholder' => esc_html__( 'Enter something', 'woocommerce-wholesale-pricing' ),
											'default'     => $default_country,
											'options'     => $countries,
										)
									);
								?>
								</<?php wwp_elements( 'p' ); ?>>
								
								<?php
							}
							if ( isset( $registrations['enable_shipping_state'] ) && 'yes' == $registrations['enable_shipping_state'] ) {
								?>
								<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
									 
									<?php 
										woocommerce_form_field(
											'shipping_state',
											array(
												'type'       => 'state',
												'class'      => array( 'chzn-drop' ),
												'label'      => ! empty( $registrations['shipping_state'] ) ? esc_html__( __( $registrations['shipping_state'] ), 'woocommerce-wholesale-pricing' ) : esc_html__( 'State / County', 'woocommerce-wholesale-pricing' ),
												'options'     => $default_county_states,
												)
										);
									?>
								</<?php wwp_elements( 'p' ); ?>>
								
								<?php
							}
							?>
						</div>
						<?php
					}
					if ( isset( $registrations['enable_tex_id'] ) && 'yes' == $registrations['enable_tex_id'] ) {
						$required = ( ! empty( $registrations['required_tex_id'] ) && 'yes' == $registrations['required_tex_id'] ) ? 'required' : '';
						?>
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="wwp_wholesaler_tax_id">
								<?php echo ! empty( $registrations['woo_tax_id'] ) ? esc_html( $registrations['woo_tax_id'] ) : esc_html__( 'Tax ID', 'woocommerce-wholesale-pricing' ); ?>
								<?php
								if ( 'required' == $required ) {
									echo '<span class="required">*</span>';
								}
								?>
							</label>
							<input type="text" name="wwp_wholesaler_tax_id" id="wwp_wholesaler_tax_id" <?php esc_attr_e( $required ); ?>>
						</p>
						<?php
					}
					if ( isset( $registrations['enable_file_upload'] ) && 'yes' == $registrations['enable_file_upload'] ) {
						$required = ( ! empty( $registrations['required_file_upload'] ) && 'yes' == $registrations['required_file_upload'] ) ? 'required' : '';
						?>
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="wwp_wholesaler_file_upload"><?php echo ! empty( $registrations['woo_file_upload'] ) ? esc_html( $registrations['woo_file_upload'] ) : esc_html__( 'File Upload', 'woocommerce-wholesale-pricing' ); ?>
							<?php
							if ( 'required' == $required ) {
								echo '<span class="required">*</span>';
							}
							?>
							</label>
							<input type="file" name="wwp_wholesaler_file_upload" id="wwp_wholesaler_file_upload" <?php esc_attr_e( $required ); ?> value="">
						</p>
						<?php
					}
					echo render_form_builder('get_option', '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

					?>
					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">                  
					<?php
					if ( isset( get_option( 'anr_admin_options' )['enabled_forms'] ) ) {
						if ( in_array( 'wwp_wholesale_recaptcha', get_option( 'anr_admin_options' )['enabled_forms'] ) ) {
						
							/**
							* Hooks
							*
							* @since 3.0
							*/
							do_action( 'anr_captcha_form_field' );
						}
					}
					?>
					</p>
					<p class="woocomerce-FormRow form-row">                   
						<input type="submit" class="woocommerce-Button button" name="wwp_register_upgrade" value="<?php esc_html_e( 'Register', 'woocommerce-wholesale-pricing' ); ?>">
					</p>
				</form>
			</div>
			<?php
			return ob_get_clean();
		}

		public function wwp_script_style() {
			wp_enqueue_script( 'wwp-script', WWP_PLUGIN_URL . 'assets/js/script.js', array( 'jquery' ), '1.0.0', true );
			wp_enqueue_style( 'wwp-wholesale', WWP_PLUGIN_URL . 'assets/css/wwp-css-script.css', array(), '1.1.0', false );

			wp_register_style( 'wwp_dataTables', WWP_PLUGIN_URL . 'assets/css/jquery.dataTables.min.css', array(), '1.0.0', false  );
			wp_register_script( 'wwp_dataTables', WWP_PLUGIN_URL . 'assets/js/jquery.dataTables.min.js', array( 'jquery' ), '1.1.0', false  );
			wp_enqueue_style( 'wwp_dataTables' );
			wp_enqueue_script( 'wwp_dataTables' );

			$settings           = get_option( 'wwp_wholesale_pricing_options', true );
			$global_settings    = get_option( 'wholesale_multi_user_pricing' );
			$role               = get_current_user_role_id();

			unset( $settings['wholesale_css'] );

			if ( is_object( wc_get_product( get_the_id() ) ) ) {
				$product = wc_get_product( get_the_id() );
				$type    = $product->get_type();
			}
			
			if (empty($type)) {
				$type ='';
			}

			$wwp_wholesale_pricing = array(
				'ajaxurl'                => admin_url( 'admin-ajax.php' ),
				'ajax_nonce'             => wp_create_nonce( 'wwp_wholesale_pricing' ),
				'product_type'           => $type,
				'product_id'             => get_the_id(),
				'plugin_url'             => WWP_PLUGIN_URL,
				'currency_symbol'        => get_woocommerce_currency_symbol(),
				'wwp_wholesale_settings' => $settings,
				'wc_get_cart_url'        => wc_get_cart_url(),
				'payment_methods'        => wwp_get_active_payment_methods(),
				'min_subtotal_enabled'   => ( bool ) ( isset( $global_settings[$role] ) && isset( $global_settings[$role]['min_subtotal'] ) && ! empty( $global_settings[$role]['min_subtotal'] ) && 0 < $global_settings[$role]['min_subtotal'] ),
				/**
				* Hooks
				*
				* @since 3.0
				*/
				'check_subtotal'   => apply_filters('wwp_check_subtotal', false ),
			);
			wp_localize_script( 'wwp-script', 'wwpscript', $wwp_wholesale_pricing );
		}

		public function woocommerce_edit_account_form_extra_fields() {
			$registrations = get_option( 'wwp_wholesale_registration_options' );
			if ( isset( $registrations['display_fields_myaccount'] ) && 'yes' == $registrations['display_fields_myaccount'] ) {
				wp_nonce_field( 'wwp_my_account_registration_extra_fields', 'wwp_my_account_registration_extra_fields' );
				echo render_form_builder( 'get_user_meta', get_current_user_id() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}

		public function my_account_details_form_extra_fields_saved( $user_id ) {
			$post = wwp_get_post_data();
			if ( !isset( $post['wwp_my_account_registration_extra_fields'] ) ||  ( isset( $post['wwp_my_account_registration_extra_fields'] ) && ! wp_verify_nonce( wc_clean( $post['wwp_my_account_registration_extra_fields'] ), 'wwp_my_account_registration_extra_fields' ) && isset( $post['wwp_form_data_json'] ) ) ) {
				return;
				// wp_die( esc_html__( 'Security Check: Nonce verification failed.', 'woocommerce-wholesale-pricing' ) );
			}

			form_builder_update_user_meta( $user_id, $post );
			
			update_user_meta( $user_id, 'wwp_form_data_json', wwp_get_post_data( 'wwp_form_data_json' ) );
		}
		
		/**
		 * Wwp_nonce_checkout_field
		 *
		 * @return void
		 */
		public function wwp_nonce_checkout_field() {
			wp_nonce_field( '_wwp_nonce', '_wwp_nonce' );
		}
		
		/**
		 * Wccs_hook_priorities_callback
		 *
		 * @param  mixed $priorities
		 * @return void
		 */
		public function wccs_hook_priorities_callback( $priorities ) {
			$priorities['woocommerce_product_get_price'] = 500;
			return $priorities;
		}
		
		/**
		 * Method reset_wholesale_pricing
		 *
		 * @return void
		 */
		public function reset_wholesale_pricing() {
			if ( filter_input( INPUT_GET, 'reset_pricing', FILTER_SANITIZE_SPECIAL_CHARS ) ) {
				// delete product data
				$products = wc_get_products( array( 'limit' => -1, 'return' => 'ids' ) );
				
				if ( ! empty( $products ) ) {
					foreach ( $products as $product_id ) {
						delete_post_meta( $product_id, 'product_tier_pricing' );
						delete_post_meta( $product_id, 'wholesale_multi_user_pricing' );
					}
				}

				// delete category data
				$categories = get_terms( array( 'taxonomy' => 'product_cat', 'fields' => 'ids' ) );
				if ( ! empty( $categories ) ) {
					foreach ( $categories as $term_id ) {
						delete_term_meta( $term_id, 'category_tier_pricing' );
						delete_term_meta( $term_id, 'wholesale_multi_user_pricing' );
					}
				}

				// delete global pricing
				delete_option( 'wholesale_multi_user_pricing' );
			}
		}
	}
	new Wwp_Wholesale_Pricing_Frontend();
}
