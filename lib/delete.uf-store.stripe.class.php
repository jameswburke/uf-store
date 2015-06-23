<?php
defined('ABSPATH') or die("No script kiddies please!");

class UFStoreStripe{

	public $cart;

	public $wp_session;

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

		//$this->cart = 'lol';

		//Add to cart
		add_action('wp_ajax_checkout', array($this, 'checkout'));
		add_action('wp_ajax_nopriv_checkout', array($this, 'checkout'));

	}

	function checkout(){
		if(get_field('stripe_active', 'option') == 'Live'){
			Stripe::setApiKey(get_field('stripe_live_secret_key', 'option'));
		}else{
			Stripe::setApiKey(get_field('stripe_test_secret_key', 'option'));
		}

		//SANATIZE INPUT !!!
		$this->customer = array(
			'name' => $_POST['cus_name'],
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
			'cart_total' => $_POST['cart_total']
		);

		if(!empty($this->cart)){

			$cart_subtotal = 0;
			$cart_shipping = 0;
			$cart_total = 0;

			foreach($this->cart as $product_id => $products):
				foreach($products as $product):
					$cart_subtotal += ($product['quantity'] * get_field('base_price', $product_id));
				endforeach;
			endforeach;


			$stripe_cost = $cart_total * 100; //convert dollars to cents
			
		}else{
			http_response_code(400);
			header('Content-Type: application/json');
			echo json_encode(array('message' => 'Cart is empty'));
			die();
		}


		// Get the credit card details submitted by the form
		$token = $_POST['stripeToken'];

		// Create the charge on Stripe's servers - this will charge the user's card
		try {
		$charge = Stripe_Charge::create(array(
			"amount" => $stripe_cost, // amount in cents, again
			"currency" => "usd",
			"card" => $token,
			"description" => "The Good Project Order")
		);
		} catch(Stripe_CardError $e) {
			http_response_code(400);
			header('Content-Type: application/json');
			echo json_encode(array('message' => 'Card was declined'));
			die();
		}


		//Send email confirmation
		ob_start(); // start output buffer
		include dirname( __FILE__ ).'/../templates/email.php';
		$template = ob_get_contents(); // get contents of buffer
		ob_end_clean();
		$this->sendEmailNice($customer['email'], $template);


		//Update stock
		foreach($this->cart as $product_id => $product){
			$stock = get_field('sizes', $product_id);
			foreach($product as $size => $quantity){
				foreach($stock as &$item){
					if($item['size'] == $size){
						$item['stock'] -= $quantity;
					}
				}
			}
			update_field('sizes', $stock, $product_id);
		}

		

		header('Content-Type: application/json');
		echo json_encode(array('cart' => $this->cart, 'user' => $this->customer));

		//Clear cart
		$this->wp_session['cart'] = array();
		
		die(); // this is required to terminate immediately and return a proper response
	}


	public function sendEmailNice($to, $message){
		$subject = get_field('store_title', 'option').' Order Confirmation';

		$headers = "From: ".get_field('store_title', 'option')." Store <store@jointhegoodproject.com>\r\n";
		$headers .= "Reply-To: The Good Project Store <store@jointhegoodproject.com>\r\n";
		$headers .= "Bcc: <james.walter.burke@gmail.com>\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

		mail($to, $subject, $message, $headers);
	}



}