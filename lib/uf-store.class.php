<?php
defined('ABSPATH') or die("No script kiddies please!");

class UFStore{

	protected static $instance;
	protected static $product_slug;
	public static $shoppingCart;
	public static $wp_session;

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			$className      = __CLASS__;
			self::$instance = new $className;
		}

		return self::$instance;
	}


	protected function __construct(){
		// error_reporting(E_ALL);
		// ini_set('display_errors', 'On');

		require_once('vendor/autoload.php');
		
		add_action( 'init', array($this, 'ufstore_setup') ); //Some global stuff	
		add_action( 'init', array($this, 'ufstore_register_types') ); //Register the post type(s) and taxonomy(s) used
		add_action('template_include', array($this, 'single_store_redirect')); //Rewrite the single store page

		//Make sure everything else is up and running
		//Requires WP Session and ACF - either plugin or theme'd
		add_action('plugins_loaded', function(){
			add_action('after_setup_theme', function(){
				if(!class_exists('WP_Session')){
					echo 'WP Session Not Installed';
				}elseif(!class_exists('acf')){
					echo 'ACF Not Installed';
				}else{
					$this->loadLibraries();
					add_filter( 'wp_session_expiration', function() { return 60 * 120; } );
					acf_add_options_sub_page( array(
						'title' => 'Store Settings',
						'parent' => 'edit.php?post_type=ufstore',
						'capability' => 'manage_options'
					) );

				}

			});
		});
		

		// //Load custom fields on the admin side of things
		// add_action( 'admin_init', function(){
		// 	include_once(__DIR__.'\uf-store.acf.fields.php');
		// 	// echo __DIR__.'\uf-store.acf.fields.php';
		// });


	}

	public static function ufstore_activated(){
		//Check our dependencies
		if(!class_exists('WP_Session')){
			deactivate_plugins( plugin_basename( __FILE__ ) );
			die('This plugin requires WP Session Manager');
		}

		if(!class_exists('acf')){
			deactivate_plugins( plugin_basename( __FILE__ ) );
			die('This plugin requires Advanced Custom Fields Pro');
		}
		flush_rewrite_rules();
	}



	protected function loadLibraries(){
		//Third Paty Libraries
		// require_once ('stripe-php-2.1.2/lib/Stripe.php');
		// require_once ('stripe-php-2.1.2/lib/Util/Set.php');
		// require_once ('stripe-php-2.1.2/lib/Util/RequestOptions.php');
		// require_once ('stripe-php-2.1.2/lib/Util/Util.php');
		// require_once ('stripe-php-2.1.2/lib/Error/Base.php');
		// require_once ('stripe-php-2.1.2/lib/Error/InvalidRequest.php');
		// require_once ('stripe-php-2.1.2/lib/Object.php');
		// require_once ('stripe-php-2.1.2/lib/ApiRequestor.php');
		// require_once ('stripe-php-2.1.2/lib/ApiResource.php');
		// require_once ('stripe-php-2.1.2/lib/SingletonApiResource.php');
		// require_once ('stripe-php-2.1.2/lib/Charge.php');

		// $files = glob('stripe-php-2.1.2/lib/*.php');
		// foreach ($files as $file) {
		//     require_once($file);   
		// }

		// $files = glob('stripe-php-2.1.2/lib/Error/*.php');
		// foreach ($files as $file) {
		//     require_once($file);   
		// }

		// $files = glob('stripe-php-2.1.2/lib/Util/*.php');
		// foreach ($files as $file) {
		//     require_once($file);   
		// }

		//General settings and what-nots
		require_once('uf-store.acf.functions.php');
		require_once('uf-store.ajax.functions.php');
		require_once('uf-store.shortcode.functions.php');

		//UFStore
		require_once('uf-store.cart.class.php');
		require_once('uf-store.shipping.class.php');
		require_once('uf-store.shipping.usps.class.php');
		require_once('uf-store.checkout.class.php');

		UFStoreCart::setupCart();
		// $ufstore = UFStore::instance();
		// $ufstore->shoppingCart = new UFStoreCart();
	}



	function ufstore_setup(){
		//Image Sizes
		add_image_size('ufstore-tiny', 75, 75, true);
		add_image_size('ufstore-small', 150, 150, true);
		add_image_size('ufstore-medium', 350, 350, true);
		add_image_size('ufstore-large', 600, 600, true);
		add_image_size('ufstore-email-banner', 558, 200, true);

		//Echo store count in wp_footer
		add_action('wp_footer', function(){
			echo "<script>\n\r";
			echo "\t\tvar ufcart_site = '".get_bloginfo("url")."';\n\r";
			echo "\t\tvar ufcart_cart_url = '".get_permalink(get_field('shopping_cart', 'option'))."';\n\r";
			echo "\t\tvar ufcart_checkout_url = '".get_permalink(get_field('checkout_page', 'option'))."';\n\r";
			echo "\t\tvar ufcart_success_url = '".get_permalink(get_field('success_page', 'option'))."';\n\r";
			if(get_field('stripe_active', 'option') == 'Live'):
			echo "\t\tvar ufcart_stripe = '".get_field('stripe_live_publishable_key', 'option')."';\n\r";
			else:
			echo "\t\tvar ufcart_stripe = '".get_field('stripe_test_publishable_key', 'option')."';\n\r";
			endif;
			echo "\t</script>\n\r";
		});

		acf_add_options_sub_page( array(
			'title' => 'Store Settings',
			'parent' => 'edit.php?post_type=ufstore',
			'capability' => 'manage_options'
		) );

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
	}

	//Register the custom post type ufstore
	//Register the custom taxonomy ufstore_category
	function ufstore_register_types() {
		// $this->product_slug = 'product';
		$labels = array(
			'name'                => _x( 'Products', 'Post Type General Name', 'text_domain' ),
			'singular_name'       => _x( 'Product', 'Post Type Singular Name', 'text_domain' ),
			'menu_name'           => __( 'Store', 'text_domain' ),
			'parent_item_colon'   => __( 'Parent Procut:', 'text_domain' ),
			'all_items'           => __( 'All Products', 'text_domain' ),
			'view_item'           => __( 'View Product', 'text_domain' ),
			'add_new_item'        => __( 'Add New Product', 'text_domain' ),
			'add_new'             => __( 'Add New', 'text_domain' ),
			'edit_item'           => __( 'Edit Product', 'text_domain' ),
			'update_item'         => __( 'Update Product', 'text_domain' ),
			'search_items'        => __( 'Search Product', 'text_domain' ),
			'not_found'           => __( 'Not found', 'text_domain' ),
			'not_found_in_trash'  => __( 'Not found in Trash', 'text_domain' ),
		);
		$args = array(
			'label'               => __( 'uf_store', 'text_domain' ),
			'description'         => __( 'Products in the store', 'text_domain' ),
			'labels'              => $labels,
			'supports'            => array(  'title', 'editor', 'thumbnail',  ),
			'taxonomies'          => array( 'ufstore_category' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 30,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
			'rewrite'             => array( 'slug' => 'product' ),
		);
		register_post_type( 'ufstore', $args );



		$labels = array(
			'name'                => _x( 'Order', 'Post Type General Name', 'text_domain' ),
			'singular_name'       => _x( 'Order', 'Post Type Singular Name', 'text_domain' ),
			'menu_name'           => __( 'Orders', 'text_domain' ),
			'name_admin_bar'      => __( 'Order', 'text_domain' ),
			'parent_item_colon'   => __( 'Parent Order:', 'text_domain' ),
			'all_items'           => __( 'All Orders', 'text_domain' ),
			'add_new_item'        => __( 'Add New Order', 'text_domain' ),
			'add_new'             => __( 'Add New', 'text_domain' ),
			'new_item'            => __( 'New Order', 'text_domain' ),
			'edit_item'           => __( 'Edit Order', 'text_domain' ),
			'update_item'         => __( 'Update Order', 'text_domain' ),
			'view_item'           => __( 'View Order', 'text_domain' ),
			'search_items'        => __( 'Search Orders', 'text_domain' ),
			'not_found'           => __( 'Not found', 'text_domain' ),
			'not_found_in_trash'  => __( 'Not found in Trash', 'text_domain' ),
		);
		$args = array(
			'label'               => __( 'ufstore_order', 'text_domain' ),
			'description'         => __( 'Store Orders', 'text_domain' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'revisions', ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 35,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
		);
		register_post_type( 'ufstore_order', $args );

		// Hook into the 'init' action
		add_action( 'init', 'ufstore_order', 0 );
		//Register the store taxonomy
		$labels = array(
			'name'                       => _x( 'Product Categories', 'Taxonomy General Name', 'text_domain' ),
			'singular_name'              => _x( 'Product Category', 'Taxonomy Singular Name', 'text_domain' ),
			'menu_name'                  => __( 'Product Categories', 'text_domain' ),
			'all_items'                  => __( 'All Categories', 'text_domain' ),
			'parent_item'                => __( 'Parent Category', 'text_domain' ),
			'parent_item_colon'          => __( 'Parent Category:', 'text_domain' ),
			'new_item_name'              => __( 'New Product Category', 'text_domain' ),
			'add_new_item'               => __( 'Add New Product Category', 'text_domain' ),
			'edit_item'                  => __( 'Edit Product Category', 'text_domain' ),
			'update_item'                => __( 'Update Product Category', 'text_domain' ),
			'separate_items_with_commas' => __( 'Separate product categories with commas', 'text_domain' ),
			'search_items'               => __( 'Search Product Categories', 'text_domain' ),
			'add_or_remove_items'        => __( 'Add or remove Product Categories', 'text_domain' ),
			'choose_from_most_used'      => __( 'Choose from the most used Product Categories', 'text_domain' ),
			'not_found'                  => __( 'Not Found', 'text_domain' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true,
			'query_var'                  => 'ufstore_category',
		);
		register_taxonomy('ufstore_category', array( 'ufstore' ), $args );
	}


	//This ensures we use the right template for the single product view
	//In reality, this should be a content replace on a page...
	function single_store_redirect($original_template) {
		if(is_single() && (get_post_type() == 'ufstore')){
			if(file_exists(get_template_directory() . '/ufstore/store-single.php')){
				return get_template_directory() . '/ufstore/store-single.php';
			}else{
				return dirname( __FILE__ ).'/../templates/store-single.php';
			}
		}else{
			return $original_template;
		}
	}

}

//Helper functions

function ufstore_acf_image($image_object, $size = 'large'){
	return $image_object['sizes'][$size];
}


function ufstore_custom_featured_image($size, $attr = array()){
	if ((function_exists('has_post_thumbnail')) && (has_post_thumbnail())) {
		the_post_thumbnail($size, $attr);
	} else {
		echo '<img src="'.get_template_directory_uri().'/assets/img/default_featured/'.$size.'.jpg" class="img-responsive" />';
	}
}