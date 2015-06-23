<?php
defined('ABSPATH') or die("No script kiddies please!");

class UFStoreCart{

	public $cart;

	public $wp_session;

	public $shipping;

	public $customer;

	public $cart_info;

	public function __construct(){
		//Get the WP Session object
		$this->wp_session = WP_Session::get_instance();
		if(!isset($this->wp_session['cart'])){
			$this->wp_session['cart'] = array();
		}
		//Get a cart array
		$this->cart = $this->wp_session['cart']->toArray();

		//Setup Shipping Class
		$this->shipping = new UFStoreShipping();

		//Global functions
		wp_enqueue_script(
			'ufstore-js-global',
			plugins_url( '/js/global.js', __FILE__ ),
			array('jquery'),
			false,
			true
		);
		wp_enqueue_script(
			'ufstore-js-single',
			plugins_url( '/js/single.js', __FILE__ ),
			array('jquery'),
			false,
			true
		);
		wp_enqueue_script(
			'ufstore-stripe',
			'https://js.stripe.com/v2/',
			array('jquery'),
			false,
			true
		);

		wp_localize_script( 'ufstore-js-global', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );


		//Register actions
		add_action('wp_ajax_add_to_cart', array($this, 'add_to_cart'));
		add_action('wp_ajax_nopriv_add_to_cart', array($this, 'add_to_cart'));

		add_action('wp_ajax_update_cart', array($this, 'update_cart'));
		add_action('wp_ajax_nopriv_update_cart', array($this, 'update_cart'));

		add_action('wp_ajax_remove_from_cart', array($this, 'remove_from_cart'));
		add_action('wp_ajax_nopriv_remove_from_cart', array($this, 'remove_from_cart'));
		
		add_action('wp_ajax_calculate_shipping', array($this, 'calculate_shipping'));
		add_action('wp_ajax_nopriv_calculate_shipping', array($this, 'calculate_shipping'));

		add_action('wp_ajax_cart_info', array($this, 'cart_info'));
		add_action('wp_ajax_nopriv_cart_info', array($this, 'cart_info'));

		add_action('wp_ajax_checkout', array($this, 'checkout'));
		add_action('wp_ajax_nopriv_checkout', array($this, 'checkout'));
	}


	//Get general info about the cart
	function cart_info(){
		$info = array(
			'cart_count' => 0,
			'total_cost' => 0,
		);

		foreach($this->cart as $product_id => $products):
			foreach($products as $product):
				$info['cart_count'] += $product['quantity'];
				$info['total_cost'] += ($product['quantity'] * get_field('base_price', $product_id));
			endforeach;
		endforeach;

		header('Content-Type: application/json');
		echo json_encode(array('cart_info' => $info));

		die(); // this is required to terminate immediately and return a proper response
	}

	function add_to_cart(){
		//Dummy Data
		// $this->cart = array(
		// 	'25' => array(
		// 		array(
		// 			'quantity' => 2,
		//			'unique_id' => '2482c045c4add14349a7bdb4ffcb1a28',
		// 			'meta' => array(
		// 				'meta_size' => 'small',
		// 				'meta_player-number' => 'alexa'
		// 			)
		// 		)
		// 	),
		// 	'31' => array(
		// 		array(
		// 			'quantity' => 2,
		// 			'meta' => array(
		// 				'meta_size' => 'small',
		// 				'meta_player-number' => 'james'
		// 			)
		// 		),
		// 		array(
		// 			'quantity' => 4,
		// 			'meta' => array(
		// 				'meta_size' => 'large',
		// 				'meta_player-number' => 'steve'
		// 			)
		// 		)
		// 	),
		// );


		//SANATIZE INPUT !!!
		$new_product_id = $_POST['product_id'];
		$new_product_quantity = $_POST['product_quantity'];
		$new_product_meta = array();
		$new_product_unique_id = md5(uniqid(rand(), true));

		//Generate the meta array
		foreach($_POST as $key => $value) {
			if (strpos($key, 'meta_') === 0) {
				$new_product_meta[$key] = $value;
			}
		}

		// //Is this item already in the cart?
		if(array_key_exists($new_product_id, $this->cart)){			
			//Does our meta set already exist?
			$exists = false;
			foreach($this->cart[$new_product_id] as &$product){
				if($product['meta'] === $new_product_meta){
					$product['quantity'] = $new_product_quantity;
					$exists = true;
				}
			}

			if($exists == false){
				array_push(
					$this->cart[$new_product_id],
					array(
						'quantity' => $new_product_quantity,
						'unique_id' => $new_product_unique_id,
						'meta' => $new_product_meta
					)
				);
			}

		}else{
			$this->cart[$new_product_id] = array(
				array(
					'quantity' => $new_product_quantity,
					'unique_id' => $new_product_unique_id,
					'meta' => $new_product_meta
				)
			);
		}

		$this->wp_session['cart'] = $this->cart;
		// $this->wp_session['cart'] = array();

		header('Content-Type: application/json');
		echo json_encode(array('cart' => $this->cart));

		die(); // this is required to terminate immediately and return a proper response
	}


