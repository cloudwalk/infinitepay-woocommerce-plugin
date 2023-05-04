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
				'title'   => __( 'Enable InfinitePay Logo?', 'infinitepay-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Logo', 'infinitepay-woocommerce' ),
				'default' => 'yes',
			),
			'status_aproved' => array(
				'title'   => __( 'Credit Card Payment approved', 'infinitepay-woocommerce' ),
				'type'    => 'select',
				'label'   => __( 'Credit Card Payment approved', 'infinitepay-woocommerce' ),
				'default' => 'wc-processing',
				'options' =>  wc_get_order_statuses()
			),
			'status_aproved_pix' => array(
				'title'   => __( 'PIX Payment approved', 'infinitepay-woocommerce' ),
				'type'    => 'select',
				'label'   => __( 'PIX Payment approved', 'infinitepay-woocommerce' ),
				'default' => 'wc-processing',
				'options' =>  wc_get_order_statuses()
			),
			'status_canceled' => array(
				'title'   => __( 'Payment canceled', 'infinitepay-woocommerce' ),
				'type'    => 'select',
				'label'   => __( 'Payment canceled', 'infinitepay-woocommerce' ),
				'default' => 'wc-cancelled',
				'options' =>  wc_get_order_statuses()
			),
			'enabled_log'	  => array(
				'title'   => __( 'Enable logs', 'infinitepay-woocommerce' ) . '?',
				'type'    => 'checkbox',
				'label'   => __( 'Enable logs', 'infinitepay-woocommerce' ),
				'default' => 'yes',
				'description' => __( 'Avaliable on', 'infinitepay-woocommerce' ) . ' <a href="admin.php?page=wc-status&tab=logs">"WooCommerce > Status > Logs"</a>',
			),
		) );

		return $fields;
	}
}
