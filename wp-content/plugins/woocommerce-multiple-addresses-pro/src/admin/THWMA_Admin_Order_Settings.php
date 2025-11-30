<?php
/**
 * The admin order page functionality of the plugin
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-multiple-addresses-pro
 * @subpackage woocommerce-multiple-addresses-pro/src/admin
 */

namespace Themehigh\WoocommerceMultipleAddressesPro\admin;

use Themehigh\WoocommerceMultipleAddressesPro\admin\THWMA_Admin_Settings;
use Themehigh\WoocommerceMultipleAddressesPro\includes\utils\THWMA_Utils;

if(!defined('WPINC')) {
	die;
}

if(!class_exists('THWMA_Admin_Order_Settings')) :

	/**
     * The Admin order settings class.
     */
	class THWMA_Admin_Order_Settings extends THWMA_Admin_Settings {
		public $email_delivery_date;
		public $product_name;
		public $product_qty;
		public $product_price;
		public $delivery_time;
		protected $item_id;

		public function __construct() {
			$this->define_order_page_hook();
			$this->email_delivery_date ='';
			$this->product_name = '';
			$this->product_qty = '';
			$this->product_price = '';
			$this->delivery_time = '';
		}

		/**
	   	* Function for define order page hook.
	   	*/
		public function define_order_page_hook() {
			add_action('woocommerce_admin_order_data_after_shipping_address', array($this, 'thwma_update_woo_order_status'),10,1);
			add_action('woocommerce_admin_order_preview_line_items', array($this, 'thwma_admin_order_preview_line_items'),10,2);
			add_action('woocommerce_after_order_itemmeta', array($this, 'thwma_admin_item_shipping_status'), 10, 2 );
			add_action('woocommerce_before_save_order_item', array($this, 'thwma_admin_item_shipping_status_update'));

			add_filter('woocommerce_hidden_order_itemmeta', array($this, 'thwma_order_hidden_order_itemmeta'));
			add_filter('woocommerce_order_item_display_meta_key', array($this, 'thwma_change_display_meta_key'));
			add_filter('woocommerce_order_item_display_meta_value', array($this, 'thwma_chage_dispay_meta_value'),10, 2);
		}

		/**
	   	* Function for displaying order status.
	   	*/
		function thwma_admin_item_shipping_status ($item_id, $item) {
			$order = wc_get_order();
			
			$items_count =  $order &&  is_object($order) && method_exists($order, 'get_items') ? count( $order->get_items() ) : 0;
			$items_count = isset($items_count) ? $items_count : ''; 
			
			if($items_count >= 2){
				$settings = THWMA_Utils::get_setting_value('settings_multiple_shipping');
				if( 'yes' === $settings['enable_cart_shipping']) {
					$shipping_status = wc_get_order_item_meta($item_id, THWMA_Utils::ORDER_KEY_SHIPPING_STATUS);
					echo "<select id='order_status_".$item_id."' name='order_status_".$item_id."' class='wc-enhanced-select thwma-order-status'>";
					$statuses = wc_get_order_statuses();
					foreach ( $statuses as $status => $status_name ) {
						echo '<option value="' . esc_attr( $status ) . '" ' . selected( $status, $shipping_status, false ) . '>' . esc_html( $status_name ) . '</option>';
					}
					echo "</select>";
				}
			}
		}

		/**
	   	* Function for hidding itemmeta in edit order page.
	   	*/
		function thwma_order_hidden_order_itemmeta ($itemmeta) {
		 	$itemmeta[] = "thwma_order_shipping_status";
		 	$itemmeta[] = "_thwma_suborders";
		 	$itemmeta[] = "_thwma_suborder";
		 	return $itemmeta;
		}

		/**
	   	* Function for change formatted meta key.
	   	*/
		function thwma_change_display_meta_key($display_key){
		 	if('thwma_order_shipping_status' === $display_key){
		 		$display_key = esc_html__('Shipping status', 'woocommerce-multiple-addresses-pro');
		 	}
		 	return $display_key;
		 }

		/**
	   	* Function for change formatted meta value.
	   	*/
		function thwma_chage_dispay_meta_value ($display_value, $meta){
			$display_value = isset($display_value) ? wc_get_order_status_name( $display_value ) : "";
			return $display_value;
		}

		/**
	   	* Function for updating order status.
	   	*/
		function thwma_admin_item_shipping_status_update ($item) {
			$item_id = $item->get_id();
			if($item_id && isset($_POST['order_status_'.$item_id])){
				$item_status = $_POST['order_status_'.$item_id];
				$item_status = sanitize_text_field($item_status);
				$item->update_meta_data( THWMA_Utils::ORDER_KEY_SHIPPING_STATUS, $item_status, $item_id);
			}
		}

		/**
	   * Function for update woocommerce order status.
	   *
	   * @param array $order The order details
	   */
		function thwma_update_woo_order_status($order) {
			$settings = THWMA_Utils::get_setting_value('settings_multiple_shipping');
			$cart_shipping_enabled = isset($settings['enable_cart_shipping']) ? $settings['enable_cart_shipping']:'';

			$user_id = get_current_user_id();

			if($cart_shipping_enabled == 'yes') {

		        // Check multi-shipping is enable on the specific order.
		        $enable_multi_ship_data = '';
		        $item_meta_data = '';
		        $order_items = $order->get_items();

		        if(is_array($order_items) && !empty($order_items)){

					foreach($order_items as $item ){
						$item_meta_data = $item->get_meta_data();
					}

					if(is_array($item_meta_data) && !empty($item_meta_data)){

						foreach( $item_meta_data as $key => $data){
							if('thwma_order_shipping_address' === $data->key || 'thwma_order_shipping_data' === $data->key){
								$enable_multi_ship_data = true;
							}
						}
					}
				}

		        // if($enable_multi_ship_data == 'yes' || $enable_multi_ship_data == true) {
		        if($enable_multi_ship_data) {
					echo '<input type="hidden" name="multi_ship_enabled" value="yes" class="multi_ship_enabled">';
				} else {
					echo '<input type="hidden" name="multi_ship_enabled" value="" class="multi_ship_enabled">';
				}

			} else {
				echo '<input type="hidden" name="multi_ship_enabled" value="" class="multi_ship_enabled">';
			}
		}

		function thwma_admin_order_preview_line_items($order_items, $order) {
			return $order_items;
		}
	}
endif;
