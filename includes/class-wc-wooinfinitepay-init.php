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

	private function calculate_installments(): array {
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
			|| empty( $this->api_key )
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
				'uuid' => vsprintf( '%s%s-%s-%s-%s-%s%s%s', str_split( bin2hex( random_bytes( 16 ) ), 4 ) ),
			)
		);
	}

	public function payment_fields() {
		if ( isset( $this->sandbox ) && $this->sandbox === 'yes' ) {
			$this->description .= ' TEST MODE ENABLED. In test mode, you can use the any card numbers.';
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

			$card_info       = explode( ':', $token );
			$card_expiration = explode( '/', $card_info[1] );


			$order_items = [];
			if ( count( $order->get_items() ) > 0 ) {
				foreach ( $order->get_items() as $item ) {
					$order_items[] = array(
						'id'          => (string) sanitize_key( $item->get_id() ),
						'description' => sanitize_text_field( $item->get_name() ),
						'amount'      => (float) sanitize_text_field( $item->get_data()['total'] ),
						'quantity'    => (int) sanitize_key( $item->get_quantity() )
					);
				}
			}

			$body = array(
				'payment'         => array(
					'amount'         => $order->get_total() * 100,
					'installments'   => (int) sanitize_text_field( $installments ),
					'capture_method' => 'ecommerce',
					'origin'         => 'woocommerce',
					'payment_method' => 'credit'
				),
				'card'            => array(
					'cvv'                   => $card_info[2],
					'card_number'           => $card_info[0],
					'card_holder_name'      => sanitize_text_field( $order->get_billing_first_name() ) . ' ' . sanitize_text_field( $order->get_billing_last_name() ),
					'card_expiration_month' => str_pad( $card_expiration[0], 2, '0', STR_PAD_LEFT ),
					'card_expiration_year'  => str_pad( $card_expiration[1], 4, '20', STR_PAD_LEFT ),
				),
				'order'           => array(
					'id'               => (string) $order->get_id(),
					'amount'           => (float) $order->get_total(),
					'items'            => $order_items,
					'delivery_details' => array(
						'email'        => sanitize_text_field( $order->get_billing_email() ),
						'name'         => sanitize_text_field( $order->get_shipping_first_name() ) . ' ' . sanitize_text_field( $order->get_shipping_last_name() ),
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
					'first_name'      => sanitize_text_field( $order->get_shipping_first_name() ),
					'last_name'       => sanitize_text_field( $order->get_billing_last_name() ),
					'phone_number'    => sanitize_text_field( $order->get_billing_phone() ),
					'address'         => sanitize_text_field( $order->get_shipping_address_1() ),
					'complement'      => sanitize_text_field( $order->get_shipping_address_2() ),
					'city'            => sanitize_text_field( $order->get_shipping_city() ),
					'state'           => sanitize_text_field( $order->get_shipping_state() ),
					'zip'             => sanitize_key( $order->get_shipping_postcode() ),
					'country'         => sanitize_text_field( $order->get_shipping_country() ),
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
					'risk' => array(
						'session_id' => $uuid
					)
				)
			);
			$args = array(
				'body'    => json_encode( $body ),
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
			if ( ! is_wp_error( $response ) && $response['response']['code'] < 500 ) {
				$body = json_decode( $response['body'], true );
				if ( $body['data']['attributes']['authorization_code'] === '00' ) {
					$order->payment_complete();
					$order->add_order_note( '
						' . __( 'Installments', 'infinitepay-woocommerce' ) . ': ' . $installments . '
						' . __( 'Final amount', 'infinitepay-woocommerce' ) . ': R$ ' . number_format( $order->get_total(), 2, ",", "." ) . '
						' . __( 'NSU', 'infinitepay-woocommerce' ) . ': ' . $body['data']['id'] . '
					' );

					WC()->cart->empty_cart();

					return array(
						'result'   => 'success',
						'redirect' => $order->get_checkout_order_received_url(),
					);
				} else {
                    $code = '';
                    if ( $body['data'] && $body['data']['attributes'] && $body['data']['attributes']['authorization_code'] ) {
                        $code = $body['data']['attributes']['authorization_code'];
                    }
					wc_add_notice( __( 'Please review your card information and try again', 'infinitepay-woocommerce' ) . ' - ' . $code, 'error' );
                    if ( isset( $this->sandbox ) && $this->sandbox === 'yes' ) {
                        wc_add_notice( json_encode( $body ), 'error' );
                    }
				}
			} else {
				wc_add_notice( __( 'Ooops, an internal error has occurred, contact an administrator!', 'infinitepay-woocommerce' ), 'error' );
			}
		} else {
			wc_add_notice( __( 'Please review your card information and try again', 'infinitepay-woocommerce' ), 'error' );
		}
	}

	public function change_payment_complete_order_status( $status, $order_id = 0, $order = false ) {
		if ( $order && $order->get_payment_method() === 'infinitepay' ) {
			$status = 'completed';
		}

		return $status;
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
