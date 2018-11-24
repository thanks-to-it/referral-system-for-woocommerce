<?php
/**
 * Referral System for WooCommerce - Referral
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\Referral' ) ) {

	class Referral {

		public $cpt_id = 'trswc-referral';
		public $postmeta = array(
			'referrer_id'        => '_trswc_referrer_id',
			'order_id'           => '_trswc_order_id',
			'total_reward_value' => '_trswc_total_reward_value',
			'currency'           => '_trswc_currency',
			'referral_code'      => '_trswc_referral_code',
			'coupon_code'        => '_trswc_coupon_code',
		);

		private function get_referral_currency( $referral_id = null ) {
			$referral_id = $referral_id ? $referral_id : ( isset( $_REQUEST['post_ID'] ) ? $_REQUEST['post_ID'] : ( isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : null ) );
			if ( $referral_id ) {
				if ( $this->cpt_id !== get_post_type( $referral_id ) ) {
					return false;
				}
			}
			return get_post_meta( $referral_id, $this->postmeta['currency'], true );
		}

		public function add_custom_metabox() {
			$cmb_demo = new_cmb2_box( array(
				'id'           => '_trswc_referral_cmb',
				'cmb_styles'   => false, // false to disable the CMB stylesheet
				'title'        => esc_html__( 'Info', 'referral-system-for-woocommerce' ),
				'object_types' => array( $this->cpt_id ), // Post type
			) );
			$cmb_demo->add_field( array(
				'name'       => esc_html__( 'Referrer', 'referral-system-for-woocommerce' ),
				'id'         => $this->postmeta['referrer_id'],
				'type'       => 'text',
				'attributes' => array(
					'type'  => 'number',
					'style' => 'width:98%'
				)
			) );
			$cmb_demo->add_field( array(
				'name'       => esc_html__( 'Order', 'referral-system-for-woocommerce' ),
				'id'         => $this->postmeta['order_id'],
				'type'       => 'text',
				'attributes' => array(
					'type'  => 'number',
					'style' => 'width:98%'
				)
			) );
			$cmb_demo->add_field( array(
				'name'       => __( 'Reward Value', 'marketplace-for-woocommerce' ) . ' (' . $this->get_referral_currency() . ')',
				'id'         => $this->postmeta['total_reward_value'],
				'type'       => 'text',
				'attributes' => array(
					'step'  => '0.001',
					'type'  => 'number',
					'style' => 'width:98%'
				),
			) );
			$cmb_demo->add_field( array(
				'name'       => esc_html__( 'Currency', 'referral-system-for-woocommerce' ),
				'id'         => $this->postmeta['currency'],
				'type'       => 'select',
				'options'    => get_woocommerce_currencies(),
				'default'    => get_woocommerce_currency(),
				'attributes' => array(
					'style' => 'width:98%'
				)
			) );
			$cmb_demo->add_field( array(
				'name'       => esc_html__( 'Referral Code', 'referral-system-for-woocommerce' ),
				'id'         => $this->postmeta['referral_code'],
				'type'       => 'text',
				'attributes' => array(
					'style' => 'width:98%'
				)

			) );
			$cmb_demo->add_field( array(
				'name'       => esc_html__( 'Coupon Code', 'referral-system-for-woocommerce' ),
				'id'         => $this->postmeta['coupon_code'],
				'type'       => 'text',
				'attributes' => array(
					'style' => 'width:98%'
				)
			) );
		}

		public function manage_ui_columns( $columns ) {
			$new_columns = array(
				$this->postmeta['referrer_id']        => __( 'Referrer', 'referral-system-for-woocommerce' ),
				$this->postmeta['order_id']           => __( 'Order', 'referral-system-for-woocommerce' ),
				$this->postmeta['total_reward_value'] => __( 'Reward', 'referral-system-for-woocommerce' ),
				$this->postmeta['referral_code']      => __( 'Referral Code', 'referral-system-for-woocommerce' ),
				$this->postmeta['coupon_code']        => __( 'Coupon Code', 'referral-system-for-woocommerce' ),
			);

			Array_Utils::array_splice_assoc( $columns, 2, 0, $new_columns );
			return $columns;
		}

		public function get_unpaid_commission_from_user_id( $user_id, $args = null ) {
			$status        = new Referral_Status();
			$unpaid_status = $status->get_unpaid_term();
			$args          = wp_parse_args( $args, array(
				'tax_query' => array(
					'fields' => 'ids',
					array(
						'taxonomy' => $status->tax_id,
						'field'    => 'slug',
						'terms'    => $unpaid_status->slug,
					),
				),
			) );
			$unpaid_query  = $this->get_commissions_query_from_user_id( get_current_user_id(), $args );
			return $unpaid_query;
		}

		public function get_unpaid_commissions_total_from_user_id( $user_id, $args = null ) {
			$referral     = new Referral();
			$unpaid_query = $this->get_unpaid_commission_from_user_id( $user_id, $args );
			$total_unpaid = 0;
			foreach ( $unpaid_query->posts as $referral_id ) {
				$referral_value = get_post_meta( $referral_id, $referral->postmeta['total_reward_value'], true );
				$total_unpaid   += $referral_value;
			}
			return $total_unpaid;
		}

		public function get_commissions_query_from_user_id( $user_id, $args = null ) {
			$args      = wp_parse_args( $args, array(
				'post_type'      => $this->cpt_id,
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
				'fields'         => 'ids',
				'orderby'        => 'date',
				'order'          => 'DESC',
				'meta_query'     => array(
					array(
						'key'     => $this->postmeta['referrer_id'],
						'value'   => $user_id,
						'compare' => '=',
					),
				),
			) );
			$the_query = new \WP_Query( $args );
			return $the_query;
		}

		public function manage_ui_columns_content( $column, $post_id ) {
			$column_meta_value = get_post_meta( $post_id, $column, true );
			switch ( $column ) {
				case $this->postmeta['referrer_id'] :
					if ( ! empty( $column_meta_value ) ) {
						$referrer_id = $column_meta_value;
						$referrer    = get_user_by( 'id', $referrer_id );
						echo '<a href="' . get_edit_user_link( $referrer_id ) . '">' . esc_html( $referrer->user_email ) . '</a>';
					}
				break;
				case $this->postmeta['order_id'] :
					$order_url = admin_url( "post.php?post={$column_meta_value}&action=edit" );
					echo '<a href="' . $order_url . '">' . $column_meta_value . '</a>';
				break;
				case $this->postmeta['total_reward_value'] :
					echo wc_price( $column_meta_value );
				break;
				case $this->postmeta['referral_code'] :
					echo '<strong>' . $column_meta_value . '</strong>';
				break;
				case $this->postmeta['coupon_code'] :
					echo '<strong>' . $column_meta_value . '</strong>';
				break;
			}
		}

		public function calculate_total_reward_value( $referral_coupon_id, \WC_Order $order, $referrer_id ) {
			$referral_coupon    = new Referral_Coupon();
			$fixed_value_reward = get_post_meta( $referral_coupon_id, $referral_coupon->postmeta['reward_fixed_value'], true );
			return apply_filters( 'trswc_total_reward_value', $fixed_value_reward, $referral_coupon_id, $order, $referrer_id );
		}

		public function remove_commission_by_order_id( $order_id ) {
			$the_query = new \WP_Query( array(
				'post_type'   => $this->cpt_id,
				'post_status' => 'publish',
				'fields'      => 'ids',
				'meta_query'  => array(
					array(
						'key'     => $this->postmeta['order_id'],
						'value'   => $order_id,
						'compare' => '=',
					),
				),
			) );

			foreach ( $the_query->posts as $post ) {
				wp_delete_post( $post );
			}

			// Restore original Post Data
			wp_reset_postdata();
		}

		public function get_referral_by_order_id( $order_id ) {
			$the_query = new \WP_Query( array(
				'post_type'   => $this->cpt_id,
				'post_status' => 'publish',
				'fields'      => 'ids',
				'meta_query'  => array(
					array(
						'key'     => $this->postmeta['order_id'],
						'value'   => $order_id,
						'compare' => '=',
					),
				),
			) );

			// Restore original Post Data
			wp_reset_postdata();

			foreach ( $the_query->posts as $post ) {
				return $post;
			}

			return false;
		}

		/*function modify_list_row_actions( $actions, $post ) {

			// Check for your post type.
			if ( $post->post_type == $this->cpt_id ) {

				// Build your links URL.
				$url = admin_url( 'admin.php?page=mycpt_page&post=' . $post->ID );

				// Maybe put in some extra arguments based on the post status.
				$edit_link = add_query_arg( array( 'action' => 'edit' ), $url );

				// The default $actions passed has the Edit, Quick-edit and Trash links.
				$trash = $actions['trash'];


				$actions = array(
					'edit' => sprintf( '<a href="%1$s">%2$s</a>',
						esc_url( $edit_link ),
						esc_html( __( 'Edit', 'contact-form-7' ) ) )
				);

				// You can check if the current user has some custom rights.
				if ( current_user_can( 'administrator', $post->ID ) ) {



					// Include a nonce in this link
					$copy_link = wp_nonce_url( add_query_arg( array( 'action' => 'copy' ), $url ), 'edit_my_cpt_nonce' );

					// Add the new Copy quick link.
					$actions = array_merge( $actions, array(
						'copy' => sprintf( '<a href="%1$s">%2$s</a>',
							esc_url( $copy_link ),
							'Duplicate'
						)
					) );

					// Re-insert thrash link preserved from the default $actions.
					$actions['trash']=$trash;
				}
			}

			error_log(print_r($actions,true));

			return $actions;
		}*/

		public function create_referral_from_order( $order_id ) {
			$referral_coupon    = new Referral_Coupon();
			$referrer_id        = get_post_meta( $order_id, $referral_coupon->order_postmeta['referrer_id'], true );
			$total_reward_value = get_post_meta( $order_id, $referral_coupon->order_postmeta['total_reward_value'], true );
			$order_currency     = get_post_meta( $order_id, '_order_currency', true );
			$referral_code      = get_post_meta( $order_id, $referral_coupon->order_postmeta['referral_code'], true );
			$coupon_code        = get_post_meta( $order_id, $referral_coupon->order_postmeta['coupon_code'], true );
			$order              = get_post( $order_id );

			// Prevents referral creation in case it's a default order
			if (
				empty( $referral_code ) ||
				empty( $referrer_id )
			) {
				return;
			}

			// Block referral creation
			$authenticity       = new Authenticity();
			$fraud_info         = get_post_meta( $order_id, $authenticity->order_postmeta['fraud_data'], true );
			$block_referral_opt = get_option( 'trswc_opt_referral_blocking', array( 'same_email' ) );
			if (
				! empty( $block_referral_opt ) &&
				! empty( $fraud_info ) &&
				count( array_intersect( $block_referral_opt, $fraud_info ) ) > 0
			) {
				return;
			}

			$meta_input = array(
				$this->postmeta['referrer_id']        => $referrer_id,
				$this->postmeta['order_id']           => $order_id,
				$this->postmeta['total_reward_value'] => $total_reward_value,
				$this->postmeta['currency']           => $order_currency,
				$this->postmeta['referral_code']      => $referral_code,
				$this->postmeta['coupon_code']        => $coupon_code,
			);

			$old_referral_id = $this->get_referral_by_order_id( $order_id );
			if ( false !== $old_referral_id ) {
				foreach ( $meta_input as $meta_key => $meta_value ) {
					update_post_meta( $old_referral_id, $meta_key, $meta_value );
				}
			} else {
				$referral_id = wp_insert_post( array(
					'post_title'  => __( 'Referral', 'referral-system-for-woocommerce' ),
					'post_type'   => $this->cpt_id,
					'post_date'   => $order->post_date,
					'post_status' => 'publish',
					'meta_input'  => $meta_input
				), true );
				wp_update_post( array(
					'ID'         => $referral_id,
					'post_title' => __( 'Referral' ) . ' ' . $referral_id
				) );

				// Set as default status (probably unpaid)
				$referral_status = new Referral_Status();
				$term            = $referral_status->get_default_term();
				wp_set_object_terms( $referral_id, $term->slug, $referral_status->tax_id );

				$this->set_referral_authenticity( $fraud_info, $referral_id );
			}
		}

		public function set_referral_authenticity( $fraud_info, $referral_id ) {
			if ( empty( $fraud_info ) || count( $fraud_info ) == 0 || $fraud_info === false ) {
				$authenticity = new Authenticity();
				$term         = $authenticity->get_reliable_term();
				wp_set_object_terms( $referral_id, $term->slug, $authenticity->tax_id );
			} else {
				$authenticity = new Authenticity();
				foreach ( $fraud_info as $fraud_id ) {
					$term_slug = get_option( 'trswc_opt_auto_auth_' . $fraud_id, 'possible-fraud' );
					$term      = get_term_by( 'slug', $term_slug, $authenticity->tax_id );
					$terms     = wp_get_post_terms( $referral_id, $authenticity->tax_id, array( 'fields' => 'ids' ) );
					$terms[]   = $term->term_id;
					wp_set_object_terms( $referral_id, $terms, $authenticity->tax_id );
				}
			}
		}

		public function register_post_type() {
			$labels = array(
				'name'               => __( 'Referrals', 'referral-system-for-woocommerce' ),
				'singular_name'      => __( 'Referral', 'referral-system-for-woocommerce' ),
				'menu_name'          => __( 'Referrals', 'referral-system-for-woocommerce' ),
				'name_admin_bar'     => __( 'Referral', 'referral-system-for-woocommerce' ),
				'add_new'            => __( 'Add New', 'referral-system-for-woocommerce' ),
				'add_new_item'       => __( 'Add New Referral', 'referral-system-for-woocommerce' ),
				'new_item'           => __( 'New Referral', 'referral-system-for-woocommerce' ),
				'edit_item'          => __( 'Edit Referral', 'referral-system-for-woocommerce' ),
				'view_item'          => __( 'View Referral', 'referral-system-for-woocommerce' ),
				'all_items'          => __( 'Referrals', 'referral-system-for-woocommerce' ),
				'search_items'       => __( 'Search Referrals', 'referral-system-for-woocommerce' ),
				'parent_item_colon'  => __( 'Parent Referrals:', 'referral-system-for-woocommerce' ),
				'not_found'          => __( 'No Referrals found.', 'referral-system-for-woocommerce' ),
				'not_found_in_trash' => __( 'No Referrals found in Trash.', 'referral-system-for-woocommerce' ),
			);

			$args = array(
				'labels'             => $labels,
				'description'        => __( 'Description.', 'referral-system-for-woocommerce' ),
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'show_in_menu'       => 'edit.php?trswc=trswc',
				'query_var'          => false,
				'rewrite'            => array( 'slug' => 'referral' ),
				//'capability_type'    => 'alg_mpwc_commission',
				/*'capabilities'       => array(
					'create_posts' => 'manage_woocommerce',
				),*/
				'map_meta_cap'       => true,
				'has_archive'        => false,
				'hierarchical'       => false,
				'menu_position'      => null,
				//'menu_icon'          => 'dashicons-cart',
				'menu_icon'          => 'dashicons-money',
				'supports'           => array( 'title' ),
			);
			register_post_type( $this->cpt_id, $args );
		}
	}
}