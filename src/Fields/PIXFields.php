<?php
namespace Woocommerce\InfinitePay\Fields;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

class PIXFields
{
	public static function fields() {
	
        $fields = apply_filters( 'wc_infinitepay_form_fields', array(
			'enabled_pix'               => array(
				'title'   => __( 'Enabled/Disabled', 'infinitepay-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable PIX with InfinitePay', 'infinitepay-woocommerce' ),
				'default' => 'yes',
			),
			'title_pix'                 => array(
				'title'       => __( 'Payment Title', 'infinitepay-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Title that will be shown for the customers on your checkout page', 'infinitepay-woocommerce' ),
				'default'     => __( 'PIX', 'infinitepay-woocommerce' ),
				'desc_tip'    => true,
			),
			'instructions'          => array(
				'title'       => __( 'Instructions', 'infinitepay-woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'Instructions that will be shown on thank you page and will be sent on email', 'infinitepay-woocommerce' ),
				'default'     => __( '', 'infinitepay-woocommerce' ),
				'desc_tip'    => true,
			),
			'discount_pix'      => array(
				'title'       => __( 'Discount (%)', 'infinitepay-woocommerce' ),
				'type'        => 'number',
				'default'     => '5.0',
				'desc_tip'    => true,
			),
			'min_value_pix' => array(
				'title'       => __( 'Minimum value (R$)', 'infinitepay-woocommerce' ),
				'type'        => 'number',
				'default'     => '10.00',
				'desc_tip'    => true,
			),
			
		) );

		return $fields;
	}
}
