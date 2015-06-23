jQuery(document).ready(function($) {
	$('a[data-ufstore-new-color]').click(function(e){
		e.preventDefault();
		var color = $(this).data('ufstore-new-color');
		console.log(color);
		$('#meta_color').attr('value', color);
		$('[data-ufstore-current-color]').html(ucwords(color.replace("-", " ")));
		$('[data-ufstore-color-show]').hide().find('select').prop('selectedIndex', 0).prop('required', false);
		$('[data-ufstore-color-show='+color+']').show().find('select').prop('required', true);
	});

	//Update large image from any applicaple thumbnails
	$('a[data-ufstore-large-image]').click(function(e){
		e.preventDefault();
		var large_photo = $(this).data('ufstore-large-image');
		$('#ufstore-product-large-photo').attr('src', large_photo);
	});

	//Add to the cart
	$('.cart-add').submit(function(e){
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
				window.location = ufcart_cart_url;
			},
			error: function(error){
				console.log(error);
			}
		});
	});
});
