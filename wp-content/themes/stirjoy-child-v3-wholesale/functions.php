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
 */
function stirjoy_get_image_url($filename) {
    $image_path = get_stylesheet_directory_uri() . '/images/home page/' . $filename;
    return $image_path;
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
    // Force quantity to 1 - each product can only be added once
    $quantity = 1;
    
    if ( ! $product_id ) {
        wp_send_json_error( array( 'message' => 'Invalid product ID' ) );
    }
    
    // Check if product is already in cart
    $cart = WC()->cart->get_cart();
    foreach ( $cart as $cart_item_key => $cart_item ) {
        if ( $cart_item['product_id'] == $product_id ) {
            wp_send_json_error( array( 
                'message' => 'This product is already in your cart. Each product can only be added once.',
                'already_in_cart' => true,
                'cart_item_key' => $cart_item_key
            ) );
        }
    }
    
    $cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity );
    
    if ( $cart_item_key ) {
        wp_send_json_success( array(
            'message' => 'Product added to cart',
            'cart_item_key' => $cart_item_key,
            'cart_count' => WC()->cart->get_cart_contents_count(),
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
            'cart_count' => WC()->cart->get_cart_contents_count(),
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
 * AJAX: Get cart info for header update
 * Works for both logged-in and non-logged-in users
 */
function stirjoy_get_cart_info() {
    check_ajax_referer( 'stirjoy_nonce', 'nonce' );
    
    // Get cart total as HTML (works for both logged-in and guest users)
    $cart_subtotal_html = WC()->cart->get_cart_subtotal();
    
    // Also get plain number for potential use
    $cart_total_plain = WC()->cart->get_subtotal();
    
    // Get product IDs in cart
    $product_ids_in_cart = array();
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        $product_ids_in_cart[] = $cart_item['product_id'];
    }
    
    wp_send_json_success( array(
        'count' => WC()->cart->get_cart_contents_count(),
        'total_html' => $cart_subtotal_html,
        'total_plain' => wc_price( $cart_total_plain ),
        'cart_subtotal_numeric' => WC()->cart->get_subtotal(),
        'cart_hash' => WC()->cart->get_cart_hash(),
        'product_ids' => $product_ids_in_cart,
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
    $ingredients = get_post_meta( $product_id, '_ingredients', true );
    $allergens = get_post_meta( $product_id, '_allergens', true );
    $instructions = get_post_meta( $product_id, '_instructions', true );
    
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
    
    wp_send_json_success( array(
        'product_id' => $product_id,
        'name' => $product->get_name(),
        'description' => $description,
        'image_url' => $image_url,
        'price' => $product->get_price_html(),
        'rating' => $average_rating,
        'prep_time' => $prep_time,
        'cook_time' => $cook_time,
        'serving_size' => $serving_size,
        'calories' => $calories,
        'protein' => $protein,
        'carbs' => $carbs,
        'fat' => $fat,
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
 * Mark user as just registered for fallback redirect
 */
function stirjoy_mark_user_registered( $customer_id ) {
    update_user_meta( $customer_id, '_stirjoy_just_registered', time() );
}
add_action( 'woocommerce_created_customer', 'stirjoy_mark_user_registered', 5, 1 );

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
 */
function stirjoy_transfer_guest_cart_to_user( $user_login, $user ) {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }
    
    // Ensure WooCommerce is fully loaded
    if ( ! WC()->cart || ! WC()->session ) {
        return;
    }
    
    // Get the current cart (guest cart) BEFORE login
    $cart = WC()->cart;
    
    if ( ! $cart || $cart->is_empty() ) {
        return;
    }
    
    // Store guest cart contents BEFORE session migration
    $guest_cart_contents = $cart->get_cart();
    $cart_totals = $cart->get_totals();
    $applied_coupons = $cart->get_applied_coupons();
    $cart_hash = $cart->get_cart_hash();
    
    // IMPORTANT: Get existing user's persistent cart BEFORE overwriting
    $existing_persistent_cart_meta = get_user_meta( $user->ID, '_woocommerce_persistent_cart_' . get_current_blog_id(), true );
    $existing_user_cart = array();
    
    if ( is_array( $existing_persistent_cart_meta ) && isset( $existing_persistent_cart_meta['cart'] ) ) {
        $existing_user_cart = array_filter( (array) $existing_persistent_cart_meta['cart'] );
    }
    
    // Merge guest cart with existing user cart
    // WooCommerce uses array_merge where later values override earlier ones
    // So we merge existing_user_cart first, then guest_cart (guest cart takes precedence for duplicates)
    // But we want to combine quantities for same products, so we'll do a smart merge
    $merged_cart = array();
    
    // First, add all existing user cart items
    foreach ( $existing_user_cart as $cart_item_key => $cart_item ) {
        $merged_cart[ $cart_item_key ] = $cart_item;
    }
    
    // Then, merge guest cart items
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
    
    // Store merged cart in transient as backup (expires in 5 minutes)
    set_transient( 'stirjoy_guest_cart_' . $user->ID, array(
        'cart' => $merged_cart,
        'totals' => $cart_totals,
        'coupons' => $applied_coupons,
        'hash' => $cart_hash,
    ), 300 );
    
    // Force session save to ensure guest cart is persisted
    if ( WC()->session ) {
        WC()->session->save_data();
    }
    
    // Save merged cart to persistent cart in user meta
    if ( ! empty( $merged_cart ) ) {
        update_user_meta( $user->ID, '_woocommerce_persistent_cart_' . get_current_blog_id(), array(
            'cart' => $merged_cart,
        ) );
        
        // Set flag to load saved cart after login
        update_user_meta( $user->ID, '_woocommerce_load_saved_cart_after_login', true );
        
        // Also update session directly with merged cart (for immediate use)
        WC()->session->set( 'cart', $merged_cart );
        WC()->session->set( 'cart_totals', $cart_totals );
        WC()->session->set( 'applied_coupons', $applied_coupons );
        
        // Save session data immediately
        WC()->session->save_data();
        
        // Clear all cart-related caches
        wp_cache_delete( 'cart_' . $user->ID, 'woocommerce' );
        wp_cache_flush_group( 'woocommerce' );
    }
}
add_action( 'wp_login', 'stirjoy_transfer_guest_cart_to_user', 10, 2 );

/**
 * Ensure cart is loaded after login/registration
 * This handles cases where session migration doesn't happen immediately
 * Enhanced for SiteGround hosting compatibility
 */
function stirjoy_ensure_cart_after_login() {
    if ( ! is_user_logged_in() || ! class_exists( 'WooCommerce' ) ) {
        return;
    }
    
    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        return;
    }
    
    // Check if we need to restore cart from transient (backup method)
    $transient_key = 'stirjoy_guest_cart_' . $user_id;
    $saved_cart_data = get_transient( $transient_key );
    
    if ( $saved_cart_data && WC()->cart && WC()->session ) {
        // Restore cart from transient if session cart is empty
        $current_cart = WC()->cart->get_cart();
        
        if ( empty( $current_cart ) && ! empty( $saved_cart_data['cart'] ) ) {
            // Restore cart from backup
            WC()->session->set( 'cart', $saved_cart_data['cart'] );
            WC()->session->set( 'cart_totals', $saved_cart_data['totals'] );
            WC()->session->set( 'applied_coupons', $saved_cart_data['coupons'] );
            WC()->session->save_data();
            
            // Force cart recalculation
            WC()->cart->get_cart();
            WC()->cart->calculate_totals();
            
            // Delete transient after successful restore
            delete_transient( $transient_key );
        } elseif ( ! empty( $current_cart ) ) {
            // Cart exists, delete transient
            delete_transient( $transient_key );
        }
    }
    
    // Force WooCommerce to initialize session
    if ( WC()->session && ! WC()->session->has_session() ) {
        WC()->session->set_customer_session_cookie( true );
    }
    
    // Ensure cart is loaded and persistent cart is merged
    if ( WC()->cart ) {
        // Trigger cart load from session (this will merge persistent cart)
        WC()->cart->get_cart();
        
        // Ensure persistent cart flag is processed
        $load_saved_cart = get_user_meta( $user_id, '_woocommerce_load_saved_cart_after_login', true );
        if ( $load_saved_cart ) {
            // Force cart recalculation to ensure persistent cart is loaded
            WC()->cart->calculate_totals();
            delete_user_meta( $user_id, '_woocommerce_load_saved_cart_after_login' );
        }
    }
}
add_action( 'wp_loaded', 'stirjoy_ensure_cart_after_login', 20 );

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
 */
function stirjoy_siteground_cart_fixes() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }
    
    // Exclude WooCommerce cookies from SiteGround cache
    if ( function_exists( 'sg_cachepress_purge_cache' ) || class_exists( 'SiteGround_Optimizer\Supercacher\Supercacher' ) ) {
        // Add no-cache headers for cart/checkout pages
        if ( is_cart() || is_checkout() || is_account_page() ) {
            nocache_headers();
            header( 'Cache-Control: no-cache, no-store, must-revalidate, private' );
            header( 'Pragma: no-cache' );
            header( 'Expires: 0' );
        }
    }
    
    // Ensure WooCommerce session cookies are set correctly
    if ( WC()->session && ! headers_sent() ) {
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
		error_log( 'Stirjoy Payment Error: ' . $error_message . ' (Order ID: ' . $order_id . ')' );
		
		// Log gateway-specific errors
		if ( isset( $result['messages'] ) ) {
			error_log( 'Stirjoy Payment Error Messages: ' . print_r( $result['messages'], true ) );
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
			
			// Set as chosen payment method
			if ( empty( WC()->session->get( 'chosen_payment_method' ) ) ) {
				WC()->session->set( 'chosen_payment_method', $selected_gateway );
				error_log( 'Stirjoy: Set default payment method to ' . $selected_gateway );
			}
		}
	}
}
add_action( 'wp', 'stirjoy_set_default_payment_method' );

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
    
    // After login, ensure cart is transferred and merged with existing user cart
    if ( class_exists( 'WooCommerce' ) && WC()->cart && ! empty( $guest_cart_contents ) ) {
        // Reinitialize WooCommerce session for logged-in user
        WC()->session->init();
        
        // Get existing user's persistent cart BEFORE merging
        $existing_persistent_cart_meta = get_user_meta( $user->ID, '_woocommerce_persistent_cart_' . get_current_blog_id(), true );
        $existing_user_cart = array();
        
        if ( is_array( $existing_persistent_cart_meta ) && isset( $existing_persistent_cart_meta['cart'] ) ) {
            $existing_user_cart = array_filter( (array) $existing_persistent_cart_meta['cart'] );
        }
        
        // Get current session cart (might be empty or have guest cart)
        $current_session_cart = WC()->cart->get_cart();
        
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
        
        // Save merged cart to persistent storage
        if ( ! empty( $merged_cart ) ) {
            update_user_meta( $user->ID, '_woocommerce_persistent_cart_' . get_current_blog_id(), array(
                'cart' => $merged_cart,
            ) );
            
            // Set flag to load saved cart after login
            update_user_meta( $user->ID, '_woocommerce_load_saved_cart_after_login', true );
        }
        
        // Now add items to WooCommerce cart object (for immediate display)
        // Clear current cart first
        WC()->cart->empty_cart( false );
        
        // Add all merged cart items to WooCommerce cart
        foreach ( $merged_cart as $cart_item_key => $cart_item ) {
            $product_id = $cart_item['product_id'];
            $variation_id = isset( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : 0;
            $quantity = $cart_item['quantity'];
            $variation = isset( $cart_item['variation'] ) ? $cart_item['variation'] : array();
            
            WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation );
        }
        
        // Save cart
        WC()->cart->calculate_totals();
        if ( WC()->session ) {
            WC()->session->save_data();
        }
    }
    
    // Redirect to checkout
    $checkout_url = wc_get_checkout_url();
    
    wp_send_json_success( array(
        'message' => 'Login successful!',
        'redirect_url' => $checkout_url,
        'user_id' => $user->ID,
        'cart_count' => class_exists( 'WooCommerce' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0
    ) );
}
add_action( 'wp_ajax_stirjoy_social_login', 'stirjoy_social_login_handler' );
add_action( 'wp_ajax_nopriv_stirjoy_social_login', 'stirjoy_social_login_handler' );