<?php
namespace Woocommerce\InfinitePay\Helper;

use Woocommerce\InfinitePay\Helper\Constants;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\InfinitePay\InfinitePayCore;

class Log
{

	public $log;
	public $id;

	public function __construct() {
		$this->id = Constants::SLUG;
		return $this->init_log();
	}

	public function init_log() {
		if ( class_exists( 'WC_Logger' ) ) {
			$this->log = new \WC_Logger();
		} else {
			$this->log = InfinitePayCore::woocommerce_instance()->logger();
		}
		return $this->log;
	}

	public function write_log( $function, $message ) {
		$options = get_option('woocommerce_infinitepay_settings');
		
		if (isset($options['enabled_log']) && $options['enabled_log'] == 'yes') {
			$this->log->add( $this->id, '[' . $function . ']: ' . $message );
			$this->log->add( $this->id, '=============================================================================================');
		}
	}

	public function set_id( $id ) {
		$this->id = $id;
	}
}