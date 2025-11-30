<?php
/**
 * Load WooCommerce functions
 */

/**
 * WooCommerce - Shows the minicart in cart and checkout (default: not visible)
 */
if (!function_exists('thecrate_always_show_cart')) {
    function thecrate_always_show_cart() {
        return false;
    }
    add_filter( 'woocommerce_widget_cart_is_hidden', 'thecrate_always_show_cart', 40, 0 );
}

/**
 * WooCommerce - star ratings
 */
if (!function_exists('thecrate_woocommerce_star_rating')) {
	function thecrate_woocommerce_star_rating(){
		global $woocommerce, $product;
		$average = $product->get_average_rating();
		echo '<div class="star-rating"><span style="width:'.( ( $average / 5 ) * 100 ) . '%"><strong class="rating">'.esc_html($average).'</strong> '.esc_html__( 'out of 5', 'thecrate' ).'</span></div>';
	}
	add_action('woocommerce_before_shop_loop_item_title', 'thecrate_woocommerce_star_rating' );
}
    

/**
 * WooCommerce - remove breadcrumbs
 */
remove_action( 'woocommerce_before_main_content','woocommerce_breadcrumb', 20, 0);
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );

/**
 * Remove "Description" Heading Title @ WooCommerce Single Product Tabs
 */
add_filter( 'woocommerce_product_description_heading', '__return_null' );
add_filter( 'woocommerce_product_additional_information_heading', '__return_null' );
add_filter( 'woocommerce_product_reviews_heading', '__return_null' );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

/**
 * WooCommerce + qty button
 */
if (!function_exists('thecrate_woocommerce_display_quantity_plus')) {
    function thecrate_woocommerce_display_quantity_plus() {
        if (is_singular('product')) {
            $_product = wc_get_product( get_the_ID() );
            if( !$_product->is_type( 'bundle' ) ) {
                echo '<button type="button" class="plus" >+</button>';
            }
        }else{
            echo '<button type="button" class="plus" >+</button>';
        }
    }
    add_action( 'woocommerce_after_quantity_input_field', 'thecrate_woocommerce_display_quantity_plus' );
}

/**
 * WooCommerce - qty button
 */
if (!function_exists('thecrate_woocommerce_display_quantity_minus')) {
    function thecrate_woocommerce_display_quantity_minus() {
        if (is_singular('product')) {
            $_product = wc_get_product( get_the_ID() );
            if( !$_product->is_type( 'bundle' ) ) {
                echo '<button type="button" class="minus" >-</button>';
            }
        }else{
           echo '<button type="button" class="minus" >-</button>';
        }
    }
    add_action( 'woocommerce_after_quantity_input_field', 'thecrate_woocommerce_display_quantity_minus' );
}

/**
 * Header Cart svg icon
 */
if(!function_exists('thecrate_cart_svg')){
    function thecrate_cart_svg(){
    	$html = '<svg width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17 20H1C0.734784 20 0.48043 19.8946 0.292893 19.7071C0.105357 19.5196 0 19.2652 0 19V1C0 0.734784 0.105357 0.48043 0.292893 0.292893C0.48043 0.105357 0.734784 0 1 0H17C17.2652 0 17.5196 0.105357 17.7071 0.292893C17.8946 0.48043 18 0.734784 18 1V19C18 19.2652 17.8946 19.5196 17.7071 19.7071C17.5196 19.8946 17.2652 20 17 20ZM16 18V2H2V18H16ZM6 4V6C6 6.79565 6.31607 7.55871 6.87868 8.12132C7.44129 8.68393 8.20435 9 9 9C9.79565 9 10.5587 8.68393 11.1213 8.12132C11.6839 7.55871 12 6.79565 12 6V4H14V6C14 7.32608 13.4732 8.59785 12.5355 9.53553C11.5979 10.4732 10.3261 11 9 11C7.67392 11 6.40215 10.4732 5.46447 9.53553C4.52678 8.59785 4 7.32608 4 6V4H6Z" fill="#222222"/></svg>';

    	return $html;
    }
}

/**
 * Header Account svg icon
 */
