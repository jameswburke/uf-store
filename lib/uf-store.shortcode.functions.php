<?php
defined('ABSPATH') or die("No script kiddies please!");

//Main store page
add_shortcode('ufstore', 'ufstore');
function ufstore($atts) {
	global $content;
	ob_start();

	$args = array(
		'post_type' => 'ufstore',
		'posts_per_page' => -1,
		'tax_query' => array()
	);
	if(isset($atts['category'])){
		array_push($args['tax_query'], 
			array(
				'taxonomy' => 'ufstore_category',
				'field'    => 'slug',
				'terms'    => $atts['category'],
			)
		);
	}
	$query = new WP_Query( $args );
	
	if(file_exists(get_template_directory() . '/ufstore/store-front.php')){
		include get_template_directory() . '/ufstore/store-front.php';
	}else{
		include dirname( __FILE__ ).'/../templates/store-front.php';
	}
	$output = ob_get_clean();
	return $output;
}


//Cart
add_shortcode('ufstorecart', 'ufstorecart');
function ufstorecart($atts){
	$ufstore = UFStore::instance();
	global $content;
	ob_start();

	$cart = UFStoreCart::getCart();
	$cart_total = UFStoreCart::getCartCost();
	
	//Load cart javascript
	wp_enqueue_script(
		'ufstore-js-cart',
		plugins_url( '/js/cart.js', __FILE__ ),
		array('jquery'),
		false,
		true
	);

	$store_page = get_field('store_page', 'option');
	$shopping_cart = get_field('shopping_cart', 'option');
	$checkout_page = get_field('checkout_page', 'option');
	$success_page = get_field('success_page', 'option');

	if(file_exists(get_template_directory() . '/ufstore/cart.php')){
		include get_template_directory() . '/ufstore/cart.php';
	}else{
		include dirname( __FILE__ ).'/../templates/cart.php';
	}
	$output = ob_get_clean();
	return $output;
}

add_shortcode('ufstorecheckout', 'ufstorecheckout');
function ufstorecheckout($atts){
	$ufstore = UFStore::instance();
	global $content;
	ob_start();

	$cart = UFStoreCart::getCart();
	$cart_total = UFStoreCart::getCartCost();

	//Load cart javascript
	wp_enqueue_script(
		'ufstore-js-cart',
		plugins_url( '/js/checkout.js', __FILE__ ),
		array('jquery'),
		false,
		true
	);

	$store_page = get_field('store_page', 'option');
	$shopping_cart = get_field('shopping_cart', 'option');
	$checkout_page = get_field('checkout_page', 'option');
	$success_page = get_field('success_page', 'option');

	if(file_exists(get_template_directory() . '/ufstore/checkout.php')){
		include get_template_directory() . '/ufstore/checkout.php';
	}else{
		include dirname( __FILE__ ).'/../templates/checkout.php';
	}
	$output = ob_get_clean();
	return $output;
}

add_shortcode('ufstoresuccess', 'ufstoresuccess');
function ufstoresuccess($atts) {
	$ufstore = UFStore::instance();
	global $content;
	ob_start();

	$cart = UFStoreCart::getCart();
	$cart_total = UFStoreCart::getCartCost();
	
	if(file_exists(get_template_directory() . '/ufstore/success.php')){
		include get_template_directory() . '/ufstore/success.php';
	}else{
		include dirname( __FILE__ ).'/../templates/success.php';
	}
	//Empty the shopping cart
	$wp_session['cart'] = array();
	
	$output = ob_get_clean();
	return $output;
}