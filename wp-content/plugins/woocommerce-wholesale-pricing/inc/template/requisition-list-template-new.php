<?php
/* Requisition list template */
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
			  <th class="wwp_table_subtotal_header" scope="col">Subtotal<span class="dashicons tooltip dashicons-info"><span class="tooltiptext">Minimum qty will apply on cart page</span></span></th>
			</tr>
			
			<tr class="wwp_table_row">
				<td>
					<div class="wwp_product_title">
					<span class="dashicons dashicons-trash"></span><select class="select2 wwp_requisition_list_price"  data-price="0" name="requisition[2640684][wwp_product_id]"  style="width:300px;"></select>
					</div>
				</td>
				<td>
					<div class="wwp_product_qty">
						<input type="number" min="1" class="form-control wwp_requisition_list_qty" value="1" name="requisition[2640684][wwp_product_qty]" >
					</div>
				</td>
				<td>
					<div class="wwp_product_subtotal">
						<input type="hidden" class="wwp_product_price wwp_requisition_list_price_hide" name="requisition[2640684][wwp_product_price]">
						<span class="wwp_requisition_list_price_display"> $0 </span>
					</div>
				</td>
			</tr>
		</table>
		<div class="wwptotalamount">
			<button type="button"> + add Item</button>
		</div> 
		<div class="wwp_totalcoloum">
			<button id="wwp_add_to_cart"> Add to Cart</button>
			<button id="wwp_save_list"> Save list</button>
			<div class="totalamountpopup">
				Total: <span class="wwp_price">$0.00</span>
			</div>
		</div> 
		<input type="hidden" name="action" value="WWP_requisition_list_template_new">
		<input type="hidden" id="wwp_list_name" name="wwp_list_name" value="">
	</form>
</div>
