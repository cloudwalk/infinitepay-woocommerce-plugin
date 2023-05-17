<?php
namespace Woocommerce\InfinitePay\Fields;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

class CreditCardFields
{
	public static function fields() {
	
        $fields = apply_filters( 'wc_infinitepay_form_fields', array(
			'enabled_creditcard'               => array(
				'title'   => _e( 'Enabled/Disabled', 'infinitepay-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => _e( 'Enable Credit Card with InfinitePay', 'infinitepay-woocommerce' ),
				'default' => 'yes',
			),
			'instructions'          => array(
				'title'       => _e( 'Instructions', 'infinitepay-woocommerce' ),
				'type'        => 'textarea',
				'description' => _e( 'Instructions that will be shown on thank you page and will be sent on email', 'infinitepay-woocommerce' ),
				'default'     => _e( '', 'infinitepay-woocommerce' ),
				'desc_tip'    => true,
			),
			'max_installments'      => array(
				'title'       => _e( 'Maximum number of installments', 'infinitepay-woocommerce' ),
				'type'        => 'select',
				'description' => _e( 'Maximum number of installments that a customer can split the final amount', 'infinitepay-woocommerce' ),
				'default'     => 12,
				'desc_tip'    => true,
				'options'	  => [ 
					1 => 1,
					2 => 2,
					3 => 3,
					4 => 4,
					5 => 5,
					6 => 6,
					7 => 7,
					8 => 8,
					9 => 9,
					10 => 10,
					11 => 11,
					12 => 12
				 ] 
			),
			'max_installments_free' => array(
				'title'       => _e( 'Maximum number of installments without interest', 'infinitepay-woocommerce' ),
				'type'        => 'select',
				'description' => _e( 'Maximum number of installments that a customer can split the final amount without interest', 'infinitepay-woocommerce' ),
				'default'     => 12,
				'desc_tip'    => true,
				'options'	  => [ 
					1 => 1,
					2 => 2,
					3 => 3,
					4 => 4,
					5 => 5,
					6 => 6,
					7 => 7,
					8 => 8,
					9 => 9,
					10 => 10,
					11 => 11,
					12 => 12
				 ] 
			)
		) );

		return $fields;
	}
}
