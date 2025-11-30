<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Class Woo_Wholesale_Registration_Page_Setting
 */
if ( ! class_exists( 'Wwp_Wholesale_Registration_Page_Setting' ) ) {

	class Wwp_Wholesale_Registration_Page_Setting {

		public function __construct() {
			$settings = get_option( 'wwp_wholesale_pricing_options', true );
				$advance_registration_form = isset($settings['advance_registration_form']) ? sanitize_text_field($settings['advance_registration_form']) : 'no';
			if ( 'no' == $advance_registration_form ) {
				add_action( 'admin_menu', array( $this, 'wwp_registration_page_setting' ) );
			}
			// enable to display tax id in billing address
			add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'wwp_tax_display_admin_order_meta' ), 10, 1 );
			add_action( 'admin_init', array( $this, 'enqueue_front_scripts' ) );

			add_action( 'wp_ajax_wwp_save_form', array( $this, 'ajax_call_wwp_save_form' ) );
			add_action( 'wp_ajax_nopriv_wwp_save_form', array( $this, 'ajax_call_wwp_save_form' ) );
		}

		public function ajax_call_wwp_save_form() {

			if ( wwp_get_post_data( 'formData' ) ) {
				update_option('wwp_save_form', strip_tags( wp_specialchars_decode( stripslashes( wwp_get_post_data('formData') ) ) ) );
				die( 'save' );
			}
		}

		public function enqueue_front_scripts() {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'wwp_formbuilder', plugin_dir_url( __DIR__ ) . 'assets/js/formbuilder/form-builder.min.js', array(), '1.0.0' );
			wp_enqueue_script( 'wwp_formrender', plugin_dir_url( __DIR__ ) . 'assets/js/formbuilder/form-render.min.js', array(), '1.0.0' );
		}

		public function wwp_tax_display_admin_order_meta( $order ) {

			$registrations = get_option( 'wwp_wholesale_registration_options' );
			if ( isset( $registrations['tax_id_display'] ) && 'yes' == $registrations['tax_id_display'] ) {
				$wholesaler_tax_id = esc_html__( 'Wholesaler Tax ID ', 'woocommerce-wholesale-pricing' );
				echo wp_kses( '<p><strong> ' . $wholesaler_tax_id . ':</strong> <br/>' . get_user_meta( $order->get_user_id(), 'wwp_wholesaler_tax_id', true ) . '</p>', shapeSpace_allowed_html() );
			}
		}

		public function wwp_registration_page_setting() {
		
			/**
			* Hooks
			*
			* @since 3.0
			*/
			$check = apply_filters( 'wwp_wholesales_menus', true );
			if ($check) {
				add_submenu_page( 'wwp_wholesale', esc_html__( 'Registration Page', 'woocommerce-wholesale-pricing' ), esc_html__( 'Registration Setting', 'woocommerce-wholesale-pricing' ), 'manage_wholesale_registration_page', 'wwp-registration-setting', array( $this, 'wwp_wholesale_registration_page_callback' ) );
			}
		}

		public static function wwp_wholesale_registration_page_callback() {
			$registrations = get_option( 'wwp_wholesale_registration_options' );
			if ( isset( $_POST['save_wwp_registration_setting'] ) ) {
				if ( isset( $_POST['wwp_wholesale_settings_nonce'] ) && wp_verify_nonce( wc_clean( $_POST['wwp_wholesale_settings_nonce'] ), 'wwp_wholesale_settings_nonce' ) ) {
					$registrations = isset( $_POST['registrations'] ) ? wc_clean( $_POST['registrations'] ) : '';
					update_option( 'wwp_wholesale_registration_options', $registrations );
				} else {
					wp_die( esc_html__( 'Security check', 'wholesale-for-woocommerce' ) );
				}
			}
			if ( isset( $registrations['enable_recaptcha'] ) && 'yes' == $registrations['enable_recaptcha'] ) {
				$version = isset( $registrations['recaptcha_version'] ) ? $registrations['recaptcha_version'] : 'v2';
				if ( empty( $registrations['recaptcha_' . $version . '_site_key'] ) ) {
					printf( '<div style="margin: 5px 0px 0px;" class="notice notice-error"><p>%s</p></div>', esc_html__( 'Wholesale Registration Form: reCAPTCHA ' . esc_attr( ucfirst( $version ) ) . ' Site Key is missing.' , 'woocommerce-wholesale-pricing' ) );
				}

				if ( empty( $registrations['recaptcha_' . $version . '_secret_key'] ) ) {
					printf( '<div style="margin: 5px 0px 0px;" class="notice notice-error"><p>%s</p></div>', esc_html__( 'Wholesale Registration Form: reCAPTCHA ' . esc_attr( ucfirst( $version ) ) . ' Secret Key is missing.' , 'woocommerce-wholesale-pricing' ) );
				}
				
			}
			?><div id="screen_fix"></div>
			<div id="registration_form_settings">

			<div class="tab" role="tabpanel">
				<ul class="nav nav-tabs" role="tablist">
					<li role="presentation">
						<a href="<?php echo esc_html_e( wholesale_tab_link( '' ) ); ?>" class="nav-tab <?php echo esc_html_e( wholesale_tab_active( '' ) ); ?>" data-tab="wholesale-general-settings">
							<?php esc_html_e( 'General Settings', 'woocommerce-wholesale-pricing' ); ?>
						</a>
					</li>
					<li role="presentation">
						<a href="<?php echo esc_html_e( wholesale_tab_link( 'default-fields' ) ); ?>" class="nav-tab <?php echo esc_html_e( wholesale_tab_active( 'default-fields' ) ); ?>" data-tab="wholesale-default-settings">
							<?php esc_html_e( 'Default Fields', 'woocommerce-wholesale-pricing' ); ?>
						</a>
					</li>
					<li role="presentation">
						<a href="<?php echo esc_html_e( wholesale_tab_link( 'extra-fields' ) ); ?>" class="nav-tab <?php echo esc_html_e( wholesale_tab_active( 'extra-fields' ) ); ?>" data-tab="wholesale-extra-settings">
							<?php esc_html_e( 'Extra Fields', 'woocommerce-wholesale-pricing' ); ?>
						</a>
					</li>
					<li role="presentation">
						<a href="<?php echo esc_html_e( wholesale_tab_link( 'recaptcha' ) ); ?>" class="nav-tab <?php echo esc_html_e( wholesale_tab_active( 'recaptcha' ) ); ?>" data-tab="wholesale-recaptcha-settings">
							<?php esc_html_e( 'reCaptcha', 'woocommerce-wholesale-pricing' ); ?>
						</a>
					</li>
				</ul>
			</div>
			<?php 
			$curr_page  = remove_query_arg( 'page' );
			$temp       = explode( 'tab=', $curr_page );
			if (  'extra-fields' == end( $temp ) ) : 
				?>
				<div id="setting-error-wwp-formbuilder-notice" class="notice notice-warning settings-error"> 
					<p>
						<strong><?php echo esc_html__( 'You are requested not to add in-line CSS or HTML code in the form builder fields. Instead, you can add additional CSS in Wholesale Settings(Sub Menu) &#8594; Additional CSS(Tab).', 'woocommerce-wholesale-pricing' ); ?></strong>
					</p>
				</div>
			<?php endif; ?>
			<?php if ( wholesale_load_form_builder() ) { ?>
				
				<form action="" method="post" style="margin:0">
					<?php wp_nonce_field( 'wwp_wholesale_settings_nonce', 'wwp_wholesale_settings_nonce' ); ?>
					
					<table class="general-table form-table" style="display: <?php echo esc_html_e( wholesale_content_tab_active( '' ) ); ?>">
						<tbody>
							<tr scope="row">
								<th><label for=""><?php esc_html_e( 'Enable Billing Address form Default Fields', 'woocommerce-wholesale-pricing' ); ?></label></th>
								<td>
									<p>
										<label for="custommer_billing_address" class="switch">
										<?php
											$checked = '';
										if ( ! isset( $registrations ) || empty( $registrations ) ) {
											$checked = 'checked';
										} elseif ( isset( $registrations['custommer_billing_address'] ) && 'yes' == $registrations['custommer_billing_address'] ) {
											$checked = 'checked';
										} else {
											$checked = '';
										}
										?>
											<input id="custommer_billing_address" type="checkbox"  value="yes" name="registrations[custommer_billing_address]" <?php echo esc_html( $checked ); ?> >
											<span class="slider round"></span>
										</label>
										<span data-tip="<?php esc_html_e( 'Enabling this option will allow default WooCommerce billing address field to appear on the front-end form.', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
									</p>
								</td>
							</tr>
							<tr scope="row">
								<th><label><?php esc_html_e( 'Enable Shipping Address form Default Fields', 'woocommerce-wholesale-pricing' ); ?></label></th>
								<td>
									<p>
										<?php
										$checked = '';
										if ( ! isset( $registrations ) || empty( $registrations ) ) {
											$checked = 'checked';
										} elseif ( isset( $registrations['custommer_shipping_address'] ) && 'yes' == $registrations['custommer_shipping_address'] ) {
											$checked = 'checked';
										} else {
											$checked = '';
										}
										?>
										<label for="custommer_shipping_address" class="switch">
											<input id="custommer_shipping_address" type="checkbox" value="yes" name="registrations[custommer_shipping_address]" <?php echo esc_html( $checked ); ?>>
											<span class="slider round"></span>
										</label>
										<span data-tip="<?php esc_html_e( 'Enabling this option will allow default WooCommerce shipping address field to appear on the front-end form.', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
									</p>
								</td>
							</tr>
							<tr scope="row">
								<th><label><?php esc_html_e( 'Display Extra Fields on Registration', 'woocommerce-wholesale-pricing' ); ?></label></th>
								<td>
									<p>
										<?php
										$checked = '';
										if ( ! isset( $registrations ) || empty( $registrations ) ) {
											$checked = 'checked';
										} elseif ( isset( $registrations['display_fields_registration'] ) && 'yes' == $registrations['display_fields_registration'] ) {
											$checked = 'checked';
										} else {
											$checked = '';
										}
										?>
										<label for="display_fields_registration" class="switch">
											<input id="display_fields_registration" type="checkbox" value="yes" name="registrations[display_fields_registration]" <?php echo esc_html( $checked ); ?>>
											<span class="slider round"></span>
										</label>
										<span data-tip="<?php esc_html_e( 'Enable this option to allow the dynamic form builder to display extra fields on the registration page.', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
									</p>
								</td>
							</tr>
							<tr scope="row">
								<th><label><?php esc_html_e( 'Display Extra Fields on  My account', 'woocommerce-wholesale-pricing' ); ?></label></th>
								<td>
									<p>
										<?php
										$checked = '';
										if ( ! isset( $registrations ) || empty( $registrations ) ) {
											$checked = 'checked';
										} elseif ( isset( $registrations['display_fields_myaccount'] ) && 'yes' == $registrations['display_fields_myaccount'] ) {
											$checked = 'checked';
										} else {
											$checked = '';
										}
										?>
										<label for="display_fields_myaccount" class="switch">
											<input id="display_fields_myaccount" type="checkbox" value="yes" name="registrations[display_fields_myaccount]" <?php echo esc_html( $checked ); ?>>
											<span class="slider round"></span>
										</label>
										<span data-tip="<?php esc_html_e( 'Enable this option to allow the dynamic form builder to display extra fields on the my account page.', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
									</p>
								</td>
							</tr>
							<tr scope="row">
								<th><label><?php esc_html_e( 'Display Extra Fields on Checkout', 'woocommerce-wholesale-pricing' ); ?></label></th>
								<td>
									<p>
										<?php
										$checked = '';
										if ( ! isset( $registrations ) || empty( $registrations ) ) {
											$checked = 'checked';
										} elseif ( isset( $registrations['display_fields_checkout'] ) && 'yes' == $registrations['display_fields_checkout'] ) {
											$checked = 'checked';
										} else {
											$checked = '';
										}
										?>
										<label for="display_fields_checkout" class="switch">
											<input id="display_fields_checkout" type="checkbox" value="yes" name="registrations[display_fields_checkout]" <?php echo esc_html( $checked ); ?>>
											<span class="slider round"></span>
										</label>
										<span data-tip="<?php esc_html_e( 'Enable this option to allow the dynamic form builder to display extra fields on the checkout page.', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
									</p>
								</td>
							</tr>
						</tbody>
					</table>
					
					<div id="billing_address_fields" style="display:<?php echo esc_html_e( wholesale_content_tab_active( 'default-fields' ) ); ?>">
						
						<div id="accordion_billing">
							<div class="card">
								<button onclick="return false;" class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse_billing" aria-expanded="false" aria-controls="collapse_billing"><?php esc_html_e( 'Billing Address form Fields', 'woocommerce-wholesale-pricing' ); ?></button>
								<div id="collapse_billing" class="collapse" data-parent="#accordion_billing" >
									<div class="card-body">
										<table class="form-table">
											<tbody>
												<tr scope="row">
													<th><label for=""><?php esc_html_e( 'Billing First Name', 'woocommerce-wholesale-pricing' ); ?></label></th>
													<td>
														<p>
															<input type="text" id="billing_first_name" placeholder="Custom label" name="registrations[billing_first_name]" value="<?php echo isset( $registrations['billing_first_name'] ) ? esc_attr( $registrations['billing_first_name'] ) : ''; ?>" >
														</p>	
														<div class="wwp-tags">
															<?php esc_html_e( ' [ default label : "First Name" ]', 'woocommerce-wholesale-pricing' ); ?>
														</div>		
														<div class="sep10px">&nbsp;</div>
														<p>
															<input class="inp-cbx" style="display: none" type="checkbox" id="enable_billing_first_name" name="registrations[enable_billing_first_name]" value="yes" <?php echo ( isset( $registrations['enable_billing_first_name'] ) && 'yes' == $registrations['enable_billing_first_name'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="enable_billing_first_name">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Enable', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
															<input class="inp-cbx" style="display: none" type="checkbox" id="required_billing_first_name" name="registrations[required_billing_first_name]" value="yes" <?php echo ( isset( $registrations['required_billing_first_name'] ) && 'yes' == $registrations['required_billing_first_name'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="required_billing_first_name">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Required', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
														</p>
													</td>
												</tr>
												<tr scope="row">
													<th><label for=""><?php esc_html_e( 'Billing Last Name', 'woocommerce-wholesale-pricing' ); ?></label></th>
													<td>														
														<p>
															<input type="text" id="billing_last_name" placeholder="Custom label" name="registrations[billing_last_name]" value="<?php echo isset( $registrations['billing_last_name'] ) ? esc_attr( $registrations['billing_last_name'] ) : ''; ?>">
														</p>
														<div class="wwp-tags">
															<?php esc_html_e( ' [ default label : "Last Name" ] ', 'woocommerce-wholesale-pricing' ); ?>
														</div>
														<div class="sep10px">&nbsp;</div>											
														<p>
															<input class="inp-cbx" style="display: none" type="checkbox" id="enable_billing_last_name" name="registrations[enable_billing_last_name]" value="yes" <?php echo ( isset( $registrations['enable_billing_last_name'] ) && 'yes' == $registrations['enable_billing_last_name'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="enable_billing_last_name">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Enable', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
															<input class="inp-cbx" style="display: none" type="checkbox" id="required_billing_last_name" name="registrations[required_billing_last_name]" value="yes" <?php echo ( isset( $registrations['required_billing_last_name'] ) && 'yes' == $registrations['required_billing_last_name'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="required_billing_last_name">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Required', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
														</p>
													</td>
												</tr>
												<tr scope="row" >
													<th><label for=""><?php esc_html_e( 'Company', 'woocommerce-wholesale-pricing' ); ?></label></th>
													<td>														
														<p>
															<input type="text" id="billing_company" placeholder="Custom label" name="registrations[billing_company]" value="<?php echo isset( $registrations['billing_company'] ) ? esc_attr( $registrations['billing_company'] ) : ''; ?>" >
														</p>
														<div class="wwp-tags">
															<?php esc_html_e( ' [ default label : "Company" ] ', 'woocommerce-wholesale-pricing' ); ?>
														</div>												
														<div class="sep10px">&nbsp;</div>
														<p>
															<input class="inp-cbx" style="display: none" type="checkbox" id="enable_billing_company" name="registrations[enable_billing_company]" value="yes" <?php echo ( isset( $registrations['enable_billing_company'] ) && 'yes' == $registrations['enable_billing_company'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="enable_billing_company">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Enable', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
															<input class="inp-cbx" style="display: none" type="checkbox" id="required_billing_company" name="registrations[required_billing_company]" value="yes" <?php echo ( isset( $registrations['required_billing_company'] ) && 'yes' == $registrations['required_billing_company'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="required_billing_company">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Required', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
														</p>
													</td>
												</tr>
												<tr scope="row" >
													<th><label for=""><?php esc_html_e( 'Address line 1 ', 'woocommerce-wholesale-pricing' ); ?></label></th>
													<td>													
														<p>
															<input type="text" id="billing_address_1" placeholder="Custom label" name="registrations[billing_address_1]" value="<?php echo isset( $registrations['billing_address_1'] ) ? esc_attr( $registrations['billing_address_1'] ) : ''; ?>">
														</p>
														<div class="wwp-tags">
															<?php esc_html_e( ' [ default label : "Address line 1" ] ', 'woocommerce-wholesale-pricing' ); ?>
														</div>														
														<div class="sep10px">&nbsp;</div>
														<p>
															<input class="inp-cbx" style="display: none" type="checkbox" id="enable_billing_address_1" name="registrations[enable_billing_address_1]" value="yes"  <?php echo ( isset( $registrations['enable_billing_address_1'] ) && 'yes' == $registrations['enable_billing_address_1'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="enable_billing_address_1">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Enable', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
															<input class="inp-cbx" style="display: none" type="checkbox" id="required_billing_address_1" name="registrations[required_billing_address_1]" value="yes"  <?php echo ( isset( $registrations['required_billing_address_1'] ) && 'yes' == $registrations['required_billing_address_1'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="required_billing_address_1">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Required', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
														</p>
													</td>
												</tr>
												<tr scope="row" >
													<th><label for=""><?php esc_html_e( 'Address line 2 ', 'woocommerce-wholesale-pricing' ); ?></label></th>
													<td>
														<p>
															<input type="text" id="billing_address_2" placeholder="Custom label" name="registrations[billing_address_2]" value="<?php echo isset( $registrations['billing_address_2'] ) ? esc_attr( $registrations['billing_address_2'] ) : ''; ?>" >
														</p>
														<div class="wwp-tags">
															<?php esc_html_e( '  [ default label : "Address line 2" ]', 'woocommerce-wholesale-pricing' ); ?>
														</div>
														<div class="sep10px">&nbsp;</div>
														<p>
															<input class="inp-cbx" style="display: none" type="checkbox" id="enable_billing_address_2" name="registrations[enable_billing_address_2]" value="yes" <?php echo ( isset( $registrations['enable_billing_address_2'] ) && 'yes' == $registrations['enable_billing_address_2'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="enable_billing_address_2">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Enable', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
															<input class="inp-cbx" style="display: none" type="checkbox" id="required_billing_address_2" name="registrations[required_billing_address_2]" value="yes" <?php echo ( isset( $registrations['required_billing_address_2'] ) && 'yes' == $registrations['required_billing_address_2'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="required_billing_address_2">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Required', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
														</p>
													</td>
												</tr>
												<tr scope="row" >
													<th><label for=""><?php esc_html_e( 'City ', 'woocommerce-wholesale-pricing' ); ?></label></th>
													<td>														
														<p>
															<input type="text" id="billing_city" placeholder="Custom label" name="registrations[billing_city]" value="<?php echo isset( $registrations['billing_city'] ) ? esc_attr( $registrations['billing_city'] ) : ''; ?>"  >															
														</p>
														<div class="wwp-tags">
															<?php esc_html_e( '  [ default label : "City" ]', 'woocommerce-wholesale-pricing' ); ?>
														</div>
														<div class="sep10px">&nbsp;</div>
														<p>
															<input class="inp-cbx" style="display: none" type="checkbox" id="enable_billing_city" name="registrations[enable_billing_city]" value="yes" <?php echo ( isset( $registrations['enable_billing_city'] ) && 'yes' == $registrations['enable_billing_city'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="enable_billing_city">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Enable', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
															<input class="inp-cbx" style="display: none" type="checkbox" id="required_billing_city" name="registrations[required_billing_city]" value="yes" <?php echo ( isset( $registrations['required_billing_city'] ) && 'yes' == $registrations['required_billing_city'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="required_billing_city">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Required', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
														</p>
													</td>
												</tr>
												<tr scope="row" >
													<th><label for=""><?php esc_html_e( 'Postcode / ZIP ', 'woocommerce-wholesale-pricing' ); ?></label></th>
													<td>
														<p>
															<input type="text" id="billing_post_code"  placeholder="Custom label" name="registrations[billing_post_code]" value="<?php echo isset( $registrations['billing_post_code'] ) ? esc_attr( $registrations['billing_post_code'] ) : ''; ?>"  >
														</p>
														<div class="wwp-tags">
															<?php esc_html_e( '  [ default label : "Postcode / ZIP" ]', 'woocommerce-wholesale-pricing' ); ?>
														</div>												
														<div class="sep10px">&nbsp;</div>
														<p>
															<input class="inp-cbx" style="display: none" type="checkbox" id="enable_billing_post_code" name="registrations[enable_billing_post_code]" value="yes" <?php echo ( isset( $registrations['enable_billing_post_code'] ) && 'yes' == $registrations['enable_billing_post_code'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="enable_billing_post_code">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Enable', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
															<input class="inp-cbx" style="display: none" type="checkbox" id="required_billing_post_code" name="registrations[required_billing_post_code]" value="yes" <?php echo ( isset( $registrations['required_billing_post_code'] ) && 'yes' == $registrations['required_billing_post_code'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="required_billing_post_code">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Required', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
														</p>
													</td>
												</tr>
												<tr scope="row" >
													<th><label for=""><?php esc_html_e( 'Countries ', 'woocommerce-wholesale-pricing' ); ?></label></th>
													<td>
														<p>
															<input type="text" id="billing_countries" placeholder="Custom label" name="registrations[billing_countries]" value="<?php echo isset( $registrations['billing_countries'] ) ? esc_attr( $registrations['billing_countries'] ) : ''; ?>" >
														</p>
														<div class="wwp-tags">
															<?php esc_html_e( '  [ default label : "Countries" ]', 'woocommerce-wholesale-pricing' ); ?>
														</div>
														<div class="sep10px">&nbsp;</div>
														<p>
															<input class="inp-cbx" style="display: none" type="checkbox" id="enable_billing_country" name="registrations[enable_billing_country]" value="yes" <?php echo ( isset( $registrations['enable_billing_country'] ) && 'yes' == $registrations['enable_billing_country'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="enable_billing_country">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Enable', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
															<input class="inp-cbx" style="display: none" type="checkbox" id="required_billing_country" name="registrations[required_billing_country]" value="yes" <?php echo ( isset( $registrations['required_billing_country'] ) && 'yes' == $registrations['required_billing_country'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="required_billing_country">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Required', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
														</p>
													</td>
												</tr>
												<tr scope="row" >
													<th><label for=""><?php esc_html_e( 'States ', 'woocommerce-wholesale-pricing' ); ?></label></th>
													<td>
														<p>
															<input type="text" id="billing_state"  placeholder="Custom label" name="registrations[billing_state]" value="<?php echo isset( $registrations['billing_state'] ) ? esc_attr( $registrations['billing_state'] ) : ''; ?>" >
														</p>
														<div class="wwp-tags">
															<?php esc_html_e( '  [ default label : "States" ]', 'woocommerce-wholesale-pricing' ); ?>
														</div>
														<div class="sep10px">&nbsp;</div>
														<p>
															<input class="inp-cbx" style="display: none" type="checkbox" id="enable_billing_state" name="registrations[enable_billing_state]" value="yes" <?php echo ( isset( $registrations['enable_billing_state'] ) && 'yes' == $registrations['enable_billing_state'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="enable_billing_state">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Enable', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>																			
															<input class="inp-cbx" style="display: none" type="checkbox" id="required_billing_state" name="registrations[required_billing_state]" value="yes" <?php echo ( isset( $registrations['required_billing_state'] ) && 'yes' == $registrations['required_billing_state'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="required_billing_state">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Required', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
														</p>
													</td>
												</tr>
												<tr scope="row" >
													<th><label><?php esc_html_e( 'Phone ', 'woocommerce-wholesale-pricing' ); ?></label></th>
													<td>
														<p>
															<input type="text" id="billing_phone" placeholder="Custom label" name="registrations[billing_phone]" value="<?php echo isset( $registrations['billing_phone'] ) ? esc_attr( $registrations['billing_phone'] ) : ''; ?>">
														</p>
														<div class="wwp-tags">
															<?php esc_html_e( '  [ default label : "Phone" ]', 'woocommerce-wholesale-pricing' ); ?>
														</div>
														<div class="sep10px">&nbsp;</div>
														<p>
															<input class="inp-cbx" style="display: none" type="checkbox" id="enable_billing_phone" name="registrations[enable_billing_phone]" value="yes" <?php echo ( isset( $registrations['enable_billing_phone'] ) && 'yes' == $registrations['enable_billing_phone'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="enable_billing_phone">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Enable', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
															<input class="inp-cbx" style="display: none" type="checkbox" id="required_billing_phone" name="registrations[required_billing_phone]" value="yes" <?php echo ( isset( $registrations['required_billing_phone'] ) && 'yes' == $registrations['required_billing_phone'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="required_billing_phone">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Required', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
														</p>
													</td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							</div>

							<div class="card">
								<button onclick="return false;" class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse_shipping" aria-expanded="false" aria-controls="collapse_shipping"><?php esc_html_e( 'Shipping Address form Fields', 'woocommerce-wholesale-pricing' ); ?></button>
								<div id="collapse_shipping" class="collapse" data-parent="#accordion_billing" >
									<div class="card-body">
										<table class="form-table">
											<tbody>
												<tr scope="row">
													<th><label for=""><?php esc_html_e( 'Shipping First Name', 'woocommerce-wholesale-pricing' ); ?></label></th>
													<td>														
														<p>
															<input type="text" id="shipping_first_name" placeholder="Custom label" name="registrations[shipping_first_name]" value="<?php echo isset( $registrations['shipping_first_name'] ) ? esc_attr( $registrations['shipping_first_name'] ) : ''; ?>">
														</p>													
														<div class="wwp-tags">
															<?php esc_html_e( '  [ default label : "First Name" ]', 'woocommerce-wholesale-pricing' ); ?>
														</div>
														<div class="sep10px">&nbsp;</div>
														<p>
															<input class="inp-cbx" style="display: none" type="checkbox" id="enable_shipping_first_name" name="registrations[enable_shipping_first_name]" value="yes" <?php echo ( isset( $registrations['enable_shipping_first_name'] ) && 'yes' == $registrations['enable_shipping_first_name'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="enable_shipping_first_name">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Enable', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
															<input class="inp-cbx" style="display: none" type="checkbox" id="required_shipping_first_name" name="registrations[required_shipping_first_name]" value="yes" <?php echo ( isset( $registrations['required_shipping_first_name'] ) && 'yes' == $registrations['required_shipping_first_name'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="required_shipping_first_name">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Required', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
														</p>
													</td>
												</tr>
												<tr scope="row">
													<th><label for=""><?php esc_html_e( 'Shipping Last Name', 'woocommerce-wholesale-pricing' ); ?></label></th>
													<td>
														<p>
															<input type="text" id="shipping_last_name" placeholder="Custom label" name="registrations[shipping_last_name]" value="<?php echo isset( $registrations['shipping_last_name'] ) ? esc_attr( $registrations['shipping_last_name'] ) : ''; ?>">
														</p>													
														<div class="wwp-tags">
															<?php esc_html_e( '  [ default label : "Last Name" ]', 'woocommerce-wholesale-pricing' ); ?>
														</div>
														<div class="sep10px">&nbsp;</div>
														<p>
															<input class="inp-cbx" style="display: none" type="checkbox" id="enable_shipping_last_name" name="registrations[enable_shipping_last_name]" value="yes" <?php echo ( isset( $registrations['enable_shipping_last_name'] ) && 'yes' == $registrations['enable_shipping_last_name'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="enable_shipping_last_name">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Enable', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
															<input class="inp-cbx" style="display: none" type="checkbox" id="required_shipping_last_name" name="registrations[required_shipping_last_name]" value="yes" <?php echo ( isset( $registrations['required_shipping_last_name'] ) && 'yes' == $registrations['required_shipping_last_name'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="required_shipping_last_name">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Required', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
														</p>
													</td>
												</tr>
												<tr scope="row" >
													<th><label for=""><?php esc_html_e( 'Company', 'woocommerce-wholesale-pricing' ); ?></label></th>
													<td>														
														<p>
															<input type="text" id="shipping_company" placeholder="Custom label" name="registrations[shipping_company]" value="<?php echo isset( $registrations['shipping_company'] ) ? esc_attr( $registrations['shipping_company'] ) : ''; ?>">
														</p>											
														<div class="wwp-tags">
															<?php esc_html_e( '  [ default label : "Company" ] ', 'woocommerce-wholesale-pricing' ); ?>
														</div>
														<div class="sep10px">&nbsp;</div>
														<p>
															<input class="inp-cbx" style="display: none" type="checkbox" id="enable_shipping_company" name="registrations[enable_shipping_company]" value="yes" <?php echo ( isset( $registrations['enable_shipping_company'] ) && 'yes' == $registrations['enable_shipping_company'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="enable_shipping_company">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Enable', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>	
															<input class="inp-cbx" style="display: none" type="checkbox" id="required_shipping_company" name="registrations[required_shipping_company]" value="yes" <?php echo ( isset( $registrations['required_shipping_company'] ) && 'yes' == $registrations['required_shipping_company'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="required_shipping_company">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Required', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>	
														</p>
													</td>
												</tr>
												<tr scope="row" >
													<th><label for=""><?php esc_html_e( 'Address line 1 ', 'woocommerce-wholesale-pricing' ); ?></label></th>
													<td>
														
														<p>
															<input type="text" id="shipping_address_1" placeholder="Custom label" name="registrations[shipping_address_1]" value="<?php echo isset( $registrations['shipping_address_1'] ) ? esc_attr( $registrations['shipping_address_1'] ) : ''; ?>">
														</p>												
														<div class="wwp-tags">
															<?php esc_html_e( '  [ default label : "Address line 1" ] ', 'woocommerce-wholesale-pricing' ); ?>
														</div>
														<div class="sep10px">&nbsp;</div>
														<p>
															<input class="inp-cbx" style="display: none" type="checkbox" id="enable_shipping_address_1" name="registrations[enable_shipping_address_1]" value="yes" value="yes" <?php echo ( isset( $registrations['enable_shipping_address_1'] ) && 'yes' == $registrations['enable_shipping_address_1'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="enable_shipping_address_1">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Enable', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
															<input class="inp-cbx" style="display: none" type="checkbox" id="required_shipping_address_1" name="registrations[required_shipping_address_1]" value="yes" value="yes" <?php echo ( isset( $registrations['required_shipping_address_1'] ) && 'yes' == $registrations['required_shipping_address_1'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="required_shipping_address_1">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Required', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
														</p>
													</td>
												</tr>
												<tr scope="row" >
													<th><label for=""><?php esc_html_e( 'Address line 2 ', 'woocommerce-wholesale-pricing' ); ?></label></th>
													<td>
														<p>
															<input type="text" id="shipping_address_2" placeholder="Custom label" name="registrations[shipping_address_2]" value="<?php echo isset( $registrations['shipping_address_2'] ) ? esc_attr( $registrations['shipping_address_2'] ) : ''; ?>">
														</p>
														<div class="wwp-tags">
															<?php esc_html_e( '  [ default label : "Address line 2" ] ', 'woocommerce-wholesale-pricing' ); ?>
														</div>
														<div class="sep10px">&nbsp;</div>
														<p>
															<input class="inp-cbx" style="display: none" type="checkbox" id="enable_shipping_address_2" name="registrations[enable_shipping_address_2]" value="yes" <?php echo ( isset( $registrations['enable_shipping_address_2'] ) && 'yes' == $registrations['enable_shipping_address_2'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="enable_shipping_address_2">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Enable', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>	
															<input class="inp-cbx" style="display: none" type="checkbox" id="required_shipping_address_2" name="registrations[required_shipping_address_2]" value="yes" <?php echo ( isset( $registrations['required_shipping_address_2'] ) && 'yes' == $registrations['required_shipping_address_2'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="required_shipping_address_2">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Required', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
														</p>
													</td>
												</tr>
												<tr scope="row" >
													<th><label for=""><?php esc_html_e( 'City ', 'woocommerce-wholesale-pricing' ); ?></label></th>
													<td>
														
														<p>
															<input type="text" id="shipping_city" placeholder="Custom label" name="registrations[shipping_city]" value="<?php echo isset( $registrations['shipping_city'] ) ? esc_attr( $registrations['shipping_city'] ) : ''; ?>">
														</p>												
														<div class="wwp-tags">
															<?php esc_html_e( '  [ default label : "City" ] ', 'woocommerce-wholesale-pricing' ); ?>
														</div>
														<div class="sep10px">&nbsp;</div>
														<p>
															<input class="inp-cbx" style="display: none" type="checkbox" id="enable_shipping_city" name="registrations[enable_shipping_city]" value="yes" <?php echo ( isset( $registrations['enable_shipping_city'] ) && 'yes' == $registrations['enable_shipping_city'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="enable_shipping_city">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Enable', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
															<input class="inp-cbx" style="display: none" type="checkbox" id="required_shipping_city" name="registrations[required_shipping_city]" value="yes" <?php echo ( isset( $registrations['required_shipping_city'] ) && 'yes' == $registrations['required_shipping_city'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="required_shipping_city">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Required', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
														</p>
													</td>
												</tr>
												<tr scope="row" >
													<th><label for=""><?php esc_html_e( 'Postcode / ZIP ', 'woocommerce-wholesale-pricing' ); ?></label></th>
													<td>
														
														<p>
															<input type="text" id="shipping_post_code" placeholder="Custom label" name="registrations[shipping_post_code]" value="<?php echo isset( $registrations['shipping_post_code'] ) ? esc_attr( $registrations['shipping_post_code'] ) : ''; ?>">
														</p>											
														<div class="wwp-tags">
															<?php esc_html_e( '  [ default label : "Postcode / ZIP" ] ', 'woocommerce-wholesale-pricing' ); ?>
														</div>
														<div class="sep10px">&nbsp;</div>
														<p>
															<input class="inp-cbx" style="display: none" type="checkbox" id="enable_shipping_post_code" name="registrations[enable_shipping_post_code]" value="yes" <?php echo ( isset( $registrations['enable_shipping_post_code'] ) && 'yes' == $registrations['enable_shipping_post_code'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="enable_shipping_post_code">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Enable', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>	
															<input class="inp-cbx" style="display: none" type="checkbox" id="required_shipping_post_code" name="registrations[required_shipping_post_code]" value="yes" <?php echo ( isset( $registrations['required_shipping_post_code'] ) && 'yes' == $registrations['required_shipping_post_code'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="required_shipping_post_code">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Required', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
														</p>
													</td>
												</tr>
												<tr scope="row" >
													<th><label for=""><?php esc_html_e( 'Countries ', 'woocommerce-wholesale-pricing' ); ?></label></th>
													<td>
														
														<p>
															<input type="text" id="shipping_countries" placeholder="Custom label" name="registrations[shipping_countries]" value="<?php echo isset( $registrations['shipping_countries'] ) ? esc_attr( $registrations['shipping_countries'] ) : ''; ?>">
														</p>
														<div class="wwp-tags">
															<?php esc_html_e( '  [ default label : "Countries" ] ', 'woocommerce-wholesale-pricing' ); ?>
														</div>
														<div class="sep10px">&nbsp;</div>
														<p>
															<input class="inp-cbx" style="display: none" type="checkbox" id="enable_shipping_country" name="registrations[enable_shipping_country]" value="yes" <?php echo ( isset( $registrations['enable_shipping_country'] ) && 'yes' == $registrations['enable_shipping_country'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="enable_shipping_country">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Enable', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>													
															<input class="inp-cbx" style="display: none" type="checkbox" id="required_shipping_country" name="registrations[required_shipping_country]" value="yes" <?php echo ( isset( $registrations['required_shipping_country'] ) && 'yes' == $registrations['required_shipping_country'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="required_shipping_country">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Required', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
														</p>
													</td>
												</tr>
												<tr scope="row">
													<th><label for=""><?php esc_html_e( 'States ', 'woocommerce-wholesale-pricing' ); ?></label></th>
													<td>
														<p>
															<input type="text" id="shipping_state" placeholder="Custom label" name="registrations[shipping_state]" value="<?php echo isset( $registrations['shipping_state'] ) ? esc_attr( $registrations['shipping_state'] ) : ''; ?>">
														</p>											
														<div class="wwp-tags">
															<?php esc_html_e( '  [ default label : "States" ] ', 'woocommerce-wholesale-pricing' ); ?>
														</div>
														<div class="sep10px">&nbsp;</div>
														<p>
															<input class="inp-cbx" style="display: none" type="checkbox" id="enable_shipping_state" name="registrations[enable_shipping_state]" value="yes" <?php echo ( isset( $registrations['enable_shipping_state'] ) && 'yes' == $registrations['enable_shipping_state'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="enable_shipping_state">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Enable', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
															<input class="inp-cbx" style="display: none" type="checkbox" id="required_shipping_state" name="registrations[required_shipping_state]" value="yes" <?php echo ( isset( $registrations['required_shipping_state'] ) && 'yes' == $registrations['required_shipping_state'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="required_shipping_state">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Required', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
														</p>
													</td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							</div>
							<?php
							wp_enqueue_script( 'wc-enhanced-select' );
wp_enqueue_style( 'wwp-select2' );
							?>
							<div class="card">
								<button onclick="return false;" class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse_others" aria-expanded="false" aria-controls="collapse_others"><?php esc_html_e( 'Other Fields', 'woocommerce-wholesale-pricing' ); ?></button>
								<div id="collapse_others" class="collapse" data-parent="#accordion_billing" >
									<div class="card-body">
										<table class="form-table">
											<tbody>
												<tr scope="row">
													<th><label for=""><?php esc_html_e( 'Tax ID ', 'woocommerce-wholesale-pricing' ); ?></label>
													<span data-tip="<?php esc_html_e( "Enable this option to create the 'Tax ID' label text.", 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-right"><span class="woocommerce-help-tip"></span></span>
													</th>
													<td>												
														<p>
															<input type="text" id="woo_tax_id"  placeholder="Custom label" name="registrations[woo_tax_id]" value="<?php echo isset( $registrations['woo_tax_id'] ) ? esc_attr( $registrations['woo_tax_id'] ) : ''; ?>">												
															<div class="wwp-tags">
																<?php esc_html_e( '  [ default label : "Tax ID" ] ', 'woocommerce-wholesale-pricing' ); ?>
															</div>
															<div class="sep10px">&nbsp;</div>
															<input class="inp-cbx" style="display: none" type="checkbox" id="enable_tex_id" name="registrations[enable_tex_id]" value="yes" <?php echo ( isset( $registrations['enable_tex_id'] ) && 'yes' == $registrations['enable_tex_id'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="enable_tex_id">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Enable', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
															<input class="inp-cbx" style="display: none" type="checkbox" id="required_tex_id" name="registrations[required_tex_id]" value="yes" <?php echo ( isset( $registrations['required_tex_id'] ) && 'yes' == $registrations['required_tex_id'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="required_tex_id">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Make Field Required', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
														</p>
													</td>
												</tr>
												<tr scope="row">
													<th><label for=""><?php esc_html_e( 'File Upload ', 'woocommerce-wholesale-pricing' ); ?></label>
													<span data-tip="<?php esc_html_e( "Enable this option to create the 'File Upload' label text.", 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-right"><span class="woocommerce-help-tip"></span></span>
													</th>
													<td>
														<p>
															<input type="text" id="woo_file_upload"  placeholder="Custom label" name="registrations[woo_file_upload]" value="<?php echo isset( $registrations['woo_file_upload'] ) ? esc_attr( $registrations['woo_file_upload'] ) : ''; ?>">											
															<div class="wwp-tags">
																<?php esc_html_e( '  [ default label : "File Upload" ] ', 'woocommerce-wholesale-pricing' ); ?>
															</div>
															<div class="sep10px">&nbsp;</div>
															<input class="inp-cbx" style="display: none" type="checkbox" id="enable_file_upload" name="registrations[enable_file_upload]" value="yes" <?php echo ( isset( $registrations['enable_file_upload'] ) && 'yes' == $registrations['enable_file_upload'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="enable_file_upload">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Enable', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
															<input class="inp-cbx" style="display: none" type="checkbox" id="required_file_upload" name="registrations[required_file_upload]" value="yes" <?php echo ( isset( $registrations['required_file_upload'] ) && 'yes' == $registrations['required_file_upload'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="required_file_upload">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Make Field Required', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
															<!-- multiple -->
															<input class="inp-cbx" style="display: none" type="checkbox" id="multiple_file_upload" name="registrations[multiple_file_upload]" value="yes" <?php echo ( isset( $registrations['multiple_file_upload'] ) && 'yes' == $registrations['multiple_file_upload'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="multiple_file_upload">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Multiple File Upload', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
														</p>
													</td>
												</tr>
												<tr scope="row">
													<th><label for=""><?php esc_html_e( 'Tax ID Display', 'woocommerce-wholesale-pricing' ); ?></label>
													<span data-tip="<?php esc_html_e( 'Enable this option to display the Tax ID in the billing address.', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-right"><span class="woocommerce-help-tip"></span></span>
													</th>
													<td>
														<p>															
															<input class="inp-cbx" style="display: none" type="checkbox" id="tax_id_display" name="registrations[tax_id_display]" value="yes" <?php echo ( isset( $registrations['tax_id_display'] ) && 'yes' == $registrations['tax_id_display'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="tax_id_display">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Enable to display tax id in billing address', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
														</p>
													</td>
												</tr>
												<tr scope="row">
													<th><label for=""><?php esc_html_e( 'Confirm Password', 'woocommerce-wholesale-pricing' ); ?></label>
													<span data-tip="<?php esc_html_e( 'Confirm Password Field Display on Wholesale Registration Form', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-right"><span class="woocommerce-help-tip"></span></span>
													</th>
													<td>
														<p>															
															<input class="inp-cbx" style="display: none" type="checkbox" id="extra_pass_field" name="registrations[extra_pass_field]" value="yes" <?php echo ( isset( $registrations['extra_pass_field'] ) && 'yes' == $registrations['extra_pass_field'] ) ? 'checked' : ''; ?>>
															<label class="cbx cbx-square" for="extra_pass_field">
																<span>
																	<svg width="12px" height="9px" viewbox="0 0 12 9">
																		<polyline points="1 5 4 8 11 1"></polyline>
																	</svg>
																</span>
																<span><?php esc_html_e( 'Enable this option to display confirm password field on registration form.', 'woocommerce-wholesale-pricing' ); ?></span>
															</label>
														</p>
													</td>
												</tr>
												<?php 
												$settings = ! empty( get_option( 'wwp_wholesale_pricing_options' ) ) ? get_option( 'wwp_wholesale_pricing_options', true ) : array();
												if ( isset( $settings['wholesale_role'] ) && 'multiple' == $settings['wholesale_role'] ) {
													?>
													<tr scope="row">
														<th>
															<label for=""><?php esc_html_e( 'Enable User Role', 'woocommerce-wholesale-pricing' ); ?></label>
															<span data-tip="<?php esc_html_e( 'Enable user role on registeration page', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-right"><span class="woocommerce-help-tip"></span></span>
														</th>
														<td>
															<p>															
																<input class="inp-cbx" style="display: none" type="checkbox" id="extra_role_fields" name="registrations[extra_role_fields]" value="yes" <?php echo ( isset( $registrations['extra_role_fields'] ) && 'yes' == $registrations['extra_role_fields'] ) ? 'checked' : ''; ?>>
																<label class="cbx cbx-square" for="extra_role_fields">
																	<span>
																		<svg width="12px" height="9px" viewbox="0 0 12 9">
																			<polyline points="1 5 4 8 11 1"></polyline>
																		</svg>
																	</span>
																	<span><?php esc_html_e( 'Enable this option to display User Role field on registration form.', 'woocommerce-wholesale-pricing' ); ?></span>
																</label>
															</p>
														</td>
													</tr>
												
													<tr>
														<th>
															<label for=""><?php esc_html_e( 'Show user role on Registration Page', 'woocommerce-wholesale-pricing' ); ?></label>
														</th>
														<td>
															<select id="roles-on-registeration" class="regular-text wc-enhanced-select" multiple name="registrations[show_role_on_reg_page][]" >
																<?php
																$allterms = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
																foreach ( $allterms as $allterm ) {
																	$selected = '';
																	if ( isset( $registrations['show_role_on_reg_page'] ) && in_array( $allterm->slug, $registrations['show_role_on_reg_page'] ) ) {
																		$selected = 'selected';
																	}
																	?>
																	<option value="<?php echo esc_attr( $allterm->slug ); ?>" <?php echo esc_html( $selected ); ?>><?php echo esc_html( $allterm->name ); ?></option>
																<?php } ?> 
															</select>
														</td>
													</tr>
													<?php } ?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<table class="recaptcha-table form-table" style="display: <?php echo esc_html_e( wholesale_content_tab_active( 'recaptcha' ) ); ?>">
						<tbody>
							<tr scope="row">
								<th><label for=""><?php esc_html_e( 'Enable Google reCAPTCHA', 'woocommerce-wholesale-pricing' ); ?></label></th>
								<td>
									<p>
										<label for="enable_recaptcha" class="switch">
											<input id="enable_recaptcha" type="checkbox" value="yes" name="registrations[enable_recaptcha]" <?php checked( ( isset( $registrations['enable_recaptcha'] ) ? $registrations['enable_recaptcha'] : '' ), 'yes', true ); ?>>
											<span class="slider round"></span>
										</label>
										<span data-tip="<?php esc_html_e( 'Enable Google reCaptcha on the Wholesale Registration Form.', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
									</p>
								</td>
							</tr>
							<tr scope="row">
								<th><label for=""><?php esc_html_e( 'Google reCAPTCHA Version', 'woocommerce-wholesale-pricing' ); ?></label></th>
								<td>
									<p>
										<input class="inp-cbx" style="display: none" type="radio" id="recaptcha_v2" name="registrations[recaptcha_version]" value="v2" <?php checked( ( isset( $registrations['recaptcha_version'] ) ? $registrations['recaptcha_version'] : 'v2' ), 'v2', true ); ?>> 
										<label class="cbx cbx-square" for="recaptcha_v2"> 
											<span>
												<svg width="12px" height="9px" viewBox="0 0 12 9">
													<polyline points="1 5 4 8 11 1"></polyline> 
												</svg> 
											</span> 
											<span> <?php echo esc_html__( 'Google reCAPTCHA Version 2', 'woocommerce-wholesale-pricing' ); ?></span> 
										</label>
										<span class="description"><?php esc_html_e( 'reCAPTCHA v2 will provide a challenge that the visitor must solve to prove they’re human', 'woocommerce-wholesale-pricing' ); ?></span>
									</p>
									<p>
										<input class="inp-cbx" style="display: none" type="radio" id="recaptcha_v3" name="registrations[recaptcha_version]" value="v3" <?php checked( ( isset( $registrations['recaptcha_version'] ) ? $registrations['recaptcha_version'] : '' ), 'v3', true ); ?>> 
										<label class="cbx cbx-square" for="recaptcha_v3"> 
											<span>
												<svg width="12px" height="9px" viewBox="0 0 12 9">
													<polyline points="1 5 4 8 11 1"></polyline> 
												</svg> 
											</span> 
											<span> <?php echo esc_html__( 'Google reCAPTCHA Version 3', 'woocommerce-wholesale-pricing' ); ?></span>
										</label> 
										<span class="description"><?php esc_html_e( 'reCAPTCHA v3 is invisible for website visitors. There are no challenges to solve. It continuously monitors each visitor’s behavior to determine whether it’s a human or a bot', 'woocommerce-wholesale-pricing' ); ?></span>
									</p>
								</td>
							</tr>
							<tr scope="row" class="v2">
								<th><label for="recaptcha_v2_site_key"><?php esc_html_e( 'Site Key', 'woocommerce-wholesale-pricing' ); ?></label></th>
								<td>
									<p>
										<input type="text" id="recaptcha_v2_site_key" name="registrations[recaptcha_v2_site_key]" value="<?php echo ( isset( $registrations['recaptcha_v2_site_key'] ) ? esc_attr( $registrations['recaptcha_v2_site_key'] ) : '' ); ?>">
										<span class="description"><?php esc_html_e( 'Enter Google reCAPTCHA v2 Site Key.', 'woocommerce-wholesale-pricing' ); ?><a href="https://developers.google.com/recaptcha/intro#recaptcha-overview" target="_blank"><?php esc_html_e( 'Click Here', 'woocommerce-wholesale-pricing' ); ?></a></span> 
									</p>
								</td>
							</tr>
							<tr scope="row" class="v2">
								<th><label for="recaptcha_v2_secret_key"><?php esc_html_e( 'Secret Key', 'woocommerce-wholesale-pricing' ); ?></label></th>
								<td>
									<p>
										<input type="text" id="recaptcha_v2_secret_key" name="registrations[recaptcha_v2_secret_key]" value="<?php echo ( isset( $registrations['recaptcha_v2_secret_key'] ) ? esc_attr( $registrations['recaptcha_v2_secret_key'] ) : '' ); ?>">
										<span class="description"><?php esc_html_e( 'Enter Google reCAPTCHA v2 Secret Key.', 'woocommerce-wholesale-pricing' ); ?>  <a href="https://developers.google.com/recaptcha/intro#recaptcha-overview" target="_blank"><?php esc_html_e( 'Click Here', 'woocommerce-wholesale-pricing' ); ?></a></span> 
									</p>
								</td>
							</tr>
							<tr scope="row" class="v2">
								<th><label for=""><?php esc_html_e( 'Theme', 'woocommerce-wholesale-pricing' ); ?></label></th>
								<td>
									<p>
										<input class="inp-cbx" style="display: none" type="radio" id="recaptcha_v2_theme_light" name="registrations[recaptcha_v2_theme]" value="light" <?php checked( ( isset( $registrations['recaptcha_v2_theme'] ) ? $registrations['recaptcha_v2_theme'] : 'light' ), 'light', true ); ?>> 
										<label class="cbx cbx-square" for="recaptcha_v2_theme_light"> 
											<span>
												<svg width="12px" height="9px" viewBox="0 0 12 9">
													<polyline points="1 5 4 8 11 1"></polyline> 
												</svg> 
											</span> 
											<span> <?php echo esc_html__( 'Light', 'woocommerce-wholesale-pricing' ); ?></span> 
										</label>
									</p>
									<p>
										<input class="inp-cbx" style="display: none" type="radio" id="recaptcha_v3_theme_dark" name="registrations[recaptcha_v2_theme]" value="dark" <?php checked( ( isset( $registrations['recaptcha_v2_theme'] ) ? $registrations['recaptcha_v2_theme'] : '' ), 'dark', true ); ?>> 
										<label class="cbx cbx-square" for="recaptcha_v3_theme_dark"> 
											<span>
												<svg width="12px" height="9px" viewBox="0 0 12 9">
													<polyline points="1 5 4 8 11 1"></polyline> 
												</svg> 
											</span> 
											<span> <?php echo esc_html__( 'Dark', 'woocommerce-wholesale-pricing' ); ?></span>
										</label> 
										<span class="description"><?php esc_html_e( 'Select Google reCAPTCHA Version 2 Theme.', 'woocommerce-wholesale-pricing' ); ?></span>
									</p>
								</td>
							</tr>
							<tr scope="row" class="v3">
								<th><label for="recaptcha_v3_site_key"><?php esc_html_e( 'Site Key', 'woocommerce-wholesale-pricing' ); ?></label></th>
								<td>
									<p>
										<input type="text" id="recaptcha_v3_site_key" name="registrations[recaptcha_v3_site_key]" value="<?php echo ( isset( $registrations['recaptcha_v3_site_key'] ) ? esc_attr( $registrations['recaptcha_v3_site_key'] ) : '' ); ?>">
										<span class="description"><?php esc_html_e( 'Enter Google reCAPTCHA v3 Site Key.', 'woocommerce-wholesale-pricing' ); ?>  <a href="https://developers.google.com/recaptcha/intro#recaptcha-overview" target="_blank"><?php esc_html_e( 'Click Here', 'woocommerce-wholesale-pricing' ); ?></a></span> 
									</p>
								</td>
							</tr>
							<tr scope="row" class="v3">
								<th><label for="recaptcha_v3_secret_key"><?php esc_html_e( 'Secret Key', 'woocommerce-wholesale-pricing' ); ?></label></th>
								<td>
									<p>
										<input type="text" id="recaptcha_v3_secret_key" name="registrations[recaptcha_v3_secret_key]" value="<?php echo ( isset( $registrations['recaptcha_v3_secret_key'] ) ? esc_attr( $registrations['recaptcha_v3_secret_key'] ) : '' ); ?>">
										<span class="description"><?php esc_html_e( 'Enter Google reCAPTCHA v3 Secret Key.', 'woocommerce-wholesale-pricing' ); ?>  <a href="https://developers.google.com/recaptcha/intro#recaptcha-overview" target="_blank"><?php esc_html_e( 'Click Here', 'woocommerce-wholesale-pricing' ); ?></a></span> 
									</p>
								</td>
							</tr>
							<tr scope="row" class="v3">
								<th><label for=""><?php esc_html_e( 'Score', 'woocommerce-wholesale-pricing' ); ?></label></th>
								<td>
									<p>
										<input class="inp-cbx" style="display: none" type="radio" id="recaptcha_v2_score_01" name="registrations[recaptcha_v3_score]" value="0.1" <?php checked( ( isset( $registrations['recaptcha_v3_score'] ) ? $registrations['recaptcha_v3_score'] : '' ), '0.1', true ); ?>> 
										<label class="cbx cbx-square" for="recaptcha_v2_score_01"> 
											<span>
												<svg width="12px" height="9px" viewBox="0 0 12 9">
													<polyline points="1 5 4 8 11 1"></polyline> 
												</svg> 
											</span> 
											<span> <?php echo esc_html__( '0.1', 'woocommerce-wholesale-pricing' ); ?></span> 
										</label>
									</p>
									<p>
										<input class="inp-cbx" style="display: none" type="radio" id="recaptcha_v2_score_02" name="registrations[recaptcha_v3_score]" value="0.2" <?php checked( ( isset( $registrations['recaptcha_v3_score'] ) ? $registrations['recaptcha_v3_score'] : '' ), '0.2', true ); ?>> 
										<label class="cbx cbx-square" for="recaptcha_v2_score_02"> 
											<span>
												<svg width="12px" height="9px" viewBox="0 0 12 9">
													<polyline points="1 5 4 8 11 1"></polyline> 
												</svg> 
											</span> 
											<span> <?php echo esc_html__( '0.2', 'woocommerce-wholesale-pricing' ); ?></span>
										</label> 
									</p>
									<p>
										<input class="inp-cbx" style="display: none" type="radio" id="recaptcha_v2_score_03" name="registrations[recaptcha_v3_score]" value="0.3" <?php checked( ( isset( $registrations['recaptcha_v3_score'] ) ? $registrations['recaptcha_v3_score'] : '0.3' ), '0.3', true ); ?>> 
										<label class="cbx cbx-square" for="recaptcha_v2_score_03"> 
											<span>
												<svg width="12px" height="9px" viewBox="0 0 12 9">
													<polyline points="1 5 4 8 11 1"></polyline> 
												</svg> 
											</span> 
											<span> <?php echo esc_html__( '0.3', 'woocommerce-wholesale-pricing' ); ?></span>
										</label> 
									</p>
									<p>
										<input class="inp-cbx" style="display: none" type="radio" id="recaptcha_v2_score_04" name="registrations[recaptcha_v3_score]" value="0.4" <?php checked( ( isset( $registrations['recaptcha_v3_score'] ) ? $registrations['recaptcha_v3_score'] : '' ), '0.4', true ); ?>> 
										<label class="cbx cbx-square" for="recaptcha_v2_score_04"> 
											<span>
												<svg width="12px" height="9px" viewBox="0 0 12 9">
													<polyline points="1 5 4 8 11 1"></polyline> 
												</svg> 
											</span> 
											<span> <?php echo esc_html__( '0.4', 'woocommerce-wholesale-pricing' ); ?></span>
										</label> 
									</p>
									<p>
										<input class="inp-cbx" style="display: none" type="radio" id="recaptcha_v2_score_05" name="registrations[recaptcha_v3_score]" value="0.5" <?php checked( ( isset( $registrations['recaptcha_v3_score'] ) ? $registrations['recaptcha_v3_score'] : '' ), '0.5', true ); ?>> 
										<label class="cbx cbx-square" for="recaptcha_v2_score_05"> 
											<span>
												<svg width="12px" height="9px" viewBox="0 0 12 9">
													<polyline points="1 5 4 8 11 1"></polyline> 
												</svg> 
											</span> 
											<span> <?php echo esc_html__( '0.5', 'woocommerce-wholesale-pricing' ); ?></span>
										</label> 
										<span class="description"><?php esc_html_e( 'Select Google Version 3 Score.', 'woocommerce-wholesale-pricing' ); ?></span>
									</p>
								</td>
							</tr>
							<tr scope="row" class="v3">
								<th><label for=""><?php esc_html_e( 'Badge Position', 'woocommerce-wholesale-pricing' ); ?></label></th>
								<td>
									<p>
										<input class="inp-cbx" style="display: none" type="radio" id="recaptcha_v2_badge_inline" name="registrations[recaptcha_v3_badge_position]" value="inline" <?php checked( ( isset( $registrations['recaptcha_v3_badge_position'] ) ? $registrations['recaptcha_v3_badge_position'] : '' ), 'inline', true ); ?>> 
										<label class="cbx cbx-square" for="recaptcha_v2_badge_inline"> 
											<span>
												<svg width="12px" height="9px" viewBox="0 0 12 9">
													<polyline points="1 5 4 8 11 1"></polyline> 
												</svg> 
											</span> 
											<span> <?php echo esc_html__( 'Inline', 'woocommerce-wholesale-pricing' ); ?></span> 
										</label>
									</p>
									<p>
										<input class="inp-cbx" style="display: none" type="radio" id="recaptcha_v2_badge_bottom_left" name="registrations[recaptcha_v3_badge_position]" value="bottomleft" <?php checked( ( isset( $registrations['recaptcha_v3_badge_position'] ) ? $registrations['recaptcha_v3_badge_position'] : '' ), 'bottomleft', true ); ?>> 
										<label class="cbx cbx-square" for="recaptcha_v2_badge_bottom_left"> 
											<span>
												<svg width="12px" height="9px" viewBox="0 0 12 9">
													<polyline points="1 5 4 8 11 1"></polyline> 
												</svg> 
											</span> 
											<span> <?php echo esc_html__( 'Bottom - Left', 'woocommerce-wholesale-pricing' ); ?></span>
										</label> 
									</p>
									<p>
										<input class="inp-cbx" style="display: none" type="radio" id="recaptcha_v2_badge_bottom_right" name="registrations[recaptcha_v3_badge_position]" value="bottomright" <?php checked( ( isset( $registrations['recaptcha_v3_badge_position'] ) ? $registrations['recaptcha_v3_badge_position'] : 'bottomright' ), 'bottomright', true ); ?>> 
										<label class="cbx cbx-square" for="recaptcha_v2_badge_bottom_right"> 
											<span>
												<svg width="12px" height="9px" viewBox="0 0 12 9">
													<polyline points="1 5 4 8 11 1"></polyline> 
												</svg> 
											</span> 
											<span> <?php echo esc_html__( 'Bottom - Right', 'woocommerce-wholesale-pricing' ); ?></span>
										</label> 
									</p>
								</td>
							</tr>
						</tbody>
					</table>

					<p>
						<div class="sep20px">&nbsp;</div>
						<button name="save_wwp_registration_setting" class="wwp-button-primary" type="submit" value="Save changes"><?php esc_html_e( 'Save changes', 'woocommerce-wholesale-pricing' ); ?></button>
					</p>
				</form>
				<?php
			} else {
				include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-form-builder.php';
			}
			?>
			
			
			</div>
			<div class="map_shortcode_callback">
				<h5><?php esc_html_e( 'Shortcode', 'woocommerce-wholesale-pricing' ); ?></h5>
				<p> <?php esc_html_e( 'Copy following shortcode, and paste in page where you would like to display wholesaler registration form.', 'woocommerce-wholesale-pricing' ); ?></p>
				<!-- <div class="map_shortcode_copy"  ><span class="dashicons dashicons-admin-page"></span><label><?php esc_html_e( 'Copy', 'woocommerce-wholesale-pricing' ); ?></label></div> -->
				<p style="position:relative"> 
					<input type="text" onfocus="copytoclipboard()" onclick="copytoclipboard()" value="[wwp_registration_form]" readonly="readonly" name="shortcode" class="large-text code"> 
					<span class="dashicons dashicons-admin-page" onclick="copytoclipboard()"></span>
				</p>
			</div>
			
			<?php
		}
	}
	new Wwp_Wholesale_Registration_Page_Setting();
}
