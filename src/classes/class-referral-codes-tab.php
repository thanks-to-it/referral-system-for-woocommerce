<?php
/**
 * Referral System for WooCommerce - My Account Tab
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Hashids\Hashids;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\Referral_Codes_Tab' ) ) {

	class Referral_Codes_Tab {
		public function init() {
			add_action( 'init', array( $this, 'add_endpoint' ) );

			// 2. Add new query var
			add_filter( 'query_vars', array( $this, 'query_vars' ), 0 );

			// 3. Insert the new endpoint into the My Account menu
			add_filter( 'woocommerce_account_menu_items', array( $this, 'add_link_my_account' ) );

			// 4. Add content to the new endpoint
			add_action( 'woocommerce_account_' . 'referral-codes' . '_endpoint', array( $this, 'add_content' ) );
		}

		function add_endpoint() {
			add_rewrite_endpoint( 'referral-codes', EP_ROOT | EP_PAGES );
		}

		function query_vars( $vars ) {
			$vars[] = 'referral-codes';

			return $vars;
		}

		function add_link_my_account( $items ) {
			$items['referral-codes'] = __( 'Referral Codes', 'referral-system-for-woocommerce' );

			return $items;
		}

		function add_content() {
			$referral_coupon = new Referral_Coupon();
			$the_query       = $referral_coupon->get_referral_coupons_query();

			$message = 'pablo';


			$hashids = new \Hashids\Hashids( Encryption::get_salt(), 6, Encryption::get_alphabet() );
			$id      = $hashids->encode( 1, 2, 3 );
			$numbers = $hashids->decode( $id );

			/*$encrypted = Encryption::encrypt_to_url_param($message);
			$decrypted = Encryption::decrypt_from_url_param($encrypted);
			echo $encrypted;
			echo'<br />';
			echo $decrypted;*/

			//$key = Key::CreateNewRandomKey();
			//$text = Crypto::encrypt('pablo',$key);
			//echo $text;
			//\Crypto::encrypt($secret_data, $key);

			echo '<h3>Referral Codes</h3>';
			echo '<table>';
			// The Loop
			if ( $the_query->have_posts() ) {
				echo '<tr>';
				echo '<td>Code</td>';
				echo '<td>Description</td>';
				echo '<td>Reward</td>';
				echo '</tr>';
				while ( $the_query->have_posts() ) {
					$the_query->the_post();
					$coupon = new \WC_Coupon( get_the_title() );
					echo '<tr>';
					echo '<td>' . $referral_coupon->convert_coupon_code_to_referral_code( $coupon->get_code(), get_current_user_id() ) . '</td>';
					echo '</tr>';
				}

				/* Restore original Post Data */
				wp_reset_postdata();
			} else {
				// no posts found
			}
			echo '</table>';
			//echo '<h3>Premium WooCommerce Support</h3><p>Welcome to the WooCommerce support area. As a premium customer, you can submit a ticket should you have any WooCommerce issues with your website, snippets or customization. <i>Please contact your theme/plugin developer for theme/plugin-related support.</i></p>';
		}
	}
}