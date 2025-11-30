<?php

if (!defined('ABSPATH')) {
	exit;
}

class Elex_Restrict_Logic {

	public function __construct() {
		add_action('woocommerce_check_cart_items', array($this, 'elex_wccr_check_cart_quantities'));
	}


	public function my_custom_checkout_field_process() {
		remove_action(
			'woocommerce_proceed_to_checkout',
			'woocommerce_button_proceed_to_checkout',
			20
		);
		echo '<a href="#" class="checkout-button button alt wc-forward">
		Proceed to checkout </a>';
	}

	public function elex_cart_products() {
		// Get the WooCommerce cart
		global $woocommerce;
		$cart = WC()->cart;
		$current_product_id = array();
		$current_product_category = array();
		// Check if the cart is empty
		if ($cart->is_empty()) {
			$current_product_id = array('');
			echo 'Cart is empty.';
		} else {
			$product_id = array();
			$product_categories = array();
			// Loop through each item in the cart
			foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
				// Get the product ID
				$product_id[] = $cart_item['product_id'];
			}
			$current_product_id = $product_id;
		}
		return $current_product_id;
	}

	public function get_product_categories( $product_ids) {
		$category_ids = array();

		// Loop through each product ID
		foreach ($product_ids as $product_id) {
			// Get the categories for the current product ID
			$product_categories = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));

			// Merge the category IDs into the main category IDs array
			$category_ids = array_merge($category_ids, $product_categories);
		}

		// Remove duplicate category IDs
		$category_ids = array_unique($category_ids);

		return $category_ids;
	}


	public function elex_particaul_product_total( $product_id) {

		$stored_product_id = $product_id;
		// Get the WooCommerce cart
		$cart = WC()->cart;

		// Initialize total price variable
		$total_price = 0;

		// Loop through each item in the cart
		foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
			// Check if the product ID matches the stored product ID
			if ($cart_item['product_id'] == $stored_product_id) {
				// Calculate the total price of the specific product
				$total_price += $cart_item['quantity'] * $cart_item['data']->get_price();
			}
		}
		return $total_price;
	}

	public function elex_wccr_check_cart_quantities() {
		global $woocommerce;
		$restrictions = get_option('elex_wccr_checkout_restriction_settings');
		if (is_user_logged_in()) {
			$user_role = wp_get_current_user()->roles[0];
			$user_id = get_current_user_id();
		} else {
			$user_role = 'unregistered_user';
		}
		
		$restrict_msg = '';
		/**
		 * Filter the conversion rate used in the calculation.
		 *
		 * This filter allows modifying the conversion rate used in some calculation.
		 *
		 * @since 2.0.0
		 * @param float $conversion_rate The conversion rate. Default is 1.
		 * @return float The modified conversion rate.
		 */
		$conversion_rate = apply_filters('elex_wccr_conversion_rate', 1);
		
		$current_product_ids = $this->elex_cart_products();
		
		$current_product_category = $this->get_product_categories($current_product_ids);
		
		if (is_array($restrictions)) {
			foreach ($restrictions as $restrict_no => $restriction) {
				
				$restrict_checkout = false;
				$rule_restriction = array();
				$rule_restriction['roles'] = false;
				$rule_restriction['users'] = false;
				$rule_restriction['category'] = false;
				$rule_restriction['product'] = false;
				// role
				if (isset($restriction['roles']) && !empty($restriction['roles'])) {
					if (in_array($user_role, $restriction['roles'])) {
						$rule_restriction['roles'] = true;
					}
				} else {
					$rule_restriction['roles'] = true;
				}

				// user
				if (isset($restriction['users']) && !empty($restriction['users'])) {
					if (in_array($user_id, $restriction['users'])) {
						$rule_restriction['users'] = true;
					}
				} else {
					$rule_restriction['users'] = true;
				}
				// product
				$product_under_rule = array();
				$products_total_according_rule = 0;
				if (isset($restriction['product']) && !empty($restriction['product'])) {
					
					if (!empty(array_intersect($current_product_ids, $restriction['product']))) {
						// At least one value matches
						$product_under_rule = array_intersect($current_product_ids, $restriction['product']);
						
						$current_product_ids = array_diff($current_product_ids, $restriction['product']);
						
					
						foreach ( $product_under_rule as $product_key => $product_id ) {
							$product = wc_get_product($product_id);
							// Get the product categories
							$product_categories[] = $product->get_category_ids();
							if (isset($restriction['category']) && !empty($restriction['category'])) {
								foreach ( $product_categories as $category_key => $product_category ) {
									if (!empty(array_intersect($product_category, $restriction['category']))) {
										$products_total_according_rule += $this->elex_particaul_product_total($product_id);
										break;
									}
								}
								
							} else {
								$products_total_according_rule += $this->elex_particaul_product_total($product_id);
							}
						}
						
						$rule_restriction['product'] = true;
						$rule_restriction['category'] = true;
					} 
				} elseif (isset($restriction['category']) && !empty($restriction['category'])) {
					
					$rule_restriction['product'] = true;
					if (!empty($current_product_ids)) {
						foreach ($current_product_ids as $product_key => $product_id) {
							$product = wc_get_product($product_id);
							$product_categories=[];
							// Get the product categories
							if (!empty($product)) {
								$product_categories[] = $product->get_category_ids();
								
								foreach ( $product_categories as $category_key => $product_category ) {
									
									if (!empty(array_intersect($product_category, $restriction['category']))) {
										$products_total_according_rule += $this->elex_particaul_product_total($product_id);
										$current_product_ids = array_diff($current_product_ids, array($product_id));
										$rule_restriction['category'] = true;
										break;
									}
								}
							}
						}
					}
					
				} else {
					$rule_restriction['product'] = true;
					$rule_restriction['category'] = true;
					if ( $rule_restriction['roles'] || $rule_restriction['users']) {
						$current_product_ids = '';
					}
				}

				
				if ( 0 != $products_total_according_rule ) {
					$specific_products_total = $products_total_according_rule;
					
				}

				

				$min_price = ( isset($restriction['min_price']) && !empty($restriction['min_price']) ) ? $restriction['min_price'] * $conversion_rate : '';
				$max_price = ( isset($restriction['max_price']) && !empty($restriction['max_price']) ) ? $restriction['max_price'] * $conversion_rate : '';
				if ( ! WC()->cart->is_empty() ) {
					if (( isset($restriction['min_price'])||isset($restriction['max_price']) ) && isset($restriction['error_message'])) {
						if (is_array($restriction) && $rule_restriction['roles'] && $rule_restriction['users'] && $rule_restriction['category'] && $rule_restriction['product'] && isset($restriction['enable_restriction'])) {
							
							// if condition for min value restriction
							if (isset($restriction['product']) || isset($restriction['category'])) {
								if (isset($specific_products_total) && !empty($specific_products_total)) {
									if ($min_price && $min_price > $specific_products_total) {
										$restrict_checkout = true;
										$restrict_msg = $restriction['error_message'];
										// break;
									}
								}
							} else {
								if ($min_price && ( $min_price > $woocommerce->cart->subtotal )) {
									$restrict_checkout = true;
									$restrict_msg = $restriction['error_message'];
									// break;
								}
							}
		
							// if condition for max value restriction
							if (isset($restriction['product']) || isset($restriction['category'])) {
								if (isset($specific_products_total) && !empty($specific_products_total)) {
									if (!$restrict_checkout && $max_price && $max_price < $specific_products_total) {
										$restrict_checkout = true;
										$restrict_msg = $restriction['error_message'];
										// break;
									} 
								}
							} else {
								if (!$restrict_checkout && $max_price && ( $max_price < $woocommerce->cart->subtotal )) {
									$restrict_checkout = true;
									$restrict_msg = $restriction['error_message'];
									// break;
								}
							}
							if ($restrict_checkout) {
								add_action('woocommerce_proceed_to_checkout', array($this, 'my_custom_checkout_field_process'));
								wc_add_notice(html_entity_decode($restrict_msg), 'error');
							}
							if (empty($current_product_ids)) {
								break;
							}
						}
					}
				}
				
			}
		}
	}
}
new Elex_Restrict_Logic();
