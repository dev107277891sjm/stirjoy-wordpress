/*------------------------------------------------------------------
[Theme Custom JS Scripts]

[Table of contents]

- Buttons
- WooCommerce Plus Minus Qty
- Navigation Dropdown Toggle to left when no space in the right side
- jQuery preloader
- Other Scripts

-------------------------------------------------------------------*/

(function ($) {
    'use strict';

    // Buttons
    jQuery( ".themeslr_button_shortcode a" ).mouseover(function() {
        // bg
        var hover_color_bg = jQuery( this ).attr('data-bg-hover');
        // color
        var hover_color_text = jQuery( this ).attr('data-text-color-hover');

        jQuery( this ).css("background",hover_color_bg);
        jQuery( this ).css("color",hover_color_text);
    }).mouseout(function() {

        var color_text = jQuery( this ).attr('data-text-color');
        var color_bg = jQuery( this ).attr('data-bg');
        
        jQuery( this ).css("background",color_bg);
        jQuery( this ).css("color",color_text);
    });


    // WooCommerce Plus Minus Qty
    (function ($) {
        
        $(document).ready(function () {
            TheCrateQtyPlusMinus.init();
        });
        
        $(document.body).on('removed_from_cart updated_cart_totals', function () {
            TheCrateQtyPlusMinus.init();
        });

        var TheCrateQtyPlusMinus = {
            init: function () {
                var singleProductQtyBox = $('input.qty');
                
                if (singleProductQtyBox.length) {
               
                    $('#woosq-popup form.cart, form.cart, form.woocommerce-cart-form, .woobt-products').on( 'click', 'button.plus, button.minus', function() {
                        // Get current quantity values
                        var qty = $( this ).parent().find('.qty');
                        var val   = parseFloat(qty.val());
                        var max = parseFloat(qty.attr( 'max' ));
                        var min = parseFloat(qty.attr( 'min' ));
                        var step = parseFloat(qty.attr( 'step' ));
              
                        if(isNaN(val)) {
                            val = 0;
                        }


                        // Change the value if plus or minus
                        if ( $( this ).is( '.plus' ) ) {
                           if ( max && ( max <= val ) ) {
                              qty.val( max );
                           } else {
                              qty.val( val + step );
                           }
                        } else {
                           if ( min && ( min >= val ) ) {
                              qty.val( min );
                           } else if ( val > 1 ) {
                              qty.val( val - step );
                           }
                        }
                       jQuery('.button[name="update_cart"]').attr('aria-disabled', 'false').removeAttr('disabled');
                       // Product bundles plugin
                       jQuery('.bundle_button button').removeClass('disabled');
                    });
                }
            }
        };
        
    })(jQuery);


    // Navigation Dropdown Toggle to left when no space in the right side
    (function ($) {
        
        $(document).ready(function () {
            TheCrateDefaultNavMenu.init();
        });
        
        var TheCrateDefaultNavMenu = {
            init: function () {
                var $menuItems = $('#navbar ul.menu > li.menu-item-has-children');
                
                if ($menuItems.length) {
                    $menuItems.each(function (i) {
                        var thisItem = $(this),
                            menuItemPosition = thisItem.offset().left,
                            dropdownMenuItem = thisItem.find(' > ul'),
                            dropdownMenuWidth = dropdownMenuItem.outerWidth(),
                            menuItemFromLeft = $(window).width() - menuItemPosition;

                        var dropDownMenuFromLeft;
                        
                        if (thisItem.find('li.menu-item-has-children').length > 0) {
                            dropDownMenuFromLeft = menuItemFromLeft - dropdownMenuWidth;
                        }
                        
                        dropdownMenuItem.removeClass('thecrate-drop-down--right');
                        
                        if (menuItemFromLeft < dropdownMenuWidth || dropDownMenuFromLeft < dropdownMenuWidth) {
                            dropdownMenuItem.addClass('thecrate-drop-down--right');
                        }
                    });
                }
            }
        };
        
    })(jQuery);

    // Row Overlay mover outside column wpbakery
    (function ($) {
        
        $(document).ready(function () {
            TheCrateRowOverlay.init();
        });
        
        var TheCrateRowOverlay = {
            init: function () {
                var $rowOverlays = $('.themeslr-row-overlay');
                
                if ($rowOverlays.length) {
                    $rowOverlays.each(function (i) {
                        var thisItem = $(this),
                            thisItemParent_InRow = $(this).parent().parent().parent().parent(),
                            thisItemParent_InCol = $(this).parent().parent(),
                            thisItem_data_in_col = $(this).attr('data-inner-column');
                        
                        if (thisItem_data_in_col == 'yes') {
                            // in col
                            thisItem.prependTo(thisItemParent_InCol);
                        }else{
                            // in row
                            thisItem.prependTo(thisItemParent_InRow);
                        }
                    });
                }
            }
        };
        
    })(jQuery);

    // jQuery preloader
    jQuery(window).on("load", function(){
        jQuery( '.thecrate_preloader_holder' ).fadeOut( 1000, function() {
            jQuery( this ).fadeOut();
        });
    });

    jQuery(document).ready(function() {
        jQuery(document).on( "click", '.header-nav-actions .cart-contents-custom', function(event) {
            // Check if user is logged in (if stirjoyData is available)
            if (typeof stirjoyData !== 'undefined' && !stirjoyData.isLoggedIn) {
                event.preventDefault();
                var message = 'Please log in to view your cart.';
                if (confirm(message + '\n\nWould you like to go to the login page?')) {
                    window.location.href = stirjoyData.loginUrl;
                }
                return false;
            }
            
            if (jQuery("body").hasClass("thecrate_fixed_sidebar_cart_on")) {
                event.preventDefault();
                jQuery('.fixed-sidebar-menu-minicart').toggleClass('open');
                //jQuery('.fixed-sidebar-menu-overlay').addClass('visible');
                //jQuery('body').addClass('overflow-disabled');
            }
        });
    });

    // Other Scripts
    jQuery(document).ready(function() {

        if( jQuery( '.woocommerce div.product form.cart .variations select' ).length == 0 ||  jQuery( '.widget_archive select' ).length == 0 || jQuery( '.widget_categories select' ).length == 0 || jQuery( '.widget_text select' ).length == 0  || jQuery( '.wpcf7-select' ).length == 0 ) {
            jQuery('.woocommerce div.product form.cart .variations select, .widget_archive select, .widget_categories select, .widget_text select, .wpcf7-select').niceSelect();
        }

        jQuery('[data-toggle="tooltip"]').tooltip();

        // FIXED SEARCH FORM
        jQuery('.thecrate-search-icon').on( "click", function(event) {
            jQuery('.fixed-search-overlay').toggleClass('visible');
            event.preventDefault();
        });
        jQuery('.theme-search-closing-icon').on( "click", function(event) {
            jQuery('.fixed-search-overlay').removeClass('visible');
            event.preventDefault();
        });

        // FIXED SEARCH FORM
        jQuery('.thecrate-account-link-loggedin').on( "click", function(event) {
            jQuery('.thecrate-woocommerce-account-tabs').toggleClass('visible');
            event.preventDefault();
        });

        // Remove sidebar panel using ESC key
        jQuery(document).keyup(function(e) {
            if (e.keyCode == 27) { // escape key maps to keycode `27`
                jQuery('.fixed-search-overlay').removeClass('visible');
                jQuery('.fixed-sidebar-menu').removeClass('open');
                jQuery('.fixed-sidebar-menu-overlay').removeClass('visible');
                jQuery('body').removeClass('overflow-disabled');
            }
        });

        // Nav Burger side menu
        jQuery('.thecrate-nav-burger').on( "click", function(event) {
            event.preventDefault();
            jQuery('.fixed-sidebar-menu.fixed-sidebar-menu-burger-content').toggleClass('open');
            jQuery(this).parent().find('#navbar').toggleClass('hidden');
            jQuery('.fixed-sidebar-menu-overlay').addClass('visible');
            jQuery('body').addClass('overflow-disabled');
        });

        /* Click on Overlay - Hide Overline / Slide Back the Sidebar header */
        jQuery('.fixed-sidebar-menu-overlay').on( "click", function() {
            jQuery('.fixed-sidebar-menu').removeClass('open');
            jQuery(this).removeClass('visible');
            jQuery('body').removeClass('overflow-disabled');
        });
        /* Click on Overlay - Hide Overline / Slide Back the Sidebar header */
        jQuery('.fixed-sidebar-menu .icon-close').on( "click", function() {
            jQuery('.fixed-sidebar-menu').removeClass('open');
            jQuery('.fixed-sidebar-menu-overlay').removeClass('visible');
            jQuery('body').removeClass('overflow-disabled');
        });

        jQuery( ".fixed-sidebar-menu .menu-button" ).on( "click", function() {
            jQuery(this).parent().parent().parent().parent().toggleClass('open');
            jQuery(this).toggleClass('open');
        });


        if (jQuery(window).width() < 768) {
            var expand = '<span class="expand"><a class="action-expand"></a></span>';
            jQuery('.navbar-collapse .menu-item-has-children, .navbar-collapse .mega1column').append(expand);
            jQuery('header #navbar .sub-menu').hide();
            jQuery(".menu-item-has-children .expand a").on("click",function() {
                jQuery(this).parent().parent().find(' > ul').toggle();
                jQuery(this).toggleClass("show-menu");
            });
            jQuery(".mega1column .expand a").on("click",function() {
                jQuery(this).parent().parent().find(' > .cf-mega-menu').toggle();
                jQuery(this).toggleClass("show-menu");
            });
        }

    
        //Begin: Sticky Head
        jQuery(function(){
           if (jQuery('body').hasClass('is_nav_sticky')) {
                jQuery("#theme-main-head").sticky({
                    topSpacing:0
                });
           }
        });

        /*Begin: Products by category*/
        jQuery(".clients-container").owlCarousel({
            navigation      : false, // Show next and prev buttons
            pagination      : false,
            autoPlay        : true,
            slideSpeed      : 700,
            paginationSpeed : 700,
            itemsCustom : [
                [0,     1],
                [450,   2],
                [600,   2],
                [700,   3],
                [1000,  5],
                [1200,  5],
                [1400,  5],
                [1600,  5]
            ]
        });
     
        /*Begin: Testimonials slider*/
        jQuery(".post_thumbnails_slider").owlCarousel({
            navigation      : false, // Show next and prev buttons
            pagination      : false,
            autoPlay        : false,
            slideSpeed      : 700,
            paginationSpeed : 700,
            singleItem      : true
        });
        var owl = jQuery(".post_thumbnails_slider");
        jQuery( ".next" ).on( "click", function() {
            owl.trigger('owl.next');
        })
        jQuery( ".prev" ).on( "click", function() {
            owl.trigger('owl.prev');
        })
        /*End: Testimonials slider*/
        
        /*Begin: Testimonials slider*/
        jQuery(".testimonials_slider").owlCarousel({
            navigation      : true, // Show next and prev buttons
            pagination      : true,
            autoPlay        : false,
            slideSpeed      : 700,
            paginationSpeed : 700,
            singleItem      : true
        });
        /*End: Testimonials slider*/
        // browser window scroll (in pixels) after which the "back to top" link is shown
        var offset = 300,
        //browser window scroll (in pixels) after which the "back to top" link opacity is reduced
        offset_opacity = 1200,
        //duration of the top scrolling animation (in ms)
        scroll_top_duration = 700,
        //grab the "back to top" link
        $back_to_top = jQuery('.back-to-top');

        //hide or show the "back to top" link
        jQuery(window).scroll(function(){
            ( jQuery(this).scrollTop() > offset ) ? $back_to_top.addClass('themeslr-is-visible') : $back_to_top.removeClass('themeslr-is-visible themeslr-fade-out');
            if( jQuery(this).scrollTop() > offset_opacity ) { 
                $back_to_top.addClass('themeslr-fade-out');
            }
        });

        //smooth scroll to top
        $back_to_top.on('click', function(event){
            event.preventDefault();
            $('body,html').animate({
                scrollTop: 0 ,
                }, scroll_top_duration
            );
        });

        //Begin: Skills
        jQuery('.statistics').appear(function() {
            jQuery('.percentage').each(function(){
                var dataperc = jQuery(this).attr('data-perc');
                jQuery(this).find('.skill-count').delay(6000).countTo({
                    from: 0,
                    to: dataperc,
                    speed: 5000,
                    refreshInterval: 100
                });
            });
        }); 
        //End: Skills 
    })
} (jQuery) );