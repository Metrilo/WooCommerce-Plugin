<?php
class Metrilo_Order_Data
{
    private $chunk_items  = 50;
    private $orders_total = 0;
    private $db_connection;
    
    public function __construct()
    {
        global $wpdb;
        
        $this->db_connection = $wpdb;
    }
    
    public function get_orders($chunk_id)
    {
        $orders = [];
        $order_ids = $this->db_connection->get_col(
            $this->db_connection->prepare(
        "
                SELECT id
                FROM $this->db_connection->posts
                WHERE post_type = 'shop_order'
                ORDER BY id ASC
                limit '%d'
                offset '%d'
                ",
                $chunk_id,
                $this->chunk_items
            )
        );
        
        foreach ($order_ids as $order_id) {
            $orders[] = new WC_Order($order_id);
        }
        
        return $orders;
    }
    
    public function get_order_chunks()
    {
        $this->orders_total = (int)$this->db_connection->get_var(
            $this->db_connection->prepare(
                "
                SELECT count(id)
                FROM $this->db_connection->posts
                WHERE post_type = 'shop_order'
                ORDER BY id ASC
                "
            )
        );
        
        return (int)ceil($this->orders_total / $this->chunk_items);
    }
}

return new Metrilo_Order_Data();
