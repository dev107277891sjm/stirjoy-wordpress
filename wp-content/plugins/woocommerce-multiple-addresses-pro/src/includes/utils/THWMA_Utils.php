<?php
/**
 * The common utility functionalities for the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-multiple-addresses-pro
 * @subpackage woocommerce-multiple-addresses-pro/src/includes/utils
 */

namespace Themehigh\WoocommerceMultipleAddressesPro\includes\utils;

if(!defined('WPINC')) {
	die;
}

if(!class_exists('THWMA_Utils')) :

	/**
     * The Utils class.
     */
	class THWMA_Utils {
		const THWMA_TEXT_DOMAIN 	='thwma';
		const SUB_ORDER           	= '_thwma_suborder';
		const SUB_ORDERS           	= '_thwma_suborders';
		const SUB_ORDER_FAKE_ID    	= '_thwma_sub_order_fake_id';
		const LAST_SUBORDER_SUB_ID 	= '_thwma_last_suborder_sub_id';
		const OPTION_QUANTITY_MIN  	= '_thwma_option_quantity_min';
		const IS_SUB_ORDER  		= '_thwma_is_sub_order';
		const PARENT_ORDER  		= '_thwma_parent_order';
		const SUB_ORDER_SUB_ID  	= '_thwma_sub_order_sub_id';
		const SORT_ID  				= '_thwma_sort_id';
		const PARENT_ORDER_ITEM  	= '_thwma_parent_order_item';
		const REMAINING 			= '_thwma_remaining';
		const MAIN_ORDER_ID         ='_thwma_main_order_id';
		const DELETING              = '_thwma_deleting';
		const OPTION_DEFAULT_MAIN_ORDER_STATUS = 'thwma_default_main_order_status';

		const ADDRESS_KEY 					= 'thwma_custom_address';
		const OPTION_KEY_CUSTOM_SECTIONS 	= 'thwma_custom_sections';
		const OPTION_KEY_THWMA_SETTINGS 	= 'thwma_general_settings';
		const OPTION_KEY_ADVANCED_SETTINGS 	= 'thwma_advanced_settings';
		const OPTION_KEY_SECTION_SETTINGS 	= 'thwma_custom_section_map';
		const ORDER_KEY_SHIPPING_ADDR 		= 'thwma_order_shipping_address';
		const ORDER_KEY_SHIPPING_DATA 		= 'thwma_order_shipping_data';
		const ORDER_KEY_SHIPPING_STATUS		= 'thwma_order_shipping_status';

		//const OPTION_KEY_SHIPPING_METHOD 	= 'thwma_option_shipping_method';
		const ORDER_KEY_SHIPPING_METHOD 	= 'thwma_order_shipping_method';
		//const ORDER_KEY_SHIPPING_CUS_FIELDS = 'thwma_order_shipping_cus_fields';

		//const OPTION_KEY_ENABLE_MULTI_SHIP 	= 'thwma_enable_multi_shipping';
		const USER_META_ENABLE_MULTI_SHIP 	= 'thwma_enable_multi_shipping';
		//const OPTION_KEY_MULTI_SHIP 		= 'thwma_multi_shipping';
		const GUEST_KEY_ENABLE_MULTI_SHIP 	= 'thwma_guest_enable_multi_shipping';
		const GUEST_USER_SHIPPING_ADDR      = 'guest_user_shipping_address';
		//const OPTION_KEY_SECTION_HOOK_MAP='thwma_section_hook_map';
		//const OPTION_KEY_NAME_TITLE_MAP='thwma_name_title_map';

		/**
         * Function for get advanced settings.
         *
         * @return array.
         */
        public static function get_advanced_settings() {
			$settings = get_option(self::OPTION_KEY_ADVANCED_SETTINGS);
			return empty($settings) ? false : $settings;
		}

		/**
         * Function for get settings value.
         *
         * @param string $key The setting key data.
         * @param string $sections The section info.
         * @param array $settings The settings details.
         *
         * @return string.
         */
    public static function get_setting_value($key, $sections=false, $settings=false) {
			if(!$settings) {
				$settings = self::get_general_settings($key);
			}
			if(is_array($settings) && isset($settings[$key])) {
				if($sections) {
					return isset($settings[$key][$sections]) ? $settings[$key][$sections] : '';
				}
				return $settings[$key];
			}
			return '';
		}

		/**
         * Function for get settings value of local pickup.
         *
         * @param string $key The setting key data.
         * @param string $sections The section info.
         * @param string $name The field name.
         * @param array $settings The settings details.
         *
         * @return string.
         */
        public static function get_setting_value_local_pickup($key, $name, $sections=false, $settings=false) {
			if(!$settings) {
				$settings = self::get_general_settings($key);
			}
			if(is_array($settings) && isset($settings[$key])) {
				if($sections) {
					if(strpos($sections, 'local_pickup_') !== false) {
						if (isset($settings[$key][$name])) {
							return $settings[$key][$name][$sections];
						}
					} else {
						return $settings[$key][$sections];
					}
				}
				return $settings[$key];
			}
			return '';
		}

		/**
         * Function for get advanced settings value.
         *
         * @param string $key The key data.
         *
         * @return string.
         */
  	public static function get_advanced_settings_value($key) {
			$settings = self::get_advanced_settings();
			if(is_array($settings) && isset($settings[$key])) {
				return $settings[$key];
			}
			return '';
		}

		/**
         * Function for get mapped sections settings value.
         *
         * @param string $section The section data.
         * @param string $key The settings key.
         *
         * @return array.
         */
        public static function get_maped_sections_settings_value($section, $key=false) {
			$settings = self::get_custom_section_settings();
			if($settings) {
				$section_values = isset($settings[$section]) ? $settings[$section] : '';
				if($key) {
					$maped_fields = array();
					if(isset($section_values[$key])) {
						$maped_fields = isset($section_values[$key]) ? $section_values[$key] : '';
					}
					return $maped_fields;
				}
				return $section_values;
			}
			return false;
		}

		/**
         * Function for get custom sections.
         *
         * @return string/int.
         */
        public static function get_custom_sections() {
			$sections = get_option(self::OPTION_KEY_CUSTOM_SECTIONS);
			return empty($sections) ? false : $sections;
		}

		/**
         * Function for get custom sections data.
         *
         * @return array.
         */
        public static function get_custom_section_settings() {
			$map_sections = get_option(self::OPTION_KEY_SECTION_SETTINGS,true);
			return is_array($map_sections) ? $map_sections : false;
		}

		/**
         * Function for the basic default settings.
         *
         * @return array.
         */
        public static function basic_default_settings() {
			$limit = apply_filters('thwma_multiple_address_limit', 20);
			$basic_default_settings = array(
				'settings_billing' =>array(
					'enable_billing'=>'yes',
					'billing_display'=>'popup_display',
					'billing_display_position'=>'above',
					'billing_display_title'=>'link',
					'billing_display_text'=> esc_html__('Billing with a different address', 'woocommerce-multiple-addresses-pro'),
					'billing_address_limit' =>$limit
				),
				'settings_shipping'=>array(
					'enable_shipping'=>'yes',
					'shipping_display'=>'popup_display',
					'shipping_display_position'=>'above',
				 	'shipping_display_title'=>'link',
					'shipping_display_text'=>esc_html__('Shipping to a different address', 'woocommerce-multiple-addresses-pro'),
					'shipping_address_limit' =>$limit
				),
				'settings_styles'=>array(
					'enable_button_styles'=>'no',
					'button_background_color'=>'#000',
					'button_text_color'=>'#fff',
					'button_padding'=>'auto'
				),
				'settings_multiple_shipping'=>array(
					'enable_cart_shipping'=>'yes',
					'exclude_products'=>'',
	                'hidden_ex_pdts_list'=>'',
					'exclude_category'=>'',
	                'hidden_ex_catg_list'=>'',
					'enable_product_variation'=>'yes',
					'order_shipping_status'=>'yes',
					'enable_product_disticty'=>'yes',
					'handling_fee'=>'yes',
					'shop_page_reload'=>'yes',
					'handling_fee'=>'yes',
					'notification_email'=>'yes'
				),
				'settings_store_pickup'=>array(
					'enable_multi_store_pickup'=>'yes',
					'store_addresses'=>array(
						'local_pickup_address1_1' => '',
						'local_pickup_address2_1'=> '',
						'local_pickup_city_1' => '',
						'local_pickup_country_1' => '',
						'local_pickup_postcode_1' => ''),
					'store_addresses'=>array(
						'local_pickup_address1_2' => '',
						'local_pickup_address2_2'=> '',
						'local_pickup_city_2' => '',
						'local_pickup_country_2' => '',
						'local_pickup_postcode_2' => '')
				),
				'settings_guest_users'=>array(
					'enable_guest_shipping'=>'yes',
					'set_time_duration'=>'10',
					'time_limit'=>'minute'
				),
				'settings_manage_style'=>array(
					'multi_address_url'=>'Ship to different address',
					'multi_address_button'=>'Show saved shipping addresses',
					'multi_shipping_checkbox_label'=>'Do you want to ship to multiple addresses?'
				)
			);
			return $basic_default_settings;
		}

		/**
         * Function for the default settings.
         *
         * @param string $key The settings section key.
         *
         * @return array.
         */
        public static function default_settings($key='') {
			$limit = apply_filters('thwma_multiple_address_limit', 20);
			$default_settings_general = array();
			$default_settings_multi  = array();
			$default_settings_store = array();
			$default_settings_guest = array();
			$default_settings_style = array();
			if($key == 'settings_billing' || $key == 'settings_shipping' || $key == 'settings_styles') {
				$default_settings_general = array(
					'settings_billing' =>array(
						'enable_billing'=>'yes',
						'billing_display'=>'popup_display',
						'billing_display_position'=>'above',
						'billing_display_title'=>'link',
						'billing_display_text'=> esc_html__('Billing with a different address', 'woocommerce-multiple-addresses-pro'),
						'billing_address_limit' =>$limit),
					'settings_shipping'=>array(
						'enable_shipping'=>'yes',
						'shipping_display'=>'popup_display',
						'shipping_display_position'=>'above',
					 	'shipping_display_title'=>'link',
						'shipping_display_text'=>esc_html__('Shipping to a different address', 'woocommerce-multiple-addresses-pro'),
						'shipping_address_limit' =>$limit),
					'settings_styles'=>array(
						'enable_button_styles'=>'no',
						'button_background_color'=>'#000',
						'button_text_color'=>'#fff',
						'button_padding'=>'auto'
					)
				);
			} else if($key == 'settings_multiple_shipping') {
				$default_settings_multi = array(
					'settings_multiple_shipping'=>array(
						'enable_cart_shipping'=>'yes',
						'exclude_products'=>'',
	                    'hidden_ex_pdts_list'=>'',
						'exclude_category'=>'',
	                    'hidden_ex_catg_list'=>'',
						'enable_product_variation'=>'yes',
						'order_shipping_status'=>'yes',
						'enable_product_disticty'=>'yes',
						'handling_fee'=>'yes',
						'shop_page_reload'=>'yes',
						'handling_fee'=>'yes',
						'notification_email'=>'yes')
				);
			} else if($key == 'settings_store_pickup') {
				$default_settings_store = array(
					'settings_store_pickup'=>array(
						'enable_multi_store_pickup'=>'yes',
						'store_addresses'=>array(
							'local_pickup_address1_1' => '',
							'local_pickup_address2_1'=> '',
							'local_pickup_city_1' => '',
							'local_pickup_country_1' => '',
							'local_pickup_postcode_1' => ''),
							'store_addresses'=>array(
							'local_pickup_address1_2' => '',
							'local_pickup_address2_2'=> '',
							'local_pickup_city_2' => '',
							'local_pickup_country_2' => '',
							'local_pickup_postcode_2' => '')
					)
						//'section_store_address'=>''),
				);
			} else if($key == 'settings_guest_users') {
				$default_settings_guest = array(
					'settings_guest_users'=>array(
						'enable_guest_shipping'=>'yes',
						'set_time_duration'=>'10',
						'time_limit'=>'minute')
				);
			} else if($key == 'settings_manage_style') {
				$default_settings_style = array(
					'settings_manage_style'=>array(
						'multi_address_url'=>'Ship to different address',
						'multi_address_button'=>'Show saved shipping addresses',
						'multi_shipping_checkbox_label'=>'Do you want to ship to multiple addresses?')
				);
			}
			$default_settings = array_merge($default_settings_general, $default_settings_multi, $default_settings_store, $default_settings_guest, $default_settings_style);

			return $default_settings;
		}

		/**
         * Function for the general default settings.
         *
         * @param string $key The section key.
         *
         * @return array.
         */
    public static function general_default_settings($key) {
			$limit = apply_filters('thwma_multiple_address_limit', 20);
			$default_settings_general = array();
			$default_settings_multi  = array();
			$default_settings_store = array();
			$default_settings_guest = array();
			$default_settings_style = array();
			if($key == 'settings_billing' || $key == 'settings_shipping' || $key == 'settings_styles') {
				$default_settings_general = array(
					'settings_billing' =>array(
						'enable_billing'=>'yes',
						'billing_display'=>'popup_display',
						'billing_display_position'=>'above',
						'billing_display_title'=>'link',
						'billing_display_text'=> esc_html__('Billing with a different address', 'woocommerce-multiple-addresses-pro'),
						'billing_address_limit' =>$limit),
					'settings_shipping'=>array(
						'enable_shipping'=>'yes',
						'shipping_display'=>'popup_display',
						'shipping_display_position'=>'above',
					 	'shipping_display_title'=>'link',
						'shipping_display_text'=>esc_html__('Shipping to a different address', 'woocommerce-multiple-addresses-pro'),
						'shipping_address_limit' =>$limit),
					'settings_styles'=>array(
						'enable_button_styles'=>'yes',
						'button_background_color'=>'#000',
						'button_text_color'=>'#fff',
						'button_padding'=>'auto'
					)
				);
			} else if($key == 'settings_multiple_shipping') {
				$default_settings_multi = array(
					'settings_multiple_shipping'=>array(
						'enable_cart_shipping'=>'yes',
						'exclude_products'=>'',
	                    'hidden_ex_pdts_list'=>'',
						'exclude_category'=>'',
	                    'hidden_ex_catg_list'=>'',
						'enable_product_variation'=>'yes',
						'order_shipping_status'=>'yes',
						'enable_product_disticty'=>'yes',
						'handling_fee'=>'yes',
						'shop_page_reload'=>'yes',
						'handling_fee'=>'yes',
						'notification_email'=>'yes')
				);
			} else if($key == 'settings_store_pickup') {
				$default_settings_store = array(
					'settings_store_pickup'=>array(
						'enable_multi_store_pickup'=>'yes',
						'store_addresses'=>array(
							'local_pickup_address1_1' => '',
							'local_pickup_address2_1'=> '',
							'local_pickup_city_1' => '',
							'local_pickup_country_1' => '',
							'local_pickup_postcode_1' => ''),
						'store_addresses'=>array(
							'local_pickup_address1_2' => '',
							'local_pickup_address2_2'=> '',
							'local_pickup_city_2' => '',
							'local_pickup_country_2' => '',
							'local_pickup_postcode_2' => '')
					)
						//'section_store_address'=>''),
				);
			} else if($key == 'settings_guest_users') {
				$default_settings_guest = array(
					'settings_guest_users'=>array(
						'enable_guest_shipping'=>'yes',
						'set_time_duration'=>'10',
						'time_limit'=>'minute')
				);
			} else if($key == 'settings_manage_style') {
				$default_settings_style = array(
					'settings_manage_style'=>array(
						'multi_address_url'=>'Ship to different address',
						'multi_address_button'=>'Show saved shipping addresses',
						'multi_shipping_checkbox_label'=>'Do you want to ship to multiple addresses?')
				);
			}

			//$default_settings = array_merge($default_settings_general,$default_settings_multi,$default_settings_store,$default_settings_guest,$default_settings_style);

			//return $default_settings;
			$settings = get_option(self::OPTION_KEY_THWMA_SETTINGS);
			if(!empty($settings)) {
				if(isset($settings['settings_billing'])) {
					$stg_bill = array(
						'settings_billing' => $settings['settings_billing']
					);
				} else {
					$stg_bill = array(
						'settings_billing' => isset($default_settings_general['settings_billing']) ? $default_settings_general['settings_billing'] : array()
					);
				}
				if(isset($settings['settings_shipping'])) {
					$stg_ship = array(
						'settings_shipping' => $settings['settings_shipping']
					);
				} else {
					$stg_ship = array(
						'settings_shipping' => isset($default_settings_general['settings_shipping']) ? $default_settings_general['settings_shipping'] : array()
					);
				}
				if(isset($settings['settings_styles'])) {
					$stg_styl = array(
						'settings_styles' => $settings['settings_styles']
					);
				} else {
					$stg_styl = array(
						'settings_styles' => isset($default_settings_general['settings_styles']) ? $default_settings_general['settings_styles'] : array()
					);
				}
				if(isset($settings['settings_multiple_shipping'])) {
					$stg_multi_ship = array(
						'settings_multiple_shipping' => $settings['settings_multiple_shipping']
					);
				} else {
					$stg_multi_ship = $default_settings_multi;
				}
				if(isset($settings['settings_store_pickup'])) {
					$stg_store = array(
						'settings_store_pickup' => $settings['settings_store_pickup']
					);
				} else {
					$stg_store = $default_settings_store;
				}
				if(isset($settings['settings_guest_users'])) {
					$stg_guest = array(
						'settings_guest_users' => $settings['settings_guest_users']
					);
				} else {
					$stg_guest = $default_settings_guest;
				}
				if(isset($settings['settings_manage_style'])) {
					$stg_style = array(
						'settings_manage_style' => $settings['settings_manage_style']
					);
				} else {
					$stg_style = $default_settings_style;
				}

				$current_settings = array_merge($stg_bill, $stg_ship, $stg_styl, $stg_multi_ship, $stg_store, $stg_guest, $stg_style);
			} else {
				$current_settings = self::basic_default_settings();
			}
			return $current_settings;
		}

		/**
         * Function for get general settings.
         *
         * @param string $key The section key.
         *
         * @return array.
         */
        public static function get_general_settings($key) {
			// $default_settings = self::default_settings($key);
			// $settings = get_option(self::OPTION_KEY_THWMA_SETTINGS);
			// return empty($settings) ? $default_settings : $settings;

			$current_settings = self::general_default_settings($key);
			return $current_settings;
		}

		/**
         * Function for reset to default section.
         *
         * @param string $section_name The section name.
         * @param string $all The all data.
         *
         * @return array.
         */
        public static function reset_to_default_section($section_name, $all=false) {
			$settings = false;
			$all = apply_filters('thwma_clear_plugin_settings', $all);
			$section_key = 'settings_'.$section_name;
			//$settings_exist = get_option(self::OPTION_KEY_THWMA_SETTINGS);
			$settings_default = self::get_default_settings();
			if($all) {
				$settings = delete_option(self::OPTION_KEY_THWMA_SETTINGS);
			} else {
				$settings = get_option(self::OPTION_KEY_THWMA_SETTINGS);
				$new_settings = self::default_settings($section_key);
				if($section_key == 'settings_billing_shipping') {
					if($section_name && isset($settings['settings_billing'])) {
						$bill_settings = self::default_settings('settings_billing');
						$settings['settings_billing'] = isset($bill_settings['settings_billing']) ? $bill_settings['settings_billing'] : "";
					}
					if($section_name && isset($settings['settings_shipping'])) {
						$ship_settings = self::default_settings('settings_shipping');
						$settings['settings_shipping'] = isset($ship_settings['settings_shipping']) ? $ship_settings['settings_shipping'] : "";
					}
					if($section_name && isset($settings['settings_styles'])) {
						$style_settings = self::default_settings('settings_styles');
						$settings['settings_styles'] = isset($style_settings['settings_styles']) ? $style_settings['settings_styles'] : "";
					}
				} else {
					if($section_name && isset($settings[$section_key])) {
						foreach($settings as $key => $data) {
							if($key == $section_key) {
								$settings[$section_key] = isset($new_settings[$section_key]) ? $new_settings[$section_key] : "";

							} else {
								$settings[$key] = isset($settings[$key]) ? $settings[$key] : "";
							}
						}
					} else {
						$settings = isset($new_settings) ? $new_settings : "";
					}
				}
				$settings = update_option(THWMA_Utils::OPTION_KEY_THWMA_SETTINGS,$settings);
			}
			return $settings;
		}

		/**
         * Function for get default settings.
         *
         * @param string $tab The tab name.
         * @param string $activation The activation info.
         *
         * @return array.
         */
        public static function get_default_settings($tab=false, $activation = false) {
			$settings = get_option(self::OPTION_KEY_THWMA_SETTINGS);
			if(!$activation) {
				if(empty($settings)) {
					$settings = self::default_settings();
				}
				if($tab) {
					$settings = isset($settings[$tab]) ? $settings[$tab] : "";
				}
			}
			return empty($settings) ? array() : $settings;
		}

		/**
         * Function for get address fields by using address key(Address).
         *
         * @param string $type The address type.
         *
         * @return array.
         */
        public static function get_address_fields_by_address_key($type) {
	    	$fields = WC()->countries->get_address_fields(WC()->countries->get_base_country(), $type);
	    	$fields_keys = array();
	    	if(!empty($fields) && is_array($fields)) {
		    	foreach ($fields as $key => $value) {
		    		if(isset($value['custom']) && $value['custom']) {
		    			if(isset($value['user_meta']) && ($value['user_meta']==='yes')) {
		    				$fields_keys[$key] = isset($value['type']) ? $value['type'] : '';
		    			}
		    		} else {
		    			if(isset($value['type'])) {
		    				$fields_keys[$key] = isset($value['type']) ? $value['type'] : '';
		    			} else {
		    				$fields_keys[$key] = 'text';
		    			}
		    		}
		    	}
		    }
	    	return $fields_keys;
	    }

	    /**
         * Function for get customer meta fields.
         *
         * @return array.
         */
        public static function thwma_get_customer_meta_fields() {
	    	$billing_fields = WC()->countries->get_address_fields(WC()->countries->get_base_country(), 'billing_');
	    	$shipping_fields = WC()->countries->get_address_fields(WC()->countries->get_base_country(), 'shipping_');

	    	$billing_fields_data = array();
	    	if(!empty($billing_fields) && is_array($billing_fields)) {
		    	foreach($billing_fields as $key => $value) {
		    	$label = isset($value['label']) ? $value['label'] : '';
					$billing_fields_data[$key] = array(
						'label'       => esc_html__($label, 'woocommerce'),
						'description' => '',
					);
		    	}
		    }
	    	$shipping_fields_data = array();
	    	if(!empty($shipping_fields) && is_array($shipping_fields)) {
		    	foreach($shipping_fields as $key => $value) {
		    	$label = isset($value['label']) ? $value['label'] : '';
					$shipping_fields_data[$key] = array(
						'label'       => esc_html__($label, 'woocommerce'),
						'description' => '',
					);
		    	}
		    }
	    	$show_fields = apply_filters(
				'woocommerce_customer_meta_fields', array(
					'billing'  => array(
						'fields' => $billing_fields_data
					),
					'shipping'  => array(
						'fields' => $shipping_fields_data
					)
				)
			);
			return $show_fields;
	    }

	    /**
         * Function for get addresses.
         *
         * @param integer $customer_id The customer id.
         * @param string $type The address type.
         *
         * @return array/int.
         */
        public static function get_addresses($customer_id, $type) {
			$address = self::get_custom_addresses($customer_id, $type);
			$default_key = self::get_custom_addresses($customer_id, 'default_'.$type);
			$same_address = self::is_same_address_exists($customer_id, $type);
			$address_key = ($default_key) ? $default_key : $same_address;

			if(is_array($address) && !empty($address)) {
				$addresses = array();
				foreach ($address as $key => $value) {
					$get_heading = self::get_custom_addresses($customer_id, $type, $key, $type.'_heading');
					$default_heading = apply_filters('thwma_default_heading', false);
					if($default_heading && $default_heading != '') {
						$heading = $get_heading ? $get_heading : esc_html__('Home', 'woocommerce-multiple-addresses-pro') ;
					} else {
						$heading = $get_heading ? $get_heading : esc_html__('', 'woocommerce-multiple-addresses-pro') ;
					}
					if($key != $address_key) {
						//if(isset($key['billing_first_name']) && !empty($key['billing_first_name'])) {
							$addresses[$type.'?atype='.$key] = $heading;
						//}
					}
				}
				$addresses = ($addresses) ?  $addresses :  false ;
				return $addresses;
			} else {
				return false;
			}

		}


		 /**
         * Function for get address fields.
         *
         * @param string $type The address type.
         *
         * @return array.
         */
        public static function get_address_fields($type) {
	    	$fields = WC()->countries->get_address_fields(WC()->countries->get_base_country(), $type.'_');
	    	$fields_keys = array();
	    	if(!empty($fields) && is_array($fields)) {
		    	foreach ($fields as $key => $value) {
		    		if(isset($value['custom']) && $value['custom']) {
		    			if(isset($value['user_meta']) && ($value['user_meta'] === 'yes')) {
		    				$fields_keys[] = $key;
		    			}
		    		} else {
		    			$fields_keys[$key] = $key;

		    		}
		    	}
		    }
	    	return $fields_keys;
	    }

		/**
         * Function for get custom addresses.
         *
         * @param integer $user_id The user id
         * @param string $type The address type.
         * @param string $address_key The address key.
         * @param string $key The key value.
         *
         * @return string/int.
         */
        public static function get_custom_addresses($user_id, $type=false, $address_key=false, $key=false) {
			$custom_address = get_user_meta($user_id, self::ADDRESS_KEY, true);
			if(is_array($custom_address)) {
				if($type && isset($custom_address[$type])) {
					if(!empty($address_key) && isset($address_key) && apply_filters('thwma_check_valid_address_key', true, $address_key)) {
						if($key) {
							if(isset($custom_address[$type][$address_key][$key])) {
								return $custom_address[$type][$address_key][$key];
							} else {
								return false;
							}
						}
						if(array_key_exists($address_key, $custom_address[$type])) {
							return $custom_address[$type][$address_key];
						}
					}
					return $custom_address[$type];
				}
			}
			return false;
		}

		/**
         * Function for save address to user.
         *
         * @param string $user_id The user id.
         * @param array $address The address data.
         * @param string $type The address type.
         */
        public static function save_address_to_user($user_id, $address, $type) {
			$custom_addresses = get_user_meta($user_id, self::ADDRESS_KEY, true);
			$custom_addresses = is_array($custom_addresses) ? $custom_addresses : array();
			$saved_address = THWMA_Utils::get_custom_addresses($user_id, $type);
			if(!is_array($saved_address)) {
				$custom_address = array();
				$default_address = self::get_default_address($user_id, $type);
				$default_heading = apply_filters('thwma_default_heading', false);
				if($default_heading) {
					if(!array_key_exists($type.'_heading', $default_address)) {
						$default_address[$type.'_heading'] = esc_html__('Home', 'woocommerce-multiple-addresses-pro');
					}
				} else {
					if(!array_key_exists($type.'_heading',$default_address)) {
						$default_address[$type.'_heading'] = esc_html__('', 'woocommerce-multiple-addresses-pro');
					}
				}
				if($default_address && array_filter($default_address) && (count(array_filter($default_address)))>2) {$custom_address['address_0'] = $default_address;
				}
				$custom_address['address_1'] = $address;
				$custom_addresses[$type] = $custom_address;
			} else {
				if(is_array($saved_address)) {
					if(isset($custom_addresses[$type])) {
						$exist_custom = $custom_addresses[$type];
						$new_key_id = self::get_new_custom_id($user_id, $type);
						$new_key = 'address_'.$new_key_id;
						$custom_address[$new_key] = $address;
						$custom_addresses[$type] = array_merge($exist_custom, $custom_address);
					}
				}
			}
			update_user_meta($user_id, self::ADDRESS_KEY, $custom_addresses);
		}

		/**
         * Function for get custom address for guest user.
         *
         * @param string $type The address type.
         * @param string $address_key The address key.
         * @param string $key The key value.
         *
         * @return string/int.
         */
        public static function get_custom_addresses_of_guest_user($type=false, $address_key=false, $key=false) {
			//$custom_address = get_transient(THWMA_Utils::GUEST_USER_SHIPPING_ADDR);
			//$custom_addresses = array();
			//$custom_address = isset($_COOKIE[THWMA_Utils::GUEST_USER_SHIPPING_ADDR])?$_COOKIE[THWMA_Utils::GUEST_USER_SHIPPING_ADDR]:array();

			$custom_address = array();
			if(isset($_COOKIE[THWMA_Utils::GUEST_USER_SHIPPING_ADDR])) {
				$shipping_address = $_COOKIE[THWMA_Utils::GUEST_USER_SHIPPING_ADDR];
				$shipping_address = preg_replace('!s:(\d+):"(.*?)";!', "'s:'.strlen('$2').':\"$2\";'", $shipping_address);
				$custom_address = unserialize(base64_decode($shipping_address));
			}
			if(is_array($custom_address)) {
				if($type && isset($custom_address[$type])) {
					if($address_key) {
						if($key) {
							if(isset($custom_address[$type][$address_key][$key])) {
								return $custom_address[$type][$address_key][$key];
							} else {
								return false;
							}
						}
						if(array_key_exists($address_key, $custom_address[$type])) {
							return $custom_address[$type][$address_key];
						}
					}
					return $custom_address[$type];
				}
			}
			return false;
		}

		/**
         * Function for get guest user new custom id.
         *
         * @param string $type The address type.
         *
         * @return int.
         */
        public static function get_guest_user_new_custom_id($type) {
			$custom_address = THWMA_Utils::get_custom_addresses_of_guest_user($type);
			if($custom_address) {
				$all_keys = array_keys($custom_address);
				$key_ids = array();
				if(!empty($all_keys) && is_array($all_keys)) {
					foreach ($all_keys as $key) {
						if($key != 'selected_address') {
							$key_ids[] = str_replace('address_', '', $key);
						}
			 		}
			 	}
				$new_id = '';
		 		if(!empty($key_ids)) {
		 			if(is_array($key_ids)) {
						if(is_numeric(max($key_ids))) {
			 				$new_id = max($key_ids)+1;
						} else {
							$new_id = 1;
						}
		 			}
				}
				return $new_id;
			}
		}

		/**
         * Function for save address to guest user.
         *
         * @param array $address The address data.
         * @param string $type The address type.
         * @param string $expiration The expiration time.
         */
        public static function save_address_to_guest_user($address, $type, $expiration) {
        	//$custom_addresses = get_transient(THWMA_Utils::GUEST_USER_SHIPPING_ADDR);
			//$custom_addresses = isset($_COOKIE[THWMA_Utils::GUEST_USER_SHIPPING_ADDR])?$_COOKIE[THWMA_Utils::GUEST_USER_SHIPPING_ADDR]:array();

			$custom_addresses = array();
			if(isset($_COOKIE[THWMA_Utils::GUEST_USER_SHIPPING_ADDR])) {
				$shipping_address = $_COOKIE[THWMA_Utils::GUEST_USER_SHIPPING_ADDR];
				$shipping_address = preg_replace('!s:(\d+):"(.*?)";!', "'s:'.strlen('$2').':\"$2\";'", $shipping_address);
				$custom_addresses = unserialize(base64_decode($shipping_address));
			}
			$custom_addresses = is_array($custom_addresses) ? $custom_addresses : array();
			$saved_address = THWMA_Utils::get_custom_addresses_of_guest_user($type);
			if(!is_array($saved_address)) {
				$custom_address = array();
				$custom_address['address_1'] = $address;
				$custom_addresses[$type] = $custom_address;
			} else {
				if(is_array($saved_address)) {
					if(isset($custom_addresses[$type])) {
						$exist_custom = $custom_addresses[$type];
						$new_key_id = self::get_guest_user_new_custom_id($type);
						$new_key = 'address_'.$new_key_id;
						$custom_address[$new_key] = $address;
						$custom_addresses[$type] = array_merge($exist_custom, $custom_address);
					}
				}
			}
			//set_transient(THWMA_Utils::GUEST_USER_SHIPPING_ADDR, $custom_addresses, $expiration);
			setcookie(THWMA_Utils::GUEST_USER_SHIPPING_ADDR, base64_encode(serialize($custom_addresses)), time() + $expiration, "/");
			if(isset($_COOKIE[THWMA_Utils::GUEST_USER_SHIPPING_ADDR])) {
				$shipping_address = $_COOKIE[THWMA_Utils::GUEST_USER_SHIPPING_ADDR];
				$shipping_address = preg_replace('!s:(\d+):"(.*?)";!', "'s:'.strlen('$2').':\"$2\";'", $shipping_address);
				$custom_address = unserialize(base64_decode($shipping_address));
			}

			// $cookie_name = "user";
			// $cookie_value = "John Doe";
			// setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day

			// if(!isset($_COOKIE[$cookie_name])) {
			//     echo "Cookie named '" . $cookie_name . "' is not set!";
			// } else {
			//     echo "Cookie '" . $cookie_name . "' is set!<br>";
			//     echo "Value is: " . $_COOKIE[$cookie_name];
			// }
		}


		/**
         * Function for get default address.
         *
         * @param string $user_id The user id.
         * @param string $type The address type.
         *
         * @return array.
         */
        public static function get_default_address($user_id, $type) {
			$fields = self::get_address_fields($type);
			$default_address = array();
			if(!empty($fields) && is_array($fields)) {
				foreach ($fields as $key) {
					$default_address[$key] = get_user_meta($user_id, $key, true);
				}
			}
			return $default_address;
		}

		/**
         * Function for get new custom address id.
         *
         * @param integer $user_id The user id.
         * @param string $type The address type.
         *
         * @return int.
         */
        public static function get_new_custom_id($user_id, $type) {
			$custom_address = THWMA_Utils::get_custom_addresses($user_id, $type);
			if($custom_address) {
				$all_keys = array_keys($custom_address);
				$key_ids = array();
				if(!empty($all_keys) && is_array($all_keys)) {
					foreach ($all_keys as $key) {
						if($key != 'selected_address') {
							$key_ids[] = str_replace('address_', '', $key);
						}
			 		}
			 	}
		 		$new_id = '';
		 	// 	if(!empty($key_ids)) {
		 	// 		//if(is_numeric($key_ids)) {
				// 		$new_id = max($key_ids)+1;
				// 	//}
				// }
				if(!empty($key_ids)) {
					if(is_array($key_ids)) {
						if(is_numeric(max($key_ids))) {
								$new_id = max($key_ids)+1;
						} else {
							$new_id = 1;
						}
					}
				}
				return $new_id;
			}
		}

		/**
         * Function for update address to user.
         *
         * @param integer $user_id The user id.
         * @param array $address The address details.
         * @param string $type The address type.
         * @param string $address_key The address key.
         */
        public static function update_address_to_user($user_id, $address, $type, $address_key) {
		    // Ensure $address_key is valid using the filter
		    if (!empty($address_key) && apply_filters('thwma_check_valid_address_key', true, $address_key)) {
		        $custom_addresses = get_user_meta($user_id, self::ADDRESS_KEY, true);
		        $exist_custom = isset($custom_addresses[$type]) ? $custom_addresses[$type] : '';
		        $exist_custom = is_array($exist_custom) ? $exist_custom : array();

		        // Safely assign the address key and address
		        $custom_address = [];
		        $custom_address[$address_key] = $address;

		        // Merge and update
		        $custom_addresses[$type] = array_merge($exist_custom, $custom_address);
		        update_user_meta($user_id, self::ADDRESS_KEY, $custom_addresses);
		    }
		}

		/**
         * The delete function.
         *
         * @param integer $user_id The user id.
         * @param string $type The address type.
         * @param string $custom The custom key.
         */
        public static function delete($user_id, $type, $custom) {
			$custom_addresses = get_user_meta($user_id,self::ADDRESS_KEY,true);
			unset($custom_addresses[$type][$custom]);
			update_user_meta($user_id,self::ADDRESS_KEY,$custom_addresses);
		}

		/**
		 * Check if the specified address key exists in the given address type for a user.
		 *
		 * @param int $user_id The user ID.
		 * @param string $type The address type (either 'billing' or 'shipping').
		 * @param string $address_key The key to be checked (e.g., 'address_1').
		 *
		 * @return bool true|false
		 */
		public static function check_key_of_address_type($user_id, $type , $address_key) {
			if(!empty($user_id)){
				if( "billing" === $type || "shipping" === $type ){
					$custom_addresses = get_user_meta($user_id, self::ADDRESS_KEY,true);
					if(is_array($custom_addresses) && array_key_exists($type , $custom_addresses)){
						if(is_array($custom_addresses[$type]) && array_key_exists($address_key , $custom_addresses[$type])){
							return true;
						}
					}
				}
			}

			return false;
		}

		/**
		 * Remove main order.
		 *
		 * @param $order_id
		 *
		 * @return mixed|void
		 */
		public static function thwma_remove_main_order($order_id) {
			wp_delete_post ( $order_id, $force_delete = false );
		}

		public static function delete_guest_address($type, $custom, $expiration) {
			$custom_address = array();
			if(isset($_COOKIE[THWMA_Utils::GUEST_USER_SHIPPING_ADDR])) {
				$shipping_address = $_COOKIE[THWMA_Utils::GUEST_USER_SHIPPING_ADDR];
				$shipping_address = preg_replace('!s:(\d+):"(.*?)";!', "'s:'.strlen('$2').':\"$2\";'", $shipping_address);
				$custom_address = unserialize(base64_decode($shipping_address));
			}
			unset($custom_address[$type][$custom]);
			setcookie(THWMA_Utils::GUEST_USER_SHIPPING_ADDR, base64_encode(serialize($custom_address)), time() + $expiration, "/");
		}


		/**
         * Function for check the same address is existing.
         *
         * @param integer $user_id The user id
         * @param string $type The address type.
         *
         * @return int/string.
         */
        public static function is_same_address_exists($user_id, $type) {
			$default_address = THWMA_Utils::get_default_address($user_id, $type);
			$addresses = THWMA_Utils::get_custom_addresses($user_id, $type);
			if($addresses && is_array($addresses)) {
				foreach ($addresses as $key => $value) {
					$is_exit = self::is_same_address($default_address, $value);
					if($is_exit == true) {
						return $key;
						break;
					}
				}
			}
			return false;
		}

	 	/**
         * Function for check is same addresses .
         *
         * @param array $address_1 The address one
         * @param array $address_2 The address two.
         *
         * @return string/int.
         */
        public static function is_same_address($address_1, $address_2) {
			$is_same = true;
			if(!empty($address_1) && is_array($address_1)) {
				foreach($address_1 as $key => $value) {
					if(!(isset($address_2[$key]) && isset($address_1[$key]) && $address_2[$key] == $address_1[$key])) {
						$is_same = false;
							break;
					}
					return $is_same;
				}
			}
			return false;
		}

		/**
         * Function for get all addresses.
         *
         * @param integer $customer_id The customer id.
         * @param string $name The name data.
         *
         * @return array.
         */
        public static function get_all_addresses($customer_id, $name) {
			$new_address_format = self::get_address_format($customer_id, $name);
			$addresses = self::thwma_get_formatted_address($new_address_format);
			return $addresses;
		}

		/**
		 * Helper function for Woo get_formatted_address
		 * 
		 * @param array $args Arguments.
		 * @param string $separator How to separate address lines.
		 * 
		 * @return string
		 */

		 public static function thwma_get_formatted_address($array= array(), $separator= '<br/>') {
			$formatted_address = WC()->countries->get_formatted_address(self::sanitize_args_array($array), $separator);
			return $formatted_address;
		 }

		/**
         * Check if $args is an array
         *
         * @param array $args The customer id.
         * @return array.
         */
		public static function sanitize_args_array($args) {

			$default_args = array(
				'first_name' => '',
				'last_name'  => '',
				'company'    => '',
				'address_1'  => '',
				'address_2'  => '',
				'city'       => '',
				'state'      => '',
				'postcode'   => '',
				'country'    => '',
			);

			$args = wp_parse_args( $args, $default_args );

			// Ensure all values are strings or empty strings to avoid passing null
			$args = array_map( function( $value ) {
				return $value ?? ''; // Convert null to an empty string
			}, $args );

			return $args;
		}

		/**
         * Function for get address format.
         *
         * @param string $customer The customer data.
         * @param string $name The name data.
         *
         * @return array.
         */
        public static function get_address_format($customer, $name) {
			$key_id = substr($name, strpos($name,"=") + 1);
			$type = substr($name.'?', 0, strpos($name, '?'));
			$changed_address = array();
			$address = THWMA_Utils::get_custom_addresses($customer, $type, $key_id);
			$changed_address = self::get_formated_address($type, $address);
			return $changed_address;
		}

		/**
         * Function for get formated address.
         *
         * @param string $type The address type.
         * @param array $address The address info
         *
         * @return array.
         */
        public static function get_formated_address($type, $address) {
        	$format_address = array();
			if(is_array($address) && !empty($address)) {
				foreach ($address as $key => $value) {
					$format_key = str_replace($type.'_', '', $key);
					$format_address[$format_key] = $value;
				}
				return self::sanitize_args_array($format_address);
			}
		}

		/**
         * Function for check woocommerce version.
         *
         * @param string $version The current version of woocommerce plugin.
         *
         * @return int.
         */
        public static function woo_version_check($version = '3.0') {
			if(function_exists('is_woocommerce_active') && is_woocommerce_active()) {
				global $woocommerce;
				if(version_compare($woocommerce->version, $version, ">=")) {
				  	return true;
				}
			}
			return false;
		}

		/**
         * Function for check wpml is active
         *
         * @return bool.
         */
		public static function is_wpml_active(){
			global $sitepress;
			return function_exists('icl_object_id') && is_object($sitepress);
		}

		/**
         * Function for modify the package.
         *
         * @return array.
         */
  //       public static function modified_package() {
		// 	global $woocommerce;
		// 	$values = array();
		// 	if(!empty($woocommerce->cart->get_cart()) && is_array($woocommerce->cart->get_cart())) {
		// 		foreach ($woocommerce->cart->get_cart() as $cart_item) {
		// 			$_product =  wc_get_product($cart_item['data']->get_id());
		// 			$product_price = $_product->get_price();
		// 	    	$product_name = $_product->get_title();
		// 	    	$user_id= get_current_user_id();

		// 			if(isset($cart_item['product_shipping_address'])) {
		// 				$ship_addresses = isset($cart_item['product_shipping_address']) ? $cart_item['product_shipping_address']:'';
		// 				$adr_data = THWMA_public::get_user_addresses($ship_addresses);

		// 				if($adr_data && is_array($adr_data)) {
		// 					$shipping_country = isset($adr_data['shipping_country']) ? $adr_data['shipping_country'] : '';
		// 					$shipping_state = isset($adr_data['shipping_state']) ? $adr_data['shipping_state'] : '';
		// 					$shipping_postcode = isset($adr_data['shipping_postcode']) ? $adr_data['shipping_postcode'] : '';
		// 					$shipping_city = isset($adr_data['shipping_city']) ? $adr_data['shipping_city'] : '';
		// 					$shipping_address_1 = isset($adr_data['shipping_address_1']) ? $adr_data['shipping_address_1'] : '';
		// 					$shipping_address_2 = isset($adr_data['shipping_address_2']) ? $adr_data['shipping_address_2'] : '';
		// 				    $active_methods   = array();
		// 				    $values[] = array ('country' => $shipping_country,
		// 	                     'amount'  => $product_price,
		// 	                     'shipping_state' => $shipping_state,
		// 	                     'shipping_postcode' => $shipping_postcode,
		// 	                     'shipping_city' => $shipping_city,
		// 	                     'shipping_address_1' => $shipping_address_1,
		// 	                     'shipping_address_2' => $shipping_address_2
		// 	                );
		// 				}
		// 			} else {
		// 				$default_address = THWMA_Utils::get_default_address($user_id, 'shipping');
		// 				if($default_address && is_array($default_address)) {
		// 					$shipping_country = isset($default_address['shipping_country']) ? $default_address['shipping_country'] : '';
		// 					$shipping_state = isset($default_address['shipping_state']) ? $default_address['shipping_state'] : '';
		// 					$shipping_postcode = isset($default_address['shipping_postcode']) ? $default_address['shipping_postcode'] : '';
		// 					$shipping_city = isset($default_address['shipping_city']) ? $default_address['shipping_city'] : '';
		// 					$shipping_address_1 = isset($default_address['shipping_address_1']) ? $default_address['shipping_address_1'] : '';
		// 					$shipping_address_2 = isset($default_address['shipping_address_2']) ? $default_address['shipping_address_2'] : '';
		// 				    $active_methods   = array();
		// 				    $values = array (
		// 				    	'country' => $shipping_country,
		// 		                'amount'  => $product_price,
		// 		                'shipping_state' => $shipping_state,
		// 		                'shipping_postcode' => $shipping_postcode,
		// 		                'shipping_city' => $shipping_city,
		// 		                'shipping_address_1' => $shipping_address_1,
		// 		                'shipping_address_2' => $shipping_address_2
		// 		            );

		// 				}
		// 			}
		// 		}
		// 	}
		// 	return $values;
		// 	// WC()->shipping->calculate_shipping(self::get_shipping_packages($values));
		//  //    $shipping_methods = WC()->shipping->packages;
		//  //    foreach($shipping_methods as $key => $methods) {
		//  //    	$active_methods = $shipping_methods[$key]['rates'];
		//  //    }
		// }



		/**
         * Function for get shipping package.
         *
         * @param string $values The shipping details.
         *
         * @return array.
         */
        public static function get_shipping_packages($values) {
			$packages = array();
			// $i = 0;
			if(!empty($values) && is_array($values)) {
			    foreach ($values as $key => $value) {
				    $packages[$key] = array(
				        'contents'        => WC()->cart->cart_contents,
				        'contents_cost'   => isset($value['amount']) ? $value['amount'] : '',
				        'applied_coupons' => WC()->cart->applied_coupons,
				        'destination'     => array(
				            'country'   => isset($value['country']) ? $value['country'] : '',
				            'state'     => isset($value['shipping_state']) ? $value['shipping_state'] : '',
				            'postcode'  => isset($value['shipping_postcode']) ? $value['shipping_postcode'] : '',
				            'city'  => isset($value['shipping_city']) ? $value['shipping_city'] : '',
				            'address'  => isset($value['shipping_address_1']) ? $value['shipping_address_1'] : '',
				            'address1'  =>isset($value['shipping_address_1']) ? $value['shipping_address_1'] : '',
				            'address_2'  => isset($value['shipping_address_2']) ? $value['shipping_address_2'] : ''
				       )
				    );
				}
			}
			return $packages;
		}

		/**
         * Get address field values.
         *
         * @param array $adr_data The address data.
         * @param array $product The prod data.
         *
         * @return array.
         */
		public static function get_address_field_values($adr_data, $product) {
			$values = array();
			$product_price = $product->get_price();
			if($adr_data && is_array($adr_data)) {
				$shipping_country = isset($adr_data['shipping_country']) ? esc_attr($adr_data['shipping_country']) : '';
				$shipping_state = isset($adr_data['shipping_state']) ? esc_attr($adr_data['shipping_state']) : '';
				$shipping_postcode = isset($adr_data['shipping_postcode']) ? esc_attr($adr_data['shipping_postcode']) : '';
				$shipping_city = isset($adr_data['shipping_city'])?esc_attr($adr_data['shipping_city']):'';
				$shipping_address_1 = isset($adr_data['shipping_address_1']) ? esc_attr($adr_data['shipping_address_1']) : '';
				$shipping_address_2 = isset($adr_data['shipping_address_2']) ? esc_attr($adr_data['shipping_address_2']) : '';
			    $active_methods   = array();

			    $values[] = array (
		    	 	'country' => $shipping_country,
	             	'amount'  => $product_price,
	             	'shipping_state' => $shipping_state,
	             	'shipping_postcode' => $shipping_postcode,
	             	'shipping_city' => $shipping_city,
	             	'shipping_address_1' => $shipping_address_1,
	             	'shipping_address_2' => $shipping_address_2
	            );
			}
			return $values;
		}

		/**
         * set address on ship to section.
         *
         * @param array $adr_data The address data.
         *
         * @return array.
         */
		public static function set_ship_to_section_address( $adr_data ) {
			$pdt_shipp_addr_formated = '';
			if($adr_data && is_array($adr_data)) {
				$shipp_addr_format = self::get_formated_address('shipping',$adr_data);
				if(apply_filters('thwma_inline_address_display', true)) {
					$separator = ', ';
					$pdt_shipp_addr_formated = self::thwma_get_formatted_address($shipp_addr_format, $separator);
				} else {
					$pdt_shipp_addr_formated = self::thwma_get_formatted_address($shipp_addr_format);
				}
			}
			return $pdt_shipp_addr_formated;
		}

		/**
         * Writelog function.
         *
         * @param string $log The passed variable.
         *
         * @return array/string/int.
         */
        public static function write_log ($log)  {
			if (true === WP_DEBUG) {
				if (is_array($log) || is_object($log)) {
					error_log(print_r($log, true));
				} else {
					error_log($log);
				}
			}
		}

		/**
         * Function for cart shipping woocommerce form field.
         *
         * @param string $key The key value.
         * @param string $args The field arguments.
         * @param string $value The field values.
         *
         * @return array.
         */
        public static function thwma_cart_shipping_woocommerce_form_field($key, $args, $value = null) {
			$defaults = array(
				'type'              => 'text',
				'label'             => '',
				'description'       => '',
				'placeholder'       => '',
				'maxlength'         => false,
				'required'          => false,
				'autocomplete'      => false,
				'id'                => $key,
				'class'             => array(),
				'label_class'       => array(),
				'input_class'       => array(),
				'return'            => false,
				'options'           => array(),
				'custom_attributes' => array(),
				'validate'          => array(),
				'default'           => '',
				'autofocus'         => '',
				'priority'          => '',
			);

			$args = wp_parse_args($args, $defaults);
			$args = apply_filters('woocommerce_form_field_args', $args, $key, $value);

			if ($args['required']) {
				$args['class'][] = 'validate-required';
				$required        = '&nbsp;<abbr class="required" title="' . esc_attr__('required', 'woocommerce') . '">*</abbr>';
			} else {
				$required = '&nbsp;<span class="optional">(' . esc_html__('optional', 'woocommerce') . ')</span>';
			}

			if(array_key_exists('label_class', $args)) {
				if (is_string($args['label_class'])) {
					$args['label_class'] = array($args['label_class']);
				}
			}

			if (is_null($value)) {
				$value = array_key_exists('default', $args) ? $args['default'] : '';
			}

			// Custom attribute handling.
			$custom_attributes         = array();
			$args['custom_attributes'] = array_filter((array) $args['custom_attributes'], 'strlen');

			if ($args['maxlength']) {
				$args['custom_attributes']['maxlength'] = absint($args['maxlength']);
			}

			if (! empty($args['autocomplete'])) {
				$args['custom_attributes']['autocomplete'] = array_key_exists('autocomplete', $args) ? $args['autocomplete'] : '';
			}

			if (true === $args['autofocus']) {
				$args['custom_attributes']['autofocus'] = 'autofocus';
			}

			if ($args['description']) {
				if(array_key_exists('id', $args)) {
					$args['custom_attributes']['aria-describedby'] = $args['id'] . '-description';
				}
			}

			if (! empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
				foreach ($args['custom_attributes'] as $attribute => $attribute_value) {
					$custom_attributes[] = esc_attr($attribute) . '="' . esc_attr($attribute_value) . '"';
				}
			}

			if (! empty($args['validate']) && is_array($args['validate'])) {
				foreach ($args['validate'] as $validate) {
					$args['class'][] = 'validate-' . $validate;
				}
			}

			$field           = '';
			$label_id        = array_key_exists('autocomplete', $args) ? $args['id'] : '';
			$sort            = $args['priority'] ? $args['priority'] : '';
			$field_container = '<p class="form-row %1$s" id="%2$s" data-priority="' . esc_attr($sort) . '">%3$s</p>';

			switch ($args['type']) {
				case 'country':
					$countries = 'shipping_country' === $key ? WC()->countries->get_shipping_countries() : WC()->countries->get_allowed_countries();

					if (1 === count($countries)) {

						$field .= '<strong>' . current(array_values($countries)) . '</strong>';

						$field .= '<input type="hidden" name="' . esc_attr($key) . '" id="' . esc_attr($args['id']) . '" value="' . current(array_keys($countries)) . '" ' . implode(' ', $custom_attributes) . ' class="country_to_state" readonly="readonly" />';
					} else {
						$field = '<select name="' . esc_attr($key) . '" id="' . esc_attr($args['id']) . '" class="country_to_state country_select ' . esc_attr(implode(' ', $args['input_class'])) . '" ' . implode(' ', $custom_attributes) . '><option value="">' . esc_html__('Select a country / region&hellip;', 'woocommerce') . '</option>';
						if(!empty($countries) && is_array($countries)) {
							foreach ($countries as $ckey => $cvalue) {
								$field .= '<option value="' . esc_attr($ckey) . '" ' . selected($value, $ckey, false) . '>' . $cvalue . '</option>';
							}
						}
						$field .= '</select>';
						$field .= '<noscript><button type="submit" name="woocommerce_checkout_update_totals" value="' . esc_attr__('Update country / region', 'woocommerce') . '">' . esc_html__('Update country / region', 'woocommerce') . '</button></noscript>';

					}

					break;
				case 'state':
					/* Get country this state field is representing */
					$for_country = isset($args['country']) ? $args['country'] : WC()->checkout->get_value('billing_state' === $key ? 'billing_country' : 'shipping_country');
					$states      = WC()->countries->get_states($for_country);

					if (is_array($states) && empty($states)) {
						$field_container = '<p class="form-row %1$s" id="%2$s" style="display: none">%3$s</p>';
						$field .= '<input type="hidden" class="hidden" name="' . esc_attr($key) . '" id="' . esc_attr($args['id']) . '" value="" ' . implode(' ', $custom_attributes) . ' placeholder="' . esc_attr($args['placeholder']) . '" readonly="readonly" data-input-classes="' . esc_attr(implode(' ', $args['input_class'])) . '"/>';

					} elseif (! is_null($for_country) && is_array($states)) {
						$field .= '<select name="' . esc_attr($key) . '" id="' . esc_attr($args['id']) . '" class="state_select ' . esc_attr(implode(' ', $args['input_class'])) . '" ' . implode(' ', $custom_attributes) . ' data-placeholder="' . esc_attr($args['placeholder'] ? $args['placeholder'] : esc_html__('Select an option&hellip;', 'woocommerce')) . '"  data-input-classes="' . esc_attr(implode(' ', $args['input_class'])) . '">
							<option value="">' . esc_html__('Select an option&hellip;', 'woocommerce') . '</option>';
						if (is_array($states) && !empty($states)) {
							foreach ($states as $ckey => $cvalue) {
								$field .= '<option value="' . esc_attr($ckey) . '" ' . selected($value, $ckey, false) . '>' . $cvalue . '</option>';
							}
						}
						$field .= '</select>';

					} else {
						$field .= '<input type="text" class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" value="' . esc_attr($value) . '"  placeholder="' . esc_attr($args['placeholder']) . '" name="' . esc_attr($key) . '" id="' . esc_attr($args['id']) . '" ' . implode(' ', $custom_attributes) . ' data-input-classes="' . esc_attr(implode(' ', $args['input_class'])) . '"/>';

					}

					break;
				case 'textarea':
					$field .= '<textarea name="' . esc_attr($key) . '" class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" id="' . esc_attr($args['id']) . '" placeholder="' . esc_attr($args['placeholder']) . '" ' . (empty($args['custom_attributes']['rows']) ? ' rows="2"' : '') . (empty($args['custom_attributes']['cols']) ? ' cols="5"' : '') . implode(' ', $custom_attributes) . '>' . esc_textarea($value) . '</textarea>';

					break;
				case 'checkbox':
					$field = '<label class="checkbox ' . implode(' ', $args['label_class']) . '" ' . implode(' ', $custom_attributes) . '>
							<input type="' . esc_attr($args['type']) . '" class="input-checkbox ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($key) . '" id="' . esc_attr($args['id']) . '" value="1" ' . checked($value, 1, false) . ' /> ' . $args['label'] . $required . '</label>';

					break;
				case 'text':
				case 'password':
				case 'datetime':
				case 'datetime-local':
				case 'date':
				case 'month':
				case 'time':
				case 'week':
				case 'number':
				case 'email':
				case 'url':
				case 'tel':
					$field .= '<input type="' . esc_attr($args['type']) . '" class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($key) . '" id="' . esc_attr($args['id']) . '" placeholder="' . esc_attr($args['placeholder']) . '"  value="' . esc_attr($value) . '" ' . implode(' ', $custom_attributes) . ' />';

					break;
				case 'select':
					$field   = '';
					$options = '';
					if (! empty($args['options']) && is_array($args['options'])) {
						foreach ($args['options'] as $option_key => $option_text) {
							if ('' === $option_key) {
								// If we have a blank option, select2 needs a placeholder.
								if (empty($args['placeholder'])) {
									$args['placeholder'] = $option_text ? $option_text : __('Choose an option', 'woocommerce');
								}
								$custom_attributes[] = 'data-allow_clear="true"';
							}
							$options .= '<option value="' . esc_attr($option_key) . '" ' . selected($value, $option_key, false) . '>' . esc_attr($option_text) . '</option>';
						}
						$field .= '<select name="' . esc_attr($key) . '" id="' . esc_attr($args['id']) . '" class="select ' . esc_attr(implode(' ', $args['input_class'])) . '" ' . implode(' ', $custom_attributes) . ' data-placeholder="' . esc_attr($args['placeholder']) . '">
								' . $options . '
							</select>';
					}

					break;
				case 'radio':
					$label_id .= '_' . current(array_keys($args['options']));
					if (! empty($args['options']) && is_array($args['options'])) {
						foreach ($args['options'] as $option_key => $option_text) {
							$field .= '<input type="radio" class="input-radio ' . esc_attr(implode(' ', $args['input_class'])) . '" value="' . esc_attr($option_key) . '" name="' . esc_attr($key) . '" ' . implode(' ', $custom_attributes) . ' id="' . esc_attr($args['id']) . '_' . esc_attr($option_key) . '"' . checked($value, $option_key, false) . ' />';
							$field .= '<label for="' . esc_attr($args['id']) . '_' . esc_attr($option_key) . '" class="radio ' . implode(' ', $args['label_class']) . '">' . $option_text . '</label>';
						}
					}

					break;
			}

			if (! empty($field)) {
				$field_html = '';
				if ($args['label'] && 'checkbox' !== $args['type']) {
					$field_html .= '<label for="' . esc_attr($label_id) . '" class="' . esc_attr(implode(' ', $args['label_class'])) . '">' . $args['label'] . $required . '</label>';
				}
				$field_html .= '<span class="woocommerce-input-wrapper">' . $field;

				if ($args['description']) {
					$field_html .= '<span class="description" id="' . esc_attr($args['id']) . '-description" aria-hidden="true">' . wp_kses_post($args['description']) . '</span>';
				}
				$field_html .= '</span>';
				$container_class = esc_attr(implode(' ', $args['class']));
				$container_id    = esc_attr($args['id']) . '_field';
				$field           = sprintf($field_container, $container_class, $container_id, $field_html);
			}

			/**
			 * Filter by type.
			 */
			//$field = apply_filters('woocommerce_form_field_' . $args['type'], $field, $key, $args, $value);

			/**
			 * General filter on form fields.
			 *
			 * @since 3.4.0
			 */
			//$field = apply_filters('woocommerce_form_field', $field, $key, $args, $value);

			if ($args['return']) {
				return $field;
			} else {
				echo $field; // WPCS: XSS ok.
			}
		}

		/**
         * Check the checkout field editor plugin is active.
         *
         * @param string $log The passed variable.
         *
         * @return array/string/int.
         */
        public static function check_thwcfe_plugin_is_active()  {
			$thwcfe_is_active = false;
			if (is_plugin_active('woocommerce-checkout-field-editor-pro/woocommerce-checkout-field-editor-pro.php')) {
			   $thwcfe_is_active = true;
			}
			return $thwcfe_is_active;
		}

		/**
         * Check the WEPO Free is active.
         *
         * @param string $log The passed variable.
         *
         * @return array/string/int.
         */
        public static function check_thwepof_plugin_is_active()  {
			$thwepof_is_active = false;
			if (is_plugin_active('woo-extra-product-options/woo-extra-product-options.php')) {
			   $thwepof_is_active = true;
			}
			return $thwepof_is_active;
		}

		/**
         * Check the WEPO Pro is active.
         *
         * @param string $log The passed variable.
         *
         * @return array/string/int.
         */
        public static function check_thwepo_plugin_is_active()  {
			$thwepo_is_active = false;
			if (is_plugin_active('woocommerce-extra-product-options-pro/woocommerce-extra-product-options-pro.php')) {
			   $thwepo_is_active = true;
			}
			return $thwepo_is_active;
		}

		/**
         * Get total address count.
         *
         * @return int.
         */
		public static function get_total_address_count(){
			// Custom address count.
	        $type = 'shipping';
	        $all_addresses = '';
	        if(is_user_logged_in()) {
	        	$customer_id = get_current_user_id();
		        $custom_address = THWMA_Utils::get_custom_addresses($customer_id, $type);
		        if(is_array($custom_address)) {
		        	$all_addresses = $custom_address;
		        } else {
					$all_addresses = array();
					$def_address = THWMA_Utils::get_default_address($customer_id, $type);

					if(array_filter($def_address) && (count(array_filter($def_address)) > 2)) {
						$all_addresses ['selected_address'] = $def_address;
					}
				}
			} else {
				$custom_address = THWMA_Utils::get_custom_addresses_of_guest_user($type);
				if(is_array($custom_address)) {
		        	$all_addresses = $custom_address;
		        }
			}
			$total_address_count = '';
			if(is_array($all_addresses)) {
				$total_address_count = count($all_addresses);
			}
			return $total_address_count;
		}
	}
endif;
