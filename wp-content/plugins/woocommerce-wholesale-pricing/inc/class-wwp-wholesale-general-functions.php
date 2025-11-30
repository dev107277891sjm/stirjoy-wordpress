<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( ! function_exists( 'shapeSpace_allowed_html' ) ) :
	function shapeSpace_allowed_html() {

		$allowed_atts                = array(
			'align'      => array(),
			'class'      => array(),
			'type'       => array(),
			'id'         => array(),
			'dir'        => array(),
			'lang'       => array(),
			'style'      => array(),
			'xml:lang'   => array(),
			'src'        => array(),
			'alt'        => array(),
			'href'       => array(),
			'rel'        => array(),
			'rev'        => array(),
			'target'     => array(),
			'novalidate' => array(),
			'required'   => array(),
			'name'       => array(),
			'tabindex'   => array(),
			'action'     => array(),
			'method'     => array(),
			'for'        => array(),
			'width'      => array(),
			'height'     => array(),
			'data'       => array(),
			'title'      => array(),
			'value'      => array(),
			'selected'   => array(),
			'enctype'    => array(),
			'disable'    => array(),
			'disabled'   => array(),
			'noscript'   => array(),
		);
		$allowedposttags['form']     = $allowed_atts;
		$allowedposttags['required'] = $allowed_atts;
		$allowedposttags['noscript'] = $allowed_atts;
		$allowedposttags['label']    = $allowed_atts;
		$allowedposttags['select']   = $allowed_atts;
		$allowedposttags['option']   = $allowed_atts;
		$allowedposttags['input']    = $allowed_atts;
		$allowedposttags['textarea'] = $allowed_atts;
		$allowedposttags['iframe']   = $allowed_atts;
		$allowedposttags['script']   = $allowed_atts;
		$allowedposttags['style']    = $allowed_atts;
		$allowedposttags['strong']   = $allowed_atts;
		$allowedposttags['small']    = $allowed_atts;
		$allowedposttags['table']    = $allowed_atts;
		$allowedposttags['span']     = $allowed_atts;
		$allowedposttags['abbr']     = $allowed_atts;
		$allowedposttags['code']     = $allowed_atts;
		$allowedposttags['pre']      = $allowed_atts;
		$allowedposttags['div']      = $allowed_atts;
		$allowedposttags['img']      = $allowed_atts;
		$allowedposttags['h1']       = $allowed_atts;
		$allowedposttags['h2']       = $allowed_atts;
		$allowedposttags['h3']       = $allowed_atts;
		$allowedposttags['h4']       = $allowed_atts;
		$allowedposttags['h5']       = $allowed_atts;
		$allowedposttags['h6']       = $allowed_atts;
		$allowedposttags['ol']       = $allowed_atts;
		$allowedposttags['ul']       = $allowed_atts;
		$allowedposttags['li']       = $allowed_atts;
		$allowedposttags['em']       = $allowed_atts;
		$allowedposttags['hr']       = $allowed_atts;
		$allowedposttags['br']       = $allowed_atts;
		$allowedposttags['tr']       = $allowed_atts;
		$allowedposttags['td']       = $allowed_atts;
		$allowedposttags['p']        = $allowed_atts;
		$allowedposttags['a']        = $allowed_atts;
		$allowedposttags['b']        = $allowed_atts;
		$allowedposttags['i']        = $allowed_atts;
		return $allowedposttags;
	}
endif;

if ( ! function_exists( 'wwp_get_post_data' ) ) :
	function wwp_get_post_data( $name = '' ) {
		if ( isset( $_POST['wwp_wholesale_registrattion_nonce'] ) && wp_verify_nonce( wc_clean( $_POST['wwp_wholesale_registrattion_nonce'] ), 'wwp_wholesale_registrattion_nonce' ) ) {
			$post = $_POST;
		}

		$post = $_POST;
		if ( isset( $post[ $name ] ) ) { 
			/**
			* Hooks
			*
			* @since 3.0
			*/
			return apply_filters( 'wwp_get_post_data', $post[ $name ] );
		} else {
			return $_POST;
		}
	}
endif;

if ( ! function_exists( 'wwp_get_get_data' ) ) :
	function wwp_get_get_data( $name = '' ) {
		if ( isset( $_GET['wwp_wholesale_registrattion_nonce'] ) && wp_verify_nonce( wc_clean( $_GET['wwp_wholesale_registrattion_nonce'] ), 'wwp_wholesale_registrattion_nonce' ) ) {
			$get = $_GET;
		}
		$get = $_GET;
		if ( isset( $get[ $name ] ) ) {
			/**
			* Hooks
			*
			* @since 3.0
			*/
			return apply_filters( 'wwp_get_get_data', wp_kses_post( $get[ $name ] ) );
		} else {
			return $_GET;
		}
	}
endif;

if ( ! function_exists( 'wholesale_tab_link' ) ) :
	function wholesale_tab_link( $tab = '' ) {

		if ( ! empty( $tab ) ) {
			return admin_url( 'admin.php?page=wwp-registration-setting&tab=' ) . $tab;
		} else {
			return admin_url( 'admin.php?page=wwp-registration-setting' );
		}
	}
endif;

if ( ! function_exists( 'wholesale_tab_active' ) ) :
	function wholesale_tab_active( $active_tab = '' ) {
		$getdata = '';
		if ( isset( $_GET['tab'] ) ) {
			$getdata = sanitize_text_field( $_GET['tab'] );
		}

		if ( $getdata == $active_tab ) {
			return 'nav-tab-active';
		}
	}
endif;

if ( ! function_exists( 'wholesale_content_tab_active' ) ) :
	function wholesale_content_tab_active( $active_tab = '' ) {
		$getdata = '';
		if ( isset( $_GET['tab'] ) ) {
			$getdata = sanitize_text_field( $_GET['tab'] );
		}

		if ( $getdata == $active_tab ) {
			return 'bolck';
		} else {
			return 'none';
		}
	}
endif;

if ( ! function_exists( 'wholesale_load_form_builder' ) ) :
	function wholesale_load_form_builder( $active_tab = '' ) {
		$tab = '';
		if ( isset( $_GET['tab'] ) ) {
			$tab = sanitize_text_field( $_GET['tab'] );
		}

		if ( 'extra-fields' != $tab ) {
			return true;
		} else {
			return false;
		}
	}
endif;


if ( ! function_exists( 'is_wholesaler_user' ) ) :
	function is_wholesaler_user( $user_id ) {
		if ( ! empty( $user_id ) ) {
			$settings  = get_option( 'wwp_wholesale_pricing_options', true );
			$user_info = get_userdata( $user_id );
			$user_role = implode( ', ', $user_info->roles );
			if ( function_exists( 'icl_object_id' ) && class_exists( 'SitePress' ) ) {
				global $sitepress;
				$current_lang = $sitepress->get_current_language();
				$sitepress->switch_lang( 'en' );
				$allterms = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
				$sitepress->switch_lang( $current_lang );
			} else {
				$allterms = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
			}

			if (  isset( $settings['wholesale_role'] ) && 'single' == $settings['wholesale_role'] && 'default_wholesaler' == $user_role ) {
				return true;
			} elseif ( isset( $settings['wholesale_role'] ) && 'multiple' == $settings['wholesale_role'] ) {
				foreach ( $allterms as $allterm_key => $allterm_value ) {
					if ( @$allterm_value->slug == $user_role ) {
						return true;
					}
				}
			}
		}
		if ( isset( $_COOKIE['access_store_id'] ) && ! empty( $_COOKIE['access_store_id'] ) ) {
			return true;
		}
		return false;
	}
endif;

if ( ! function_exists( 'is_wholesale_product_quantity' ) ) :
	function is_wholesale_product_quantity( $product_id ) {
		$role = get_current_user_role_id();
		$data = get_post_meta( $product_id, 'wholesale_multi_user_pricing', true );
		if ( isset( $data[ $role ] ) ) {
			if ( isset( $data[ $role ]['min_quatity'] ) ) {
				return (int) $data[ $role ]['min_quatity'];
			} else {
				return 1;
			}
		}
		$terms = get_the_terms( $product_id, 'product_cat' );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$data = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );
				if ( isset( $data[ $role ] ) && isset( $data[ $role ]['discount_type'] ) && isset( $data[ $role ]['wholesale_price'] ) ) {
					if ( isset( $data[ $role ]['min_quatity'] ) ) {
						return (int) $data[ $role ]['min_quatity'];
					} else {
						return 1;
					}
				}
			}
		}
		$data = get_option( 'wholesale_multi_user_pricing' );
		if ( isset( $data[ $role ] ) && isset( $data[ $role ]['discount_type'] ) && isset( $data[ $role ]['wholesale_price'] ) ) {
			if ( isset( $data[ $role ]['min_quatity'] ) ) {
				return (int) $data[ $role ]['min_quatity'];
			} else {
				return 1;
			}
		}
		return 1;
	}
endif;

if ( ! function_exists( 'multi_wholesale_product_ids' ) ) :
	function multi_wholesale_product_ids() {
		$cate      = array();
		$total_ids = array();

		$categories = get_terms( array( 'taxonomy' => 'product_cat' ) );

		if ( is_array( $categories ) ) {

			foreach ( $categories as $category ) {

				$data = get_term_meta( $category->term_id, 'wholesale_multi_user_pricing', true );

				if ( ! empty( $data ) ) {
					foreach ( $data as $key => $value ) {

						if ( isset( $data[ $key ]['wholesale_price'] ) ) {
							$cate[] = $category->term_id;
						}
					}
				}
			}

			$cate = array_unique( $cate );

			if ( empty( $cate ) ) {
				return array();
			}

			$cate_string = implode( $cate );

			global $wpdb;

			$result = $wpdb->get_results(
				$wpdb->prepare( 
					"SELECT p.ID AS id FROM {$wpdb->prefix}posts AS p LEFT JOIN {$wpdb->prefix}term_relationships AS tr ON p.ID = tr.object_id WHERE tr.term_taxonomy_id IN (%s) AND p.post_type = 'product' AND p.post_status = 'publish' GROUP BY p.ID", $cate_string )
			);

			$ids = wp_list_pluck( (array) $result, 'id' );

			if ( is_array( $ids ) ) {

				foreach ( $ids  as $id ) {
					$total_ids[] = $id;
				}
			}
		}

		$all_ids = get_products_by_meta();
		
		foreach ( $all_ids as $id ) {

			$data = get_post_meta( $id, 'wholesale_multi_user_pricing', true );
			if ( ! empty( $data ) ) {
				foreach ( $data as $key => $value ) {
					if ( isset( $data[ $key ] ) ) {
						$total_ids[] = $id;
					}
				}
			}
		}

		if ( is_array( $all_ids ) ) {
			foreach ( $all_ids  as $id ) {
				$total_ids[] = $id;
			}
		}
		return array_unique( $total_ids );
	}
endif;

if ( ! function_exists( 'refresh_structure_form' ) ) :
	function refresh_structure_form( $formData ) {
		$formData = json_decode( $formData );
		if ( is_array( $formData ) ) {
			foreach ( $formData as $formData_key => $formData_value ) {
				if ( isset( $formData_value->userData ) ) {
					if ( isset( $formData_value->required ) ) {
						$formData[ $formData_key ]->required = false;
					}
					if ( isset( $formData_value->values ) ) {
						foreach ( $formData_value->values as $formData_value_key => $formData_value_val ) {
							$formData[ $formData_key ]->values[ $formData_value_key ]->selected = false;
						}
					}
				}
			}
		}
		return json_encode( $formData );
	}
endif;

if ( ! function_exists( 'wwp_render_characters_remove' ) ) :
	function wwp_render_characters_remove( $formData ) {
		$formData = refresh_structure_form( $formData );
		$formData = str_replace( "'", '&#39;', $formData );
		/**
		* Hooks
		*
		* @since 3.0
		*/
		return apply_filters( 'wwp_render_characters_remove', $formData );
	}
endif;

if ( ! function_exists( 'render_form_builder' ) ) :
	function render_form_builder( $callbach_data_form, $user_id = '' ) {
		?>
		<div id="container-wrap">
			<div class="render-wrap"></div>
			<input type="hidden" name="wwp_form_data_json" id="wwp_form_data_json"  value="">
		</div>
		<script>
			jQuery( document ).ready(function($) {
				<?php if ( 'get_option' == $callbach_data_form ) { ?>
					<?php
					if ( get_option( 'wwp_save_form' ) ) {
						$wwp_save_form = get_option( 'wwp_save_form', '[]' );
					} else {
						$wwp_save_form = '[]';
					}
					?>
					formData = <?php echo wp_kses_post( stripslashes( $wwp_save_form ) ); ?>;
					
					<?php 
				} elseif ( 'get_post_meta' == $callbach_data_form ) { 
					if ( get_post_meta( $user_id, 'wwp_form_data_json', true ) ) {
						$formData = get_post_meta( $user_id, 'wwp_form_data_json', true );
					} else {
						$formData = '[]';
					} 
					?>
					formData = <?php echo wp_kses_post( stripslashes( (string) $formData ) ); ?>; 
				<?php } else { ?>
			
					<?php if ( ! empty( $user_id ) && ! empty( get_user_meta( $user_id, 'wwp_form_data_json', true ) ) ) { ?> 
						
					formData  = <?php echo wp_kses_post( stripslashes( get_user_meta( $user_id, 'wwp_form_data_json', true ) ) ); ?>;
					<?php } else { ?>
						<?php
						if ( get_option( 'wwp_save_form' ) ) {
							$wwp_save_form = get_option( 'wwp_save_form', '[]' );
						} else {
							$wwp_save_form = '[]';
						} 
						?>
						formData = <?php echo wp_kses_post( stripslashes( $wwp_save_form ) ); ?>;
					<?php } ?>
				
				<?php } ?>
			wwp_filter_css = "<?php echo wp_kses_post(registration_form_class( ' woocommerce-form-row wwp_form_css_row ' )); ?>";		
			render_wrap = jQuery('.render-wrap').formRender({
				formData,
				layoutTemplates: {
					default: function( field, label, help, data ) {
						if ( data.type == 'checkbox-group' ) {
							if ( data.values.length ) {
								for( var i in data.values ) {
									if ( typeof data.values[i] !== "undefined" ) {
										if ( typeof data.userData != "undefined" ) {
											if ( Object.values(data.userData).indexOf(data.values[i].value) != -1 ) {
												field.querySelector(`input[value="${data.values[i].value}"]`).checked = true;
											}
										}
									}
								}
							}
						}

						if ( data.type == 'checkbox-group' || data.type == 'radio-group') {
							return $('<p/>').addClass(wwp_filter_css).append(label, field, help);
						} else {
							return $('<p/>').addClass(wwp_filter_css +" form-row").append(label, field, help);
						}
					}
				},
			});
			jQuery("input,textarea").keyup(function() { 
				wwp_set_json_to_hidden_field(); 
			});
			jQuery("select,input,textarea").change(function() { 
				wwp_set_json_to_hidden_field(); 
			});
			jQuery("ul.formbuilder-autocomplete-list li").click(function(){ 
				wwp_set_json_to_hidden_field();
			});
			jQuery("input,textarea").on("input paste", function() { 
				wwp_set_json_to_hidden_field();
			});
			
			function wwp_set_json_to_hidden_field() { 
				jQuery('#wwp_form_data_json').val(window.JSON.stringify(jQuery(render_wrap).formRender("userData")));
				return true;
			}
			
			wwp_set_json_to_hidden_field();
			
			<?php if ( ! is_admin() ) { ?>
			jQuery('.formBuilder-injected-style').remove();
			jQuery('#row-row--wide').removeClass("row");
			
			<?php } ?>
			
			});
		</script>
		<?php
	}
