<?php
class Metrilo_Customer_Data
{
    private $chunk_items     = 50;
    private $customers_total = 0;
    private $db_connection;
    
    public function __construct()
    {
        global $wpdb;
        
        $this->db_connection = $wpdb;
    }
    
    public function get_customers($chunk_id)
    {
        $customers  = [];
        $user_ids = get_users(['fields' => ['ID'], 'number' => $this->chunk_items, 'offset' => $chunk_id]);
        
        foreach ($user_ids as $user_id) {
            $customer_data = get_userdata((int)$user_id->ID);
            $customer_data->data->first_name = get_user_meta((int)$user_id->ID, 'first_name', true);
            $customer_data->data->last_name  = get_user_meta((int)$user_id->ID, 'last_name', true);
            $customers[] = $customer_data;
        }
        
        return $customers;
    }
    
    public function get_customer_chunks()
    {
        $this->customers_total = (int)$this->db_connection->get_var(
            "SELECT count(id) FROM {$this->db_connection->users} ORDER BY id ASC"
        );
        
        return (int)ceil($this->customers_total / $this->chunk_items);
    }
}

return new Metrilo_Customer_Data();
