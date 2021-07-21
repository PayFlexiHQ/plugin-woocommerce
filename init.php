<?php
/*
    Plugin Name: PayFlexi Flexible Payment Plans for WooCommerce
    Plugin URI: https://developers.payflexi.co
    Description: PayFlexi flexible payment plans plugin for Woocommerce is a payment option that allows you to accept automated installment or one-time payments from your customers.
    Version: 1.3.0
    Author: PayFlexi
    Author URI: https://payflexi.co
    License: GPLv2 or later
    License URI: http://www.gnu.org/licenses/gpl-2.0.txt
    WC requires at least: 3.8.0
    WC tested up to: 5.5.1
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define constants.
if ( defined('PAYFLEXI_FLEXIBLE_CHECKOUT_VERSION')) {
	return;
} else {
	define('PAYFLEXI_FLEXIBLE_CHECKOUT_VERSION', '1.3.0');
}

if (!defined('PAYFLEXI_FLEXIBLE_CHECKOUT_DIR')) {
	define('PAYFLEXI_FLEXIBLE_CHECKOUT_DIR', plugin_dir_path( __FILE__ ));
}

if (!defined('PAYFLEXI_FLEXIBLE_CHECKOUT_FILE')) {
	define( 'PAYFLEXI_FLEXIBLE_CHECKOUT_FILE', __FILE__ );
}

if (!defined('PAYFLEXI_FLEXIBLE_CHECKOUT_URL')) {
	define( 'PAYFLEXI_FLEXIBLE_CHECKOUT_URL', plugins_url( '/', __FILE__ ));
}

if (!defined('PAYFLEXI_FLEXIBLE_CHECKOUT_INC')) {
	define('PAYFLEXI_FLEXIBLE_CHECKOUT_INC', PAYFLEXI_FLEXIBLE_CHECKOUT_DIR . '/includes/' );
}

if (!defined('PAYFLEXI_FLEXIBLE_CHECKOUT_INIT')) {
	define('PAYFLEXI_FLEXIBLE_CHECKOUT_INIT', plugin_basename( __FILE__ ) );
}

if (!defined('PAYFLEXI_FLEXIBLE_CHECKOUT_ASSETS_URL')) {
	define('PAYFLEXI_FLEXIBLE_CHECKOUT_ASSETS_URL', PAYFLEXI_FLEXIBLE_CHECKOUT_URL . 'assets' );
}
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-payflexi-flexible-checkout.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.1.0
 */
function run_payflexi_flexible_checkout() {

    $plugin = new Payflexi_Flexible_Checkout();
    $plugin->run();
}

function woocommerce_payflexi_flexible_checkout_init() {
	run_payflexi_flexible_checkout();
}

add_action( 'plugins_loaded', 'woocommerce_payflexi_flexible_checkout_init');

