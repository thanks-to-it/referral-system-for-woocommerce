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
use ThanksToIT\RSWC\Admin\Referral_Coupon_Tab;
use ThanksToIT\RSWC\Admin\Referral_Menu_Item;
use \Carbon_Fields\Container;
use \Carbon_Fields\Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\Core' ) ) {

	class Core {

		public $plugin_info = array();

		/**
		 * @var Referral_Codes_Tab
		 */
		public $referral_codes_tab;

		/**
		 * @var Referrals_Tab
		 */
		public $referrals_tab;

		/**
		 * @var Referral
		 */
		public $referral;

		/**
		 * @var Referral_Coupon
		 */
		public $referral_coupon;

		/**
		 * @var Referrer
		 */
		public $referrer;

		/**
		 * @var Referral_Status
		 */
		public $referral_status;

		/**
		 * @var Authenticity;
		 */
		public $authenticity;

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
			$args              = wp_parse_args( $args, array(
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
				$this->handle_my_account();
				$this->handle_referral_coupon();
				$this->handle_referrals();
				$this->handle_referrer();
				$this->handle_referral_status();
				$this->handle_authenticity();
				register_activation_hook( $this->plugin_info['path'], array( $this, 'update_rewrite_rules' ) );
			}
		}

		public function update_rewrite_rules() {
			$referral_codes_tab = new Referral_Codes_Tab();
			$referral_codes_tab->add_endpoint();
			$referrals = new Referrals_Tab();
			$referrals->add_endpoint();
			flush_rewrite_rules( true );
		}

		public function handle_authenticity() {
			add_action( 'init', function () {
				$this->authenticity         = new Authenticity();
				$authenticity               = $this->authenticity;
				$this->authenticity->cpt_id = $this->referral->cpt_id;
				$authenticity->register_taxonomy();

				// Move taxonomy menu under Referral
				add_action( 'admin_menu', array( $authenticity, 'move_taxonomy_menu' ) );
				add_action( 'parent_file', array( $authenticity, 'highlight_taxonomy_parent_menu' ) );

				// Show authenticity data on order
				add_action( 'woocommerce_admin_order_data_after_order_details', array( $authenticity, 'show_admin_order_authenticity_data' ), 10, 2 );
			} );

			// Create default terms
			register_activation_hook( $this->plugin_info['path'], function () {
				$this->authenticity = new Authenticity();
				$authenticity       = $this->authenticity;
				$authenticity->create_initial_terms();
			} );

			// Turn checkbox taxonomy into radio (https://github.com/WebDevStudios/Taxonomy_Single_Term)
			//$custom_tax_mb = new \Taxonomy_Single_Term( $authenticity->tax_id );
		}

		private function handle_referral_status() {
			add_action( 'init', function () {
				$this->referral_status         = new Referral_Status();
				$referral_status               = $this->referral_status;
				$this->referral_status->cpt_id = $this->referral->cpt_id;

				// Register taxonomy
				$referral_status->register_taxonomy();

				// Move taxonomy menu under Referral
				add_action( 'admin_menu', array( $referral_status, 'move_taxonomy_menu' ) );
				add_action( 'parent_file', array( $referral_status, 'highlight_taxonomy_parent_menu' ) );
			} );

			register_activation_hook( $this->plugin_info['path'], function () {
				$this->referral_status = new Referral_Status();
				$referral_status       = $this->referral_status;
				$referral_status->create_initial_terms();
			} );

			// Turn checkbox taxonomy into radio (https://github.com/WebDevStudios/Taxonomy_Single_Term)
			//$custom_tax_mb = new \Taxonomy_Single_Term( $referral_status->tax_id );
		}

		private function handle_referral_coupon() {
			add_action( 'wp_loaded', function () {
				$this->referral_coupon = new Referral_Coupon();
				$referral_coupon       = $this->referral_coupon;

				// Save referral code data from query string in wc_session
				$referral_coupon->save_referral_code_data_in_wc_session();

				// Apply referral code discount programmatically
				//add_action( 'woocommerce_calculate_totals', array( $referral_coupon, 'apply_discount_programmatically' ) );

				add_action( 'woocommerce_before_cart_table', array( $referral_coupon, 'apply_discount_programmatically' ) );
				add_action( 'woocommerce_before_checkout_form', array( $referral_coupon, 'apply_discount_programmatically' ) );

				// Mask coupon name with referral code
				add_action( 'woocommerce_cart_totals_coupon_label', array( $referral_coupon, 'mask_coupon_name' ), 10, 2 );

				// Remove coupon html if discount is zero
				add_filter( 'woocommerce_coupon_discount_amount_html', array( $referral_coupon, 'remove_coupon_html_if_zero_discount' ), 10, 2 );

				// Save referral code data on order creation
				add_action( 'woocommerce_checkout_create_order', array( $referral_coupon, 'save_referral_code_data_on_order' ), 10, 2 );

				// Show referral code data on admin order
				add_action( 'woocommerce_admin_order_data_after_order_details', array( $referral_coupon, 'show_admin_order_referral_code_data' ), 10, 2 );
			} );
		}

		private function handle_referrer() {
			$referrer = $this->referrer;

			// Add Referrer roles on plugin activation
			register_activation_hook( $this->plugin_info['path'], array( $referrer, 'add_roles' ) );

			// Save referrer ip
			add_action( 'wp_login', array( $referrer, 'save_ip' ), 10, 2 );

			// Save referrer cookie
			add_action( 'wp_login', array( $referrer, 'save_cookie' ), 10, 2 );
		}

		private function handle_referrals() {
			add_action( 'init', function () {
				$this->referral = new Referral();
			} );

			add_action( 'init', function () {
				$referral = $this->referral;

				// Add commission post type
				$referral->register_post_type();

				// Create commission when order is completed
				add_action( 'woocommerce_order_status_completed', array( $referral, 'create_referral_from_order' ), 10 );

				// Add columns for commissions on admin
				add_filter( "manage_{$referral->cpt_id}_posts_columns", array( $referral, 'manage_ui_columns' ) );
				add_action( "manage_{$referral->cpt_id}_posts_custom_column", array( $referral, 'manage_ui_columns_content' ), 10, 2 );
				add_action( "manage_{$referral->cpt_id}_posts_column", array( $referral, 'manage_ui_columns_content' ), 10, 2 );

				//add_filter( 'post_row_actions', array( $referral, 'modify_list_row_actions' ), 10, 2 );
			} );
		}

		private function handle_my_account() {
			add_action( 'init', function () {
				$this->referral_codes_tab = new Referral_Codes_Tab();
				$this->referrals_tab      = new Referrals_Tab();
			} );

			add_action( 'init', function () {
				// My Account > Referral Codes tab
				$referral_codes_tab = $this->referral_codes_tab;
				$referral_codes_tab->add_endpoint();
				add_filter( 'query_vars', array( $referral_codes_tab, 'add_query_vars' ), 0 );
				add_filter( 'woocommerce_account_menu_items', array( $referral_codes_tab, 'add_menu_item' ) );
				add_action( 'woocommerce_account_' . $referral_codes_tab->tab_id . '_endpoint', array( $referral_codes_tab, 'add_content' ) );
				add_filter( 'the_title', array( $referral_codes_tab, 'handle_endpoint_title' ) );

				// My Account > Referral Codes tab
				$referrals_tab = $this->referrals_tab;
				$referrals_tab->add_endpoint();
				add_filter( 'query_vars', array( $referrals_tab, 'add_query_vars' ), 0 );
				add_filter( 'woocommerce_account_menu_items', array( $referrals_tab, 'add_menu_item' ) );
				add_action( 'woocommerce_account_' . $referrals_tab->tab_id . '_endpoint', array( $referrals_tab, 'add_content' ) );
				add_filter( 'the_title', array( $referrals_tab, 'handle_endpoint_title' ) );
			} );

			add_action( 'woocommerce_before_account_navigation', array( 'ThanksToIT\RSWC\My_Account_Style', 'add_style' ) );
		}

		/**
		 * Sets admin
		 * @version 1.0.0
		 * @since 1.0.0
		 */
		private function handle_admin() {
			add_action( 'admin_menu', function () {
				// Add referral menu item
				$referral_menu_item = new Referral_Menu_Item();
				$referral_menu_item->add_referral_page();

				$referral_coupon_tab = new Referral_Coupon_Tab();

				// Referral tab on admin
				add_filter( 'woocommerce_coupon_data_tabs', array( $referral_coupon_tab, 'add_tab' ) );
				add_filter( 'woocommerce_coupon_data_panels', array( $referral_coupon_tab, 'add_tab_panel' ) );
				add_filter( 'woocommerce_coupon_options_save', array( $referral_coupon_tab, 'save_tab_data' ) );
			} );

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