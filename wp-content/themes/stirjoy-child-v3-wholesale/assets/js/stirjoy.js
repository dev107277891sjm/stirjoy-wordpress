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
                    
                    // Update cart bar progress bars (Shop page header)
                    updateCartBarProgressBars(subtotal);
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

            var cartCount = $(".header-nav-actions .cart-contents span").text() || '0';
            var itemText = cartCount === '1' ? 'item' : 'items';
            $(".widget_shopping_cart .widgettitle").html('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-cart w-6 h-6 mr-2 text-primary" aria-hidden="true"><circle cx="8" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>Your Box <span>' + cartCount + ' ' + itemText + '</span>');

            updateFreeShippingAndGiftBar();
        });

        $('body').on( 'removed_from_cart', function(){
            var cartCount = $(".header-nav-actions .cart-contents span").text() || '0';
            var itemText = cartCount === '1' ? 'item' : 'items';
            $(".widget_shopping_cart .widgettitle").html('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-cart w-6 h-6 mr-2 text-primary" aria-hidden="true"><circle cx="8" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>Your Box <span>' + cartCount + ' ' + itemText + '</span>');

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

        // Cart Item Remove Icon
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
                        alert('Error removing product from cart');
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
         */
        $(document).on('click', '.toggle-cart-sidebar', function(e) {
            e.preventDefault();
            $('.fixed-sidebar-menu-minicart').toggleClass('open');
            $('.your-box-header').toggleClass('cart-open');
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
                        alert(response.data.message || 'Error adding to cart');
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
                        alert(response.data.message || 'Error removing from cart');
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
                        console.log('updateYourBoxHeader response:', response.data);
                        
                        // Update Your Box header
                        $('.your-box-count').text(response.data.count);
                        $('.your-box-total').html(response.data.total_html); // Use .html() to render HTML properly
                        
                        // Update main header cart badge
                        $('.cart-contents span').text(response.data.count);
                        
                        // Update cart sidebar widget title
                        var itemText = response.data.count === 1 ? 'item' : 'items';
                        $(".widget_shopping_cart .widgettitle").html('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-cart w-6 h-6 mr-2 text-primary" aria-hidden="true"><circle cx="8" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>Your Box <span>' + response.data.count + ' ' + itemText + '</span>');
                        
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
                }
            });
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
                            var $shopCard = $('.meal-product-card[data-product-id="' + productId + '"]');
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
                            alert(response.data.message || 'Error adding to cart');
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

    }); // End document ready

})(jQuery);
