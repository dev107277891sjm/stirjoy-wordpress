<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    Woocommerce_Subscriptions_Pro
 * @subpackage Woocommerce_Subscriptions_Pro/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woocommerce_Subscriptions_Pro
 * @subpackage Woocommerce_Subscriptions_Pro/includes
 * @author     WP Swings <webmaster@wpswings.com>
 */
class Woocommerce_Subscriptions_Pro {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Woocommerce_Subscriptions_Pro_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $wsp_onboard    To initializsed the object of class onboard.
	 */
	protected $wsp_onboard;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area,
	 * the public-facing side of the site and common side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		if ( defined( 'WOOCOMMERCE_SUBSCRIPTIONS_PRO_VERSION' ) ) {

			$this->version = WOOCOMMERCE_SUBSCRIPTIONS_PRO_VERSION;
		} else {

			$this->version = '2.6.3';
		}

		$this->plugin_name = 'woocommerce-subscriptions-pro';

		$this->woocommerce_subscriptions_pro_dependencies();
		$this->woocommerce_subscriptions_pro_locale();
		if ( is_admin() ) {
			$this->woocommerce_subscriptions_pro_admin_hooks();
		} else {
			$this->woocommerce_subscriptions_pro_public_hooks();
		}
		$this->woocommerce_subscriptions_pro_common_hooks();

		$this->woocommerce_subscriptions_pro_api_hooks();

		$this->wps_wsp_init_payment_integration();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woocommerce_Subscriptions_Pro_Loader. Orchestrates the hooks of the plugin.
	 * - Woocommerce_Subscriptions_Pro_i18n. Defines internationalization functionality.
	 * - Woocommerce_Subscriptions_Pro_Admin. Defines all hooks for the admin area.
	 * - Woocommerce_Subscriptions_Pro_Common. Defines all hooks for the common area.
	 * - Woocommerce_Subscriptions_Pro_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function woocommerce_subscriptions_pro_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-woocommerce-subscriptions-pro-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-woocommerce-subscriptions-pro-i18n.php';

		if ( is_admin() ) {

			// The class responsible for defining all actions that occur in the admin area.
			require_once plugin_dir_path( __DIR__ ) . 'admin/class-woocommerce-subscriptions-pro-admin.php';
			require_once plugin_dir_path( __DIR__ ) . 'admin/partials/class-woocommerce-subscriptions-pro-overview.php';

		} else {

			// The class responsible for defining all actions that occur in the public-facing side of the site.
			require_once plugin_dir_path( __DIR__ ) . 'public/class-woocommerce-subscriptions-pro-public.php';

		}

		require_once plugin_dir_path( __DIR__ ) . 'package/rest-api/class-woocommerce-subscriptions-pro-rest-api.php';

		require_once plugin_dir_path( __DIR__ ) . 'package/rest-api/class-woocommerce-subscriptions-pro-rest-api.php';

