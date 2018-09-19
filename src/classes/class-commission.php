<?php
/**
 * Referral System for WooCommerce - Commission
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\Commission' ) ) {

	class Commission {

		public $cpt_slug = 'trswc-commission';
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
			);
			Array_Utils::array_splice_assoc( $columns, 2, 0, $new_columns );
			return $columns;
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
			}
		}

		public function calculate_total_reward_value( $referral_coupon_id, $order ) {
			$referral_coupon    = new Referral_Coupon();
			$fixed_value_reward = get_post_meta( $referral_coupon_id, $referral_coupon->postmeta['reward_fixed_value'], true );
			return apply_filters( 'trswc_total_reward_value', $fixed_value_reward, $referral_coupon_id, $order );
		}

		public function remove_commission_by_order_id( $order_id ) {
			$the_query = new \WP_Query( array(
				'post_type'   => $this->cpt_slug,
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

		public function get_comission_by_order_id( $order_id ) {
			$the_query = new \WP_Query( array(
				'post_type'   => $this->cpt_slug,
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

		public function create_commission_from_order( $order_id ) {
			$referral_coupon    = new Referral_Coupon();
			$referrer_id        = get_post_meta( $order_id, $referral_coupon->order_postmeta['referrer_id'], true );
			$total_reward_value = get_post_meta( $order_id, $referral_coupon->order_postmeta['total_reward_value'], true );
			$order_currency     = get_post_meta( $order_id, '_order_currency', true );
			$referral_code      = get_post_meta( $order_id, $referral_coupon->order_postmeta['referral_code'], true );
			$coupon_code        = get_post_meta( $order_id, $referral_coupon->order_postmeta['coupon_code'], true );
			$order              = get_post( $order_id );

			$meta_input = array(
				$this->postmeta['referrer_id']        => $referrer_id,
				$this->postmeta['order_id']           => $order_id,
				$this->postmeta['total_reward_value'] => $total_reward_value,
				$this->postmeta['currency']           => $order_currency,
				$this->postmeta['referral_code']      => $referral_code,
				$this->postmeta['coupon_code']        => $coupon_code,
			);

			$old_commission_id = $this->get_comission_by_order_id( $order_id );
			if ( false !== $old_commission_id ) {
				foreach ( $meta_input as $meta_key => $meta_value ) {
					update_post_meta( $old_commission_id, $meta_key, $meta_value );
				}
			} else {
				$commission_id = wp_insert_post( array(
					'post_title'  => __( 'Commission', 'referral-system-for-woocommerce' ),
					'post_type'   => $this->cpt_slug,
					'post_date'   => $order->post_date,
					'post_status' => 'publish',
					'meta_input'  => $meta_input
				), true );
				wp_update_post( array(
					'ID'         => $commission_id,
					'post_title' => __( 'Commission' ) . ' ' . $commission_id
				) );
			}
		}

		public function register_post_type() {
			$labels = array(
				'name'               => __( 'Commissions', 'referral-system-for-woocommerce' ),
				'singular_name'      => __( 'Commission', 'referral-system-for-woocommerce' ),
				'menu_name'          => __( 'Commissions', 'referral-system-for-woocommerce' ),
				'name_admin_bar'     => __( 'Commission', 'referral-system-for-woocommerce' ),
				'add_new'            => __( 'Add New', 'referral-system-for-woocommerce' ),
				'add_new_item'       => __( 'Add New Commission', 'referral-system-for-woocommerce' ),
				'new_item'           => __( 'New Commission', 'referral-system-for-woocommerce' ),
				'edit_item'          => __( 'Edit Commission', 'referral-system-for-woocommerce' ),
				'view_item'          => __( 'View Commission', 'referral-system-for-woocommerce' ),
				'all_items'          => __( 'Commissions', 'referral-system-for-woocommerce' ),
				'search_items'       => __( 'Search Commissions', 'referral-system-for-woocommerce' ),
				'parent_item_colon'  => __( 'Parent Commissions:', 'referral-system-for-woocommerce' ),
				'not_found'          => __( 'No Commissions found.', 'referral-system-for-woocommerce' ),
				'not_found_in_trash' => __( 'No Commissions found in Trash.', 'referral-system-for-woocommerce' ),
			);

			$args = array(
				'labels'             => $labels,
				'description'        => __( 'Description.', 'referral-system-for-woocommerce' ),
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'show_in_menu'       => 'edit.php?trswc=trswc',
				'query_var'          => false,
				'rewrite'            => array( 'slug' => 'commission' ),
				//'capability_type'    => 'alg_mpwc_commission',
				/*'capabilities'       => array(
					'create_posts' => 'manage_woocommerce',
				),*/
				'map_meta_cap'       => true,
				'has_archive'        => false,
				'hierarchical'       => false,
				'menu_position'      => null,
				'menu_icon'          => 'dashicons-cart',
				'supports'           => array( 'title' ),
			);
			register_post_type( $this->cpt_slug, $args );
		}
	}
}