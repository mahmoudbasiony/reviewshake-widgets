<?php
/**
 * The WPBLC_Broken_Links_Checker_Admin_Assets class.
 *
 * @package WPBLC_Broken_Links_Checker/Admin
 * @author
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WPBLC_Broken_Links_Checker_Admin_Assets' ) ) :

	/**
	 * Admin assets.
	 *
	 * Handles back-end styles and scripts.
	 *
	 * @since   1.0.0
	 */
	class WPBLC_Broken_Links_Checker_Admin_Assets {
		/**
		 * The constructor.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ), 20 );
			add_action( 'admin_enqueue_scripts', array( $this, 'styles' ), 20 );
		}

		/**
		 * Enqueues admin scripts.
		 *
		 * @since   1.0.0
		 * @version 2.0.0
		 *
		 * @return void
		 */
		public function scripts() {
			$current_page = isset( $_GET ) && isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
			if ( empty( $current_page ) || 'wpblc-broken-links-checker' !== $current_page ) {
				return;
			}

			// Global admin scripts.
			wp_enqueue_script(
				'wpblc_broken_links_checker_admin_scripts',
				WPBLC_BROKEN_LINKS_CHECKER_ROOT_URL . 'assets/dist/js/admin/wpblc-broken-links-checker-admin-scripts.min.js',
				array( 'jquery' ),
				WPBLC_BROKEN_LINKS_CHECKER_PLUGIN_VERSION,
				true
			);

			// Localization variables.
			wp_localize_script(
				'wpblc_broken_links_checker_admin_scripts',
				'wpblc_broken_links_checker_params',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'wpblc_broken_links_checker' ),
				)
			);
		}

		/**
		 * Enqueues admin styles.
		 *
		 * @since   1.0.0
		 * @version 1.1.0
		 *
		 * @return void
		 */
		public function styles() {
			// Global admin styles.
			wp_enqueue_style( 'wpblc_broken_links_checker_admin_styles', WPBLC_BROKEN_LINKS_CHECKER_ROOT_URL . 'assets/dist/css/admin/wpblc-broken-links-checker-admin-styles.min.css', array( '' ), WPBLC_BROKEN_LINKS_CHECKER_PLUGIN_VERSION, 'all' );
		}
	}

	return new WPBLC_Broken_Links_Checker_Admin_Assets();

endif;
