<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Class to handle backend functionality
 */
if ( ! class_exists( 'WWP_Wholesale_Pricing_Backend' ) ) {

	class WWP_Wholesale_Pricing_Backend extends WWP_Wholesale_Reports {

		public function __construct() {
			$settings = get_option( 'wwp_wholesale_pricing_options', true );
			add_action( 'admin_menu', array( $this, 'wwp_register_custom_menu_page' ), 10 );
			add_action( 'admin_menu', array( $this, 'wwp_register_custom_menu_page_2' ), 200 );
			add_action( 'admin_init', array( $this, 'wwp_request_options' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'wwp_admin_script_style' ) );
			/**
			 * C4WP Integration Settings
			 */
			add_filter( 'c4wp_settings_fields', array( $this, 'wwp_wholesale_recaptcha' ) );
			/**
			 * ReCaptcha for WooCommerce Integration Settings
			 */
			add_filter('woocommerce_get_sections_i13_woo_recaptcha', array( $this, 'wwp_register_tab_i13_woo' ) );
			add_filter('woocommerce_get_settings_i13_woo_recaptcha', array( $this, 'wwp_recaptcha_setting_i13_woo' ), 10, 2);
			
			add_action( 'wp_ajax_register_redirect', array( $this, 'register_redirect' ) );
			add_action( 'wp_ajax_nopriv_register_redirect', array( $this, 'register_redirect' ) );
			
			add_action( 'wp_ajax_WWP_requisition_list_template_new', array( $this, 'WWP_requisition_list_template_new' ) );
			add_action( 'wp_ajax_nopriv_WWP_requisition_list_template_new', array( $this, 'WWP_requisition_list_template_new' ) );
			
			add_filter( 'woocommerce_product_data_tabs', array( $this, 'wwp_add_wholesale_product_data_tab' ), 99, 1 );
			add_action( 'admin_head', array( $this, 'wcpp_custom_style' ) );
			add_filter( 'woocommerce_screen_ids', array( $this, 'wwp_screen_ids' ), 10, 1 );

			add_action( 'add_meta_boxes', array( $this, 'wwp_register_meta_box' ) );

			add_action( 'save_post_shop_order', array( $this, 'update_order_wwp_form_data_json_value' ) );
			add_action( 'restrict_manage_posts', array( $this, 'shop_order_user_role_filter' ) );
			add_action( 'woocommerce_order_list_table_restrict_manage_orders', array( $this, 'shop_order_user_role_filter' ) );
			add_filter( 'pre_get_posts', array( $this, 'shop_order_user_role_posts_where' ) );
			add_action( 'woocommerce_product_data_panels', array( $this, 'wwp_add_wholesale_product_data_fields_multi' ) );
			add_action( 'woocommerce_process_product_meta', array( $this, 'wwp_woo_wholesale_fields_save_multi' ), 99 );
			
			add_filter( 'manage_edit-product_columns', array( $this, 'change_columns_filter' ), 10, 1 );
			add_action( 'manage_product_posts_custom_column', array( $this, 'manage_product_posts_custom_column' ), 10, 2 );
			
			add_filter( 'woocommerce_coupon_data_tabs', array( $this, 'woocommerce_coupon_data_tabs' ), 10, 1 );
			add_action( 'woocommerce_coupon_data_panels', array( $this, 'woocommerce_coupon_data_panels' ) );
			add_action( 'woocommerce_coupon_options_save', array( $this, 'woocommerce_coupon_options_save' ) );
			
			add_action( 'wp_ajax_wwp_get_datatable', array( $this, 'wwp_get_datatable' ) );
			add_action( 'wp_ajax_nopriv_wwp_get_datatable', array( $this, 'wwp_get_datatable' ) );
			
			add_action( 'wp_ajax_select2_get_ajax_callback', array( $this, 'select2_get_ajax_callback' ) );  
			add_action( 'wp_ajax_nopriv_select2_get_ajax_callback', array( $this, 'select2_get_ajax_callback' ) );  
			
			add_action( 'wp_ajax_requisition_open_list_edit', array( $this, 'requisition_open_list_edit' ) );  
			add_action( 'wp_ajax_nopriv_requisition_open_list_edit', array( $this, 'requisition_open_list_edit' ) );  
			
			add_action( 'wp_ajax_WWP_requisition_list_template_edit', array( $this, 'WWP_requisition_list_template_edit' ) );  
			add_action( 'wp_ajax_nopriv_WWP_requisition_list_template_edit', array( $this, 'WWP_requisition_list_template_edit' ) );
			
			add_action( 'wp_ajax_requisition_list_add_to_cart', array( $this, 'requisition_list_add_to_cart' ) );  
			add_action( 'wp_ajax_nopriv_requisition_list_add_to_cart', array( $this, 'requisition_list_add_to_cart' ) );
				
			add_action( 'wp_ajax_requisition_list_delete', array( $this, 'requisition_list_delete' ) );  
			
			add_action( 'wp_ajax_requisition_list_add_cart_page', array( $this, 'requisition_list_add_cart_page' ) );  
			add_action( 'wp_ajax_nopriv_requisition_list_add_cart_page', array( $this, 'requisition_list_add_cart_page' ) );

			add_filter( 'woocommerce_order_list_table_prepare_items_query_args', array( $this, 'wwp_woocommerce_order_query_filter' ), 10 );
		}

		public function wwp_woocommerce_order_query_filter( $query_args ) {
			$type = filter_input( INPUT_GET, '_user_role', FILTER_SANITIZE_SPECIAL_CHARS );

			if ( ! empty( $type ) ) {
				$wholesale_user_ids = array();
				$all_wholesale_role = array();

				$allterms = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
				foreach ( $allterms as $allterm_value ) {
					$all_wholesale_role[] = $allterm_value->slug;
				}

				$user_args = array(
					'role__in' => $all_wholesale_role,
					'fields'   => 'ID',
				);

				if ( 'non-Wholesaler' === $type ) {
					$user_args['role__not_in'] = $all_wholesale_role;
					unset( $user_args['role__in'] );

					$wholesale_user_ids = get_users( $user_args );
					if ( ! empty( $wholesale_user_ids ) ) {
						$query_args['customer_id'] = $wholesale_user_ids;
						$query_args['customer_id'][] = 0; // Add guest orders
					} else {
						$query_args['customer_id'] = array( 0 );
					}
				} else {
					$wholesale_user_ids = get_users( $user_args );

					if ( ! empty( $wholesale_user_ids ) ) {
						$query_args['customer_id'] = $wholesale_user_ids;
					} else {
						$query_args['customer_id'] = array( -1 );
					}
				}
			}

			return $query_args;
		}
		
		public function requisition_list_add_cart_page() { 
			// $post_cart = wwp_get_post_data( 'cart' );
			$arraydata = array();
			if ( !empty(WC()->cart->get_cart()) ) { 
				foreach ( WC()->cart->get_cart() as $key => $cart ) { 
					$data = WC()->cart->get_cart_item( $key );
					$rand = rand(120, 11100333);
					if ( 0 == $data['variation_id'] ) {
						$arraydata[$rand]['wwp_product_id'] = $data['product_id'];
					} else {
						$arraydata[$rand]['wwp_product_id'] = $data['variation_id'];
					}
					$arraydata[$rand]['wwp_product_qty']   = $cart['quantity'];
					$arraydata[$rand]['wwp_product_price'] = $data['data']->get_price();
				}
	
				$wwp_requisition = array(
					'post_title'   => esc_html(wwp_get_post_data('list_name')),
					'post_type' => 'wp_requisition_list',
					'post_author'   => 1,
					'meta_input'   => array(
						'requisition' => $arraydata,
						'user_id' => get_current_user_id(),
					),
				);
				
				$id = wp_insert_post( $wwp_requisition );
				if ( !is_wp_error($id) ) { 
					echo 'Successfully Added';
				} else {
					echo wp_kses_post( $id->get_error_message() ) ;
				}
			} else {
				echo 'Failed';
			}
			wp_die();
		}
		
		public function requisition_list_delete() {
			if ( ! is_user_logged_in() ) {
				return;
			}
			wp_delete_post( wwp_get_post_data('post_id'), true); 
			wp_die('deleted');
		}
		
		public function requisition_list_add_to_cart() { 
			$data = wwp_get_post_data();
			foreach ( $data['requisition'] as $list ) {
				if ( isset($list['wwp_product_id']) && !empty($list['wwp_product_id']) ) {
					WC()->cart->add_to_cart( $list['wwp_product_id'], $list['wwp_product_qty'] );
				}
			}
			wp_die('done');
		}
		
		public function requisition_open_list_edit() {
			include_once WWP_PLUGIN_PATH . 'inc/template/requisition-list-template-edit.php';
			wp_die();
		}
			
		public function select2_get_ajax_callback() {

			include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-multiuser.php';
			include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-frontend.php';
			include_once WWP_PLUGIN_PATH . 'inc/class-wwp-products-visibility.php';
			$return =array();
			$args   = array( 
				'post_type' =>  array( 'product', 'product_variation' ),
				'post_status' => array( 'publish' ),
				'posts_per_page' => '-1',
			);
			
			if ( 'productname' == wwp_get_get_data('bytype') ) { 
				$args['s'] = wwp_get_get_data('q');
			} else {
				$args['meta_query'] = 
						array(
							array(
							'key'     => '_sku',
							'value'   =>  wwp_get_get_data('q'),
							'compare' => '=',
							),
						);
			}   
			$search_results = get_posts($args);
			if ( $search_results ) :
				foreach ( $search_results as $post ) : 
					$product  = wc_get_product( $post->ID );
					$title    = ( mb_strlen( $post->post_title ) > 50 ) ? mb_substr( $post->post_title, 0, 49 ) . '...' : $post->post_title;
					$return[] = array( $post->ID, $title, wwp_get_price_including_tax_for_requisition( $product, array( 'price' => $product->get_price() ) ) ); 
				endforeach;
			endif;
			echo json_encode( $return );
			wp_die();
		}
		
		public function WWP_requisition_list_template_edit() {
			$postdata        = wwp_get_post_data();
			$wwp_requisition = array(
				'ID' => $postdata['post_id'],
				'post_title'   => esc_html($postdata['wwp_list_name']),
				'post_type' => 'wp_requisition_list',
				'post_author'   => 1,
				'meta_input'   => array(
					'requisition' => $postdata['requisition'],
					'user_id' => get_current_user_id(),
				),
			);
			
			$id = wp_update_post( $wwp_requisition );
			if ( !is_wp_error($id) ) {
				echo 'updated';
			} else {
				echo wp_kses_post( $id->get_error_message() ) ;
			}
	 
			wp_die();
		}
		public function WWP_requisition_list_template_new() {
			$psotdata        = wwp_get_post_data();
			$wwp_requisition = array(
				'post_title'   => esc_html($psotdata['wwp_list_name']),
				'post_type' => 'wp_requisition_list',
				'post_author'   => 1,
				'meta_input'   => array(
					'requisition' => $psotdata['requisition'],
					'user_id' => get_current_user_id(),
				),
			);
			
			$id = wp_insert_post( $wwp_requisition );
			if ( !is_wp_error($id) ) {
				echo 'updated';
			} else {
				echo wp_kses_post( $id->get_error_message() ) ;
			}
			wp_die();
		}
		
		public function wwp_get_datatable() {
			global $wpdb;
			$args        = array(
			  'post_type'             => 'wp_requisition_list',
			  'posts_per_page'        => -1,
			  'meta_query'=> array(
				array(
					'key' => 'user_id',
					'value' => get_current_user_id(),
				 ),
			  ),
			);
			$postsQ      = new WP_Query( $args );
			$return_json = array();
			
			
			while ( $postsQ->have_posts() ) { 
				$postsQ->the_post();
				$i           =0;
				$count_lists = get_post_meta(  get_the_ID() , 'requisition', true );
				foreach ( $count_lists as $key => $count_list ) {
					
					if (isset($count_list['wwp_product_id'])) {
						$i++;
					}
				}
				$row           = array(
					'title' => get_the_title(),
					'count_list' => $i . ' items',
					'id' => get_the_ID(),
				);
				$return_json[] = $row;
			}
			echo json_encode(array( 'data' => $return_json ));
			wp_die();
		}
		
		public function woocommerce_coupon_data_tabs( $tabs ) {
			$tabs['wwp_wholesale'] = array( 
				'label'  => __( 'Wholesale', 'woocommerce-wholesale-pricing' ), 
				'target' => 'wwp_wholesale_data', 
				'class'  => 'wwp_wholesale_data', 
			); 
			return $tabs; 
		}
		
		public function woocommerce_coupon_data_panels() { 
			global $post;
			$coupon_id = $post->ID;
			$roles     = array();
			$taxroles  =  get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
			if ( ! empty( $taxroles ) ) {
				foreach ( $taxroles as $key => $role ) {
					$roles[ $role->slug ] = $role->name;
				}
			}
			?>
			<div id="wwp_wholesale_data" class="panel woocommerce_options_panel">
				<?php 
				woocommerce_wp_checkbox( 
					array(
						'id' => 'wwp_coupon_enable',
						'label' => esc_html__( 'Enable Wholesale', 'woocommerce-wholesale-pricing' ),
						'description' => esc_html__( 'Enable to restrict user roles for this coupons.', 'woocommerce-wholesale-pricing'),
					) 
				); 
				
				woocommerce_wp_select(
					array(
						'id' => 'wwp_coupon_type',
						'label' => esc_html__( 'Type', 'woocommerce-wholesale-pricing' ),
						'options' => 
									array(
										'included' => esc_html__( 'Included', 'woocommerce-wholesale-pricing' ),
										'excluded' => esc_html__( 'Excluded', 'woocommerce-wholesale-pricing' ),
									),
						)
				);
				
				$value = get_post_meta( $coupon_id, 'wwp_coupon_roles', true );      
				woocommerce_wp_select(
					array(
						'id'                => 'wwp_coupon_roles[]',
						'label'             => esc_html__( 'User Roles', 'woocommerce-wholesale-pricing' ),
						'type'              => 'select',
						'class'             => 'wc-enhanced-select',
						'style'             => 'min-width: 50%;',
						'options'           => $roles,
						'value'             => $value,
						'custom_attributes' => array(
							'multiple' => 'multiple',
						),
					)
				); 
				
				woocommerce_wp_textarea_input(
					array(
						'id' => 'wwp_coupon_not_allowed_message',
						'placeholder' => esc_html__( 'You are not allowed to use this coupon code.', 'woocommerce-wholesale-pricing' ),
						'label' => __('Invalid user role message', 'woocommerce-wholesale-pricing'),
					)
				);
				
				?>
			</div>
			<?php
		}
		
		public function woocommerce_coupon_options_save( $post_id = null ) { 
			$post              = wwp_get_post_data('');
			$coupon = new WC_Coupon($post_id);

			$wwp_coupon_enable = isset( $post['wwp_coupon_enable'] ) ? wc_clean( $post['wwp_coupon_enable'] ) : '';
			//update_post_meta( $post_id, 'wwp_coupon_enable', $wwp_coupon_enable );
			$coupon->update_meta_data('wwp_coupon_enable', $wwp_coupon_enable);
			
			$wwp_coupon_type = isset( $post['wwp_coupon_type'] ) ? wc_clean( $post['wwp_coupon_type'] ) : '';
			//update_post_meta( $post_id, 'wwp_coupon_type', $wwp_coupon_type );    
			$coupon->update_meta_data('wwp_coupon_type', $wwp_coupon_type);
			
			$wwp_coupon_roles = isset( $post['wwp_coupon_roles'] ) ? wc_clean( $post['wwp_coupon_roles'] ) : '';
			//update_post_meta( $post_id, 'wwp_coupon_roles', $wwp_coupon_roles );
			$coupon->update_meta_data('wwp_coupon_roles', $wwp_coupon_roles);
			
			$wwp_coupon_not_allowed_message = isset( $post['wwp_coupon_not_allowed_message'] ) ? wc_clean( $post['wwp_coupon_not_allowed_message'] ) : '';
			//update_post_meta( $post_id, 'wwp_coupon_not_allowed_message', $wwp_coupon_not_allowed_message );
			$coupon->update_meta_data('wwp_coupon_not_allowed_message', $wwp_coupon_not_allowed_message);
			
			$coupon->save();
		}
		
		public function change_columns_filter( $columns ) {
			$columns['Wholesale'] = esc_html__( 'Wholesale Pricing On', 'woocommerce-wholesale-pricing' );
			return $columns; 
		}
		
		public function manage_product_posts_custom_column( $column, $product_id ) {
			if ( 'Wholesale' == $column ) {
				$allterms = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
				foreach ( $allterms as $allterm ) { 
					$role = $allterm->term_id;
					$data = get_post_meta( $product_id, 'wholesale_multi_user_pricing', true );
					if ( isset( $data[ $role ] ) ) { 
						?>
						<button class="wholesale_on button">
							<div class="border_on"></div><?php echo esc_attr($allterm->name); ?> Product
						</button>
						<?php 
					}
					$terms = get_the_terms( $product_id, 'product_cat' );
					if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
						foreach ( $terms as $term ) {
							$data = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );
							if ( isset( $data[ $role ] ) && isset( $data[ $role ]['discount_type'] ) && isset( $data[ $role ]['wholesale_price'] ) ) {
								?>
								<button class="wholesale_on button">
									<div class="border_on"></div><?php echo esc_attr($allterm->name); ?> Category
								</button>
								<?php  
							}
						}
					}
					$data = get_option( 'wholesale_multi_user_pricing' );
					if ( isset( $data[ $role ] ) && isset( $data[ $role ]['discount_type'] ) && isset( $data[ $role ]['wholesale_price'] ) ) {
						?>
							<button class="wholesale_on button">
								<div class="border_on"></div><?php echo esc_attr($allterm->name); ?> Global
							</button>						
						<?php 
					}   

				}
			}
		}
		
		public function register_redirect() {
		
			$wwp_get_get_data = wwp_get_get_data();
			if ( isset($wwp_get_get_data['sample_product']) ) {
				$post_type = array( 'product' );
			} else {
				$post_type = array( 'page', 'product' );
			}

			$args     = array(
				'posts_per_page' => -1,
				'post_type'      => $post_type,
				'post_status'    => 'publish',
				's'              => wwp_get_get_data( 'name' ),
			);
			$query    = get_posts( $args );
			$response = array();
			foreach ( $query as $r ) {
				$post_title = $r->post_title . ' - (' . $r->ID . ')';
				$response[] = array(
					'value' => $r->ID,
					'label' => $post_title,
				);
			}
			echo wp_kses_post( wwp_get_get_data( 'callback' ) . '(' . json_encode( $response ) . ')' );
			die();
		}

		public function shop_order_user_role_filter() {

			if ( isset( $_GET['post_type'], $_GET['page'] ) && ( 'wc-orders' !== $_GET['page'] || 'shop_order' != $_GET['post_type'] ) ) {
				return;
			}

			if ( ! empty( $_GET['_user_role'] ) ) {
				$user_role = sanitize_text_field( $_GET['_user_role'] );
			} else {
				$user_role = '';
			}
			?>
			<select name="_user_role" class="">
				<option value=""><?php esc_html_e( 'All', 'woocommerce-wholesale-pricing' ); ?></option>
				<option <?php selected( $user_role, 'Wholesaler' ); ?> value="Wholesaler"><?php esc_html_e( 'Wholesaler', 'woocommerce-wholesale-pricing' ); ?></option>
				<option <?php selected( $user_role, 'non-Wholesaler' ); ?> value="non-Wholesaler"><?php esc_html_e( 'Non - Wholesaler', 'woocommerce-wholesale-pricing' ); ?></option>
			</select>
			<?php
		}

		public function shop_order_user_role_posts_where( $query ) {

			if ( ! $query->is_main_query() || ! isset( $_GET['_user_role'] ) ) {
				return;
			}
			$wholesale_user_ids = array();
			$all_wholesale_role = array();
			$allterms           = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
			foreach ( $allterms as $allterm_key => $allterm_value ) {
				array_push( $all_wholesale_role, $allterm_value->slug );
			}

			$wholesale_user_ids = get_users(
				array(
					'role__in' => $all_wholesale_role,
					'fields'   => 'ID',
				)
			);

			switch ( $_GET['_user_role'] ) {
				case 'Wholesaler':
					$query->set(
						'meta_query',
						array(
							array(
								'key'     => '_customer_user',
								'compare' => 'IN',
								'value'   => $wholesale_user_ids,
							),
						)
					);
					break;
				case 'non-Wholesaler':
					$query->set(
						'meta_query',
						array(
							array(
								'key'     => '_customer_user',
								'compare' => 'NOT IN',
								'value'   => $wholesale_user_ids,
							),
						)
					);
					break;
			}
			if ( empty( $wholesale_user_ids ) ) {
				$query->set( 'posts_per_page', 0 );
			}
		}



		public function wwp_woo_wholesale_fields_save_multi( $post_id ) {
			
			if ( ! isset( $_POST['wwp_product_wholesale_nonce'] ) || ( isset( $_POST['wwp_product_wholesale_nonce'] ) && ! wp_verify_nonce( wc_clean( $_POST['wwp_product_wholesale_nonce'] ), 'wwp_product_wholesale_nonce' ) ) ) {
				return;
			}

			$product = wc_get_product($post_id); 

			// hide product for customer
			$_wwp_hide_for_customer = isset( $_POST['_wwp_hide_for_customer'] ) ? wc_clean( $_POST['_wwp_hide_for_customer'] ) : '';
			//update_post_meta( $post_id, '_wwp_hide_for_customer', esc_attr( $_wwp_hide_for_customer ) );
			$product->update_meta_data('_wwp_hide_for_customer', esc_attr( $_wwp_hide_for_customer ) );
			// hide product for visitor
			$_wwp_hide_for_visitor = isset( $_POST['_wwp_hide_for_visitor'] ) ? wc_clean( $_POST['_wwp_hide_for_visitor'] ) : '';
			//update_post_meta( $post_id, '_wwp_hide_for_visitor', esc_attr( $_wwp_hide_for_visitor ) );
			$product->update_meta_data('_wwp_hide_for_visitor', esc_attr( $_wwp_hide_for_visitor ) );
			
			$wwp_icon_url = isset( $_POST['wwp_icon_url'] ) ? wc_clean( $_POST['wwp_icon_url'] ) : '';
			//update_post_meta( $post_id, 'wwp_icon_url', esc_attr( $wwp_icon_url ) );
			$product->update_meta_data('wwp_icon_url', esc_attr( $wwp_icon_url ) );
			
			$wwp_image_url = isset( $_POST['wwp_image_url'] ) ? wc_clean( $_POST['wwp_image_url'] ) : '';
			//update_post_meta( $post_id, 'wwp_image_url', esc_attr( $wwp_image_url ) );
			$product->update_meta_data('wwp_image_url', esc_attr( $wwp_image_url ) );
			
			$wwp_attachment_title = isset( $_POST['wwp_attachment_title'] ) ? wc_clean( $_POST['wwp_attachment_title'] ) : '';
			//update_post_meta( $post_id, 'wwp_attachment_title', esc_attr( $wwp_attachment_title ) );
			$product->update_meta_data('wwp_attachment_title', esc_attr( $wwp_attachment_title ) );
			
			$wwp_attachment_text_link = isset( $_POST['wwp_attachment_text_link'] ) ? wc_clean( $_POST['wwp_attachment_text_link'] ) : '';
			//update_post_meta( $post_id, 'wwp_attachment_text_link', esc_attr( $wwp_attachment_text_link ) );
			$product->update_meta_data('wwp_attachment_text_link', esc_attr( $wwp_attachment_text_link ) );
			
			$wwp_variation_explode_eneble = isset( $_POST['wwp_variation_explode_eneble'] ) ? wc_clean( $_POST['wwp_variation_explode_eneble'] ) : '';
			//update_post_meta( $post_id, 'wwp_variation_explode_eneble', esc_attr( $wwp_variation_explode_eneble ) );
			$product->update_meta_data('wwp_variation_explode_eneble', esc_attr( $wwp_variation_explode_eneble ) );
			
			$wwp_variation_attribute_with_qty = isset( $_POST['wwp_variation_attribute_with_qty'] ) ? wc_clean( $_POST['wwp_variation_attribute_with_qty'] ) : '';
			//update_post_meta( $post_id, 'wwp_variation_attribute_with_qty', esc_attr( $wwp_variation_attribute_with_qty ) );
			$product->update_meta_data('wwp_variation_attribute_with_qty', esc_attr( $wwp_variation_attribute_with_qty ) );
			
			$sample_product_id_hidden = isset( $_POST['sample_product_id_hidden'] ) ? wc_clean( $_POST['sample_product_id_hidden'] ) : '';
			//update_post_meta( $post_id, 'sample_product_id_hidden', esc_attr( $sample_product_id_hidden ) );
			$product->update_meta_data('sample_product_id_hidden', esc_attr( $sample_product_id_hidden ) );
			
			$sample_product_id = isset( $_POST['sample_product_id'] ) ? wc_clean( $_POST['sample_product_id'] ) : '';
			//update_post_meta( $post_id, 'sample_product_id', esc_attr( $sample_product_id ) );
			$product->update_meta_data('sample_product_id', esc_attr( $sample_product_id ) );
			
			$request_for_sample_enable = isset( $_POST['request_for_sample_enable'] ) ? wc_clean( $_POST['request_for_sample_enable'] ) : '';
			//update_post_meta( $post_id, 'request_for_sample_enable', esc_attr( $request_for_sample_enable ) );
			$product->update_meta_data('request_for_sample_enable', esc_attr( $request_for_sample_enable ) );
			
			$product->save();
		}

		public function update_order_wwp_form_data_json_value( $post_id ) {
			$wwp_form_data_json = wwp_get_post_data( 'wwp_form_data_json' );
			if ( isset( $wwp_form_data_json ) ) {
				$wwp_form_data_json = wc_clean( $wwp_form_data_json );
				update_post_meta( $post_id, 'wwp_form_data_json', $wwp_form_data_json );
			}
		}
		
		public function wwp_register_meta_box() {

			$registrations = get_option( 'wwp_wholesale_registration_options' );
			if ( isset( $registrations['display_fields_checkout'] ) && 'yes' == $registrations['display_fields_checkout'] ) {
				add_meta_box(
					'wwp_form_builder',
					esc_html__( 'Checkout Extra Fields Data', 'woocommerce-wholesale-pricing' ),
					array( $this, 'wwp_meta_box_callback' ),
					'shop_order',
					'advanced',
					'high'
				);
			}
		}

		public function wwp_meta_box_callback( $order_id ) {
			echo wp_kses_post( render_form_builder( 'get_post_meta', $order_id->ID ) );
		}
		
		/**
		 * Method wwp_wholesale_recaptcha
		 *
		 * @param array $fields
		 *
		 * @return void
		 */
		public function wwp_wholesale_recaptcha( $fields ) {

			$add                                = array( 'wwp_wholesale_recaptcha' => 'Wholesale Registration Form' );
			$fields['enabled_forms']['options'] = array_merge( $fields['enabled_forms']['options'], $add );
			return $fields;
		}
				
		/**
		 * Method wwp_wholesale_recaptcha_woo
		 *
		 * @param array $sections
		 *
		 * @return void
		 */
		public function wwp_register_tab_i13_woo( $sections ) {
			$sections['wwp_wholesale_recaptcha_woo'] = __('Wholesale Registration Form', 'woocommerce-wholesale-pricing');
			return $sections;
		}

		/**
		 * Method wwp_show_setting_recaptcha
		 *
		 * @param $settings
		 * @param $current_section
		 *
		 * @return void
		 */
		public function wwp_recaptcha_setting_i13_woo( $settings, $current_section ) {
			if ('wwp_wholesale_recaptcha_woo' == $current_section && class_exists( 'I13_Woo_Recpatcha' ) ) {
				/**
				*  Filter: filter recaptcha settings 
				*          
				*  @since 2.4.0 
				*/
				$settings = apply_filters( 'i13_woo_wholesale_recaptcha_settings', array(
						'section_title_recpacha_on_woo_wholesale' => array(
							'name' => __('Recaptcha on Wholesale Registration Form', 'woocommerce-wholesale-pricing'),
							'type' => 'title',
							'desc' => '',
							'id' => 'wwp_settings_tab_recapcha_woo_wholesale',
						),
						'wwp_recapcha_enable_on_woo_wholesale' => array(
							'name' => __('Enable Recaptcha on Wholesale Registration Form', 'woocommerce-wholesale-pricing'),
							'type' => 'checkbox',
							'id' => 'wwp_recapcha_enable_on_woo_wholesale',
						), 
						array(
							'type' => 'sectionend',
							'id' => 'wwp_settings_tab_recapcha_woo_wholesale',
						),
					)
				);
			}
			return $settings;
		}

		public function wwp_request_options() {
			register_setting( 'wwp_wholesale_request_notifications', 'wwp_wholesale_admin_request_notification' );
			register_setting( 'wwp_wholesale_request_notifications', 'wwp_wholesale_stop_admin_notification' );
			register_setting( 'wwp_wholesale_request_notifications', 'wwp_wholesale_send_notification_email_other' );
			register_setting( 'wwp_wholesale_request_notifications', 'wwp_wholesale_admin_request_subject' );
			register_setting( 'wwp_wholesale_request_notifications', 'wwp_wholesale_admin_request_recipient' );
			register_setting( 'wwp_wholesale_request_notifications', 'wwp_wholesale_admin_request_body' );
			register_setting( 'wwp_wholesale_request_notifications', 'wwp_wholesale_request_approve_notification' );
			register_setting( 'wwp_wholesale_request_notifications', 'wwp_wholesale_email_request_subject' );
			register_setting( 'wwp_wholesale_request_notifications', 'wwp_wholesale_email_request_body' );

			register_setting( 'wwp_wholesale_request_notifications', 'wwp_wholesale_user_registration_notification' );
			register_setting( 'wwp_wholesale_request_notifications', 'wwp_wholesale_registration_notification_subject' );
			register_setting( 'wwp_wholesale_request_notifications', 'wwp_wholesale_registration_notification_body' );

			register_setting( 'wwp_wholesale_request_notifications', 'wwp_wholesale_user_rejection_notification' );
			register_setting( 'wwp_wholesale_request_notifications', 'wwp_wholesale_rejection_notification_subject' );
			register_setting( 'wwp_wholesale_request_notifications', 'wwp_wholesale_rejection_notification_body' );

			/**
			* Version 1.3.0 For Subscriptions User Role Update Notification
			*
			* @since 1.3.0
			*/
			if ( in_array( 'woocommerce-subscriptions/woocommerce-subscriptions.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				register_setting( 'wwp_wholesale_request_notifications', 'wwp_wholesale_subscription_role_notification' );
				register_setting( 'wwp_wholesale_request_notifications', 'wwp_wholesale_subscription_role_subject' );
				register_setting( 'wwp_wholesale_request_notifications', 'wwp_wholesale_subscription_role_body' );
			}
			// ends version 1.3.0
			$this->upgrade_plugin_fixes();
		}

		public function upgrade_plugin_fixes() {
			$settings = get_option( 'wwp_wholesale_pricing_options', true );
			if ( isset( $settings['upgrade_btn_label'] ) && ! empty( $settings['upgrade_btn_label'] ) ) {
				$settings['upgrade_tab_text']  = $settings['upgrade_btn_label'];
				$settings['upgrade_btn_label'] = '';
				update_option( 'wwp_wholesale_pricing_options', $settings );
			}
		}

		public function wwp_register_custom_menu_page() {
			/**
			* Hooks
			*
			* @since 3.0
			*/
			$check = apply_filters( 'wwp_wholesales_menus', true );
			if ($check) {
				add_menu_page(
					esc_html__( 'Wholesale Pricing', 'woocommerce-wholesale-pricing' ),
					esc_html__( 'Wholesale', 'woocommerce-wholesale-pricing' ),
					'manage_wholesale',
					'wwp_wholesale',
					array( $this, 'wwp_wholesale_reports_callback' ),
					'dashicons-store',
					51
				);
				add_submenu_page(
					'wwp_wholesale',
					esc_html__( 'Wholesale For WooCommerce', 'woocommerce-wholesale-pricing' ),
					esc_html__( 'Dashboard', 'woocommerce-wholesale-pricing' ),
					'manage_wholesale_reports',
					'wwp_wholesale',
					array( $this, 'wwp_wholesale_reports_callback' )
				);
				add_submenu_page(
					'wwp_wholesale',
					esc_html__( 'Wholesale Settings', 'woocommerce-wholesale-pricing' ),
					esc_html__( 'Settings', 'woocommerce-wholesale-pricing' ),
					'manage_wholesale_settings',
					'wwp_wholesale_settings',
					array( $this, 'wwp_wholesale_page_callback' )
				);
				add_submenu_page(
					'wwp_wholesale',
					esc_html__( 'User Roles', 'woocommerce-wholesale-pricing' ),
					esc_html__( 'User Roles', 'woocommerce-wholesale-pricing' ),
					'manage_wholesale_user_role',
					'edit-tags.php?taxonomy=wholesale_user_roles'
				);

				$settings = get_option( 'wwp_wholesale_pricing_options', true );
				$advance_registration_form = isset($settings['advance_registration_form']) ? sanitize_text_field($settings['advance_registration_form']) : 'no';

				if ( 'no' == $advance_registration_form ) {
					add_submenu_page(
						'wwp_wholesale',
						esc_html__( 'Notifications', 'woocommerce-wholesale-pricing' ),
						esc_html__( 'Notifications', 'woocommerce-wholesale-pricing' ),
						'manage_wholesale_notifications',
						'wwp_wholesale_notifcations',
						array( $this, 'wwp_wholesale_notifications_callback' )
					);
				}
	
				add_submenu_page(
					'wwp_wholesale',
					esc_html__( 'Import', 'woocommerce-wholesale-pricing' ),
					esc_html__( 'Import', 'woocommerce-wholesale-pricing' ),
					'manage_wholesale_import',
					'edit.php?post_type=product&page=product_importer'
				);
			}
		}

		public  function wwp_wholesale_reports_callback() {
			?>
			<div class="container remove-space">
			<br>
				<h2 class="text-center"><?php esc_html_e( 'Wholesale For WooCommerce', 'woocommerce-wholesale-pricing' ); ?></h2><hr>
			</div>
			<?php 
			// This hook call from reports page
			
			/**
			* Reports Section
			*
			* @since 3.0
			*/
			do_action( 'wwp_dashboard_reports', $this);
			
			/**
			* User Request Section
			*
			* @since 3.0
			*/
			do_action( 'wwp_dashboard_user_requests', $this);

			/**
			* Wholesale recent Order
			*
			* @since 3.0
			*/
			do_action( 'wwp_dashboard_recent_order', $this);
		}
		
		

		public function wwp_register_custom_menu_page_2() {
		
			/**
			* Hooks
			*
			* @since 3.0
			*/
			$check = apply_filters( 'wwp_wholesales_menus', true );
	
			if ( $check ) {
				add_submenu_page(
					'wwp_wholesale',
					esc_html__( 'Import', 'woocommerce-wholesale-pricing' ),
					esc_html__( 'Import', 'woocommerce-wholesale-pricing' ),
					'manage_options',
					'edit.php?post_type=product&page=product_importer'
				);
	
				add_submenu_page(
					'wwp_wholesale',
					esc_html__( 'Export', 'woocommerce-wholesale-pricing' ),
					esc_html__( 'Export', 'woocommerce-wholesale-pricing' ),
					'manage_options',
					'edit.php?post_type=product&page=product_exporter'
				);
			}
		}

		public static function wwp_wholesale_page_callback() {

			$settings = ! empty( get_option( 'wwp_wholesale_pricing_options' ) ) ? get_option( 'wwp_wholesale_pricing_options', true ) : array();
			if ( isset( $_POST['save-wwp_wholesale'] ) ) {
				if ( isset( $_POST['wwp_wholesale_register_nonce'] ) && wp_verify_nonce( wc_clean( $_POST['wwp_wholesale_register_nonce'] ), 'wwp_wholesale_register_nonce' ) ) {
					$settings                                 = isset( $_POST['options'] ) ? wc_clean( $_POST['options'] ) : '';
					$settings['enable_registration_page']     = isset( $settings['enable_registration_page'] ) ? 'yes' : 'no';
					$settings['wholesaler_allow_minimum_qty'] = isset( $settings['wholesaler_allow_minimum_qty'] ) ? 'yes' : 'no';
					$settings['restrict_store_access']        = isset( $settings['restrict_store_access'] ) ? 'yes' : 'no';
					$settings['over_right_wholesale_form']    = isset( $settings['over_right_wholesale_form'] ) ? 'yes' : 'no';
					$settings['wholesaler_prodcut_only']      = isset( $settings['wholesaler_prodcut_only'] ) ? 'yes' : 'no';
					$settings['enable_upgrade']               = isset( $settings['enable_upgrade'] ) ? 'yes' : 'no';
					$settings['disable_auto_role']            = isset( $settings['disable_auto_role'] ) ? 'yes' : 'no';
					$settings['retailer_disabled']            = isset( $settings['retailer_disabled'] ) ? 'yes' : 'no';
					$settings['save_price_disabled']          = isset( $settings['save_price_disabled'] ) ? 'yes' : 'no';
					$settings['enable_admin_new_order_item']  = isset( $settings['enable_admin_new_order_item'] ) ? 'yes' : 'no';
					// 2.6
					// hide add to cart button
					$settings['wwp_hide_add_to_cart']         = isset( $settings['wwp_hide_add_to_cart'] ) ? 'yes' : 'no';
					$settings['wwp_hide_add_to_cart_on']      = isset( $settings['wwp_hide_add_to_cart_on'] ) ? $settings['wwp_hide_add_to_cart_on'] : '';
					$settings['wwp_specific_product'] = isset( $settings['wwp_specific_product'] ) ? $settings['wwp_specific_product'] : array();
					
					$settings['advance_registration_form']     = isset($settings['advance_registration_form']) ? 'yes' : 'no';

					$settings['wwp_specific_cat'] = isset( $settings['wwp_specific_cat'] ) ? $settings['wwp_specific_cat'] : array();
					
					$settings['wwp_specific_user_role'] = isset( $settings['wwp_specific_user_role'] ) ? $settings['wwp_specific_user_role'] : array();

					$settings['custom_message_for_cart']      = isset( $settings['custom_message_for_cart'] ) ? $settings['custom_message_for_cart'] : '-';

					if ( ( 'specific_product' == $settings['wwp_hide_add_to_cart_on'] && empty( $settings['wwp_specific_product'] ) ) || ( 'specific_product_cat' == $settings['wwp_hide_add_to_cart_on'] && empty( $settings['wwp_specific_cat'] ) ) || ( 'specific_user_roles' == $settings['wwp_hide_add_to_cart_on'] && empty( $settings['wwp_specific_user_role'] ) ) ) {
						$settings['wwp_hide_add_to_cart_on'] = '';
						$settings['wwp_hide_add_to_cart'] = 'no';
						$settings['wwp_specific_cat'] = array();
						$settings['wwp_specific_user_role'] = array();
						$settings['wwp_specific_product'] = array();
					}

					if ( ( 'specific_product' == $settings['wwp_hide_add_to_cart_on'] && !empty( $settings['wwp_specific_product'] ) )  ) {
						$settings['wwp_specific_cat'] = array();
						$settings['wwp_specific_user_role'] = array();
					} elseif ( ( 'specific_product_cat' == $settings['wwp_hide_add_to_cart_on'] && !empty( $settings['wwp_specific_cat'] ) )  ) {
						$settings['wwp_specific_product'] = array();
						$settings['wwp_specific_user_role'] = array();
					} elseif ( ( 'specific_user_roles' == $settings['wwp_hide_add_to_cart_on'] && !empty( $settings['wwp_specific_user_role'] ) )  ) {
						$settings['wwp_specific_product'] = array();
						$settings['wwp_specific_cat'] = array();
					}

					// end hide to cart
					// hide price
					$settings['wwp_hide_price']           = isset( $settings['wwp_hide_price'] ) ? 'yes' : 'no';
					$settings['wwp_hide_price_on']        = isset( $settings['wwp_hide_price_on'] ) ? $settings['wwp_hide_price_on'] : '';
					$settings['wwp_specific_product_price'] = isset( $settings['wwp_specific_product_price'] ) ? $settings['wwp_specific_product_price'] : array();
					$settings['wwp_specific_cat_price'] = isset( $settings['wwp_specific_cat_price'] ) ? $settings['wwp_specific_cat_price'] : array();
					$settings['wwp_specific_user_role_price'] = isset( $settings['wwp_specific_user_role_price'] ) ? $settings['wwp_specific_user_role_price'] : array();
					$settings['custom_message_for_price']     = isset( $settings['custom_message_for_price'] ) ? $settings['custom_message_for_price'] : '-';

					if ( ( 'specific_product' == $settings['wwp_hide_price_on'] && empty( $settings['wwp_specific_product_price'] ) ) || ( 'specific_product_cat' == $settings['wwp_hide_price_on'] && empty( $settings['wwp_specific_cat_price'] ) ) || ( 'specific_user_roles' == $settings['wwp_hide_price_on'] && empty( $settings['wwp_specific_user_role_price'] ) ) ) {
						$settings['wwp_hide_price_on'] = '';
						$settings['wwp_hide_price'] = 'no';
						$settings['wwp_specific_product_price'] = array();
						$settings['wwp_specific_cat_price'] = array();
						$settings['wwp_specific_user_role_price'] = array();
					}

					if ( ( 'specific_product' == $settings['wwp_hide_price_on'] && !empty( $settings['wwp_specific_product_price'] ) ) ) {
						$settings['wwp_specific_cat_price'] = array();
						$settings['wwp_specific_user_role_price'] = array();
					} elseif ( ( 'specific_product_cat' == $settings['wwp_hide_price_on'] && !empty( $settings['wwp_specific_cat_price'] ) ) ) {
						$settings['wwp_specific_product_price'] = array();
						$settings['wwp_specific_user_role_price'] = array();
					} elseif ( ( 'specific_user_roles' == $settings['wwp_hide_price_on'] && !empty( $settings['wwp_specific_user_role_price'] ) ) ) {
						$settings['wwp_specific_product_price'] = array();
						$settings['wwp_specific_cat_price'] = array();
					}
					// end hide price
					// end 2.6
					$settings['enable_strike_through_price']  = isset( $settings['enable_strike_through_price'] ) ? 'yes' : 'no';
					$settings['enable_groups']                = isset( $settings['enable_groups'] ) ? 'yes' : 'no';
					$settings['payment_method_name']          = array();
					if ( isset( $_POST['options']['payment_method_name'] ) ) {
						$post = $_POST;
						foreach ( ( array ) $post['options']['payment_method_name'] as $key => $value ) {
							if ($value) {
								$settings['payment_method_name'][$value] = 'no';
							}
						}
					}
					update_option( 'wwp_wholesale_pricing_options', $settings );
					
					$roles         = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
					$data          = array();
					$cart_discount = array();
					if ( ! empty( $roles ) ) {
						foreach ( $roles as $key => $role ) {
							if ( isset( $_POST[ 'role_' . $role->term_id ] ) ) {

								if ( isset( $_POST[ 'role_' . $role->term_id ] ) ) {
									$data[ $role->term_id ]['slug'] = $role->slug;
								}
								if ( isset( $_POST[ 'discount_type_' . $role->term_id ] ) ) {
									$data[ $role->term_id ]['discount_type'] = wc_clean( $_POST[ 'discount_type_' . $role->term_id ] );
								}
								if ( isset( $_POST[ 'wholesale_price_' . $role->term_id ] ) ) {
									$data[ $role->term_id ]['wholesale_price'] = is_numeric( wc_clean( $_POST[ 'wholesale_price_' . $role->term_id ] ) ) ? wc_clean( $_POST[ 'wholesale_price_' . $role->term_id ] ) : '';
								}
								if ( isset( $_POST[ 'min_quatity_' . $role->term_id ] ) ) {
									$data[ $role->term_id ]['min_quatity'] = is_numeric( wc_clean( $_POST[ 'min_quatity_' . $role->term_id ] ) ) ? wc_clean( $_POST[ 'min_quatity_' . $role->term_id ] ) : 1;
								}
								if ( isset( $_POST[ 'min_subtotal_' . $role->term_id ] ) ) {
									$data[ $role->term_id ]['min_subtotal'] = is_numeric( wc_clean( $_POST[ 'min_subtotal_' . $role->term_id ] ) ) ? wc_clean( $_POST[ 'min_subtotal_' . $role->term_id ] ) : '';
								}

								if ( isset( $_POST[ 'exclude_categories_' . $role->term_id ] ) ) {
									$data[ $role->term_id ]['exclude_categories'] = ! empty( $_POST[ 'exclude_categories_' . $role->term_id ] ) ? array_map( 'intval', $_POST['exclude_categories_' . $role->term_id ] ) : array();
								}
							}
							if ( isset( $_POST[ 'role_' . $role->term_id . '_cart' ] ) ) {
								if ( isset( $_POST[ 'role_' . $role->term_id . '_cart' ] ) ) {
									$cart_discount[ $role->term_id ]['slug'] = $role->slug;
								}
								if ( isset($_POST['discount_type_' . $role->term_id . '_cart']) ) {
									$cart_discount[$role->term_id]['discount_type'] = wc_clean($_POST['discount_type_' . $role->term_id . '_cart']);
								}

								// 2.7 
								if ( isset($_POST['cart_price_' . $role->term_id ]) ) {
									$cart_discount[$role->term_id]['cart_price'] = wc_clean($_POST['cart_price_' . $role->term_id]);
								}
								if ( isset($_POST['discount_on_' . $role->term_id . '_cart']) ) {
									$cart_discount[$role->term_id]['discount_on'] = wc_clean($_POST['discount_on_' . $role->term_id . '_cart']);
								}
								if ( isset($_POST['min_subtotal_' . $role->term_id . '_cart']) ) {
									$cart_discount[$role->term_id]['cart_min_subtotal'] = wc_clean($_POST['min_subtotal_' . $role->term_id . '_cart']);
								}
							}
						}
					}
					if ( isset($_POST['wholesale_multi_user_cart_discount']) ) {
						$post         = wwp_get_post_data('');
						$tier_pricing = $post['wholesale_multi_user_cart_discount'];
						update_option( 'cart_tire_preces', $tier_pricing );
					}
					update_option( 'wholesale_multi_user_pricing', $data );
					update_option( 'wholesale_multi_user_cart_discount', $cart_discount );
				} else {
					wp_die( esc_html__( 'Security check', 'wholesale-for-woocommerce' ) );
				}
			}

			wp_enqueue_script( 'wc-enhanced-select' );
			wp_enqueue_style( 'wwp-select2' );
			
			?>
			<form action="" id="wwp-global-settings" method="post">
				<?php
					/**
					* Hooks
					*
					* @since 3.0
					*/
					$wwp_wholsale_plg_title = apply_filters( 'wwp_wholsale_plg_title', esc_html__( 'Wholesale For WooCommerce', 'woocommerce-wholesale-pricing' ) );
				?>
				<h2 class="text-center"><?php echo esc_attr( $wwp_wholsale_plg_title ); ?></h2><hr>
				<?php wp_nonce_field( 'wwp_wholesale_register_nonce', 'wwp_wholesale_register_nonce' ); ?>
				
				<div class="tab" role="tabpanel">
					<!-- Nav tabs -->
				<div class="row">
					<div class="col-md-3 col-sm-12">
							<?php 
							/**
							* Hooks
							*
							* @since 3.0
							*/
							$wwp_settings_tab_lists = apply_filters( 'wwp_settings_tab_lists', 
								array(
									'section1' => esc_html__( 'General', 'wwp-wholesale' ),
									'section1_5' => esc_html__( 'Subscription', 'wwp-wholesale' ),
									'section2' => esc_html__( 'Wholesale Price Global', 'wwp-wholesale' ),
									'section9' => esc_html__( 'Cart Total Discount', 'wwp-wholesale' ),
									'section3' => esc_html__( 'Labels', 'wwp-wholesale' ),
									'section4' => esc_html__( 'Login Restrictions', 'wwp-wholesale' ),
									'section5' => esc_html__( 'Product Visibility', 'wwp-wholesale' ),
									'section6' => esc_html__( 'Upgrade Settings', 'wwp-wholesale' ),
									'section10' => esc_html__( 'Payment Methods', 'wwp-wholesale' ),
									'section7' => esc_html__( 'Additional CSS', 'wwp-wholesale' ),
									'section8' => esc_html__( 'Compatible Plugins', 'wwp-wholesale' ),
									
									)
								);
							?>
					<ul class="nav nav-tabs" role="tablist">
						<?php
						foreach ( $wwp_settings_tab_lists as $section_id => $section_name ) {
							?>
							<li role="presentation">
								<a href="#<?php echo esc_attr( $section_id ); ?>" role="tab" data-toggle="tab"><?php echo esc_html( $section_name ); ?></a>
							</li>
							<?php
						}
						?>
					</ul>
					</div>
					<div class="col-md-9 col-sm-12">	
						<div class="tab-content tabs">
							<div role="tabpanel" class="tab-pane fade" id="section1">
								<table class="form-table wwp-main-settings">
									<tbody>
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Wholesale Pricing Mode', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<p>
													<input class="inp-cbx" style="display: none" id="single_wholesaler_role" type="radio" value="single" name="options[wholesale_role]" <?php echo ( isset( $settings['wholesale_role'] ) && 'single' == $settings['wholesale_role'] ) ? 'checked' : ''; ?>>
													<label class="cbx" for="single_wholesaler_role">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>
														<span><?php esc_html_e( ' Single Wholesale Role', 'woocommerce-wholesale-pricing' ); ?></span>												
													</label>
													<span data-tip="<?php esc_html_e( 'Default settings for single user role.', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>											
												</p>
												<p>
													<input class="inp-cbx" style="display: none" id="multiple_wholesaler_role" type="radio" value="multiple" name="options[wholesale_role]" <?php echo ( isset( $settings['wholesale_role'] ) && 'multiple' == $settings['wholesale_role'] ) ? 'checked' : ''; ?>>
													<label class="cbx" for="multiple_wholesaler_role">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>													
														<span><?php esc_html_e( ' Multiple Wholesale Roles', 'woocommerce-wholesale-pricing' ); ?></span>													
													</label>
													<span data-tip="<?php esc_html_e( 'Manage prices according to multiple wholesaler user roles.', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
												</p>
											</td>
										</tr>
										
										<tr scope="row" id="multiroledropdown">
										
											<th><label for="default_multipe_wholesale_roles"><?php esc_html_e( 'Default Multi Wholesale Roles', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<?php
												$allterms = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
												?>
												<select id="default_multipe_wholesale_roles" class="regular-text" name="options[default_multipe_wholesale_roles]" >
													<option value=""><?php esc_html_e( 'Select Wholesale Role', 'woocommerce-wholesale-pricing' ); ?></option>
													<?php
													foreach ( $allterms as $allterm ) {
														$selected = '';
														if ( isset( $settings['default_multipe_wholesale_roles'] ) && $settings['default_multipe_wholesale_roles'] == $allterm->slug ) {
																$selected = 'selected';
														}
														?>
														<option value="<?php echo esc_attr( $allterm->slug ); ?>" <?php echo esc_html( $selected ); ?>><?php echo esc_html( $allterm->name ); ?></option>
													<?php } ?> 
												</select>     
												<span data-tip="<?php esc_html_e( 'Define the default wholesaler role for your user.', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
											</td>
										</tr>
										
										<tr scope="row">
											<th>
												<label for=""><?php esc_html_e( 'Disable Auto Approval', 'woocommerce-wholesale-pricing' ); ?></label>
											</th>
											<td>
												<p>
													<input class="inp-cbx" style="display: none" id="disable_auto_role" type="checkbox" value="yes" name="options[disable_auto_role]" <?php echo ( isset( $settings['disable_auto_role'] ) && 'yes' == $settings['disable_auto_role'] ) ? 'checked' : ''; ?>>
													<label class="cbx cbx-square" for="disable_auto_role">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>
														<span><?php esc_html_e( 'Check this option to disable auto approval for wholesale user role registration requests', 'woocommerce-wholesale-pricing' ); ?></span>
													</label>
												</p>
											</td>
										</tr>
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Enable Registration Link', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<p>
													<input class="inp-cbx" style="display: none" id="enable_registration_page" type="checkbox" value="yes" name="options[enable_registration_page]" <?php echo ( isset( $settings['enable_registration_page'] ) && 'yes' == $settings['enable_registration_page'] ) ? 'checked' : ''; ?>>
													<label class="cbx cbx-square" for="enable_registration_page">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>
														<span><?php esc_html_e( ' Enable wholesale registration link on my account page (You must enable registration form on myaccount page to work this functionality)', 'woocommerce-wholesale-pricing' ); ?></span>
													</label>
												</p>
											</td>
										</tr>
										<tr scope="row">
											<th><label for="registration_page_for_wholesale"><?php esc_html_e( 'Registration Page', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<?php
												$args  = array(
													'posts_per_page'   => -1,
													'post_type'        => 'page',
													'post_status'      => 'publish',
												);
												$pages = get_posts( $args );
												?>
												<select id="registration_page_for_wholesale" class="regular-text" name="options[registration_page]" >
													<option value=""><?php esc_html_e( 'Select Page', 'woocommerce-wholesale-pricing' ); ?></option>
													<?php
													foreach ( $pages as $page ) {
														$selected = '';
														if ( isset( $settings['registration_page'] ) && $settings['registration_page'] == $page->ID ) {
															$selected = 'selected';
														}
														?>
														<option value="<?php echo esc_attr( $page->ID ); ?>" <?php echo esc_html( $selected ); ?>><?php echo esc_html( $page->post_title ); ?></option>
													<?php } ?> 
												</select>  
												<span data-tip="<?php esc_html_e( 'Select the page on which you want to display your wholesale registration form.', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>								
											</td>
										</tr>
										<tr scope="row">
											<th><label for="registration_page_for_wholesale"><?php esc_html_e( 'Registration Page Redirect', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
											<?php
											if ( isset( $settings['register_redirect'] ) && ! empty( $settings['register_redirect'] ) ) {
												$register_redirect_title = get_the_title( $settings['register_redirect'] ) . ' - (' . $settings['register_redirect'] . ')';
											} else {
												$register_redirect_title       = '';
												$settings['register_redirect'] = '';
											}
											?>
												<input id="register_redirect_autocomplete" type="text" class="regular-text" value="<?php echo esc_attr( $register_redirect_title ); ?>" >
												<input id="register_redirect" type="hidden" class="" name="options[register_redirect]" value="<?php echo esc_attr( $settings['register_redirect'] ); ?>" > 
												<span data-tip="<?php esc_html_e( 'Please select a page or product to redirect after a successful registration.', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
											</td>
										</tr>

										<?php if (class_exists('WC_USER_REGISTRATION') ) : ?>
											<tr scope="row">
												<th><label for="advance_registration_form"><?php esc_html_e('Advance Registration Form', 'woocommerce-wholesale-pricing'); ?></label></th>
												<td>
													<label class="switch">
														<input id="advance_registration_form" type="checkbox" value="yes" name="options[advance_registration_form]" <?php echo ( isset($settings['advance_registration_form']) && 'yes' == $settings['advance_registration_form'] ) ? 'checked' : ''; ?>>
														<span class="advance_registration_form slider round"></span>
													</label>
													<p class="advance_registration_form_note"><?php esc_html_e('Note: This feature requires User Registration for WooCommerce by WPExperts.', 'woocommerce-wholesale-pricing'); ?></p>
												</td>
											</tr>  

											<div id="advanceRegistrationPopup" style="display: none;" class="popup-modal">
											<div class="popup-content">
												<div style="text-align:center;">
												<svg width="90" height="50" viewBox="0 0 90 90" fill="none" xmlns="http://www.w3.org/2000/svg">
												<circle cx="45" cy="45" r="42.5" fill="white" stroke="#FF0000" stroke-width="5"/>
												<rect x="40" y="20" width="9" height="37" rx="4.5" fill="#FF0000"/>
												<circle cx="44.5" cy="64.5" r="4.5" fill="#FF0000"/>
												</svg>
												<h5 style="margin: 15px;"><?php esc_html_e('Enable Advance User Registration', 'woocommerce-wholesale-pricing'); ?></h5>
												<p class="advance_user_registration_note"><strong><?php esc_html_e('Note: ', 'woocommerce-wholesale-pricing'); ?></strong><?php esc_html_e('Using advance user registration will disable default Wholesale Registration, Notifications and Request section. All the registration form settings, requests and emails will be handled using the User Registration. Please check pending wholesaler requests before enabling.', 'woocommerce-wholesale-pricing'); ?></p>
												<button class="popup-allow"><?php esc_html_e('Allow', 'woocommerce-wholesale-pricing'); ?></button>
												<button class="popup-decline"><?php esc_html_e('Decline', 'woocommerce-wholesale-pricing'); ?></button>
												</div>
											</div>
											</div>
											
										<?php endif; ?>  

										<tr scope="row" id="attachment_location">
											<th><label for=""><?php esc_html_e( 'Select Attachment location', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<?php
												$attachment_location_value = array(
													'woocommerce_before_add_to_cart_button' => 'Woocommerce before add to cart button',
													'woocommerce_after_add_to_cart_button' => 'Woocommerce after add to cart button',
													'woocommerce_after_single_product_summary' => 'Woocommerce after single product summary',
												);
												?>
												<select id="attachment_location_value" class="regular-text" name="options[wwp_attachment_location]" >
													<option value=""><?php esc_html_e( 'Select Attachment location', 'woocommerce-wholesale-pricing' ); ?></option>
													<?php
													foreach ( $attachment_location_value as $key => $value ) {
														$selected = '';
														if ( isset( $settings['wwp_attachment_location'] ) && $settings['wwp_attachment_location'] == $key ) {
															$selected = 'selected';
														}
														?>
														<option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_html( $selected ); ?>><?php echo esc_html( $value ); ?></option>
													<?php } ?> 
												</select>  
												<span data-tip="<?php esc_html_e( 'Select a product attachment location.', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>								
											</td>
										</tr>
										
										
										
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Order Notification Email', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
										  
											<p>
													<input class="inp-cbx" style="display: none" id="emailuserrole" type="radio" value="order_email_user_role" name="options[emailuserrole]" <?php echo ( isset( $settings['emailuserrole'] ) && 'order_email_user_role' == $settings['emailuserrole'] ) ? 'checked' : ''; ?>>
													<label class="cbx" for="emailuserrole">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>
														<span><?php esc_html_e( 'User Role', 'woocommerce-wholesale-pricing' ); ?></span>												
													</label>
													
													<input class="inp-cbx" style="display: none" id="order_email_custom" type="radio" value="order_email_custom" name="options[emailuserrole]"<?php echo ( isset( $settings['emailuserrole'] ) && 'order_email_custom' == $settings['emailuserrole'] ) ? 'checked' : ''; ?>>
													<label class="cbx" for="order_email_custom">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>
														<span><?php esc_html_e( 'Custom Email', 'woocommerce-wholesale-pricing' ); ?></span>												
													</label>
													<span data-tip="<?php esc_html_e( 'Select option to send wholesale order notification email.', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>											
											</p>
											
											</td>
										</tr>
										<tr scope="row" id="select_role_wrap">
											<th><label for=""><?php esc_html_e( 'Select Role', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<select id="email_user_role_value" class="regular-text" name="options[email_user_role_value]" >
													<option value=""><?php esc_html_e( 'Select Role', 'woocommerce-wholesale-pricing' ); ?></option>
													<?php
													foreach ( get_editable_roles() as $role_name => $role_info ) {
														$selected = '';
														if ( isset( $settings['email_user_role_value'] ) && $settings['email_user_role_value'] == $role_name ) {
															$selected = 'selected';
														}
														?>
														<option value="<?php echo esc_attr( $role_name ); ?>" <?php echo esc_html( $selected ); ?>><?php echo esc_html( $role_info['name'] ); ?></option>
													<?php } ?> 
												</select>  
												<span data-tip="<?php esc_html_e( 'Select a role of email notifications.', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>								
											</td>
										</tr>

										<?php 
										if ( !isset($settings['order_custom_email_value']) ) { 
											$settings['order_custom_email_value'] = '';
										} 
										?>
										<tr scope="row" id="select_email_custom_wrap">
											<th><label for=""><?php esc_html_e( 'Recipient(s)', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
											<input id="order_custom_email_value"  name="options[order_custom_email_value]"  type="text" class="regular-text" value="<?php echo esc_attr( $settings['order_custom_email_value'] ); ?>" >
											<span data-tip="<?php esc_html_e( 'Enter recipients (comma separated)', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>								
											</td>
										</tr>
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Manual Wholesale Order', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<p>
													<input class="inp-cbx" style="display: none" id="enable_admin_new_order_item" type="checkbox" value="yes" name="options[enable_admin_new_order_item]" <?php echo ( isset( $settings['enable_admin_new_order_item'] ) && 'yes' == $settings['enable_admin_new_order_item'] ) ? 'checked' : ''; ?>>
													<label class="cbx cbx-square" for="enable_admin_new_order_item">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>
														<span><?php esc_html_e( ' Allow admin to add wholesale orders', 'woocommerce-wholesale-pricing' ); ?></span>
													</label>
												</p>
											</td>
										</tr>

										<!-- 2.6 -->
										<!-- hide add to cart button -->
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Hide Add To Cart Button', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<p>
													<input class="inp-cbx" style="display: none" id="wwp-hide-add-to-cart" type="checkbox" value="yes" name="options[wwp_hide_add_to_cart]" <?php echo ( isset( $settings['wwp_hide_add_to_cart'] ) && 'yes' == $settings['wwp_hide_add_to_cart'] ) ? 'checked' : ''; ?>>
													<label class="cbx cbx-square" for="wwp-hide-add-to-cart">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>
														<span><?php //esc_html_e( ' Allow admin to add wholesale orders', 'woocommerce-wholesale-pricing' ); ?></span>
													</label>
												</p>
											</td>
										</tr>

										<?php 
										if ( isset( $settings['wwp_hide_add_to_cart'] ) && 'yes' == $settings['wwp_hide_add_to_cart'] ) {
											$style = 'display:table-row;';
										} else {
											$style = 'display:none;';
										} 
										?>
										<tr scope="row" class="wwp-hide-cart-on" style="<?php esc_attr_e( $style ); ?>">
											<th><label for=""><?php esc_html_e( 'Hide Add To Cart Button on', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<p>
													<input class="inp-cbx" style="display: none" id="wwp-on-specific-product" type="radio" value="specific_product" name="options[wwp_hide_add_to_cart_on]" <?php echo ( isset( $settings['wwp_hide_add_to_cart_on'] ) && 'specific_product' == $settings['wwp_hide_add_to_cart_on'] ) ? 'checked' : ''; ?>>
													<label class="cbx" for="wwp-on-specific-product">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>
														<span><?php esc_html_e( ' Specific Products', 'woocommerce-wholesale-pricing' ); ?></span>
													</label>
													<input class="inp-cbx" style="display: none" id="wwp-on-specific-product-cat" type="radio" value="specific_product_cat" name="options[wwp_hide_add_to_cart_on]" <?php echo ( isset( $settings['wwp_hide_add_to_cart_on'] ) && 'specific_product_cat' == $settings['wwp_hide_add_to_cart_on'] ) ? 'checked' : ''; ?>>
													<label class="cbx" for="wwp-on-specific-product-cat">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>
														<span><?php esc_html_e( ' Specific Products Category', 'woocommerce-wholesale-pricing' ); ?></span>
													</label>
													<input class="inp-cbx" style="display: none" id="wwp-on-specific-user-role" type="radio" value="specific_user_roles" name="options[wwp_hide_add_to_cart_on]" <?php echo ( isset( $settings['wwp_hide_add_to_cart_on'] ) && 'specific_user_roles' == $settings['wwp_hide_add_to_cart_on'] ) ? 'checked' : ''; ?>>
													<label class="cbx" for="wwp-on-specific-user-role">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>
														<span><?php esc_html_e( ' Specific User Roles', 'woocommerce-wholesale-pricing' ); ?></span>
													</label>
												</p>
												<p class="get-checked-value-for-select">	
												</p>
												<?php
												// Load selected product IDs from settings (if any)
												$selected_products = isset( $settings['wwp_specific_product'] ) ? $settings['wwp_specific_product'] : array();
												?>

													<p class="hide-select-product" style="display: none;">
														<select style="width: 100%;"
																class="wc-product-search"
																multiple="multiple"
																name="options[wwp_specific_product][]"
																id="specific_product"
																data-placeholder="<?php esc_attr_e( 'Search for a product…', 'woocommerce' ); ?>"
																data-action="woocommerce_json_search_products_and_variations">

															<?php
															// Show selected products in the list
															if ( ! empty( $selected_products ) ) {
																foreach ( $selected_products as $product_id ) {
																	$product = wc_get_product( $product_id );
																	if ( $product ) {
																		echo '<option value="' . esc_attr( $product_id ) . '" selected="selected">' . esc_html( $product->get_formatted_name() ) . '</option>';
																	}
																}
															}
															?>
														</select>
													</p>
													<?php 
													$categories = get_terms( array(
														'taxonomy' => 'product_cat',
														'orderby'  => 'name',
														'hide_empty' => false,
													) );
													?>
													<p class="hide-select-category" style="display: none;">
														<select style="width: 100%;" class="wc-enhanced-select" multiple  name="options[wwp_specific_cat][]" id="specific_product_cat">
															<option value="">Select Product Category</option>
															<?php
															foreach ( $categories as $category ) : 
																$selected = '';
																if ( isset( $settings['wwp_specific_cat'] ) && in_array( $category->term_id, $settings['wwp_specific_cat'] ) ) {
																	$selected = 'selected="selected"';
																}
																?>
																<option <?php esc_attr_e( $selected ); ?> value="<?php echo esc_attr( $category->term_id ); ?>">
																	<?php echo esc_html( $category->name ); ?>
																</option>
															<?php endforeach; ?>
														</select>
													</p>
													<?php
													$roles = get_editable_roles();
													?>
													<p class="hide-select-user_role" style="display: none;">
														<select style="width: 100%;" class="wc-enhanced-select" multiple  name="options[wwp_specific_user_role][]" id="specific_user_roles">
															<option value="">Select User Role</option>
															<?php
															foreach ( $roles as $role_key => $role ) : 
																$selected = '';
																if ( isset( $settings['wwp_specific_user_role'] ) && in_array( $role_key, array_map( 'strtolower', $settings['wwp_specific_user_role'] ) ) ) {
																	$selected = 'selected="selected"';
																}
																?>
																<option <?php esc_attr_e( $selected ); ?>value="<?php echo esc_attr( $role_key ); ?>">
																	<?php echo esc_html( $role['name'] ); ?>
																</option>
															<?php endforeach; ?>
														</select>
													</p>
											</td>
										</tr>
										
										<?php 
										if ( ! isset( $settings['custom_message_for_cart']) ) { 
											$settings['custom_message_for_cart'] = '';
										}
										?>

										<tr scope="row" class="wwp-hide-cart-custom_msg" style="<?php esc_attr_e( $style ); ?>">
											<th><label for=""><?php esc_html_e( 'Custom Message For Hide Add To Cart Button', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
											<input id="wwp-custom-message-for-cart" name="options[custom_message_for_cart]"  type="text" class="regular-text" value="<?php echo esc_attr( $settings['custom_message_for_cart'] ); ?>" >
											<span data-tip="<?php esc_html_e( 'Enter custom message instead of add to cart button', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>								
											</td>
										</tr>
										<!-- end for add to cart button -->
										<!-- hide price -->
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Hide Price', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<p>
													<input class="inp-cbx" style="display: none" id="wwp-hide-price" type="checkbox" value="yes" name="options[wwp_hide_price]" <?php echo ( isset( $settings['wwp_hide_price'] ) && 'yes' == $settings['wwp_hide_price'] ) ? 'checked' : ''; ?>>
													<label class="cbx cbx-square" for="wwp-hide-price">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>
														<span><?php //esc_html_e( ' Allow admin to hide wholesale price', 'woocommerce-wholesale-pricing' ); ?></span>
													</label>
												</p>
											</td>
										</tr>

										<?php 
										if ( isset( $settings['wwp_hide_price'] ) && 'yes' == $settings['wwp_hide_price'] ) {
											$style = 'display:table-row;';
										} else {
											$style = 'display:none;';
										} 
										?>
										<tr scope="row" class="wwp-hide-price-on" style="<?php esc_attr_e( $style ); ?>">
											<th><label for=""><?php esc_html_e( 'Hide Price on', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<p>
													<input class="inp-cbx" style="display: none" id="wwp-on-specific-product-price" type="radio" value="specific_product" name="options[wwp_hide_price_on]" <?php echo ( isset( $settings['wwp_hide_price_on'] ) && 'specific_product' == $settings['wwp_hide_price_on'] ) ? 'checked' : ''; ?>>
													<label class="cbx" for="wwp-on-specific-product-price">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>
														<span><?php esc_html_e( ' Specific Products', 'woocommerce-wholesale-pricing' ); ?></span>
													</label>
													<input class="inp-cbx" style="display: none" id="wwp-on-specific-product-cat-price" type="radio" value="specific_product_cat" name="options[wwp_hide_price_on]" <?php echo ( isset( $settings['wwp_hide_price_on'] ) && 'specific_product_cat' == $settings['wwp_hide_price_on'] ) ? 'checked' : ''; ?>>
													<label class="cbx" for="wwp-on-specific-product-cat-price">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>
														<span><?php esc_html_e( ' Specific Product Category', 'woocommerce-wholesale-pricing' ); ?></span>
													</label>
													<input class="inp-cbx" style="display: none" id="wwp-on-specific-user-role-price" type="radio" value="specific_user_roles" name="options[wwp_hide_price_on]" <?php echo ( isset( $settings['wwp_hide_price_on'] ) && 'specific_user_roles' == $settings['wwp_hide_price_on'] ) ? 'checked' : ''; ?>>
													<label class="cbx" for="wwp-on-specific-user-role-price">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>
														<span><?php esc_html_e( ' Specific User Roles', 'woocommerce-wholesale-pricing' ); ?></span>
													</label>
												</p>
												<p class="get-checked-value-for-select-price">    
												</p>
												<?php
												// Load selected product IDs from settings
												$selected_products = isset( $settings['wwp_specific_product_price'] ) ? $settings['wwp_specific_product_price'] : array();
												?>

												<p class="hide-select-price-product" style="display: none;">
													<select style="width: 100%;" class="wc-product-search"
															multiple="multiple"
															name="options[wwp_specific_product_price][]"
															id="specific_product_price"
															data-placeholder="<?php esc_attr_e( 'Search for a product…', 'woocommerce' ); ?>"
															data-action="woocommerce_json_search_products_and_variations">

														<?php
														// Show pre-selected products
														if ( ! empty( $selected_products ) ) {
															foreach ( $selected_products as $product_id ) {
																$product = wc_get_product( $product_id );
																if ( $product ) {
																	echo '<option value="' . esc_attr( $product_id ) . '" selected="selected">' . esc_html( $product->get_formatted_name() ) . '</option>';
																}
															}
														}
														?>
													</select>
												</p>
													<?php 
													$categories = get_terms( array(
														'taxonomy' => 'product_cat',
														'orderby'  => 'name',
														'hide_empty' => false,
													) );
													?>
													<p class="hide-select-price-category" style="display: none;">
														<select style="width: 100%;" class="wc-enhanced-select" multiple  name="options[wwp_specific_cat_price][]" id="specific_product_cat_price">
															<option value="">Select Product Category</option>
															<?php
															foreach ( $categories as $category ) : 
																$selected = '';
																if ( isset( $settings['wwp_specific_cat_price'] ) && in_array( $category->term_id, $settings['wwp_specific_cat_price'] ) ) {
																	$selected = 'selected="selected"';
																}
																?>
																<option <?php esc_attr_e( $selected ); ?> value="<?php echo esc_attr( $category->term_id ); ?>">
																	<?php echo esc_html( $category->name ); ?>
																</option>
															<?php endforeach; ?>
														</select>
													</p>
													<?php 
													$roles = get_editable_roles();
													?>
													<p class="hide-select-price-user-role" style="display: none;">
														<select style="width: 100%;" class="wc-enhanced-select" multiple  name="options[wwp_specific_user_role_price][]" id="specific_user_roles_price">
															<option value="">Select User Role</option>
															<?php
															foreach ( $roles as $role_key => $role ) : 
																$selected = '';
																if ( isset( $settings['wwp_specific_user_role_price'] ) && in_array( $role_key, array_map( 'strtolower', $settings['wwp_specific_user_role_price'] ) ) ) {
																	$selected = 'selected="selected"';
																}
																?>
																<option <?php esc_attr_e( $selected ); ?>value="<?php echo esc_attr( $role_key ); ?>">
																	<?php echo esc_html( $role['name'] ); ?>
																</option>
															<?php endforeach; ?>
														</select>
													</p>
											</td>
										</tr>

										<?php 
										if ( ! isset( $settings['custom_message_for_price']) ) { 
											$settings['custom_message_for_price'] = '';
										}
										?>

										<tr scope="row" class="wwp-hide-price-custom_msg" style="<?php esc_attr_e( $style ); ?>">
											<th><label for=""><?php esc_html_e( 'Custom Message For Hide Price', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<input id="wwp-custom-message-for-price" name="options[custom_message_for_price]"  type="text" class="regular-text" value="<?php echo esc_attr( $settings['custom_message_for_price'] ); ?>" >
												<span data-tip="<?php esc_html_e( 'Enter custom message when price is hidden', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>                                
											</td>
										</tr>
										<!-- end for hide price -->

										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Enable Coupon', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<p>
													<input class="inp-cbx" style="display: none" id="enable_coupon" type="checkbox" value="yes" name="options[enable_coupon]" <?php echo ( isset( $settings['enable_coupon'] ) && 'yes' == $settings['enable_coupon'] ) ? 'checked' : ''; ?>>
													<label class="cbx cbx-square" for="enable_coupon">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>
														<span><?php esc_html_e( 'Enable Wholesale Coupon', 'woocommerce-wholesale-pricing' ); ?></span>
													</label>
												</p>
											</td>
										</tr>
										
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Requisition List', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<p>
													<input class="inp-cbx" style="display: none" id="requisition_list" type="checkbox" value="yes" name="options[requisition_list]" <?php echo ( isset( $settings['requisition_list'] ) && 'yes' == $settings['requisition_list'] ) ? 'checked' : ''; ?>>
													<label class="cbx cbx-square" for="requisition_list">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>
														<span><?php esc_html_e( 'Enable Requisition List for customers', 'woocommerce-wholesale-pricing' ); ?></span>
													</label>
												</p>
											</td>
										</tr>
										
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Requisition List on cart page', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<p>
													<input class="inp-cbx" style="display: none" id="requisition_list_cart_page" type="checkbox" value="yes" name="options[requisition_list_cart_page]" <?php echo ( isset( $settings['requisition_list_cart_page'] ) && 'yes' == $settings['requisition_list_cart_page'] ) ? 'checked' : ''; ?>>
													<label class="cbx cbx-square" for="requisition_list_cart_page">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>
														<span><?php esc_html_e( 'Enable Requisition List button on cart page', 'woocommerce-wholesale-pricing' ); ?></span>
													</label>
												</p>
											</td>
										</tr>
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Variation Table', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<p>
													<input class="inp-cbx" style="display: none" id="variation_table_enable" type="checkbox" value="yes" name="options[variation_table_enable]" <?php echo ( isset( $settings['variation_table_enable'] ) && 'yes' == $settings['variation_table_enable'] ) ? 'checked' : ''; ?>>
													<label class="cbx cbx-square" for="variation_table_enable">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>
														<span><?php esc_html_e( 'Enable Variation table on the variation product page', 'woocommerce-wholesale-pricing' ); ?></span>
													</label>
												</p>
											</td>
										</tr>
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Request for Sample', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<p>
													<input class="inp-cbx" style="display: none" id="request_for_sample_enable" type="checkbox" value="yes" name="options[request_for_sample_enable]" <?php echo ( isset( $settings['request_for_sample_enable'] ) && 'yes' == $settings['request_for_sample_enable'] ) ? 'checked' : ''; ?>>
													<label class="cbx cbx-square" for="request_for_sample_enable">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>
														<span><?php esc_html_e( 'Enable to allow users to request for sample product', 'woocommerce-wholesale-pricing' ); ?></span>
													</label>
												</p>
											</td>
										</tr>
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Request for Sample Label', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
											<input id="request_for_sample_label"  name="options[request_for_sample_label]"  type="text" class="regular-text" value="<?php echo isset( $settings['request_for_sample_label'] ) ? esc_attr( $settings['request_for_sample_label'] ) : ''; ?>" >
											<span data-tip="<?php esc_html_e( 'Enter request for sample button label', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>								
											</td>
										</tr>
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Enable Group', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<p>
													<input class="inp-cbx" style="display: none" id="enable-groups" type="checkbox" value="yes" name="options[enable_groups]" <?php echo ( isset( $settings['enable_groups'] ) && 'yes' == $settings['enable_groups'] ) ? 'checked' : ''; ?>>
													<label class="cbx cbx-square" for="enable-groups">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>
														<span><?php esc_html_e( 'Enable to allow creating groups for customers', 'woocommerce-wholesale-pricing' ); ?></span>
													</label>
												</p>
											</td>
										</tr>
									</tbody>
								</table>
								<!-- non-logged in settings -->
								<h5><?php esc_html_e( 'Non Logged In Settings', 'woocommerce-wholesale-pricing' ); ?></h5>
								<table class="form-table wwp-main-settings">
									<tbody>
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Display Wholesaler price for non logged in', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<p>
													<input class="inp-cbx" style="display: none" id="enable_wholesale_price" type="checkbox" value="yes" name="options[enable_wholesale_price]" <?php echo ( isset( $settings['enable_wholesale_price'] ) && 'yes' == $settings['enable_wholesale_price'] ) ? 'checked' : ''; ?>>
													<label class="cbx cbx-square" for="enable_wholesale_price">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>
														<span><?php esc_html_e( ' Enable Wholesale price', 'woocommerce-wholesale-pricing' ); ?></span>
													</label>
												</p>
											</td>
										</tr>
										<tr scope="row" id="non_logged_in_role">
											<th><label for=""><?php esc_html_e( 'Select wholesale role', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<?php
													$wwp_roles = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
												?>
												<select id="wwp_select_wholesale_role_for_non_logged_in" class="regular-text" name="options[wwp_select_wholesale_role_for_non_logged_in]" >
													<option value="-1"><?php esc_html_e( 'Select wholesale role', 'woocommerce-wholesale-pricing' ); ?></option>
														<?php foreach ( $wwp_roles as $wwp_role ) : ?>
															<option value="<?php esc_attr_e( $wwp_role->term_id ); ?>" <?php selected( ( isset( $settings['wwp_select_wholesale_role_for_non_logged_in'] ) ? $settings['wwp_select_wholesale_role_for_non_logged_in'] : '' ), $wwp_role->term_id, true ); ?> ><?php esc_attr_e( $wwp_role->name ); ?></option>
														<?php endforeach; ?>
												</select>  
											</td>
										</tr>
										<tr scope="row">
											<th><label><?php esc_html_e( 'Display Settings', 'woocommerce-wholesale-pricing' ); ?><label></th>
											<td>&nbsp;</td>
										</tr>
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Shop Page', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<p>
													<input class="inp-cbx" style="display: none" id="enable_non_logged_shop_page_wholesale_price" type="checkbox" value="yes" name="options[enable_non_logged_shop_page_wholesale_price]" <?php echo ( isset( $settings['enable_non_logged_shop_page_wholesale_price'] ) && 'yes' == $settings['enable_non_logged_shop_page_wholesale_price'] ) ? 'checked' : ''; ?>>
													<label class="cbx cbx-square" for="enable_non_logged_shop_page_wholesale_price">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>
														<span><?php esc_html_e( ' Enable to display on shop page', 'woocommerce-wholesale-pricing' ); ?></span>
													</label>
												</p>
											</td>
										</tr>
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Single Product Page', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<p>
													<input class="inp-cbx" style="display: none" id="enable_non_logged_product_page_wholesale_price" type="checkbox" value="yes" name="options[enable_non_logged_product_page_wholesale_price]" <?php echo ( isset( $settings['enable_non_logged_product_page_wholesale_price'] ) && 'yes' == $settings['enable_non_logged_product_page_wholesale_price'] ) ? 'checked' : ''; ?>>
													<label class="cbx cbx-square" for="enable_non_logged_product_page_wholesale_price">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>
														<span><?php esc_html_e( ' Enable to display on single product page', 'woocommerce-wholesale-pricing' ); ?></span>
													</label>
												</p>
											</td>
										</tr>
										<tr scope="row" id="custom_message_non_logged_in">
											<th><label for=""><?php esc_html_e( 'Custom Message', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
											<input id="wwp_custom_message_non_logged_in"  name="options[wwp_custom_message_non_logged_in]"  type="text" class="regular-text" value="<?php isset( $settings['wwp_custom_message_non_logged_in'] ) ? esc_attr_e( $settings['wwp_custom_message_non_logged_in'] ) : ''; ?>" >
											<p class="description">Tags: {saved_amount}, {saved_percentage}</p>							
											</td>
										</tr>
									</tbody>
								</table>						
							</div>

							<div role="tabpanel" class="tab-pane fade" id="section1_5">
								<table class="form-table wwp-main-settings">
									<tbody>
										<?php
										/**
										* Hooks
										*
										* @since 3.0
										*/
										if ( in_array( 'woocommerce-subscriptions/woocommerce-subscriptions.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && isset( $settings['wholesale_role'] ) ) {
											?>
											<tr scope="row">
												<th><label for=""><?php esc_html_e( 'Enable Wholesale For Subscription', 'woocommerce-wholesale-pricing' ); ?></label></th>
												<td>
													<input class="inp-cbx" style="display: none" id="enable_subscription" type="checkbox" value="yes" name="options[enable_subscription]" <?php echo ( isset( $settings['enable_subscription'] ) && 'yes' == $settings['enable_subscription'] ) ? 'checked' : ''; ?>>
													<label class="cbx cbx-square" for="enable_subscription">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>
														<span><?php esc_html_e( 'if checked Wholesaler role will be assigned on the respective wholesale subscription.', 'woocommerce-wholesale-pricing' ); ?></span>
													</label>
												</td>
											</tr>
											<?php
											$args     = array(
												'post_type'     => array( 'product' ),
												'post_status' => 'publish',
												'posts_per_page' => -1,
												'tax_query' => array(
													array(
														'taxonomy' => 'product_type',
														'field'    => 'slug',
														'terms'    => 'variable-subscription',
													),
												),
											);
											$products = get_posts( $args );
											?>
											<tr scope="row">
												<th><label for=""><?php esc_html_e( 'Select Variable Subscription', 'woocommerce-wholesale-pricing' ); ?></label></th>
												<td>
													<label for="wholesale_subscription">
														<select id="wholesale_subscription" class="" name="options[wholesale_subscription]">
														<option disabled><?php esc_html_e( 'Select variable', 'woocommerce-wholesale-pricing' ); ?></option> 
														<?php
														if ( ! empty( $products ) ) {
															foreach ( $products as $key => $product ) {
																$selected = ( isset( $settings['wholesale_subscription'] ) && $product->ID == $settings['wholesale_subscription'] ) ? 'selected' : '';
																echo '<option value="' . esc_attr( $product->ID ) . '" ' . esc_attr( $selected ) . ' >' . esc_html( $product->post_title ) . '</option>';
															}
														}
														?>
														</select>
														<p><?php esc_html_e( 'Select the variable subscription product.', 'woocommerce-wholesale-pricing' ); ?></p>
													</label>
												</td>
											</tr>
											<?php
										} else {
											echo esc_html__( 'You need to enable Woocommerce Subscription plugin.', 'woocommerce-wholesale-pricing' );
										}
										?>
									</tbody>
								</table>
							</div>

							<div role="tabpanel" class="tab-pane fade" id="section2">
								<?php
								$roles = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
								if ( ! empty( $roles ) ) {
									?>
									<div id="accordion">
										<?php
										$data = get_option( 'wholesale_multi_user_pricing' );
										foreach ( $roles as $key => $role ) :
											$min            = 1;
											$min_subtotal   = '';
											$price          = '';
											$discount       = '';
											$excluded_categories  = array();
											if ( isset( $settings['wholesale_role'] ) && 'single' == $settings['wholesale_role'] && 'default_wholesaler' != $role->slug ) {
												continue;
											}
											if ( isset( $data[ $role->term_id ] ) ) {
												$min      = $data[ $role->term_id ]['min_quatity'];
												$min_subtotal   = isset( $data[ $role->term_id ]['min_subtotal'] ) ? $data[ $role->term_id ]['min_subtotal'] : '';
												$price    = $data[ $role->term_id ]['wholesale_price'];
												$discount = $data[ $role->term_id ]['discount_type'];
												$excluded_categories  = isset( $data[ $role->term_id ]['exclude_categories'] ) ? $data[ $role->term_id ]['exclude_categories'] : array();

											}
											?>
											<div class="card">
												<button onclick="return false;" class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse_<?php esc_attr_e( $role->term_id ); ?>" aria-expanded="false" aria-controls="collapse_<?php esc_attr_e( $role->term_id ); ?>">
													<?php esc_html_e( $role->name ); ?>
													<div class="wwp_signal">
														<?php
														$wwp_on_active  = '';
														$wwp_off_active = '';
														if ( isset( $data[ $role->term_id ] ) && ! empty( $data[ $role->term_id ] ) ) {
															$wwp_on_active = 'active';
														} else {
															$wwp_off_active = 'active';
														}

														?>
														<div class="wwp_circle wwp_circle_off <?php echo esc_attr( @$wwp_off_active ); ?> ">&nbsp;</div>
														<div class="wwp_circle wwp_circle_on <?php echo esc_attr( @$wwp_on_active ); ?> ">&nbsp;</div>
													</div>
												</button>
												<div id="collapse_<?php esc_attr_e( $role->term_id ); ?>" class="collapse" aria-labelledby="heading_<?php esc_attr_e( $role->term_id ); ?>" data-parent="#accordion" style="">
													<div class="card-body">
														<table class="form-table wwp-main-settings">
															<tbody>
																<tr scope="row">
																	<th>
																		<label for=""><?php esc_html_e( 'Role Activation', 'woocommerce-wholesale-pricing' ); ?></label>
																	</th>
																	<td>
																		<input class="inp-cbx wwp-checbox" style="display: none" type="checkbox" value="<?php esc_attr_e( $role->slug ); ?>" id="role_<?php esc_attr_e( $role->term_id ); ?>" name="role_<?php esc_attr_e( $role->term_id ); ?>" <?php echo isset( $data[ $role->term_id ] ) ? 'checked' : ''; ?> >
																		<label class="cbx cbx-square" for="role_<?php esc_attr_e( $role->term_id ); ?>">
																			<span>
																				<svg width="12px" height="9px" viewbox="0 0 12 9">
																					<polyline points="1 5 4 8 11 1"></polyline>
																				</svg>
																			</span>
																			<span><?php esc_html_e( 'Enable Role', 'woocommerce-wholesale-pricing' ); ?></span>
																		</label>
																	</td>
																</tr>

																<tr scope="row">
																	<th>
																		<label for=""><?php esc_html_e( 'Discount Type', 'woocommerce-wholesale-pricing' ); ?></label>
																	</th>
																	<td>
																		<select class="regular-text" name="discount_type_<?php esc_attr_e( $role->term_id ); ?>" value="">
																			<option value="percent" <?php selected( $discount, 'percent' ); ?> > <?php esc_html_e( 'Percent', 'woocommerce-wholesale-pricing' ); ?> </option>
																			<option value="fixed"  <?php selected( $discount, 'fixed' ); ?> > <?php esc_html_e( 'Fixed', 'woocommerce-wholesale-pricing' ); ?> </option>
																		</select>
																		<span data-tip="<?php esc_html_e( 'Price type for wholesale products', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
																	</td>
																</tr>

																<tr scope="row">
																	<th>
																		<label for=""><?php esc_html_e( 'Wholesale Value', 'woocommerce-wholesale-pricing' ); ?></label>
																	</th>
																	<td>
																		<input class="regular-text wwp-price" type="text" name="wholesale_price_<?php esc_attr_e( $role->term_id ); ?>" value="<?php esc_attr_e( $price ); ?>">
																		<span data-tip="<?php esc_html_e( 'Enter the value you would like to change the Wholesale User', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
																	</td>
																</tr>

																<tr scope="row">
																	<th>
																		<label for=""><?php esc_html_e( 'Min Quantity', 'woocommerce-wholesale-pricing' ); ?></label>
																	</th>
																	<td>
																		<input class="regular-text " type="text" name="min_quatity_<?php esc_attr_e( $role->term_id ); ?>" value="<?php esc_attr_e( $min ); ?>">
																		<span data-tip="<?php esc_html_e( 'Enter Wholesale minimum per product quantity to apply discount', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
																	</td>
																</tr>
																<tr scope="row">
																	<th>
																		<label for=""><?php esc_html_e( 'Min Subtotal', 'woocommerce-wholesale-pricing' ); ?></label>
																	</th>
																	<td>
																		<input class="regular-text " type="number" min=".01" step=".01" name="min_subtotal_<?php esc_attr_e( $role->term_id ); ?>" value="<?php esc_attr_e( $min_subtotal ); ?>">
																		<span data-tip="<?php esc_html_e( 'Enter Wholesale minimum cart subtotal to apply discount', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
																	</td>
																</tr>
																<tr scope="row">
																	<th>
																		<label for=""><?php esc_html_e( 'Exclude Catagory', 'woocommerce-wholesale-pricing' ); ?></label>
																	</th>
																	<td>
																		<select name="exclude_categories_<?php echo esc_attr( $role->term_id ); ?>[]" multiple class="wc-enhanced-select" style="width: 300px;">
																			<?php
																			$categories = get_terms( array(
																				'taxonomy'   => 'product_cat',
																				'hide_empty' => false,
																			) );

																			if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
																				foreach ( $categories as $category ) {
																					$selected = in_array( $category->term_id, (array) $excluded_categories ) ? 'selected' : '';
																					echo '<option value="' . esc_attr( $category->term_id ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $category->name ) . '</option>';
																				}
																			}
																			?>
																		</select>
																		<span data-tip="<?php esc_html_e( 'Select the Catagory you want to exclude.', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
																	</td>
																</tr>
																<tr scope="row">
																	<th>
																		<label for=""><?php esc_html_e( 'Global Tier Pricing', 'woocommerce-wholesale-pricing' ); ?></label>
																	</th>
																	<td>
																		<button name="save-wwp_wholesale" data-toggle="modal" data-target="#global_tier_pricing_Modal<?php esc_attr_e( $role->term_id ); ?>" class="wwp-button-primary" type="button" value="Save changes">Add Tier Pricing</button>
																		<span data-tip="<?php esc_html_e( 'Global Tier Pricing', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
																		<?php
																		if (!isset($settings['tier_pricing'])) {
																			$settings['tier_pricing'] = '';
																		}
																		
																		$name = 'options[tier_pricing]';
																		echo wp_kses_post( '<div>' . tier_pricing_modal_popup( 'Global Tier Pricing', 'global_tier_pricing_Modal' . esc_attr( $role->term_id ), esc_attr( $role->term_id ), $settings['tier_pricing'], esc_attr( $name ), '' ) . '</div>' );

																		?>
																	</td>
																</tr>
															</tbody>
														</table>
													</div>
												</div>
											</div>
											<?php
										endforeach;
										?>
									</div>
									<?php
								}
								?>
							</div>

							<div role="tabpanel" class="tab-pane fade" id="section3">
								<table class="form-table wwp-main-settings">
									<tbody>
										<tr scope="row">
											<th><label for="retailer_label"><?php esc_html_e( 'Retailer Price Label', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<input type="text" class="regular-text" name="options[retailer_label]" id="retailer_label" value="<?php echo isset( $settings['retailer_label'] ) ? esc_html( $settings['retailer_label'] ) : ''; ?>"> 
												
												<input class="inp-cbx" style="display: none" id="retailer_disabled" type="checkbox" value="yes" name="options[retailer_disabled]" <?php echo ( isset( $settings['retailer_disabled'] ) && 'yes' == $settings['retailer_disabled'] ) ? 'checked' : ''; ?>>
												<label class="cbx cbx-square" for="retailer_disabled">
													<span>
														<svg width="12px" height="9px" viewbox="0 0 12 9">
															<polyline points="1 5 4 8 11 1"></polyline>
														</svg>
													</span>
													<span for="retailer_disabled"><?php esc_html_e( 'Label Hide', 'woocommerce-wholesale-pricing' ); ?></span>												
												</label>
												<span data-tip="<?php esc_html_e( 'Hide price Label for wholesale user only.', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
											
											</td>
										</tr>
										<tr scope="row">
											<th><label for="wholesaler_price_label"><?php esc_html_e( 'Wholesaler Price Label', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td><input type="text" class="regular-text" name="options[wholesaler_label]" id="wholesaler_price_label" value="<?php echo isset( $settings['wholesaler_label'] ) ? esc_html( $settings['wholesaler_label'] ) : ''; ?>">
											</td>
										</tr>
										<tr scope="row">
											<th><label for="save_price_label"><?php esc_html_e( 'Save Price Label', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<input type="text" class="regular-text" name="options[save_label]" id="save_price_label" value="<?php echo isset( $settings['save_label'] ) ? esc_html( $settings['save_label'] ) : ''; ?>">
												<input class="inp-cbx" style="display: none" id="save_price_disabled" type="checkbox" value="yes" name="options[save_price_disabled]" <?php echo ( isset( $settings['save_price_disabled'] ) && 'yes' == $settings['save_price_disabled'] ) ? 'checked' : ''; ?>>
												<label class="cbx cbx-square" for="save_price_disabled">
													<span>
														<svg width="12px" height="9px" viewbox="0 0 12 9">
															<polyline points="1 5 4 8 11 1"></polyline>
														</svg>
													</span>
													<span for="save_price_disabled"><?php esc_html_e( 'Label Hide', 'woocommerce-wholesale-pricing' ); ?></span>
												</label>
												<span data-tip="<?php esc_html_e( 'Hide price Label for wholesale user only.', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
											</td>
										</tr>
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Strike Through Price', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<p>
													<input class="inp-cbx" style="display: none" id="enable_strike_through_price" type="checkbox" value="yes" name="options[enable_strike_through_price]" <?php checked( ( isset( $settings['enable_strike_through_price'] ) ? $settings['enable_strike_through_price'] : 'yes' ), 'yes', true ); ?>>
													<label class="cbx cbx-square" for="enable_strike_through_price">
														<span>
															<svg width="12px" height="9px" viewbox="0 0 12 9">
																<polyline points="1 5 4 8 11 1"></polyline>
															</svg>
														</span>
														<span><?php esc_html_e( ' Display strike through regular price', 'woocommerce-wholesale-pricing' ); ?></span>
													</label>
												</p>
											</td>
										</tr>
									</tbody>
								</table>							
							</div>

							<div role="tabpanel" class="tab-pane fade" id="section4">
								<table class="form-table wwp-main-settings">
									<tbody>
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Hide price', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<input class="inp-cbx" style="display: none" id="price_hide" type="checkbox" value="yes" name="options[price_hide]" <?php echo ( isset( $settings['price_hide'] ) && 'yes' == $settings['price_hide'] ) ? 'checked' : ''; ?>>
												<label class="cbx cbx-square" for="price_hide">
													<span>
														<svg width="12px" height="9px" viewbox="0 0 12 9">
															<polyline points="1 5 4 8 11 1"></polyline>
														</svg>
													</span>
													<span><?php esc_html_e( 'Hide retail prices until user gets logged in', 'woocommerce-wholesale-pricing' ); ?></span>												
												</label>
											</td>
										</tr>
										<tr scope="row">
											<th><label for="display_link_text"><?php esc_html_e( 'Label for login link', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td><input type="text" class="regular-text" name="options[display_link_text]" id="display_link_text" value="<?php echo isset( $settings['display_link_text'] ) ? esc_html( $settings['display_link_text'] ) : ''; ?>">
											<span data-tip="<?php esc_html_e( 'This login link will appear on every product if Hide price option is checked', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
											</td>
										</tr>
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Restrict wholesale store access', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<input class="inp-cbx" style="display: none" id="wholesaler_login_restriction" type="checkbox" value="yes" name="options[wholesaler_login_restriction]" <?php echo ( isset( $settings['wholesaler_login_restriction'] ) && 'yes' == $settings['wholesaler_login_restriction'] ) ? 'checked' : ''; ?>>
												<label class="cbx cbx-square" for="wholesaler_login_restriction">
													<span>
														<svg width="12px" height="9px" viewbox="0 0 12 9">
															<polyline points="1 5 4 8 11 1"></polyline>
														</svg>
													</span>
													<span><?php esc_html_e( 'Enabling this option will allow only approved wholesale users to login.', 'woocommerce-wholesale-pricing' ); ?></span>
												</label>
											</td>
										</tr>
										<tr scope="row">
											<th><label for="login_message_waiting_user"><?php esc_html_e( 'Custom message for pending request', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<?php
											if ( empty( $settings['login_message_waiting_user'] ) ) {
												$settings['login_message_waiting_user'] = __( 'You can not access this store, Your request status is in Pending', 'woocommerce-wholesale-pricing' );
											}
											?>
											<td><input type="text" class="regular-text" name="options[login_message_waiting_user]" id="login_message_waiting_user" value="<?php echo isset( $settings['login_message_waiting_user'] ) ? esc_html( $settings['login_message_waiting_user'] ) : ''; ?>">
											<span data-tip="<?php esc_html_e( 'Enter message to display for pending request', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
											</td>
										</tr>
										<tr scope="row">
											<th><label for="login_message_rejected_user"><?php esc_html_e( 'Custom message for rejected request', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<?php
											if ( empty( $settings['login_message_rejected_user'] ) ) {
												$settings['login_message_rejected_user'] = __( 'You can not access this store, Your request is Rejected by admin', 'woocommerce-wholesale-pricing' );
											}
											?>
											<td><input type="text" class="regular-text" name="options[login_message_rejected_user]" id="login_message_rejected_user" value="<?php echo isset( $settings['login_message_rejected_user'] ) ? esc_html( $settings['login_message_rejected_user'] ) : ''; ?>">
											<span data-tip="<?php esc_html_e( 'Enter message to display for rejected request', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
											</td>
										</tr>
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Restrict Full Store Access', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<input class="inp-cbx" style="display: none" id="restrict_store_access" type="checkbox" value="yes" name="options[restrict_store_access]" <?php echo ( isset( $settings['restrict_store_access'] ) && 'yes' == $settings['restrict_store_access'] ) ? 'checked' : ''; ?>>
												<label class="cbx cbx-square" for="restrict_store_access">
													<span>
														<svg width="12px" height="9px" viewbox="0 0 12 9">
															<polyline points="1 5 4 8 11 1"></polyline>
														</svg>
													</span>
													<span><?php esc_html_e( 'Restrict or Hide your store with Password', 'woocommerce-wholesale-pricing' ); ?></span>
												</label>
											</td>
										</tr>
										<tr>
											<th>
												<label for="enable_store_access_cookie"><?php esc_html_e( 'Enable Cookie', 'woocommerce-wholesale-pricing' ); ?></label>
											</th>
											<td>
												<input class="inp-cbx" style="display: none" id="enable_store_access_cookie" type="checkbox" value="yes" name="options[enable_store_access_cookie]" <?php echo ( isset( $settings['enable_store_access_cookie'] ) && 'yes' == $settings['enable_store_access_cookie'] ) ? 'checked' : ''; ?>>
												<label class="cbx cbx-square" for="enable_store_access_cookie">
													<span>
														<svg width="12px" height="9px" viewbox="0 0 12 9">
															<polyline points="1 5 4 8 11 1"></polyline>
														</svg>
													</span>
													<span><?php esc_html_e( 'Enable your store cookie with a password', 'woocommerce-wholesale-pricing' ); ?></span>
												</label>
											</td>
										</tr>
										<?php
											$style = 'display: none';
										if ( isset( $settings['enable_store_access_cookie'] ) && 'yes' == $settings['enable_store_access_cookie'] ) {
											$style = 'display: table-row';
										} 
										?>
										<tr class="wwp-cookie-interval" style="<?php echo esc_attr( $style ); ?>" >
											<th>
												<label for="wwp_cookie_interval"><?php esc_html_e( 'Cookie Interval', 'woocommerce-wholesale-pricing' ); ?></label>
											</th>
											<td>
												<input type="number" min="1" class="regular-text" name="options[wwp_cookie_interval]" placeholder="<?php esc_html_e( 'Enter number of days', 'woocommerce-wholesale-pricing' ); ?>" id="wwp_cookie_interval" value="<?php echo isset( $settings['wwp_cookie_interval'] ) ? esc_html( $settings['wwp_cookie_interval'] ) : '1'; ?>">
												<span data-tip="<?php esc_html_e( 'The store\'s cookie with a password will be deleted after the specified time interval.', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
											</td>
										</tr>
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Override Default registration form', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>									
												<input class="inp-cbx" style="display: none" id="over_right_wholesale_form" type="checkbox" value="yes" name="options[over_right_wholesale_form]" <?php echo ( isset( $settings['over_right_wholesale_form'] ) && 'yes' == $settings['over_right_wholesale_form'] ) ? 'checked' : ''; ?>>
												<label class="cbx cbx-square" for="over_right_wholesale_form">
													<span>
														<svg width="12px" height="9px" viewbox="0 0 12 9">
															<polyline points="1 5 4 8 11 1"></polyline>
														</svg>
													</span>
													<span><?php esc_html_e( 'Override Default registration form to wholesale form', 'woocommerce-wholesale-pricing' ); ?></span>
												</label>
											</td>
										</tr>
										
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Restrict Store Access Message', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
											<?php $settings['restrict_store_access_message'] = isset( $settings['restrict_store_access_message'] ) ? $settings['restrict_store_access_message'] : ''; ?>
											<textarea id="restrict_store_access_message" rows="7" name="options[restrict_store_access_message]" class="textarea"><?php echo wp_kses_post( wp_unslash( $settings['restrict_store_access_message'] ) ); ?></textarea> 
											</td>
										</tr>
									</tbody>
								</table>
							</div>

							<div role="tabpanel" class="tab-pane fade" id="section5">
								<table class="form-table wwp-main-settings">
									<tbody>
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Restrict wholesale products visibility', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<input class="inp-cbx" style="display: none" id="wholesaler_prodcut_only" type="checkbox" value="yes" name="options[wholesaler_prodcut_only]" <?php echo ( isset( $settings['wholesaler_prodcut_only'] ) && 'yes' == $settings['wholesaler_prodcut_only'] ) ? 'checked' : ''; ?>>
												<label class="cbx cbx-square" for="wholesaler_prodcut_only">
													<span>
														<svg width="12px" height="9px" viewbox="0 0 12 9">
															<polyline points="1 5 4 8 11 1"></polyline>
														</svg>
													</span>
													<span><?php esc_html_e( 'By enabling this option wholesale only products will be visible to wholesaler user roles only.', 'woocommerce-wholesale-pricing' ); ?></span>
												</label>
											</td>
										</tr>
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Restrict wholesale products globally from non-wholesaler customer.', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<input class="inp-cbx" style="display: none" id="non_wholesale_product_hide" type="checkbox" value="yes" name="options[non_wholesale_product_hide]" <?php echo ( isset( $settings['non_wholesale_product_hide'] ) && 'yes' == $settings['non_wholesale_product_hide'] ) ? 'checked' : ''; ?>>
												<label class="cbx cbx-square" for="non_wholesale_product_hide">
													<span>
														<svg width="12px" height="9px" viewbox="0 0 12 9">
															<polyline points="1 5 4 8 11 1"></polyline>
														</svg>
													</span>
													<span><?php esc_html_e( 'Enable this option to hide wholesale products from retailers and non-logged in user.', 'woocommerce-wholesale-pricing' ); ?></span>
												</label>
											</td>
										</tr>
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Enforce minimum quantity rules', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<input class="inp-cbx" style="display: none" id="wholesaler_allow_minimum_qty" type="checkbox" value="yes" name="options[wholesaler_allow_minimum_qty]" <?php echo ( isset( $settings['wholesaler_allow_minimum_qty'] ) && 'yes' == $settings['wholesaler_allow_minimum_qty'] ) ? 'checked' : ''; ?>>
												<label class="cbx cbx-square" for="wholesaler_allow_minimum_qty">
													<span>
														<svg width="12px" height="9px" viewbox="0 0 12 9">
															<polyline points="1 5 4 8 11 1"></polyline>
														</svg>
													</span>
													<span><?php esc_html_e( 'Enforce the wholesale customer to purchase with minimum quantity rules', 'woocommerce-wholesale-pricing' ); ?></span>
												</label>
											</td>
										</tr>
									</tbody>
								</table>
							</div>

							<div role="tabpanel" class="tab-pane fade" id="section6">
								<table class="form-table wwp-main-settings">
									<tbody>									
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Enable Upgrade Tab', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<input class="inp-cbx" style="display: none" id="enable_upgrade" type="checkbox" value="yes" name="options[enable_upgrade]" <?php echo ( isset( $settings['enable_upgrade'] ) && 'yes' == $settings['enable_upgrade'] ) ? 'checked' : ''; ?>>
												<label class="cbx cbx-square" for="enable_upgrade">
													<span>
														<svg width="12px" height="9px" viewbox="0 0 12 9">
															<polyline points="1 5 4 8 11 1"></polyline>
														</svg>
													</span>
													<span><?php esc_html_e( 'Enable wholesale upgrade tab on my account page for non wholesale users', 'woocommerce-wholesale-pricing' ); ?></span>
												</label>
											</td>
										</tr>
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Request Again Submit', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<input class="inp-cbx" style="display: none" id="request_again_submit" type="checkbox" value="yes" name="options[request_again_submit]" <?php echo ( isset( $settings['request_again_submit'] ) && 'yes' == $settings['request_again_submit'] ) ? 'checked' : ''; ?>>
												<label class="cbx cbx-square" for="request_again_submit">
													<span>
														<svg width="12px" height="9px" viewbox="0 0 12 9">
															<polyline points="1 5 4 8 11 1"></polyline>
														</svg>
													</span>
													<span><?php esc_html_e( 'Ability to enable submitting request again after rejection.', 'woocommerce-wholesale-pricing' ); ?></span>
												</label>
											</td>
										</tr>
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Upgrade Tab Text', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<label for="upgrade_tab_text">
													<input type="text" class="regular-text" name="options[upgrade_tab_text]" id="upgrade_tab_text" value="<?php echo isset( $settings['upgrade_tab_text'] ) ? esc_html( $settings['upgrade_tab_text'] ) : ''; ?>" Placeholder="Label for Upgrade to Wholesaler tab">
												</label>
												<span data-tip='<?php esc_html_e( 'Display any text you want on the "Upgrade to Wholesaler" tab.', 'woocommerce-wholesale-pricing' ); ?>' class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
											</td>
										</tr>
									</tbody>
								</table>
							</div>

							<div role="tabpanel" class="tab-pane fade" id="section7">
								<table class="form-table wwp-main-settings">
									<tbody>
										<?php 
										/**
										* Hooks
										*
										* @since 3.0
										*/
										do_action( 'registration_page_css_after', $settings ); 
										?>
										<tr scope="row">
											<th colspan="2"><h2><?php esc_html_e( 'Additional CSS', 'woocommerce-wholesale-pricing' ); ?></h2></th>
										</tr>
										
										<?php
										if ( empty( $settings['wholesale_css'] ) ) {
											$settings['wholesale_css'] = '/* Enter Your Custom CSS Here */';
										}
										?>
										
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Registration Page CSS', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
											<textarea id="code_editor_page_css" rows="15" name="options[wholesale_css]" class="widefat-100 textarea"><?php echo wp_kses_post( wp_unslash( $settings['wholesale_css'] ) ); ?></textarea>
											<p class="wwwp_help_text"><?php esc_html_e( 'Enter css without <style> tag.', 'woocommerce-wholesale-pricing' ); ?></p>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
							
							<div role="tabpanel" class="tab-pane fade" id="section8">
								
								<div class="wholesaleplugin_container" style="background:#fbfaff;">
									<div class="wholesaleplugin_content">
										<h1><?php esc_html_e( 'Compatibility & Integrations', 'woocommerce-wholesale-pricing' ); ?></h1> </div>
								</div>
								<div class="wholesaleplugin_container">
									<div class="wholesaleplugin_content">
										<div class="wholesaleplugin_content_holder">
											<div class="col_left">
												<h3><?php esc_html_e( 'Bulk order for WooCommerce', 'woocommerce-wholesale-pricing' ); ?> </h3>
												<p><?php esc_html_e( 'Bulk Order Form for WooCommerce is the perfect WooCommerce extension that gives you easy ways to list your products and, at the same time, allows you to customize your product tables without the need of an expert by your side.', 'woocommerce-wholesale-pricing' ); ?></p>
												<p><?php esc_html_e( 'Create a simple, flexible, and responsive product table with Bulk Order for WooCommerce.', 'woocommerce-wholesale-pricing' ); ?></p>
												<ul>
													<li> <?php esc_html_e( 'Fully responsive design view;', 'woocommerce-wholesale-pricing' ); ?> </li>
													<li> <?php esc_html_e( 'Sorting and filtering options;', 'woocommerce-wholesale-pricing' ); ?> </li>
													<li> <?php esc_html_e( 'Multiple pagination options;', 'woocommerce-wholesale-pricing' ); ?> </li>
													<li> <?php esc_html_e( 'Display extra columns such as SKU, weight, dimensions, etc.;', 'woocommerce-wholesale-pricing' ); ?> </li>
													<li> <?php esc_html_e( 'Full product data control;', 'woocommerce-wholesale-pricing' ); ?> </li>
													<li> <?php esc_html_e( 'List specific products based on category, tag, status custom field value, or date;', 'woocommerce-wholesale-pricing' ); ?> </li>
													<li> <?php esc_html_e( 'Instant add to cart button;', 'woocommerce-wholesale-pricing' ); ?> </li>
													<li> <?php esc_html_e( 'Customers can add multiple or bulk quantities to cart', 'woocommerce-wholesale-pricing' ); ?> </li>
												</ul> <a href="https://woocommerce.com/products/bulk-order-form-for-woocommerce/" class="btn--view"><?php esc_html_e( 'Buy on WooCommerce', 'woocommerce-wholesale-pricing' ); ?></a> </div>
											<div class="col_right">
												<iframe width="470" height="315" src="https://www.youtube.com/embed/59JewNgiLLM" frameborder="0" allow="autoplay;" allowfullscreen></iframe>
											</div>
										</div>
									</div>
								</div>
								<div class="wholesaleplugin_container" style="background:#fbfaff;">
									<div class="wholesaleplugin_content">
										<div class="col_left">
											<iframe width="520" height="315" src="https://www.youtube.com/embed/-va24oCJ0LM" frameborder="0" allow=" autoplay; " allowfullscreen></iframe>
											</iframe>
										</div>
										<div class="col_right">
											<h3><?php esc_html_e( 'Currency Switcher for WooCommerce', 'woocommerce-wholesale-pricing' ); ?></h3>
											<p><?php esc_html_e( 'Currency Switcher for WooCommerce automatically detects the Geo-Location IP of your customer and the country from which they are browsing your store. When any page from your store loads, the extension displays all the prices in the home currency of the customer.', 'woocommerce-wholesale-pricing' ); ?> </p>
											<p><?php esc_html_e( 'The easy-to-use extension can be widgetized anywhere on the store from the shop and cart to the checkout page of your WooCommerce website.', 'woocommerce-wholesale-pricing' ); ?></p>
											<ul>
												<li><?php esc_html_e( 'Expand your business to other countries & regions;', 'woocommerce-wholesale-pricing' ); ?>  </li>
												<li><?php esc_html_e( 'Removes the hassle of currency conversion;', 'woocommerce-wholesale-pricing' ); ?>  </li>
												<li><?php esc_html_e( 'Reduce Cart Abandonment, Refunds, Chargebacks;', 'woocommerce-wholesale-pricing' ); ?>  </li>
												<li><?php esc_html_e( 'Ensure pricing display consistency across your store;', 'woocommerce-wholesale-pricing' ); ?>  </li>
												<li><?php esc_html_e( 'Change currency automatically using customer�s GEOIP;', 'woocommerce-wholesale-pricing' ); ?>  </li>
												<li><?php esc_html_e( 'Help customers avoid extra fee charges on their credit card statements.', 'woocommerce-wholesale-pricing' ); ?>  </li>
											</ul> <a href="https://woocommerce.com/products/currency-switcher-for-woocommerce/" class="btn--view"><?php esc_html_e( 'Buy on WooCommerce', 'woocommerce-wholesale-pricing' ); ?></a> </div>
									</div>
								</div>
							</div>
							<div role="tabpanel" class="tab-pane fade" id="section9">
							<?php
								$roles = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
							if ( ! empty( $roles ) ) {
								?>
									<div id="accordion">
									<?php
									$data             = get_option( 'wholesale_multi_user_cart_discount' );
									$cart_tire_preces = get_option( 'cart_tire_preces' );
									foreach ( $roles as $key => $role ) :

										$min_wholesale_amount = '';
										$wholesale_amount     = '';
										$wholesale_label      = '';
										$discount             = '';
										// 2.7
										$discount_on          = '';
										$cart_discount_value  = '';
										$min_subtotal_value   = '';
										if ( isset( $settings['wholesale_role'] ) && 'single' == $settings['wholesale_role'] && 'default_wholesaler' != $role->slug ) {
											continue;
										}
										if ( isset( $data[ $role->term_id ] ) ) {
											if ( isset($data[ $role->term_id ]['wholesale_price']) ) {
												$price = $data[ $role->term_id ]['wholesale_price'];
											}
											if ( isset($data[ $role->term_id ]['discount_type']) ) {
												$discount = $data[ $role->term_id ]['discount_type'];
											}
											// 2.7
											if ( isset($data[ $role->term_id ]['discount_on']) ) {
												$discount_on = $data[ $role->term_id ]['discount_on'];
											}
											if ( isset( $data[ $role->term_id ]['cart_price'] ) && ! empty( $data[ $role->term_id ]['cart_price'] ) ) {
												$cart_discount_value = $data[ $role->term_id ]['cart_price'];
											}
											if ( isset( $data[ $role->term_id ]['cart_min_subtotal'] ) && ! empty( $data[ $role->term_id ]['cart_min_subtotal'] ) ) {
												$min_subtotal_value = $data[ $role->term_id ]['cart_min_subtotal'];
											}
										
										}
										?>
											<div class="card">
												<button onclick="return false;" class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse_<?php esc_attr_e( $role->term_id ); ?>_cart" aria-expanded="false" aria-controls="collapse_<?php esc_attr_e( $role->term_id ); ?>_cart">
												<?php esc_html_e( $role->name ); ?>
													<div class="wwp_signal">
													<?php
													$wwp_on_active  = '';
													$wwp_off_active = '';
													if ( isset( $data[ $role->term_id ] ) && ! empty( $data[ $role->term_id ] ) ) {
														$wwp_on_active = 'active';
													} else {
														$wwp_off_active = 'active';
													}
													?>
														<div class="wwp_circle wwp_circle_off <?php echo esc_attr( @$wwp_off_active ); ?> ">&nbsp;</div>
														<div class="wwp_circle wwp_circle_on <?php echo esc_attr( @$wwp_on_active ); ?> ">&nbsp;</div>
													</div>
												</button>
												<div id="collapse_<?php esc_attr_e( $role->term_id ); ?>_cart" class="collapse" aria-labelledby="heading_<?php esc_attr_e( $role->term_id ); ?>" data-parent="#accordion" style="">
													<div class="card-body">
													<table class="form-table wwp-main-settings">
															<tbody>
																<tr scope="row">
																	<th>
																		<label for=""><?php esc_html_e( 'Role Activation', 'woocommerce-wholesale-pricing' ); ?></label>
																	</th>
																	<td>
																		<input class="inp-cbx wwp-checbox" style="display: none" type="checkbox" value="<?php esc_attr_e( $role->slug ); ?>" id="role_<?php esc_attr_e( $role->term_id ); ?>_cart" name="role_<?php esc_attr_e( $role->term_id ); ?>_cart" <?php echo isset( $data[ $role->term_id ] ) ? 'checked' : ''; ?> >
																		<label class="cbx cbx-square" for="role_<?php esc_attr_e( $role->term_id ); ?>_cart">
																			<span>
																				<svg width="12px" height="9px" viewbox="0 0 12 9">
																					<polyline points="1 5 4 8 11 1"></polyline>
																				</svg>
																			</span>
																			<span><?php esc_html_e( 'Enable Role', 'woocommerce-wholesale-pricing' ); ?></span>
																		</label>
																	</td>
																</tr>

																<tr scope="row">
																	<th>
																		<label for=""><?php esc_html_e( 'Discount Type', 'woocommerce-wholesale-pricing' ); ?></label>
																	</th>
																	<td>
																		<select class="regular-text" name="discount_type_<?php esc_attr_e( $role->term_id ); ?>_cart" value="">
																			<option value="percent" <?php selected( $discount, 'percent' ); ?> > <?php esc_html_e( 'Percent', 'woocommerce-wholesale-pricing' ); ?> </option>
																			<option value="fixed"  <?php selected( $discount, 'fixed' ); ?> > <?php esc_html_e( 'Fixed', 'woocommerce-wholesale-pricing' ); ?> </option>
																		</select>
																		<span data-tip="<?php esc_html_e( 'Price type for cart discount', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
																	</td>
																</tr>

																<?php // 2.7 ?> 
																<tr scope="row">
																	<th>
																		<label for=""><?php esc_html_e( 'Discount Value', 'woocommerce-wholesale-pricing' ); ?></label>
																	</th>
																	<td>
																		<input class="regular-text wwp-price" type="text" name="cart_price_<?php esc_attr_e( $role->term_id ); ?>" value="<?php echo esc_attr( $cart_discount_value ); ?>">
																		<span data-tip="<?php esc_html_e( 'Enter the discount value', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
																	</td>
																</tr>

																<tr scope="row">
																	<th>
																		<label for=""><?php esc_html_e( 'Tier Discount On', 'woocommerce-wholesale-pricing' ); ?></label>
																	</th>
																	<td>
																		<select class="regular-text cart-discount-on cart-discount-on_<?php esc_attr_e( $role->term_id ); ?>" name="discount_on_<?php esc_attr_e( $role->term_id ); ?>_cart" value="">
																			<option value="cart_discount_amount" <?php selected( $discount_on, 'cart_discount_amount' ); ?> > <?php esc_html_e( 'Amount', 'woocommerce-wholesale-pricing' ); ?> </option>
																			<option value="cart_discount_quantity"  <?php selected( $discount_on, 'cart_discount_quantity' ); ?> > <?php esc_html_e( 'Quantity', 'woocommerce-wholesale-pricing' ); ?> </option>
																		</select>
																		<span data-tip="<?php esc_html_e( 'Select type on behalf of which you want to create tier', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
																	</td>
																</tr>
																<?php 
																if ( ! empty( $discount_on ) && 'cart_discount_quantity' == $discount_on ) {
																	$minlabel = 'Minimum Cart Quantity';
																	$discountlabel = 'Cart Quantity Discount Range';
																} else {
																	$minlabel = 'Minimum Cart Subtotal';
																	$discountlabel = 'Cart Total Discount Range';
																}
																?>
																<tr scope="row">
																	<th>
																		<label id="wwp-min-cart-label" for=""><?php esc_html_e( $minlabel, 'woocommerce-wholesale-pricing' ); ?></label>
																	</th>
																	<td>
																		<input class="regular-text wwp-price" type="text" name="min_subtotal_<?php esc_attr_e( $role->term_id ); ?>_cart" value="<?php echo esc_attr( $min_subtotal_value ); ?>">
																		<span data-tip="<?php esc_html_e( 'Enter The Minimum Cart Subtotal', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top cart-subtotal"><span class="woocommerce-help-tip"></span></span>
																	</td>
																</tr>

																<tr scope="row">
																	<th>
																		<label id="wwp-cart-discount-label" for=""><?php esc_html_e( $discountlabel, 'woocommerce-wholesale-pricing' ); ?></label>
																	</th>
																	<td>
																		<button name="cart_total_discount_range" data-toggle="modal" data-id="<?php esc_attr_e( $role->term_id ); ?>" data-target="#cart_total_discount_range<?php esc_attr_e( $role->term_id ); ?>" class="wwp-button-primary" type="button" value="Save changes"><?php esc_html_e( 'Add Discount', 'woocommerce-wholesale-pricing' ); ?></button>
																		<span data-tip="<?php esc_attr_e( $discountlabel ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
																		<div class="cart_total_discount_range_wrap">
																		<?php
																		
																		$name = 'wholesale_multi_user_cart_discount[tier_pricing]';
																		if ( !isset( $cart_tire_preces['tier_pricing'] ) ) {
																			$cart_tire_preces['tier_pricing'] = '';
																		}
																		echo wp_kses_post( '<div>' . tier_pricing_modal_popup( $discountlabel, 'cart_total_discount_range' . $role->term_id, $role->term_id, $cart_tire_preces['tier_pricing'], $name, '' ) . '</div>' );
																		?>
																		
																		</div>
																	</td>
																</tr>
															</tbody>
														</table>
													
													</div>
												</div>
											</div>
											<?php
										endforeach;
									?>
									</div>
									<?php
							}
							?>
							</div>
								<div role="tabpanel" class="tab-pane fade" id="section10">
								<table class="form-table wwp-main-settings">
									<tbody>
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Payment Method', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td>
												<input class="inp-cbx" style="display: none" id="enable_custom_payment_method" type="checkbox" value="yes" name="options[enable_custom_payment_method]" <?php echo ( isset( $settings['enable_custom_payment_method'] ) && 'yes' == $settings['enable_custom_payment_method'] ) ? 'checked' : ''; ?>>
												<label class="cbx cbx-square" for="enable_custom_payment_method">
													<span>
														<svg width="12px" height="9px" viewbox="0 0 12 9">
															<polyline points="1 5 4 8 11 1"></polyline>
														</svg>
													</span>
													<span><?php esc_html_e( 'Enable Custom Payment Method', 'woocommerce-wholesale-pricing' ); ?></span>
												</label>
											</td>
										</tr>
										<tr scope="row">
											<th><label for=""><?php esc_html_e( 'Payment Method Name', 'woocommerce-wholesale-pricing' ); ?></label></th>
											<td >
											<div id="payment_method_data">
												<?php 
												if (isset($settings['payment_method_name']) && !empty($settings['payment_method_name'])) {
													foreach ( (array) $settings['payment_method_name'] as $key => $payment_method_name_value) { 
														if ( !empty($payment_method_name_value) ) { 
															?>
																<div class="panel_payment_method_name">
																	<label for="payment_method_name">
																		<input type="text" class="regular-text payment_method_name" readonly name="options[payment_method_name][<?php esc_attr_e( $key); ?>]" 
																		value="<?php esc_attr_e( $key); ?>" 
																		Placeholder="<?php esc_html_e( 'Enter Payment Method Name', 'woocommerce-wholesale-pricing' ); ?>">
																	</label>
																	<span><a class="button removebtn"><?php esc_html_e( 'Remove', 'woocommerce-wholesale-pricing' ); ?></a></span>
																	<span><a class="button editbtn wwpeditbtn"><?php esc_html_e( 'Edit', 'woocommerce-wholesale-pricing' ); ?></a></span>
																</div>
															<?php
														}
													} 
												}
												?>
											</div>	 
												<div> <a class="button add_row_payment_method_name" ><?php esc_html_e( 'Add Method', 'woocommerce-wholesale-pricing' ); ?></a> </div>
											</td>
										</tr>
									</tbody>
								</table>
								<script>
								
									jQuery(document).ready(function(){
										jQuery(".add_row_payment_method_name").click(function() {
											template  =	'<div class="panel_payment_method_name " ><label for="payment_method_name">';
											template  += '<input type="text" class="regular-text payment_method_name"  name="options[payment_method_name][]" value="" Placeholder="Enter Payment Method Name">';
											template  += '</label> <span><a class="button removebtn">Remove</a></span></div>';
											console.log(template);
											jQuery("#payment_method_data").append(template);
										});
										jQuery(document).on( 'click','.button.removebtn', function (e) {
											jQuery(this).closest(".panel_payment_method_name").remove();
										});
										
										jQuery(document).on( 'click','.button.wwpeditbtn', function (e) { 
											alert('Editing the title of the gateway will result in disconnection of wholesale roles from this gateway.');
											console.log(jQuery(this).closest(".panel_payment_method_name").find('.payment_method_name'));
											jQuery(this).closest(".panel_payment_method_name").find('.payment_method_name').removeAttr('readonly');
										});
									});
								</script>
							</div>
						</div>
				<div class="sep20px">&nbsp;</div>
				<div class="main-save-settings">
				<button name="save-wwp_wholesale" class="wwp-button-primary save-general-setting" type="submit" value="Save changes"><?php esc_html_e( 'Save changes', 'woocommerce-wholesale-pricing' ); ?></button>
				</div>
				<div class="sep20px">&nbsp;</div>
					</div>

				</div>	
				</div>
				
			
			</form>
			<?php
		}
		public static function wwp_wholesale_notifications_callback() {
			?>
			<div class="wrap">
				<form method="post" action="options.php">
					<?php settings_errors(); ?>
					<?php settings_fields( 'wwp_wholesale_request_notifications' ); ?>
					<?php do_settings_sections( 'wwp_wholesale_request_notifications' ); ?>

					<div class="tab" role="tabpanel">
						<!-- Nav tabs -->
						<ul id="blkTabs" class="nav nav-tabs" role="tablist">
							<li role="presentation">
								<a href="#section_notification1" role="tab" data-toggle="tab"><?php esc_html_e( 'New Request', 'woocommerce-wholesale-pricing' ); ?></a>
							</li>
							<li role="presentation">
								<a href="#section_notification2" role="tab" data-toggle="tab"><?php esc_html_e( 'Request Rejection', 'woocommerce-wholesale-pricing' ); ?></a>
							</li>
							<li role="presentation">
								<a href="#section_notification3" role="tab" data-toggle="tab"><?php esc_html_e( 'Request Approval', 'woocommerce-wholesale-pricing' ); ?></a>
							</li>
							<li role="presentation">
								<a href="#section_notification4" role="tab" data-toggle="tab"><?php esc_html_e( 'Subscription Upgrade', 'woocommerce-wholesale-pricing' ); ?></a>
							</li>
						</ul>

						<!-- Tab panes -->
						<div class="tab-content tabs">
							<div role="tabpanel" class="tab-pane fade" id="section_notification1">
								<div id="accordion_notification">
									<!-- NEW REQUEST ADMIN NOTIFICATION -->
									<div class="card">
										<?php 
										$value = get_option( 'wwp_wholesale_admin_request_notification' ); 
										$stop_notification = get_option( 'wwp_wholesale_stop_admin_notification' );
										$to_others_notification = get_option( 'wwp_wholesale_send_notification_email_other' );
										wp_enqueue_script( 'wc-enhanced-select' );
										wp_enqueue_style( 'wwp-select2' );
										?>
																				<button onclick="return false;" class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse_admin_notify" aria-expanded="false" aria-controls="collapse_admin_notify">
											<?php esc_html_e( 'Admin Notification', 'woocommerce-wholesale-pricing' ); ?>
											<div class="wwp_signal">
											<?php
											if ( 'yes' === $value ) {
												?>
												<div class="wwp_circle wwp_circle_off">&nbsp;</div>
												<div class="wwp_circle wwp_circle_on active">&nbsp;</div>
												<?php
											} else {
												?>
												<div class="wwp_circle wwp_circle_off active">&nbsp;</div>
												<div class="wwp_circle wwp_circle_on">&nbsp;</div>
												<?php
											}
											?>
											</div>
										</button>										
										<div id="collapse_admin_notify" class="collapse" data-parent="#accordion_notification" >
											<div class="card-body">
												<table class="form-table wwp-main-settings">
													<tr>
														<th>
															<label for="wwp_wholesale_admin_request_notification"><?php esc_html_e( 'Role Request Notification', 'woocommerce-wholesale-pricing' ); ?></label>
														</th>
														<td>
															<input class="inp-cbx" style="display: none" type="checkbox" name="wwp_wholesale_admin_request_notification" value="yes" id="wwp_wholesale_admin_request_notification" <?php echo checked( 'yes', $value ); ?>>
															<label class="cbx cbx-square" for="wwp_wholesale_admin_request_notification">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'When checked, an Email will be sent to admin about the new requested User Role.', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
														</td>
													</tr>
													<tr>
														<th>
															<label for="wwp-wholesale-stop-admin-notification"><?php esc_html_e( "Don't Send Notification Emails to Current Site Admin: ", 'woocommerce-wholesale-pricing' ); ?></label>
														</th>
														<td>
															<input class="inp-cbx" style="display: none" type="checkbox" name="wwp_wholesale_stop_admin_notification" value="yes" id="wwp-wholesale-stop-admin-notification" <?php echo checked( 'yes', $stop_notification ); ?>>
															<label class="cbx cbx-square" for="wwp-wholesale-stop-admin-notification">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
															</label>
															</br>
															<label class=""><?php esc_html_e( get_option( 'admin_email' ) ); ?></label>
														</td>
													</tr>
													<!-- <tr>
														<th>
															<label for="wwp-wholesale-send-notification-email-other"><?php //esc_html_e( 'Send Email Notification To Other Than Admins:', 'woocommerce-wholesale-pricing' ); ?> </label>
														</th>
														<td>
															<input class="inp-cbx" style="display: none" type="checkbox" name="wwp_wholesale_send_notification_email_other" value="yes" id="wwp-wholesale-send-notification-email-other" <?php //echo checked( 'yes', $to_others_notification ); ?>>
															<label class="cbx cbx-square" for="wwp-wholesale-send-notification-email-other">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
															</label>
														</td>
													</tr> -->
													<?php
													$required = '';
													if ( 'yes' == $stop_notification ) {
														$required = 'required';
													}
													?>
													<tr scope="row" id="recipient-row">
														<th><label for=""><?php esc_html_e( 'Recipient(s)', 'woocommerce-wholesale-pricing' ); ?></label></th>
														<td>
														<?php $value = get_option( 'wwp_wholesale_admin_request_recipient' ); ?>
														<input <?php echo ! empty( $required ) ? esc_attr( $required ) : esc_attr( $required ); ?> id="wwp_wholesale_admin_request_recipient"  name="wwp_wholesale_admin_request_recipient"  type="text" class="widefat-100 regular-text" value="<?php echo esc_attr( $value ); ?>" >
														<span data-tip="<?php esc_html_e( 'Enter recipients (comma separated)', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>								
														</td>
													</tr>
													<tr>
														<th>
															<label for="wwp_wholesale_admin_request_subject"><?php esc_html_e( 'Email Subject', 'woocommerce-wholesale-pricing' ); ?></label>
														</th>
														<td>
															<?php $value = get_option( 'wwp_wholesale_admin_request_subject' ); ?>
															<input type="text" name="wwp_wholesale_admin_request_subject" id="wwp_wholesale_admin_request_subject" value="<?php echo esc_attr( $value ); ?>" class="widefat-100 regular-text"/>
														</td>
													</tr>
													
													<tr>
														<th>
															<label for="wwp_wholesale_admin_request_body"><?php esc_html_e( 'Message', 'woocommerce-wholesale-pricing' ); ?></label>
														</th>
														<td>
															<?php
																$content = html_entity_decode( get_option( 'wwp_wholesale_admin_request_body' ), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 );
																wp_editor(
																	$content,
																	'wwp_wholesale_admin_request_body',
																	array( 'textarea_rows' => 3 )
																);
																
															?>
															<p><?php esc_html_e( 'Email body for the new requested user role. Use {first_name}, {last_name}, {username}, {email}, {date}, {time} tag in body to get user email.', 'woocommerce-wholesale-pricing' ); ?></p>
														</td>
													</tr>
												</table>
											</div>
										</div>
									</div>


									<!-- NEW USER REQUEST USER NOTIFICATION-->
									<div class="card">
										<?php $value = get_option( 'wwp_wholesale_user_registration_notification' ); ?>
										<button onclick="return false;" class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse_user_notify" aria-expanded="false" aria-controls="collapse_user_notify">
											<?php esc_html_e( 'User Notification', 'woocommerce-wholesale-pricing' ); ?>
											<div class="wwp_signal">
											<?php
											if ( 'yes' === $value ) {
												?>
												<div class="wwp_circle wwp_circle_off">&nbsp;</div>
												<div class="wwp_circle wwp_circle_on active">&nbsp;</div>
												<?php
											} else {
												?>
												<div class="wwp_circle wwp_circle_off active">&nbsp;</div>
												<div class="wwp_circle wwp_circle_on">&nbsp;</div>
												<?php
											}
											?>
											</div>
										</button>										
										<div id="collapse_user_notify" class="collapse" data-parent="#accordion_notification" >
											<div class="card-body">
												<table class="form-table wwp-main-settings">
												<tr>
													<th>
														<label for="wwp_wholesale_user_registration_notification"><?php esc_html_e( 'Registration Notification', 'woocommerce-wholesale-pricing' ); ?></label>
													</th>
													<td>														
														<input class="inp-cbx" style="display: none" type="checkbox" name="wwp_wholesale_user_registration_notification" value="yes" id="wwp_wholesale_user_registration_notification" <?php echo checked( 'yes', $value ); ?>>
														<label class="cbx cbx-square" for="wwp_wholesale_user_registration_notification">
															<span>
																<svg width="12px" height="9px" viewbox="0 0 12 9">
																	<polyline points="1 5 4 8 11 1"></polyline>
																</svg>
															</span>
															<span><?php esc_html_e( 'When checked, an Email will be sent to user registration requested	.', 'woocommerce-wholesale-pricing' ); ?></span>
														</label>
													</td>
												</tr>
												<tr>
													<th>
														<label for="wwp_wholesale_registration_notification_subject"><?php esc_html_e( 'Email Subject', 'woocommerce-wholesale-pricing' ); ?></label>
													</th>
													<td>
														<?php $value = get_option( 'wwp_wholesale_registration_notification_subject' ); ?>
														<input type="text" name="wwp_wholesale_registration_notification_subject" id="wwp_wholesale_registration_notification_subject" value="<?php echo esc_attr( $value ); ?>" class="widefat-100 regular-text"/>
													</td>
												</tr>
												<tr>
													<th>
														<label for="wwp_wholesale_registration_notification_body"><?php esc_html_e( 'Message', 'woocommerce-wholesale-pricing' ); ?></label>
													</th>
													<td>
														<?php
															$content = html_entity_decode( get_option( 'wwp_wholesale_registration_notification_body' ), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 );
															wp_editor(
																$content,
																'wwp_wholesale_registration_notification_body',
																array( 'textarea_rows' => 3 )
															);
														?>
														<p><?php esc_html_e( 'Email body for the new registration user role. Use {first_name}, {last_name}, {username}, {email}, {date}, {time} tag in body to get user email.', 'woocommerce-wholesale-pricing' ); ?></p>
													</td>
												</tr>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div role="tabpanel" class="tab-pane fade" id="section_notification2">
								<div id="accordion_notification_rejected">
									<!-- USER REJECTION NOTIFICATION -->
									<div class="card">
										<?php $value = get_option( 'wwp_wholesale_user_rejection_notification' ); ?>
										<button onclick="return false;" class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse_user_notify_rejected" aria-expanded="true" aria-controls="collapse_user_notify_rejected">
											<?php esc_html_e( 'Request Rejection Notification', 'woocommerce-wholesale-pricing' ); ?>
											<div class="wwp_signal">
											<?php
											if ( 'yes' === $value ) {
												?>
												<div class="wwp_circle wwp_circle_off">&nbsp;</div>
												<div class="wwp_circle wwp_circle_on active">&nbsp;</div>
												<?php
											} else {
												?>
												<div class="wwp_circle wwp_circle_off active">&nbsp;</div>
												<div class="wwp_circle wwp_circle_on">&nbsp;</div>
												<?php
											}
											?>
											</div>
										</button>										
										<div id="collapse_user_notify_rejected" class="collapse show" data-parent="#accordion_notification_rejected" >
											<div class="card-body">
												<table class="form-table wwp-main-settings">
													<tr>
														<th>
															<label for="wwp_wholesale_user_rejection_notification"><?php esc_html_e( 'Rejection Notification', 'woocommerce-wholesale-pricing' ); ?></label>
														</th>
														<td>														
															<input class="inp-cbx" style="display: none" type="checkbox" name="wwp_wholesale_user_rejection_notification" value="yes" id="wwp_wholesale_user_rejection_notification" <?php echo checked( 'yes', $value ); ?>>
															<label class="cbx cbx-square" for="wwp_wholesale_user_rejection_notification">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'When checked, an Email will be sent to user Rejection requested.', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
														</td>
													</tr>
													<tr>
														<th>
															<label for="wwp_wholesale_rejection_notification_subject"><?php esc_html_e( 'Email Subject', 'woocommerce-wholesale-pricing' ); ?></label>
														</th>
														<td>
															<?php $value = get_option( 'wwp_wholesale_rejection_notification_subject' ); ?>
															<input type="text" name="wwp_wholesale_rejection_notification_subject" id="wwp_wholesale_rejection_notification_subject" value="<?php echo esc_attr( $value ); ?>" class="widefat-100 regular-text"/>
														</td>
													</tr>
													<tr>
														<th>
															<label for="wwp_wholesale_rejection_notification_body"><?php esc_html_e( 'Message', 'woocommerce-wholesale-pricing' ); ?></label>
														</th>
														<td>
															<?php
																$content = html_entity_decode( get_option( 'wwp_wholesale_rejection_notification_body' ), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 );
																wp_editor(
																	$content,
																	'wwp_wholesale_rejection_notification_body',
																	array( 'textarea_rows' => 3 )
																);
															?>
															<p><?php esc_html_e( 'Email body for the new rejection user role. Use {first_name}, {last_name}, {username}, {email}, {date}, {time} tag in body to get user email.', 'woocommerce-wholesale-pricing' ); ?></p>
														</td>
													</tr>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div role="tabpanel" class="tab-pane fade" id="section_notification3">
								<div id="accordion_notification_approved">
									<!-- USER REJECTION NOTIFICATION -->
									<div class="card">
										<?php $value = get_option( 'wwp_wholesale_request_approve_notification' ); ?>
										<button onclick="return false;" class="btn btn-link" data-toggle="collapse" data-target="#collapse_user_notify_approved" aria-expanded="true" aria-controls="collapse_user_notify_approved">
											<?php esc_html_e( 'Request Approval Notification', 'woocommerce-wholesale-pricing' ); ?>
											<div class="wwp_signal">
											<?php
											if ( 'yes' === $value ) {
												?>
												<div class="wwp_circle wwp_circle_off">&nbsp;</div>
												<div class="wwp_circle wwp_circle_on active">&nbsp;</div>
												<?php
											} else {
												?>
												<div class="wwp_circle wwp_circle_off active">&nbsp;</div>
												<div class="wwp_circle wwp_circle_on">&nbsp;</div>
												<?php
											}
											?>
											</div>
										</button>										
										<div id="collapse_user_notify_approved" class="collapse show" data-parent="#accordion_notification_approved" >
											<div class="card-body">
												<table class="form-table wwp-main-settings">
													<tr>
														<th>
															<label for="wwp_wholesale_request_approve_notification"><?php esc_html_e( 'Request Approval Email', 'woocommerce-wholesale-pricing' ); ?></label>
														</th>
														<td>																													
															<input class="inp-cbx" style="display: none" type="checkbox" name="wwp_wholesale_request_approve_notification" value="yes" id="wwp_wholesale_request_approve_notification" <?php echo checked( 'yes', $value ); ?>>
															<label class="cbx cbx-square" for="wwp_wholesale_request_approve_notification">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'When checked, an Email will be sent to user about the approval of their requested User Role.', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
														</td>
													</tr>
													<tr>
														<th>
															<label for="wwp_wholesale_email_request_subject"><?php esc_html_e( 'Email Subject', 'woocommerce-wholesale-pricing' ); ?></label>
														</th>
														<td>
															<?php $value = get_option( 'wwp_wholesale_email_request_subject' ); ?>
															<input type="text" name="wwp_wholesale_email_request_subject" id="wwp_wholesale_email_request_subject" value="<?php echo esc_attr( $value ); ?>" class="widefat-100 regular-text"/>
														</td>
													</tr>
													<tr>
														<th>
															<label for="wwp_wholesale_email_request_body"><?php esc_html_e( 'Message', 'woocommerce-wholesale-pricing' ); ?></label>
														</th>
														<td>
															<?php
																$content = html_entity_decode( get_option( 'wwp_wholesale_email_request_body' ), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 );
																wp_editor(
																	$content,
																	'wwp_wholesale_email_request_body',
																	array( 'textarea_rows' => 3 )
																);
															?>
															<p><?php esc_html_e( 'Email body for the approval of User Role request. Use {first_name}, {last_name}, {username}, {email}, {date}, {time} tag in body to get user email.', 'woocommerce-wholesale-pricing' ); ?></p>
														</td>
													</tr>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div role="tabpanel" class="tab-pane fade" id="section_notification4">
								<div id="accordion_notification_subscription">
									<!-- USER REJECTION NOTIFICATION -->
									<div class="card">
										<?php $value = get_option( 'wwp_wholesale_subscription_role_notification' ); ?>
										<button onclick="return false;" class="btn btn-link" data-toggle="collapse" data-target="#collapse_subscription_notification" aria-expanded="true" aria-controls="collapse_subscription_notification">
											<?php esc_html_e( 'User Role Upgrade Notification', 'woocommerce-wholesale-pricing' ); ?>
											<div class="wwp_signal">
											<?php
											if ( 'yes' === $value ) {
												?>
												<div class="wwp_circle wwp_circle_off">&nbsp;</div>
												<div class="wwp_circle wwp_circle_on active">&nbsp;</div>
												<?php
											} else {
												?>
												<div class="wwp_circle wwp_circle_off active">&nbsp;</div>
												<div class="wwp_circle wwp_circle_on">&nbsp;</div>
												<?php
											}
											?>
											</div>
										</button>										
										<div id="collapse_subscription_notification" class="collapse show" data-parent="#accordion_notification_subscription" >
											<div class="card-body">
												<table class="form-table wwp-main-settings">
													<?php
													/**
													* Hooks
													*
													* @since 3.0
													*/
													if ( in_array( 'woocommerce-subscriptions/woocommerce-subscriptions.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
														?>
														<tr>
															<th>
																<label for="wwp_wholesale_subscription_role_notification"><?php esc_html_e( 'Enable Role Upgrade Notification', 'woocommerce-wholesale-pricing' ); ?></label>
															</th>
															<td>
																<input class="inp-cbx" style="display: none" type="checkbox" name="wwp_wholesale_subscription_role_notification" value="yes" id="wwp_wholesale_subscription_role_notification" <?php echo checked( 'yes', $value ); ?>>
																<label class="cbx cbx-square" for="wwp_wholesale_subscription_role_notification">
																	<span>
																		<svg width="12px" height="9px" viewbox="0 0 12 9">
																			<polyline points="1 5 4 8 11 1"></polyline>
																		</svg>
																	</span>
																	<span><?php esc_html_e( 'When checked, an Email will be sent to user on the role upgrade after subscription.', 'woocommerce-wholesale-pricing' ); ?></span>
																</label>
															</td>
														</tr>
														<tr>
															<th>
																<label for="wwp_wholesale_subscription_role_subject"><?php esc_html_e( 'Email Subject', 'woocommerce-wholesale-pricing' ); ?></label>
															</th>
															<td>
																<?php $value = get_option( 'wwp_wholesale_subscription_role_subject' ); ?>
																<input type="text" name="wwp_wholesale_subscription_role_subject" id="wwp_wholesale_subscription_role_subject" value="<?php echo esc_attr( $value ); ?>" class="widefat-100 regular-text"/>
															</td>
														</tr>
														<tr>
															<th>
																<label for="wwp_wholesale_subscription_role_body"><?php esc_html_e( 'Message', 'woocommerce-wholesale-pricing' ); ?></label>
															</th>
															<td>
																<?php
																	$content = html_entity_decode( get_option( 'wwp_wholesale_subscription_role_body' ), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 );
																	wp_editor(
																		$content,
																		'wwp_wholesale_subscription_role_body',
																		array( 'textarea_rows' => 3 )
																	);
																?>
																<p><?php esc_html_e( 'Email body for the role upgrade after subscription. Use {first_name}, {last_name}, {username}, {date}, {time}, {email} & {role} tag in body to get user email.', 'woocommerce-wholesale-pricing' ); ?></p>
															</td>
														</tr>
														<?php
													} else {
														esc_html_e( 'You need to enable Woocommerce Subscription plugin.', 'woocommerce-wholesale-pricing' );
													}
													?>
													<!-- ends version 1.3.0 -->
												</table>
											</div>
											
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php /*submit_button();*/ ?>
					<div class="sep20px">&nbsp;</div>
					<input type="submit" name="submit" id="submit" class="wwp-button-primary" value="<?php esc_html_e( 'Save Changes', 'woocommerce-wholesale-pricing' ); ?>">					
					<div class="sep20px">&nbsp;</div>
				</form>
			</div>
			<?php
		}
		/**
		 * Initialize product wholesale data tab
		 *
		 * @since   1.0
		 * @version 1.0
		 */
		public function wwp_add_wholesale_product_data_tab( $product_data_tabs ) {
			$product_data_tabs['wwp-wholesale-tab'] = array(
				'label'  => esc_html__( 'Wholesale', 'woocommerce-wholesale-pricing' ),
				'target' => 'wwp_wholesale_product_data',
			);
			return $product_data_tabs;
		}
		/**
		 * Initialize product wholesale data tab
		 *
		 * @since   1.0
		 * @version 1.0
		 */
		public function wcpp_custom_style() {
			?>
			<style>
				.wwp-wholesale-tab_tab a:before {
					font-family: Dashicons;
					content: "\f240" !important;
				}
			</style>
			<?php
		}
		/**
		 * Product wholesale data tab multi users
		 *
		 * @since   1.0
		 * @version 1.0
		 */
		public function wwp_add_wholesale_product_data_fields_multi() {
			// version 1.3.0
			global $post;
			$product_id = $post->ID;
			$roles      = array();
			$taxroles   = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
			if ( ! empty( $taxroles ) ) {
				foreach ( $taxroles as $key => $role ) {
					$roles[ $role->slug ] = $role->name;
				}
			}
			?>
			<div id="wwp_wholesale_product_data" class="panel woocommerce_options_panel">
			<?php
			wp_nonce_field( 'wwp_product_wholesale_nonce', 'wwp_product_wholesale_nonce' );
			$wwp_icon_url  = get_post_meta( $product_id, 'wwp_icon_url', true );
			$wwp_image_url = get_post_meta( $product_id, 'wwp_image_url', true );
			woocommerce_wp_checkbox(
				array(
					'id'            => '_wwp_hide_for_customer',
					'wrapper_class' => '_wwp_hide_for_customer',
					'label'         => esc_html__( 'Hide Product For Customer Role', 'woocommerce-wholesale-pricing' ),
					'description'   => esc_html__( 'Hide this product from users having customer role', 'woocommerce-wholesale-pricing' ),
				)
			);

			woocommerce_wp_checkbox(
				array(
					'id'            => '_wwp_hide_for_visitor',
					'wrapper_class' => '_wwp_hide_for_visitor',
					'label'         => esc_html__( 'Hide Product For Guest Users', 'woocommerce-wholesale-pricing' ),
					'description'   => esc_html__( 'Hide this product from visitors', 'woocommerce-wholesale-pricing' ),
				)
			);

			$value = get_post_meta( $product_id, 'wholesale_product_visibility_multi', true );
			woocommerce_wp_select(
				array(
					'id'                => 'wholesale_product_visibility_multi[]',
					'label'             => esc_html__( 'Hide Product for Wholesaler Roles', 'woocommerce-wholesale-pricing' ),
					'type'              => 'select',
					'class'             => 'wc-enhanced-select',
					'style'             => 'min-width: 50%;',
					'desc_tip'          => 'true',
					'description'       => esc_html__( 'Choose specific user roles to hide the product.', 'woocommerce-wholesale-pricing' ),
					'options'           => $roles,
					'value'             => $value,
					'custom_attributes' => array(
						'multiple' => 'multiple',
					),
				)
			); // ends version 1.3.0

			$settings = get_option( 'wwp_wholesale_pricing_options', true );
			if ( isset($settings['variation_table_enable']) && 'yes' == $settings['variation_table_enable']) {
				$product = wc_get_product( $product_id );
				if ( $product->get_type() == 'variable' ) {
					
					woocommerce_wp_checkbox(
						array(
							'id'            => 'wwp_variation_explode_eneble',
							'wrapper_class' => 'wwp_variation_explode_eneble',
							'label'         => esc_html__( 'Enable', 'woocommerce-wholesale-pricing' ),
							'description'   => esc_html__( 'Enable Attributes Quantity', 'woocommerce-wholesale-pricing' ),
						)
					);
					
					$available_attributes = $product->get_attributes();
					$formatted_attributes = array();
					$attributes           = $product->get_attributes();
					foreach ($attributes as $attr => $attr_deets) {
						$attribute_label = wc_attribute_label($attr);
						if ( isset( $attributes[ $attr ] ) || isset( $attributes[ 'pa_' . $attr ] ) ) {
							$attribute = isset( $attributes[ $attr ] ) ? $attributes[ $attr ] : $attributes[ 'pa_' . $attr ];
							if ( $attribute['is_taxonomy'] ) {
								$formatted_attributes[$attr] = $attribute_label;
							} else {
								$attribute_name                                = $attr_deets->get_data();
								$formatted_attributes[$attribute_name['name']] = $attribute_name['name'];
							}
						}
					}
					
					woocommerce_wp_select(
						array(
							'id'                => 'wwp_variation_attribute_with_qty',
							'label'             => esc_html__( 'Select Primray Attribute', 'woocommerce-wholesale-pricing' ),
							'type'              => 'select',
							'class'             => 'wc-enhanced-select',
							'style'             => 'min-width: 50%;',
							'desc_tip'          => 'true',
							'description'       => esc_html__( 'Select the attribute to dispaly along side the quantity counter', 'woocommerce-wholesale-pricing' ),
							'options'           => $formatted_attributes,
							'value'             => get_post_meta( $product_id, 'wwp_variation_attribute_with_qty', true ),
						)
					);
				}
			}

			if ( isset($settings['request_for_sample_enable']) && 'yes' == $settings['request_for_sample_enable']) {
				$product = wc_get_product( $product_id );
				woocommerce_wp_checkbox(
					array(
						'id'            => 'request_for_sample_enable',
						'wrapper_class' => 'request_for_sample_enable',
						'label'         => esc_html__( 'Sample Product', 'woocommerce-wholesale-pricing' ),
						'description'   => esc_html__( 'Enable sample for this product', 'woocommerce-wholesale-pricing' ),
					)
				);
				
				$sample_product_id        = get_post_meta( $product_id, 'sample_product_id', true );
				$sample_product_id_hidden = get_post_meta( $product_id, 'sample_product_id_hidden', true );
				
				
				if ( ! empty( $sample_product_id ) ) {
					$register_redirect_title = get_the_title( $sample_product_id_hidden ) . ' - (' . $sample_product_id_hidden . ')';
				} else {
					$register_redirect_title  = '';
					$sample_product_id_hidden = '';
				}

				$value = get_post_meta( $product_id, 'sample_product_id', true );
				woocommerce_wp_text_input(
					array(
						'id'            => 'sample_product_id',
						'value'         => $register_redirect_title,
						'label'         => __( 'Select Sample Product', 'woocommerce-wholesale-pricing' ),
						'desc_tip'      => 'true',
						'description'   => esc_html__( 'Select sample for this product', 'woocommerce-wholesale-pricing' ),
						'wrapper_class' => 'sample_product_id',
					)
				);

				woocommerce_wp_hidden_input(
					array(
						'id'    => 'sample_product_id_hidden', 
						'value' => $sample_product_id_hidden,
					)
				);
			}

			?>
				<div id="wwp_wholesale_product_data" class="panel woocommerce_options_panel">
					<div id="variable_product_options" class=" wc-metaboxes-wrapper" style="display: block;">
						<div id="variable_product_options_inner">
							<div id="message" class="inline notice woocommerce-message">
								<p><?php printf('%1$s <strong>%2$s</strong> %3$s', esc_html__('For', 'woocommerce-wholesale-pricing'), esc_html__('Multi-user wholesale roles', 'woocommerce-wholesale-pricing'), esc_html__('manage price from wholesale metabox', 'woocommerce-wholesale-pricing')); ?></p>
								<p><a class="button-primary" id="wholesale-pricing-pro-multiuser-move"><?php esc_html_e( 'Move', 'woocommerce-wholesale-pricing' ); ?></a></p>
							</div>
						</div>
					</div>
				</div>
		<h2 class=""><b><?php esc_html_e( 'Select Attachment File', 'woocommerce-wholesale-pricing' ); ?></b></h2>	
		<p class="form-field">
			<label for="wwp_icon_url"><?php esc_html_e( 'Upload Icon', 'woocommerce-wholesale-pricing' ); ?></label>
			<input type="text" name="wwp_icon_url" id="wwp_icon_url" value="<?php echo esc_attr( $wwp_icon_url ); ?>" class="short regular-text">
			<span class="wwp_media_selection">
				<input type="button" name="www_upload_icon" id="www_upload_icon" class="button-secondary" value="<?php esc_html_e( 'Upload', 'woocommerce-wholesale-pricing' ); ?>">
				<input type="button" id="www_remove_icon" class="button-secondary" value="Remove">
			</span>
		</p>
		<p class="form-field">
			<label for="wwp_image_url"><?php esc_html_e( 'Upload Attachment', 'woocommerce-wholesale-pricing' ); ?></label>
			<input type="text" name="wwp_image_url" id="wwp_image_url" value="<?php echo esc_attr( $wwp_image_url ); ?>"  class="short regular-text">
			<span class="wwp_media_selection">
				<input type="button" name="www_upload_attachment" id="www_upload_attachment" class="button-secondary" value="<?php esc_html_e( 'Upload', 'woocommerce-wholesale-pricing' ); ?>">
				<input type="button" id="www_remove_attachment" class="button-secondary" value="Remove">
			</span>
		</p>
			<?php 
				woocommerce_wp_text_input(
					array(
						'id' => 'wwp_attachment_title',
						'placeholder' => __( 'Set Custom Title', 'woocommerce-wholesale-pricing' ),
						'label' => __('Set Custom Title', 'woocommerce-wholesale-pricing'),
						'type' => 'text',
						'custom_attributes' => array(
							'step' => 'any',
							'min' => '0',
						),
					)
				);
				woocommerce_wp_text_input(
					array(
						'id' => 'wwp_attachment_text_link',
						'placeholder' => __( 'Text For Download Link', 'woocommerce-wholesale-pricing' ),
						'label' => __('Text For Download Link', 'woocommerce-wholesale-pricing'),
						'type' => 'text',
						'custom_attributes' => array(
							'step' => 'any',
							'min' => '0',
						),
					)
				); 
			?>
		<h2 class=""><b><?php esc_html_e( 'Current Attachment', 'woocommerce-wholesale-pricing' ); ?> = <a onclick="wwp_open_attachment()" url="<?php echo esc_attr( $wwp_image_url ); ?>" class="wwp_download_link" target="_blank" ><?php esc_html_e( 'Download Here', 'woocommerce-wholesale-pricing' ); ?></a></b></h2>		
		</br></br>
		<script type="text/javascript">
			function wwp_open_attachment () {
				url = jQuery('.wwp_download_link').attr('url');
				
				if (url != '' && url  != undefined) {
					window.open(url);
				}
				return false;
				
				}
				jQuery(document).ready(function($) {
					$('#www_upload_icon').click(function(e) {
					e.preventDefault();
					var image = wp.media({ 
						title: 'Upload Icon',
						multiple: false
					}).open().on('select', function(e) {
						var uploaded_image = image.state().get('selection').first();
						var image_url = uploaded_image.toJSON().url;
						$('#wwp_icon_url').val(image_url);
					});
				});
			
				$('#www_upload_attachment').click(function(e) {
					e.preventDefault();
					var image = wp.media({ 
						title: 'Upload Attachment',
						multiple: false
					}).open().on('select', function(e) {
						var uploaded_image = image.state().get('selection').first();
						var image_url = uploaded_image.toJSON().url;
						$('#wwp_image_url').val(image_url);
						$('wwp_download_link').val(image_url);
						$('.wwp_download_link').attr( 'url', image_url );
						console.log(uploaded_image);
					});
				});
	
				$('#www_remove_attachment').click(function(e) {
					$('#wwp_image_url').val('');
					$('.wwp_download_link').attr( 'url', '' );
				});
				$('#www_remove_icon').click(function(e) {
					$('#wwp_icon_url').val('');
				});    
				$('#wwp_image_url').on('input',function(e){
					if ( this.value  ) {
						$('.wwp_download_link').attr( 'url', this.value );
					} else {
						$('.wwp_download_link').attr( 'url', '' );
					}
				});
			});
		</script>
			</div>
			<?php
		}

		public function wwp_screen_ids( $screen_ids ) {
			$custom     = array( 'edit-wholesale_user_roles' );
			$screen_ids = array_merge( $custom, $screen_ids );
			return $screen_ids;
		}
		public function wwp_admin_script_style() {
			/**
			* Hooks
			*
			* @since 3.0
			*/
			$quantity_flag_cart_total_discount = apply_filters( 'wwp_enable_quantity_flag_cart_total_discount', false );
			wp_enqueue_style( 'jquery-ui-styles' );
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'wwp-script', WWP_PLUGIN_URL . 'assets/js/admin-script.js', array( 'jquery' ), '1.0' );
			wp_enqueue_script( 'wwp-chart', WWP_PLUGIN_URL . 'assets/js/chart.js', array( 'jquery' ), '1.0');
			$settings = get_option( 'wwp_wholesale_pricing_options', true );
			unset( $settings['wholesale_css'] );
			wp_localize_script(
				'wwp-script',
				'wwpscript',
				array(
					'ajaxurl'                => admin_url( 'admin-ajax.php' ),
					'admin_url'              => admin_url(),
					'ajax_nonce'             => wp_create_nonce( 'wwp_wholesale_pricing' ),
					'wwp_wholesale_settings' => $settings,
					'quantity_flag_cart_total_discount' => $quantity_flag_cart_total_discount,
					'wc_get_cart_url' => wc_get_cart_url(),
				)
			);
			add_thickbox();

			wp_enqueue_style( 'wwp-style', WWP_PLUGIN_URL . 'assets/css/admin-style.css', array(), '1.0' );
			$screen = get_current_screen();
			$screen_id    = $screen ? $screen->id : '';     
			/**
			*  Filter 
			*                            
			*  @since 2.3.0 
			*/
			if ( ! in_array( $screen_id, apply_filters( 'wwp_disable_scripts', array() ) ) ) {
				wp_enqueue_style('wwp_fontawesome', WWP_PLUGIN_URL . 'assets/css/font-awesome.min.css', array(), '1.0');
			}
			// wp_enqueue_script('wwp-popper-min-js', WWP_PLUGIN_URL . 'assets/js/popper.min.js', array( 'jquery' ), '1.0.0' );
			// wp_enqueue_script('wwp-bootstrap-js', WWP_PLUGIN_URL . 'assets/js/bootstrap.min.js', array( 'jquery' ), '4.5.3' );

			// wp_enqueue_script('wwp-bootstrap-select-js', WWP_PLUGIN_URL . 'assets/js/bootstrap-select.min.js', array( 'wwp-bootstrap-js' ), '1.13.14' );
			wp_enqueue_script( 'wwp-popper-min-js', WWP_PLUGIN_URL . 'assets/js/popper.min.js', array( 'jquery' ), '1.0.0' );
			wp_enqueue_script( 'wwp-bootstrap-js', WWP_PLUGIN_URL . 'assets/js/bootstrap.min.js', array( 'jquery' ), '4.5.3' );
			wp_enqueue_script( 'wwp-bootstrap-select-js', WWP_PLUGIN_URL . 'assets/js/bootstrap-select.min.js', array( 'wwp-bootstrap-js' ), '1.13.14' );

			$wholesale_screens = array( 'wwp_wholesale', 'wwp_wholesale_settings', 'wwp-registration-setting', 'wwp_wholesale_notifcations', 'wwp-bulk-wholesale-pricing' );

			if ( ( isset( $_GET['page'] ) && in_array( sanitize_text_field( $_GET['page'] ), $wholesale_screens ) ) || ( in_array( $screen_id, array( 'wwp_requests' ) ) ) ) {
				wp_enqueue_style( 'wwp-data-tip', WWP_PLUGIN_URL . 'assets/css/data-tip.min.css', array(), '1.0' );
				wp_enqueue_style( 'wwp-bootstrap', WWP_PLUGIN_URL . 'assets/css/bootstrap.min.css', array(), '4.5.3', 'all' );
				wp_enqueue_style( 'wwp-bootstrap-select', WWP_PLUGIN_URL . 'assets/css/bootstrap-select.min.css', array(), '1.13.14', 'all' );
				wp_enqueue_style( 'wwp-admin-new', WWP_PLUGIN_URL . 'assets/css/admin-style-new.css', array(), '1.5.0', 'all' );
			}
			wp_register_style( 'wwp-select2', WWP_PLUGIN_URL . 'assets/css/wc-enhanced-select.css', false, '1.0.0' );
		}
	}
	new WWP_Wholesale_Pricing_Backend();
}
