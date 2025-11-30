<?php
/**
 * The template for displaying a standard search form.
 *
 */
?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
	<label>
		<span class="screen-reader-text"><?php esc_html_e( 'Search for:', 'thecrate' ); ?></span>
		<input type="search" class="search-field" placeholder="<?php esc_attr_e( 'Search', 'thecrate' ); ?>" value=""
		name="s" title="<?php esc_attr_e( 'Search for:', 'thecrate' ); ?>" />
	</label>
	<input class="search-submit" value="&#xf002;" type="submit">
</form>