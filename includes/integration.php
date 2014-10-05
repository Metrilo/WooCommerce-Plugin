<?php

 
if ( ! class_exists( 'Metrilo_Woo_Analytics_Integration' ) ) :

class Metrilo_Woo_Analytics_Integration extends WC_Integration {


	private $events_queue = array();
	private $single_item_tracked = false;
	private $has_events_in_cookie = false;
	private $identify_call_data = false;

 
	/**
	 * Init and hook in the integration.
	 */
	public function __construct() {
		global $woocommerce, $metrilo_woo_analytics_integration;

		$this->id = 'metrilo-woo-analytics';
		$this->method_title = __( 'Metrilo', 'metrilo-woo-analytics' );
		$this->method_description = __( 'Metrilo offers powerful yet simple Analytics for WooCommerce Stores. Enter your API key to active analytics tracking.', 'metrilo-woo-analytics' );
 

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();
 
		// Define user set variables.
		$this->api_key = $this->get_option('api_key', false);

		// initialization methods
		$this->ensure_path();
		$this->ensure_hooks();
		$this->process_cookie_events();
		$this->ensure_identify();
 
		// hook to integration settings update
		add_action( 'woocommerce_update_options_integration_' .  $this->id, array( $this, 'process_admin_options' ) );
 
	}


	// basic initiation methods and hooks


	public function ensure_hooks(){


		// general tracking snipper hook
		add_filter('wp_head', array($this, 'render_snippet'));
		add_filter('wp_head', array($this, 'woocommerce_tracking'));

		// background events tracking
		add_action('woocommerce_add_to_cart', array($this, 'add_to_cart'), 10, 6);
		add_action('woocommerce_before_cart_item_quantity_zero', array($this, 'remove_from_cart'), 10);
		add_filter('woocommerce_applied_coupon', array($this, 'applied_coupon'), 10);

		// cookie clearing actions
		add_action('wp_ajax_metrilo_clear', array($this, 'clear_cookie_events'));
		add_action('wp_ajax_nopriv_metrilo_clear', array($this, 'clear_cookie_events'));


	}

	public function ensure_path(){
		// define the Metrilo plugin path
		define('METRILO_PLUGIN_PATH', dirname(__FILE__));
	}

	public function ensure_identify(){
		if(!is_admin() && is_user_logged_in() && !(isset($_COOKIE[$this->get_identify_cookie_name()]))){
			$user = wp_get_current_user();
			$this->identify_call_data = array('id' => $user->user_email, 'params' => array('email' => $user->user_email, 'name' => $user->display_name));
			if($user->user_firstname!= '' && $user->user_lastname){
				$this->identify_call_data['params']['first_name'] = $user->user_firstname;
				$this->identify_call_data['params']['last_name'] = $user->user_lastname;
			}
			@setcookie($this->get_identify_cookie_name(), 'true', time() + 720, COOKIEPATH, COOKIE_DOMAIN);
		}
	}	


	// session and cookie handling

	public function add_item_to_cookie($data){
		$items = $this->get_items_in_cookie();
		if(empty($items)) $items = array();
		array_push($items, $data);
		$encoded_items = json_encode($items, true);
		@setcookie($this->get_cookie_name(), $encoded_items, time() + 43200, COOKIEPATH, COOKIE_DOMAIN);
		$_COOKIE[$this->get_cookie_name()] = $encoded_items;
	}

	public function get_items_in_cookie(){
		$items = array();
		$data = isset($_COOKIE[$this->get_cookie_name()]) ? $_COOKIE[$this->get_cookie_name()] : false;
		if(!empty($data)) $items = json_decode(stripslashes($data), true);
		return $items;
	}

	public function clear_items_in_cookie(){
		@setcookie($this->get_cookie_name(), json_decode(array(), true), time() + 43200, COOKIEPATH, COOKIE_DOMAIN);
		$_COOKIE[$this->get_cookie_name()] = json_decode(array(), true);
	}

	private function get_cookie_name(){
		return 'metriloqueue_' . COOKIEHASH;
	}

	private function get_identify_cookie_name(){
		return 'metrilodid_' . COOKIEHASH;
	}	

	public function clear_cookie_events(){
		$this->clear_items_in_cookie();
		wp_send_json_success();
	}


	// website tracking methods

