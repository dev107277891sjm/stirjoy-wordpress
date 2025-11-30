<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( ! class_exists( 'WWP_Easy_Wholesale_Multiuser' ) ) {

	class WWP_Easy_Wholesale_Multiuser {
		public $product_obj;
		
		public $tire_table;

		public $exclude_ids = array();

		public $settings    = array();

		public function __construct() { 
			$this->tire_table                        = false;
			$this->settings['restrict_store_access'] = '';
			$this->settings                          = get_option( 'wwp_wholesale_pricing_options', true );
			
			if ( isset( $this->settings['restrict_store_access'] ) && 'yes' != $this->settings['restrict_store_access'] ) {
				$ws_group = wwp_get_group_post_by_proid( 0, get_current_user_id() );
				if ( ! is_wholesaler_user( get_current_user_id() ) && ( ! wwp_guest_wholesale_pricing_enabled() ) && ! $ws_group ) {
					return;
				}
			}
			add_filter( 'woocommerce_get_price_html', array( $this, 'wwp_change_product_price_display' ), 200, 2 );
			add_filter( 'woocommerce_cart_item_price', array( $this, 'wwp_change_product_price_display' ), 200, 2 );

			add_filter( 'woocommerce_variable_sale_price_html', array( $this, 'wwp_variable_price_format' ), 200, 2 );
			add_filter( 'woocommerce_variable_price_html', array( $this, 'wwp_variable_price_format' ), 200, 2 );

			add_filter( 'woocommerce_product_get_price', array( $this, 'wwp_regular_price_change' ), 200, 2 );
			add_filter( 'woocommerce_product_get_regular_price', array( $this, 'wwp_regular_price_change' ), 200, 2 );
			// Product variations (of a variable product)
			add_filter( 'woocommerce_product_variation_get_price', array( $this, 'wwp_regular_price_change' ), 200, 2 );
			add_filter( 'woocommerce_product_variation_get_regular_price', array( $this, 'wwp_regular_price_change' ), 200, 2 );
		
			add_action( 'woocommerce_before_calculate_totals', array( $this, 'wwp_override_product_price_cart' ), 200 );
			add_action( 'woocommerce_after_calculate_totals', array( $this, 'wwp_override_price_filter_on' ), 200 );

			add_filter( 'woocommerce_available_payment_gateways', array( $this, 'wwp_available_payment_gateways' ), 200, 1 );
			add_filter( 'woocommerce_package_rates', array( $this, 'wwp_restrict_shipping_wholesaler' ), 10, 2 );
			add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'add_the_date_validation' ), 200, 5 );
			// add_filter( 'woocommerce_update_cart_validation', array($this, 'add_the_date_validation'), 200, 5 );

			add_action( 'pre_get_posts', array( $this, 'default_wholesaler_products_only' ), 10, 1 );
			add_action( 'woocommerce_shortcode_products_query', array( $this, 'woocommerce_shortcode_products_query' ), 200, 1 );
			add_action( 'woocommerce_products_widget_query_args', array( $this, 'woocommerce_shortcode_products_query' ), 200, 1 );

			add_filter( 'woocommerce_available_variation', array( $this, 'filter_woocommerce_available_variation' ), 200, 3 );
			add_filter( 'woocommerce_cart_crosssell_ids', array( $this, 'remove_non_wholesale_product_crosssell' ), 200, 2 );

			// Cart display html fixed
			add_filter( 'woocommerce_before_cart', array( $this, 'woocommerce_before_cart' ), 200 );
			// Cart display html fixed
			add_filter( 'woocommerce_after_cart_table', array( $this, 'woocommerce_after_cart_table' ), 200 );
			
			// WooCommerce Block Cart step quantity cart item 
			add_filter( 'woocommerce_store_api_product_quantity_multiple_of', array( $this, 'wwp_filter_woocommerce_quantity_step_block_cart' ), 10, 2 );
			// Step Quantity functionalty hook
			add_filter( 'woocommerce_quantity_input_args', array( $this, 'wwp_filter_woocommerce_quantity_input_args' ), 10, 2 );
			add_filter( 'woocommerce_loop_add_to_cart_args', array( $this, 'wwp_filter_woocommerce_quantity_input_args' ), 10, 2 );
			
			add_action( 'woocommerce_single_variation', array( $this, 'variation_load_tire_priceing_table' ) );
			add_action( 'woocommerce_before_add_to_cart_form', array( $this, 'simple_load_tire_priceing_table' ) );
			
			// Support Add Product Bundle
			add_filter( 'woocommerce_bundled_item_price_html', array( $this, 'wwp_change_product_price_display' ), 200, 2 );
			add_action( 'woocommerce_after_bundle_price', array( $this, 'woocommerce_after_bundle_price' ) );
			
			// Support Add  WooCommerce Product Add-ons
			add_filter( 'wwp_override_product_price_cart_price', array( $this, 'wwp_override_product_price_cart_price' ), 10, 2 ); 
			add_filter( 'woocommerce_quantity_input_args', array( $this, 'wwp_quantity_input_args' ), 10, 2 );
			add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 20, 2 );
			
			// Single page product attachment
			$this->single_page_product_attachment();
			
			add_action( 'woocommerce_cart_contents', array( $this, 'woocommerce_cart_contents' ) );
			
			add_filter( 'woocommerce_dropdown_variation_attribute_options_html', array( $this, 'wwp_explode_variation_display' ), 10, 2);
			add_action( 'wp_loaded', array( $this, 'add_to_cart_multiple_variation' ), 10 );
			add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'show_content_after_add_to_cart' ) );
			add_action('template_redirect', array( $this, 'request_sample_template_redirect' ) );
			add_filter( 'body_class', array( $this, 'wwp_custom_class' ) );
			
			add_action( 'woocommerce_before_mini_cart_contents', array( $this, 'woocommerce_before_mini_cart_contents' ) );
			add_action( 'woocommerce_store_api_cart_errors', array( $this, 'woocommerce_cart_min_subtotal_errors' ) );
			/** 
			 * Filter Hook: wwp_hook_for_min_subtotal
			 * 
			 * @since 2.7
			 * 
			 * Type: Filter
			 */
			$hook = apply_filters('wwp_hook_for_min_subtotal', 'woocommerce_before_cart_table' );
			add_action( $hook, array( $this, 'woocommerce_cart_min_subtotal_errors' ) );
			add_filter( 'woocommerce_subscriptions_product_price_string', array( $this, 'woocommerce_subscriptions_product' ), 10 , 3 );

			/** 
			 * Filter Hook: wpp_apply_discount_on_product_addon
			 * 
			 * @since 2.7
			 * 
			 * Type: Filter
			 */
			$addon = apply_filters( 'wpp_apply_discount_on_product_addon', false );
			if ( $addon ) {
				// discount only product base
				add_filter( 'woocommerce_product_addons_option_price_raw', array( $this, 'wwp_woo_product_addons' ), 50, 2 );
				add_filter( 'woocommerce_add_cart_item_data', array( $this, 'wwp_product_add_cart_item_data' ), 50, 2);
			}
			
			/** 
			 * Hook: woocommerce_cart_calculate_fees
			 * 
			 * @since 2.4.0
			 * 
			 * Type: action
			 */
		}

		public function wwp_exclude_categories_for_wholesale_roles() {

			$user_roles = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );

			if ( empty( $user_roles ) || is_wp_error( $user_roles ) ) {
				return array();
			}
			// Get saved excluded categories from options
			$settings = get_option( 'wholesale_multi_user_pricing' );
			if ( empty( $settings ) || ! is_array( $settings ) ) {
				return array();
			}

			$excluded_cat_ids = array();

			foreach ( $user_roles as $role ) {
				$term_id = $role->term_id;
				if (
					isset( $settings[ $term_id ]['exclude_categories'] ) &&
					is_array( $settings[ $term_id ]['exclude_categories'] )
				) {
					$excluded_cat_ids = array_merge( $excluded_cat_ids, $settings[ $term_id ]['exclude_categories'] );
				}
			}

			if ( empty( $excluded_cat_ids ) ) {
				return array();
			}

			return $excluded_cat_ids;
		}
	
		public function wwp_product_add_cart_item_data( $cart_item_data, $product_id ) {
			if ( ! class_exists('WC_Product_Addons_Helper') ) {
				return $cart_item_data;
			}
			$data = get_post_meta( $product_id, 'wholesale_multi_user_pricing', true );
			$role              = get_current_user_role_id();
			if ( isset( $data[ $role ] ) ) {
				$my = $data[$role];
				if (isset($my['discount_type']) && isset($my['wholesale_price'])) {
					foreach ($cart_item_data['addons'] as $key => $value) {
						$price = $this->change_price( $cart_item_data['addons'][$key]['price'], $my['discount_type'], $my['wholesale_price'], $product_id);
						$cart_item_data['addons'][$key]['price'] = $price;
					}
				}
			}

			return $cart_item_data;
		}

		public function wwp_woo_product_addons( $price, $addon ) {

			global $product;
			if ( ! $product ) {
				return $price;
			}
			$role              = get_current_user_role_id();
			$product_id = $product->get_id();
			$pr = (int) $addon['price'];
			if ( empty( $pr ) ) {
				return $price;
			}
			$data = get_post_meta( $product_id, 'wholesale_multi_user_pricing', true );
			if ( isset( $data[ $role ] ) && 'product' == get_post_type( $product_id ) && isset( $data[ $role ]['discount_type'] ) && isset( $data[ $role ]['wholesale_price'] ) ) {

				$my = $data[ $role ];
				if ( isset( $my['min_quatity'] ) && ! empty( $my['min_quatity'] ) ) {
					$wholesale_qty = $my['min_quatity'];
				}


				/**
				 * Hooks
				 *
				 * @since 3.0
				 */
				$price = apply_filters( 'wwp_mnm_get_price', $pr, $product );

				return $this->change_price( $price, $my['discount_type'], $my['wholesale_price'], $product_id);
			} elseif ( 'product_variation' == get_post_type( $product_id ) ) {

				$variation_id = $product_id;
				$product_id   = wp_get_post_parent_id( $product_id );

				$data = get_post_meta( $product_id, 'wholesale_multi_user_pricing', true );
				if ( isset( $data[ $role ] ) ) {
					if ( isset( $data[ $role ][ $variation_id ]['wholesaleprice'] ) && !empty( $data[ $role ][ $variation_id ]['wholesaleprice'] ) ) {
						$my = $data[ $role ][ $variation_id ];
						if ( isset( $my['qty'] ) && ! empty( $my['qty'] ) ) {
							$wholesale_qty = $my['qty'];
						}
						$price = $this->get_variable_wholesale_price( $data[ $role ][ $variation_id ], $variation_id );
						return $price;
					} else {
						return $price;
					}
				} else {
					$wc_get_product               = wc_get_product( $variation_id );
					$wwp_variation_explode_eneble = get_post_meta( $wc_get_product->get_parent_id(), 'wwp_variation_explode_eneble', true );
					if ( 'yes' == $wwp_variation_explode_eneble ) {
						$price = wwp_get_price_including_tax( $wc_get_product, array( 'price' => $price ) );
					}
				}
			}
			return $price;
		}

		public function woocommerce_subscriptions_product( $subscription_string, $product, $include ) {
			if ( is_array( $product ) ) {
				$post_id = $product['product_id'];
				$product = wc_get_product( $post_id );
			}
	
			if ( $product->is_type( 'variable-subscription') ) {
				return $subscription_string;
			}
			$post_id = $product->get_id();
			$role = get_current_user_role_id();
			if ( wwp_guest_wholesale_pricing_enabled() ) {
				if ( ( is_shop() && ! wwp_guest_wholesale_pricing_enabled( 'display_on_shop' ) ) || ( is_product() && ! wwp_guest_wholesale_pricing_enabled( 'display_on_single' ) ) ) {
					return $subscription_string;
				}
				$role = wwp_guest_wholesale_pricing_enabled( 'role' );
			}

			if ( wwp_get_group_post_by_proid( $product->get_id(), get_current_user_id() ) ) {
				$this->tire_table = true;
				return tire_simple_subsc_wholesale_product_price_html( $product->get_id(), $product, 'group' );
			}

			$data = get_post_meta( $post_id, 'wholesale_multi_user_pricing', true );
			if ( isset( $data[ $role ] ) ) {
				if ( tire_simple_subsc_wholesale_product_price_html( $product->get_id(), $product, 'product' ) ) {
					$this->tire_table = true;
					return tire_simple_subsc_wholesale_product_price_html( $product->get_id(), $product, 'product' );
				}
			}

			if ( isset( $data[ $role ] ) ) {
				$my = $data[ $role ];
				if ( isset( $my['discount_type'] ) && ! empty( $my['discount_type'] ) && isset( $my['wholesale_price'] ) && ! empty( $my['wholesale_price'] ) && isset( $my['min_quatity'] ) && ! empty( $my['min_quatity'] ) ) {
					
					return simple_subsc_wholesale_product_price_html( $my['discount_type'], $my['wholesale_price'], $my['min_quatity'], $product );
				} else {
					return $subscription_string;
				}
			}
			
			if ( tire_simple_subsc_wholesale_product_price_html( $product->get_id(), $product, 'category' ) ) {
				$this->tire_table = true;
				return tire_simple_subsc_wholesale_product_price_html( $product->get_id(), $product, 'category' );
			}

			$terms = get_the_terms( $post_id, 'product_cat' );
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$data = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );
					if ( isset( $data[ $role ] ) ) {
						$my = $data[ $role ];
						if ( isset( $my['discount_type'] ) && ! empty( $my['discount_type'] ) && isset( $my['wholesale_price'] ) && ! empty( $my['wholesale_price'] ) && isset( $my['min_quatity'] ) && ! empty( $my['min_quatity'] ) ) {
							return simple_subsc_wholesale_product_price_html( $my['discount_type'], $my['wholesale_price'], $my['min_quatity'], $product );
						} else {
							return $subscription_string;
						}
					}
				}
			}

			$data = get_option( 'wholesale_multi_user_pricing' );

			if ( isset( $data[ $role ] ) ) {
				if ( tire_simple_subsc_wholesale_product_price_html( $product->get_id(), $product, 'global' ) ) {
					$this->tire_table = true;
					return tire_simple_subsc_wholesale_product_price_html( $product->get_id(), $product, 'global' );
				}
			}
			if ( isset( $data[ $role ] ) ) {
				$my = $data[ $role ];
				if ( isset( $my['discount_type'] ) && ! empty( $my['discount_type'] ) && isset( $my['wholesale_price'] ) && ! empty( $my['wholesale_price'] ) && isset( $my['min_quatity'] ) && ! empty( $my['min_quatity'] ) ) {
					return simple_subsc_wholesale_product_price_html( $my['discount_type'], $my['wholesale_price'], $my['min_quatity'], $product );
				} else {
					return $subscription_string;
				}
			}

			return $subscription_string;
		}
		
		public function woocommerce_before_mini_cart_contents() {
			/** 
			 * Filter Hook: wwp_before_mini_cart_contents
			 * 
			 * @since 2.4.7
			 * 
			 * Type: action
			 */
			if ( apply_filters( 'wwp_before_mini_cart_contents', false ) ) {
				if ( ! defined( 'WOOCOMMERCE_CART' ) ) { 
					define( 'WOOCOMMERCE_CART', true );
					WC()->cart->calculate_totals();
					WC()->cart->set_session();
					WC()->cart->maybe_set_cart_cookies();
				}   
			}
		}
		
		public function wwp_custom_class( $classes ) {
			$settings = $this->settings;
			if ( isset($settings['variation_table_enable']) && 'yes' == $settings['variation_table_enable']) {
				$wwp_variation_explode_eneble     = get_post_meta( get_the_ID(), 'wwp_variation_explode_eneble', true );
				$wwp_variation_attribute_with_qty = get_post_meta( get_the_ID(), 'wwp_variation_attribute_with_qty', true );
				if ( 'yes' == $wwp_variation_explode_eneble && !empty($wwp_variation_attribute_with_qty)) {
					$classes[] = 'wwp_variation_table_enable';
				}
			}
			return $classes;
		}
		
		public function request_sample_template_redirect( $redirect_url ) {
			$get = wwp_get_get_data();
			if (isset($get['sample']) && 'request' == $get['sample'] && !empty($get['add-to-cart-sample'])) {
				WC()->cart->add_to_cart( $get['add-to-cart-sample'] );
				wp_safe_redirect( wc_get_checkout_url() );
				exit;
			}
		}

		public function show_content_after_add_to_cart() {
			$settings          = $this->settings;
			$enable            = get_post_meta( get_the_ID(), 'request_for_sample_enable', true );
			$sample_product_id = get_post_meta( get_the_ID(), 'sample_product_id_hidden', true );
			if (  isset($settings['request_for_sample_enable']) && 'yes' == $settings['request_for_sample_enable'] && 'yes' == $enable && !empty($sample_product_id)) {
				if (empty($settings['request_for_sample_lable'])) {
					$lable = esc_html__( 'Request For Sample', 'woocommerce-wholesale-pricing' );
				} else {
					$lable = $settings['request_for_sample_lable'];
				}
				?>
					<a href="?add-to-cart-sample=<?php echo esc_attr( $sample_product_id ); ?>&sample=request" class="button"><?php esc_html_e( $lable, 'woocommerce-wholesale-pricing' ); ?></a>
				<?php
			}
		}
		
		public function add_to_cart_multiple_variation( $url = false ) { 
			$request = $_REQUEST;
			if ( !isset($request['wwp_variation_add_to_cart']) ) {
				return;
			}
			
			if ( ! class_exists( 'WC_Form_Handler' ) || empty( $_REQUEST['add-to-cart'] ) ) {
				return;
			}
			remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'add_to_cart_action' ), 20 );
			remove_filter( 'woocommerce_add_to_cart_validation', array( $this, 'add_the_date_validation' ), 200, 5 );
			$product_ids = explode( ',', $request['add-to-cart'] );
			
			$count  = count( $product_ids );
			$number = 0;
		
			foreach ( $product_ids as $id_and_quantity ) {
				$id_and_quantity = explode( ':', $id_and_quantity );
				$product_id      = $id_and_quantity[0];
		
				$_REQUEST['quantity'] = ! empty( $id_and_quantity[1] ) ? absint( $id_and_quantity[1] ) : 1;
				if ( isset($_REQUEST['quantity']) ) {
					$qty = sanitize_text_field ($_REQUEST['quantity']);
				}
				if ( ++$number === $count ) {
					$_REQUEST['variation_id'] = $product_id;
					return WC_Form_Handler::add_to_cart_action( $url );
				}
				/**
				* Hooks
				*
				* @since 3.0
				*/
				$product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $product_id ) );
				$was_added_to_cart = false;
				$adding_to_cart    = wc_get_product( $product_id );

				$variations_parent_id = wp_get_post_parent_id( $product_id );
				$pass                 = $this->add_the_date_validation( true, $variations_parent_id, $qty, $product_id ); 

				if ( ! $adding_to_cart || false == $pass ) {
					continue;
				}
				
				/**
				* Hooks
				*
				* @since 3.0
				*/
				$add_to_cart_handler = apply_filters( 'woocommerce_add_to_cart_handler', $adding_to_cart->get_type(), $adding_to_cart );
				
				if ( 'variable' === $add_to_cart_handler ) {
					$this->woo_hack_invoke_private_method( 'WC_Form_Handler', 'add_to_cart_handler_variable', $product_id );

				} elseif ( 'grouped' === $add_to_cart_handler ) {
					$this->woo_hack_invoke_private_method( 'WC_Form_Handler', 'add_to_cart_handler_grouped', $product_id );

				} elseif ( has_action( 'woocommerce_add_to_cart_handler_' . $add_to_cart_handler ) ) {
					/**
					* Hooks
					*
					* @since 3.0
					*/
					do_action( 'woocommerce_add_to_cart_handler_' . $add_to_cart_handler, $url );

				} else {
					$this->woo_hack_invoke_private_method( 'WC_Form_Handler', 'add_to_cart_handler_simple', $product_id );
				}
			}
		}
		
		public function woo_hack_invoke_private_method( $class_name, $methodName ) {
			if ( version_compare( phpversion(), '5.3', '<' ) ) {
				throw new Exception( 'PHP version does not support ReflectionClass::setAccessible()', __LINE__ );
			}
			$args = func_get_args();
			unset( $args[0], $args[1] );
			$reflection = new ReflectionClass( $class_name );
			$method     = $reflection->getMethod( $methodName );
			$method->setAccessible( true );
			$args = array_merge( array( $reflection ), $args );
			return call_user_func_array( array( $method, 'invoke' ), $args );
		}
		
		public function wwp_explode_variation_display( $html, $args ) {
			$settings = $this->settings;
			if ( isset($settings['variation_table_enable']) && 'yes' == $settings['variation_table_enable']) {
				$options               = $args['options'];
				$product               = $args['product'];
				$attribute             = $args['attribute'];  
				$name                  = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title( $attribute );
				$id                    = $args['id'] ? $args['id'] : sanitize_title( $attribute );
				$class                 = $args['class'];
				$show_option_none      = $args['show_option_none'] ? true : false;
				$show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : __( 'Choose an option', 'woocommerce' );
				$available_variations  = $product->get_available_variations();
				
				$wwp_variation_explode_eneble     = get_post_meta( $product->get_id(), 'wwp_variation_explode_eneble', true );
				$wwp_variation_attribute_with_qty = get_post_meta( $product->get_id(), 'wwp_variation_attribute_with_qty', true );
				
				
				if ( 'yes' == $wwp_variation_explode_eneble && !empty($wwp_variation_attribute_with_qty)) {
					if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
						$attributes = $product->get_variation_attributes();
						$options    = $attributes[ $attribute ];
					}
					
					if ( $args['attribute'] == $wwp_variation_attribute_with_qty) {
						foreach ($available_variations as $key => $value) {
							$attrs            = array();
							$outofstock_class = '';
							foreach (wc_get_product($value['variation_id'])->get_variation_attributes() as $keys => $attr) {
								$attribute_match = strtolower(str_replace(' ', '-', $wwp_variation_attribute_with_qty ));
								if ('attribute_' . $attribute_match != $keys) { 
									$attrs[$keys] =$attr;
								}
							} 
							$price       = get_post_meta( $value['variation_id'], '_regular_price', true );
							$product_obj = wc_get_product( $value['variation_id'] );
							$w_price     = $this->wwp_regular_price_change( $price, $product_obj );
							
							$w_price_convert    = number_format( (float) $w_price, 2, '.', '' );
							$w_price_convert    = wc_price( $w_price_convert );
							$tax_display_suffix = wwp_get_tax_price_display_suffix( $value['variation_id'] );
							
							if ( 'outofstock' == $product_obj->get_stock_status()) {
								$outofstock_class = 'wwp_disable_variation';
							}
							?>
							<div style="" class="wwp_variation_wrap <?php echo esc_attr($outofstock_class); ?>" data-w-p="<?php echo esc_attr( $w_price ); ?>"  date-variation-id="<?php echo esc_attr( $value['variation_id'] ); ?>"  data-attr-slug='<?php echo json_encode($attrs); ?>' name="<?php echo esc_attr( $name ); ?>" data-attribute_name="attribute_<?php echo esc_attr( sanitize_title( $attribute ) ); ?>">
								<div class="wwp_variation_lable">
									<?php echo esc_attr( $value['attributes'][$name] ); ?>: <span class="wwp_variation_wholesale_price"><?php echo wp_kses_post( $w_price_convert . ' ' . $tax_display_suffix  ); ?></span>  
								</div>
								<div class="wwp_quantity_box">
									<button type="button" class="minus" >-</button>
									<input type="number" class="wwp_quantitys get_variation_qty_<?php echo esc_attr( $value['variation_id'] ); ?>" name="quantitys" value="1" min="0" title="Qty" autocomplete="off">
									<button type="button" class="plus" >+</button>
								</div>
								<div class="wwp_quantity_box_availability_html">
								<?php echo wp_kses_post ($value['availability_html'] ); ?>
								</div>
							</div>
							<?php 
						}
					} else {
						return $html;
					}
				} else {
					return $html;
				}
			} else {
				return $html;
			}
		}
		
		
		public function woocommerce_cart_contents() {
			remove_filter( 'woocommerce_quantity_input_args', array( $this, 'wwp_filter_woocommerce_quantity_input_args' ), 10, 2 );
		}
		
		public function single_page_product_attachment() {
			$settings = $this->settings;
			if ( isset($settings['wwp_attachment_location']) && !empty($settings['wwp_attachment_location']) ) {
				add_action( $settings['wwp_attachment_location'], array( $this, 'wwp_attachment_location' ), 10, 1);
			}
		}
		
		public function wwp_attachment_location( $var ) {
			
			$wwp_icon_url             = get_post_meta( get_the_ID(), 'wwp_icon_url', true );
			$wwp_attachment_url       = get_post_meta( get_the_ID(), 'wwp_image_url', true );
			$wwp_attachment_title     = get_post_meta( get_the_ID(), 'wwp_attachment_title', true );
			$wwp_attachment_text_link = get_post_meta( get_the_ID(), 'wwp_attachment_text_link', true );
			
			if ( !empty($wwp_attachment_url) ) {
				?>
				<div class="wwp_attachment_container">
					<h3 class="wwp_attachment_title"> <?php echo esc_attr( $wwp_attachment_title ); ?></h3>
					<a href="<?php echo esc_attr( $wwp_attachment_url ); ?>" target="_blank">
						<?php if ( !empty( $wwp_icon_url ) ) { ?>
						<img src="<?php echo esc_attr( $wwp_icon_url ); ?>">
						<?php } ?>
						<p><?php echo esc_attr( $wwp_attachment_text_link ); ?></p>
					</a>
					
				</div>
				<?php   
			}
		} 
		
		public function woocommerce_after_bundle_price() {
			?>
			<style>
				.bundle_form .bundle_price {
					display: none!important;
				}
			</style>
			<?php
		}
		
		public function get_cart_item_from_session( $cart_item, $values ) {
			if ( ! empty( $values['addons'] ) ) {
				$variation_id =  $cart_item['variation_id'];
				if ( 0 != $variation_id ) {
					$product = wc_get_product( $cart_item['variation_id'] );
				} else {
					$product = wc_get_product( $cart_item['product_id'] );
				}
				$cart_item['addons_price_before_calc'] = $product->get_regular_price();
			}
			return $cart_item;
		}
		
		public function wwp_quantity_input_args( $args, $product ) {
			if ( class_exists( 'WC_Product_Addons' ) ) {
				if ( ! ( is_cart() || is_product() ) ) {
					return $args;
				}
				if ( is_cart() || is_checkout() ) {
					return $args;
				}
				if ( ! is_wholesaler_user( wp_get_current_user()->ID ) ) {
					return $args;
				}
				$product_id = $product->is_type('variation') ? $product->get_parent_id() : $product->get_id();
				if ( ! $this->is_wholesale( $product_id ) ) {
					return $args;
				}
				$args['input_value'] = is_wholesale_product_quantity( $product_id );
			}
			return $args;
		}

		public function wwp_override_product_price_cart_price( $price, $item ) { 
			 
			if ( class_exists( 'WC_Product_Addons_Cart' ) && ! empty( $item['addons'] ) ) {
				$quantity = $item['quantity'];
  
				foreach ( $item['addons'] as $addon ) {
					$price_type  = $addon['price_type'];
					$addon_price = $addon['price'];
					
					switch ( $price_type ) {
						case 'percentage_based':
							$price += (float) ( $price * ( $addon_price / 100 ) );
							break;
						case 'flat_fee':
							$price += (float) ( $addon_price / $quantity );
							break;
						default:
							$price += (float) $addon_price;
							break;
					}
				}
			}
			  
			return $price;
		}
		
		public function simple_load_tire_priceing_table( $p_id = '' ) {
				
			$product_id = !empty($p_id) ? $p_id : get_the_ID();
			$product    = wc_get_product( $product_id );

			// Bulk Order Compatibility
			if ( ! $product ) {
				return;
			}

			if ( $product->get_type() == 'variable' || $product->get_type() == 'variable-subscription' ) {
				return;
			}

			$role = get_current_user_role_id();
		
			$product_tier_pricing = wholesale_tire_prices( $product );
			if ( ! empty( $product_tier_pricing ) && $this->tire_table ) {
				?>
				<table id="wholesale_tire_price" style="width:100%">
					<tr>
						<th><?php esc_html_e( 'Quantity', 'woocommerce-wholesale-pricing'); ?></th>
						<th><?php esc_html_e( 'Save Discount', 'woocommerce-wholesale-pricing'); ?></th>
						<th><?php esc_html_e( 'Price per unit', 'woocommerce-wholesale-pricing'); ?></th>
					</tr>
				<?php
				foreach ( $product_tier_pricing[ $role ] as $product_data ) {
					if ( isset( $product_data['price'] ) && ! empty( $product_data['price'] ) ) {
						$max_original_variation_price = get_post_meta( $product_id, '_regular_price', true );
						if ( empty( $max_original_variation_price ) ) { 
							$max_original_variation_price = 1;
						}
						$max_wholesale_price          = tire_get_type( $product_id, $product, $product_data['price'], $max_original_variation_price, $product_tier_pricing[ $role ]['discount_type'] );
						$max_saving_percent           = ( $max_original_variation_price - $max_wholesale_price ) / $max_original_variation_price * 100;
						$max_saving_percent           = round( $max_saving_percent );
						$max_wholesale_price          = wwp_get_price_including_tax( $product, array( 'price' => $max_wholesale_price ) );
						$max_wholesale_price          = wc_price( $max_wholesale_price );
						
						if ( !empty($product_data['min']) || !empty($product_data['max']) || 0 != $max_saving_percent) {
							?>
								<tr class="wrap_<?php esc_attr_e( $product_id ); ?> row_tire" data-id="<?php esc_attr_e( $product_id ); ?>" data-min="<?php esc_attr_e( $product_data['min'] ); ?>" data-max="<?php esc_attr_e( $product_data['max'] ); ?>">
									<td> <?php echo wp_kses_post( $product_data['min'] . '-' . $product_data['max'] ); ?> </td>
									<td> <?php esc_attr_e($max_saving_percent); ?>% </td>
									<td> <?php echo wp_kses_post($max_wholesale_price); ?> </td>
								</tr>
							<?php
						}
					}
				}
				?>
				</table>
				<?php
			}
		}
		public function variation_load_tire_priceing_table( $p_id = '' ) {
			
			$curent_veration_id = !empty($p_id) ? $p_id : get_the_ID();
			$product            = new WC_Product_Variable( $curent_veration_id );
			$role               = get_current_user_role_id();
			$variables          = $product->get_available_variations();

			if ( $product->get_type() == 'simple' ) {
				return;
			}
 
			$product_tier_pricing = wholesale_tire_prices( $product );
			if ( !empty($product_tier_pricing) && $this->tire_table ) { 
				if ( ! empty( $variables ) ) {
					wp_nonce_field( 'wwp_tier_load', 'wwp_tier_load' );
					?>
					<table id="wholesale_tire_price" style="width:100%; display:none;">
						<tr></tr>
					</table>
					<?php
				}
			}
		}

		public function woocommerce_before_cart() {
			remove_filter( 'woocommerce_get_price_html', array( $this, 'wwp_change_product_price_display' ), 200, 2 );
			remove_filter( 'woocommerce_variable_sale_price_html', array( $this, 'wwp_variable_price_format' ), 200, 2 );
			remove_filter( 'woocommerce_variable_price_html', array( $this, 'wwp_variable_price_format' ), 200, 2 );
			remove_filter( 'woocommerce_cart_item_price', array( $this, 'wwp_change_product_price_display' ), 200, 2 );
		}

		public function woocommerce_after_cart_table() {
			add_filter( 'woocommerce_get_price_html', array( $this, 'wwp_change_product_price_display' ), 200, 2 );
			add_filter( 'woocommerce_variable_sale_price_html', array( $this, 'wwp_variable_price_format' ), 200, 2 );
			add_filter( 'woocommerce_variable_price_html', array( $this, 'wwp_variable_price_format' ), 200, 2 );
			add_filter( 'woocommerce_cart_item_price', array( $this, 'wwp_change_product_price_display' ), 200, 2 );
		}
		public function wwp_override_price_filter_on() {
			if ( isset( $_REQUEST['_locale'] ) && 'site' == $_REQUEST['_locale'] ) { 
				return;
			}
			add_filter( 'woocommerce_product_get_price', array( $this, 'wwp_regular_price_change' ), 200, 2 );
			add_filter( 'woocommerce_product_variation_get_price', array( $this, 'wwp_regular_price_change' ), 200, 2 );
			add_filter( 'woocommerce_product_get_regular_price', array( $this, 'wwp_regular_price_change' ), 200, 2 );
			add_filter( 'woocommerce_product_variation_get_regular_price', array( $this, 'wwp_regular_price_change' ), 200, 2 );
			add_filter( 'woocommerce_get_price_html', array( $this, 'wwp_change_product_price_display' ), 200, 2 );
			add_filter( 'woocommerce_cart_item_price', array( $this, 'wwp_change_product_price_display' ), 200, 2 );
		}

		public function remove_non_wholesale_product_crosssell( $cross_sells, $cart ) {
			if ( ! empty( $cross_sells ) ) {
				return array_diff( $cross_sells, $this->exclude_ids );
			}
			return $cross_sells;
		}

		public function filter_woocommerce_available_variation( $variation_data, $instance, $variation ) {
			
			$role       = get_current_user_role_id();
			if ( wwp_guest_wholesale_pricing_enabled() ) {
				if ( ( is_product() && ! wwp_guest_wholesale_pricing_enabled( 'display_on_single' ) ) ) {
					return $variation_data;
				}
				$role = wwp_guest_wholesale_pricing_enabled( 'role' );
			}
			$product_id = wp_get_post_parent_id( $variation_data['variation_id'] );
			$data       = get_post_meta( $product_id, 'wholesale_multi_user_pricing', true );
			if ( $this->is_wholesale( $product_id ) ) {
				 
				$terms = get_the_terms( $product_id, 'product_cat' );
					
				if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
					foreach ( $terms as $term ) {

						$data = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );
						
						if ( isset($data[$role]['min_quatity']) && !empty($data[$role]['min_quatity']) ) {
							$variation_data['input_value']  = (int) $data[$role]['min_quatity'];
							$variation_data['is_wholesale'] = true;
						}
						
						if ( isset($data[$role]['step_quantity']) && !empty($data[$role]['step_quantity']) ) {
							$variation_data['step'] = (int) $data[$role]['step_quantity'];
							if ( isset($data[$role]['min_quatity']) && !empty($data[$role]['min_quatity']) ) {
								$variation_data['min_value']    = (int) $data[$role]['min_quatity'];
								$variation_data['is_wholesale'] = true;
							}
						}
					}
					
				}
				$data = get_post_meta( $product_id, 'wholesale_multi_user_pricing', true );
				if ( isset($data[$role][$variation_data['variation_id']]['qty']) && !empty($data[$role][$variation_data['variation_id']]['qty']) ) {
					$variation_data['input_value']  = (int) $data[$role][$variation_data['variation_id']]['qty'];
					$variation_data['is_wholesale'] = true;
				}
				if ( isset($data[$role][$variation_data['variation_id']]['step']) && !empty($data[$role][$variation_data['variation_id']]['step']) ) {
					$variation_data['step'] =  (int) $data[$role][$variation_data['variation_id']]['step'];
					if ( isset($data[$role][$variation_data['variation_id']]['qty']) && !empty($data[$role]['qty']) ) {
						$variation_data['min_value']    =  (int) $data[$role][$variation_data['variation_id']]['qty'];
						$variation_data['is_wholesale'] = true;
					}
				}
			}

			$settings = $this->settings;
			if ( 'yes' != $settings['restrict_store_access'] ) {
				if ( ! is_wholesaler_user( get_current_user_id() ) && ( ! wwp_guest_wholesale_pricing_enabled() ) ) {
					return $variation_data;
				}
			}
			/*
			Wholesale Version 1.6.0
			Minimum  Quantity Set by Product
			*/
			if ( isset( $data[ $role ][ $variation_data['variation_id'] ]['qty'] ) ) {

				$min_quantity = (int) $data[ $role ][ $variation_data['variation_id'] ]['qty'];

				if ( $min_quantity && 1 != $min_quantity ) {
					/* translators: %s: wholesale price on minmum */
					$textqty = sprintf( esc_html__( 'Wholesale price will only be applied to a minimum quantity of %1$s products', 'woocommerce-wholesale-pricing' ), $min_quantity );
					
					/**
					* Hooks
					*
					* @since 3.0
					*/
					$variation_data['availability_html'] .= apply_filters( 'wwp_product_minimum_quantity_text', '<p style="font-size: 10px;">' . $textqty . '</p>', $min_quantity );
					return $variation_data;
				} else {
					return $variation_data;
				}
			}

			/*
			Wholesale Version 1.6.0
			Minimum  Quantity Set by Category
			*/

			$terms = get_the_terms( $product_id, 'product_cat' );
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$data = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );
					if ( isset( $data[ $role ] ) ) {

						$min_quantity = (int) $data[ $role ]['min_quatity'];
						if ( $min_quantity && 1 != $min_quantity ) {
							/* translators: %s: wholesale price on minmum */
							$textqty = sprintf( esc_html__( 'Wholesale price will only be applied to a minimum quantity of %1$s products', 'woocommerce-wholesale-pricing' ), $data[ $role ]['min_quatity'] );
							
							/**
							* Hooks
							*
							* @since 3.0
							*/
							$variation_data['availability_html'] .= apply_filters( 'wwp_product_minimum_quantity_text', '<p style="font-size: 10px;">' . $textqty . '</p>', $data[ $role ]['min_quatity'] );
							return $variation_data;

						} else {
							return $variation_data;
						}
					}
				}
			}

			/*
			Wholesale Version 1.6.0
			Minimum  Quantity Set by Globaly
			*/

			$data = get_option( 'wholesale_multi_user_pricing' );
			if ( isset( $data[ $role ] ) ) {

				$min_quantity = (int) $data[ $role ]['min_quatity'];
				if ( $min_quantity && 1 != $min_quantity ) {
					/* translators: %s: wholesale price on minmum */
					$textqty = sprintf( esc_html__( 'Wholesale price will only be applied to a minimum quantity of %1$s products', 'woocommerce-wholesale-pricing' ), $data[ $role ]['min_quatity'] );
					
					/**
					* Hooks
					*
					* @since 3.0
					*/
					$variation_data['availability_html'] .= apply_filters( 'wwp_product_minimum_quantity_text', '<p style="font-size: 10px;">' . $textqty . '</p>', $data[ $role ]['min_quatity'] );
					return $variation_data;

				} else {
					return $variation_data;
				}
			}

			return $variation_data;
		}

		public function wwp_filter_woocommerce_quantity_input_args( $wp_parse_args, $product ) {
			if ( $this->is_wholesale( $product->get_id() ) ) {
				$role     = get_current_user_role_id();
				$settings = $this->settings;
				if ( ( 'object' == gettype( $product ) ) ) {
					if ( isset( $settings['wholesaler_allow_minimum_qty'] ) && 'yes' == $settings['wholesaler_allow_minimum_qty'] ) {
						if ( 'simple' == $product->get_type() || 'bundle' == $product->get_type() ) {
							$terms = get_the_terms( $product->get_id(), 'product_cat' );
							if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
								foreach ( $terms as $term ) {
		
									$data = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );
									if ( isset($data[$role]['min_quatity']) && !empty($data[$role]['min_quatity']) ) {
										if ( !defined('WOOCOMMERCE_CART')  ) {
											$wp_parse_args['input_value'] = $data[$role]['min_quatity'];
										}
									}
									
									if ( isset($data[$role]['step_quantity']) && !empty($data[$role]['step_quantity']) ) {
										$wp_parse_args['step'] = $data[$role]['step_quantity'];
										if ( isset($data[$role]['min_quatity']) && !empty($data[$role]['min_quatity']) ) {
											$wp_parse_args['min_value'] = $data[$role]['min_quatity'];
											$wp_parse_args['quantity']  = $data[$role]['step_quantity'];
										}
									}
								}
							}
							
							$data = get_post_meta( $product->get_id(), 'wholesale_multi_user_pricing', true );
							
							if ( isset($data[$role]['min_quatity']) && !empty($data[$role]['min_quatity']) ) { 
								if ( !defined('WOOCOMMERCE_CART')  ) {
									$wp_parse_args['input_value'] = $data[$role]['min_quatity'];
								} 
							}
							if ( isset($data[$role]['step_quantity']) && !empty($data[$role]['step_quantity']) ) {
								$wp_parse_args['step'] = $data[$role]['step_quantity'];
								if ( isset($data[$role]['min_quatity']) && !empty($data[$role]['min_quatity']) ) {
									$wp_parse_args['min_value'] = $data[$role]['min_quatity'];
									$wp_parse_args['quantity']  = $data[$role]['step_quantity'];
								}
							} 
						} elseif ( 'variation' == $product->get_type() ) { 
						
							$product_id = wp_get_post_parent_id( $product->get_id() );
							$terms      = get_the_terms( $product_id, 'product_cat' );
							if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
								foreach ( $terms as $term ) {
		
									$data = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );
									if ( isset($data[$role]['min_quatity']) && !empty($data[$role]['min_quatity']) ) {
										if ( !defined('WOOCOMMERCE_CART')  ) {
											$wp_parse_args['input_value'] = $data[$role]['min_quatity'];
										}
									}
									
									if ( isset($data[$role]['step_quantity']) && !empty($data[$role]['step_quantity']) ) {
										$wp_parse_args['step'] = $data[$role]['step_quantity'];
										if ( isset($data[$role]['min_quatity']) && !empty($data[$role]['min_quatity']) ) {
											$wp_parse_args['min_value'] = $data[$role]['min_quatity'];
											$wp_parse_args['quantity']  = $data[$role]['step_quantity'];
										}
									}
								}
							}
							
							$data = get_post_meta( $product_id, 'wholesale_multi_user_pricing', true );
							if ( isset($data[$role][$product->get_id()]['qty']) && !empty($data[$role][$product->get_id()]['qty']) ) {
								if ( !defined('WOOCOMMERCE_CART')  ) {
									$wp_parse_args['input_value'] = $data[$role][$product->get_id()]['qty'];
								} 
							}
							if ( isset($data[$role][$product->get_id()]['step']) && !empty($data[$role][$product->get_id()]['step']) ) {
								$wp_parse_args['step'] = $data[$role][$product->get_id()]['step'];
								if ( isset($data[$role][$product->get_id()]['qty']) && !empty($data[$role][$product->get_id()]['qty']) ) {
									$wp_parse_args['min_value'] = $data[$role][$product->get_id()]['qty'];
									if ( isset( $data[$role]['step_quantity'] ) ) {
										$wp_parse_args['quantity'] = $data[$role]['step_quantity'];
									}
								}
							}
						
						}
					} elseif ( 'simple' == $product->get_type() || 'bundle' == $product->get_type()) {
							$terms = get_the_terms( $product->get_id(), 'product_cat' );
						if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
							foreach ( $terms as $term ) {
								$data = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );
									
								if ( isset($data[$role]['step_quantity']) && !empty($data[$role]['step_quantity']) ) {
									$wp_parse_args['step']      = $data[$role]['step_quantity'];
									$wp_parse_args['min_value'] = $data[$role]['step_quantity'];
									$wp_parse_args['quantity']  = $data[$role]['step_quantity'];
								}
							}
						}
							
							$data = get_post_meta( $product->get_id(), 'wholesale_multi_user_pricing', true );
							
						if ( isset($data[$role]['step_quantity']) && !empty($data[$role]['step_quantity']) ) {
							$wp_parse_args['step'] = $data[$role]['step_quantity'];
							$wp_parse_args['min_value'] = $data[$role]['step_quantity'];
							$wp_parse_args['quantity']  = $data[$role]['step_quantity'];
						} 
					} elseif ( 'variation' == $product->get_type() ) { 
						
						$product_id = wp_get_post_parent_id( $product->get_id() );
						$terms      = get_the_terms( $product_id, 'product_cat' );
						if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
							foreach ( $terms as $term ) {
		
								$data = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );
								if ( isset($data[$role]['step_quantity']) && !empty($data[$role]['step_quantity']) ) {
									$wp_parse_args['step'] = $data[$role]['step_quantity'];
									$wp_parse_args['min_value'] = $data[$role]['step_quantity'];
									$wp_parse_args['quantity']  = $data[$role]['step_quantity'];
								}
							}
						}
							
						$data = get_post_meta( $product_id, 'wholesale_multi_user_pricing', true );
						if ( isset($data[$role][$product->get_id()]['step']) && !empty($data[$role][$product->get_id()]['step']) ) {
							$wp_parse_args['step'] = $data[$role][$product->get_id()]['step'];
							if ( isset($data[$role][$product->get_id()]['qty']) && !empty($data[$role][$product->get_id()]['qty']) ) {
								$wp_parse_args['min_value'] = $data[$role][$product->get_id()]['qty'];
								if ( isset( $data[$role]['step_quantity'] ) ) {
									$wp_parse_args['quantity'] = $data[$role]['step_quantity'];
								}
							}
						}
					}
				}
			} 
			return $wp_parse_args; 
		}

		public function wwp_filter_woocommerce_quantity_step_block_cart( $value, $product ) {
			if ( $this->is_wholesale( $product->get_id() ) ) {
				$role     = get_current_user_role_id();
				$settings = $this->settings;
				if ( ( 'object' == gettype( $product ) ) ) {
					if ( 'simple' == $product->get_type() ) {
						$terms = get_the_terms( $product->get_id(), 'product_cat' );
						if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
							foreach ( $terms as $term ) {
								$data = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );
								if ( isset($data[$role]['step_quantity']) && !empty($data[$role]['step_quantity']) ) {
									$wp_parse_args['step'] = $data[$role]['step_quantity'];
									if ( isset($data[$role]['min_quatity']) && !empty($data[$role]['min_quatity']) ) {
										return $data[$role]['step_quantity'];
									}
								}
							}
						}
						$data = get_post_meta( $product->get_id(), 'wholesale_multi_user_pricing', true );
						if ( isset($data[$role]['step_quantity']) && !empty($data[$role]['step_quantity']) ) {
							$wp_parse_args['step'] = $data[$role]['step_quantity'];
							if ( isset($data[$role]['min_quatity']) && !empty($data[$role]['min_quatity']) ) {
								$wp_parse_args['min_value'] = $data[$role]['min_quatity'];
								return $data[$role]['step_quantity'];
							}
						} 
					} elseif ( 'variation' == $product->get_type() ) { 
					
						$product_id = wp_get_post_parent_id( $product->get_id() );
						$terms      = get_the_terms( $product_id, 'product_cat' );
						if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
							foreach ( $terms as $term ) {
								$data = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );
								if ( isset($data[$role]['step_quantity']) && !empty($data[$role]['step_quantity']) ) {
									$wp_parse_args['step'] = $data[$role]['step_quantity'];
									if ( isset($data[$role]['min_quatity']) && !empty($data[$role]['min_quatity']) ) {
										$wp_parse_args['min_value'] = $data[$role]['min_quatity'];
										return $data[$role]['step_quantity'];
									}
								}
							}
						}
						
						$data = get_post_meta( $product_id, 'wholesale_multi_user_pricing', true );
						if ( isset($data[$role][$product->get_id()]['step']) && !empty($data[$role][$product->get_id()]['step']) ) {
							$wp_parse_args['step'] = $data[$role][$product->get_id()]['step'];
							if ( isset($data[$role][$product->get_id()]['qty']) && !empty($data[$role][$product->get_id()]['qty']) ) {
								$wp_parse_args['min_value'] = $data[$role][$product->get_id()]['qty'];
								if ( isset( $data[$role]['step_quantity'] ) ) {
									return $data[$role]['step_quantity'];
								}
							}
						}
					
					}
				}
			} 
			return $value;
		}

		public function add_the_date_validation( $passed, $product_id, $quantity, $variation_id = '', $variations = '' ) {
			
			if ( $this->is_wholesale( $product_id ) ) {
				
				$settings = $this->settings;
				if ( 'yes' == $settings['wholesaler_allow_minimum_qty'] ) {
					$role         = get_current_user_role_id();
					$min_quantity = 1;
					
					if ( ! empty( $variation_id ) ) {
						$data = get_post_meta( $product_id, 'wholesale_multi_user_pricing', true );

						if ( isset( $data[ $role ][ $variation_id ]['qty'] ) ) {
							$min_quantity = $data[ $role ][ $variation_id ]['qty'];

							if ( empty( $min_quantity ) || ! isset( $min_quantity ) ) {
								$min_quantity = 1;
							}
							if ( $min_quantity > $quantity ) {

								/* translators: %s: wholesale price on minmum */
								$textqty = sprintf( esc_html__( 'Wholesale price will only be applied to a minimum quantity of %1$s products', 'woocommerce-wholesale-pricing' ), $min_quantity );
								
								/**
								* Hooks
								*
								* @since 3.0
								*/
								wc_add_notice( __( apply_filters( 'wwp_product_minimum_quantity_text', $textqty, $min_quantity ), 'woocommerce' ), 'error' );
								return false;

							} else {
								return true;
							}
						}

						$terms = get_the_terms( $product_id, 'product_cat' );

						if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
							foreach ( $terms as $term ) {

								$data = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );

								if ( isset( $data[ $role ]['min_quatity'] ) ) {
									$min_quantity = (int) $data[ $role ]['min_quatity'];

									if ( empty( $min_quantity ) || ! isset( $min_quantity ) ) {
										$min_quantity = 1;
									}

									if ( $min_quantity > $quantity ) {

										/* translators: %s: wholesale price on minmum */
										$textqty = sprintf( esc_html__( 'Wholesale price will only be applied to a minimum quantity of %1$s products', 'woocommerce-wholesale-pricing' ), $min_quantity );
										
										/**
										* Hooks
										*
										* @since 3.0
										*/                      
										wc_add_notice( __( apply_filters( 'wwp_product_minimum_quantity_text', $textqty, $min_quantity ), 'woocommerce' ), 'error' );
										return false;
									} else {
										return true;
									}
								}
							}
						}
						$data = get_option( 'wholesale_multi_user_pricing' );
						if ( isset( $data[ $role ]['min_quatity'] ) ) {
							$min_quantity = $data[ $role ]['min_quatity'];

							if ( empty( $min_quantity ) || ! isset( $min_quantity ) ) {
								$min_quantity = 1;
							}
							if ( $min_quantity > $quantity ) {
								/* translators: %s: wholesale price on minmum */
								$textqty = sprintf( esc_html__( 'Wholesale price will only be applied to a minimum quantity of %1$s products', 'woocommerce-wholesale-pricing' ), $min_quantity );
								
								/**
								* Hooks
								*
								* @since 3.0
								*/
								wc_add_notice( __( apply_filters( 'wwp_product_minimum_quantity_text', $textqty, $min_quantity ), 'woocommerce' ), 'error' );
								return false;
							} else {
								return true;
							}
						}
					} else {

						$data = get_post_meta( $product_id, 'wholesale_multi_user_pricing', true );
						if ( isset( $data[ $role ]['min_quatity'] ) ) {
							$min_quantity = $data[ $role ]['min_quatity'];

							if ( empty( $min_quantity ) || ! isset( $min_quantity ) ) {
								$min_quantity = 1;
							}
							if ( $min_quantity > $quantity ) {

								/* translators: %s: wholesale price on minmum */
								$textqty = sprintf( esc_html__( 'Wholesale price will only be applied to a minimum quantity of %1$s products', 'woocommerce-wholesale-pricing' ), $min_quantity );
								
								/**
								* Hooks
								*
								* @since 3.0
								*/                              
								wc_add_notice( __( apply_filters( 'wwp_product_minimum_quantity_text', $textqty, $min_quantity ), 'woocommerce' ), 'error' );
								return false;
							} else {
								return true;
							}
						}

						$terms = get_the_terms( $product_id, 'product_cat' );
						if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
							foreach ( $terms as $term ) {
								$data = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );

								if ( isset( $data[ $role ]['min_quatity'] ) ) {
									$min_quantity = $data[ $role ]['min_quatity'];

									if ( empty( $min_quantity ) || ! isset( $min_quantity ) ) {
										$min_quantity = 1;
									}
									if ( $min_quantity > $quantity ) {

										/* translators: %s: wholesale price on minmum */
										$textqty = sprintf( esc_html__( 'Wholesale price will only be applied to a minimum quantity of %1$s products', 'woocommerce-wholesale-pricing' ), $min_quantity );
										
										/**
										* Hooks
										*
										* @since 3.0
										*/
										wc_add_notice( __( apply_filters( 'wwp_product_minimum_quantity_text', $textqty, $min_quantity ), 'woocommerce' ), 'error' );
										return false;

									} else {
										return true;
									}
								}
							}
						}

						$data = get_option( 'wholesale_multi_user_pricing' );
						if ( isset( $data[ $role ]['min_quatity'] ) ) {
							$min_quantity = $data[ $role ]['min_quatity'];

							if ( empty( $min_quantity ) || ! isset( $min_quantity ) ) {
								$min_quantity = 1;
							}
							if ( $min_quantity > $quantity ) {

								/* translators: %s: wholesale price on minmum */
								$textqty = sprintf( esc_html__( 'Wholesale price will only be applied to a minimum quantity of %1$s products', 'woocommerce-wholesale-pricing' ), $min_quantity );
								/**
								* Hooks
								*
								* @since 3.0
								*/
								wc_add_notice( __( apply_filters( 'wwp_product_minimum_quantity_text', $textqty, $min_quantity ), 'woocommerce' ), 'error' );
								return false;

							} else {
								return true;
							}
						}
					}/// else end
				}
			}
			return true;
		}

		public function wwp_available_payment_gateways( $available_gateways ) {
			$settings   = $this->settings;
			$term       = '';
			if ( isset( $settings['restrict_store_access'] ) && 'yes' == $settings['restrict_store_access'] ) {
				if ( isset( $_COOKIE['access_store_id'] ) && ! empty( $_COOKIE['access_store_id'] ) ) {
					$term = get_term_by( 'id', sanitize_text_field( $_COOKIE['access_store_id'] ), 'wholesale_user_roles' );
				}
			} else {
				$user_info  = get_userdata( get_current_user_id() );
				$ws_group = wwp_get_group_post_by_proid( 0, get_current_user_id()  );
				$restricted_gateway = array();
				if ( ! empty( $ws_group ) ) { 
					foreach ( $ws_group as $group_id ) {
						$restricted_gateway = get_post_meta( $group_id, '_disable_payment_methods', true );
					}
					
				}
				
				$user_roles = (array) ( isset( $user_info->roles ) ? $user_info->roles : array() );
				if ( empty( $user_roles ) ) {
					return $available_gateways;
				}
				$term = isset( $user_roles[0] ) ? get_term_by( 'slug', $user_roles[0], 'wholesale_user_roles' ) : '';
			}

			if ( ! empty( $restricted_gateway ) ) {
				$restrict_gateway = array_unique( $restricted_gateway );
				foreach ( $restrict_gateway as $value ) {
					unset( $available_gateways[ $value ] );
				}
			}

			if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
				$restricted = get_term_meta( $term->term_id, 'wwp_restricted_pmethods_wholesaler', true );

				if ( ! empty( $restricted ) && ! empty( $available_gateways ) ) {
					foreach ( $restricted as $restrict ) {
						unset( $available_gateways[ $restrict ] );
					}
				}
			}
			return $available_gateways;
		}
		public function wwp_restrict_shipping_wholesaler( $rates, $package ) {
			if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
				return $rates;
			}
			$settings   = $this->settings;
			$term       = '';
			if ( isset( $settings['restrict_store_access'] ) && 'yes' == $settings['restrict_store_access'] ) {
				if ( isset( $_COOKIE['access_store_id'] ) && ! empty( $_COOKIE['access_store_id'] ) ) {
					$term = get_term_by( 'id', sanitize_text_field( $_COOKIE['access_store_id'] ), 'wholesale_user_roles' );
				}
			} else {
				$user_info  = get_userdata( get_current_user_id() );

				$ws_group = wwp_get_group_post_by_proid( 0, get_current_user_id()  );
				$restricted_shipping = array();
				if ( ! empty( $ws_group ) ) { 
					foreach ( $ws_group as $group_id ) {
						$restricted_shipping = get_post_meta( $group_id, '_disable_shipping_methods', true );
					}
					
				}


				$user_roles = (array) ( isset( $user_info->roles ) ? $user_info->roles : array() );
				if ( empty( $user_roles ) ) {
					return $rates;
				}
				$term = isset( $user_roles[0] ) ? get_term_by( 'slug', $user_roles[0], 'wholesale_user_roles' ) : '';
			}
			if ( ! empty( $restricted_shipping ) ) {
				$restrict_ship = array_unique( $restricted_shipping );
				foreach ( $rates as $rate_key => $rate ) {
					if ( in_array( $rate->method_id, $restrict_ship ) ) {
						unset( $rates[ $rate_key ] ); // Remove shipping method
					}
				}
			}

			if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
				$restricted = get_term_meta( $term->term_id, 'wwp_restricted_smethods_wholesaler', true );
				if ( ! empty( $restricted ) && ! empty( $rates ) ) {
					// $rates = array_diff_key($rates, $restricted);
					foreach ( $rates as $rate_key => $rate ) {
						if ( in_array( $rate->method_id, $restricted ) ) {
							unset( $rates[ $rate_key ] ); // Remove shipping method
						}
					}
				}
			}
			return $rates;
		}

		public function wwp_regular_price_change( $price, $product ) {

			// compatible with quote for woocommerce
			if ( isset( $_REQUEST['wc_quote_convert_to_order_customer'] ) ) {
				return $price;
			}

			if ( is_shop() && 'product_variation' == get_post_type(  $product->get_id() ) ) {
				return $price;
			}

			if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
				return $price; 
			}

			if ( is_cart() && ! $this->wholesale_pricing_cart_based_on_subtotal() ) {
				return $price;
			}
			$wholesale_qty = '';

			/**
			* Filter Hook
			*
			* @since 2.5
			*/
			$transient = apply_filters( 'product_transient_time_load', true );
			if ( $transient ) {
				wc_delete_product_transients( $product->get_id() );
			}

			$product_id        = $product->get_id();
			$qty               = (int) get_cart_qty( $product_id );
			$variation_id      = $product_id;
			$this->product_obj = $product;
			$role              = get_current_user_role_id();
			if ( wwp_guest_wholesale_pricing_enabled() ) {
				if ( ( is_shop() && ! wwp_guest_wholesale_pricing_enabled( 'display_on_shop' ) ) || ( is_product() && ! wwp_guest_wholesale_pricing_enabled( 'display_on_single' ) ) ) {
					return $price;
				}
				$role = wwp_guest_wholesale_pricing_enabled( 'role' );
			}

			$ws_group = wwp_get_group_post_by_proid( $product_id, get_current_user_id() );

			if ( $ws_group ) { 
				$group_id = isset( $ws_group[0] ) ? $ws_group[0] : '';
				if ( ! empty( $group_id ) ) {
					if ( tire_variable_wholesale_product_price( $variation_id, $product, 'group' ) ) {
						return tire_variable_wholesale_product_price( $variation_id, $product, 'group' );
					}
				}
			}

			$data              = get_post_meta( $variation_id, 'wholesale_multi_user_pricing', true );
			if ( isset( $data[ $role ] ) ) { 
				if ( tire_variable_wholesale_product_price( $variation_id, $product, 'product' ) ) {
					return tire_variable_wholesale_product_price( $variation_id, $product, 'product' );
				}
			}
			if ( isset( $data[ $role ] ) && 'product' == get_post_type( $product_id ) && isset( $data[ $role ]['discount_type'] ) && isset( $data[ $role ]['wholesale_price'] ) ) {

				$my = $data[ $role ];
				if ( isset( $my['min_quatity'] ) && ! empty( $my['min_quatity'] ) ) {
					$wholesale_qty = $my['min_quatity'];
				}
				/**
				* Hooks
				*
				* @since 3.0
				*/
				if ( apply_filters( 'wwp_mnm_get_price_qty', $this->quantity( $qty, $wholesale_qty ), $product ) ) {
					/**
					* Hooks
					*
					* @since 3.0
					*/
					return apply_filters( 'wwp_product_addon_get_price_qty', $price, $product );
				} 
				
				$price = get_post_meta( $product_id, '_regular_price', true );
				
				/**
				* Hooks
				*
				* @since 3.0
				*/
				$price = apply_filters( 'wwp_mnm_get_price', $price, $product );
				return $this->change_price( $price, $my['discount_type'], $my['wholesale_price'], $product_id);
			
			} elseif ( 'product_variation' == get_post_type( $product_id ) ) { 
				
				$variation_id = $product_id;
				$product_id   = wp_get_post_parent_id( $product_id );
 
				$data = get_post_meta( $product_id, 'wholesale_multi_user_pricing', true );
				if ( isset( $data[ $role ] ) ) {  
					if ( isset( $data[ $role ][ $variation_id ]['wholesaleprice'] ) && !empty( $data[ $role ][ $variation_id ]['wholesaleprice'] ) ) { 
						$my = $data[ $role ][ $variation_id ]; 
						if ( isset( $my['qty'] ) && ! empty( $my['qty'] ) ) {
							$wholesale_qty = $my['qty'];
						}
						
						if ( $this->quantity( $qty, $wholesale_qty ) ) {
						
							/**
							* Hooks
							*
							* @since 3.0
							*/
							return apply_filters( 'wwp_product_addon_get_price_qty', $price, $product ) ;
						}
						
						$price = $this->get_variable_wholesale_price( $data[ $role ][ $variation_id ], $variation_id );
						
						return $price;
					} else {
						return $price;
					} 
				} else {
					$wc_get_product               = wc_get_product( $variation_id );
					$wwp_variation_explode_eneble = get_post_meta( $wc_get_product->get_parent_id(), 'wwp_variation_explode_eneble', true );
					if ( 'yes' == $wwp_variation_explode_eneble ) {
						$price = wwp_get_price_including_tax( $wc_get_product, array( 'price' => $price ) );
					}
				}
			}

			if ( 'product_variation' == get_post_type( $product_id ) ) {  
				$variation_id = $product_id;
				$product_id   = wp_get_post_parent_id( $product_id );
			}
			
			if ( tire_variable_wholesale_product_price( $product_id, $product, 'category' ) ) {
				return tire_variable_wholesale_product_price( $product_id, $product, 'category' );
			}
			
			$terms = get_the_terms( $product_id, 'product_cat' );
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$data = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );
					if ( isset( $data[ $role ] ) && isset( $data[ $role ]['discount_type'] ) && isset( $data[ $role ]['wholesale_price'] ) ) {
						$my = $data[ $role ];
						if ( isset( $my['min_quatity'] ) && ! empty( $my['min_quatity'] ) ) {
							$wholesale_qty = $my['min_quatity'];
						}

						if ( $this->quantity( $qty, $wholesale_qty ) ) {
						
							/**
							* Hooks
							*
							* @since 3.0
							*/
							return apply_filters( 'wwp_product_addon_get_price_qty', $price, $product ) ;
						}
						if ( empty( get_post_meta( $variation_id, '_regular_price', true ) ) ) {
							return $price;
						}
						$price = get_post_meta( $variation_id, '_regular_price', true );
						
						return $this->change_price( $price, $my['discount_type'], $my['wholesale_price'], $variation_id );
					}
				}
			}

			$data = get_option( 'wholesale_multi_user_pricing' );
			
			if ( isset( $data[ $role ] ) ) {  
				if ( tire_variable_wholesale_product_price( $product_id, $product, 'global' ) ) {
					return tire_variable_wholesale_product_price( $product_id, $product, 'global' );
				}
			}
			
			if ( isset( $data[ $role ] ) && isset( $data[ $role ]['discount_type'] ) && isset( $data[ $role ]['wholesale_price'] ) ) {
				$my = $data[ $role ];
				$product_id = $product->get_id();
				$category_ids = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );

				$all_category_ids = array();

				foreach ( $category_ids as $cat_id ) {
					$all_category_ids[] = $cat_id;

					$parent_id = get_term( $cat_id, 'product_cat' )->parent;

					while ( 0 != $parent_id && ! in_array( $parent_id, $all_category_ids ) ) {
						$all_category_ids[] = $parent_id;
						$parent_id = get_term( $parent_id, 'product_cat' )->parent;
					}
				}
				$flag = false;
				$excluded_categories = $this->wwp_exclude_categories_for_wholesale_roles();

				if ( ! empty( $all_category_ids ) && ! empty( $excluded_categories ) ) {
					foreach ( $all_category_ids as $cat_id ) {
						if ( in_array( $cat_id, $excluded_categories ) ) {
							$flag = true;
							break;
						}
					}
				}

				if ( $flag ) {
					return $price;
				}
				if ( isset( $my['min_quatity'] ) && ! empty( $my['min_quatity'] ) ) {
					$wholesale_qty = (int) $my['min_quatity'];
				}
				if ( $this->quantity( $qty, $wholesale_qty ) ) {
				
					/**
					* Hooks
					*
					* @since 3.0
					*/
					return apply_filters( 'wwp_product_addon_get_price_qty', $price, $product ) ;
				}
				if ( empty( get_post_meta( $variation_id, '_regular_price', true ) ) ) {
					return $price;
				}
				// $price = get_post_meta( $variation_id, '_sale_price', true );

				if ( empty( $price ) ) {
					$price = get_post_meta( $variation_id, '_regular_price', true );
				}
				return $this->change_price( $price, $data[ $role ]['discount_type'], $data[ $role ]['wholesale_price'], $variation_id);
			}
			return $price;
		}

		public function quantity( $qty, $wholesale_qty ) {
			if ( ( isset( $_SERVER['REQUEST_URI'] ) && '/wp-json/wc/store/v1/batch?_locale=user' == sanitize_text_field( $_SERVER['REQUEST_URI'] ) ) ) {
				return true;
			}
			/**
			* Hooks
			*
			* @since 3.0
			*/
			if ( apply_filters( 'wwp_quantity_min_check', false, $qty, $wholesale_qty, $this->product_obj ) ) {
				return true;
			}

			if ( ( is_cart() || is_checkout() ) ) {
				return true;
			}
			if ( is_admin() && '' != $qty && $qty < $wholesale_qty ) {
				return true;
			}

			if ( '' != $qty && $qty < $wholesale_qty ) {
				return false;
			}
		}

		public function wwp_variation_price( $price, $variation ) {
			$variation_id = $variation->get_id();
			$product_id   = wp_get_post_parent_id( $product_id );
			$role         = get_current_user_role_id();
			if ( isset( $data[ $role ][ $variation_id ] ) ) {
				$price = $this->get_variable_wholesale_price( $data[ $role ][ $variation_id ], $variation_id );
			}
			return $price;
		}

		public function change_price( $price, $type, $amount, $variation_id = 0 ) {
			if ( empty( $price ) || empty( $amount ) ) {
				return $price;
			}
			if ( 'fixed' == $type ) {
				$price = $amount;
			} else {
				$price = $price * $amount / 100;
			}

			$wc_get_product = wc_get_product( $variation_id );
			if ( ( 'object' == gettype( $wc_get_product ) )) {
				if ( 'product_variation' == get_post_type( $wc_get_product->get_id() ) ) {
					$wwp_variation_explode_eneble = get_post_meta( $wc_get_product->get_parent_id(), 'wwp_variation_explode_eneble', true );
				} else {
					$wwp_variation_explode_eneble = get_post_meta( $wc_get_product->get_id(), 'wwp_variation_explode_eneble', true );
				}
				if ( 'yes' == $wwp_variation_explode_eneble ) {
					$price = wwp_get_price_including_tax( $wc_get_product, array( 'price' => $price ) );
				}
			}
			/**
			 * Filter
			 * 
			 *  @since 2.5
			 */
			return apply_filters( 'wwp_change_price', $price, $wc_get_product );
		}
		public function wwp_change_product_price_display( $price, $product ) {
			if ( is_array( $product ) ) {
				$post_id = $product['product_id'];
				$product = wc_get_product( $post_id );
			}
			if ( is_cart() ) {
				return $price;
			}
			if ( ( 'object' != gettype( $product ) )) {
				return $price;
			}
			$post_id = $product->get_id();
			if ( ( 'object' == gettype( $product ) ) && ( $product->is_type( 'subscription') || $product->is_type( 'variable-subscription') || ! $product->is_type( 'simple' ) ) ) {
				if ( 'product_variation' == get_post_type( $product->get_id() ) ) {
					if ( ! empty( get_option( 'woocommerce_price_display_suffix' ) ) ) {
						$price = $price;
					} else {
						$price = wc_price( wwp_get_price_including_tax( $product, array( 'price' => $product->get_regular_price() ) ) );
					}
					return $price;
				} elseif ( 'bundle' != $product->get_type() ) {
					return $price;
				}
			}

			$role = get_current_user_role_id();
			if ( wwp_guest_wholesale_pricing_enabled() ) {
				if ( ( is_shop() && ! wwp_guest_wholesale_pricing_enabled( 'display_on_shop' ) ) || ( is_product() && ! wwp_guest_wholesale_pricing_enabled( 'display_on_single' ) ) ) {
					return $price;
				}
				$role = wwp_guest_wholesale_pricing_enabled( 'role' );
			}

			if ( wwp_get_group_post_by_proid( $product->get_id(), get_current_user_id() ) ) {
				$this->tire_table = true;
				return tire_simple_wholesale_product_price_html( $product->get_id(), $product, 'group' );
			}

			$data = get_post_meta( $post_id, 'wholesale_multi_user_pricing', true );
			if ( isset( $data[ $role ] ) ) {
				if ( tire_simple_wholesale_product_price_html( $product->get_id(), $product, 'product' ) ) {
					$this->tire_table = true;
					return tire_simple_wholesale_product_price_html( $product->get_id(), $product, 'product' );
				}
			}

			if ( isset( $data[ $role ] ) ) {
				$my = $data[ $role ];
				if ( isset( $my['discount_type'] ) && ! empty( $my['discount_type'] ) && isset( $my['wholesale_price'] ) && ! empty( $my['wholesale_price'] ) && isset( $my['min_quatity'] ) && ! empty( $my['min_quatity'] ) ) {

					return simple_wholesale_product_price_html( $my['discount_type'], $my['wholesale_price'], $price, $my['min_quatity'], $product );
				} else {
					return $price;
				}
			}
			
			if ( tire_simple_wholesale_product_price_html( $product->get_id(), $product, 'category' ) ) {
				$this->tire_table = true;
				return tire_simple_wholesale_product_price_html( $product->get_id(), $product, 'category' );
			}

			$terms = get_the_terms( $post_id, 'product_cat' );
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$data = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );
					if ( isset( $data[ $role ] ) ) {
						$my = $data[ $role ];
						if ( isset( $my['discount_type'] ) && ! empty( $my['discount_type'] ) && isset( $my['wholesale_price'] ) && ! empty( $my['wholesale_price'] ) && isset( $my['min_quatity'] ) && ! empty( $my['min_quatity'] ) ) {
							return simple_wholesale_product_price_html( $my['discount_type'], $my['wholesale_price'], $price, $my['min_quatity'], $product );
						} else {
							return $price;
						}
					}
				}
			}

			$data = get_option( 'wholesale_multi_user_pricing' );

			if ( isset( $data[ $role ] ) ) {
				if ( tire_simple_wholesale_product_price_html( $product->get_id(), $product, 'global' ) ) {
					$this->tire_table = true;
					return tire_simple_wholesale_product_price_html( $product->get_id(), $product, 'global' );
				}
			}
			if ( isset( $data[ $role ] ) ) {
				$my = $data[ $role ];
				if ( isset( $my['discount_type'] ) && ! empty( $my['discount_type'] ) && isset( $my['wholesale_price'] ) && ! empty( $my['wholesale_price'] ) && isset( $my['min_quatity'] ) && ! empty( $my['min_quatity'] ) ) {
					$product_id = $product->get_id();
					$category_ids = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );

					$all_category_ids = array();

					foreach ( $category_ids as $cat_id ) {
						$all_category_ids[] = $cat_id;

						$parent_id = get_term( $cat_id, 'product_cat' )->parent;

						while ( 0 != $parent_id && ! in_array( $parent_id, $all_category_ids ) ) {
							$all_category_ids[] = $parent_id;
							$parent_id = get_term( $parent_id, 'product_cat' )->parent;
						}
					}
					$flag = false;
					$excluded_categories = $this->wwp_exclude_categories_for_wholesale_roles();

					if ( ! empty( $all_category_ids ) && ! empty( $excluded_categories ) ) {
						foreach ( $all_category_ids as $cat_id ) {
							if ( in_array( $cat_id, $excluded_categories ) ) {
								$flag = true;
								break;
							}
						}
					}

					if ( $flag ) {
						return $price;
					}

					return simple_wholesale_product_price_html( $my['discount_type'], $my['wholesale_price'], $price, $my['min_quatity'], $product );
				} else {
					return $price;
				}
			}
			
			return $price;
		}

		public function wwp_variable_price_format( $price, $product ) {  
			global $woocommerce;
			$prod_id = $product->get_id();
			$wwp_group = wwp_get_group_post_by_proid( $product->get_id(), get_current_user_id() );
			
			if ( ! $this->is_wholesale( $prod_id ) && ! $wwp_group ) {
				return $price;
			}
			$return_check       = false;
			$product_variations = $product->get_children();
			$wholesale_product_variations = array();
			$original_variation_price     = array();
			$role                         = get_current_user_role_id();
			if ( wwp_guest_wholesale_pricing_enabled() ) {
				if ( ( is_shop() && ! wwp_guest_wholesale_pricing_enabled( 'display_on_shop' ) ) || ( is_product() && ! wwp_guest_wholesale_pricing_enabled( 'display_on_single' ) ) ) {
					return $price;
				}
				$role = wwp_guest_wholesale_pricing_enabled( 'role' );
			}

			if ( wwp_get_group_post_by_proid( $product->get_id(), get_current_user_id() ) ) {
				$this->tire_table = true;
				return tire_variable_wholesale_product_price_html( $prod_id, $product, 'group' );
			}
			
			$data                         = get_post_meta( $prod_id, 'wholesale_multi_user_pricing', true );

			if ( isset( $data[ $role ] ) ) {
				
				if ( tire_variable_wholesale_product_price_html( $product->get_id(), $product, 'product' ) ) {
					$this->tire_table = true;
					return tire_variable_wholesale_product_price_html( $product->get_id(), $product, 'product' );
				}
			}
			
			if ( isset( $data[ $role ] ) && false === $return_check ) {
			
				foreach ( $product_variations as $product_variation ) {
					if ( isset( $data[ $role ] ) && isset( $data[ $role ][ $product_variation ] ) ) {
						$_product                       = wc_get_product( $product_variation );
						$wholesale_product_variations[] = $this->get_wholesale_price_multi( $data[ $role ]['discount_type'], $data[ $role ][ $product_variation ]['wholesaleprice'], $product_variation );
						//$wholesale_product_variations[] = $_product->get_regular_price();
						$original_variation_price[] = get_post_meta( $product_variation, '_regular_price', true );
					}
				}
				
				if ( ( 'object' != gettype( @$_product ) )) {
					return $price;
				}
				
				$return_check = true;
				return variable_wholesale_product_price_html( $wholesale_product_variations, $original_variation_price, $_product );
			}
			
			if ( tire_variable_wholesale_product_price_html( $prod_id, $product, 'category' ) ) {
				$this->tire_table = true;
				return tire_variable_wholesale_product_price_html( $prod_id, $product, 'category' );
			}
			
			$terms = get_the_terms( $prod_id, 'product_cat' );

			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$data = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );
					if ( isset( $data[ $role ]['min_quatity'] ) && false === $return_check ) {
						$min_quantity = (int) $data[ $role ]['min_quatity'];

						if ( empty( $min_quantity ) || ! isset( $min_quantity ) ) {
							$min_quantity = 1;
						}
						$wholesale_product_variations = array();
						$original_variation_price     = array();
						if ( isset( $data[ $role ] ) ) {
							foreach ( $product_variations as $product_variation ) {
									$_product                       = wc_get_product( $product_variation );
									$wholesale_product_variations[] = $this->get_wholesale_price_multi( $data[ $role ]['discount_type'], $data[ $role ]['wholesale_price'], $product_variation );
									$original_variation_price[]     = get_post_meta( $product_variation, '_regular_price', true );
							}
							$return_check = true;
							return variable_wholesale_product_price_html( $wholesale_product_variations, $original_variation_price, $_product );    
						}
					}
				}
			}
			
			$data = get_option( 'wholesale_multi_user_pricing' );
			if ( isset( $data[ $role ] ) && false === $return_check ) {
				if ( tire_variable_wholesale_product_price_html( $product->get_id(), $product, 'global' ) ) {
					$this->tire_table = true;
					return tire_variable_wholesale_product_price_html( $product->get_id(), $product, 'global' );
				}
			}
			 
			if ( isset( $data[ $role ]['min_quatity'] ) && false === $return_check ) {
				$product_id      = $product->get_id();
				$category_ids    = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
				$all_category_ids = array();

				foreach ( $category_ids as $cat_id ) {
					$all_category_ids[] = $cat_id;

					$parent_id = get_term( $cat_id, 'product_cat' )->parent;

					while ( 0 != $parent_id && ! in_array( $parent_id, $all_category_ids ) ) {
						$all_category_ids[] = $parent_id;
						$parent_id = get_term( $parent_id, 'product_cat' )->parent;
					}
				}

				$excluded_categories = $this->wwp_exclude_categories_for_wholesale_roles();

				$is_excluded = false;
				if ( ! empty( $excluded_categories ) && ! empty( $all_category_ids ) ) {
					foreach ( $all_category_ids as $cat_id ) {
						if ( in_array( $cat_id, $excluded_categories ) ) {
							$is_excluded = true;
							break;
						}
					}
				}
				if ( $is_excluded ) {
					return $price;
				}
				$min_quantity = $data[ $role ]['min_quatity'];

				if ( empty( $min_quantity ) || ! isset( $min_quantity ) ) {
					$min_quantity = 1;
				}
				$wholesale_product_variations = array();
				$original_variation_price     = array();

				foreach ( $product_variations as $product_variation ) {
					$_product                       = wc_get_product( $product_variation );
					$wholesale_product_variations[] = $this->get_wholesale_price_multi( $data[ $role ]['discount_type'], $data[ $role ]['wholesale_price'], $product_variation );
					$original_variation_price[]     = get_post_meta( $product_variation, '_regular_price', true );
				}
				return variable_wholesale_product_price_html( $wholesale_product_variations, $original_variation_price, $_product );
				$return_check = true;
			}

			if (empty($wholesale_product_variations)) {
				return $price;
			}
			
			return variable_wholesale_product_price_html( $wholesale_product_variations, $original_variation_price, $_product );
		}

		public function wwp_override_product_price_cart( $_cart ) {

			if ( ! $this->wholesale_pricing_cart_based_on_subtotal() ) {
				foreach ( $_cart->cart_contents as $key => $item ) {
					$item['data']->set_price( get_post_meta( $item['data']->get_id(), '_regular_price', true ) );
				}
				return;
			}
			remove_filter( 'woocommerce_product_get_price', array( $this, 'wwp_regular_price_change' ), 200, 2 );
			//remove_filter( 'woocommerce_product_variation_get_price', array( $this, 'wwp_regular_price_change' ), 200, 2 );
			remove_filter( 'woocommerce_product_get_regular_price', array( $this, 'wwp_regular_price_change' ), 200, 2 );
			remove_filter( 'woocommerce_product_variation_get_regular_price', array( $this, 'wwp_regular_price_change' ), 200, 2 );
			remove_filter( 'woocommerce_get_price_html', array( $this, 'wwp_change_product_price_display' ), 200, 2 );
			remove_filter( 'woocommerce_cart_item_price', array( $this, 'wwp_change_product_price_display' ), 200, 2 );
			
			global $woocommerce;
			$items = $woocommerce->cart->get_cart();
			$ws_group = wwp_get_group_post_by_proid( 0, get_current_user_id() );
			$role  = get_current_user_role_id();
			if ( empty( $role ) && ! $ws_group ) {
				return;
			}
			foreach ( $_cart->cart_contents as $key => $item ) {

				$return_check = false;
				$variation_id = $item['variation_id'];

				$product_id =  $item['product_id'];
				$product = wc_get_product($product_id);

				if ( ! empty( $variation_id ) ) {

					$ws_group = wwp_get_group_post_by_proid( $item['product_id'], get_current_user_id() );
					if ( $ws_group ) { 
						$group_id = isset( $ws_group[0] ) ? $ws_group[0] : '';
						
						if ( ! empty( $group_id ) ) {
							$discount_type = get_post_meta( $group_id, '_discount_type', true );
							$min_quantity = get_post_meta( $group_id, '_min_quantity', true );
							$max_quantity = get_post_meta( $group_id, '_max_quantity', true );
							$wwp_price = get_post_meta( $group_id, '_wwp_group_price', true );

							if ( ! empty( $discount_type ) && ! empty( $wwp_price ) && ! empty( $min_quantity ) && false === $return_check ) { 
								
								$regular_price = get_post_meta( $variation_id, '_regular_price', true );
								
								if ( empty( $regular_price ) || empty( $wwp_price ) ) {
									/**
									* Hooks
									*
									* @since 3.0
									*/
									$price = apply_filters( 'wwp_override_product_price_cart_price', $regular_price, $item );
									$item['data']->set_price( $price );

									continue;
								}
								
								if ( 'fixed' == $discount_type ) {
									$price = $wwp_price;
								} else {
									$price = $regular_price * $wwp_price / 100;
								}
								$qty            = $item['quantity'];
								if ( $qty >= $min_quantity && $qty <= $max_quantity ) {  
									/**
									* Filter
									*
									* @since 2.4
									*/
									$price = apply_filters( 'wwp_override_product_price_cart_price', $price, $item );
									$item['data']->set_price( $price );
									$return_check = true;
									continue;
								}
							}
						}
					}
					  
					$data = get_post_meta( $item['product_id'], 'wholesale_multi_user_pricing', true );
					if ( isset( $data[ $role ] ) ) { 
					
						if ( tire_variable_wholesale_product_price( $item['variation_id'], wc_get_product( $item['variation_id'] ), 'product' ) ) {
							$price = tire_variable_wholesale_product_price( $item['variation_id'], wc_get_product( $item['variation_id'] ), 'product' );
							
							/**
							* Hooks
							*
							* @since 3.0
							*/
							$price = apply_filters( 'wwp_override_product_price_cart_price', $price, $item );
							$item['data']->set_price( $price );
							$return_check = true;
							continue;
						}
						if ( isset( $data[ $role ][ $variation_id ] ) && false === $return_check ) {
						
							$min_quantity = $data[ $role ][ $variation_id ]['qty'];
							if ( empty( $min_quantity ) || ! isset( $min_quantity ) ) { // IF MIN QUANTITY NOT SET OR DOESN't EXIST ON DB
								$min_quantity = 1;
							}
	
							if ( $min_quantity <= $item['quantity'] ) {
								if ( ! empty( $this->get_variable_wholesale_price( $data[ $role ][ $variation_id ], $variation_id ) ) ) {
									
									/**
									* Hooks
									*
									* @since 3.0
									*/
									$price = apply_filters( 'wwp_override_product_price_cart_price', $this->get_variable_wholesale_price( $data[ $role ][ $variation_id ], $variation_id ), $item );
									$item['data']->set_price( $price );
									$return_check = true;
									continue;
								} else {
								
									/**
									* Hooks
									*
									* @since 3.0
									*/
									$price = apply_filters( 'wwp_override_product_price_cart_price', get_post_meta( $item['variation_id'], '_regular_price', true ), $item );
									$item['data']->set_price( $price );
									$return_check = true;
									continue;
								}
							} else {
							
								/**
								* Hooks
								*
								* @since 3.0
								*/
								$price = apply_filters( 'wwp_override_product_price_cart_price', get_post_meta( $item['variation_id'], '_regular_price', true ), $item );
								$item['data']->set_price( $price );
								$return_check = true;
								continue;
							}
							if ( true === $return_check ) {
								continue;
							}
						} else {
						
							/**
							* Hooks
							*
							* @since 3.0
							*/
							$price = apply_filters( 'wwp_override_product_price_cart_price', get_post_meta( $item['variation_id'], '_regular_price', true ), $item );
							$item['data']->set_price( $price );
							$return_check = true;
						}
					}
					 
					$terms = get_the_terms( $item['product_id'], 'product_cat' );
					if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
						foreach ( $terms as $term ) {
							$data = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );
							
							if ( isset( $data[ $role ] ) ) {
							
								if ( tire_variable_wholesale_product_price( $item['variation_id'], wc_get_product( $item['variation_id'] ), 'category' ) ) {
									$price = tire_variable_wholesale_product_price( $item['variation_id'], wc_get_product( $item['variation_id'] ), 'category' );
									
									/**
									* Hooks
									*
									* @since 3.0
									*/
									$price = apply_filters( 'wwp_override_product_price_cart_price', $price, $item );
									$item['data']->set_price( $price );
									$return_check = true;
									continue;
								}
							
							}
							
							if ( isset( $data[ $role ] ) ) {

								if ( isset( $data[ $role ]['discount_type'] ) && isset( $data[ $role ]['wholesale_price'] ) && false === $return_check ) {
								
									$my = $data[ $role ];
									if ( isset( $my['min_quatity'] ) && ! empty( $my['min_quatity'] ) ) {
										$wholesale_qty = (int) $my['min_quatity'];
									}
									if ( $wholesale_qty <= $item['quantity'] ) {
									
										/**
										* Hooks
										*
										* @since 3.0
										*/
										$price = apply_filters( 'wwp_override_product_price_cart_price', $this->change_price( get_post_meta( $item['variation_id'], '_regular_price', true ), $my['discount_type'], $my['wholesale_price'] ), $item );
										$item['data']->set_price( $price );
										$return_check = true;
										continue;
									} else {
									
										/**
										* Hooks
										*
										* @since 3.0
										*/
										$price = apply_filters( 'wwp_override_product_price_cart_price', get_post_meta( $item['variation_id'], '_regular_price', true ), $item );
										$item['data']->set_price( $price );
										$return_check = true;
										continue;
									}
								}   
							}   
						}
						if ( true === $return_check ) {
							continue;
						}
					}
					
					$data = get_option( 'wholesale_multi_user_pricing' );
					if ( isset( $data[ $role ] ) ) {
						if ( tire_variable_wholesale_product_price( $item['variation_id'], wc_get_product( $item['variation_id'] ), 'global' ) ) { 
							$price = tire_variable_wholesale_product_price( $item['variation_id'], wc_get_product( $item['variation_id'] ), 'global' );
							
							/**
							* Hooks
							*
							* @since 3.0
							*/
							$price = apply_filters( 'wwp_override_product_price_cart_price', $price, $item );
							$item['data']->set_price( $price );
							$return_check = true;
							continue;
						}
					}
					
					if ( isset( $data[ $role ] ) ) {
					
						if ( isset( $data[ $role ]['discount_type'] ) && isset( $data[ $role ]['wholesale_price'] ) ) {
							$product_id = $item['product_id'];
							$category_ids = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );

							$all_category_ids = array();

							foreach ( $category_ids as $cat_id ) {
								$all_category_ids[] = $cat_id;

								$parent_id = get_term( $cat_id, 'product_cat' )->parent;

								while ( 0 != $parent_id && ! in_array( $parent_id, $all_category_ids ) ) {
									$all_category_ids[] = $parent_id;
									$parent_id = get_term( $parent_id, 'product_cat' )->parent;
								}
							}

							$flag = false;
							$excluded_categories = $this->wwp_exclude_categories_for_wholesale_roles();

							if ( ! empty( $all_category_ids ) && ! empty( $excluded_categories ) ) {
								foreach ( $all_category_ids as $cat_id ) {
									if ( in_array( $cat_id, $excluded_categories ) ) {
										$flag = true;
										break;
									}
								}
							}

							if ( $flag ) {
								continue;
							}
							$my = $data[ $role ];
							if ( isset( $my['min_quatity'] ) && ! empty( $my['min_quatity'] ) ) {
								$wholesale_qty = $my['min_quatity'];
							}
							if ( $wholesale_qty <= $item['quantity'] ) {
							
								/**
								* Hooks
								*
								* @since 3.0
								*/
								$price = apply_filters( 'wwp_override_product_price_cart_price', $this->change_price( get_post_meta( $item['variation_id'], '_regular_price', true ), $my['discount_type'], $my['wholesale_price'] ), $item );
								$item['data']->set_price( $price );
								continue;
							} else {
							
								/**
								* Hooks
								*
								* @since 3.0
								*/
								$price = apply_filters( 'wwp_override_product_price_cart_price', get_post_meta( $item['variation_id'], '_regular_price', true ), $item );
								$item['data']->set_price( $price );
								$return_check = true;
							}
							if ( true === $return_check ) {
								continue;
							}
						} else {
						
							/**
							* Hooks
							*
							* @since 3.0
							*/
							$price = apply_filters( 'wwp_override_product_price_cart_price', get_post_meta( $item['variation_id'], '_regular_price', true ), $item );
							$item['data']->set_price( $price );
							$return_check = true;
							continue;
						}

					}
				} else {

					$ws_group = wwp_get_group_post_by_proid( $product_id, get_current_user_id() );
					if ( $ws_group ) { 
						$group_id = isset( $ws_group[0] ) ? $ws_group[0] : '';
						if ( ! empty( $group_id ) ) {
							$discount_type = get_post_meta( $group_id, '_discount_type', true );
							$min_quantity = get_post_meta( $group_id, '_min_quantity', true );
							$max_quantity = get_post_meta( $group_id, '_max_quantity', true );
							$wwp_price = get_post_meta( $group_id, '_wwp_group_price', true );

							
							if ( ! empty( $discount_type ) && ! empty( $wwp_price ) && ! empty( $min_quantity ) && false === $return_check ) { 
								
								$regular_price = get_post_meta( $item['product_id'], '_regular_price', true );
								if ( empty( $regular_price ) || empty( $wwp_price ) ) {
									/**
									* Hooks
									*
									* @since 3.0
									*/
									$price = apply_filters( 'wwp_override_product_price_cart_price', $regular_price, $item );
									$item['data']->set_price( $price );

									continue;
								}
								
								if ( 'fixed' == $discount_type ) {
									$price = $wwp_price;
								} else {
									$price = $regular_price * $wwp_price / 100;
								}
								$qty            = $item['quantity'];
								if ( $qty >= $min_quantity && $qty <= $max_quantity ) {  
									/**
									* Filter
									*
									* @since 2.4
									*/
									$price = apply_filters( 'wwp_override_product_price_cart_price', $price, $item );
									$item['data']->set_price( $price );
									$return_check = true;
									continue;
								}
							}
						}
					}
					$data = get_post_meta( $item['product_id'], 'wholesale_multi_user_pricing', true );
					
					if ( isset( $data[ $role ] ) ) {
						if ( tire_variable_wholesale_product_price( $item['product_id'], wc_get_product( $item['product_id'] ), 'product' ) ) {
							$price = tire_variable_wholesale_product_price( $item['product_id'], wc_get_product( $item['product_id'] ), 'product' );
							
							/**
							* Hooks
							*
							* @since 3.0
							*/
							$price = apply_filters( 'wwp_override_product_price_cart_price', $price, $item );
							$item['data']->set_price( $price );
							$return_check = true;
							continue;
						}
					
						$my = $data[ $role ];
						if ( isset( $my['discount_type'] ) && ! empty( $my['discount_type'] ) && isset( $my['wholesale_price'] ) && ! empty( $my['wholesale_price'] ) && isset( $my['min_quatity'] ) && ! empty( $my['min_quatity'] ) && false === $return_check ) {
							
							if ( isset( $my['min_quatity'] ) && ! empty( $my['min_quatity'] ) ) {
								$wholesale_qty = (int) $my['min_quatity'];
							}
							if ( $wholesale_qty <= $item['quantity'] ) {
								$price = $this->get_wholesale_price_multi( $my['discount_type'], $my['wholesale_price'], $item['product_id'] );

								if ( 'bundle' == $product->get_type() && 0 == $price ) {
									$item['data']->set_price( $price );
									$return_check = true;
									continue;
								}
								 
								if ( ! empty( $price ) ) {
								
									/**
									* Hooks
									*
									* @since 3.0
									*/
									$price = apply_filters( 'wwp_override_product_price_cart_price', $price, $item );
									$item['data']->set_price( $price );
									$return_check = true;
									continue;
								}
							} else {
							
								/**
								* Hooks
								*
								* @since 3.0
								*/
								$price = apply_filters( 'wwp_override_product_price_cart_price', get_post_meta( $item['product_id'], '_regular_price', true ), $item );
								$item['data']->set_price( $price );
								$return_check = true;
								continue;
							}
						} else {
							/**
							* Hooks
							*
							* @since 3.0
							*/
							$price = apply_filters( 'wwp_override_product_price_cart_price', get_post_meta( $item['product_id'], '_regular_price', true ), $item );
							$item['data']->set_price( $price );
							$return_check = true;
							continue;
						}
					}

					$terms = get_the_terms( $item['product_id'], 'product_cat' );
					if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {

						foreach ( $terms as $term ) {
							$data = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );

							if ( isset( $data[ $role ] ) ) {
							
								if ( tire_variable_wholesale_product_price( $item['product_id'], wc_get_product( $item['product_id'] ), 'category' ) ) {
									$price = tire_variable_wholesale_product_price( $item['product_id'], wc_get_product( $item['product_id'] ), 'category' );
									
									/**
									* Hooks
									*
									* @since 3.0
									*/
									$price = apply_filters( 'wwp_override_product_price_cart_price', $price, $item );
									$item['data']->set_price( $price );
									$return_check = true;
									break;
								}
							}

							if ( isset( $data[ $role ] ) ) {

								if ( isset( $data[ $role ]['discount_type'] ) && isset( $data[ $role ]['wholesale_price'] ) && false === $return_check ) {
									$my = $data[ $role ];
									if ( isset( $my['min_quatity'] ) && ! empty( $my['min_quatity'] ) ) {
										$wholesale_qty = (int) $my['min_quatity'];
									}
									if ( $wholesale_qty <= $item['quantity'] ) {

										/**
										* Hooks
										*
										* @since 3.0
										*/                                      
										$price = apply_filters( 'wwp_override_product_price_cart_price', $this->change_price( get_post_meta( $item['product_id'], '_regular_price', true ), $my['discount_type'], $my['wholesale_price'] ), $item );
										$item['data']->set_price( $price );
										$return_check = true;
										break;
									} else {
										/**
										* Hooks
										*
										* @since 3.0
										*/
										$price = apply_filters( 'wwp_override_product_price_cart_price', get_post_meta( $item['product_id'], '_regular_price', true ), $item );
										$item['data']->set_price( $price );
										$return_check = true;
										continue;
									}
								
								} else {
								
									/**
									* Hooks
									*
									* @since 3.0
									*/
									$price = apply_filters( 'wwp_override_product_price_cart_price', get_post_meta( $item['product_id'], '_regular_price', true ), $item );
									$item['data']->set_price( $price );
									$return_check = true;
									continue;
								}
								
							}
						}
						if ( true === $return_check ) {
							continue;
						}
					}
					
					$data = get_option( 'wholesale_multi_user_pricing' );
					
					if ( isset( $data[ $role ] ) ) {
					
						if ( tire_variable_wholesale_product_price( $item['product_id'], wc_get_product( $item['product_id'] ), 'global' ) ) { 
							$price = tire_variable_wholesale_product_price( $item['product_id'], wc_get_product( $item['product_id'] ), 'global' );
							/**
							* Hooks
							*
							* @since 3.0
							*/
							$price = apply_filters( 'wwp_override_product_price_cart_price', $price, $item );
							$item['data']->set_price( $price );
							$return_check = true;
							continue;
						}
					}

					if ( isset( $data[ $role ] ) ) {
					
						if ( isset( $data[ $role ]['discount_type'] ) && isset( $data[ $role ]['wholesale_price'] ) ) {
							$product_id = $item['product_id'];
							$category_ids = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );

							$all_category_ids = array();

							foreach ( $category_ids as $cat_id ) {
								$all_category_ids[] = $cat_id;

								$parent_id = get_term( $cat_id, 'product_cat' )->parent;

								while ( 0 != $parent_id && ! in_array( $parent_id, $all_category_ids ) ) {
									$all_category_ids[] = $parent_id;
									$parent_id = get_term( $parent_id, 'product_cat' )->parent;
								}
							}
							$flag = false;
							$excluded_categories = $this->wwp_exclude_categories_for_wholesale_roles();

							if ( ! empty( $all_category_ids ) && ! empty( $excluded_categories ) ) {
								foreach ( $all_category_ids as $cat_id ) {
									if ( in_array( $cat_id, $excluded_categories ) ) {
										$flag = true;
										break;
									}
								}
							}

							if ( $flag ) {
								continue;
							}
							$my = $data[ $role ];
							if ( isset( $my['min_quatity'] ) && ! empty( $my['min_quatity'] ) ) {
								$wholesale_qty = $my['min_quatity'];
							}
							if ( $wholesale_qty <= $item['quantity'] ) {
							
								/**
								* Hooks
								*
								* @since 3.0
								*/
								$price = apply_filters( 'wwp_override_product_price_cart_price', $this->change_price( get_post_meta( $item['product_id'], '_regular_price', true ), $my['discount_type'], $my['wholesale_price'] ), $item );

								//compatibility for bundle products
								if ( 'bundle' === $product->get_type() && !empty( $product->get_price() ) ) {
									/**
									 * Hooks
									 * 
									 *  @since 3.0
									 */
									$price = apply_filters( 'wwp_override_product_price_cart_price', $this->change_price( $product->get_price(), $my['discount_type'], $my['wholesale_price'] ), $item );
									$item['data']->set_price($price);
								}
								
								if ( 'bundle' != $product->get_type() ) {
									$item['data']->set_price( $price );
								}

								continue;
							} else {
							
								/**
								* Hooks
								*
								* @since 3.0
								*/
								$price = apply_filters( 'wwp_override_product_price_cart_price', get_post_meta( $item['product_id'], '_regular_price', true ), $item );
								$item['data']->set_price( $price );
								$return_check = true;
								continue;
							}
							if ( true === $return_check ) {
								continue;
							}
						} else {
						
							/**
							* Hooks
							*
							* @since 3.0
							*/
							$price = apply_filters( 'wwp_override_product_price_cart_price', get_post_meta( $item['product_id'], '_regular_price', true ), $item );
							$item['data']->set_price( $price );
							$return_check = true;
							continue;
						}
					}
				}
			} //ends foreach
		}
		
		public function get_variable_wholesale_price( $arr, $variation_id ) {  
			if ( empty( $arr ) ) {
				return false;
			}

			if ( wwp_guest_wholesale_pricing_enabled() ) { 
				 return false;
			}
			
			$role           = get_current_user_role_id();
			$data           = get_post_meta( wp_get_post_parent_id( $variation_id ), 'wholesale_multi_user_pricing', true );
			$variable_price = isset( $arr['wholesaleprice'] ) ? $arr['wholesaleprice'] : 0;

			$wholesale_amount_type        = $data[ $role ]['discount_type'];
			$wc_get_product               = wc_get_product( $variation_id );
			$wwp_variation_explode_eneble = get_post_meta( $wc_get_product->get_parent_id(), 'wwp_variation_explode_eneble', true );
			if ( 'fixed' === $wholesale_amount_type ) {
				if ( 'yes' == $wwp_variation_explode_eneble && 'excl' == get_option( 'woocommerce_tax_display_shop' ) ) {
					$variable_price = wwp_get_price_including_tax( $wc_get_product, array( 'price' => $variable_price ) );
				}
				return $variable_price;
			} else {
				$product_price   = get_post_meta( $variation_id, '_regular_price', true );
				$product_price   = ( isset( $product_price ) && is_numeric( $product_price ) ) ? $product_price : 0;
				$variable_price  = ( isset( $variable_price ) && is_numeric( $variable_price ) ) ? $variable_price : 0;
				$wholesale_price = $product_price * $variable_price / 100;
				if ( 'yes' == $wwp_variation_explode_eneble && 'excl' == get_option( 'woocommerce_tax_display_shop' ) ) {
					$wholesale_price = wwp_get_price_including_tax( $wc_get_product, array( 'price' => $wholesale_price ) );
				}
				return $wholesale_price;
			}
		}
		
		public function is_wholesale( $product_id ) {
			$role = get_current_user_role_id();
			if ( wwp_guest_wholesale_pricing_enabled() ) {
				if ( ( is_shop() && ! wwp_guest_wholesale_pricing_enabled( 'display_on_shop' ) ) || ( is_product() && ! wwp_guest_wholesale_pricing_enabled( 'display_on_single' ) ) ) {
					return false;
				}
				$role = wwp_guest_wholesale_pricing_enabled( 'role' );
			}
			$data = get_post_meta( $product_id, 'wholesale_multi_user_pricing', true );
			if ( isset( $data[ $role ] ) ) {
				return true;
			}
			
			$terms = get_the_terms( $product_id, 'product_cat' );
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$data = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );
					if ( isset( $data[ $role ] ) && isset( $data[ $role ]['discount_type'] ) && isset( $data[ $role ]['wholesale_price'] ) ) {
						return true;
					}
				}
			}
			$data = get_option( 'wholesale_multi_user_pricing' );
			if ( isset( $data[ $role ] ) && isset( $data[ $role ]['discount_type'] ) && isset( $data[ $role ]['wholesale_price'] ) ) {
				return true;
			}
			return false;
		}
		public function wcpt_product_table_args( $wcpt_product_table_args ) {
		
			$settings             = $this->settings;
			$wholesaler_prod_only = ( isset( $settings['wholesaler_prodcut_only'] ) && 'yes' == $settings['wholesaler_prodcut_only'] ) ? 'yes' : 'no';
			if ( 'yes' == $wholesaler_prod_only ) {
				if ( is_wholesaler_user( get_current_user_id() ) ) {
					$exclude_ids                             = $this->wwp_hide_product_ids( array() );
					$wcpt_product_table_args['post__not_in'] = $exclude_ids['post__not_in']; 
				}
			}
			return $wcpt_product_table_args;
		}
		public function get_wholesale_price_multi( $discount, $wprice, $post_id ) {

			if (empty($wprice)) {
				return get_post_meta( $post_id, '_regular_price', true );
			}

			if ( 'fixed' == $discount ) {
				return $wprice;
			} else {
				$product = wc_get_product( $post_id );
				$product_price = 'bundle' === $product->get_type() && ! empty( get_post_meta( $post_id, '_price', true ) ) ? get_post_meta( $post_id, '_regular_price', true ) : get_post_meta( $post_id, '_price', true );
				/**
				* Filter Hook
				*
				* @since 2.5
				*/
				$product_price = apply_filters( 'wwp_check_product_price', $product_price, $product, $post_id );
				$product_price   = ( isset( $product_price ) && is_numeric( $product_price ) ) ? $product_price : 0;
				$wholesale_price = $product_price * $wprice / 100;
				return $wholesale_price;
			}
		}
		public function woocommerce_shortcode_products_query( $q ) {
			$settings             = $this->settings;
			$wholesaler_prod_only = ( isset( $settings['wholesaler_prodcut_only'] ) && 'yes' == $settings['wholesaler_prodcut_only'] ) ? 'yes' : 'no';

			if ( 'yes' == $wholesaler_prod_only && ! is_admin() ) {
				if ( is_wholesaler_user( get_current_user_id() ) ) {
					$q = $this->wwp_hide_product_ids( $q );
				}
			}
			return $q;
		}
		 
		public function wwp_hide_product_ids( $q ) { 
		
			$data = get_option( 'wholesale_multi_user_pricing' );
			$role = get_current_user_role_id();

			if ( ! isset( $data[ $role ] ) ) {
				$total_ids = array();
				$cate      = array();

				$categories = get_terms( array( 'taxonomy' => 'product_cat' ) );

				if ( is_array( $categories ) ) {

					foreach ( $categories as $category ) {

						$data = get_term_meta( $category->term_id, 'wholesale_multi_user_pricing', true );

						if ( isset( $data[ $role ]['wholesale_price'] ) ) {

							$cate[] = $category->term_id;

						}
					}

					$args = array(
						'post_type' => 'product',
						'fields'    => 'ids',
						'tax_query' => array(
							array(
								'taxonomy' => 'product_cat',
								'field'    => 'term_id',
								'terms'    => $cate,
								'operator' => 'IN',

							),
						),
					);

					$ids = get_posts( $args );

					if ( is_array( $ids ) ) {

						foreach ( $ids  as $id ) {
								$total_ids[] = $id;
						}
					}
				}

				$all_ids = get_posts(
					array(
						'post_type'        => 'product',
						'numberposts'      => -1,
						'suppress_filters' => false,
						'post_status'      => 'publish',
						'fields'           => 'ids',
					)
				);
				foreach ( $all_ids as $id ) {

					$data = get_post_meta( $id, 'wholesale_multi_user_pricing', true );

					if ( isset( $data[ $role ] ) ) {
						$total_ids[] = $id;
					}
				}
				
				if ( is_array( $ids ) ) {
					foreach ( $ids  as $id ) {
						$total_ids[] = $id;
					}
				}
				unset( $q['post__in'] );
				
				
				if ( is_array( $total_ids ) ) {

					$total_ids         = array_unique( $total_ids );
					$exclude_ids       = array_diff( $all_ids, $total_ids );
					$post__not_in      = isset( $q['post__not_in'] ) ? (array) $q['post__not_in'] : array();
					$q['post__not_in'] = array_merge( $post__not_in, $exclude_ids );

				} else {

					$q['post__not_in'] = $all_ids;

				}
			}
			return $q;
		}
		 
		public function default_wholesaler_products_only( $q ) { 
			$settings             = $this->settings;
			$wholesaler_prod_only = ( isset( $settings['wholesaler_prodcut_only'] ) && 'yes' == $settings['wholesaler_prodcut_only'] ) ? 'yes' : 'no';
			
			if ( 'yes' == $wholesaler_prod_only && $q->is_main_query() && ! is_admin() ) {
				
				if ( is_wholesaler_user( get_current_user_id() ) ) {

					// Global price get
					$data = get_option( 'wholesale_multi_user_pricing' );

					$role = get_current_user_role_id();

					$total_ids = array();
					if ( ! isset( $data[ $role ] ) ) {
						
						$cate = array();

						$categories = get_terms( array( 'taxonomy' => 'product_cat' ) );

						if ( is_array( $categories ) ) {

							foreach ( $categories as $category ) {

								$data = get_term_meta( $category->term_id, 'wholesale_multi_user_pricing', true );

								if ( isset( $data[ $role ]['wholesale_price'] ) ) {

									$cate[] = $category->term_id;

								}
							}
							$args = array(
								'post_type'   => 'product',
								'numberposts' => -1,
								'fields'      => 'ids',
								'tax_query'   => array(
									array(
										'taxonomy' => 'product_cat',
										'field'    => 'term_id',
										'terms'    => $cate,
										'operator' => 'IN',

									),
								),
							);

							$ids = get_posts( $args );

							if ( is_array( $ids ) ) {

								foreach ( $ids  as $id ) {
										$total_ids[] = $id;
								}
							}
						}

						$all_ids = get_products_by_meta();
						
						foreach ( $all_ids as $id ) {

							$data = get_post_meta( $id, 'wholesale_multi_user_pricing', true );

							if ( isset( $data[ $role ] ) ) {
								$total_ids[] = $id;
							}
						}

						if ( is_array( $ids ) ) {
							foreach ( $ids  as $id ) {
								$total_ids[] = $id;
							}
						}

						if ( ! empty( $total_ids ) && is_array( $total_ids ) ) {

							$total_ids   = array_unique( $total_ids );
							$exclude_ids = array_diff( $all_ids, $total_ids );
							$q->set( 'post__not_in', $exclude_ids );
							$this->exclude_ids = $exclude_ids;
							add_filter( 'woocommerce_related_products', array( $this, 'exclude_related_products' ), 10, 3 );
							
						} else {
							$q->set( 'post__not_in', $all_ids );
						}
					}
				}
			}
		}

		public function exclude_related_products( $related_posts, $product_id, $args ) {
			 return array_diff( $related_posts, $this->exclude_ids );
		}
		
		/**
		 * Method wholesale_pricing_cart_based_on_subtotal
		 *
		 * @param Array $cart 
		 *
		 * @return void
		 */
		public function wholesale_pricing_cart_based_on_subtotal( $context = '' ) {

			$global_settings    = get_option( 'wholesale_multi_user_pricing' );
			$role               = get_current_user_role_id();
			
			if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
				return true;
			}

			if ( ! is_user_logged_in() && ! is_wholesaler_user( get_current_user_id() ) ) {
				return true;
			}

			// return if Min Subtotal featured is disabled
			if ( ! isset( $global_settings[$role] ) || ! isset( $global_settings[$role]['min_subtotal'] ) || empty( $global_settings[$role]['min_subtotal'] ) || 0 == $global_settings[$role]['min_subtotal'] ) {
				return true;
			}

			$cart_total     = 0;
			$cart_items     = 0;
			$min_quantity   = 0;
			$min_subtotal   = $global_settings[$role]['min_subtotal'];
			$quantity_check =  false;

			foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {

				$wholesale_price = '';

				if ( $cart_item['data'] instanceof WC_Product ) {
					$wholesale_price = (float) $this->wwp_get_wholesale_price( $cart_item['data'] );
					$quantity = (int) $cart_item['quantity'];
					$cart_total += $wholesale_price * $quantity;
					$cart_items += $quantity;

					if ( ! empty( $global_settings[$role]['min_quatity'] ) && $global_settings[$role]['min_quatity'] > 0 ) {
						$min_quantity = $global_settings[$role]['min_quatity'];
					}

					// min quantity check in category discount
					$terms = get_the_terms( $cart_item['data']->get_id(), 'product_cat' );

					if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {

						foreach ( $terms as $term ) {
							$category_discount = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );

							if ( isset( $category_discount[$role]['min_quatity'] ) ) {
								$min_quantity = $category_discount[$role]['min_quatity'];
							}
						}
					}
					
					// min quantity check in product discount
					$product_discount = get_post_meta( $cart_item['data']->get_id(), 'wholesale_multi_user_pricing', true );

					if ( isset( $product_discount[$role]['min_quatity'] ) ) {
						$min_quantity = $product_discount[$role]['min_quatity'];
					}

				}

				$flag = false;
				if ( ! ( $cart_total >= $min_subtotal ) && 0 != $cart_total ) {
					$flag = true;
					continue;
				}

				if ( 'view' == $context && true == $flag ) {
					$min_qty_messages['min_order_subtotal'] = sprintf('You have not met the minimum order subtotal of <strong>(%s)</strong> to activate adjusted pricing. Retail prices will be shown below until the minimum order threshold is met. The cart subtotal calculated with wholesale prices is <strong>(%s)</strong>', wc_price($min_subtotal), wc_price($cart_total));
				}

				if ( ( ! empty( $min_quantity ) && $min_quantity > 0 ) && ! ( (int) $cart_item['quantity'] >= $min_quantity ) ) {
					$min_qty_messages[] = sprintf( 'You have not met the minimum quantity <strong>(%s)</strong> for the product %s.' , $min_quantity, $cart_item['data']->get_title() );
				}
			}

			if ( ! empty( $min_qty_messages ) && count( $min_qty_messages ) > 0 ) {
				if ( 'view' == $context ) {
					return $min_qty_messages;
				}
				return false;

			} elseif ( ! ( $cart_total >= $min_subtotal ) && 0 != $cart_total ) {
				if ( 'view' == $context ) {
					$message = sprintf( 'You have not met the minimum order subtotal of <strong>(%s)</strong> to activate adjusted pricing. Retail prices will be shown below until the minimum order threshold is met. The cart subtotal calculated with wholesale prices is <strong>(%s)</strong>', wc_price( $min_subtotal ), wc_price( $cart_total ) );
					return $message;
				}
				return false;
			} else {
				return true;
			}
		}

		public function wwp_get_wholesale_price( $product ) {
			$cart_item_quantities = WC()->cart->get_cart_item_quantities();
			$role  = get_current_user_role_id();
			$variation_id = $product->get_id();
			if ( $product->is_type( 'variation' ) ) {
				$data = get_post_meta( $product->get_id(), 'wholesale_multi_user_pricing', true );
				if ( isset( $data[ $role ] ) ) { 
				
					if ( tire_variable_wholesale_product_price( $variation_id, wc_get_product( $variation_id ), 'product' ) ) {
						return tire_variable_wholesale_product_price( $variation_id, wc_get_product( $variation_id ), 'product' );
					}
					
					if ( isset( $data[ $role ][ $variation_id ] ) ) {
					
						$min_quantity = $data[ $role ][ $variation_id ]['qty'];
						if ( empty( $min_quantity ) || ! isset( $min_quantity ) ) { // IF MIN QUANTITY NOT SET OR DOESN't EXIST ON DB
							$min_quantity = 1;
						}

						if ( $min_quantity <= $cart_item_quantities[$variation_id] ) {
							if ( ! empty( $this->get_variable_wholesale_price( $data[ $role ][ $variation_id ], $variation_id ) ) ) {
								return $this->get_variable_wholesale_price( $data[ $role ][ $variation_id ], $variation_id );
							} else {
								return get_post_meta( $variation_id, '_regular_price', true );
							}
						} else {
							return get_post_meta( $variation_id, '_regular_price', true );
						}
					} else {
						return get_post_meta( $variation_id, '_regular_price', true );
					}
				}
				$terms = get_the_terms( $product->get_parent_id(), 'product_cat' );
				if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
					foreach ( $terms as $term ) {
						$data = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );
						if ( isset( $data[ $role ] ) ) {
							if ( tire_variable_wholesale_product_price( $variation_id, wc_get_product( $variation_id ), 'category' ) ) {
								return tire_variable_wholesale_product_price( $variation_id, wc_get_product( $variation_id ), 'category' );
							}
						
						}
						
						if ( isset( $data[ $role ] ) ) {

							if ( isset( $data[ $role ]['discount_type'] ) && isset( $data[ $role ]['wholesale_price'] ) ) {
							
								$my = $data[ $role ];
								if ( isset( $my['min_quatity'] ) && ! empty( $my['min_quatity'] ) ) {
									$wholesale_qty = (int) $my['min_quatity'];
								}
								if ( $wholesale_qty <= $cart_item_quantities[$variation_id] ) {
									return $this->change_price( get_post_meta( $variation_id, '_regular_price', true ), $my['discount_type'], $my['wholesale_price'] );
								} else {
									return get_post_meta( $variation_id, '_regular_price', true );
								}
							}   
						}   
					}
				}
				
				$data = get_option( 'wholesale_multi_user_pricing' );
				if ( isset( $data[ $role ] ) ) {
					if ( tire_variable_wholesale_product_price( $variation_id, wc_get_product( $variation_id ), 'global' ) ) { 
						return tire_variable_wholesale_product_price( $variation_id, wc_get_product( $variation_id ), 'global' );
					}
				}
				
				if ( isset( $data[ $role ] ) ) {
				
					if ( isset( $data[ $role ]['discount_type'] ) && isset( $data[ $role ]['wholesale_price'] ) ) {
						$my = $data[ $role ];
						if ( isset( $my['min_quatity'] ) && ! empty( $my['min_quatity'] ) ) {
							$wholesale_qty = $my['min_quatity'];
						}
						if ( $wholesale_qty <= $cart_item_quantities[$variation_id] ) {
							return $this->change_price( get_post_meta( $variation_id, '_regular_price', true ), $my['discount_type'], $my['wholesale_price'] );
						} else {
							return get_post_meta( $variation_id, '_regular_price', true );
						}
					} else {
						return get_post_meta( $variation_id, '_regular_price', true );
					}

				}
			} else {
				$data = get_post_meta( $product->get_id(), 'wholesale_multi_user_pricing', true );
				if ( isset( $data[ $role ] ) ) {
					if ( tire_variable_wholesale_product_price( $product->get_id(), wc_get_product( $product->get_id() ), 'product' ) ) {
						return tire_variable_wholesale_product_price( $product->get_id(), wc_get_product( $product->get_id() ), 'product' );
					}
				
					$my = $data[ $role ];
					if ( isset( $my['discount_type'] ) && ! empty( $my['discount_type'] ) && isset( $my['wholesale_price'] ) && ! empty( $my['wholesale_price'] ) && isset( $my['min_quatity'] ) && ! empty( $my['min_quatity'] ) ) {
						
						if ( isset( $my['min_quatity'] ) && ! empty( $my['min_quatity'] ) ) {
							$wholesale_qty = (int) $my['min_quatity'];
						}

						return $this->get_wholesale_price_multi( $my['discount_type'], $my['wholesale_price'], $product->get_id() );
					} else {
						return get_post_meta( $product->get_id(), '_regular_price', true );
					}
				}

				$terms = get_the_terms( $product->get_id(), 'product_cat' );
				if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {

					foreach ( $terms as $term ) {
						$data = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );

						if ( isset( $data[ $role ] ) ) {
						
							if ( tire_variable_wholesale_product_price( $product->get_id(), wc_get_product( $product->get_id() ), 'category' ) ) {
								return tire_variable_wholesale_product_price( $product->get_id(), wc_get_product( $product->get_id() ), 'category' );
							}
						}

						if ( isset( $data[ $role ] ) ) {

							if ( isset( $data[ $role ]['discount_type'] ) && isset( $data[ $role ]['wholesale_price'] ) ) {
								$my = $data[ $role ];
								if ( isset( $my['min_quatity'] ) && ! empty( $my['min_quatity'] ) ) {
									$wholesale_qty = (int) $my['min_quatity'];
								}
								return $this->change_price( get_post_meta( $product->get_id(), '_regular_price', true ), $my['discount_type'], $my['wholesale_price'] );
							} else {
								return get_post_meta( $product->get_id(), '_regular_price', true );
							}
							
						}
					}
				}
				
				$data = get_option( 'wholesale_multi_user_pricing' );
				
				if ( isset( $data[ $role ] ) ) {
				
					if ( tire_variable_wholesale_product_price( $product->get_id(), wc_get_product( $product->get_id() ), 'global' ) ) { 
						return tire_variable_wholesale_product_price( $product->get_id(), wc_get_product( $product->get_id() ), 'global' );
					}
				}

				if ( isset( $data[ $role ] ) ) {
				
					if ( isset( $data[ $role ]['discount_type'] ) && isset( $data[ $role ]['wholesale_price'] ) ) {
						
						$my = $data[ $role ];
						if ( isset( $my['min_quatity'] ) && ! empty( $my['min_quatity'] ) ) {
							$wholesale_qty = $my['min_quatity'];
						}
						return $this->change_price( get_post_meta( $product->get_id(), '_regular_price', true ), $my['discount_type'], $my['wholesale_price'] );
					} else {
						return get_post_meta( $product->get_id(), '_regular_price', true );
					}
				}
			}
			return 0;
		}
		public function woocommerce_cart_min_subtotal_errors( $errors ) {
			$message = $this->wholesale_pricing_cart_based_on_subtotal('view');
			if ( ! is_cart() || defined('DOING_AJAX') ) {
				return;
			}
			if ( ! WC()->session ) {
				wc_load_cart();
			}
			if ( ! is_bool( $message ) && ( is_cart() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) ) {
				if ( is_array( $message ) ) {
					foreach ( $message as $m ) {
						wc_add_notice( $m, 'error');
						if ( $errors instanceof WP_Error ) {
							$errors->add( uniqid(), $m );
						}
					}
				} else {
					wc_add_notice( $message, 'error');
				}
			}
		}
	}
	
	$wwp_multi_user_obj = new WWP_Easy_Wholesale_Multiuser();
	$GLOBALS['WWP_MULTI_USER'] = $wwp_multi_user_obj;
}
