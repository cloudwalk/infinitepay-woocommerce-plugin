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
				'title'   => _e( 'Enabled/Disabled', 'infinitepay-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => _e( 'Enable PIX with InfinitePay', 'infinitepay-woocommerce' ),
				'default' => 'yes',
			),
			'instructions_pix'          => array(
				'title'       => _e( 'Instructions', 'infinitepay-woocommerce' ),
				'type'        => 'textarea',
				'description' => _e( 'Instructions that will be shown on thank you page and will be sent on email', 'infinitepay-woocommerce' ),
				'default'     => _e( '', 'infinitepay-woocommerce' ),
				'desc_tip'    => true,
			),
			'discount_pix'      => array(
				'title'       => _e( 'Discount (%)', 'infinitepay-woocommerce' ),
				'type'        => 'number',
				'default'     => '0',
				'desc_tip'    => true,
			),
			'min_value_pix' => array(
				'title'       => _e( 'Minimum value (R$)', 'infinitepay-woocommerce' ),
				'type'        => 'number',
				'default'     => '10.00',
				'desc_tip'    => true,
			),
			
		) );

		return $fields;
	}
}
