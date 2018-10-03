<?php
/**
 * Referral System for WooCommerce - Template
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\Template' ) ) {

	class Template {
		/**
		 * Locate a template and return the path for inclusion.
		 *
		 * This is the load order:
		 *
		 * yourtheme/$template_path/$template_name
		 * yourtheme/$template_name
		 * $default_path/$template_name
		 *
		 * @access public
		 * @param string $template_name Template name.
		 * @param string $template_path Template path. (default: '').
		 * @param string $default_path  Default path. (default: '').
		 * @return string
		 */
		public static function locate_template( $template_name, $template_path = '', $default_path = '' ) {
			if ( ! $template_path ) {
				$template_path = apply_filters( 'trswc_template_path', 'referral-system-for-woocommerce/' );
			}

			if ( ! $default_path ) {
				$plugin = Core::instance();
				$default_path = untrailingslashit( plugin_dir_path( $plugin->plugin_info['path'] ) ) . '/src/templates/';
			}

			// Look within passed path within the theme - this is priority.
			$template = locate_template(
				array(
					trailingslashit( $template_path ) . $template_name,
					$template_name,
				)
			);

			// Get default template/.
			if ( ! $template ) {
				$template = $default_path . $template_name;
			}

			// Return what we found.
			return apply_filters( 'trswc_locate_template', $template, $template_name, $template_path );
		}

		public static function get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ){
			if ( ! empty( $args ) && is_array( $args ) ) {
				extract( $args ); // @codingStandardsIgnoreLine
			}

			$located = self::locate_template( $template_name, $template_path, $default_path );

			if ( ! file_exists( $located ) ) {
				/* translators: %s template */
				wc_doing_it_wrong( __FUNCTION__, sprintf( __( '%s does not exist.', 'woocommerce' ), '<code>' . $located . '</code>' ), '2.1' );
				return;
			}

			// Allow 3rd party plugin filter template file from their plugin.
			$located = apply_filters( 'trswc_get_template', $located, $template_name, $args, $template_path, $default_path );

			do_action( 'trswc_before_template_part', $template_name, $template_path, $located, $args );

			include $located;

			do_action( 'trswc_after_template_part', $template_name, $template_path, $located, $args );
		}
	}
}