<?php
// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Wwp_Wholesale_Ajax_Handler
 *
 * This class handles AJAX requests for the wholesale pricing functionality.
 */
class Wwp_Wholesale_Ajax_Handler {

	/**
	 * Constructor: Hooks the class methods to WordPress actions.
	 */
	public function __construct() {
		// Register the AJAX actions for logged-in and non-logged-in users
		add_action( 'wp_ajax_wwp_show_discount_list_product_variation', array( $this, 'wwp_show_discount_list_product_variation' ) );
		add_action( 'wp_ajax_nopriv_wwp_show_discount_list_product_variation', array( $this, 'wwp_show_discount_list_product_variation' ) );
	}

	/**
	 * Handles the AJAX request to show discount list for product variation.
	 */
	public function wwp_show_discount_list_product_variation() {

		if ( isset( $_POST['wwp_tier_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['wwp_tier_nonce'] ), 'wwp_tier_load' ) ) {

			if ( isset( $_POST['variation_id'] ) && ! empty( $_POST['variation_id'] ) ) {

				$variation_id = sanitize_text_field( $_POST['variation_id'] );
				$variation = wc_get_product( $variation_id );
				$product_id = $variation->get_parent_id();
				$product = new WC_Product_Variable( $product_id );
				$role = get_current_user_role_id();

				if ( $product->get_type() == 'simple' ) {
					return;
				}

				$product_tier_pricing = wholesale_tire_prices( $product );
				$html = '';

				// check tier for variable product
				if ( isset( $product_tier_pricing[ $role ][ $variation_id ] )  ) {
					$i = 1;
					foreach ( $product_tier_pricing[ $role ][ $variation_id ] as $variation_data ) {

						$max_original_variation_price = get_post_meta( $variation_id, '_regular_price', true );
						$max_wholesale_price = tire_get_type( $variation_id, $product, $variation_data['price'], $max_original_variation_price, $product_tier_pricing[ $role ]['discount_type'] );
						$max_saving_percent = ( $max_original_variation_price - $max_wholesale_price ) / $max_original_variation_price * 100;
						$max_saving_percent = round( $max_saving_percent );
						$max_wholesale_price = wwp_get_price_including_tax( $product, array( 'price' => $max_wholesale_price ) );
						$max_wholesale_price = wc_price( $max_wholesale_price ); 

						$product = wc_get_product( $variation_id );

						// Generate the row HTML if conditions are met
						if ( ! empty( $variation_data['min'] ) || ! empty( $variation_data['max'] ) || 0 != $max_saving_percent ) {
							if ( 1 === $i ) {
								$html .= '<tr>
                                    <th>Variations</th>
                                    <th>Quantity</th>
                                    <th>Save Discount</th>
                                    <th>Price per unit</th>
                                </tr>';
							}

							$html .= '<tr class="wrap_' . esc_attr( $variation_id ) . ' row_tire" data-id="' . esc_attr( $variation_id ) . '" data-min="' . esc_attr( $variation_data['min'] ) . '" data-max="' . esc_attr( $variation_data['max'] ) . '">';
							$html .= '<td>' . wp_kses_post( wc_get_formatted_variation( $product->get_variation_attributes(), true ) ) . '</td>';
							$html .= '<td>' . wp_kses_post( $variation_data['min'] . '-' . $variation_data['max'] ) . '</td>';
							$html .= '<td>' . esc_attr( $max_saving_percent ) . '%</td>';
							$html .= '<td>' . wp_kses_post( $max_wholesale_price ) . '</td>';
							$html .= '</tr>';
						}

						$i++;
					}
				}

				// check tier for global setting
				if ( isset( $product_tier_pricing[ $role ] )  ) {
					$i = 1;
					foreach ( $product_tier_pricing[ $role ] as $key => $variation_data ) {
						
						$max_original_variation_price = (float) get_post_meta( $variation_id, '_regular_price', true );
						$variation_data_price = isset($variation_data['price']) ? floatval($variation_data['price']) : 0;

						$max_wholesale_price =  tire_get_type( $variation_id, $product, $variation_data_price, $max_original_variation_price, $product_tier_pricing[ $role ]['discount_type'] );
						
						$max_saving_percent = floatval( $max_original_variation_price - $max_wholesale_price ) / $max_original_variation_price * 100;
						$max_saving_percent = round( $max_saving_percent );
						$max_wholesale_price = wwp_get_price_including_tax( $product, array( 'price' => $max_wholesale_price ) );
						$max_wholesale_price = wc_price( $max_wholesale_price ); 

						$product = wc_get_product( $variation_id );

						// Generate the row HTML if conditions are met
						if ( ! empty( $variation_data['min'] ) || ! empty( $variation_data['max'] ) || 0 != $max_saving_percent ) {
							if ( 1 === $i ) {
								$html .= '<tr>
                                    <th>Variations</th>
                                    <th>Quantity</th>
                                    <th>Save Discount</th>
                                    <th>Price per unit</th>
                                </tr>';
							}

							$html .= '<tr class="wrap_' . esc_attr( $variation_id ) . ' row_tire" data-id="' . esc_attr( $variation_id ) . '" data-min="' . esc_attr( $variation_data['min'] ) . '" data-max="' . esc_attr( $variation_data['max'] ) . '">';
							$html .= '<td>' . wp_kses_post( wc_get_formatted_variation( $product->get_variation_attributes(), true ) ) . '</td>';
							$html .= '<td>' . wp_kses_post( $variation_data['min'] . '-' . $variation_data['max'] ) . '</td>';
							$html .= '<td>' . esc_attr( $max_saving_percent ) . '%</td>';
							$html .= '<td>' . wp_kses_post( $max_wholesale_price ) . '</td>';
							$html .= '</tr>';
						}

						$i++;
					}
				}

				$status = array();
				$status['html'] = $html;
				echo json_encode( $status );
				wp_die();
			}
		} else {
			echo json_encode( 'Security Error' ); 
			wp_die();
		}
	}
}

// Instantiate the class
$wwp_wholesale_ajax_handler = new Wwp_Wholesale_Ajax_Handler();
