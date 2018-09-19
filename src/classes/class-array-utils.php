<?php
/**
 * Referral System for WooCommerce - Array Utils
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\Array_Utils' ) ) {

	class Array_Utils {

		public static function array_splice_assoc(&$input, $offset, $length, $replacement) {
			$replacement = (array) $replacement;
			$key_indices = array_flip(array_keys($input));
			if (isset($input[$offset]) && is_string($offset)) {
				$offset = $key_indices[$offset];
			}
			if (isset($input[$length]) && is_string($length)) {
				$length = $key_indices[$length] - $offset;
			}

			$input = array_slice($input, 0, $offset, TRUE)
			         + $replacement
			         + array_slice($input, $offset + $length, NULL, TRUE);
		}
	}
}