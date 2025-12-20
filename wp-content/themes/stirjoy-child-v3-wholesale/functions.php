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
 * Helper function to get image URL from images folder
 * Available globally across all template files
 * CRITICAL: Forces HTTPS to prevent mixed content warnings
 */
function stirjoy_get_image_url($filename) {
    $image_path = get_stylesheet_directory_uri() . '/images/home page/' . $filename;
    // Force HTTPS if site is using HTTPS
    if ( is_ssl() ) {
        $image_path = str_replace( 'http://', 'https://', $image_path );
    }
    return $image_path;
}

/**
 * Force all image URLs to use HTTPS to prevent mixed content warnings
 * This fixes HTTP images being loaded on HTTPS pages
 */
function stirjoy_force_https_images( $url ) {
    // Only force HTTPS if site is using HTTPS
    if ( is_ssl() && strpos( $url, 'http://' ) === 0 ) {
        $url = str_replace( 'http://', 'https://', $url );
    }
    return $url;
}
add_filter( 'wp_get_attachment_url', 'stirjoy_force_https_images', 10, 1 );
add_filter( 'wp_get_attachment_image_src', function( $image ) {
    if ( is_ssl() && is_array( $image ) && isset( $image[0] ) ) {
        $image[0] = str_replace( 'http://', 'https://', $image[0] );
    }
    return $image;
}, 10, 1 );
add_filter( 'the_content', function( $content ) {
    if ( is_ssl() ) {
        $host = parse_url( home_url(), PHP_URL_HOST );
        $content = str_replace( 'http://' . $host, 'https://' . $host, $content );
    }
    return $content;
}, 999 );
add_filter( 'option_siteurl', function( $value ) {
    if ( is_ssl() && strpos( $value, 'http://' ) === 0 ) {
        $value = str_replace( 'http://', 'https://', $value );
    }
    return $value;
}, 10, 1 );
add_filter( 'option_home', function( $value ) {
    if ( is_ssl() && strpos( $value, 'http://' ) === 0 ) {
        $value = str_replace( 'http://', 'https://', $value );
    }
    return $value;
}, 10, 1 );

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
    
    // Enqueue My Account CSS on My Account page
    if ( is_account_page() ) {
        wp_enqueue_style( 'stirjoy-my-account-style',
            get_stylesheet_directory_uri() . '/assets/css/my-account.css',
            array( 'stirjoy-child-style' ),
            wp_get_theme()->get('Version')
        );
    }
    
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
    $login_url = get_permalink( get_option('woocommerce_myaccount_page_id') );
    $checkout_url = wc_get_checkout_url();
    
    // Social login credentials (these should be set in wp-config.php or theme options)
    $google_client_id = defined( 'STIRJOY_GOOGLE_CLIENT_ID' ) ? STIRJOY_GOOGLE_CLIENT_ID : '';
    $facebook_app_id = defined( 'STIRJOY_FACEBOOK_APP_ID' ) ? STIRJOY_FACEBOOK_APP_ID : '';
    $apple_client_id = defined( 'STIRJOY_APPLE_CLIENT_ID' ) ? STIRJOY_APPLE_CLIENT_ID : '';
    
    wp_localize_script( 'stirjoy-child-script', 'stirjoyData', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'stirjoy_nonce' ),
        'isLoggedIn' => is_user_logged_in(),
        'loginUrl' => $login_url ? $login_url : wp_login_url(),
        'checkoutUrl' => $checkout_url ? $checkout_url : '',
        'googleClientId' => $google_client_id,
        'facebookAppId' => $facebook_app_id,
        'appleClientId' => $apple_client_id,
    ));
    
    // Localize script for checkout page with Stripe nonce
    if ( is_checkout() ) {
        wp_localize_script( 'stirjoy-child-script', 'stirjoy_checkout_params', array(
            'stripe_nonce' => wp_create_nonce( 'stirjoy_stripe_payment_method' ),
            'ajax_url' => admin_url( 'admin-ajax.php' ),
        ) );
    }

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
 * Remove default order review hook since we're rendering it manually in our custom checkout template
 * This prevents duplicate rendering of the order summary
 */
function stirjoy_remove_default_order_review() {
    remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 10 );
}
add_action( 'woocommerce_init', 'stirjoy_remove_default_order_review', 20 );

/**
 * Remove coupon form from checkout page
 */
function stirjoy_remove_checkout_coupon_form() {
    if ( is_checkout() ) {
        remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
    }
}
add_action( 'wp', 'stirjoy_remove_checkout_coupon_form', 10 );

/**
 * Remove notices wrapper from checkout page
 */
function stirjoy_remove_checkout_notices() {
    if ( is_checkout() ) {
        remove_action( 'woocommerce_before_checkout_form', 'woocommerce_output_all_notices', 10 );
        remove_action( 'woocommerce_before_checkout_form_cart_notices', 'woocommerce_output_all_notices', 10 );
    }
}
add_action( 'wp', 'stirjoy_remove_checkout_notices', 10 );

/**
 * Fix AJAX order review fragments to extract only the table from the returned template
 * This prevents the wrapper from being nested inside stirjoy-box-summary during AJAX updates
 */
function stirjoy_fix_order_review_ajax_fragments( $fragments ) {
    // WooCommerce AJAX returns the entire review-order.php template (with wrapper)
    // but tries to replace only .woocommerce-checkout-review-order-table
    // We need to extract only the table element from the returned HTML
    if ( isset( $fragments['.woocommerce-checkout-review-order-table'] ) ) {
        $full_html = $fragments['.woocommerce-checkout-review-order-table'];
        
        // If the returned HTML contains the wrapper, extract only the table
        if ( strpos( $full_html, 'stirjoy-order-summary-wrapper' ) !== false ) {
            // Use DOMDocument to properly extract the table element
            $dom = new DOMDocument();
            @$dom->loadHTML( '<?xml encoding="UTF-8">' . $full_html );
            $xpath = new DOMXPath( $dom );
            
            // Find the table with the class woocommerce-checkout-review-order-table
            $tables = $xpath->query( '//table[contains(@class, "woocommerce-checkout-review-order-table")]' );
            
            if ( $tables->length > 0 ) {
                $table = $tables->item( 0 );
                // Get the table HTML
                $table_html = $dom->saveHTML( $table );
                // Clean up the HTML (remove XML declaration if present)
                $table_html = preg_replace( '/^<\?xml[^>]*\?>/', '', $table_html );
                $fragments['.woocommerce-checkout-review-order-table'] = trim( $table_html );
            } else {
                // Fallback: use regex to extract the table
                preg_match( '/<table[^>]*class="[^"]*woocommerce-checkout-review-order-table[^"]*"[^>]*>.*?<\/table>/s', $full_html, $matches );
                if ( ! empty( $matches[0] ) ) {
                    $fragments['.woocommerce-checkout-review-order-table'] = $matches[0];
                }
            }
        }
    }
    
    return $fragments;
}
add_filter( 'woocommerce_update_order_review_fragments', 'stirjoy_fix_order_review_ajax_fragments', 10, 1 );

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
 * Note: Cart sidebar is now rendered directly in archive-product.php after the cart bar
 * This function is kept for backward compatibility but does nothing
 */
function stirjoy_add_cart_sidebar() {
    // Cart sidebar is now rendered directly in archive-product.php template
    // after the cart bar for better positioning
    return;
}
// Removed hook - cart sidebar is now in template
// add_action( 'woocommerce_after_main_content', 'stirjoy_add_cart_sidebar', 5 );

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

// Add short description to product archive pages (only for non-customize pages)
function woocommerce_template_loop_product_short_description() {
    // Skip on customize your box page
    if ( is_shop() && isset( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], 'shop' ) !== false ) {
        return;
    }
    
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

// Add quantity input before add to cart button on archive pages (only for non-customize pages)
function add_quantity_field_to_archive() {
    // Skip on customize your box page (our custom template handles everything)
    if ( is_shop() && isset( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], 'shop' ) !== false ) {
        return;
    }
    
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

/**
 * AJAX handler for delivery calendar navigation
 */
function stirjoy_get_calendar_month() {
    check_ajax_referer( 'stirjoy_nonce', 'nonce' );
    
    // Use WordPress timezone for default month/year
    $month = isset( $_POST['month'] ) ? intval( $_POST['month'] ) : intval( current_time( 'n' ) );
    $year = isset( $_POST['year'] ) ? intval( $_POST['year'] ) : intval( current_time( 'Y' ) );
    
    // Get user subscription data
    global $wpdb;
    $user_id = get_current_user_id();
    $is_hpos = \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
    
    if ( $is_hpos ) {
        $table = $wpdb->prefix . 'wc_orders';
        $meta_table = $wpdb->prefix . 'wc_orders_meta';
        $id_field = 'id';
        $type_field = 'type';
        $order_id_field = 'order_id';
    } else {
        $table = $wpdb->prefix . 'posts';
        $meta_table = $wpdb->prefix . 'postmeta';
        $id_field = 'ID';
        $type_field = 'post_type';
        $order_id_field = 'post_id';
    }
    
    $sql = "
        SELECT DISTINCT {$table}.{$id_field}
        FROM {$table}
        INNER JOIN {$meta_table} AS meta ON meta.{$order_id_field} = {$table}.{$id_field}
        WHERE meta.meta_key = 'wps_customer_id'
        AND meta.meta_value = %s
        AND {$table}.{$type_field} = 'wps_subscriptions'
        ORDER BY {$table}.{$id_field} DESC LIMIT 1
    ";
    
    $subscription_ids = $wpdb->get_col( $wpdb->prepare( $sql, $user_id ) );
    
    $last_subscription_id = 0;
    $wps_status = false;
    $parent_order = false;
    $wps_schedule_start = 0;
    
    if ( ! empty( $subscription_ids ) && is_array( $subscription_ids ) ) {
        $last_subscription_id = $subscription_ids[0];
        $wps_status = wps_sfw_get_meta_data( $last_subscription_id, 'wps_subscription_status', true );
        $wps_schedule_start = wps_sfw_get_meta_data( $last_subscription_id, 'wps_schedule_start', true );
        $parent_order_id = wps_sfw_get_meta_data( $last_subscription_id, 'wps_parent_order', true );
        $parent_order = wc_get_order( $parent_order_id );
    }
    
    // Calculate calendar dates
    $calendar_cutoff_dates = array();
    $calendar_delivery_dates = array();
    
    if ( $last_subscription_id > 0 && $wps_status === 'active' ) {
        $subscription_number = wps_sfw_get_meta_data( $last_subscription_id, 'wps_sfw_subscription_number', true );
        $subscription_interval = wps_sfw_get_meta_data( $last_subscription_id, 'wps_sfw_subscription_interval', true );
        $subscription_number = ! empty( $subscription_number ) ? intval( $subscription_number ) : 1;
        $subscription_interval = ! empty( $subscription_interval ) ? $subscription_interval : 'month';
        
        $base_timestamp = current_time( 'timestamp' );
        if ( $parent_order && is_a( $parent_order, 'WC_Order' ) ) {
            $order_created_at = $parent_order->get_date_created();
            if ( $order_created_at ) {
                $base_timestamp = $order_created_at->getTimestamp();
            }
        } elseif ( ! empty( $wps_schedule_start ) && is_numeric( $wps_schedule_start ) ) {
            $base_timestamp = $wps_schedule_start;
        }
        
        for ( $i = 0; $i < 12; $i++ ) {
            if ( $subscription_interval === 'month' ) {
                $cutoff_timestamp = strtotime( '+' . ( $i * $subscription_number ) . ' month', $base_timestamp );
            } elseif ( $subscription_interval === 'week' ) {
                $cutoff_timestamp = strtotime( '+' . ( $i * $subscription_number * 7 ) . ' days', $base_timestamp );
            } elseif ( $subscription_interval === 'day' ) {
                $cutoff_timestamp = strtotime( '+' . ( $i * $subscription_number ) . ' days', $base_timestamp );
            } else {
                $cutoff_timestamp = strtotime( '+' . $i . ' month', $base_timestamp );
            }
            
            if ( $cutoff_timestamp >= strtotime( 'first day of this month', current_time( 'timestamp' ) ) ) {
                $delivery_timestamp = strtotime( '+5 days', $cutoff_timestamp );
                
                $calendar_cutoff_dates[] = array(
                    'date' => date( 'Y-m-d', $cutoff_timestamp ),
                );
                
                $calendar_delivery_dates[] = array(
                    'date' => date( 'Y-m-d', $delivery_timestamp ),
                );
            }
        }
    }
    
    // Generate calendar HTML
    $first_day = mktime( 0, 0, 0, $month, 1, $year );
    $days_in_month = date( 't', $first_day );
    $day_of_week = date( 'w', $first_day );
    $prev_month = $month == 1 ? 12 : $month - 1;
    $prev_year = $month == 1 ? $year - 1 : $year;
    $next_month = $month == 12 ? 1 : $month + 1;
    $next_year = $month == 12 ? $year + 1 : $year;
    $month_name = date( 'F Y', $first_day );
    $today_date = current_time( 'Y-m-d' );
    
    ob_start();
    ?>
    <div class="calendar-navigation">
        <button type="button" class="calendar-nav-btn calendar-prev" data-month="<?= esc_attr( $prev_month ) ?>" data-year="<?= esc_attr( $prev_year ) ?>">‹</button>
        <span class="calendar-month-year"><?= esc_html( $month_name ) ?></span>
        <button type="button" class="calendar-nav-btn calendar-next" data-month="<?= esc_attr( $next_month ) ?>" data-year="<?= esc_attr( $next_year ) ?>">›</button>
    </div>
    <div class="calendar-grid">
        <div class="calendar-header">
            <div class="calendar-day-header">Sun</div>
            <div class="calendar-day-header">Mon</div>
            <div class="calendar-day-header">Tue</div>
            <div class="calendar-day-header">Wed</div>
            <div class="calendar-day-header">Thu</div>
            <div class="calendar-day-header">Fri</div>
            <div class="calendar-day-header">Sat</div>
        </div>
        <div class="calendar-days">
            <?php
            for ( $i = 0; $i < $day_of_week; $i++ ) {
                echo '<div class="calendar-day empty"></div>';
            }
            
            for ( $day = 1; $day <= $days_in_month; $day++ ) {
                $date_str = sprintf( '%04d-%02d-%02d', $year, $month, $day );
                $is_cutoff = false;
                $is_delivery = false;
                $is_today = ( $date_str === $today_date );
                
                foreach ( $calendar_cutoff_dates as $cutoff ) {
                    if ( $cutoff['date'] === $date_str ) {
                        $is_cutoff = true;
                        break;
                    }
                }
                
                foreach ( $calendar_delivery_dates as $delivery ) {
                    if ( $delivery['date'] === $date_str ) {
                        $is_delivery = true;
                        break;
                    }
                }
                
                $day_class = 'calendar-day';
                $day_icon = '';
                
                if ( $is_today ) {
                    $day_class .= ' today';
                }
                
                if ( $is_cutoff ) {
                    $day_class .= ' cutoff-date';
                    $day_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="calendar-icon"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path></svg>';
                } elseif ( $is_delivery ) {
                    $day_class .= ' delivery-date';
                    $day_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="calendar-icon"><circle cx="12" cy="12" r="10"></circle></svg>';
                }
                
                echo '<div class="' . esc_attr( $day_class ) . '">';
                if ( $day_icon ) {
                    echo $day_icon;
                }
                echo '<span class="day-number">' . esc_html( $day ) . '</span>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
    <?php
    $calendar_html = ob_get_clean();
    
    wp_send_json_success( array(
        'html' => $calendar_html,
        'month' => $month,
        'year' => $year
    ) );
}
add_action( 'wp_ajax_stirjoy_get_calendar_month', 'stirjoy_get_calendar_month' );
add_action( 'wp_ajax_nopriv_stirjoy_get_calendar_month', 'stirjoy_get_calendar_month' );

/**
 * AJAX: Add product to cart
 * Allows both logged-in and non-logged-in users to add products to cart
 */
function stirjoy_add_to_cart() {
    check_ajax_referer( 'stirjoy_nonce', 'nonce' );
    
    $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
    $quantity = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;
    // Ensure minimum quantity of 1
    if ( $quantity < 1 ) {
        $quantity = 1;
    }
    
    if ( ! $product_id ) {
        wp_send_json_error( array( 'message' => 'Invalid product ID' ) );
    }
    
    // Check if product is already in cart
    $cart = WC()->cart->get_cart();
    $cart_item_key = null;
    foreach ( $cart as $key => $cart_item ) {
        if ( $cart_item['product_id'] == $product_id ) {
            $cart_item_key = $key;
            break;
        }
    }
    
    if ( $cart_item_key ) {
        // Product already in cart, update quantity
        WC()->cart->set_quantity( $cart_item_key, $quantity );
    } else {
        // Add new product to cart
        $cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity );
    }
    
    if ( $cart_item_key ) {
        wp_send_json_success( array(
            'message' => 'Product added to cart',
            'cart_item_key' => $cart_item_key,
            'cart_count' => stirjoy_get_accurate_cart_count(),
            'cart_total' => WC()->cart->get_cart_subtotal()
        ) );
    } else {
        wp_send_json_error( array( 'message' => 'Failed to add product to cart' ) );
    }
}
add_action( 'wp_ajax_stirjoy_add_to_cart', 'stirjoy_add_to_cart' );
add_action( 'wp_ajax_nopriv_stirjoy_add_to_cart', 'stirjoy_add_to_cart' );

