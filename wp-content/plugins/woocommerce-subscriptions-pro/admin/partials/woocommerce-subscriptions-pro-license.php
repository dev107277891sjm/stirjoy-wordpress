<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the html field for API tab.
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

?>
<div class="wps-wsp-license-wrap">
	<h1><?php esc_html_e( 'Your License', 'woocommerce-subscriptions-pro' ); ?></h1>
	<div class="wps_wsp_license_text">
		<p>
			<?php
			esc_html_e( 'This is the License Activation Panel. After purchasing an extension from WP Swings you will get the purchase code of this extension. Please verify your purchase below so that you can use the feature of this plugin.', 'woocommerce-subscriptions-pro' );
			?>
		</p>
		<form id="wps_wsp_license_form">
			<div class="wps_wsp_license_form-col-left">
				<table class="wps-wsp-form-table">
				<tr>
					<th scope="row"><label for="puchase-code"><?php esc_html_e( 'Purchase Code: ', 'woocommerce-subscriptions-pro' ); ?></label></th>
					<td>
						<input type="text" id="wps_wsp_license_key" name="purchase-code" required="" size="30" class="wps-wsp-purchase-code" value="" placeholder="<?php esc_html_e( 'Enter your code here...', 'woocommerce-subscriptions-pro' ); ?>">
					</td>
				</tr>
				</table>
						<p id="wps_wsp_license_activation_status"></p>
						<p class="submit">
						<button id="wps_wsp_license_activate" required="" class="button-primary woocommerce-save-button" name="wps_wsp_license_settings"><?php esc_html_e( 'Validate', 'woocommerce-subscriptions-pro' ); ?></button>
					</p>
			</div>
			<div class="wps_wsp_license_form-col-right">
				<div id="wps-wsp-ajax-loading-gif" class="wps-wsp-ajax-loading-gif" style="display: none;">
					<img src="<?php echo esc_url( WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'admin/image/loading.gif' ); ?>">
				</div>
			</div>
		</form>
	</div>
</div>
