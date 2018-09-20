<?php
/**
 * Referral System for WooCommerce - Referrer User Role
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\Referrer' ) ) {

	class Referrer {

		public static $role_referrer = 'trswc_referrer';
		public static $role_referrer_pending = 'trswc_referrer_pending';
		public static $role_referrer_rejected = 'trswc_referrer_rejected';

		public static function is_current_user_referrer() {
			$current_user = wp_get_current_user();
			if ( in_array( self::$role_referrer, $current_user->roles ) ) {
				return true;
			} else {
				return false;
			}
		}

		public static function is_user_referrer( $user_id ) {
			$current_user = get_user_by( 'ID', $user_id );
			if ( in_array( self::$role_referrer, $current_user->roles ) ) {
				return true;
			} else {
				return false;
			}
		}

		public $user_caps = array(
			"read"                      => true,
			"edit_product"              => true,
			"read_product"              => true,
			"delete_product"            => true,
			"edit_products"             => true,
			"delete_products"           => true,
			"delete_published_products" => true,
			"edit_published_products"   => true,
			"assign_product_terms"      => true,
			'level_0'                   => true,
			'edit_alg_mpwc_commissions' => true,
			"edit_shop_orders"          => false,
			'edit_others_shop_orders'   => false,
			'read_shop_order'           => false
		);

		public function add_role( $role, $role_name ) {
			if ( get_role( $role ) ) {
				remove_role( $role );
			}
			add_role( $role, $role_name, $this->user_caps );
		}

		public function add_roles() {
			$this->add_role( self::$role_referrer, sanitize_text_field( __( 'Referrer', 'referral-system-for-woocommerce' ) ) );
			$this->add_role( self::$role_referrer_pending, sanitize_text_field( __( 'Referrer - Pending', 'referral-system-for-woocommerce' ) ) );
			$this->add_role( self::$role_referrer_rejected, sanitize_text_field( __( 'Referrer - Rejected', 'referral-system-for-woocommerce' ) ) );
		}
	}
}