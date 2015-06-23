<?php
defined('ABSPATH') or die("No script kiddies please!");

	add_shortcode('ufstorecart', 'shopping_cart_shortcode');
	function shopping_cart_shortcode($atts) {
		include(dirname( __FILE__ ).'\..\templates\cart.php');
	}

	add_shortcode('ufstorecheckout', 'shopping_cart_checkout_shortcode');
	function shopping_cart_checkout_shortcode($atts) {
		include(dirname( __FILE__ ).'\..\templates\checkout.php');
	}

	add_shortcode('ufstoresuccess', 'shopping_cart_success_shortcode');
	function shopping_cart_success_shortcode($atts) {
		include(dirname( __FILE__ ).'\..\templates\success.php');
	}