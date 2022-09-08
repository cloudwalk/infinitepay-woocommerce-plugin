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
				'default' => 'production',
				'options'     => [
					'production' => 'Production',
					'sandbox' => 'Sandbox'
				],
			),
			'client_id'       => array(
				'title'       => __( 'Client ID', 'infinitepay-woocommerce' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'placeholder' => __( 'Client ID', 'infinitepay-woocommerce' ),
				'custom_attributes' => [
					'required' => true
				]
			),
			'client_secret'	  => array(
				'title'       => __( 'Client Secret', 'infinitepay-woocommerce' ),
				'type'        => 'password',
				'desc_tip'    => true,
				'placeholder' => __( 'Client Secret', 'infinitepay-woocommerce' ),
				'custom_attributes' => [
					'required' => true
				]
			)
		) );

		return $fields;
	}
	
	public static function get_desc_auth() {
		
		$message = 'Faça seu <a href="https://comprar.infinitepay.io/ecommerce" target="_blank">cadastro na InfinitePay</a> ou <a href="https://money.infinitepay.io/settings/credentials" target="_blank">acesse sua conta</a> para obter as credenciais do plugin.';

		return $message;
	}
}
