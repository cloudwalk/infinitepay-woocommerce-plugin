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

	// Callback handler
	public function infinite_pay_callback(WP_REST_Request $data) {
		global $woocommerce;

		// Retrieve parameters
		$orderId = $data['order_id'];
		$safetyHash = $data->get_header('X-Callback-Signature');

		// Retrieve order
		$order = wc_get_order($orderId);
		$transactionIds = get_post_meta($orderId, 'transactionSecret');
		$body = json_encode($data->get_json_params());
		$convertedTransactionId = hash_hmac('sha256', $body, $transactionIds[0], false);

		// Validate if the request is valid
		if ($convertedTransactionId != $safetyHash) {
			// Return bad request
			return array(
				'status' => 400
			);
		}

		// Update order status to payment received
		$paymentReceivedStatus = 'processing';
		$order->update_status($paymentReceivedStatus);

		$nsu = get_post_meta($orderId, 'nsuHost');

		// Returning
		return array(
			'status' => 200,
			'nsu' => $nsu,
			'orderId' => $orderId,
			'safety' => $safetyHash,
			'body' => $body,
			'transactionId' => $transactionIds[0],
			'convertedTransactionId' => $convertedTransactionId
		);
	}

	// API Router
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->pix_callback_endpoint,
			array(
				'methods' => 'POST',
				'callback' => array($this, 'infinite_pay_callback')
			)
		);
	}
}