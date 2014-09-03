<?php
/**
 * Plugin Name: Metrilo for WooCommerce
 * Plugin URI: https://www.metrilo.com/woocommerce-integration
 * Description: Metrilo eCommerce Analytics one-click integration for WooCommerce
 * Version: 0.8
 * Author: Metrilo
 * Author URI: https://www.metrilo.com/
 * License: GPL2
 */

class Metrilo_Woo_Analytics {

	private static $instance;
	private $options_pool = 'metrilo_woo_analytics';
	private $default_options = array('api_token' => '');
	private $events_queue = array();
	private $single_item_tracked = false;

	// make sure there's Metrilo instance
	public static function ensure_instance(){

		if (!isset(self::$instance)){
			self::$instance = new Metrilo_Woo_Analytics;
			self::$instance->ensure_path();
			self::$instance->ensure_hooks();
		}
		return self::$instance;

	}

	public function get_options_pool(){
		return $this->options_pool;
	}

	public static function ensure_settings(){
		$settings = get_option(Metrilo_Woo_Analytics::ensure_instance()->options_pool);
		if(empty($settings)){
			update_option(Metrilo_Woo_Analytics::ensure_instance()->options_pool, Metrilo_Woo_Analytics::ensure_instance()->default_options);
		}
	}

	public static function get_settings(){
		return get_option(self::ensure_instance()->get_options_pool());
	}

	public function ensure_path(){
		// define the Metrilo plugin path
		define('METRILO_PLUGIN_PATH', dirname(__FILE__));
	}

	// website tracking methods

	public function render_snippet(){
		$settings = $this->get_settings();
		include_once(METRILO_PLUGIN_PATH.'/includes/js.php');
		// this is where we're going to place the tracking code if we need to send something
	} 

	public function woocommerce_tracking(){
		// check if woocommerce is installed
		if(class_exists('WooCommerce')){
			// check certain tracking scenarios
			if(is_product()){
				$product = get_product(get_queried_object_id());
				$this->put_event_in_queue('track', 'view_product', $this->prepare_product_hash($product));
				$this->single_item_tracked = true;
			}
			if(is_product_category()){
				$this->put_event_in_queue('track', 'view_category', $this->prepare_category_hash(get_queried_object()));
				$this->single_item_tracked = true;
			}
		}

		// check if there are events in the queue to be sent to Metrilo
		if(count($this->events_queue) > 0) $this->render_events();
	}

	public function prepare_product_hash($product){
		$product_hash = array(
			'id'			=> $product->id, 
			'name'			=> $product->get_title(),
			'price'			=> $product->get_price(),
			'url'			=> $product->get_permalink()
		);
		// fetch image URL
		$image_id = get_post_thumbnail_id($product->id);
		$image = get_post($image_id);
		if($image && $image->guid) $product_hash['image_url'] = $image->guid;
		return $product_hash;
	}

	public function prepare_category_hash($category){
		$category_hash = array(
			'id'	=>	$category->term_id, 
			'name'	=> 	$category->name
		);
		return $category_hash;
	}

	public function put_event_in_queue($method, $event, $params){
		array_push($this->events_queue, $this->prepare_event_for_queue($method, $event, $params));
	}

	public function put_event_in_cookie_queue($method, $event, $params){
		$this->add_item_to_cookie($this->prepare_event_for_queue($method, $event, $params));
	}

	public function prepare_event_for_queue($method, $event, $params){
		return array('method' => $method, 'event' => $event, 'params' => $params);
	}

	public function render_events(){
		include_once(METRILO_PLUGIN_PATH.'/includes/render_tracking_events.php');
	}

	public function add_to_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation){
		return true; // temporary return true because it's FUCKING BUGGY
		$product = get_product($product_id);
		$this->put_event_in_cookie_queue('track', 'view_product', $this->prepare_product_hash($product));
		$items = $this->get_items_in_cookie();
	}

	// hooks

	public function ensure_hooks(){
		// general tracking snipper hook
		add_filter('wp_head', array(self::$instance, 'render_snippet'));
		add_filter('wp_head', array(self::$instance, 'woocommerce_tracking'));

		// background events tracking
		add_action('woocommerce_add_to_cart', array($this, 'add_to_cart'), 10, 3);

		// add admin menu option
		add_action('admin_menu', array(self::$instance, 'ensure_admin_menu'));
		// register metrilo plugin settings
		add_action('admin_init', array(self::$instance, 'ensure_admin_settings'));

	}

	// admin panel stuff

	public function ensure_admin_menu() {
		//update_option('metrilo_woo_analytics', array('api_key' => ''));
		add_options_page(
			apply_filters( 'metrilo_admin_menu_page_title', __( 'Metrilo Settings', 'metrilo' ) ), // Page Title
			apply_filters( 'metrilo_admin_menu_menu_title', __( 'Metrilo', 'metrilo' ) ), // Menu Title
			apply_filters( 'metrilo_admin_settings_capability', 'manage_options' ),  // Capability Required
			'metrilo',                                                               // Menu Slug
			array( $this, 'render_admin_page' )                                             // Function
		);

	}

	public function ensure_admin_settings(){
		register_setting('metrilo', $this->options_pool, array(self::$instance, 'save_settings'));
		add_settings_section('metrilo_general', 'Your Metrilo Token', false, 'metrilo');
		add_settings_field('api_token', 'API Token', array(self::$instance, 'api_token_callback' ), 'metrilo', 'metrilo_general');
	}

	public function api_token_callback(){
		$settings = Metrilo_Woo_Analytics::ensure_instance()->get_settings();
		$name     = Metrilo_Woo_Analytics::ensure_instance()->options_pool . '[api_token]';
	?>
			<input class="regular-text ltr" type="text" name="<?php echo esc_attr( $name ); ?>" id="api_token" value="<?php echo esc_attr( $settings['api_token'] ); ?>" />
			<p class="description">Do not know your API token? Go to "Settings" in your Metrilo account and it's going to be waiting for you there.</p>
		<?php
	}

	public static function save_settings($options){
		return array('api_token' => $options['api_token']);
	}

	public function render_admin_page(){
		include_once(METRILO_PLUGIN_PATH.'/includes/settings.php');
	}

	// cookie handling

	public function add_item_to_cookie($data){
		$items = $this->get_items_in_cookie();
		if(empty($items)) $items = array();
		array_push($items, $data);
		$encoded_items = json_encode($items);
		@setcookie($this->get_cookie_name(), json_encode($encoded_items), time() + 43200, COOKIEPATH, COOKIE_DOMAIN);
		$_COOKIE[$this->get_cookie_name()] = $encoded_items;
	}

	public function get_items_in_cookie(){
		$items = array();
		$data = $_COOKIE[$this->get_cookie_name()];
		if($data) $items = json_decode(stripslashes($data), true);
		return $items;
	}

	private function get_cookie_name(){
		return 'metriloqueue_' . COOKIEHASH;
	}

}

register_activation_hook(__FILE__, array('Metrilo_Woo_Analytics', 'ensure_settings' ));
add_action( 'plugins_loaded', 'Metrilo_Woo_Analytics::ensure_instance');

?>