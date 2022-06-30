<?php
/*
 * Part of Woo InfinitePay Module
 * Author - InfinitePay
 * Developer
 * Copyright - Copyright(c) CloudWalk [https://www.cloudwalk.io]
 * License - https://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 *
 *  @package InfinitePay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_InfinitePay_Log {
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
			$this->log = new WC_Logger();
		} else {
			$this->log = WC_InfinitePay_Module::woocommerce_instance()->logger();
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