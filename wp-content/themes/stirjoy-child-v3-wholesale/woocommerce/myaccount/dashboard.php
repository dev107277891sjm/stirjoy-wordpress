<?php
/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Automattic\WooCommerce\Utilities\OrderUtil;

if ( ! function_exists( 'wps_sfw_cancel_url' ) ) {
	/**
	 * This function is used to cancel url.
	 *
	 * @name wps_sfw_cancel_url.
	 * @param int    $wps_subscription_id wps_subscription_id.
	 * @param String $wps_status wps_status.
	 * @since 1.0.0
	 */
	function wps_sfw_cancel_url( $wps_subscription_id, $wps_status ) {

		$wps_link = add_query_arg(
			array(
				'wps_subscription_id'        => $wps_subscription_id,
				'wps_subscription_status' => $wps_status,
			)
		);
		$wps_link = wp_nonce_url( $wps_link, $wps_subscription_id . $wps_status );

		return $wps_link;
	}
}

global $wpdb;
$user_id = get_current_user_id();
$customer = new WC_Customer($user_id);
$user_id          = absint( $user_id );
$is_hpos          = OrderUtil::custom_orders_table_usage_is_enabled();
$wps_per_page     = get_option( 'posts_per_page', 1000 );
$wps_current_page = empty( $wps_current_page ) ? 1 : absint( $wps_current_page );
$offset           = ( $wps_current_page - 1 ) * $wps_per_page;

if ( $is_hpos ) {
	$table      = $wpdb->prefix . 'wc_orders';
	$meta_table = $wpdb->prefix . 'wc_orders_meta';
	$id_field   = 'id';
	$type_field = 'type';
	$order_id_field = 'order_id';
} else {
	$table      = $wpdb->prefix . 'posts';
	$meta_table = $wpdb->prefix . 'postmeta';
	$id_field   = 'ID';
	$type_field = 'post_type';
	$order_id_field = 'post_id';
}

// Paginated query.
$sql = "
	SELECT DISTINCT {$table}.{$id_field}
	FROM {$table}
	INNER JOIN {$meta_table} AS meta ON meta.{$order_id_field} = {$table}.{$id_field}
	WHERE meta.meta_key = 'wps_customer_id'
	AND meta.meta_value = %s
	AND {$table}.{$type_field} = 'wps_subscriptions'
";

$sql .= " ORDER BY {$table}.{$id_field} DESC LIMIT %d OFFSET %d";

$subscription_ids = $wpdb->get_col(
	$wpdb->prepare( $sql, $user_id, $wps_per_page, $offset )
);

$wps_status = false;
$last_subscription_id = 0;
$total_delivery_count = 0;
$first_delivery_date = '';
$first_subscription_id = 0;
$next_delivery_date = '';
$next_delivery_year = '';
$subscription_price = 0;
$formatted_subscription_price = '';
$parent_order_id = 0;
$parent_order = false;
$shipping_price = 0;
$formatted_shipping_price = '';
$cutoff_date = '';
$delivery_date = '';
$exist_past_order = false;

if ( ! empty( $subscription_ids ) && is_array( $subscription_ids ) ) {
	/*foreach ( $subscription_ids as $key => $subcription_id ) {
		$wps_status = wps_sfw_get_meta_data( $subcription_id, 'wps_subscription_status', true );
		if ( 'active' === $wps_status ) {
			$last_subscription_id = $subcription_id;
			break;
		}
	}*/

	$last_subscription_id = $subscription_ids[0];
	$wps_status = wps_sfw_get_meta_data( $last_subscription_id, 'wps_subscription_status', true );

	foreach ( $subscription_ids as $key => $subcription_id ) {
		$wps_status_1 = wps_sfw_get_meta_data( $subcription_id, 'wps_subscription_status', true );
		$first_subscription_id = $subcription_id;
	}

	$total_delivery_count = count($subscription_ids);
	if($wps_status === 'active') {
		$total_delivery_count = $total_delivery_count - 1;
	}
	$first_schedule_start = wps_sfw_get_meta_data( $first_subscription_id, 'wps_schedule_start', true );
	if ( ! empty( $first_schedule_start ) && is_numeric( $first_schedule_start ) ) {
		$first_delivery_date = date('M Y', strtotime('+5 days', $first_schedule_start));
	}
}

