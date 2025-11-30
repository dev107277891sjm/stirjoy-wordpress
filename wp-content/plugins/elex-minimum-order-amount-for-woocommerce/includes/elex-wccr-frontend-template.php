<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( WP_PLUGIN_DIR . '/woocommerce/includes/admin/settings/class-wc-settings-page.php' );

class Elex_WCCR_Settings extends WC_Settings_Page {

	public $id;

	public $user_adjustment_settings;

	public $restriction_table;
	
	public function __construct() {
		$this->init();
		$this->id = 'elex-wccr';
	}
	public function init() {
		$this->user_adjustment_settings = get_option( 'elex_wccr_checkout_restriction_settings', array() );
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'elex_wccr_add_settings_tab' ), 50 );
		add_filter( 'woocommerce_sections_elex-wccr', array( $this, 'output_sections' ) );
	
		add_filter( 'woocommerce_settings_elex-wccr', array( $this, 'elex_wccr_output_settings' ) );
		
		add_action( 'woocommerce_update_options_elex-wccr', array( $this, 'elex_wccr_update_settings' ) );
		add_action( 'woocommerce_admin_field_checkoutrestrictiontable', array( $this, 'elex_wccr_admin_field_checkoutrestrictiontable' ) );
		add_action('wp_ajax_fetch_products_by_category', array( $this, 'fetch_products_by_category'));
		add_action('wp_ajax_nopriv_fetch_products_by_category', array( $this,'fetch_products_by_category'));

		add_action('wp_ajax_fetch_users_by_role', array( $this, 'fetch_users_by_role'));
		add_action('wp_ajax_nopriv_fetch_users_by_role', array( $this, 'fetch_users_by_role'));
		
	}
	public function fetch_products_by_category() {
		// Sanitize and verify nonce
		$nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
		if (!wp_verify_nonce($nonce, 'minimum_order_nonce')) {
			wp_send_json_error(array('message' => 'Invalid nonce'));
			return;
		}
	
		// Check if categories are set and are an array
		if (isset($_POST['categories']) && is_array($_POST['categories'])) {
		  $categories = array_map('sanitize_text_field', $_POST['categories']);
		} else {
		  $categories = array(); // Set an empty array for categories
		}
	  
		// Prepare query arguments
		$args = array(
		  'post_type' => 'product',
		  'posts_per_page' => -1,
		);
	  
		// Add category filter if categories are provided
		if (!empty($categories)) {
		  $args['tax_query'] = array(
			array(
			  'taxonomy' => 'product_cat',
			  'field' => 'id',
			  'terms' => $categories,
			),
		  );
		}
	  
		// Execute query
		$products_query = new WP_Query($args);
		$products = array();
	  
		if ($products_query->have_posts()) {
			while ($products_query->have_posts()) {
			  $products_query->the_post();
			  $products[] = array(
				'id' => get_the_ID(),
				'name' => get_the_title(),
			  );
			}
		  wp_reset_postdata();
		}
		// Send JSON response
		wp_send_json_success(array('products' => $products));
	}

	public function fetch_users_by_role() {
		// Sanitize and verify nonce
		$nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
		if (!wp_verify_nonce($nonce, 'minimum_order_nonce')) {
			wp_send_json_error(array('message' => 'Invalid nonce'));
			return;
		}
		if (isset($_POST['roles']) && is_array($_POST['roles'])) {
			$roles = array_map('sanitize_text_field', $_POST['roles']);
		} else {
			$roles = array(); // Set an empty array for roles
		}
	
		// Prepare query arguments
		$args = array(
			'fields' => array('ID', 'display_name', 'user_email'),
		);
	
		// Add role filter if roles are provided
		if (!empty($roles)) {
			$args['role__in'] = $roles;
		}
	
		// Execute query
		$user_query = new WP_User_Query($args);
		$users = array();
	
		if (!empty($user_query->get_results())) {
			foreach ($user_query->get_results() as $user) {
				$users[] = array(
					'id' => $user->ID,
					'name' => $user->display_name,
					'email' => $user->user_email,
				);
			}
		}
		wp_send_json_success(array('users' => $users));
	}

	public function elex_wccr_add_settings_tab( $settings_tabs ) {
		$settings_tabs['elex-wccr'] = esc_html__( 'Minimum Order Amount', 'elex-wc-checkout-restriction' );
		return $settings_tabs;
	}
	public function get_sections() {
		$sections = array(
			''                           => __( 'Minimum Order Amount', 'elex-wc-checkout-restriction' ),
			'min-order-related-products' => __( '<li><strong><font color="red">Related Products</font></strong></li>', 'elex-wc-checkout-restriction' ),
		);
		
		/**
		 * To woocommerce get sections.
		 *
		 * @since  1.0.0
		 */
		return apply_filters( 'woocommerce_get_sections_minimum_order_amount', $sections );
	}
	public function output_sections() {
		global $current_section;
		$sections = $this->get_sections();
		if ( empty( $sections ) || 1 === count( $sections ) ) {
			return;
		}
		echo '<ul class="subsubsub">';
		$array_keys = array_keys( $sections );
		foreach ( $sections as $id => $label ) {
			echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) ) . '" class="' . ( $current_section === $id ? 'current' : '' ) . '">' . wp_kses_post( $label ) . '</a> ' . ( end( $array_keys ) === $id ? '' : '|' ) . ' </li>';
		}
		echo '</ul><br class="clear" />';
	}
	public function elex_wccr_output_settings() {
		$settings = $this->elex_wccr_get_settings();

		global $current_section;
		if ( '' === $current_section ) {
			WC_Admin_Settings::output_fields( $settings );

		}
		if ( 'min-order-related-products' === $current_section ) {
			wp_enqueue_style( 'bootstrap', plugins_url( '../assests/css/bootstrap.css', __FILE__ ), false, true );
			wp_enqueue_style( 'bootstrap', plugins_url( '../assests/css/base.css', __FILE__ ), false, true );
			include_once 'market.php';
		}

	}

	
	public function elex_wccr_update_settings() {
		$options = $this->elex_wccr_get_settings();
		woocommerce_update_options( $options );
		$this->user_adjustment_settings = get_option( 'elex_wccr_checkout_restriction_settings', array() );
	}
	public function elex_wccr_admin_field_checkoutrestrictiontable( $settings ) {
		
		wp_enqueue_style( 'bootstrap', plugins_url( '../assests/css/base.css', __FILE__ ), false, true );
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script( 'elex_popper_js', plugins_url( '../assests/js/elex_popper.js', __FILE__ ), array( 'jquery', 'underscore' ), true );
		wp_enqueue_script( 'elex_bootstrap_js', plugins_url( '../assests/js/elex_bootstrap.js', __FILE__ ), array( 'jquery', 'underscore' ), true );
		wp_enqueue_script( 'min_order_rules_js', plugins_url( '../assests/js/min-order-rules.js', __FILE__ ), array( 'jquery', 'underscore' ), true );
		wp_localize_script('min_order_rules_js', 'myAjax', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('minimum_order_nonce')
		));
		// include( 'elex-wccr-restriction-table.php' );
		include( 'elex-wccr-restriction-table.php' );
	}

	public function elex_wccr_get_settings() {
		$settings = array(
			'elex_restricton_settings' => array(
				'type' => 'checkoutrestrictiontable',
				'id' => 'elex_wccr_checkout_restriction_settings',
				'value' => '',
			)
		);
		return $settings;
	}
}
new Elex_WCCR_Settings();
