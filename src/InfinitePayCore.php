<?php
namespace Woocommerce\InfinitePay;

if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\InfinitePay\Controller\Settings;
use Woocommerce\InfinitePay\Controller\Checkout;
use Woocommerce\InfinitePay\Helper\ApiInfinitePay;
use Woocommerce\InfinitePay\Helper\Log;
use Woocommerce\InfinitePay\Helper\Utils;
use Woocommerce\InfinitePay\Helper\Constants;

class InfinitePayCore extends \WC_Payment_Gateway
{
	public $core_settings;
	public $api;

    public function __construct()
    {
        $this->update_plugin_version();
        $this->load_plugin_textdomain();
        $this->setup_properties();
		$this->init_form_fields();
    
		$this->core_settings = new Settings($this);
        $this->title         = 'Cartão de crédito & Pix';
        $this->icon			 = $this->get_ip_icon();
		$this->log			 = new Log();

		$this->api = new ApiInfinitePay();

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options') );
        add_action( 'wp_enqueue_scripts', array($this, 'payment_scripts') );
        add_action( 'admin_enqueue_scripts', array($this, 'admin_scripts') );
        add_action( 'woocommerce_thankyou_' . $this->id, array($this, 'thank_you_page') );
        add_filter( 'woocommerce_payment_complete_order_status', array($this, 'change_payment_complete_order_status'), 10, 3 );
        add_action( 'woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3 );        
    }
    
    public static function load_plugin_textdomain()
    {
        $text_domain = 'infinitepay-woocommerce';
        $locale      = apply_filters('plugin_locale', get_locale(), $text_domain);

        $original_language_file = dirname(__FILE__) . '/../i18n/languages/infinitepay-woocommerce-' . $locale . '.mo';

        unload_textdomain($text_domain);
        load_textdomain($text_domain, $original_language_file);
    }

    public function update_plugin_version()
    {
        $old_version = get_option('_ip_version', '0');
        if (version_compare(Constants::VERSION, $old_version, '>')) {
            update_option('_ip_version', Constants::VERSION, true);
        }
    }

    public static function woocommerce_instance()
    {
        if (function_exists('WC')) {
            return WC();
        } else {
            global $woocommerce;

            return $woocommerce;
        }
    }

	public function admin_options()
    {
		?>
		<h2>InfinitePay</h2>
        <?php if ( Utils::getConfig('environment') === 'sandbox' || !Utils::getConfig('client_id') ): ?>
            <style>.bgwarning{ background-color:#dba61740;}</style>
			<div id="message" class="notice-warning notice bgwarning">
                <h3>InfinitePay</h3>
                <p><?php echo __('NOTICE: Before receiving payments using InfinitePay, you must:', 'infinitepay-woocommerce' ); ?></p>
                <ul>
                <?php if (!Utils::getConfig('client_id')): ?>
                    <li>&#8227; <?php echo __('Configure access credentials (Client ID and Client Secret, visit the Credentials tab for more information)', 'infinitepay-woocommerce' ); ?></li>
                <?php endif;?>
                <?php if (Utils::getConfig('environment') === 'sandbox'): ?>
                    <li>&#8227; <?php echo __('Disable Sandbox mode (sandbox environment should only be used for testing, sales will not be effected on your InfinitePay account)', 'infinitepay-woocommerce' ); ?></li>
                <?php endif;?>
                </ul>
                <!-- <p><a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=checkout&section=infinitepay' ); ?>"><?php echo __('Go to configuration', 'infinitepay-woocommerce' ); ?></a> -->
			</div>
		<?php endif;

         if (!$this->api->has_access_token): ?>
			<div id="message" class="notice-warning notice">
                Faça seu <a href="https://comprar.infinitepay.io/ecommerce" target="_blank">cadastro na InfinitePay</a> ou <a href="https://money.infinitepay.io/settings/credentials" target="_blank">acesse sua conta</a> para obter as credenciais do plugin.
			</div>
		<?php endif;?>

		<?php
			echo Settings::build_submenu();
			echo '<table class="form-table">';
			echo $this->generate_settings_html($this->get_form_fields(), false);
			echo '</table>';
	}

    

    public function init_form_fields()
    {
        $tab = filter_input(INPUT_GET, 'ip-tab');
        $current_section = isset($tab) ? $tab : 'ip-credentials';
		$this->form_fields = Settings::form_fields($current_section); 
    }

    function admin_scripts($hook) {
        if ('woocommerce_page_wc-settings' !== $hook) {
            return;
        }
        $script_path       = '/../build/admin.js';
        $script_asset_path = dirname(__FILE__) . '/../build/index.asset.php';
        $script_asset      = file_exists($script_asset_path) ? require $script_asset_path : array('dependencies' => array(), 'version' => filemtime($script_path));
        $script_url 	= plugins_url($script_path, __FILE__);
        
        wp_register_script(
            'woocommerce_infinitepay',
            $script_url,
            $script_asset['dependencies'],
            $script_asset['version'],
            true
        );
    }

    protected function setup_properties()
    {
        $this->id                 = 'infinitepay';
        $this->icon               = apply_filters('woocommerce_offline_icon', '');
        $this->method_title       = 'Cartão de crédito & Pix | by InfinitePay';
        $this->method_description = __('Accept payments with InfinitePay', 'infinitepay-woocommerce');
        $this->has_fields         = true;
        $this->supports           = [ 'products' ];
    }

    public function payment_scripts()
    {
        if (
            (
                !is_cart()
                && !is_checkout()
                && !empty(filter_input(INPUT_GET, 'pay_for_order'))
            )
            || $this->core_settings->enabled_creditcard === 'no'
            || empty($this->core_settings->client_id ?: $this->core_settings->client_secret)
            || ((
                !isset($this->core_settings->environment) || $this->core_settings->environment === 'production'
            ) && !is_ssl()
            )
        ) {
            return;
        }

		$card_tokenization = $this->api->tokenize();

        $script_path       = '/../build/index.js';
        $script_asset_path = dirname(__FILE__) . '/../build/index.asset.php';
        $script_asset      = file_exists($script_asset_path) ? require $script_asset_path : array('dependencies' => array(), 'version' => filemtime($script_path));
        $script_url 	= plugins_url($script_path, __FILE__);

        wp_register_script(
            'woocommerce_infinitepay',
            $script_url,
            $script_asset['dependencies'],
            $script_asset['version'],
            true
        );

        wp_enqueue_script('woocommerce_infinitepay');
        wp_localize_script(
            'woocommerce_infinitepay',
            'wc_infinitepay_params',
            array(
                'access_token'	=> $card_tokenization,
                'environment'	=> $this->core_settings->environment,
				'script_url'	=> $this->core_settings->environment === 'sandbox' ? "https://ipayjs.infinitepay.io/development/ipay-latest.min.js" : "https://ipayjs.infinitepay.io/production/ipay-latest.min.js"
            )
        );
    }

    public function payment_fields()
    {
        $pix_value = 0;
        $orderTotalWithDiscount = $this->get_order_total();
        $discount_pix  = (float)$this->core_settings->discount_pix;
        $min_value_pix = (float)$this->core_settings->min_value_pix;
        
        if ( $discount_pix && $orderTotalWithDiscount >= $min_value_pix ) {
            $discountValue  = ( $orderTotalWithDiscount * $discount_pix ) / 100;
            $pix_value      = number_format( ($orderTotalWithDiscount - $discountValue), 2, ',', '.');
        }

        $css_custom = 'wcv'. WC_VERSION . ' wpv' . get_bloginfo('version') . ' ipv' . Constants::VERSION;

        $parameters = array(
            'max_installments'   => $this->core_settings->max_installments,
            'amount'             => $this->get_order_total(),
            'id'                 => $this->id,
            'installments_value' => Utils::calculate_installments($this->get_order_total()),
            'enabled_creditcard' => $this->core_settings->enabled_creditcard,
            'enabled_pix'        => $this->core_settings->enabled_pix,
            'instructions'       => $this->core_settings->instructions,
            'instructions_pix'   => $this->core_settings->instructions_pix,
            'enabled_logo'       => $this->core_settings->enabled_logo,
            'pix_logo'           => plugins_url('/assets/images/pix-106.svg', plugin_dir_path(__FILE__)),
            'pix_value'          => $pix_value,
            'discount_pix'       => $discount_pix,
            'sandbox_warning'    => (isset($this->core_settings->environment) && $this->core_settings->environment === 'sandbox') ? __('TEST MODE ENABLED. In test mode, you can use any card numbers.', 'infinitepay-woocommerce') : '',
            'css_custom'         => str_replace('.', '_', $css_custom),
        );

        wc_get_template(
            'checkout/checkout.php',
            $parameters,
            'woo/infinite/pay/module/',
            plugin_dir_path(__FILE__) . '../templates/'
        );
    }

    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);        
        $infinitepay_custom = filter_input( INPUT_POST, 'infinitepay_custom',  FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

        if ( empty( $infinitepay_custom ) ) {
            return false;
        }

        if ($order->get_total() > 0) {
            return $this->process_infinitepay_payment($order);
        }

        return false;
    }

    private function process_infinitepay_payment($order)
    {

        try {

			$checkout = new Checkout($order);

            $post = filter_input( INPUT_POST, 'infinitepay_custom',  FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		    $ip_method = filter_input( INPUT_POST, 'ip_method', FILTER_SANITIZE_STRING );

            $is_creditcard = ( !empty($post) &&
                isset($post['token']) && !empty($post['token']) &&
                isset($post['uuid']) && !empty($post['uuid']) &&
                isset($post['doc_number']) && !empty($post['doc_number']) &&
                isset($post['installments']) && !empty($post['installments']) && -1 !== (int) $post['installments'] );

            $is_pix = ( $ip_method == 'pix-form');

            $log_header = '[' . $order->get_id() . '] ';
            if ( $is_creditcard && !$is_pix ) {
				$result = $checkout->process_credit_card();
                if($result) {
                    return array(
                        'result'   => 'success',
                        'redirect' => $order->get_checkout_order_received_url(),
                    );
                }
            } else if ( $is_pix ) {

                $result = $checkout->process_pix();
                if($result) {
                    return array(
						'result'   => 'success',
						'redirect' => $order->get_checkout_order_received_url(),
					);
                }
            } else {
                $this->log->write_log(__FUNCTION__, $log_header . ' Could not catch all form information (Pix or Credit)');
                wc_add_notice(__( 'Please review your payment information and try again', 'infinitepay-woocommerce'), 'error');
            }
        } catch (Exception $ex) {
            $this->log->write_log(__FUNCTION__, 'Caught exception: ' . $ex->getMessage());
        }
    }

    public function change_payment_complete_order_status($status, $order_id = 0, $order = false)
    {
        if ($order && $order->get_payment_method() === 'infinitepay') {
            if( $order->get_meta('payment_method') == 'credit' ) {
                $status = 'processing';
            } else  if( $order->get_meta('payment_method') == 'pix' ) {
                $status = 'pending';
            }
        }
        return $status;
    }

    public function get_ip_icon()
    {
        if ($this->core_settings->enabled_logo == 'yes') {
            return apply_filters('woocommerce_infinitepay_icon', plugins_url('/assets/images/logo.png', plugin_dir_path(__FILE__)));
        }
    }

    public function thank_you_page( $order_id )
    {
        $order = wc_get_order( $order_id );

        if( $order->get_meta('payment_method') == 'credit' ) {
            if (!empty($this->core_settings->instructions)) {
                echo wpautop(wptexturize(esc_html($this->core_settings->instructions)));
            }
        } else  if( $order->get_meta('payment_method') == 'pix' ) {
            $this->pix_checkout_html( $order );
        }
    }

    public function pix_checkout_html( $order ) {

		if ( $order->get_payment_method() != 'infinitepay' ) {
			return '';
		}

        // Retrieve order comments
        remove_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );
        $orderComments = get_comments( array(
            'post_id' => $order->get_id(),
            'orderby' => 'comment_ID',
            'order'   => 'DESC',
            'approve' => 'approve',
            'type'    => 'order_note',
            'number'  => 1
        ) );
        add_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );

        $code = ltrim( rtrim( str_replace( "br_code: ", "", $orderComments[0]->comment_content ) ) );
        $storeUrl = Utils::getStoreUrl();

        $parameters = array(
            'order' => $order,
            'storeUrl' => $storeUrl,
            'code' => $code
        );

        wc_get_template(
            'order-received/order-received.php',
            $parameters,
            'woo/infinite/pay/module/',
            plugin_dir_path(__FILE__) . '../templates/'
        );
	}

