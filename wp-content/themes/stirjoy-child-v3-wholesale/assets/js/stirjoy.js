/**
 * Stirjoy Child Theme JavaScript
 *
 * @package Stirjoy_Child
 * @since 1.0.0
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        
        /**
         * Confirm Box Button
         */
        $(document).on('click', '#stirjoy-confirm-box', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            $button.addClass('loading');
            
            $.ajax({
                url: stirjoyData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'stirjoy_confirm_box',
                    nonce: stirjoyData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Reload page to show confirmed state
                        location.reload();
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                },
                complete: function() {
                    $button.removeClass('loading');
                }
            });
        });
        
        /**
         * Modify Selection Button
         */
        $(document).on('click', '#stirjoy-modify-selection', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            $button.addClass('loading');
            
            $.ajax({
                url: stirjoyData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'stirjoy_modify_selection',
                    nonce: stirjoyData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Reload page to show unconfirmed state
                        location.reload();
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                },
                complete: function() {
                    $button.removeClass('loading');
                }
            });
        });
        
        /**
         * Update cart sidebar when items are added/removed
         */
        $(document.body).on('added_to_cart removed_from_cart', function() {
            // Reload cart sidebar
            //updateCartSidebar();
        });
        
        /**
         * Update cart sidebar via AJAX
         */
        function updateCartSidebar() {
            // This will be implemented when we add the cart sidebar template
            // For now, just reload the page
            location.reload();
        }

        function updateFreeShippingAndGiftBar() {
            fetch('/wp-json/wc/store/v1/cart')
                .then(response => response.json())
                .then(cart => {
                    const subtotal = cart.totals.total_items / 100;
                    const freeShippingThreshold = $(".free-shipping-bar").attr('data-threshold');
                    var rest = freeShippingThreshold - subtotal;
                    var caption2 = 'Complete!';
                    var width = 100;
                    if(rest > 0) {
                        caption2 = '$' + rest + ' to go';
                        width = (subtotal / freeShippingThreshold) * 100;
                    }
                    
                    $(".free-shipping-bar .caption2").text(caption2);
                    $(".free-shipping-bar .bar > div").width(width + '%');

                    const freeGiftThreshold = $(".free-gift-bar").attr('data-threshold');
                    rest = freeGiftThreshold - subtotal;
                    caption2 = 'Complete!';
                    width = 100;
                    if(rest > 0) {
                        caption2 = '$' + rest + ' to go';
                        width = (subtotal / freeGiftThreshold) * 100;
                    }
                    
                    $(".free-gift-bar .caption2").text(caption2);
                    $(".free-gift-bar .bar > div").width(width + '%');
                    
                })
                .catch(error => console.error('Error:', error));
        }

        $('body').on( 'added_to_cart', function(){
            if ($("body").hasClass("thecrate_fixed_sidebar_cart_on")) {
            // if( $( '.fixed-sidebar-menu-minicart' ).length == 0) {
                //$('.fixed-sidebar-menu-minicart').toggleClass('open');
                $('.fixed-sidebar-menu-minicart').addClass('open');
                //$('.fixed-sidebar-menu-overlay').addClass('visible');
                //$('body').addClass('overflow-disabled');
            }

            $(".widget_shopping_cart .widgettitle").text('Your Box (' + $(".header-nav-actions .cart-contents span").text() + ' meals selected)')

            updateFreeShippingAndGiftBar();
        });

        $('body').on( 'removed_from_cart', function(){
            $(".widget_shopping_cart .widgettitle").text('Your Box (' + $(".header-nav-actions .cart-contents span").text() + ' meals selected)')

            updateFreeShippingAndGiftBar();
        });

        setTimeout(function(){
            $('.qty-block .quantity input[type="number"]').each(function(){
                var productId = $(this).parent().parent().parent().find(".add_to_cart_button").attr('data-product_id');
                var $miniCartItem = $('.mini_cart_item [data-product_id="' + productId + '"]');
                $(this).val($miniCartItem.length).change();
            });
        }, 500);
                
        // Minus button
        $(document).on('click', '.qty-block .quantity button.minus', function(e) {
            e.preventDefault();

            var $input = $(this).parent().find('input[type="number"]');
            var currentVal = parseInt($input.val()) || 0;
            var minVal = $input.attr('min') ? parseInt($input.attr('min')) : 0;
            
            if (currentVal > minVal) {
                $input.val(currentVal - 1).change();
                
                var productId = $(this).parent().parent().parent().find(".add_to_cart_button").attr('data-product_id');
                var $miniCartItem = $('.mini_cart_item [data-product_id="' + productId + '"]');
                if($miniCartItem.length > 0) {
                    $miniCartItem.first().trigger('click');

                    if ($("body").hasClass("thecrate_fixed_sidebar_cart_on")) {
                        $('.fixed-sidebar-menu-minicart').addClass('open');
                        //$('.fixed-sidebar-menu-overlay').addClass('visible');
                        //$('body').addClass('overflow-disabled');
                    }
                }
            }
        });
        
        // Plus button
        $(document).on('click', '.qty-block .quantity button.plus', function(e) {
            e.preventDefault();

            var $input = $(this).parent().find('input[type="number"]');
            var currentVal = parseInt($input.val()) || 0;
            var maxVal = $input.attr('max') ? parseInt($input.attr('max')) : '';
            
            if (maxVal === '' || currentVal < maxVal) {
                $input.val(currentVal + 1).change();

                $(this).parent().parent().parent().find(".add_to_cart_button").trigger('click');
            }
        });

        // Cart Item Rmove Icon
        $(document).on('click', '.mini_cart_item a.remove', function(e) {
            e.preventDefault();

            var productId = $(this).attr('data-product_id');
            var $miniCartItem = $('.mini_cart_item [data-product_id="' + productId + '"]');
            var $input = $('.add_to_cart_button[data-product_id="' + productId + '"]').parent().find('input[type="number"]');
            $input.val($miniCartItem.length - 1).change();
        });

        function updateSubscriptionStatus(subscription_id, action) {
            $.ajax({
                url: '/wp-json/wsp-route/v1/wsp-update-subscription/' + subscription_id,
                type: 'PUT',
                data: {
                    action: action,
                    consumer_secret: 'wps_a50a76fd27b5076c5807b7469594a721a2d00dc7'
                },
                beforeSend: function() {
                    // Show loading spinner
                    //$('#result').html('Loading...');
                },
                success: function(response) {
                    if (response.data.status == 200) {
                        switch(action) {
                            case 'pause':
                                $(".subscription-status .icon-block span").text('Paused');
                                $(".subscription-status .icon-block span").css('background', '#e67e22');
                                $(".pause-subscription").hide();
                                $(".reactivate-subscription").show();

                                alert('Current subscription was paused successfully.');
                                break;
                            case 'reactivate':
                                $(".subscription-status .icon-block span").text('Active');
                                $(".subscription-status .icon-block span").css('background', '#2d5a27');
                                $(".reactivate-subscription").hide();
                                $(".pause-subscription").show();

                                alert('Current subscription was reactivated successfully.');
                                break;
                            case 'cancel':
                                $(".subscription-status .icon-block span").text('Cancelled');
                                $(".subscription-status .icon-block span").css('background', '#c10007');
                                $(".pause-subscription").hide();
                                $(".cancel-subscription").hide();

                                alert('Current subscription was cancelled successfully.');
                                break;
                            default:
                        }
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    //$('#result').html('AJAX Error: ' + error);
                }
            });
        }

        $(document).on('click', '.pause-subscription', function(e) {
            e.preventDefault();

            let subscription_id = $(this).data('subscription-id');
            updateSubscriptionStatus(subscription_id, 'pause');
        });

        $(document).on('click', '.reactivate-subscription', function(e) {
            e.preventDefault();

            let subscription_id = $(this).data('subscription-id');
            updateSubscriptionStatus(subscription_id, 'reactivate');
        });

        $(document).on('click', '.cancel-subscription', function(e) {
            e.preventDefault();

            let subscription_id = $(this).data('subscription-id');
            updateSubscriptionStatus(subscription_id, 'cancel');
        });

        $(document).on('click', '.customer-info .edit-icon', function(e) {
            e.preventDefault();

            $(".customer-info .customer-info-text").hide();
            $(".customer-info .customer-info-form").show();
        });

        $(document).on('click', '.customer-info-save', function(e) {
            e.preventDefault();

            $.ajax({
                url: stirjoyData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'update_customer_info',
                    nonce: stirjoyData.nonce,
                    customer_name: $("#customer-name").val(),
                    customer_email: $("#customer-email").val(),
                    customer_phone: $("#customer-phone").val()
                },
                success: function(response) {
                    if(response.data.success == 'ok') {
                        $(".customer-info-text .customer-name span").text($("#customer-name").val());
                        $(".customer-info-text .customer-email span").text($("#customer-email").val());
                        $(".customer-info-text .customer-phone span").text($("#customer-phone").val());

                        $(".customer-info .customer-info-text").show();
                        $(".customer-info .customer-info-form").hide();
                    }
                },
                error: function(err) {
                    alert('Error: ' + err);
                }
            });
        });

        $(document).on('click', '.customer-info .customer-info-cancel', function(e) {
            e.preventDefault();

            $(".customer-info .customer-info-text").show();
            $(".customer-info .customer-info-form").hide();
        });

        setTimeout(function(){
            $(".postid-8480 .fixed-sidebar-menu.fixed-sidebar-menu-minicart").addClass('open');
        }, 500);

    });
})(jQuery);
