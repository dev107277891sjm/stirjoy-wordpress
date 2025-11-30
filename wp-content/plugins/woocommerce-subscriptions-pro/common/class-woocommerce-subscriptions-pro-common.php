<?php
/**
 * The common functionality of the plugin.
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    Woocommerce_Subscriptions_Pro
 * @subpackage Woocommerce_Subscriptions_Pro/common
 */

use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * The common functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the common stylesheet and JavaScript.
 * namespace woocommerce_subscriptions_pro_common.
 *
 * @package    Woocommerce_Subscriptions_Pro
 * @subpackage Woocommerce_Subscriptions_Pro/common
 * @author     WP Swings <webmaster@wpswings.com>
 */
class Woocommerce_Subscriptions_Pro_Common {
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The coupon error of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $coupon_error    The current version of this plugin.
	 */
	private $coupon_error;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the common side of the site.
	 *
	 * @since    1.0.0
	 */
	public function wsp_common_enqueue_styles() {

		wp_enqueue_style( $this->plugin_name . 'common', WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'common/css/woocommerce-subscriptions-pro-common.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the common side of the site.
	 *
	 * @since    1.0.0
	 */
	public function wsp_common_enqueue_scripts() {

		wp_register_script( $this->plugin_name . 'common', WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'common/js/woocommerce-subscriptions-pro-common.js', array( 'jquery' ), $this->version, false );
		wp_localize_script(
			$this->plugin_name . 'common',
			'wsp_common_param',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'wps_wsp_common_nonce' ),
				'update_success' => esc_attr__( 'The Subscription has been updated', 'woocommerce-subscriptions-pro' ),
				'update_error' => esc_attr__( 'Something went wrong !!!', 'woocommerce-subscriptions-pro' ),

			)
		);
		wp_enqueue_script( $this->plugin_name . 'common' );
	}

	/**
	 * Check License validation.
	 *
	 * @name wps_wsp_validate_license_key
	 * @since    1.0.0
	 */
	public function wps_wsp_validate_license_key() {
		global $wpdb;

		// First check the nonce, if it fails the function will break.
		check_ajax_referer( 'wps-wsp-nonce-action', 'wps_wsp_license_nonce' );

		$wps_wsp_license_key = ! empty( $_POST['wps_wsp_license_code'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wsp_license_code'] ) ) : '';

		if ( is_multisite() ) {
			// Get all blogs in the network and activate plugins on each one.
			if ( function_exists( 'get_sites' ) ) {
				$blog_ids = get_sites( array( 'fields' => 'ids' ) );
			} else {
				$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			}
			$wps_wsp_license_data = $this->wps_wsp_license_key_validation( $wps_wsp_license_key );
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );

				if ( isset( $wps_wsp_license_data->result ) && 'success' === $wps_wsp_license_data->result ) {

					update_option( 'wps_wsp_license_key', $wps_wsp_license_key );
					update_option( 'wps_wsp_license_key_status', 'true' );

					$response = json_encode(
						array(
							'status' => true,
							'msg' => __(
								'Successfully Verified...',
								'woocommerce-subscriptions-pro'
							),
						)
					);
				} else {
					$error_message = ! empty( $wps_wsp_license_data->message ) ? $wps_wsp_license_data->message : __( 'License Verification Failed.', 'woocommerce-subscriptions-pro' );

					$response = json_encode(
						array(
							'status' => false,
							'msg' => $error_message,
						)
					);
					break;
				}
				restore_current_blog();
			}
		} else {
			// activated on a single site, in a multi-site or on a single site.
			$wps_wsp_license_data = $this->wps_wsp_license_key_validation( $wps_wsp_license_key );

			if ( isset( $wps_wsp_license_data->result ) && 'success' === $wps_wsp_license_data->result ) {

				update_option( 'wps_wsp_license_key', $wps_wsp_license_key );
				update_option( 'wps_wsp_license_key_status', 'true' );

				$response = json_encode(
					array(
						'status' => true,
						'msg' => __(
							'Successfully Verified...',
							'woocommerce-subscriptions-pro'
						),
					)
				);
			} else {
				$error_message = ! empty( $wps_wsp_license_data->message ) ? $wps_wsp_license_data->message : __( 'License Verification Failed.', 'woocommerce-subscriptions-pro' );

				$response = json_encode(
					array(
						'status' => false,
						'msg' => $error_message,
					)
				);
			}
		}
		echo $response; //phpcs:ignore.
		wp_die();
	}

	/**
	 * Create subscription type coupon.
	 *
	 * @name wsp_subscription_coupon_discount_types
	 * @param array $discount_types discount_types.
	 * @since    1.0.0
	 */
	public function wsp_subscription_coupon_discount_types( $discount_types ) {
		$wps_discount_types = array(
			'initial_fee_discount' => __( 'Initial Signup Fee Discount', 'woocommerce-subscriptions-pro' ),
			'initial_fee_percent_discount' => __( 'Initial Signup Fee Percent Discount', 'woocommerce-subscriptions-pro' ),
			'recurring_product_discount' => __( 'Recurring And Product Discount', 'woocommerce-subscriptions-pro' ),
			'recurring_product_percent_discount' => __( 'Recurring And Product Percent Discount', 'woocommerce-subscriptions-pro' ),
		);
		return array_merge( $discount_types, $wps_discount_types );
	}

	/**
	 * This function is used to validate subscription coupon.
	 *
	 * @name wps_wsp_validate_subscription_coupon_for_product
	 * @param bool   $is_valid is_valid.
	 * @param object $product product.
	 * @param object $coupon coupon.
	 * @since    1.0.0
	 */
	public function wps_wsp_validate_subscription_coupon_for_product( $is_valid, $product, $coupon ) {
		if ( ! $is_valid ) {
			return $is_valid;
		}
		$coupon_type  = $coupon->get_discount_type();
		$wps_recurring_subscription_coupon = wps_wsp_get_subscription_discount_type();
		$wps_signup_subscription_coupon = wps_wsp_get_subscription_signup_discount_type();
		$wps_is_recurring_coupon   = isset( $wps_recurring_subscription_coupon[ $coupon_type ] );
		$wps_is_sign_up_fee_coupon   = isset( $wps_signup_subscription_coupon[ $coupon_type ] );

		if ( ( $wps_is_recurring_coupon || $wps_is_sign_up_fee_coupon ) && ! wps_sfw_check_product_is_subscription( $product ) ) {
			$this->coupon_error = __( 'Sorry, this coupon is only valid for subscription products.', 'woocommerce-subscriptions-pro' );
			$is_valid = false;
		} elseif ( $wps_is_sign_up_fee_coupon && '' == wps_wsp_get_signup_fee( $product ) ) {
			$this->coupon_error = __( 'Sorry, this coupon is only valid for subscription products with a sign-up fee.', 'woocommerce-subscriptions-pro' );
			$is_valid = false;
		}

		return $is_valid;
	}

	/**
	 * This function is used to set subscription type coupon.
	 *
	 * @name wsp_woocommerce_product_coupon_types
	 * @param array $coupon_types coupon_types.
	 * @since    1.0.0
	 */
	public function wsp_woocommerce_product_coupon_types( $coupon_types ) {

		if ( is_array( $coupon_types ) ) {
			$wps_discount_type = array(
				'initial_fee_discount',
				'initial_fee_percent_discount',
				'recurring_product_discount',
				'recurring_product_percent_discount',
			);

			$coupon_types = array_merge( $coupon_types, $wps_discount_type );

		}

		return $coupon_types;
	}

	/**
	 * Get discount amount.
	 *
	 * @name wps_wsp_get_discount_amount
	 * @param string $discount discount.
	 * @param int    $discounting_amount discounting_amount.
	 * @param object $item item.
	 * @param string $single single.
	 * @param object $coupon coupon.
	 * @since    1.0.0
	 */
	public function wps_wsp_get_discount_amount( $discount, $discounting_amount, $item, $single, $coupon ) {

		if ( is_a( $item, 'WC_Order_Item' ) ) {

			$discount = $this->wps_wsp_get_discount_amount_for_line_item( $item, $discount, $discounting_amount, $single, $coupon );
		} else {

			$discount = $this->wps_get_discount_amount_for_cart_item( $item, $discount, $discounting_amount, $single, $coupon );
		}
		return $discount;
	}

	/**
	 * Get discount amount.
	 *
	 * @name wps_wsp_get_discount_amount_for_line_item
	 * @param object $line_item line_item.
	 * @param int    $discount discount.
	 * @param int    $discounting_amount discounting_amount.
	 * @param string $single single.
	 * @param object $coupon coupon.
	 * @since    1.0.0
	 */
	public function wps_wsp_get_discount_amount_for_line_item( $line_item, $discount, $discounting_amount, $single, $coupon ) {

		if ( ! is_callable( array( $line_item, 'get_order' ) ) ) {
			return $discount;
		}

		$coupon_type  = $coupon->get_discount_type();

		$order       = $line_item->get_order();
		$product     = $line_item->get_product();

		if ( in_array( $coupon_type, array( 'recurring_product_discount', 'recurring_product_percent_discount' ) ) && ( wps_sfw_check_valid_subscription( $order->get_id() ) || wps_sfw_check_product_is_subscription( $product ) ) ) {
			if ( 'recurring_product_percent_discount' === $coupon_type ) {
				$discount = (float) $coupon->get_amount() * ( $discounting_amount / 100 );
			} else {
				$discount = min( $coupon->get_amount(), $discounting_amount );
				$discount = $single ? $discount : $discount * $line_item->get_quantity();
			}
		} elseif ( in_array( $coupon_type, array( 'initial_fee_discount', 'initial_fee_percent_discount' ) ) && wps_sfw_check_product_is_subscription( $product ) && 0 != wps_wsp_get_signup_fee( $product ) ) {
			if ( 'initial_fee_discount' === $coupon_type ) {
				$discount = min( $coupon->get_amount(), wps_wsp_get_signup_fee( $product ) );
				$discount = $single ? $discount : $discount * $line_item->get_quantity();
			} else {
				$discount = (float) $coupon->get_amount() * ( wps_wsp_get_signup_fee( $product ) / 100 );
			}
		}

		return $discount;
	}