$wps_cancel_url = '';
if($last_subscription_id > 0){
	$wps_schedule_start = wps_sfw_get_meta_data( $last_subscription_id, 'wps_schedule_start', true );
	if ( ! empty( $wps_schedule_start ) && is_numeric( $wps_schedule_start ) ) {
		$next_delivery_date = date('M d', strtotime('+5 days', $wps_schedule_start));
		$next_delivery_year = date('Y', strtotime('+5 days', $wps_schedule_start));
	}

	$subscription_price = wps_sfw_get_meta_data( $last_subscription_id, 'line_subtotal', true );
	$formatted_subscription_price = wp_kses_post( wc_price( $subscription_price ) );
	$parent_order_id   = wps_sfw_get_meta_data( $last_subscription_id, 'wps_parent_order', true );
	$parent_order = wc_get_order( $parent_order_id );
	
	if ( $parent_order && is_a( $parent_order, 'WC_Order' ) ) {
		$shipping_price = $parent_order->get_shipping_total();
		$formatted_shipping_price = wp_kses_post( wc_price( $shipping_price ) );

		$order_created_at = $parent_order->get_date_created();
		if ( $order_created_at ) {
			$cutoff_date = $order_created_at->date('F d, Y');
			$timestamp = $order_created_at->getTimestamp();
			if ( $timestamp ) {
				$delivery_date = date('F d, Y', strtotime('+5 days', $timestamp));
			}
		}
	}

	$wps_sfw_cancel_subscription = get_option( 'wps_sfw_cancel_subscription_for_customer', '' );
	$wps_sfw_cancel_subscription = apply_filters( 'wps_sfw_customer_cancel_button', $wps_sfw_cancel_subscription, $last_subscription_id );
	if ( 'on' == $wps_sfw_cancel_subscription ) {
		$wps_cancel_url = wps_sfw_cancel_url( $last_subscription_id, $wps_status );
	}
}
?>

<h2 class="title">My Account</h2>
<h3 class="subtitle">Manage your subscription and deliveries</h3>

