<?php
/**
 * Load Gutenberg stylesheet.
 */
function thecrate_add_gutenberg_assets() {
	// Load the theme styles within Gutenberg.
	wp_enqueue_style( 'thecrate-gutenberg-editor-style', get_theme_file_uri( '/css/gutenberg-editor-style.css' ), false );
	wp_enqueue_style( 'wp-block-library' ); 
    wp_enqueue_style( 
        'thecrate-gutenberg-fonts', 
        '//fonts.googleapis.com/css?family=Jost:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap|Work+Sans:100,200,300,regular,500,600,700,800,900,latin-ext,latin' 
    ); 
}
add_action( 'enqueue_block_editor_assets', 'thecrate_add_gutenberg_assets' );

/**
 * Gutenberg body class.
 */
function thecrate_add_gutenberg_body_class( $body_classes ) {
	if ( is_singular() && false !== strpos( get_queried_object()->post_content, '<!-- wp:' ) ) {
		$body_classes[] = 'gutenberg';
	}
	return $body_classes;
}
add_filter( 'body_class', 'thecrate_add_gutenberg_body_class' );