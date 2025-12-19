
<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

?>
<div class="customize-your-box-page">
	
	<!-- Your Box Header Bar -->
	<div class="your-box-header">
		<div class="container">
			<div class="your-box-info">
				<div class="your-box-left-section">
					<svg class="cart-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="9" cy="21" r="1"></circle>
						<circle cx="20" cy="21" r="1"></circle>
						<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
					</svg>
					<span class="your-box-text">Your Box</span>
					<span class="your-box-count"><?php 
						// Use accurate cart count function
						if ( function_exists( 'stirjoy_get_accurate_cart_count' ) ) {
							echo esc_html( stirjoy_get_accurate_cart_count() );
						} else {
							// Fallback to WooCommerce function
							WC()->cart->calculate_totals();
							echo esc_html( WC()->cart->get_cart_contents_count() );
						}
					?></span>
				</div>
				
				<div class="your-box-right-section">
					<?php
					$subtotal = WC()->cart->get_subtotal();
					$free_shipping_threshold = 80;
					$free_gift_threshold = 120;
					
					$shipping_remaining = max(0, $free_shipping_threshold - $subtotal);
					$shipping_progress = min(100, ($subtotal / $free_shipping_threshold) * 100);
					
					$gift_remaining = max(0, $free_gift_threshold - $subtotal);
					$gift_progress = min(100, ($subtotal / $free_gift_threshold) * 100);
					?>
					
					<!-- Free Shipping Progress -->
					<div class="cart-bar-progress-item">
						<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="truck-icon">
							<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path>
							<path d="M15 18H9"></path>
							<path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"></path>
							<circle cx="17" cy="18" r="2"></circle>
							<circle cx="7" cy="18" r="2"></circle>
						</svg>
						<div class="progress-bar-wrapper">
							<div class="progress-bar">
								<div class="progress-fill shipping-progress" style="width: <?php echo esc_attr($shipping_progress); ?>%"></div>
							</div>
						</div>
					</div>
					
					<!-- Free Gift Progress -->
					<div class="cart-bar-progress-item">
						<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gift-icon">
							<rect x="3" y="8" width="18" height="4" rx="1"></rect>
							<path d="M12 8v13"></path>
							<path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"></path>
							<path d="M7.5 8a2.5 2.5 0 0 1 0-5A4.8 8 0 0 1 12 8a4.8 8 0 0 1 4.5-5 2.5 2.5 0 0 1 0 5"></path>
						</svg>
						<div class="progress-bar-wrapper">
							<div class="progress-bar">
								<div class="progress-fill gift-progress" style="width: <?php echo esc_attr($gift_progress); ?>%"></div>
							</div>
						</div>
					</div>
					
					<span class="your-box-total"><?php echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></span>
					
					<!-- Toggle Cart Button -->
					<button type="button" class="toggle-cart-sidebar" aria-label="Toggle cart">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="chevron-down">
							<polyline points="6 9 12 15 18 9"></polyline>
						</svg>
					</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Cart Sidebar - Rendered right after cart bar -->
	<?php get_template_part( 'woocommerce/cart-sidebar' ); ?>

	<!-- Main Content -->
	<div class="container customize-box-container">
		
		<header class="woocommerce-products-header">
			<h1 class="woocommerce-products-header__title page-title">Our menu</h1>
			<p class="customize-box-subtitle">It’s simple: every month, we prepare a new menu of meal kits that you can customize to your liking online.</p>
		</header>

		<!-- Search Bar -->
		<div class="meal-search-wrapper">
			<form role="search" method="get" class="meal-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input type="search" 
					   id="meal-search" 
					   class="meal-search-field" 
					   placeholder="Search meals..." 
					   value="<?php echo get_search_query(); ?>" 
					   name="s"
					   autocomplete="off" />
				<input type="hidden" name="post_type" value="product" />
				<button type="submit" class="meal-search-submit">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="11" cy="11" r="8"></circle>
						<path d="m21 21-4.35-4.35"></path>
					</svg>
				</button>
			</form>
		</div>

		<?php
		if ( woocommerce_product_loop() ) {

			// Query all meal products (exclude subscriptions, coffee, etc.)
			$args = array(
				'post_type' => 'product',
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'orderby' => 'title',
				'order' => 'ASC',
				'tax_query' => array(
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'slug',
						'terms'    => array('mains', 'breakfast', 'snacks-desserts', 'desserts'),
						'operator' => 'IN',
					),
				),
			);

			$meal_query = new WP_Query( $args );

			// Collect all products
			$all_products = array();
			if ( $meal_query->have_posts() ) {
				while ( $meal_query->have_posts() ) {
					$meal_query->the_post();
					global $product;
					$all_products[] = $product;
				}
				wp_reset_postdata();
			}

			// Sort products alphabetically by name
			usort( $all_products, function( $a, $b ) {
				return strcasecmp( $a->get_name(), $b->get_name() );
			} );

			// Display all products in a single list (alphabetically sorted)
			if ( ! empty( $all_products ) ) {
				?>
				<ul class="products">
					<?php
					foreach ( $all_products as $product ) {
						$GLOBALS['product'] = $product;
						wc_get_template_part( 'content', 'product' );
					}
					?>
				</ul>
				<?php
			}

		} else {
			/**
			 * Hook: woocommerce_no_products_found.
			 */
			do_action( 'woocommerce_no_products_found' );
		}

		?>

	</div><!-- .container -->

	<!-- Product Detail Modal -->
	<div id="product-detail-modal" class="product-detail-modal">
		<div class="modal-overlay"></div>
		<div class="modal-content-wrapper">
			<div class="modal-content">
				<button type="button" class="modal-close" aria-label="Close modal">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M18 6 6 18"></path>
						<path d="m6 6 12 12"></path>
					</svg>
				</button>
				
				<div class="modal-body">
					<!-- Product Image -->
					<div class="modal-product-image">
						<img src="" alt="" id="modal-product-img" />
					</div>
					
					<!-- Product Info -->
					<div class="modal-product-info">
						<!-- Title and Rating -->
						<div class="modal-header">
							<h2 class="modal-product-title" id="modal-product-title"></h2>
							<div class="modal-rating" id="modal-rating">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="none">
									<path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
								</svg>
								<span id="modal-rating-value"></span>
							</div>
						</div>
						
						<!-- Description -->
						<p class="modal-description" id="modal-description"></p>
						
						<!-- Key Metrics -->
						<div class="modal-metrics">
							<div class="metric-item" id="modal-prep-time">
								<span class="metric-value"></span>
								<span class="metric-label">Prep</span>
							</div>
							<div class="metric-item" id="modal-cook-time">
								<span class="metric-value"></span>
								<span class="metric-label">Cook</span>
							</div>
							<div class="metric-item" id="modal-serving-size">
								<span class="metric-value"></span>
								<span class="metric-label">Servings</span>
							</div>
							<div class="metric-item" id="modal-calories">
								<span class="metric-value"></span>
								<span class="metric-label">cal</span>
							</div>
						</div>
						
						<!-- Nutrition Facts -->
						<div class="modal-nutrition">
							<h3>Nutrition Facts</h3>
							<div class="nutrition-items">
								<div class="nutrition-item" id="modal-protein">
									<span class="nutrition-value"></span>
									<span class="nutrition-label">protein</span>
								</div>
								<div class="nutrition-item" id="modal-carbs">
									<span class="nutrition-value"></span>
									<span class="nutrition-label">carbs</span>
								</div>
								<div class="nutrition-item" id="modal-fat">
									<span class="nutrition-value"></span>
									<span class="nutrition-label">fat</span>
								</div>
							</div>
						</div>
						
						<!-- Ingredients -->
						<div class="modal-ingredients" id="modal-ingredients-section">
							<h3>Ingredients</h3>
							<div class="ingredients-list" id="modal-ingredients-list"></div>
						</div>
						
						<!-- Allergens -->
						<div class="modal-allergens" id="modal-allergens-section">
							<h3>Allergens</h3>
							<div class="allergens-list" id="modal-allergens-list"></div>
						</div>
						
						<!-- Instructions -->
						<div class="modal-instructions" id="modal-instructions-section">
							<h3>Instructions</h3>
							<ol class="instructions-list" id="modal-instructions-list"></ol>
						</div>
						
						<!-- Price and Action Button -->
						<div class="modal-footer">
							<div class="modal-price" id="modal-price"></div>
							<button type="button" class="modal-action-btn" id="modal-action-btn" data-product-id=""></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

</div><!-- .customize-your-box-page -->

<?php
get_footer( 'shop' );

