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
        $this->title         = $this->core_settings->title;
        $this->icon			 = $this->get_ip_icon();
		$this->log			 = new Log($this);


		$this->api = new ApiInfinitePay();

		add_action('woocommerce_update_options_payment_gateways_infinitepay', array($this, 'process_admin_options'));
        add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));
        add_action('woocommerce_thankyou_infinitepay', array($this, 'thank_you_page'));
        add_filter('woocommerce_payment_complete_order_status', array($this, 'change_payment_complete_order_status'), 10, 3);
        add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);
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
		<?php if (!$this->api->has_access_token): ?>
			<div id="message" class="notice-warning notice">
			<?php
			printf(
					'<p><strong>%s: </strong>%s <strong>%s</strong>.</p>',
					__('Before accepting payments', 'infinitepay-woocommerce'),
					__('you need to add your', 'infinitepay-woocommerce'),
					__('Client ID and Client Secret', 'infinitepay-woocommerce')
				);
        	?>
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
        $current_section = isset($_GET['if-tab']) ? $_GET['if-tab'] : 'if-credit-card';
		$this->form_fields = Settings::form_fields($current_section); 
    }

    protected function setup_properties()
    {
        $this->id                 = 'infinitepay';
        $this->icon               = apply_filters('woocommerce_offline_icon', '');
        $this->method_title       = 'InfinitePay';
        $this->method_description = __('Accept payments with InfinitePay', 'infinitepay-woocommerce');
        $this->has_fields         = true;
        $this->supports           = [ 'products' ];
    }

    //TODO: validar o sandbox para environment
    public function payment_scripts()
    {
        if (
            (
                !is_cart()
                && !is_checkout()
                && !isset($_GET['pay_for_order'])
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

        //TODO: validar o sandbox para environment
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
		//add_option('infinitepay_accesstoken', 'malkafly');

		//echo get_option('infinitepay_accesstoken');
        $parameters = array(
            'max_installments'   => $this->core_settings->max_installments,
            'amount'             => $this->get_order_total(),
            'id'                 => $this->id,
            'installments_value' => Utils::calculate_installments($this->get_order_total()),
            'enabled_creditcard' => $this->core_settings->enabled_creditcard,
            'enabled_pix'        => $this->core_settings->enabled_pix,
            'title_credit_card'  => $this->core_settings->title_credit_card,
            'title_pix'          => $this->core_settings->title_pix,
            'enabled_logo'       => $this->core_settings->enabled_logo,
            'sandbox_warning'    => (isset($this->core_settings->environment) && $this->core_settings->environment === 'sandbox') ? __('TEST MODE ENABLED. In test mode, you can use any card numbers.', 'infinitepay-woocommerce') : '',
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

        if (!isset($_POST['infinitepay_custom'])) {
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

            $is_creditcard = ( isset($_POST['infinitepay_custom']) &&
                isset($_POST['infinitepay_custom']['token']) && !empty($_POST['infinitepay_custom']['token']) &&
                isset($_POST['infinitepay_custom']['uuid']) && !empty($_POST['infinitepay_custom']['uuid']) &&
                isset($_POST['infinitepay_custom']['doc_number']) && !empty($_POST['infinitepay_custom']['doc_number']) &&
                isset($_POST['infinitepay_custom']['installments']) && !empty($_POST['infinitepay_custom']['installments']) &&
                -1 !== (int) $_POST['infinitepay_custom']['installments'] );

            $is_pix = (isset($_POST['infinitepay_custom']) && $_POST['ip_method'] == 'pix-form');

            $log_header = '[' . $order->get_id() . '] ';
            if ( $is_creditcard ) {
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
                $this->log->write_log(__FUNCTION__, $log_header . 'Misconfiguration error on plugin ');
                wc_add_notice(__($is_pix . 'Please review your payment information and try again', 'infinitepay-woocommerce'), 'error');
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
            return apply_filters('woocommerce_infinitepay_icon', plugins_url('./assets/images/logo.png', plugin_dir_path(__FILE__)));
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
            echo $this->pix_checkout_html( $order );
        }
    }

    public function pix_checkout_html( $order ) {

		if ( $order->get_payment_method() != 'infinitepix' ) {
			return '';
		}

		// Retrieve order comments
		remove_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );
		$orderComments = get_comments( array(
			'post_id' => $order->id,
			'orderby' => 'comment_ID',
			'order'   => 'DESC',
			'approve' => 'approve',
			'type'    => 'order_note',
			'number'  => 1
		) );
		add_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );

		$code = ltrim( rtrim( str_replace( "br_code: ", "", $orderComments[0]->comment_content ) ) );
		$storeUrl = $this->storeUrl;

		// Create html structure
		$html = '<div id="qrcodepixcontent" style="display: flex;flex-direction: row;justify-content: flex-start;align-items: center;background-color: #f8f8f8;border-radius: 8px; padding: 1rem;">';
		$html .= '  <img id="copy-code" style="cursor:pointer; display: initial;margin-right: 1rem;" class="wcpix-img-copy-code" src="https://gerarqrcodepix.com.br/api/v1?brcode=' . urlencode( $code ) . '"	alt="QR Code"/>';
		$html .= '  <div>';
		$html .= '    <p style="font-size: 19px;margin-bottom: 0.5rem;">Pix: <strong>R$ ' . $order->get_total() . '</strong></p>';
		$html .= '    <div style="word-wrap: break-word; max-width: 450px;">';
		$html .= '      <small>Código de transação</small><br>';
		$html .= '      <code style="font-size: 87.5%; color: #e83e8c; word-wrap: break-word;">' . $code . '</code>';
		$html .= '    </div>';
		$html .= '  </div>';
		$html .= '</div>';
		$html.=  '<p style="margin-top: 1rem;">Caso já tenha feito o pagamento, verifique se foi confirmado na página de <a href="'.$order->get_view_order_url().'">detalhes do pedido</a></p>';

		// Javascript structure to update qrcode once payment is done
		$html .= '<script type="text/javascript">';
		$html .= 'const req = new XMLHttpRequest();';
		$html .= 'var lastStatus = "";';
		$html .= 'req.onreadystatechange = function() {';
		$html .= '  if (this.readyState == 4 && this.status == 200) {';
		$html .= '    const data = JSON.parse(req.responseText);';
		$html .= '    console.log("status update", data.order_status);';
		$html .= '    lastStatus = data.order_status;'; 							
		$html .= '    if (data.order_status == "processing") {';
		$html .= '      const pixQrElement = document.getElementById("qrcodepixcontent");';
		$html .= '      pixQrElement.innerHTML = "";';
		$html .= '      pixQrElement.innerHTML = "<div><h2>Pagamento recebido</h2><p>Obrigado por comprar em nossa loja. Você pode consultar o andamento de seu pedido pela página do mesmo.</p><a href=\"'.$order->get_view_order_url().'\">Acessar pedido</a></div>";';
		$html .= '    }';
		$html .= '  }';
		$html .= '};';
		$html .= 'setTimeout(() => {';
		$html .= '  let pixInterval = setInterval(() => {';
		$html .= '    if (lastStatus == "processing") clearInterval(pixInterval);'; 
		$html .= '    req.open("GET", "'.$storeUrl.'/wp-json/wc/v3/infinitepay_order_status?order_id='.$order->id.'", true);';
		$html .= '    req.setRequestHeader("X-Requested-With", "XMLHttpRequest");';
		$html .= '    req.setRequestHeader("Access-Control-Allow-Origin", "*");';
		$html .= '    req.send(null); }, 10000);';
		$html .= '}, 1000);';
		$html .= '</script>';

		// Return html
		return $html;
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
		
		if ( $order->get_payment_method() != 'infinitepix' ) {
			return '';
		}

		// Retrieve order comments
		remove_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );
		$orderComments = get_comments( array(
			'post_id' => $order->id,
			'orderby' => 'comment_ID',
			'order'   => 'DESC',
			'approve' => 'approve',
			'type'    => 'order_note',
			'number'  => 1
		) );
		add_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );

		$code = ltrim( rtrim( str_replace( "br_code: ", "", $orderComments[0]->comment_content ) ) );

		// Create html structure
		$html = '<div id="qrcodepixcontent" style="display: flex;flex-direction: row;justify-content: flex-start;align-items: center;background-color: #f8f8f8;border-radius: 8px; padding: 1rem;">';
		$html .= '  <img id="copy-code" style="cursor:pointer; display: initial;margin-right: 1rem;" class="wcpix-img-copy-code" src="https://gerarqrcodepix.com.br/api/v1?brcode=' . urlencode( $code ) . '"	alt="QR Code"/>';
		$html .= '  <div>';
		$html .= '    <p style="font-size: 19px;margin-bottom: 0.5rem;">Pix: <strong>R$ ' . $order->get_total() . '</strong></p>';
		$html .= '    <div style="word-wrap: break-word; max-width: 450px;">';
		$html .= '      <small>Código de transação</small><br>';
		$html .= '      <code style="font-size: 87.5%; color: #e83e8c; word-wrap: break-word;">' . $code . '</code>';
		$html .= '    </div>';
		$html .= '  </div>';
		$html .= '</div>';

		// Return html
		return $html;
	}

}
