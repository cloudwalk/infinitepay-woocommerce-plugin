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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters('active_plugins', get_option( 'active_plugins')))) {
	return;
}

// Woocommerce setup here
add_action('plugins_loaded', 'wc_infiintepay_init', 11);
add_filter('woocommerce_payment_gateways', 'wc_infinitepay_add_to_gateway');
add_filter('plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_infinitepay_plugin_links');
add_filter('woocommerce_rest_api_get_rest_namespaces', 'woo_custom_api');

/**
 * Custom API Controller
 */
function woo_custom_api($controllers) {
	require_once dirname(__FILE__) . '/includes/class-wc-rest-custom-controller.php';
	$controllers['wc/v3']['custom'] = 'WC_REST_Custom_Controller';
	return $controllers;
}

/**
 * Require plugin files
 */
function wc_infiintepay_init() {
	if (class_exists( 'WC_Payment_Gateway' )) {

		// Credit card and its contants method
		require_once dirname( __FILE__ ) . '/includes/class-wc-wooinfinitepay-log.php';
		require_once dirname(__FILE__) . '/includes/class-wc-wooinfinitepay-init.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-wooinfinitepay-constants.php';

		// PIX mwthod
		require_once dirname(__FILE__) . '/includes/class-wc-wooinfinitepix-init.php';

		// Plugin methods (sstart class)
		WC_InfinitePay_Module::update_plugin_version();
		WC_InfinitePix_Module::update_plugin_version();
	}
}

/**
 * Add pix and credit card payment gateways
 */
function wc_infinitepay_add_to_gateway($gateways) {
	// Append payment methods
	$gateways[] = 'WC_InfinitePay_Module';
	array_push($gateways, 'WC_InfinitePix_Module');

	// Return plugin avalable methods
	return $gateways;
}

/**
 * Plugin URL
 */
function wc_infinitepay_plugin_links($links) {
	$plugins_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=infinitepay' ) . '">' . __( 'Configure', 'infinitepay-woocommerce' ) . '</a>'
	);
	return array_merge($plugins_links, $links);
}
