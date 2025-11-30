<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Class Woo_Wholesale_Bulk_Price
 */
if ( ! class_exists( 'WWP_Wholesale_Bulk_Price' ) ) {
	class WWP_Wholesale_Bulk_Price {
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'wwp_register_bulk_menu' ), 99 );
			add_action( 'wp_ajax_save_single_wholesale_product', array( $this, 'save_single_wholesale_product_callback' ) );
		}
		/**
		 * Register sub menu page
		 *
		 * @since   1.0
		 * @version 1.0
		 */
		public function wwp_register_bulk_menu() {
			/**
			* Hooks
			*
			* @since 3.0
			*/
			$bulk_pricing_menu_name = apply_filters( 'wwp_wholsale_Bulk_title', esc_html__( 'Bulk Wholesale Pricing', 'woocommerce-wholesale-pricing' ) );
			add_submenu_page( 'wwp_wholesale', $bulk_pricing_menu_name, $bulk_pricing_menu_name, 'manage_wholesale_bulk_ricing', 'wwp-bulk-wholesale-pricing', array( $this, 'wwp_bulk_wholesale_pricing_callback' ) );
		}
		/**
		 * Sub menu page callback
		 *
		 * @since   1.0
		 * @version 1.0
		 */
		public function wwp_bulk_wholesale_pricing_callback() {
			/**
			* Hooks
			*
			* @since 1.0
			*/
			$wwp_wholsale_Bulk_title = apply_filters( 'wwp_wholsale_Bulk_title', esc_html__( 'Bulk Wholesale Pricing', 'woocommerce-wholesale-pricing' ) );
			echo '<form action="#" id="wwp_bulk_form" method="post">';
			echo '<h2 class="text-center">' . esc_html__( $wwp_wholsale_Bulk_title ) . '</h2>';
			echo '<hr />';

			wp_nonce_field( 'wwp_bulk_wholesale_nonce', 'wwp_bulk_wholesale_nonce' );
			$this->wwp_bulk_update_options();

			$paged    = ( isset( $_GET['paged'] ) && wc_clean( $_GET['paged'] ) ) ? wc_clean( $_GET['paged'] ) : 1;
			$category = ( isset( $_GET['category'] ) && wc_clean( $_GET['category'] ) ) ? wc_clean( $_GET['category'] ) : '';
			$sku      = ( isset( $_GET['sku'] ) && wc_clean( $_GET['sku'] ) ) ? wc_clean( $_GET['sku'] ) : '';
			$post_per_page = 10;
			$offset        = ( $post_per_page * $paged ) - $post_per_page;
			$tax_query = array();
			if ( ! empty( $category ) ) {
				$tax_query[] = array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => array( $category ),
				);
			}

			$settings = get_option( 'wwp_wholesale_pricing_options', true );
			$roles    = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
			if ( ! isset( $settings['wholesale_role'] ) ) {
				$settings['tier_pricing'] = 'default_wholesaler';
			}
			if ( empty( $sku ) ) {
				$args = array(
					'post_type'      => 'product',
					'posts_per_page' => $post_per_page,
					'offset'         => $offset,
					'paged'          => $paged,
					'orderby'        => 'title',
					'order'          => 'ASC',
					'tax_query'      => $tax_query,
				);

				$the_query = new WP_Query( $args );
			} else {
				$args_title = array(
					'post_type'      => 'product',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					's'              => $sku,
					'tax_query'      => $tax_query,
				);
				$query_title = new WP_Query( $args_title );
				$ids_title = $query_title->posts;

				$args_sku = array(
					'post_type'      => 'product',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'meta_query'     => array(
						array(
							'key'     => '_sku',
							'value'   => $sku,
							'compare' => 'LIKE',
						),
					),
					'tax_query'      => $tax_query,
				);
				$query_sku = new WP_Query( $args_sku );
				$ids_sku = $query_sku->posts;
				$args_variations = array(
					'post_type'      => 'product_variation',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'meta_query'     => array(
						array(
							'key'     => '_sku',
							'value'   => $sku,
							'compare' => 'LIKE',
						),
					),
				);
				$variation_query = new WP_Query( $args_variations );
				$variation_ids = $variation_query->posts;

				$parent_ids = array();
				if ( ! empty( $variation_ids ) ) {
					foreach ( $variation_ids as $variation_id ) {
						$parent_id = wp_get_post_parent_id( $variation_id );
						if ( $parent_id ) {
							$parent_ids[] = $parent_id;
						}
					}
				}
				$parent_ids = array_unique( $parent_ids );
				$ids_sku = array_unique( array_merge( $ids_sku, $parent_ids ) );
				$all_ids = array_unique( array_merge( $ids_title, $ids_sku ) );

				if ( ! empty( $sku ) ) {
					$filtered_ids = array();
					$wholesaler_slug_search = $sku;
					$wholesale_products = new WP_Query( array(
						'post_type'      => 'product',
						'posts_per_page' => -1,
						'fields'         => 'ids',
						'meta_query'     => array(
							array(
								'key'     => 'wholesale_multi_user_pricing',
								'compare' => 'EXISTS',
							),
						),
					) );

					if ( $wholesale_products->have_posts() ) {
						foreach ( $wholesale_products->posts as $product_id ) {
							$wholesale_data = get_post_meta( $product_id, 'wholesale_multi_user_pricing', true );

							if ( is_array( $wholesale_data ) ) {
								foreach ( $wholesale_data as $role_id => $role_data ) {
									$role = get_term_by('id', $role_id, 'wholesale_user_roles');
									if ( ( isset( $role_data['slug'] ) && $role_data['slug'] == $wholesaler_slug_search ) || ( ! empty( $role->name ) && ! empty( $role->slug ) && isset( $role_data['slug'] ) && ucfirst( $role->name ) == ucfirst( $sku ) && $role->slug == $role_data['slug'] ) ) {
										$filtered_ids[] = $product_id;
										break;
									}
								}
							}
						}
					}

					$all_ids = array_unique( array_merge( $all_ids, $filtered_ids ) );
				}

				if ( empty( $all_ids ) ) {
					$the_query = new WP_Query( array( 'post__in' => array( 0 ) ) );
				} else {
					$args_final = array(
						'post_type'      => 'product',
						'posts_per_page' => $post_per_page,
						'paged'          => $paged,
						'post__in'       => $all_ids,
						'orderby'        => 'title',
						'order'          => 'ASC',
						'tax_query'      => $tax_query,
					);
					$the_query = new WP_Query( $args_final );
				}
			}

			if ( $the_query->have_posts() ) :
				while ( $the_query->have_posts() ) {
					$the_query->the_post();
					$product_ID = get_the_ID();
					$this->wwp_product_tab_content( $product_ID, $settings['wholesale_role'], $roles );
					$max_pages = $the_query->max_num_pages;
					$nextpage  = $paged + 1;
				}
			else :
				echo '<p>No products found.</p>';
			endif;

			wp_reset_postdata();

			echo '</form>';


			$cat = ( isset( $_GET['category'] ) && wc_clean( $_GET['category'] ) ) ? '&category=' . wc_clean( $_GET['category'] ) : '';
			$sku = ( isset( $_GET['sku'] ) && wc_clean( $_GET['sku'] ) ) ? '&sku=' . wc_clean( $_GET['sku'] ) : '';
			if ( @$max_pages > $paged ) {
				echo '<a class="wwp-btn-pagination" href="' . esc_url( admin_url( 'admin.php?page=wwp-bulk-wholesale-pricing' . esc_attr( $cat ) . esc_attr( $sku ) . '&paged=' . esc_attr( $nextpage ) ) ) . '">' . esc_html__( 'Next Page', 'woocommerce-wholesale-pricing' ) . '</a>';
			}

			$prevpage = max( ( $paged - 1 ), 0 ); // max() will discard any negative value
			if ( 0 !== $prevpage ) {
				echo '<a class="wwp-btn-pagination"  href="' . esc_url( admin_url( 'admin.php?page=wwp-bulk-wholesale-pricing' . esc_attr( $cat ) . esc_attr( $sku ) . '&paged=' . esc_attr( $prevpage ) ) ) . '">' . esc_html__( 'Previous Page', 'woocommerce-wholesale-pricing' ) . '</a>';
			}
		}

		public function wwp_update_variable_product( $post_id, $variable_id, $price ) {
			
			if ( ! isset( $_POST['wwp_bulk_wholesale_nonce'] ) || ( isset( $_POST['wwp_bulk_wholesale_nonce'] ) && ! wp_verify_nonce( wc_clean( $_POST['wwp_bulk_wholesale_nonce'] ), 'wwp_bulk_wholesale_nonce' ) ) ) {
				return;
			}
			
			if ( isset( $_POST['wwp_wholesale_type'] ) ) {
				update_post_meta( $post_id, '_wwp_wholesale_type', wc_clean( $_POST['wwp_wholesale_type'] ) );
			}
			update_post_meta( $post_id, '_wwp_enable_wholesale_item', 'yes' );
			update_post_meta( $variable_id, '_wwp_wholesale_amount', $price );
		}
		public function wwp_product_tab_content( $product_id, $role_type, $roles ) {
			$settings        = get_option( 'wwp_wholesale_pricing_options', true );
			$regular_price   = get_post_meta( $product_id, '_regular_price', true );
			$sale_price      = get_post_meta( $product_id, '_sale_price', true );
			$wholesale_type  = get_post_meta( $product_id, '_wwp_wholesale_type', true );
			$wholesale_price = get_post_meta( $product_id, '_wwp_wholesale_amount', true );
			$_product        = wc_get_product( $product_id );
			$tickets         = new WC_Product_Variable( $product_id );
			$variables       = $tickets->get_available_variations();
			$data            = get_post_meta( $product_id, 'wholesale_multi_user_pricing', true );
			$rolehtml        = '';
			if ( ! empty( $roles ) ) {
				$rolehtml .= '<table class="wholesale_pricing"  cellpadding="10">';
				$rolehtml .= '<tr>';
				$rolehtml .= '<th>' . esc_html__( 'Wholesale Role', 'woocommerce-wholesale-pricing' ) . '</th>';
				$rolehtml .= '<th>' . esc_html__( 'SKU', 'woocommerce-wholesale-pricing' ) . '</th>';
				$rolehtml .= '<th>' . esc_html__( 'Enable for Role', 'woocommerce-wholesale-pricing' ) . '</th>';
				$rolehtml .= '<th>' . esc_html__( 'Discount Type', 'woocommerce-wholesale-pricing' ) . '</th>';
				$rolehtml .= '<th>' . esc_html__( 'Wholesale Price', 'woocommerce-wholesale-pricing' ) . '</th>';
				$rolehtml .= '<th>' . esc_html__( 'Min Quantity', 'woocommerce-wholesale-pricing' ) . '</th>';
				$rolehtml .= '</tr>';
				foreach ( $roles as $key => $role ) {
						$min      = 1;
						$price    = $wholesale_price;
						$discount = $wholesale_type;
					if ( isset( $data[ $role->term_id ] ) ) {
						$min      = isset( $data[ $role->term_id ]['min_quatity'] ) ? $data[ $role->term_id ]['min_quatity'] : 1;
						$price    = isset( $data[ $role->term_id ]['wholesale_price'] ) ? $data[ $role->term_id ]['wholesale_price'] : '';
						$discount = isset( $data[ $role->term_id ]['discount_type'] ) ? $data[ $role->term_id ]['discount_type'] : '';
					}
					if ( isset( $settings['wholesale_role'] ) && 'single' == $settings['wholesale_role'] && 'default_wholesaler' != $role->slug ) {
						continue;
					}
					$rolehtml .= '<tr>';
					$rolehtml .= '<td>' . esc_html( $role->name ) . '</td>';
					$rolehtml .= '<td>' . esc_html( $_product->get_sku() ) . '</td>';
					$rolehtml .= '<td><input class="inp-cbx" style="display: none" id="role_' . esc_attr( $role->term_id ) . '_' . esc_attr( $product_id ) . '" type="checkbox" value="' . esc_attr( $role->slug ) . '" name="role_' . esc_attr( $role->term_id ) . '" ' . ( ( isset( $data[ $role->term_id ] ) ) ? 'checked' : '' ) . '>';
					$rolehtml .= '<label class="cbx cbx-square wosvg" for="role_' . esc_attr( $role->term_id ) . '_' . esc_attr( $product_id ) . '">';
					$rolehtml .= '<span><svg width="12px" height="9px" viewbox="0 0 12 9"><polyline points="1 5 4 8 11 1"></polyline></svg></span>';
					$rolehtml .= '</label>';
					$rolehtml .= '</td>';
					$rolehtml .= '<td>
									<select class="widefat" name="discount_type_' . esc_attr( $role->term_id ) . '" value="' . esc_attr( $wholesale_type ) . '">
										<option value="percent" ' . ( ( 'percent' == $discount ) ? 'selected' : '' ) . '>' . esc_html__( 'Percent', 'woocommerce-wholesale-pricing' ) . '</option>
										<option value="fixed" ' . ( ( 'fixed' == $discount ) ? 'selected' : '' ) . '>' . esc_html__( 'Fixed', 'woocommerce-wholesale-pricing' ) . '</option>
									</select>
								</td>';
					if ( $_product->is_type( 'simple' ) ) {
						$rolehtml .= '<td><input class="widefat" type="text" name="wholesale_price_' . esc_attr( $role->term_id ) . '" value="' . esc_attr( $price ) . '"> </td>';
					} else {
						$tickets   = new WC_Product_Variable( $product_id );
						$variables = $tickets->get_available_variations();
						foreach ( $variables as $keey ) {
							$wholesale_price = get_post_meta( $keey['variation_id'], '_wwp_wholesale_amount', true );
							$rolehtml       .= '<td><input type="text" name="wholesaleprice_' . esc_attr( $keey['variation_id'] ) . '" value="' . esc_attr( $wholesale_price ) . '"/></td>';
						}
					}
					$rolehtml .= '<td><input class="widefat" type="text" name="min_quatity_' . esc_attr( $role->term_id ) . '" value="' . esc_attr( $min ) . '"> </td>';
					$rolehtml .= '</tr>';
				}
				$rolehtml .= '</table>';
			}
			$featured_image = get_the_post_thumbnail_url( $product_id, 'thumbnail' );
			$html           = '<div class="flip" id="' . esc_attr( $product_id ) . '"> <div class="flip_img_wrapper"> <img class="img-fluid" src="' . esc_url( $featured_image ) . '" width="30" /> </div> ';

			$html .= ' <div class="flip_content_wrapper"> <div class="flip_title_wrap"> <span class="wwp-title">' . esc_html( get_the_title() ) . '</span></div> <div class="flip_price_wrap"><span class="regular_price">' . esc_html__( 'Regular Price: ', 'woocommerce-wholesale-pricing' ) . esc_html( $regular_price ) . '</span>';
			if ( '' != $sale_price ) {
				$html .= '<span class="sales_price">' . esc_html__( 'Sales Price: ', 'woocommerce-wholesale-pricing' ) . esc_html( $sale_price ) . ' </span>';
			}
			$html .= '</div> </div> </div>';
			if ( $_product->is_type( 'simple' ) ) {
				if ( ! empty( $roles ) ) {
					$html     .= '<div class="product_details" id="pannel-' . esc_attr( $product_id ) . '">';
						$html .= '<div class="wwp-loader"></div>';
						$html .= $rolehtml;
						$html .= '<input type="hidden" name="product_type_' . esc_attr( $product_id ) . '" value="simple">';
						$html .= '<input type="hidden" name="prod_id_' . esc_attr( $product_id ) . '" value="' . esc_attr( $product_id ) . '">';
						$html .= '<div class="wwp-btn-bulk-price"><button data-id="' . esc_attr( $product_id ) . '" class="wwp-button-primary" id="wholesale_pricing_bulk_update">' . esc_html__( 'Update', 'woocommerce-wholesale-pricing' ) . '</button></div>';
					$html     .= '</div>';
				}
			} else {
				$wholesale_type = get_post_meta( $product_id, '_wwp_wholesale_type', true );
				if ( ! empty( $roles ) ) {
					$html .= '<div class="product_details" id="pannel-' . esc_attr( $product_id ) . '">';
					// here we are stuck
					$tickets   = new WC_Product_Variable( $product_id );
					$variables = $tickets->get_available_variations();

					$html     .= '<div class="wwp-loader"></div>';
					if ( ! empty( $variables ) ) {
						$html .= '<table class="wholesale_pricing" cellpadding="10">';
						$html .= '<input type="hidden" name="product_type_' . esc_attr( $product_id ) . '" value="variable">';
						$html .= '<tr>';
						$html .= '<th style="min-width:132px">' . esc_html__( 'Wholesale Role', 'woocommerce-wholesale-pricing' ) . '</th>';
						$html .= '<th style="min-width:132px">' . esc_html__( 'SKU', 'woocommerce-wholesale-pricing' ) . '</th>';

						$html .= '<th style="min-width:132px">' . esc_html__( 'Enable for Role', 'woocommerce-wholesale-pricing' ) . '</th>';
						$html .= '<th style="min-width:132px">' . esc_html__( 'Discount Type', 'woocommerce-wholesale-pricing' ) . '</th>';
						$html .= '<th>' . esc_html__( 'Wholesale Price & Min Quantity (Per Variation)', 'woocommerce-wholesale-pricing' ) . '</th>';
						$html .= '</tr>';
						foreach ( $roles as $key => $role ) {
							if ( isset( $settings['wholesale_role'] ) && 'single' == $settings['wholesale_role'] && 'default_wholesaler' != $role->slug ) {
								continue;
							}
							if ( isset( $data[ $role->term_id ] ) ) {
								$discount = isset( $data[ $role->term_id ]['discount_type'] ) ? $data[ $role->term_id ]['discount_type'] : '';
							}
							$html                   .= '<tr>';
							$html                   .= '<td>';
							$html                   .= esc_html( $role->name );
							$html                   .= '</td>';
							$html .= '<td>';
							foreach ( $variables as $key ) {
								$variation_id             = $key['variation_id'];
								$product = wc_get_product($variation_id);
								$html .= '<p style="padding: 12px 0px 11px 0px;">' . $product->get_sku() . '</p>';
							}
							$html .= '</td>';
							$html                   .= '<td><input class="inp-cbx" type="checkbox" value="' . esc_attr( $role->slug ) . '" id="role_' . esc_attr( $role->term_id ) . '_' . esc_attr( $product_id ) . '" name="role_' . esc_attr( $role->term_id ) . '" ' . ( ( isset( $data[ $role->term_id ] ) ) ? 'checked' : '' ) . '>';
							$html                   .= '<label class="cbx cbx-square wosvg" for="role_' . esc_attr( $role->term_id ) . '_' . esc_attr( $product_id ) . '"><span><svg width="12px" height="9px" viewbox="0 0 12 9"><polyline points="1 5 4 8 11 1"></polyline></svg></span></label>';
							$html                   .= '</td>';
							$html                   .= '<td><select class="widefat" name="discount_type_' . esc_attr( $role->term_id ) . '" value="' . esc_attr( $wholesale_type ) . '">
							<option value="percent" ' . ( ( 'percent' == $discount ) ? 'selected' : '' ) . '>' . esc_html__( 'Percent', 'woocommerce-wholesale-pricing' ) . '</option>
							<option value="fixed"  ' . ( ( 'fixed' == $discount ) ? 'selected' : '' ) . '>' . esc_html__( 'Fixed', 'woocommerce-wholesale-pricing' ) . '</option>
							</select></td>';
							$html                   .= '<td>';
							$html                   .= '<div class="wwp-variable">';
							$wholesale_price_pro_ids = 'wholesale_variable_id_' . esc_attr( $product_id ) . '[]';
							$wholesale_price_qty     = esc_html__( ' Qty: ', 'woocommerce-wholesale-pricing' );
							$wholesale_prod_type     = 'product_type_' . esc_attr( $product_id );
							$wholesale_prod_id       = 'prod_id_' . esc_attr( $product_id );
							foreach ( $variables as $key ) {
								$variation_id             = $key['variation_id'];
								$wholesale_price_var_name = 'wholesaleprice_' . esc_attr( $role->term_id ) . '_' . esc_attr( $key['variation_id'] );
								$wholesale_price_qty_name = 'qty_' . esc_attr( $role->term_id ) . '_' . esc_attr( $key['variation_id'] );
								if ( isset( $data[ $role->term_id ][ $variation_id ] ) ) {
									$wholesale_price = $data[ $role->term_id ][ $variation_id ]['wholesaleprice'];
									$qty             = $data[ $role->term_id ][ $variation_id ]['qty'];
								} else {
									// $sale_price      = get_post_meta( $key['variation_id'], '_sale_price', true );
									$wholesale_price = get_post_meta( $key['variation_id'], '_wwp_wholesale_amount', true );
									$qty             = 1;
								}
								$product = wc_get_product($variation_id);

								$regular_price = get_post_meta( $key['variation_id'], '_regular_price', true );
								$html         .= '<div class="variable-item"><span class="variation"> #' . esc_html( $variation_id ) . ' </span><label>' . esc_html__( 'Regular Price', 'woocommerce-wholesale-pricing' ) . '</label><input type="text" readonly name="reg-price" value="' . esc_attr( $regular_price ) . '"/> <label>' . esc_html__( 'Wholesale Price', 'woocommerce-wholesale-pricing' ) . '</label>';
								$html         .= '<input type="text" name="' . esc_attr( $wholesale_price_var_name ) . '" value="' . esc_attr( $wholesale_price ) . '"/>';
								$html         .= '<label>' . esc_html( $wholesale_price_qty ) . '<input type="number" name="' . esc_attr( $wholesale_price_qty_name ) . '" value="' . esc_attr( $qty ) . '"/></label>';
								$html         .= '<input type="hidden" name="' . esc_attr( $wholesale_price_pro_ids ) . '" value="' . esc_attr( $key['variation_id'] ) . '">';
								$html         .= '<input type="hidden" name="' . esc_attr( $wholesale_prod_type ) . '" value="variable">';
								$html         .= '<input type="hidden" name="' . esc_attr( $wholesale_prod_id ) . '" value="' . esc_attr( $product_id ) . '">
								</div>';
							}
							$html .= '</div>';
							$html .= '</td>';
							$html .= '</tr>';
						}
						$html .= '</table>';
						$html .= '<div class="wwp-btn-bulk-price"><button data-id="' . esc_attr( $product_id ) . '" class="wwp-button-primary" id="wholesale_pricing_bulk_update">' . esc_html__( 'Update', 'woocommerce-wholesale-pricing' ) . '</button></div>';
					} else {
						$html .= esc_html__( 'No variations found. Add variations before.', 'woocommerce-wholesale-pricing' );
					}
					$html .= '</div>';
				}
			}
			echo wp_kses( $html, $this->wwp_allowed_tags() );
		}
		private function wwp_allowed_tags() {
			$allowed_tags = array(
				'a'      => array(
					'class' => array(),
					'href'  => array(),
					'rel'   => array(),
					'title' => array(),
				),
				'b'      => array(),
				'del'    => array(
					'datetime' => array(),
					'title'    => array(),
				),
				'dd'     => array(),
				'select' => array(
					'id'       => array(),
					'class'    => array(),
					'title'    => array(),
					'style'    => array(),
					'name'     => array(),
					'disabled' => array(),
				),
				'table'  => array(
					'id'          => array(),
					'class'       => array(),
					'style'       => array(),
					'cellpadding' => array(),
				),
				'tr'     => array(
					'id'    => array(),
					'class' => array(),
					'style' => array(),
				),
				'td'     => array(
					'id'    => array(),
					'class' => array(),
					'style' => array(),
				),
				'th'     => array(
					'id'    => array(),
					'class' => array(),
					'style' => array(),
				),
				'button' => array(
					'id'          => array(),
					'class'       => array(),
					'type'        => array(),
					'style'       => array(),
					'value'       => array(),
					'placeholder' => array(),
					'name'        => array(),
					'data-id'     => array(),
				),
				'input'  => array(
					'id'          => array(),
					'class'       => array(),
					'type'        => array(),
					'style'       => array(),
					'value'       => array(),
					'placeholder' => array(),
					'name'        => array(),
					'data'        => array(),
					'checked'     => array(),
					'readonly'    => array(),
					'disabled'    => array(),
				),
				'option' => array(
					'selected' => array(),
					'value'    => array(),
				),
				'div'    => array(
					'id'    => array(),
					'class' => array(),
					'title' => array(),
					'style' => array(),
				),
				'dl'     => array(),
				'dt'     => array(),
				'em'     => array(),
				'h1'     => array(),
				'h2'     => array(),
				'h3'     => array(),
				'h4'     => array(),
				'h5'     => array(),
				'h6'     => array(),
				'i'      => array(),
				'img'    => array(
					'alt'    => array(),
					'class'  => array(),
					'height' => array(),
					'src'    => array(),
					'width'  => array(),
				),
				'li'     => array(
					'class' => array(),
				),
				'ol'     => array(
					'class' => array(),
				),
				'p'      => array(
					'id'    => array(),
					'class' => array(),
					'style' => array(),
				),
				'span'   => array(
					'class' => array(),
					'id'    => array(),
					'title' => array(),
					'style' => array(),
				),
				'label'  => array(
					'for'   => array(),
					'id'    => array(),
					'class' => array(),
				),
				'strike' => array(),
				'strong' => array(),
				'ul'     => array(
					'class' => array(),
				),
			);
			return $allowed_tags;
		}
		public function wwp_bulk_update_options() {
			ob_start();
			?>
			<div class="bulk-options">
				<div class="wwp_bulk_filter">
					<b><?php esc_html_e( 'Filter Products By Category', 'woocommerce-wholesale-pricing' ); ?></b>
					<?php $this->wwp_get_product_category(); ?>
				</div>
				<div class="wwp-bulk-search">
					<div>
						<b><label for="wwp-product-sku"><?php esc_html_e( 'Search :', 'woocommerce-wholesale-pricing' ); ?></label></b>
						<input placeholder="<?php esc_html_e( 'Product Name, Wholesaler Name/slug or SKU', 'woocommerce-wholesale-pricing' ); ?> " type="text" name="wwp-product-sku" id="wwp-product-sku" value="<?php echo isset( $_GET['sku'] ) ? esc_attr(  sanitize_text_field( $_GET['sku'] ) ) : ''; ?>" />
						<?php 
						if ( isset( $_GET['sku'] ) ) { 
							?>
							<input class="wwp-product-sku-clear" type="button" value="&#8634;">
							<?php
						}
						?>
						<input class="wwp-button-primary wwp-product-sku-search" type="button" value="Search">
					</div>
				</div>
			</div>
			<?php
			echo wp_kses( ob_get_clean(), $this->wwp_allowed_tags() );
		}
		public function wwp_bulk_notify( $notify ) {
			$notify_msg            = array();
			$notify_msg['error']   = esc_html__( 'Error, Something wrong while saving wholesale prices.', 'woocommerce-wholesale-pricing' );
			$notify_msg['empty']   = '<b>' . esc_html__( 'Error!', 'woocommerce-wholesale-pricing' ) . '</b> ' . esc_html__( 'No Product Selected', 'woocommerce-wholesale-pricing' );
			$notify_msg['success'] = esc_html__( 'Products have been updated successfully', 'woocommerce-wholesale-pricing' );
			$html                  = '<div class="wwp_notify ' . esc_attr( $notify ) . '">' . wp_kses_post( $notify_msg[ $notify ] ) . '</div>';
			return $html;
		}
		public function wwp_extract_id( $extract_to ) {
			$iliminate_text = strstr( $extract_to, '_' );
			$product_id     = str_replace( '_', '', $iliminate_text );
			return $product_id;
		}
		public function wwp_get_product_category() {
			$terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
			echo '<select name="wwp_prod_cat" class="wwp_prod_cat">';
			echo '<option value="">' . esc_html__( 'Select Category', 'woocommerce-wholesale-pricing' ) . '</option>';
			foreach ( $terms as $term ) {
				$selected = '';
				if ( isset( $_GET['category'] ) && $_GET['category'] == $term->term_id ) {
					$selected = 'selected';
				}
				echo '<option value="' . esc_attr__( $term->term_id ) . '" ' . esc_html( $selected ) . ' >' . esc_html__( $term->name ) . '</option>';
			}
			echo '</select>';
		}
		/**
		 * Ajax function to retrieve meta html
		 *
		 * @since   1.0
		 * @version 1.0
		 */
		public function save_single_wholesale_product_callback() {

			check_ajax_referer( 'wwp_wholesale_pricing', 'security' );

			if ( ! isset( $_POST['product_id'] ) || ! is_numeric( $_POST['product_id'] ) ) {
				die();
			}
			$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : '';

			if ( '' == $product_id ) {
				return;
			}
			$roles  = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
			$params = array();
			if ( isset( $_POST['data'] ) ) {
				parse_str( wc_clean( $_POST['data'] ), $params );
			}
			$data  = array();
			$ptype = isset( $params[ 'product_type_' . $product_id ] ) ? $params[ 'product_type_' . $product_id ] : '';
			if ( ! empty( $roles ) ) {
				if ( 'variable' == $ptype ) {
					$tickets   = new WC_Product_Variable( $product_id );
					$variables = $tickets->get_available_variations();
					if ( ! empty( $variables ) ) {
						foreach ( $variables as $variable ) {
							$variation_id = $variable['variation_id'];
							$vary         = array();
							foreach ( $roles as $key => $role ) {
								if ( isset( $role->term_id ) && isset( $params[ 'role_' . $role->term_id ] ) ) {
									if ( isset( $params[ 'role_' . $role->term_id ] ) ) {
										$data[ $role->term_id ]['slug'] = $role->slug;
										$vary[ $role->term_id ]['slug'] = $role->slug;
									}
									if ( isset( $params[ 'discount_type_' . $role->term_id ] ) ) {
										$data[ $role->term_id ]['discount_type'] = $params[ 'discount_type_' . $role->term_id ];
										$vary[ $role->term_id ]['discount_type'] = $params[ 'discount_type_' . $role->term_id ];
									}
									if ( isset( $params[ 'wholesaleprice_' . $role->term_id . '_' . $variation_id ] ) ) {
										$data[ $role->term_id ][ $variation_id ]['wholesaleprice'] = is_numeric( $params[ 'wholesaleprice_' . $role->term_id . '_' . $variation_id ] ) ? $params[ 'wholesaleprice_' . $role->term_id . '_' . $variation_id ] : '';
										$vary[ $role->term_id ][ $variation_id ]['wholesaleprice'] = is_numeric( $params[ 'wholesaleprice_' . $role->term_id . '_' . $variation_id ] ) ? $params[ 'wholesaleprice_' . $role->term_id . '_' . $variation_id ] : '';
									}
									if ( isset( $params[ 'qty_' . $role->term_id . '_' . $variation_id ] ) ) {
										$data[ $role->term_id ][ $variation_id ]['qty'] = is_numeric( $params[ 'qty_' . $role->term_id . '_' . $variation_id ] ) ? $params[ 'qty_' . $role->term_id . '_' . $variation_id ] : 1;
										$vary[ $role->term_id ][ $variation_id ]['qty'] = is_numeric( $params[ 'qty_' . $role->term_id . '_' . $variation_id ] ) ? $params[ 'qty_' . $role->term_id . '_' . $variation_id ] : 1;
									}
								}
							}
							update_post_meta( $variation_id, 'wholesale_multi_user_pricing', $vary );
						}
					}
				} else {
					foreach ( $roles as $key => $role ) {
						if ( ! isset( $params[ 'role_' . esc_attr( $role->term_id ) ] ) ) {
							continue;
						}
						if ( isset( $params[ 'role_' . esc_attr( $role->term_id ) ] ) ) {
							$data[ $role->term_id ]['slug'] = esc_attr( $role->slug );
						}
						if ( isset( $params[ 'discount_type_' . $role->term_id ] ) ) {
							$data[ $role->term_id ]['discount_type'] = $params[ 'discount_type_' . esc_attr( $role->term_id ) ];
						}
						if ( isset( $params[ 'wholesale_price_' . esc_attr( $role->term_id ) ] ) ) {
							$data[ $role->term_id ]['wholesale_price'] = is_numeric( $params[ 'wholesale_price_' . esc_attr( $role->term_id ) ] ) ? $params[ 'wholesale_price_' . esc_attr( $role->term_id ) ] : '';
						}
						if ( isset( $params[ 'min_quatity_' . $role->term_id ] ) ) {
							$data[ $role->term_id ]['min_quatity'] = is_numeric( $params[ 'min_quatity_' . esc_attr( $role->term_id ) ] ) ? $params[ 'min_quatity_' . esc_attr( $role->term_id ) ] : 1;
						}
					}
				}
			}
			update_post_meta( $product_id, 'wholesale_multi_user_pricing', $data );
			echo wp_kses_post( $this->wwp_bulk_notify( 'success' ) );
			die();
		}
	}
	new WWP_Wholesale_Bulk_Price();
}
