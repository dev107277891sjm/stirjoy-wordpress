<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( ! class_exists( 'WWP_Wholesale_Import' ) ) {

	class WWP_Wholesale_Import {

		public function __construct() {

			add_filter( 'woocommerce_product_export_column_names', array( $this, 'add_export_column' ) );
			add_filter( 'woocommerce_product_export_product_default_columns', array( $this, 'add_export_column' ) );
			add_filter( 'woocommerce_product_import_pre_insert_product_object', array( $this, 'process_import' ), 10, 2 );
			add_filter( 'woocommerce_csv_product_import_mapping_default_columns', array( $this, 'add_column_to_mapping_screen' ) );
			add_filter( 'woocommerce_csv_product_import_mapping_options', array( $this, 'add_column_to_importer' ) );
		}

		public function add_column_to_importer( $options ) {

			// column slug => column name

			$allterms = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
			foreach ( $allterms as $wholesale_user_roles ) {
				$options[ $wholesale_user_roles->slug . '_type' ]     = $wholesale_user_roles->slug . ':wholesale_type';
				$options[ $wholesale_user_roles->slug ]               = $wholesale_user_roles->slug . ':wholesale_price';
				$options[ $wholesale_user_roles->slug . '_quantity' ] = $wholesale_user_roles->slug . ':wholesale_quantity';
				// $options['custom_column'] = 'Custom Column';
			}
			return $options;
		}

		public function add_column_to_mapping_screen( $columns ) {

			// potential column name => column slug

			$allterms = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
			foreach ( $allterms as $wholesale_user_roles ) {
				$columns[ $wholesale_user_roles->slug . ':wholesale_type' ]     = $wholesale_user_roles->slug . '_type';
				$columns[ $wholesale_user_roles->slug . ':wholesale_price' ]    = $wholesale_user_roles->slug;
				$columns[ $wholesale_user_roles->slug . ':wholesale_quantity' ] = $wholesale_user_roles->slug . '_quantity';

			}

			return $columns;
		}

		public function process_import( $product, $data ) {
			$vary     = array();
			$p_data   = array();
			$amount   = '';
			$qty      = '';
			$allterms = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
			foreach ( $allterms as $wholesale_user_roles ) {
				$variation_id = $product->get_id();
				$slug         = $wholesale_user_roles->slug;
				$role_id      = $wholesale_user_roles->term_id;

				if ( isset( $data[ $wholesale_user_roles->slug ] ) && ! empty( $data[ $wholesale_user_roles->slug ] ) ) {

					$amount = $data[ $wholesale_user_roles->slug ];
					$type   = $data[ $wholesale_user_roles->slug . '_type' ];
					$qty    = $data[ $wholesale_user_roles->slug . '_quantity' ];

					if ( empty( $data[ $wholesale_user_roles->slug . '_type' ] ) ) {
						$type = 'percent';
					}
					if ( empty( $data[ $wholesale_user_roles->slug . '_quantity' ] ) ) {
						$qty = 1;
					}

					if ( 'simple' == $product->get_type() || 'bundle' == $product->get_type() ) {
						$p_data[ $role_id ] = array(
							'slug'            => $wholesale_user_roles->slug,
							'discount_type'   => $type,
							'wholesale_price' => $amount,
							'min_quatity'     => $qty,
						);

						if ( ! empty( get_post_meta( $product->get_parent_id(), 'wholesale_multi_user_pricing', true ) ) ) {
							$previous = get_post_meta( $product->get_parent_id(), 'wholesale_multi_user_pricing', true );
							$p_data   = array_merge( $p_data, $previous );
							$product->update_meta_data( 'wholesale_multi_user_pricing', $p_data );
						} else {
							$product->update_meta_data( 'wholesale_multi_user_pricing', $p_data );
						}
					} else {

						if ( ! empty( get_post_meta( $product->get_parent_id(), 'wholesale_multi_user_pricing', true ) ) ) {
							$p_data = get_post_meta( $product->get_parent_id(), 'wholesale_multi_user_pricing', true );
						}

						$vary[ $role_id ]['slug']                            = $wholesale_user_roles->slug;
						$vary[ $role_id ]['discount_type']                   = $type;
						$vary[ $role_id ][ $variation_id ]['wholesaleprice'] = $amount;
						$vary[ $role_id ][ $variation_id ]['qty']            = $qty;

						$p_data[ $role_id ]['slug']                            = $wholesale_user_roles->slug;
						$p_data[ $role_id ]['discount_type']                   = $type;
						$p_data[ $role_id ][ $variation_id ]['wholesaleprice'] = $amount;
						$p_data[ $role_id ][ $variation_id ]['qty']            = $qty;

						$product->update_meta_data( 'wholesale_multi_user_pricing', $vary );
						update_post_meta( $product->get_parent_id(), 'wholesale_multi_user_pricing', $p_data );
					}
					$product->save();
				}
			}
			return $product;
		}

		public function add_export_column( $columns ) {

			// column slug => column name
			$allterms = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
			foreach ( $allterms as $wholesale_user_roles ) {
				$columns[ $wholesale_user_roles->slug . '_type' ]     = $wholesale_user_roles->slug . ':wholesale_type';
				$columns[ $wholesale_user_roles->slug ]               = $wholesale_user_roles->slug . ':wholesale_price';
				$columns[ $wholesale_user_roles->slug . '_quantity' ] = $wholesale_user_roles->slug . ':wholesale_quantity';

				$slug = $wholesale_user_roles->slug;
				$role = $wholesale_user_roles->term_id;

				// wholesale type
				add_filter(
					'woocommerce_product_export_product_column_' . $wholesale_user_roles->slug . '_type',
					function ( $value, $product ) use ( $slug, $role ) {

						$data = $product->get_meta( 'wholesale_multi_user_pricing', true, 'edit' );
						if ( isset( $data[ $role ]['discount_type'] ) ) {
								return $data[ $role ]['discount_type'];
						}
					},
					10,
					2
				);

				// wholesale price
				add_filter(
					'woocommerce_product_export_product_column_' . $wholesale_user_roles->slug,
					function ( $value, $product ) use ( $slug, $role ) {

						$data = $product->get_meta( 'wholesale_multi_user_pricing', true, 'edit' );

						if ( 'simple' == $product->get_type() || 'bundle' == $product->get_type() ) {
							if ( isset( $data[ $role ] ) ) {
								$my = $data[ $role ];
								return $my['wholesale_price'];
							}
						} elseif ( isset( $data[ $role ][ $product->post->ID ]['wholesaleprice'] ) ) {
							return $data[ $role ][ $product->post->ID ]['wholesaleprice'];
						}
					},
					10,
					2
				);

				// wholesale quantity
				add_filter(
					'woocommerce_product_export_product_column_' . $wholesale_user_roles->slug . '_quantity',
					function ( $value, $product ) use ( $slug, $role ) {

						$data = $product->get_meta( 'wholesale_multi_user_pricing', true, 'edit' );

						if ( 'simple' == $product->get_type() || 'bundle' == $product->get_type() ) {
							if ( isset( $data[ $role ] ) ) {
								return $data[ $role ]['min_quatity'];
							}
						} elseif ( isset( $data[ $role ][ $product->post->ID ]['qty'] ) ) {
							return $data[ $role ][ $product->post->ID ]['qty'];
						}
					},
					10,
					2
				);
			}

			return $columns;
		}
	}
	new WWP_Wholesale_Import();
}
