<?php
namespace Woocommerce\InfinitePay\Helper;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\InfinitePay\InfinitePayCore;
use Woocommerce\InfinitePay\Helper\Constants;

class Utils
{
    public static function generate_uuid() {
		$data = openssl_random_pseudo_bytes( 16 );
		assert( strlen( $data ) == 16 );

		$data[6] = chr( ord( $data[6] ) & 0x0f | 0x40 ); // set version to 0100
		$data[8] = chr( ord( $data[8] ) & 0x3f | 0x80 ); // set bits 6-7 to 10

		return vsprintf( '%s%s-%s-%s-%s-%s%s%s', str_split( bin2hex( $data ), 4 ) );
	}
    
    public static function get_querystring( $type, $name, $default, $sanitize = 'strip_html' )
	{
		$request = filter_input_array( $type, FILTER_SANITIZE_SPECIAL_CHARS );

		if ( ! isset( $request[ $name ] ) || empty( $request[ $name ] ) ) {
			return $default;
		}

		return self::sanitize( $request[ $name ], $sanitize );
	}


    public static function sanitize( $value, $sanitize )
	{
		if ( ! is_callable( $sanitize ) ) {
	    	return ( false === $sanitize ) ? $value : self::strip_html( $value );
		}

		if ( is_array( $value ) ) {
			return array_map( $sanitize, $value );
		}

		return call_user_func( $sanitize, $value );
	}


    public static function strip_html( $value, $remove_breaks = false )
	{
		if ( empty( $value ) || is_object( $value ) ) {
			return $value;
		}

		if ( is_array( $value ) ) {
			return array_map( __METHOD__, $value );
		}

	    return wp_strip_all_tags( $value, $remove_breaks );
	}

	public static function calculate_installments($amount)
    {
		$options = get_option('woocommerce_infinitepay_settings');
        $installments_value = [];
		
        for (
            $i = 1;
            $i <= (int) $options['max_installments'];
            $i++
        ) {
            $tax      = !((int) $options['max_installments_free'] >= $i) && $i > 1;
            $interest = 1;
            if ($tax) {
                $interest = Constants::INFINITEPAY_TAX[$i - 1] / 100;
            }
            $value = !$tax ? $amount / $i : $amount * ($interest / (1 - pow(1 + $interest, -$i)));
            $installments_value[] = array(
                'value'    => $value,
                'interest' => $tax,
            );
        }

        return $installments_value;
    }

	public static function getConfig( $key, $default = null )
	{
		$options = get_option('woocommerce_infinitepay_settings');
		if( isset($options) && isset($options[$key]) ) {
			return $options[$key];
		} else if( isset($default) ) {
			return $default;
		} else {
			return null;
		}
	}

	public static function getStoreUrl()
	{
		$https = filter_input(INPUT_SERVER, 'HTTPS');
		$server = filter_input(INPUT_SERVER, 'SERVER_NAME');
		$server_post = filter_input(INPUT_SERVER, 'SERVER_PORT');

		$protocol = (!empty($https) && (strtolower($https) == 'on' || $https == '1')) ? 'https://' : 'http://';
		$port = $server_post ? ':'.$server_post : '';
		return $protocol.$server.$port;
	}

	public static function getErrorByCode($code) {
		$error = array_filter(Constants::ERROR_CODES, function ($var) {
			return ( in_array($code,  $var['code']));
		});
		if(!$error) {
			$error = [
				'title'   => 'Payment fail',
				'content' => 'Please review your card information and try again',
				'code'    => [$code],
			];
		}
		return $error;
	}
}