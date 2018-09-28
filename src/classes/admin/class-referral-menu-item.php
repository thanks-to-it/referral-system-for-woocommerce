<?php
/**
 * Referral System for WooCommerce - Referral_Menu_Item
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC\Admin;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\Admin\Referral_Menu_Item' ) ) {

	class Referral_Menu_Item {
		public function add_referral_page() {
			add_menu_page(
				__( 'Referrals', 'referral-system-for-woocommerce' ),
				__( 'Referrals', 'referral-system-for-woocommerce' ),
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