<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('WWP_REST_Wholesale_Settings_V1_Controller')) {

	class WWP_REST_Wholesale_Settings_V1_Controller extends WC_REST_Controller {
	
		protected $namespace = 'wholesale/v1';
		protected $rest_base = 'general-discount';
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
				'/' . $this->rest_base . '/(?P<role_key>[a-z0-9_-]*)?',
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
			$settings = get_option('wholesale_multi_user_pricing', array());
			
			if (!empty($settings)) {
				$pre = array_map(function ( $key, $value ) {
					$value['role-id'] = $key;
					return $value;
				}, array_keys($settings), $settings);
			}
			
			/**
			 * Hooks
			 *
			 * @since 2.4.0
			 */
			$wholesale_settings = apply_filters('wwp_api_fetch_wholesale_settings_filter', $pre, $request);
		
			return new WP_REST_Response($wholesale_settings, 200);
		}
		

		public function get_item( $request ) {
			$role_key = $request->get_param('role_key');
			$settings = get_option('wholesale_multi_user_pricing', array());
			if (!empty($settings)) {
				$wholesale_role = term_exists($role_key, 'wholesale_user_roles');
				if ( isset( $settings[ $wholesale_role['term_id'] ] ) ) {
					$settings            = $settings[ $wholesale_role['term_id'] ];
					$settings['role-id'] = $wholesale_role['term_id'];
				}
			}
			/**
			 * Hooks
			 *
			 * @since 2.4.0
			 */     
			$wholesale_settings = apply_filters('wwp_api_fetch_wholesale_settings_filter', $settings, $request);
			return new WP_REST_Response($wholesale_settings, 200);
		}
		
		public function create_item( $request ) {
			$params   = $request->get_params();
			$settings = get_option('wholesale_multi_user_pricing', array());
		
			$wholesale_role = term_exists($params['slug'], 'wholesale_user_roles');
			if (!empty($wholesale_role)) {
				$settings[$wholesale_role['term_id']] = array(
					'slug' => $params['slug'],
					'discount_type' => $params['discount_type'],
					'wholesale_price' => $params['wholesale_price'],
					'min_quatity' => $params['min_quatity'],
				);
				update_option('wholesale_multi_user_pricing', $settings);
		
				$response_message = __('Successfully created discount.', 'woocommerce-wholesale-pricing');
				$response_status  = 200;
			} else {
				$response_message = __('Role not exist', 'woocommerce-wholesale-pricing');
				$response_status  = 400;
			}
		
			$response = array(
				'message' => $response_message,
			);
			return new WP_REST_Response($response, $response_status);
		}
		
		public function update_item( $request ) {
			$role_key = $request->get_param('role_key');
			$params   = $request->get_params();
			$settings = get_option('wholesale_multi_user_pricing', array());
		
			$wholesale_role = term_exists($role_key, 'wholesale_user_roles');
			if (!empty($wholesale_role)) {
				$settings[$wholesale_role['term_id']] = array(
					'slug' => $role_key,
					'discount_type' => $params['discount_type'],
					'wholesale_price' => $params['wholesale_price'],
					'min_quatity' => $params['min_quatity'],
				);
				update_option('wholesale_multi_user_pricing', $settings);
		
				$response_message = __('Successfully updated discount.', 'woocommerce-wholesale-pricing');
				$response_status  = 200;
			} else {
				$response_message = __('Role not exist', 'woocommerce-wholesale-pricing');
				$response_status  = 400;
			}
		
			$response = array(
				'message' => $response_message,
			);
			return new WP_REST_Response($response, $response_status);
		}
		
		public function delete_item( $request ) {
			$role_key       = $request->get_param('role_key');
			$settings       = get_option('wholesale_multi_user_pricing', array());
			$wholesale_role = term_exists($role_key, 'wholesale_user_roles');
			if (!empty($wholesale_role)) {
				unset( $settings[$wholesale_role['term_id']]);
				update_option('wholesale_multi_user_pricing', $settings);
				$response_message = __('Successfully deleted discount.', 'woocommerce-wholesale-pricing');
				$response_status  = 200;
			} else {
				$response_message = __('Role not exist', 'woocommerce-wholesale-pricing');
				$response_status  = 400;
			}
			$response = array(
				'message' => $response_message,
			);
			return new WP_REST_Response($response, $response_status);
		}
	}
}
