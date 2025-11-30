<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( ! class_exists( 'WWP_mnm_compatibility' ) ) {

	class WWP_Mnm_Compatibility extends WWP_Easy_Wholesale_Multiuser {

		public function __construct() {
			add_filter( 'mnm_get_price', array( $this, 'mnm_get_price' ), 10, 2 );
			add_filter( 'wwp_mnm_get_price', array( $this, 'wwp_mnm_get_price' ), 10, 2 );
			add_filter( 'wwp_mnm_get_price_qty', array( $this, 'wwp_mnm_get_price_qty' ), 10, 2 );
		}
		public function mnm_get_price( $value, $instance ) {
			$price = $this->wwp_regular_price_change( $value, $instance );
			return $price;
		}
		public function wwp_mnm_get_price_qty( $value, $product ) {
			if ( 'mix-and-match' == $product->get_type() ) {
				return false;
			}
			return $value;
		}


		public function wwp_mnm_get_price( $price, $product ) {
			if ( 'mix-and-match' == $product->get_type() ) {
				$price = get_post_meta( $product->get_id(), '_min_raw_regular_price', true );
			}
			return $price;
		}
	}
	new WWP_mnm_compatibility();
}
