<?php
namespace Woocommerce\InfinitePay\Controller;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\InfinitePay\InfinitePayCore;
use Woocommerce\Moip\Helper\ApiInfinitePay;


class Credentials
{
	public function __construct()
	{
    }



// 	curl --request POST \
//   --url https://api.infinitepay.io/v2/oauth/token \
//   --header 'Content-Type: application/json' \
//   --data '{
//         "grant_type": "client_credentials",
//         "client_id": "[client_id]",
//         "client_secret": "[client_secret]",
//         "scope": "[access_token_scope]"
//     }'

	
}