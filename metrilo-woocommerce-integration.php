<?php
/**
 * Plugin Name: Metrilo
 * Plugin URI: https://www.metrilo.com/woocommerce-analytics
 * Description: One-click WooCommerce integration with Metrilo eCommerce Analytics
 * Version: 2.0.0
 * Author: Metrilo
 * Author URI: https://www.metrilo.com/?ref=wpplugin
 * License: GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'Metrilo_Analytics' ) ) :
    
    class Metrilo_Analytics {
        private $integration_version = '2.0.0';
        
        public function __construct() {
            // ensure correct plugin path
            $this->define_globals();
            
            add_action('plugins_loaded', array($this, 'init'));
            
            
            add_filter('query_vars', array($this, 'add_clear_query_var'), 10, 1);
            add_filter('query_vars', array($this, 'add_endpoint_query_vars'), 10, 1);
        }
        
        public function init(){
            // Checks if WooCommerce is installed and activated.
            if ( class_exists( 'WC_Integration' ) ) {
                // Include our integration class.
                include_once 'class.metrilo-admin-settings.php';
                
                // Register the integration.
                add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );
            } else {
                // throw an admin error if you like
                add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
            }
        }
        
        public function add_clear_query_var($vars){
            $vars[] = 'metrilo_clear';
            return $vars;
        }
        
        public function add_endpoint_query_vars($vars){
            $vars[] = 'metrilo_endpoint';
            $vars[] = 'req_id';
            $vars[] = 'recent_orders_sync_days';
            $vars[] = 'metrilo_order_ids';
            return $vars;
        }
        
        public function add_integration($integrations){
            $integrations[] = 'Metrilo_Admin_Settings';
            return $integrations;
        }
        
        public function define_globals() {
            define('METRILO_ANALYTICS_PLUGIN_PATH', plugin_dir_path(__FILE__));
            define('METRILO_ANALYTICS_BASE_URL', plugin_dir_url(__FILE__));
            define('METRILO_ANALYTICS_PLUGIN_VERSION', $this->integration_version);
        }
    }
    
    $MetriloWooAnalytics = new Metrilo_Analytics(__FILE__);

endif;
