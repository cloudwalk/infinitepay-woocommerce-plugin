<?php
namespace Woocommerce\InfinitePay\Helper;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\InfinitePay\Helper\Log;

use Woocommerce\InfinitePay\InfinitePayCore;

class ApiInfinitePay
{
    const ACCESS_TOKEN_KEY = 'infinitepay_access_token';
    protected $endpoint = '';
    protected $args = [];
    protected $environment = '';
    public $has_access_token = false;

    public function __construct($_environment)
	{
        $this->environment = $_environment;
        //TODO: criar endpoint para cada um dos 3 tipos que panachi mandou
        $this->endpoint = $this->environment === 'sandbox' ? 'https://authorizer-staging.infinitepay.io/v2' : 'https://api.infinitepay.io/v2';
        $this->args = array(
			'headers' => array(
				'Content-Type'  => 'application/json',
			),
		);
        
		if ($this->environment === 'sandbox') {
			$this->args['headers']['Env'] = 'mock';
		}
        $this->has_access_token = false !== get_option(self::ACCESS_TOKEN_KEY);
        $this->log = new Log();
    }

    public function auth( $client_id, $client_secret, $scope = 'transactions' ) {

        $body = [
            "grant_type" => "client_credentials",
            "client_id" => $client_id,
            "client_secret" => $client_secret,
            "scope" => "[$scope]"
        ];

        $this->args['body'] = json_encode($body, JSON_UNESCAPED_UNICODE);
        $response = wp_remote_post((isset($this->environment) && $this->environment === 'sandbox') ? 'https://api-staging.infinitepay.io/v2/oauth/token' : 'https://api.infinitepay.io/v2/oauth/token', $this->args );
        if (is_wp_error($response)) {
            return null;
        }

        $body = json_decode($response['body'], true);
        if (!is_wp_error($response) && $response['response']['code'] < 500) {
            if (!is_wp_error($response) && $response['response']['code'] == 401) {
                return null;
            }

            if ($body['access_token']) {
                add_option( self::ACCESS_TOKEN_KEY, $body['access_token']);
                return $body['access_token'];
            }
        }
        return null;
    }


    public function transactions($body) {

        $this->args['headers']['Authorization'] = 'Bearer ' .  get_option( self::ACCESS_TOKEN_KEY, $body['access_token']);
        $this->args['body'] = json_encode($body, JSON_UNESCAPED_UNICODE);

		$response = wp_remote_post( $this->endpoint . '/v2/transactions', $args );

        return $response;

        //TODO: trazer o contexto do erro para cá
    }

}