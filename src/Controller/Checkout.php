<?php
namespace Woocommerce\InfinitePay\Controller;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\InfinitePay\InfinitePayCore;
use Woocommerce\InfinitePay\Controller\Setting;
use Woocommerce\InfinitePay\Controller\Log;
use Woocommerce\InfinitePay\Helper\Utils;

// WooCommerce
use \WC_Order;
//use WC_Payment_Gateway;

class Checkout extends \WC_Payment_Gateway
{

	public $order;
	public $api;

	public function __construct($_order) {

		$this->order = $_order;
	}

	public function process_credit_card() {


		$log_header = '[' . $order->get_id() . '] ';

		$token        = sanitize_text_field($_POST['infinitepay_custom']['token']);
		$uuid         = sanitize_key($_POST['infinitepay_custom']['uuid']);
		$installments = sanitize_text_field($_POST['infinitepay_custom']['installments']);
		$doc_number   = sanitize_text_field($_POST['infinitepay_custom']['doc_number']);
		$cvv          = sanitize_text_field($_POST['infinitepay_custom']['cvv']);
		$nsu          = Util::generate_uuid();
		$this->core_settings->log->write_log(__FUNCTION__, $log_header . 'Starting IP payment for nsu ' . $nsu);

		$order_items = [];
		if (count($order->get_items()) > 0) {
			foreach ($order->get_items() as $item) {
				$order_items[] = array(
					'id'          => (string) sanitize_key($item->get_id()),
					'description' => sanitize_text_field($item->get_name()),
					'amount'      => (int) sanitize_text_field($item->get_data()['total'] * 100),
					'quantity'    => (int) sanitize_key($item->get_quantity()),
				);
			}
		}

		$installments_val = $order->get_total();
		if ($this->calculate_installments()[$installments - 1]['interest']) {
			$installments_val = (int) sanitize_text_field($installments) * round($this->calculate_installments()[$installments - 1]['value'], 2, PHP_ROUND_HALF_UP);
		}

		$order_value = $installments == 1 ? $order->get_total() * 100 : $installments_val * 100;
		$final_value = (int) explode('.', $order_value)[0];

		$body = array(
			'payment'         => array(
				'amount'         => $final_value,
				'installments'   => (int) sanitize_text_field($installments),
				'capture_method' => 'ecommerce',
				'origin'         => 'woocommerce',
				'payment_method' => 'credit',
				'nsu'            => $nsu,
			),
			'card'            => array(
				'cvv'              => $cvv,
				'token'            => $token,
				'card_holder_name' => sanitize_text_field($order->get_billing_first_name()) . ' ' . sanitize_text_field($order->get_billing_last_name()),
			),
			'order'           => array(
				'id'               => (string) $order->get_id(),
				'amount'           => $final_value,
				'items'            => $order_items,
				'delivery_details' => array(
					'email'        => sanitize_text_field($order->get_billing_email()),
					'name'         => sanitize_text_field($order->get_shipping_first_name() ?: $order->get_billing_first_name()) . ' ' . sanitize_text_field($order->get_shipping_last_name() ?: $order->get_billing_last_name()),
					'phone_number' => sanitize_text_field($order->get_shipping_phone()) ?: sanitize_text_field($order->get_billing_phone()),
					'address'      => array(
						'line1'   => sanitize_text_field($order->get_billing_address_1()),
						'line2'   => sanitize_text_field($order->get_billing_address_2()),
						'city'    => sanitize_text_field($order->get_billing_city()),
						'state'   => sanitize_text_field($order->get_billing_state()),
						'zip'     => sanitize_text_field($order->get_billing_postcode()),
						'country' => sanitize_text_field($order->get_billing_country()),
					),
				),
			),
			'customer'        => array(
				'document_number' => $doc_number,
				'email'           => sanitize_email($order->get_billing_email()),
				'first_name'      => sanitize_text_field($order->get_shipping_first_name() ?: $order->get_billing_first_name()),
				'last_name'       => sanitize_text_field($order->get_shipping_last_name() ?: $order->get_billing_last_name()),
				'phone_number'    => sanitize_text_field($order->get_billing_phone()),
				'address'         => sanitize_text_field($order->get_shipping_address_1() ?: $order->get_billing_address_1()),
				'complement'      => sanitize_text_field($order->get_shipping_address_2() ?: $order->get_billing_address_2()),
				'city'            => sanitize_text_field($order->get_shipping_city() ?: $order->get_billing_city()),
				'state'           => sanitize_text_field($order->get_shipping_state() ?: $order->get_billing_state()),
				'zip'             => sanitize_text_field($order->get_shipping_postcode() ?: $order->get_billing_postcode()),
				'country'         => sanitize_text_field($order->get_shipping_country() ?: $order->get_billing_country()),
			),
			'billing_details' => array(
				'address' => array(
					'line1'   => sanitize_text_field($order->get_billing_address_1()),
					'line2'   => sanitize_text_field($order->get_billing_address_2()),
					'city'    => sanitize_text_field($order->get_billing_city()),
					'state'   => sanitize_text_field($order->get_billing_state()),
					'zip'     => sanitize_key($order->get_billing_postcode()),
					'country' => sanitize_text_field($order->get_billing_country()),
				),
			),
			'metadata'        => array(
				'origin'         => 'woocommerce',
				'plugin_version' => self::VERSION,
				'store_url'      => $_SERVER['SERVER_NAME'],
				'risk'           => array(
					'session_id' => $uuid,
					'payer_ip'   => isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']),
				),
			),
		);
	

		$response = $this->api->transactions($body);

		$this->log->write_log(__FUNCTION__, $log_header . 'API response code: ' . $response['response']['code']);
		$body = json_decode($response['body'], true);
		if (!is_wp_error($response) && $response['response']['code'] < 500) {
			$this->log->write_log(__FUNCTION__, $log_header . 'API response authorization_code: ' . $body['data']['attributes']['authorization_code']);
			if ($body['data']['attributes']['authorization_code'] === '00') {

				if ($installments != 1) {
					$order->set_total($installments_val);
					$order->save();
				}

				$order->payment_complete();

				$order->add_order_note('
					' . __('Installments', 'infinitepay-woocommerce') . ': ' . $installments . '
					' . __('Final amount', 'infinitepay-woocommerce') . ': R$ ' . number_format($order->get_total(), 2, ",", ".") . '
					' . __('NSU', 'infinitepay-woocommerce') . ': ' . $body['data']['id']
				);

				WC()->cart->empty_cart();

				$this->log->write_log(__FUNCTION__, $log_header . 'Finished IP payment for nsu ' . $nsu . ' successfully');

				return array(
					'result'   => 'success',
					'redirect' => $order->get_checkout_order_received_url(),
				);
			} else {
				$code = '';
				if ($body['data'] && $body['data']['attributes'] && $body['data']['attributes']['authorization_code']) {
					$code = $body['data']['attributes']['authorization_code'];
				} else {
					$code = $response['response']['code'];
				}
				$this->log->write_log(__FUNCTION__, $log_header . 'Error ' . $code . ' on IP payment for nsu ' . $nsu . ', error log: ' . json_encode($body));
				wc_add_notice(__('Please review your card information and try again', 'infinitepay-woocommerce') . ' - ' . $code, 'error');
				if (isset($this->sandbox) && $this->sandbox === 'yes') {
					wc_add_notice(json_encode($body), 'error');
				}
			}
		} else {
			$this->log->write_log(__FUNCTION__, $log_header . 'Error 500 on IP payment for nsu ' . $nsu . ', error log: ' . json_encode($body));
			wc_add_notice(__('Ooops, an internal error has occurred, contact an administrator!', 'infinitepay-woocommerce'), 'error');
		}
		
	}
}