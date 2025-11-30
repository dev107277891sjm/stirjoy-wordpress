(function( $ ) {
    'use strict';
    // on clicking license check button
    $(document).on('click', '#wps_check_license', function(e){
        e.preventDefault();
           
        $.ajax({
            type: "POST",
            dataType: "JSON",
            url: wsfwp_admin_notice_param.ajaxurl,
            data: {
                action: "wps_wsp_check_license_key_status",
                nonce : wsfwp_admin_notice_param.nonce,
            },
       
            success: function(data) {
                if (data.status == true) {
                    jQuery("div.wps-subsc_notice p").html(data.msg);
                    setTimeout(function(){
                        location = wsfwp_admin_notice_param.wsfwp_admin_param_location;
                    }, 2000);                  
                }
                else{
                    jQuery("div.wps-subsc_notice p").html(data.msg);
                    setTimeout(function(){
                        location = wsfwp_admin_notice_param.wsfwp_admin_param_location;
                    }, 2000);
                }
            },
        })
        .fail(function ( response ) {
            location = wsfwp_admin_notice_param.wsfwp_admin_param_location;
        });
       
    });
 
 
   
 })( jQuery );
 