endif;

if ( ! function_exists( 'wwp_wholesale_css' ) ) :
	function wwp_wholesale_css( $settings ) {
		if ( isset( $settings['wholesale_css'] ) ) {
			?>
			<style type="text/css">
			<?php 
			/**
			* Hooks
			*
			* @since 3.0
			*/
			$wwp_registration_form_css = apply_filters( 'wwp_registration_form_css', $settings['wholesale_css'] );
			echo wp_kses_post( $wwp_registration_form_css );
			?>
			</style>
			<?php
		}
	}
endif;

if ( ! function_exists( 'registration_form_class' ) ) :
	function registration_form_class( $css ) {
		/**
		* Hooks
		*
		* @since 3.0
		*/
		return apply_filters( 'registration_form_class', $css );
	}
endif;

if ( ! function_exists( 'wwp_elements' ) ) :
	function wwp_elements( $elements ) {
		/**
		* Hooks
		*
		* @since 3.0
		*/
		echo wp_kses_post( apply_filters( 'wwp_registration_form_elements', $elements ) );
	}
endif;

if ( ! function_exists( 'form_builder_update_user_meta' ) ) :
	function form_builder_update_user_meta( $user_id, $post ) {
		
		if ( isset( $post['wwp_form_data_json'] ) && ! empty( $post['wwp_form_data_json'] ) ) {
			$customer = new WC_Customer( $user_id );
			$wwp_form_data_json = json_decode( stripslashes( wwp_get_post_data( 'wwp_form_data_json' ) ), true );
			foreach ( $wwp_form_data_json as $formdata_key => $meta_value ) {
				$meta_key = 'form_builder_' . str_replace( ' ', '_', $meta_value['label'] );
				$customer->update_meta_data( $meta_key, $meta_value );
			}
			$customer->save();
		}
	}
endif;

if ( ! function_exists( 'wwp_get_tax_price_display_suffix' ) ) :
	function wwp_get_tax_price_display_suffix( $product_id ) {

		if ( is_admin() ) {
			return;
		}

		global $woocommerce;
		$product = wc_get_product( $product_id );

		$tax_display_suffix = '';
		if ( $woocommerce->customer->is_vat_exempt() == false && get_option( 'woocommerce_price_display_suffix' ) && 'taxable' == get_post_meta( $product_id, '_tax_status', true ) ) {
			$tax_display_suffix = get_option( 'woocommerce_price_display_suffix' );
			$tax_display_suffix = '<small class="woocommerce-price-suffix">' . $tax_display_suffix . '</small>';
		}

		/**
		* Hooks
		*
		* @since 3.0
		*/
		return apply_filters( 'wwp_get_tax_price_display_suffix', $tax_display_suffix );
	}
endif;

if ( ! function_exists( 'wwp_get_price_including_tax' ) ) :
	function wwp_get_price_including_tax( $product, $args = array() ) {

		if ( is_admin() ) {
			return $args['price'];
		}
		
		global $woocommerce;
		if ( isset( $woocommerce ) && is_object( $woocommerce ) && method_exists( $woocommerce->customer, 'is_vat_exempt' ) ) {
			if ( $woocommerce->customer->is_vat_exempt() == false && 'taxable' == get_post_meta( $product->get_id(), '_tax_status', true ) ) {
				if ( ( ! is_cart() || ! is_checkout() ) && 'excl' == get_option( 'woocommerce_tax_display_shop' ) ) {
					$price = $args['price'];
				} else {
					$price = wc_get_price_including_tax( $product, array( 'price' => $args['price'] ) );
				}
			} else {
				$price = $args['price'] ;
			}
		} else {
			// Handle the case when $woocommerce object is not available
			$price = $args['price'] ;
		}

		if ( class_exists( 'Wpexperts\CurrencySwitcherForWoocommerce\WCCS' ) ) {
			global $WCCS;
			$price = $WCCS->wccs_price_conveter( $price, false );
		}
		
		/**
		* Hooks
		*
		* @since 3.0
		*/
		return apply_filters( 'wwp_get_price_including_tax', $price, $product );
	}
endif;

if ( ! function_exists( 'wwp_get_price_including_tax' ) ) :
	function wwp_get_price_including_tax( $product, $args = array() ) {

		if ( is_admin() ) {
			return $args['price'];
		}
		
		global $woocommerce;
		if ( isset( $woocommerce ) && is_object( $woocommerce ) && method_exists( $woocommerce->customer, 'is_vat_exempt' ) ) {
			if ( $woocommerce->customer->is_vat_exempt() == false && 'taxable' == get_post_meta( $product->get_id(), '_tax_status', true ) ) {
				if ( ( ! is_cart() || ! is_checkout() ) && 'excl' == get_option( 'woocommerce_tax_display_shop' ) ) {
					$price = $args['price'];
				} else {
					$price = wc_get_price_including_tax( $product, array( 'price' => $args['price'] ) );
				}
			} else {
				$price = $args['price'] ;
			}
		} else {
			// Handle the case when $woocommerce object is not available
			$price = $args['price'] ;
		}

		if ( class_exists( 'Wpexperts\CurrencySwitcherForWoocommerce\WCCS' ) ) {
			global $WCCS;
			$price = $WCCS->wccs_price_conveter( $price, false );
		}
		
		/**
		* Hooks
		*
		* @since 3.0
		*/
		return apply_filters( 'wwp_get_price_including_tax', $price, $product );
	}
endif;

if ( ! function_exists( 'wwp_get_price_including_tax_for_requisition' ) ) :
	function wwp_get_price_including_tax_for_requisition( $product, $args = array() ) {

		global $woocommerce;
		if ( $woocommerce->customer->is_vat_exempt() == false && 'taxable' == get_post_meta( $product->get_id(), '_tax_status', true ) ) {
			if ( ( ! is_cart() || ! is_checkout() ) && 'excl' == get_option( 'woocommerce_tax_display_shop' ) ) {
				$price = $args['price'];
			} else {
				$price = wc_get_price_including_tax( $product, array( 'price' => $args['price'] ) );
			}
		} else {
			$price = $args['price'];
		}

		if ( class_exists( 'Wpexperts\CurrencySwitcherForWoocommerce\WCCS' ) ) {
			global $WCCS;
			$price = $WCCS->wccs_price_conveter( $price, false );
		}
		
		/**
		* Hooks
		*
		* @since 3.0
		*/
		return apply_filters( 'wwp_get_price_including_tax_for_requisition', $price, $product );
	}
endif;

if ( ! function_exists( 'get_wholesale_role_id' ) ) :
	function get_wholesale_role_id( $user_role = '' ) {
		$wholesale_role = term_exists( $user_role, 'wholesale_user_roles' );
		if ( 0 !== $wholesale_role && null !== $wholesale_role ) {
			if ( is_array( $wholesale_role ) && isset( $wholesale_role['term_id'] ) ) {
				return $wholesale_role['term_id'];
			}
		}
	}
endif;

if ( ! function_exists( 'tier_pricing_modal_popup' ) ) :
	function tier_pricing_modal_popup( $title = '', $id = '', $role_id = '', $data = '', $name = '', $product_id = '' ) {

		if ( 'product_variation' == get_post_type( $product_id ) ) {
			$variation_slug = '[' . $product_id . ']';

			if ( isset( $data[ $role_id ] ) && ! empty( $data[ $role_id ] && isset( $data[ $role_id ][ $product_id ] ) ) ) {
				@$data[ $role_id ] = $data[ $role_id ][ $product_id ];
			}

		} else {
			$variation_slug = '';
		}
		$class = '';
		$minclass = '';
		if ( 'wholesale_multi_user_cart_discount[tier_pricing]' == $name ) { 
			$class = 'cart-tier-popup';
			$minclass = 'cartstartqty';
		}
		?>
	<!-- Modal -->
	<div class="modal fade tier_popup <?php echo esc_attr( $class ); ?>" id="<?php esc_attr_e( $id ); ?>" tabindex="-1" aria-labelledby="<?php esc_attr_e( $id ); ?>Label" aria-hidden="true">
	  <div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="<?php esc_attr_e( $id ); ?>Label"><?php esc_attr_e( $title ); ?></h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
		  <p class="wwp_required_field" style=" width: 100%;"><?php esc_html_e( 'Invalid Quantity or Rquierd price', 'woocommerce-wholesale-pricing' ); ?></p>
			<div class="row_tire">
				<div class="tire_table_lable lable_first">
					<?php esc_html_e( 'Starting Quantity', 'woocommerce-wholesale-pricing' ); ?> 
				</div>
				<?php if ( 'wholesale_multi_user_cart_discount[tier_pricing]' == $name ) { ?>
					<div class="tire_table_lable lable_secound">
						<?php esc_html_e( 'Test Quantity', 'woocommerce-wholesale-pricing' ); ?> 
					</div> 
					<?php
				}
				?>
				<div class="tire_table_lable lable_third">
					<?php esc_html_e( 'Ending Quantity', 'woocommerce-wholesale-pricing' ); ?> 
				</div> 
				<div class="tire_table_lable lable_forth">
					<?php esc_html_e( 'Wholesale Price', 'woocommerce-wholesale-pricing' ); ?> 
				</div>
			</div>
			<div style="clear:both;"></div>
		<?php

		if ( ! empty( $data[ $role_id ] ) ) {
			foreach ( $data[ $role_id ] as $key => $value ) {
				?>
				<div class="form-inline append-data">
					<div class="bunch_row" data-role="<?php esc_attr_e( $role_id ); ?>">
						<div class="col-md-4 wrapper_my_input">
							<input type="number" value="<?php isset($value['min']) ? esc_attr_e( $value['min'] ) : ''; ?>" name="<?php esc_attr_e( $name ); ?>[<?php esc_attr_e( $role_id ); ?>]<?php esc_attr_e( $variation_slug ); ?>[<?php esc_attr_e( $key ); ?>][min]" min="1" class="startingqty <?php esc_attr_e($minclass); ?> form-control form-control-sm" data-id="<?php esc_attr_e( $role_id ); ?>" placeholder="Starting Quantity">
							<span class="wwp_error_span str_qty_error" style="display:none" ><?php esc_html_e( 'Invalid Quantity', 'woocommerce-wholesale-pricing' ); ?></span>
						</div>

						<?php if ( 'wholesale_multi_user_cart_discount[tier_pricing]' == $name ) { ?>
							<div class="col-md-4 wrapper_my_input">
								<input type="number" value="<?php isset($value['cart_max']) ? esc_attr_e( $value['cart_max'] ) : ''; ?>" name="<?php esc_attr_e( $name ); ?>[<?php esc_attr_e( $role_id ); ?>]<?php esc_attr_e( $variation_slug ); ?>[<?php esc_attr_e( $key ); ?>][cart_max]" min="1" class="endingqty cartmaxqty form-control form-control-sm" placeholder="Cart Max Quantity">
								<span class="wwp_error_span cart_max_qty_error" style="display:none" ><?php esc_html_e( 'Invalid Quantity', 'woocommerce-wholesale-pricing' ); ?></span>
							</div> 
							<?php
						}
						?>
						
						<div class="col-md-4 wrapper_my_input">
							<input type="number" value="<?php isset($value['max']) ? esc_attr_e( $value['max'] ) : ''; ?>" name="<?php esc_attr_e( $name ); ?>[<?php esc_attr_e( $role_id ); ?>]<?php esc_attr_e( $variation_slug ); ?>[<?php esc_attr_e( $key ); ?>][max]"  min="1" step="<?php echo ( 'wholesale_multi_user_cart_discount[tier_pricing]' == $name ) ? 0.1 : 1; ?>" class="endingqty form-control form-control-sm" placeholder="Ending Quantity">
							<span class="wwp_error_span end_qty_error" style="display:none" ><?php esc_html_e( 'Invalid Quantity', 'woocommerce-wholesale-pricing' ); ?></span>
						</div>
						
						<div class="col-md-4 wrapper_my_input">
							<input type="<?php ( 'wholesale_multi_user_cart_discount[tier_pricing]' == $name ) ? esc_attr_e( 'text' ) : esc_attr_e( 'number' ); ?>" value="<?php isset($value['price']) ? esc_attr_e( $value['price'] ) : ''; ?>" name="<?php esc_attr_e( $name ); ?>[<?php esc_attr_e( $role_id ); ?>]<?php esc_attr_e( $variation_slug ); ?>[<?php esc_attr_e( $key ); ?>][price]" class="wwp_tire_price form-control form-control-sm" placeholder="Wholesale Price" step=".01" min=".01">

							<span class="wwp_error_span price_error" style="display:none" ><?php esc_html_e( 'Price Required', 'woocommerce-wholesale-pricing' ); ?></span>
						</div>
						
						<div class="icons">
							<span class="dashicons dashicons-trash"></span>
							<span class="dashicons dashicons-plus-alt"  onclick="add_row_tier_price(jQuery(this),'<?php esc_attr_e( $name ); ?>','<?php esc_attr_e( $variation_slug ); ?>')"></span>
						</div>
						 
					</div>
				</div>
				<?php
			}
		} else {

			$random = substr( md5( mt_rand() ), 0, 10 );
			?>
			<div class="form-inline append-data">	        
				<div class="bunch_row" data-role="<?php esc_attr_e( $role_id ); ?>">
					<div class="col-md-4 wrapper_my_input">
						<input type="text" name="<?php esc_attr_e( $name ); ?>[<?php esc_attr_e( $role_id ); ?>]<?php esc_attr_e( $variation_slug ); ?>[<?php esc_attr_e( $random ); ?>][min]" min="1" class="form-control form-control-sm" placeholder="Starting Quantity">
						<span class="wwp_error_span str_qty_error" style="display:none" ><?php esc_html_e( 'Invalid quantity', 'woocommerce-wholesale-pricing' ); ?></span>
					</div>
					<div class="col-md-4 wrapper_my_input">
						<input type="text" name="<?php esc_attr_e( $name ); ?>[<?php esc_attr_e( $role_id ); ?>]<?php esc_attr_e( $variation_slug ); ?>[<?php esc_attr_e( $random ); ?>][max]" min="1" class="form-control form-control-sm" placeholder="Ending Quantity">
						<span class="wwp_error_span end_qty_error" style="display:none" ><?php esc_html_e( 'Invalid quantity', 'woocommerce-wholesale-pricing' ); ?></span>
					</div>
					<div class="col-md-4 wrapper_my_input">
						<input type="text" name="<?php esc_attr_e( $name ); ?>[<?php esc_attr_e( $role_id ); ?>]<?php esc_attr_e( $variation_slug ); ?>[<?php esc_attr_e( $random ); ?>][price]" class="wwp_tire_price form-control form-control-sm" placeholder="Wholesale Price">
						<span class="wwp_error_span price_error" style="display:none" ><?php esc_html_e( 'Price required', 'woocommerce-wholesale-pricing' ); ?></span>
					</div>
					<div class="icons">
						<span class="dashicons dashicons-trash" ></span>
						<span class="dashicons dashicons-plus-alt" onclick="add_row_tier_price(jQuery(this),'<?php esc_attr_e( $name ); ?>','<?php esc_attr_e( $variation_slug ); ?>')"></span>
					</div>
				</div>
			</div>
			<?php
		}
		?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Close', 'woocommerce-wholesale-pricing' ); ?></button>
			<button name="save-wwp_wholesale" class="wwp-button-primary" type="submit" value="<?php esc_html_e( 'Save changes', 'woocommerce-wholesale-pricing' ); ?>"><?php esc_html_e( 'Save changes', 'woocommerce-wholesale-pricing' ); ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<script>
		jQuery(document).on("click","#<?php esc_attr_e( $id ); ?> .bunch_row span.dashicons.dashicons-trash",function() { 
			if (jQuery( "#<?php esc_attr_e( $id ); ?> .bunch_row" ).length > 1) {
				bunch_row = jQuery(this).parents( ".bunch_row" );
				bunch_row.slideUp("normal", function() {
					jQuery(this).remove(); 
				});
			}
		});
	</script>
		<?php
	}
