<?php
/**
 * My Addresses
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/my-address.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$customer_id = get_current_user_id();

if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) {
	$get_addresses = apply_filters( 'woocommerce_my_account_get_addresses', array(
		'billing' => __( 'Billing address','woocommerce-multiple-addresses-pro' ),
		'shipping' => __( 'Shipping address','woocommerce-multiple-addresses-pro' ),
	), $customer_id );
} else {
	$get_addresses = apply_filters( 'woocommerce_my_account_get_addresses', array(
		'billing' => __( 'Billing address','woocommerce-multiple-addresses-pro' ),
	), $customer_id );
}

$oldcol = 1;
$col    = 1;
?>

<p>
	<?php echo apply_filters( 'woocommerce_my_account_my_address_description', __( 'The following addresses will be used on the checkout page by default.','woocommerce' ) ); ?>
</p>

<?php if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) : ?>
	<div class="u-columns woocommerce-Addresses col2-set addresses">
<?php endif; ?>

<?php foreach ( $get_addresses as $name => $title ) : ?>

	<div class="u-column<?php echo ( ( $col = $col * -1 ) < 0 ) ? 1 : 2; ?> col-<?php echo ( ( $oldcol = $oldcol * -1 ) < 0 ) ? 1 : 2; ?> woocommerce-Address">
		<header class="woocommerce-Address-title title">
			<h3><?php echo $title; ?></h3>
			<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', $name ) ); ?>" class="edit"><?php _e( 'Edit','woocommerce' ); ?></a>
		</header>
		<address><?php
			$address = wc_get_account_formatted_address( $name );
			echo $address ? wp_kses_post( $address ) : esc_html_e( 'You have not set up this type of address yet.','woocommerce-multiple-addresses-pro' );
		?></address>
	</div>

<?php endforeach; ?>

<?php if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) : ?>
	</div>
<?php endif;
do_action('thwma_after_address_display', $customer_id);
// $settings = THWMA_Utils::get_advanced_settings();
// if(!empty($settings)){
// 	$user_roles = array();
// 	$current_user = array();
// 	$user = wp_get_current_user();
// 	$enable_usr_acnt = (isset($settings['enable_user_account'])) ? $settings['enable_user_account'] : '';
// 	$user_roles = (isset($settings['select_user_role'])) ? $settings['select_user_role'] : '';
// 	$userroles = explode(',', $user_roles);
// 	$current_user = $user->roles;
// 	if($enable_usr_acnt == 'yes'){
// 		if(!empty($user_roles)){
// 			foreach( $current_user as $cur_user ){
// 				if (in_array($cur_user, $userroles, TRUE)) { 
// 					do_action('thwma_after_address_display', $customer_id);
// 				}
// 			}
// 		} else {
// 			do_action('thwma_after_address_display', $customer_id);
// 		}
// 	}
// }