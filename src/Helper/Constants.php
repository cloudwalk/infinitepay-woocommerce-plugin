<?php
namespace Woocommerce\InfinitePay\Helper;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\InfinitePay\InfinitePayCore;

class Constants
{
	const TEXT_DOMAIN      = 'infinitepay-woocommerce';
    const SLUG             = 'infinitepay';
    const VERSION          = '2.0.0';
    const MIN_PHP          = 5.6;
    const API_IP_BASE_URL  = 'https://api.infinitepay.io';
    const ACCESS_TOKEN_KEY = 'infinitepay_access_token';
    const CLIENT_ID 	   = 'infinitepay_client_id';
    const CLIENT_SECRET	   = 'infinitepay_client_secret';
    const INFINITEPAY_TAX = [
        1,
        1.3390,
        1.5041,
        1.5992,
        1.6630,
        1.7057,
        2.3454,
        2.3053,
        2.2755,
        2.2490,
        2.2306,
        2.2111,
    ];
	
}