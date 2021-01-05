<?php
    if (class_exists('WooCommerce')) {
        // if visitor is viewing product
        if (is_product()) {
            $product    = $this->resolve_product(get_queried_object_id());
            $product_id = method_exists($product, 'get_id') ? $product->get_id() : $product->id;
            $this->put_event_in_queue("window.metrilo.viewProduct('$product_id');");
        }
        
        // if visitor is viewing product category
        if (is_product_category()) {
            $category_id = get_queried_object()->term_id;
            $this->put_event_in_queue("window.metrilo.viewCategory('$category_id');");
        }
        
        // if visitor is viewing search page
        if (is_search()) {
            $search_query = get_search_query();
            $search_link  = get_search_link();
            $this->put_event_in_queue("window.metrilo.search('$search_query', '$search_link');");
        }
        
        // if visitor is viewing shopping cart page
        if (is_cart()) {
            $this->put_event_in_queue("window.metrilo.customEvent('view_cart');");
        }
        
        // if visitor is anywhere in the checkout process
        if (is_order_received_page()) {
            $url  = get_permalink();
            $name = json_encode(['name' => get_the_title()]);
            $this->put_event_in_queue("window.metrilo.viewPage('$url', $name);");
        } elseif (function_exists('is_checkout_pay_page') && is_checkout_pay_page()) {
            $this->put_event_in_queue("window.metrilo.customEvent('checkout_payment');");
        } elseif (is_checkout()) {
            $this->put_event_in_queue("window.metrilo.checkout();");
        }
    }
    
    //
    //  ** GENERIC WordPress tracking - doesn't require WooCommerce in order to work **//
    //
    //  if visitor is viewing homepage or any text page
    if ((is_front_page() || is_page() || is_shop()) && !is_cart() && !is_checkout()) {
        $url  = get_permalink();
        $name = json_encode(['name' => get_the_title()]);
        $this->put_event_in_queue("window.metrilo.viewPage('$url', $name);");
    }
    
    //  if visitor is viewing post
    if (is_single() && !is_product()) {
        $post_id      = get_the_id();
        $name_and_url = json_encode(['name' => get_the_title(), 'url' => get_permalink($post_id)]);
        $this->put_event_in_queue("window.metrilo.viewArticle('$post_id', $name_and_url);");
    }
    
    if ($this->identify_call_data !== false) {
        $this->render_identify();
    }
    
    if ($this->events_queue) {
        $this->render_events();
    }
    