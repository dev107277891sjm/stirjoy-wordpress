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
 */
function stirjoy_add_to_cart() {
    check_ajax_referer( 'stirjoy_nonce', 'nonce' );
    
    $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
    $quantity = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;
    
    if ( ! $product_id ) {
        wp_send_json_error( array( 'message' => 'Invalid product ID' ) );
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
 */
function stirjoy_get_cart_info() {
    check_ajax_referer( 'stirjoy_nonce', 'nonce' );
    
    // Get cart total as HTML
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
        'product_ids' => $product_ids_in_cart
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