<?php
/**
 * Renewal Invoice Email template
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

if ( ! class_exists( 'Woocommerce_Subscription_Pro_Renewal_Subscription_Invoice_Email' ) ) {

	/**
	 * Renewal Invoice Email template Class
	 *
	 * @link       https://wpswings.com/
	 * @since      1.0.0
	 *
	 * @package    Woocommerce_Subscriptions_Pro
	 * @subpackage Woocommerce_Subscriptions_Pro/email
	 */
	class Woocommerce_Subscription_Pro_Renewal_Subscription_Invoice_Email extends WC_Email {
		/**
		 * Create class for email notification.
		 *
		 * @access public
		 */
		public function __construct() {

			$this->id          = 'wps_wsp_renewal_subscription_invoice';
			$this->title       = __( 'Manual Renewal Subscription Invoice Email Notification', 'woocommerce-subscriptions-pro' );
			$this->customer_email = true;
			$this->description = __( 'invoice, The email contains renewal order information and payment links', 'woocommerce-subscriptions-pro' );

			$this->template_html  = 'wps-wsp-renewal-subscription-invoice-email-template.php';
			$this->template_plain = 'plan/wps-wsp-renewal-subscription-invoice-email-template.php';
			$this->template_base  = WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_PATH . 'emails/templates/';

			parent::__construct();
		}

		/**
		 * Get email subject.
		 *
		 * @since  1.0.0
		 * @return string
		 */
		public function get_default_subject() {
			return __( 'Invoice for renewal order {order_number}', 'woocommerce-subscriptions-pro' );
		}

		/**
		 * Get email heading.
		 *
		 * @since  1.0.0
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Invoice for renewal order {order_number}', 'woocommerce-subscriptions-pro' );
		}

		/**
		 * This function is used to trigger for email.
		 *
		 * @since  1.0.0
		 * @param int    $order_id order_id.
		 * @param object $order order.
		 */
		public function trigger( $order_id, $order = null ) {

			if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
				$order = wc_get_order( $order_id );
			}

			if ( is_a( $order, 'WC_Order' ) ) {
				$this->object    = $order;
				$this->recipient = $this->object->get_billing_email();

				$order_number_index = array_search( '{order_number}', $this->find );
				if ( false === $order_number_index ) {
					$this->find['order_number']    = '{order_number}';
					$this->replace['order_number'] = $this->object->get_order_number();
				} else {
					$this->replace[ $order_number_index ] = $this->object->get_order_number();
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
					'order'              => $this->object,
					'email_heading'      => $this->get_heading(),
					'sent_to_admin'      => false,
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
					'order'       => $this->object,
					'email_heading'      => $this->get_heading(),
					'sent_to_admin'      => false,
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

return new Woocommerce_Subscription_Pro_Renewal_Subscription_Invoice_Email();
