<?php
/**
 * Stirjoy Child Theme Functions
 * 
 * @package Stirjoy_Child
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue parent and child theme styles
 */
function stirjoy_child_enqueue_styles() {
    // Enqueue parent theme stylesheet
    wp_enqueue_style( 'thecrate-parent-style', get_template_directory_uri() . '/style.css' );
    
    // Enqueue child theme stylesheet
    wp_enqueue_style( 'stirjoy-child-style', 
        get_stylesheet_directory_uri() . '/style.css',
        array( 'thecrate-parent-style' ),
        wp_get_theme()->get('Version')
    );
    
    // Enqueue wholesale CSS on wholesale page
    if ( is_page_template( 'template-wholesale.php' ) ) {
        wp_enqueue_style( 'stirjoy-wholesale-style',
            get_stylesheet_directory_uri() . '/assets/css/wholesale.css',
            array( 'stirjoy-child-style' ),
            wp_get_theme()->get('Version')
        );
    }
    
    // Enqueue child theme JavaScript
    wp_enqueue_script( 'stirjoy-child-script',
        get_stylesheet_directory_uri() . '/assets/js/stirjoy.js',
        array( 'jquery' ),
        wp_get_theme()->get('Version'),
        true
    );

    // Localize script for AJAX
    wp_localize_script( 'stirjoy-child-script', 'stirjoyData', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'stirjoy_nonce' ),
    ));

    wp_dequeue_script( 'thecrate-custom' );
    wp_deregister_script( 'thecrate-custom' );
    wp_enqueue_script( 'thecrate-custom', 
        get_stylesheet_directory_uri() . '/assets/js/thecrate-custom.js', 
        array( 'jquery' ),
        wp_get_theme()->get('Version'),
        true
    );

    wp_dequeue_script( 'subscriptions-for-woocommerce' );
    wp_deregister_script( 'subscriptions-for-woocommerce' );
    wp_enqueue_script( 'subscriptions-for-woocommerce', 
        get_stylesheet_directory_uri() . '/assets/js/subscriptions-for-woocommerce-public.js', 
        array( 'jquery' ),
        wp_get_theme()->get('Version'),
        true
    );
    wp_localize_script(
        'subscriptions-for-woocommerce',
        'sfw_public_param',
        array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'cart_url' => wc_get_cart_url(),
            'sfw_public_nonce'    => wp_create_nonce( 'wps_sfw_public_nonce' ),
        )
    );
}
add_action( 'wp_enqueue_scripts', 'stirjoy_child_enqueue_styles', 20 );

/**
 * Include custom functionality files
 */
if ( file_exists( get_stylesheet_directory() . '/inc/cart-confirmation.php' ) ) {
    require_once get_stylesheet_directory() . '/inc/cart-confirmation.php';
}

if ( file_exists( get_stylesheet_directory() . '/inc/product-meta.php' ) ) {
    require_once get_stylesheet_directory() . '/inc/product-meta.php';
}

if ( file_exists( get_stylesheet_directory() . '/inc/wholesale.php' ) ) {
    require_once get_stylesheet_directory() . '/inc/wholesale.php';
}

/**
 * Customize WooCommerce
 */
function stirjoy_child_woocommerce_setup() {
    // Add theme support for WooCommerce
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'stirjoy_child_woocommerce_setup' );

/**
 * Add custom body classes
 */
function stirjoy_child_body_classes( $classes ) {
    if ( is_shop() || is_product_category() || is_product_tag() ) {
        $classes[] = 'stirjoy-shop-page';
    }
    return $classes;
}
add_filter( 'body_class', 'stirjoy_child_body_classes' );

/**
 * Modify product loop to add cart sidebar
 */
function stirjoy_add_cart_sidebar() {
    if ( is_shop() || is_product_category() || is_product_tag() ) {
        get_template_part( 'woocommerce/cart-sidebar' );
    }
}
add_action( 'woocommerce_after_main_content', 'stirjoy_add_cart_sidebar', 5 );

function register_custom_sidebars1() {
    register_sidebar(array(
        'name' => 'Footer Row 1 Column 1',
        'id' => 'footer_row_1_1',
        'description' => 'Footer first row, first column',
        'before_widget' => '<div class="widget">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="widget-title">',
        'after_title' => '</h4>',
    ));
}
add_action('widgets_init', 'register_custom_sidebars1');

function register_custom_sidebars2() {
    register_sidebar(array(
        'name' => 'Footer Row 1 Column 2',
        'id' => 'footer_row_1_2',
        'description' => 'Footer first row, second column',
        'before_widget' => '<div class="widget">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="widget-title">',
        'after_title' => '</h4>',
    ));
}
add_action('widgets_init', 'register_custom_sidebars2');

function register_custom_sidebars3() {
    register_sidebar(array(
        'name' => 'Footer Row 1 Column 3',
        'id' => 'footer_row_1_3',
        'description' => 'Footer first row, third column',
        'before_widget' => '<div class="widget">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="widget-title">',
        'after_title' => '</h4>',
    ));
}
add_action('widgets_init', 'register_custom_sidebars3');

function register_custom_sidebars4() {
    register_sidebar(array(
        'name' => 'Footer Row 1 Column 4',
        'id' => 'footer_row_1_4',
        'description' => 'Footer first row, fourth column',
        'before_widget' => '<div class="widget">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="widget-title">',
        'after_title' => '</h4>',
    ));
}
add_action('widgets_init', 'register_custom_sidebars4');

