<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link			https://themehigh.com
 * @since			1.0.0
 *
 * @package			woocommerce-multiple-addresses-pro
 * @subpackage		woocommerce-multiple-addresses-pro/src/thpublic
 */

namespace Themehigh\WoocommerceMultipleAddressesPro\thpublic;

use Themehigh\WoocommerceMultipleAddressesPro\includes\utils\THWMA_Utils;
use WC_AJAX;
use WP_Query;

if(!defined('WPINC')) {
	die;
}

if(!class_exists('THWMA_Public_shipping')) :

	/**
	 * public class
	 */
	class THWMA_Public_shipping extends THWMA_Public {
		const DEFAULT_SHIPPING_ADDRESS_KEY = 'thwma-shipping-alt';
		
		public $cart_contents;
        
		public function register() {
			parent::register();
			add_action('after_setup_theme', array($this, 'define_public_hooks'));

		}

		/**
         * Function for enqueue public hooks.
         */

		public function define_public_hooks(){
			parent::define_public_hooks();
			add_action('woocommerce_before_checkout_shipping_form', array($this, 'address_above_shipping_form'));
			add_action('woocommerce_after_checkout_shipping_form', array($this, 'address_below_shipping_form'));
			add_action('woocommerce_checkout_order_processed', array($this, 'update_custom_shipping_address_from_checkout'), 10, 3);

			$shipping_section = isset($_POST['ship_to_different_address']) ? sanitize_key($_POST['ship_to_different_address']) : '';

			//if($shipping_section == true){
	    	$settings = THWMA_Utils::get_setting_value('settings_multiple_shipping');
			$cart_shipping_enabled = isset($settings['enable_cart_shipping']) ? $settings['enable_cart_shipping']:'';
			$enable_product_disticty = isset($settings['enable_product_disticty']) ? $settings['enable_product_disticty']:'';
			$user_id = get_current_user_id();
	        $enable_multi_ship = '';

	        // Enable multi-shipping on checkout page.
	        if (is_user_logged_in()) {
	        	$enable_multi_ship = get_user_meta($user_id, THWMA_Utils::USER_META_ENABLE_MULTI_SHIP, true);
	        } else {
	        	$enable_multi_ship = isset($_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP]) ? $_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP]:'';
	        }

	        // Checkout page- multi-shipping.
			add_action('wp_ajax_enable_ship_to_multi_address', array($this, 'enable_ship_to_multi_address'),10);
	    	add_action('wp_ajax_nopriv_enable_ship_to_multi_address', array($this, 'enable_ship_to_multi_address'),10);

	    	// multi-shipping delete address from checkout.
	    	add_action('wp_ajax_delete_address_with_id_cart', array($this, 'delete_address_from_cart'));
	    	add_action('wp_ajax_nopriv_delete_address_with_id_cart', array($this, 'delete_address_from_cart'));

	    	// multi-shipping set to default address from checkout.
			add_action('wp_ajax_set_default_address_cart', array($this, 'default_address_from_cart'));
	    	add_action('wp_ajax_nopriv_set_default_address_cart', array($this, 'default_address_from_cart'));

	    	if($enable_multi_ship == 'yes'){
				// Save seleted addresses-multi shipping.
		    	add_action('wp_ajax_save_multi_selected_shipping', array($this, 'save_multi_selected_shipping'));
		    	add_action('wp_ajax_nopriv_save_multi_selected_shipping', array($this, 'save_multi_selected_shipping'));
		    }
		    if($cart_shipping_enabled == 'yes') {
	        	if($enable_multi_ship == 'yes') {
					if(apply_filters('thwma_enable_multi_shipping_calculation', true)){
						add_filter('woocommerce_shipping_show_shipping_calculator', '__return_false');
						add_action('woocommerce_cart_shipping_packages', array($this, 'thwma_get_shipping_packages'), 10, 1);
						//add_action('woocommerce_package_rates', array($this, 'thwma_woocommerce_package_rates'), 10, 2);

						// fill shipping fields by using default address.
						add_filter( 'woocommerce_checkout_get_value', array($this, 'thwma_fill_shipping_fields_values'), 99, 2 );
					}
	        	} else {
					add_action('woocommerce_cart_shipping_packages', array($this, 'thwma_get_shipping_packages_default'), 10, 1);
				}

				if($enable_product_disticty == 'yes') {
					add_filter('woocommerce_add_cart_item_data', array($this,'thwma_namespace_force_individual_cart_items'), 10, 3);
		    	}
	    	}
	    	if($enable_multi_ship == 'yes'){
				// Multi-addresses add to order meta.
		  		if(THWMA_Utils::woo_version_check('3.3')) {
		  			add_action('woocommerce_new_order_item', array($this, 'thwma_add_addrs_to_new_order_item'), 1, 3);
				} else {
		    		add_action('woocommerce_add_order_item_meta', array($this, 'thwma_add_addrs_to_order_item_meta'), 1, 3);
				}

				// Shipping method disaply on order edit page.
				add_action('woocommerce_checkout_create_order_shipping_item', array($this, 'order_shipping_item'), 300, 4);
			}

			add_action('wp_ajax_save_shipping_method_details', array($this, 'save_shipping_method_details'));
			add_action('wp_ajax_nopriv_save_shipping_method_details', array($this, 'save_shipping_method_details'));

			add_action('wp_ajax_additional_address_management', array($this, 'additional_address_management'));
			add_action('wp_ajax_nopriv_additional_address_management', array($this, 'additional_address_management'));

			add_action('wp_ajax_remove_multi_shipping_row', array($this, 'remove_multi_shipping_row'));
			add_action('wp_ajax_nopriv_remove_multi_shipping_row', array($this, 'remove_multi_shipping_row'));


			add_action('wp_ajax_update_multi_shipping_qty_field', array($this, 'update_multi_shipping_qty_field'));
			add_action('wp_ajax_nopriv_update_multi_shipping_qty_field', array($this, 'update_multi_shipping_qty_field'));

			// Logged in User
			add_action('wp_ajax_add_new_shipping_address', array($this, 'add_new_shipping_address'));
	    	add_action('wp_ajax_nopriv_add_new_shipping_address', array($this, 'add_new_shipping_address'));

	    	add_action('wp_ajax_thwma_save_address', array($this, 'thwma_save_address'),999);
	    	add_action('wp_ajax_nopriv_thwma_save_address', array($this, 'thwma_save_address'),999);

	    	// Multi-shipping (Guest user).
	    	add_action('wp_ajax_guest_users_add_new_shipping_address', array($this, 'guest_users_add_new_shipping_address'));
	    	add_action('wp_ajax_nopriv_guest_users_add_new_shipping_address', array($this, 'guest_users_add_new_shipping_address'));

	    	add_action('wp_ajax_thwma_save_guest_address', array($this, 'thwma_save_guest_address'));
	    	add_action('wp_ajax_nopriv_thwma_save_guest_address', array($this, 'thwma_save_guest_address'));

	    	// Delete guest user.
	    	add_action('wp_ajax_delete_address_with_id_cart_guest', array($this, 'delete_guest_address_from_cart'));
	    	add_action('wp_ajax_nopriv_delete_address_with_id_cart_guest', array($this, 'delete_guest_address_from_cart'));


		}

		/**
         * Function for set address above shipping form (Checkout page - address display position)
         *
         * @return void.
         */
		public function address_above_shipping_form() {
			$settings=THWMA_Utils::get_setting_value('settings_shipping');
			if (is_user_logged_in()) {
				if (! wc_ship_to_billing_address_only() && wc_shipping_enabled()) {
					if($settings && !empty($settings)) {
						if($settings['enable_shipping'] == 'yes') {

							// Checkout page Multi-shipping.
							$settings_multi_ship = THWMA_Utils::get_setting_value('settings_multiple_shipping');
							$multi_shipping_enabled = isset($settings_multi_ship['enable_cart_shipping']) ? $settings_multi_ship['enable_cart_shipping']:'';
							//$this->add_checkbox_for_choose_different_address();
							if($multi_shipping_enabled == 'yes') {
								$this->add_checkbox_for_set_multi_shipping();
							}

							// Set display position of normal address book.
							if($settings['shipping_display_position'] == 'above') {
								if($settings['shipping_display'] =='popup_display') {
									$this->add_tile_to_checkout_shipping_fields();
								}
								elseif($settings['shipping_display']=='dropdown_display') {
									$this->add_dd_to_checkout_shipping();
								}
								else {
									//$this->add_accordion_to_checkout_shipping();
								}

							}
						}
					}
				}
			} else{
				// Checkout page Multi-shipping.
				if($settings && !empty($settings)) {
					if(array_key_exists('enable_shipping', $settings)) {
						if($settings['enable_shipping'] == 'yes') {

							// Check Multi-shipping is enabled.
							$settings_multi_ship = THWMA_Utils::get_setting_value('settings_multiple_shipping');
							$multi_shipping_enabled = isset($settings_multi_ship['enable_cart_shipping']) ? $settings_multi_ship['enable_cart_shipping']:'';
							if($multi_shipping_enabled == 'yes') {
								$settings_guest_urs = THWMA_Utils::get_setting_value('settings_guest_users');
								$enabled_guest_user = isset($settings_guest_urs['enable_guest_shipping']) ? $settings_guest_urs['enable_guest_shipping']:'';
								if($enabled_guest_user == 'yes') {
									$this->add_checkbox_for_set_multi_shipping();
								}
							}
						}
					}
				}
			}
		}

		/**
         * Function for set address below shipping form (Checkout page - address display position)
         *
         * @return void.
         */
		public function address_below_shipping_form() {
			$settings = THWMA_Utils::get_setting_value('settings_shipping');
			if (is_user_logged_in()) {
				if (! wc_ship_to_billing_address_only() && wc_shipping_enabled()) {
					if($settings && !empty($settings)) {
						if($settings['enable_shipping'] == 'yes') {
							if($settings['shipping_display_position'] == 'below') {
								if($settings['shipping_display'] == 'popup_display') {
									$this->add_tile_to_checkout_shipping_fields();
								}
								elseif($settings['shipping_display'] == 'dropdown_display') {
									$this->add_dd_to_checkout_shipping();
								}
								else {
									//$this->add_accordion_to_checkout_shipping();
								}

							}
						}
					}
				}
			}
		}

		/**
         * Function for setting multi-shipping on checkout page( checkout page).
         */
		public function add_checkbox_for_set_multi_shipping(){
			$settings_manage_style = THWMA_Utils::get_setting_value('settings_manage_style');
			$multi_address_button = isset($settings_manage_style['multi_address_button']) ? $settings_manage_style['multi_address_button']:'';
			$multi_shipping_checkbox_label = isset($settings_manage_style['multi_shipping_checkbox_label']) ? $settings_manage_style['multi_shipping_checkbox_label']:'';

			$time_duration = isset($settings['set_time_duration']) ? $settings['set_time_duration']:'';  // doubt - Undefined variable.
			$user_id = get_current_user_id();
	        $enable_multi_ship_data = '';

	        // Check user is logged in.
	        if (is_user_logged_in()) {
	        	$enable_multi_ship_data = get_user_meta($user_id, THWMA_Utils::USER_META_ENABLE_MULTI_SHIP, true);
	        	$user_id = get_current_user_id();
				$default_address = THWMA_Utils::get_default_address($user_id, 'shipping');
				
				echo "<input type='hidden' name='ship_default_adr' value='".json_encode($default_address)."'>";

	        } else {
	        	$enable_multi_ship_data = isset($_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP]) ? $_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP] : '';

	        	$custom_addresses = array();
	        	$settings_guest_urs = THWMA_Utils::get_setting_value('settings_guest_users');
				$enabled_guest_user = isset($settings_guest_urs['enable_guest_shipping']) ? $settings_guest_urs['enable_guest_shipping']:'';
				$custom_adr = array();
	        	if($enabled_guest_user == 'yes') {
					$custom_addresses = THWMA_Utils::get_custom_addresses_of_guest_user('shipping');
					if(!empty($custom_addresses) && is_array($custom_addresses)) {
						foreach ($custom_addresses as $key => $value) {
							$custom_adr[] = $value;
						}
					}
				}
				if(!empty($custom_adr)) {
					$encoded_default_address = json_encode($custom_adr[0]);
					echo "<input type='hidden' name='ship_default_adr' value='".$encoded_default_address."'>";
				}
	        }


	        // Check multi-shipping checkbox is enabled on front end.
	        if(!empty($enable_multi_ship_data)) {
	        	$value = $enable_multi_ship_data;
		        if($enable_multi_ship_data == 'yes') {
		        	$checked = 'checked';
		        	$display_ppty = 'block';
		        } else {
		        	$checked = '';
		        	$display_ppty = 'none';
	        	}
	        } else {
	        	$value = 'no';
				$checked = '';
				$display_ppty = 'none';
	        }

	        $total_address_count = THWMA_Utils::get_total_address_count();

			echo '<input type="checkbox" name="ship_to_multi_address" value="'.$value.'" '.esc_attr__($checked).' data-address_count="'.$total_address_count.'"> '. esc_html__($multi_shipping_checkbox_label, "woocommerce-multiple-addresses-pro").'</br>';
			echo '<a onclick="thwma_cart_shipping_popup(event)" class=" thwma_cart_shipping_button" style="display:'.$display_ppty.'">'.esc_html__($multi_address_button, "woocommerce-multiple-addresses-pro").'</a>';
			$all_address='';

        	// Check user is logged in.
        	$html_address =	'';
			if (is_user_logged_in()) {
				$html_address = self::get_tile_field_to_cart($user_id, 'shipping');
			} else {
				$html_address = $this->get_not_loggedin_tile_field('shipping');
			}
			$theme_class_name = $this->check_current_theme();
			$theme_class = $theme_class_name.'_tile_field';

			// List of shipping addresses-popup.
			$all_address.= '<div id="thwma-cart-shipping-tile-field" class="thwma-cart-modal thwma-cart-shipping-tile-field '.esc_attr__($theme_class).'">'. $html_address.'</div>';

			// Add address form - popup.
			$all_address.= '<div id="thwma-cart-shipping-form-section" class="thwma-cart-modal2 thwma-cart-shipping-form-section '.esc_attr__($theme_class).'"><div class="thwma_hidden_error_mssgs"></div></div>'; ?>
			<div class="u-columns woocommerce-Addresses col2-set addresses cart-shipping-addresses ">
				<?php echo $all_address; ?>
			</div>
			<!-- <div id="multi-shipping-wrapper"> -->
				<div class="multi-shipping-wrapper">

				<?php
				echo self::multiple_address_management_form(); ?>
				</div>
			<!-- </div> -->
		<?php }

		/**
         * Function for set checkout popup shipping tiles (Checkout page - address display style(popup))
         *
         * @return void.
         */
		public function add_tile_to_checkout_shipping_fields() {
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

			if (is_user_logged_in()) {
				if (! wc_ship_to_billing_address_only() && wc_shipping_enabled()) {
					$customer_id = get_current_user_id();
					$settings = THWMA_Utils::get_setting_value('settings_shipping');
					$theme_class_name = $this->check_current_theme();
					$theme_class = $theme_class_name.'_tile_field';
					$disable_adr_mngmnt = self::disable_address_mnagement();
					$shipping_display_text = htmlspecialchars($settings['shipping_display_text']); ?>
					<div id="shipping_tiles" class="<?php echo $theme_class; ?>">
						<input type="hidden" id="button_color" name="button_color" value="<?php echo $button_color; ?>">
						<input type="hidden" id="text_color" name="text_color" value="<?php  echo $text_color; ?>">
						<input type="hidden" id="button_padding" name="button_padding" value="<?php  echo $button_padding; ?>">
						<?php if($settings['shipping_display_title']=='button') { ?>
							<div class = "add-address thwma-add-adr btn-checkout">
								<a id="thwma-popup-show-shipping" class="btn-add-adrs-checkout <?php echo $field_style; ?> primary is-outline"  onclick="thwma_show_shipping_popup(event)">
							<!-- <button type="button" id="thwma-popup-show-shipping" class="btn-add-adrs-checkout"  onclick="thwma_show_shipping_popup(event)"> -->
							<?php esc_html_e($shipping_display_text, 'woocommerce-multiple-addresses-pro'); ?>
								</a>
							<!-- </button>  -->
							</div>
						<?php }
						else { ?>
							<a href='#' id="thwma-popup-show-shipping_link" class='th-popup-shipping th-pop-link' onclick="thwma_show_shipping_popup(event)">
							<?php esc_html_e($shipping_display_text, 'woocommerce-multiple-addresses-pro'); ?>
							</a>
						<?php }
						$all_address='';
						$html_address = $this->get_tile_field($customer_id, 'shipping');
						$theme_class_name = $this->check_current_theme();
						$theme_class = $theme_class_name.'_tile_field';
						$all_address.= '<div id="thwma-shipping-tile-field" class="'.esc_attr($theme_class).'">'. $html_address.'</div>' ?>
						<div class="u-columns woocommerce-Addresses col2-set addresses billing-addresses ">
							<?php echo $all_address; ?>
						</div>
					</div>
				<?php }
			}
		}

		/**
         * Function for add shipping address to dropdown(Checkout page - address display style(dropdown))
         *
         * @return void
         */
		public function add_dd_to_checkout_shipping() {
			$customer_id = get_current_user_id();
			$custom_addresses = THWMA_Utils::get_custom_addresses($customer_id, 'shipping');
			$default_ship_address = THWMA_Utils::get_custom_addresses($customer_id, 'default_shipping');
			$same_address = THWMA_Utils::is_same_address_exists($customer_id, 'shipping');
			$default_address = $default_ship_address ? $default_ship_address : $same_address;
			$default_address = $same_address ? $same_address : $default_ship_address;

			// Address limit.
			$address_limit = THWMA_Utils::get_setting_value('settings_shipping', 'shipping_address_limit');
            if (!is_numeric($address_limit)) {
                $address_limit = 0;
            }

			$options = array();
			if(is_array($custom_addresses)) {
	        	$custom_address = $custom_addresses;
	        } else {
				$custom_address = array();
				$def_address = THWMA_Utils::get_default_address($customer_id, 'shipping');
				if(array_filter($def_address) && (count(array_filter($def_address)) > 2)) {
					$custom_address ['selected_address'] = $def_address;
				}
			}
			if($custom_address) {
				$address_count = count($custom_address);
				if($address_limit && ($address_count > 0)) {
					$shipping_heading = ( !empty($default_address) &&  apply_filters('thwma_check_valid_address_key', true, $default_address) && isset($custom_address[$default_address]['shipping_heading']) && $custom_address[$default_address]['shipping_heading'] !='') ? $custom_address[$default_address]['shipping_heading'] : esc_html__('', '');
					if($default_address) {
						if( !empty($default_address) &&  apply_filters('thwma_check_valid_address_key', true, $default_address) && isset($options[$default_address])) {
							$options[$default_address] = $shipping_heading .' - '. $custom_address[$default_address]['shipping_address_1'];
						}
					} else {
						$default_address = 'selected_address';
						$options[$default_address] = esc_html__('Shipping Address', 'woocommerce-multiple-addresses-pro');
					}

					if(is_array($custom_address)) {
						// Default address.
						foreach ($custom_address as $key => $address_values) {
							$adrsvalues_to_dd = array();
							if($key == $default_address) {
								$heading = (isset($address_values['shipping_heading']) && $address_values['shipping_heading'] != '') ? $address_values['shipping_heading'] : esc_html__('', '');
								if(apply_filters('thwma_remove_dropdown_address_format', true)) {
									if(!empty($address_values) && is_array($address_values)) {
										foreach ($address_values as $adrs_key => $adrs_value) {
											if($adrs_key == 'shipping_address_1' || $adrs_key =='shipping_address_2' || $adrs_key =='shipping_city' || $adrs_key =='shipping_state' || $adrs_key =='shipping_postcode') {
												if($adrs_value) {
													$adrsvalues_to_dd[] = $adrs_value;
												}
											}
										}
									}
								} else {
									$type = 'shipping';
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
						$default_ship_address = THWMA_Utils::get_custom_addresses($customer_id, 'default_shipping');
						$same_address = THWMA_Utils::is_same_address_exists($customer_id, 'shipping');
						$get_default_address = $same_address ? $same_address : $default_ship_address ;
						$no_of_tile_display = '';
						if(!empty($get_default_address)) {
							$no_of_tile_display = 1;
						} else {
							$no_of_tile_display = 0;
						}
						if($address_limit > $no_of_tile_display) {
							foreach ($custom_address as $key => $address_values) {
								if($key != $default_address) {
									$heading = (isset($address_values['shipping_heading']) && $address_values['shipping_heading'] != '') ? $address_values['shipping_heading'] : esc_html__('', 'woocommerce-multiple-addresses-pro');
									$adrsvalues_to_dd = array();
									if(apply_filters('thwma_remove_dropdown_address_format', true)) {
										if(!empty($address_values) && is_array($address_values)) {
											foreach ($address_values as $adrs_key => $adrs_value) {
												if($adrs_key == 'shipping_address_1' || $adrs_key =='shipping_address_2' || $adrs_key =='shipping_city' || $adrs_key =='shipping_state' || $adrs_key =='shipping_postcode') {
													if($adrs_value) {
														$adrsvalues_to_dd[] = $adrs_value;
													}
												}
											}
										}
									} else {
										$type = 'shipping';
										$separator = '</br>';
										$new_address = $custom_address[$key];
										$new_address_format = THWMA_Utils::get_formated_address($type,$new_address);
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
		       //                          break;
		       //                      }
									// Shipping dropdown.
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
				$options[$default_address] = esc_html__('Shipping Address', 'woocommerce-multiple-addresses-pro');
			}
			$alt_field = array(
				'required' => false,
				'class'    => array('form-row form-row-wide enhanced_select', 'select2-selection'),
				'clear'    => true,
				'type'     => 'select',
				'label'    => THWMA_Utils::get_setting_value('settings_shipping', 'shipping_display_text'),
				//'placeholder' =>esc_html__('Choose an Address..', ''),
				'options'  => $options
			);
			//woocommerce_form_field(self::DEFAULT_SHIPPING_ADDRESS_KEY, $alt_field,$options[$default_address]);
			$this->thwma_woocommerce_form_field(self::DEFAULT_SHIPPING_ADDRESS_KEY, $alt_field,$default_address);
		}

		/**
         * The tile field creation function of guest users.(Multi-shipping on cart page - (Guest User))
         *
         * @param string $type The address type
         *
         * @return array.
         */
		function get_not_loggedin_tile_field($type) {
			$oldcols = 1;
			$cols    = 1;
			$all_addresses = array();
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
			$custom_address = THWMA_Utils::get_custom_addresses_of_guest_user($type);
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
			$add_class='';
			$address_type = "'$type'";
			$add_list_class  = ($type == 'billing') ? " thwma-thslider-list-ms bill " : " thwma-thslider-list-ms ship";
	    	$add_address_btn = '<div class="add-address thwma-add-adr '.esc_attr($disable_acnt_sec).'">
	            <button class="btn-add-address primary btn-different-address '.esc_attr($disable_mngt_class).'" onclick="thwma_guest_users_add_new_shipping_address(event, this, '.esc_attr($address_type).')">
	                <i class="fa fa-plus"></i> '.esc_html__('Add new address', 'woocommerce-multiple-addresses-pro').'
	            </button>
	        </div>';
	        if(is_array($custom_address)) {
	        	$all_addresses = $custom_address;
	        }
	        if($address_limit) {
	        	$shipping_addresses = __("Shipping Addresses", "woocommerce-multiple-addresses-pro");
			$return_html .= '<div class="thwma-cart-modal-content">';
				$return_html .= '<div class="thwma-cart-modal-title-bar" >';
					$return_html .= '<span class="thwma-cart-modal-title" >'.$shipping_addresses.'</span>';
					//$return_html .= '<span class="thwma-cart-modal-close" onclick="thwma_close_cart_adr_list_modal(this)">&times;</span>';
					$return_html .= '<span class="thwma-cart-modal-close" onclick="thwma_close_cart_adr_list_modal(this)"><span class="dashicons dashicons-no"></span></span>';
				$return_html .= '</div>';
				$return_html .= '<div class="thwma-thslider thwma-thslider-guest-user">';
					if($all_addresses && is_array($all_addresses)) {
			           	$return_html .= '<div class="thwma-thslider-box">';
				           	$return_html .= '<div class="thwma-thslider-viewport '.esc_attr($type).'">';
					           	$return_html .= '<ul class=" '.esc_attr($add_list_class).'">';
									$i = 0;
									$total_address_count = count($all_addresses);

									foreach ($all_addresses as $address_key => $value) {
										$new_address = array_key_exists($address_key, $all_addresses) ? $all_addresses[$address_key] : array();
										$new_address_format = THWMA_Utils::get_formated_address($type,$new_address);
										$options_arr = THWMA_Utils::thwma_get_formatted_address($new_address_format);
										$address_key_param = "'".$address_key."'";
										$heading = !empty($new_address[$type.'_heading']) ? $new_address[$type.'_heading'] : esc_html__('', 'woocommerce-multiple-addresses-pro') ;
										$action_row_html = '';
										$address_type_css = '';
										$action_row_html .= '<div class="thwma-adr-footer address-footer">
											<div class="btn-delete '.esc_attr($disable_mngt_class).'" data-index="0" data-address-id="" onclick="thwma_guest_users_delete_selected_address_cart_page(this,'.esc_attr($address_type).','.esc_attr($address_key_param).')" title="'.esc_html__('Delete', 'woocommerce-multiple-addresses-pro').'">
												<span>'.esc_html__('Delete', 'woocommerce-multiple-addresses-pro').'</span>
											</div>
										</div>';
										if(isset($heading) && $heading != '') {
											$heading_css = '<div class="address-type-wrapper row">
												<div title="'.$heading.'" class="address-type '.esc_attr($address_type_css).'">'.esc_html($heading).'</div>
												</div>
												<div class="tile-adrr-text thwma-adr-text address-text address-wrapper">'.$options_arr.'</div>';
										} else {
											$heading_css = '<div class="tile-adrr-text thwma-adr-text address-text address-wrapper wrapper-only">'.$options_arr.'</div>';
										}
										$add_class  = "thwma-thslider-item-ms $type " ;
										$add_class .= $i == 0 ? ' first' : '';
										$return_html .= '<li class="'.$add_class.'" value="'. esc_attr($address_key).'" >
											<div class="thwma-adr-box address-box" data-index="'.esc_attr($i).'" data-address-id="">
												<div class="thwma-main-content">
													<div class="complete-aaddress">
														'.$heading_css.'
													</div>
													<div class="btn-continue address-wrapper">
														<a class="th-btn button primary is-outline '.esc_attr($address_key).'" onclick="thwma_populate_selected_address(event, this, '.esc_attr($address_type).', '.esc_attr($address_key_param).')">
															<span>'.esc_html__('Choose This Address', 'woocommerce-multiple-addresses-pro').'</span>
														</a>
													</div>
												</div>'.$action_row_html.'</div></li>';

										// Guest shipping tile.
										if($i >= $address_limit-1) {
                                            break;
                                        }
										$i++;
									}
								$return_html .= '</ul>';
							$return_html .= '</div>';
						$return_html .= '</div>';
						$return_html .= '<div class="control-buttons control-buttons-'.esc_attr($type).'">';
						if($address_count && $address_count > 3) {
							if($address_limit>3) {
				            	$return_html .= '<div class="prev thwma-thslider-prev multi-'.esc_attr($type).'"><i class="fa fa-angle-left fa-3x"></i></div>';
				            	$return_html .= '<div class="next thwma-thslider-next multi-'.esc_attr($type).'"><i class="fa fa-angle-right fa-3x"></i></div>';
				            }
			            }
						$return_html .= '</div>';
			           		if(((int)($address_limit)) > $address_count) {
			           		$return_html .= $add_address_btn;
			           	}
			        } else {
			        	$return_html .= '<div class="th-no-address-msg"  >	<span>'.esc_html__('No saved addresses found', 'woocommerce-multiple-addresses-pro').'</span>  </div>';
			        	$return_html .= $add_address_btn;
			        }
		        $return_html .= '</div>';
	        $return_html .= '</div>';
	        }
			return $return_html;
		}

		/**
         * Function for update custom shipping address from checkout page(checkout page)
         *
         * @param integer $order_id The order id
         * @param array $posted_data The posted data info
         * @param array $order The order datas
         */
		public function update_custom_shipping_address_from_checkout($order_id, $posted_data, $order) {
			$settings = THWMA_Utils::get_setting_value('settings_multiple_shipping');
			$cart_shipping_enabled = isset($settings['enable_cart_shipping']) ? $settings['enable_cart_shipping']:'';
			$user_id = get_current_user_id();
	        $enable_multi_ship = '';

	        // Enable multi-shipping on checkout page.
	        if (is_user_logged_in()) {
	        	$enable_multi_ship = get_user_meta($user_id, THWMA_Utils::USER_META_ENABLE_MULTI_SHIP, true);
	        }
	        //$flag = false;
	  //       if($cart_shipping_enabled == 'yes') {
	  //       	if($enable_multi_ship != 'yes') {
			// 		$flag = true;
			// 	}
			// } else {
				$flag = true;
			// }

			if($flag == true) {
	        	if (is_user_logged_in()) {
					$user_id = get_current_user_id();
					$custom_key = THWMA_Utils::get_custom_addresses($user_id, 'default_shipping');
					$same_address_key = THWMA_Utils::is_same_address_exists($user_id, 'shipping');
					$default_key = ($custom_key) ? $custom_key : $same_address_key ;
					if (! wc_ship_to_billing_address_only() && wc_shipping_enabled()) {
						$address_key = isset($posted_data['thwma_hidden_field_shipping']) ? $posted_data['thwma_hidden_field_shipping'] : '';
						$ship_select = isset($posted_data['thwma_checkbox_shipping']) ? $posted_data['thwma_checkbox_shipping'] : '';
						if($ship_select == 'ship_select') {
							$this->update_address_from_checkout('shipping', $address_key, $posted_data, $default_key);
						} else {
							if(!$custom_key) {
								$this->update_address_from_checkout('shipping', $ship_select, $posted_data, $default_key);
								//$custom_address = self::get_custom_addresses($user_id, 'shipping', $ship_select);
								//$this->update_address_to_user($user_id, $custom_address, 'shipping', $ship_select);
							}
						}
					}
					if($custom_key) {
						$modify = apply_filters('thwma_modify_shipping_update_address', true);
						if($modify) {
							$this->change_default_address($user_id, 'shipping', $default_key);
						} else {
							if ($address_key == 'add_address') {
								$new_key_id = (THWMA_Utils::get_new_custom_id($user_id, 'shipping')) - 1;
								$new_key = 'address_'.$new_key_id;
								$this->change_default_address($user_id, 'shipping', $new_key);
							} elseif(!empty($address_key)) {
								$this->change_default_address($user_id, 'shipping', $address_key);
							}
						}
					}
				}
			}
		}

		/**
		 * Function enable or disable multi-shipping facility on checkout page(Ajax-response).
		 * function for saving the enabled ship to multiple address data.(ajax function- cart page)
		 */
		function enable_ship_to_multi_address() {
			check_ajax_referer( 'enable-ship-to-multi-address', 'security' );

				$value = isset($_POST['value']) ? sanitize_text_field($_POST['value']) : '';
				$settings_guest_urs = THWMA_Utils::get_setting_value('settings_guest_users');
				$enabled_guest_user = isset($settings_guest_urs['enable_guest_shipping']) ? $settings_guest_urs['enable_guest_shipping'] : '';
				if (is_user_logged_in()) {
					$user_id = get_current_user_id();
					update_user_meta($user_id, THWMA_Utils::USER_META_ENABLE_MULTI_SHIP, $value);
				} else {
					if($enabled_guest_user == 'yes') {
						$expiration = self::expiration_time();
						setcookie(THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP, $value, time() + $expiration, "/");
					}
				}
				if($enabled_guest_user == 'yes') {
					$custom_addresses = THWMA_Utils::get_custom_addresses_of_guest_user('shipping');
				}
				if(!empty($custom_addresses)){
					echo true;
				} else {
					echo  false;
				}
				if($value == 'no') {
					//$this->thwma_get_shipping_change_to_packages_default($packages);
				}
			exit();
		}

		/*-- Multi Shipping on Checkout page --*/

		/**
		 * Function for delete addresses from shipping address list of multi-shipping section(ajax response- checkout page).
         * Function for delete address from cart page(ajax response-cart page)
         *
         * @return void
         */
		public function delete_address_from_cart() {
			global $woocommerce;
			check_ajax_referer( 'delete-address-with-id-cart', 'security' );
			$address_key = isset($_POST['selected_address_id']) ? sanitize_key($_POST['selected_address_id']) : '';
			$type = isset($_POST['selected_type']) ? sanitize_key($_POST['selected_type']) : '';
			$customer_id = get_current_user_id();
			THWMA_Utils::delete($customer_id, $type, $address_key);
			$output_shipping = self::get_tile_field_to_cart($customer_id, 'shipping');
			$total_address_count = THWMA_Utils::get_total_address_count();

			$response = array(
				'result_shipping' => $output_shipping,
				'address_key' => $address_key,
				'address_count' => $total_address_count,
			);
			$cart = $woocommerce->cart->cart_contents;
			if(!empty($cart) && is_array($cart)) {
				foreach ($cart as $key => $item) {
					if(isset($cart[$key]['multi_ship_address'])) {
						$ship_adr_data = $cart[$key]['multi_ship_address'];
						if(!empty($cart[$key]['multi_ship_address']) && is_array($cart[$key]['multi_ship_address'])) {
							foreach ($cart[$key]['multi_ship_address'] as $value) {
								if(is_array($value)) {
									if (array_key_exists("ship_address",$value)) {
										if($value['ship_address'] == $address_key) {
									        $woocommerce->cart->cart_contents[$key]['multi_ship_address']['ship_address'] = '';
										}
									}
								}
							}
						}
					}
					if(isset($cart[$key]['product_shipping_address'])) {
						if($cart[$key]['product_shipping_address'] == $address_key) {
					        $woocommerce->cart->cart_contents[$key]['product_shipping_address'] = '';
						}
					}
				}
			}

			$woocommerce->cart->set_session();
			wp_send_json($response);
		}

		/**
		 * Function for set default address from shipping address list of multi-shipping section(ajax response- checkout page).
         * Function for set default address from cart page(ajax response-cart page)
         *
         * @return void
         */
		public function default_address_from_cart() {
			check_ajax_referer( 'set-default-address-cart', 'security' );
			$address_key = isset($_POST['selected_address_id']) ? sanitize_key($_POST['selected_address_id']) : '';
			$type = isset($_POST['selected_type']) ? sanitize_key($_POST['selected_type']) : '';

			$user_id = get_current_user_id();
			$this->change_default_address($user_id, $type, $address_key);
			$output_shipping = self::get_tile_field_to_cart($user_id, 'shipping');
			$output_table = self::multiple_address_management_form();

			$user_id = get_current_user_id();
			$default_address = THWMA_Utils::get_default_address($user_id, 'shipping');
			$encoded_default_address = json_encode($default_address);

			$response = array(
				'result_shipping' => $output_shipping,
				'output_table'    => $output_table,
				'default_address' => $encoded_default_address,
			);
			wp_send_json($response);
		}

		/**
		 * Save selected multi shipping address on cart item data(checkout page - ajax response).
		 *
		 * @return void
		 */
		public function save_multi_selected_shipping() {
			global $woocommerce;
			check_ajax_referer( 'save-multi-selected-shipping', 'security' );
			$user_id = get_current_user_id();
	        $enable_multi_ship = '';
	        if (is_user_logged_in()) {
	        	$enable_multi_ship = get_user_meta($user_id, THWMA_Utils::USER_META_ENABLE_MULTI_SHIP, true);
	        } else {
	        	$enable_multi_ship = isset($_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP]) ? $_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP] : '';
	        }
			$shipping_name = isset($_POST['value']) ? $_POST['value'] : '';
			$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : '';
			$cart_key = isset($_POST['cart_key']) ? $_POST['cart_key'] : '';
			$multi_ship_id = isset($_POST['multi_ship_id']) ? $_POST['multi_ship_id'] : '';
			//$multi_ship_id = isset($_POST['multi_ship_id']) ? $_POST['multi_ship_id'] : '';

			// $this->save_multi_selected_shipping_addresses($cart_key);

			$cart = $woocommerce->cart->cart_contents;
			$input_value = '';

			// Get shipping custom fields.
			// $shipping_adrs = THWMA_Utils::get_custom_addresses($user_id, 'shipping', $shipping_name);
			// $custom_fields = $this->get_shipping_custom_fields_from_addresses($shipping_adrs);
			// $custom_address = THWMA_Utils::get_custom_addresses_of_guest_user('shipping');
			// if(count($custom_address) == 1) {
			// 	foreach ($custom_address as $key => $value) {
			// 		$first_custom_address = $value;
			// 	}
			// }
			// $new_address = $first_custom_address;

			// $shipp_addr_format = THWMA_Utils::get_formated_address('shipping', $new_address);
			// if(apply_filters('thwma_inline_address_display', true)) {
			// 	$separator = ', ';
				// $pdt_shipp_addr_formated = THWMA_Utils::thwma_get_formatted_address($shipp_addr_format, $shipp_addr_format);
			// } else {
			// 	$pdt_shipp_addr_formated = THWMA_Utils::thwma_get_formatted_address($shipp_addr_format);
			// }
			// $response = array(
			// 	'first_address' => $pdt_shipp_addr_formated,
			// );
			// wp_send_json($response);

			if(!empty($cart) && is_array($cart)){
				foreach ($cart as $key => $item) {
				    if($key == $cart_key) {
				    	$woocommerce->cart->cart_contents[$key]['product_shipping_address'] = $shipping_name;
				        $woocommerce->cart->cart_contents[$key]['multi_ship_address']['ship_address'] = $shipping_name;

				        //$woocommerce->cart->cart_contents[$key]['thwma_custom_fields'] = $custom_fields;
				        sleep(4);
					}
				}
				$woocommerce->cart->set_session();
			}
			exit();
		}

		/**
         * Function for sget shipping package(Set $package-shipping method).
         *
         * @param array $packages The package details
         *
         * @return array
         */
		public function thwma_get_shipping_packages($packages) {
			$individual_details = array();
			$shipping_country = '';
			$guest_shipping_data = WC()->session->get('guest_shipping_data');
			$shipping_package = WC()->session->get('guest_shipping_package');
			
			if(isset($guest_shipping_data) && is_user_logged_in()){
				if($guest_shipping_data['guest_calculation'] && isset($shipping_package)){
					$packages = $shipping_package['guest_package'];
				}
			}
			
			if(!empty(WC()->cart->get_cart()) && is_array(WC()->cart->get_cart())) {
				foreach (WC()->cart->get_cart() as $key => $cart_item) {
					$_product =  wc_get_product($cart_item['data']->get_id());

					// Check the product is vertual or downloadable.
					$downloadable = apply_filters( 'wmap_product_is_downloadable', $_product->is_downloadable('yes'));
					if ((!$_product->is_virtual()) && (!$downloadable)) {
					//if ((!$_product->is_virtual()) && (!$_product->is_downloadable('yes'))) {

					$product_price = $_product->get_price();
					$product_name = $_product->get_title();
					$user_id= get_current_user_id();
					$values = array();

					if(isset($cart_item['product_shipping_address']) && !empty($cart_item['product_shipping_address'])) {
						$ship_addresses = isset($cart_item['product_shipping_address']) ? $cart_item['product_shipping_address'] : '';
						$settings_guest_usr = THWMA_Utils::get_setting_value('settings_guest_users');
						$enabled_guest_user = isset($settings_guest_usr['enable_guest_shipping']) ? $settings_guest_usr['enable_guest_shipping'] : '';
						$adr_data = '';
						if(is_user_logged_in()) {
							$adr_data = THWMA_Public::get_user_addresses($ship_addresses);
						} else {
							if($enabled_guest_user == 'yes') {
								$adr_data = THWMA_Public::get_guest_user_addresses($ship_addresses);
							}
						}

						if($adr_data && is_array($adr_data)) {
							$shipping_country = isset($adr_data['shipping_country']) ? esc_attr($adr_data['shipping_country']) : '';
							$shipping_state = isset($adr_data['shipping_state']) ? esc_attr($adr_data['shipping_state']) : '';
							$shipping_postcode = isset($adr_data['shipping_postcode']) ? esc_attr($adr_data['shipping_postcode']):'';
							$shipping_city = isset($adr_data['shipping_city'])?esc_attr($adr_data['shipping_city']) : '';
							$shipping_address_1 = isset($adr_data['shipping_address_1']) ? esc_attr($adr_data['shipping_address_1']) : '';
							$shipping_address_2 = isset($adr_data['shipping_address_2']) ? esc_attr($adr_data['shipping_address_2']) : '';
						    $active_methods   = array();
						    $values = array (
						    	'country' => $shipping_country,
				                'amount'  => $product_price,
				                'shipping_state' => $shipping_state,
				                'shipping_postcode' => $shipping_postcode,
				                'shipping_city' => $shipping_city,
				                'shipping_address_1' => $shipping_address_1,
				                'shipping_address_2' => $shipping_address_2
				           );

						    foreach ($adr_data as $adr_key => $adr_value) {
						    	if(array_key_exists($adr_key, $_POST)){
						    		if(isset($_POST[$adr_key]) && empty($_POST[$adr_key])){
						    			$_POST[$adr_key] = $adr_value;
						    		}
						    	}
						    }

						}

					} else {
						$default_address = THWMA_Utils::get_default_address($user_id, 'shipping');
						if($default_address && is_array($default_address)) {
							$shipping_country = isset($default_address['shipping_country']) ? esc_attr($default_address['shipping_country']) : '';
							$shipping_state = isset($default_address['shipping_state'])?esc_attr($default_address['shipping_state']) : '';
							$shipping_postcode = isset($default_address['shipping_postcode']) ? esc_attr($default_address['shipping_postcode']) : '';
							$shipping_city = isset($default_address['shipping_city']) ? esc_attr($default_address['shipping_city']) : '';
							$shipping_address_1 = isset($default_address['shipping_address_1']) ? esc_attr($default_address['shipping_address_1']) : '';
							$shipping_address_2 = isset($default_address['shipping_address_2']) ? esc_attr($default_address['shipping_address_2']) : '';
						    $active_methods   = array();
						    $values = array (
						    	'country' => $shipping_country,
				                'amount'  => $product_price,
				                'shipping_state' => $shipping_state,
				                'shipping_postcode' => $shipping_postcode,
				                'shipping_city' => $shipping_city,
				                'shipping_address_1' => $shipping_address_1,
				                'shipping_address_2' => $shipping_address_2
				           );

						}
					}
					$individual_details[] = $this->create_item_package_array($values, $cart_item);

					} // Check the product is vertual or downloadable.

				}
			}
			if($shipping_country) {
				$packages = $individual_details;
			} else {
				$packages = $packages;
			}
			if ( is_checkout() && ! is_user_logged_in() ) {
				$guest_shipping_data = array('guest_calculation' => true,);
				WC()->session->set( 'guest_shipping_data', $guest_shipping_data );
				$shipping_packages = array('guest_package' => $packages);
				WC()->session->set('guest_shipping_package', $shipping_packages);
			}
			return $packages;
		}
		/**
         * Function for create item package array(Set $package-shipping method).
         *
         * @param array $value The address values
         * @param array $cart_item The cart item details
         *
         * @return array
         */
		public function create_item_package_array($value, $cart_item) {
			$package = array();
			if($value) {
				$package = array(
			        'contents'        	=> $this->get_items_needing_shipping(),
			        'contents_cost'   	=> array_key_exists( 'line_total', $cart_item ) ? $cart_item['line_total'] : '',
			        'applied_coupons' 	=> WC()->cart->applied_coupons,
			        'destination'     	=> array(
			            'country'   	=> $value['country'],
			            'state'     	=> $value['shipping_state'],
			            'postcode'  	=> $value['shipping_postcode'],
			            'city'  		=> $value['shipping_city'],
			            'address'  		=> $value['shipping_address_1'],
			            'address1'  	=> $value['shipping_address_1'],
			            'address_2'  	=> $value['shipping_address_2']
			       )
				);
			}
			return $package;
		}

		/**
		 * Filled the shipping fields by using default address.
		 *
		 * @return void
		 */
		function thwma_fill_shipping_fields_values($value, $input) {
			if(is_user_logged_in()) {
				$user_id = get_current_user_id();
				$default_address = THWMA_Utils::get_default_address($user_id, 'shipping');

				foreach ($default_address as $key => $data) {
					if($input == $key) {
						$value = $data;
					}
				}
			}
			return $value;
		}

		/**
         * Function for set defalut shipping package(Set $package-shipping method).
         *
         * @param array $packages The package details
         *
         * @return string
         */
		public function thwma_get_shipping_packages_default($packages) {
			$packages = $this->thwma_get_shipping_change_to_packages_default($packages);
			return $packages;
		}

		/**
         * Core function for set defalut shipping package(Set $package-shipping method)
         *
         * @param array $packages The package details
         *
         * @return array
         */
		public function thwma_get_shipping_change_to_packages_default($packages) {
			$packages = array(array(
				'contents'        => $this->get_items_needing_shipping(),
				'contents_cost'   => array_key_exists( 'line_total', array($this, 'filter_items_needing_shipping')) ?array_sum(wp_list_pluck($this->get_items_needing_shipping(), 'line_total')) : '',
				'applied_coupons' => WC()->cart->applied_coupons,
				'user'            => array(
					'ID' => get_current_user_id(),
				),
				'destination'     => array(
					'country'   => WC()->customer->get_shipping_country(),
					'state'     => WC()->customer->get_shipping_state(),
					'postcode'  => WC()->customer->get_shipping_postcode(),
					'city'      => WC()->customer->get_shipping_city(),
					'address'   => WC()->customer->get_shipping_address(),
					'address_1' => WC()->customer->get_shipping_address(), // Provide both address and address_1 for backwards compatibility.
					'address_2' => WC()->customer->get_shipping_address_2(),
				)
			));
			return $packages;
		}

		/**
         * Function for get items needing shipping(Set $package-shipping method).
         *
         * @return void
         */
		protected function get_items_needing_shipping() {
			return array_filter($this->get_cart(), array($this, 'filter_items_needing_shipping'));
		}

		protected function filter_items_needing_shipping($item) {
			$product = $item['data'];
			return $product && $product->needs_shipping();
		}

		/**
         * Function for get cart(Set $package-shipping method)
         *
         * @return void
         */
		public function get_cart() {
			if (! did_action('wp_loaded')) {
				wc_doing_it_wrong(__FUNCTION__, esc_html__('Get cart should not be called before the wp_loaded action.', 'woocommerce'), '2.3');
			}
			if (! did_action('woocommerce_load_cart_from_session')) {
				$this->session->get_cart_from_session();
			}
			return array_filter($this->get_cart_contents());
		}

		/**
         * Function for get cart contents(Set $package-shipping method)
         *
         * @return void
         */
		public function get_cart_contents() {
			$cart                = WC()->session->get('cart', null);
			$merge_saved_cart    = (bool) get_user_meta(get_current_user_id(), '_woocommerce_load_saved_cart_after_login', true);
			if (is_null($cart) || $merge_saved_cart) {
				$saved_cart          = $this->get_saved_cart();
				$cart                = is_null($cart) ? array() : $cart;
				$cart                = array_merge($saved_cart, $cart);
				$update_cart_session = true;
				delete_user_meta(get_current_user_id(), '_woocommerce_load_saved_cart_after_login');
			}
			$cart_contents = array();

			if(!empty($cart) && is_array($cart)){
				foreach ($cart as $key => $values) {
					$product = wc_get_product($values['variation_id'] ? $values['variation_id'] : $values['product_id']);
					$session_data = array_merge(
						$values,
						array(
							'data' => $product,
						)
					);
					$cart_contents[ $key ] = apply_filters('woocommerce_get_cart_item_from_session', $session_data, $values, $key);
				}
			}
			$this->cart_contents = (array) $cart_contents;
			return apply_filters('woocommerce_get_cart_contents', (array) $this->cart_contents);
		}

		/**
         * Function for set woocommerce package rate.
         *
         * @param array $cart_item_data The cart item datas
         * @param integer $product_id The product id
         *
         * @return array
         */
		function thwma_namespace_force_individual_cart_items($cart_item_data, $product_id) {
			$unique_cart_item_key = md5(microtime() . rand());
			$created_time = date("h:i:sa");
			$cart_item_data['unique_key'] = $unique_cart_item_key;
			$cart_item_data['time'] = $created_time;
			return $cart_item_data;
		}

		/**
		 * Get the persistent cart from the database.
		 *
		 * @since  3.5.0
		 * @return array
		 */
		
		private function get_saved_cart() {
			$saved_cart = array();

			if ( apply_filters( 'woocommerce_persistent_cart_enabled', true ) ) {
				$saved_cart_meta = get_user_meta( get_current_user_id(), '_woocommerce_persistent_cart_' . get_current_blog_id(), true );

				if ( isset( $saved_cart_meta['cart'] ) ) {
					$saved_cart = array_filter( (array) $saved_cart_meta['cart'] );
				}
			}

			return $saved_cart;
		}

		/**
         * Function for get shipping method details(Ajax response).
         */
		public function save_shipping_method_details() {
			global $woocommerce;
			check_ajax_referer( 'save-shipping-method-details', 'security' );
			// Update shipping package and Disable shipping calculator.
	    	$settings = THWMA_Utils::get_setting_value('settings_multiple_shipping');
			$cart_shipping_enabled = isset($settings['enable_cart_shipping']) ? $settings['enable_cart_shipping']:'';
			$user_id= get_current_user_id();
	        $enable_multi_ship = '';
	        if (is_user_logged_in()) {
	        	$enable_multi_ship = get_user_meta($user_id, THWMA_Utils::USER_META_ENABLE_MULTI_SHIP, true);
	        } else {
	        	$enable_multi_ship = isset($_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP])?$_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP]:'';
	        }
	        if($enable_multi_ship == 'yes') {
	        	if($cart_shipping_enabled == 'yes') {
	        		$ship_method_arr = isset($_POST['ship_method_arr']) ? $_POST['ship_method_arr'] : '';
						if(!empty($ship_method_arr) && is_array($ship_method_arr)) {
						//foreach ($ship_method_arr as $ship_method_arr_data) {
						$cart_key = '';
						$shipping_methods = array();
						foreach ($ship_method_arr as $key => $value) {
							$id = isset($value['method_id']) ? $value['method_id'] : '';
							$method_id = substr($id, 0, strpos($id, ":"));

							$cart_key = isset($value['cart_key']) ? $value['cart_key'] : '';
							$cart_unique_key = isset($value['cart_unique_key']) ? $value['cart_unique_key'] : '';
							$product_name = isset($value['item_name']) ? rtrim ($value['item_name']) : '';
							$product_id = isset($value['product_id']) ? $value['product_id'] : '';
							$product_qty = isset($value['item_qty']) ? $value['item_qty'] : '';
							$address_name = isset($value['shipping_name']) ? $value['shipping_name'] : '';
							$shipping_adrs = isset($value['shipping_adrs']) ? json_decode(base64_decode($value['shipping_adrs'])) : '';
							$shipping_adrs = json_decode(json_encode($shipping_adrs), true);
							$custom_fields = $this->get_custom_fields_from_addresses($shipping_adrs);
							$shipping_methods[$id] = array(
								'method_id' 		=> $method_id,
								'product_id'		=> $product_id,
								'cart_key'			=> $cart_key,
								'cart_unique_key'	=> $cart_unique_key,
								'item_name' 		=> $product_name,
								'item_quantity' 	=> $product_qty,
								'address_name'		=> $address_name,
								'shipping_address' 	=> $shipping_adrs,
								'custom_fields'		=> $custom_fields

							);
							$cart = $woocommerce->cart->cart_contents;
							$input_value = '';
							if(!empty($cart) && is_array($cart)){
								foreach ($cart as $key => $item) {
									if(!empty($cart_key)) {
									    if($key == $cart_key) {
									    	$woocommerce->cart->cart_contents[$key]['thwma_shipping_methods'] = $shipping_methods;

									        $shipping_methods = array();
										}
									}
								}
							}
							$woocommerce->cart->set_session();
						}
					}
				}
			}
			//update_option(THWMA_Utils::OPTION_KEY_SHIPPING_METHOD, $shipping_methods);
			exit();
		}

		/**
         * Function for get custom fields from the address fields
         *
         * @param obj $shipping_adrs The whole address fields including field values
         *
         * @return array
         */
		public function get_custom_fields_from_addresses($shipping_adrs) {
			$default_fields = array(
				'first_name'=> '',
			    'last_name' => '',
			    'company' 	=> '',
			    'country' 	=> '',
			    'address_1' => '',
			    'address_2' => '',
			    'city' 		=> '',
			    'state' 	=> '',
			    'postcode' 	=> ''
			);
			$custom_fields = array();
			if(is_array($shipping_adrs)) {
				$custom_fields = array_diff_key($shipping_adrs,$default_fields);
			}
			return $custom_fields;
		}

		/**
		 * Add additional address and quantity fields on checkout page(checkout page-ajax response).
		 */
		function additional_address_management(){
			global $woocommerce;
			check_ajax_referer( 'additional-address-management', 'security' );
			$cartitem = isset($_POST['cart_item']) ? $_POST['cart_item'] : '';
	        $product_id = isset($_POST['product_id']) ? sanitize_key($_POST['product_id']) : '';
			$variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : '';
			$cart_item_key = isset($_POST['cart_item_key']) ? $_POST['cart_item_key'] : '';
	        $quantity = empty($_POST['quantity']) ? 1 : wc_stock_amount($_POST['quantity']);
	        $parent_item_id = isset($_POST['parent_item_id']) ? sanitize_key($_POST['parent_item_id']) : '';
	        $row_stage = isset($_POST['sub_row_stage']) ? sanitize_key($_POST['sub_row_stage']) : '';
	        $updated_qty = isset($_POST['updated_qty']) ? sanitize_key($_POST['updated_qty']) : '';

	        // Reduce one qty from main item.
	        // $display_qty = $updated_qty - 1;

	        $woocommerce->cart->set_quantity($cart_item_key, $updated_qty);

			// Update shipping package and Disable shipping calculator.
	    	$settings = THWMA_Utils::get_setting_value('settings_multiple_shipping');
			$cart_shipping_enabled = isset($settings['enable_cart_shipping']) ? $settings['enable_cart_shipping']:'';
			$user_id= get_current_user_id();
	        $enable_multi_ship = '';
	        if (is_user_logged_in()) {
	        	$enable_multi_ship = get_user_meta($user_id, THWMA_Utils::USER_META_ENABLE_MULTI_SHIP, true);
	        } else {
	        	$enable_multi_ship = isset($_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP]) ? $_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP] : '';
	        }

	        // Check multi-shipping is enable.
	        if($enable_multi_ship == 'yes') {
				if($cart_shipping_enabled == 'yes') {
			       // $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_POST['product_id']));
			        $product = wc_get_product($product_id);
			        $new_row_stage = '';
			        if(is_numeric ($row_stage)) {
			        	$new_row_stage = $row_stage - 1;
			        }

			        $items = $woocommerce->cart->get_cart();
			        $cart = $woocommerce->cart->cart_contents;

			        //$crnt_updated_qty = isset($cart[$cart_item_key]['multi_ship_address']['updated_quantity']) ? $cart[$cart_item_key]['multi_ship_address']['updated_quantity'] : '';
			        if(array_key_exists($cart_item_key, $cart)) {
			       		$crnt_child_keys = isset($cart[$cart_item_key]['multi_ship_address']['child_keys']) ? $cart[$cart_item_key]['multi_ship_address']['child_keys'] : array();
			       	} else {
			       		$crnt_child_keys = array();
			       	}

			       	// check thwepo options is existing.
			       	$cart_item 	= isset($cart[$cart_item_key]) ? $cart[$cart_item_key] : array();
			       	$thwepo_options = '';
			       	if(THWMA_Utils::check_thwepof_plugin_is_active()){
			       		if ($cart_item) {
				       		$thwepo_options = isset($cart_item['thwepof_options']) ? $cart_item['thwepof_options'] : '';
				       	}
			       	}if(THWMA_Utils::check_thwepo_plugin_is_active()){
				       	if ($cart_item) {
				       		$thwepo_options = isset($cart_item['thwepo_options']) ? $cart_item['thwepo_options'] : '';
				       	}
			       	}

			       	// Create new item.
			        $nw_quantity = 1;
					add_filter('woocommerce_add_cart_item_data', array($this,'thwma_namespace_force_individual_cart_items'), 10, 3);
			        $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $nw_quantity);
			        $product_status = get_post_status($product_id);
			        $variation    = array();
			        $variation    = isset($cartitem['variation']) ? $cartitem['variation'] : '';
			        $new_item_key = WC()->cart->add_to_cart($product_id, $nw_quantity, $variation_id, $variation);
			        if ($passed_validation && $new_item_key && 'publish' === $product_status) {
						do_action('woocommerce_ajax_added_to_cart', $product_id);
			            if ('yes' === get_option('woocommerce_cart_redirect_after_add')) {
			            	wc_add_to_cart_message(array($product_id => $nw_quantity), true);
			            }
		            //WC_AJAX :: get_refreshed_fragments();
			        } else {
			            $data = array(
			                'error' => true,
			                'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id));
			            echo wp_send_json($data);
			        }

			        // New item data saved to cart content.
			        $cart = $woocommerce->cart->cart_contents;
			        if(!empty($crnt_child_keys)) {
			        	$child_keys = $crnt_child_keys;
				       	$child_keys[] = $new_item_key;
				    } else {
				    	$child_keys = array($new_item_key);
				    }
				    $unique_key = rand(1000,9999);
			        $multi_ship_id = 'multi_ship_'.$unique_key;

			        if(array_key_exists($cart_item_key, $cart)) {
						$woocommerce->cart->cart_contents[$cart_item_key]['multi_ship_address']['child_keys'] = $child_keys;
					}
					if(array_key_exists($new_item_key, $cart)) {
				     	$woocommerce->cart->cart_contents[$new_item_key]['multi_ship_address'] = array(
							'product_id' => $product_id,
							'variation_id' => $variation_id,
							'quantity' => '1',
							//'updated_quantity' => $updated_quantity,
							'multi_ship_id' => $multi_ship_id,
							'multi_ship_parent_id' => $parent_item_id,
							'child_keys' => array(),
							'parent_cart_key' => $cart_item_key,
						);

				     	if(!empty($thwepo_options) && apply_filters('thwma_apply_wepo_options_to_item', true, $new_item_key, $cart_item)){
				     		$woocommerce->cart->cart_contents[$new_item_key]['thwepof_options'] = $thwepo_options;
				     	}
						
						$woocommerce->cart->set_session();
					}

					// Create a new item display.
			        $cart_item_data = array();
			        foreach ($items as $key => $value) {
			        	$cart_item_data = isset($items[$new_item_key]) ? $items[$new_item_key] : '';
			        }

			        $link_show = '0';
    				$multi_shipping_addresses = self::multi_shipping_addresses($cart_item_data, $new_item_key, $link_show, $multi_ship_id);

					$multi_ship_form = '';
					$multi_ship_form .= '<tr class="multi-ship-tr">';
						$multi_ship_form .= '<td>';
							// $multi_ship_form .= '<a id ="remove-multi-ship-tr" class="remove-multi-ship-tr"  onclick="thwma_remove_multi_shipping_tr(event,this)"><span class="dashicons dashicons-dismiss"></span></a>';
						$multi_ship_form .= '</td>';
						$multi_ship_form .= '<td><input type="number" name="pdct-qty" value="1" min="1" class="multi-ship-pdct-qty pdct-qty sub-pdct-qty pdct-qty-'.$new_item_key.'" data-cart_key="'.$new_item_key.'"></td>';
						$multi_ship_form .= '<td><input class="multi-ship-item" type="hidden" data-multi_ship_id="'.$multi_ship_id.'" data-multi_ship_parent_id="'.$parent_item_id.'" data-sub_row_stage="1">'.$multi_shipping_addresses;
							$multi_ship_form .= '<a id ="remove-multi-ship-tr" class="remove-multi-ship-tr"  onclick="thwma_remove_multi_shipping_tr(event,this)"><span class="dashicons dashicons-dismiss"></span></a>';
						$multi_ship_form .= '</td>';
					$multi_ship_form .= '</tr>';
					// if(array_key_exists($parent_item_id, $cart[$cart_item_key]['multi_ship_address'])) {
					// 	//$woocommerce->cart->cart_contents[$cart_item_key]['multi_ship_address'][$parent_item_id]['updated_quantity'] = $updated_quantity;
					// 	$woocommerce->cart->cart_contents[$cart_item_key]['multi_ship_address']['updated_quantity'] = $updated_quantity;
					// 	$woocommerce->cart->set_session();
					// }
					echo $multi_ship_form;
				}
			}
			exit();
		}

		/**
		 * Remove multi-shipping row(checkout page-ajax response).
		 */
		public function remove_multi_shipping_row() {
			global $woocommerce;
			check_ajax_referer( 'remove-multi-shipping-row', 'security' );
			$product_id = isset($_POST['product_id']) ? sanitize_key($_POST['product_id']) : '';
			$cart_item_key = isset($_POST['cart_item_key']) ? sanitize_key($_POST['cart_item_key']) : '';
			$multi_ship_id = isset($_POST['multi_ship_id']) ? sanitize_key($_POST['multi_ship_id']) : '';
			$cart = $woocommerce->cart->cart_contents;
			if($cart) {
				$parent_cart_key = isset($cart[$cart_item_key]['multi_ship_address']['parent_cart_key']) ? $cart[$cart_item_key]['multi_ship_address']['parent_cart_key'] : '';

				if(!empty($parent_cart_key)) {
					$child_keys = $woocommerce->cart->cart_contents[$parent_cart_key]['multi_ship_address']['child_keys'];

					if(!empty($cart[$parent_cart_key]['multi_ship_address']['child_keys']) && is_array($cart[$parent_cart_key]['multi_ship_address']['child_keys'])) {
						foreach ($cart[$parent_cart_key]['multi_ship_address']['child_keys'] as $key => $value) {
							if($value == $cart_item_key) {
								unset($cart[$parent_cart_key]['multi_ship_address']['child_keys'][$key]);
							}
						}

						$woocommerce->cart->cart_contents[$parent_cart_key]['multi_ship_address']['child_keys'] = $cart[$parent_cart_key]['multi_ship_address']['child_keys'];
						$woocommerce->cart->set_session();
					}
				}
				WC()->cart->remove_cart_item( $cart_item_key );

				//$bool = WC_Cart::remove_cart_item( $cart_item_key );
				// if(!empty($cart[$cart_item_key]['multi_ship_address']) && is_array($cart[$cart_item_key]['multi_ship_address'])) {
				// 	foreach ($cart[$cart_item_key]['multi_ship_address'] as $key) {
				// 		unset($cart[$cart_item_key]['multi_ship_address'][$multi_ship_id]);
				// 	}
				// 	$woocommerce->cart->cart_contents[$cart_item_key]['multi_ship_address'] = $cart[$cart_item_key]['multi_ship_address'];
				// 	$woocommerce->cart->set_session();
				// }
			}
			exit();
		}
		/**
		 * Update order item meta.(for latest WC version).
		 *
		 * @param integer $item_id The item id
		 * @param array $item The item details
		 * @param integer $order_id The sorder id
		 */
	   	public function thwma_add_addrs_to_new_order_item($item_id, $item, $order_id) {
	   		global $woocommerce,$wpdb;
	   		$order = wc_get_order( $order_id );

	   		$legacy_values = is_object($item) && isset($item->legacy_values) ? $item->legacy_values : false;
	   		if(!empty($legacy_values)) {

	   			// Check the product is vertual or downloadable.
	   			$product = wc_get_product( $legacy_values[ 'product_id' ] );

	   			$downloadable = apply_filters( 'wmap_product_is_downloadable', $product->is_downloadable('yes'));
	   			if ((!$product->is_virtual()) && (!$downloadable)) {
	   			//if ((!$product->is_virtual()) && (!$product->is_downloadable('yes'))) {

					$product_shipping_address = isset($legacy_values['product_shipping_address']) ?  $legacy_values['product_shipping_address'] : '';

					$shipping_addr_info = array();

					// Check is user logged in.
			   		if (is_user_logged_in()) {
			   			if (isset($_COOKIE[THWMA_Utils::GUEST_USER_SHIPPING_ADDR])) {
			   				$shipping_address = $_COOKIE[THWMA_Utils::GUEST_USER_SHIPPING_ADDR];
							$shipping_address = preg_replace('!s:(\d+):"(.*?)";!', "'s:'.strlen('$2').':\"$2\";'", $shipping_address);
							$custom_address = unserialize(base64_decode($shipping_address));
							$shipping_addr_info = $this->thwma_add_order_item_datas($legacy_values,$custom_address);
							$user_id = get_current_user_id();
							update_user_meta($user_id, THWMA_Utils::USER_META_ENABLE_MULTI_SHIP, 'yes');
							update_user_meta($user_id, THWMA_Utils::ADDRESS_KEY, $custom_address);
						}

						if(empty($shipping_addr_info)) {
							$user_id = get_current_user_id();
							if(!empty($user_id)) {
								$custom_address  = get_user_meta($user_id , 'thwma_custom_address', true);
								$shipping_addr_info = $this->thwma_add_order_item_datas($legacy_values, $custom_address);
							}
						}

			   		} else {
			   			if (isset($_COOKIE[THWMA_Utils::GUEST_USER_SHIPPING_ADDR])) {
							$shipping_address = $_COOKIE[THWMA_Utils::GUEST_USER_SHIPPING_ADDR];
							$shipping_address = preg_replace('!s:(\d+):"(.*?)";!', "'s:'.strlen('$2').':\"$2\";'", $shipping_address);
							$custom_address = unserialize(base64_decode($shipping_address));
							$shipping_addr_info = $this->thwma_add_order_item_datas($legacy_values,$custom_address);
						}
			   		}
			   		// Shipping address name.
			   		if(!empty($shipping_addr_info)) {
			            wc_update_order_item_meta($item_id, THWMA_Utils::ORDER_KEY_SHIPPING_ADDR, $shipping_addr_info);
			        }
			        // Multi-shipping details.
			        $multi_ship_address = isset($legacy_values['multi_ship_address']) ?  $legacy_values['multi_ship_address'] : '';
			        if(!empty($multi_ship_address)) {
			        	wc_update_order_item_meta($item_id, THWMA_Utils::ORDER_KEY_SHIPPING_DATA, $multi_ship_address);
			        }

			        // $multi_ship_custom_fields = isset($legacy_values['thwma_custom_fields']) ?  $legacy_values['thwma_custom_fields'] : '';
			        // if(!empty($multi_ship_custom_fields)) {
			        // 	wc_update_order_item_meta($item_id, THWMA_Utils::ORDER_KEY_SHIPPING_CUS_FIELDS, $multi_ship_custom_fields);
			        // }

			        // Shipping method.

			        $shipping_method_data = isset($legacy_values['thwma_shipping_methods']) ?  $legacy_values['thwma_shipping_methods'] : '';
			        if(!empty($shipping_method_data)) {
			        	wc_update_order_item_meta($item_id, THWMA_Utils::ORDER_KEY_SHIPPING_METHOD, $shipping_method_data);
			        }

		    	} // Check the product is vertual or downloadable.

		   	}

	   	}

	   	/**
         * Function for add order item datas
         *
         * @param array $data The item datas
         * @param array $custom_address The custom addresses
         *
         * @return array
         */
		public function thwma_add_order_item_datas($data, $custom_address) {
			$unique_key = isset($data['unique_key']) ? $data['unique_key'] : '';
	   		if(is_array($data)) {
				if(!empty($data['product_shipping_address']) && isset($data['product_shipping_address'])) {
					$adrs_key = 'Shipping Address';
					$shipping_addr = isset($data['product_shipping_address']) ? $data['product_shipping_address']: '';
					// $custom_address  = get_user_meta($user_id , 'thwma_custom_address', true);
					$custom_address = $custom_address;
					$shipping_addr_info = array();
					if(!empty($custom_address)) {
						$ship_addrs = array();
						$dflt_ship_addrs = array();
						if(is_array($custom_address)){
							foreach ($custom_address as $key => $value) {
								$ship_addrs = isset($custom_address['shipping']) ? $custom_address['shipping']:'';
								$dflt_ship_addrs = isset($custom_address['default_shipping']) ? $custom_address['default_shipping']:'';
							}
						}
						$pdt_shipp_addr = array();
						if(!empty($ship_addrs) && is_array($ship_addrs)) {
							foreach ($ship_addrs as $adr_key => $adr_value) {
								if($adr_key == $shipping_addr) {
									$pdt_shipp_addr = $adr_value;
								}
							}
						}
						// $pdt_shipp_addr = implode(", ", $pdt_shipp_addr);
						//$shipping_addr_info = array();
						if(!empty($pdt_shipp_addr)) {
							$shipping_addr_info[$shipping_addr] = array(
								'product_id' => isset($data['product_id']) ? $data['product_id'] : '',
								'shipping_address' => $pdt_shipp_addr,
								'unique_key' => $unique_key,
							);
							return $shipping_addr_info;
						}
					}
					return $shipping_addr_info;
				}
			}
	   	}

	   	/**
		 * Update order item meta.(for below 3.0.0 WC version).
		 *
		 * @param integer $item_id The item id
		 * @param array $values The values
		 * @param integer $cart_item_key The cart item key
		 */
		public function thwma_add_addrs_to_order_item_meta($item_id, $values, $cart_item_key) {
			global $woocommerce, $wpdb;
			if (is_user_logged_in()) {
				$current_user_id = get_current_user_id();
				if(!empty($current_user_id)) {
					$shipping_address_name = array();
					if(!empty($values) && is_array($values)) {

						// Check the product is vertual or downloadable.
			   			$product = wc_get_product( $values[ 'product_id' ] );

			   			$downloadable = apply_filters( 'wmap_product_is_downloadable', $product->is_downloadable('yes'));
			   			if ((!$product->is_virtual()) && (!$downloadable)) {
			   			// if ((!$product->is_virtual()) && (!$product->is_downloadable('yes'))) {

						foreach($values as $key => $data) {
							// 	$shipping_address_name[] = isset($data['product_shipping_address']) ? $data['product_shipping_address']: '';
							// 	$shipping_address_name_1 = array_filter($shipping_address_name);
							// }
							if(is_array($data)) {
								if(!empty($data['product_shipping_address']) && isset($data['product_shipping_address'])) {
									$pdt_ship_adr = '';
									$pdt_ship_adr = isset($data['product_shipping_address']) ? $data['product_shipping_address'] : '';
									$adrs_key = 'Shipping Address';
									$shipping_addr = isset($data['product_shipping_address']) ? $data['product_shipping_address']: '';
									$custom_address  = get_user_meta($current_user_id , 'thwma_custom_address', true);
									if(!empty($custom_address)) {
										$ship_addrs = array();
										$dflt_ship_addrs = array();
										if(!empty($custom_address) && is_array($custom_address)) {
											foreach ($custom_address as $key => $value) {
												$ship_addrs = isset($custom_address['shipping']) ? $custom_address['shipping']:'';
												$dflt_ship_addrs = isset($custom_address['default_shipping']) ? $custom_address['default_shipping']:'';
											}
										}
										$pdt_shipp_addr = array();
										if(!empty($ship_addrs) && is_array($ship_addrs)) {
											foreach ($ship_addrs as $adr_key => $adr_value) {
												if($adr_key == $shipping_addr) {
													$pdt_shipp_addr = $adr_value;
												}
											}
										}
										// $pdt_shipp_addr = implode(", ", $pdt_shipp_addr);
										$shipping_addr_info = array();
										if(!empty($pdt_shipp_addr)) {
											$shipping_addr_info[$pdt_ship_adr] = array(
												'Shipping Address' => $pdt_shipp_addr,
											);
										}
									}
									if(!empty($shipping_addr_info)) {
							            wc_update_order_item_meta($item_id, THWMA_Utils::ORDER_KEY_SHIPPING_ADDR, $shipping_addr_info);
							        }
								}
							}
						}

						} // Check the product is vertual or downloadable.
					}
				}
			}
		}

		/**
		 * To save the item name on shipping method object(display item below shipping method on order edit page)
		 * @param $item
		 * @param $package_key
		 * @param $package
		 */
		function order_shipping_item( $item, $package_key, $package, $order ) {
			$note = [];
		    foreach ($package['contents'] as $key => $package_data) {
		    	$qunatity = $package_data['quantity'];
		    	$shipping_methods = '';
				foreach ($package_data as $pk_key => $package_value) {
		    		$shipping_methods = isset($package_data['thwma_shipping_methods']) ? $package_data['thwma_shipping_methods'] : '';
		    		$multi_ship_address = isset($package_data['multi_ship_address']) ? $package_data['multi_ship_address'] : '';
		    	}
		    	if(!empty($shipping_methods) && is_array($shipping_methods)) {
			    	foreach ($shipping_methods as $ship_key => $ship_value) {
			    		$note[] = $ship_value['item_name'].' × '.$qunatity;
			    	}

			    } else if(!empty($multi_ship_address) && is_array($multi_ship_address)) {
			    	$product_id = isset($multi_ship_address['product_id']) ? $multi_ship_address['product_id'] : '';
					$product = wc_get_product( $product_id );
					$product_name = $product->get_name();
					$note[] = $product_name.' × '.$qunatity;
			    }
		    }
		    if ( $note ) {
		    	if(isset($note[$package_key])) {
		    		$item->add_meta_data( 'Items', $note[$package_key], true );
		    	}
		    }

		}

		public function add_new_shipping_address() {
			check_ajax_referer( 'add-new-shipping-address', 'security' );
			if(isset($_POST['thwma_save_address'])) {
				echo $this->thwma_save_address();
			}
			$load_address = sanitize_key('shipping');
			$country      = get_user_meta(get_current_user_id(), $load_address . '_country', true);
			if ('shipping' === $load_address) {
				$allowed_countries = WC()->countries->get_shipping_countries();
				if($country != null) {
					if (! array_key_exists($country, $allowed_countries)) {
						$country = current(array_keys($allowed_countries));
					}
				}
			}
			$address = WC()->countries->get_address_fields($country, $load_address . '_');
			if(!empty($address) && is_array($address)) {
				foreach ($address as $key => $field) {
					$value = get_user_meta(get_current_user_id(), $key, true);
					if (! $value) {
						switch ($key) {
							case 'billing_email':
							case 'shipping_email':
								$value = $current_user->user_email;
								break;
						}
					}
					$address[ $key ]['value'] = '';
				}
			} ?>
			<div class="thwma-cart-modal-content2">
				<div class="thwma-cart-modal-title-bar2" >
					<!-- <span class="thwma-cart-modal-title" >Shipping Address</span> -->
					<span class="thwma-cart-modal-close2" onclick="thwma_close_cart_add_adr_modal(event);">&times;</span>
				</div>
				<div class="thwma_hidden_error_mssgs"></div>
				<div id="cart_shipping_form_wrap">
				<form method="post" id="cart_shipping_form" name="thwma_cart_ship_form_action">
					<!-- <input type="hidden" name="cart_ship_form_action" value="<?php wp_create_nonce('thwma_cart_ship_form_action') ?>"> -->
					<?php echo '<input type="hidden" name="cart_ship_form_action" id="cart_ship_form_action" value="' . wp_create_nonce( 'thwma_cart_ship_form_action' ) . '" />';
					//$ajax_nonce = wp_create_nonce( "wpdocs-special-string" );
					//$nonce = wp_create_nonce('cart_ship_form_action'); ?>
					<div>
						<?php if(!empty($address) && is_array($address)) {
							foreach ($address as $key => $field) {
								woocommerce_form_field($key, $field, wc_get_post_data_by_key($key, $field['value']));
								// THWMA_Utils::thwma_cart_shipping_woocommerce_form_field($key, $field, wc_get_post_data_by_key($key, $field['value']));
							}
						}
						do_action("woocommerce_after_edit_address_form_{$load_address}");?>
					</div>
					<p>
						<button id="thwma_save_address_cart" type="submit" class="button form-row-odd" name="thwma_save_address" onclick="thwma_cart_save_address(event);" value="<?php esc_attr_e('Save address', 'woocommerce'); ?>"><?php esc_html_e('Save address', 'woocommerce'); ?></button>
					</p>
				</form>
				</div>
			</div>
			<?php exit();
		}
		public static function thwma_save_address() {
			check_ajax_referer( 'cart_ship_form_action', 'security', false );
			$error_messgs = '';
			$user_id = get_current_user_id();
			// if ($user_id <= 0) {
			// 	return;
			// }
			$customer = new \WC_Customer($user_id);
			// if (! $customer) {
			// 	return;
			// }
			$load_address = sanitize_key('shipping');
			$country      = get_user_meta(get_current_user_id(), $load_address . '_country', true);
			$cart_shipping = isset($_POST['cart_shipping']) ? $_POST['cart_shipping'] : '';
			$cfe_hide_field = isset($_POST['cfe_hide_field']) ? $_POST['cfe_hide_field'] : '';
			$cart_shipping_data = self::thwma_unserializeForm($cart_shipping);
			$new_shipping_country = isset($cart_shipping_data['shipping_country']) ? $cart_shipping_data['shipping_country'] : '';
			$country = $new_shipping_country;
			$country_exist = 'true';
			if ('shipping' === $load_address) {
				$allowed_countries = WC()->countries->get_shipping_countries();

				if (! array_key_exists($country, $allowed_countries)) {
					$country_exist = 'false';
					$country = current(array_keys($allowed_countries));
				}
			}
			// $cart_shipping = $_POST['cart_shipping'];
			// $cart_shipping_data = self::thwma_unserializeForm($cart_shipping);
			// $new_shipping_country = isset($cart_shipping_data['shipping_country'])?$cart_shipping_data['shipping_country']:'';
			$address = WC()->countries->get_address_fields(wc_clean(wp_unslash($new_shipping_country)), $load_address . '_');
			$address_data = array();
			$address_new = array();
			if(!empty($address) && is_array($address)){
				foreach ($address as $key => $field) {
					if(!empty($cart_shipping_data) && is_array($cart_shipping_data)){
						foreach ($cart_shipping_data as $fkey => $value) {
							$address_data[$key] = array(
								'label' 		=> isset($field['label']) ? $field['label'] : '',
								'value' 		=> isset($cart_shipping_data[$key]) ? $cart_shipping_data[$key] : '',
								'required' 		=> isset($field['required']) ? $field['required'] : '',
								'type' 			=> isset($field['type']) ? $field['type'] : '',
								'validate' 		=> isset($field['validate']) ? $field['validate'] : ''
								);
							//$address_new[$key] = isset($cart_shipping_data[$key]) ? $cart_shipping_data[$key] : '';
							$address_new[$key] = $cart_shipping_data[$key];
						}
					}
				}
			}

			// Validate the address.
			$true_check = self::validate_cart_shipping_addr_data($cart_shipping_data, $load_address, $country, $address_data, $country_exist, $cfe_hide_field);

			$true_check_val = '';
			if($true_check ==  'true') {
				if ($user_id) {
					THWMA_Utils::save_address_to_user($user_id, $address_new, 'shipping');
					$true_check_val = 'true';
				}
			} else {
				$true_check_val = $true_check;
			}

			// Create new dropdown list of newly added address.
			$adr_key = '';
			$custom_address = THWMA_Utils::get_custom_addresses($user_id,'shipping');
			if(!empty($custom_address) && is_array($custom_address)) {
				foreach ($custom_address as $a_key => $a_value) {
					$adr_key = $a_key;
				}
			}
			$address_dropdown = '';
			$opt_key = $adr_key;
			$value = '';

			$type  = 'shipping';
			$adrsvalues_to_dd = array();
			if(!empty($cart_shipping_data) && is_array($cart_shipping_data)) {
				foreach ($cart_shipping_data as $adrs_key => $adrs_value) {
					if($adrs_key == 'shipping_address_1' || $adrs_key =='shipping_address_2' || $adrs_key =='shipping_city' || $adrs_key =='shipping_state' || $adrs_key =='shipping_postcode') {
						if($adrs_value) {
							$adrsvalues_to_dd[] = $adrs_value;
						}
					}
				}
			}
			$new_adrs_string = implode(', ', $adrsvalues_to_dd);
			// $new_address = THWMA_Utils::get_formated_address('shipping', $cart_shipping_data);
			// $separator = ', ';
			// $new_option = THWMA_Utils::thwma_get_formatted_address($new_address, $separator);
			if($true_check_val == 'true') {
				$address_dropdown .= '<option value="' . esc_attr($opt_key) . '" ' . selected($value, $opt_key, false) . ' >' . esc_attr($new_adrs_string) . '</option>';
			}

			$customer_id = get_current_user_id();

			// Create new tile of shipping address.
			if($true_check_val == 'true') {
				$output_shipping = self::get_tile_field_to_cart($customer_id, 'shipping');
			} else {
				$output_shipping = '';
			}
			$total_address_count = THWMA_Utils::get_total_address_count();
			$output_table = self::multiple_address_management_form();
			$response = array(
				'result_shipping' => $output_shipping,
				'true_check' => $true_check_val,
				'address_dropdown' => $address_dropdown,
				'address_count' => $total_address_count,
				'output_table' => $output_table,
			);
			// Clean all output buffers (in case any unwanted output was generated)
			while (ob_get_level()) {
			    ob_end_clean();
			}

			wp_send_json($response);
			exit();
		}

		/**
		 * Add new shipping addresses on cart page - (Guest user-ajax response).
		 */
		public function guest_users_add_new_shipping_address() {
			check_ajax_referer( 'guest-users-add-new-shipping-address', 'security' );
			if(isset($_POST['thwma_save_guest_address'])) {
				echo $this->thwma_save_guest_address();
			}
			$load_address = sanitize_key('shipping');
			//$country      = get_user_meta(get_current_user_id(), $load_address . '_country', true);
			if ('shipping' === $load_address) {
				$allowed_countries = WC()->countries->get_shipping_countries();
				$country = current(array_keys($allowed_countries));
			}
			$address = WC()->countries->get_address_fields($country, $load_address . '_');
			if(!empty($address) && is_array($address)) {
				foreach ($address as $key => $field) {
					$value = get_user_meta(get_current_user_id(), $key, true);
					if (! $value) {
						switch ($key) {
							case 'billing_email':
							case 'shipping_email':
								$value = $current_user->user_email;
								break;
						}
					}
					$address[ $key ]['value'] = '';
				}
			} ?>
			<div class="thwma-cart-modal-content2">
				<div class="thwma-cart-modal-title-bar2" >
					<!-- <span class="thwma-cart-modal-title" >Shipping Address</span> -->
					<span class="thwma-cart-modal-close2" onclick="thwma_close_cart_add_adr_modal(event);">&times;</span>
				</div>
				<div class="thwma_hidden_error_mssgs"></div>
				<div id="cart_shipping_form_wrap">
				<form method="post" id="cart_shipping_form" name="thwma_guest_cart_ship_form_action">
					<!-- <input type="hidden" name="guest_cart_ship_form_action" id="guest_cart_ship_form_action" value="<?php wp_create_nonce('quest_thwma_cart_ship_form_action') ?>"> -->
					<?php echo '<input type="hidden" name="guest_cart_ship_form_action" id="guest_cart_ship_form_action" value="' . wp_create_nonce( 'thwma_guest_cart_ship_form_action' ) . '" />'; ?>
					<div>
						<?php if(!empty($address) && is_array($address)){
							foreach ($address as $key => $field) {
								woocommerce_form_field($key, $field, wc_get_post_data_by_key($key, $field['value']));
								// THWMA_Utils::thwma_cart_shipping_woocommerce_form_field($key, $field, wc_get_post_data_by_key($key, $field['value']));
							}
						}
						do_action("woocommerce_after_edit_address_form_{$load_address}"); ?>
					</div>
					<p>
						<button id="thwma_save_guest_address_cart" type="submit" class="button" name="thwma_save_guest_address" onclick="thwma_cart_save_guest_address(event);" value="<?php esc_attr_e('Save address', 'woocommerce'); ?>"><?php esc_html_e('Save address', 'woocommerce'); ?></button>
					</p>
				</form>
				</div>
			</div>
			<?php exit();
		}

		/**
		 * Save new shipping address from cart page(Ajax function-guest user).
		 */
		public static function thwma_save_guest_address() {
			check_ajax_referer( 'thwma-save-guest-address', 'security' );
			$error_messgs = '';
			$index = 0;
			$load_address = sanitize_key('shipping');
			$cart_shipping = isset($_POST['cart_shipping']) ? $_POST['cart_shipping'] : '';
			$cfe_hide_field = isset($_POST['cfe_hide_field']) ? $_POST['cfe_hide_field'] : '';

			$cart_shipping_data = self::thwma_unserializeForm($cart_shipping);

			$new_shipping_country = isset($cart_shipping_data['shipping_country']) ? $cart_shipping_data['shipping_country'] : '';
			$country = $new_shipping_country;
			$country_exist = 'true';
			if ('shipping' === $load_address) {
				$allowed_countries = WC()->countries->get_shipping_countries();
				if (! array_key_exists($country, $allowed_countries)) {
					$country_exist = 'false';
					$country = current(array_keys($allowed_countries));
				}
			}
			$address = WC()->countries->get_address_fields($country, $load_address . '_');
			$address_data = array();
			$address_new = array();
			if(!empty($address) && is_array($address)){
				foreach ($address as $key => $field) {
					if(!empty($cart_shipping_data) && is_array($cart_shipping_data)) {
						foreach ($cart_shipping_data as $fkey => $value) {
							$address_data[$key] = array(
								'label' =>isset($field['label']) ? $field['label'] : '',
								'value' => isset($cart_shipping_data[$key]) ? $cart_shipping_data[$key] : '',
								'required' => isset($field['required']) ? $field['required'] : '',
								'type' => isset($field['type']) ? $field['type'] : '',
								'validate' => isset($field['validate']) ? $field['validate'] : ''
								);
							//$address_new[$key] = $cart_shipping_data[$key];
							$address_new[$key] = isset($cart_shipping_data[$key]) ? $cart_shipping_data[$key] : '';
						}
					}
				}
			}
			$true_check = self::validate_cart_shipping_addr_data($cart_shipping_data, $load_address, $country, $address_data, $country_exist, $cfe_hide_field);
			$true_check_val = '';
			if($true_check == 'true') {
				$settings_guest_usr = THWMA_Utils::get_setting_value('settings_guest_users');
				$settings = $settings_guest_usr;
				if($settings && !empty($settings)) {
					$enabled_guest_user = isset($settings['enable_guest_shipping']) ? $settings['enable_guest_shipping'] : '';
					if($enabled_guest_user == 'yes') {
						$expiration = self::expiration_time();
						THWMA_Utils::save_address_to_guest_user($address_new, 'shipping', $expiration);
						$true_check_val = 'true';
					} else {
						$true_check_val = 'error';
					}
				} else {
					$true_check_val = 'error';
				}
			} else {
				$true_check_val = $true_check;
			}

			// Create new tile field for append the existing address tiles.
			$custom_address = THWMA_Utils::get_custom_addresses_of_guest_user('shipping');
			if(is_array($custom_address)) {
				$adr_count = count($custom_address);
			} else {
				$adr_count = '';
			}
			$new_id = THWMA_Utils::get_guest_user_new_custom_id('shipping');
			// new.
			// if($new_id == ''){
			// 	$new_id = '1';
			// }
			$address_key = 'address_'.$new_id;
			if($address_key == 'address_') {
				$address_key = 'address_1';
			}

			$add_list_class = 'thwma-thslider-list-ms ship';
			$new_adr_count = $new_id + 1;
			if(empty($adr_count)) {
				$adr_count = '';
				$return_adr_tile  = '';
				$return_adr_tile .= '<div class="thwma-thslider-box">';
		           	$return_adr_tile .= '<div class="thwma-thslider-viewport '.esc_attr('shipping').'">';
			           	$return_adr_tile .= '<ul class=" '.esc_attr($add_list_class).'">';
			           		$return_adr_tile .= self::create_a_new_adrs_tile_for_guest_user($cart_shipping_data, $address_key, $adr_count);

			           	$return_adr_tile .= '</ul>';
			        $return_adr_tile .= '</div>';
			    $return_adr_tile .= '</div>';
			} else {
				$return_adr_tile = self::create_a_new_adrs_tile_for_guest_user($cart_shipping_data, $address_key, $adr_count);
			}
			// Multi-shipping address table.
			$type  = 'shipping';
			$shipping_table = '';
			if(empty($adr_count)) {
				$shipping_table = self::guest_multiple_address_management_form();
			}

			// Create a new dropdown option.
			$opt_key = $address_key;
			$value = '';
			$address_dropdown = '';
			$adrsvalues_to_dd = array();
			if(!empty($cart_shipping_data) && is_array($cart_shipping_data)) {
				foreach ($cart_shipping_data as $adrs_key => $adrs_value) {
					if($adrs_key == 'shipping_address_1' || $adrs_key =='shipping_address_2' || $adrs_key =='shipping_city' || $adrs_key =='shipping_state' || $adrs_key =='shipping_postcode') {
						if($adrs_value) {
							$adrsvalues_to_dd[] = $adrs_value;
						}
					}
				}
			}

			// Set the Default address.
			// $default_addr = '';
			// 		if(!empty($cart_shipping_data) && is_array($cart_shipping_data)) {
			// 			foreach ($cart_shipping_data as $key => $value) {
			// 				$custom_adr[] = $value;
			// 			}
			// 		}
			// 		if(!empty($custom_adr)) {
			// 			$encoded_default_address = json_encode($custom_adr[0]);
			// 			$default_addr .= '<input type="hidden" name="ship_default_adr" value='.$encoded_default_address.'>';
			// 		}




			$new_adrs_string = implode(', ', $adrsvalues_to_dd);
			// $new_address = THWMA_Utils::get_formated_address('shipping', $cart_shipping_data);
			// $separator = ', ';
			// $new_option = THWMA_Utils::thwma_get_formatted_address($new_address, $separator);
			$address_dropdown .= '<option value="' . esc_attr($opt_key) . '" ' . selected($value, $opt_key, false) . ' >' . esc_attr($new_adrs_string) . '</option>';

			// Slider arrows.
			$slider_arrows = '';
			$slider_arrows.= '<div class="control-buttons control-buttons-'.esc_attr($type).'">';
			if($adr_count && $adr_count >= 3) {
            	$slider_arrows .= '<div class="prev thwma-thslider-prev multi-'.esc_attr('shipping').'"><i class="fa fa-angle-left fa-3x"></i></div>';
            	$slider_arrows .= '<div class="next thwma-thslider-next multi-'.esc_attr('shipping').'"><i class="fa fa-angle-right fa-3x"></i></div>';
            }
            $slider_arrows.= '</div>';
            $total_address_count = '';
            $total_address_count = THWMA_Utils::get_total_address_count();

           	if(is_numeric( $total_address_count)) {
            	$total_address_count = $total_address_count + 1;
            } else if($total_address_count == null) {
            	$total_address_count = 1;
            }

            // Address limit.
			$address_limit = '';
            if($type) {
                $address_limit = THWMA_Utils::get_setting_value('settings_'.$type , $type.'_address_limit');
            }
            if (!is_numeric($address_limit)) {
                $address_limit = 0;
            }

            $response = array(
				//'result_shipping' => $output_shipping,
				'adr_count' => $adr_count,
				'true_check' => $true_check_val,
				'new_tile' => $return_adr_tile,
				'shipping_table' => $shipping_table,
				'address_dropdown' => $address_dropdown,
				'slider_arrows' => $slider_arrows,
				'default_address' => $opt_key,
				'address_count' => $total_address_count,
				'address_limit' => $address_limit,
			);
			wp_send_json($response);
			exit();
		}

		/**
		 * Function for validate multi shipping address form address data(checkout page).
         * Function for validate cart shipping address form address data(cart page).
         *
         * @param array $cart_shipping The cart shipping details
         * @param array $load_address The loaded addresses
         * @param string $country The country name
         * @param array $address_data The address datas
         * @param string $country_exist  Check info of the country is exist
         *
         * @return string.
         */
		public static function validate_cart_shipping_addr_data($cart_shipping, $load_address, $country, $address_data, $country_exist, $cfe_hide_field=array()) {
			$true_check = array();
			$error_check = '';
			$validation_woocommerce = new \WC_Validation();

			// Ensure $cfe_hide_field is an array
			if (!is_array($cfe_hide_field)) {
				$cfe_hide_field = array();
			}

			if(!empty($address_data) && is_array($address_data)) {
				foreach($address_data as $dkey => $dvalue) {
					$value = $dvalue['value'];
					$required = $dvalue['required'];
					$ftype = $dvalue['type'];
					$validate = $dvalue['validate'];
					if (! isset($ftype)) {
						$type = 'text';
					}
					if (! empty($required) && empty($value)) {
						// check if thcfe required field is hidden (cfe).
						if (!in_array($dkey, $cfe_hide_field)) {
							$error_check .= esc_html__($dvalue['label'].' is a required field.', 'woocommerce');
							$error_check .= '</br>';
							$true_check[] = false;
						}
					} else {
						$true_check[] = true;
					}
					if (! empty($value)) {

						// Validation and formatting rules.
						if (! empty($validate) && is_array($validate)) {
							foreach ($validate as $rule) {
								if ($rule == 'postcode') {
									$country = isset($cart_shipping[$load_address . '_country']) ? $cart_shipping[$load_address . '_country']: '';
									if ($country_exist == 'false') {
										$error_check .= esc_html__('Please enter a valid country', 'woocommerce');
										$error_check .= '</br>';
										$true_check[] = false;
									} else {
										$true_check[] = true;
									}
									//$country = wc_clean(wp_unslash($_POST[ $load_address . '_country' ]));
									$value   = wc_format_postcode($value, $country);
									if ('' !== $value && ! $validation_woocommerce->is_postcode($value, $country)) {
										switch ($country) {
											case 'IE':
												$postcode_validation_notice = esc_html__('Please enter a valid Eircode.', 'woocommerce');
												break;
											default:
												$postcode_validation_notice = esc_html__('Please enter a valid postcode / ZIP.', 'woocommerce');
										}
										//wc_add_notice($postcode_validation_notice, 'error');
										$error_check .= $postcode_validation_notice;
										$true_check[] = false;
									} else {
										$true_check[] = true;
									}
								}
								if ($rule == 'phone') {
									if ('' !== $value && ! $validation_woocommerce->is_phone($value)) {
										$error_check .=  esc_html__($dvalue['label'].' is not a valid phone number.', 'woocommerce');
										$true_check[] = false;
									} else {
										$true_check[] = true;
									}
								}
								if ($rule == 'email') {
									$value = strtolower($value);
									if (! is_email($value)) {
										$error_check .=  esc_html__($dvalue['label'].' is not a valid email address.', 'woocommerce');
										$true_check[] = false;
									} else {
										$true_check[] = true;
									}
								}
							}
						}
					}
				}
			} else {
				$error_check .=  esc_html__('Your address fields are empty', 'woocommerce');
				$true_check[] = false;
			}
			$true_chk = array_unique($true_check);
			if (in_array(false, $true_chk)) {
				return $error_check;
			} else {
				return 'true';
			}
			//return $true_check;
		}

		/**
         * Function for set expiration time (Guest users)
         *
         * @return string
         */
		public static function expiration_time() {
			$settings_guest_usr = THWMA_Utils::get_setting_value('settings_guest_users');
			$settings = $settings_guest_usr;
			$expiration = '';
			if(isset($settings)) {
				$time_duration = isset($settings['set_time_duration']) ? $settings['set_time_duration']:'';
				$time_limit = isset($settings['time_limit']) ? $settings['time_limit']:'';
				if($time_limit == 'minute') {
					$expiration = $time_duration * MINUTE_IN_SECONDS;
				} else if($time_limit == 'hour') {
					$expiration = $time_duration * HOUR_IN_SECONDS;
				} else if($time_limit == 'day') {
					$expiration = $time_duration * DAY_IN_SECONDS;
				}
			}
			return $expiration;
		}

		public static function create_a_new_adrs_tile_for_guest_user($cart_shipping_data, $address_key, $adr_count) {
			if(!empty($adr_count)) {
				$i = $adr_count;
			} else{
				$i = '';
			}
			$action_row_html = '';
			$address_type_css = '';

			$disable_adr_mngmnt = self::disable_address_mnagement();
			if($disable_adr_mngmnt == true) {
				$disable_mngt_class = 'thwma_disable_adr_mngt';
				$disable_acnt_sec = 'disable_acnt_sec';
			} else {
				$disable_mngt_class = '';
				$disable_acnt_sec = '';
			}
			$type  = 'shipping';
			$new_address = THWMA_Utils::get_formated_address('shipping', $cart_shipping_data);
			$options_arr = THWMA_Utils::thwma_get_formatted_address($new_address);
			$address_key_param = "'".$address_key."'";
			$address_type = "'".$type."'";
			$heading = !empty($new_address[$type.'_heading']) ? $new_address[$type.'_heading'] : esc_html__('', 'woocommerce-multiple-addresses-pro') ;

			$action_row_html .= '<div class="thwma-adr-footer address-footer">
				<div class="btn-delete '.esc_attr($disable_mngt_class).'" data-index="0" data-address-id="" onclick="thwma_guest_users_delete_selected_address_cart_page(this,'.esc_attr($address_type).','.esc_attr($address_key_param).')" title="'.esc_html__('Delete', 'woocommerce-multiple-addresses-pro').'">
					<span>'.esc_html__('Delete', 'woocommerce-multiple-addresses-pro').'</span>
				</div>
			</div>';
			if(isset($heading) && $heading != '') {
				$heading_css = '<div class="address-type-wrapper row">
					<div title="'.$heading.'" class="address-type '.esc_attr($address_type_css).'">'.esc_html($heading).'</div>
					</div>
					<div class="tile-adrr-text thwma-adr-text address-text address-wrapper">'.$options_arr.'</div>';
			} else {
				$heading_css = '<div class="tile-adrr-text thwma-adr-text address-text address-wrapper wrapper-only">'.$options_arr.'</div>';
			}
			$add_class  = "thwma-thslider-item-ms $type " ;
			$add_class .= $i == 0 ? ' first' : '';
			$return_html = '';
			$return_html .= '<li class="'.$add_class.'" value="'. esc_attr($address_key).'" >
				<div class="thwma-adr-box address-box" data-index="'.esc_attr($i).'" data-address-id="">
					<div class="thwma-main-content">
						<div class="complete-aaddress">
							'.$heading_css.'
						</div>
						<div class="btn-continue address-wrapper">
							<a class="th-btn button primary is-outline '.esc_attr($address_key).'" onclick="thwma_populate_selected_address(event, this, '.esc_attr($address_type).', '.esc_attr($address_key_param).')">
								<span>'.esc_html__('Choose This Address', 'woocommerce-multiple-addresses-pro').'</span>
							</a>
						</div>
					</div>'.$action_row_html.'</div></li>';
			return $return_html;
		}

		/**
         * Function for guest- first multi-shipping management form( checkout page-qust add first address).
         *
         * @return string
         */
		public static function guest_multiple_address_management_form() {
			global $woocommerce;
			$items = $woocommerce->cart->get_cart();
    		$custom_addresses = self::get_saved_custom_addresses_from_db();
    		$multi_ship_form = '';
    		if(empty($custom_addresses)) {
    			$product = __("Product", "woocommerce-multiple-addresses-pro");
	    		$quantity = __("Quantity", "woocommerce-multiple-addresses-pro");
	    		$sent_to = __("Sent to", "woocommerce-multiple-addresses-pro");
    			$multi_ship_form .= '<div class="multi-shipping-table-wrapper">';
					$multi_ship_form .= '<div class="multi-shipping-table-overlay"></div>';
					$multi_ship_form .= '<table class="multi-shipping-table">';
		    			$multi_ship_form .= '<tr>';
		    				$multi_ship_form .= '<th>'.$product.'</th>';
		    				$multi_ship_form .= '<th>'.$quantity.'</th>';
		    				$multi_ship_form .= '<th>'.$sent_to.':</th>';
		    			$multi_ship_form .= '</tr>';
		    			$i = 1;
		        		$ini_stage = 0;
		    			foreach ($items as $key => $value) {
		    				$multi_ship_id = 'multi_ship_'.$i;
		    				$product = wc_get_product( $value[ 'product_id' ] );

		    				$cart = $woocommerce->cart->cart_contents;
		    				$qty = $value['quantity'];
							$multi_ship_parent_id = '';

							$cart_item = $items[$key];
					    	$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $key );

					    	$child_keys = '';
					    	if($qty <= 1) {
					    		$link_show = false;
					    	} else {
					    		$link_show = true;
					    	}
					    	$multi_shipping_addresses = self::multi_shipping_addresses($value, $key, $link_show, $multi_ship_id);

					    	// Check the product is vertual or downloadable.
					    	$downloadable = apply_filters( 'wmap_product_is_downloadable', $product->is_downloadable('yes'));
					    	if ((!$downloadable)) {
					    	//if ((!$product->is_downloadable('yes'))) {
			    				if(!empty($cart[$key]['multi_ship_address'])) {
			    					$multi_ship_content = $cart[$key]['multi_ship_address'];
			    					if(!empty($multi_ship_content) && is_array($multi_ship_content)) {
				    					foreach ( $multi_ship_content as $multi_ship_datas) {
				    						$multi_ship_parent_id = $multi_ship_content['multi_ship_parent_id'];
				    						$child_keys = $multi_ship_content['child_keys'];
				    					}
				    				}
				    				//$child_keys = $child_keys_data;
				    				// $child_keys_data = $child_keys;

									if($multi_ship_parent_id == null) {
										if($qty == 1) {
					        				$link_show = false;
					        			} else {
					        				$link_show = true;
					        			}
					        			$multi_shipping_addresses = self::multi_shipping_addresses($value, $key, $link_show, $multi_ship_id);
										$multi_ship_form .= '<tr class="main-pdct-tr">';
						    				$multi_ship_form .= '<td class="wmap-img-tr">';
						    					$multi_ship_form .= '<div class="checkout-thumbnail-img">';
							    					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $items ) : '', $items, $key );
								    				//$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $items, $key );
								    				$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $key );

													if ( ! $product_permalink ) {
														echo $thumbnail; // PHPCS: XSS ok.
													} else {
														$multi_ship_form .= '<a href="'.esc_url( $product_permalink ).'">'.$thumbnail.'</a>';
													}
								    				//$multi_ship_form .= '<p class="product-thumb-name">'.$_product->get_name().'</p>';
								    				$multi_ship_form .= '<p class="product-thumb-name">';
														if ( ! $product_permalink ) {
															$multi_ship_form .=  wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $key ) . '&nbsp;' );
														} else {
															$multi_ship_form .=  wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $key ) );
														}

														$multi_ship_form .=  wc_get_formatted_cart_item_data( $cart_item );
													$multi_ship_form .= '</p>';
									    			//do_action( 'woocommerce_after_cart_item_name', $cart_item, $key );

								    			$multi_ship_form .= '</div>';
						    				$multi_ship_form .= '</td>';
						    				$multi_ship_form .= '<td><input type="number" min="1" name="pdct-qty" value="'.$qty.'" class="multi-ship-pdct-qty pdct-qty main-pdct-qty pdct-qty-'.$key.'" data-cart_key="'.$key.'"></td>';
						    				$multi_ship_form .= '<td><input class="multi-ship-item" type="hidden" data-multi_ship_id="multi_ship_'.$i.'" data-multi_ship_parent_id="0" data-updated_qty="'.$qty.'" data-sub_row_stage="1">'.$multi_shipping_addresses.'</td>';
						    			$multi_ship_form .= '</tr>';

					    				if(!empty($child_keys) && is_array($child_keys)) {
					    					foreach ( $child_keys as $item_key) {
						    					if(!empty($cart[$item_key])) {
					    							$cart_data = $cart[$item_key];
					    							$child_qty = isset($cart[$item_key]['quantity']) ? $cart[$item_key]['quantity'] : '';
					    							$multi_ship_id = $cart[$item_key]['multi_ship_address']['multi_ship_id'];
													$link_show = false;
					    							$multi_shipping_addresses = self::multi_shipping_addresses($cart_data, $item_key, $link_show, $multi_ship_id);

					    							$multi_ship_form .= '<tr>';
									    				$multi_ship_form .= '<td>';
											    			// $multi_ship_form .= '<a id ="remove-multi-ship-tr" class="remove-multi-ship-tr"  onclick="thwma_remove_multi_shipping_tr(event,this)"><span class="dashicons dashicons-dismiss"></span></a>';
									    				$multi_ship_form .= '</td>';
									    				$multi_ship_form .= '<td>';
									    					$multi_ship_form .= '<input type="number" min="1" name="pdct-qty" value="'.$child_qty.'" class="multi-ship-pdct-qty pdct-qty sub-pdct-qty pdct-qty-'.$item_key.'" data-cart_key="'.$item_key.'">';
									    					$multi_ship_form .= '</td>';
									    				$multi_ship_form .= '<td>';
									    					$multi_ship_form .= '<input class="multi-ship-item" type="hidden" data-multi_ship_id="'.$multi_ship_id.'" data-multi_ship_parent_id="0" data-updated_qty="'.$child_qty.'" data-sub_row_stage="1">'.$multi_shipping_addresses;
									    					$multi_ship_form .= '<a id ="remove-multi-ship-tr" class="remove-multi-ship-tr"  onclick="thwma_remove_multi_shipping_tr(event,this)"><span class="dashicons dashicons-dismiss"></span></a>';
									    				$multi_ship_form .= '</td>';
									    			$multi_ship_form .= '</tr>';
					    						}
					    					}
					    				}
					    			} else {
					    				$parent_cart_key = isset($multi_ship_content['parent_cart_key']) ? $multi_ship_content['parent_cart_key'] : '';
					    				$child_keys = isset($multi_ship_content['child_keys']) ? $multi_ship_content['child_keys'] : array();

					    				if (!array_key_exists($parent_cart_key, $items)){
						    				if($qty == 1) {
						        				$link_show = false;
						        			} else {
						        				$link_show = true;
						        			}
						        			$multi_shipping_addresses = self::multi_shipping_addresses($value, $key, $link_show, $multi_ship_id);
											$multi_ship_form .= '<tr class="main-pdct-tr">';
							    				$multi_ship_form .= '<td class="wmap-img-tr">';
							    					$multi_ship_form .= '<div class="checkout-thumbnail-img">';
								    					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $items ) : '', $items, $key );
									    				//$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $items, $key );
									    				$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $key );

														if ( ! $product_permalink ) {
															echo $thumbnail; // PHPCS: XSS ok.
														} else {
															$multi_ship_form .= '<a href="'.esc_url( $product_permalink ).'">'.$thumbnail.'</a>';
														}
									    				//$multi_ship_form .= '<p class="product-thumb-name">'.$_product->get_name().'</p>';
									    				$multi_ship_form .= '<p class="product-thumb-name">';
															if ( ! $product_permalink ) {
																$multi_ship_form .=  wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $key ) . '&nbsp;' );
															} else {
																$multi_ship_form .=  wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $key ) );
															}

															$multi_ship_form .=  wc_get_formatted_cart_item_data( $cart_item );
														$multi_ship_form .= '</p>';
										    			//do_action( 'woocommerce_after_cart_item_name', $cart_item, $key );

									    			$multi_ship_form .= '</div>';
							    				$multi_ship_form .= '</td>';
							    				$multi_ship_form .= '<td><input type="number" min="1" name="pdct-qty" value="'.$qty.'" class="multi-ship-pdct-qty pdct-qty main-pdct-qty pdct-qty-'.$key.'" data-cart_key="'.$key.'"></td>';
							    				$multi_ship_form .= '<td><input class="multi-ship-item" type="hidden" data-multi_ship_id="multi_ship_'.$i.'" data-multi_ship_parent_id="0" data-updated_qty="'.$qty.'" data-sub_row_stage="1">'.$multi_shipping_addresses.'</td>';
							    			$multi_ship_form .= '</tr>';

							    			// Update multi ship parent id.
							    			$woocommerce->cart->cart_contents[$key]['multi_ship_address']['multi_ship_parent_id'] = '0';
					    					$woocommerce->cart->set_session();
					    					if(!empty($child_keys) && is_array($child_keys)) {
						    					foreach ( $child_keys as $item_key) {
							    					if(!empty($cart[$item_key])) {
						    							$cart_data = $cart[$item_key];
						    							$child_qty = isset($cart[$item_key]['quantity']) ? $cart[$item_key]['quantity'] : '';
						    							$multi_ship_id = $cart[$item_key]['multi_ship_address']['multi_ship_id'];
														$link_show = false;
						    							$multi_shipping_addresses = self::multi_shipping_addresses($cart_data, $item_key, $link_show, $multi_ship_id);

						    							$multi_ship_form .= '<tr>';
										    				$multi_ship_form .= '<td>';
												    			// $multi_ship_form .= '<a id ="remove-multi-ship-tr" class="remove-multi-ship-tr"  onclick="thwma_remove_multi_shipping_tr(event,this)"><span class="dashicons dashicons-dismiss"></span></a>';
										    				$multi_ship_form .= '</td>';
										    				$multi_ship_form .= '<td>';
										    					$multi_ship_form .= '<input type="number" min="1" name="pdct-qty" value="'.$child_qty.'" class="multi-ship-pdct-qty pdct-qty sub-pdct-qty pdct-qty-'.$item_key.'" data-cart_key="'.$item_key.'">';
										    					$multi_ship_form .= '</td>';
										    				$multi_ship_form .= '<td>';
										    					$multi_ship_form .= '<input class="multi-ship-item" type="hidden" data-multi_ship_id="'.$multi_ship_id.'" data-multi_ship_parent_id="0" data-updated_qty="'.$child_qty.'" data-sub_row_stage="1">'.$multi_shipping_addresses;
										    					$multi_ship_form .= '<a id ="remove-multi-ship-tr" class="remove-multi-ship-tr"  onclick="thwma_remove_multi_shipping_tr(event,this)"><span class="dashicons dashicons-dismiss"></span></a>';
										    				$multi_ship_form .= '</td>';
										    			$multi_ship_form .= '</tr>';
						    						}
						    					}
						    				}
								    	}
					    			}
			    				} else {
				    				$multi_ship_form .= '<tr class="main-pdct-tr">';
					    				$multi_ship_form .= '<td class="wmap-img-tr">';
					    					$multi_ship_form .= '<div class="checkout-thumbnail-img">';
						    					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $items ) : '', $items, $key );
							    				//$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $items, $key );
							    				$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $key );

												if ( ! $product_permalink ) {
													echo $thumbnail; // PHPCS: XSS ok.
												} else {
													$multi_ship_form .= '<a href="'.esc_url( $product_permalink ).'">'.$thumbnail.'</a>';
												}
							    				//$multi_ship_form .= '<p class="product-thumb-name">'.$_product->get_name().'</p>';
							    				$multi_ship_form .= '<p class="product-thumb-name">';
													if ( ! $product_permalink ) {
														$multi_ship_form .=  wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $key ) . '&nbsp;' );
													} else {
														$multi_ship_form .=  wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $key ) );
													}

													$multi_ship_form .=  wc_get_formatted_cart_item_data( $cart_item );
												$multi_ship_form .= '</p>';
								    			//do_action( 'woocommerce_after_cart_item_name', $cart_item, $key );

							    			$multi_ship_form .= '</div>';
					    				$multi_ship_form .= '</td>';
					    				$multi_ship_form .= '<td><input type="number" min="1" name="pdct-qty" value="'.$qty.'" class="multi-ship-pdct-qty pdct-qty main-pdct-qty pdct-qty-'.$key.'" data-cart_key="'.$key.'"></td>';
					    				$multi_ship_form .= '<td><input class="multi-ship-item" type="hidden" data-multi_ship_id="multi_ship_'.$i.'" data-multi_ship_parent_id="0" data-updated_qty="'.$qty.'" data-sub_row_stage="1">'.$multi_shipping_addresses.'</td>';
					    			$multi_ship_form .= '</tr>';
					    			$product_id = $value[ 'product_id' ];
					    			$variation_id = $value[ 'variation_id' ];
					    			$quantity = $value['quantity'];
					    			//$updated_quantity = $quantity;
					    			$parent_item_id = 0;
					    			// $woocommerce->cart->cart_contents[$key]['multi_ship_address'] = array();
					    			// $woocommerce->cart->set_session();
						    		$woocommerce->cart->cart_contents[$key]['multi_ship_address'] = array(
										'product_id' => $product_id,
										'variation_id' => $variation_id,
										'quantity' => $quantity,
										//'updated_quantity' => $updated_quantity,
										'multi_ship_id' => $multi_ship_id,
										'multi_ship_parent_id' => $parent_item_id,
										'child_keys' => array(),
										'parent_cart_key' => '',
									);
									$woocommerce->cart->set_session();
								}
								$i++;
							}
			    		}
			    		// $multi_ship_form .= '<tr>';
			    		// 	$multi_ship_form .= '<td></td><td></td>';
			    		// 	$multi_ship_form .= '<td><div class="save-multi-ship-adr-btn button alt" onclick="thwma_save_multi_ship_adr_btn(event,this)">save shipping addresses</div></td>';
			    		// $multi_ship_form .= '</tr>';
		    		$multi_ship_form .= '</table>';
		    		$multi_ship_form .= '<input type="hidden" name="multi_shipping_adr_data" class="multi-shipping-adr-data" value=""></input>';
		    	$multi_ship_form .= '</div>';
			} else {
				$multi_ship_form = self::multiple_address_management_form();
	    	}
			return $multi_ship_form;
		}

		/**
         * Delte guest user's addresses from cart page(Ajax respose -Guest users)
         */

		public function delete_guest_address_from_cart() {
			global $woocommerce;
			check_ajax_referer( 'delete-address-with-id-cart-guest', 'security' );
			$address_key = isset($_POST['selected_address_id']) ? $_POST['selected_address_id'] : '';
			$type = isset($_POST['selected_type']) ? $_POST['selected_type'] : '';
			$customer_id = get_current_user_id();
			THWMA_Utils::delete($customer_id, $type, $address_key);
			$output_shipping = self::get_tile_field_to_cart($customer_id, 'shipping');
			$expiration = self::expiration_time();
			THWMA_Utils::delete_guest_address($type, $address_key, $expiration);

			if($address_key) {
				if (isset($_COOKIE[THWMA_Utils::GUEST_USER_SHIPPING_ADDR])) {
					$shipping_address = $_COOKIE[THWMA_Utils::GUEST_USER_SHIPPING_ADDR];
					$shipping_address = preg_replace('!s:(\d+):"(.*?)";!', "'s:'.strlen('$2').':\"$2\";'", $shipping_address);
					$custom_address = unserialize(base64_decode($shipping_address));

					setcookie(THWMA_Utils::GUEST_USER_SHIPPING_ADDR, '', time()-36000, '/');

					unset($custom_address[$type][$address_key]);

					$new_custom_address = $custom_address;
				   setcookie(THWMA_Utils::GUEST_USER_SHIPPING_ADDR, base64_encode(serialize($new_custom_address)), time() + $expiration, "/");

				    $shipping_address1 = $_COOKIE[THWMA_Utils::GUEST_USER_SHIPPING_ADDR];
					$shipping_address1 = preg_replace('!s:(\d+):"(.*?)";!', "'s:'.strlen('$2').':\"$2\";'", $shipping_address1);
					$custom_address1 = unserialize(base64_decode($shipping_address1));
				}
				$output_shipping = $this->get_not_loggedin_tile_field('shipping');

				$total_address_count = THWMA_Utils::get_total_address_count();
				$total_address_count = $total_address_count - 1;


				// Address limit.
				$address_limit = '';
	            if($type) {
	                $address_limit = THWMA_Utils::get_setting_value('settings_'.$type , $type.'_address_limit');
	            }
	            if (!is_numeric($address_limit)) {
	                $address_limit = 0;
	            }

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
				$type = 'shipping';
				$address_type = "'$type'";
				$add_address_btn = '<div class="add-address thwma-add-adr '.esc_attr($disable_acnt_sec).'">
		            <button class="btn-add-address primary button '.esc_attr($disable_mngt_class).'" onclick="thwma_guest_users_add_new_shipping_address(event, this, '.esc_attr($address_type).')">
		                <i class="fa fa-plus"></i> '.esc_html__('Add new address', 'woocommerce-multiple-addresses-pro').'
		            </button>
		        </div>';

		        if($address_limit-1 == $total_address_count) {
		        	$add_new_button =  $add_address_btn;
		        } else {
		        	$add_new_button =  '';
		        }

				$response = array(
					'result_shipping' => $output_shipping,
					'address_key' => $address_key,
					'address_count' => $total_address_count,
					'add_new_button' =>  $add_new_button,
				);

				$cart = $woocommerce->cart->cart_contents;
				if(!empty($cart) && is_array($cart)) {
					foreach ($cart as $key => $item) {
						// if(isset($cart[$key]['multi_ship_address'])) {
						// 	$ship_adr_data = $cart[$key]['multi_ship_address'];
						// 	if(!empty($cart[$key]['multi_ship_address']) && is_array($cart[$key]['multi_ship_address'])) {
						// 		foreach ($cart[$key]['multi_ship_address'] as $ship_adr_key => $value) {
						// 			if($value['ship_address'] == $address_key) {
						// 		        $woocommerce->cart->cart_contents[$key]['multi_ship_address'][$ship_adr_key]['ship_address'] = '';
						// 			}
						// 		}
						// 	}
						// }
						if(isset($cart[$key]['product_shipping_address'])) {
							if($cart[$key]['product_shipping_address'] == $address_key) {
						        $woocommerce->cart->cart_contents[$key]['product_shipping_address'] = '';
							}
						}
					}
				}
				$woocommerce->cart->set_session();
				wp_send_json($response);
			}
			exit();
		}

		/*-- Multi-Shipping --*/

		/**
		 * Function for get tile addresses fields for multi-shipping addresses (checkout page).
         * Function for get tile field to cart page (cart page)
         *
         * @param integer $customer_id The user id
         * @param string $type The address type
         *
         * @return string
         */
		public static function get_tile_field_to_cart($customer_id, $type) {
			$oldcols = 1;
			$cols    = 1;
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
			//$default_address = $default_set_address ? $default_set_address : $same_address;
			$default_address = 	$same_address ? $same_address : $default_set_address;
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
			$add_class='';
			$address_type = "'$type'";
			$add_list_class  = ($type == 'billing') ? " thwma-thslider-list-ms bill " : " thwma-thslider-list-ms ship";
			$add_address_btn = '<div class="add-address thwma-add-adr '.esc_attr($disable_acnt_sec).'">
	            <button class="btn-add-address primary button '.esc_attr($disable_mngt_class).'" onclick="thwma_add_new_address(event, this, '.esc_attr($address_type).')">
	                <i class="fa fa-plus"></i> '.esc_html__('Add new address', 'woocommerce-multiple-addresses-pro').'
	            </button>
	        </div>';
	    	$add_address_btn = '<div class="add-address thwma-add-adr '.esc_attr($disable_acnt_sec).'">
	            <button class="btn-add-address primary btn-different-address '.esc_attr($disable_mngt_class).'" onclick="thwma_add_new_shipping_address(event, this, '.esc_attr($address_type).')">
	                <i class="fa fa-plus"></i> '.esc_html__('Add new address', 'woocommerce-multiple-addresses-pro').'
	            </button>
	        </div>';
	        if(is_array($custom_address)) {
	        	$all_addresses = $custom_address;
	        } else {
				$all_addresses = array();
				$def_address = THWMA_Utils::get_default_address($customer_id, $type);
				if(array_filter($def_address) && (count(array_filter($def_address)) > 2)) {
					$all_addresses ['selected_address'] = $def_address;
				}
			}
			$shipping_addresses = __("Shipping Addresses", "woocommerce-multiple-addresses-pro");
			$total_address_count = count($all_addresses);
			//if($address_limit && ($total_address_count > 1)) {
			$return_html .= '<div class="thwma-cart-modal-content">';
				$return_html .= '<div class="thwma-cart-modal-title-bar" >';
					$return_html .= '<span class="thwma-cart-modal-title" >'.$shipping_addresses.'</span>';
					//$return_html .= '<span class="thwma-cart-modal-close" onclick="thwma_close_cart_adr_list_modal(this)">&times;</span>';
					$return_html .= '<span class="thwma-cart-modal-close" onclick="thwma_close_cart_adr_list_modal(this)"><span class="dashicons dashicons-no"></span></span>';
				$return_html .= '</div>';
				$return_html .= '<div class="thwma-thslider">';
					if($address_limit && ($total_address_count >= 1)) {
						if($all_addresses && is_array($all_addresses)) {
				           	$return_html .= '<div class="thwma-thslider-box">';
					           	$return_html .= '<div class="thwma-thslider-viewport multi-'.esc_attr($type).'">';
						           	$return_html .= '<ul class=" '.esc_attr($add_list_class).'">';

						           	// Default address.
						           	$action_row_html = '';
									$new_address = !empty($default_address) &&  apply_filters('thwma_check_valid_address_key', true, $default_address) && isset($all_addresses[$default_address]) ? $all_addresses[$default_address] : '';
									$def_new_address = $new_address;
									if(!empty($def_new_address)) {
										$new_address_format = THWMA_Utils::get_formated_address($type, $new_address);
										$options_arr = THWMA_Utils::thwma_get_formatted_address($new_address_format);
										$address_key_param = "'".$default_address."'";
										$address_type_css = 'default';
										$heading = sprintf(esc_html__('Default', 'woocommerce-multiple-addresses-pro'));
										$action_row_html .= '<div class="thwma-adr-footer address-footer '.$address_type_css.'">
											<div class="th-btn btn-delete '.esc_attr($disable_mngt_class).'"><span>'.esc_html__('Delete', 'woocommerce-multiple-addresses-pro').'</span></div>
											<div class="th-btn btn-default"><span>'.esc_html__('Default', 'woocommerce-multiple-addresses-pro').'</span></div>
										</div>';
										if(isset($heading) && $heading != '') {
											$heading_css = '<div class="address-type-wrapper row">
												<div title="'.$heading.'" class="address-type '.esc_attr($address_type_css).'">'.esc_attr($heading).'</div>
											</div>
											<div class="tile-adrr-text thwma-adr-text address-text address-wrapper">'.$options_arr.'</div>';
										} else {
											$heading_css = '<div class="tile-adrr-text thwma-adr-text address-text address-wrapper wrapper-only">'.$options_arr.'</div>';
										}
										$add_class  = "thwma-thslider-item-ms $type" ;
										$return_html .= '<li class="'.esc_attr($add_class).'" value="'. esc_attr($default_address).'" >
										<div class="thwma-adr-box address-box" data-index="0" data-address-id="">
											<div class="thwma-main-content">
												<div class="complete-aaddress">
													'.$heading_css.'
												</div>
												<div class="btn-continue address-wrapper">
													<a class="th-btn button primary is-outline '.esc_attr($default_address).'" onclick="thwma_populate_selected_address(event, this, '.esc_attr($address_type).', '.esc_attr($address_key_param).')">
														<span>'.esc_html__('Choose This Address', 'woocommerce-multiple-addresses-pro').'</span>
													</a>
												</div>
											</div>'.$action_row_html.'</div></li>';
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
											$action_row_html = '';
											if($default_address) {
												$is_default = ($default_address == $address_key) ? true : false;
											} else {
												$is_default = false;
											}
											$address_type_css = '';
											if(!$is_default || empty($def_new_address)) {
												if($total_address_count>=1) {
													if(!empty($custom_address))	{
														$action_row_html .= '<div class="thwma-adr-footer address-footer">
															<div class="btn-delete '.esc_attr($disable_mngt_class).'" data-index="0" data-address-id="" onclick="thwma_delete_selected_address_cart_page(this, '.esc_attr($address_type).', '.esc_attr($address_key_param).')" title="'.esc_html__('Delete', 'woocommerce-multiple-addresses-pro').'">
																<span>'.esc_html__('Delete', 'woocommerce-multiple-addresses-pro').'</span>
															</div>
															<div class="btn-default" data-index="0" data-address-id="" onclick="thwma_set_default_address_cart_page(this, '.esc_attr($address_type).', '.esc_attr($address_key_param).')" title="'.esc_html__('Default', 'woocommerce-multiple-addresses-pro').'">
																<span>'.esc_html__('Default', 'woocommerce-multiple-addresses-pro').'</span>
															</div>
														</div>';
													}
												} else {
													// // $address_type_css = 'default';
													// // $heading = sprintf(esc_html__('Default', 'woocommerce-multiple-addresses-pro'));
													// $action_row_html .= '<div class="thwma-adr-footer address-footer '.$address_type_css.'">
													// 	<div class="th-btn btn-delete '.esc_attr($disable_mngt_class).'"><span>'.esc_html__('Delete', 'woocommerce-multiple-addresses-pro').'</span></div>
													// 	<div class="btn-default" data-index="0" data-address-id="" onclick="thwma_set_default_address_cart_page(this, '.esc_attr($address_type).', '.esc_attr($address_key_param).')" title="'.esc_html__('Default', 'woocommerce-multiple-addresses-pro').'">
													// 		<span>'.esc_html__('Default', 'woocommerce-multiple-addresses-pro').'</span>
													// 	</div>
													// </div>';
												}
												// }
												// else
												if(empty($def_new_address)) {
													if(empty($custom_address)) {
														$address_type_css = 'default';
														$heading = sprintf(esc_html__('Default', 'woocommerce-multiple-addresses-pro'));
														$action_row_html .= '<div class="thwma-adr-footer address-footer '.$address_type_css.'">
															<div class="th-btn btn-delete '.esc_attr($disable_mngt_class).'"><span>'.esc_html__('Delete', 'woocommerce-multiple-addresses-pro').'</span></div>
															<div class="th-btn btn-default"><span>'.esc_html__('Default', 'woocommerce-multiple-addresses-pro').'</span></div>
														</div>';
													}
												}
												if(isset($heading) && $heading != '') {
													$heading_css = '<div class="address-type-wrapper row">
														<div title="'.$heading.'" class="address-type '.esc_attr($address_type_css).'">'.esc_attr($heading).'</div>
													</div>
													<div class="tile-adrr-text thwma-adr-text address-text address-wrapper">'.$options_arr.'</div>';
												} else {
													$heading_css = '<div class="tile-adrr-text thwma-adr-text address-text address-wrapper wrapper-only">'.$options_arr.'</div>';
												}
												$add_class  = "thwma-thslider-item-ms $type " ;
												$add_class .= $i == 0 ? ' first' : '';
												$return_html .= '<li class="'.esc_attr($add_class).'" value="'. esc_attr($address_key).'" >
													<div class="thwma-adr-box address-box" data-index="'.esc_attr($i).'" data-address-id="">
														<div class="thwma-main-content">
															<div class="complete-aaddress">
																'.$heading_css.'
															</div>
															<div class="btn-continue address-wrapper">
																<a class="th-btn button primary is-outline '.esc_attr($address_key).'" onclick="thwma_populate_selected_address(event, this, '.esc_attr($address_type).', '.esc_attr($address_key_param).')">
																	<span>'.esc_html__('Choose This Address', 'woocommerce-multiple-addresses-pro').'</span>
																</a>
															</div>
														</div>'.$action_row_html.'</div></li>';
												// if($i >= $address_limit-1) {
		          //                                   break;
		          //                               }
												// multi-shipping tile.
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
							$return_html .= '<div class="control-buttons control-buttons-multi'.esc_attr($type).'">';
								if($address_count && $address_count > 3) {
									if($address_limit>3) {
						            	$return_html .= '<div class="prev thwma-thslider-prev multi-'.esc_attr($type).'"><i class="fa fa-angle-left fa-3x"></i></div>';
						            	$return_html .= '<div class="next thwma-thslider-next multi-'.esc_attr($type).'"><i class="fa fa-angle-right fa-3x"></i></div>';
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
	        $return_html .= '</div>';
		    //}
			return $return_html;
		}

		/**
         * Function for get saved custom addresses from db (cart page)
         *
         * @return array
         */
		public static function get_saved_custom_addresses_from_db() {
			$custom_addresses = array();
			$customer_id = get_current_user_id();
			$settings_guest_urs = THWMA_Utils::get_setting_value('settings_guest_users');
			$enabled_guest_user = isset($settings_guest_urs['enable_guest_shipping']) ? $settings_guest_urs['enable_guest_shipping']:'';
			if(is_user_logged_in()) {
				$default_ship_address = THWMA_Utils::get_custom_addresses($customer_id, 'default_shipping');
				$same_address = THWMA_Utils::is_same_address_exists($customer_id, 'shipping');
				$default_address = $default_ship_address ? $default_ship_address : $same_address;
				$custom_addresses = THWMA_Utils::get_custom_addresses($customer_id, 'shipping');
			} else {
				if($enabled_guest_user == 'yes') {
					$custom_addresses = THWMA_Utils::get_custom_addresses_of_guest_user('shipping');
				}
			}
			if(is_array($custom_addresses)) {
	        	$custom_address = $custom_addresses;
	        } else {
				$custom_address = array();
				$def_address = THWMA_Utils::get_default_address($customer_id, 'shipping');
				if(array_filter($def_address) && (count(array_filter($def_address)) > 2)) {
					$custom_address ['selected_address'] = $def_address;
				}
			}
			return $custom_address;
		}

		public function update_multi_shipping_qty_field() {
			global $woocommerce;
			check_ajax_referer( 'update-multi-shipping-qty-field', 'security' );
			$quantity = isset($_POST['value']) ? sanitize_key($_POST['value']) : '';
			$cart_item_key = isset($_POST['cart_key']) ? sanitize_key($_POST['cart_key']) : '';
			$woocommerce->cart->set_quantity($cart_item_key, $quantity);

			$settings_manage_style = THWMA_Utils::get_setting_value('settings_manage_style');
			$multi_address_url = isset($settings_manage_style['multi_address_url']) ? $settings_manage_style['multi_address_url']:'';
			if($quantity > 1) {
				$disabled_class = 'link_enabled_class';
			} else {
				$disabled_class = 'link_disabled_class';
			}
			$display_ppty = 'block';
			$cart_item_data = array();
			$items = $woocommerce->cart->get_cart();
			foreach ($items as $key => $value) {
				$cart_item_data = isset($items[$cart_item_key]) ? $items[$cart_item_key] : '';
			}
			$link_html = '';
			if(!empty($cart_item_data) && is_array($cart_item_data)) {
				foreach($cart_item_data as $key => $datas) {
					$cart_qty = isset($cart_item_data["quantity"]) ? $cart_item_data["quantity"] : '';
					$product_id = isset($cart_item_data["product_id"]) ? $cart_item_data["product_id"] : '';
					$variation_id = isset($cart_item_data["variation_id"]) ? $cart_item_data["variation_id"] : '';
				}
				$cart_item_encoded = json_encode($cart_item_data);
				$link_html .= '<a href="" class="ship_to_diff_adr add-dif-ship-adr '.$disabled_class.'" data-product_id ="'.esc_attr($product_id).'" data-cart_quantity ="'.esc_attr($cart_qty).'" data-variation_id="'.esc_attr($variation_id).'" data-cart_item ='.esc_attr($cart_item_encoded).'  data-cart_item_key = "'.esc_attr($cart_item_key).'" data-updated_qty="'.esc_attr($cart_qty).'" style="display:'.esc_attr($display_ppty).'">'.esc_html__($multi_address_url, "woocommerce-multiple-addresses-pro").'</a>';
			}
			echo $link_html;
			exit();
		}
		
		/*
         * Function for set the dropdown div content( checkout page-multi shipping).
         *
         * @param array $cart_item The cart item data
         * @param int $multi_ship_id The multi shipping is
         * @param string $cart_item_key The cart item key
         */
		public static function multi_shipping_dropdown_view($cart_item_key, $cart_item = null, $multi_ship_id=false) {
			$multi_shipping_list = null;
			$data_field_name = null;
			$customer_id = get_current_user_id();
			$default_ship_address = THWMA_Utils::get_custom_addresses($customer_id, 'default_shipping');
			$same_address = THWMA_Utils::is_same_address_exists($customer_id, 'shipping');
			$default_address = $default_ship_address ? $default_ship_address : $same_address;
			$default_address = $same_address ? $same_address : $default_ship_address;
			$address_limit = THWMA_Utils::get_setting_value('settings_shipping', 'shipping_address_limit');
            if (!is_numeric($address_limit)) {
                $address_limit = 0;
            }

			$options = array();
			$settings_guest_urs = THWMA_Utils::get_setting_value('settings_guest_users');
			$enabled_guest_user = isset($settings_guest_urs['enable_guest_shipping']) ? $settings_guest_urs['enable_guest_shipping']:'';
			if(is_user_logged_in()) {
				$custom_addresses = THWMA_Utils::get_custom_addresses($customer_id, 'shipping');
			} else {
				if($enabled_guest_user == 'yes') {
					$custom_addresses = THWMA_Utils::get_custom_addresses_of_guest_user('shipping');
				}
			}
			if(is_array($custom_addresses)) {
	        	$custom_address = $custom_addresses;
	        } else {
				$custom_address = array();
				$def_address = THWMA_Utils::get_default_address($customer_id, 'shipping');
				if(array_filter($def_address) && (count(array_filter($def_address)) > 2)) {
					$custom_address ['selected_address'] = $def_address;
				}
			}
			if($custom_address) {
				$address_count = count($custom_address);
				if($address_limit && ($address_count > 0)) {
					$shipping_heading = ( !empty($default_address) && apply_filters('thwma_check_valid_address_key', true, $default_address) && isset($custom_address[$default_address]['shipping_heading']) && $custom_address[$default_address]['shipping_heading'] !='') ? $custom_address[$default_address]['shipping_heading'] : esc_html__('', '');
					if( !empty($default_address) && apply_filters('thwma_check_valid_address_key', true, $default_address) ) {
						if(isset($options[$default_address])) {
							$options[$default_address] = $shipping_heading .' - '. $custom_address[$default_address]['shipping_address_1'];
						}
					} else {
						$default_address = 'selected_address';
						//$options[$default_address] = esc_html__('Shipping Address', 'woocommerce-multiple-addresses-pro');
					}
					if(is_array($custom_address)){

						// Default address.
						foreach ($custom_address as $key => $address_values) {
							$adrsvalues_to_dd = array();
							if($key == $default_address) {
								$heading = (isset($address_values['shipping_heading']) && $address_values['shipping_heading'] != '') ? $address_values['shipping_heading'] : esc_html__('', '');
								if(apply_filters('thwma_remove_dropdown_address_format', true)) {
									if(!empty($address_values) && is_array($address_values)) {
										foreach ($address_values as $adrs_key => $adrs_value) {
											if($adrs_key == 'shipping_address_1' || $adrs_key =='shipping_address_2' || $adrs_key =='shipping_city' || $adrs_key =='shipping_state' || $adrs_key =='shipping_postcode') {
												if($adrs_value) {
													$adrsvalues_to_dd[] = $adrs_value;
												}
											}
										}
									}
								} else {
									$type = 'shipping';
									$separator = '</br>';
									$new_address = $custom_address[$default_address];
									$new_address_format = THWMA_Utils::get_formated_address($type, $new_address);
									$options_arr = THWMA_Utils::thwma_get_formatted_address($new_address_format);
									$adrsvalues_to_dd = explode('<br/>', $options_arr);
								}
							}
							$adrs_string = implode(', ', $adrsvalues_to_dd);
							//if((isset($heading)) && ($heading!= '') && (!is_array($heading))) {
							if((isset($heading)) && ($heading != '')  && (!is_array($heading))) {
								$options[$key] = $heading .' - '.$adrs_string;
							} else {
								$options[$key] = $adrs_string;
							}
							$options = array_filter($options);
						}

						// Custom addresses.
						$i = 0;

						$no_of_tile_display = '';
						if(is_user_logged_in()) {
							$default_ship_address = THWMA_Utils::get_custom_addresses($customer_id, 'default_shipping');
							$same_address = THWMA_Utils::is_same_address_exists($customer_id, 'shipping');
							$get_default_address = $same_address ? $same_address : $default_ship_address ;
							if(!empty($get_default_address)) {
								$no_of_tile_display = 1;
							} else {
								$no_of_tile_display = 0;
							}
						}
						if(($address_limit > $no_of_tile_display) || (!is_user_logged_in())) {
							foreach ($custom_address as $key => $address_values) {
								if($key != $default_address) {
									$heading = isset($address_values['shipping_heading']) && $address_values['shipping_heading'] != '' ? $address_values['shipping_heading'] : esc_html__('', 'woocommerce-multiple-addresses-pro');
									$adrsvalues_to_dd = array();
									if(apply_filters('thwma_remove_dropdown_address_format', true)) {
										if(!empty($address_values) && is_array($address_values)) {
											foreach ($address_values as $adrs_key => $adrs_value) {
												if($adrs_key == 'shipping_address_1' || $adrs_key =='shipping_address_2' || $adrs_key =='shipping_city' || $adrs_key =='shipping_state' || $adrs_key =='shipping_postcode') {
													if($adrs_value) {
														$adrsvalues_to_dd[] = $adrs_value;
													}
												}
											}
										}
									} else {
										$type = 'shipping';
										$separator = '</br>';
										$new_address = array_key_exists($key, $custom_address) ? $custom_address[$key] : array();

										$new_address_format = THWMA_Utils::get_formated_address($type,$new_address);
										$options_arr = THWMA_Utils::thwma_get_formatted_address($new_address_format);
										$adrsvalues_to_dd = explode('<br/>', $options_arr);
									}
									$adrs_string = implode(',', $adrsvalues_to_dd);
									if((isset($heading)) && ($heading != '') && (!is_array($heading))) {
										$options[$key] = $heading .' - '.$adrs_string;
									} else {
										$options[$key] = $adrs_string;
									}

									// Multi-shipping drop-down.
									if(!is_user_logged_in()) {
										if($i >= $address_limit-1) {
			                                break;
			                            }
									} else {
										// if($i >= $address_limit-2) {
			       //                          break;
			       //                      }
			                            if(!empty($get_default_address)){
											if($i >= $address_limit-2) {
							                    break;
							                }
							            } else {
							            	if($i >= $address_limit-1) {
							                    break;
							                }
							            }
			                        }
		                            $i++;
		                        }
							}
						}
					}
				}
				// $address_count = count($custom_address);
				// $address_limit = THWMA_Utils::get_setting_value('settings_shipping', 'shipping_address_limit');
				// $disable_adr_mngmnt = self::disable_address_mnagement();
				// if($disable_adr_mngmnt != true) {
				// 	if(((int)($address_limit)) > $address_count) {
				// 		$options['add_address'] = esc_html__('Add New Address', 'woocommerce-multiple-addresses-pro');
				// 	}
				// }
			} else {
				$default_address = 'selected_address';
				if (!is_user_logged_in()) {
					$options[] = '';
				}
			}

			//$options = array_filter($options);
			$cart_shipping_class = 'cart_shipping_adr_slct';
			//$default_address = 'selected_address';
			$cart_item_data = $cart_item;
			$alt_field = array(
				'required' => false,
				'class'    => array('form-row form-row-wide enhanced_select', 'select2-selection',$cart_shipping_class),
				'clear'    => true,
				'type'     => 'select',
				'label'    => THWMA_Utils::get_setting_value('settings_shipping', 'shipping_display_text'),
				//'placeholder' =>esc_html__('Choose an Address..', ''),
				'options'  => $options
			);
			$dropdown_fields = self::thwma_shipping_dropdown_fields(self::DEFAULT_SHIPPING_ADDRESS_KEY, $alt_field,  $multi_ship_id, $cart_item_key, $default_address, $cart_item_data, $multi_shipping_list, $data_field_name);
			return $dropdown_fields;
		}

		/**
		 * Function for unserialize address.
		 *
         * @param string $str The given serialise address data
         *
         * @return array.
		 */
		public static function thwma_unserializeForm($str) {
			$returndata = array();
		    $strArray = explode("&", $str);
		    $i = 0;
		    if(!empty($strArray) && is_array($strArray)) {
			    foreach ($strArray as $item) {
			        $array = explode("=", $item);
			        $returndata[$array[0]] = urldecode($array[1]);
			    }
			}
		    return $returndata;
		}
		/**
		 * Drowpdown core function on cart page multi shipping (cart page)
         *
         * @param string $key The key value
         * @param array $args The argument data
         * @param string $value The field value
         * @param array $cart_item The cart item data
         * @param array $multi_shipping_list The shipping address list
         * @param string $data_field_name The data field name
		 *
		 * @return void.
		 */
		public static function thwma_shipping_dropdown_fields($key, $args,  $multi_ship_id, $cart_item_key, $value = null, $cart_item = null, $multi_shipping_list = null, $data_field_name = null) {
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
			$label_id        = isset($args['id']) ? $args['id'] : '';
			$sort            = isset($args['priority']) ? $args['priority'] : '';
			$field_container = '<p class="form-row %1$s" id="%2$s-multi-ship" data-priority="' . esc_attr($sort) . '">%3$s</p>';
			$field   = '';
			$options = '';
			$optgroup = '';

			// Local Pickup
			// $settings = get_option(THWMA_Utils::OPTION_KEY_THWMA_SETTINGS);
			// if(!empty($settings)) {
			// 	$store_pickup = isset($settings['settings_store_pickup'])?$settings['settings_store_pickup']:array();
			// 	if(!empty($store_pickup)) {
			// 		$local_pickup_enabled = isset($store_pickup['enable_multi_store_pickup'])?$store_pickup['enable_multi_store_pickup']:array();
			// 		if($local_pickup_enabled == 'yes') {
			// 			$store_addresses = array();
			// 			for($i = 1; $i <=2; $i++) {
			// 				$store_addresses[] =$store_pickup['store_addresses'.$i];
			// 			}
			//
			// 		}
			// 	}
			// }

			$custom_attributes = array();
			$product_id = isset($cart_item["product_id"]) ? $cart_item["product_id"] : '';
			$cart_key = isset($cart_item["key"]) ? $cart_item["key"] : '' ;
			$cart_key = $cart_item_key;

			// Address limit.
			$address_limit = THWMA_Utils::get_setting_value('settings_shipping', 'shipping_address_limit');
            if (!is_numeric($address_limit)) {
                $address_limit = 0;
            }

			if(!empty($multi_shipping_list)) {
				$exist_multi_shipping_list = 'exist_multi_shipping_list';
				$key_multi_shipping_list = '';
			} else {
				$exist_multi_shipping_list = '';
				$key_multi_shipping_list = '';
			}
			if (!empty($args['options']) && is_array($args['options']) ) {
				$new_options = array_filter($args['options']);
				foreach ($new_options as $option_key => $option_text) {
					if (empty($args['placeholder'])) {
						$args['placeholder'] = $option_text ? $option_text : esc_html__('Choose an option', 'woocommerce-multiple-addresses-pro');
					}
					$custom_attributes[] = 'data-allow_clear="true"';
					$shipping_addr = isset($cart_item['product_shipping_address']) ? $cart_item['product_shipping_address']: '';

					if(!empty($shipping_addr)) {
						$value = $shipping_addr;
					}

					if(!empty($multi_shipping_list)) {
						if(!empty($multi_shipping_list['quantity_data']) && is_array($multi_shipping_list['quantity_data'])) {
							foreach ($multi_shipping_list['quantity_data'] as $shipping_key => $shipping_value) {
								if(isset($shipping_value[$data_field_name])) {
									$value = $shipping_value[$data_field_name]['shipping_address'];
								}
							}
						}
					}

					//if($option_key != null){
						$options .= '<option value="' . esc_attr($option_key) . '" ' . selected($value, $option_key, false) . ' >' . esc_attr($option_text) . '</option>';
					//}
				}
				if($address_limit) {
					$optgroup .= '<option value="" >'.esc_html__('Select Address', 'woocommerce-multiple-addresses-pro').'</option>';
				}
				$optgroup .= $options ;

				$field .= '<select name="' . esc_attr($key) . '" id="' . esc_attr($args['id'].'_'.$product_id.'_'.$cart_key) . '" class="thwma-cart-shipping-options select ' . esc_attr(implode(' ', $args['input_class'])) . '" ' . implode(' ', $custom_attributes) . ' data-placeholder="' . esc_attr($args['placeholder']) . '" data-product_id="'.esc_attr($product_id).'" data-cart_key="'.esc_attr($cart_key).'" data-exist_multi_adr = "'.esc_attr($exist_multi_shipping_list).'" data-key_multi_adr = "'.esc_attr($key_multi_shipping_list).'">' . $optgroup . '</select>';
			}
			if (! empty($field)) {
				$field_html = '';
				$field_html .= '<span class="woocommerce-input-wrapper">' . $field;
				if ($args['description']) {
					$field_html .= '<span class="description" id="' . esc_attr($args['id']) . '-description" aria-hidden="true">' . wp_kses_post($args['description']) . '</span>';
				}
				$field_html .= '</span>';
				$container_class = esc_attr(implode(' ', $args['class']));
				$container_id    = esc_attr($args['id']) . '_field';
				$field           = sprintf($field_container, $container_class, $container_id, $field_html);
			}
			return $field;
			//echo $field; // WPCS: XSS ok.
		}
		/**
         * Function for set multi shipping drop down div( checkout page)
         *
         * @param array $cart_item The cart item data
         * @param string $cart_item_key The cart item key
         */
		public static function multi_shipping_dropdown_div($cart_item_key, $cart_item = null, $link_show=false, $multi_ship_id=false) {
			$cart_qty = '';
			$settings_manage_style = THWMA_Utils::get_setting_value('settings_manage_style');
			$multi_address_url = isset($settings_manage_style['multi_address_url']) ? $settings_manage_style['multi_address_url']:'';
			$product_id = '';
			$variation_id = '';
			if(!empty($cart_item) && is_array($cart_item)) {
				foreach($cart_item as $key => $datas) {
					$cart_qty = isset($cart_item["quantity"]) ? $cart_item["quantity"] : '';
					$product_id = isset($cart_item["product_id"]) ? $cart_item["product_id"] : '';
					$variation_id = isset($cart_item["variation_id"]) ? $cart_item["variation_id"] : '';
				}
			}
			$user_id = get_current_user_id();
	        $enable_multi_ship = '';
	        if (is_user_logged_in()) {
	        	$enable_multi_ship = get_user_meta($user_id, THWMA_Utils::USER_META_ENABLE_MULTI_SHIP, true);
	        } else {
	        	$enable_multi_ship = isset($_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP]) ? $_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP] : '';
	        }
	        if($enable_multi_ship == 'yes') {
	        	$display_ppty = 'block';
	        } else {
	        	$display_ppty = 'none';
	        }
	        $dpdwn_div = '';
			$dpdwn_div .= '<div id="thwma_cart_multi_shipping_display" class="thwma_cart_multi_shipping_display thwma_cart_multi_shipping_display_'.esc_attr($cart_item_key).'" style="display:'.esc_attr($display_ppty).'">';
				$dpdwn_div .= '<input type="hidden" name="hiden_qty_key" class="hiden_qty_key hiden_qty_key_1" value="4" data-field_name="cart['.esc_attr($cart_item_key).'][qty]_1" data-qty_hd_key="1">';
				$dpdwn_div .=  self::multi_shipping_dropdown_view($cart_item_key, $cart_item, $multi_ship_id);
			$dpdwn_div .= '</div>';
			$custom_addresses = self::get_saved_custom_addresses_from_db();
			if($custom_addresses) {
				$cart_item_encoded = json_encode($cart_item);
				$dpdwn_div .= '<input type="hidden" value="1" name="ship_to_diff_hidden" class="ship_to_diff_hidden">';

				$disabled_class = '';
				if($enable_multi_ship == 'yes') {
					if($link_show == false) {
						$disabled_class = 'link_disabled_class';
						$display_ppty = 'none';
					} else {
						$disabled_class = 'link_enabled_class';
						$display_ppty = 'block';
					}
				} else {
					$display_ppty = 'none';
				}
				$dpdwn_div .= '<a href="" class="ship_to_diff_adr add-dif-ship-adr '.$disabled_class.'" data-product_id="'.esc_attr($product_id).'" data-cart_quantity="'.esc_attr($cart_qty).'" data-variation_id="'.esc_attr($variation_id).'" data-cart_item='.esc_attr($cart_item_encoded).'  data-cart_item_key= "'.esc_attr($cart_item_key).'" data-updated_qty="'.esc_attr($cart_qty).'" style="display:'.esc_attr($display_ppty).'">'.esc_html__($multi_address_url, "woocommerce-multiple-addresses-pro").'</a>';
			} else {
				if (!is_user_logged_in()) {
					$cart_item_encoded = json_encode($cart_item);
					$dpdwn_div .= '<input type="hidden" value="1" name="ship_to_diff_hidden" class="ship_to_diff_hidden">';

					$disabled_class = '';
					if($enable_multi_ship == 'yes') {
						if($link_show == false) {
							$disabled_class = 'link_disabled_class';
							$display_ppty = 'none';
						} else {
							$disabled_class = 'link_enabled_class';
							$display_ppty = 'block';
						}
					} else {
						$display_ppty = 'none';
					}
					$dpdwn_div .= '<a href="" class="ship_to_diff_adr add-dif-ship-adr '.$disabled_class.'" data-product_id="'.esc_attr($product_id).'" data-cart_quantity="'.esc_attr($cart_qty).'" data-variation_id="'.esc_attr($variation_id).'" data-cart_item='.esc_attr($cart_item_encoded).'  data-cart_item_key= "'.esc_attr($cart_item_key).'" data-updated_qty="'.esc_attr($cart_qty).'" style="display:'.esc_attr($display_ppty).'">'.esc_html__($multi_address_url, "woocommerce-multiple-addresses-pro").'</a>';
				}
			}
			return $dpdwn_div;
		}
		/**
		 * Custom addresses displayed in dropdown format on cart page(checkout page).
		 *
         * @param array $cart_item The cart item data
         * @param string $cart_item_key The cart item key
		 */
		public static function multi_shipping_addresses($cart_item, $cart_item_key, $link_show=false, $multi_ship_id=false) {
			$ex_pdt_ids = array();
			$cart_qty = '';
			$variation_id = '';
			$product_id = '';
			if(!empty($cart_item) && is_array($cart_item)) {
				foreach($cart_item as $key => $datas) {
					$cart_qty = isset($cart_item["quantity"]) ? $cart_item["quantity"] : '';
					$product_id = isset($cart_item["product_id"]) ? $cart_item["product_id"] : '';
					$variation_id = isset($cart_item["variation_id"]) ? $cart_item["variation_id"] : '';
				}
			}
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
				$time_duration = isset($settings['set_time_duration']) ? $settings['set_time_duration']:'';
				$user_id = get_current_user_id();
		        $enable_multi_ship = '';
		        if (is_user_logged_in()) {
		        	$enable_multi_ship = get_user_meta($user_id, THWMA_Utils::USER_META_ENABLE_MULTI_SHIP, true);
		        } else {
		        	$enable_multi_ship = isset($_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP])?$_COOKIE[THWMA_Utils::GUEST_KEY_ENABLE_MULTI_SHIP]:'';
		        }
		        $multi_ship_dpdwn = '';
				if (is_user_logged_in()) {

					// Default address of logged in user.
					$user_id = get_current_user_id();
					$default_address = THWMA_Utils::get_default_address($user_id, 'shipping');
					$shipp_addr_format = THWMA_Utils::get_formated_address('shipping', $default_address);
					$default_shipp_addr = '';
					if(apply_filters('thwma_inline_address_display', true)) {
						$separator = ', ';
						$default_shipp_addr = THWMA_Utils::thwma_get_formatted_address($shipp_addr_format, $separator);
					} else {
						$default_shipp_addr = THWMA_Utils::thwma_get_formatted_address($shipp_addr_format);
					}

					if($cart_shipping_enabled == 'yes') {
						if($variation_id != '') {
							if($enable_product_variation == 'yes') {
								if(empty($ex_pdt_ids)) {
									$multi_ship_dpdwn = self::multi_shipping_dropdown_div($cart_item_key, $cart_item, $link_show, $multi_ship_id);
								} else if(!empty($ex_pdt_ids)) {
									if(!in_array($product_id, $ex_pdt_ids)) {
										$multi_ship_dpdwn = self::multi_shipping_dropdown_div($cart_item_key, $cart_item, $link_show, $multi_ship_id);
									} else {
										$multi_ship_dpdwn = '<p>'.$default_shipp_addr.'</p>';
									}
								}
							} else {
								$multi_ship_dpdwn = '<p>'.$default_shipp_addr.'</p>';
							}
						} else {
							if(empty($ex_pdt_ids)) {
								$multi_ship_dpdwn = self::multi_shipping_dropdown_div($cart_item_key, $cart_item, $link_show, $multi_ship_id);
							} else if(!empty($ex_pdt_ids)) {
								if(!in_array($product_id, $ex_pdt_ids)) {
									$multi_ship_dpdwn = self::multi_shipping_dropdown_div($cart_item_key, $cart_item, $link_show, $multi_ship_id);
								} else {
									$multi_ship_dpdwn = '<p>'.$default_shipp_addr.'</p>';
								}
							}
						}
					}
				} else {
					if($enabled_guest_user == 'yes') {
						if($cart_shipping_enabled == 'yes') {
							if($cart_shipping_enabled == 'yes') {
								if($variation_id != '') {
									if($enable_product_variation == 'yes') {
										if(empty($ex_pdt_ids)) {
											$multi_ship_dpdwn = self::multi_shipping_dropdown_div($cart_item_key, $cart_item, $link_show, $multi_ship_id);
										} else if(!empty($ex_pdt_ids)) {
											if(!in_array($product_id, $ex_pdt_ids)) {
												$multi_ship_dpdwn = self::multi_shipping_dropdown_div($cart_item_key, $cart_item, $link_show, $multi_ship_id);
											} else {
												$multi_ship_dpdwn = '<p>Multi shipping not available</p>';
											}
										}
									} else {
										$multi_ship_dpdwn = '<p>Multi shipping not available</p>';
									}
								} else {
									if(empty($ex_pdt_ids)) {
										$multi_ship_dpdwn = self::multi_shipping_dropdown_div($cart_item_key, $cart_item,  $link_show, $multi_ship_id);
									} else if(!empty($ex_pdt_ids)) {
										if(!in_array($product_id, $ex_pdt_ids)) {
											$multi_ship_dpdwn = self::multi_shipping_dropdown_div($cart_item_key, $cart_item, $link_show, $multi_ship_id);
										} else {
											$multi_ship_dpdwn = '<p>Multi shipping not available</p>';
										}
									}
								}
							}
						}
					}
				}
				return $multi_ship_dpdwn;
			}
		}

		/**
         * Function for setting multi-shipping management form( checkout page).
         *
         * @return string
         */
		public static function multiple_address_management_form() {
			global $woocommerce;
			//$items = $woocommerce->cart->get_cart();
			$items = $woocommerce->cart->cart_contents;
			$customer_id = get_current_user_id();
			$def_address = THWMA_Utils::get_default_address($customer_id, 'shipping');
    		$custom_addresses = self::get_saved_custom_addresses_from_db();
    		$multi_ship_form = '';
    		$product = __("Product", "woocommerce-multiple-addresses-pro");
    		$quantity = __("Quantity", "woocommerce-multiple-addresses-pro");
    		$sent_to = __("Sent to", "woocommerce-multiple-addresses-pro");
			if($custom_addresses) {
				$multi_ship_form .= '<div class="multi-shipping-table-wrapper">';
					$multi_ship_form .= '<div class="multi-shipping-table-overlay"></div>';
					$multi_ship_form .= '<table class="multi-shipping-table">';
		    			$multi_ship_form .= '<tr>';
		    				$multi_ship_form .= '<th>'.$product.'</th>';
		    				$multi_ship_form .= '<th>'.$quantity.'</th>';
		    				$multi_ship_form .= '<th>'.$sent_to.'</th>';
		    			$multi_ship_form .= '</tr>';
		    			$i = 1;
		        		$ini_stage = 0;
		    			foreach ($items as $key => $value) {
		    				$multi_ship_id = 'multi_ship_'.$i;
		    				$product = wc_get_product( $value[ 'product_id' ] );



		    				//$cart = $woocommerce->cart->cart_contents;
		    				$cart = $items;
		    				$qty = $value['quantity'];
							$multi_ship_parent_id = '';
					    	$child_keys = '';
					    	if($qty <= 1) {
					    		$link_show = false;
					    	} else {
					    		$link_show = true;
					    	}

					    	$cart_item = $items[$key];
					    	$cart_item = $value;
					    	$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $key );
					    	//$_product  =  $product;

					    	$multi_shipping_addresses = self::multi_shipping_addresses($value, $key, $link_show, $multi_ship_id);

					    	// Check the product is vertual or downloadable.
					    	//if ((!$product->is_virtual()) && (!$product->is_downloadable('yes'))) {
					    	$downloadable = apply_filters( 'wmap_product_is_downloadable', $product->is_downloadable('yes'));
					    	if ((!$product->is_virtual()) && (!$downloadable)) {
			    				if(!empty($cart[$key]['multi_ship_address'])) {
			    					$multi_ship_content = $cart[$key]['multi_ship_address'];
			    					if(!empty($multi_ship_content) && is_array($multi_ship_content)) {
				    					foreach ( $multi_ship_content as $multi_ship_datas) {
				    						$multi_ship_parent_id = isset($multi_ship_content['multi_ship_parent_id']) ? $multi_ship_content['multi_ship_parent_id'] : '';
				    						$child_keys = isset($multi_ship_content['child_keys']) ? $multi_ship_content['child_keys'] : '';
				    					}
				    				}
				    				//$child_keys = $child_keys_data;
				    				// $child_keys_data = $child_keys;

									if($multi_ship_parent_id == null) {
										if($qty == 1) {
					        				$link_show = false;
					        			} else {
					        				$link_show = true;
					        			}
					        			$multi_shipping_addresses = self::multi_shipping_addresses($value, $key, $link_show, $multi_ship_id);
										$multi_ship_form .= '<tr class="main-pdct-tr">';
						    				$multi_ship_form .= '<td class="wmap-img-tr">';
						    					$multi_ship_form .= '<div class="checkout-thumbnail-img">';

							    					$product_permalink = $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '';
								    				$thumbnail = $_product->get_image();

													if ( ! $product_permalink ) {
														echo $thumbnail; // PHPCS: XSS ok.
													} else {
														$multi_ship_form .= '<a href="'.esc_url( $product_permalink ).'">'.$thumbnail.'</a>';
													}
													//$multi_ship_form .= '<p class="product-thumb-name">'.$_product->get_name().'</p>';
													$multi_ship_form .= '<p class="product-thumb-name">';
														if ( ! $product_permalink ) {
															$multi_ship_form .=  wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $key ) . '&nbsp;' );
														} else {

															$multi_ship_form .=  wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $key ) );
														}
														//$multi_ship_form .= do_action( 'woocommerce_after_cart_item_name', $cart_item, $key );
														$multi_ship_form .=  wc_get_formatted_cart_item_data( $cart_item );
													$multi_ship_form .= '</p>';
													// Backorder notification.
													if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
														$multi_ship_form .=  wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) );
													}

													// $product_name = do_action( 'woocommerce_after_cart_item_name', $cart_item, $key );
								    	// 			$multi_ship_form .= '<p class="product-thumb-name">'.$product_name.'</p>';
								    				// do_action( 'woocommerce_after_cart_item_name', $cart_item, $key );
								    				//echo wc_get_formatted_cart_item_data( $items );




								    			$multi_ship_form .= '</div>';
						    				$multi_ship_form .= '</td>';
						    				$multi_ship_form .= '<td><input type="number" min="1" name="pdct-qty" value="'.$qty.'" class="multi-ship-pdct-qty pdct-qty main-pdct-qty pdct-qty-'.$key.'" data-cart_key="'.$key.'"></td>';
						    				$multi_ship_form .= '<td><input class="multi-ship-item" type="hidden" data-multi_ship_id="multi_ship_'.$i.'" data-multi_ship_parent_id="0" data-updated_qty="'.$qty.'" data-sub_row_stage="1">'.$multi_shipping_addresses.'</td>';
						    			$multi_ship_form .= '</tr>';

					    				if(!empty($child_keys) && is_array($child_keys)) {
					    					foreach ( $child_keys as $item_key) {
						    					if(!empty($cart[$item_key])) {
					    							$cart_data = $cart[$item_key];
					    							$child_qty = isset($cart[$item_key]['quantity']) ? $cart[$item_key]['quantity'] : '';
					    							$multi_ship_id = $cart[$item_key]['multi_ship_address']['multi_ship_id'];
													$link_show = false;
					    							$multi_shipping_addresses = self::multi_shipping_addresses($cart_data, $item_key, $link_show, $multi_ship_id);

					    							$multi_ship_form .= '<tr>';
									    				$multi_ship_form .= '<td>';
											    			// $multi_ship_form .= '<a id ="remove-multi-ship-tr" class="remove-multi-ship-tr"  onclick="thwma_remove_multi_shipping_tr(event,this)"><span class="dashicons dashicons-dismiss"></span></a>';
									    				$multi_ship_form .= '</td>';
									    				$multi_ship_form .= '<td>';
									    					$multi_ship_form .= '<input type="number" min="1" name="pdct-qty" value="'.$child_qty.'" class="multi-ship-pdct-qty pdct-qty sub-pdct-qty pdct-qty-'.$item_key.'" data-cart_key="'.$item_key.'">';
									    					$multi_ship_form .= '</td>';
									    				$multi_ship_form .= '<td>';
									    					$multi_ship_form .= '<input class="multi-ship-item" type="hidden" data-multi_ship_id="'.$multi_ship_id.'" data-multi_ship_parent_id="0" data-updated_qty="'.$child_qty.'" data-sub_row_stage="1">'.$multi_shipping_addresses;
									    					$multi_ship_form .= '<a id ="remove-multi-ship-tr" class="remove-multi-ship-tr"  onclick="thwma_remove_multi_shipping_tr(event,this)"><span class="dashicons dashicons-dismiss"></span></a>';
									    				$multi_ship_form .= '</td>';
									    			$multi_ship_form .= '</tr>';
					    						}
					    					}
					    				}
					    			} else {
					    				$parent_cart_key = isset($multi_ship_content['parent_cart_key']) ? $multi_ship_content['parent_cart_key'] : '';
					    				$child_keys = isset($multi_ship_content['child_keys']) ? $multi_ship_content['child_keys'] : array();

					    				if (!array_key_exists($parent_cart_key, $items)){
						    				if($qty == 1) {
						        				$link_show = false;
						        			} else {
						        				$link_show = true;
						        			}
						        			$multi_shipping_addresses = self::multi_shipping_addresses($value, $key, $link_show, $multi_ship_id);
											$multi_ship_form .= '<tr class="main-pdct-tr">';
							    				$multi_ship_form .= '<td class="wmap-img-tr">';
							    					$multi_ship_form .= '<div class="checkout-thumbnail-img">';
								    					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $items ) : '', $items, $key );

									    				//$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $items, $key );
									    				$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $key );

														if ( ! $product_permalink ) {
															echo $thumbnail; // PHPCS: XSS ok.
														} else {
															$multi_ship_form .= '<a href="'.esc_url( $product_permalink ).'">'.$thumbnail.'</a>';
														}

									    				//$multi_ship_form .= '<p class="product-thumb-name">'.$_product->get_name().'</p>';
									    				$multi_ship_form .= '<p class="product-thumb-name">';
															if ( ! $product_permalink ) {
																$multi_ship_form .=  wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $key ) . '&nbsp;' );
															} else {
																$multi_ship_form .=  wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $key ) );
															}

															$multi_ship_form .=  wc_get_formatted_cart_item_data( $cart_item );
														$multi_ship_form .= '</p>';
										    			//do_action( 'woocommerce_after_cart_item_name', $cart_item, $key );

									    			$multi_ship_form .= '</div>';
							    				$multi_ship_form .= '</td>';
							    				$multi_ship_form .= '<td><input type="number" min="1" name="pdct-qty" value="'.$qty.'" class="multi-ship-pdct-qty pdct-qty main-pdct-qty pdct-qty-'.$key.'" data-cart_key="'.$key.'"></td>';
							    				$multi_ship_form .= '<td><input class="multi-ship-item" type="hidden" data-multi_ship_id="multi_ship_'.$i.'" data-multi_ship_parent_id="0" data-updated_qty="'.$qty.'" data-sub_row_stage="1">'.$multi_shipping_addresses.'</td>';
							    			$multi_ship_form .= '</tr>';

							    			// Update multi ship parent id.
							    			$woocommerce->cart->cart_contents[$key]['multi_ship_address']['multi_ship_parent_id'] = '0';
					    					$woocommerce->cart->set_session();
					    					if(!empty($child_keys) && is_array($child_keys)) {
						    					foreach ( $child_keys as $item_key) {
							    					if(!empty($cart[$item_key])) {
						    							$cart_data = $cart[$item_key];
						    							$child_qty = isset($cart[$item_key]['quantity']) ? $cart[$item_key]['quantity'] : '';
						    							$multi_ship_id = $cart[$item_key]['multi_ship_address']['multi_ship_id'];
														$link_show = false;
						    							$multi_shipping_addresses = self::multi_shipping_addresses($cart_data, $item_key, $link_show, $multi_ship_id);

						    							$multi_ship_form .= '<tr>';
										    				$multi_ship_form .= '<td>';
												    			// $multi_ship_form .= '<a id ="remove-multi-ship-tr" class="remove-multi-ship-tr"  onclick="thwma_remove_multi_shipping_tr(event,this)"><span class="dashicons dashicons-dismiss"></span></a>';
										    				$multi_ship_form .= '</td>';
										    				$multi_ship_form .= '<td>';
										    					$multi_ship_form .= '<input type="number" min="1" name="pdct-qty" value="'.$child_qty.'" class="multi-ship-pdct-qty pdct-qty sub-pdct-qty pdct-qty-'.$item_key.'" data-cart_key="'.$item_key.'">';
										    					$multi_ship_form .= '</td>';
										    				$multi_ship_form .= '<td>';
										    					$multi_ship_form .= '<input class="multi-ship-item" type="hidden" data-multi_ship_id="'.$multi_ship_id.'" data-multi_ship_parent_id="0" data-updated_qty="'.$child_qty.'" data-sub_row_stage="1">'.$multi_shipping_addresses;
										    					$multi_ship_form .= '<a id ="remove-multi-ship-tr" class="remove-multi-ship-tr"  onclick="thwma_remove_multi_shipping_tr(event,this)"><span class="dashicons dashicons-dismiss"></span></a>';
										    				$multi_ship_form .= '</td>';
										    			$multi_ship_form .= '</tr>';
						    						}
						    					}
						    				}
								    	}
					    			}
			    				} else {
				    				$multi_ship_form .= '<tr class="main-pdct-tr">';
					    				$multi_ship_form .= '<td class="wmap-img-tr">';
					    					$multi_ship_form .= '<div class="checkout-thumbnail-img">';
						    					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $items ) : '', $items, $key );
							    				//$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $product->get_image(), $items, $key );
							    				$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $key );

												if ( ! $product_permalink ) {
													echo $thumbnail; // PHPCS: XSS ok.
												} else {
													$multi_ship_form .= '<a href="'.esc_url( $product_permalink ).'">'.$thumbnail.'</a>';
												}
							    				//$multi_ship_form .= '<p class="product-thumb-name">'.$_product->get_name().'</p>';

							    				$multi_ship_form .= '<p class="product-thumb-name">';
													if ( ! $product_permalink ) {
														$multi_ship_form .=  wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $key ) . '&nbsp;' );
													} else {
														$multi_ship_form .=  wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $key ) );
													}

													$multi_ship_form .=  wc_get_formatted_cart_item_data( $cart_item );
												$multi_ship_form .= '</p>';
								    			//do_action( 'woocommerce_after_cart_item_name', $cart_item, $key );

							    			$multi_ship_form .= '</div>';
					    				$multi_ship_form .= '</td>';
					    				$multi_ship_form .= '<td><input type="number" min="1" name="pdct-qty" value="'.$qty.'" class="multi-ship-pdct-qty pdct-qty main-pdct-qty pdct-qty-'.$key.'" data-cart_key="'.$key.'"></td>';
					    				$multi_ship_form .= '<td><input class="multi-ship-item" type="hidden" data-multi_ship_id="multi_ship_'.$i.'" data-multi_ship_parent_id="0" data-updated_qty="'.$qty.'" data-sub_row_stage="1">'.$multi_shipping_addresses.'</td>';
					    			$multi_ship_form .= '</tr>';
					    			$product_id = $value[ 'product_id' ];
					    			$variation_id = $value[ 'variation_id' ];
					    			$quantity = $value['quantity'];
					    			//$updated_quantity = $quantity;
					    			$parent_item_id = 0;
					    			// $woocommerce->cart->cart_contents[$key]['multi_ship_address'] = array();
					    			// $woocommerce->cart->set_session();
						    		$woocommerce->cart->cart_contents[$key]['multi_ship_address'] = array(
										'product_id' => $product_id,
										'variation_id' => $variation_id,
										'quantity' => $quantity,
										//'updated_quantity' => $updated_quantity,
										'multi_ship_id' => $multi_ship_id,
										'multi_ship_parent_id' => $parent_item_id,
										'child_keys' => array(),
										'parent_cart_key' => '',
									);
									$woocommerce->cart->set_session();
								}
								$i++;
							}
			    		}
			    		// $multi_ship_form .= '<tr>';
			    		// 	$multi_ship_form .= '<td></td><td></td>';
			    		// 	$multi_ship_form .= '<td><div class="save-multi-ship-adr-btn button alt" onclick="thwma_save_multi_ship_adr_btn(event,this)">save shipping addresses</div></td>';
			    		// $multi_ship_form .= '</tr>';
		    		$multi_ship_form .= '</table>';

		    		$multi_shipping = array();
		    		foreach ($items as $key => $value) {
		    			$product_id = isset($value['product_id']) ? $value['product_id'] : '';
		    			$adr_name = isset($value['product_shipping_address']) ? $value['product_shipping_address'] : '';
		    			$cart_key = isset($value['key']) ? $value['key'] : '';
		    			$multi_shipping[$cart_key] = array(
		    				'product_id' => "$product_id",
		    				'address_name' => $adr_name,
		    			);
		    		}
		    		$multi_shipping_data = $multi_shipping;
					$multi_shipping_info = json_encode($multi_shipping_data);

		    		$multi_ship_form .= '<input type="hidden" name="multi_shipping_adr_data" class="multi-shipping-adr-data" value='.$multi_shipping_info.'></input>';
		    	$multi_ship_form .= '</div>';
	    	}
			return $multi_ship_form;
		}
	}
endif;
?>