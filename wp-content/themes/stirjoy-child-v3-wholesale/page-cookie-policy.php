<?php
/**
 * Template Name: Cookie Policy
 * 
 * Custom template for Cookie Policy page matching Figma design
 *
 * @package Stirjoy Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header(); ?>

<div class="stirjoy-privacy-policy-page">
	<div class="stirjoy-privacy-policy-container">
		<!-- Page Title -->
		<h1 class="stirjoy-privacy-policy-title"><?php esc_html_e( 'Cookie Policy', 'stirjoy-child' ); ?></h1>
		
		<!-- Last Updated Date -->
		<p class="stirjoy-privacy-policy-date"><?php esc_html_e( '(Last updated: Oct 25, 2025)', 'stirjoy-child' ); ?></p>
		
		<!-- Introduction -->
		<div class="stirjoy-privacy-section stirjoy-privacy-section-intro">
			<p class="stirjoy-privacy-policy-intro"><?php esc_html_e( 'Our website uses cookies to enhance your browsing experience and understand how people use our site.', 'stirjoy-child' ); ?></p>
		</div>
		
		<!-- What Are Cookies Section -->
		<div class="stirjoy-privacy-section">
			<h2 class="stirjoy-privacy-section-title"><?php esc_html_e( 'What Are Cookies?', 'stirjoy-child' ); ?></h2>
			<p class="stirjoy-privacy-text"><?php esc_html_e( 'Cookies are small text files stored on your device that help us remember preferences or measure performance.', 'stirjoy-child' ); ?></p>
		</div>
		
		<!-- Types of Cookies We Use Section -->
		<div class="stirjoy-privacy-section">
			<h2 class="stirjoy-privacy-section-title"><?php esc_html_e( 'Types of Cookies We Use', 'stirjoy-child' ); ?></h2>
			<ul class="stirjoy-privacy-list">
				<li><?php esc_html_e( 'Essential cookies (for core site functionality)', 'stirjoy-child' ); ?></li>
				<li><?php esc_html_e( 'Analytical cookies (to help us improve user experience)', 'stirjoy-child' ); ?></li>
				<li><?php esc_html_e( 'Preference cookies (to remember your settings)', 'stirjoy-child' ); ?></li>
			</ul>
		</div>
		
		<!-- How to Manage Cookies Section -->
		<div class="stirjoy-privacy-section">
			<h2 class="stirjoy-privacy-section-title"><?php esc_html_e( 'How to Manage Cookies', 'stirjoy-child' ); ?></h2>
			<p class="stirjoy-privacy-text"><?php esc_html_e( 'You can change your browser settings to refuse or delete cookies at any time. Some parts of the site may not work properly if you disable them.', 'stirjoy-child' ); ?></p>
		</div>
		
		<!-- Contact Section -->
		<div class="stirjoy-privacy-section">
			<h2 class="stirjoy-privacy-section-title"><?php esc_html_e( 'Contact', 'stirjoy-child' ); ?></h2>
			<p class="stirjoy-privacy-text">
				<?php 
				printf(
					esc_html__( 'Questions about cookies? Contact us at %s.', 'stirjoy-child' ),
					'<a href="mailto:service@stirjoy.ca">service@stirjoy.ca</a>'
				);
				?>
			</p>
		</div>
	</div>
</div>

<?php get_footer(); ?>