	public function render_snippet(){
		return true;
		include_once(METRILO_PLUGIN_PATH.'/js.php');
		// this is where we're going to place the tracking code if we need to send something
	} 

	public function process_cookie_events(){
		$items = $this->get_items_in_cookie();
		if(count($items) > 0){
			$this->has_events_in_cookie = true;
			foreach($items as $event){
				$this->put_event_in_queue($event['method'], $event['event'], $event['params']);
			}
		}
	}

	public function woocommerce_tracking(){
		// check if woocommerce is installed
		if(class_exists('WooCommerce')){
			/** check certain tracking scenarios **/

			// if visitor is viewing product
			if(!$this->single_item_tracked && is_product()){
				$product = get_product(get_queried_object_id());
				$this->put_event_in_queue('track', 'view_product', $this->prepare_product_hash($product));
				$this->single_item_tracked = true;
			}

			// if visitor is viewing product category
			if(!$this->single_item_tracked && is_product_category()){
				$this->put_event_in_queue('track', 'view_category', $this->prepare_category_hash(get_queried_object()));
				$this->single_item_tracked = true;
			}

			// if visitor is viewing shopping cart page
			if(!$this->single_item_tracked && is_cart()){
				$this->put_event_in_queue('track', 'view_cart', array());
				$this->single_item_tracked = true;
			}
			// if visitor is anywhere in the checkout process
			if(!$this->single_item_tracked && is_order_received_page()){
				$order_id = get_query_var('order-received');
				$order = new WC_Order($order_id);
				// also - prepare to identify user
				$this->identify_call_data = array(
					'id'		=> get_post_meta($order_id, '_billing_email', true),
					'params'	=> array(
								'email' 		=> get_post_meta($order_id, '_billing_email', true),
								'first_name' 	=> get_post_meta($order_id, '_billing_first_name', true),
								'last_name' 	=> get_post_meta($order_id, '_billing_last_name', true),
								'name'			=> get_post_meta($order_id, '_billing_first_name', true) . ' ' . get_post_meta($order_id, '_billing_last_name', true),
							)
				);
				$order_items = $order->get_items();
				$purchase_params = array('order_id' => $order_id, 'amount' => $order->get_total(), 'items' => array());
				foreach($order_items as $product){
					$product_hash = array('id' => $product['product_id'], 'quantity' => $product['qty'], 'name' => $product['name']);
					if(!empty($product['variation_id'])){
						$variation_data = $this->prepare_variation_data($product['variation_id']);
						$product_hash['option_id'] = $variation_data['id'];
						$product_hash['option_price'] = $variation_data['price'];
					}
					array_push($purchase_params['items'], $product_hash);
				}
				$this->put_event_in_queue('track', 'order', array('order_id' => $order_id));
				$this->put_event_in_queue('purchase', '', $purchase_params);
				$this->single_item_tracked = true;
			}elseif(!$this->single_item_tracked && is_checkout_pay_page()){
				$this->put_event_in_queue('track', 'checkout_payment', array());
				$this->single_item_tracked = true;
			}elseif(!$this->single_item_tracked && is_checkout()){
				$this->put_event_in_queue('track', 'checkout_start', array());
				$this->single_item_tracked = true;
			}
		}

		// ** GENERIC WordPress tracking - doesn't require WooCommerce in order to work **//

		// if visitor is viewing homepage or any text page
		if(!$this->single_item_tracked && is_front_page()){
			$this->put_event_in_queue('track', 'pageview', 'Homepage');
			$this->single_item_tracked = true;
		}elseif(!$this->single_item_tracked && is_page()){
			$this->put_event_in_queue('track', 'pageview', get_the_title());
			$this->single_item_tracked = true;
		}

		// if visitor is viewing post
		if(!$this->single_item_tracked && is_single()){
			$post_id = get_the_id();
			$this->put_event_in_queue('track', 'view_article', array('id' => $post_id, 'name' => get_the_title(), 'url' => get_permalink($post_id)));
			$this->single_item_tracked = true;
		}

		// if nothing else is tracked - send pageview event
		if(!$this->single_item_tracked){
			$this->put_event_in_queue('pageview');
		}

		// check if there are events in the queue to be sent to Metrilo
		if(count($this->events_queue) > 0) $this->render_events();
		if($this->identify_call_data !== false) $this->render_identify();
	}

