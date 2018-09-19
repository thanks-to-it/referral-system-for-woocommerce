<?php
/**
 * Referral System for WooCommerce - My Account Tab
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\Referral_Codes_Tab' ) ) {

	class Referral_Codes_Tab {
		function add_endpoint() {
			if ( ! Referrer::is_current_user_referrer() ) {
				return;
			}

			add_rewrite_endpoint( 'referral-codes', EP_ROOT | EP_PAGES );
		}

		function add_query_vars( $vars ) {
			if ( ! Referrer::is_current_user_referrer() ) {
				return $vars;
			}

			$vars[] = 'referral-codes';
			return $vars;
		}

		function add_menu_item( $items ) {
			if ( ! Referrer::is_current_user_referrer() ) {
				return $items;
			}

			$items['referral-codes'] = __( 'Referral Codes', 'referral-system-for-woocommerce' );
			return $items;
		}

		function add_content() {
			if ( ! Referrer::is_current_user_referrer() ) {
				return;
			}

			$referral_coupon = new Referral_Coupon();
			$the_query       = $referral_coupon->get_referral_coupons_query();
			//Referral_Coupon_Code::encode( $coupon->get_id(), get_current_user_id() )

			echo '<h2>Referral Codes</h2>';
			if ( $the_query->have_posts() ) {
				while ( $the_query->have_posts() ) {
					$the_query->the_post();
					$coupon                       = new \WC_Coupon( get_the_title() );
					$referral_coupon_code         = new Referral_Coupon();
					$referral_coupon_code_encoded = $referral_coupon_code->encode( $coupon->get_id(), get_current_user_id() );
					echo "
					<dl class='trswc-dl'>
						<dt>Code:</dt>
						<dd>{$referral_coupon_code_encoded}</dd>
						<dt>URL:</dt>
						<dd>" .
					     add_query_arg( array(
						     $referral_coupon->query_string_variables['referral_code'] => $referral_coupon_code_encoded
					     ), get_home_url() ) .
					     "</dd>
					</dl>
					";
				}
				wp_reset_postdata();
			}
			?>
            <style>
                .trswc-dl {
                    margin: 0;
                    padding: 20px 0 12px 0;
                    border-top: 1px solid rgba(0, 0, 0, .05);
                }

                .trswc-dl dd {
                    margin-bottom: 10px;
                }
            </style>
			<?php
		}
	}
}