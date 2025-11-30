<?php
/**
 * The application scope class to retreive data.
 *
 * @link       https://themehigh.com
 * @since      2.1.1
 *
 * @package    woocommerce-multiple-addresses-pro
 * @subpackage woocommerce-multiple-addresses-pro/src/includes
 */
namespace Themehigh\WoocommerceMultipleAddressesPro\admin;

use \Themehigh\WoocommerceMultipleAddressesPro\includes\utils\THWMA_Utils;

if(!defined('WPINC')) {	
	die; 
}

if(!class_exists('THWMA_Admin_Data')) :

class THWMA_Admin_Data {
	protected static $_instance = null;

	/**
	 * for create instances.
	 */
	public static function instance() {
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * ajax function for product lazy loading.
	 * Called in the `Multiple shipping` section on the `General settings` page
	 */
	public function load_products_ajax(){
		$product_list = array();
		$value = isset($_POST['value']) ? sanitize_text_field($_POST['value']) : '';
		$count = 0;
		$limit = apply_filters('thwma_load_products_per_page', 100);
		
		if(!empty($value)){
			$value_arr = is_array($value) ? $value : explode(',', stripslashes($value));

			$args = array(
			    'include' => $value_arr,
				'orderby' => 'name',
				'order' => 'ASC',
				'return' => 'ids',
				'limit' => $limit,
				'type'  => $this->get_all_product_types(),
			);
			$products = $this->get_products($args);

			if(is_array($products) && !empty($products)){
				foreach($products as $pid){
					$product_list[] = array("id" => get_post_field('post_name', $pid), "text" => get_the_title($pid). "(#" .$pid. ")", "selected" => true);
				}
			}
			$count = count($products);
		}else{
			$term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';
			$page = isset($_POST['page']) ? sanitize_text_field($_POST['page']) : 1;

		    $status = apply_filters('thwma_load_products_status', 'publish');

		    $args = array(
				's' => $term,
			    'limit' => $limit,
			    'page'  => $page,
			    'status' => $status,
				'orderby' => 'name',
				'order' => 'ASC',
				'return' => 'ids',
				'type'  => $this->get_all_product_types()
			);

			$products = $this->get_products($args);

			if(is_array($products) && !empty($products)){
				foreach($products as $pid){
					$product_list[] = array("id" => get_post_field('post_name', $pid), "text" => get_the_title($pid) . "(#" .$pid. ")" );
				}
			}
			$count = count($products);
		}

		$morePages = $count < $limit ? false : true;
		$results = array(
			"results" => $product_list,
			"pagination" => array( "more" => $morePages )
		);

		wp_send_json_success($results);
  		die();
	}

	/**
	 * Get all product types in store incluiding product variation
	 *
	 * @return Array
	 */ 
	public function get_all_product_types(){
		$product_types = array_merge( array_keys( wc_get_product_types() ));
		array_push($product_types, "variation");
		apply_filters('thmaf_rules_product_types',  $product_types);
		return $product_types;
	}

	/**
	 * Get all products.
	 * And check wpml is active.
	 *
	 * @return Array
	 */
	private function get_products($args){
		$products = false;
		$is_wpml_active = THWMA_Utils::is_wpml_active();
		if($is_wpml_active){
			global $sitepress;
			global $icl_adjust_id_url_filter_off;

			$orig_flag_value = $icl_adjust_id_url_filter_off;
			$icl_adjust_id_url_filter_off = true;
			$default_lang = $sitepress->get_default_language();
			$current_lang = $sitepress->get_current_language();
			$sitepress->switch_lang($default_lang);

			$products = wc_get_products($args);

			$sitepress->switch_lang($current_lang);
			$icl_adjust_id_url_filter_off = $orig_flag_value;
		}else{
			$products = wc_get_products($args);
		}

		return $products;
	}
}

endif;