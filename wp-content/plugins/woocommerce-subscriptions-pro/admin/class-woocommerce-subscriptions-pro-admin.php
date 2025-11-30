<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    Woocommerce_Subscriptions_Pro
 * @subpackage Woocommerce_Subscriptions_Pro/admin
 */

use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woocommerce_Subscriptions_Pro
 * @subpackage Woocommerce_Subscriptions_Pro/admin
 * @author     WP Swings <webmaster@wpswings.com>
 */
class Woocommerce_Subscriptions_Pro_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 * @param    string $hook      The plugin page slug.
	 */
	public function wsp_admin_enqueue_styles( $hook ) {

		$wps_sfw_screen_ids = wps_sfw_get_page_screen();
		$screen = get_current_screen();
		if ( ( isset( $screen->id ) && in_array( $screen->id, $wps_sfw_screen_ids ) ) || 'wps_subscriptions' == $screen->id || 'woocommerce_page_wc-orders--wps_subscriptions' === $screen->id ) {

			wp_enqueue_style( $this->plugin_name, WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'admin/css/woocommerce-subscriptions-pro-admin.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'jquery-ui-css', 'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css' );
		}

		if ( isset( $screen->id ) && 'product' == $screen->id ) {

			wp_enqueue_style( $this->plugin_name, WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'admin/css/woocommerce-subscriptions-pro-product-edit.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 * @param    string $hook      The plugin page slug.
	 */
	public function wsp_admin_enqueue_scripts( $hook ) {

		$wps_sfw_screen_ids = wps_sfw_get_page_screen();
		$screen = get_current_screen();

		if ( ( isset( $screen->id ) && in_array( $screen->id, $wps_sfw_screen_ids ) ) || 'wps_subscriptions' == $screen->id || 'woocommerce_page_wc-orders--wps_subscriptions' == $screen->id ) {

			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_register_script( $this->plugin_name . 'admin-js', WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'admin/js/woocommerce-subscriptions-pro-admin.js', array( 'jquery' ), $this->version, false );

			wp_localize_script(
				$this->plugin_name . 'admin-js',
				'wsp_admin_param',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'wps_auth_nonce'    => wp_create_nonce( 'wps_sfw_admin_nonce' ),
					'reloadurl' => admin_url( 'admin.php?page=subscriptions_for_woocommerce_menu' ),
					'screen_id' => $screen->id,
					'subscription_price_error' => __( 'Subscription price must be greater than 0.', 'woocommerce-subscriptions-pro' ),
					'subscription_next_payment_date_error' => __( 'Next payment date cannot be in the past date. Kindly check!', 'woocommerce-subscriptions-pro' ),
				)
			);

			wp_enqueue_script( $this->plugin_name . 'admin-js' );

		}
		if ( isset( $screen->id ) && 'product' == $screen->id ) {

			wp_register_script( 'wps-wsp-admin-single-product-js', WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'admin/js/woocommerce-subscriptions-pro-product-edit.js', array( 'jquery' ), $this->version, false );
			wp_enqueue_script( 'wps-wsp-admin-single-product-js' );
			$wps_sfw_data = array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'reloadurl' => admin_url( 'admin.php?page=subscriptions_for_woocommerce_menu' ),
				'day' => __( 'Days', 'woocommerce-subscriptions-pro' ),
				'week' => __( 'Weeks', 'woocommerce-subscriptions-pro' ),
				'month' => __( 'Months', 'woocommerce-subscriptions-pro' ),
				'year' => __( 'Years', 'woocommerce-subscriptions-pro' ),
			);
			wp_localize_script(
				'wps-wsp-admin-single-product-js',
				'wps_wsp_product_param',
				$wps_sfw_data
			);
		}

		// License validation.
		$wps_wsp_license_tab = isset( $_GET['sfw_tab'] ) ? sanitize_text_field( wp_unslash( $_GET['sfw_tab'] ) ) : '';
		if ( isset( $screen->id ) && in_array( $screen->id, $wps_sfw_screen_ids ) && 'woocommerce-subscriptions-pro-license' == $wps_wsp_license_tab ) {
			wp_register_script( $this->plugin_name . 'license-js', WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'admin/js/woocommerce-subscriptions-pro-license.js', array( 'jquery' ), $this->version, false );
			wp_localize_script(
				$this->plugin_name . 'license-js',
				'wsp_admin_param',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'reloadurl' => admin_url( 'admin.php?page=subscriptions_for_woocommerce_menu' ),
					'wps_wsp_license_nonce' => wp_create_nonce( 'wps-wsp-nonce-action' ),
				)
			);
			wp_enqueue_script( $this->plugin_name . 'license-js' );
		}

		// license notification.
		if ( ( isset( $screen->id ) && 'plugins' === $screen->id ) || ( 'wp-swings_page_home' == $screen->id ) || ( 'wp-swings_page_subscriptions_for_woocommerce_menu' == $screen->id ) ) {

			wp_register_script( $this->plugin_name . 'notice-admin-js', WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'admin/js/woocommerce-subscriptions-pro-notice-admin.js', array( 'jquery' ), $this->version, false );

			wp_localize_script(
				$this->plugin_name . 'notice-admin-js',
				'wsfwp_admin_notice_param',
				array(
					'ajaxurl'                       => admin_url( 'admin-ajax.php' ),
					'reloadurl'                     => admin_url( 'admin.php?page=subscriptions_for_woocommerce_menu' ),

					'wsfwp_ajax_error'                => __( 'An error occured!', 'woocommerce-subscriptions-pro' ),
					'wsfwp_admin_param_location'      => ( admin_url( 'admin.php' ) . '?page=subscriptions_for_woocommerce_menu&sfw_tab=subscriptions-for-woocommerce-general' ),
					'nonce'                           => wp_create_nonce( 'check-nonce' ),
				)
			);

			wp_enqueue_script( $this->plugin_name . 'notice-admin-js' );
		}

		// wps paypal integration js script.

		wp_enqueue_script( $this->plugin_name . 'paypal-intergration', WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'includes/wps-paypal-subscription/wps-paypal-subscription-integration-admin.js', array( 'jquery' ), $this->version, false );

		wp_localize_script(
			$this->plugin_name . 'paypal-intergration',
			'wps_paypal_subscription',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'reloadurl' => admin_url( 'admin.php?page=subscriptions_for_woocommerce_menu' ),
				'auth_nonce'    => wp_create_nonce( 'wps_paypal_subscription_admin_nonce' ),
				'empty_fields'    => esc_html__( 'Make Sure, You have filled the Client ID and Client secret keys', 'woocommerce-subscriptions-pro' ),
			)
		);

		// Subscription report handling script.
		if ( isset( $_GET['sfw_tab'] ) && 'woocommerce-subscriptions-pro-report' === $_GET['sfw_tab'] ) {
			$script_path       = '../../build/index.js';
			$script_asset_path = WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_PATH . 'build/index-asset.php';
			$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(
					'wp-hooks',
					'wp-element',
					'wp-i18n',
					'wc-components',
				),
				'version'      => filemtime( $script_path ),
			);
			$script_url        = WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'build/index.js';

			wp_register_script(
				'wps-wsp-react-app-block',
				$script_url,
				$script_asset['dependencies'],
				$script_asset['version'],
				true
			);
			wp_enqueue_script( 'wps-wsp-react-app-block' );
			wp_localize_script(
				'wps-wsp-react-app-block',
				'wps_wsp_localize_report_data',
				array(
					'ajaxurl'            => admin_url( 'admin-ajax.php' ),
					'wps_wsp_react_nonce' => wp_create_nonce( 'ajax-nonce' ),
					'redirect_url' => admin_url( 'admin.php?page=subscriptions_for_woocommerce_menu' ),
					'wc_currency' => get_woocommerce_currency_symbol(),
					'subscription_redirect' => admin_url( 'admin.php?page=subscriptions_for_woocommerce_menu&sfw_tab=subscriptions-for-woocommerce-subscriptions-table&wps_order_type=subscription&id=' ),
					'renewal_section_redirect' => admin_url( 'admin.php?renewal_section_redirect=yes&renewal_id=' ),
					'subscription_renewal_link' => admin_url( 'admin.php?page=subscriptions_for_woocommerce_menu&sfw_tab=subscriptions-for-woocommerce-subscriptions-table&wps_subscription_view_renewal_order=pending&_wpnonce=noneed&wps_subscription_id=' ),
					'product_edit_url' => admin_url( 'post.php?&action=edit&post=' ),
					'wps_report_grid1' => esc_attr__( 'Total Subscriptions Sale', 'woocommerce-subscriptions-pro' ),
					'wps_report_grid2' => esc_attr__( 'Top Subscribe Products', 'woocommerce-subscriptions-pro' ),
					'wps_report_grid3' => esc_attr__( 'Total Renewals', 'woocommerce-subscriptions-pro' ),
					'wps_report_grid4' => esc_attr__( 'Cancelled Subscriptions', 'woocommerce-subscriptions-pro' ),
					'wps_report_grid5' => esc_attr__( 'Renewed Subscriptions', 'woocommerce-subscriptions-pro' ),
					'wps_report_grid6' => esc_attr__( 'Monthly Renewal Revenue(MRR)', 'woocommerce-subscriptions-pro' ),
					'wps_report_arr' => esc_attr__( 'Annual Recurring Revenue(ARR)', 'woocommerce-subscriptions-pro' ),
					'wps_report_churn_rate' => esc_attr__( 'Churn Rate', 'woocommerce-subscriptions-pro' ),
					'wps_report_no_data_found' => esc_attr__( 'No Data Found', 'woocommerce-subscriptions-pro' ),
					'wps_report_subscription_id' => esc_attr__( 'Subscription ID', 'woocommerce-subscriptions-pro' ),
					'wps_report_parent_id' => esc_attr__( 'Parent ID', 'woocommerce-subscriptions-pro' ),
					'wps_report_renewal_id' => esc_attr__( 'Renewal ID', 'woocommerce-subscriptions-pro' ),
					'wps_report_status' => esc_attr__( 'Status', 'woocommerce-subscriptions-pro' ),
					'wps_report_last_renewal_id' => esc_attr__( 'Last Renewal ID', 'woocommerce-subscriptions-pro' ),
					'wps_report_created_date' => esc_attr__( 'Created Date', 'woocommerce-subscriptions-pro' ),
					'wps_report_product_id' => esc_attr__( 'Product ID', 'woocommerce-subscriptions-pro' ),
					'wps_report_product_name' => esc_attr__( 'Name', 'woocommerce-subscriptions-pro' ),
					'wps_report_product_type' => esc_attr__( 'Type', 'woocommerce-subscriptions-pro' ),
					'wps_report_product_total_purchased' => esc_attr__( 'Total Purchased', 'woocommerce-subscriptions-pro' ),
					'wps_report_cancelled_reason' => esc_attr__( 'Reason', 'woocommerce-subscriptions-pro' ),
					'wps_report_cancelled_date' => esc_attr__( 'Cancelled Date', 'woocommerce-subscriptions-pro' ),
					'wps_report_see_renewals' => esc_attr__( 'See Renewals', 'woocommerce-subscriptions-pro' ),
					'wps_report_total' => esc_attr__( 'Total', 'woocommerce-subscriptions-pro' ),
					'wps_report_detailed_data' => esc_attr__( 'Detailed Data', 'woocommerce-subscriptions-pro' ),

					'wps_report_total_sale' => esc_attr__( 'Total Sale', 'woocommerce-subscriptions-pro' ),
					'wps_report_new_subscriptions' => esc_attr__( 'New Subscriptions', 'woocommerce-subscriptions-pro' ),
					'wps_report_product' => esc_attr__( 'Product', 'woocommerce-subscriptions-pro' ),
					'wps_report_total_renewal_order' => esc_attr__( 'Total Renewal Order', 'woocommerce-subscriptions-pro' ),
					'wps_report_total_cancelled_subscriptions' => esc_attr__( 'Total Cancelled Subscriptions', 'woocommerce-subscriptions-pro' ),
					'wps_report_total_subscription_renewed' => esc_attr__( 'Total Subscription Renewed', 'woocommerce-subscriptions-pro' ),

					'wps_report_previous' => esc_attr__( 'Previous', 'woocommerce-subscriptions-pro' ),
					'wps_report_page' => esc_attr__( 'Page', 'woocommerce-subscriptions-pro' ),
					'wps_report_next' => esc_attr__( 'Next', 'woocommerce-subscriptions-pro' ),
					'wps_report_show' => esc_attr__( 'Show', 'woocommerce-subscriptions-pro' ),
					'wps_report_loading' => esc_attr__( 'Loading...', 'woocommerce-subscriptions-pro' ),

				)
			);
		}
	}

	/**
	 * WooCommerce Subscriptions PRO admin menu page.
	 *
	 * @param array $wps_sfw_tabs wps_sfw_tabs.
	 * @since    1.0.0
	 */
	public function wsp_admin_other_settings_page( $wps_sfw_tabs ) {

		$thirty_days = get_option( 'wps_wsp_lcns_thirty_days', 0 );
		$current_time = current_time( 'timestamp' );
		$day_count = ( $thirty_days - $current_time ) / ( 24 * 60 * 60 );
		$wps_wsp_license_key = get_option( 'wps_wsp_license_key', '' );

		if ( ! empty( $wps_wsp_license_key ) || 0 <= $day_count ) {
			$wps_sfw_tabs['woocommerce-subscriptions-pro-others'] = array(
				'title'       => esc_html__( 'Advance Settings', 'woocommerce-subscriptions-pro' ),
				'name'        => 'woocommerce-subscriptions-pro-others',
				'file_path'       => WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_PATH,
			);
			$wps_sfw_tabs['woocommerce-subscriptions-pro-report'] = array(
				'title'       => esc_html__( 'Report', 'woocommerce-subscriptions-pro' ),
				'name'        => 'woocommerce-subscriptions-pro-report',
				'file_path'       => WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_PATH,
			);
		}
		if ( empty( $wps_wsp_license_key ) ) {

			$wps_sfw_tabs['woocommerce-subscriptions-pro-license'] = array(
				'title'       => esc_html__( 'License', 'woocommerce-subscriptions-pro' ),
				'name'        => 'woocommerce-subscriptions-pro-license',
				'file_path'       => WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_PATH,
			);
		}
		return $wps_sfw_tabs;
	}


	/**
	 * WooCommerce Subscriptions PRO admin menu page.
	 *
	 * @since    1.0.0
	 * @param array $wsp_settings_general Settings fields.
	 */
	public function wsp_admin_other_settings_fields( $wsp_settings_general ) {
		$roles = wp_roles()->roles;

		$roles_kv = array(
			'none' => esc_html__( 'Choose any role', 'woocommerce-subscriptions-pro' ),
		);
		foreach ( $roles as $role_key => $role_data ) {
			$roles_kv[ $role_key ] = $role_data['name'];
		}
		$wsp_settings_general = array(
			array(
				'id'  => __( 'Subscription Manage by Customer', 'woocommerce-subscriptions-pro' ),
				'type'  => 'section',
			),
			array(
				'title' => __( 'Give ability to pause the subscription for a certain time', 'woocommerce-subscriptions-pro' ),
				'type'  => 'radio-switch',
				'id'    => 'wsp_enable_pause_susbcription_by_customer',
				'value' => get_option( 'wsp_enable_pause_susbcription_by_customer' ),
				'class' => 'wsp-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'woocommerce-subscriptions-pro' ),
					'no' => __( 'NO', 'woocommerce-subscriptions-pro' ),
				),
			),
			array(
				'title' => __( 'Give ability to reactivate the paused subscription by customer', 'woocommerce-subscriptions-pro' ),
				'type'  => 'radio-switch',
				'id'    => 'wsp_start_pause_susbcription_by_customer',
				'value' => get_option( 'wsp_start_pause_susbcription_by_customer' ),
				'class' => 'wsp-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'woocommerce-subscriptions-pro' ),
					'no' => __( 'NO', 'woocommerce-subscriptions-pro' ),
				),
			),
			array(
				'title' => __( 'Give Ability to Edit their active subscription', 'woocommerce-subscriptions-pro' ),
				'type'  => 'radio-switch',
				'description'  => '',
				'id'    => 'wsp_allow_customer_subsciption_edit',
				'value' => get_option( 'wsp_allow_customer_subsciption_edit' ),
				'class' => 'wsp-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'woocommerce-subscriptions-pro' ),
					'no' => __( 'NO', 'woocommerce-subscriptions-pro' ),
				),
			),
			array(
				'title' => __( 'Allow the Time duration for the Subscription cancellation', 'woocommerce-subscriptions-pro' ),
				'type'  => 'radio-switch',
				'description'  => '',
				'id'    => 'wsp_allow_time_subscription_cancellation',
				'value' => get_option( 'wsp_allow_time_subscription_cancellation' ),
				'class' => 'wsp-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'woocommerce-subscriptions-pro' ),
					'no' => __( 'NO', 'woocommerce-subscriptions-pro' ),
				),
			),
			array(
				'title' => __( 'Enter the number of days after which the user will be able to cancel their subscription', 'woocommerce-subscriptions-pro' ),
				'type'  => 'number',
				'description'  => '',
				'id'    => 'wsp_time_duration_subscription_cancellation',
				'value' => get_option( 'wsp_time_duration_subscription_cancellation', null ),
				'class' => 'wsp-number-class',
				'placeholder' => '',
				'min'   => 1,
			),
			array(
				'title' => __( 'Allow customer to choose subscription expiry date', 'woocommerce-subscriptions-pro' ),
				'type'  => 'radio-switch',
				'id'    => 'wsp_allow_subscription_expiry_customer',
				'value' => get_option( 'wsp_allow_subscription_expiry_customer' ),
				'class' => 'wsp-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'woocommerce-subscriptions-pro' ),
					'no' => __( 'NO', 'woocommerce-subscriptions-pro' ),
				),
			),
			array(
				'id' => __( 'Failed Renewal Attempts', 'woocommerce-subscriptions-pro' ),
				'type'  => 'section',
			),
			array(
				'title' => __( 'Enable automatic payment retry for failed attempts', 'woocommerce-subscriptions-pro' ),
				'type'  => 'radio-switch',
				'id'    => 'wsp_enable_automatic_retry_failed_attempts',
				'value' => get_option( 'wsp_enable_automatic_retry_failed_attempts' ),
				'class' => 'wsp-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'woocommerce-subscriptions-pro' ),
					'no' => __( 'NO', 'woocommerce-subscriptions-pro' ),
				),
			),
			array(
				'title' => __( 'Enter the number of failed payment attempts', 'woocommerce-subscriptions-pro' ),
				'type'  => 'number',
				'description'  => __( 'After a certain number of failed attempts, the subscription will be canceled.', 'woocommerce-subscriptions-pro' ),
				'id'    => 'wsp_after_no_failed_attempt_cancel',
				'value' => get_option( 'wsp_after_no_failed_attempt_cancel', '3' ),
				'class' => 'wsp-number-class',
				'placeholder' => __( 'Enter the number after certain failed attempts subscription will be canceled', 'woocommerce-subscriptions-pro' ),
				'min'   => 1,
			),
			array(
				'id' => __( 'Email Notification', 'woocommerce-subscriptions-pro' ),
				'type'  => 'section',
			),
			array(
				'title' => __( 'Ability to send subscription is going to expire email notification', 'woocommerce-subscriptions-pro' ),
				'type'  => 'radio-switch',
				'id'    => 'wsp_enable_plan_going_to_expire',
				'value' => get_option( 'wsp_enable_plan_going_to_expire' ),
				'class' => 'wsp-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'woocommerce-subscriptions-pro' ),
					'no' => __( 'NO', 'woocommerce-subscriptions-pro' ),
				),
			),
			array(
				'title' => __( 'Enter the number of days before subscription expire email send', 'woocommerce-subscriptions-pro' ),
				'type'  => 'number',
				'id'    => 'wsp_plan_going_to_expire_before_days',
				'value' => get_option( 'wsp_plan_going_to_expire_before_days', '7' ),
				'class' => 'wsp-number-class',
				'placeholder' => __( 'Enter the number of days before subscription expire email send', 'woocommerce-subscriptions-pro' ),
				'min'   => 1,
			),
			array(
				'title' => __( 'Enter the number of days before which you want to send the recurring payment reminder.', 'woocommerce-subscriptions-pro' ),
				'type'  => 'number',
				'description'  => __( 'Enter the number of days before which you want to send the recurring payment reminder.', 'woocommerce-subscriptions-pro' ),
				'id'    => 'wsp_send_before_recurring_reminder',
				'value' => get_option( 'wsp_send_before_recurring_reminder', '5' ),
				'class' => 'wsp-number-class',
				'placeholder' => __( 'Enter the number of days before which you want to send the recurring payment reminder.', 'woocommerce-subscriptions-pro' ),
				'min'   => 1,
			),
			array(
				'id'  => __( 'Variable Subscriptions Upgrade/Downgrade', 'woocommerce-subscriptions-pro' ),
				'type'  => 'section',
			),
			array(
				'title' => __( 'Give ability to upgrade/downgrade', 'woocommerce-subscriptions-pro' ),
				'type'  => 'radio-switch',
				'id'    => 'wsp_enbale_downgrade_upgrade_subscription',
				'value' => get_option( 'wsp_enbale_downgrade_upgrade_subscription' ),
				'class' => 'wsp-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'woocommerce-subscriptions-pro' ),
					'no' => __( 'NO', 'woocommerce-subscriptions-pro' ),
				),
			),
			array(
				'title' => __( 'Allow Upgrade/Downgrade only with same interval', 'woocommerce-subscriptions-pro' ),
				'type'  => 'radio-switch',
				'id'    => 'wsp_enable_allow_same_interval',
				'value' => get_option( 'wsp_enable_allow_same_interval' ),
				'class' => 'wsp-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'woocommerce-subscriptions-pro' ),
					'no' => __( 'NO', 'woocommerce-subscriptions-pro' ),
				),
			),
			array(
				'title' => __( 'Do not allow Downgrade', 'woocommerce-subscriptions-pro' ),
				'type'  => 'radio-switch',
				'id'    => 'wps_wsp_downgrade_variable_subscription',
				'value' => get_option( 'wps_wsp_downgrade_variable_subscription' ),
				'class' => 'wsp-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'woocommerce-subscriptions-pro' ),
					'no' => __( 'NO', 'woocommerce-subscriptions-pro' ),
				),
			),
			array(
				'title' => __( 'Upgrade and Downgrade button text', 'woocommerce-subscriptions-pro' ),
				'type'  => 'text',
				'id'    => 'wps_wsp_upgrade_downgrade_btn_text',
				'value' => get_option( 'wps_wsp_upgrade_downgrade_btn_text', 'Upgrade and Downgrade' ),
				'class' => 'wsp-text-class',
				'placeholder' => __( 'Upgrade and Downgrade button text', 'woocommerce-subscriptions-pro' ),
			),
			array(
				'title' => __( 'Ability to accept prorate price during Upgrade/Downgrade', 'woocommerce-subscriptions-pro' ),
				'type'  => 'radio-switch',
				'id'    => 'wsp_enable_prorate_on_price_downgrade_upgrade_subscription',
				'value' => get_option( 'wsp_enable_prorate_on_price_downgrade_upgrade_subscription' ),
				'class' => 'wsp-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'woocommerce-subscriptions-pro' ),
					'no' => __( 'NO', 'woocommerce-subscriptions-pro' ),
				),
			),
			array(
				'title' => __( 'Manage prorate price during Upgrade/Downgrade', 'woocommerce-subscriptions-pro' ),
				'type'  => 'radio',
				'description'  => __( 'Enable this to downgrade variable subscription.', 'woocommerce-subscriptions-pro' ),
				'id'    => 'wps_wsp_manage_prorate_amount',
				'value' => get_option( 'wps_wsp_manage_prorate_amount' ),
				'class' => 'wsp-radio-switch-class',
				'options' => array(
					'wps_manage_prorate_next_payment_date' => __( 'Extend next payment date', 'woocommerce-subscriptions-pro' ),
					'wps_manage_prorate_using_wallet' => __( 'Put left amount in the user wallet', 'woocommerce-subscriptions-pro' ),
				),
			),
			array(
				'title' => __( 'Ability to accept prorate signup fee during Upgrade/Downgrade', 'woocommerce-subscriptions-pro' ),
				'type'  => 'radio-switch',
				'id'    => 'wsp_enable_signup_fee_downgrade_upgrade_subscription',
				'value' => get_option( 'wsp_enable_signup_fee_downgrade_upgrade_subscription' ),
				'class' => 'wsp-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'woocommerce-subscriptions-pro' ),
					'no' => __( 'NO', 'woocommerce-subscriptions-pro' ),
				),
			),
			array(
				'id'  => __( 'Renewal Date Synchronization', 'woocommerce-subscriptions-pro' ),
				'type'  => 'section',
			),

			array(
				'title' => __( 'Ability to take renewal payment from the certain date', 'woocommerce-subscriptions-pro' ),
				'type'  => 'radio-switch',
				'id'    => 'wsp_start_susbcription_from_certain_date_of_month',
				'value' => get_option( 'wsp_start_susbcription_from_certain_date_of_month' ),
				'class' => 'wsp-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'woocommerce-subscriptions-pro' ),
					'no' => __( 'NO', 'woocommerce-subscriptions-pro' ),
				),
			),
			array(
				'title' => __( 'Prorate price for certain date of month', 'woocommerce-subscriptions-pro' ),
				'type'  => 'select',
				'id'    => 'wsp_prorate_price_on_sync',
				'name'  => 'wsp_prorate_price_on_sync',
				'value' => get_option( 'wsp_prorate_price_on_sync', 'wps_wsp_prorate_no' ),
				'class' => 'wsp-select-class',
				'options' => array(
					'wps_wsp_prorate_no' => __( 'Do not charge prorate price', 'woocommerce-subscriptions-pro' ),
					'wps_wsp_prorate_simple' => __( 'Charge prorate price', 'woocommerce-subscriptions-pro' ),
					'wps_wsp_prorate_if_free_trial' => __( 'Charge prorate price, even if free trial', 'woocommerce-subscriptions-pro' ),
				),
			),

			array(
				'id'  => __( 'Manage Subscription Products and Quantities in the Cart', 'woocommerce-subscriptions-pro' ),
				'type'  => 'section',
			),

			array(
				'title' => __( 'Ability to allow the customer to add multiple quantity subscription in cart.', 'woocommerce-subscriptions-pro' ),
				'type'  => 'radio-switch',
				'id'    => 'wsp_allow_multiple_quantity_subscription',
				'value' => get_option( 'wsp_allow_multiple_quantity_subscription' ),
				'class' => 'wsp-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'woocommerce-subscriptions-pro' ),
					'no' => __( 'NO', 'woocommerce-subscriptions-pro' ),
				),
			),
			array(
				'title' => __( 'Ability to allow the customer to add multiple subscriptions in cart', 'woocommerce-subscriptions-pro' ),
				'type'  => 'radio-switch',
				'id'    => 'wsp_allow_to_add_multiple_subscription_cart',
				'value' => get_option( 'wsp_allow_to_add_multiple_subscription_cart' ),
				'class' => 'wsp-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'woocommerce-subscriptions-pro' ),
					'no' => __( 'NO', 'woocommerce-subscriptions-pro' ),
				),
			),
			array(
				'id'  => __( 'Manage Shipping Cost', 'woocommerce-subscriptions-pro' ),
				'type'  => 'section',
			),
			array(
				'title' => __( 'Allow shipping cost during checkout only', 'woocommerce-subscriptions-pro' ),
				'type'  => 'radio-switch',
				'id'    => 'wsp_allow_shipping_on_subscription_first_puchase',
				'value' => get_option( 'wsp_allow_shipping_on_subscription_first_puchase' ),
				'class' => 'wsp-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'woocommerce-subscriptions-pro' ),
					'no' => __( 'NO', 'woocommerce-subscriptions-pro' ),
				),
			),
			array(
				'title' => __( 'Allow Shipping Costs for Renewals', 'woocommerce-subscriptions-pro' ),
				'type'  => 'radio-switch',
				'id'    => 'wsp_allow_shipping_subscription',
				'value' => get_option( 'wsp_allow_shipping_subscription', 'on' ),
				'class' => 'wsp-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'woocommerce-subscriptions-pro' ),
					'no' => __( 'NO', 'woocommerce-subscriptions-pro' ),
				),
			),
			array(
				'id'  => __( 'User Roles', 'woocommerce-subscriptions-pro' ),
				'type'  => 'section',
			),
			array(
				'title' => __( 'Default Subscriber Role', 'woocommerce-subscriptions-pro' ),
				'type'  => 'select',
				'id'    => 'wps_wsp_subsciber_user_role',
				'name'  => 'wps_wsp_subsciber_user_role',
				'value' => get_option( 'wps_wsp_subsciber_user_role', '' ),
				'class' => 'wsp-select-class',
				'options' => $roles_kv,
				'description' => __( 'Assign this role to new users when a subscription is activated, either manually or after a successful purchase', 'woocommerce-subscriptions-pro' ),
			),
			array(
				'title' => __( 'Inactive Subscriber Role', 'woocommerce-subscriptions-pro' ),
				'type'  => 'select',
				'id'    => 'wps_wsp_inactive_subscriber_role',
				'name'  => 'wps_wsp_inactive_subscriber_role',
				'value' => get_option( 'wps_wsp_inactive_subscriber_role', '' ),
				'class' => 'wsp-select-class',
				'options' => $roles_kv,
				'description' => __( 'Assign this role when a subscriber\'s subscription is cancelled or expires', 'woocommerce-subscriptions-pro' ),

			),
			array(
				'id'  => __( 'Other Settings', 'woocommerce-subscriptions-pro' ),
				'type'  => 'section',
			),

			array(
				'title' => __( 'Ability to checkout with BACS, COD and Cheque Payment Gateways', 'woocommerce-subscriptions-pro' ),
				'type'  => 'radio-switch',
				'id'    => 'wsp_enbale_accept_manual_payment',
				'value' => get_option( 'wsp_enbale_accept_manual_payment' ),
				'class' => 'wsp-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'woocommerce-subscriptions-pro' ),
					'no' => __( 'NO', 'woocommerce-subscriptions-pro' ),
				),
			),

			array(
				'title' => __( 'Allow start date on subscription products.', 'woocommerce-subscriptions-pro' ),
				'type'  => 'radio-switch',
				'id'    => 'wsp_allow_start_date_subscription',
				'value' => get_option( 'wsp_allow_start_date_subscription' ),
				'class' => 'wsp-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'woocommerce-subscriptions-pro' ),
					'no' => __( 'NO', 'woocommerce-subscriptions-pro' ),
				),
			),
		);

		$wsp_settings_general[] = array(
			'type'  => 'button',
			'id'    => 'wsp_save_other_settings',
			'button_text' => __( 'Save Settings', 'woocommerce-subscriptions-pro' ),
			'class' => 'wsp-button-class',
		);

		return apply_filters( 'wps_sfw_add_advance_settings_fields', $wsp_settings_general );
	}

	/**
	 * WooCommerce Subscriptions PRO save tab settings.
	 *
	 * @name wsp_admin_save_tab_settings
	 * @since 1.0.0
	 */
	public function wsp_admin_save_tab_settings() {
		global $wsp_wps_wsp_obj;
		global $wps_sfw_notices;
		if ( isset( $_POST['wsp_save_other_settings'] ) && isset( $_POST['wps-wsp-others-nonce-field'] ) ) {
			if ( ! isset( $_POST['wps-wsp-others-nonce-field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wps-wsp-others-nonce-field'] ) ), 'wps-wsp-others-nonce' ) ) {
				return;
			}
			$wps_wsp_gen_flag = false;
			$wsp_genaral_settings = apply_filters( 'wsp_others_settings_array', array() );
			$wsp_button_index = array_search( 'submit', array_column( $wsp_genaral_settings, 'type' ) );
			if ( isset( $wsp_button_index ) && ( null == $wsp_button_index || '' == $wsp_button_index ) ) {
				$wsp_button_index = array_search( 'button', array_column( $wsp_genaral_settings, 'type' ) );
			}
			if ( isset( $wsp_button_index ) && '' !== $wsp_button_index ) {
				unset( $wsp_genaral_settings[ $wsp_button_index ] );
				if ( is_array( $wsp_genaral_settings ) && ! empty( $wsp_genaral_settings ) ) {
					foreach ( $wsp_genaral_settings as $wsp_genaral_setting ) {
						if ( isset( $wsp_genaral_setting['id'] ) && '' !== $wsp_genaral_setting['id'] ) {
							if ( isset( $_POST[ $wsp_genaral_setting['id'] ] ) ) {
								$posted_value = sanitize_text_field( wp_unslash( $_POST[ $wsp_genaral_setting['id'] ] ) );
								update_option( $wsp_genaral_setting['id'], $posted_value );
							} else {
								update_option( $wsp_genaral_setting['id'], '' );
							}
						} else {
							$wps_wsp_gen_flag = true;
						}
					}
				}
				if ( $wps_wsp_gen_flag ) {
					$wps_wsp_error_text = esc_html__( 'Id of some field is missing', 'woocommerce-subscriptions-pro' );
					$wsp_wps_wsp_obj->wps_wsp_plug_admin_notice( $wps_wsp_error_text, 'error' );
				} else {
					$wps_sfw_notices = true;
				}
			}
		}
	}

	/**
	 * This function is used to create variable susbcription.
	 *
	 * @name wsp_sfw_woocommerce_variation_options
	 * @param int    $loop loop.
	 * @param array  $variation_data variation_data.
	 * @param object $variation variation.
	 * @since 1.0.0
	 */
	public function wsp_sfw_woocommerce_variation_options( $loop, $variation_data, $variation ) {
		$variation_id = $variation->ID;
		$wps_is_enable = wps_wsp_get_meta_data( $variation_id, 'wps_sfw_variable_product', true );

		?>
		<label class="tips" data-tip="<?php esc_attr_e( 'Enable this option to make subscription type variation', 'woocommerce-subscriptions-pro' ); ?>">
			<?php esc_html_e( 'Subscription', 'woocommerce-subscriptions-pro' ); ?>:
			<input type="checkbox" class="checkbox wps_sfw_variation_enable" name="wps_sfw_variation_enable[<?php echo esc_attr( $loop ); ?>]" <?php checked( $wps_is_enable, 'yes' ); ?> />
		</label>
		<?php
		wp_nonce_field( 'wps_wsp_variation_field', 'wps_wsp_variation_field_nonce', false );
	}

	/**
	 * This function is used to save variable susbcription.
	 *
	 * @name wsp_sfw_save_product_variation
	 * @param int $variation_id variation_id.
	 * @param int $loop loop.
	 * @since 1.0.0
	 */
	public function wsp_sfw_save_product_variation( $variation_id, $loop ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$wps_sfw_product = isset( $_POST['wps_sfw_variation_enable'][ $loop ] ) ? 'yes' : 'no';

		if ( ! isset( $_POST['wps_wsp_variation_field_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['wps_wsp_variation_field_nonce'] ) ), 'wps_wsp_variation_field' ) ) {
			return;
		}

		if ( 'yes' == $wps_sfw_product ) {

			$wps_sfw_variation_subscription_number = isset( $_POST['wps_sfw_variation_subscription_number'][ $loop ] ) ? sanitize_text_field( wp_unslash( $_POST['wps_sfw_variation_subscription_number'][ $loop ] ) ) : '';
			$wps_sfw_variation_subscription_interval = isset( $_POST['wps_sfw_variation_subscription_interval'][ $loop ] ) ? sanitize_text_field( wp_unslash( $_POST['wps_sfw_variation_subscription_interval'][ $loop ] ) ) : '';
			$wps_sfw_variation_subscription_expiry_number = isset( $_POST['wps_sfw_variation_subscription_expiry_number'][ $loop ] ) ? sanitize_text_field( wp_unslash( $_POST['wps_sfw_variation_subscription_expiry_number'][ $loop ] ) ) : '';
			$wps_sfw_variation_subscription_expiry_interval = isset( $_POST['wps_sfw_variation_subscription_expiry_interval'][ $loop ] ) ? sanitize_text_field( wp_unslash( $_POST['wps_sfw_variation_subscription_expiry_interval'][ $loop ] ) ) : '';

			$wps_sfw_variation_subscription_start_date = isset( $_POST['wps_sfw_variation_subscription_start_date'][ $loop ] ) ? sanitize_text_field( wp_unslash( $_POST['wps_sfw_variation_subscription_start_date'][ $loop ] ) ) : '';

			/*get valid subscription expiry*/
			$wps_sfw_variation_subscription_expiry_number = wps_wsp_get_valid_subscription_expiry( $wps_sfw_variation_subscription_expiry_number, $wps_sfw_variation_subscription_expiry_interval );
			$wps_sfw_variation_subscription_initial_signup_price = isset( $_POST['wps_sfw_variation_subscription_initial_signup_price'][ $loop ] ) ? sanitize_text_field( wp_unslash( $_POST['wps_sfw_variation_subscription_initial_signup_price'][ $loop ] ) ) : '';

			$wps_sfw_variation_subscription_free_trial_number = isset( $_POST['wps_sfw_variation_subscription_free_trial_number'][ $loop ] ) ? sanitize_text_field( wp_unslash( $_POST['wps_sfw_variation_subscription_free_trial_number'][ $loop ] ) ) : '';

			$wps_sfw_variation_subscription_free_trial_interval = isset( $_POST['wps_sfw_variation_subscription_free_trial_interval'][ $loop ] ) ? sanitize_text_field( wp_unslash( $_POST['wps_sfw_variation_subscription_free_trial_interval'][ $loop ] ) ) : '';
			/*get valid subscription expiry*/
			$wps_sfw_variation_subscription_free_trial_number = wps_wsp_get_valid_subscription_expiry( $wps_sfw_variation_subscription_free_trial_number, $wps_sfw_variation_subscription_free_trial_interval );

			$wps_sfw_variation_subscription_limit_for_trial = isset( $_POST['wps_sfw_variation_subscription_limit_for_trial'][ $loop ] ) ? sanitize_text_field( wp_unslash( $_POST['wps_sfw_variation_subscription_limit_for_trial'][ $loop ] ) ) : '';
			$wps_sfw_free_trials_limit = isset( $_POST['wps_sfw_free_trials_limit'][ $loop ] ) ? sanitize_text_field( wp_unslash( $_POST['wps_sfw_free_trials_limit'][ $loop ] ) ) : '';

			/*certain date of month*/

			$wps_wsp_enbale_certain_month = isset( $_POST['wps_wsp_variation_enbale_certain_month'][ $loop ] ) ? 'yes' : 'no';

			$wps_wsp_week_sync = isset( $_POST['wps_wsp_variation_week_sync'][ $loop ] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wsp_variation_week_sync'][ $loop ] ) ) : '';

			$wps_wsp_month_sync = isset( $_POST['wps_wsp_variation_month_sync'][ $loop ] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wsp_variation_month_sync'][ $loop ] ) ) : '';

			$wps_wsp_year_sync = isset( $_POST['wps_wsp_variation_year_sync'][ $loop ] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wsp_variation_year_sync'][ $loop ] ) ) : '';

			$wps_wsp_year_number = isset( $_POST['wps_wsp_variation_certain_date_enable_year_number'][ $loop ] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wsp_variation_certain_date_enable_year_number'][ $loop ] ) ) : 1;

			wps_wsp_update_meta_data( $variation_id, 'wps_wsp_enbale_certain_month', $wps_wsp_enbale_certain_month );
			wps_wsp_update_meta_data( $variation_id, 'wps_wsp_week_sync', $wps_wsp_week_sync );
			wps_wsp_update_meta_data( $variation_id, 'wps_wsp_month_sync', $wps_wsp_month_sync );
			wps_wsp_update_meta_data( $variation_id, 'wps_wsp_year_sync', $wps_wsp_year_sync );
			wps_wsp_update_meta_data( $variation_id, 'wps_wsp_year_number', $wps_wsp_year_number );

			/*certain date of month end*/

			wps_wsp_update_meta_data( $variation_id, 'wps_sfw_subscription_number', $wps_sfw_variation_subscription_number );
			wps_wsp_update_meta_data( $variation_id, 'wps_sfw_subscription_interval', $wps_sfw_variation_subscription_interval );
			wps_wsp_update_meta_data( $variation_id, 'wps_sfw_subscription_expiry_number', $wps_sfw_variation_subscription_expiry_number );
			wps_wsp_update_meta_data( $variation_id, 'wps_sfw_subscription_expiry_interval', $wps_sfw_variation_subscription_expiry_interval );
			wps_wsp_update_meta_data( $variation_id, 'wps_sfw_subscription_initial_signup_price', $wps_sfw_variation_subscription_initial_signup_price );
			wps_wsp_update_meta_data( $variation_id, 'wps_sfw_subscription_free_trial_number', $wps_sfw_variation_subscription_free_trial_number );
			wps_wsp_update_meta_data( $variation_id, 'wps_sfw_subscription_free_trial_interval', $wps_sfw_variation_subscription_free_trial_interval );
			if ( wps_wsp_allow_start_date_subscription() ) {
				wps_wsp_update_meta_data( $variation_id, 'wps_sfw_subscription_start_date', $wps_sfw_variation_subscription_start_date );
			}

			/*Set variable meta*/
			$variation = wc_get_product( $variation_id );
			if ( isset( $variation ) && ! empty( $variation ) ) {
				$product_id = $variation->get_parent_id();
				wps_wsp_update_meta_data( $product_id, 'wps_sfw_subscription_number', $wps_sfw_variation_subscription_number );
				wps_wsp_update_meta_data( $product_id, 'wps_sfw_subscription_interval', $wps_sfw_variation_subscription_interval );
				wps_wsp_update_meta_data( $product_id, 'wps_sfw_subscription_expiry_number', $wps_sfw_variation_subscription_expiry_number );
				wps_wsp_update_meta_data( $product_id, 'wps_sfw_subscription_expiry_interval', $wps_sfw_variation_subscription_expiry_interval );
				wps_wsp_update_meta_data( $product_id, 'wps_sfw_subscription_initial_signup_price', $wps_sfw_variation_subscription_initial_signup_price );
				wps_wsp_update_meta_data( $product_id, 'wps_sfw_subscription_free_trial_number', $wps_sfw_variation_subscription_free_trial_number );
				wps_wsp_update_meta_data( $product_id, 'wps_sfw_subscription_free_trial_interval', $wps_sfw_variation_subscription_free_trial_interval );
				wps_wsp_update_meta_data( $product_id, 'wps_sfw_variable_product', $wps_sfw_product );
				if ( wps_wsp_allow_start_date_subscription() ) {
					wps_wsp_update_meta_data( $product_id, 'wps_sfw_subscription_start_date', $wps_sfw_variation_subscription_start_date );
				}
			}

			// Save variable one time meta.
			$wps_sfw_one_time_purchase = isset( $_POST['wps_sfw_one_time_purchase'][ $loop ] ) ? sanitize_text_field( wp_unslash( $_POST['wps_sfw_one_time_purchase'][ $loop ] ) ) : '';
			$wps_sfw_subscription_one_time_price = isset( $_POST['wps_sfw_subscription_one_time_price'][ $loop ] ) ? sanitize_text_field( wp_unslash( $_POST['wps_sfw_subscription_one_time_price'][ $loop ] ) ) : '';
			wps_wsp_update_meta_data( $variation_id, 'wps_sfw_one_time_purchase', $wps_sfw_one_time_purchase );
			wps_wsp_update_meta_data( $variation_id, 'wps_wsp_onetime_price', $wps_sfw_subscription_one_time_price );

			wps_wsp_update_meta_data( $variation_id, 'wps_sfw_subscription_limit_for_trial', $wps_sfw_variation_subscription_limit_for_trial );
			wps_wsp_update_meta_data( $variation_id, 'wps_sfw_free_trials_limit', $wps_sfw_free_trials_limit );

			$learnpress_courses = isset( $_POST['wps_learnpress_course'][ $loop ] ) ? wp_unslash( $_POST['wps_learnpress_course'][ $loop ] ) : '';
			if ( is_array( $learnpress_courses ) ) {
				$learnpress_courses = array_map( 'sanitize_text_field', $learnpress_courses );
			} else {
				$learnpress_courses = sanitize_text_field( $learnpress_courses );
			}
			wps_wsp_update_meta_data( $variation_id, 'wps_learnpress_course', $learnpress_courses );
			$all_attached_courses = get_option( 'wps_learnpress_course', array() );
			$all_attached_courses[ $variation_id ] = $learnpress_courses;
			update_option( 'wps_learnpress_course', $all_attached_courses );
		}

		wps_wsp_update_meta_data( $variation_id, 'wps_sfw_variable_product', $wps_sfw_product );

		do_action( 'wps_wsp_save_variation_field', $variation_id );

		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * This function is used create variable susbcription field.
	 *
	 * @name wsp_sfw_variation_options_pricing
	 * @param int    $loop loop.
	 * @param array  $variation_data variation_data.
	 * @param object $variation variation.
	 * @since 1.0.0
	 */
	public function wsp_sfw_variation_options_pricing( $loop, $variation_data, $variation ) {
		$variation_id = $variation->ID;
		$wps_sfw_variation_number = wps_wsp_get_meta_data( $variation_id, 'wps_sfw_subscription_number', true );

		if ( empty( $wps_sfw_variation_number ) ) {
			$wps_sfw_variation_number = 1;
		}
		$wps_sfw_variation_subscription_interval = wps_wsp_get_meta_data( $variation_id, 'wps_sfw_subscription_interval', true );

		if ( empty( $wps_sfw_variation_subscription_interval ) ) {
			$wps_sfw_variation_subscription_interval = 'day';
		}

		$wps_sfw_variation_expiry_number = wps_wsp_get_meta_data( $variation_id, 'wps_sfw_subscription_expiry_number', true );
		$wps_sfw_variation_expiry_interval = wps_wsp_get_meta_data( $variation_id, 'wps_sfw_subscription_expiry_interval', true );
		$wps_sfw_variation_initial_fee = wps_wsp_get_meta_data( $variation_id, 'wps_sfw_subscription_initial_signup_price', true );
		$wps_sfw_variation_free_trial = wps_wsp_get_meta_data( $variation_id, 'wps_sfw_subscription_free_trial_number', true );
		$wps_sfw_variation_free_trial_interval = wps_wsp_get_meta_data( $variation_id, 'wps_sfw_subscription_free_trial_interval', true );
		$wps_sfw_variation_subscription_start_date = wps_wsp_get_meta_data( $variation_id, 'wps_sfw_subscription_start_date', true );

		$wps_sfw_subscription_limit_for_trial = wps_wsp_get_meta_data( $variation_id, 'wps_sfw_subscription_limit_for_trial', true );
		$wps_sfw_free_trials_limit = wps_wsp_get_meta_data( $variation_id, 'wps_sfw_free_trials_limit', true );

		?>
		<div class="wps_sfw_product" style="display: none;">
			<p class="form-field form-row form-row-first wps_sfw_variation_subscription_number_field ">
				<label for="wps_sfw_variation_subscription_number<?php echo esc_attr( $loop ); ?>">
				<?php esc_html_e( 'Subscriptions Per Interval', 'woocommerce-subscriptions-pro' ); ?>
				</label>
				<?php
					$description_text = __( 'Choose the subscriptions time interval for the product "for example 10 days"', 'woocommerce-subscriptions-pro' );
					echo wp_kses_post( wc_help_tip( $description_text ) ); // WPCS: XSS ok.
				?>
				<input type="number" class="short wc_input_price wps_sfw_variation_subscription_number"  min="1" required name="wps_sfw_variation_subscription_number[<?php echo esc_attr( $loop ); ?>]" id="wps_sfw_variation_subscription_number<?php echo esc_attr( $loop ); ?>" value="<?php echo esc_attr( $wps_sfw_variation_number ); ?>" placeholder="<?php esc_html_e( 'Enter subscription interval', 'woocommerce-subscriptions-pro' ); ?>" data-attr="<?php echo esc_attr( $loop ); ?>">
				<select id="wps_sfw_variation_subscription_interval<?php echo esc_attr( $loop ); ?>" name="wps_sfw_variation_subscription_interval[<?php echo esc_attr( $loop ); ?>]" class="wps_sfw_variation_subscription_interval" data-attr="<?php echo esc_attr( $loop ); ?>">
					<?php foreach ( wps_sfw_subscription_period() as $value => $label ) { ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $wps_sfw_variation_subscription_interval, true ); ?>><?php echo esc_html( $label ); ?></option>
					<?php } ?>
					</select>

			</p>
			<?php
			if ( wps_wsp_allow_start_date_subscription() ) {

				?>
				
			<p class="form-field form-row form-row-last wps_sfw_variation_subscription_start_date_field">
				<label for="wps_sfw_variation_subscription_start_date<?php echo esc_attr( $loop ); ?>">
				<?php esc_html_e( 'Choose subscription start date', 'woocommerce-subscriptions-pro' ); ?>
				</label>
				<?php
					$description_text = __( 'Choose subscription start date', 'woocommerce-subscriptions-pro' );
					echo wp_kses_post( wc_help_tip( $description_text ) ); // WPCS: XSS ok.
				?>
				<input type="text" class="wps_sfw_subscription_start_date wps_sfw_variation_subscription_start_date" name="wps_sfw_variation_subscription_start_date[<?php echo esc_attr( $loop ); ?>]" id="wps_sfw_variation_subscription_start_date<?php echo esc_attr( $loop ); ?>" value="<?php echo esc_attr( $wps_sfw_variation_subscription_start_date ); ?>" placeholder="<?php esc_html_e( 'Choose Start Date', 'woocommerce-subscriptions-pro' ); ?>" data-attr="<?php echo esc_attr( $loop ); ?>">

			</p>
				<?php
			}
			?>
			<p class="form-field form-row form-row-first wps_sfw_variation_subscription_expiry_field ">
				<label for="wps_sfw_variation_subscription_expiry_number<?php echo esc_attr( $loop ); ?>">
				<?php esc_html_e( 'Subscriptions Expiry Interval', 'woocommerce-subscriptions-pro' ); ?>
				</label>
				<?php
				$description_text = __( 'Choose the subscriptions expiry time interval for the product "leave empty for unlimited"', 'woocommerce-subscriptions-pro' );
				echo wp_kses_post( wc_help_tip( $description_text ) ); // WPCS: XSS ok.
				?>
				<input type="number" class="short wc_input_price wps_sfw_variation_subscription_expiry_number"  min="1" name="wps_sfw_variation_subscription_expiry_number[<?php echo esc_attr( $loop ); ?>]" id="wps_sfw_variation_subscription_expiry_number<?php echo esc_attr( $loop ); ?>" value="<?php echo esc_attr( $wps_sfw_variation_expiry_number ); ?>" placeholder="<?php esc_html_e( 'Enter subscription expiry', 'woocommerce-subscriptions-pro' ); ?>" data-attr="<?php echo esc_attr( $loop ); ?>">
				<select id="wps_sfw_variation_subscription_expiry_interval<?php echo esc_attr( $loop ); ?>" name="wps_sfw_variation_subscription_expiry_interval[<?php echo esc_attr( $loop ); ?>]" class="wps_sfw_variation_subscription_expiry_interval" data-attr="<?php echo esc_attr( $loop ); ?>">
					<?php foreach ( wps_sfw_subscription_expiry_period( $wps_sfw_variation_subscription_interval ) as $value => $label ) { ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $wps_sfw_variation_expiry_interval, true ); ?>><?php echo esc_html( $label ); ?></option>
					<?php } ?>
					</select>
			</p>
			<p class="form-field form-row form-row-last wps_sfw_variation_subscription_initial_signup_field ">
				<label for="wps_sfw_variation_subscription_initial_signup_price<?php echo esc_attr( $loop ); ?>">
				<?php
				esc_html_e( 'Initial Signup fee', 'woocommerce-subscriptions-pro' );
				echo esc_html( '(' . get_woocommerce_currency_symbol() . ')' );
				?>
				</label>
				<?php
					$description_text = __( 'Choose the subscriptions initial fee for the product "leave empty for no initial fee"', 'woocommerce-subscriptions-pro' );
					echo wp_kses_post( wc_help_tip( $description_text ) ); // WPCS: XSS ok.
				?>
				<input type="number" class="short wc_input_price wps_sfw_variation_subscription_initial_signup_price"  min="0" step="any" name="wps_sfw_variation_subscription_initial_signup_price[<?php echo esc_attr( $loop ); ?>]" id="wps_sfw_variation_subscription_initial_signup_price<?php echo esc_attr( $loop ); ?>" value="<?php echo esc_attr( $wps_sfw_variation_initial_fee ); ?>" placeholder="<?php esc_html_e( 'Enter signup fee', 'woocommerce-subscriptions-pro' ); ?>" data-attr="<?php echo esc_attr( $loop ); ?>">

			</p>
			<p class="form-field form-row form-row-first wps_sfw_variation_subscription_free_trial_field ">
				<label for="wps_sfw_variation_subscription_free_trial_number<?php echo esc_attr( $loop ); ?>">
				<?php esc_html_e( 'Free trial interval', 'woocommerce-subscriptions-pro' ); ?>
				</label>
				<?php
				$description_text = __( 'Choose the trial period for subscription "leave empty for no trial period"', 'woocommerce-subscriptions-pro' );
				echo wp_kses_post( wc_help_tip( $description_text ) ); // WPCS: XSS ok.
				?>
				<input type="number" class="short wc_input_number wps_sfw_variation_subscription_free_trial_number"  min="1" name="wps_sfw_variation_subscription_free_trial_number[<?php echo esc_attr( $loop ); ?>]" id="wps_sfw_variation_subscription_free_trial_number<?php echo esc_attr( $loop ); ?>" value="<?php echo esc_attr( $wps_sfw_variation_free_trial ); ?>" placeholder="<?php esc_html_e( 'Enter free trial interval', 'woocommerce-subscriptions-pro' ); ?>" data-attr="<?php echo esc_attr( $loop ); ?>">
				<select id="wps_sfw_variation_subscription_free_trial_interval<?php echo esc_attr( $loop ); ?>" name="wps_sfw_variation_subscription_free_trial_interval[<?php echo esc_attr( $loop ); ?>]" class="wps_sfw_variation_subscription_free_trial_interval" >
					<?php foreach ( wps_sfw_subscription_period() as $value => $label ) { ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $wps_sfw_variation_free_trial_interval, true ); ?>><?php echo esc_html( $label ); ?></option>
					<?php } ?>
					</select>

			</p>
			<!-- limit for variation -->
			<p class="form-field form-row form-row-first wps_sfw_variation_subscription_free_trial__limit_field ">
				<input type="checkbox" class="checkbox wps_sfw_free_trials_limit" name="wps_sfw_free_trials_limit[<?php echo esc_attr( $loop ); ?>]" <?php checked( $wps_sfw_free_trials_limit, 'on' ); ?> />
					<label for="wps_sfw_free_trials_limit<?php echo esc_attr( $loop ); ?>">
					<span><?php esc_html_e( 'Free trial Limit', 'woocommerce-subscriptions-pro' ); ?></span>
				</label>
				<?php
				$description_text = __( 'This Limit Restrict User to puchase multiple subscription"', 'woocommerce-subscriptions-pro' );
				echo wp_kses_post( wc_help_tip( $description_text ) ); // WPCS: XSS ok.
				?>
				<input type="number" class="short wc_input_number wps_sfw_variation_subscription_free_trial_limit"  min="1" name="wps_sfw_variation_subscription_limit_for_trial[<?php echo esc_attr( $loop ); ?>]" id="wps_sfw_variation_subscription_limit_for_trial<?php echo esc_attr( $loop ); ?>" value="<?php echo esc_html( $wps_sfw_subscription_limit_for_trial ); ?>" placeholder="<?php esc_html_e( 'Enter free trial Limit', 'woocommerce-subscriptions-pro' ); ?>" data-attr="<?php echo esc_attr( $loop ); ?>">
			</p>
			<!-- limit for variation -->
			<?php
			if ( wps_wsp_start_susbcription_from_certain_date_of_month() ) {

				$wps_is_enable_certain_date = wps_wsp_get_meta_data( $variation_id, 'wps_wsp_enbale_certain_month', true );
				$wps_wsp_year_number = wps_wsp_get_meta_data( $variation_id, 'wps_wsp_year_number', true );
				if ( empty( $wps_wsp_year_number ) ) {
					$wps_wsp_year_number = 1;
				}
				$wps_wsp_billing_period = $wps_sfw_variation_subscription_interval;
				$wps_wsp_week_sync_value = wps_wsp_get_meta_data( $variation_id, 'wps_wsp_week_sync', true );
				$wps_wsp_month_sync_value = wps_wsp_get_meta_data( $variation_id, 'wps_wsp_month_sync', true );
				$wps_wsp_year_sync_value = wps_wsp_get_meta_data( $variation_id, 'wps_wsp_year_sync', true );

				$wps_show_week = ( 'week' == $wps_wsp_billing_period ) ? '' : 'display: none;';

				$wps_show_month = ( 'month' == $wps_wsp_billing_period ) ? '' : 'display: none;';

				$wps_show_year     = ( 'year' == $wps_wsp_billing_period ) ? '' : 'display: none;';

				$wps_show_week_month_year_check = ( ! in_array( $wps_wsp_billing_period, array( 'month', 'week', 'year' ) ) ) ? 'display: none;' : '';
				?>
				<div class="form-field form-row form-row-full options wps_wsp_certain_date_enable<?php echo esc_attr( $loop ); ?>" style="<?php echo esc_attr( $wps_show_week_month_year_check ); ?>">
					<label class="tips" data-tip="<?php esc_attr_e( 'Enable subscription renewal on certain day/ date', 'woocommerce-subscriptions-pro' ); ?>">
				<?php esc_html_e( 'Enable subscription renewal on certain day/ date', 'woocommerce-subscriptions-pro' ); ?>:
						<input type="checkbox" class="checkbox wps_wsp_variation_enbale_certain_month" name="wps_wsp_variation_enbale_certain_month[<?php echo esc_attr( $loop ); ?>]" <?php checked( $wps_is_enable_certain_date, 'yes' ); ?> data-attr="<?php echo esc_attr( $loop ); ?>" />
					</label>
				</div>
				<div class="wps_wsp_certain_date_enable_wrap<?php echo esc_attr( $loop ); ?>">
					<div class="form-field form-row wps-enable-week wps_wsp_certain_date_enable_week<?php echo esc_attr( $loop ); ?>" style="<?php echo esc_attr( $wps_show_week ); ?>">
				<?php
						woocommerce_wp_select(
							array(
								'id'          => "wps_wsp_variation_week_sync{$loop}",
								'name'        => "wps_wsp_variation_week_sync[{$loop}]",
								'class'       => 'select short',
								'label'       => __( 'Week for Synchronisation', 'woocommerce-subscriptions-pro' ),
								'options'     => wps_wsp_subscription_week_period(),
								'value'       => $wps_wsp_week_sync_value,
							)
						);
				?>
					</div>
					<div class="form-field form-row wps-enable-month wps_wsp_certain_date_enable_month<?php echo esc_attr( $loop ); ?>" style="<?php echo esc_attr( $wps_show_month ); ?>">
					<?php
					woocommerce_wp_select(
						array(
							'id'          => "wps_wsp_variation_month_sync{$loop}",
							'name'        => "wps_wsp_variation_month_sync[{$loop}]",
							'class'       => 'select short',
							'label'       => __( 'Month for Synchronisation', 'woocommerce-subscriptions-pro' ),
							'options'     => wps_wsp_subscription_month_period(),
							'value'       => $wps_wsp_month_sync_value,
						)
					);
					?>
					</div>
					<div class="form-field form-row wps-enable-year wps_wsp_certain_date_enable_year<?php echo esc_attr( $loop ); ?>" style="<?php echo esc_attr( $wps_show_year ); ?>">
						<label class="tips" data-tip="<?php esc_attr_e( 'Year for Synchronisation', 'woocommerce-subscriptions-pro' ); ?>">
							<?php esc_html_e( 'Year for Synchronisation', 'woocommerce-subscriptions-pro' ); ?>
						</label>
						<select id="wps_wsp_variation_year_sync<?php echo esc_attr( $loop ); ?>" name="wps_wsp_variation_year_sync[<?php echo esc_attr( $loop ); ?>]" class="select short wps_wsp_variation_year_sync" >
						<?php foreach ( wps_wsp_subscription_syn_year_period() as $value => $label ) { ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $wps_wsp_year_sync_value, true ); ?>><?php echo esc_html( $label ); ?></option>
						<?php } ?>
						</select>

					<?php
					$wps_current_time = current_time( 'timestamp' );
					$wps_max_no_of_days = gmdate( 't', $wps_current_time );
					?>
					<input type="number" class="short wc_input_number wps_wsp_certain_date_enable_year_num"  min="1" max="<?php echo esc_attr( $wps_max_no_of_days ); ?>" name="wps_wsp_variation_certain_date_enable_year_number[<?php echo esc_attr( $loop ); ?>]" id="wps_wsp_variation_certain_date_enable_year_number<?php echo esc_attr( $loop ); ?>" value="<?php echo esc_attr( $wps_wsp_year_number ); ?>">
					</div>
				</div>
				<?php
			}
			// one time purchase value.
			$wps_sfw_one_time_purchase = wps_wsp_get_meta_data( $variation_id, 'wps_sfw_one_time_purchase', true );
			$wps_sfw_subscription_one_time_price = wps_wsp_get_meta_data( $variation_id, 'wps_wsp_onetime_price', true );
			?>
			<!-- one time purchase for variation -->
			<p class="form-field form-row form-row-full options wps_sfw_one_time_purchase_field">
				<input type="checkbox" class="checkbox wps_sfw_one_time_purchase" name="wps_sfw_one_time_purchase[<?php echo esc_attr( $loop ); ?>]" <?php checked( $wps_sfw_one_time_purchase, 'on' ); ?> />
				<label for="wps_sfw_one_time_purchase[<?php echo esc_attr( $loop ); ?>]">
					<span><?php esc_html_e( 'Enable one-time purchase', 'woocommerce-subscriptions-pro' ); ?></span>
				</label>
				<?php
				$description_text = __( 'Please enter the One Time Purchase amount and make sure you have set the one time purchase amount is greater than subscription price otherwise this will not work', 'woocommerce-subscriptions-pro' );
				echo wp_kses_post( wc_help_tip( $description_text ) ); // WPCS: XSS ok.
				?>
				<input type="number" class="short wc_input_number wps_sfw_subscription_one_time_price"  min="1" data-prod_id="<?php echo esc_html( $variation_id ); ?>" name="wps_sfw_subscription_one_time_price[<?php echo esc_attr( $loop ); ?>]" id="wps_sfw_subscription_one_time_price<?php echo esc_attr( $loop ); ?>" value="<?php echo esc_html( $wps_sfw_subscription_one_time_price ); ?>" placeholder="<?php esc_html_e( 'Enter One Time Purchase Subscription Price', 'woocommerce-subscriptions-pro' ); ?>" data-attr="<?php echo esc_attr( $loop ); ?>">
				<i><?php esc_html_e( 'Make sure you have set the one time purchase amount is greater than subscription price otherwise this will not work', 'woocommerce-subscriptions-pro' ); ?></i>
			</p>
			<!-- one time purchase for variation -->
			<?php
			if ( function_exists( 'learn_press_get_all_courses' ) ) {
				$courses       = learn_press_get_all_courses();
				$saved_courses = get_post_meta( $variation_id, 'wps_learnpress_course', true ) ? get_post_meta( $variation_id, 'wps_learnpress_course', true ) : array();
				?>
				<p class="form-field orm-row-full wps_learnpress_course_field">
					<?php
					if ( ! empty( $courses ) && is_array( $courses ) ) {
						?>
						<label for="wps_learnpress_course">
							<?php esc_html_e( 'Attach LearnPress Courses', 'woocommerce-subscriptions-pro' ); ?>
						</label>
						<select class="wps_learnpress_course custom-multiselect" name="wps_learnpress_course[<?php echo esc_attr( $loop ); ?>][]" data-attr="<?php echo esc_attr( $loop ); ?>" multiple>
						<?php
						foreach ( $courses as $course_id ) {
							$course = learn_press_get_course( $course_id );
							?>
							<option value="<?php echo esc_attr( $course_id ); ?>" <?php selected( true, in_array( $course_id, $saved_courses ) ); ?> ><?php echo esc_attr( $course->get_title() ); ?></option>
							<?php
						}
						?>
						</select>
						<?php
					}
					?>
				</p>
				<?php
			}
			do_action( 'wps_wsp_create_variation_field', $loop, $variation_data, $variation, $variation_id );
			?>
		</div>
		<?php
	}

	/**
	 * This function is used add option in subscription table.
	 *
	 * @name wps_wsp_add_option_details
	 * @param array $actions actions.
	 * @param bool  $subscription_id subscription_id.
	 * @since 1.0.0
	 */
	public function wps_wsp_add_option_details( $actions, $subscription_id ) {
		$wps_wsp_status = wps_wsp_get_meta_data( $subscription_id, 'wps_subscription_status', true );
		if ( 'active' == $wps_wsp_status ) {
			$wps_link = add_query_arg(
				array(
					'wps_subscription_id'               => $subscription_id,
					'wps_subscription_status_admin_pause'     => $wps_wsp_status,
				)
			);

			$wps_link = wp_nonce_url( $wps_link, $subscription_id . $wps_wsp_status );
			$wps_pause_link = array(
				'wps_wsp_pause' => '<a href="' . $wps_link . '">' . __( 'Pause', 'woocommerce-subscriptions-pro' ) . '</a>',

			);
			$actions = array_merge( $wps_pause_link, $actions );
		} elseif ( 'paused' == $wps_wsp_status ) {
			$wps_link = add_query_arg(
				array(
					'wps_subscription_id'               => $subscription_id,
					'wps_subscription_status_admin_reactivate'     => $wps_wsp_status,
				)
			);

			$wps_link = wp_nonce_url( $wps_link, $subscription_id . $wps_wsp_status );
			$wps_reactivate_link = array(
				'wps_wsp_reactivate' => '<a href="' . $wps_link . '">' . __( 'Reactivate', 'woocommerce-subscriptions-pro' ) . '</a>',

			);
			$actions = array_merge( $wps_reactivate_link, $actions );
		}
		if ( ! empty( $wps_wsp_status ) ) {

			$wps_link = add_query_arg(
				array(
					'wps_subscription_id'               => $subscription_id,
					'wps_subscription_view_renewal_order'     => $wps_wsp_status,
				)
			);

			$wps_link = wp_nonce_url( $wps_link, $subscription_id . $wps_wsp_status );
			$wps_view_link = array(
				'wps_wsp_view' => '<a href="' . $wps_link . '">' . __( 'View Renewals', 'woocommerce-subscriptions-pro' ) . '</a>',

			);
			$actions = array_merge( $wps_view_link, $actions );
		}

		if ( 'active' == $wps_wsp_status ) {
			$interval_type = wps_wsp_get_meta_data( $subscription_id, 'wps_sfw_subscription_interval', true );
			$interval_freq = wps_wsp_get_meta_data( $subscription_id, 'wps_sfw_subscription_number', true );
			if ( 'day' === $interval_type && 1 == $interval_freq ) {
				$wps_link = add_query_arg(
					array(
						'wps_subscription_id'                            => $subscription_id,
						'wps_subscription_status_admin_create_recurring' => true,
					)
				);

				$wps_link = wp_nonce_url( $wps_link, $subscription_id . $wps_wsp_status );
				$wps_view_link['wps_initiate_recurring_now'] = '<a href="' . $wps_link . '">' . __( 'Create Renewal', 'woocommerce-subscriptions-pro' ) . '</a>';
				$actions = array_merge( $actions, $wps_view_link );
			}
		}

		return $actions;
	}


	/**
	 * This function allow to create a renewal for the subscription .
	 */
	public function wps_wsp_create_manually_recurring() {
		if ( isset( $_GET['wps_subscription_status_admin_create_recurring'] ) && 1 == $_GET['wps_subscription_status_admin_create_recurring'] && isset( $_GET['wps_subscription_id'] ) && isset( $_GET['_wpnonce'] ) && ! empty( $_GET['_wpnonce'] ) ) {
			$wps_subscription_id = sanitize_text_field( wp_unslash( $_GET['wps_subscription_id'] ) );
			wps_wsp_update_meta_data( $wps_subscription_id, 'wps_next_payment_date', current_time( 'timestamp' ) - 100 );
			if ( class_exists( 'Subscriptions_For_Woocommerce_Scheduler' ) ) {
				$scheduler_object = new Subscriptions_For_Woocommerce_Scheduler();
				if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
					$scheduler_object->wps_sfw_renewal_order_on_scheduler_hpos();
				} else {
					$scheduler_object->wps_sfw_renewal_order_on_scheduler();
				}
				$redirect_url = admin_url() . 'admin.php?page=subscriptions_for_woocommerce_menu&sfw_tab=subscriptions-for-woocommerce-subscriptions-table';
				wp_safe_redirect( $redirect_url );
				exit;
			}
		}
	}

	/**
	 * This function is used pause susbcription.
	 *
	 * @name wps_wsp_admin_pause_susbcription
	 * @since 1.0.0
	 */
	public function wps_wsp_admin_pause_susbcription() {

		if ( isset( $_GET['wps_subscription_status_admin_pause'] ) && isset( $_GET['wps_subscription_id'] ) && isset( $_GET['_wpnonce'] ) && ! empty( $_GET['_wpnonce'] ) ) {
			$wps_status   = sanitize_text_field( wp_unslash( $_GET['wps_subscription_status_admin_pause'] ) );
			$wps_subscription_id = sanitize_text_field( wp_unslash( $_GET['wps_subscription_id'] ) );
			if ( wps_sfw_check_valid_subscription( $wps_subscription_id ) ) {
				 wps_wsp_update_meta_data( $wps_subscription_id, 'wps_subscription_status', 'paused' );
				 wps_wsp_set_pause_subscription_timestamp( $wps_subscription_id );
				 wps_wsp_send_email_for_pause_susbcription( $wps_subscription_id );
				$redirect_url = admin_url() . 'admin.php?page=subscriptions_for_woocommerce_menu&sfw_tab=subscriptions-for-woocommerce-subscriptions-table';
				wp_safe_redirect( $redirect_url );
				exit;
			}
		} elseif ( isset( $_GET['wps_subscription_status_admin_reactivate'] ) && isset( $_GET['wps_subscription_id'] ) && isset( $_GET['_wpnonce'] ) && ! empty( $_GET['_wpnonce'] ) ) {
			$wps_status   = sanitize_text_field( wp_unslash( $_GET['wps_subscription_status_admin_reactivate'] ) );
			$wps_subscription_id = sanitize_text_field( wp_unslash( $_GET['wps_subscription_id'] ) );
			if ( wps_sfw_check_valid_subscription( $wps_subscription_id ) ) {
				wps_wsp_reactivate_time_calculation( $wps_subscription_id );
				wps_wsp_update_meta_data( $wps_subscription_id, 'wps_subscription_status', 'active' );
				wps_wsp_send_email_for_reactivate_susbcription( $wps_subscription_id );
				$redirect_url = admin_url() . 'admin.php?page=subscriptions_for_woocommerce_menu&sfw_tab=subscriptions-for-woocommerce-subscriptions-table';
				wp_safe_redirect( $redirect_url );
				exit;
			}
		}
	}

	/**
	 * This function is used status susbcription.
	 *
	 * @name wps_wsp_status_array
	 * @param array $status status.
	 * @since 1.0.0
	 */
	public function wps_wsp_status_array( $status ) {

		if ( is_array( $status ) && ! empty( $status ) ) {
			$status[] = 'paused';
		}
		return $status;
	}

	/**
	 * Add Export CSV button with popup modal.
	 *
	 * @since 1.0.0
	 */
	public function wps_wsp_export_button_html() {
		?>
		<a href="javascript:void(0);" id="wps_wsp_open_export_popup" class="wps_wsp_export_button button action">
			<?php esc_html_e( 'Export CSV', 'woocommerce-subscriptions-pro' ); ?>
		</a>

		<!-- Popup Modal -->
		<div id="wps_wsp_export_modal" style="display:none;">
			<div class="wps_wsp_modal_content">
				<h2><?php esc_html_e( 'Export Subscriptions CSV', 'woocommerce-subscriptions-pro' ); ?></h2>

				<div class="wps_form_pop_item">
					<label for="wps_wsp_status"><?php esc_html_e( 'Select Status', 'woocommerce-subscriptions-pro' ); ?></label>
					<select id="wps_wsp_status">
						<option value="all"><?php esc_html_e( 'All', 'woocommerce-subscriptions-pro' ); ?></option>
						<option value="active"><?php esc_html_e( 'Active', 'woocommerce-subscriptions-pro' ); ?></option>
						<option value="cancelled"><?php esc_html_e( 'Cancelled', 'woocommerce-subscriptions-pro' ); ?></option>
						<option value="paused"><?php esc_html_e( 'Paused', 'woocommerce-subscriptions-pro' ); ?></option>
						<option value="expired"><?php esc_html_e( 'Expired', 'woocommerce-subscriptions-pro' ); ?></option>
						<option value="on-hold"><?php esc_html_e( 'On-hold', 'woocommerce-subscriptions-pro' ); ?></option>
						<option value="pending"><?php esc_html_e( 'Pending', 'woocommerce-subscriptions-pro' ); ?></option>
					</select>
				</div>

				<div class="wps_form_pop_item">
					<label><?php esc_html_e( 'Start Date', 'woocommerce-subscriptions-pro' ); ?></label>
					<input type="text" id="wps_wsp_start_date" class="wps_datepicker" placeholder="YYYY-MM-DD">
				</div>

				<div class="wps_form_pop_item">
					<label><?php esc_html_e( 'End Date', 'woocommerce-subscriptions-pro' ); ?></label>
					<input type="text" id="wps_wsp_end_date" class="wps_datepicker" placeholder="YYYY-MM-DD">
				</div>

				<div class="wps_form_pop_item wps_form_pop_actions">
					<button type="button" id="wps_wsp_export_csv" class="button button-primary">
						<?php esc_html_e( 'Export', 'woocommerce-subscriptions-pro' ); ?>
					</button>
					<button type="button" id="wps_wsp_close_modal" class="button">
						<?php esc_html_e( 'Cancel', 'woocommerce-subscriptions-pro' ); ?>
					</button>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Export CSV report callback function.
	 *
	 * @since 1.0.0
	 */
	public function wps_wsp_export_csv_report_callback() {

		check_ajax_referer( 'wps_sfw_admin_nonce', 'nonce' );
		$status     = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'all';
		$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
		$end_date   = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';

		$meta_query = array( 'relation' => 'AND' );

		if ( 'all' !== $status ) {
			$meta_query[] = array(
				'key'   => 'wps_subscription_status',
				'value' => $status,
			);
		}

		if ( ! empty( $start_date ) || ! empty( $end_date ) ) {

			// Convert YYYY-MM-DD into timestamps.
			$start_ts = ! empty( $start_date ) ? strtotime( $start_date . ' 00:00:00' ) : 0;
			$end_ts   = ! empty( $end_date ) ? strtotime( $end_date . ' 23:59:59' ) : time();

			$meta_query[] = array(
				'key'     => 'wps_schedule_start',
				'type'    => 'NUMERIC',
				'compare' => 'BETWEEN',
				'value'   => array( $start_ts, $end_ts ),
			);
		}

		if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
			$args = array(
				'limit'      => -1,
				'return'     => 'ids',
				'type'       => 'wps_subscriptions',
				'meta_query' => $meta_query,
			);
			$wps_subscriptions = wc_get_orders( $args );
		} else {
			$args = array(
				'numberposts' => -1,
				'post_type'   => 'wps_subscriptions',
				'post_status' => 'wc-wps_renewal',
				'meta_query'  => $meta_query,
			);
			$wps_subscriptions = get_posts( $args );
		}

		if ( empty( $wps_subscriptions ) ) {
			wp_send_json_error( 'No subscriptions found' );
		}

		$content = array();
		foreach ( $wps_subscriptions as $key => $value ) {
			$subscription_id = OrderUtil::custom_orders_table_usage_is_enabled() ? $value : $value->ID;
			$subscription    = new WPS_Subscription( $subscription_id );
			$parent_order_id = wps_wsp_get_meta_data( $subscription_id, 'wps_parent_order', true );

			if ( function_exists( 'wps_sfw_check_valid_order' ) && ! wps_sfw_check_valid_order( $parent_order_id ) ) {
				continue;
			}

			$wps_subscription_status = wps_wsp_get_meta_data( $subscription_id, 'wps_subscription_status', true );
			$product_name            = wps_wsp_get_meta_data( $subscription_id, 'product_name', true );
			$wps_next_payment_date   = wps_wsp_get_meta_data( $subscription_id, 'wps_next_payment_date', true );
			$wps_susbcription_end    = wps_wsp_get_meta_data( $subscription_id, 'wps_susbcription_end', true );
			$wps_customer_id         = wps_wsp_get_meta_data( $subscription_id, 'wps_customer_id', true );

			$user           = get_user_by( 'id', $wps_customer_id );
			$user_nicename  = isset( $user->user_nicename ) ? $user->user_nicename : '';

			$content[] = apply_filters(
				'wps_wsp_csv_file_data',
				array(
					'subs_id'           => $subscription_id,
					'parent_order_id'   => $parent_order_id,
					'status'            => $wps_subscription_status,
					'product_name'      => $product_name,
					'recurring_amount'  => $subscription->get_total(),
					'username'          => $user_nicename,
					'next_payment_date' => wps_sfw_get_the_csv_date_format( $wps_next_payment_date ),
					'expiry_date'       => wps_sfw_get_the_csv_date_format( $wps_susbcription_end ),
				)
			);
		}

		$title = array(
			'subs_id'           => __( 'Subscription ID', 'woocommerce-subscriptions-pro' ),
			'parent_order_id'   => __( 'Parent Order ID', 'woocommerce-subscriptions-pro' ),
			'status'            => __( 'Status', 'woocommerce-subscriptions-pro' ),
			'product_name'      => __( 'Product Name', 'woocommerce-subscriptions-pro' ),
			'recurring_amount'  => __( 'Recurring Amount', 'woocommerce-subscriptions-pro' ),
			'username'          => __( 'User Name', 'woocommerce-subscriptions-pro' ),
			'next_payment_date' => __( 'Next Payment Date', 'woocommerce-subscriptions-pro' ),
			'expiry_date'       => __( 'Subscription Expiry Date', 'woocommerce-subscriptions-pro' ),
		);

		// Output CSV directly to response.
		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=subscriptions-export.csv' );

		$output = fopen( 'php://output', 'w' );
		fputcsv( $output, $title );
		foreach ( $content as $con ) {
			fputcsv( $output, $con );
		}
		fclose( $output );
		exit;
	}


	/**
	 * This function is used to add renewal sync field.
	 *
	 * @name wps_wsp_product_edit_renewal_on_certain_date
	 * @param int $product_id product_id.
	 * @since 1.0.0
	 */
	public function wps_wsp_product_edit_renewal_on_certain_date( $product_id ) {

		if ( wps_wsp_allow_start_date_subscription() ) {
			$wps_sfw_subscription_start_date = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_start_date', true );
			?>
			<p class="form-field wps_sfw_subscription_start_date_field ">
			<label for="wps_sfw_subscription_start_date">
			<?php esc_html_e( 'Choose Subscription Start Date', 'woocommerce-subscriptions-pro' ); ?>
			</label>
			<input type="text" class="short wc_input_text wps_sfw_subscription_start_date" name="wps_sfw_subscription_start_date" value="<?php echo esc_attr( $wps_sfw_subscription_start_date ); ?>" id="wps_sfw_subscription_start_date"  placeholder="<?php esc_html_e( 'Enter start date', 'woocommerce-subscriptions-pro' ); ?>"> 
			<?php
			$description_text = __( 'Choose subscription start date"', 'woocommerce-subscriptions-pro' );
			echo wp_kses_post( wc_help_tip( $description_text ) ); // WPCS: XSS ok.
			?>
		</p>
			<?php
		}
		if ( ! wps_wsp_start_susbcription_from_certain_date_of_month() ) {
			return;
		}
		$wps_wsp_year_number = wps_wsp_get_meta_data( $product_id, 'wps_wsp_year_number', true );
		if ( empty( $wps_wsp_year_number ) ) {
			$wps_wsp_year_number = 1;
		}
		$wps_wsp_billing_period = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_interval', true );
		$wps_wsp_week_sync_value = wps_wsp_get_meta_data( $product_id, 'wps_wsp_week_sync', true );
		$wps_wsp_month_sync_value = wps_wsp_get_meta_data( $product_id, 'wps_wsp_month_sync', true );
		$wps_wsp_year_sync_value = wps_wsp_get_meta_data( $product_id, 'wps_wsp_year_sync', true );

		if ( empty( $wps_wsp_billing_period ) ) {
			$wps_wsp_billing_period = 'day';
		}

		$wps_show_week = ( 'week' == $wps_wsp_billing_period ) ? '' : 'display: none;';

		$wps_show_month = ( 'month' == $wps_wsp_billing_period ) ? '' : 'display: none;';

		$wps_show_year     = ( 'year' == $wps_wsp_billing_period ) ? '' : 'display: none;';

		$wps_show_week_month_year_check = ( ! in_array( $wps_wsp_billing_period, array( 'month', 'week', 'year' ) ) ) ? 'display: none;' : '';

		?>
		<div class="form-field wps_wsp_certain_date_enable" style="<?php echo esc_attr( $wps_show_week_month_year_check ); ?>">
		<?php
		woocommerce_wp_checkbox(
			array(
				'id' => 'wps_wsp_enbale_certain_month',
				'class' => 'wps_wsp_enbale_certain_month',
				'label' => __( 'Enable subscription renewal on certain day/ date', 'woocommerce-subscriptions-pro' ),
				'name' => 'wps_wsp_enbale_certain_month',
				'custom_attributes' => array( 'wps_billing_period' => $wps_wsp_billing_period ),
			)
		);
		?>
		</div>
		<div class="wps_wsp_certain_date_enable_wrap">
			<div class="form-field wps_wsp_certain_date_enable_week" style="<?php echo esc_attr( $wps_show_week ); ?>">
			<?php
			woocommerce_wp_select(
				array(
					'id'          => 'wps_wsp_week_sync',
					'class'       => 'select short',
					'label'       => __( 'Week for Synchronisation', 'woocommerce-subscriptions-pro' ),
					'options'     => wps_wsp_subscription_week_period(),
					'value'       => $wps_wsp_week_sync_value,
				)
			);
			?>
			</div>
			<div class="form-field wps_wsp_certain_date_enable_month" style="<?php echo esc_attr( $wps_show_month ); ?>">
			<?php
			woocommerce_wp_select(
				array(
					'id'          => 'wps_wsp_month_sync',
					'class'       => 'select short',
					'label'       => __( 'Month for Synchronisation', 'woocommerce-subscriptions-pro' ),
					'options'     => wps_wsp_subscription_month_period(),
					'value'       => $wps_wsp_month_sync_value,
				)
			);
			?>
			</div>
			<div class="form-field wps_wsp_certain_date_enable_year" style="<?php echo esc_attr( $wps_show_year ); ?>">

				<p class="wps_wsp_year_sync_label"><?php esc_html_e( 'Year for Synchronisation', 'woocommerce-subscriptions-pro' ); ?></p>
				<select id="wps_wsp_year_sync" name="wps_wsp_year_sync" class="select short wps_wsp_year_sync" >
					<?php foreach ( wps_wsp_subscription_syn_year_period() as $value => $label ) { ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $wps_wsp_year_sync_value, true ); ?>><?php echo esc_html( $label ); ?></option>
					<?php } ?>
				</select>

			<?php

			$wps_current_time = current_time( 'timestamp' );
			$wps_max_no_of_days = gmdate( 't', $wps_current_time );
			?>
			<input type="number" class="short wc_input_number"  min="1" max="<?php echo esc_attr( $wps_max_no_of_days ); ?>" name="wps_wsp_certain_date_enable_year_number" id="wps_wsp_certain_date_enable_year_number" value="<?php echo esc_attr( $wps_wsp_year_number ); ?>">
			</div>
		</div>
		<?php
	}

	/**
	 * This function is used to save field.
	 *
	 * @name wps_wsp_save_simple_subscription_field
	 * @param int   $product_id product_id.
	 * @param array $post post.
	 * @since 1.0.0
	 */
	public function wps_wsp_save_simple_subscription_field( $product_id, $post ) {

		// one time purchase setting save.
		$wps_sfw_one_time_purchase = isset( $_POST['wps_sfw_one_time_purchase'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_sfw_one_time_purchase'] ) ) : '';
		wps_wsp_update_meta_data( $product_id, 'wps_sfw_one_time_purchase', $wps_sfw_one_time_purchase );

		$wps_wsp_onetime_price = isset( $_POST['wps_sfw_subscription_one_time_price'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_sfw_subscription_one_time_price'] ) ) : '';
		wps_wsp_update_meta_data( $product_id, 'wps_wsp_onetime_price', $wps_wsp_onetime_price );
		// one time purchase setting save.

		// saving free trial limit.

		$wps_wsp_limt_for_free_trial = isset( $_POST['wps_sfw_subscription_limit_for_trial'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_sfw_subscription_limit_for_trial'] ) ) : '';
		wps_wsp_update_meta_data( $product_id, 'wps_wsp_limt_for_free_trial', $wps_wsp_limt_for_free_trial );

		$wps_sfw_free_trails_limit_checkbox = isset( $_POST['wps_sfw_free_trails_limit_checkbox'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_sfw_free_trails_limit_checkbox'] ) ) : '';
		wps_wsp_update_meta_data( $product_id, 'wps_sfw_free_trails_limit_checkbox', $wps_sfw_free_trails_limit_checkbox );

		// saving free trial limit.

		if ( wps_wsp_allow_start_date_subscription() ) {
			$wps_sfw_subscription_start_date = ( ! empty( $_POST['wps_sfw_subscription_start_date'] ) || isset( $_POST['wps_sfw_subscription_start_date'] ) ) ? sanitize_text_field( wp_unslash( $_POST['wps_sfw_subscription_start_date'] ) ) : '';
			wps_wsp_update_meta_data( $product_id, 'wps_sfw_subscription_start_date', $wps_sfw_subscription_start_date );
		}

		if ( ! wps_wsp_start_susbcription_from_certain_date_of_month() ) {
			return;
		}
		if ( ! isset( $_POST['wps_sfw_edit_nonce_filed'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wps_sfw_edit_nonce_filed'] ) ), 'wps_sfw_edit_nonce' ) ) {
			return;
		}

		$wps_wsp_enbale_certain_month = isset( $_POST['wps_wsp_enbale_certain_month'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wsp_enbale_certain_month'] ) ) : 'no';

		$wps_wsp_week_sync = isset( $_POST['wps_wsp_week_sync'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wsp_week_sync'] ) ) : '';

		$wps_wsp_month_sync = isset( $_POST['wps_wsp_month_sync'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wsp_month_sync'] ) ) : '';

		$wps_wsp_year_sync = isset( $_POST['wps_wsp_year_sync'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wsp_year_sync'] ) ) : '';

		$wps_wsp_year_number = isset( $_POST['wps_wsp_certain_date_enable_year_number'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wsp_certain_date_enable_year_number'] ) ) : 1;
		if ( empty( $wps_wsp_year_number ) ) {
			$wps_wsp_year_number = 1;
		}
		wps_wsp_update_meta_data( $product_id, 'wps_wsp_enbale_certain_month', $wps_wsp_enbale_certain_month );
		wps_wsp_update_meta_data( $product_id, 'wps_wsp_week_sync', $wps_wsp_week_sync );
		wps_wsp_update_meta_data( $product_id, 'wps_wsp_month_sync', $wps_wsp_month_sync );
		wps_wsp_update_meta_data( $product_id, 'wps_wsp_year_sync', $wps_wsp_year_sync );
		wps_wsp_update_meta_data( $product_id, 'wps_wsp_year_number', $wps_wsp_year_number );
	}

	/**
	 * Validate License daily.
	 *
	 * @name wps_wsp_validate_license_daily
	 * @since 1.0.0
	 */
	public function wps_wsp_validate_license_daily() {

		$wps_wsp_license_key = get_option( 'wps_wsp_license_key', '' );

		// API query parameters.
		$wps_wsp_api_params = array(
			'slm_action' => 'slm_check',
			'secret_key' => WOOCOMMERCE_SUBSCRIPTIONS_PRO_SPECIAL_SECRET_KEY,
			'license_key' => $wps_wsp_license_key,
			'registered_domain' => isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : home_url(),
			'item_reference' => urlencode( WOOCOMMERCE_SUBSCRIPTIONS_PRO_ITEM_REFERENCE ),
			'product_reference' => WOOCOMMERCE_SUBSCRIPTIONS_PRO_PRODUCT_REFERENCE,
		);

		// Send query to the license manager server.
		$wps_wsp_query = esc_url_raw( add_query_arg( $wps_wsp_api_params, WOOCOMMERCE_SUBSCRIPTIONS_PRO_SERVER_URL ) );

		$wps_wsp_response = wp_remote_get(
			$wps_wsp_query,
			array(
				'timeout' => 50,
				'sslverify' => false,
				'user-agent' => 'WooCommerce Subscriptions PRO/' . $this->version,
			)
		);

		$wps_wsp_license_data = json_decode( wp_remote_retrieve_body( $wps_wsp_response ) );

		if ( isset( $wps_wsp_license_data ) && ! empty( $wps_wsp_license_data ) ) {

			if ( isset( $wps_wsp_license_data->result ) && 'success' === $wps_wsp_license_data->result && isset( $wps_wsp_license_data->status ) && 'active' === $wps_wsp_license_data->status ) {

				update_option( 'wps_wsp_license_key', $wps_wsp_license_key );
				update_option( 'wps_wsp_license_key_status', 'true' );

				// Subscription Code.
				if ( isset( $wps_wsp_license_data->subscr_status ) ) {
					if ( 'on-hold' === $wps_wsp_license_data->subscr_status || 'cancelled' === $wps_wsp_license_data->subscr_status ) {
						update_option( 'wps_wsp_pro_subscription_status', true );
					} else {
						update_option( 'wps_wsp_pro_subscription_status', false );
					}

					$today_date = gmdate( 'Y-m-d', strtotime( 'now' ) );
					$reminder_date = gmdate( 'Y-m-d', strtotime( '-5 days', strtotime( $wps_wsp_license_data->date_renewed ) ) );

					if ( 'pending-cancel' === $wps_wsp_license_data->subscr_status && ( $today_date >= $reminder_date && $today_date < $wps_wsp_license_data->date_renewed ) ) {
						update_option( 'wps_wsp_pro_subscription_renewdate', $wps_wsp_license_data->date_renewed );
					} else {
						delete_option( 'wps_wsp_pro_subscription_renewdate' );
					}
				}
			} else {

				delete_option( 'wps_wsp_license_key' );
				update_option( 'wps_wsp_license_key_status', 'false' );
			}
		}
	}

	/**
	 * License notice.
	 *
	 * @name wps_wsp_license_notice
	 * @since 1.0.0
	 */
	public function wps_wsp_license_notice() {

		$callname_lic = Woocommerce_Subscriptions_Pro::$lic_callback_function;
		$callname_lic_initial = Woocommerce_Subscriptions_Pro::$lic_ini_callback_function;
		$day_count = Woocommerce_Subscriptions_Pro::$callname_lic_initial();
		if ( ! Woocommerce_Subscriptions_Pro::$callname_lic() ) {
			if ( 0 <= $day_count ) {
				$day_count_warning = floor( $day_count );

				/* translators: %s: day */
				$day_string = sprintf( _n( '%s day', '%s days', $day_count_warning, 'woocommerce-subscriptions-pro' ), number_format_i18n( $day_count_warning ) );

				$day_string = '<span id="wps-wsp-license-day-count" >' . esc_html( $day_string ) . '</span>';

				?>
				<div id="wps-wsp-license-thirty-days-notify" class="notice notice-warning">
					<p>
						<strong><a href="?page=subscriptions_for_woocommerce_menu&sfw_tab=woocommerce-subscriptions-pro-license"><?php esc_html_e( 'Activate', 'woocommerce-subscriptions-pro' ); ?></a>
						<?php
						/* translators: %s: day_string */
						printf( esc_html__( ' the license key before %s or you may risk losing data and the plugin will also become dysfunctional.', 'woocommerce-subscriptions-pro' ), wp_kses_post( $day_string ) );
						?>
						</strong>
					</p>
				</div>
				<?php
			} else {
				$wps_license_key = get_option( 'wps_wsp_license_key', '' );
				if ( '' == $wps_license_key ) {
					?>
					<div id="wps-wsp-license-thirty-days-notify-wrap" class="notice notice-warning">
						<p>
							<strong><?php esc_html_e( 'Unfortunately, Your trial has been expired. Please', 'woocommerce-subscriptions-pro' ); ?>
							<a href="?page=subscriptions_for_woocommerce_menu&sfw_tab=woocommerce-subscriptions-pro-license"><?php esc_html_e( 'Activate', 'woocommerce-subscriptions-pro' ); ?></a>
							<?php esc_html_e( 'your license key to avail the premium features.', 'woocommerce-subscriptions-pro' ); ?>
							</strong>
						</p>
					</div>
					<?php
				}
			}
		}
	}

	/**
	 * Add Coupon type settings.
	 *
	 * @name wps_wsp_additional_coupon_setting
	 * @param array $settings settings.
	 * @since 1.1.0
	 */
	public function wps_wsp_additional_coupon_setting( $settings ) {
		$additional_other_settings = array(
			array(
				'title' => __( 'Enable subscription coupon type', 'woocommerce-subscriptions-pro' ),
				'id' => 'wps_wgm_addition_subscription_coupon_option_enable',
				'type' => 'checkbox',
				'class' => 'input-text',
				'desc_tip' => __( 'Enable subscription coupon type', 'woocommerce-subscriptions-pro' ),
				'desc' => __( 'Enable subscription coupon type', 'woocommerce-subscriptions-pro' ),
			),
			array(
				'title' => __( 'Select coupon type', 'woocommerce-subscriptions-pro' ),
				'id' => 'wps_wgm_addition_subscription_coupon_type',
				'type' => 'singleSelectDropDownWithKeyvalue',
				'class' => 'input-text',
				'desc_tip' => __( 'Select coupon type', 'woocommerce-subscriptions-pro' ),
				'custom_attribute' => array(
					array(
						'id' => 'fixed_cart',
						'name' => __( 'Fixed cart discount( Gift card )', 'woocommerce-subscriptions-pro' ),
					),
				),
			),
		);
			return ( array_merge( $settings, $additional_other_settings ) );
	}


	/**
	 * This function is used to custom field compatibility with WPML.
	 *
	 * @name wps_wsp_add_lock_custom_fields_pro.
	 * @since 1.1.0
	 * @param array $ids ids.
	 */
	public function wps_wsp_add_lock_custom_fields_pro( $ids ) {

		$ids[] = 'wps_wsp_enbale_certain_month';
		$ids[] = 'wps_wsp_week_sync';
		$ids[] = 'wps_wsp_month_sync';
		$ids[] = 'wps_wsp_year_sync';
		$ids[] = 'wps_sfw_variation_enable';
		$ids[] = 'wps_sfw_variation_subscription_number';
		$ids[] = 'wps_sfw_variation_subscription_interval';
		$ids[] = 'wps_sfw_variation_subscription_initial_signup_price';
		$ids[] = 'wps_sfw_variation_subscription_free_trial_number';
		$ids[] = 'wps_sfw_variation_subscription_free_trial_interval';
		$ids[] = 'wps_wsp_variation_enbale_certain_month';
		$ids[] = 'wps_wsp_variation_week_sync';
		$ids[] = 'wps_wsp_variation_month_sync';
		$ids[] = 'wps_wsp_variation_year_sync';
		$ids[] = 'wps_wsp_variation_certain_date_enable_year_number';

		return apply_filters( 'wps_wsp_add_lock_fields', $ids );
	}

	/**
	 * This function is used to show notice to activate the wallet plugin.
	 *
	 * @return void
	 */
	public function wps_wsp_wallet_activation_notice() {
		if ( ! in_array( 'wallet-system-for-woocommerce/wallet-system-for-woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			$wps_wps_enable = get_option( 'wsp_enbale_downgrade_upgrade_subscription', '' );
			if ( 'on' == $wps_wps_enable ) {
				?>
				<div class="notice notice-warning">
					<p>
						<strong><?php esc_html_e( 'Please Activate', 'woocommerce-subscriptions-pro' ); ?>
						<a href="https://wordpress.org/plugins/wallet-system-for-woocommerce/"><?php esc_html_e( 'Wallet System For Woocommerce', 'woocommerce-subscriptions-pro' ); ?></a>
						<?php esc_html_e( 'to manage prorate amount while downgrade', 'woocommerce-subscriptions-pro' ); ?>
					</strong>
					</p>
				</div>
				<?php
			}
		}
	}

	/**
	 * Add onetime purchase field in the product in the backend
	 *
	 * @return void
	 */
	public function wps_wsp_custom_product_fields_for_onetime_purchase_subscription() {
		global $post;
		$post_id = $post->ID;
		$product = wc_get_product( $post_id );

		$wps_wsp_onetime_price = wps_wsp_get_meta_data( $post_id, 'wps_wsp_onetime_price', true );
		$wps_sfw_one_time_purchase = wps_wsp_get_meta_data( $post_id, 'wps_sfw_one_time_purchase', true );

		$wps_wsp_limt_for_free_trial = wps_wsp_get_meta_data( $post_id, 'wps_wsp_limt_for_free_trial', true );
		$wps_sfw_free_trails_limit_checkbox = wps_wsp_get_meta_data( $post_id, 'wps_sfw_free_trails_limit_checkbox', true );
		?>
		<div class="wps_sfw_one_time_purchase-wrap">
		<span><?php esc_html_e( 'Enable Free Trials Limit', 'woocommerce-subscriptions-pro' ); ?></span>
		<input type="checkbox" class="checkbox wps_sfw_free_trials_limit" name="wps_sfw_free_trails_limit_checkbox" <?php checked( $wps_sfw_free_trails_limit_checkbox, 'on' ); ?> />
		</div>
		<p class="form-field">
			<label for="wps_wsp_subscription_free_trails">
			<?php
			esc_html_e( 'Limit Value', 'woocommerce-subscriptions-pro' );
			?>
			</label>
			<input type="number" class="short wc_input_limit"  min="1" step="any" data-prod_id="<?php echo esc_html( $post_id ); ?>" name="wps_sfw_subscription_limit_for_trial" id="wps_sfw_subscription_limit_for_trial" value="<?php echo esc_html( $wps_wsp_limt_for_free_trial ); ?>" placeholder="<?php esc_html_e( 'Enter Limit Value', 'woocommerce-subscriptions-pro' ); ?>"> 
		 <?php
			$description_text = __( 'If the Subscription Cancellation Exceed the Set Limit, User Will Not Able To Cancel Their Subscription', 'woocommerce-subscriptions-pro' );
			echo wp_kses_post( wc_help_tip( $description_text ) ); // WPCS: XSS ok.
			?>
		</p>
			
		<div class="wps_sfw_one_time_purchase-wrap">
		<span><?php esc_html_e( 'Enable one-time purchase', 'woocommerce-subscriptions-pro' ); ?></span>
		<input type="checkbox" class="checkbox wps_sfw_one_time_purchase" name="wps_sfw_one_time_purchase" <?php checked( $wps_sfw_one_time_purchase, 'on' ); ?> />
		</div>
		<p class="form-field">
			<label for="wps_wsp_subscription_onetime_purchase_price">
			<?php
			esc_html_e( 'One Time Purchase Price', 'woocommerce-subscriptions-pro' );
			echo esc_html( '(' . get_woocommerce_currency_symbol() . ')' );
			?>
			</label>
			<input type="number" class="short wc_input_price"  min="1" step="any" data-prod_id="<?php echo esc_html( $post_id ); ?>" name="wps_sfw_subscription_one_time_price" id="wps_sfw_subscription_one_time_price" value="<?php echo esc_html( $wps_wsp_onetime_price ); ?>" placeholder="<?php esc_html_e( 'Enter One Time Purchase Price', 'woocommerce-subscriptions-pro' ); ?>"> 
		 <?php
			$description_text = __( 'Please enter the One Time Purchase amount and make sure you have set the one time purchase amount is greater than subscription price otherwise this will not work', 'woocommerce-subscriptions-pro' );
			echo wp_kses_post( wc_help_tip( $description_text ) ); // WPCS: XSS ok.
			?>
		<p><i><?php esc_html_e( 'Make sure you have set the one time purchase amount is greater than subscription price otherwise this will not work', 'woocommerce-subscriptions-pro' ); ?></i></p>
		</p>
		<?php
	}

	/**
	 * Change the plugin title for Pro
	 *
	 * @param string $plugin_title as plugin title.
	 * @return string
	 */
	public function wps_sfw_dashboard_plugin_title_callback( $plugin_title ) {
		return 'Subscriptions For WooCommerce Pro';
	}

	/**
	 * Function To Add Manual Subscription Button.
	 *
	 * @return void
	 */
	public function wps_sfw_add_button_manual_subscription_callback() {
		?>
		<a href="<?php echo esc_url( admin_url( '/post-new.php?post_type=wps_subscriptions' ) ); ?>" class="wps_wsp_add_subscription_button button action" target="_blank"><?php esc_html_e( 'Add Manual Subscription', 'woocommerce-subscriptions-pro' ); ?> </a>
		<?php
	}

	/**
	 * Function to List parebt order for manual subscription.
	 *
	 * @param array $order as order.
	 * @return void
	 */
	public function wps_wsp_add_dropdown_for_manual_subscription_parent_order( $order ) {

		$screen = get_current_screen();

		if ( 'wps_subscriptions' == $screen->id || 'woocommerce_page_wc-orders--wps_subscriptions' == $screen->id ) {
			$order_id = $order->get_id();
			$wps_parent_order = wps_wsp_get_meta_data( $order_id, 'wps_parent_order', true );

			?>
			<p class="form-field form-field-wide">
			<?php
			if ( $wps_parent_order ) {

				?>
				<label for="parent-order-id"><?php echo esc_html__( 'Parent order:', 'woocommerce-subscriptions-pro' ) . esc_html( $wps_parent_order ); ?> </label>
				<?php

			} else {

				?>
				<label for="parent-order-id"><?php esc_html_e( 'Parent order:', 'woocommerce-subscriptions-pro' ); ?> </label>
				<?php
				echo '<select id="wps_wsp_parent_order_selection" name="wps_sfw_parent_order">';
				echo '<option value="">Select an order</option>';
				echo '</select>';
			}
			?>
			</p>
			<?php
		}
	}

	/**
	 * Function To Default order status for manual subscription.
	 *
	 * @param array $statuses as status.
	 * @return array
	 */
	public function wps_wsp_remove_default_status_manual_subscription( $statuses ) {

		if ( ( key_exists( 'post_type', $_GET ) && 'wps_subscriptions' === $_GET['post_type'] ) || ( isset( $_GET['page'] ) && 'wc-orders--wps_subscriptions' === $_GET['page'] && isset( $_GET['action'] ) && 'new' == $_GET['action'] ) ) {

			if ( isset( $statuses['wc-processing'] ) ) {
				unset( $statuses['wc-completed'] );
				unset( $statuses['wc-pending'] );
				unset( $statuses['wc-on-hold'] );
				unset( $statuses['wc-cancelled'] );
				unset( $statuses['wc-refunded'] );
				unset( $statuses['wc-failed'] );
				unset( $statuses['wc-processing'] );
			}
		}
		return $statuses;
	}

	/**
	 * Function to show parent order Manual Subscription order.
	 *
	 * @return void
	 */
	public function wps_wsp_show_parent_order_for_custom_manual_callback() {

		check_ajax_referer( 'wps_sfw_admin_nonce', 'nonce' );
		$user_id = isset( $_POST['user_id'] ) ? sanitize_text_field( wp_unslash( $_POST['user_id'] ) ) : '';
		if ( $user_id ) {

			$customer_orders = wc_get_orders(
				array(
					'customer' => $user_id,
					'status'   => array( 'wc-completed', 'wc-processing' ),
					'limit'    => -1,
				)
			);

			$html = '';
			if ( empty( $customer_orders ) ) {
				$html .= '<option value=""> No Order Exist For Selected Customer </option>';
			} else {

				foreach ( $customer_orders as $order ) {
					$payment_method_title = $order->get_payment_method();
					if ( 'stripe' == $payment_method_title || 'paypal' == $payment_method_title || 'wps_paypal' == $payment_method_title || 'bacs' == $payment_method_title || 'cheque' == $payment_method_title || 'cod' == $payment_method_title || 'wps_wcb_wallet_payment_gateway' == $payment_method_title ) {
						$html .= '<option value=' . $order->get_id() . '>' . $order->get_id() . '</option>';
					}
				}
			}

			echo json_encode( array( 'html' => $html ) );

		}
			wp_die();
	}

	/**
	 * Function to add Meta Box For manual Subscription.
	 *
	 * @param object $post_id as post id.
	 * @param object $post as post.
	 * @return void
	 */
	public function wps_wsp_add_meta_boxes( $post_id, $post ) {
		$screen = get_current_screen();

		if ( 'wps_subscriptions' == $screen->id || 'woocommerce_page_wc-orders--wps_subscriptions' == $screen->id ) {
			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
				$order_id = $post->get_id();
				$wps_parent_order = wps_wsp_get_meta_data( $order_id, 'wps_parent_order', true );
				if ( empty( $wps_parent_order ) ) {
					add_meta_box(
						'wps-wsp-subscriptios-meta-box',
						_x( 'Subscriptions Schedule Data', 'meta box title', 'woocommerce-subscriptions-pro' ),
						array( $this, 'wps_subscriptions_order_meta_box_data' ),
						$post_id,
						'side',
						'high'
					);
				}
			} else {
				$order_id = $post->ID;
				$wps_parent_order = wps_wsp_get_meta_data( $post_id, 'wps_parent_order', true );
				if ( empty( $wps_parent_order ) ) {
					add_meta_box(
						'wps-wsp-subscriptios-meta-box',
						_x( 'Subscriptions Schedule Data', 'meta box title', 'woocommerce-subscriptions-pro' ),
						array( $this, 'wps_subscriptions_order_meta_box_data' ),
						'wps_subscriptions',
						'side',
						'high'
					);
				}
			}
		}
	}

	/**
	 * Function To show Html in Metabox for Manual subscription.
	 *
	 * @return void
	 */
	public function wps_subscriptions_order_meta_box_data() {

		$wps_sfw_subscription_interval = 'day';
		$wps_sfw_subscription_expiry_interval = 'day';
		?>
		<div class="wc-metaboxes-wrapper">

			<div id="wps_wsp_billing_schedule">
				<div>
				<label for ="wps_sfw_subscription_number">
					<?php esc_html_e( 'Subscriptions Per Interval', 'woocommerce-subscriptions-pro' ); ?>
				</label>
				</div>
				<div class="wps_wsp_manual_inner_wrap">

					<input type="number" class="short wc_input_number"  min="1" required name="wps_sfw_subscription_number" id="wps_sfw_subscription_number" value="<?php echo esc_attr( $wps_sfw_subscription_number ); ?>" placeholder="<?php esc_html_e( 'Enter subscription interval', 'woocommerce-subscriptions-pro' ); ?>">
					<select id="wps_sfw_subscription_interval" name="wps_sfw_subscription_interval" class="wps_sfw_subscription_interval" >
						<?php foreach ( wps_sfw_subscription_period() as $value => $label ) { ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $wps_sfw_subscription_interval, true ); ?>><?php echo esc_html( $label ); ?></option>
						<?php } ?>
					</select>
				</div>

				<div>
				<label for= "wps_sfw_subscription_expiry_number">
					<?php esc_html_e( 'Subscriptions Expiry Interval', 'woocommerce-subscriptions-pro' ); ?>
				</label>
				</div>
				<div class="wps_wsp_manual_inner_wrap">
				<input type="number" class="short wc_input_number"  min="1" name="wps_sfw_subscription_expiry_number" id="wps_sfw_subscription_expiry_number" value="<?php echo esc_attr( $wps_sfw_subscription_expiry_number ); ?>" placeholder="<?php esc_html_e( 'Enter subscription expiry', 'woocommerce-subscriptions-pro' ); ?>"> 
				<select id="wps_sfw_subscription_expiry_interval" name="wps_sfw_subscription_expiry_interval" class="wps_sfw_subscription_expiry_interval" >
				<?php foreach ( wps_sfw_subscription_period() as $value => $label ) { ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $wps_sfw_subscription_expiry_interval, true ); ?>><?php echo esc_html( $label ); ?></option>
				<?php } ?>
				</select>
				</div>			
			</div>
		</div>
		<?php
	}

	/**
	 * Function To save and create manual subscription.
	 *
	 * @param object $post_id as post id.
	 * @param object $post as post.
	 * @return void
	 */
	public function wps_wsp_save_manual_subscription_order_details( $post_id, $post ) {

		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( empty( $screen ) || ! isset( $_POST['woocommerce_meta_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' ) ) {
			return;
		}
		if ( 'wps_subscriptions' !== $screen->id ) {
			return;
		}

		$wps_parent_order = wps_wsp_get_meta_data( $post_id, 'wps_parent_order', true );

		if ( ! empty( $wps_parent_order ) ) {
			return; // Already created.
		}

		// Sanitize all inputs.
		$fields = array(
			'wps_sfw_parent_order',
			'wps_sfw_subscription_number',
			'wps_sfw_subscription_interval',
			'wps_sfw_subscription_expiry_number',
			'wps_sfw_subscription_expiry_interval',
			'customer_user',
		);
		$data = array();

		foreach ( $fields as $field ) {
			$data[ $field ] = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';
		}
		$parent_order = wc_get_order( $data['wps_sfw_parent_order'] );

		wps_wsp_update_meta_data( $post_id, 'wps_subscription_status', 'active' );
		wps_wsp_update_meta_data( $post_id, 'wps_customer_id', $data['customer_user'] );

		// Set Meta.
		$current_time = apply_filters( 'wps_sfw_subs_curent_time', current_time( 'timestamp' ), $post_id );
		$expiry_number = (int) $data['wps_sfw_subscription_expiry_number'];
		$expiry_interval = $data['wps_sfw_subscription_expiry_interval'];

		$subscription_end = 0;
		if ( $expiry_interval && $expiry_number ) {
			$subscription_end = wps_sfw_susbcription_calculate_time( $current_time, $expiry_number, $expiry_interval );
		}

		$meta_updates = array(
			'wps_susbcription_trial_end'             => 0,
			'wps_parent_order'                        => $data['wps_sfw_parent_order'] ? $data['wps_sfw_parent_order'] : 'manual',
			'_order_key'                              => wc_generate_order_key(),
			'wps_sfw_order_has_subscription'          => 'yes',
			'wps_sfw_subscription_number'             => $data['wps_sfw_subscription_number'],
			'wps_sfw_subscription_interval'           => $data['wps_sfw_subscription_interval'],
			'wps_sfw_subscription_expiry_number'      => $expiry_number,
			'wps_sfw_subscription_expiry_interval'    => $expiry_interval,
			'wps_susbcription_end'                    => $subscription_end,
			'wps_schedule_start'                      => $current_time,
			'wps_wsp_payment_type'                    => 'wps_wsp_manual_method',
		);

		foreach ( $meta_updates as $key => $value ) {
			wps_wsp_update_meta_data( $post_id, $key, $value );
		}

		$next_payment = function_exists( 'wps_sfw_next_payment_date' ) ? wps_sfw_next_payment_date( $post_id, $current_time, 0 ) : null;
		wps_wsp_update_meta_data( $post_id, 'wps_next_payment_date', $next_payment );

		$order = OrderUtil::custom_orders_table_usage_is_enabled() ? new WPS_Subscription( $post_id ) : wc_get_order( $post_id );

		if ( ! $order ) {
			return;
		}
		$items = $order->get_items();

		foreach ( $items as $item_id => $item_data ) {
			$product_id = $item_data->get_product_id();
			$product_name = get_the_title( $product_id );
			$product_qty = $item_data->get_quantity();
			// Get the product name.
			wps_wsp_update_meta_data( $post_id, 'product_id', $product_id );
			wps_wsp_update_meta_data( $post_id, 'product_name', $product_name );
			wps_wsp_update_meta_data( $post_id, 'product_qty', $product_qty );
			break;
		}

		$line_total = 0;
		$line_subtotal = 0;
		$line_subtotal_tax = 0;
		$line_tax = 0;

		foreach ( $items as $item_id => $item_data ) {
			$line_subtotal += $item_data->get_subtotal();
			$line_total += $item_data->get_total();

			$tax_data = $item_data->get_taxes();
			if ( ! empty( $tax_data ) ) {
				foreach ( $tax_data as $tax_id => $tax ) {

					$line_subtotal_tax += $tax[1];
					$line_tax += $tax[1];
				}
			}
		}

		wps_wsp_update_meta_data( $post_id, 'line_subtotal', $line_subtotal );
		wps_wsp_update_meta_data( $post_id, 'line_total', $line_total );
		wps_wsp_update_meta_data( $post_id, 'line_subtotal_tax', $line_subtotal_tax );
		wps_wsp_update_meta_data( $post_id, 'line_tax', $line_tax );

		if ( $parent_order ) {
			// Inherit payment method.
			$order->set_payment_method( $parent_order->get_payment_method() );
			$order->set_payment_method_title( $parent_order->get_payment_method_title() );
			$order->set_currency( $parent_order->get_currency() );
			$order->save();
			$parent_order->set_meta_data( 'wps_subscription_id', $post_id );
			$parent_order->save();
		}
		if ( isset( $data['wps_sfw_parent_order'] ) && $data['wps_sfw_parent_order'] ) {
			wps_wsp_update_meta_data( $data['wps_sfw_parent_order'], 'wps_sfw_subscription_activated', 'yes' );
		}

		$mailer = WC()->mailer()->get_emails();
		// Send the "reactivate" notification.
		if ( isset( $mailer['wps_wsp_new_subscription'] ) ) {
			$mailer['wps_wsp_new_subscription']->trigger( $post_id );
		}

		do_action( 'wps_sfw_after_created_subscription', $post_id, $data['wps_sfw_parent_order'] );

		wp_safe_redirect( admin_url( 'admin.php?page=subscriptions_for_woocommerce_menu&sfw_tab=subscriptions-for-woocommerce-subscriptions-table' ) );
		exit;
	}


	/**
	 *  Function To save and create manual subscription.
	 *
	 * @param [type] $order_id is the subscription order id.
	 * @return void
	 */
	public function wps_wsp_save_manual_subscription_order_details_hpos( $order_id ) {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( empty( $screen ) || ! isset( $_POST['woocommerce_meta_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' ) ) {
			return;
		}
		if ( ! OrderUtil::custom_orders_table_usage_is_enabled() ) {
			return;
		}
		if ( 'woocommerce_page_wc-orders--wps_subscriptions' !== $screen->id ) {
			return;
		}

		$post_id = $order_id;
		$wps_parent_order = wps_wsp_get_meta_data( $post_id, 'wps_parent_order', true );

		if ( ! empty( $wps_parent_order ) ) {
			return; // Already created.
		}

		// Sanitize all inputs.
		$fields = array(
			'wps_sfw_parent_order',
			'wps_sfw_subscription_number',
			'wps_sfw_subscription_interval',
			'wps_sfw_subscription_expiry_number',
			'wps_sfw_subscription_expiry_interval',
			'customer_user',
		);
		$data = array();

		foreach ( $fields as $field ) {
			$data[ $field ] = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';
		}
		$parent_order = wc_get_order( $data['wps_sfw_parent_order'] );

		wps_wsp_update_meta_data( $post_id, 'wps_subscription_status', 'active' );
		wps_wsp_update_meta_data( $post_id, 'wps_customer_id', $data['customer_user'] );

		// Set Meta.
		$current_time = apply_filters( 'wps_sfw_subs_curent_time', current_time( 'timestamp' ), $post_id );
		$expiry_number = (int) $data['wps_sfw_subscription_expiry_number'];
		$expiry_interval = $data['wps_sfw_subscription_expiry_interval'];

		$subscription_end = 0;
		if ( $expiry_interval && $expiry_number ) {
			$subscription_end = wps_sfw_susbcription_calculate_time( $current_time, $expiry_number, $expiry_interval );
		}

		$meta_updates = array(
			'wps_susbcription_trial_end'             => 0,
			'wps_parent_order'                        => $data['wps_sfw_parent_order'] ? $data['wps_sfw_parent_order'] : 'manual',
			'_order_key'                              => wc_generate_order_key(),
			'wps_sfw_order_has_subscription'          => 'yes',
			'wps_sfw_subscription_number'             => $data['wps_sfw_subscription_number'],
			'wps_sfw_subscription_interval'           => $data['wps_sfw_subscription_interval'],
			'wps_sfw_subscription_expiry_number'      => $expiry_number,
			'wps_sfw_subscription_expiry_interval'    => $expiry_interval,
			'wps_susbcription_end'                    => $subscription_end,
			'wps_schedule_start'                      => $current_time,
			'wps_wsp_payment_type'                    => 'wps_wsp_manual_method',
		);

		foreach ( $meta_updates as $key => $value ) {
			wps_wsp_update_meta_data( $post_id, $key, $value );
		}

		$next_payment = function_exists( 'wps_sfw_next_payment_date' ) ? wps_sfw_next_payment_date( $post_id, $current_time, 0 ) : null;
		wps_wsp_update_meta_data( $post_id, 'wps_next_payment_date', $next_payment );

		$order = OrderUtil::custom_orders_table_usage_is_enabled() ? new WPS_Subscription( $post_id ) : wc_get_order( $post_id );

		if ( ! $order ) {
			return;
		}
		$items = $order->get_items();

		foreach ( $items as $item_id => $item_data ) {
			$product_id = $item_data->get_product_id();
			$product_name = get_the_title( $product_id );
			$product_qty = $item_data->get_quantity();
			// Get the product name.
			wps_wsp_update_meta_data( $post_id, 'product_id', $product_id );
			wps_wsp_update_meta_data( $post_id, 'product_name', $product_name );
			wps_wsp_update_meta_data( $post_id, 'product_qty', $product_qty );
			break;
		}

		$line_total = 0;
		$line_subtotal = 0;
		$line_subtotal_tax = 0;
		$line_tax = 0;

		foreach ( $items as $item_id => $item_data ) {
			$line_subtotal += $item_data->get_subtotal();
			$line_total += $item_data->get_total();

			$tax_data = $item_data->get_taxes();
			if ( ! empty( $tax_data ) ) {
				foreach ( $tax_data as $tax_id => $tax ) {

					$line_subtotal_tax += $tax[1];
					$line_tax += $tax[1];
				}
			}
		}

		wps_wsp_update_meta_data( $post_id, 'line_subtotal', $line_subtotal );
		wps_wsp_update_meta_data( $post_id, 'line_total', $line_total );
		wps_wsp_update_meta_data( $post_id, 'line_subtotal_tax', $line_subtotal_tax );
		wps_wsp_update_meta_data( $post_id, 'line_tax', $line_tax );

		if ( $parent_order ) {
			// Inherit payment method.
			$order->set_payment_method( $parent_order->get_payment_method() );
			$order->set_payment_method_title( $parent_order->get_payment_method_title() );
			$order->set_currency( $parent_order->get_currency() );
			$order->save();
			$parent_order->set_meta_data( 'wps_subscription_id', $post_id );
			$parent_order->save();
		}
		if ( isset( $data['wps_sfw_parent_order'] ) && $data['wps_sfw_parent_order'] ) {
			wps_wsp_update_meta_data( $data['wps_sfw_parent_order'], 'wps_sfw_subscription_activated', 'yes' );
		}

		$mailer = WC()->mailer()->get_emails();
		// Send the "reactivate" notification.
		if ( isset( $mailer['wps_wsp_new_subscription'] ) ) {
			$mailer['wps_wsp_new_subscription']->trigger( $post_id );
		}
		do_action( 'wps_sfw_after_created_subscription', $post_id, $data['wps_sfw_parent_order'] );

		wp_safe_redirect( admin_url( 'admin.php?page=subscriptions_for_woocommerce_menu&sfw_tab=subscriptions-for-woocommerce-subscriptions-table' ) );
		exit;
	}

	/**
	 * Check the license stauts
	 *
	 * @since 2.2.9
	 */
	public function wps_wsp_check_license_key_status() {
		check_ajax_referer( 'check-nonce', 'nonce' );

		$this->wps_wsp_validate_license_daily();

		$wpss_subscr_status = get_option( 'wps_wsp_pro_subscription_status', false );
		$wps_subscr_renewdate = get_option( 'wps_wsp_pro_subscription_renewdate', false );

		if ( isset( $wpss_subscr_status ) && true == $wpss_subscr_status ) {

			echo json_encode(
				array(
					'status' => false,
					'msg' => __(
						'Subscription is not renewed yet.',
						'woocommerce-subscriptions-pro'
					),
				)
			);
		} else if ( ! empty( $wps_subscr_renewdate ) && '0000-00-00' != $wps_subscr_renewdate ) {

			echo json_encode(
				array(
					'status' => false,
					'msg' => __(
						'Subscription is not renewed yet.',
						'woocommerce-subscriptions-pro'
					),
				)
			);
		} else {
			echo json_encode(
				array(
					'status' => true,
					'msg' => __(
						'License Status Updated. Please Wait.',
						'woocommerce-subscriptions-pro'
					),
				)
			);
		}

		wp_die();
	}

	/**
	 * Function to show notification for subscription status.
	 *
	 * @return void
	 */
	public function wps_wsp_subscription_notification_html() {
		$screen = get_current_screen();
		if ( isset( $screen->id ) ) {
			$pagescreen = $screen->id;
		}
		if ( ( isset( $pagescreen ) && 'plugins' === $pagescreen ) || ( 'wp-swings_page_home' == $pagescreen ) || ( 'wp-swings_page_subscriptions_for_woocommerce_menu' == $pagescreen ) ) {
			$wps_subscr_status = get_option( 'wps_wsp_pro_subscription_status', false );

			if ( isset( $wps_subscr_status ) && true == $wps_subscr_status ) {
				?>
					<div class="wps-subsc_notice notice notice-warning is-dismissible">
						<p><?php esc_html_e( 'Stay on track! Renew your subscription now to maintain uninterrupted service!', 'woocommerce-subscriptions-pro' ); ?> <a href="https://wpswings.com/my-account/subscriptions/" target="_blank"><?php esc_html_e( 'Renew Now', 'woocommerce-subscriptions-pro' ); ?></a>. <?php esc_html_e( 'If already renewed', 'woocommerce-subscriptions-pro' ); ?>, <a href="#" id="wps_check_license"><?php esc_html_e( 'Validate Now', 'woocommerce-subscriptions-pro' ); ?></a>.</p>
					</div>
					
					<?php
			}

			$wps_subscr_renewdate = get_option( 'wps_wsp_pro_subscription_renewdate', false );
			$today_date = gmdate( 'Y-m-d', strtotime( 'now' ) );
			$reminder_date = gmdate( 'Y-m-d', strtotime( '-5 days', strtotime( $wps_subscr_renewdate ) ) );

			$date1 = new DateTime( $wps_subscr_renewdate );
			$date2 = new DateTime( $today_date );
			$interval = $date1->diff( $date2 );

			if ( isset( $wps_subscr_renewdate ) && ( $today_date >= $reminder_date && $today_date < $wps_subscr_renewdate ) ) {

				?>
					<div class="wps-subsc_notice notice notice-warning is-dismissible">
						<p><strong>
						<?php
						esc_html_e( 'Your Subscription Will Expire in ', 'woocommerce-subscriptions-pro' );
						echo esc_attr( $interval->days );
						esc_html_e( ' days', 'woocommerce-subscriptions-pro' );
						?>
						.</strong><br>
						<?php esc_html_e( 'Stay on track! Renew your subscription now to maintain uninterrupted service!', 'woocommerce-subscriptions-pro' ); ?> <a href="https://wpswings.com/my-account/subscriptions/" target="_blank"><?php esc_html_e( 'Renew Now', 'woocommerce-subscriptions-pro' ); ?></a>. <?php esc_html_e( 'If already renewed', 'woocommerce-subscriptions-pro' ); ?>, <a href="#" id="wps_check_license"><?php esc_html_e( 'Validate Now', 'woocommerce-subscriptions-pro' ); ?></a>.</p>
					</div>
					
				<?php
			}
		}
	}

	/**
	 * Validate the Paypal Keys
	 */
	public function wps_paypal_subscription_integration_keys_validation_callback() {

		check_ajax_referer( 'wps_paypal_subscription_admin_nonce', 'nonce' );

		$test_mode     = isset( $_POST['testMode'] ) ? sanitize_text_field( wp_unslash( $_POST['testMode'] ) ) : '';
		$client_id     = isset( $_POST['clientID'] ) ? sanitize_text_field( wp_unslash( $_POST['clientID'] ) ) : '';
		$client_secret = isset( $_POST['clientSecret'] ) ? sanitize_text_field( wp_unslash( $_POST['clientSecret'] ) ) : '';

		$endpoint = ( 'true' === $test_mode ) ? WPS_PAYPAL_SUBSCRIPTION_INTEGRATION_SANDBOX_URL : WPS_PAYPAL_SUBSCRIPTION_INTEGRATION_LIVE_URL;

		$response = wp_remote_post(
			$endpoint . '/v1/oauth2/token',
			array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array(
					'Accept' => 'application/json',
					'Accept-Language' => 'en_US',
					'Authorization'   => 'Basic ' . base64_encode( $client_id . ':' . $client_secret ),
				),
				'body' => array(
					'grant_type' => 'client_credentials',
				),
			)
		);

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( 200 == $response_code ) {
			$response = array(
				'msg' => esc_html__( 'Verification Successful', 'woocommerce-subscriptions-pro' ),
				'status' => 'success',
				'code' => 200,
			);
		} else {
			$response = array(
				'msg' => $response_data->error_description,
				'status' => 'error',
				'code' => $response_code,
			);
		}
		echo json_encode( $response );
		wp_die();
	}
	/**
	 * Remove the actions
	 *
	 * @param array() $actions .
	 * @param integer $wps_subscription_id .
	 */
	public function wps_sfw_add_action_details_callack( $actions, $wps_subscription_id ) {
		if ( OrderUtil::custom_orders_table_usage_is_enabled() && class_exists( 'WPS_Subscription' ) ) {
			$subscription       = new WPS_Subscription( $wps_subscription_id );
			$wps_payment_method = $subscription->get_payment_method();
		} else {
			$wps_payment_method = wps_wsp_get_meta_data( $wps_subscription_id, '_payment_method', true );
		}
		if ( 'wps_paypal_subscription' === $wps_payment_method ) {
			unset( $actions['wps_wsp_pause'] );
			unset( $actions['wps_initiate_recurring_now'] );
			unset( $actions['wps_wsp_reactivate'] );
		}
		return $actions;
	}

	/**
	 * Add custom order actions to the order actions dropdown.
	 *
	 * @param mixed $actions .
	 * @return mixed
	 */
	public function wps_sfw_add_renewal_payment_actions( $actions ) {
		global $theorder;
		if ( is_object( $theorder ) && 'yes' == $theorder->get_meta( 'wps_sfw_renewal_order' ) && $theorder->has_status( array( 'pending', 'wps_renewal' ) ) ) {
			$actions['wps_retry_renewal'] = __( 'Retry Renewal Payment', 'woocommerce-subscriptions-pro' );
		}
		return $actions;
	}

	/**
	 * Handle the custom order action.
	 *
	 * @param mixed $order .
	 * @return void
	 */
	public function wps_sfw_handle_renewal_payment( $order ) {
		$order_id = $order->get_id();

		$subcription_id = $order->get_meta( 'wps_sfw_subscription' );

		// Initiate the renewal payment.
		do_action( 'wps_sfw_other_payment_gateway_renewal', $order, $subcription_id, $order->get_payment_method() );
	}

	/**
	 * New Column to show update subscrition.
	 *
	 * @param array $columns as columns.
	 * @return array
	 */
	public function wps_wsp_add_update_subscription_column( $columns ) {
		// Add a new column named 'Update Subscription'.
		$columns['update_subscription'] = __( 'Update Subscription', 'woocommerce-subscriptions-pro' );
		return $columns;
	}

	/**
	 * Update subscription coloumn values function
	 *
	 * @param array $output as output.
	 * @param array $column_name as column name.
	 * @param array $item as item.
	 * @return array
	 */
	public function wps_wsp_add_update_button_to_subscription_column( $output, $column_name, $item ) {
		if ( 'update_subscription' === $column_name ) {
			$output = '<button class="button update-subscription" data-subscription_id="' . esc_attr( $item['subscription_id'] ) . '">' . __( 'Update', 'woocommerce-subscriptions-pro' ) . '</button>';
			?>
			<div id="update-subscription-popup" class="wps-wsp_popup-overlay" style="display: none;">

				<div class="popup-content">
					<span class="close-popup">&times;</span>
					<h4><?php esc_html_e( 'Update Subscription', 'woocommerce-subscriptions-pro' ); ?></h4>
					<form id="update-subscription-form">
						<input type="hidden" id="subscription-id" name="subscription_id">
						
						<div class="wps_wsp_sub_popup_wrapper">
						<label for="next-payment-date"><?php esc_html_e( 'Next Payment Date:', 'woocommerce-subscriptions-pro' ); ?></label>
						<input type="date" id="next-payment-date" name="next_payment_date" placeholder="Select date">
						</div>

						<div class="wps_wsp_sub_popup_wrapper">
						<label for="subscription-price"><?php esc_html_e( 'Subscription Item Total:', 'woocommerce-subscriptions-pro' ); ?></label>
						<input type="number" id="subscription-price" name="subscription_price" placeholder="Enter new price" step="0.01" min="0">
						<span><?php esc_html_e( 'final price will based on all taxes calculation and shipping charges if applicable previously on subscription', 'woocommerce-subscriptions-pro' ); ?></span>
						</div>
						
						<button type="button" id="update-subscription-btn"><?php esc_html_e( 'Update Subscription', 'woocommerce-subscriptions-pro' ); ?></button>
					</form>
				</div>
			</div>

			<?php
		}

		return $output;
	}

	/**
	 * Ajax Function to update item of existing subscription.
	 *
	 * @return void
	 */
	public function wps_wsp_update_subscription_items_callback() {

		check_ajax_referer( 'wps_sfw_admin_nonce', 'nonce' );
		$subscription_id = isset( $_POST['subscription_id'] ) ? sanitize_text_field( wp_unslash( $_POST['subscription_id'] ) ) : 0;
		$next_payment_date = isset( $_POST['next_payment_date'] ) ? sanitize_text_field( wp_unslash( $_POST['next_payment_date'] ) ) : 0;
		$subscription_price = isset( $_POST['subscription_price'] ) ? sanitize_text_field( wp_unslash( $_POST['subscription_price'] ) ) : 0;
		$subscription_price = floatval( $subscription_price );
		$update = false;
		if ( $next_payment_date ) {
			$next_payment_timestamp = strtotime( $next_payment_date );
			if ( false == $next_payment_timestamp ) {
				wp_send_json_error( array( 'message' => 'Invalid date format.' ) );
			} else {
				wps_wsp_update_meta_data( $subscription_id, 'wps_next_payment_date', $next_payment_timestamp );
				$update = true;
			}
		}
		if ( $subscription_price ) {
			$subscription = wc_get_order( $subscription_id );
			if ( $subscription ) {
				// Loop through the subscription line items.
				foreach ( $subscription->get_items() as $item_id => $item ) {
					// Set the item prices.
					$quantity = $item->get_quantity();
					$line_subtotal = $subscription_price * $quantity;
					$line_total = $subscription_price * $quantity;

					$tax_class = $item->get_tax_class(); // Can be '', 'reduced-rate', etc.
					$tax_rates = WC_Tax::get_rates( $tax_class );
					$include_tax = get_option( 'woocommerce_prices_include_tax' );
					if ( function_exists( 'wps_sfw_is_woocommerce_tax_enabled' ) && wps_sfw_is_woocommerce_tax_enabled() ) {
						if ( 'yes' === $include_tax ) {
							$line_subtotal_tax = WC_Tax::get_tax_total( WC_Tax::calc_inclusive_tax( $line_subtotal, $tax_rates ) );
							$line_tax     = WC_Tax::get_tax_total( WC_Tax::calc_inclusive_tax( $line_total, $tax_rates ) );

							// Adjust totals for inclusive tax.
							$line_total -= $line_tax;
							$line_subtotal -= $line_subtotal_tax;
						} else {
							$line_subtotal_tax = WC_Tax::get_tax_total( WC_Tax::calc_exclusive_tax( $line_subtotal, $tax_rates ) );
							$line_tax     = WC_Tax::get_tax_total( WC_Tax::calc_exclusive_tax( $line_total, $tax_rates ) );
						}
					}

					// Update item totals.
					$item->set_subtotal( $line_subtotal );
					$item->set_total( $line_total );
					$item->set_subtotal_tax( $line_subtotal_tax );
					$item->set_total_tax( $line_total_tax );
					// Save updated meta data.
					wps_sfw_update_meta_data( $subscription_id, 'line_subtotal', $line_subtotal );
					wps_sfw_update_meta_data( $subscription_id, 'line_total', $line_total );
					wps_sfw_update_meta_data( $subscription_id, 'line_subtotal_tax', $line_subtotal_tax );
					wps_sfw_update_meta_data( $subscription_id, 'line_tax', $line_total_tax );

					// Save the updated line item.
					$item->save();

					// Recalculate and save the subscription totals.
					$subscription->calculate_totals();
					$subscription->save();

					// Optionally, add a note to the subscription about the price update.
					$subscription->add_order_note( esc_attr__( 'Subscrition price updated by admin', 'woocommerce-subscriptions-pro' ) );
					$update = true;
				}
			}
		}
		if ( empty( $next_payment_date ) && empty( $subscription_price ) ) {
			wp_send_json_success(
				array(
					'sucess' => $update,
					'message' => esc_attr__( 'Kindly Enter either next payment date or subscription item Price or Both !', 'woocommerce-subscriptions-pro' ),
				)
			);
		}
		if ( $update ) {
			wp_send_json_success(
				array(
					'sucess' => $update,
					'message' => esc_attr__( 'Subscription updated!', 'woocommerce-subscriptions-pro' ),
				)
			);
		} else {
			wp_send_json_success(
				array(
					'sucess' => $update,
					'message' => esc_attr__( 'No Changes', 'woocommerce-subscriptions-pro' ),
				)
			);
		}

		// Return a success response.
		wp_send_json_success(
			array(
				'message' => 'Subscription updated!',
				'timestamp' => $next_payment_timestamp,
				'price' => $subscription_price,
			)
		);

		wp_die();
	}

	/**
	 * Get the total subscription sale data.
	 */
	public function wps_wsp_chart_data_callback() {
		check_ajax_referer( 'ajax-nonce', 'nonce' );

		$start_date = isset( $_POST['startDate'] ) ? sanitize_text_field( wp_unslash( $_POST['startDate'] ) ) : null;
		$end_date = isset( $_POST['endDate'] ) ? sanitize_text_field( wp_unslash( $_POST['endDate'] ) ) : null;

		$args = array(
			'date_created' => $start_date . '...' . $end_date,
			'limit'        => -1,
			'post_type'    => 'wps_subscriptions',
			'return'       => 'ids',
		);
		$orders = wc_get_orders( $args );

		if ( ! empty( $orders ) ) {
			$orders = array_reverse( $orders );
			$temp_order_date    = array();
			$temp_no_order      = array();
			$temp_order_revenue = array();
			$display_data = array();

			foreach ( $orders as $order_id ) {
				$subscription = new WPS_Subscription( $order_id );

				$subscription_created = $subscription->get_date_created();

				$formatted_date = $subscription_created->format( wc_date_format() );

				$total = $subscription->get_total();

				if ( ! in_array( $formatted_date, $temp_order_date ) ) {
					$temp_order_date[] = $formatted_date;
				}
				if ( isset( $temp_no_order[ $formatted_date ] ) ) {
					$temp_no_order[ $formatted_date ] += 1;
				} else {
					$temp_no_order[ $formatted_date ] = 1;
				}
				if ( isset( $temp_order_revenue[ $formatted_date ] ) ) {
					$temp_order_revenue[ $formatted_date ] += $total;

				} else {
					$temp_order_revenue[ $formatted_date ] = $total;
				}
				$parent_id = $subscription->get_meta( 'wps_parent_order' );
				$status = $subscription->get_meta( 'wps_subscription_status' );
				$last_renewal_orders = $subscription->get_meta( 'wps_wsp_renewal_order_data' );
				$last_renewal_order_id = esc_attr__( 'No Renewal', 'woocommerce-subscriptions-pro' );
				if ( is_array( $last_renewal_orders ) ) {
					$last_renewal_order_id = end( $last_renewal_orders );
				}
				$display_data[] = array(
					'id' => $order_id,
					'parent_id' => $parent_id,
					'status' => $status,
					'last_renewal_id' => $last_renewal_order_id,
					'date' => $formatted_date,
				);
			}
			$data = array(
				'dates' => $temp_order_date,
				'newSubscriptions' => array_values( $temp_no_order ),
				'subscriptionsRevenue' => array_values( $temp_order_revenue ),
				'displayData' => $display_data,
			);
		} else {
			$data = array(
				'dates' => null,
				'newSubscriptions' => null,
				'subscriptionsRevenue' => null,
			);
		}
		echo wp_json_encode( $data );
		wp_die();
	}
	/**
	 * Get the total product sale data.
	 */
	public function wps_wsp_chart_product_data_callback() {
		check_ajax_referer( 'ajax-nonce', 'nonce' );

		$start_date = isset( $_POST['startDate'] ) ? sanitize_text_field( wp_unslash( $_POST['startDate'] ) ) : null;
		$end_date = isset( $_POST['endDate'] ) ? sanitize_text_field( wp_unslash( $_POST['endDate'] ) ) : null;

		$args = array(
			'limit' => -1,
			'post_type' => 'wps_subscriptions',
			'date_created' => $start_date . '...' . $end_date,
			'return' => 'ids',
		);
		$orders = wc_get_orders( $args );

		$result       = array();
		$display_data = array();
		$product_ids  = array();
		if ( ! empty( $orders ) ) {
			$orders = array_reverse( $orders );
			$temp_order_date    = array();
			$temp_no_order      = array();
			$temp_order_revenue = array();

			foreach ( $orders as $order_id ) {
				$subscription = new WPS_Subscription( $order_id );

				$product_id = $subscription->get_meta( 'product_id' );

				$product_ids[ $product_id ] += 1;
			}
			// Calculate total count of products.
			$total_count = array_sum( $product_ids );

			// Sorting the array.
			arsort( $product_ids );

			// Extract top 3 products.
			$top_products = array_slice( $product_ids, 0, 4, true );
			$other_products = array_slice( $product_ids, 4, null, true );

			$others_percentage = 0;

			// Calculate percentages for top products.
			foreach ( $top_products as $product_id => $count ) {
				$percentage = ( $count / $total_count ) * 100;
				$product = get_post( $product_id );
				$result[] = array(
					'value' => round( $percentage, 2 ), // Rounded to 2 decimal places.
					'name' => 'ID : ' . $product->ID . ' | ' . $product->post_title,
				);
			}

			// Calculate "Others" percentage.
			foreach ( $other_products as $product_id => $count ) {
				$others_percentage += ( $count / $total_count ) * 100;
			}

			$result[] = array(
				'value' => round( $others_percentage, 2 ), // Rounded to 2 decimal places.
				'name' => 'others',
			);
			foreach ( $product_ids as $product_id => $count ) {
				$product = wc_get_product( $product_id );
				if ( empty( $product ) ) {
					continue;
				}
				$display_data[] = array(
					'id' => $product_id,
					'name' => $product->get_name(),
					'type' => $product->get_type(),
					'count' => $count,
				);
			}
		}
		$res_result['displayData'] = $display_data;
		$res_result['result1'] = $result;
		echo wp_json_encode( $res_result );

		wp_die();
	}
	/**
	 * Get the renewal orders sale data .
	 */
	public function wps_wsp_chart_total_renewal_callback() {
		check_ajax_referer( 'ajax-nonce', 'nonce' );

		$start_date = isset( $_POST['startDate'] ) ? sanitize_text_field( wp_unslash( $_POST['startDate'] ) ) : null;
		$end_date = isset( $_POST['endDate'] ) ? sanitize_text_field( wp_unslash( $_POST['endDate'] ) ) : null;

		$args = array(
			'date_created' => $start_date . '...' . $end_date,
			'limit' => -1,
			'meta_key'  => 'wps_sfw_renewal_order',
			'meta_value' => 'yes',
			'return' => 'ids',
		);

		$orders = wc_get_orders( $args );

		$array_data = array();
		$display_data = array();
		if ( ! empty( $orders ) ) {
			$orders = array_reverse( $orders );
			foreach ( $orders as $id ) {
				$renewal_order = wc_get_order( $id );
				$subcription_id = $renewal_order->get_meta( 'wps_sfw_subscription' );
				$parent_id = $renewal_order->get_meta( 'wps_sfw_parent_order_id' );
				$status = $renewal_order->get_status();
				$product_id = wps_wsp_get_meta_data( $subcription_id, 'product_id', true );
				$created_date = $renewal_order->get_date_created();
				$date = $created_date->date( wc_date_format() );
				$total = $renewal_order->get_total();

				$array_data[ $date ]['ids'][] = $id;
				$array_data[ $date ]['parent_id'][] = $parent_id;
				$array_data[ $date ]['subcription_id'][] = $subcription_id;
				$array_data[ $date ]['status'][] = $status;
				$array_data[ $date ]['product_id'][] = $product_id;
				$array_data[ $date ]['total'][] = $total;

				$display_data[] = array(
					'id' => $id,
					'subscription_id' => $subcription_id,
					'status' => $status,
					'date' => $date,
				);
			}
		}
		$res_result = array(
			'result2' => $array_data,
			'displayData' => $display_data,
		);
		echo json_encode( $res_result );

		wp_die();
	}
	/**
	 * Get the cancelled subscriptions data .
	 */
	public function wps_wsp_get_cancelled_subscription_callback() {
		check_ajax_referer( 'ajax-nonce', 'nonce' );

		$start_date = isset( $_POST['startDate'] ) ? sanitize_text_field( wp_unslash( $_POST['startDate'] ) ) : null;
		$end_date = isset( $_POST['endDate'] ) ? sanitize_text_field( wp_unslash( $_POST['endDate'] ) ) : null;

		$args = array(
			'date_created' => $start_date . '...' . $end_date,
			'limit' => -1,
			'post_type' => 'wps_subscriptions',
			'meta_key'  => 'wps_subscription_status',
			'meta_value' => 'cancelled',
			'return' => 'ids',
		);
		$orders = wc_get_orders( $args );

		$array_ids      = array();
		$cancelled_subs = array();
		$display_data   = array();
		if ( ! empty( $orders ) ) {
			foreach ( $orders as $subscription_id ) {

				$subscription = new WPS_Subscription( $subscription_id );
				$created_date = $subscription->get_date_created();
				$date = $created_date->date( wc_date_format() );
				$by = $subscription->get_meta( 'wps_subscription_cancelled_by' );
				$cancelled_date = $subscription->get_meta( 'wps_subscription_cancelled_date' );
				$cancelled_date = $cancelled_date ? date_i18n( wc_date_format(), $cancelled_date ) : null;

				$cancelled_subs[ $date ]['id'][] = $subscription_id;
				$cancelled_subs[ $date ]['reason'][] = $by;

				$display_data[] = array(
					'id' => $subscription_id,
					'reason' => $by,
					'status' => $status,
					'date' => $date,
					'cancelled_date' => $cancelled_date,
				);

			}
		}
		$res_result = array(
			'result3' => $cancelled_subs,
			'displayData' => $display_data,
		);
		echo wp_json_encode( $res_result );

		wp_die();
	}
	/**
	 * Get the renewed subscription data.
	 */
	public function wps_wsp_get_renewed_subscription_callback() {
		check_ajax_referer( 'ajax-nonce', 'nonce' );

		$start_date = isset( $_POST['startDate'] ) ? sanitize_text_field( wp_unslash( $_POST['startDate'] ) ) : null;
		$end_date = isset( $_POST['endDate'] ) ? sanitize_text_field( wp_unslash( $_POST['endDate'] ) ) : null;

		$args = array(
			'limit'        => -1,
			'date_created' => $start_date . '...' . $end_date,
			'post_type' => 'wps_subscriptions',
			'meta_query'   => array(
				array(
					'key'     => 'wps_wsp_renewal_order_data',
					'value'   => '',
					'compare' => '!=',
				),
			),
			'return' => 'ids',
		);
		$orders = wc_get_orders( $args );

		$subscription_data = array();
		if ( ! empty( $orders ) ) {
			$orders = array_reverse( $orders );
			foreach ( $orders as $subscription_id ) {
				$subscription = new WPS_Subscription( $subscription_id );

				$parent_id = $subscription->get_meta( 'wps_parent_order' );
				$status = $subscription->get_meta( 'wps_subscription_status' );
				$product_id = $subscription->get_meta( 'product_id' );

				$subscription_created = $subscription->get_date_created();

				$formatted_date = $subscription_created->format( wc_date_format() );

				$total = $subscription->get_total();

				$subscription_data[ $formatted_date ]['id'][] = $subscription_id;
				$subscription_data[ $formatted_date ]['parent_id'][] = $parent_id;
				$subscription_data[ $formatted_date ]['status'][] = $status;
				$subscription_data[ $formatted_date ]['product_id'][] = $product_id;
				$subscription_data[ $formatted_date ]['total'][] = $total;

				$display_data[] = array(
					'id' => $subscription_id,
					'status' => $status,
					'see_renewals' => $subscription_id,
					'date' => $formatted_date,
				);
			}
		}
		$res_result = array(
			'result4' => $subscription_data,
			'displayData' => $display_data,
		);
		echo wp_json_encode( $res_result );

		wp_die();
	}
	/**
	 * Get the monthly renewal revenue data.
	 */
	public function wps_wsp_get_mrr_data_callback() {
		check_ajax_referer( 'ajax-nonce', 'nonce' );

		$start_date = isset( $_POST['startDate'] ) ? sanitize_text_field( wp_unslash( $_POST['startDate'] ) ) : null;
		$end_date = isset( $_POST['endDate'] ) ? sanitize_text_field( wp_unslash( $_POST['endDate'] ) ) : null;

		$args = array(
			'limit'        => -1,
			'date_created' => $start_date . '...' . $end_date,
			'return' => 'ids',
			'meta_key'  => 'wps_sfw_renewal_order',
			'meta_value' => 'yes',
			'status' => array( 'wc-processing', 'wc-completed' ),
		);

		$orders = wc_get_orders( $args );

		$subscription_data = array();
		$display_data = array();
		if ( ! empty( $orders ) ) {
			$orders = array_reverse( $orders );
			foreach ( $orders as $renewal_id ) {
				$renewal = wc_get_order( $renewal_id );
				$renewal_created = $renewal->get_date_created();
				$formatted_date = $renewal_created->format( 'F, y' );

				$total = $renewal->get_total();
				$subcription_id = $renewal->get_meta( 'wps_sfw_subscription' );

				$subscription_data[ $formatted_date ]['ids'][] = $renewal_id;
				$subscription_data[ $formatted_date ]['total'][] = $total;

				$display_data[] = array(
					'id' => $renewal_id,
					'subscription_id' => $subcription_id,
					'total' => $total,
					'date' => $formatted_date,
				);
			}
		}
		$res_result = array(
			'result5' => $subscription_data,
			'displayData' => $display_data,
		);
		echo wp_json_encode( $res_result );
		wp_die();
	}
	/**
	 * Get the grid data.
	 */
	public function wps_wsp_get_grid_data_callback() {
		check_ajax_referer( 'ajax-nonce', 'nonce' );

		$start_date = isset( $_POST['startDate'] ) ? sanitize_text_field( wp_unslash( $_POST['startDate'] ) ) : null;
		$end_date = isset( $_POST['endDate'] ) ? sanitize_text_field( wp_unslash( $_POST['endDate'] ) ) : null;

		$args = array(
			'date_created' => $start_date . '...' . $end_date,
			'limit' => -1,
			'post_type' => 'wps_subscriptions',
			'return' => 'ids',
		);
		$response_data = array();

		$orders = wc_get_orders( $args );
		if ( ! empty( $orders ) ) {
			$response_data[1] = count( $orders );
		} else {
			$response_data[1] = 0;
		}

		$args = array(
			'limit' => -1,
			'post_type' => 'wps_subscriptions',
			'date_created' => $start_date . '...' . $end_date,
			'return' => 'ids',
		);

		$orders = wc_get_orders( $args );

		$product_ids = array();
		if ( ! empty( $orders ) ) {
			$orders = array_reverse( $orders );
			$temp_order_date    = array();
			$temp_no_order      = array();
			$temp_order_revenue = array();

			foreach ( $orders as $order_id ) {
				$subscription = new WPS_Subscription( $order_id );

				$product_id = $subscription->get_meta( 'product_id' );

				$product_ids[ $product_id ] += 1;
			}
		}
		if ( ! empty( $product_ids ) ) {
			$response_data[2] = count( $product_ids );
		} else {
			$response_data[2] = 0;
		}

		$args = array(
			'date_created' => $start_date . '...' . $end_date,
			'limit' => -1,
			'meta_key'  => 'wps_sfw_renewal_order',
			'meta_value' => 'yes',
			'return' => 'ids',
		);

		$orders = wc_get_orders( $args );

		if ( ! empty( $orders ) ) {
			$response_data[3] = count( $orders );
		} else {
			$response_data[3] = 0;
		}

		$args = array(
			'date_created' => $start_date . '...' . $end_date,
			'limit' => -1,
			'post_type' => 'wps_subscriptions',
			'meta_key'  => 'wps_subscription_status',
			'meta_value' => 'cancelled',
			'return' => 'ids',
		);

		$orders = wc_get_orders( $args );

		if ( ! empty( $orders ) ) {
			$response_data[4] = count( $orders );
		} else {
			$response_data[4] = 0;
		}

		$args = array(
			'limit'        => -1,
			'date_created' => $start_date . '...' . $end_date,
			'post_type' => 'wps_subscriptions',
			'meta_query'   => array(
				array(
					'key'     => 'wps_wsp_renewal_order_data',
					'value'   => '',
					'compare' => '!=',
				),
			),
			'return' => 'ids',
		);

		$orders = wc_get_orders( $args );

		if ( ! empty( $orders ) ) {
			$response_data[5] = count( $orders );
		} else {
			$response_data[5] = 0;
		}

		$args = array(
			'limit'        => -1,
			'date_created' => $start_date . '...' . $end_date,
			'return' => 'ids',
			'meta_key'  => 'wps_sfw_renewal_order',
			'meta_value' => 'yes',
			'status' => array( 'wc-processing', 'wc-completed' ),
		);

		$orders = wc_get_orders( $args );
		$total  = 0;
		if ( ! empty( $orders ) ) {

			foreach ( $orders as $renewal_id ) {
				$renewal = wc_get_order( $renewal_id );
				$total += $renewal->get_total();
			}
			$response_data[6] = $total;
		} else {
			$response_data[6] = 0;
		}

		echo json_encode( $response_data );
		wp_die();
	}

	/**
	 * Get the churn rate data.
	 */
	public function wps_wsp_get_churn_arr_data_callback() {

		check_ajax_referer( 'ajax-nonce', 'nonce' );

		$churn_rate = 0;

		$current_year = gmdate( 'Y' );

		// Set the start date to January 1st of the current year.
		$start_date = "$current_year-01-01";

		// Set the end date to today's date.
		$end_date = gmdate( 'Y-m-d' );

		$args = array(
			'limit'        => -1,
			'date_created' => $start_date . '...' . $end_date,
			'return' => 'ids',
			'meta_key'  => 'wps_sfw_renewal_order',
			'meta_value' => 'yes',
			'status' => array( 'wc-processing', 'wc-completed' ),
		);

		$orders = wc_get_orders( $args );

		$sub_args = array(
			'date_created' => $start_date . '...' . $end_date,
			'limit' => -1,
			'post_type' => 'wps_subscriptions',
			'return' => 'ids',
		);
		$sub_orders = wc_get_orders( $sub_args );

		$total = 0;
		$sub_count = 0;
		$sub_cancel_count = 0;
		if ( ! empty( $orders ) ) {
			$orders = array_reverse( $orders );
			foreach ( $orders as $renewal_id ) {
				$renewal = wc_get_order( $renewal_id );
				$total += $renewal->get_total();
			}
		}
		if ( ! empty( $sub_orders ) ) {
			$sub_orders = array_reverse( $sub_orders );
			foreach ( $sub_orders as $subscription_id ) {
				$subscription = new WPS_Subscription( $subscription_id );
				$wps_subscription_status = $subscription->get_meta( 'wps_subscription_status' );
				if ( 'cancelled' === $wps_subscription_status ) {
					$sub_cancel_count++;
				}
				$sub_count++;
			}
		}

		$churn_rate = ( $sub_cancel_count / $sub_count ) * 100;
		$churn_rate = round( $churn_rate, 2 );

		wp_send_json_success(
			array(
				'churnRate' => $churn_rate,
				'arr' => $total,
			)
		);
	}

	/**
	 * Subscription box step template.
	 */
	public function wps_sfw_after_subscription_box_steps_html_callback() {
		?>
			<!-- extra step hide -->
				<script type="text/html" id="tmpl-wps-sfw-step-template">
					<div class="wps_sfw_step_card" data-step="{{stepKey}}" style="border:1px solid #ddd; padding:12px; margin-bottom:12px;">
						<div class="wps_sfw_step_header" style="display:flex; align-items:center; justify-content:space-between;">
						<strong>{{STEP_TITLE}}</strong>
						<button type="button" class="button button-link-delete wps_sfw_remove_step">Remove</button>
						</div>

						<p class="form-field">
						<label for="wps_sfw_label_{{stepKey}}"><strong>Box Step Label</strong></label>
						<input id="wps_sfw_label_{{stepKey}}" type="text" name="wps_sfw_steps[{{stepKey}}][label]" class="short" placeholder="<?php echo esc_attr__( 'Enter step label', 'subscriptions-for-woocommerce' ); ?>">
						</p>

						<p class="form-field">
						<label for="wps_sfw_type_{{stepKey}}">Apply Subscription Box To</label>
						<select id="wps_sfw_type_{{stepKey}}" class="wps_sfw_step_type" name="wps_sfw_steps[{{stepKey}}][type]">
							<option value="specific_products">Specific Products</option>
							<option value="specific_categories">Specific Categories</option>
						</select>
						</p>

						<p class="form-field wps_sfw_products_field">
						<label for="wps_sfw_products_{{stepKey}}">Select Products</label>
						<select id="wps_sfw_products_{{stepKey}}" name="wps_sfw_steps[{{stepKey}}][product_ids][]" class="wc-product-search" multiple="multiple" style="width:100%;"
								data-placeholder="<?php echo esc_attr__( 'Search for a product…', 'subscriptions-for-woocommerce' ); ?>"
								data-action="woocommerce_json_search_products_and_variations"></select>
						</p>

						<p class="form-field wps_sfw_categories_field" style="display:none;">
						<label for="wps_sfw_categories_{{stepKey}}">Select Categories</label>
						<select id="wps_sfw_categories_{{stepKey}}" name="wps_sfw_steps[{{stepKey}}][category_ids][]" class="wc-category-search" multiple="multiple" style="width:100%;"
								data-placeholder="<?php echo esc_attr__( 'Search for categories…', 'subscriptions-for-woocommerce' ); ?>"
								data-action="woocommerce_json_search_categories">
							</select>
						</p>
						<p class="form-field wps_sfw_subscription_box_min_number_field" >
							<label for="wps_sfw_subscription_box_min_number_{{stepKey}}">Minimum Number of Product Allow in this step</label>
								<input type="number" class="short wc_input_number"  min="1" name="wps_sfw_steps[{{stepKey}}][min_num]" id="wps_sfw_subscription_box_min_number_{{stepKey}}"  placeholder="<?php esc_html_e( 'Enter minimum number', 'subscriptions-for-woocommerce' ); ?>">	 
						</p>
						<p class="form-field wps_sfw_subscription_box_max_number_field">
							<label for="wps_sfw_subscription_box_max_number_{{stepKey}}">Maximum Number of Product Allow in this step</label>
								<input type="number" class="short wc_input_number"  min="1" name="wps_sfw_steps[{{stepKey}}][max_num]" id="wps_sfw_subscription_box_max_number_{{stepKey}}"  placeholder="<?php esc_html_e( 'Enter maximum number', 'subscriptions-for-woocommerce' ); ?>">	 
						</p>
					</div>
				</script>
				 <!-- extra step hide -->
				  <?php
	}
}