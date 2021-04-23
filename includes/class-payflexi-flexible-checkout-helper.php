<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


if ( ! class_exists( 'PayFlexi_Flexible_Checkout_Helper' ) ) {
	/**
	 * Class PayFlexi_Flexible_Checkout_Helper
	 */
	class PayFlexi_Flexible_Checkout_Helper {

		public static function is_ssl_enable() {
			if (is_ssl() || get_option('woocommerce_force_ssl_checkout') == 'yes') {
				return true;
			} else {
				return false;
			}
		}

		public function payflexi_flexible_checkout_is_available() {
			$is_enable = $this->payflexi_checkout_get_option('enabled');
			if (isset($is_enable) && $is_enable == 'yes') {
				return true;
			} else {
				return false;
			}
		}

		public function payflexi_checkout_get_option($option_name) {
			$woocommerce_payflexi_flexible_checkout_settings = get_option('woocommerce_payflexi-flexible-checkout_settings');
			if (isset($woocommerce_payflexi_flexible_checkout_settings[$option_name]) && !empty($woocommerce_payflexi_flexible_checkout_settings[$option_name])) {
				return $woocommerce_payflexi_flexible_checkout_settings[$option_name];
			} else {
				return false;
			}
		}

		public function payflexi_flexible_checkout_public_key(){
			$env  = $this->payflexi_checkout_get_option( 'env' ) != '' ? $this->payflexi_checkout_get_option( 'env' ) : 'test';
			$public_key  = $this->payflexi_checkout_get_option( $env . '_api_public_key' );
			return $public_key;
		}

		public function payflexi_flexible_checkout_gateway(){
			$gateway  = $this->payflexi_checkout_get_option('gateway');
			return $gateway;
		}

    }
}

