<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php esc_html(bloginfo( 'charset' )); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="//gmpg.org/xfn/11">
    <link rel="pingback" href="<?php esc_url(bloginfo( 'pingback_url' )); ?>">
    <?php if ( ! function_exists( 'has_site_icon' ) || ! has_site_icon() ) { ?>
        <link rel="shortcut icon" href="<?php echo esc_url(thecrate_redux('thecrate_favicon', 'url')); ?>">
    <?php } ?>
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php 
        if ( function_exists( 'wp_body_open' ) ) {
            wp_body_open();
        }

        // PAGE PRELOADER
        if (thecrate_redux('thecrate_preloader_status')) {
            echo '<div class="thecrate_preloader_holder '.esc_attr(thecrate_redux('thecrate_preloader_animation')).'">'.thecrate_loader_animation().'</div>';
        }
        
        $normal_headers = array('header1', 'header2', 'header3');
        $header_custom_variant = get_post_meta( get_the_ID(), 'themeslr_header_custom_variant', true );
        $header_layout = thecrate_redux('thecrate_header_layout');
        if (isset($header_custom_variant) && $header_custom_variant != '') {
            $header_layout = $header_custom_variant;
        }
    ?>

    
    <?php if(thecrate_redux('thecrate_header_fixed_sidebar_menu_status') == true || thecrate_redux('thecrate_fixed_sidebar_cart') == true ){ ?>
        <!-- Fixed Sidebar Overlay -->
        <div class="fixed-sidebar-menu-overlay"></div>
    <?php } ?>
    
    <?php if(thecrate_redux('thecrate_header_fixed_sidebar_menu_status') == true){ ?>
        <!-- Fixed Sidebar Menu -->
        <div class="relative fixed-sidebar-menu-holder header7">
            <div class="fixed-sidebar-menu fixed-sidebar-menu-burger-content">
                <!-- Close Sidebar Menu + Close Overlay -->
                <img class="icon-close" src="<?php echo get_template_directory_uri();?>/images/svg/burger-x-close-dark.svg" alt="<?php echo esc_attr__('Close', 'thecrate'); ?>" />
                <!-- Sidebar Menu Holder -->
                <div class="header7">
                    <!-- RIGHT SIDE -->
                    <div class="left-side">
                        <?php dynamic_sidebar( thecrate_redux('thecrate_header_fixed_sidebar_menu_select_sidebar') ); ?>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php /*if ( class_exists( 'WooCommerce' ) && thecrate_redux('thecrate_fixed_sidebar_cart') == true ) { ?>
        <!-- Fixed Sidebar Menu -->
        <div class="relative fixed-sidebar-menu-holder header7">
            <div class="fixed-sidebar-menu fixed-sidebar-menu-minicart">
                <!-- Close Sidebar Menu + Close Overlay -->
                <img class="icon-close" src="<?php echo get_template_directory_uri();?>/images/svg/burger-x-close-dark.svg" alt="<?php echo esc_attr__('Close', 'thecrate'); ?>" />
                <!-- Sidebar Menu Holder -->
                <div class="header7">
                    <!-- RIGHT SIDE -->
                    <div class="left-side">
                        <div class="header_mini_cart">
                            <?php the_widget( 'WC_Widget_Cart', array( 'title' => esc_html__('Your Cart:', 'thecrate') ) ); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php }*/ ?>


    <!-- Mobile Menu Full Screen -->
    <div class="stirjoy-mobile-menu" id="stirjoy-mobile-menu">
        <div class="stirjoy-mobile-menu-header">
            <div class="stirjoy-mobile-menu-logo">
                <?php if(thecrate_redux('thecrate_logo','url')){ ?>
                    <a href="<?php echo esc_url(get_site_url()); ?>">
                        <img src="<?php echo esc_url(thecrate_redux('thecrate_logo','url')); ?>" alt="<?php echo esc_attr(get_bloginfo()); ?>">
                    </a>
                <?php } else { ?>
                    <a href="<?php echo esc_url(get_site_url()); ?>"><?php echo get_bloginfo(); ?></a>
                <?php } ?>
            </div>
            <button type="button" class="stirjoy-mobile-menu-close" aria-label="Close menu">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        
        <div class="stirjoy-mobile-menu-content">
            <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="stirjoy-mobile-menu-cta">
                CUSTOMIZE YOUR BOX
            </a>
            
            <nav class="stirjoy-mobile-menu-nav">
                <a href="<?php echo esc_url( wc_get_page_permalink( 'home' ) ); ?>/#how-it-works" class="stirjoy-mobile-menu-link">HOW IT WORKS</a>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'home' ) ); ?>/#our-story" class="stirjoy-mobile-menu-link">OUR STORY</a>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'my account' ) ); ?>" class="stirjoy-mobile-menu-link">MY ACCOUNT</a>
                <a href="<?php echo esc_url( get_permalink( get_page_by_path( 'delivery' ) ) ?: '#' ); ?>" class="stirjoy-mobile-menu-link">FAQs</a>
            </nav>
        </div>
        
        <div class="stirjoy-mobile-menu-footer">
            <a href="https://www.tiktok.com/@stirjoy" target="_blank" rel="noopener" class="stirjoy-mobile-menu-social">
                <img src="<?php echo esc_url(stirjoy_get_image_url('Group (1).png')); ?>" alt="TIKTOK">
                <span>TIKTOK</span>
            </a>
            <a href="https://www.instagram.com/stirjoy" target="_blank" rel="noopener" class="stirjoy-mobile-menu-social">
                <img src="<?php echo esc_url(stirjoy_get_image_url('Group (2).png')); ?>" alt="INSTAGRAM">
                <span>INSTAGRAM</span>
            </a>
        </div>
    </div>

    <!-- PAGE #page -->
    <div id="page" class="hfeed site">
        <?php
            $page_slider = get_post_meta( get_the_ID(), 'select_revslider_shortcode', true );
            if (in_array($header_layout, $normal_headers)){
                // Header template variant
                echo thecrate_current_header_template();
                // Revolution slider
                if (!empty($page_slider)) {
                    echo '<div class="theme_header_slider">';
                    echo do_shortcode('[rev_slider '.esc_attr($page_slider).']');
                    echo '</div>';
                }
            }else{
                echo thecrate_current_header_template();
            }