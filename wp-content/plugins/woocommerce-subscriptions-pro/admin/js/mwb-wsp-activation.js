/**
 * The admin-specific js functionlity
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    Woocommerce_Subscriptions_Pro
 * @subpackage Woocommerce_Subscriptions_Pro/admin
 */

jQuery(document).ready(function ($) {
    jQuery(document).on('click','#wps-wsp-install-lite', function(e) {
        e.preventDefault();
        jQuery("#wps_wsp_notice_loader").show();
        var data = {
            action: 'wps_wsp_activate_lite_plugin',
        };
        $.ajax({
            url: wps_wsp_activation.ajaxurl,
            type: 'POST',
            data: data,
            success: function (response) {
                jQuery("#wps_wsp_notice_loader").hide();
                window.location.reload();
            }
        });
    });
});