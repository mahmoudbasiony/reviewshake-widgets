<?php
/**
 * The WPBLC_Broken_Links_Checker_Admin_Notices class.
 *
 * @package WPBLC_Broken_Links_Checker/Admin
 * @author Ilias Chelidonis.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WPBLC_Broken_Links_Checker_Admin_Notices' ) ) :

	/**
	 * Handles admin notices.
	 *
	 * @since 1.0.0
	 */
	class WPBLC_Broken_Links_Checker_Admin_Notices {
		/**
		 * Notices array.
		 *
		 * @var array
		 */
		public $notices = array();

		/**
		 * The constructor.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function __construct() {
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			add_action( 'wp_loaded', array( $this, 'hide_notices' ) );
		}

		/**
		 * Adds slug keyed notices (to avoid duplication).
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug        Notice slug.
		 * @param string $class       CSS class.
		 * @param string $message     Notice body.
		 * @param bool   $dismissible Allow/disallow dismissing the notice. Default value false.
		 *
		 * @return void
		 */
		public function add_admin_notice( $slug, $class, $message, $dismissible = false ) {
			$this->notices[ $slug ] = array(
				'class'       => esc_attr( $class ),
				'message'     => esc_html( $message ),
				'dismissible' => $dismissible,
			);
		}

		/**
		 * Displays the notices.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function admin_notices() {
			// Exit if user has no privilges.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// Basic checks.
			$this->check_environment();

			// Display the notices collected so far.
			foreach ( (array) $this->notices as $notice_key => $notice ) {
				echo '<div class="' . esc_attr( $notice['class'] ) . '" style="position:relative;">';

				if ( $notice['dismissible'] ) {
					echo '<a href="' . esc_url( wp_nonce_url( add_query_arg( 'wpblc-broken-links-checker-hide-notice', $notice_key ), 'wpblc_broken_links_checker_hide_notices_nonce', '_wpblc_broken_links_checker_notice_nonce' ) ) . '" class="woocommerce-message-close notice-dismiss" style="position:absolute;right:1px;padding:9px;text-decoration:none;"></a>';
				}

				echo '<p>' . wp_kses( $notice['message'], array( 'a' => array( 'href' => array() ) ) ) . '</p>';

				echo '</div>';
			}
		}

		/**
		 * Handles all the basic checks.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function check_environment() {
			$show_phpver_notice = get_option( 'wpblc_broken_links_checker_show_phpver_notice' );
			$show_wpver_notice  = get_option( 'wpblc_broken_links_checker_show_wpver_notice' );

			if ( empty( $show_phpver_notice ) ) {
				if ( version_compare( phpversion(), WPBLC_BROKEN_LINKS_CHECKER_MIN_PHP_VER, '<' ) ) {
					/* translators: 1) int version 2) int version */
					$message = esc_html__( 'WP Broken Links Checker - The minimum PHP version required for this plugin is %1$s. You are running %2$s.', 'wpblc-broken-links-checker' );
					$this->add_admin_notice( 'phpver', 'error', sprintf( $message, WPBLC_BROKEN_LINKS_CHECKER_MIN_PHP_VER, phpversion() ), true );
				}
			}

			if ( empty( $show_wpver_notice ) ) {
				global $wp_version;

				if ( version_compare( $wp_version, WPBLC_BROKEN_LINKS_CHECKER_MIN_WP_VER, '<' ) ) {
					/* translators: 1) int version 2) int version */
					$message = esc_html__( 'WP Broken Links Checker - The minimum WordPress version required for this plugin is %1$s. You are running %2$s.', 'wpblc-broken-links-checker' );
					$this->add_admin_notice( 'wpver', 'notice notice-warning', sprintf( $message, WPBLC_BROKEN_LINKS_CHECKER_MIN_WP_VER, WC_VERSION ), true );
				}
			}
		}

		/**
		 * Hides any admin notices.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function hide_notices() {
			if ( isset( $_GET['wpblc-broken-links-checker-hide-notice'] ) && isset( $_GET['_wpblc_broken_links_checker_notice_nonce'] ) ) {
				if ( ! wp_verify_nonce( $_GET['_wpblc_broken_links_checker_notice_nonce'], 'wpblc_broken_links_checker_hide_notices_nonce' ) ) {
					wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'wpblc-broken-links-checker' ) );
				}

				$notice = sanitize_text_field( $_GET['wpblc-broken-links-checker-hide-notice'] );

				switch ( $notice ) {
					case 'phpver':
						update_option( 'wpblc_broken_links_checker_show_phpver_notice', 'no' );
						break;
					case 'wpver':
						update_option( 'wpblc_broken_links_checker_show_wpver_notice', 'no' );
						break;
				}
			}
		}
	}

	new WPBLC_Broken_Links_Checker_Admin_Notices();

endif;