/**
 * AJAX: Remove product from cart
 * Allows both logged-in and non-logged-in users to remove products from cart
 */
function stirjoy_remove_from_cart() {
    check_ajax_referer( 'stirjoy_nonce', 'nonce' );
    
    $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
    
    if ( ! $product_id ) {
        wp_send_json_error( array( 'message' => 'Invalid product ID' ) );
    }
    
    // Find cart item key for this product
    $cart = WC()->cart->get_cart();
    $removed = false;
    
    foreach ( $cart as $cart_item_key => $cart_item ) {
        if ( $cart_item['product_id'] == $product_id ) {
            WC()->cart->remove_cart_item( $cart_item_key );
            $removed = true;
            break;
        }
    }
    
    if ( $removed ) {
        wp_send_json_success( array(
            'message' => 'Product removed from cart',
            'cart_count' => stirjoy_get_accurate_cart_count(),
            'cart_total' => WC()->cart->get_cart_subtotal(),
            'cart_subtotal_numeric' => WC()->cart->get_subtotal()
        ) );
    } else {
        wp_send_json_error( array( 'message' => 'Product not found in cart' ) );
    }
}
add_action( 'wp_ajax_stirjoy_remove_from_cart', 'stirjoy_remove_from_cart' );
add_action( 'wp_ajax_nopriv_stirjoy_remove_from_cart', 'stirjoy_remove_from_cart' );

/**
 * Get accurate cart count (number of unique products in cart)
 * Forces recalculation to ensure accuracy, especially after cart merge
 */
function stirjoy_get_accurate_cart_count() {
    if ( ! class_exists( 'WooCommerce' ) || ! WC()->cart ) {
        return 0;
    }
    
    // Force cart recalculation to ensure all items are registered
    WC()->cart->calculate_totals();
    
    // Get cart and count unique products
    $cart = WC()->cart->get_cart();
    $cart_count = 0;
    
    foreach ( $cart as $cart_item ) {
        // Only count if quantity > 0 and product exists
        if ( isset( $cart_item['quantity'] ) && $cart_item['quantity'] > 0 ) {
            $cart_count++;
        }
    }
    
    return $cart_count;
}

/**
 * AJAX: Get cart info for header update
 * Works for both logged-in and non-logged-in users
 */
function stirjoy_get_cart_info() {
    check_ajax_referer( 'stirjoy_nonce', 'nonce' );
    
    // Force cart recalculation to ensure accuracy
    WC()->cart->calculate_totals();
    
    // Get cart total as HTML (works for both logged-in and guest users)
    $cart_subtotal_html = WC()->cart->get_cart_subtotal();
    
    // Also get plain number for potential use
    $cart_total_plain = WC()->cart->get_subtotal();
    
    // Get product IDs in cart and cart contents with quantities
    $product_ids_in_cart = array();
    $cart_contents = array();
    $cart_total_quantity = 0;
    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
        $product_ids_in_cart[] = $cart_item['product_id'];
        $cart_contents[] = array(
            'product_id' => $cart_item['product_id'],
            'quantity' => isset( $cart_item['quantity'] ) ? $cart_item['quantity'] : 1,
            'cart_item_key' => $cart_item_key,
        );
        $cart_total_quantity += isset( $cart_item['quantity'] ) ? $cart_item['quantity'] : 0;
    }
    
    // Use accurate cart count function
    $cart_count = stirjoy_get_accurate_cart_count();
    
    wp_send_json_success( array(
        'count' => $cart_count, // Number of unique products
        'total_quantity' => $cart_total_quantity, // Total quantity of all items
        'total_html' => $cart_subtotal_html,
        'total_plain' => wc_price( $cart_total_plain ),
        'cart_subtotal_numeric' => WC()->cart->get_subtotal(),
        'cart_hash' => WC()->cart->get_cart_hash(),
        'product_ids' => $product_ids_in_cart,
        'cart_contents' => $cart_contents, // Include cart contents with quantities
        'logged_in' => is_user_logged_in()
    ) );
}
add_action( 'wp_ajax_stirjoy_get_cart_info', 'stirjoy_get_cart_info' );
add_action( 'wp_ajax_nopriv_stirjoy_get_cart_info', 'stirjoy_get_cart_info' );

/**
 * AJAX handler to get product details for modal
 */
function stirjoy_get_product_details() {
    check_ajax_referer( 'stirjoy_nonce', 'nonce' );
    
    $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
    
    if ( ! $product_id ) {
        wp_send_json_error( array( 'message' => 'Invalid product ID' ) );
    }
    
    $product = wc_get_product( $product_id );
    
    if ( ! $product ) {
        wp_send_json_error( array( 'message' => 'Product not found' ) );
    }
    
    // Get all product meta
    $prep_time = get_post_meta( $product_id, '_prep_time', true );
    $cook_time = get_post_meta( $product_id, '_cook_time', true );
    $serving_size = get_post_meta( $product_id, '_serving_size', true );
    $calories = get_post_meta( $product_id, '_calories', true );
    $protein = get_post_meta( $product_id, '_protein', true );
    $carbs = get_post_meta( $product_id, '_carbs', true );
    $fat = get_post_meta( $product_id, '_fat', true );
    $fiber = get_post_meta( $product_id, '_fiber', true );
    $portions_of_veggies = get_post_meta( $product_id, '_portions_of_veggies', true );
    $ingredients = get_post_meta( $product_id, '_ingredients', true );
    $allergens = get_post_meta( $product_id, '_allergens', true );
    $instructions = get_post_meta( $product_id, '_instructions', true );
    
    // Get product weight
    $weight = $product->get_weight();
    $weight_text = $weight ? $weight . ' g' : '115 g';
    
    // Get product image
    $image_id = $product->get_image_id();
    $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'large' ) : wc_placeholder_img_src();
    
    // Get rating
    $average_rating = $product->get_average_rating();
    
    // Get description (strip HTML tags to avoid rendering issues)
    $description = $product->get_description() ?: $product->get_short_description();
    $description = wp_strip_all_tags( $description );
    
    // Check if in cart
    $in_cart = false;
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        if ( $cart_item['product_id'] == $product_id ) {
            $in_cart = true;
            break;
        }
    }
    
    // Calculate price per portion
    $price = $product->get_price();
    $price_per_portion = $serving_size ? round( $price / $serving_size, 2 ) : $price;
    
    wp_send_json_success( array(
        'product_id' => $product_id,
        'name' => $product->get_name(),
        'description' => $description,
        'image_url' => $image_url,
        'price' => $product->get_price_html(),
        'price_per_portion' => $price_per_portion,
        'rating' => $average_rating,
        'prep_time' => $prep_time,
        'cook_time' => $cook_time,
        'serving_size' => $serving_size,
        'calories' => $calories,
        'protein' => $protein,
        'carbs' => $carbs,
        'fat' => $fat,
        'fiber' => $fiber,
        'portions_of_veggies' => $portions_of_veggies,
        'weight' => $weight_text,
        'ingredients' => $ingredients,
        'allergens' => $allergens,
        'instructions' => $instructions,
        'in_cart' => $in_cart,
    ) );
}
add_action( 'wp_ajax_stirjoy_get_product_details', 'stirjoy_get_product_details' );
add_action( 'wp_ajax_nopriv_stirjoy_get_product_details', 'stirjoy_get_product_details' );

/**
 * Remove parent theme hooks that interfere with custom shop page
 */
function stirjoy_remove_parent_theme_shop_hooks() {
    if ( is_shop() ) {
        // Remove star rating that creates extra grid elements
        remove_action('woocommerce_before_shop_loop_item_title', 'thecrate_woocommerce_star_rating');
        
        // Remove any other interfering hooks
        remove_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);
        remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5);
    }
}
add_action( 'wp', 'stirjoy_remove_parent_theme_shop_hooks' );

/**
 * Override the login form template when action=register
 * This intercepts WooCommerce's template loading to show registration form instead
 */
function stirjoy_override_login_form_template( $template, $template_name, $template_path ) {
    // Only override the login form template when not logged in
    if ( 'myaccount/form-login.php' === $template_name && ! is_user_logged_in() ) {
        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
        
        // Check if we're on the account page or if action=register is set
        if ( 'register' === $action && 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) {
            // Use registration form template instead
            $child_template = get_stylesheet_directory() . '/woocommerce/myaccount/form-register.php';
            if ( file_exists( $child_template ) ) {
                return $child_template;
            }
        }
    }
    return $template;
}
add_filter( 'woocommerce_locate_template', 'stirjoy_override_login_form_template', 10, 3 );

/**
 * Require login at checkout (standard WooCommerce business logic)
 * Users can add/remove products to/from cart before logging in,
 * but must be logged in to proceed to checkout
 */
function stirjoy_require_login_at_checkout() {
    // Only require login at checkout, not for cart operations
    if ( is_checkout() && ! is_user_logged_in() ) {
        // Redirect to login page with return URL to checkout
        $checkout_url = wc_get_checkout_url();
        $login_url = add_query_arg( 'redirect_to', urlencode( $checkout_url ), wc_get_page_permalink( 'myaccount' ) );
        wp_safe_redirect( $login_url );
        exit;
    }
}
add_action( 'template_redirect', 'stirjoy_require_login_at_checkout' );

/**
 * Ensure checkout requires account creation/login
 * This enforces the standard WooCommerce behavior
 */
function stirjoy_enforce_checkout_login_requirement( $value ) {
    // Require account creation at checkout
    return true;
}
add_filter( 'woocommerce_checkout_registration_required', 'stirjoy_enforce_checkout_login_requirement', 10, 1 );

/**
 * Override checkout form template location
 * This ensures our custom checkout template is used
 * Priority 20 to ensure it runs after other filters
 */
function stirjoy_override_checkout_template( $template, $template_name, $template_path ) {
    // Override checkout form template
    if ( 'checkout/form-checkout.php' === $template_name ) {
        $child_template = get_stylesheet_directory() . '/woocommerce/checkout/form-checkout.php';
        if ( file_exists( $child_template ) ) {
            return $child_template;
        }
    }
    // Override review order template
    if ( 'checkout/review-order.php' === $template_name ) {
        $child_template = get_stylesheet_directory() . '/woocommerce/checkout/review-order.php';
        if ( file_exists( $child_template ) ) {
            return $child_template;
        }
    }
    // Override billing form template
    if ( 'checkout/form-billing.php' === $template_name ) {
        $child_template = get_stylesheet_directory() . '/woocommerce/checkout/form-billing.php';
        if ( file_exists( $child_template ) ) {
            return $child_template;
        }
    }
    // Override shipping form template
    if ( 'checkout/form-shipping.php' === $template_name ) {
        $child_template = get_stylesheet_directory() . '/woocommerce/checkout/form-shipping.php';
        if ( file_exists( $child_template ) ) {
            return $child_template;
        }
    }
    return $template;
}
add_filter( 'woocommerce_locate_template', 'stirjoy_override_checkout_template', 20, 3 );

/**
 * Disable WooCommerce block templates for checkout page
 * Force use of PHP templates instead of block templates
 */
function stirjoy_disable_checkout_block_template( $templates ) {
    // Remove block template from hierarchy on checkout page
    if ( is_checkout() && ! is_wc_endpoint_url() ) {
        // Remove page-checkout.html and checkout block templates
        $templates = array_values( array_filter( $templates, function( $template ) {
            return ! in_array( $template, array( 'page-checkout.html', 'checkout' ), true );
        } ) );
    }
    return $templates;
}
add_filter( 'page_template_hierarchy', 'stirjoy_disable_checkout_block_template', 20, 1 );

/**
 * Disable guest checkout to require login
 */
function stirjoy_disable_guest_checkout( $value ) {
    return false; // Disable guest checkout, require login
}
add_filter( 'woocommerce_enable_guest_checkout', 'stirjoy_disable_guest_checkout', 10, 1 );

/**
 * Save first name and last name from registration form
 * Saves first_name and last_name to user meta
 */
function stirjoy_save_registration_name( $customer_id ) {
    $first_name = '';
    $last_name = '';
    
    // Get first name and last name from form
    if ( isset( $_POST['billing_first_name'] ) && ! empty( $_POST['billing_first_name'] ) ) {
        $first_name = sanitize_text_field( wp_unslash( $_POST['billing_first_name'] ) );
    }
    
    if ( isset( $_POST['billing_last_name'] ) && ! empty( $_POST['billing_last_name'] ) ) {
        $last_name = sanitize_text_field( wp_unslash( $_POST['billing_last_name'] ) );
    }
    
    // If we have names, save them
    if ( ! empty( $first_name ) || ! empty( $last_name ) ) {
        // Update WordPress user meta
        if ( ! empty( $first_name ) ) {
            update_user_meta( $customer_id, 'first_name', $first_name );
        }
        if ( ! empty( $last_name ) ) {
            update_user_meta( $customer_id, 'last_name', $last_name );
        }
        
        // Update WooCommerce billing meta
        if ( ! empty( $first_name ) ) {
            update_user_meta( $customer_id, 'billing_first_name', $first_name );
        }
        if ( ! empty( $last_name ) ) {
            update_user_meta( $customer_id, 'billing_last_name', $last_name );
        }
        
        // Update user display name
        $display_name = trim( $first_name . ' ' . $last_name );
        if ( ! empty( $display_name ) ) {
            wp_update_user( array(
                'ID' => $customer_id,
                'display_name' => $display_name,
                'first_name' => $first_name,
                'last_name' => $last_name,
            ) );
        }
    }
}
add_action( 'woocommerce_created_customer', 'stirjoy_save_registration_name', 10, 1 );

/**
 * Redirect to checkout after successful registration
 * When user clicks "CONTINUE TO DELIVERY" on register page, redirect to checkout
 */
function stirjoy_redirect_after_registration( $redirect ) {
    // Always redirect to checkout after registration
    // The button says "CONTINUE TO DELIVERY" which means checkout
    return wc_get_checkout_url();
}
add_filter( 'woocommerce_registration_redirect', 'stirjoy_redirect_after_registration', 20, 1 );

/**
 * CRITICAL: Save guest cart BEFORE registration (for SiteGround compatibility)
 * This captures the guest cart before any session changes during registration
 */
function stirjoy_save_guest_cart_before_registration() {
    if ( ! class_exists( 'WooCommerce' ) || ! WC()->cart || ! WC()->session ) {
        return;
    }
    
    // Only run on registration page/form submission
    if ( ! isset( $_POST['register'] ) && ! isset( $_POST['woocommerce-register-nonce'] ) ) {
        return;
    }
    
    // Get guest cart before registration
    $guest_cart = WC()->cart->get_cart();
    
    if ( ! empty( $guest_cart ) ) {
        // Save to a temporary location that will be retrieved after user creation
        // Use a unique key based on session ID or IP
        $session_id = WC()->session->get_customer_id();
        $temp_key = 'stirjoy_guest_cart_before_registration_' . md5( $session_id . ( isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '' ) );
        
        $cart_data = array(
            'cart' => $guest_cart,
            'totals' => WC()->cart->get_totals(),
            'coupons' => WC()->cart->get_applied_coupons(),
            'timestamp' => time(),
            'session_id' => $session_id,
        );
        
        // Save to transient (expires in 1 hour)
        set_transient( $temp_key, $cart_data, 3600 );
        
        // Also save to a global option for SiteGround reliability
        $global_key = 'stirjoy_pending_registration_cart_' . md5( $session_id . time() );
        update_option( $global_key, $cart_data, false );
        
        // Store the key in session so we can retrieve it after registration
        WC()->session->set( 'stirjoy_pending_cart_key', $temp_key );
        WC()->session->set( 'stirjoy_pending_cart_global_key', $global_key );
        WC()->session->save_data();
        
        error_log( 'Stirjoy SiteGround: Saved guest cart before registration. Items: ' . count( $guest_cart ) . ', Key: ' . $temp_key );
    }
}
// Hook into registration form submission with HIGH priority
add_action( 'woocommerce_register_form', 'stirjoy_save_guest_cart_before_registration', 1 );
add_action( 'template_redirect', 'stirjoy_save_guest_cart_before_registration', 1 );

/**
 * CRITICAL: Also save guest cart during registration processing
 * This catches the cart even if form hook doesn't fire (SiteGround timing issues)
 * NOTE: woocommerce_process_registration_errors is a FILTER, not an ACTION
 */
function stirjoy_save_guest_cart_during_registration( $errors, $username = '', $password = '', $email = '' ) {
    // This is a filter, so we must return the errors object
    if ( ! is_wp_error( $errors ) ) {
        $errors = new WP_Error();
    }
    
    if ( ! class_exists( 'WooCommerce' ) || ! WC()->cart || ! WC()->session ) {
        return $errors; // Return errors object even if WooCommerce not available
    }
    
    // Check if this is a registration POST request
    if ( ! isset( $_POST['woocommerce-register-nonce'] ) && ! isset( $_POST['register'] ) ) {
        return $errors;
    }
    
    // Get guest cart
    $guest_cart = WC()->cart->get_cart();
    
    if ( ! empty( $guest_cart ) ) {
        $session_id = WC()->session->get_customer_id();
        $temp_key = 'stirjoy_guest_cart_before_registration_' . md5( $session_id . ( isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '' ) );
        
        $cart_data = array(
            'cart' => $guest_cart,
            'totals' => WC()->cart->get_totals(),
            'coupons' => WC()->cart->get_applied_coupons(),
            'timestamp' => time(),
            'session_id' => $session_id,
        );
        
        // Save to multiple locations for SiteGround reliability
        set_transient( $temp_key, $cart_data, 3600 );
        $global_key = 'stirjoy_pending_registration_cart_' . md5( $session_id . time() );
        update_option( $global_key, $cart_data, false );
        
        if ( WC()->session ) {
            WC()->session->set( 'stirjoy_pending_cart_key', $temp_key );
            WC()->session->set( 'stirjoy_pending_cart_global_key', $global_key );
            WC()->session->save_data();
        }
        
        error_log( 'Stirjoy SiteGround: Saved guest cart during registration processing. Items: ' . count( $guest_cart ) );
    }
    
    // CRITICAL: Return the errors object (this is a filter)
    return $errors;
}