endif;

if ( ! function_exists( 'get_current_user_role_id' ) ) :
	function get_current_user_role_id() {
		
		/**
		* Hooks
		*
		* @since 3.0
		*/
		do_action( 'wwp_get_current_user_role_id');
		if ( ( isset($_REQUEST['oauth_consumer_key']) && isset($_REQUEST['wholesale_request']['role'] ) && !empty($_REQUEST['wholesale_request']['role'] ) ) 
		|| 
		( isset($_REQUEST['wholesale_request']['role'] ) && !empty($_REQUEST['wholesale_request']['role'] ) )
		) { 
			$user_role      = isset($_REQUEST['wholesale_request']['role']) ? sanitize_text_field( $_REQUEST['wholesale_request']['role'] ) : '';
			$wholesale_role = term_exists( $user_role, 'wholesale_user_roles' );
			if ( 0 !== $wholesale_role && null !== $wholesale_role ) {
				if ( is_array( $wholesale_role ) && isset( $wholesale_role['term_id'] ) ) {
					return $wholesale_role['term_id'];
				}
			}
		} elseif ( isset($_REQUEST['wholesale_rolebypass'] ) && !empty($_REQUEST['wholesale_rolebypass'] ) ) { 
			$wholesale_role = term_exists( wc_clean( $_REQUEST['wholesale_rolebypass'] ), 'wholesale_user_roles' );
			if ( 0 !== $wholesale_role && null !== $wholesale_role ) {
				if ( is_array( $wholesale_role ) && isset( $wholesale_role['term_id'] ) ) {
					return $wholesale_role['term_id'];
				}
			}
		}

		$order_id = isset($_REQUEST['order_id']) ? sanitize_text_field( $_REQUEST['order_id'] ) : get_the_ID();

		if ( get_option('woocommerce_custom_orders_table_enabled') && isset( $_REQUEST['page'], $_REQUEST['id'] ) && sanitize_text_field( 'wc-orders' ) == $_REQUEST['page'] ) {
			$order_id = sanitize_text_field( $_REQUEST['id'] );
		}

		if (  in_array( get_post_type( $order_id ), array( 'shop_order', 'shop_order_placehold' ) )  ) {
			$order   = wc_get_order( $order_id );
			if ( ! $order ) {
				return false;
			}
			$user_id = $order->get_user_id();
			$user    = get_userdata( $user_id );
			if ( !empty($user->roles) ) {
				$user_role = implode( ', ', $user->roles );
				return get_wholesale_role_id( $user_role );
			} else {
				return false;
			}
		}
		
		// Add to Quote compatibility
		$quote_id = isset($_REQUEST['post_id']) ? sanitize_text_field( $_REQUEST['post_id'] ) : get_the_ID();
		if (  'wc-quote' == get_post_type( $quote_id ) ) {
			$quote   = get_post( $quote_id );
			$user_id = $quote->post_author;
			$user    = get_userdata( $user_id );
			if ($user->roles) {
				$user_role = implode( ', ', $user->roles );
				return get_wholesale_role_id( $user_role );
			}
			
		}
		
		// if ( is_admin() && 'single' == $settings['wholesale_role'] ) {
		//  $wholesale_role = term_exists( 'default_wholesaler', 'wholesale_user_roles' );
		//  if ( isset( $wholesale_role['term_id'] ) ) {
		//      return $wholesale_role['term_id'];
		//  }
		// } elseif ( is_admin() && 'multiple' == $settings['wholesale_role'] ) {
		//  $wholesale_role = term_exists( $settings['default_multipe_wholesale_roles'], 'wholesale_user_roles' );
		//  if ( isset( $wholesale_role['term_id'] ) ) {
		//      return $wholesale_role['term_id'];
		//  }
		// }
		if ( is_user_logged_in() ) {
			$user_info      = get_userdata( get_current_user_id() );
			$user_role      = implode( ', ', $user_info->roles );
			$wholesale_role = term_exists( $user_role, 'wholesale_user_roles' );
			if ( 0 !== $wholesale_role && null !== $wholesale_role ) {
				if ( is_array( $wholesale_role ) && isset( $wholesale_role['term_id'] ) ) {
					return $wholesale_role['term_id'];
				}
			}
		} else {
			$settings = get_option( 'wwp_wholesale_pricing_options', true );
			if ( 'yes' == $settings['restrict_store_access'] && isset( $_COOKIE['access_store_id'] ) && ! empty( $_COOKIE['access_store_id'] ) ) {
				return sanitize_text_field( $_COOKIE['access_store_id'] );
			}
		}
		return false;
	}
endif;

if ( ! function_exists( 'wholesale_type_price' ) ) :
	function wholesale_type_price( $price, $type, $amount ) {
	
		if ( empty( $price ) || empty( $amount ) ) {
			return $price;
		}
		if ( 'fixed' == $type ) {
			$price = $amount; 
		} else { 
			$price = $price * $amount / 100;
		}
		
		return $price;
	}
endif;
 
if ( ! function_exists( 'tire_get_type' ) ) :
	function tire_get_type( $id, $product, $tire_price, $regular_price, $type ) {

		if ( empty( $regular_price ) || empty( $tire_price ) ) {
			return $regular_price;
		}
		
		if ( 'fixed' == $type ) {
			$regular_price = $tire_price;
		} else {
			$regular_price = $regular_price * $tire_price / 100;
		}
		return $regular_price;
	}
endif;
 
if ( ! function_exists( 'tire_simple_wholesale_product_price_html' ) ) :
	function tire_simple_wholesale_product_price_html( $id, $product, $type = '' ) {

		if ( is_admin() ) {
			return false;
		}

		$role = get_current_user_role_id();

		$wholesale_product_variations = array();
		$original_variation_price     = array();
		$settings                     = get_option( 'wwp_wholesale_pricing_options', true );
		if ( 'product' == $type ) {
	
			$product_tier_pricing = get_post_meta( $id, 'product_tier_pricing', true );
			if ( isset( $product_tier_pricing[ $role ] ) ) {
			
				$data   = get_post_meta( $product->get_id(), 'wholesale_multi_user_pricing', true );
				$prices = array();
				foreach ( $product_tier_pricing[ $role ] as $key => $value ) {
					if ( isset( $value['price'] ) && ! empty( $value['price'] ) ) {
						$wholesale_product_variations[] = tire_get_type( $id, $product, $value['price'], get_post_meta( $id, '_regular_price', true ), $data[ $role ]['discount_type'] );
						$original_variation_price[]     = get_post_meta( $id, '_regular_price', true );
					}
				}
			} else {
				return false;

			}
		} elseif ( 'category' == $type ) {
			$wholesale_product_variations = array();
			$original_variation_price     = array();

			$terms = get_the_terms( $id, 'product_cat' );

			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$data           = get_term_meta( $term->term_id, 'category_tier_pricing', true );
					$data_wholesale = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );

					if ( isset( $data['tier_pricing'][ $role ] ) ) {
						if ( isset($data_wholesale[ $role ]) ) {
							foreach ( $data['tier_pricing'][ $role ] as $key => $value ) {
								if ( isset( $value['price'] ) && ! empty( $value['price'] ) ) {
									$wholesale_product_variations[] = tire_get_type( $id, $product, $value['price'], get_post_meta( $id, '_regular_price', true ), $data_wholesale[ $role ]['discount_type'] );
									$original_variation_price[]     = get_post_meta( $id, '_regular_price', true );
								}
							}
						}
						
					}
					
				}
			}
		} elseif ( 'global' == $type ) {

			if ( isset( $settings['tier_pricing'] ) ) {
				if ( isset( $settings['tier_pricing'][ $role ] ) ) {
					$data_wholesale = get_option( 'wholesale_multi_user_pricing' );
					foreach ( $settings['tier_pricing'][ $role ] as $key => $value ) {
						if ( isset( $value['price'] ) && ! empty( $value['price'] ) ) {
							$wholesale_product_variations[] = tire_get_type( $id, $product, $value['price'], get_post_meta( $id, '_regular_price', true ), $data_wholesale[ $role ]['discount_type'] );
							$original_variation_price[]     = get_post_meta( $id, '_regular_price', true );
						}
					}
					
				} else {
					return false;

				}
			}
		} elseif ( 'group' == $type ) {
			$ws_group = wwp_get_group_post_by_proid( $id, get_current_user_id() );
			if ( $ws_group ) { 
				$group_id = isset( $ws_group[0] ) ? $ws_group[0] : '';
				if ( ! empty( $group_id ) ) {
					$discount_type = get_post_meta( $group_id, '_discount_type', true );
					$wwp_price = get_post_meta( $group_id, '_wwp_group_price', true );
					$wholesale_product_variations[] = tire_get_type( $id, $product, $wwp_price, get_post_meta( $id, '_regular_price', true ), $discount_type );
					$original_variation_price[]     = get_post_meta( $id, '_regular_price', true );
				}
			}
		}

		if ( empty( $wholesale_product_variations ) ) {
			return false;
		}
		
		sort( $wholesale_product_variations );
		sort( $original_variation_price );
		foreach ( $original_variation_price as $key => $value ) {
			if ( empty( $value ) ) {
				$value = 1;
			}
			
			if ( empty( $wholesale_product_variations[$key] ) ) {
				$wholesale_product_variations[$key] = 1;
			}
			$up_to_save[] = ( $value - $wholesale_product_variations[$key] ) / $value * 100;
		}
		// orignal price add tax
		$original_variation_price[0]                                        = wwp_get_price_including_tax( $product, array( 'price' => $original_variation_price[0] ) );
		$original_variation_price[ count( $original_variation_price ) - 1 ] = wwp_get_price_including_tax( $product, array( 'price' => $original_variation_price[ count( $original_variation_price ) - 1 ] ) );
		// wholesale price add tax
		$wholesale_product_variations[0] = wwp_get_price_including_tax( $product, array( 'price' => $wholesale_product_variations[0] ) );

		$wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ] = wwp_get_price_including_tax( $product, array( 'price' => $wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ] ) );

		$min_wholesale_price          = $wholesale_product_variations[0];
		$max_wholesale_price          = $wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ];
		$min_original_variation_price = $original_variation_price[0];
		$max_original_variation_price = $original_variation_price[ count( $original_variation_price ) - 1 ];
		
		if ( empty( $min_original_variation_price ) ) {
			$min_original_variation_price = 1;
		}

		$min_saving_amount  = round( ( $min_original_variation_price - $min_wholesale_price ) );
		
		if ( empty( $max_original_variation_price ) ) {
			$max_original_variation_price = 1;
		}

		if ( 0 == $min_original_variation_price ) {
			$min_saving_percent = 0;
		} else {
			$min_saving_percent = ( $min_original_variation_price - $min_wholesale_price ) / $min_original_variation_price * 100;
		}
		
		$max_saving_amount  = round( ( $max_original_variation_price - $max_wholesale_price ) );

		if ( 0 == $max_original_variation_price ) {
			$max_saving_percent = 0;
		} else {
			$max_saving_percent = ( $max_original_variation_price - $max_wholesale_price ) / $max_original_variation_price * 100;
		}


		//$min_quantity = get_post_meta( $prod_id, '_wwp_wholesale_min_quantity', true );
		$settings = get_option( 'wwp_wholesale_pricing_options', true );
		$actual   = ( isset( $settings['retailer_label'] ) && ! empty( $settings['retailer_label'] ) ) ? esc_html( $settings['retailer_label'] ) : esc_html__( 'Actual', 'woocommerce-wholesale-pricing' );
		$save     = ( isset( $settings['save_label'] ) && ! empty( $settings['save_label'] ) ) ? esc_html( $settings['save_label'] ) : esc_html__( 'Save Up To', 'woocommerce-wholesale-pricing' );
		$new      = ( isset( $settings['wholesaler_label'] ) && ! empty( $settings['wholesaler_label'] ) ) ? esc_html( $settings['wholesaler_label'] ) : esc_html__( 'New', 'woocommerce-wholesale-pricing' );
		$html     = '<div class="wwp-wholesale-pricing-details">';

		$wcv_max_price = $max_original_variation_price;
		$wcv_min_price = $min_original_variation_price;

		// Tax display suffix function call
		$tax_display_suffix = wwp_get_tax_price_display_suffix( $product->get_id() );

		if ( 'yes' != $settings['retailer_disabled'] ) {
			if ( $wcv_min_price == $wcv_max_price ) {
				$html .= '<p><span class="retailer-text">' . esc_html__( $actual, 'woocommerce-wholesale-pricing' ) . '</span>: ' . wwp_strike_through_price_element('start') . wc_price( $wcv_min_price ) . ' ' . $product->get_price_suffix( get_post_meta( $product->get_id(), '_regular_price', true ) ) . wwp_strike_through_price_element('end') . '</p>';
			} else {
				$html .= '<p><span class="retailer-text">' . esc_html__( $actual, 'woocommerce-wholesale-pricing' ) . '</span>: ' . wwp_strike_through_price_element('start') . wc_price( $wcv_min_price ) . ' - ' . wc_price( $wcv_max_price ) . ' ' . $product->get_price_suffix( get_post_meta( $product->get_id(), '_regular_price', true ) ) . wwp_strike_through_price_element('end') . '</p>';
			}
		}

		if ( wc_price( $wholesale_product_variations[0] ) !== wc_price( $wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ] ) ) {
			$html .= '<p><b><span class="price-text">' . esc_html__( $new, 'woocommerce-wholesale-pricing' ) . '</span>: ' . wc_price( $wholesale_product_variations[0] ) . ' - ' . wc_price( $wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ] ) . ' ' . $product->get_price_suffix( $wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ] ) . '</b></p>';
		} else {
			$html .= '<p><b><span class="price-text">' . esc_html__( $new, 'woocommerce-wholesale-pricing' ) . '</span>: ' . wc_price( $wholesale_product_variations[0] ) . ' ' . $product->get_price_suffix( $wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ] ) . '</b></p>';
		}

		if ( 'yes' != $settings['save_price_disabled'] ) {
			$html .= '<p><b><span class="save-price-text">' . esc_html__( $save, 'woocommerce-wholesale-pricing' ) . '</span>:  (' . round( max($up_to_save) ) . '%) </b></p>';
		}
		$html .= '</div>';
		return $html;
	}
