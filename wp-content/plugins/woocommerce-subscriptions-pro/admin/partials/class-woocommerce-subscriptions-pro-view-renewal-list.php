<?php
/**
 * Exit if accessed directly
 *
 * @since      1.0.0
 * @package    Woocommerce_Subscriptions_Pro
 * @subpackage Woocommerce_Subscriptions_Pro/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * This is construct of class where all susbcriptions listed.
 *
 * @name Woocommerce_Subscriptions_Pro_View_Renewal_List
 * @since      1.0.0
 * @category Class
 * @author WP Swings<ticket@wpswings.com>
 * @link https://www.wpswings.com/
 */
class Woocommerce_Subscriptions_Pro_View_Renewal_List extends WP_List_Table {
	/**
	 * This is variable which is used for the store all the data.
	 *
	 * @var array $example_data variable for store data.
	 */
	public $example_data;

	/**
	 * This is variable which is used for the total count.
	 *
	 * @var array $wps_total_count variable for total count.
	 */
	public $wps_total_count;


	/**
	 * This construct colomns in susbcription table.
	 *
	 * @name get_columns.
	 * @since      1.0.0
	 * @author WP Swings<ticket@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function get_columns() {

		$columns = array(
			'cb'             => '<input type="checkbox" />',
			'order_id'       => __( 'Order ID', 'woocommerce-subscriptions-pro' ),
			'status'         => __( 'Status', 'woocommerce-subscriptions-pro' ),
			'date'           => __( 'Date', 'woocommerce-subscriptions-pro' ),
			'order_total'    => __( 'Order Total', 'woocommerce-subscriptions-pro' ),
			'retry_attempts' => __( 'Retry Failed Attempts', 'woocommerce-subscriptions-pro' ),
		);
		return $columns;
	}

	/**
	 * This show susbcriptions table list.
	 *
	 * @name column_default.
	 * @since      1.0.0
	 * @author WP Swings<ticket@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param array  $item  array of the items.
	 * @param string $column_name name of the colmn.
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {

			case 'order_id':
				if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
					$html = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-orders&action=edit&id=' . $item[ $column_name ] ) ) . '">' . $item[ $column_name ] . '</a>';
				} else {
					$html = '<a href="' . esc_url( get_edit_post_link( $item[ $column_name ] ) ) . '">' . $item[ $column_name ] . '</a>';
				}
				return $html;
			case 'status':
				return $item[ $column_name ];
			case 'date':
				return $item[ $column_name ];
			case 'order_total':
				return $item[ $column_name ];
			case 'retry_attempts':
				$failed_attempts = wps_wsp_get_meta_data( $item['order_id'], 'wps_wsp_no_of_retry_attempt', true );
				return $failed_attempts ? $failed_attempts : '---';
			default:
				return false;
		}
	}

	/**
	 * Perform admin bulk action setting for susbcription table.
	 *
	 * @name process_bulk_action.
	 * @author WP Swings<ticket@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function process_bulk_action() {

		if ( 'bulk-delete' === $this->current_action() ) {

			if ( isset( $_POST['wsp_susbcription_list_table'] ) ) {
				$wsp_susbcription_list_table = sanitize_text_field( wp_unslash( $_POST['wsp_susbcription_list_table'] ) );
				if ( wp_verify_nonce( $wsp_susbcription_list_table, 'wsp_susbcription_list_table' ) ) {
					if ( isset( $_POST['wps_wsp_order_ids'] ) && ! empty( $_POST['wps_wsp_order_ids'] ) ) {
						$all_id = map_deep( wp_unslash( $_POST['wps_wsp_order_ids'] ), 'sanitize_text_field' );
						$wps_subscription_id = isset( $_GET['wps_subscription_id'] ) ? sanitize_text_field( wp_unslash( $_GET['wps_subscription_id'] ) ) : '';

						$wps_renewal_order_data = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_wsp_renewal_order_data', true );

						foreach ( $all_id as $key => $value ) {
							if ( in_array( $value, $wps_renewal_order_data ) ) {
								$delet_key = array_search( $value, $wps_renewal_order_data );
								if ( $delet_key ) {
									unset( $wps_renewal_order_data[ $delet_key ] );
									$wps_renewal_order_data = array_values( $wps_renewal_order_data );
									wps_wsp_update_meta_data( $wps_subscription_id, 'wps_wsp_renewal_order_data', $wps_renewal_order_data );
								}
							}
							wp_delete_post( $value, true );
						}

						?>
						<div class="notice notice-success is-dismissible"> 
							<p><strong><?php esc_html_e( 'Order Deleted Successfully', 'woocommerce-subscriptions-pro' ); ?></strong></p>
						</div>
						<?php
					}
				}
			}
		}
	}
	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @name process_bulk_action.
	 * @since      1.0.0
	 * @return array
	 * @author WP Swings<ticket@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => __( 'Delete', 'woocommerce-subscriptions-pro' ),
		);
		return apply_filters( 'wps_wsp_bulk_option', $actions );
	}

	/**
	 * Returns an associative array containing the bulk action for sorting.
	 *
	 * @name get_sortable_columns.
	 * @since      1.0.0
	 * @return array
	 * @author WP Swings<ticket@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'order_id'   => array( 'order_id', false ),
		);
		return $sortable_columns;
	}

	/**
	 * Prepare items for sorting.
	 *
	 * @name prepare_items.
	 * @since      1.0.0
	 * @author WP Swings<ticket@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function prepare_items() {
		$per_page              = 10;
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->process_bulk_action();
		$current_page = $this->get_pagenum();

		$this->example_data = $this->wps_wsp_get_subscription_list();
		$data               = $this->example_data;
		usort( $data, array( $this, 'wps_wsp_usort_reorder' ) );
		$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );
		$total_items = $this->wps_total_count;
		$this->items  = $data;
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}



	/**
	 * Return sorted associative array.
	 *
	 * @name wps_wsp_usort_reorder.
	 * @since      1.0.0
	 * @return array
	 * @author WP Swings<ticket@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param array $cloumna column of the susbcriptions.
	 * @param array $cloumnb column of the susbcriptions.
	 */
	public function wps_wsp_usort_reorder( $cloumna, $cloumnb ) {

		$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'order_id';
		$order   = ( ! empty( $_REQUEST['order'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'desc';

		if ( is_numeric( $cloumna[ $orderby ] ) && is_numeric( $cloumnb[ $orderby ] ) ) {
			if ( $cloumna[ $orderby ] == $cloumnb[ $orderby ] ) {
				return 0;
			} elseif ( $cloumna[ $orderby ] < $cloumnb[ $orderby ] ) {
				$result = -1;
				return ( 'asc' === $order ) ? $result : -$result;
			} elseif ( $cloumna[ $orderby ] > $cloumnb[ $orderby ] ) {
				$result = 1;
				return ( 'asc' === $order ) ? $result : -$result;
			}
		} else {
			$result = strcmp( $cloumna[ $orderby ], $cloumnb[ $orderby ] );
			return ( 'asc' === $order ) ? $result : -$result;
		}
	}

	/**
	 * THis function is used for the add the checkbox.
	 *
	 * @name column_cb.
	 * @since      1.0.0
	 * @return array
	 * @author WP Swings<ticket@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param array $item array of the items.
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="wps_wsp_order_ids[]" value="%s" />',
			$item['order_id']
		);
	}


	/**
	 * This function used to get all susbcriptions list.
	 *
	 * @name wps_wsp_get_subscription_list.
	 * @since      1.0.0
	 * @return array
	 * @author WP Swings<ticket@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_wsp_get_subscription_list() {
		$wps_subscriptions_data = array();
		$total_count = 0;
		if ( isset( $_GET['wps_subscription_id'] ) && ! empty( $_GET['wps_subscription_id'] ) ) {

			$wps_subscription_id = isset( $_GET['wps_subscription_id'] ) ? sanitize_text_field( wp_unslash( $_GET['wps_subscription_id'] ) ) : '';

			$wps_renewal_order_data = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_wsp_renewal_order_data', true );

			if ( isset( $wps_renewal_order_data ) && ! empty( $wps_renewal_order_data ) && is_array( $wps_renewal_order_data ) ) {

				/*search*/
				if ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) {
					$data           = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) );

					if ( in_array( $data, $wps_renewal_order_data ) ) {
						$wps_renewal_order_data = array( $data );
					} else {
						$wps_renewal_order_data = array();
					}
				}

