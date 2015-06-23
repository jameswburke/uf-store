<?php
defined('ABSPATH') or die("No script kiddies please!");

class UFStoreShipping{

	public function __construct(){
		
	}

	public static function getShipping($shipping_to, $country = 'United States'){
		$ufstore = UFStore::instance();
		$shipping_info = array();

		//Get ready to calculate for USPS
		$usps_package = array(
			'total_cost' => 0,
			'total_weight' => 0
		);

		$shipping_costs = array(
			'total' => 0,
			'flat' => 0,
			'flat_us' => 0,
			'flat_international' => 0,
			'included' => 0,
			'groups' => 0,
			'usps' => 0,
		);

		$groups = array();


		// Generate an array to loop through
		//Collect each cart's shipping info
		if(UFStoreCart::getCartCount() > 0){
			foreach(UFStoreCart::getCart() as $product_id => $products):
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
							'total_weight' => ($product['quantity'] * get_field('product_weight', $product_id)),
							'group_cost' => get_field('group_cost', $product_id),
							'group_amount_of_product' => get_field('group_amount_of_product', $product_id),
							'group_category' => get_field('group_category', $product_id)
						)
					);
				endforeach;
			endforeach;
		}

		if(!empty($shipping_info)){
			//Loop through products
			foreach($shipping_info as $product):
				//Total Flat Rates
				if($product['shipping_type'] == 'flat'){
					if($country == 'United States'){
						$shipping_costs['flat'] += $product['flat_rate_us'];
						$shipping_costs['flat_us'] += $product['flat_rate_us'];
					}else{
						$shipping_costs['flat'] += $product['flat_rate_international'];
						$shipping_costs['flat_international'] += $product['flat_rate_international'];
					}

				//Group products together
				}else if($product['shipping_type'] == 'group'){
					if(!array_key_exists($product['group_category'][0], $groups)){
						// echo $product['group_category'][0];
						$groups[$product['group_category'][0]] = array(
							'cost' => $product['group_cost'],
							'max_amount' => $product['group_amount_of_product'],
							'actual_amount' => $product['quantity']
						);
					}else{
						$groups[$product['group_category'][0]]['actual_amount'] = ($groups[$product['group_category'][0]]['actual_amount'] + $product['quantity']);
					}

				//Total USPS Rates
				//Don't compute this, just tally all values into a single 'package'
				}else if($product['shipping_type'] == 'usps'){
					$usps_package['total_cost'] += $product['price'] * $product['quantity'];
					$usps_package['total_weight'] += $product['total_weight'];	
				}

			endforeach;			
		}

		foreach($groups as $group){
			$shipping_costs['groups'] += (((floor($group['actual_amount'] / $group['max_amount'])) + 1) * $group['cost']); //Charge $6 for every 4 shirts
		}

		$shipping_costs['usps'] = UFStoreShippingUSPS::usps($usps_package, $country, $shipping_to);

		return $shipping_costs;
	}

}