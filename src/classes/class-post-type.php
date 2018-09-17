<?php
/**
 * Referral System for WooCommerce - PostType
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\Post_Type' ) ) {

	class Post_Type {
		public static function add_commission_post_type() {
			$commission = new Commission();
			$commission->register_post_type();
		}
	}
}