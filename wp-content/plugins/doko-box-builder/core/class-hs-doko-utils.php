<?php

namespace Hs\Doko;

/**
 * Get WC Products by Name.
 *
 * @param  mixed $product_name Product Name.
 * @return mixed
 */
function hs_dk_get_wc_products(  $product_name  ) {
    /**
     * product options : 
     * 	--- Option 1 ==> stock validity
     *  --- Option 2 ==> product without prices
     *  --- Option 3 ==> product hidden
     * 
     */
    $product_options = [
        'stock' => [
            'key'   => '_stock_status',
            'value' => 'instock',
        ],
        'price' => [
            'key'     => '_regular_price',
            'value'   => '0',
            'compare' => '!=',
        ],
    ];
    // Prepare the query arguments for product retrieval
    $query_args = [
        'fields'           => 'ids',
        'post_type'        => ['product', 'product_variation'],
        'post_status'      => 'publish',
        'meta_query'       => array_values( $product_options ),
        'meta_compare_key' => 'AND',
        'search_columns'   => ['post_title'],
        'limit'            => -1,
    ];
    if ( $product_name ) {
        $query_args['s'] = $product_name;
    }
    $all_query = array();
    $query = new \WP_Query($query_args);
    foreach ( $query->posts as $query_key => $query_value ) {
        $product = wc_get_product( $query_value );
        $price = $product->get_price() . " " . get_woocommerce_currency();
        $ptitle = get_the_title( $query_value );
        $ptitle = html_entity_decode( $ptitle, ENT_NOQUOTES, 'utf-8' );
        $all_query[$query_value] = $ptitle . " | ({$price})";
    }
    return $all_query;
}

/**
 * Get WC Categories.
 *
 * @param  mixed $query List of queries.
 * @return mixed
 */
function hs_dk_get_wc_categories(  $query = false  ) {
    $args = array();
    if ( $query ) {
        $args['search'] = $query;
    }
    $catgs = get_terms( 'product_cat', $args );
    $all_ctgs = array();
    if ( !empty( $catgs ) ) {
        foreach ( $catgs as $catg_value ) {
            $ctitle = $catg_value->name;
            $ctitle = html_entity_decode( $ctitle, ENT_NOQUOTES, 'utf-8' );
            $all_ctgs[$catg_value->term_id] = $ctitle;
        }
    }
    return $all_ctgs;
}

/**
 * Get Product Data.
 *
 * @param  mixed $product_ids Products List ID.
 * @return mixed
 */
function hs_dk_get_data(  $ids, $is_product = false  ) {
    $product_lists = array();
    if ( empty( $ids ) ) {
        return $product_lists;
    }
    foreach ( $ids as $pid ) {
        if ( $is_product ) {
            $product = wc_get_product( $pid );
            if ( is_object( $product ) ) {
                $product_lists[$pid] = $product->get_title();
            }
        } else {
            $product_lists[$pid] = get_the_category_by_ID( $pid );
        }
    }
    return $product_lists;
}

/**
 * Provide selection data.
 *
 * @param  mixed $package_data Package Data.
 * @param  mixed $selection_mode Selection mode.
 * @param  mixed $selector_name Selector Name.
 * @return mixed
 */
function hs_dk_get_selection_mode(  $package_data, $selection_mode, $selector_name  ) {
    /**
     * Filter the selection data.
     *
     * @since 1.0.0
     */
    return apply_filters(
        'doko_get_selection_data',
        ( isset( $package_data[$selector_name] ) ? $package_data[$selector_name] : array() ),
        $package_data,
        $selection_mode,
        $selection_mode
    );
}

/**
 * Get Products Options
 */
function hs_dk_get_products_options() {
    /**
     * Filter the products options.
     *
     * @since 1.0.0
     */
    return apply_filters( 'doko_get_products_options_type', array(
        'low-price'  => esc_html__( 'Price (Low to High)', 'doko-bundle-builder' ),
        'high-price' => esc_html__( 'Price (High to Low)', 'doko-bundle-builder' ),
    ) );
}

/**
 * Get Products from a specific category.
 *
 * @param  mixed $category_id Category ID.
 * @return mixed
 */
function hs_dk_get_products_from_category(  $category_id  ) {
    $product_ids = wc_get_products( array(
        'category_id' => $category_id,
    ) );
    return array_map( function ( $products_obj ) {
        return $products_obj->get_id();
    }, $product_ids );
}

/**
 * Get Screen different disposition.
 */
function hs_dk_get_disposition(  $is_title = false  ) {
    $values = array(
        'products'           => array(
            'title'            => esc_html__( 'Bundle Products', 'doko-bundle-builder' ),
            'preview_img_link' => plugin_dir_url( __DIR__ ) . 'admin/img/products.png',
            'slug'             => 'products',
        ),
        'products-with-cart' => array(
            'title'            => esc_html__( 'Bundle Cart Content', 'doko-bundle-builder' ),
            'preview_img_link' => plugin_dir_url( __DIR__ ) . 'admin/img/products-with-cart.png',
            'slug'             => 'products-with-cart',
        ),
        'card-page-type'     => array(
            'title'            => esc_html__( 'Bundle Card Page', 'doko-bundle-builder' ),
            'preview_img_link' => plugin_dir_url( __DIR__ ) . 'admin/img/card-step.png',
            'slug'             => 'card-page-type',
        ),
    );
    if ( $is_title ) {
        $values['products']['preview_img_link'] = plugin_dir_url( __DIR__ ) . 'admin/img/choose-products.png';
        $values['products-with-cart']['preview_img_link'] = plugin_dir_url( __DIR__ ) . 'admin/img/choose-products-with-cart.png';
        $values['card-page-type']['preview_img_link'] = plugin_dir_url( __DIR__ ) . 'admin/img/card-step.png';
    }
    /**
     * Filter the screen disposition.
     *
     * @since 1.0.0
     */
    return apply_filters( 'doko_get_screen_disposition', $values );
}

