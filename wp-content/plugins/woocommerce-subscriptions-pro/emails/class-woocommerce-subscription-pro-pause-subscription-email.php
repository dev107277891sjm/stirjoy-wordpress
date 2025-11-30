<?php
/**
 * Paused Email template
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    Woocommerce_Subscriptions_Pro
 * @subpackage Woocommerce_Subscriptions_Pro/email
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Woocommerce_Subscription_Pro_Pause_Subscription_Email' ) ) {

	/**
	 * Paused Email template Class
	 *
	 * @link       https://wpswings.com/
	 * @since      1.0.0
	 *
	 * @package    Woocommerce_Subscriptions_Pro
	 * @subpackage Woocommerce_Subscriptions_Pro/email
	 */
	class Woocommerce_Subscription_Pro_Pause_Subscription_Email extends WC_Email {
		/**
		 * Create class for email notification.
		 *
		 * @access public
		 */
		public function __construct() {

			$this->id          = 'wps_wsp_pause_subscription';
			$this->title       = __( 'Paused Subscription Email Notification', 'woocommerce-subscriptions-pro' );

			$this->description = __( 'This Email Notification Send if any subscription is Paused', 'woocommerce-subscriptions-pro' );

			$this->template_html  = 'wps-wsp-pause-subscription-email-template.php';
			$this->template_plain = 'plain/wps-wsp-pause-subscription-email-template.php';
			$this->template_base  = WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_PATH . 'emails/templates/';

			parent::__construct();

			$this->recipient = $this->get_option( 'recipient' );

			if ( ! $this->recipient ) {
				$this->recipient = get_option( 'admin_email' );
			}
		}

		/**
		 * Get email subject.
		 *
		 * @since  1.0.0
		 * @return string
		 */
		public function get_default_subject() {
			return __( 'Paused Susbcription Email {site_title}', 'woocommerce-subscriptions-pro' );
		}

		/**
		 * Get email heading.
		 *
		 * @since  1.0.0
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Subscription Paused', 'woocommerce-subscriptions-pro' );
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
			$this->object = $wps_subscription;

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

return new Woocommerce_Subscription_Pro_Pause_Subscription_Email();
