<?php
defined('ABSPATH') or die("No script kiddies please!");

class UFStoreCart extends UFStore{

	public function __construct(){ }

	//The only time this actually matters
	public static function setupCart(){
		$ufstore = UFStore::instance();

		parent::$wp_session = WP_Session::get_instance();
		if(!isset(parent::$wp_session['cart'])){
			parent::$wp_session['cart'] = array();
		}

		parent::$shoppingCart = parent::$wp_session['cart']->toArray();
	}

	//Return cart as array
	public static function getCart(){
		return parent::$shoppingCart;
	}

	//Return cart as array
	public static function clearCart(){
		parent::$wp_session['cart'] = array();
		return true;
	}

	//Total count of products in cart
	public static function getCartCount(){
		$count = 0;
		foreach(parent::$shoppingCart as $product_id => $products):
			foreach($products as $product):
				$count += $product['quantity'];
			endforeach;
		endforeach;
		return $count;
	}

	//Total cost of everything in the cart
	public static function getCartCost(){
		$cost = 0;
		foreach(parent::$shoppingCart as $product_id => $products):
			foreach($products as $product):
				$cost += ($product['quantity'] * get_field('base_price', $product_id));
			endforeach;
		endforeach;
		return $cost;
	}

	public static function addToCart($id, $quantity, $meta = array()){
		$unique_id = md5(uniqid(rand(), true));		

		// //Is this item already in the cart?
		if(array_key_exists($id, parent::$shoppingCart)){			
			//Does our meta set already exist?
			$exists = false;
			foreach(parent::$shoppingCart[$id] as &$product){
				if($product['meta'] === $meta){
					$product['quantity'] = $quantity;
					$exists = true;
				}
			}

			if($exists == false){
				array_push(
					parent::$shoppingCart[$id],
					array(
						'quantity' => $quantity,
						'unique_id' => $unique_id,
						'meta' => $meta
					)
				);
			}

		}else{
			parent::$shoppingCart[$id] = array(
				array(
					'quantity' => $quantity,
					'unique_id' => $unique_id,
					'meta' => $meta
				)
			);
		}

		parent::$wp_session['cart'] = parent::$shoppingCart;
		return parent::$shoppingCart;
	}


	public static function updateCart($id, $quantity, $unique_id){
		//Is this item already in the cart?
		if(array_key_exists($id, parent::$shoppingCart)){
			foreach(parent::$shoppingCart[$id] as $key => &$product){
				if($product['unique_id'] == $unique_id){
					$product['quantity'] = $quantity;
				}
			}
		}

		parent::$wp_session['cart'] = parent::$shoppingCart;
		return parent::$shoppingCart;
	}


	function removeFromCart($id, $unique_id){
		if(array_key_exists($id, parent::$shoppingCart)){
			foreach(parent::$shoppingCart[$id] as $key => &$product){
				if($product['unique_id'] == $unique_id){
					unset(parent::$shoppingCart[$id][$key]);
				}
			}
			if(empty(parent::$shoppingCart[$id])){
				unset(parent::$shoppingCart[$id]);
			}
		}

		parent::$wp_session['cart'] = parent::$shoppingCart;
		return parent::$shoppingCart;
	}


}