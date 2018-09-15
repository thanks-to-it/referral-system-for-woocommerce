<?php
/**
 * Referral System for WooCommerce - Encryption
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\Encryption' ) ) {

	class Encryption {
		public static $salt;

		/**
		 * Gets salt to be userd on hashids library
		 *
		 * @version 1.0.0
		 * @since 1.0.0
		 * @return int|string
		 */
		public static function get_salt() {
			if ( empty( self::$salt ) ) {
				$wp_salt    = SECURE_AUTH_SALT;
				$salt       = empty( $wp_salt ) ? SECURE_AUTH_SALT : ip2long( $_SERVER['SERVER_ADDR'] );
				self::$salt = $salt;
			}

			return self::$salt;
		}

		/**
		 * Gets alphabet to be used on hashids library
		 *
		 * @version 1.0.0
		 * @since 1.0.0
		 * @return string
		 */
		public static function get_alphabet() {
			return 'abcdefghijklmnopqrstuvwxyz1234567890';
		}
	}
}