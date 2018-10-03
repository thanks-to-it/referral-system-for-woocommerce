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

		public $usermeta = array(
			'ip' => '_trswc_ip',
		);

		public static function is_current_user_referrer() {
			$current_user = wp_get_current_user();
			if ( in_array( self::$role_referrer, $current_user->roles ) ) {
				return true;
			} else {
				return false;
			}
		}

		public function get_cookie() {
			$authenticity = new Authenticity();
			if ( isset( $_COOKIE[ $authenticity->cookies['referrer_cookie'] ] ) ) {
				return $_COOKIE[ $authenticity->cookies['referrer_cookie'] ];
			} else {
				return false;
			}
		}

		public function get_referrer_id_from_cookie( $cookie ) {
			$hashids = new \Hashids\Hashids( Encryption::get_salt(), 6, Encryption::get_alphabet() );
			$numbers = $hashids->decode( $cookie );
			if ( is_array( $numbers ) && count( $numbers ) == 1 ) {
				return $numbers[0];
			} else {
				return false;
			}
		}

		public function save_cookie( $user_login, \WP_User $user ) {
			if ( ! self::is_user_referrer( $user->ID ) ) {
				return;
			}
			$authenticity = new Authenticity();
			$hashids      = new \Hashids\Hashids( Encryption::get_salt(), 6, Encryption::get_alphabet() );
			$response     = setcookie( $authenticity->cookies['referrer_cookie'], $hashids->encode( $user->ID ), time() + ( 1 * YEAR_IN_SECONDS ), COOKIEPATH, COOKIE_DOMAIN, is_ssl() );
		}

		function save_ip( $user_login, $user ) {
			if ( ! self::is_user_referrer( $user->ID ) ) {
				return;
			}
			update_user_meta( $user->ID, $this->usermeta['ip'], $this->get_ip() );
		}

		function get_ip() {
			foreach ( array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' ) as $key ) {
				if ( array_key_exists( $key, $_SERVER ) === true ) {
					foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) {
						if ( filter_var( $ip, FILTER_VALIDATE_IP ) !== false ) {
							return $ip;
						}
					}
				}
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
			//'edit_alg_mpwc_commissions' => true,
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