<?php
/**
 * Cart Confirmation Functionality
 *
 * @package Stirjoy_Child
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * AJAX handler for confirming box
 */
function stirjoy_ajax_confirm_box() {
    check_ajax_referer( 'stirjoy_nonce', 'nonce' );
    
    if ( ! class_exists( 'WooCommerce' ) ) {
        wp_send_json_error( array( 'message' => 'WooCommerce not active' ) );
    }
    
    // Set session variable
    WC()->session->set( 'box_confirmed', true );
    WC()->session->set( 'box_confirmed_time', current_time( 'timestamp' ) );
    
    wp_send_json_success( array(
        'message' => __( 'Box confirmed!', 'stirjoy-child' ),
        'confirmed' => true,
    ));
}
add_action( 'wp_ajax_stirjoy_confirm_box', 'stirjoy_ajax_confirm_box' );
add_action( 'wp_ajax_nopriv_stirjoy_confirm_box', 'stirjoy_ajax_confirm_box' );

/**
 * AJAX handler for modifying selection
 */
function stirjoy_ajax_modify_selection() {
    check_ajax_referer( 'stirjoy_nonce', 'nonce' );
    
    if ( ! class_exists( 'WooCommerce' ) ) {
        wp_send_json_error( array( 'message' => 'WooCommerce not active' ) );
    }
    
    // Clear confirmation
    WC()->session->set( 'box_confirmed', false );
    
    wp_send_json_success( array(
        'message' => __( 'You can now modify your selection', 'stirjoy-child' ),
        'confirmed' => false,
    ));
}
add_action( 'wp_ajax_stirjoy_modify_selection', 'stirjoy_ajax_modify_selection' );
add_action( 'wp_ajax_nopriv_stirjoy_modify_selection', 'stirjoy_ajax_modify_selection' );

/**
 * Check if box is confirmed
 */
function stirjoy_is_box_confirmed() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return false;
    }
    return (bool) WC()->session->get( 'box_confirmed', false );
}

/**
 * Reset confirmation when cart is modified
 */
function stirjoy_reset_confirmation_on_cart_change() {
    if ( class_exists( 'WooCommerce' ) && WC()->session ) {
        WC()->session->set( 'box_confirmed', false );
    }
}
add_action( 'woocommerce_add_to_cart', 'stirjoy_reset_confirmation_on_cart_change' );
add_action( 'woocommerce_cart_item_removed', 'stirjoy_reset_confirmation_on_cart_change' );
add_action( 'woocommerce_cart_item_restored', 'stirjoy_reset_confirmation_on_cart_change' );

