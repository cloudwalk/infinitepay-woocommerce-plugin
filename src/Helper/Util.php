<?php
namespace Woocommerce\InfinitePay\Helper;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

// use Moip\Auth\Connect;
// use Moip\Moip;

use Woocommerce\InfinitePay\InfinitePayCore;

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


    public static function log( $data, $log_name = 'debug' )
	{
		$name = sprintf( '%s-%s.log', $log_name, date( 'd-m-Y' ) );
		$log  = print_r( $data, true ) . PHP_EOL;
		$log .= "\n=============================\n";

		file_put_contents( Core::get_file_path( $name, 'logs/' ), $log, FILE_APPEND );
	}

}