if(!function_exists('thecrate_account_svg')){
    function thecrate_account_svg(){
    	$html = '<svg width="18" height="21" viewBox="0 0 18 21" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.99977 9.65383C9.90499 9.65383 10.7899 9.3854 11.5426 8.88248C12.2952 8.37956 12.8819 7.66475 13.2283 6.82843C13.5747 5.9921 13.6653 5.07184 13.4887 4.184C13.3121 3.29617 12.8762 2.48064 12.2361 1.84055C11.596 1.20046 10.7805 0.764547 9.89268 0.587946C9.00484 0.411345 8.08458 0.501983 7.24826 0.848399C6.41193 1.19481 5.69712 1.78145 5.1942 2.53412C4.69128 3.28679 4.42285 4.17169 4.42285 5.07692C4.42285 6.29079 4.90506 7.45495 5.7634 8.31328C6.62174 9.17162 7.78589 9.65383 8.99977 9.65383ZM8.99977 1.80769C9.64636 1.80769 10.2784 1.99943 10.8161 2.35866C11.3537 2.71788 11.7727 3.22847 12.0201 3.82584C12.2676 4.42321 12.3323 5.08054 12.2062 5.71471C12.08 6.34888 11.7687 6.9314 11.3115 7.38861C10.8542 7.84582 10.2717 8.15718 9.63756 8.28332C9.00339 8.40947 8.34606 8.34473 7.74869 8.09729C7.15131 7.84985 6.64073 7.43082 6.28151 6.8932C5.92228 6.35558 5.73054 5.72351 5.73054 5.07692C5.73054 4.20986 6.07498 3.37832 6.68808 2.76523C7.30117 2.15213 8.13271 1.80769 8.99977 1.80769Z" fill="black"/><path d="M17.1534 14.4726C16.1049 13.3644 14.8413 12.4816 13.4399 11.8783C12.0386 11.275 10.5289 10.9638 9.00325 10.9638C7.47756 10.9638 5.9679 11.275 4.56656 11.8783C3.16522 12.4816 1.90164 13.3644 0.853078 14.4726C0.625744 14.7154 0.499485 15.0358 0.500002 15.3684V18.8076C0.500002 19.1544 0.637776 19.487 0.883015 19.7323C1.12825 19.9775 1.46087 20.1153 1.80769 20.1153H16.1923C16.5391 20.1153 16.8717 19.9775 17.117 19.7323C17.3622 19.487 17.5 19.1544 17.5 18.8076V15.3684C17.5023 15.0367 17.3784 14.7164 17.1534 14.4726ZM16.1923 18.8076H1.80769V15.3618C2.73434 14.3861 3.84984 13.6091 5.0863 13.0782C6.32277 12.5472 7.65434 12.2734 8.99999 12.2734C10.3456 12.2734 11.6772 12.5472 12.9137 13.0782C14.1501 13.6091 15.2656 14.3861 16.1923 15.3618V18.8076Z" fill="#222222"/></svg>';

    	return $html;
    }
}


/**
 * WooCommerce - Ensure cart contents update when products are added to the cart via AJAX
 */
if (!function_exists('thecrate_woocommerce_header_add_to_cart_fragment')) {
    function thecrate_woocommerce_header_add_to_cart_fragment( $fragments ) {
        ob_start();
        ?>
        <a class="cart-contents" href="<?php echo wc_get_cart_url(); ?>"><?php echo thecrate_cart_svg(); ?><span><?php echo sprintf (_n( '%d', '%d', WC()->cart->get_cart_contents_count(), 'thecrate' ), WC()->cart->get_cart_contents_count() ); ?></span></a> 
        <?php
        
        $fragments['a.cart-contents'] = ob_get_clean();
        
        return $fragments;
    }
    add_filter( 'woocommerce_add_to_cart_fragments', 'thecrate_woocommerce_header_add_to_cart_fragment' );
}

/**
 * Change number of related products output
 */ 
if (!function_exists('thecrate_related_products_args')) {
	function thecrate_related_products_args( $args ) {

        if ( class_exists( 'WooCommerce' ) && class_exists( 'ReduxFrameworkPlugin' ) ) {
            $posts_per_page = 3;
            $columns = 3;
        }else{
            $posts_per_page = 4;
            $columns = 4;
        }

		$args['posts_per_page'] = $posts_per_page; // 4 related products
		$args['columns'] = $columns; // arranged in 4 columns
		return $args;
	}
}


