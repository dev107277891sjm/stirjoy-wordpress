<?php
/**
 * The admin-specific cron functionality of the plugin.
 *
 * @link       https://wpswings.com
 * @since      1.0.0
 *
 * @package     Woocommerce_Subscriptions_Pro
 * @subpackage  Woocommerce_Subscriptions_Pro/package
 */

use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * The cron-specific functionality of the plugin admin side.
 *
 * @package     Woocommerce_Subscriptions_Pro
 * @subpackage  Woocommerce_Subscriptions_Pro/include
 * @author      WP Swings <webmaster@wpswings.com>
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'Woocommerce_Subscriptions_Pro_Scheduler' ) ) {

	/**
	 * Define class and module for cron.
	 */
	class Woocommerce_Subscriptions_Pro_Scheduler {
		/**
		 * Constructor
		 */
		public function __construct() {
			if ( wps_sfw_check_plugin_enable() ) {
				add_action( 'wps_sfw_create_admin_scheduler', array( $this, 'wps_wsp_retry_failed_order_scheduler' ) );
				add_action( 'wps_wsp_retry_failed_renewal_order_schedule', array( $this, 'wps_wsp_retry_failed_reneal_order_callback' ) );
				add_action( 'wps_wsp_plan_going_to_expire_schedule', array( $this, 'wps_wsp_plan_going_to_expire_schedule_callback' ) );
				add_action( 'wps_wsp_update_status_start_date', array( $this, 'wps_wsp_update_status_start_date_callback' ) );
				add_action( 'wps_wsp_send_reminder_recurring', array( $this, 'wps_wsp_send_reminder_callback' ) );
				add_action( 'wps_sfw_renewal_order_creation', array( $this, 'wps_wsp_add_shipping_price_on_renewal_order' ), 10, 2 );
				add_action( 'wps_sfw_subscription_order', array( $this, 'wps_wsp_add_shipping_price_on_subscription_order' ), 10, 2 );
			}
		}

		/**
		 * Function name wps_wsp_send_reminder_callback.
		 * this function is used to send reminder email.
		 *
		 * @return void
		 */
		public function wps_wsp_send_reminder_callback() {
			$current_time = current_time( 'timestamp' );

			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
				$args = array(
					'return' => 'ids',
					'type'   => 'wps_subscriptions',
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key'   => 'wps_subscription_status',
							'value' => array( 'active', 'pending' ),
						),
						array(
							'relation' => 'AND',
							array(
								'key'   => 'wps_parent_order',
								'compare' => 'EXISTS',
							),
							array(
								'relation' => 'AND',
								array(
									'key'   => 'wps_next_payment_date',
									'value' => $current_time,
									'compare' => '>',
								),
								array(
									'key'   => 'wps_next_payment_date',
									'value' => 0,
									'compare' => '!=',
								),
							),
						),
					),
				);
				$wps_subscriptions = wc_get_orders( $args );
			} else {
				$args = array(
					'numberposts' => -1,
					'post_type'   => 'wps_subscriptions',
					'post_status'   => 'wc-wps_renewal',
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key'   => 'wps_subscription_status',
							'value' => array( 'active', 'pending' ),
						),
						array(
							'relation' => 'AND',
							array(
								'key'   => 'wps_parent_order',
								'compare' => 'EXISTS',
							),
							array(
								'relation' => 'AND',
								array(
									'key'   => 'wps_next_payment_date',
									'value' => $current_time,
									'compare' => '>',
								),
								array(
									'key'   => 'wps_next_payment_date',
									'value' => 0,
									'compare' => '!=',
								),
							),
						),
					),
				);
				$wps_subscriptions = get_posts( $args );
			}

			if ( isset( $wps_subscriptions ) && ! empty( $wps_subscriptions ) && is_array( $wps_subscriptions ) ) {
				foreach ( $wps_subscriptions as $key => $subscription ) {

					if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
						$wps_subscription_id = $subscription ? $subscription : '';
					} else {
						$wps_subscription_id = isset( $subscription->ID ) ? $subscription->ID : '';
					}
					if ( wps_sfw_check_valid_subscription( $wps_subscription_id ) ) {
						$wps_subscription_exp = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_next_payment_date', true );
						if ( isset( $wps_subscription_exp ) && ! empty( $wps_subscription_exp ) ) {

							$wps_wsp_is_send = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_sfw_is_recurring_reminder_sent', true );
							if ( 'sent' != $wps_wsp_is_send ) {
								$wps_wps_send_time = strtotime( '-' . get_option( 'wsp_send_before_recurring_reminder', '14' ) . 'days', $wps_subscription_exp );
								if ( current_time( 'Y-m-d' ) === gmdate( 'Y-m-d', $wps_wps_send_time ) ) {
									if ( isset( $wps_subscription_id ) && ! empty( $wps_subscription_id ) ) {
										$mailer = WC()->mailer()->get_emails();
										// Send the "Reminer" notification.
										if ( isset( $mailer['wps_wsp_recurring_reminder'] ) ) {
											$mailer['wps_wsp_recurring_reminder']->trigger( $wps_subscription_id );
										}
									}
									wps_wsp_update_meta_data( $wps_subscription_id, 'wps_sfw_is_recurring_reminder_sent', 'sent' );
								}
							}
						}
					}
				}
			}
		}

		/**
		 * This function is used create scheduler.
		 *
		 * @name wps_wsp_retry_failed_order_scheduler
		 * @since 1.0.0
		 */
		public function wps_wsp_retry_failed_order_scheduler() {
			if ( class_exists( 'ActionScheduler' ) ) {
				if ( function_exists( 'as_next_scheduled_action' ) && false === as_next_scheduled_action( 'wps_wsp_retry_failed_renewal_order_schedule' ) ) {
					as_schedule_recurring_action( strtotime( 'hourly' ), DAY_IN_SECONDS, 'wps_wsp_retry_failed_renewal_order_schedule' );
				}
				if ( function_exists( 'as_next_scheduled_action' ) && false === as_next_scheduled_action( 'wps_wsp_plan_going_to_expire_schedule' ) ) {
					as_schedule_recurring_action( strtotime( 'hourly' ), DAY_IN_SECONDS, 'wps_wsp_plan_going_to_expire_schedule' );
				}
				if ( function_exists( 'as_next_scheduled_action' ) && false === as_next_scheduled_action( 'wps_wsp_update_status_start_date' ) ) {
					as_schedule_recurring_action( strtotime( 'hourly' ), 3600, 'wps_wsp_update_status_start_date' );
				}
				if ( function_exists( 'as_next_scheduled_action' ) && false === as_next_scheduled_action( 'wps_wsp_send_reminder_recurring' ) ) {
					as_schedule_recurring_action( strtotime( 'daily' ), 24 * 3600, 'wps_wsp_send_reminder_recurring' );
				}
			}
		}

		/**
		 * This function is used to update status on subscription start date.
		 *
		 * @return void
		 */
		public function wps_wsp_update_status_start_date_callback() {
			if ( ! wps_wsp_allow_start_date_subscription() ) {
				return;
			}
			$current_time = current_time( 'Y-m-d' );

			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
				$args = array(
					'return' => 'ids',
					'type'   => 'wps_subscriptions',
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key'   => 'wps_subscription_status',
							'value' => array( 'pending' ),
						),
						array(
							'relation' => 'AND',
							array(
								'key'   => 'wps_parent_order',
								'compare' => 'EXISTS',
							),
							array(
								array(
									'key'   => 'wps_sfw_subscription_start_date',
									'value' => $current_time,
									'compare' => '=',
								),
							),
						),
					),
				);
				$wps_subscriptions = wc_get_orders( $args );
			} else {
				$args = array(
					'numberposts' => -1,
					'post_type'   => 'wps_subscriptions',
					'post_status'   => 'wc-wps_renewal',
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key'   => 'wps_subscription_status',
							'value' => array( 'pending' ),
						),
						array(
							'relation' => 'AND',
							array(
								'key'   => 'wps_parent_order',
								'compare' => 'EXISTS',
							),
							array(
								array(
									'key'   => 'wps_sfw_subscription_start_date',
									'value' => $current_time,
									'compare' => '=',
								),
							),
						),
					),
				);
				$wps_subscriptions = get_posts( $args );
			}
			if ( isset( $wps_subscriptions ) && ! empty( $wps_subscriptions ) && is_array( $wps_subscriptions ) ) {
				foreach ( $wps_subscriptions as $key => $subscription ) {
					if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
						$wps_subscription_id = $subscription ? $subscription : '';
					} else {
						$wps_subscription_id = isset( $subscription->ID ) ? $subscription->ID : '';
					}
					if ( wps_sfw_check_valid_subscription( $wps_subscription_id ) ) {

						wps_wsp_update_meta_data( $wps_subscription_id, 'wps_subscription_status', 'active' );

					}
				}
			}
		}

		/**
		 * This function is used create scheduler callback.
		 *
		 * @name wps_wsp_retry_failed_reneal_order_callback
		 * @since 1.0.0
		 */
		public function wps_wsp_retry_failed_reneal_order_callback() {
			if ( wps_wsp_enable_automatic_retry_failed_attewsp() ) {
				if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
					$args = array(
						'return' => 'ids',
						'type'   => 'shop_order',
						'status'   => array( 'wc-failed', 'wc-pending' ),
						'meta_query' => array(
							array(
								'key'   => 'wps_sfw_renewal_order',
								'value' => 'yes',
							),
						),
					);
					$wps_failed_renewal_order = wc_get_orders( $args );
				} else {
					$args = array(
						'numberposts' => -1,
						'post_type'   => 'shop_order',
						'post_status'   => array( 'wc-failed', 'wc-pending' ),
						'meta_query' => array(
							array(
								'key'   => 'wps_sfw_renewal_order',
								'value' => 'yes',
							),
						),
					);
					$wps_failed_renewal_order = get_posts( $args );
				}
				if ( isset( $wps_failed_renewal_order ) && ! empty( $wps_failed_renewal_order ) && is_array( $wps_failed_renewal_order ) ) {
					foreach ( $wps_failed_renewal_order as $key => $value ) {
						if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
							$order_id = $value;
						} else {
							$order_id = $value->ID;
						}

						$subscription_id = wps_wsp_get_meta_data( $order_id, 'wps_sfw_subscription', true );
						if ( isset( $subscription_id ) && ! empty( $subscription_id ) && wps_sfw_check_valid_subscription( $subscription_id ) ) {
							$subscription_status = wps_wsp_get_meta_data( $subscription_id, 'wps_subscription_status', true );
							if ( 'cancelled' == $subscription_status ) {
								return;
							}
							$parent_order_id = wps_wsp_get_meta_data( $order_id, 'wps_sfw_parent_order_id', true );
							$parent_order   = wc_get_order( $parent_order_id );
							$payment_method = $parent_order->get_payment_method();

							wps_wsp_no_of_time_retry_failed_order( $order_id );

							$renewal_order = wc_get_order( $order_id );
							do_action( 'wps_sfw_other_payment_gateway_renewal', $renewal_order, $subscription_id, $payment_method );
							do_action( 'wps_sfw_custom_hook_for_tmu', $order_id );

							if ( $renewal_order->get_status() != 'processing' || $renewal_order->get_status() != 'completed' ) {
								do_action( 'wps_sfw_cancel_failed_susbcription', false, $order_id, $subscription_id );
							}
						}
					}
				}
			}
		}

		/**
		 * This function is used send email on plan going to expire.
		 *
		 * @name wps_wsp_plan_going_to_expire_schedule_callback
		 * @since 1.0.0
		 */
		public function wps_wsp_plan_going_to_expire_schedule_callback() {

			if ( wps_wsp_check_enable_plan_going_expire_email_notification() ) {
				return;
			}

			$current_time = current_time( 'timestamp' );

			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
				$args = array(
					'return'  => 'ids',
					'type'   => 'wps_subscriptions',
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key'   => 'wps_subscription_status',
							'value' => array( 'active', 'pending' ),
						),
						array(
							'relation' => 'AND',
							array(
								'key'   => 'wps_parent_order',
								'compare' => 'EXISTS',
							),
							array(
								'relation' => 'AND',
								array(
									'key'   => 'wps_susbcription_end',
									'value' => $current_time,
									'compare' => '>',
								),
								array(
									'key'   => 'wps_susbcription_end',
									'value' => 0,
									'compare' => '!=',
								),
							),
						),
					),
				);
				$wps_subscriptions = wc_get_orders( $args );
			} else {
				$args = array(
					'numberposts' => -1,
					'post_type'   => 'wps_subscriptions',
					'post_status'   => 'wc-wps_renewal',
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key'   => 'wps_subscription_status',
							'value' => array( 'active', 'pending' ),
						),
						array(
							'relation' => 'AND',
							array(
								'key'   => 'wps_parent_order',
								'compare' => 'EXISTS',
							),
							array(
								'relation' => 'AND',
								array(
									'key'   => 'wps_susbcription_end',
									'value' => $current_time,
									'compare' => '>',
								),
								array(
									'key'   => 'wps_susbcription_end',
									'value' => 0,
									'compare' => '!=',
								),
							),
						),
					),
				);
				$wps_subscriptions = get_posts( $args );
			}
			if ( isset( $wps_subscriptions ) && ! empty( $wps_subscriptions ) && is_array( $wps_subscriptions ) ) {
				foreach ( $wps_subscriptions as $key => $subscription ) {
					if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
						$wps_subscription_id = $subscription ? $subscription : '';
					} else {
						$wps_subscription_id = isset( $subscription->ID ) ? $subscription->ID : '';
					}
					if ( wps_sfw_check_valid_subscription( $wps_subscription_id ) ) {
						$wps_subscription_exp = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_susbcription_end', true );
						if ( isset( $wps_subscription_exp ) && ! empty( $wps_subscription_exp ) ) {

							$wps_wsp_is_send = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_wsp_plan_expire_notice_send', true );
							if ( 'sent' != $wps_wsp_is_send ) {
								$wps_wps_no_of_days = wps_wsp_plan_going_to_expire_before_days();
								$wps_wps_no_of_days = apply_filters( 'wps_wsp_plan_going_to_expire_extra_checking', $wps_wps_no_of_days, $wps_subscription_id );
								$wps_wps_send_time = strtotime( '-' . $wps_wps_no_of_days . ' days', $wps_subscription_exp );

								if ( $current_time >= $wps_wps_send_time ) {
									wps_wsp_send_plan_going_to_expire_email( $wps_subscription_id );
									wps_wsp_update_meta_data( $wps_subscription_id, 'wps_wsp_plan_expire_notice_send', 'sent' );
								}
							}
						}
					}
				}
			}
		}

		/**
		 * Function for wps_wsp_add_shipping_price_on_renewal_order.
		 *
		 * @param object  $renewal_order for $renewal_order.
		 * @param integer $subscription_id for $subscription_id.
		 * @return void
		 */
		public function wps_wsp_add_shipping_price_on_renewal_order( $renewal_order, $subscription_id ) {
			$check_shipping = get_option( 'wsp_allow_shipping_subscription', 'no' );
			if ( 'on' === $check_shipping ) {
				if ( function_exists( 'wps_wsp_set_shipping_fee' ) ) {
					wps_wsp_set_shipping_fee( $subscription_id, $renewal_order->get_id() );
				}
			}
		}
		/**
		 * Function for wps_wsp_add_shipping_price_on_subscription_order.
		 *
		 * @param object  $subscription_order for $subscription_order.
		 * @param integer $parent_order_id for $parent_order_id.
		 * @return void
		 */
		public function wps_wsp_add_shipping_price_on_subscription_order( $subscription_order, $parent_order_id ) {
			$check_shipping = get_option( 'wsp_allow_shipping_subscription', '' );
			if ( 'on' === $check_shipping ) {
				if ( function_exists( 'wps_wsp_set_shipping_fee' ) ) {
					wps_wsp_set_shipping_fee( $parent_order_id, $subscription_order->get_id() );
				}
			}
		}
	}
}
return new Woocommerce_Subscriptions_Pro_Scheduler();
