
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

	<!-- Product Detail Modal - Figma Design -->
	<div id="product-detail-modal" class="product-detail-modal">
		<div class="modal-overlay"></div>
		<div class="modal-content-wrapper">
			<div class="modal-content">
				<!-- Close Button (Top Right) -->
				<button type="button" class="modal-close" aria-label="Close modal">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M18 6 6 18"></path>
						<path d="m6 6 12 12"></path>
					</svg>
				</button>
				
				<!-- Top Section: Orange Background -->
				<div class="modal-top-section">
					<div class="modal-meal-image">
						<img src="" alt="" id="modal-product-img" />
					</div>
				</div>
				
				<!-- Bottom Section: Cream/Yellow Background -->
				<div class="modal-bottom-section">
					<!-- Three Circular Nutritional Icons -->
					<img src="<?php echo esc_url(stirjoy_get_image_url('pastilles.png')); ?>" alt="Bowl">
					
					<!-- Product Title -->
					<h2 class="modal-product-title" id="modal-product-title"></h2>
					
					<!-- Serving Info with Person Icon -->
					<div class="modal-serving-info">
						<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="person-icon">
							<circle cx="7" cy="4.5" r="2.5"/>
							<path d="M2 12.5c0-2.2 2.2-4 5-4s5 1.8 5 4"/>
						</svg>
						<span class="serving-text" id="modal-serving-text">2 people</span>
						<span class="price-per-portion" id="modal-price-per-portion">(6$/portion)</span>
					</div>
					
					<!-- Description -->
					<p class="modal-description" id="modal-description"></p>
					
					<!-- Quantity Selector and Add to Cart -->
					<div class="modal-actions">
						<div class="modal-quantity-selector">
							<button type="button" class="modal-quantity-btn modal-quantity-minus" id="modal-quantity-minus">-</button>
							<span class="modal-quantity-value" id="modal-quantity-value">1</span>
							<button type="button" class="modal-quantity-btn modal-quantity-plus" id="modal-quantity-plus">+</button>
						</div>
						<button type="button" class="modal-add-to-cart-btn" id="modal-action-btn" data-product-id="">
							ADD TO CART
						</button>
					</div>
					
					<!-- Expandable Sections -->
					<div class="modal-expandable-sections">
						<!-- Cooking Instructions -->
						<div class="modal-expandable-section" id="modal-instructions-section">
							<button type="button" class="modal-expandable-header">
								<span class="expandable-title">Cooking instructions</span>
								<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="expandable-icon">
									<path d="M12 5v14"></path>
									<path d="M5 12h14"></path>
								</svg>
							</button>
							<div class="modal-expandable-content">
								<ol class="instructions-list" id="modal-instructions-list"></ol>
							</div>
						</div>
						
						<!-- Nutritional Info -->
						<div class="modal-expandable-section" id="modal-nutrition-section">
							<button type="button" class="modal-expandable-header">
								<span class="expandable-title">Nutritional info</span>
								<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="expandable-icon">
									<path d="M12 5v14"></path>
									<path d="M5 12h14"></path>
								</svg>
							</button>
							<div class="modal-expandable-content">
								<div class="modal-nutrition-details">
									<div class="nutrition-detail-item" id="modal-nutrition-protein-detail">
										<span class="nutrition-detail-label">Protein</span>
										<span class="nutrition-detail-value" id="modal-protein-detail-value">20g</span>
									</div>
									<div class="nutrition-detail-item" id="modal-nutrition-carbs-detail">
										<span class="nutrition-detail-label">Carbs</span>
										<span class="nutrition-detail-value" id="modal-carbs-detail-value">0g</span>
									</div>
									<div class="nutrition-detail-item" id="modal-nutrition-fat-detail">
										<span class="nutrition-detail-label">Fat</span>
										<span class="nutrition-detail-value" id="modal-fat-detail-value">0g</span>
									</div>
									<div class="nutrition-detail-item" id="modal-nutrition-calories-detail">
										<span class="nutrition-detail-label">Calories</span>
										<span class="nutrition-detail-value" id="modal-calories-detail-value">0</span>
									</div>
								</div>
							</div>
						</div>
						
						<!-- Ingredients and Allergens -->
						<div class="modal-expandable-section" id="modal-ingredients-section">
							<button type="button" class="modal-expandable-header">
								<span class="expandable-title">Ingredients and allergens</span>
								<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="expandable-icon">
									<path d="M12 5v14"></path>
									<path d="M5 12h14"></path>
								</svg>
							</button>
							<div class="modal-expandable-content">
								<div class="modal-ingredients-list" id="modal-ingredients-list"></div>
								<div class="modal-allergens-list" id="modal-allergens-list"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

</div><!-- .customize-your-box-page -->
<!-- FAQs Section -->
<section class="stirjoy-faqs" id="faqs">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h2 class="section-title-white">FAQs</h2>
            </div>
        </div>
        <div class="row">
            <div class="faq-wrapper">
                <div class="faq-illustration">
                    <img src="<?php echo esc_url(stirjoy_get_image_url('77f8e297e41413ad7d4480ebc4973808e9612955.png')); ?>" alt="FAQ">
                </div>
                <div class="faq-accordion">
                    <div class="faq-item">
                        <div class="faq-question">Are dehydrated meals nutritious?</div>
                        <div class="faq-answer">Usually not right? Well that's why we launched Stirjoy. We work with chefs and nutritionists to make sure all our meals contain loads of plant-based protein, vegetables, and grains while remaining conscious of sodium and sugar. Dehydration is simply the removal of water, and so most of the nutritional value of ingredients is preserved.</div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">Why are Stirjoy meal kits so much cheaper than traditional meal kits?</div>
                        <div class="faq-answer">Our dehydrated format reduces shipping costs and extends shelf life, allowing us to offer better prices without compromising on quality or nutrition.</div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">How does the Stirjoy subscription work?</div>
                        <div class="faq-answer">Choose your meals monthly, and we'll deliver them to your door. You can customize, skip, or cancel anytime with full flexibility.</div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">Can I reschedule or skip a month?</div>
                        <div class="faq-answer">Absolutely! You have full control over your subscription and can skip or reschedule deliveries anytime through your account dashboard.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Social Media Section -->
<section class="stirjoy-social">
    <div class="row">
        <div class="col-12 text-center">
            <h2 class="section-title-social">Looks good!</h2>
            <div class="social-posts-wrapper">
                <div class="social-posts" id="social-posts-container">
                    <img class="social-media-image" src="<?php echo esc_url(stirjoy_get_image_url('3cc37f4fecfa9aebb97332df4da1fa5c620bcb24.png')); ?>" alt="Social Media">
                </div>
            </div>
            <p class="social-follow">Follow along @stirjoy.ca</p>
        </div>
    </div>
</section>

<?php
get_footer( 'shop' );

