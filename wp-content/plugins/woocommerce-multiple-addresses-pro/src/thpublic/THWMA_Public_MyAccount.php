<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link			https://themehigh.com
 * @since			1.0.0
 *
 * @package			woocommerce-multiple-addresses-pro
 * @subpackage		woocommerce-multiple-addresses-pro/public
 */

namespace Themehigh\WoocommerceMultipleAddressesPro\thpublic;

use Themehigh\WoocommerceMultipleAddressesPro\includes\utils\THWMA_Utils;

if(!defined('WPINC')) {
	die;
}

if(!class_exists('THWMA_Public_MyAccount')) :

	/**
	 * public class
	 */

	class THWMA_Public_MyAccount extends THWMA_Public {
        
		public function register() {
			parent::register();
			add_action('after_setup_theme', array($this, 'define_public_hooks'));

		}
		

		/**
         * Function for enqueue public hooks.
         */

		public function define_public_hooks(){
			parent::define_public_hooks();

			add_filter('woocommerce_address_to_edit', array($this, 'change_edit_form'), 10, 2);
			add_action('woocommerce_after_save_address_validation', array($this, 'save_address'), 10, 3);

			add_action('thwma_after_address_display', array($this, 'display_custom_addresses'));
			add_action('woocommerce_before_edit_account_address_form', array($this, 'delete_address'));

			add_action('woocommerce_before_edit_account_address_form', array($this, 'set_default_billing_address'));
			add_action('woocommerce_before_edit_account_address_form', array($this, 'set_default_shipping_address'));
			

			// add_action( 'woocommerce_thankyou_order_id', array( $this, 'thwma_set_sub_order_id' ),10 );
			// add_filter('woocommerce_order_again_cart_item_data', array($this, 'thwma_filter_order_again_cart_item_data'), 10, 3);
			// add_action( 'woocommerce_order_details_after_order_table', array( $this, 'thwma_before_thankyou_account' ),10 );
		}

		/**
	     * Function for change the address edit form on my-account page.
	     *
	     * @param array $address The existing address
	     * @param string $load_address The loaded address type
	     *
	     * @return array
	     */
		public function change_edit_form($address, $load_address) {
			if($load_address) {
				if(isset($_GET['atype'])) {
					$url = $_GET['atype'];
					$type = str_replace('/', '', $url);
					if($type == 'add-address') {
						$sell_countries =  WC()->countries->get_allowed_countries();
						$sell_countries_size = count($sell_countries);
						if(!empty($address) && is_array($address)){
							foreach ($address as $key => $value) {
								$skip = ($sell_countries_size === 1 && $key == $load_address.'_country') ? true : false;
								if (!$skip) {
									$address[$key]['value']='';
								}
							}
						}
					} else {
						$address = $this->get_address_to_edit($load_address, $address, $type);
					}
				}
			}
			return $address;
		}

		/**
	     * Function for Save Address from my-account section.
	     *
	     * @param integer $user_id The user id
	     * @param string $load_address The loaded address type
	     * @param array $address The addresses
	     *
	     * @return array
	     */
		
		public function save_address($user_id, $load_address, $address) {
			$thwcfe_is_active = THWMA_Utils::check_thwcfe_plugin_is_active();
			if($thwcfe_is_active){
				$this->override_address_field_validation();
			}

			if($load_address != '') {
				if(isset($_GET['atype'])) {
					$url = isset($_GET['atype'])?sanitize_key($_GET['atype']):'';
					$type = str_replace('/', '', $url);
					if (0 === wc_notice_count('error')) {
						if($type =='add-address') {
							if($load_address == 'billing') {
								$new_address = $this->prepare_posted_address($user_id, $address, 'billing');
								THWMA_Utils::save_address_to_user($user_id, $new_address, 'billing');
							} elseif($load_address == 'shipping') {
								$custom_address=$this->prepare_posted_address($user_id, $address, 'shipping');
								THWMA_Utils::save_address_to_user($user_id, $custom_address, 'shipping');
							}
						} else {
							$this->update_address($user_id, $load_address, $address, $type);
						}

						if($type == 'add-address') {
							wc_add_notice(esc_html__('Address Added Successfully.', 'woocommerce-multiple-addresses-pro'));
						} else {
							wc_add_notice(esc_html__('Address Changed Successfully.', 'woocommerce-multiple-addresses-pro'));
						}
						$redirection_endpoint = apply_filters('thwma_add_save_address_redirection','', $load_address, $type);
						wp_safe_redirect(wc_get_endpoint_url('edit-address', $redirection_endpoint, wc_get_page_permalink('myaccount')));
	 					exit;
					}
				} else {
					$exist_address = get_user_meta($user_id,THWMA_Utils::ADDRESS_KEY, true);
					if(is_array($exist_address)) {
						if (0 === wc_notice_count('error')) {
							$default_key = THWMA_Utils::get_custom_addresses($user_id, 'default_'.$load_address);
							$same_address_key = THWMA_Utils::is_same_address_exists($user_id, $load_address);
							$address_key = ($default_key) ? $default_key : $same_address_key;

							if($address_key) {
								$this->update_address($user_id, $load_address, $address, $address_key);
							} else {

								$new_address = $this->prepare_posted_address($user_id, $address, $load_address);
								THWMA_Utils::save_address_to_user($user_id, $new_address, $load_address);
							}
						}
					}
				}
			}
		}

		/**
	     * Function for display custom addresses on my-account addresses section.
	     *
	     * @param integer $customer_id The customer id
	     */
		public function display_custom_addresses($customer_id) {
			$enable_billing = THWMA_Utils::get_setting_value('settings_billing', 'enable_billing');
			$enable_shipping = THWMA_Utils::get_setting_value('settings_shipping', 'enable_shipping');
			$custom_addresses_billing	= THWMA_Utils::get_addresses($customer_id, 'billing');
			if(is_array($custom_addresses_billing)) {
				$billing_addresses = $this->get_account_addresses($customer_id, 'billing', $custom_addresses_billing);
			}

			$custom_addresses_shipping	= THWMA_Utils::get_addresses($customer_id, 'shipping');
			if(is_array($custom_addresses_shipping)) {
				$shipping_addresses = $this->get_account_addresses($customer_id, 'shipping', $custom_addresses_shipping);
			}

			$disable_adr_mngmnt = self::disable_address_mnagement();
			if($disable_adr_mngmnt == true) {
				$disable_mngt_class = 'thwma_disable_adr_mngt';
			} else {
				$disable_mngt_class = '';
			}

			$theme_class_name = $this->check_current_theme();
			$theme_class =  $theme_class_name.'_acnt';
            $additional_billing_addresses = apply_filters('additional_billing_address_label',esc_html__('Additional billing addresses', 'woocommerce-multiple-addresses-pro'));
            $additional_shipping_addresses = apply_filters('additional_shipping_address_label',esc_html__('Additional shipping addresses','woocommerce-multiple-addresses-pro'));
			?>
			<div class= 'th-custom thwma_my_acnt <?php echo esc_attr($theme_class); ?>'>
				<?php if($enable_billing == 'yes') {
					if(empty($custom_addresses_billing) && $disable_adr_mngmnt == true) {
						$div_hide = 'thwma_hide_div';
					} else {
						$div_hide = '';
					} ?>
					<div class='thwma-acnt-cus-addr th-custom-address <?php echo esc_attr($div_hide); ?>' >
						<div class = 'th-head'><h3><?php esc_html_e($additional_billing_addresses, 'woocommerce-multiple-addresses-pro'); ?> </h3></div>
						<?php if($custom_addresses_billing) {
							echo $billing_addresses;
						} else {
							$query_arg_add_adr = add_query_arg('atype', 'add-address', wc_get_endpoint_url('edit-address', 'billing')); ?>
							<div class="add-acnt-adrs thwma-add-acnt-adrs new-adrs <?php echo esc_attr($disable_mngt_class); ?>">
		               		<?php /*<a href="<?php echo esc_url(wc_get_endpoint_url('edit-address', 'billing?atype=add-address')); ?>" class="button primary is-outline"> <i class="fa fa-plus"></i><?php  esc_html_e('Add new address', 'woocommerce-multiple-addresses-pro'); ?> </a>*/ ?>
		               		<a href="<?php echo esc_attr($query_arg_add_adr); ?>" class="button primary is-outline"> <i class="fa fa-plus"></i><?php  esc_html_e('Add new address', 'woocommerce-multiple-addresses-pro'); ?> </a>
		            		</div>
		            	<?php } ?>
					</div>
				<?php }
				if (! wc_ship_to_billing_address_only() && wc_shipping_enabled()) {
					if($enable_shipping == 'yes') {
					if(empty($custom_addresses_shipping) && $disable_adr_mngmnt == true) {
						$div_hide = 'thwma_hide_div';
					} else {
						$div_hide = '';
					} ?>
						<div class='thwma-acnt-cus-addr th-custom-address <?php echo esc_attr($div_hide); ?>'>
							<div class = 'th-head'><h3><?php esc_html_e($additional_shipping_addresses, 'woocommerce-multiple-addresses-pro'); ?> </h3></div>
							<?php if($custom_addresses_shipping) {
								 	echo $shipping_addresses;
							} else {
								$query_arg_add_adr = add_query_arg('atype', 'add-address', wc_get_endpoint_url('edit-address', 'shipping')); ?>
								<div class="add-acnt-adrs thwma-add-acnt-adrs new-adrs <?php echo esc_attr($disable_mngt_class); ?>">
			               			<a href="<?php echo esc_url_raw($query_arg_add_adr); ?>" class="button primary is-outline"> <i class="fa fa-plus"></i> <?php esc_html_e("Add new address", 'woocommerce-multiple-addresses-pro'); ?></a>

			            		</div>
			            	<?php } ?>
						</div>
					<?php }
				} ?>
			</div>
		<?php }

		/**
         * Function for Delete Address (my-account page).
         */
		public function delete_address() {
			if(isset($_POST['thwma_del_addr'])) {
				if (! isset($_POST['acnt_adr_delete_action']) || ! wp_verify_nonce($_POST['acnt_adr_delete_action'], 'thwma_account_adr_delete_action')) {
                   echo $responce = '<div class="error"><p>'.esc_html__('Sorry, your nonce did not verify.').'</p></div>';
                   exit;
                }else {
					$user_id = get_current_user_id();
					$buton_id = isset($_POST['thwma_deleteby']) ? sanitize_key($_POST['thwma_deleteby']) : '';
					$type = substr($buton_id.'_', 0, strpos($buton_id, '_'));
					$address_key = substr($buton_id, strpos($buton_id, "_") + 1);
					THWMA_Utils::delete($user_id, $type, $address_key);
				}
			}
		}

		/**
         * Function for set default billing address (my-account page).
         */
		public function set_default_billing_address() {
			$customer_id = get_current_user_id();
			if(isset($_POST['thwma_default_bil_addr'])) {
                if (! isset($_POST['bil_adr_default_action']) || ! wp_verify_nonce($_POST['bil_adr_default_action'], 'thwma_bil_adr_default_action')) {
                   echo $responce = '<div class="error"><p>'.esc_html__('Sorry, your nonce did not verify.').'</p></div>';
                   exit;
                } else {
					$address_key = isset($_POST['thwma_bil_defaultby']) ? sanitize_key($_POST['thwma_bil_defaultby']) : '';
					$address_key = str_replace(' ', '', $address_key);
					$type = substr($address_key.'_', 0, strpos($address_key, '_'));
					$custom_key = substr($address_key, strpos($address_key, "_") + 1);
					$this->change_default_address($customer_id, $type, $custom_key);
				}
			}
		}

		/**
         * Function for set default shipping address (my-account page).
         */
		public function set_default_shipping_address() {
			$customer_id = get_current_user_id();
			if(isset($_POST['thwma_default_ship_addr'])) {
				if (! isset($_POST['ship_adr_default_action']) || ! wp_verify_nonce($_POST['ship_adr_default_action'], 'thwma_ship_adr_default_action')) {
                   echo $responce = '<div class="error"><p>'.esc_html__('Sorry, your nonce did not verify.').'</p></div>';
                   exit;
                }else {
					$address_key = isset($_POST['thwma_ship_defaultby']) ? sanitize_key($_POST['thwma_ship_defaultby']) : '';
					$type = substr($address_key.'_', 0, strpos($address_key, '_'));
					$custom_key = substr($address_key, strpos($address_key, "_") + 1);
					$this->change_default_address($customer_id, $type, $custom_key);
				}
			}
		}

		/**
         * Function for get address to edit(my-account page).
         *
         * @param string $load_address The loaded adress.
         * @param array $address  The address data.
         * @param string $type  The address type.
         *
         * @return array.
         */
		public function get_address_to_edit($load_address, $address, $type) {
			$user_id = get_current_user_id();
			$custom_address = THWMA_Utils::get_custom_addresses($user_id, $load_address, $type);
			if(!empty($address) && is_array($address)) {
				foreach ($address as $key => $value) {
				 	if(isset($custom_address[$key])) {
						$address[$key]['value'] = $custom_address[$key];
						if(($key == 'shipping_state') || ($key == 'billing_state')) {
							if(!empty($custom_address) && is_array($custom_address)) {
								foreach ($custom_address as $k => $data) {
									if(($k == 'shipping_country') || ($k == 'billing_country')) {
										$address[$key]['country'] = $custom_address[$k];
									}
								}
							}
						}
					}
				}
			}
			return $address;
		}

		public function override_address_field_validation(){
			$disabled_fields = isset( $_POST['thwcfe_disabled_fields'] ) ? wc_clean( $_POST['thwcfe_disabled_fields'] ) : '';
			$dis_fields = $disabled_fields ? explode(",", $disabled_fields) : array();

			if(empty($dis_fields)){
				return;
			}

			$wc_notices = wc_get_notices();
			$wc_errors = isset($wc_notices['error']) ? $wc_notices['error'] : null;

			if(!empty($wc_errors)){
				foreach($wc_errors as $key => $wc_error){
					$id = isset($wc_error['data']['id']) ? $wc_error['data']['id'] : false;
					if(in_array($id, $dis_fields)){
						unset($wc_errors[$key]);
					}
				}

				$wc_notices['error'] = $wc_errors;
				wc_set_notices( $wc_notices );
			}
		}

		/**
	     * Function prepare for saving Address on my-account section.
	     *
	     * @param integer $user_id The user id
	     * @param string $load_address The loaded address type
	     * @param array $address The addresses
	     *
	     * @return array
	     */
		private function prepare_posted_address($user_id, $address, $type) {
			$address_new = array();
			if(!empty($address) && is_array($address)){
				foreach ($address as $key => $value) {
					if(isset($_POST[ $key ])){
						$address_value = is_array($_POST[ $key ]) ? implode(', ', sanitize_key(wc_clean($_POST[ $key ]))): wc_clean($_POST[ $key ]);
					}
					$address_new[$key] = $address_value;
				}
			}
			$default_heading = apply_filters('thwma_default_heading', false);
			if($default_heading) {
				if($type=='billing') {
					if(isset($address_new['billing_heading']) && ($address_new['billing_heading'] == '')) {
						$address_new['billing_heading'] = esc_html__('Home', 'woocommerce-multiple-addresses-pro');
					}
				} elseif($type=='shipping') {
					if(isset($address_new['shipping_heading']) && ($address_new['shipping_heading'] == '')) {
						$address_new['shipping_heading'] = esc_html__('Home', 'woocommerce-multiple-addresses-pro');
					}
				}
			}
			return $address_new;
		}

		/**
         * Function for update address(my-account page).
         *
         * @param integer $user_id The user id.
         * @param string $address_type  The type of the address.
         * @param array $address  The address data.
         * @param string $type  The address type.
         */
		public function update_address($user_id, $address_type, $address, $type) {
			$edited_address = $this->prepare_posted_address($user_id, $address, $address_type);
			THWMA_Utils::update_address_to_user($user_id, $edited_address, $address_type, $type);
		}

		/**
	     * Function for get account addresses (my-account page addresses section).
	     *
	     * @param integer $customer_id The user id
	     * @param string $type The loaded address type (billing/shipping)
	     * @param array $custom_addresses The custom addresses
	     *
	     * @return string
	     */
		public function get_account_addresses($customer_id,$type,$custom_addresses) {
			$return_html = '';
			$add_class='';
			$address_type = "'$type'";
			$address_count = count(THWMA_Utils::get_custom_addresses($customer_id, $type));

			// Address limit.
			$address_limit = '';
            if($type) {
                $address_limit = THWMA_Utils::get_setting_value('settings_'.$type , $type.'_address_limit');
            }
            if (!is_numeric($address_limit)) {
                $address_limit = 0;
            }
			$disable_adr_mngmnt = self::disable_address_mnagement();
			if($disable_adr_mngmnt == true) {
				$disable_mngt_class = 'thwma_disable_adr_mngt';
				$disable_acnt_sec = 'disable_acnt_sec';
			} else {
				$disable_mngt_class = '';
				$disable_acnt_sec = '';
			}

			$default_set_address = THWMA_Utils::get_custom_addresses($customer_id, 'default_'.$type);
			$same_address = THWMA_Utils::is_same_address_exists($customer_id, $type);
			//$default_address = $default_set_address ? $default_set_address : $same_address;
			$default_address = 	$same_address ? $same_address : $default_set_address;

			$no_of_tile_display = '';
			if(!empty($default_address)) {
				$no_of_tile_display = 1;
			} else {
				$no_of_tile_display = 0;
			}

			if(apply_filters('add_default_address_to_slider', false)) {
				if($default_address) {
					$no_of_tile_display = 0;
					$def_adr = array($type.'?atype='.$default_address => 'default');
					$custom_addresses = array_merge($custom_addresses, $def_adr);
				}
			}

			if($address_limit > $no_of_tile_display) {
                if(!empty($custom_addresses)) {
					if(is_array($custom_addresses)) {
						$add_list_class  = ($type == 'billing') ? " thwma-thslider-list bill " : " thwma-thslider-list ship";
						$return_html .= '<div class="thwma-thslider">';
				           	$return_html .= '<div class="thwma-thslider-box">';
					           	$return_html .= '<div class="thwma-thslider-viewport '.esc_attr($type).'">';
						           	$return_html .= '<ul id="thwma-th-list" class="'.esc_attr($add_list_class).'">';
										$i = 0;
										if(!empty($custom_addresses)) {
											foreach ($custom_addresses as $name => $title) {
												$default_heading = apply_filters('thwma_default_heading', false);
												if($default_heading) {
													$heading = !empty($title) ? $title : esc_html__('Home', 'woocommerce-multiple-addresses-pro') ;
												} else {
													$heading = !empty($title) ? $title : esc_html__('', 'woocommerce-multiple-addresses-pro') ;
												}

												$address = THWMA_Utils::get_all_addresses($customer_id,$name);
												$address_key = substr($name, strpos($name,"=") + 1);
												$action_row_html = '';
												$action_def_html = '';
												$delete_msg = esc_html__('Are you sure you want to delete this address?', 'woocommerce-multiple-addresses-pro');
												//$confirm = "return confirm(". $delete_msg .");";
												$str_arr = preg_split ("/\?/", $name);
												$str_arr1 = $str_arr[0];
												$str_arr2 = $str_arr[1];
												$str_arr_sec = preg_split ("/\=/", $str_arr2);
												$str_arr_sec1 = $str_arr_sec[0];
												$str_arr_sec2 = $str_arr_sec[1];
												$query_arg = add_query_arg($str_arr_sec1, $str_arr_sec2, wc_get_endpoint_url('edit-address', $str_arr1));
												$action_row_html .= '<div class="thwma-acnt-adr-footer acnt-address-footer '.$disable_acnt_sec.'">';
													$action_row_html .= '<div class="btn-acnt-edit '.esc_attr($disable_mngt_class).'"> <a href=" '.esc_url_raw($query_arg).'" class="" title="'.esc_html__('Edit', 'woocommerce-multiple-addresses-pro').'"> <span> '.esc_html__('Edit', 'woocommerce-multiple-addresses-pro').' </span> </a></div>';
													$action_row_html .= '<form action="" method="post" name="thwma_account_adr_delete_action">';
													 $action_row_html.=  '<input type="hidden" name="acnt_adr_delete_action" value="'.wp_create_nonce('thwma_account_adr_delete_action').'"> ';

														$action_row_html .=' <button type="submit" name="thwma_del_addr"  class="thwma-del-acnt th-del-acnt '.esc_attr($disable_mngt_class).'" title="'.esc_html__('Delete', 'woocommerce-multiple-addresses-pro').'"  onclick="return confirm(\''. $delete_msg .'\');">'.esc_html__('Delete', 'woocommerce-multiple-addresses-pro').'</button>';
														$action_row_html .=	'<input type="hidden" name="thwma_deleteby" value="'.esc_attr($type).'_'. esc_attr($address_key).'"/>';
													$action_row_html .= '</form>';
												$action_row_html .= '</div>';
												if($type == "billing") {
													$action_def_html.=  ' <form action="" method="post" name="thwma_bil_adr_default_action">';
													$action_def_html.=  '<button type="submit" name="thwma_default_bil_addr" id="submit-billing" class="primary btn-different-address account-default thwma-acnt-dflt"  >'.esc_html__('Set as default', 'woocommerce-multiple-addresses-pro').' </button>
													<input type="hidden" name="thwma_bil_defaultby" value="'.esc_attr($type
													).'_'. esc_attr($address_key).'"/>';

													$action_def_html.=  '<input type="hidden" name="bil_adr_default_action" value="'.wp_create_nonce('thwma_bil_adr_default_action').'"> ';
													$action_def_html.=  '</form>';
												} else {
													$action_def_html.= '<form action="" method="post" name="thwma_ship_adr_default_action">';

													$action_def_html.=  '<button type="submit" name="thwma_default_ship_addr" id="submit-shipping" class="primary btn-different-address account-default thwma-acnt-dflt" >'.esc_html__('Set as default', 'woocommerce-multiple-addresses-pro').'</button>
													<input type="hidden" name="thwma_ship_defaultby" value="'.esc_attr($type).'_'. esc_attr($address_key).'"/>';

													$action_def_html.=  '<input type="hidden" name="ship_adr_default_action" value="'.wp_create_nonce('thwma_ship_adr_default_action').'"> ';
													$action_def_html.= '</form>';
												}
												$query_arg_add_adr = add_query_arg('atype', 'add-address', wc_get_endpoint_url('edit-address', $type));
									            $add_address_btn = '<div class="add-acnt-adrs thwma-add-acnt-adrs '.esc_attr($disable_mngt_class).' '.esc_attr($disable_acnt_sec).'">
									               	<a href=" '.$query_arg_add_adr.'" class="button primary is-outline" >
									                	<i class="fa fa-plus"></i> '.esc_html__('Add new address', 'woocommerce-multiple-addresses-pro').' </a>
									            </div>';
												$add_class  = "thwma-thslider-item $type " ;
												$add_class .= $i == 0 ? ' first' : '';

												if(isset($heading) && $heading != '') {
													$show_heading = '<div class="address-type-wrapper row">
														<div title="'.esc_attr($heading).'" class="address-type">'.esc_attr($heading).'</div>
														</div>
														<div class="acnt-adrr-text thwma-adr-text address-text address-wrapper">'.$address.'</div>' ;
												} else {
													$show_heading = '<div class="acnt-adrr-text thwma-adr-text address-text address-wrapper wrapper-only">'.$address.'</div>';
												}
												$return_html .= '<li class="'.esc_attr($add_class).'" value="'. esc_attr($address_key).'" >
													<div class="thwma-adr-box address-box" data-index="'.esc_attr($i).'" data-address-id="">
														<div class="thwma-main-content">
															<div class="complete-aaddress">
																'.$show_heading.'
															</div>
															<div class="btn-continue address-wrapper">
																'.$action_def_html.'
															</div>
														</div>
															'.$action_row_html.'
													</div>
												</li>';
												// if($i >= $address_limit-2) {
		                                        //     break;
		                                        // }

		                                        // My-account tiles.
		                                        if($default_address) {
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
									$return_html .= '</ul>';
								$return_html .= '</div>';
							$return_html .= '</div>';
							$return_html .= '<div class="control-buttons control-buttons-'.esc_attr($type).'">';
								$return_html .= '<input type="hidden" value="'.esc_attr($address_count).'" class="get_addr_count">';
								if($address_count) {
									if($default_address) {
										$slider_limit = 3;
									} else {
										$slider_limit = 2;
									}
									//if($address_limit > $slider_limit) {
							  			$return_html .= '<div class="prev thwma-thslider-prev '.esc_attr($type).'"><i class="fa fa-angle-left fa-3x"></i></div>';
							  			$return_html .= '<div class="next thwma-thslider-next '.esc_attr($type).'"><i class="fa fa-angle-right fa-3x"></i></div>';
							  		//}
								}
			           		$return_html .= '</div>';
				           	if($address_limit > $address_count) {

				           		$return_html .= $add_address_btn;
				           	}
			           	$return_html .= '</div>';
					}
				}
			}
			return $return_html;
		}
	}

endif;
