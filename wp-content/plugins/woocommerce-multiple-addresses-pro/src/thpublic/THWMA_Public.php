<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link			https://themehigh.com
 * @since			1.0.0
 *
 * @package			woocommerce-multiple-addresses-pro
 * @subpackage		woocommerce-multiple-addresses-pro/src/public
 */

namespace Themehigh\WoocommerceMultipleAddressesPro\thpublic;

use Themehigh\WoocommerceMultipleAddressesPro\includes\utils\THWMA_Utils;

if(!defined('WPINC')) {
	die;
}

if(!class_exists('THWMA_Public')) :

    /**
     * public class
     */

    class THWMA_Public {
        const THWMA_TEXT_DOMAIN = 'thwma';
        private $plugin_name;
        private $version;
        public static $is_creating_suborder = false;


        /**
         * Constructor.
         *
         * @param string $plugin_name The plugin name
         * @param string $version The plugin version number
         */

        // public function __construct($plugin_name, $version) {
        //     $this->plugin_name = $plugin_name;
        //     $this->version = $version;
        //     add_action('after_setup_theme', array($this, 'define_public_hooks'));

        // }

        public function register() {
            // $this->plugin_name = $plugin_name;
            // $this->version = $version;
            add_action('after_setup_theme', array($this, 'define_public_hooks'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_styles_and_scripts'), 20);
            add_filter('woocommerce_locate_template', array($this, 'address_template'), 10, 3);

        }

        // private function define_public_constants() {
        //     !defined('THWMA_ASSETS_URL_PUBLIC') && define('THWMA_ASSETS_URL_PUBLIC', THWMA_URL . 'src/thpublic/assets/');
        //     !defined('THWMA_WOO_ASSETS_URL') && define('THWMA_WOO_ASSETS_URL', WC()->plugin_url() . '/assets/');
        //     !defined('THWMA_TEMPLATE_URL_PUBLIC') && define('THWMA_TEMPLATE_URL_PUBLIC',THWMA_PATH . 'src/thpublic/templates/');
        // }

        /**
         * Enqueue script and style.
         */

        public function enqueue_styles_and_scripts() {
            global $wp_scripts;
            // $this->define_public_constants();
            if(is_wc_endpoint_url('edit-address') || (is_checkout()) || is_cart() || is_wc_endpoint_url('view-order') || apply_filters('thwma_force_enqueue_public_scripts', false)) {
                $debug_mode = apply_filters('thwma_debug_mode', false);
                $suffix = $debug_mode ? '' : '.min';

                $jquery_version = isset($wp_scripts->registered['jquery-ui-core']->ver) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';

                $this->enqueue_styles($suffix, $jquery_version);
                $this->enqueue_scripts($suffix, $jquery_version);

            }
        }

        /**
         * Function for enqueue style.
         *
         * @param string $suffix The suffix of the style sheet file
         * @param string $jquery_version The passed jquery version
         */
        private function enqueue_styles($suffix, $jquery_version) {
            wp_register_style('select2', THWMA_WOO_ASSETS_URL.'/css/select2.css');
            wp_enqueue_style('woocommerce_frontend_styles');
            wp_enqueue_style('select2');
            wp_enqueue_style('dashicons');
            //wp_enqueue_style('jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/'. $jquery_version .'/themes/smoothness/jquery-ui.css');
            //wp_enqueue_style('FontAwesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css');
            wp_enqueue_style('jquery-ui-style', THWMA_ASSETS_URL_PUBLIC . 'css/jquery-ui.min.css', 'v1.12.1');
            if(apply_filters('thwma_force_enqueue_font_awesome_style', true)){
                wp_enqueue_style('font-awesome-style', THWMA_ASSETS_URL_PUBLIC . 'css/font/font-awesome.min.css', '3.0.2');
            }
            wp_enqueue_style('thwma-public-style', THWMA_ASSETS_URL_PUBLIC . 'css/thwma-public'. $suffix .'.css', $this->version);


            $settings = THWMA_Utils::get_setting_value('settings_styles');
            $is_button_style_enabled = isset($settings['enable_button_styles']) ? $settings['enable_button_styles'] : '';

            $button_bg = isset($settings['button_background_color']) && $settings['button_background_color'] ? 'background:'. $settings['button_background_color'] .' !important;' : '' ;
            $button_text_color = isset($settings['button_text_color']) && $settings['button_text_color'] ? 'color:' . $settings['button_text_color'].' !important;' : '';
            $button_padding = isset($settings['button_padding']) && $settings['button_padding'] ? 'padding:' . $settings['button_padding'] .' !important;': '';
            $button_class = '.btn-different-address';

            $plugin_style = '';
            $settings = get_option(THWMA_Utils::OPTION_KEY_ADVANCED_SETTINGS);
            if ($settings) {
                $custom_style = isset($settings['custom_styles']) && $settings['custom_styles'] ? $settings['custom_styles'] : ''; 
                $plugin_style .= $custom_style;
            }
            
            if($is_button_style_enabled == 'yes'){
                $plugin_style .= "
                    $button_class{
                        $button_bg
                        $button_text_color
                        $button_padding
                    }
                ";
            }
            if(apply_filters('thwma_force_enqueue_font_awesome_style', true)){
                wp_enqueue_style('font-awesome-style', THWMA_ASSETS_URL_PUBLIC . 'css/font-awesome.min.css', '3.0.2');
            }

            wp_add_inline_style( 'thwma-public-style', $plugin_style );
        }

        /**
         * Function for enqueue script.
         *
         * @param string $suffix The suffix of the style sheet file
         * @param string $jquery_version The passed jquery version
         * @param string $is_quick_view string for check quick view of the theme flatsome
         */
        private function enqueue_scripts($suffix, $jquery_version) {
            wp_register_script('thwma-public-script', THWMA_ASSETS_URL_PUBLIC . 'js/thwma-public'. $suffix .'.js', array('jquery', 'jquery-ui-dialog', 'jquery-ui-accordion', 'select2',), $this->version, true);
            wp_enqueue_script('thwma-public-script');

            if(THWMA_Utils:: get_advanced_settings_value('enable_autofill') == 'yes') {
                $api_key = THWMA_Utils:: get_advanced_settings_value('autofill_apikey');
                if($api_key) {
                    wp_enqueue_script('google-autocomplete', 'https://maps.googleapis.com/maps/api/js?v=3&libraries=places&key='.$api_key);
                }
            }

            $select_options = apply_filters('thwma_checkout_select_options', false);

            $address_fields_billing = THWMA_Utils::get_address_fields_by_address_key('billing_');
            $address_fields_shipping = THWMA_Utils::get_address_fields_by_address_key('shipping_');

            $section_settings = THWMA_Utils::get_custom_section_settings();
            $section_settings = $section_settings ? $section_settings : '';

            $store_country = WC()->countries->get_base_country();
            $sell_countries =  WC()->countries->get_allowed_countries();
            $specific_coutries =  array();

            // convert auto fill state code.
            // $state_code_array = array(
            //  'Gro.' => 'GR',
            //  'Jal.' => 'JA',
            //);

            // Filter for state list.
            $state_code_convert_data = apply_filters('thwma_state_code_list', $state_code_array=null);
            $state_code_convert = json_encode($state_code_convert_data);
            // Filter for console.log.
            $enable_console_log = apply_filters('enable_consolelog_to_display_state_code_from_google_api', false);

            $thwcfe_is_active = THWMA_Utils::check_thwcfe_plugin_is_active();
            if(is_checkout()) {
                $flag = true;
            } else{
                $flag = false;
            }

            global $current_user;
            $current_user_id = get_current_user_id();

            // Address limit.
            $address_limit_bil = '';
            $address_limit_ship = '';
            $address_limit_bil = THWMA_Utils::get_setting_value('settings_billing' , 'billing_address_limit');
            $address_limit_ship = THWMA_Utils::get_setting_value('settings_shipping' , 'shipping_address_limit');

            if (!is_numeric($address_limit_bil)) {
                $address_limit_bil = 0;
            }

            if (!is_numeric($address_limit_ship)) {
                $address_limit_ship = 0;
            }

            // Default address.
            $customer_id = get_current_user_id();
            $default_set_address = THWMA_Utils::get_custom_addresses($customer_id, 'default_billing');
            $same_address = THWMA_Utils::is_same_address_exists($customer_id, 'billing');
            $default_bil_address =  $same_address ? $same_address : $default_set_address;

            $default_set_address = THWMA_Utils::get_custom_addresses($customer_id, 'default_shipping');
            $same_address = THWMA_Utils::is_same_address_exists($customer_id, 'shipping');
            $default_ship_address =     $same_address ? $same_address : $default_set_address;

            $settings = THWMA_Utils::get_setting_value('settings_multiple_shipping');
            $cart_shipping_enabled = isset($settings['enable_cart_shipping']) ? $settings['enable_cart_shipping'] : '';
            $cart_shipping_enabled = apply_filters('thwma_modify_cart_shipping_option_filter', $cart_shipping_enabled);


            $script_var = array(
                'ajax_url' => apply_filters('thwma_should_use_wc_ajax_endpoint', false) ? WC_AJAX::get_endpoint('') : admin_url('admin-ajax.php'),
                'address_fields_billing'    => $address_fields_billing,
                'address_fields_shipping'   => $address_fields_shipping,
                'current_user_id'           => $current_user_id,

                'enable_autofill'   => THWMA_Utils:: get_advanced_settings_value('enable_autofill'),
                'store_country'     => $store_country,
                'sell_countries'    => $sell_countries,
                'specific_country'  => apply_filters('thwma_auto_fill_specific_country', $specific_coutries),
                'custom_sections'   => $section_settings,
                'select_options'    => $select_options,
                'billing_address'   => esc_html__('Billing Addresses', 'woocommerce-multiple-addresses-pro'),
                'shipping_address'  => esc_html__('Shipping Addresses', 'woocommerce-multiple-addresses-pro'),
                'addresses'         => esc_html__('Addresses', 'woocommerce-multiple-addresses-pro'),
                'state_code_convert'=> $state_code_convert,
                'enable_console_log'=> $enable_console_log,
                'thwcfe_is_active'  => $thwcfe_is_active,
                'is_checkout_page'  => $flag,
                'billing_adr_limit' => $address_limit_bil,
                'shipping_adr_limit'=> $address_limit_ship,
                'default_bil_address'=> $default_bil_address,
                'default_ship_address'=> $default_ship_address,
                'cart_shipping_enabled'=> $cart_shipping_enabled,
                'checkout_page_form_class' => apply_filters('thwma_checkout_page_form_class', 'form.checkout'),
                'get_address_with_id_nonce'                     => wp_create_nonce( 'get-address-with-id' ),
                'delete_address_with_id_nonce'                  => wp_create_nonce( 'delete-address-with-id' ),
                'set_default_address_nonce'                     => wp_create_nonce( 'set-default-address' ),
                'delete_address_with_id_cart_nonce'             => wp_create_nonce( 'delete-address-with-id-cart' ),
                'set_default_address_cart_nonce'                => wp_create_nonce( 'set-default-address-cart' ),
                'enable_ship_to_multi_address_nonce'            => wp_create_nonce( 'enable-ship-to-multi-address' ),
                'add_new_shipping_address_nonce'                => wp_create_nonce( 'add-new-shipping-address' ),
                'thwma_save_address_nonce'                      => wp_create_nonce( 'thwma-save-address' ),
                'guest_users_add_new_shipping_address_nonce'    => wp_create_nonce( 'guest-users-add-new-shipping-address' ),
                'thwma_save_guest_address_nonce'                => wp_create_nonce( 'thwma-save-guest-address' ),
                'delete_address_with_id_cart_guest_nonce'       => wp_create_nonce( 'delete-address-with-id-cart-guest' ),
                'save_multi_selected_shipping_nonce'            => wp_create_nonce( 'save-multi-selected-shipping' ),
                'save_shipping_method_details_nonce'            => wp_create_nonce( 'save-shipping-method-details' ),
                'additional_address_management_nonce'           => wp_create_nonce( 'additional-address-management' ),
                'remove_multi_shipping_row_nonce'               => wp_create_nonce( 'remove-multi-shipping-row' ),
                'update_multi_shipping_qty_field_nonce'         => wp_create_nonce( 'update-multi-shipping-qty-field' ),
            );
            wp_localize_script('thwma-public-script', 'thwma_public_var', $script_var);
        }

        public function define_public_hooks(){
        	if(THWMA_Utils::check_thwcfe_plugin_is_active() ){
                add_filter('thwcfe_force_enqueue_checkout_public_scripts', '__return_true');
            }
            add_action('woocommerce_before_checkout_billing_form', array($this, 'session_update_billing'));
            add_action('woocommerce_before_checkout_shipping_form', array($this, 'session_update_shipping'));
            if(!is_admin()) {
                add_filter('woocommerce_checkout_fields', array($this, 'add_hidden_field_to_checkout_fields'), 9);
            }
            add_filter('woocommerce_form_field_hidden', array($this, 'add_hidden_field'), 5, 4);

            $atype = true;
            if(apply_filters('disable_additional_address_field_on_default_address', true)){
                $atype = isset($_GET['atype']);
            }
            //if(isset($_GET['atype'])) {
            if($atype) {
                add_filter('woocommerce_billing_fields', array($this, 'add_additional_billing_field'), 1000, 2);
                add_filter('woocommerce_shipping_fields', array($this, 'add_additional_shipping_field'), 1000, 2);
            }
            add_action('woocommerce_after_checkout_validation', array($this, 'add_address_from_checkout'), 30, 2);

            add_filter('woocommerce_localisation_address_formats', array($this, 'localisation_address_formats'));
            add_filter('woocommerce_calculated_total',array($this,'thwma_calculate_order_total'),10,2);
            add_filter('woocommerce_cart_get_shipping_total', array($this, 'thwma_calculate_shipping_total'),10, 1);
            $section_settings = THWMA_Utils::get_custom_section_settings();
			if(is_user_logged_in() && $section_settings && is_array($section_settings)){
				foreach ($section_settings as $section => $props) {
					if($props['enable_section'] == 'yes') {
						add_action('thwcfe_before_section_fields_'.$section, array($this, 'add_additional_fields'), 10, 1);
					}
				}
			}

            //cmnt.
            // Display Thankyou page.
            add_filter('woocommerce_order_item_get_formatted_meta_data', array($this, 'thwma_shipping_addresses_display_on_thankyou_page'), 10, 2);
            add_filter('woocommerce_order_get_formatted_shipping_address', array($this, 'thwma_overrides_shipping_address_section_on_thankyou_page'), 10, 3);

            
        }

        public function address_template($template, $template_name, $template_path) {
            $settings = THWMA_Utils::get_setting_value('settings_multiple_shipping');
			$cart_shipping_enabled = isset($settings['enable_cart_shipping']) ? $settings['enable_cart_shipping']:'';

			if('myaccount/my-address.php' == $template_name) {
	        	$template = THWMA_TEMPLATE_URL_PUBLIC.'myaccount/my-address.php';
	        }
	        // if('cart/cart.php' == $template_name) {
	        // 	$template = THWMA_TEMPLATE_URL_PUBLIC.'cart/cart.php';
	        // }
	        // if('order/order-details.php' == $template_name) {
	        // 	$template = THWMA_TEMPLATE_URL_PUBLIC.'order/order-details.php';
	        // }
	        // if('order/order-details-item.php' == $template_name) {
	        // 	$template = THWMA_TEMPLATE_URL_PUBLIC.'order/order-details-item.php';
	        // }
	        $user_id = get_current_user_id();
	        $enable_multi_ship = '';
	        if (is_user_logged_in()) {
	        	$enable_multi_ship = get_user_meta($user_id, THWMA_Utils::USER_META_ENABLE_MULTI_SHIP, true);
	        } else {
	        	$enable_multi_ship = isset($_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP]) ? $_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP] : '';
	        }

	    	if($enable_multi_ship == 'yes') {
	    		if(apply_filters('thwma_enable_multi_shipping_calculation',true)){
			        if($cart_shipping_enabled == 'yes') {
				        if('cart/cart-shipping.php' == $template_name) {
				        	$template = THWMA_TEMPLATE_URL_PUBLIC.'/cart/cart-shipping.php';
				        }
				    }
				}
			}
	        return $template;
	    }

        /**
         * Function for update billing session (Checkout page)
         *
         * @param array $checkout The checkout informations.
         *
         * @return void.
         */
        public function session_update_billing($checkout) {
            $customer_id = get_current_user_id();
            if(is_user_logged_in()) {
                $default_address = THWMA_Utils::get_default_address($customer_id, 'billing');
                $addresfields = array('first_name', 'last_name', 'company','address_1', 'address_2', 'city', 'state', 'postcode', 'country', 'phone', 'email');
                if($default_address && array_filter($default_address) && (count(array_filter($default_address)) > 2)) {
                    if(!empty($addresfields) && is_array($addresfields)){
                        foreach ($addresfields as $key) {
                            if(isset($default_address['billing_'.$key])) {
                                $temp_value = isset($default_address['billing_'.$key]) ? $default_address['billing_'.$key] : '';
                                WC()->customer->{"set_billing_"."{$key}"}($temp_value);
                                WC()->customer->save();
                            }
                        }
                    }
                }

                // Fix for deactivate & activate.
                $default_set_address = THWMA_Utils::get_custom_addresses($customer_id, 'default_billing');
                if($default_set_address) {
                    $address_key = THWMA_Utils::is_same_address_exists($customer_id, 'billing');
                    if(!$address_key) {
                        THWMA_Utils::update_address_to_user($customer_id, $default_address, 'billing', $default_set_address);
                    }
                }
            }
        }

        /**
         * Function for update shipping session (Checkout page)
         *
         * @param array $checkout The checkout informations.
         *
         * @return void.
         */
        public function session_update_shipping($checkout) {
            $customer_id = get_current_user_id();
            if (is_user_logged_in()) {
                $default_address = THWMA_Utils::get_default_address($customer_id, 'shipping');
                $addresfields = array('first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country');
                if($default_address && array_filter($default_address) && (count(array_filter($default_address)) > 2)) {
                    if(!empty($addresfields) && is_array($addresfields)) {
                        foreach ($addresfields as $key) {
                            if(isset($default_address['shipping_'.$key])) {
                                $temp_value = isset($default_address['shipping_'.$key]) ? $default_address['shipping_'.$key] : '';
                                WC()->customer->{"set_shipping_"."{$key}"}($temp_value);
                                WC()->customer->save();
                            }
                        }
                    }
                }
                // Fix for deactivate & activate.
                $default_set_address = THWMA_Utils::get_custom_addresses($customer_id, 'default_shipping');
                if($default_set_address) {
                    $address_key = THWMA_Utils::is_same_address_exists($customer_id, 'shipping');
                    if(!$address_key) {
                        THWMA_Utils::update_address_to_user($customer_id, $default_address, 'shipping', $default_set_address);
                    }
                }
            }
        }

        /**
         * Function for add hidden fieds to checkout form (Checkout page)
         *
         * @param array $fields The field informations.
         *
         * @return array.
         */
        public function add_hidden_field_to_checkout_fields($fields) {
            $user_id = get_current_user_id();
            $default_bil_key = THWMA_Utils::get_custom_addresses($user_id, 'default_billing');
            $same_bil_key = THWMA_Utils:: is_same_address_exists($user_id, 'billing');
            $default_value = $default_bil_key ? $default_bil_key : $same_bil_key;
            $fields['billing']['thwma_hidden_field_billing'] = array(
                'label'    => esc_html__('hidden value',''),
                'required' => false,
                'class'    => array('form-row'),
                'clear'    => true,
                'default'  => $default_value,
                'type'     => 'hidden',
                'priority' => '',
            );

            $default_ship_key = THWMA_Utils::get_custom_addresses($user_id, 'default_shipping');
            $same_ship_key = THWMA_Utils:: is_same_address_exists($user_id, 'shipping');
            $default_value_ship = $default_ship_key ? $default_ship_key : $same_ship_key;
            
            $fields['billing']['thwma_checkbox_shipping'] = array(
                'label'    => esc_html__('hidden value', ''),
                'required' => false,
                'class'    => array('form-row'),
                'clear'    => true,
                'default'   => $default_value_ship,
                'type'     => 'hidden',
                'priority' => '',
            );
            $fields['shipping']['thwma_hidden_field_shipping'] = array(
                'label'    => esc_html__('hidden value', ''),
                'required' => false,
                'class'    => array('form-row'),
                'clear'    => true,
                'default'   => $default_value_ship,
                'type'     => 'hidden',
                'priority' => '',
            );
            return $fields;
        }
        /**
         * Function for create hidden fields (Checkout page)
         *
         * @param array $fields The field informations.
         * @param array $key The key value of hidden field.
         * @param array $args The hidden field arguments.
         * @param array $value The hidden field value.
         *
         * @return string.
         */
        public function add_hidden_field($field, $key, $args, $value) {
            return '<input type="hidden" name="'.esc_attr($key).'" id="'.esc_attr($args['id']).'" value="'.esc_attr($args['default']).'" />';
        }
        /**
         * Function for change default address (my-account page).
         *
         * @param integer $customer_id The user id.
         * @param string $type  The type of the address.
         * @param string $custom_key  The custom key.
         */
		public function change_default_address($customer_id, $type, $custom_key) {
            $default_address = THWMA_Utils::get_custom_addresses($customer_id, $type, $custom_key);
			$current_address = THWMA_Utils::get_default_address($customer_id, $type);
			$custom_addresses = get_user_meta($customer_id, THWMA_Utils::ADDRESS_KEY, true);
			if(is_array($custom_addresses)) {
	        	$all_addresses = $custom_addresses;
	        } else {
				$all_addresses = array();
				$def_address = THWMA_Utils::get_default_address($customer_id, $type);
				if(array_filter($def_address) && (count(array_filter($def_address)) > 2)) {
					$all_addresses ['selected_address'] = $def_address;
				}
			}

			if(!empty($default_address)) {
				if(!empty($current_address) && is_array($current_address)) {
					foreach ($current_address as $key => $value) {
						$new_address = (isset($default_address[$key])) ? $default_address[$key] : '';

						update_user_meta($customer_id, $key, $new_address, $current_address[$key]);
					}
				}
			}
			if(isset($custom_addresses[$type]) && is_array($custom_addresses[$type])) {
				$total_address_count = count($custom_addresses[$type]);
			} else {
				$total_address_count = 0;
			}
			if($total_address_count == 0) {
				$custom_addresses = array(
					$type => array(
						'selected_address' => $current_address
					),
					'default_'.$type => 'selected_address'
				);
			} else {
				$custom_addresses['default_'.$type] = $custom_key;
			}
			update_user_meta($customer_id, THWMA_Utils::ADDRESS_KEY, $custom_addresses);
			$custom_addresses = get_user_meta($customer_id, THWMA_Utils::ADDRESS_KEY, true);
			$current_address = THWMA_Utils::get_default_address($customer_id, $type);
		}

        /**
         * Function for add billing heading.
         *
         * @param array $address_fields The existing address fields
         * @param string $country The country details
         *
         * @return array
         */
        public function add_additional_billing_field($address_fields, $country) {
            if( is_wc_endpoint_url('edit-address') ){
                if(apply_filters('enable_additional_address_field', true)){
                    $label = apply_filters('additional_billing_label',__('Address Type', 'woocommerce-multiple-addresses-pro'));
                    $placeholder = apply_filters('additional_billing_placeholder',__('Home/Office/Other', 'woocommerce-multiple-addresses-pro'));
                    $address_fields['billing_heading'] = array(
                        'label'        => esc_html($label),
                        'required'     => false,
                        'class'        => array('form-row-wide', 'address-field'),
                        'autocomplete' => '',
                        'placeholder'  => esc_html($placeholder),
                        'priority'     => 0.5,
                    );
                }
            }
            return $address_fields;
        }

        /**
         * Function for add shipping heading.
         *
         * @param array $address_fields The existing address fileds
         * @param string $country The country details
         *
         * @return array
         */
        public function add_additional_shipping_field($address_fields, $country) {
            if( is_wc_endpoint_url('edit-address') ){
                if(apply_filters('enable_additional_address_field', true)){
                    $label = apply_filters('additional_shipping_label',__('Address Type', 'woocommerce-multiple-addresses-pro'));
                    $placeholder = apply_filters('additional_shipping_placeholder',__('Home/Office/Other', 'woocommerce-multiple-addresses-pro'));
                    $address_fields['shipping_heading'] = array(
                            'label'        => esc_html($label),
                            'required'     => false,
                            'class'        => array('form-row-wide', 'address-field'),
                            'autocomplete' => '',
                            'placeholder'  => esc_html($placeholder),
                            'priority'     => 0.5,
                        );
                }
            }
            return $address_fields;
        }

        /**
         * Function for add address from cehckout (for initial case-checkout page)
         *
         * @param string $data The given item datas
         * @param string $errors The existing errors
         *
         * @return string
         */
        public function add_address_from_checkout($data, $errors) {
            $this->validate_multi_shipping_address_of_guest_users($data, $errors);

            $user_id = get_current_user_id();
            if(empty($errors->get_error_messages() )) {
                if(isset($_POST['thwma_hidden_field_billing'])) {
                    $checkout_bil_key = isset($_POST['thwma_hidden_field_billing']) ? $_POST['thwma_hidden_field_billing'] : '';
                    if($checkout_bil_key == 'add_address') {
                        $this->set_first_address_from_checkout($user_id, 'billing');
                    }
                }
                if (! wc_ship_to_billing_address_only() && wc_shipping_enabled()) {
                    $checkout_ship_key = isset($_POST['thwma_hidden_field_shipping']) ? $_POST['thwma_hidden_field_shipping'] : '';
                    if(!empty($checkout_ship_key)) {
                        if($checkout_ship_key == 'add_address') {
                            $this->set_first_address_from_checkout($user_id, 'shipping');
                        }
                    }
                }
            }
        }

        /**
         * Function for validate multi-shiiping address fields of guest users (checkout page)
         *
         * @param string $data The given item datas
         * @param string $errors The existing errors
         *
         * @return string
         */
		public function validate_multi_shipping_address_of_guest_users($data, $errors) {
            if(!is_user_logged_in()) {
                $settings = THWMA_Utils::get_setting_value('settings_shipping');
                $custom_fields = isset($_POST['thwcfe_custom_field_data']) ? $_POST['thwcfe_custom_field_data'] : "";
                $custom_fields_arr = explode(',', $custom_fields);
                if(isset($custom_fields_arr)){
                    foreach ($custom_fields_arr as $key => $custom_field) {
                        if(array_key_exists($custom_field, $data)){
                            $value = isset($_POST[$custom_field]) ? $_POST[$custom_field] : '';
                            $data[$custom_field] = $value;
                            $error_data_arr = $errors->error_data;
                            $error_arr = $errors->errors;
                            foreach ($error_data_arr as $error_key => $error_data) {
                                if($custom_field == $error_data['id']){
                                    unset($error_data_arr[$error_key]);
                                    unset($error_arr[$error_key]);
                                    $errors->error_data = $error_data_arr;
                                    $errors->errors = $error_arr;
                                }
                            }
                        }
                    }
                }
                
                if($settings && !empty($settings)) {

					// Check shipping is enabled.
					if(array_key_exists('enable_shipping', $settings)) {

						if($settings['enable_shipping'] == 'yes') {

							// Check Multi-shipping is enabled.
							$settings_multi_ship = THWMA_Utils::get_setting_value('settings_multiple_shipping');
							$multi_shipping_enabled = isset($settings_multi_ship['enable_cart_shipping']) ? $settings_multi_ship['enable_cart_shipping']:'';
							if($multi_shipping_enabled == 'yes') {
								$settings_guest_urs = THWMA_Utils::get_setting_value('settings_guest_users');
								$enabled_guest_user = isset($settings_guest_urs['enable_guest_shipping']) ? $settings_guest_urs['enable_guest_shipping']:'';
								$enable_product_variation = isset($settings_multi_ship['enable_product_variation']) ? $settings_multi_ship['enable_product_variation']:'';
								if($enabled_guest_user == 'yes') {

									// Enable multi-shipping on checkout page.
							        $enable_multi_ship = isset($_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP]) ? $_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP]:'';

							        if($enable_multi_ship == 'yes') {

										// Case of shipping fields are not filled.
										if(!empty($errors->errors) && is_array($errors->errors)) {
											foreach ($errors->errors as $key => $value) {
												if(substr($key, 0, strlen('shipping')) == 'shipping') {
													unset($errors->errors[$key]);
												}
											}
										}

										if(!empty($errors->error_data) && is_array($errors->error_data)) {
                                            foreach ($errors->error_data as $key => $value) {
												if(substr($key, 0, strlen('shipping')) == 'shipping') {
													$errors->errors['multiple_shipping'] = array('0' =>'Addresses are not chosen for some of the products. Please choose an Address for all products.');
												}
											}
											return $errors;
										}

										// Case of shipping fields are filled.
										if(!empty(WC()->cart->get_cart()) && is_array(WC()->cart->get_cart())) {
                                            foreach ( WC()->cart->get_cart() as $cart_item ) {
												if(array_key_exists('variation_id', $cart_item)) {
													if($cart_item['variation_id'] !== 0){
														if($enable_product_variation == 'yes'){
														 	if(!array_key_exists('product_shipping_address', $cart_item)) {
                                                                $errors->errors['multiple_shipping'] = array('0' =>'Addresses are not chosen for some of the products. Please choose an Address for all products.');
													        } else {
													        	$multi_shipping_adr = $cart_item['product_shipping_address'];
													        	if($multi_shipping_adr == null){
													        		$errors->errors['multiple_shipping'] = array('0' =>'Addresses are not chosen for some of the products. Please choose an Address for all products.');
																}
													        }
														} else {
													    }
													} else {
														$excluding_pdts = $this->multiship_excludeing_products();
														$product_id = isset($cart_item["product_id"]) ? $cart_item["product_id"] : '';
														if(!empty($excluding_pdts)) {
                                                            if(!in_array($product_id, $excluding_pdts)) {
																if(!array_key_exists('product_shipping_address', $cart_item)) {
                                                                    $errors->errors['multiple_shipping'] = array('0' =>'Addresses are not chosen for some of the products. Please choose an Address for all products.');
																} else {
                                                                    $multi_shipping_adr = $cart_item['product_shipping_address'];
														        	if($multi_shipping_adr == null){
														        		$errors->errors['multiple_shipping'] = array('0' =>'Addresses are not chosen for some of the products. Please choose an Address for all products.');
																	}
														        }

															}
														} else {
                                                            if(!array_key_exists('product_shipping_address', $cart_item)) {
                                                                $errors->errors['multiple_shipping'] = array('0' =>'Addresses are not chosen for some of the products. Please choose an Address for all products.');
															} else {
                                                                $multi_shipping_adr = $cart_item['product_shipping_address'];
													        	if($multi_shipping_adr == null){
													        		$errors->errors['multiple_shipping'] = array('0' =>'Addresses are not chosen for some of the products. Please choose an Address for all products.');
																}
													        }
													    }
												    }
												}
											}
										    return $errors;
										}
									}
								}
							}
						}
					}
				}
			}
		}

		/**
         * Function for multi-ship excluding produts lists.
         *
         * @param string $data The given item datas
         * @param string $errors The existing errors
         *
         * @return string
         */
		public function multiship_excludeing_products() {
            $settings = THWMA_Utils::get_setting_value('settings_multiple_shipping');
			$settings_guest_urs = THWMA_Utils::get_setting_value('settings_guest_users');
			if($settings && !empty($settings)) {
				$cart_shipping_enabled = isset($settings['enable_cart_shipping']) ? $settings['enable_cart_shipping'] : '';
				$enable_product_variation = isset($settings['enable_product_variation']) ? $settings['enable_product_variation'] : '';
				$enabled_guest_user = isset($settings_guest_urs['enable_guest_shipping']) ? $settings_guest_urs['enable_guest_shipping'] : '';
				$exclude_products = isset($settings['exclude_products']) ? $settings['exclude_products'] : '';
				$exclude_category = isset($settings['exclude_category']) ? $settings['exclude_category'] : '';
				$excl_pdt_ids = array();
				if(!empty($exclude_products) && is_array($exclude_products)) {
					foreach ($exclude_products as $ex_key => $ex_value) {
						$product_obj = get_page_by_path($ex_value, OBJECT, 'product');
						if($product_obj != '') {
							$excl_pdt_ids[] = $product_obj->ID;
						}
					}
				}
				$category_pdt_id = array();
				if(!empty($exclude_category) && is_array($exclude_category)) {
					foreach ($exclude_category as $ex_cat_key => $ex_cat_value) {
						$args = array('post_type' => 'product', 'product_cat' => $ex_cat_value, 'orderby' =>'rand');
						if(!empty($args)) {
							$loop = new WP_Query($args);
							while ($loop->have_posts()) : $loop->the_post();
							global $product;
							$category_pdt_id[] = get_the_ID();
							endwhile;wp_reset_query();
						}
					}
				}
				$ex_pdt_ids = array_merge($excl_pdt_ids, $category_pdt_id);
			}
			return $ex_pdt_ids;
		}

        /**
         * Function for set first address from checkout(checkout page)
         *
         * @param integer $user_id The user id
         * @param string $type The address type
         */
		public function set_first_address_from_checkout($user_id, $type) {
            $custom_addresses = get_user_meta($user_id, THWMA_Utils::ADDRESS_KEY, true);
			$custom_address = THWMA_Utils::get_custom_addresses($user_id, $type);
			$checkout_address_key = isset($_POST['thwma_hidden_field_'.$type]) ? sanitize_text_field($_POST['thwma_hidden_field_'.$type]) : '';
			if(!$custom_address && $checkout_address_key == 'add_address') {
				$custom_address = array();
				$custom_addresses = is_array($custom_addresses) ? $custom_addresses : array();
				$default_address = THWMA_Utils::get_default_address($user_id, $type);
				if(array_filter($default_address) && (count(array_filter($default_address)) > 2)) {
					if(!array_key_exists($type.'_heading', $default_address)) {
						$default_address[$type.'_heading'] = esc_html__('', 'woocommerce-multiple-addresses-pro');
					}
					$custom_address['address_0'] = $default_address;
					$custom_addresses[$type] = $custom_address;
					$default_set_address = THWMA_Utils::get_custom_addresses($user_id, 'default_'.$type);
					if(!$default_set_address){
						$custom_addresses['default_'.$type] = 'address_0';
					}
					update_user_meta($user_id, THWMA_Utils::ADDRESS_KEY, $custom_addresses);
				}
			}
		}

        /**
         * Localisation of address formats (address override)
         *
         * @param array $formats The address format info
         *
         * @return array
         */
        public function localisation_address_formats($formats) {
            $address_formats_str = THWMA_Utils:: get_advanced_settings_value('address_formats');
            $custom_formats = array();
            if(!empty($address_formats_str)) {
                $address_formats_arr = explode("|", $address_formats_str);
                if(is_array($address_formats_arr) && !empty($address_formats_arr)) {
                    foreach($address_formats_arr as $address_format) {
                        if(!empty($address_format)) {
                            $format_arr = explode("=>", $address_format);
                            if(is_array($format_arr) && count($format_arr) == 2) {
                                $frmt = str_replace('\n', "\n", $format_arr[1]);
                                $custom_formats[trim($format_arr[0])] = $frmt;
                            }
                        }
                    }
                }
            }
            if(is_array($formats) && $custom_formats && is_array($custom_formats)) {
                $formats = array_merge($formats, $custom_formats);
            }
            return $formats;
        }

        /**
         * Function for calculate shipping method( flat rate).
         */

        public function thwma_calculate_order_total($total, $cart_item){
            $user_id = get_current_user_id();
            $settings = THWMA_Utils::get_setting_value('settings_multiple_shipping');
            
            // Enable multi-shipping on checkout page.
            if (is_user_logged_in()) {
                $enable_multi_ship = get_user_meta($user_id, THWMA_Utils::USER_META_ENABLE_MULTI_SHIP, true);
            } else {
                $enable_multi_ship = isset($_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP]) ? wp_unslash( $_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP]) : '';
            }
            
            if($enable_multi_ship == 'yes') {
                $cart_count = $cart_item->get_cart_contents_count();
                $shipp_method = WC()->shipping->get_shipping_methods();
                $set_amount = array();
                $total_count = 0;

                foreach ($shipp_method as $key => $value) {
                    if ('flat_rate' ==  $value->id && isset($value->instance_settings) && isset($value->instance_settings['cost'])) {
                        if (strpos($value->instance_settings['cost'],'[qty]')) {
                            $set_amount[] = (int) filter_var($value->instance_settings['cost'], FILTER_SANITIZE_NUMBER_INT);
                        }   
                    }

                }
                
                if (!empty($set_amount) && !empty($cart_count)) {
                    $total_count = min($set_amount) * $cart_count;
                }
                
                $sub_total = $cart_item->subtotal;

                if (0 == $total_count) {
                    return $total;  
                }else{
                    return $total_count + $sub_total;
                }
                
            }else{
                return $total;
            }
            
        }

        // public function thwma_calculate_shipping_total($total){
        //     $user_id = get_current_user_id();
        //     $settings = THWMA_Utils::get_setting_value('settings_multiple_shipping');
        //     $order_ship_staus = isset($settings['order_shipping_status']) ? $settings['order_shipping_status'] : '';
            
        //     // Enable multi-shipping on checkout page.
        //     if (is_user_logged_in()) {
        //         $enable_multi_ship = get_user_meta($user_id, THWMA_Utils::USER_META_ENABLE_MULTI_SHIP, true);
        //     } else {
        //         $enable_multi_ship = isset($_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP]) ? $_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP]:'';
        //     }
        //     if('yes' == $enable_multi_ship && 'yes' == $order_ship_staus) {
        //         $shipp_method = WC()->shipping->get_shipping_methods();
        //         $set_amount = '';
        //         foreach ($shipp_method as $key => $value) {
        //             if ('flat_rate' ==  $value->id && isset($value->instance_settings) && isset($value->instance_settings['cost'])) {
        //                 if (strpos($value->instance_settings['cost'],'[qty]')) {
        //                     $set_amount = (int) filter_var($value->instance_settings['cost'], FILTER_SANITIZE_NUMBER_INT);
        //                 }elseif ($total ==  $value->$instance_settings['cost']) {
        //                     $set_amount = (int) filter_var($value->instance_settings['cost'], FILTER_SANITIZE_NUMBER_INT);
        //                 }
        //             }

        //         }
        //         $cart_count = WC()->cart->get_cart_contents_count();

        //         if (!empty($set_amount) && !empty($cart_count)) {
        //             $total = $set_amount * $cart_count;
        //         }
        //     }
        //     return $total;
        // }
        /**
         * Function for calculating shipping total (thankyou page, my-account, admin order).
         * If set flat rate as amount * [qty].
         * when 'palce order' button click change the shipping total.
         * 
         * @return intiger
         */
		public function thwma_calculate_shipping_total($total){
            $user_id = get_current_user_id();
			$settings = THWMA_Utils::get_setting_value('settings_multiple_shipping');
			$order_ship_staus = isset($settings['order_shipping_status']) ? $settings['order_shipping_status'] : '';
			
			// Enable multi-shipping on checkout page.
	        if (is_user_logged_in()) {
	        	$enable_multi_ship = get_user_meta($user_id, THWMA_Utils::USER_META_ENABLE_MULTI_SHIP, true);
	        } else {
	        	$enable_multi_ship = isset($_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP]) ? $_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP]:'';
	        }

	        if('yes' == $enable_multi_ship && 'yes' == $order_ship_staus) {
	        	$shipp_method = WC()->shipping->get_shipping_methods();
				$set_amount = '';

				foreach ($shipp_method as $key => $value) {
					if ('flat_rate' ==  $value->id && isset($value->instance_settings) && isset($value->instance_settings['cost'])) {
						if (strpos($value->instance_settings['cost'],'[qty]')) {
							$set_amount = (int) filter_var($value->instance_settings['cost'], FILTER_SANITIZE_NUMBER_INT);
						}	
					}

				}
				$cart_count = WC()->cart->get_cart_contents_count();

				if (!empty($set_amount) && !empty($cart_count)) {
					$total = $set_amount * $cart_count;
				}
	        }

	        return $total;
		}

        /**
         * Display addresses on thankyou page, my-account, admin order edit page and admin order preview page.
         *
         * @param array $formatted_meta The meta data info from ordered item
         * @param array $order_item The order item details
         *
         * @return array.
         */
        public function thwma_shipping_addresses_display_on_thankyou_page($formatted_meta, $order_item) {
            $settings = THWMA_Utils::get_setting_value('settings_multiple_shipping');
            $cart_shipping_enabled = isset($settings['enable_cart_shipping']) ? $settings['enable_cart_shipping']:'';

            if($cart_shipping_enabled == 'yes') {
                if(method_exists($order_item, 'get_product_id')) {
                    $product_id = $order_item->get_product_id();
                    $product = wc_get_product( $product_id );
                    if(empty($product)){
                        return $formatted_meta;
                    }
                    $downloadable = apply_filters( 'wmap_product_is_downloadable', $product->is_downloadable('yes'));
                    if (($product->is_virtual()) || ($downloadable)) {
                        return $formatted_meta;
                    }

                    $enable_multi_ship_data = '';
                    if(!empty($order_item)) {
                        $item_meta_data = $order_item->get_meta_data();

                        if(is_array($item_meta_data) && !empty($item_meta_data)){
                            foreach( $item_meta_data as $key => $data){
                                 $data = $data;
                                if ('thwma_order_shipping_data' === $data->key || 'thwma_order_shipping_method' === $data->key || 'thwma_order_shipping_address' === $data->key){
                                    $enable_multi_ship_data = 'yes';
                                }
                            }
                        }
                    }
                

                    if($settings && !empty($settings)) {
                        if($enable_multi_ship_data == 'yes') {
                            $formatted_meta = $this->thwma_shipping_thankyou_page($formatted_meta, $order_item);
                        }
                    }




                }
            }
            return $formatted_meta;
        }

        /**
		 * Core function to display thankyou page,my-account,admin order edit page and admin order preview page.
		 *
		 * @param array $formatted_meta The meta data info from ordered item
		 * @param array $order_item The order item details
         *
         * @return array.
		 */
		public function thwma_shipping_thankyou_page($formatted_meta, $order_item) {
            $meta_data = $order_item->get_meta_data();
			$meta_ship_adrsses = '';
			$user_id= get_current_user_id();
			$custom_fields = '';
			// foreach for checking custom CFE field exist in selected the address.
			if(!empty($meta_data) && is_array($meta_data)) {
				foreach ($meta_data as $id => $meta_array) {
					if('thwma_order_shipping_address' == $meta_array->key) {
						$meta_ship_adrsses = $meta_array->value;
					}
					$custom_fields = $this->get_custom_fields_to_display($custom_fields, $meta_ship_adrsses);
				}
			}

			if(!empty($meta_ship_adrsses) && is_array($meta_ship_adrsses)) {
				foreach($meta_ship_adrsses as $addr_key => $addr_data) {
					$addr_values = isset($addr_data["shipping_address"]) ? $addr_data["shipping_address"] : '';
					$shipp_addr_format = THWMA_Utils::get_formated_address('shipping', $addr_values);
					if(apply_filters('thwma_inline_address_display', true)) {
						// $separator = ', ';
						$pdt_shipp_addr_formated = THWMA_Utils::thwma_get_formatted_address($shipp_addr_format, ', ');
					} else {
						$pdt_shipp_addr_formated = THWMA_Utils::thwma_get_formatted_address($shipp_addr_format);
					}
					$addr_value = $pdt_shipp_addr_formated;
					$adrs_key = 'Shipping Address';

					// Set shipping address meta data.
					$formatted_meta = $this->set_formatted_meta_data($formatted_meta, $addr_value, $adrs_key, $addr_key, $order_item, $custom_fields);
				}

				// Custom fields display.
				$formatted_meta = $this->set_custom_fields_display($formatted_meta, $addr_value, $adrs_key, $addr_key, $order_item, $custom_fields);
				return $formatted_meta;
			} else {
				if(is_user_logged_in()) {
					$default_address = THWMA_Utils::get_default_address($user_id, 'shipping');
					if (THWMA_Utils::check_thwcfe_plugin_is_active()) {
						// Custom fields.
						$custom_fields = $this->get_shipping_custom_fields_from_addresses($default_address);
					}
					// $addr_values = $default_address;  // unnecessary code. @TODO: Remove this line later if there are no issues.
					$shipp_addr_format = THWMA_Utils::get_formated_address('shipping', $default_address);
					
					if(apply_filters('thwma_inline_address_display', true)) {
						// $separator = ', ';
						$pdt_shipp_addr_formated = THWMA_Utils::thwma_get_formatted_address($shipp_addr_format, ', ');
					} else {
						$pdt_shipp_addr_formated = THWMA_Utils::thwma_get_formatted_address($shipp_addr_format);
					}
					$addr_key = 'default';
					$addr_value = $pdt_shipp_addr_formated;
					$adrs_key = 'Shipping Address';

					// Set default shipping address meta data.
					$this->set_formatted_meta_data($formatted_meta, $addr_value, $adrs_key, $addr_key, $order_item, $custom_fields);

				    // Custom fields for deafult address.
				    $formatted_meta = $this->set_custom_fields_display($formatted_meta, $addr_value, $adrs_key, $addr_key, $order_item, $custom_fields);
				}
				return $formatted_meta;
			}
		}

		/**
		 * Function for add custom field to meta data.
		 *
		 * @param array $formatted_meta The existing formated meta data.
		 * @param array $addr_value The address values.
		 * @param array $adrs_key The address label.
		 * @param array $addr_key The address name.
		 * @param array $order_item The order details
		 * @param array $custom_fields The custom fields
         *
         * @return array.
		 */
		public function set_custom_fields_display($formatted_meta, $addr_value, $adrs_key, $addr_key, $order_item, $custom_fields) {
            if(!empty($custom_fields) && is_array($custom_fields)) {
				$custom_field_data = array();
				unset( $custom_fields['heading'] );
				foreach($custom_fields as $custom_key => $custom_val) {
					if(!empty($custom_fields[$custom_key])) {
						if(!is_array($custom_val)) {
							$custom_field_data[] = $custom_key.' : '.$custom_val;
						}
					}
				}
				if(apply_filters('thwma_inline_address_display', true)) {
					$custom_field_dt = implode(",  ", $custom_field_data);
				} else {
					$custom_field_dt = implode("<br/>  ", $custom_field_data);
				}
				$addr_key = 'custom_fields';
				$addr_value = $custom_field_dt;
				$adrs_key = 'Custom fields';
				$formatted_meta = $this->set_formatted_meta_data($formatted_meta, $addr_value, $adrs_key, $addr_key, $order_item, $custom_fields);
			}
			return $formatted_meta;
		}
		/**
		 * Function for add address fields to meta data.
		 *
		 * @param array $formatted_meta The existing formated meta data.
		 * @param array $addr_value The address values.
		 * @param array $adrs_key The address label.
		 * @param array $addr_key The address name.
		 * @param array $order_item The order details
		 * @param array $custom_fields The custom fields
         *
         * @return array.
		 */
		public function set_formatted_meta_data($formatted_meta, $addr_value, $adrs_key, $addr_key, $order_item, $custom_fields)  {
            if($adrs_key == 'Shipping Address') {
				if(!empty($formatted_meta) && is_array($formatted_meta)) {
					$formatted_meta = $this->update_formated_meta_data($formatted_meta, $addr_value, $adrs_key, $addr_key, $order_item, $custom_fields);
				} else {
					$formatted_meta = $this->prepare_formatted_meta_data($formatted_meta, $addr_value, $adrs_key, $addr_key, $order_item, $custom_fields);
				}
			} else if($adrs_key == 'Custom fields') {
				if(!empty($formatted_meta) && is_array($formatted_meta)) {
					$formatted_meta = $this->update_formated_meta_data($formatted_meta, $addr_value, $adrs_key, $addr_key, $order_item, $custom_fields);
				} else {
					$formatted_meta = $this->prepare_formatted_meta_data($formatted_meta, $addr_value, $adrs_key, $addr_key, $order_item, $custom_fields);
				}
			}
			return $formatted_meta;
		}

		/**
		 * Function for update the existing meta data.
		 *
		 * @param array $formatted_meta The existing formated meta data.
		 * @param array $addr_value The address values.
		 * @param array $adrs_key The address label.
		 * @param array $addr_key The address name.
		 * @param array $order_item The order details
		 * @param array $custom_fields The custom fields
         *
         * @return array.
		 */
		public function update_formated_meta_data($formatted_meta, $addr_value, $adrs_key, $addr_key, $order_item, $custom_fields) {
            if(!empty($formatted_meta) && is_array($formatted_meta)) {
				$data = array();
				foreach ($formatted_meta as $values) {
					$data[$values->key] = $values;
				}

				foreach ($formatted_meta as $key => $value) {
					if(!array_key_exists($adrs_key, $data)) {
						if($formatted_meta[$key]->key == $adrs_key) {
							$formatted_meta_data = $this->prepare_formatted_meta_data($formatted_meta, $addr_value, $adrs_key, $addr_key, $order_item, $custom_fields);
							$formatted_meta[$key] = $formatted_meta_data[$addr_key];
						} else {
							$formatted_meta = $this->prepare_formatted_meta_data($formatted_meta, $addr_value, $adrs_key, $addr_key, $order_item, $custom_fields);
						}
					}
				}
			}
			return $formatted_meta;
		}

		/**
		 * Function for prepare formated meta data.
		 *
		 * @param array $formatted_meta The existing formated meta data.
		 * @param array $addr_value The address values.
		 * @param array $adrs_key The address label.
		 * @param array $addr_key The address name.
		 * @param array $order_item The order details
		 * @param array $custom_fields The custom fields
         *
         * @return array.
		 */
		public function prepare_formatted_meta_data($formatted_meta, $addr_value, $adrs_key, $addr_key, $order_item, $custom_fields) {
            $zeros_eliminated_value = ltrim($addr_value, '0');
			$addrs_value = $zeros_eliminated_value;
			$product = is_callable(array($this, 'get_product')) ? $this->get_product() : false;
			$display_key   = '<span class="thwma-thku-ship-adr-name">'.wc_attribute_label($adrs_key, $product).'<span>';
			$display_value = wp_kses_post($addrs_value);
			$display_value = '<span class="thwma-thku-ship-adr">'.$display_value.'<span>';

			if($adrs_key == 'Shipping Address') {
				if($addr_key) {
				    $formatted_meta[$addr_key] = (object) array(
						'key'           => $adrs_key,
						'value'         => $addr_value,
						'display_key'   => apply_filters('woocommerce_order_item_display_meta_key', $display_key, $addrs_value, $order_item),
						'display_value' => wpautop(make_clickable(apply_filters('woocommerce_order_item_display_meta_value', $display_value, $addrs_value, $order_item))),
					);

				}

			}else if($adrs_key == 'Custom fields') {
				if(count(array_filter($custom_fields)) != 0){
					if($addr_value) {
					    $formatted_meta[$addr_key] = (object) array(
							'key'           => $adrs_key,
							'value'         => $addr_value,
							'display_key'   => apply_filters('woocommerce_order_item_display_meta_key', $display_key, $addrs_value, $order_item),
							'display_value' => wpautop(make_clickable(apply_filters('woocommerce_order_item_display_meta_value', $display_value, $addrs_value, $order_item))),
						);
					}
				}
			}

			// cammand for showing empty custom field in edit order page,(some cases).

			return $formatted_meta;
		}


		/**
		 * Function for get custom field to display.
		 *
		 * @param array $custom_fields The custom fields
		 * @param array $meta_ship_adrsses The shipping address data.
         *
         * @return array.
		 */
		public function get_custom_fields_to_display($custom_fields, $meta_ship_adrsses) {
		//	if (THWMA_Utils::check_thwcfe_plugin_is_active()) {
				if(!empty($meta_ship_adrsses) && is_array($meta_ship_adrsses)) {
					foreach($meta_ship_adrsses as $addr_key => $addr_data) {
						$user_id = get_current_user_id();
						$shipping_adrs = THWMA_Utils::get_custom_addresses($user_id, 'shipping', $addr_key);
						$custom_fields = $this->get_shipping_custom_fields_from_addresses($shipping_adrs);
					}
				}
		//	}
				// 
			return $custom_fields;
		}
		/**
         * Function for get custom fields from the address fields
         *
         * @param obj $shipping_adrs The whole address fields including field values
         *
         * @return array
         */
		public function get_shipping_custom_fields_from_addresses($shipping_adrs) {
            $default_fields = array(
				'shipping_heading' => '',
				'shipping_first_name'=> '',
			    'shipping_last_name' => '',
			    'shipping_company' 	=> '',
			    'shipping_country' 	=> '',
			    'shipping_address_1' => '',
			    'shipping_address_2' => '',
			    'shipping_city' 		=> '',
			    'shipping_state' 	=> '',
			    'shipping_postcode' 	=> ''
			);
			$custom_fields = '';
			if(!empty($shipping_adrs)) {
				$custom_fields = array_diff_key($shipping_adrs,$default_fields);
			}
			return $custom_fields;
		}

        /**
         * Function for override shipping address display section on thankyou pageand order edit page(back-end)
         *
         * @param array $raw_address The exsting shipping address
         * @param array $order_item The order item details
         *
         * @return array
         */
        public function thwma_overrides_shipping_address_section_on_thankyou_page($address, $raw_address, $order_item) {
            $settings = THWMA_Utils::get_setting_value('settings_multiple_shipping');
            $cart_shipping_enabled = isset($settings['enable_cart_shipping']) ? $settings['enable_cart_shipping'] : '';
            $user_id = get_current_user_id();
            if($cart_shipping_enabled == 'yes') {
                $enable_multi_ship_data = '';
                if (is_user_logged_in()) {
                    $enable_multi_ship_data = get_user_meta($user_id, THWMA_Utils::USER_META_ENABLE_MULTI_SHIP, true);
                } else {
                    $enable_multi_ship_data = isset($_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP]) ? $_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP] : '';
                }

                // Check multi-shipping is enable on the specific order.
                $enable_multi_ship_data = '';
                $item_meta_data = '';
                if(is_array($order_item->get_items()) && !empty($order_item->get_items())){
                    foreach( $order_item->get_items() as $item ){
                        $item_meta_data = $item->get_meta_data();
                    }
                    $data = '';
                    if(is_array($item_meta_data) && !empty($item_meta_data)){
                        foreach( $item_meta_data as $key => $data){
                            if('thwma_order_shipping_address' === $data->key || 'thwma_order_shipping_data' === $data->key){
                                $enable_multi_ship_data = true;
                            }
                            $data = $data;

                        }
                    }
                    if(!empty($data)) {
                        if(is_array($data->get_data()) && !empty($data->get_data())){
                            if(in_array('thwma_order_shipping_data', $data->get_data())) {
                                $enable_multi_ship_data = 'yes';
                            } else if($data->get_data()['key'] == 'thwma_order_shipping_method'){
                                $enable_multi_ship_data = 'yes';
                            }
                        }
                    }

                }

                $shipping_address_section = 'Multiple shipping is enabled.';
                $shipping_address_section = apply_filters('thwma_update_shipping_address_section', $shipping_address_section);

                if($enable_multi_ship_data == 'yes' || $enable_multi_ship_data == true) {
                    return esc_html__($shipping_address_section, 'woocommerce-multiple-addresses-pro');
                } else {
                    return $address;
                }
            } else {
                return $address;
            }
        }

        		/**
         * Function for add additional fields (Additional drop-down fields  sections-checkout page) .
         *
         * @param integer $section The section data.
         *
         * @return string.
         */
		public function add_additional_fields($section) {
            $section_name = $section->name;
			$enable_billing = THWMA_Utils::get_setting_value('settings_billing', 'enable_billing');
			$enable_shipping = THWMA_Utils::get_setting_value('settings_shipping', 'enable_shipping');
			$mapped_section = THWMA_Utils::get_maped_sections_settings_value($section_name, 'maped_section');
			$disable_adr_mngmnt = self::disable_address_mnagement();
			$customer_id = get_current_user_id();
			$same_address = THWMA_Utils::is_same_address_exists($customer_id, $mapped_section);
			//$default_address = $default_bil_address ? $default_bil_address : $same_address ;
			$default_address = $same_address;

			// Address limit.
			$address_limit = '';
            if($mapped_section) {
                $address_limit = THWMA_Utils::get_setting_value('settings_'.$mapped_section , $mapped_section.'_address_limit');
            }
            if (!is_numeric($address_limit)) {
                $address_limit = 0;
            }

			if(!($mapped_section == 'shipping' &&  (wc_ship_to_billing_address_only()  || !wc_shipping_enabled()))) {
				$customer_id = get_current_user_id();
				$settings = THWMA_Utils::get_setting_value('settings_'.$mapped_section);
				$display_type = $settings[$mapped_section.'_display'];
				$display_title = $settings[$mapped_section.'_display_title'];
				if(($mapped_section == 'shipping')&&($enable_shipping == 'yes') ||($mapped_section == 'billing')&&($enable_billing == 'yes')) {
					if($display_type == 'popup_display') {
						if($display_title == 'link') { ?>
							<a href='#' class='thma-popup-show-custom_link th-pop-link' onclick="thwma_show_custom_popup(event,'<?php echo $section_name; ?>', '<?php echo $mapped_section; ?>')" > <?php esc_html_e('Choose Different Address', 'woocommerce-multiple-addresses-pro') ?> </a><?php
						} else {
							$theme_class_name = $this->check_current_theme();
							$theme_class = $theme_class_name.'_tile_field'; ?>
							<div class="<?php echo $theme_class;?>">
								<div class = "add-address thwma-add-adr btn-checkout">
									<a  class="btn-add-adrs-checkout button primary is-outline" onclick = "thwma_show_custom_popup(event,'<?php echo $section_name; ?>', '<?php echo $mapped_section; ?>')"><?php esc_html_e('Choose Different Address', 'woocommerce-multiple-addresses-pro') ?>
										<!-- <button type="button"  class="btn-add-adrs-checkout" onclick = "thwma_show_custom_popup(event, '<?php echo $section_name; ?>', '<?php echo $mapped_section; ?>')">
										<?php //esc_html_e('Choose Different Address', 'woocommerce-multiple-addresses-pro') ?> -->
									</a>
									<!-- </button> -->
								</div>
							</div>
						<?php }
						$all_address = '';
						$html_address = $this->get_custom_tile_field($customer_id, $section, $mapped_section);
						$theme_class_name = $this->check_current_theme();
						$theme_class = $theme_class_name.'_tile_field';
						$all_address.= '<div id="thwma-custom-tile-field_'.$section_name.'" style="display:none;" class="'.$theme_class.'">'. $html_address.'</div>' ?>
						<div class="u-columns woocommerce-Addresses  addresses custom_section_address ">
							<?php echo $all_address; ?>
						</div>
					<?php } else {
						$section_name = $section->name;
						$custom_address = THWMA_Utils::get_custom_addresses($customer_id, $mapped_section);
						$options = array();
						$def_section_address =  $this->get_default_section_address($customer_id, $section_name);

						if($custom_address && is_array($custom_address)) {
							$address_count = count($custom_address);
							if($address_limit && ($address_count > 0)) {

								// Default address.
								foreach ($custom_address as $key => $address_values) {
									$adrsvalues_to_dd = array();
									if($key == $default_address) {
										if(array_key_exists($mapped_section.'_heading',$address_values)) {
											$heading = ($address_values[$mapped_section.'_heading'] != '') ? $address_values[$mapped_section.'_heading'] : esc_html__('', 'woocommerce-multiple-addresses-pro');
										}
										if(apply_filters('thwma_remove_dropdown_address_format', true)) {
											if(!empty($address_values) && is_array($address_values)) {
												foreach ($address_values as $adrs_key => $adrs_value) {
													if($adrs_key == $mapped_section.'_address_1' || $adrs_key == $mapped_section.'_address_2' || $adrs_key ==$mapped_section.'_city' || $adrs_key == $mapped_section.'_state' || $adrs_key == $mapped_section.'_postcode') {
														if($adrs_value) {
															$adrsvalues_to_dd[] = $adrs_value;
														}
													}
												}
											}
										} else {
											$type = $mapped_section;
											$separator = '</br>';
											$new_address = $custom_address[$def_section_address];
											$new_address_format = THWMA_Utils::get_formated_address($type, $new_address);
											$options_arr = THWMA_Utils::thwma_get_formatted_address($new_address_format);
											$adrsvalues_to_dd = explode('<br/>', $options_arr);
										}
									}
									$adrs_string = implode(', ', $adrsvalues_to_dd);
									//if((isset($heading)) && ($heading!= '') && (!is_array($heading))) {
									if(isset($heading) && $heading != '') {
										$options[$key] = $heading .' - '.$adrs_string;
									} else {
										$options[$key] = $adrs_string;
									}
									$options = array_filter($options);
								}

								// Custom addresses.
								$i = 0;

								$customer_id = get_current_user_id();
								$same_address = THWMA_Utils::is_same_address_exists($customer_id, $mapped_section);
								$get_default_address = $same_address;
								$no_of_tile_display = '';
								if(!empty($get_default_address)) {
									$no_of_tile_display = 1;
								} else {
									$no_of_tile_display = 0;
								}
								if($address_limit > $no_of_tile_display) {
									foreach ($custom_address as $key => $address_values) {
										if($key != $default_address) {
											if(isset($address_values[$mapped_section.'_heading'])) {
												if(array_key_exists($mapped_section.'_heading',$address_values)) {
													$heading = ($address_values[$mapped_section.'_heading'] != '') ? $address_values[$mapped_section.'_heading'] : esc_html__('', 'woocommerce-multiple-addresses-pro');
												}
											}
											$adrsvalues_to_dd = array();
											if(apply_filters('thwma_remove_dropdown_address_format', true)) {
												if(!empty($address_values) && is_array($address_values)){
													foreach ($address_values as $adrs_key => $adrs_value) {
														if($adrs_key == $mapped_section.'_address_1' || $adrs_key == $mapped_section.'_address_2' || $adrs_key ==$mapped_section.'_city' || $adrs_key == $mapped_section.'_state' || $adrs_key == $mapped_section.'_postcode') {
															if($adrs_value) {
																$adrsvalues_to_dd[] = $adrs_value;
															}
														}
													}
												}
											} else {
												$type = $mapped_section;
												$separator = '</br>';
												$new_address = $custom_address[$def_section_address];
												$new_address_format = THWMA_Utils::get_formated_address($type, $new_address);
												$options_arr = THWMA_Utils::thwma_get_formatted_address($new_address_format);
												$adrsvalues_to_dd = explode('<br/>', $options_arr);
											}
											$adrs_string = implode(', ', $adrsvalues_to_dd);
											//if((isset($heading)) && ($heading != '') && (!is_array($heading))) {
											if(isset($heading) && $heading != '') {
												$options[$key] = $heading .' - '.$adrs_string;
											} else {
												$options[$key] = $adrs_string;
											}
											// if($i >= $address_limit-2) {
		         //                                break;
		         //                            }
											// custom field drop-down.
		                                    if(!empty($get_default_address)){
												if($i >= $address_limit-2) {
								                    break;
								                }
								            } else {
								            	if($i >= $address_limit-1) {
								                    break;
								                }
								            }
		                                    $i++;
		                                }
									}
								}
							}
						}
						if(is_array($def_section_address)) {
							if(array_filter($def_section_address)) {
								$options['section_address'] = esc_html__('Default Address','woocommerce-multiple-addresses-pro');
							}
						}

						$alt_field = array(
							'required' => false,
							'class'    => array('form-row form-row-wide enhanced_select select2-selection th-select'),
							'clear'    => true,
							'type'     => 'select',
							//'required' => 'true',
							//'label'    => 'Choose Address',
							//'placeholder' =>esc_html__('Choose an Address..', ''),

							'options'  => array('' => esc_html__('Choose saved address&hellip;','woocommerce-multiple-addresses-pro'))+$options,
						);
						$this->thwma_woocommerce_form_field('thwma_'.$section_name, $alt_field);
					}
				}
			}
		}

		/**
         * Function for get additional section tile fields (Additional sections-checkout page).
         *
         * @param string $customer_id The setted delievery day.
         * @param string $section  Start date of the delivey.
         * @param string $maped_section  End date of the delivey.
         *
         * @return string.
         */
		public function get_custom_tile_field($customer_id, $section, $maped_section) {
            $custom_address = THWMA_Utils::get_custom_addresses($customer_id, $maped_section);
			$type = $maped_section;
			$section_name = $section->name;
			$section_address = array('section_address' => $this->get_default_section_address($customer_id, $section_name));
			//$new_address_format = self::get_formated_address('billing', $section_address);
			$section_key_param = "'".$section_name."'";
			$return_html = '';
			$add_class='';
			$address_type ="'". $maped_section."'";
			$add_list_class  =  "thwma-thslider-list  "  .$section_name ;
			$all_addresses = 	$custom_address;

			$default_set_address = THWMA_Utils::get_custom_addresses($customer_id, 'default_'.$type);
			$same_address = THWMA_Utils::is_same_address_exists($customer_id, $type);
			$default_address = $default_set_address ? $default_set_address : $same_address;
			$default_address = $same_address;

			// Address limit.
			$address_limit = '';
            if($type) {
                $address_limit = THWMA_Utils::get_setting_value('settings_'.$type , $type.'_address_limit');
            }
            if (!is_numeric($address_limit)) {
                $address_limit = 0;
            }

	        if(is_array($custom_address)) {
	        	if($section_address['section_address'] != null && array_filter($section_address['section_address'])) {
	        		$all_addresses = array_merge($section_address, $custom_address);
	        	}
	        } else {
				$all_addresses = array();
				if(is_array($section_address['section_address'])) {
					if(array_filter($section_address['section_address'])) {
						$def_address = THWMA_Utils::get_default_address($customer_id, $type);
						if(array_filter($def_address) && (count(array_filter($def_address)) > 2)) {
							$all_addresses ['selected_address'] = $def_address;
						}
						$all_addresses = array_merge($section_address, $all_addresses);
						//$all_addresses  = $section_address;
					} else {
						$def_address = THWMA_Utils::get_default_address($customer_id, $type);
						if(array_filter($def_address) && (count(array_filter($def_address)) > 2)) {
							$all_addresses ['selected_address'] = $def_address;
						}
					}
				} else {
					$def_address = THWMA_Utils::get_default_address($customer_id, $type);
					if(array_filter($def_address) && (count(array_filter($def_address)) > 2)) {
						$all_addresses ['selected_address'] = $def_address;
					}
				}
			}
			$address_count = is_array($all_addresses) ? count($all_addresses) : 0 ;
			//if($address_limit && ($address_count > 0)) {
			$return_html .= '<div class="thwma-thslider" id="thslider-'.esc_attr($section_name).'">';
				if($address_limit && ($address_count >= 0)) {
					if($all_addresses && is_array($all_addresses)) {
			           	$return_html .= '<div class="thwma-thslider-box">';
				           	$return_html .= '<div  class = "thwma-thslider-viewport '.esc_attr($type).'" >';
					           	$return_html .= '<ul class=" '.esc_attr($add_list_class).'">';

					           		// Default address.
					           		$action_row_html = '';
									$new_address = isset($all_addresses[$default_address]) ? $all_addresses[$default_address] : '';
									$def_new_address = $new_address;
									if(!empty($def_new_address)) {
										$new_address_format = THWMA_Utils::get_formated_address($type, $new_address);
										$options_arr = THWMA_Utils::thwma_get_formatted_address($new_address_format);
										$address_key_param = "'".$default_address."'";
										if(isset($heading) && $heading != '') {
											$heading_css = '<div class="address-type-wrapper row">
												<div title="'.esc_attr($heading).'" class="address-type '.esc_attr($address_type_css).'">'.esc_attr($heading).'</div>
												</div>
												<div class="cus-adrr-text thwma-adr-text address-text address-wrapper">'.$options_arr.'</div>';
										} else {
											$heading_css = '<div class="cus-adrr-text thwma-adr-text address-text address-wrapper wrapper-only">'.$options_arr.'</div>';
										}
										$add_class  = "thwma-thslider-item_c $section_name " ;
										$return_html .= '<li class="'.esc_attr($add_class).'" value="'. esc_attr($default_address).'" >
											<div class="thwma-adr-box address-box" data-index="0" data-address-id="">
												<div class="thwma-main-content">
													<div class="complete-aaddress">
														'.$heading_css.'

													</div>
													<div class="btn-continue address-wrapper">
														<a class="th-btn '.esc_attr($default_address).' button primary is-outline" onclick="thwma_populate_selected_section_address(event, this, '.esc_attr($address_key_param).', '.esc_attr($section_key_param).', '.esc_attr($address_type).')">
															<span>'.esc_html__('Choose This Address', 'woocommerce-multiple-addresses-pro').'</span>
														</a>
													</div>
												</div>'.$action_row_html.'</div>';
										$return_html .= '</li>';
									}

									$i = 1;
									$no_of_tile_display = '';
									if(!empty($def_new_address)) {
										$no_of_tile_display = 1;
									} else {
										$no_of_tile_display = 0;
									}
									if($address_limit > $no_of_tile_display) {
										foreach ($all_addresses as $address_key => $value) {
											if($default_address) {
												$is_default = ($default_address == $address_key) ? true : false;
											} else {
												$is_default = false;
											}
											if(!$is_default) {
												$new_address = $all_addresses[$address_key];
												$new_address_format = THWMA_Utils::get_formated_address($maped_section, $new_address);
												$options_arr = THWMA_Utils::thwma_get_formatted_address($new_address_format);
												$address_key_param = "'".$address_key."'";
												$heading = !empty($new_address[$maped_section.'_heading']) ? $new_address[$maped_section.'_heading'] : esc_html__('', self::THWMA_TEXT_DOMAIN) ;
												$action_row_html = '';
												$address_type_css = '';
												if($address_key == 'section_address') {
													$address_type_css = 'default';
													$heading = sprintf(esc_html__('Default ', 'woocommerce-multiple-addresses-pro'));
												}
												$add_class  = "thwma-thslider-item_c  $section_name " ;
												$add_class .= $i == 0 ? ' first' : '';
												//if((isset($heading)) && ($heading != '') && (!is_array($heading))) {
												if(isset($heading) && $heading != '') {
													$heading_css =	'<div class="address-type-wrapper row">
														<div title="'.esc_attr($heading).'" class="address-type  '.esc_attr($address_type_css).'">'.esc_attr($heading).'</div>
													</div>
													<div class="cus-adrr-text thwma-adr-text address-text address-wrapper">'.$options_arr.'</div>';
												} else {
													$heading_css =	'<div class="cus-adrr-text thwma-adr-text address-text address-wrapper wrapper-only">'.$options_arr.'</div>';
												}
													// <div class="th-btn '.$address_key.'" onclick="thwma_populate_selected_section_address(event, this, '.$address_key_param.', '.$section_key_param.', '.$address_type.')">
													// 	<span>'.esc_html__('Choose This Address', 'woocommerce-multiple-addresses-pro').'</span>
													// </div>
												$return_html .= '<li class="'.esc_attr($add_class).'" value="'. esc_attr($address_key).'" >
													<div class="thwma-adr-box address-box" data-index="'.$i.'" data-address-id="">
														<div class="thwma-main-content">
															<div class="complete-aaddress">
																'.$heading_css.'

															</div>
															<div class="btn-continue address-wrapper">
																<a class="th-btn '.esc_attr($address_key).' button primary is-outline" onclick="thwma_populate_selected_section_address(event, this, '.esc_attr($address_key_param).', '.esc_attr($section_key_param).', '.esc_attr($address_type).')">
																	<span>'.esc_html__('Choose This Address', 'woocommerce-multiple-addresses-pro').'</span>
																</a>
															</div>
														</div>'.$action_row_html.'
													</div>
												</li>';

												// if($i >= $address_limit-1) {
		          //                                   break;
		          //                               }

												// Custom address tile field.
												if($def_new_address) {
													if($i >= $address_limit-1) {
			                                            break;
			                                        }
			                                    } else {
			                                    	if($i >= $address_limit) {
			                                            break;
			                                        }
			                                    }
												$i++;
											}
										}
									}
								$return_html .= '</ul>';
							$return_html .= '</div>';
						$return_html .= '</div>';
						$return_html .= '<div class="control-buttons control-buttons-'.esc_attr($section_name).'">';
							if($address_limit>2) {
				            	$return_html .= '<div class="prev_'.esc_attr($section_name).' thwma-thslider-prev '.esc_attr($section_name).'"><i class="fa fa-angle-left fa-3x"></i></div>';
				            	$return_html .= '<div class="next_'.esc_attr($section_name).' thwma-thslider-next '.esc_attr($section_name).'"><i class="fa fa-angle-right fa-3x"></i></div>';
				            }
			            $return_html .= '</div>';
			        } else {
			        	$return_html .= '<div class="th-no-address-msg"  >	<span>'. esc_html__('No saved addresses found', 'woocommerce-multiple-addresses-pro').'</span>  </div>';
			        }
		        } else {
		        	$return_html .= '<div class="th-no-address-msg"  >	<span>'. esc_html__('No saved addresses found', 'woocommerce-multiple-addresses-pro').'</span>  </div>';
		        }
	        $return_html .= '</div>';
			return $return_html;
		}

		/**
         * Function for get default section address from checkout page(checkout page)
         *
         * @param string $user_id The User id
         * @param string $section_name The section name
         *
         * @return array
         */
		public function get_default_section_address($user_id, $section_name) {
            $section_fields  = THWMA_Utils::get_maped_sections_settings_value($section_name, 'map_fields');
			$section_address = array();
			if(is_array($section_fields) && !empty($section_fields)) {
				foreach ($section_fields as $default_field => $custom_field) {
					$section_address[$default_field] = get_user_meta($user_id, $custom_field, true);
				}
				return $section_address ;
			}
		}

		/**
         * Function for set disable address management(Using user role)
         *
         * @return string
         */
		public static function disable_address_mnagement() {
            $settings = THWMA_Utils::get_advanced_settings();
			if(!empty($settings)) {
				$user_roles = array();
				$current_user = array();
				$user = wp_get_current_user();
				$disable_adr_mngmt = isset($settings['disable_address_management']) ? $settings['disable_address_management'] : '';
				$user_roles = isset($settings['select_user_role']) ? $settings['select_user_role'] : '';
				$userroles = explode(',', $user_roles);
				$current_user = $user->roles;

				if($disable_adr_mngmt == 'yes') {
					if(!empty($user_roles)) {
						if(!empty($current_user) && is_array($current_user)){
							foreach($current_user as $cur_user) {
								if (in_array($cur_user, $userroles, TRUE)) {
									return true;
								}
							}
						}
					} else {
						return true;
					}
				}
			}
		}

		/**
         * Function for check current theme
         *
         * @return string
         */
		public function check_current_theme() {
            $current_theme = wp_get_theme();
		   	$current_theme_name = isset($current_theme['Template']) ? $current_theme['Template'] : '';
		   	$wrapper_class = '';
		  	if($current_theme_name) {
		   		$wrapper_class = str_replace(' ', '-', strtolower($current_theme_name));
		   		$theme_class_name = 'thwma_'.$wrapper_class;
		   	}
		   	return $theme_class_name;
		}

		/**
         * Function for create address form field
         *
         * @param string $key The key value
         * @param array $args The arguments of form field
         * @param string $value The field passing values
         * @param array $cart_item The cart item data
         *
         * @return string
         */
		public function thwma_woocommerce_form_field($key, $args, $value = null, $cart_item = null) {
            $defaults = array(
				'type'              => '',
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
			$field           = '';
			$label_id        = $args['id'];
			$sort            = $args['priority'] ? $args['priority'] : '';
			$field_container = '<p class="form-row %1$s" id="%2$s" data-priority="' . esc_attr($sort) . '">%3$s</p>';
			$field   = '';
			$options = '';
			$custom_attributes= array();
			if(is_cart()) {
				$product_id = $cart_item["product_id"];
				$cart_key = $cart_item["key"];
			} else {
				$product_id = '';
			}
			if (! empty($args['options']) && is_array($args['options'])) {
				foreach ($args['options'] as $option_key => $option_text) {
					// If we have a blank option, select2 needs a placeholder.
					if (empty($args['placeholder'])) {
						$args['placeholder'] = $option_text ? $option_text : esc_html__('Choose an option','woocommerce-multiple-addresses-pro');
					}
					$custom_attributes[] = 'data-allow_clear="true"';
					// if(is_cart()) {
					// 	$shipping_addr = isset($cart_item['product_shipping_address']) ? $cart_item['product_shipping_address']: '';
					// 	$value = $shipping_addr;
					// 	$options .= '<option value="' . esc_attr($option_key) . '" ' . selected($value, $option_key, false) . ' >' . esc_attr($option_text) . '</option>';
					// } else {
					if($option_key);
					$options .= '<option value="' . esc_attr($option_key) . '" ' . selected($value, $option_key, false) . ' >' . esc_attr($option_text) . '</option>';
					// }
				}
				// if(is_cart()) {
				// 	$field .= '<select name="' . esc_attr($key) . '" id="' . esc_attr($args['id']) . '" class="thwma-cart-shipping-options select ' . esc_attr(implode(' ', $args['input_class'])) . '" ' . implode(' ', $custom_attributes) . ' data-placeholder="' . esc_attr($args['placeholder']) . '" data-product_id="'.$product_id.'" data-cart_key="'.$cart_key.'">' . $options . '</select>';
				// } else {
					$field .= '<select name="' . esc_attr($key) . '" id="' . esc_attr($args['id']) . '" class="select ' . esc_attr(implode(' ', $args['input_class'])) . '" ' . implode(' ', $custom_attributes) . ' data-placeholder="' . esc_attr($args['placeholder']) . '">' . $options . '</select>';
				// }
			}
			if (! empty($field)) {
				$field_html = '';
				if(!is_cart()) { // Check is Cart page.
					if ($args['label']) {
						$field_html .= '<label for="' . esc_attr($label_id) . '" class="' . esc_attr(implode(' ', $args['label_class'])) . '">' . esc_html__($args['label'], 'woocommerce-multiple-addresses-pro')  .'</label>';
					}
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
			echo $field; // WPCS: XSS ok.
		}
		/**
         * Function for get tile fields (Checkout page) .
         *
         * @param integer $customer_id The user id.
         * @param string $type  The address type.
         *
         * @return string.
         */
		public function get_tile_field($customer_id, $type) {
            $oldcols = 1;
			$cols    = 1;
			// $custom_address = THWMA_Utils::get_custom_addresses($customer_id, $type) ? THWMA_Utils::get_custom_addresses($customer_id, $type) : array();
			$disable_adr_mngmnt = self::disable_address_mnagement();
			if($disable_adr_mngmnt != true){
				$disable_adr_mngmnt = apply_filters('disable_address_management_on_checkout', false);
			}
			if($disable_adr_mngmnt == true) {
				$disable_mngt_class = 'thwma_disable_adr_mngt';
				$disable_acnt_sec = 'disable_acnt_sec';
			} else {
				$disable_mngt_class = '';
				$disable_acnt_sec = '';
			}
			$custom_address = THWMA_Utils::get_custom_addresses($customer_id, $type);
			$default_set_address = THWMA_Utils::get_custom_addresses($customer_id, 'default_'.$type);
			$same_address = THWMA_Utils::is_same_address_exists($customer_id, $type);
			$default_address = $default_set_address ? $default_set_address : $same_address;
			$default_address = $same_address;
			$address_count = is_array($custom_address) ? count($custom_address) : 0 ;

			// Address limit.
			$address_limit = '';
            if($type) {
                $address_limit = THWMA_Utils::get_setting_value('settings_'.$type , $type.'_address_limit');
            }
            if (!is_numeric($address_limit)) {
                $address_limit = 0;
            }

			$return_html = '';
			$add_class = '';
			$address_type = "'$type'";
			$add_list_class  = ($type == 'billing') ? " thwma-thslider-list bill " : " thwma-thslider-list ship";

			$add_address_btn = '<div class="add-address thwma-add-adr '.esc_attr($disable_acnt_sec).'">
	            <button class="btn-add-address primary btn-different-address '.esc_attr($disable_mngt_class).'" onclick="thwma_add_new_address(event, this, '.$address_type.')">
	                <i class="fa fa-plus"></i> '.esc_html__('Add new address', 'woocommerce-multiple-addresses-pro').'
	            </button>
	        </div>';
	        // if(is_cart()) { // Check is Cart page.
	        // 	$add_address_btn = '<div class="add-address thwma-add-adr '.esc_attr($disable_acnt_sec).'">
		       //      <button class="btn-add-address primary button '.esc_attr($disable_mngt_class).'" onclick="thwma_add_new_shipping_address(event, this, '.$address_type.')">
		       //          <i class="fa fa-plus"></i> '.esc_html__('Add new address', 'woocommerce-multiple-addresses-pro').'
		       //      </button>
		       //  </div>';
	        // }

	        if(is_array($custom_address)) {
	        	$all_addresses = $custom_address;
	        } else {
				$all_addresses = array();
				$def_address = THWMA_Utils::get_default_address($customer_id, $type);

				if(array_filter($def_address) && (count(array_filter($def_address)) > 2)) {
					$all_addresses ['selected_address'] = $def_address;
				}
			}
			$total_address_count = count($all_addresses);
			// if($address_limit && ($total_address_count > 1)) {
			$return_html .= '<div class="thwma-thslider">';
				if($address_limit && ($total_address_count >= 1)) {
					if($all_addresses && is_array($all_addresses)) {
			           	$return_html .= '<div class="thwma-thslider-box">';
				           	$return_html .= '<div class="thwma-thslider-viewport '.esc_attr($type).'">';
					           	$return_html .= '<ul class=" '.esc_attr($add_list_class).'">';
									$i = 1;

									// Default address.
									$action_row_html = '';
									$new_address = isset($all_addresses[$default_address]) ? $all_addresses[$default_address] : '';
									$def_new_address = $new_address;
									if(!empty($def_new_address) && apply_filters('thwma_hide_default_address_section',true, $type, $all_addresses)) {
										$new_address_format = THWMA_Utils::get_formated_address($type, $new_address);
										$options_arr = THWMA_Utils::thwma_get_formatted_address($new_address_format);
										$address_key_param = "'".$default_address."'";
										$address_type_css = 'default';
										$heading = sprintf(esc_html__('Default', 'woocommerce-multiple-addresses-pro'));
										$action_row_html .= '<div class="thwma-adr-footer address-footer '.$address_type_css.'">
											<div class="th-btn btn-delete '.$disable_mngt_class.'"><span>'.esc_html__('Delete', 'woocommerce-multiple-addresses-pro').'</span></div>
											<div class="th-btn btn-default"><span>'.esc_html__('Default', 'woocommerce-multiple-addresses-pro').'</span></div>
										</div>';
										if(isset($heading) && $heading != '') {
											$heading_css = '<div class="address-type-wrapper row">
												<div title="'.$heading.'" class="address-type '.$address_type_css.'">'.$heading.'</div>
												</div>
												<div class="tile-adrr-text thwma-adr-text address-text address-wrapper">'.$options_arr.'</div>';
										} else {
											$heading_css = '<div class="tile-adrr-text thwma-adr-text address-text address-wrapper wrapper-only">'.$options_arr.'</div>';
										}
										$add_class  = "thwma-thslider-item $type " ;
										$return_html .= '<li class="'.$add_class.'" value="'. $default_address.'" >
											<div class="thwma-adr-box address-box" data-index="0" data-address-id="">
												<div class="thwma-main-content">
													<div class="complete-aaddress">
														'.$heading_css.'

													</div>
													<div class="btn-continue address-wrapper">
														<a class="th-btn button primary is-outline '.$default_address.'" onclick="thwma_populate_selected_address(event, this, '.$address_type.', '.$address_key_param.')">
															<span>'.esc_html__('Choose This Address', 'woocommerce-multiple-addresses-pro').'</span>
														</a>
													</div>
												</div>'.$action_row_html.'</div>';
										$return_html .= '</li>';
									}

									$no_of_tile_display = '';
									if(!empty($def_new_address)) {
										$no_of_tile_display = 2;
									} else {
										$no_of_tile_display = 1;
									}

									if($address_limit >= $no_of_tile_display) {
										foreach ($all_addresses as $address_key => $value) {
											// if(isset($value[$type.'_heading'])) {
											// 	if(!is_array($value[$type.'_heading'])) {
											// 		$new_address = $all_addresses[$address_key];
											// 		$new_address_format = THWMA_Utils::get_formated_address($type, $new_address);
											// 	}
											// }
											$new_address = $all_addresses[$address_key];
											$new_address_format = THWMA_Utils::get_formated_address($type, $new_address);
											$options_arr = THWMA_Utils::thwma_get_formatted_address($new_address_format);
											$address_key_param = "'".$address_key."'";
											$heading = !empty($new_address[$type.'_heading']) ? $new_address[$type.'_heading'] : esc_html__('', 'woocommerce-multiple-addresses-pro') ;

											if($default_address) {
												$is_default = ($default_address == $address_key) ? true : false;
											} else {
												$is_default = false;
											}
											$address_type_css = '';
											$action_row_html = '';
											if(!$is_default || empty($def_new_address)) {
												if($total_address_count>=1) {
													if(!empty($custom_address))	{
														$action_row_html .= '<div class="thwma-adr-footer address-footer">
															<div class="btn-delete '.$disable_mngt_class.'" data-index="0" data-address-id="" onclick="thwma_delete_selected_address(this, '.$address_type.', '.$address_key_param.')" title="'.esc_html__('Delete', 'woocommerce-multiple-addresses-pro').'">
																<span>'.esc_html__('Delete', 'woocommerce-multiple-addresses-pro').'</span>
															</div>
															<div class="btn-default" data-index="0" data-address-id="" onclick="thwma_set_default_address(this, '.$address_type.', '.$address_key_param.')" title="'.esc_html__('Default', 'woocommerce-multiple-addresses-pro').'">
																<span>'.esc_html__('Default', 'woocommerce-multiple-addresses-pro').'</span>
															</div>
														</div>';
													}
												} else{
													// $action_row_html .= '<div class="thwma-adr-footer address-footer '.$address_type_css.'">
													// 	<div class="th-btn btn-delete '.$disable_mngt_class.'"><span>'.esc_html__('Delete', 'woocommerce-multiple-addresses-pro').'</span></div>
													// 	<div class="btn-default" data-index="0" data-address-id="" onclick="thwma_set_default_address(this, '.$address_type.', '.$address_key_param.')" title="'.esc_html__('Default', 'woocommerce-multiple-addresses-pro').'">
													// 		<span>'.esc_html__('Default', 'woocommerce-multiple-addresses-pro').'</span>
													// 	</div>
													// </div>';
												}
												if(empty($def_new_address)) {
													if(empty($custom_address)) {
														$address_type_css = 'default';
														$heading = sprintf(esc_html__('Default', 'woocommerce-multiple-addresses-pro'));
														$action_row_html .= '<div class="thwma-adr-footer address-footer '.$address_type_css.'">
															<div class="th-btn btn-delete '.$disable_mngt_class.'"><span>'.esc_html__('Delete', 'woocommerce-multiple-addresses-pro').'</span></div>
															<div class="th-btn btn-default"><span>'.esc_html__('Default', 'woocommerce-multiple-addresses-pro').'</span></div>
														</div>';
													}
												}
											//if((isset($heading)) && ($heading != '') && (!is_array($heading))) {
												if(isset($heading) && $heading != '') {
													$heading_css = '<div class="address-type-wrapper row">
														<div title="'.$heading.'" class="address-type '.$address_type_css.'">'.$heading.'</div>
														</div>
														<div class="tile-adrr-text thwma-adr-text address-text address-wrapper">'.$options_arr.'</div>';
												} else {
													$heading_css = '<div class="tile-adrr-text thwma-adr-text address-text address-wrapper wrapper-only">'.$options_arr.'</div>';
												}
												//$add_class  = "u-columns address-single  $type " ;
												$add_class  = "thwma-thslider-item $type " ;
												// $add_class .= $is_default ? ' first' : '';
												$add_class .= $i == 1 ? ' first' : '';
												$return_html .= '<li class="'.$add_class.'" value="'. $address_key.'" >
													<div class="thwma-adr-box address-box" data-index="'.$i.'" data-address-id="">
														<div class="thwma-main-content">
															<div class="complete-aaddress">
																'.$heading_css.'

															</div>
															<div class="btn-continue address-wrapper">
																<a class="th-btn button primary is-outline '.$address_key.'" onclick="thwma_populate_selected_address(event, this, '.$address_type.', '.$address_key_param.')">
																	<span>'.esc_html__('Choose This Address', 'woocommerce-multiple-addresses-pro').'</span>
																</a>
															</div>
														</div>'.$action_row_html.'</div>';
												$return_html .= '</li>';
												// if($i >= $address_limit-1) {
		          //                                   break;
		          //                               }
												// checkout tile fields.
												if(!empty($def_new_address)){
													if($i >= $address_limit-1) {
			                                            break;
			                                        }
			                                    } else {
			                                    	if($i >= $address_limit) {
			                                            break;
			                                        }
			                                    }
												$i++;
											}
										}
									}
								$return_html .= '</ul>';
							$return_html .= '</div>';
						$return_html .= '</div>';
						$return_html .= '<div class="control-buttons control-buttons-'.$type.'">';
						if($address_count && $address_count > 3) {
							if($address_limit>3) {
				            	$return_html .= '<div class="prev thwma-thslider-prev '.$type.'"><i class="fa fa-angle-left fa-3x"></i></div>';
				            	$return_html .= '<div class="next thwma-thslider-next '.$type.'"><i class="fa fa-angle-right fa-3x"></i></div>';
				            }
			            }

			           	$return_html .= '</div>';
			           		if(((int)($address_limit)) > $address_count) {
			           		$return_html .= $add_address_btn;
			           	}
			        }
		        } else {
		        	$return_html .= '<div class="th-no-address-msg"  >	<span>'.esc_html__('No saved addresses found', 'woocommerce-multiple-addresses-pro').'</span>  </div>';
		        	//if(is_cart()) {
		        		$return_html .= $add_address_btn;
		        	//}
		        }
	        $return_html .= '</div>';
			return $return_html;
		}
		/**
		 * Get current user saved addresses.
		 *
		 * @param array $ship_addresses The shipping address info
		 *
		 * @return string
		 */
		public static function get_user_addresses($ship_addresses) {
            $user_id = get_current_user_id();
	    	$shipping_address = array();
	    	$default_shipping_address  = array();
            

	    	// Address data from user.
	    	$user_address_data = get_user_meta($user_id, THWMA_Utils::ADDRESS_KEY, true);
	    	
			// Address details.
	    	if(!empty($user_address_data) && is_array($user_address_data)) {
		    	foreach($user_address_data as $index => $ship_data) {
		    		$shipping_address = isset($user_address_data['shipping'])?$user_address_data['shipping']:'';
		    		$default_shipping_address = isset($user_address_data['default_shipping'])?$user_address_data['default_shipping'] : '';
		    	}
		    }
            if($shipping_address && is_array($shipping_address)) {
		    	foreach($shipping_address as $key => $value) {
		    		if($key == $ship_addresses) {
	    				return $value;
	    			}
		    	}
		    }
		    if($default_shipping_address && is_array($default_shipping_address)) {
		    	foreach($default_shipping_address as $key => $value) {
		    		if($key == $ship_addresses) {
	    				return $value;
	    			}
		    	}
		    }
		}
		/**
         * Function for update address from checkout(checkout page)
         *
         * @param string $type The type of the address
         * @param string $address_key The address key value
         * @param array $posted_data The posted data info
         * @param string $default_key The passed default key
         */
		public function update_address_from_checkout($type, $address_key, $posted_data, $default_key) {
            $user_id = get_current_user_id();
			$added_address = array();
			$added_address = $this->prepare_order_placed_address($user_id, $posted_data, $type);
			$heading = THWMA_Utils::get_custom_addresses($user_id, $type, $address_key, $type.'_heading');
            $added_address[$type.'_heading'] = $heading ? $heading : esc_html__('','woocommerce-multiple-addresses-pro');
			if($address_key == 'add_address') {
				self::save_address_to_user_from_checkout($added_address, $type);
			}
			elseif(($default_key) && (empty($address_key)|| ($address_key == $default_key))) {
				if(!apply_filters('disable_address_management_on_checkout', false)) {
					THWMA_Utils::update_address_to_user($user_id, $added_address, $type, $default_key);
				}
			} elseif($address_key && ($address_key != 'selected_address')) {
				if(!apply_filters('disable_address_management_on_checkout', false)) {
					THWMA_Utils::update_address_to_user($user_id, $added_address, $type, $address_key);
					$this->update_address_to_user_from_checkout($user_id, $added_address, $type, $address_key);
				}
			}
		}
		/**
         * Function for save address to user from checkout(checkout page)
         *
         * @param array $address The address information
         * @param string $type The type of the address
         */
		private function save_address_to_user_from_checkout($address, $type) {
            $user_id = get_current_user_id();
			$custom_addresses = get_user_meta($user_id,THWMA_Utils::ADDRESS_KEY, true);
			$custom_addresses = is_array($custom_addresses) ? $custom_addresses : array();
			$saved_address = THWMA_Utils::get_custom_addresses($user_id, $type);
			if(!is_array($saved_address)) {
				$custom_address = array();
				$default_address = THWMA_Utils::get_default_address($user_id, $type);
				if(!array_key_exists($type.'_heading', $default_address)) {
					$default_address[$type.'_heading'] = esc_html__('', 'woocommerce-multiple-addresses-pro');
				}
				$custom_address['address_0'] = $default_address;
				//$custom_address['address_1'] = $address;
				$custom_key = THWMA_Utils::get_custom_addresses($user_id, 'default_'.$type);
				$custom_addresses[$type] = $custom_address;
			} else {
				if(is_array($saved_address)) {
					if(isset($custom_addresses[$type])) {
						$exist_custom = $custom_addresses[$type];
						$new_key_id = THWMA_Utils::get_new_custom_id($user_id, $type);
						$new_key = 'address_'.$new_key_id;
						$custom_address[$new_key] = $address;
						$custom_key = THWMA_Utils::get_custom_addresses($user_id, 'default_'.$type);
						if(!$custom_key) {
							$custom_addresses['default_'.$type] = $new_key;
						}
						$custom_addresses[$type] = array_merge($exist_custom, $custom_address);
					}
				}
			}

			update_user_meta($user_id,THWMA_Utils::ADDRESS_KEY, $custom_addresses);
		}
		/**
         * Function for prepare the address after place order process(checkout page)
         *
         * @param integer $user_id The user id
         * @param string $posted_data The posted datas
         * @param string $type The address type
         */
		private function prepare_order_placed_address($user_id, $posted_data, $type) {
            $fields = THWMA_Utils::get_address_fields($type);
			$new_address = array();
			if(!empty($fields) && is_array($fields)) {
				foreach ($fields as $key) {
					if(isset($posted_data[$key])) {
						$new_address[$key] = is_array($posted_data[$key]) ? implode(', ', $posted_data[$key]) : $posted_data[$key];
					}
				}
			}
			return $new_address;
		}
		/**
         * Function for update address to user from checkout(checkout page)
         *
         * @param integer $user_id The user id
         * @param array $address The address information
         * @param string $type The type of the address
         * @param string $address_key The address key
         */
		private function update_address_to_user_from_checkout($user_id, $address, $type, $address_key) {
            $custom_addresses = get_user_meta($user_id,THWMA_Utils::ADDRESS_KEY, true);
			$exist_custom = isset($custom_addresses[$type]) ? $custom_addresses[$type] : array();
			$custom_address[$address_key] = $address;
			$custom_key = THWMA_Utils::get_custom_addresses($user_id, 'default_'.$type);
			if(!$custom_key && is_array($custom_addresses)) {
				$custom_addresses['default_'.$type] = $address_key;
			}
			if(is_array($custom_addresses)){
				$custom_addresses[$type] = array_merge($exist_custom, $custom_address);
			}
			update_user_meta($user_id,THWMA_Utils::ADDRESS_KEY, $custom_addresses);
		}

		/**
		 * Get current guest user saved addresses (Guest users).
		 *
		 * @param array ship_addresses The Guest user addresses
		 *
		 */
		public static function get_guest_user_addresses($ship_addresses) {
            $user_address_data = array();
			if(isset($_COOKIE[THWMA_Utils::GUEST_USER_SHIPPING_ADDR])) {
				$shipping_address = $_COOKIE[THWMA_Utils::GUEST_USER_SHIPPING_ADDR];
				$shipping_address = preg_replace('!s:(\d+):"(.*?)";!', "'s:'.strlen('$2').':\"$2\";'", $shipping_address);
				$user_address_data = unserialize(base64_decode($shipping_address));
			}

			// Address details.
	    	if($user_address_data) {
	    		if(is_array($user_address_data)) {
			    	foreach($user_address_data as $index => $ship_data) {
			    		$shipping_address = isset($user_address_data['shipping'])?$user_address_data['shipping']:'';
			    		$default_shipping_address = isset($user_address_data['default_shipping'])?$user_address_data['default_shipping'] : '';
			    	}
			    }
                if(!empty($shipping_address) && is_array($shipping_address)) {
			    	foreach($shipping_address as $key => $value) {
			    		if($key == $ship_addresses) {
		    				return $value;
		    			}
			    	}
			    }
			    if(!empty($default_shipping_address) && is_array($default_shipping_address)) {
			    	foreach($default_shipping_address as $key => $value) {
			    		if($key == $ship_addresses) {
		    				return $value;
		    			}
			    	}
			    }
		    }
		}
    }
endif;