	public function prepare_product_hash($product, $variation_id = false, $variation = false){
		$product_hash = array(
			'id'			=> $product->id, 
			'name'			=> $product->get_title(),
			'price'			=> $product->get_price(),
			'url'			=> $product->get_permalink()
		);
		if($variation_id){
			$variation_data = $this->prepare_variation_data($variation_id, $variation);
			$product_hash['option_id'] = $variation_data['id'];
			$product_hash['option_name'] = $variation_data['name'];
			$product_hash['option_price'] = $variation_data['price'];
		}
		// fetch image URL
		$image_id = get_post_thumbnail_id($product->id);
		$image = get_post($image_id);
		if($image && $image->guid) $product_hash['image_url'] = $image->guid;

		// fetch the categories
		$categories = wp_get_post_terms($product->id, 'product_cat');
		if(!empty($categories)){
			$categories_list = array();
			foreach($categories as $cat){
				array_push($categories_list, array('id' => $cat->term_id, 'name' => $cat->name));
			}
			if(!empty($categories_list)) $product_hash['categories'] = $categories_list;
		}

		// return 
		return $product_hash;
	}

	public function prepare_category_hash($category){
		$category_hash = array(
			'id'	=>	$category->term_id, 
			'name'	=> 	$category->name
		);
		return $category_hash;
	}

	public function put_event_in_queue($method, $event = '', $params = array()){
		array_push($this->events_queue, $this->prepare_event_for_queue($method, $event, $params));
	}

	public function put_event_in_cookie_queue($method, $event, $params){
		$this->add_item_to_cookie($this->prepare_event_for_queue($method, $event, $params));
	}

	public function prepare_event_for_queue($method, $event, $params){
		return array('method' => $method, 'event' => $event, 'params' => $params);
	}

	public function render_events(){
		include_once(METRILO_PLUGIN_PATH.'/render_tracking_events.php');
	}

	public function render_identify(){
		include_once(METRILO_PLUGIN_PATH.'/render_identify.php');
	}


	public function add_to_cart($cart_item_key, $product_id, $quantity, $variation_id = false, $variation = false, $cart_item_data = false){
		$product = get_product($product_id);
		$this->put_event_in_cookie_queue('track', 'add_to_cart', $this->prepare_product_hash($product, $variation_id, $variation));
		$items = $this->get_items_in_cookie();
	}

	public function remove_from_cart($key_id){
		if (!is_object(WC()->cart)) {
			return true;
		}		
		$cart_items = WC()->cart->get_cart();
		$removed_cart_item = isset($cart_items[$key_id]) ? $cart_items[$key_id] : false;
		if($removed_cart_item){
			$event_params = array('id' => $removed_cart_item['product_id']);
			if(!empty($removed_cart_item['variation_id'])){
				$event_params['option_id'] = $removed_cart_item['variation_id'];
			}
			$this->put_event_in_cookie_queue('track', 'remove_from_cart', $event_params);
		}
	}

	public function prepare_variation_data($variation_id, $variation = false){
		// prepare variation data array
		$variation_data = array('id' => $variation_id, 'name' => '', 'price' => '');

		// prepare variation name if $variation is provided as argument
		if($variation){
			$variation_attribute_count = 0;
			foreach($variation as $attr => $value){
				$variation_data['name'] = $variation_data['name'] . ($variation_attribute_count == 0 ? '' : ', ') . $value;
				$variation_attribute_count++;
			}
		}

		// get variation price from object
		$variation_obj = new WC_Product_Variation($variation_id);
		$variation_data['price'] = $variation_obj->price;

		// return
		return $variation_data;
	}

	public function applied_coupon($coupon_code){
		$this->put_event_in_queue('track', 'applied_coupon', $coupon_code);	
	}

	// session management functions

	public function session_get($v){
		var_dump($v);
	}

	public function session_set($v){

	}
 
	/**
	 * Initialize integration settings form fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'api_key' => array(
				'title'             => __( 'API Key', 'metrilo-woo-analytics' ),
				'type'              => 'text',
				'description'       => __( 'Enter your Metrilo API key. You can find it in your "Settings" menu in your Metrilo account at metrilo.com', 'metrilo-woo-analytics' ),
				'desc_tip'          => true,
				'default'           => ''
			)
		);
	}
 
}



endif;