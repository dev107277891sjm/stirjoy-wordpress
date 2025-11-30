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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
echo esc_html( $email_heading ) . "\n\n"; // PHPCS:Ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>
<?php /* translators: %s: search term */ ?>
<p><?php printf( esc_html__( 'A subscription [#%s] has been created. Their subscription\'s details are as follows:', 'woocommerce-subscriptions-pro' ), esc_html( $wps_subscription ) ); ?></p>

<?php
$wps_product_name = wps_wsp_get_meta_data( $wps_subscription, 'product_name', true );
$product_qty = wps_wsp_get_meta_data( $wps_subscription, 'product_qty', true );
$wps_next_payment_date = wps_wsp_get_meta_data( $wps_subscription, 'wps_next_payment_date', true );
$wps_next_payment_date = wps_sfw_get_the_wordpress_date_format( $wps_next_payment_date );

?>
<table>
	<tr>
		<td><?php esc_html_e( 'Product', 'woocommerce-subscriptions-pro' ); ?></td>
		<td><?php echo esc_html( $wps_product_name ); ?> </td>
	</tr>
	<tr>
		<td> <?php esc_html_e( 'Quantity', 'woocommerce-subscriptions-pro' ); ?> </td>
		<td> <td><?php echo esc_html( $product_qty ); ?> </td> </td>
	</tr>
	<tr>
		<td> <?php esc_html_e( 'Price', 'woocommerce-subscriptions-pro' ); ?> </td>
		<td> <?php do_action( 'wps_sfw_display_susbcription_recerring_total_account_page', $wps_subscription ); ?> </td>
	</tr>
    <tr>
		<td> <?php esc_html_e( 'Next Recurring Date', 'woocommerce-subscriptions-pro' ); ?> </td>
		<td> <?php echo esc_html( $wps_next_payment_date ); ?> </td>
	</tr>
</table>
<?php
echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) ); // PHPCS:Ignore WordPress.Security.EscapeOutput.OutputNotEscaped
