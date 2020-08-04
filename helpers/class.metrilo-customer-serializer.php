<?php
class Metrilo_Customer_Serializer
{
    public function serialize($customer)
    {
        $tags = [];
        foreach ($customer->roles as $role) {
            $tags[] = $role;
        }
        
        $customer_data = [
            'email'      => $customer->data->user_email,
            'createdAt'  => strtotime($customer->data->user_registered),
            'subscribed' => '', // default wordpress installation has no newsletter functionality
            'tags'       => $tags
        ];
        
        if ($customer->data->first_name) {
            $customer_data['firstName'] = $customer->data->first_name;
        }
        
        if ($customer->data->last_name) {
            $customer_data['lastName'] = $customer->data->last_name;
        }
        
        return $customer_data;
    }
}

return new Metrilo_Customer_Serializer();
