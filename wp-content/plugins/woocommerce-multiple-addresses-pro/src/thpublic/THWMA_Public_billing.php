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

if(!class_exists('THWMA_Public_billing')) :

	/**
	 * public class
	 */

	class THWMA_Public_billing extends THWMA_Public {
		const DEFAULT_BILLING_ADDRESS_KEY  = 'thwma-billing-alt';
        
		public function register() {
			parent::register();
			add_action('after_setup_theme', array($this, 'define_public_hooks'));

		}
		

		/**
         * Function for enqueue public hooks.
         */

		public function define_public_hooks(){
			parent::define_public_hooks();

			add_action('woocommerce_before_checkout_billing_form', array($this, 'address_above_billing_form'));
			add_action('woocommerce_after_checkout_billing_form', array($this, 'address_below_billing_form'));
			
			add_action('woocommerce_checkout_order_processed', array($this, 'update_custom_billing_address_from_checkout'), 10, 3);
			add_action('wp_ajax_get_address_with_id', array($this, 'get_addresses_by_id'));
	    	add_action('wp_ajax_nopriv_get_address_with_id', array($this, 'get_addresses_by_id'));

	    	add_action('wp_ajax_delete_address_with_id', array($this, 'delete_address_from_checkout'));
	    	add_action('wp_ajax_nopriv_delete_address_with_id', array($this, 'delete_address_from_checkout'));

			add_action('wp_ajax_set_default_address', array($this, 'default_address_from_checkout'));
	    	add_action('wp_ajax_nopriv_set_default_address', array($this, 'default_address_from_checkout'));

	    	add_filter('woocommerce_billing_fields', array($this, 'prepare_address_fields_before_billing'), 1500, 2);

		}

		

		/**
         * Function for set address above billing form (Checkout page - address display position)
         *
         * @return void.
         */

		public function address_above_billing_form() {
			$settings = THWMA_Utils::get_setting_value('settings_billing');
			if (is_user_logged_in()) {
				if($settings && !empty($settings)) {
					if($settings['enable_billing'] == 'yes') {
						if($settings['billing_display_position'] == 'above') {
							if($settings['billing_display'] == 'popup_display') {
								$this->add_tile_to_checkout_billing_fields();
							}
							elseif($settings['billing_display'] == 'dropdown_display') {
								$this->add_dd_to_checkout_billing();
							}
							else {
								//$this->add_accordion_to_checkout_billing();
							}
						}
					}
				}
			}
		}

		public function address_below_billing_form($checkout) {
			$settings = THWMA_Utils::get_setting_value('settings_billing');
			if (is_user_logged_in()) {
				if($settings && !empty($settings)) {
					if($settings['enable_billing'] == 'yes') {
						if($settings['billing_display_position']=='below') {
							if($settings['billing_display']=='popup_display') {
								$this->add_tile_to_checkout_billing_fields();
							}
							elseif($settings['billing_display']=='dropdown_display') {
								$this->add_dd_to_checkout_billing();
							}
							else {
								//$this->add_accordion_to_checkout_billing();
							}

						}
					}
				}
			}
		}

		/**
         * Function for set checkout popup billing tiles (Checkout page - address display style(popup))
         *
         * @return void.
         */

		public function add_tile_to_checkout_billing_fields() {
			$settings = THWMA_Utils::get_setting_value('settings_styles');
			if($settings['enable_button_styles'] == 'yes'){
				$button_color = $settings['button_background_color'];
				$text_color = $settings['button_text_color'];
				$button_padding = $settings['button_padding'];
				$field_style = 'btn-different-address';
			}
			$button_color = isset($button_color) ? $button_color : '';
			$text_color = isset($text_color) ? $text_color : '';
			$button_padding = isset($button_padding) ? $button_padding : '';
			$field_style = isset($field_style)? $field_style : 'button';
			$customer_id = get_current_user_id();
			if (is_user_logged_in()) {
				$settings = THWMA_Utils::get_setting_value('settings_billing');
				//$button_color = THWMA_Utils::get_setting_value('button_color');
				$theme_class_name = $this->check_current_theme();
				$theme_class = $theme_class_name.'_tile_field';
				$disable_adr_mngmnt = self::disable_address_mnagement();
				$billing_display_text = htmlspecialchars($settings['billing_display_text']); ?>
				<div id="billing_tiles" class="<?php echo $theme_class; ?>">
					<input type="hidden" id="button_color" name="button_color" value="<?php echo $button_color; ?>">
					<input type="hidden" id="text_color" name="text_color" value="<?php  echo $text_color; ?>">
					<input type="hidden" id="button_padding" name="button_padding" value="<?php  echo $button_padding; ?>">
					<?php if($settings['billing_display_title'] == 'button') { ?>
						<div class = "add-address thwma-add-adr btn-checkout ">
							<a id = "thwma-popup-show-billing" class="btn-add-adrs-checkout <?php echo $field_style; ?> primary is-outline"  onclick="thwma_show_billing_popup(event)">
							<!-- <button type="button" id = "thwma-popup-show-billing" class="btn-add-adrs-checkout"  onclick="thwma_show_billing_popup(event)"> -->
							<?php esc_html_e($billing_display_text, 'woocommerce-multiple-addresses-pro'); ?>
							</a>
							<!-- </button> -->
						</div>
					<?php } else { ?>
						<a href='#' id="thma-popup-show-billing_link" class='th-popup-billing th-pop-link' onclick="thwma_show_billing_popup(event)">
						<?php esc_html_e($billing_display_text, 'woocommerce-multiple-addresses-pro'); ?>
						</a>
					<?php }
					$all_address='';
					$html_address = $this->get_tile_field($customer_id, 'billing');
						$theme_class_name = $this->check_current_theme();
						$theme_class = $theme_class_name.'_tile_field';
						$all_address.= '<div id="thwma-billing-tile-field" class="'.esc_attr($theme_class).'">
							<div>'. $html_address.'</div>
						</div>' ;?>
					<div class="u-columns woocommerce-Addresses col2-set addresses  ">
						<?php echo $all_address; ?>
					</div>
				</div>
			<?php }
		}

		/**
         * Function for add billing address to dropdown(Checkout page - address display style(dropdown))
         * @return void
         */
		public function add_dd_to_checkout_billing() {
			$customer_id = get_current_user_id();
			$custom_addresses = THWMA_Utils::get_custom_addresses($customer_id, 'billing');
			$default_bil_address = THWMA_Utils::get_custom_addresses($customer_id, 'default_billing');
			$same_address = THWMA_Utils::is_same_address_exists($customer_id, 'billing');
			$default_address = $default_bil_address ? $default_bil_address : $same_address ;
			$default_address = $same_address;

			// Address limit.
			$address_limit = THWMA_Utils::get_setting_value('settings_billing', 'billing_address_limit');
			if (!is_numeric($address_limit)) {
                $address_limit = 0;
            }

			$options = array();
			if(is_array($custom_addresses)) {
	        	$custom_address = $custom_addresses;
	        } else {
				$custom_address = array();
				$def_address = THWMA_Utils::get_default_address($customer_id, 'billing');
				if(array_filter($def_address) && (count(array_filter($def_address)) > 2)) {
					$custom_address ['selected_address'] = $def_address;
				}
			}
			if($custom_address) {
				$address_count = count($custom_address);
				if($address_limit && ($address_count > 0)) {
					$billing_heading = (isset($custom_address[$default_address]['billing_heading']) && $custom_address[$default_address]['billing_heading'] !='') ? $custom_address[$default_address]['billing_heading'] : esc_html__('', '');
					if($default_address) {
						if(isset($options[$default_address])) {
							$options[$default_address] = $billing_heading .'&nbsp - &nbsp'.$custom_address[$default_address]['billing_address_1'];
						}
					} else {
						$default_address = 'selected_address';
						$options[$default_address]  = esc_html__('Billing Address', 'woocommerce-multiple-addresses-pro');
					}

					if(is_array($custom_address)) {

						// Default address.
						foreach ($custom_address as $key => $address_values) {
							$adrsvalues_to_dd = array();
							if($key == $default_address) {
								$heading = (isset($address_values['billing_heading']) && $address_values['billing_heading'] != '') ? $address_values['billing_heading'] : esc_html__('', '');
								if(apply_filters('thwma_remove_dropdown_address_format', true)) {
									if(!empty($address_values) && is_array($address_values)) {
										foreach ($address_values as $adrs_key => $adrs_value) {
											if($adrs_key == 'billing_address_1' || $adrs_key =='billing_address_2' || $adrs_key =='billing_city' || $adrs_key =='billing_state' || $adrs_key =='billing_postcode') {
												if($adrs_value) {
													$adrsvalues_to_dd[] = $adrs_value;
												}
											}
										}
									}
								} else {
									$type = 'billing';
									$separator = '</br>';
									$new_address = $custom_address[$default_address];
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

						$default_bil_address = THWMA_Utils::get_custom_addresses($customer_id, 'default_billing');
						$same_address = THWMA_Utils::is_same_address_exists($customer_id, 'billing');
						$get_default_address = $same_address ? $same_address : $default_bil_address ;
						$no_of_tile_display = '';
						if(!empty($get_default_address)) {
							$no_of_tile_display = 1;
						} else {
							$no_of_tile_display = 0;
						}
						if($address_limit > $no_of_tile_display) {
							foreach ($custom_address as $key => $address_values) {
								if($key != $default_address) {
									$heading = (isset($address_values['billing_heading']) && $address_values['billing_heading'] != '') ? $address_values['billing_heading'] : esc_html__('', '');
									$adrsvalues_to_dd = array();
									// $dropdown_adr_ft = apply_filters('thwma_dropdown_address_format', false);
									if(apply_filters('thwma_remove_dropdown_address_format', true)) {
										if(!empty($address_values) && is_array($address_values)) {
											foreach ($address_values as $adrs_key => $adrs_value) {
												if($adrs_key == 'billing_address_1' || $adrs_key =='billing_address_2' || $adrs_key =='billing_city' || $adrs_key =='billing_state' || $adrs_key =='billing_postcode') {
													if($adrs_value) {
														$adrsvalues_to_dd[] = $adrs_value;
													}
												}
											}
										}
									} else {
										$type = 'billing';
										$separator = '</br>';
										$new_address = $custom_address[$key];
										$new_address_format = THWMA_Utils::get_formated_address($type, $new_address);
										$options_arr = THWMA_Utils::thwma_get_formatted_address($new_address_format);
										$adrsvalues_to_dd = explode('<br/>', $options_arr);
									}
									$adrs_string = implode(', ', $adrsvalues_to_dd);
									//if((isset($heading)) && ($heading!= '') && (!is_array($heading))) {
									if(isset($heading) && $heading != '') {
										$options[$key] = $heading .' - '.$adrs_string;
									} else {
										$options[$key] = $adrs_string;
									}
									// if($i >= $address_limit-2) {
		       //                          break;
		       //                      }

									// Billing dropdown.
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
					$address_count = count($custom_address);
					$disable_adr_mngmnt = self::disable_address_mnagement();
					if($disable_adr_mngmnt != true){
						$disable_adr_mngmnt = apply_filters('disable_address_management_on_checkout', false);
					}
					if($disable_adr_mngmnt != true) {
						if(((int)($address_limit)) > $address_count) {
							$options['add_address'] = esc_html__('Add New Address', 'woocommerce-multiple-addresses-pro');
						}
					}
				}
			} else {
				$default_address = 'selected_address';
				$options[$default_address] = esc_html__('Billing Address', 'woocommerce-multiple-addresses-pro');
			}
			$alt_field = array(
				'required' => false,
				'class'    => array('form-row form-row-wide enhanced_select select2-selection th-select'),
				'clear'    => true,
				'type'     => 'select',
				//'required' => 'true',
				'label'    => THWMA_Utils::get_setting_value('settings_billing','billing_display_text'),
				//'placeholder' =>esc_html__('Choose an Address..',''),
				'options'  => $options
			);
			//woocommerce_form_field(self::DEFAULT_BILLING_ADDRESS_KEY, $alt_field, $options[$default_address]);
			$this->thwma_woocommerce_form_field(self::DEFAULT_BILLING_ADDRESS_KEY, $alt_field, $default_address);
		}

		/**
         * Function for update custom billing address from checkout page(checkout page)
         *
         * @param integer $order_id The order id
         * @param array $posted_data The posted data info
         * @param array $order The order datas
         */
        public function update_custom_billing_address_from_checkout($order_id, $posted_data, $order) {
            if (is_user_logged_in()) {
                $address_key = isset($posted_data['thwma_hidden_field_billing']) ? $posted_data['thwma_hidden_field_billing'] : '';
                $user_id = get_current_user_id();
                $custom_key = THWMA_Utils::get_custom_addresses($user_id, 'default_billing');
                $same_address_key = THWMA_Utils::is_same_address_exists($user_id, 'billing');
                $default_key = ($custom_key) ? $custom_key : $same_address_key ;
                $this->update_address_from_checkout('billing', $address_key, $posted_data, $default_key);
                if($custom_key) {
                    $modify = apply_filters('thwma_modify_billing_update_address', true);
                    if($modify) {
                        $this->change_default_address($user_id, 'billing', $default_key);
                    } else {
                        if ($address_key == 'add_address') {
                            $new_key_id = (THWMA_Utils::get_new_custom_id($user_id, 'billing')) - 1;
                            $new_key = 'address_'.$new_key_id;
                            $this->change_default_address($user_id, 'billing', $new_key);
                        } elseif(!empty($address_key)) {
                            $this->change_default_address($user_id, 'billing', $address_key);
                        }
                    }
                }
            }
        }

		/**
         * Function for get address by id(ajax response-checkout page)
         *
         * @return void
         */
		public function get_addresses_by_id() {
			check_ajax_referer( 'get-address-with-id', 'security' );
			$address_key = isset($_POST['selected_address_id']) ? sanitize_key($_POST['selected_address_id']) : '';
			$type = isset($_POST['selected_type']) ? sanitize_key($_POST['selected_type']) : '';
			$section_name = isset($_POST['section_name']) ? sanitize_key($_POST['section_name']) : '';
			if(is_user_logged_in()) {
				$customer_id = get_current_user_id();
				if(!empty($section_name) && $address_key == 'section_address') {
					$custom_address = $this->get_default_section_address($customer_id, $section_name);
				} else {
					if($address_key == 'selected_address') {
						$custom_address = THWMA_Utils::get_default_address($customer_id, $type);
					} else {
						$custom_address = THWMA_Utils::get_custom_addresses($customer_id, $type, $address_key);
					}
				}
			} else {
				$custom_address = THWMA_Utils::get_custom_addresses_of_guest_user('shipping', $address_key);
			}
			if(!empty($custom_address) && is_array($custom_address)) {
				foreach ($custom_address as $key => $value) {
					if(is_array($value)) {
						$custom_address_data = $value;
					} else {
						$custom_address_data = $custom_address;
					}
				}
				$custom_address = $custom_address_data;
			}
			wp_send_json($custom_address);
		}

		/**
         * Function for delete address from checkout(ajax response-checkout page)
         *
         * @return void
         */
		public function delete_address_from_checkout() {
			check_ajax_referer( 'delete-address-with-id', 'security' );
			$address_key = isset($_POST['selected_address_id']) ? sanitize_key($_POST['selected_address_id']) : '';
			$type = isset($_POST['selected_type']) ? sanitize_key($_POST['selected_type']) : '';
			$customer_id = get_current_user_id();
			THWMA_Utils::delete($customer_id, $type, $address_key);
			$output_shipping = $this->get_tile_field($customer_id, 'shipping');
			$output_billing = $this->get_tile_field($customer_id, 'billing');
			$response = array(
				'result_billing' => $output_billing,
				'result_shipping' => $output_shipping,
			);
			wp_send_json($response);
		}

		/**
         * Function for set default address from checkout page(ajax response-checkout page)
         *
         * @return void
         */
		public function default_address_from_checkout() {
			check_ajax_referer( 'set-default-address', 'security' );
			$address_key = isset($_POST['selected_address_id']) ? sanitize_key($_POST['selected_address_id']) : '';
			$type = isset($_POST['selected_type']) ? sanitize_key($_POST['selected_type']) : '';
			$user_id = get_current_user_id();
			$this->change_default_address($user_id, $type, $address_key);
			$output_shipping = $this->get_tile_field($user_id, 'shipping');
			$output_billing = $this->get_tile_field($user_id, 'billing');
			$response = array(
				'result_billing' => $output_billing,
				'result_shipping' => $output_shipping,
			);
			wp_send_json($response);
		}

		/**
         * Function for prepare address fields before billing (checkout page)
         *
         * @param array $fields The field datas
         * @param string $country The country info
         *
         * @return array
         */
		public function prepare_address_fields_before_billing($fields, $country) {
			if(!empty($fields) && is_array($fields)) {
				foreach ($fields as $key => $value) {
					if ('billing_state' === $key) {
						if(!isset($fields[$key]['country_field'])) {
							$fields[$key]['country_field'] = 'billing_country';
						}
					}
				}
			}
			return $fields;
		}
		
	}
	// new THWMA_Public_billing($this->get_plugin_name(), $this->get_version());

endif;

 ?>
