<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'Metrilo_Integration' ) ) {
    class Metrilo_Integration extends WC_Integration
    {
        private $woo = false;
        private $events_queue = [];
        private $has_events_in_cookie = false;
        private $identify_call_data = false;
        private $user_tags = [];
        private $possible_events = array(
            'view_product' => 'View Product',
            'view_category' => 'View Category',
            'view_article' => 'View Article',
            'add_to_cart' => 'Add to cart',
            'remove_from_cart' => 'Remove from cart',
            'view_cart' => 'View Cart',
            'checkout_start' => 'Started Checkout',
            'identify' => 'Identify calls'
        );
        private $plugin_log_path = METRILO_ANALYTICS_PLUGIN_PATH . 'Metrilo_Analytics.log';
        
        public $import_chunk_size        = 50;
        public $tracking_endpoint_domain = 'trk.mtrl.me';
        public $activity_enpoint_domain  = 'p.metrilo.com';
        
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
        
        public function __construct()
        {
            global $woocommerce, $metrilo_woo_analytics_integration;
            
            $this->woo = function_exists('WC') ? WC() : $woocommerce;
            
            $this->id = 'metrilo-analytics';
            $this->method_title = __('Metrilo', 'metrilo-analytics');
            $this->method_description = __('Metrilo offers powerful yet simple CRM & Analytics for WooCommerce and WooCommerce Subscription Stores. Enter your API key to activate analytics tracking.', 'metrilo-analytics');
            
            
            // Load the settings.
            $this->init_form_fields();
            $this->init_settings();
            
            // Fetch the integration settings
            $this->api_token = $this->get_option('api_token', false);
            $this->api_secret = $this->get_option('api_secret', false);
            $this->ignore_for_roles = $this->get_option('ignore_for_roles', false);
            $this->ignore_for_events = $this->get_option('ignore_for_events', []);
            $this->product_brand_taxonomy = $this->get_option('product_brand_taxonomy', 'none');
            $this->send_roles_as_tags = $this->get_option('send_roles_as_tags', false);
            $this->add_tag_to_every_customer = $this->get_option('add_tag_to_every_customer', '');
            $this->prefix_order_ids = $this->get_option('prefix_order_ids', '');
            $this->http_or_https = $this->get_option('http_or_https', 'https') == 'https' ? 'https' : 'http';
            $this->accept_tracking = true;
            $this->tracking_library = $this->initialize_metrilo_library();
            
            // initiate woocommerce hooks and activities
            add_action('woocommerce_init', array($this, 'on_woocommerce_init'));
            
            // hook to integration settings update
            add_action('woocommerce_update_options_integration_' . $this->id, array($this, 'process_admin_options'));
        }
        
        public function on_woocommerce_init()
        {
            $this->load_helpers_and_data_models();
            
            // check if I should clear the events cookie queue
            $this->check_for_metrilo_clear();
            
            // check if API token and Secret are both entered
            $this->check_for_keys();
            
            // hook to WooCommerce models
            $this->ensure_hooks();
            
            // ensure identification
            $this->ensure_identify();
            
            // process cookie events
            $this->process_cookie_events();
        }
        
        public function load_helpers_and_data_models()
        {
            $this->activity_helper = include_once(METRILO_ANALYTICS_PLUGIN_PATH . 'helpers/class.metrilo-activity.php');
            $this->api_client = include_once(METRILO_ANALYTICS_PLUGIN_PATH . 'helpers/class.metrilo-api-client.php');
            
            $this->customer_data = include_once(METRILO_ANALYTICS_PLUGIN_PATH . 'models/class.metrilo-customer-data.php');
            $this->customer_serializer = include_once(METRILO_ANALYTICS_PLUGIN_PATH . 'helpers/class.metrilo-customer-serializer.php');
            
            $this->category_data = include_once(METRILO_ANALYTICS_PLUGIN_PATH . 'models/class.metrilo-category-data.php');
            $this->category_serializer = include_once(METRILO_ANALYTICS_PLUGIN_PATH . 'helpers/class.metrilo-category-serializer.php');
    
            $this->deleted_product_data = include_once(METRILO_ANALYTICS_PLUGIN_PATH . 'models/class.metrilo-deleted-product-data.php');
            
            $this->product_data = include_once(METRILO_ANALYTICS_PLUGIN_PATH . 'models/class.metrilo-product-data.php');
            $this->product_serializer = include_once(METRILO_ANALYTICS_PLUGIN_PATH . 'helpers/class.metrilo-product-serializer.php');
            
            $this->order_data = include_once(METRILO_ANALYTICS_PLUGIN_PATH . 'models/class.metrilo-order-data.php');
            $this->order_serializer = include_once(METRILO_ANALYTICS_PLUGIN_PATH . 'helpers/class.metrilo-order-serializer.php');
        }
        
        public function check_for_keys()
        {
            if (!is_admin()) {
                return;
            }
            
            if ((empty($this->api_token) || empty($this->api_secret)) && empty($_POST['save'])) {
                add_action('admin_notices', array($this, 'admin_keys_notice'));
                return;
            }
    
            if(empty($_POST['save'])) {
                return;
            }
            
            $key = trim($_POST['woocommerce_metrilo-analytics_api_token']);
            $secret = trim($_POST['woocommerce_metrilo-analytics_api_secret']);
            
            if(empty($key) || empty($secret)) {
                return;
            }
            
            $response = $this->activity_helper->create_activity('integrated', $this->activity_enpoint_domain);
            
            if ($response) {
                add_action('admin_notices', array($this, 'admin_import_invite'));
            } else {
                WC_Admin_Settings::add_error($this->admin_import_error_message());
                $_POST['woocommerce_metrilo-woo-analytics_api_key'] = '';
                $_POST['woocommerce_metrilo-woo-analytics_api_secret'] = '';
            }
        }
        
        public function ensure_hooks()
        {
            // general tracking snipper hook
            add_filter('wp_head', array($this, 'render_snippet'));
            add_filter('wp_head', array($this, 'woocommerce_tracking'));
            add_filter('wp_footer', array($this, 'render_footer_events'));
            
            // background events tracking
            add_action('woocommerce_add_to_cart', array($this, 'add_to_cart'), 10, 6);
            add_action('woocommerce_remove_cart_item', array($this, 'remove_from_cart'), 10);
            
            // hook on new order placed
            add_action('woocommerce_checkout_order_processed', array($this, 'new_order_event'), 10);
            
            add_action('wp_ajax_metrilo_import', array($this, 'metrilo_import'));
            add_action('admin_menu', array($this, 'setup_admin_pages'));
        }
        
        public function ensure_identify(){
            if(is_admin() && !is_user_logged_in() && $this->session_get($this->get_identify_cookie_name())){
                return;
            }
            
            $user       = wp_get_current_user();
            $user_roles = $user->roles;
            $this->identify_call_data = $user->user_email;
            
            // check if roles should be sent and if they exist
            if($this->send_roles_as_tags == 'yes' && !empty($user_roles)){
                $this->user_tags = $user_roles;
                $this->session_set($this->get_identify_cookie_name(), $user_roles);
            }
            $this->session_set($this->get_identify_cookie_name(), 'true');
        }
        
        public function render_identify()
        {
            include_once(METRILO_ANALYTICS_PLUGIN_PATH . 'views/render-identify.php');
        }
        
        public function render_events()
        {
            include_once(METRILO_ANALYTICS_PLUGIN_PATH . 'views/render-tracking-events.php');
        }
        
        public function render_footer_events(){
            include_once(METRILO_ANALYTICS_PLUGIN_PATH.'views/render-footer-tracking-events.php');
        }
        
        public function woocommerce_footer_tracking(){
            if($this->events_queue) {
                $this->render_footer_events();
            }
        }
        
        public function render_snippet()
        {
            // check if we should track data for this user (if user is available)
            if (!is_admin() && is_user_logged_in()) {
                $user = wp_get_current_user();
                if ($user->roles && $this->ignore_for_roles) {
                    foreach ($user->roles as $r) {
                        if (in_array($r, $this->ignore_for_roles)) {
                            $this->accept_tracking = false;
                        }
                    }
                }
            }
            
            // render the JS tracking code
            include_once(METRILO_ANALYTICS_PLUGIN_PATH . 'views/metrilo-front-end-library.php');
        }
        
        public function woocommerce_tracking()
        {
            include_once(METRILO_ANALYTICS_PLUGIN_PATH . 'includes/tracking-events.php');
        }
        
        public function add_to_cart(
            $cart_item_key,
            $product_id,
            $quantity,
            $variation_id = false,
            $variation = false,
            $cart_item_data = false
        )
        {
            $product_id = $variation_id ? $variation_id : $product_id;
            $this->put_event_in_cookie_queue("window.metrilo.addToCart('$product_id', '$quantity');");
        }
        
        public function remove_from_cart($key_id)
        {
            if (!is_object($this->woo->cart)) {
                return true;
            }
            $cart_items        = $this->woo->cart->get_cart();
            $removed_cart_item = $cart_items[$key_id];
            if ($removed_cart_item) {
                $product_id = $removed_cart_item['data']->get_id();
                $quantity   = (int)$removed_cart_item['quantity'];
                $this->put_event_in_cookie_queue("window.metrilo.removeFromCart('$product_id', $quantity);");
            }
        }
        
        public function new_order_event($order_id) {
            try {
                // fetch the order
                $order                    = new WC_Order($order_id);
                $serialized_order         = $this->order_serializer->serialize($order);
                $client                   = $this->api_client->get_client($this->tracking_endpoint_domain);
                $this->identify_call_data = $order->get_billing_email();
                
                $client->order($serialized_order);
                $this->session_set(
                    $this->get_do_identify_cookie_name(),
                    wp_json_encode($this->identify_call_data, JSON_UNESCAPED_UNICODE)
                );
            } catch(\Exception $e) {
                $this->error_logger('new_order_event ', $e->getMessage(), $this->plugin_log_path);
            }
        }
        
        public function resolve_product($product_id)
        {
            if (function_exists('wc_get_product')) {
                return wc_get_product($product_id);
            }
        }
        
        public function put_event_in_queue($event)
        {
            if (!$this->check_if_event_should_be_ignored($event)) {
                array_push($this->events_queue, $event);
            }
        }
        
        public function put_event_in_cookie_queue($event)
        {
            if (!$this->check_if_event_should_be_ignored($event)) {
                $this->add_item_to_cookie($event);
            }
        }
        
        public function check_if_event_should_be_ignored($event)
        {
            return in_array($event, $this->ignore_for_events);
        }
        
        /**
         *
         *
         * Session and cookie handling
         *
         *
         */
        
        public function session_get($k)
        {
            if (!is_object($this->woo->session)) {
                return isset($_COOKIE[$k]) ? $_COOKIE[$k] : false;
            }
            return $this->woo->session->get($k);
        }
        
        public function session_set($k, $v)
        {
            if (!is_object($this->woo->session)) {
                @setcookie($k, $v, time() + 43200, COOKIEPATH, COOKIE_DOMAIN);
                $_COOKIE[$k] = $v;
            } else {
                $this->woo->session->set($k, $v);
            }
        }
        
        public function add_item_to_cookie($data)
        {
            $items = $this->get_items_in_cookie();
            if (empty($items)) $items = [];
            array_push($items, $data);
            $encoded_items = json_encode($items);
            $this->session_set($this->get_cookie_name(), $encoded_items);
        }
        
        public function get_items_in_cookie()
        {
            $items = array();
            $data = $this->session_get($this->get_cookie_name());
            if (!empty($data)) {
                $items = json_decode($data, true);
            }
            
            return $items;
        }
        
        public function get_identify_data_in_cookie()
        {
            $identify = [];
            $data = $this->session_get($this->get_do_identify_cookie_name());
            if (!empty($data)) {
                $identify = json_decode($data, true);
            }
            return $identify;
        }
        
        public function clear_items_in_cookie()
        {
            $this->session_set($this->get_cookie_name(), json_encode([]));
            $this->session_set($this->get_do_identify_cookie_name(), json_encode([]));
        }
        
        public function get_order_ip($order_id)
        {
            $ip_address = get_post_meta($order_id, '_customer_ip_address', true);
            if (strpos($ip_address, '.') !== false) {
                return $ip_address;
            }
            return false;
        }
        
        public function process_cookie_events()
        {
            $items = $this->get_items_in_cookie();
            if (count($items) > 0) {
                $this->has_events_in_cookie = true;
                foreach ($items as $event) {
                    // put event in queue for sending to the JS library
                    $this->put_event_in_queue($event);
                }
                
            }
            
            // check if identify data resides in the session
            $identify_data = $this->get_identify_data_in_cookie();
            if(!empty($identify_data)) {
                $this->identify_call_data = $identify_data;
            }
        }
        
        public function check_for_metrilo_clear()
        {
            if (!empty($_REQUEST) && !empty($_REQUEST['metrilo_clear'])) {
                $this->clear_items_in_cookie();
                wp_send_json_success();
            }
        }
        
        public function setup_admin_pages()
        {
            add_submenu_page('tools.php', 'Export to Metrilo', 'Export to Metrilo', 'export', 'metrilo-import', array($this, 'metrilo_import_page'));
        }
        
        public function metrilo_import_page()
        {
            wp_enqueue_script('jquery');
            $metrilo_import = include_once(METRILO_ANALYTICS_PLUGIN_PATH . 'class.metrilo-import.php');
            if (!empty($_GET['import'])) {
                $metrilo_import->set_importing_mode(true);
            }
            $metrilo_import->output();
        }
        
        public function metrilo_import()
        {
            $result = false;
            
            try {
                $chunk_id = (int)$_REQUEST['chunkId'];
                $import_type = (string)$_REQUEST['importType'];
                $client = $this->api_client->get_client($this->tracking_endpoint_domain);
                
                switch ($import_type) {
                    case 'customers':
                        if ($chunk_id == 0) {
                            $this->activity_helper->create_activity('import_start', $this->activity_enpoint_domain);
                        }
                        $serialized_customers = $this->serialize_import_records($this->customer_data->get_customers($chunk_id), $this->customer_serializer);
                        $result               = $client->customerBatch($serialized_customers);
                        break;
                    case 'categories':
                        $serialized_categories = $this->serialize_import_records($this->category_data->get_categories($chunk_id), $this->category_serializer);
                        $result                = $client->categoryBatch($serialized_categories);
                        break;
                    case 'deletedProducts':
                        $serialized_deleted_products = $this->deleted_product_data->get_deleted_products();
                        $deleted_product_chunks      = array_chunk($serialized_deleted_products, $this->import_chunk_size);
                        foreach ($deleted_product_chunks as $chunk) {
                            $client->productBatch($chunk);
                        }
                        break;
                    case 'products':
                        $serialized_products = $this->serialize_import_records($this->product_data->get_products($chunk_id), $this->product_serializer);
                        $result              = $client->productBatch($serialized_products);
                        break;
                    case 'orders':
                        $serialized_orders = $this->serialize_import_records($this->order_data->get_orders($chunk_id), $this->order_serializer);
                        $result            = $client->orderBatch($serialized_orders);
                        if ($chunk_id == (int)$_REQUEST['ordersChunks'] - 1) {
                            $this->activity_helper->create_activity('import_end', $this->activity_enpoint_domain);
                        }
                        break;
                    default:
                        $result = false;
                        break;
                }
                return json_encode($result);
            } catch (\Exception $e) {
                $this->error_logger('metrilo_import ', $e->getMessage(), $this->plugin_log_path);
            }
        }
        
        public function admin_keys_notice()
        {
            $message = 'Almost done! Just enter your Metrilo API key and secret';
            echo '<div class="updated"><p>' . $message . ' <a href="' . admin_url('admin.php?page=wc-settings&tab=integration') . '">here</a></p></div>';
        }
        
        public function admin_import_invite()
        {
            echo '<div class="updated"><p>Awesome! Have you tried <a href="' . admin_url('tools.php?page=metrilo-import') . '"><strong>importing your existing data to Metrilo</strong></a>?</p></div>';
        }
        
        public function admin_import_error_message()
        {
            return 'The API Token and/or API Secret you have entered are invalid. You can find the correct ones in Settings -> Installation in your Metrilo account.';
        }
        
        /**
         * Initialize integration settings form fields.
         */
        public function init_form_fields()
        {
            include_once(METRILO_ANALYTICS_PLUGIN_PATH . 'includes/form-fields.php');
        }
        
        private function get_cookie_name()
        {
            return 'metriloqueue_' . COOKIEHASH;
        }
        
        private function get_identify_cookie_name()
        {
            return 'metriloid_' . COOKIEHASH;
        }
        
        private function get_do_identify_cookie_name()
        {
            return 'metrilodoid_' . COOKIEHASH;
        }
        
        private function initialize_metrilo_library()
        {
            return $this->tracking_endpoint_domain . '/tracking.js?token=' . $this->api_token;
        }
        
        private function serialize_import_records($records, $serializer)
        {
            $serializedData = [];
            foreach ($records as $record) {
                $serializedRecord = $serializer->serialize($record);
                if ($serializedRecord) {
                    $serializedData[] = $serializedRecord;
                }
            }
            
            return $serializedData;
        }
        
        private function error_logger($import_type, $serialized_data, $plugin_log_path)
        {
            return error_log(
                json_encode([$import_type => $serialized_data]) . PHP_EOL,
                3,
                $plugin_log_path
            );
        }
    }
}
