<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Wwp_Hide_Price' ) ) {

	class Wwp_Hide_Price {
		
		public function __construct() {
			add_filter( 'woocommerce_get_price_html', array( $this, 'conditionally_hide_price_shop' ), 210, 2 );
			add_filter( 'woocommerce_subscriptions_product_price_string', array( $this, 'conditionally_hide_price_for_sub_product' ), 210, 3 );
		}

		public function conditionally_hide_price_for_sub_product( $subscription_string, $product, $include ) {
			
			if ( $product->is_type('simple') || $product->is_type('variable') ) { 
				return $subscription_string;
			}

			$settings = get_option( 'wwp_wholesale_pricing_options', true );

			$product_id = $product->get_id();
			if ( $product->get_type() === 'variable-subscription' || $product->get_type() === 'subscription_variation' ) {
				$product_id = $product->get_parent_id();
			}

			/** 
			 * Filter Hook: wwp_conditionally_hide_subs_price
			 * 
			 * @since 2.7
			 * 
			 * Type: Filter
			 */
			$pass = apply_filters( 'wwp_conditionally_hide_subs_price', false, $subscription_string, $product_id, $product );
			if ( $pass ) {
				return $subscription_string;
			}
			if ( isset( $settings['wwp_hide_price'] ) && 'yes' === $settings['wwp_hide_price'] ) {
				$hide_on = isset( $settings['wwp_hide_price_on'] ) ? $settings['wwp_hide_price_on'] : '';
				if ( 'specific_product' === $hide_on ) {
					$specific_products = isset( $settings['wwp_specific_product_price'] ) ? $settings['wwp_specific_product_price'] : array();
					if ( in_array( $product_id, $specific_products ) ) {
						return ! empty( $settings['custom_message_for_price'] ) ? esc_html( $settings['custom_message_for_price'] ) : '';
					}
				} elseif ( 'specific_product_cat' === $hide_on ) {
					$specific_categories = isset( $settings['wwp_specific_cat_price'] ) ? $settings['wwp_specific_cat_price'] : array();
					
					$product_categories = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
					
					if ( ! empty( array_intersect( $product_categories, $specific_categories ) ) ) {
						return ! empty( $settings['custom_message_for_price'] ) ? esc_html( $settings['custom_message_for_price'] ) : '';
					}
				} elseif ( 'specific_user_roles' === $hide_on ) {
					$specific_user_roles = isset( $settings['wwp_specific_user_role_price'] ) ? $settings['wwp_specific_user_role_price'] : array();
					
					if ( is_user_logged_in() ) {
						$current_user = wp_get_current_user();
						$user_roles = $current_user->roles;
						if ( ! empty( array_intersect( array_map( 'strtolower', $user_roles ), array_map( 'strtolower', $specific_user_roles ) ) ) ) {
							return ! empty( $settings['custom_message_for_price'] ) ? esc_html( $settings['custom_message_for_price'] ) : '';
						}
					}
				}
			}

			return $subscription_string;
		}

		public function conditionally_hide_price_shop( $price, $product ) {
			
			if ( $product->is_type('subscription') || $product->is_type('subscription_variation') ) { 
				return $price;
			}
			$settings = get_option( 'wwp_wholesale_pricing_options', true );

			$product_id = $product->get_id();
			if ( $product->get_type() === 'variation' ) {
				$product_id = $product->get_parent_id();
			}

			/** 
			 * Filter Hook: wwp_conditionally_hide_price
			 * 
			 * @since 2.7
			 * 
			 * Type: Filter
			 */
			$pass = apply_filters( 'wwp_conditionally_hide_price', false, $price, $product_id, $product );
			if ( $pass ) {
				return $price;
			}
			if ( isset( $settings['wwp_hide_price'] ) && 'yes' === $settings['wwp_hide_price'] ) {
				$hide_on = isset( $settings['wwp_hide_price_on'] ) ? $settings['wwp_hide_price_on'] : '';
				if ( 'specific_product' === $hide_on ) {
					$specific_products = isset( $settings['wwp_specific_product_price'] ) ? $settings['wwp_specific_product_price'] : array();

					if ( in_array( $product_id, $specific_products ) ) {
						return ! empty( $settings['custom_message_for_price'] ) ? esc_html( $settings['custom_message_for_price'] ) : '';
					}
				} elseif ( 'specific_product_cat' === $hide_on ) {
					$specific_categories = isset( $settings['wwp_specific_cat_price'] ) ? $settings['wwp_specific_cat_price'] : array();
					
					$product_categories = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
					
					if ( ! empty( array_intersect( $product_categories, $specific_categories ) ) ) {
						return ! empty( $settings['custom_message_for_price'] ) ? esc_html( $settings['custom_message_for_price'] ) : '';
					}
				} elseif ( 'specific_user_roles' === $hide_on ) {
					$specific_user_roles = isset( $settings['wwp_specific_user_role_price'] ) ? $settings['wwp_specific_user_role_price'] : array();
					
					if ( is_user_logged_in() ) {
						$current_user = wp_get_current_user();
						$user_roles = $current_user->roles;
						if ( ! empty( array_intersect( array_map( 'strtolower', $user_roles ), array_map( 'strtolower', $specific_user_roles ) ) ) ) {
							return ! empty( $settings['custom_message_for_price'] ) ? esc_html( $settings['custom_message_for_price'] ) : '';
						}
					}
				}
			}

			return $price;
		}
	}

	$hide_price = new Wwp_Hide_Price();
}
