<?php
defined('ABSPATH') or die("No script kiddies please!");

class UFStoreCartCheckout{

	public function __construct(){
	}

	public static function checkout($customer, $cart_info, $stripeToken){
		$ufstore = UFStore::instance();	
		$shipping_info = array();

		//Calculate new costs, all should be in pennies
		$cart_subtotal = UFStoreCart::getCartCost();
		$cart_shipping = $cart_info['shipping_cost'];
		$cart_total = $cart_subtotal + $cart_shipping;

		//!!!Doublecheck our new calculations match the AJAX response

		//Run the amount on stripe
		if(UFStoreCartCheckout::stripePayment($cart_total, $stripeToken)){
			$cart = UFStoreCart::getCart();

			//Send email confirmation
			ob_start(); // start output buffer
			include dirname( __FILE__ ).'/../templates/email.php';
			$template = ob_get_contents(); // get contents of buffer
			ob_end_clean();
			UFStoreCartCheckout::sendReceipt($customer['email'], $template);

			UFStoreCartCheckout::saveOrder($customer, $cart_info, $cart, $cart_subtotal, $cart_shipping, $cart_total);

			UFStoreCart::clearCart();
			return array('cart' => $cart, 'customer' => $customer);
		}else{
			return false;
		}
	}

	public static function stripePayment($totalCost, $token){
		
		//Get the right stripe token
		if(get_field('stripe_active', 'option') == 'Live'){
			\Stripe\Stripe::setApiKey(get_field('stripe_live_secret_key', 'option'));
		}else{
			\Stripe\Stripe::setApiKey(get_field('stripe_test_secret_key', 'option'));
		}

		// Create the charge on Stripe's servers - this will charge the user's card
		try {
			$charge = \Stripe\Charge::create(array(
				"amount" => $totalCost, // amount in cents, again
				"currency" => "usd",
				"source" => $token,
				"description" => get_field('store_title', 'option')." Order")
			);

		} catch(\Stripe\Error\Card $e) {
			return false;
		}
		return true;
	}

	public static function sendReceipt($to, $message){
		$subject = get_field('store_title', 'option').' Order Confirmation';

		$headers = "From: ".get_field('store_title', 'option')." <".get_field('from_email_address', 'option').">\r\n";
		$headers .= "Reply-To: ".get_field('store_title', 'option')." <".get_field('reply_to_email_address', 'option').">\r\n";
		$headers .= "Bcc: <".get_field('bcc_email_address', 'option').">\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

		mail($to, $subject, $message, $headers);
	}

	public static function saveOrder($customer, $cart_info, $cart, $cart_subtotal, $cart_shipping, $cart_total){
		$date = new DateTime();
		$post = array(
			'post_title' => sanitize_text_field($date->format('Y-m-d H:i:s')),
			'post_type' => 'ufstore_order',
			'post_status' => 'publish'
		);

		$post_id = wp_insert_post($post, $wp_error);

		//On success
		if( !is_wp_error($post_id) ) {
			$address_string = $customer['address1']."\n";
			$address_string .= $customer['address2']."\n";
			$address_string .= $customer['city'].", ";
			$address_string .= $customer['state'].' ';
			$address_string .= $customer['zipcode'];
			
			//Customer Info
			update_field('field_553545e7e553c', $customer['name'], $post_id);
			update_field('field_553545efe553d', $customer['email'], $post_id);
			update_field('field_553545f7e553e', $address_string, $post_id);

			//Meta Data
			update_field('field_5535463bb5c99', $cart_subtotal, $post_id);
			update_field('field_55354648b5c9a', $cart_shipping, $post_id);
			update_field('field_5535464fb5c9b', $cart_total, $post_id);
			// update_field('field_55354656b5c9c', $cart_info['shipping_cost'], $post_id); //Shipping Type
			// update_field('field_55354661b5c9d', $cart_info['shipping_speed'], $post_id); //Shipping Speed

			//Products
			foreach($cart as $product_id => $products): foreach($products as $product):
				$meta_string = '';
				foreach($product['meta'] as $meta_key => $meta_value){
					$meta_string .= $meta_key.": ".$meta_value."</br>";
				}
				$repeater_array[] = array(
					'field_5535469060a11' => $product_id,
					'field_55354a9032c0c' => get_field('base_price', $product_id),
					'field_55354a9532c0d' => $product['quantity'],
					'field_55354daef221b' => $meta_string
				);
			endforeach; endforeach;

			update_field('field_55354668b5c9e', $repeater_array, $post_id); //Products
		}

	}

}