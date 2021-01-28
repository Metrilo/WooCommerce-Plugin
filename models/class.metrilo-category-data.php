<?php
class Metrilo_Category_Data
{
    private $chunk_items      = 50;
    private $categories_total = 0;
    private $db_connection;
    
    public function __construct()
    {
        global $wpdb;
        
        $this->db_connection = $wpdb;
    }
    
    public function get_categories($chunk_id)
    {
        $categories   = [];
        $category_ids = $this->db_connection->get_col(
            $this->db_connection->prepare(
            "
                SELECT term_taxonomy_id
                FROM {$this->db_connection->term_taxonomy} AS term_taxonomy
                LEFT JOIN {$this->db_connection->terms} AS terms
                ON term_taxonomy.term_id = terms.term_id
                WHERE term_taxonomy.taxonomy = 'product_cat'
                limit %d
                offset %d
                ",
                $this->chunk_items,
                $chunk_id
            )
        );
        
        foreach ($category_ids as $category_id) {
            $categories[] = get_term_by( 'id', $category_id, 'product_cat' );
        }
        
        return $categories;
    }
    
    public function get_category_chunks()
    {
        $this->categories_total = $this->db_connection->get_var(
            "
                SELECT count(term_taxonomy_id)
                FROM {$this->db_connection->term_taxonomy} AS term_taxonomy
                LEFT JOIN {$this->db_connection->terms} AS terms
                ON term_taxonomy.term_id = terms.term_id
                WHERE term_taxonomy.taxonomy = 'product_cat'
                "
        );
        
        return (int)ceil($this->categories_total / $this->chunk_items);
    }
}

return new Metrilo_Category_Data();
