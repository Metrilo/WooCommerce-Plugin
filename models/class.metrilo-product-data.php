<?php
class Metrilo_Product_Data
{
    private $chunk_items    = 50;
    private $products_total = 0;
    private $db_connection;
    
    public function __construct()
    {
        global $wpdb;
        
        $this->db_connection = $wpdb;
    }
    
    public function get_products($chunk_id)
    {
        $products = [];
        $product_ids = $this->db_connection->get_col(
            $this->db_connection->prepare(
                "
                SELECT id
                FROM $this->db_connection->posts
                WHERE post_type = 'product'
                limit %d
                offset %d
                ",
                $this->chunk_items,
                $chunk_id
            )
        );
        
        foreach ($product_ids as $product_id) {
            $products[] = wc_get_product($product_id);
        }
        
        return $products;
    }
    
    public function get_product_chunks()
    {
        $this->products_total = (int)$this->db_connection->get_var(
            $this->db_connection->prepare(
                "
                SELECT count(id)
                FROM $this->db_connection->posts
                WHERE post_type = 'product' ORDER BY id ASC
                "
            )
        );
        
        return (int)ceil($this->products_total / $this->chunk_items);
    }
}

return new Metrilo_Product_Data();