/**
 * Save guest cart on woocommerce_register_post action
 * This is a separate action hook that doesn't require a return value
 */
function stirjoy_save_guest_cart_on_register_post( $username, $email, $errors ) {
    if ( ! class_exists( 'WooCommerce' ) || ! WC()->cart || ! WC()->session ) {
        return;
    }
    
    // Get guest cart
    $guest_cart = WC()->cart->get_cart();
    
    if ( ! empty( $guest_cart ) ) {
        $session_id = WC()->session->get_customer_id();
        $temp_key = 'stirjoy_guest_cart_before_registration_' . md5( $session_id . ( isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '' ) );
        
        $cart_data = array(
            'cart' => $guest_cart,
            'totals' => WC()->cart->get_totals(),
            'coupons' => WC()->cart->get_applied_coupons(),
            'timestamp' => time(),
            'session_id' => $session_id,
        );
        
        // Save to multiple locations for SiteGround reliability
        set_transient( $temp_key, $cart_data, 3600 );
        $global_key = 'stirjoy_pending_registration_cart_' . md5( $session_id . time() );
        update_option( $global_key, $cart_data, false );
        
        if ( WC()->session ) {
            WC()->session->set( 'stirjoy_pending_cart_key', $temp_key );
            WC()->session->set( 'stirjoy_pending_cart_global_key', $global_key );
            WC()->session->save_data();
        }
        
        error_log( 'Stirjoy SiteGround: Saved guest cart on register_post. Items: ' . count( $guest_cart ) );
    }
}

// Hook into registration processing - woocommerce_process_registration_errors is a FILTER
add_filter( 'woocommerce_process_registration_errors', 'stirjoy_save_guest_cart_during_registration', 1, 4 );
// woocommerce_register_post is an ACTION (doesn't need return value)
add_action( 'woocommerce_register_post', 'stirjoy_save_guest_cart_on_register_post', 1, 3 );

/**
 * Mark user as just registered and merge guest cart
 * CRITICAL: This runs when user is created during registration
 */
function stirjoy_mark_user_registered_and_merge_cart( $customer_id ) {
    // Mark user as just registered
    update_user_meta( $customer_id, '_stirjoy_just_registered', time() );
    
    // CRITICAL: Merge guest cart immediately after user creation
    // This happens BEFORE the user is logged in, so we need to handle it specially
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }
    
    // Get the saved guest cart from before registration
    $session_id = WC()->session ? WC()->session->get_customer_id() : '';
    $temp_key = WC()->session ? WC()->session->get( 'stirjoy_pending_cart_key' ) : '';
    
    if ( ! $temp_key ) {
        // Try to find it by session ID
        $temp_key = 'stirjoy_guest_cart_before_registration_' . md5( $session_id . ( isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '' ) );
    }
    
    $saved_guest_cart = get_transient( $temp_key );
    
    // If transient is empty, try global option
    if ( ! $saved_guest_cart || empty( $saved_guest_cart['cart'] ) ) {
        $global_key = WC()->session ? WC()->session->get( 'stirjoy_pending_cart_global_key' ) : '';
        if ( $global_key ) {
            $saved_guest_cart = get_option( $global_key );
        }
    }
    
    // If still empty, try to get from current session (might still be there)
    if ( ( ! $saved_guest_cart || empty( $saved_guest_cart['cart'] ) ) && WC()->cart && ! WC()->cart->is_empty() ) {
        $saved_guest_cart = array(
            'cart' => WC()->cart->get_cart(),
            'totals' => WC()->cart->get_totals(),
            'coupons' => WC()->cart->get_applied_coupons(),
        );
        error_log( 'Stirjoy SiteGround: Using current session cart for registration merge. User: ' . $customer_id );
    }
    
    if ( $saved_guest_cart && ! empty( $saved_guest_cart['cart'] ) ) {
        // Save to the same location as login merge (for consistency)
        set_transient( 'stirjoy_guest_cart_before_login_' . $customer_id, $saved_guest_cart, 3600 );
        update_user_meta( $customer_id, '_stirjoy_guest_cart_backup', $saved_guest_cart );
        
        error_log( 'Stirjoy SiteGround: Saved guest cart for registration merge. User: ' . $customer_id . ', Items: ' . count( $saved_guest_cart['cart'] ) );
        
        // Clear temporary storage
        if ( $temp_key ) {
            delete_transient( $temp_key );
        }
        if ( $global_key ) {
            delete_option( $global_key );
        }
        if ( WC()->session ) {
            WC()->session->__unset( 'stirjoy_pending_cart_key' );
            WC()->session->__unset( 'stirjoy_pending_cart_global_key' );
        }
    }
    
    // CRITICAL: Schedule merge to happen after user is logged in
    // WooCommerce logs the user in automatically after registration
    // We need to wait for that, but merge BEFORE session is finalized
    add_action( 'wp_login', function( $user_login, $user ) use ( $customer_id ) {
        if ( $user->ID == $customer_id ) {
            // This is our newly registered user - merge cart immediately
            error_log( 'Stirjoy SiteGround: User logged in after registration. Merging cart for user: ' . $customer_id );
            stirjoy_merge_guest_cart_with_user_cart( $customer_id );
        }
    }, 5, 2 );
    
    // Also try to merge immediately if user is already logged in (some registration flows)
    if ( is_user_logged_in() && get_current_user_id() == $customer_id ) {
        error_log( 'Stirjoy SiteGround: User already logged in during registration. Merging cart immediately for user: ' . $customer_id );
        stirjoy_merge_guest_cart_with_user_cart( $customer_id );
    }
}
add_action( 'woocommerce_created_customer', 'stirjoy_mark_user_registered_and_merge_cart', 5, 1 );

/**
 * Hook into wholesale plugin's registration hooks to redirect to checkout
 * This runs BEFORE the wholesale plugin's redirect (priority 5)
 */
function stirjoy_wholesale_registration_redirect_to_checkout( $user_id ) {
    // Mark user as just registered
    update_user_meta( $user_id, '_stirjoy_just_registered', time() );
    
    // Redirect to checkout immediately - this happens before wholesale plugin's redirect
    wp_safe_redirect( wc_get_checkout_url() );
    exit;
}
// Hook into wholesale plugin's registration hooks with HIGH priority (5) to run FIRST
add_action( 'wwp_wholesale_new_registered_request', 'stirjoy_wholesale_registration_redirect_to_checkout', 5, 1 );
add_action( 'wwp_wholesale_new_request_submitted', 'stirjoy_wholesale_registration_redirect_to_checkout', 5, 1 );

/**
 * Force redirect to checkout if user just registered and landed on my account page
 * This is a fallback in case other plugins redirect to my account
 */
function stirjoy_force_checkout_redirect_after_registration() {
    // Only run if user is logged in and on my account page
    if ( is_account_page() && is_user_logged_in() ) {
        $user_id = get_current_user_id();
        
        // Check if user just registered (within last 10 seconds)
        $user_registered = get_user_meta( $user_id, '_stirjoy_just_registered', true );
        
        if ( $user_registered && ( time() - intval( $user_registered ) ) < 10 ) {
            // Clear the flag
            delete_user_meta( $user_id, '_stirjoy_just_registered' );
            
            // Redirect to checkout
            wp_safe_redirect( wc_get_checkout_url() );
            exit;
        }
    }
}
add_action( 'template_redirect', 'stirjoy_force_checkout_redirect_after_registration', 30 );

/**
 * Transfer guest cart to user when they log in
 * This ensures cart persists even with caching/speed optimizers
 * Enhanced for SiteGround hosting compatibility
 * FIXED: Now properly merges guest cart with user cart
 * CRITICAL: Merge happens BEFORE WooCommerce loads persistent cart from session
 */
function stirjoy_transfer_guest_cart_to_user( $user_login, $user ) {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }
    
    // CRITICAL: Merge cart BEFORE WooCommerce loads persistent cart from session
    // Hook into woocommerce_load_cart_from_session with HIGH priority (5) to run FIRST
    // This ensures merge happens before session cart is processed
    add_action( 'woocommerce_load_cart_from_session', function() use ( $user ) {
        // Only merge once per login
        static $merged = false;
        if ( $merged ) {
            return;
        }
        $merged = true;
        
        // Perform merge BEFORE WooCommerce processes the session cart
        // This ensures: new user cart = old user cart + guest cart
        stirjoy_merge_guest_cart_with_user_cart( $user->ID );
    }, 5 );
    
    // Also hook into wp_loaded as backup with HIGH priority (5) to run BEFORE WooCommerce's cart loading (priority 10)
    add_action( 'wp_loaded', function() use ( $user ) {
        // Only merge if not already merged via woocommerce_load_cart_from_session
        $merge_completed = get_user_meta( $user->ID, '_stirjoy_cart_merge_completed', true );
        if ( ! $merge_completed ) {
            // Perform merge BEFORE WooCommerce loads cart from session
            stirjoy_merge_guest_cart_with_user_cart( $user->ID );
        }
    }, 5 );
    
    // CRITICAL: Also hook into init with HIGH priority to ensure merge happens before session initialization
    add_action( 'init', function() use ( $user ) {
        // Only merge if not already merged
        $merge_completed = get_user_meta( $user->ID, '_stirjoy_cart_merge_completed', true );
        if ( ! $merge_completed ) {
            // Perform merge BEFORE WooCommerce initializes session
            stirjoy_merge_guest_cart_with_user_cart( $user->ID );
        }
    }, 5 );
}
add_action( 'wp_login', 'stirjoy_transfer_guest_cart_to_user', 10, 2 );

/**
 * CRITICAL: Prevent session cookie initialization until cart merge completes
 * This ensures merged cart is saved before cookies are set on SiteGround
 */
function stirjoy_delay_session_cookies_until_merge_complete( $set ) {
    // Only delay on login/checkout pages and if merge is in progress
    if ( ! is_user_logged_in() || ! is_checkout() && ! is_cart() ) {
        return $set;
    }
    
    $user_id = get_current_user_id();
    $merge_completed = get_user_meta( $user_id, '_stirjoy_cart_merge_completed', true );
    
    // If merge is not completed, delay cookie setting
    if ( ! $merge_completed ) {
        // Check if we have a guest cart that needs merging
        $transient_key = 'stirjoy_guest_cart_before_login_' . $user_id;
        $saved_guest_cart = get_transient( $transient_key );
        
        if ( ! $saved_guest_cart || empty( $saved_guest_cart['cart'] ) ) {
            $saved_guest_cart = get_user_meta( $user_id, '_stirjoy_guest_cart_backup', true );
        }
        
        // If we have a guest cart to merge, prevent cookie setting until merge completes
        if ( $saved_guest_cart && ! empty( $saved_guest_cart['cart'] ) ) {
            // Try to complete merge immediately
            stirjoy_merge_guest_cart_with_user_cart( $user_id );
            
            // Check again if merge completed
            $merge_completed = get_user_meta( $user_id, '_stirjoy_cart_merge_completed', true );
            if ( ! $merge_completed ) {
                // Still not merged - delay cookie setting
                error_log( 'Stirjoy SiteGround: Delaying session cookie until merge completes for user: ' . $user_id );
                return false; // Prevent cookie setting
            }
        }
    }
    
    return $set;
}
// Hook into WooCommerce cookie setting with HIGH priority
add_filter( 'woocommerce_set_cart_cookies', 'stirjoy_delay_session_cookies_until_merge_complete', 5, 1 );

/**
 * Actually perform the cart merge
 * CRITICAL: This runs BEFORE WooCommerce loads persistent cart from session
 * Enhanced for SiteGround hosting - uses multiple fallback methods
 * Returns true if merge was successful, false otherwise
 * 
 * Flow: new user cart = old user cart + guest cart
 */
function stirjoy_merge_guest_cart_with_user_cart( $user_id ) {
    if ( ! class_exists( 'WooCommerce' ) ) {
        error_log( 'Stirjoy SiteGround: WooCommerce not available for merge. User: ' . $user_id );
        return false;
    }
    
    // CRITICAL: Initialize WooCommerce objects if not already initialized
    // But do NOT call session->init() yet - we need to merge BEFORE session is finalized
    if ( ! WC()->cart ) {
        WC()->initialize_cart();
    }
    if ( ! WC()->session ) {
        WC()->initialize_session();
        // But don't load cart from session yet - we'll do that after merge
        if ( method_exists( WC()->session, 'init' ) ) {
            // Initialize session but prevent cart loading
            WC()->session->init();
        }
    }
    
    if ( ! WC()->cart || ! WC()->session ) {
        error_log( 'Stirjoy SiteGround: WooCommerce cart/session not available for merge. User: ' . $user_id );
        return false;
    }
    
    // Check if merge already completed
    $merge_completed = get_user_meta( $user_id, '_stirjoy_cart_merge_completed', true );
    if ( $merge_completed ) {
        return true; // Already merged
    }
    
    error_log( 'Stirjoy SiteGround: Starting cart merge for user: ' . $user_id . ' BEFORE session cart loading' );
    
    // Check if we have a saved guest cart from before login
    $transient_key = 'stirjoy_guest_cart_before_login_' . $user_id;
    $saved_guest_cart = get_transient( $transient_key );
    
    // If transient is empty (SiteGround cache issue), try user meta backup
    if ( ! $saved_guest_cart || empty( $saved_guest_cart['cart'] ) ) {
        error_log( 'Stirjoy SiteGround: Transient empty, trying user meta backup for user: ' . $user_id );
        $saved_guest_cart = get_user_meta( $user_id, '_stirjoy_guest_cart_backup', true );
        
        // If still empty, try database options (ultimate backup)
        if ( ! $saved_guest_cart || empty( $saved_guest_cart['cart'] ) ) {
            error_log( 'Stirjoy SiteGround: User meta empty, trying database options for user: ' . $user_id );
            global $wpdb;
            $option_keys = $wpdb->get_col( $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s ORDER BY option_id DESC LIMIT 1",
                'stirjoy_guest_cart_' . $user_id . '_%'
            ) );
            if ( ! empty( $option_keys ) ) {
                $saved_guest_cart = get_option( $option_keys[0] );
                error_log( 'Stirjoy SiteGround: Found cart in database option: ' . $option_keys[0] );
            }
        }
        
        // If still empty, check if there's a guest cart in the current session
        if ( ! $saved_guest_cart || empty( $saved_guest_cart['cart'] ) ) {
            $current_session_cart = WC()->session->get( 'cart' );
            if ( ! empty( $current_session_cart ) ) {
                $saved_guest_cart = array( 'cart' => $current_session_cart );
                error_log( 'Stirjoy SiteGround: Found cart in current session for user: ' . $user_id );
            } else {
                // No guest cart to merge
                error_log( 'Stirjoy SiteGround: No guest cart found to merge for user: ' . $user_id );
                // Mark as completed even if no guest cart (prevents retry loops)
                update_user_meta( $user_id, '_stirjoy_cart_merge_completed', time() );
                return false;
            }
        }
    }
    
    $guest_cart_contents = $saved_guest_cart['cart'];
    
    if ( empty( $guest_cart_contents ) ) {
        error_log( 'Stirjoy SiteGround: Guest cart is empty for user: ' . $user_id );
        update_user_meta( $user_id, '_stirjoy_cart_merge_completed', time() );
        return false;
    }
    
    error_log( 'Stirjoy SiteGround: Starting merge for user ' . $user_id . '. Guest items: ' . count( $guest_cart_contents ) );
    
    // CRITICAL: Get existing user's persistent cart DIRECTLY from database
    // Do NOT rely on WooCommerce session cart - it may not be loaded yet
    // This ensures we get the OLD user cart before any session processing
    $existing_persistent_cart_meta = get_user_meta( $user_id, '_woocommerce_persistent_cart_' . get_current_blog_id(), true );
    $existing_user_cart = array();
    
    if ( is_array( $existing_persistent_cart_meta ) && isset( $existing_persistent_cart_meta['cart'] ) ) {
        $existing_user_cart = array_filter( (array) $existing_persistent_cart_meta['cart'] );
        error_log( 'Stirjoy SiteGround: Found existing user cart with ' . count( $existing_user_cart ) . ' items for user: ' . $user_id );
    } else {
        error_log( 'Stirjoy SiteGround: No existing user cart found for user: ' . $user_id );
    }
    
    // Also check current session cart (if already loaded by WooCommerce)
    // But prioritize persistent cart from database
    $current_session_cart = WC()->session->get( 'cart', null );
    if ( ! is_null( $current_session_cart ) && ! empty( $current_session_cart ) ) {
        error_log( 'Stirjoy SiteGround: Found session cart with ' . count( $current_session_cart ) . ' items for user: ' . $user_id );
        // Merge session cart with existing user cart (combine quantities for duplicates)
        foreach ( $current_session_cart as $cart_item_key => $cart_item ) {
            $product_id = $cart_item['product_id'];
            $variation_id = isset( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : 0;
            
            // Check if already in existing_user_cart
            $found = false;
            foreach ( $existing_user_cart as $key => $item ) {
                if ( $item['product_id'] == $product_id && 
                     ( isset( $item['variation_id'] ) ? $item['variation_id'] : 0 ) == $variation_id ) {
                    // Same product - combine quantities
                    $existing_user_cart[ $key ]['quantity'] += $cart_item['quantity'];
                    $found = true;
                    break;
                }
            }
            if ( ! $found ) {
                $existing_user_cart[ $cart_item_key ] = $cart_item;
            }
        }
    }
    
    // Start with existing user cart
    $merged_cart = $existing_user_cart;
    
    // Merge guest cart items with existing user cart
    foreach ( $guest_cart_contents as $guest_item_key => $guest_item ) {
        $product_id = $guest_item['product_id'];
        $variation_id = isset( $guest_item['variation_id'] ) ? $guest_item['variation_id'] : 0;
        $guest_quantity = $guest_item['quantity'];
        
        // Check if this product already exists in merged cart
        $found_in_merged = false;
        foreach ( $merged_cart as $merged_key => $merged_item ) {
            if ( $merged_item['product_id'] == $product_id && 
                 ( isset( $merged_item['variation_id'] ) ? $merged_item['variation_id'] : 0 ) == $variation_id ) {
                // Same product and variation - combine quantities
                $merged_cart[ $merged_key ]['quantity'] += $guest_quantity;
                $found_in_merged = true;
                break;
            }
        }
        
        // If not found, add guest item to merged cart
        if ( ! $found_in_merged ) {
            // Generate new cart item key for the merged cart
            $new_key = md5( $product_id . '_' . $variation_id . '_' . time() . '_' . wp_rand() );
            $merged_cart[ $new_key ] = $guest_item;
        }
    }
    
    // Save merged cart to persistent cart in user meta
    if ( ! empty( $merged_cart ) ) {
        update_user_meta( $user_id, '_woocommerce_persistent_cart_' . get_current_blog_id(), array(
            'cart' => $merged_cart,
        ) );
        
        // Clear current cart and add merged items
        WC()->cart->empty_cart( false );
        
        // Add all merged cart items to WooCommerce cart
        foreach ( $merged_cart as $cart_item_key => $cart_item ) {
            $product_id = $cart_item['product_id'];
            $variation_id = isset( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : 0;
            $quantity = isset( $cart_item['quantity'] ) ? absint( $cart_item['quantity'] ) : 1;
            $variation = isset( $cart_item['variation'] ) ? $cart_item['variation'] : array();
            
            // Add to cart (this will create proper cart item keys)
            $new_cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation );
            
            if ( ! $new_cart_item_key ) {
                error_log( 'Stirjoy SiteGround: Failed to add product ' . $product_id . ' (qty: ' . $quantity . ') to cart during merge' );
            }
        }
        
        // Force cart recalculation to ensure all items are properly registered
        WC()->cart->calculate_totals();
        
        // Update session with merged cart (use actual cart from WooCommerce object)
        $final_cart = WC()->cart->get_cart();
        WC()->session->set( 'cart', $final_cart );
        
        // Save session multiple times for SiteGround reliability
        WC()->session->save_data();
        
        // Force another save after a brief delay (for SiteGround cache)
        if ( function_exists( 'sg_cachepress_purge_cache' ) || class_exists( 'SiteGround_Optimizer\Supercacher\Supercacher' ) ) {
            // Schedule an additional save
            add_action( 'shutdown', function() use ( $user_id ) {
                if ( WC()->session && WC()->cart ) {
                    WC()->session->save_data();
                    // Also purge SiteGround cache for this user
                    if ( function_exists( 'sg_cachepress_purge_cache' ) ) {
                        sg_cachepress_purge_cache();
                    }
                }
            }, 999 );
        }
        
        // Mark merge as completed
        update_user_meta( $user_id, '_stirjoy_cart_merge_completed', time() );
        
        // Clear transient and backup
        delete_transient( $transient_key );
        delete_user_meta( $user_id, '_stirjoy_guest_cart_backup' );
        
        // Clear all cart-related caches
        wp_cache_delete( 'cart_' . $user_id, 'woocommerce' );
        wp_cache_flush_group( 'woocommerce' );
        
        // Clear object cache if available
        if ( function_exists( 'wp_cache_flush' ) ) {
            wp_cache_flush();
        }
        
        // Verify merge was successful
        $final_cart = WC()->cart->get_cart();
        $final_count = count( $final_cart );
        
        error_log( 'Stirjoy SiteGround: SUCCESS - Merged guest cart with user cart. Total items: ' . $final_count . ' for user: ' . $user_id );
        error_log( 'Stirjoy SiteGround: Merge details - Guest items: ' . count( $guest_cart_contents ) . ', Merged items: ' . count( $merged_cart ) . ', Final cart items: ' . $final_count );
        
        return true;
    } else {
        error_log( 'Stirjoy SiteGround: Merge failed - merged cart is empty for user: ' . $user_id );
        return false;
    }
}

