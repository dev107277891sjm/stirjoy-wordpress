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
use \THWCFE_Utils;

if(!defined('WPINC')) {	
	die; 
}

if(!class_exists('THWMA_Admin_Settings_custom')) :

	/**
     * The custom admin settings class.
     */ 
	class THWMA_Admin_Settings_custom extends THWMA_Admin_Settings {
		protected static $_instance = null;		
		private $cell_props_L = array();
		private $cell_props_R = array();
		private $cell_props_CB = array();
		private $cell_props_CBS = array();
		private $cell_props_CBL = array();
		private $cell_props_CP = array();		
		private $settings_props = array();
		
		/**
         * Constructor
         */ 
		public function __construct() {
			parent::__construct('custom_section_settings', '');
			$this->init_constants();
			$this->page_id = 'custom_section_settings';
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
		
		/**
         * Function for initialise constants.
         */
		public function init_constants() {
			$this->cell_props_L = array(
				'label_cell_props' => 'width="23%"', 
				'input_cell_props' => 'width="75%"', 
				'input_width' => '250px',  
			);
			$this->cell_props_R = array(
				'label_cell_props' => 'width="13%"', 
				'input_cell_props' => 'width="34%"', 
				'input_width' => '250px', 
			);
			$this->cell_props_CB = array(
				'label_props' => 'style="margin-right: 40px;"', 
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
		} 

		/**
         * Function for render action row.
         */
		private function render_actions_row() {
			$sections = THWCFE_Utils::get_custom_sections();
			$custom_sections = array();
			if(!empty($sections) && is_array($sections)) {
				foreach ($sections as $name => $values) {
					if($name != 'billing'  && $name != 'shipping' && $name != 'additional') {
						$custom_sections[$name] = $values;
					} 
				}
			} ?>			
	        <th colspan="3">
	        	<select name="def_sections" class="th-map-select" id="thwma_def_section">
		        	<!-- <option value="">Select default section</option> --> 
		        	<option value=""></option>	
					<option value="billing"> <?php echo esc_html__('Billing', 'woocommerce-multiple-addresses-pro'); ?></option>
					<option value="shipping"><?php echo esc_html__('Shipping', 'woocommerce-multiple-addresses-pro'); ?></option>
				</select>
	        	<select name="cus_sections" class="th-map-select" id="thwma_cus_section">
		        	<!-- <option value="">Select custom section </option> --> 
		        	<option value=""></option>
		        	<?php if(!empty($custom_sections) && is_array($custom_sections)) {
						foreach($custom_sections as $name => $value) { ?>
							<option value="<?php echo $name; ?>"><?php echo esc_html__($name, 'woocommerce-multiple-addresses-pro'); ?> </option>
						<?php } 
					} ?>
				</select>				
	            <input type="submit" class="button-primary add_section_map_btn" name ="s_add_section_map" value="<?php echo esc_html__('+ Add', 'woocommerce-multiple-addresses-pro'); ?>"></input>
	           	<?php $reset_msg = esc_html__('Are you sure you want to reset to default settings? all your changes will be deleted.', 'woocommerce-multiple-addresses-pro');
	           	?>
	           	<input type="submit" name="reset_settings" class="button-secondary" value="<?php esc_html_e('Reset Settings', 'woocommerce-multiple-addresses-pro'); ?>" style="float:none; margin-right: 5px;" 
	            onclick="return confirm('<?php echo $reset_msg; ?>');">
	        </th> 
		<?php }
		
		/**
         * Function for render page.
         */
		public function render_page() {
			$this->render_tabs();
			$this->render_content();
		}

		/**
         * Function for populate posted address settings.
         *
         * @return array
         */
		private function populate_posted_address_settings() {
			$posted_section = array();
			$prefix='i_';
			$SETTINGS_PROPS = $this->settings_props;
			if(!empty($SETTINGS_PROPS) && is_array($SETTINGS_PROPS)) {			
				foreach($SETTINGS_PROPS as $section => $fields ) {
					$posted = array();
					if(!empty($fields) && is_array($fields)) {				
						foreach ($fields as $field => $props) {
							$map_field = array();
							if($field == 'section_fields') {
								if(!empty($props) && is_array($props)) {
									foreach ($props as $custom_fld) {
										$name = $custom_fld['name'];
										$value = isset($_POST[$prefix.$name]) ? sanitize_text_field($_POST[$prefix.$name]) : $props['value'];
										$map_field[$name ] = $value;
									}
								}
								$posted['custom_section'] = $map_field;
							} else {
								$name  = $props['name'];
								if($props['type']== 'checkbox') {
									$value = isset($_POST[$prefix.$name]) ? 'yes' : 'no';
								} else {
									$value = isset($_POST[$prefix.$name]) ? sanitize_text_field($_POST[$prefix.$name]) : $props['value'];
								}
								$posted[$name] = $value;
							}		
						}
					}
					$posted_section[$section] = $posted;
				}
			}
			return $posted_section;
		}
		
		/**
         * Function for return reset to default.
         *
         * @return string
         */
		public function reset_to_default() {			
			delete_option(THWMA_Utils::OPTION_KEY_SECTION_SETTINGS);			
			return '<div class="updated"><p>'. esc_html__('Settings successfully reset', 'woocommerce-multiple-addresses-pro') .'</p></div>';
		}

		/**
         * Function for save mapping fields.
         * 
         * @param string $sect_name The mapping section name
         *
         * @return void
         */
		private function save_mapping_fields($sect_name) {
			$def_fields = isset($_POST[$sect_name.'_def_select']) ? $_POST[$sect_name.'_def_select'] : '';
			$sec_fields = isset($_POST[$sect_name.'_sec_select']) ? $_POST[$sect_name.'_sec_select'] : '';
			$save_def_fields = array_map( 'sanitize_text_field', wp_unslash( $def_fields ) );
			$save_sec_fields = array_map( 'sanitize_text_field', wp_unslash( $sec_fields ) );
			$saved_fields = array();
			$saved_fields  = THWMA_Utils::get_custom_section_settings();
			$map_array = $saved_fields;
			if(!empty($saved_fields) && is_array($saved_fields)) {
				foreach ($saved_fields as $section_name => $section_value) {
					if($section_name == $sect_name) {
						$new_array = array();
						if(!empty($save_def_fields) && is_array($save_def_fields)) {
							foreach ($save_def_fields as $def_key => $def_value) {
								if(!empty($save_sec_fields) && is_array($save_sec_fields)) {
									foreach ($save_sec_fields as $sec_key => $sec_value) {
										if($sec_value != '' && $def_value != '') {
											if($def_key == $sec_key) {
												$new_array[$def_value] = $sec_value;
											}
										}
									}
								}
							}
						}
						$map_array[$section_name]['map_fields'] =  $new_array;
						$saved_fields = array_merge($saved_fields, $map_array);
					} 
				}
			}				 	
			$result = update_option(THWMA_Utils::OPTION_KEY_SECTION_SETTINGS, $saved_fields); 

			if ($result == true) {
				echo '<div class="updated"><p>'. esc_html__('Your changes were saved.', 'woocommerce-multiple-addresses-pro') .'</p></div>';
			} else {
				echo '<div class="error"><p>'. esc_html__('Your changes were not saved due to an error (or you made none!).', 'woocommerce-multiple-addresses-pro') .'</p></div>';
			}	
		}

		/**
         * Function for enabling mapping fields.
         * 
         * @param string $sect_name The section name
         */
		private function enable_mapping_fields($sect_name) {
			$saved_fields = array();
			//$saved_fields = get_option(THWMA_Utils::OPTION_KEY_SECTION_SETTINGS, true);
			$saved_fields  = THWMA_Utils::get_custom_section_settings();
			if(!empty($saved_fields) && is_array($saved_fields)) {
				foreach ($saved_fields as $section_name => $section_value) {
					if($section_name == $sect_name) {
						$enable_field = array('enable_section' => 'yes');
						if(is_array($section_value)) {
							$new_field[$section_name] = array_merge($section_value, $enable_field);
						} else {
							$new_field[$section_name] = $enable_field;
						} 
					}
				}
			}
			if($new_field) {				
				$saved_fields = array_merge($saved_fields, $new_field);
			}			
			$result = update_option(THWMA_Utils::OPTION_KEY_SECTION_SETTINGS, $saved_fields);
			if ($result == true) {
				echo '<div class="updated"><p>'. esc_html__('Your changes were saved.', 'woocommerce-multiple-addresses-pro') .'</p></div>';
			} else {
				echo '<div class="error"><p>'. esc_html__('Your changes were not saved due to an error (or you made none!).', 'woocommerce-multiple-addresses-pro') .'</p></div>';
			}
		}

		/**
         * Function for disable the mapping fields.
         * 
         * @param string $sectn_name The section name
         */
		public function disable_mapping_fields($sectn_name) {
			$saved_fields = array();
			//$saved_fields = get_option(THWMA_Utils::OPTION_KEY_SECTION_SETTINGS, true);
			$saved_fields  = THWMA_Utils::get_custom_section_settings();
			if(!empty($saved_fields) && is_array($saved_fields)) {
				foreach ($saved_fields as $section_name => $section_value) {
					if($section_name == $sectn_name) {
						$enable_field = array('enable_section' => 'no');
						if(is_array($section_value)) {
							$new_field[$section_name] = array_merge($section_value, $enable_field);
						} else {
							$new_field[$section_name] = $enable_field;
						} 
					}
				}
			}
			if($new_field) {				
				$saved_fields = array_merge($saved_fields, $new_field);
			}			
			$result = update_option(THWMA_Utils::OPTION_KEY_SECTION_SETTINGS, $saved_fields);
			if ($result == true) {
				echo '<div class="updated"><p>'. esc_html__('Your changes were saved.', 'woocommerce-multiple-addresses-pro') .'</p></div>';
			} else {
				echo '<div class="error"><p>'. esc_html__('Your changes were not saved due to an error (or you made none!).', 'woocommerce-multiple-addresses-pro') .'</p></div>';
			}
		}

		/**
         * Function for delete mapping fields.
         * 
         * @param string $sect_name The section name
         */
		private function delete_mapping_fields($sect_name) {
			$saved_fields = get_option(THWMA_Utils::OPTION_KEY_SECTION_SETTINGS, true);
			unset($saved_fields[$sect_name]);
			$result = update_option(THWMA_Utils::OPTION_KEY_SECTION_SETTINGS, $saved_fields);
			if ($result == true) {
				echo '<div class="updated"><p>'. esc_html__('Your changes were saved.', 'woocommerce-multiple-addresses-pro') .'</p></div>';
			} else {
				echo '<div class="error"><p>'. esc_html__('Your changes were not saved due to an error (or you made none!).', 'woocommerce-multiple-addresses-pro') .'</p></div>';
			}				
		}
		
		/**
         * Function for create render content.
         */
		private function render_content() {	
			if(isset($_POST['reset_settings'])) {
				if( check_admin_referer( 'custom_settings_form', 'thwma_custom_settings_form')) {
					echo $this->reset_to_default();
				}
			}
			
			$custom_section  = THWMA_Utils::get_custom_section_settings();
			if(is_array($custom_section) && !empty($custom_section)) {
				foreach ($custom_section as $sectn_name => $sectn_value) {
					if(isset($_POST[$sectn_name.'_map_save'])) {
						if( check_admin_referer( 'custom_settings_form', 'thwma_custom_settings_form')) {
							$this->save_mapping_fields($sectn_name);
						}
					}
					if(isset($_POST[$sectn_name.'_map_delete'])) {
						$this->delete_mapping_fields($sectn_name);
					}
					if(isset($_POST[$sectn_name.'_map_enable'])) {
						$this->enable_mapping_fields($sectn_name);
					}
					if(isset($_POST[$sectn_name.'_map_disable'])) {
						$this->disable_mapping_fields($sectn_name);
					}
				}
			}
			$style = ''; 
			?>
			<div style="margin-top: 20px; margin-right: 30px;">               
			    <form id="thwma_tab_custom_settings_form" method="post" action="" name="thwma_custom_settings_form">
			    	<?php if (function_exists('wp_nonce_field')) {
                        wp_nonce_field('custom_settings_form', 'thwma_custom_settings_form'); 
                    } ?>
	                <table id="" class="wc_gateways widefat thpladmin_steps_table thwma_steps_table" cellspacing="0" >
	                    <thead>
                        <?php $this->render_actions_row(); ?></thead>
                        <tr>
                       		<?php echo $this->create_new_mapping(); ?>
                    	</tr>
	     			</table>
	            </form>
	        </div>         
		<?php }

		/**
         * Function for create a new mapping.
         */
	    private function create_new_mapping() {
	    	$step = $this->prepare_new_section();
			$settings = THWMA_Utils::get_custom_section_settings(); 
			if(is_array($settings) && !empty($settings)) {
				foreach ($settings as $key => $S_value) { 									
					$disabled = is_array($S_value) && isset($S_value['enable_section']) && $S_value['enable_section'] == 'yes' ?  "yes" : "no";
					$fields = self::get_sections_fields($key);
					$to_map = isset($S_value['maped_section'])  ?  $S_value['maped_section'] : "billing";
					$default_fields = self::get_sections_fields($to_map);
					$title =  $this->get_custom_section_title($key);
					$title = $title.'   ';
					?>
					<table width="100%" class="widefat thpladmin_fields_table thwma_admin_fields_table">
						<thead>
							<tr><th colspan="4" class="thpladmin-form-section-title th-section-title" style=" font-weight: 600;"><?php esc_html_e($title, 'woocommerce-multiple-addresses-pro');   esc_html_e( '('.$key.')', ''); ?>  </th></tr>
							<tr>
								<th colspan="2">
									<input type="button" class="button" onclick="addRow('<?php echo $key ?>')" value="<?php esc_html_e('+ Add new mapping', 'woocommerce-multiple-addresses-pro'); ?>" >
									
									<input type="hidden" class="check_map_disable" value="<?php echo esc_attr($disabled) ; ?>">
									<?php if($disabled == 'no') { ?>
										<input type="submit" name="<?php echo esc_attr($key);?>_map_enable"  class="button" value="<?php esc_html_e('Enable', 'woocommerce-multiple-addresses-pro'); ?>"> <?php
									} else { ?>
										<input type="submit" name="<?php echo esc_attr($key);?>_map_disable" class="button" value="<?php esc_html_e('Disable', 'woocommerce-multiple-addresses-pro'); ?>">	<?php
									} ?>
								</th>
								<th>&nbsp;</th>
							</tr>
						</thead>
						<tbody id="<?php echo esc_attr($key) ?>">
							<tr style="display: none;">
								<td style="width: 10%;">
									<input type="hidden" class="custom_addr_name" value="<?php echo esc_attr($key) ?>">
									<select name="<?php echo esc_attr($key); ?>_def_select[]" class="th-map-select thwma-def-select-map-option">
					        			<option value=""></option>
					        			<?php if(!empty($default_fields) && is_array($default_fields)) {
											foreach($default_fields as $name => $value) { ?>
												<option value="<?php echo esc_attr($name); ?>"><?php echo esc_attr($name); ?></option>
											<?php } 
										} ?>
									</select>
								</td>
								<td style="width:10%;">
									<select name = "<?php echo esc_attr($key);?>_sec_select[]" class="th-map-select thwma-sec-select-map-option" >
					        			<option value=""></option>
					        			<?php if($fields && is_array($fields)) {
											foreach($fields as $name => $value) { ?>
												<option value="<?php echo esc_attr($name); ?>"><?php echo esc_attr($name); ?> </option>
											<?php } 
										} ?>
									</select>
								</td>
								<td>
									<button type="button" class="f_add_btn btn-blue" onclick="addRow('<?php echo $key ?>')">+</button>
									<button type="button" class="f_delete_btn btn-red" onclick="thwmadelete(this,<?php echo $key; ?>)"> x </button>	
								</td>
							</tr>
							<?php $map_fields = isset($S_value['map_fields']) ?  $S_value['map_fields'] : '';
							if(is_array($map_fields) && !empty($map_fields)) {
								foreach ($map_fields as $left => $right) { ?>
									<tr>
										<td style="width: 10%;">
											<input type="hidden" class="custom_addr_name" value="<?php echo esc_attr($key) ?>">
											<select name="<?php echo esc_attr($key);?>_def_select[]"  class="th-map-select thwma-def-select-map-option1">
												 <option value=""></option>
			        							<?php if(!empty($default_fields) && is_array($default_fields)) {
				        							foreach($default_fields as $name => $value) {
														if($name == $left) { ?>
															<option value="<?php echo esc_attr($name); ?>" selected><?php echo $left; ?> </option> <?php
														} else { ?>
															<option value="<?php echo esc_attr($name); ?>"><?php echo $name; ?> </option>
														<?php }
													} 
												} ?>
											</select>
										</td>
										<td style="width:10%;">
											<select name = "<?php echo esc_attr($key);?>_sec_select[]" class="th-map-select thwma-sec-select-map-option1">
												<option value=""></option>
			        							<?php if($fields && is_array($fields)) {
													foreach($fields as $name => $value) {							if($name == $right) { ?>
															<option value="<?php echo esc_attr($name); ?>" selected><?php echo $right; ?> </option> <?php
														} else { ?>
															<option value="<?php echo esc_attr($name); ?>"><?php echo $name; ?> </option>
														<?php }
													} 
												} ?>
											</select>
										</td>
										<td>
											<button type="button" class="f_add_btn btn-blue" onclick="addRow('<?php echo esc_attr($key) ?>')">+</button>
											<button type="button" class="f_delete_btn btn-red" onclick="thwmadelete(this,<?php echo $key; ?>)">x</button>					
										</td>
									</tr>
								<?php }
							} else { ?>
								<tr>
									<td style="width:10%;">
										<input type="hidden" class="custom_addr_name" value="<?php echo esc_attr($key) ?>">
										<select name="<?php echo esc_attr($key);?>_def_select[]" class="th-map-select thwma-def-select-map-option1">
					        			<option value=""></option>
					        			<?php 
					        			if(!empty($default_fields) && is_array($default_fields)) {
											foreach($default_fields as $name => $value) {?>
												<option value="<?php echo esc_attr($name); ?>"><?php echo esc_attr($name); ?> </option>
											<?php } 
										} ?>
										</select>
									</td>
									<td style="width:10%;">
										<select name = "<?php echo esc_attr($key);?>_sec_select[]" class="th-map-select thwma-sec-select-map-option1" >
										<option value=""></option>
					        			<?php 
					        			if($fields && is_array($fields)) {
											foreach($fields as $name => $value) { ?>									<option value="<?php echo esc_attr($name); ?>"><?php echo $name; ?> </option>
											<?php }
										} ?>
										</select>
									</td>
									<td>
										<button type="button" class="f_add_btn btn-blue" onclick="addRow('<?php echo $key ?>')">+</button>
										<button type="button" class="f_delete_btn btn-red" onclick="thwmadelete(this,<?php echo $key; ?>)">x</button>									
									</td>
									
								</tr>
							<?php } ?>
						</tbody>
						<tfoot>
							<tr>
								<th colspan="3">
									<?php $dlt_changes = esc_html__('Are you sure you want to delete your changes?', 'woocommerce-multiple-addresses-pro');  ?>
									<input type="submit" name="<?php echo esc_attr($key);?>_map_save" class="button-primary" style="" value="<?php esc_html_e(' Save changes', 'woocommerce-multiple-addresses-pro'); ?>">
									
									<input type="submit" name="<?php echo esc_attr($key);?>_map_delete" class="button" value="<?php esc_html_e('Remove', 'woocommerce-multiple-addresses-pro'); ?>" onclick="return confirm('<?php echo $dlt_changes; ?>');">
								</th>
							</tr>
						</tfoot>
					</table> 
				<?php }	
			}
	    }

	    /**
         * Function for prepare new section.
         */
	    private function prepare_new_section() {
	    	$sectionee = array();
	    	$settings = THWMA_Utils::get_custom_section_settings();	    	
	    	if(isset($_POST['s_add_section_map'])) {
	    		$chosed_section = isset($_POST['cus_sections']) ? sanitize_text_field($_POST['cus_sections']) : '';
	    		$map_section = isset($_POST['def_sections']) ? sanitize_text_field($_POST['def_sections']) : '';
	    		if($chosed_section != '' && $map_section != '') {
	    			$settings_array = array('enable_section' => 'yes', 'maped_section' => $map_section,);
	    			$sectionee[$chosed_section] = $settings_array;
		    	}	    			
	    	}
	    	// Array check.
	    	$settings = is_array($settings) ? $settings : array();
	    	$sectionee = isset($sectionee) ? $sectionee : array();
			$sectionzz = $settings ? array_merge($settings, $sectionee) : $sectionee;
	    	update_option(THWMA_Utils::OPTION_KEY_SECTION_SETTINGS, $sectionzz);  	
	    }
		
		/**
         * Function for get custom section title
         * 
         * @param string $section_key The section key
         *
         * @return array
         */
		private function get_custom_section_title($section_key) {
			$custom_sections = THWCFE_Utils::get_custom_sections();
			if(!empty($custom_sections) && is_array($custom_sections)) {
				foreach ($custom_sections as $name => $fields) {
					if($name == $section_key) {
						return($fields->get_property('title'));
					}
				}
			}
		}

		/**
         * Function for set default fields.
         *
         * @return string
         */
		private function get_default_fields() {
			$sections = THWCFE_Utils::get_custom_sections();
		}

		/**
         * Function for get section fields.
         * 
         * @param string $section_name The section name
         *
         * @return array
         */
		public static function get_sections_fields($section_name) {
			$custom_sections = THWCFE_Utils::get_custom_sections();
			$section_settings_fields = array();
			$section_fields_value = array();
			if(!empty($custom_sections) && is_array($custom_sections)) {
				foreach ($custom_sections as $name => $fields) {
					if($section_name == $name) {
						//$section_fields_value = array();
						$section_fields = $fields->get_property('fields');
						if(!empty($section_fields) && is_array($section_fields)) {
							foreach ($section_fields as $key => $value) {
								if(isset($value->user_meta) && ($value->user_meta =='yes')) {
									$section_fields_value[$key] = $key;
								}
							}
						}
						return $section_fields_value;
					}	
				}
			}
		}		
	}
endif;