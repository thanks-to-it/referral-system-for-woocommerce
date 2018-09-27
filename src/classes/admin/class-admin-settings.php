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
		}

		public function get_status_terms() {
			$status = new Referral_Status();
			$terms  = $status->get_terms( array( 'get_only' => 'slug_and_title' ) );
			return $terms;
		}

		public function get_authenticity_terms(){
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
						'type' => 'sectionend',
						'id'   => 'trswc_opt_general'
					),

					// Status
					array(
						'name' => __( 'Status', 'referral-system-for-woocommerce' ),
						'type' => 'title',
						'desc' => sprintf( __( 'Options regarding <a href="%s">Referral Status</a>', 'referral-system-for-woocommerce' ), $this->get_status_admin_url() ),
						'id'   => 'trswc_opt_status',
					),
					array(
						'type'    => 'select',
						'id'      => 'trswc_opt_status_paid',
						'name'    => __( 'Paid', 'referral-system-for-woocommerce' ),
						'desc'    => __( 'After the referral has been paid', 'referral-system-for-woocommerce' ),
						'options' => $this->get_status_terms(),
						'class'   => 'wc-enhanced-select',
						'default' => array('paid'),
					),
					array(
						'type'    => 'select',
						'id'      => 'trswc_opt_status_unpaid',
						'name'    => __( 'Unpaid', 'referral-system-for-woocommerce' ),
						'desc'    => __( 'Before the referral has been paid', 'referral-system-for-woocommerce' ),
						'options' => $this->get_status_terms(),
						'class'   => 'wc-enhanced-select',
						'default' => array('unpaid'),
					),
					array(
						'type'    => 'select',
						'id'      => 'trswc_opt_status_rejected',
						'name'    => __( 'Rejected', 'referral-system-for-woocommerce' ),
						'desc'    => __( 'If the referral is considered a fraud and is not going to be paid', 'referral-system-for-woocommerce' ),
						'options' => $this->get_status_terms(),
						'class'   => 'wc-enhanced-select',
						'default' => array('rejected'),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'trswc_opt_status'
					),

					// Authenticity
					array(
						'name' => __( 'Authenticity', 'referral-system-for-woocommerce' ),
						'type' => 'title',
						'desc' => sprintf(__( 'Options regarding <a href="%s">Referral Authenticity</a>, including fraud detection methods', 'referral-system-for-woocommerce' ),$this->get_authenticity_admin_url()),
						'id'   => 'trswc_opt_authenticity',
					),
					array(
						'type'    => 'select',
						'id'      => 'trswc_opt_authenticity_reliable',
						'name'    => __( 'Apparently Reliable', 'referral-system-for-woocommerce' ),
						'desc'    => __( "When a referral doesn't match none of the fraud detection methods", 'referral-system-for-woocommerce' ),
						'options' => $this->get_authenticity_terms(),
						'class'   => 'wc-enhanced-select',
						'default' => array('apparently-reliable'),
					),
					apply_filters('trswc_admin_authenticity_term_options',array()),
					array(
						'type'    => 'multiselect',
						'id'      => 'trswc_opt_referral_blocking',
						'name'    => __( 'Referral Blocking', 'referral-system-for-woocommerce' ),
						'desc'    => __( 'Prevents referrals from being created in case it matches a fraud detection method', 'referral-system-for-woocommerce' ),
						'desc_tip'=> __( 'Leave it empty if you do not want to block referrals', 'referral-system-for-woocommerce' ),
						'options' => array( 'same_email' => __( 'Referrer and Customer have the same email', 'referral-system-for-woocommerce' ) ),
						'class'   => 'wc-enhanced-select',
						'default' => array('same_email'),
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