<?php
/**
 * Referral System for WooCommerce - Admin Menu
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC\Admin;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\Admin\Admin_Menu' ) ) {

	class Admin_Menu {

		public static function add_referral_page() {
			add_menu_page(
				__( 'Referral', 'referral-system-for-woocommerce' ),
				__( 'Referral', 'referral-system-for-woocommerce' ),
				'manage_options',
				//'edit.php?post_type=trswc-commission',
				'edit.php?trswc=trswc',
				'',
				'dashicons-money',
				40
			);
		}
	}
}