endif;


if ( ! function_exists( 'tire_variable_wholesale_product_price_html' ) ) :
	function tire_variable_wholesale_product_price_html( $id, $product, $type = '' ) {
		
		if ( is_admin() ) {
			return;
		}
		$role                         = get_current_user_role_id();
		$wholesale_product_variations = array();
		$original_variation_price     = array();
		$product_variations           = $product->get_children();
		$settings                     = get_option( 'wwp_wholesale_pricing_options', true );

		if ( 'product' == $type ) {
			$product_tier_pricing = get_post_meta( $id, 'product_tier_pricing', true );
			if ( isset( $product_tier_pricing[ $role ] ) ) { 
				$data_wholesale = get_post_meta( $id, 'wholesale_multi_user_pricing', true );
				$prices         = array();
				foreach ( $product_tier_pricing[ $role ] as $keys => $parent ) {
					foreach ( $parent as  $key => $value ) {
						if ( isset( $value['price'] ) && ! empty( $value['price'] ) ) {
							$wholesale_product_variations[] = tire_get_type( $keys, $product, $value['price'], get_post_meta( $keys, '_regular_price', true ) , $data_wholesale[ $role ]['discount_type'] );            
							$original_variation_price[]     = get_post_meta( $keys, '_regular_price', true );
						}
					}
				}
			}
		} elseif ( 'category' == $type ) {

			$wholesale_product_variations = array();
			$original_variation_price     = array();

			$terms = get_the_terms( $id, 'product_cat' );
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$data           = get_term_meta( $term->term_id, 'category_tier_pricing', true );
					$data_wholesale = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );
					if ( isset( $data_wholesale[ $role ] ) ) {
						if ( isset( $data['tier_pricing'][ $role ] ) ) {
							foreach ( $product_variations as $product_variation ) {
								foreach ( $data['tier_pricing'][ $role ] as $key => $value ) {
									if ( isset( $value['price'] ) && ! empty( $value['price'] ) ) {
										$original_variation_price[]     = get_post_meta( $product_variation, '_regular_price', true );
										$wholesale_product_variations[] = tire_get_type( $product_variation, $product, $value['price'], get_post_meta( $product_variation, '_regular_price', true ), $data_wholesale[ $role ]['discount_type'] );
									}
								}
							}
						} else {
							return false;
						}
					}
				}
			}
		} elseif ( 'global' == $type ) {
			if ( isset( $settings['tier_pricing'] ) ) {

				$wholesale_product_variations = array();
				$original_variation_price     = array();
				$key                          = array();
				$value                        = array();
				
				if ( isset( $settings['tier_pricing'][ $role ] ) ) {
					$data_wholesale = get_option( 'wholesale_multi_user_pricing' );
					foreach ( $product_variations as $product_variation ) {
						foreach ( $settings['tier_pricing'][ $role ] as $key => $value ) {
							if ( isset( $value['price'] ) && ! empty( $value['price'] ) ) {
								$original_variation_price[]     = get_post_meta( $product_variation, '_regular_price', true );
								$wholesale_product_variations[] = tire_get_type( $product_variation, $product, $value['price'], get_post_meta( $product_variation, '_regular_price', true ), $data_wholesale[ $role ]['discount_type'] );
							}
						}
					}
				} else {
					return false;
				}
			}
		} elseif ( 'group' == $type ) {
			$ws_group = wwp_get_group_post_by_proid( $id, get_current_user_id() );
			if ( $ws_group ) { 
				$group_id = isset( $ws_group[0] ) ? $ws_group[0] : '';
				if ( ! empty( $group_id ) ) {
					$discount_type = get_post_meta( $group_id, '_discount_type', true );
					$wwp_price = get_post_meta( $group_id, '_wwp_group_price', true );
					foreach ( $product_variations as $product_variation ) { 
						$wholesale_product_variations[] = tire_get_type( $product_variation, $product, $wwp_price, get_post_meta( $product_variation, '_regular_price', true ), $discount_type );
						$original_variation_price[]     = get_post_meta( $product_variation, '_regular_price', true );

					}
				}
			}
		}

		if ( empty( $wholesale_product_variations ) ) {
			return false;
		}
		foreach ( $original_variation_price as $key => $value ) { 
			if ( empty( $value ) ) {
				$value = 1;
			}
			
			if ( empty( $wholesale_product_variations[$key] ) ) {
				$wholesale_product_variations[$key] = 1;
			}
			$up_to_save[] = ( $value - $wholesale_product_variations[$key] ) / $value * 100;
		}

		sort( $wholesale_product_variations );
		sort( $original_variation_price );
		
		// orignal price add tax
		$original_variation_price[0]                                        = wwp_get_price_including_tax( $product, array( 'price' => $original_variation_price[0] ) );
		$original_variation_price[ count( $original_variation_price ) - 1 ] = wwp_get_price_including_tax( $product, array( 'price' => $original_variation_price[ count( $original_variation_price ) - 1 ] ) );
		// wholesale price add tax
		$wholesale_product_variations[0] = wwp_get_price_including_tax( $product, array( 'price' => $wholesale_product_variations[0] ) );
		$wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ] = wwp_get_price_including_tax( $product, array( 'price' => $wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ] ) );

		$min_wholesale_price = $wholesale_product_variations[0];
		$max_wholesale_price = $wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ];

		$min_original_variation_price = $original_variation_price[0];
		$max_original_variation_price = $original_variation_price[ count( $original_variation_price ) - 1 ];
		
		if ( empty( $min_original_variation_price ) ) {
			$min_original_variation_price = 1;
		}

		if ( empty( $max_original_variation_price ) ) {
			$max_original_variation_price = 1;
		}

		$min_saving_amount  = round( ( (float) $min_original_variation_price - (float) $min_wholesale_price ) );
		$min_saving_percent = ( (float) $min_original_variation_price - (float) $min_wholesale_price ) / (float) $min_original_variation_price * 100;
		
		$max_saving_amount  = round( ( (float) $max_original_variation_price - (float) $max_wholesale_price ) );
		$max_saving_percent = ( (float) $max_original_variation_price - (float) $max_wholesale_price ) / (float) $max_original_variation_price * 100;

		//$min_quantity = get_post_meta( $prod_id, '_wwp_wholesale_min_quantity', true );
		$settings = get_option( 'wwp_wholesale_pricing_options', true );
		$actual   = ( isset( $settings['retailer_label'] ) && ! empty( $settings['retailer_label'] ) ) ? esc_html( $settings['retailer_label'] ) : esc_html__( 'Actual', 'woocommerce-wholesale-pricing' );
		$save     = ( isset( $settings['save_label'] ) && ! empty( $settings['save_label'] ) ) ? esc_html( $settings['save_label'] ) : esc_html__( 'Save Up To', 'woocommerce-wholesale-pricing' );
		$new      = ( isset( $settings['wholesaler_label'] ) && ! empty( $settings['wholesaler_label'] ) ) ? esc_html( $settings['wholesaler_label'] ) : esc_html__( 'New', 'woocommerce-wholesale-pricing' );
		$html     = '<div class="wwp-wholesale-pricing-details">';

		$wcv_max_price = $max_original_variation_price;
		$wcv_min_price = $min_original_variation_price;

		if ( 'yes' != $settings['retailer_disabled'] ) {
			if ( $wcv_min_price == $wcv_max_price ) {
				$html .= '<p><span class="retailer-text">' . esc_html__( $actual, 'woocommerce-wholesale-pricing' ) . '</span>: ' . wwp_strike_through_price_element('start') . wc_price( $wcv_min_price ) . ' ' . wwp_strike_through_price_element('end') . '</p>';
			} else {
				$html .= '<p><span class="retailer-text">' . esc_html__( $actual, 'woocommerce-wholesale-pricing' ) . '</span>: ' . wwp_strike_through_price_element('start') . wc_price( $wcv_min_price ) . '  - ' . wc_price( $wcv_max_price ) . ' ' . wwp_strike_through_price_element('end') . '</p>';
			}
		}
		
		if ( wc_price( $wholesale_product_variations[0] ) !== wc_price( $wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ] ) ) {
			$html .= '<p><b><span class="price-text">' . esc_html__( $new, 'woocommerce-wholesale-pricing' ) . '</span>: ' . wc_price( $wholesale_product_variations[0] ) . ' - ' . wc_price( $wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ] ) . ' </b></p>';
		} else {
			$html .= '<p><b><span class="price-text">' . esc_html__( $new, 'woocommerce-wholesale-pricing' ) . '</span>: ' . wc_price( $wholesale_product_variations[0] ) . ' </b></p>';
		}
		
		if ( 'yes' != $settings['save_price_disabled'] ) {
			  
				$html .= '<p><b><span class="save-price-text">' . esc_html__( $save, 'woocommerce-wholesale-pricing' ) . '</span>:  (' . round( max($up_to_save) ) . '%) </b></p>';
		}
		$html .= '</div>';
		return $html;
	}
endif;

