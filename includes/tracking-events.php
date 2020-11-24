<?php
    if(class_exists('WooCommerce')){
        // /** check certain tracking scenarios **/
        // if visitor is viewing product
        if(!$this->single_item_tracked && is_product()){
            $product = $this->resolve_product(get_queried_object_id());
            $product_id = method_exists($product, 'get_id') ? $product->get_id() : $product->id;
            $this->put_event_in_queue(
                "window.metrilo.viewProduct('"
                . $product_id
                . "');"
            );
            $this->single_item_tracked = true;
        }
        
        // if visitor is viewing product category
        if (!$this->single_item_tracked && is_product_category()) {
            $category = get_queried_object();
            $this->put_event_in_queue(
                "window.metrilo.viewCategory('"
                . $category->term_id
                . "');"
                
            );
            $this->single_item_tracked = true;
        }
    
        // if visitor is viewing search page
        if (!$this->single_item_tracked && is_search()) {
            $search = get_queried_object();
            $this->put_event_in_queue(
                "window.metrilo.search('"
                . get_search_query()
                . "', '"
                . get_search_link()
                . "');"
        
            );
            $this->single_item_tracked = true;
        }
        
        // if visitor is viewing shopping cart page
        if(!$this->single_item_tracked && is_cart()){
            $this->put_event_in_queue(
                "window.metrilo.customEvent('view_cart');"
            );
            $this->single_item_tracked = true;
        }
        // if visitor is anywhere in the checkout process
        if (!$this->single_item_tracked && is_order_received_page()) {
            $this->put_event_in_queue(
                "window.metrilo.viewPage('"
                . get_permalink()
                . "', "
                . json_encode(['name' => get_the_title()])
                . ");"
            );
            $this->single_item_tracked = true;
            
        } elseif (
            !$this->single_item_tracked &&
            function_exists('is_checkout_pay_page') &&
            is_checkout_pay_page()
        ) {
            $this->put_event_in_queue(
                "window.metrilo.customEvent('checkout_payment');"
            );
            $this->single_item_tracked = true;
        } elseif (!$this->single_item_tracked && is_checkout()) {
            $this->put_event_in_queue(
                "window.metrilo.checkout();"
            );
            $this->single_item_tracked = true;
        }
    }
    //
    //  ** GENERIC WordPress tracking - doesn't require WooCommerce in order to work **//
    //
    //  if visitor is viewing homepage or any text page
    if (!$this->single_item_tracked && is_front_page()) {
        $this->put_event_in_queue(
            "window.metrilo.viewPage('"
            . get_permalink()
            . "', "
            . json_encode(['name' => get_the_title()])
            . ");"
        );
        $this->single_item_tracked = true;
    } elseif (!$this->single_item_tracked && is_page()) {
        $this->put_event_in_queue(
            "window.metrilo.viewPage('"
            . get_permalink()
            . "', "
            . json_encode(['name' => get_the_title()])
            . ");"
        );
        $this->single_item_tracked = true;
    }

    //  if visitor is viewing post
    if (!$this->single_item_tracked && is_single()) {
        $post_id = get_the_id();
        $this->put_event_in_queue(
            "window.metrilo.viewArticle('"
            . $post_id
            . "', "
            . json_encode(['name' => get_the_title(), 'url' => get_permalink($post_id)])
            . ");"
        );
        $this->single_item_tracked = true;
    }
    
    //  check if there are events in the queue to be sent to Metrilo
    if ($this->identify_call_data !== false) {
        $this->render_identify();
    }
    
    if (count($this->events_queue) > 0) {
        $this->render_events();
    }