
jQuery(document).ready(function($) {

	// $('[title="Cart (0)"]').html('Cart ('+cart_count+')');

	$('#store-form-alert .close').click(function(e){
		$(this).parent().hide();
	});

	//Execute jQuery
	$('.store-form').submit(function(e){
		e.preventDefault();
		var submit_button = $('#add-to-cart');

		var data = $(this).serialize();

		submit_button.html('<i class="fa fa-spinner fa-spin"></i>');

		$.ajax({
			url: MyAjax.ajaxurl,
			data: data,
			type: 'POST',
			dataType: 'JSON',
			success: function(data){
				console.log(data);
				$('#store-form-alert').show();
				submit_button.html('Add to Cart');
				// console.log(data);
			},
			failure: function(error){
				console.log(error);
			}
		});
	});


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
				console.log(data);
				row_data.find('.subtotal').html('$'+((row_data.data('product-price') * row_data.find('.product-quantity').val())/100));
				icon.removeClass('fa-spin');
				update_total();
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
				console.log(data);
				row_data.remove();
				update_total();
			},
			failure: function(error){
				console.log(error);
			}
		});
	});

	var update_total = function(){
		var new_total = 0;
		$('.product-row').each(function(index){
			new_total += ($(this).find('.product-quantity').val() * $(this).data('product-price'));
		});
		$('.cart-total').html('Total $'+new_total);
	}

	$('.calculate-shipping').submit(function(e){
		e.preventDefault();

		var submit_button = $('#calculate-shipping-submit');
		var form = $(this);
		var data = $(this).serialize();
		submit_button.html('<i class="fa fa-spinner fa-spin"></i>');

		$.ajax({
			url: MyAjax.ajaxurl,
			data: data,
			type: 'POST',
			dataType: 'JSON',
			success: function(data){
				console.log(data);
				submit_button.html('Calculate Shipping');
				new_total = $('.cart-cost').data('cost') + (data.total_cost.total*100);

				$('.cart-shipping').data('cost', data.total_cost.total).html('Shipping: $'+data.total_cost.total);
				$('.cart-total').data('cost', new_total).html('Total: $' + (new_total/100));
			},
			error: function(error){
				console.log(error);
				submit_button.html('Calculate Shipping');
				$('.cart-shipping').html('Please enter a valid zipcode');
			}
		});
	});

	var test = function(){
		// new_total = $('.cart-cost').data('cost') + $('.cart-shipping').data('cost');

		// $('.cart-shipping').data('cost', data.total_cost.total).html('Shipping: $'+data.total_cost.total);
		// $('.cart-total').data('cost', new_total).html('Total: $' + (new_total/100));-total').data('cost', new_total).html('Total: $' + (new_total/100));
	}



	/* Stripe Stuff */
	$('#payment-form').submit(function(event) {
		//Stripe.setPublishableKey('pk_live_saoQtK3UE1KZz8vnVsT0pv6H');
		Stripe.setPublishableKey('pk_test_Nok4HNZmtFWO9bW47jWrNiUo');
		var $form = $(this);
		// Disable the submit button to prevent repeated clicks
		$form.find('button').prop('disabled', true);

		Stripe.card.createToken($form, stripeResponseHandler);

		// // Prevent the form from submitting with the default action
		return false;
	});

	function stripeResponseHandler(status, response) {
			console.log(5);
			var $form = $('#payment-form');
			if (response.error) {
				// Show the errors on the form
				$form.find('.payment-errors').text(response.error.message);
				$form.find('button').prop('disabled', false);
			} else {
				// response contains id and card, which contains additional card details
				var token = response.id;
				// Insert the token into the form so it gets submitted to the server
				$form.append($('<input type="hidden" name="stripeToken" />').val(token));
				//console.log($form.serialize());

				var data = {
					action: 'checkout',
					cus_name: $('#cus_name').val(),
					email: $('#email').val(),
					address1: $('#address1').val(),
					address2: $('#address2').val(),
					city: $('#city').val(),
					country: $('#country').val(),
					state: $('#state').val(),
					zipcode: $('#zipcode').val(),
					stripeToken: token
				};

				$.ajax({
					url: MyAjax.ajaxurl,
					data: data,
					type: 'POST',
					dataType: 'JSON',
					success: function(data){
						console.log(data);
						$form.find('button').prop('disabled', false);
						$form.find('input[name="stripeToken"]').remove();
						window.location = "http://127.0.0.1/thegoodproject/cart/success/";
					},
					failure: function(error){
						console.log(error);
						$form.find('button').prop('disabled', false);
						$form.find('input[name="stripeToken"]').remove();
					}
				});
				// and submit
				//$form.get(0).submit();
			}
		};
});