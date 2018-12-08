<?php
/**
 * Referral System for WooCommerce - Admin Settings
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC\Admin;

use ThanksToIT\RSWC\Authenticity;
use ThanksToIT\RSWC\Referral_Status;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\Admin\Admin_Settings' ) ) {

	class Admin_Settings extends \WC_Settings_Page {

		/**
		 * Setup settings class
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function __construct() {
			$this->id    = 'trswc';
			$this->label = __( 'Referral', 'referral-system-for-woocommerce' );

			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
			add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );

			add_filter( 'woocommerce_get_settings_' . 'trswc', array( $this, 'add_fraud_detection_options' ) );
			add_filter( 'woocommerce_get_settings_' . 'trswc', array( $this, 'add_authenticity_options' ) );
		}

		public function add_authenticity_options( $options ) {
			$detection_index = wp_list_filter( $options, array( 'type' => 'sectionend', 'id' => 'trswc_opt_authenticity' ) );
			reset( $detection_index );
			$first_key = key( $detection_index );

			$authenticity = new Authenticity();
			//$methods       = $authenticity->get_fraud_detection_methods();
			$valid_methods = $this->get_valid_fraud_methods();
			$new_methods   = array();
			foreach ( $valid_methods as $valid_method_id => $valid_method_friendly_id ) {
				$method        = $authenticity->get_fraud_detection_method( $valid_method_id );
				$new_option    = array(
					'name'    => $valid_method_friendly_id,
					'type'    => 'select',
					'class'   => 'wc-enhanced-select',
					'options' => $authenticity->get_terms( array( 'get_only' => 'slug_and_title' ) ),
					'default' => 'possible-fraud',
					'desc'    => $method['description'],
					'id'      => 'trswc_opt_auto_auth_' . $valid_method_id,
				);
				$new_methods[] = $new_option;
			}

			array_splice( $options, $first_key, 0, $new_methods );
			return $options;
		}

		public function add_fraud_detection_options( $options ) {
			$detection_index = wp_list_filter( $options, array( 'type' => 'sectionend', 'id' => 'trswc_opt_fraud_detection_methods' ) );
			reset( $detection_index );
			$first_key = key( $detection_index );

			$authenticity = new Authenticity();
			$methods      = $authenticity->get_fraud_detection_methods();
			$new_methods  = array();
			foreach ( $methods as $method ) {
				$new_option    = array(
					'name'    => $method['friendly_id'],
					'type'    => 'checkbox',
					'default' => 'yes',
					'desc'    => $method['description'],
					'id'      => 'trswc_opt_fraud_method_' . $method['id'],
				);
				$new_methods[] = $new_option;
			}

			array_splice( $options, $first_key, 0, $new_methods );
			return $options;
		}

		public function get_status_terms() {
			$status = new Referral_Status();
			$terms  = $status->get_terms( array( 'get_only' => 'slug_and_title' ) );
			return $terms;
		}

		public function get_authenticity_terms() {
			$status = new Authenticity();
			$terms  = $status->get_terms( array( 'get_only' => 'slug_and_title' ) );
			return $terms;
		}

		public function get_authenticity_admin_url() {
			$status = new Authenticity();
			return admin_url( "edit-tags.php?taxonomy={$status->tax_id}" );
		}

		public function get_status_admin_url() {
			$status = new Referral_Status();
			return admin_url( "edit-tags.php?taxonomy={$status->tax_id}" );
		}

		/**
		 * Get sections
		 *
		 * @return array
		 */
		public function get_sections() {

			$sections = array(
				'' => __( 'General', 'referral-system-for-woocommerce' ),
				//'second' => __( 'Section 2', 'referral-system-for-woocommerce' )
			);

			return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
		}

		public function get_valid_fraud_methods() {
			$authenticity  = new Authenticity();
			$methods       = $authenticity->get_fraud_detection_methods();
			$valid_methods = array();
			foreach ( $methods as $method ) {
				$method_opt = get_option( 'trswc_opt_fraud_method_' . $method['id'], 'yes' );
				if ( 'yes' === $method_opt ) {
					$valid_methods[ $method['id'] ] = $method['friendly_id'];
				}
			}
			return $valid_methods;
		}

		/**
		 * Get settings array
		 *
		 * @since 1.0.0
		 *
		 * @param string $current_section Optional. Defaults to empty string.
		 *
		 * @return array Array of settings
		 */
		public function get_settings( $current_section = '' ) {

			if ( 'second' == $current_section ) {

				/**
				 * Filter Plugin Section 2 Settings
				 *
				 * @since 1.0.0
				 *
				 * @param array $settings Array of the plugin settings
				 */
				/*$settings = apply_filters( 'myplugin_section2_settings', array(

					array(
						'name' => __( 'Group 1', 'referral-system-for-woocommerce' ),
						'type' => 'title',
						'desc' => '',
						'id'   => 'myplugin_group1_options',
					),

					array(
						'type'    => 'checkbox',
						'id'      => 'myplugin_checkbox_1',
						'name'    => __( 'Do a thing?', 'referral-system-for-woocommerce' ),
						'desc'    => __( 'Enable to do something', 'referral-system-for-woocommerce' ),
						'default' => 'no',
					),

					array(
						'type' => 'sectionend',
						'id'   => 'myplugin_group1_options'
					),

					array(
						'name' => __( 'Group 2', 'referral-system-for-woocommerce' ),
						'type' => 'title',
						'desc' => '',
						'id'   => 'myplugin_group2_options',
					),

					array(
						'type'     => 'select',
						'id'       => 'myplugin_select_1',
						'name'     => __( 'What should happen?', 'referral-system-for-woocommerce' ),
						'options'  => array(
							'something' => __( 'Something', 'referral-system-for-woocommerce' ),
							'nothing'   => __( 'Nothing', 'referral-system-for-woocommerce' ),
							'idk'       => __( 'IDK', 'referral-system-for-woocommerce' ),
						),
						'class'    => 'wc-enhanced-select',
						'desc_tip' => __( 'Don\'t ask me!', 'referral-system-for-woocommerce' ),
						'default'  => 'idk',
					),

					array(
						'type' => 'sectionend',
						'id'   => 'myplugin_group2_options'
					),

				) );*/

			} else {

				/**
				 * Filter Plugin Section 1 Settings
				 *
				 * @since 1.0.0
				 *
				 * @param array $settings Array of the plugin settings
				 */
				$settings = apply_filters( 'trswc_settings_general', array(

					array(
						'name' => __( 'Referral System General Options', 'referral-system-for-woocommerce' ),
						'type' => 'title',
						//'desc' => __( 'General Options', 'referral-system-for-woocommerce' ),
						'id'   => 'trswc_opt_general',
					),
					array(
						'type'    => 'checkbox',
						'id'      => 'trswc_opt_enable',
						'name'    => __( 'Enable Plugin', 'referral-system-for-woocommerce' ),
						'desc'    => sprintf( __( 'Enables %s plugin', 'referral-system-for-woocommerce' ), '<strong>' . __( 'Referral System for WooCommerce', 'referral-system-for-woocommerce' ) . '</strong>' ),
						//'class'    => 'wc-enhanced-select',
						'default' => 'yes',
					),
					array(
						'type'     => 'multiselect',
						'id'       => 'trswc_opt_referral_blocking',
						'name'     => __( 'Referral Blocking', 'referral-system-for-woocommerce' ),
						'desc'     => __( 'Prevents a referral from being created in case it matches a fraud detection method', 'referral-system-for-woocommerce' ),
						'desc_tip' => __( 'Leave it empty if you do not want to block referrals', 'referral-system-for-woocommerce' ),
						'options'  => $this->get_valid_fraud_methods(),
						'class'    => 'wc-enhanced-select',
						'default'  => array( 'same_email' ),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'trswc_opt_general'
					),

					// Referrer
					array(
						'name' => __( 'Referrer', 'referral-system-for-woocommerce' ),
						'type' => 'title',
						'desc' => __( 'A person who refers other users', 'referral-system-for-woocommerce' ),
						'id'   => 'trswc_opt_referrer',
					),
					array(
						'type'    => 'checkbox',
						'id'      => 'trswc_opt_referrer_payment_fields_enable',
						'name'    => __( 'Profile Payment Fields', 'referral-system-for-woocommerce' ),
						'desc'    => __( 'Display payment fields on profile page for referrer users', 'referral-system-for-woocommerce' ),
						'desc_tip'=> __( 'These fields are optional but will help you to know how to pay referrers individually. Some fields would be bank account, paypal info and so on', 'referral-system-for-woocommerce' ),
						//'class'    => 'wc-enhanced-select',
						'default' => 'yes',
					),
					array(
						'type'    => 'checkbox',
						'id'      => 'trswc_opt_referrer_register_auto_approval',
						'name'    => __( 'Automatic Approval', 'referral-system-for-woocommerce' ),
						'desc'    => __( 'After users confirm they want to become referrers, they will be automatically accepted as such, bypassing the pending status', 'referral-system-for-woocommerce' ),
						//'class'    => 'wc-enhanced-select',
						'default' => 'no',
					),
					array(
						'type'     => 'select',
						'id'       => 'trswc_opt_referrer_role_method',
						'name'     => __( 'Automatic Approval Method', 'referral-system-for-woocommerce' ),
						'desc'     => __( 'How the Referrer role will be automatically set to users, replacing their old role or adding it', 'referral-system-for-woocommerce' ),
						'desc_tip' => __( 'You should set to "Add" if you want to preserve their old role.', 'referral-system-for-woocommerce' ),
						'options'  => array(
							'add'     => __( 'Add', 'referral-system-for-woocommerce' ),
							'replace' => __( 'Replace', 'referral-system-for-woocommerce' ),
						),
						'class'    => 'wc-enhanced-select',
						'default'  => 'replace',
					),
					array(
						'type'    => 'text',
						'id'      => 'trswc_opt_referrer_register_text',
						'name'    => __( 'Registration Checkbox Text', 'referral-system-for-woocommerce' ),
						'desc'    => __( 'Checkbox Text displayed on <strong>My Account > Account Details</strong> page to users who want to become referrers', 'referral-system-for-woocommerce' ),
						'desc_tip'=> __( 'Leaving it empty will remove it from being displayed', 'referral-system-for-woocommerce' ),
						//'class'    => 'wc-enhanced-select',
						'default' => __( 'Become a Referrer', 'referral-system-for-woocommerce' ),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'trswc_opt_referrer'
					),

					// Status
					array(
						'name' => __( 'Status', 'referral-system-for-woocommerce' ),
						'type' => 'title',
						'desc' => sprintf( __( 'Options regarding <a href="%s">Referral Status</a>.', 'referral-system-for-woocommerce' ), $this->get_status_admin_url() ) . '<br />' . __( 'You are free to create as many Status you want, but at least 3 are recommended (paid, unpaid, rejected)', 'referral-system-for-woocommerce' ),
						'id'   => 'trswc_opt_status',
					),
					array(
						'type'    => 'select',
						'id'      => 'trswc_opt_status_default',
						'name'    => __( 'Default Commission Status', 'referral-system-for-woocommerce' ),
						'desc'    => __( 'Status after a commission is automatically created', 'referral-system-for-woocommerce' ),
						'options' => $this->get_status_terms(),
						'class'   => 'wc-enhanced-select',
						'default' => array( 'unpaid' ),
					),

					/*array(
						'type'    => 'select',
						'id'      => 'trswc_opt_status_paid',
						'name'    => __( 'Paid', 'referral-system-for-woocommerce' ),
						'desc'    => __( 'After a referral has been paid', 'referral-system-for-woocommerce' ),
						'options' => $this->get_status_terms(),
						'class'   => 'wc-enhanced-select',
						'default' => array( 'paid' ),
					),
					array(
						'type'    => 'select',
						'id'      => 'trswc_opt_status_unpaid',
						'name'    => __( 'Unpaid', 'referral-system-for-woocommerce' ),
						'desc'    => __( 'Before a referral has been paid', 'referral-system-for-woocommerce' ),
						'options' => $this->get_status_terms(),
						'class'   => 'wc-enhanced-select',
						'default' => array( 'unpaid' ),
					),
					array(
						'type'    => 'select',
						'id'      => 'trswc_opt_status_rejected',
						'name'    => __( 'Rejected', 'referral-system-for-woocommerce' ),
						'desc'    => __( 'If a referral is considered a fraud and is not going to be paid', 'referral-system-for-woocommerce' ),
						'options' => $this->get_status_terms(),
						'class'   => 'wc-enhanced-select',
						'default' => array( 'rejected' ),
					),*/
					array(
						'type' => 'sectionend',
						'id'   => 'trswc_opt_status'
					),

					// Fraud detection methods
					array(
						'name' => __( 'Fraud Detection methods', 'referral-system-for-woocommerce' ),
						'type' => 'title',
						'desc' => __( 'Fraud detection methods used to validate referrals', 'referral-system-for-woocommerce' ),
						'id'   => 'trswc_opt_fraud_detection_methods',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'trswc_opt_fraud_detection_methods'
					),

					// Authenticity
					array(
						'name' => __( 'Authenticity', 'referral-system-for-woocommerce' ),
						'type' => 'title',
						'desc' => sprintf( __( '<a href="%s">Referral Authenticity</a> can suggest if a Referral can be considered trustworthy and it is automatically set from fraud detection methods when a new Referral is created.', 'referral-system-for-woocommerce' ), $this->get_authenticity_admin_url() ) . '<br />' . __( 'You are free to create as many Authenticity terms you wish, but at least 2 are required (for valid referrals and for possible frauds)', 'referral-system-for-woocommerce' ),
						//'desc' => sprintf( __( 'Options regarding <a href="%s">Referral Authenticity</a> suggested by fraud detection methods.', 'referral-system-for-woocommerce' ), $this->get_authenticity_admin_url() ) . '<br />' . __( 'You are free to create as many Authenticity terms you wish, but at least 2 are required (for valid referrals and for possible frauds)', 'referral-system-for-woocommerce' ),
						'id'   => 'trswc_opt_authenticity',
					),
					array(
						'type'    => 'select',
						'id'      => 'trswc_opt_auto_auth_reliable',
						'name'    => __( 'Apparently Reliable', 'referral-system-for-woocommerce' ),
						'desc'    => __( "When a referral doesn't match none of the fraud detection methods", 'referral-system-for-woocommerce' ),
						'options' => $this->get_authenticity_terms(),
						'class'   => 'wc-enhanced-select',
						'default' => array( 'apparently-reliable' ),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'trswc_opt_authenticity'
					),
				) );


			}

			/**
			 * Filter MyPlugin Settings
			 *
			 * @since 1.0.0
			 *
			 * @param array $settings Array of the plugin settings
			 */
			return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );

		}


		/**
		 * Output the settings
		 *
		 * @since 1.0
		 */
		public function output() {

			global $current_section;

			$settings = $this->get_settings( $current_section );
			\WC_Admin_Settings::output_fields( $settings );
		}


		/**
		 * Save settings
		 *
		 * @since 1.0
		 */
		public function save() {

			global $current_section;

			$settings = $this->get_settings( $current_section );
			\WC_Admin_Settings::save_fields( $settings );
		}
	}
}