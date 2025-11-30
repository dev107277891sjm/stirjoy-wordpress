<?php 
	require_once WWP_PLUGIN_PATH . 'inc/class-wwp-wholesale-multiuser.php';
	$post_data   = get_post( wwp_get_post_data('post_id') );
	$requisition = get_post_meta(  $post_data->ID , 'requisition', true );
?>
	<div class="wwp_requisition_list_new">
		<form id="form_requisition_list" action="#" method="post">
			<table class="wwp_requisition_list_table" >
				<tr>
				  <th class="wwp_search_by_header"> 
					Search by 
					<select name="wwp_search_by_type" id="wwp_search_by_type" class="selectWoo">
						<option value="productname">Product Name</option>
						<option value="sku">Sku</option>
					</select>
				  </th>
				  <th class="wwp_table_qty_header">Qty</th>
				  <th class="wwp_table_subtotal_header" scope="col">Subtotal<span class="dashicons tooltip dashicons-info"><span class="tooltiptext">Minimum qty will apply on cart page</span></span>
				  </th>
				</tr>
				<?php 
				foreach ( $requisition as $index => $list ) {
					if (isset($list['wwp_product_id']) && !empty($list['wwp_product_id'])) {
						$product = wc_get_product( $list['wwp_product_id'] );
						$price = wwp_get_price_including_tax_for_requisition( $product, array( 'price' => $product->get_price() ) );
						?>
						<tr class="wwp_table_row">
							<td>
								<div class="wwp_product_title">
									<span class="dashicons dashicons-trash"></span> 
									<select class="select2 wwp_requisition_list_price" data-price="<?php esc_attr_e( $price ); ?>" name="requisition[<?php esc_attr_e( $index ); ?>][wwp_product_id]"  style="width:300px;">
									<option value="<?php esc_attr_e( $list['wwp_product_id'] ); ?>" data-select2-tag="true" ><?php esc_attr_e( $product->get_name() ); ?></option>
									</select>
								</div>
							</td>
							<td>
								<div class="wwp_product_qty">
									<input type="number" min="1" class="form-control wwp_requisition_list_qty" value="<?php esc_attr_e( $list['wwp_product_qty'] ) ; ?>" name="requisition[<?php esc_attr_e( $index ); ?>][wwp_product_qty]" >
								</div>
							</td>
							<td>
								<div class="wwp_product_subtotal">
									<input type="hidden" class="wwp_product_price wwp_requisition_list_price_hide" value="<?php esc_attr_e( $price ); ?>" name="requisition[<?php esc_attr_e( $index ); ?>][wwp_product_price]">
									<span class="wwp_requisition_list_price_display"> <?php esc_attr_e( $price ); ?> </span>
								</div>
							</td>
						</tr>
						<?php
					}
				}
				?>
			</table>
			<div class="wwptotalamount">
				<button type="button"> + add Item</button>
			</div> 
			<div class="wwp_totalcoloum">
				<button id="wwp_add_to_cart"> Add to Cart</button>
				<button id="wwp_save_list"> Update list</button>
				<div class="totalamountpopup">
					Total: <span class="wwp_price">$0.00</span>
				</div>
			</div> 
			<input type="hidden" name="action" value="WWP_requisition_list_template_edit">
			<input type="hidden" name="post_id" value="<?php esc_attr_e( $post_data->ID ) ; ?>">
			<input type="hidden" id="wwp_list_name" name="wwp_list_name" value="<?php esc_attr_e( $post_data->post_title ) ; ?>">
		</form>
	</div>