/**
 * Save guest cart before login (capture it before session changes)
 * Enhanced for SiteGround hosting - uses longer expiration and multiple storage methods
 */
function stirjoy_save_guest_cart_before_login( $user_login, $user ) {
    if ( ! class_exists( 'WooCommerce' ) || ! WC()->cart || ! WC()->session ) {
        return;
    }
    
    // Clear any previous merge completion flag
    delete_user_meta( $user->ID, '_stirjoy_cart_merge_completed' );
    
    // Get guest cart before login
    $guest_cart = WC()->cart->get_cart();
    
    if ( ! empty( $guest_cart ) ) {
        $cart_data = array(
            'cart' => $guest_cart,
            'totals' => WC()->cart->get_totals(),
            'coupons' => WC()->cart->get_applied_coupons(),
            'timestamp' => time(),
        );
        
        // Save to transient (expires in 1 hour for SiteGround compatibility - longer than before)
        set_transient( 'stirjoy_guest_cart_before_login_' . $user->ID, $cart_data, 3600 );
        
        // Also save to user meta as backup (for SiteGround cache issues) - permanent until merge
        update_user_meta( $user->ID, '_stirjoy_guest_cart_backup', $cart_data );
        
        // Also save to database option as ultimate backup
        $option_key = 'stirjoy_guest_cart_' . $user->ID . '_' . time();
        update_option( $option_key, $cart_data, false );
        
        // Force session save before login
        WC()->session->save_data();
        
        error_log( 'Stirjoy SiteGround: Saved guest cart before login. Items: ' . count( $guest_cart ) . ' for user: ' . $user->ID );
        error_log( 'Stirjoy SiteGround: Cart saved to transient, user meta, and option: ' . $option_key );
    } else {
        error_log( 'Stirjoy SiteGround: No guest cart to save for user: ' . $user->ID );
    }
}
add_action( 'wp_login', 'stirjoy_save_guest_cart_before_login', 5, 2 );

/**
 * Ensure cart is loaded after login/registration
 * This handles cases where session migration doesn't happen immediately
 * Enhanced for SiteGround hosting compatibility - tries multiple times until successful
 */
function stirjoy_ensure_cart_after_login() {
    if ( ! is_user_logged_in() || ! class_exists( 'WooCommerce' ) ) {
        return;
    }
    
    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        return;
    }
    
    // Check if merge has already been completed (to avoid infinite loops)
    $merge_completed = get_user_meta( $user_id, '_stirjoy_cart_merge_completed', true );
    
    // Check if we have a saved guest cart that needs to be merged
    $transient_key = 'stirjoy_guest_cart_before_login_' . $user_id;
    $saved_guest_cart = get_transient( $transient_key );
    
    // If transient is empty, try user meta backup (SiteGround cache fallback)
    if ( ! $saved_guest_cart || empty( $saved_guest_cart['cart'] ) ) {
        $saved_guest_cart = get_user_meta( $user_id, '_stirjoy_guest_cart_backup', true );
    }
    
    // If we have a guest cart and merge hasn't been completed, attempt merge
    if ( $saved_guest_cart && ! empty( $saved_guest_cart['cart'] ) && ! $merge_completed ) {
        error_log( 'Stirjoy SiteGround: Attempting cart merge for user ' . $user_id . '. Guest items: ' . count( $saved_guest_cart['cart'] ) );
        
        // Force WooCommerce session initialization first
        if ( WC()->session ) {
            if ( ! WC()->session->has_session() ) {
                WC()->session->set_customer_session_cookie( true );
            }
            // Force session start
            WC()->session->init();
        }
        
        // For SiteGround, try multiple approaches
        if ( function_exists( 'sg_cachepress_purge_cache' ) || class_exists( 'SiteGround_Optimizer\Supercacher\Supercacher' ) ) {
            // Try immediate merge first
            $merge_result = stirjoy_merge_guest_cart_with_user_cart( $user_id );
            
            // If immediate merge didn't work, schedule for shutdown
            if ( ! $merge_result ) {
                add_action( 'shutdown', function() use ( $user_id ) {
                    stirjoy_merge_guest_cart_with_user_cart( $user_id );
                }, 1 );
            }
            
            // Also schedule for next page load as backup
            add_action( 'template_redirect', function() use ( $user_id ) {
                if ( is_user_logged_in() && get_current_user_id() == $user_id ) {
                    $merge_completed = get_user_meta( $user_id, '_stirjoy_cart_merge_completed', true );
                    if ( ! $merge_completed ) {
                        stirjoy_merge_guest_cart_with_user_cart( $user_id );
                    }
                }
            }, 999 );
        } else {
            // Immediate merge for other hosts
            stirjoy_merge_guest_cart_with_user_cart( $user_id );
        }
    }
    
    // Force WooCommerce to initialize session
    if ( WC()->session && ! WC()->session->has_session() ) {
        WC()->session->set_customer_session_cookie( true );
    }
    
    // Ensure cart is loaded and calculated
    if ( WC()->cart ) {
        // Trigger cart load from session
        WC()->cart->get_cart();
        WC()->cart->calculate_totals();
        
        // Force session save for SiteGround
        if ( WC()->session ) {
            WC()->session->save_data();
        }
    }
}
add_action( 'wp_loaded', 'stirjoy_ensure_cart_after_login', 30 );

/**
 * Prevent caching of cart-related AJAX requests
 * This ensures cart operations work properly with speed optimizers
 */
function stirjoy_prevent_cart_ajax_caching() {
    // Only for AJAX requests
    if ( ! wp_doing_ajax() ) {
        return;
    }
    
    // Check if it's a cart-related AJAX request
    $cart_actions = array(
        'stirjoy_add_to_cart',
        'stirjoy_remove_from_cart',
        'stirjoy_get_cart_info',
        'stirjoy_social_login',
        'woocommerce_add_to_cart',
        'woocommerce_remove_from_cart',
        'woocommerce_get_cart',
        'woocommerce_update_order_review',
    );
    
    $action = isset( $_REQUEST['action'] ) ? sanitize_text_field( $_REQUEST['action'] ) : '';
    
    if ( in_array( $action, $cart_actions, true ) ) {
        // Prevent caching
        nocache_headers();
        
        // Set headers to prevent caching
        header( 'Cache-Control: no-cache, no-store, must-revalidate' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );
        
        // Ensure session is started
        if ( ! session_id() && class_exists( 'WooCommerce' ) && WC()->session ) {
            WC()->session->init();
        }
    }
}
add_action( 'admin_init', 'stirjoy_prevent_cart_ajax_caching', 1 );
add_action( 'init', 'stirjoy_prevent_cart_ajax_caching', 1 );

/**
 * SiteGround-specific fixes for cart session handling
 * Excludes WooCommerce cookies from caching and ensures proper session handling
 * Enhanced for reliable cart merging on SiteGround hosting
 */
function stirjoy_siteground_cart_fixes() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }
    
    // Exclude WooCommerce cookies from SiteGround cache
    if ( function_exists( 'sg_cachepress_purge_cache' ) || class_exists( 'SiteGround_Optimizer\Supercacher\Supercacher' ) ) {
        // Add no-cache headers for cart/checkout/account pages and shop pages
        if ( is_cart() || is_checkout() || is_account_page() || is_shop() || is_product() ) {
            if ( ! headers_sent() ) {
                nocache_headers();
                header( 'Cache-Control: no-cache, no-store, must-revalidate, private, max-age=0' );
                header( 'Pragma: no-cache' );
                header( 'Expires: Thu, 01 Jan 1970 00:00:00 GMT' );
                header( 'X-Accel-Expires: 0' ); // Nginx cache control
                
                // CRITICAL: Add CSP headers to allow Stripe payment iframes and other required resources
                // More permissive CSP to allow all necessary third-party services
                if ( is_checkout() && ! headers_sent() ) {
                    // CRITICAL: Explicitly allow Stripe domains for better compatibility
                    $stripe_domains = 'https://js.stripe.com https://hooks.stripe.com https://m.stripe.com https://r.stripe.com https://q.stripe.com https://b.stripecdn.com https://stripecdn.com https://*.stripe.com https://*.stripecdn.com';
                    
                    $csp_directives = array(
                        // CRITICAL: Explicitly allow Stripe iframes and frames (including all Stripe domains)
                        "frame-src 'self' " . $stripe_domains . " https: http:",
                        // CRITICAL: Explicitly allow Stripe scripts
                        "script-src 'self' 'unsafe-inline' 'unsafe-eval' " . $stripe_domains . " https: http: blob:",
                        // Explicitly allow script-src-elem for external scripts (including Stripe)
                        "script-src-elem 'self' 'unsafe-inline' " . $stripe_domains . " https: http: blob:",
                        // CRITICAL: Allow workers from blob: URLs (required for Stripe and other services)
                        "worker-src 'self' blob: " . $stripe_domains . " https: http:",
                        // CRITICAL: Explicitly allow Stripe connections (including all Stripe API endpoints)
                        "connect-src 'self' " . $stripe_domains . " https: http: ws: wss:",
                        // CRITICAL: Allow payment manifest for Google Pay
                        "manifest-src 'self' https://www.google.com https://pay.google.com",
                        // Allow images from anywhere
                        "img-src 'self' data: https: http:",
                        // Allow styles from Google Fonts (both HTTP and HTTPS) - more permissive
                        "style-src 'self' 'unsafe-inline' https: http:",
                        // Explicitly allow style-src-elem for external stylesheets (more permissive)
                        "style-src-elem 'self' 'unsafe-inline' https: http:",
                        // Allow fonts from Google Fonts
                        "font-src 'self' data: https: http:",
                    );
                    
                    // CRITICAL: Remove any existing CSP headers first to avoid conflicts
                    // Then set our permissive CSP
                    if ( function_exists( 'header_remove' ) ) {
                        header_remove( 'Content-Security-Policy' );
                        header_remove( 'X-Content-Security-Policy' );
                        header_remove( 'X-WebKit-CSP' );
                    }
                    
                    // Set our permissive CSP that allows all necessary resources
                    header( 'Content-Security-Policy: ' . implode( '; ', $csp_directives ) );
                }
            }
        }
        
        // Exclude WooCommerce cookies from SiteGround cache
        if ( function_exists( 'sg_cachepress_exclude_cookies' ) ) {
            $excluded_cookies = array(
                'woocommerce_cart_hash',
                'woocommerce_items_in_cart',
                'wp_woocommerce_session_',
                'woocommerce_recently_viewed',
                'woocommerce_session_',
            );
            foreach ( $excluded_cookies as $cookie ) {
                sg_cachepress_exclude_cookies( $cookie );
            }
        }
    }
    
    // Ensure WooCommerce session cookies are set correctly
    if ( WC()->session && ! headers_sent() ) {
        // Force session initialization
        if ( ! WC()->session->has_session() ) {
            WC()->session->set_customer_session_cookie( true );
        }
        
        // Set session cookie parameters for better compatibility
        $cookie_params = session_get_cookie_params();
        
        // Ensure cookies work across subdomains if needed
        if ( defined( 'COOKIE_DOMAIN' ) && COOKIE_DOMAIN ) {
            // Cookie domain is already set in wp-config
        }
    }
}
add_action( 'template_redirect', 'stirjoy_siteground_cart_fixes', 1 );

/**
 * Add CSP meta tag for Stripe iframes (fallback if headers don't work)
 * This fixes sandboxed frame errors on checkout page that prevent payment processing
 * NOTE: Meta CSP tags must be in <head> section, so we use wp_head hook with early priority
 */
function stirjoy_add_csp_meta_tag() {
    // Only add on checkout page and ensure we're in the head section
    if ( ! is_checkout() ) {
        return;
    }
    
    // Double-check we're in wp_head context (not in body)
    if ( ! doing_action( 'wp_head' ) ) {
        return;
    }
    
    // CRITICAL: Explicitly allow Stripe domains
    $stripe_domains = 'https://js.stripe.com https://hooks.stripe.com https://m.stripe.com https://r.stripe.com https://q.stripe.com https://b.stripecdn.com https://stripecdn.com https://*.stripe.com https://*.stripecdn.com';
    
    // Build CSP content
    $csp_content = sprintf(
        "frame-src 'self' %s https: http:; script-src 'self' 'unsafe-inline' 'unsafe-eval' %s https: http: blob:; script-src-elem 'self' 'unsafe-inline' %s https: http: blob:; worker-src 'self' blob: %s https: http:; connect-src 'self' %s https: http: ws: wss:; manifest-src 'self' https://www.google.com https://pay.google.com; img-src 'self' data: https: http:; style-src 'self' 'unsafe-inline' https: http:; style-src-elem 'self' 'unsafe-inline' https: http:; font-src 'self' data: https: http:;",
        esc_attr( $stripe_domains ),
        esc_attr( $stripe_domains ),
        esc_attr( $stripe_domains ),
        esc_attr( $stripe_domains ),
        esc_attr( $stripe_domains )
    );
    
    echo '<meta http-equiv="Content-Security-Policy" content="' . esc_attr( $csp_content ) . '">' . "\n";
}
// Use early priority to ensure it's added before other head content
add_action( 'wp_head', 'stirjoy_add_csp_meta_tag', 1 );

