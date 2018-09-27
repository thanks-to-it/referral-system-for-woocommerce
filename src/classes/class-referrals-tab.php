<?php
/**
 * Referral System for WooCommerce - My Account > Referrals Tab
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\Referrals_Tab' ) ) {

	class Referrals_Tab {
		public $tab_id = 'referrals';

		function add_endpoint() {
			add_rewrite_endpoint( $this->tab_id, EP_ROOT | EP_PAGES );
		}

		function add_query_vars( $vars ) {
			if ( ! Referrer::is_current_user_referrer() ) {
				return $vars;
			}

			$vars[] = $this->tab_id;
			return $vars;
		}

		function add_menu_item( $items ) {
			if ( ! Referrer::is_current_user_referrer() ) {
				return $items;
			}

			$items[ $this->tab_id ] = __( 'Referrals', 'referral-system-for-woocommerce' );
			return $items;
		}

		function add_content() {
			if ( ! Referrer::is_current_user_referrer() ) {
				return;
			}

			$commission = new Referral();
			$the_query  = $commission->get_commissions_query_from_user_id( get_current_user_id() );
			$referral_status = new Referral_Status();
			$authenticity = new Authenticity();

			//Referral_Coupon_Code::encode( $coupon->get_id(), get_current_user_id() )

			//echo '<h2>Referral Commissions</h2>';
			?>
			<?php if ( $the_query->have_posts() ) : ?>
                <table>
                    <thead>
                    <th>Code</th>
                    <th>Reward</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th>Authenticity</th>
                    <th>Date</th>
                    </thead>
                    <body>
					<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
						<?php
                            $custom_fields = get_post_custom( get_the_ID() );
						    $status_terms = wp_get_post_terms( get_the_ID(), $referral_status->tax_id,array('fields'=>'names') );
						    $auth_terms = wp_get_post_terms( get_the_ID(), $authenticity->tax_id,array('fields'=>'names') );
						?>
                        <tr>
                            <td><?php echo $custom_fields[ $commission->postmeta['referral_code'] ][0]; ?></td>
                            <td><?php echo wc_price( $custom_fields[ $commission->postmeta['total_reward_value'] ][0] ); ?></td>
                            <td><?php echo $custom_fields[ $commission->postmeta['order_id'] ][0] ; ?></td>
                            <td><?php echo implode(', ', $status_terms); ?></td>
                            <td><?php echo implode(', ', $auth_terms); ?></td>
                            <td><?php the_date('Y/m/d'); ?></td>
                        </tr>
					<?php endwhile; ?>
                    </body>
					<?php wp_reset_postdata(); ?>
                </table>
			<?php else : ?>

			<?php endif; ?>
			<?php
		}
	}
}