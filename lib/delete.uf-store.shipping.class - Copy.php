<?php
defined('ABSPATH') or die("No script kiddies please!");

class UFStoreShipping{

	public $usps_api_url;
	public $package;

	public function __construct(){
		$this->usps_api_url = 'http://production.shippingapis.com/ShippingAPI.dll';
	}

	public function usps($package, $country, $shipping_to){		
		$shipping_from = get_field('shipping_from', 'option');

		if(($package['total_cost'] == 0) || ($package['total_weight'] == 0)){
			return array();
		}
		//perform validation here

		//Create package
		$this->package = $this->usps_create_package($package, $country, $shipping_to, $shipping_from);

		//Execute CURL
		$response = $this->execute_curl($this->usps_api_url.$this->package);

		//Sort through it
		$shipping_options = $this->parse_response_xml($response, $country);

		return $shipping_options;
	}


	public function usps_create_package($package, $country, $shipping_to, $shipping_from){
		if($country == 'United States'){
			if($package['total_weight'] > 13){
				$shipping_service = 'PRIORITY';
			}else{
				$shipping_service = 'FIRST CLASS';
			}

			$package_string .= '<RateV4Request USERID="'.get_field('usps_user_id', 'option').'">';
			$package_string .= '<Package ID="main_package">';
			$package_string .= '<Service>'.$shipping_service.'</Service>';
			$package_string .= '<FirstClassMailType>parcel</FirstClassMailType>';
			$package_string .= '<ZipOrigination>'.$shipping_from.'</ZipOrigination>';
			$package_string .= '<ZipDestination>'.$shipping_to.'</ZipDestination>';
			$package_string .= '<Pounds>'.(int)($package['total_weight']/16).'</Pounds>';
			$package_string .= '<Ounces>'.($package['total_weight'] % 16).'</Ounces>';
			$package_string .= '<Container/>';
			$package_string .= '<Size>REGULAR</Size>';
			$package_string .= '<Machinable>true</Machinable>';
			$package_string .= '</Package>';
			$package_string .= '</RateV4Request>';

			//Add correct API version, and url encode
			$query_string = '?API=RateV4&XML='.rawurlencode($package_string);

		}else{
			$package_string .= '<IntlRateV2Request USERID="'.get_field('usps_user_id', 'option').'">';
			$package_string .= '<Revision>2</Revision>';
			$package_string .= '<Package ID="main_package">';
			$package_string .= '<Pounds>'.(int)($package['total_weight']/16).'</Pounds>';
			$package_string .= '<Ounces>'.($package['total_weight'] % 16).'</Ounces>';
			$package_string .= '<Machinable>True</Machinable>';
			$package_string .= '<MailType>Package</MailType>'; //Double check this info
			$package_string .= '<GXG>';
			$package_string .= '<POBoxFlag>N</POBoxFlag>';
			$package_string .= '<GiftFlag>N</GiftFlag>';
			$package_string .= '</GXG>';
			$package_string .= '<ValueOfContents>'.($package['total_cost'] / 100).'</ValueOfContents>';
			$package_string .= '<Country>'.$country.'</Country>';
			$package_string .= '<Container>RECTANGULAR</Container>';
			$package_string .= '<Size>Regular</Size>';
			$package_string .= '<Width>10</Width>';
			$package_string .= '<Length>10</Length>';
			$package_string .= '<Height>11</Height>';
			$package_string .= '<Girth></Girth>';
			$package_string .= '<CommercialFlag>y</CommercialFlag>';
			$package_string .= '</Package>';
			$package_string .= '</IntlRateV2Request>';

			//Add correct API version, and url encode
			$query_string = '?API=IntlRateV2&XML='.rawurlencode($package_string);
		}

		return $query_string;
	}

	public function execute_curl($url){
		$errors = array();
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $url
		));
		$response = curl_exec($curl);
		curl_close($curl);
		return $response;
	}

	public function parse_response_xml($response, $country){
		$shipping_options = array();
		$errors = array();

		//Parse into SimpleXML object
		$xml = simplexml_load_string($response);
		$xml_parser = xml_parser_create();
		xml_parse_into_struct($xml_parser, $response, $vals, $index);
		xml_parser_free($xml_parser);

		//Check for errors
		if(isset($index['ERROR'])){
			foreach($index['DESCRIPTION'] as $error){
				array_push($errors, array('shippingError' => $vals[$error]['value']));
			}
			http_response_code(400);
			echo json_encode(array('errors' => $errors));
			die();
		}

		//Domestic
		if($country == 'United States'){
			$usps_cost = 0;
			foreach($index['RATE'] as $rate_index){
				$usps_cost += $vals[$rate_index]['value'];
			}

			//Add options
			array_push(
				$shipping_options,
				array(
					'type' => html_entity_decode($vals[$index['MAILSERVICE'][0]]['value']),
					'cost' => ($usps_cost * 100),
					'speed' => ''
				)
			);

		//International
		}else{
			foreach($xml->Package as $packages){
				foreach ($packages->Service as $services) {

					array_push(
						$shipping_options,
						array(
							'type' => html_entity_decode($services->SvcDescription),
							'speed' => ''.$services->SvcCommitments,
							'cost' => ($services->Postage * 100)
						)
					);				

				}
			}
		}

		return $shipping_options;

	}


}