/**
 * SiteGround-specific: Exclude WooCommerce pages from dynamic cache
 */
function stirjoy_siteground_exclude_pages_from_cache() {
    if ( ! function_exists( 'sg_cachepress_exclude_url' ) ) {
        return;
    }
    
    // Exclude cart, checkout, and account pages from cache
    $excluded_paths = array(
        '/cart/',
        '/checkout/',
        '/my-account/',
        '/wc-api/',
    );
    
    foreach ( $excluded_paths as $path ) {
        sg_cachepress_exclude_url( $path );
    }
}
add_action( 'init', 'stirjoy_siteground_exclude_pages_from_cache', 5 );

/**
 * Force cart persistence on login redirect
 * This ensures cart is preserved during redirect after login
 */
function stirjoy_preserve_cart_on_login_redirect() {
    if ( ! is_user_logged_in() || ! class_exists( 'WooCommerce' ) ) {
        return;
    }
    
    // Check if this is right after login (within 5 seconds)
    $login_time = get_user_meta( get_current_user_id(), '_stirjoy_last_login_time', true );
    if ( $login_time && ( time() - $login_time ) < 5 ) {
        // Force cart reload from persistent storage
        if ( WC()->cart && WC()->session ) {
            // Trigger cart load from session
            WC()->cart->get_cart();
            
            // If cart is empty, try to load from persistent cart
            if ( WC()->cart->is_empty() ) {
                $user_id = get_current_user_id();
                $persistent_cart = get_user_meta( $user_id, '_woocommerce_persistent_cart_' . get_current_blog_id(), true );
                
                if ( $persistent_cart && isset( $persistent_cart['cart'] ) && ! empty( $persistent_cart['cart'] ) ) {
                    // Restore cart from persistent storage
                    WC()->session->set( 'cart', $persistent_cart['cart'] );
                    WC()->session->save_data();
                    WC()->cart->get_cart();
                    WC()->cart->calculate_totals();
                }
            }
        }
        
        // Clear the login time flag
        delete_user_meta( get_current_user_id(), '_stirjoy_last_login_time' );
    }
}
add_action( 'wp', 'stirjoy_preserve_cart_on_login_redirect', 1 );

/**
 * Track login time for cart restoration
 */
function stirjoy_track_login_time( $user_login, $user ) {
    update_user_meta( $user->ID, '_stirjoy_last_login_time', time() );
}
add_action( 'wp_login', 'stirjoy_track_login_time', 5, 2 );

/**
 * Auto-populate required billing fields on checkout
 * This ensures validation passes without modifying the design
 */
function stirjoy_auto_populate_billing_fields( $data, $errors = null ) {
	// Only run if we're processing checkout
	if ( ! isset( $_POST['woocommerce_checkout_place_order'] ) ) {
		return $data;
	}
	
	// Ensure WooCommerce is loaded
	if ( ! class_exists( 'WooCommerce' ) ) {
		return $data;
	}
	
	// Ensure countries object exists
	if ( ! WC()->countries ) {
		error_log( 'Stirjoy Error: WC()->countries not available' );
		return $data;
	}
	
	$current_user = wp_get_current_user();
	
	// Auto-populate email if empty
	if ( empty( $data['billing_email'] ) ) {
		if ( is_user_logged_in() && ! empty( $current_user->user_email ) ) {
			$data['billing_email'] = $current_user->user_email;
		} elseif ( isset( $_POST['billing_email'] ) && ! empty( $_POST['billing_email'] ) ) {
			$data['billing_email'] = sanitize_email( $_POST['billing_email'] );
		}
	}
	
	// Auto-populate first name if empty
	if ( empty( $data['billing_first_name'] ) ) {
		if ( is_user_logged_in() ) {
			$first_name = get_user_meta( $current_user->ID, 'billing_first_name', true );
			if ( empty( $first_name ) ) {
				$first_name = $current_user->first_name;
			}
			if ( ! empty( $first_name ) ) {
				$data['billing_first_name'] = $first_name;
			}
		} elseif ( isset( $_POST['billing_first_name'] ) && ! empty( $_POST['billing_first_name'] ) ) {
			$data['billing_first_name'] = sanitize_text_field( $_POST['billing_first_name'] );
		}
	}
	
	// Auto-populate last name if empty
	if ( empty( $data['billing_last_name'] ) ) {
		if ( is_user_logged_in() ) {
			$last_name = get_user_meta( $current_user->ID, 'billing_last_name', true );
			if ( empty( $last_name ) ) {
				$last_name = $current_user->last_name;
			}
			if ( ! empty( $last_name ) ) {
				$data['billing_last_name'] = $last_name;
			}
		} elseif ( isset( $_POST['billing_last_name'] ) && ! empty( $_POST['billing_last_name'] ) ) {
			$data['billing_last_name'] = sanitize_text_field( $_POST['billing_last_name'] );
		}
	}
	
	// Auto-populate country if empty (default to store base country)
	if ( empty( $data['billing_country'] ) && WC()->countries ) {
		$data['billing_country'] = WC()->countries->get_base_country();
	}
	
	// Auto-populate state if empty
	if ( empty( $data['billing_state'] ) ) {
		if ( is_user_logged_in() ) {
			$state = get_user_meta( $current_user->ID, 'billing_state', true );
			if ( ! empty( $state ) ) {
				$data['billing_state'] = $state;
			}
		}
		
		// If still empty, check if state is required for the selected country
		if ( empty( $data['billing_state'] ) && ! empty( $data['billing_country'] ) && class_exists( 'WooCommerce' ) && WC()->countries ) {
			$country = $data['billing_country'];
			$locale = WC()->countries->get_country_locale();
			
			// Check if state field is required for this country
			if ( isset( $locale[ $country ]['state']['required'] ) && $locale[ $country ]['state']['required'] ) {
				// Get states for this country
				$states = WC()->countries->get_states( $country );
				if ( ! empty( $states ) && is_array( $states ) ) {
					// Use first available state as default
					$state_keys = array_keys( $states );
					if ( ! empty( $state_keys ) ) {
						$data['billing_state'] = $state_keys[0];
					}
				}
			} else {
				// If state is not required, set empty string to avoid validation errors
				$data['billing_state'] = '';
			}
		} elseif ( empty( $data['billing_state'] ) ) {
			// Ensure state field exists even if empty (for validation)
			$data['billing_state'] = '';
		}
	}
	
	return $data;
}
add_filter( 'woocommerce_checkout_posted_data', 'stirjoy_auto_populate_billing_fields', 10, 2 );

/**
 * Ensure payment method is selected before checkout processing
 */
function stirjoy_ensure_payment_method_selected( $data, $errors = null ) {
	// Only run if we're processing checkout
	if ( ! isset( $_POST['woocommerce_checkout_place_order'] ) ) {
		return $data;
	}
	
	// Ensure WooCommerce is loaded
	if ( ! class_exists( 'WooCommerce' ) || ! WC()->payment_gateways ) {
		return $data;
	}
	
	// Check if payment method is set
	if ( empty( $data['payment_method'] ) ) {
		// Get available payment gateways
		$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
		
		if ( ! empty( $available_gateways ) ) {
			$gateway_ids = array_keys( $available_gateways );
			$preferred_gateways = array( 'stripe', 'stripe_cc', 'stripe_credit_card', 'woocommerce_payments' );
			
			// Try to find Stripe/credit card gateway first
			$selected_gateway = null;
			foreach ( $preferred_gateways as $preferred ) {
				if ( in_array( $preferred, $gateway_ids ) ) {
					$selected_gateway = $preferred;
					break;
				}
			}
			
			// If no Stripe gateway found, check for any gateway with 'stripe' or 'card' in the ID
			if ( ! $selected_gateway ) {
				foreach ( $gateway_ids as $gateway_id ) {
					if ( stripos( $gateway_id, 'stripe' ) !== false || stripos( $gateway_id, 'card' ) !== false ) {
						$selected_gateway = $gateway_id;
						break;
					}
				}
			}
			
			// Fallback to first available gateway
			if ( ! $selected_gateway ) {
				$selected_gateway = $gateway_ids[0];
			}
			
			$data['payment_method'] = $selected_gateway;
			$_POST['payment_method'] = $selected_gateway;
			
			error_log( 'Stirjoy: Auto-selected payment method ' . $selected_gateway );
		} else {
			// Use wc_add_notice instead of $errors->add() for better compatibility
			if ( is_object( $errors ) && method_exists( $errors, 'add' ) ) {
				$errors->add( 'payment', __( 'No payment methods are available.', 'woocommerce' ) );
			} else {
				wc_add_notice( __( 'No payment methods are available.', 'woocommerce' ), 'error' );
			}
		}
	}
	
	return $data;
}
add_filter( 'woocommerce_checkout_posted_data', 'stirjoy_ensure_payment_method_selected', 20, 2 );

/**
 * Log checkout errors for debugging
 */
function stirjoy_log_checkout_errors( $order_id, $posted_data, $order ) {
	if ( ! $order ) {
		error_log( 'Stirjoy Checkout Error: Order object is null' );
		return;
	}
	
	// Log order details
	error_log( 'Stirjoy Checkout: Order ID ' . $order_id . ' created' );
	error_log( 'Stirjoy Checkout: Payment Method - ' . ( isset( $posted_data['payment_method'] ) ? $posted_data['payment_method'] : 'NOT SET' ) );
	error_log( 'Stirjoy Checkout: Order Status - ' . $order->get_status() );
	error_log( 'Stirjoy Checkout: Order Total - ' . $order->get_total() );
}
add_action( 'woocommerce_checkout_order_processed', 'stirjoy_log_checkout_errors', 10, 3 );

/**
 * Catch order creation errors
 */
function stirjoy_catch_order_creation_errors( $order_id, $data, $order ) {
	if ( ! $order_id || ! $order ) {
		error_log( 'Stirjoy Error: Order creation failed - Order ID: ' . $order_id );
		if ( $data ) {
			error_log( 'Stirjoy Error: Order data: ' . print_r( $data, true ) );
		}
	}
}
add_action( 'woocommerce_checkout_order_processed', 'stirjoy_catch_order_creation_errors', 1, 3 );

/**
 * Catch payment gateway processing errors
 */
function stirjoy_catch_payment_processing_errors( $order_id ) {
	if ( ! $order_id ) {
		error_log( 'Stirjoy Error: Payment processing called without order ID' );
		return;
	}
	
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		error_log( 'Stirjoy Error: Order not found for ID: ' . $order_id );
		return;
	}
	
	$payment_method = $order->get_payment_method();
	error_log( 'Stirjoy: Processing payment for order ' . $order_id . ' with method ' . $payment_method );
	
	// Check if payment gateway exists
	if ( WC()->payment_gateways ) {
		$gateways = WC()->payment_gateways->get_available_payment_gateways();
		if ( ! isset( $gateways[ $payment_method ] ) ) {
			error_log( 'Stirjoy Error: Payment gateway ' . $payment_method . ' not found or not available' );
		}
	}
}
add_action( 'woocommerce_checkout_order_processed', 'stirjoy_catch_payment_processing_errors', 5, 1 );

/**
 * Catch and log checkout processing errors before order creation
 */
function stirjoy_log_checkout_processing_errors() {
	if ( ! isset( $_POST['woocommerce_checkout_place_order'] ) ) {
		return;
	}
	
	// Log all checkout notices/errors
	$notices = wc_get_notices( 'error' );
	if ( ! empty( $notices ) ) {
		error_log( 'Stirjoy Checkout Validation Errors: ' . print_r( $notices, true ) );
	}
	
	// Log payment method
	if ( isset( $_POST['payment_method'] ) ) {
		error_log( 'Stirjoy Checkout: Payment method in POST - ' . $_POST['payment_method'] );
	} else {
		error_log( 'Stirjoy Checkout Error: Payment method NOT in POST data' );
	}
	
	// Log required billing fields
	$required_fields = array( 'billing_email', 'billing_first_name', 'billing_last_name', 'billing_country' );
	foreach ( $required_fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			error_log( 'Stirjoy Checkout: ' . $field . ' = ' . ( ! empty( $_POST[ $field ] ) ? $_POST[ $field ] : 'EMPTY' ) );
		} else {
			error_log( 'Stirjoy Checkout Error: ' . $field . ' NOT in POST data' );
		}
	}
}
add_action( 'woocommerce_after_checkout_validation', 'stirjoy_log_checkout_processing_errors', 10 );

/**
 * Catch payment processing errors and log PHP errors
 */
function stirjoy_catch_payment_errors() {
	if ( ! is_checkout() || ! isset( $_POST['woocommerce_checkout_place_order'] ) ) {
		return;
	}
	
	// Ensure WooCommerce is loaded before logging
	if ( ! class_exists( 'WooCommerce' ) ) {
		error_log( 'Stirjoy Checkout Error: WooCommerce class not found' );
		return;
	}
	
	// Enable error logging
	$previous_handler = set_error_handler( function( $errno, $errstr, $errfile, $errline ) {
		error_log( "Stirjoy Checkout PHP Error: [$errno] $errstr in $errfile on line $errline" );
		return false; // Let PHP handle the error normally
	});
	
	// Log POST data for debugging (remove sensitive data)
	$post_data = $_POST;
	unset( $post_data['card_number'], $post_data['card_cvc'], $post_data['card_expiry'], $post_data['stripe_token'], $post_data['stripe-source'], $post_data['stripe_payment_method'] );
	error_log( 'Stirjoy Checkout POST Data: ' . print_r( $post_data, true ) );
	
	// Check if payment method is set
	if ( empty( $_POST['payment_method'] ) ) {
		error_log( 'Stirjoy Checkout Error: Payment method not set in POST data' );
	} else {
		error_log( 'Stirjoy Checkout: Payment method is ' . $_POST['payment_method'] );
	}
	
	// Check if WooCommerce is properly loaded
	if ( ! WC()->countries ) {
		error_log( 'Stirjoy Checkout Error: WC()->countries not available' );
	}
	
	// Restore previous error handler
	if ( $previous_handler !== null ) {
		set_error_handler( $previous_handler );
	}
}
add_action( 'woocommerce_before_checkout_process', 'stirjoy_catch_payment_errors', 1 );

/**
 * Catch fatal errors during checkout processing
 */
