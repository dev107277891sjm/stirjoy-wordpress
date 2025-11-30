<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 */

get_header(); 

$class = "col-md-8";
if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
    if ( thecrate_redux('thecrate_blog_layout') == 'thecrate_blog_fullwidth' ) {
        $class = "col-md-12";
    }elseif ( thecrate_redux('thecrate_blog_layout') == 'thecrate_blog_right_sidebar' or thecrate_redux('thecrate_blog_layout') == 'thecrate_blog_left_sidebar') {
        $class = "col-md-8";
    }
    $sidebar = thecrate_redux('thecrate_blog_layout_sidebar');
}else{
    $sidebar = 'sidebar-1';
}


if ( !is_active_sidebar ( $sidebar ) ) { 
  $class = "col-md-12";
}

?>

    <?php 
        /**
         * thecrate_before_primary_area hook.
         *
         * @hooked thecrate_header_title_breadcrumbs_include
         */
        do_action('thecrate_before_primary_area');
    ?>

    <!-- Page content -->
    <div class="high-padding">
        <!-- Blog content -->
        <div class="container blog-posts">
            <div class="row">

                <?php
                    if ( thecrate_redux('thecrate_blog_layout') == 'thecrate_blog_left_sidebar') {
                        echo '<div class="col-md-4 sidebar-content blog-sidebar-content blog-left-sidebar-content">';
                            dynamic_sidebar( $sidebar );
                        echo '</div>';
                    }
                ?>

                <div class="<?php echo esc_attr($class); ?> main-content">
                    <?php if ( have_posts() ) : ?>
                        <div class="row">

                            <?php /* Start the Loop */ ?>
                            <?php while ( have_posts() ) : the_post(); ?>

                                <?php get_template_part( 'content', 'post' ); ?>
                                    
                            <?php endwhile; ?>
                        
                        </div>

                        <div class="theme-pagination-holder row">             
                            <div class="theme-pagination pagination">             
                                <?php thecrate_pagination(); ?>
                            </div>
                        </div>

                    <?php else : ?>
                        <?php get_template_part( 'content', 'none' ); ?>
                    <?php endif; ?>
                </div>

                <?php
                    if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
                        if ( thecrate_redux('thecrate_blog_layout') == 'thecrate_blog_right_sidebar') {
                            if ( is_active_sidebar ( $sidebar ) ){
                                echo '<div class="col-md-4 sidebar-content blog-sidebar-content blog-right-sidebar-content">';
                                    dynamic_sidebar( $sidebar );
                                echo '</div>';
                            }
                        }
                    }else{
                        if ( is_active_sidebar ( $sidebar ) ){
                            echo '<div class="col-md-4 sidebar-content blog-sidebar-content blog-right-sidebar-content">';
                                dynamic_sidebar( $sidebar );
                            echo '</div>';
                        }
                    }
                ?>
            </div>
        </div>
    </div>

<?php get_footer(); ?>