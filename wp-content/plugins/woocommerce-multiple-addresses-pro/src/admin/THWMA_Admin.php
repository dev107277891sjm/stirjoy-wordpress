<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-multiple-addresses-pro
 * @subpackage woocommerce-multiple-addresses-pro/src/admin
 */

namespace Themehigh\WoocommerceMultipleAddressesPro\admin;

use \Themehigh\WoocommerceMultipleAddressesPro\admin\THWMA_Admin_Order_Settings;
use \Themehigh\WoocommerceMultipleAddressesPro\includes\utils\THWMA_Utils;
use \Themehigh\WoocommerceMultipleAddressesPro\admin\THWMA_Admin_Settings_General;
use \Themehigh\WoocommerceMultipleAddressesPro\admin\THWMA_Admin_Settings_Advanced;
use \Themehigh\WoocommerceMultipleAddressesPro\admin\THWMA_Admin_Settings_custom;
use \Themehigh\WoocommerceMultipleAddressesPro\admin\THWMA_Admin_Settings_License;
use \Themehigh\WoocommerceMultipleAddressesPro\admin\THWMA_Admin_Data;

if(!defined('WPINC')) {
	die;
}

if(!class_exists('THWMA_Admin')) :

 	/**
     * Main admin class.
     */
	class THWMA_Admin {
		private $plugin_name;
		private $version;

		public $plugin_pages;
		public $screen_id;

		/**
		 * Initialize the class and set its properties.
		 *
		 * @param      string    $plugin_name       The name of this plugin.
		 * @param      string    $version    The version of this plugin.
		 */
		public function register() {
			// $this->plugin_name = $plugin_name;
			// $this->version = $version;
			$this->plugin_pages = array(
				'woocommerce_page_th_multiple_addresses_pro', 'user-edit.php', 'profile.php', 'post.php', 'woocommerce_page_wc-orders',
			);
			add_action('admin_init', array($this, 'define_admin_hooks'));
			add_action('admin_enqueue_scripts', array($this, 'enqueue_styles_and_scripts'));
			add_action('admin_menu', array($this, 'admin_menu'));
			add_filter('woocommerce_screen_ids', array($this, 'add_screen_id'));
			add_filter('plugin_action_links_'.THWMA_BASE_NAME, array($this, 'plugin_action_links'));
			add_filter('plugin_row_meta', array($this, 'plugin_row_meta'), 10, 2);
			add_action('wp', array($this, 'set_key'));

			$thwma_data = THWMA_Admin_Data::instance();
			add_action('wp_ajax_thwma_load_products', array($thwma_data, 'load_products_ajax'));
			add_action('init', array($this, 'order_page_wmap_settings'));
		}

		public function set_key(){
			$this->define_public_constants();
		}
		
		private function define_public_constants() {
			!defined('THWMA_ASSETS_URL_ADMIN') && define('THWMA_ASSETS_URL_ADMIN', THWMA_URL . 'src/admin/assets/');
			!defined('THWMA_ASSETS_URL_PUBLIC') && define('THWMA_ASSETS_URL_PUBLIC', THWMA_URL . 'src/thpublic/assets/');
			!defined('THWMA_WOO_ASSETS_URL') && define('THWMA_WOO_ASSETS_URL', WC()->plugin_url() . '/assets/');
			!defined('THWMA_TEMPLATE_URL_PUBLIC') && define('THWMA_TEMPLATE_URL_PUBLIC',THWMA_PATH . 'src/thpublic/templates/');
		}

		/**
         * Function for enqueue style and scripts.
         *
         * @param string $hook The screen id.
         */
        public function enqueue_styles_and_scripts($hook) {
			if(!in_array($hook, $this->plugin_pages)) {
				return;
			}
			// if(strpos($hook, 'page_th_multiple_addresses_pro') === false) {
			// 	return;
			// }
			$debug_mode = apply_filters('thwma_debug_mode', false);
			$suffix = $debug_mode ? '' : '.min';
			$this->enqueue_styles($suffix);
			$this->enqueue_scripts($suffix);
		}

		/**
         * Function for enqueue style.
         *
         * @param string $suffix The style sheey suffix.
         */
		private function enqueue_styles($suffix) {
			//wp_enqueue_style('jquery-ui-style', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css?ver=1.11.4');
			// wp_enqueue_style('jquery-ui-style', THWMA_ASSETS_URL_PUBLIC . 'css/jquery-ui.min.css', 'v1.12.1');

			wp_enqueue_style('woocommerce_admin_styles', THWMA_WOO_ASSETS_URL.'css/admin.css');
			wp_enqueue_style('wp-color-picker');
			wp_enqueue_style('thwma-admin-style', THWMA_ASSETS_URL_ADMIN . 'css/thwma-admin'. $suffix .'.css', $this->version);
			wp_enqueue_style('select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css');
			//wp_enqueue_style('select2', THWMA_ASSETS_URL_ADMIN . 'css/select2.min.css', '4.1.0-beta.1');
			
		}

		/**
         * Function for enqueue script.
         *
         * @param string $days The js file suffix.
         */
		private function enqueue_scripts($suffix) {
			$address_fields_billing = THWMA_Utils::get_address_fields_by_address_key('billing_');
			$address_fields_shipping = THWMA_Utils::get_address_fields_by_address_key('shipping_');
			$deps = array('jquery', 'jquery-ui-dialog', 'jquery-ui-sortable', 'jquery-tiptip', 'wc-enhanced-select', 'select2', 'wp-color-picker');
			wp_enqueue_script('thwma-admin-script', THWMA_ASSETS_URL_ADMIN . 'js/thwma-admin'. $suffix .'.js', $deps, $this->version, false);

			$script_var = array(
				'err_msg_cus_section' => esc_html__('Please select a Custom Section', 'woocommerce-multiple-addresses-pro'),
				'err_msg_def_section' => esc_html__('Please select a Default Section', 'woocommerce-multiple-addresses-pro'),
				'slt_def_sec' => esc_html__('Select default section', 'woocommerce-multiple-addresses-pro'),
				'slt_cus_sec' => esc_html__('Select custom section', 'woocommerce-multiple-addresses-pro'),
				'slt_def_fld' => esc_html__('Select default field', 'woocommerce-multiple-addresses-pro'),
				'slt_cus_fld' => esc_html__('Select custom field', 'woocommerce-multiple-addresses-pro'),
				'slt_user_role' => esc_html__('Choose the user types', 'woocommerce-multiple-addresses-pro'),
				'cancel' => esc_html__('Cancel', 'woocommerce-multiple-addresses-pro'),
				'update_address' => esc_html__('Update Address', 'woocommerce-multiple-addresses-pro'),
				'add_address' => esc_html__('Add address', 'woocommerce-multiple-addresses-pro'),
				'additional_billing_adr' => esc_html__('Additional Billing Address', 'woocommerce-multiple-addresses-pro'),
				'additional_shipping_adr' => esc_html__('Additional Shipping Address', 'woocommerce-multiple-addresses-pro'),
				'address_fields_billing'=>$address_fields_billing,
				'address_fields_shipping'=>$address_fields_shipping,
	            'admin_url' => admin_url(),
	            'ajax_url'    => admin_url('admin-ajax.php'),
	      	);
			wp_localize_script('thwma-admin-script', 'thwma_var', $script_var);
			wp_enqueue_script('select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', array('jquery'));
			//wp_enqueue_script('select2', THWMA_ASSETS_URL_ADMIN . 'js/select2.min.js', array('jquery', 'select2'));

			//wp_enqueue_script('thwma_select2', THWMA_ASSETS_URL_ADMIN . 'js/inc/thwma_select2.js', array('jquery', 'select2'));
		}

		/**
         * Function for set admin menu.
         */
        public function admin_menu() {
			$this->screen_id = add_submenu_page('woocommerce', esc_html__('WooCommerce Multiple Addresses', 'woocommerce-multiple-addresses-pro'), esc_html__('Manage Address', 'woocommerce-multiple-addresses-pro'), 'manage_woocommerce', 'th_multiple_addresses_pro', array($this, 'output_settings'));
		}

		/**
         * Function for add screen id.
         *
         * @param string $ids The given id.
         *
         * @return string.
         */
        public function add_screen_id($ids) {
			$ids[] = 'woocommerce_page_th_multiple_addresses_pro';
			$ids[] = strtolower(esc_html__('WooCommerce', 'woocommerce-multiple-addresses-pro')) .'_page_th_multiple_addresses_pro';
			return $ids;
		}

		/**
         * Function for set plugin action links.
         *
         * @param string $links The action links.
         *
         * @return string.
         */
        public function plugin_action_links($links) {
			$settings_link = '<a href="'.admin_url('admin.php?&page=th_multiple_addresses_pro').'">'. esc_html__('Settings', 'woocommerce-multiple-addresses-pro') .'</a>';
			array_unshift($links, $settings_link);
			return $links;
		}

		/**
         * Function for set row meta.
         *
         * @param string $links The gien link.
         * @param string $file The sfile name.
         *
         * @return string.
         */
        public function plugin_row_meta($links, $file) {
			if(THWMA_BASE_NAME == $file) {
				$doc_link = esc_url('https://www.themehigh.com/docs/category/multiple-shipping-addresses-for-woocommerce/');
				$support_link = esc_url('https://help.themehigh.com/hc/en-us/requests/new?utm_source=thwma_pro&utm_medium=wp_list_link&utm_campaign=get_support');
				$row_meta = array(
					'docs' => '<a href="'.esc_url_raw($doc_link).'" target="_blank" aria-label="'.esc_html__('View plugin documentation', 'woocommerce-multiple-addresses-pro').'">'.esc_html__('Docs', 'woocommerce-multiple-addresses-pro').'</a>',
					'support' => '<a href="'.esc_url_raw($support_link).'" target="_blank" aria-label="'. esc_html__('Visit premium customer support', 'woocommerce-multiple-addresses-pro') .'">'. esc_html__('Premium support', 'woocommerce-multiple-addresses-pro') .'</a>',
				);
				return array_merge($links, $row_meta);
			}
			return (array) $links;
		}

		/**
         * output setting function.
         */
        public function output_settings() {
			$tab  = isset($_GET['tab']) ? esc_attr($_GET['tab']) : 'general_settings';
			if($tab === 'advanced_settings') {
				$advanced_settings = THWMA_Admin_Settings_Advanced::instance();
				$advanced_settings->render_page();
			} else if($tab === 'license_settings') {
				$license_settings = THWMA_Admin_Settings_License::instance();
				$license_settings->render_page();
			} else if($tab === 'general_settings') {
				$general_settings = THWMA_Admin_Settings_General::instance();
				$general_settings->render_page();
			} else if($tab === 'custom_section_settings') {
				$custom_settings = THWMA_Admin_Settings_custom::instance();
				$custom_settings->render_page();
			}
		}

		/**
         * Function for define admin hooks.
         */
        	public function define_admin_hooks() {
			add_action('show_user_profile', array($this, 'add_customer_custom_addresses'), 20);
			add_action('edit_user_profile', array($this, 'add_customer_custom_addresses'), 20);
			add_action('current_screen', array($this, 'current_screen'), 50);
			add_action('personal_options_update', array($this, 'delete_address'));
			add_action('edit_user_profile_update', array($this, 'delete_address'));
			add_action('personal_options_update', array($this, 'save_customer_data_to_custom'), 2);
			add_action('admin_enqueue_scripts', array($this, 'codemirror_enqueue_scripts'));

			$this->thwma_remove_parent_orders();
			$this->define_admin_constants();
		}

		private function define_admin_constants() {
			!defined('THWMA_ASSETS_URL_ADMIN') && define('THWMA_ASSETS_URL_ADMIN', THWMA_URL . 'src/admin/assets/');
			!defined('THWMA_WOO_ASSETS_URL') && define('THWMA_WOO_ASSETS_URL', WC()->plugin_url() . '/assets/');
		}
		

		/*public function add_heading_fields($fields) {
			$fields['billing']['fields']['billing_heading']= array(
								'label'       => esc_html__('Address Type', 'woocommerce'),
								'description' => '',
							);
			return $fields;
		}

		public function add_custom_addresses_meta_fields($fields) {

		}*/

		/**
         * Function for getcustom meta fields.
         *
         * @return array.
         */
        	public function get_customer_meta_fields() {
			$show_fields = apply_filters(
				'woocommerce_customer_meta_fields', array(
					'billing'  => array(
						'fields' => array(
							'billing_heading' => array(
								'label'       => esc_html__('Address Type', 'woocommerce'),
								'description' => '',
							),

							'billing_first_name' => array(
								'label'       => esc_html__('First name', 'woocommerce'),
								'description' => '',
							),
							'billing_last_name'  => array(
								'label'       => esc_html__('Last name', 'woocommerce'),
								'description' => '',
							),
							'billing_company'    => array(
								'label'       => esc_html__('Company', 'woocommerce'),
								'description' => '',
							),
							'billing_address_1'  => array(
								'label'       => esc_html__('Address line 1', 'woocommerce'),
								'description' => '',
							),
							'billing_address_2'  => array(
								'label'       => esc_html__('Address line 2', 'woocommerce'),
								'description' => '',
							),
							'billing_city'       => array(
								'label'       => esc_html__('City', 'woocommerce'),
								'description' => '',
							),
							'billing_postcode'   => array(
								'label'       => esc_html__('Postcode / ZIP', 'woocommerce'),
								'description' => '',
							),
							'billing_country'    => array(
								'label'       => esc_html__('Country', 'woocommerce'),
								'description' => '',
								'class'       => 'js_field-country',
								'type'        => 'select',
								'options'     => array('' => esc_html__('Select a country&hellip;', 'woocommerce')) + WC()->countries->get_allowed_countries(),
							),
							'billing_state'      => array(
								'label'       => esc_html__('State / County', 'woocommerce'),
								'description' => esc_html__('State / County or state code', 'woocommerce'),
								'class'       => 'js_field-state',
							),
							'billing_phone'      => array(
								'label'       => esc_html__('Phone', 'woocommerce'),
								'description' => '',
							),
							'billing_email'      => array(
								'label'       => esc_html__('Email address', 'woocommerce'),
								'description' => '',
							),
						),
					),
					'shipping' => array(
						'fields' => array(
							'shipping_heading' => array(
								'label'       => esc_html__('Address Type', 'woocommerce'),
								'description' => '',
							),
							'shipping_first_name' => array(
								'label'       => esc_html__('First name', 'woocommerce'),
								'description' => '',
							),
							'shipping_last_name'  => array(
								'label'       => esc_html__('Last name', 'woocommerce'),
								'description' => '',
							),
							'shipping_company'    => array(
								'label'       => esc_html__('Company', 'woocommerce'),
								'description' => '',
							),
							'shipping_address_1'  => array(
								'label'       => esc_html__('Address line 1', 'woocommerce'),
								'description' => '',
							),
							'shipping_address_2'  => array(
								'label'       => esc_html__('Address line 2', 'woocommerce'),
								'description' => '',
							),
							'shipping_city'       => array(
								'label'       => esc_html__('City', 'woocommerce'),
								'description' => '',
							),
							'shipping_postcode'   => array(
								'label'       => esc_html__('Postcode / ZIP', 'woocommerce'),
								'description' => '',
							),
							'shipping_country'    => array(
								'label'       => esc_html__('Country', 'woocommerce'),
								'description' => '',
								'class'       => 'js_field-country',
								'type'        => 'select',
								'options'     => array('' => esc_html__('Select a country&hellip;', 'woocommerce')) + WC()->countries->get_allowed_countries(),
							),
							'shipping_state'      => array(
								'label'       => esc_html__('State / County', 'woocommerce'),
								'description' => esc_html__('State / County or state code', 'woocommerce'),
								'class'       => 'js_field-state',
							),
						),
					),
				)
			);
			return $show_fields;
		}

		// public function add_customer_custom_addresses($user) {
		// 	$settings = THWMA_Utils::get_advanced_settings();
		// 	if(!empty($settings)) {
		// 		$user_roles = array();
		// 		$current_user = array();
		// 		$user_roles = (isset($settings['select_user_role'])) ? $settings['select_user_role'] : '';
		// 		$userroles = explode(', ', $user_roles);
		// 		$current_user = $user->roles;
		// 		if(!empty($user_roles)) {
		// 			foreach($current_user as $cur_user) {
		// 				if (in_array($cur_user, $userroles, TRUE)) {
		// 					$this->add_customer_custom_addresses_show($user);
		// 				}
		// 			}
		// 		} else {
		// 			$this->add_customer_custom_addresses_show($user);
		// 		}
		// 	}
		// }

		/**
         * Function for add users custom addresses.
         *
         * @param arrat $user The user details.
         */
        public function add_customer_custom_addresses($user) {
			$customer_id = $user->ID; ?>
			<h2><?php echo esc_html__('Additional Billing Addresses', 'woocommerce-multiple-addresses-pro'); ?></h2>
			<a href="javascript:void(0)" id="th-bill_btn" class='th-popup-billing th-pop-link' onclick="thwma_admin_custom_address_popup(event, this, 'billing', 'add', '')" ><?php echo esc_html__('ADD NEW BILLING ADDRESS', 'woocommerce-multiple-addresses-pro'); ?>
			</a>
			<?php $custom_addresses_billing = THWMA_Utils::get_addresses($customer_id, 'billing');
			$billing_addresses = $this->get_account_addresses($customer_id, 'billing', $custom_addresses_billing); ?>
			<h2><?php echo esc_html__('Additional Shipping Addresses', 'woocommerce-multiple-addresses-pro'); ?></h2>
			<a href="javascript:void(0)" id="th-ship_btn" class='th-popup-shippinging th-pop-link' onclick="thwma_admin_custom_address_popup(event, this, 'shipping', 'add', '')" ><?php echo esc_html__('ADD NEW SHIPPING ADDRESS', 'woocommerce-multiple-addresses-pro'); ?>
			</a>
			<?php $custom_addresses_shipping = THWMA_Utils::get_addresses($customer_id, 'shipping');
			$shipping_addresses = $this->get_account_addresses($customer_id, 'shipping', $custom_addresses_shipping);

		}

		/**
         * Function for get current screen.
         *
         * @param string $screen_id The screen id data.
         *
         * @return array.
         */
        public function current_screen($screen_id) {
			if($screen_id->id == 'profile' || $screen_id->id == 'user-edit') {
				if($screen_id->id == 'profile') {
						$user = wp_get_current_user();
						$user_id = $user->ID;
				} else {
					if (current_user_can('edit_users')) {
						$user_id = (isset($_REQUEST['user_id'])) ? $_REQUEST['user_id'] : '';
					}
				}
				if(isset($user_id)) {
					//$show_fields = $this->get_customer_meta_fields();
					$show_fields = THWMA_Utils::thwma_get_customer_meta_fields();
					if(!empty($show_fields) && is_array($show_fields)) {
						foreach ($show_fields as $fieldset_key => $fieldset) :
							$type = $fieldset_key;
							if(isset($_POST[$type.'_custom_user_id'])) {
								$address_key = isset($_POST[$type.'_custom_address_key']) ? sanitize_text_field($_POST[$type.'_custom_address_key']) : '';
								if(empty($address_key)) {
									$this->save_new_address($user_id, $type);
								} else {
									$this->update_address($user_id, $type, $address_key);
								}
							} ?>
							<div id='custom-<?php echo $fieldset_key ?>-address' style="display:none">
								<form id='<?php echo $fieldset_key ?>' action="" method="post" name="custom_form">
									<table class="form-table">
										<?php if(!empty($fieldset['fields']) && is_array($fieldset['fields'])) {
											foreach ($fieldset['fields'] as $key => $field) : ?>
												<tr>
													<th>
														<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?></label>
													</th>
													<td>
														<?php if (! empty($field['type']) && 'select' === $field['type']) { ?>
															<select id="custom_<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>"  class="<?php echo esc_attr($field['class']); ?>" style="width: 25em;">
																<?php
																if(!empty($field['options']) && is_array($field['options'])) {
																	foreach ($field['options'] as $option_key => $option_value) : ?>
																		<option value="<?php echo esc_attr($option_key); ?>"><?php echo esc_attr($option_value); ?></option>
																	<?php endforeach;
																} ?>
															</select>
														<?php } else if (! empty($field['type']) && 'radio' === $field['type']){
															if(!empty($field['options']) && is_array($field['options'])) {
													            foreach ($field['options'] as $option_key => $option_value) : ?>
													                <label for="<?php echo esc_attr($option_value); ?>" >
													                <input id="custom_<?php echo esc_attr($key); ?>_<?php echo esc_attr($option_key); ?>" class=" <?php echo esc_attr($field['class']); ?>" type="radio" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($option_key); ?>" >
													                <span><?php echo esc_attr($option_value); ?></span>
													            <?php endforeach;
													        }
													    } else if (! empty($field['type']) && 'checkbox' === $field['type']) { ?>
													                <label for="<?php echo esc_attr($key); ?>" >
													                <span><?php echo esc_attr($option_value); ?></span>
													                <input type="checkbox" id="custom_<?php echo esc_attr($key); ?>" class=" <?php echo esc_attr($field['class']); ?>" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($key); ?>"/>
													            <?php
													    } else if (! empty($field['type']) && 'textarea' === $field['type']) { ?>
													         <textarea  name="<?php echo esc_attr($key); ?>" rows="3" cols="46" id="custom_<?php echo esc_attr($key); ?>" ></textarea>
													    <?php } else {
															if(apply_filters('emnable_thwma_modify_profile_address_fields', false)) {
																do_action('thwma_modify_profile_address_fields',$field, $key);
															} else { ?>
																<input id="custom_<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" type="text"  value="" class="<?php echo (! empty($field['class']) ? esc_attr($field['class']) : 'regular-text'); ?>" />
															<?php }
														} ?>
														<br/>

													</td>
												</tr>
											<?php endforeach;
										} ?>
										<tr>
											<td>
											<input type="hidden" name="<?php echo $fieldset_key?>_custom_user_id"  value="<?php echo esc_attr($user_id); ?>" />
											<input type="hidden" name="<?php echo $fieldset_key?>_custom_address_key"  value="" />

											</td>
										</tr>
									</table>
								</form>
							</div>
						<?php endforeach;
					}
				}
			}
		}

		/**
         * Function for save new address.
         *
         * @param int $user_id The user id.
         * @param string $user_id The address type.
         *
         * @return array.
         */
        public function save_new_address($user_id, $type) {
			//$save_fields = $this->get_customer_meta_fields();
			$save_fields = THWMA_Utils::thwma_get_customer_meta_fields();
			$address_new = array();
			$fieldset = $save_fields[$type];
			if(!empty($fieldset['fields']) && is_array($fieldset['fields'])) {
				foreach ($fieldset['fields'] as $key => $field) {
					if(isset($_POST[ $key ])) {
						$address_value = is_array($_POST[ $key ]) ? implode(', ', wc_clean($_POST[ $key ])) : wc_clean($_POST[ $key ]);
						$address_new[$key] = $address_value;
					}
				}
			}
			THWMA_Utils::save_address_to_user($user_id, $address_new, $type);
		}

		/**
         * Function for update addres.
         *
         * @param int $user_id The user id
         * @param string $type The address type
         * @param string $address_key The unique key of each address
         */
        public function update_address($user_id, $type, $address_key) {
			//$save_fields = $this->get_customer_meta_fields();
			$save_fields = THWMA_Utils::thwma_get_customer_meta_fields();
			$fieldset = $save_fields[$type];
			$address_new = array();
			if(!empty($fieldset['fields']) && is_array($fieldset['fields'])) {
				foreach ($fieldset['fields'] as $key => $field) {
					if(isset($_POST[ $key ])) {
						$address_value = is_array($_POST[ $key ]) ? implode(', ', wc_clean($_POST[ $key ])) : wc_clean($_POST[ $key ]);
						$address_new[$key] = $address_value;
					}
				}
			}
			THWMA_Utils::update_address_to_user($user_id, $address_new, $type, $address_key);
		}

		/**
         * Function for delete address.
         *
         * @param int $user_id The user id.
         */
        public function delete_address($user_id) {
			if(isset($_POST['delete_address'])) {
				$name = isset($_POST['delete_address']) ? sanitize_text_field($_POST['delete_address']) : '';
				$address_key = substr($name, strpos($name, "=") + 1);
				$type =substr($name.'?', 0, strpos($name, '?'));
				THWMA_Utils::delete($user_id, $type, $address_key);
			}
		}

		/**
		 *Function for remove the parent order id.
		 *
		 */
		public function thwma_remove_parent_orders(){
			$main_order = get_option('thwmap_remove_main_order');
			if($main_order && is_array($main_order)){
				foreach ($main_order as $value ) {
					THWMA_Utils::thwma_remove_main_order($value);
				}
				update_option('thwmap_remove_main_order', array());
			}
		}

		/**
         * Function for get account addresses.
         *
         * @param int $customer_id The user id.
         * @param string $type The address type(billing/shipping).
         * @param array $custom_addresses The customer addressses.
         */
        public function get_account_addresses($customer_id, $type, $custom_addresses) {
			$return_html = '';
			$add_class='';
			$saved_addresses = THWMA_Utils::get_custom_addresses($customer_id, $type) ? THWMA_Utils::get_custom_addresses($customer_id, $type) : array();
			$address_count = count(array_filter($saved_addresses));
			$address_limit = THWMA_Utils::get_setting_value('settings_'.$type , $type.'_address_limit');
				if(is_array($custom_addresses)) {
				$add_list_class  = ($type == 'billing') ? " thwma-thslider-list bill " : " thwma-thslider-list ship"; ?>
				<div class="th-prof-address">
	           		<ul id="th-prof-list" class="'.$add_list_class.'">
						<?php if(!empty($custom_addresses)) {
							foreach ($custom_addresses as $name => $title) {
								$default_heading = apply_filters('thwma_default_heading', false);
								if($default_heading) {
									$heading = !empty($title) ? $title : esc_html__('Home', 'woocommerce-multiple-addresses-pro') ;
								} else {
									$heading = !empty($title) ? $title : esc_html__('', 'woocommerce-multiple-addresses-pro') ;
								}
								$address = THWMA_Utils::get_all_addresses($customer_id, $name);
								$address_key = substr($name, strpos($name, "=") + 1); ?>
								<li class="th-address-list" value="<?php echo $address_key; ?>"" >
									<div class="thwma-adr-box address-box"  data-address-id="">
										<div class="thwma-main-content">
											<div class="address-type-wrapper"> <?php echo $heading  ?></div>
											<div class="complete-aaddress">

												<div class="thwma-adr-text address-text address-wrapper">
													<?php echo $address; ?>
												</div>
											</div>
										</div>
										<div class="btn-continue address-wrapper">
											<?php $custom_address = THWMA_Utils::get_custom_addresses($customer_id, $type, $address_key);
											$json_address = json_encode($custom_address); ?>
											<input type="hidden" name="f_adrs[<?php echo $address_key; ?>]" class="e_adrs_<?php echo $type.'_'.$address_key; ?>" value='<?php echo  $json_address; ?>' />
											<a href="javascript:void(0)" id="" class="th-admin-edit" onclick="thwma_admin_custom_address_popup(event, this, '<?php echo $type; ?>', 'edit', '<?php echo $address_key; ?>')" ><?php echo esc_html__('EDIT', 'woocommerce-multiple-addresses-pro'); ?>
											</a>
											<?php $delete_mssg = esc_html__('Are you sure you want to delete this address?', 'woocommerce-multiple-addresses-pro'); ?>
											<input type="hidden" name="delete_id_<?php echo $address_key; ?>" value="<?php echo $address_key; ?>"/>
											<button type="submit" class="th-admin-del" name="delete_address" value="<?php echo $name; ?>" onclick="return confirm('<?php echo $delete_mssg; ?> ');"><?php echo esc_html__('Delete', 'woocommerce-multiple-addresses-pro'); ?></button>
										</div>
									</div>
								</li>
							<?php }
						} ?>
					</ul>
				</div>
			<?php }
		}

		/**
         * Function for get address by using json.
         *
         * @param string $address_key The uniqueue identity of address.
         *
         * @return void.
         */
        public function get_address_set_json($address_key) {
			$props_set = array();
			$attr_settings = THWVS_Utils::get_swatches_settings_value($attr_key);
			if(is_array($attr_settings) && !empty($this->field_props)) {
				foreach($this->field_props as $pname => $property) {
					if(isset($attr_settings[$pname])) {
						$pvalue =$attr_settings[$pname];
						$pvalue = is_array($pvalue) ? implode(',', $pvalue) : $pvalue;
						$pvalue = esc_attr($pvalue);
						if($property['type'] == 'checkbox') {
							$pvalue = $pvalue ? 1 : 0;
						}
						$props_set[$pname] = $pvalue;
					}
				}
			}
		}

		/**
	     * Function for save customer data to custom.
	     *
	     * @param int $user_id The user id.
	     */
	    public function save_customer_data_to_custom($user_id) {
				$this->update_custom_address_from_profile($user_id, 'shipping');
				$this->update_custom_address_from_profile($user_id, 'billing');
			}

		/**
	   * Function for adding codemirror supports.
	   *
	   */
		function codemirror_enqueue_scripts($hook) {
			if ( 'woocommerce_page_th_multiple_addresses_pro' !== get_current_screen()->id ) {
		        return;
		    }
	  		$thwma_settings['codeEditor'] = wp_enqueue_code_editor(array('type' => 'text/css'));
	  		wp_localize_script('jquery', 'thwma_settings', $thwma_settings);
	  		wp_enqueue_style('wp-codemirror');
		}


		/**
         * Function for update custom address from profile.
         *
         * @param int $user_id The user id.
         * @param string $type The address type.
         */
		private function update_custom_address_from_profile($user_id, $type) {
			$default_address = THWMA_Utils::get_custom_addresses($user_id, 'default_'.$type);
			$def_addr = THWMA_Utils::is_same_address_exists($user_id, $type);
			$address_key = $default_address ? $default_address : $def_addr ;
			if($address_key) {
				$address = THWMA_Utils::get_custom_addresses($user_id, $type, $address_key);
				//$save_fields =  $this-> get_customer_meta_fields();
				$save_fields = THWMA_Utils::thwma_get_customer_meta_fields();
				$fieldset = $save_fields [$type];
				$profile_address = array();
				if(!empty($fieldset['fields']) && is_array($fieldset['fields'])) {
					foreach ($fieldset['fields'] as $key => $field) {
						if($key != 'billing_heading' && $key != 'shipping_heading') {
							if(isset($_POST[ $key ])) {
								$address_value = is_array($_POST[ $key ]) ? implode(',', wc_clean($_POST[ $key ])) : wc_clean($_POST[ $key ]);
								$profile_address[$key] = $address_value;
							}
						}
					}
				}
				$default_heading = apply_filters('thwma_default_heading', false);
				if($default_heading) {
					if(!isset($address[$type.'_heading'])) {
						$profile_address[$type.'_heading'] = esc_html__('Home', 'woocommerce-multiple-addresses-pro');
					} else {
						$profile_address[$type.'_heading'] = $address[$type.'_heading'];
					}
				}
				THWMA_Utils::update_address_to_user($user_id, $profile_address, $type, $address_key);
			}
		}

		/**
         * The order page settings adding funtion.
         */
        public function order_page_wmap_settings() {
        		$order_page = new THWMA_Admin_Order_Settings();
		}
	}
endif;