function stirjoy_catch_fatal_errors() {
	if ( ! is_checkout() || ! isset( $_POST['woocommerce_checkout_place_order'] ) ) {
		return;
	}
	
	// Register shutdown function to catch fatal errors
	register_shutdown_function( function() {
		$error = error_get_last();
		if ( $error !== null && in_array( $error['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR ) ) ) {
			error_log( 'Stirjoy Checkout Fatal Error: ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line'] );
			
			// Try to send a user-friendly error message
			if ( wp_doing_ajax() ) {
				wp_send_json_error( array(
					'messages' => '<div class="woocommerce-error">' . __( 'There was an error processing your order. Please try again or contact support.', 'woocommerce' ) . '</div>'
				) );
			}
		}
	});
}
add_action( 'init', 'stirjoy_catch_fatal_errors' );

/**
 * Ensure all required checkout fields are present before processing
 */
function stirjoy_ensure_required_checkout_fields() {
	if ( ! isset( $_POST['woocommerce_checkout_place_order'] ) ) {
		return;
	}
	
	// Ensure WooCommerce is loaded
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	
	// Ensure required billing fields exist
	$required_fields = array(
		'billing_email'      => __( 'Billing Email', 'woocommerce' ),
		'billing_first_name' => __( 'Billing First Name', 'woocommerce' ),
		'billing_last_name'  => __( 'Billing Last Name', 'woocommerce' ),
		'billing_country'    => __( 'Billing Country', 'woocommerce' ),
	);
	
	foreach ( $required_fields as $field => $label ) {
		if ( empty( $_POST[ $field ] ) ) {
			// Try to get from hidden fields or user data
			if ( isset( $_POST[ $field ] ) && empty( $_POST[ $field ] ) ) {
				// Field exists but is empty - will be handled by auto-populate function
				continue;
			} elseif ( ! isset( $_POST[ $field ] ) ) {
				// Field doesn't exist - add it
				$_POST[ $field ] = '';
			}
		}
	}
	
	// Ensure payment method is set (prioritize Stripe/credit card)
	if ( empty( $_POST['payment_method'] ) && WC()->payment_gateways ) {
		$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
		if ( ! empty( $available_gateways ) ) {
			$gateway_ids = array_keys( $available_gateways );
			$preferred_gateways = array( 'stripe', 'stripe_cc', 'stripe_credit_card', 'woocommerce_payments' );
			
			// Try to find Stripe/credit card gateway first
			$selected_gateway = null;
			foreach ( $preferred_gateways as $preferred ) {
				if ( in_array( $preferred, $gateway_ids ) ) {
					$selected_gateway = $preferred;
					break;
				}
			}
			
			// If no Stripe gateway found, check for any gateway with 'stripe' or 'card' in the ID
			if ( ! $selected_gateway ) {
				foreach ( $gateway_ids as $gateway_id ) {
					if ( stripos( $gateway_id, 'stripe' ) !== false || stripos( $gateway_id, 'card' ) !== false ) {
						$selected_gateway = $gateway_id;
						break;
					}
				}
			}
			
			// Fallback to first available gateway
			if ( ! $selected_gateway ) {
				$selected_gateway = $gateway_ids[0];
			}
			
			$_POST['payment_method'] = $selected_gateway;
		}
	}
}
add_action( 'woocommerce_before_checkout_process', 'stirjoy_ensure_required_checkout_fields', 1 );

/**
 * Handle payment gateway errors and provide better error messages
 */
function stirjoy_handle_payment_gateway_errors( $result, $order_id ) {
	if ( isset( $result['result'] ) && 'failure' === $result['result'] ) {
		$error_message = isset( $result['errorMessage'] ) ? $result['errorMessage'] : 'Unknown payment error';
		
		// Comprehensive error logging
		$error_details = array(
			'order_id' => $order_id,
			'error_message' => $error_message,
			'result' => $result,
			'timestamp' => current_time( 'mysql' ),
			'user_id' => get_current_user_id(),
			'ip_address' => WC_Geolocation::get_ip_address(),
		);
		
		// Log gateway-specific errors
		if ( isset( $result['messages'] ) ) {
			$error_details['messages'] = $result['messages'];
		}
		
		// Log all error data
		error_log( '=== STIRJOY PAYMENT FAILURE DETAILS ===' );
		error_log( 'Order ID: ' . $order_id );
		error_log( 'Error Message: ' . $error_message );
		error_log( 'Full Result: ' . print_r( $result, true ) );
		error_log( 'Error Details: ' . print_r( $error_details, true ) );
		error_log( '========================================' );
		
		// Store error in session for display on checkout page
		WC()->session->set( 'stirjoy_payment_error_details', $error_details );
		
		// Get order for additional context
		$order = wc_get_order( $order_id );
		if ( $order ) {
			$error_details['order_status'] = $order->get_status();
			$error_details['payment_method'] = $order->get_payment_method();
			$error_details['order_total'] = $order->get_total();
			
			// Add order notes
			$order_notes = $order->get_customer_order_notes();
			if ( ! empty( $order_notes ) ) {
				$error_details['order_notes'] = $order_notes;
			}
			
			error_log( 'Order Details: Status=' . $order->get_status() . ', Payment Method=' . $order->get_payment_method() . ', Total=' . $order->get_total() );
		}
	} elseif ( isset( $result['result'] ) && 'success' === $result['result'] ) {
		// Clear any previous error details
		WC()->session->__unset( 'stirjoy_payment_error_details' );
		
		// Ensure redirect URL is set for successful payments
		if ( ! isset( $result['redirect'] ) || empty( $result['redirect'] ) ) {
			$order = wc_get_order( $order_id );
			if ( $order ) {
				$result['redirect'] = $order->get_checkout_order_received_url();
				error_log( 'Stirjoy Payment Success: Redirect URL set to order-received page (Order ID: ' . $order_id . ')' );
			}
		} else {
			error_log( 'Stirjoy Payment Success: Redirecting to ' . $result['redirect'] . ' (Order ID: ' . $order_id . ')' );
		}
	}
	return $result;
}
add_filter( 'woocommerce_payment_successful_result', 'stirjoy_handle_payment_gateway_errors', 10, 2 );

/**
 * Catch payment processing failures
 */
function stirjoy_catch_payment_failures( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( $order ) {
		$payment_method = $order->get_payment_method();
		error_log( 'Stirjoy Payment Processing: Order ' . $order_id . ' - Payment Method: ' . $payment_method );
		error_log( 'Stirjoy Payment Processing: Order Status: ' . $order->get_status() );
		
		// Check if payment failed
		if ( $order->has_status( 'failed' ) || $order->has_status( 'pending' ) ) {
			$order_notes = $order->get_customer_order_notes();
			if ( ! empty( $order_notes ) ) {
				error_log( 'Stirjoy Payment Processing: Order Notes: ' . print_r( $order_notes, true ) );
			}
		}
	}
}
add_action( 'woocommerce_payment_complete', 'stirjoy_catch_payment_failures', 10, 1 );
add_action( 'woocommerce_order_status_failed', 'stirjoy_catch_payment_failures', 10, 1 );

/**
 * Catch payment processing errors during checkout
 * This hook fires when payment processing fails
 */
function stirjoy_catch_payment_processing_errors_detailed( $order_id, $posted_data, $order ) {
	if ( ! $order ) {
		return;
	}
	
	// Check if order has failed status or is pending payment
	if ( $order->has_status( array( 'failed', 'pending', 'on-hold' ) ) ) {
		// Extract error message from WooCommerce notices
		$error_message = '';
		$notices = wc_get_notices( 'error' );
		if ( ! empty( $notices ) ) {
			// Get the most recent error notice
			$last_notice = end( $notices );
			if ( isset( $last_notice['notice'] ) ) {
				$error_message = strip_tags( $last_notice['notice'] );
			}
		}
		
		// If no notice found, try to get from order notes
		if ( empty( $error_message ) ) {
			$order_notes = $order->get_customer_order_notes();
			if ( ! empty( $order_notes ) ) {
				// Get the most recent order note
				$last_note = reset( $order_notes );
				if ( isset( $last_note->comment_content ) ) {
					$error_message = strip_tags( $last_note->comment_content );
				}
			}
		}
		
		// If still no message, use a default based on order status
		if ( empty( $error_message ) ) {
			if ( $order->has_status( 'failed' ) ) {
				$error_message = 'Payment processing failed. Please try again or use a different payment method.';
			} elseif ( $order->has_status( 'pending' ) ) {
				$error_message = 'Payment is pending. Please check your payment method and try again.';
			} else {
				$error_message = 'Payment could not be processed. Please try again.';
			}
		}
		
		$error_details = array(
			'order_id' => $order_id,
			'error_message' => $error_message,
			'order_status' => $order->get_status(),
			'payment_method' => $order->get_payment_method(),
			'order_total' => $order->get_total(),
			'timestamp' => current_time( 'mysql' ),
			'user_id' => $order->get_user_id(),
			'customer_email' => $order->get_billing_email(),
		);
		
		// Get order notes for additional context
		$order_notes = $order->get_customer_order_notes();
		if ( ! empty( $order_notes ) ) {
			$error_details['order_notes'] = array();
			foreach ( $order_notes as $note ) {
				$error_details['order_notes'][] = array(
					'comment_date' => $note->comment_date,
					'comment_content' => $note->comment_content,
				);
			}
		}
		
		// Get payment transaction ID if available
		$transaction_id = $order->get_transaction_id();
		if ( $transaction_id ) {
			$error_details['transaction_id'] = $transaction_id;
		}
		
		// Log comprehensive error details
		error_log( '=== STIRJOY PAYMENT PROCESSING ERROR ===' );
		error_log( 'Order ID: ' . $order_id );
		error_log( 'Error Message: ' . $error_message );
		error_log( 'Order Status: ' . $order->get_status() );
		error_log( 'Payment Method: ' . $order->get_payment_method() );
		error_log( 'Order Total: ' . $order->get_total() );
		error_log( 'Transaction ID: ' . ( $transaction_id ? $transaction_id : 'N/A' ) );
		error_log( 'Full Error Details: ' . print_r( $error_details, true ) );
		error_log( '=========================================' );
		
		// Store error in session for display
		WC()->session->set( 'stirjoy_payment_error_details', $error_details );
	}
}
add_action( 'woocommerce_checkout_order_processed', 'stirjoy_catch_payment_processing_errors_detailed', 999, 3 );

/**
 * Catch all PHP errors during checkout processing
 */
function stirjoy_catch_checkout_php_errors() {
	if ( ! isset( $_POST['woocommerce_checkout_place_order'] ) ) {
		return;
	}
	
	// Store previous error handler
	$previous_handler = set_error_handler( function( $errno, $errstr, $errfile, $errline ) use ( &$previous_handler ) {
		// Log all errors
		error_log( sprintf( 
			'Stirjoy Checkout PHP Error [%d]: %s in %s on line %d', 
			$errno, 
			$errstr, 
			basename( $errfile ), 
			$errline 
		) );
		
		// Call previous handler if it exists
		if ( $previous_handler && is_callable( $previous_handler ) ) {
			return call_user_func( $previous_handler, $errno, $errstr, $errfile, $errline );
		}
		
		return false; // Let PHP handle the error normally
	}, E_ALL | E_STRICT );
	
	// Register shutdown function to catch fatal errors
	register_shutdown_function( function() {
		$error = error_get_last();
		if ( $error !== null && in_array( $error['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR ) ) ) {
			error_log( sprintf( 
				'Stirjoy Checkout Fatal Error: %s in %s on line %d', 
				$error['message'], 
				basename( $error['file'] ), 
				$error['line'] 
			) );
		}
	});
}
add_action( 'woocommerce_before_checkout_process', 'stirjoy_catch_checkout_php_errors', 1 );

/**
 * Log checkout POST data for debugging (sanitized)
 */
function stirjoy_log_checkout_post_data() {
	if ( ! isset( $_POST['woocommerce_checkout_place_order'] ) ) {
		return;
	}
	
	$post_data = $_POST;
	
	// Remove sensitive data
	unset( $post_data['card_number'], $post_data['card_cvc'], $post_data['card_expiry'] );
	unset( $post_data['stripe_token'], $post_data['stripe-source'], $post_data['stripe_payment_method'] );
	unset( $post_data['stirjoy_card_number_display'], $post_data['stirjoy_card_expiry_display'] );
	unset( $post_data['stirjoy_card_cvc_display'], $post_data['stirjoy_card_name_display'] );
	unset( $post_data['password'], $post_data['password_1'], $post_data['password_2'] );
	
	// Log sanitized POST data
	error_log( 'Stirjoy Checkout POST Data: ' . print_r( $post_data, true ) );
	
	// Check critical fields
	$critical_fields = array( 'payment_method', 'billing_email', 'billing_first_name', 'billing_last_name', 'billing_country' );
	foreach ( $critical_fields as $field ) {
		if ( ! isset( $post_data[ $field ] ) || empty( $post_data[ $field ] ) ) {
			error_log( 'Stirjoy Checkout Warning: Missing or empty field - ' . $field );
		}
	}
}
add_action( 'woocommerce_before_checkout_process', 'stirjoy_log_checkout_post_data', 2 );

/**
 * Set default payment method to Stripe/Credit Card on checkout page load
 * CRITICAL: Enhanced for SiteGround compatibility with multiple attempts
 */
function stirjoy_set_default_payment_method() {
	if ( is_checkout() && ! is_wc_endpoint_url() && class_exists( 'WooCommerce' ) && WC()->payment_gateways ) {
		$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
		
		if ( ! empty( $available_gateways ) ) {
			$gateway_ids = array_keys( $available_gateways );
			$preferred_gateways = array( 'stripe', 'stripe_cc', 'stripe_credit_card', 'woocommerce_payments' );
			
			// Try to find Stripe/credit card gateway first
			$selected_gateway = null;
			foreach ( $preferred_gateways as $preferred ) {
				if ( in_array( $preferred, $gateway_ids ) ) {
					$selected_gateway = $preferred;
					break;
				}
			}
			
			// If no Stripe gateway found, check for any gateway with 'stripe' or 'card' in the ID
			if ( ! $selected_gateway ) {
				foreach ( $gateway_ids as $gateway_id ) {
					if ( stripos( $gateway_id, 'stripe' ) !== false || stripos( $gateway_id, 'card' ) !== false ) {
						$selected_gateway = $gateway_id;
						break;
					}
				}
			}
			
			// Fallback to first available gateway
			if ( ! $selected_gateway ) {
				$selected_gateway = $gateway_ids[0];
			}
			
			// CRITICAL: Always set as chosen payment method (not just if empty)
			// On SiteGround, the session might have a different value that needs to be overridden
			$current_method = WC()->session->get( 'chosen_payment_method' );
			
			// Only set if not already set to our preferred gateway
			if ( $current_method !== $selected_gateway ) {
				WC()->session->set( 'chosen_payment_method', $selected_gateway );
				error_log( 'Stirjoy: Set default payment method to ' . $selected_gateway . ' (was: ' . ( $current_method ? $current_method : 'none' ) . ')' );
			}
		}
	}
}
// CRITICAL: Run on multiple hooks for SiteGround compatibility
add_action( 'init', 'stirjoy_set_default_payment_method', 20 );
add_action( 'wp', 'stirjoy_set_default_payment_method', 20 );
add_action( 'template_redirect', 'stirjoy_set_default_payment_method', 20 );
add_action( 'woocommerce_checkout_init', 'stirjoy_set_default_payment_method', 5 );

/**
 * Ensure billing state is always present in checkout data (even if empty)
 * This prevents validation errors when state is conditionally required
 */
function stirjoy_ensure_billing_state_field( $fields ) {
	// Ensure billing_state field exists in checkout fields
	if ( ! isset( $fields['billing']['billing_state'] ) ) {
		$fields['billing']['billing_state'] = array(
			'type'        => 'state',
			'label'       => __( 'State / County', 'woocommerce' ),
			'required'    => false,
			'class'       => array( 'form-row-wide', 'address-field' ),
			'validate'    => array( 'state' ),
			'autocomplete' => 'address-level1',
		);
	}
	
	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'stirjoy_ensure_billing_state_field', 20 );

/**
 * Validate and fix checkout data before processing
 */
function stirjoy_validate_checkout_data() {
	// Only run during checkout processing
	if ( ! isset( $_POST['woocommerce_checkout_place_order'] ) ) {
		return;
	}
	
	// Ensure WooCommerce is loaded
	if ( ! class_exists( 'WooCommerce' ) ) {
		error_log( 'Stirjoy Error: WooCommerce class not found in validation' );
		return;
	}
	
	// Ensure countries object exists
	if ( ! WC()->countries ) {
		error_log( 'Stirjoy Error: WC()->countries not available in validation' );
		return;
	}
	
	try {
		// Ensure email is valid
		if ( ! empty( $_POST['billing_email'] ) && ! is_email( $_POST['billing_email'] ) ) {
			wc_add_notice( __( 'Please enter a valid email address.', 'woocommerce' ), 'error' );
		}
		
		// Ensure country is valid
		if ( ! empty( $_POST['billing_country'] ) ) {
			$allowed_countries = WC()->countries->get_allowed_countries();
			if ( ! empty( $allowed_countries ) && ! array_key_exists( $_POST['billing_country'], $allowed_countries ) ) {
				// Reset to base country if invalid
				$_POST['billing_country'] = WC()->countries->get_base_country();
			}
		}
		
		// Validate state if country requires it
		if ( ! empty( $_POST['billing_country'] ) ) {
			$locale = WC()->countries->get_country_locale();
			if ( ! empty( $locale ) && isset( $locale[ $_POST['billing_country'] ]['state']['required'] ) && 
				 $locale[ $_POST['billing_country'] ]['state']['required'] && 
				 empty( $_POST['billing_state'] ) ) {
				// Try to get default state
				$states = WC()->countries->get_states( $_POST['billing_country'] );
				if ( ! empty( $states ) && is_array( $states ) ) {
					$state_keys = array_keys( $states );
					if ( ! empty( $state_keys ) ) {
						$_POST['billing_state'] = $state_keys[0];
					}
				}
			}
		}
	} catch ( Exception $e ) {
		error_log( 'Stirjoy Validation Error: ' . $e->getMessage() );
	}
}
add_action( 'woocommerce_checkout_process', 'stirjoy_validate_checkout_data', 5 );

/**
 * Handle "use shipping as billing" checkbox functionality
 */
function stirjoy_copy_shipping_to_billing( $data ) {
	// Only run if we're processing checkout
	if ( ! isset( $_POST['woocommerce_checkout_place_order'] ) ) {
		return $data;
	}
	
	// Only run if checkbox is checked
	if ( isset( $_POST['use_shipping_as_billing'] ) && $_POST['use_shipping_as_billing'] == '1' ) {
		// Copy shipping fields to billing fields
		$shipping_fields = array(
			'address_1' => 'billing_address_1',
			'address_2' => 'billing_address_2',
			'city'      => 'billing_city',
			'state'     => 'billing_state',
			'postcode'  => 'billing_postcode',
			'country'   => 'billing_country',
		);
		
		foreach ( $shipping_fields as $shipping_key => $billing_key ) {
			if ( isset( $_POST[ 'shipping_' . $shipping_key ] ) && ! empty( $_POST[ 'shipping_' . $shipping_key ] ) ) {
				$data[ $billing_key ] = sanitize_text_field( $_POST[ 'shipping_' . $shipping_key ] );
				$_POST[ $billing_key ] = $data[ $billing_key ];
			}
		}
	}
	
	return $data;
}
add_filter( 'woocommerce_checkout_posted_data', 'stirjoy_copy_shipping_to_billing', 30 );

/**
 * Remove custom display card fields from POST data to prevent validation issues
 * These are visual-only fields and should not be processed by WooCommerce
 */
function stirjoy_remove_display_card_fields_from_post( $data ) {
	// Ensure data is an array
	if ( ! is_array( $data ) ) {
		error_log( 'Stirjoy Error: $data is not an array in stirjoy_remove_display_card_fields_from_post' );
		return $data;
	}
	
	// Remove display-only card fields from POST data (they're just for visual design)
	unset( $data['stirjoy_card_number_display'] );
	unset( $data['stirjoy_card_expiry_display'] );
	unset( $data['stirjoy_card_cvc_display'] );
	unset( $data['stirjoy_card_name_display'] );
	
	// Also remove from $_POST to prevent any issues (safely)
	if ( isset( $_POST['stirjoy_card_number_display'] ) ) {
		unset( $_POST['stirjoy_card_number_display'] );
	}
	if ( isset( $_POST['stirjoy_card_expiry_display'] ) ) {
		unset( $_POST['stirjoy_card_expiry_display'] );
	}
	if ( isset( $_POST['stirjoy_card_cvc_display'] ) ) {
		unset( $_POST['stirjoy_card_cvc_display'] );
	}
	if ( isset( $_POST['stirjoy_card_name_display'] ) ) {
		unset( $_POST['stirjoy_card_name_display'] );
	}
	
	return $data;
}
add_filter( 'woocommerce_checkout_posted_data', 'stirjoy_remove_display_card_fields_from_post', 1 );

/**
 * Ensure standard WooCommerce checkout validation is used
 * Payment gateway validation (like Stripe) is handled by the gateway itself
 */
function stirjoy_ensure_standard_checkout_validation( $data, $errors = null ) {
	// Ensure data is an array
	if ( ! is_array( $data ) ) {
		error_log( 'Stirjoy Error: $data is not an array in stirjoy_ensure_standard_checkout_validation' );
		return $data;
	}
	
	// WooCommerce handles standard field validation automatically
	// We only need to ensure payment method is selected
	if ( empty( $data['payment_method'] ) && WC()->payment_gateways ) {
		$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
		if ( ! empty( $available_gateways ) ) {
			$gateway_ids = array_keys( $available_gateways );
			$preferred_gateways = array( 'stripe', 'stripe_cc', 'stripe_credit_card', 'woocommerce_payments' );
			
			// Try to find Stripe/credit card gateway first
			$selected_gateway = null;
			foreach ( $preferred_gateways as $preferred ) {
				if ( in_array( $preferred, $gateway_ids ) ) {
					$selected_gateway = $preferred;
					break;
				}
			}
			
			// If no Stripe gateway found, check for any gateway with 'stripe' or 'card' in the ID
			if ( ! $selected_gateway ) {
				foreach ( $gateway_ids as $gateway_id ) {
					if ( stripos( $gateway_id, 'stripe' ) !== false || stripos( $gateway_id, 'card' ) !== false ) {
						$selected_gateway = $gateway_id;
						break;
					}
				}
			}
			
			// Fallback to first available gateway
			if ( ! $selected_gateway ) {
				$selected_gateway = $gateway_ids[0];
			}
			
			$data['payment_method'] = $selected_gateway;
			$_POST['payment_method'] = $selected_gateway;
			
			error_log( 'Stirjoy: Auto-selected payment method ' . $selected_gateway . ' in validation' );
		} else {
			// Use wc_add_notice if errors object not available
			if ( is_object( $errors ) && method_exists( $errors, 'add' ) ) {
				$errors->add( 'payment', __( 'Please choose a payment method.', 'woocommerce' ) );
			} else {
				wc_add_notice( __( 'Please choose a payment method.', 'woocommerce' ), 'error' );
			}
		}
	}
	
	return $data;
}
add_filter( 'woocommerce_checkout_posted_data', 'stirjoy_ensure_standard_checkout_validation', 5, 2 );


/**
 * AJAX Handler for Social Login
 * Processes Google, Facebook, and Apple login data
 */
function stirjoy_social_login_handler() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'stirjoy_nonce' ) ) {
        wp_send_json_error( array( 'message' => 'Security check failed.' ) );
        return;
    }
    
    // Get provider and user data
    $provider = isset( $_POST['provider'] ) ? sanitize_text_field( $_POST['provider'] ) : '';
    $user_data = isset( $_POST['user_data'] ) ? $_POST['user_data'] : array();
    
    if ( empty( $provider ) || empty( $user_data ) ) {
        wp_send_json_error( array( 'message' => 'Invalid request data.' ) );
        return;
    }
    
    // Extract user information
    $social_id = isset( $user_data['id'] ) ? sanitize_text_field( $user_data['id'] ) : '';
    $email = isset( $user_data['email'] ) ? sanitize_email( $user_data['email'] ) : '';
    $first_name = isset( $user_data['first_name'] ) ? sanitize_text_field( $user_data['first_name'] ) : '';
    $last_name = isset( $user_data['last_name'] ) ? sanitize_text_field( $user_data['last_name'] ) : '';
    $name = isset( $user_data['name'] ) ? sanitize_text_field( $user_data['name'] ) : '';
    
    // If no email, generate a placeholder (some providers don't provide email)
    if ( empty( $email ) ) {
        $email = $provider . '_' . $social_id . '@' . $provider . '.temp';
    }
    
    // Check if user already exists by email
    $user = email_exists( $email ) ? get_user_by( 'email', $email ) : false;
    
    // If user doesn't exist, create new user
    if ( ! $user ) {
        // Generate username from email or name
        $username = ! empty( $email ) ? sanitize_user( current( explode( '@', $email ) ) ) : sanitize_user( $name );
        
        // If username exists, append number
        if ( username_exists( $username ) ) {
            $username = $username . '_' . rand( 1000, 9999 );
        }
        
        // If still empty, use provider + ID
        if ( empty( $username ) ) {
            $username = $provider . '_' . $social_id;
        }
        
        // Generate random password (user won't need it for social login)
        $password = wp_generate_password( 20, false );
        
        // Create user
        $user_id = wp_create_user( $username, $password, $email );
        
        if ( is_wp_error( $user_id ) ) {
            wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
            return;
        }
        
        // Get user object
        $user = get_user_by( 'id', $user_id );
        
        // Update user meta
        if ( ! empty( $first_name ) ) {
            update_user_meta( $user_id, 'first_name', $first_name );
            update_user_meta( $user_id, 'billing_first_name', $first_name );
        }
        if ( ! empty( $last_name ) ) {
            update_user_meta( $user_id, 'last_name', $last_name );
            update_user_meta( $user_id, 'billing_last_name', $last_name );
        }
        
        // Set display name
        $display_name = ! empty( $name ) ? $name : ( trim( $first_name . ' ' . $last_name ) );
        if ( ! empty( $display_name ) ) {
            wp_update_user( array(
                'ID' => $user_id,
                'display_name' => $display_name
            ) );
        }
        
        // Store social login provider info
        update_user_meta( $user_id, '_stirjoy_social_provider', $provider );
        update_user_meta( $user_id, '_stirjoy_social_id', $social_id );
        update_user_meta( $user_id, '_stirjoy_just_registered', time() );
        
        // Set user role (WooCommerce customer)
        $user->set_role( 'customer' );
        
        // Trigger WooCommerce customer creation hook
        do_action( 'woocommerce_created_customer', $user_id, array(
            'user_login' => $username,
            'user_email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name,
        ), $password );
    } else {
        // User exists, update social login info
        update_user_meta( $user->ID, '_stirjoy_social_provider', $provider );
        update_user_meta( $user->ID, '_stirjoy_social_id', $social_id );
    }
    
    // Save guest cart before login (if WooCommerce is active)
    $guest_cart_contents = array();
    if ( class_exists( 'WooCommerce' ) && WC()->cart && ! WC()->cart->is_empty() ) {
        $guest_cart_contents = WC()->cart->get_cart();
        // Force save session before login
        if ( WC()->session ) {
            WC()->session->save_data();
        }
    }
    
    // Log the user in
    wp_clear_auth_cookie();
    wp_set_current_user( $user->ID );
    wp_set_auth_cookie( $user->ID, true );
    do_action( 'wp_login', $user->user_login, $user );
    
    // After login, save guest cart to transient for merging
    if ( class_exists( 'WooCommerce' ) && WC()->cart && ! empty( $guest_cart_contents ) ) {
        // Save guest cart to transient (will be merged by stirjoy_merge_guest_cart_with_user_cart)
        set_transient( 'stirjoy_guest_cart_before_login_' . $user->ID, array(
            'cart' => $guest_cart_contents,
            'totals' => WC()->cart->get_totals(),
            'coupons' => WC()->cart->get_applied_coupons(),
        ), 600 );
        
        // Also save to user meta as backup
        update_user_meta( $user->ID, '_stirjoy_guest_cart_backup', array(
            'cart' => $guest_cart_contents,
            'totals' => WC()->cart->get_totals(),
            'coupons' => WC()->cart->get_applied_coupons(),
        ) );
        
        // Reinitialize WooCommerce session for logged-in user
        WC()->session->init();
        
        // IMPORTANT: Perform merge immediately before redirect (for SiteGround)
        // This ensures cart is merged before the next page loads
        $merge_result = stirjoy_merge_guest_cart_with_user_cart( $user->ID );
        
        if ( $merge_result ) {
            error_log( 'Stirjoy SiteGround: Cart merge completed before redirect for user: ' . $user->ID );
        } else {
            error_log( 'Stirjoy SiteGround: Cart merge failed before redirect for user: ' . $user->ID . ' - will retry on next page load' );
        }
        
        // Force session save after merge (multiple saves for SiteGround reliability)
        if ( WC()->session ) {
            WC()->session->save_data();
            // Additional save for SiteGround reliability
            if ( function_exists( 'sg_cachepress_purge_cache' ) || class_exists( 'SiteGround_Optimizer\Supercacher\Supercacher' ) ) {
                usleep( 100000 ); // 0.1 second delay
                WC()->session->save_data();
            }
        }
        
        // Force cart recalculation to ensure accurate count
        if ( WC()->cart ) {
            WC()->cart->calculate_totals();
            // Force another calculation to ensure all items are registered
            WC()->cart->get_cart();
        }
    }
    
    // Get accurate cart count after merge using helper function
    // Small delay for SiteGround to ensure cart is fully loaded
    if ( function_exists( 'sg_cachepress_purge_cache' ) || class_exists( 'SiteGround_Optimizer\Supercacher\Supercacher' ) ) {
        usleep( 50000 ); // 0.05 second delay
        if ( WC()->cart ) {
            WC()->cart->calculate_totals();
        }
    }
    
    $cart_count = stirjoy_get_accurate_cart_count();
    
    // Redirect to checkout
    $checkout_url = wc_get_checkout_url();
    
    wp_send_json_success( array(
        'message' => 'Login successful!',
        'redirect_url' => $checkout_url,
        'user_id' => $user->ID,
        'cart_count' => $cart_count,
        'cart_merged' => isset( $merge_result ) ? $merge_result : false,
        'cart_ready' => true // Flag to indicate cart is ready before redirect
    ) );
}
add_action( 'wp_ajax_stirjoy_social_login', 'stirjoy_social_login_handler' );
add_action( 'wp_ajax_nopriv_stirjoy_social_login', 'stirjoy_social_login_handler' );