if ( ! function_exists( 'tire_simple_subsc_wholesale_product_price_html' ) ) :
	function tire_simple_subsc_wholesale_product_price_html( $id, $product, $type = '' ) {

		if ( is_admin() ) {
			return false;
		}

		$role = get_current_user_role_id();

		$wholesale_product_variations = array();
		$original_variation_price     = array();
		$settings                     = get_option( 'wwp_wholesale_pricing_options', true );
		if ( 'product' == $type ) {
	
			$product_tier_pricing = get_post_meta( $id, 'product_tier_pricing', true );
			if ( isset( $product_tier_pricing[ $role ] ) ) {
			
				$data   = get_post_meta( $product->get_id(), 'wholesale_multi_user_pricing', true );
				$prices = array();
				foreach ( $product_tier_pricing[ $role ] as $key => $value ) {
					if ( isset( $value['price'] ) && ! empty( $value['price'] ) ) {
						$wholesale_product_variations[] = tire_get_type( $id, $product, $value['price'], get_post_meta( $id, '_regular_price', true ), $data[ $role ]['discount_type'] );
						$original_variation_price[]     = get_post_meta( $id, '_regular_price', true );
					}
				}
			} else {
				return false;

			}
		} elseif ( 'category' == $type ) {
			$wholesale_product_variations = array();
			$original_variation_price     = array();

			$terms = get_the_terms( $id, 'product_cat' );

			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$data           = get_term_meta( $term->term_id, 'category_tier_pricing', true );
					$data_wholesale = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );

					if ( isset( $data['tier_pricing'][ $role ] ) ) {
						if ( isset($data_wholesale[ $role ]) ) {
							foreach ( $data['tier_pricing'][ $role ] as $key => $value ) {
								if ( isset( $value['price'] ) && ! empty( $value['price'] ) ) {
									$wholesale_product_variations[] = tire_get_type( $id, $product, $value['price'], get_post_meta( $id, '_regular_price', true ), $data_wholesale[ $role ]['discount_type'] );
									$original_variation_price[]     = get_post_meta( $id, '_regular_price', true );
								}
							}
						}
						
					}
					
				}
			}
		} elseif ( 'global' == $type ) {

			if ( isset( $settings['tier_pricing'] ) ) {
				if ( isset( $settings['tier_pricing'][ $role ] ) ) {
					$data_wholesale = get_option( 'wholesale_multi_user_pricing' );
					foreach ( $settings['tier_pricing'][ $role ] as $key => $value ) {
						if ( isset( $value['price'] ) && ! empty( $value['price'] ) ) {
							$wholesale_product_variations[] = tire_get_type( $id, $product, $value['price'], get_post_meta( $id, '_regular_price', true ), $data_wholesale[ $role ]['discount_type'] );
							$original_variation_price[]     = get_post_meta( $id, '_regular_price', true );
						}
					}
					
				} else {
					return false;

				}
			}
		} elseif ( 'group' == $type ) {
			$ws_group = wwp_get_group_post_by_proid( $id, get_current_user_id() );
			if ( $ws_group ) { 
				$group_id = isset( $ws_group[0] ) ? $ws_group[0] : '';
				if ( ! empty( $group_id ) ) {
					$discount_type = get_post_meta( $group_id, '_discount_type', true );
					$wwp_price = get_post_meta( $group_id, '_wwp_group_price', true );
					$wholesale_product_variations[] = tire_get_type( $id, $product, $wwp_price, get_post_meta( $id, '_regular_price', true ), $discount_type );
					$original_variation_price[]     = get_post_meta( $id, '_regular_price', true );
				}
			}
		}

		if ( empty( $wholesale_product_variations ) ) {
			return false;
		}
		
		sort( $wholesale_product_variations );
		sort( $original_variation_price );
		foreach ( $original_variation_price as $key => $value ) {
			if ( empty( $value ) ) {
				$value = 1;
			}
			
			if ( empty( $wholesale_product_variations[$key] ) ) {
				$wholesale_product_variations[$key] = 1;
			}
			$up_to_save[] = ( $value - $wholesale_product_variations[$key] ) / $value * 100;
		}
		// orignal price add tax
		$original_variation_price[0]                                        = wwp_get_price_including_tax( $product, array( 'price' => $original_variation_price[0] ) );
		$original_variation_price[ count( $original_variation_price ) - 1 ] = wwp_get_price_including_tax( $product, array( 'price' => $original_variation_price[ count( $original_variation_price ) - 1 ] ) );
		// wholesale price add tax
		$wholesale_product_variations[0] = wwp_get_price_including_tax( $product, array( 'price' => $wholesale_product_variations[0] ) );

		$wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ] = wwp_get_price_including_tax( $product, array( 'price' => $wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ] ) );

		$min_wholesale_price          = $wholesale_product_variations[0];
		$max_wholesale_price          = $wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ];
		$min_original_variation_price = $original_variation_price[0];
		$max_original_variation_price = $original_variation_price[ count( $original_variation_price ) - 1 ];
		
		if ( empty( $min_original_variation_price ) ) {
			$min_original_variation_price = 1;
		}

		$min_saving_amount  = round( ( $min_original_variation_price - $min_wholesale_price ) );
		
		if ( empty( $max_original_variation_price ) ) {
			$max_original_variation_price = 1;
		}

		if ( 0 == $min_original_variation_price ) {
			$min_saving_percent = 0;
		} else {
			$min_saving_percent = ( $min_original_variation_price - $min_wholesale_price ) / $min_original_variation_price * 100;
		}
		
		$max_saving_amount  = round( ( $max_original_variation_price - $max_wholesale_price ) );

		if ( 0 == $max_original_variation_price ) {
			$max_saving_percent = 0;
		} else {
			$max_saving_percent = ( $max_original_variation_price - $max_wholesale_price ) / $max_original_variation_price * 100;
		}


		//$min_quantity = get_post_meta( $prod_id, '_wwp_wholesale_min_quantity', true );
		$settings = get_option( 'wwp_wholesale_pricing_options', true );
		$actual   = ( isset( $settings['retailer_label'] ) && ! empty( $settings['retailer_label'] ) ) ? esc_html( $settings['retailer_label'] ) : esc_html__( 'Actual', 'woocommerce-wholesale-pricing' );
		$save     = ( isset( $settings['save_label'] ) && ! empty( $settings['save_label'] ) ) ? esc_html( $settings['save_label'] ) : esc_html__( 'Save Up To', 'woocommerce-wholesale-pricing' );
		$new      = ( isset( $settings['wholesaler_label'] ) && ! empty( $settings['wholesaler_label'] ) ) ? esc_html( $settings['wholesaler_label'] ) : esc_html__( 'New', 'woocommerce-wholesale-pricing' );
		$html     = '<div class="wwp-wholesale-pricing-details">';

		$wcv_max_price = $max_original_variation_price;
		$wcv_min_price = $min_original_variation_price;
		$charge_price =$product->get_meta('_subscription_period_interval');
		if ( 1 == $charge_price ) {
			$charge_price = '/';
		}
		$period = $product->get_meta('_subscription_period');
		$cancel = $product->get_meta('_subscription_length');
		$sign_up_fee = $product->get_meta('_subscription_sign_up_fee');
		$sign_up_fee_text = '';
		if ( ! empty( $sign_up_fee ) ) {
			$sign_up_fee_text = ' and a ' . get_woocommerce_currency_symbol() . ' ' . $sign_up_fee . ' sign-up fee';
		} 
		$cancelation = '';
		if ( ! empty( $cancel ) ) {
			$cancelation = ' for ' . $cancel . ' ' . $period;
		}
		// Tax display suffix function call
		$tax_display_suffix = wwp_get_tax_price_display_suffix( $product->get_id() );

		if ( 'yes' != $settings['retailer_disabled'] ) {
			if ( $wcv_min_price == $wcv_max_price ) {
				$html .= '<p><span class="retailer-text">' . esc_html__( $actual, 'woocommerce-wholesale-pricing' ) . '</span>: ' . wwp_strike_through_price_element('start') . wc_price( $wcv_min_price ) . ' ' . $product->get_price_suffix( get_post_meta( $product->get_id(), '_regular_price', true ) ) . wwp_strike_through_price_element('end') . '</p>';
			} else {
				$html .= '<p><span class="retailer-text">' . esc_html__( $actual, 'woocommerce-wholesale-pricing' ) . '</span>: ' . wwp_strike_through_price_element('start') . wc_price( $wcv_min_price ) . ' - ' . wc_price( $wcv_max_price ) . ' ' . $product->get_price_suffix( get_post_meta( $product->get_id(), '_regular_price', true ) ) . wwp_strike_through_price_element('end') . '</p>';
			}
		}

		if ( wc_price( $wholesale_product_variations[0] ) !== wc_price( $wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ] ) ) {
			$html .= '<p><b><span class="price-text">' . esc_html__( $new, 'woocommerce-wholesale-pricing' ) . '</span>: ' . wc_price( $wholesale_product_variations[0] ) . ' - ' . wc_price( $wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ] ) . ' ' . $product->get_price_suffix( $wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ] ) . $charge_price . ' ' . $period . $cancelation . $sign_up_fee_text . '</b></p>';
		} else {
			$html .= '<p><b><span class="price-text">' . esc_html__( $new, 'woocommerce-wholesale-pricing' ) . '</span>: ' . wc_price( $wholesale_product_variations[0] ) . ' ' . $product->get_price_suffix( $wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ] ) . '</b></p>';
		}

		if ( 'yes' != $settings['save_price_disabled'] ) {
			$html .= '<p><b><span class="save-price-text">' . esc_html__( $save, 'woocommerce-wholesale-pricing' ) . '</span>:  (' . round( max($up_to_save) ) . '%) </b></p>';
		}
		$html .= '</div>';
		return $html;
	}
endif;
	
if ( ! function_exists( 'wholesale_tire_prices' ) ) :
	function wholesale_tire_prices( $product ) {  
	
		$role                 = get_current_user_role_id();
		
		$ws_group = wwp_get_group_post_by_proid( $product->get_id(), get_current_user_id() );
		if ( $ws_group ) { 
			$group_id = isset( $ws_group[0] ) ? $ws_group[0] : '';
			if ( ! empty( $group_id ) ) {
				$discount_type = get_post_meta( $group_id, '_discount_type', true );
				$min_quantity = get_post_meta( $group_id, '_min_quantity', true );
				$max_quantity = get_post_meta( $group_id, '_max_quantity', true );
				$wwp_price = get_post_meta( $group_id, '_wwp_group_price', true );
				$group = array();
				if ( ! empty( $wwp_price ) ) {
					$group[$role] = array( array( 'min' => $min_quantity, 'max' => $max_quantity, 'price' => $wwp_price ), 'discount_type' => $discount_type, 'run' => 'group' );
					return $group;
				}
			}
		}

		$product_tier_pricing = get_post_meta( $product->get_id(), 'product_tier_pricing', true );
		
		if ( isset( $product_tier_pricing[ $role ] ) ) {
			if ( $product->get_type() == 'variable' ) {
				$data_wholesale = get_post_meta( $product->get_id(), 'wholesale_multi_user_pricing', true );
				if ( isset( $data_wholesale[ $role ] ) ) {
					foreach ( $product_tier_pricing[ $role ] as $product_data ) {
						foreach ( $product_data as $variable_product_data ) {
							if ( isset( $variable_product_data['price'] ) && ! empty( $variable_product_data['price'] ) ) {
								$product_tier_pricing[ $role ]['discount_type'] = $data_wholesale[ $role ]['discount_type'];
								$product_tier_pricing[ $role ]['run']           = 'product';
								return $product_tier_pricing;
							}
						}
					}               
				} 
			}

			if ( $product->get_type() == 'simple' ) {
				$data_wholesale = get_post_meta( $product->get_id(), 'wholesale_multi_user_pricing', true );
				if ( isset( $data_wholesale[ $role ] ) ) {
					foreach ( $product_tier_pricing[ $role ] as $product_data ) {
						if ( isset( $product_data['price'] ) && ! empty( $product_data['price'] ) ) {
							$product_tier_pricing[ $role ]['discount_type'] = $data_wholesale[ $role ]['discount_type'];
							$product_tier_pricing[ $role ]['run']           = 'product';
							return $product_tier_pricing;
						}
					}
				}
			}

			if ( $product->get_type() == 'subscription' ) {
				$data_wholesale = get_post_meta( $product->get_id(), 'wholesale_multi_user_pricing', true );
				if ( isset( $data_wholesale[ $role ] ) ) {
					foreach ( $product_tier_pricing[ $role ] as $product_data ) {
						if ( isset( $product_data['price'] ) && ! empty( $product_data['price'] ) ) {
							$product_tier_pricing[ $role ]['discount_type'] = $data_wholesale[ $role ]['discount_type'];
							$product_tier_pricing[ $role ]['run']           = 'product-subscription';
							return $product_tier_pricing;
						}
					}
				}
			}
		}

		$terms = get_the_terms( $product->get_id(), 'product_cat' );

		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$product_tier_pricing = get_term_meta( $term->term_id, 'category_tier_pricing', true );
				if ( isset( $product_tier_pricing['tier_pricing'][ $role ] ) ) {
					$data_wholesale = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );
					if ( isset( $data_wholesale[ $role ] ) ) {
						foreach ( $product_tier_pricing['tier_pricing'][ $role ] as $key => $value ) {
							if ( isset( $value['price'] ) && ! empty( $value['price'] ) ) {
								$product_tier_pricing['tier_pricing'][ $role ]['discount_type'] = $data_wholesale[ $role ]['discount_type'];
								$product_tier_pricing['tier_pricing'][ $role ]['run']           = 'category';
								return $product_tier_pricing['tier_pricing'];
							}
						}
					}   
				}
			}
		}

		$settings = get_option( 'wwp_wholesale_pricing_options', true );
		if ( isset( $settings['tier_pricing'] ) ) {
			if ( isset( $settings['tier_pricing'][ $role ] ) ) {
				$data_wholesale = get_option( 'wholesale_multi_user_pricing' );
				if ( isset( $data_wholesale[ $role ] ) ) {
					foreach ( $settings['tier_pricing'][ $role ] as $key => $value ) {
						if ( isset( $value['price'] ) && ! empty( $value['price'] ) ) {
							$settings['tier_pricing'][ $role ]['discount_type'] = $data_wholesale[ $role ]['discount_type'];
							$settings['tier_pricing'][ $role ]['run']           = 'global';
							return $settings['tier_pricing'];
	
						}
					}
				}
			}
		}
		return false;
	}
endif;

