<?php
/**
 * Referral System for WooCommerce - Referrer User Role
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC;

use ThanksToIT\RSWC\Referrer\Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\Referrer' ) ) {

	class Referrer {

		public $role_referrer = 'trswc_referrer';
		public $role_referrer_pending = 'trswc_referrer_pending';
		public $role_referrer_rejected = 'trswc_referrer_rejected';

		/**
		 * @var Registry
		 */
		public $registry;

		public function __construct() {
			$this->registry = new Registry($this);
		}

		public $usermeta = array(
			'ip'                  => '_trswc_ip',
			'bank_account_name'   => '_trswc_bank_account_name',
			'bank_name'           => '_trswc_bank_name',
			'bank_address'        => '_trswc_bank_address',
			'aba_routing_number'  => '_trswc_aba_routing_number',
			'iban'                => '_trswc_iban',
			'account_holder_name' => '_trswc_account_holder_name',
			'paypal_email'        => '_trswc_paypal_email',
		);

		public function get_cookie() {
			$authenticity = new Authenticity();
			if ( isset( $_COOKIE[ $authenticity->cookies['referrer_cookie'] ] ) ) {
				return $_COOKIE[ $authenticity->cookies['referrer_cookie'] ];
			} else {
				return false;
			}
		}

		public function get_referrer_id_from_cookie( $cookie ) {
			$hashids = new \Hashids\Hashids( Encryption::get_salt(), 6, Encryption::get_alphabet() );
			$numbers = $hashids->decode( $cookie );
			if ( is_array( $numbers ) && count( $numbers ) == 1 ) {
				return $numbers[0];
			} else {
				return false;
			}
		}

		public function save_cookie( $user_login, \WP_User $user ) {
			if ( ! $this->is_user_referrer( $user->ID ) ) {
				return;
			}
			$authenticity = new Authenticity();
			$hashids      = new \Hashids\Hashids( Encryption::get_salt(), 6, Encryption::get_alphabet() );
			$response     = setcookie( $authenticity->cookies['referrer_cookie'], $hashids->encode( $user->ID ), time() + ( 1 * YEAR_IN_SECONDS ), COOKIEPATH, COOKIE_DOMAIN, is_ssl() );
		}

		function save_ip( $user_login, $user ) {
			if ( ! $this->is_user_referrer( $user->ID ) ) {
				return;
			}
			update_user_meta( $user->ID, $this->usermeta['ip'], $this->get_ip() );
		}

		function show_meta_to_chosen_roles( $cmb ) {
			$roles = $cmb->prop( 'show_on_roles', array() );
			if ( empty( $roles ) ) {
				return false;
			}
			global $user_id;
			if ( ! $user_id || empty( $user_id ) ) {
				return false;
			}
			$edited_user = new \WP_User( $user_id );
			$has_role    = array_intersect( (array) $roles, $edited_user->roles );
			return ! empty( $has_role );
		}

		function add_payment_fields() {
			if ( 'yes' !== get_option( 'trswc_opt_referrer_payment_fields_enable', 'yes' ) ) {
				return;
			}

			/**
			 * Metabox for the user profile screen
			 */
			$cmb_user = new_cmb2_box( array(
				'id'               => '_trswc_referrer_fields',
				'title'            => esc_html__( 'User Profile Metabox', 'referral-system-for-woocommerce' ), // Doesn't output for user boxes
				'object_types'     => array( 'user' ), // Tells CMB2 to use user_meta vs post_meta
				'show_names'       => true,
				'show_on_roles'    => array( $this->role_referrer_pending, $this->role_referrer ),
				'new_user_section' => 'add-new-user', // where form will show on new user page. 'add-existing-user' is only other valid option.
				'show_on_cb'       => array( $this, 'show_meta_to_chosen_roles' ),
			) );
			$cmb_user->add_field( array(
				'name'     => esc_html__( 'Payment Details', 'referral-system-for-woocommerce' ),
				//'desc'     => esc_html__( 'field description (optional)', 'referral-system-for-woocommerce' ),
				'id'       => '_trswc_title_1',
				'type'     => 'title',
				'on_front' => false,
			) );
			$cmb_user->add_field( array(
				'name'     => esc_html__( 'Bank Account Name', 'referral-system-for-woocommerce' ),
				'id'       => $this->usermeta['bank_account_name'],
				'type'     => 'text',
				'on_front' => false,
			) );
			$cmb_user->add_field( array(
				'name'     => esc_html__( 'Bank Name', 'referral-system-for-woocommerce' ),
				'id'       => $this->usermeta['bank_name'],
				'type'     => 'text',
				'on_front' => false,
			) );
			$cmb_user->add_field( array(
				'name'     => esc_html__( 'Bank Address', 'referral-system-for-woocommerce' ),
				'id'       => $this->usermeta['bank_address'],
				'type'     => 'text',
				'on_front' => false,
			) );
			$cmb_user->add_field( array(
				'name'     => esc_html__( 'ABA Routing Number', 'referral-system-for-woocommerce' ),
				'id'       => $this->usermeta['aba_routing_number'],
				'type'     => 'text',
				'on_front' => false,
			) );
			$cmb_user->add_field( array(
				'name'     => esc_html__( 'IBAN', 'referral-system-for-woocommerce' ),
				'id'       => $this->usermeta['iban'],
				'type'     => 'text',
				'on_front' => false,
			) );
			$cmb_user->add_field( array(
				'name'     => esc_html__( 'Account Holder Name', 'referral-system-for-woocommerce' ),
				'id'       => $this->usermeta['account_holder_name'],
				'type'     => 'text',
				'on_front' => false,
			) );
			$cmb_user->add_field( array(
				'name'     => esc_html__( 'Paypal Email', 'referral-system-for-woocommerce' ),
				'id'       => $this->usermeta['paypal_email'],
				'type'     => 'text',
				'on_front' => false,
			) );
		}

		function get_ip() {
			foreach ( array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' ) as $key ) {
				if ( array_key_exists( $key, $_SERVER ) === true ) {
					foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) {
						if ( filter_var( $ip, FILTER_VALIDATE_IP ) !== false ) {
							return $ip;
						}
					}
				}
			}
		}

		public function is_current_user_referrer() {
			$current_user = wp_get_current_user();
			$user_id      = $current_user->ID;
			return $this->is_user_referrer( $user_id );
		}

		public function is_user_referrer( $user_id ) {
			if ( $user_id == 0 ) {
				return false;
			}
			$current_user = get_user_by( 'ID', $user_id );
			if ( in_array( $this->role_referrer, $current_user->roles ) ) {
				return true;
			} else {
				return false;
			}
		}

		public $user_caps = array(
			"read"    => true,
			'level_0' => true,
			//'edit_alg_mpwc_commissions' => true,
		);

		public function add_role( $role, $role_name ) {
			if ( get_role( $role ) ) {
				remove_role( $role );
			}
			add_role( $role, $role_name, $this->user_caps );
		}

		public function add_roles() {
			$this->add_role( $this->role_referrer, sanitize_text_field( __( 'Referrer', 'referral-system-for-woocommerce' ) ) );
			$this->add_role( $this->role_referrer_pending, sanitize_text_field( __( 'Referrer - Pending', 'referral-system-for-woocommerce' ) ) );
			$this->add_role( $this->role_referrer_rejected, sanitize_text_field( __( 'Referrer - Rejected', 'referral-system-for-woocommerce' ) ) );
		}
	}
}