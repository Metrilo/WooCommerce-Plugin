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
            "SELECT id FROM {$this->db_connection->posts}
    WHERE post_type = 'product'
    limit {$this->chunk_items}
    offset {$chunk_id}"
        );
        
        foreach ($product_ids as $product_id) {
            $products[] = wc_get_product($product_id);
        }
        
        return $products;
    }
    
    public function get_product_chunks()
    {
        $this->products_total = (int)$this->db_connection->get_var(
            "SELECT count(id) FROM {$this->db_connection->posts}
    WHERE post_type = 'product' ORDER BY id ASC"
        );
        
        return (int)ceil($this->products_total / $this->chunk_items);
    }
}

return new Metrilo_Product_Data();


//    public $chunkItems = Metrilo_Analytics_Helper_Data::chunkItems;
//
//    public function getProducts($storeId, $chunkId)
//{
//    return $this->_getProductQuery($storeId)->setPageSize($this->chunkItems)->setCurPage($chunkId + 1);
//}
//
//    public function getProductChunks($storeId)
//{
//    $storeTotal = $this->_getProductQuery($storeId)->getSize();
//
//    return (int)ceil($storeTotal / $this->chunkItems);
//}
//
//    private function _getProductQuery($storeId)
//{
//    return Mage::getModel('catalog/product')
//        ->getCollection()
//        ->addStoreFilter($storeId)
//        ->addFieldToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
//        ->addAttributeToSelect(['entity_id','type_id','sku','created_at','updated_at','name','image','price','url_path', 'visibility']);
//}
//
//    public function getProductWithRequestPath($productId, $storeId) {
//    return Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);
//}