<?php
/**
 * Product Meta Fields (Prep Time, Cook Time, Calories, Protein)
 *
 * @package Stirjoy_Child
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add custom product fields to admin
 */
function stirjoy_add_product_meta_fields() {
    global $post;
    
    echo '<div class="options_group">';
    
    woocommerce_wp_text_input( array(
        'id' => '_prep_time',
        'label' => __( 'Prep Time (min)', 'stirjoy-child' ),
        'placeholder' => '15',
        'type' => 'number',
        'custom_attributes' => array(
            'step' => '1',
            'min' => '0',
        ),
    ));
    
    woocommerce_wp_text_input( array(
        'id' => '_cook_time',
        'label' => __( 'Cook Time (min)', 'stirjoy-child' ),
        'placeholder' => '20',
        'type' => 'number',
        'custom_attributes' => array(
            'step' => '1',
            'min' => '0',
        ),
    ));
    
    woocommerce_wp_text_input( array(
        'id' => '_calories',
        'label' => __( 'Calories', 'stirjoy-child' ),
        'placeholder' => '450',
        'type' => 'number',
        'custom_attributes' => array(
            'step' => '1',
            'min' => '0',
        ),
    ));
    
    woocommerce_wp_text_input( array(
        'id' => '_protein',
        'label' => __( 'Protein (g)', 'stirjoy-child' ),
        'placeholder' => '35',
        'type' => 'text',
    ));
    
    woocommerce_wp_text_input( array(
        'id' => '_carbs',
        'label' => __( 'Carbs (g)', 'stirjoy-child' ),
        'placeholder' => '45',
        'type' => 'text',
    ));
    
    woocommerce_wp_text_input( array(
        'id' => '_fat',
        'label' => __( 'Fat (g)', 'stirjoy-child' ),
        'placeholder' => '18',
        'type' => 'text',
    ));
    
    woocommerce_wp_text_input( array(
        'id' => '_serving_size',
        'label' => __( 'Serving Size', 'stirjoy-child' ),
        'placeholder' => '2',
        'type' => 'number',
        'custom_attributes' => array(
            'step' => '1',
            'min' => '1',
        ),
        'desc_tip' => true,
        'description' => __( 'Number of servings per meal', 'stirjoy-child' ),
    ));
    
    woocommerce_wp_textarea_input( array(
        'id' => '_ingredients',
        'label' => __( 'Ingredients', 'stirjoy-child' ),
        'placeholder' => 'Quinoa, Bell peppers, Zucchini, Cherry tomatoes, Tahini...',
        'description' => __( 'Enter ingredients separated by commas', 'stirjoy-child' ),
        'rows' => 3,
    ));
    
    woocommerce_wp_textarea_input( array(
        'id' => '_allergens',
        'label' => __( 'Allergens', 'stirjoy-child' ),
        'placeholder' => 'Sesame',
        'description' => __( 'Enter allergens separated by commas', 'stirjoy-child' ),
        'rows' => 2,
    ));
    
    woocommerce_wp_textarea_input( array(
        'id' => '_instructions',
        'label' => __( 'Cooking Instructions', 'stirjoy-child' ),
        'placeholder' => '1. Cook quinoa according to package directions.\n2. Roast vegetables at 400°F for 20 minutes.\n3. Prepare tahini dressing.\n4. Combine and serve.',
        'description' => __( 'Enter cooking instructions, one per line', 'stirjoy-child' ),
        'rows' => 5,
    ));
    
    echo '</div>';
}
add_action( 'woocommerce_product_options_general_product_data', 'stirjoy_add_product_meta_fields' );

/**
 * Save custom product fields
 */
function stirjoy_save_product_meta_fields( $post_id ) {
    $text_fields = array( '_prep_time', '_cook_time', '_calories', '_protein', '_carbs', '_fat', '_serving_size' );
    $textarea_fields = array( '_ingredients', '_allergens', '_instructions' );
    
    foreach ( $text_fields as $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            update_post_meta( $post_id, $field, sanitize_text_field( $_POST[ $field ] ) );
        }
    }
    
    foreach ( $textarea_fields as $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            update_post_meta( $post_id, $field, sanitize_textarea_field( $_POST[ $field ] ) );
        }
    }
}
add_action( 'woocommerce_process_product_meta', 'stirjoy_save_product_meta_fields' );

/**
 * Display product meta on product cards (only for non-customize pages)
 */
function stirjoy_display_product_meta() {
    // Skip on customize your box page (our custom template handles everything)
    if ( is_shop() && isset( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], 'shop' ) !== false ) {
        return;
    }
    
    global $product;
    
    $prep_time = get_post_meta( $product->get_id(), '_prep_time', true );
    $cook_time = get_post_meta( $product->get_id(), '_cook_time', true );
    $calories = get_post_meta( $product->get_id(), '_calories', true );
    $protein = get_post_meta( $product->get_id(), '_protein', true );
    
    if ( $prep_time || $cook_time || $calories || $protein ) {
        echo '<div class="product-meta-info">';
        
        if ( $prep_time ) {
            echo '<span class="product-meta-item">';
            echo '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>';
            echo esc_html( $prep_time ) . ' min prep';
            echo '</span>';
        }
        
        /*if ( $cook_time ) {
            echo '<span class="product-meta-item">';
            echo '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/></svg>';
            echo esc_html( $cook_time ) . ' min cook';
            echo '</span>';
        }
        
        if ( $calories ) {
            echo '<span class="product-meta-item">';
            echo esc_html( $calories ) . ' cal';
            echo '</span>';
        }
        
        if ( $protein ) {
            echo '<span class="product-meta-item">';
            echo esc_html( $protein ) . ' protein';
            echo '</span>';
        }*/

        $tagHtml = '';
        $product_tags = get_the_terms( $product->get_id(), 'product_tag' );
        if ( !empty( $product_tags ) && !is_wp_error( $product_tags ) ) {
            foreach ( $product_tags as $tag ) {
                $tagHtml = '<span class="tag">' . $tag->name . '</span>';
            }
        }
        echo $tagHtml;
        
        echo '</div>';
    }
}
add_action( 'woocommerce_after_shop_loop_item', 'stirjoy_display_product_meta', 7 );

