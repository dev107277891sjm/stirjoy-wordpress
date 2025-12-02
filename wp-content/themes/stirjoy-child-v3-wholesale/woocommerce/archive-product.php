
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

/**
 * Hook: woocommerce_before_main_content.
 */
do_action( 'woocommerce_before_main_content' );

?>
<div class="customize-your-box-page">
	
	<!-- Your Box Header Bar -->
	<div class="your-box-header">
		<div class="container">
			<div class="your-box-info">
				<svg class="cart-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<circle cx="9" cy="21" r="1"></circle>
					<circle cx="20" cy="21" r="1"></circle>
					<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
				</svg>
				<span class="your-box-text">Your Box</span>
				<span class="your-box-count">(<?php echo WC()->cart->get_cart_contents_count(); ?>)</span>
				<span class="your-box-total"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
			</div>
		</div>
	</div>

	<!-- Main Content -->
	<div class="container customize-box-container">
		
		<header class="woocommerce-products-header">
			<h1 class="woocommerce-products-header__title page-title">Customize Your Box</h1>
			<p class="customize-box-subtitle">Your box is pre-filled with this month's selection. Swap meals to customize!</p>
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

		<!-- Category Tabs -->
		<div class="meal-category-tabs">
			<button class="category-tab active" data-category="all">All</button>
			<button class="category-tab" data-category="breakfast">Breakfast</button>
			<button class="category-tab" data-category="mains">Mains</button>
			<button class="category-tab" data-category="snacks">Snacks & Desserts</button>
			<button class="category-tab" data-category="desserts">Desserts</button>
		</div>

		<?php
		if ( woocommerce_product_loop() ) {

			// Get all product categories to organize products
			$categories = array(
				'mains' => array(
					'title' => 'Mains',
					'slug' => 'mains',
					'products' => array()
				),
				'breakfast' => array(
					'title' => 'Breakfast',
					'slug' => 'breakfast',
					'products' => array()
				),
				'snacks' => array(
					'title' => 'Snacks',
					'slug' => 'snacks-desserts',
					'products' => array()
				),
				'desserts' => array(
					'title' => 'Desserts',
					'slug' => 'desserts',
					'products' => array()
				)
			);

			// Query all meal products (exclude subscriptions, coffee, etc.)
			$args = array(
				'post_type' => 'product',
				'posts_per_page' => -1,
				'post_status' => 'publish',
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

			// Organize products by category
			if ( $meal_query->have_posts() ) {
				while ( $meal_query->have_posts() ) {
					$meal_query->the_post();
					global $product;
					
					// Get product categories
					$product_cats = wp_get_post_terms( get_the_ID(), 'product_cat', array( 'fields' => 'slugs' ) );
					
					// Add to appropriate category
					foreach ( $categories as $key => $cat_data ) {
						if ( in_array( $cat_data['slug'], $product_cats ) ) {
							$categories[$key]['products'][] = $product;
							break; // Add to first matching category only
						}
					}
				}
				wp_reset_postdata();
			}

			// Display products by category
			foreach ( $categories as $key => $cat_data ) {
				if ( ! empty( $cat_data['products'] ) ) {
					?>
					<div class="meal-category-section" data-category="<?php echo esc_attr( $key ); ?>">
						<h2 class="category-heading"><?php echo esc_html( $cat_data['title'] ); ?></h2>
						<ul class="products columns-3">
							<?php
							foreach ( $cat_data['products'] as $product ) {
								$GLOBALS['product'] = $product;
								wc_get_template_part( 'content', 'product' );
							}
							?>
						</ul>
					</div>
					<?php
				}
			}

		} else {
			/**
			 * Hook: woocommerce_no_products_found.
			 */
			do_action( 'woocommerce_no_products_found' );
		}

		?>

	</div><!-- .container -->

</div><!-- .customize-your-box-page -->

<?php
/**
 * Hook: woocommerce_after_main_content.
 */
do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );

