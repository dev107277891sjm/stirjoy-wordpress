(function ( $ ) {
	'use strict';

	$( document ).ready(
		function () {
			$( 'div.doko-spinner-loading' ).show();
			$( 'div.doko-spinner-complete' ).hide();
			$( 'div.doko-is-complete' ).hide()
			$( 'div.doko-spinner-error' ).hide();

			// Decide if the first page product must be deleted or not.
			wp.hooks.addFilter(
				'doko_is_dynamic_screen',
				'doko',
				function (is_dynamic_screen, current_page) {
					if ( parseInt( current_page ) != parseInt( 1 ) ) {
						is_dynamic_screen = true;
					} else {
						is_dynamic_screen = false;
					}
					return is_dynamic_screen;
				},
				10,
				2
			);

			// display toast for saying a new product have been added to bundle gift box.
			wp.hooks.addAction(
				'doko_after_calculate_add_to_box',
				'doko',
				function ( product, productData, current_page ) {
					var pname           = productData['productName'];
					let text = doko_object.product_is_added_to_box_message;
					text = text.replace('%s', pname );
					Snackbar.show( { actionText: 'Close', pos: 'bottom-center', duration: 10000, text: text } );
				},
				20,
				3
			);



			// Trigger event to go to the next page.
			wp.hooks.addAction(
				'doko_after_calculate_add_to_box',
				'doko',
				function ( product, productData, current_page ) {
					var pid             = productData['productId'];
					var pqty            = productData['quantity'];
					var pprice          = productData['productPrice'];
					var puid            = productData['unique_id'];
					var pvid            = productData['variation_id'];
					var o_options       = productData['options'];
					var o_options_is_checked       = productData['options_is_checked'];
					var step_identifier = JSON.parse( doko_step_identifier );

					// store into the history the product added to the box. 
					if ( productData.current_page > 1 ) {
						const historyData  = productData;
						window.doko.history.unshift( historyData );
					}



					if ( current_page == 1 ) {
						if ( wp.hooks.applyFilters( 'doko_execute_rule_on_page' , true , current_page ) ) {
							if ( undefined != doko_bundle_data ) {
								var enable_redirect = wp.hooks.applyFilters( 'doko_enable_first_page_redirect', doko_bundle_data['enable-screen-redirect'] , current_page );
								// if ( "" != window.doko.box_container ) {
								// 	var pprice              = window.doko.box_container['product_price'] * window.doko.box_container['product_qty'];
								// 	window.doko.total_price = parseFloat( window.doko.total_price ) - parseFloat( pprice );
								// }
								window.doko.box_container         = {
									product_id : pid,
									product_qty: pqty,
									product_price : pprice,
									unique_id : puid,
									variation_id : pvid,
									options : o_options
								};

								let sprice = pprice;

								for ( var o in o_options ) {
									if ( undefined != o_options[o].amount ) {
										sprice += o_options[o].amount
									}
								}


								window.doko.screens['first-step'] = {
									product_id : pid,
									qty : productData['quantity'],
									amount : wp.hooks.applyFilters('doko_bundle_product_price', sprice, product, productData, current_page ),
									options : o_options
								}

								var elemUL = $( 'ul.doko-box-container' );
								hs_dk_add_li_to_box( productData, elemUL, true,  true );

								if ( "yes" == enable_redirect ) {
									$( 'ul.doko-btn-navigation button.doko-page-btn[data-btn-type="next"]' ).trigger( 'click' );
								}
							}
						}
					} else {
						if ( wp.hooks.applyFilters( 'doko_execute_rule_on_page' , true , current_page ) ) {
							var elemUL = $( 'ul.doko-box-contents' );
							if ( wp.hooks.applyFilters( 'doko_add_product_to_navigation', true , current_page ) ) {
								hs_dk_add_li_to_box( productData, elemUL, false,  true );
							}

							if ( o_options_is_checked ) {
								pid = "p"+pid
							}

							var sidentifier = step_identifier[current_page];

							if ( window.doko.cardMode == "no" ) {
								let sprice = pprice;

								for ( var o in o_options ) {
									if ( undefined != o_options[o].amount ) {
										sprice += o_options[o].amount
									}
								}

								if ( undefined == window.doko.screens[sidentifier] ) {
									window.doko.screens[sidentifier] = {}
								} 

								if ( ! window.doko.screens[sidentifier].hasOwnProperty( pid ) ) {
									window.doko.screens[sidentifier][pid] = {
										product_id : pid,
										qty : 0,
										amount : wp.hooks.applyFilters('doko_bundle_product_price', sprice, product, productData, current_page ),
										options : o_options
									}
								} 

								window.doko.screens[sidentifier][pid]['qty'] += pqty;
																	
								if ( window.doko.box_contents.hasOwnProperty( pid ) ) {
									window.doko.box_contents[pid]['product_qty'] += pqty;			
								} else {
									window.doko.box_contents[pid] = {
										product_id : productData['productId'],
										product_qty: pqty,
										product_price : pprice,
										unique_id : puid,
										variation_id : pvid,
										options : o_options
									};
								}
							}

							
						}
					}
					wp.hooks.doAction( 'doko_execute_rule', current_page, pid, pqty, pprice, puid, pvid, product, productData );
				},
				10,
				3
			);

			wp.hooks.addAction(
				'doko_after_calculate_add_to_box',
				'doko',
				function ( product, productData, current_page ) {
					window.hs_dk_recalculate_total_html();
					hs_dk_rebuild_total_modal();
					},
				50,
				3
			);

			wp.hooks.addAction(
				'doko_delete_product_from_box',
				'doko',
				function ( product_id ) {
					var cur_screen = $( 'div.hs-dk-bundle-page' ).filter( ':visible' ).data();
					if ( cur_screen.pageId > 1 ) {
						delete window.doko.box_contents[product_id];
					}
				}
			);

			wp.hooks.addAction(
				'doko_after_delete_product_from_box',
				'doko',
				function ( product_id ) {
					hs_dk_rebuild_total_modal();
				}
			);

			wp.hooks.addAction(
				'doko_after_click_navigate_to_page',
				'doko',
				function ( current_page ) {
					if ( current_page == $( 'div.hs-dk-bundle-page' ).length ) {
						hs_dk_add_to_cart_wc();
					}
				}
			);



			function hs_dk_update_bottom_nav( product_id, qty ) {
				$('ul.doko-box-contents li[data-product-id="' + product_id + '"]').find('div.doko-qty-exposant').html( qty );
				$('ul.doko-box-contents li[data-product-id="' + product_id + '"]').attr('data-product-qty', qty );
			}

			function hs_dk_update_card_mode_details( product_id, qty ) {
				$('table.doko-box-contents tr[data-product-id="'+product_id+'"]:eq(2)').html(qty);
				$('table.doko-box-contents tr[data-product-id="'+product_id+'"]:eq(2)').attr('data-product-qty', qty );
			}

			window.hs_dk_update_bottom_nav = hs_dk_update_bottom_nav;
			window.hs_dk_update_card_mode_details = hs_dk_update_card_mode_details;

			function hs_dk_get_table_row_to_box( product_data, ) {
				var qty             = product_data['quantity'];
				var product_id      = product_data['productId'];
				var product_price   = product_data['productPrice'];		
				var product_img_url = product_data['imageUrl'];
				var hash_code       = product_data['unique_id'];
				var o_options_is_checked       = product_data['options_is_checked'];
				if ( o_options_is_checked ) {
					product_id = "p"+product_id
				}
				var html = `<tr data-product-id="${product_id}" data-product-qty="${qty}" data-product-price="${product_price}" data-hash-code="${hash_code}">
					<td>${product_data['productName']}</td>
					<td class="doko_style_to_hide">${qty}</td>
					<td class="doko_style_to_hide">${product_price}</td>
					<td>${product_price}</td>
					<td><a class="doko-remove"><i class="fa fa-times" aria-hidden="true"></i></a></td>
				</tr>`;
				return html;
			}
			window.hs_dk_get_table_row_to_box = hs_dk_get_table_row_to_box;


					// responsible for the box contents.


			function hs_dk_add_li_to_box( product_data, parent_sibling, remove=true, is_bottom=true, updateMode=false  ) {
				var qty             = product_data['quantity'];
				var product_id      = product_data['productId'];
				var product_price   = product_data['productPrice'];
				var product_img_url = product_data['imageUrl'];
				var hash_code       = product_data['unique_id'];
				var o_options_is_checked       = product_data['options_is_checked'];

				if ( o_options_is_checked ) {
					product_id = "p"+product_id
				}
				var li_class_name   = "";
				if ( ! is_bottom ) {
					li_class_name = 'doko-reset-icone-remove';
				}

				if ( updateMode ) {
					if ( $( parent_sibling ).find( 'li[data-product-id="' + product_id + '"]' ).length > 0 ) {
						$( parent_sibling ).find( 'li[data-product-id="' + product_id + '"] div.doko-qty-exposant' ).html( qty );
						$( parent_sibling ).find( 'li[data-product-id="' + product_id + '"]' ).attr( 'data-product-qty', qty ).attr( 'data-hash-code', hash_code )
						$( "table.doko-box-contents tr[data-product-id='" + product_id + "']  td:nth-child(2)" ).html( qty );
					}
					return
				}

				if ( remove ) {
					// responsible for the box container.
					$( parent_sibling ).empty();
					var html                      = "<li class='" + li_class_name + "' data-product-qty='" + qty + "' data-product-price='" + product_price + "' data-product-id='" + product_id + "' data-hash-code='" + hash_code + "'><i style='cursor: pointer;' aria-hidden='true'></i><div class='selected-box' ><img class='box-color' src='" + product_img_url + "'></div> "
					var initial_is_dynamic_screen = false;
					var is_dynamic_screen         = wp.hooks.applyFilters( 'doko_is_dynamic_screen', initial_is_dynamic_screen, window.doko.current_page );
					if ( is_dynamic_screen ) {
						html += "<div class='doko-right-sup-exposant'>x</div>  <div class='doko-qty-exposant'>" + qty + "</div>"
					}
					html             += "</li>";
					$( parent_sibling ).append( html )
					$( parent_sibling ).find( 'li img' ).css( 'width','40px' )
					// window.doko.rules = []
				} else {
					// responsible for the box contents.
					if ( $( parent_sibling ).find( 'li[data-product-id="' + product_id + '"]' ).length > 0 ) {
						var product_qty = $( parent_sibling ).find( 'li[data-product-id="' + product_id + '"]' ).attr( 'data-product-qty' );
						qty             = parseInt( product_qty ) + 1;
						$( parent_sibling ).find( 'li[data-product-id="' + product_id + '"] div.doko-qty-exposant' ).html( qty );
						$( parent_sibling ).find( 'li[data-product-id="' + product_id + '"]' ).attr( 'data-product-qty', qty ).attr( 'data-hash-code', hash_code )

					} else {
						var html                      = "<li class='" + li_class_name + "' data-product-qty=" + qty + " data-product-price=" + product_price + " data-product-id=" + product_id + " data-hash-code=" + hash_code + "><i ' style='cursor: pointer;' aria-hidden='true'></i></div><div class='selected-box' ><img class='box-color' src='" + product_img_url + "'></div> "
						var initial_is_dynamic_screen = false;
						var is_dynamic_screen         = wp.hooks.applyFilters( 'doko_is_dynamic_screen', initial_is_dynamic_screen, window.doko.current_page );
						if ( is_dynamic_screen ) {
							html += "<div class='doko-right-sup-exposant'>x</div>  <div class='doko-qty-exposant'>" + qty + "</div>"
						}
						html += "</li>";
						$( parent_sibling ).append( html )
						$( parent_sibling ).find( 'li img' ).css( 'width','40px' )
					}
				}

			}

			window.hs_dk_add_li_to_box = hs_dk_add_li_to_box;


			/**
			 * create a cart into woocommerce.
			 */
			function hs_dk_add_to_cart_wc() {
				var doko_products = {
					'container' : window.doko.box_container,
					'contents' : window.doko.box_contents,
					'total_price' : window.doko.total_price,
					'card_details' : window.doko.box_card,
					'gift_message' : window.doko.gift_messages
				}
				if ( undefined !=  window.doko.discounts_applied  ) {
					doko_products.discounts_applied = window.doko.discounts_applied;
				}
				$.post(
					doko_object.ajaxurl,
					{
						action : "doko_wc_add_to_cart",
						contents : doko_products,
						bundle_id : $( 'div[data-bundle-id]' ).data( 'bundleId' ),

					},
					function ( response ) {
						var json_resp = JSON.parse( response );
						if ( json_resp.doko_is_added_to_cart ) {
							$( 'div.doko-spinner-loading' ).hide();
							$( 'div.doko-spinner-complete' ).show();
							$( 'div.doko-is-complete' ).show()
						} else {
							$( 'div.doko-spinner-loading' ).hide();
							$( 'div.doko-spinner-error' ).show();
							$( 'div.doko-is-complete' ).show();
							$( 'button.doko-go-to-cart' ).hide();
							// Snackbar.show( { actionText: 'Thanks!', pos: 'bottom-center', duration: 30000, text: "This bundle can't be added to the cart, an error occured, please write us to our support with the link of the page."} );
						}
						$('div#build-complete').append(json_resp.doko_html_response_adc)
					}
				)
			}

			window.hs_dk_add_to_cart_wc = hs_dk_add_to_cart_wc;
		}
	);

})( jQuery );

