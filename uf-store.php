<?php
/**
 * Plugin Name: Umbrella Fish Store
 * Plugin URI: http://umbrella-fish.com
 * Description: Super simple store plugin
 * Version: 0.2
 * Author: James Buke
 * Author URI: http://jameswburke.com
 * Network: False
 * License: IDK
 */

defined('ABSPATH') or die("No script kiddies please!");

require_once( dirname( __FILE__ ) . '/lib/uf-store.class.php' );

UFStore::instance();

register_activation_hook( __FILE__ , array( 'UFStore', 'ufstore_activated' ) );