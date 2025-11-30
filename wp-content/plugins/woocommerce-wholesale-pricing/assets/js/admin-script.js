jQuery(document).ready(
    function ($) {


        // jQuery(document).on('click', '.save-general-setting', function() {
        //     setTimeout( function() {
        //         location.reload();
        //     }, 2000 );
        // }); 

       //  function check_cookie_for_submenu() {.

       //      if ( jQuery('#enable-groups').is(':checked') ) {
       //         document.cookie = "wwp_wholesale_enable_groups=yes; path=/;";
       //     } else {
       //         document.cookie = "wwp_wholesale_enable_groups=no; path=/;";
       //     }

       //     if ( jQuery('#advance_registration_form').is(':checked') ) {
       //         document.cookie = "wwp_advance_registration_form=yes; path=/;";
       //     } else {
       //         document.cookie = "wwp_advance_registration_form=no; path=/;";
       //     }
       // }

        $('.wwp-product-sku-clear').on('click', function() {
            let url = new URL(window.location.href);
            url.searchParams.delete('sku');
            window.location.href = url.toString();
        });

        $('#wwp-hide-add-to-cart').on('click', function(e) { 
            if ( $(this).is(':checked') ) {
                $('.wwp-hide-cart-on').css('display', 'table-row' );
                $('.wwp-hide-cart-custom_msg').css('display', 'table-row' );
            } else {
                $('.wwp-hide-cart-on').css('display', 'none' );
                $('.wwp-hide-cart-custom_msg').css('display','none');
            }
        });
        var selectedValue = $('input[name="options[wwp_hide_add_to_cart_on]"]:checked').val();

        // Based on the selected value, show the corresponding select box
        if ( selectedValue == 'specific_product' ) {
            $('.hide-select-product').show();
            $('.hide-select-category').hide();
            $('.hide-select-user_role').hide();
        } else if ( selectedValue == 'specific_product_cat' ) {
            $('.hide-select-product').hide();
            $('.hide-select-category').show();
            $('.hide-select-user_role').hide();
        } else if ( selectedValue == 'specific_user_roles' ) {
            $('.hide-select-product').hide();
            $('.hide-select-category').hide();
            $('.hide-select-user_role').show();
        }
      
        $(document).on('click', 'input[name="options[wwp_hide_add_to_cart_on]"]', function(e) {
            var val = $(this).val();
            if( 'specific_product' == val ) {
                $('.hide-select-product').show()
                $('.hide-select-category').hide()
                $('.hide-select-user_role').hide()
            } else if ( val == 'specific_product_cat') {
                $('.hide-select-product').hide()
                $('.hide-select-category').show()
                $('.hide-select-user_role').hide()
            } else if ( val == 'specific_user_roles') { 
                $('.hide-select-product').hide()
                $('.hide-select-category').hide()
                $('.hide-select-user_role').show()
            }

        });

        // Handle the 'Hide Price' checkbox click event
        $('#wwp-hide-price').on('click', function(e) {
            if ( $(this).is(':checked') ) {
                // Show the settings for "Hide Price"
                $('.wwp-hide-price-on').css('display', 'table-row');
                $('.wwp-hide-price-custom_msg').css('display', 'table-row');
            } else {
                // Hide the settings for "Hide Price"
                $('.wwp-hide-price-on').css('display', 'none');
                $('.wwp-hide-price-custom_msg').css('display', 'none');
            }
        });


        var selectedValue = $('input[name="options[wwp_hide_price_on]"]:checked').val();

        // Based on the selected value, show the corresponding select box
        if ( selectedValue == 'specific_product' ) {
            $('.hide-select-price-product').show();
            $('.hide-select-price-category').hide();
            $('.hide-select-price-user-role').hide();
        } else if ( selectedValue == 'specific_product_cat' ) {
            $('.hide-select-price-product').hide();
            $('.hide-select-price-category').show();
            $('.hide-select-price-user-role').hide();
        } else if ( selectedValue == 'specific_user_roles' ) {
            $('.hide-select-price-product').hide();
            $('.hide-select-price-category').hide();
            $('.hide-select-price-user-role').show();
        }
        
        // Handle the selection of options for "Hide Price on"
        $(document).on('click', 'input[name="options[wwp_hide_price_on]"]', function(e) {
            var val = $(this).val();
            if( 'specific_product' == val ) {
                $('.hide-select-price-product').show()
                $('.hide-select-price-category').hide()
                $('.hide-select-price-user-role').hide()
            } else if ( val == 'specific_product_cat') {
                $('.hide-select-price-product').hide()
                $('.hide-select-price-category').show()
                $('.hide-select-price-user-role').hide()
            } else if ( val == 'specific_user_roles') { 
                $('.hide-select-price-product').hide()
                $('.hide-select-price-category').hide()
                $('.hide-select-price-user-role').show()
            }

        });
        
        // Store the original list of all products
        var allProducts = $('.include-specific-product option').map(function() {
            return { id: $(this).val(), text: $(this).text() };
        }).get();

        function updateOptions() {
            var includeSelected = $('.include-specific-product').val() || [];
            var excludeSelected = $('.exclude-specific-product').val() || [];

            // Filter products that are NOT selected in the Exclude dropdown for the Include dropdown
            var availableForInclude = allProducts.filter(function(product) {
                return excludeSelected.indexOf(product.id) === -1;
            });

            // Filter products that are NOT selected in the Include dropdown for the Exclude dropdown
            var availableForExclude = allProducts.filter(function(product) {
                return includeSelected.indexOf(product.id) === -1;
            });

            // Update the Include select box
            var $includeSelect = $('.include-specific-product');
            $includeSelect.empty(); // Clear existing options
            availableForInclude.forEach(function(product) {
                $includeSelect.append(new Option(product.text, product.id, false, includeSelected.indexOf(product.id) !== -1));
            });

            // Update the Exclude select box
            var $excludeSelect = $('.exclude-specific-product');
            $excludeSelect.empty(); // Clear existing options
            availableForExclude.forEach(function(product) {
                $excludeSelect.append(new Option(product.text, product.id, false, excludeSelected.indexOf(product.id) !== -1));
            });

            // Re-trigger select2 to update with the new options
            $includeSelect.trigger('change.select2');
            $excludeSelect.trigger('change.select2');
        }

        // Trigger update when the Include dropdown changes
        $('.include-specific-product').on('change', function() {
            updateOptions();
        });

        // Trigger update when the Exclude dropdown changes
        $('.exclude-specific-product').on('change', function() {
            updateOptions();
        });

        // Trigger update when items are removed from exclude dropdown
        $('.exclude-specific-product').on('select2:unselect', function() {
            updateOptions();
        });

        // Initial call to populate options on page load
        updateOptions();


        function toggleRecipientField() {
            if ($('#wwp-wholesale-stop-admin-notification').is(':checked')) {
                $('#wwp_wholesale_admin_request_recipient').attr('required', true);
            } else {
                $('#wwp_wholesale_admin_request_recipient').attr('required', false);
            }
        }

        toggleRecipientField();
        
        // Toggle when 'Send Email Notification To Other Than Admins' checkbox changes
        $('#wwp-wholesale-send-notification-email-other').on('change', function() {
            toggleRecipientField();

            if ($(this).is(':checked')) {
                $('#wwp-wholesale-stop-admin-notification').prop('checked', false);
            }
        });

        // When 'Don't Send Notification Emails to Current Site Admin' is checked
        $('#wwp-wholesale-stop-admin-notification').on('change', function() {
            if ($(this).is(':checked')) {
                $('#wwp-wholesale-send-notification-email-other').prop('checked', false);
                toggleRecipientField(); // Update recipient field when admin checkbox is checked
            }
        });

        // On Page Load
        var selectedValue = $( 'input[name="include_products"]:checked' ).val();
        // console.log(selectedValue);
        toggleSpecificProductsSelect( selectedValue );

        // On Click Event
        $( '#include-products input[name="include_products"]' ).on( 'click', function() {
            var selectedValue = $( this ).val();
            toggleSpecificProductsSelect( selectedValue );
        });

        // Function to Show/Hide Select Element
        function toggleSpecificProductsSelect( val ) {
            var $selectElement = $( '.test' );
        
            if ( val === 'specific' ) {
                // console.log( $selectElement.closest( '.select2-container' ) );
                // $selectElement.addClass( 'wc-enhanced-select' );
                $selectElement.css( 'display', 'block' );
                $selectElement.css( 'margin', '8px 0' );
            } else {
                // $selectElement.removeClass( 'wc-enhanced-select' ); 
                $selectElement.css( 'display', 'none' );
            }
        }

        // Handle button click for adding the announcement
        $('#add_announcement_button').on('click', function(e) {
            e.preventDefault();
            var _this = $(this);
            _this.attr('disabled', 'disabled');
            var announcementContent = $('#add_announcement').val();
            var announcementSubject = $('input[name="announcement_subject"]').val();
            var announcementnonce = $('input[name="wwp_announcement"]').val();
            var id = $(this).data('id');
            _this.attr('disable', 'disable');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'add_wholesale_announcement',
                    announcement_content: announcementContent,
                    announcement_subject: announcementSubject,
                    announcement_nonce: announcementnonce,
                    post_id: id
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        var announcementKey = response.data.announcement_key;
                        $('.announcement-content').append(
                            '<div class="single-announcement" data-key="' + announcementKey + '">' +
                                '<strong>' + response.data.announcement_subject + '</strong>' +
                                '<p>' + response.data.announcement_content + '</p>' +
                                '<p class="description">' +
                                response.data.announcement_date + ' ' +
                                'by ' + response.data.announcement_author + 
                                ' | <a href="#" data-id="' + id + '" data-key="' + announcementKey + '" class="delete-announcement">Delete Announcement</a>' +
                                '</p>' +
                            '</div>' +
                            '<div class="wwp-remove-dashes"> --------------------------------- </div>'
                        );

                        $('#add_announcement').val('');
                        $('input[name="announcement_subject"]').val('');
                        _this.removeAttr('disabled');
                    } else {
                        alert(response.data.message);
                        _this.removeAttr('disabled');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    _this.removeAttr('disabled');
                }
            });
        });

        $(document).on("click", ".delete-announcement", function (e) {
            e.preventDefault();

            var _this = $(this);
            if (_this.hasClass('disabled')) {
                return;
            }

            _this.addClass('disabled');

            var postId = $(this).data("id");
            var announcementIndex = $(this).data("key"); 
            var announcementNonce = $('input[name="wwp_announcement"]').val();
            var $announcementItem = $(this).closest(".single-announcement");
            var $dashesItem = $announcementItem.next('.wwp-remove-dashes');
            $announcementItem.css('background-color', '#ffcccc');
            $dashesItem.css('background-color', '#ffcccc');

            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                action: "delete_wholesale_announcement_data",
                announcement_nonce: announcementNonce,
                post_id: postId,
                announcement_index: announcementIndex,
                },
                success: function (response) {
                    if (response.success) {
                        $announcementItem.fadeOut(500, function () {
                            $(this).remove();
                        });

                        $dashesItem.fadeOut(500, function () {
                            $(this).remove();
                        });
                        $(".single-announcement").each(function (index) {
                            $(this).data("key", index);
                        });

                        if ($(".single-announcement").length === 0) {
                            $(".announcement-content").html("<p>No announcements yet.</p>");
                        }
                    } else {
                        alert(response.data.message);
                        _this.removeClass('disabled');
                    }
                },
                error: function () {
                    alert("An error occurred while deleting the announcement data.");
                    _this.removeClass('disabled');
                },
            });
        });
    
        //  jQuery("form#wwp-global-settings,form#post,form#edittag,form#addtag").submit(function(event){
        //         $('.card .form-table tbody').each(function() {
        //             $(this).find('.wwp-price').removeClass('wwp-error');
        //             if ( $(this).find('.wwp-checbox').is(':checked') && $(this).find('.wwp-price').val() == '' ) {
        //                 $(this).find('.wwp-price').addClass('wwp-error').focus();
        //                 event.preventDefault();
        //                 return false;
        //             }
        //        }); 
        //     });
         
        jQuery("#wholesale_user_roleschecklist-pop input, #wholesale_user_roleschecklist input, .wholesale_user_roles-checklist input").each(
            function () {
                this.type="radio"}
        );

        // for general settings
        jQuery('body.wholesale_page_wwp_wholesale_settings ul.nav-tabs li a[data-toggle="tab"]').on('show.bs.tab', function(e) {
            localStorage.setItem('activeTabGeneral', jQuery(e.target).attr('href'));
        });
        var activeTab = localStorage.getItem('activeTabGeneral');
        if(activeTab){
            jQuery('body.wholesale_page_wwp_wholesale_settings  ul.nav-tabs li a[href="' + activeTab + '"]').tab('show');
        } else {
            jQuery('body.wholesale_page_wwp_wholesale_settings  ul.nav-tabs li a[href="#section1"]').tab('show');
        }

        // for notification settings
        jQuery('body.wholesale_page_wwp_wholesale_notifcations ul.nav-tabs li a[data-toggle="tab"]').on('show.bs.tab', function(e) {
            localStorage.setItem('activeTabNotify', jQuery(e.target).attr('href'));
        });
        var activeTabNotify = localStorage.getItem('activeTabNotify');
        if(activeTabNotify){
            jQuery('body.wholesale_page_wwp_wholesale_notifcations  ul.nav-tabs li a[href="' + activeTabNotify + '"]').tab('show');
        } else {
            jQuery('body.wholesale_page_wwp_wholesale_notifcations  ul.nav-tabs li a[href="#section_notification1"]').tab('show');
        }
       
        jQuery('#wholesale_user_roles-add-submit').on(
            'click',function () {
                setTimeout(
                    function () {
                        jQuery("#wholesale_user_roleschecklist-pop input, #wholesale_user_roleschecklist input, .wholesale_user_roles-checklist input").each(
                            function () {
                                this.type="radio"}
                        );
                    },1000
                );
            }
        );
        jQuery(document).on(
            'change', '#woocommerce-product-data #product-type', function () {

                var ptype = jQuery(this).val();
                var product_id = jQuery(document).find('input[name="product_id"]').val();
                var data = {
                    action : 'retrieve_wholesale_multiuser_pricing',
                    product_id: product_id,
                    ptype : ptype,
					security : wwpscript.ajax_nonce
                };
                jQuery(document).find('#wholesale-multiuser-pricing .wholesale_loader').show();
                jQuery.ajax(
                    {
                        type: 'POST',
                        url: wwpscript.ajaxurl,
                        dataType: 'html',
                        cache: false,
                        data: data,
                        success: function (response) {
                            jQuery(document).find('#wholesale-multiuser-pricing .wholesale_loader').hide();
                            jQuery(document).find('#wholesale-multiuser-pricing .wholesale_container').html(response);
                        }
                    }
                );
            }
        );
		jQuery('#woocommerce-product-data #product-type').trigger('change');
        jQuery(document).on(
            'click','#wholesale-pricing-pro-multiuser-move',function (e) {
                e.preventDefault();
                jQuery('html').delay(100).animate({scrollTop: jQuery('#wholesale-pricing-pro-multiuser').offset().top }, 1000);
            }
        );
        jQuery(document).on(
            'click','#wholesale_pricing_bulk_update', function (e) {
                e.preventDefault();
                var me = jQuery(this);
                var product_id = me.data('id');
                var data = {
                    action: 'save_single_wholesale_product',
                    product_id: product_id,
					security : wwpscript.ajax_nonce,
                    data : me.closest('#pannel-'+product_id).find(':input').serialize()
                };
                me.closest('#pannel-'+product_id).find('.wwp-loader').show();
                jQuery.ajax(
                    {
                        type: 'POST',
                        url: wwpscript.ajaxurl,
                        dataType: 'html',
                        cache: false,
                        data: data,
                        success: function (response) {
							
                            me.closest('#pannel-'+product_id).find('.wwp-loader').hide();
                            
                            jQuery('body').append(`<div class="wwp-custom-alert alert alert-success alert-dismissible fade" role="alert">${response}<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>`);
                            setTimeout(() => {
                                jQuery('body .wwp-custom-alert').addClass('in show');
                            }, 300);

                            setTimeout(() => {
                                jQuery('body .wwp-custom-alert').removeClass('in show').addClass('out');
                                
                            }, 4300);
                            
                            setTimeout(() => {
                                jQuery('body .wwp-custom-alert').remove();
                            }, 4800);
                            
                        }
                    }
                );
        
            }
        );
        jQuery(".flip").click(
            function () {
                var me =jQuery(this);
                var pannel = me.attr('id');
                jQuery("#pannel-"+pannel).slideToggle("slow");
                me.toggleClass('flipped');
            }
        );

        jQuery('#wwp_all_products').click(
            function () {
                if(jQuery(this).is(':checked')) {
                    jQuery('.wwp_selected_item').attr('checked',true);
                } else {
                    jQuery('.wwp_selected_item').attr('checked',false);
                }
            }
        );

        jQuery('.wwp_selected_item').click(
            function (e) {
                e.stopImmediatePropagation(); // STOP ACCORDION
            }
        );

        jQuery('.wwp-product-sku-search').on('click', function (e) {
            var sku = jQuery(this).data('sku');
            if ( ! sku ) {
                return;
            }
            var cat = jQuery('.wwp_prod_cat').val();  // Get selected category value
            var url = '&sku=' + sku;
            if ( cat ) {
                url = '&category=' + cat + '&sku=' + sku;
            }
            var location = wwpscript.admin_url + 'admin.php?page=wwp-bulk-wholesale-pricing' + url;
            window.open(location, '_self');
        });

        jQuery('.wwp_prod_cat').change(
            function () {
                //jQuery('#wwp_bulk_form').submit();
                var cat =jQuery(this).val();
                var sku = jQuery('.wwp-product-sku-search').data('sku') || jQuery('#wwp-product-sku').val();
                var url = '&category='+cat;
                if ( sku ) {
                    url = '&category='+cat+ '&sku=' + sku;
                }
                var location = wwpscript.admin_url + 'admin.php?page=wwp-bulk-wholesale-pricing' + url;
                window.open(location,'_self');
            }
        );

        jQuery('#wwp-product-sku').on('input', function () {
            var sku = jQuery(this).val();
            jQuery('.wwp-product-sku-search').attr('data-sku', sku);
        });

        jQuery('.view_only_wholesale').click(
            function () {
                if(jQuery(".view_only_wholesale").is(':checked')) {
                    var location = window.location.href + '&view_wholesale_items=1'
                    window.open(location,'_self');
                } else {
                    var location = window.location.href + '&view_wholesale_items=0'
                    window.open(location,'_self');
                }
            }
        );

		function check_multirole_enable(){
			if(jQuery('#multiple_wholesaler_role').is(':checked')) 
			{ 
				jQuery('#multiroledropdown').show();
			}else{
				jQuery('#multiroledropdown').hide();
			}
		}
        function order_notification_email(){ 
			if(jQuery('#emailuserrole').is(':checked')) 
			{ 
				jQuery('#select_role_wrap').show();
				jQuery('#select_email_custom_wrap').hide();
			}else{ 
				jQuery('#select_role_wrap').hide();
				jQuery('#select_email_custom_wrap').show();
			}
		}
		
		jQuery("#multiple_wholesaler_role,#single_wholesaler_role").click(function(){
		check_multirole_enable();
		});
		jQuery("#emailuserrole,#order_email_custom").click(function(){
            order_notification_email();
        });

		check_multirole_enable();
		order_notification_email();
		
		jQuery('#rejected_note').hide();
		function rejected_note() {
			if ( jQuery('#rejected').is(':checked') ) {
		 
				jQuery('#rejected_note').show();
			} else {
			
				jQuery('#rejected_note').hide();
			}
			
		}
		
		jQuery("#rejected,#active").click(function(){
		rejected_note();
		});		

		rejected_note();
		
		//jQuery('#multiroledropdown').show(); rejected_note
		
		jQuery(".role_password-wrap #role_password_btn").click(function(){
            jQuery(this).find('span.dashicons').toggleClass('dashicons-visibility');
            jQuery(this).find('span.dashicons').toggleClass('dashicons-hidden');
            var x = document.getElementById("role_password");
              if (x.type === "password") {
                x.type = "text";
              } else {
                 x.type = "password";
              }
          });
        jQuery( '#register_redirect_autocomplete' ).autocomplete({
            source: function(request, response) {
                jQuery.ajax({
                    dataType: 'jsonp',
                    url: ajaxurl,
                    data: {
                        action: 'register_redirect',
                        name: request.term
                      },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            minLength: 2,
            select: function (event, ui) {
                // Set selection
                jQuery('#register_redirect_autocomplete').val(ui.item.label); // display the selected text
                jQuery('#register_redirect').val(ui.item.value); // save selected id to input
                return false;
               }
        });
        jQuery("#register_redirect_autocomplete").keyup(function(){
            if (jQuery("#register_redirect_autocomplete").val() == '') {
                jQuery("#register_redirect").val('');
            }
        });
        
        jQuery( '#sample_product_id' ).autocomplete({
            source: function(request, response) {
                jQuery.ajax({
                    dataType: 'jsonp',
                    url: ajaxurl,
                    data: {
                        action: 'register_redirect',
                        name: request.term,
                        sample_product: 'sample_product'
                      },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            minLength: 2,
            select: function (event, ui) {
                // Set selection
                jQuery('#sample_product_id').val(ui.item.label); // display the selected text
                jQuery('#sample_product_id_hidden').val(ui.item.value); // save selected id to input
                return false;
            }
        });
        jQuery("#sample_product_id").keyup(function() {
            if (jQuery("#sample_product_id").val() == '') {
                jQuery("#sample_product_id_hidden").val('');
            }
        });
        
        jQuery('button[name="cart_total_discount_range"]').on('click', function() {
            var id = jQuery(this).data('id');
            updateTieredInputAttributes(id);
            if ( jQuery( '.cart-discount-on_' + id ).val() == 'cart_discount_quantity' ) {
                jQuery('#cart_total_discount_range'+id+'Label').text('Cart Quantity Discount Range');
            } else {
                jQuery('#cart_total_discount_range'+id+'Label').text('Cart Total Discount Range');
            }
            global_cart_discount_lable( jQuery( '.cart-discount-on_' + id ).val() );

        });
        // global_cart_discount_lable(jQuery('.cart-discount-on').val());
  
        jQuery(document).on("click",".tier_popup button.close,.tier_popup button.btn.btn-secondary",function() {
            jQuery(this).parents( ".tier_popup" ).fadeOut('fast');
        });

        jQuery(document).on( "keydown", ".tier_popup .bunch_row input", function(event){
            if (event.key === 'Enter') {
                event.preventDefault();
            }
        }); 
        
        jQuery(document).on( "keyup", ".tier_popup .bunch_row input", function(){
           validation_tire_pricing();
        }); 
        validation_tire_pricing();
        reCaptchaVersionFieldsToggle();
        jQuery( '.cart-discount-on' ).on( 'change', function( ) {
            var val = jQuery(this).val();
            var minlabel = $(this).closest('tr').next('tr').find('label[id^="wwp-min-cart-label"]');
            var discountlabel = $(this).closest('tr').next('tr').next('tr').find('label[id^="wwp-cart-discount-label"]');
            if ( 'cart_discount_quantity' == val ) {
                minlabel.text('Minimum Cart Quantity');
                discountlabel.text('Cart Quantity Discount Range');
            } else {
                minlabel.text('Minimum Cart Subtotal');
                discountlabel.text('Cart Total Discount Range');
            }
            global_cart_discount_lable( val );
        } );

        jQuery('#enable_store_access_cookie').on('click', function() {
            if (jQuery(this).is(':checked')) {
                jQuery('.wwp-cookie-interval').show();
            } else {
                jQuery('.wwp-cookie-interval').hide();
            }
        });

        jQuery(document).on('keyup change', '.cartstartqty, .cartmaxqty', function() {
            var id = jQuery(this).data('id');
            updateTieredInputAttributes(id);
        });
    }
);

function validation_tire_pricing(){
    
   jQuery('.tier_popup .bunch_row').each(function(e) {  
         
        tier_popup_class = jQuery(this).closest('.tier_popup').parents().attr('class')
        if ('cart_total_discount_range_wrap' == tier_popup_class) {
            return;
        }
         
        var inputs = jQuery(this).find('input[type=number]');

        min = parseInt( jQuery( inputs[0] ).val() );
        max = parseInt( jQuery( inputs[1] ).val() );
        price = jQuery( inputs[2] ).val();
        
        if ( jQuery( inputs[0] ).val() == '' && jQuery( inputs[1] ).val() == '' && price == '' ) {
            jQuery(this).find('.wwp_error_span').hide();
            jQuery(this).find('.form-control').removeClass('addborderred'); 
            return;
        } 
 
        if ( price == '' ) {
            jQuery(this).find('.wwp_tire_price').addClass('addborderred'); 
            jQuery(this).find('.price_error').show();
        } else {
            jQuery(this).find('.wwp_tire_price').removeClass('addborderred'); 
            jQuery(this).find('.price_error').hide();
        }
 
        if ( min > max ) {
            // jQuery(this).find('input[type=text]').addClass('addborderred'); 
            //jQuery( ".wwp_required_field" ).show();

            jQuery(this).find('.startingqty').addClass('addborderred'); 
            jQuery(this).find('.endingqty').addClass('addborderred'); 
            
            jQuery(this).find('.str_qty_error').text('Value must be less than ' + max);
            jQuery(this).find('.str_qty_error').show();
            
            //Ending Quantity Must Be Less Then Starting Quantity
            jQuery(this).find('.end_qty_error').text('Value must be greater than ' + min);
            jQuery(this).find('.end_qty_error').show();
            
        } else {
        
            jQuery(this).find('.startingqty').removeClass('addborderred'); 
            jQuery(this).find('.endingqty').removeClass('addborderred'); 
            
            jQuery(this).find('.str_qty_error').hide();
            jQuery(this).find('.end_qty_error').hide();

           // jQuery(this).find('input[type=text]').removeClass('addborderred'); 
           // jQuery( ".wwp_required_field" ).hide();
        }
        
        
        if ( isNaN(min) == true  ) {
            jQuery(this).find('.startingqty').addClass('addborderred'); 
            jQuery(this).find('.str_qty_error').text('Required quantity');
            jQuery(this).find('.str_qty_error').show();
        }
        if ( isNaN(max) == true  ) {
            jQuery(this).find('.endingqty').addClass('addborderred'); 
            jQuery(this).find('.end_qty_error').text('Required quantity');
            jQuery(this).find('.end_qty_error').show();
        }
        // if ( price == ''  ) {
        //     jQuery(this).find('.wwp_tire_price').addClass('addborderred'); 
        //     jQuery(this).find('.price_error').text('Required price');
        //     jQuery(this).find('.price_error').show();
        // }
        jQuery(".modal-content button[name='save-wwp_wholesale']").on("click", function (event) {

            var rows = jQuery(this).parents('.modal-content').find('.form-inline.append-data');
            var formValid = true; // Assume the form is valid initially

            rows.each(function(i, e) {
                var inputs = jQuery(e).find('input');
                var temp = [false, false, false];
                inputs.each(function(j, inputElement) {
                    if (inputElement.value.trim() === "") {
                        temp[j] = false;
                        formValid = false;
                    } else {
                        temp[j] = true;
                    }
                });
            });

            if (!formValid || min > max) {
                event.preventDefault(); 
            }
        });       
    });
}

function global_cart_discount_lable( val = 'cart_discount_amount' ) {
    jQuery('.cart_total_discount_range_wrap .bunch_row').each(function () {
        var inputs = jQuery(this).find("input[type=text], input[type=number]");
        if ( wwpscript.quantity_flag_cart_total_discount || 'cart_discount_quantity' == val ) {
            jQuery(inputs[0]).attr("placeholder", "Cart Minimum Quantity");
            // 2.7
            jQuery(inputs[1]).attr("placeholder", "Cart Maximum Quantity");
            jQuery(inputs[2]).attr("placeholder", "Cart Discount Value");
            jQuery(inputs[3]).attr("placeholder", "Cart Discount Label");
        } else {
            jQuery(inputs[0]).attr("placeholder", "Cart Minimum Amount");
            // 2.7
            jQuery(inputs[1]).attr("placeholder", "Cart Maximum Amount");
            jQuery(inputs[2]).attr("placeholder", "Cart Discount Value");
            jQuery(inputs[3]).attr("placeholder", "Cart Discount Label");
        }
    });

    // Labels
    // 2.7
    if ( wwpscript.quantity_flag_cart_total_discount || 'cart_discount_quantity' == val ) {
        jQuery('.cart_total_discount_range_wrap .lable_first').html('Cart Minimum Quantity');
        jQuery('.cart_total_discount_range_wrap .lable_secound').html('Cart Maximium Quantity');

    } else {
        jQuery('.cart_total_discount_range_wrap .lable_first').html('Cart Minimum Amount');
        jQuery('.cart_total_discount_range_wrap .lable_secound').html('Cart Maximium Amount');

    }

    jQuery('.cart_total_discount_range_wrap .lable_third').html('Cart Discount Value');
    jQuery('.cart_total_discount_range_wrap .lable_forth').html('Cart Discount Label');
}

function updateTieredInputAttributes(id) {
    var previousMax = null;
    jQuery(`.form-inline.append-data > .bunch_row[data-role="${id}"]`).not('#global_tier_pricing_Modal' + id + ' .bunch_row').each(function(index) {
        var $row = jQuery(this);
        var $minInput = $row.find('.cartstartqty');  // First input
        var $maxInput = $row.find('.cartmaxqty');    // Second input
        // Reset old min/max
        $minInput.removeAttr('min');
        $maxInput.removeAttr('min');

        if (index === 0) {
            $minInput.attr('min', 1);
        }

        // ✅ Set min of current .cartmaxqty = value of current .cartstartqty
        var startVal = parseInt($minInput.val());
        if (!isNaN(startVal)) {
            $maxInput.attr('min', startVal + 1);
        }

        // ✅ Set min of .cartstartqty in this row based on previous row's max
        if (index > 0 && previousMax !== null && !isNaN(previousMax)) {
            $minInput.attr('min', previousMax + 1);
        }

        // Update previousMax for next row
        var maxVal = parseInt($maxInput.val());
        if (!isNaN(maxVal)) {
            previousMax = maxVal;
        } else {
            previousMax = null;
        }
    });
}

function add_row_tier_price(this_instance, name, variation_slug = '') {
    var $type = ( name == 'wholesale_multi_user_cart_discount[tier_pricing]' ) ? 'text' : 'number';
    var minclass = name == 'wholesale_multi_user_cart_discount[tier_pricing]' ? 'cartstartqty' : '';
    role_id = this_instance.parents( ".bunch_row" ).data( "role" ); 
    rand_number = Math.random().toString(36).slice(-10);
    template = '';
    template += '<div class="bunch_row" data-role="'+role_id+'">';
    
    template += '<div class="col-md-4 wrapper_my_input">';
    template += '<input type="number" name="'+name+'['+role_id+']'+variation_slug+'['+rand_number+'][min]"  min="1" class="startingqty '+minclass+' form-control form-control-sm" data-id="'+role_id+'" placeholder="Starting Quantity"> ';
    template += '<span class="wwp_error_span str_qty_error" style="display:none" >Invalid Quantity</span>';
    template += '</div>';

    // 2.7
    if ( name == 'wholesale_multi_user_cart_discount[tier_pricing]' ) {
        template += '<div class="col-md-4 wrapper_my_input">';
        template += '<input type="number" name="'+name+'['+role_id+']'+variation_slug+'['+rand_number+'][cart_max]"  min="1" class="cartmaxqty form-control form-control-sm" placeholder="Cart Max Quantity"> ';
        template += '<span class="wwp_error_span cart_max_qty_error" style="display:none" >Invalid Quantity</span>';
        template += '</div>';
    }
    
    template += '<div class="col-md-4 wrapper_my_input">';
    template += '<input type="number" name="'+name+'['+role_id+']'+variation_slug+'['+rand_number+'][max]" min="1" class="endingqty form-control form-control-sm" placeholder="Ending Quantity"> ';
    template += '<span class="wwp_error_span end_qty_error" style="display:none" >Invalid Quantity</span>';
    template += '</div>';
    
    template += '<div class="col-md-4 wrapper_my_input">';
    template += '<input type="'+$type+'" name="'+name+'['+role_id+']'+variation_slug+'['+rand_number+'][price]" class="wwp_tire_price form-control form-control-sm" placeholder="Wholesale Price"  step=".01" min=".01"> ';
    template += '<span class="wwp_error_span price_error" style="display:none" >Required Price</span>';
    template += '</div>';
    
    template += '<div class="icons">';
    template += '<span class="dashicons dashicons-trash"></span> ';
    
    // if ('' == variation_slug) {
    //     variation_slug = "''";
    // }
   // name = "'" + name + "'";
    //template += '<span class="dashicons dashicons-plus-alt" onclick="add_row_tier_price(jQuery(this),'+name+', '+variation_slug+')"></span> ';
    template += `<span class="dashicons dashicons-plus-alt" onclick="add_row_tier_price(jQuery(this), '${name}', '${variation_slug}')"></span>`;
    template += '</div>';
    template += '</div>';
    this_instance.parents( ".bunch_row" ).after( template ).hide().slideDown();

    global_cart_discount_lable(jQuery('.cart-discount-on_' + role_id ).val());
//	});
}
function copytoclipboard() {
    var shortcode = jQuery('.map_shortcode_callback').find('p input');
    shortcode[0].select();
    document.execCommand("Copy");
}
function generatePassword() {
    var length = 8,
    charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
    retVal = "";
    for (var i = 0, n = charset.length; i < length; ++i) {
    retVal += charset.charAt(Math.floor(Math.random() * n));
}
jQuery("#role_password").val(retVal);  
}

function reCaptchaVersionFieldsToggle() {
    jQuery('input[name="registrations[recaptcha_version]"]').on('change', function() {
        if ( 'v2' == jQuery(this).val() ) {
            jQuery('tr.v2').fadeIn();
            jQuery('tr.v3').fadeOut();
        } else {
            jQuery('tr.v2').fadeOut();
            jQuery('tr.v3').fadeIn();
        }
    });
    if ( 'v2' == jQuery('input[name="registrations[recaptcha_version]"]:checked').val() ) {
        jQuery('tr.v2').fadeIn();
        jQuery('tr.v3').fadeOut();
    } else {
        jQuery('tr.v2').fadeOut();
        jQuery('tr.v3').fadeIn();
    }
}

jQuery(document).ready(function ($) {
    var $checkbox = $('#advance_registration_form');
    var originalChecked = $checkbox.is(':checked');
  
    $checkbox.on('change', function (e) {
      if ($checkbox.is(':checked')) {
        e.preventDefault();
        $checkbox.prop('checked', originalChecked); // revert
        $('#advanceRegistrationPopup').fadeIn();
      }
    });
  
    // When user allows
    $('.popup-allow').on('click', function (e) {
      e.preventDefault(); // prevent form submission
      $checkbox.prop('checked', true);
      $('#advanceRegistrationPopup').fadeOut();
    });
  
    // When user declines
    $('.popup-decline').on('click', function (e) {
      e.preventDefault(); // prevent form submission
      $checkbox.prop('checked', false);
      $('#advanceRegistrationPopup').fadeOut();
    });
  });
  