// Add short description to product archive pages
function woocommerce_template_loop_product_short_description() {
    global $product;
    
    $short_description = $product->get_short_description();
    if ( empty( $short_description ) ) return;
    $limited_description = wp_trim_words( $short_description, 5, '...' );
    
    echo '<div class="product-short-description">';
    echo wp_kses_post( $limited_description );
    echo '</div>';
}
add_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_short_description', 15 );

// Remove price from product archive pages
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );

// Add quantity input before add to cart button on archive pages
function add_quantity_field_to_archive() {
    global $product;
    
    echo '<div class="qty-block">';
    echo '<a href="' . $product->get_permalink() . '">View Details</a>';

    if ( ! $product->is_purchasable() || ! $product->is_in_stock() ) return;
    
    if ( $product->is_type( 'simple' ) ) {
        woocommerce_quantity_input( array(
            'min_value'   => 0,
            'max_value'   => $product->get_max_purchase_quantity(),
            'input_value' => 0,
        ) );
    }

    echo '</div>';
}
add_action( 'woocommerce_after_shop_loop_item', 'add_quantity_field_to_archive', 8 );

// Add Free Shipping and Free Gift progress bar to the mini cart
function add_free_shipping_bar_to_mini_cart() {
    $freeShippingThreshold = 80;
    $subtotal = WC()->cart->get_subtotal();
    $rest = $freeShippingThreshold - $subtotal;
    $caption2 = 'Complete!';
    $width = 100;
    if($rest > 0) {
        $caption2 = '$' . $rest . ' to go';
        $width = ($subtotal / $freeShippingThreshold) * 100;
    }

    echo '<div class="free-shipping-bar" data-threshold = "' . $freeShippingThreshold . '">' .
            '<div class="captions">' .
                '<div class="caption1">' .
                    '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-truck w-3 h-3 mr-1 text-primary" aria-hidden="true"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path><path d="M15 18H9"></path><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"></path><circle cx="17" cy="18" r="2"></circle><circle cx="7" cy="18" r="2"></circle></svg>' .
                    '<span>Free shipping at $' . $freeShippingThreshold . '</span>' .
                '</div>' .
                '<div class="caption2">' . $caption2 . '</div>' .
            '</div>' .
            '<div class="bar"><div style="width: ' . $width . '%;"></div></div>' .
         '</div>';

    $freeGiftThreshold = 120;
    $rest = $freeGiftThreshold - $subtotal;
    $caption2 = 'Complete!';
    $width = 100;
    if($rest > 0) {
        $caption2 = '$' . $rest . ' to go';
        $width = ($subtotal / $freeGiftThreshold) * 100;
    }

    echo '<div class="free-gift-bar" data-threshold = "' . $freeGiftThreshold . '">' .
            '<div class="captions">' .
                '<div class="caption1">' .
                    '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-gift w-3 h-3 mr-1 text-accent" aria-hidden="true"><rect x="3" y="8" width="18" height="4" rx="1"></rect><path d="M12 8v13"></path><path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"></path><path d="M7.5 8a2.5 2.5 0 0 1 0-5A4.8 8 0 0 1 12 8a4.8 8 0 0 1 4.5-5 2.5 2.5 0 0 1 0 5"></path></svg>' .
                    '<span>Free gift at $' . $freeGiftThreshold . '</span>' .
                '</div>' .
                '<div class="caption2">' . $caption2 . '</div>' .
            '</div>' .
            '<div class="bar"><div style="width: ' . $width . '%;"></div></div>' .
         '</div>';
}
add_action( 'woocommerce_widget_shopping_cart_before_buttons', 'add_free_shipping_bar_to_mini_cart', 10 );

function update_customer_info() {
    $customer_name = isset($_POST['customer_name']) ? sanitize_text_field($_POST['customer_name']) : '';
    $customer_email = isset($_POST['customer_email']) ? sanitize_text_field($_POST['customer_email']) : '';
    $customer_phone = isset($_POST['customer_phone']) ? sanitize_text_field($_POST['customer_phone']) : '';

    $customer_name_arr = explode(' ', $customer_name);
    $customer_firstname = $customer_name_arr[0];
    $customer_lastname = '';
    for($i = 1; $i < count($customer_name_arr); $i ++) {
        $customer_lastname .= $customer_name_arr[$i] . ' ';    
    }
    $customer_lastname = trim($customer_lastname);

    // WordPress user info
    $user_id = get_current_user_id();
    $userdata = array(
        'ID'         => $user_id,
        'first_name' => $customer_firstname,
        'last_name'  => $customer_lastname,
        'user_email' => $customer_email,
    );
    wp_update_user( $userdata );

    // WooCommerce billing details
    update_user_meta( $user_id, 'billing_first_name', $customer_firstname );
    update_user_meta( $user_id, 'billing_last_name', $customer_lastname );
    update_user_meta( $user_id, 'billing_phone', $customer_phone );
    update_user_meta( $user_id, 'billing_email', $customer_email );

    $response = array(
        'success' => 'ok'
    );

    wp_send_json_success( $response );
}
add_action( 'wp_ajax_update_customer_info', 'update_customer_info' );