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
				'title'   => __( 'Enabled/Disabled', 'infinitepay-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Credit Card with InfinitePay', 'infinitepay-woocommerce' ),
				'default' => 'yes',
			),
			'title_credit_card'                 => array(
				'title'	  	 => __( 'Credit Card Payment Title', 'infinitepay-woocommerce' ),
				'type'		  => 'text',
				'description' => __( 'Title that will be shown for the customers on your checkout page', 'infinitepay-woocommerce' ),
				'default'     => __( 'Credit Card', 'infinitepay-woocommerce' ),
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
				'type'        => 'select',
				'description' => __( 'Maximum number of installments that a customer can split the final amount', 'infinitepay-woocommerce' ),
				'default'     => 12,
				'desc_tip'    => true,
				'options'	  => [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 ] 
			),
			'max_installments_free' => array(
				'title'       => __( 'Maximum number of installments without interest', 'infinitepay-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Maximum number of installments that a customer can split the final amount without interest', 'infinitepay-woocommerce' ),
				'default'     => '12',
				'desc_tip'    => true,
				'options'	  => [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 ] 
			)
		) );

		return $fields;
	}
}