if (!function_exists('thecrate_woocommerce_get_sidebar')) {
    function thecrate_woocommerce_get_sidebar() {
        global $thecrate_redux;

        if ( is_shop() || is_product_category() || is_product_tag() ) {
            if (is_active_sidebar($thecrate_redux['thecrate_shop_layout_sidebar'])) {
                dynamic_sidebar( $thecrate_redux['thecrate_shop_layout_sidebar'] );
            }else{
                if (is_active_sidebar('woocommerce')) {
                    dynamic_sidebar( 'woocommerce' );
                } 
            }
        }elseif ( is_product() ) {
            if (is_active_sidebar($thecrate_redux['thecrate_single_shop_sidebar'])) {
                dynamic_sidebar( $thecrate_redux['thecrate_single_shop_sidebar'] );
            }else{
                if (is_active_sidebar('woocommerce')) {
                    dynamic_sidebar( 'woocommerce' );
                }
            }
        }
    }
    add_action ( 'woocommerce_sidebar', 'thecrate_woocommerce_get_sidebar' );
}

/*
 * Return a new number of maximum columns for shop archives
 * @param int Original value
 * @return int New number of columns
 */
if (!function_exists('thecrate_wc_loop_shop_columns')) {
    add_filter( 'loop_shop_columns', 'thecrate_wc_loop_shop_columns', 1, 13 );
    function thecrate_wc_loop_shop_columns( $number_columns ) {
        global $thecrate_redux;

        if ( class_exists( 'WooCommerce' ) && class_exists( 'ReduxFrameworkPlugin' ) ) {
            $columns = 3;
        }else{
            $columns = 4;
        }

        if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
            if ( $thecrate_redux['thecrate-shop-columns'] ) {
                return $thecrate_redux['thecrate-shop-columns'];
            }else{
                return $columns;
            }
        }else{
            return $columns;
        }
    }
}


if (!function_exists('thecrate_new_loop_shop_per_page')) {
    add_filter( 'loop_shop_per_page', 'thecrate_new_loop_shop_per_page', 20 );
    function thecrate_new_loop_shop_per_page( $cols ) {
        global $thecrate_redux;

        if ( class_exists( 'WooCommerce' ) && class_exists( 'ReduxFrameworkPlugin' ) ) {
            if ( $thecrate_redux['thecrate-shop-columns'] ) {
                $posts_per_page = $thecrate_redux['thecrate-shop-products-per-page'];
            }else{
                $posts_per_page = 12;
            }
        }else{
            $posts_per_page = 12;
        }

        // Return the number of products you wanna show per page.
        $cols = $posts_per_page;
        return $cols;
    }
}


// Display a custom select field in "Linked Products" section
if (!function_exists('thecrate_single_product_meta_whats_included')) {
    add_action( 'woocommerce_product_meta_end', 'thecrate_single_product_meta_whats_included' );
    function thecrate_single_product_meta_whats_included() {
        $box_products = get_post_meta( get_the_ID(), '_thecrate_box_products', true);
        
        if ($box_products) { 
            $count = count($box_products); ?>
            <span class="tagged_as">
                <?php esc_html_e( 'Box Contents: ', 'thecrate' ); ?> 
                <a class="go-to-tab" href="#box_products">
                    <?php echo sprintf( _n( '%s Product', '%s Products', $count, 'thecrate' ), $count); ?>
                </a>
            </span>
            <?php 
        }
    }
}


// Display a custom select field in "Linked Products" section
if (!function_exists('thecrate_crate_included_products_custom_field')) {
    add_action( 'woocommerce_product_options_related', 'thecrate_crate_included_products_custom_field' );
    function thecrate_crate_included_products_custom_field() {
        global $product_object, $post;
        ?>
        <p class="form-field">
            <label for="thecrate_box_products"><?php _e( 'Box Included Products', 'thecrate' ); ?></label>
            <select class="wc-product-search" multiple="multiple" style="width: 50%;" id="thecrate_box_products" name="_thecrate_box_products[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'thecrate' ); ?>" data-action="woocommerce_json_search_products_and_variations" data-exclude="<?php echo intval( $post->ID ); ?>">
                <?php
                    $product_ids = $product_object->get_meta( '_thecrate_box_products' );

                    foreach ( $product_ids as $product_id ) {
                        $product = wc_get_product( $product_id );
                        if ( is_object( $product ) ) {
                            echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . esc_html( $product->get_formatted_name() ) . '</option>';
                        }
                    }
                ?>
            </select>
        </p>
        <?php
    }
}

