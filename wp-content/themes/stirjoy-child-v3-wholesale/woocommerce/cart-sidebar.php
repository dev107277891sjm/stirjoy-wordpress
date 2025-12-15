<?php
/**
 * Cart Sidebar Template
 * 
 * This template renders the cart sidebar for the shop page.
 * It's displayed when the user clicks the collapse button on the cart bar.
 * 
 * @package WooCommerce\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Only render on shop pages
if ( ! ( is_shop() || is_product_category() || is_product_tag() ) ) {
	return;
}
?>

<!-- Fixed Sidebar Menu - Cart -->
<?php if ( class_exists( 'WooCommerce' ) ) { ?>
	<div class="relative fixed-sidebar-menu-holder header7">
		<div class="fixed-sidebar-menu fixed-sidebar-menu-minicart">
			<!-- Close Cart Button (styled like cart bar toggle) -->
			<button type="button" class="cart-box-close-button" aria-label="Close cart">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<line x1="18" y1="6" x2="6" y2="18"></line>
					<line x1="6" y1="6" x2="18" y2="18"></line>
				</svg>
			</button>
			<!-- Sidebar Menu Holder -->
			<div class="header7">
				<!-- RIGHT SIDE -->
				<div class="left-side container">
					<div class="header_mini_cart">
						<?php the_widget( 'WC_Widget_Cart', array( 'title' => esc_html__('Your Box', 'thecrate') ) ); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php } ?>

