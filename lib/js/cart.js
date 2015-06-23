//Create node to update cart at any point
jQuery.fn.cart_functions = function(){
	return{
		//Pass a value in pennies to update the cost
		update_cart_subtotal: function(cost){
			var parent = this;
			var subtotal = jQuery('.ufstore-cart-subtotal');
			subtotal.html((cost/100).toFixed(2));
		},

		//Trigger when updated amount or removed something
		update_entire_cart: function(){
			jQuery('.ufstore-cart-subtotal').html('<i class="fa fa-spinner fa-spin"></i>');
			jQuery('.ufstore-cart-shipping').html('<i class="fa fa-spinner fa-spin"></i>');

			//Update the subtotal
			jQuery('node').cart_info().get_cart_cost(function(value){
				jQuery('node').cart_functions().update_cart_subtotal(value);
				// jQuery('node').cart_functions().update_cart_shipping();
			});


		}
	}
}


jQuery(document).ready(function($) {
	//Load global functions into cart_info
	var cart_info = $('node').cart_info();
	var cart_functions = $('node').cart_functions();
	$('.ufstore-cart-subtotal').html('<i class="fa fa-spinner fa-spin"></i>');

	//Compute total on first page load
	cart_info.get_cart_cost(function(value){
		cart_functions.update_cart_subtotal(value);
	})


	$('.cart-update').click(function(e){
		e.preventDefault();
		var icon = $(this).children('i');
		var row_data = $(this).closest('tr');

		var data = {
			action: 'update_cart',
			product_id: row_data.data('product-id'),
			product_unique_id: row_data.data('unique-id'),
			product_quantity: row_data.find('.product-quantity').val()
		};

		icon.addClass('fa-spin');

		$.ajax({
			url: MyAjax.ajaxurl,
			data: data,
			type: 'POST',
			dataType: 'JSON',
			success: function(data){
				icon.removeClass('fa-spin');
				row_data.find('.subtotal').html('$'+((row_data.data('product-price') * row_data.find('.product-quantity').val())/100));
				cart_functions.update_entire_cart();
			},
			failure: function(error){
				console.log(error);
			}
		});
	});


	$('.cart-remove').click(function(e){
		e.preventDefault();
		var row_data = $(this).closest('tr');

		var data = {
			action: 'remove_from_cart',
			product_id: row_data.data('product-id'),
			product_unique_id: row_data.data('unique-id')
		};

		$(this).children('i').removeClass('fa-trash-o').addClass('fa-spinner fa-spin');

		$.ajax({
			url: MyAjax.ajaxurl,
			data: data,
			type: 'POST',
			dataType: 'JSON',
			success: function(data){
				row_data.remove();
				cart_functions.update_entire_cart();
			},
			failure: function(error){
				console.log(error);
			}
		});
	});


});