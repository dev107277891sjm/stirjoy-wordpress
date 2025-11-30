<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     8.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header( 'shop' ); ?>


<?php 
    /**
     * thecrate_before_primary_area hook.
     *
     * @hooked thecrate_header_title_breadcrumbs_include
     */
    do_action('thecrate_before_primary_area');
?>

<?php 
global $thecrate_redux;
$class = "col-md-12";
$side = "";

if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
	if ( $thecrate_redux['thecrate_shop_layout'] == 'thecrate_shop_fullwidth' ) {
	    $class = "col-md-12";
	}elseif ( $thecrate_redux['thecrate_shop_layout'] == 'thecrate_shop_right_sidebar' or $thecrate_redux['thecrate_shop_layout'] == 'thecrate_shop_left_sidebar') {
	    $class = "col-md-9";
	    if ( $thecrate_redux['thecrate_shop_layout'] == 'thecrate_shop_right_sidebar' ) {
	    	$side = "right";
	    }else{
	    	$side = "left";
	    }
	}
}
?>


<div class="thecrate-woo-shop row">

	<div class="container high-padding">
		<?php
			/**
			 * woocommerce_before_main_content hook.
			 *
			 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
			 * @hooked woocommerce_breadcrumb - 20
			 */
			do_action( 'woocommerce_before_main_content' );
		?>

	        <?php if ( class_exists( 'ReduxFrameworkPlugin' ) ) { ?>
		        <?php if (is_active_sidebar(thecrate_redux('thecrate_shop_layout_sidebar'))) { ?>
			        <?php if ( $side == 'left' ) { ?>
				        <div class="col-md-3 sidebar-content thecrate-shop-sidebar left">
				        	<div class="thecrate-shop-sidebar-content-inner">
					            <?php
									/**
									 * woocommerce_sidebar hook
									 *
									 * @hooked woocommerce_get_sidebar - 10
									 */
									do_action( 'woocommerce_sidebar' );
								?>
					        </div>
				        </div>
			        <?php } ?>
		        <?php }else{ ?>
		            <?php if (is_active_sidebar('woocommerce')) { ?>
				        <div class="col-md-3 sidebar-content thecrate-shop-sidebar left">
				        	<div class="thecrate-shop-sidebar-content-inner">
					            <?php
									/**
									 * woocommerce_sidebar hook
									 *
									 * @hooked woocommerce_get_sidebar - 10
									 */
									do_action( 'woocommerce_sidebar' );
								?>
					        </div>
				        </div>
		            <?php }else{ ?>
		            	<?php $class = 'col-md-12'; ?>
		            <?php } ?>
		        <?php } ?>
	        <?php } ?>

            <div class="<?php echo esc_attr($class); ?> main-content">
				<?php
					/**
					 * woocommerce_archive_description hook.
					 *
					 * @hooked woocommerce_taxonomy_archive_description - 10
					 * @hooked woocommerce_product_archive_description - 10
					 */
					do_action( 'woocommerce_archive_description' );
				?>

				<?php if ( have_posts() ) : ?>

					<?php
						/**
						 * woocommerce_before_shop_loop hook.
						 *
						 * @hooked woocommerce_result_count - 20
						 * @hooked woocommerce_catalog_ordering - 30
						 */
						do_action( 'woocommerce_before_shop_loop' );
					?>

					<?php woocommerce_product_loop_start(); ?>

						<?php woocommerce_product_subcategories(); ?>

						<?php while ( have_posts() ) : the_post(); ?>

							<?php wc_get_template_part( 'content', 'product' ); ?>

						<?php endwhile; // end of the loop. ?>

					<?php woocommerce_product_loop_end(); ?>

					<?php
						/**
						 * woocommerce_after_shop_loop hook.
						 *
						 * @hooked woocommerce_pagination - 10
						 */
						do_action( 'woocommerce_after_shop_loop' );
					?>

				<?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

					<?php wc_get_template( 'loop/no-products-found.php' ); ?>

				<?php endif; ?>
			</div>

	        <?php if ( $side == 'right' ) { ?>
		        <div class="col-md-3 sidebar-content thecrate-shop-sidebar right">
		        	<div class="thecrate-shop-sidebar-content-inner">
			            <?php
							do_action( 'woocommerce_sidebar' );
						?>
			        </div>
		        </div>
	        <?php } ?>


		<?php
			/**
			 * woocommerce_after_main_content hook.
			 *
			 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
			 */
			do_action( 'woocommerce_after_main_content' );
		?>
	</div>
</div>


<?php get_footer( 'shop' ); ?>
