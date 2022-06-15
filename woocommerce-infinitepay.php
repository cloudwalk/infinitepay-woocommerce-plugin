<?php
/*
 * Plugin Name: InfinitePay payments for WooCommerce
 * Description: Configure the payment options and accept payments with cards.
 * Version: 1.1.6
 * Author: Infinite Pay
 * Author URI: https://infinitepay.io/
 * Text Domain: infinitepay-woocommerce
 * Domain Path: /i18n/languages/
 * WC requires at least: 5.7.0
 * WC tested up to: 7.0.0
 *
 * @package InfinitePay
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

add_action( 'plugins_loaded', 'wc_infiintepay_init', 11 );
add_filter( 'woocommerce_payment_gateways', 'wc_infinitepay_add_to_gateway' );
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_infinitepay_plugin_links' );

function wc_infiintepay_init() {
	if ( class_exists( 'WC_Payment_Gateway' ) ) {
		require_once dirname( __FILE__ ) . '/includes/class-wc-wooinfinitepay-log.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-wooinfinitepay-init.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-wooinfinitepay-constants.php';
		WC_InfinitePay_Module::update_plugin_version();
	}
}

function wc_infinitepay_add_to_gateway( $gateways ) {
	$gateways[] = 'WC_InfinitePay_Module';

	return $gateways;
}


function wc_infinitepay_plugin_links( $links ) {
	$plugins_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=infinitepay' ) . '">' . __( 'Configure', 'infinitepay-woocommerce' ) . '</a>'
	);

	return array_merge( $plugins_links, $links );
}