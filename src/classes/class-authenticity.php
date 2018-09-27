<?php
/**
 * Referral System for WooCommerce - Authenticity Authenticity
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\RSWC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\RSWC\Authenticity' ) ) {

	class Authenticity {

		public $tax_id = 'trswc-authenticity';
		public $cpt_id = '';
		public $cookies = array(
			'referrer_cookie' => '_trswc_referrer_cookie',
		);

		public $order_postmeta = array(
			'fraud_data' => '_trswc_fraud_data',
		);

		public function get_terms( $args ) {
			$args  = wp_parse_args( $args, array(
				'taxonomy'   => $this->tax_id,
				'hide_empty' => false,
			) );
			$terms = get_terms( $args );
			if ( isset( $args['get_only'] ) && $args['get_only'] == 'id_and_title' ) {
				$terms = wp_list_pluck( $terms, 'name', 'term_id' );
			}
			if ( isset( $args['get_only'] ) && $args['get_only'] == 'slug_and_title' ) {
				$terms = wp_list_pluck( $terms, 'name', 'slug' );
			}
			return $terms;
		}

		public function get_fraud_data_label( $fraud_data_id ) {
			switch ( $fraud_data_id ) {
				case 'same_email':
					return __( 'Referrer and Customer have the same email', 'referral-system-for-woocommerce' );
				break;
				case 'same_ip':
					return __( 'Referral and Customer have the same IP', 'referral-system-for-woocommerce' );
				break;
				case 'found_cookie':
					return __( 'Found a Referrer Cookie', 'referral-system-for-woocommerce' );
				break;
				case 'cookie_match_referrer':
					return __( 'Cookie match Referrer ID', 'referral-system-for-woocommerce' );
				break;
			}
		}

		public function show_admin_order_authenticity_data( \WC_Order $order ) {
			$referral_coupon = new Referral_Coupon();
			$referrer_id     = get_post_meta( $order->get_id(), $referral_coupon->order_postmeta['referrer_id'], true );
			if ( empty( $referrer_id ) ) {
				return;
			}

			$authenticity      = new Authenticity();
			$authenticity_data = get_post_meta( $order->get_id(), $authenticity->order_postmeta['fraud_data'], true );
			$authenticity_data = empty( $authenticity_data ) ? array() : $authenticity_data;
			$reliable_term          = $authenticity->get_reliable_term();
			?>
            <div class="order_data_column trswc-order-data-column">
                <h3><?php _e( 'Referral Authenticity' ); ?></h3>
				<?php if ( empty( $authenticity_data ) || count( $authenticity_data ) == 0 ): ?>
                    <p><?php echo $reliable_term->name ?></p>
				<?php endif; ?>
                <ul>
					<?php foreach ( $authenticity_data as $data ): ?>
                        <li><?php echo $authenticity->get_fraud_data_label( $data ) ?></li>
					<?php endforeach; ?>
                </ul>
            </div>
            <style>
                .trswc-order-data-column ul {
                    list-style: inside;
                    color: red;
                }
            </style>
			<?php
		}

		public function save_fraud_data_on_order( \WC_Order $order, $fraud_data ) {
			if ( ! empty( $fraud_data ) ) {
				$order->update_meta_data( $this->order_postmeta['fraud_data'], array_keys( $fraud_data ) );
			}
		}

		public function get_fraud_detection_data( \WC_Order $order, $referrer_id ) {
			$referral_coupon = new Referral_Coupon();
			$referrer        = new Referrer();
			$referrer_user   = get_user_by( 'ID', $referrer_id );
			$referrer_email  = $referrer_user->user_email;
			$referrer_ip     = get_user_meta( $referrer_id, $referrer->usermeta['ip'], true );
			$referrer_cookie = $referrer->get_cookie();

			// Customer info
			$customer_email = $order->get_billing_email();
			$customer_ip    = $order->get_customer_ip_address();

			$fraud_info = array();

			if ( $customer_email == $referrer_email ) {
				$fraud_info['same_email'] = $this->get_fraud_data_label( 'same_email' );
			}

			if ( $customer_ip == $referrer_ip ) {
				$fraud_info['same_ip'] = __( 'Referral and Customer have the same IP', 'referral-system-for-woocommerce' );
			}

			if ( ! empty( $referrer_cookie ) ) {
				$fraud_info['found_cookie'] = __( 'Found a Referrer Cookie', 'referral-system-for-woocommerce' );

				if ( $referrer->get_referrer_id_from_cookie( $referrer_cookie ) == $referrer_id ) {
					$fraud_info['cookie_match_referrer'] = __( 'Cookie match Referrer ID', 'referral-system-for-woocommerce' );
				}
			}

			return $fraud_info;
		}

		public function get_reliable_term() {
			$term_opt      = get_option( 'trswc_opt_authenticity_reliable', array( 'apparently-reliable' ) );
			$term               = get_term_by( 'slug', $term_opt[0], $this->tax_id );
			return $term;
		}

		public function get_default_terms() {
			return array(
				array(
					'slug'  => 'apparently-reliable',
					'label' => __( 'Apparently Reliable', 'referral-system-for-woocommerce' ),
				),
				array(
					'slug'  => 'possible-fraud',
					'label' => __( 'Possible fraud', 'referral-system-for-woocommerce' ),
				),
				array(
					'slug'  => 'fraud-alert',
					'label' => __( 'Fraud alert', 'referral-system-for-woocommerce' ),
				),
			);
		}

		public function create_initial_terms() {
			$this->register_taxonomy();
			$terms = $this->get_default_terms();
			foreach ( $terms as $term ) {
				if ( term_exists( $term['slug'], $this->tax_id ) == null ) {
					$response = $this->add_term( $term['slug'] );
				}
			}
		}

		public function add_term( $term_slug ) {
			$terms = $this->get_default_terms();
			$term  = wp_list_filter( $terms, array( 'slug' => $term_slug ) );
			$pos   = reset( $term );

			return wp_insert_term(
				$pos['label'],
				$this->tax_id,
				array(
					'slug' => $term_slug,
				)
			);
		}

		function move_taxonomy_menu() {
			add_submenu_page( 'edit.php?trswc=trswc', esc_html__( 'Authenticity', 'referral-system-for-woocommerce' ), esc_html__( 'Authenticity', 'referral-system-for-woocommerce' ), 'manage_categories', 'edit-tags.php?taxonomy=' . $this->tax_id . '' );
		}

		function highlight_taxonomy_parent_menu( $parent_file ) {
			if ( get_current_screen()->taxonomy == $this->tax_id ) {
				$parent_file = 'edit.php?trswc=trswc';
			}
			return $parent_file;
		}

		public function register_taxonomy() {
			// Add new taxonomy, make it hierarchical (like categories)
			$labels = array(
				'name'              => __( 'Authenticity', 'marketplace-for-woocommerce' ),
				'singular_name'     => __( 'Authenticity', 'marketplace-for-woocommerce' ),
				'search_items'      => __( 'Search Authenticity', 'marketplace-for-woocommerce' ),
				'all_items'         => __( 'All Authenticity', 'marketplace-for-woocommerce' ),
				'parent_item'       => __( 'Parent Authenticity', 'marketplace-for-woocommerce' ),
				'parent_item_colon' => __( 'Parent Authenticity:', 'marketplace-for-woocommerce' ),
				'edit_item'         => __( 'Edit Authenticity', 'marketplace-for-woocommerce' ),
				'update_item'       => __( 'Update Authenticity', 'marketplace-for-woocommerce' ),
				'add_new_item'      => __( 'Add New Authenticity', 'marketplace-for-woocommerce' ),
				'new_item_name'     => __( 'New Authenticity Name', 'marketplace-for-woocommerce' ),
				'menu_name'         => __( 'Authenticity', 'marketplace-for-woocommerce' ),
			);

			$args = array(
				'hierarchical'       => true,
				'labels'             => $labels,
				'show_in_menu'       => true,
				//'show_in_menu'       => 'edit.php?trswc=trswc',
				'show_ui'            => true,
				'show_admin_column'  => true,
				'show_in_quick_edit' => true,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'authenticity' ),
			);

			$cpt_id = $this->cpt_id;
			if ( ! empty( $cpt_id ) ) {
				register_taxonomy( $this->tax_id, $cpt_id, $args );
			}
		}
	}
}