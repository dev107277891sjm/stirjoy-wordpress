(function ( $ ) {
	'use strict';

	window.doko               = {};
	window.doko.total_price   = 0;
	window.doko.total_qty    = 0;
	window.doko.box_container = "";
	window.doko.box_contents  = {};
	window.doko.rules         = {};
	window.doko.total_rules   = {}
	window.doko.screens       = {};
	window.doko.box_card      = {};
	window.doko.gift_messages = {};
	window.doko.opt_management = {};
	window.doko.history	   = [];
	let qty;
	let data_to_send = {
		action: 'doko-get-box-display'
	};
	$( document ).ready(
		function () {
			window.doko_bundle_id        = $('input[name="doko-bundle-page-id"]').val();
			window.doko_step_decoded = JSON.parse( window.doko_step_identifier );

			function hs_dk_init_cart_content_page(){
				$('select.hs-dk-categories').on('change', function(e) {
					e.preventDefault();
					let curbId =  $( this ).data( 'bundleContentId' );
					data_to_send = {
						action: 'doko-get-box-display',
						operation_type : 'categories',
						filter_type : $( 'div[data-bundle-content-id="' + curbId + '"] select.hs-dk-options' ).val(),
						list_ids : $( 'div[data-bundle-content-id="' + curbId + '"] select.hs-dk-categories' ).val(),
						bundleContentId : curbId,
						bundleId : $( this ).data( 'bundleId' ),
						current_page : window.doko.current_page
					};
					$.post( doko_object.ajaxurl, data_to_send ).done(
						function (data) {
							$( 'div[data-bundle-content-id="' + curbId + '"]' ).closest( '.hs-dk-products-to-add-to-box' ).html( data );
							hs_dk_init_infinite_loading();
							hs_dk_init_info_click();
							hs_dk_init_variation_select();
						}
					)
				})
				$('select.hs-dk-categories').trigger('change')
			}

			hs_dk_init_cart_content_page();

			$( 'textarea[name="hs-dk-gift-message-box"]' ).on(
				'keyup',
				function (e) {
					// let bundle_sti = $(this).data('bundleContentId');
					let bundle_sn                 = $( this ).data( 'bundleScreenName' );
					doko.gift_messages[bundle_sn] = $( this ).val();
				}
			);

			if ( $( document ).innerWidth() <= 767 ) {
				if ( $( document ).innerWidth() <= 767 ) {
					if ( $( '.storefront-handheld-footer-bar' ) ){
						var woocommerce_storefront_footer = $( '.storefront-handheld-footer-bar' ).height();
						$( 'div.hs-dk-menu-fix' ).css( 'bottom', parseFloat(woocommerce_storefront_footer) *  2 + 'px' )
					}
				}
			}

			$( window ).resize(
				function () {
					console.log('is resized');
					if ( $( document ).innerWidth() <= 767 ) {
						if ( $( '.storefront-handheld-footer-bar' ) ){
							var woocommerce_storefront_footer = $( '.storefront-handheld-footer-bar' ).height();
							$( 'div.hs-dk-menu-fix' ).css( 'bottom', parseFloat(woocommerce_storefront_footer) * 2 + 'px' )
						}
					} else {
						$( 'div.hs-dk-menu-fix' ).css('bottom', '0px');
					}
				}
			);

			hs_dk_init_add_to_box();

			hs_dk_init_search_card_page();

			hs_dk_init_variation_select();

			$( 'div.hs-dk-bundle-page' ).hide()
			$( 'div.hs-dk-bundle-page[data-page-id="1"]' ).show()
			$( 'div.hs-dk-menu-fix' ).hide()
			if ( undefined != doko_bundle_data ) {
				if ( doko_bundle_data['enable-bottom-navigation'] == 'yes' ) {
					$( 'div.hs-dk-menu-fix' ).show();
				}
			}
			$( 'div.hs-dk-product-card-panel' ).hide()

			var current_page         = 1;
			window.doko.current_page = current_page;
			$( 'div#doko-giftbox-progress-bar div.step a' ).removeClass( 'active_tab_link' );
			$( 'div#doko-giftbox-progress-bar div.step[data-step-id="' + current_page + '"] a' ).addClass( 'active_tab_link' );
			$( 'button.doko-page-btn[data-btn-type="next"]' ).on(
				'click',
				function () {

					if ( current_page + 1 > $( 'div.hs-dk-bundle-page' ).length ) {
						return false;
					}
					wp.hooks.doAction( 'doko_before_click_navigate_to_page', current_page );

					if ( undefined != doko_bundle_data ) {
						if ( wp.hooks.applyFilters( 'doko_restrict_first_page', true, current_page ) ) {
							if ( "" == window.doko.box_container ) {
								alert( doko_bundle_data['no-products-message'] );
								return false;
							}
						}
					}

					if ( $( 'div.hs-dk-bundle-page' ).length == current_page + 1 ) {
						$( 'div.hs-dk-menu-box' ).hide()
					}

					current_page++;
					$( 'div.hs-dk-bundle-page' ).hide();
					$( 'div.hs-dk-bundle-page[data-page-id="' + current_page + '"]' ).show()
					$( 'div#doko-giftbox-progress-bar div.step a' ).removeClass( 'active_tab_link' );
					$( 'div#doko-giftbox-progress-bar div.step[data-step-id="' + current_page + '"] a' ).addClass( 'active_tab_link' );
					window.doko.current_page = current_page;
					wp.hooks.doAction( 'doko_after_click_navigate_to_page', current_page );

					const element = document.querySelector('.doko-box-section');

					if (element) {
						const top = element.getBoundingClientRect().top + window.pageYOffset;
						window.scrollTo({
							top: top,
							behavior: 'smooth' // or 'auto' for instant scroll
						});
					}

					let doko_step_decoded = JSON.parse( doko_step_identifier );
					hs_dk_init_infinite_loading( {
						action: 'doko-get-products-infinite-loading',
						bundleContentId : doko_step_decoded[current_page],
						bundleId : window.doko_bundle_id,
						current_page : window.doko.current_page,
						current_position : $('div[data-bundle-content-id="'+doko_step_decoded[current_page]+'"] input[name="doko_current_position"]').val()
					})
				}
			);

			$( 'button.doko-page-btn[data-btn-type="prev"]' ).on(
				'click',
				function () {
					if ( current_page - 1 < 1 ) {
						return false;
					}
					wp.hooks.doAction( 'doko_before_click_navigate_to_page', current_page );
					$( 'div.hs-dk-bundle-page' ).hide();
					current_page--;
					$( 'div.hs-dk-bundle-page[data-page-id="' + current_page + '"]' ).show()
					$( 'div#doko-giftbox-progress-bar div.step a' ).removeClass( 'active_tab_link' );
					$( 'div#doko-giftbox-progress-bar div.step[data-step-id="' + current_page + '"] a' ).addClass( 'active_tab_link' );
					window.doko.current_page = current_page;

					wp.hooks.doAction( 'doko_after_click_navigate_to_page', current_page );
				}
			);

			$( 'button.doko-restart' ).on(
				'click',
				function () {
					window.location.reload();
				}
			);

			$( 'button.doko-go-to-cart' ).on(
				'click',
				function () {
					window.location.href = doko_object.cart_page_url;
				}
			);
		
			hs_dk_init_info_click();

			function hs_dk_init_delete_box_action( hash_code , trigger= false ) {

				var jsonId         = JSON.parse( doko_step_identifier );
				var current_screen = window.doko.current_page;

				var screenId = jsonId[current_screen];
				if ( $( 'table.doko-box-contents' ).length > 0 ) {
					$( 'table.doko-box-contents tr[data-product-id]' ).each(
						function () {
							$( this ).children( 'td:nth(4)' ).children( 'a.doko-remove' ).unbind();
							$( this ).children( 'td:nth(4)' ).children( 'a.doko-remove' ).on(
								'click',
								function () {
									var product_id = $( this ).parent().parent().data( 'productId' );
									var productQty = parseInt( $( 'table.doko-box-contents tr[data-product-id="' + product_id + '"]' ).data( 'productQty' ) );
									var price      = parseFloat( $( 'table.doko-box-contents tr[data-product-id="' + product_id + '"]' ).data( 'productPrice' ) );
									wp.hooks.doAction( 'doko_before_delete_product_from_box', product_id, productQty, price );
									delete window.doko.box_contents[product_id];
									delete window.doko.screens[screenId][product_id];

									recalculate_total_html();

									$( this ).parent().closest( 'tr' ).remove()
									$( 'ul.box-images li[data-product-id="' + product_id + '"]' ).remove();
									$( 'ul.doko-box-contents li[data-product-id="' + product_id + '"]' ).remove();
									$( 'table.doko-box-contents tr[data-product-id="' + product_id + '"]' ).remove();

									wp.hooks.doAction( 'doko_after_delete_product_from_box', product_id, productQty, price )
								}
							);
						}
					);
				}

				$( document ).on(
					'click',
					'li[data-hash-code="' + hash_code + '"] div.doko-right-sup-exposant',
					function () {
						var price      = $( this ).closest( 'li' ).data( 'productPrice' );
						var productQty = $( this ).closest( 'li' ).data( 'productQty' );
						var product_id = $( this ).closest( 'li' ).data( 'productId' );
						$( this ).closest( 'li' ).remove();
						wp.hooks.doAction( 'doko_before_delete_product_from_box', product_id, productQty, price )
						delete window.doko.box_contents[product_id];
						delete window.doko.screens[screenId][product_id];
						recalculate_total_html();
						$( 'ul.box-images li[data-product-id="' + product_id + '"]' ).remove();
						$( 'ul.doko-box-contents li[data-product-id="' + product_id + '"]' ).remove();
						$( 'table.doko-box-contents tr[data-product-id="' + product_id + '"]' ).remove();
						wp.hooks.doAction( 'doko_after_delete_product_from_box', product_id, productQty, price )
					}
				);

				if ( trigger ) {
					$( 'li[data-hash-code="' + hash_code + '"] div.doko-right-sup-exposant' ).trigger( 'click' );
				}

			}

			window.hs_dk_init_delete_box_action = hs_dk_init_delete_box_action;
			window.hs_dk_recalculate_total      = recalculate_total;
			window.hs_dk_recalculate_total_html      = recalculate_total_html;

			/**
			 * Converts numbers to formatted price strings. Respects WC price format settings.
			 *
			 * @param float price The value to format
			 */
			function hs_dk_get_wc_price( price, add_currency = true ) {
				var formatted_price;
				var wc_mnm_params = doko_object.wc_price_args;
				var default_args  = {
					decimal_sep:       wc_mnm_params.decimal_separator,
					currency_position: wc_mnm_params.currency_position,
					currency_symbol:   wc_mnm_params.currency_symbol,
					trim_zeros:        wc_mnm_params.currency_format_trim_zeros,
					num_decimals:      wc_mnm_params.currency_format_num_decimals,
					html:              true
				};
				if ( default_args.num_decimals > 0 ) {
					var rounded     = Math.round( (price % 1) * 100 ) / 100;
					formatted_price = parseInt( price ) + rounded;
				} else {
					formatted_price = parseInt( price );
				}

				price = price.toString().replace( '.', default_args.decimal_sep );

				if ( add_currency ) {
					var formatted_symbol = default_args.html ? '<span class="woocommerce-Price-currencySymbol">' + default_args.currency_symbol + '</span>' : default_args.currency_symbol;
					if ( 'left' === default_args.currency_position ) {
						formatted_price = formatted_symbol + formatted_price;
					} else if ( 'right' === default_args.currency_position ) {
						formatted_price = formatted_price + formatted_symbol;
					} else if ( 'left_space' === default_args.currency_position ) {
						formatted_price = formatted_symbol + ' ' + formatted_price;
					} else if ( 'right_space' === default_args.currency_position ) {
						formatted_price = formatted_price + ' ' + formatted_symbol;
					}
					formatted_price = default_args.html ? '<span class="woocommerce-Price-amount amount">' + formatted_price + '</span>' : formatted_price;
				}

				return formatted_price;
			}

			window.hs_dk_get_wc_price = hs_dk_get_wc_price;

			function hs_dk_build_box_contents( product_qty, product_name, product_price, product_id ) {
				var html = '<tr data-product-id="' + product_id + '" data-product-qty="' + product_qty + '" data-product-price="' + product_price + '">'
				html    += '<td>' + product_name + '</td><td class="doko_style_to_hide">' + product_qty + '</td><td class="doko_style_to_hide">' + product_price + '</td><td>' + product_price * product_qty + '</td>'
				html    += '<td> <a class="doko-remove" ><i class="fa fa-times" aria-hidden="true"></i></a></td>'
				html    += '</tr>'
				return html;
			}

			function hs_dk_has_decimals() {
				var wc_mnm_params = doko_object.wc_price_args;
				var default_args  = {
					decimal_sep:       wc_mnm_params.decimal_separator,
					currency_position: wc_mnm_params.currency_position,
					currency_symbol:   wc_mnm_params.currency_symbol,
					trim_zeros:        wc_mnm_params.currency_format_trim_zeros,
					num_decimals:      wc_mnm_params.currency_format_num_decimals,
					html:              true
				};
				if ( default_args.num_decimals > 0 ) {
					return true;
				} else {
					return false;
				}
			}

			function hs_generateUniqueId(prefix = '', length = 13) {
				const characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
				const charactersLength = characters.length;

				let result = prefix;

				for (let i = 0; i < length; i++) {
					result += characters.charAt( Math.floor( Math.random() * charactersLength ) );
				}

				return result;
			}

			$( document ).on(
				'change keydown click',
				'input.doko-bundle-qty',
				function () {
					let bqty         = $( this ).val();
					let pid          = $( this ).data( 'productId' );
					let pname        = $( this ).data( 'productName' );
					let pprice       = $( this ).data( 'productPrice' );
					let current_page = doko.current_page;
					let purl         = $( this ).data( 'productUrl' )

					let dkscrt = JSON.parse( doko_step_identifier );

					let cpda = dkscrt[current_page];
					if ( undefined != doko.box_contents[pid] ) {
						var elemUL = $( 'ul.doko-box-contents' );

						hs_dk_add_li_to_box(
							{
								"productId" : pid,
								"imageUrl" : purl,
								"productPrice" : pprice,
								"quantity" : bqty

							},
							elemUL,
							false,
							true,
							true
						);

						doko.box_contents[pid]['product_qty'] = parseInt( bqty );
						doko.screens[cpda][pid]['qty']        = parseInt( bqty );

					}

					recalculate_total_html();

				}
			);

			function hs_dk_rebuild_li_product_lines( productData, product_img_url, product_quantity, product_name, sprice, product_id ) {
						
				let box_contents_html = hs_dk_build_box_contents( product_quantity, product_name, sprice, product_id );
	
				if ($( 'table.doko-box-contents tbody tr[data-product-id="' + product_id + '"]' ).length == 0) {
					$( 'table.doko-box-contents tbody' ).append( box_contents_html );
					$( 'ul.box-images' ).append( hs_dk_build_img_block( product_img_url, false, productData ) );
				} else {
					qty               = $( 'ul.doko-box-contents li[data-product-id="' + product_id + '"] div.doko-qty-exposant' ).html();
					box_contents_html = hs_dk_build_box_contents( qty, product_name, sprice, product_id );
					$( 'table.doko-box-contents tr[data-product-id="' + product_id + '"]' ).replaceWith( box_contents_html )
				}

			}

			window.hs_dk_rebuild_li_product_lines = hs_dk_rebuild_li_product_lines;

			function hs_dk_init_add_to_box( args = false ) {
				$( 'a.doko-add-to-box' ).unbind();
				$( document ).on(
					'click',
					'a.doko-add-to-box',
					function (e) {
						e.preventDefault();

						if ( $(this).hasClass('disabled') ) {
							return;
						}

						var unique_id           = hs_generateUniqueId( 'doko', 12 );
						var dk_current_page     = window.doko.current_page;
						var product_img_url     = $( this ).data( 'imageUrl' );
						var product_id          = $( this ).data( 'productId' );
						var product_price       = $( this ).data( 'productPrice' );
						var product_quantity    = $( this ).data( 'quantity' );
						var parent_variation_id = $( this ).data( 'productVariationParentId' );
						var bundle_id           = $( 'input[name="doko-bundle-page-id"]' ).val();
						var productData         = {}
						var cardMode            = $( this ).data( 'cardMode' );
						window.doko.cardMode    = cardMode;

						// generate code to get data of a parent of multilevel wih data-page-id and data-product-id
						var element   = $( this ); // Select the child element A
						var parentDiv = element.parents( "div.hs-dk-bundle-page" ); // Select the 5th parent div
						var page_data = $( parentDiv ).data();

						productData = $( this ).data();

						var closest_qty = $( this ).parent().find( 'span.formulus-input-wrapper' ).find( 'input.doko-bundle-qty' );
						if ( closest_qty ) {

							let qtye = $( closest_qty ).val();
							if ( qtye == undefined ) {
								productData.quantity = 1;
								product_quantity     = 1;
							} else {
								productData.quantity = $( closest_qty ).val();
								product_quantity     = $( closest_qty ).val();
							}

						}

						product_quantity = parseInt( product_quantity );
						console.log( 'product_quantity', product_quantity );

						productData.current_page = dk_current_page;
						productData.bundle_id    = bundle_id;
						var product_name         = $( this ).data( 'productName' )
						productData.productName  = product_name;

						productData.unique_id = unique_id;

						wp.hooks.doAction( 'doko_before_calculate_add_to_box', product_id, productData, window.doko.current_page, page_data );

						productData.variation_id = parent_variation_id;

						wp.hooks.doAction( 'doko_after_calculate_add_to_box', product_id, productData, window.doko.current_page, page_data );

						var o_options_is_checked       = productData['options_is_checked'];

						if ( o_options_is_checked ) {
							product_id = "p"+product_id
						}

						if ( "no" == cardMode ) {
							if ($( 'table.doko-box-contents' ).length > 0 && dk_current_page != 1) {
								var box_contents_html;

								let sprice = parseFloat(productData['productPrice']);
								if ( undefined != productData['options'] && productData['options'].length > 0  && o_options_is_checked ) {
									for ( var o in productData['options'] ) {
										if ( undefined != productData['options'][o].amount ) {
											sprice += productData['options'][o].amount ;
										}
									}
								}

								hs_dk_rebuild_li_product_lines( productData, product_img_url, product_quantity, product_name, sprice, product_id );

							}

							$( 'div.hs-dk-menu-fix' ).show();
							hs_dk_init_delete_box_action( unique_id );
						} else {
							$( 'div.hs-dk-menu-fix' ).hide();
							$( 'img.hs-dk-card-image' ).attr( 'src', product_img_url )
							$( 'div.hs-dk-product-card-panel' ).show()
							$( 'div.hs-dk-product-panel' ).hide()
							window.doko.box_card.product_id = product_id;

						}

						recalculate_total_html();
					}
				);

				$( 'a.hs-dk-change-card' ).on(
					'click',
					function () {
						$( 'div.hs-dk-product-card-panel' ).hide()
						$( 'div.hs-dk-product-panel' ).show()
					}
				);

				$( 'input.hs-dk-input-container-to' ).on(
					'change',
					function () {
						window.doko.box_card.to = $( this ).val()
					}
				);
				$( 'input.hs-dk-input-container-to' ).trigger( 'change' )

				$( 'input.hs-dk-input-container-from' ).on(
					'change',
					function () {
						window.doko.box_card.from = $( this ).val()
					}
				);
				$( 'input.hs-dk-input-container-from' ).trigger( 'change' )

				$( '#message-box' ).on(
					'change',
					function () {
						window.doko.box_card.message = $( this ).val()
					}
				);
				$( '#message-box' ).trigger( 'change' );

				$( 'div.hs-dk-card-details [name="doko-note-option"]' ).on(
					'change',
					function () {
						window.doko.box_card.options = $( this ).val()
					}
				);
				$( 'div.hs-dk-card-details [name="doko-note-option"]' ).change();

			}



			function recalculate_total() {
				var screens = window.doko.screens;
				var total   = 0;
				for (var sc in screens) {
					var product = screens[sc];
					if (product.qty !== undefined && product.amount !== undefined) {
						total += product.qty * product.amount;
					} else {
						for (var ccd in product) {
							var data = product[ccd];		
							if (data.qty !== undefined && data.amount !== undefined) {
								total += data.qty * data.amount;
							}
						}
					}
				}

				total = wp.hooks.applyFilters('doko_total_price', total, screens );
				
				window.doko.total_price = total;
				return total;

			}

			function recalculate_total_html() {
				let total = recalculate_total();
				$( 'button.doko-total-btn' ).html( 'Total : ' + hs_dk_get_wc_price( total, true ) + " ⓘ " );
				$('.doko_total_details_price').html('Total : '+ hs_dk_get_wc_price( doko.total_price, true ) );
			}

			function hs_dk_build_img_block( product_img_url, is_bottom = true, productData ){

				var li_class_name  = "";
				var div_class_name = "doko-icone-remove";
				var i_class_name   = "";
				if ( ! is_bottom ) {
					li_class_name  = 'doko-reset-icone-remove';
					div_class_name = "doko-reset-icone";
				}

				return "<li class='" + li_class_name + "' data-product-id='" + productData.productId + "'><i ' style='cursor: pointer;' aria-hidden='true'></i></div><div class='selected-box' ><img class='box-color' src='" + product_img_url + "'></div></li>";
			}

			function hs_dk_init_infinite_loading(  ) {
				$( 'a.doko-load-more-content-page-action' ).unbind();
				$( 'a.doko-load-more-content-page-action' ).on(
					"click",
					function (e) {
						e.preventDefault();
						$(this).html('Loading ...');
						let nb_of_post_per_page = 0;
						let data_to_send = {
							'bundleId' : window.doko_bundle_id,
							'bundleContentId' : $(this).data('bundleContentId')
						}
						let curbId = $(this).data('bundleContentId');

						let cposition 	   = $( 'input[data-bundle-content-id="'+curbId+'"][name="doko_current_position"]' ).val();

						if ( cposition ) {
							nb_of_post_per_page = doko_bundle_data[curbId]['nb-products-per-page'];
						}
						data_to_send['action'] = 'doko-get-products-infinite-loading';
						data_to_send['current_position'] = parseInt(cposition) + 1 ;
						data_to_send['filter_type']      = $( 'div[data-bundle-content-id="' + window.doko_step_decoded[doko.current_page] + '"] select.hs-dk-options' ).val();
						data_to_send['list_ids']      = $( 'div[data-bundle-content-id="' + window.doko_step_decoded[doko.current_page] + '"] select.hs-dk-categories' ).val();
						

						$.post(
							doko_object.ajaxurl,
							data_to_send
						).done(
							function (response) {
								$( 'a.doko-load-more-content-page-action' ).html('Load more');
								$('input[data-bundle-content-id="'+curbId+'"][name="doko_current_position"]').remove();
								$('input[data-bundle-content-id="'+curbId+'"][name="doko_operation_type"]').remove();
								let unique_id  = hs_generateUniqueId('doko_part', 10 );
								$('ul.hs-doko-loop.products').append(response);
								cposition 	   = $( 'input[data-bundle-content-id="'+curbId+'"][name="doko_current_position"]' ).val();
								let dtt = parseInt(cposition)+parseInt(nb_of_post_per_page);
								if ( $( 'div.woocommerce-no-products-found' ).length > 0 ) {
									$( 'a.doko-load-more-content-page-action' ).remove();
									$( 'div.woocommerce-no-products-found' ).remove();
								} else {
									$( 'input[name="doko_current_position"]' ).val(dtt)
								}
								hs_dk_init_info_click();
								hs_dk_init_variation_select();
							}
						)
					}
				)
			}


			function hs_dk_init_search_card_page() {
				$( 'input#hs-dk-custom' ).on(
					'keyup',
					function (e) {
						var value_entered = $( this ).val().toLowerCase();
						// storefront theme
						if ( $( 'div.hs-dk-products-to-add-to-box h2.woocommerce-loop-product__title' ).length > 0 ) {
							$( 'div.hs-dk-products-to-add-to-box h2.woocommerce-loop-product__title' ).each(
								function (u, v) {
									var textL = $( this ).text().toLowerCase();
									$( this ).closest( '.status-publish' )[ textL.indexOf( value_entered ) !== -1 ? 'show' : 'hide' ]();
								}
							);
						}
						
					}
				);
			}

			function hs_dk_delete_elmt_modal() {
				let modyl = $.modal.getCurrent();
				var jsonId = JSON.parse(doko_step_identifier);
				var current_screen = window.doko.current_page;
				var screenId = jsonId[current_screen];

				// Use event delegation to ensure newly added elements are also affected
				$(modyl.$elm[0]).on("click", "a.doko-remove", function () {
					var product_id = $(this).parent().parent().data("productId");
					var productQty = parseInt($('table.doko_total_details tr[data-product-id="' + product_id + '"]').data("productQty"));
					var price = parseFloat($('table.doko_total_details tr[data-product-id="' + product_id + '"]').data("productPrice"));

					wp.hooks.doAction("doko_before_delete_product_from_box", product_id, productQty, price);

					// Remove product from data
					delete window.doko.box_contents[product_id];
					delete window.doko.screens[screenId][product_id];

					recalculate_total_html();

					// Remove product elements from UI
					$(this).parent().closest("tr").remove();
					$('ul.box-images li[data-product-id="' + product_id + '"]').remove();
					$('ul.doko-box-contents li[data-product-id="' + product_id + '"]').remove();
					$('table.doko-box-contents tr[data-product-id="' + product_id + '"]').remove();

					wp.hooks.doAction("doko_after_delete_product_from_box", product_id, productQty, price);
				});

			}


			function hs_dk_init_info_click(){
				$('div.doko_infoButton').on('click', function(e){
					e.preventDefault();
					var productId = $(this).parent().parent().find(".doko-add-to-box").data();
					var btn       = $(this).parent().parent().find(".doko-add-to-box").clone();
					$('div.doko_modal div.doko_modal_product_title').empty();
					$('div.doko_modal div.doko_modal_product_title').append( '<h2>' + productId['productName'] + '</h2>' );
					$('div.doko_modal div.doko_modal_product_img').empty();
					$('div.doko_modal div.doko_modal_product_img').append( '<img src="' + productId['imageUrl'] + '" alt="doko-image" class="doko-modal-img" />' );
					$('div.doko_modal div.doko_modal_product_desc').empty();
					$('div.doko_modal div.doko_modal_product_desc').append(  $(this).data('productDescription') )
					$('div.doko_modal div.doko_modal_product_price').empty();
					$('div.doko_modal div.doko_modal_product_price').append(  "<p class='doko-price-section'>" + productId['productPrice'] + " "+ productId['siteCurrency'] + "</p>" );
					$('div.doko_modal div.doko_modal_product_price').append(  btn );
					$('div.doko_modal').modal();
				});
			}

			function hs_dk_init_variation_select() {
				$('select.doko-add-to-box-variable').on('change', function(e){
					var selected_value = $(this).val();
					$(this).parent().find('div.doko_wrapper_btn_variations').hide();
					$(this).prevAll().find('span.price span.doko_variable_prices').hide();
					$(this).parent().find('div.doko_wrapper_btn_variations[data-product-id="'+selected_value+'"]').show();
					$(this).parent().find('div.doko_wrapper_btn_variations[data-product-id="'+selected_value+'"] a').show();
					$(this).prevAll().find('span.price span.doko_variable_prices[data-product-id="'+selected_value+'"]').show();
					if ( selected_value == "" || !selected_value ) {
						$(this).prevAll().find('span.doko_old_price').show();
					}
				})

				$('select.doko-add-to-box-variable').trigger('change');
			}

			$('button.doko_total_btn_elt').on('click', function(){
				$('div#doko_total_details').modal()
				hs_dk_delete_elmt_modal();
			});

			function hs_dk_rebuild_total_modal(){
				let ds = window.doko.screens;
				let uld = $('table.doko_select tbody');

				let tli = "";
				tli = wp.hooks.applyFilters('doko_before_creating_total_details_modal', tli );
				for( var d in ds ) {
					let elop = ds[d];
					if ( d == "first-step" ){
						let product = $('a[data-product-id="'+elop.product_id+'"]').data();
						if ( undefined != product ) {
							tli += "<tr class='cart_item' data-product-id='"+elop.product_id+"' data-product-qty='"+elop.qty+"' data-product-price='"+product.productPrice+"' data-product-currency='"+product.siteCurrency+"'>"+
								"<td><img src='"+product.imageUrl+ "' class='doko-modal-img-preview' /></td>" +
								"<td>"+product.productName+"</td>" +
								"<td class='doko_style_to_hide'>"+product.productPrice+" "+product.siteCurrency+"</td>" +
								"<td class='doko_style_to_hide'>"+elop.qty+"</td>" +
								"<td>"+(elop.qty * product.productPrice)+" "+product.siteCurrency+"</td>" +
								'<td></td>'+
								"</tr>";
						}
					} else {
						for( var pp in elop ) {
							let selop = elop[pp];
							let product = $('a[data-product-id="'+selop.product_id+'"]').data();
							if ( undefined != product ) {
								tli += "<tr class='cart_item' data-product-id='"+selop.product_id+"' data-product-qty='"+selop.qty+"' data-product-price='"+product.productPrice+"' data-product-currency='"+product.siteCurrency+"'>"+
									"<td><img src='"+product.imageUrl+ "' class='doko-modal-img-preview' /></td>" +
									"<td>"+product.productName+"</td>" +
									"<td class='doko_style_to_hide'>"+product.productPrice+" "+product.siteCurrency+"</td>" +
									"<td class='doko_style_to_hide'>"+selop.qty+"</td>" +
									"<td>"+(selop.qty * product.productPrice)+ " "+product.siteCurrency+"</td>" +
									'<td><a class="doko-remove"><i class="fa fa-times" aria-hidden="true"></i></a></td>'+
									"</tr>";
							}
						}
					}
				}
				tli = wp.hooks.applyFilters('doko_after_creating_total_details_modal', tli );
				uld.html(tli);
			}

			window.hs_dk_rebuild_total_modal = hs_dk_rebuild_total_modal;




		}

	);

})( jQuery );
