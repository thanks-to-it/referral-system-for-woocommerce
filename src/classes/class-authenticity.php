<?php
/**
 * Referral System for WooCommerce - Authenticity Authenticity
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\Authenticity' ) ) {

	class Authenticity {

		public $tax_id = 'trswc-authenticity';
		public $cpt_id = '';
		public $cookies = array(
			'referrer_cookie' => '_trswc_referrer_cookie',
		);

		public function get_fraud_suspicion_info( $order_id ) {
			$referral_coupon = new Referral_Coupon();
			$order           = wc_get_order( $order_id );
			$referrer        = new Referrer();

			// Referrer info
			$referrer_id     = get_post_meta( $order_id, $referral_coupon->order_postmeta['referrer_id'], true );
			$referrer_user   = get_user_by( 'ID', $referrer_id );
			$referrer_email  = $referrer_user->user_email;
			$referrer_ip     = get_user_meta( $referrer_id, $referrer->usermeta['ip'], true );
			$referrer_cookie = $referrer->get_cookie();

			// Customer info
			$customer_email = $order->get_billing_email();
			$customer_ip    = $order->get_customer_ip_address();

			$fraud_info = array();

			if ( $customer_email == $referrer_email ) {
				$fraud_info['same_email'] = __( 'Referral and Customer have the same email', 'referral-system-for-woocommerce' );
			}

			if ( $customer_ip == $referrer_ip ) {
				$fraud_info['same_ip'] = __( 'Referral and Customer have the same IP', 'referral-system-for-woocommerce' );
			}

			if ( ! is_empty( $referrer_cookie ) ) {
				$fraud_info['found_cookie'] = __( 'Found a Referrer Cookie', 'referral-system-for-woocommerce' );

				if ( $referrer->get_referrer_id_from_cookie( $referrer_cookie ) == $referrer_id ) {
					$fraud_info['cookie_match_referrer'] = __( 'Cookie match Referrer ID', 'referral-system-for-woocommerce' );
				}
			}

			return $fraud_info;
		}

		public function get_default_terms() {
			return array(
				array(
					'slug'  => 'ok',
					'label' => __( 'Ok', 'referral-system-for-woocommerce' ),
				),
				array(
					'slug'  => 'possible-fraud',
					'label' => __( 'Possible Fraud', 'referral-system-for-woocommerce' ),
				),
			);
		}

		public function create_initial_terms() {
			$this->register_taxonomy();
			$terms = $this->get_default_terms();
			foreach ( $terms as $term ) {
				if ( term_exists( $term['slug'], $this->tax_id ) == null ) {
					$response = $this->add_term( $term['slug'] );
				}
			}
		}

		public function add_term( $term_slug ) {
			$terms = $this->get_default_terms();
			$term  = wp_list_filter( $terms, array( 'slug' => $term_slug ) );
			$pos   = reset( $term );

			return wp_insert_term(
				$pos['label'],
				$this->tax_id,
				array(
					'slug' => $term_slug,
				)
			);
		}

		function move_taxonomy_menu() {
			add_submenu_page( 'edit.php?trswc=trswc', esc_html__( 'Authenticity', 'referral-system-for-woocommerce' ), esc_html__( 'Authenticity', 'referral-system-for-woocommerce' ), 'manage_categories', 'edit-tags.php?taxonomy=' . $this->tax_id . '' );
		}

		function highlight_taxonomy_parent_menu( $parent_file ) {
			if ( get_current_screen()->taxonomy == $this->tax_id ) {
				$parent_file = 'edit.php?trswc=trswc';
			}
			return $parent_file;
		}

		public function register_taxonomy() {
			// Add new taxonomy, make it hierarchical (like categories)
			$labels = array(
				'name'              => __( 'Authenticity', 'marketplace-for-woocommerce' ),
				'singular_name'     => __( 'Authenticity', 'marketplace-for-woocommerce' ),
				'search_items'      => __( 'Search Authenticity', 'marketplace-for-woocommerce' ),
				'all_items'         => __( 'All Authenticity', 'marketplace-for-woocommerce' ),
				'parent_item'       => __( 'Parent Authenticity', 'marketplace-for-woocommerce' ),
				'parent_item_colon' => __( 'Parent Authenticity:', 'marketplace-for-woocommerce' ),
				'edit_item'         => __( 'Edit Authenticity', 'marketplace-for-woocommerce' ),
				'update_item'       => __( 'Update Authenticity', 'marketplace-for-woocommerce' ),
				'add_new_item'      => __( 'Add New Authenticity', 'marketplace-for-woocommerce' ),
				'new_item_name'     => __( 'New Authenticity Name', 'marketplace-for-woocommerce' ),
				'menu_name'         => __( 'Authenticity', 'marketplace-for-woocommerce' ),
			);

			$args = array(
				'hierarchical'       => true,
				'labels'             => $labels,
				'show_in_menu'       => true,
				//'show_in_menu'       => 'edit.php?trswc=trswc',
				'show_ui'            => true,
				'show_admin_column'  => true,
				'show_in_quick_edit' => true,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'authenticity' ),
			);

			$cpt_id = $this->cpt_id;
			if ( ! empty( $cpt_id ) ) {
				register_taxonomy( $this->tax_id, $cpt_id, $args );
			}
		}
	}
}