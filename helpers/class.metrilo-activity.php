<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Metrilo_Activity {
    
    public function create_activity($type)
    {
        $module_options = get_option('woocommerce_metrilo-analytics_settings', false);
        $token          = $module_options['api_token'];
        $secret         = $module_options['api_secret'];
        $end_point      = 'https://p.metrilo.com';
        $api_client     = new Metrilo_Api_Client();
        $client         = $api_client->get_client();
        
        $data = array(
            'type'          => $type,
            'project_token' => $token,
            'signature'     => md5($token . $type . $secret)
        );
        
        $url = $end_point . '/tracking/' . $token . '/activity';
        
        return $client->createActivity($url, $data);
    }
}

return new Metrilo_Activity();
