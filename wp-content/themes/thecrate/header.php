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

    <?php if ( class_exists( 'WooCommerce' ) && thecrate_redux('thecrate_fixed_sidebar_cart') == true ) { ?>
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
    <?php } ?>


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