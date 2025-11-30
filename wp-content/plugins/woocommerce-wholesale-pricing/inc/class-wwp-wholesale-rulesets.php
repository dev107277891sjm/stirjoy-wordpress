<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Class To Add Wholesale Functionality with WooCommerce
 */
if ( ! class_exists( 'Wwp_Wholesale_Rulesets' ) ) {

	class Wwp_Wholesale_Rulesets {

		public function __construct() {
			add_action( 'product_cat_add_form_fields', array( $this, 'wwp_add_new_field' ), 10 );
			add_action( 'product_cat_edit_form_fields', array( $this, 'wwp_edit_new_field' ), 10, 1 );
			add_action( 'edited_product_cat', array( $this, 'wwp_save_new_field' ), 10, 2 );
			add_action( 'create_product_cat', array( $this, 'wwp_save_new_field' ), 10, 2 );
		}

		public function wwp_add_new_field() {
			wp_nonce_field( 'wwp_wholeset_ruleset_nonce', 'wwp_wholeset_ruleset_nonce' );
			$settings = get_option( 'wwp_wholesale_pricing_options', true );
			$roles    = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
			if ( ! empty( $roles ) ) {
				?>
			<div class="form-field term-visibility-wrap">
				<label for="wholesale_product_visibility_multi"><?php esc_html_e( 'Hide Product for Wholesaler Roles', 'woocommerce-wholesale-pricing' ); ?></label>
				<select name="wholesale_product_visibility_multi[]" id="wholesale_product_visibility_multi" class="widefat wc-enhanced-select" multiple>
				<?php
				foreach ( $roles as $key => $role ) {
					echo '<option value="' . esc_attr( $role->slug ) . '">' . esc_html( $role->name ) . '</option>';
				}
				?>
				</select>
				<p><?php esc_html_e( 'Select specific user roles to hide the products of this category.', 'woocommerce-wholesale-pricing' ); ?></p>
			</div>
			<h1><?php esc_html_e( 'Wholesale Role', 'woocommerce-wholesale-pricing' ); ?></h1>
			<div id="accordion">
				<?php
				foreach ( $roles as $key => $role ) {
					$min      = 1;
					$step      = 1;
					$price    = '';
					$discount = '';
					if ( isset( $settings['wholesale_role'] ) && 'single' == $settings['wholesale_role'] && 'default_wholesaler' != $role->slug ) {
						continue;
					}
					?>
					<div class="card">
						<button onclick="return false;" class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse_<?php esc_attr_e( $role->term_id ); ?>" aria-expanded="false" aria-controls="collapse_<?php esc_attr_e( $role->term_id ); ?>">
							<?php esc_html_e( $role->name ); ?>
						</button>
						<div id="collapse_<?php esc_attr_e( $role->term_id ); ?>" class="collapse" aria-labelledby="heading_<?php esc_attr_e( $role->term_id ); ?>" data-parent="#accordion" style="">
							<div class="card-body">
								<table class="form-table wwp-main-settings">
									<tbody>
										<tr scope="row">
											<td colspan="2">
												<input class="inp-cbx wwp-checbox" style="display: none" type="checkbox" value="<?php esc_attr_e( $role->slug ); ?>" id="role_<?php esc_attr_e( $role->term_id ); ?>" name="role_<?php esc_attr_e( $role->term_id ); ?>" <?php echo isset( $data[ $role->term_id ] ) ? 'checked' : ''; ?> >
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
											<td colspan="2">
												<label for=""><?php esc_html_e( 'Discount Type', 'woocommerce-wholesale-pricing' ); ?></label><br>
												<select class="regular-text" name="discount_type_<?php esc_attr_e( $role->term_id ); ?>" value="">
													<option value="percent" <?php selected( $discount, 'percent' ); ?> > <?php esc_html_e( 'Percent', 'woocommerce-wholesale-pricing' ); ?> </option>
													<option value="fixed"  <?php selected( $discount, 'fixed' ); ?> > <?php esc_html_e( 'Fixed', 'woocommerce-wholesale-pricing' ); ?> </option>
												</select>
												<span class="wwwp_help_text"><?php esc_html_e( 'Price type for wholesale products', 'woocommerce-wholesale-pricing' ); ?></span>
											</td>
										</tr>

										<tr scope="row">
											<td colspan="2">
												<label for=""><?php esc_html_e( 'Wholesale Price', 'woocommerce-wholesale-pricing' ); ?></label><br>
												<input class="regular-text wwp-price" type="text" name="wholesale_price_<?php esc_attr_e( $role->term_id ); ?>" value="<?php esc_attr_e( $price ); ?>">
												<span class="wwwp_help_text"><?php esc_html_e( 'Enter the value you would like to change the Wholesale User', 'woocommerce-wholesale-pricing' ); ?></span>
											</td>
										</tr>

										<tr scope="row">
											<td colspan="2">
												<label for=""><?php esc_html_e( 'Min Quantity', 'woocommerce-wholesale-pricing' ); ?></label><br>
												<input class="regular-text " type="number" name="min_quatity_<?php esc_attr_e( $role->term_id ); ?>" value="<?php esc_attr_e( $min ); ?>">
												<span class="wwwp_help_text"><?php esc_html_e( 'Enter Wholesale minimum quantity to apply discount', 'woocommerce-wholesale-pricing' ); ?></span>
											</td>
										</tr>
										<tr scope="row">
											<td colspan="2">
												<label for=""><?php esc_html_e( 'Step Quantity', 'woocommerce-wholesale-pricing' ); ?></label><br>
												<input class="regular-text " type="text" name="step_quantity_<?php esc_attr_e( $role->term_id ); ?>" value="<?php esc_attr_e( $step ); ?>">
												<span class="wwwp_help_text"><?php esc_html_e( 'Enter Wholesale step quantity', 'woocommerce-wholesale-pricing' ); ?></span>
											</td>
										</tr>
										<tr scope="row">

											<td>
												<button data-toggle="modal" data-target="#category_tier_pricing_Modal<?php esc_attr_e( $role->term_id ); ?>" class="wwp-button-primary" type="button">Add Tier Pricing</button>
												<?php
												$name = nl2br( 'category_tier_pricing[tier_pricing]' );
												echo wp_kses_post( tier_pricing_modal_popup( 'Category Tier Pricing', 'category_tier_pricing_Modal' . $role->term_id, $role->term_id, '', $name, '' ) );
												?>
												<span class="wwwp_help_text"><?php esc_html_e( 'Category Tier Pricing', 'woocommerce-wholesale-pricing' ); ?></span>
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
			}
		}
		public function wwp_edit_new_field( $term ) {
			wp_nonce_field( 'wwp_wholeset_ruleset_nonce', 'wwp_wholeset_ruleset_nonce' );
			$settings = get_option( 'wwp_wholesale_pricing_options', true );
			$roles    = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
			if ( ! empty( $roles ) ) {
				$data              = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );
				$cate_tier_pricing = get_term_meta( $term->term_id, 'category_tier_pricing', true );
				$roles_selected = get_term_meta( $term->term_id, 'wholesale_product_visibility_multi', true );
				$non_wholesaler = get_term_meta( $term->term_id, 'wholesale_product_visibility_multi_customer', true );
				?>
			<tr class="form-field term-visibility-wrap">
				<th>
					<label for="wholesale_product_visibility_multi">
						<?php esc_html_e( 'Hide Product for Wholesaler Roles', 'woocommerce-wholesale-pricing' ); ?>
					</label>
				</th>
				<td scope="row">
					<select name="wholesale_product_visibility_multi[]" id="wholesale_product_visibility_multi" class="regular-text wc-enhanced-select" multiple>
					<?php
					foreach ( $roles as $key => $role ) {
						$selected = ( ! empty( $roles_selected ) && in_array( $role->slug, $roles_selected ) ) ? 'selected' : '';
						echo '<option value="' . esc_attr( $role->slug ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $role->name ) . '</option>';
					}
					?>
					</select>
					<p><?php esc_html_e( 'Select specific user roles to hide the products of this category.', 'woocommerce-wholesale-pricing' ); ?></p>
				</td>
			</tr>
			<tr>
				<th>
					<label for="wholesale_product_visibility_multi_customer">
						<?php esc_html_e( 'Hide Product for Non Wholesale User', 'woocommerce-wholesale-pricing' ); ?>
					</label>
				</th>
				<td scope="row">
					<input name="wholesale_product_visibility_multi_customer" id="wholesale_product_visibility_multi_customer" type="checkbox" value="yes" <?php echo isset( $non_wholesaler[0] ) && 'yes' == $non_wholesaler[0] ? 'checked' : ''; ?> >
					<p><?php esc_html_e( 'Check this option to hide this category from non wholesale user.', 'woocommerce-wholesale-pricing' ); ?></p>
				</td>
			</tr>
			<tr>
				<td colspan="2">
				<h1>Wholesale Role</h1>
					<div id="accordion">
						<?php
						foreach ( $roles as $key => $role ) {
							$min      = 1;
							$price    = '';
							$discount = '';
							$step      = '';
							if ( isset( $settings['wholesale_role'] ) && 'single' == $settings['wholesale_role'] && 'default_wholesaler' != $role->slug ) {
								continue;
							}
							if ( isset( $data[ $role->term_id ] ) ) {
								$min      = $data[ $role->term_id ]['min_quatity'];
								$price    = $data[ $role->term_id ]['wholesale_price'];
								$discount = $data[ $role->term_id ]['discount_type'];
								if (isset($data[ $role->term_id ]['step_quantity'])) {
									$step = $data[ $role->term_id ]['step_quantity'];
								}
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
														<input class="inp-cbx  wwp-checbox" style="display: none" type="checkbox" value="<?php esc_attr_e( $role->slug ); ?>" id="role_<?php esc_attr_e( $role->term_id ); ?>" name="role_<?php esc_attr_e( $role->term_id ); ?>" <?php echo isset( $data[ $role->term_id ] ) ? 'checked' : ''; ?> >
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
														<select class="regular-text" name="discount_type_<?php esc_attr_e( $role->term_id ); ?>" value="">
															<option value="percent" <?php selected( $discount, 'percent' ); ?> > <?php esc_html_e( 'Percent', 'woocommerce-wholesale-pricing' ); ?> </option>
															<option value="fixed"  <?php selected( $discount, 'fixed' ); ?> > <?php esc_html_e( 'Fixed', 'woocommerce-wholesale-pricing' ); ?> </option>
														</select>
														<span class="wwwp_help_text"><?php esc_html_e( 'Price type for wholesale products', 'woocommerce-wholesale-pricing' ); ?></span>
													</td>
												</tr>

												<tr scope="row">
													<th>
														<label for=""><?php esc_html_e( 'Wholesale Price', 'woocommerce-wholesale-pricing' ); ?></label>
													</th>
													<td>
														<input class="regular-text wwp-price " type="text" name="wholesale_price_<?php esc_attr_e( $role->term_id ); ?>" value="<?php esc_attr_e( $price ); ?>">
														<span class="wwwp_help_text"><?php esc_html_e( 'Enter the value you would like to change the Wholesale User', 'woocommerce-wholesale-pricing' ); ?> </span>
													</td>
												</tr>

												<tr scope="row">
													<th>
														<label for=""><?php esc_html_e( 'Min Quantity', 'woocommerce-wholesale-pricing' ); ?></label>
													</th>
													<td>
														<input class="regular-text " type="number" name="min_quatity_<?php esc_attr_e( $role->term_id ); ?>" value="<?php esc_attr_e( $min ); ?>">
														<span class="wwwp_help_text"><?php esc_html_e( 'Enter Wholesale minimum quantity to apply discount', 'woocommerce-wholesale-pricing' ); ?></span>
													</td>
												</tr>
												<tr scope="row">
													<th>
														<label for=""><?php esc_html_e( 'Step Quantity', 'woocommerce-wholesale-pricing' ); ?></label>
													</th>
													<td>
														<input class="regular-text " type="text" name="step_quantity_<?php esc_attr_e( $role->term_id ); ?>" value="<?php esc_attr_e( $step ); ?>">
														<span class="wwwp_help_text"><?php esc_html_e( 'Enter Wholesale step quantity', 'woocommerce-wholesale-pricing' ); ?></span>
													</td>
												</tr>												
												<tr scope="row">
													<th>
														<label for=""><?php esc_html_e( 'Category Tier Pricing', 'woocommerce-wholesale-pricing' ); ?></label>
													</th>
													<td>
														<button data-toggle="modal" data-target="#category_tier_pricing_Modal<?php esc_attr_e( $role->term_id ); ?>" class="wwp-button-primary" type="button">Add Tier Pricing</button>
														<?php
														$name = 'category_tier_pricing[tier_pricing]';
														if ( isset( $cate_tier_pricing['tier_pricing'] ) ) {
															$cate_tier_pricing = $cate_tier_pricing['tier_pricing'];
														}
														echo wp_kses_post( '<div>' . tier_pricing_modal_popup( 'Category Tier Pricing', 'category_tier_pricing_Modal' . $role->term_id, $role->term_id, $cate_tier_pricing, $name, '' ) . '</div>' );
														?>
														<span class="wwwp_help_text"><?php esc_html_e( 'Category Tier Pricing', 'woocommerce-wholesale-pricing' ); ?></span>
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
				</td>
			</tr>
				<?php
			}
		}
		public function wwp_save_new_field( $term_id, $term ) {
			if ( ! isset( $_POST['wwp_wholeset_ruleset_nonce'] ) || ! wp_verify_nonce( wc_clean( $_POST['wwp_wholeset_ruleset_nonce'] ), 'wwp_wholeset_ruleset_nonce' ) ) {
				return;
			}
			$roles = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
			$data  = array();

			if ( ! empty( $roles ) ) {
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
			}

			update_term_meta( $term_id, 'wholesale_multi_user_pricing', $data );
			if ( isset( $_POST['wholesale_product_visibility_multi'] ) ) {
				update_term_meta( $term_id, 'wholesale_product_visibility_multi', (array) wc_clean( $_POST['wholesale_product_visibility_multi'] ) );
			} else {
				update_term_meta( $term_id, 'wholesale_product_visibility_multi', '' );
			}
			if ( isset( $_POST['wholesale_product_visibility_multi_customer'] ) ) {
				update_term_meta( $term_id, 'wholesale_product_visibility_multi_customer', (array) wc_clean( $_POST['wholesale_product_visibility_multi_customer'] ) );
			} else {
				update_term_meta( $term_id, 'wholesale_product_visibility_multi_customer', '' );
			}
			if ( isset( $_POST['category_tier_pricing'] ) ) {
				update_term_meta( $term_id, 'category_tier_pricing', (array) wc_clean( $_POST['category_tier_pricing'] ) );
			} else {
				update_term_meta( $term_id, 'category_tier_pricing', '' );
			}
		}
	}
	new Wwp_Wholesale_Rulesets();
}
