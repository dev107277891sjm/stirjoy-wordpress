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
            
            // Check if user is logged in
            if (!stirjoyData.isLoggedIn) {
                var message = 'Please log in to confirm your box.';
                if (confirm(message + '\n\nWould you like to go to the login page?')) {
                    window.location.href = stirjoyData.loginUrl;
                }
                return false;
            }
            
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
                    } else {
                        // Check if login is required
                        if (response.data && response.data.login_required) {
                            if (confirm(response.data.message + '\n\nWould you like to go to the login page?')) {
                                window.location.href = response.data.login_url || stirjoyData.loginUrl;
                            }
                        } else {
                            alert(response.data.message || 'An error occurred. Please try again.');
                        }
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
            
            // Check if user is logged in
            if (!stirjoyData.isLoggedIn) {
                var message = 'Please log in to modify your selection.';
                if (confirm(message + '\n\nWould you like to go to the login page?')) {
                    window.location.href = stirjoyData.loginUrl;
                }
                return false;
            }
            
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
                    } else {
                        // Check if login is required
                        if (response.data && response.data.login_required) {
                            if (confirm(response.data.message + '\n\nWould you like to go to the login page?')) {
                                window.location.href = response.data.login_url || stirjoyData.loginUrl;
                            }
                        } else {
                            alert(response.data.message || 'An error occurred. Please try again.');
                        }
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

        /**
         * Update Cart Bar Progress Bars in Shop Page Header
         */
        function updateCartBarProgressBars(subtotal) {
            console.log('Updating cart bar progress with subtotal:', subtotal);
            
            const shippingThreshold = 80;
            const giftThreshold = 120;
            
            // Calculate shipping progress
            var shippingProgress = Math.min(100, (subtotal / shippingThreshold) * 100);
            console.log('Shipping progress:', shippingProgress + '%');
            $('.shipping-progress').css('width', shippingProgress + '%');
            
            // Calculate gift progress
            var giftProgress = Math.min(100, (subtotal / giftThreshold) * 100);
            console.log('Gift progress:', giftProgress + '%');
            $('.gift-progress').css('width', giftProgress + '%');
            
            // Log if elements were found
            console.log('Shipping elements found:', $('.shipping-progress').length);
            console.log('Gift elements found:', $('.gift-progress').length);
        }

        function updateFreeShippingAndGiftBar() {
            // Use existing AJAX handler instead of WooCommerce Store API
            $.ajax({
                url: stirjoyData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'stirjoy_get_cart_info',
                    nonce: stirjoyData.nonce
                },
                success: function(response) {
                    if (response.success && response.data) {
                        // Get subtotal from response (already a number, not in cents)
                        const subtotal = parseFloat(response.data.cart_subtotal_numeric) || 0;
                        
                        // Update free shipping bar
                        const freeShippingThreshold = parseFloat($(".free-shipping-bar").attr('data-threshold')) || 80;
                        var rest = freeShippingThreshold - subtotal;
                        var caption2 = 'Complete!';
                        var width = 100;
                        if(rest > 0) {
                            caption2 = '$' + rest.toFixed(2) + ' to go';
                            width = Math.min(100, (subtotal / freeShippingThreshold) * 100);
                        }
                        
                        $(".free-shipping-bar .caption2").text(caption2);
                        $(".free-shipping-bar .bar > div").css('width', width + '%');

                        // Update free gift bar
                        const freeGiftThreshold = parseFloat($(".free-gift-bar").attr('data-threshold')) || 120;
                        rest = freeGiftThreshold - subtotal;
                        caption2 = 'Complete!';
                        width = 100;
                        if(rest > 0) {
                            caption2 = '$' + rest.toFixed(2) + ' to go';
                            width = Math.min(100, (subtotal / freeGiftThreshold) * 100);
                        }
                        
                        $(".free-gift-bar .caption2").text(caption2);
                        $(".free-gift-bar .bar > div").css('width', width + '%');
                        
                        // Update cart bar progress bars (Shop page header)
                        updateCartBarProgressBars(subtotal);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error updating free shipping and gift bar:', error);
                }
            });
        }

        $('body').on( 'added_to_cart', function(){
            if ($("body").hasClass("thecrate_fixed_sidebar_cart_on")) {
            // if( $( '.fixed-sidebar-menu-minicart' ).length == 0) {
                //$('.fixed-sidebar-menu-minicart').toggleClass('open');
                $('.fixed-sidebar-menu-minicart').addClass('open');
                //$('.fixed-sidebar-menu-overlay').addClass('visible');
                //$('body').addClass('overflow-disabled');
            }

            // Use updateYourBoxHeader() to get accurate cart count from server
            // This ensures we always have the correct count instead of reading stale data
            updateYourBoxHeader();
        });

        $('body').on( 'removed_from_cart', function(){
            // Use updateYourBoxHeader() to get accurate cart count from server
            // This ensures we always have the correct count instead of reading stale data
            updateYourBoxHeader();
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

        // Cart Item Remove Icon
        // Allows both logged-in and non-logged-in users to remove products from cart
        $(document).on('click', '.mini_cart_item a.remove', function(e) {
            e.preventDefault();

            // Check if another operation is in progress
            if (isCartOperationInProgress) {
                return false;
            }
            
            var $button = $(this);
            var productId = $button.attr('data-product_id');
            var cartItemKey = $button.attr('data-cart_item_key');
            var $cartItem = $button.closest('.mini_cart_item');
            
            // Lock cart operations
            isCartOperationInProgress = true;
            $('.add-to-cart-btn, .remove-from-cart-btn, .mini_cart_item a.remove').css('pointer-events', 'none').css('opacity', '0.6');
            
            // Immediately remove item for instant feedback
            $cartItem.fadeOut(150, function() {
                $(this).remove();
                
                // Check if cart is empty
                if ($('.mini_cart_item').length === 0) {
                    $('.mini-cart-left-block .woocommerce-mini-cart').html(
                        '<p class="woocommerce-mini-cart__empty-message">No products in the cart.</p>'
                    );
                }
            });
            
            // Update Shop page card state immediately (if on Shop page)
            var $shopCard = $('.meal-product-card[data-product-id="' + productId + '"]');
            if ($shopCard.length > 0) {
                $shopCard.attr('data-in-cart', '0');
                $shopCard.find('.remove-from-cart-btn').replaceWith(
                    '<button type="button" class="add-to-cart-btn" data-product-id="' + productId + '">+ Add</button>'
                );
            }
            
            // Call AJAX to remove from cart in background
            $.ajax({
                url: stirjoyData.ajaxUrl,
                type: 'POST',
                timeout: 5000,
                data: {
                    action: 'stirjoy_remove_from_cart',
                    nonce: stirjoyData.nonce,
                    product_id: productId
                },
                success: function(response) {
                    if (response.success) {
                        console.log('Cart sidebar remove success:', response.data);
                        
                        // Update cart bar progress bars IMMEDIATELY with response data
                        if (response.data.cart_subtotal_numeric !== undefined && response.data.cart_subtotal_numeric !== null) {
                            updateCartBarProgressBars(response.data.cart_subtotal_numeric);
                        }
                        
                        // Update all cart displays and shop page buttons via updateYourBoxHeader
                        updateYourBoxHeader();
                        
                        // Trigger removed_from_cart event
                        $('body').trigger('removed_from_cart');
                    } else {
                        // On error, show the item again
                        $cartItem.fadeIn(150);
                        if ($shopCard.length > 0) {
                            $shopCard.attr('data-in-cart', '1');
                            $shopCard.find('.add-to-cart-btn').replaceWith(
                                '<button type="button" class="remove-from-cart-btn" data-product-id="' + productId + '">- Remove</button>'
                            );
                        }
                        
                        // Check if login is required
                        if (response.data && response.data.login_required) {
                            if (confirm(response.data.message + '\n\nWould you like to go to the login page?')) {
                                window.location.href = response.data.login_url || stirjoyData.loginUrl;
                            }
                        } else {
                            alert(response.data.message || 'Error removing product from cart');
                        }
                    }
                    
                    // Unlock cart operations
                    isCartOperationInProgress = false;
                    $('.add-to-cart-btn, .remove-from-cart-btn, .mini_cart_item a.remove').css('pointer-events', 'auto').css('opacity', '1');
                },
                error: function() {
                    // On error, show the item again
                    $cartItem.fadeIn(150);
                    if ($shopCard.length > 0) {
                        $shopCard.attr('data-in-cart', '1');
                        $shopCard.find('.add-to-cart-btn').replaceWith(
                            '<button type="button" class="remove-from-cart-btn" data-product-id="' + productId + '">- Remove</button>'
                        );
                    }
                    alert('An error occurred. Please try again.');
                    
                    // Unlock cart operations
                    isCartOperationInProgress = false;
                    $('.add-to-cart-btn, .remove-from-cart-btn, .mini_cart_item a.remove').css('pointer-events', 'auto').css('opacity', '1');
                }
            });
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
        
        // Global flag to prevent simultaneous cart operations
        var isCartOperationInProgress = false;
        
        /**
         * Toggle Cart Sidebar from Shop Page
         * Allows both logged-in and non-logged-in users to view cart
         */
        $(document).on('click', '.toggle-cart-sidebar', function(e) {
            e.preventDefault();
            
            var $cartSidebar = $('.fixed-sidebar-menu-minicart');
            var $yourBoxHeader = $('.your-box-header');
            
            console.log('Toggle cart sidebar clicked');
            console.log('Cart sidebar found:', $cartSidebar.length);
            console.log('Your box header found:', $yourBoxHeader.length);
            
            if ($cartSidebar.length === 0) {
                console.error('Cart sidebar element not found!');
                return;
            }
            
            var isOpening = !$cartSidebar.hasClass('open');
            
            $cartSidebar.toggleClass('open');
            $yourBoxHeader.toggleClass('cart-open');
            
            // Update cart count when opening the sidebar to ensure accuracy
            if (isOpening) {
                updateYourBoxHeader();
            }
            
            console.log('Cart sidebar open class:', $cartSidebar.hasClass('open'));
        });
        
        /**
         * Close Cart Sidebar with X Button
         */
        $(document).on('click', '.cart-box-close-button', function(e) {
            e.preventDefault();
            $('.fixed-sidebar-menu-minicart').removeClass('open');
            $('.your-box-header').removeClass('cart-open');
        });
        
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
         * Allows both logged-in and non-logged-in users to add products to cart
         */
        $(document).on('click', '.add-to-cart-btn', function(e) {
            e.preventDefault();
            
            // Check if another operation is in progress
            if (isCartOperationInProgress) {
                return false;
            }
            
            var $button = $(this);
            var productId = $button.data('product-id');
            var $card = $button.closest('.meal-product-card');
            
            // Check if product is already in cart (client-side check)
            if ($card.attr('data-in-cart') === '1') {
                alert('This product is already in your cart. Each product can only be added once.');
                return false;
            }
            
            // Lock cart operations
            isCartOperationInProgress = true;
            $('.add-to-cart-btn, .remove-from-cart-btn, .mini_cart_item a.remove').css('pointer-events', 'none').css('opacity', '0.6');
            
            // Immediately update UI for instant feedback
            $button.replaceWith(
                '<button type="button" class="remove-from-cart-btn" data-product-id="' + productId + '">- Remove</button>'
            );
            $card.attr('data-in-cart', '1');
            
            // Send AJAX request in background
            $.ajax({
                url: stirjoyData.ajaxUrl,
                type: 'POST',
                timeout: 5000, // 5 second timeout
                data: {
                    action: 'stirjoy_add_to_cart',
                    product_id: productId,
                    quantity: 1,
                    nonce: stirjoyData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update Your Box header
                        updateYourBoxHeader();
                        
                        // Update progress bars immediately with response data
                        if (response.data.cart_subtotal_numeric !== undefined) {
                            updateCartBarProgressBars(response.data.cart_subtotal_numeric);
                        }
                        
                        // Also update cart sidebar bars
                        updateFreeShippingAndGiftBar();
                    } else {
                        // Revert on error
                        var $newButton = $card.find('.remove-from-cart-btn[data-product-id="' + productId + '"]');
                        $newButton.replaceWith(
                            '<button type="button" class="add-to-cart-btn" data-product-id="' + productId + '">+ Add</button>'
                        );
                        $card.attr('data-in-cart', '0');
                        
                        // Check if login is required
                        if (response.data && response.data.login_required) {
                            if (confirm(response.data.message + '\n\nWould you like to go to the login page?')) {
                                window.location.href = response.data.login_url || stirjoyData.loginUrl;
                            }
                        } else if (response.data && response.data.already_in_cart) {
                            // Product already in cart - update UI to show remove button
                            $card.attr('data-in-cart', '1');
                            if ($newButton.length === 0) {
                                $card.find('.add-to-cart-btn[data-product-id="' + productId + '"]').replaceWith(
                                    '<button type="button" class="remove-from-cart-btn" data-product-id="' + productId + '">- Remove</button>'
                                );
                            }
                            alert(response.data.message || 'This product is already in your cart.');
                        } else {
                            alert(response.data.message || 'Error adding to cart');
                        }
                    }
                    
                    // Unlock cart operations
                    isCartOperationInProgress = false;
                    $('.add-to-cart-btn, .remove-from-cart-btn, .mini_cart_item a.remove').css('pointer-events', 'auto').css('opacity', '1');
                },
                error: function() {
                    // Revert on error
                    var $newButton = $card.find('.remove-from-cart-btn[data-product-id="' + productId + '"]');
                    $newButton.replaceWith(
                        '<button type="button" class="add-to-cart-btn" data-product-id="' + productId + '">+ Add</button>'
                    );
                    $card.attr('data-in-cart', '0');
                    alert('An error occurred. Please try again.');
                    
                    // Unlock cart operations
                    isCartOperationInProgress = false;
                    $('.add-to-cart-btn, .remove-from-cart-btn, .mini_cart_item a.remove').css('pointer-events', 'auto').css('opacity', '1');
                }
            });
        });
        
        /**
         * Remove from Cart Button
         * Allows both logged-in and non-logged-in users to remove products from cart
         */
        $(document).on('click', '.remove-from-cart-btn', function(e) {
            e.preventDefault();
            
            // Check if another operation is in progress
            if (isCartOperationInProgress) {
                return false;
            }
            
            var $button = $(this);
            var productId = $button.data('product-id');
            var $card = $button.closest('.meal-product-card');
            
            // Lock cart operations
            isCartOperationInProgress = true;
            $('.add-to-cart-btn, .remove-from-cart-btn, .mini_cart_item a.remove').css('pointer-events', 'none').css('opacity', '0.6');
            
            // Immediately update UI for instant feedback
            $button.replaceWith(
                '<button type="button" class="add-to-cart-btn" data-product-id="' + productId + '">+ Add</button>'
            );
            $card.attr('data-in-cart', '0');
            
            // Send AJAX request in background
            $.ajax({
                url: stirjoyData.ajaxUrl,
                type: 'POST',
                timeout: 5000, // 5 second timeout
                data: {
                    action: 'stirjoy_remove_from_cart',
                    product_id: productId,
                    nonce: stirjoyData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update Your Box header
                        updateYourBoxHeader();
                        
                        // Update progress bars immediately with response data
                        if (response.data.cart_subtotal_numeric !== undefined) {
                            updateCartBarProgressBars(response.data.cart_subtotal_numeric);
                        }
                        
                        // Also update cart sidebar bars
                        updateFreeShippingAndGiftBar();
                    } else {
                        // Revert on error
                        var $newButton = $card.find('.add-to-cart-btn[data-product-id="' + productId + '"]');
                        $newButton.replaceWith(
                            '<button type="button" class="remove-from-cart-btn" data-product-id="' + productId + '">- Remove</button>'
                        );
                        $card.attr('data-in-cart', '1');
                        
                        // Check if login is required
                        if (response.data && response.data.login_required) {
                            if (confirm(response.data.message + '\n\nWould you like to go to the login page?')) {
                                window.location.href = response.data.login_url || stirjoyData.loginUrl;
                            }
                        } else {
                            alert(response.data.message || 'Error removing from cart');
                        }
                    }
                    
                    // Unlock cart operations
                    isCartOperationInProgress = false;
                    $('.add-to-cart-btn, .remove-from-cart-btn, .mini_cart_item a.remove').css('pointer-events', 'auto').css('opacity', '1');
                },
                error: function() {
                    // Revert on error
                    var $newButton = $card.find('.add-to-cart-btn[data-product-id="' + productId + '"]');
                    $newButton.replaceWith(
                        '<button type="button" class="remove-from-cart-btn" data-product-id="' + productId + '">- Remove</button>'
                    );
                    $card.attr('data-in-cart', '1');
                    alert('An error occurred. Please try again.');
                    
                    // Unlock cart operations
                    isCartOperationInProgress = false;
                    $('.add-to-cart-btn, .remove-from-cart-btn, .mini_cart_item a.remove').css('pointer-events', 'auto').css('opacity', '1');
                }
            });
        });
        
        /**
         * Update all shop page buttons based on cart state
         */
        function updateShopPageButtons(productIdsInCart) {
            // Convert array to object for faster lookup
            var inCartMap = {};
            if (productIdsInCart && productIdsInCart.length > 0) {
                productIdsInCart.forEach(function(id) {
                    inCartMap[id] = true;
                });
            }
            
            // Update all product cards on shop page
            $('.meal-product-card').each(function() {
                var $card = $(this);
                var productId = parseInt($card.attr('data-product-id'));
                var isInCart = inCartMap[productId] || false;
                
                // Update data attribute
                $card.attr('data-in-cart', isInCart ? '1' : '0');
                
                // Find and update button
                var $addBtn = $card.find('.add-to-cart-btn[data-product-id="' + productId + '"]');
                var $removeBtn = $card.find('.remove-from-cart-btn[data-product-id="' + productId + '"]');
                
                if (isInCart) {
                    // Product is in cart - show remove button
                    if ($addBtn.length > 0) {
                        $addBtn.replaceWith('<button type="button" class="remove-from-cart-btn" data-product-id="' + productId + '">- Remove</button>');
                    }
                } else {
                    // Product is not in cart - show add button
                    if ($removeBtn.length > 0) {
                        $removeBtn.replaceWith('<button type="button" class="add-to-cart-btn" data-product-id="' + productId + '">+ Add</button>');
                    }
                }
            });
        }
        
        /**
         * Update cart sidebar widget title with accurate count
         * This ensures consistency across all updates
         */
        function updateCartSidebarWidgetTitle(count) {
            // Ensure count is a number
            count = parseInt(count) || 0;
            var itemText = count === 1 ? 'item' : 'items';
            
            // Update the widget title with accurate count
            $(".widget_shopping_cart .widgettitle").html(
                '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-cart w-6 h-6 mr-2 text-primary" aria-hidden="true">' +
                '<circle cx="8" cy="21" r="1"></circle>' +
                '<circle cx="19" cy="21" r="1"></circle>' +
                '<path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>' +
                '</svg>Your Box <span>' + count + ' ' + itemText + '</span>'
            );
        }
        
        /**
         * Update Your Box Header Display
         * Made globally accessible for use in other scripts
         */
        window.updateYourBoxHeader = function updateYourBoxHeader() {
            $.ajax({
                url: stirjoyData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'stirjoy_get_cart_info',
                    nonce: stirjoyData.nonce
                },
                success: function(response) {
                    if (response.success && response.data) {
                        console.log('updateYourBoxHeader response:', response.data);
                        
                        // Ensure count is a number
                        var cartCount = parseInt(response.data.count) || 0;
                        
                        // Update Your Box header
                        $('.your-box-count').text(cartCount);
                        $('.your-box-total').html(response.data.total_html); // Use .html() to render HTML properly
                        
                        // Update main header cart badge
                        $('.cart-contents-custom span').text('(' + cartCount + ')');
                        
                        // Update cart sidebar widget title using helper function
                        updateCartSidebarWidgetTitle(cartCount);
                        
                        // Update cart bar progress bars immediately
                        if (response.data.cart_subtotal_numeric !== undefined) {
                            updateCartBarProgressBars(response.data.cart_subtotal_numeric);
                        }
                        
                        // Update cart sidebar progress bars
                        updateFreeShippingAndGiftBar();
                        
                        // Update all shop page buttons based on cart state
                        if (response.data.product_ids !== undefined) {
                            updateShopPageButtons(response.data.product_ids);
                        }
                        
                        // Trigger cart updated event for other scripts
                        $(document.body).trigger('wc_fragment_refresh');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error updating cart header:', error);
                }
            });
        }
        
        // Initialize cart count on page load for shop pages and My Account page
        // This ensures the cart count is accurate even if the page was reloaded
        // after AJAX cart operations. Must be called after updateYourBoxHeader is defined.
        if ($('body').hasClass('stirjoy-shop-page') || $('body').hasClass('woocommerce-shop') || $('body').hasClass('woocommerce-account')) {
            // Update cart count immediately on page load to ensure accuracy
            updateYourBoxHeader();
        }
        
        /**
         * Product Detail Modal
         */
        var $modal = $('#product-detail-modal');
        var $modalOverlay = $modal.find('.modal-overlay');
        var $modalContent = $modal.find('.modal-content');
        var $modalClose = $modal.find('.modal-close');
        
        // Open modal when View Details button is clicked
        $(document).on('click', '.view-details-btn', function(e) {
            e.preventDefault();
            console.log('=== VIEW DETAILS CLICKED ===');
            
            var $button = $(this);
            var productId = $button.data('product-id');
            console.log('Product ID from view details button:', productId);
            
            if (!productId) {
                console.error('No product ID on view details button');
                return;
            }
            
            // Show loading state
            console.log('Opening modal...');
            $modal.addClass('loading');
            $modal.addClass('active');
            $('body').addClass('modal-open');
            
            // Fetch product details via AJAX
            $.ajax({
                url: stirjoyData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'stirjoy_get_product_details',
                    product_id: productId,
                    nonce: stirjoyData.nonce
                },
                success: function(response) {
                    if (response.success && response.data) {
                        populateModal(response.data);
                    } else {
                        alert('Failed to load product details.');
                        closeModal();
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    closeModal();
                },
                complete: function() {
                    $modal.removeClass('loading');
                }
            });
        });
        
        // Close modal handlers
        $modalClose.on('click', closeModal);
        $modalOverlay.on('click', closeModal);
        
        // Prevent closing when clicking inside modal content
        $modalContent.on('click', function(e) {
            e.stopPropagation();
        });
        
        // Also allow closing by clicking on the wrapper (outside content)
        $('.modal-content-wrapper').on('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Close on Escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $modal.hasClass('active')) {
                closeModal();
            }
        });
        
        // Populate modal with product data
        function populateModal(data) {
            console.log('=== POPULATING MODAL ===');
            console.log('Product data:', data);
            
            // Basic info
            $('#modal-product-img').attr('src', data.image_url).attr('alt', data.name);
            $('#modal-product-title').text(data.name);
            
            // Rating
            if (data.rating > 0) {
                $('#modal-rating-value').text(data.rating.toFixed(1));
                $('#modal-rating').show();
            } else {
                $('#modal-rating').hide();
            }
            
            // Description (use .html() to properly render text, but data is already sanitized in PHP)
            $('#modal-description').html(data.description || '');
            
            // Metrics
            if (data.prep_time) {
                $('#modal-prep-time .metric-value').text(data.prep_time);
                $('#modal-prep-time').show();
            } else {
                $('#modal-prep-time').hide();
            }
            
            if (data.cook_time) {
                $('#modal-cook-time .metric-value').text(data.cook_time);
                $('#modal-cook-time').show();
            } else {
                $('#modal-cook-time').hide();
            }
            
            if (data.serving_size) {
                $('#modal-serving-size .metric-value').text(data.serving_size);
                $('#modal-serving-size').show();
            } else {
                $('#modal-serving-size').hide();
            }
            
            if (data.calories) {
                $('#modal-calories .metric-value').text(data.calories);
                $('#modal-calories').show();
            } else {
                $('#modal-calories').hide();
            }
            
            // Nutrition
            if (data.protein) {
                $('#modal-protein .nutrition-value').text(data.protein + 'g');
                $('#modal-protein').show();
            } else {
                $('#modal-protein').hide();
            }
            
            if (data.carbs) {
                $('#modal-carbs .nutrition-value').text(data.carbs + 'g');
                $('#modal-carbs').show();
            } else {
                $('#modal-carbs').hide();
            }
            
            if (data.fat) {
                $('#modal-fat .nutrition-value').text(data.fat + 'g');
                $('#modal-fat').show();
            } else {
                $('#modal-fat').hide();
            }
            
            // Ingredients
            if (data.ingredients) {
                var ingredients = data.ingredients.split(',').map(function(item) {
                    return item.trim();
                }).filter(function(item) {
                    return item.length > 0;
                });
                
                var $ingredientsList = $('#modal-ingredients-list');
                $ingredientsList.empty();
                
                ingredients.forEach(function(ingredient) {
                    $ingredientsList.append('<span class="ingredient-tag">' + ingredient + '</span>');
                });
                
                $('#modal-ingredients-section').show();
            } else {
                $('#modal-ingredients-section').hide();
            }
            
            // Allergens
            if (data.allergens) {
                var allergens = data.allergens.split(',').map(function(item) {
                    return item.trim();
                }).filter(function(item) {
                    return item.length > 0;
                });
                
                var $allergensList = $('#modal-allergens-list');
                $allergensList.empty();
                
                allergens.forEach(function(allergen) {
                    $allergensList.append('<span class="allergen-tag">' + allergen + '</span>');
                });
                
                $('#modal-allergens-section').show();
            } else {
                $('#modal-allergens-section').hide();
            }
            
            // Instructions
            if (data.instructions) {
                var instructions = data.instructions.split('\n').filter(function(item) {
                    return item.trim().length > 0;
                });
                
                var $instructionsList = $('#modal-instructions-list');
                $instructionsList.empty();
                
                instructions.forEach(function(instruction) {
                    $instructionsList.append('<li>' + instruction.trim() + '</li>');
                });
                
                $('#modal-instructions-section').show();
            } else {
                $('#modal-instructions-section').hide();
            }
            
            // Price and action button
            $('#modal-price').html(data.price);
            
            var $actionBtn = $('#modal-action-btn');
            console.log('Found action button:', $actionBtn.length, 'elements');
            console.log('Button before update:', $actionBtn[0] ? $actionBtn[0].outerHTML : 'not found');
            
            // Set product ID
            $actionBtn.attr('data-product-id', data.product_id);
            console.log('Set product ID to:', data.product_id);
            console.log('Product ID after setting:', $actionBtn.attr('data-product-id'));
            
            // Set button text and class based on cart status
            if (data.in_cart) {
                $actionBtn.text('- Remove').removeClass('add-btn').addClass('remove-btn');
                console.log('Set button to REMOVE mode');
            } else {
                $actionBtn.text('+ Add').removeClass('remove-btn').addClass('add-btn');
                console.log('Set button to ADD mode');
            }
            
            console.log('Button after update:', $actionBtn[0] ? $actionBtn[0].outerHTML : 'not found');
            console.log('=== MODAL POPULATION COMPLETE ===');
        }
        
        // Close modal function
        function closeModal() {
            $modal.removeClass('active');
            $('body').removeClass('modal-open');
        }
        
        // Handle modal action button (Add/Remove) - Works independently
        $(document).on('click', '#modal-action-btn', function(e) {
            e.preventDefault();
            console.log('=== MODAL BUTTON CLICKED ===');
            
            var $button = $(this);
            console.log('Button element:', $button);
            console.log('Button HTML:', $button[0] ? $button[0].outerHTML : 'not found');
            
            // Check if another operation is in progress
            if (isCartOperationInProgress) {
                console.log('Cart operation already in progress, blocking');
                return false;
            }
            
            // Use .attr() to read the attribute we set with .attr()
            var productId = $button.attr('data-product-id');
            console.log('Product ID from attr:', productId);
            
            var isRemove = $button.hasClass('remove-btn');
            console.log('Is remove button:', isRemove);
            console.log('Button classes:', $button.attr('class'));
            
            if (!productId) {
                console.error('NO PRODUCT ID FOUND!');
                console.error('All button attributes:', $button[0].attributes);
                alert('Error: Product ID is missing. Please close and reopen the modal.');
                return false;
            }
            
            console.log('Proceeding with', isRemove ? 'REMOVE' : 'ADD', 'for product ID:', productId);
            
            // Lock cart operations
            isCartOperationInProgress = true;
            $('.add-to-cart-btn, .remove-from-cart-btn, .mini_cart_item a.remove, #modal-action-btn').css('pointer-events', 'none').css('opacity', '0.6');
            
            if (isRemove) {
                // Immediately update button for instant feedback
                $button.text('+ Add').removeClass('remove-btn').addClass('add-btn');
                
                // Send AJAX request in background
                $.ajax({
                    url: stirjoyData.ajaxUrl,
                    type: 'POST',
                    timeout: 5000,
                    data: {
                        action: 'stirjoy_remove_from_cart',
                        product_id: productId,
                        nonce: stirjoyData.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update shop page card state if exists
                            var $shopCard = $('.meal-product-card[data-product-id="' + productId + '"]');
                            if ($shopCard.length > 0) {
                                $shopCard.attr('data-in-cart', '0');
                                $shopCard.find('.remove-from-cart-btn').replaceWith(
                                    '<button type="button" class="add-to-cart-btn" data-product-id="' + productId + '">+ Add</button>'
                                );
                            }
                            
                            // Update cart displays
                            updateYourBoxHeader();
                            if (response.data.cart_subtotal_numeric !== undefined) {
                                updateCartBarProgressBars(response.data.cart_subtotal_numeric);
                            }
                            updateFreeShippingAndGiftBar();
                        } else {
                            // Revert on error
                            $button.text('- Remove').removeClass('add-btn').addClass('remove-btn');
                            alert(response.data.message || 'Error removing from cart');
                        }
                        
                        // Unlock cart operations
                        isCartOperationInProgress = false;
                        $('.add-to-cart-btn, .remove-from-cart-btn, .mini_cart_item a.remove, #modal-action-btn').css('pointer-events', 'auto').css('opacity', '1');
                    },
                    error: function() {
                        // Revert on error
                        $button.text('- Remove').removeClass('add-btn').addClass('remove-btn');
                        alert('An error occurred. Please try again.');
                        
                        // Unlock cart operations
                        isCartOperationInProgress = false;
                        $('.add-to-cart-btn, .remove-from-cart-btn, .mini_cart_item a.remove, #modal-action-btn').css('pointer-events', 'auto').css('opacity', '1');
                    }
                });
            } else {
                // Check if product is already in cart (client-side check)
                var $shopCard = $('.meal-product-card[data-product-id="' + productId + '"]');
                if ($shopCard.length > 0 && $shopCard.attr('data-in-cart') === '1') {
                    alert('This product is already in your cart. Each product can only be added once.');
                    return false;
                }
                
                // Immediately update button for instant feedback
                $button.text('- Remove').removeClass('add-btn').addClass('remove-btn');
                
                // Send AJAX request in background
                $.ajax({
                    url: stirjoyData.ajaxUrl,
                    type: 'POST',
                    timeout: 5000,
                    data: {
                        action: 'stirjoy_add_to_cart',
                        product_id: productId,
                        quantity: 1,
                        nonce: stirjoyData.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update shop page card state if exists
                            if ($shopCard.length > 0) {
                                $shopCard.attr('data-in-cart', '1');
                                $shopCard.find('.add-to-cart-btn').replaceWith(
                                    '<button type="button" class="remove-from-cart-btn" data-product-id="' + productId + '">- Remove</button>'
                                );
                            }
                            
                            // Update cart displays
                            updateYourBoxHeader();
                            if (response.data.cart_subtotal_numeric !== undefined) {
                                updateCartBarProgressBars(response.data.cart_subtotal_numeric);
                            }
                            updateFreeShippingAndGiftBar();
                        } else {
                            // Revert on error
                            $button.text('+ Add').removeClass('remove-btn').addClass('add-btn');
                            
                            // Check if product already in cart
                            if (response.data && response.data.already_in_cart) {
                                // Product already in cart - update UI to show remove button
                                if ($shopCard.length > 0) {
                                    $shopCard.attr('data-in-cart', '1');
                                    $shopCard.find('.add-to-cart-btn').replaceWith(
                                        '<button type="button" class="remove-from-cart-btn" data-product-id="' + productId + '">- Remove</button>'
                                    );
                                }
                                $button.text('- Remove').removeClass('add-btn').addClass('remove-btn');
                                alert(response.data.message || 'This product is already in your cart.');
                            } else {
                                alert(response.data.message || 'Error adding to cart');
                            }
                        }
                        
                        // Unlock cart operations
                        isCartOperationInProgress = false;
                        $('.add-to-cart-btn, .remove-from-cart-btn, .mini_cart_item a.remove, #modal-action-btn').css('pointer-events', 'auto').css('opacity', '1');
                    },
                    error: function() {
                        // Revert on error
                        $button.text('+ Add').removeClass('remove-btn').addClass('add-btn');
                        alert('An error occurred. Please try again.');
                        
                        // Unlock cart operations
                        isCartOperationInProgress = false;
                        $('.add-to-cart-btn, .remove-from-cart-btn, .mini_cart_item a.remove, #modal-action-btn').css('pointer-events', 'auto').css('opacity', '1');
                    }
                });
            }
        });

        /**
         * FAQ Accordion Functionality
         */
        $(document).on('click', '.faq-question', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $faqItem = $(this).closest('.faq-item');
            var $faqAnswer = $faqItem.find('.faq-answer');
            var isActive = $faqItem.hasClass('active');
            
            // Close all other FAQ items
            $('.faq-item').not($faqItem).each(function() {
                var $otherItem = $(this);
                var $otherAnswer = $otherItem.find('.faq-answer');
                $otherItem.removeClass('active');
                if ($otherAnswer.is(':visible')) {
                    $otherAnswer.slideUp(300, function() {
                        $(this).css('display', 'none');
                    });
                }
            });
            
            // Toggle current FAQ item
            if (isActive) {
                $faqItem.removeClass('active');
                $faqAnswer.slideUp(300, function() {
                    $(this).css('display', 'none');
                });
            } else {
                $faqItem.addClass('active');
                $faqAnswer.css('display', 'none').slideDown(300);
            }
        });
        
        // Ensure all FAQ answers are hidden on page load
        $('.faq-answer').css('display', 'none');

        /**
         * Testimonial Cards Dragging Functionality
         */
        var testimonialContainer = $('#testimonial-cards-container');
        var testimonialWrapper = $('.testimonial-cards-wrapper');
        
        if (testimonialContainer.length && testimonialWrapper.length) {
            var isDragging = false;
            var startX = 0;
            var currentX = 0;
            var currentTranslate = 0;
            var prevTranslate = 0;

            testimonialWrapper.on('mousedown', function(e) {
                isDragging = true;
                startX = e.pageX - testimonialWrapper.offset().left;
                prevTranslate = currentTranslate;
                testimonialWrapper.css('cursor', 'grabbing');
                e.preventDefault();
            });

            $(document).on('mousemove', function(e) {
                if (!isDragging) return;
                e.preventDefault();
                
                currentX = e.pageX - testimonialWrapper.offset().left;
                var movedX = currentX - startX;
                currentTranslate = prevTranslate + movedX;
                
                // Apply transform
                testimonialContainer.css('transform', 'translateX(' + currentTranslate + 'px)');
            });

            $(document).on('mouseup', function() {
                if (isDragging) {
                    isDragging = false;
                    testimonialWrapper.css('cursor', 'grab');
                    
                    // Get container and wrapper dimensions
                    var containerWidth = testimonialContainer.outerWidth();
                    var wrapperWidth = testimonialWrapper.outerWidth();
                    var maxTranslate = 0;
                    var minTranslate = wrapperWidth - containerWidth;
                    
                    // Constrain movement
                    if (currentTranslate > maxTranslate) {
                        currentTranslate = maxTranslate;
                    } else if (currentTranslate < minTranslate) {
                        currentTranslate = minTranslate;
                    }
                    
                    prevTranslate = currentTranslate;
                    testimonialContainer.css('transform', 'translateX(' + currentTranslate + 'px)');
                }
            });

            // Touch events for mobile
            var touchStartX = 0;
            var touchCurrentX = 0;
            var touchPrevTranslate = 0;
            var touchCurrentTranslate = 0;

            testimonialWrapper.on('touchstart', function(e) {
                touchStartX = e.touches[0].clientX;
                touchPrevTranslate = currentTranslate;
                e.preventDefault();
            });

            testimonialWrapper.on('touchmove', function(e) {
                touchCurrentX = e.touches[0].clientX;
                var movedX = touchCurrentX - touchStartX;
                touchCurrentTranslate = touchPrevTranslate + movedX;
                testimonialContainer.css('transform', 'translateX(' + touchCurrentTranslate + 'px)');
                e.preventDefault();
            });

            testimonialWrapper.on('touchend', function() {
                // Get container and wrapper dimensions
                var containerWidth = testimonialContainer.outerWidth();
                var wrapperWidth = testimonialWrapper.outerWidth();
                var maxTranslate = 0;
                var minTranslate = wrapperWidth - containerWidth;
                
                // Constrain movement
                if (touchCurrentTranslate > maxTranslate) {
                    touchCurrentTranslate = maxTranslate;
                } else if (touchCurrentTranslate < minTranslate) {
                    touchCurrentTranslate = minTranslate;
                }
                
                currentTranslate = touchCurrentTranslate;
                touchPrevTranslate = touchCurrentTranslate;
                testimonialContainer.css('transform', 'translateX(' + touchCurrentTranslate + 'px)');
            });
        }

        /**
         * Social Posts Dragging Functionality (Mobile Only - Same as Testimonial Cards)
         */
        var socialPostsContainer = $('#social-posts-container');
        var socialPostsWrapper = $('.social-posts-wrapper');
        
        function isMobileView() {
            return window.innerWidth <= 768;
        }
        
        if (socialPostsContainer.length && socialPostsWrapper.length) {
            var isSocialDragging = false;
            var socialStartX = 0;
            var socialCurrentX = 0;
            var socialCurrentTranslate = 0;
            var socialPrevTranslate = 0;
            var isMobile = isMobileView();
            
            // Function to center the social posts image
            function centerSocialPosts() {
                if (!isMobile) return;
                
                var containerWidth = socialPostsContainer.outerWidth();
                var wrapperWidth = socialPostsWrapper.outerWidth();
                
                if (containerWidth > wrapperWidth) {
                    // Center the image: move left by half the difference
                    socialCurrentTranslate = (wrapperWidth - containerWidth) / 2;
                    socialPrevTranslate = socialCurrentTranslate;
                    socialPostsContainer.css('transform', 'translateX(' + socialCurrentTranslate + 'px)');
                }
            }
            
            // Center on page load (after images are loaded)
            $(window).on('load', function() {
                setTimeout(centerSocialPosts, 100);
            });
            
            // Also center immediately if already loaded
            if (document.readyState === 'complete') {
                setTimeout(centerSocialPosts, 100);
            } else {
                $(document).ready(function() {
                    setTimeout(centerSocialPosts, 100);
                });
            }
            
            // Update mobile detection on resize and recenter
            $(window).on('resize', function() {
                var wasMobile = isMobile;
                isMobile = isMobileView();
                
                // If switching to mobile or already mobile, recenter
                if (isMobile && !isSocialDragging) {
                    setTimeout(centerSocialPosts, 100);
                }
            });

            socialPostsWrapper.on('mousedown', function(e) {
                if (!isMobile) return;
                
                isSocialDragging = true;
                socialStartX = e.pageX - socialPostsWrapper.offset().left;
                socialPrevTranslate = socialCurrentTranslate;
                socialPostsWrapper.css('cursor', 'grabbing');
                e.preventDefault();
            });

            $(document).on('mousemove', function(e) {
                if (!isSocialDragging || !isMobile) return;
                e.preventDefault();
                
                socialCurrentX = e.pageX - socialPostsWrapper.offset().left;
                var movedX = socialCurrentX - socialStartX;
                socialCurrentTranslate = socialPrevTranslate + movedX;
                
                // Apply transform to container (not image)
                socialPostsContainer.css('transform', 'translateX(' + socialCurrentTranslate + 'px)');
            });

            $(document).on('mouseup', function() {
                if (isSocialDragging && isMobile) {
                    isSocialDragging = false;
                    socialPostsWrapper.css('cursor', 'grab');
                    
                    // Get container and wrapper dimensions
                    var containerWidth = socialPostsContainer.outerWidth();
                    var wrapperWidth = socialPostsWrapper.outerWidth();
                    var maxTranslate = 0;
                    var minTranslate = wrapperWidth - containerWidth;
                    
                    // Constrain movement
                    if (socialCurrentTranslate > maxTranslate) {
                        socialCurrentTranslate = maxTranslate;
                    } else if (socialCurrentTranslate < minTranslate) {
                        socialCurrentTranslate = minTranslate;
                    }
                    
                    socialPrevTranslate = socialCurrentTranslate;
                    socialPostsContainer.css('transform', 'translateX(' + socialCurrentTranslate + 'px)');
                }
            });

            // Touch events for mobile
            var touchSocialStartX = 0;
            var touchSocialCurrentX = 0;
            var touchSocialPrevTranslate = 0;
            var touchSocialCurrentTranslate = 0;

            socialPostsWrapper.on('touchstart', function(e) {
                if (!isMobile) return;
                
                touchSocialStartX = e.touches[0].clientX;
                touchSocialPrevTranslate = socialCurrentTranslate;
                e.preventDefault();
            });

            socialPostsWrapper.on('touchmove', function(e) {
                if (!isMobile) return;
                
                touchSocialCurrentX = e.touches[0].clientX;
                var movedX = touchSocialCurrentX - touchSocialStartX;
                touchSocialCurrentTranslate = touchSocialPrevTranslate + movedX;
                socialPostsContainer.css('transform', 'translateX(' + touchSocialCurrentTranslate + 'px)');
                e.preventDefault();
            });

            socialPostsWrapper.on('touchend', function() {
                if (!isMobile) return;
                
                // Get container and wrapper dimensions
                var containerWidth = socialPostsContainer.outerWidth();
                var wrapperWidth = socialPostsWrapper.outerWidth();
                var maxTranslate = 0;
                var minTranslate = wrapperWidth - containerWidth;
                
                // Constrain movement
                if (touchSocialCurrentTranslate > maxTranslate) {
                    touchSocialCurrentTranslate = maxTranslate;
                } else if (touchSocialCurrentTranslate < minTranslate) {
                    touchSocialCurrentTranslate = minTranslate;
                }
                
                socialCurrentTranslate = touchSocialCurrentTranslate;
                touchSocialPrevTranslate = touchSocialCurrentTranslate;
                socialPostsContainer.css('transform', 'translateX(' + touchSocialCurrentTranslate + 'px)');
            });
        }

        /**
         * Listen for Bootstrap collapse events to update header height SMOOTHLY
         * Use 'show' and 'hide' events (fire before animation) for smooth synchronized animation
         * Only on desktop - prevent on mobile
         */
        $(document).on('show.bs.collapse', '#navbar1', function(e) {
            // Don't handle on mobile - use custom menu instead
            if ($(window).width() <= 991) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
            
            // Menu is about to open - expand header IMMEDIATELY (at same time as collapse animation starts)
            $('#theme-main-head, .navbar-default').addClass('menu-open');
        });
        
        $(document).on('hide.bs.collapse', '#navbar1', function(e) {
            // Don't handle on mobile - use custom menu instead
            if ($(window).width() <= 991) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
            // Menu is about to close - collapse header SMOOTHLY
            var $header = $('#theme-main-head, .navbar-default');
            var $container = $('#theme-main-head > .container');
            
            // Get current height (auto) before removing menu-open for smooth transition
            var currentHeight = $header.outerHeight();
            
            // Set explicit height before removing class so CSS can transition smoothly
            // This allows transition from explicit height to 62px
            $header.css({
                'height': currentHeight + 'px',
                'min-height': currentHeight + 'px'
            });
            $container.css({
                'height': currentHeight + 'px',
                'min-height': currentHeight + 'px'
            });
            
            // Force reflow to ensure height is set before transition
            if ($header.length && $header[0]) {
                $header[0].offsetHeight;
            }
            
            // Remove menu-open class - CSS transition will animate from currentHeight to 62px
            $header.removeClass('menu-open');
            
            // After transition completes (350ms), set to final fixed height
            setTimeout(function() {
                $header.css({
                    'height': '62px',
                    'min-height': '62px'
                });
                $container.css({
                    'height': '62px',
                    'min-height': '62px'
                });
            }, 350);
        });
        
        /**
         * Backup: Ensure state is correct after animation completes
         */
        $(document).on('shown.bs.collapse', '#navbar1', function(e) {
            // Don't handle on mobile
            if ($(window).width() <= 991) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
            
            var $header = $('#theme-main-head, .navbar-default');
            $header.addClass('menu-open');
            // Reset height to auto after transition completes
            setTimeout(function() {
                $header.css({
                    'height': 'auto',
                    'min-height': '62px'
                });
            }, 350);
        });
        
        $(document).on('hidden.bs.collapse', '#navbar1', function(e) {
            // Don't handle on mobile
            if ($(window).width() <= 991) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
            
            var $header = $('#theme-main-head, .navbar-default');
            var $container = $('#theme-main-head > .container');
            // Ensure fixed height after transition completes
            setTimeout(function() {
                $header.css({
                    'height': '62px',
                    'min-height': '62px'
                });
                $container.css({
                    'height': '62px',
                    'min-height': '62px'
                });
            }, 50);
        });
        
        /**
         * Initialize: Remove Bootstrap collapse attributes on mobile and ensure navbar1 is hidden
         */
        function initMobileMenu() {
            if ($(window).width() <= 991) {
                // Remove Bootstrap collapse attributes from toggle button
                $('.navbar-toggle').removeAttr('data-toggle').removeAttr('data-target');
                
                // Ensure navbar1 is always hidden on mobile
                var $navbar1 = $('#navbar1');
                $navbar1.removeClass('in show');
                $navbar1.addClass('collapse');
                $navbar1.hide();
                
                // Ensure toggle button is in collapsed state
                $('.navbar-toggle').addClass('collapsed').attr('aria-expanded', 'false');
            }
        }
        
        // Initialize on page load
        initMobileMenu();
        
        // Re-initialize on window resize
        $(window).on('resize', function() {
            initMobileMenu();
        });
        
        /**
         * Mobile Menu Toggle - Open/Close Full Screen Mobile Menu
         * Prevent Bootstrap collapse from working on mobile
         */
        $(document).on('click', '.navbar-toggle', function(e) {
            // Only handle on mobile devices
            if ($(window).width() > 991) {
                return;
            }
            
            // Prevent Bootstrap collapse completely
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            // Remove Bootstrap data attributes to prevent collapse
            var $toggle = $(this);
            $toggle.removeAttr('data-toggle');
            $toggle.removeAttr('data-target');
            
            // Ensure navbar1 is always hidden on mobile
            var $navbar1 = $('#navbar1');
            $navbar1.removeClass('in show');
            $navbar1.addClass('collapse');
            $navbar1.collapse('hide');
            
            // Update button state
            $toggle.addClass('collapsed');
            $toggle.attr('aria-expanded', 'false');
            
            var $mobileMenu = $('#stirjoy-mobile-menu');
            
            if ($mobileMenu.length === 0) {
                console.error('Mobile menu element not found!');
                return false;
            }
            
            if ($mobileMenu.hasClass('active')) {
                // Close menu - slide up and fade out
                // Use requestAnimationFrame for smooth animation
                requestAnimationFrame(function() {
                    // Ensure transition is set
                    $mobileMenu.css({
                        'transition': 'transform 0.8s ease-in-out, opacity 0.8s ease-in-out'
                    });
                    
                    // Trigger animation by setting final state
                    requestAnimationFrame(function() {
                        $mobileMenu.css({
                            'transform': 'translateY(-100%)',
                            'opacity': '0'
                        });
                    });
                });
                
                // Wait for animation to complete before hiding
                setTimeout(function() {
                    $mobileMenu.removeClass('active');
                    $('body').removeClass('mobile-menu-open');
                    
                    // Remove inline styles
                    $mobileMenu.css({
                        'display': '',
                        'visibility': '',
                        'opacity': '',
                        'transform': '',
                        'transition': ''
                    });
                }, 800); // Match CSS transition duration
            } else {
                // Open menu - slide down from top
                // First, set initial state (hidden above viewport) without transition
                $mobileMenu.css({
                    'display': 'flex',
                    'visibility': 'visible',
                    'opacity': '0',
                    'transform': 'translateY(-100%)',
                    'transition': 'none' // No transition for initial state
                });
                
                // Force reflow to ensure initial state is applied
                $mobileMenu[0].offsetHeight;
                
                // Use requestAnimationFrame to ensure smooth transition
                requestAnimationFrame(function() {
                    // Now enable transition and add active class
                    $mobileMenu.css({
                        'transition': 'transform 0.8s ease-in-out, opacity 0.8s ease-in-out'
                    });
                    
                    $mobileMenu.addClass('active');
                    // Don't add mobile-menu-open class yet - wait for animation to complete
                    // This keeps homepage visible during slide animation
                    
                    // Trigger animation by setting final state
                    requestAnimationFrame(function() {
                        $mobileMenu.css({
                            'opacity': '1',
                            'transform': 'translateY(0)'
                        });
                        
                        // Add mobile-menu-open class after animation completes
                        // This will hide homepage only after menu is fully visible
                        setTimeout(function() {
                            $('body').addClass('mobile-menu-open');
                        }, 800); // Match CSS transition duration
                    });
                });
            }
            
            return false;
        });
        
        /**
         * Prevent Bootstrap collapse events on mobile
         */
        $(document).on('show.bs.collapse hide.bs.collapse', '#navbar1', function(e) {
            if ($(window).width() <= 991) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });
        
        /**
         * Close Mobile Menu with X button - smooth slide up animation
         */
        $(document).on('click', '.stirjoy-mobile-menu-close', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var $mobileMenu = $('#stirjoy-mobile-menu');
            
            // Remove mobile-menu-open class immediately to show homepage during slide up
            $('body').removeClass('mobile-menu-open');
            
            // Use requestAnimationFrame for smooth animation
            requestAnimationFrame(function() {
                // Ensure transition is set
                $mobileMenu.css({
                    'transition': 'transform 0.8s ease-in-out, opacity 0.8s ease-in-out'
                });
                
                // Trigger animation
                requestAnimationFrame(function() {
                    $mobileMenu.css({
                        'transform': 'translateY(-100%)',
                        'opacity': '0'
                    });
                });
            });
            
            // Wait for animation to complete before hiding
            setTimeout(function() {
                $mobileMenu.removeClass('active');
                
                // Remove inline styles
                $mobileMenu.css({
                    'display': '',
                    'visibility': '',
                    'opacity': '',
                    'transform': '',
                    'transition': ''
                });
            }, 800); // Match CSS transition duration
        });
        
        /**
         * Close Mobile Menu when clicking on menu links - smooth slide up animation
         */
        $(document).on('click', '.stirjoy-mobile-menu-link, .stirjoy-mobile-menu-cta', function() {
            var $mobileMenu = $('#stirjoy-mobile-menu');
            
            // Remove mobile-menu-open class immediately to show homepage during slide up
            $('body').removeClass('mobile-menu-open');
            
            // Use requestAnimationFrame for smooth animation
            requestAnimationFrame(function() {
                // Ensure transition is set
                $mobileMenu.css({
                    'transition': 'transform 0.8s ease-in-out, opacity 0.8s ease-in-out'
                });
                
                // Trigger animation
                requestAnimationFrame(function() {
                    $mobileMenu.css({
                        'transform': 'translateY(-100%)',
                        'opacity': '0'
                    });
                });
            });
            
            // Wait for animation to complete before hiding
            setTimeout(function() {
                $mobileMenu.removeClass('active');
                
                // Remove inline styles
                $mobileMenu.css({
                    'display': '',
                    'visibility': '',
                    'opacity': '',
                    'transform': '',
                    'transition': ''
                });
            }, 800); // Match CSS transition duration
        });
        
        /**
         * Close Mobile Menu when clicking outside (on overlay) - smooth slide up animation
         */
        $(document).on('click', '.stirjoy-mobile-menu', function(e) {
            // Only close if clicking directly on the menu container (not on children)
            // Don't close if clicking the close button
            if ($(e.target).hasClass('stirjoy-mobile-menu') && !$(e.target).closest('.stirjoy-mobile-menu-close').length) {
                var $mobileMenu = $('#stirjoy-mobile-menu');
                
                // Remove mobile-menu-open class immediately to show homepage during slide up
                $('body').removeClass('mobile-menu-open');
                
                // Use requestAnimationFrame for smooth animation
                requestAnimationFrame(function() {
                    // Ensure transition is set
                    $mobileMenu.css({
                        'transition': 'transform 0.8s ease-in-out, opacity 0.8s ease-in-out'
                    });
                    
                    // Trigger animation
                    requestAnimationFrame(function() {
                        $mobileMenu.css({
                            'transform': 'translateY(-100%)',
                            'opacity': '0'
                        });
                    });
                });
                
                // Wait for animation to complete before hiding
                setTimeout(function() {
                    $mobileMenu.removeClass('active');
                    
                    // Remove inline styles
                    $mobileMenu.css({
                        'display': '',
                        'visibility': '',
                        'opacity': '',
                        'transform': '',
                        'transition': ''
                    });
                }, 800); // Match CSS transition duration
            }
        });
        
        /**
         * Prevent closing when clicking inside menu content (but allow close button)
         */
        $(document).on('click', '.stirjoy-mobile-menu-header, .stirjoy-mobile-menu-content, .stirjoy-mobile-menu-footer', function(e) {
            // Don't stop propagation if clicking the close button
            if (!$(e.target).closest('.stirjoy-mobile-menu-close').length) {
                e.stopPropagation();
            }
        });
        
        /**
         * Close mobile menu when clicking on menu links
         */
        $(document).on('click', '#navbar1 .mobile-menu a', function(e) {
            var $link = $(this);
            var $menu = $('#navbar1');
            var $toggle = $('.navbar-toggle');
            
            // Only close if it's a regular link (not a dropdown toggle)
            if (!$link.parent().hasClass('dropdown-toggle') && !$link.hasClass('dropdown-toggle')) {
                // Use Bootstrap's collapse method for proper state management
                setTimeout(function() {
                    if ($menu.hasClass('in') || $menu.hasClass('show')) {
                        $menu.collapse('hide');
                        $toggle.addClass('collapsed').attr('aria-expanded', 'false');
                    }
                }, 300);
            }
        });
        
        /**
         * Close mobile menu when clicking outside
         */
        $(document).on('click', function(e) {
            var $target = $(e.target);
            var $menu = $('#navbar1');
            var $toggle = $('.navbar-toggle');
            
            // If click is outside menu, toggle button, and navbar header
            if (!$target.closest('#navbar1').length && 
                !$target.closest('.navbar-toggle').length && 
                !$target.closest('.navbar-header').length) {
                
                // Only close if menu is open
                if ($menu.hasClass('in') || $menu.hasClass('show')) {
                    $menu.collapse('hide');
                    $toggle.addClass('collapsed').attr('aria-expanded', 'false');
                }
            }
        });
        
        /**
         * Ensure mobile menu is properly initialized on page load
         */
        if ($(window).width() <= 991) {
            $('#navbar1').addClass('collapse');
            $('.navbar-toggle').addClass('collapsed').attr('aria-expanded', 'false');
        }

    }); // End document ready

})(jQuery);
