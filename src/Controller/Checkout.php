<?php
namespace Woocommerce\InfinitePay\Controller;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\InfinitePay\Helper\ApiInfinitePay;
use Woocommerce\InfinitePay\Helper\Log;
use Woocommerce\InfinitePay\Helper\Utils;
use Woocommerce\InfinitePay\Helper\Constants;

use \WC_Order;

class Checkout extends \WC_Payment_Gateway
{

	public $storeUrl;
	public $order;
	public $api;

	public function __construct($_order) {
		$this->order	= $_order;
		$this->log 		= new Log();
		$this->api 		= new ApiInfinitePay();
	}

	public function process_credit_card() {


		$log_header = '[' . $this->order->get_id() . '] ';

		$post = filter_input( INPUT_POST, 'infinitepay_custom',  FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
	
		$token        = sanitize_text_field($post['token']);
		$uuid         = sanitize_key($post['uuid']);
		$installments = sanitize_text_field($post['installments']);
		$doc_number   = sanitize_text_field($post['doc_number']);
		$cvv          = sanitize_text_field($post['cvv']);
		$nsu          = Utils::generate_uuid();
		$this->log->write_log(__FUNCTION__, $log_header . 'Starting IP payment for nsu ' . $nsu);

		$order_items = [];
		if (count($this->order->get_items()) > 0) {
			foreach ($this->order->get_items() as $item) {
				$product    = $item->get_product();
				$order_items[] = array(
					'id'          => (string) sanitize_key($item->get_id()),
					'description' => sanitize_text_field($item->get_name()),
					'amount'      => (int) $product->get_price() * 100,
					'quantity'    => (int) sanitize_key($item->get_quantity()),
				);
			}
		}

		$installments_val = $this->order->get_total();
		if (Utils::calculate_installments($this->order->get_total())[$installments - 1]['interest']) {
			$installments_val = (int) sanitize_text_field($installments) * round(Utils::calculate_installments($this->order->get_total())[$installments - 1]['value'], 2, PHP_ROUND_HALF_UP);
		}

		$order_value = $installments == 1 ? $this->order->get_total() * 100 : $installments_val * 100;
		$final_value = (int) explode('.', $order_value)[0];

		$body = array(
			'origin'         => 'ecommerce',
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
				'card_holder_name' => sanitize_text_field($this->order->get_billing_first_name()) . ' ' . sanitize_text_field($this->order->get_billing_last_name()),
			),
			'order'           => array(
				'id'               => (string) $this->order->get_id(),
				'amount'           => $final_value,
				'items'            => $order_items,
				'delivery_details' => array(
					'email'        => sanitize_text_field($this->order->get_billing_email()),
					'name'         => sanitize_text_field($this->order->get_shipping_first_name() ?: $this->order->get_billing_first_name()) . ' ' . sanitize_text_field($this->order->get_shipping_last_name() ?: $this->order->get_billing_last_name()),
					'phone_number' => sanitize_text_field($this->order->get_shipping_phone()) ?: sanitize_text_field($this->order->get_billing_phone()),
					'address'      => array(
						'line1'   => sanitize_text_field($this->order->get_billing_address_1()),
						'line2'   => sanitize_text_field($this->order->get_billing_address_2()),
						'city'    => sanitize_text_field($this->order->get_billing_city()),
						'state'   => sanitize_text_field($this->order->get_billing_state()),
						'zip'     => sanitize_text_field($this->order->get_billing_postcode()),
						'country' => sanitize_text_field($this->order->get_billing_country()),
					),
				),
			),
			'customer'        => array(
				'document_number' => $doc_number,
				'email'           => sanitize_email($this->order->get_billing_email()),
				'first_name'      => sanitize_text_field($this->order->get_shipping_first_name() ?: $this->order->get_billing_first_name()),
				'last_name'       => sanitize_text_field($this->order->get_shipping_last_name() ?: $this->order->get_billing_last_name()),
				'phone_number'    => sanitize_text_field($this->order->get_billing_phone()),
				'address'         => sanitize_text_field($this->order->get_shipping_address_1() ?: $this->order->get_billing_address_1()),
				'complement'      => sanitize_text_field($this->order->get_shipping_address_2() ?: $this->order->get_billing_address_2()),
				'city'            => sanitize_text_field($this->order->get_shipping_city() ?: $this->order->get_billing_city()),
				'state'           => sanitize_text_field($this->order->get_shipping_state() ?: $this->order->get_billing_state()),
				'zip'             => sanitize_text_field($this->order->get_shipping_postcode() ?: $this->order->get_billing_postcode()),
				'country'         => sanitize_text_field($this->order->get_shipping_country() ?: $this->order->get_billing_country()),
			),
			'billing_details' => array(
				'address' => array(
					'line1'   => sanitize_text_field($this->order->get_billing_address_1()),
					'line2'   => sanitize_text_field($this->order->get_billing_address_2()),
					'city'    => sanitize_text_field($this->order->get_billing_city()),
					'state'   => sanitize_text_field($this->order->get_billing_state()),
					'zip'     => sanitize_key($this->order->get_billing_postcode()),
					'country' => sanitize_text_field($this->order->get_billing_country()),
				),
			),
			'metadata'        => array(
				'origin'         => 'woocommerce',
				'platform'       => 'woocommerce',
				'plugin_version' => Constants::VERSION,
				'store_url'      => filter_input(INPUT_SERVER, 'SERVER_NAME'),
				'wordpress_version' => get_bloginfo('version'),
				'woocommerce_version' => WC_VERSION,
				'payment_method' => 'credit',
				'risk'           => array(
					'session_id' => $uuid,
					'payer_ip'   => $this->payer_ip(),
				),
			),
		);

		$response = $this->api->transactions( $body, 'credit');
	
		if (!is_wp_error($response) && $response['response']['code'] < 500) {
			
			$body = json_decode($response['body'], true);
			$this->log->write_log(__FUNCTION__, $log_header . 'API response code: ' . $response['response']['code']);
			$this->log->write_log(__FUNCTION__, $log_header . 'API response authorization_code: ' . $body['data']['attributes']['authorization_code']);
			if ($body['data']['attributes']['authorization_code'] === '00') {

				if ($installments != 1) {
					$this->order->set_total($installments_val);
					$this->order->save();
				}

				$this->order->payment_complete();

				$this->order->add_order_note('
					' . __('Installments', 'infinitepay-woocommerce') . ': ' . $installments . '
					' . __('Final amount', 'infinitepay-woocommerce') . ': R$ ' . number_format($this->order->get_total(), 2, ",", ".") . '
					' . __('NSU', 'infinitepay-woocommerce') . ': ' . $body['data']['id']
				);

				if($this->order->get_meta('payment_method')) {
					update_post_meta( $this->order->get_id(), 'payment_method', 'credit' );
				} else {
					add_post_meta( $this->order->get_id(), 'payment_method', 'credit' );
				}

				WC()->cart->empty_cart();

				$status = (Utils::getConfig('status_aproved') !== null) ? Utils::getConfig('status_aproved') : 'processing';
				$this->order->update_status( $status );

				$this->log->write_log(__FUNCTION__, $log_header . 'Finished IP payment for nsu ' . $nsu . ' successfully');

				return true;

			} else {
				$code = '';
				if ($body['data'] && $body['data']['attributes'] && $body['data']['attributes']['authorization_code']) {
					$code = $body['data']['attributes']['authorization_code'];
				} else {
					$code = $response['response']['code'];
				}
				$this->log->write_log(__FUNCTION__, $log_header . 'Error ' . $code . ' on IP payment for nsu ' . $nsu . ' ' . json_encode($response));
				
				$error = Utils::getErrorByCode($code);
				$this->log->write_log(__FUNCTION__, $error);
				
				wc_add_notice(__($error['content'], 'infinitepay-woocommerce') . ' - ' . $code, 'error');
				
				if (isset($this->sandbox) && $this->sandbox === 'yes') {
					wc_add_notice(json_encode($body), 'error');
				}
			}
		} else {
			$this->log->write_log(__FUNCTION__, $log_header . 'Error 500 on IP payment for nsu ' . $nsu . ', error log: ' . json_encode($response));
			wc_add_notice(__('Ooops, an internal error has occurred, contact an administrator!', 'infinitepay-woocommerce'), 'error');
		}
		return false;
	}

