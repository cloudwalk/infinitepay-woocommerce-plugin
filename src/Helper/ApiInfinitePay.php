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
    protected $client_id;
    protected $client_secret;
    public $has_access_token = false;

    public function __construct()
	{
        $options = get_option('woocommerce_infinitepay_settings');
        $this->environment = $options['environment'];
        $this->client_id = $options['client_id'];
        $this->client_secret = $options['client_secret'];

        $this->args = array(
			'headers' => array(
				'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
			),
            'user-agent' => 'ecommerce/infinitepay'
		);
        
		if ($this->environment === 'sandbox') {
			$this->args['headers']['Env'] = 'mock';
		}
        $this->args['timeout'] = 30;
        
        $this->has_access_token = false !== get_option(Constants::ACCESS_TOKEN_KEY);
        $this->log = new Log();
    }

    public function tokenize() {

        $body = [
            "grant_type"    => "client_credentials",
            "client_id"     =>  $this->client_id,
            "client_secret" =>  $this->client_secret,
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
                $this->log->write_log(__FUNCTION__, json_encode($response['response']));
                return null;
            }

            if ($body['access_token']) {
                add_option( Constants::ACCESS_TOKEN_KEY, $body['access_token']);
                return $body['access_token'];
            }
        }
        $this->log->write_log(__FUNCTION__, json_encode($response['response']));
        return null;
    }


    public function transactions($body, $payment_method) {

        $bodyAuth = [
            "grant_type"    => "client_credentials",
            "client_id"     =>  $this->client_id,
            "client_secret" =>  $this->client_secret,
            "scope"         => 'transactions'
        ];

        $this->args['body'] = json_encode($bodyAuth, JSON_UNESCAPED_UNICODE);
        $responseAuth = wp_remote_post(($this->environment == 'sandbox') ? 'https://api-staging.infinitepay.io/v2/oauth/token' : 'https://api.infinitepay.io/v2/oauth/token', $this->args );
        if (is_wp_error($responseAuth)) {
            $this->log->write_log(__FUNCTION__, 'auth: ' . json_encode($response['response']));
            return null;
        }

        $bodyAuth = json_decode($responseAuth['body'], true);
        if (!is_wp_error($responseAuth) && $responseAuth['response']['code'] < 500) {
            if (!is_wp_error($responseAuth) && $responseAuth['response']['code'] == 401) {
                $this->log->write_log(__FUNCTION__, 'auth: ' . json_encode($response['response']));
                return null;
            }
            if ($bodyAuth['access_token']) {

                if( 'pix' == $payment_method) { 
                    unset($this->args['headers']['Env']);
                }

                $endpoint = $this->environment == 'sandbox' ? 'https://authorizer-staging.infinitepay.io/v2/transactions' : 'https://api.infinitepay.io/v2/transactions';
                $this->args['headers']['Authorization'] = 'Bearer ' . $bodyAuth['access_token'];
                $this->args['body'] = json_encode($body, JSON_UNESCAPED_UNICODE);

                if(isset($body['card'])) {
                    unset($body['card']);
                }
                if(isset($body['customer'])) {
                    unset($body['customer']);
                }
                $contentTolog = ['endpoint' => $endpoint, 'body' => $body];

                $this->log->write_log(__FUNCTION__ . '-request', json_encode($contentTolog, JSON_PRETTY_PRINT));

                $response = wp_remote_post( $endpoint, $this->args );

                $this->log->write_log(__FUNCTION__ . '-response', json_encode($response, JSON_PRETTY_PRINT));

                return $response;
            }
        }
        return null;
    }

}