	function update_cart(){
		header('Content-Type: application/json');
		$errors = array();		
		$product_id = $_POST['product_id'];
		$product_unique_id = $_POST['product_unique_id'];
		$product_quantity = $_POST['product_quantity'];

		if((int)($product_quantity) <= 0){ array_push($errors, array('product_quantity' => 'Quantity must be greater than 0')); }

		//Return Errors
		if(!empty($errors)){
			http_response_code(400);
			echo json_encode(array('errors' => $errors));
			die();
		}

		//Is this item already in the cart?
		if(array_key_exists($product_id, $this->cart)){
			foreach($this->cart[$product_id] as $key => &$product){
				if($product['unique_id'] == $product_unique_id){
					$product['quantity'] = $product_quantity;
				}
			}
		}

		$this->wp_session['cart'] = $this->cart;
		echo json_encode($this->cart);
		die(); // this is required to terminate immediately and return a proper response
	}


	function remove_from_cart(){
		//SANATIZE INPUT !!!
		$product_id = $_POST['product_id'];
		$product_unique_id = $_POST['product_unique_id'];

		//Is this item already in the cart?
		if(array_key_exists($product_id, $this->cart)){
			foreach($this->cart[$product_id] as $key => &$product){
				if($product['unique_id'] == $product_unique_id){
					unset($this->cart[$product_id][$key]);
				}
			}
			if(empty($this->cart[$product_id])){
				unset($this->cart[$product_id]);
			}
		}

		$this->wp_session['cart'] = $this->cart;

		header('Content-Type: application/json');
		echo json_encode($this->cart);

		die(); // this is required to terminate immediately and return a proper response
	}

	function calculate_shipping($shipping_to = null, $country = null){
		header('Content-Type: application/json');
		$errors = array();
		$shipping_info = array();

		$usps_package = array(
			'total_cost' => 0,
			'total_weight' => 0
		);

		$shipping_costs = array(
			'total' => 0,
			'flat' => 0,
			'included' => 0,
			'usps' => 0,
		);

		//Super simple validation
		if($shipping_to == null){ $shipping_to = $_POST['shipping_to']; }
		if($country == null){ $country = $_POST['country']; }


		if(strlen($_POST['country']) <= 0){ array_push($errors, array('country' => 'Invalid Country')); }

		//Require zip if US
		if($country == 'United States'){
			if((strlen($_POST['shipping_to']) <= 0) || (!is_numeric($shipping_to))){ array_push($errors, array('shipping_to' => 'Invalid Postal Code')); }
		}
		

		//Return Errors
		if(!empty($errors)){
			http_response_code(400);
			echo json_encode(array('errors' => $errors));
			die();
		}

		
		// Generate an array to loop through
		//Collect each cart's shipping info
		foreach($this->cart as $product_id => $products):
			foreach($products as $product):
				array_push($shipping_info, 
					array(
						'id' => $product['unique_id'],
						'price' => get_field('base_price', $product_id),
						'quantity' => $product['quantity'],
						'product_id' => $product_id,
						'shipping_type' => get_field('shipping_type', $product_id),
						'flat_rate_us' => get_field('flat_rate_us', $product_id),
						'flat_rate_international' => get_field('flat_rate_international', $product_id),
						'individual_weight' => get_field('product_weight', $product_id),
						'total_weight' => ($product['quantity'] * get_field('product_weight', $product_id))
					)
				);
			endforeach;
		endforeach;


		//Loop through products
		foreach($shipping_info as $product):
			//Total Flat Rates
			if($product['shipping_type'] == 'flat'){
				if($country == 'United States'){
					$shipping_costs['flat'] += $product['flat_rate_us'];
				}else{
					$shipping_costs['flat'] += $product['flat_rate_international'];
				}

			//Total USPS Rates
			//Don't compute this, just tally all values into a single 'package'
			}else if($product['shipping_type'] == 'usps'){
				$usps_package['total_cost'] += $product['price'] * $product['quantity'];
				$usps_package['total_weight'] += $product['total_weight'];	
			}

		endforeach;

		//Pass this single package, return all shipping options
		$shipping_costs['usps'] = $this->shipping->usps($usps_package, $country, $shipping_to);

		echo json_encode(array('shipping_options' => $shipping_costs));
		die(); // this is required to terminate immediately and return a proper response
	}



