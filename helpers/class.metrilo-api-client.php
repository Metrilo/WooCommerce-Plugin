<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

require_once(METRILO_ANALYTICS_PLUGIN_PATH . 'api/class.metrilo-client.php');

class Metrilo_Api_Client {
    
    public function get_client($api_endpoint_domain, $token = '', $secret = '')
    {
        if(empty($token) || empty($secret)) {
            $module_options = get_option('woocommerce_metrilo-analytics_settings', false);
            $token          = $module_options['api_token'];
            $secret         = $module_options['api_secret'];
        }
        
        $platform       = 'Wordpress ' . get_bloginfo( 'version' );
        $plugin_version = METRILO_ANALYTICS_PLUGIN_VERSION;
        $url            = 'https://' . $api_endpoint_domain;
        
        return new Metrilo_Client($token, $secret, $platform, $plugin_version, $url, METRILO_ANALYTICS_PLUGIN_PATH);
    }
}

return new Metrilo_Api_Client();
