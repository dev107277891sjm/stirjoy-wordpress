<?php
/**
 * Exit if accessed directly
 *
 * @since      1.0.0
 * @package    Woocommerce_Subscriptions_Pro
 * @subpackage Woocommerce_Subscriptions_Pro/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Automattic\WooCommerce\Utilities\OrderUtil;

if ( ! function_exists( 'wps_sfw_get_free_trial_period_html_for_variable' ) ) {

	/**
	 * This function is used to show free trial period on subscription product page.
	 *
	 * @name wps_sfw_get_free_trial_period_html_for_variable
	 * @param int    $product_id Product ID.
	 * @param string $price Product Price.
	 * @since    1.0.0
	 */
	function wps_sfw_get_free_trial_period_html_for_variable( $product_id, $price ) {

		$wps_sfw_subscription_free_trial_number = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_free_trial_number', true );
		$wps_sfw_subscription_free_trial_interval = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_free_trial_interval', true );
		if ( isset( $wps_sfw_subscription_free_trial_number ) && ! empty( $wps_sfw_subscription_free_trial_number ) ) {
			$wps_price_html = wps_sfw_get_time_interval( $wps_sfw_subscription_free_trial_number, $wps_sfw_subscription_free_trial_interval );
			/* translators: %s: search term */
			$price .= '<span class="wps_sfw_free_trial">' . sprintf( esc_html__( ' and %s  free trial', 'woocommerce-subscriptions-pro' ), $wps_price_html ) . '</span>';

		}
		return $price;
	}
}

if ( ! function_exists( 'wps_sfw_get_initial_signup_fee_html_for_variable' ) ) {
	/**
	 * This function is used to show initial signup fee on subscription product page.
	 *
	 * @name wps_sfw_get_initial_signup_fee_html_for_variable
	 * @param int    $product_id Product ID.
	 * @param string $price Product Price.
	 * @since    1.0.0
	 */
	function wps_sfw_get_initial_signup_fee_html_for_variable( $product_id, $price ) {
		$wps_sfw_subscription_initial_signup_price = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_initial_signup_price', true );

		if ( isset( $wps_sfw_subscription_initial_signup_price ) && ! empty( $wps_sfw_subscription_initial_signup_price ) ) {
			/* translators: %s: search term */

			$price .= '<span class="wps_sfw_signup_fee">' . sprintf( esc_html__( ' and %s  Sign up fee', 'woocommerce-subscriptions-pro' ), wc_price( $wps_sfw_subscription_initial_signup_price ) ) . '</span>';
		}
		return $price;
	}
}
if ( ! function_exists( 'wps_sfw_check_variable_product_is_subscription' ) ) {
	/**
	 * This function is used to check susbcripton product.
	 *
	 * @name wps_sfw_check_variable_product_is_subscription
	 * @param object $product product.
	 * @since 1.0.0
	 */
	function wps_sfw_check_variable_product_is_subscription( $product ) {

		$wps_is_subscription = false;
		if ( is_object( $product ) ) {
			$product_id = $product->get_id();
			$wps_subscription_product = wps_wsp_get_meta_data( $product_id, 'wps_sfw_variable_product', true );
			if ( 'yes' === $wps_subscription_product ) {
				$wps_is_subscription = true;
			}
		}

		return $wps_is_subscription;
	}
}
if ( ! function_exists( 'wps_wsp_get_valid_subscription_expiry' ) ) {
	/**
	 * This function is used to check susbcripton product.
	 *
	 * @name wps_wsp_get_valid_subscription_expiry
	 * @param int    $wps_wsp_expiry_number wps_wsp_expiry_number.
	 * @param string $wps_wsp_expiry_interval wps_wsp_expiry_interval.
	 * @since 1.0.0
	 */
	function wps_wsp_get_valid_subscription_expiry( $wps_wsp_expiry_number, $wps_wsp_expiry_interval ) {

		if ( isset( $wps_wsp_expiry_number ) && ! empty( $wps_wsp_expiry_number ) ) {
			if ( 'day' == $wps_wsp_expiry_interval ) {
				if ( $wps_wsp_expiry_number > 90 ) {
					$wps_wsp_expiry_number = 90;
				}
			} elseif ( 'week' == $wps_wsp_expiry_interval ) {
				if ( $wps_wsp_expiry_number > 52 ) {
					 $wps_wsp_expiry_number = 52;
				}
			} elseif ( 'month' == $wps_wsp_expiry_interval ) {
				if ( $wps_wsp_expiry_number > 24 ) {
					$wps_wsp_expiry_number = 24;
				}
			} elseif ( 'year' == $wps_wsp_expiry_interval ) {
				if ( $wps_wsp_expiry_number > 5 ) {
					$wps_wsp_expiry_number = 5;
				}
			}
		}

		return $wps_wsp_expiry_number;
	}
}

if ( ! function_exists( 'wps_wsp_check_allow_expiry_by_customer' ) ) {
	/**
	 * This function is used to check allow subscription expiry enable.
	 *
	 * @name wps_wsp_check_allow_expiry_by_customer
	 * @since 1.0.0
	 */
	function wps_wsp_check_allow_expiry_by_customer() {
		$is_enable = false;
		$wps_wps_enable = get_option( 'wsp_allow_subscription_expiry_customer', '' );
		if ( 'on' == $wps_wps_enable ) {
			$is_enable = true;
		}
		return $is_enable;
	}
}
if ( ! function_exists( 'wps_wsp_allow_start_date_subscription' ) ) {
	/**
	 * This function is used to check allow subscription expiry enable.
	 *
	 * @name wps_wsp_allow_start_date_subscription
	 * @since 1.0.0
	 */
	function wps_wsp_allow_start_date_subscription() {
		$is_enable = false;
		$wps_wps_enable = get_option( 'wsp_allow_start_date_subscription', '' );
		if ( 'on' == $wps_wps_enable ) {
			$is_enable = true;
		}
		return $is_enable;
	}
}


if ( ! function_exists( 'wps_wsp_enable_automatic_retry_failed_attewsp' ) ) {
	/**
	 * This function is used to check allow subscription expiry enable.
	 *
	 * @name wps_wsp_enable_automatic_retry_failed_attewsp
	 * @since 1.0.0
	 */
	function wps_wsp_enable_automatic_retry_failed_attewsp() {
		$is_enable = false;
		$wps_wps_enable = get_option( 'wsp_enable_automatic_retry_failed_attempts', '' );
		if ( 'on' == $wps_wps_enable ) {
			$is_enable = true;
		}
		return $is_enable;
	}
}

if ( ! function_exists( 'wps_wsp_after_no_failed_attempt_cancel' ) ) {
	/**
	 * This function is used to get no of failed attempt cancel.
	 *
	 * @name wps_wsp_after_no_failed_attempt_cancel
	 * @since 1.0.0
	 */
	function wps_wsp_after_no_failed_attempt_cancel() {

		$wps_wps_enable = get_option( 'wsp_after_no_failed_attempt_cancel', '3' );

		return $wps_wps_enable;
	}
}

if ( ! function_exists( 'wps_wsp_enable_pause_susbcription_by_customer' ) ) {
	/**
	 * This function is used to check enable pause subcription.
	 *
	 * @name wps_wsp_enable_pause_susbcription_by_customer
	 * @since 1.0.0
	 */
	function wps_wsp_enable_pause_susbcription_by_customer() {
		$is_enable = false;
		$wps_wps_enable = get_option( 'wsp_enable_pause_susbcription_by_customer', '' );
		if ( 'on' == $wps_wps_enable ) {
			$is_enable = true;
		}
		return $is_enable;
	}
}

if ( ! function_exists( 'wps_wsp_start_pause_susbcription_by_customer' ) ) {
	/**
	 * This function is used to check enable start pause subcription.
	 *
	 * @name wps_wsp_start_pause_susbcription_by_customer
	 * @since 1.0.0
	 */
	function wps_wsp_start_pause_susbcription_by_customer() {
		$is_enable = false;
		$wps_wps_enable = get_option( 'wsp_start_pause_susbcription_by_customer', '' );
		if ( 'on' == $wps_wps_enable ) {
			$is_enable = true;
		}
		return $is_enable;
	}
}

if ( ! function_exists( 'wps_wsp_start_susbcription_from_certain_date_of_month' ) ) {
	/**
	 * This function is used to check enable start subcription from certain month.
	 *
	 * @name wps_wsp_start_susbcription_from_certain_date_of_month
	 * @since 1.0.0
	 */
	function wps_wsp_start_susbcription_from_certain_date_of_month() {
		$is_enable = false;
		$wps_wps_enable = get_option( 'wsp_start_susbcription_from_certain_date_of_month', '' );
		if ( 'on' == $wps_wps_enable ) {
			$is_enable = true;
		}
		return $is_enable;
	}
}

if ( ! function_exists( 'wps_wsp_enbale_accept_manual_payment' ) ) {
	/**
	 * This function is used to check enable accept manual payment.
	 *
	 * @name wps_wsp_enbale_accept_manual_payment
	 * @since 1.0.0
	 */
	function wps_wsp_enbale_accept_manual_payment() {
		$is_enable = false;
		$wps_wps_enable = get_option( 'wsp_enbale_accept_manual_payment', '' );
		if ( 'on' == $wps_wps_enable ) {
			$is_enable = true;
		}
		return $is_enable;
	}
}

