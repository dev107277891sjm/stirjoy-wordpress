<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('WWP_REST_Wholesale_Roles_V1_Controller')) {

	class WWP_REST_Wholesale_Roles_V1_Controller extends WC_REST_Controller {
	
		protected $namespace = 'wholesale/v1';
		protected $rest_base = 'roles';
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
			$all_terms = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
			/**
			 * Hooks
			 *
			 * @since 2.4.0
			 */     
			$wholesale_roles = apply_filters('wwp_api_fetch_wholesale_role_filter', $all_terms, $request);
			return new WP_REST_Response($wholesale_roles, 200);
		}

		public function get_item( $request ) {
			$role_key       = $request->get_param('role_key');
			$wholesale_role = term_exists($role_key, 'wholesale_user_roles');
		
			if (!empty($role_key) && !empty($wholesale_role)) {
			
				$role = get_term_by('id', $wholesale_role['term_id'], 'wholesale_user_roles');
				/**
				 * Hooks
				 *
				 * @since 2.4.0
				 */ 
				$wholesale_role = apply_filters('wwp_api_fetch_wholesale_role_filter', $role, $request);
				return new WP_REST_Response($wholesale_role, 200);
			}

			return new WP_Error('wholesale_rest_cannot_view', __('Item not found.', 'woocommerce-wholesale-pricing'), array( 'status' => 400 ));
		}
		
		public function create_item( $request ) {
			if ( isset( $request['wholesale_role'] )  ) {
				$request_data = $request->get_param('wholesale_role');
				$result       = wp_insert_term($request_data['name'] , 'wholesale_user_roles', $request_data);
				if (is_wp_error($result)) {
					$response_message = $result->get_error_message();
					$response_status  = 400;
				} else {
					// translators: %s: wholesale role name.
					$response_message = sprintf(__('Wholesale Role %s has been Created.', 'woocommerce-wholesale-pricing'), $request_data['name']);
					$response_status  = 200;
				}

				$response = array(
					'message' => $response_message,
				);

				return new WP_REST_Response($response, $response_status);
			}
			return new WP_Error('wholesale_rest_role_cannot_update', __('Item not found.', 'woocommerce-wholesale-pricing'), array( 'status' => 400 ));
		}

		public function update_item( $request ) {
			$role_key       = $request->get_param('role_key');
			$wholesale_role = term_exists($role_key, 'wholesale_user_roles');

			if (!empty($role_key) && !empty($wholesale_role)) {
				$request_data = $request->get_param('wholesale_role');
				$result       = wp_update_term($wholesale_role['term_id'], 'wholesale_user_roles', $request_data);

				$term = get_term( $wholesale_role['term_id'], 'wholesale_user_roles' );
				if ( ! wp_roles()->is_role( $term->slug ) ) {
					add_role(
						$term->slug,
						$term->name . esc_html__( ' - Wholesaler role', 'woocommerce-wholesale-pricing' ),
						array(
							'read'    => true,
							'level_0' => true,
						)
					);
				}

				if (is_wp_error($result)) {
					$response_message = $result->get_error_message();
					$response_status  = 400;
				} else {
					// translators: %s: wholesale role name.
					$response_message = sprintf(__('Wholesale Role %s has been updated.', 'woocommerce-wholesale-pricing'), $role_key);
					$response_status  = 200;
				}

				$response = array(
					'message' => $response_message,
				);

				return new WP_REST_Response($response, $response_status);
			}

			return new WP_Error('wholesale_rest_role_cannot_update', __('Item not found.', 'woocommerce-wholesale-pricing'), array( 'status' => 400 ));
		}

		public function delete_item( $request ) {
		
			$role_key       = $request->get_param('role_key');
			$wholesale_role = term_exists($role_key, 'wholesale_user_roles');

			if (!empty($role_key) && !empty($wholesale_role)) {
				 
				$result = wp_delete_term($wholesale_role['term_id'], 'wholesale_user_roles' );

				if (is_wp_error($result)) {
					$response_message = $result->get_error_message();
					$response_status  = 400;
				} else {
					// translators: %s: wholesale role name.
					$response_message = sprintf(__('Wholesale Role %s has been Deleted.', 'woocommerce-wholesale-pricing'), $role_key);
					$response_status  = 200;
				}

				$response = array(
					'message' => $response_message,
				);

				return new WP_REST_Response($response, $response_status);
			}

			return new WP_Error('wholesale_rest_role_cannot_delete', __('Item not found.', 'woocommerce-wholesale-pricing'), array( 'status' => 400 ));
		}
	}

}
