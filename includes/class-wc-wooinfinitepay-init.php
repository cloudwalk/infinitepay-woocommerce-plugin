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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_InfinitePay_Module extends WC_Payment_Gateway {
	public $infinite_pay_tax = [
		1,
		1.3390,
		1.5041,
		1.5992,
		1.6630,
		1.7057,
		2.3454,
		2.3053,
		2.2755,
		2.2490,
		2.2306,
		2.2111
	];

	public static function load_plugin_textdomain() {
		$text_domain = 'infinitepay-woocommerce';
		$locale      = apply_filters( 'plugin_locale', get_locale(), $text_domain );

		$original_language_file = dirname( __FILE__ ) . '/../i18n/languages/infinitepay-woocommerce-' . $locale . '.mo';

		unload_textdomain( $text_domain );
		load_textdomain( $text_domain, $original_language_file );
	}

	public static function update_plugin_version() {
		$old_version = get_option( '_ip_version', '0' );
		if ( version_compare( WC_InfinitePay_Constants::VERSION, $old_version, '>' ) ) {
			update_option( '_ip_version', WC_InfinitePay_Constants::VERSION, true );
		}
	}

	public static function woocommerce_instance() {
		if ( function_exists( 'WC' ) ) {
			return WC();
		} else {
			global $woocommerce;

			return $woocommerce;
		}
	}

	public static function generate_uuid() {
		$data = openssl_random_pseudo_bytes( 16 );
		assert( strlen( $data ) == 16 );

		$data[6] = chr( ord( $data[6] ) & 0x0f | 0x40 ); // set version to 0100
		$data[8] = chr( ord( $data[8] ) & 0x3f | 0x80 ); // set bits 6-7 to 10

		return vsprintf( '%s%s-%s-%s-%s-%s%s%s', str_split( bin2hex( $data ), 4 ) );
	}

	public function __construct() {
		self::load_plugin_textdomain();

		$this->setup_properties();

		$this->init_form_fields();
		$this->init_settings();

		$this->title                 = sanitize_text_field( $this->get_option( 'title' ) );
		$this->description           = sanitize_text_field( $this->get_option( 'description' ) );
		$this->instructions          = sanitize_textarea_field( $this->get_option( 'instructions', $this->description ) );
		$this->max_installments      = sanitize_key( $this->get_option( 'max_installments', 12 ) );
		$this->max_installments_free = sanitize_key( $this->get_option( 'max_installments_free', 12 ) );
		$this->enabled               = sanitize_key( $this->get_option( 'enabled' ) );
		$this->api_key               = $this->get_option( 'api_key' );
		$this->sandbox               = sanitize_key( $this->get_option( 'sandbox', 'no' ) );
		$this->sandbox_api_key       = $this->get_option( 'sandbox_api_key' );
		$this->log                   = new WC_InfinitePay_Log( $this );
		$this->icon                  = $this->get_ip_icon();

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
			$this,
			'process_admin_options'
		) );
		add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thank_you_page' ) );
		add_filter( 'woocommerce_payment_complete_order_status', array(
			$this,
			'change_payment_complete_order_status'
		), 10, 3 );
		add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
	}

	protected function setup_properties() {
		$this->id                 = 'infinitepay';
		$this->icon               = apply_filters( 'woocommerce_offline_icon', '' );
		$this->method_title       = 'InfinitePay';
		$this->method_description = __( 'Accept credit cards payments with InfinitePay', 'infinitepay-woocommerce' );
		$this->has_fields         = true;
		$this->supports           = array(
			'products',
		);
	}

	public function init_form_fields() {
		$this->form_fields = apply_filters( 'wc_infinitepay_form_fields', array(
			'enabled'               => array(
				'title'   => __( 'Enabled/Disabled', 'infinitepay-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable payments with InfinitePay', 'infinitepay-woocommerce' ),
				'default' => 'yes',
			),
			'title'                 => array(
				'title'       => __( 'Payment Title', 'infinitepay-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Title that will be shown for the customers on your checkout page', 'infinitepay-woocommerce' ),
				'default'     => __( 'Credit Card', 'infinitepay-woocommerce' ),
				'desc_tip'    => true,
			),
			'description'           => array(
				'title'       => __( 'Description', 'infinitepay-woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'Description that will be shown for the customers on your checkout page', 'infinitepay-woocommerce' ),
				'default'     => __( '', 'infinitepay-woocommerce' ),
				'desc_tip'    => true,
			),
			'instructions'          => array(
				'title'       => __( 'Instructions', 'infinitepay-woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'Instructions that will be shown on thank you page and will be sent on email', 'infinitepay-woocommerce' ),
				'default'     => __( '', 'infinitepay-woocommerce' ),
				'desc_tip'    => true,
			),
			'max_installments'      => array(
				'title'       => __( 'Maximum number of installments', 'infinitepay-woocommerce' ),
				'type'        => 'number',
				'description' => __( 'Maximum number of installments that a customer can split the final amount', 'infinitepay-woocommerce' ),
				'default'     => '12',
				'desc_tip'    => true,
			),
			'max_installments_free' => array(
				'title'       => __( 'Maximum number of installments without interest', 'infinitepay-woocommerce' ),
				'type'        => 'number',
				'description' => __( 'Maximum number of installments that a customer can split the final amount without interest', 'infinitepay-woocommerce' ),
				'default'     => '12',
				'desc_tip'    => true,
			),
			'api_key'               => array(
				'title'       => __( 'API Key', 'infinitepay-woocommerce' ),
				'type'        => 'password',
				'description' => __( 'Key to connect with InfinitePay', 'infinitepay-woocommerce' ),
				'default'     => '',
			),
			'sandbox'               => array(
				'title'   => 'Ativo/Inativo',
				'type'    => 'checkbox',
				'label'   => 'Habilitar ambiente de sandbox',
				'default' => 'no',
			),
			'sandbox_api_key'       => array(
				'title'       => 'Chave de API do ambiente de Sandbox',
				'type'        => 'password',
				'description' => 'Chave para conexão com o ambiente de sandbox da InfinitePay',
				'default'     => '',
			),
		) );
	}

	private function calculate_installments() {
		$amount             = $this->get_order_total();
		$installments_value = [];
		for (
			$i = 1;
			$i <= (int) $this->max_installments;
			$i ++
		) {
			$tax      = ! ( (int) $this->max_installments_free >= $i ) && $i > 1;
			$interest = 1;
			if ( $tax ) {
				$interest = $this->infinite_pay_tax[ $i - 1 ] / 100;
			}
			$value                = ! $tax ? $amount / $i : $amount * ( $interest / ( 1 - pow( 1 + $interest, - $i ) ) );
			$installments_value[] = array(
				'value'    => $value,
				'interest' => $tax,
			);
		}

		return $installments_value;
	}

	public function payment_scripts() {
		if (
			(
				! is_cart()
				&& ! is_checkout()
				&& ! isset( $_GET['pay_for_order'] )
			)
			|| $this->enabled === 'no'
			|| empty( $this->api_key ?: $this->sandbox_api_key )
			|| ( (
				     ! isset( $this->sandbox ) || $this->sandbox === 'no'
			     ) && ! is_ssl()
			)
		) {
			return;
		}

		$script_path       = '/../build/index.js';
		$script_asset_path = dirname( __FILE__ ) . '/../build/index.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require( $script_asset_path )
			: array( 'dependencies' => array(), 'version' => filemtime( $script_path ) );
		$script_url        = plugins_url( $script_path, __FILE__ );

		wp_register_script(
			'woocommerce_infinitepay',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
		wp_enqueue_script( 'woocommerce_infinitepay' );
		wp_localize_script(
			'woocommerce_infinitepay',
			'wc_infinitepay_params',
			array(
				'api_key' => $this->sandbox === 'yes' ? $this->sandbox_api_key : $this->api_key,
				'sandbox' => $this->sandbox,
			)
		);
	}

	public function payment_fields() {
		if ( isset( $this->sandbox ) && $this->sandbox === 'yes' ) {
			$this->description .= ' TEST MODE ENABLED. In test mode, you can use any card numbers.';
			$this->description = trim( $this->description );
		}
		if ( ! empty( $this->description ) ) {
			echo wpautop( wp_kses_post( $this->description ) );
		}

		$parameters = array(
			'max_installments'   => $this->max_installments,
			'amount'             => $this->get_order_total(),
			'id'                 => $this->id,
			'installments_value' => $this->calculate_installments(),
		);
		wc_get_template(
			'checkout/credit-card.php',
			$parameters,
			'woo/infinite/pay/module/',
			plugin_dir_path( __FILE__ ) . '../templates/'
		);
	}

	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! isset( $_POST['infinitepay_custom'] ) ) {
			return false;
		}

		if ( $order->get_total() > 0 ) {
			return $this->process_infinitepay_payment( $order );
		}

		return false;
	}

	private function process_infinitepay_payment( $order ) {
		try {
			$log_header = '[' . $order->get_id() . '] ';
			if ( isset( $_POST['infinitepay_custom'] ) &&
			     isset( $_POST['infinitepay_custom']['token'] ) && ! empty( $_POST['infinitepay_custom']['token'] ) &&
			     isset( $_POST['infinitepay_custom']['uuid'] ) && ! empty( $_POST['infinitepay_custom']['uuid'] ) &&
			     isset( $_POST['infinitepay_custom']['doc_number'] ) && ! empty( $_POST['infinitepay_custom']['doc_number'] ) &&
			     isset( $_POST['infinitepay_custom']['installments'] ) && ! empty( $_POST['infinitepay_custom']['installments'] ) &&
			     - 1 !== (int) $_POST['infinitepay_custom']['installments']
			) {
				$token        = sanitize_text_field( $_POST['infinitepay_custom']['token'] );
				$uuid         = sanitize_key( $_POST['infinitepay_custom']['uuid'] );
				$installments = sanitize_text_field( $_POST['infinitepay_custom']['installments'] );
				$doc_number   = sanitize_text_field( $_POST['infinitepay_custom']['doc_number'] );
				$cvv          = sanitize_text_field( $_POST['infinitepay_custom']['cvv'] );
				$nsu          = self::generate_uuid();
				$this->log->write_log( __FUNCTION__, $log_header . 'Starting IP payment for nsu ' . $nsu );

				$order_items = [];
				if ( count( $order->get_items() ) > 0 ) {
					foreach ( $order->get_items() as $item ) {
						$order_items[] = array(
							'id'          => (string) sanitize_key( $item->get_id() ),
							'description' => sanitize_text_field( $item->get_name() ),
							'amount'      => (int) sanitize_text_field( $item->get_data()['total'] * 100 ),
							'quantity'    => (int) sanitize_key( $item->get_quantity() )
						);
					}
				}

				$order_value = $order->get_total() * 100;
				$final_value = (int) explode( '.', $order_value )[0];

				$body = array(
					'payment'         => array(
						'amount'         => $order->get_total() * 100,
						'installments'   => $final_value,
						'capture_method' => 'ecommerce',
						'origin'         => 'woocommerce',
						'payment_method' => 'credit',
						'nsu'            => $nsu
					),
					'card'            => array(
						'cvv'              => $cvv,
						'token'            => $token,
						'card_holder_name' => sanitize_text_field( $order->get_billing_first_name() ) . ' ' . sanitize_text_field( $order->get_billing_last_name() ),
					),
					'order'           => array(
						'id'               => (string) $order->get_id(),
						'amount'           => $final_value,
						'items'            => $order_items,
						'delivery_details' => array(
							'email'        => sanitize_text_field( $order->get_billing_email() ),
							'name'         => sanitize_text_field( $order->get_shipping_first_name() ?: $order->get_billing_first_name() ) . ' ' . sanitize_text_field( $order->get_shipping_last_name() ?: $order->get_billing_last_name() ),
							'phone_number' => sanitize_text_field( $order->get_shipping_phone() ) ?: sanitize_text_field( $order->get_billing_phone() ),
							'address'      => array(
								'line1'   => sanitize_text_field( $order->get_billing_address_1() ),
								'line2'   => sanitize_text_field( $order->get_billing_address_2() ),
								'city'    => sanitize_text_field( $order->get_billing_city() ),
								'state'   => sanitize_text_field( $order->get_billing_state() ),
								'zip'     => sanitize_text_field( $order->get_billing_postcode() ),
								'country' => sanitize_text_field( $order->get_billing_country() ),
							)
						)
					),
					'customer'        => array(
						'document_number' => $doc_number,
						'email'           => sanitize_email( $order->get_billing_email() ),
						'first_name'      => sanitize_text_field( $order->get_shipping_first_name() ?: $order->get_billing_first_name() ),
						'last_name'       => sanitize_text_field( $order->get_shipping_last_name() ?: $order->get_billing_last_name() ),
						'phone_number'    => sanitize_text_field( $order->get_billing_phone() ),
						'address'         => sanitize_text_field( $order->get_shipping_address_1() ?: $order->get_billing_address_1() ),
						'complement'      => sanitize_text_field( $order->get_shipping_address_2() ?: $order->get_billing_address_2() ),
						'city'            => sanitize_text_field( $order->get_shipping_city() ?: $order->get_billing_city() ),
						'state'           => sanitize_text_field( $order->get_shipping_state() ?: $order->get_billing_state() ),
						'zip'             => sanitize_text_field( $order->get_shipping_postcode() ?: $order->get_billing_postcode() ),
						'country'         => sanitize_text_field( $order->get_shipping_country() ?: $order->get_billing_country() ),
					),
					'billing_details' => array(
						'address' => array(
							'line1'   => sanitize_text_field( $order->get_billing_address_1() ),
							'line2'   => sanitize_text_field( $order->get_billing_address_2() ),
							'city'    => sanitize_text_field( $order->get_billing_city() ),
							'state'   => sanitize_text_field( $order->get_billing_state() ),
							'zip'     => sanitize_key( $order->get_billing_postcode() ),
							'country' => sanitize_text_field( $order->get_billing_country() ),
						)
					),
					'metadata'        => array(
						'origin'         => 'woocommerce',
						'plugin_version' => WC_InfinitePay_Constants::VERSION,
						'store_url'      => $_SERVER['SERVER_NAME'],
						'risk'           => array(
							'session_id' => $uuid,
							'payer_ip'   => isset( $_SERVER['HTTP_CLIENT_IP'] ) ? $_SERVER['HTTP_CLIENT_IP'] : ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'] ),
						)
					)
				);
				$args = array(
					'body'    => json_encode( $body, JSON_UNESCAPED_UNICODE ),
					'headers' => array(
						'Authorization' => $this->sandbox === 'yes' ? $this->sandbox_api_key : $this->api_key,
						'Content-Type'  => 'application/json'
					)
				);
				if ( isset( $this->sandbox ) && $this->sandbox === 'yes' ) {
					$args['headers']['Env'] = 'mock';
				}

				$response = wp_remote_post(
					( isset( $this->sandbox ) && $this->sandbox === 'yes' ) ? 'https://authorizer-staging.infinitepay.io/v2/transactions' : 'https://api.infinitepay.io/v2/transactions',
					$args
				);
				$this->log->write_log( __FUNCTION__, $log_header . 'API response code: ' . $response['response']['code'] );
				$body = json_decode( $response['body'], true );
				if ( ! is_wp_error( $response ) && $response['response']['code'] < 500 ) {
					$this->log->write_log( __FUNCTION__, $log_header . 'API response authorization_code: ' . $body['data']['attributes']['authorization_code'] );
					if ( $body['data']['attributes']['authorization_code'] === '00' ) {
						$order->payment_complete();
						$order->add_order_note( '
						' . __( 'Installments', 'infinitepay-woocommerce' ) . ': ' . $installments . '
						' . __( 'Final amount', 'infinitepay-woocommerce' ) . ': R$ ' . number_format( $order->get_total(), 2, ",", "." ) . '
						' . __( 'NSU', 'infinitepay-woocommerce' ) . ': ' . $body['data']['id'] . '
					' );

						WC()->cart->empty_cart();

						$this->log->write_log( __FUNCTION__, $log_header . 'Finished IP payment for nsu ' . $nsu . ' successfully' );

						return array(
							'result'   => 'success',
							'redirect' => $order->get_checkout_order_received_url(),
						);
					} else {
						$code = '';
						if ( $body['data'] && $body['data']['attributes'] && $body['data']['attributes']['authorization_code'] ) {
							$code = $body['data']['attributes']['authorization_code'];
						} else {
							$code = $response['response']['code'];
						}
						$this->log->write_log( __FUNCTION__, $log_header . 'Error ' . $code . ' on IP payment for nsu ' . $nsu . ', error log: ' . json_encode( $body ) );
						wc_add_notice( __( 'Please review your card information and try again', 'infinitepay-woocommerce' ) . ' - ' . $code, 'error' );
						if ( isset( $this->sandbox ) && $this->sandbox === 'yes' ) {
							wc_add_notice( json_encode( $body ), 'error' );
						}
					}
				} else {
					$this->log->write_log( __FUNCTION__, $log_header . 'Error 500 on IP payment for nsu ' . $nsu . ', error log: ' . json_encode( $body ) );
					wc_add_notice( __( 'Ooops, an internal error has occurred, contact an administrator!', 'infinitepay-woocommerce' ), 'error' );
				}
			} else {
				$this->log->write_log( __FUNCTION__, $log_header . 'Misconfiguration error on plugin ' );
				wc_add_notice( __( 'Please review your card information and try again', 'infinitepay-woocommerce' ), 'error' );
			}
		} catch ( Exception $ex ) {
			$this->log->write_log( __FUNCTION__, 'Caught exception: ' . $ex->getMessage() );
		}
	}

	public function change_payment_complete_order_status( $status, $order_id = 0, $order = false ) {
		if ( $order && $order->get_payment_method() === 'infinitepay' ) {
			$status = 'completed';
		}

		return $status;
	}

	public function get_ip_icon() {
		return apply_filters( 'woocommerce_infinitepay_icon', plugins_url( './assets/images/logo.png', plugin_dir_path( __FILE__ ) ) );
	}

	public function thank_you_page() {
		if ( ! empty( $this->instructions ) ) {
			echo wpautop( wptexturize( esc_html( $this->instructions ) ) );
		}
	}

	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if (
			$this->instructions
			&& ! $sent_to_admin
			&& $this->id === $order->payment_method
		) {
			echo wp_kses_post( wpautop( wptexturize( esc_html( $this->instructions ) ) ) . PHP_EOL );
		}
	}
}
