<?php
if (!defined('ABSPATH')) {
  exit;
}

global $woocommerce;
global $wp_roles;
$user_adjustment_settings = get_option('elex_wccr_checkout_restriction_settings');
$table_show_hide = empty($user_adjustment_settings)?true:false;


$wordpress_roles = $wp_roles->role_names;
$allowed_html = wp_kses_allowed_html('post');
$i = 0;
$decimal_steps = 1;
$woo_decimal = wc_get_price_decimals();
for ($temp = 0; $temp < $woo_decimal; $temp++) {
  $decimal_steps = $decimal_steps / 10;
}
$wordpress_roles['unregistered_user'] = 'Unregistered User';
$user_role_options = '';
foreach ( $wordpress_roles as $k => $v ) {
  $user_role_options .= '<option value="' . $k . '" >' . $v . '</option>';
}
$user_adjustment_settings = get_option('elex_wccr_checkout_restriction_settings');
$this->restriction_table = array();

$users = get_users();
$users_options = '';
foreach ( $users as $key => $user ) {
  $users_options .= '<option value="' . $user->ID . '" >' . $user->data->display_name . '(' . $user->user_email . ') </option>';
}
// category list
$product_category = get_terms(
  'product_cat',
array(
'fields' => 'id=>name',
'hide_empty' => false,
'orderby' => 'title',
'order' => 'ASC',
  )
);

$category_options = '';
foreach ( $product_category as $k => $v ) {
  $category_options .= '<option value="' . $k . '" >' . $v . '</option>';
}
  // product list
$args = array(
'post_type'      => 'product', // Fetch products
'posts_per_page' => -1, // Get all products
);

$products_query = new WC_Product_Query($args);
$products = $products_query->get_products();
  
$products_options = '';
foreach ( $products as $product ) {
  $products_options .= '<option value="' . $product->get_id() . '" >' . $product->get_name() . '</option>';
}

