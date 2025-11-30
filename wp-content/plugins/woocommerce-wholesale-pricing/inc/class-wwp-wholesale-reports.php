<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Class To Add Wholesale Reports
 */
if ( ! class_exists( 'WWP_Wholesale_Reports' ) ) {
	class WWP_Wholesale_Reports {
		
		public function __construct() {
			add_action( 'wwp_dashboard_reports', array( $this, 'wwp_dashboard_reports' ) );
			add_action( 'wwp_dashboard_user_requests', array( $this, 'wwp_dashboard_user_requests' ) );
			add_action( 'wwp_dashboard_recent_order', array( $this, 'wwp_dashboard_recent_order' ) );
			add_action( 'wp_ajax_wwp_custom_reports', array( $this, 'wwp_custom_reports_ajax' ) );
		}
		public function wwp_custom_reports_ajax() {

			global $wpdb;
			
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$timezone = get_option('timezone_string');
			if ( empty( $timezone ) || null === $timezone ) { 
				$timezone = 'UTC';
			}
			 
			//date_default_timezone_set($timezone);
			// total wholesale sales
			$total_wholesale_sales_today = 0;
			$total_wholesale_sales_today_wholesale = 0;
			$total_wholesale_sales_today_retailer = 0;
			$total_wholesale_sales_seven_days = 0;
			$total_wholesale_sales_thirtyone_days = 0;
			$total_wholesale_sales_seven_days_wholesale =0;
			$total_wholesale_sales_seven_days_retailer =0;
			// total tax
			$tax_wholesale_sales_today = 0;
			$tax_wholesale_sales_seven_days = 0;
			$tax_wholesale_sales_thirtyone_days = 0;
	
			// nr of orders
			$tax_wholesale_sales_today = 0;
			$number_wholesale_sales_seven_days = 0;
			$number_wholesale_sales_thirtyone_days = 0;
	
			// nr of unique customers
			$customers_wholesale_sales_today = 0;
			$customers_wholesale_sales_seven_days = 0;
			$customers_wholesale_sales_thirtyone_days = 0;
			$number_wholesale_sales_today_days = 0;
			$total_wholesale_sales_thirtyone_days_wholesale = 0;
			$total_wholesale_sales_thirtyone_days_retailer  = 0;
			$wholesalerefund_manual  = 0;
			
			$data = array();
			$post = wwp_get_post_data('');
			
			$date_to = $post['date_end'];
			$date_from = $post['date_start'] . ' 00:00:00';

			$begin = new DateTime( $date_from );
			$end = new DateTime( $date_to );
			if ( 'custom' == $post['date_type'] ) {
				$end = $end->modify( '+1 day' ); 
				$date_to = $date_to . ' 23:59:59';
			}
			
			$interval = new DateInterval('P1D');
			$daterange = new DatePeriod( $begin, $interval, $end );

			if ( 'yes' == get_option( 'woocommerce_custom_orders_table_enabled' ) ) {
				$orders_today = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}wc_orders
						WHERE `status` IN ('wc-processing', 'wc-completed')
						AND date_created_gmt BETWEEN %s AND %s
					", $date_from, $date_to ) );
			} else {
				$orders_today = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts
						WHERE post_type = 'shop_order'
						AND post_status IN ('wc-processing', 'wc-completed')
						AND post_date BETWEEN %s AND %s
					", $date_from, $date_to ) );
			}
			
			if ( 'yes' == get_option( 'woocommerce_custom_orders_table_enabled' ) ) {
				$orders_today_refunded = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}wc_orders 
						WHERE `status` IN ('wc-refunded')
						AND date_created_gmt BETWEEN %s AND %s
					", $date_from, $date_to ) );
			} else {
				$orders_today_refunded = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts
						WHERE post_type = 'shop_order'
						AND post_status IN ('wc-refunded')
						AND post_date BETWEEN %s AND %s
					", $date_from, $date_to ) );
			}


			//calculate today
			$array_of_customers_ids = array();
			foreach ($orders_today as $order) {
				$order_user_id = get_post_meta($order->ID, '_customer_user', true);
				$orders_data = wc_get_order( $order->ID );
				if ( $orders_data instanceof WC_Order_Refund ) {
					$orders_data = wc_get_order( $orders_data->get_parent_id() );
				}
				if ( empty( $order_user_id ) ) {
					$order_user_id = $orders_data->get_user_id();
				}
				// wholesale coustomer 
				if ( is_wholesaler_user( $order_user_id ) ) {
					$total_wholesale_sales_today += $orders_data->get_remaining_refund_amount();
					$total_wholesale_sales_today_wholesale += $orders_data->get_remaining_refund_amount();
					$wholesalerefund_manual += $orders_data->get_total_refunded();
					$tax_wholesale_sales_today += (float) get_post_meta($order->ID, '_order_tax', true) + (float) get_post_meta($order->ID, '_order_shipping_tax', true);
					$tax_wholesale_sales_today++;
					$number_wholesale_sales_today_days++;
					array_push($array_of_customers_ids, $order_user_id);
				} else {
					// retailer customer
					if ( $orders_data instanceof WC_Order_Refund ) {
						$orders_data = wc_get_order( $orders_data->get_parent_id() );
					}
					$total_wholesale_sales_today += $orders_data->get_remaining_refund_amount();
					$total_wholesale_sales_today_retailer += $orders_data->get_remaining_refund_amount();
					$tax_wholesale_sales_today += (float) get_post_meta($order->ID , '_order_tax', true) + (float) get_post_meta($order->ID, '_order_shipping_tax', true);
				}
			}
			$customers_wholesale_sales=count(array_unique($array_of_customers_ids));
			 
			// get each day in the past 31 days and form an array with day and total sales
			$i=1;
			$days_sales_array = array();
			$days_sales_b2c_array = array();
			$hours_sales_b2c_array = array(
				'00' => 0,
				'01' => 0,
				'02' => 0,
				'03' => 0,
				'04' => 0,
				'05' => 0,
				'06' => 0,
				'07' => 0,
				'08' => 0,
				'09' => 0,
				'10' => 0,
				'11' => 0,
				'12' => 0,
				'13' => 0,
				'14' => 0,
				'15' => 0,
				'16' => 0,
				'17' => 0,
				'18' => 0,
				'19' => 0,
				'20' => 0,
				'21' => 0,
				'22' => 0,
				'23' => 0,
			);
			$hours_sales_array = $hours_sales_b2c_array;
			// Refunded

			$total_wholesale_sales_today_days_refunded = 0;
			foreach ($orders_today_refunded as $order) { 
				$order_user_id = get_post_meta($order->ID, '_customer_user', true);
				// wholesale coustomer 
				if ( is_wholesaler_user( $order_user_id ) ) {
					$total_wholesale_sales_today_days_refunded += get_post_meta($order->ID, '_order_total', true);
				} 
			}
			
			$total_wholesale_sales_today_days_refunded = $total_wholesale_sales_today_days_refunded + $wholesalerefund_manual;

			//while ( $i < 32 ) { 
			foreach ($daterange as $key => $value) {
				$date_to = $value->format('Y-m-d');
				//var_dump($date_to);
				
				$date_from = $date_to;
				$date_to = $date_to . ' 23:59:59';
				$date_wholesale_order_from[$value->format('Y-m-d')] = 0;
				$date_retailer_order_from[$value->format('Y-m-d')] = 0;
				 
				
				if ( 1 === $i ) { 
					if ( 'yes' == get_option( 'woocommerce_custom_orders_table_enabled' ) ) {
						$orders_day = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}wc_orders 
							WHERE `status` IN ('wc-processing', 'wc-completed')
							AND date_created_gmt BETWEEN %s AND %s
						", $date_from, $date_to ) );  
					} else {
						$orders_day = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts 
							WHERE post_type = 'shop_order'
							AND post_status IN ('wc-processing', 'wc-completed')
							AND post_date BETWEEN %s AND %s
						", $date_from, $date_to ) );  
					}

				} elseif ( 'yes' == get_option( 'woocommerce_custom_orders_table_enabled' ) ) {
						$orders_day = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}wc_orders
							WHERE `status` IN ('wc-processing', 'wc-completed')
							AND date_created_gmt BETWEEN %s AND %s
						", $date_from, $date_to ) );
				} else {
					$orders_day = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts
							WHERE post_type = 'shop_order'
							AND post_status IN ('wc-processing', 'wc-completed')
							AND post_date BETWEEN %s AND %s
						", $date_from, $date_to ) );
				}
				//calculate totals
				$sales_total_wholesale = 0;
				$sales_total_retailer = 0;
				foreach ( $orders_day as $order ) {
					$orders_data = wc_get_order( $order->ID );
					$order_user_id = get_post_meta($order->ID, '_customer_user', true);
					if ( empty( $order_user_id ) ) {
						$order_user_id = $orders_data->get_user_id();
					}
					if ( is_wholesaler_user( $order_user_id ) ) {
						if ( $orders_data instanceof WC_Order_Refund ) {
							$orders_data = wc_get_order( $orders_data->get_parent_id() );
						}
						$sales_total_wholesale += $orders_data->get_remaining_refund_amount();
						$date_wholesale_order_from[$value->format('Y-m-d')] += $orders_data->get_remaining_refund_amount();
					} else {
						// check user
						if ( $orders_data instanceof WC_Order_Refund ) {
							$orders_data = wc_get_order( $orders_data->get_parent_id() );
						}
						$sales_total_retailer += $orders_data->get_remaining_refund_amount();
						$date_retailer_order_from[$value->format('Y-m-d')] += $orders_data->get_remaining_refund_amount();
					}
				}
	
				// if first day, get this by hour
				if ( 1 === $i ) { 
					$date_to = gmdate('Y-m-d');
					$date_from = gmdate('Y-m-d');
					//var_dump( $date_to, $date_from );
					$post_status = implode("','", array( 'wc-processing', 'wc-completed' ) );
					if ( 'yes' == get_option( 'woocommerce_custom_orders_table_enabled' ) ) {
						$orders_day = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}wc_orders 
								WHERE `status` IN ('wc-processing', 'wc-completed')
								AND date_created_gmt BETWEEN %s AND %s
							", $date_from, $date_to ) );
					} else {
						$orders_day = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts 
								WHERE post_type = 'shop_order'
								AND post_status IN ('wc-processing', 'wc-completed')
								AND post_date BETWEEN %s AND %s
							", $date_from, $date_to ) );
					}
					foreach ($orders_day as $order) {
						// get hour of the order
						$orders_data = wc_get_order( $order->ID );
						$order_user_id = get_post_meta($order->ID, '_customer_user', true);
						$hour = get_post_time('H', false, $order->ID);
						
						if ( is_wholesaler_user( $order_user_id ) ) { 
							if ( $orders_data instanceof WC_Order_Refund ) {
								$orders_data = wc_get_order( $orders_data->get_parent_id() );
							}
							$hours_sales_array[$hour] += $orders_data->get_remaining_refund_amount();
						} else {
							if ( $orders_data instanceof WC_Order_Refund ) {
								$orders_data = wc_get_order( $orders_data->get_parent_id() );
							}
							$hours_sales_b2c_array[$hour] += $orders_data->get_remaining_refund_amount();
						}
					}
				}
				array_push ($days_sales_array, $sales_total_wholesale);
				array_push ($days_sales_b2c_array, $sales_total_retailer);
				$i++;
			}

			$data['graph_B2B'] = array();
			$data['graph_B2C'] = array();
			$data['hours_sales_array'] = array();
			$data['hours_sales_b2c_array'] = array();
			 
			foreach ( $date_wholesale_order_from as $date_order_key => $date_order_value ) { 
				$data['graph_B2B'][] = array( 'x' => $date_order_key, 'y' =>  $date_order_value );
			}
			foreach ( $date_retailer_order_from as $date_order_key => $date_order_value ) { 
				$data['graph_B2C'][] = array( 'x' => $date_order_key, 'y' =>  $date_order_value );
			}
			foreach ( $hours_sales_array as $date_order_key => $date_order_value ) { 
				$data['hours_sales_array'][] = array( 'x' => strval($date_order_key), 'y' =>  $date_order_value );
			}
			foreach ( $hours_sales_b2c_array as $date_order_key => $date_order_value ) { 
				$data['hours_sales_b2c_array'][] = array( 'x' => strval( $date_order_key ), 'y' =>  $date_order_value );
			}
		 
			$data['total_wholesale_sales_today_wholesale'] = wc_price( $total_wholesale_sales_today_wholesale );
			$data['total_wholesale_sales_today_retailer'] = wc_price( $total_wholesale_sales_today_retailer );
			$data['total_wholesale_sales_today_days_refunded'] = wc_price( $total_wholesale_sales_today_days_refunded );
			$data['total_of_customers'] = $customers_wholesale_sales;
			$data['number_wholesale_sales_days'] = $number_wholesale_sales_today_days;
			
			echo wp_json_encode($data);
			wp_die();
		}
		 
		public function wwp_dashboard_reports() {
			global $wpdb;
			?>
			<div class="container  whitebg shadow-sm bg-body rounded">
			<h5><?php esc_html_e( 'Sales Summary (B2B) - (B2C) ', 'woocommerce-wholesale-pricing' ); ?></h5>
				<div id="wwp_main_reports" class="row">
				 <div class="col-md-3">
					<div class="row">  
						<div class="col-md-2">
							<svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" fill="currentColor" class="bi bi-cart3" viewBox="0 0 16 16">
								<path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
							</svg>
						</div>
						<div class="col-md-8">
							<p class="card-text"><?php esc_html_e( 'Total B2B - Wholesaler', 'woocommerce-wholesale-pricing' ); ?></p>
							<h5 class="card-title total_sale_b2b_amount">0</h5>
						</div>
					</div>
					<div class="row">  
						<div class="col-md-2">
							<svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" fill="currentColor" class="bi bi-archive" viewBox="0 0 16 16">
							  <path d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1V2zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5H2zm13-3H1v2h14V2zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
							</svg>
						</div>
						<div class="col-md-8">
							<p class="card-text"><?php esc_html_e( 'Total B2C - Retailer', 'woocommerce-wholesale-pricing' ); ?></p>
							<h5 class="card-title total_sale_b2c_amount">0</h5>
						</div>
					</div>
					<div class="row">  
						<div class="col-md-2">
							<svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" fill="currentColor" class="bi bi-archive-fill" viewBox="0 0 16 16">
							  <path d="M12.643 15C13.979 15 15 13.845 15 12.5V5H1v7.5C1 13.845 2.021 15 3.357 15h9.286zM5.5 7h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1 0-1zM.8 1a.8.8 0 0 0-.8.8V3a.8.8 0 0 0 .8.8h14.4A.8.8 0 0 0 16 3V1.8a.8.8 0 0 0-.8-.8H.8z"/>
							</svg>
						</div>
						<div class="col-md-8">
							<p class="card-text"><?php esc_html_e( 'Wholesale Refund', 'woocommerce-wholesale-pricing' ); ?></p>
							<h5 class="card-title wholesale_refund">0</h5>
						</div>
					</div>
					<div class="row">  
						<div class="col-md-2">
							<svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" fill="currentColor" class="bi bi-basket2" viewBox="0 0 16 16">
							  <path d="M4 10a1 1 0 0 1 2 0v2a1 1 0 0 1-2 0v-2zm3 0a1 1 0 0 1 2 0v2a1 1 0 0 1-2 0v-2zm3 0a1 1 0 1 1 2 0v2a1 1 0 0 1-2 0v-2z"/>
							  <path d="M5.757 1.071a.5.5 0 0 1 .172.686L3.383 6h9.234L10.07 1.757a.5.5 0 1 1 .858-.514L13.783 6H15.5a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-.623l-1.844 6.456a.75.75 0 0 1-.722.544H3.69a.75.75 0 0 1-.722-.544L1.123 8H.5a.5.5 0 0 1-.5-.5v-1A.5.5 0 0 1 .5 6h1.717L5.07 1.243a.5.5 0 0 1 .686-.172zM2.163 8l1.714 6h8.246l1.714-6H2.163z"/>
							</svg>
						</div>
						<div class="col-md-8">
							<p class="card-text"><?php esc_html_e( 'Wholesale Orders', 'woocommerce-wholesale-pricing' ); ?></p>
							<h5 class="card-title number_wholesale_sales_days">0</h5>
						</div>
					</div>
					<div class="row">  
						<div class="col-md-2">
							<svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
							  <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
							  <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
							</svg>
						</div>
						<div class="col-md-8">
							<p class="card-text"><?php esc_html_e( 'Wholesale Customer', 'woocommerce-wholesale-pricing' ); ?></p>
							<h5 class="card-title total_of_customers">0</h5>
						</div>
					</div>
					
				</div>
				<div class="col-md-9 wwp-form-element">
					<select id="wwp_reports_op_select" class="custom-selects form-control" onchange="call_reports(this.value)" >
						<option value="0" ><?php esc_html_e( 'Today', 'woocommerce-wholesale-pricing' ); ?></option>
						<option value="1" ><?php esc_html_e( 'Last 7 Days', 'woocommerce-wholesale-pricing' ); ?></option>
						<option value="2" selected=""><?php esc_html_e( 'Last 31 Days', 'woocommerce-wholesale-pricing' ); ?></option>
						<option value="3" ><?php esc_html_e( 'Annual', 'woocommerce-wholesale-pricing' ); ?></option>
						<option value="4" ><?php esc_html_e( 'Custom', 'woocommerce-wholesale-pricing' ); ?></option>
					</select>
					<span class="customreports">
						<input type="date" name="date_start" max="<?php echo esc_attr( gmdate('Y-m-d') ) ; ?>" class="form-control date_start" value="">
						<span style="float: right;padding-right: 11px;padding-top: 8px;"> to </span>
						<input type="date" name="date_end" max="<?php echo esc_attr( gmdate('Y-m-d') ) ; ?>" class="form-control date_end" value="">
					</span>
					<canvas id="myChart" width="400" height="180"></canvas>
				</div>
				</div>
			</div>
			<script>
			myData = { 
					datasets: [
					{
						label: '# B2B',
						data: '',
						fill: true,
							backgroundColor: [
							  'rgba(108, 38, 205, 0.5)',
							  'rgba(108, 38, 205, 0.2)',
							  'rgba(108, 38, 205, 0.2)',
							  'rgba(108, 38, 205, 0.2)',
							  'rgba(108, 38, 205, 0.2)',
							  'rgba(108, 38, 205, 0.2)',
							  'rgba(108, 38, 205, 0.1)'
							],
							borderColor: [
								'rgba(108, 38, 205, 1)',
								'rgba(108, 38, 205, 0.4)',
								'rgba(108, 38, 205, 0.5)',
								'rgba(108, 38, 205, 0.5)',
								'rgba(108, 38, 205, 0.5)',
								'rgba(108, 38, 205, 0.5)',
								'rgba(108, 38, 205, 0.5)'
							],
							borderWidth: 1
					},{
						label: '# B2C',
						data: '',
						fill: true,
							backgroundColor: [
							  'rgba(255, 159, 64, 0.2)',
							  'rgba(255, 159, 64, 0.2)',
							  'rgba(255, 205, 86, 0.2)',
							  'rgba(75, 192, 192, 0.2)',
							  'rgba(54, 162, 235, 0.2)',
							  'rgba(153, 102, 255, 0.2)',
							  'rgba(201, 203, 207, 0.2)'
							],
							borderColor: [
							  'rgb(255, 99, 132)',
							  'rgb(255, 159, 64)',
							  'rgb(255, 205, 86)',
							  'rgb(75, 192, 192)',
							  'rgb(54, 162, 235)',
							  'rgb(153, 102, 255)',
							  'rgb(201, 203, 207)'
							],
						borderWidth: 1
					}
					]
				}
			var ctx = document.getElementById('myChart').getContext('2d');
			var myChart = new Chart(ctx, {
			type: 'line',
			data: myData,
			options: {
					scales: {
						y: {
							beginAtZero: true,
							ticks: {
								callback: function(value, index, values) { return "<?php echo esc_attr( html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 ) ) ; ?>" + value; }
							}
						},
					}
				}
			});

			function call_reports_data( date_start, date_end, date_type ) {
			
				jQuery.ajax({
					type : "POST",
					url : "<?php echo esc_attr( admin_url('admin-ajax.php') ) ; ?>",
					data: { action: "wwp_custom_reports", date_start: date_start, date_end: date_end, date_type: date_type },
					success: function(response) {
						response = jQuery.trim( response );
						response = JSON.parse( response );
						console.log(response);
						myData.labels = [];
						
						if ( date_type == 'today_days' ) {
							myData.datasets[0].data = response.hours_sales_array;
							myData.datasets[1].data = response.hours_sales_b2c_array;
							update_labels = response.hours_sales_array;
						} else {
							myData.datasets[0].data = response.graph_B2B;
							myData.datasets[1].data = response.graph_B2C;
							update_labels = response.graph_B2B;					
						}
						
						myData.labels.push(update_labels.forEach(function(item, index){ return index; }) );
						myData.labels = myData.labels.filter(function(x) {
							return x !== undefined;
						});
						myChart.update();
						jQuery('.total_sale_b2b_amount').html(response.total_wholesale_sales_today_wholesale);
						jQuery('.total_sale_b2c_amount').html(response.total_wholesale_sales_today_retailer);
						jQuery('.wholesale_refund').html(response.total_wholesale_sales_today_days_refunded);
						jQuery('.total_of_customers').html(response.total_of_customers);
						jQuery('.number_wholesale_sales_days').html(response.number_wholesale_sales_days);
						
					}
				});
			} 
			
			call_reports_data('<?php echo esc_attr( gmdate('Y-m-d', strtotime('-30 days')) ) ; ?>', '<?php echo esc_attr( gmdate('Y-m-d') ) ; ?> 23:59:59', 'thirty_one_days');

			function call_reports(selectedValue) { 
				myData.labels = [];
				jQuery('.customreports').hide();
				if ( selectedValue == 0 ) {
					// Today
					date_end = '<?php echo esc_attr(  gmdate('Y-m-d') ); ?> 23:59:59';
					date_start = '<?php echo esc_attr(  gmdate('Y-m-d') ); ?>';
					date_type = 'today_days';
				} else if ( selectedValue == 1 ) {
					// Last 7 Days
					date_end = '<?php echo esc_attr( gmdate('Y-m-d') ); ?>  23:59:59';
					date_start = '<?php echo esc_attr( gmdate('Y-m-d', strtotime('-6 days')) ); ?>';
					date_type = 'seven_days';
				} else if ( selectedValue == 2 ) {
					// Last 31 Days
					date_end = '<?php echo esc_attr( gmdate('Y-m-d') ); ?> 23:59:59';
					date_start = '<?php echo esc_attr( gmdate('Y-m-d', strtotime('-30 days')) ); ?>';
					date_type = 'thirty_one_days';
				} else if ( selectedValue == 3 ) {
					// Annual
					date_end = '<?php echo esc_attr( gmdate('Y-m-d') ); ?> 23:59:59';
					date_start = '<?php echo esc_attr( gmdate('Y-m-d', strtotime('-1 year')) ); ?>';
					date_type = 'annual';
				} else {
					// Custom
					date_start = jQuery('.date_end').val();
					date_end = jQuery('.date_start').val(); 
					date_type = 'custom';
					jQuery('.customreports').show();

					return false;
				}
				call_reports_data(date_start, date_end, date_type);
				 
			}
			
			jQuery('input.form-control.date_end,input.form-control.date_start').on('input',function(e){
			 
				// Custom
				date_start = jQuery('.date_end').val();
				date_end = jQuery('.date_start').val(); 
				date_type = "custom"; 
				
				if ( date_start == '' || date_end == '' ) {
					return false;
				}
				call_reports_data(date_start, date_end, date_type);
			});
			
			</script>
			<?php 
		}
		public function wwp_dashboard_user_requests() {
			$allterms = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
			$roles = wp_roles()->roles;
			$args = array(
				'post_type' => 'wwp_requests',
				'posts_per_page' => 3,
				'post_status' => 'publish',
			);
			
			/**
			* Hooks
			*
			* @since 3.0
			*/
			$args = apply_filters( 'wwp_user_requests_order', $args ); 
			$loop = new WP_Query( $args );
								 
			?>
			<div class="container">
				<div id="wwp_request_order" class="row ">
					<div class="col-md-8 shadow-sm bg-body rounded remove-padding">
						<div class="request_wholesale_order_first ">
							<?php 
							$args = array(
								'post_type'      => 'wwp_requests',
								'post_status'    => 'publish',
								'meta_key'       => '_user_status',
								'meta_value'     => 'waiting',
							);
							$posts_query = new WP_Query( $args );
							$count   = $posts_query->post_count;
							if ( 0 != $count ) { 
								$the_count = '<span class="badge rounded-pill bg-danger"> ' . $count . ' </span>';
							} else {
								$the_count = '<span class="badge rounded-pill bg-danger"> 0 </span>';
							}
							?>
							<h5 class="padding-add"><?php esc_html_e( 'New Registrations Approval Needed', 'woocommerce-wholesale-pricing' ); ?> <?php echo wp_kses_post( $the_count ); ?> </h5>
							<table class="table table-borderless">
								<caption>
									<a class="view_all_order"style="font-size:12px" href="<?php echo esc_url( admin_url( 'edit.php?post_type=wwp_requests' ) ); ?>">
										<?php esc_html_e( 'View All Requests', 'woocommerce-wholesale-pricing' ); ?>
									</a>
								</caption>
								<thead class="table-light">
									<tr>
									  <th scope="col"><?php esc_html_e( 'Name and Email', 'woocommerce-wholesale-pricing' ); ?></th>
									  <th scope="col"><?php esc_html_e( 'User Role', 'woocommerce-wholesale-pricing' ); ?></th>
									  <th scope="col"><?php esc_html_e( 'Status', 'woocommerce-wholesale-pricing' ); ?></th>
									  <th scope="col"><?php esc_html_e( 'Approval', 'woocommerce-wholesale-pricing' ); ?></th>
									</tr>
								</thead>
								<tbody>
								<?php 
								while ( $loop->have_posts() ) : 
									$loop->the_post();
									$user_id = get_post_meta( get_the_ID(), '_user_id', true );
									?>
									<tr class="border-bottom">
										<td class="wwp_img_">
											<div class="d-flex position-relative">
												<img class="img-thumbnail rounded-2 flex-shrink-0 me-3" src="<?php echo esc_url( get_avatar_url( $user_id ) ); ?>" />
												<div class="wwp_reg_content">
													<h5 class="mt-0"> <a href="<?php echo esc_url( admin_url( 'post.php?action=edit&post=' . get_the_ID() ) ); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h5>
													<p>
													<?php 
													$user_id = get_post_meta( get_the_ID(), '_user_id', true );
													if ( ! empty( $user_id ) ) {
														$user_info = get_userdata( $user_id );
														if ( $user_info ) {
															echo esc_html( $user_info->user_email );
														}
													}
													?>
													</p>
												</div>
											</div>
										</td>
										<td style="vertical-align: inherit;">
											<?php 
											
											$user = get_userdata( $user_id );
											if ( isset( $user->roles ) && ! empty( $user->roles ) ) {
												$user_info = $user->roles;
												if ( isset($user_info[0]) && array_key_exists($user_info[0], $roles) ) {
													echo wp_kses_post($roles[$user_info[0]]['name']);
												} else {
													echo esc_html('None');
												}
											}
											?>
										</td>
										<td class="order_status column-order_status" style="vertical-align: inherit;">
										<?php
										$status = get_post_meta(get_the_ID(), '_user_status', true);
										if ('active' == $status) { 
											echo '<mark class="order-status status-processing tips"><span>Approved</span></mark>';
										} elseif ('waiting' == $status) { 
											echo '<mark class="order-status status-on-hold tips"><span>Waiting</span></mark>';
										} elseif ('rejected' == $status) { 
											echo '<mark class="order-status status-failed tips"><span>Rejected</span></mark>';
										}
										?>
										</td>
										<td style="vertical-align: inherit;">
										<?php
										if ( !empty($user_id) ) { 
											$status = get_post_meta(get_the_ID() , '_user_status', true);
											$nonce = wp_create_nonce('request_user_role_nonce');
											?>
											<form action="" style="min-width: 120px;">
												<a href="admin.php?page=wwp_wholesale&post_id=<?php echo esc_attr(get_the_ID()); ?>&user_status=active&_wpnonce=<?php echo esc_attr($nonce); ?>">Approve</a> | 
												<a href="admin.php?page=wwp_wholesale&post_id=<?php echo esc_attr(get_the_ID()); ?>&user_status=rejected&_wpnonce=<?php echo esc_attr($nonce); ?>">Reject</a>
											</form> 
											<?php
										}
										?>
										</td>
									</tr>
									<?php
								endwhile;
								wp_reset_postdata();
								?>
								</tbody>
							</table>
						</div>
					</div>
					<div class="col-md-4">
						<div class="request_wholesale_order alert alert-success total_user shadow-sm bg-body rounded min-height-block">
							<h4 class="alert-heading font-size-alertbox">
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img" aria-label="Warning:">
								<path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"></path>
							</svg>
								<?php esc_html_e( 'Total Wholesale Users', 'woocommerce-wholesale-pricing' ); ?>
							</h4>
							<hr>
							<p class="mb-0">
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
								<path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
							</svg>
							<?php 
							$wholesale_user_ids = array();
							$all_wholesale_role = array();
							$allterms = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
							foreach ( $allterms as $allterm_key => $allterm_value ) { 
								array_push( $all_wholesale_role, $allterm_value->slug );
							}
							$users_count = count( get_users(
								array(
									'role__in' => $all_wholesale_role,
									'fields'   => 'ID',
								)
							) );
							printf( '%s Users', esc_attr( $users_count ) );
							?>
							</p>
						</div>
						<div class="request_wholesale_order alert-warning pending_request shadow-sm bg-body rounded min-height-block">
					
							<h4 class="alert-heading font-size-alertbox">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img" aria-label="Warning:">
								<path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"></path>
								</svg>
								<?php esc_html_e( 'Pending Request', 'woocommerce-wholesale-pricing' ); ?>
							</h4>
							<hr>
							<p class="mb-0">
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
								<path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
							</svg>
							<?php echo wp_kses_post( $count ); ?> <?php esc_html_e( 'Users Pending', 'woocommerce-wholesale-pricing' ); ?>
							</p>
						</div>
						<div class="request_wholesale_order alert-danger rejected_user shadow-sm bg-body rounded min-height-block">
							<h4 class="alert-heading font-size-alertbox">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img" aria-label="Warning:">
									<path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"></path>
								</svg>
								<?php esc_html_e( 'Rejected Request', 'woocommerce-wholesale-pricing' ); ?>
							</h4>
							<hr>
							<p class="mb-0">
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
								<path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
							</svg>
							<?php 
						 
							$args        = array(
								'post_type'      => 'wwp_requests',
								'post_status'    => 'publish',
								'meta_key'       => '_user_status',
								'meta_value'     => 'rejected',
							);
							$posts_query = new WP_Query( $args );
							
							$count   = $posts_query->post_count;
							
							printf( '%s Users Rejected' , esc_attr( $count ) );
							?>
							</p>
						</div>
					</div>	
				</div>
			</div>
			<?php
		}
		public function wwp_dashboard_recent_order() {
			?>
			<div class="container">
				<div id="wwp_recent_order" class="row shadow-sm bg-body rounded remove-padding">
					<div class="col-md-12">
						<div class="recent_wholesale_order">
							<h5 class="padding-add"><?php esc_html_e( 'Recent Wholesale Orders', 'woocommerce-wholesale-pricing' ); ?></h5>
								<table class="table table-borderless">
								<caption><a class="view_all_order"style="font-size:12px" href="<?php echo esc_url( admin_url( 'edit.php?post_type=shop_order' ) ); ?>">View All Order</a></caption>
								  <thead  class="table-light">
									<tr>
									  <th scope="col"><?php esc_html_e( 'Order', 'woocommerce-wholesale-pricing' ); ?></th>
									  <th scope="col"><?php esc_html_e( 'Date', 'woocommerce-wholesale-pricing' ); ?></th>
									  <th scope="col"><?php esc_html_e( 'Status', 'woocommerce-wholesale-pricing' ); ?></th>
									  <th scope="col"><?php esc_html_e( 'Total', 'woocommerce-wholesale-pricing' ); ?></th>
									</tr>
								  </thead>
								  <tbody>
									<?php   
									
									$wholesale_user_ids = array();
									$all_wholesale_role = array();
									$allterms           = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) ); 
									foreach ( $allterms as $allterm_key => $allterm_value ) {
										array_push( $all_wholesale_role, $allterm_value->slug );
									}
									
									$wholesale_user_ids = get_users(
										array(
											'role__in' => $all_wholesale_role,
											'fields'   => 'ID',
										)
									);
									if ( empty( $wholesale_user_ids ) ) {
										$orders = array();
									} else {
										$args = array(
										'post_type' => 'shop_order',
										'posts_per_page' => 5,
										'post_status' => array_keys( wc_get_order_statuses() ),
										'suppress_filters' => true,
										'fields'    => 'ids',
										'meta_query' =>
											array(
												array(
													'key'     => '_customer_user',
													'compare' => 'IN',
													'value'   => $wholesale_user_ids,
												),
											),
										);
										
										/**
										* Hooks
										*
										* @since 3.0
										*/
										$args = apply_filters( 'wwp_recent_wholesale_orders', $args ); 
										$hpos = get_option( 'woocommerce_custom_orders_table_enabled' );
										
										if ( 'yes' == $hpos ) {
											$orders = wc_get_orders(
												array(
													'customer_id' => $wholesale_user_ids,
													'limit' => 5,
													'return' => 'ids',
												)
											);
											
										} else {
											$loop = new WP_Query( $args );
											$orders = $loop->get_posts();
										}

									}
									
									foreach ( $orders as $order ) :
										// $loop->the_post();
										$order = wc_get_order( $order );
										if ( $order->get_billing_first_name() || $order->get_billing_last_name() ) {
											/* translators: 1: first name 2: last name */
											$buyer = trim( sprintf( _x( '%1$s %2$s', 'full name', 'woocommerce' ), $order->get_billing_first_name(), $order->get_billing_last_name() ) );
										} elseif ( $order->get_billing_company() ) {
											$buyer = trim( $order->get_billing_company() );
										} elseif ( $order->get_customer_id() ) {
											$user  = get_user_by( 'id', $order->get_customer_id() );
											$buyer = ucwords( $user->display_name );
										}
										?>
																
										<tr class="border-bottom">
											<th scope="row">
												<h5 class="mt-0">
													<?php echo wp_kses_post( '<a href="' . esc_url( admin_url( 'post.php?post=' . absint( $order->get_id() ) ) . '&action=edit' ) . '" class="order-view"><strong>#' . esc_attr( $order->get_order_number() ) . ' ' . esc_html( $buyer ) . '</strong></a>' ); ?>
												</h5>
											</th>
											<td>
											<?php 
											$order_timestamp = $order->get_date_created() ? $order->get_date_created()->getTimestamp() : '';
											if ( ! $order_timestamp ) {
												echo '&ndash;';
												return;
											}
											// Check if the order was created within the last 24 hours, and not in the future.
											if ( $order_timestamp > strtotime( '-1 day', time() ) && $order_timestamp <= time() ) {
												$show_date = sprintf(
												/* translators: %s: human-readable time difference */
												_x( '%s ago', '%s = human-readable time difference', 'woocommerce' ),
												human_time_diff( $order->get_date_created()->getTimestamp(), time() )
												);
											} else {
											
												/**
												* Hooks
												*
												* @since 3.0
												*/
												$show_date = $order->get_date_created()->date_i18n( apply_filters( 'woocommerce_admin_order_date_format', __( 'M j, Y', 'woocommerce' ) ) );
											}
											printf(
											'<time datetime="%1$s" title="%2$s">%3$s</time>',
											esc_attr( $order->get_date_created()->date( 'c' ) ),
											esc_html( $order->get_date_created()->date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ),
											esc_html( $show_date )
											);
											?>
											</td>
											<td class="order_status column-order_status">
												<mark class="order-status status-<?php echo wp_kses_post( $order->get_status() ); ?> tips"><span><?php echo wp_kses_post( $order->get_status() ); ?></span></mark>
											</td>
											<td>
												<?php echo wp_kses_post( wc_price($order->get_total()) ); ?>
											</td>
										</tr>
										<?php
									endforeach;
									// wp_reset_postdata();
									?>
								</tbody>
								</table>
												
						</div>
					</div>
				</div>
			</div>
			<?php
		}
	}
	new WWP_Wholesale_Reports();
} 
