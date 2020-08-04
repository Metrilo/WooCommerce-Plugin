<?php
class Metrilo_Product_Serializer
{
    public function serialize($product)
    {
        $product_options = [];
        
        if ($product->has_child()) {
            $product_variations = $product->get_available_variations();
            foreach ($product_variations as $product_variation) {
                
                $product_variation_name = '';
                foreach ($product_variation['attributes'] as $attribute) {
                    $product_variation_name .= '-' . $attribute;
                }
                
                $product_options[] = [
                    'id'       => (string)$product_variation['variation_id'],
                    'sku'      => $product_variation['sku'],
                    'name'     => $product_variation_name,
                    'price'    => $product_variation['display_price'],
                    'imageUrl' => wp_get_attachment_image_url($product_variation['image_id'], 'full')
                ];
            }
        }
        
        $serialized_product = [
            'categories' => array_map('strval', $product->get_category_ids()),
            'id'         => (string)$product->get_id(),
            'imageUrl'   => wp_get_attachment_image_url($product->get_image_id(), 'full'),
            'name'       => $product->get_name(),
            'price'      => $product->get_price(),
            'url'        => $product->get_permalink(),
            'options'    => $product_options
        ];
        
        if ($product->get_sku()) {
            $serialized_product['sku'] = $product->get_sku();
        }
        
        return $serialized_product;
    }
}

return new Metrilo_Product_Serializer();
