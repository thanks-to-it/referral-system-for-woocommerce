<?php
/**
 * Referral System for WooCommerce - Referral Coupon
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\Referral_Coupon' ) ) {

	class Referral_Coupon {

		public function init() {
			// Coupon Referral tab
			add_filter( 'woocommerce_coupon_data_tabs', array( $this, 'add_coupon_referral_tab' ) );
			add_filter( 'woocommerce_coupon_data_panels', array( $this, 'add_coupon_referral_panel' ) );
			add_filter( 'woocommerce_coupon_options_save', array( $this, 'save_coupon_data' ) );
		}

		public function save_coupon_data( $post_id = null ) {
			// Enable option
			$option_name = 'trswc_referral_enable';
			$option      = isset( $_POST[ $option_name ] ) ? 'yes' : 'no';
			update_post_meta( $post_id, $option_name, $option );

			// Fixed value reward option
			$option_name = 'trswc_reward_fixed_value';
			$option      = isset( $_POST[ $option_name ] ) ? $_POST[ $option_name ] : 0;
			$option      = filter_var( $option, FILTER_SANITIZE_NUMBER_FLOAT );
			if ( ! empty( $option ) ) {
				update_post_meta( $post_id, $option_name, $option );
			}
		}

		public function get_referral_coupons_query() {
			$the_query = new \WP_Query( array(
				'post_type'      => 'shop_coupon',
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
				'meta_query'     => array(
					array(
						'key'     => 'trswc_referral_enable',
						'value'   => 'yes',
						'compare' => '=',
					),
				),
			) );

			return $the_query;
		}

		public function add_coupon_referral_panel() {
			?>
            <div id="referral_coupon_data" class="panel woocommerce_options_panel">
                <div class="options_group">
					<?php
					woocommerce_wp_checkbox( array(
						'id'          => 'trswc_referral_enable',
						'label'       => __( 'Use as a referral coupon', 'referral-system-for-woocommerce' ),
						'description' => __( 'Allows this coupon to be used by Referrers', 'referral-system-for-woocommerce' )
					) );
					?>
                </div>
                <div class="options_group">
					<?php
					woocommerce_wp_text_input( array(
						'id'          => 'trswc_reward_fixed_value',
						'label'       => __( 'Fixed value Reward' . ' (' . get_woocommerce_currency_symbol() . ')', 'referral-system-for-woocommerce' ),
						'description' => __( 'Rewards the Referrer a fixed value if a Referee purchases', 'referral-system-for-woocommerce' ),
						'data_type'   => 'price'
					) );
					?>
                </div>
            </div>
			<?php
		}

		public function add_coupon_referral_tab( $tabs ) {
			$tabs['trswc'] = array(
				'label'  => __( 'Referral', 'referral-system-for-woocommerce' ),
				'target' => 'referral_coupon_data',
				'class'  => 'referral_coupon_data',
			);

			return $tabs;
		}

	}
}