	function checkout(){
		header('Content-Type: application/json');
		$errors = array();

		if(get_field('stripe_active', 'option') == 'Live'){
			Stripe::setApiKey(get_field('stripe_live_secret_key', 'option'));
		}else{
			Stripe::setApiKey(get_field('stripe_test_secret_key', 'option'));
		}

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
		if(empty($this->cart)){ array_push($errors, array('cart' => 'Shopping cart is empty')); }

		// if(strlen($_POST['cart_subtotal']) <= 0){ array_push($errors, array('zipcode' => 'Please enter your zipcode')); }
		// if(strlen($_POST['cart_shipping']) <= 0){ array_push($errors, array('zipcode' => 'Please enter your zipcode')); }
		// if(strlen($_POST['cart_total']) <= 0){ array_push($errors, array('zipcode' => 'Please enter your zipcode')); }

		//Return Errors
		if(!empty($errors)){
			http_response_code(400);
			echo json_encode(array('errors' => $errors));
			die();
		}


		//SANATIZE INPUT !!!
		$this->customer = array(
			'name' => $_POST['fullname'],
			'email' => $_POST['email'],
			'address1' => $_POST['address1'],
			'address2' => $_POST['address2'],
			'city' => $_POST['city'],
			'country' => $_POST['country'],
			'state' => $_POST['state'],
			'zipcode' => $_POST['zipcode']
		);
		$this->cart_info = array(
			'cart_subtotal' => $_POST['cart_subtotal'],
			'cart_shipping' => $_POST['cart_shipping'],
			'cart_total' => $_POST['cart_total'],
			'shipping_name' => $_POST['shipping_name'],
			'shipping_cost' => $_POST['shipping_cost'],
			'shipping_speed' => $_POST['shipping_speed']
		);


		//Calculate new costs, all should be in pennies
		$cart_subtotal = 0;
		$cart_shipping = $_POST['shipping_cost'];
		$cart_total = 0;

		foreach($this->cart as $product_id => $products):
			foreach($products as $product):
				$cart_subtotal += ($product['quantity'] * get_field('base_price', $product_id));
			endforeach;
		endforeach;

		// echo $cart_subtotal;

		$cart_total = $cart_subtotal + $cart_shipping;

		//!!!Doublecheck our new calculations match the AJAX response


		// Get the credit card details submitted by the form
		$token = $_POST['stripeToken'];

		// Create the charge on Stripe's servers - this will charge the user's card
		try {
			$charge = Stripe_Charge::create(array(
				"amount" => $cart_total, // amount in cents, again
				"currency" => "usd",
				"card" => $token,
				"description" => get_field('store_title', 'option')." Order")
			);

		} catch(Stripe_CardError $e) {
			http_response_code(400);
			if(empty($this->cart)){ array_push($errors, array('card' => 'Card was declined')); }
			echo json_encode(array('errors' => $errors));
			die();
		}


		//Send email confirmation
		ob_start(); // start output buffer
		include dirname( __FILE__ ).'/../templates/email.php';
		$template = ob_get_contents(); // get contents of buffer
		ob_end_clean();
		$this->sendEmailNice($customer['email'], $template);


		// //Update stock
		// foreach($this->cart as $product_id => $product){
		// 	$stock = get_field('sizes', $product_id);
		// 	foreach($product as $size => $quantity){
		// 		foreach($stock as &$item){
		// 			if($item['size'] == $size){
		// 				$item['stock'] -= $quantity;
		// 			}
		// 		}
		// 	}
		// 	update_field('sizes', $stock, $product_id);
		// }

		

		echo json_encode(array('cart' => $this->cart, 'user' => $this->customer, 'cart_info' => $this->cart_info));

		//Clear cart
		// $this->wp_session['cart'] = array();
		
		die(); // this is required to terminate immediately and return a proper response
	}


	public function sendEmailNice($to, $message){
		$subject = get_field('store_title', 'option').' Order Confirmation';

		$headers = "From: ".get_field('store_title', 'option')." <".get_field('from_email_address', 'option').">\r\n";
		$headers .= "Reply-To: ".get_field('store_title', 'option')." <".get_field('reply_to_email_address', 'option').">\r\n";
		$headers .= "Bcc: <".get_field('bcc_email_address', 'option').">\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

		mail($to, $subject, $message, $headers);
	}

}