	public function process_pix() {
		try {
			// Retrieve order items
			$log_header = '[' . $this->order->get_id() . '] ';
			$this->log->write_log( __FUNCTION__, $log_header . 'Starting IP PIX payment' );
			
			$nsu          = Utils::generate_uuid();

			$order_items = [];
			if ( count( $this->order->get_items() ) > 0 ) {
				foreach ( $this->order->get_items() as $item ) {
					$product    = $item->get_product();
					$order_items[] = array(
						'id'          => (string) sanitize_key( $item->get_id() ),
						'description' => sanitize_text_field( $item->get_name() ),
						'amount'      => (int) $product->get_price() * 100,
						'quantity'    => (int) sanitize_key( $item->get_quantity() )
					);
				}
			}

			// Generate unique uuid for transaction secret
			$transactionSecret = sha1( $this->order->get_id() . random_bytes(10) );

			$options = get_option('woocommerce_infinitepay_settings');

			// Apply discount if it has one
			$orderTotalWithDiscount = $this->order->get_total();
			$discount_pix  = (isset($options['discount_pix']) ? (float) $options['discount_pix'] : 0);
			$min_value_pix = isset($options['min_value_pix']) ? (float) $options['min_value_pix'] : 0;
			
			if ( $discount_pix && $orderTotalWithDiscount >= $min_value_pix ) {
				$discountValue          = ( $orderTotalWithDiscount * $discount_pix ) / 100;
				$orderTotalWithDiscount = $orderTotalWithDiscount - $discountValue;
				$this->order->set_discount_total( $discountValue );
				$this->order->set_total( $orderTotalWithDiscount );
				$this->order->save();
			}

			$order_value = $orderTotalWithDiscount * 100;
			$final_value = (int) explode( '.', $order_value )[0];

			if( $final_value < 100 ) {
				$this->log->write_log(__FUNCTION__, $log_header . ' Could not process pix payment, value < R$ 1,00');
                wc_add_notice(__( 'The minimum amount for the PIX is BRL 1.00', 'infinitepay-woocommerce'), 'error');
				return false;
			}

			// Prepare transaction request
			$body = array(
				'amount'         => $final_value,
				'capture_method' => 'pix',
				'payment_method' => 'pix',
				'origin'		 => 'ecommerce',
				'nsu'			 => $nsu,
				'metadata'       => array(
					'origin'         => 'woocommerce',
					'platform'       => 'woocommerce',
					'plugin_version' => Constants::VERSION,
					'wordpress_version' => get_bloginfo('version'),
					'woocommerce_version' => WC_VERSION,
					'store_url'      => filter_input(INPUT_SERVER, 'SERVER_NAME'),
					'payment_method' => 'pix',
					'callback' => array(
						'validate' => '',
						'confirm'  => Utils::getStoreUrl() . '/wp-json/wc/v3/infinitepay_pix_callback?order_id=' . $this->order->get_id(),
						'secret'   => $transactionSecret
					)
				)
			);

			$response = $this->api->transactions( $body,  'pix' );

			// Check transaction create response
			if ( ! is_wp_error( $response ) && $response['response']['code'] < 500 ) {
				$body = json_decode( $response['body'], true );
				$this->log->write_log( __FUNCTION__, $log_header . 'API response code: ' . $response['response']['code'] );
				$this->log->write_log( __FUNCTION__, $log_header . 'API response authorization_code: ' . $body['data']['attributes']['authorization_code'] );
				
				//* Validates if pix qrcode was successfully generated
				if ( $body['data']['attributes']['br_code'] ) {

					// Retrieve infinite pay response fields
					$pixBrCode  = $body['data']['attributes']['br_code'];
					$pixNsuHost = $body['data']['attributes']['nsu_host'];

					// Add transaction secret to order
					add_post_meta( $this->order->get_id(), 'transactionSecret', $transactionSecret );
					add_post_meta( $this->order->get_id(), 'nsuHost', $pixNsuHost );

					if($this->order->get_meta('payment_method')) {
						update_post_meta( $this->order->get_id(), 'payment_method', 'pix' );
					} else {
						add_post_meta( $this->order->get_id(), 'payment_method', 'pix' );
					}

					// Add br code to order object
					$this->order->add_order_note( '
						' . __( 'br_code', 'infinitepay-woocommerce' ) . ': ' . $pixBrCode . '
					' );

					// Clear user cart
					WC()->cart->empty_cart();

					$this->log->write_log( __FUNCTION__, $log_header . 'Finished IP PIX payment successfully' );

					// Return that your transaction was successfully created
					return true;

					//! PIX Qrcode generation failed
				} else {
					$code = '';
					if ( $body['data'] && $body['data']['attributes'] && $body['data']['attributes']['authorization_code'] ) {
						$code = $body['data']['attributes']['authorization_code'];
					} else {
						$code = $response['response']['code'];
					}
					
					$this->log->write_log( __FUNCTION__, $log_header . 'Error ' . $code . ' on IP PIX payment, error log: ' . json_encode( $response ) );
					
					wc_add_notice( __( 'Ooops, an internal error has occurred, wait bit and try again!', 'infinitepay-woocommerce' ) . ' - ' . $code, 'error' );
					if ( isset( $this->sandbox ) && $this->sandbox === 'yes' ) {
						wc_add_notice( json_encode( $body ), 'error' );
					}
				}
			} else {
				$this->log->write_log( __FUNCTION__, $log_header . 'Error 500 on IP payment, error log: ' . json_encode( $response ) );
				wc_add_notice( __( 'Ooops, an internal error has occurred, contact an administrator!', 'infinitepay-woocommerce' ), 'error' );
			}
		} catch ( Exception $ex ) {
			$this->log->write_log( __FUNCTION__, 'Caught exception: ' . $ex->getMessage() );
		}
		return false;
	}
	
	protected function payer_ip() {

		$http_client_ip = filter_input( INPUT_SERVER, 'HTTP_CLIENT_IP' );
		$http_x_foward = filter_input( INPUT_SERVER, 'HTTP_X_FORWARDED_FOR' );
		$remote_addr = filter_input( INPUT_SERVER, 'REMOTE_ADDR' );

		return isset( $http_client_ip ) ? $http_client_ip : ( isset($http_x_foward) ? $http_x_foward : $remote_addr );
	}
}