if ( ! function_exists( 'wps_wsp_get_subscription_discount_type' ) ) {
	/**
	 * This function is used to check enable accept manual payment.
	 *
	 * @name wps_wsp_get_subscription_discount_type
	 * @since 1.0.0
	 */
	function wps_wsp_get_subscription_discount_type() {
		$coupon_type = array(
			'recurring_product_discount' => 1,
			'recurring_product_percent_discount' => 1,
		);
		return $coupon_type;
	}
}
if ( ! function_exists( 'wps_wsp_get_subscription_signup_discount_type' ) ) {
	/**
	 * This function is used to check enable accept manual payment.
	 *
	 * @name wps_wsp_get_subscription_signup_discount_type
	 * @since 1.0.0
	 */
	function wps_wsp_get_subscription_signup_discount_type() {
		$coupon_type = array(
			'initial_fee_discount' => 1,
			'initial_fee_percent_discount' => 1,
		);
		return $coupon_type;
	}
}

if ( ! function_exists( 'wps_wsp_get_signup_fee' ) ) {
	/**
	 * This function is used to check signup fee.
	 *
	 * @name wps_wsp_get_signup_fee
	 * @param object $product product.
	 * @since 1.0.0
	 */
	function wps_wsp_get_signup_fee( $product ) {
		$wps_signup_fee = 0;
		if ( is_object( $product ) ) {
			$product_id = $product->get_id();
			$wps_get_signup_fee = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_initial_signup_price', true );
			if ( isset( $wps_get_signup_fee ) && ! empty( $wps_get_signup_fee ) ) {
				$wps_signup_fee = $wps_get_signup_fee;
			}
		}
		return $wps_signup_fee;
	}
}
if ( ! function_exists( 'wps_wsp_get_recurring_total' ) ) {
	/**
	 * This function is used to get recurring total.
	 *
	 * @name wps_wsp_get_recurring_total
	 * @param object $product product.
	 * @since 1.0.0
	 */
	function wps_wsp_get_recurring_total( $product ) {

		$price = 0;
		if ( $product->is_on_sale() ) {
			$price = $product->get_sale_price();
		} else {
			$price = $product->get_regular_price();
		}

		return $price;
	}
}

if ( ! function_exists( 'wps_get_get_trial_period' ) ) {
	/**
	 * This function is used to get trial period.
	 *
	 * @name wps_get_get_trial_period
	 * @param object $product product.
	 * @since 1.0.0
	 */
	function wps_get_get_trial_period( $product ) {
		$trial_length = 0;

		$product_id = $product->get_id();
		$wps_sfw_free_trial_number = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_free_trial_number', true );
		if ( isset( $wps_sfw_free_trial_number ) && ! empty( $wps_sfw_free_trial_number ) ) {
			$trial_length = $wps_sfw_free_trial_number;
		}
		return $trial_length;
	}
}

if ( ! function_exists( 'wps_wsp_check_is_cart_subscription' ) ) {
	/**
	 * This function is used to check product subscription.
	 *
	 * @name wps_wsp_check_is_cart_subscription
	 * @since 1.0.0
	 */
	function wps_wsp_check_is_cart_subscription() {
		$wps_has_subscription = false;

		if ( ! empty( WC()->cart->cart_contents ) ) {
			foreach ( WC()->cart->cart_contents as $cart_item ) {
				if ( wps_sfw_check_product_is_subscription( $cart_item['data'] ) ) {
					$wps_has_subscription = true;
					break;
				}
			}
		}
		return $wps_has_subscription;
	}
}

if ( ! function_exists( 'wps_wsp_check_is_order_subscription' ) ) {
	/**
	 * This function is used to order has subscription.
	 *
	 * @name wps_wsp_check_is_order_subscription
	 * @param object $order order.
	 * @since 1.0.0
	 */
	function wps_wsp_check_is_order_subscription( $order ) {
		$wps_has_subscription = false;
		if ( isset( $order ) && ! empty( $order ) ) {
			foreach ( $order->get_items() as $key => $order_item ) {
				$product_id = ( $order_item['variation_id'] ) ? $order_item['variation_id'] : $order_item['product_id'];
				$product    = wc_get_product( $product_id );
				if ( wps_sfw_check_product_is_subscription( $product ) ) {
					$wps_has_subscription = true;
				}
			}
		}
		return $wps_has_subscription;
	}
}

if ( ! function_exists( 'wps_wsp_check_is_renewal_order' ) ) {
	/**
	 * This function is used to check renewal order.
	 *
	 * @name wps_wsp_check_is_renewal_order
	 * @param object $order order.
	 * @since 1.0.0
	 */
	function wps_wsp_check_is_renewal_order( $order ) {
		$wps_is_renewal_order = false;
		if ( isset( $order ) && ! empty( $order ) ) {
			$order_id = $order->get_id();
			$wps_is_renewal = wps_wsp_get_meta_data( $order_id, 'wps_sfw_renewal_order', true );
			if ( 'yes' == $wps_is_renewal ) {
				$wps_is_renewal_order = true;
			}
		}
		return $wps_is_renewal_order;
	}
}

if ( ! function_exists( 'wps_wsp_no_of_time_retry_failed_order' ) ) {
	/**
	 * This function is used to update retry attempt.
	 *
	 * @name wps_wsp_no_of_time_retry_failed_order
	 * @param int $order_id order_id.
	 * @since 1.0.0
	 */
	function wps_wsp_no_of_time_retry_failed_order( $order_id ) {
		$wps_retry_attempt = wps_wsp_get_meta_data( $order_id, 'wps_wsp_no_of_retry_attempt', true );
		if ( empty( $wps_retry_attempt ) ) {
			wps_wsp_update_meta_data( $order_id, 'wps_wsp_no_of_retry_attempt', 1 );
		} else {
			$wps_retry_attempt = ++$wps_retry_attempt;
			wps_wsp_update_meta_data( $order_id, 'wps_wsp_no_of_retry_attempt', $wps_retry_attempt );
		}
	}
}

if ( ! function_exists( 'wps_wsp_send_email_for_pause_susbcription' ) ) {
	/**
	 * This function is used to send pause email.
	 *
	 * @name wps_wsp_send_email_for_pause_susbcription
	 * @since 1.0.0
	 * @param int $wps_subscription_id wps_subscription_id.
	 */
	function wps_wsp_send_email_for_pause_susbcription( $wps_subscription_id ) {

		if ( isset( $wps_subscription_id ) && ! empty( $wps_subscription_id ) ) {
			$mailer = WC()->mailer()->get_emails();
			// Send the "pause" notification.
			if ( isset( $mailer['wps_wsp_pause_subscription'] ) ) {
				 $mailer['wps_wsp_pause_subscription']->trigger( $wps_subscription_id );
			}
		}
	}
}

if ( ! function_exists( 'wps_wsp_send_email_for_reactivate_susbcription' ) ) {
	/**
	 * This function is used to send reactivate email.
	 *
	 * @name wps_wsp_send_email_for_reactivate_susbcription
	 * @since 1.0.0
	 * @param int $wps_subscription_id wps_subscription_id.
	 */
	function wps_wsp_send_email_for_reactivate_susbcription( $wps_subscription_id ) {

		if ( isset( $wps_subscription_id ) && ! empty( $wps_subscription_id ) ) {
			$mailer = WC()->mailer()->get_emails();
			// Send the "reactivate" notification.
			if ( isset( $mailer['wps_wsp_reactivate_subscription'] ) ) {
				 $mailer['wps_wsp_reactivate_subscription']->trigger( $wps_subscription_id );
			}
		}
	}
}

if ( ! function_exists( 'wps_sfw_get_the_csv_date_format' ) ) {

	/**
	 * This function is used to get date format.
	 *
	 * @name wps_sfw_get_the_csv_date_format
	 * @since 1.0.0
	 * @param int $saved_date saved_date.
	 */
	function wps_sfw_get_the_csv_date_format( $saved_date ) {
		$return_date = '---';
		if ( isset( $saved_date ) && ! empty( $saved_date ) ) {
			$return_date = date_i18n( 'Y-m-d', $saved_date );
		}
		return $return_date;
	}
}

if ( ! function_exists( 'wps_wsp_support_manual_payment' ) ) {

	/**
	 * This function is used to get manual payment gateway.
	 *
	 * @name wps_wsp_support_manual_payment
	 * @since 1.0.0
	 */
	function wps_wsp_support_manual_payment() {
		$wps_manual_payment_gateway = array(
			'bacs',
			'cheque',
			'cod',
		);
		return $wps_manual_payment_gateway;
	}
}
if ( ! function_exists( 'wps_wsp_check_upgrade_downgrade' ) ) {
	/**
	 * This function is used to check upgrade/downgrade enbale.
	 *
	 * @name wps_wsp_check_upgrade_downgrade
	 * @since 1.0.0
	 */
	function wps_wsp_check_upgrade_downgrade() {
		$is_enable = false;
		$wps_wps_enable = get_option( 'wsp_enbale_downgrade_upgrade_subscription', '' );
		if ( 'on' == $wps_wps_enable ) {
			$is_enable = true;
		}
		return $is_enable;
	}
}

if ( ! function_exists( 'wps_wsp_get_upgrade_downgrade_text' ) ) {
	/**
	 * This function is used to get upgrade/downgrade button text.
	 *
	 * @name wps_wsp_get_upgrade_downgrade_text
	 * @since 1.0.0
	 */
	function wps_wsp_get_upgrade_downgrade_text() {

		$wps_wps_btn_text = get_option( 'wps_wsp_upgrade_downgrade_btn_text', 'Upgrade and Downgrade' );

		return $wps_wps_btn_text;
	}
}

