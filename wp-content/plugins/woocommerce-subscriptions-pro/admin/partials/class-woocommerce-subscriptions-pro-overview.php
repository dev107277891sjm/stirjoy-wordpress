<?php
/**
 * Exit if accessed directly
 *
 * @since      1.0.0
 * @package    Woocommerce_Subscriptions_Pro
 * @subpackage Woocommerce_Subscriptions_Pro/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'Woocommerce_Subscriptions_Pro_Overview' ) ) {

	/**
	 * This is construct of overview section.
	 *
	 * @name Woocommerce_Subscriptions_Pro_Overview
	 * @since      1.0.0
	 * @category Class
	 * @author WP Swings<ticket@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	class Woocommerce_Subscriptions_Pro_Overview {
		/**
		 * Create class for overview.
		 *
		 * @access public
		 */
		public function __construct() {
			add_action( 'wps_sfw_overview_feature_description', array( $this, 'wps_wsp_overview_feature_description' ) );
			add_action( 'wps_sfw_overview_keywords_description', array( $this, 'wps_wsp_overview_keywords_description' ) );
		}

			/**
			 * This function is used to show features description in overview section.
			 *
			 * @name wps_wsp_overview_feature_description
			 * @since    1.0.0
			 */
		public function wps_wsp_overview_feature_description() {
			?>
					<ul class="sfw-overview__features-list">
						<li><?php esc_html_e( 'Create subscriptions for variation type products', 'woocommerce-subscriptions-pro' ); ?></li>
						<li><?php esc_html_e( 'Set and change subscriptions plan expiry date', 'woocommerce-subscriptions-pro' ); ?></li>
						<li><?php esc_html_e( 'Automatic retrial and cancellation of subscriptions plan', 'woocommerce-subscriptions-pro' ); ?></li>
						<li><?php esc_html_e( 'Upgrade or downgrade subscription plans by users', 'woocommerce-subscriptions-pro' ); ?></li>
						<li><?php esc_html_e( 'Exclusive coupon types', 'woocommerce-subscriptions-pro' ); ?></li>
						<li><?php esc_html_e( 'Pause Subscription plans', 'woocommerce-subscriptions-pro' ); ?></li>
						<li><?php esc_html_e( 'Email notifications for reminders', 'woocommerce-subscriptions-pro' ); ?></li>
						<li><?php esc_html_e( 'API of course for details on a mobile app', 'woocommerce-subscriptions-pro' ); ?></li>
						<li><?php esc_html_e( 'Export active subscriptions ', 'woocommerce-subscriptions-pro' ); ?></li>
					</ul>
				<?php
		}

			/**
			 * This function is used to show keywords section in overview section.
			 *
			 * @name wps_wsp_overview_keywords_description
			 * @since    1.0.0
			 */
		public function wps_wsp_overview_keywords_description() {
			?>
					<div class="sfw-overview__keywords-item">
						<div class="sfw-overview__keywords-card">
							<div class="sfw-overview__keywords-text">
								<img src="<?php echo esc_url( WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'admin/images/Set-Start-And-Due-Date.png' ); ?>" alt="feature_six" width="100px">
								<h4 class="sfw-overview__keywords-heading"><?php esc_html_e( 'Set Start And Due Date', 'woocommerce-subscriptions-pro' ); ?></h4>
								<p class="sfw-overview__keywords-description">
								<?php esc_html_e( 'Admin has settings to enable users to start a subscription from a certain date for a subscription plan. Users can change the subscription plan based on dates, months, or years as allowed by the admin.', 'woocommerce-subscriptions-pro' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="sfw-overview__keywords-item">
						<div class="sfw-overview__keywords-card">
							<div class="sfw-overview__keywords-text">
								<img src="<?php echo esc_url( WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'admin/images/Automatic-Payment-Retrial,-Cancel-Subscription-Plans.png' ); ?>" alt="feature_six" width="100px">
								<h4 class="sfw-overview__keywords-heading"><?php esc_html_e( 'Automatic Payment Retrial, Cancel Subscription Plans', 'woocommerce-subscriptions-pro' ); ?></h4>
								<p class="sfw-overview__keywords-description">
								<?php esc_html_e( 'Merchants can allow automatic subscription payment retry on failed attempts. After a set number of failed payment attempts, the subscription plan will be aborted automatically.', 'woocommerce-subscriptions-pro' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="sfw-overview__keywords-item">
						<div class="sfw-overview__keywords-card">
							<div class="sfw-overview__keywords-text">
								<img src="<?php echo esc_url( WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'admin/images/Subscription-Coupons.png' ); ?>" alt="feature_six" width="100px">
								<h4 class="sfw-overview__keywords-heading"><?php esc_html_e( 'Subscription Coupons', 'woocommerce-subscriptions-pro' ); ?></h4>
								<p class="sfw-overview__keywords-description">
								<?php esc_html_e( 'Different coupon types apply on subscription plans, i.e., Initial Fee Discount Type, Initial percentage discount Type, Recurring Product Discount Type, and Recurring Product percentage discount type. Users can apply them.', 'woocommerce-subscriptions-pro' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="sfw-overview__keywords-item">
						<div class="sfw-overview__keywords-card">
							<div class="sfw-overview__keywords-text">
								<img src="<?php echo esc_url( WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'admin/images/Handle-Proration.png' ); ?>" alt="feature_six" width="100px">
								<h4 class="sfw-overview__keywords-heading"><?php esc_html_e( 'Handle Proration', 'woocommerce-subscriptions-pro' ); ?></h4>
								<p class="sfw-overview__keywords-description">
								<?php esc_html_e( 'Admin can enable the users to upgrade and downgrade their subscription plans. Users can easily upgrade or downgrade their variable product subscription plans for a set period whenever they want. The WooCommerce Subscriptions Pro plugin can handle proration on recurring payments and sign-up fees.', 'woocommerce-subscriptions-pro' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="sfw-overview__keywords-item">
						<div class="sfw-overview__keywords-card">
							<div class="sfw-overview__keywords-text">
								<img src="<?php echo esc_url( WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'admin/images/Pause--Hold-and-Re-Activate-Subscription-Plans.png' ); ?>" alt="feature_six" width="100px">
								<h4 class="sfw-overview__keywords-heading"><?php esc_html_e( 'Pause / Hold and Re-Activate Subscription Plans', 'woocommerce-subscriptions-pro' ); ?></h4>
								<p class="sfw-overview__keywords-description">
								<?php esc_html_e( 'Admin gets the settings allowing users to start subscriptions from a specific date of a month. Users can pause and restart their subscription plans after putting them on hold for a particular time.', 'woocommerce-subscriptions-pro' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="sfw-overview__keywords-item">
						<div class="sfw-overview__keywords-card">
							<div class="sfw-overview__keywords-text">
								<img src="<?php echo esc_url( WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'admin/images/Email-notifications-for-Admin-and-Users.png' ); ?>" alt="feature_six" width="100px">
								<h4 class="sfw-overview__keywords-heading"><?php esc_html_e( 'Email notifications for Admin and Users.', 'woocommerce-subscriptions-pro' ); ?></h4>
								<p class="sfw-overview__keywords-description">
								<?php esc_html_e( 'Admin can enable email notification for all the activity related to subscription plans. There are email notification options are.,Subscription plan is going to expire, Subscription payment has been made, Subscription plan has been Paused/On-Hold, Subscription plan has been Resumed', 'woocommerce-subscriptions-pro' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="sfw-overview__keywords-item">
						<div class="sfw-overview__keywords-card">
							<div class="sfw-overview__keywords-text">
								<img src="<?php echo esc_url( WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'admin/images/Manual-and-Automatic-Subscription-Payment-Option.png' ); ?>" alt="feature_six" width="100px">
								<h4 class="sfw-overview__keywords-heading"><?php esc_html_e( 'Manual and Automatic Subscription Payment Option.', 'woocommerce-subscriptions-pro' ); ?></h4>
								<p class="sfw-overview__keywords-description">
								<?php esc_html_e( 'Admin can set manual or automatic subscription payment options. WooCommerce Subscription Pro will send an automated invoice to the customer payable through a link in the manual subscription payment. Offline payment COD is also available.', 'woocommerce-subscriptions-pro' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="sfw-overview__keywords-item">
						<div class="sfw-overview__keywords-card">
							<div class="sfw-overview__keywords-text">
								<img src="<?php echo esc_url( WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'admin/images/Export-Subscription-Plan-Details.png' ); ?>" alt="feature_six" width="100px">
								<h4 class="sfw-overview__keywords-heading"><?php esc_html_e( 'Export Subscription Plan Details.', 'woocommerce-subscriptions-pro' ); ?></h4>
								<p class="sfw-overview__keywords-description">
								<?php esc_html_e( 'Admin can export all active subscription plans in a file and view all subscription renewal orders under the Subscription table tab.', 'woocommerce-subscriptions-pro' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="sfw-overview__keywords-item">
						<div class="sfw-overview__keywords-card">
							<div class="sfw-overview__keywords-text">
								<img src="<?php echo esc_url( WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'admin/images/API-for-Mobile-App.png' ); ?>" alt="feature_six" width="100px">
								<h4 class="sfw-overview__keywords-heading"><?php esc_html_e( 'API for Mobile App.', 'woocommerce-subscriptions-pro' ); ?></h4>
								<p class="sfw-overview__keywords-description">
								<?php esc_html_e( 'APIs, of course, have been provided in the WooCommerce Subscriptions Pro to let the admin get subscription details on mobile apps quickly.', 'woocommerce-subscriptions-pro' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="sfw-overview__keywords-item">
						<div class="sfw-overview__keywords-card">
							<div class="sfw-overview__keywords-text">
								<img src="<?php echo esc_url( WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'admin/images/View-Subscriptions-Renewal-Order-for-Particular-Subscription.png' ); ?>" alt="feature_six" width="100px">
								<h4 class="sfw-overview__keywords-heading"><?php esc_html_e( 'View Subscriptions Renewal Order for Particular Subscription.', 'woocommerce-subscriptions-pro' ); ?></h4>
								<p class="sfw-overview__keywords-description">
								<?php esc_html_e( 'Admin can view all renewal orders for each subscription. It will help them to identify the number of recurring payments completed for a particular subscription plan.', 'woocommerce-subscriptions-pro' ); ?>
								</p>
							</div>
						</div>
					</div>
				<?php
		}
	}
}

return new Woocommerce_Subscriptions_Pro_Overview();
