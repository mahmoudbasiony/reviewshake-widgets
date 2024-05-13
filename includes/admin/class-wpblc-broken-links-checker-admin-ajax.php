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
			add_action( 'wp_ajax_wpblc_broken_links_mark_as_fixed', array( $this, 'mark_as_fixed' ) );
			add_action( 'wp_ajax_wpblc_broken_links_mark_as_broken', array( $this, 'mark_as_broken' ) );
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
				$scan = WPBLC_Broken_Links_Checker_Utilities::get_content_to_scan( array( 'post', 'page' ) );

				// Create a new instance of the table class
				$broken_links_table = new WPBLC_Broken_Links_Checker_Admin_Links_List_Table();
				$broken_links_table->prepare_items();

				// Capture the output of the display method
				ob_start();
				echo '<form method="get">';
				$broken_links_table->display();
				echo '</form>';
				$table_html = ob_get_clean();
		
				// Return the table HTML in the AJAX response
				wp_send_json_success($table_html);
			}

			die();
		}

		/**
		 * Mark as fixed.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function mark_as_fixed() {
			// Check for nonce security.
			if ( ! wp_verify_nonce( $_POST['nonce'], 'wpblc_broken_links_checker' ) ) {
				wp_die( esc_html__( 'Cheatin&#8217; huh?', 'reviewshake-widgets' ) );
			}

			if ( isset( $_POST ) && isset( $_POST['action'] ) && 'wpblc_broken_links_mark_as_fixed' === $_POST['action'] ) {
				$link = isset( $_POST['link'] ) ? sanitize_text_field( $_POST['link'] ) : '';
				$post_id = isset( $_POST['postId'] ) ? intval( $_POST['postId'] ) : 0;

				$links = get_option( 'wpblc_broken_links_checker_links', array() );

				if ( isset( $links ) && isset( $links['broken'] ) ) {
					$links_column = array_column($links['broken'], 'link');
					$position = array_search($link, $links_column);

					if ( $position !== false ) {
						$links['broken'][$position]['marked_fixed'] = 'fixed';
						$links['fixed'][] = $links['broken'][$position];
					}
				}

				wp_send_json_success( update_option( 'wpblc_broken_links_checker_links', $links ) );
			}

			die();
		}

		/**
		 * Mark as broken.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function mark_as_broken() {
			// Check for nonce security.
			if ( ! wp_verify_nonce( $_POST['nonce'], 'wpblc_broken_links_checker' ) ) {
				wp_die( esc_html__( 'Cheatin&#8217; huh?', 'reviewshake-widgets' ) );
			}

			if ( isset( $_POST ) && isset( $_POST['action'] ) && 'wpblc_broken_links_mark_as_broken' === $_POST['action'] ) {
				$link = isset( $_POST['link'] ) ? sanitize_text_field( $_POST['link'] ) : '';
				$post_id = isset( $_POST['postId'] ) ? intval( $_POST['postId'] ) : 0;

				$links = get_option( 'wpblc_broken_links_checker_links', array() );

				if ( isset( $links ) && isset( $links['broken'] ) ) {
					$links_column = array_column($links['broken'], 'link');
					$position = array_search($link, $links_column);

					if ( $position !== false ) {
						$links['broken'][$position]['marked_fixed'] = 'not-fixed';

						if ( isset( $links['fixed'] ) && ! empty( $links['fixed'] ) ) {
							$links_column = array_column($links['fixed'], 'link');
							$position = array_search($link, $links_column);

							if ( $position !== false ) {
								unset( $links['fixed'][$position] );
							}
						}
					}
				}

				wp_send_json_success( update_option( 'wpblc_broken_links_checker_links', $links ) );
			}

			die();
		}

	}

	return new WPBLC_Broken_Links_Checker_Admin_Ajax();

endif;
