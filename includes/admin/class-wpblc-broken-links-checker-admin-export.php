<?php
/**
 * The WPBLC_Broken_Links_Checker_Admin_Export class.
 *
 * @package WPBLC_Broken_Links_Checker/Admin
 * @author  
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WPBLC_Broken_Links_Checker_Admin_Export' ) ) :
	
	/**
	 * Admin report.
	 *
	 * Handles the report page.
	 *
	 * @since 1.0.0
	 */
	class WPBLC_Broken_Links_Checker_Admin_Export {

		/**
		 * The constructor.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function __construct() {
			// Actions.
			add_action( 'admin_init', array( $this, 'export_csv' ) );
		}

		/**
		 * Export CSV.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function export_csv() {
			// Check if we are on the correct page.
			if ( isset( $_GET['page'] ) && $_GET['page'] === 'wpblc-broken-links-checker' && isset( $_GET['tab'] ) && $_GET['tab'] === 'scan' ) {
				// Check if our form has been submitted.
				if ( isset( $_POST['action'] ) && $_POST['action'] === 'wpblc_export_csv' ) {
					// Verify the nonce.
					if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpblc_export_csv_nonce' ) ) {
						wp_die( esc_html__( 'Cheatin&#8217; huh?', 'wpblc-broken-links-checer' ) );
					}
		
					// Check if the current user can manage options.
					if ( ! current_user_can( 'manage_options' ) ) {
						wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wpblc-broken-links-checer' ) );
					}

					$generated_date = gmdate( 'd-m-Y His' );
		
					// Get the broken links.
					$links = get_option( 'wpblc_broken_links_checker_links', array() );
					$broken_links = isset($links['broken']) ? $links['broken'] : array();

					// Output headers
					header( 'Pragma: public' );
					header( 'Expires: 0' );
					header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
					header( 'Cache-Control: private', false );
					header( 'Content-Type: application/x-excel' );
					header(
						'Content-Disposition: attachment; filename="broken_links_report' . $generated_date
						. '.csv";'
					);
					header( 'Content-Transfer-Encoding: binary' );
			
					// Open the output stream
					$fh = @fopen('php://output', 'w');
			
					// Output column headers
					fputcsv($fh, array('Link', 'Status', 'Code', 'Message', 'SourceId', 'SourcePostType', 'Date'));
			
					// Output rows
					foreach ($broken_links as $key => $link) {
						fputcsv($fh, array($link['link'], $link['type'], $link['code'], $link['text'], $link['ID'], 'post', $link['detected_at']));
					}

					// Close the output stream
					fclose($fh);
					exit;
				}
			}

		}

	}

	return new WPBLC_Broken_Links_Checker_Admin_Export();

endif;