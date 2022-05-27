<?php
/*
 * Part of Woo InfinitePay Module
 * Author - InfinitePay
 * Developer
 * Copyright - Copyright(c) CloudWalk [https://www.cloudwalk.io]
 * License - https://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 *
 *  @package InfinitePay
 */
class WC_REST_Custom_Controller {
	/**
	 * You can extend this class with
	 * WP_REST_Controller / WC_REST_Controller / WC_REST_Products_V2_Controller / WC_REST_CRUD_Controller etc.
	 * Found in packages/woocommerce-rest-api/src/Controllers/
	 */
	protected $namespace = 'wc/v3';

	protected $rest_base = 'custom';

	public function get_custom( $data ) {
		return array( 'custom' => 'Data' );
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods' => 'GET',
				'callback' => array( $this, 'get_custom' ),
			)
		);
	}
}