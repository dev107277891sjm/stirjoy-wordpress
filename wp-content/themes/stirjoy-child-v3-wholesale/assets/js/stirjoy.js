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

        /**
         * Delivery Calendar Navigation (AJAX)
         */
        function initCalendarNavigation() {
            // Use more specific selector to ensure we only catch calendar buttons
            $(document).on('click', '.delivery-calendar .calendar-nav-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('Calendar button clicked');
                
                var $button = $(this);
                var $calendar = $button.closest('.delivery-calendar');
                
                if (!$calendar.length) {
                    console.error('Calendar container not found');
                    return;
                }
                
                // Try both .data() and .attr() methods
                var month = $button.data('month') || parseInt($button.attr('data-month'), 10);
                var year = $button.data('year') || parseInt($button.attr('data-year'), 10);
                
                if (!month || !year || isNaN(month) || isNaN(year)) {
                    console.error('Missing or invalid month/year data', {
                        month: month,
                        year: year,
                        button: $button,
                        dataMonth: $button.data('month'),
                        attrMonth: $button.attr('data-month'),
                        dataYear: $button.data('year'),
                        attrYear: $button.attr('data-year')
                    });
                    return;
                }
                
                // Check if stirjoyData is available
                if (typeof stirjoyData === 'undefined') {
                    console.error('stirjoyData is not defined');
                    alert('Calendar navigation is not available. Please refresh the page.');
                    return;
                }
                
                // Disable buttons during loading
                $calendar.find('.calendar-nav-btn').prop('disabled', true).css('opacity', '0.6');
                
                $.ajax({
                    url: stirjoyData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'stirjoy_get_calendar_month',
                        nonce: stirjoyData.nonce,
                        month: month,
                        year: year
                    },
                    success: function(response) {
                        if (response && response.success && response.data && response.data.html) {
                            // Wrap HTML in a temporary container to parse it
                            var $temp = $('<div>').html(response.data.html);
                            
                            // Update calendar navigation
                            var $navHtml = $temp.find('.calendar-navigation');
                            if ($navHtml.length) {
                                $calendar.find('.calendar-navigation').html($navHtml.html());
                            }
                            
                            // Update calendar grid
                            var $gridHtml = $temp.find('.calendar-grid');
                            if ($gridHtml.length) {
                                $calendar.find('.calendar-grid').html($gridHtml.html());
                            }
                            
                            // Update data attributes
                            if (response.data.month && response.data.year) {
                                $calendar.attr('data-current-month', response.data.month);
                                $calendar.attr('data-current-year', response.data.year);
                            }
                        } else {
                            console.error('Invalid response:', response);
                            alert('An error occurred while loading the calendar. Please try again.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        if (xhr.responseText) {
                            console.error('Response:', xhr.responseText);
                        }
                        alert('An error occurred while loading the calendar. Please try again.');
                    },
                    complete: function() {
                        // Re-enable buttons
                        $calendar.find('.calendar-nav-btn').prop('disabled', false).css('opacity', '1');
                    }
                });
            });
        }
        
        // Initialize calendar navigation
        initCalendarNavigation();

        /**
         * Delivery Calendar Day Click Handler - Show Modal
         */
        $(document).on('click', '.delivery-calendar .calendar-day:not(.empty)', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $day = $(this);
            var dateStr = $day.data('date');
            var formattedDate = $day.data('formatted-date');
            
            if (!dateStr || !formattedDate) {
                return;
            }
            
            // Show modal
            var $modal = $('#delivery-options-modal');
            $modal.find('#modal-selected-date').text(formattedDate);
            $modal.find('.delivery-option-card').data('selected-date', dateStr);
            $modal.fadeIn(300);
        });

        /**
         * Close Modal Handlers
         */
        $(document).on('click', '.delivery-options-modal-overlay, .delivery-options-cancel-btn', function(e) {
            e.preventDefault();
            $('#delivery-options-modal').fadeOut(300);
        });

        /**
         * Prevent modal content clicks from closing modal
         */
        $(document).on('click', '.delivery-options-modal-content', function(e) {
            e.stopPropagation();
        });

        /**
         * Delivery Option Selection
         */
        $(document).on('click', '.delivery-option-card', function(e) {
            e.preventDefault();
            var $card = $(this);
            var action = $card.data('action');
            var selectedDate = $card.data('selected-date');
            
            if (!action || !selectedDate) {
                return;
            }
            
            // Handle the action (move box or order extra box)
            console.log('Selected action:', action, 'for date:', selectedDate);
            
            // TODO: Implement AJAX call to handle the action
            // For now, just show an alert
            if (action === 'move-box') {
                alert('Moving monthly box to ' + selectedDate);
            } else if (action === 'extra-box') {
                alert('Ordering extra box for ' + selectedDate);
            }
            
            // Close modal
            $('#delivery-options-modal').fadeOut(300);
        });

        /**
         * ========================================
         * Customize Your Box - Shop Page
         * ========================================
         */
        
        /**
         * Category Tab Filtering
         */
        $('.category-tab').on('click', function(e) {
            e.preventDefault();
            
            var $tab = $(this);
            var category = $tab.data('category');
            
            // Update active tab
            $('.category-tab').removeClass('active');
            $tab.addClass('active');
            
            // Filter sections
            if (category === 'all') {
                $('.meal-category-section').show();
            } else {
                $('.meal-category-section').hide();
                $('.meal-category-section[data-category="' + category + '"]').show();
            }
            
            // Reset search
            $('#meal-search').val('');
            $('.meal-product-card').show();
        });
        
        /**
         * Real-time Search Filtering
         */
        $('#meal-search').on('input', function() {
            var searchTerm = $(this).val().toLowerCase().trim();
            
            if (searchTerm === '') {
                $('.meal-product-card').show();
                return;
            }
            
            $('.meal-product-card').each(function() {
                var $card = $(this);
                var searchText = $card.data('search-text') || '';
                
                if (searchText.indexOf(searchTerm) !== -1) {
                    $card.show();
                } else {
                    $card.hide();
                }
            });
        });
        
        /**
         * Add to Cart Button
         */
        $(document).on('click', '.add-to-cart-btn', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var productId = $button.data('product-id');
            var $card = $button.closest('.meal-product-card');
            
            // Disable button during request
            $button.prop('disabled', true).addClass('loading');
            
            $.ajax({
                url: stirjoyData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'stirjoy_add_to_cart',
                    product_id: productId,
                    quantity: 1,
                    nonce: stirjoyData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update button to Remove
                        $button.replaceWith(
                            '<button type="button" class="remove-from-cart-btn" data-product-id="' + productId + '">- Remove</button>'
                        );
                        
                        // Update card data
                        $card.attr('data-in-cart', '1');
                        
                        // Update Your Box header
                        updateYourBoxHeader();
                    } else {
                        alert(response.data.message || 'Error adding to cart');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                },
                complete: function() {
                    $button.prop('disabled', false).removeClass('loading');
                }
            });
        });
        
        /**
         * Remove from Cart Button
         */
        $(document).on('click', '.remove-from-cart-btn', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var productId = $button.data('product-id');
            var $card = $button.closest('.meal-product-card');
            
            // Disable button during request
            $button.prop('disabled', true).addClass('loading');
            
            $.ajax({
                url: stirjoyData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'stirjoy_remove_from_cart',
                    product_id: productId,
                    nonce: stirjoyData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update button to Add
                        $button.replaceWith(
                            '<button type="button" class="add-to-cart-btn" data-product-id="' + productId + '">+ Add</button>'
                        );
                        
                        // Update card data
                        $card.attr('data-in-cart', '0');
                        
                        // Update Your Box header
                        updateYourBoxHeader();
                    } else {
                        alert(response.data.message || 'Error removing from cart');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                },
                complete: function() {
                    $button.prop('disabled', false).removeClass('loading');
                }
            });
        });
        
        /**
         * Update Your Box Header Display
         */
        function updateYourBoxHeader() {
            $.ajax({
                url: stirjoyData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'stirjoy_get_cart_info',
                    nonce: stirjoyData.nonce
                },
                success: function(response) {
                    if (response.success && response.data) {
                        $('.your-box-count').text('(' + response.data.count + ')');
                        $('.your-box-total').text(response.data.total);
                    }
                }
            });
        }

    });
})(jQuery);
