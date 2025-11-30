<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

if ( !class_exists('WFWC_WHOLESALE_CUSTOMIZATION') ) { 
	class WFWC_WHOLESALE_CUSTOMIZATION {

		public function __construct() {
			// add_filter('wwp_wholesaler_file_upload_multiple', array($this, 'wwp_wholesaler_file_upload_multiple_callback'));
			add_action('wwp_wholesaler_file_upload_multiple_handle', array( $this, 'wwp_wholesaler_file_upload_multiple_handle_callback' ), 10, 2);
			add_action('wwp_wholesaler_file_upload_user_edit_page', array( $this, 'wwp_wholesaler_file_upload_user_edit_page' ));
		}

		public function wwp_wholesaler_file_upload_multiple_callback( $multiple ) { 
			return true;
		}

		public function wwp_wholesaler_file_upload_multiple_handle_callback( $files, $user_id ) { 
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			
			$post_ids = array();

			$file_data = $files;
			if (isset($file_data['wwp_wholesaler_file_upload'])) {
				$customer = new WC_Customer( $user_id );
				$files = $file_data['wwp_wholesaler_file_upload'];
				foreach ($files['name'] as $key => $value) {
					if ($files['name'][$key]) {
						$file          = array(
							'name' => $files['name'][$key],
							'type' => $files['type'][$key],
							'tmp_name' => $files['tmp_name'][$key],
							'error' => $files['error'][$key],
							'size' => $files['size'][$key],
						);
						if ( UPLOAD_ERR_OK === $file['error'] ) {
							$attachment_id = media_handle_sideload($file, 0); 
							if (!is_wp_error($attachment_id)) { 
								$post_ids[] = $attachment_id;
							} else {
								$error = $attachment_id->get_error_message();
							}
						}
					}
				}
				//update_user_meta( $user_id, 'wwp_wholesaler_file_upload', $post_ids );
				$customer->update_meta_data( 'wwp_wholesaler_file_upload', $post_ids );
				$customer->save();
			}
		}

		public function wwp_wholesaler_file_upload_user_edit_page( $file ) { 
			?>
			<p>
			<?php foreach ($file as $j) : ?>
					<a href="<?php echo esc_url( admin_url( 'upload.php?item=' . $j ) ); ?>">
						<img src="<?php echo esc_url( wp_get_attachment_url($j) ); ?>" width="100" height="100" class="wwp_wholesaler_file_upload">
					</a>
			<?php endforeach; ?>
			</p> 
			<?php
		}
	}
	new WFWC_WHOLESALE_CUSTOMIZATION();
}