?>
<div class="elex-min-order-wrap">
  <div class="elex-min-order-main">
	<div class=" bg-white p-3 " style="min-height: 100vh">
	<?php
	if (empty($user_adjustment_settings)) {
		?>
		<div class="rounded bg-white shadow overflow-hidden h-100 min_order_rule_create_div">
		  <div class="row py-3 h-100 gap-5 justify-content-center flex-column align-items-center" style="min-height:500px;">
			<div class="col-4">
			  <img src="<?php echo esc_url(MINIMUM_ORDER_MAIN_PATH . 'assests/img/add_new_rule.png'); ?>" class="w-100">
			</div>

			<button type="button" class="btn min-order-btn min_order_create_rule_btn  btn-primary d-flex gap-2 align-items-center w-auto">
			  <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M17.5938 9C17.5938 9.2735 17.4851 9.53581 17.2917 9.7292C17.0983 9.9226 16.836 10.0312 16.5625 10.0312H10.0312V16.5625C10.0312 16.836 9.9226 17.0983 9.7292 17.2917C9.53581 17.4851 9.2735 17.5938 9 17.5938C8.7265 17.5938 8.46419 17.4851 8.2708 17.2917C8.0774 17.0983 7.96875 16.836 7.96875 16.5625V10.0312H1.4375C1.164 10.0312 0.901693 9.9226 0.708296 9.7292C0.514899 9.53581 0.40625 9.2735 0.40625 9C0.40625 8.7265 0.514899 8.46419 0.708296 8.2708C0.901693 8.0774 1.164 7.96875 1.4375 7.96875H7.96875V1.4375C7.96875 1.164 8.0774 0.901693 8.2708 0.708296C8.46419 0.514899 8.7265 0.40625 9 0.40625C9.2735 0.40625 9.53581 0.514899 9.7292 0.708296C9.9226 0.901693 10.0312 1.164 10.0312 1.4375V7.96875H16.5625C16.836 7.96875 17.0983 8.0774 17.2917 8.2708C17.4851 8.46419 17.5938 8.7265 17.5938 9Z" fill="white" />
			  </svg>
			  <span>Create New</span>
			</button>
		  </div>

		</div>
	  <?php
	} 
	?>
		<div class="min_order_rule_table_container">
			<div class="text-dark mb-3"><small>Please specify a Minimum or Maximum amount and a Alert Message to activate the rule. The remaining fields are optional. "And condition" is applied to Optional fields.</small></div>
			
			<div class="rounded bg-white shadow overflow-hidden position-relative">
				<div id="min-order-table-loader" style="display:none">
					<div  class="bg-dark position-absolute w-100 h-100 d-flex align-items-center justify-content-center bg-opacity-25" style="z-index:99; ">
						<img src="<?php echo esc_url( MINIMUM_ORDER_MAIN_PATH . 'assests/img/loader.gif' ); ?>" style="width:100px" >
					</div>
				</div>
				<div class="table-responsive">
				
				<table class="table table-borderless elex-min-order-rules-table" id="elex_wccr_checkout_restriction_settings">
				<thead>
					<tr>
					<th class="elex-min-order-rules-table-drag"></th>
					<th class="elex-min-order-rules-table-user-role"><?php esc_html_e('User Role', 'elex-wc-checkout-restriction'); ?></th>
					<th class="elex-min-order-rules-table-user"><?php esc_html_e('User', 'elex-wc-checkout-restriction'); ?></th>
					<th class="elex-min-order-rules-table-categories"><?php esc_html_e('Product Categories', 'elex-wc-checkout-restriction'); ?></th>
					<th class="elex-min-order-rules-table-product"><?php esc_html_e('Product', 'elex-wc-checkout-restriction'); ?></th>
					<th class="text-nowrap elex-min-order-rules-table-min-amt"><?php echo esc_html__('Minimum Amount ', 'elex-wc-checkout-restriction') . ' ( ' . esc_html(get_woocommerce_currency_symbol()) . ' )'; ?></th>
					<th class="text-nowrap elex-min-order-rules-table-max-amt"><?php echo esc_html__('Maximum Amount ', 'elex-wc-checkout-restriction') . ' ( ' . esc_html(get_woocommerce_currency_symbol()) . ' )'; ?></th>
					<th class="text-nowrap elex-min-order-rules-table-warning"><?php esc_html_e('Alert', 'elex-wc-checkout-restriction'); ?></th>
					<th class="text-nowrap elex-min-order-rules-table-enable"><?php esc_html_e('Enable', 'elex-wc-checkout-restriction'); ?></th>
					<th class="elex-min-order-rules-table-remove"><?php esc_html_e('Remove', 'elex-wc-checkout-restriction'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					global $woocommerce;
					$allowed_html = array( 'option' => array( 'value' => array() ) );

					$index = 0;
					if (!empty($user_adjustment_settings)) {
					
						foreach ( $user_adjustment_settings as $key => $value ) {
							?>
					<tr>
						<td class=" elex-min-order-rules-table-drag">
						<input type="hidden" class="order" name="elex_wccr_checkout_restriction_settings[<?php echo esc_html( $index ); ?>]"  />
						<svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path fill-rule="evenodd" clip-rule="evenodd" d="M7.52067 3.95833C7.52067 4.37826 7.35386 4.78099 7.05692 5.07792C6.75999 5.37485 6.35726 5.54167 5.93734 5.54167C5.51741 5.54167 5.11468 5.37485 4.81775 5.07792C4.52082 4.78099 4.354 4.37826 4.354 3.95833C4.354 3.53841 4.52082 3.13568 4.81775 2.83875C5.11468 2.54181 5.51741 2.375 5.93734 2.375C6.35726 2.375 6.75999 2.54181 7.05692 2.83875C7.35386 3.13568 7.52067 3.53841 7.52067 3.95833ZM5.93734 11.0833C6.35726 11.0833 6.75999 10.9165 7.05692 10.6196C7.35386 10.3227 7.52067 9.91993 7.52067 9.5C7.52067 9.08007 7.35386 8.67735 7.05692 8.38041C6.75999 8.08348 6.35726 7.91667 5.93734 7.91667C5.51741 7.91667 5.11468 8.08348 4.81775 8.38041C4.52082 8.67735 4.354 9.08007 4.354 9.5C4.354 9.91993 4.52082 10.3227 4.81775 10.6196C5.11468 10.9165 5.51741 11.0833 5.93734 11.0833ZM5.93734 16.625C6.35726 16.625 6.75999 16.4582 7.05692 16.1613C7.35386 15.8643 7.52067 15.4616 7.52067 15.0417C7.52067 14.6217 7.35386 14.219 7.05692 13.9221C6.75999 13.6251 6.35726 13.4583 5.93734 13.4583C5.51741 13.4583 5.11468 13.6251 4.81775 13.9221C4.52082 14.219 4.354 14.6217 4.354 15.0417C4.354 15.4616 4.52082 15.8643 4.81775 16.1613C5.11468 16.4582 5.51741 16.625 5.93734 16.625ZM14.6457 3.95833C14.6457 4.37826 14.4789 4.78099 14.1819 5.07792C13.885 5.37485 13.4823 5.54167 13.0623 5.54167C12.6424 5.54167 12.2397 5.37485 11.9428 5.07792C11.6458 4.78099 11.479 4.37826 11.479 3.95833C11.479 3.53841 11.6458 3.13568 11.9428 2.83875C12.2397 2.54181 12.6424 2.375 13.0623 2.375C13.4823 2.375 13.885 2.54181 14.1819 2.83875C14.4789 3.13568 14.6457 3.53841 14.6457 3.95833ZM13.0623 11.0833C13.4823 11.0833 13.885 10.9165 14.1819 10.6196C14.4789 10.3227 14.6457 9.91993 14.6457 9.5C14.6457 9.08007 14.4789 8.67735 14.1819 8.38041C13.885 8.08348 13.4823 7.91667 13.0623 7.91667C12.6424 7.91667 12.2397 8.08348 11.9428 8.38041C11.6458 8.67735 11.479 9.08007 11.479 9.5C11.479 9.91993 11.6458 10.3227 11.9428 10.6196C12.2397 10.9165 12.6424 11.0833 13.0623 11.0833ZM13.0623 16.625C13.4823 16.625 13.885 16.4582 14.1819 16.1613C14.4789 15.8643 14.6457 15.4616 14.6457 15.0417C14.6457 14.6217 14.4789 14.219 14.1819 13.9221C13.885 13.6251 13.4823 13.4583 13.0623 13.4583C12.6424 13.4583 12.2397 13.6251 11.9428 13.9221C11.6458 14.219 11.479 14.6217 11.479 15.0417C11.479 15.4616 11.6458 15.8643 11.9428 16.1613C12.2397 16.4582 12.6424 16.625 13.0623 16.625Z" fill="white" />
						</svg>

						</td>
						<td class="elex-min-order-rules-table-user-role">
						<select  id="" multiple class="form-select min-order-rule-select " name="elex_wccr_checkout_restriction_settings[<?php echo esc_html( $index ); ?>][roles][]">
						<?php
							foreach ( $wordpress_roles as $role_id => $role_name ) {
								if ( isset( $value['roles'] ) && is_array( $value['roles'] ) && in_array( $role_id, $value['roles'] ) ) {
								echo '<option value="' . esc_html( $role_id ) . '" selected >' . esc_html( $role_name ) . '</option>';
								} else {
								echo '<option value="' . esc_html( $role_id ) . '" >' . esc_html( $role_name ) . '</option>';
								}
							}
							?>
						</select>
						
						</td>
						<td class="elex-min-order-rules-table-user">
						<select  id="" multiple class="form-select min-order-rule-select " name="elex_wccr_checkout_restriction_settings[<?php echo esc_html( $index ); ?>][users][]">
							<?php
							$user_ids = isset( $value['users'] ) ? $value['users'] : array();
							foreach ( $users as $key => $user ) {
								if (in_array($user->ID , $user_ids)) {
								echo '<option value="' . esc_html( $user->ID ) . '" selected >' . esc_html( $user->data->display_name ) . '(' . esc_html( $user->user_email ) . ') </option>';
								} else {
								echo '<option value="' . esc_html( $user->ID ) . '" >' . esc_html( $user->data->display_name ) . '(' . esc_html( $user->user_email ) . ') </option>';
								}
							}
							
							?>
						</select>
						</td>
						<td class="elex-min-order-rules-table-categories">
						<select  id="" multiple class="form-select min-order-rule-select " name="elex_wccr_checkout_restriction_settings[<?php echo esc_html( $index ); ?>][category][]">
							<?php
							foreach ( $product_category as $product_category_id => $product_category_one ) {
								if ( isset( $value['category'] ) && is_array( $value['category'] ) && in_array( $product_category_id, $value['category'] ) ) {
									echo '<option value="' . esc_html( $product_category_id ) . '" selected >' . esc_html( $product_category_one ) . '</option>';
								} else {
									echo '<option value="' . esc_html( $product_category_id ) . '" >' . esc_html( $product_category_one ) . '</option>';
								}
							}
							?>
						</select>
						</td>
						<td class="elex-min-order-rules-table-product">
						<select  id="" multiple class="form-select min-order-rule-select " name="elex_wccr_checkout_restriction_settings[<?php echo esc_html( $index ); ?>][product][]">
							<?php
							if ($products) {
							
								foreach ($products as $product) {
									if ( isset( $value['product'] ) && is_array( $value['product'] ) && in_array( $product->get_id(), $value['product'] ) ) {
										echo '<option value="' . esc_html( $product->get_id() ) . '" selected >' . esc_html( $product->get_name() ) . '</option>';
									} else {
										echo '<option value="' . esc_html( $product->get_id() ) . '" >' . esc_html( $product->get_name() ) . '</option>';
									}
								
								}
							} else {
								echo '<option value="">No products found</option>';
							}
							?>
						</select>
						</td>
						<td class="elex-min-order-rules-table-min-amt">
						<input type="number" class="form-control" name="elex_wccr_checkout_restriction_settings[<?php echo esc_html( $index ); ?>][min_price]" value="<?php echo isset( $value['min_price'] ) ? esc_html( $value['min_price'] ) : ''; ?>">
						</td>
						<td class="elex-min-order-rules-table-max-amt">
						<input type="number" class="form-control" name="elex_wccr_checkout_restriction_settings[<?php echo esc_html( $index ); ?>][max_price]" value="<?php echo isset( $value['max_price'] ) ? esc_html( $value['max_price'] ) : ''; ?>">
						</td>
						<td class="elex-min-order-rules-table-warning">
						<input type="text" class="form-control" name="elex_wccr_checkout_restriction_settings[<?php echo esc_html( $index ); ?>][error_message]" value="<?php echo isset( $value['error_message'] ) ? esc_html( $value['error_message'] ) : ''; ?>">
						</td>
						<td class="elex-min-order-rules-table-enable">
						
							<?php
							$checked = ( ! empty( $value['enable_restriction'] ) ) ? true : false;
						
							?>
						
						<label class="elex-switch-btn">
							<input onchange="" type="checkbox" name="elex_wccr_checkout_restriction_settings[<?php echo esc_html( $index ); ?>][enable_restriction]" <?php checked( $checked, true ); ?>>
							<div class="elex-switch-icon round"></div>
						</label>
						</td>
						<td class="elex-min-order-rules-table-remove">
						<button class="elex_min_order_remove_btn btn rounded-circle p-0" data-bs-custom-class="tooltip-outline-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete">
							<svg width="34" height="34" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M12.2499 25.6667C11.7516 25.6667 11.3356 25.4999 11.0019 25.1662C10.6675 24.8318 10.5003 24.4154 10.5003 23.9171V10.5H9.41699V9.41669H13.7503V8.58252H20.2503V9.41669H24.5837V10.5H23.5003V23.9171C23.5003 24.4154 23.3335 24.8314 22.9998 25.1651C22.6654 25.4995 22.2491 25.6667 21.7507 25.6667H12.2499ZM14.6257 22.4167H15.709V12.6667H14.6257V22.4167ZM18.2917 22.4167H19.375V12.6667H18.2917V22.4167Z" fill="black" />
							</svg>

						</button>
						</td>
					</tr>

						<?php
						$index++;
						}   
					}
					
					?>
					<input type="hidden" class="elex_min_order_tabel_next_index"  value="<?php echo esc_html( $index ); ?>" />


				</tbody>
				</table>
			</div>

			<div class="p-3">
				<div class="d-flex justify-content-between align-items-baseline">
				<button id="elex_min_order_add_rule" type="button" class="btn min-order-btn  btn-primary d-flex gap-2 align-items-center">
					<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M17.5938 9C17.5938 9.2735 17.4851 9.53581 17.2917 9.7292C17.0983 9.9226 16.836 10.0312 16.5625 10.0312H10.0312V16.5625C10.0312 16.836 9.9226 17.0983 9.7292 17.2917C9.53581 17.4851 9.2735 17.5938 9 17.5938C8.7265 17.5938 8.46419 17.4851 8.2708 17.2917C8.0774 17.0983 7.96875 16.836 7.96875 16.5625V10.0312H1.4375C1.164 10.0312 0.901693 9.9226 0.708296 9.7292C0.514899 9.53581 0.40625 9.2735 0.40625 9C0.40625 8.7265 0.514899 8.46419 0.708296 8.2708C0.901693 8.0774 1.164 7.96875 1.4375 7.96875H7.96875V1.4375C7.96875 1.164 8.0774 0.901693 8.2708 0.708296C8.46419 0.514899 8.7265 0.40625 9 0.40625C9.2735 0.40625 9.53581 0.514899 9.7292 0.708296C9.9226 0.901693 10.0312 1.164 10.0312 1.4375V7.96875H16.5625C16.836 7.96875 17.0983 8.0774 17.2917 8.2708C17.4851 8.46419 17.5938 8.7265 17.5938 9Z" fill="white" />
					</svg>
					<span>Add</span>
				</button>
				<button name="save" value="save" class="btn min-order-btn btn-primary woocommerce-save-button d-block" type="submit">Save</button>
				</div>
			</div>
			</div>

		</div>


	</div>
  </div>

</div>


<script>
  jQuery(document).ready(function() {
	var stored_rule = <?php echo $table_show_hide ? 'true' : 'false'; ?>;
	if(stored_rule){
	  jQuery(".min_order_rule_table_container").hide();
	}else{
	  jQuery(".min_order_rule_table_container").show();
	}

	jQuery('tbody').sortable({
	  placeholder: "ui-widget-shadow",
	  handle: 'td.elex-min-order-rules-table-drag',
	  update: function() {}
	});


	// Function to initialize Select2 with custom class
	function initializeSelect2() {
		jQuery('.min-order-rule-select').select2({
			minimumResultsForSearch: -1,
			showArrow:true,
			dropdownCssClass: "elex-min-order-wrap",
		});
	}
	initializeSelect2();
  
	jQuery(".elex-min-order-rules-table").on('click', '.elex_min_order_remove_btn', function (e) {
		e.preventDefault();
		var table = jQuery(this).closest('.elex-min-order-rules-table');
		var rowCount = table.find('tbody tr').length;
		if (rowCount === 1) {
		jQuery(this).closest("tr").find('input').val('');
		jQuery(this).closest("tr").find('select option:selected').removeAttr("selected");
		}else{
		jQuery(this).closest("tr").remove().find('input','select').val('');
		}
	});

	jQuery('#elex_min_order_add_rule').click( function(e) {
		e.preventDefault();
		var tbody = jQuery('.elex_wccr_checkout_restriction_settings').find('tbody');
		var size = jQuery('.elex_min_order_tabel_next_index').val();

		var user_roles = '<?php echo wp_kses( $user_role_options, $allowed_html ); ?>';
		var categories = '<?php echo wp_kses( addcslashes( $category_options, "'" ), $allowed_html ); ?>';
		var products = '<?php echo wp_kses( addcslashes( $products_options, "'" ), $allowed_html ); ?>';
		var users = '<?php echo wp_kses( addcslashes( $users_options, "'" ), $allowed_html ); ?>';
  
		var decimal_steps = '<?php echo esc_html( $decimal_steps ); ?>';
		var currency_symbol = '<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?>';
		var code = `<tr >
		  <td class=" elex-min-order-rules-table-drag">
			<input type="hidden" class="order" name="elex_wccr_checkout_restriction_settings[`+size+`]"  />
			<svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
			  <path fill-rule="evenodd" clip-rule="evenodd" d="M7.52067 3.95833C7.52067 4.37826 7.35386 4.78099 7.05692 5.07792C6.75999 5.37485 6.35726 5.54167 5.93734 5.54167C5.51741 5.54167 5.11468 5.37485 4.81775 5.07792C4.52082 4.78099 4.354 4.37826 4.354 3.95833C4.354 3.53841 4.52082 3.13568 4.81775 2.83875C5.11468 2.54181 5.51741 2.375 5.93734 2.375C6.35726 2.375 6.75999 2.54181 7.05692 2.83875C7.35386 3.13568 7.52067 3.53841 7.52067 3.95833ZM5.93734 11.0833C6.35726 11.0833 6.75999 10.9165 7.05692 10.6196C7.35386 10.3227 7.52067 9.91993 7.52067 9.5C7.52067 9.08007 7.35386 8.67735 7.05692 8.38041C6.75999 8.08348 6.35726 7.91667 5.93734 7.91667C5.51741 7.91667 5.11468 8.08348 4.81775 8.38041C4.52082 8.67735 4.354 9.08007 4.354 9.5C4.354 9.91993 4.52082 10.3227 4.81775 10.6196C5.11468 10.9165 5.51741 11.0833 5.93734 11.0833ZM5.93734 16.625C6.35726 16.625 6.75999 16.4582 7.05692 16.1613C7.35386 15.8643 7.52067 15.4616 7.52067 15.0417C7.52067 14.6217 7.35386 14.219 7.05692 13.9221C6.75999 13.6251 6.35726 13.4583 5.93734 13.4583C5.51741 13.4583 5.11468 13.6251 4.81775 13.9221C4.52082 14.219 4.354 14.6217 4.354 15.0417C4.354 15.4616 4.52082 15.8643 4.81775 16.1613C5.11468 16.4582 5.51741 16.625 5.93734 16.625ZM14.6457 3.95833C14.6457 4.37826 14.4789 4.78099 14.1819 5.07792C13.885 5.37485 13.4823 5.54167 13.0623 5.54167C12.6424 5.54167 12.2397 5.37485 11.9428 5.07792C11.6458 4.78099 11.479 4.37826 11.479 3.95833C11.479 3.53841 11.6458 3.13568 11.9428 2.83875C12.2397 2.54181 12.6424 2.375 13.0623 2.375C13.4823 2.375 13.885 2.54181 14.1819 2.83875C14.4789 3.13568 14.6457 3.53841 14.6457 3.95833ZM13.0623 11.0833C13.4823 11.0833 13.885 10.9165 14.1819 10.6196C14.4789 10.3227 14.6457 9.91993 14.6457 9.5C14.6457 9.08007 14.4789 8.67735 14.1819 8.38041C13.885 8.08348 13.4823 7.91667 13.0623 7.91667C12.6424 7.91667 12.2397 8.08348 11.9428 8.38041C11.6458 8.67735 11.479 9.08007 11.479 9.5C11.479 9.91993 11.6458 10.3227 11.9428 10.6196C12.2397 10.9165 12.6424 11.0833 13.0623 11.0833ZM13.0623 16.625C13.4823 16.625 13.885 16.4582 14.1819 16.1613C14.4789 15.8643 14.6457 15.4616 14.6457 15.0417C14.6457 14.6217 14.4789 14.219 14.1819 13.9221C13.885 13.6251 13.4823 13.4583 13.0623 13.4583C12.6424 13.4583 12.2397 13.6251 11.9428 13.9221C11.6458 14.219 11.479 14.6217 11.479 15.0417C11.479 15.4616 11.6458 15.8643 11.9428 16.1613C12.2397 16.4582 12.6424 16.625 13.0623 16.625Z" fill="white" />
			</svg>

		  </td>
					<td class="elex-min-order-rules-table-user-role"><select id="roles_field"  data-placeholder="N/A" class="form-select min-order-rule-select " name="elex_wccr_checkout_restriction_settings[`+size+`][roles][]"  multiple="multiple">`+ user_roles + `</select></td>
					<td class="elex-min-order-rules-table-user"><select   class="form-select min-order-rule-select " name="elex_wccr_checkout_restriction_settings[`+size+`][users][]"  multiple="multiple" >` + users + `</select></td>
					<td class="elex-min-order-rules-table-categories"><select   class="form-select min-order-rule-select  " name="elex_wccr_checkout_restriction_settings[`+size+`][category][]"  multiple="multiple">` + categories + `</select></td>
					<td class="elex-min-order-rules-table-product"><select id="" multiple="" class="form-select min-order-rule-select  " name="elex_wccr_checkout_restriction_settings[`+size+`][product][]" tabindex="-1" aria-hidden="true">` + products + `</select></td>
					 
					<td class="elex-min-order-rules-table-min-amt"><input type="number" class="form-control"  min="0"  name="elex_wccr_checkout_restriction_settings[`+size+`][min_price]"   /> </td>
					<td class="elex-min-order-rules-table-max-amt"><input type="number"  class="form-control" min="0"  name="elex_wccr_checkout_restriction_settings[`+size+`][max_price]" /></td>
					<td class="elex-min-order-rules-table-warning"><input type="text" class="form-control" name="elex_wccr_checkout_restriction_settings[`+size+`][error_message]" value=""></td>
					<td class="elex-min-order-rules-table-enable">                              
			<label class="elex-switch-btn">
			  <input onchange="" type="checkbox" name="elex_wccr_checkout_restriction_settings[`+size+`][enable_restriction]" checked="checked">
			  <div class="elex-switch-icon round"></div>
			</label>
		  </td>
		  <td class="elex-min-order-rules-table-remove">
					  <button type="botton" class="elex_min_order_remove_btn btn rounded-circle p-0" data-bs-custom-class="tooltip-outline-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="" data-bs-original-title="Delete">
						<svg width="34" height="34" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg">
						  <path d="M12.2499 25.6667C11.7516 25.6667 11.3356 25.4999 11.0019 25.1662C10.6675 24.8318 10.5003 24.4154 10.5003 23.9171V10.5H9.41699V9.41669H13.7503V8.58252H20.2503V9.41669H24.5837V10.5H23.5003V23.9171C23.5003 24.4154 23.3335 24.8314 22.9998 25.1651C22.6654 25.4995 22.2491 25.6667 21.7507 25.6667H12.2499ZM14.6257 22.4167H15.709V12.6667H14.6257V22.4167ZM18.2917 22.4167H19.375V12.6667H18.2917V22.4167Z" fill="black"></path>
						</svg>

					  </button>
					</td>`;
					
					jQuery('#elex_wccr_checkout_restriction_settings tbody').append( code );
					jQuery('#roles_field').trigger('wc-enhanced-select-init');
					size++;
					jQuery('.elex_min_order_tabel_next_index').val(size);
					initializeSelect2();
					return false;
	
				});

			});

</script>
