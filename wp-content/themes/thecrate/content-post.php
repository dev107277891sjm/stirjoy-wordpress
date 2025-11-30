<?php 
$placeholder = '600x500';
$master_class = 'col-md-12';
$thumbnail_class = 'col-md-4';
$post_details_class = 'col-md-8';
$type_class = 'list-view';

if ( thecrate_redux('thecrate_blog_display_type') == 'list' ) {
    $master_class = 'col-md-12';
    $thumbnail_class = 'col-md-4';
    $post_details_class = 'col-md-8';
    $type_class = 'list-view';
} else {
    if ( thecrate_redux('thecrate_blog_grid_columns') == 1 ) {
        $master_class = 'col-md-12';
        $type_class .= ' grid-one-column';
        $placeholder = '900x500';
    }elseif ( thecrate_redux('thecrate_blog_grid_columns') == 2 ) {
        $master_class = 'col-md-6';
        $type_class .= ' grid-two-columns';
        $placeholder = '900x500';
    }elseif ( thecrate_redux('thecrate_blog_grid_columns') == 3 ) {
        $master_class = 'col-md-4';
        $type_class .= ' grid-three-columns';
        $placeholder = '600x500';
    }elseif ( thecrate_redux('thecrate_blog_grid_columns') == 4 ) {
        $master_class = 'col-md-3';
        $type_class .= ' grid-four-columns';
        $placeholder = '600x500';
    }
    $thumbnail_class = 'full-width-part';
    $post_details_class = 'full-width-part';
} 


// THUMBNAIL
$post_img = '';
$featured_image_status = '';
$thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'thecrate_1150x600' );
if ($thumbnail_src) {
    $post_img = '<img class="blog_post_image" src="'. esc_url($thumbnail_src[0]) . '" alt="'.esc_attr(the_title_attribute( 'echo=0' )).'" />';
}else{
    $featured_image_status = 'no-featured-image';
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('single-post archive-page grid-view '.esc_attr($master_class).' '.esc_attr($type_class).' '.esc_attr($featured_image_status)); ?> > 
    <div class="blog_custom">

        <?php if ($post_img) { ?>
            <!-- POST THUMBNAIL -->
            <div class="col-md-12 post-thumbnail">
                <a href="<?php the_permalink(); ?>" class="relative">
                    <?php echo wp_kses($post_img, 'link'); ?>
                    <span class="read-more-overlay">
                        <i class="fas fa-link"></i>
                    </span>
                </a>
            </div>
        <?php } ?>

        <!-- POST DETAILS -->
        <div class="col-md-12 post-details">

            <div class="post-category-comment-date">
                <span class="post-date">
                    <a title="<?php the_title_attribute() ?>" href="<?php echo esc_url(get_day_link(get_post_time('Y'), get_post_time('m'), get_post_time('j')));  ?>"><?php echo get_the_date(); ?></a>
                </span> 
                <span class="post-author">/ <?php echo esc_html__('by', 'thecrate'); ?> <a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ), get_the_author_meta( 'user_nicename' ) ); ?>"><?php echo get_the_author(); ?></a></span>
                <span class="post-tags">
                    / <?php echo esc_html__('In', 'thecrate'); ?>  <?php echo get_the_term_list( get_the_ID(), 'category', '', ', ' ); ?>
                </span>
            </div>

            <h3 class="post-name row">
                <a title="<?php the_title_attribute() ?>" href="<?php the_permalink(); ?>">
                    <?php the_title() ?>
                    <?php if (is_sticky()) { echo esc_html__('*', 'thecrate'); } ?>
                </a>
            </h3>
            
            <div class="post-excerpt row">
            <?php
                the_excerpt();
            ?>
            <p>
                <a href="<?php the_permalink(); ?>" class="more-link">
                    <?php echo esc_html__( 'Read More', 'thecrate' ); ?>
                </a>
            </p>

            <div class="clearfix"></div>
            <?php
                wp_link_pages( array(
                    'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'thecrate' ),
                    'after'  => '</div>',
                ) );
            ?>
            </div>
        </div>
    </div>
</article>