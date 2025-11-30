<?php
/**
 * The admin settings page common utility functionalities.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-multiple-addresses-pro
 * @subpackage woocommerce-multiple-addresses-pro/src/admin
 */

namespace Themehigh\WoocommerceMultipleAddressesPro\admin;

use \Themehigh\WoocommerceMultipleAddressesPro\includes\utils\THWMA_Utils;

if(!defined('WPINC')) {
	die;
}

if(!class_exists('THWMA_Admin_Utils')) :

	/**
     * Admin Utils class.
     */
	class THWMA_Admin_Utils {
		protected static $_instance = null;

		/**
         * Instance function.
         *
         * @return void.
         */
    public static function instance() {
			if(is_null(self::$_instance)) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
         * Function for get sections.
         *
         * @return array.
         */
        public static function get_sections() {
			$sections = THWMA_Utils::get_custom_sections();
			if($sections && is_array($sections) && !empty($sections)) {
				return $sections;
			} else {
				$section = THWMA_Utils_Section::prepare_default_section();
				$sections = array();
				$sections[$section->get_property('name')] = $section;
				return $sections;
			}
		}

		/**
         * Function for get each section.
         *
         * @param string $section_name The section name.
         *
         * @return string/int.
         */
        public static function get_section($section_name) {
		 	if($section_name) {
				$sections = self::get_sections();
				if(is_array($sections) && isset($sections[$section_name])) {
					$section = isset($sections[$section_name]) ? $sections[$section_name] : '';
					if(THWMA_Utils_Section::is_valid_section($section)) {
						return $section;
					}
				}
			}
			return false;
		}

		/**
         * Function for save section.
         *
         * @param string $sections The section data.
         *
         * @return array.
         */
        public static function save_sections($sections) {
			$result = update_option(THWMA_Utils::OPTION_KEY_CUSTOM_SECTIONS, $sections);
			return $result;
		}

		/**
         * Function for load user roles.
         *
         * @return array.
         */
        public static function load_user_roles() {
			$user_roles = array();
			global $wp_roles;
	    	$roles = $wp_roles->roles;
			//$roles = get_editable_roles();
			if(!empty($roles) && is_array($roles)) {
				foreach($roles as $key => $role) {
					$user_roles[] = array(
						"id" => $key,
						"title" => isset($role['name']) ? $role['name'] : ''
					);
				}
			}
			return $user_roles;
		}

		/**
         * Function for get default sections.
         *
         * @return array.
         */
        public static function get_default_sections() {
			$sections = array(
				'billing_shipping' => esc_html__('Billing/Shipping', 'woocommerce-multiple-addresses-pro'),
				'multiple_shipping' => esc_html__('Multiple Shipping', 'woocommerce-multiple-addresses-pro'),
				//'store_pickup' => esc_html__('Store Pick-up', 'woocommerce-multiple-addresses-pro'), // Store pickup section hide.
				'guest_users' => esc_html__('Guest Users', 'woocommerce-multiple-addresses-pro'),
				'manage_style' => esc_html__('Manage Text', 'woocommerce-multiple-addresses-pro')
				//'manage_style' => esc_html__('Manage Style', 'woocommerce-multiple-addresses-pro')
			);
			return $sections;
		}

		/**
         * Function for prepare sections and fileds.
         *
         * @return void.
         */
        public static function prepare_sections_and_fields() {
			$sections = self::get_default_sections();
			$sections_data = array();
			if(!empty($sections) && is_array($sections)) {
				foreach($sections as $key => $value) {
					$sections_data[] = array(
						'id' => $key,
						'name' => $key,
						'order' => 1,
						'title' => $value
					);
				}
			}
			self::save_sections($sections_data);
		}
	}
endif;
