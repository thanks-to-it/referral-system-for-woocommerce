<?php
/**
 * Referral System for WooCommerce - Core Class
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC;

use ThanksToIT\RSWC\Admin\Admin_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\Core' ) ) {

	class Core {

		public $plugin_info = array();

		/**
		 * Call this method to get singleton
		 * @return Core
		 */
		public static function instance() {
			static $instance = false;
			if ( $instance === false ) {
				$instance = new static();
			}

			return $instance;
		}

		/**
		 * Setups plugin
		 *
		 * @version 1.0.0
		 * @since 1.0.0
		 *
		 * @param $args
		 */
		public function setup( $args ) {
			$args = wp_parse_args( $args, array(
				'path' => '' // __FILE__
			) );

			$this->plugin_info = $args;
		}

		/**
		 * Gets plugin url
		 *
		 * @version 1.0.0
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public function get_plugin_url() {
			$path = $this->plugin_info['path'];

			return plugin_dir_url( $path );
		}

		/**
		 * Gets plugins dir
		 *
		 * @version 1.0.0
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public function get_plugin_dir() {
			$path = $this->plugin_info['path'];

			return untrailingslashit( plugin_dir_path( $path ) ) . DIRECTORY_SEPARATOR;;
		}

		/**
		 * Initializes
		 *
		 * @version 1.0.0
		 * @since 1.0.0
		 *
		 * @return Core
		 */
		public function init() {
			$this->handle_admin();

			if ( 'yes' === get_option( 'trswc_opt_enable', 'yes' ) ) {

				// Save referral code data from query string in wc_session
				add_action( 'woocommerce_init', array( 'ThanksToIT\RSWC\WC_Session', 'save_referral_code_data_from_query_string' ) );

				// Apply referral code discount programmatically
				add_action( 'woocommerce_calculate_totals', array( 'ThanksToIT\RSWC\WC_Cart', 'apply_referral_code_discount_programmatically' ) );

				// Mask coupon name with referral code
				add_action( 'woocommerce_cart_totals_coupon_label', array( 'ThanksToIT\RSWC\WC_Coupon', 'mask_coupon_name_with_referral_code' ), 10, 2 );

				// Remove coupon html if discount is zero
				add_filter( 'woocommerce_coupon_discount_amount_html', array( 'ThanksToIT\RSWC\WC_Coupon', 'remove_coupon_html_if_zero_discount' ), 10, 2 );

				// Save referral code data on order creation
				add_action( 'woocommerce_checkout_create_order', array( 'ThanksToIT\RSWC\WC_Order', 'save_referral_code_data_on_order_creation' ) );

				// Show referral code data on admin order
				add_action( 'woocommerce_admin_order_data_after_order_details', array('ThanksToIT\RSWC\WC_Order','show_referral_code_data_on_admin_order'), 10, 2 );

				// My Account > Referral Codes tab
				add_action( 'init', array( 'ThanksToIT\RSWC\WC_My_Account', 'add_referral_codes_tab_endpoint' ) );
				add_filter( 'query_vars', array( 'ThanksToIT\RSWC\WC_My_Account', 'add_referral_codes_tab_query_vars' ), 0 );
				add_filter( 'woocommerce_account_menu_items', array( 'ThanksToIT\RSWC\WC_My_Account', 'add_referral_codes_tab_menu_item' ) );
				add_action( 'woocommerce_account_' . 'referral-codes' . '_endpoint', array( 'ThanksToIT\RSWC\WC_My_Account', 'add_referral_codes_tab_content' ) );



				// Admin Coupon Tab > Referral tab
				/*$coupon = new Coupon\Referral_Coupon_Tab();
				$coupon->init();

				// My Account > Referral Codes tab
				$tab = new My_Account\Referral_Codes_Tab();
				$tab->init();

				// Referral Coupon Code
				$coupon_code = new Referral_Coupon_Code();
				$coupon_code->init();*/
			}
		}

		/**
		 * Sets admin
		 * @version 1.0.0
		 * @since 1.0.0
		 */
		private function handle_admin() {
			// Handle referral tab on admin
			add_filter( 'woocommerce_coupon_data_tabs', array( 'ThanksToIT\RSWC\Admin\WC_Coupon', 'add_coupon_referral_tab' ) );
			add_filter( 'woocommerce_coupon_data_panels', array( 'ThanksToIT\RSWC\Admin\WC_Coupon', 'add_referral_tab_panel' ) );
			add_filter( 'woocommerce_coupon_options_save', array( 'ThanksToIT\RSWC\Admin\WC_Coupon', 'save_referral_tab_data' ) );

			//Settings page
			add_filter( 'woocommerce_get_settings_pages', array( $this, 'create_admin_settings' ), 15 );

			// Add settings link on plugins page
			$path = $this->plugin_info['path'];
			add_filter( 'plugin_action_links_' . plugin_basename( $path ), array( $this, 'add_action_links' ) );
		}

		/**
		 * Adds action links
		 *
		 * @version 1.0.0
		 * @since 1.0.0
		 *
		 * @param $links
		 *
		 * @return array
		 */
		public function add_action_links( $links ) {
			$mylinks = array(
				'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=trswc' ) . '">Settings</a>',
			);

			//if ( true === apply_filters( 'ttt_RSWC_license_data', true, 'is_free') ) {
			//$mylinks[] = '<a href="https://wpfactory.com/item/popup-notices-for-woocommerce/">' . __( 'Unlock All', 'product-input-fields-for-woocommerce' ) . '</a>';

			//}

			return array_merge( $mylinks, $links );
		}

		/**
		 * Creates admin settings
		 *
		 * @version 1.0.0
		 * @since 1.0.0
		 *
		 * @param $settings
		 *
		 * @return mixed
		 */
		public function create_admin_settings( $settings ) {
			$settings[] = new Admin_Settings();

			return $settings;
		}
	}
}