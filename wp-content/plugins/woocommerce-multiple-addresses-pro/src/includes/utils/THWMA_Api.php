<?php
/**
 * THWMA_Api class
 * Communicates with THWMA API.
 *
 * @link       https://themehigh.com
 * @since      2.1.3
 *
 * @package    woocommerce-multiple-addresses-pro
 * @subpackage woocommerce-multiple-addresses-pro/src/includes/utils
 */

namespace Themehigh\WoocommerceMultipleAddressesPro\includes\utils;

use WP_REST_Controller;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;
use Exception;


if(!defined('WPINC')) {
	die;
}

if(!class_exists('THWMA_Api')) :

	/**
     * The API class.
     */
	class THWMA_Api extends WP_REST_Controller{
		const VERSION = '1';
	    const NAMES = 'thwma/v'. self::VERSION;
	    const BASE = 'user';

		private $result = "";
		private $user_id = "";

		public function register_routes($data="") {
			register_rest_route( self::NAMES, self::BASE.'/(?P<user_id>\d+)/addresses',
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
				        'callback'            => array( $this, 'get_addresses' ),
				        'permission_callback' => array( $this, 'get_items_permissions_check' ),
				        'args'                => array(
				        	'user_id' => array(
				        		'required' => true,
				        	)
				        ),
				    ),
				    array(
						'methods'             => WP_REST_Server::CREATABLE,
				        'callback'            => array( $this, 'create_addresses' ),
				        'permission_callback' => array( $this, 'get_items_permissions_check' ),
				        'args'                => array(
				        	'user_id' => array(
				        		'required' => true,
				        	)
				        ),
				    ),
		    	)
			);

			register_rest_route( self::NAMES, self::BASE.'/(?P<user_id>\d+)/address/(?P<type>[a-zA-Z0-9-]+)',
				array(
					array(
				        'methods'             => WP_REST_Server::CREATABLE,
				        'callback'            => array( $this, 'create_address' ),
				        'permission_callback' => array( $this, 'create_item_permissions_check' ),
				        'args'                => array(
				        	'user_id' => array(
				        		'required' => true,
				        	),
				        	'type' => array(
				        		'required' => true,
				        	)
				        ),
		    		)
				)
			);

			register_rest_route( self::NAMES, self::BASE.'/(?P<user_id>\d+)/address/(?P<type>[a-zA-Z0-9-]+)',
				array(
					array(
			        'methods'             => WP_REST_Server::DELETABLE,
			        'callback'            => array( $this, 'delete_address' ),
			        'permission_callback' => array( $this, 'delete_item_permissions_check' ),
			        'args'                => array(
			          	'user_id' => array(
			        			'required' => true,
			        		),
			        	'type' => array(
			        			'required' => true,
			        		),
			        	'delete_id' => array(
			        			'required' => true,
			        		)
			        	),
			      	),
		    	)
			);
		}

		/**
		 * Retrieve address for the user.
		 */
		public function get_addresses( $request ) {
			try {
				$this->thwam_set_user($request, 'user_id');
				$this->thwam_get_addresses();

				$is_user_exist = get_user_by('id', $this->user_id);

				if ( ! $is_user_exist || !$this->result ) {
					return new WP_Error( 'thwma_rest_user_invalid_id', __( 'Invalid user ID.', 'woocommerce-multiple-addresses-pro' ), array( 'status' => 404 ) );
				}

				return new WP_REST_Response($this->result , 200 );

			}catch ( Exception $e ) {
				return new WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ) );
			}
			
		}

		/***
		 * save multiple addresses.
		 */
		public function create_addresses( $request ){
			try {
				$this->thwam_set_user($request, 'user_id');

				$is_user_exist = get_user_by('id', $this->user_id);
				$params = $request->get_params();
				if ( empty($is_user_exist) ) {
					return new WP_Error( 'thwma_rest_invalid_user_id', __( 'Invalid user ID.', 'woocommerce-multiple-addresses-pro' ), array( 'status' => 404 ) );
				}


				$check = $this->find_is_valid_section_key($params, 'shipping');
				if($check){
					return array('code' => $check->get_error_code(), 'message' => $check->get_error_message(),'data' =>  $check->get_error_data());
				}

				$this->update_multiple_addresses($params['shipping'], 'shipping');

				return new WP_REST_Response(" addresses has been successfully added." , 200 );

			}catch ( Exception $e ) {
				return new WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ) );
			}

		}		

		/**
		 * save one address.
		 */
		public function create_address( $request ) {
			try {
				$this->thwam_set_user($request, 'user_id');

				$is_user_exist = get_user_by('id', $this->user_id);
				$params = $request->get_params();
				if ( empty($is_user_exist) ) {
					return new WP_Error( 'thwma_rest_invalid_user_id', __( 'Invalid user ID.', 'woocommerce-multiple-addresses-pro' ), array( 'status' => 404 ) );
				}

				// check argument is valid `type`.
				if ( $params["type"] != "billing" &&  $params["type"] != "shipping"  ) {
					return new WP_Error( 'thwma_rest_user_invalid_type', __( 'The type specified is not valid; it should be either "billing" or "shipping."', 'woocommerce-multiple-addresses-pro' ), array( 'status' => 404 ) );
				}

				$field_keys = THWMA_Utils::get_address_fields($params["type"]);
				$field_keys_check = $this->find_is_valid_field_key($field_keys, $params);
				if ($field_keys_check ==  'empty_array') {
					return new WP_Error( 'thwma_rest_invalid_body', __( 'Invalid requist body', 'woocommerce-multiple-addresses-pro' ), array( 'status' => rest_authorization_required_code() ) );
				}elseif(!empty($field_keys_check)){
					return new WP_Error( 'thwma_rest_user_invalid_key', __( "Invalid keys: " . implode(', ', $field_keys_check ), 'woocommerce-multiple-addresses-pro' ), array( 'status' => 404 ) );
				}

				// update the address.
				THWMA_Utils::save_address_to_user($this->user_id, $params, $params["type"]);

				return new WP_REST_Response($params["type"]." address has been successfully added." , 200 );

			}catch ( Exception $e ) {
				return new WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ) );
			}
			
		}

		/**
		 * Delete an address from a user.
		 */
		public function delete_address( $request ) {
			try {
				$this->thwam_set_user($request, 'user_id');
				$this->thwam_get_addresses();

				$is_user_exist = get_user_by('id', $this->user_id);
				$params = $request->get_params();

				if ( ! $is_user_exist || !$this->result ) {
					return new WP_Error( 'thwma_rest_user_invalid_id', __( 'Invalid user ID.', 'woocommerce-multiple-addresses-pro' ), array( 'status' => 404 ) );
				}

				// check argument is valid `type`.
				if ( $params["type"] != "billing" &&  $params["type"] != "shipping"  ) {
					return new WP_Error( 'thwma_rest_user_invalid_type', __( 'The type specified is not valid; it should be either "billing" or "shipping."', 'woocommerce-multiple-addresses-pro' ), array( 'status' => 404 ) );
				}

				$check_delete_id =  THWMA_Utils::check_key_of_address_type($this->user_id, $params["type"], $params["delete_id"]);
				// check argument is valid `delete_id`.
				if ( !$check_delete_id) {
					return new WP_Error( 'thwma_rest_invalid_delete_id', __( 'Invalid Delete key', 'woocommerce-multiple-addresses-pro' ), array( 'status' => 404 ) );
				}

				
				$deleted = THWMA_Utils::delete($this->user_id, $params["type"], $params["delete_id"]);

				return new WP_REST_Response("Deleted successfully: " . $params["delete_id"] , 200 );

			}catch ( Exception $e ) {
				return new WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ) );
			}
			
		}



		/**
		 * Set API result.
		 */
		private function thwam_get_addresses() {
			$this->result = get_user_meta($this->user_id, THWMA_Utils::ADDRESS_KEY, true);
		}

		/**
		 * Set API parameters.
		 */
		private function thwam_set_user($request ,$key) {
			$this->user_id = $request->get_param( $key );
		}


		//////////////////////////////// REST API HELPER FUNCTION - START ///////////////////////////////

		/**
		 * Check whether a given request has permission to read customers.
		 *
		 * @param  WP_REST_Request $request Full details about the request.
		 * @return WP_Error|boolean
		 */
		public function get_items_permissions_check( $request ) {
			if ( ! wc_rest_check_user_permissions( 'read' ) ) {
				return new WP_Error( 'thwma_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'woocommerce-multiple-addresses-pro' ), array( 'status' => rest_authorization_required_code() ) );
			}
			return true;
		}

		/**
		 * Check if a given request has access to create a new address.
		 *
		 * @param  WP_REST_Request $request Full details about the request.
		 * @return WP_Error|boolean
		 */
		public function create_item_permissions_check( $request ) {
			if ( ! wc_rest_check_product_reviews_permissions( 'create' ) ) {
				return new WP_Error( 'thwma_rest_cannot_create', __( 'Sorry, you are not allowed to create resources.', 'woocommerce-multiple-addresses-pro' ), array( 'status' => rest_authorization_required_code() ) );
			}

			return true;
		}

		/**
		 * Check if a given request has access delete a address.
		 *
		 * @param  WP_REST_Request $request Full details about the request.
		 *
		 * @return bool|WP_Error
		 */
		public function delete_item_permissions_check( $request ) {
			if ( ! wc_rest_check_user_permissions( 'delete' ) ) {
				return new WP_Error( 'woocommerce_rest_cannot_delete', __( 'Sorry, you are not allowed to delete this resource.', 'woocommerce-multiple-addresses-pro' ), array( 'status' => rest_authorization_required_code() ) );
			}

			return true;
		}

		/**
		 * Check the requisted body of an api.
		 * here checking the field name
		 *
		 * @param $expectedKey the section field keys.
		 * @param $actualData the api request params.
		 * @return The invalid keys|boolean
		 */
		private function find_is_valid_field_key($expectedKeys, $actualData) {
			if(isset($actualData["type"])) {//TODO: update me later
				unset($actualData["type"]);
			}elseif(isset($actualData["user_id"])){
				unset($actualData["user_id"]);
			}

			if(!empty($actualData) && is_array($actualData)){
			    $invalidKeys = array_diff(array_keys($actualData), $expectedKeys);
			    return $invalidKeys;
		    }
		    return 'empty_array';
		}

		private function find_is_valid_section_key($params, $field){
			if(is_array($params) && array_key_exists($field , $params)){
				if(is_array($params[$field]) && !empty($params[$field])){
					$field_keys = THWMA_Utils::get_address_fields($field);

					foreach ($params[$field] as  $value) {
						$field_keys_check = $this->find_is_valid_field_key($field_keys, $value);
					}
					if('empty_array' === $field_keys_check){
						return new WP_Error( 'thwma_rest_invalid_body', __( 'Invalid requist body', 'woocommerce-multiple-addresses-pro' ), array( 'status' => rest_authorization_required_code() ) );
					}elseif(!empty($field_keys_check)){
						return new WP_Error( 'thwma_rest_invalid_key', __( "Invalid keys: " . implode(', ', $field_keys_check ), 'woocommerce-multiple-addresses-pro' ), array( 'status' => rest_authorization_required_code() ) );
					}
					return false;
				}
				return new WP_Error( 'thwma_rest_invalid_key', __( "Something is missing in the requist body, should follow the JSON format like '[field_name] : {...}'", 'woocommerce-multiple-addresses-pro' ), array( 'status' => rest_authorization_required_code() ) );
			}
			return new WP_Error( 'thwma_rest_invalid_key', __( "Missing required keys 'shipping' ", 'woocommerce-multiple-addresses-pro' ), array( 'status' => rest_authorization_required_code() ) );
		}

		public function update_multiple_addresses($object, $type="shipping"){
			if(is_array($object)){
				foreach ($object as $value) {
					THWMA_Utils::save_address_to_user($this->user_id, $value, $type);
				}
			}
		}


		//////////////////////////////// REST API HELPER FUNCTION - END //////////////////////////////////


	}
endif;
