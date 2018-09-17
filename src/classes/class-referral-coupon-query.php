<?php
/**
 * Referral System for WooCommerce - Referral_Coupon_Query
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC;

use ThanksToIT\RSWC\Encryption;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\Coupon\Referral_Coupon_Query' ) ) {

	class Referral_Coupon_Query {
		public function get_referral_coupons() {
			$referral_coupon = new Referral_Coupon();

			$the_query = new \WP_Query( array(
				'post_type'      => 'shop_coupon',
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
				'meta_query'     => array(
					array(
						'key'     => $referral_coupon->postmeta['referral_enable'],
						'value'   => 'yes',
						'compare' => '=',
					),
				),
			) );

			return $the_query;
		}
	}
}