	/**
	 * Get discount amount.
	 *
	 * @name wps_wsp_get_discount_amount_for_line_item
	 * @param array  $cart_item cart_item.
	 * @param int    $discount discount.
	 * @param int    $discounting_amount discounting_amount.
	 * @param string $single single.
	 * @param object $coupon coupon.
	 * @since    1.0.0
	 */
	public function wps_get_discount_amount_for_cart_item( $cart_item, $discount, $discounting_amount, $single, $coupon ) {

		$coupon_type  = $coupon->get_discount_type();

		if ( ! in_array( $coupon_type, array( 'initial_fee_discount', 'initial_fee_percent_discount', 'recurring_product_discount', 'recurring_product_percent_discount' ) ) ) {
			return $discount;
		}

		// If not a subscription product return the default discount.
		if ( ! wps_sfw_check_product_is_subscription( $cart_item['data'] ) ) {
			return $discount;
		}
		$wps_wsp_apply_initial_coupon = false;
		$wps_wsp_apply_initial_percent_coupon = false;
		$discount_amount = 0;
		$cart_item_qty = is_null( $cart_item ) ? 1 : $cart_item['quantity'];
		if ( wps_wsp_get_signup_fee( $cart_item['data'] ) > 0 ) {

			if ( 'initial_fee_discount' == $coupon_type ) {
				$wps_wsp_apply_initial_coupon = true;
			}

			if ( 'initial_fee_percent_discount' == $coupon_type ) {
				$wps_wsp_apply_initial_percent_coupon = true;
			}

			if ( in_array( $coupon_type, array( 'initial_fee_discount', 'initial_fee_percent_discount' ) ) ) {
				$discounting_amount = wps_wsp_get_signup_fee( $cart_item['data'] );
			}
		}
		if ( wps_wsp_get_recurring_total( $cart_item['data'] ) > 0 ) {
			if ( 'recurring_product_discount' == $coupon_type ) {
				$wps_wsp_apply_initial_coupon = true;

			}

			if ( 'recurring_product_percent_discount' == $coupon_type ) {
				$wps_wsp_apply_initial_percent_coupon = true;
			}

			if ( in_array( $coupon_type, array( 'recurring_product_discount', 'recurring_product_percent_discount' ) ) ) {
				$discounting_amount = wps_wsp_get_recurring_total( $cart_item['data'] );
			}
		}

		// Calculate our discount.
		if ( $wps_wsp_apply_initial_coupon ) {

			// Recurring coupons only apply when there is no free trial (carts can have a mix of free trial and non free trial items).
			if ( $wps_wsp_apply_initial_coupon && 'recurring_product_discount' == $coupon_type && wps_get_get_trial_period( $cart_item['data'] ) > 0 ) {
				$discounting_amount = 0;
			}
			$discount_amount = min( $coupon->get_amount(), $discounting_amount );

			$discount_amount = $single ? $discount_amount : $discount_amount * $cart_item_qty;

		} elseif ( $wps_wsp_apply_initial_percent_coupon ) {

			if ( $wps_wsp_apply_initial_percent_coupon && 'recurring_product_percent_discount' == $coupon_type && wps_get_get_trial_period( $cart_item['data'] ) > 0 ) {
				$discounting_amount = 0;
			}

			$discount_amount = ( $discounting_amount / 100 ) * $coupon->get_amount();
		}

		$discount_amount = round( $discount_amount, wc_get_price_decimals() );
		return $discount_amount;
	}

	/**
	 * Validate coupon.
	 *
	 * @name wps_wsp_validate_subscription_coupon
	 * @param bool   $valid valid.
	 * @param string $wps_coupon wps_coupon.
	 * @param object $discount discount.
	 * @since    1.0.0
	 */
	public function wps_wsp_validate_subscription_coupon( $valid, $wps_coupon, $discount ) {
		if ( is_a( $discount, 'WC_Discounts' ) ) {
			$discount_items = $discount->get_items();
			if ( is_array( $discount_items ) && ! empty( $discount_items ) ) {
				$item = reset( $discount_items );

				if ( isset( $item->object ) && is_a( $item->object, 'WC_Order_Item' ) ) {

					$valid = $this->wps_wsp_validate_coupon_for_order( $valid, $wps_coupon, $item->object->get_order() );
				} else {
					$valid = $this->wps_wsp_validate_coupon_for_cart( $valid, $wps_coupon );
				}
			}
		} else {
			$valid = $this->wps_wsp_validate_coupon_for_cart( $valid, $wps_coupon );
		}
		return $valid;
	}

	/**
	 * Validate coupon.
	 *
	 * @name wps_wsp_validate_coupon_for_cart
	 * @param bool   $valid valid.
	 * @param string $wps_coupon wps_coupon.
	 * @since    1.0.0
	 */
	public function wps_wsp_validate_coupon_for_cart( $valid, $wps_coupon ) {
		$coupon_type  = $wps_coupon->get_discount_type();

		if ( ! in_array( $coupon_type, array( 'initial_fee_discount', 'initial_fee_percent_discount', 'recurring_product_discount', 'recurring_product_percent_discount' ) ) ) {
			return $valid;
		} elseif ( ! wps_wsp_check_is_cart_subscription() ) {
			// prevent subscription coupons from being applied to non-subscription products.
			$this->coupon_error = __( 'Sorry, this coupon is only valid for subscription products.', 'woocommerce-subscriptions-pro' );
			$valid = false;
		}
		return $valid;
	}

	/**
	 * Validate coupon.
	 *
	 * @name wps_wsp_coupon_error
	 * @param string $error error.
	 * @since    1.0.0
	 */
	public function wps_wsp_coupon_error( $error ) {

		if ( ! empty( $this->coupon_error ) ) {
			return $this->coupon_error;
		} else {
			return $error;
		}
	}

	/**
	 * Validate coupon.
	 *
	 * @name wps_wsp_validate_coupon_for_order
	 * @param bool   $valid valid.
	 * @param string $wps_coupon wps_coupon.
	 * @param object $order order.
	 * @throws Exception When not able to apply coupon.
	 * @since    1.0.0
	 */
	public function wps_wsp_validate_coupon_for_order( $valid, $wps_coupon, $order ) {
		$coupon_type  = $wps_coupon->get_discount_type();
		$wps_error_message = '';

		if ( ! in_array( $coupon_type, array( 'initial_fee_discount', 'initial_fee_percent_discount', 'recurring_product_discount', 'recurring_product_percent_discount' ) ) ) {

			return $valid;
		} elseif ( ! ( wps_wsp_check_is_order_subscription( $order ) || wps_wsp_check_is_renewal_order( $order ) ) ) {
			// prevent subscription coupons from being applied to non-subscription products.
			$wps_error_message = __( 'Sorry, this coupon is only valid for subscription products.', 'woocommerce-subscriptions-pro' );
		}

		if ( ! empty( $wps_error_message ) ) {
			throw new Exception( esc_html( $wps_error_message ) );
		}

		return $valid;
	}

	/**
	 * Add Email Classes.
	 *
	 * @name wps_wsp_woocommerce_email_classes
	 * @param array $email_class email_class.
	 * @since    1.0.0
	 */
	public function wps_wsp_woocommerce_email_classes( $email_class ) {

		$email_class['wps_wsp_pause_subscription'] = require_once plugin_dir_path( __DIR__ ) . 'emails/class-woocommerce-subscription-pro-pause-subscription-email.php';
		$email_class['wps_wsp_reactivate_subscription'] = require_once plugin_dir_path( __DIR__ ) . 'emails/class-woocommerce-subscription-pro-reactivate-subscription-email.php';
		$email_class['wps_wsp_renewal_subscription_invoice'] = require_once plugin_dir_path( __DIR__ ) . 'emails/class-woocommerce-subscription-pro-renewal-subscription-invoice-email.php';
		$email_class['wps_wsp_plan_going_expire'] = require_once plugin_dir_path( __DIR__ ) . 'emails/class-woocommerce-subscription-pro-plan-going-to-expire-email.php';
		$email_class['wps_wsp_recurring_reminder'] = require_once plugin_dir_path( __DIR__ ) . 'emails/class-woocommerce-subscription-pro-reminder-email.php';
		$email_class['wps_wsp_new_subscription'] = require_once plugin_dir_path( __DIR__ ) . 'emails/class-woocommerce-subscription-pro-new-subscription-email.php';

		return $email_class;
	}

	/**
	 * Order status change.
	 *
	 * @name wps_wsp_woocommerce_order_status_changed
	 * @param int    $order_id order_id.
	 * @param string $old_status old_status.
	 * @param string $new_status new_status.
	 * @since    1.0.0
	 */
	public function wps_wsp_woocommerce_order_status_changed( $order_id, $old_status, $new_status ) {
		$wps_is_manual_renewal = wps_wsp_get_meta_data( $order_id, 'wps_wsp_manual_renewal_order', true );

		if ( 'pending' != $wps_is_manual_renewal ) {
			return;
		}

		if ( $old_status != $new_status ) {
			if ( 'completed' == $new_status || 'processing' == $new_status ) {
				$wps_subscription_id = wps_wsp_get_meta_data( $order_id, 'wps_sfw_subscription', true );
				if ( wps_sfw_check_valid_subscription( $wps_subscription_id ) ) {
					$wps_subscription_status = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_subscription_status', true );
					if ( 'on-hold' == $wps_subscription_status ) {
						wps_wsp_update_meta_data( $wps_subscription_id, 'wps_subscription_status', 'active' );
						wps_wsp_update_meta_data( $order_id, 'wps_wsp_manual_renewal_order', 'success' );
					}
				}
			}
		}
	}

	/**
	 * Unset payment method on rebewal order.
	 *
	 * @name wps_wsp_reordering_order_item_totals
	 * @param array  $total_rows total_rows.
	 * @param object $order order.
	 * @param string $tax_display tax_display.
	 * @since    1.0.0
	 */
	public function wps_wsp_reordering_order_item_totals( $total_rows, $order, $tax_display ) {

		$order_id = $order->get_id();
		$wps_is_manual_renewal = wps_wsp_get_meta_data( $order_id, 'wps_wsp_manual_renewal_order', true );

		if ( 'pending' == $wps_is_manual_renewal ) {
			if ( isset( $total_rows['payment_method'] ) ) {
				 unset( $total_rows['payment_method'] );
			}
		}
		return $total_rows;
	}