<div class="my-account-content">
	<div class="left-block">
		<div class="subscription-summary">
			<div class="subscription-status">
				<div class="icon-block">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package w-5 h-5 text-primary" aria-hidden="true"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path><path d="M12 22V12"></path><polyline points="3.29 7 12 12 20.71 7"></polyline><path d="m7.5 4.27 9 5.15"></path></svg>
					<?php if($last_subscription_id > 0): ?>
						<?php
							switch ($wps_status) {
								case 'active':
									$bgColor = '#2d5a27';
									break;
								case 'on-hold':
									$bgColor = '#e67e22';
									break;
								case 'cancelled':
									$bgColor = '#c10007';
									break;
								case 'paused':
									$bgColor = '#e67e22';
									break;
								case 'pending':
									$bgColor = '#e67e22';
									break;
								case 'expired':
									$bgColor = '#e67e22';
									break;
								
								default:
									$bgColor = '#e67e22';
									break;
							}
						?>
						<span style="background: <?= esc_attr( $bgColor ) ?>"><?= esc_html( ucfirst( $wps_status ) ) ?></span>
					<?php endif; ?>
				</div>
				<h4>Subscription Status</h4>
				<?php if($last_subscription_id > 0): ?>
					<h3>Monthly Plan</h3>
				<?php else: ?>
					<h3>- - -</h3>
				<?php endif; ?>
			</div>
			<div class="next-delivery">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar w-5 h-5 text-primary mb-2" aria-hidden="true"><path d="M8 2v4"></path><path d="M16 2v4"></path><rect width="18" height="18" x="3" y="4" rx="2"></rect><path d="M3 10h18"></path></svg>
				<h4>Next Delivery</h4>
				<?php if($wps_status === 'active'): ?>
					<h3><?= esc_html( $next_delivery_date ) ?></h3>
					<h5><?= esc_html( $next_delivery_year ) ?></h5>
				<?php else: ?>
					<h3>- - -</h3>
				<?php endif; ?>
			</div>
			<div class="total-deliveries">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-truck w-5 h-5 text-primary mb-2" aria-hidden="true"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path><path d="M15 18H9"></path><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"></path><circle cx="17" cy="18" r="2"></circle><circle cx="7" cy="18" r="2"></circle></svg>
				<h4>Total Deliveries</h4>
				<h3><?= esc_html( $total_delivery_count ) ?></h3>
				<?php if ( ! empty( $first_delivery_date ) ): ?>
				<h5>since <?= esc_html( $first_delivery_date ) ?></h5>
				<?php endif; ?>
			</div>
		</div>

		<?php
		// Calculate delivery calendar dates
		$calendar_cutoff_dates = array();
		$calendar_delivery_dates = array();
		// Use WordPress timezone for current month/year
		$current_month = isset( $_GET['cal_month'] ) ? intval( $_GET['cal_month'] ) : intval( current_time( 'n' ) );
		$current_year = isset( $_GET['cal_year'] ) ? intval( $_GET['cal_year'] ) : intval( current_time( 'Y' ) );
		
		if ( $last_subscription_id > 0 && $wps_status === 'active' ) {
			// Get subscription interval
			$subscription_number = wps_sfw_get_meta_data( $last_subscription_id, 'wps_sfw_subscription_number', true );
			$subscription_interval = wps_sfw_get_meta_data( $last_subscription_id, 'wps_sfw_subscription_interval', true );
			$subscription_number = ! empty( $subscription_number ) ? intval( $subscription_number ) : 1;
			$subscription_interval = ! empty( $subscription_interval ) ? $subscription_interval : 'month';
			
			// Get base timestamp from order or schedule start
			$base_timestamp = current_time( 'timestamp' );
			if ( $parent_order && is_a( $parent_order, 'WC_Order' ) ) {
				$order_created_at = $parent_order->get_date_created();
				if ( $order_created_at ) {
					$base_timestamp = $order_created_at->getTimestamp();
				}
			} elseif ( ! empty( $wps_schedule_start ) && is_numeric( $wps_schedule_start ) ) {
				$base_timestamp = $wps_schedule_start;
			}
			
			// Calculate next 12 months of delivery dates
			for ( $i = 0; $i < 12; $i++ ) {
				// Calculate cutoff date based on subscription interval
				if ( $subscription_interval === 'month' ) {
					$cutoff_timestamp = strtotime( '+' . ( $i * $subscription_number ) . ' month', $base_timestamp );
				} elseif ( $subscription_interval === 'week' ) {
					$cutoff_timestamp = strtotime( '+' . ( $i * $subscription_number * 7 ) . ' days', $base_timestamp );
				} elseif ( $subscription_interval === 'day' ) {
					$cutoff_timestamp = strtotime( '+' . ( $i * $subscription_number ) . ' days', $base_timestamp );
				} else {
					// Default to monthly
					$cutoff_timestamp = strtotime( '+' . $i . ' month', $base_timestamp );
				}
				
				// Only include dates that are in the future or current month
				if ( $cutoff_timestamp >= strtotime( 'first day of this month', current_time( 'timestamp' ) ) ) {
					// Delivery is 5 days after cutoff
					$delivery_timestamp = strtotime( '+5 days', $cutoff_timestamp );
					
					$calendar_cutoff_dates[] = array(
						'date' => date( 'Y-m-d', $cutoff_timestamp ),
						'display' => date( 'F d, Y', $cutoff_timestamp ),
					);
					
					$calendar_delivery_dates[] = array(
						'date' => date( 'Y-m-d', $delivery_timestamp ),
						'display' => date( 'F d, Y', $delivery_timestamp ),
					);
				}
			}
		}
		
		// Generate calendar
		$first_day = mktime( 0, 0, 0, $current_month, 1, $current_year );
		$days_in_month = date( 't', $first_day );
		$day_of_week = date( 'w', $first_day ); // 0 = Sunday, 6 = Saturday
		$prev_month = $current_month == 1 ? 12 : $current_month - 1;
		$prev_year = $current_month == 1 ? $current_year - 1 : $current_year;
		$next_month = $current_month == 12 ? 1 : $current_month + 1;
		$next_year = $current_month == 12 ? $current_year + 1 : $current_year;
		$month_name = date( 'F Y', $first_day );
		?>
		
		<div class="delivery-calendar" data-current-month="<?= esc_attr( $current_month ) ?>" data-current-year="<?= esc_attr( $current_year ) ?>">
			<div class="block-title">Delivery Calendar</div>
			<div class="calendar-navigation">
				<button type="button" class="calendar-nav-btn calendar-prev" data-month="<?= esc_attr( $prev_month ) ?>" data-year="<?= esc_attr( $prev_year ) ?>">‹</button>
				<span class="calendar-month-year"><?= esc_html( $month_name ) ?></span>
				<button type="button" class="calendar-nav-btn calendar-next" data-month="<?= esc_attr( $next_month ) ?>" data-year="<?= esc_attr( $next_year ) ?>">›</button>
			</div>
			<div class="calendar-grid">
				<div class="calendar-header">
					<div class="calendar-day-header">Sun</div>
					<div class="calendar-day-header">Mon</div>
					<div class="calendar-day-header">Tue</div>
					<div class="calendar-day-header">Wed</div>
					<div class="calendar-day-header">Thu</div>
					<div class="calendar-day-header">Fri</div>
					<div class="calendar-day-header">Sat</div>
				</div>
				<div class="calendar-days">
					<?php
					// Empty cells for days before the first day of the month
					for ( $i = 0; $i < $day_of_week; $i++ ) {
						echo '<div class="calendar-day empty"></div>';
					}
					
					// Days of the month
					// Use WordPress timezone for today's date
					$today_date = current_time( 'Y-m-d' );
					for ( $day = 1; $day <= $days_in_month; $day++ ) {
						$date_str = sprintf( '%04d-%02d-%02d', $current_year, $current_month, $day );
						$is_cutoff = false;
						$is_delivery = false;
						$is_today = ( $date_str === $today_date );
						
						// Check if this date is a cutoff date
						foreach ( $calendar_cutoff_dates as $cutoff ) {
							if ( $cutoff['date'] === $date_str ) {
								$is_cutoff = true;
								break;
							}
						}
						
						// Check if this date is a delivery date
						foreach ( $calendar_delivery_dates as $delivery ) {
							if ( $delivery['date'] === $date_str ) {
								$is_delivery = true;
								break;
							}
						}
						
						$day_class = 'calendar-day';
						$day_icon = '';
						
						if ( $is_today ) {
							$day_class .= ' today';
						}
						
						if ( $is_cutoff ) {
							$day_class .= ' cutoff-date';
							$day_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="calendar-icon"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path></svg>';
						} elseif ( $is_delivery ) {
							$day_class .= ' delivery-date';
							$day_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="calendar-icon"><circle cx="12" cy="12" r="10"></circle></svg>';
						}
						
						echo '<div class="' . esc_attr( $day_class ) . '">';
						if ( $day_icon ) {
							echo $day_icon;
						}
						echo '<span class="day-number">' . esc_html( $day ) . '</span>';
						echo '</div>';
					}
					?>
				</div>
			</div>
			<div class="calendar-legend">
				<div class="legend-item">
					<div class="legend-color cutoff-color"></div>
					<span>Cutoff Date</span>
				</div>
				<div class="legend-item">
					<div class="legend-color delivery-color"></div>
					<span>Estimated Delivery</span>
				</div>
				<div class="legend-item">
					<div class="legend-color paused-color"></div>
					<span>Paused Delivery</span>
				</div>
			</div>
			<div class="calendar-info">
				<p>Payment processed at midnight</p>
				<p>3-5 business days after cutoff</p>
			</div>
		</div>

		<div class="upcoming-deliveries">
			<div class="block-title">Upcoming Deliveries</div>
			<?php if ( $parent_order && is_a( $parent_order, 'WC_Order' ) && $parent_order_id > 0 ): ?>
			<div class="order-item upcoming-order">
				<div class="order-summary">
					<div class="order-number">
						<span>Order #<?= esc_html( $parent_order_id ) ?></span>
						<span>Scheduled</span>
					</div>
					<div class="order-price"><?= wp_kses_post( $parent_order->get_formatted_order_total() ) ?></div>
				</div>
				<?php if ( ! empty( $cutoff_date ) ): ?>
				<div class="cutoff">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock w-3 h-3 mr-1" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
					Cutoff: <?= esc_html( $cutoff_date ) ?>
				</div>
				<?php endif; ?>
				<?php if ( ! empty( $delivery_date ) ): ?>
				<div class="delivery">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-truck w-3 h-3 mr-1" aria-hidden="true"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path><path d="M15 18H9"></path><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"></path><circle cx="17" cy="18" r="2"></circle><circle cx="7" cy="18" r="2"></circle></svg>
					Delivery: <?= esc_html( $delivery_date ) ?>
				</div>
				<?php endif; ?>
			</div>
			<?php else: ?>
			<div class="order-item upcoming-order">
				<div>No upcoming deliveries</div>
			</div>
			<?php endif; ?>
		</div>

		<div class="past-deliveries">
			<div class="block-title">Past Deliveries</div>
			<?php if($exist_past_order): ?>
				<div class="order-item past-order">
					<div class="order-summary">
						<div class="order-number">
							<span>Order #1001</span>
							<span>
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-check-big w-3 h-3 mr-1" aria-hidden="true"><path d="M21.801 10A10 10 0 1 1 17 3.335"></path><path d="m9 11 3 3L22 4"></path></svg>
								Delivered
							</span>
						</div>
						<div class="order-price">$77.99</div>
					</div>
					<div class="delivery">Delivered: October 15, 2025</div>
				</div>
			<?php else: ?>	
				<div>No past delivery</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="right-block">
		<div class="current-plan">
			<div class="block-title">Current Plan</div>

			<?php if($last_subscription_id > 0): ?>
				<h3>Monthly Plan</h3>
				<div class="subscription-name">6 meals per month</div>
				<div class="subscription-price"><?= $formatted_subscription_price ?> + <?= $formatted_shipping_price ?> shipping</div>
				<div class="delivery-freq">
					<p>Delivery Frequency</p>
					<button type="button">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-pen w-3 h-3 mr-1" aria-hidden="true"><path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"></path></svg>
						Edit Frequency
					</button>
				</div>
				<h5>Monthly (15th of each month)</h5>

				<?php if($wps_status === 'active' || $wps_status === 'paused'): ?>
					<button type="button" class="pause-subscription" data-subscription-id="<?= $last_subscription_id ?>" style="<?php if($wps_status === 'paused') echo 'display: none;'; ?>">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pause w-4 h-4 mr-2" aria-hidden="true"><rect x="14" y="4" width="4" height="16" rx="1"></rect><rect x="6" y="4" width="4" height="16" rx="1"></rect></svg>
						Pause Subscription
					</button>
				
					<button type="button" class="reactivate-subscription" data-subscription-id="<?= $last_subscription_id ?>" style="<?php if($wps_status === 'active') echo 'display: none;'; ?>">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pause w-4 h-4 mr-2" aria-hidden="true"><rect x="14" y="4" width="4" height="16" rx="1"></rect><rect x="6" y="4" width="4" height="16" rx="1"></rect></svg>
						Reactivate Subscription
					</button>
				<?php endif; ?>

				<?php if($wps_status !== 'cancelled' && !empty($wps_cancel_url)): ?>
					<button type="button" class="cancel-subscription" data-subscription-id="<?= $last_subscription_id ?>">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x w-4 h-4 mr-2" aria-hidden="true"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
						Cancel Subscription
					</button>
				<?php endif; ?>
			<?php else: ?>
				<h3>No Active Plan</h3>
			<?php endif; ?>
		</div>

		<div class="customer-info">
			<div class="block-title">
				Customer Information
				<div class="edit-icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-pen w-3 h-3" aria-hidden="true"><path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"></path></svg>
				</div>
			</div>
			<div class="customer-info-text">
				<div class="line-with-left-icon customer-name">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user w-4 h-4 mr-2 text-muted-foreground" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
					<span><?= esc_html( $customer->get_first_name() . ' ' . $customer->get_last_name() ) ?></span>
				</div>
				<div class="line-with-left-icon customer-email">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail w-4 h-4 mr-2 text-muted-foreground" aria-hidden="true"><path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"></path><rect x="2" y="4" width="20" height="16" rx="2"></rect></svg>
					<span><?= esc_html( $customer->get_email() ) ?></span>
				</div>
				<div class="line-with-left-icon customer-phone">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone w-4 h-4 mr-2 text-muted-foreground" aria-hidden="true"><path d="M13.832 16.568a1 1 0 0 0 1.213-.303l.355-.465A2 2 0 0 1 17 15h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2A18 18 0 0 1 2 4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-.8 1.6l-.468.351a1 1 0 0 0-.292 1.233 14 14 0 0 0 6.392 6.384"></path></svg>
					<span><?= esc_html( $customer->get_billing_phone() ) ?></span>
				</div>
			</div>
			<div class="customer-info-form" style="display: none;">
				<label for="customer-name">Name</label>
				<input type="text" name="customer_name" id="customer-name" value="<?= esc_attr( $customer->get_first_name() . ' ' . $customer->get_last_name() ) ?>">
				<label for="customer-email">Email</label>
				<input type="email" name="customer_email" id="customer-email" value="<?= esc_attr( $customer->get_email() ) ?>">
				<label for="customer-phone">Phone</label>
				<input type="text" name="customer_phone" id="customer-phone" value="<?= esc_attr( $customer->get_billing_phone() ) ?>">

				<div class="button-wrapper">
					<button type="button" class="customer-info-save">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-save w-3 h-3 mr-1" aria-hidden="true"><path d="M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"></path><path d="M17 21v-7a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v7"></path><path d="M7 3v4a1 1 0 0 0 1 1h7"></path></svg>
						Save Changes
					</button>
					<button type="button" class="customer-info-cancel">Cancel</button>
				</div>
			</div>
		</div>

		<div class="delivery-address">
			<div class="block-title">
				Delivery Address
				<div class="edit-icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-pen w-3 h-3" aria-hidden="true"><path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"></path></svg>
				</div>
			</div>
			<div class="line-with-left-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pin w-4 h-4 mr-2 text-muted-foreground mt-0.5" aria-hidden="true"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>
				<div>
					<?= esc_html( $customer->get_shipping_address_1() ) ?><br>
					<?php if(!empty($customer->get_shipping_address_2())): ?>
						<?= esc_html( $customer->get_shipping_address_2() ) ?><br>
					<?php endif; ?>
					<?= esc_html( $customer->get_shipping_city() ) ?>, <?= esc_html( $customer->get_shipping_state() ) ?>, <?= esc_html( $customer->get_shipping_postcode() ) ?><br>
					<?php 
					$shipping_country = $customer->get_shipping_country();
					if ( ! empty( $shipping_country ) && isset( WC()->countries->countries[ $shipping_country ] ) ) {
						echo esc_html( WC()->countries->countries[ $shipping_country ] );
					}
					?>
				</div>
			</div>
		</div>
	</div>
</div>


<?php
	/**
	 * My Account dashboard.
	 *
	 * @since 2.6.0
	 */
	do_action( 'woocommerce_account_dashboard' );

	/**
	 * Deprecated woocommerce_before_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_before_my_account' );

	/**
	 * Deprecated woocommerce_after_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_after_my_account' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
