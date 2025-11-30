(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	jQuery(document).ready(function($) {
		function updateFreeShippingAndGiftProgressBar(subtotal) {
            const freeShippingThreshold = $(".free-shipping-bar").attr('data-threshold');
            var rest = freeShippingThreshold - subtotal;
            var caption2 = 'Complete!';
            var width = 100;
            if(rest > 0) {
                caption2 = '$' + rest + ' to go';
                width = (subtotal / freeShippingThreshold) * 100;
            }
            
            $(".free-shipping-bar .caption2").text(caption2);
            $(".free-shipping-bar .bar > div").width(width + '%');

            const freeGiftThreshold = $(".free-gift-bar").attr('data-threshold');
            rest = freeGiftThreshold - subtotal;
            caption2 = 'Complete!';
            width = 100;
            if(rest > 0) {
                caption2 = '$' + rest + ' to go';
                width = (subtotal / freeGiftThreshold) * 100;
            }
            
            $(".free-gift-bar .caption2").text(caption2);
            $(".free-gift-bar .bar > div").width(width + '%');
        }

        function updateMiniCartSummary(wps_sfw_sub_box_total) {
        	let subTotal = parseFloat($(".mini-cart-subtotal .price").data('price')) + wps_sfw_sub_box_total;
			$(".mini-cart-subtotal .price").html('$' + subTotal.toFixed(2));
			let grandTotal = parseFloat($(".mini-cart-grandtotal .price").data('price')) + wps_sfw_sub_box_total;
			$(".mini-cart-grandtotal .price").html('$' + grandTotal.toFixed(2));
        }

        function updateItemsCount() {
        	let selectedItemsCount = $(".selected-items .selected-item").length;
			if(selectedItemsCount > 1) {
				$(".widget_shopping_cart .widgettitle span").text(selectedItemsCount + ' items');	
			} else {
				$(".widget_shopping_cart .widgettitle span").text(selectedItemsCount + ' item');	
			}
        }

		$('.wps_sfw_subs_box-button').on('click', function(e) {
			e.preventDefault();
			
			$('#wps_sfw_subs_box-popup').css('display','flex');
			$('#wps_sfw_subs_box-popup').fadeIn();
		});

		$('.wps_sfw_subs_box-close, #wps_sfw_subs_box-popup').on('click', function() {
			$('#wps_sfw_subs_box-popup').fadeOut();
		});

		$('.wps_sfw_subs_box-content').on('click', function(e) {
			e.stopPropagation();
		});

		$(".widget_shopping_cart .widgettitle").prepend('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-cart w-6 h-6 mr-2 text-primary" aria-hidden="true"><circle cx="8" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>');
		$(".widget_shopping_cart .widgettitle").append('<span>0 item</span>');
		

		$('.wps_sfw_sub_box_prod_add_btn').on('click', function(e) {
			e.preventDefault();

			let $btn = $(this);
			let $input = $btn.prev('.wps_sfw_sub_box_prod_count'); // Get input field
			let $minusBtn = $input.prev('.wps_sfw_sub_box_prod_minus_btn'); // Get minus button
			let count = parseInt($input.val()) || 0;

			var wps_sfw_sub_box_price = $btn.prev('.wps_sfw_sub_box_prod_count').data('wps_sfw_sub_box_price'); 

			var wps_sfw_subscription_box_price = $('.wps_sfw-sb-cta-total').data('wps_sfw_subscription_box_price');
			
			// Get the existing price from the span and convert it to a number
			if( wps_sfw_subscription_box_price == 0 ){

				var existing_price = parseFloat($('.wps_sfw-sb-cta-total span').text()) || 0;
				
				// Calculate the new total price
				var wps_sfw_sub_box_total = existing_price + wps_sfw_sub_box_price;
				
				// Update the span with the new total
				$('.wps_sfw-sb-cta-total span').text(wps_sfw_sub_box_total.toFixed(2));
			}
			
			count = count + 1;
			$input.val(count).show(); // Update and show input
			$minusBtn.show(); // Show minus button
			$btn.hide();
			
			let $parent = $btn.closest(".wps_sfw_sub_box_prod_item");
			$parent.addClass("selected");

			let $itemHtml = '<div class="selected-item" data-product-id="' + $btn.data('product-id') + '">' +
								'<span class="remove"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x w-3 h-3 text-red-500" aria-hidden="true"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg></span>' +
								'<div class="item-image"><img src="' + $parent.find('.wps_sfw_sub_box_prod_image img').attr('src') + '"></div>' +
								'<h3>' + $parent.find('.wps_sfw_sub_box_prod_name').text() + '</h3>' +
								'<p>' + $parent.find('.wps_sfw_sub_box_prod_price').text() + '</p>' +
							'</div>';
			$(".selected-items").append($itemHtml);

			let subTotal = parseFloat($(".mini-cart-subtotal .price").data('price')) + wps_sfw_sub_box_total;
			updateMiniCartSummary(wps_sfw_sub_box_total);
			updateFreeShippingAndGiftProgressBar(subTotal);
			updateItemsCount();

			$(".postid-8480 .fixed-sidebar-menu.fixed-sidebar-menu-minicart").addClass('open');
		});

		$('.wps_sfw_sub_box_prod_minus_btn').on('click', function(e) {
			e.preventDefault();

			let $btn = $(this);
			let $input = $btn.next('.wps_sfw_sub_box_prod_count'); // Get input field
			let count = parseInt($input.val()) || 0;
			//let $plusbtn = $('.wps_sfw_sub_box_prod_add_btn');
			let $plusbtn = $input.next('.wps_sfw_sub_box_prod_add_btn');
			$plusbtn.show(); 

			var wps_sfw_sub_box_price = $btn.next('.wps_sfw_sub_box_prod_count').data('wps_sfw_sub_box_price'); 

			var wps_sfw_subscription_box_price = $('.wps_sfw-sb-cta-total').data('wps_sfw_subscription_box_price');
			
			// Get the existing price from the span and convert it to a number
			if( wps_sfw_subscription_box_price == 0 ){

				var existing_price = parseFloat($('.wps_sfw-sb-cta-total span').text()) || 0;
				
				// Calculate the new total price
				var wps_sfw_sub_box_total = existing_price - wps_sfw_sub_box_price;
				
				// Update the span with the new total
				$('.wps_sfw-sb-cta-total span').text(wps_sfw_sub_box_total.toFixed(2));
			}
			
			count = count - 1;
			$input.val(count) // Hide input when 0
			if( count == 0 ){
				$input.val(count).hide(); 
				$btn.hide(); // Hide minus button
			}
			
			$btn.closest(".wps_sfw_sub_box_prod_item").removeClass("selected");

			$(".selected-items [data-product-id=" + $btn.data('product-id') + "]").remove();

			let subTotal = parseFloat($(".mini-cart-subtotal .price").data('price')) + wps_sfw_sub_box_total;
			updateMiniCartSummary(wps_sfw_sub_box_total);
			updateFreeShippingAndGiftProgressBar(subTotal);
			updateItemsCount();

			$(".postid-8480 .fixed-sidebar-menu.fixed-sidebar-menu-minicart").addClass('open');
		});

		$(document).on('click', '.selected-items svg', function(e){
			e.preventDefault();

			let productId = $(this).closest('.selected-item').data('product-id');
			$(".wps_sfw_sub_box_prod_minus_btn[data-product-id=" + productId + "]").trigger('click');
		});

		$(document).on('click', '.confirm-my-box', function(e){
			e.preventDefault();

			$('.wps_sfw_subscription_product_id').trigger('click');
		});
	
		$('.wps_sfw-empty-cart').on('click', function() {
			console.log('Empty Cart button clicked');
			// Send AJAX request to empty the cart
			$.ajax({
				url: sfw_public_param.ajaxurl,
				type: 'POST',
				data: {
					action: 'wps_sfw_sub_box_empty_cart',
					nonce: sfw_public_param.sfw_public_nonce,
				},
				success: function(response) {
					console.log('Cart emptied:', response);
					// Show success message and update cart totals
					alert('Your cart has been emptied!');
					$('.wps_sfw-empty-cart').hide();
					$(document.body).trigger('updated_cart_totals');
				}
			});
		});

		$('.wps_sfw_product_page-empty-cart').on('click', function() {
			console.log('Empty Cart button clicked');
			// Send AJAX request to empty the cart
			$.ajax({
				url: sfw_public_param.ajaxurl,
				type: 'POST',
				data: {
					action: 'wps_sfw_sub_box_empty_cart',
					nonce: sfw_public_param.sfw_public_nonce,
				},
				success: function(response) {
					console.log('Cart emptied:', response);
					// Show success message and update cart totals
					alert('Your cart has been emptied!');
					$(document.body).trigger('updated_cart_totals');
					window.location.assign(window.location.href);
				}
			});
		});

		//new code.
		$('#wps_sfw_subs_box-form').on('submit', function (e) {
			e.preventDefault();
			var $form = $('#wps_sfw_subs_box-form');
			var $current = $form.find('.wps_sfw-sb-step:visible');
			if(!validateStep($current)){
				e.preventDefault();
				return false;
			}
			let wps_sfw_subscription_product_id = $('.wps_sfw_subscription_product_id').data('subscription-box-id');
		
			let formData = {
				products: [],
				total: $('.wps_sfw-sb-cta-total>span').text(),
				wps_sfw_subscription_product_id: wps_sfw_subscription_product_id,
			};
		
			$('.wps_sfw_sub_box_prod_container .wps_sfw_sub_box_prod_item').each(function () {
				let container = $(this);
				let productId = container.find('.wps_sfw_sub_box_prod_add_btn').data('product-id');
				let quantity = container.find('.wps_sfw_sub_box_prod_count').val();
		
				if (productId && quantity > 0) {
					formData.products.push({
						product_id: productId,
						quantity: quantity
					});
				}
			});
		
			if (formData.products.length === 0) {
				$('.wps_sfw_subscription_box_error_notice').text('No products selected. Please add at least one product.').show();
				setTimeout(function() {
					$('.wps_sfw_subscription_box_error_notice').fadeOut(500);
				}, 5000); // Hide after 5 seconds
				return;
			}
		
			$.ajax({
				url: sfw_public_param.ajaxurl,
				type: 'POST',
				data: {
					action: 'wps_sfw_handle_subscription_box',
					subscription_data: JSON.stringify(formData),
					nonce: sfw_public_param.sfw_public_nonce,
				},
				success: function (response) {
					console.log('Server Response:', response);
		
					if (response.message === "Subscription added to cart!") {
						window.location.href = sfw_public_param.cart_url; // Redirect to cart page
					} else {
						// var emptyCartButton = '<button type="button" class="button wps_sfw-empty-cart" id="wps_sfw-empty-cart">Empty Cartssssdsdsds</button>';
						$('.wps_sfw_subscription_box_error_notice').html((response.data || 'Something went wrong.') ).show();
						$('.wps_sfw-empty-cart').show();
						setTimeout(function() {
							$('.wps_sfw_subscription_box_error_notice').fadeOut(500);
						}, 5000); // Hide after 5 seconds
						
					}
				},
				error: function (error) {
					console.error('Error:', error);
					$('.wps_sfw_subscription_box_error_notice').text('Failed to process request.').show();
					setTimeout(function() {
						$('.wps_sfw_subscription_box_error_notice').fadeOut(500);
					}, 5000); // Hide after 5 seconds
				}
			});
		});

		$(document).on('click','.wps_show_customer_subscription_box_popup', function(e) {
			e.preventDefault();
			$(this).next('.wps-attached-products-popup').addClass('active_customer_popup');
		});
		$(document).on('click','.wps_sfw_customer_close_popup', function(e) {
			$(this).parent('.wps-attached-products-popup').removeClass('active_customer_popup');
		});

		var $form = $('#wps_sfw_subs_box-form');
	    if(!$form.length) return;

	    var totalSteps = $form.find('.wps_sfw-sb-step').length;
	    var $prevBtn = $form.find('.wps_sfw-sb-prev');
	    var $nextBtn = $form.find('.wps_sfw-sb-next');
	    var $addBtn  = $form.find('.wps_sfw_subscription_product_id');
	    var $error   = $form.find('.wps_sfw_subscription_box_error_notice');

	    function showError(msg){
			$error.text(msg).show();
			setTimeout(function() {
				$error.fadeOut(500);
			}, 5000); // Hide after 5 seconds
	    }
	    function clearError(){
	        $error.hide().text('');
	    }

	    function validateStep($step){
	        var min = parseInt($step.data('min-num'), 10) || 0;
	        var max = parseInt($step.data('max-num'), 10) || 0;
	        if(min === 0 && max === 0){ return true; }

	        var totalQty = 0;
	        $step.find('.wps_sfw_sub_box_prod_count').each(function(){
	            totalQty += parseInt($(this).val(), 10) || 0;
	        });
			console.log(totalQty, min, max);
	        if(min && totalQty < min){
	            showError("Please select at least " + min + " products in this step.");
	            return false;
	        }
	        if(max && totalQty > max){
	            showError("You can select maximum " + max + " products in this step.");
	            return false;
	        }
	        return true;
	    }

	    function showStep(index){
	        $form.find('.wps_sfw-sb-step').hide();
	        $form.find('.wps_sfw-sb-step[data-step-index="'+index+'"]').show();
	        clearError();

	        if(index <= 1){
	            $prevBtn.hide();
	        } else {
	            $prevBtn.show().data('goto', index-1);
	        }

	        if(index >= totalSteps){
	            $nextBtn.hide();
	            $addBtn.show();
	        } else {
	            $nextBtn.show().data('goto', index+1);
	            $addBtn.hide();
	        }
	    }

	    // Init first step
	    showStep(1);

	    $prevBtn.on('click', function(e){
	        e.preventDefault();
	        var gotoIndex = parseInt($(this).data('goto'), 10);
	        showStep(gotoIndex);
	    });

	    $nextBtn.on('click', function(e){
	        e.preventDefault();
	        var $current = $form.find('.wps_sfw-sb-step:visible');
			 if(!validateStep($current)){
	            e.preventDefault();
	            return false;
	        }
	        var gotoIndex = parseInt($(this).data('goto'), 10);
	        showStep(gotoIndex);
	    });

	    $form.on('submit', function(e){
	        var $current = $form.find('.wps_sfw-sb-step:visible');
	        if(!validateStep($current)){
	            e.preventDefault();
	            return false;
	        }
	    });
	});
})( jQuery );