    public function email_instructions($order, $sent_to_admin, $plain_text = false)
    {
        if( $order->get_meta('payment_method') == 'credit' ) {
            if (
                $this->core_settings->instructions
                && !$sent_to_admin
                && $this->id === $order->payment_method
            ) {
                echo wp_kses_post(wpautop(wptexturize(esc_html($this->core_settings->instructions))) . PHP_EOL);
            }
        } else  if( $order->get_meta('payment_method') == 'pix' ) {
            echo $this->pix_email_html( $order );
        }
    }

    public function pix_email_html( $order ) {
		
		if ( $order->get_payment_method() != 'infinitepay' ) {
			return '';
		}

		// Retrieve order comments
		remove_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );
		$orderComments = get_comments( array(
			'post_id' => $order->get_id(),
			'orderby' => 'comment_ID',
			'order'   => 'DESC',
			'approve' => 'approve',
			'type'    => 'order_note',
			'number'  => 1
		) );
		add_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );

		$code = ltrim( rtrim( str_replace( "br_code: ", "", $orderComments[0]->comment_content ) ) );

		// Create html structure
		$html = '<div id="qrcodepixcontent">';
		$html .= '  <img id="copy-code" style="cursor:pointer; display: initial;margin-right: 1rem;" class="wcpix-img-copy-code" src="https://gerarqrcodepix.com.br/api/v1?brcode=' . urlencode( $code ) . '"	alt="QR Code"/>';
        $html .= '    <p style="font-size: 19px;margin-bottom: 0.5rem;">Pix: <strong>R$ ' . $order->get_total() . '</strong></p>';
		$html .= '    <div style="word-wrap: break-word; max-width: 450px;">';
		$html .= '      <small>Código de transação</small><br>';
		$html .= '      <code style="font-size: 87.5%; color: #e83e8c; word-wrap: break-word;">' . $code . '</code>';
		$html .= '    </div>';
		$html .= '</div>';

		// Return html
		return $html;
	}

}
