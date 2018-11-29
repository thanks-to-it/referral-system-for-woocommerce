<?php
/**
 * Referral System for WooCommerce - Referrer Registry
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC\Referrer;

use ThanksToIT\RSWC\Referrer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\Referrer\Registry' ) ) {

	class Registry {
		/**
		 * @var Referrer
		 */
		public $referrer;

		public function __construct( Referrer $referrer ) {
			$this->referrer = $referrer;
		}

		public function send_email_to_admin_regarding_new_referrers( $user_id, $role ) {
			$user      = get_user_by( 'id', $user_id );
			$user_link = add_query_arg( 'user_id', $user_id, self_admin_url( 'user-edit.php' ) );
			if ( $role == $this->referrer->role_referrer ) {
				$subject = __( 'New Referrer', 'referral-system-for-woocommerce' );
				$message = wc_get_template_html( 'emails/email-header.php', array( 'email_heading' => $subject ) );
				$message .= '<p>' . sprintf( __( '<a href="%s"><strong>%s</strong></a> is a new Referrer user', 'referral-system-for-woocommerce' ), $user_link, $user->display_name ) . '</p>';
			} elseif ( $role == $this->referrer->role_referrer_pending ) {
				$subject = __( 'New Referrer Pending', 'referral-system-for-woocommerce' );
				$message = wc_get_template_html( 'emails/email-header.php', array( 'email_heading' => $subject ) );
				$message .= '<p>' . sprintf( __( '<a href="%s"><strong>%s</strong></a> wants to become a Referrer user', 'referral-system-for-woocommerce' ), $user_link, $user->display_name ) . '</p>';
			}

			$mail_to = get_option( 'admin_email' );
			$message .= wc_get_template_html( 'emails/email-footer.php' );
			wc_mail( $mail_to, $subject, $message );
		}

		public function change_user_role_to_referrer( $user_id ) {
			if (
				! isset( $_POST['trswc_apply_for_referrer'] ) ||
				! filter_var( $_POST['trswc_apply_for_referrer'], FILTER_VALIDATE_BOOLEAN )
			) {
				return;
			}
			$automatic_approval = filter_var( get_option( 'trswc_opt_referrer_register_auto_approval', 'no' ), FILTER_VALIDATE_BOOLEAN );
			$role               = $automatic_approval ? $this->referrer->role_referrer : $this->referrer->role_referrer_pending;

			if ( 'add' === get_option( 'trswc_opt_referrer_role_method', 'replace' ) ) {
				$user = get_userdata( $user_id );
				$user->add_role( $role );
			} elseif ( 'replace' === get_option( 'trswc_opt_referrer_role_method', 'replace' ) ) {
				$user_data       = new \StdClass();
				$user_data->ID   = $user_id;
				$user_data->role = $role;
				wp_update_user( $user_data );
			}

			do_action( 'trswc_change_user_role_to_referrer', $user_id, $role );
		}

		function add_registration_checkbox() {
			if ( current_filter() == 'woocommerce_edit_account_form' ) {
				if ( current_user_can( $this->referrer->role_referrer ) || current_user_can( $this->referrer->role_referrer_pending ) || current_user_can( $this->referrer->role_referrer_rejected ) ) {
					return;
				}
			}
			?>
            <div style="margin-bottom:35px;">
                <label class="woocommerce-form__label woocommerce-form__label-for-checkbox">
                    <input class="woocommerce-form__input woocommerce-form__input-checkbox inline"
                           name="trswc_apply_for_referrer" type="checkbox" id="trswc_apply_for_referrer"/>
                    <span><?php echo esc_html( sanitize_text_field( get_option( 'trswc_opt_referrer_register_text' ), __( 'Become a Referrer', 'referral-system-for-woocommerce' ) ) ); ?></span>
                </label>
            </div>
			<?php
		}

	}
}