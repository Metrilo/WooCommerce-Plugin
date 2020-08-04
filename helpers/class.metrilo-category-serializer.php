<?php
class Metrilo_Category_Serializer
{
    public function serialize($category)
    {
        return [
            'id'   => (string)$category->term_id,
            'name' => $category->name,
            'url'  => get_category_link($category->term_id)
        ];
    }
}

return new Metrilo_Category_Serializer();
