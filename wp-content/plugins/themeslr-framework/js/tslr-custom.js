 
( function() {
    var container, button, menu;

    container = document.getElementById( 'site-navigation' );
    if ( ! container ) {
        return;
    }

    button = container.getElementsByTagName( 'button' )[0];
    if ( 'undefined' === typeof button ) {
        return;
    }

    menu = container.getElementsByTagName( 'ul' )[0];

    // Hide menu toggle button if menu is empty and return early.
    if ( 'undefined' === typeof menu ) {
        button.style.display = 'none';
        return;
    }

    menu.setAttribute( 'aria-expanded', 'false' );

    if ( -1 === menu.className.indexOf( 'nav-menu' ) ) {
        menu.className += ' nav-menu';
    }

    button.onclick = function() {
        if ( -1 !== container.className.indexOf( 'toggled' ) ) {
            container.className = container.className.replace( ' toggled', '' );
            button.setAttribute( 'aria-expanded', 'false' );
            menu.setAttribute( 'aria-expanded', 'false' );
        } else {
            container.className += ' toggled';
            button.setAttribute( 'aria-expanded', 'true' );
            menu.setAttribute( 'aria-expanded', 'true' );
        }
    };
} )();


( function() {
    var is_webkit = navigator.userAgent.toLowerCase().indexOf( 'webkit' ) > -1,
        is_opera  = navigator.userAgent.toLowerCase().indexOf( 'opera' )  > -1,
        is_ie     = navigator.userAgent.toLowerCase().indexOf( 'msie' )   > -1;

    if ( ( is_webkit || is_opera || is_ie ) && document.getElementById && window.addEventListener ) {
        window.addEventListener( 'hashchange', function() {
            var element = document.getElementById( location.hash.substring( 1 ) );

            if ( element ) {
                if ( ! /^(?:a|select|input|button|textarea)$/i.test( element.tagName ) ) {
                    element.tabIndex = -1;
                }

                element.focus();
            }
        }, false );
    }
})();



// Sidebar Effects
 var SidebarMenuEffects = (function() {

    function hasParentClass( e, classname ) {
        if(e === document) return false;
        if( classie.has( e, classname ) ) {
            return true;
        }
        return e.parentNode && hasParentClass( e.parentNode, classname );
    }

    // http://coveroverflow.com/a/11381730/989439
    function mobilecheck() {
        var check = false;
        (function(a){if(/(android|ipad|playbook|silk|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))check = true})(navigator.userAgent||navigator.vendor||window.opera);
        return check;
    }

    function init() {

        var container = document.getElementById( 'st-container' ),
            buttons = Array.prototype.slice.call( document.querySelectorAll( '#st-trigger-effects > button' ) ),
            // event type (if mobile use touch events)
            eventtype = mobilecheck() ? 'touchstart' : 'click',
            resetMenu = function() {
                classie.remove( container, 'st-menu-open' );
            },
            bodyClickFn = function(evt) {
                if( !hasParentClass( evt.target, 'st-menu' ) ) {
                    resetMenu();
                    document.removeEventListener( eventtype, bodyClickFn );
                }
            };

        buttons.forEach( function( el, i ) {
            var effect = el.getAttribute( 'data-effect' );

            el.addEventListener( eventtype, function( ev ) {
                ev.stopPropagation();
                ev.preventDefault();
                container.className = 'st-container'; // clear
                classie.add( container, effect );
                setTimeout( function() {
                    classie.add( container, 'st-menu-open' );
                }, 25 );
                document.addEventListener( eventtype, bodyClickFn );
            });
        } );

    }

    init();

})();


