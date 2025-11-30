<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('WWP_REST_Wholesale_Category_V1_Controller')) {

	class WWP_REST_Wholesale_Category_V1_Controller extends WC_REST_Controller {
	
		protected $namespace = 'wholesale/v1';
		protected $rest_base = 'category-discount';
		protected $wc_wholesale_prices;

		public function __construct() {
			global $wc_wholesale_prices;
			$this->wc_wholesale_prices = $wc_wholesale_prices;

			add_action('rest_api_init', array( $this, 'register_routes' ));
			add_filter('wwp_rest_response_product_object', array( $this, 'filter_product_object' ), 10, 3);
		}

		public function filter_product_object( $response, $object, $request ) {
			if (isset($request['fields']) && !empty($request['fields'])) {
				$data    = $response->get_data();
				$newdata = array();
				foreach (explode(',', $request['fields']) as $field) {
					$newdata[$field] = $data[$field];
				}
				$response->set_data($newdata);
			}
			return $response;
		}

		public function register_routes() {
			register_rest_route(
				$this->namespace,
				'/' . $this->rest_base,
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_items' ),
						'permission_callback' => array( $this, 'permissions_check' ),
					),
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( $this, 'create_item' ),
						'permission_callback' => array( $this, 'permissions_check' ),
					),
				)
			);

			register_rest_route(
				$this->namespace,
				'/' . $this->rest_base . '/(?P<cate_id>[a-z0-9_]*)',
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_item' ),
						'permission_callback' => array( $this, 'permissions_check' ),
					),
					array(
						'methods'             => WP_REST_Server::EDITABLE,
						'callback'            => array( $this, 'update_item' ),
						'permission_callback' => array( $this, 'permissions_check' ),
					),
					array(
						'methods'             => WP_REST_Server::DELETABLE,
						'callback'            => array( $this, 'delete_item' ),
						'permission_callback' => array( $this, 'permissions_check' ),
					),
				)
			);
		}

		public function permissions_check( $request ) {
			if (current_user_can('manage_woocommerce')) {
				return true;
			}
			return new WP_Error('wholesale_rest_role_permission_failed', __('You don\'t have permission.', 'woocommerce-wholesale-pricing'), array( 'status' => rest_authorization_required_code() ));
		}

		public function get_items( $request ) {
			$cate       = array();
			$categories = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
			if (!empty($categories)) {
				foreach ($categories as $category) {
					$data = get_term_meta($category->term_id, 'wholesale_multi_user_pricing', true);
					if (!empty($data)) {
						$pre = array_map(function ( $key, $value ) {
							$value['role-id'] = $key;
							return $value;
						}, array_keys($data), $data);
			
						$cate[] = array(
							'category-id' => $category->term_id,
							'category-name' => $category->name,
							'wholesale_price' => $pre,
						);
					}
				}
			}
		
			/**
			 * Hooks
			 *
			 * @since 2.4.0
			 */     
			$wholesale_category = apply_filters('wwp_api_fetch_wholesale_category_filter', $cate, $request);
			return new WP_REST_Response($wholesale_category, 200);
		}
		

		public function get_item( $request ) {
			$cate_id  = $request->get_param('cate_id');
			$cate     = array();
			$category = get_term_by('id', $cate_id, 'product_cat');
			if (!empty($category)) {
					$data = get_term_meta($category->term_id, 'wholesale_multi_user_pricing', true);
				if (!empty($data)) {
					$pre = array_map(function ( $key, $value ) {
						$value['role-id'] = $key;
						return $value;
					}, array_keys($data), $data);
			
					$cate[] = array(
						'category-id' => $category->term_id,
						'category-name' => $category->name,
						'wholesale_price' => $pre,
					);
				}
			}
			/**
			 * Hooks
			 *
			 * @since 2.4.0
			 */     
			$wholesale_category = apply_filters('wwp_api_fetch_wholesale_category_filter', $cate, $request);
			return new WP_REST_Response($wholesale_category, 200);
		}
		
		public function create_item( $request ) {  
			extract( $request->get_params() ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
			if ( isset( $request['wholesale_price'] )  ) {
				$request_data    = $request->get_param('create_category');
				$wholesale_price = $request->get_param('wholesale_price');
				$wholesale_role = term_exists($wholesale_price['role'], 'wholesale_user_roles');
				if (!empty($wholesale_role)) {
					$result          = wp_insert_term($request_data['name'] , 'product_cat', $request_data);
					if (is_wp_error($result)) {
						$response_message = $result->get_error_message();
						$response_status  = 400;
					} else {
							$data           =  get_term_meta( $result['term_id'], 'wholesale_multi_user_pricing', false );
							$role           = $wholesale_price['role'];
							$w_price        = $wholesale_price['wholesale_price'];
							$discount_type  = $wholesale_price['discount_type'];
							$min_qty        = $wholesale_price['min_qty'];
							$step           = 1;
							
							$data[ $wholesale_role['term_id'] ]['slug']            = $role;
							$data[ $wholesale_role['term_id'] ]['discount_type']   = wc_clean( $discount_type );
							$data[ $wholesale_role['term_id'] ]['wholesale_price'] = is_numeric( wc_clean( $w_price ) ) ? wc_clean( $w_price ) : '';
							$data[ $wholesale_role['term_id'] ]['min_quatity']     = is_numeric( wc_clean( $min_qty ) ) ? wc_clean( $min_qty ) : 1;
							
							update_term_meta( $result['term_id'], 'wholesale_multi_user_pricing', $data );
							$response_message = __('Successfully created category discount.', 'woocommerce-wholesale-pricing');
							$response_status  = 200;
					}
					$response = array(
						'message' => $response_message,
					);
					return new WP_REST_Response($response, $response_status);
				}
			}
			return new WP_Error('wholesale_rest_category_cannot_create', __('Item not created.', 'woocommerce-wholesale-pricing'), array( 'status' => 400 ));
		}

		public function update_item( $request ) {
			$cate_id         = $request->get_param('cate_id');
			$category        = get_term_by('id', $cate_id, 'product_cat');
			$request_data    = $request->get_param('update_category');
			$wholesale_price = $request->get_param('wholesale_price');
		
			if (isset($wholesale_price['role']) && !empty($category)) {
				$result = wp_update_term($cate_id, 'product_cat', $request_data);
		
				if (is_wp_error($result)) {
					$response_message = $result->get_error_message();
					$response_status  = 400;
				} else {
					$wholesale_role = term_exists($wholesale_price['role'], 'wholesale_user_roles');
					$data           = get_term_meta($cate_id, 'wholesale_multi_user_pricing', true);
		
					if (!empty($wholesale_role)) {
						$data[$wholesale_role['term_id']] = array(
							'slug' => $wholesale_price['role'],
							'discount_type' => wc_clean($wholesale_price['discount_type']),
							'wholesale_price' => is_numeric(wc_clean($wholesale_price['wholesale_price'])) ? wc_clean($wholesale_price['wholesale_price']) : '',
							'min_quatity' => is_numeric(wc_clean($wholesale_price['min_qty'])) ? wc_clean($wholesale_price['min_qty']) : 1,
						);
					}
		
					update_term_meta($cate_id, 'wholesale_multi_user_pricing', $data);
		
					$response_message = __('Successfully updated category discount.', 'woocommerce-wholesale-pricing');
					$response_status  = 200;
				}
		
				$response = array(
					'message' => $response_message,
				);
		
				return new WP_REST_Response($response, $response_status);
			}
		
			return new WP_Error('wholesale_rest_category_cannot_update', __('Category Cannot Update', 'woocommerce-wholesale-pricing'), array( 'status' => 400 ));
		}
		

		public function delete_item( $request ) {
		
			$cate_id  = $request->get_param('cate_id');
			$category = get_term_by('id', $cate_id, 'product_cat');

			if (!empty($cate_id) && !empty($category)) {
				 
				$result = wp_delete_term($cate_id , 'product_cat' );

				if (is_wp_error($result)) {
					$response_message = $result->get_error_message();
					$response_status  = 400;
				} else {
					$response_message = __('Successfully deleted category discount.', 'woocommerce-wholesale-pricing');
					$response_status  = 200;
				}

				$response = array(
					'message' => $response_message,
				);

				return new WP_REST_Response($response, $response_status);
			}

			return new WP_Error('wholesale_rest_category_cannot_delete', __('Category Cannot deleted.', 'woocommerce-wholesale-pricing'), array( 'status' => 400 ));
		}
	}

}
