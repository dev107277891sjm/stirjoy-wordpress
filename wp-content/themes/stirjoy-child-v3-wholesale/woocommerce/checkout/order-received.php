<?php
/**
 * "Order received" message - Custom Design Based on Figma
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/order-received.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version Custom
 *
 * @var WC_Order|false $order
 */

defined( 'ABSPATH' ) || exit;

// Get order data
$order_id = $order ? $order->get_id() : 0;
$order_total = $order ? $order->get_total() : 0;
$order_subtotal = $order ? $order->get_subtotal() : 0;
$shipping_total = $order ? (float) $order->get_shipping_total() : 0;

// Calculate cutoff date (7 days before the 15th of next month)
$next_month = date('Y-m-15', strtotime('+1 month'));
$cutoff_date = date('Y-m-d', strtotime($next_month . ' -7 days'));
$cutoff_datetime = date('F j, Y g:i A', strtotime($cutoff_date . ' 23:59:59'));
$cutoff_day = date('j', strtotime($cutoff_date));

?>

<div class="stirjoy-order-received-page">
	<div class="stirjoy-order-received-container">
		
		<!-- "You're in!" Section -->
		<div class="stirjoy-youre-in-section">
			<h1 class="stirjoy-youre-in-title"><?php esc_html_e( 'You\'re in!', 'woocommerce' ); ?></h1>
			<p class="stirjoy-youre-in-description">
				<?php esc_html_e( 'We\'ve pre-filled your first box with this month\'s menu! You can customize it by clicking on the button below.', 'woocommerce' ); ?>
			</p>
		</div>

		<!-- Main Content Area -->
		<div class="stirjoy-order-received-content">
			
			<!-- "What's next:" Box (Left) -->
			<div class="stirjoy-whats-next-box">
				<h2 class="stirjoy-whats-next-title"><?php esc_html_e( 'What\'s next:', 'woocommerce' ); ?></h2>
				
				<div class="stirjoy-whats-next-list">
					<div class="stirjoy-whats-next-item">
						<div class="stirjoy-bullet-point">
							<div class="stirjoy-bullet-circle"></div>
							<div class="stirjoy-bullet-line"></div>
						</div>
						<div class="stirjoy-whats-next-text">
							<p class="stirjoy-whats-next-text-primary">
								<?php esc_html_e( 'Customize your first box:', 'woocommerce' ); ?>
							</p>
							<p class="stirjoy-whats-next-text-secondary">
								<?php printf( esc_html__( 'You have until %s', 'woocommerce' ), '<strong>' . esc_html( $cutoff_datetime ) . '</strong>' ); ?>
							</p>
						</div>
					</div>
					
					<div class="stirjoy-whats-next-item">
						<div class="stirjoy-bullet-point">
							<div class="stirjoy-bullet-circle"></div>
							<div class="stirjoy-bullet-line"></div>
						</div>
						<div class="stirjoy-whats-next-text">
							<p class="stirjoy-whats-next-text-primary">
								<?php esc_html_e( 'Reminder email;', 'woocommerce' ); ?>
							</p>
							<p class="stirjoy-whats-next-text-secondary">
								<?php esc_html_e( 'We\'ll send you an email 3 days before your next delivery with the new monthly menu', 'woocommerce' ); ?>
							</p>
						</div>
					</div>
					
					<div class="stirjoy-whats-next-item">
						<div class="stirjoy-bullet-point">
							<div class="stirjoy-bullet-circle"></div>
						</div>
						<div class="stirjoy-whats-next-text">
							<p class="stirjoy-whats-next-text-primary">
								<?php esc_html_e( 'Next monthly deliveries:', 'woocommerce' ); ?>
							</p>
							<p class="stirjoy-whats-next-text-secondary">
								<?php printf( esc_html__( 'You\'ll have until %s of the month to customize or skip the delivery', 'woocommerce' ), '<strong>' . esc_html( $cutoff_day ) . '</strong>' ); ?>
							</p>
						</div>
					</div>
				</div>
			</div>

			<!-- "Contents:" Box (Right) -->
			<div class="stirjoy-contents-box">
				<h2 class="stirjoy-contents-title"><?php esc_html_e( 'Contents:', 'woocommerce' ); ?></h2>
				
				<div class="stirjoy-contents-list">
					<?php if ( $order ) : ?>
						<div class="stirjoy-contents-item">
							<span class="stirjoy-contents-label"><?php esc_html_e( '6-Meal Box', 'woocommerce' ); ?></span>
							<span class="stirjoy-contents-value"><?php echo wc_price( $order_subtotal ); ?></span>
						</div>
						
						<?php if ( $shipping_total > 0 ) : ?>
							<div class="stirjoy-contents-item">
								<span class="stirjoy-contents-label"><?php esc_html_e( 'Shipping', 'woocommerce' ); ?></span>
								<span class="stirjoy-contents-value"><?php echo wc_price( $shipping_total ); ?></span>
							</div>
						<?php endif; ?>
						
						<div class="stirjoy-contents-divider"></div>
						
						<div class="stirjoy-contents-item stirjoy-contents-total">
							<span class="stirjoy-contents-label"><?php esc_html_e( 'Total paid', 'woocommerce' ); ?></span>
							<span class="stirjoy-contents-value"><?php echo $order->get_formatted_order_total(); ?></span>
						</div>
					<?php else : ?>
						<div class="stirjoy-contents-item">
							<span class="stirjoy-contents-label"><?php esc_html_e( '6-Meal Box', 'woocommerce' ); ?></span>
							<span class="stirjoy-contents-value"><?php echo wc_price( 72.00 ); ?></span>
						</div>
						
						<div class="stirjoy-contents-item">
							<span class="stirjoy-contents-label"><?php esc_html_e( 'Shipping', 'woocommerce' ); ?></span>
							<span class="stirjoy-contents-value"><?php echo wc_price( 5.99 ); ?></span>
						</div>
						
						<div class="stirjoy-contents-divider"></div>
						
						<div class="stirjoy-contents-item stirjoy-contents-total">
							<span class="stirjoy-contents-label"><?php esc_html_e( 'Total paid', 'woocommerce' ); ?></span>
							<span class="stirjoy-contents-value"><?php echo wc_price( 77.99 ); ?></span>
						</div>
					<?php endif; ?>
				</div>
				
				<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="stirjoy-customize-box-button">
					<?php esc_html_e( 'CUSTOMIZE YOUR BOX', 'woocommerce' ); ?>
				</a>
			</div>
		</div>
		<img class="stirjoy-order-received-bg-graphic" src="<?php echo esc_url(stirjoy_get_image_url('201905d2706adecae372bf8dc4d4bad416fdac3e.png')); ?>" alt="Mastercard">
	</div>
</div>
