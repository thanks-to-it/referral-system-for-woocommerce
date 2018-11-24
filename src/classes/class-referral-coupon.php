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

		public $postmeta = array(
			'referral_enable'    => '_trswc_referral_enable',
			'reward_fixed_value' => '_trswc_reward_fixed_value',
		);

		public $order_postmeta = array(
			'referrer_id'        => '_trswc_referrer_id',
			'referral_code'      => '_trswc_referral_code',
			'coupon_id'          => '_trswc_coupon_id',
			'coupon_code'        => '_trswc_coupon_code',
			'total_reward_value' => '_trswc_total_reward_value',
		);

		public $wc_session_variables = array(
			'referrer_id'   => '_trswc_referrer_id',
			'referral_code' => '_trswc_referral_code',
			'coupon_id'     => '_trswc_coupon_id',
			'coupon_code'   => '_trswc_coupon_code',
		);

		public $query_string_variables = array(
			'referral_code' => 'referral_code'
		);

		public function apply_discount_programmatically() {
			$referral_code = WC()->session->get( $this->wc_session_variables['referral_code'] );
			if ( empty( $referral_code ) ) {
				return;
			}
			$coupon_code = WC()->session->get( $this->wc_session_variables['coupon_code'] );
			$coupon_id   = WC()->session->get( $this->wc_session_variables['coupon_id'] );
			$referrer_id = WC()->session->get( $this->wc_session_variables['referrer_id'] );
			if (
				! Referrer::is_user_referrer( $referrer_id ) ||
				! $this->is_referral_coupon_valid( $coupon_id )
			) {
				return;
			}

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
			if ( ! $coupon_code_decoded ) {
				return;
			}

			$wc_coupon_id = $coupon_code_decoded['coupon_id'];
			$wc_coupon    = new \WC_Coupon( $wc_coupon_id );
			$referrer_id  = $coupon_code_decoded['referrer_id'];


			if (
				! $this->is_referral_coupon_valid( $wc_coupon->get_code() ) ||
				! Referrer::is_user_referrer( $referrer_id )
			) {
				return;
			}

			WC()->session->set( $this->wc_session_variables['referral_code'], $referral_code_query_string );
			WC()->session->set( $this->wc_session_variables['coupon_code'], $wc_coupon->get_code() );
			WC()->session->set( $this->wc_session_variables['coupon_id'], $wc_coupon->get_id() );
			WC()->session->set( $this->wc_session_variables['referrer_id'], $referrer_id );

			wc_add_notice( __( "The referral code <strong>{$referral_code_query_string}</strong> has been successfully applied!", 'referral-system-for-woocommerce' ), 'success' );
		}

		public function get_referral_coupons_query() {
			$referral_coupon = new Referral_Coupon();

			$the_query = new \WP_Query( array(
				'post_type'      => 'shop_coupon',
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
				'meta_query'     => array(
					array(
						'key'     => $referral_coupon->postmeta['referral_enable'],
						'value'   => 'yes',
						'compare' => '=',
					),
				),
			) );

			return $the_query;
		}

		public function get_referral_code_from_query_string() {
			if ( ! isset( $_REQUEST[ $this->query_string_variables['referral_code'] ] ) ) {
				return '';
			}
			$referral_code_query_string = $_REQUEST[ $this->query_string_variables['referral_code'] ];
			if ( empty( $referral_code_query_string ) ) {
				return '';
			}
			return $referral_code_query_string;
		}

		public function remove_coupon_html_if_zero_discount( $html, \WC_Coupon $coupon ) {
			$referral_code = WC()->session->get( $this->wc_session_variables['referral_code'] );
			if (
				empty( $referral_code ) ||
				'yes' !== get_post_meta( $coupon->get_id(), $this->postmeta['referral_enable'], true )
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
			$referral_code = WC()->session->get( $this->wc_session_variables['referral_code'] );
			if (
				empty( $referral_code ) ||
				'yes' !== get_post_meta( $coupon->get_id(), $this->postmeta['referral_enable'], true )
			) {
				return $label;
			}
			$label = sprintf( esc_html__( 'Referral Code: %s', 'referral-system-for-woocommerce' ), $referral_code );
			return $label;
		}

		public function show_admin_order_referral_code_data( \WC_Order $order ) {
			$referrer_id = get_post_meta( $order->get_id(), $this->order_postmeta['referrer_id'], true );
			if ( empty( $referrer_id ) ) {
				return;
			}

			$referral_code      = get_post_meta( $order->get_id(), $this->order_postmeta['referral_code'], true );
			$coupon_id          = get_post_meta( $order->get_id(), $this->order_postmeta['coupon_id'], true );
			$fixed_value_reward = get_post_meta( $order->get_id(), $this->order_postmeta['total_reward_value'], true );
			$referrer           = get_user_by( 'id', $referrer_id );
			?>
            <div class="order_data_column trswc-order-data-column">
                <h3><?php _e( 'Referral' ); ?></h3>
                <p>
                    <strong><?php echo __( 'Referrer', 'referral-system-for-woocommerce' ); ?>:</strong>
                    <a href="<?php echo get_edit_user_link( $referrer_id ) ?>"><?php echo esc_html( $referrer->display_name ); ?></a>
                </p>
                <p>
                    <strong><?php echo __( 'Referrer email', 'referral-system-for-woocommerce' ); ?>:</strong>
                    <?php echo esc_html( $referrer->user_email ); ?>
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

		public function save_referral_code_data_on_order( \WC_Order $order, $data ) {
			$referral_code = WC()->session->get( $this->wc_session_variables['referral_code'] );
			if ( empty( $referral_code ) ) {
				return;
			}
			$referral_code  = WC()->session->get( $this->wc_session_variables['referral_code'] );
			$wc_coupon_code = WC()->session->get( $this->wc_session_variables['coupon_code'] );
			$wc_coupon_id   = WC()->session->get( $this->wc_session_variables['coupon_id'] );
			$referrer_id    = WC()->session->get( $this->wc_session_variables['referrer_id'] );

			if (
				! WC()->cart->has_discount( $wc_coupon_code ) ||
				! Referrer::is_user_referrer( $referrer_id )
			) {
				return;
			}

			// Calculate reward value
			$referral           = new Referral();
			$total_reward_value = $referral->calculate_total_reward_value( $wc_coupon_id, $order, $referrer_id );

			// save referral coupon data on order
			$order->update_meta_data( $this->order_postmeta['referral_code'], $referral_code );
			$order->update_meta_data( $this->order_postmeta['coupon_code'], $wc_coupon_code );
			$order->update_meta_data( $this->order_postmeta['coupon_id'], $wc_coupon_id );
			$order->update_meta_data( $this->order_postmeta['referrer_id'], $referrer_id );
			$order->update_meta_data( $this->order_postmeta['total_reward_value'], $total_reward_value );

			// Save fraud data on order
			$authenticity = new Authenticity();
			$fraud_info = $authenticity->get_fraud_detection_data( $order, $referrer_id );
			$authenticity->save_fraud_data_on_order( $order, $fraud_info );

			// Delete referral data from wc session
			$this->delete_referral_data_from_wc_session();
		}

		public function delete_referral_data_from_wc_session() {
			WC()->session->__unset( $this->wc_session_variables['referrer_id'] );
			WC()->session->__unset( $this->wc_session_variables['coupon_id'] );
			WC()->session->__unset( $this->wc_session_variables['coupon_code'] );
			WC()->session->__unset( $this->wc_session_variables['referral_code'] );
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
			if ( 'yes' !== get_post_meta( $wc_coupon->get_id(), $this->postmeta['referral_enable'], true ) ) {
				return false;
			}
			return true;
		}

		public function is_referral_coupon_valid( $coupon_id, $method = 'wc_discounts' ) {
			$coupon = new \WC_Coupon( $coupon_id );
			if ( 'yes' !== get_post_meta( $coupon->get_id(), $this->postmeta['referral_enable'], true ) ) {
				return false;
			}

			if ( $method == 'wc_discounts' ) {
				$discounts      = new \WC_Discounts( WC()->cart );
				$valid_response = $discounts->is_coupon_valid( $coupon );
				if ( is_wp_error( $valid_response ) ) {
					return false;
				}
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
			if ( is_array( $numbers ) && count( $numbers ) == 2 ) {
				return array(
					'coupon_id'   => $numbers[0],
					'referrer_id' => $numbers[1],
				);
			} else {
				return false;
			}
		}

	}
}