<?php
/**
 * Referral System for WooCommerce - WooCommerce My Account
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC;




if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\WC_My_Account' ) ) {

	class WC_My_Account {
		public static function add_referral_codes_tab_endpoint() {
			$referral_codes_tab = new Referral_Codes_Tab();
			$referral_codes_tab->add_endpoint();
		}

		public static function add_referral_codes_tab_query_vars( $vars ) {
			$referral_codes_tab = new Referral_Codes_Tab();
			$vars               = $referral_codes_tab->add_query_vars( $vars );
			return $vars;
		}

		public static function add_referral_codes_tab_menu_item( $items ) {
			$referral_codes_tab = new Referral_Codes_Tab();
			$items              = $referral_codes_tab->add_menu_item( $items );
			return $items;
		}

		public static function add_referral_codes_tab_content() {
			$referral_codes_tab = new Referral_Codes_Tab();
			$referral_codes_tab->add_content();
		}
	}
}