<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Class To handle Wholesale Customer Requests
 */
if ( ! class_exists( 'WWP_Easy_Wholesale_Requests' ) ) {

	class WWP_Easy_Wholesale_Requests {

		public function __construct() {
			$settings = get_option( 'wwp_wholesale_pricing_options', true );
				$advance_registration_form = isset($settings['advance_registration_form']) ? sanitize_text_field($settings['advance_registration_form']) : 'no';
			if ( 'no' == $advance_registration_form ) {
				add_action( 'init', array( $this, 'register_requests_post_type' ) );
				add_action( 'init', array( $this, 'update_user_status' ) );
				add_filter( 'manage_wwp_requests_posts_columns', array( $this, 'register_wwp_requests_columns' ) );
				add_action( 'manage_wwp_requests_posts_custom_column', array( $this, 'custom_columns_wwp_requests' ), 15, 2 );
				add_action( 'admin_menu', array( $this, 'register_menu_for_requests' ) );
				add_action( 'add_meta_boxes', array( $this, 'register_add_meta_box_requests' ) );
				add_action( 'save_post_wwp_requests', array( $this, 'save_requests_meta' ) );
				add_action('deleted_user', array( $this, 'delete_wholesale_request' ));
			}
		}
		public function register_requests_post_type() {
		
			$labels = array(
				'name'               => esc_html_x( 'Requests', 'Post Type Name', 'woocommerce-wholesale-pricing' ),
				'singular_name'      => esc_html_x( 'Request', 'Post Type Singular Name', 'woocommerce-wholesale-pricing' ),
				'menu_name'          => esc_html__( 'Request', 'woocommerce-wholesale-pricing' ),
				'name_admin_bar'     => esc_html__( 'Request', 'woocommerce-wholesale-pricing' ),
				'add_new'            => esc_html__( 'Add New Request', 'woocommerce-wholesale-pricing' ),
				'add_new_item'       => esc_html__( 'Add New Request', 'woocommerce-wholesale-pricing' ),
				'new_item'           => esc_html__( 'New Request', 'woocommerce-wholesale-pricing' ),
				'edit_item'          => esc_html__( 'Edit Request', 'woocommerce-wholesale-pricing' ),
				'view_item'          => esc_html__( 'View Request', 'woocommerce-wholesale-pricing' ),
				'all_items'          => esc_html__( 'Requests', 'woocommerce-wholesale-pricing' ),
				'search_items'       => esc_html__( 'Search Request', 'woocommerce-wholesale-pricing' ),
				'not_found'          => esc_html__( 'No Request found.', 'woocommerce-wholesale-pricing' ),
				'not_found_in_trash' => esc_html__( 'No Request found in Trash.', 'woocommerce-wholesale-pricing' ),
			);
			$args   = array(
				'labels'          => $labels,
				'description'     => esc_html__( 'Description.', 'woocommerce-wholesale-pricing' ),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => '',
				'query_var'       => false,
				'rewrite'         => false,
				'capability_type' => 'post',
				'has_archive'     => false,
				'hierarchical'    => false,
				'menu_position'   => 58.3,
				'supports'        => array( 'thumbnail', 'title' ),
			);
			register_post_type( 'wwp_requests', $args );
		}
		public function register_menu_for_requests() {
		
			/**
			* Hooks
			*
			* @since 3.0
			*/
			$check = apply_filters( 'wwp_wholesales_menus', true );
			if ($check) {
				$args        = array(
					'posts_per_page' => -1,
					'post_type'      => 'wwp_requests',
					'post_status'    => 'publish',
					'meta_key'       => '_user_status',
					'meta_value'     => 'waiting',
				);
				$posts_query = new WP_Query( $args );
				$the_count   = $posts_query->post_count;
				if ( 0 != $the_count ) {
					$the_count = '<span class="awaiting-mod">' . $the_count . '</span>';
				} else {
					$the_count = '';
				}
			
				add_submenu_page( 'wwp_wholesale', esc_html__( 'Wholesale User Requests', 'woocommerce-wholesale-pricing' ), __( 'Requests ' . $the_count, 'woocommerce-wholesale-pricing' ), 'manage_wholesale_user_requests', 'edit.php?post_type=wwp_requests' );
			}

			add_filter( 'parent_file', array( $this, 'wwp_menu_highlight_wholesale_requests' ), 10 );
		}
		
		public function wwp_menu_highlight_wholesale_requests( $parent_file ) {
			global $current_screen;
			if ( 'wwp_requests' == $current_screen->post_type ) {
				$parent_file = 'wwp_wholesale';
			}
			return $parent_file;
		}
		
		public function register_wwp_requests_columns( $columns ) {
			unset( $columns['author'] );
			unset( $columns['date'] );
			$columns['user_email']  = esc_html__( 'User Email', 'woocommerce-wholesale-pricing' );
			$columns['user_status'] = esc_html__( 'User Status', 'woocommerce-wholesale-pricing' );
			$columns['date']        = esc_html__( 'Date', 'woocommerce-wholesale-pricing' );
			$columns['action']      = esc_html__( 'Action', 'woocommerce-wholesale-pricing' );
			return $columns;
		}
		public function custom_columns_wwp_requests( $column, $post_id ) {
			switch ( $column ) {
				case 'user_status':
					$status = get_post_meta( $post_id, '_user_status', true );
					if ( 'active' == $status ) {
						echo '<p class="approved">' . esc_html__( 'Approved', 'woocommerce-wholesale-pricing' ) . '</p>';
					} elseif ( 'waiting' == $status ) {
						echo '<p class="waiting">' . esc_html__( 'Waiting', 'woocommerce-wholesale-pricing' ) . '</p>';
					} elseif ( 'rejected' == $status ) {
						echo '<p class="rejected">' . esc_html__( 'Rejected', 'woocommerce-wholesale-pricing' ) . '</p>';
					}
					break;

				case 'user_email':
					$user_id = get_post_meta( $post_id, '_user_id', true );
					if ( ! empty( $user_id ) ) {
						$user_info = get_userdata( $user_id );
						if ( $user_info ) {
							echo esc_html( $user_info->user_email );
						}
					}
					break;

				case 'action':
					$user_id = get_post_meta( $post_id, '_user_id', true );
					if ( ! empty( $user_id ) ) {
						$status = get_post_meta( $post_id, '_user_status', true );
						$nonce  = wp_create_nonce( 'request_user_role_nonce' );
						?>
						<form action="">
							<a href="edit.php?post_type=wwp_requests&post_id=<?php echo esc_attr( $post_id ); ?>&user_status=active&_wpnonce=<?php echo esc_attr( $nonce ); ?>">Approve</a> | 
							<a href="edit.php?post_type=wwp_requests&post_id=<?php echo esc_attr( $post_id ); ?>&user_status=rejected&_wpnonce=<?php echo esc_attr( $nonce ); ?>">Reject</a>
						</form> 
						<?php
					}
					break;
			}
		}
		public function register_add_meta_box_requests() {
			add_meta_box(
				'wholesale-pricing-pro-user-status',
				esc_html__( 'Wholesale User Request Confirmation', 'woocommerce-wholesale-pricing' ),
				array( $this, 'wholesale_user_confirmation' ),
				'wwp_requests',
				'normal',
				'high'
			);
		}
		public function wholesale_user_confirmation() {
			global $post;
			$settings      = get_option( 'wwp_wholesale_pricing_options', true );
			$status        = get_post_meta( $post->ID, '_user_status', true );
			$user_id       = get_post_meta( $post->ID, '_user_id', true );
			$rejected_note = get_user_meta( $user_id, 'rejected_note', true );
			$user_role_set = get_post_meta( $post->ID, 'user_role_set', true );
			wp_nonce_field( 'request_user_role_nonce', 'request_user_role_nonce' );
			?>
			<div class="wholesale_user_confirmation">
			<?php
			if ( ! empty( $user_id ) ) {
				$user_info = get_userdata( $user_id );
				if ( $user_info ) {
					?>
					<div class="user_info">
						<table class="form-table">
							<tbody>
							<?php
							$wp_roles = wp_roles();
							$wp_roles_user_info = 'None';
							if ( ! empty( $user_info->roles[0] ) ) {
								$wp_roles_user_info = $wp_roles->role_names[ $user_info->roles[0] ];
							}

							if ( isset( $settings['wholesale_role'] ) && 'single' == $settings['wholesale_role'] ) {

								echo '<tr class=""><td><strong>' . esc_html__( 'Wholesale roles to be assign: ', 'woocommerce-wholesale-pricing' ) . '</strong></td><td>' . esc_html( 'default_wholesaler' ) . '</td></tr>';

							} else {

								?>
								
								<tr scope="row" class="">
							
									<td><strong><label for="default_multipe_wholesale_roles"><?php esc_html_e( 'Wholesale roles to be assign:', 'woocommerce-wholesale-pricing' ); ?></label></strong></td>
									<td>
										<?php

										$allterms = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );

										$update_role = '';

										foreach ( $allterms as $allterm ) {
											if ( isset( $user_info->roles[0] ) ) {
												if ( $allterm->slug == $user_info->roles[0] ) {
													$update_role = 'yes';
												}
											}
										}
										?>
										<select id="default_multipe_wholesale_roles" class="regular-text" name="user_role_set" >
											<option value="" disabled><?php esc_html_e( 'Select Wholesale Role', 'woocommerce-wholesale-pricing' ); ?></option>
											<?php
											foreach ( $allterms as $allterm ) {

												$selected = '';

												if ( 'yes' == $update_role ) {

													if ( $user_info->roles[0] == $allterm->slug ) {
														$selected = 'selected';
													}
												} elseif ( isset( $settings['default_multipe_wholesale_roles'] ) && $settings['default_multipe_wholesale_roles'] == $allterm->slug && empty( $user_role_set ) ) {

													$selected = 'selected';
												} elseif ( $user_role_set == $allterm->slug ) {
													$selected = 'selected';
												}
												?>
												<option value="<?php echo esc_attr( $allterm->slug ); ?>" <?php echo esc_html( $selected ); ?>><?php echo esc_html( $allterm->name ); ?></option>
											<?php } ?> 
										</select>        
									</td>
								</tr>
								<?php
							}
							echo '<tr><td><strong>' . esc_html__( 'User ID: ', 'woocommerce-wholesale-pricing' ) . '</strong></td><td>' . esc_html( $user_info->ID ) . '</td></tr>';
							echo '<tr><td><strong>' . esc_html__( 'User Email: ', 'woocommerce-wholesale-pricing' ) . '</strong></td><td>' . esc_html( $user_info->user_email ) . '</td></tr>';
							echo '<tr><td><strong>' . esc_html__( 'Name: ', 'woocommerce-wholesale-pricing' ) . '</strong></td><td>' . esc_html( $user_info->display_name ) . '</td></tr>';
							echo '<tr><td><strong>' . esc_html__( 'Username: ', 'woocommerce-wholesale-pricing' ) . '</strong></td><td>' . esc_html( $user_info->user_login ) . '</td></tr>';
							echo '<tr><td><strong>' . esc_html__( 'Current user role: ', 'woocommerce-wholesale-pricing' ) . '</strong></td><td>' . esc_html( $wp_roles_user_info ) . '</td></tr>';
							echo '<tr><td><a style="font-size:12px" href="' . esc_url( admin_url( "user-edit.php?user_id=$user_id" ) ) . '" >' . esc_html__( 'View Customer details', 'woocommerce-wholesale-pricing' ) . '</a></td></tr>';
							?>
							</tbody>
						</table>
					</div>
					<?php
				} else {
					echo esc_html__( 'User does not exist', 'woocommerce-wholesale-pricing' );
				}
			} else {
				echo esc_html__( 'User does not exist', 'woocommerce-wholesale-pricing' );
			}
			?>
				<table>
					<tbody>
						<tr>
							<td>
								<strong><?php esc_html_e( 'Request status', 'woocommerce-wholesale-pricing' ); ?></strong>
							</td>
							<td>
								<div class="wwp-request-wrapper">
									<input class="inp-cbx" style="display: none" id="active" type="radio" name="user_status" value="active" <?php echo ( 'active' == $status ) ? 'checked' : ''; ?> >
									<label class="cbx" for="active">
										<span>
											<svg width="12px" height="9px" viewbox="0 0 12 9">
												<polyline points="1 5 4 8 11 1"></polyline>
											</svg>
										</span>
										<span><?php esc_html_e( 'Approve', 'woocommerce-wholesale-pricing' ); ?></span>
									</label>

									<input class="inp-cbx" style="display: none" id="rejected" type="radio" name="user_status" value="rejected" <?php echo ( 'rejected' == $status ) ? 'checked' : ''; ?> >
									<label class="cbx" for="rejected">
										<span>
											<svg width="12px" height="9px" viewbox="0 0 12 9">
												<polyline points="1 5 4 8 11 1"></polyline>
											</svg>
										</span>
										<span><?php esc_html_e( 'Reject', 'woocommerce-wholesale-pricing' ); ?></span>
									</label>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div id="rejected_note" style="padding-bottom: 11px;padding-top: 11px;">
				<textarea name="rejected_note" class="widefat-100" rows="4" cols="120"  placeholder="Reject Note"><?php echo esc_html_e( $rejected_note, 'woocommerce-wholesale-pricing' ); ?></textarea>
			</div>
			<input name="save" type="submit" class="wwp-button-primary" id="publish" value="<?php esc_html_e( 'Update', 'woocommerce-wholesale-pricing' ); ?>">
			<style type="">
				.post-type-wwp_requests .page-title-action {
					display: none;
				}
			</style>
			<?php
		}
		public function save_requests_meta( $post_id ) {
			// Autosave
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}
			// AJAX
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				return;
			}
			if ( ! isset( $_POST['request_user_role_nonce'] ) || ! wp_verify_nonce( wc_clean( $_POST['request_user_role_nonce'] ), 'request_user_role_nonce' ) ) {
				return;
			}
			if ( isset( $_POST['user_status'] ) ) {
				$status = wc_clean( $_POST['user_status'] );
				if ( isset( $_POST['user_role_set'] ) ) {
					$user_role_set = wc_clean( $_POST['user_role_set'] );
				} else {
					$user_role_set = 'default_wholesaler';
				}

				$user_id = get_post_meta( $post_id, '_user_id', true );
				if ( user_can( $user_id, 'edit_others_posts' ) ) {
					wp_die('Administrator or editor role can not be changed.');
				} 

				if ( isset( $_POST['rejected_note'] ) ) {
					$rejected_note = wc_clean( $_POST['rejected_note'] );
					update_post_meta( $post_id, '_user_status', $status );
				}
				$term_list = wp_get_post_terms( $post_id, 'wholesale_user_roles', array( 'fields' => 'all' ) );

				$allterms = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );

				$user_id = get_post_meta( $post_id, '_user_id', true );
				if ( user_can( $user_id, 'edit_others_posts' ) ) {
					wp_die('Administrator or editor role can not be changed.');
				}
				
				$customer = new WC_Customer( $user_id );
				//update_user_meta( $user_id, '_user_status', $status );
				//update_user_meta( $user_id, 'rejected_note', $rejected_note );
				$customer->update_meta_data( '_user_status', $status );
				$customer->update_meta_data( 'rejected_note', $rejected_note );

				$u = new WP_User( $user_id );
				if ( 'active' == $status ) {

						$wp_roles = new WP_Roles();
						$names    = $wp_roles->get_names();

					foreach ( $names as $key => $value ) {
						$u->remove_role( $key );
					}
					$u->add_role( $user_role_set );

					foreach ( $term_list as $term_remove ) {

						wp_remove_object_terms( $post_id, $term_remove->slug, 'wholesale_user_roles', true );
					}

					wp_set_object_terms( $post_id, $user_role_set, 'wholesale_user_roles', true );

					if ( 'sent' != get_post_meta( $post_id, '_approval_notification', true ) ) {
					
						/**
						* Hooks
						*
						* @since 3.0
						*/
						do_action( 'wwp_wholesale_user_request_approved', $user_id );
						update_post_meta( $post_id, '_approval_notification', 'sent' );
					}
				} elseif ( 'rejected' == $status ) {
					foreach ( $allterms as $key => $value ) {
						$u->remove_role( $value->slug );
					}
						$u->add_role( get_option( 'default_role' ) );
						
						/**
						* Hooks
						*
						* @since 3.0
						*/
						do_action( 'wwp_wholesale_user_rejection_notification', $user_id );
				}
				$customer->save();
			}
		}

		public function update_user_status() { 
			// Autosave
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}
			// AJAX
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				return;
			}
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( wc_clean( $_GET['_wpnonce'] ), 'request_user_role_nonce' ) ) {
				return;
			}
			$settings = get_option( 'wwp_wholesale_pricing_options', true );
			if ( isset( $_GET['post_id'] ) ) {
				$post_id = wc_clean( $_GET['post_id'] );

			}
			if ( isset( $_GET['user_status'] ) && ( ( isset( $_GET['post_type'] ) && 'wwp_requests' == $_GET['post_type'] )  || ( isset( $_GET['page'] ) && 'wwp_wholesale' == $_GET['page'] ) ) ) {
				$status = wc_clean( $_GET['user_status'] );

				$user_role_set = 'default_wholesaler';

				if ( isset( $settings['default_multipe_wholesale_roles'] ) && '' != $settings['default_multipe_wholesale_roles'] && 'multiple' == $settings['wholesale_role'] ) {
					$user_role_set = $settings['default_multipe_wholesale_roles'];
					if ( ! empty( get_post_meta( $post_id, 'user_role_set', true ) ) ) {
						$user_role_set = get_post_meta( $post_id, 'user_role_set', true );
					}
				}
				
				$term_list = wp_get_post_terms( $post_id, 'wholesale_user_roles', array( 'fields' => 'all' ) );

				$allterms = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );

				$user_id = get_post_meta( $post_id, '_user_id', true );
				
				$customer = new WC_Customer( $user_id );
				//update_user_meta( $user_id, '_user_status', $status );
				update_post_meta( $post_id, '_user_status', $status );
				$customer->update_meta_data( '_user_status', $status );
				
				$u = new WP_User( $user_id );

				if ( 'active' == $status ) {
				
					$wp_roles = new WP_Roles();
					$names    = $wp_roles->get_names();

					foreach ( $names as $key => $value ) {
						$u->remove_role( $key );
					}
					$u->add_role( $user_role_set );

					foreach ( $term_list as $term_remove ) {
						wp_remove_object_terms( $post_id, $term_remove->slug, 'wholesale_user_roles', true );
					}

					wp_set_object_terms( $post_id, $user_role_set, 'wholesale_user_roles', true );

					if ( 'sent' != get_post_meta( $post_id, '_approval_notification', true ) ) {
					
						/**
						* Hooks
						*
						* @since 3.0
						*/
						do_action( 'wwp_wholesale_user_request_approved', $user_id );
						update_post_meta( $post_id, '_approval_notification', 'sent' );
					}
				} elseif ( 'rejected' == $status ) { 
						
					foreach ( $allterms as $key => $value ) {
						$u->remove_role( $value->slug );
					}
						
						$u->add_role( get_option( 'default_role' ) );
						
						/**
						* Hooks
						*
						* @since 3.0
						*/
						do_action( 'wwp_wholesale_user_rejection_notification', $user_id );
				}
				$customer->save();
			}
		}

		public function delete_wholesale_request( $user_id ) {
			$request = get_posts(
				array(
					'post_type'     => 'wwp_requests',
					'fields'        => 'ids',
					'meta_key'      => '_user_id',
					'meta_value'    => $user_id,
				)
			);

			if ( ! empty( $request ) && count( $request ) > 0 ) {
				wp_delete_post( reset( $request ), true );
			}
		}
	}
	new WWP_Easy_Wholesale_Requests();
}
