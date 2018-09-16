<?php
/**
 * Referral System for WooCommerce - WooCommerce Coupon
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

if ( ! class_exists( 'ThanksToIT\RSWC\WC_Coupon' ) ) {

	class WC_Coupon {
		public static function mask_coupon_name_with_referral_code( $label, \WC_Coupon $coupon ) {
			$referral_code = new Referral_Coupon();
			$label         = $referral_code->mask_coupon_name( $label, $coupon );
			return $label;
		}

		public function remove_coupon_html_if_zero_discount( $html, \WC_Coupon $coupon ) {
			$referral_code = new Referral_Coupon();
			$html          = $referral_code->remove_coupon_html_if_zero_discount( $html, $coupon );
			return $html;
		}
	}
}