<?php
/**
 * The admin general settings page functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-multiple-addresses-pro
 * @subpackage woocommerce-multiple-addresses-pro/src/admin
 */

namespace Themehigh\WoocommerceMultipleAddressesPro\admin;

use \Themehigh\WoocommerceMultipleAddressesPro\includes\utils\THWMA_Utils;
use \Themehigh\WoocommerceMultipleAddressesPro\admin\THWMA_Admin_Settings;
use \Themehigh\WoocommerceMultipleAddressesPro\includes\THWMA_i18n;

if(!defined('WPINC')) {
	die;
}

if(!class_exists('THWMA_Admin_Settings_General')) :

	/**
	 * The general admin settings class.
	 */
	class THWMA_Admin_Settings_General extends THWMA_Admin_Settings {
		protected static $_instance = null;
		private $cell_props_L = array();
		private $cell_props_R = array();
		private $cell_props_CB = array();
		private $cell_props_CBS = array();
		private $cell_props_CBL = array();
		private $cell_props_CP = array();
		private $cell_props_TP = array();
		private $checkbox_cell_props = array();
		private $settings_props = array();

	/**
     * Constructor
     */
		public function __construct() {
			parent::__construct('general_settings', '');
			$this->page_id    = 'general_settings';
			$this->section_id = 'billing_shipping';
			$this->init_constants();
			THWMA_Admin_Utils::prepare_sections_and_fields();
		}

	/**
	 * Function for instance.
	 *
	 * @return void
	 */
		public static function instance() {
			if(is_null(self::$_instance)) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		// public function get_sections() {
		// 	$sections = array(
		// 		''        => esc_html__('Shipping zones', 'woocommerce'),
		// 		'options' => esc_html__('Shipping options', 'woocommerce'),
		// 		'classes' => esc_html__('Shipping classes', 'woocommerce'),
		// 	);
		// 	return $this->sections;
		// }

	/**
     * Function for initialise constants.
     */
	public function init_constants() {
		$this->cell_props_L = array(
			'label_cell_props' => 'width="23%"',
			'input_cell_props' => 'width="30%"',
			'input_float' => 'left',
			'input_width' => '250px',
		);
		$this->cell_props_R = array(
			'label_cell_props' => 'width="30%"',
			'input_cell_props' => 'width="10%"',
			'input_width' => '250px',
		);
		$this->cell_props_CB = array(
			'label_props' => 'style="margin-right: 40px;width=34%;"',
		);
		$this->cell_props_CBS = array(
			'label_props' => 'style="margin-right: 15px;"',
		);
		$this->cell_props_CBL = array(
			'label_props' => 'style="margin-right: 52px;"',
		);
		$this->cell_props_CP = array(
			'label_cell_props' => 'width="13%"',
			'input_cell_props' => 'width="34%"',
			'input_width' => '225px',
		);
		$this->cell_props_TP = array(
			'label_cell_props' => 'width="25%"',
			'input_cell_props' => 'width="5%"',
			'input_width' => '56px',
		);
		$this->checkbox_cell_props = array('cell_props' => '3');
		$this->settings_props = $this->get_field_form_props();
	}

	/**
     * Function for get field from props.
     */
	public function get_field_form_props() {
		$hint_name = esc_html__('Used to save values in database. Name must begin with a lowercase letter.', 'woocommerce-multiple-addresses-pro');
		$hint_handle_fee = esc_html__('Handling fee for products shipping addresses different from checkout shipping address.', 'woocommerce-multiple-addresses-pro');
		$pickup_adr1 = esc_html__('The street address for your business location.', 'woocommerce-multiple-addresses-pro');
		$pickup_adr2 = esc_html__('An additional, optional address line for your business location.', 'woocommerce-multiple-addresses-pro');
		$pickup_city = esc_html__('The city in which your business is located.', 'woocommerce-multiple-addresses-pro');
		$pickup_country =  esc_html__('The country and state or province, if any, in which your business is located.', 'woocommerce-multiple-addresses-pro');
		$pickup_postcode =  esc_html__('The postal code, if any, in which your business is located.', 'woocommerce-multiple-addresses-pro');
		$time_period = esc_html__('Set time duration for guest users to save their addresses (enter Days in DD, Hours in HH and Minutes in MM format)', 'woocommerce-multiple-addresses-pro');

		$display_styles = array(
			'dropdown_display' => esc_html__('Drop Down', 'woocommerce-multiple-addresses-pro'),
			'popup_display' => esc_html__('Pop Up', 'woocommerce-multiple-addresses-pro'),
			//'accordion' => 'Accordion',
		);
		$display_positions = array(
	    	'above' => esc_html__('Above the form', 'woocommerce-multiple-addresses-pro'),
			'below' => esc_html__('Below the form', 'woocommerce-multiple-addresses-pro')
	    );
		$link_types = array(
       		'button' => esc_html__('Button', 'woocommerce-multiple-addresses-pro'),
       		'link'=>esc_html__('Link', 'woocommerce-multiple-addresses-pro')
	    );

      	$exclude_catg = array();
		$orderby = 'name';
		$order = 'asc';
		$hide_empty = false ;
		$parentid = get_queried_object_id();
		$cat_args = array(
		    'orderby'    => $orderby,
		    'order'      => $order,
		    'hide_empty' => $hide_empty,
		    'parent' 	 => $parentid
		);
		$product_categories = get_terms('product_cat', $cat_args);
		$exclude_catgs = array();
		if(!empty($product_categories) && is_array($product_categories)) {
			foreach ($product_categories as $key => $category) {
				$catg_name = $category->name;
				$catg_slug = $category->slug;
				//$exclude_catgs[$catg_name] = $catg_slug;
				$exclude_catgs[$catg_slug] = $catg_name;
			}
			$exclude_catg = $exclude_catgs;
		}
		$time_limit = array(
			'minute' => esc_html__('Minute', 'woocommerce-multiple-addresses-pro'),
			'hour' => esc_html__('Hour', 'woocommerce-multiple-addresses-pro'),
			'day' => esc_html__('Day', 'woocommerce-multiple-addresses-pro'),
			// 'month' => esc_html__('Month', 'woocommerce-multiple-addresses-pro'),
			// 'year' => esc_html__('Year', 'woocommerce-multiple-addresses-pro')
		);

		$limit = apply_filters('thwma_multiple_address_limit', 20);
      	$settings_props_billing = array(
			'enable_billing' => array('name'=>'enable_billing', 'label' => esc_html__('Enable multiple addresses', 'woocommerce-multiple-addresses-pro'), 'type'=>'checkbox', 'value'=>'yes', 'checked'=>1),
			'billing_display' => array('type'=>'select', 'name'=>'billing_display', 'label'=>esc_html__('Display type', 'woocommerce-multiple-addresses-pro'), 'options'=>$display_styles , 'value'=> 'popup_display'),
			'billing_display_position' => array('type'=>'select', 'name'=>'billing_display_position', 'label'=>esc_html__('Display position', 'woocommerce-multiple-addresses-pro'), 'options'=>$display_positions , 'value'=> 'above'),
			'billing_display_title' => array('type'=>'select', 'name'=>'billing_display_title', 'label'=>esc_html__('Display style', 'woocommerce-multiple-addresses-pro'), 'options'=>$link_types, 'value'=>''),
			'billing_display_text' => array('type'=>'text', 'name'=>'billing_display_text', 'label'=>esc_html__('Display text', 'woocommerce-multiple-addresses-pro'), 'value'=> esc_html__('Billing with a different address', 'woocommerce-multiple-addresses-pro')),
			'billing_address_limit' => array('type'=>'number', 'name'=>'billing_address_limit', 'label'=>esc_html__('Address limit', 'woocommerce-multiple-addresses-pro'), 'id'=>'thb_limit_value', 'required' => '', 'value'=> $limit, 'min'=>1),
     	);

      	$settings_props_shipping = array(
			'enable_shipping'=> array('type'=>'checkbox', 'name'=>'enable_shipping', 'label' => esc_html__('Enable multiple addresses', 'woocommerce-multiple-addresses-pro'), 'checked'=>1, 'value'=>'yes'),
			'shipping_display' => array('type'=>'select', 'name'=>'shipping_display', 'label'=>esc_html__('Display type', 'woocommerce-multiple-addresses-pro'), 'value'=> 'popup_display' , 'options'=>$display_styles),
			'shipping_display_position' => array('type'=>'select', 'name'=>'shipping_display_position', 'label'=>esc_html__('Display position', 'woocommerce-multiple-addresses-pro'), 'options'=>$display_positions , 'value'=> 'above'),
			'shipping_display_title' => array('type'=>'select', 'name'=>'shipping_display_title', 'label'=>esc_html__('Display style', 'woocommerce-multiple-addresses-pro'), 'options'=>$link_types, 'value'=>''),
			'shipping_display_text' => array('type'=>'text', 'name'=>'shipping_display_text', 'label'=>esc_html__('Display text', 'woocommerce-multiple-addresses-pro'), 'value'=>esc_html__('Shipping to a different address', 'woocommerce-multiple-addresses-pro')),
			'shipping_address_limit' => array('type'=>'number', 'name'=>'shipping_address_limit', 'label'=>esc_html__('Address limit', 'woocommerce-multiple-addresses-pro'), 'id'=>'ths_limit_value', 'required' => '', 'value'=> $limit, 'min'=>1),
     	);

		$settings_props_styles = array(
			'enable_button_styles' => array('type'=>'checkbox', 'name'=>'enable_button_styles', 'label'=> esc_html__('Enable button styles', 'woocommerce-multipe-address-pro'), 'checked'=>1, 'value'=>'yes'),
			'button_background_color' =>  array('type' => 'colorpicker', 'name' => 'button_background_color', 'label' => esc_html__('Background color', 'woocommerce-multiple-addresses-pro'), 'value' => '#000'),
			'button_text_color' => array('type' => 'colorpicker', 'name' => 'button_text_color', 'label' => esc_html__('Text color', 'woocommerce-multiple-addresses-pro'), 'value' => '#fff'),
			'button_padding' => array('type' => 'text', 'name' => 'button_padding', 'label' => esc_html__('Button padding', 'woocommerce-multiple-addresses-pro' ), 'value' => 'auto'),
		);

      	$settings_props_multiple_shipping = array(
	      	'enable_cart_shipping'=> array('type'=>'checkbox', 'name'=>'enable_cart_shipping', 'label' => esc_html__('Allow products to be shipped to different locations within an order', 'woocommerce-multiple-addresses-pro'), 'checked'=>1, 'value'=>'yes'),
	      	'exclude_products'    => array('type'=>'select_woo', 'name'=>'exclude_products', 'label'=>esc_html__('Exclude certain products from multi-shipping', 'woocommerce-multiple-addresses-pro'), 'placeholder'=>'', 'hint_text'=>'', 'options'=>'', 'value'=>'', 'class' => 'thwma-exclude-product'),
	      	'hidden_ex_pdts_list' => array('type'=>'hidden', 'name'=>'hidden_ex_pdts_list', 'value'=> ''),
	      	'exclude_category'    => array('type'=>'multiselect', 'name'=>'exclude_category', 'label'=>esc_html__('Exclude certain categories from multi-shipping', 'woocommerce-multiple-addresses-pro'), 'placeholder'=>'', 'hint_text'=>'', 'options'=>$exclude_catg, 'value'=>''),
	      	'hidden_ex_catg_list' => array('type'=>'hidden', 'name'=>'hidden_ex_catg_list', 'value'=> ''),
	      	'enable_product_variation'=> array('type'=>'checkbox', 'name'=>'enable_product_variation', 'label' => esc_html__('Multi-shipping for product variations', 'woocommerce-multiple-addresses-pro'), 'checked'=>1, 'value'=>'yes'),
	      	'order_shipping_status'=> array('type'=>'checkbox', 'name'=>'order_shipping_status', 'label' => esc_html__('Manage each shipping status within the order', 'woocommerce-multiple-addresses-pro'), 'checked'=>1, 'value'=>'yes'),
	      	'enable_product_disticty'=> array('type'=>'checkbox', 'name'=>'enable_product_disticty', 'label' => esc_html__('Add the same products distinctly to the cart', 'woocommerce-multiple-addresses-pro'), 'checked'=>1, 'value'=>'yes'),
	      	'handling_fee'=> array('type'=>'checkbox', 'name'=>'handling_fee', 'label' => esc_html__('Handling fee', 'woocommerce-multiple-addresses-pro'), 'checked'=>1, 'value'=>'yes'),
	      	'shop_page_reload'=> array('type'=>'checkbox', 'name'=>'shop_page_reload', 'label' => esc_html__('Disable shop page reloading if a product is added to cart'), 'checked'=>1, 'value'=>'yes'),
	      	'notification_email'=> array('type'=>'checkbox', 'name'=>'notification_email', 'label' => esc_html__('Notification email to customers when a shipping status changes', 'woocommerce-multiple-addresses-pro'), 'checked'=>1, 'value'=>'yes'),
     	);
      	
      	$settings_props_store_pickup = array(
	      	//'hidden_cart_ship_status' => array('type'=>'hidden', 'name'=>'hidden_cart_ship_status', 'value'=> ''),
	      	'enable_multi_store_pickup'=> array('type'=>'checkbox', 'name'=>'enable_multi_store_pickup', 'label' => esc_html__('Multi store pick-up', 'woocommerce-multiple-addresses-pro'), 'checked'=>1, 'value'=>'yes'),
	      	// 'section_store_address' => array('title'=>esc_html__('Store Address', 'woocommerce-multiple-addresses-pro'), 'name' => 'section_store_address', 'type'=>'separator', 'colspan'=>'3'),
	      	'store_addresses1' => array(
	        	'local_pickup_address1_1' => array('type'=>'text', 'name'=>'local_pickup_address1_1', 'label'=>esc_html__('Address line 1', 'woocommerce-multiple-addresses-pro'), 'value'=>'', 'hint_text'=>$pickup_adr1),
	        	'local_pickup_address2_1' => array('type'=>'text', 'name'=>'local_pickup_address2_1', 'label'=>esc_html__('Address line 2', 'woocommerce-multiple-addresses-pro'), 'value'=>'', 'hint_text'=>$pickup_adr2),
	        	'local_pickup_city_1' => array('type'=>'text', 'name'=>'local_pickup_city_1', 'label'=>esc_html__('City', 'woocommerce-multiple-addresses-pro'), 'value'=>'', 'hint_text'=>$pickup_city),
	        	'local_pickup_country_1' => array('type'=>'text', 'name'=>'local_pickup_country_1', 'label'=>esc_html__('Country / State', 'woocommerce-multiple-addresses-pro'), 'value'=>'', 'hint_text'=>$pickup_country),
	        	'local_pickup_postcode_1' => array('type'=>'text', 'name'=>'local_pickup_postcode_1', 'label'=>esc_html__('Postcode / ZIP', 'woocommerce-multiple-addresses-pro'), 'value'=>'', 'hint_text'=>$pickup_postcode),
      		),
	      	'store_addresses2' => array(
	        	'local_pickup_address1_2' => array('type'=>'text', 'name'=>'local_pickup_address1_2', 'label'=>esc_html__('Address line 1', 'woocommerce-multiple-addresses-pro'), 'value'=>'', 'hint_text'=>$pickup_adr1),
	        	'local_pickup_address2_2' => array('type'=>'text', 'name'=>'local_pickup_address2_2', 'label'=>esc_html__('Address line 2', 'woocommerce-multiple-addresses-pro'), 'value'=>'', 'hint_text'=>$pickup_adr2),
	        	'local_pickup_city_2' => array('type'=>'text', 'name'=>'local_pickup_city_2', 'label'=>esc_html__('City', 'woocommerce-multiple-addresses-pro'), 'value'=>'', 'hint_text'=>$pickup_city),
	        	'local_pickup_country_2' => array('type'=>'text', 'name'=>'local_pickup_country_2', 'label'=>esc_html__('Country / State', 'woocommerce-multiple-addresses-pro'), 'value'=>'', 'hint_text'=>$pickup_country),
	        	'local_pickup_postcode_2' => array('type'=>'text', 'name'=>'local_pickup_postcode_2', 'label'=>esc_html__('Postcode / ZIP', 'woocommerce-multiple-addresses-pro'), 'value'=>'', 'hint_text'=>$pickup_postcode),
	      	),
     	);

	    $settings_props_guest_users = array(
	    	//'hidden_cart_ship_status' => array('type'=>'hidden', 'name'=>'hidden_cart_ship_status', 'value'=> ''),
      		'enable_guest_shipping'=> array('type'=>'checkbox', 'name'=>'enable_guest_shipping', 'label' => esc_html__('Enable multi-shipping for guest users', 'woocommerce-multiple-addresses-pro'), 'checked'=>1, 'value'=>'yes'),
	      	'set_time_duration' => array('type'=>'number', 'name'=>'set_time_duration', 'label'=>esc_html__('Set time duration', 'woocommerce-multiple-addresses-pro'), 'value'=>'10', 'hint_text'=>$time_period),
	      	'time_limit' => array('type'=>'select', 'name'=>'time_limit', 'options'=>$time_limit , 'value'=>'minute'),
     	);
     	
      	$settings_props_manage_style = array(
      		//'hidden_cart_ship_status' => array('type'=>'hidden', 'name'=>'hidden_cart_ship_status', 'value'=> ''),
      		'multi_address_url' => array('type'=>'text', 'name'=>'multi_address_url', 'label'=>esc_html__('Label for multiple address picking URL', 'woocommerce-multiple-addresses-pro'), 'value'=>esc_html__('Ship to different address', 'woocommerce-multiple-addresses-pro')),
      		'multi_address_button' => array('type'=>'text', 'name'=>'multi_address_button', 'label'=>esc_html__('Label for add shipping address URL', 'woocommerce-multiple-addresses-pro'), 'value'=>esc_html__('Show saved shipping addresses', 'woocommerce-multiple-addresses-pro')),
      		'multi_shipping_checkbox_label' => array('type'=>'text', 'name'=>'multi_shipping_checkbox_label', 'label'=>esc_html__('Label for multiple shipping Checkbox', 'woocommerce-multiple-addresses-pro'), 'value'=>esc_html__('Do you want to ship to multiple addresses?', 'woocommerce-multiple-addresses-pro')),
     	);

		return array(
			'section_address_fields' => array('title'=>esc_html__('Billing Address Properties', 'woocommerce-multiple-addresses-pro'), 'type'=>'separator', 'colspan'=>'3'),
			'settings_props_billing'  => $settings_props_billing,
			'section_shipping_address_fields' => array('title'=>esc_html__('Shipping Address Properties', 'woocommerce-multiple-addresses-pro'), 'type'=>'separator', 'colspan'=>'3'),
			'settings_props_shipping' => $settings_props_shipping,
			'section_button_styles' => array('title'=>esc_html__('Button Styles Properties', 'woocommerce-multiple-addresses-pro'), 'type'=>'separator', 'colspan'=>'3'),
			'settings_props_styles' => $settings_props_styles,
			//'section_general_fields' => array('title'=>esc_html__('General Properties', 'woocommerce-multiple-addresses-pro'), 'type'=>'separator', 'colspan'=>'3'),
			//'settings_props_general' => $settings_props_general,
			'section_multiple_shipping' => array('title'=>esc_html__('Multiple Shipping', 'woocommerce-multiple-addresses-pro'), 'type'=>'separator', 'colspan'=>'3'),
			'settings_props_multiple_shipping' => $settings_props_multiple_shipping,
			'section_store_pickup' => array('title'=>esc_html__('Store Pick-up Settings', 'woocommerce-multiple-addresses-pro'), 'type'=>'separator', 'colspan'=>'3'),
			'settings_props_store_pickup' => $settings_props_store_pickup,
			'section_gust_users' => array('title'=>esc_html__('Guest Users', 'woocommerce-multiple-addresses-pro'), 'type'=>'separator', 'colspan'=>'3'),
			'section_store_address1' => array('title'=>esc_html__('Store Address1', 'woocommerce-multiple-addresses-pro'), 'type'=>'separator', 'colspan'=>'3'),
			'section_store_address2' => array('title'=>esc_html__('Store Address2', 'woocommerce-multiple-addresses-pro'), 'type'=>'separator', 'colspan'=>'3'),
			'settings_props_guest_users' => $settings_props_guest_users,
			//'section_manage_style' => array('title'=>esc_html__('Style and Label Management', 'woocommerce-multiple-addresses-pro'), 'type'=>'separator', 'colspan'=>'3'),
			'section_manage_style' => array('title'=>esc_html__('Display Text Settings', 'woocommerce-multiple-addresses-pro'), 'type'=>'separator', 'colspan'=>'3'),
			'settings_props_manage_style' => $settings_props_manage_style,

			//'custom_section' => array('title'=>'', 'type'=>'separator', 'colspan'=>'3'),
			//'enable_custom'=> array('type'=>'checkbox', 'name'=>'enable_custom', 'label' => 'Enable  custom Scetions as addresses', 'checked'=>0, 'value'=>'no'),
		);
	}

		/**
     * Function for create render page.
     */
		public function render_page() {
			$this->render_tabs();
			$this->render_sections();
			$this->render_content();
		}

		/**
     * Function for save settings.
     *
     * @param string $section_name The section name
     */
		public function save_settings($section_name) {
			$settings = array();
			$settings_data = get_option(THWMA_Utils::OPTION_KEY_THWMA_SETTINGS);
			if($section_name == 'billing_shipping') {
				if(!empty($settings_data)) {
					if (!in_array('settings_billing', $settings_data)) {
						$settings['settings_billing'] = $this->populate_posted_address_settings('billing');
					}
					if (!in_array('settings_shipping', $settings_data)) {
						$settings['settings_shipping'] = $this->populate_posted_address_settings('shipping');
					}
					if (!in_array('settings_styles', $settings_data)) {
						$settings['settings_styles'] = $this->populate_posted_address_settings('styles');
					}
					if(is_array($settings_data)) {
						foreach($settings_data as $key => $value) {
							if($key == 'settings_billing') {
								$settings['settings_billing'] = $this->populate_posted_address_settings('billing');
							} else if($key == 'settings_shipping') {
								$settings['settings_shipping'] = $this->populate_posted_address_settings('shipping');
							}else if($key == 'settings_styles') {
								$settings['settings_styles'] = $this->populate_posted_address_settings('styles');
							} else {
								$settings[$key] = $value;
							}
						}
					}
				} else {
					$settings['settings_billing'] = $this->populate_posted_address_settings('billing');
					$settings['settings_styles'] = $this->populate_posted_address_settings('styles');
					$settings['settings_shipping'] = $this->populate_posted_address_settings('shipping');
				}
			} elseif($section_name == 'store_pickup') {
				if(!empty($settings_data)) {
					if (!in_array('settings_'.$section_name, $settings_data)) {
						$settings['settings_'.$section_name] = $this->populate_posted_address_settings_store_pickup($section_name);
					}
					if(is_array($settings_data)) {
						foreach($settings_data as $key => $value) {
							if($key == 'settings_billing') {
								$settings[$key] = $value;
							} else if($key == 'settings_shipping') {
								$settings[$key] = $value;
							} else if($key != 'settings_'.$section_name) {
								$settings[$key] = $value;
							} else {
								$settings['settings_'.$section_name] = $this->populate_posted_address_settings_store_pickup($section_name);
							}
						}
					}
				} else {
					$settings['settings_'.$section_name] = $this->populate_posted_address_settings_store_pickup($section_name);
				}
			} else {
				if(!empty($settings_data)) {
					if (!in_array('settings_'.$section_name, $settings_data)) {
						$settings['settings_'.$section_name] = $this->populate_posted_address_settings($section_name);
					}
					if(is_array($settings_data)) {
						foreach($settings_data as $key => $value) {
							if($key == 'settings_billing') {
								$settings[$key] = $value;
							} else if($key == 'settings_shipping') {
								$settings[$key] = $value;
							} else if($key != 'settings_'.$section_name) {
								$settings[$key] = $value;
							} else {
								$settings['settings_'.$section_name] = $this->populate_posted_address_settings($section_name);
							}
						}
					}
				} else {
					$settings['settings_'.$section_name] = $this->populate_posted_address_settings($section_name);
				}
			}
			$settings['enable_custom_section'] = isset($_POST['i_enable_custom']) ? 'yes' :'no';
			$result = update_option(THWMA_Utils::OPTION_KEY_THWMA_SETTINGS, $settings);
			if ($result == true) {
				echo '<div class="updated"><p>'. esc_html__('Your changes were saved.', 'woocommerce-multiple-addresses-pro') .'</p></div>';
			} else {
				echo '<div class="error"><p>'. esc_html__('Your changes were not saved due to an error (or you made none!).', 'woocommerce-multiple-addresses-pro') .'</p></div>';
			}
		}

	/**
     * Function for save settings.
     *
     * @param string $type The field type
     *
     * @return array
     */
		private function populate_posted_address_settings($type) {
			$posted = array();
			$prefix='i_';
			$SETTINGS_PROPS = $this->settings_props['settings_props_'.$type];
			if(!empty($SETTINGS_PROPS) && is_array($SETTINGS_PROPS)) {
				foreach($SETTINGS_PROPS as $props) {
					$name  = $props['name'];
					if($props['type'] == 'checkbox') {
						$value = isset($_POST[$prefix.$name]) ? 'yes' : 'no';
					} elseif($props['type'] == 'text') {
						$value = isset($_POST[$prefix.$name]) ? sanitize_text_field($_POST[$prefix.$name]) : $props['value'];
						$value = stripslashes($value);
					} else if($props['type'] === 'multiselect_grouped' || $props['type'] === 'multiselect' || $props['type'] === 'select_woo') {
						$value = !empty($_POST[$prefix.$name]) ? $_POST[$prefix.$name] : '';
					} else {
						$value = isset($_POST[$prefix.$name]) ? sanitize_text_field($_POST[$prefix.$name]) : $props['value'];
					}
					// if($value != '') {
					// 	$value = stripslashes($value);
					// 	//$value = wc_clean(wp_unslash($value));
					// }
					if($name === 'billing_display_text' || $name === 'shipping_display_text') {
						THWMA_i18n::wpml_register_string('Address Link - '.$name, $value);
					}
					$posted[$name] = $value;
				}
			}
			return $posted;
		}

	/**
     * Function for set posted address settings of store pickup.
     *
     * @param string $type The field type
     *
     * @return array
     */
		private function populate_posted_address_settings_store_pickup($type) {
			$posted = array();
			$prefix='i_';
			$SETTINGS_PROPS = $this->settings_props['settings_props_'.$type];
			$store_addresses =  array();
			$store_address_values1 = array();
			$store_address_values2 = array();
			if(!empty($SETTINGS_PROPS) && is_array($SETTINGS_PROPS)) {
				foreach($SETTINGS_PROPS as $key => $props) {
					if($key == 'store_addresses1') {
						if(!empty($props) && is_array($props)) {
							foreach($props as $props_values) {
								if($props_values['type'] == 'text') {
									$name  = isset($props_values['name']) ? $props_values['name'] : '';
									$value = isset($_POST[$prefix.$name]) ? sanitize_text_field($_POST[$prefix.$name]) : $props_values['value'];
									$value = stripslashes($value);

									if($props_values['type'] == 'text') {
										$value = isset($_POST[$prefix.$name]) ? sanitize_text_field($_POST[$prefix.$name]) : $props_values['value'];
										$value = stripslashes($value);
										$store_address_values1[$name] = $value;
									}
									if(!empty($store_address_values1)) {
										$value = $store_address_values1;
										$posted['store_addresses1'] = $value;
									}
								}
							}
						}
					} elseif($key == 'store_addresses2') {
						if(!empty($props) && is_array($props)) {
							foreach($props as $props_values) {
								if($props_values['type'] == 'text') {
									$name  = isset($props_values['name']) ? $props_values['name'] : '';
									$value = isset($_POST[$prefix.$name]) ? sanitize_text_field($_POST[$prefix.$name]) : $props_values['value'];
									$value = stripslashes($value);
									if($props_values['type'] == 'text') {
										$value = isset($_POST[$prefix.$name]) ? sanitize_text_field($_POST[$prefix.$name]) : $props_values['value'];
										$value = stripslashes($value);
										$store_address_values2[$name] = $value;
									}
									if(!empty($store_address_values2)) {
										$value = $store_address_values2;
										$posted['store_addresses2'] = $value;
									}
								}
							}
						}
					} else {
						if($props['type'] == 'checkbox') {
							$name  = isset($props['name']) ? $props['name'] : '';
							$value = isset($_POST[$prefix.$name]) ? 'yes' : 'no';
							$posted[$name] = $value;
						} elseif($props['type'] == 'text') {
							$value = isset($_POST[$prefix.$name]) ? sanitize_text_field($_POST[$prefix.$name]) : $props['value'];
							$value = stripslashes($value);
							$posted[$name] = $value;
						} else {
							$value = isset($_POST[$prefix.$name]) ? sanitize_text_field($_POST[$prefix.$name]) : $props['value'];
							$posted[$name] = $value;
						}
					}
				}
			}
			return $posted;

			/*if($type == 'store_pickup') {
				$store_addresses =  array();
				$store_address_values = array();
				foreach($SETTINGS_PROPS as $props) {
					$name  = $props['name'];
					if($props['type'] == 'text') {
						$value = isset($_POST[$prefix.$name]) ? $_POST[$prefix.$name] : $props['value'];
						$value = stripslashes($value);

							$store_address_values[$name] = $value;
					}
				}

			}

			foreach($SETTINGS_PROPS as $props) {
				$name  = $props['name'];

				if($props['type'] == 'checkbox') {
					$value = isset($_POST[$prefix.$name]) ? 'yes' : 'no';
					$posted[$name] = $value;
				} elseif($props['type'] == 'text') {
					$value = isset($_POST[$prefix.$name]) ? $_POST[$prefix.$name] : $props['value'];
					$value = stripslashes($value);
					if(!empty($store_address_values)) {
						$value = $store_address_values;
						$posted['store_addresses'] = $value;
					}
				} else {
					$value = isset($_POST[$prefix.$name]) ? $_POST[$prefix.$name] : $props['value'];
					$posted[$name] = $value;
				}

			}
			return $posted;*/
		}

	/**
     * Reset to default function.
     *
     * @param string $section_name The section name
     *
     * @return string
     */
		public function reset_to_default($section_name) {
			THWMA_Utils::reset_to_default_section($section_name);
			return '<div class="updated"><p>'. esc_html__('Settings successfully reset', 'woocommerce-multiple-addresses-pro') .'</p></div>';
		}

	/**
     * Function for create render content.
     */
		private function render_content() {
			$section_name = $this->get_current_section();
			$prefix='i_';
			if(isset($_POST['save_settings'])) {
				if( check_admin_referer( 'settings_fields_forms', 'thwma_settings_fields_forms')) {
					echo $this->save_settings($section_name);
				}
			}
			if(isset($_POST['reset_settings'])) {
	        	if( check_admin_referer( 'settings_fields_forms', 'thwma_settings_fields_forms')) {
					echo $this->reset_to_default($section_name);
				}
			}

			$settings_fields = $this->get_field_form_props();
			$settings_props_billing = $settings_fields['settings_props_billing'];
			$settings_props_shipping = $settings_fields['settings_props_shipping'];
			$settings_props_styles = $settings_fields['settings_props_styles'] ? $settings_fields['settings_props_styles'] : "";
			$settings_props_multiple_shipping = $settings_fields['settings_props_multiple_shipping'];
			$settings_props_store_pickup = $settings_fields['settings_props_store_pickup'];
			$settings_props_guest_users = $settings_fields['settings_props_guest_users'];
			$settings_props_manage_style = $settings_fields['settings_props_manage_style'];

			$settings_props_billing = $this->set_values_props($settings_props_billing, 'billing');
			$settings_props_shipping = $this->set_values_props($settings_props_shipping, 'shipping');
			$settings_props_styles = $this->set_values_props($settings_props_styles, 'styles');
			$settings_props_multiple_shipping = $this->set_values_props($settings_props_multiple_shipping, 'multiple_shipping');
			$settings_props_store_pickup = $this->set_values_props($settings_props_store_pickup, 'store_pickup');
			$settings_props_guest_users = $this->set_values_props($settings_props_guest_users, 'guest_users');
			$settings_props_manage_style = $this->set_values_props($settings_props_manage_style, 'manage_style');

			// $handling_fee = $this->settings_props_general['handling_fee'];
			// $handling_fee_tooltip = (isset($handling_fee['hint_text']) && !empty($handling_fee['hint_text'])) ? $handling_fee['hint_text'] : false;
			?>
			<div style="padding-left: 30px;">
			    <form id="thwma_settings_fields_form" method="post" action="" name="thwma_settings_fields_forms">
			    	<?php if (function_exists('wp_nonce_field')) {
                        wp_nonce_field('settings_fields_forms', 'thwma_settings_fields_forms');
                    }
                    if(!empty($settings_props_multiple_shipping)) {
                    	if(array_key_exists('enable_cart_shipping', $settings_props_multiple_shipping)) {
                    		if(array_key_exists('value', $settings_props_multiple_shipping['enable_cart_shipping'])) {
                    			$multi_shipping_enabled = $settings_props_multiple_shipping['enable_cart_shipping']['value'];
								echo '<input type="hidden" class="check_mult_ship_is_enabled" name="check_mult_ship_is_enabled" value="'.$multi_shipping_enabled.'">';
							}
						}
					}
				    if(($section_name == 'billing_shipping') || ($section_name == '')) { ?>
		                <table class="form-table thpladmin-form-table thwma-genrl-admn-table">
		                	<tbody>
		                		<tr>
					            	<?php $this->render_form_section_separator($settings_fields['section_address_fields']);?>
					            </tr>
					            <tr>
					            	<?php if($settings_props_billing['enable_billing']['value']=='no')
					            	{
					            		$settings_props_billing['enable_billing']['checked']=0;
					            	}
					            	$this->render_form_field_element($settings_props_billing['enable_billing']); ?>
					            </tr>
					            <tr>
					            	<?php
					            	$this->render_form_field_element($settings_props_billing['billing_display'], $this->cell_props_L); ?>
					            </tr>
					            <tr>
					            	<?php $this->render_form_field_element($settings_props_billing['billing_display_position'], $this->cell_props_L); ?>
					            </tr>
					             <tr>
					            	<?php $this->render_form_field_element($settings_props_billing['billing_display_title'], $this->cell_props_L); ?>
					            </tr>
					            <tr>
					            	<?php $this->render_form_field_element($settings_props_billing['billing_display_text'], $this->cell_props_L); ?>
					            </tr>
					            <tr>
					            	<?php $this->render_form_field_element($settings_props_billing['billing_address_limit'], $this->cell_props_L); ?>
					        	</tr>
					       	</tbody>

		                </table>
		                <table class="form-table thpladmin-form-table thwma-genrl-admn-table">
		                	<tbody>
					            <tr>
					            	<?php $this->render_form_section_separator($settings_fields['section_shipping_address_fields']); ?>
					            </tr>
					            <tr>
					            	<?php if($settings_props_shipping['enable_shipping']['value']=='no') {
					            		$settings_props_shipping['enable_shipping']['checked']=0;
					            	}
					            	$this->render_form_field_element($settings_props_shipping['enable_shipping']); ?>
					            </tr>
					            <tr>
					            	<?php $this->render_form_field_element($settings_props_shipping['shipping_display'], $this->cell_props_L); ?>
					            </tr>
					            <tr>
					            	<?php $this->render_form_field_element($settings_props_shipping['shipping_display_position'], $this->cell_props_L); ?>
					            </tr>
					             <tr>
					            	<?php $this->render_form_field_element($settings_props_shipping['shipping_display_title'], $this->cell_props_L); ?>
					            </tr>
					            <tr>
					            	<?php $this->render_form_field_element($settings_props_shipping['shipping_display_text'], $this->cell_props_L); ?>
					            </tr>
					            <tr>
					            	<?php $this->render_form_field_element($settings_props_shipping['shipping_address_limit'], $this->cell_props_L); ?>
					            </tr>
		                    </tbody>
		                </table>
						<table class="form-table thpladmin-form-table thwma-genrl-admn-table">
							<tbody>
								<tr>
									<?php $this->render_form_section_separator($settings_fields['section_button_styles']); ?>
								</tr>

								<tr>
		            	<?php if($settings_props_styles['enable_button_styles']['value']=='no')
		            	{
		            		$settings_props_styles['enable_button_styles']['checked']=0;
		            	}
		            	$this->render_form_field_element($settings_props_styles['enable_button_styles']); ?>
		            </tr>
								<tr>
									<?php $this->render_form_field_element($settings_props_styles['button_background_color'], $this->cell_props_L); ?>
								</tr>
								<tr>
									<?php $this->render_form_field_element($settings_props_styles['button_text_color'], $this->cell_props_L); ?>
								</tr>
								<tr>
									<?php $this->render_form_field_element($settings_props_styles['button_padding'], $this->cell_props_L); ?>
								</tr>
							</tbody>
						</table>

		           	<?php  } else if($section_name == 'multiple_shipping') { ?>
		           		<table class="form-table thpladmin-form-table">
		                	<tbody>
		                		<tr>
					            		<?php $this->render_form_section_separator($settings_fields['section_multiple_shipping']); ?>
					            	</tr>
					            	<tr>
                          <?php if($settings_props_multiple_shipping['enable_cart_shipping']['value']=='no') {
                              $settings_props_multiple_shipping['enable_cart_shipping']['checked']=0;
                          }
                          $this->render_form_field_element($settings_props_multiple_shipping['enable_cart_shipping'], $this->cell_props_CB); ?>
                        </tr>
                        <tr>
                        	<?php $settings_props_multiple_shipping['exclude_products']['value'] = isset($settings_props_multiple_shipping['exclude_products']['value']) ? $settings_props_multiple_shipping['exclude_products']['value'] : '';
													if($settings_props_multiple_shipping['exclude_products']['value'] == '') {
		                    			$settings_props_multiple_shipping['exclude_products']['value'] = isset($settings_props_multiple_shipping['hidden_ex_pdts_list']['value']) ? $settings_props_multiple_shipping['hidden_ex_pdts_list']['value'] : '';
		                    	}
													$this->render_form_field_element($settings_props_multiple_shipping['exclude_products'], $this->cell_props_R);
													$settings_props_multiple_shipping['hidden_ex_pdts_list']['value'] = isset($settings_props_multiple_shipping['hidden_ex_pdts_list']['value']) ? $settings_props_multiple_shipping['hidden_ex_pdts_list']['value'] : '';
					            		$this->render_form_field_element($settings_props_multiple_shipping['hidden_ex_pdts_list'], $this->cell_props_R); ?>
                        </tr>
                        <tr>
                        	<?php $settings_props_multiple_shipping['exclude_category']['value'] = isset($settings_props_multiple_shipping['exclude_category']['value']) ? $settings_props_multiple_shipping['exclude_category']['value'] : '';
													if($settings_props_multiple_shipping['exclude_category']['value'] == '') {
		                    			$settings_props_multiple_shipping['exclude_category']['value'] = isset($settings_props_multiple_shipping['hidden_ex_catg_list']['value']) ? $settings_props_multiple_shipping['hidden_ex_catg_list']['value'] : '';
	                    		}
													$this->render_form_field_element($settings_props_multiple_shipping['exclude_category'], $this->cell_props_R);
													$settings_props_multiple_shipping['hidden_ex_catg_list']['value'] = isset($settings_props_multiple_shipping['hidden_ex_catg_list']['value']) ? $settings_props_multiple_shipping['hidden_ex_catg_list']['value'] : '';
													$this->render_form_field_element($settings_props_multiple_shipping['hidden_ex_catg_list'], $this->cell_props_R); ?>
                        </tr>
                        <tr>
                        	<?php if($settings_props_multiple_shipping['enable_product_variation']['value']=='no') {
                        		$settings_props_multiple_shipping['enable_product_variation']['checked']=0;
                          }
                          $this->render_form_field_element($settings_props_multiple_shipping['enable_product_variation']); ?>
                        </tr>
                        <tr>
                        	<?php

                        		/***
                        		 * == Upgrade Notice: Version 2.1.4 ==
                        		 * We have removed the feature "Manage each shipping status within the order" to streamline and optimize our system.

	                        		if($settings_props_multiple_shipping['order_shipping_status']['value']=='no') {
	                          			$settings_props_multiple_shipping['order_shipping_status']['checked']=0;
	                          		}
	                          		$this->render_form_field_element($settings_props_multiple_shipping['order_shipping_status']);
                          		*/
                          	?>
                        </tr>
                       	<tr>
                        	<?php if($settings_props_multiple_shipping['enable_product_disticty']['value']=='no') {
                          	$settings_props_multiple_shipping['enable_product_disticty']['checked']=0;
                        	}
                          $this->render_form_field_element($settings_props_multiple_shipping['enable_product_disticty']); ?>
                        </tr>

                       <?php /* ?>
										 	<tr>
                      	<?php if($settings_props_multiple_shipping['handling_fee']['value']=='no') {
                      		$settings_props_multiple_shipping['handling_fee']['checked']=0;
                        	}
                        	$this->render_form_field_element($settings_props_multiple_shipping['handling_fee']); ?>
                  			</tr>
	                     <tr>
	                      <?php if($settings_props_multiple_shipping['shop_page_reload']['value']=='no') {
	                      	$settings_props_multiple_shipping['shop_page_reload']['checked']=0;
	                      }
	                      $this->render_form_field_element($settings_props_multiple_shipping['shop_page_reload']);
	                    	?>
	              				</tr>
		                   <tr>
		                    <?php if($settings_props_multiple_shipping['notification_email']['value']=='no') {
		                    	$settings_props_multiple_shipping['notification_email']['checked']=0;
		                    }
		                    $this->render_form_field_element($settings_props_multiple_shipping['notification_email']); ?>
		                	</tr><?php */ ?>

					        </tbody>
					    </table>
					<?php } else if($section_name == 'store_pickup') { ?>
	           		<table class="form-table thpladmin-form-table">
	            		<tbody>
	            			<tr>
			            		<?php $this->render_form_section_separator($settings_fields['section_store_pickup']); ?>
				            </tr>
	                  <tr>
	                  	<?php if($settings_props_store_pickup['enable_multi_store_pickup']['value']=='no') {
	                  					$settings_props_store_pickup['enable_multi_store_pickup']['checked']=0;
	                  				}
	                        	$this->render_form_field_element($settings_props_store_pickup['enable_multi_store_pickup']); ?>
	                  </tr>
	              		<tr>
			            		<?php $this->render_form_section_separator($settings_fields['section_store_address1']);
											?>
			            	</tr>
				            <tr>
		                  <tr class="local_pickup">
		                  	<?php $this->render_form_field_element($settings_props_store_pickup['store_addresses1']['local_pickup_address1_1'], $this->cell_props_L); ?>
					            </tr>
					           	<tr class="local_pickup">
					           		<?php $this->render_form_field_element($settings_props_store_pickup['store_addresses1']['local_pickup_address2_1'], $this->cell_props_L);  ?>
						          </tr>
						          <tr class="local_pickup">
		                    <?php
		                    $this->render_form_field_element($settings_props_store_pickup['store_addresses1']['local_pickup_city_1'], $this->cell_props_L); ?>
		                  </tr>
		                  <tr class="local_pickup">
						          	<?php $this->render_form_field_element($settings_props_store_pickup['store_addresses1']['local_pickup_country_1'], $this->cell_props_L); ?>
						          </tr>
		                  <tr class="local_pickup">
		                  	<?php $this->render_form_field_element($settings_props_store_pickup['store_addresses1']['local_pickup_postcode_1'], $this->cell_props_L); ?>
		                  </tr>
	                 	</tr>
		              	<tr>
			            		<?php $this->render_form_section_separator($settings_fields['section_store_address2']);?>
				            </tr>
										<tr class="local_pickup">
					          	<?php $this->render_form_field_element($settings_props_store_pickup['store_addresses2']['local_pickup_address1_2'], $this->cell_props_L); ?>
					          </tr>
	                  <tr class="local_pickup">
	                      <?php $this->render_form_field_element($settings_props_store_pickup['store_addresses2']['local_pickup_address2_2'], $this->cell_props_L); ?>
	                  </tr>
	                  <tr class="local_pickup">
	                      <?php $this->render_form_field_element($settings_props_store_pickup['store_addresses2']['local_pickup_city_2'], $this->cell_props_L); ?>
	                  </tr>
	                  <tr class="local_pickup">
	                      <?php $this->render_form_field_element($settings_props_store_pickup['store_addresses2']['local_pickup_country_2'], $this->cell_props_L); ?>
	                  </tr>
	                  <tr class="local_pickup">
	                      <?php $this->render_form_field_element($settings_props_store_pickup['store_addresses2']['local_pickup_postcode_2'], $this->cell_props_L); ?>
	                  </tr>
										</tr>
					        </tbody>
						    </table>

		           	<?php } else if($section_name == 'guest_users') { ?>
	           			<table class="form-table thpladmin-form-table">
			            	<tbody>
	              			<tr>
			            			<?php $this->render_form_section_separator($settings_fields['section_gust_users']); ?>
			            		</tr>
	                    <tr>
	                    	<?php if($settings_props_guest_users['enable_guest_shipping']['value']=='no') {
	                            	$settings_props_guest_users['enable_guest_shipping']['checked']=0;
	                  					}
	                        		$this->render_form_field_element($settings_props_guest_users['enable_guest_shipping']);?>
	                    </tr>
	                    <tr class="thwma-set-time">
	                    	<?php $this->render_form_field_element($settings_props_guest_users['set_time_duration']);
	                      $this->render_form_field_element($settings_props_guest_users['time_limit']); ?>
	                    </tr>
			        			</tbody>
			    				</table>

			           	<?php } else if($section_name == 'manage_style') { ?>
			           		<table class="form-table thpladmin-form-table">
			                	<tbody>
			                		<tr>
						            	<?php $this->render_form_section_separator($settings_fields['section_manage_style']);
						            	 //$this->render_form_field_element($settings_props_manage_style['hidden_cart_ship_status'], $this->cell_props_L); ?>
						            	</tr>
	                        <tr class="set_manage_style">
	                            <?php $this->render_form_field_element($settings_props_manage_style['multi_address_url'], $this->cell_props_L); ?>
	                        </tr>
	                        <tr class="set_manage_style">
	                            <?php $this->render_form_field_element($settings_props_manage_style['multi_address_button'], $this->cell_props_L); ?>
	                        </tr>
	                        <tr class="set_manage_style">
	                            <?php $this->render_form_field_element($settings_props_manage_style['multi_shipping_checkbox_label'], $this->cell_props_L); ?>
	                        </tr>
					        			</tbody>
								    </table>

			           	<?php } ?>
               			 <p class="submit">
                			<?php $reset_msg = esc_html__('Are you sure you want to reset to default settings? all your changes will be deleted.', 'woocommerce-multiple-addresses-pro'); ?>
									<input type="submit" name="save_settings" class="button-primary" value=" <?php _e('Save changes', 'woocommerce-multiple-addresses-pro'); ?> " id="thwma_save_settings">
								 	<input type="submit" name="reset_settings" class="button" value="<?php _e('Reset to default', 'woocommerce-multiple-addresses-pro'); ?>"
									onclick="return confirm('<?php echo $reset_msg ?>');">
            			</p>
	        	</form>
    		</div>
	    <?php }

		/**
         * Function for set value props.
         *
         * @param array $settings_props The setting props data
         * @param string $type The type of the field
         *
         * @return array
         */
		private function set_values_props($settings_props, $type) {
			if(!empty($settings_props) && is_array($settings_props)) {
				foreach ($settings_props as $name => $props) {

					// Case of store pickup.
					// if($type == 'store_pickup') {
					// 	if($name == 'store_addresses1') {
					// 		if(!empty($props) && is_array($props)) {
					// 			foreach ($props as $keys => $props_value) {
					// 				$settings_props[$name][$keys]['value'] = THWMA_Utils::get_setting_value_local_pickup('settings_'.$type, $keys, $name);
					// 			}
					// 		}
					// 	} elseif($name == 'store_addresses2') {
					// 		if(!empty($props) && is_array($props)) {
					// 			foreach ($props as $keys => $props_value) {
					// 				$settings_props[$name][$keys]['value'] = THWMA_Utils::get_setting_value_local_pickup('settings_'.$type, $keys, $name);
					// 			}
					// 		}
					// 	} else {
					// 		$name = $name;
					// 	   	$settings_props[$name]['value'] = THWMA_Utils::get_setting_value('settings_'.$type, $name);
					// 	}
					// } else {
						$settings_props[$name]['value'] = THWMA_Utils::get_setting_value('settings_'.$type, $name);
					//}
				}
			}
			return $settings_props;
		}
	}
endif;
