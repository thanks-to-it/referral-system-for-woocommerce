<?php
/**
 * Plugin Name: Referral System for WooCommerce
 * Description: Referral System for WooCommerce
 * Version: 1.0.0
 * Author: Thanks to IT
 * Author URI: https://github.com/thanks-to-it
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: referral-system-for-woocommerce
 * Domain Path: /languages
 * WC requires at least: 3.0.0
 * WC tested up to: 3.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

require_once "vendor/autoload.php";

// Check if WooCommerce is active
$plugin = 'woocommerce/woocommerce.php';
if (
	! in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) &&
	! ( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
) {
	return;
}

$plugin = \ThanksToIT\RSWC\Core::instance();
$plugin->setup( array(
	'path' => __FILE__
) );
if ( true === apply_filters( 'trswc_init', true ) ) {
	$plugin->init();
}
