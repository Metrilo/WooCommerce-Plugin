<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

require_once(METRILO_ANALYTICS_PLUGIN_PATH . 'api/class.metrilo-connection.php');
require_once(METRILO_ANALYTICS_PLUGIN_PATH . 'api/class.metrilo-validator.php');

class Metrilo_Client {
    
    private $backend_params = array();
    private $api_end_point;
    private $log_path;
    private $validator;
    
    public function __construct(
        $token,
        $platform,
        $plugin_version,
        $api_end_point,
        $log_path
    ) {
        $this->backend_params['token']         = $token;
        $this->backend_params['platform']      = $platform;
        $this->backend_params['pluginVersion'] = $plugin_version;
        $this->api_end_point                   = $api_end_point;
        $this->log_path                        = $log_path;
        $this->validator                       = new Metrilo_Validator($this->log_path);
    }
    
    public function backendCall($path, $body) {
        $connection                   = new Metrilo_Connection();
        $this->backend_params['time'] = round(microtime(true) * 1000);
        $body                         = array_merge($body, $this->backend_params);
        
        return $connection->post($this->api_end_point.$path, $body, false);
    }
    
    public function customer($customer) {
        $validCustomer = $this->validator->validateCustomer($customer);
        
        if ($validCustomer) {
            return $this->backendCall('/customer', ['params' => $customer]);
        }
    }
    
    public function customerBatch($customers) {
        $validCustomers = $this->validator->validateCustomers($customers);
        
        if (!empty($validCustomers)) {
            return $this->backendCall('/customer/batch', ['batch' => $validCustomers]);
        }
    }
    
    public function category($category) {
        $validCategory = $this->validator->validateCategory($category);
        
        if ($validCategory) {
            return $this->backendCall('/category', ['params' => $category]);
        }
    }
    
    public function categoryBatch($categories) {
        $validCategories = $this->validator->validateCategories($categories);
        
        if (!empty($validCategories)) {
            return $this->backendCall('/category/batch', ['batch' => $validCategories]);
        }
    }
    
    public function product($product) {
        $validProduct = $this->validator->validateProduct($product);
        
        if ($validProduct) {
            return $this->backendCall('/product', ['params' => $product]);
        }
    }
    
    public function productBatch($products) {
        $validProducts = $this->validator->validateProducts($products);
        
        if (!empty($validProducts)) {
            return $this->backendCall('/product/batch', ['batch' => $validProducts]);
        }
    }
    
    public function order($order) {
        $validOrder = $this->validator->validateOrder($order);
        
        if ($validOrder) {
            return $this->backendCall('/order', ['params' => $order]);
        }
    }
    
    public function orderBatch($orders) {
        $validOrders = $this->validator->validateOrders($orders);
        
        if (!empty($validOrders)) {
            return $this->backendCall('/order/batch', ['batch' => $validOrders]);
        }
    }
    
    public function createActivity($url, $data) {
        $connection = new Metrilo_Connection();
        $result     = $connection->post($url, $data, true);
        return $result['code'] == 200;
    }
}
