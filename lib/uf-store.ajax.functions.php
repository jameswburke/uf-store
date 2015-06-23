<?php
defined('ABSPATH') or die("No script kiddies please!");

header('Content-Type: application/json');

//Register actions
add_action('wp_ajax_get_cart', 'get_cart');
add_action('wp_ajax_nopriv_get_cart', 'get_cart');

add_action('wp_ajax_add_to_cart', 'add_to_cart');
add_action('wp_ajax_nopriv_add_to_cart', 'add_to_cart');

add_action('wp_ajax_update_cart', 'update_cart');
add_action('wp_ajax_nopriv_update_cart', 'update_cart');

add_action('wp_ajax_remove_from_cart', 'remove_from_cart');
add_action('wp_ajax_nopriv_remove_from_cart', 'remove_from_cart');

add_action('wp_ajax_calculate_shipping', 'calculate_shipping');
add_action('wp_ajax_nopriv_calculate_shipping', 'calculate_shipping');

add_action('wp_ajax_cart_info', 'cart_info');
add_action('wp_ajax_nopriv_cart_info', 'cart_info');

add_action('wp_ajax_checkout', 'checkout');
add_action('wp_ajax_nopriv_checkout', 'checkout');


function get_cart(){
	header('Content-Type: application/json');
	echo json_encode(array('cart' => UFStoreCart::getCart()));
	die();
}

function add_to_cart(){
	header('Content-Type: application/json');
	$ufstore = UFStore::instance();
	$errors = array();
	$meta_array = array(); // Build meta data on top of this

	$id = $_POST['product_id'];
	$quantity = $_POST['product_quantity'];

	//Validation of input
	if(!validProduct($id)){ array_push($errors, array('product_quantity' => 'Please enter a valid product ID')); }
	if(!validQuantity($quantity)){ array_push($errors, array('product_quantity' => 'Quantity must be greater than 0')); }
	if(!empty($errors)){
		http_response_code(400);
		echo json_encode(array('errors' => $errors));
		die();
	}

	//Generate the meta array
	foreach($_POST as $key => $value) {
		if (strpos($key, 'meta_') === 0) {
			$meta_array[$key] = $value;
		}
	}

	$cart = UFStoreCart::addToCart($id, $quantity, $meta_array);
	echo json_encode(array('cart' => $cart));

	die();
}


function update_cart(){
	header('Content-Type: application/json');
	$ufstore = UFStore::instance();
	$errors = array();

	$id = $_POST['product_id'];
	$quantity = $_POST['product_quantity'];
	$unique_id = $_POST['product_unique_id'];

	//Validation of input
	if(!validProduct($id)){ array_push($errors, array('product_id' => 'Please enter a valid product ID')); }
	if(!validQuantity($quantity)){ array_push($errors, array('product_quantity' => 'Quantity must be greater than 0')); }
	if(!isset($unique_id)){ array_push($errors, array('product_unique_id' => 'Please enter a unique ID')); }
	if(!empty($errors)){
		http_response_code(400);
		echo json_encode(array('errors' => $errors));
		die();
	}

	$cart = UFStoreCart::updateCart($id, $quantity, $unique_id);
	echo json_encode(array('cart' => $cart));
	die();
}



function remove_from_cart(){
	header('Content-Type: application/json');
	$ufstore = UFStore::instance();
	$errors = array();

	$id = $_POST['product_id'];
	$unique_id = $_POST['product_unique_id'];

	//Validation of input
	if(!validProduct($id)){ array_push($errors, array('product_id' => 'Please enter a valid product ID')); }
	if(!isset($unique_id)){ array_push($errors, array('product_unique_id' => 'Please enter a unique ID')); }
	if(!empty($errors)){
		http_response_code(400);
		echo json_encode(array('errors' => $errors));
		die();
	}

	$cart = UFStoreCart::removeFromCart($id, $unique_id);
	echo json_encode(array('cart' => $cart));
	die();
}



