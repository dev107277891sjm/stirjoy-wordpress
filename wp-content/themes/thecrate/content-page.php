<?php
/**
 * The template used for displaying page content in page.php
 *
 */

$post_slug = get_post_field( 'post_name', get_post() );
if ($post_slug) {
    $post_slug_class = $post_slug;
}else{
    $post_slug_class = '';
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="entry-content <?php echo esc_attr($post_slug_class); ?>">
		<?php the_content(); ?>
		<div class="clearfix"></div>
		<?php
			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'thecrate' ),
				'after'  => '</div>',
			) );
		?>
	</div><!-- .entry-content -->
</article><!-- #post-## -->