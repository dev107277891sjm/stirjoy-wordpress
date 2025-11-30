<?php
/*
* Template Name: Blog - Right Sidebar
*/
    get_header(); 

    /**
     * thecrate_before_primary_area hook.
     *
     * @hooked thecrate_header_title_breadcrumbs_include
     */
    do_action('thecrate_before_primary_area');


    $class = "col-md-8";
    $sidebar = 'sidebar-1';
    if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
        if ( thecrate_redux('thecrate_blog_layout') == 'thecrate_blog_fullwidth' ) {
            $class = "col-md-12";
        }elseif ( thecrate_redux('thecrate_blog_layout') == 'thecrate_blog_right_sidebar' or thecrate_redux('thecrate_blog_layout') == 'thecrate_blog_left_sidebar') {
            $class = "col-md-8";
        }
        $sidebar = thecrate_redux('thecrate_blog_layout_sidebar');
    }

    if ( !is_active_sidebar ( $sidebar ) ) { 
      $class = "col-md-12";
    }
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
            <div class="col-md-12 main-content">
                <div class="row">
                    <div class="<?php echo esc_attr($class); ?> blog-posts-list">
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

                        <div class="clearfix"></div>

                        <?php 
                        query_posts($args);
                        global  $wp_query;
                        if ($wp_query->max_num_pages != 1) { ?>                
                            <div class="theme-pagination-holder row">           
                                <div class="theme-pagination pagination">           
                                    <?php thecrate_pagination(); ?>
                                </div>
                            </div>
                        <?php } ?>

                    </div>

                    <?php if ( is_active_sidebar( $sidebar )) { ?>
                        <div class="col-md-4 sidebar-content blog-sidebar-content blog-right-sidebar-content">
                            <?php dynamic_sidebar( $sidebar ); ?>
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