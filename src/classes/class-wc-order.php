<?php
/**
 * Referral System for WooCommerce - WooCommerce Session
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\WC_Order' ) ) {
	class WC_Order {
		public static function save_referral_code_data_on_order_creation( $order, $data ) {
			$referral_code = new Referral_Coupon();
			$referral_code->save_referral_code_data_on_order_creation( $order, $data );
		}

		public static function show_referral_code_data_on_admin_order( $order ) {
			$referral_code = new Referral_Coupon();
			$referral_code->show_referral_code_data_on_admin_order( $order );
		}

		public static function create_commission_when_order_is_completed( $order_id, $status_from, $status_to ) {
			if ( $status_to != 'completed' ) {
				return;
			}
			$commission = new Commission();
			$commission->create_commission_when_order_is_completed( $order_id, $status_from, $status_to );
		}
	}
}


