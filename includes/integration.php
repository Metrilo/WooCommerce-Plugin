<?php

 
if ( ! class_exists( 'Metrilo_Woo_Analytics_Integration' ) ) :
 
class Metrilo_Woo_Analytics_Integration extends WC_Integration {
 
	/**
	 * Init and hook in the integration.
	 */
	public function __construct() {
		global $woocommerce;
 
		$this->id                 = 'metrilo-woo-analytics';
		$this->method_title       = __( 'Metrilo', 'metrilo-woo-analytics' );
		$this->method_description = __( 'Metrilo offers powerful yet simple Analytics for WooCommerce Stores. Enter your API key to active analytics tracking.', 'metrilo-woo-analytics' );
 
		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();
 
		// Define user set variables.
		$this->api_key          = $this->get_option( 'api_key' );
 
		// Actions.
		add_action( 'woocommerce_update_options_integration_' .  $this->id, array( $this, 'process_admin_options' ) );
 
	}
 
	/**
	 * Initialize integration settings form fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'api_key' => array(
				'title'             => __( 'API Key', 'metrilo-woo-analytics' ),
				'type'              => 'text',
				'description'       => __( 'Enter your Metrilo API key. You can find it in your "Settings" menu in your Metrilo account at metrilo.com', 'metrilo-woo-analytics' ),
				'desc_tip'          => true,
				'default'           => ''
			)
		);
	}
 
}
 
endif;