	/**
	 * Switch order.
	 *
	 * @name wps_wsp_upgrade_downgrade_order_status_changed
	 * @param int    $order_id order_id.
	 * @param string $old_status old_status.
	 * @param string $new_status new_status.
	 * @since    1.0.0
	 */
	public function wps_wsp_upgrade_downgrade_order_status_changed( $order_id, $old_status, $new_status ) {
		$wps_is_switch = wps_wsp_get_meta_data( $order_id, 'wps_upgrade_downgrade_order', true );

		if ( 'yes' != $wps_is_switch ) {
			return;
		}
		$wps_is_switch_succes = wps_wsp_get_meta_data( $order_id, 'wps_upgrade_downgrade_order_succes', true );

		if ( 'success' == $wps_is_switch_succes ) {
			return;
		}

		if ( $old_status != $new_status ) {
			if ( 'completed' == $new_status || 'processing' == $new_status ) {

				$wps_subscription_id = wps_wsp_get_meta_data( $order_id, 'wps_subscription_id', true );

				if ( WC()->session->downgrade_upgrade_notice ) {
					WC()->session->__unset( 'downgrade_upgrade_notice' );
				}
				if ( wps_sfw_check_valid_subscription( $wps_subscription_id ) ) {

					$wps_is_switch_data = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_upgrade_downgrade_data', true );
					$wps_recurring_data = isset( $wps_is_switch_data['wps_wsp_switch_recurring_data'] ) ? $wps_is_switch_data['wps_wsp_switch_recurring_data'] : '';

					$wps_switch_order_id = isset( $wps_is_switch_data['wps_wsp_switch_recurring_order_id'] ) ? $wps_is_switch_data['wps_wsp_switch_recurring_order_id'] : '';

					$wps_wsp_switch_cart_data = isset( $wps_is_switch_data['wps_wsp_switch_cart_data'] ) ? $wps_is_switch_data['wps_wsp_switch_cart_data'] : '';

					$wps_wsp_switch_type = isset( $wps_is_switch_data['wps_wsp_switch_type'] ) ? $wps_is_switch_data['wps_wsp_switch_type'] : '';

					/*check valid order*/
					if ( $wps_switch_order_id != $order_id ) {
						return;
					}
					if ( isset( $wps_recurring_data ) && ! empty( $wps_recurring_data ) && is_array( $wps_recurring_data ) ) {
						if ( ! isset( $wps_recurring_data['wps_sfw_subscription_expiry_number'] ) ) {
							$wps_recurring_data['wps_sfw_subscription_expiry_number'] = 0;
						}
						if ( ! isset( $wps_recurring_data['wps_sfw_subscription_free_trial_number'] ) ) {
							$wps_recurring_data['wps_sfw_subscription_free_trial_number'] = 0;
						}
						$order = wc_get_order( $order_id );

						if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
							$new_order = new WPS_Subscription( $wps_subscription_id );
						} else {
							$new_order = new WC_Order( $wps_subscription_id );
						}

						$_product = wc_get_product( $wps_recurring_data['product_id'] );
						foreach ( $new_order->get_items() as $remove_item_id => $item ) {
							if ( $remove_item_id ) {
								$item->set_props(
									array(
										'name'         => $_product->get_name(),
										'product_id'   => $wps_recurring_data['product_id'],
										'quantity'     => $wps_recurring_data['product_qty'],
										'subtotal'     => $wps_recurring_data['line_subtotal'],
										'total'        => $wps_recurring_data['line_total'],
									)
								);
							}
						}

						$billing_details = $order->get_address( 'billing' );
						$shipping_details = $order->get_address( 'shipping' );

						$new_order->set_address( $billing_details, 'billing' );
						$new_order->set_address( $shipping_details, 'shipping' );

						$new_order->save();
						$new_order->update_taxes();
						$new_order->calculate_totals();

						wps_sfw_update_meta_key_for_susbcription( $wps_subscription_id, $wps_recurring_data );

						/*calculate next payment date*/
						$current_time = current_time( 'timestamp' );
						if ( 'downgrade' == $wps_wsp_switch_type ) {
							$wps_susbcription_trial_end = wps_sfw_susbcription_trial_date( $wps_subscription_id, $current_time );
							wps_wsp_update_meta_data( $wps_subscription_id, 'wps_susbcription_trial_end', $wps_susbcription_trial_end );
							if ( empty( $wps_susbcription_trial_end ) ) {
								$wps_next_payment_date = wps_sfw_next_payment_date( $wps_subscription_id, $current_time, $wps_susbcription_trial_end );
							} else {
								$wps_next_payment_date = isset( $wps_wsp_switch_cart_data['wps_next_payment_date'] ) ? $wps_wsp_switch_cart_data['wps_next_payment_date'] : '';
							}
							$wps_susbcription_end = wps_sfw_susbcription_expiry_date( $wps_subscription_id, $current_time, $wps_susbcription_trial_end );

							wps_wsp_update_meta_data( $wps_subscription_id, 'wps_susbcription_end', $wps_susbcription_end );
							wps_wsp_update_meta_data( $wps_subscription_id, 'wps_next_payment_date', $wps_next_payment_date );

							$wps_wsf_manage_prorate_upgrade_downgrade = get_option( 'wps_wsp_manage_prorate_amount', false );
							if ( $wps_wsf_manage_prorate_upgrade_downgrade ) {
								if ( 'wps_manage_prorate_next_payment_date' === $wps_wsf_manage_prorate_upgrade_downgrade ) {
									$get_updated_payment_date = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_wsf_manage_prorate_negativ_amount_date', true );
									if ( $get_updated_payment_date ) {
										$timestamp = strtotime( '+' . $get_updated_payment_date . 'days', $wps_next_payment_date );
										wps_wsp_update_meta_data( $wps_subscription_id, 'wps_next_payment_date', $timestamp );
										if ( $timestamp >= $wps_susbcription_end && 0 != $wps_susbcription_end ) {
											wps_wsp_update_meta_data( $wps_subscription_id, 'wps_next_payment_date', $wps_susbcription_end );
											wps_wsp_update_meta_data( $wps_subscription_id, 'wps_wsf_manage_prorate_negativ_amount_date', 0 );
										}
									}
								} elseif ( 'wps_manage_prorate_using_wallet' === $wps_wsf_manage_prorate_upgrade_downgrade ) {
									$get_left_wallet_balance = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_wsf_manage_prorate_negativ_amount_wallet', true );

									if ( $get_left_wallet_balance ) {

										$order = wc_get_order( $order_id );
										$user_id = $order->get_user_id();
										if ( 0 !== $user_id ) {
											$wallet_balance = get_user_meta( $user_id, 'wps_wallet', true );

											$wallet_balance  = empty( $wallet_balance ) ? 0 : $wallet_balance;
											$final_price = $wallet_balance + $get_left_wallet_balance;

											update_user_meta( $user_id, 'wps_Wallet', $final_price );
											wps_wsp_update_meta_data( $wps_subscription_id, 'wps_wsf_manage_prorate_negativ_amount_wallet', 0 );

										}
									}
								}
							}
						} elseif ( 'upgrade' == $wps_wsp_switch_type ) {
							$wps_susbcription_trial_end = wps_sfw_susbcription_trial_date( $wps_subscription_id, $current_time );
							wps_wsp_update_meta_data( $wps_subscription_id, 'wps_susbcription_trial_end', $wps_susbcription_trial_end );

							$wps_next_payment_date = wps_sfw_next_payment_date( $wps_subscription_id, $current_time, $wps_susbcription_trial_end );
							$get_updated_payment_date = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_next_payment_date_updated', true );
							wps_wsp_update_meta_data( $wps_subscription_id, 'wps_next_payment_date', $wps_next_payment_date );
							$wps_susbcription_end = wps_sfw_susbcription_expiry_date( $wps_subscription_id, $current_time, $wps_susbcription_trial_end );
							wps_wsp_update_meta_data( $wps_subscription_id, 'wps_susbcription_end', $wps_susbcription_end );

							$wps_wsf_manage_prorate_upgrade_downgrade = get_option( 'wps_wsp_manage_prorate_amount', false );
							if ( $wps_wsf_manage_prorate_upgrade_downgrade ) {
								if ( 'wps_manage_prorate_next_payment_date' === $wps_wsf_manage_prorate_upgrade_downgrade ) {
									$get_updated_payment_date = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_wsf_manage_prorate_negativ_amount_date', true );
									if ( $get_updated_payment_date ) {
										$timestamp = strtotime( '+' . $get_updated_payment_date . 'days', $wps_next_payment_date );
										wps_wsp_update_meta_data( $wps_subscription_id, 'wps_next_payment_date', $timestamp );
										if ( $timestamp >= $wps_susbcription_end ) {
											wps_wsp_update_meta_data( $wps_subscription_id, 'wps_next_payment_date', $wps_susbcription_end );
											wps_wsp_update_meta_data( $wps_subscription_id, 'wps_wsf_manage_prorate_negativ_amount_date', 0 );
										}
									}
								} elseif ( 'wps_manage_prorate_using_wallet' === $wps_wsf_manage_prorate_upgrade_downgrade ) {
									$get_left_wallet_balance = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_wsf_manage_prorate_negativ_amount_wallet', true );

									if ( $get_left_wallet_balance ) {

										$order = wc_get_order( $order_id );
										$user_id = $order->get_user_id();
										if ( 0 !== $user_id ) {
											$wallet_balance = get_user_meta( $user_id, 'wps_wallet', true );

											$wallet_balance  = empty( $wallet_balance ) ? 0 : $wallet_balance;
											$final_price = $wallet_balance + $get_left_wallet_balance;

											update_user_meta( $user_id, 'wps_Wallet', $final_price );
											wps_wsp_update_meta_data( $wps_subscription_id, 'wps_wsf_manage_prorate_negativ_amount_wallet', 0 );

										}
									}
								}
							}
						}

						$wps_switch_order_data = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_wsp_switch_order_data', true );
						if ( empty( $wps_switch_order_data ) ) {
							$wps_switch_order_data = array( $order_id );
							wps_wsp_update_meta_data( $wps_subscription_id, 'wps_wsp_switch_order_data', $wps_switch_order_data );
						} else {
							$wps_switch_order_data[] = $order_id;
							wps_wsp_update_meta_data( $wps_subscription_id, 'wps_wsp_switch_order_data', $wps_switch_order_data );
						}
						wps_wsp_update_meta_data( $wps_subscription_id, 'wps_wsp_last_switch_order_id', $order_id );

						wps_wsp_update_meta_data( $order_id, 'wps_upgrade_downgrade_order_succes', 'success' );
					}
				}
			}
		}
	}

	/**
	 * First payment date.
	 *
	 * @name wps_wsp_first_payment_date_for_sync
	 * @param int $wps_next_payment_date wps_next_payment_date.
	 * @param int $wps_subscription_id wps_subscription_id.
	 * @since    1.0.0
	 */
	public function wps_wsp_first_payment_date_for_sync( $wps_next_payment_date, $wps_subscription_id ) {

		if ( wps_sfw_check_valid_subscription( $wps_subscription_id ) ) {

			$product_id = wps_wsp_get_meta_data( $wps_subscription_id, 'product_id', true );
			if ( wps_wsp_start_susbcription_from_certain_date_of_month() && wps_wsp_subscription_syn_enable_per_product( $product_id ) ) {
				$wps_first_payment_date = wps_wsp_get_sync_first_payment_date_for_price( $product_id );

				if ( ! empty( $wps_first_payment_date ) && ! wps_wsp_check_is_today_date( $wps_first_payment_date ) ) {
					$wps_next_payment_date = $wps_first_payment_date;
					wps_wsp_update_meta_data( $wps_subscription_id, 'wps_wsp_first_payment_date', $wps_first_payment_date );
				}
			}
		}
		return $wps_next_payment_date;
	}

	/**
	 * Expiry interval for variation products.
	 *
	 * @name wps_wsp_variation_expiry
	 * @since    1.0.0
	 */
	public function wps_wsp_variation_expiry() {
		check_ajax_referer( 'wps-wsp-verify-nonce', 'wps_wsp_nonce' );
		$wps_response = array();
		$wps_response['result'] = false;
		if ( isset( $_POST['variation_id'] ) && ! empty( $_POST['variation_id'] ) ) {
			$variation_id = sanitize_text_field( wp_unslash( $_POST['variation_id'] ) );
			$product = wc_get_product( $variation_id );

			if ( wps_sfw_check_product_is_subscription( $product ) ) {

				$subscription_interval = wps_wsp_get_meta_data( $variation_id, 'wps_sfw_subscription_interval', true );
				if ( empty( $subscription_interval ) ) {
					$subscription_interval = 'day';
				}

				$wps_response['wps_interval'] = $subscription_interval;
				$wps_response['result'] = true;
			}
		}
		wp_send_json( $wps_response );
	}


	/**
	 * Set discount type for giftcard.
	 *
	 * @name wps_wsp_discount_type_for_giftcard
	 * @param string $discount_type discount_type.
	 * @since    1.1.0
	 */
	public function wps_wsp_discount_type_for_giftcard( $discount_type ) {
		if ( wps_wsp_get_subscription_coupon_enable_for_gc() ) {
			$discount_type = wps_wsp_get_subscription_coupon_type_for_gc();
		}
		return $discount_type;
	}

	/**
	 * Apply giftcard.
	 *
	 * @name wps_wsp_apply_giftcard_coupon
	 * @since    1.1.0
	 */
	public function wps_wsp_apply_giftcard_coupon() {
		check_ajax_referer( 'wps-wsp-verify-nonce', 'wps_wsp_nonce' );
		$wps_response = array();
		$wps_response['result'] = false;
		if ( isset( $_POST['subscription_id'] ) && ! empty( $_POST['subscription_id'] ) ) {
			$subscription_id = sanitize_text_field( wp_unslash( $_POST['subscription_id'] ) );
			$coupon_code = isset( $_POST['coupon_code'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) : '';
			if ( wps_sfw_check_valid_subscription( $subscription_id ) ) {
				$already_used_gift_card = wps_wsp_get_meta_data( $subscription_id, 'wps_wgm_giftcard_coupon', true );
				if ( empty( $already_used_gift_card ) ) {
					$coupon = new WC_Coupon( $coupon_code );
					$coupon_id = $coupon->get_id();

					if ( '' !== $coupon_id && 0 !== $coupon_id ) {

						$giftcardcoupon_order_id = wps_wsp_get_meta_data( $coupon_id, 'wps_wgm_giftcard_coupon', true );
						if ( isset( $giftcardcoupon_order_id ) && '' !== $giftcardcoupon_order_id ) {

							$coupon_usage_count = wps_wsp_get_meta_data( $coupon_id, 'usage_count', true );
							$coupon_usage_limit = wps_wsp_get_meta_data( $coupon_id, 'usage_limit', true );

							if ( 0 == $coupon_usage_limit || $coupon_usage_limit > $coupon_usage_count ) {
								$coupon_expiry = wps_wsp_get_meta_data( $coupon_id, 'date_expires', true );
								if ( '' == $coupon_expiry || $coupon_expiry > current_time( 'timestamp' ) ) {

									wps_wsp_update_meta_data( $subscription_id, 'wps_wgm_giftcard_coupon', strtolower( $coupon_code ) );
									$wps_response['msg'] = __( 'Coupon Applied Successfully', 'woocommerce-subscriptions-pro' );
									$wps_response['result'] = true;
								} else {
									$wps_response['msg'] = __( 'Coupon is expired', 'woocommerce-subscriptions-pro' );
								}
							} else {
								$wps_response['msg'] = __( 'Coupon is already used', 'woocommerce-subscriptions-pro' );
							}
						} else {
							$wps_response['msg'] = __( 'Coupon is not valid Giftcard', 'woocommerce-subscriptions-pro' );
						}
					} else {
						$wps_response['msg'] = __( 'Coupon is not valid Giftcard', 'woocommerce-subscriptions-pro' );

					}
				} else {
					$wps_response['msg'] = __( 'Gift Card already applied', 'woocommerce-subscriptions-pro' );

				}
			}
		}
		wp_send_json( $wps_response );
	}

	/**
	 * Validate giftcard coupon..
	 *
	 * @name wps_wsp_subscription_renewal_order_coupon
	 * @param bool   $valid $valid.
	 * @param int    $order_id $order_id.
	 * @param object $coupon $coupon.
	 * @since    1.1.0
	 */
	public function wps_wsp_subscription_renewal_order_coupon( $valid, $order_id, $coupon ) {
		// Return if renewal order.
		$order = wc_get_order( $order_id );
		if ( wps_wsp_check_is_renewal_order( $order ) ) {
			$valid = true;
		} elseif ( isset( $coupon ) && is_object( $coupon ) ) {
			$coupon_type  = $coupon->get_discount_type();
			if ( in_array( $coupon_type, apply_filters( 'wps_wsp_validate_coupon_type_for_giftcard', array( 'initial_fee_discount', 'initial_fee_percent_discount', 'recurring_product_discount', 'recurring_product_percent_discount' ) ) ) ) {
				$valid = true;
			}
		}
		return $valid;
	}

	/**
	 * Order status change.
	 *
	 * @name wps_wsp_update_giftcard_coupon_amount
	 * @param int    $order_id order_id.
	 * @param string $old_status old_status.
	 * @param string $new_status new_status.
	 * @since    1.0.0
	 */
	public function wps_wsp_update_giftcard_coupon_amount( $order_id, $old_status, $new_status ) {

		$order = wc_get_order( $order_id );
		if ( ! wps_wsp_check_is_renewal_order( $order ) ) {
			return;
		}

		$wps_is_gc_amount = wps_wsp_get_meta_data( $order_id, 'wps_wsp_gc_coupon_updated', true );
		if ( 'yes' == $wps_is_gc_amount ) {
			return;
		}
		if ( $old_status != $new_status ) {
			if ( in_array( $new_status, apply_filters( 'wps_wsp_check_order_status', array( 'processing', 'completed', 'on-hold' ) ) ) ) {

				$order = wc_get_order( $order_id );

				$subscription_id = wps_wsp_get_meta_data( $order_id, 'wps_sfw_subscription', true );

				if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
					$subscription = new WPS_Subscription( $subscription_id );
				} else {
					$subscription = wc_get_order( $subscription_id );
				}
				$order_currency = $subscription->get_currency();

				$coupon_itmes = $order->get_items( 'coupon' );
				if ( isset( $coupon_itmes ) && ! empty( $coupon_itmes ) && is_array( $coupon_itmes ) ) {

					foreach ( $coupon_itmes as $item_id => $item ) {
						$coupon_code = $item->get_code();
						$the_coupon = new WC_Coupon( $coupon_code );
						$coupon_id = $the_coupon->get_id();
						if ( isset( $coupon_id ) ) {
							$rate = 1;
							// price based on country.
							if ( class_exists( 'WCPBC_Pricing_Zone' ) ) {

								if ( wcpbc_the_zone() != null && wcpbc_the_zone() ) {

									$rate = wcpbc_the_zone()->get_exchange_rate();
								}
							}
							$giftcardcoupon = wps_wsp_get_meta_data( $coupon_id, 'wps_wgm_giftcard_coupon', true );
							if ( ! empty( $giftcardcoupon ) ) {

								$coupon_type  = $the_coupon->get_discount_type();

								if ( ! in_array( $coupon_type, apply_filters( 'wps_wsp_validate_coupon_type_for_giftcard', array( 'initial_fee_discount', 'initial_fee_percent_discount', 'recurring_product_discount', 'recurring_product_percent_discount' ) ) ) ) {

									if ( class_exists( 'Woocommerce_Gift_Cards_Common_Function' ) ) {
										$wps_wgm_discount = $item->get_discount();
										$wps_wgm_discount_tax = $item->get_discount_tax();
										$amount = wps_wsp_get_meta_data( $coupon_id, 'coupon_amount', true );
										$wps_common_fun = new Woocommerce_Gift_Cards_Common_Function();
										$total_discount = $wps_common_fun->wps_wgm_calculate_coupon_discount( $wps_wgm_discount, $wps_wgm_discount_tax );
										$total_discount = $total_discount / $rate;

										// For currency switchers.
										if ( wps_sfw_check_valid_subscription( $subscription_id ) ) {
											if ( function_exists( 'wps_mmcsfw_admin_fetch_currency_rates_to_base_currency' ) ) {

												$total_discount = wps_mmcsfw_admin_fetch_currency_rates_to_base_currency( $order_currency, $total_discount );
											}
										}
										if ( $amount < $total_discount ) {
											$remaining_amount = 0;
										} else {
											$remaining_amount = $amount - $total_discount;
											$remaining_amount = round( $remaining_amount, 2 );
										}
										wps_wsp_update_meta_data( $coupon_id, 'coupon_amount', $remaining_amount );
										wps_wsp_update_meta_data( $order_id, 'wps_wsp_gc_coupon_updated', 'yes' );
										do_action( 'wps_wgm_send_mail_remaining_amount', $coupon_id, $remaining_amount );
										do_action( 'wps_wgm_coupon_reporting_with_order', $coupon_id, $item, $total_discount, $remaining_amount );
									}
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Add subscription type coupon for currency switchers.
	 *
	 * @name wps_wsp_currency_switcher_set_supported_coupon_type
	 * @param int    $discount discount.
	 * @param object $coupon coupon.
	 * @since    1.1.0
	 */
	public function wps_wsp_currency_switcher_set_supported_coupon_type( $discount, $coupon ) {

		if ( get_option( 'mmcsfw_radio_switch_demo' ) !== 'on' ) {
			return $discount;
		}

		$wps_discount_types = array(
			'initial_fee_discount',
			'initial_fee_percent_discount',
			'recurring_product_discount',
			'recurring_product_percent_discount',
		);
		if ( in_array( $coupon->get_discount_type(), $wps_discount_types ) ) {
			$default_price = '';
			if ( WC()->session->__isset( 's_selected_currency' ) ) {
				$default_price = WC()->session->get( 's_selected_currency' );
			}
			if ( ! empty( $default_price ) ) {
				$mcs_price = get_option( 'wps_mmcsfw_text_rate_' . $default_price );

				$decimal = get_option( 'wps_mmcsfw_decimial_' . $default_price );
				$cents   = get_option( 'wps_mmcsfw_cents_' . $default_price );
				if ( empty( $decimal ) ) {
					$decimal = 0;
				}
				if ( 'hide' === $cents ) {
					$decimal = 0;
				}
				if ( 0 === $decimal ) {
					$discount  = floatval( $discount * round( $mcs_price, 2 ) );
				} else {
					$discount  = floatval( $discount * round( $mcs_price, $decimal ) );
				}
				return $discount;
			}
		}
		return $discount;
	}

	/**
	 * Check Subscription type products.
	 *
	 * @name wps_sfw_is_variable_subscription_product_type
	 * @param int    $wps_is_subscription wps_is_subscription.
	 * @param object $product product.
	 * @since    1.0.0
	 */
	public function wps_sfw_is_variable_subscription_product_type( $wps_is_subscription, $product ) {
		if ( is_object( $product ) ) {
			$product_id = $product->get_id();
			$wps_subscription_product = wps_wsp_get_meta_data( $product_id, 'wps_sfw_variable_product', true );
			if ( 'yes' === $wps_subscription_product ) {
				$wps_is_subscription = true;
			}
		}
		return apply_filters( 'wps_sfw_subscription_product_type', $wps_is_subscription, $product );
	}

	/**
	 * Set the trial period on the new site.
	 *
	 * @param object $new_site as new site.
	 * @param String $args as args.
	 */
	public function wps_wsp_standard_plugin_on_new_site( $new_site, $args ) {
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}
		$license_code      = get_option( 'wps_wsp_license_key' );

		// check if the plugin has been activated on the network.
		if ( is_plugin_active_for_network( 'woocommerce-subscriptions-pro/woocommerce-subscriptions-pro.php' ) ) {
			$blog_id = $new_site->blog_id;
			switch_to_blog( $blog_id );

			require_once WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_PATH . 'includes/class-woocommerce-subscriptions-pro-activator.php';
			Woocommerce_Subscriptions_Pro_Activator::woocommerce_subscriptions_pro_activate( $new_site );
			// Skip the multi-step.
			update_option( 'wps_sfw_multistep_done', 'yes' );
			update_option( 'wps_sfw_enable_plugin', 'on' );

			// API query parameters.
			$wps_wsp_license_data = $this->wps_wsp_license_key_validation( $license_code );

			if ( isset( $wps_wsp_license_data->result ) && 'success' === $wps_wsp_license_data->result && isset( $wps_wsp_license_data->status ) && 'active' === $wps_wsp_license_data->status ) {
				update_option( 'wps_wsp_license_key', $wps_wsp_license_key );
				update_option( 'wps_wsp_license_key_status', 'true' );
			} else {
				delete_option( 'wps_wsp_license_key' );
				update_option( 'wps_wsp_license_key_status', 'false' );
			}

			restore_current_blog();
		}
	}

	/**
	 * Function to validate/activate the license key.
	 *
	 * @param string $wps_wsp_license_key .
	 * @return object
	 */
	public function wps_wsp_license_key_validation( $wps_wsp_license_key ) {
		// API query parameters.
		$wps_wsp_api_params = array(
			'slm_action' => 'slm_activate',
			'secret_key' => WOOCOMMERCE_SUBSCRIPTIONS_PRO_SPECIAL_SECRET_KEY,
			'license_key' => $wps_wsp_license_key,
			'registered_domain' => isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : home_url(),
			'item_reference' => urlencode( WOOCOMMERCE_SUBSCRIPTIONS_PRO_ITEM_REFERENCE ),
			'product_reference' => WOOCOMMERCE_SUBSCRIPTIONS_PRO_PRODUCT_REFERENCE,
		);

		// Send query to the license manager server.
		$wps_wsp_query = esc_url_raw( add_query_arg( $wps_wsp_api_params, WOOCOMMERCE_SUBSCRIPTIONS_PRO_SERVER_URL ) );

		$wps_wsp_response = wp_remote_get(
			$wps_wsp_query,
			array(
				'timeout' => 50,
				'sslverify' => false,
				'user-agent' => 'WooCommerce Subscriptions PRO/' . $this->version,
			)
		);
		$wps_wsp_license_data = json_decode( wp_remote_retrieve_body( $wps_wsp_response ) );
		return $wps_wsp_license_data;
	}

	/**
	 * Function to add bundle in subscription and renewal order.
	 *
	 * @param array $new_order_id as new order id.
	 * @param array $wps_old_id as old order id.
	 * @param array $product as product.
	 * @return void
	 */
	public function wps_sfw_subscription_bundle_addition_callback( $new_order_id, $wps_old_id, $product ) {

		$order = wc_get_order( $wps_old_id );
		if ( empty( $order ) || empty( $product ) ) {
			return;
		}

		$order_items = $order->get_items();
		$temp = 0;
		$product_type = $product->get_type();

		foreach ( $order_items as $items_key => $items_value ) {
			if ( 0 != $temp && 'bundle' == $product_type ) {
				$order_item_id = wc_add_order_item(
					$new_order_id,
					array(
						'order_item_name' => $items_value['name'], // may differ from the product name.
						'order_item_type' => 'line_item', // product.
					)
				);
				if ( $order_item_id ) {
					// provide its meta information.
					wc_add_order_item_meta( $order_item_id, '_qty', $items_value['qty'], true ); // quantity.
					wc_add_order_item_meta( $order_item_id, '_product_id', $items_value['product_id'], true ); // ID of the product.
				}
			}
			$temp++;
		}
	}

	/**
	 * Function To save product for renewal order based on manual subscription.
	 *
	 * @param int $renewal_order_id as renewal order id.
	 * @param int $subscription_id as subscription order id.
	 * @return void
	 */
	public function wps_sfw_add_new_product_for_manual_subscription_callback( $renewal_order_id, $subscription_id ) {

		$payment_type = wps_wsp_get_meta_data( $subscription_id, 'wps_wsp_payment_type', true );
		if ( 'wps_wsp_manual_method' == $payment_type ) {

			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
				$order = new WPS_Subscription( $subscription_id );
			} else {
				$order = wc_get_order( $subscription_id );
			}
			$renewal_order = wc_get_order( $renewal_order_id );

			if ( ! $renewal_order instanceof WC_Order ) {
				return;
			}
			$order_items = $order->get_items();

			foreach ( $order_items as $items_key => $item ) {
				$product  = wc_get_product( $item->get_product_id() );
				$quantity = $item->get_quantity();

				$wps_args = array(
					'variation' => array(),
					'totals'    => array(
						'subtotal'     => $item->get_subtotal(),
						'subtotal_tax' => $item->get_subtotal_tax(),
						'total'        => $item->get_total(),
						'tax'          => $item->get_total_tax(),
						'tax_data'     => $item->get_taxes(),
					),
				);

				if ( $product ) {
					$renewal_order->add_product( $product, $quantity, $wps_args );
				}
			}
			$renewal_order->calculate_totals();
			$renewal_order->save();
		}
	}

	/**
	 * This function is used create variable susbcription field.
	 *
	 * @name wsp_renewal_order_apply_coupon
	 * @param object $wps_renewal_order wps_renewal_order.
	 * @param int    $wps_subscription_id wps_subscription_id.
	 * @since 1.0.0
	 */
	public function wsp_renewal_order_apply_coupon( $wps_renewal_order, $wps_subscription_id ) {

		if ( isset( $wps_renewal_order ) && ! empty( $wps_renewal_order ) ) {
			$order_id = $wps_renewal_order->get_id();
			$is_renewal_success = wps_wsp_get_meta_data( $order_id, '_wps_is_renewal_success', true );
			Subscriptions_For_Woocommerce_Log::log( 'Wps Coupon log: ' . wc_print_r( 'Check1', true ) );
			if ( 'yes' == $is_renewal_success ) {
				return;
			}
			Subscriptions_For_Woocommerce_Log::log( 'Wps Coupon log: ' . wc_print_r( 'Check2', true ) );

			$recurring_product_discount = wps_wsp_get_meta_data( $wps_subscription_id, 'recurring_product_discount', true );
			$recurring_product_percent_discount = wps_wsp_get_meta_data( $wps_subscription_id, 'recurring_product_percent_discount', true );
			// Apply giftcard coupon.
			$wps_wgm_giftcard_coupon = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_wgm_giftcard_coupon', true );

			Subscriptions_For_Woocommerce_Log::log( 'Wps Coupon log: ' . wc_print_r( 'Check3', true ) );
			// Return id standard paypal subscription.
			$wps_sfw_paypal_subscriber_id = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_sfw_paypal_subscriber_id', true );
			if ( isset( $wps_sfw_paypal_subscriber_id ) && ! empty( $wps_sfw_paypal_subscriber_id ) ) {
				return;
			}
			Subscriptions_For_Woocommerce_Log::log( 'Wps Coupon log: ' . wc_print_r( 'Check4', true ) );

			if ( isset( $recurring_product_discount ) && ! empty( $recurring_product_discount ) ) {
				Subscriptions_For_Woocommerce_Log::log( 'Wps Coupon log: ' . wc_print_r( 'Check5', true ) );
				$result = $wps_renewal_order->apply_coupon( $recurring_product_discount );
				if ( ! is_wp_error( $result ) ) {
					$wps_renewal_order->get_data_store()->set_recorded_coupon_usage_counts( $wps_renewal_order, true );
				}
			} elseif ( isset( $recurring_product_percent_discount ) && ! empty( $recurring_product_percent_discount ) ) {
				Subscriptions_For_Woocommerce_Log::log( 'Wps Coupon log: ' . wc_print_r( 'Check6', true ) );
				$result = $wps_renewal_order->apply_coupon( $recurring_product_percent_discount );
				if ( ! is_wp_error( $result ) ) {
					$wps_renewal_order->get_data_store()->set_recorded_coupon_usage_counts( $wps_renewal_order, true );

				}
			} elseif ( isset( $wps_wgm_giftcard_coupon ) && ! empty( $wps_wgm_giftcard_coupon ) ) {
				Subscriptions_For_Woocommerce_Log::log( 'Wps Coupon log: ' . wc_print_r( 'Check7', true ) );
				if ( wps_wsp_get_subscription_coupon_enable_for_gc() ) {
					$the_coupon = new WC_Coupon( $wps_wgm_giftcard_coupon );
					$coupon_id = $the_coupon->get_id();
					if ( isset( $coupon_id ) ) {
						$amount = wps_wsp_get_meta_data( $coupon_id, 'coupon_amount', true );
						if ( 0 < $amount ) {
							$result = $wps_renewal_order->apply_coupon( $wps_wgm_giftcard_coupon );
							if ( ! is_wp_error( $result ) ) {
								$wps_renewal_order->get_data_store()->set_recorded_coupon_usage_counts( $wps_renewal_order, true );

							}
						}
					}
				}
			}
			wps_wsp_update_meta_data( $order_id, '_wps_is_renewal_success', 'yes' );

		}
	}

	/**
	 * This function is used cancel failed subscription.
	 *
	 * @name wps_wsp_cancel_failed_susbcription_callback
	 * @param bool $result result.
	 * @param int  $order_id order_id.
	 * @param bool $subscription_id subscription_id.
	 * @since 1.0.0
	 */
	public function wps_wsp_cancel_failed_susbcription_callback( $result, $order_id, $subscription_id ) {
		if ( ! $result ) {

			$wps_wsp_failed_attemp = wps_wsp_get_meta_data( $subscription_id, 'wps_wsp_failed_attemp_for_subscription', true );
			if ( empty( $wps_wsp_failed_attemp ) ) {

				wps_wsp_update_meta_data( $subscription_id, 'wps_wsp_failed_attemp_for_subscription', 1 );
			} else {
				$wps_wsp_failed_attemp = ++$wps_wsp_failed_attemp;

				wps_wsp_update_meta_data( $subscription_id, 'wps_wsp_failed_attemp_for_subscription', $wps_wsp_failed_attemp );
			}

			$wps_wsp_failed_order = wps_wsp_get_meta_data( $subscription_id, 'wps_wsp_failed_order_for_subscription', true );
			if ( empty( $wps_wsp_failed_order ) ) {
				$wps_wsp_failed_order = array( $order_id );
				wps_wsp_update_meta_data( $subscription_id, 'wps_wsp_failed_order_for_subscription', $wps_wsp_failed_order );
			} else {
				$wps_wsp_failed_order[] = $order_id;
				wps_wsp_update_meta_data( $subscription_id, 'wps_wsp_failed_order_for_subscription', $wps_wsp_failed_order );
			}
			$wps_wsp_failed_attemp = wps_wsp_get_meta_data( $subscription_id, 'wps_wsp_failed_attemp_for_subscription', true );
			$wps_cancel_subscription = wps_wsp_after_no_failed_attempt_cancel();

			if ( $wps_wsp_failed_attemp >= $wps_cancel_subscription ) {
				wps_wsp_update_meta_data( $subscription_id, 'wps_subscription_status', 'cancelled' );
				wps_sfw_send_email_for_cancel_susbcription( $subscription_id );
			}
		}
	}

	/**
	 * Creating subscription based on upselling products
	 *
	 * @param object  $order .
	 * @param array() $product_data .
	 * @param object  $child_order .
	 */
	public function cartflow_subscription_creation_while_upselling( $order, $product_data, $child_order ) {
		foreach ( $child_order->get_items() as $item_id => $item ) {
			$product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();

			if ( function_exists( 'wps_sfw_check_product_is_subscription' ) && ! wps_sfw_check_product_is_subscription( wc_get_product( $product_id ) ) ) {
				continue;
			}

			$product         = $item->get_product();
			$product_name    = $item->get_name();
			$quantity        = $item->get_quantity();
			$subtotal        = $item->get_subtotal();
			$total           = $item->get_total();
			$subtotal_tax    = $item->get_subtotal_tax();
			$tax             = $item->get_total_tax();
			$parent_order_id = $order->get_id();
			$current_date    = current_time( 'timestamp' );

			$wps_args = array(
				'wps_parent_order'   => $parent_order_id,
				'wps_customer_id'    => $order->get_user_id(),
				'wps_schedule_start' => $current_date,
				'product_id'         => $product_id,
				'product_name'       => $product_name,
				'product_qty'        => $quantity,
			);

			$wps_recurring_data = array();

			$wps_sfw_subscription_number = wps_sfw_get_meta_data( $product_id, 'wps_sfw_subscription_number', true );
			$wps_sfw_subscription_interval = wps_sfw_get_meta_data( $product_id, 'wps_sfw_subscription_interval', true );

			$wps_recurring_data['wps_sfw_subscription_number'] = $wps_sfw_subscription_number;
			$wps_recurring_data['wps_sfw_subscription_interval'] = $wps_sfw_subscription_interval;
			$wps_sfw_subscription_expiry_number = wps_sfw_get_meta_data( $product_id, 'wps_sfw_subscription_expiry_number', true );

			if ( isset( $wps_sfw_subscription_expiry_number ) && ! empty( $wps_sfw_subscription_expiry_number ) ) {
				$wps_recurring_data['wps_sfw_subscription_expiry_number'] = $wps_sfw_subscription_expiry_number;
			}

			$wps_sfw_subscription_expiry_interval = wps_sfw_get_meta_data( $product_id, 'wps_sfw_subscription_expiry_interval', true );

			if ( isset( $wps_sfw_subscription_expiry_interval ) && ! empty( $wps_sfw_subscription_expiry_interval ) ) {
				$wps_recurring_data['wps_sfw_subscription_expiry_interval'] = $wps_sfw_subscription_expiry_interval;
			}
			$wps_sfw_subscription_initial_signup_price = wps_sfw_get_meta_data( $product_id, 'wps_sfw_subscription_initial_signup_price', true );

			if ( isset( $wps_sfw_subscription_expiry_interval ) && ! empty( $wps_sfw_subscription_expiry_interval ) ) {
				$wps_recurring_data['wps_sfw_subscription_initial_signup_price'] = $wps_sfw_subscription_initial_signup_price;
			}

			$wps_sfw_subscription_free_trial_number = wps_sfw_get_meta_data( $product_id, 'wps_sfw_subscription_free_trial_number', true );

			if ( isset( $wps_sfw_subscription_free_trial_number ) && ! empty( $wps_sfw_subscription_free_trial_number ) ) {
				$wps_recurring_data['wps_sfw_subscription_free_trial_number'] = $wps_sfw_subscription_free_trial_number;
			}
			$wps_sfw_subscription_free_trial_interval = wps_sfw_get_meta_data( $product_id, 'wps_sfw_subscription_free_trial_interval', true );
			if ( isset( $wps_sfw_subscription_free_trial_interval ) && ! empty( $wps_sfw_subscription_free_trial_interval ) ) {
				$wps_recurring_data['wps_sfw_subscription_free_trial_interval'] = $wps_sfw_subscription_free_trial_interval;
			}
			$wps_recurring_data = apply_filters( 'wps_sfw_recurring_data', $wps_recurring_data, $product_id );

			$show_price = $total + $tax;

			$wps_recurring_data['wps_recurring_total'] = $show_price;
			$wps_recurring_data['wps_show_recurring_total'] = $show_price;
			$wps_recurring_data['product_id']   = $product_id;
			$wps_recurring_data['product_name'] = $product_name;
			$wps_recurring_data['product_qty']  = $quantity;

			$wps_recurring_data['line_tax_data'] = array(
				'subtotal' => array( $subtotal_tax ),
				'total'    => array( $tax ),
			);
			$wps_recurring_data['line_subtotal'] = $subtotal;
			$wps_recurring_data['line_subtotal_tax'] = $subtotal_tax;
			$wps_recurring_data['line_total'] = $total;
			$wps_recurring_data['line_tax'] = $tax;

			$wps_recurring_data = apply_filters( 'wps_sfw_cart_data_for_susbcription', $wps_recurring_data, $cart_item );

			if ( apply_filters( 'wps_sfw_is_upgrade_downgrade_order', false, $wps_recurring_data, $order, $posted_data, $cart_item ) ) {
				return;
			}
			// Subscription creation code start.

			if ( ! empty( $order ) ) {
				$order_id     = $order->get_id();
				$current_date = current_time( 'timestamp' );

				$wps_default_args = array(
					'wps_parent_order'   => $order_id,
					'wps_customer_id'    => $order->get_user_id(),
					'wps_schedule_start' => $current_date,
				);

				$wps_args = wp_parse_args( $wps_recurring_data, $wps_default_args );
				$wps_args['wps_order_currency'] = $order->get_currency();
				$wps_args['wps_subscription_status'] = 'pending';

				$wps_args = apply_filters( 'wps_sfw_new_subscriptions_data', $wps_args );
				// translators: post title date parsed by strftime.
				$post_title_date = gmdate( _x( '%1$b %2$d, %Y @ %I:%M %p', 'subscription post title. "Subscriptions order - <this>"', 'woocommerce-subscriptions-pro' ) );
				$wps_subscription_data = array();
				$wps_subscription_data['post_type']     = 'wps_subscriptions';

				$wps_subscription_data['post_status']   = 'wc-wps_renewal';
				$wps_subscription_data['post_author']   = 1;
				$wps_subscription_data['post_parent']   = $order_id;
				/* translators: %s: post title date */
				$wps_subscription_data['post_title']    = sprintf( _x( 'WPS Subscription &ndash; %s', 'Subscription post title', 'woocommerce-subscriptions-pro' ), $post_title_date );
				$wps_subscription_data['post_date_gmt'] = $order->get_date_created()->date( 'Y-m-d H:i:s' );
				$wps_subscription_data['post_date_gmt'] = $order->get_date_created()->date( 'Y-m-d H:i:s' );

				if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {

					$subscription_order = wps_create_subscription();
					$subscription_id    = $subscription_order->get_id();

					$subscription_order->set_customer_id( $order->get_user_id() );

					$new_order = new WPS_Subscription( $subscription_id );
					$new_order->update_status( 'wc-wps_renewal' );
				} else {
					$subscription_id = wp_insert_post( $wps_subscription_data, true );

					$new_order = wc_get_order( $subscription_id );
				}
				if ( ! $subscription_id ) {
					return;
				}

				wps_wsp_update_meta_data( $order_id, 'wps_subscription_id', $subscription_id );
				wps_wsp_update_meta_data( $subscription_id, 'wps_susbcription_trial_end', 0 );
				wps_wsp_update_meta_data( $subscription_id, 'wps_susbcription_end', 0 );
				wps_wsp_update_meta_data( $subscription_id, 'wps_next_payment_date', 0 );
				wps_wsp_update_meta_data( $subscription_id, '_order_key', wc_generate_order_key() );

				$_product = wc_get_product( $product_id );

				$billing_details  = $order->get_address( 'billing' );
				$shipping_details = $order->get_address( 'shipping' );

				$new_order->set_address( $billing_details, 'billing' );
				$new_order->set_address( $shipping_details, 'shipping' );

				$new_order->set_payment_method( $order->get_payment_method() );
				$new_order->set_payment_method_title( $order->get_payment_method_title() );

				$new_order->set_currency( $order->get_currency() );

				$line_subtotal   = $wps_args['line_subtotal'];
				$line_total      = $wps_args['line_total'];
				$total_taxes     = $wps_args['line_tax'];
				$substotal_taxes = $wps_args['line_subtotal_tax'];

				$wps_pro_args = array(
					'variation' => array(),
					'totals'    => array(
						'subtotal'     => $line_subtotal,
						'subtotal_tax' => $substotal_taxes,
						'total'        => $line_total,
						'tax'          => $total_taxes,
						'tax_data'     => array(
							'subtotal' => array( $substotal_taxes ),
							'total'    => array( $total_taxes ),
						),
					),
				);

				$wps_pro_args = apply_filters( 'wps_product_args_for_order', $wps_pro_args );

				$wps_args = apply_filters( 'wps_product_args_for_renewal_order_propate_amount', $wps_args, $cart_item );

				$item_id = $new_order->add_product(
					$_product,
					$wps_args['product_qty'],
					$wps_pro_args
				);
				$new_order->update_taxes();
				$new_order->calculate_totals();
				$new_order->save();

				do_action( 'wps_sfw_subscription_bundle_addition', $subscription_id, $order_id, $_product );

				// After susbcription order created.
				do_action( 'wps_sfw_subscription_order', $new_order, $order_id );

				// new subscription meta from the version  1.5.8.
				wps_wsp_update_meta_data( $subscription_id, 'wps_sfw_new_sub', 'yes' );

				wps_sfw_update_meta_key_for_susbcription( $subscription_id, $wps_args );
				// After susbcription order created.
				do_action( 'wps_sfw_after_created_subscription', $subscription_id, $order_id );
				// After susbcription created.

				$wps_has_susbcription = wps_sfw_get_meta_data( $order_id, 'wps_sfw_order_has_subscription', true );
				if ( 'yes' != $wps_has_susbcription ) {
					wps_wsp_update_meta_data( $order_id, 'wps_sfw_order_has_subscription', 'yes' );
				}
			}
		}
	}


	/**
	 * This action hook registers our PHP class as a WooCommerce payment gateway
	 *
	 * @param array() $gateways All Available Woo Gateways .
	 */
	public function wps_paypal_subscription_add_gateway_class( $gateways ) {
		$gateways[] = 'WPS_Paypal_Subscription_Integration_Gateway';
		return $gateways;
	}

	/**
	 * Redirect to custom pay page
	 *
	 * @param string $template .
	 * @param string $template_name .
	 * @param string $template_path .
	 */
	public function wps_pay_form_custom_template( $template, $template_name, $template_path ) {

		global $wp;
		if ( 'checkout/form-pay.php' === $template_name || 'checkout/order-receipt.php' === $template_name ) {

			$order_id = 0;
			if ( isset( $_GET['order'] ) ) {
				$order_id = intval( $_GET['order'] );
			} elseif ( isset( $wp->query_vars['order-pay'] ) ) {
				$order_id = intval( $wp->query_vars['order-pay'] );
			}
			$order = wc_get_order( $order_id );
			if ( ! empty( $order ) && 'payfast' === $order->get_payment_method() ) {
				return $template;
			}
			$template = WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_PATH . 'public/partials/form-pay.php';
		}
		return $template;
	}

	/**
	 * Redirect to the payment page after order is placed
	 *
	 * @param integer $order_id .
	 */
	public function wps_paypal_subscription_redirect_after_checkout( $order_id ) {
		$order = wc_get_order( $order_id );

		// Check if the order needs payment.
		if ( $order->needs_payment() && 'wps_paypal_subscription' === $order->get_payment_method() ) {
			// Get the URL of the payment page.
			$payment_url = $order->get_checkout_payment_url( true );
			wp_redirect( $payment_url );
			exit;
		}
	}

	/**
	 * Saved the subcribed data and redirect to the thank you page
	 */
	public function wps_paypal_subscribed_data_callback() {
		check_ajax_referer( 'wps_paypal_subscription_nonce', 'nonce' );

		$order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : 0;

		$subscription_data = isset( $_POST['subscribed_data'] ) ? array_map( 'sanitize_text_field', $_POST['subscribed_data'] ) : array();

		$get_subscription_id = wps_wsp_get_meta_data( $order_id, 'wps_subscription_id', true );

		wps_wsp_update_meta_data( $get_subscription_id, 'wps_created_paypal_subscription_data', $subscription_data );
		wps_wsp_update_meta_data( $get_subscription_id, 'wps_created_paypal_subscription_id', $subscription_data['subscriptionID'] );
		$order = wc_get_order( $order_id );
		$order->payment_complete( $subscription_data['orderID'] );
		/* translators: %s: subscription id */
		$order_notes = sprintf( esc_html__( 'A subscription %s has created on Paypal', 'woocommerce-subscriptions-pro' ), $subscription_data['subscriptionID'] );
		$order->add_order_note( $order_notes );
		/* translators: %s: order id */
		$order_notes = sprintf( esc_html__( 'Created Paypal Order ID : %s', 'woocommerce-subscriptions-pro' ), $subscription_data['orderID'] );
		$order->add_order_note( $order_notes );
		$get_session_data = WC()->session->get( 'wps_paypal_subscription_session_data' );
		wps_wsp_update_meta_data( $get_subscription_id, 'wps_paypal_subscription_session_data', $get_session_data );
		WC()->session->__unset( 'wps_paypal_subscription_session_data' );
		$url                               = $order->get_checkout_order_received_url();
		$subscription_data['redirect_url'] = $url;
		echo json_encode( $subscription_data );
		wp_die();
	}

	/**
	 * Not allow the recurring by the scheduler, if the subscription has purchased by the wps paypal subscription payment gateway.
	 *
	 * @param bool    $default .
	 * @param integer $subscription_id .
	 */
	public function wps_sfw_recurring_allow_on_scheduler_callback( $default, $subscription_id ) {
		$subscription_data = wc_get_order( $subscription_id );

		if ( OrderUtil::custom_orders_table_usage_is_enabled() && class_exists( 'WPS_Subscription' ) ) {
			$subscription       = new WPS_Subscription( $subscription_id );
			$wps_payment_method = $subscription->get_payment_method();
		} else {
			$wps_payment_method = get_post_meta( $subscription_id, '_payment_method', true );
		}
		if ( $subscription_data && 'wps_paypal_subscription' === $wps_payment_method ) {
			return true;
		}
		return $default;
	}

	/**
	 * Paypal Subscription Gateway support during wc checkout block
	 */
	public function wsp_psi_woocommerce_block_support() {
		if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) && class_exists( 'WPS_Paypal_Subscription_Integration_Gateway' ) ) {
			require_once WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_PATH . 'includes/wps-paypal-subscription/wc-blocks-compatibility/class-psi-block-support.php';
			add_action(
				'woocommerce_blocks_payment_method_type_registration',
				function ( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
					$payment_method_registry->register( new PSI_Block_Support() );
				}
			);
		}
	}

	/**
	 * Create a dynamic edit form for the subscription.
	 */
	public function wps_wsp_get_subscription_edit_form_callback() {
		check_ajax_referer( 'wps_wsp_common_nonce', 'nonce' );

		$subscription_id = isset( $_POST['subscription_id'] ) ? sanitize_text_field( wp_unslash( $_POST['subscription_id'] ) ) : 0;

		$subscription = new WPS_Subscription( $subscription_id );

		$popup_content = '<div class="wps-wsp-shadow"></div><form id="wps-wps-subscription-edit-form" method="post">
			<h2>' . esc_attr__( 'Edit Subscription Items', 'woocommerce-subscriptions-pro' ) . '</h2>
			<span class="wps-wsp-close-popup">&times;</span>
			<table class="shop_table order_edit_table">
				<thead>
					<tr>
						<th>' . esc_attr__( 'Product', 'woocommerce-subscriptions-pro' ) . '</th>
						<th>' . esc_attr__( 'Quantity', 'woocommerce-subscriptions-pro' ) . '</th>
					</tr>
				</thead>
				<tbody>';
					$subscription_interval_type = wps_wsp_get_meta_data( $subscription_id, 'wps_sfw_subscription_interval', true );

					// Loop through order items.
		foreach ( $subscription->get_items() as $item_id => $item ) {
			$product = $item->get_product();

			if ( $product->is_type( 'variation' ) ) {
				$product = wc_get_product( $item->get_variation_id() );
			}

			$args = array(
				'limit'        => -1,
				'status'       => 'publish',
				'meta_key'     => array( '_wps_sfw_product', 'wps_sfw_variable_product' ),
				'meta_value'   => 'yes',
				'return'       => 'ids',
				'type'         => array( 'simple', 'variation' ),
			);

			$get_subs_products = wc_get_products( $args );

			$popup_content .= '<tr>
							<td>';
			if ( empty( $get_subs_products ) || count( $get_subs_products ) == '1' ) {
						$popup_content .= '<input value="' . $item->get_name() . '" disabled>';
						$popup_content .= '<input type="hidden" name="' . $item_id . '[product_id]" value="' . $product->get_id() . '">';
			} else {
							$popup_content .= '<select name="' . $item_id . '[product_id]">';
				foreach ( $get_subs_products as $id ) {
					$interval_type = get_post_meta( $id, 'wps_sfw_subscription_interval', true );
					if ( $subscription_interval_type != $interval_type ) {
								continue;
					}
							$product_object = wc_get_product( $id );

							$popup_content .= '<option value="' . $id . '" ' . ( ( $product->get_id() == $id ) ? 'selected' : '' ) . '>' . $product_object->get_name() . '</option>';
				}
							$popup_content .= '</select>';
			}
									$popup_content .= '</td>
							<td>
								<input type="number" name="' . $item_id . '[qty]" value="' . $item->get_quantity() . '" min="1">
							</td>
						</tr>';
		}
				$popup_content .= '</tbody>
			</table><input type="hidden" name="subscription_id" value="' . $subscription_id . '"><input type="submit" class="woocommerce-button wp-element-button button" name="wps_wsp_update" value="Update" />
		</form>';

		echo $popup_content;
		wp_die();
	}

	/**
	 * Saved the updated edit form fields data.
	 */
	public function wps_wsp_update_subscription_callback() {
		check_ajax_referer( 'wps_wsp_common_nonce', 'nonce' );

		parse_str( $_POST['form_data'], $form_data );

		$subscription_id = sanitize_text_field( wp_unslash( $form_data['subscription_id'] ) );
		$subscription = new WPS_Subscription( $subscription_id );

		$is_update = false;

		foreach ( $subscription->get_items() as $item_id => $item ) {

			foreach ( $form_data as $form_item_id => $data ) {
				if ( $item_id == $form_item_id ) {
					$new_product_id = $form_data[ $item_id ]['product_id'];
					$new_product_qty = $form_data[ $item_id ]['qty'];
					$new_product = wc_get_product( $new_product_id );

					if ( $new_product_id && $new_product_qty && $new_product && $new_product_qty > 0 ) {

						$new_price = $new_product->get_price();

						$variation_id = 0;
						if ( $new_product->is_type( 'variation' ) ) {
							$variation_id = $new_product_id;
							$new_product_id = $new_product->get_parent_id();
						}

						$item->set_product_id( $new_product_id );

						$item->set_variation_id( $variation_id );

						$item->set_quantity( $new_product_qty );

						$item->set_name( $new_product->get_name() );

						$item->set_subtotal( $new_price * $new_product_qty );
						$item->set_total( $new_price * $new_product_qty );

						$item->update_meta_data( '_sku', $new_product->get_sku() );

						$item->save();

						$is_update = true;

						$subscription->update_meta_data( 'product_id', $new_product_id );
						$subscription->update_meta_data( 'product_qty', $new_product_qty );
						$subscription->update_meta_data( 'product_name', $new_product->get_name() );
						$subscription->update_meta_data( 'line_total', $new_price * $new_product_qty );
						$subscription->update_meta_data( 'line_subtotal', $new_price * $new_product_qty );

						$subscription->calculate_totals();
						$subscription->save();

						break;
					}
				}
			}
		}
		echo wp_json_encode( array( 'status' => $is_update ) );

		wp_die();
	}

	/**
	 * This function is used process manual renewal order.
	 *
	 * @name wps_wsp_other_payment_gateway_renewal_order
	 * @param object $wps_new_order wps_new_order.
	 * @param int    $susbcription_id susbcription_id.
	 * @param string $payment_method payment_method.
	 */
	public function wps_wsp_other_payment_gateway_renewal_order( $wps_new_order, $susbcription_id, $payment_method ) {
		if ( wps_wsp_enbale_accept_manual_payment() ) {
			$wps_manual_supported_gateway = wps_wsp_support_manual_payment();
			if ( in_array( $payment_method, $wps_manual_supported_gateway ) ) {
				$wps_new_order->update_status( 'pending' );
				wps_wsp_update_meta_data( $susbcription_id, 'wps_subscription_status', 'on-hold' );
				$order_id = $wps_new_order->get_id();
				wps_wsp_update_meta_data( $order_id, 'wps_wsp_manual_renewal_order', 'pending' );

				$mailer = WC()->mailer()->get_emails();
				// Send the "renewal invoive" notification.
				if ( isset( $mailer['wps_wsp_renewal_subscription_invoice'] ) ) {
					$mailer['wps_wsp_renewal_subscription_invoice']->trigger( $order_id );
				}
			}
		}

		// wallet custom.
		if ( 'wps_wcb_wallet_payment_gateway' == $payment_method ) {
			$order_id              = $wps_new_order->get_id();
			$wps_sfw_renewal_order = wps_sfw_get_meta_data( $order_id, 'wps_sfw_renewal_order', true );

			if ( 'yes' === $wps_sfw_renewal_order ) {
				$amount       = $wps_new_order->get_total();
				$user_id      = $wps_new_order->get_user_id();
				$wps_wallet_bal = get_user_meta( $user_id, 'wps_wallet', true );
				if ( (float) $wps_wallet_bal >= (float) $amount ) {

					$wps_wallet_bal = (float) $wps_wallet_bal - (float) $amount;
					update_user_meta( $user_id, 'wps_wallet', $wps_wallet_bal );

					$wallet_payment_gateway = new Wallet_System_For_Woocommerce();

					$transaction_type = __( 'Wallet Debited through Subscription Renewal', 'woocommerce-subscriptions-pro' ) . ' <a href="' . admin_url( 'post.php?post=' . $order_id . '&action=edit' ) . '" >#' . $order_id . '</a>';
					$transaction_data = array(
						'user_id'          => $user_id,
						'amount'           => $amount,
						'currency'         => $wps_new_order->get_currency(),
						'payment_method'   => 'Subscription Renewal Payment',
						'transaction_type' => htmlentities( $transaction_type ),
						'transaction_type_1' => 'debit',
						'order_id'         => $order_id,
						'note'             => '',
					);

					$wallet_payment_gateway->insert_transaction_data_in_table( $transaction_data );

					/* translators: %s: amount */
					$wps_new_order->update_status( 'processing', sprintf( __( 'Payment Recieve From Customer Wallet of Amount - : %s.', 'woocommerce-subscriptions-pro' ), $amount ) );
					$wps_new_order->save();

				} else {
					$wps_new_order->add_order_note( esc_html( ' order payment is not completed due to insufficient funds in customer wallet ', 'woocommerce-subscriptions-pro' ) );
					$wps_new_order->update_status( 'pending' );
					wps_wsp_update_meta_data( $susbcription_id, 'wps_subscription_status', 'on-hold' );
					wps_wsp_update_meta_data( $order_id, 'wps_wsp_manual_renewal_order', 'pending' );

					$mailer = WC()->mailer()->get_emails();
					// Send the "renewal invoive" notification.
					if ( isset( $mailer['wps_wsp_renewal_subscription_invoice'] ) ) {
							$mailer['wps_wsp_renewal_subscription_invoice']->trigger( $order_id );
					}
				}
			}
		}
		// wallet custom.
	}

	/**
	 * Function to Pro Plugin is active on org.
	 *
	 * @param bool $check as true or false.
	 * @return bool
	 */
	public function wsp_sfw_check_pro_plugin_callback( $check ) {
		return true;
	}

	/**
	 * Function to Pro Plugin is active on org.
	 *
	 * @param bool $check as true or false.
	 */
	public function wps_wsp_subscription_api_html_content() {
		?>
		<!-- subscriptino cancellation api html -->
		<h4><?php esc_html_e( 'Subscription Cancellation Api', 'woocommerce-subscriptions-pro' ); ?></h4>
			<div class="wps_sfw_rest_api_response">
				<strong>
				<?php
				esc_html_e( 'Base Url : ', 'woocommerce-subscriptions-pro' );
				echo esc_html( site_url() );
				esc_html_e( '/wp-json/wsp-route/v1/wsp-update-subscription/subscription_id', 'woocommerce-subscriptions-pro' );
				?>
				</strong>
				<p>
				<?php
				esc_html_e( 'Parameters Required : ', 'woocommerce-subscriptions-pro' );
				echo wp_kses_post( '<strong> {consumer_secret}</strong>' );
				echo wp_kses_post( '<strong> {action}</strong>' );
				?>
				</p>
				<p><strong><?php esc_html_e( 'Note - action required as cancel', 'woocommerce-subscriptions-pro' ); ?> </strong></p>
				<p><strong><?php esc_html_e( 'Method Required as PUT ', 'woocommerce-subscriptions-pro' ); ?> </strong></p>
				<p><?php esc_html_e( 'JSON response example:', 'woocommerce-subscriptions-pro' ); ?></p>
				<pre>
	{
		"code": "subscription_cancel",
		"message": "Subscription is now cancelled.",
		"data": {
			"status": 200
		}
	}
				</pre>
			</div>

			<!-- subscriptino pause api html -->
		<h4><?php esc_html_e( 'Subscription Pause Api', 'woocommerce-subscriptions-pro' ); ?></h4>
			<div class="wps_sfw_rest_api_response">
				<strong>
				<?php
				esc_html_e( 'Base Url : ', 'woocommerce-subscriptions-pro' );
				echo esc_html( site_url() );
				esc_html_e( '/wp-json/wsp-route/v1/wsp-update-subscription/subscription_id', 'woocommerce-subscriptions-pro' );
				?>
				</strong>
				<p>
				<?php
				esc_html_e( 'Parameters Required : ', 'woocommerce-subscriptions-pro' );
				echo wp_kses_post( '<strong> {consumer_secret}</strong>' );
				echo wp_kses_post( '<strong> {action}</strong>' );
				?>
				</p>
				<p><strong><?php esc_html_e( 'Note - action Required as pause ', 'woocommerce-subscriptions-pro' ); ?> </strong></p>
				<p><strong><?php esc_html_e( 'Method Required as PUT ', 'woocommerce-subscriptions-pro' ); ?> </strong></p>
				<p><?php esc_html_e( 'JSON response example:', 'woocommerce-subscriptions-pro' ); ?></p>
				<pre>
	{
		"code": "subscription_pause",
		"message": "Subscription is now paused.",
		"data": {
			"status": 200
		}
	}
				</pre>
			</div>
			<!-- subscriptino reactive api html -->
		<h4><?php esc_html_e( 'Subscription Reactivate Api', 'woocommerce-subscriptions-pro' ); ?></h4>
			<div class="wps_sfw_rest_api_response">
				<strong>
				<?php
				esc_html_e( 'Base Url : ', 'woocommerce-subscriptions-pro' );
				echo esc_html( site_url() );
				esc_html_e( '/wp-json/wsp-route/v1/wsp-update-subscription/subscription_id', 'woocommerce-subscriptions-pro' );
				?>
				</strong>
				<p>
				<?php
				esc_html_e( 'Parameters Required : ', 'woocommerce-subscriptions-pro' );
				echo wp_kses_post( '<strong> {consumer_secret}</strong>' );
				echo wp_kses_post( '<strong> {action}</strong>' );
				?>
				</p>
				<p><strong><?php esc_html_e( 'Note - action Required as reactivate ', 'woocommerce-subscriptions-pro' ); ?> </strong></p>
				<p><strong><?php esc_html_e( 'Method Required as PUT ', 'woocommerce-subscriptions-pro' ); ?> </strong></p>
				<p><?php esc_html_e( 'JSON response example:', 'woocommerce-subscriptions-pro' ); ?></p>
				<pre>
	{
		"code": "subscription_reactivate",
		"message": "Subscription is now reactivated.",
		"data": {
			"status": 200
		}
	}
				</pre>
			</div>
			<!-- particular subscription api html -->
		<h4><?php esc_html_e( 'Retrive Particular Subscription Reactivate', 'woocommerce-subscriptions-pro' ); ?></h4>
			<div class="wps_sfw_rest_api_response">
				<strong>
				<?php
				esc_html_e( 'Base Url : ', 'woocommerce-subscriptions-pro' );
				echo esc_html( site_url() );
				esc_html_e( '/wp-json/wsp-route/v1/view-subscription/subscription_id', 'woocommerce-subscriptions-pro' );
				?>
				</strong>
				<p>
				<?php
				esc_html_e( 'Parameters Required : ', 'woocommerce-subscriptions-pro' );
				echo wp_kses_post( '<strong> {consumer_secret}</strong>' );
				?>
				</p>
				<p><strong><?php esc_html_e( 'Method Required as GET ', 'woocommerce-subscriptions-pro' ); ?> </strong></p>
				<p><?php esc_html_e( 'JSON response example:', 'woocommerce-subscriptions-pro' ); ?></p>
				<pre>
	{
		"code": 200,
		"status": "success",
		"data": [
			{
				"subscription_id": "113",
				"parent_order_id": "112",
				"status": "active",
				"product_name": "Hoodie with Zipper",
				"recurring_amount": "45",
				"interval_number": "1",
				"interval_type": "day",
				"user_name": "admin",
				"next_payment_date": "May 20, 2025",
				"subscriptions_expiry_date": "---"
			}
		]
	}
				</pre>
			</div>
		<?php
	}

	/**
	 * Check if user has active subscription.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public function wps_wsp_check_if_user_has_active_subscription( $user_id ) {
		global $wpdb;

		$is_hpos = OrderUtil::custom_orders_table_usage_is_enabled();

		// Build the base query depending on whether HPOS is enabled or not.
		if ( $is_hpos ) {
			// HPOS is enabled, query the custom orders table.
			$query = "
				SELECT COUNT(*)
				FROM {$wpdb->prefix}wc_orders AS o
				INNER JOIN {$wpdb->prefix}wc_orders_meta AS om1 ON o.id = om1.order_id
				INNER JOIN {$wpdb->prefix}wc_orders_meta AS om2 ON o.id = om2.order_id
				WHERE o.type = 'wps_subscriptions'
				AND om1.meta_key = 'wps_subscription_status' AND om1.meta_value = 'active'
				AND om2.meta_key = 'wps_customer_id' AND om2.meta_value = '%d'
			";
		} else {
			// HPOS is not enabled, query the legacy tables.
			$query = "
				SELECT COUNT(*)
				FROM {$wpdb->prefix}posts AS p
				INNER JOIN {$wpdb->prefix}postmeta AS pm1 ON p.ID = pm1.post_id
				INNER JOIN {$wpdb->prefix}postmeta AS pm2 ON o.ID = pm2.post_id
				WHERE p.post_type = 'wps_subscriptions'
				AND pm1.meta_key = 'wps_subscription_status' AND pm1.meta_value = 'active'
				AND pm2.meta_key = 'wps_customer_id' AND pm2.meta_value = '%d'
			";
		}
		$active_subscriptions_count = $wpdb->get_var( $wpdb->prepare( $query, $user_id ) );

		if ( 0 == $active_subscriptions_count ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Assign inactive user role when subscription is expired.
	 *
	 * @param int $wps_subscription_id Subscription ID.
	 */
	public function wps_wsp_expire_subscription_scheduler_callback( $wps_subscription_id ) {
		$status = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_subscription_status', true );
		$assiging_inactive_role = get_option( 'wps_wsp_inactive_subscriber_role' );
		if ( 'expired' === $status && ! empty( $assiging_inactive_role ) ) {
			$user_id = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_customer_id', true );
			$user = get_user_by( 'ID', (int) $user_id );
			if ( $user && ! in_array( 'administrator', (array) $user->roles, true ) && ! $this->wps_wsp_check_if_user_has_active_subscription( $user_id ) ) {
				$user->set_role( $assiging_inactive_role );
			}
		}
	}

	/**
	 * Assign inactive user role when subscription is cancelled
	 *
	 * @param int    $wps_subscription_id Subscription ID.
	 * @param string $status Status.
	 */
	public function wps_wsp_cancel_assign_user_role( $wps_subscription_id, $status ) {
		$status = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_subscription_status', true );
		$assiging_inactive_role = get_option( 'wps_wsp_inactive_subscriber_role' );
		if ( 'cancelled' === $status && ! empty( $assiging_inactive_role ) ) {
			$user_id = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_customer_id', true );
			$user = get_user_by( 'ID', (int) $user_id );
			if ( $user && ! in_array( 'administrator', (array) $user->roles, true ) && ! $this->wps_wsp_check_if_user_has_active_subscription( $user_id ) ) {
				$user->set_role( $assiging_inactive_role );
			}
		}
	}
	/**
	 * Handle the manual subscription status incase of renewal
	 *
	 * @param object $wps_new_order Renewal Order.
	 * @param int    $subscription_id subscription Id.
	 * @param string $type Type.
	 */
	public function wps_sfw_handle_manual_renewal_status_callback( $wps_new_order, $subscription_id, $type ) {
		if ( 'manual' == $type && ! $wps_new_order->is_paid() ) {
			wps_wsp_update_meta_data( $subscription_id, 'wps_subscription_status', 'on-hold' );
		}
	}


	/**
	 * This function is used to set subscription start time.
	 *
	 * @param int $timestamp timestamp.
	 * @param int $wps_subscription_id wps_subscription_id.
	 * @return int
	 */
	public function wps_wsp_set_current_time_with_start_time( $timestamp, $wps_subscription_id ) {

		if ( wps_wsp_allow_start_date_subscription() ) {
			$wps_sfw_subscription_start_date = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_sfw_subscription_start_date', true );
			if ( $wps_sfw_subscription_start_date ) {
				if ( ( current_time( 'Y-m-d' ) ) >= $wps_sfw_subscription_start_date ) {
					$timestamp = current_time( 'timestamp' );
				} else {
					$timestamp = strtotime( $wps_sfw_subscription_start_date );
				}
			}
		}
		$status = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_subscription_status', true );
		$assiging_role = get_option( 'wps_wsp_subsciber_user_role' );

		// Assign user when subscription is activated.
		if ( 'active' === $status && ! empty( $assiging_role ) ) {
			$user_id = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_customer_id', true );
			$user = get_user_by( 'ID', (int) $user_id );
			if ( $user && ! in_array( 'administrator', (array) $user->roles, true ) ) {
				$user->set_role( $assiging_role );
			}
		}
		return $timestamp;
	}
}