				$total_count = count( $wps_renewal_order_data );
				foreach ( $wps_renewal_order_data as $key => $order_id ) {
					$order = wc_get_order( $order_id );
					if ( $order ) {
						$order_timestamp = $order->get_date_created() ? $order->get_date_created()->getTimestamp() : '';
						$order_total = $order->get_formatted_order_total();
						$order_status = $order->get_status();
						$wps_subscriptions_data[] = array(
							'order_id'           => $order_id,
							'status'             => $order_status,
							'date'              => wps_sfw_get_the_wordpress_date_format( $order_timestamp ),
							'order_total'          => $order_total,
						);
					}
				}
			}
		}
		$this->wps_total_count = $total_count;
		return $wps_subscriptions_data;
	}
}

?>
	<h3 class="wp-heading-inline" id="wps_wsp_heading"><?php esc_html_e( 'Subscriptions Renewal Order', 'woocommerce-subscriptions-pro' ); ?></h3>
		<form method="post">
		<input type="hidden" name="page" value="<?php esc_html_e( 'wsp_susbcription_list_table', 'woocommerce-subscriptions-pro' ); ?>">
		<?php wp_nonce_field( 'wsp_susbcription_list_table', 'wsp_susbcription_list_table' ); ?>
		<div class="wps_wsp_list_table">
			<?php
			$mylisttable = new Woocommerce_Subscriptions_Pro_View_Renewal_List();
			$mylisttable->prepare_items();
			$mylisttable->search_box( __( 'Search Order', 'woocommerce-subscriptions-pro' ), 'wps-sfw-order' );
			$mylisttable->display();
			?>
		</div>
	</form>
	<a  href="<?php echo esc_url( admin_url( 'admin.php?page=subscriptions_for_woocommerce_menu&sfw_tab=subscriptions-for-woocommerce-subscriptions-table' ) ); ?>" style="line-height: 2" class="button button-primary wps_wsp_go_back"><?php esc_html_e( 'Go Back', 'woocommerce-subscriptions-pro' ); ?></a> 
	<?php