/**
* @snippet       Save the values to the product
*/
if (!function_exists('thecrate_crate_included_products_save_custom_field')) {
    add_action( 'woocommerce_admin_process_product_object', 'thecrate_crate_included_products_save_custom_field', 10, 1 );
    function thecrate_crate_included_products_save_custom_field( $product ){
        
        $data = isset( $_POST['_thecrate_box_products'] ) ? array_map( 'intval', (array)$_POST['_thecrate_box_products'] ) : array();
        $product->update_meta_data( '_thecrate_box_products', $data );
    }
}



if (!function_exists('thecrate_box_included_products_tab')) {
    function thecrate_box_included_products_tab( $tabs ) {

        $box_products = get_post_meta( get_the_ID(), '_thecrate_box_products', true);
        if (isset($box_products) && !empty($box_products)) {
            $tabs['box_products'] = array(
                'title'    => __( 'What\'s in the Box', 'thecrate' ). ' ('.count($box_products).')',
                'callback' => 'thecrate_box_included_products_tab_content',
                'priority' => 50,
            );
        }

        return $tabs;

    }
    add_filter( 'woocommerce_product_tabs', 'thecrate_box_included_products_tab' );
}


/**
 * Function that displays output for the shipping tab.
 */
if (!function_exists('thecrate_box_included_products_tab_content')) {
    function thecrate_box_included_products_tab_content( $slug, $tab ) {
        $box_products = get_post_meta( get_the_ID(), '_thecrate_box_products', true);
        if ($box_products) {
            echo do_shortcode('[products limit="12" columns="4" ids="'.implode(",",$box_products).'" ]');
        }
    }
}


function thecrate_single_wishlist_compare_buttons(){
    if ( function_exists( 'woosc_init' ) ) {
        echo do_shortcode('[woosc id="'.esc_attr(get_the_ID()).'"]');
    }
    if ( function_exists( 'woosw_init' ) ) {
        echo do_shortcode('[woosw id="'.esc_attr(get_the_ID()).'"]');
    }
}
add_action('woocommerce_after_add_to_cart_button', 'thecrate_single_wishlist_compare_buttons');


// Function to handle the thumbnail request
function thecrate_get_the_post_thumbnail_src($img)
{
  return (preg_match('~\bsrc="([^"]++)"~', $img, $matches)) ? $matches[1] : '';
}

