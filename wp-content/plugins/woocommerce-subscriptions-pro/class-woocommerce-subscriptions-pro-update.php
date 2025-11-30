<?php
/**
 * Update plugin functionality.
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    Woocommerce_Subscriptions_Pro
 * @subpackage Woocommerce_Subscriptions_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Woocommerce_Subscriptions_Pro_Update' ) ) {
	/**
	 * The update-specific functionality of the plugin.
	 *
	 * @package    Woocommerce_Subscriptions_Pro
	 */
	class Woocommerce_Subscriptions_Pro_Update {
		/**
		 * Initialize the class and set its properties.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			register_activation_hook( WOOCOMMERCE_SUBSCRIPTIONS_PRO_BASE_FILE, array( $this, 'wps_wsp_check_activation' ) );
			add_action( 'pre_set_site_transient_update_plugins', array( $this, 'wps_wsp_check_update' ) );
			add_filter( 'http_request_args', array( $this, 'wps_wsp_updates_exclude' ), 5, 2 );
			register_deactivation_hook( WOOCOMMERCE_SUBSCRIPTIONS_PRO_BASE_FILE, array( $this, 'wps_wsp_check_deactivation' ) );
			$plugin_update = get_option( 'wps_wsp_plugin_update', 'false' );
			if ( 'true' === $plugin_update ) {
				// To add view details content in plugin update notice on plugins page.
				add_action( 'install_plugins_pre_plugin-information', array( $this, 'wps_wsp_details' ) );
				// To add plugin update notice after plugin update message.
				add_action( 'in_plugin_update_message-woocommerce-subscriptions-pro/woocommerce-subscriptions-pro.php', array( $this, 'wps_wsp_in_plugin_update_notice' ), 10, 2 );
			}
		}
		/**
		 * Clears the scheduler on deactivation.
		 *
		 * @since 1.0.0
		 */
		public function wps_wsp_check_deactivation() {
			wp_clear_scheduled_hook( 'wps_wsp_plugin_check_event' );
		}
		/**
		 * Schedules the scheduler on activation.
		 *
		 * @since 1.0.0
		 */
		public function wps_wsp_check_activation() {
			wp_schedule_event( time(), 'daily', 'wps_wsp_plugin_check_event' );
		}
		/**
		 * Details and request for updatation.
		 *
		 * @since 1.0.0
		 */
		public function wps_wsp_details() {
			global $tab;

			if ( 'plugin-information' === $tab && ( isset( $_REQUEST['plugin'] ) && 'woocommerce-subscriptions-pro' === $_REQUEST['plugin'] ) ) {
				$data = $this->wps_wsp_get_plugin_update_data();
				if ( is_wp_error( $data ) || empty( $data ) ) {
					return;
				}
				if ( ! empty( $data['body'] ) ) {
					$all_data = json_decode( $data['body'], true );
					if ( ! empty( $all_data ) && is_array( $all_data ) ) {
						$this->wps_wsp_create_html_data( $all_data );
						exit;
					}
				}
			}
		}
		/**
		 * Gets the update data for plugin.
		 *
		 * @since 1.0.0
		 */
		public function wps_wsp_get_plugin_update_data() {
			// replace with your plugin url.
			$url = 'https://wpswings.com/pluginupdates/woocommerce-subscriptions-pro/update.php';
			$postdata = array(
				'action' => 'check_update',
				'license_code' => WOOCOMMERCE_SUBSCRIPTIONS_PRO_LICENSE_KEY,
			);
			$args = array(
				'method' => 'POST',
				'body' => $postdata,
			);
			$data = wp_remote_post( $url, $args );
			return $data;
		}
		/**
		 * Render HTML content.
		 *
		 * @since 1.0.0
		 * @param array $all_data list of data.
		 */
		public function wps_wsp_create_html_data( $all_data ) {
			?>
			<style>
				#TB_window{
					top : 4% !important;
				}
				.wps_wsp_banner > img {
					width: 50%;
				}
				.wps_wsp_banner > h1 {
					margin-top: 0px;
				}
				.wps_wsp_banner {
					text-align: center;
				}
				.wps_wsp_description > h4 {
					background-color: #3779B5;
					padding: 5px;
					color: #ffffff;
					border-radius: 5px;
				}
				.wps_wsp_changelog_details > h4 {
					background-color: #3779B5;
					padding: 5px;
					color: #ffffff;
					border-radius: 5px;
				}
			</style>
			<div class="wps_wsp_details_wrapper">
				<div class="wps_wsp_banner">
					<?php
					$value = $all_data['name'] . ' ' . $all_data['version'];
					$image_url = isset( $all_data['banners']['logo'] ) ? $all_data['banners']['logo'] : '';
					?>
					<h1><?php echo esc_html( $value ); ?></h1>
					<img src="<?php echo esc_url( $image_url ); ?>">
				</div>
				<div class="wps_wsp_description">
					<h4><?php esc_html_e( 'Plugin Description', 'woocommerce-subscriptions-pro' ); ?></h4>
					<span><?php echo esc_html( $all_data['sections']['description'] ); ?></span>
				</div>
				<div class="wps_wsp_changelog_details">
					<h4><?php esc_html_e( 'Plugin Change Log', 'woocommerce-subscriptions-pro' ); ?></h4>
					<span><?php echo wp_kses_post( $all_data['sections']['changelog'] ); ?></span>
				</div>
			</div>
			<?php
		}
		/**
		 * Render update notice content.
		 *
		 * @since 1.0.0
		 */
		public function wps_wsp_in_plugin_update_notice() {
			$data = $this->wps_wsp_get_plugin_update_data();
			if ( is_wp_error( $data ) || empty( $data ) ) {
				return;
			}
			if ( isset( $data['body'] ) ) {
				$all_data = json_decode( $data['body'], true );

				if ( is_array( $all_data ) && ! empty( $all_data['sections']['update_notice'] ) ) {
					?>
					<style type="text/css">
						#wps_wsp_in_plugin_update_div .dummy {
							display: none;
						}
						#wps_wsp_in_plugin_update_div p:before {
							content: none;
						}
						#wps_wsp_in_plugin_update_div {
							border-top: 1px solid #ffb900;
							margin-left: -13px;
							padding-left: 20px;
							padding-top: 10px;
							padding-bottom: 5px;
						}
						#wps_wsp_in_plugin_update_div ul {
							list-style-type: decimal;
							padding-left: 20px;
						}
					</style>
					<?php
					echo '</p><div id="wps_wsp_in_plugin_update_div">' . wp_kses_post( $all_data['sections']['update_notice'] ) . '</div><p class="dummy">';
				}
			}
		}
		/**
		 * Checks for the update.
		 *
		 * @param object $transient .
		 */
		public function wps_wsp_check_update( $transient ) {

			$wps_wsp_update_check = 'https://wpswings.com/pluginupdates/woocommerce-subscriptions-pro/update.php';
			if ( empty( $transient->checked ) ) {
				return $transient;
			}

			$plugin_folder    = plugin_basename( dirname( WOOCOMMERCE_SUBSCRIPTIONS_PRO_BASE_FILE ) );
			$plugin_file      = basename( ( WOOCOMMERCE_SUBSCRIPTIONS_PRO_BASE_FILE ) );

			$response = wp_remote_post( $wps_wsp_update_check, array(
				'method' => 'POST',
				'body'   => array(
					'action'          => 'check_update',
					'current_version' => WOOCOMMERCE_SUBSCRIPTIONS_PRO_VERSION,
					'license_key'     => WOOCOMMERCE_SUBSCRIPTIONS_PRO_LICENSE_KEY,
				),
			) );
 
			if ( is_wp_error( $response ) || empty( $response['body']) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
				return $transient;
			}
 
			$data = wp_remote_retrieve_body( $response );
			if ( empty( $data ) ) {

				return $transient;
			}
 
			list($version, $url) = explode( '~', $data );
			if ( $this->wps_wsp_plugin_get( 'Version' ) >= $version ) {
				update_option( 'wps_wsp_plugin_update', false );
				return $transient;
			}
			update_option( 'wps_wsp_plugin_update', true );
			$transient->response[ $plugin_folder . '/' . $plugin_file ] = (object) array(
				'slug'        => $plugin_folder,
				'new_version' => $version,
				'url'         => $this->wps_wsp_plugin_get( 'AuthorURI' ),
				'package'     => $url,
			);
 
			return $transient;

		
		}
		/**
		 * Mwb_updates_exclude excludes the update.
		 *
		 * @since 1.0.0
		 * @param array  $r array of details.
		 * @param string $url link for the site.
		 */
		public function wps_wsp_updates_exclude( $r, $url ) {
			if ( 0 !== strpos( $url, 'http://api.wordpress.org/plugins/update-check' ) ) {
				return $r;
			}
			if ( isset( $r['body'] ) && ! empty( $r['body'] ) && isset( $r['body']['plugins'] ) && ! empty( $r['body']['plugins'] ) ) {
				$plugins = isset( $r['body']['plugins'] ) ? unserialize( $r['body']['plugins'] ) : '';

				if ( isset( $plugins ) && '' !== $plugins ) {
					if ( ! empty( $plugins->plugins ) ) {
						unset( $plugins->plugins[ plugin_basename( __FILE__ ) ] );
					}
					if ( ! empty( $plugins->active ) ) {
						unset( $plugins->active[ array_search( plugin_basename( __FILE__ ), $plugins->active ) ] );
					}
					$r['body']['plugins'] = serialize( $plugins );
				}
			}

			return $r;
		}
		/**
		 * Returns current plugin info.
		 *
		 * @since 1.0.0
		 * @param string $i index.
		 */
		public function wps_wsp_plugin_get( $i ) {
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$plugin_folder = get_plugins( '/' . plugin_basename( dirname( WOOCOMMERCE_SUBSCRIPTIONS_PRO_BASE_FILE ) ) );
			$plugin_file = basename( ( WOOCOMMERCE_SUBSCRIPTIONS_PRO_BASE_FILE ) );
			return $plugin_folder[ $plugin_file ][ $i ];
		}
	}
	new Woocommerce_Subscriptions_Pro_Update();
}

