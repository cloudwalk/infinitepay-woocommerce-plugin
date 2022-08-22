<?php
namespace Woocommerce\InfinitePay\Helper;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\InfinitePay\InfinitePayCore;

class Log
{

	public $log;
	public $id;

	public function __construct( $payment = null ) {
		if ( ! empty( $payment ) ) {
			$this->id = get_class( $payment );
		}

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
		$this->log->add( $this->id, '[' . $function . ']: ' . $message );
	}

	public function set_id( $id ) {
		$this->id = $id;
	}
}