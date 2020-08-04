<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Metrilo_Import {
    
    public $import_chunk_size = 50;
    private $importing = false;
    
    public function set_importing_mode($mode){
        $this->importing = $mode;
    }
    
    public function output(){
        require_once( 'views/metrilo-import-view.php' );
    }
    
    public function get_customer_chunks() {
        $customer_data = new Metrilo_Customer_Data();
        return $customer_data->get_customer_chunks();
    }
    
    public function get_category_chunks() {
        $category_data = new Metrilo_Category_Data();
        return $category_data->get_category_chunks();
    }
    
    public function get_product_chunks() {
        $product_data = new Metrilo_Product_Data();
        return $product_data->get_product_chunks();
    }
    
    public function get_order_chunks() {
        $order_data = new Metrilo_Order_Data();
        return $order_data->get_order_chunks();
    }
    
}

return new Metrilo_Import();
