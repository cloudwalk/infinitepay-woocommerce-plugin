<?php
namespace Woocommerce\InfinitePay\Fields;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

class ConfigurationsFields
{
	public static function fields() {
	
        $fields = apply_filters( 'wc_infinitepay_form_fields', array(
			'enabled_logo'	  => array(
				'title'   => _e( 'Enable InfinitePay Logo?', 'infinitepay-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => _e( 'Enable Logo', 'infinitepay-woocommerce' ),
				'default' => 'yes',
			),
			'status_aproved' => array(
				'title'   => _e( 'Credit Card Payment approved', 'infinitepay-woocommerce' ),
				'type'    => 'select',
				'label'   => _e( 'Credit Card Payment approved', 'infinitepay-woocommerce' ),
				'default' => 'wc-processing',
				'options' =>  wc_get_order_statuses()
			),
			'status_aproved_pix' => array(
				'title'   => _e( 'PIX Payment approved', 'infinitepay-woocommerce' ),
				'type'    => 'select',
				'label'   => _e( 'PIX Payment approved', 'infinitepay-woocommerce' ),
				'default' => 'wc-processing',
				'options' =>  wc_get_order_statuses()
			),
			'status_canceled' => array(
				'title'   => _e( 'Payment canceled', 'infinitepay-woocommerce' ),
				'type'    => 'select',
				'label'   => _e( 'Payment canceled', 'infinitepay-woocommerce' ),
				'default' => 'wc-cancelled',
				'options' =>  wc_get_order_statuses()
			),
			'enabled_log'	  => array(
				'title'   => _e( 'Enable logs', 'infinitepay-woocommerce' ) . '?',
				'type'    => 'checkbox',
				'label'   => _e( 'Enable logs', 'infinitepay-woocommerce' ),
				'default' => 'yes',
				'description' => _e( 'Avaliable on', 'infinitepay-woocommerce' ) . ' <a href="admin.php?page=wc-status&tab=logs">"WooCommerce > Status > Logs"</a>',
			),
		) );

		return $fields;
	}
}
