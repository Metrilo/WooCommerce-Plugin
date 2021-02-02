<?php

class Metrilo_Order_Serializer
{
    public function serialize($order)
    {
        $email = $order->get_billing_email();
        $phone = $order->get_billing_phone();
        
        if(!trim($order->get_billing_email())) {
            return;
        }
        
        $order_items = $order->get_items();
        $order_products = [];
        
        foreach ($order_items as $order_item) {
        
            $order_products[] = [
                'productId' => (string)$order_item->get_product_id(),
                'quantity'  => $order_item->get_quantity()
            ];
        }
        
        $order_billing = [
            "firstName"     => $order->get_billing_first_name(),
            "lastName"      => $order->get_billing_last_name(),
            "address"       => $order->get_billing_address_1(),
            "city"          => $order->get_billing_city(),
            "countryCode"   => $order->get_billing_country(),
            "phone"         => $phone,
            "postcode"      => $order->get_billing_postcode(),
            "paymentMethod" => $order->get_payment_method_title()
        ];
        
        return [
            'id'        => (string)$order->get_id(),
            'createdAt' => strtotime($order->get_date_created()),
            'email'     => $email ? $email : $phone . '@phone_email',
            'amount'    => $order->get_total(),
            'coupons'   => $order->get_coupon_codes(),
            'status'    => $order->get_status(),
            'products'  => $order_products,
            'billing'   => $order_billing
        ];
    }
}

return new Metrilo_Order_Serializer();
