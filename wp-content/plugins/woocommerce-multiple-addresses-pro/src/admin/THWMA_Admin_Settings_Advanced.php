<?php
/**
 * The admin advanced settings page functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-multiple-addresses-pro
 * @subpackage woocommerce-multiple-addresses-pro/src/admin
 */

namespace Themehigh\WoocommerceMultipleAddressesPro\admin;

use Themehigh\WoocommerceMultipleAddressesPro\admin\THWMA_Admin_Settings;
use \Themehigh\WoocommerceMultipleAddressesPro\includes\utils\THWMA_Utils;

if(!defined('WPINC')) {
	die;
}

if(!class_exists('THWMA_Admin_Settings_Advanced')) :

	/**
     * The advanced admin settings class.
     */
	class THWMA_Admin_Settings_Advanced extends THWMA_Admin_Settings{
		//const THWMA_TEXT_DOMAIN = 'thwma';
		protected static $_instance = null;
		private $settings_fields = NULL;
		private $cell_props_L = array();
		private $cell_props_R = array();
		private $cell_props_CB = array();
		private $cell_props_TA = array();
		private $left_cell_props = array();

		public function __construct() {
			parent::__construct('advanced_settings');
			$this->init_constants();
			$this->page_id = 'advanced_settings';
		}

		public static function instance() {
			if(is_null(self::$_instance)) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public function init_constants() {
			$this->cell_props_L = array(
				'label_cell_props' => 'class="titledesc" scope="row" style="width: 20%;"',
				'input_cell_props' => 'class="forminp"',
				'input_width' => '250px',
				'label_cell_th' => true
			);
			$this->cell_props_R = array('label_cell_width' => '13%', 'input_cell_width' => '34%', 'input_width' => '250px');
			$this->cell_props_CB = array('cell_props' => 'colspan="3"', 'render_input_cell' => true);
			$this->cell_props_TA = array(
				'label_cell_props' => 'class="titledesc" scope="row" style="width: 20%; vertical-align:top"',
				'rows' => 10,
			);
			$this->left_cell_props = array(
				'label_cell_props' => 'class="titledesc" scope="row" style="width: 20%;"',
				'input_cell_props' => 'class="forminp"',
				'input_width' => '700px',
				'label_cell_th' => true
			);

			$this->settings_fields = $this->get_advanced_settings_fields();
		}

		public function get_advanced_settings_fields() {
			$user_types_exist = array(
				'administrator' => __('Administrator', 'woocommerce-multiple-addresses-pro'),
				'author' => __('Author', 'woocommerce-multiple-addresses-pro'),
				'contributor'=> __('Contributor', 'woocommerce-multiple-addresses-pro'),
				'customer' => __('Customer', 'woocommerce-multiple-addresses-pro'),
				'editor' => __('Editor', 'woocommerce-multiple-addresses-pro'),
				'shop_manager' => __('Shop manager', 'woocommerce-multiple-addresses-pro'),
				'subscriber'=> __('Subscriber', 'woocommerce-multiple-addresses-pro'),
			);
			$new_user_roles = '';
			$custom_user_roles = array();
			$new_user_roles = apply_filters( 'thwma_custom_created_user_roles', $custom_user_roles );
			$user_types_exist = array_merge($user_types_exist,$new_user_roles);

			$addrft_hint_text = '';
			if(apply_filters('thwma_remove_dropdown_address_format', true)) {
				$addrft_hint_text = 'The format is applicable in the address display format of saved addresses pop-up. This format will not be applied for the saved addresses displayed in the drop-down.';
			} else {
				$addrft_hint_text = '';
			}
			return array(
				'section_address_autofill' => array('title'=>__('Address Autofill', 'woocommerce-multiple-addresses-pro'), 'type'=>'separator', 'colspan'=>'3'),
				'enable_autofill' => array('name'=>'enable_autofill', 'label' => __('Enable Address Autofill', 'woocommerce-multiple-addresses-pro'), 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0),
				'autofill_apikey' => array('type'=>'text', 'name'=>'autofill_apikey', 'label'=>__('Google Maps API Key', 'woocommerce-multiple-addresses-pro'), 'value'=> ''),

				'section_address_fields' => array('title'=>__('Address Format', 'woocommerce-multiple-addresses-pro'), 'type'=>'separator', 'colspan'=>'3'),
				// 'address_formats' => array('name'=>'address_formats', 'label'=>__('Address format overrides', 'woocommerce-multiple-addresses-pro'), 'type'=>'textarea'),
				'address_formats' => array('name'=>'address_formats', 'label'=>__('Address Format Overrides', 'woocommerce-multiple-addresses-pro'), 'type'=>'textarea', 'hint_text'=>$addrft_hint_text),
				'other_settings' => array('title'=>__('Other Settings', 'woocommerce-multiple-addresses-pro'), 'type'=>'separator', 'colspan'=>'3'),
				//'select_user_role' => array('name'=>'select_user_role', 'label' => __('Select Specific User', 'woocommerce-multiple-addresses-pro'), 'type'=>'multiselect', 'hint_text'=>'Select role to enable multiple addresses', 'options'=>$fields_position_email),
				// 'enable_user_account' => array('name'=>'enable_user_account', 'label' => __('Enable multiple addresses at my account page', 'woocommerce-multiple-addresses-pro'), 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0),
				// 'select_user_role'    => array('type'=>'multiselect', 'name'=>'select_user_role', 'label'=>__('Select user types', 'woocommerce-multiple-addresses-pro'), 'placeholder'=>'Select user roles', 'hint_text'=>'Choose user types for displaying saved address at edit user page.', 'options'=>$fields_position_email),
				'disable_address_management' => array('name'=>'disable_address_management', 'label' => __('Disable Address Management(Add/ Edit/ Delete) for Selected User Types', 'woocommerce-multiple-addresses-pro'), 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0),
				'hidden_user_role' => array('type'=>'hidden', 'name'=>'hidden_user_role', 'value'=> ''),
				'select_user_role'    => array('type'=>'multiselect', 'name'=>'select_user_role', 'class'=>'thwma_user_role_options', 'label'=>__('Select User Types', 'woocommerce-multiple-addresses-pro'), 'placeholder'=>'Select user roles', 'hint_text'=>'The chosen user types will not be able to manage saved addresses. The saved addresses will be available at checkout page to choose from while placing an order.', 'options'=>$user_types_exist),
				'additional_styles' => array('title'=>__('Additional CSS', 'woocommerce-multiple-addresses-pro'), 'type'=>'separator', 'colspan'=>'3'),
				'custom_styles' => array('name'=>'custom_styles', 'label'=>__('Add Custom Styles', 'woocommerce-multiple-addresses-pro'), 'type'=>'textarea', 'id'=>'thwma-css-textarea'),
			);
		}

		public function render_page() {
			$this->render_tabs();
			$this->render_content();
			$this->render_import_export_settings();
		}

		public function save_advanced_settings($settings) {
			$result = update_option(THWMA_Utils::OPTION_KEY_ADVANCED_SETTINGS, $settings);
			return $result;
		}

		private function reset_settings() {
			delete_option(THWMA_Utils::OPTION_KEY_ADVANCED_SETTINGS);
			echo '<div class="updated"><p>'. __('Settings successfully reset', 'woocommerce-multiple-addresses-pro') .'</p></div>';
		}

		private function save_settings() {
			$settings = array();
			$prefix = 'i_';
			if(!empty($this->settings_fields) && is_array($this->settings_fields)) {
				foreach ($this->settings_fields as $name => $field) {
					if(isset($field['name'])) {
						$value = '';

						if($field['type'] === 'checkbox') {
							 $value = isset($_POST[$prefix.$name]) ? 'yes' : 'no';
						} else if($field['type'] === 'multiselect_grouped' || $field['type'] === 'multiselect') {
							$value = !empty($_POST[$prefix.$name]) ? $_POST[$prefix.$name] : '';
							$value = is_array($value) ? implode(',', $value) : $value;
						} else if($field['type'] === 'text' || $field['type'] === 'textarea') {
							$value = !empty($_POST[$prefix.$name]) ? sanitize_textarea_field($_POST[$prefix.$name]) : '';
							$value = !empty($value) ? stripslashes(trim($value)) : '';
						} else {
							$value = !empty($_POST[$prefix.$name]) ? sanitize_text_field($_POST[$prefix.$name]) : '';
						}
						$settings[$name] = $value;
					}
				}
			}
			$result = $this->save_advanced_settings($settings);
			if ($result == true) {
				echo '<div class="updated"><p>'. esc_html__('Your changes were saved.', 'woocommerce-multiple-addresses-pro') .'</p></div>';
			} else {
				echo '<div class="error"><p>'. esc_html__('Your changes were not saved due to an error (or you made none!).', 'woocommerce-multiple-addresses-pro') .'</p></div>';
			}
		}

		/**
         * The render content function.
         */
		private function render_content() {
			if(isset($_POST['reset_settings'])) {
				if( check_admin_referer( 'advanced_settings_form', 'thwma_advanced_settings_form')) {
					$this->reset_settings();
				}
			}
			if(isset($_POST['save_settings'])) {
				if( check_admin_referer( 'advanced_settings_form', 'thwma_advanced_settings_form')) {
					$this->save_settings();
				}
			}

    	$settings_field = $this->get_advanced_settings_fields();
    	$settings = THWMA_Utils::get_advanced_settings();
    	$user_role = $this->settings_fields['select_user_role'];
			$tooltip = (isset($user_role['hint_text']) && !empty($user_role['hint_text'])) ? $user_role['hint_text'] : false;
    	$addr_format = $this->settings_fields['address_formats'];
			$addrf_tooltip = (isset($addr_format['hint_text']) && !empty($addr_format['hint_text'])) ? $addr_format['hint_text'] : false; ?>
      <div style="padding-left: 30px;">
	  	<form id="advanced_settings_form" method="post" action="" name="thwma_advanced_settings_form">
	  		<?php if (function_exists('wp_nonce_field')) {
                    wp_nonce_field('advanced_settings_form', 'thwma_advanced_settings_form');
                } ?>
              <!--<h2>Custom Fields Display Settings</h2>
              <p>The following options affect how prices are displayed on the frontend.</p>-->
              <table class="form-table thpladmin-form-table">
                  <tbody>
                  	<tr>
                  		<?php $this->render_form_section_separator($settings_field['section_address_autofill']);?>
                  	</tr>
                  	<tr>
                  		<td>
                  			<p><a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank"><?php echo esc_html__('Click here to get your API Key', 'woocommerce-multiple-addresses-pro');?></a></p>
                  		</td>
                  	</tr>
                  	<tr>
		            	<?php $settings_field['enable_autofill']['value'] = isset($settings['enable_autofill']) ? $settings['enable_autofill'] : '';
		            	if($settings_field['enable_autofill']['value'] == 'yes') {
		            		$settings_field['enable_autofill']['checked']=1;
		            	}
		            	$this->render_form_field_element($settings_field['enable_autofill']); ?>
		            </tr>
                  	<tr>

                  		<?php $settings_field['autofill_apikey']['value'] = isset($settings['autofill_apikey']) ? $settings['autofill_apikey'] : '';
		            	$this->render_form_field_element($settings_field['autofill_apikey'], $this->left_cell_props); ?>
                  	</tr>
                 		<tr>
		            	<?php $this->render_form_section_separator($settings_field['section_address_fields']);?>
		            </tr>
		            <tr>
		            	<?php $settings_field['address_formats']['value'] = isset($settings['address_formats']) ? $settings['address_formats'] : '';
		            	$this->render_form_field_element($settings_field['address_formats'], $this->left_cell_props); ?>
		            </tr>
		            <tr>
		            	<?php $this->render_form_section_separator($settings_field['other_settings']); ?>
		            </tr>
		            <tr>
                  		<?php $settings_field['disable_address_management']['value'] = isset($settings['disable_address_management']) ? $settings['disable_address_management'] : '';
		            	if($settings_field['disable_address_management']['value'] == 'yes') {
		            		$settings_field['disable_address_management']['checked']=1;
		            	}
		            	$this->render_form_field_element($settings_field['disable_address_management'], $this->cell_props_CB); ?>
                  	</tr>
                  	<tr>
                  		<?php $settings_field['select_user_role']['value'] = isset($settings['select_user_role']) ? $settings['select_user_role'] : '';

                  		if($settings_field['select_user_role']['value'] == '') {
                  			$settings_field['select_user_role']['value'] = isset($settings['hidden_user_role']) ? $settings['hidden_user_role'] : '';
                  		}

		            	$this->render_form_field_element($settings_field['select_user_role'], $this->left_cell_props);

		            	$settings_field['hidden_user_role']['value'] = isset($settings['hidden_user_role']) ? $settings['hidden_user_role'] : '';
		            	$this->render_form_field_element($settings_field['hidden_user_role'], $this->left_cell_props); ?>
                  	</tr>
										<tr>
										<?php	$this->render_form_section_separator($settings_field['additional_styles']); ?>
										</tr>
										<tr>
											<?php
											$settings_field['custom_styles']['value'] = isset($settings['custom_styles']) ? $settings['custom_styles'] : '';
											$this->render_form_field_element($settings_field['custom_styles'], $this->left_cell_props) ?>
										</tr>
                  </tbody>
              </table>
              <p class="submit">
              	<?php $reset_msg = esc_html__('Are you sure you want to reset to default settings? all your changes will be deleted.', 'woocommerce-multiple-addresses-pro'); ?>
				<input type="submit" name="save_settings" class="button-primary" value="<?php echo esc_html__('Save changes', 'woocommerce-multiple-addresses-pro'); ?>">
                  <input type="submit" name="reset_settings" class="button" value="<?php echo esc_html__('Reset to Default', 'woocommerce-multiple-addresses-pro'); ?>" onclick="return confirm('<?php echo $reset_msg ; ?>');">
          	</p>
          </form>
			</div>
	    <?php }

	    /**
         * Function for prepare the Import and Export settings.
         *
         * @return void
         */
		public function prepare_plugin_settings() {
			$settings_sections = get_option(THWMA_Utils::OPTION_KEY_THWMA_SETTINGS);
			$settings_custom =  get_option(THWMA_Utils::OPTION_KEY_SECTION_SETTINGS);
			$settings_advanced = get_option(THWMA_Utils::OPTION_KEY_ADVANCED_SETTINGS);
			$plugin_settings = array(
				'OPTION_KEY_THWMA_SETTINGS' => $settings_sections,
				//'OPTION_KEY_SECTION_HOOK_MAP' => $settings_hook_map,
				'OPTION_KEY_CUSTOM_MAPPING_SETTINGS' => $settings_custom,
				'OPTION_KEY_ADVANCED_SETTINGS' => $settings_advanced,
			);
			return base64_encode(serialize($plugin_settings));
		}

		/**
         * Function for render theImport and Export settings.
         *
         * @return void
         */
		public function render_import_export_settings() {
			if(isset($_POST['save_plugin_settings'])) {
				if( check_admin_referer( 'import_export_settings_form', 'thwma_import_export_settings_form')) {
					$result = $this->save_plugin_settings();
				}
			}
			if(isset($_POST['import_settings'])) {
			}
			$plugin_settings = $this->prepare_plugin_settings();
			if(isset($_POST['export_settings']))
				echo $this->export_settings($plugin_settings);
			$imp_exp_fields = array(
				'section_import_export' => array('title'=>esc_html__('Backup and Import Settings', 'woocommerce-multiple-addresses-pro'), 'type'=>'separator', 'colspan'=>'3'),
				'settings_data' => array(
					'name'=>'settings_data', 'label'=>esc_html__('Plugin Settings Data', 'woocommerce-multiple-addresses-pro'), 'type'=>'textarea', 'value' => $plugin_settings,
					'sub_label'=>esc_html__('You can transfer the saved settings data between different installs by copying the text inside the text box. To import data from another install, replace the data in the text box with the one from another install and click "Import Settings".', 'woocommerce-multiple-addresses-pro'),
					//'sub_label'=>'You can insert the settings data to the textarea field to import the settings from one site to another website.'
				),
			); ?>
			<div style="padding-left: 30px;">
			    <form id="import_export_settings_form" method="post" action="" class="clear" name="thwma_import_export_settings_form">
			    	<?php if (function_exists('wp_nonce_field')) {
                        wp_nonce_field('import_export_settings_form', 'thwma_import_export_settings_form');
                    } ?>
	                <table class="form-table thpladmin-form-table">
	                    <tbody>
	                    <?php if(!empty($imp_exp_fields) && is_array($imp_exp_fields)) {
		                    foreach($imp_exp_fields as $name => $field) {
								if($field['type'] === 'separator') {
									$this->render_form_section_separator($field);
								} else { ?>
									<tr valign="top">
										<?php if($field['type'] === 'checkbox') {
											$this->render_form_field_element($field, $this->cell_props_CB, false);
										} else if($field['type'] === 'multiselect') {
											$this->render_form_field_element($field, $cell_props);
										} else if($field['type'] === 'textarea') {
											$this->render_form_field_element($field, $this->cell_props_TA);
										} else {
											$this->render_form_field_element($field, $cell_props);
										} ?>
									</tr>
		                    	<?php }
							}
						} ?>
	                    </tbody>
						<tfoot>
							<tr valign="top">
								<td colspan="2">&nbsp;</td>
								<td class="submit">
									<input type="submit" name="save_plugin_settings" class="button-primary" value="<?php echo esc_html__('Import Settings', 'woocommerce-multiple-addresses-pro'); ?>" >
									<!--<input type="submit" name="import_settings" class="button" value="Import Settings(CSV)">-->
									<!--<input type="submit" name="export_settings" class="button" value="Export Settings(CSV)">-->
								</td>
							</tr>
						</tfoot>
	                </table>
	            </form>
	    	</div>
		<?php }

		/**
         * Function for save plugin settings.
         *
         * @return int
         */
		public function save_plugin_settings() {
			if(isset($_POST['i_settings_data']) && !empty($_POST['i_settings_data'])) {
				$settings_data_encoded = sanitize_text_field($_POST['i_settings_data']);
				$settings = unserialize(base64_decode($settings_data_encoded));
				$result='';
				$result1='';
				$result2='';
				$result3='';
				if($settings && is_array($settings)) {
					foreach($settings as $key => $value) {
						if($key === 'OPTION_KEY_THWMA_SETTINGS') {
							$result = update_option(THWMA_Utils::OPTION_KEY_THWMA_SETTINGS, $value);
						}
						if($key === 'OPTION_KEY_CUSTOM_MAPPING_SETTINGS') {
							$result1 = update_option(THWMA_Utils::OPTION_KEY_SECTION_SETTINGS, $value);
						}
						if($key === 'OPTION_KEY_ADVANCED_SETTINGS') {
							$result2 = update_option(THWMA_Utils::OPTION_KEY_ADVANCED_SETTINGS, $value);
						}

					}
				}
				if($result||$result1||$result3 ||$result2) {
					echo '<div class="updated"><p>'. esc_html__('Your Settings Updated.', 'woocommerce-multiple-addresses-pro') .'</p></div>';
					return true;
				} else {
					echo '<div class="error"><p>'. esc_html__('Your changes were not saved due to an error (or you made none!).', 'woocommerce-multiple-addresses-pro') .'</p></div>';
					return false;
				}
			}
		}

		/**
         * Function for export settings(Ajax function).
         *
         * @param array $settings The settings data
         */
		public function export_settings($settings) {
			ob_clean();
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private", false);
			header("Content-Type: text/csv");
			header("Content-Disposition: attachment; filename=\"wcfe-checkout-field-editor-settings.csv\";");
			echo $settings;
	        ob_flush();
	     	exit;
		}

		/**
         * Function for import settings(Ajax function).
         */
		public function import_settings() {
		}
	}
endif;
