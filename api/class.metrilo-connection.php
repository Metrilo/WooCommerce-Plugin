<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Metrilo_Connection {
    /**
     * Create HTTP POST request to URL
     *
     * @param String $url
     * @param Array $bodyArray
     * @param Boolean $activity
     * @param String $secret
     * @return array
     */
    public function post($url, $bodyArray, $secret)
    {
        $parsedUrl = parse_url($url);
        $headers = [
            'Content-Type' => 'application/json',
            'Accept'       =>  '*/*',
            'User-Agent'   => 'HttpClient/1.0.0',
            'Connection'   => 'Close',
            'Host'         => $parsedUrl['host'],
            'X-Digest'     => hash_hmac('sha256', json_encode($bodyArray), $secret)
        ];

        $encodedBody = $bodyArray ? json_encode($bodyArray) : '';
        
        return $this->curlCall($url, $headers, $encodedBody);
    }
    
    /**
     * CURL call
     *
     * @param string $url
     * @param array $headers
     * @param string $body
     * @param string $method
     * @return array
     */
    public function curlCall($url, $headers = [], $body = '', $method = "POST")
    {
        $response = wp_remote_post(
            $url,
            ['headers' => $headers, 'body' => $body, 'type' => $method, 'timeout' => 15, 'blocking' => true]
        );
        
        return array(
            'response' => $response['response'],
            'code' => $response['response']['code']
        );
    }
}
