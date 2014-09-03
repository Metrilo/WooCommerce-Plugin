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
	} 

	// hooks

	public function ensure_hooks(){
		// general tracking snipper hook
		add_filter('wp_head', array(self::$instance, 'render_snippet'));

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

}

register_activation_hook(__FILE__, array('Metrilo_Woo_Analytics', 'ensure_settings' ));
add_action( 'plugins_loaded', 'Metrilo_Woo_Analytics::ensure_instance');

?>