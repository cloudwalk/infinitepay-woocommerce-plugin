<?php
namespace Woocommerce\InfinitePay\Helper;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\InfinitePay\Helper\Log;
use Woocommerce\InfinitePay\Helper\Constants;

use Woocommerce\InfinitePay\InfinitePayCore;

class ApiInfinitePay
{
    protected $args = [];
    protected $environment = '';
    public $has_access_token = false;

    public function __construct()
	{
        $options = get_option('woocommerce_infinitepay_settings');
        $this->environment = $options['environment'];

        $this->args = array(
			'headers' => array(
				'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
			),
		);
        
		if ($this->environment === 'sandbox') {
			$this->args['headers']['Env'] = 'mock';
		}
        $this->has_access_token = false !== get_option(Constants::ACCESS_TOKEN_KEY);
        $this->log = new Log();
    }

    public function tokenize() {

        $body = [
            "grant_type"    => "client_credentials",
            "client_id"     =>  get_option(Constants::CLIENT_ID),
            "client_secret" =>  get_option(Constants::CLIENT_SECRET),
            "scope"         => 'card_tokenization'
        ];

        $this->args['body'] = json_encode($body, JSON_UNESCAPED_UNICODE);
        $response = wp_remote_post(($this->environment == 'sandbox') ? 'https://api-staging.infinitepay.io/v2/oauth/token' : 'https://api.infinitepay.io/v2/oauth/token', $this->args );
        if (is_wp_error($response)) {
            return null;
        }

        $body = json_decode($response['body'], true);
        if (!is_wp_error($response) && $response['response']['code'] < 500) {
            if (!is_wp_error($response) && $response['response']['code'] == 401) {
                return null;
            }

            if ($body['access_token']) {
                add_option( Constants::ACCESS_TOKEN_KEY, $body['access_token']);
                return $body['access_token'];
            }
        }
        return null;
    }


    public function transactions($body) {

        $bodyAuth = [
            "grant_type"    => "client_credentials",
            "client_id"     =>  get_option(Constants::CLIENT_ID),
            "client_secret" =>  get_option(Constants::CLIENT_SECRET),
            "scope"         => 'transactions'
        ];

        $this->args['body'] = json_encode($bodyAuth, JSON_UNESCAPED_UNICODE);
        $responseAuth = wp_remote_post(($this->environment == 'sandbox') ? 'https://api-staging.infinitepay.io/v2/oauth/token' : 'https://api.infinitepay.io/v2/oauth/token', $this->args );
        if (is_wp_error($responseAuth)) {
            return null;
        }

        $bodyAuth = json_decode($responseAuth['body'], true);
        if (!is_wp_error($responseAuth) && $responseAuth['response']['code'] < 500) {
            if (!is_wp_error($responseAuth) && $responseAuth['response']['code'] == 401) {
                return null;
            }
            if ($bodyAuth['access_token']) {

                $this->args['headers']['Authorization'] = 'Bearer ' . $bodyAuth['access_token'];
                $this->args['body'] = json_encode($body, JSON_UNESCAPED_UNICODE);
                $response = wp_remote_post( $this->environment == 'sandbox' ? 'https://authorizer-staging.infinitepay.io/v2/transactions' : 'https://api.infinitepay.io/v2/transactions', $this->args );
                return $response;

            }
        }
        return null;
    }

}