function calculate_shipping(){
	header('Content-Type: application/json');
	$ufstore = UFStore::instance();
	$errors = array();

	$country = $_POST['country'];
	$zipcode = $_POST['shipping_to'];

	//Eventually validate that it's a legit country
	if($country == ''){
		$country = 'United States';
	}

	//Validation of input
	if((!isset($zipcode)) && ($country == 'United States') ){ array_push($errors, array('shipping_to' => 'Invalid Postal Code')); }
	if(!isset($country)){ array_push($errors, array('country' => 'Please enter a country')); }
	if(!empty($errors)){
		http_response_code(400);
		echo json_encode(array('errors' => $errors));
		die();
	}

	$shippingOptions = UFStoreShipping::getShipping($zipcode, $country);
	echo json_encode(array('shipping_options' => $shippingOptions));
	die();
}

function checkout(){
	header('Content-Type: application/json');
	$ufstore = UFStore::instance();
	$errors = array();

	//Super simple validation
	if(strlen($_POST['fullname']) <= 0){ array_push($errors, array('fullname' => 'Please enter your name')); }
	if(strlen($_POST['email']) <= 0){ array_push($errors, array('email' => 'Please enter your email address')); }
	if(strlen($_POST['address1']) <= 0){ array_push($errors, array('address1' => 'Please enter your address')); }
	if(strlen($_POST['city']) <= 0){ array_push($errors, array('city' => 'Please enter your city')); }
	if(strlen($_POST['country']) <= 0){ array_push($errors, array('country' => 'Please enter your country')); }
	if(strlen($_POST['state']) <= 0){ array_push($errors, array('state' => 'Please enter your state')); }
	if($_POST['country'] == 'United States'){
		if((strlen($_POST['zipcode']) <= 0) || (!is_numeric($_POST['zipcode']))|| (strlen($_POST['zipcode']) !== 5)){ array_push($errors, array('zipcode' => 'Invalid Postal Code')); }
	}else{
		if(strlen($_POST['zipcode']) <= 0){ array_push($errors, array('zipcode' => 'Please enter your zipcode')); }
	}
	if((strlen($_POST['shipping_name']) <= 0) || (strlen($_POST['shipping_cost']) <= 0)){ array_push($errors, array('shippingError' => 'Please select a shiping option')); }
	if(strlen($_POST['stripeToken']) <= 0){ array_push($errors, array('stripeToken' => 'No Stripe Token')); }

	// if(UFStoreCart::getCartCount() <= 0){ array_push($errors, array('cart' => 'Shopping cart is empty')); }

	//Return Errors
	if(!empty($errors)){
		http_response_code(400);
		echo json_encode(array('errors' => $errors));
		die();
	}


	//This needs to be cleaned up
	//SANATIZE INPUT !!!
	$customer = array(
		'name' => $_POST['fullname'],
		'email' => $_POST['email'],
		'address1' => $_POST['address1'],
		'address2' => $_POST['address2'],
		'city' => $_POST['city'],
		'country' => $_POST['country'],
		'state' => $_POST['state'],
		'zipcode' => $_POST['zipcode']
	);
	$cart_info = array(
		'cart_subtotal' => $_POST['cart_subtotal'],
		'cart_shipping' => $_POST['cart_shipping'],
		'cart_total' => $_POST['cart_total'],
		'shipping_name' => $_POST['shipping_name'],
		'shipping_cost' => $_POST['shipping_cost'],
		'shipping_speed' => $_POST['shipping_speed']
	);
	$stripeToken = $_POST['stripeToken'];


	$result = UFStoreCartCheckout::checkout($customer, $cart_info, $stripeToken);
	echo json_encode($result);
	die();
}



function cart_info(){
	header('Content-Type: application/json');
	$ufstore = UFStore::instance();

	$info = array(
		'cart_count' => UFStoreCart::getCartCount(),
		'total_cost' => UFStoreCart::getCartCost()
	);

	echo json_encode(array('cart_info' => $info));
	die();
}

// cart_info
// checkout


// Validation Shortcuts
function validProduct($id = 0){
	if((!isset($id)) || ((int)$id < 1) ){
		return false;
	}else{
		$product = get_post($id);
		if( ($product->post_type !== 'ufstore') || ($product->post_status !== 'publish') ){
			return false;
		}
	}
	return true;
}

function validQuantity($quantity = 0){
	if((int)($quantity) <= 0){
		return false;
	}
	return true;
}