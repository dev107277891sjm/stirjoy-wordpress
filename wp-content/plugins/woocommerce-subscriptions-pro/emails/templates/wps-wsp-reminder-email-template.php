<?php
/**
 * Plan Expire Email template
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    Woocommerce_Subscriptions_Pro
 * @subpackage Woocommerce_Subscriptions_Pro/email
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>
<?php /* translators: %s: search term */ ?>
<p><?php printf( esc_html__( 'We will cut your recurring payment for this subscription [#%s]. Their subscription\'s details are as follows:', 'woocommerce-subscriptions-pro' ), esc_html( $wps_subscription ) ); ?></p>

<?php
wps_wsp_email_subscriptions_details_recurring_reminder( $wps_subscription );

do_action( 'woocommerce_email_footer', $email );
