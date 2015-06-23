function ucwords(str,force){
	str=force ? str.toLowerCase() : str;  
	return str.replace(/(\b)([a-zA-Z])/g,
	function(firstLetter){
		return firstLetter.toUpperCase();
	});
}

jQuery.fn.cart_info = function(){
	return{
		//Update the cart total. That's it
		update_cart_count: function(){
			var parent = this;
			jQuery.ajax({
				url: MyAjax.ajaxurl,
				data: { action: 'cart_info' },
				type: 'POST',
				dataType: 'JSON',
				success: function(data){
					jQuery('.cart-count a').html('Cart ('+data.cart_info.cart_count+')');
				},
				failure: function(error){
					console.log(error);
				}
			});
		},
		get_cart_cost: function(callback){
			var parent = this;
			jQuery.ajax({
				url: MyAjax.ajaxurl,
				data: { action: 'cart_info' },
				type: 'POST',
				dataType: 'JSON',
				success: function(data){
					callback(data.cart_info.total_cost);
				},
				failure: function(error){
					console.log(error);
				}
			});
		}
	}
}

jQuery(document).ready(function($) {
	//Always update cart info in navbar
	var cart_info = $('node').cart_info();
	cart_info.update_cart_count()

	$('#store-form-alert .close').click(function(e){
		$(this).parent().hide();
	});
});