if ( ! function_exists( 'wps_wsp_check_enable_singup_upgrade_downgrade' ) ) {
	/**
	 * This function is used to check upgrade/downgrade enbale.
	 *
	 * @name wps_wsp_check_enable_singup_upgrade_downgrade
	 * @since 1.0.0
	 */
	function wps_wsp_check_enable_singup_upgrade_downgrade() {
		$is_enable = false;
		$wps_wps_enable = get_option( 'wsp_enable_signup_fee_downgrade_upgrade_subscription', '' );
		if ( 'on' == $wps_wps_enable ) {
			$is_enable = true;
		}
		return $is_enable;
	}
}

if ( ! function_exists( 'wps_wsp_check_enable_prorate_price_upgrade_downgrade' ) ) {
	/**
	 * This function is used to check upgrade/downgrade enbale.
	 *
	 * @name wps_wsp_check_enable_prorate_price_upgrade_downgrade
	 * @since 1.0.0
	 */
	function wps_wsp_check_enable_prorate_price_upgrade_downgrade() {
		$is_enable = false;
		$wps_wps_enable = get_option( 'wsp_enable_prorate_on_price_downgrade_upgrade_subscription', '' );
		if ( 'on' == $wps_wps_enable ) {
			$is_enable = true;
		}
		return $is_enable;
	}
}


if ( ! function_exists( 'wps_wsp_check_enable_plan_going_expire_email_notification' ) ) {
	/**
	 * This function is used to check plan going expire email notification enbale.
	 *
	 * @name wps_wsp_check_enable_plan_going_expire_email_notification
	 * @since 1.0.0
	 */
	function wps_wsp_check_enable_plan_going_expire_email_notification() {
		$is_enable = false;
		$wps_wps_enable = get_option( 'wsp_enable_signup_fee_downgrade_upgrade_subscription', '' );
		if ( 'on' == $wps_wps_enable ) {
			$is_enable = true;
		}
		return $is_enable;
	}
}

if ( ! function_exists( 'wps_wsp_plan_going_to_expire_before_days' ) ) {
	/**
	 * This function is used to get upgrade/downgrade button text.
	 *
	 * @name wps_wsp_plan_going_to_expire_before_days
	 * @since 1.0.0
	 */
	function wps_wsp_plan_going_to_expire_before_days() {

		$wps_wps_no_of_days = get_option( 'wsp_plan_going_to_expire_before_days', '7' );

		return (int) $wps_wps_no_of_days;
	}
}

if ( ! function_exists( 'wps_wsp_send_plan_going_to_expire_email' ) ) {
	/**
	 * This function is used to send expire email.
	 *
	 * @name wps_wsp_send_plan_going_to_expire_email
	 * @since 1.0.0
	 * @param int $wps_subscription_id wps_subscription_id.
	 */
	function wps_wsp_send_plan_going_to_expire_email( $wps_subscription_id ) {

		if ( isset( $wps_subscription_id ) && ! empty( $wps_subscription_id ) ) {
			$mailer = WC()->mailer()->get_emails();
			// Send the "plan expire" notification.
			if ( isset( $mailer['wps_wsp_plan_going_expire'] ) ) {
				 $mailer['wps_wsp_plan_going_expire']->trigger( $wps_subscription_id );
			}
		}
	}
}

if ( ! function_exists( 'wps_wsp_proprate_price_calculate' ) ) {

	/**
	 * This function is used to calculate proprate price.
	 *
	 * @name wps_wsp_proprate_price_calculate
	 * @since 1.0.0
	 * @param int     $wps_subscription_id wps_subscription_id.
	 * @param int     $product_id product_id.
	 * @param int     $new_price new_price.
	 * @param mixed   $cart_data cart data.
	 * @param boolean $set set.
	 * @return $new_price
	 */
	function wps_wsp_proprate_price_calculate( $wps_subscription_id, $product_id, $new_price, $cart_data, $set ) {
		$last_order_date_time = wps_wsp_get_last_renewal_order_date( $wps_subscription_id );
		if ( empty( $last_order_date_time ) ) {
			return $new_price;
		}
		$current_time = current_time( 'timestamp' );

		$wps_no_of_days_paid = ceil( ( $current_time - $last_order_date_time ) / DAY_IN_SECONDS );

		$wps_wsp_old_number = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_sfw_subscription_number', true );
		$wps_wsp_old_interval = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_sfw_subscription_interval', true );
		$wps_old_recurring_total = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_recurring_total', true );

		$wps_new_interval = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_interval', true );

		$wps_new_subs_number = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_number', true );

		if ( 'day' == $wps_new_interval ) {
			$new_per_day_price = $new_price / $wps_new_subs_number;

		} elseif ( 'week' == $wps_new_interval ) {
			$wps_new_subs_number = $wps_new_subs_number * 7;
			$new_per_day_price = $new_price / $wps_new_subs_number;
		} elseif ( 'month' == $wps_new_interval ) {
			$wps_new_subs_number = $wps_new_subs_number * 30;
			$new_per_day_price = $new_price / $wps_new_subs_number;
		} elseif ( 'year' == $wps_new_interval ) {
			$wps_new_subs_number = $wps_new_subs_number * 365;
			$new_per_day_price = $new_price / $wps_new_subs_number;
		}
		if ( 'day' == $wps_wsp_old_interval ) {
			$per_day_price = ( $wps_old_recurring_total / $wps_wsp_old_number );

			$wps_wsp_interval_left = $wps_wsp_old_number - $wps_no_of_days_paid;

			$wps_price_paid = $per_day_price * $wps_wsp_interval_left;
			$new_price = $new_price - $wps_price_paid;

		} elseif ( 'week' == $wps_wsp_old_interval ) {
			$wps_old_sub_number = $wps_wsp_old_number * 7;
			$per_day_price = ( $wps_old_recurring_total / $wps_old_sub_number );
			$wps_wsp_interval_left = ( 7 * $wps_wsp_old_number ) - $wps_no_of_days_paid;

			$wps_price_paid = $per_day_price * $wps_wsp_interval_left;

			$new_price = $new_price - $wps_price_paid;

		} elseif ( 'month' == $wps_wsp_old_interval ) {

			$wps_old_sub_number = $wps_wsp_old_number * 30;
			$per_day_price = ( $wps_old_recurring_total / $wps_old_sub_number );
			$wps_wsp_interval_left = ( 30 * $wps_wsp_old_number ) - $wps_no_of_days_paid;
			$wps_price_paid = $per_day_price * $wps_wsp_interval_left;

			$new_price = $new_price - $wps_price_paid;

		} elseif ( 'year' == $wps_wsp_old_interval ) {
			$wps_old_sub_number = $wps_wsp_old_number * 365;
			$per_day_price = ( $wps_old_recurring_total / $wps_old_sub_number );
			$wps_wsp_interval_left = ( 365 * $wps_wsp_old_number ) - $wps_no_of_days_paid;

			$wps_price_paid = $per_day_price * $wps_wsp_interval_left;

			$new_price = $new_price - $wps_price_paid;
		}
		if ( get_option( 'wsp_enable_prorate_on_price_downgrade_upgrade_subscription', false ) ) {
			if ( 0 > $new_price ) {
				$set = true;
				$new_price = abs( $new_price );
				wc_clear_notices();
				$wps_wsf_manage_prorate_upgrade_downgrade = get_option( 'wps_wsp_manage_prorate_amount', false );
				if ( $wps_wsf_manage_prorate_upgrade_downgrade ) {

					if ( 'wps_manage_prorate_next_payment_date' === $wps_wsf_manage_prorate_upgrade_downgrade ) {
						$adjust_next_payment_date = ceil( $new_price / $new_per_day_price );

						$wps_final_updated_next_payment_data = $adjust_next_payment_date + $wps_new_subs_number;
						if ( 'day' === $wps_new_interval ) {
							$wps_final_show_msg_data = $wps_final_updated_next_payment_data;
						}
						if ( 'week' === $wps_new_interval ) {
							$wps_final_show_msg_data = $wps_final_updated_next_payment_data / 7;
						}
						if ( 'month' === $wps_new_interval ) {
							$wps_final_show_msg_data = $wps_final_updated_next_payment_data / 30;

						}
						if ( 'year' === $wps_new_interval ) {
							$wps_final_show_msg_data = $wps_final_updated_next_payment_data / 365;

						}
						wps_wsp_update_meta_data( $wps_subscription_id, 'wps_wsf_manage_prorate_negativ_amount_date', $wps_final_updated_next_payment_data );

						/* translators: Placeholder 1: Message, placeholder 2: interval, Placeholder 3: Message, placeholder 4: amount */

						$downgrade_upgrade_notice = sprintf( __( 'Your next recurring payment will be taken after %1$s %2$s because your %3$s %4$s amount has been left from previous product', 'woocommerce-subscriptions-pro' ), round( (float) $wps_final_show_msg_data, 1 ), $wps_new_interval, get_woocommerce_currency_symbol(), round( (float) $new_price, 1 ) );

						if ( $set ) {
							WC()->session->set( 'downgrade_upgrade_notice', $downgrade_upgrade_notice );
						}
					} elseif ( 'wps_manage_prorate_using_wallet' === $wps_wsf_manage_prorate_upgrade_downgrade ) {
						/* translators: Placeholder 1: currrency symbol, placeholder 2: amount */
						$downgrade_upgrade_notice = sprintf( __( 'Your left amount %1$s %2$s will be added to your wallet, You can check that from', 'woocommerce-subscriptions-pro' ), get_woocommerce_currency_symbol(), round( (float) $new_price, 1 ) ) . ' <a href="' . get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . '/wps-wallet/" target="_blank" class="button alt" >' . __( 'Wallet', 'woocommerce-subscriptions-pro' ) . '</a>';
						if ( $set ) {
							WC()->session->set( 'downgrade_upgrade_notice', $downgrade_upgrade_notice );
						}
						wps_wsp_update_meta_data( $wps_subscription_id, 'wps_wsf_manage_prorate_negativ_amount_wallet', $new_price );

					}
				}

				$new_price = 0;
			}
		}

		return $new_price;
	}
}

