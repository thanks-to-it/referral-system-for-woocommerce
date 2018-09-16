<?php
/**
 * Referral System for WooCommerce - Admin WooCommerce Coupon
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC\Admin;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\Admin\WC_Coupon' ) ) {

	class WC_Coupon {
		public static function save_referral_tab_data( $post_id = null ) {
			$tab = new Referral_Coupon_Tab();
			$tab->save_tab_data( $post_id );
		}

		public static function add_referral_tab_panel() {
			$tab = new Referral_Coupon_Tab();
			$tab->add_tab_panel();
		}

		public static function add_coupon_referral_tab( $tabs ) {
			$tab = new Referral_Coupon_Tab();
			$tabs = $tab->add_tab($tabs);
			return $tabs;
		}
	}
}