if ( !function_exists( 'hs_dk_display_products_from_categories' ) ) {
    function hs_dk_display_products_from_categories(  $product_categories, $is_card_screen = false  ) {
        $query_args = array(
            'post_type'      => array('product', 'product_variation'),
            'post_status'    => 'publish',
            'fields'         => 'ids',
            'posts_per_page' => '-1',
            'tax_query'      => array(array(
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $product_categories,
            )),
        );
        $query = new \WP_Query($query_args);
        $products = $query->posts;
        echo \do_shortcode( '[doko_products ids="' . implode( ',', $products ) . '"   columns="3" order="ASC" is_card_screen="' . $is_card_screen . '"]' );
    }

}
if ( !function_exists( 'hs_dk_display_products_disposition' ) ) {
    function hs_dk_display_products_disposition(  $products, $is_card_screen = false  ) {
        global $doko_current_package_data;
        echo \do_shortcode( '[doko_products ids="' . implode( ',', $products ) . '"   columns="3" order="ASC" is_card_screen="' . $is_card_screen . '"]' );
    }

}
if ( !function_exists( 'hs_dk_display_products_from_tags__premium_only' ) ) {
}
if ( !function_exists( 'hs_dk_get_allowed_slug' ) ) {
    function hs_dk_get_allowed_slug() {
        return apply_filters( 'doko_get_allowed_slugs', array(
            'post_type' => array('doko-bundles', 'doko-bundles-rules'),
            'page'      => array('edit.php?post_type=doko-bundles', 'edit.php?post_type=doko-bundles-rules', 'edit.php?post_type=doko-bundles&page=doko-settings'),
        ) );
    }

}
function doko_display_products(  $atts  ) {
    global $is_doko_page;
    global $doko_is_infinite_loading;
    $is_doko_page = true;
    ob_start();
    $atts = shortcode_atts( array(
        'ids'            => '',
        'columns'        => 3,
        'order'          => 'ASC',
        'is_card_screen' => false,
    ), $atts );
    global $doko_bundle_id;
    $doko_package_data = get_post_meta( $doko_bundle_id, 'doko', true );
    $objBundle = array(
        'ids'               => $atts['ids'],
        'columns'           => $atts['columns'],
        'order_mode'        => $atts['order'],
        'is_card_screen'    => $atts['is_card_screen'],
        'doko_package_data' => $doko_package_data,
    );
    $doko_current_page_data = $doko_package_data;
    if ( is_ajax() ) {
        $doko_current_pageId = $_POST['bundleContentId'];
        $doko_current_page_data = $doko_current_page_data[$doko_current_pageId];
        $objBundle = array_merge( $objBundle, array(
            'doko_current_page_data' => $doko_current_page_data,
            'doko_bundle_content_id' => $doko_current_pageId,
        ) );
    }
    if ( $doko_is_infinite_loading ) {
    } else {
        wc_get_template( 'single-product/doko-products.php', $objBundle );
    }
    return ob_get_clean();
}

function doko_get_children_products(  $all_products  ) {
    $products = array();
    if ( is_array( $all_products ) ) {
        foreach ( $all_products as $pdtId ) {
            $pdt = wc_get_product( $pdtId );
            $childrens = $pdt->get_children();
            if ( count( $childrens ) > 0 ) {
                $dpt = array();
                foreach ( $childrens as $pt ) {
                    if ( is_object( $pt ) ) {
                        $dpt[] = $pt->get_id();
                    } else {
                        $dpt[] = $pt;
                    }
                }
                $products = array_merge( $products, $dpt );
            } else {
                $products[] = $pdt->get_id();
            }
        }
    }
    return $products;
}

if ( !function_exists( 'doko_template_loop_add_to_cart' ) ) {
    /**
     * Get the add to cart template for the loop.
     *
     * @param array $args Arguments.
     */
    function doko_template_loop_add_to_cart(  $args = array()  ) {
        global $product;
        if ( $product ) {
            $defaults = array(
                'quantity'   => 1,
                'class'      => implode( ' ', array_filter( array(
                    'button',
                    wc_wp_theme_get_element_class_name( 'button' ),
                    // escaped in the template.
                    'product_type_' . $product->get_type(),
                    ( $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '' ),
                    ( $product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ? 'ajax_add_to_cart' : '' ),
                ) ) ),
                'attributes' => array(
                    'data-product_id'  => $product->get_id(),
                    'data-product_sku' => $product->get_sku(),
                    'aria-label'       => $product->add_to_cart_description(),
                    'aria-describedby' => $product->add_to_cart_aria_describedby(),
                    'rel'              => 'nofollow',
                ),
            );
            $args = apply_filters( 'woocommerce_loop_add_to_cart_args', wp_parse_args( $args, $defaults ), $product );
            if ( isset( $args['attributes']['aria-describedby'] ) ) {
                $args['attributes']['aria-describedby'] = wp_strip_all_tags( $args['attributes']['aria-describedby'] );
            }
            if ( isset( $args['attributes']['aria-label'] ) ) {
                $args['attributes']['aria-label'] = wp_strip_all_tags( $args['attributes']['aria-label'] );
            }
            $args['aria-describedby_text'] = $product->add_to_cart_aria_describedby() . $product->get_id();
            wc_get_template( 'loop/add-to-cart.php', $args );
        }
    }

}
if ( !function_exists( 'doko_template_loop_get_qty_button__premium_only' ) ) {
}
if ( !function_exists( 'doko_get_all_tags__premium_only' ) ) {
}