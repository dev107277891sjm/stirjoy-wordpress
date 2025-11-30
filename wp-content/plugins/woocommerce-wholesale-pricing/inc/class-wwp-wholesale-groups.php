<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Class To handle Wholesale Customer Requests
 */
if ( ! class_exists( 'WWP_Wholesale_Groups' ) ) {

	class WWP_Wholesale_Groups { 
		public $group_announce;
		public function __construct() {

			$this->group_announce = 'group-announcement';

			add_action( 'init', array( $this, 'register_wwp_group_post_type' ) );
			add_action( 'admin_menu', array( $this, 'register_menu_for_groups' ) );
			if ( isset( get_option( 'wwp_wholesale_pricing_options' )['enable_groups'] ) && 'yes' == get_option( 'wwp_wholesale_pricing_options' )['enable_groups'] ) {
				add_filter( 'manage_wwp_groups_posts_columns', array( $this, 'register_wwp_group_columns' ) );
				add_action( 'manage_wwp_groups_posts_custom_column', array( $this, 'custom_columns_wwp_group' ), 15, 2 );
				add_filter( 'post_row_actions', array( $this, 'remove_quick_buttons_groups' ), 10, 2 );
				add_action( 'add_meta_boxes', array( $this, 'register_meta_box_group' ) );
				add_action( 'save_post_wwp_groups', array( $this, 'save_groups_meta' ), 10, 2 );
				add_filter( 'manage_users_columns', array( $this, 'new_modify_user_table' ) );
				add_filter( 'manage_users_custom_column', array( $this, 'new_modify_user_table_row' ), 10, 3 );
				add_action( 'wp_ajax_add_wholesale_announcement', array( $this, 'add_wholesale_announcement' ) );
				add_action( 'wp_ajax_delete_wholesale_announcement_data', array( $this, 'delete_wholesale_announcement' ) );

				add_action( 'init', array( $this, 'wwp_group_announce_rewrite' ) );
				add_filter( 'query_vars', array( $this, 'wwp_group_announce_var' ), 10 );
				add_filter( 'woocommerce_account_menu_items', array( $this, 'wwp_group_announce_menu_items' ) );
				add_action( "woocommerce_account_{$this->group_announce}_endpoint", array( $this, 'wwp_group_announce_content' ) );
				add_action( 'init', array( $this, 'handle_group_status_change' ) );
				add_action( 'wp_ajax_update_announcement_status', array( $this, 'update_announcement_status' ) );
				add_action( 'wp_ajax_wwp_get_announcement_content', array( $this, 'wwp_get_announcement_content' ) );
			}
		}

		public function update_announcement_status() {

			if ( ! empty( get_current_user_id() ) ) {

				if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'wwp_user_annoucement' ) ) {  

					$current_user_id = get_current_user_id();
					if ( isset( $_POST['announcement_index'] ) ) {
						
						$announcement_index = intval( $_POST['announcement_index']);

						$user_group_id = get_user_meta( $current_user_id, 'user_wwp_groupname', true );

						
						$users = get_post_meta( $user_group_id, '_add_users', true );

						if ( ! empty( $users ) && is_array( $users ) ) {
						
							if ( in_array( $current_user_id, $users ) ) {
								
								update_user_meta( $current_user_id, 'wwp_read_announcement_' . $announcement_index, 'true' );

								wp_send_json_success( array(
									'message' => __( 'Announcement status updated successfully.', 'woocommerce-wholesale-pricing' ),
									'announcement_index' => $announcement_index,
								));
							}
						} else {
							wp_send_json_error( 'No users found in this group.' );
						}
					} else {
						wp_send_json_error( 'Invalid or missing Announcement.' );
					}
				} else {
					wp_send_json_error( 'Security Error.' );
				}
			} else {
				wp_send_json_error( 'User not logged in.' );
			}
			wp_die();
		}

		public function wwp_get_announcement_content() {
			if ( ! is_user_logged_in() ) {
				wp_send_json_error('User not logged in.');
			}

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'wwp_user_annoucement' ) ) {  

				$user_id = get_current_user_id();
				$user_group = get_user_meta($user_id, 'add_in_wwp_group', true);

				if (!empty($user_group) && 'true' == $user_group) {
					$group_id = get_user_meta($user_id, 'user_wwp_groupname', true);
					$group_announcements = get_post_meta($group_id, '_wholesale_group_announcements', true); 

					if (!empty($group_announcements) && is_array($group_announcements)) {
						$announcement_index = isset($_POST['announcement_index']) ? intval($_POST['announcement_index']) : 0;

						if (isset($group_announcements[$announcement_index])) {
							$announcement = $group_announcements[$announcement_index];

							wp_send_json_success(array(
								'subject' => $announcement['subject'],
								'content' => $announcement['content'], 
							));
						} else {
							wp_send_json_error('Invalid announcement index.');
						}
					}
				}
			} else {
				wp_send_json_error( 'Security Error.' );
			}
			wp_send_json_error('No announcements available.');
		}

		public function delete_wholesale_announcement() {

			if ( isset( $_POST['announcement_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['announcement_nonce'] ), 'wwp_announcement' ) ) {

				if ( isset( $_POST['post_id'] ) && is_numeric( $_POST['post_id'] ) && isset( $_POST['announcement_index'] ) ) {
					$post_id = intval( $_POST['post_id'] );
					$announcement_index = intval( $_POST['announcement_index'] );

					if ( ! current_user_can( 'edit_post', $post_id ) ) {
						wp_send_json_error( array( 'message' => __( 'You do not have permission to delete this announcement.', 'woocommerce-wholesale-pricing' ) ) );
					}

					$announcements = get_post_meta( $post_id, '_wholesale_group_announcements', true );

					if ( is_array( $announcements ) && isset( $announcements[$announcement_index] ) ) {

						unset( $announcements[$announcement_index] );

						update_post_meta( $post_id, '_wholesale_group_announcements', $announcements );

						wp_send_json_success( array( 'message' => __( 'Announcement deleted successfully.', 'woocommerce-wholesale-pricing' ) ) );
					} else {
						wp_send_json_error( array( 'message' => __( 'Invalid announcement data.', 'woocommerce-wholesale-pricing' ) ) );
					}
				} else {
					wp_send_json_error( array( 'message' => __( 'Invalid announcement ID or index.', 'woocommerce-wholesale-pricing' ) ) );
				}
			} else {
				wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'woocommerce-wholesale-pricing' ) ) );
			}

			wp_die();
		}

		public function add_wholesale_announcement() {
			if ( isset( $_POST['announcement_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['announcement_nonce'] ), 'wwp_announcement' ) ) {

				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( array( 'message' => __( 'Unauthorized', 'woocommerce-wholesale-pricing' ) ) );
				}

				if ( ! empty( $_POST['announcement_content'] ) && ! empty( $_POST['announcement_subject'] ) && ! empty( $_POST['post_id'] ) ) {
					$announcement_content = sanitize_text_field( $_POST['announcement_content'] );
					$announcement_subject = sanitize_text_field( $_POST['announcement_subject'] );
					$post_id = intval( $_POST['post_id'] );
					
					$announcement_date = current_time( 'mysql' );
					
					$announcements = get_post_meta( $post_id, '_wholesale_group_announcements', true );

					if ( ! is_array( $announcements ) ) {
						$announcements = array();
					}

					$announcement_key = count( $announcements ) + 1;

					$announcements[$announcement_key] = array(
						'content' => $announcement_content,
						'subject' => $announcement_subject,
						'date'    => $announcement_date,
						'key'     => $announcement_key,
					);

					update_post_meta( $post_id, '_wholesale_group_announcements', $announcements );

					$users = get_post_meta( $post_id, '_add_users', true );
					if ( ! empty( $users ) ) {
						foreach ( $users as $user_id ) {
							update_user_meta( $user_id, 'wwp_new_announce', 'true' );
						}
					}

					wp_send_json_success( array(
						'message'              => __( 'Announcement added successfully!', 'woocommerce-wholesale-pricing' ),
						'announcement_content' => $announcement_content,
						'announcement_subject' => $announcement_subject,
						'announcement_date'    => date_i18n( get_option( 'date_format' ), strtotime( $announcement_date ) ),
						'announcement_author'  => get_option( 'admin_email' ),
						'announcement_key'     => $announcement_key,
					) );
				} else {
					wp_send_json_error( array( 'message' => __( 'Missing fields', 'woocommerce-wholesale-pricing' ) ) );
				}
			} else {
				wp_send_json_error( array( 'message' => __( 'Invalid nonce', 'woocommerce-wholesale-pricing' ) ) );
			}

			wp_die();
		}

		public function new_modify_user_table( $column ) {
			if ( isset( $column['posts'] ) ) {
				unset( $column['posts'] );
			}
			$column['wwp_group'] = 'Group';
			if ( ! isset( $column['post'] ) ) {
				$column['posts'] = 'Posts';
			}
			return $column;
		}

		public function new_modify_user_table_row( $val, $column_name, $user_id ) {
			switch ( $column_name ) {
				case 'wwp_group':
					$group_name = '-';
					if ( ! empty( get_user_meta( $user_id, 'add_in_wwp_group', true ) ) && 'true' == get_user_meta( $user_id, 'add_in_wwp_group', true ) ) {
						$group_id = get_user_meta( $user_id, 'user_wwp_groupname', true );
						$group_name = get_the_title( $group_id );
					}
					return $group_name;
				default:
			}
			return $val;
		}

		public function wwp_group_announce_rewrite() {
			global $wp_rewrite;
			add_rewrite_endpoint( $this->group_announce, EP_ROOT | EP_PAGES );
			$wp_rewrite->flush_rules();
		}

		public function wwp_group_announce_var( $vars ) {
			$vars[] = $this->group_announce;
			return $vars;
		}

		public function wwp_group_announce_menu_items( $items ) {
			if ( is_user_logged_in() ) {
				$user_id = get_current_user_id();
				if ( ! empty( get_user_meta( $user_id, 'add_in_wwp_group', true ) ) && 'true' == get_user_meta( $user_id, 'add_in_wwp_group', true ) ) {
					if ( isset( $items['customer-logout'] ) ) { 
						unset( $items['customer-logout'] );
					}
					$items[$this->group_announce] = esc_html__( 'Announcements', 'woocommerce-wholesale-pricing' );
					$items = array_merge( $items, array( 'customer-logout' => esc_html__( 'Log out', 'woocommerce' ) ) );
				}
			}

			return $items;
		}
		
		public function wwp_group_announce_content() {
			if ( is_user_logged_in() ) {
				$user_id = get_current_user_id();
				$user_group = get_user_meta( $user_id, 'add_in_wwp_group', true );
				if ( ! empty( $user_group ) && 'true' == $user_group ) {

					$group_id = get_user_meta( $user_id, 'user_wwp_groupname', true );
					$group_announcements = get_post_meta( $group_id, '_wholesale_group_announcements', true );

					if ( ! empty( $group_announcements ) && is_array( $group_announcements ) ) {
						
						// Display the announcement table
						?>
						<table class="wwp_group_announcement_table">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Subject', 'woocommerce-wholesale-pricing' ); ?></th>
									<th><?php esc_html_e( 'Date Published', 'woocommerce-wholesale-pricing' ); ?></th>
									<th><?php esc_html_e( 'Status', 'woocommerce-wholesale-pricing' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $group_announcements as $index => $announcement ) : ?>
									<tr>
										<td>
											<a href="#" data-nonce="<?php esc_attr_e( wp_create_nonce( 'wwp_user_annoucement' ) ); ?>" class="show-announcement-popup" data-index="<?php echo esc_attr( $index ); ?>">
												<?php echo esc_html( $announcement['subject'] ); ?>
											</a>
											<?php 
											// Check if this announcement has been read
											$announcement_read_status = get_user_meta($user_id, 'wwp_read_announcement_' . $index, true);
											$new_announce = empty($announcement_read_status) || 'true' !== $announcement_read_status;
											?>
											<span class="<?php echo $new_announce ? 'wwp-new-label' : ''; ?>"><?php echo $new_announce ? 'New' : ''; ?></span>
										</td>
										<td><?php echo esc_html( $announcement['date'] ); ?></td>
										<td><?php echo $new_announce ? esc_html__( 'Unread', 'woocommerce-wholesale-pricing' ) : esc_html__( 'Read', 'woocommerce-wholesale-pricing' ); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>

						<!-- Popup HTML (hidden by default) -->
						<div class="wwp-announcement-popup-overlay" id="popup-overlay" style="display:none;">
							<div class="wwp-announcement-popup">
								<div class="wwp-announcement-header">
									<h2 id="popup-subject"></h2>
									<span class="wwp-close-popup" id="close-popup">&times;</span>
								</div>
								<div class="announcement-content">
									<p id="popup-content"></p>
								</div>
							</div>
						</div>
						<?php
					} else {
						echo '<p>' . esc_html__( 'No announcements available.', 'woocommerce-wholesale-pricing' ) . '</p>';
					}
				}
			}
		}

		public function handle_group_status_change() {
			// Check if we're in the admin area and the correct action is set
			if ( is_admin() && isset( $_GET['post'] ) && 'wwp_groups' == sanitize_text_field( $_GET['post'] ) && isset( $_GET['action'] ) && isset( $_GET['post_id'] ) && isset( $_GET['_wpnonce'] ) ) {
				$post_id = intval( $_GET['post_id'] );
				$nonce = sanitize_text_field( $_GET['_wpnonce'] );
				$action = sanitize_text_field( $_GET['action'] );

				// Verify the nonce
				if ( ! wp_verify_nonce( $nonce, 'wwp_group_nonce' ) ) {
					return;
				}

				// Handle activation
				if ( 'activate_group' === $action ) {
					// Update the post status to publish
					wp_update_post( array(
						'ID'          => $post_id,
						'post_status' => 'publish',
					) );
					update_post_meta( $post_id, '_wholesale_group_status', 'active' );
				}

				// Handle deactivation
				if ( 'deactivate_group' === $action ) {
					// Update the post status to draft
					wp_update_post( array(
						'ID'          => $post_id,
						'post_status' => 'draft',
					) );
					update_post_meta( $post_id, '_wholesale_group_status', 'inactive' );

				}

				// Redirect to avoid duplicate action execution on page refresh
				wp_safe_redirect( 
					add_query_arg( 
						array( 
							'post_type' => 'wwp_groups', // Replace with your post type
						),
						remove_query_arg(
							array( 
								'action', 
								'post', 
								'post_id', 
								'_wpnonce', 
							) 
						) 
					) 
				);
				exit;
			}
		}

		public function register_wwp_group_post_type() {
			if ( isset( get_option( 'wwp_wholesale_pricing_options' )['enable_groups'] ) && 'yes' == get_option( 'wwp_wholesale_pricing_options' )['enable_groups'] ) {

				$labels = array(
					'name'               => esc_html_x( 'Group', 'Post Type Name', 'woocommerce-wholesale-pricing' ),
					'singular_name'      => esc_html_x( 'Group', 'Post Type Singular Name', 'woocommerce-wholesale-pricing' ),
					'menu_name'          => esc_html__( 'Group', 'woocommerce-wholesale-pricing' ),
					'name_admin_bar'     => esc_html__( 'Group', 'woocommerce-wholesale-pricing' ),
					'add_new'            => esc_html__( 'Add New Group', 'woocommerce-wholesale-pricing' ),
					'add_new_item'       => esc_html__( 'Add New Group', 'woocommerce-wholesale-pricing' ),
					'new_item'           => esc_html__( 'New Group', 'woocommerce-wholesale-pricing' ),
					'edit_item'          => esc_html__( 'Edit Group', 'woocommerce-wholesale-pricing' ),
					'view_item'          => esc_html__( 'View Group', 'woocommerce-wholesale-pricing' ),
					'all_items'          => esc_html__( 'Group', 'woocommerce-wholesale-pricing' ),
					'search_items'       => esc_html__( 'Search Group', 'woocommerce-wholesale-pricing' ),
					'not_found'          => esc_html__( 'No Group found.', 'woocommerce-wholesale-pricing' ),
					'not_found_in_trash' => esc_html__( 'No Group found in Trash.', 'woocommerce-wholesale-pricing' ),
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
					'supports'        => array( 'title' ),
				);
				register_post_type( 'wwp_groups', $args );
			}
		}

		public function register_menu_for_groups() {
			if ( isset( get_option( 'wwp_wholesale_pricing_options' )['enable_groups'] ) && 'yes' == get_option( 'wwp_wholesale_pricing_options' )['enable_groups'] ) {
				/**
				* Filter
				*
				* @since 2.4
				*/
				$check = apply_filters( 'wwp_wholesales_menus', true );
				if ($check) {   
					add_submenu_page( 
						'wwp_wholesale',
						esc_html__( 'Wholesale Group', 'woocommerce-wholesale-pricing' ),
						__( 'Group', 'woocommerce-wholesale-pricing' ),
						'manage_wholesale_group',
						'edit.php?post_type=wwp_groups' 
					);
				}

				add_action( 'parent_file', array( $this, 'wwp_menu_highlight_wholesale_group' ), 10 );
			}
		}

		public function wwp_menu_highlight_wholesale_group() {
			global $current_screen;
			$parent_file = '';
			if ( 'wwp_groups' == $current_screen->post_type ) {
				$parent_file = 'wwp_wholesale';
			}
			return $parent_file;
		}
		
		public function register_wwp_group_columns( $columns ) {
			$columns['wwp_group_members']  = esc_html__( 'Members', 'woocommerce-wholesale-pricing' );
			$columns['wwp_group_status'] = esc_html__( 'Status', 'woocommerce-wholesale-pricing' );
			$columns['wwp_group_action']      = esc_html__( 'Action', 'woocommerce-wholesale-pricing' );
			return $columns;
		}

		public function custom_columns_wwp_group( $column, $post_id ) {
			switch ( $column ) {
				case 'title':
					$post_title = get_the_title( $post_id );
					echo '<a href="' . esc_url( get_edit_post_link( $post_id ) ) . '">' . esc_html( $post_title ) . '</a>';
					break;

				case 'date':
					$post_date = get_the_date( 'Y/m/d \a\t g:i a', $post_id );
					$post_status = get_post_status( $post_id );
					echo '<span>' . esc_html( ucfirst( $post_status ) ) . '</span><br>';
					echo '<span>' . esc_html( $post_date ) . '</span>';
					break;

				case 'wwp_group_members':
					// Assuming the number of members is stored in post meta with the key '_group_members_count'
					$members_count = get_post_meta( $post_id, '_group_members_count', true );
					if ( empty( $members_count ) ) {
						$members_count = 0;
					}
					echo esc_html( $members_count );
					break;

				case 'wwp_group_status':
					// Assuming the status is stored in post meta with the key '_group_status'
					$status = get_post_meta( $post_id, '_wholesale_group_status', true );
					if ( 'active' == $status ) {
						echo '<span class="active">' . esc_html__( 'Active', 'woocommerce-wholesale-pricing' ) . '</span>';
					} else {
						echo '<span class="inactive">' . esc_html__( 'Inactive', 'woocommerce-wholesale-pricing' ) . '</span>';
					}
					break;

				case 'wwp_group_action':
					// Actions: Deactivate | Edit | Delete
					$status = get_post_meta( $post_id, '_wholesale_group_status', true );
					$status = get_post_status( $post_id );
					$edit_link = get_edit_post_link( $post_id );
					$delete_link = get_delete_post_link( $post_id );
					$nonce = wp_create_nonce( 'wwp_group_nonce' );

					$activate_url = add_query_arg( array(
						'action'   => 'activate_group',
						'post' => 'wwp_groups',
						'post_id'  => $post_id,
						'_wpnonce' => $nonce,
					), admin_url( 'edit.php' ) );

					$deactivate_url = add_query_arg( array(
						'action'   => 'deactivate_group',
						'post' => 'wwp_groups',
						'post_id'  => $post_id,
						'_wpnonce' => $nonce,
					), admin_url( 'edit.php' ) );

					if ( 'publish' == $status ) {
						echo '<a href="' . esc_url( $deactivate_url ) . '">' . esc_html__( 'Deactivate', 'woocommerce-wholesale-pricing' ) . '</a>';
					} else {
						echo '<a href="' . esc_url( $activate_url ) . '">' . esc_html__( 'Activate', 'woocommerce-wholesale-pricing' ) . '</a>';
					}
					echo ' | <a href="' . esc_url( $edit_link ) . '">' . esc_html__( 'Edit', 'woocommerce-wholesale-pricing' ) . '</a>';
					echo ' | <a href="' . esc_url( $delete_link ) . '">' . esc_html__( 'Delete', 'woocommerce-wholesale-pricing' ) . '</a>';
					break;
			}
		}

		public function remove_quick_buttons_groups( $actions, $post ) { 

			if ( isset( $post->post_type ) && 'wwp_groups' === $post->post_type ) {
				unset($actions['edit']);
				unset($actions['trash']);
				unset($actions['view']);
				unset($actions['inline hide-if-no-js']);
			}
			return $actions;
		}

		public function register_meta_box_group() {
			wp_enqueue_script( 'wc-enhanced-select' );
			wp_enqueue_style( 'wwp-select2' );

			add_meta_box(
				'wwp-group-description',
				esc_html__( 'Group description', 'woocommerce-wholesale-pricing' ),
				array( $this, 'wholesale_group_description' ),
				'wwp_groups',
				'advanced',
				'high'
			);

			add_meta_box(
				'wwp-group-configuration',
				esc_html__( 'Group Configuration', 'woocommerce-wholesale-pricing' ),
				array( $this, 'wholesale_group_configuration' ),
				'wwp_groups',
				'advanced',
				'high'
			);

			add_meta_box(
				'wwp-group-action',
				esc_html__( 'Actions', 'woocommerce-wholesale-pricing' ),
				array( $this, 'wholesale_group_action' ),
				'wwp_groups',
				'side',
				'core'
			);

			add_meta_box(
				'wwp-group-announcement',
				esc_html__( 'Announcement', 'woocommerce-wholesale-pricing' ),
				array( $this, 'wholesale_group_announcement' ),
				'wwp_groups',
				'side',
				'core'
			);
		}

		public function wholesale_group_description( $post ) {

			$group_desc = get_post_meta( $post->ID, '_wwp_group_description', true ); 
			?>
			<div>
				<?php 
				$content   = $group_desc;
				$editor_id = 'wwp_group_description';
				$settings = array(
					'textarea_rows' => 5, // Set to 5 rows for smaller size
					'media_buttons' => false,
				);
				wp_editor( $content, $editor_id, $settings ); 
				?>
			</div>
			<?php
		}

		public function wholesale_group_configuration( $post ) {
			// Use nonce for verification
			wp_nonce_field( 'wwp_save_group', 'wwp_group_nonce' );

			// Get meta data for the group
			$include_products = get_post_meta( $post->ID, '_include_products', true );
			$include_specific_products = get_post_meta( $post->ID, '_include_specific_products', true );
			$exclude_products = get_post_meta( $post->ID, '_exclude_products', true );
			$add_users = get_post_meta( $post->ID, '_add_users', true );
			$discount_type = get_post_meta( $post->ID, '_discount_type', true );
			$min_quantity = get_post_meta( $post->ID, '_min_quantity', true );
			$wwp_price = get_post_meta( $post->ID, '_wwp_group_price', true );
			$max_quantity = get_post_meta( $post->ID, '_max_quantity', true );
			$disable_payment_methods = get_post_meta( $post->ID, '_disable_payment_methods', true );
			$disable_shipping_methods = get_post_meta( $post->ID, '_disable_shipping_methods', true );

			// Get all users
			$users = get_users();

			// Get available payment methods
			$payment_gateways = WC()->payment_gateways->payment_gateways();

			// Get available shipping methods
			$shipping_methods = WC()->shipping()->get_shipping_methods();

			// Get all WooCommerce products
			$args = array(
				'post_type' => 'product',
				'posts_per_page' => -1, // Get all products
				'post_status' => 'publish',
			);
			$products = get_posts( $args );

			?>

			<table class="form-table">
				<tbody>
					<input id="wwp-group-id" type="hidden" value="<?php esc_attr_e( $post->id ); ?>">
					<!-- Include Products -->
					<tr>
						<th>
							<strong><?php esc_html_e( 'Include Products', 'woocommerce-wholesale-pricing' ); ?></strong>
						</th>
						<td id="include-products">
							<input type="radio" id="all_products" name="include_products" value="all" <?php checked( $include_products, 'all' ); ?>>
							<label for="all_products"><?php esc_html_e( 'All', 'woocommerce-wholesale-pricing' ); ?></label>
							<input type="radio" id="specific_products" name="include_products" value="specific" <?php checked( $include_products, 'specific' ); ?>>
							<label for="specific_products"><?php esc_html_e( 'Specific Products', 'woocommerce-wholesale-pricing' ); ?></label><br>
							<div class="test" >
								<select class="include-specific-product wc-enhanced-select" name="include_specific_products[]" multiple>
									<?php
									foreach ( $products as $product ) { 
										$product_id = $product->ID;
										$selected = in_array( $product_id, (array) $include_specific_products ) ? 'selected' : '';
										?>
										<option value="<?php echo esc_attr( $product_id ); ?>" <?php echo esc_attr( $selected ); ?>>
											<?php echo esc_html( $product->post_title ); ?>
										</option>
									<?php } ?>
								</select>
							</div>
						</td>
					</tr>

					<!-- Exclude Products -->
					<tr>
						<th>
							<strong><?php esc_html_e( 'Exclude Products', 'woocommerce-wholesale-pricing' ); ?></strong>
						</th>
						<td>
							<select class="exclude-specific-product wc-enhanced-select" name="exclude_products[]" multiple>
								<?php
								foreach ( $products as $product ) { 
									$product_id = $product->ID;
									$selected = in_array( $product_id, (array) $exclude_products ) ? 'selected' : '';
									?>
									<option value="<?php echo esc_attr( $product_id ); ?>" <?php echo esc_attr( $selected ); ?>>
										<?php echo esc_html( $product->post_title ); ?>
									</option>
								<?php } ?>
							</select>
						</td>
					</tr>

					<!-- Add Users in Group -->
					<tr>
						<th>
							<strong><?php esc_html_e( 'Add Users in Group', 'woocommerce-wholesale-pricing' ); ?></strong>
						</th>
						<td>
							<select class="wc-enhanced-select" name="add_users_in_group[]" multiple>
								<?php
								foreach ( $users as $user ) {
									$selected = in_array( $user->ID, (array) $add_users ) ? 'selected' : '';
									echo '<option value="' . esc_attr( $user->ID ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $user->display_name ) . ' (' . esc_html( $user->user_email ) . ')</option>';
								}
								?>
							</select>
						</td>
					</tr>

					<!-- Discount Type -->
					<tr>
						<th>
							<strong><?php esc_html_e( 'Discount Type', 'woocommerce-wholesale-pricing' ); ?></strong>
						</th>
						<td>
							<select class="wc-enhanced-select" name="discount_type">
								<option value="percent" <?php selected( $discount_type, 'percent' ); ?>><?php esc_html_e( 'Percent', 'woocommerce-wholesale-pricing' ); ?></option>
								<option value="fixed" <?php selected( $discount_type, 'fixed' ); ?>><?php esc_html_e( 'Fixed', 'woocommerce-wholesale-pricing' ); ?></option>
							</select>
						</td>
					</tr>

					<!-- Min Quantity -->
					<tr>
						<th>
							<strong><?php esc_html_e( 'Min Quantity', 'woocommerce-wholesale-pricing' ); ?></strong>
						</th>
						<td>
							<input style="padding: 5px;" type="number" name="min_quantity" value="<?php echo esc_attr( $min_quantity ); ?>">
						</td>
					</tr>

					<!-- Max Quantity -->
					<tr>
						<th>
							<strong><?php esc_html_e( 'Max Quantity', 'woocommerce-wholesale-pricing' ); ?></strong>
						</th>
						<td>
							<input style="padding: 5px;" type="number" name="max_quantity" value="<?php echo esc_attr( $max_quantity ); ?>">
						</td>
					</tr>

					<!-- Wholesale value -->
					<tr>
						<th>
							<strong><?php esc_html_e( 'Wholesale Value', 'woocommerce-wholesale-pricing' ); ?></strong>
						</th>
						<td>
							<input style="padding: 5px;" type="number" name="wwp_group_price" value="<?php echo esc_attr( $wwp_price ); ?>">
						</td>
					</tr>
				</tbody>
			</table>

			<!-- Disable Payment Methods -->
			<div>
				<p>
					<strong><?php esc_html_e( 'Disable Payment Methods', 'woocommerce-wholesale-pricing' ); ?></strong>

				</p>
				<p>
					<select class="wc-enhanced-select" name="disable_payment_methods[]" multiple>
						<?php
						foreach ( $payment_gateways as $gateway_id => $gateway ) { 
							$selected = in_array( $gateway_id, (array) $disable_payment_methods ) ? 'selected' : '';
							?>
							<option value="<?php echo esc_attr( $gateway_id ); ?>" <?php echo esc_attr( $selected ); ?>>
								<?php esc_html_e( $gateway->title ); ?>
							</option>
						<?php } ?>
					</select>
					<p class="description"><?php esc_html_e( 'Select payment methods to restrict for group members.', 'woocommerce-wholesale-pricing' ); ?></p>
				</p>
			</div>

			<!-- Disable Shipping Methods -->
			<div>
				<p>
					<strong><?php esc_html_e( 'Disable Shipping Methods', 'woocommerce-wholesale-pricing' ); ?></strong>
				</p>
				<p>
					<select class="wc-enhanced-select" name="disable_shipping_methods[]" multiple>
						<?php
						foreach ( $shipping_methods as $method_id => $method ) {
							$selected = in_array( $method_id, (array) $disable_shipping_methods ) ? 'selected' : '';
							?>
							<option value="<?php echo esc_attr( $method_id ); ?>" <?php echo esc_attr( $selected ); ?>>
								<?php echo esc_html( $method->get_method_title() ); ?>
							</option>
						<?php } ?>
					</select>
					<p class="description"><?php esc_html_e( 'Select shipping methods to restrict for group members.', 'woocommerce-wholesale-pricing' ); ?></p>
				</p>
			</div>
			<?php
		}


		public function wholesale_group_action() {
			?>
				<div class="action-wrap" style="display: flex; justify-content: space-between; align-items: center;">
					<label for="wholesale_group_status"><?php esc_html_e('Status', 'woocommerce-wholesale-pricing'); ?></label>
					<select class="wc-enhanced-select" name="wholesale_group_status" id="wholesale_group_status">
						<option value="active" <?php selected( get_post_meta( get_the_ID(), '_wholesale_group_status', true ), 'active' ); ?>><?php esc_html_e('Active', 'woocommerce-wholesale-pricing'); ?></option>
						<option value="inactive" <?php selected( get_post_meta( get_the_ID(), '_wholesale_group_status', true ), 'inactive' ); ?>><?php esc_html_e('InActive', 'woocommerce-wholesale-pricing'); ?></option>
					</select>
				</div>
			<?php
		}

		public function wholesale_group_announcement() {
			$announcements = get_post_meta( get_the_ID(), '_wholesale_group_announcements', true );
			$announcement_author = get_option( 'admin_email' );
			
			wp_nonce_field( 'wwp_announcement', 'wwp_announcement' );
			?>
			<div class="inside">
				<!-- Display current announcements -->
				<div class="announcement-content">
					<?php if ( ! empty( $announcements ) && is_array( $announcements ) ) : ?>
						<?php foreach ( $announcements as $key => $announcement ) : ?>
							<div class="single-announcement" data-key="<?php echo esc_attr( $key ); ?>">
								<strong><?php echo esc_html( $announcement['subject'] ); ?></strong>
								<p><?php echo esc_html( $announcement['content'] ); ?></p>
								<p class="description">
									<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $announcement['date'] ) ) ); ?>
									<?php esc_html_e('by', 'woocommerce-wholesale-pricing'); ?> <?php echo esc_html( $announcement_author ); ?>
									| <a href="#" data-id="<?php echo esc_attr( get_the_ID() ); ?>" data-key="<?php echo esc_attr( $key ); ?>" class="delete-announcement"><?php esc_html_e('Delete Announcement', 'woocommerce-wholesale-pricing'); ?></a>
								</p>
							</div>
							<div class="wwp-remove-dashes"> --------------------------------- </div>
						<?php endforeach; ?>
					<?php else : ?>
						<p><?php esc_html_e( 'No announcements yet.', 'woocommerce-wholesale-pricing' ); ?></p>
					<?php endif; ?>
				</div>

				<!-- Add new announcement -->
				<div class="add-announcement">
					<label for="add_announcement"><?php esc_html_e('Add Announcement', 'woocommerce-wholesale-pricing'); ?></label>
					<textarea name="add_announcement" id="add_announcement" rows="3" style="width:100%;"></textarea>
					<label for="add_announcement_subject"><?php esc_html_e('Add Subject', 'woocommerce-wholesale-pricing'); ?></label>
					<input type="text" name="announcement_subject" id="add_announcement_subject" style="width:100%;" />
					<button style="margin-top:10px;" data-id="<?php echo esc_attr( get_the_ID() ); ?>" id="add_announcement_button" class="button"><?php esc_html_e('Add', 'woocommerce-wholesale-pricing'); ?></button>
				</div>
			</div>
			<?php
		}

		public function save_groups_meta( $post_id, $post ) {

			// Verify nonce
			if ( ! isset( $_POST['wwp_group_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['wwp_group_nonce'] ), 'wwp_save_group' ) ) {
				return;
			}

			// Check if the user has permission to save
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			// Save metadata
			if ( ! empty( $_POST['wwp_group_description'] ) ) {
				update_post_meta( $post_id, '_wwp_group_description', wp_kses_post( $_POST['wwp_group_description'] ) );
			} else {
				update_post_meta( $post_id, '_wwp_group_description', '' );

			}

			if ( !empty( $_POST['include_products'] ) ) {
				update_post_meta( $post_id, '_include_products', sanitize_text_field( $_POST['include_products'] ) );
			}

			if ( ! empty( $_POST['include_specific_products'] ) ) {
				update_post_meta( $post_id, '_include_specific_products', array_map( 'sanitize_text_field', $_POST['include_specific_products'] ) );
			} else {
				update_post_meta( $post_id, '_include_specific_products', array() );
			}

			if ( ! empty( $_POST['exclude_products'] ) ) {
				update_post_meta( $post_id, '_exclude_products', array_map( 'sanitize_text_field', $_POST['exclude_products'] ) );
			} else {
				update_post_meta( $post_id, '_exclude_products', array() );
			}
			
			if ( ! empty( $_POST['add_users_in_group'] ) ) {
				$users = array_map( 'sanitize_text_field', $_POST['add_users_in_group'] );
				$old_users = get_post_meta( $post_id, '_add_users', true );
				if ( ! empty( $old_users ) ) {
					foreach ( $old_users as $user_id ) { 
						if ( ! in_array( $user_id, $users ) ) {
							update_user_meta( $user_id, 'add_in_wwp_group', 'false' );
							update_user_meta( $user_id, 'user_wwp_groupname', '' );
							// update_user_meta( $user_id, 'wwp_new_announce', '' );

						}
					}
				}
				update_post_meta( $post_id, '_group_members_count', intval( count( $_POST['add_users_in_group'] ) ) );
				foreach ( $users as $user_id ) {
					update_user_meta( $user_id, 'add_in_wwp_group', 'true' );
					update_user_meta( $user_id, 'user_wwp_groupname', $post_id );
					// update_user_meta( $user_id, 'wwp_new_announce', 'true' );

				}
				update_post_meta( $post_id, '_add_users', $users );

			} else {
				$old_users = get_post_meta( $post_id, '_add_users', true );
				if ( ! empty( $old_users ) ) {
					foreach ( $old_users as $user_id ) { 
						update_user_meta( $user_id, 'add_in_wwp_group', 'false' );
						update_user_meta( $user_id, 'user_wwp_groupname', '' );
						// update_user_meta( $user_id, 'wwp_new_announce', '' );
					}
				}
				update_post_meta( $post_id, '_add_users', array() );

			}
			
			if ( ! empty( $_POST['discount_type'] ) ) {
				update_post_meta( $post_id, '_discount_type', sanitize_text_field( $_POST['discount_type'] ) );
			}
			
			if ( ! empty( $_POST['min_quantity'] ) ) {
				update_post_meta( $post_id, '_min_quantity', intval( $_POST['min_quantity'] ) );
			} else {
				update_post_meta( $post_id, '_min_quantity', '' );
			}

			if ( ! empty( $_POST['wwp_group_price'] ) ) {
				update_post_meta( $post_id, '_wwp_group_price', intval( $_POST['wwp_group_price'] ) );
			} else {
				update_post_meta( $post_id, '_wwp_group_price', '' );
			}

			if ( ! empty( $_POST['max_quantity'] ) ) {
				update_post_meta( $post_id, '_max_quantity', intval( $_POST['max_quantity'] ) );
			} else {
				update_post_meta( $post_id, '_max_quantity', '' );
			}

			if ( ! empty( $_POST['disable_payment_methods'] ) ) {
				update_post_meta( $post_id, '_disable_payment_methods', array_map( 'sanitize_text_field', $_POST['disable_payment_methods'] ) );
			} else {
				update_post_meta( $post_id, '_disable_payment_methods', array() );
			}

			if ( ! empty( $_POST['disable_shipping_methods'] ) ) {
				update_post_meta( $post_id, '_disable_shipping_methods', array_map( 'sanitize_text_field', $_POST['disable_shipping_methods'] ) );
			} else {
				update_post_meta( $post_id, '_disable_shipping_methods', array() );
			}

			if ( ! empty( $_POST['wholesale_group_status'] ) ) {
				update_post_meta( $post_id, '_wholesale_group_status', sanitize_text_field( $_POST['wholesale_group_status'] ) );
			}

			if ( 'inactive' == $_POST['wholesale_group_status'] ) {
				if ( 'publish' == $post->post_status ) {
					wp_update_post( array( 'ID' => $post_id, 'post_status' => 'draft' ) );
				}
			}
		}
	}

	new WWP_Wholesale_Groups();

}