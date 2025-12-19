<?php
/**
 * The template for displaying product content within loops
 * Updated to match Figma design exactly
 *
 * @package WooCommerce\Templates
 * @version Custom
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

// Check if product is in cart and get quantity
$in_cart = false;
$cart_quantity = 0;
foreach ( WC()->cart->get_cart() as $cart_item ) {
	if ( $cart_item['product_id'] == $product_id ) {
		$in_cart = true;
		$cart_quantity = isset( $cart_item['quantity'] ) ? intval( $cart_item['quantity'] ) : 1;
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

// Check for "High in protein" tag
$has_high_protein = false;
if ( ! empty( $product_tags ) && ! is_wp_error( $product_tags ) ) {
	foreach ( $product_tags as $tag ) {
		if ( stripos( $tag->name, 'protein' ) !== false || $tag->slug === 'high-protein' ) {
			$has_high_protein = true;
			break;
		}
	}
}

// Calculate price per portion
$price = $product->get_price();
$serving_count = $serving_size ? intval( preg_replace( '/[^0-9]/', '', $serving_size ) ) : 2;
$price_per_portion = $serving_count > 0 ? $price / $serving_count : $price;

?>
<li <?php wc_product_class( 'meal-product-card', $product ); ?> data-product-id="<?php echo esc_attr( $product_id ); ?>" data-search-text="<?php echo esc_attr( $searchable_text ); ?>" data-in-cart="<?php echo $in_cart ? '1' : '0'; ?>">
	
	<div class="meal-card-inner">
		
		<!-- Product Image with "High in protein" Label -->
		<div class="meal-image-wrapper">
			<?php
			// "High in protein" label (top right of image)
			if ( $has_high_protein ) {
				echo '<div class="meal-protein-label">High in protein</div>';
			}
			
			// Product Image
			if ( $product->get_image_id() ) {
				echo wp_get_attachment_image( $product->get_image_id(), 'medium', false, array( 'class' => 'meal-image' ) );
			} else {
				// Placeholder
				echo '<div class="meal-image-placeholder"></div>';
			}
			?>
		</div>

		<!-- Product Info -->
		<div class="meal-info">
			
			<!-- Product Name -->
			<h3 class="meal-name">
				<?php echo esc_html( $product->get_name() ); ?>
			</h3>

			<!-- Price and Serving Info: "2 people (6$/portion)" -->
			<div class="meal-price-serving">
				<span class="serving-info-wrapper"> 
					<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="person-icon">
						<circle cx="7" cy="4.5" r="2.5"/>
						<path d="M2 12.5c0-2.2 2.2-4 5-4s5 1.8 5 4"/>
					</svg>
					<?php if ( $serving_size ) : ?>
						<span class="serving-info"><?php echo esc_html( $serving_size ); ?></span>
					<?php else : ?>
						<span class="serving-info">2 people</span>
					<?php endif; ?>
					<span class="price-per-portion">(<?php echo wc_price( $price_per_portion ); ?>/portion)</span>
				</span>
				<!-- Preparation Time: "25 min" -->
				<?php if ( $prep_time ) : ?>
					<span class="meal-prep-time">
						<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="clock-icon">
							<circle cx="7" cy="7" r="5.5"/>
							<line x1="7" y1="7" x2="7" y2="4" stroke-width="1.2"/>
							<line x1="7" y1="7" x2="9.5" y2="7" stroke-width="1.2"/>
						</svg>
						<?php echo esc_html( $prep_time ); ?> min
					</span>
				<?php endif; ?>
			</div>

			

			<!-- Quantity Selector and View Details Button -->
			<div class="meal-actions-row">
				<!-- Quantity Selector -->
				<div class="meal-quantity-selector">
					<button type="button" class="quantity-btn quantity-minus" data-product-id="<?php echo esc_attr( $product_id ); ?>" <?php echo ( $cart_quantity <= 1 ) ? 'disabled' : ''; ?>>-</button>
					<span class="quantity-value"><?php echo esc_html( $cart_quantity > 0 ? $cart_quantity : 1 ); ?></span>
					<button type="button" class="quantity-btn quantity-plus" data-product-id="<?php echo esc_attr( $product_id ); ?>">+</button>
				</div>
				
				<!-- View Details Button with Eye Icon -->
				<button type="button" class="view-details-btn" data-product-id="<?php echo esc_attr( $product_id ); ?>">
				<img src="<?php echo esc_url(stirjoy_get_image_url('Frame 244.png')); ?>" alt="Bowl">
					View details
				</button>
			</div>

		</div><!-- .meal-info -->

	</div><!-- .meal-card-inner -->

</li>