if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
    if (!function_exists('thecrate_social_share_buttons')) {
        function thecrate_social_share_buttons() {
            // Get current page URL 
            $url = esc_url(get_permalink());

            // Get current page title
            $title = str_replace( ' ', '%20', get_the_title());
            
            // Get Post Thumbnail for pinterest
            $thumb = thecrate_get_the_post_thumbnail_src(get_the_post_thumbnail());

            $thumb_url = '';
            if(!empty($thumb)) {
                $thumb_url = $thumb[0];
            }

            // Construct sharing URL without using any script
            $twitter_url = 'https://twitter.com/intent/tweet?text='.esc_html($title).'&amp;url='.esc_url($url).'&amp;via='.esc_attr(get_bloginfo( 'name' ));
            $facebook_url = 'https://www.facebook.com/sharer/sharer.php?u='.esc_url($url);
            $whatsapp_url = 'whatsapp://send?text='.esc_html($title) . ' ' . esc_url($url);
            $linkedin_url = 'https://www.linkedin.com/shareArticle?mini=true&url='.esc_url($url).'&amp;title='.esc_html($title);
            if(!empty($thumb)) {
                $pinterest_url = 'https://pinterest.com/pin/create/button/?url='.esc_url($url).'&amp;media='.esc_url($thumb_url).'&amp;description='.esc_html($title);
            }else {
                $pinterest_url = 'https://pinterest.com/pin/create/button/?url='.esc_url($url).'&amp;description='.esc_html($title);
            }
            // Based on popular demand added Pinterest too
            $pinterest_url = 'https://pinterest.com/pin/create/button/?url='.esc_url($url).'&amp;media='.esc_url($thumb_url).'&amp;description='.esc_html($title);
            $email_url = 'mailto:?subject='.esc_html($title).'&amp;body='.esc_url($url);

            $telegram_url = 'https://telegram.me/share/url?url=<'.esc_url($url).'>&text=<'.esc_html($title).'>';

            $social_shares = thecrate_redux('thecrate_social_share_links');
            $social_share_locations = thecrate_redux('thecrate_social_share_locations');

            if ((is_product() && $social_share_locations['product'] == 1) || (is_singular('post') && $social_share_locations['post'] == 1)) {
                if ($social_shares) {
                    // The Visual Buttons
                    echo '<div class="social-box"><div class="social-btn">';
                        if ($social_shares['twitter'] == 1) {
                            echo '<a data-toggle="tooltip" data-placement="top" title="'.esc_attr__('Share on Twitter', 'thecrate').'" class="col-2 sbtn s-twitter" href="'. esc_url($twitter_url) .'" target="_blank" rel="nofollow"><i class="fab fa-twitter"></i></a>';
                        }
                        if ($social_shares['facebook'] == 1) {
                            echo '<a data-toggle="tooltip" data-placement="top" title="'.esc_attr__('Share on Facebook', 'thecrate').'" class="col-2 sbtn s-facebook" href="'.esc_url($facebook_url).'" target="_blank" rel="nofollow"><i class="fab fa-facebook-f"></i></a>';
                        }
                        if ($social_shares['whatsapp'] == 1) {
                            echo '<a data-toggle="tooltip" data-placement="top" title="'.esc_attr__('Share on WhatsApp', 'thecrate').'" class="col-2 sbtn s-whatsapp" href="'.esc_url($whatsapp_url).'" target="_blank" rel="nofollow"><i class="fab fa-whatsapp"></i></a>';
                        }
                        // if ($social_shares['messenger'] == 1) {
                        //     echo '<a data-toggle="tooltip" data-placement="top" title="'.esc_attr__('Share on Messenger', 'thecrate').'" class="col-2 sbtn s-whatsapp" href="https://fb-messenger://share/?link='.esc_url($url).'&app_id=[ID]" target="_blank" rel="nofollow"><i class="fab fa-facebook-messenger"></i></a>';
                        // }
                        if ($social_shares['pinterest'] == 1) {
                            echo '<a data-toggle="tooltip" data-placement="top" title="'.esc_attr__('Share on Pinterest', 'thecrate').'" class="col-2 sbtn s-pinterest" href="'.esc_url($pinterest_url).'" data-pin-custom="true" target="_blank" rel="nofollow"><i class="fab fa-pinterest-p"></i></a>';
                        }
                        if ($social_shares['linkedin'] == 1) {
                            echo '<a data-toggle="tooltip" data-placement="top" title="'.esc_attr__('Share on LinkedIn', 'thecrate').'" class="col-2 sbtn s-linkedin" href="'.esc_url($linkedin_url).'" target="_blank" rel="nofollow"><i class="fab fa-linkedin-in"></i></a>';
                        }
                        if ($social_shares['telegram'] == 1) {
                            echo '<a data-toggle="tooltip" data-placement="top" title="'.esc_attr__('Share on Telegram', 'thecrate').'" class="col-2 sbtn s-telegram" href="'.esc_url($telegram_url).'" target="_blank" rel="nofollow"><i class="fab fa-telegram-plane"></i></a>';
                        }
                        if ($social_shares['email'] == 1) {
                            echo '<a data-toggle="tooltip" data-placement="top" title="'.esc_attr__('Email to a Friend', 'thecrate').'" class="col-2 sbtn s-email" href="'.esc_url($email_url).'" target="_blank" rel="nofollow"><i class="far fa-envelope"></i></a>';
                        }
                    echo '</div></div>';
                }
            }
        }
        // single product page
        add_action( 'woocommerce_product_meta_end', 'thecrate_social_share_buttons');
        // single post page
        add_action( 'thecrate_after_single_post_metas', 'thecrate_social_share_buttons');
    }
}