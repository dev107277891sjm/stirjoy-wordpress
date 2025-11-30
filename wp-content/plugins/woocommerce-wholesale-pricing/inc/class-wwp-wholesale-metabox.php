<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Class to add product meta boxes
 */
if ( ! class_exists( 'Wwp_Wholesale_Product_Metabox' ) ) {

	class Wwp_Wholesale_Product_Metabox {

		public $settings = array();

		public function __construct() {

			add_action( 'add_meta_boxes', array( $this, 'add_multiuser_role_metabox' ) );
			add_action( 'wp_ajax_retrieve_wholesale_multiuser_pricing', array( $this, 'retrieve_wholesale_multiuser_pricing' ) );
			add_action( 'save_post_product', array( $this, 'save_wholesale_metabox_data' ), 10, 1 );
			add_action( 'admin_enqueue_scripts', array( $this, 'wwp_product_admin_scripts_styles' ) );
			add_action( 'admin_head', array( $this, 'admin_head' ) );
			$settings       = get_option( 'wwp_wholesale_pricing_options' );
			$this->settings = $settings;
			if ( isset($_REQUEST['post']) ) {
				$order_id = sanitize_text_field( $_REQUEST['post'] ) ;
			} elseif ( isset( $_REQUEST['order_id'] ) ) {
				$order_id = sanitize_text_field( $_REQUEST['order_id'] ) ;
			} elseif ( get_the_ID() ) {
				$order_id = get_the_ID();
			} elseif ( isset( $_REQUEST['page'], $_REQUEST['id'] ) && 'wc-orders' == sanitize_text_field( $_REQUEST['page'] ) ) {
				$order_id =sanitize_text_field( $_REQUEST['id'] );
			} else {
				$order_id ='';
			}

			if ( isset( $settings['enable_admin_new_order_item'] ) && 'yes' == $settings['enable_admin_new_order_item'] && in_array( get_post_type( $order_id ), array( 'shop_order', 'shop_order_placehold' ) ) ) { 
				include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-multiuser.php';
				add_action( 'woocommerce_admin_order_item_headers', array( $this, 'woocommerce_admin_order_item_headers' ), 10, 1 );
				add_action( 'woocommerce_admin_order_item_values', array( $this, 'woocommerce_admin_order_item_values' ), 10, 3 );
				add_action( 'woocommerce_new_order_item', array( $this, 'action_woocommerce_new_order_item' ), 10, 3 );
			}
		}
		 
		public function admin_head() { 
			$settings = $this->settings;
			if ( isset( $settings['enable_admin_new_order_item'] ) && 'yes' == $settings['enable_admin_new_order_item'] ) { 
				$get = wwp_get_get_data('post_type');
				if ( is_admin() && ( ( 'shop_order' == get_post_type( get_the_ID() ) || 'shop_order' == $get ) || ( isset( $get['page'] ) && 'wc-orders' == $get['page'] ) ) ) {
					?>
					<script type="text/javascript">
						jQuery(document).ready(function() {
							jQuery('#customer_user').on('change', function() {
								setTimeout( function () { 
									//this.form.submit();
									var postElement = document.getElementById('post');
									if (postElement) {
										postElement.submit();
									} else {
										jQuery(".save_order").click();
									}
								}, 3000);
							});
						});
					</script>
					<?php 
					if ( isset($_REQUEST['post']) ) {
						$order_id = sanitize_text_field( $_REQUEST['post'] ) ;
					} elseif ( isset( $_REQUEST['order_id'] ) ) {
						$order_id = sanitize_text_field( $_REQUEST['order_id'] ) ;
					} elseif ( isset( $_REQUEST['page'], $_REQUEST['id'] ) && 'wc-orders' == sanitize_text_field( $_REQUEST['page'] ) ) {
						$order_id =sanitize_text_field( $_REQUEST['id'] );
					} else {
						$order_id ='';
					}

					$order = wc_get_order( $order_id );
					if ( ! $order ) {
						return;
					}
					 
					if ( ! is_wholesaler_user( $order->get_user_id() ) ) {
						return;
					}
					?>
					<style>
						.woocommerce_order_items td.item_cost, .woocommerce_order_items th.item_cost.sortable {
							display: none;
						}
					</style>
					<?php 
				}
			}
		}

		public function woocommerce_admin_order_item_headers( $order ) {
			if ( ! is_wholesaler_user( $order->get_user_id() ) ) {
				return;
			}
			echo '<th class="wholesale_th">' . esc_html__( 'Cost', 'woocommerce-wholesale-pricing' ) . '</th>';
			echo '<th class="wholesale_th">' . esc_html__( 'Wholesale Prices', 'woocommerce-wholesale-pricing' ) . '</th>';
		}

		public function woocommerce_admin_order_item_values( $product, $item, $item_id = null ) {
			if ( isset($_REQUEST['post']) ) {
				$order_id = sanitize_text_field( $_REQUEST['post'] ) ;
			} elseif ( isset( $_REQUEST['order_id'] ) ) {
				$order_id = sanitize_text_field( $_REQUEST['order_id'] ) ;
			} elseif ( isset( $_REQUEST['page'], $_REQUEST['id'] ) && 'wc-orders' == sanitize_text_field( $_REQUEST['page'] ) ) {
				$order_id =sanitize_text_field( $_REQUEST['id'] );
			} else {
				$order_id ='';
			}
			if ( ! is_wholesaler_user( wc_get_order( $order_id )->get_user_id() ) ) {
				return;
			}
			if ( 'shop_order_refund' == $item->get_type() ) {
				echo wp_kses_post( '<td></td><td></td>' );
				return;
			}
			$settings              = $this->settings;
			$Wholesale_Price_class = new WWP_Easy_Wholesale_Multiuser();
			$variation_id          =  $item['variation_id'];
			if ( 0 != $variation_id ) {
				$r_price = get_post_meta( $variation_id, '_regular_price', true );
				$data    = $Wholesale_Price_class->wwp_variable_price_format( $r_price, wc_get_product( $product->get_parent_id() )  );
			} else {
				$r_price = get_post_meta( $item['product_id'], '_regular_price', true );
				$data    = $Wholesale_Price_class->wwp_change_product_price_display( $r_price, $product );
			}

			$lable     = !empty($settings['retailer_label']) ? $settings['retailer_label'] : 'Actual';
			$wholesale = strpos($data, $lable );
			$note      = '<div class="edit note_wholesale">' . __('The wholesale pricing will not be applied to the manually changed quantity and price. In order to apply the wholesale price, delete the product from the order and re-add it.', 'woocommerce-wholesale-pricing') . '</div>';
			$data      =  $data . $note;

			//if ( false === $wholesale ) {
				//$data = '';
			//} 
			$order = wc_get_order( $order_id );
			echo wp_kses_post( '<td class="wholesale_td_cost"> ' . wc_price( $order->get_item_subtotal( $item, false, true ), array( 'currency' => $order->get_currency() ) ) . ' </td>' );
			echo wp_kses_post( '<td class="wholesale_td replace_wholesale">' . $data . '</td>' );
		}

		public function action_woocommerce_new_order_item( $item_id, $item, $order_id ) {
			
			if ( 0 == $order_id ) {
				return;
			}
			
			if ( ! is_wholesaler_user( wc_get_order( $item->get_order_id() )->get_user_id() ) ) {
				return;
			} 
			if ( 'line_item' != $item->get_type() ) {
				return;
			}
			
			$variation_id = $item->get_variation_id();
			
			if ( 0 != $variation_id ) {
				$product = wc_get_product( $variation_id );
			} else {
				$product = wc_get_product( $item->get_product_id() );
			}
			if ( ! $product ) {
				return;
			} 
			 
			$Wholesale_Price_class = new WWP_Easy_Wholesale_Multiuser();
			$Wprice                = $Wholesale_Price_class->wwp_regular_price_change( get_post_meta( $product->get_id(), '_regular_price', true ) , $product );
			
			$role                       = get_current_user_role_id();
			$wwp_wholesaler_tax_classes = get_term_meta( $role , 'wwp_wholesaler_tax_classes', true);
			if ( !empty( $wwp_wholesaler_tax_classes ) ) {
				$item->set_tax_class($wwp_wholesaler_tax_classes);
			}
			
			if ( !empty($Wprice) ) {
				$item->set_total( $Wprice * $item->get_quantity() );
				$item->set_subtotal( $Wprice * $item->get_quantity() );
				$item->save();
			}
		}

		public function wwp_product_admin_scripts_styles() {
			global $post_type;
			if ( ( 'product' === $post_type ) || ( isset( $_GET['post_type'] ) && isset( $_GET['taxonomy'] ) && 'product' === $_GET['post_type'] && 'product_cat' === $_GET['taxonomy'] ) ) {
				// wp_enqueue_style('wwp-bootstrap', WWP_PLUGIN_URL . 'assets/css/bootstrap.min.css', array(), '4.5.3', 'all' );
				// wp_enqueue_style('wwp-bootstrap-select', WWP_PLUGIN_URL . 'assets/css/bootstrap-select.min.css', array(), '1.13.14', 'all' );
				wp_enqueue_style( 'wwp-product-admin', WWP_PLUGIN_URL . 'assets/css/product-admin.css', array(), '1.0', 'all' );
				// wp_enqueue_script('wwp-popper-min-js', WWP_PLUGIN_URL . 'assets/js/popper.min.js', array( 'jquery' ), '1.0.0' );
				wp_enqueue_script( 'wwp-bootstrap-js', WWP_PLUGIN_URL . 'assets/js/bootstrap.min.js', array( 'jquery' ), '4.5.3' );
				// wp_enqueue_script('wwp-bootstrap-select-js', WWP_PLUGIN_URL . 'assets/js/bootstrap-select.min.js', array( 'wwp-bootstrap-js' ), '1.13.14' );
				wp_enqueue_style( 'wwp-data-tip', WWP_PLUGIN_URL . 'assets/css/data-tip.min.css', array(), '1.0' );
			}
		}
		public function add_multiuser_role_metabox() {
			add_meta_box(
				'wholesale-pricing-pro-multiuser',
				esc_html__( 'Wholesale User Pricing', 'woocommerce-wholesale-pricing' ),
				array( $this, 'wholesale_multi_user_pricing_callback' ),
				'product',
				'normal',
				'high'
			);
		}
		public function wholesale_multi_user_pricing_callback() {
			global $post, $product;
			wp_nonce_field( 'wwp_wholesale_multi_user', 'wwp_wholesale_multi_user' );
			?>
			<div class="" id="wholesale-multiuser-pricing">
				<input type="hidden" value="<?php esc_attr_e( $post->ID ); ?>" name="product_id">
				<div class="wholesale_loader"></div>
				<div class="wholesale_container"></div>
			</div>
			<?php
		}
		public function retrieve_wholesale_multiuser_pricing() {

			check_ajax_referer( 'wwp_wholesale_pricing', 'security' );

			if ( ! isset( $_POST['product_id'] ) && ! is_numeric( $_POST['product_id'] ) && ! isset( $_POST['ptype'] ) && empty( $_POST['ptype'] ) ) {
				die();
			}
			$settings        = get_option( 'wwp_wholesale_pricing_options' );
			$product_id      = wc_clean( $_POST['product_id'] );
			$ptype           = wc_clean( $_POST['ptype'] );
			$data            = get_post_meta( $product_id, 'wholesale_multi_user_pricing', true );
			$wholesale_type  = get_post_meta( $product_id, '_wwp_wholesale_type', true );
			$wholesale_price = get_post_meta( $product_id, '_wwp_wholesale_amount', true );
			$roles           = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
			$rolehtml        = '';

			if ( 'variable' == $ptype || 'variable-subscription' == $ptype ) {
				$rolehtml     = $this->get_variable_product_wholesale( $product_id, $roles, $ptype  );
				$allowed_html = array(
					'input'      => array(
						'name'     => array(),
						'value'    => array(),
						'checked'  => array(),
						'class'    => array(),
						'id'       => array(),
						'type'     => array(),
						'readonly' => array(),
					),
					'select'     => array(
						'name'  => array(),
						'id'    => array(),
						'class' => array(),
						'value' => array(),
					),
					'option'     => array(
						'value'    => array(),
						'selected' => array(),

					),
					'a'          => array(
						'class' => array(),
						'href'  => array(),
						'rel'   => array(),
						'title' => array(),
					),
					'abbr'       => array(
						'title' => array(),
					),
					'b'          => array(),
					'blockquote' => array(
						'cite' => array(),
					),
					'cite'       => array(
						'title' => array(),
					),
					'code'       => array(),
					'del'        => array(
						'datetime' => array(),
						'title'    => array(),
					),
					'dd'         => array(),
					'div'        => array(
						'class' => array(),
						'title' => array(),
						'style' => array(),
					),
					'table'      => array(
						'id'    => array(),
						'class' => array(),
						'title' => array(),
						'style' => array(),
					),
					'tr'         => array(),
					'th'         => array(),
					'td'         => array(),
					'dl'         => array(),
					'dt'         => array(),
					'em'         => array(),
					'h1'         => array(),
					'h2'         => array(),
					'h3'         => array(),
					'h4'         => array(),
					'h5'         => array(),
					'h6'         => array(),
					'i'          => array(),
					'img'        => array(
						'alt'    => array(),
						'class'  => array(),
						'height' => array(),
						'src'    => array(),
						'width'  => array(),
					),
					'li'         => array(
						'class' => array(),
					),
					'ol'         => array(
						'class' => array(),
					),
					'p'          => array(
						'class' => array(),
					),
					'q'          => array(
						'cite'  => array(),
						'title' => array(),
					),
					'span'       => array(
						'class' => array(),
						'title' => array(),
						'style' => array(),
					),
					'strike'     => array(),
					'strong'     => array(),
					'ul'         => array(
						'class' => array(),
					),
				);
					// echo wp_kses( $rolehtml, $allowed_html );
			} elseif ( ! empty( $roles ) ) { 
					$product_tier_pricing = get_post_meta( $product_id, 'product_tier_pricing', true );
				if ( 'variable' === WC_Product_Factory::get_product_type( $product_id ) || 'variable-subscription' === WC_Product_Factory::get_product_type( $product_id ) ) {
					$product_tier_pricing = array();
				}
				?>
					<div id="accordion"> 
					<?php
					foreach ( $roles as $key => $role ) {
						$min      = 1;
						$price    = $wholesale_price;
						$discount = $wholesale_type;
						$step     = 1;
						if ( isset( $data[ $role->term_id ] ) ) {
							$min      = isset( $data[ $role->term_id ]['min_quatity'] ) ? $data[ $role->term_id ]['min_quatity'] : 1;
							$price    = isset( $data[ $role->term_id ]['wholesale_price'] ) ? $data[ $role->term_id ]['wholesale_price'] : $wholesale_price;
							$discount = isset( $data[ $role->term_id ]['discount_type'] ) ? $data[ $role->term_id ]['discount_type'] : $wholesale_type;
							$step     = isset( $data[ $role->term_id ]['step_quantity'] ) ? $data[ $role->term_id ]['step_quantity'] : 1;
						}
						if ( isset( $settings['wholesale_role'] ) && 'single' == $settings['wholesale_role'] && 'default_wholesaler' != $role->slug ) {
							continue;
						}
						?>
						<div class="card">
							<button onclick="return false;" class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse_<?php esc_attr_e( $role->term_id ); ?>" aria-expanded="false" aria-controls="collapse_<?php esc_attr_e( $role->term_id ); ?>">
							<?php esc_html_e( $role->name ); ?>
							<div class="wwp_signal">
							<?php
							$wwp_on_active  = '';
							$wwp_off_active = '';
							if ( isset( $data[ $role->term_id ] ) && ! empty( $data[ $role->term_id ] ) ) {
								$wwp_on_active = 'active';
							} else {
								$wwp_off_active = 'active';
							}

							?>
							<div class="wwp_circle wwp_circle_off <?php echo esc_attr( @$wwp_off_active ); ?> ">&nbsp;</div>
							<div class="wwp_circle wwp_circle_on <?php echo esc_attr( @$wwp_on_active ); ?> ">&nbsp;</div>										
							</div>
							</button>
							<div id="collapse_<?php esc_attr_e( $role->term_id ); ?>" class="collapse" aria-labelledby="heading_<?php esc_attr_e( $role->term_id ); ?>" data-parent="#accordion" style="">
							<div class="card-body">
							<table class="form-table wwp-main-settings">
							<tbody>
							<tr scope="row">
							<th>
							<label for=""><?php esc_html_e( 'Role Activation', 'woocommerce-wholesale-pricing' ); ?></label>
							</th>
							<td>
							<input class="inp-cbx  wwp-checbox" style="display: none" id="role_<?php esc_attr_e( $role->term_id ); ?>" type="checkbox" value="<?php esc_attr_e( $role->slug ); ?>" name="role_<?php esc_attr_e( $role->term_id ); ?>" <?php echo isset( $data[ $role->term_id ] ) ? 'checked' : ''; ?> >
							<label class="cbx cbx-square" for="role_<?php esc_attr_e( $role->term_id ); ?>">
							<span>
							<svg width="12px" height="9px" viewbox="0 0 12 9">
							<polyline points="1 5 4 8 11 1"></polyline>
							</svg>
							</span>
							<span><?php esc_html_e( 'Enable Role', 'woocommerce-wholesale-pricing' ); ?></span>
							</label>
							</td>
							</tr>
							
							<tr scope="row">
							<th>
							<label for=""><?php esc_html_e( 'Discount Type', 'woocommerce-wholesale-pricing' ); ?></label>
							</th>
							<td>													
							<select class="widefat" name="discount_type_<?php esc_attr_e( $role->term_id ); ?>" value="<?php esc_attr_e( $wholesale_type ); ?>">
							<option value="percent" <?php selected( $discount, 'percent' ); ?> > <?php esc_html_e( 'Percent', 'woocommerce-wholesale-pricing' ); ?> </option>
							<option value="fixed"  <?php selected( $discount, 'fixed' ); ?> > <?php esc_html_e( 'Fixed', 'woocommerce-wholesale-pricing' ); ?> </option>
							</select>
							<span data-tip="Price type for wholesale products" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
							</td>
							</tr>
							
							<tr scope="row">
							<th>
							<label for=""><?php esc_html_e( 'Wholesale Price', 'woocommerce-wholesale-pricing' ); ?></label>
							</th>
							<td>
							<input class="widefat wwp-price " type="text" name="wholesale_price_<?php esc_attr_e( $role->term_id ); ?>" value="<?php esc_attr_e( $price ); ?>">
							<span data-tip="Enter the value you would like to change the Wholesale User" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
							</td>
							</tr>
							
							<tr scope="row">
							<th>
							<label for=""><?php esc_html_e( 'Min Quantity', 'woocommerce-wholesale-pricing' ); ?></label>
							</th>
							<td>
							<input class="widefat" type="number" name="min_quatity_<?php esc_attr_e( $role->term_id ); ?>" min="1" value="<?php esc_attr_e( $min ); ?>">
							<span data-tip="Enter Wholesale minimum quantity to apply discount" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
							</td>
							</tr>
							<tr scope="row">
							<th>
							<label for=""><?php esc_html_e( 'Step Quantity', 'woocommerce-wholesale-pricing' ); ?></label>
							</th>
							<td>
							<input class="widefat" type="text" name="step_quantity_<?php esc_attr_e( $role->term_id ); ?>" min="1" value="<?php esc_attr_e( $step ); ?>">
							<span data-tip="Enter Wholesale step quantity" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
							</td>
							</tr>
							<tr scope="row">
							<th>
							<label for=""><?php esc_html_e( 'Product Tier Pricing', 'woocommerce-wholesale-pricing' ); ?></label>
							</th>
							<td>
							<button data-toggle="modal" data-target="#product_tier_pricing<?php esc_attr_e( $role->term_id ); ?>" class="wwp-button-primary" type="button"><?php esc_html_e( 'Add Tier Pricing', 'woocommerce-wholesale-pricing' ); ?></button>
							<span class="wwwp_help_text"><?php esc_html_e( 'Product Tier Pricing', 'woocommerce-wholesale-pricing' ); ?></span>
							<?php
							$name = 'product_tier_pricing[tier_pricing]';
							echo wp_kses_post( '<div>' . tier_pricing_modal_popup( 'Product Tier Pricing', 'product_tier_pricing' . $role->term_id, $role->term_id, $product_tier_pricing, $name, $product_id ) . '</div>' );
							?>
							</td>
							</tr>
							</tbody>
							</table>
							</div>
							</div>
							</div>
							<?php
					}
					?>
						</div>
					<?php
			} else {
				esc_html_e( 'Please add Wholesale user roles first', 'woocommerce-wholesale-pricing' );
			}
			die();
		}
		public function get_variable_product_wholesale( $product_id, $roles, $ptype ) {
			if ( ! empty( $product_id ) && ! empty( $roles ) ) {
			
				$wholesale_type       = get_post_meta( $product_id, '_wwp_wholesale_type', true );
				$data                 = get_post_meta( $product_id, 'wholesale_multi_user_pricing', true );
				$product_tier_pricing = get_post_meta( $product_id, 'product_tier_pricing', true );
				$tickets              = new WC_Product_Variable( $product_id );
				$variables            = $tickets->get_children();
				$settings             = get_option( 'wwp_wholesale_pricing_options' );
				
				// ob_start();
				if ( ! empty( $variables ) ) {
					?>
					<div id="accordion">
						<?php
						foreach ( $roles as $key => $role ) {
							$discount = '';
							
							if ( isset( $data[ $role->term_id ]['discount_type'] ) ) {
								$discount = $data[ $role->term_id ]['discount_type'];
							}
							if ( isset( $settings['wholesale_role'] ) && 'single' == $settings['wholesale_role'] && 'default_wholesaler' != $role->slug ) {
								continue;
							}
							?>
							<div class="card">
								<button onclick="return false;" class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse_<?php esc_attr_e( $role->term_id ); ?>" aria-expanded="false" aria-controls="collapse_<?php esc_attr_e( $role->term_id ); ?>">
									<?php esc_html_e( $role->name ); ?>
									<div class="wwp_signal">
										<?php
										$wwp_on_active  = '';
										$wwp_off_active = '';
										if ( isset( $data[ $role->term_id ] ) && ! empty( $data[ $role->term_id ] ) ) {
											$wwp_on_active = 'active';
										} else {
											$wwp_off_active = 'active';
										}

										?>
										<div class="wwp_circle wwp_circle_off <?php echo esc_attr( @$wwp_off_active ); ?> ">&nbsp;</div>
										<div class="wwp_circle wwp_circle_on <?php echo esc_attr( @$wwp_on_active ); ?> ">&nbsp;</div>										
									</div>
								</button>
								<div id="collapse_<?php esc_attr_e( $role->term_id ); ?>" class="collapse" aria-labelledby="heading_<?php esc_attr_e( $role->term_id ); ?>" data-parent="#accordion" style="">
									<div class="card-body">
										<table class="form-table wwp-main-settings">
											<tbody>
												<tr scope="row">
													<th>
														<label for=""><?php esc_html_e( 'Role Activation', 'woocommerce-wholesale-pricing' ); ?></label>
													</th>
													<td>
														<input class="inp-cbx" style="display: none" id="role_<?php esc_attr_e( $role->term_id ); ?>" type="checkbox" value="<?php esc_attr_e( $role->slug ); ?>" name="role_<?php esc_attr_e( $role->term_id ); ?>" <?php echo isset( $data[ $role->term_id ] ) ? 'checked' : ''; ?> >
														<label class="cbx cbx-square" for="role_<?php esc_attr_e( $role->term_id ); ?>">
															<span>
																<svg width="12px" height="9px" viewbox="0 0 12 9">
																	<polyline points="1 5 4 8 11 1"></polyline>
																</svg>
															</span>
															<span><?php esc_html_e( 'Enable Role', 'woocommerce-wholesale-pricing' ); ?></span>
														</label>
													</td>
												</tr>

												<tr scope="row">
													<th>
														<label for=""><?php esc_html_e( 'Discount Type', 'woocommerce-wholesale-pricing' ); ?></label>
													</th>
													<td>				        									
														<select class="widefat" name="discount_type_<?php esc_attr_e( @$role->term_id ); ?>" value="<?php esc_attr_e( @$wholesale_type ); ?>">
															<option value="percent" <?php selected( @$discount, 'percent' ); ?> > <?php esc_html_e( 'Percent', 'woocommerce-wholesale-pricing' ); ?> </option>
															<option value="fixed"  <?php selected( @$discount, 'fixed' ); ?> > <?php esc_html_e( 'Fixed', 'woocommerce-wholesale-pricing' ); ?> </option>
														</select>
														<span data-tip="<?php esc_html_e( 'Price type for wholesale products', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
													</td>
												</tr>
											</tbody>
										</table>
												
										<?php
										foreach ( $variables as $key ) {
											$variation_id = $key;
											$variation = wc_get_product($variation_id);
											// $formatted_name = preg_replace( '/<span[^>]*>.*?<\/span>/is', '', $variation->get_formatted_name() );
											// $formatted_name = str_replace( 'Product - ', '', $formatted_name );
											$formatted_name = '#' . $variation_id . ' ( ' . wc_get_formatted_variation( $variation, true, false, false ) . ' )';
											$step = 1;
											if ( isset( $data[ $role->term_id ][ $variation_id ] ) ) {
												$wholesale_price = $data[ $role->term_id ][ $variation_id ]['wholesaleprice'];
												$qty             = $data[ $role->term_id ][ $variation_id ]['qty'];
												$step            = $data[ $role->term_id ][ $variation_id ]['step'];
											} else {
												$wholesale_price = get_post_meta( $key, '_regular_price', true );
												$qty             = 1;
											}
											$regular_price        = get_post_meta( $key, '_regular_price', true );
											$wholesale_field_name = 'wholesaleprice_' . esc_attr( $role->term_id ) . '_' . esc_attr( $key );
											$qty_field_name       = 'qty_' . esc_attr( $role->term_id ) . '_' . esc_attr( $key );
											$step_field_name      = 'step_' . esc_attr( $role->term_id ) . '_' . esc_attr( $key );
											$quanity_label        = esc_html__( 'Quantity: ', 'woocommerce-wholesale-pricing' );
											?>
											<table class="form-table variable-item-table">
												<tbody>
													<tr scope="row">
														<th colspan="2" class="row-heading">
															<label for=""><?php esc_html_e( 'Wholesale Price & Min Qty ' . $formatted_name, 'woocommerce-wholesale-pricing' ); ?></label>
														</th>
													</tr>
													<tr class="variable-item">
														<th>
															<label><?php esc_html_e( 'Regular Price', 'woocommerce-wholesale-pricing' ); ?></label>
														</th>
														<td>
															<input type="text" readonly name="reg-price" value="<?php esc_attr_e( $regular_price ); ?>"/>
														</td>
													</tr>

													<tr>
														<th>
															<label><?php esc_html_e( 'Wholesale Price', 'woocommerce-wholesale-pricing' ); ?></label>
														</th>
														<td>
															<input type="text" name="<?php esc_attr_e( $wholesale_field_name ); ?>" value="<?php esc_attr_e( $wholesale_price ); ?>"/>
															<span data-tip="<?php esc_html_e( 'Enter the value you would like to change the Wholesale User', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
														</td>
													</tr>

													<tr>
														<th>
															<label><?php esc_html_e( $quanity_label, 'woocommerce-wholesale-pricing' ); ?></label>
														</th>
														<td>
															<input type="text" class="qty" name="<?php esc_attr_e( $qty_field_name ); ?>" value="<?php esc_attr_e( $qty ); ?>"/>
															<span data-tip="<?php esc_html_e( 'Enter Wholesale minimum quantity to apply discount', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
														</td>														
														<input type="hidden" name="product_type_<?php esc_attr_e( $product_id ); ?>" value="variable">
														<input type="hidden" name="prod_id_<?php esc_attr_e( $product_id ); ?>" value="<?php esc_attr_e( $product_id ); ?>">
													</tr>
													<tr>
														<th>
															<label><?php esc_html_e( 'Step Quantity', 'woocommerce-wholesale-pricing' ); ?></label>
														</th>
														<td>
															<input type="text" class="step" name="<?php esc_attr_e( $step_field_name ); ?>" value="<?php esc_attr_e( @$step ); ?>"/>
															<span data-tip="<?php esc_html_e( 'Enter Wholesale step quantity', 'woocommerce-wholesale-pricing' ); ?>" class="data-tip-top"><span class="woocommerce-help-tip"></span></span>
														</td>														
														
													</tr>
													<tr scope="row">
													<th>
													<label for=""><?php esc_html_e( 'Product Tier Pricing', 'woocommerce-wholesale-pricing' ); ?></label>
													</th>
													<td>
													<button data-toggle="modal" data-target="#product_tier_pricing<?php esc_attr_e( $role->term_id ) . esc_attr_e( $variation_id ); ?>" class="wwp-button-primary" type="button"><?php esc_html_e( 'Add Tier Pricing', 'woocommerce-wholesale-pricing' ); ?></button>
													<span class="wwwp_help_text"><?php esc_html_e( 'Product Tier Pricing', 'woocommerce-wholesale-pricing' ); ?></span><br><br>
													<?php
													$name = 'product_tier_pricing[tier_pricing]';
													$tier_popup = tier_pricing_modal_popup( 'Product Tier Pricing #' . $variation_id, 'product_tier_pricing' . $role->term_id . $variation_id, $role->term_id, $product_tier_pricing, $name, $variation_id );
													echo $tier_popup ? wp_kses_post( $tier_popup ) : '';
													?>
													</td>
													</tr>
													
												</tbody>
											</table>
											<?php
										}
										?>
										</tbody>
										</table>
									</div>
								</div>
							</div>
							<?php
						}
						?>
					</div>
					<input type="hidden" name="wholesale_product_type" value="<?php echo esc_attr( $ptype ); ?>">
					<?php
				} else {
					esc_html_e( 'No variations found. Add variations before.', 'woocommerce-wholesale-pricing' );
				}
			} else {
				esc_html_e( 'Wholesale Roles not found', 'woocommerce-wholesale-pricing' );
			}
			// return ob_get_clean();
		}
		public function save_wholesale_metabox_data( $post_id ) {
			// Autosave
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}
			// AJAX
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				return;
			}
			if ( ! isset( $_POST['wwp_wholesale_multi_user'] ) || ! wp_verify_nonce( wc_clean( $_POST['wwp_wholesale_multi_user'] ), 'wwp_wholesale_multi_user' ) ) {
				return;
			}
			$roles = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
			
			$product_tier_pricing = array();

			if ( isset( $_POST['product_tier_pricing'] ) && !empty( $_POST['product_tier_pricing']['tier_pricing'] ) ) {
				$product_tier_pricing = wc_clean( $_POST['product_tier_pricing']['tier_pricing'] );
			}
			$product = wc_get_product($post_id); 
			
			$data = array();
			if ( ! empty( $roles ) ) {
				if ( isset( $_POST['product-type'] ) && isset( $_POST['wholesale_product_type'] ) && ( 'variable' == wc_clean( $_POST['wholesale_product_type'] ) || 'variable-subscription' == wc_clean( $_POST['wholesale_product_type'] ) ) ) {
					
					$wholesale_product_type = wc_clean( $_POST['wholesale_product_type'] );
					if ( 'variable-subscription' == $wholesale_product_type ) {
						$tickets = new WC_Product_Variable_Subscription( $post_id );
					} else {
						$tickets   = new WC_Product_Variable( $post_id );
					}

					$variables = $tickets->get_available_variations();
					if ( ! empty( $variables ) ) {
						foreach ( $variables as $variable ) {
							$variation_id = $variable['variation_id'];
							if ( 'variable-subscription' == $wholesale_product_type ) {
								$variation_obj = new WC_Product_Subscription_Variation( $variation_id );
							} else {
								$variation_obj = new WC_Product_Variation( $variation_id );
							}
							$vary         = array();
							foreach ( $roles as $key => $role ) {
								if ( ! isset( $_POST[ 'role_' . $role->term_id ] ) ) {
									continue;
								}
								if ( isset( $_POST[ 'role_' . $role->term_id ] ) ) {
									$data[ $role->term_id ]['slug'] = $role->slug;
									$vary[ $role->term_id ]['slug'] = $role->slug;
								}
								if ( isset( $_POST[ 'discount_type_' . $role->term_id ] ) ) {
									$data[ $role->term_id ]['discount_type'] = wc_clean( $_POST[ 'discount_type_' . $role->term_id ] );
									$vary[ $role->term_id ]['discount_type'] = wc_clean( $_POST[ 'discount_type_' . $role->term_id ] );
								}
								if ( isset( $_POST[ 'wholesaleprice_' . $role->term_id . '_' . $variation_id ] ) ) {
									$data[ $role->term_id ][ $variation_id ]['wholesaleprice'] = is_numeric( wc_clean( $_POST[ 'wholesaleprice_' . $role->term_id . '_' . $variation_id ] ) ) ? wc_clean( $_POST[ 'wholesaleprice_' . $role->term_id . '_' . $variation_id ] ) : '';
									$vary[ $role->term_id ][ $variation_id ]['wholesaleprice'] = is_numeric( wc_clean( $_POST[ 'wholesaleprice_' . $role->term_id . '_' . $variation_id ] ) ) ? wc_clean( $_POST[ 'wholesaleprice_' . $role->term_id . '_' . $variation_id ] ) : '';
								}
								if ( isset( $_POST[ 'qty_' . $role->term_id . '_' . $variation_id ] ) ) {
									$data[ $role->term_id ][ $variation_id ]['qty'] = is_numeric( wc_clean( $_POST[ 'qty_' . $role->term_id . '_' . $variation_id ] ) ) ? wc_clean( $_POST[ 'qty_' . $role->term_id . '_' . $variation_id ] ) : 1;
									$vary[ $role->term_id ][ $variation_id ]['qty'] = is_numeric( wc_clean( $_POST[ 'qty_' . $role->term_id . '_' . $variation_id ] ) ) ? wc_clean( $_POST[ 'qty_' . $role->term_id . '_' . $variation_id ] ) : 1;
								}
								if ( isset( $_POST[ 'step_' . $role->term_id . '_' . $variation_id ] ) ) {
									$data[ $role->term_id ][ $variation_id ]['step'] = is_numeric( wc_clean( $_POST[ 'step_' . $role->term_id . '_' . $variation_id ] ) ) ? wc_clean( $_POST[ 'step_' . $role->term_id . '_' . $variation_id ] ) : '';
									$vary[ $role->term_id ][ $variation_id ]['step'] = is_numeric( wc_clean( $_POST[ 'step_' . $role->term_id . '_' . $variation_id ] ) ) ? wc_clean( $_POST[ 'step_' . $role->term_id . '_' . $variation_id ] ) : '';
								}
							}
							//update_post_meta( $variation_id, 'wholesale_multi_user_pricing', $vary );
							//update_post_meta( $variation_id, 'product_tier_pricing', $product_tier_pricing );
							$variation_obj->update_meta_data('wholesale_multi_user_pricing', $vary);
							$variation_obj->update_meta_data('product_tier_pricing', $product_tier_pricing);
							$variation_obj->save();
						}
						//update_post_meta( $post_id, 'wholesale_multi_user_pricing', $data );
						//update_post_meta( $post_id, 'product_tier_pricing', $product_tier_pricing );
						$product->update_meta_data('wholesale_multi_user_pricing', $data );
						$product->update_meta_data('product_tier_pricing', $product_tier_pricing );
					}
				} else {
					foreach ( $roles as $key => $role ) {
						if ( ! isset( $_POST[ 'role_' . $role->term_id ] ) ) {
							continue;
						}
						if ( isset( $_POST[ 'role_' . $role->term_id ] ) ) {
							$data[ $role->term_id ]['slug'] = $role->slug;
						}
						if ( isset( $_POST[ 'discount_type_' . $role->term_id ] ) ) {
							$data[ $role->term_id ]['discount_type'] = wc_clean( $_POST[ 'discount_type_' . $role->term_id ] );
						}
						if ( isset( $_POST[ 'wholesale_price_' . $role->term_id ] ) ) {
							$data[ $role->term_id ]['wholesale_price'] = is_numeric( wc_clean( $_POST[ 'wholesale_price_' . $role->term_id ] ) ) ? wc_clean( $_POST[ 'wholesale_price_' . $role->term_id ] ) : '';
						}
						if ( isset( $_POST[ 'min_quatity_' . $role->term_id ] ) ) {
							$data[ $role->term_id ]['min_quatity'] = is_numeric( wc_clean( $_POST[ 'min_quatity_' . $role->term_id ] ) ) ? wc_clean( $_POST[ 'min_quatity_' . $role->term_id ] ) : 1;
						}
						if ( isset( $_POST[ 'step_quantity_' . $role->term_id ] ) ) {
							$data[ $role->term_id ]['step_quantity'] = is_numeric( wc_clean( $_POST[ 'step_quantity_' . $role->term_id ] ) ) ? wc_clean( $_POST[ 'step_quantity_' . $role->term_id ] ) : '';
						}
					}
					//update_post_meta( $post_id, 'product_tier_pricing', $product_tier_pricing );
					$product->update_meta_data('wholesale_multi_user_pricing', $data );
					$product->update_meta_data('product_tier_pricing', $product_tier_pricing );
					
				}
			}
			if ( isset( $_POST['wholesale_product_visibility_multi'] ) ) {
				//update_post_meta( $post_id, 'wholesale_product_visibility_multi', (array) wc_clean( $_POST['wholesale_product_visibility_multi'] ) );
				$product->update_meta_data('wholesale_product_visibility_multi', (array) wc_clean( $_POST['wholesale_product_visibility_multi'] ) );
			} else {
				//update_post_meta( $post_id, 'wholesale_product_visibility_multi', '' );
				$product->update_meta_data( 'wholesale_product_visibility_multi', '' );
			}
			//update_post_meta( $post_id, 'wholesale_multi_user_pricing', $data );
			$product->update_meta_data( 'wholesale_multi_user_pricing', $data );
			$product->save();
		}
	}
	new Wwp_Wholesale_Product_Metabox();
}