if ( ! function_exists( 'get_cart_qty' ) ) :
	function get_cart_qty( $product_id ) {

		if ( ! current_user_can( 'edit_post', $product_id ) && ( is_cart() || is_checkout() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) ) {
		
			if ( isset( WC()->cart ) ) {
				foreach ( WC()->cart->get_cart() as $cart_item ) {
					if ( $cart_item['product_id'] == $product_id ) {
						return $cart_item['quantity'];
					}
				}
			}
		}
		
		$data = $_REQUEST;
		if ( isset( $data['data'] ) && is_admin() ) {
			foreach ($data['data'] as $key => $value ) {
				if ( isset( $value['id'] ) && $value['id'] == $product_id ) {
					return $value['qty'];
				}
			}
		}
		/**
		* Hooks
		*
		* @since 3.0
		*/
		return apply_filters( 'wwp_cart_quantity', 1, $product_id );
	}
endif;

if ( ! function_exists( 'get_cart_variation_qty' ) ) :
	function get_cart_variation_qty( $current_variation_id ) {
		if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || is_cart() || is_checkout() ) {
			if ( isset( WC()->cart ) ) {
				foreach ( WC()->cart->get_cart() as $cart_item ) {
					if ( $cart_item['variation_id'] == $current_variation_id ) {
						return $cart_item['quantity'];
					}
				}
			}
		}
	}
endif;


if ( ! function_exists( 'tire_wholesale_qty_price' ) ) :
	function tire_wholesale_qty_price( $data, $product ) {
		$tire_variation_price = array();
		if ( ! empty( $data ) ) {
			
			if ( is_array( $data ) ) {
				foreach ( $data as $keys => $value ) {
					if ( isset( $value['price'] ) && ! empty( $value['price'] ) ) {
						$tire_variation_price[] = $value['price'];
					}
				}
			} else {
				$tire_variation_price[] = $data;
			}
		} 

		if ( $tire_variation_price ) {
			return min( $tire_variation_price );
		} else {
			return false;
		}
	}
endif;
 
if ( ! function_exists( 'tire_wholesale_qty_price_simple' ) ) :
	function tire_wholesale_qty_price_simple( $data, $product ) {
		
		$tire_variation_price = array();
		if ( $data ) {
			foreach ( $data as $keys => $value ) {
				if ( isset( $value['price'] ) && ! empty( $value['price'] ) ) {
					$tire_variation_price[] = $value['price'];
				}
			}
		}
		
		if ( isset ( $data[ $product->get_id() ] ) ) {
			foreach ( $data[ $product->get_id() ] as $keys => $value ) {
				if ( isset( $value['price'] ) && ! empty( $value['price'] ) ) {
					$tire_variation_price[] = $value['price'];
				}
			}
		}
		
		if ( $tire_variation_price ) {
			return min( $tire_variation_price );
		} else {
			return false;
		}
	}
endif;

if ( ! function_exists( 'tire_variable_wholesale_product_price' ) ) :
	function tire_variable_wholesale_product_price( $current_variation_id, $product, $type = '' ) {  
		
		/**
		* Hooks
		*
		* @since 3.0
		*/
		if ( apply_filters( 'wwp_is_admin_product_price', is_admin() ) ) {
			return;
		}
		$role                         = get_current_user_role_id();
		$product_variations           = $product->get_children();
		$product_id                   = $product->get_id();
		$main_product_id              = wp_get_post_parent_id( $product_id );
		$wholesale_product_variations = array();
		$original_variation_price     = array();
		$prices                       = array();
		$qty                          = 1;
		/**
		* Hooks
		*
		* @since 3.0
		*/
		$is_cart_or_checkout_page =   apply_filters( 'wwp_is_cart_or_checkout_page', ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || is_cart() || is_checkout() ) ? true : false );   
		$variation_id             = $product_id;

		if ( 'product' == $type ) {
			if ( 'product' == get_post_type( $product_id ) ) {
				$qty                  = get_cart_qty( $product_id );
				$product_tier_pricing = get_post_meta( $product_id, 'product_tier_pricing', true );
				if ( isset( $product_tier_pricing[ $role ] ) ) {
					$data = get_post_meta( $product_id, 'wholesale_multi_user_pricing', true );
					if ( isset( $data[ $role ] ) ) {
						foreach ( $product_tier_pricing[ $role ] as $product_data ) { 
							if ( isset( $product_data['price'] ) && ! empty( $product_data['price'] ) ) { 
								if ( $is_cart_or_checkout_page ) { 
									if ( $qty >= $product_data['min'] && $qty <= $product_data['max'] ) {
										return tire_get_type( $product_id, $product, $product_data['price'], get_post_meta( $product_id, '_regular_price', true ), $data[ $role ]['discount_type'] );
									}
								} else { 
									return tire_get_type( $product_id, $product, tire_wholesale_qty_price_simple( $product_tier_pricing[ $role ], $product ), get_post_meta( $product_id, '_regular_price', true ), $data[ $role ]['discount_type'] );
								}
							}
						}
					}
				}
			} elseif ( 'product_variation' == get_post_type( $product_id ) ) {
			
				$variation_id         = $product_id;
				$product_id           = wp_get_post_parent_id( $product_id );
				$qty                  = get_cart_variation_qty( $variation_id );
				$product_tier_pricing = get_post_meta( $product_id, 'product_tier_pricing', true );
				$data                 = get_post_meta( $product_id, 'wholesale_multi_user_pricing', true );
				if ( isset( $product_tier_pricing[ $role ][ $variation_id ] ) ) {
					if ( isset( $data[ $role ] ) ) { 
						foreach ( $product_tier_pricing[ $role ][ $variation_id ] as $keys => $value ) {
							if ( isset( $value['price'] ) && ! empty( $value['price'] ) ) { 
								if ( $is_cart_or_checkout_page ) {
									if ( $qty >= $value['min'] && $qty <= $value['max'] ) {
										return tire_get_type( $variation_id, $product, $value['price'], get_post_meta( $variation_id, '_regular_price', true ), $data[ $role ]['discount_type'] );
									}
								} else { 
									return tire_get_type( $variation_id, $product, tire_wholesale_qty_price( $product_tier_pricing[ $role ][ $variation_id ], $product ) , get_post_meta( $variation_id, '_regular_price', true ), $data[ $role ]['discount_type'] );
								}
							}
						}
					}
				}
			}
		} elseif ( 'category' == $type ) {
			 
			if ( 'product_variation' == get_post_type( $product_id ) ) {  
				$variation_id = $product_id;
				$product_id   = wp_get_post_parent_id( $product_id );
				$qty = get_cart_variation_qty( $variation_id );

			} else {
				$qty = get_cart_qty( $product_id );
			}
			
			$terms = get_the_terms( $product_id, 'product_cat' );
			
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				
				foreach ( $terms as $term ) { 
					$product_tier_pricing = get_term_meta( $term->term_id, 'category_tier_pricing', true );
					$data_wholesale       = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );
					if ( isset( $product_tier_pricing['tier_pricing'][ $role ] ) ) {
						if ( isset( $data_wholesale[ $role ] ) ) {
							foreach ( $product_tier_pricing['tier_pricing'][ $role ] as $key => $value ) {
								if ( isset( $value['price'] ) && ! empty( $value['price'] ) ) {
									if ( $is_cart_or_checkout_page ) {
										if ( $qty >= $value['min'] && $qty <= $value['max'] ) { 
											return tire_get_type( $variation_id, $product, $value['price'], get_post_meta( $variation_id, '_regular_price', true ) , $data_wholesale[ $role ]['discount_type'] );
										}
									} else { 
										return tire_get_type( $variation_id, $product, tire_wholesale_qty_price_simple( $product_tier_pricing['tier_pricing'][ $role ], $product ), get_post_meta( $variation_id, '_regular_price', true ), $data_wholesale[ $role ]['discount_type'] );
									}
								}
							}
						}
					}
				}
			}
		} elseif ( 'global' == $type ) {
		
			if ( 'product_variation' == get_post_type( $product_id ) ) {  
				$variation_id = $product_id;
				$product_id   = wp_get_post_parent_id( $product_id );
			}
			$settings = get_option( 'wwp_wholesale_pricing_options', true );
			if ( isset( $settings['tier_pricing'] ) ) {
				if ( isset( $settings['tier_pricing'][ $role ] ) ) {
					$qty            = get_cart_qty( $current_variation_id );
					$data_wholesale = get_option( 'wholesale_multi_user_pricing' );
					if ( isset( $data_wholesale[ $role ] ) ) {
						foreach ( $settings['tier_pricing'][ $role ] as $key => $value ) {
							if ( isset( $value['price'] ) && ! empty( $value['price'] ) ) {
								if ( $is_cart_or_checkout_page ) {  
	
									if ( $qty >= $value['min'] && $qty <= $value['max'] ) {      
										return tire_get_type( $variation_id, $product, $value['price'], get_post_meta( $variation_id, '_regular_price', true ), $data_wholesale[ $role ]['discount_type'] );
									}
								} else { 
									return tire_get_type( $variation_id, $product, tire_wholesale_qty_price( $settings['tier_pricing'][ $role ], $product ), get_post_meta( $variation_id, '_regular_price', true ), $data_wholesale[ $role ]['discount_type'] );
								}
							}
						}
					}
				}
			}
		} elseif ( 'group' == $type ) {
			$ws_group = wwp_get_group_post_by_proid( $product_id, get_current_user_id() );
			if ( $ws_group ) { 
				$group_id = isset( $ws_group[0] ) ? $ws_group[0] : '';
				if ( ! empty( $group_id ) ) {
					$qty            = get_cart_qty( $current_variation_id );
					$discount_type = get_post_meta( $group_id, '_discount_type', true );
					$min_quantity = get_post_meta( $group_id, '_min_quantity', true );
					$max_quantity = get_post_meta( $group_id, '_max_quantity', true );
					$wwp_price = get_post_meta( $group_id, '_wwp_group_price', true );

					if ( ! empty( $wwp_price ) ) {
						if ( $is_cart_or_checkout_page ) { 
							if ( $qty >= $min_quantity && $qty <= $max_quantity ) {
								return tire_get_type( $product_id, $product, $wwp_price, get_post_meta( $product_id, '_regular_price', true ), $discount_type );
							}
						} else {
							return tire_get_type( $variation_id, $product, tire_wholesale_qty_price( $wwp_price, $product ), get_post_meta( $variation_id, '_regular_price', true ), $discount_type );
						}
					}
				}
			}

		}
	}
endif;

