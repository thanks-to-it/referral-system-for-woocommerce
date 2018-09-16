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

		public function apply_discount_programmatically() {
			$referral_code = WC()->session->get( 'trswc_referral_code' );
			if ( empty( $referral_code ) ) {
				return;
			}
			$coupon_code = WC()->session->get( 'trswc_coupon_code' );
			if ( ! WC()->cart->has_discount( $coupon_code ) ) {
				WC()->cart->add_discount( $coupon_code );
			}
		}

		public function save_referral_code_data_in_wc_session() {
			$referral_code_query_string = $this->get_referral_code_from_query_string();
			if (
				empty( $referral_code_query_string ) ||
				! $this->validate_referral_code( $referral_code_query_string )
			) {
				return;
			}

			$coupon_code_decoded = $this->decode( $referral_code_query_string );
			$wc_coupon_id        = $coupon_code_decoded['coupon_id'];
			$wc_coupon           = new \WC_Coupon( $wc_coupon_id );
			$referrer_id         = $coupon_code_decoded['referrer_id'];

			WC()->session->set( 'trswc_referral_code', $referral_code_query_string );
			WC()->session->set( 'trswc_coupon_code', $wc_coupon->get_code() );
			WC()->session->set( 'trswc_coupon_id', $wc_coupon->get_id() );
			WC()->session->set( 'trswc_referrer_id', $referrer_id );

			wc_add_notice( __( "The referral code <strong>{$referral_code_query_string}</strong> has been successfully applied!", 'referral-system-for-woocommerce' ), 'success' );

			//WC()->session->__unset( 'sess_variable_name' );
		}

		public function get_referral_code_from_query_string() {
			if ( ! isset( $_REQUEST['referral_code'] ) ) {
				return '';
			}
			$referral_code_query_string = $_REQUEST['referral_code'];
			if ( empty( $referral_code_query_string ) ) {
				return '';
			}
			return $referral_code_query_string;
		}

		public function remove_coupon_html_if_zero_discount( $html, \WC_Coupon $coupon ) {
			$referral_code = WC()->session->get( 'trswc_referral_code' );
			if (
				empty( $referral_code ) ||
				'yes' !== get_post_meta( $coupon->get_id(), 'trswc_referral_enable', true )
			) {
				return $html;
			}
			$amount = $coupon->get_amount();
			if ( empty( $amount ) ) {
				$html = '';
			}
			return $html;
		}

		public function mask_coupon_name( $label, \WC_Coupon $coupon ) {
			$referral_code = WC()->session->get( 'trswc_referral_code' );
			if (
				empty( $referral_code ) ||
				'yes' !== get_post_meta( $coupon->get_id(), 'trswc_referral_enable', true )
			) {
				return $label;
			}
			$label = sprintf( esc_html__( 'Referral Code: %s', 'referral-system-for-woocommerce' ), $referral_code );
			return $label;
		}

		public function show_referral_code_data_on_admin_order( \WC_Order $order ) {
			$referrer_id = get_post_meta( $order->get_id(), '_trswc_referrer_id', true );
			if ( empty( $referrer_id ) ) {
				return;
			}

			$referral_code      = get_post_meta( $order->get_id(), '_trswc_referral_code', true );
			$coupon_id          = get_post_meta( $order->get_id(), '_trswc_coupon_id', true );
			$fixed_value_reward = get_post_meta( $coupon_id, 'trswc_reward_fixed_value', true );
			$referrer           = get_user_by( 'id', $referrer_id );
			?>
            <div class="order_data_column trswc-order-data-column">
                <h3><?php _e( 'Referral' ); ?></h3>
                <p>
                    <strong><?php echo __( 'Referrer', 'referral-system-for-woocommerce' ); ?>:</strong>
	                <a href="<?php echo get_edit_user_link( $referrer_id ) ?>"><?php echo esc_html( $referrer->display_name  ); ?></a> <?php echo esc_html( $referrer->user_email ); ?>
                </p>
                <p>
                    <strong><?php echo __( 'Referral Code', 'referral-system-for-woocommerce' ); ?>:</strong>
					<?php echo esc_html( $referral_code ); ?>
                </p>
                <p>
                    <strong><?php echo __( 'Fixed Value Reward', 'referral-system-for-woocommerce' ); ?>:</strong>
					<?php echo wc_price( $fixed_value_reward ); ?>
                </p>
            </div>
            <style>
                .trswc-order-data-column strong {
                    display: block;
                }
            </style>
			<?php
		}

		public function save_referral_code_data_on_order_creation( $order, $data ) {
			$referral_code = WC()->session->get( 'trswc_referral_code' );
			if ( empty( $referral_code ) ) {
				return;
			}

			$referral_code  = WC()->session->get( 'trswc_referral_code' );
			$wc_coupon_code = WC()->session->get( 'trswc_coupon_code' );
			$wc_coupon_id   = WC()->session->get( 'trswc_coupon_id' );
			$referrer_id    = WC()->session->get( 'trswc_referrer_id' );

			$order->update_meta_data( '_trswc_referral_code', $referral_code );
			$order->update_meta_data( '_trswc_coupon_code', $wc_coupon_code );
			$order->update_meta_data( '_trswc_coupon_id', $wc_coupon_id );
			$order->update_meta_data( '_trswc_referrer_id', $referrer_id );
		}

		public function validate_referral_code( $referral_code ) {
			$coupon_code_decoded = $this->decode( $referral_code );
			if (
				! is_array( $coupon_code_decoded )
				|| count( $coupon_code_decoded ) < 2 ||
				! is_numeric( $coupon_code_decoded['coupon_id'] ) ||
				! is_numeric( $coupon_code_decoded['referrer_id'] )
			) {
				return false;
			}
			$wc_coupon_id = $coupon_code_decoded['coupon_id'];
			$wc_coupon    = new \WC_Coupon( $wc_coupon_id );
			if ( 'yes' !== get_post_meta( $wc_coupon->get_id(), 'trswc_referral_enable', true ) ) {
				return false;
			}
			return true;
		}

		public function encode( $coupon_id, $referrer_id = - 1 ) {
			$hashids = new \Hashids\Hashids( Encryption::get_salt(), 6, Encryption::get_alphabet() );
			if ( $referrer_id == - 1 ) {
				$referrer_id = get_current_user_id();
			}
			return $hashids->encode( $coupon_id, $referrer_id );
		}

		public function decode( $code ) {
			$hashids = new \Hashids\Hashids( Encryption::get_salt(), 6, Encryption::get_alphabet() );
			$numbers = $hashids->decode( $code );
			return array(
				'coupon_id'   => $numbers[0],
				'referrer_id' => $numbers[1],
			);
		}

	}
}