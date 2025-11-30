<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Class Woo_Wholesale_User_Roles
 */
if ( ! class_exists( 'WWP_Wholesale_User_Roles' ) ) {

	class WWP_Wholesale_User_Roles {

		public function __construct() {

			add_action( 'init', array( $this, 'wwp_add_default_role' ), 3 );
			add_action( 'init', array( $this, 'register_taxonomy_for_users' ), 5 );
			add_action( 'admin_menu', array( $this, 'register_menu_for_user_roles' ));
			add_action( 'created_wholesale_user_roles', array( $this, 'set_term_to_user_role' ), 10, 2 );
			add_action( 'delete_wholesale_user_roles', array( $this, 'remove_term_and_user_role' ), 10, 3 );
			add_action( 'edit_wholesale_user_roles', array( $this, 'edit_term_and_user_role' ), 10, 2 );
			add_action( 'wp_head', array( $this, 'print_css_styles' ) );
			add_action( 'wholesale_user_roles_add_form_fields', array( $this, 'wwp_add_new_field' ), 10 );
			add_action( 'wholesale_user_roles_edit_form_fields', array( $this, 'wwp_edit_new_field' ), 10, 1 );
			add_action( 'edited_wholesale_user_roles', array( $this, 'wwp_save_new_field' ), 10, 2 );
			add_action( 'create_wholesale_user_roles', array( $this, 'wwp_save_new_field' ), 10, 2 );
			add_filter( 'wholesale_user_roles_row_actions', array( $this, 'remove_row_actions' ) , 10, 2 );
			add_filter( 'manage_edit-wholesale_user_roles_columns', array( $this, 'wwp_modify_taxonomy_columns' ));
			add_filter( 'manage_wholesale_user_roles_custom_column', array( $this, 'wwp_custom_taxonomy_term_count_link' ), 10, 3 );
			add_filter( 'manage_edit-wholesale_user_roles_sortable_columns', array( $this, 'wwp_make_count_column_sortable' ) );
			add_action( 'pre_get_posts', array( $this, 'wwp_sort_by_count_column' ) );
		}

		public function wwp_sort_by_count_column( $query ) {
			// Ensure we are on the correct admin page
			if ( is_admin() && isset( $_GET['taxonomy'] ) && 'wholesale_user_roles' == $_GET['taxonomy'] ) {
				if ( isset( $_GET['orderby'] ) && 'count' == $_GET['orderby'] ) {
					// Modify the query to sort by term count
					$query->set( 'orderby', 'count' );
					$query->set( 'order', 'ASC' ); // Change 'ASC' to 'DESC' for descending order
				}
			}
		}

		public function wwp_modify_taxonomy_columns( $columns ) {
			if ( isset( $columns['posts'] ) ) {
				unset( $columns['posts'] );
			}
			$columns['count'] = 'Count';
			return $columns;
		}

		public function wwp_make_count_column_sortable( $columns ) {
			$columns['count'] = 'count'; // Make the 'count' column sortable
			return $columns;
		}

		public function wwp_custom_taxonomy_term_count_link( $content, $column_name, $term_id ) {
			if ( 'count' == $column_name ) {
				// Get the term object for the specific term
				$term = get_term( $term_id, 'wholesale_user_roles' );
				
				if ( $term && isset( $term->name ) ) {
					// Construct the URL to show users with this term (role)
					$url = admin_url( 'users.php?role=' . urlencode( $term->slug ) );
					$args = array(
						'role'    => $term->name,
					);

					$users = get_users( array( 'role__in' => $term->slug ) );
					
					$content = '<a href="' . esc_url( $url ) . '">' . esc_html( count( $users ) ) . '</a>';
				}
			}

			return $content;
		}

		public function register_menu_for_user_roles() {
			add_filter('parent_file', array( $this, 'wwp_menu_highlight_wholesale_user_roles' ), 10);
		}

		public function wwp_menu_highlight_wholesale_user_roles( $parent_file ) {
			global $current_screen;
			if ('wholesale_user_roles' == $current_screen->taxonomy  ) {
				$parent_file = 'wwp_wholesale';
			}
			return $parent_file;
		}

		public function wwp_add_default_role() {
			add_role(
				'default_wholesaler',
				esc_html__( 'Wholesaler - Wholesaler Role', 'woocommerce-wholesale-pricing' ),
				array(
					'read'    => true,
					'level_0' => true,
				)
			);
		}
		
		public function remove_row_actions( $actions, $tag ) { 
			unset( $actions['view'] );
			return $actions;
		}
		
		public function print_css_styles() {
			?>
			<style type="text/css">
				p.user_not_wholesale {
					text-align: center;
				}
				p.user_not_wholesale a {
					text-decoration: none;
					border: 2px solid #333;
					color: #333;
					padding: 10px 60px;
				}
			</style>
			<?php
		}
		public function register_taxonomy_for_users() {
			$capabilities = array();
			// global $wp_roles;
			$labels = array(
				'label'                      => esc_html__( 'Wholesale Roles', 'woocommerce-wholesale-pricing' ),
				'name'                       => esc_html__( 'Wholesale User Roles', 'woocommerce-wholesale-pricing' ),
				'singular_name'              => esc_html__( 'Wholesale Role', 'woocommerce-wholesale-pricing' ),
				'search_items'               => esc_html__( 'Search User Roles', 'woocommerce-wholesale-pricing' ),
				'popular_items'              => esc_html__( 'Popular User Roles', 'woocommerce-wholesale-pricing' ),
				'all_items'                  => esc_html__( 'All User Roles', 'woocommerce-wholesale-pricing' ),
				'parent_item'                => null,
				'parent_item_colon'          => null,
				'edit_item'                  => esc_html__( 'Edit User Role', 'woocommerce-wholesale-pricing' ),
				'update_item'                => esc_html__( 'Update User Role', 'woocommerce-wholesale-pricing' ),
				'add_new_item'               => esc_html__( 'Add New User Role', 'woocommerce-wholesale-pricing' ),
				'new_item_name'              => esc_html__( 'New User Role Name', 'woocommerce-wholesale-pricing' ),
				'separate_items_with_commas' => esc_html__( 'Separate topics with commas', 'woocommerce-wholesale-pricing' ),
				'add_or_remove_items'        => esc_html__( 'Add or remove topics', 'woocommerce-wholesale-pricing' ),
				'choose_from_most_used'      => esc_html__( 'Choose from the most used topics', 'woocommerce-wholesale-pricing' ),
				'menu_name'                  => esc_html__( 'Wholesale Roles', 'woocommerce-wholesale-pricing' ),
			);
			$args   = array(
				'hierarchical'          => false,
				'labels'                => $labels,
				'show_ui'               => true,
				'show_admin_column'     => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var'             => true,
			);
			register_taxonomy( 'wholesale_user_roles', array( 'wwp_requests' ), $args );
			$term = term_exists( 'default_wholesaler', 'wholesale_user_roles' );
			if ( null === $term ) {
				wp_insert_term( 'Wholesaler', 'wholesale_user_roles', array( 'slug' => 'default_wholesaler' ) );
			}

			// user capabilities add
			$wp_roles = wp_roles();
			if ( ! class_exists( 'WP_Roles' ) ) {
				return;
			}

			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}

			$capabilities = array(
				'manage_wholesale',
				'manage_wholesale_settings',
				'manage_wholesale_user_role',
				'manage_wholesale_notifications',
				'manage_wholesale_bulk_ricing',
				'manage_wholesale_registration_page',
				'manage_wholesale_user_requests',
				'manage_wholesale_group',
				'manage_wholesale_reports',
			);
			
			/**
			* Hooks
			*
			* @since 3.0
			*/
			$capabilities = apply_filters( 'wholesale_user_capabilities', $capabilities );
			foreach ( $capabilities as $cap ) {
				// $wp_roles->remove_cap( 'shop_manager', $cap );
				// $wp_roles->remove_cap( 'administrator', $cap );
				$wp_roles->add_cap( 'shop_manager', $cap );
				$wp_roles->add_cap( 'administrator', $cap );
			}
		}
		public function set_term_to_user_role( $term_id, $tt_id ) {
			$term = get_term( $term_id, 'wholesale_user_roles' );
			if ( ! wp_roles()->is_role( $term->slug ) ) {
				add_role(
					$term->slug,
					$term->name . esc_html__( ' - Wholesaler role', 'woocommerce-wholesale-pricing' ),
					array(
						'read'    => true,
						'level_0' => true,
					)
				);
			}
		}
		public function remove_term_and_user_role( $term, $tt_id, $deleted_term ) {
			$termObj = get_term( $deleted_term, 'wholesale_user_roles' );
			if ( wp_roles()->is_role( $termObj->slug ) ) {
				remove_role( $termObj->slug );
			}
		}
		public function edit_term_and_user_role( $term_id, $tt_id ) {
			
			if ( isset( $_GET['taxonomy'] ) && 'wholesale_user_roles' == sanitize_text_field( $_GET['taxonomy'] ) && isset( $_GET['tag_ID'] ) ) {
				if ( ! isset( $_POST['wwp_tax_exempt_nonce'] ) || ( isset( $_POST['wwp_tax_exempt_nonce'] ) && ! wp_verify_nonce( wc_clean( @$_POST['wwp_tax_exempt_nonce'] ), 'wwp_tax_exempt_nonce' ) ) ) {
					return;
				}
			}
			$termObj  = get_term( $term_id, 'wholesale_user_roles' );
			$new_name = isset( $_POST['name'] ) ? wc_clean( $_POST['name'] ) : '';
			$new_slug = isset( $_POST['slug'] ) ? wc_clean( $_POST['slug'] ) : '';
			if ( $new_slug != $termObj->slug || $new_name != $termObj->name ) {
				if ( empty( $new_slug ) ) {
					$new_slug = sanitize_title( $new_name );
				}
				if ( wp_roles()->is_role( $termObj->slug ) ) {
					remove_role( $termObj->slug );
				}
				if ( ! wp_roles()->is_role( $new_slug ) ) {
					add_role(
						$new_slug,
						$new_name . esc_html__( ' - Wholesaler role', 'woocommerce-wholesale-pricing' ),
						array(
							'read'    => true,
							'level_0' => true,
						)
					);
				}
				$args  = array(
					'role' => $termObj->slug,
				);
				$users = get_users( $args );
				if ( ! empty( $users ) ) {
					foreach ( $users as $user ) {
						$user = new WP_User( $user->ID );
						// Remove current subscriber role
						$user->remove_role( $termObj->slug );
						$user->remove_cap( $termObj->slug );
						// Upgrade to editor role
						$user->add_role( $new_slug );
						$user->add_cap( $new_slug );
						wp_cache_delete( $user->ID, 'users' );
					}
				}
			}
		}
		public function wwp_add_new_field() {
			wp_nonce_field( 'wwp_tax_exempt_nonce', 'wwp_tax_exempt_nonce' );
			// version 1.3.0
			$settings                 = get_option( 'wwp_wholesale_pricing_options' );
			$variable_subscription_id = ! empty( $settings['wholesale_subscription'] ) ? $settings['wholesale_subscription'] : '';
			// ends version 1.3.0
			?>
			<div class="form-field term-tax-wrap">
				<label for="wwp_tax_exmept_wholesaler"><?php esc_html_e( 'Tax Exempt', 'woocommerce-wholesale-pricing' ); ?></label>
				<input type="checkbox" name="wwp_tax_exmept_wholesaler" id="wwp_tax_exmept_wholesaler" value="yes">
				<span><?php esc_html_e( 'Tax exempt for wholesale user role.', 'woocommerce-wholesale-pricing' ); ?></span>
			</div>
			<div class="form-field role-tax-wrap">
				<label for="wwp_tax_exmept_wholesaler"><?php esc_html_e('Role Tax Exempt', 'woocommerce-wholesale-pricing'); ?></label>
				<select name="wwp_wholesaler_tax_classes" id="wwp_wholesaler_tax_classes" class="">
				<option value=""><?php esc_html_e( 'Select Tax Class', 'woocommerce-wholesale-pricing' ); ?></option>
					<?php 
					$tax_classes = WC_Tax::get_tax_rate_classes();
					if ( !empty($tax_classes) ) {
						foreach ( $tax_classes as $tax_class ) {
							echo '<option value="' . esc_attr($tax_class->slug) . '">' . esc_attr($tax_class->name) . '</option>';
						}
					}
					?>
					</select>
				<span><?php esc_html_e('Tax exempt for wholesale user role.', 'woocommerce-wholesale-pricing'); ?></span>
			</div>
			
			<div class="form-field term-coupons-wrap">
				<label for="wwp_wholesale_disable_coupons"><?php esc_html_e( 'Disable Coupons', 'woocommerce-wholesale-pricing' ); ?></label>
				<input type="checkbox" name="wwp_wholesale_disable_coupons" id="wwp_wholesale_disable_coupons" value="yes">
				<span><?php esc_html_e( 'Disable Coupons for wholesale user role.', 'woocommerce-wholesale-pricing' ); ?></span>
			</div>
			
			<?php esc_html_e( 'Payment Methods', 'woocommerce-wholesale-pricing' ); ?>
			<?php foreach ( (array) @$settings['payment_method_name'] as $key => $value) { ?>
			<div class="form-field payment_method_role_settings" style="padding:3px;">
			<input type="checkbox" name="wwp_wholesale_payment_method_name[<?php echo esc_attr( $key ); ?>]" value="yes">
			<span><?php esc_html_e( $key, 'woocommerce-wholesale-pricing' ); ?></span>
			</div>
			<?php } ?>
			
			<div class="form-field term-gateways-wrap">
				<label for="wwp_restricted_pmethods_wholesaler"><?php esc_html_e( 'Disable Payment Methods', 'woocommerce-wholesale-pricing' ); ?></label>
				<?php $available_gateways = WC()->payment_gateways->payment_gateways(); ?>
					<select name="wwp_restricted_pmethods_wholesaler[]" id="wwp_restricted_pmethods_wholesaler" class="regular-text wc-enhanced-select" multiple>
					<?php
					if ( ! empty( $available_gateways ) ) {
						foreach ( $available_gateways as $key => $method ) {
							echo '<option value="' . esc_attr( $key ) . '">' . esc_attr( $method->title ) . '</option>';
						}
					}
					?>
					</select>
					<p><?php esc_html_e( 'Select payment methods to restrict for wholesale users.', 'woocommerce-wholesale-pricing' ); ?></p>
			</div>
			<div class="form-field term-shipping-wrap">
				<label for="wwp_restricted_smethods_wholesaler"><?php esc_html_e( 'Disable Shipping Methods', 'woocommerce-wholesale-pricing' ); ?></label>
				<?php $shipping_methods = WC()->shipping->get_shipping_methods(); ?>
					<select name="wwp_restricted_smethods_wholesaler[]" id="wwp_restricted_smethods_wholesaler" class="regular-text wc-enhanced-select" multiple>
					<?php
					if ( ! empty( $shipping_methods ) ) {
						foreach ( $shipping_methods as $key => $method ) {
							echo '<option value="' . esc_attr( $key ) . '" >' . esc_attr( $method->method_title ) . '</option>';
						}
					}
					?>
					</select>
					<p><?php esc_html_e( 'Select shipping methods to restrict for wholesale users.', 'woocommerce-wholesale-pricing' ); ?></p>
			</div>
			<div class="form-field role_password-wrap">
				<div>
					<label for="role_password">
						<?php esc_html_e( 'Password', 'woocommerce-wholesale-pricing' ); ?>
					</label>
				</div>
				<div>
				<div>
				<input name="password" id="role_password" type="password" value="" size="40" aria-required="true" class="">
				<button type="button" id="role_password_btn" class="button wp-hide-pw hide-if-no-js" data-toggle="0" aria-label="Show password">
				<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
				<span class="text"><?php esc_html_e( 'Show', 'woocommerce-wholesale-pricing' ); ?></span>
				</button>
				<button type="button" id="genratepassword" class="button" onclick="generatePassword()">
				<span class="text"><?php esc_html_e( 'Generate Password', 'woocommerce-wholesale-pricing' ); ?></span>
				</button>
				</div>
				<p class="description"><?php esc_html_e( 'Password to access wholesale store.', 'woocommerce-wholesale-pricing' ); ?></p>
				</div>
			</div>
			<?php
			if ( ! empty( $variable_subscription_id ) && 'publish' == get_post_status( $variable_subscription_id ) ) {
				$product    = wc_get_product( $variable_subscription_id );
				if ( ! $product->is_type( 'simple' ) ) {
					$variations = $product->get_available_variations();
					$variations = $this->wwp_exclude_variations( $variations ); 
					?>
					<div class="form-field term-subscription-wrap">
						<label for="wwp_wholesaler_subscription"><?php esc_html_e( 'Select Subscription Variation', 'woocommerce-wholesale-pricing' ); ?></label>
						<select name="wwp_wholesaler_subscription" id="wwp_wholesaler_subscription">
							<option value=""><?php esc_html_e( 'Select Subscription Variation', 'woocommerce-wholesale-pricing' ); ?></option>
								<?php foreach ( $variations as $key => $variation ) { ?> 
									<option value="<?php echo esc_attr( $variation['variation_id'] ); ?>"><?php echo esc_attr( implode( ',', $variation['attributes'] ) ); ?></option>
								<?php } ?>						
							<?php
							// foreach ( $variations as $key => $variation ) {
								// echo wp_kses_post( '<option value="' . esc_attr($variation['variation_id']) . '">' . implode(',', $variation['attributes']) . '</option>' );
							// }
							?>
						</select>
						<p><?php esc_html_e( 'On the purchase of the selected variation the users will be assigned this role.', 'woocommerce-wholesale-pricing' ); ?></p>
					</div>
					<?php
				}
			}
		}

		public function wwp_exclude_variations( $variations, $mine = '' ) {
			if ( ! empty( $variations ) ) {
				$args  = array(
					'hide_empty'     => false,
					'fields'         => 'ids',
					'posts_per_page' => -1,
					'meta_query'     => array(
						array(
							'key'     => 'wwp_wholesaler_subscription',
							'compare' => 'EXISTS',
						),
					),
					'taxonomy'       => 'wholesale_user_roles',
				);
				$terms = get_terms( $args );
				if ( ! empty( $terms ) ) {
					foreach ( $terms as $term_id ) {
						$variation_id = get_term_meta( $term_id, 'wwp_wholesaler_subscription', true );
						if ( $mine == $variation_id ) {
							continue;
						}
						$variations = array_filter(
							$variations,
							function ( $element ) use ( $variation_id ) {
								return ( $element['variation_id'] != $variation_id );
							}
						);
					}
				}
			}
			return $variations;
		}
		
		public function wwp_edit_new_field( $term ) {
			$term_id                           = $term->term_id;
			$tax                               = get_term_meta( $term_id, 'wwp_tax_exmept_wholesaler', true );
			$wwp_wholesale_payment_method_name =get_term_meta($term_id, 'wwp_wholesale_payment_method_name', true);
			$wwp_wholesaler_tax_classes        =get_term_meta($term_id, 'wwp_wholesaler_tax_classes', true);
			$coupons                           = get_term_meta( $term_id, 'wwp_wholesale_disable_coupons', true );
			$settings                          = get_option( 'wwp_wholesale_pricing_options' );
			$variable_subscription_id          = ! empty( $settings['wholesale_subscription'] ) ? $settings['wholesale_subscription'] : '';
			$selected_variation                = get_term_meta( $term_id, 'wwp_wholesaler_subscription', true );
			wp_nonce_field( 'wwp_tax_exempt_nonce', 'wwp_tax_exempt_nonce' );
			?>
			<tr class="form-field term-tax-wrap">
				<th> 
					<label for="wwp_tax_exmept_wholesaler"> 
						<?php esc_html_e( 'Tax Exempt', 'woocommerce-wholesale-pricing' ); ?>
					</label>
				</th>
				<td scope="row">
					<input type="checkbox" name="wwp_tax_exmept_wholesaler" value="yes" <?php checked( 'yes', $tax ); ?>>
					<span><?php esc_html_e( 'Tax exempt for wholesale user role.', 'woocommerce-wholesale-pricing' ); ?></span>
				</td>
			</tr>
			<tr class="form-field classes-tax-wrap">
				<th> 
					<label for="wwp_tax_classes_wholesaler"> 
						<?php esc_html_e('Tax Class', 'woocommerce-wholesale-pricing'); ?>
					</label>
				</th>
				<td scope="row">
				<select name="wwp_wholesaler_tax_classes" id="wwp_wholesaler_tax_classes" class="">
				<option value=""><?php esc_html_e( 'Select Tax Class', 'woocommerce-wholesale-pricing' ); ?></option>
					<?php 
					$tax_classes = WC_Tax::get_tax_rate_classes();
			
					if ( !empty($tax_classes) ) {
						foreach ( $tax_classes as $tax_class ) {
							$selected ='';
							if (   $wwp_wholesaler_tax_classes ==  $tax_class->slug  ) {
								$selected ='selected';
							}
							echo '<option value="' . esc_attr($tax_class->slug) . '" ' . esc_attr($selected) . '>' . esc_attr($tax_class->name) . '</option>';
						}
					}
					?>
					</select>
					<span><?php esc_html_e('Tax class for wholesale user role.', 'woocommerce-wholesale-pricing'); ?></span>
				</td>
			</tr>
			<tr class="form-field term-coupons-wrap">
				<th>
					<label for="wwp_wholesale_disable_coupons">
						<?php esc_html_e( 'Disable Coupons', 'woocommerce-wholesale-pricing' ); ?>
					</label>
				</th>
				<td scope="row">
					<input type="checkbox" name="wwp_wholesale_disable_coupons" value="yes" <?php checked( 'yes', $coupons ); ?>>
					<span><?php esc_html_e( 'Disable Coupons for wholesale user role.', 'woocommerce-wholesale-pricing' ); ?></span>
				</td>
			</tr>
			<tr class="form-field term-coupons-wrap">
				<th>
					<label for="wwp_wholesale_disable_coupons">
						<?php esc_html_e( 'Payment Method', 'woocommerce-wholesale-pricing' ); ?>
					</label>
				</th>
				<td scope="row">
				<?php 
				foreach ( (array) @$settings['payment_method_name'] as $key => $value) { 
					$checked ='';
					if ( array_key_exists($key, (array) $wwp_wholesale_payment_method_name) ) {
						$checked ='checked';
					}
					?>
					<div class="form-field payment_method_role_settings" style="padding:3px;">
						<input type="checkbox" name="wwp_wholesale_payment_method_name[<?php echo esc_attr( $key ); ?>]" <?php echo esc_attr( $checked ); ?> value="yes">
						<span><?php esc_html_e( $key, 'woocommerce-wholesale-pricing' ); ?></span>
					</div>
					<?php 
				} 
				?>
				</td>
			</tr>
			<tr class="form-field term-gateways-wrap">
				<th><label for="wwp_restricted_pmethods_wholesaler">
					<?php esc_html_e( 'Disable Payment Methods', 'woocommerce-wholesale-pricing' ); ?></label>
				</th>
				<td>
					<?php
						$value              = get_term_meta( $term_id, 'wwp_restricted_pmethods_wholesaler', true );
						$available_gateways = WC()->payment_gateways->payment_gateways();
					?>
					<select name="wwp_restricted_pmethods_wholesaler[]" id="wwp_restricted_pmethods_wholesaler" class="regular-text wc-enhanced-select" multiple>
					<?php
					if ( ! empty( $available_gateways ) ) {
						foreach ( $available_gateways as $key => $method ) {
							$selected = '';
							if ( ! empty( $value ) && in_array( $key, $value ) ) {
								$selected = 'selected="selected"';
							}
							echo '<option value="' . esc_attr( $key ) . '" ' . esc_attr( $selected ) . '>' . esc_attr( $method->title ) . '</option>';
						}
					}
					?>
					</select>
					<p><?php esc_html_e( 'Select payment methods to restrict for wholesale users.', 'woocommerce-wholesale-pricing' ); ?></p>
				</td>
			</tr>
			<tr class="form-field term-shipping-wrap">
				<th><label for="wwp_restricted_smethods_wholesaler">
					<?php esc_html_e( 'Disable Shipping Methods', 'woocommerce-wholesale-pricing' ); ?></label>
				</th>
				<td>
					<?php
						$value            = get_term_meta( $term_id, 'wwp_restricted_smethods_wholesaler', true );
						$shipping_methods = WC()->shipping->get_shipping_methods();
					?>
					<select name="wwp_restricted_smethods_wholesaler[]" id="wwp_restricted_smethods_wholesaler" class="regular-text wc-enhanced-select" multiple>
					<?php
					if ( ! empty( $shipping_methods ) ) {
						foreach ( $shipping_methods as $key => $method ) {
							$selected = '';
							if ( ! empty( $value ) && in_array( $key, $value ) ) {
								$selected = 'selected="selected"';
							}
							echo '<option value="' . esc_attr( $key ) . '" ' . esc_attr( $selected ) . '>' . esc_attr( $method->method_title ) . '</option>';
						}
					}
					?>
					</select>
					<p><?php esc_html_e( 'Select shipping methods to restrict for wholesale users.', 'woocommerce-wholesale-pricing' ); ?></p>
				</td>
			</tr>

			<?php
				$password = get_term_meta( $term_id, 'password', true );
			?>
			<tr class="form-field role_password-wrap">
				<th>
					<label for="role_password">
						<?php esc_html_e( 'Password', 'woocommerce-wholesale-pricing' ); ?>
					</label>
				</th>
				<td>
				<input name="password" id="role_password" type="password" value="<?php echo esc_html_e( $password, 'woocommerce-wholesale-pricing' ); ?>" size="40" aria-required="true" class="">
				<button type="button" id="role_password_btn" class="button wp-hide-pw hide-if-no-js" data-toggle="0" aria-label="Show password">
				<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
				<span class="text"><?php esc_html_e( 'Show', 'woocommerce-wholesale-pricing' ); ?></span>
				</button>
				<button type="button" id="genratepassword" class="button" onclick="generatePassword()">
				<span class="text"><?php esc_html_e( 'Generate Password', 'woocommerce-wholesale-pricing' ); ?></span>
				</button>
				<p class="description"><?php esc_html_e( 'Password to access wholesale store.', 'woocommerce-wholesale-pricing' ); ?></p>
				</td>
			</tr>
			<?php
			if ( ! empty( $variable_subscription_id ) && 'publish' == get_post_status( $variable_subscription_id ) ) {
				$product = wc_get_product( $variable_subscription_id );
				if (!$product->is_type( 'simple' )) {
					$variations = $product->get_available_variations();
					$variations = $this->wwp_exclude_variations( $variations, $selected_variation );
					?>
					<tr class="form-field term-subscription-wrap">
						<th><label for="wwp_wholesaler_subscription"><?php esc_html_e( 'Select Subscription Variation', 'woocommerce-wholesale-pricing' ); ?></label></th>
						<td>
							<select name="wwp_wholesaler_subscription" id="wwp_wholesaler_subscription">
								<option value=""><?php esc_html_e( 'Select Subscription Variation', 'woocommerce-wholesale-pricing' ); ?></option>
								<?php foreach ( $variations as $key => $variation ) { ?> 
									<option value="<?php echo esc_attr( $variation['variation_id'] ); ?>" <?php echo selected( $selected_variation, $variation['variation_id'], false ); ?>><?php echo esc_attr( implode( ',', $variation['attributes'] ) ); ?></option>
								<?php } ?>
							</select>
							<p><?php esc_html_e( 'On the purchase of the selected variation the users will be assigned this role.', 'woocommerce-wholesale-pricing' ); ?></p>
						</td>
					</tr>
					<?php
				}
			}
		}

		public function wwp_save_new_field( $term_id, $term ) {
		
			if ( ! isset( $_POST['wwp_tax_exempt_nonce'] ) || ! wp_verify_nonce( wc_clean( $_POST['wwp_tax_exempt_nonce'] ), 'wwp_tax_exempt_nonce' ) ) {
				return;
			}
			if ( isset( $_POST['wwp_tax_exmept_wholesaler'] ) ) {
				update_term_meta( $term_id, 'wwp_tax_exmept_wholesaler', 'yes' );
			} else {
				update_term_meta( $term_id, 'wwp_tax_exmept_wholesaler', 'no' );
			}
			if ( isset( $_POST['wwp_wholesaler_tax_classes'] ) ) {
				update_term_meta($term_id, 'wwp_wholesaler_tax_classes', wc_clean($_POST['wwp_wholesaler_tax_classes']));
			} else {
				update_term_meta($term_id, 'wwp_wholesaler_tax_classes', '');
			}
			// version 1.3.0
			if ( isset( $_POST['wwp_wholesale_disable_coupons'] ) ) {
				update_term_meta( $term_id, 'wwp_wholesale_disable_coupons', 'yes' );
			} else {
				update_term_meta( $term_id, 'wwp_wholesale_disable_coupons', 'no' );
			}
			if ( isset( $_POST['wwp_wholesaler_subscription'] ) ) {
				update_term_meta( $term_id, 'wwp_wholesaler_subscription', wc_clean( $_POST['wwp_wholesaler_subscription'] ) );
			} else {
				update_term_meta( $term_id, 'wwp_wholesaler_subscription', '' );
			}
			// ends version 1.3.0
			if ( isset( $_POST['wwp_restricted_pmethods_wholesaler'] ) ) {
				update_term_meta( $term_id, 'wwp_restricted_pmethods_wholesaler', wc_clean( $_POST['wwp_restricted_pmethods_wholesaler'] ) );
			} else {
				update_term_meta( $term_id, 'wwp_restricted_pmethods_wholesaler', '' );
			}
			if ( isset( $_POST['wwp_restricted_smethods_wholesaler'] ) ) {
				update_term_meta( $term_id, 'wwp_restricted_smethods_wholesaler', wc_clean( $_POST['wwp_restricted_smethods_wholesaler'] ) );
			} else {
				update_term_meta( $term_id, 'wwp_restricted_smethods_wholesaler', '' );
			}
			if ( isset( $_POST['password'] ) ) {
				update_term_meta( $term_id, 'password', wc_clean( $_POST['password'] ) );
			} else {
				update_term_meta( $term_id, 'password', '' );
			}
			if ( isset( $_POST['wwp_wholesale_payment_method_name'] ) ) {
				update_term_meta( $term_id, 'wwp_wholesale_payment_method_name', wc_clean( $_POST['wwp_wholesale_payment_method_name'] ) );
			} else {
				update_term_meta( $term_id, 'wwp_wholesale_payment_method_name', '' );
			}
		}
	}
	new WWP_Wholesale_User_Roles();
}
