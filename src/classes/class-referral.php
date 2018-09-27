<?php
/**
 * Referral System for WooCommerce - Referral
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC;

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

		public function add_ui_columns( $columns ) {
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

		public function get_commissions_query_from_user_id( $user_id ) {
			$the_query = new \WP_Query( array(
				'post_type'   => $this->cpt_id,
				'post_status' => 'publish',
				'fields'      => 'ids',
				'orderby'     => 'date',
				'order'       => 'DESC',
				'meta_query'  => array(
					array(
						'key'     => $this->postmeta['referrer_id'],
						'value'   => $user_id,
						'compare' => '=',
					),
				),
			) );
			return $the_query;
		}

		public function add_ui_columns_content( $column, $post_id ) {
			$column_meta_value = get_post_meta( $post_id, $column, true );
			switch ( $column ) {
				case $this->postmeta['referrer_id'] :
					$referrer_id = $column_meta_value;
					$referrer    = get_user_by( 'id', $referrer_id );
					echo '<a href="' . get_edit_user_link( $referrer_id ) . '">' . esc_html( $referrer->user_email ) . '</a>';
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

		public function create_referral_from_order( $order_id ) {
			$referral_coupon    = new Referral_Coupon();
			$referrer_id        = get_post_meta( $order_id, $referral_coupon->order_postmeta['referrer_id'], true );
			$total_reward_value = get_post_meta( $order_id, $referral_coupon->order_postmeta['total_reward_value'], true );
			$order_currency     = get_post_meta( $order_id, '_order_currency', true );
			$referral_code      = get_post_meta( $order_id, $referral_coupon->order_postmeta['referral_code'], true );
			$coupon_code        = get_post_meta( $order_id, $referral_coupon->order_postmeta['coupon_code'], true );
			$order              = get_post( $order_id );

			// Block referral creation
			$authenticity       = new Authenticity();
			$fraud_info         = get_post_meta( $order_id, $authenticity->order_postmeta['fraud_data'], true );
			$block_referral_opt = get_option( 'trswc_opt_referral_blocking', array( 'same_email' ) );
			if (
				! empty( $block_referral_opt ) &&
				! empty( $fraud_info ) &&
				count( array_intersect_key( $block_referral_opt, $fraud_info ) ) > 0
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

				// Set as status unpaid
				$referral_status = new Referral_Status();
				$term = $referral_status->get_unpaid_term();
				wp_set_object_terms( $referral_id, $term->slug, $referral_status->tax_id );

				// Set as authenticity Ok
				$authenticity = new Authenticity();
				$term = $authenticity->get_reliable_term();
				wp_set_object_terms( $referral_id, $term->slug, $authenticity->tax_id );
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