<?php
/**
 * Template Name: Privacy Policy
 * 
 * Custom template for Privacy Policy page matching Figma design
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
		<h1 class="stirjoy-privacy-policy-title"><?php esc_html_e( 'Privacy Policy', 'stirjoy-child' ); ?></h1>
		
		<!-- Last Updated Date -->
		<p class="stirjoy-privacy-policy-date"><?php esc_html_e( '(Last updated: Oct 25, 2025)', 'stirjoy-child' ); ?></p>
		
		<!-- Introduction -->
		<div class="stirjoy-privacy-section stirjoy-privacy-section-intro">
			<p class="stirjoy-privacy-policy-intro"><?php esc_html_e( 'Your privacy matters to us. We collect only what we need to serve you better.', 'stirjoy-child' ); ?></p>
		</div>
		<!-- Information We Collect Section -->
		<div class="stirjoy-privacy-section">
			<h2 class="stirjoy-privacy-section-title"><?php esc_html_e( 'Information We Collect', 'stirjoy-child' ); ?></h2>
			<ul class="stirjoy-privacy-list">
				<li><?php esc_html_e( 'Personal information you provide when ordering (name, address, email, payment info).', 'stirjoy-child' ); ?></li>
				<li><?php esc_html_e( 'Non-identifiable data like site traffic or preferences through cookies or analytics tools.', 'stirjoy-child' ); ?></li>
			</ul>
		</div>
		
		<!-- How We Use It Section -->
		<div class="stirjoy-privacy-section">
			<h2 class="stirjoy-privacy-section-title"><?php esc_html_e( 'How We Use It', 'stirjoy-child' ); ?></h2>
			<p class="stirjoy-privacy-text"><?php esc_html_e( 'We use your information to process orders, communicate with you, improve our products, and send updates (only if you\'ve subscribed).', 'stirjoy-child' ); ?></p>
		</div>
		
		<!-- Sharing of Information Section -->
		<div class="stirjoy-privacy-section">
			<h2 class="stirjoy-privacy-section-title"><?php esc_html_e( 'Sharing of Information', 'stirjoy-child' ); ?></h2>
			<p class="stirjoy-privacy-text"><?php esc_html_e( 'We don\'t sell your data. We may share it with trusted service providers (e.g., payment processors, delivery partners) who are bound by confidentiality obligations.', 'stirjoy-child' ); ?></p>
		</div>
		
		<!-- Your Rights Section -->
		<div class="stirjoy-privacy-section">
			<h2 class="stirjoy-privacy-section-title"><?php esc_html_e( 'Your Rights', 'stirjoy-child' ); ?></h2>
			<p class="stirjoy-privacy-text">
				<?php 
				printf(
					esc_html__( 'Under Québec\'s Law 25, you have the right to access, correct, or delete your personal information. To make a request, contact our Privacy Officer at %s.', 'stirjoy-child' ),
					'<a href="mailto:service@stirjoy.ca">service@stirjoy.ca</a>'
				);
				?>
			</p>
		</div>
		
		<!-- Data Retention & Security Section -->
		<div class="stirjoy-privacy-section">
			<h2 class="stirjoy-privacy-section-title"><?php esc_html_e( 'Data Retention & Security', 'stirjoy-child' ); ?></h2>
			<p class="stirjoy-privacy-text"><?php esc_html_e( 'We keep your information only as long as needed for the purposes described above and protect it with industry-standard safeguards.', 'stirjoy-child' ); ?></p>
		</div>
		
		<!-- Contact Us Section -->
		<div class="stirjoy-privacy-section">
			<h2 class="stirjoy-privacy-section-title"><?php esc_html_e( 'Contact Us', 'stirjoy-child' ); ?></h2>
			<p class="stirjoy-privacy-text">
				<?php 
				printf(
					esc_html__( 'If you have any privacy concerns, email us at %s.', 'stirjoy-child' ),
					'<a href="mailto:service@stirjoy.ca">service@stirjoy.ca</a>'
				);
				?>
			</p>
		</div>
	</div>
</div>

<?php get_footer(); ?>

