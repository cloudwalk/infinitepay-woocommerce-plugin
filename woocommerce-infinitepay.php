<?php
/*
 * Plugin Name: InfinitePay for WooCommerce
 * Description: Configure the payment options and accept payments with cards.
 * Version: 2.0.17
 * Author: Infinite Pay
 * Author URI: https://infinitepay.io/
 * Text Domain: infinitepay-woocommerce
 * Domain Path: /i18n/languages/
 * WC requires at least: 5.5.2
 * WC tested up to: 6.2.0
 *
 * @package InfinitePay
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters('active_plugins', get_option( 'active_plugins')))) {
	return;
}

add_action( 'plugins_loaded', 'wc_infintepay_init',  0 );
add_filter( 'woocommerce_payment_gateways', 'wc_infinitepay_add_to_gateway');
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_infinitepay_plugin_links');
add_filter( 'woocommerce_rest_api_get_rest_namespaces', 'woo_custom_api');

function shutdown_notice() {
    ?>
    <div class="notice notice-warning is-dismissible">
        <h3>Descontinuação do Plugin InfinitePay - [Ação necessária]</h3>
		<p>Importante: Estamos <strong>descontinuando</strong> o plugin InfinitePay for WooCommerce até o final de agosto.</p>
		<p>Recomendamos a desinstalação imediata do plugin "InfinitePay for WooCommerce" para evitar inconvenientes.</p>
		<p>Para qualquer dúvida ou questão adicional, não hesite em nos contatar através de <a href="mailto:ajuda@infinitepay.io">ajuda@infinitepay.io</a>.</p>
    </div>
    <?php
}
add_action( 'admin_notices', 'shutdown_notice' );


function welcome_render() {
	include __DIR__ . '/templates/welcome/welcome.php';
}


function woo_custom_api($controllers) {
	require_once dirname(__FILE__) . '/includes/class-wc-rest-custom-controller.php';
	$controllers['wc/v3']['custom'] = 'WC_REST_Custom_Controller';
	return $controllers;
}


function wc_infintepay_init() {
	if (class_exists( 'WC_Payment_Gateway' )) {
		require_once __DIR__ . '/vendor/autoload.php';
	}
}


function plugin_activate() {
	add_option('ip_activation_redirect', true);
}

function ip_redirect() {
    if (get_option('ip_activation_redirect', false)) {
        delete_option('ip_activation_redirect');
        exit( wp_redirect( 'admin.php?page=ip-welcome' ) );
    }
}


function wc_infinitepay_add_to_gateway( $gateways ) {
	$gateways[] = 'Woocommerce\InfinitePay\InfinitePayCore';
	return $gateways;
}

function wc_infinitepay_plugin_links( $links ) {
	$plugins_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=infinitepay' ) . '">' . __( 'Configure', 'infinitepay-woocommerce' ) . '</a>',
		'<a href="https://ajuda.infinitepay.io/pt-BR/collections/3609678-e-commerce" target="_blank">Ajuda</a>'
	);
	return array_merge( $plugins_links, $links );
}
