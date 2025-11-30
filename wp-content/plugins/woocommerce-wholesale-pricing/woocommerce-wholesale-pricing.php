<?php
/**
 * Plugin Name: Wholesale For WooCommerce
 * Plugin URI: https://woocommerce.com/products/wholesale-for-woocommerce/
 * Description: Wholesale for WooCommerce enables store owners to create and manage wholesale prices, multiple wholesale user roles, user registration forms, product and price visibility, tax, payment and shipping methods, and much more.
 * Version: 2.7.0
 * Author: WPExperts
 * Author URI: https://wpexperts.io
 * Developer: WPExperts
 * Developer URI: https://wpexperts.io
 * Text Domain: woocommerce-wholesale-pricing
 * WC requires at least: 5.0
 * WC tested up to: 10.0.2
 * Requires Plugins: woocommerce
 * Copyright: © 2009-2015 WooCommerce.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Woo: 4830851:fd98fab857870bb9b375e1e0ca9d003e

 */
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

if ( ! defined( 'WWP_PLUGIN_URL' ) ) {
	define( 'WWP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'WWP_PLUGIN_PATH' ) ) {
	define( 'WWP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'WWP_PLUGIN_DIRECTORY_NAME' ) ) {
	define( 'WWP_PLUGIN_DIRECTORY_NAME', __DIR__ );
}
if ( ! class_exists( 'Wwp_Wholesale_Pricing' ) ) {

	class Wwp_Wholesale_Pricing {

		public function __construct() { 
			add_action( 'before_woocommerce_init', array( $this, 'woo_hpos_incompatibility' ) );
			$plugin                 = 'woocommerce/woocommerce.php';
			$subsite_active_plugins = (array) get_option( 'active_plugins' );
			$network_active_plugins = (array) get_site_option( 'active_sitewide_plugins' );
			register_activation_hook( __FILE__, array( $this, 'wholesale_register_activation_hook' ) );
			if ( array_key_exists( $plugin, $network_active_plugins ) || in_array( $plugin, $subsite_active_plugins ) || class_exists( 'WooCommerce' ) ) {
				self::init();
				add_filter('woocommerce_payment_gateways', array( $this, 'wwp_woocommerce_payment_gateways' ) );
				// Load modules.
				add_action( 'plugins_loaded', array( $this, 'module_includes' ), 100 );
				add_action('init', array( $this, 'wwp_registeration_block' ) );
				
			} else {
				add_action( 'admin_notices', array( __CLASS__, 'wholesale_admin_notice_error' ) );
			}
		}
		
		public function wwp_registeration_block() {
			wp_register_script('wwp_register_block', WWP_PLUGIN_URL . 'build/registration-form-block/index.js', array( 'wp-blocks', 'wp-element' ) );
			register_block_type('wholesale/wholesale-registration', array(
				'editor_script' => 'wwp_register_block', 'render_callback'=> array( $this, 'wwp_registeration_block_callback' ),
			));
		}
		
		public function wwp_registeration_block_callback() {
			return '[wwp_registration_form]';
		}

		public function woo_hpos_incompatibility() {
			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
			}
		}

		public function module_includes() {
			$module_paths = array();

			if ( class_exists( 'Automattic\WooCommerce\Blocks\Package' ) && version_compare( \Automattic\WooCommerce\Blocks\Package::get_version(), '7.2.0' ) >= 0 ) {
				$module_paths[ 'blocks' ] = WWP_PLUGIN_PATH . 'inc/blocks/class-wwp-blocks.php';
			}

			/**
			 * 'woocommerce_bundles_compatibility_modules' filter.
			 *
			 * Use this to filter the required compatibility modules.
			 *
			 * @since  5.7.6
			 * @param  array $module_paths
			 */
			$module_paths = apply_filters( 'woocommerce_wholeasle_pricing_compatibility_modules', $module_paths );

			foreach ( $module_paths as $name => $path ) {
				require_once $path ;
			}
		}
		
		public function wholesale_register_activation_hook() {

			if ( get_option( 'wwp_save_form' ) == false ) {
				if ( get_option( 'wwp_wholesale_registration_options' ) ) {
					$registrations = get_option( 'wwp_wholesale_registration_options' );
					$type          = 'text';
					$fields        = array();
					for ( $i = 1; $i < 6; $i++ ) {
	
						if ( isset( $registrations[ 'custom_field_' . $i ] ) && 'yes' == $registrations[ 'custom_field_' . $i ] ) {
	
							if ( isset( $registrations[ 'woo_custom_field_' . $i ] ) ) {
								$field_name = $registrations[ 'woo_custom_field_' . $i ];
							} else {
								$field_name = esc_html__( 'Custom Field ' . $i, 'woocommerce-wholesale-pricing' );
							}
	
							if ( isset( $registrations[ 'required_field_' . $i ] ) && 'yes' == $registrations[ 'required_field_' . $i ] ) {
								$required = true;
							} else {
								$required = false;
							}
							if ( '5' == $i ) {
								$type = esc_html__( 'textarea', 'woocommerce-wholesale-pricing' );
							}
	
								$fields[] = array(
									'type'      => $type,
									'required'  => $required,
									'label'     => $field_name,
									'className' => 'form-control',
									'name'      => 'text-159670154749' . $i,
									'value'     => '',
									'subtype'   => 'text',
								);
						}
					}
					$fields                                       = json_encode( $fields );
					$registrations['display_fields_registration'] = 'yes';
					if ( !empty($fields) ) {
						update_option( 'wwp_save_form', $fields );
					}
					update_option( 'wwp_wholesale_registration_options', $registrations );
				}
			}
		}

		public static function init() {
			if ( function_exists( 'load_plugin_textdomain' ) ) {
				load_plugin_textdomain( 'woocommerce-wholesale-pricing', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
			} 
			if ( get_option( 'wwp_wholesale_pricing_options' ) == false ) { 
				$default_settings = array( 'wholesale_role' => 'single', 'restrict_store_access' => '', 'retailer_disabled' => '', 'save_price_disabled' => '' );
				update_option( 'wwp_wholesale_pricing_options', $default_settings , false );
			}
			include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-general-functions.php';
			include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-ajax-handler.php';
			include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-common.php';
			include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-user-roles.php';
			include_once WWP_PLUGIN_PATH . 'inc/class-wwp-hide-add-to-cart.php';
			include_once WWP_PLUGIN_PATH . 'inc/class-wwp-hide-price.php';
			include_once WWP_PLUGIN_PATH . 'inc/api/class-wwpp-api.php';

			add_action( 'plugins_loaded', __CLASS__ . '::wwp_wholesale_gateways_init', 11 );
			
			if ( is_admin() ) {
				include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-reports.php';
				include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-backend.php';
				include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-metabox.php';
				include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-user-custom-fields.php';
				include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-bulk-price.php';
				include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-registration-setting.php';
				include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-rulesets.php';
				include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-export-import.php';

				// Meta data migrate from single to multi wholesaler
				$migrate_singel_file_to_multi_file = get_option( 'migrate_singel_file_to_multi_file' );
				if ( empty( $migrate_singel_file_to_multi_file ) ) {
					include_once WWP_PLUGIN_PATH . 'inc/migrate-singel-file-to-multi-file.php';
				}

				add_action( 'admin_notices', array( __CLASS__, 'setting_notice' ) );
			} else {
				include_once WWP_PLUGIN_PATH . '/inc/class-wwp-wholesale-frontend.php';
				add_action( 'init', array( __CLASS__, 'include_wholesale_functionality' ) );
				// version 1.3.0
				include_once WWP_PLUGIN_PATH . 'inc/class-wwp-products-visibility.php';
				// ends version 1.3.0
			}
			include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-requests.php';
			include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-groups.php';
			
			/**
			 * Hooks
			 *
			 * @since 2.3.0
			 */
			if ( in_array( 'woocommerce-subscriptions/woocommerce-subscriptions.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
					include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-subscription.php';
			}
			include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-multi-file-upload.php';
			include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-registration.php';
			include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-registration-ajax.php';
			include_once WWP_PLUGIN_PATH . 'inc/integrations/recaptcha/index.php';
		}
		
		public static function wwp_wholesale_gateways_init() {
			include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-payment-method.php';
		}

		public function wwp_woocommerce_payment_gateways( $gateways ) {
			$settings = get_option('wwp_wholesale_pricing_options', true);
			if (!isset($settings['enable_custom_payment_method']) || 'yes' != $settings['enable_custom_payment_method'] ) {
				return $gateways;
			}
			if (! is_wholesaler_user(get_current_user_id()) && ( ! is_admin() && isset( $_SERVER['REQUEST_URI'] ) && ! str_contains( sanitize_text_field( $_SERVER['REQUEST_URI'] ), 'wc-admin/settings/payments/providers' ) ) ) {
				return $gateways;
			}
			$role = get_current_user_role_id();
			if ($role) {  
				$payment_method_name = get_term_meta($role, 'wwp_wholesale_payment_method_name', true);
			} else {
				$payment_method_name =  $settings['payment_method_name'];
			}
			foreach ( (array) $payment_method_name as  $key => $wholesale_custom_gateway ) {
				if (array_key_exists($key, $settings['payment_method_name']) ) {
					$class = 'WWP_Wholesale_Payment_Method';
					if (class_exists($class) ) { 
						$wholesale_custom_gateway_id = strtolower(str_replace(' ', '_', $wholesale_custom_gateway));
						if (is_wholesaler_user(get_current_user_id()) ) {
							if ('yes' == $wholesale_custom_gateway ) {
								$gateways[] = new $class($key);
							}
						} else {
							$gateways[] = new $class($key);
						} 
					}
				}
			}
			
			return $gateways;
		}

		public static function include_wholesale_functionality() { 
			$settings = get_option( 'wwp_wholesale_pricing_options', true );
			if ( is_user_logged_in() ) { 
				$user_info      = get_userdata( get_current_user_id() );
				$user_role      = implode( ', ', $user_info->roles );
				$ws_group = wwp_get_group_post_by_proid( 0, $user_info->ID );
				$wholesale_role = term_exists( $user_role, 'wholesale_user_roles' );
				if ( ( 0 !== $wholesale_role && null !== $wholesale_role ) || is_admin() || $ws_group ) { 
					include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-multiuser.php';
					include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-functions.php';
					include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-mnm-compatibility.php';
				}
			} elseif ( ( isset( $settings['restrict_store_access'] ) && 'yes' == $settings['restrict_store_access'] ) || ( wwp_guest_wholesale_pricing_enabled() ) ) {
				self::restrict_store_access();
				include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-multiuser.php';
				include_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-mnm-compatibility.php';
			}
		}

		public static function restrict_store_access() {
			if ( is_admin() ) {
				return;
			}
			$post = wwp_get_post_data( '' );
			$settings = get_option( 'wwp_wholesale_pricing_options', true );
			if ( isset( $post['access_store_pass'] ) && ! empty( $post['access_store_pass'] ) ) {
				$allterms = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
				foreach ( $allterms as $allterm_key => $allterm_value ) {
					$password = get_term_meta( $allterm_value->term_id, 'password', true );
					if ( $password == $post['access_store_pass'] ) {
						setcookie( 'access_store_id', $allterm_value->term_id, time() + 86400, '/', '', 0 );
						if ( isset( $settings['enable_store_access_cookie'] ) && 'yes' == $settings['enable_store_access_cookie'] && isset( $settings['wwp_cookie_interval'] ) && ! empty( $settings['wwp_cookie_interval'] ) ) {
							$cookie_days = intval( $settings['wwp_cookie_interval'] );
							$cookie_lifetime = $cookie_days * 86400;
							setcookie( 'access_store_id', $allterm_value->term_id, time() + $cookie_lifetime, '/', '', 0 );
						}
						if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
							$refresh = sanitize_text_field( $_SERVER['HTTP_REFERER'] ) . '?message=successfully';
							/**
							 * Hooks
							 *
							 * @since 3.0
							 */                         
							$shop_page_url = apply_filters( 'wwp_redirect_shop_page_url', get_permalink( wc_get_page_id( 'shop' ) ) );
							if ( $shop_page_url ) {
								header( 'Location: ' . $shop_page_url );
								exit;
							}
							header( 'Location: ' . $refresh );
							exit;
						}
					}
				}
				$refresh = sanitize_text_field( $_SERVER['HTTP_REFERER'] );
				wc_add_notice( __( 'Your password is invalid', 'woocommerce-wholesale-pricing' ), 'error' );
				//header( 'Location: ' . $refresh );
				//exit;
			}
			if ( isset( $post['back_to_retailer'] ) && ! empty( $post['back_to_retailer'] ) ) {
				unset( $_COOKIE['access_store_id'] );
				setcookie( 'access_store_id', null, 0, '/' );
				header( 'Location: ' . remove_query_arg( 'message' ) );
				wp_safe_redirect( site_url( '/my-account' ) );
			}
			$get = wwp_get_get_data( '' );
			if ( isset( $get['message'] ) && ! empty( $get['message'] ) ) {
				wc_add_notice( __( 'You have been successfully access the wholesale store.', 'woocommerce-wholesale-pricing' ), 'success' );
			}
		}
		public static function wholesale_admin_notice_error() {
			$class   = 'notice notice-error';
			$message = esc_html__( 'The plugin Wholesale For WooCommerce requires WooCommerce to be installed and activated, in order to work', 'woocommerce-wholesale-pricing' );
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_html( $class ), esc_html( $message ) );
		}
		public static function setting_notice() {
			$wwp_pricing_options = get_option( 'wwp_wholesale_pricing_options' );
			/**
			 * Hooks
			 *
			 * @since 3.0
			 */
			if ( empty( $wwp_pricing_options ) && apply_filters( 'wwp_settings_notice', true )) {
				$class = 'notice notice-warning is-dismissible';
				/**
				* Hooks
				*
				* @since 3.0
				*/
				$plugin_name = apply_filters( 'wwp_wholsale_plg_title', esc_html__( 'Wholesale For WooCommerce', 'woocommerce-wholesale-pricing' ) );
				$heading     = esc_html__( 'Thank You for installing', 'woocommerce-wholesale-pricing' );
				$message     = esc_html__( 'Your current settings are incomplete. Settings must be saved in order to run the plugin. Click here to ', 'woocommerce-wholesale-pricing' );
				printf( '<div class="%1$s"><h2>%2$s %3$s</h2><p> %4$s <a href="%5$s">%6$s</a></p></div>', esc_html( $class ), esc_html( $heading ), esc_html( $plugin_name ), esc_html( $message ), esc_url( admin_url( 'admin.php?page=wwp_wholesale' ) ), esc_html__( 'Setup', 'woocommerce-wholesale-pricing' ) );
			}
		}
	}
	new Wwp_Wholesale_Pricing();
}
