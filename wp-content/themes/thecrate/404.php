<?php
/**
 * The template for displaying 404 pages (not found).
 *
 */

get_header(); ?>

	<!-- Page content -->
	<div id="primary" class="content-area">
	    <main id="main" class="container blog-posts site-main">
	        <div class="col-md-12 main-content">
				<section class="error-404 not-found">
					<header class="page-header-404">
						<div class="high-padding">

							<?php 
								$image_url = get_template_directory_uri() . '/images/404.png';
								$heading = esc_html__( 'Sorry, this page does not exist', 'thecrate' );
								$paragraph = esc_html__( 'The link you clicked might be corrupted, or the page may have been removed.', 'thecrate' );
							?>

							<?php if ( class_exists( 'ReduxFrameworkPlugin' ) ) { ?>
								<?php if ( thecrate_redux('thecrate_404_override_status') == true ) { ?>
									<?php 
									if(thecrate_redux('thecrate_404_image','url')){
										$image_url = thecrate_redux('thecrate_404_image','url');
									}
									if(thecrate_redux('thecrate_404_heading')){
										$heading = thecrate_redux('thecrate_404_heading');
									}
									if(thecrate_redux('thecrate_404_paragraph')){
										$paragraph = thecrate_redux('thecrate_404_paragraph');
									}
									?>
								<?php } ?>
							<?php } ?>
							
							<img class="aligncenter" src="<?php echo esc_url($image_url); ?>" alt="<?php esc_attr_e( 'Not Found', 'thecrate' ); ?>">
							<h1 class="page-title text-center"><?php echo esc_html($heading); ?></h1>
							<p class="page-title text-center"><?php echo esc_html($paragraph); ?></p>
							<p class="text-center">
								<a class="btn-xl btn-theme-default" href="<?php echo esc_url(get_site_url()); ?>"><?php esc_html_e( 'Back to Homepage', 'thecrate' ); ?></a>
							</p>
						</div>
					</header>
				</section>
			</div>
		</main>
	</div>

<?php get_footer(); ?>