/**
 * AJAX endpoint to manually trigger cart merge (for SiteGround debugging/fixing)
 */
function stirjoy_manual_cart_merge() {
    check_ajax_referer( 'stirjoy_nonce', 'nonce' );
    
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'User must be logged in' ) );
    }
    
    $user_id = get_current_user_id();
    
    // Clear merge completion flag to force retry
    delete_user_meta( $user_id, '_stirjoy_cart_merge_completed' );
    
    // Attempt merge
    $result = stirjoy_merge_guest_cart_with_user_cart( $user_id );
    
    if ( $result ) {
        $cart_count = WC()->cart->get_cart_contents_count();
        wp_send_json_success( array(
            'message' => 'Cart merge successful',
            'cart_count' => $cart_count,
            'cart_total' => WC()->cart->get_cart_subtotal()
        ) );
    } else {
        wp_send_json_error( array(
            'message' => 'Cart merge failed. Check error logs for details.',
            'has_guest_cart' => ! empty( get_transient( 'stirjoy_guest_cart_before_login_' . $user_id ) ) || ! empty( get_user_meta( $user_id, '_stirjoy_guest_cart_backup', true ) )
        ) );
    }
}
add_action( 'wp_ajax_stirjoy_manual_cart_merge', 'stirjoy_manual_cart_merge' );

/**
 * Cleanup old database option backups (runs daily)
 */
function stirjoy_cleanup_old_cart_backups() {
    global $wpdb;
    
    // Delete options older than 24 hours
    $wpdb->query( $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND option_id < %d",
        'stirjoy_guest_cart_%',
        time() - 86400
    ) );
}
add_action( 'wp_scheduled_delete', 'stirjoy_cleanup_old_cart_backups' );

/**
 * Make postcode validation more lenient
 * Accepts any postcode that contains alphanumeric characters and common separators
 * This fixes issues where valid postcodes are rejected due to strict country-specific validation
 */
function stirjoy_relax_postcode_validation( $valid, $postcode, $country ) {
	// If WooCommerce already validated it as valid, keep it valid
	if ( $valid ) {
		return $valid;
	}
	
	// If postcode is empty, let WooCommerce handle it (required field validation)
	$trimmed_postcode = trim( $postcode );
	if ( empty( $trimmed_postcode ) ) {
		return $valid;
	}
	
	// Remove whitespace and common separators for validation
	$clean_postcode = str_replace( array( ' ', '-', '_', '.' ), '', $trimmed_postcode );
	
	// Accept if postcode contains only alphanumeric characters and is at least 2 characters long
	// This is more lenient than country-specific validation
	if ( preg_match( '/^[A-Za-z0-9]{2,}$/i', $clean_postcode ) ) {
		error_log( 'Stirjoy: Postcode validation passed (alphanumeric): ' . $postcode . ' for country: ' . $country );
		return true;
	}
	
	// For countries with numeric-only postcodes, accept if it's all digits and reasonable length (2-12 digits)
	if ( preg_match( '/^[0-9]{2,12}$/', $clean_postcode ) ) {
		error_log( 'Stirjoy: Postcode validation passed (numeric): ' . $postcode . ' for country: ' . $country );
		return true;
	}
	
	// If postcode contains valid characters (letters, numbers, spaces, hyphens, periods), accept it
	// This is a very lenient check - only reject if it contains clearly invalid characters
	// Minimum length of 2 characters to avoid accepting single characters
	if ( ! preg_match( '/[^A-Za-z0-9\s\-\.]/', $trimmed_postcode ) && strlen( $trimmed_postcode ) >= 2 ) {
		error_log( 'Stirjoy: Postcode validation passed (lenient): ' . $postcode . ' for country: ' . $country );
		return true;
	}
	
	// Log when validation fails for debugging
	error_log( 'Stirjoy: Postcode validation failed: ' . $postcode . ' for country: ' . $country );
	
	// Return original validation result if none of the lenient checks pass
	return $valid;
}
add_filter( 'woocommerce_validate_postcode', 'stirjoy_relax_postcode_validation', 10, 3 );

/**
 * Disable WooCommerce Payments Express Checkout on checkout page
 * Prevents wcpay-express-checkout-wrapper from being displayed
 */
function stirjoy_disable_wcpay_express_checkout() {
	if ( ! is_checkout() ) {
		return;
	}
	
	// Remove all possible hooks that display express checkout
	// Try to remove using class name (static method)
	if ( class_exists( 'WC_Payments_Express_Checkout_Button_Display_Handler' ) ) {
		remove_action( 'woocommerce_checkout_before_customer_details', array( 'WC_Payments_Express_Checkout_Button_Display_Handler', 'display_express_checkout_buttons' ), 1 );
	}
	
	// Remove using global if available
	global $wcpay_express_checkout_button_display_handler;
	if ( isset( $wcpay_express_checkout_button_display_handler ) && is_object( $wcpay_express_checkout_button_display_handler ) ) {
		remove_action( 'woocommerce_checkout_before_customer_details', array( $wcpay_express_checkout_button_display_handler, 'display_express_checkout_buttons' ), 1 );
		remove_action( 'woocommerce_proceed_to_checkout', array( $wcpay_express_checkout_button_display_handler, 'display_express_checkout_buttons' ), 21 );
		remove_action( 'woocommerce_after_add_to_cart_form', array( $wcpay_express_checkout_button_display_handler, 'display_express_checkout_buttons' ), 1 );
		remove_action( 'woocommerce_pay_order_before_payment', array( $wcpay_express_checkout_button_display_handler, 'display_express_checkout_buttons' ), 1 );
	}
	
	// Try to remove all instances by checking all registered hooks
	global $wp_filter;
	if ( isset( $wp_filter['woocommerce_checkout_before_customer_details'] ) ) {
		$callbacks = $wp_filter['woocommerce_checkout_before_customer_details']->callbacks;
		if ( isset( $callbacks[1] ) ) {
			foreach ( $callbacks[1] as $callback_key => $callback ) {
				if ( is_array( $callback['function'] ) && is_object( $callback['function'][0] ) ) {
					$class_name = get_class( $callback['function'][0] );
					if ( strpos( $class_name, 'WC_Payments_Express_Checkout' ) !== false || 
					     strpos( $class_name, 'Express_Checkout_Button' ) !== false ) {
						remove_action( 'woocommerce_checkout_before_customer_details', $callback['function'], 1 );
					}
				}
			}
		}
	}
}
add_action( 'init', 'stirjoy_disable_wcpay_express_checkout', 999 );
add_action( 'wp', 'stirjoy_disable_wcpay_express_checkout', 999 );
add_action( 'template_redirect', 'stirjoy_disable_wcpay_express_checkout', 999 );

/**
 * Filter to disable WooPay and Express Checkout buttons
 * These filters prevent the buttons from being enabled in the first place
 */
