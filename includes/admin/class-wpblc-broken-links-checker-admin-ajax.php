<?php
/**
 * The WPBLC_Broken_Links_Checker_Admin_Ajax class.
 *
 * @package WPBLC_Broken_Links_Checker/Admin
 * @author  
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WPBLC_Broken_Links_Checker_Admin_Ajax' ) ) :

	/**
	 * Admin Ajax.
	 *
	 * Calls admin Ajax.
	 *
	 * @since   1.0.0
	 */
	class WPBLC_Broken_Links_Checker_Admin_Ajax {
		/**
		 * The constructor.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function __construct() {
			add_action( 'wp_ajax_wpblc_broken_links_manual_scan', array( $this, 'manual_scan' ) );
		}

		/**
		 * Manual scan.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function manual_scan() {
			// Check for nonce security.
			if ( ! wp_verify_nonce( $_POST['nonce'], 'wpblc_broken_links_checker' ) ) {
				wp_die( esc_html__( 'Cheatin&#8217; huh?', 'reviewshake-widgets' ) );
			}

			if ( isset( $_POST ) && isset( $_POST['action'] ) && 'wpblc_broken_links_manual_scan' === $_POST['action'] ) {
				$scan = WPBLC_Broken_Links_Checker_Utilities::get_content_to_scan();

				wp_send_json_success( 1 );
				die;
			}

			die();
		}
	}

	return new WPBLC_Broken_Links_Checker_Admin_Ajax();

endif;
