//Create node to update cart at any point
jQuery.fn.checkout_functions = function(){
	return{
		update_shipping: function(){
			//Just incase we get a shipping error
			jQuery('#ufstore-checkout-total').hide();
			jQuery('.alert').hide();
			jQuery('#shipping-options').html('<i class="fa fa-2x fa-spinner fa-spin"></i>');

			jQuery('.ufstore-checkout-shipping').html('<small>Please enter a shipping address</small>');

			var data = {
				action: 'calculate_shipping',
				shipping_to: jQuery('#zipcode').val(),
				country: jQuery('#country').val()
			}


			jQuery.ajax({
				url: MyAjax.ajaxurl,
				data: data,
				type: 'POST',
				dataType: 'JSON',
				success: function(data){
					jQuery('#shipping-options-row').show();
					jQuery('#shipping_name').val('');
					jQuery('#shipping_cost').val('');
					jQuery('#shipping_speed').val('');

					//Does USPS values exist?
					if(data.shipping_options.usps.length > 0){
						jQuery('node').checkout_functions().generate_shipping_options_html(data.shipping_options, function(return_html){
							jQuery('#shipping-options').html(return_html);
						});

					//Non USPS calculations
					}else{
						var flat = 0;
						if(data.shipping_options.flat){
							flat = data.shipping_options.flat;
						}//Add group costs to flat
						if(data.shipping_options.groups){
							flat += data.shipping_options.groups;
						}
						//Remove spinner
						jQuery('#shipping-options').html('');
						//Show ost
						jQuery('#shipping-flat-row').html('<strong>$'+(flat/100).toFixed(2)+'</strong>');
						//Add values to our shipping form
						jQuery('#shipping_name').val('flat');
						jQuery('#shipping_cost').val(flat);

						//Show the total
						jQuery('node').checkout_functions().show_total();
					}
				},
				error: function(error){
					jQuery('#shipping-options').html('')

					for(var i = 0, len = error.responseJSON.errors.length; i < len; i++){
						for(var key in error.responseJSON.errors[i]){
							if(key == "generalError"){
								jQuery('#generalError').show().html(error.responseJSON.errors[i][key]);
							}else if(key == "shippingError"){
								jQuery('#shippingError').show().html(error.responseJSON.errors[i][key]);
							}else{
								jQuery('#shippingError').show().html(error.responseJSON.errors[i][key]);
								jQuery('#'+key).after('<span class="help-block">'+error.responseJSON.errors[i][key]+'</span>');
								jQuery('#'+key).closest('.form-group').addClass('has-error');
							}
						}
					}
				}
			});
		},

		//Creates the radio buttons, and combines with any flat rates
		generate_shipping_options_html: function(options, callback){
			var flat = 0;
			if(options.flat){
				flat = options.flat;
			}
			if(options.groups){
				flat += options.groups;
			}
			var return_string = "";
			for (var x = 0; x < options.usps.length; x++) {
				return_string += '<div class="radio">';
				return_string += '	<label>';
				return_string += '		<input type="radio" name="shippingOptions" id="shippingOptions'+x+'" value="option'+x+'" data-shipping-cost="'+(options.usps[x].cost+flat)+'" data-shipping-name="'+options.usps[x].type+'" data-shipping-speed="'+options.usps[x].speed+'">';
				return_string += '		<p class="margin-bottom-none"><strong>$'+((options.usps[x].cost+flat)/100).toFixed(2)+'</strong> - '+options.usps[x].type+'</p>';
				return_string += '		<p><small>'+options.usps[x].speed+'</small></p>';
				return_string += '	</label>';
				return_string += '</div>';
			};
			callback(return_string);
		},

		show_total: function(){
			//Show total
			jQuery('#ufstore-checkout-total').show();
			var subtotal = jQuery('.ufstore-checkout-subtotal').data('subtotal');
			var shipping = Number(jQuery('#shipping_cost').val());

			console.log(subtotal + shipping);

			jQuery('.ufstore-checkout-total').html(((subtotal + shipping)/100).toFixed(2));
		},


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
				jQuery('node').checkout_functions().update_cart_subtotal(value);
				jQuery('node').checkout_functions().update_cart_shipping();
			});
		}
	}
}


