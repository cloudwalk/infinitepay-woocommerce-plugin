<?php
namespace Woocommerce\InfinitePay\Fields;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

class CredentialsFields
{
	public static function fields() {
	
		$fields = apply_filters( 'wc_infinitepay_form_fields', array(
			'auth'				=> array(
				'title'       => '',
				'type'        => 'title',
				'description' => self::get_desc_auth(),
			),
			'environment' => array(
				'title'   => __( 'Store environment', 'infinitepay-woocommerce' ),
				'type'    => 'select',
				'label'   => __( 'Store environment', 'infinitepay-woocommerce' ),
				'default' => 'yes',
				'options'     => [
					'sandbox' => 'Sandbox',
					'production' => 'Production',
				],
			),
			'client_id'       => array(
				'title'       => __( 'Client ID', 'infinitepay-woocommerce' ),
				'type'        => 'text',
				'desc_tip'    => true,
			),
			'client_secret'	  => array(
				'title'       => __( 'Client Secret', 'infinitepay-woocommerce' ),
				'type'        => 'password',
				'desc_tip'    => true,
			)
		) );

		return $fields;
	}
	
	public static function get_desc_auth() {
		
		$message = sprintf(
			'<a href="https://www.infinitepay.io/woocommerce/" target="_blank">%s</a> %s',
			__( 'Click here', 'infinitepay-woocommerce' ),
			__( 'to create an account or request plugin credentials', 'infinitepay-woocommerce' ),
		);

		return $message;
	}
}
