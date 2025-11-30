<?php
/**
 * The wcfm compatibility functionality of the plugin.
 *
 * @link       https://wpswings.com
 * @since      2.0.0
 *
 * @package     Woocommerce_Subscriptions_Pro
 * @subpackage  Woocommerce_Subscriptions_Pro/package
 */

/**
 * The wcfm compatibility functionality of the plugin.
 *
 * @package     Woocommerce_Subscriptions_Pro
 * @subpackage  Woocommerce_Subscriptions_Pro/include
 * @author      WP Swings <webmaster@wpswings.com>
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'Woocommerce_Subscriptions_Pro_Wcfm_Compatibility' ) ) {

	/**
	 * Define class and module for wcfm.
	 */
	class Woocommerce_Subscriptions_Pro_Wcfm_Compatibility {
		/**
		 * Constructor
		 */
		public function __construct() {
			if ( wps_sfw_check_plugin_enable() ) {

				if ( class_exists( 'WCFMmp_Dependencies' ) && WCFMmp_Dependencies::wcfm_plugin_active_check() ) {

					add_filter( 'wcfm_product_manage_fields_general', array( $this, 'wps_wsp_product_manage_fields_subscription_mode_for_wcfm' ), 999, 5 );
					add_action( 'after_wcfm_products_manage_meta_save', array( $this, 'wps_wsp_products_manage_catalog_options_save_for_wcfm' ), 10, 2 );
					add_action( 'after_wcfm_products_manage_general', array( $this, 'wps_wsp_products_manage_subscription_field_options_for_wxfm' ), 15, 2 );
					add_action( 'before_wcfm_load_scripts', array( $this, 'wps_wsp_load_scripts_for_wcfm' ), 99 );
					add_action( 'after_wcfm_load_styles', array( $this, 'wps_wsp_load_styles_for_wcfm' ), 99 );
					add_filter( 'wcfm_product_manage_fields_variations', array( $this, 'wps_wsp_product_manage_fields_variations' ), 99, 7 );
					add_filter( 'wcfm_product_variation_data_factory', array( $this, 'wps_wsp_variations_product_meta_save' ), 130, 5 );
					add_filter( 'wcfm_variation_edit_data', array( $this, 'wps_wsp_show_product_data_variations' ), 100, 3 );
					add_filter( 'wps_sfw_column_subscription_table', array( $this, 'wps_wsp_column_subscription_table' ), 100 );
					add_filter( 'wps_sfw_add_case_column', array( $this, 'wps_wsp_show_column_data' ), 99, 3 );
					add_filter( 'wps_sfw_subs_table_data', array( $this, 'wps_wsp_add_subs_table_data' ), 99 );

				}
			}
		}

		/**
		 * This function is used to add vendor column.
		 *
		 * @name wps_wsp_add_subs_table_data
		 * @since 2.0.0
		 * @param array $subscription_data subscription_data.
		 */
		public function wps_wsp_add_subs_table_data( $subscription_data ) {
			if ( isset( $subscription_data['parent_order_id'] ) ) {
				$order_id = $subscription_data['parent_order_id'];
				$wps_vendor_name = '';
				$wps_processed_vendors = array();
				if ( function_exists( 'wcfm_get_vendor_store_by_post' ) ) {
					$order = wc_get_order( $order_id );
					if ( is_a( $order, 'WC_Order' ) ) {
						$items = $order->get_items( 'line_item' );
						if ( ! empty( $items ) ) {
							foreach ( $items as $order_item_id => $item ) {
								$line_item = new WC_Order_Item_Product( $item );
								$product  = $line_item->get_product();
								$product_id = $line_item->get_product_id();
								$vendor_id  = wcfm_get_vendor_id_by_post( $product_id );

								if ( ! $vendor_id ) {
									continue;
								}
								if ( in_array( $vendor_id, $wps_processed_vendors ) ) {
									continue;
								}

								$store_name = wcfm_get_vendor_store( $vendor_id );
								if ( $store_name ) {
									if ( $wps_vendor_name ) {
										$wps_vendor_name .= '<br />';
									}
									$wps_vendor_name .= '<span class="dashicons dashicons-businessperson"></span>' . $store_name;
									$wps_processed_vendors[ $vendor_id ] = $vendor_id;
								}
							}
						}
					}
				}
				if ( ! $wps_vendor_name ) {
					$wps_vendor_name = '&ndash;';
				}
				$subscription_data['vendor_store'] = $wps_vendor_name;
			}
			return $subscription_data;
		}

		 /**
		  * This function is used to add vendor column.
		  *
		  * @name wps_wsp_show_column_data
		  * @since 2.0.0
		  * @param bool   $result result.
		  * @param string $column_name column_name.
		  * @param array  $item item.
		  */
		public function wps_wsp_show_column_data( $result, $column_name, $item ) {

			if ( 'vendor_store' == $column_name ) {
				return $item[ $column_name ];
			}
			return $result;
		}

		/**
		 * This function is used to add vendor column.
		 *
		 * @name wps_wsp_column_subscription_table
		 * @since 2.0.0
		 * @param array $columns columns.
		 */
		public function wps_wsp_column_subscription_table( $columns ) {
			$columns['vendor_store'] = __( 'Store', 'woocommerce-subscriptions-pro' );
			return $columns;
		}

		/**
		 * This function is used to save variable subscription fields.
		 *
		 * @name wps_wsp_show_product_data_variations
		 * @since 2.0.0
		 * @param array  $variations variations.
		 * @param int    $variation_id $variation_id.
		 * @param string $variation_id_key $variation_id_key.
		 */
		public function wps_wsp_show_product_data_variations( $variations, $variation_id, $variation_id_key ) {

			if ( $variation_id ) {
				$variations[ $variation_id_key ]['wps_sfw_variable_product'] = ( 'yes' == wps_wsp_get_meta_data( $variation_id, 'wps_sfw_variable_product', true ) ) ? 'enable' : '';

				$wps_sfw_variation_number = wps_wsp_get_meta_data( $variation_id, 'wps_sfw_subscription_number', true );

				if ( empty( $wps_sfw_variation_number ) ) {
					$wps_sfw_variation_number = 1;
				}
				$wps_sfw_variation_subscription_interval = wps_wsp_get_meta_data( $variation_id, 'wps_sfw_subscription_interval', true );

				if ( empty( $wps_sfw_variation_subscription_interval ) ) {
					$wps_sfw_variation_subscription_interval = 'day';
				}

				$wps_sfw_variation_expiry_number = wps_wsp_get_meta_data( $variation_id, 'wps_sfw_subscription_expiry_number', true );
				$wps_sfw_variation_expiry_interval = wps_wsp_get_meta_data( $variation_id, 'wps_sfw_subscription_expiry_interval', true );
				$wps_sfw_variation_initial_fee = wps_wsp_get_meta_data( $variation_id, 'wps_sfw_subscription_initial_signup_price', true );
				$wps_sfw_variation_free_trial = wps_wsp_get_meta_data( $variation_id, 'wps_sfw_subscription_free_trial_number', true );
				$wps_sfw_variation_free_trial_interval = wps_wsp_get_meta_data( $variation_id, 'wps_sfw_subscription_free_trial_interval', true );

				$variations[ $variation_id_key ]['wps_sfw_variation_subscription_number'] = $wps_sfw_variation_number;
				$variations[ $variation_id_key ]['wps_sfw_variation_subscription_interval'] = $wps_sfw_variation_subscription_interval;
				$variations[ $variation_id_key ]['wps_sfw_variation_subscription_expiry_number'] = $wps_sfw_variation_expiry_number;
				$variations[ $variation_id_key ]['wps_sfw_variation_subscription_expiry_interval'] = $wps_sfw_variation_expiry_interval;
				$variations[ $variation_id_key ]['wps_sfw_variation_subscription_initial_signup_price'] = $wps_sfw_variation_initial_fee;
				$variations[ $variation_id_key ]['wps_sfw_variation_subscription_free_trial_number'] = $wps_sfw_variation_free_trial;
				$variations[ $variation_id_key ]['wps_sfw_variation_subscription_free_trial_interval'] = $wps_sfw_variation_free_trial_interval;
			}
			return $variations;
		}

		/**
		 * This function is used to save variable subscription fields.
		 *
		 * @name wps_wsp_variations_product_meta_save
		 * @since 2.0.0
		 * @param array $wcfm_variation_data wcfm_variation_data.
		 * @param int   $product_id $product_id.
		 * @param int   $variation_id $variation_id.
		 * @param array $variations $variations.
		 * @param array $wcfm_products_manage_form_data $wcfm_products_manage_form_data.
		 */
		public function wps_wsp_variations_product_meta_save( $wcfm_variation_data, $product_id, $variation_id, $variations, $wcfm_products_manage_form_data ) {
			if ( $variation_id ) {

				$wps_sfw_product = isset( $variations['wps_sfw_variable_product'] ) ? 'yes' : 'no';

				wps_wsp_update_meta_data( $variation_id, 'wps_sfw_variable_product', $wps_sfw_product );
				if ( 'yes' == $wps_sfw_product ) {

					$wps_sfw_variation_subscription_number = isset( $variations['wps_sfw_variation_subscription_number'] ) ? sanitize_text_field( wp_unslash( $variations['wps_sfw_variation_subscription_number'] ) ) : '';
					$wps_sfw_variation_subscription_interval = isset( $variations['wps_sfw_variation_subscription_interval'] ) ? sanitize_text_field( wp_unslash( $variations['wps_sfw_variation_subscription_interval'] ) ) : '';
					$wps_sfw_variation_subscription_expiry_number = isset( $variations['wps_sfw_variation_subscription_expiry_number'] ) ? sanitize_text_field( wp_unslash( $variations['wps_sfw_variation_subscription_expiry_number'] ) ) : '';
					$wps_sfw_variation_subscription_expiry_interval = isset( $variations['wps_sfw_variation_subscription_expiry_interval'] ) ? sanitize_text_field( wp_unslash( $variations['wps_sfw_variation_subscription_expiry_interval'] ) ) : '';

					/*get valid subscription expiry*/
					$wps_sfw_variation_subscription_expiry_number = wps_wsp_get_valid_subscription_expiry( $wps_sfw_variation_subscription_expiry_number, $wps_sfw_variation_subscription_expiry_interval );
					$wps_sfw_variation_subscription_initial_signup_price = isset( $variations['wps_sfw_variation_subscription_initial_signup_price'] ) ? sanitize_text_field( wp_unslash( $variations['wps_sfw_variation_subscription_initial_signup_price'] ) ) : '';

					$wps_sfw_variation_subscription_free_trial_number = isset( $variations['wps_sfw_variation_subscription_free_trial_number'] ) ? sanitize_text_field( wp_unslash( $variations['wps_sfw_variation_subscription_free_trial_number'] ) ) : '';

					$wps_sfw_variation_subscription_free_trial_interval = isset( $variations['wps_sfw_variation_subscription_free_trial_interval'] ) ? sanitize_text_field( wp_unslash( $variations['wps_sfw_variation_subscription_free_trial_interval'] ) ) : '';
					/*get valid subscription expiry*/
					$wps_sfw_variation_subscription_free_trial_number = wps_wsp_get_valid_subscription_expiry( $wps_sfw_variation_subscription_free_trial_number, $wps_sfw_variation_subscription_free_trial_interval );

					wps_wsp_update_meta_data( $variation_id, 'wps_sfw_subscription_number', $wps_sfw_variation_subscription_number );
					wps_wsp_update_meta_data( $variation_id, 'wps_sfw_subscription_interval', $wps_sfw_variation_subscription_interval );
					wps_wsp_update_meta_data( $variation_id, 'wps_sfw_subscription_expiry_number', $wps_sfw_variation_subscription_expiry_number );
					wps_wsp_update_meta_data( $variation_id, 'wps_sfw_subscription_expiry_interval', $wps_sfw_variation_subscription_expiry_interval );
					wps_wsp_update_meta_data( $variation_id, 'wps_sfw_subscription_initial_signup_price', $wps_sfw_variation_subscription_initial_signup_price );
					wps_wsp_update_meta_data( $variation_id, 'wps_sfw_subscription_free_trial_number', $wps_sfw_variation_subscription_free_trial_number );
					wps_wsp_update_meta_data( $variation_id, 'wps_sfw_subscription_free_trial_interval', $wps_sfw_variation_subscription_free_trial_interval );

					/*Set variable meta*/
					if ( isset( $product_id ) && ! empty( $product_id ) ) {
						wps_wsp_update_meta_data( $product_id, 'wps_sfw_subscription_number', $wps_sfw_variation_subscription_number );
						wps_wsp_update_meta_data( $product_id, 'wps_sfw_subscription_interval', $wps_sfw_variation_subscription_interval );
						wps_wsp_update_meta_data( $product_id, 'wps_sfw_subscription_expiry_number', $wps_sfw_variation_subscription_expiry_number );
						wps_wsp_update_meta_data( $product_id, 'wps_sfw_subscription_expiry_interval', $wps_sfw_variation_subscription_expiry_interval );
						wps_wsp_update_meta_data( $product_id, 'wps_sfw_subscription_initial_signup_price', $wps_sfw_variation_subscription_initial_signup_price );
						wps_wsp_update_meta_data( $product_id, 'wps_sfw_subscription_free_trial_number', $wps_sfw_variation_subscription_free_trial_number );
						wps_wsp_update_meta_data( $product_id, 'wps_sfw_subscription_free_trial_interval', $wps_sfw_variation_subscription_free_trial_interval );
						wps_wsp_update_meta_data( $product_id, 'wps_sfw_variable_product', $wps_sfw_product );
					}
				}
			}
			return $wcfm_variation_data;
		}

		/**
		 * This function is used to create fields for variable subscription.
		 *
		 * @name wps_wsp_product_manage_fields_variations
		 * @since 2.0.0
		 * @param array  $variation_fileds variation_fileds.
		 * @param array  $variations $variations.
		 * @param array  $variation_shipping_option_array $variation_shipping_option_array.
		 * @param string $variation_tax_classes_options $variation_tax_classes_options.
		 * @param array  $products_array $products_array.
		 * @param int    $product_id $product_id.
		 * @param string $product_type $product_type.
		 */
		public function wps_wsp_product_manage_fields_variations( $variation_fileds, $variations, $variation_shipping_option_array, $variation_tax_classes_options, $products_array, $product_id, $product_type ) {

			$wps_wsp_variation_fields = array(
				'wps_sfw_variable_product' => array(
					'label' => __( 'Subscription', 'woocommerce-subscriptions-pro' ),
					'type' => 'checkbox',
					'value' => 'enable',
					'class' => 'wcfm-checkbox wcfm_ele variable wps_sfw_variable_product',
					'label_class' => 'wcfm_title checkbox_title',
				),
			);

			$variation_fileds = array_slice( $variation_fileds, 0, 4, true ) + $wps_wsp_variation_fields + array_slice( $variation_fileds, 1, count( $variation_fileds ) - 1, true );
			$variation_fileds = array_slice( $variation_fileds, 1, count( $variation_fileds ) - 1, true );

			$wps_wsp_variation_fields = array(

				'wps_sfw_variation_subscription_number' => array(
					'label' => __( 'Subscriptions Interval', 'woocommerce-subscriptions-pro' ),
					'type' => 'number',
					'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input wcfm_half_ele variable wps_sfw_variation_subscription_number wps_sfw_var_sub_interval_num',
					'label_class' => 'wcfm_title wcfm_ele wcfm_half_ele_title variable wps_sfw_variation_subscription_number wps-sfw-label',
					'attributes' => array(
						'min' => '1',
						'step' => '1',
					),
				),
				'wps_sfw_variation_subscription_interval' => array(
					'type' => 'select',
					'options' => wps_sfw_subscription_period(),
					'class' => 'wcfm-select wcfm_ele wcfm_half_ele variable wps_sfw_variation_subscription_interval wps_sfw_var_sub_interval',
					'label_class' => 'wcfm_title wcfm_half_ele_title wps_sfw_variation_subscription_interval',
				),
				'wps_sfw_variation_subscription_expiry_number' => array(
					'label' => __( 'Subscriptions Expiry Interval', 'woocommerce-subscriptions-pro' ),
					'type' => 'number',
					'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input wcfm_half_ele variable wps_sfw_variation_subscription_expiry_number wps_sfw_var_sub_expiry_num',
					'label_class' => 'wcfm_title wcfm_ele wcfm_half_ele_title variable wps_sfw_variation_subscription_expiry_number wps-sfw-label',
					'attributes' => array(
						'min' => '1',
						'step' => '1',
					),
				),
				'wps_sfw_variation_subscription_expiry_interval' => array(
					'type' => 'select',
					'options' => wps_sfw_subscription_period(),
					'class' => 'wcfm-select wcfm_ele wcfm_half_ele variable wps_sfw_variation_subscription_expiry_interval wps_sfw_var_sub_exp_interval',
					'label_class' => 'wcfm_title wcfm_half_ele_title wps_sfw_variation_subscription_expiry_interval',
				),
				'wps_sfw_variation_subscription_initial_signup_price' => array(
					'label' => __( 'Initial Signup fee', 'woocommerce-subscriptions-pro' ) . '(' . get_woocommerce_currency_symbol() . ')',
					'type' => 'number',
					'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input wcfm_half_ele variable wps_sfw_variation_subscription_initial_signup_price wps_sfw_var_sub_initial_signup_price',
					'label_class' => 'wcfm_title wcfm_ele wcfm_half_ele_title variable wps_sfw_variation_subscription_initial_signup_price wps-sfw-label',
					'attributes' => array(
						'min' => '0.1',
						'step' => '0.1',
					),
				),
				'wps_sfw_variation_subscription_free_trial_number' => array(
					'label' => __( 'Free trial interval', 'woocommerce-subscriptions-pro' ),
					'type' => 'number',
					'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input wcfm_half_ele variable wps_sfw_variation_subscription_free_trial_number wps_sfw_var_sub_free_trial_num',
					'label_class' => 'wcfm_title wcfm_ele wcfm_half_ele_title variable wps_sfw_variation_subscription_free_trial_number wps-sfw-label',
					'attributes' => array(
						'min' => '1',
						'step' => '1',
					),
				),
				'wps_sfw_variation_subscription_free_trial_interval' => array(
					'type' => 'select',
					'options' => wps_sfw_subscription_period(),
					'class' => 'wcfm-select wcfm_ele wcfm_half_ele variable wps_sfw_variation_subscription_free_trial_interval wps_sfw_var_sub_free_trial_interval',
					'label_class' => 'wcfm_title wcfm_half_ele_title wps_sfw_variation_subscription_free_trial_interval',
				),
			);
			$variation_fileds = array_slice( $variation_fileds, 0, 10, true ) + $wps_wsp_variation_fields + array_slice( $variation_fileds, 1, count( $variation_fileds ) - 1, true );
			$variation_fileds = array_slice( $variation_fileds, 1, count( $variation_fileds ) - 1, true );
			return $variation_fileds;
		}

		/**
		 * Include script.
		 *
		 * @name wps_wsp_load_scripts_for_wcfm
		 * @since 2.0.0
		 * @param string $end_points $end_points.
		 */
		public function wps_wsp_load_scripts_for_wcfm( $end_points ) {
			if ( 'wcfm-products-manage' == $end_points ) {
				wp_register_script( 'wps-wsp-wcfm-js', WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'package/wcfm-compatibility/js/woocommerec-subscriptions-pro-wcfm-product-mange.js', array( 'jquery', 'wcfm_products_manage_js' ), WOOCOMMERCE_SUBSCRIPTIONS_PRO_VERSION, true );

				wp_localize_script(
					'wps-wsp-wcfm-js',
					'wsp_wcfm_param',
					array(
						'ajaxurl' => admin_url( 'admin-ajax.php' ),
						'day' => __( 'Days', 'woocommerce-subscriptions-pro' ),
						'week' => __( 'Weeks', 'woocommerce-subscriptions-pro' ),
						'month' => __( 'Months', 'woocommerce-subscriptions-pro' ),
						'year' => __( 'Years', 'woocommerce-subscriptions-pro' ),
						'expiry_notice' => __( 'Expiry Interval must be greater than subscription interval', 'woocommerce-subscriptions-pro' ),

					)
				);

				 wp_enqueue_script( 'wps-wsp-wcfm-js' );
			}
		}

		/**
		 * Include style.
		 *
		 * @name wps_wsp_load_styles_for_wcfm
		 * @since 2.0.0
		 * @param string $end_points $end_points.
		 */
		public function wps_wsp_load_styles_for_wcfm( $end_points ) {
			if ( 'wcfm-products-manage' == $end_points ) {
				wp_enqueue_style( 'wps-wsp-wcfm-css', WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'package/wcfm-compatibility/css/woocommerec-subscriptions-pro-wcfm-product-mange.css', array(), WOOCOMMERCE_SUBSCRIPTIONS_PRO_VERSION, false );

			}
		}

		/**
		 * This function is used to create subscription checkbox.
		 *
		 * @name wps_wsp_product_manage_fields_subscription_mode_for_wcfm
		 * @since 2.0.0
		 * @param array  $general_fields general_fields.
		 * @param int    $product_id product_id.
		 * @param string $product_type $product_type.
		 * @param bool   $wcfm_is_translated_product $wcfm_is_translated_product.
		 * @param string $wcfm_wpml_edit_disable_element wcfm_wpml_edit_disable_element.
		 */
		public function wps_wsp_product_manage_fields_subscription_mode_for_wcfm( $general_fields, $product_id, $product_type, $wcfm_is_translated_product = false, $wcfm_wpml_edit_disable_element = '' ) {
			$is_subscription = ( wps_wsp_get_meta_data( $product_id, '_wps_sfw_product', true ) == 'yes' ) ? 'yes' : 'no';

			$general_fields = array_slice( $general_fields, 0, 4, true ) +
																		array(
																			'_wps_sfw_product' => array(
																				'desc' => __( 'Subscription', 'woocommerce-subscriptions-pro' ),
																				'type' => 'checkbox',
																				'class' => 'wcfm-checkbox wcfm_ele wcfm_half_ele_checkbox simple non-pw-gift-card ' . $wcfm_wpml_edit_disable_element,
																				'desc_class' => 'wcfm_title wcfm_ele virtual_ele_title checkbox_title simple non-pw-gift-card ' . $wcfm_wpml_edit_disable_element,
																				'value' => 'yes',
																				'dfvalue' => $is_subscription,
																			),
																		) +
																		array_slice( $general_fields, 1, count( $general_fields ) - 1, true );
			return $general_fields;
		}

		/**
		 * This function is used to save subscription fields.
		 *
		 * @name wps_wsp_products_manage_catalog_options_save_for_wcfm
		 * @since 2.0.0
		 * @param int   $product_id product_id.
		 * @param array $wcfm_products_manage_form_data $wcfm_products_manage_form_data.
		 */
		public function wps_wsp_products_manage_catalog_options_save_for_wcfm( $product_id, $wcfm_products_manage_form_data ) {

			$is_wps_subscription = ( isset( $wcfm_products_manage_form_data['_wps_sfw_product'] ) ) ? 'yes' : 'no';

			wps_wsp_update_meta_data( $product_id, '_wps_sfw_product', $is_wps_subscription );
			if ( isset( $wcfm_products_manage_form_data['_wps_sfw_product'] ) && ! empty( $wcfm_products_manage_form_data['_wps_sfw_product'] ) ) {

				$wps_sfw_subscription_number = isset( $wcfm_products_manage_form_data['wps_sfw_subscription_number'] ) ? sanitize_text_field( wp_unslash( $wcfm_products_manage_form_data['wps_sfw_subscription_number'] ) ) : '';
				$wps_sfw_subscription_interval = isset( $wcfm_products_manage_form_data['wps_sfw_subscription_interval'] ) ? sanitize_text_field( wp_unslash( $wcfm_products_manage_form_data['wps_sfw_subscription_interval'] ) ) : '';
				$wps_sfw_subscription_expiry_number = isset( $wcfm_products_manage_form_data['wps_sfw_subscription_expiry_number'] ) ? sanitize_text_field( wp_unslash( $wcfm_products_manage_form_data['wps_sfw_subscription_expiry_number'] ) ) : '';
				$wps_sfw_subscription_expiry_interval = isset( $wcfm_products_manage_form_data['wps_sfw_subscription_expiry_interval'] ) ? sanitize_text_field( wp_unslash( $wcfm_products_manage_form_data['wps_sfw_subscription_expiry_interval'] ) ) : '';
				$wps_sfw_subscription_initial_signup_price = isset( $wcfm_products_manage_form_data['wps_sfw_subscription_initial_signup_price'] ) ? sanitize_text_field( wp_unslash( $wcfm_products_manage_form_data['wps_sfw_subscription_initial_signup_price'] ) ) : '';
				$wps_sfw_subscription_free_trial_number = isset( $wcfm_products_manage_form_data['wps_sfw_subscription_free_trial_number'] ) ? sanitize_text_field( wp_unslash( $wcfm_products_manage_form_data['wps_sfw_subscription_free_trial_number'] ) ) : '';
				$wps_sfw_subscription_free_trial_interval = isset( $wcfm_products_manage_form_data['wps_sfw_subscription_free_trial_interval'] ) ? sanitize_text_field( wp_unslash( $wcfm_products_manage_form_data['wps_sfw_subscription_free_trial_interval'] ) ) : '';

				wps_wsp_update_meta_data( $product_id, 'wps_sfw_subscription_number', $wps_sfw_subscription_number );
				wps_wsp_update_meta_data( $product_id, 'wps_sfw_subscription_interval', $wps_sfw_subscription_interval );
				wps_wsp_update_meta_data( $product_id, 'wps_sfw_subscription_expiry_number', $wps_sfw_subscription_expiry_number );
				wps_wsp_update_meta_data( $product_id, 'wps_sfw_subscription_expiry_interval', $wps_sfw_subscription_expiry_interval );
				wps_wsp_update_meta_data( $product_id, 'wps_sfw_subscription_initial_signup_price', $wps_sfw_subscription_initial_signup_price );
				wps_wsp_update_meta_data( $product_id, 'wps_sfw_subscription_free_trial_number', $wps_sfw_subscription_free_trial_number );
				wps_wsp_update_meta_data( $product_id, 'wps_sfw_subscription_free_trial_interval', $wps_sfw_subscription_free_trial_interval );

				do_action( 'wps_wsp_save_simple_subscription_field_for_wcfm', $product_id, $wcfm_products_manage_form_data );
			}

			return;
		}

		/**
		 * This function is used to create subscription fields.
		 *
		 * @name wps_wsp_products_manage_subscription_field_options_for_wxfm
		 * @since 2.0.0
		 * @param int    $product_id product_id.
		 * @param string $product_type $product_type.
		 */
		public function wps_wsp_products_manage_subscription_field_options_for_wxfm( $product_id, $product_type ) {
			global $WCFM; //phpcs:ignore

			$wps_sfw_subscription_number = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_number', true );
			if ( empty( $wps_sfw_subscription_number ) ) {
				$wps_sfw_subscription_number = 1;
			}
			$wps_sfw_subscription_interval = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_interval', true );
			if ( empty( $wps_sfw_subscription_interval ) ) {
				$wps_sfw_subscription_interval = 'day';
			}

			$wps_sfw_subscription_expiry_number = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_expiry_number', true );
			$wps_sfw_subscription_expiry_interval = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_expiry_interval', true );
			$wps_sfw_subscription_initial_signup_price = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_initial_signup_price', true );
			$wps_sfw_subscription_free_trial_number = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_free_trial_number', true );
			$wps_sfw_subscription_free_trial_interval = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_free_trial_interval', true );

			?>
			
			<!-- collapsible 2.1 -->
			<div class="page_collapsible products_manage_wps_subscriptions simple wps_subscriptions_options" id="wcfm_products_manage_form_wps_subscriptions_head"><label class="wcfmfa fa-bars"></label><?php esc_html_e( 'Subscription Settings', 'woocommerce-subscriptions-pro' ); ?><span></span></div>
			<div class="wcfm-container simple wps_subscriptions_options">
				<div id="wcfm_products_manage_form_wps_subscriptions_expander" class="wcfm-content">
					<?php
					$WCFM->wcfm_fields->wcfm_generate_form_field( //phpcs:ignore
						apply_filters(
							'wps_wsp_product_fields_subscription_fields_options',
							array(

								'wps_sfw_subscription_number' => array(
									'label' => __( 'Subscriptions Per Interval', 'woocommerce-subscriptions-pro' ),
									'type' => 'number',
									'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input simple external non-subscription non-variable-subscription non-auction non-redq_rental non-accommodation-booking non-lottery non-pw-gift-card wps_sfw_subscription_number wps_sfw_sub_num',
									'label_class' => 'wcfm_ele wcfm_title simple external non-subscription non-variable-subscription non-auction non-redq_rental non-accommodation-booking non-lottery non-pw-gift-card',
									'value' => $wps_sfw_subscription_number,
									'attributes' => array(
										'min' => '1',
										'step' => '1',
									),
									'hints' => __( 'Choose the subscriptions time interval for the product "for example 10 days".', 'woocommerce-subscriptions-pro' ),
								),
								'wps_sfw_subscription_interval' => array(
									'type' => 'select',
									'options' => wps_sfw_subscription_period(),
									'class' => 'wcfm-select wps_sfw_subscription_interval wps_sfw_sub_interval',
									'value' => $wps_sfw_subscription_interval,
								),
								'wps_sfw_subscription_expiry_number' => array(
									'label' => __( 'Subscriptions Expiry Interval', 'woocommerce-subscriptions-pro' ),
									'type' => 'number',
									'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input simple external non-subscription non-variable-subscription non-auction non-redq_rental non-accommodation-booking non-lottery non-pw-gift-card wps_sfw_subscription_expiry_number wps_sfw_sub_expiry_num',
									'label_class' => 'wcfm_ele wcfm_title simple external non-subscription non-variable-subscription non-auction non-redq_rental non-accommodation-booking non-lottery non-pw-gift-card',
									'value' => $wps_sfw_subscription_expiry_number,
									'attributes' => array(
										'min' => '1',
										'step' => '1',
									),
									'hints' => __( 'Choose the subscriptions expiry time interval for the product leave empty for unlimited, And expiry Interval must be greater than subscription interval .', 'woocommerce-subscriptions-pro' ),
								),
								'wps_sfw_subscription_expiry_interval' => array(
									'type' => 'select',
									'options' => wps_sfw_subscription_period(),
									'class' => 'wcfm-select wps_sfw_subscription_expiry_interval wps_sfw_exp',
									'value' => $wps_sfw_subscription_expiry_interval,
								),
								'wps_sfw_subscription_initial_signup_price' => array(
									'label' => __( 'Initial Signup fee', 'woocommerce-subscriptions-pro' ) . '(' . get_woocommerce_currency_symbol() . ')',
									'type' => 'number',
									'label_class' => 'wcfm_ele wcfm_title simple external non-variable-subscription non-auction non-redq_rental non-accommodation-booking non-lottery non-pw-gift-card',
									'value' => $wps_sfw_subscription_initial_signup_price,
									'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input simple external non-subscription non-variable-subscription non-auction non-redq_rental non-accommodation-booking non-lottery non-pw-gift-card wps_sfw_subscription_initial_signup_price',
									'attributes' => array(
										'min' => '0.1',
										'step' => '0.1',
									),
									'hints' => __( 'Choose the subscriptions initial fee for the product "leave empty for no initial fee".', 'woocommerce-subscriptions-pro' ),

								),
								'wps_sfw_subscription_free_trial_number' => array(
									'label' => __( 'Free trial interval', 'woocommerce-subscriptions-pro' ),
									'type' => 'number',
									'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input simple external non-subscription non-variable-subscription non-auction non-redq_rental non-accommodation-booking non-lottery non-pw-gift-card wps_sfw_subscription_free_trial_number wps_sfw_sub_free_trial',
									'label_class' => 'wcfm_ele wcfm_title simple external non-subscription non-variable-subscription non-auction non-redq_rental non-accommodation-booking non-lottery non-pw-gift-card',
									'value' => $wps_sfw_subscription_free_trial_number,
									'attributes' => array(
										'min' => '1',
										'step' => '1',
									),
									'hints' => __( 'Choose the trial period for subscription "leave empty for no trial period".', 'woocommerce-subscriptions-pro' ),
								),
								'wps_sfw_subscription_free_trial_interval' => array(
									'type' => 'select',
									'options' => wps_sfw_subscription_period(),
									'class' => 'wcfm-select  wps_sfw_subscription_free_trial_interval wps_sfw_sub_free_trial_interval',
									'value' => $wps_sfw_subscription_free_trial_interval,
								),

							),
							$product_id,
							$product_type
						)
					);
					?>
				</div>
			</div>
			<!-- end collapsible -->
			<div class="wcfm_clearfix"></div>
			<?php
		}
	}
}
new Woocommerce_Subscriptions_Pro_Wcfm_Compatibility();