jQuery(document).ready(function($) {

	//Update shipping when zipcode or country updated
	$('#zipcode').bind("change", function(e){
		e.preventDefault();
		var ziplength = $(this).val().length;
		$(this).find('.help-block').remove();

		if($('#country').val() == 'United States'){
			$('node').checkout_functions().update_shipping();
		}
	});

	$('#country').change(function(e){
		e.preventDefault();
		$('node').checkout_functions().update_shipping();
	});


	//Update pricing whenever new shipping is selected

	$('body').on('change', "input[name='shippingOptions']", function(){
		var checked = $("input[name='shippingOptions']:checked");
		$('#shipping_name').val(checked.data('shipping-name'));
		$('#shipping_cost').val(checked.data('shipping-cost'));
		$('#shipping_speed').val(checked.data('shipping-speed'));

		$('node').checkout_functions().usps_shipping = checked.data('shipping-cost');

		$('node').checkout_functions().show_total();
	});



	/* Stripe Stuff */
	$('#checkout-form').submit(function(event) {
		//House cleaning
		$('#paymentErrors').hide();
		$('#shippingError').hide();
		$('.has-error').removeClass('has-error');
		$('.help-block').remove();

		Stripe.setPublishableKey(ufcart_stripe);
		var $form = $(this);

		// Disable the submit button to prevent repeated clicks
		$form.find('button').prop('disabled', true);

		Stripe.card.createToken($form, stripeResponseHandler);

		// Prevent the form from submitting with the default action
		return false;
	});

	function stripeResponseHandler(status, response) {
		var $form = $('#checkout-form');
		if (response.error) {
			// Show the errors on the form
			$form.find('#paymentErrors').show().html(response.error.message);
			$form.find('button').prop('disabled', false);
		} else {
			// response contains id and card, which contains additional card details
			var token = response.id;
			// Insert the token into the form so it gets submitted to the server
			$form.append($('<input type="hidden" name="stripeToken" />').val(token));
			//console.log($form.serialize());

			//House cleaning
			$('.help-block').remove();

			var data = {
				action: 'checkout',
				fullname: $('#fullname').val(),
				email: $('#email').val(),
				address1: $('#address1').val(),
				address2: $('#address2').val(),
				city: $('#city').val(),
				country: $('#country').val(),
				state: $('#state').val(),
				zipcode: $('#zipcode').val(),
				cart_subtotal: $('.ufstore-checkout-subtotal').val(),
				cart_shipping: $('.ufstore-checkout-shipping').val(),
				cart_total: $('.ufstore-checkout-total').val(),
				shipping_name: $('#shipping_name').val(),
				shipping_cost: $('#shipping_cost').val(),
				shipping_speed: $('#shipping_speed').val(),
				stripeToken: token
			};

			console.log(data);

			$.ajax({
				url: MyAjax.ajaxurl,
				data: data,
				type: 'POST',
				dataType: 'JSON',
				success: function(data){
					console.log(data);
					$form.find('button').prop('disabled', false);
					$form.find('input[name="stripeToken"]').remove();
					window.location = ufcart_success_url;
				},
				error: function(error){
					console.log(error);
					for(var i = 0, len = error.responseJSON.errors.length; i < len; i++){
						for(var key in error.responseJSON.errors[i]){
							if(key == "generalError"){
								$('#generalError').show().html(error.responseJSON.errors[i][key]);
							}else if(key == "shippingError"){
								$('#shippingError').show().html(error.responseJSON.errors[i][key]);
							}else{
								$('#'+key).after('<span class="help-block">'+error.responseJSON.errors[i][key]+'</span>');
								$('#'+key).closest('.form-group').addClass('has-error');
							}
						}
					}
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