if ( ! function_exists( 'variable_wholesale_product_price_html' ) ) :
	function variable_wholesale_product_price_html( $wholesale_product_variations, $original_variation_price, $product ) {

		sort( $wholesale_product_variations );
		sort( $original_variation_price );

		if ( class_exists( 'Wpexperts\CurrencySwitcherForWoocommerce\WCCS' ) ) {
			global $WCCS;
			if ( is_array( $wholesale_product_variations ) && count( $wholesale_product_variations ) > 0 ) {
				foreach ( $wholesale_product_variations as $key => $price ) {
					$wholesale_product_variations[$key] = $WCCS->wccs_price_conveter((float) $price);
				}
			}
			
			if ( is_array( $original_variation_price ) && count($original_variation_price) > 0 ) {
				foreach ( $original_variation_price as $key => $price ) {
					$original_variation_price[$key] = $WCCS->wccs_price_conveter((float) $price);
				}
			}
						
		}

		/**
		 * Filter
		 * 
		 *  @since 2.5
		 */
		$check_page = apply_filters( 'wwp_check_page_for_inclusive_tax', false );

		$order_id = isset( $_REQUEST['order_id'] ) ? sanitize_text_field( $_REQUEST['order_id'] ) : get_the_ID();
		
		// orignal price add tax
		if ( ( ( $check_page || is_shop() || is_product() || is_product_category() || ( 'shop_order' != get_post_type( $order_id ) && shortcode_exists( 'wc_products_table' ) ) ) && 'incl' === get_option( 'woocommerce_tax_display_shop' ) ) || ( ( is_cart() || is_checkout() ) && 'incl' === get_option( 'woocommerce_tax_display_cart' ) ) ) {
			$original_variation_price[0]                                        = wwp_get_price_including_tax( $product, array( 'price' => $original_variation_price[0] ) );
			$original_variation_price[ count( $original_variation_price ) - 1 ] = wwp_get_price_including_tax( $product, array( 'price' => $original_variation_price[ count( $original_variation_price ) - 1 ] ) );
			// wholesale price add tax
			$wholesale_product_variations[0] = wwp_get_price_including_tax( $product, array( 'price' => $wholesale_product_variations[0] ) );

			$wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ] = wwp_get_price_including_tax( $product, array( 'price' => $wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ] ) );
		} else {
			$original_variation_price[0]                                        = wc_get_price_excluding_tax( $product, array( 'price' => $original_variation_price[0] ) );
			$original_variation_price[ count( $original_variation_price ) - 1 ] = wc_get_price_excluding_tax( $product, array( 'price' => $original_variation_price[ count( $original_variation_price ) - 1 ] ) );
			// wholesale price add tax
			$wholesale_product_variations[0] = wc_get_price_excluding_tax( $product, array( 'price' => $wholesale_product_variations[0] ) );

			$wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ] = wc_get_price_excluding_tax( $product, array( 'price' => $wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ] ) );
		}
		// $original_variation_price[0]                                        = wwp_get_price_including_tax( $product, array( 'price' => $original_variation_price[0] ) );
		// $original_variation_price[ count( $original_variation_price ) - 1 ] = wwp_get_price_including_tax( $product, array( 'price' => $original_variation_price[ count( $original_variation_price ) - 1 ] ) );
		// // wholesale price add tax
		// $wholesale_product_variations[0] = wwp_get_price_including_tax( $product, array( 'price' => $wholesale_product_variations[0] ) );

		// $wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ] = wwp_get_price_including_tax( $product, array( 'price' => $wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ] ) );


		$min_wholesale_price          = $wholesale_product_variations[0];
		$max_wholesale_price          = $wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ];
		$min_original_variation_price = ( empty( $original_variation_price[0] ) || 0 == $original_variation_price[0] ) ? 1 : $original_variation_price[0];
		$max_original_variation_price = $original_variation_price[ count( $original_variation_price ) - 1 ];
		$max_original_variation_price = ( empty( $max_original_variation_price ) || 0 == $max_original_variation_price ) ? 1 : $max_original_variation_price;
		
		if ( 0 == $min_original_variation_price ) {
			$min_original_variation_price = 1;
		}

		if ( 0 == $max_original_variation_price ) {
			$max_original_variation_price = 1;
		}
		
		
		$min_saving_amount  = round( ( $min_original_variation_price - $min_wholesale_price ) );
		$min_saving_percent = ( $min_original_variation_price - $min_wholesale_price ) / $min_original_variation_price * 100;

		$max_saving_amount  = round( ( $max_original_variation_price - $max_wholesale_price ) );
		$max_saving_percent = ( $max_original_variation_price - $max_wholesale_price ) / $max_original_variation_price * 100;

		$min_max_sort = array( $min_saving_percent, $max_saving_percent );
		$min_saving_percent = min( $min_max_sort );
		$max_saving_percent = max( $min_max_sort );

		$settings = get_option( 'wwp_wholesale_pricing_options', true );
		$actual   = ( isset( $settings['retailer_label'] ) && ! empty( $settings['retailer_label'] ) ) ? esc_html( $settings['retailer_label'] ) : esc_html__( 'Actual', 'woocommerce-wholesale-pricing' );
		$save     = ( isset( $settings['save_label'] ) && ! empty( $settings['save_label'] ) ) ? esc_html( $settings['save_label'] ) : esc_html__( 'Save', 'woocommerce-wholesale-pricing' );
		$new      = ( isset( $settings['wholesaler_label'] ) && ! empty( $settings['wholesaler_label'] ) ) ? esc_html( $settings['wholesaler_label'] ) : esc_html__( 'New', 'woocommerce-wholesale-pricing' );
		$html     = '';
		
		/**
		* Hooks
		*
		* @since 3.0
		*/
		$html .= do_action( 'wwp_before_pricing', $product );
		$html  = '<div class="wwp-wholesale-pricing-details">';

		$wcv_max_price = $max_original_variation_price;
		$wcv_min_price = $min_original_variation_price;

		if ( 'yes' != $settings['retailer_disabled'] ) {
			if ( $wcv_min_price == $wcv_max_price ) {
				$html .= '<p><span class="retailer-text">' . esc_html__( $actual, 'woocommerce-wholesale-pricing' ) . '</span>: ' . wwp_strike_through_price_element('start') . wc_price( $wcv_min_price ) . ' ' . wwp_strike_through_price_element('end') . '</p>';
			} else {
				$html .= '<p><span class="retailer-text">' . esc_html__( $actual, 'woocommerce-wholesale-pricing' ) . '</span>: ' . wwp_strike_through_price_element('start') . wc_price( $wcv_min_price ) . '  - ' . wc_price( $wcv_max_price ) . ' ' . wwp_strike_through_price_element('end') . '</p>';
			}
		}

		if ( wc_price( $wholesale_product_variations[0] ) !== wc_price( $wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ] ) ) {
			$html .= '<p><b><span class="price-text">' . esc_html__( $new, 'woocommerce-wholesale-pricing' ) . '</span>: ' . wc_price( $wholesale_product_variations[0] ) . '  - ' . wc_price( $wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ] ) . ' </b></p>';
		} else {
			$html .= '<p><b><span class="price-text">' . esc_html__( $new, 'woocommerce-wholesale-pricing' ) . '</span>: ' . wc_price( $wholesale_product_variations[0] ) . ' </b></p>';
		}

		if ( 'yes' != $settings['save_price_disabled'] ) {
			if ( round( $min_saving_percent ) !== round( $max_saving_percent ) ) {
				/**
				* Hooks
				*
				* @since 2.6.1
				*/
				$save_variable = apply_filters( 'wwp_remove_save_variable_wholesale_percent', ' (' . round( $min_saving_percent ) . '% - ' . round( $max_saving_percent ) . '%)' );
				$html .= '<p><b><span class="save-price-text">' . esc_html__( $save, 'woocommerce-wholesale-pricing' ) . '</span>: ' . $save_variable . '  </b></p>';
			} else {
				/**
				* Hooks
				*
				* @since 2.6.1
				*/
				$save_variable = apply_filters( 'wwp_remove_save_variable_wholesale_percent_same', '(' . round( $min_saving_percent ) . '%)' );
				$html .= '<p><b><span class="save-price-text">' . esc_html__( $save, 'woocommerce-wholesale-pricing' ) . '</span>: ' . $save_variable . ' </b></p>';
			}
		}
		if ( wwp_guest_wholesale_pricing_enabled() ) {
			$min = ( float ) $min_original_variation_price - ( float ) $wholesale_product_variations[0];
			$max =  ( float ) $max_original_variation_price -  ( float ) $wholesale_product_variations[ count( $wholesale_product_variations ) - 1 ];
			$text = str_replace( '{saved_amount}', '<strong>' . wc_price( $min ) . ' - ' . wc_price( $max ) . '</strong>', wwp_guest_wholesale_pricing_enabled( 'message' ) );
			$text = str_replace( '{saved_percentage}', '<strong>' . round( $max_saving_percent ) . '%</strong>', $text );
			$html .= '<p>' . wp_kses( $text, 'woocommerce-wholesale-pricing' ) . '</p>';
		}
		$html .= '</div>';
		
		/**
		* Hooks
		*
		* @since 3.0
		*/
		$html .= do_action( 'wwp_after_pricing', $product );
		/**
		* Hooks
		*
		* @since 3.0
		*/
		return apply_filters( 'wwp_product_price_variable', $html );
	}
endif;

if ( ! function_exists( 'simple_wholesale_product_price_html' ) ) :
	function simple_wholesale_product_price_html( $discountType = '', $wholesale_price = '', $r_price = '', $min = '', $product = '' ) {
	 
		$r_price = get_post_meta( $product->get_id(), '_regular_price', true );
		
		if ( class_exists( 'Wpexperts\CurrencySwitcherForWoocommerce\WCCS' ) ) {
			global $WCCS;
			$r_price = $WCCS->wccs_price_conveter($r_price);
		}

		$Wprice  = $product->get_regular_price();
		
		if ( is_admin() ) {
			$Wprice =   wholesale_type_price( $r_price, $discountType, $wholesale_price ) ;
		}
		
		if ( empty( $r_price ) || '0' == $r_price ) {
			return wc_price($r_price);
		}

		/**
		 * Filter
		 * 
		 *  @since 2.5
		 */
		$check_page = apply_filters( 'wwp_check_page_for_inclusive_tax', false );
		$order_id = isset( $_REQUEST['order_id'] ) ? sanitize_text_field( $_REQUEST['order_id'] ) : get_the_ID();
		// Normal price include tax
		if ( ( ( $check_page || is_shop() || is_product() || is_product_category() || ( 'shop_order' != get_post_type( $order_id ) && shortcode_exists( 'wc_products_table' ) ) ) && 'incl' === get_option( 'woocommerce_tax_display_shop' ) ) || ( ( is_cart() || is_checkout() ) && 'incl' === get_option( 'woocommerce_tax_display_cart' ) ) ) {
			$r_price = wwp_get_price_including_tax( $product, array( 'price' => $r_price ) );
			$Wprice  = wwp_get_price_including_tax( $product, array( 'price' => $Wprice ) );
		} else {
			$r_price = wc_get_price_excluding_tax( $product, array( 'price' => $r_price ) );
			$Wprice  = wc_get_price_excluding_tax( $product, array( 'price' => $Wprice ) );
		}

		/**
		 * Filter
		 * 
		 *  @since 2.6
		 */
		$r_price = apply_filters( 'wwp_regular_price_on_product_page', $r_price, $product );
		/**
		 * Filter
		 * 
		 *  @since 2.6
		 */
		$Wprice = apply_filters( 'wwp_wholesale_price_on_product_page', $Wprice, $product );

		$saving_amount  = ( $r_price - $Wprice );
		$saving_amount  = number_format( (float) $saving_amount, 2, '.', '' );
		$saving_percent = ( $r_price - $Wprice ) / $r_price * 100;
		$saving_percent = number_format( (float) $saving_percent, 2, '.', '' );
		$settings       = get_option( 'wwp_wholesale_pricing_options', true );
		$actual         = ( isset( $settings['retailer_label'] ) && ! empty( $settings['retailer_label'] ) ) ? esc_html( $settings['retailer_label'] ) : esc_html__( 'Actual', 'woocommerce-wholesale-pricing' );
		$save           = ( isset( $settings['save_label'] ) && ! empty( $settings['save_label'] ) ) ? esc_html( $settings['save_label'] ) : esc_html__( 'Save', 'woocommerce-wholesale-pricing' );
		$new            = ( isset( $settings['wholesaler_label'] ) && ! empty( $settings['wholesaler_label'] ) ) ? esc_html( $settings['wholesaler_label'] ) : esc_html__( 'New', 'woocommerce-wholesale-pricing' );
		$html           = '';
		if ( ! empty( $Wprice ) ) {
			/**
			* Hooks
			*
			* @since 3.0
			*/
			$html .= do_action( 'wwp_before_pricing', $product );
			$html .= '<div class="wwp-wholesale-pricing-details">';
			if ( 'yes' != $settings['retailer_disabled'] ) {
				$html .= '<p><span class="retailer-text">' . esc_html__( $actual, 'woocommerce-wholesale-pricing' ) . '</span>: ' . wwp_strike_through_price_element('start') . wc_price( $r_price ) . ' ' . $product->get_price_suffix( get_post_meta( $product->get_id(), '_regular_price', true ) ) . wwp_strike_through_price_element('end') . '</p>';
			}
			$html .= '<p><span class="price-text">' . esc_html__( $new, 'woocommerce-wholesale-pricing' ) . '</span>: ' . wc_price( $Wprice ) . ' ' . $product->get_price_suffix() . '</p>';
			if ( 'yes' != $settings['save_price_disabled'] ) {
				/**
				* Hooks
				*
				* @since 2.6.1
				*/
				$save_percent = apply_filters( 'wwp_remove_save_wholesale_percent', ' (' . round( $saving_percent ) . '%)' );
				$html .= '<p><b><span class="save-price-text">' . esc_html__( $save, 'woocommerce-wholesale-pricing' ) . '</span>: ' . wc_price( $saving_amount ) . $save_percent . '</b></p>';
			}
			if ( $min > 1 ) {
				if ( $product->get_type() == 'simple' || $product->get_type() == 'subscription' ) {
				
					/* translators: %s: wholesale price on minmum */
					$qtytext = sprintf( esc_html__( 'Wholesale price will only be applied to a minimum quantity of %1$s products', 'woocommerce-wholesale-pricing' ), $min );
					
					/**
					* Hooks
					*
					* @since 3.0
					*/
					$html .= apply_filters( 'wwp_product_minimum_quantity_text', '<p style="font-size: 10px;">' . $qtytext . '</p>', $min );
				}
			}
			if ( wwp_guest_wholesale_pricing_enabled() ) {
				$text = str_replace( '{saved_amount}', '<strong>' . wc_price( $saving_amount ) . '</strong>', wwp_guest_wholesale_pricing_enabled( 'message' ) );
				$text = str_replace( '{saved_percentage}', '<strong>' . round( $saving_percent ) . '%</strong>', $text );
				$html .= '<p>' . wp_kses( $text, 'woocommerce-wholesale-pricing' ) . '</p>';
			}
			$html .= '</div>';
			
			/**
			* Hooks
			*
			* @since 3.0
			*/
			$html .= do_action( 'wwp_after_pricing', $product );
		}
		
		/**
		* Hooks
		*
		* @since 3.0
		*/
		return apply_filters( 'wwp_product_price_simple', $html, $product );
	}
endif;

