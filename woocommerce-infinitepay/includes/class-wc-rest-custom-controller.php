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
	public function infinite_pay_callback($data) {
		global $woocommerce;

		// Retrieve parameters
		$orderId = $data['order_id'];

		// Retrieve order
		$order = wc_get_order($orderId);
		$transactionIds = get_post_meta($orderId, 'transactionSecret');
		$convertedTransactionId = hash('sha256', $transactionIds[0]);

		// Validate if the request is valid

		// Update order status to payment received
		$paymentReceivedStatus = 'processing';
		$order->update_status($paymentReceivedStatus);

		// Returning
		return array(
			'orderId' => $orderId,
			'transactionId' => $transactionIds[0],
			'convertedTransactionId' => $convertedTransactionId,
			'postId' => $post_id,
			'order' => $order
		);
	}

	// API Router
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->pix_callback_endpoint,
			array(
				'methods' => 'POST',
				'callback' => array( $this, 'infinite_pay_callback' )
			)
		);
	}
}
