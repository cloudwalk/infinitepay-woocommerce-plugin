<?php
namespace Woocommerce\InfinitePay\Controller;

use Woocommerce\InfinitePay\Helper\Constants;
use Woocommerce\InfinitePay\Helper\Log;
use Woocommerce\InfinitePay\Fields\ConfigurationsFields;
use Woocommerce\InfinitePay\Fields\CredentialsFields;
use Woocommerce\InfinitePay\Fields\CreditCardFields;
use Woocommerce\InfinitePay\Fields\PIXFields;

if (!function_exists('add_action')) {
    exit(0);
}

class Settings
{
    public $title;
    public $enabled_logo;

    public $title_credit_card;
    public $instructions;
    public $max_installments;
    public $max_installments_free;
    public $enabled_creditcard;

    public $environment;
    public $client_id;
    public $client_secret;

    public $enabled_pix;
    public $title_pix;
    public $instructions_pix;
    public $discount_pix;
    public $min_value_pix;

    public $icon;

    public function __construct($wc_payments = null)
    {
        $wc_payments->init_settings();

        $this->title        = 'InfinitePay';
        $this->enabled_logo = sanitize_text_field($wc_payments->get_option('enabled_logo', 'yes'));
        $this->enabled_log  = sanitize_text_field($wc_payments->get_option('enabled_log', 'yes'));

        $this->instructions          = sanitize_textarea_field($wc_payments->get_option('instructions'));
        $this->max_installments      = sanitize_key($wc_payments->get_option('max_installments', 12));
        $this->max_installments_free = sanitize_key($wc_payments->get_option('max_installments_free', 12));
        $this->enabled_creditcard    = sanitize_key($wc_payments->get_option('enabled_creditcard'));

        $this->environment   = sanitize_key($wc_payments->get_option('environment', 'sandbox'));
        $this->client_id     = $wc_payments->get_option('client_id');
        $this->client_secret = $wc_payments->get_option('client_secret');

        $this->enabled_pix   = sanitize_key($wc_payments->get_option('enabled_pix'));
        $this->instructions_pix  = sanitize_text_field($wc_payments->get_option('instructions_pix'));
        $this->discount_pix  = sanitize_key($wc_payments->get_option('discount_pix', 0));
        $this->min_value_pix = sanitize_key($wc_payments->get_option('min_value_pix', 0));
    }

	public static function form_fields($current_section)
    {
        $fiels = CreditCardFields::fields();

        switch ($current_section) {
            case 'if-credentials':
				$fiels = CredentialsFields::fields();
                break;
            case 'if-credit-card':
				$fiels = CreditCardFields::fields();
                break;
            case 'if-pix':
				$fiels = PIXFields::fields();
                break;
            case 'if-settings':
				$fiels = ConfigurationsFields::fields();
                break;
        }

        return $fiels;
    }

    public static function build_submenu()
    {

        ob_start();

        echo '<ul class="subsubsub">';

        $sections = array(
            'if-credentials' => __('Credentials', 'infinitepay-woocommerce'),
            'if-credit-card' => __('Credit Card', 'infinitepay-woocommerce'),
            'if-pix'         => __('PIX', 'infinitepay-woocommerce'),
            'if-settings'    => __('Settings', 'infinitepay-woocommerce'),
        );

        $array_keys      = array_keys($sections);
        $current_section = isset($_GET['if-tab']) ? $_GET['if-tab'] : 'if-credit-card';

        foreach ($sections as $id => $label) {
            $link = admin_url('admin.php?page=wc-settings&tab=checkout&section=infinitepay&if-tab=' . sanitize_title($id));
            $pipe = (end($array_keys) === $id ? '' : '|');
            echo '<li><a href="' . $link . '" class="' . ($current_section === $id ? 'current' : '') . '">' . $label . '</a> ' . $pipe . ' </li>';
        }

        echo '</ul><br class="clear" />';

        $content = ob_get_contents();

        ob_end_clean();

        return $content;
    }

}