		/**
		 * This class responsible for defining common functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'common/class-woocommerce-subscriptions-pro-common.php';

		require_once plugin_dir_path( __DIR__ ) . 'includes/woocommerce-subscription-pro-common-function.php';

		$this->loader = new Woocommerce_Subscriptions_Pro_Loader();

		require_once plugin_dir_path( __DIR__ ) . 'includes/class-woocommerce-subscriptions-pro-scheduler.php';
		// WCFM.
		require_once plugin_dir_path( __DIR__ ) . 'package/wcfm-compatibility/class-woocommerce-subscriptions-pro-wcfm-compatibility.php';

		// wps paypal integration file include.
		require WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_PATH . 'includes/wps-paypal-subscription/class-wps-paypal-subscription-integration-gateway.php';

		require WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_PATH . 'includes/wps-paypal-subscription/class-wps-paypal-subscription-integration-compatibility.php';

		require WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_PATH . 'includes/wps-paypal-subscription/class-wps-paypal-subscription-integration-request-api.php';

		require WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_PATH . 'includes/wps-paypal-subscription/class-wps-paypal-subscription-integration-common-functions.php';
	}

	/**
	 * The function is used to include payment gateway integration.
	 */
	public function wps_wsp_init_payment_integration() {

		$wps_sfw_dir = plugin_dir_path( __DIR__ ) . '/package/gateways';
		wps_sfw_include_process_directory( $wps_sfw_dir );
		do_action( 'wps_wsp_payment_integration' );
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woocommerce_Subscriptions_Pro_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function woocommerce_subscriptions_pro_locale() {

		$plugin_i18n = new Woocommerce_Subscriptions_Pro_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function woocommerce_subscriptions_pro_admin_hooks() {

		$wsp_plugin_admin = new Woocommerce_Subscriptions_Pro_Admin( $this->wsp_get_plugin_name(), $this->wsp_get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $wsp_plugin_admin, 'wsp_admin_enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $wsp_plugin_admin, 'wsp_admin_enqueue_scripts' );

		$this->loader->add_action( 'wps_sfw_notice_message', $wsp_plugin_admin, 'wps_wsp_license_notice' );

		$this->loader->add_filter( 'wps_sfw_sfw_plugin_standard_admin_settings_tabs_before', $wsp_plugin_admin, 'wsp_admin_other_settings_page', 10 );
		// Add settings menu for WooCommerce Subscriptions PRO.
		$callname_lic         = self::$lic_callback_function;
		$callname_lic_initial = self::$lic_ini_callback_function;
		$day_count            = self::$callname_lic_initial();

		// Condition for validating.
		if ( self::$callname_lic() || 0 <= $day_count ) {
			// Saving tab settings.
			/*Daily ajax license action.*/
			$this->loader->add_action( 'wps_wsp_check_license_daily', $wsp_plugin_admin, 'wps_wsp_validate_license_daily' );
			$this->loader->add_action( 'admin_init', $wsp_plugin_admin, 'wsp_admin_save_tab_settings' );
			$this->loader->add_filter( 'wsp_others_settings_array', $wsp_plugin_admin, 'wsp_admin_other_settings_fields', 10 );

			if ( wps_sfw_check_plugin_enable() ) {

				$this->loader->add_action( 'woocommerce_variation_options', $wsp_plugin_admin, 'wsp_sfw_woocommerce_variation_options', 10, 3 );
				$this->loader->add_action( 'woocommerce_save_product_variation', $wsp_plugin_admin, 'wsp_sfw_save_product_variation', 10, 2 );
				$this->loader->add_action( 'woocommerce_variation_options_pricing', $wsp_plugin_admin, 'wsp_sfw_variation_options_pricing', 10, 3 );

				$this->loader->add_action( 'wps_sfw_add_action_details', $wsp_plugin_admin, 'wps_wsp_add_option_details', 10, 2 );

				$this->loader->add_action( 'init', $wsp_plugin_admin, 'wps_wsp_admin_pause_susbcription' );

				$this->loader->add_filter( 'wps_sfw_status_array', $wsp_plugin_admin, 'wps_wsp_status_array' );

				$this->loader->add_action( 'wps_sfw_extra_tablenav_html', $wsp_plugin_admin, 'wps_wsp_export_button_html' );

				$this->loader->add_action( 'wps_sfw_product_edit_field', $wsp_plugin_admin, 'wps_wsp_product_edit_renewal_on_certain_date' );

				// one time purchase.
				$this->loader->add_action( 'wps_sfw_product_edit_field', $wsp_plugin_admin, 'wps_wsp_custom_product_fields_for_onetime_purchase_subscription' );

				$this->loader->add_action( 'wps_sfw_save_simple_subscription_field', $wsp_plugin_admin, 'wps_wsp_save_simple_subscription_field', 10, 2 );

				// For Giftcard.
				$this->loader->add_filter( 'wps_wgm_other_setting', $wsp_plugin_admin, 'wps_wsp_additional_coupon_setting' );
				// WPLM Translation.
				$this->loader->add_filter( 'wps_sfw_add_lock_fields_ids_pro', $wsp_plugin_admin, 'wps_wsp_add_lock_custom_fields_pro' );
				$this->loader->add_action( 'wps_sfw_notice_message', $wsp_plugin_admin, 'wps_wsp_wallet_activation_notice', 15 );

				$this->loader->add_filter( 'wps_sfw_dashboard_plugin_title', $wsp_plugin_admin, 'wps_sfw_dashboard_plugin_title_callback' );

				$this->loader->add_action( 'admin_init', $wsp_plugin_admin, 'wps_wsp_create_manually_recurring' );

				// manual subscription feature.
				$this->loader->add_action( 'wps_sfw_add_button_manual_subscription', $wsp_plugin_admin, 'wps_sfw_add_button_manual_subscription_callback' );
				$this->loader->add_action( 'woocommerce_admin_order_data_after_order_details', $wsp_plugin_admin, 'wps_wsp_add_dropdown_for_manual_subscription_parent_order', 10, 1 );
				$this->loader->add_filter( 'wp_ajax_wps_wsp_show_parent_order_for_custom_manual', $wsp_plugin_admin, 'wps_wsp_show_parent_order_for_custom_manual_callback', 10 );
				$this->loader->add_action( 'add_meta_boxes', $wsp_plugin_admin, 'wps_wsp_add_meta_boxes', 10, 2 );
				$this->loader->add_action( 'save_post', $wsp_plugin_admin, 'wps_wsp_save_manual_subscription_order_details', 10, 2 );
				$this->loader->add_filter( 'wc_order_statuses', $wsp_plugin_admin, 'wps_wsp_remove_default_status_manual_subscription', 10, 1 );
				$this->loader->add_filter( 'woocommerce_new_order', $wsp_plugin_admin, 'wps_wsp_save_manual_subscription_order_details_hpos', 10, 1 );

				$this->loader->add_action( 'wp_ajax_wps_wsp_check_license_key_status', $wsp_plugin_admin, 'wps_wsp_check_license_key_status' );
				$this->loader->add_action( 'admin_notices', $wsp_plugin_admin, 'wps_wsp_subscription_notification_html', 10 );

				// wps paypal integration code.
				$this->loader->add_action( 'wp_ajax_wps_paypal_subscription_integration_keys_validation', $wsp_plugin_admin, 'wps_paypal_subscription_integration_keys_validation_callback' );

				$this->loader->add_action( 'wps_sfw_add_action_details', $wsp_plugin_admin, 'wps_sfw_add_action_details_callack', 10, 2 );

				$this->loader->add_filter( 'woocommerce_order_actions', $wsp_plugin_admin, 'wps_sfw_add_renewal_payment_actions', 10, 1 );
				$this->loader->add_action( 'woocommerce_order_action_wps_retry_renewal', $wsp_plugin_admin, 'wps_sfw_handle_renewal_payment', 10, 1 );

				// update existing subscription meta via admin.
				$this->loader->add_filter( 'wps_sfw_column_subscription_table', $wsp_plugin_admin, 'wps_wsp_add_update_subscription_column', 10, 1 );
				$this->loader->add_filter( 'wps_sfw_add_case_column', $wsp_plugin_admin, 'wps_wsp_add_update_button_to_subscription_column', 10, 3 );
				$this->loader->add_filter( 'wp_ajax_wps_wsp_update_subscription_items', $wsp_plugin_admin, 'wps_wsp_update_subscription_items_callback', 10 );

				// Subscription React Report Request hanadling .
				$this->loader->add_action( 'wp_ajax_wps_wsp_chart_data', $wsp_plugin_admin, 'wps_wsp_chart_data_callback' );
				$this->loader->add_action( 'wp_ajax_wps_wsp_chart_product_data', $wsp_plugin_admin, 'wps_wsp_chart_product_data_callback' );
				$this->loader->add_action( 'wp_ajax_wps_wsp_chart_total_renewal', $wsp_plugin_admin, 'wps_wsp_chart_total_renewal_callback' );
				$this->loader->add_action( 'wp_ajax_wps_wsp_get_cancelled_subscription', $wsp_plugin_admin, 'wps_wsp_get_cancelled_subscription_callback' );
				$this->loader->add_action( 'wp_ajax_wps_wsp_get_renewed_subscription', $wsp_plugin_admin, 'wps_wsp_get_renewed_subscription_callback' );
				$this->loader->add_action( 'wp_ajax_wps_wsp_get_mrr_data', $wsp_plugin_admin, 'wps_wsp_get_mrr_data_callback' );
				$this->loader->add_action( 'wp_ajax_wps_wsp_get_grid_data', $wsp_plugin_admin, 'wps_wsp_get_grid_data_callback' );
				$this->loader->add_action( 'wp_ajax_wps_wsp_get_churn_arr_data', $wsp_plugin_admin, 'wps_wsp_get_churn_arr_data_callback' );

				$this->loader->add_action( 'wps_sfw_after_subscription_box_steps', $wsp_plugin_admin, 'wps_sfw_after_subscription_box_steps_html_callback', 10, 1 );

				$this->loader->add_action( 'wp_ajax_wps_wsp_generate_csv', $wsp_plugin_admin, 'wps_wsp_export_csv_report_callback'  );
			}
		}
	}

	/**
	 * Register all of the hooks related to the common functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function woocommerce_subscriptions_pro_common_hooks() {

		$wsp_plugin_common = new Woocommerce_Subscriptions_Pro_Common( $this->wsp_get_plugin_name(), $this->wsp_get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $wsp_plugin_common, 'wsp_common_enqueue_styles' );

		$this->loader->add_action( 'wp_enqueue_scripts', $wsp_plugin_common, 'wsp_common_enqueue_scripts' );

		$this->loader->add_action( 'wp_ajax_wps_wsp_validate_license_key', $wsp_plugin_common, 'wps_wsp_validate_license_key' );
		$this->loader->add_action( 'wp_ajax_nopriv_wps_wsp_validate_license_key', $wsp_plugin_common, 'wps_wsp_validate_license_key' );

		$callname_lic         = self::$lic_callback_function;
		$callname_lic_initial = self::$lic_ini_callback_function;
		$day_count            = self::$callname_lic_initial();

		// Condition for validating.
		if ( self::$callname_lic() || 0 <= $day_count ) {
			if ( wps_sfw_check_plugin_enable() ) {
				$this->loader->add_filter( 'wps_sfw_check_subscription_product_type', $wsp_plugin_common, 'wps_sfw_is_variable_subscription_product_type', 10, 2 );

				$this->loader->add_filter( 'woocommerce_coupon_discount_types', $wsp_plugin_common, 'wsp_subscription_coupon_discount_types' );

				$this->loader->add_filter( 'woocommerce_product_coupon_types', $wsp_plugin_common, 'wsp_woocommerce_product_coupon_types' );

				$this->loader->add_filter( 'woocommerce_coupon_error', $wsp_plugin_common, 'wps_wsp_coupon_error', 10 );
				$this->loader->add_filter( 'woocommerce_coupon_is_valid', $wsp_plugin_common, 'wps_wsp_validate_subscription_coupon', 10, 3 );

				$this->loader->add_filter( 'woocommerce_coupon_is_valid_for_product', $wsp_plugin_common, 'wps_wsp_validate_subscription_coupon_for_product', 10, 3 );

				$this->loader->add_filter( 'woocommerce_coupon_get_discount_amount', $wsp_plugin_common, 'wps_wsp_get_discount_amount', 10, 5 );

				$this->loader->add_filter( 'wps_sfw_email_classes', $wsp_plugin_common, 'wps_wsp_woocommerce_email_classes' );

				$this->loader->add_action( 'woocommerce_order_status_changed', $wsp_plugin_common, 'wps_wsp_woocommerce_order_status_changed', 99, 3 );

				$this->loader->add_action( 'woocommerce_order_status_changed', $wsp_plugin_common, 'wps_wsp_upgrade_downgrade_order_status_changed', 100, 3 );

				$this->loader->add_filter( 'woocommerce_get_order_item_totals', $wsp_plugin_common, 'wps_wsp_reordering_order_item_totals', 10, 3 );

				$this->loader->add_filter( 'wps_sfw_next_payment_date', $wsp_plugin_common, 'wps_wsp_first_payment_date_for_sync', 10, 2 );

				$this->loader->add_action( 'wp_ajax_wps_wsp_variation_expiry', $wsp_plugin_common, 'wps_wsp_variation_expiry' );
				$this->loader->add_action( 'wp_ajax_nopriv_wps_wsp_variation_expiry', $wsp_plugin_common, 'wps_wsp_variation_expiry' );
				// For Giftcard.
				$this->loader->add_filter( 'wps_wgm_discount_type', $wsp_plugin_common, 'wps_wsp_discount_type_for_giftcard' );
				$this->loader->add_action( 'wp_ajax_wps_wsp_apply_giftcard_coupon', $wsp_plugin_common, 'wps_wsp_apply_giftcard_coupon' );
				$this->loader->add_action( 'wp_ajax_nopriv_wps_wsp_apply_giftcard_coupon', $wsp_plugin_common, 'wps_wsp_apply_giftcard_coupon' );

				$this->loader->add_filter( 'wps_wgm_subscription_renewal_order_coupon', $wsp_plugin_common, 'wps_wsp_subscription_renewal_order_coupon', 10, 3 );
				$this->loader->add_action( 'woocommerce_order_status_changed', $wsp_plugin_common, 'wps_wsp_update_giftcard_coupon_amount', 100, 3 );
				$this->loader->add_filter( 'wps_currency_switcher_set_coupon_discount_percentage', $wsp_plugin_common, 'wps_wsp_currency_switcher_set_supported_coupon_type', 10, 2 );

				// hook for bundle product working on subscription and renewal.
				$this->loader->add_action( 'wps_sfw_subscription_bundle_addition', $wsp_plugin_common, 'wps_sfw_subscription_bundle_addition_callback', 10, 3 );

				// new feature hook.
				$this->loader->add_action( 'wps_sfw_add_new_product_for_manual_subscription', $wsp_plugin_common, 'wps_sfw_add_new_product_for_manual_subscription_callback', 10, 2 );

				$this->loader->add_action( 'wps_sfw_renewal_order_creation', $wsp_plugin_common, 'wsp_renewal_order_apply_coupon', 10, 2 );

				$this->loader->add_action( 'wps_sfw_cancel_failed_susbcription', $wsp_plugin_common, 'wps_wsp_cancel_failed_susbcription_callback', 10, 3 );

				$this->loader->add_action( 'cartflows_offer_product_processed', $wsp_plugin_common, 'cartflow_subscription_creation_while_upselling', 10, 3 );

				// wps paypal integration code.

				$this->loader->add_action( 'woocommerce_payment_gateways', $wsp_plugin_common, 'wps_paypal_subscription_add_gateway_class' );
				$this->loader->add_action( 'woocommerce_thankyou', $wsp_plugin_common, 'wps_paypal_subscription_redirect_after_checkout' );

				$this->loader->add_filter( 'woocommerce_locate_template', $wsp_plugin_common, 'wps_pay_form_custom_template', 10, 3 );

				$this->loader->add_action( 'wp_ajax_wps_paypal_subscribed_data', $wsp_plugin_common, 'wps_paypal_subscribed_data_callback' );
				$this->loader->add_action( 'wp_ajax_nopriv_wps_paypal_subscribed_data', $wsp_plugin_common, 'wps_paypal_subscribed_data_callback' );

				$this->loader->add_action( 'wps_sfw_recurring_allow_on_scheduler', $wsp_plugin_common, 'wps_sfw_recurring_allow_on_scheduler_callback', 10, 2 );
				// wc block support.
				$this->loader->add_action( 'woocommerce_blocks_loaded', $wsp_plugin_common, 'wsp_psi_woocommerce_block_support' );

				// update subscription.
				$this->loader->add_action( 'wp_ajax_wps_wsp_get_subscription_edit_form', $wsp_plugin_common, 'wps_wsp_get_subscription_edit_form_callback' );
				$this->loader->add_action( 'wp_ajax_nopriv_wps_wsp_get_subscription_edit_form', $wsp_plugin_common, 'wps_wsp_get_subscription_edit_form_callback' );
				$this->loader->add_action( 'wp_ajax_wps_wsp_update_subscription', $wsp_plugin_common, 'wps_wsp_update_subscription_callback' );
				$this->loader->add_action( 'wp_ajax_nopriv_wps_wsp_update_subscription', $wsp_plugin_common, 'wps_wsp_update_subscription_callback' );

				$this->loader->add_action( 'wps_sfw_other_payment_gateway_renewal', $wsp_plugin_common, 'wps_wsp_other_payment_gateway_renewal_order', 10, 3 );

				$this->loader->add_filter( 'wsp_sfw_check_pro_plugin', $wsp_plugin_common, 'wsp_sfw_check_pro_plugin_callback' );

				$this->loader->add_action( 'wps_sfw_expire_subscription_scheduler', $wsp_plugin_common, 'wps_wsp_expire_subscription_scheduler_callback' );
				$this->loader->add_action( 'wps_sfw_subscription_cancel', $wsp_plugin_common, 'wps_wsp_cancel_assign_user_role', 999, 2 );

				$this->loader->add_action( 'wps_sfw_handle_manual_renewal_status', $wsp_plugin_common, 'wps_sfw_handle_manual_renewal_status_callback', 10, 3 );

				$this->loader->add_filter( 'wps_sfw_subs_curent_time', $wsp_plugin_common, 'wps_wsp_set_current_time_with_start_time', 20, 2 );

			}
		}

		$this->loader->add_action( 'wps_sfw_subscription_api_html', $wsp_plugin_common, 'wps_wsp_subscription_api_html_content' );
		$this->loader->add_action( 'wp_initialize_site', $wsp_plugin_common, 'wps_wsp_standard_plugin_on_new_site', 999, 2 );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function woocommerce_subscriptions_pro_public_hooks() {

		$wsp_plugin_public = new Woocommerce_Subscriptions_Pro_Public( $this->wsp_get_plugin_name(), $this->wsp_get_version() );

		$callname_lic         = self::$lic_callback_function;
		$callname_lic_initial = self::$lic_ini_callback_function;
		$day_count            = self::$callname_lic_initial();

		/*Condition for validating.*/
		if ( self::$callname_lic() || 0 <= $day_count ) {

			if ( wps_sfw_check_plugin_enable() ) {

				$this->loader->add_action( 'wp_enqueue_scripts', $wsp_plugin_public, 'wsp_public_enqueue_styles' );
				$this->loader->add_action( 'wp_enqueue_scripts', $wsp_plugin_public, 'wsp_public_enqueue_scripts' );
				$this->loader->add_action( 'woocommerce_before_add_to_cart_button', $wsp_plugin_public, 'wps_wsp_woocommerce_before_add_to_cart_button' );
				$this->loader->add_filter( 'woocommerce_add_cart_item_data', $wsp_plugin_public, 'wps_wsp_woocommerce_add_cart_item_data', 10, 3 );

				$this->loader->add_filter( 'wps_sfw_show_time_interval', $wsp_plugin_public, 'wps_wsp_show_time_interval_on_cart', 10, 3 );

				$this->loader->add_filter( 'wps_sfw_cart_data_for_susbcription', $wsp_plugin_public, 'wps_wsp_change_subscription_expiry_by_customer', 10, 2 );

				$this->loader->add_filter( 'wps_sfw_supported_payment_gateway_for_woocommerce', $wsp_plugin_public, 'wps_wsp_manual_payment_gateway_for_woocommerce', 10, 2 );

				$this->loader->add_action( 'wps_sfw_order_details_html_after_cancel_button', $wsp_plugin_public, 'wps_wsp_order_details_html_for_paused_subscription' );

				$this->loader->add_action( 'init', $wsp_plugin_public, 'wps_wsp_pause_susbcription' );

				$this->loader->add_action( 'wps_sfw_order_details_html_after_cancel', $wsp_plugin_public, 'wps_wsp_product_details_downgrade_upgrade' );

				$this->loader->add_filter( 'post_type_link', $wsp_plugin_public, 'wps_wsp_downgrade_upgrade_link', 15, 2 );

				$this->loader->add_filter( 'woocommerce_add_to_cart_validation', $wsp_plugin_public, 'wps_wsp_upgrade_downgrade_add_to_cart_validation', 10, 3 );

				$this->loader->add_filter( 'woocommerce_add_cart_item_data', $wsp_plugin_public, 'wps_wsp_upgrade_downgrade_cart_details', 10, 3 );

				$this->loader->add_filter( 'wps_sfw_is_upgrade_downgrade_order', $wsp_plugin_public, 'wps_wsp_is_upgrade_downgrade_order', 10, 5 );

				$this->loader->add_filter( 'woocommerce_cart_item_subtotal', $wsp_plugin_public, 'wps_wsp_is_upgrade_downgrade_text', 10, 3 );

				$this->loader->add_action( 'wps_sfw_did_woocommerce_before_calculate_totals', $wsp_plugin_public, 'wps_wsp_add_switch_subscription_price_and_sigup_fee' );
				$this->loader->add_filter( 'wps_sfw_allow_signup_fee', $wsp_plugin_public, 'wps_sfw_allow_signup_fee', 99, 2 );

				$this->loader->add_filter( 'wps_sfw_show_sync_interval', $wsp_plugin_public, 'wps_wsp_show_sync_interval_price', 10, 2 );
				$this->loader->add_action( 'woocommerce_single_product_summary', $wsp_plugin_public, 'wps_wsp_show_first_payment_date_for_sync_subscription', 15 );

				$this->loader->add_action( 'wps_sfw_cart_price_subscription', $wsp_plugin_public, 'wps_wsp_cart_price_for_sync_subscription', 10, 2 );

				$this->loader->add_filter( 'woocommerce_available_variation', $wsp_plugin_public, 'wps_wsp_variation_descriptions', 10, 3 );

				$this->loader->add_filter( 'wps_sfw_add_to_cart_validation', $wsp_plugin_public, 'wps_wsp_add_to_cart_validation', 10, 3 );

				// Allow only if the Allow shipping cost during checkout only and Allow Shipping Costs for Renewals enabled.
				$this->loader->add_filter( 'wc_shipping_enabled', $wsp_plugin_public, 'wps_wsp_enable_shipping_subscription' );
				$this->loader->add_filter( 'woocommerce_cart_needs_shipping', $wsp_plugin_public, 'woocommerce_cart_needs_shipping' );

				$this->loader->add_action( 'wps_sfw_after_subscription_details', $wsp_plugin_public, 'wps_wsp_add_gift_card_coupon_apply_html' );

				$this->loader->add_filter( 'wps_sfw_expiry_add_to_cart_validation', $wsp_plugin_public, 'wps_wsp_expiry_add_to_cart_validation', 10, 3 );

				$this->loader->add_filter( 'wps_sfw_show_quantity_fields_for_susbcriptions', $wsp_plugin_public, 'wps_wsp_show_quantity_fields_for_susbcriptions', 10, 2 );

				$this->loader->add_filter( 'wps_sfw_recurring_data', $wsp_plugin_public, 'wps_wsp_add_start_date_recurring', 20, 2 );

				$this->loader->add_filter( 'wps_sfw_set_subscription_status', $wsp_plugin_public, 'wps_wsp_set_subscription_status', 20, 2 );

				$this->loader->add_action( 'woocommerce_before_cart', $wsp_plugin_public, 'wps_wsp_show_downgrade_upgrade_msg' );
				$this->loader->add_action( 'woocommerce_before_checkout_form', $wsp_plugin_public, 'wps_wsp_show_downgrade_upgrade_msg' );

				$this->loader->add_action( 'woocommerce_cart_updated', $wsp_plugin_public, 'wps_wsp_remove_cart_notice' );

				// subscription info on thanku.
				$this->loader->add_action( 'woocommerce_after_order_details', $wsp_plugin_public, 'wps_wsp_show_related_subscription_on_order', 10, 1 );
				$this->loader->add_action( 'wps_sfw_after_subscription_details', $wsp_plugin_public, 'wps_wsp_show_renewal_order_for_customer', 10, 1 );

				// One time subscription.
				$this->loader->add_filter( 'wps_skip_creating_subscription', $wsp_plugin_public, 'wps_skip_creating_subscription', 20, 2 );
				$this->loader->add_filter( 'wps_sfw_show_one_time_subscription_price', $wsp_plugin_public, 'wps_wsp_price_html_onetime_subscription_product', 20, 3 );
				$this->loader->add_action( 'wps_sfw_did_woocommerce_before_calculate_totals', $wsp_plugin_public, 'wps_wsp_add_to_cart_one_time_add_price', 50, 1 );
				$this->loader->add_filter( 'wps_sfw_show_one_time_subscription_price_block', $wsp_plugin_public, 'wps_sfw_show_one_time_subscription_price_block_callback', 20, 3 );

				$this->loader->add_action( 'woocommerce_show_variation_price', $wsp_plugin_public, 'wps_wsp_woocommerce_show_variation_price', 99, 3 );

				$this->loader->add_action( 'wps_sfw_cancel_susbcription', $wsp_plugin_public, 'wps_wsp_restrict_customer_to_cancel_before_trial_ended', 10, 2 );
				$this->loader->add_action( 'wps_sfw_customer_cancel_button', $wsp_plugin_public, 'wps_wsp_customer_cancel_button_callback', 10, 2 );

				// wc block.
				$this->loader->add_filter( 'woocommerce_get_item_data', $wsp_plugin_public, 'wps_wsp_get_subscription_meta_on_cart', 10, 2 );

				$this->loader->add_filter( 'wps_sfw_manage_line_total_for_plan_switch', $wsp_plugin_public, 'wps_sfw_manage_line_total_for_plan_switch_callback', 10, 3 );

				$this->loader->add_filter( 'wps_sfw_modify_cart_item_data', $wsp_plugin_public, 'wps_sfw_add_shipping_fee_for_display_callack', 10, 3 );

				$this->loader->add_filter( 'woocommerce_cart_item_subtotal', $wsp_plugin_public, 'woocommerce_cart_item_prorate_subtotal', 10, 3 );

				// Handle the Manual payment method update for stripe and stripe sepa.
				$this->loader->add_action( 'wc_stripe_add_payment_method_stripe_success', $wsp_plugin_public, 'wps_sfw_update_payment_method_for_subscription', 10, 2 );
				$this->loader->add_action( 'wc_stripe_add_payment_method_stripe_sepa_success', $wsp_plugin_public, 'wps_sfw_update_payment_method_for_subscription', 10, 1 );
				$this->loader->add_action( 'wc_stripe_payment_fields_stripe', $wsp_plugin_public, 'wps_sfw_display_a_notice', 10, 1 );

				// $this->loader->add_filter( 'wps_sfw_add_general_settings_fields', $wsp_plugin_public, 'wps_wsp_add_general_settings_fields_callback' );
				// $this->loader->add_filter( 'woocommerce_payment_gateways', $wsp_plugin_public, 'wps_wsp_remove_discontinued_paypal', 99 );

				$this->loader->add_filter( 'wps_sfw_supported_payment_gateway_for_woocommerce', $wsp_plugin_public, 'wps_wsp_wallet_payment_gateway_for_subscription', 10, 2 );

				$this->loader->add_filter( 'wps_sfw_product_summary', $wsp_plugin_public, 'wps_sfw_variable_course_description' );
			}
		}
	}

	/**
	 * Register all of the hooks related to the api functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function woocommerce_subscriptions_pro_api_hooks() {

		$wsp_plugin_api = new Woocommerce_Subscriptions_Pro_Rest_Api( $this->wsp_get_plugin_name(), $this->wsp_get_version() );

		$callname_lic         = self::$lic_callback_function;
		$callname_lic_initial = self::$lic_ini_callback_function;
		$day_count            = self::$callname_lic_initial();

		/*Condition for validating.*/
		if ( self::$callname_lic() || 0 <= $day_count ) {
			$this->loader->add_action( 'rest_api_init', $wsp_plugin_api, 'wps_wsp_add_endpoint' );
		}
	}

	/**
	 * Public static variable to be accessed in this plugin.
	 *
	 * @var string lic_callback_function
	 */
	public static $lic_callback_function = 'check_lcns_validity';

	/**
	 * Public static variable to be accessed in this plugin.
	 *
	 * @var string lic_callback_function
	 */
	public static $lic_ini_callback_function = 'check_lcns_initial_days';

	/**
	 * Validate the use of features of this plugin.
	 *
	 * @since    1.0.0
	 */
	public static function check_lcns_validity() {

		$wps_wsp_license_key = get_option( 'wps_wsp_license_key', '' );

		$wps_wsp_license_key_status = get_option( 'wps_wsp_license_key_status', '' );

		if ( $wps_wsp_license_key && 'true' === $wps_wsp_license_key_status ) {

			return true;
		} else {

			return false;
		}
	}

	/**
	 * Validate the use of features of this plugin for initial days.
	 *
	 * @since    1.0.0
	 */
	public static function check_lcns_initial_days() {

		$thirty_days = get_option( 'wps_wsp_lcns_thirty_days', 0 );

		$current_time = current_time( 'timestamp' );

		$day_count = ( $thirty_days - $current_time ) / ( 24 * 60 * 60 );

		return $day_count;
	}


	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function wsp_run() {
		$this->loader->wsp_run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function wsp_get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Woocommerce_Subscriptions_Pro_Loader    Orchestrates the hooks of the plugin.
	 */
	public function wsp_get_loader() {
		return $this->loader;
	}


	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Woocommerce_Subscriptions_Pro_Onboard    Orchestrates the hooks of the plugin.
	 */
	public function wsp_get_onboard() {
		return $this->wsp_onboard;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function wsp_get_version() {
		return $this->version;
	}

	/**
	 * Show admin notices.
	 *
	 * @param  string $wsp_message    Message to display.
	 * @param  string $type       notice type, accepted values - error/update/update-nag.
	 * @since  1.0.0
	 */
	public static function wps_wsp_plug_admin_notice( $wsp_message, $type = 'error' ) {

		$wsp_classes = 'notice ';

		switch ( $type ) {

			case 'update':
				$wsp_classes .= 'updated is-dismissible';
				break;

			case 'update-nag':
				$wsp_classes .= 'update-nag is-dismissible';
				break;

			case 'success':
				$wsp_classes .= 'notice-success is-dismissible';
				break;

			default:
				$wsp_classes .= 'notice-error is-dismissible';
		}

		$wsp_notice  = '<div class="' . esc_attr( $wsp_classes ) . ' wps-errorr-8">';
		$wsp_notice .= '<p>' . esc_html( $wsp_message ) . '</p>';
		$wsp_notice .= '</div>';

		echo wp_kses_post( $wsp_notice );
	}

	/**
	 * Generate html components.
	 *
	 * @param  string $wsp_components    html to display.
	 * @since  1.0.0
	 */
	public function wps_wsp_plug_generate_html( $wsp_components = array() ) {
		if ( is_array( $wsp_components ) && ! empty( $wsp_components ) ) {
			foreach ( $wsp_components as $wsp_component ) {
				if ( ! empty( $wsp_component['type'] ) && ! empty( $wsp_component['id'] ) ) {
					switch ( $wsp_component['type'] ) {

						case 'hidden':
						case 'number':
						case 'email':
						case 'text':
							?>
						<div class="wps-form-group wps-wsp-<?php echo esc_attr( $wsp_component['type'] ); ?>">
							<div class="wps-form-group__label">
								<label for="<?php echo esc_attr( $wsp_component['id'] ); ?>" class="wps-form-label"><?php echo ( isset( $wsp_component['title'] ) ? esc_html( $wsp_component['title'] ) : '' ); // WPCS: XSS ok. ?></label>
							</div>
							<div class="wps-form-group__control">
								<label class="mdc-text-field mdc-text-field--outlined">
									<span class="mdc-notched-outline">
										<span class="mdc-notched-outline__leading"></span>
										<span class="mdc-notched-outline__notch">
											<?php if ( 'number' != $wsp_component['type'] ) { ?>
												<span class="mdc-floating-label" id="my-label-id" style=""><?php echo ( isset( $wsp_component['placeholder'] ) ? esc_attr( $wsp_component['placeholder'] ) : '' ); ?></span>
											<?php } ?>
										</span>
										<span class="mdc-notched-outline__trailing"></span>
									</span>
									<input
									class="mdc-text-field__input <?php echo ( isset( $wsp_component['class'] ) ? esc_attr( $wsp_component['class'] ) : '' ); ?>" 
									name="<?php echo ( isset( $wsp_component['name'] ) ? esc_html( $wsp_component['name'] ) : esc_html( $wsp_component['id'] ) ); ?>"
									id="<?php echo esc_attr( $wsp_component['id'] ); ?>"
									type="<?php echo esc_attr( $wsp_component['type'] ); ?>"
									value="<?php echo ( isset( $wsp_component['value'] ) ? esc_attr( $wsp_component['value'] ) : '' ); ?>"
									placeholder="<?php echo ( isset( $wsp_component['placeholder'] ) ? esc_attr( $wsp_component['placeholder'] ) : '' ); ?>"
									min="<?php echo ( isset( $wsp_component['min'] ) ? esc_attr( $wsp_component['min'] ) : '' ); ?>"
									>
								</label>
								<div class="mdc-text-field-helper-line">
									<div class="mdc-text-field-helper-text--persistent wps-helper-text" id="" aria-hidden="true"><?php echo ( isset( $wsp_component['description'] ) ? esc_attr( $wsp_component['description'] ) : '' ); ?></div>
								</div>
							</div>
						</div>
							<?php
							break;

						case 'password':
							?>
						<div class="wps-form-group">
							<div class="wps-form-group__label">
								<label for="<?php echo esc_attr( $wsp_component['id'] ); ?>" class="wps-form-label"><?php echo ( isset( $wsp_component['title'] ) ? esc_html( $wsp_component['title'] ) : '' ); // WPCS: XSS ok. ?></label>
							</div>
							<div class="wps-form-group__control">
								<label class="mdc-text-field mdc-text-field--outlined mdc-text-field--with-trailing-icon">
									<span class="mdc-notched-outline">
										<span class="mdc-notched-outline__leading"></span>
										<span class="mdc-notched-outline__notch">
										</span>
										<span class="mdc-notched-outline__trailing"></span>
									</span>
									<input 
									class="mdc-text-field__input <?php echo ( isset( $wsp_component['class'] ) ? esc_attr( $wsp_component['class'] ) : '' ); ?> wps-form__password" 
									name="<?php echo ( isset( $wsp_component['name'] ) ? esc_html( $wsp_component['name'] ) : esc_html( $wsp_component['id'] ) ); ?>"
									id="<?php echo esc_attr( $wsp_component['id'] ); ?>"
									type="<?php echo esc_attr( $wsp_component['type'] ); ?>"
									value="<?php echo ( isset( $wsp_component['value'] ) ? esc_attr( $wsp_component['value'] ) : '' ); ?>"
									placeholder="<?php echo ( isset( $wsp_component['placeholder'] ) ? esc_attr( $wsp_component['placeholder'] ) : '' ); ?>"
									>
									<i class="material-icons mdc-text-field__icon mdc-text-field__icon--trailing wps-password-hidden" tabindex="0" role="button">visibility</i>
								</label>
								<div class="mdc-text-field-helper-line">
									<div class="mdc-text-field-helper-text--persistent wps-helper-text" id="" aria-hidden="true"><?php echo ( isset( $wsp_component['description'] ) ? esc_attr( $wsp_component['description'] ) : '' ); ?></div>
								</div>
							</div>
						</div>
							<?php
							break;

						case 'textarea':
							?>
						<div class="wps-form-group">
							<div class="wps-form-group__label">
								<label class="wps-form-label" for="<?php echo esc_attr( $wsp_component['id'] ); ?>"><?php echo ( isset( $wsp_component['title'] ) ? esc_html( $wsp_component['title'] ) : '' ); // WPCS: XSS ok. ?></label>
							</div>
							<div class="wps-form-group__control">
								<label class="mdc-text-field mdc-text-field--outlined mdc-text-field--textarea"  	for="text-field-hero-input">
									<span class="mdc-notched-outline">
										<span class="mdc-notched-outline__leading"></span>
										<span class="mdc-notched-outline__notch">
											<span class="mdc-floating-label"><?php echo ( isset( $wsp_component['placeholder'] ) ? esc_attr( $wsp_component['placeholder'] ) : '' ); ?></span>
										</span>
										<span class="mdc-notched-outline__trailing"></span>
									</span>
									<span class="mdc-text-field__resizer">
										<textarea class="mdc-text-field__input <?php echo ( isset( $wsp_component['class'] ) ? esc_attr( $wsp_component['class'] ) : '' ); ?>" rows="2" cols="25" aria-label="Label" name="<?php echo ( isset( $wsp_component['name'] ) ? esc_html( $wsp_component['name'] ) : esc_html( $wsp_component['id'] ) ); ?>" id="<?php echo esc_attr( $wsp_component['id'] ); ?>" placeholder="<?php echo ( isset( $wsp_component['placeholder'] ) ? esc_attr( $wsp_component['placeholder'] ) : '' ); ?>"><?php echo ( isset( $wsp_component['value'] ) ? esc_textarea( $wsp_component['value'] ) : '' ); // WPCS: XSS ok. ?></textarea>
									</span>
								</label>

							</div>
						</div>

							<?php
							break;

						case 'select':
						case 'multiselect':
							?>
						<div class="wps-form-group">
							<div class="wps-form-group__label">
								<label class="wps-form-label" for="<?php echo esc_attr( $wsp_component['id'] ); ?>"><?php echo ( isset( $wsp_component['title'] ) ? esc_html( $wsp_component['title'] ) : '' ); // WPCS: XSS ok. ?></label>
							</div>
							<div class="wps-form-group__control">
								<div class="wps-form-select">
									<select id="<?php echo esc_attr( $wsp_component['id'] ); ?>" name="<?php echo ( isset( $wsp_component['name'] ) ? esc_html( $wsp_component['name'] ) : '' ); ?><?php echo ( 'multiselect' === $wsp_component['type'] ) ? '[]' : ''; ?>" id="<?php echo esc_attr( $wsp_component['id'] ); ?>" class="mdl-textfield__input <?php echo ( isset( $wsp_component['class'] ) ? esc_attr( $wsp_component['class'] ) : '' ); ?>" <?php echo 'multiselect' === $wsp_component['type'] ? 'multiple="multiple"' : ''; ?> >
										<?php
										foreach ( $wsp_component['options'] as $wsp_key => $wsp_val ) {
											?>
											<option value="<?php echo esc_attr( $wsp_key ); ?>"
												<?php
												if ( is_array( $wsp_component['value'] ) ) {
													selected( in_array( (string) $wsp_key, $wsp_component['value'], true ), true );
												} else {
													selected( $wsp_component['value'], (string) $wsp_key );
												}
												?>
												>
												<?php echo esc_html( $wsp_val ); ?>
											</option>
											<?php
										}
										?>
									</select>
									<br/>
									<label class="mdl-textfield__label" for="octane"><?php echo ( isset( $wsp_component['description'] ) ? esc_attr( $wsp_component['description'] ) : '' ); ?></label>
								</div>
							</div>
						</div>

							<?php
							break;

						case 'checkbox':
							?>
						<div class="wps-form-group">
							<div class="wps-form-group__label">
								<label for="<?php echo esc_attr( $wsp_component['id'] ); ?>" class="wps-form-label"><?php echo ( isset( $wsp_component['title'] ) ? esc_html( $wsp_component['title'] ) : '' ); // WPCS: XSS ok. ?></label>
							</div>
							<div class="wps-form-group__control wps-pl-4">
								<div class="mdc-form-field">
									<div class="mdc-checkbox">
										<input 
										name="<?php echo ( isset( $wsp_component['name'] ) ? esc_html( $wsp_component['name'] ) : esc_html( $wsp_component['id'] ) ); ?>"
										id="<?php echo esc_attr( $wsp_component['id'] ); ?>"
										type="checkbox"
										class="mdc-checkbox__native-control <?php echo ( isset( $wsp_component['class'] ) ? esc_attr( $wsp_component['class'] ) : '' ); ?>"
										value="<?php echo ( isset( $wsp_component['value'] ) ? esc_attr( $wsp_component['value'] ) : '' ); ?>"
										<?php checked( $wsp_component['value'], '1' ); ?>
										/>
										<div class="mdc-checkbox__background">
											<svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
												<path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59"/>
											</svg>
											<div class="mdc-checkbox__mixedmark"></div>
										</div>
										<div class="mdc-checkbox__ripple"></div>
									</div>
									<label for="checkbox-1"><?php echo ( isset( $wsp_component['description'] ) ? esc_attr( $wsp_component['description'] ) : '' ); ?></label>
								</div>
							</div>
						</div>
							<?php
							break;

						case 'radio':
							?>
						<div class="wps-form-group">
							<div class="wps-form-group__label">
								<label for="<?php echo esc_attr( $wsp_component['id'] ); ?>" class="wps-form-label"><?php echo ( isset( $wsp_component['title'] ) ? esc_html( $wsp_component['title'] ) : '' ); // WPCS: XSS ok. ?></label>
							</div>
							<div class="wps-form-group__control wps-pl-4">
								<div class="wps-flex-col">
									<?php
									foreach ( $wsp_component['options'] as $wsp_radio_key => $wsp_radio_val ) {
										?>
										<div class="mdc-form-field">
											<div class="mdc-radio">
												<input
												name="<?php echo ( isset( $wsp_component['name'] ) ? esc_html( $wsp_component['name'] ) : esc_html( $wsp_component['id'] ) ); ?>"
												value="<?php echo esc_attr( $wsp_radio_key ); ?>"
												type="radio"
												class="mdc-radio__native-control <?php echo ( isset( $wsp_component['class'] ) ? esc_attr( $wsp_component['class'] ) : '' ); ?>"
												<?php checked( $wsp_radio_key, $wsp_component['value'] ); ?>
												>
												<div class="mdc-radio__background">
													<div class="mdc-radio__outer-circle"></div>
													<div class="mdc-radio__inner-circle"></div>
												</div>
												<div class="mdc-radio__ripple"></div>
											</div>
											<label for="radio-1"><?php echo esc_html( $wsp_radio_val ); ?></label>
										</div>	
										<?php
									}
									?>
								</div>
							</div>
						</div>
							<?php
							break;

						case 'radio-switch':
							?>

						<div class="wps-form-group">
							<div class="wps-form-group__label">
								<label for="" class="wps-form-label"><?php echo ( isset( $wsp_component['title'] ) ? esc_html( $wsp_component['title'] ) : '' ); // WPCS: XSS ok. ?></label>
							</div>
							<div class="wps-form-group__control">
								<div>
									<div class="mdc-switch">
										<div class="mdc-switch__track"></div>
										<div class="mdc-switch__thumb-underlay">
											<div class="mdc-switch__thumb"></div>
											<input name="<?php echo ( isset( $wsp_component['name'] ) ? esc_html( $wsp_component['name'] ) : esc_html( $wsp_component['id'] ) ); ?>" type="checkbox" id="<?php echo esc_html( $wsp_component['id'] ); ?>" value="on" class="mdc-switch__native-control <?php echo ( isset( $wsp_component['class'] ) ? esc_attr( $wsp_component['class'] ) : '' ); ?>" role="switch" aria-checked="
																	<?php
																	if ( 'on' == $wsp_component['value'] ) {
																		echo 'true';
																	} else {
																		echo 'false';
																	}
																	?>
											"
											<?php checked( $wsp_component['value'], 'on' ); ?>
											>
										</div>
										<label for="checkbox-1"><?php echo ( isset( $wsp_component['description'] ) ? esc_attr( $wsp_component['description'] ) : '' ); ?></label>
									</div>
								</div>
							</div>
						</div>
							<?php
							break;

						case 'button':
							?>
						<div class="<?php echo isset( $wsp_component['id'] ) && 'wsp_save_other_settings' === $wsp_component['id'] ? 'wps-form-group-save' : 'wps-form-group'; ?>">
							<div class="wps-form-group__label"></div>
							<div class="wps-form-group__control">
								<button class="mdc-button mdc-button--raised" name= "<?php echo ( isset( $wsp_component['name'] ) ? esc_html( $wsp_component['name'] ) : esc_html( $wsp_component['id'] ) ); ?>"
									id="<?php echo esc_attr( $wsp_component['id'] ); ?>"> <span class="mdc-button__ripple"></span>
									<span class="mdc-button__label <?php echo ( isset( $wsp_component['class'] ) ? esc_attr( $wsp_component['class'] ) : '' ); ?>"><?php echo ( isset( $wsp_component['button_text'] ) ? esc_html( $wsp_component['button_text'] ) : '' ); ?></span>
								</button>
							</div>
						</div>

							<?php
							break;

						case 'multi':
							?>
							<div class="wps-form-group wps-isfw-<?php echo esc_attr( $wsp_component['type'] ); ?>">
								<div class="wps-form-group__label">
									<label for="<?php echo esc_attr( $wsp_component['id'] ); ?>" class="wps-form-label"><?php echo ( isset( $wsp_component['title'] ) ? esc_html( $wsp_component['title'] ) : '' ); // WPCS: XSS ok. ?></label>
									</div>
									<div class="wps-form-group__control">
									<?php
									foreach ( $wsp_component['value'] as $component ) {
										?>
											<label class="mdc-text-field mdc-text-field--outlined">
												<span class="mdc-notched-outline">
													<span class="mdc-notched-outline__leading"></span>
													<span class="mdc-notched-outline__notch">
														<?php if ( 'number' != $component['type'] ) { ?>
															<span class="mdc-floating-label" id="my-label-id" style=""><?php echo ( isset( $wsp_component['placeholder'] ) ? esc_attr( $wsp_component['placeholder'] ) : '' ); ?></span>
														<?php } ?>
													</span>
													<span class="mdc-notched-outline__trailing"></span>
												</span>
												<input 
												class="mdc-text-field__input <?php echo ( isset( $wsp_component['class'] ) ? esc_attr( $wsp_component['class'] ) : '' ); ?>" 
												name="<?php echo ( isset( $wsp_component['name'] ) ? esc_html( $wsp_component['name'] ) : esc_html( $wsp_component['id'] ) ); ?>"
												id="<?php echo esc_attr( $component['id'] ); ?>"
												type="<?php echo esc_attr( $component['type'] ); ?>"
												value="<?php echo ( isset( $wsp_component['value'] ) ? esc_attr( $wsp_component['value'] ) : '' ); ?>"
												placeholder="<?php echo ( isset( $wsp_component['placeholder'] ) ? esc_attr( $wsp_component['placeholder'] ) : '' ); ?>"
												<?php echo esc_attr( ( 'number' === $component['type'] ) ? 'max=10 min=0' : '' ); ?>
												>
											</label>
								<?php } ?>
									<div class="mdc-text-field-helper-line">
										<div class="mdc-text-field-helper-text--persistent wps-helper-text" id="" aria-hidden="true"><?php echo ( isset( $wsp_component['description'] ) ? esc_attr( $wsp_component['description'] ) : '' ); ?></div>
									</div>
								</div>
							</div>
								<?php
							break;
						case 'color':
						case 'date':
						case 'file':
							?>
							<div class="wps-form-group wps-isfw-<?php echo esc_attr( $wsp_component['type'] ); ?>">
								<div class="wps-form-group__label">
									<label for="<?php echo esc_attr( $wsp_component['id'] ); ?>" class="wps-form-label"><?php echo ( isset( $wsp_component['title'] ) ? esc_html( $wsp_component['title'] ) : '' ); // WPCS: XSS ok. ?></label>
								</div>
								<div class="wps-form-group__control">
									<label class="mdc-text-field mdc-text-field--outlined">
										<input 
										class="<?php echo ( isset( $wsp_component['class'] ) ? esc_attr( $wsp_component['class'] ) : '' ); ?>" 
										name="<?php echo ( isset( $wsp_component['name'] ) ? esc_html( $wsp_component['name'] ) : esc_html( $wsp_component['id'] ) ); ?>"
										id="<?php echo esc_attr( $wsp_component['id'] ); ?>"
										type="<?php echo esc_attr( $wsp_component['type'] ); ?>"
										value="<?php echo ( isset( $wsp_component['value'] ) ? esc_attr( $wsp_component['value'] ) : '' ); ?>"
										<?php echo esc_html( ( 'date' === $wsp_component['type'] ) ? 'max=' . gmdate( 'Y-m-d', strtotime( gmdate( 'Y-m-d', mktime() ) . ' + 365 day' ) ) . 'min=' . gmdate( 'Y-m-d' ) : '' ); ?>
										>
									</label>
									<div class="mdc-text-field-helper-line">
										<div class="mdc-text-field-helper-text--persistent wps-helper-text" id="" aria-hidden="true"><?php echo ( isset( $wsp_component['description'] ) ? esc_attr( $wsp_component['description'] ) : '' ); ?></div>
									</div>
								</div>
							</div>
							<?php
							break;
						case 'submit':
							?>
						<tr valign="top">
							<td scope="row">
								<input type="submit" class="button button-primary" 
								name="<?php echo ( isset( $wsp_component['name'] ) ? esc_html( $wsp_component['name'] ) : esc_html( $wsp_component['id'] ) ); ?>"
								id="<?php echo esc_attr( $wsp_component['id'] ); ?>"
								class="<?php echo ( isset( $wsp_component['class'] ) ? esc_attr( $wsp_component['class'] ) : '' ); ?>"
								value="<?php echo esc_attr( $wsp_component['button_text'] ); ?>"
								/>
							</td>
						</tr>
							<?php
						case 'section':
							?>
							<tr valign="top">
								<td scope="row">
									<h3><?php echo isset( $wsp_component['id'] ) ? esc_attr( $wsp_component['id'] ) : null; ?></h3>
								</td>
							</tr>
							<?php
							break;

						default:
							break;
					}
				}
			}
		}
	}
}
