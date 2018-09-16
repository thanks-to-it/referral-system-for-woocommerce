<?php
/**
 * Referral System for WooCommerce - WooCommerce Session
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

if ( ! class_exists( 'ThanksToIT\RSWC\WC_Session' ) ) {

	class WC_Session {
		public static function save_referral_code_data_from_query_string() {
			$referral_code = new Referral_Coupon();
			$referral_code->save_referral_code_data_in_wc_session();
		}
	}
}