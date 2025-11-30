<?php

/**
 * Doko Products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/doko-products.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woo.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.5.0
 *
 * @var mixed $ids List of product Ids.
 * @var mixed $columns Number of columns.
 * @var mixed $order_mode Order as DESC or ASC.
 * @var mixed $wrapper Yes or No.
 * @var mixed $doko_package_data Doko package data.
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}
global $doko_bundle_id;
$product_ids = explode( ',', $ids );
$product_ids = array_unique( $product_ids );
if ( !isset( $doko_bundle_content_id ) ) {
    $doko_bundle_content_id = 'first-step';
}
if ( !isset( $doko_current_page_data ) ) {
    $doko_current_page_data = [];
}
$query_args = array(
    'post_type'  => array('product', 'product_variation'),
    'post__in'   => $product_ids,
    'orderby'    => 'post__in',
    'columns'    => $columns,
    'order'      => $order_mode,
    'meta_query' => array(array(
        'key'     => '_stock_status',
        'value'   => 'instock',
        'compare' => 'IN',
    )),
);
$doko_display_variations_as_many = true;
$doko_current_screen = $doko_current_page_data;
$query_args['posts_per_page'] = -1;
$posts = new WP_Query($query_args);
if ( $posts->have_posts() ) {
    woocommerce_product_loop_start();
    while ( $posts->have_posts() ) {
        $posts->the_post();
        global $product;
        $product_type = $product->get_type();
        $childrens = $product->get_children();
        $childrens = array_unique( $childrens );
        if ( $doko_display_variations_as_many && count( $childrens ) > 0 && !in_array( $product_type, array('simple', 'variations') ) ) {
            if ( count( $childrens ) > 0 ) {
                foreach ( $childrens as $variation ) {
                    $product = wc_get_product( $variation );
                    // prevent product out of stock to be added to the bundle.
                    if ( !$product->is_in_stock() || apply_filters( 'doko_skip_product_in_shortcode_loop', false, $product->get_id() ) ) {
                        continue;
                    }
                    $GLOBALS['post'] = get_post( $variation );
                    setup_postdata( $GLOBALS['post'] );
                    wc_get_template_part( 'content', 'product' );
                    wp_reset_postdata();
                }
            }
        } else {
            // prevent product out of stock to be added to the bundle.
            if ( !$product->is_in_stock() || apply_filters( 'doko_skip_product_in_shortcode_loop', false, $product->get_id() ) ) {
                continue;
            }
            wc_get_template_part( 'content', 'product' );
            wp_reset_postdata();
        }
    }
    woocommerce_product_loop_end();
    wp_reset_postdata();
} else {
    wc_no_products_found();
    echo "<input type='hidden' name='doko_latest_position' value='yes' />";
}
do_action(
    'doko_display_after_loop',
    $posts,
    $doko_bundle_content_id,
    $doko_current_screen
);