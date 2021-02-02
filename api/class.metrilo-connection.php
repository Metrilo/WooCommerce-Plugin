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
     * @return void
     */
    public function post($url, $bodyArray, $activity = false)
    {
        $encodedBody = $activity ? $bodyArray : json_encode($bodyArray);
        $parsedUrl = parse_url($url);
        $headers = [
            'Content-Type: application/json',
            'Accept: */*',
            'User-Agent: HttpClient/1.0.0',
            'Connection: Close',
            'Host: '.$parsedUrl['host']
        ];
        return $this->curlCall($url, $headers, $encodedBody);
    }
    
    /**
     * CURL call
     *
     * @param string $url
     * @param array $headers
     * @param string $body
     * @param string $method
     * @return void
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
