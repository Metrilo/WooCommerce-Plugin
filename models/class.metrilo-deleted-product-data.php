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
        
        $order_items_table = $this->db_connection->prefix . 'woocommerce_order_items';
        $deleted_items = $this->db_connection->get_results(
                "
                SELECT
                  order_items.order_item_name,
                  item_meta1.meta_value AS product_id,
                  item_meta2.meta_value AS variation_id,
                  item_meta3.meta_value AS qty,
                  item_meta4.meta_value AS subtotal
                FROM {$order_items_table} AS order_items
                JOIN {$this->db_connection->order_itemmeta} AS item_meta1
                  ON order_items.order_item_id = item_meta1.order_item_id
                  AND item_meta1.meta_key = '_product_id'
                JOIN {$this->db_connection->order_itemmeta} AS item_meta2
                  ON order_items.order_item_id = item_meta2.order_item_id
                  AND item_meta2.meta_key = '_variation_id'
                JOIN {$this->db_connection->order_itemmeta} AS item_meta3
                  ON order_items.order_item_id = item_meta3.order_item_id
                  AND item_meta3.meta_key = '_qty'
                JOIN {$this->db_connection->order_itemmeta} AS item_meta4
                  ON order_items.order_item_id = item_meta4.order_item_id
                  AND item_meta4.meta_key = '_line_subtotal'
                WHERE
                  item_meta1.meta_value NOT IN (SELECT id FROM wp_posts WHERE wp_posts.post_type = 'product')
                OR
                  (
                      item_meta2.meta_value NOT IN (SELECT id FROM wp_posts WHERE wp_posts.post_type = 'product_variation')
                    AND
                      item_meta2.meta_value != 0
                  )
                GROUP BY item_meta1.meta_value, item_meta2.meta_value
                "
            );
        
        foreach ($deleted_items as $item) {
            $configurable_product = ($item->variation_id !== '0');
            $subtotal = $item->subtotal / $item->qty;
            
            if ($configurable_product) {
                $deleted_product_options = [
                    'id'       => $item->variation_id,
                    'sku'      => '',
                    'name'     => $item->order_item_name,
                    'price'    => $subtotal,
                    'imageUrl' => ''
                ];
            }
    
            $deleted_products[] = [
                'categories' => [],
                'id'         => $item->product_id,
                'sku'        => '',
                'imageUrl'   => '',
                'name'       => $item->order_item_name,
                'price'      => $configurable_product ? 0 : $subtotal,
                'url'        => '',
                'options'    => $configurable_product ? [$deleted_product_options] : $deleted_product_options
            ];
            
        }
    
        return $deleted_products;
    }
}

return new Metrilo_Deleted_Product_Data();
