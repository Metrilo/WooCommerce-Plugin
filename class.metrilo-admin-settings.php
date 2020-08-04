<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Metrilo_Admin_Settings extends WC_Integration {
    
    private static $initiated = false;
    public $import_chunk_size = 50;
    private $woo = false;
    private $possible_events = array(
        'view_product'     => 'View Product',
        'view_category'    => 'View Category',
        'view_article'     => 'View Article',
        'add_to_cart'      => 'Add to cart',
        'remove_from_cart' => 'Remove from cart',
        'view_cart'        => 'View Cart',
        'checkout_start'   => 'Started Checkout',
        'identify'         => 'Identify calls'
    );
    private $endpoint_domain = 'p.metrilo.com';
    public  $tracking_endpoint_domain = 't.metrilo.com';
    
    private $activity_helper;
    private $api_client;
    
    private $customer_data;
    private $customer_serializer;
    
    private $category_data;
    private $category_serializer;
    
    private $order_data;
    private $order_serializer;
    
    
    /**
     *
     *
     * Initialization and hooks
     *
     *
     */
    
    public function __construct() {
        global $woocommerce, $metrilo_woo_analytics_integration;
        
        $this->woo = function_exists('WC') ? WC() : $woocommerce;
        
        $this->id = 'metrilo-analytics';
        $this->method_title = __( 'Metrilo', 'metrilo-analytics' );
        $this->method_description = __( 'Metrilo offers powerful yet simple CRM & Analytics for WooCommerce and WooCommerce Subscription Stores. Enter your API key to activate analytics tracking.', 'metrilo-analytics' );
        
        
        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();
        
        // Fetch the integration settings
        $this->api_token                 = $this->get_option('api_token', false);
        $this->api_secret                = $this->get_option('api_secret', false);
        $this->ignore_for_roles          = $this->get_option('ignore_for_roles', false);
        $this->ignore_for_events         = $this->get_option('ignore_for_events', false);
        $this->product_brand_taxonomy    = $this->get_option('product_brand_taxonomy', 'none');
        $this->send_roles_as_tags        = $this->get_option('send_roles_as_tags', 'no');
        $this->add_tag_to_every_customer = $this->get_option('add_tag_to_every_customer', '');
        $this->prefix_order_ids          = $this->get_option('prefix_order_ids', '');
        $this->http_or_https             = $this->get_option('http_or_https', 'https') == 'https' ? 'https' : 'http';
        
        // initiate woocommerce hooks and activities
        add_action('woocommerce_init', array($this, 'on_woocommerce_init'));
        
        // hook to integration settings update
        add_action( 'woocommerce_update_options_integration_' .  $this->id, array($this, 'process_admin_options'));
    }
    
    public function on_woocommerce_init(){
        $this->load_resources();
        
        // check if API token and Secret are both entered
        $this->check_for_keys();
        
        // hook to WooCommerce models
        $this->ensure_hooks();
    }
    
    public function load_resources() {
        $this->activity_helper     = require_once(METRILO_ANALYTICS_PLUGIN_PATH . 'helpers/class.metrilo-activity.php');
        $this->api_client          = require_once(METRILO_ANALYTICS_PLUGIN_PATH . 'helpers/class.metrilo-api-client.php');
        
        $this->customer_data       = require_once(METRILO_ANALYTICS_PLUGIN_PATH . 'models/class.metrilo-customer-data.php');
        $this->customer_serializer = require_once(METRILO_ANALYTICS_PLUGIN_PATH . 'helpers/class.metrilo-customer-serializer.php');
        
        $this->category_data       = require_once(METRILO_ANALYTICS_PLUGIN_PATH . 'models/class.metrilo-category-data.php');
        $this->category_serializer = require_once(METRILO_ANALYTICS_PLUGIN_PATH . 'helpers/class.metrilo-category-serializer.php');
        
        $this->product_data        = require_once(METRILO_ANALYTICS_PLUGIN_PATH . 'models/class.metrilo-product-data.php');
        $this->product_serializer  = require_once(METRILO_ANALYTICS_PLUGIN_PATH . 'helpers/class.metrilo-product-serializer.php');
        
        $this->order_data          = require_once(METRILO_ANALYTICS_PLUGIN_PATH . 'models/class.metrilo-order-data.php');
        $this->order_serializer    = require_once(METRILO_ANALYTICS_PLUGIN_PATH . 'helpers/class.metrilo-order-serializer.php');
    }
    
    public function check_for_keys(){
        if(is_admin()){
            
            if((empty($this->api_token) || empty($this->api_secret)) && empty($_POST['save'])){
                add_action('admin_notices', array($this, 'admin_keys_notice'));
            }
            
            if(!empty($_POST['save'])){
                $key    = trim($_POST['woocommerce_metrilo-analytics_api_token']);
                $secret = trim($_POST['woocommerce_metrilo-analytics_api_secret']);
                
                if(!empty($key) && !empty($secret)){
                    # submit to Metrilo to validate credentialswq
                    $response = $this->activity_helper->create_activity('integrated');
                    
                    if($response) {
                        add_action('admin_notices', array($this, 'admin_import_invite'));
                    } else {
                        WC_Admin_Settings::add_error($this->admin_import_error_message());
                    }
                }
            }
        }
    }
    
    public function ensure_hooks(){
        add_action('wp_ajax_metrilo_import', array($this, 'metrilo_import'));
//        add_action('wp_ajax_admin_metrilo_import', array('Metrilo_Import', 'metrilo_import'));
        add_action('admin_menu', array($this, 'setup_admin_pages'));
    }
    
    public function setup_admin_pages(){
        add_submenu_page('tools.php', 'Export to Metrilo', 'Export to Metrilo', 'export', 'metrilo-import', array($this, 'metrilo_import_page'));
    }
    
    public function metrilo_import_page(){
        wp_enqueue_script('jquery');
        $metrilo_import = require_once(METRILO_ANALYTICS_PLUGIN_PATH . 'class.metrilo-import.php');
//        $metrilo_import->prepare_import();
        if(!empty($_GET['import'])){
            $metrilo_import->set_importing_mode(true);
//            $metrilo_import->prepare_order_chunks($this->import_chunk_size);
        }
        $metrilo_import->output();
    }
    
    public function metrilo_import() {
        $result          = false;
        $plugin_log_path = METRILO_ANALYTICS_PLUGIN_PATH . 'Metrilo_Analytics.log';
        
        try {
            $chunk_id    = (int)$_REQUEST['chunkId'];
            $import_type = (string)$_REQUEST['importType'];
            $client      = $this->api_client->get_client();
            
            switch($import_type) {
                case 'customers':
                    if ($chunk_id == 0) {
                        $this->activity_helper->create_activity('import_start');
                    }
                    $serialized_customers = $this->serialize_import_records($this->customer_data->get_customers($chunk_id), $this->customer_serializer);
//                    $result               = $client->customerBatch($serialized_customers);
                    $this->error_logger('serializedCustomers ', $serialized_customers, $plugin_log_path);
                    break;
                case 'categories':
                    $serialized_categories = $this->serialize_import_records($this->category_data->get_categories($chunk_id), $this->category_serializer);
//                    $result                = $client->categoryBatch($serialized_categories);
                    $this->error_logger('serializedCategories ', $serialized_categories, $plugin_log_path);
                    break;
                case 'deletedProducts':
//                    $deletedProductOrders = $this->_deletedProductOrderObject->getDeletedProductOrders($storeId);
//                    if ($deletedProductOrders) {
//                        $serializedDeletedProducts = Mage::helper('metrilo_analytics/deletedproductserializer')->serialize($deletedProductOrders);
//                        $deletedProductChunks      = array_chunk($serializedDeletedProducts, Metrilo_Analytics_Helper_Data::chunkItems);
//                        foreach($deletedProductChunks as $chunk) {
//                            $client->productBatch($chunk);
//                        }
//                    }
                    break;
                case 'products':
                    $serialized_products = $this->serialize_import_records($this->product_data->get_products($chunk_id), $this->product_serializer);
//                    $result              = $client->productBatch($serialized_products);
                    $this->error_logger('serializedProducts ', $serialized_products, $plugin_log_path);
                    break;
                case 'orders':
                    $serialized_orders = $this->serialize_import_records($this->order_data->get_orders($chunk_id), $this->order_serializer);
//                    $result            = $client->orderBatch($serialized_orders);
                    $this->error_logger('serializedOrders ', $serialized_orders, $plugin_log_path);
                    if ($chunk_id == (int)$_REQUEST['ordersChunks'] - 1) {
                        $this->activity_helper->create_activity('import_end');
                    }
                    break;
                default:
                    $result = false;
                    break;
            }
            return json_encode($result);
            wp_die();
            
        } catch (\Exception $e) {
            $this->error_logger('metrilo_import ', $e->getMessage(), $plugin_log_path);
        }
    }
    
    private function serialize_import_records($records, $serializer) {
        $serializedData = [];
        foreach($records as $record) {
            $serializedRecord = $serializer->serialize($record);
            if ($serializedRecord) {
                $serializedData[] = $serializedRecord;
            }
        }
        
        return $serializedData;
    }
    
    private function error_logger($import_type, $serialized_data, $plugin_log_path) {
        return error_log(json_encode(array($import_type => $serialized_data)) . PHP_EOL, 3, $plugin_log_path);
    }
    
    public function admin_keys_notice(){
        $message = 'Almost done! Just enter your Metrilo API key and secret';
        echo '<div class="updated"><p>'.$message.' <a href="'.admin_url('admin.php?page=wc-settings&tab=integration').'">here</a></p></div>';
    }
    
    public function admin_import_invite(){
        echo '<div class="updated"><p>Awesome! Have you tried <a href="'.admin_url('tools.php?page=metrilo-import').'"><strong>importing your existing data to Metrilo</strong></a>?</p></div>';
    }
    
    public function admin_import_error_message(){
        return 'The API Token and/or API Secret you have entered are invalid. You can find the correct ones in Settings -> Installation in your Metrilo account.';
    }
    
    /**
     * Initialize integration settings form fields.
     */
    public function init_form_fields() {
        
        // initiate possible user roles from settings
        $possible_ignore_roles = false;
        
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
        
        if($possible_ignore_roles){
            $this->form_fields['ignore_for_roles'] = array(
                'title'             => __( 'Ignore tracking for roles', 'metrilo-analytics' ),
                'type'              => 'multiselect',
                'description'       => __( '<strong style="color: #999;">(Optional)</strong> If you check any of the roles, tracking data will be ignored for WP users with this role', 'metrilo-analytics' ),
                'desc_tip'          => false,
                'default'           => '',
                'options'           => $possible_ignore_roles
            );
        }
        
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
    }
}
