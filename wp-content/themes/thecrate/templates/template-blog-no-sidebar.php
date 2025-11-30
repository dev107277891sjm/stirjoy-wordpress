<?php
/*
* Template Name: Blog - No Sidebar
*/


get_header(); 


    /**
     * thecrate_before_primary_area hook.
     *
     * @hooked thecrate_header_title_breadcrumbs_include
     */
    do_action('thecrate_before_primary_area');
?>

<!-- Page content -->
<div class="high-padding">
    <?php
    wp_reset_postdata();
    $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
    $args = array(
        'post_type'        => 'post',
        'post_status'      => 'publish',
        'paged'            => $paged,
    );
    $posts = new WP_Query( $args );
    ?>
    <!-- Blog content -->
    <div class="container blog-posts">
        <div class="row">
            <div class="col-md-10 col-md-offset-1 main-content">
                <div class="row">
                    <div class="col-md-12 blog-posts-list">
                    	<div class="row"> 
			                <?php if ( $posts->have_posts() ) : ?>
			                    <?php /* Start the Loop */ ?>
			                    <?php while ( $posts->have_posts() ) : $posts->the_post(); ?>

			                    	<?php get_template_part( 'content', 'post' ); ?>

			                    <?php endwhile; ?>
			                    <?php echo '<div class="clearfix"></div>'; ?>
			                <?php else : ?>
			                    <?php get_template_part( 'content', 'none' ); ?>
			                <?php endif; ?>
                        </div>
                    </div>

                <div class="clearfix"></div>

                <?php 
                query_posts($args);
                global  $wp_query;
                if ($wp_query->max_num_pages != 1) { ?>                
                <div class="theme-pagination-holder col-md-12">           
                    <div class="theme-pagination pagination">           
                        <?php thecrate_pagination(); ?>
                    </div>
                </div>
                <?php } ?>
                </div>
            </div>

        </div>
    </div>
</div>


<?php
get_footer();
?>