if ( ! function_exists( 'simple_subsc_wholesale_product_price_html' ) ) :
	function simple_subsc_wholesale_product_price_html( $discountType = '', $wholesale_price = '', $min = '', $product = '' ) {

		$charge_price =$product->get_meta('_subscription_period_interval');
		if ( 1 == $charge_price ) {
			$charge_price = '/';
		}
		$period = $product->get_meta('_subscription_period');
		$cancel = $product->get_meta('_subscription_length');
		$sign_up_fee = $product->get_meta('_subscription_sign_up_fee');
		$cancelation = '';
		if ( ! empty( $cancel ) ) {
			$cancelation = ' for ' . $cancel . ' ' . $period;
		}
	 
		$r_price = get_post_meta( $product->get_id(), '_regular_price', true );
		
		if ( class_exists( 'Wpexperts\CurrencySwitcherForWoocommerce\WCCS' ) ) {
			global $WCCS;
			$r_price = $WCCS->wccs_price_conveter($r_price);
		}

		$Wprice  = $product->get_regular_price();
		
		if ( is_admin() ) {
			$Wprice =   wholesale_type_price( $r_price, $discountType, $wholesale_price ) ;
		}
		
		if ( empty( $r_price ) || '0' == $r_price ) {
			return wc_price($r_price);
		}

		/**
		 * Filter
		 * 
		 *  @since 2.5
		 */
		$check_page = apply_filters( 'wwp_check_page_for_inclusive_tax', false );
		$order_id = isset( $_REQUEST['order_id'] ) ? sanitize_text_field( $_REQUEST['order_id'] ) : get_the_ID();
		// Normal price include tax
		if ( ( ( $check_page || is_shop() || is_product() || is_product_category() || ( 'shop_order' != get_post_type( $order_id ) && shortcode_exists( 'wc_products_table' ) ) ) && 'incl' === get_option( 'woocommerce_tax_display_shop' ) ) || ( ( is_cart() || is_checkout() ) && 'incl' === get_option( 'woocommerce_tax_display_cart' ) ) ) {
			$r_price = wwp_get_price_including_tax( $product, array( 'price' => $r_price ) );
			$Wprice  = wwp_get_price_including_tax( $product, array( 'price' => $Wprice ) );
		} else {
			$r_price = wc_get_price_excluding_tax( $product, array( 'price' => $r_price ) );
			$Wprice  = wc_get_price_excluding_tax( $product, array( 'price' => $Wprice ) );
		}
		$saving_amount  = ( $r_price - $Wprice );
		$saving_amount  = number_format( (float) $saving_amount, 2, '.', '' );
		$saving_percent = ( $r_price - $Wprice ) / $r_price * 100;
		$saving_percent = number_format( (float) $saving_percent, 2, '.', '' );
		$settings       = get_option( 'wwp_wholesale_pricing_options', true );
		$actual         = ( isset( $settings['retailer_label'] ) && ! empty( $settings['retailer_label'] ) ) ? esc_html( $settings['retailer_label'] ) : esc_html__( 'Actual', 'woocommerce-wholesale-pricing' );
		$save           = ( isset( $settings['save_label'] ) && ! empty( $settings['save_label'] ) ) ? esc_html( $settings['save_label'] ) : esc_html__( 'Save', 'woocommerce-wholesale-pricing' );
		$new            = ( isset( $settings['wholesaler_label'] ) && ! empty( $settings['wholesaler_label'] ) ) ? esc_html( $settings['wholesaler_label'] ) : esc_html__( 'New', 'woocommerce-wholesale-pricing' );
		$html           = '';
		if ( ! empty( $Wprice ) ) {
		
			/**
			* Hooks
			*
			* @since 3.0
			*/
			$html .= do_action( 'wwp_before_pricing', $product );
			$sign_up_fee_text = !empty($sign_up_fee) ? ' and a ' . get_woocommerce_currency_symbol() . $sign_up_fee . ' sign-up fee' : '';
			$html .= '<div class="wwp-wholesale-pricing-details">';
			if ( 'yes' != $settings['retailer_disabled'] ) {
				$html .= '<p><span class="retailer-text">' . esc_html__( $actual, 'woocommerce-wholesale-pricing' ) . '</span>: ' . wwp_strike_through_price_element('start') . wc_price( $r_price ) . ' ' . $product->get_price_suffix( get_post_meta( $product->get_id(), '_regular_price', true ) ) . wwp_strike_through_price_element('end') . '</p>';
			}
			$html .= sprintf( '<p><span class="price-text">%s</span>: %s %s%s%s%s%s</p>', esc_html__( $new, 'woocommerce-wholesale-pricing' ), wc_price( $Wprice ), $product->get_price_suffix(), $charge_price, $period, $cancelation, $sign_up_fee_text );
			if ( 'yes' != $settings['save_price_disabled'] ) {
				$html .= '<p><b><span class="save-price-text">' . esc_html__( $save, 'woocommerce-wholesale-pricing' ) . '</span>: (' . round( $saving_percent ) . '%) </b></p>';
			}
			if ( $min > 1 ) {
				if ( $product->get_type() == 'simple' || $product->get_type() == 'subscription' ) {
				
					/* translators: %s: wholesale price on minmum */
					$qtytext = sprintf( esc_html__( 'Wholesale price will only be applied to a minimum quantity of %1$s products', 'woocommerce-wholesale-pricing' ), $min );
					
					/**
					* Hooks
					*
					* @since 3.0
					*/
					$html .= apply_filters( 'wwp_product_minimum_quantity_text', '<p style="font-size: 10px;">' . $qtytext . '</p>', $min );
				}
			}
			if ( wwp_guest_wholesale_pricing_enabled() ) {
				$text = str_replace( '{saved_amount}', '<strong>' . wc_price( $saving_amount ) . '</strong>', wwp_guest_wholesale_pricing_enabled( 'message' ) );
				$text = str_replace( '{saved_percentage}', '<strong>' . round( $saving_percent ) . '%</strong>', $text );
				$html .= '<p>' . wp_kses( $text, 'woocommerce-wholesale-pricing' ) . '</p>';
			}
			$html .= '</div>';
			
			/**
			* Hooks
			*
			* @since 3.0
			*/
			$html .= do_action( 'wwp_after_pricing', $product );
		}
		
		/**
		* Hooks
		*
		* @since 3.0
		*/
		return apply_filters( 'wwp_product_price_simple', $html, $product );
	}
endif;

if ( ! function_exists( 'is_wholesale_product' ) ) :
	function is_wholesale_product( $product_id = '' ) {
		$role = get_current_user_role_id();
		$data = get_post_meta( $product_id, 'wholesale_multi_user_pricing', true );
		if ( isset( $data[ $role ] ) ) {
			return true;
		}
		$terms = get_the_terms( $product_id, 'product_cat' );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$data = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );
				if ( isset( $data[ $role ] ) && isset( $data[ $role ]['discount_type'] ) && isset( $data[ $role ]['wholesale_price'] ) ) {
					return true;
				}
			}
		}
		$data = get_option( 'wholesale_multi_user_pricing' );
		if ( isset( $data[ $role ] ) && isset( $data[ $role ]['discount_type'] ) && isset( $data[ $role ]['wholesale_price'] ) ) {
			return true;
		}
		return false;
	}
endif;

function calculate_percentage( $price, $wholesale_price ) {
	if ( empty( $price ) || empty( $wholesale_price ) ) {
		return false;
	}
	$wholesale_price = $price * $wholesale_price / 100;
	$saving_percent  = ( $price - $wholesale_price ) / $price * 100;
	$saving_percent  = number_format( (float) $saving_percent, 2, '.', '' );
	return round( $saving_percent );
}

if ( ! function_exists( 'get_wholesale_percentage' ) ) :
	function get_wholesale_percentage( $product_id = '' ) {
		if ( is_wholesale_product($product_id) ) {
			$role         = get_current_user_role_id();
			$variation_id = $product_id;
			$data         = get_post_meta( $product_id, 'wholesale_multi_user_pricing', true );
			if ( isset( $data[ $role ] ) && 'product' == get_post_type( $product_id ) && isset( $data[ $role ]['discount_type'] ) && isset( $data[ $role ]['wholesale_price'] ) ) {
				$wholesale = $data[ $role ];
				$price     = get_post_meta( $product_id, '_regular_price', true );
				return calculate_percentage( $price, $wholesale['wholesale_price'] );
			} elseif ( 'product_variation' == get_post_type( $product_id ) ) { 
				if ( isset( $data[ $role ] ) ) {  
					if ( isset( $data[ $role ][ $product_id ]['wholesaleprice'] ) && !empty( $data[ $role ][ $product_id ]['wholesaleprice'] ) ) { 
						$wholesale = $data[ $role ][ $product_id ]; 
						$price     = get_post_meta( $product_id, '_regular_price', true );
						return calculate_percentage( $price, $wholesale['wholesaleprice'] );
					}
				}
			}
			if ( 'product_variation' == get_post_type( $product_id ) ) {  
				$variation_id = $product_id;
				$product_id   = wp_get_post_parent_id( $product_id );
			}
			
			$terms = get_the_terms( $product_id, 'product_cat' );
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				foreach ( $terms as $term ) { 
					$data = get_term_meta( $term->term_id, 'wholesale_multi_user_pricing', true );
					if ( isset( $data[ $role ] ) && isset( $data[ $role ]['discount_type'] ) && isset( $data[ $role ]['wholesale_price'] ) ) {
						$wholesale = $data[ $role ];
						$price     = get_post_meta( $variation_id, '_regular_price', true ); 
						return calculate_percentage( $price, $wholesale['wholesale_price'] );
					}
				}
			}
			
			$data = get_option( 'wholesale_multi_user_pricing' );
			if ( isset( $data[ $role ] ) && isset( $data[ $role ]['discount_type'] ) && isset( $data[ $role ]['wholesale_price'] ) ) {
				$price = get_post_meta( $variation_id, '_regular_price', true );
				return calculate_percentage( $price, $data[ $role ]['wholesale_price'] );
			}
		}
		return false;
	}
endif;

if ( ! function_exists( 'get_products_by_meta' ) ) :

	function get_products_by_meta( $key = null, $value = null ) {
		global $wpdb;

		if ( ! is_null( $key ) && ! is_null( $value ) ) {
			$result = $wpdb->get_results( 
				$wpdb->prepare(
					"SELECT DISTINCT p.ID AS id FROM {$wpdb->prefix}posts AS p LEFT JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id WHERE p.post_type = %s AND p.post_status = %s AND pm.meta_key = %s AND pm.meta_value = %s", 'product', 'publish', $key, $value 
				),
				ARRAY_A
			);
		} else {
			$result = $wpdb->get_results( 
				$wpdb->prepare(
					"SELECT p.ID AS id FROM {$wpdb->prefix}posts AS p WHERE p.post_type = %s AND p.post_status = %s", 'product', 'publish'
				),
				ARRAY_A
			);
		}

		$all_ids = wp_list_pluck( (array) $result, 'id' );

		return $all_ids;
	}

endif; 

if ( ! function_exists( 'wwp_registration_field_required_attr' ) ) {
	
	function wwp_registration_field_required_attr( $data = '', $attr = 'required' ) {

		if ( isset( $data ) && 'yes' == $data ) {
			if ( 'required' == $attr ) {
				echo 'required';
			} else {
				echo '<span class="required">*</span>';
			}
		} else if ( 'span' == $attr ) {
			echo '<span class="optional">(optional)</span>';
		}

		return null;
	}

}

if ( ! function_exists( 'wwp_get_active_payment_methods' ) ) {
	function wwp_get_active_payment_methods() {
		$active_payment_methods = array();
		$payment_methods = array();
		$settings = get_option('wwp_wholesale_pricing_options', true);
		
		if ( ! isset( $settings['enable_custom_payment_method'] ) || 'yes' != $settings['enable_custom_payment_method'] ) {
			return array();
		}
		if ( ! is_wholesaler_user( get_current_user_id() ) && ! is_admin() ) {
			return array();
		}

		$role = get_current_user_role_id();
		
		if ( $role ) {  
			$payment_methods = get_term_meta($role, 'wwp_wholesale_payment_method_name', true);
		}

		if ( empty( $payment_methods ) ) {
			return array();
		}

		foreach ( $payment_methods as $index => $active ) {
			if ( 'yes' == $active ) {
				$active_payment_methods[] = strtolower( str_replace( ' ', '_', $index ) );
			}
		}

		return $active_payment_methods;
	}
}

function wwp_strike_through_price_element( $type ) {
	$settings = get_option( 'wwp_wholesale_pricing_options' );
	if ( isset( $settings['enable_strike_through_price'] ) && 'no' == $settings['enable_strike_through_price'] ) {
		return ( $type ) ? '<span>' : '</span>';
	}

	return ( 'start' == $type ) ? '<s>' : '</s>';
}

function wwp_guest_wholesale_pricing_enabled( $key = '' ) {
	
	$settings = get_option( 'wwp_wholesale_pricing_options' );

	if ( isset( $settings['enable_wholesale_price'], $settings['wwp_select_wholesale_role_for_non_logged_in'] ) && 'yes' == $settings['enable_wholesale_price'] && -1 != $settings['wwp_select_wholesale_role_for_non_logged_in'] && function_exists( 'is_user_logged_in' ) &&! is_user_logged_in() ) {

		$guest_wholesaler_data = array( 
			'role' => $settings['wwp_select_wholesale_role_for_non_logged_in'],
			'display_on_shop'   => ( isset( $settings['enable_non_logged_shop_page_wholesale_price'] ) && 'yes' == $settings['enable_non_logged_shop_page_wholesale_price'] ) ? true : false,
			'display_on_single' => ( isset( $settings['enable_non_logged_product_page_wholesale_price'] ) && 'yes' == $settings['enable_non_logged_product_page_wholesale_price'] ) ? true : false,
			'message'   => isset( $settings['wwp_custom_message_non_logged_in'] ) ? $settings['wwp_custom_message_non_logged_in'] : '',
		);

		if ( ! empty( $key ) && isset( $guest_wholesaler_data[$key] ) ) {
			return $guest_wholesaler_data[$key];
		}

		return $guest_wholesaler_data;
	} else {
		return false;
	}
}

if ( ! function_exists( 'wwp_get_group_post_by_proid' ) ) {
	function wwp_get_group_post_by_proid( $product_id = 0, $user_id = 0 ) {

		if ( isset( get_option( 'wwp_wholesale_pricing_options' )['enable_groups'] ) && 'yes' !== get_option( 'wwp_wholesale_pricing_options' )['enable_groups'] ) { 
			return array();
		}
		
		// Check if user_id is empty, return empty array
		if ( empty( $user_id ) ) {
			return array();
		}

		$args = array(
			'post_type'      => 'wwp_groups',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'     => '_add_users',
					'value'   => '"' . $user_id . '"',
					'compare' => 'LIKE',
				),
			),
		);

		$user_posts = get_posts( $args );

		if ( empty( $user_posts ) ) {
			return array();
		}

		if ( $product_id > 0 ) {

			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				return array();
			}

			$product_excluded = false;

			// Loop through all posts to check if the product is excluded in any post
			foreach ( $user_posts as $post_id ) {

				$exclude_products = get_post_meta( $post_id, '_exclude_products', true );
				
				if ( ! empty( $exclude_products ) && in_array( $product_id, $exclude_products ) ) {
					$product_excluded = true;
					break;
				}
			}

			if ( $product_excluded && $product_id > 0 ) {
				return array();
			}

			$included_posts = array();

			foreach ( $user_posts as $post_id ) {
				$include_products = get_post_meta( $post_id, '_include_specific_products', true );
				$include_type      = get_post_meta( $post_id, '_include_products', true );
				if ( ( 'specific' == $include_type && ! empty( $include_products ) &&  in_array( $product_id, $include_products ) ) || 'all' == $include_type ) {
					$included_posts[] = $post_id;
				}
			}
			return $included_posts;
		}

		return $user_posts;
	}

}