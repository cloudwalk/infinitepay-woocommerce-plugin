<?php
/*
 * Plugin Name: InfinitePay payments for WooCommerce
 * Description: Configure the payment options and accept payments with cards.
 * Version: 1.1.9
 * Author: Infinite Pay
 * Author URI: https://infinitepay.io/
 * Text Domain: infinitepay-woocommerce
 * Domain Path: /i18n/languages/
 * WC requires at least: 5.5.2
 * WC tested up to: 6.2.0
 *
 * @package InfinitePay
 */

//TODO: REMOVER opcache_reset() DA PR
/*
  _______ ____  _____   ____      _____                                                _          _ _       _                   _           _           
 |__   __/ __ \|  __ \ / __ \ _  |  __ \                                              | |        | (_)     | |                 | |         (_)          
    | | | |  | | |  | | |  | (_) | |__) |___ _ __ ___   _____   _____ _ __    ___  ___| |_ __ _  | |_ _ __ | |__   __ _    __ _| |__   __ _ ___  _____  
    | | | |  | | |  | | |  | |   |  _  // _ \ '_ ` _ \ / _ \ \ / / _ \ '__|  / _ \/ __| __/ _` | | | | '_ \| '_ \ / _` |  / _` | '_ \ / _` | \ \/ / _ \ 
    | | | |__| | |__| | |__| |_  | | \ \  __/ | | | | | (_) \ V /  __/ |    |  __/\__ \ || (_| | | | | | | | | | | (_| | | (_| | |_) | (_| | |>  < (_) |
    |_|  \____/|_____/ \____/(_) |_|  \_\___|_| |_| |_|\___/ \_/ \___|_|     \___||___/\__\__,_| |_|_|_| |_|_| |_|\__,_|  \__,_|_.__/ \__,_|_/_/\_\___/                                                                                                                                                      
*/
opcache_reset();

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters('active_plugins', get_option( 'active_plugins')))) {
	return;
}

add_action('plugins_loaded', 'wc_infintepay_init',  0 );
add_filter('woocommerce_payment_gateways', 'wc_infinitepay_add_to_gateway');
add_filter('plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_infinitepay_plugin_links');
add_filter('woocommerce_rest_api_get_rest_namespaces', 'woo_custom_api');


function woo_custom_api($controllers) {
	require_once dirname(__FILE__) . '/includes/class-wc-rest-custom-controller.php';
	$controllers['wc/v3']['custom'] = 'WC_REST_Custom_Controller';
	return $controllers;
}


function wc_infintepay_init() {
	if (class_exists( 'WC_Payment_Gateway' )) {

		// require_once dirname( __FILE__ ) . '/includes/class-wc-wooinfinitepay-log.php';
		// require_once dirname( __FILE__ ) . '/includes/class-wc-wooinfinitepay-init.php';
		// require_once dirname( __FILE__ ) . '/includes/class-wc-wooinfinitepay-constants.php';
		//WC_InfinitePay_Module::update_plugin_version();

		require_once __DIR__ . '/vendor/autoload.php';
		new Woocommerce\InfinitePay\InfinitePayCore;
	}
}


function wc_infinitepay_add_to_gateway( $gateways ) {
	$gateways[] = 'Woocommerce\InfinitePay\InfinitePayCore';
	return $gateways;
}

function wc_infinitepay_plugin_links( $links ) {
	$plugins_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=infinitepay' ) . '">' . __( 'Configure', 'infinitepay-woocommerce' ) . '</a>'
	);
	//TODO: Adicionar link de Support
	return array_merge( $plugins_links, $links );
}
