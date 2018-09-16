<?php
/**
 * Referral System for WooCommerce - WooCommerce Cart
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC;

use ThanksToIT\RSWC\Referral_Coupon;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\WC_Cart' ) ) {

	class WC_Cart {
		public static function apply_referral_code_discount_programmatically() {
			$referral_code = new Referral_Coupon();
			$referral_code->apply_discount_programmatically();
		}
	}
}