add_filter( 'wcpay_woopay_enabled', '__return_false', 999 );
add_filter( 'wcpay_payment_request_enabled', '__return_false', 999 );
add_filter( 'wcpay_express_checkout_enabled', '__return_false', 999 );
add_filter( 'wcpay_should_show_woopay_button', '__return_false', 999 );
add_filter( 'wcpay_should_show_express_checkout_button', '__return_false', 999 );

/**
 * Remove WooCommerce order details and customer details sections from order received page
 * These sections are always hidden to match the custom design
 */
function stirjoy_remove_order_details_sections() {
	// Remove order details table from thankyou page
	remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );
	
	// Remove customer details from order details (displayed after order table)
	remove_action( 'woocommerce_order_details_after_order_table', 'woocommerce_order_again_button', 10 );
	
	// Also hide via CSS as a fallback (in case other plugins add these sections)
	add_action( 'wp_head', function() {
		if ( is_wc_endpoint_url( 'order-received' ) || is_wc_endpoint_url( 'order-received' ) ) {
			echo '<style>
				.woocommerce-order-details,
				.woocommerce-customer-details,
				section.woocommerce-order-details,
				section.woocommerce-customer-details {
					display: none !important;
					visibility: hidden !important;
					height: 0 !important;
					overflow: hidden !important;
					position: absolute !important;
					left: -9999px !important;
					opacity: 0 !important;
				}
			</style>';
		}
	}, 999 );
}
add_action( 'init', 'stirjoy_remove_order_details_sections', 20 );

/**
 * CRITICAL: Ensure Stripe payment gateway is available on checkout
 * This prevents Stripe from being blocked by other plugins or filters
 */
function stirjoy_ensure_stripe_available( $available_gateways ) {
    // Only run on checkout page
    if ( ! is_checkout() && ! is_wc_endpoint_url( 'order-pay' ) ) {
        return $available_gateways;
    }
    
    // Check if Stripe gateway exists but is not available
    $stripe_gateway_ids = array( 'stripe', 'stripe_cc', 'stripe_credit_card', 'woocommerce_payments' );
    
    if ( ! WC()->payment_gateways ) {
        return $available_gateways;
    }
    
    $all_gateways = WC()->payment_gateways->payment_gateways();
    
    foreach ( $stripe_gateway_ids as $gateway_id ) {
        if ( isset( $all_gateways[ $gateway_id ] ) ) {
            $gateway = $all_gateways[ $gateway_id ];
            
            // If gateway is enabled but not in available gateways, add it
            if ( $gateway->enabled === 'yes' && ! isset( $available_gateways[ $gateway_id ] ) ) {
                // Check if gateway is available (not blocked by other conditions)
                // Use try-catch to safely check availability
                try {
                    if ( method_exists( $gateway, 'is_available' ) && $gateway->is_available() ) {
                        $available_gateways[ $gateway_id ] = $gateway;
                        error_log( 'Stirjoy: Added Stripe gateway ' . $gateway_id . ' to available gateways' );
                    } else {
                        // Try to get error message if method exists
                        $error_msg = '';
                        if ( method_exists( $gateway, 'get_error_message' ) ) {
                            $error_msg = $gateway->get_error_message();
                        } elseif ( method_exists( $gateway, 'get_error_messages' ) ) {
                            $errors = $gateway->get_error_messages();
                            if ( is_array( $errors ) && ! empty( $errors ) ) {
                                $error_msg = implode( ', ', $errors );
                            }
                        }
                        if ( $error_msg ) {
                            error_log( 'Stirjoy: Stripe gateway ' . $gateway_id . ' is enabled but not available: ' . $error_msg );
                        } else {
                            error_log( 'Stirjoy: Stripe gateway ' . $gateway_id . ' is enabled but is_available() returned false' );
                        }
                    }
                } catch ( Exception $e ) {
                    error_log( 'Stirjoy: Error checking Stripe gateway ' . $gateway_id . ' availability: ' . $e->getMessage() );
                }
            }
        }
    }
    
    return $available_gateways;
}
add_filter( 'woocommerce_available_payment_gateways', 'stirjoy_ensure_stripe_available', 999 );

/**
 * CRITICAL: Log Stripe gateway availability for debugging
 */
function stirjoy_log_stripe_availability() {
    if ( ! is_checkout() || ! class_exists( 'WooCommerce' ) || ! WC()->payment_gateways ) {
        return;
    }
    
    $all_gateways = WC()->payment_gateways->payment_gateways();
    $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
    
    $stripe_gateway_ids = array( 'stripe', 'stripe_cc', 'stripe_credit_card', 'woocommerce_payments' );
    
    foreach ( $stripe_gateway_ids as $gateway_id ) {
        if ( isset( $all_gateways[ $gateway_id ] ) ) {
            $gateway = $all_gateways[ $gateway_id ];
            $is_enabled = $gateway->enabled === 'yes';
            $is_available = isset( $available_gateways[ $gateway_id ] );
            $is_available_method = $gateway->is_available();
            
            error_log( 'Stirjoy Stripe Debug: Gateway ' . $gateway_id . 
                      ' - Enabled: ' . ( $is_enabled ? 'Yes' : 'No' ) . 
                      ' - Available Method: ' . ( $is_available_method ? 'Yes' : 'No' ) . 
                      ' - In Available Gateways: ' . ( $is_available ? 'Yes' : 'No' ) );
        }
    }
}
add_action( 'wp', 'stirjoy_log_stripe_availability', 999 );

/**
 * CRITICAL: Server-side AJAX endpoint to create Stripe payment method from card details
 * This is needed because Stripe.js doesn't allow creating payment methods with raw card details on client side
 */
function stirjoy_create_stripe_payment_method() {
	// Set proper headers for JSON response
	header( 'Content-Type: application/json' );
	
	// Verify nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'stirjoy_stripe_payment_method' ) ) {
		error_log( 'Stirjoy: Invalid nonce in payment method creation' );
		wp_send_json_error( array( 'message' => 'Invalid security token. Please refresh the page and try again.' ) );
		return;
	}
	
	// Ensure WooCommerce is loaded
	if ( ! function_exists( 'WC' ) || ! WC()->payment_gateways ) {
		error_log( 'Stirjoy: WooCommerce not loaded in payment method creation' );
		wp_send_json_error( array( 'message' => 'WooCommerce is not available. Please refresh the page.' ) );
		return;
	}
	
	// Get card details from POST
	$card_number = isset( $_POST['card_number'] ) ? sanitize_text_field( wp_unslash( $_POST['card_number'] ) ) : '';
	$exp_month = isset( $_POST['exp_month'] ) ? intval( $_POST['exp_month'] ) : 0;
	$exp_year = isset( $_POST['exp_year'] ) ? intval( $_POST['exp_year'] ) : 0;
	$cvc = isset( $_POST['cvc'] ) ? sanitize_text_field( wp_unslash( $_POST['cvc'] ) ) : '';
	$card_name = isset( $_POST['card_name'] ) ? sanitize_text_field( wp_unslash( $_POST['card_name'] ) ) : '';
	
	// Get test token and test mode flags
	$test_token = isset( $_POST['test_token'] ) ? sanitize_text_field( wp_unslash( $_POST['test_token'] ) ) : '';
	$is_test_card = isset( $_POST['is_test_card'] ) ? sanitize_text_field( wp_unslash( $_POST['is_test_card'] ) ) : '0';
	$test_mode = isset( $_POST['test_mode'] ) ? sanitize_text_field( wp_unslash( $_POST['test_mode'] ) ) : '0';
	
	// Log test information
	error_log( 'Stirjoy: Test mode: ' . $test_mode . ', Is test card: ' . $is_test_card . ', Test token: ' . $test_token );
	
	// If test token is provided and valid, return it directly (for testing purposes)
	if ( ! empty( $test_token ) && strpos( $test_token, 'pm_test_' ) === 0 ) {
		error_log( 'Stirjoy: Using test token directly: ' . $test_token );
		wp_send_json_success( array( 'payment_method_id' => $test_token ) );
		return;
	}
	
	// Validate inputs
	if ( empty( $card_number ) || strlen( $card_number ) < 13 ) {
		wp_send_json_error( array( 'message' => 'Invalid card number.' ) );
		return;
	}
	
	if ( $exp_month < 1 || $exp_month > 12 ) {
		wp_send_json_error( array( 'message' => 'Invalid expiry month.' ) );
		return;
	}
	
	// Validate expiry year (handle both YY and YYYY formats)
	$current_year = (int) date( 'Y' );
	if ( $exp_year < $current_year ) {
		wp_send_json_error( array( 'message' => 'Card has expired.' ) );
		return;
	}
	
	// Check if expiry is in current month/year (card expires at end of month)
	if ( $exp_year === $current_year ) {
		$current_month = (int) date( 'n' );
		if ( $exp_month < $current_month ) {
			wp_send_json_error( array( 'message' => 'Card has expired.' ) );
			return;
		}
	}
	
	if ( empty( $cvc ) || strlen( $cvc ) < 3 ) {
		wp_send_json_error( array( 'message' => 'Invalid security code.' ) );
		return;
	}
	
	// Check if Stripe gateway is available
	if ( ! class_exists( 'WC_Stripe_Payment_Gateway' ) && ! class_exists( 'WC_Gateway_Stripe' ) ) {
		wp_send_json_error( array( 'message' => 'Stripe payment gateway is not available.' ) );
		return;
	}
	
	// Get Stripe gateway instance
	$gateways = WC()->payment_gateways->get_available_payment_gateways();
	$stripe_gateway = null;
	
	foreach ( $gateways as $gateway_id => $gateway ) {
		if ( stripos( $gateway_id, 'stripe' ) !== false ) {
			$stripe_gateway = $gateway;
			break;
		}
	}
	
	if ( ! $stripe_gateway ) {
		wp_send_json_error( array( 'message' => 'Stripe payment gateway is not enabled.' ) );
		return;
	}
	
	// Create payment method using Stripe API
	try {
		// Use WooCommerce Stripe API helper if available
		if ( class_exists( 'WC_Stripe_API' ) ) {
			error_log( 'Stirjoy: Creating payment method using WC_Stripe_API' );
			
			$response = WC_Stripe_API::request(
				array(
					'type' => 'card',
					'card' => array(
						'number' => $card_number,
						'exp_month' => $exp_month,
						'exp_year' => $exp_year,
						'cvc' => $cvc,
					),
					'billing_details' => array(
						'name' => $card_name,
					),
				),
				'payment_methods'
			);
			
			error_log( 'Stirjoy: WC_Stripe_API response: ' . print_r( $response, true ) );
			
			if ( is_wp_error( $response ) ) {
				error_log( 'Stirjoy: WP_Error in payment method creation: ' . $response->get_error_message() );
				wp_send_json_error( array( 'message' => $response->get_error_message() ) );
				return;
			}
			
			if ( ! empty( $response->error ) ) {
				// Comprehensive Stripe error logging
				$stripe_error = $response->error;
				$error_details = array(
					'type' => isset( $stripe_error->type ) ? $stripe_error->type : 'unknown',
					'code' => isset( $stripe_error->code ) ? $stripe_error->code : 'unknown',
					'message' => isset( $stripe_error->message ) ? $stripe_error->message : 'Unknown Stripe error',
					'decline_code' => isset( $stripe_error->decline_code ) ? $stripe_error->decline_code : null,
					'param' => isset( $stripe_error->param ) ? $stripe_error->param : null,
					'full_error' => $stripe_error,
				);
				
				error_log( '=== STIRJOY STRIPE API ERROR ===' );
				error_log( 'Error Type: ' . $error_details['type'] );
				error_log( 'Error Code: ' . $error_details['code'] );
				error_log( 'Error Message: ' . $error_details['message'] );
				error_log( 'Decline Code: ' . ( $error_details['decline_code'] ? $error_details['decline_code'] : 'N/A' ) );
				error_log( 'Parameter: ' . ( $error_details['param'] ? $error_details['param'] : 'N/A' ) );
				error_log( 'Full Error Object: ' . print_r( $stripe_error, true ) );
				error_log( '===============================' );
				
				// Build detailed error message
				$error_message = $error_details['message'];
				if ( $error_details['decline_code'] ) {
					$error_message .= ' (Decline Code: ' . $error_details['decline_code'] . ')';
				}
				
				wp_send_json_error( array( 
					'message' => $error_message,
					'error_details' => $error_details,
					'error_code' => $error_details['code'],
					'decline_code' => $error_details['decline_code'],
				) );
				return;
			}
			
			if ( ! empty( $response->id ) ) {
				error_log( 'Stirjoy: Payment method created successfully: ' . $response->id );
				wp_send_json_success( array( 'payment_method_id' => $response->id ) );
				return;
			} else {
				error_log( 'Stirjoy: Payment method creation response missing ID: ' . print_r( $response, true ) );
				wp_send_json_error( array( 'message' => 'Invalid response from payment processor. Please try again.' ) );
				return;
			}
		} else {
			error_log( 'Stirjoy: WC_Stripe_API class not found, trying fallback' );
			// Fallback: Try to load Stripe API class
			$stripe_api_path = ABSPATH . 'wp-content/plugins/woocommerce-gateway-stripe/includes/class-wc-stripe-api.php';
			if ( file_exists( $stripe_api_path ) ) {
				require_once $stripe_api_path;
				
				if ( class_exists( 'WC_Stripe_API' ) ) {
					$response = WC_Stripe_API::request(
						array(
							'type' => 'card',
							'card' => array(
								'number' => $card_number,
								'exp_month' => $exp_month,
								'exp_year' => $exp_year,
								'cvc' => $cvc,
							),
							'billing_details' => array(
								'name' => $card_name,
							),
						),
						'payment_methods'
					);
					
					if ( is_wp_error( $response ) ) {
						wp_send_json_error( array( 'message' => $response->get_error_message() ) );
						return;
					}
					
					if ( ! empty( $response->error ) ) {
						// Comprehensive Stripe error logging (fallback)
						$stripe_error = $response->error;
						$error_details = array(
							'type' => isset( $stripe_error->type ) ? $stripe_error->type : 'unknown',
							'code' => isset( $stripe_error->code ) ? $stripe_error->code : 'unknown',
							'message' => isset( $stripe_error->message ) ? $stripe_error->message : 'Unknown Stripe error',
							'decline_code' => isset( $stripe_error->decline_code ) ? $stripe_error->decline_code : null,
							'param' => isset( $stripe_error->param ) ? $stripe_error->param : null,
						);
						
						error_log( '=== STIRJOY STRIPE API ERROR (FALLBACK) ===' );
						error_log( 'Error Type: ' . $error_details['type'] );
						error_log( 'Error Code: ' . $error_details['code'] );
						error_log( 'Error Message: ' . $error_details['message'] );
						error_log( 'Full Error: ' . print_r( $stripe_error, true ) );
						error_log( '===========================================' );
						
						$error_message = $error_details['message'];
						if ( $error_details['decline_code'] ) {
							$error_message .= ' (Decline Code: ' . $error_details['decline_code'] . ')';
						}
						
						wp_send_json_error( array( 
							'message' => $error_message,
							'error_details' => $error_details,
						) );
						return;
					}
					
					if ( ! empty( $response->id ) ) {
						error_log( 'Stirjoy: Payment method created successfully (fallback): ' . $response->id );
						wp_send_json_success( array( 'payment_method_id' => $response->id ) );
						return;
					} else {
						error_log( 'Stirjoy: Payment method creation response missing ID (fallback): ' . print_r( $response, true ) );
						wp_send_json_error( array( 'message' => 'Invalid response from payment processor. Please try again.' ) );
						return;
					}
				} else {
					error_log( 'Stirjoy: WC_Stripe_API class still not found after fallback attempt' );
					wp_send_json_error( array( 'message' => 'Stripe payment gateway is not properly configured. Please contact support.' ) );
					return;
				}
			} else {
				error_log( 'Stirjoy: Stripe API file not found at: ' . $stripe_api_path );
				wp_send_json_error( array( 'message' => 'Stripe payment gateway files not found. Please contact support.' ) );
				return;
			}
		}
		
		// If we reach here, something went wrong
		error_log( 'Stirjoy: Failed to create payment method - no valid response from Stripe API' );
		wp_send_json_error( array( 'message' => 'Failed to create payment method. Please try again or contact support if the problem persists.' ) );
		
	} catch ( Exception $e ) {
		// Comprehensive exception logging
		$exception_details = array(
			'message' => $e->getMessage(),
			'code' => $e->getCode(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
			'trace' => $e->getTraceAsString(),
		);
		
		error_log( '=== STIRJOY PAYMENT METHOD EXCEPTION ===' );
		error_log( 'Exception Message: ' . $e->getMessage() );
		error_log( 'Exception Code: ' . $e->getCode() );
		error_log( 'File: ' . $e->getFile() . ':' . $e->getLine() );
		error_log( 'Stack Trace: ' . $e->getTraceAsString() );
		error_log( 'Full Exception: ' . print_r( $exception_details, true ) );
		error_log( '========================================' );
		
		wp_send_json_error( array( 
			'message' => 'An error occurred while processing your card. Please try again.',
			'error_details' => $exception_details,
			'error_type' => 'exception',
		) );
	}
}
add_action( 'wp_ajax_stirjoy_create_stripe_payment_method', 'stirjoy_create_stripe_payment_method' );
add_action( 'wp_ajax_nopriv_stirjoy_create_stripe_payment_method', 'stirjoy_create_stripe_payment_method' );