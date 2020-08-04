<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
require_once(METRILO_ANALYTICS_PLUGIN_PATH . 'api/class.metrilo-client.php');

class Metrilo_Api_Client {
    
    public function get_client()
    {
        $module_options = get_option('woocommerce_metrilo-analytics_settings', false);
        $token          = $module_options['api_token'];
        $platform       = 'Wordpress ' . get_bloginfo( 'version' );
        $plugin_version = METRILO_ANALYTICS_PLUGIN_VERSION;
        $api_end_point  = 'https://tracking.metrilo.com';
//        $api_end_point  = 'https://trk.mtrl.me';
        
        return new Metrilo_Client($token, $platform, $plugin_version, $api_end_point, METRILO_ANALYTICS_PLUGIN_PATH);
    }
}

return new Metrilo_Api_Client();
