<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the html field for general tab.
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    Woocommerce_Subscriptions_Pro
 * @subpackage Woocommerce_Subscriptions_Pro/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $wsp_wps_wsp_obj;
$wsp_genaral_settings = apply_filters( 'wsp_others_settings_array', array() );
?>
<!--  template file for admin settings. -->
<form action="" method="POST" class="wps-wsp-advanced-section-form">
	<div class="wsp-secion-wrap">
		<?php
		$wsp_general_html = $wsp_wps_wsp_obj->wps_wsp_plug_generate_html( $wsp_genaral_settings );
		echo esc_html( $wsp_general_html );
		wp_nonce_field( 'wps-wsp-others-nonce', 'wps-wsp-others-nonce-field' );
		?>
	</div>
</form>
