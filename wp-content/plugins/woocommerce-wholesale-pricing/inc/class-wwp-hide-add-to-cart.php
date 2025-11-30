<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Wwp_Hide_Add_To_Cart_Buttons' ) ) {

	class Wwp_Hide_Add_To_Cart_Buttons {

		public function __construct() {
			add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'conditionally_hide_add_to_cart_button_shop' ), 10, 3 );
			add_action( 'woocommerce_single_product_summary', array( $this, 'conditionally_hide_add_to_cart_button' ), 10 );
			add_action( 'woocommerce_variable_add_to_cart', array( $this, 'conditionally_hide_add_to_cart_button' ), 10 );
			add_filter( 'woocommerce_is_purchasable', array( $this, 'preorder_is_purchasable' ), 10, 2);
		}

		public function preorder_is_purchasable( $is_purchasable, $product ) { 
			
			$settings = get_option( 'wwp_wholesale_pricing_options', true );
			/** 
			 * Filter Hook: wwp_is_purchasable_hide
			 * 
			 * @since 2.7
			 * 
			 * Type: Filter
			 */
			$pass = apply_filters( 'wwp_is_purchasable_hide', false, $is_purchasable );
			if ( $pass ) {
				return $is_purchasable;
			}
			if ( isset( $settings['wwp_hide_add_to_cart'] ) && 'yes' === $settings['wwp_hide_add_to_cart'] ) {

				$hide_on = isset( $settings['wwp_hide_add_to_cart_on'] ) ? $settings['wwp_hide_add_to_cart_on'] : '';
				if ( 'specific_product' === $hide_on ) {
					$specific_products = isset( $settings['wwp_specific_product'] ) ? $settings['wwp_specific_product'] : array();

					if ( in_array( $product->get_id(), $specific_products ) ) {
						add_action( 'woocommerce_after_shop_loop_item', array( $this, 'display_custom_message' ), 20 );
						return false;
					}
				} elseif ( 'specific_product_cat' === $hide_on ) {
					$specific_categories = isset( $settings['wwp_specific_cat'] ) ? $settings['wwp_specific_cat'] : array();
					
					$product_categories = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'ids' ) );
					
					if ( ! empty( array_intersect( $product_categories, $specific_categories ) ) ) {
						add_action( 'woocommerce_after_shop_loop_item', array( $this, 'display_custom_message' ), 20 );
						return false;
					}
				} elseif ( 'specific_user_roles' === $hide_on ) {
					$specific_user_roles = isset( $settings['wwp_specific_user_role'] ) ? $settings['wwp_specific_user_role'] : array();

					if ( is_user_logged_in() ) {
						$current_user = wp_get_current_user();
						$user_roles = $current_user->roles;
						if ( ! empty( array_intersect( array_map( 'strtolower', $user_roles ), array_map( 'strtolower', $specific_user_roles ) ) ) ) {
							add_action( 'woocommerce_after_shop_loop_item', array( $this, 'display_custom_message' ), 20 );
							return false;
						}
					}
				}
			}

			return $is_purchasable;
		}

		public function conditionally_hide_add_to_cart_button_shop( $class, $product, $args = array() ) {
			$settings = get_option( 'wwp_wholesale_pricing_options', true );
			/** 
			 * Filter Hook: wwp_hide_cart_button_on_shop
			 * 
			 * @since 2.7
			 * 
			 * Type: Filter
			 */
			$pass = apply_filters( 'wwp_hide_cart_button_on_shop', false, $class, $product );
			if ( $pass ) {
				return $class;
			}

			if ( isset( $settings['wwp_hide_add_to_cart'] ) && 'yes' === $settings['wwp_hide_add_to_cart'] ) {

				$hide_on = isset( $settings['wwp_hide_add_to_cart_on'] ) ? $settings['wwp_hide_add_to_cart_on'] : '';
				if ( 'specific_product' === $hide_on ) {
					$specific_products = isset( $settings['wwp_specific_product'] ) ? $settings['wwp_specific_product'] : array();

					if ( in_array( $product->get_id(), $specific_products ) ) {
						add_action( 'woocommerce_after_shop_loop_item', array( $this, 'display_custom_message' ), 20 );
						return false;
					}
				} elseif ( 'specific_product_cat' === $hide_on ) {
					$specific_categories = isset( $settings['wwp_specific_cat'] ) ? $settings['wwp_specific_cat'] : array();
					
					$product_categories = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'ids' ) );
					
					if ( ! empty( array_intersect( $product_categories, $specific_categories ) ) ) {
						add_action( 'woocommerce_after_shop_loop_item', array( $this, 'display_custom_message' ), 20 );
						return false;
					}
				} elseif ( 'specific_user_roles' === $hide_on ) {
					$specific_user_roles = isset( $settings['wwp_specific_user_role'] ) ? $settings['wwp_specific_user_role'] : array();

					if ( is_user_logged_in() ) {
						$current_user = wp_get_current_user();
						$user_roles = $current_user->roles;
						if ( ! empty( array_intersect( array_map( 'strtolower', $user_roles ), array_map( 'strtolower', $specific_user_roles ) ) ) ) {
							add_action( 'woocommerce_after_shop_loop_item', array( $this, 'display_custom_message' ), 20 );
							return false;
						}
					}
				}
			}

			return $class;
		}

		public function conditionally_hide_add_to_cart_button() {

			global $product;
			
			if ( ! $product ) {
				return;
			}

			$settings =  get_option( 'wwp_wholesale_pricing_options', true );

			/** 
			 * Filter Hook: wwp_hide_cart_button_on_single_page
			 * 
			 * @since 2.7
			 * 
			 * Type: Filter
			 */
			$pass = apply_filters( 'wwp_hide_cart_button_on_single_page', false, $product );
			if ( $pass ) {
				return;
			}

			if ( isset( $settings['wwp_hide_add_to_cart'] ) && 'yes' === $settings['wwp_hide_add_to_cart'] ) {
				$hide_on = isset( $settings['wwp_hide_add_to_cart_on'] ) ? $settings['wwp_hide_add_to_cart_on'] : '';
				if ( 'specific_product' === $hide_on ) {
					$specific_products = isset( $settings['wwp_specific_product'] ) ? $settings['wwp_specific_product'] : array();

					if ( in_array( $product->get_id(), $specific_products ) ) {

						if ( 'variable' === $product->get_type() ) {
							remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
							add_action( 'woocommerce_single_variation', array( $this, 'display_custom_message' ), 30 );
						} else {
							remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
							remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );

							add_action( 'woocommerce_single_product_summary', array( $this, 'display_custom_message' ), 31 );
						}
					}
				} elseif ( 'specific_product_cat' === $hide_on ) {
					$specific_categories = isset( $settings['wwp_specific_cat'] ) ? $settings['wwp_specific_cat'] : array();

					$product_categories = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'ids' ) );

					if ( ! empty( array_intersect( $product_categories, $specific_categories ) ) ) {
						if ( 'variable' === $product->get_type() ) {
							remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
							add_action( 'woocommerce_single_variation', array( $this, 'display_custom_message' ), 30 );
						} else {
							remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
							remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
							add_action( 'woocommerce_single_product_summary', array( $this, 'display_custom_message' ), 31 );
						}
					}
				} elseif ( 'specific_user_roles' === $hide_on ) {
					$specific_user_roles = isset( $settings['wwp_specific_user_role'] ) ? $settings['wwp_specific_user_role'] : array();
					
					if ( is_user_logged_in() ) {
						$current_user = wp_get_current_user();
						$user_roles = $current_user->roles;
						if ( ! empty( array_intersect( array_map( 'strtolower', $user_roles ), array_map( 'strtolower', $specific_user_roles ) ) ) ) {
							if ( 'variable' === $product->get_type() ) {
								remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
								add_action( 'woocommerce_single_variation', array( $this, 'display_custom_message' ), 30 );
							} else {
								remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
								remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
								add_action( 'woocommerce_single_product_summary', array( $this, 'display_custom_message' ), 31 );
							}
						}
					}
				}
			}
		}

		public function display_custom_message() {
			global $product;
		
			if ( ! $product ) {
				return;
			}

			$settings = get_option( 'wwp_wholesale_pricing_options', true );

			if ( isset( $settings['wwp_hide_add_to_cart'] ) && 'yes' === $settings['wwp_hide_add_to_cart'] ) {
				$hide_on = isset( $settings['wwp_hide_add_to_cart_on'] ) ? $settings['wwp_hide_add_to_cart_on'] : '';
				
				if ( 'specific_product' === $hide_on ) {
					$specific_products = isset( $settings['wwp_specific_product'] ) ? $settings['wwp_specific_product'] : array();

					if ( in_array( $product->get_id(), $specific_products ) ) {
						echo ! empty( $settings['custom_message_for_cart'] ) ? esc_html( $settings['custom_message_for_cart'] ) : '';
						return;
					}
				}

				if ( 'specific_product_cat' === $hide_on ) {
					$specific_categories = isset( $settings['wwp_specific_cat'] ) ? $settings['wwp_specific_cat'] : array();

					$product_categories = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'ids' ) );

					if ( ! empty( array_intersect( $product_categories, $specific_categories ) ) ) {
						echo ! empty( $settings['custom_message_for_cart'] ) ? esc_html( $settings['custom_message_for_cart'] ) : '';
						return;
					}
				}

				if ( 'specific_user_roles' === $hide_on ) {
					$specific_user_roles = isset( $settings['wwp_specific_user_role'] ) ? $settings['wwp_specific_user_role'] : array();

					if ( is_user_logged_in() ) {
						$current_user = wp_get_current_user();
						$user_roles = $current_user->roles;

						if ( ! empty( array_intersect( array_map( 'strtolower', $user_roles ), array_map( 'strtolower', $specific_user_roles ) ) ) ) {
							echo ! empty( $settings['custom_message_for_cart'] ) ? esc_html( $settings['custom_message_for_cart'] ) : '';
							return;
						}
					}
				}
			}
		}
	}

	$hide_add_to_cart_buttons = new Wwp_Hide_Add_To_Cart_Buttons();
}
