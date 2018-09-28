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

		public function handle_endpoint_title( $title ) {
			global $wp_query;
			$is_endpoint = isset( $wp_query->query_vars[ $this->tab_id ] );
			if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
				$title = __( 'Referrals', 'referral-system-for-woocommerce' );
			}
			return $title;
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

			$referral        = new Referral();
			$the_query       = $referral->get_commissions_query_from_user_id( get_current_user_id() );
			$total_unpaid    = $referral->get_unpaid_commissions_total_from_user_id( get_current_user_id() );
			$referral_status = new Referral_Status();
			$authenticity    = new Authenticity();
			?>
            <h3>Report</h3>
            <table class="my_account_orders">
                <tr>
                    <thead>
                    <th>Status</span></th>
                    <th>Sum</th>
                    </thead>
                </tr>
                <tbody>
                <tr>
                    <td>Unpaid</td>
                    <td><?php echo wc_price($total_unpaid); ?></td>
                </tr>
                </tbody>
            </table>

			<?php if ( $the_query->have_posts() ) : ?>
                <h3>Referrals</h3>
                <table class="my_account_orders">
                    <tr>
                        <thead>
                        <th>Code</span></th>
                        <th>Reward</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Authenticity</th>
                        <th>Date</th>
                        </thead>
                    </tr>
                    <body>
					<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
						<?php
                            $custom_fields = get_post_custom( get_the_ID() );
						    $status_terms = wp_get_post_terms( get_the_ID(), $referral_status->tax_id,array('fields'=>'names') );
						    $auth_terms = wp_get_post_terms( get_the_ID(), $authenticity->tax_id,array('fields'=>'names') );
						?>
                        <tr>
                            <td><?php echo $custom_fields[ $referral->postmeta['referral_code'] ][0]; ?></td>
                            <td><?php echo wc_price( $custom_fields[ $referral->postmeta['total_reward_value'] ][0] ); ?></td>
                            <td><?php echo $custom_fields[ $referral->postmeta['order_id'] ][0] ; ?></td>
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