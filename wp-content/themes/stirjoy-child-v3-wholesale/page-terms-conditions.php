<?php
/**
 * Template Name: Terms & Conditions
 * 
 * Custom template for Terms of Service page matching Figma design
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
		<h1 class="stirjoy-privacy-policy-title"><?php esc_html_e( 'Terms of Service', 'stirjoy-child' ); ?></h1>
		
		<!-- Last Updated Date -->
		<p class="stirjoy-privacy-policy-date"><?php esc_html_e( '(Last updated: Oct 25, 2025)', 'stirjoy-child' ); ?></p>
		
		<!-- Introduction -->
		<div class="stirjoy-privacy-section stirjoy-privacy-section-intro">
			<p class="stirjoy-privacy-policy-intro"><?php esc_html_e( 'Welcome to Stirjoy! By using our website or purchasing our products, you agree to these Terms of Service.', 'stirjoy-child' ); ?></p>
			<p class="stirjoy-privacy-policy-intro"><?php esc_html_e( 'Our goal is simple: to make real meals easier for humans and more affordable for the planet.', 'stirjoy-child' ); ?></p>
		</div>
		
		<!-- Use of Service Section -->
		<div class="stirjoy-privacy-section">
			<h2 class="stirjoy-privacy-section-title"><?php esc_html_e( 'Use of Service', 'stirjoy-child' ); ?></h2>
			<p class="stirjoy-privacy-text"><?php esc_html_e( 'Our products are intended for personal, non-commercial use. You agree not to misuse our website, attempt to hack or breach our security, or access restricted areas of our systems.', 'stirjoy-child' ); ?></p>
		</div>
		
		<!-- Orders and Payments Section -->
		<div class="stirjoy-privacy-section">
			<h2 class="stirjoy-privacy-section-title"><?php esc_html_e( 'Orders and Payments', 'stirjoy-child' ); ?></h2>
			<p class="stirjoy-privacy-text"><?php esc_html_e( 'All prices are in Canadian dollars. We strive to ensure accuracy in our product descriptions and pricing, but we reserve the right to correct any errors in pricing or product information. We may cancel any order before it ships if we discover pricing errors or other issues.', 'stirjoy-child' ); ?></p>
		</div>
		
		<!-- Shipping & Returns Section -->
		<div class="stirjoy-privacy-section">
			<h2 class="stirjoy-privacy-section-title"><?php esc_html_e( 'Shipping & Returns', 'stirjoy-child' ); ?></h2>
			<p class="stirjoy-privacy-text"><?php esc_html_e( 'We deliver within Canada. Due to food safety regulations, we cannot accept returns of opened or used products. If you receive damaged or incorrect items, please contact us at service@stirjoy.ca within 7 days of delivery.', 'stirjoy-child' ); ?></p>
		</div>
		
		<!-- Intellectual Property Section -->
		<div class="stirjoy-privacy-section">
			<h2 class="stirjoy-privacy-section-title"><?php esc_html_e( 'Intellectual Property', 'stirjoy-child' ); ?></h2>
			<p class="stirjoy-privacy-text"><?php esc_html_e( 'All content on this website, including text, images, packaging designs, and trademarks, is the property of Stirjoy or our partners. You may not reproduce, distribute, or use any content without our written permission.', 'stirjoy-child' ); ?></p>
		</div>
		
		<!-- Limitation of Liability Section -->
		<div class="stirjoy-privacy-section">
			<h2 class="stirjoy-privacy-section-title"><?php esc_html_e( 'Limitation of Liability', 'stirjoy-child' ); ?></h2>
			<p class="stirjoy-privacy-text"><?php esc_html_e( 'While we work hard to keep our information accurate and our meals safe, Stirjoy is not liable for any indirect or incidental damages that may arise from your use of our website or products, except where such limitation is prohibited by law.', 'stirjoy-child' ); ?></p>
		</div>
		
		<!-- Governing Law Section -->
		<div class="stirjoy-privacy-section">
			<h2 class="stirjoy-privacy-section-title"><?php esc_html_e( 'Governing Law', 'stirjoy-child' ); ?></h2>
			<p class="stirjoy-privacy-text"><?php esc_html_e( 'These Terms of Service are governed by the laws of Québec and the federal laws of Canada. Any disputes will be resolved in the courts of Montréal, Québec.', 'stirjoy-child' ); ?></p>
		</div>
	</div>
</div>

<?php get_footer(); ?>

