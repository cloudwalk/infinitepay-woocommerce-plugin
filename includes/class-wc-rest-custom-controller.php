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
	protected $pix_callback_endpoint = 'infinitepay_pix_callback';
	protected $order_status_endpoint = 'infinitepay_order_status';

	// Callback handler
	public function infinite_pay_callback(WP_REST_Request $data) {
		global $woocommerce;

		// Retrieve parameters
		$orderId = $data['order_id'];
		$safetyHash = $data->get_header('X-Callback-Signature');

		// Retrieve order
		$order = wc_get_order($orderId);
		$transactionSecrets = get_post_meta($orderId, 'transactionSecret');
		//$nsu = get_post_meta($orderId, 'nsuHost');
		$body = json_encode($data->get_json_params(), JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE);
		$transactionSignature = hash_hmac('sha256', $body, $transactionSecrets[0]);

		// Validate if the request is valid
		if ($transactionSignature != $safetyHash) {
			// Return bad request
			return array(
				'status' => 400,
				//'transactionId' => $nsu[0],
				'signature' => $safetyHash,
				'generatedHash' => $transactionSignature
			);
		}

		// Update order status to payment received
		$paymentReceivedStatus = 'processing';
		$order->update_status($paymentReceivedStatus);

		// Returning
		return array(
			'status' => 200,
			'message' => 'Transaction successfully validated'
		);
	}

	// Validate order status
	public function get_order_status(WP_REST_Request $data) {
		global $woocommerce;

		// Retrieve parameters
		$orderId = $data['order_id'];

		// Retrieve order
		$order = wc_get_order($orderId);

		// Returning
		return array(
			'status' => 200,
			'order_status' => $order->get_status()
		);
	}
	

	// API Router
	public function register_routes() {
		// Register webhook route
		register_rest_route(
			$this->namespace,
			'/' . $this->pix_callback_endpoint,
			array(
				'methods' => 'POST',
				'callback' => array($this, 'infinite_pay_callback')
			)
		);
		// Register order status route
		register_rest_route(
			$this->namespace,
			'/' . $this->order_status_endpoint,
			array(
				'methods' => 'GET',
				'callback' => array($this, 'get_order_status')
			)
		);
	}
}
