<?php
/**
 * Referral System for WooCommerce - Referral Coupon
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC\Admin;

use ThanksToIT\RSWC\Referral_Coupon;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\Admin\Referral_Coupon_Tab' ) ) {

	class Referral_Coupon_Tab {

		public function save_tab_data( $post_id = null ) {
			$referral_coupon = new Referral_Coupon();

			// Enable option
			$option_name = $referral_coupon->postmeta['referral_enable'];
			$option      = isset( $_POST[ $option_name ] ) ? 'yes' : 'no';
			update_post_meta( $post_id, $option_name, $option );

			// Fixed value reward option
			$option_name = $referral_coupon->postmeta['reward_fixed_value'];
			$option      = isset( $_POST[ $option_name ] ) ? $_POST[ $option_name ] : 0;
			$option      = filter_var( $option, FILTER_SANITIZE_NUMBER_FLOAT );
			if ( ! empty( $option ) ) {
				update_post_meta( $post_id, $option_name, $option );
			}
		}

		public function add_tab_panel() {
			$referral_coupon = new Referral_Coupon();
			?>
            <div id="referral_coupon_data" class="panel woocommerce_options_panel">
                <div class="options_group">
					<?php
					woocommerce_wp_checkbox( array(
						'id'          => $referral_coupon->postmeta['referral_enable'],
						'label'       => __( 'Use as a referral coupon', 'referral-system-for-woocommerce' ),
						'description' => __( 'Allows this coupon to be used by Referrers', 'referral-system-for-woocommerce' )
					) );
					?>
                </div>
                <div class="options_group">
					<?php
					woocommerce_wp_text_input( array(
						'id'          => $referral_coupon->postmeta['reward_fixed_value'],
						'label'       => __( 'Fixed value Reward' . ' (' . get_woocommerce_currency_symbol() . ')', 'referral-system-for-woocommerce' ),
						'description' => __( 'Rewards the Referrer a fixed value if a Referee purchases', 'referral-system-for-woocommerce' ),
						'data_type'   => 'price'
					) );
					?>
                </div>
            </div>
			<?php
		}

		public function add_tab( $tabs ) {
			$tabs['trswc'] = array(
				'label'  => __( 'Referral', 'referral-system-for-woocommerce' ),
				'target' => 'referral_coupon_data',
				'class'  => 'referral_coupon_data',
			);

			return $tabs;
		}

	}
}