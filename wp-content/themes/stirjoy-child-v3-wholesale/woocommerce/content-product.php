<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

// Get product data
$product_id = $product->get_id();
$prep_time = get_post_meta( $product_id, '_prep_time', true );
$serving_size = get_post_meta( $product_id, '_serving_size', true );
$product_tags = get_the_terms( $product_id, 'product_tag' );
$average_rating = $product->get_average_rating();
$short_description = $product->get_short_description();

// Check if product is in cart
$in_cart = false;
foreach ( WC()->cart->get_cart() as $cart_item ) {
	if ( $cart_item['product_id'] == $product_id ) {
		$in_cart = true;
		break;
	}
}

// Get searchable text for filtering
$searchable_tags = '';
if ( ! empty( $product_tags ) && ! is_wp_error( $product_tags ) ) {
	$tag_names = array();
	foreach ( $product_tags as $tag ) {
		$tag_names[] = $tag->name;
	}
	$searchable_tags = implode( ' ', $tag_names );
}
$product_cats = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'names' ) );
$searchable_cats = is_array( $product_cats ) ? implode( ' ', $product_cats ) : '';
$searchable_text = strtolower( $product->get_name() . ' ' . $short_description . ' ' . $searchable_tags . ' ' . $searchable_cats );

?>
<li <?php wc_product_class( 'meal-product-card', $product ); ?> data-product-id="<?php echo esc_attr( $product_id ); ?>" data-search-text="<?php echo esc_attr( $searchable_text ); ?>" data-in-cart="<?php echo $in_cart ? '1' : '0'; ?>">
	
	<div class="meal-card-inner">
		
		<!-- Product Image with Tags -->
		<div class="meal-image-wrapper">
			<?php
			// Product Tags/Badges
			if ( ! empty( $product_tags ) && ! is_wp_error( $product_tags ) ) {
				echo '<div class="meal-tags">';
				foreach ( $product_tags as $tag ) {
					$tag_slug = $tag->slug;
					$tag_class = 'meal-tag';
					
					// Add specific classes for styling
					if ( in_array( $tag_slug, array( 'popular', 'quick', 'high-protein', 'seasonal', 'new' ) ) ) {
						$tag_class .= ' tag-' . $tag_slug;
					}
					
					echo '<span class="' . esc_attr( $tag_class ) . '">' . esc_html( $tag->name ) . '</span>';
				}
				echo '</div>';
			}
			
			// Product Image
			echo '<a href="' . esc_url( $product->get_permalink() ) . '" class="meal-image-link">';
			if ( $product->get_image_id() ) {
				echo wp_get_attachment_image( $product->get_image_id(), 'medium', false, array( 'class' => 'meal-image' ) );
			} else {
				// Placeholder with leaf icon
				echo '<div class="meal-image-placeholder">';
				echo '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="leaf-icon">';
				echo '<path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"></path>';
				echo '<path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"></path>';
				echo '</svg>';
				echo '</div>';
			}
			echo '</a>';
			?>
		</div>

		<!-- Product Info -->
		<div class="meal-info">
			
			<!-- Product Name -->
			<h3 class="meal-name">
				<?php echo esc_html( $product->get_name() ); ?>
			</h3>

			<!-- Product Description -->
			<?php if ( $short_description ) : ?>
				<p class="meal-description"><?php echo wp_trim_words( $short_description, 6, '' ); ?></p>
			<?php endif; ?>

			<!-- Metadata Row -->
			<div class="meal-meta-row">
				<?php if ( $prep_time ) : ?>
					<div class="meta-item prep-time">
						<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="clock-icon">
							<circle cx="12" cy="12" r="10"></circle>
							<polyline points="12 6 12 12 16 14"></polyline>
						</svg>
						<span><?php echo esc_html( $prep_time ); ?> min</span>
					</div>
				<?php endif; ?>

				<?php if ( $serving_size ) : ?>
					<div class="meta-item serving-size">
						<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
							<circle cx="9" cy="7" r="4"></circle>
							<path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
							<path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
						</svg>
						<span><?php echo esc_html( $serving_size ); ?></span>
					</div>
				<?php endif; ?>

				<?php if ( $average_rating > 0 ) : ?>
					<div class="meta-item rating">
						<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="none">
							<path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
						</svg>
						<span><?php echo esc_html( number_format( $average_rating, 1 ) ); ?></span>
					</div>
				<?php endif; ?>
			</div>

			<!-- Price -->
			<div class="meal-price">
				<?php echo $product->get_price_html(); ?>
			</div>

			<!-- Action Buttons -->
			<div class="meal-actions">
				<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="view-details-btn">
					View Details
				</a>
				
				<?php if ( $in_cart ) : ?>
					<button type="button" class="remove-from-cart-btn" data-product-id="<?php echo esc_attr( $product_id ); ?>">- Remove</button>
				<?php else : ?>
					<button type="button" class="add-to-cart-btn" data-product-id="<?php echo esc_attr( $product_id ); ?>">+ Add</button>
				<?php endif; ?>
			</div>

		</div><!-- .meal-info -->

	</div><!-- .meal-card-inner -->

</li>
