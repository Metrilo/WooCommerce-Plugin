<?php
    // initiate possible user roles from settings
    
    if(is_admin()){
        global $wp_roles;
        $possible_ignore_roles = array();
        foreach($wp_roles->roles as $role => $stuff){
            $possible_ignore_roles[$role] = $stuff['name'];
        }
    }
    
    $this->form_fields = array(
        'api_token' => array(
            'title'             => __( 'API Token', 'metrilo-analytics' ),
            'type'              => 'text',
            'description'       => __( '<strong style="color: green;">(Required)</strong> Enter your Metrilo API token. You can find it under "Settings" in your Metrilo account.<br /> Don\'t have one? <a href="https://www.metrilo.com/signup?ref=woointegration" target="_blank">Sign-up for free</a> now, it only takes a few seconds.', 'metrilo-analytics' ),
            'desc_tip'          => false,
            'default'           => ''
        ),
        'api_secret' => array(
            'title'             => __( 'API Secret', 'metrilo-analytics' ),
            'type'              => 'text',
            'description'       => __( '<strong style="color: green;">(Required)</strong> Enter your Metrilo API secret key.', 'metrilo-analytics' ),
            'desc_tip'          => false,
            'default'           => ''
        )
    );
    
    $this->form_fields['ignore_for_roles'] = array(
        'title'             => __( 'Ignore tracking for roles', 'metrilo-analytics' ),
        'type'              => 'multiselect',
        'description'       => __( '<strong style="color: #999;">(Optional)</strong> If you check any of the roles, tracking data will be ignored for WP users with this role', 'metrilo-analytics' ),
        'desc_tip'          => false,
        'default'           => '',
        'options'           => $possible_ignore_roles
    );
    
    $this->form_fields['ignore_for_events'] = array(
        'title'             => __( 'Do not send the selected tracking events', 'metrilo-analytics' ),
        'type'              => 'multiselect',
        'description'       => __( '<strong style="color: #999;">(Optional)</strong> Tracking won\'t be sent for the selected events', 'metrilo-analytics' ),
        'desc_tip'          => false,
        'default'           => '',
        'options'           => $this->possible_events
    );
    
    $product_brand_taxonomy_options = array('none' => 'None');
    foreach(wc_get_attribute_taxonomies() as $v){
        $product_brand_taxonomy_options[$v->attribute_name] = $v->attribute_label;
    }
    
    $this->form_fields['product_brand_taxonomy'] = array(
        'title'             => __( 'Product brand attribute', 'metrilo-analytics' ),
        'type'              => 'select',
        'description'       => __( '<strong style="color: #999;">(Optional)</strong> If you check any of those attributes, it\'ll be synced with Metrilo as the product\'s brand' ),
        'desc_tip'          => false,
        'default'           => '',
        'options'           => $product_brand_taxonomy_options
    );
    
    $this->form_fields['send_roles_as_tags'] = array(
        'title'             => __( 'Send user roles as tags', 'metrilo-analytics' ),
        'type'              => 'checkbox',
        'description'       => __( '<strong style="color: #999;">(Optional)</strong> If you check this, your user\'s roles will be sent to Metrilo as tags when they browse your website' ),
        'desc_tip'          => false,
        'label'             => 'Send roles as tags',
        'default'           => false
    );
    
    $this->form_fields['add_tag_to_every_customer'] = array(
        'title'             => __( 'Add this tag to every customer', 'metrilo-analytics' ),
        'type'              => 'text',
        'description'       => __( '<strong style="color: #999;">(Optional)</strong> If you enter tag, it will be added to every customer synced with Metrilo' ),
        'desc_tip'          => false,
        'label'             => 'Add this tag to every customer in Metrilo',
        'default'           => ''
    );
    
    $this->form_fields['prefix_order_ids'] = array(
        'title'             => __( 'Prefix order IDs with', 'metrilo-analytics' ),
        'type'              => 'text',
        'description'       => __( '<strong style="color: #999;">(Optional)</strong> If you enter a prefix, all your order IDs will be prefixed with it. Useful for multiple stores connected to one Metrilo account' ),
        'desc_tip'          => false,
        'label'	            => 'Prefix all your order IDs',
        'default'           => ''
    );
    
    $this->form_fields['http_or_https'] = array(
        'title'             => __( 'Sync data to Metrilo with HTTPS', 'metrilo-analytics' ),
        'type'              => 'select',
        'description'       => __( '<strong style="color: #999;">(Optional)</strong> Set if data should be sent to Metrilo from your WooCommerce backend through HTTPS or HTTP' ),
        'desc_tip'          => false,
        'default'           => '',
        'options'           => array('https' => 'Yes (HTTPS)', 'http' => 'No (HTTP)')
    );
    