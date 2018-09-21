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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\Core' ) ) {

	class Core {

		public $plugin_info = array();

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
				$this->referral_coupon         = $referral_coupon = new Referral_Coupon();
				$this->referral                = $referral = new Referral();
				$this->referrer                = $referrer = new Referrer();
				$this->referral_status         = $referral_status = new Referral_Status();
				$this->authenticity            = $authenticity = new Authenticity();
				$this->referral_status->cpt_id = $referral->cpt_id;
				$this->authenticity->cpt_id    = $referral->cpt_id;
				$this->handle_my_account();
				$this->handle_referral_coupon();
				$this->handle_referrals();
				$this->handle_referrer();
				$this->handle_referral_status();
				$this->handle_authenticity();
			}
		}

		public function handle_authenticity(){
			$authenticity = $this->authenticity;

			// Register taxonomy
			add_action( 'init', array( $authenticity, 'register_taxonomy' ), 10 );

			// Move taxonomy menu under Referral
			add_action( 'admin_menu', array( $authenticity, 'move_taxonomy_menu' ) );
			add_action( 'parent_file', array( $authenticity, 'highlight_taxonomy_parent_menu' ) );

			// Create default terms
			register_activation_hook( $this->plugin_info['path'], array( $authenticity, 'create_initial_terms' ) );

			// Turn checkbox taxonomy into radio (https://github.com/WebDevStudios/Taxonomy_Single_Term)
			$custom_tax_mb = new \Taxonomy_Single_Term( $authenticity->tax_id );
		}

		private function handle_referral_status() {
			$referral_status = $this->referral_status;

			// Register taxonomy
			add_action( 'init', array( $referral_status, 'register_taxonomy' ), 10 );

			// Move taxonomy menu under Referral
			add_action( 'admin_menu', array( $referral_status, 'move_taxonomy_menu' ) );
			add_action( 'parent_file', array( $referral_status, 'highlight_taxonomy_parent_menu' ) );

			// Create default terms
			register_activation_hook( $this->plugin_info['path'], array( $referral_status, 'create_initial_terms' ) );

			// Turn checkbox taxonomy into radio (https://github.com/WebDevStudios/Taxonomy_Single_Term)
			$custom_tax_mb = new \Taxonomy_Single_Term( $referral_status->tax_id );
		}

		private function handle_referral_coupon() {
			$referral_coupon = $this->referral_coupon;

			// Save referral code data from query string in wc_session
			add_action( 'wp_loaded', array( $referral_coupon, 'save_referral_code_data_in_wc_session' ) );

			// Apply referral code discount programmatically
			add_action( 'woocommerce_calculate_totals', array( $referral_coupon, 'apply_discount_programmatically' ) );

			// Mask coupon name with referral code
			add_action( 'woocommerce_cart_totals_coupon_label', array( $referral_coupon, 'mask_coupon_name' ), 10, 2 );

			// Remove coupon html if discount is zero
			add_filter( 'woocommerce_coupon_discount_amount_html', array( $referral_coupon, 'remove_coupon_html_if_zero_discount' ), 10, 2 );

			// Save referral code data on order creation
			add_action( 'woocommerce_checkout_create_order', array( $referral_coupon, 'save_referral_code_data_on_order' ), 10, 2 );

			// Show referral code data on admin order
			add_action( 'woocommerce_admin_order_data_after_order_details', array( $referral_coupon, 'show_referral_code_data_on_admin_order' ), 10, 2 );
		}

		private function handle_referrer() {
			$referrer = $this->referrer;

			// Add Referrer roles on plugin activation
			register_activation_hook( $this->plugin_info['path'], array( $referrer, 'add_roles' ) );

			// Save referrer ip
			add_action( 'wp_login', array( $referrer, 'save_ip' ),10,2 );
		}

		private function handle_referrals() {
			$referral = $this->referral;

			// Add commission post type
			add_action( 'init', array( $referral, 'register_post_type' ) );

			// Create commission when order is completed
			add_action( 'woocommerce_order_status_completed', array( $referral, 'create_referral_from_order' ), 10 );

			// Add columns for commissions on admin
			add_filter( "manage_{$referral->cpt_id}_posts_columns", array( $referral, 'add_ui_columns' ) );
			add_action( "manage_{$referral->cpt_id}_posts_custom_column", array( $referral, 'add_ui_columns_content' ), 10, 2 );
		}

		private function handle_my_account() {

			// My Account > Referral Codes tab
			$referral_codes_tab = new Referral_Codes_Tab();
			add_action( 'init', array( $referral_codes_tab, 'add_endpoint' ) );
			add_filter( 'query_vars', array( $referral_codes_tab, 'add_query_vars' ), 0 );
			add_filter( 'woocommerce_account_menu_items', array( $referral_codes_tab, 'add_menu_item' ) );
			add_action( 'woocommerce_account_' . $referral_codes_tab->tab_id . '_endpoint', array( $referral_codes_tab, 'add_content' ) );

			// My Account > Referral Codes tab
			$commissions_tab = new Referrals_Tab();
			add_action( 'init', array( $commissions_tab, 'add_endpoint' ) );
			add_filter( 'query_vars', array( $commissions_tab, 'add_query_vars' ), 0 );
			add_filter( 'woocommerce_account_menu_items', array( $commissions_tab, 'add_menu_item' ) );
			add_action( 'woocommerce_account_' . $commissions_tab->tab_id . '_endpoint', array( $commissions_tab, 'add_content' ) );

			add_action( 'woocommerce_before_account_navigation', array( 'ThanksToIT\RSWC\My_Account_Style', 'add_Style' ) );
		}

		/**
		 * Sets admin
		 * @version 1.0.0
		 * @since 1.0.0
		 */
		private function handle_admin() {
			$referral_coupon_tab = new Referral_Coupon_Tab();
			$referral_menu_item  = new Referral_Menu_Item();

			// Add referral menu item
			add_action( 'admin_menu', array( $referral_menu_item, 'add_referral_page' ) );

			// Referral tab on admin
			add_filter( 'woocommerce_coupon_data_tabs', array( $referral_coupon_tab, 'add_tab' ) );
			add_filter( 'woocommerce_coupon_data_panels', array( $referral_coupon_tab, 'add_tab_panel' ) );
			add_filter( 'woocommerce_coupon_options_save', array( $referral_coupon_tab, 'save_tab_data' ) );

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