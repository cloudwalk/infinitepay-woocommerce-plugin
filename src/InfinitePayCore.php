<?php
namespace Woocommerce\InfinitePay;

if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\InfinitePay\Controller\Settings;
use Woocommerce\InfinitePay\Controller\Checkout;
use Woocommerce\InfinitePay\Helper\ApiInfinitePay;
use Woocommerce\InfinitePay\Helper\Log;
use Woocommerce\InfinitePay\Helper\Util;

class InfinitePayCore extends \WC_Payment_Gateway
{
 
	const TEXT_DOMAIN     = 'infinitepay-woocommerce';
    const SLUG            = 'infinitepay';
    const VERSION         = '1.1.9';
    const MIN_PHP         = 5.6;
    const API_IP_BASE_URL = 'https://api.infinitepay.io';

	public $core_settings;
	public $api;

    protected $infinite_pay_tax = [
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


		$this->api = new ApiInfinitePay($this->core_settings->environment);

		$this->api->auth($this->core_settings->client_id, $this->core_settings->client_secret);
        
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
        if (version_compare(self::VERSION, $old_version, '>')) {
            update_option('_ip_version', self::VERSION, true);
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

    private function calculate_installments()
    {
        $amount             = $this->get_order_total();
        $installments_value = [];
        for (
            $i = 1;
            $i <= (int) $this->core_settings->max_installments;
            $i++
        ) {
            $tax      = !((int) $this->core_settings->max_installments_free >= $i) && $i > 1;
            $interest = 1;
            if ($tax) {
                $interest = $this->infinite_pay_tax[$i - 1] / 100;
            }
            $value                = !$tax ? $amount / $i : $amount * ($interest / (1 - pow(1 + $interest, -$i)));
            $installments_value[] = array(
                'value'    => $value,
                'interest' => $tax,
            );
        }

        return $installments_value;
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

		$card_tokenization = $this->api->auth($this->core_settings->client_id, $this->core_settings->client_secret, 'card_tokenization');

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
            'installments_value' => $this->calculate_installments(),
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

			echo $_POST['infinitepay_custom']['token'] . $_POST['infinitepay_custom']['doc_number']  . $_POST['infinitepay_custom']['uuid'];
		die;

			$checkout = new Checkout($order);

            //TODO: primeiro if, validar se vem do pix ou não  $_POST['ip_method'] == 'pix-form'
            $is_creditcard = (isset($_POST['infinitepay_custom']) &&
                isset($_POST['infinitepay_custom']['token']) && !empty($_POST['infinitepay_custom']['token']) &&
                isset($_POST['infinitepay_custom']['uuid']) && !empty($_POST['infinitepay_custom']['uuid']) &&
                isset($_POST['infinitepay_custom']['doc_number']) && !empty($_POST['infinitepay_custom']['doc_number']) &&
                isset($_POST['infinitepay_custom']['installments']) && !empty($_POST['infinitepay_custom']['installments']) &&
                -1 !== (int) $_POST['infinitepay_custom']['installments']);

            $is_pix = (isset($_POST['infinitepay_custom']) && $_POST['ip_method'] == 'pix-form');

            $log_header = '[' . $order->get_id() . '] ';
            if ($is_creditcard) {
                
				$checkout->process_credit_card();


            } else {
                $this->log->write_log(__FUNCTION__, $log_header . 'Misconfiguration error on plugin ');
                wc_add_notice(__($is_pix . 'Please review your card information and try again', 'infinitepay-woocommerce'), 'error');
            }
        } catch (Exception $ex) {
            $this->log->write_log(__FUNCTION__, 'Caught exception: ' . $ex->getMessage());
        }
    }

    public function change_payment_complete_order_status($status, $order_id = 0, $order = false)
    {
        if ($order && $order->get_payment_method() === 'infinitepay') {
            $status = 'processing';
        }

        return $status;
    }

    public function get_ip_icon()
    {
        if ($this->core_settings->enabled_logo == 'yes') {
            return apply_filters('woocommerce_infinitepay_icon', plugins_url('./assets/images/logo.png', plugin_dir_path(__FILE__)));
        }
    }

    public function thank_you_page()
    {
        if (!empty($this->core_settings->instructions)) {
            echo wpautop(wptexturize(esc_html($this->core_settings->instructions)));
        }
    }

    public function email_instructions($order, $sent_to_admin, $plain_text = false)
    {
        if (
            $this->core_settings->instructions
            && !$sent_to_admin
            && $this->id === $order->payment_method
        ) {
            echo wp_kses_post(wpautop(wptexturize(esc_html($this->core_settings->instructions))) . PHP_EOL);
        }
    }

}
