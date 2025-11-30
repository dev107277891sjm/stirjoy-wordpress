<?php
/**
 * Reactivate Email template
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    Woocommerce_Subscriptions_Pro
 * @subpackage Woocommerce_Subscriptions_Pro/email
 */

use Automattic\WooCommerce\Utilities\OrderUtil;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Woocommerce_Subscription_Pro_New_Subscription_Email' ) ) {

	/**
	 * Reactivate Email template class
	 *
	 * @link       https://wpswings.com/
	 * @since      1.0.0
	 *
	 * @package    Woocommerce_Subscriptions_Pro
	 * @subpackage Woocommerce_Subscriptions_Pro/email
	 */
	class Woocommerce_Subscription_Pro_New_Subscription_Email extends WC_Email {
		/**
		 * Create class for email notification.
		 *
		 * @access public
		 */
		public function __construct() {

			$this->id          = 'wps_wsp_new_subscription';
			$this->title       = __( 'New Subscription Email Notification', 'woocommerce-subscriptions-pro' );
			$this->description = __( 'This Email Notification Send if any new subscription is created', 'woocommerce-subscriptions-pro' );
			$this->template_html  = 'wps-wsp-new-subscription-email-template.php';
			$this->template_plain = 'plain/wps-wsp-new-subscription-email-template.php';
			$this->template_base  = WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_PATH . 'emails/templates/';
			$this->customer_email = true;
			parent::__construct();
		}

		/**
		 * Get email subject.
		 *
		 * @since  1.0.0
		 * @return string
		 */
		public function get_default_subject() {
			return __( 'New Subscription Email {site_title}', 'woocommerce-subscriptions-pro' );
		}

		/**
		 * Get email heading.
		 *
		 * @since  1.0.0
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Subscription New', 'woocommerce-subscriptions-pro' );
		}

		/**
		 * This function is used to trigger for email.
		 *
		 * @since  1.0.0
		 * @param int $wps_subscription wps_subscription.
		 * @access public
		 * @return void
		 */
		public function trigger( $wps_subscription ) {
				$user_email = '';
			if ( $wps_subscription ) {

				$this->object = $wps_subscription;
				if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
					$subscription = new WPS_Subscription( $wps_subscription );
				} else {
					$subscription = wc_get_order( $wps_subscription );
				}
				$user_email = $subscription->get_billing_email();
				if ( isset( $user_email ) && ! empty( $user_email ) ) {
					$this->recipient = $user_email;
				} else {
					$wps_parent_order_id = wps_wsp_get_meta_data( $wps_subscription, 'wps_parent_order', true );
					$wps_parent_order = wc_get_order( $wps_parent_order_id );
					$user_email = $wps_parent_order->get_billing_email();
					$this->recipient = $user_email;
				}
			}

			if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
				return;
			}

			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		/**
		 * Get_content_html function.
		 *
		 * @access public
		 * @return string
		 */
		public function get_content_html() {
			return wc_get_template_html(
				$this->template_html,
				array(
					'wps_subscription'       => $this->object,
					'email_heading'      => $this->get_heading(),
					'sent_to_admin'      => true,
					'plain_text'         => false,
					'email' => $this,
				),
				'',
				$this->template_base
			);
		}

		/**
		 * Get_content_plain function.
		 *
		 * @access public
		 * @return string
		 */
		public function get_content_plain() {
			return wc_get_template_html(
				$this->template_plain,
				array(
					'wps_subscription'       => $this->object,
					'email_heading'      => $this->get_heading(),
					'sent_to_admin'      => true,
					'plain_text'         => true,
					'email' => $this,
				),
				'',
				$this->template_base
			);
		}

		/**
		 * Initialise Settings Form Fields
		 *
		 * @access public
		 * @return void
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'    => array(
					'title'   => __( 'Enable/Disable', 'woocommerce-subscriptions-pro' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this email notification', 'woocommerce-subscriptions-pro' ),
					'default' => 'no',
				),
				'recipient'  => array(
					'title'       => __( 'Recipient Email Address', 'woocommerce-subscriptions-pro' ),
					'type'        => 'text',
					// translators: placeholder is admin email.
					'description' => sprintf( __( 'Enter recipient email address. Defaults to %s.', 'woocommerce-subscriptions-pro' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
					'placeholder' => '',
					'default'     => '',
					'desc_tip'    => true,
				),
				'subject'    => array(
					'title'       => __( 'Subject', 'woocommerce-subscriptions-pro' ),
					'type'        => 'text',
					'description' => __( 'Enter the email subject', 'woocommerce-subscriptions-pro' ),
					'placeholder' => $this->get_default_subject(),
					'default'     => '',
					'desc_tip'    => true,
				),
				'heading'    => array(
					'title'       => __( 'Email Heading', 'woocommerce-subscriptions-pro' ),
					'type'        => 'text',
					'description' => __( 'Email Heading', 'woocommerce-subscriptions-pro' ),
					'placeholder' => $this->get_default_heading(),
					'default'     => '',
					'desc_tip'    => true,
				),
				'email_type' => array(
					'title'       => __( 'Email type', 'woocommerce-subscriptions-pro' ),
					'type'        => 'select',
					'description' => __( 'Choose which format of email to send.', 'woocommerce-subscriptions-pro' ),
					'default'     => 'html',
					'class'       => 'email_type wc-enhanced-select',
					'options'     => $this->get_email_type_options(),
					'desc_tip'    => true,
				),
			);
		}
	}

}

return new Woocommerce_Subscription_Pro_New_Subscription_Email();
