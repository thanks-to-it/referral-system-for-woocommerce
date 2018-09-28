<?php
/**
 * Referral System for WooCommerce - My Account > Style
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\My_Account_Style' ) ) {

	class My_Account_Style {
		public static function add_style() {
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

                .woocommerce-MyAccount-navigation-link--referral-codes a:before {
                    content: "\f3ff" !important;
                }

                .woocommerce-MyAccount-navigation-link--referrals a:before {
                    content: "\f362" !important;
                }


                /*.woocommerce-MyAccount-navigation-link--commissions a:before {
                    content: "\f155" !important;
                }*/
            </style>
			<?php
		}
	}
}