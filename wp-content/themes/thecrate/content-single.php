<?php
/**
* Content Single
*/
$select_post_layout = get_post_meta( get_the_ID(), 'select_post_layout', true );
$select_post_sidebar = get_post_meta( get_the_ID(), 'select_post_sidebar', true );
$post_slug = get_post_field( 'post_name', get_post() );
if ($post_slug) {
    $post_slug_class = $post_slug;
}else{
    $post_slug_class = '';
}
$sidebar = 'sidebar-1';
if ( function_exists('themeslr_framework')) {
    if (isset($select_post_sidebar) && $select_post_sidebar != '') {
        $sidebar = $select_post_sidebar;
    }else{
        $sidebar = thecrate_redux('thecrate_single_blog_layout_sidebar');
    }
}


$cols = 'col-md-12 col-sm-12';
$sidebars_lr_meta = array("left-sidebar", "right-sidebar");
if (isset($select_post_layout) && in_array($select_post_layout, $sidebars_lr_meta)) {
    $cols = 'col-md-9 col-sm-9 status-meta-sidebar';
}elseif(isset($select_post_layout) && $select_post_layout == 'no-sidebar'){
    $cols = 'col-md-12 col-sm-12 status-meta-fullwidth';
}else{
    if(class_exists( 'ReduxFrameworkPlugin' )){
        $sidebars_lr_panel = array("thecrate_single_blog_left_sidebar", "thecrate_single_blog_right_sidebar");
        if (in_array(thecrate_redux('thecrate_single_blog_layout'), $sidebars_lr_panel)) {
            $cols = 'col-md-9 col-sm-9 status-panel-sidebar';
        }else{
            $cols = 'col-md-12 col-sm-12 status-panel-no-sidebar';
        }
    }
}
if (!is_active_sidebar($sidebar)) {
    $cols = "col-md-12";
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

<article id="post-<?php the_ID(); ?>" <?php post_class('post high-padding'); ?>>
    <div class="container">
       <div class="row">
            <div class="sidebar-content-wrap">
                <?php if (isset($select_post_layout) && $select_post_layout == 'left-sidebar') { ?>
                    <div class="col-md-3 col-sm-3 sidebar-content sidebar-left">
                        <?php if (is_active_sidebar($sidebar)) { ?>
                            <?php dynamic_sidebar($sidebar); ?>
                        <?php } ?>
                    </div>
                <?php }else{ ?>
                    <?php if (isset($select_post_layout) && $select_post_layout == 'inherit') { ?>
                        <?php if(class_exists( 'ReduxFrameworkPlugin' )){ ?>
                            <?php if ( thecrate_redux('thecrate_single_blog_layout') == 'thecrate_single_blog_left_sidebar') { ?>
                                <div class="col-md-3 col-sm-3 sidebar-content sidebar-left">
                                    <?php if (is_active_sidebar($sidebar)) { ?>
                                        <?php dynamic_sidebar($sidebar); ?>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>

                <!-- POST CONTENT -->
                <div class="<?php echo esc_attr($cols); ?> main-content">
                    <!-- CONTENT -->
                    <div class="article-content <?php echo esc_attr($post_slug_class); ?>">
                        
                        <?php /*WP 5.x Changes*/ ?>
                        <?php if(class_exists( 'ReduxFrameworkPlugin' ) && thecrate_redux('thecrate_post_featured_image')){ ?>
                            <?php if(has_post_thumbnail()) { ?>
                                <?php $thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ),'thecrate_1300x650' ); ?>
                                <?php if($thumbnail_src) { ?>
                                    <img src="<?php echo esc_url($thumbnail_src[0]); ?>" class="img-responsive single-post-featured-img" alt="<?php the_title_attribute(); ?>" />
                                <?php } ?>
                            <?php } ?>
                        <?php }else{ ?>
                            <?php if(has_post_thumbnail()) { ?>
                                <?php $thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ),'thecrate_1300x650' ); ?>
                                <?php if($thumbnail_src) { ?>
                                    <img src="<?php echo esc_url($thumbnail_src[0]); ?>" class="img-responsive single-post-featured-img" alt="<?php the_title_attribute(); ?>" />
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>

                        <div class="post-category-comment-date">
                            <span class="post-date">
                                <a title="<?php the_title_attribute() ?>" href="<?php echo esc_url(get_day_link(get_post_time('Y'), get_post_time('m'), get_post_time('j')));  ?>"><?php echo get_the_date(); ?></a>
                            </span> 
                            <span class="post-author">/ <?php echo esc_html__('By', 'thecrate'); ?> <a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ), get_the_author_meta( 'user_nicename' ) ); ?>"><?php echo get_the_author(); ?></a></span>
                            <span class="post-tags">
                                / <?php echo esc_html__('In', 'thecrate'); ?>  <?php echo get_the_term_list( get_the_ID(), 'category', '', ', ' ); ?>
                            </span>

                            <?php do_action('thecrate_after_single_post_metas'); ?>
                        </div>
                        <div class="clearfix"></div>

                        <?php the_content(); ?>
                        <div class="clearfix"></div>
                        
                        <?php if (get_the_tags()) { ?>
                            <div class="single-post-tags">
                                <?php echo esc_html__( 'Post tags: ', 'thecrate' ) . get_the_term_list( get_the_ID(), 'post_tag', '', ' ' ); ?>
                            </div>
                        <?php } ?>
                        <div class="clearfix"></div>
                      
                        <?php
                            wp_link_pages( array(
                                'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'thecrate' ),
                                'after'  => '</div>',
                            ) );
                        ?>
                        <div class="clearfix"></div>

                        <!-- AUTHOR BIO -->
                        <?php if ( thecrate_redux('thecrate_enable_authorbio') ) { ?>

                            <?php   
                            $avatar = get_avatar( get_the_author_meta('email'), '80', get_the_author() );
                            $has_image = '';
                            if( $avatar !== false ) {
                                $has_image .= 'no-author-pic';
                            }
                            ?>

                            <div class="author-bio relative <?php echo esc_attr($has_image); ?>">
                                <div class="author-thumbnail col-md-4">
                                    <?php
                                    if( $avatar !== false ) {
                                        echo get_avatar( get_the_author_meta('email'), '80', get_the_author() ); 
                                    }
                                    ?>
                                    <div class="pull-left">
                                        <div class="author-name">
                                            <span><?php echo esc_html__('Article by','thecrate'); ?></span>
                                            <span class="name"><?php echo get_the_author(); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="author-thumbnail col-md-8">
                                    <div class="author-biography"><?php the_author_meta('description'); ?></div>
                                </div>
                            </div>
                        <?php } ?>


                        <div class="clearfix"></div>

                        <!-- COMMENTS -->
                        <?php
                            // If comments are open or we have at least one comment, load up the comment template
                            if ( comments_open() || get_comments_number() ) {
                                comments_template();
                            }
                        ?>
                    </div>
                </div>

                <?php /*WP 5.x Changes*/ ?>
                <?php if(class_exists( 'ReduxFrameworkPlugin' )){ ?>
                    <?php if (isset($select_post_layout) && $select_post_layout == 'right-sidebar') { ?>
                        <div class="col-md-3 sidebar-content sidebar-right">
                            <?php if (is_active_sidebar($sidebar)) { ?>
                                <?php dynamic_sidebar($sidebar); ?>
                            <?php } ?>
                        </div>
                    <?php }elseif(isset($select_post_layout) && $select_post_layout == 'inherit') { ?>
                        <?php if ( thecrate_redux('thecrate_single_blog_layout') == 'thecrate_single_blog_right_sidebar') { ?>
                            <div class="col-md-3 sidebar-content sidebar-right">
                                <?php if (is_active_sidebar($sidebar)) { ?>
                                    <?php dynamic_sidebar($sidebar); ?>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>

            </div>
        </div>
    </div>
</article>


<?php if ( class_exists( 'ReduxFrameworkPlugin' ) ) { ?>
    <div class="row post-details-bottom">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <?php if ( thecrate_redux('thecrate_enable_related_posts') ) { ?>
                        <div class="clearfix"></div>
                        <div class="related-posts sticky-posts">
                            <h2 class="heading-bottom"><?php esc_html_e('Related Posts', 'thecrate'); ?></h2>
                            <div class="row">
                                <?php
                                wp_reset_postdata();
                                $args=array(  
                                    'post__not_in'          => array($post->ID),  
                                    'posts_per_page'        => 3, // Number of related posts to display.  
                                    'ignore_sticky_posts'   => 1  
                                );  

                                $related_posts_query = new WP_Query( $args );
                                while( $related_posts_query->have_posts() ) {  
                                    $related_posts_query->the_post(); ?>  
                                    <div class="col-md-4 post">
                                        <div class="related_blog_custom">
                                            <?php $thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ),'thecrate_related_post_pic700x400' ); ?>
                                            <?php if($thumbnail_src){ ?>
                                            <a href="<?php the_permalink(); ?>" class="relative">
                                                <?php if($thumbnail_src) { ?>
                                                    <?php the_post_thumbnail('thecrate_related_post_pic700x400', ['class' => 'img-responsive']); ?>
                                                <?php } ?>
                                            </a>
                                            <?php } ?>
                                            <div class="related_blog_details">
                                                <h4 class="post-name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                                                <div class="post-author"><?php echo esc_html__('Posted by ','thecrate'); ?><a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ), get_the_author_meta( 'user_nicename' ) ); ?>"><?php echo get_the_author(); ?></a> / <?php echo get_the_date( 'j M' ); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>  
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
<?php } ?>