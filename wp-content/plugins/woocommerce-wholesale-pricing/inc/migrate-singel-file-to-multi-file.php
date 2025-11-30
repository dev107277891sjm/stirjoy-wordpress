<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( ! class_exists( 'Migrate_Singel_File_To_Multi_File' ) ) {

	class Migrate_Singel_File_To_Multi_File {

		public $settings = array();

		public function __construct() {
			add_action( 'admin_init', array( $this, 'action_woocommerce_loaded' ), 200, 1 );
		}

		public function action_woocommerce_loaded( $array ) {

			$settings = get_option( 'wwp_wholesale_pricing_options', true );

			if ( isset( $settings['wholesale_role'] ) && 'multiple' == $settings['wholesale_role'] ) {
				return;
			} elseif ( isset( $settings['wholesale_role'] ) && 'single' == $settings['wholesale_role'] ) {

				$wholesale_multi_user_pricing = get_option( 'wholesale_multi_user_pricing', true );
				$user_role                    = 'default_wholesaler';
				$wholesale_role               = term_exists( $user_role, 'wholesale_user_roles' );
				$data                         = array();
				if ( 0 !== $wholesale_role && null !== $wholesale_role ) {
					if ( is_array( $wholesale_role ) && isset( $wholesale_role['term_id'] ) ) {

						// Global single settings meta shifting to global multi settings
						if ( 'yes' == get_option( '_wwp_enable_wholesale_item' ) && '' != get_option( '_wwp_wholesale_amount' ) ) {
							$role_id = $wholesale_role['term_id'];

							$data[ $role_id ] = array(
								'slug'            => 'default_wholesaler',
								'discount_type'   => get_option( '_wwp_wholesale_type' ),
								'wholesale_price' => get_option( '_wwp_wholesale_amount' ),
								'min_quatity'     => get_option( '_wwp_wholesale_min_quantity' ),
							);
							update_option( 'wholesale_multi_user_pricing', $data );
						}

						$product_categories = get_terms(
							array(
								'taxonomy'   => 'product_cat',
								'hide_empty' => false,
							)
						);
						foreach ( $product_categories as $term ) {

							$visibility = get_term_meta( $term->term_id, '_wwp_wholesale_product_visibility', true );
							if ( 'yes' == $visibility ) {
								update_term_meta( $term->term_id, 'wholesale_product_visibility_multi', array( 'default_wholesaler' ) );
							}
							$enable = get_term_meta( $term->term_id, '_wwp_enable_wholesale_item', true );
							$amount = get_term_meta( $term->term_id, '_wwp_wholesale_amount', true );
							$type   = get_term_meta( $term->term_id, '_wwp_wholesale_type', true );
							$qty    = get_term_meta( $term->term_id, '_wwp_wholesale_min_quantity', true );

							if ( 'yes' == $enable && '' != $amount ) {
								$role_id          = get_wholesale_role_id( 'default_wholesaler' );
								$data             = array();
								$data[ $role_id ] = array(
									'slug'            => 'default_wholesaler',
									'discount_type'   => $type,
									'wholesale_price' => $amount,
									'min_quatity'     => $qty,
								);
								update_term_meta( $term->term_id, 'wholesale_multi_user_pricing', $data );
							}
						}

						$all_ids = get_posts(
							array(
								'post_type'        => array( 'product' ),
								'numberposts'      => -1,
								'suppress_filters' => false,
								'post_status'      => 'publish',
								'fields'           => 'ids',
							)
						);
						$role_id = get_wholesale_role_id( 'default_wholesaler' );
						foreach ( $all_ids as $product_ID ) {
							$data      = array();
							$product   = new WC_Product_Variable( $product_ID );
							$variables = $product->get_available_variations();

							if ( ! empty( $variables ) ) {
								foreach ( $variables as $variable ) {
									$vary         = array();
									$variation_id = $variable['variation_id'];

									$enable = get_post_meta( $product_ID, '_wwp_enable_wholesale_item', true );
									$amount = get_post_meta( $variation_id, '_wwp_wholesale_amount', true );
									$qty    = get_post_meta( $variation_id, '_wwp_wholesale_min_quantity', true );
									$type   = get_post_meta( $product_ID, '_wwp_wholesale_type', true );

									if ( 'yes' == $enable && '' != $amount ) {

										$vary[ $role_id ]['slug']                            = 'default_wholesaler';
										$vary[ $role_id ]['discount_type']                   = $type;
										$vary[ $role_id ][ $variation_id ]['wholesaleprice'] = $amount;
										$vary[ $role_id ][ $variation_id ]['qty']            = $qty;

										$data[ $role_id ]['slug']                            = 'default_wholesaler';
										$data[ $role_id ]['discount_type']                   = $type;
										$data[ $role_id ][ $variation_id ]['wholesaleprice'] = $amount;
										$data[ $role_id ][ $variation_id ]['qty']            = $qty;

										update_post_meta( $variation_id, 'wholesale_multi_user_pricing', $vary );
									}
								}
								update_post_meta( $product_ID, 'wholesale_multi_user_pricing', $data );
							} else {
								$amount = get_post_meta( $product_ID, '_wwp_wholesale_amount', true );
								$enable = get_post_meta( $product_ID, '_wwp_enable_wholesale_item', true );
								if ( 'yes' == $enable && '' != $amount ) {
									$qty  = get_post_meta( $product_ID, '_wwp_wholesale_min_quantity', true );
									$type = get_post_meta( $product_ID, '_wwp_wholesale_type', true );

									// simple product
									$data[ $role_id ] = array(
										'slug'            => 'default_wholesaler',
										'discount_type'   => $type,
										'wholesale_price' => $amount,
										'min_quatity'     => $qty,
									);
									update_post_meta( $product_ID, 'wholesale_multi_user_pricing', $data );
								}
							}
							if ( 'yes' == get_post_meta( $product_ID, '_wwp_wholesale_product_visibility', true ) ) {
								update_post_meta( $product_ID, 'wholesale_product_visibility_multi', array( 'default_wholesaler' ) );
							}
						}
					}
				}
			}
			update_option( 'migrate_singel_file_to_multi_file', 'done' );
		}
	}
	new Migrate_Singel_File_To_Multi_File();
}
