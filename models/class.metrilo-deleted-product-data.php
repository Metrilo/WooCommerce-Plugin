<?php
class Metrilo_Deleted_Product_Data
{
    private $db_connection;
    
    public function __construct()
    {
        global $wpdb;
        
        $this->db_connection = $wpdb;
    }
    
    public function get_deleted_products()
    {
        $deleted_products        = [];
        $deleted_product_options = [];
        $parent_id = '';
        $child_id  = '';
        $qty       = '';
        $subtotal  = '';
        
        $order_items_table = $this->db_connection->prefix . 'woocommerce_order_items';
        $order_items = $this->db_connection->get_results(
                "
                SELECT order_items.order_item_id, order_items.order_item_name
                FROM {$order_items_table} AS order_items
                JOIN {$this->db_connection->order_itemmeta} AS item_meta
                ON order_items.order_item_id = item_meta.order_item_id
                AND item_meta.meta_key IN ('_product_id', '_variation_id')
                WHERE item_meta.meta_value
                NOT IN (
                  SELECT id FROM {$this->db_connection->posts} AS posts
                  WHERE posts.post_type IN ('product', 'product_variation')
                )
                AND item_meta.meta_value != 0
                GROUP BY item_meta.meta_value
                "
            );
        
        foreach ($order_items as $item) {
            
            $deleted_item_meta = $this->db_connection->get_results(
                "
                SELECT *
                FROM {$this->db_connection->order_itemmeta}
                WHERE order_item_id = {$item->order_item_id}
                AND meta_key IN ('_product_id', '_variation_id', '_qty', '_line_subtotal')
                "
            );
            
            foreach ($deleted_item_meta as $item_meta) {
                if ($item_meta->meta_key === '_product_id') {
                    $parent_id = $item_meta->meta_value;
                }
                
                if ($item_meta->meta_key === '_qty') {
                    $qty = $item_meta->meta_value;
                }
                
                if ($item_meta->meta_key === '_line_subtotal') {
                    $subtotal = $item_meta->meta_value;
                }
                
                if ($item_meta->meta_key === '_variation_id' && $item_meta->meta_value !== '0') {
                    $child_id = $item_meta->meta_value;
                }
                
                $subtotal = $subtotal / $qty;
                
                if ($child_id) {
                    $deleted_product_options = [
                        'id'       => $child_id,
                        'sku'      => $item->order_item_name,
                        'name'     => $item->order_item_name,
                        'price'    => $subtotal,
                        'imageUrl' => ''
                    ];
                }
            }
            
            $deleted_products[] = [
                'categories' => [],
                'id'         => $parent_id ? $parent_id : $child_id,
                'sku'        => $item->order_item_name,
                'imageUrl'   => '',
                'name'       => $item->order_item_name,
                'price'      => $child_id ? 0 : $subtotal,
                'url'        => '',
                'options'    => $child_id ? [$deleted_product_options] : $deleted_product_options
            ];
        }
        
        return $deleted_products;
    }
}

return new Metrilo_Deleted_Product_Data();
