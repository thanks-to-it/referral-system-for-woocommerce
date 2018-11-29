<?php
/**
 * Referral System for WooCommerce - My Account > Referral Codes Tab
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
	    public $tab_id='referral-codes';

		function add_endpoint() {
			add_rewrite_endpoint( $this->tab_id, EP_ROOT | EP_PAGES );
		}

		function add_query_vars( $vars ) {
			$referrer = new Referrer();
			if ( ! $referrer->is_current_user_referrer() ) {
				return $vars;
			}

			$vars[] = $this->tab_id;
			return $vars;
		}

		public function handle_endpoint_title( $title ) {
			global $wp_query;
			$is_endpoint = isset( $wp_query->query_vars[ $this->tab_id ] );
			if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
				$title = __( 'Referral Codes', 'referral-system-for-woocommerce' );
			}
			return $title;
		}

		function add_menu_item( $items ) {
			$referrer = new Referrer();
			if ( ! $referrer->is_current_user_referrer() ) {
				return $items;
			}

			$items[$this->tab_id] = __( 'Referral Codes', 'referral-system-for-woocommerce' );
			return $items;
		}

		function add_content() {
			$referrer = new Referrer();
			if ( ! $referrer->is_current_user_referrer() ) {
				return;
			}

			$referral_coupon = new Referral_Coupon();
			$the_query       = $referral_coupon->get_referral_coupons_query();
			//Referral_Coupon_Code::encode( $coupon->get_id(), get_current_user_id() )

			//echo '<h2>Referral Codes</h2>';
			if ( $the_query->have_posts() ) {
				while ( $the_query->have_posts() ) {
					$the_query->the_post();
					global $post;

					$coupon                       = new \WC_Coupon( get_the_ID() );

					$referral_coupon_code         = new Referral_Coupon();
					$referral_coupon_code_encoded = $referral_coupon_code->encode( $coupon->get_id(), get_current_user_id() );

					/*Template::get_template( 'referral-code-box.php', array(
						'referral_coupon_code_encoded' => $referral_coupon_code_encoded
					) );*/
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
			<?php
		}
	}
}