(function ($) {
  'use strict';

  jQuery( document ).ready(function() {



    /* 
    ###############################################
    Bootstrap Carousel Touch Slider.
    ############################################### 
    */
    $.fn.bsTouchSlider = function ( options ) {
        var carousel = jQuery( ".carousel" );
        return this.each( function ( ) {

            function doAnimations( elems ) {
                //Cache the animationend event in a variable
                var animEndEv = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
                elems.each( function ( ) {
                    var $this = jQuery( this ),
                        $animationType = $this.data( 'animation' );
                    $this.addClass( $animationType ).one( animEndEv, function ( ) {
                        $this.removeClass( $animationType );
                    } );
                } );
            }

            //Variables on page load
            var $firstAnimatingElems = carousel.find( '.item:first' ).find( "[data-animation ^= 'animated']" );
            //Initialize carousel
            carousel.carousel( );
            //Animate captions in first slide on page load
            doAnimations( $firstAnimatingElems );
            //Other slides to be animated on carousel slide event
            carousel.on( 'slide.bs.carousel', function ( e ) {
                var $animatingElems = jQuery( e.relatedTarget ).find( "[data-animation ^= 'animated']" );
                doAnimations( $animatingElems );
            } );
            //swipe initial 
            jQuery( ".carousel .carousel-inner" ).swipe( {
                swipeLeft: function ( event, direction, distance, duration, fingerCount ) {
                    this.parent( ).carousel( 'next' );
                },
                swipeRight: function ( ) {
                    this.parent( ).carousel( 'prev' );
                },
                threshold: 0
            } );

        } );
    };

    jQuery('#bootstrap-touch-slider').bsTouchSlider();



    if ( jQuery( ".themeslr-vimeo-popup" ).length ) {
        jQuery(".themeslr-vimeo-popup").magnificPopup({
            type:"iframe",
            disableOn: 700,
            removalDelay: 160,
            preloader: false,
            fixedContentPos: false
        });
    }


    if ( jQuery( ".tslr-carousel-everything-shortcode" ).length ) {
        jQuery(".tslr-carousel-everything-shortcode").each(function(){
            var slide_id = jQuery(this).attr('id');
            setTimeout(function() {
                jQuery("#"+slide_id).owlCarousel({
                    navigation      : false, // Show next and prev buttons
                    pagination      : true,
                    autoPlay        : false,
                    slideSpeed      : 700,
                    paginationSpeed : 700,
                    itemsCustom : [
                        [0,     1],
                        [450,   1],
                        [600,   1],
                        [700,   1],
                        [1000,  1],
                        [1200,  1],
                        [1400,  1],
                        [1600,  1]
                    ]
                });
            }, 100);
        });

    }

    /*
    * ||||||||||||||||||||||||||||||||||||||||||||||||||||||||-> TESTIMONIALS01 SLIDER (Shortcode)
    */
    jQuery(".testimonials-container").owlCarousel({
        navigation      : false, // Show next and prev buttons
        pagination      : true,
        autoPlay        : false,
        slideSpeed      : 700,
        paginationSpeed : 700,
        itemsCustom : [
            [0,     1],
            [450,   1],
            [600,   2],
            [700,   2],
            [1000,  2],
            [1200,  2],
            [1400,  2],
            [1600,  2]
        ]
    });
    jQuery(".members-container").owlCarousel({
        navigation      : false, // Show next and prev buttons
        pagination      : false,
        autoPlay        : false,
        slideSpeed      : 700,
        paginationSpeed : 700,
        singleItem      : true,
        itemsCustom : [
            [0,     1],
            [450,   1],
            [600,   1],
            [700,   1],
            [1000,  1],
            [1200,  1],
            [1400,  1],
            [1600,  1]
        ]
    });

    // setTimeout(function() {
        jQuery(".testimonials-container-1").owlCarousel({
            navigation      : false, // Show next and prev buttons
            pagination      : true,
            autoPlay        : true,
            slideSpeed      : 700,
            paginationSpeed : 700,
            itemsCustom : [
                [0,     1],
                [450,   1],
                [600,   1],
                [700,   1],
                [1000,  1],
                [1200,  1],
                [1400,  1],
                [1600,  1]
            ]
        });
    // }, 1000);


    jQuery(".testimonials-container-2").owlCarousel({
        navigation      : false, // Show next and prev buttons
        pagination      : false,
        autoPlay        : false,
        slideSpeed      : 700,
        paginationSpeed : 700,
        itemsCustom : [
            [0,     1],
            [450,   1],
            [600,   2],
            [700,   2],
            [1000,  2],
            [1200,  2],
            [1400,  2],
            [1600,  2]
        ]
    });
    jQuery(".testimonials-container-3").owlCarousel({
        navigation      : false, // Show next and prev buttons
        pagination      : false,
        autoPlay        : false,
        slideSpeed      : 700,
        paginationSpeed : 700,
        itemsCustom : [
            [0,     1],
            [450,   1],
            [600,   2],
            [700,   2],
            [1000,  3],
            [1200,  3],
            [1400,  3],
            [1600,  3]
        ]
    });

  jQuery(".tslr_tweets_slider").owlCarousel({
      navigation      : false, // Show next and prev buttons
      pagination      : false,
      autoPlay        : true,
      slideSpeed      : 700,
      paginationSpeed : 700,
      itemsCustom : [
          [0,     1],
          [450,   1],
          [600,   1],
          [700,   1],
          [1000,  1],
          [1200,  1],
          [1400,  1],
          [1600,  1]
      ]
  });

    /*
    * ||||||||||||||||||||||||||||||||||||||||||||||||||||||||-> TESTIMONIALS02 SLIDER (Shortcode)
    */
    jQuery(".testimonials02-container").owlCarousel({
      navigation      : false, // Show next and prev buttons
      pagination      : true,
      autoPlay        : true,
      slideSpeed      : 700,
      paginationSpeed : 700,
      navigationText  : ["<i class='icon-arrow-left'></i>","<i class='icon-arrow-right'></i>"],
      // navigationText  : ["",""],
      singleItem      : true
    });

    /*
    * ||||||||||||||||||||||||||||||||||||||||||||||||||||||||-> TWEETS SLIDER (Shortcode)
    */
    jQuery(".mt_tweets_slider").owlCarousel({
      navigation      : false, // Show next and prev buttons
      pagination      : false,
      autoPlay        : false,
      slideSpeed      : 700,
      paginationSpeed : 700,
      singleItem      : true
    });



    /*
    * ||||||||||||||||||||||||||||||||||||||||||||||||||||||||-> CLIENTS SLIDER (Shortcode)
    */
    jQuery(".clients_container_shortcode-1").owlCarousel({
        navigation      : false, // Show next and prev buttons
        navigationText      : ["<i class='fa fa-long-arrow-left'></i>","<i class='fa fa-long-arrow-right'></i>"],
        pagination      : false,
        autoPlay        : false,
        slideSpeed      : 700,
        paginationSpeed : 700,
        autoPlay : true,
        autoPlayTimeout:10000,
        autoPlayHoverPause:true,
        itemsCustom : [
            [0,     1],
            [450,   1],
            [600,   1],
            [700,   1],
            [1000,  1],
            [1200,  1],
            [1400,  1],
            [1600,  1]
        ]
    });
    jQuery(".clients_container_shortcode-2").owlCarousel({
        navigation      : false, // Show next and prev buttons
        navigationText      : ["<i class='fa fa-long-arrow-left'></i>","<i class='fa fa-long-arrow-right'></i>"],
        pagination      : false,
        autoPlay        : false,
        slideSpeed      : 700,
        paginationSpeed : 700,
        autoPlay : true,
        autoPlayTimeout:10000,
        autoPlayHoverPause:true,
        itemsCustom : [
            [0,     1],
            [450,   1],
            [600,   2],
            [700,   2],
            [1000,  2],
            [1200,  2],
            [1400,  2],
            [1600,  2]
        ]
    });
    jQuery(".clients_container_shortcode-3").owlCarousel({
        navigation      : false, // Show next and prev buttons
        navigationText      : ["<i class='fa fa-long-arrow-left'></i>","<i class='fa fa-long-arrow-right'></i>"],
        pagination      : false,
        autoPlay        : false,
        slideSpeed      : 700,
        paginationSpeed : 700,
        autoPlay : true,
        autoPlayTimeout:10000,
        autoPlayHoverPause:true,
        itemsCustom : [
            [0,     1],
            [450,   1],
            [600,   2],
            [700,   2],
            [1000,  3],
            [1200,  3],
            [1400,  3],
            [1600,  3]
        ]
    });

    jQuery(".clients_container_shortcode-4").owlCarousel({
        navigation      : false, // Show next and prev buttons
        navigationText      : ["<i class='fa fa-long-arrow-left'></i>","<i class='fa fa-long-arrow-right'></i>"],
        pagination      : false,
        autoPlay        : false,
        slideSpeed      : 700,
        paginationSpeed : 700,
        autoPlay : true,
        autoPlayTimeout:10000,
        autoPlayHoverPause:true,
        itemsCustom : [
            [0,     1],
            [450,   1],
            [600,   2],
            [700,   3],
            [1000,  4],
            [1200,  4],
            [1400,  4],
            [1600,  4]
        ]
    });


    jQuery(".clients_container_shortcode-5").owlCarousel({
        navigation      : false, // Show next and prev buttons
        navigationText      : ["<i class='fa fa-long-arrow-left'></i>","<i class='fa fa-long-arrow-right'></i>"],
        pagination      : false,
        autoPlay        : false,
        slideSpeed      : 700,
        paginationSpeed : 700,
        autoPlay : true,
        autoPlayTimeout:10000,
        autoPlayHoverPause:true,
        itemsCustom : [
            [0,     1],
            [450,   1],
            [600,   2],
            [700,   3],
            [1000,  4],
            [1200,  5],
            [1400,  5],
            [1600,  5]
        ]
    });

    jQuery(".clients_container_shortcode-6").owlCarousel({
        navigation      : false, // Show next and prev buttons
        navigationText      : ["<i class='fa fa-long-arrow-left'></i>","<i class='fa fa-long-arrow-right'></i>"],
        pagination      : false,
        autoPlay        : false,
        slideSpeed      : 700,
        paginationSpeed : 700,
        autoPlay : true,
        autoPlayTimeout:10000,
        autoPlayHoverPause:true,
        itemsCustom : [
            [0,     2],
            [450,   2],
            [600,   3],
            [700,   3],
            [1000,  4],
            [1200,  6],
            [1400,  6],
            [1600,  6]
        ]
    });

    /*
    * ||||||||||||||||||||||||||||||||||||||||||||||||||||||||-> SERVICES SLIDER (Shortcode)
    */

    // SET LEFT SIDE height of RIGHT SIDE
    jQuery( '.right-side' ).ready(function() {
      var right_side_height = jQuery('.right-side').height();
      jQuery( '.left-side' ).height( right_side_height )
    });

  });
} (jQuery) )