if ( ! function_exists( 'wps_wsp_email_subscriptions_details_recurring_reminder' ) ) {
	/**
	 * This function is used to create html for susbcription details.
	 *
	 * @name wps_sfw_email_subscriptions_details
	 *
	 * @since 1.0.0
	 * @param int $wps_subscription_id wps_subscription_id.
	 * @return void
	 */
	function wps_wsp_email_subscriptions_details_recurring_reminder( $wps_subscription_id ) {
		$wps_text_align = is_rtl() ? 'right' : 'left';
		?>
		<div style="margin-bottom: 40px;">
			<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
				<thead>
					<tr>
						<th class="td" scope="col" style="text-align:<?php echo esc_attr( $wps_text_align ); ?>;"><?php esc_html_e( 'Product', 'woocommerce-subscriptions-pro' ); ?></th>
						<th class="td" scope="col" style="text-align:<?php echo esc_attr( $wps_text_align ); ?>;"><?php esc_html_e( 'Quantity', 'woocommerce-subscriptions-pro' ); ?></th>
						<th class="td" scope="col" style="text-align:<?php echo esc_attr( $wps_text_align ); ?>;"><?php esc_html_e( 'Price', 'woocommerce-subscriptions-pro' ); ?></th>
						<th class="td" scope="col" style="text-align:<?php echo esc_attr( $wps_text_align ); ?>;"><?php esc_html_e( 'Recurring Payment Date', 'woocommerce-subscriptions-pro' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<?php
								$wps_product_name = wps_wsp_get_meta_data( $wps_subscription_id, 'product_name', true );
								echo esc_html( $wps_product_name );
							?>
						  </td>
						<td>
						<?php
							$product_qty = wps_wsp_get_meta_data( $wps_subscription_id, 'product_qty', true );
							echo esc_html( $product_qty );
						?>
						</td>
						<td>
						<?php
							do_action( 'wps_sfw_display_susbcription_recerring_total_account_page', $wps_subscription_id );
						?>
						</td>
						<td>
							<?php
							$wps_next_payment_date = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_next_payment_date', true );
							echo esc_html( gmdate( 'Y-m-d', $wps_next_payment_date ) );
							?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}
}

if ( ! function_exists( 'wps_wsp_get_last_renewal_order_date' ) ) {
	/**
	 * This function is used to last order date.
	 *
	 * @name wps_wsp_get_last_renewal_order_date
	 * @since 1.0.0
	 * @param int $wps_subscription_id wps_subscription_id.
	 */
	function wps_wsp_get_last_renewal_order_date( $wps_subscription_id ) {

		$wps_last_order_timestamp = 0;

		$wps_last_order_id = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_wsp_last_renewal_order_id', true );

		$wps_parent_order_id = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_parent_order', true );

		$wps_start_time = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_schedule_start', true );

		if ( $wps_last_order_id ) {
			$wps_last_order = wc_get_order( $wps_last_order_id );
			$wps_order_paid_date = $wps_last_order->get_date_paid();
			if ( $wps_order_paid_date ) {

				$wps_last_order_timestamp = $wps_order_paid_date->getTimestamp();
			}
		} elseif ( $wps_parent_order_id ) {
			$wps_parent_order = wc_get_order( $wps_parent_order_id );
			$wps_order_paid_date = $wps_parent_order->get_date_paid();
			if ( $wps_order_paid_date ) {
				$wps_last_order_timestamp = $wps_order_paid_date->getTimestamp();
			}
		} else {
			$wps_last_order_timestamp = $wps_start_time;
		}

		return $wps_last_order_timestamp;
	}
}

if ( ! function_exists( 'wps_wsp_subscription_week_period' ) ) {
	/**
	 * This function is used to get billing period.
	 *
	 * @name wps_wsp_subscription_week_period
	 * @since 1.0.0
	 */
	function wps_wsp_subscription_week_period() {
		$wps_billing_array = array();

		$wps_billing_array = array(
			'1' => __( 'Monday', 'woocommerce-subscriptions-pro' ),
			'2' => __( 'Tuesday', 'woocommerce-subscriptions-pro' ),
			'3' => __( 'Wednesday', 'woocommerce-subscriptions-pro' ),
			'4' => __( 'Thursday', 'woocommerce-subscriptions-pro' ),
			'5' => __( 'Friday', 'woocommerce-subscriptions-pro' ),
			'6' => __( 'Saturday', 'woocommerce-subscriptions-pro' ),
			'7' => __( 'Sunday', 'woocommerce-subscriptions-pro' ),
		);

		return $wps_billing_array;
	}
}
if ( ! function_exists( 'wps_wsp_subscription_month_period' ) ) {
	/**
	 * This function is used to get billing period.
	 *
	 * @name wps_wsp_subscription_month_period
	 * @since 1.0.0
	 */
	function wps_wsp_subscription_month_period() {
		$wps_billing_array = array();
		for ( $i = 1; $i <= 28; $i++ ) {
			/* translators: %s: search term */
			$wps_billing_array[ $i ] = sprintf( __( 'Day %s', 'woocommerce-subscriptions-pro' ), $i );
		}
			$wps_billing_array['end'] = __( 'Last day of month', 'woocommerce-subscriptions-pro' );
		return $wps_billing_array;
	}
}

if ( ! function_exists( 'wps_wsp_subscription_syn_year_period' ) ) {
	/**
	 * This function is used to get billing period.
	 *
	 * @name wps_wsp_subscription_syn_year_period
	 * @since 1.0.0
	 */
	function wps_wsp_subscription_syn_year_period() {
		$wps_billing_array = array();

			$wps_billing_array = array(
				'01' => __( 'January', 'woocommerce-subscriptions-pro' ),
				'02' => __( 'February', 'woocommerce-subscriptions-pro' ),
				'03' => __( 'March', 'woocommerce-subscriptions-pro' ),
				'04' => __( 'April', 'woocommerce-subscriptions-pro' ),
				'05' => __( 'May', 'woocommerce-subscriptions-pro' ),
				'06' => __( 'June', 'woocommerce-subscriptions-pro' ),
				'07' => __( 'July', 'woocommerce-subscriptions-pro' ),
				'08' => __( 'August', 'woocommerce-subscriptions-pro' ),
				'09' => __( 'September', 'woocommerce-subscriptions-pro' ),
				'10' => __( 'October', 'woocommerce-subscriptions-pro' ),
				'11' => __( 'November', 'woocommerce-subscriptions-pro' ),
				'12' => __( 'December', 'woocommerce-subscriptions-pro' ),
			);

			return $wps_billing_array;
	}
}

if ( ! function_exists( 'wps_wsp_subscription_syn_enable_per_product' ) ) {
	/**
	 * This function is used to get billing period.
	 *
	 * @name wps_wsp_subscription_syn_enable_per_product.
	 * @param int $product_id product_id.
	 * @since 1.0.0
	 */
	function wps_wsp_subscription_syn_enable_per_product( $product_id ) {
		$wps_is_enable = false;
		if ( isset( $product_id ) && ! empty( $product_id ) ) {
			$wps_check_enable = wps_wsp_get_meta_data( $product_id, 'wps_wsp_enbale_certain_month', true );
			$wps_wps_interval = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_interval', true );

			if ( 'yes' == $wps_check_enable && 'day' != $wps_wps_interval ) {
				$wps_is_enable = true;
			}
		}

		return $wps_is_enable;
	}
}

if ( ! function_exists( 'wps_wsp_get_sync_subscription_details' ) ) {
	/**
	 * This function is used to get billing period.
	 *
	 * @name wps_wsp_get_sync_subscription_details.
	 * @param int    $product_id product_id.
	 * @param string $wps_price_html wps_price_html.
	 * @since 1.0.0
	 */
	function wps_wsp_get_sync_subscription_details( $product_id, $wps_price_html ) {

		$wps_wsp_frequency = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_interval', true );
		$wps_wsp_number = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_number', true );
		if ( 'month' == $wps_wsp_frequency ) {
			$wps_wsp_month_sync_value = wps_wsp_get_meta_data( $product_id, 'wps_wsp_month_sync', true );
			$wps_wsp_month_sync_value = wps_wsp_get_sync_month_text( $wps_wsp_month_sync_value, $wps_wsp_number );
			/* translators: %s: search term */
			$wps_price_html = '<span class="wps_sfw_interval">' . sprintf( esc_html__( ' payment on  %s month', 'woocommerce-subscriptions-pro' ), $wps_wsp_month_sync_value ) . '</span>';
		} elseif ( 'week' == $wps_wsp_frequency ) {
			$wps_wsp_week_sync_value = wps_wsp_get_meta_data( $product_id, 'wps_wsp_week_sync', true );
			$wps_wsp_week_sync_value = wps_wsp_get_sync_text_week( $wps_wsp_week_sync_value, $wps_wsp_number );

			/* translators: %s: search term */
			$wps_price_html = '<span class="wps_sfw_interval">' . sprintf( esc_html__( ' payment on %s ', 'woocommerce-subscriptions-pro' ), $wps_wsp_week_sync_value ) . '</span>';
		} elseif ( 'year' == $wps_wsp_frequency ) {
			$wps_wsp_year_sync_value = wps_wsp_get_meta_data( $product_id, 'wps_wsp_year_sync', true );
			$wps_wsp_year_number = wps_wsp_get_meta_data( $product_id, 'wps_wsp_year_number', true );
			$wps_wsp_year_sync_value = wps_wsp_get_sync_year_text( $wps_wsp_year_sync_value, $wps_wsp_number, $wps_wsp_year_number );
			/* translators: %s: search term */
			$wps_price_html = '<span class="wps_sfw_interval">' . sprintf( esc_html__( ' payment on %s', 'woocommerce-subscriptions-pro' ), $wps_wsp_year_sync_value ) . '</span>';
		}

		return $wps_price_html;
	}
}

if ( ! function_exists( 'wps_wsp_get_sync_month_text' ) ) {
	/**
	 * This function is used to show month billing interval.
	 *
	 * @name wps_wsp_get_sync_month_text
	 * @param int $wps_wsp_sync_value wps_wsp_sync_value.
	 * @param int $wps_wsp_number wps_wsp_number.
	 * @since 1.0.0
	 */
	function wps_wsp_get_sync_month_text( $wps_wsp_sync_value, $wps_wsp_number ) {
		if ( 1 == $wps_wsp_number ) {
			$wps_wsp_number_text = __( 'every', 'woocommerce-subscriptions-pro' );
		} else {
			/* translators: %s: search term */
			$wps_wsp_number_text = sprintf( __( 'every %s', 'woocommerce-subscriptions-pro' ), $wps_wsp_number );
		}
		$wps_wsp_sync_string = '';
		switch ( $wps_wsp_sync_value ) {
			case 1:
				/* translators: %s: search term */
				$wps_wsp_sync_string = sprintf( __( '%1$1sst of %2$2s', 'woocommerce-subscriptions-pro' ), $wps_wsp_sync_value, $wps_wsp_number_text );
				break;
			case 2:
				/* translators: %s: search term */
				$wps_wsp_sync_string = sprintf( __( '%1$snd of %2$2s', 'woocommerce-subscriptions-pro' ), $wps_wsp_sync_value, $wps_wsp_number_text );
				break;
			case 3:
				/* translators: %s: search term */
				$wps_wsp_sync_string = sprintf( __( '%1$srd of %2$2s', 'woocommerce-subscriptions-pro' ), $wps_wsp_sync_value, $wps_wsp_number_text );
				break;
			case 'end':
				/* translators: %s: search term */
				$wps_wsp_sync_string = sprintf( __( 'last day of %s', 'woocommerce-subscriptions-pro' ), $wps_wsp_number_text );
				break;
			default:
				/* translators: %s: search term */
				$wps_wsp_sync_string = sprintf( __( '%1$sth of %2$2s', 'woocommerce-subscriptions-pro' ), $wps_wsp_sync_value, $wps_wsp_number_text );
				break;
		}

		return $wps_wsp_sync_string;
	}
}
if ( ! function_exists( 'wps_wsp_get_sync_text_week' ) ) {
	/**
	 * This function is used to show week billing interval.
	 *
	 * @name wps_wsp_get_sync_text_week
	 * @param int $wps_wsp_sync_value wps_wsp_sync_value.
	 * @param int $wps_wsp_number wps_wsp_number.
	 * @since 1.0.0
	 */
	function wps_wsp_get_sync_text_week( $wps_wsp_sync_value, $wps_wsp_number ) {

		if ( 1 == $wps_wsp_number ) {
			$wps_wsp_number_text = __( 'every', 'woocommerce-subscriptions-pro' );
		} else {
			/* translators: %s: number's of week */
			$wps_wsp_number_text = sprintf( __( 'every %s week on', 'woocommerce-subscriptions-pro' ), $wps_wsp_number );
		}

		$wps_wsp_sync_string = '';
		switch ( $wps_wsp_sync_value ) {
			case 1:
				/* translators: %s: number's of week */
				$wps_wsp_sync_string = sprintf( __( '%s Monday', 'woocommerce-subscriptions-pro' ), $wps_wsp_number_text );
				break;
			case 2:
				/* translators: %s: number's of week */
				$wps_wsp_sync_string = sprintf( __( '%s Tuesday', 'woocommerce-subscriptions-pro' ), $wps_wsp_number_text );
				break;
			case 3:
				/* translators: %s: number's of week */
				$wps_wsp_sync_string = sprintf( __( '%s Wednesday', 'woocommerce-subscriptions-pro' ), $wps_wsp_number_text );
				break;
			case 4:
				/* translators: %s: number's of week */
				$wps_wsp_sync_string = sprintf( __( '%s Thursday', 'woocommerce-subscriptions-pro' ), $wps_wsp_number_text );
				break;
			case 5:
				/* translators: %s: number's of week */
				$wps_wsp_sync_string = sprintf( __( '%s Friday', 'woocommerce-subscriptions-pro' ), $wps_wsp_number_text );
				break;
			case 6:
				/* translators: %s: number's of week */
				$wps_wsp_sync_string = sprintf( __( '%s Saturday', 'woocommerce-subscriptions-pro' ), $wps_wsp_number_text );
				break;
			case 7:
				/* translators: %s: number's of week */
				$wps_wsp_sync_string = sprintf( __( '%s Sunday', 'woocommerce-subscriptions-pro' ), $wps_wsp_number_text );
				break;
			default:
				break;
		}

		return $wps_wsp_sync_string;
	}
}

if ( ! function_exists( 'wps_wsp_get_sync_year_text' ) ) {
	/**
	 * This function is used to show year billing interval.
	 *
	 * @name wps_wsp_get_sync_year_text
	 * @param int $wps_wsp_sync_value wps_wsp_sync_value.
	 * @param int $wps_wsp_number wps_wsp_number.
	 * @param int $wps_wsp_year_number wps_wsp_year_number.
	 * @since 1.0.0
	 */
	function wps_wsp_get_sync_year_text( $wps_wsp_sync_value, $wps_wsp_number, $wps_wsp_year_number ) {
		$wps_wsp_selected_month = '';
		$wps_wsp_sync_string = '';
		$wps_wsp_years = wps_wsp_subscription_syn_year_period();

		if ( ! empty( $wps_wsp_years ) && is_array( $wps_wsp_years ) ) {
			if ( array_key_exists( $wps_wsp_sync_value, $wps_wsp_years ) ) {
				$wps_wsp_selected_month = $wps_wsp_years[ $wps_wsp_sync_value ];
			}
		}

		if ( 1 == $wps_wsp_number ) {
			$wps_wsp_number_text = __( 'every year', 'woocommerce-subscriptions-pro' );
		} else {
			/* translators: %s: search term */
			$wps_wsp_number_text = sprintf( __( 'every %s year', 'woocommerce-subscriptions-pro' ), $wps_wsp_number );
		}
		switch ( $wps_wsp_year_number ) {
			case 1:
				/* translators: %s: search term */
				$wps_wsp_sync_string = sprintf( __( '%1$1sst of %2$2s', 'woocommerce-subscriptions-pro' ), $wps_wsp_year_number, $wps_wsp_number_text );
				break;
			case 2:
				/* translators: %s: search term */
				$wps_wsp_sync_string = sprintf( __( '%1$snd of %2$2s', 'woocommerce-subscriptions-pro' ), $wps_wsp_year_number, $wps_wsp_number_text );
				break;
			case 3:
				/* translators: %s: search term */
				$wps_wsp_sync_string = sprintf( __( '%1$srd of %2$2s', 'woocommerce-subscriptions-pro' ), $wps_wsp_year_number, $wps_wsp_number_text );
				break;
			default:
				/* translators: %s: search term */
				$wps_wsp_sync_string = sprintf( __( '%1$sth of %2$2s', 'woocommerce-subscriptions-pro' ), $wps_wsp_year_number, $wps_wsp_number_text );
				break;
		}

		/* translators: %s: search term */
		$wps_wsp_sync_string = sprintf( __( '%1$1s %2$2s', 'woocommerce-subscriptions-pro' ), $wps_wsp_selected_month, $wps_wsp_sync_string );

		return $wps_wsp_sync_string;
	}
}
if ( ! function_exists( 'wps_wsp_get_sync_start_payment_date' ) ) {
	/**
	 * This function is used to sync the start date.
	 *
	 * @param int $product_id product_id.
	 * @return int
	 */
	function wps_wsp_get_sync_start_payment_date( $product_id ) {
		$wps_start_date = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_start_date', true );
		if ( $wps_start_date ) {
			$wps_start_date = '<div class="wps_wsp_start_date"><span>' . esc_html__( 'Your subscription will start on: ', 'woocommerce-subscriptions-pro' ) . '</span>' . wps_sfw_get_the_wordpress_date_format( strtotime( $wps_start_date ) ) . '</div>';
			return $wps_start_date;
		}
	}
}

if ( ! function_exists( 'wps_wsp_get_sync_first_payment_date' ) ) {
	/**
	 * This function is used to get first payment date.
	 *
	 * @name wps_wsp_get_sync_first_payment_date.
	 * @param int $product_id product_id.
	 * @since 1.0.0
	 */
	function wps_wsp_get_sync_first_payment_date( $product_id ) {
		$wps_first_payment_date = '';
		if ( ! wps_wsp_subscription_syn_enable_per_product( $product_id ) ) {
			return $wps_first_payment_date;
		}
		if ( isset( $product_id ) && ! empty( $product_id ) ) {
			$wps_current_time = current_time( 'timestamp' );

			$wps_wsp_frequency = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_interval', true );
			$wps_wsp_number = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_number', true );

			$wps_get_get_trial_period = wps_wsp_get_trial_sync_time( $product_id, $wps_current_time );
			if ( $wps_get_get_trial_period > 0 ) {
				$wps_current_time = $wps_get_get_trial_period;
			}
			if ( 'week' == $wps_wsp_frequency ) {
				$wps_wsp_week_sync_value = wps_wsp_get_meta_data( $product_id, 'wps_wsp_week_sync', true );

				$wps_wsp_week = wps_wsp_subscription_week_period();

				if ( ! empty( $wps_wsp_week ) && is_array( $wps_wsp_week ) ) {
					if ( array_key_exists( $wps_wsp_week_sync_value, $wps_wsp_week ) ) {
						$wps_wsp_selected_week = $wps_wsp_week[ $wps_wsp_week_sync_value ];
					}
				}

				$wps_no_day = gmdate( 'N', $wps_current_time );

				$wps_add_days = $wps_wsp_week_sync_value < $wps_no_day ? 0 : 7;
				$wps_no_day = $wps_no_day + $wps_add_days;

				$wps_first_payment_date = wps_wsp_get_the_next_year_sync_time( $wps_wsp_selected_week, $wps_current_time );

			} elseif ( 'month' == $wps_wsp_frequency ) {
				$wps_wsp_month_sync_value = wps_wsp_get_meta_data( $product_id, 'wps_wsp_month_sync', true );
				if ( 'end' == $wps_wsp_month_sync_value ) {
					$wps_wsp_month_sync_value = gmdate( 't', $wps_current_time );
				}

				$wps_no_of_days = gmdate( 't', $wps_current_time );
				$wps_no_of_cur_date = gmdate( 'j', $wps_current_time );

				if ( $wps_no_of_cur_date <= $wps_wsp_month_sync_value ) {
					$wps_day_diff = $wps_no_of_days - $wps_no_of_cur_date;
					$wps_day_diff = $wps_day_diff + $wps_wsp_month_sync_value;

				} else {
					$wps_day_diff = $wps_no_of_days + $wps_wsp_month_sync_value - $wps_no_of_cur_date;
				}

				$wps_first_payment_date = wps_sfw_get_timestamp( $wps_current_time, intval( $wps_day_diff ) );
			} elseif ( 'year' == $wps_wsp_frequency ) {

				$wps_wsp_year_sync_value = wps_wsp_get_meta_data( $product_id, 'wps_wsp_year_sync', true );
				$wps_wsp_year_number = wps_wsp_get_meta_data( $product_id, 'wps_wsp_year_number', true );

				$wps_curr_year = gmdate( 'Y', $wps_current_time );

				$wps_curr_month_day = gmdate( 'md', $wps_current_time );

				$wps_wsp_years = wps_wsp_subscription_syn_year_period();

				if ( ! empty( $wps_wsp_years ) && is_array( $wps_wsp_years ) ) {
					if ( array_key_exists( $wps_wsp_year_sync_value, $wps_wsp_years ) ) {
						$wps_wsp_selected_month = $wps_wsp_years[ $wps_wsp_year_sync_value ];
					}
				}

				$wps_selected_month_day = sprintf( '%02d%02d', $wps_wsp_year_sync_value, $wps_wsp_year_number );
				if ( $wps_curr_month_day > $wps_selected_month_day ) {
					$wps_curr_year++;
				}

				$wps_first_payment_date = wps_wsp_get_the_next_year_sync_time( "{$wps_wsp_year_number} {$wps_wsp_selected_month} {$wps_curr_year}" );
			}
		}
		if ( ! empty( $wps_first_payment_date ) ) {
			/* translators: %s: search term */
			$wps_first_payment_date = '<span class="wps_wsp_fist_payment_date">' . sprintf( __( 'You have to pay first recurring payment on: %s ', 'woocommerce-subscriptions-pro' ), wps_wsp_get_the_date_format( $wps_first_payment_date ) ) . '</span>';
		}
		return $wps_first_payment_date;
	}
}
if ( ! function_exists( 'wps_wsp_get_sync_first_payment_date_for_price' ) ) {
	/**
	 * This function is used to get first payment date.
	 *
	 * @name wps_wsp_get_sync_first_payment_date_for_price.
	 * @param int $product_id product_id.
	 * @since 1.0.0
	 */
	function wps_wsp_get_sync_first_payment_date_for_price( $product_id ) {
		$wps_first_payment_date = '';
		if ( ! wps_wsp_subscription_syn_enable_per_product( $product_id ) ) {
			return $wps_first_payment_date;
		}
		if ( isset( $product_id ) && ! empty( $product_id ) ) {
			$wps_current_time = current_time( 'timestamp' );

			$wps_wsp_frequency = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_interval', true );
			$wps_wsp_number = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_number', true );

			$wps_get_get_trial_period = wps_wsp_get_trial_sync_time( $product_id, $wps_current_time );
			if ( $wps_get_get_trial_period > 0 ) {
				$wps_current_time = $wps_get_get_trial_period;
			}
			if ( 'week' == $wps_wsp_frequency ) {
				$wps_wsp_week_sync_value = wps_wsp_get_meta_data( $product_id, 'wps_wsp_week_sync', true );

				$wps_wsp_week = wps_wsp_subscription_week_period();

				if ( ! empty( $wps_wsp_week ) && is_array( $wps_wsp_week ) ) {
					if ( array_key_exists( $wps_wsp_week_sync_value, $wps_wsp_week ) ) {
						$wps_wsp_selected_week = $wps_wsp_week[ $wps_wsp_week_sync_value ];
					}
				}

				$wps_no_day = gmdate( 'N', $wps_current_time );

				$wps_add_days = $wps_wsp_week_sync_value < $wps_no_day ? 0 : 7;
				$wps_no_day = $wps_no_day + $wps_add_days;

				$wps_first_payment_date = wps_wsp_get_the_next_year_sync_time( $wps_wsp_selected_week, $wps_current_time );

			} elseif ( 'month' == $wps_wsp_frequency ) {
				$wps_wsp_month_sync_value = wps_wsp_get_meta_data( $product_id, 'wps_wsp_month_sync', true );
				if ( 'end' == $wps_wsp_month_sync_value ) {
					$wps_wsp_month_sync_value = gmdate( 't', $wps_current_time );
				}

				$wps_no_of_days = gmdate( 't', $wps_current_time );
				$wps_no_of_cur_date = gmdate( 'j', $wps_current_time );

				if ( $wps_no_of_cur_date <= $wps_wsp_month_sync_value ) {

					$wps_day_diff = $wps_no_of_days - $wps_no_of_cur_date;
					$wps_day_diff = $wps_day_diff + $wps_wsp_month_sync_value;

				} else {
					$wps_day_diff = $wps_no_of_days + $wps_wsp_month_sync_value - $wps_no_of_cur_date;
				}
				$wps_first_payment_date = wps_sfw_get_timestamp( $wps_current_time, intval( $wps_day_diff ) );
			} elseif ( 'year' == $wps_wsp_frequency ) {

				$wps_wsp_year_sync_value = wps_wsp_get_meta_data( $product_id, 'wps_wsp_year_sync', true );
				$wps_wsp_year_number = wps_wsp_get_meta_data( $product_id, 'wps_wsp_year_number', true );

				$wps_curr_year = gmdate( 'Y', $wps_current_time );

				$wps_curr_month_day = gmdate( 'md', $wps_current_time );

				$wps_wsp_years = wps_wsp_subscription_syn_year_period();

				if ( ! empty( $wps_wsp_years ) && is_array( $wps_wsp_years ) ) {
					if ( array_key_exists( $wps_wsp_year_sync_value, $wps_wsp_years ) ) {
						$wps_wsp_selected_month = $wps_wsp_years[ $wps_wsp_year_sync_value ];
					}
				}

				$wps_selected_month_day = sprintf( '%02d%02d', $wps_wsp_year_sync_value, $wps_wsp_year_number );
				if ( $wps_curr_month_day > $wps_selected_month_day ) {
					$wps_curr_year++;
				}

				$wps_first_payment_date = wps_wsp_get_the_next_year_sync_time( "{$wps_wsp_year_number} {$wps_wsp_selected_month} {$wps_curr_year}" );
			}
		}
		return $wps_first_payment_date;
	}
}

if ( ! function_exists( 'wps_wsp_get_the_date_format' ) ) {

	/**
	 * This function is used to get date format.
	 *
	 * @name wps_wsp_get_the_date_format
	 * @since 1.0.0
	 * @param int $saved_date saved_date.
	 */
	function wps_wsp_get_the_date_format( $saved_date ) {
		$return_date = '---';
		if ( isset( $saved_date ) && ! empty( $saved_date ) ) {

			$date_format = get_option( 'date_format', 'Y-m-d' );
			$return_date = date_i18n( $date_format, $saved_date );
		}

		return $return_date;
	}
}

if ( ! function_exists( 'wps_wsp_get_the_next_year_sync_time' ) ) {

	/**
	 * This function is used to get date format.
	 *
	 * @name wps_wsp_get_the_next_year_sync_time.
	 * @since 1.0.0
	 * @param string $wps_date_string wps_date_string.
	 * @param int    $wps_current_time wps_current_time.
	 */
	function wps_wsp_get_the_next_year_sync_time( $wps_date_string, $wps_current_time = '' ) {
		$wps_next_time_stamp = '';

		if ( empty( $wps_current_time ) ) {
			$wps_next_time_stamp = strtotime( $wps_date_string );
		} else {
			$wps_next_time_stamp = strtotime( $wps_date_string, $wps_current_time );
		}
		return $wps_next_time_stamp;
	}
}

if ( ! function_exists( 'wps_wsp_get_trial_sync_time' ) ) {

	/**
	 * This function is used to get date format.
	 *
	 * @name wps_wsp_get_the_next_year_sync_time
	 * @since 1.0.0
	 * @param int $product_id product_id.
	 * @param int $current_time current_time.
	 */
	function wps_wsp_get_trial_sync_time( $product_id, $current_time ) {
		$wps_sfw_trial_date = 0;
		$trial_number = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_free_trial_number', true );
		$trial_interval = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_free_trial_interval', true );

		if ( isset( $trial_number ) && ! empty( $trial_number ) ) {
			$wps_sfw_trial_date = wps_sfw_susbcription_calculate_time( $current_time, $trial_number, $trial_interval );

		}

		return $wps_sfw_trial_date;
	}
}

if ( ! function_exists( 'wsp_get_prorate_price_on_sync_enable' ) ) {

	/**
	 * This function is used to get prorate price.
	 *
	 * @name wsp_get_prorate_price_on_sync_enable
	 * @since 1.0.0
	 */
	function wsp_get_prorate_price_on_sync_enable() {

		$wsp_prorate_price_on_sync = get_option( 'wsp_prorate_price_on_sync', 'wps_wsp_prorate_no' );

		return $wsp_prorate_price_on_sync;
	}
}
if ( ! function_exists( 'wps_wsp_check_is_today_date' ) ) {

	/**
	 * This function is used to check today date.
	 *
	 * @param int $wps_timestamp wps_timestamp.
	 * @name wps_wsp_check_is_today_date
	 * @since 1.0.0
	 */
	function wps_wsp_check_is_today_date( $wps_timestamp ) {
		$wps_is_today = false;
		$wps_timestamp = (int) ( $wps_timestamp ) +  (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );

		$wps_today_date = gmdate( 'Y-m-d', current_time( 'timestamp' ) );
		if ( gmdate( 'Y-m-d', $wps_timestamp ) == $wps_today_date ) {
			$wps_is_today = true;
		}

		return $wps_is_today;
	}
}

if ( ! function_exists( 'wps_wsp_prorate_price_for_sync' ) ) {

	/**
	 * This function is used to check prorate price.
	 *
	 * @param int   $price price.
	 * @param int   $product_id product_id.
	 * @param array $cart_data cart_data.
	 * @name wps_wsp_prorate_price_for_sync
	 * @since 1.0.0
	 */
	function wps_wsp_prorate_price_for_sync( $price, $product_id, $cart_data ) {

		$wps_wsp_first_payment_date = wps_wsp_get_sync_first_payment_date_for_price( $product_id );

		if ( wps_wsp_check_is_today_date( $wps_wsp_first_payment_date ) ) {
			return $price;
		}
		$wps_wsp_frequency = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_interval', true );
		$wps_wsp_number = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_number', true );
		$wps_no_of_days = wps_wsp_get_no_of_days( $wps_wsp_frequency, $wps_wsp_number );

		$manage_time = current_time( 'timestamp' );
		if ( wps_wsp_allow_start_date_subscription() ) {
			$wps_sfw_subscription_start_date = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_start_date', true );
			if ( $wps_sfw_subscription_start_date ) {
				$manage_time = strtotime( $wps_sfw_subscription_start_date );
			}
		}
		$wps_next_payment = ceil( ( $wps_wsp_first_payment_date - $manage_time ) / ( DAY_IN_SECONDS ) );
		$wps_wsp_prorate_type = wsp_get_prorate_price_on_sync_enable();
		if ( wps_wsp_check_is_trial( $product_id ) && 'wps_wsp_prorate_if_free_trial' == $wps_wsp_prorate_type ) {
			$product_price = $cart_data['data']->get_price();
			$wps_wsp_free_trial_number = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_free_trial_number', true );
			$wps_wsp_free_trial_frequency = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_free_trial_interval', true );
			$wps_no_of_free_days = wps_wsp_get_no_of_days( $wps_wsp_free_trial_frequency, $wps_wsp_free_trial_number );
			;
			$wps_no_of_days = $wps_no_of_days - $wps_no_of_free_days;
			if ( 0 == $wps_no_of_days ) {
				$wps_no_of_days = $wps_next_payment - $wps_no_of_free_days;
				$product_price = ( $product_price / $wps_no_of_free_days ) * $wps_no_of_days;
				$price = $product_price;
			} else {
				$product_price = $wps_next_payment * ( $product_price / $wps_no_of_days );
				$price = $price + $product_price;
			}
		} elseif ( wps_wsp_check_is_trial( $product_id ) && 'wps_wsp_prorate_if_free_trial' != $wps_wsp_prorate_type ) {
			return $price;
		} else {
			$price = $wps_next_payment * ( $price / $wps_no_of_days );
		}
		$price = round( $price, wc_get_price_decimals() );
		if ( $price < 0 ) {
			$price = 0;
		}
		return $price;
	}
}
if ( ! function_exists( 'wps_wsp_get_no_of_days' ) ) {

	/**
	 * This function is used to get no days.
	 *
	 * @param string $wps_wsp_frequency wps_wsp_frequency.
	 * @param int    $wps_wsp_number wps_wsp_number.
	 * @name wps_wsp_get_no_of_days
	 * @since 1.0.0
	 */
	function wps_wsp_get_no_of_days( $wps_wsp_frequency, $wps_wsp_number ) {

		switch ( $wps_wsp_frequency ) {
			case 'week':
				$wps_wsp_number = 7 * $wps_wsp_number;
				break;
			case 'month':
				$wps_wsp_number = gmdate( 't' ) * $wps_wsp_number;
				break;
			case 'year':
				$wps_wsp_number = ( 365 + gmdate( 'L' ) ) * $wps_wsp_number;
				break;
		}
		return $wps_wsp_number;
	}
}

if ( ! function_exists( 'wps_wsp_check_is_trial' ) ) {

	/**
	 * This function is used to check is trial.
	 *
	 * @param int $product_id product_id.
	 * @name wps_wsp_check_is_trial
	 * @since 1.0.0
	 */
	function wps_wsp_check_is_trial( $product_id ) {
		$wps_is_trial = false;
		$wps_wsp_free_trial_number = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_free_trial_number', true );
		if ( isset( $wps_wsp_free_trial_number ) && ! empty( $wps_wsp_free_trial_number ) ) {
			$wps_is_trial = true;
		}
		return $wps_is_trial;
	}
}

if ( ! function_exists( 'wps_wsp_check_enable_add_multiple_subscription_cart' ) ) {
	/**
	 * This function is used to check enable to add multiple product in cart.
	 *
	 * @name wps_wsp_check_enable_add_multiple_subscription_cart
	 * @since 1.0.0
	 */
	function wps_wsp_check_enable_add_multiple_subscription_cart() {
		$is_enable = false;
		$wps_wps_enable = get_option( 'wsp_allow_to_add_multiple_subscription_cart', '' );
		if ( 'on' == $wps_wps_enable ) {
			$is_enable = true;
		}
		return $is_enable;
	}
}

if ( ! function_exists( 'wps_wsp_reactivate_time_calculation' ) ) {
	/**
	 * This function is used to calculate time for reactivate subscription.
	 *
	 * @name wps_wsp_reactivate_time_calculation
	 * @param int $wps_subscription_id wps_subscription_id.
	 * @since 1.0.0
	 */
	function wps_wsp_reactivate_time_calculation( $wps_subscription_id ) {
		if ( wps_sfw_check_valid_subscription( $wps_subscription_id ) ) {
			$wps_reactivate_time = current_time( 'timestamp' );
			wps_wsp_update_meta_data( $wps_subscription_id, 'wps_subscription_reactive_time', $wps_reactivate_time );
			$wps_pause_time = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_subscription_pause_time', true );

			if ( wps_wsp_check_is_today_date( $wps_pause_time ) ) {
				return;
			}

			$wps_next_payment_date = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_next_payment_date', true );
			$wps_susbcription_end = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_susbcription_end', true );

			if ( ! empty( $wps_reactivate_time ) && ! empty( $wps_pause_time ) ) {

				$wps_no_of_days = round( ( $wps_reactivate_time - $wps_pause_time ) / ( DAY_IN_SECONDS ) );

				if ( ! empty( $wps_no_of_days ) && $wps_no_of_days >= 1 ) {
					if ( ! empty( $wps_next_payment_date ) ) {
						$wps_next_payment_date = wps_sfw_get_timestamp( $wps_next_payment_date, intval( $wps_no_of_days ) );
						wps_wsp_update_meta_data( $wps_subscription_id, 'wps_next_payment_date', $wps_next_payment_date );
					}
					if ( ! empty( $wps_susbcription_end ) ) {
						$wps_susbcription_end = wps_sfw_get_timestamp( $wps_susbcription_end, intval( $wps_no_of_days ) );
						wps_wsp_update_meta_data( $wps_subscription_id, 'wps_susbcription_end', $wps_susbcription_end );
					}
				}
			}
		}
	}
}

if ( ! function_exists( 'wps_wsp_enable_shipping_on_subscription' ) ) {
	/**
	 * This function is used to enable shipping on subscription.
	 *
	 * @name wps_wsp_enable_shipping_on_subscription
	 * @since 1.0.0
	 */
	function wps_wsp_enable_shipping_on_subscription() {
		$is_enable = false;
		$wps_wps_enable = get_option( 'wsp_allow_shipping_subscription', 'on' );
		if ( 'on' == $wps_wps_enable ) {
			$is_enable = true;
		}
		return $is_enable;
	}
}

if ( ! function_exists( 'wps_wsp_no_of_susbcription_in_cart' ) ) {
	/**
	 * This function is used to enable shipping on subscription.
	 *
	 * @name wps_wsp_no_of_susbcription_in_cart
	 * @since 1.0.0
	 */
	function wps_wsp_no_of_susbcription_in_cart() {
		$count = 0;
		if ( ! empty( WC()->cart->cart_contents ) ) {

			foreach ( WC()->cart->cart_contents as $cart_item ) {
				if ( wps_sfw_check_product_is_subscription( $cart_item['data'] ) ) {
					$wps_has_subscription = true;
					$count++;
				}
			}
		}
		return $count;
	}
}

if ( ! function_exists( 'wps_wsp_set_pause_subscription_timestamp' ) ) {
	/**
	 * This function is used to set subscription pause timestamp.
	 *
	 * @name wps_wsp_set_pause_subscription_timestamp
	 * @param int $wps_subscription_id subscription id.
	 * @since 1.0.0
	 */
	function wps_wsp_set_pause_subscription_timestamp( $wps_subscription_id ) {
		$wps_pause_time = current_time( 'timestamp' );
		wps_wsp_update_meta_data( $wps_subscription_id, 'wps_subscription_pause_time', $wps_pause_time );
	}
}

if ( ! function_exists( 'wps_wsp_get_subscription_coupon_enable_for_gc' ) ) {
	/**
	 * This function is used to get coupon type for giftcard.
	 *
	 * @name wps_wsp_get_subscription_coupon_enable_for_gc
	 * @since 1.1.0
	 */
	function wps_wsp_get_subscription_coupon_enable_for_gc() {
		$wps_is_enable = false;
		$general_settings = get_option( 'wps_wgm_other_settings', array() );
		if ( class_exists( 'Woocommerce_Gift_Cards_Common_Function' ) ) {
			$wps_public_obj = new Woocommerce_Gift_Cards_Common_Function();
			$wps_gc_coupon_enable = $wps_public_obj->wps_wgm_get_template_data( $general_settings, 'wps_wgm_addition_subscription_coupon_option_enable' );

			if ( isset( $wps_gc_coupon_enable ) && 'on' == $wps_gc_coupon_enable ) {
				$wps_is_enable = true;
			}
		}

		return $wps_is_enable;
	}
}

if ( ! function_exists( 'wps_wsp_get_subscription_coupon_type_for_gc' ) ) {
	/**
	 * This function is used to get coupon type for giftcard.
	 *
	 * @name wps_wsp_get_subscription_coupon_type_for_gc
	 * @since 1.1.0
	 */
	function wps_wsp_get_subscription_coupon_type_for_gc() {
		$wps_gc_coupon_type = '';

		$general_settings = get_option( 'wps_wgm_other_settings', array() );
		if ( class_exists( 'Woocommerce_Gift_Cards_Common_Function' ) ) {
			$wps_public_obj = new Woocommerce_Gift_Cards_Common_Function();
			$wps_gc_coupon_type = $wps_public_obj->wps_wgm_get_template_data( $general_settings, 'wps_wgm_addition_subscription_coupon_type' );
		}
		if ( empty( $wps_gc_coupon_type ) ) {
			$wps_gc_coupon_type = 'fixed_cart';
		}

		return $wps_gc_coupon_type;
	}
}

if ( ! function_exists( 'wps_wsp_enable_multiple_quantity_field' ) ) {
	/**
	 * This function is used to enable multiple quantity on subscription.
	 *
	 * @name wps_wsp_enable_multiple_quantity_field
	 * @since 1.1.0
	 */
	function wps_wsp_enable_multiple_quantity_field() {
		$is_enable = false;
		$wps_wps_enable = get_option( 'wsp_allow_multiple_quantity_subscription', '' );
		if ( 'on' == $wps_wps_enable ) {
			$is_enable = true;
		}
		return $is_enable;
	}
}
if ( ! function_exists( 'wps_wsp_set_shipping_fee' ) ) {
	/**
	 * Function for wps_wsp_set_shipping_fee.
	 *
	 * @param [type] $get_fee_order_id for get_shipping_fee_order_id.
	 * @param [type] $get_fee_order_id for set_shipping_fee_order_id.
	 * @return bool
	 */
	function wps_wsp_set_shipping_fee( $get_fee_order_id, $set_fee_order_id ) {

		$flag = false;
		$get_order = wc_get_order( $get_fee_order_id );

		$order_shipping = $get_order->get_items( 'shipping' );
		$set_order = wc_get_order( $set_fee_order_id );

		foreach ( $order_shipping as $item_id => $item ) {
			$item_data = $item->get_data();

			$name      = $item_data['name'];
			$method_id = $item_data['method_id'];
			$total     = $item_data['total'];

			if ( $name && $total ) {
				$shipping_fee = new WC_Order_Item_Shipping();
				$shipping_fee->set_method_title( $name );
				$shipping_fee->set_method_id( $method_id );
				$shipping_fee->set_total( wc_format_decimal( $total ) );
				$set_order->add_item( $shipping_fee );
				$set_order->calculate_totals();
				$set_order->update_taxes();
				$set_order->save();
				$flag = true;
			}
		}
		return $flag;
	}
}
if ( ! function_exists( 'wps_sfw_check_plugin_enable' ) ) {
	/**
	 * This function is used to check plugin is enable.
	 *
	 * @name wps_sfw_check_plugin_enable
	 * @since 1.0.0
	 */
	function wps_sfw_check_plugin_enable() {
		$is_enable = false;
		$wps_sfw_enable_plugin = get_option( 'wps_sfw_enable_plugin', '' );
		if ( 'on' == $wps_sfw_enable_plugin ) {
			$is_enable = true;
		}
		return $is_enable;
	}
}
if ( ! function_exists( 'wps_sfw_include_process_directory' ) ) {
	/**
	 * This function is used to include payment file.
	 *
	 * @since 1.0.0
	 * @name wps_sfw_include_process_directory
	 * @param string $wps_sfw_dir wps_sfw_dir.
	 * @param string $wps_selected_dir wps_selected_dir.
	 * @author WP Swings<ticket@wpswings.com>
	 * @link https://www.wpswing.com/
	 */
	function wps_sfw_include_process_directory( $wps_sfw_dir, $wps_selected_dir = '' ) {

		if ( is_dir( $wps_sfw_dir ) ) {
			$wps_dh = opendir( $wps_sfw_dir );
			if ( $wps_dh ) {

				while ( ( $wps_file = readdir( $wps_dh ) ) !== false ) {

					if ( '.' == $wps_file[0] ) {
						continue; // skip dirs . and .. by first char test.
					}

					if ( is_dir( $wps_sfw_dir . '/' . $wps_file ) ) {

						wps_sfw_include_process_directory( $wps_sfw_dir . '/' . $wps_file, $wps_file );

					} elseif ( 'class-wps-subscriptions-payment-' . $wps_selected_dir . '-main.php' == $wps_file ) {

						include $wps_sfw_dir . '/' . $wps_file;
					}
				}
				closedir( $wps_dh );
			}
		}
	}
}
if ( ! function_exists( 'wps_sfw_get_page_screen' ) ) {
	/**
	 * This function is used to get current screen.
	 *
	 * @name wps_sfw_get_page_screen
	 * @since 1.0.0
	 */
	function wps_sfw_get_page_screen() {

		$wps_screen_id = sanitize_title( 'WP Swings' );
		$screen_ids   = array(
			'toplevel_page_' . $wps_screen_id,
			$wps_screen_id . '_page_subscriptions_for_woocommerce_menu',
		);

		return apply_filters( 'wps_sfw_page_screen', $screen_ids );
	}
}

if ( ! function_exists( 'wps_wsp_disable_discontinued_paypal' ) ) {
	/**
	 * This function used to check if the subscription exist for payment methods
	 *
	 * @param mixed $payment_method .
	 * @return bool
	 */
	function wps_wsp_disable_discontinued_paypal( $payment_method ) {

		$is_paypal_subscription_exist = false;

		if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
			$args = array(
				'limit'        => -1,
				'type' => 'wps_subscriptions',
				'return' => 'ids',
				'payment_method' => $payment_method,
			);

			$orders = wc_get_orders( $args );
			if ( is_array( $orders ) && ! empty( $orders ) ) {
				$is_paypal_subscription_exist = true;
			}
		} else {
			$args = array(
				'numberposts' => -1,
				'post_type'   => 'wps_subscriptions',
				'meta_query'   => array(
					array(
						'key'     => '_payment_method',
						'value'   => $payment_method,
						'compare' => '=',
					),
				),
			);
			$orders = get_posts( $args );
			if ( is_array( $orders ) && ! empty( $orders ) ) {
				$is_paypal_subscription_exist = true;
			}
		}
		return $is_paypal_subscription_exist;
	}
}