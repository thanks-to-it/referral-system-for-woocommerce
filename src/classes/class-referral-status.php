<?php
/**
 * Referral System for WooCommerce - Referral Status
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\Referral_Status' ) ) {

	class Referral_Status {

		public $tax_id = 'trswc-referral-status';
		public $cpt_id = '';

		public function get_default_terms() {
			return array(
				array(
					'slug'  => 'paid',
					'label' => __( 'Paid', 'referral-system-for-woocommerce' ),
				),
				array(
					'slug'  => 'unpaid',
					'label' => __( 'Unpaid', 'referral-system-for-woocommerce' ),
				),
				array(
					'slug'  => 'rejected',
					'label' => __( 'Rejected', 'referral-system-for-woocommerce' ),
				),
			);
		}

		public function get_default_term() {
			$term_opt = get_option( 'trswc_opt_status_default', 'unpaid' );
			$term = get_term_by( 'slug', $term_opt, $this->tax_id );
			return $term;
		}

		public function get_paid_term() {
			$term_opt = get_option( 'trswc_opt_status_paid', 'paid' );
			$term     = get_term_by( 'slug', $term_opt, $this->tax_id );
			return $term;
		}

		public function get_unpaid_term() {
			$term_opt = get_option( 'trswc_opt_status_unpaid', 'unpaid' );
			$term     = get_term_by( 'slug', $term_opt, $this->tax_id );
			return $term;
		}

		public function get_rejected_term() {
			$term_opt = get_option( 'trswc_opt_status_rejected', 'rejected' );
			$term     = get_term_by( 'slug', $term_opt, $this->tax_id );
			return $term;
		}

		public function get_terms( $args ) {
			$args  = wp_parse_args( $args, array(
				'taxonomy'   => $this->tax_id,
				'hide_empty' => false,
			) );
			$terms = get_terms( $args );
			if ( isset( $args['get_only'] ) && $args['get_only'] == 'id_and_title' ) {
				$terms = wp_list_pluck( $terms, 'name', 'term_id' );
			}
			if ( isset( $args['get_only'] ) && $args['get_only'] == 'slug_and_title' ) {
				$terms = wp_list_pluck( $terms, 'name', 'slug' );
			}
			return $terms;
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
			add_submenu_page( 'edit.php?trswc=trswc', esc_html__( 'Status', 'referral-system-for-woocommerce' ), esc_html__( 'Status', 'referral-system-for-woocommerce' ), 'manage_categories', 'edit-tags.php?taxonomy=' . $this->tax_id . '' );
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
				'name'              => __( 'Status', 'marketplace-for-woocommerce' ),
				'singular_name'     => __( 'Status', 'marketplace-for-woocommerce' ),
				'search_items'      => __( 'Search Status', 'marketplace-for-woocommerce' ),
				'all_items'         => __( 'All Status', 'marketplace-for-woocommerce' ),
				'parent_item'       => __( 'Parent Status', 'marketplace-for-woocommerce' ),
				'parent_item_colon' => __( 'Parent Status:', 'marketplace-for-woocommerce' ),
				'edit_item'         => __( 'Edit Status', 'marketplace-for-woocommerce' ),
				'update_item'       => __( 'Update Status', 'marketplace-for-woocommerce' ),
				'add_new_item'      => __( 'Add New Status', 'marketplace-for-woocommerce' ),
				'new_item_name'     => __( 'New Status Name', 'marketplace-for-woocommerce' ),
				'menu_name'         => __( 'Status', 'marketplace-for-woocommerce' ),
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
				'rewrite'            => array( 'slug' => 'status' ),
			);

			$cpt_id = $this->cpt_id;
			if ( ! empty( $cpt_id ) ) {
				register_taxonomy( $this->tax_id, $cpt_id, $args );
			}
		}
	}
}