<?php
/**
 * Plugin Name: WP Broken Links Checker
 * Plugin URI:
 * Description: Schedule automated scans to detect broken links on your WordPress site, view results in an intuitive table, and receive email notifications for swift resolution.
 * Version: 1.0.0
 * Author: Ilias Chelidonis
 * Author URI:
 * Requires at least: 5.4
 * Tested up to: 6.5.3
 *
 * Text Domain: wpblc-broken-links-checker
 * Domain Path: /languages/
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package WPBLC_Broken_Links_Checker
 * @author Ilias Chelidonis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/*
 * Globals constants.
 */
define( 'WPBLC_BROKEN_LINKS_CHECKER_PLUGIN_NAME', 'WP Broken Links Checker' );
define( 'WPBLC_BROKEN_LINKS_CHECKER_PLUGIN_VERSION', '1.0.0' );
define( 'WPBLC_BROKEN_LINKS_CHECKER_MIN_PHP_VER', '7.3' );
define( 'WPBLC_BROKEN_LINKS_CHECKER_MIN_WP_VER', '5.4' );
define( 'WPBLC_BROKEN_LINKS_CHECKER_ROOT_PATH', __DIR__ );
define( 'WPBLC_BROKEN_LINKS_CHECKER_ROOT_URL', plugin_dir_url( __FILE__ ) );
define( 'WPBLC_BROKEN_LINKS_CHECKER_TEMPLATES_PATH', __DIR__ . '/templates/' );

if ( ! class_exists( 'WPBLC_Broken_Links_Checker' ) ) :

	/**
	 * The main class.
	 *
	 * @since 1.0.0
	 */
	class WPBLC_Broken_Links_Checker {
		/**
		 * Plugin version.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * Database version.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		private static $db_version = '1.0.0';

		/**
		 * The singelton instance of WPBLC_Broken_Links_Checker.
		 *
		 * @since 1.0.0
		 *
		 * @var WPBLC_Broken_Links_Checker
		 */
		private static $instance = null;

		/**
		 * Returns the singelton instance of WPBLC_Broken_Links_Checker.
		 *
		 * Ensures only one instance of WPBLC_Broken_Links_Checker is/can be loaded.
		 *
		 * @since 1.0.0
		 *
		 * @return WPBLC_Broken_Links_Checker
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * The constructor.
		 *
		 * Private constructor to make sure it can not be called directly from outside the class.
		 *
		 * @since 1.0.0
		 */
		private function __construct() {
			$this->includes();
			$this->hooks();

			do_action( 'wpblc_broken_links_checker_loaded' );
		}

		/**
		 * Includes the required files.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function includes() {
			/*
			 * Global includes.
			 */
			include_once WPBLC_BROKEN_LINKS_CHECKER_ROOT_PATH . '/includes/functions.php';
			include_once WPBLC_BROKEN_LINKS_CHECKER_ROOT_PATH . '/includes/class-wpblc-broken-links-checker-utilities.php';
			include_once WPBLC_BROKEN_LINKS_CHECKER_ROOT_PATH . '/includes/class-wpblc-broken-links-checker-schedule.php';

			/*
			 * Back-end includes.
			 */
			if ( is_admin() ) {
				include_once WPBLC_BROKEN_LINKS_CHECKER_ROOT_PATH . '/includes/admin/class-wpblc-broken-links-checker-admin-notices.php';
				include_once WPBLC_BROKEN_LINKS_CHECKER_ROOT_PATH . '/includes/admin/class-wpblc-broken-links-checker-admin-assets.php';
				include_once WPBLC_BROKEN_LINKS_CHECKER_ROOT_PATH . '/includes/admin/class-wpblc-broken-links-checker-admin-settings.php';
				include_once WPBLC_BROKEN_LINKS_CHECKER_ROOT_PATH . '/includes/admin/class-wpblc-broken-links-checker-admin-links-list-table.php';
				include_once WPBLC_BROKEN_LINKS_CHECKER_ROOT_PATH . '/includes/admin/class-wpblc-broken-links-checker-admin-ajax.php';
				include_once WPBLC_BROKEN_LINKS_CHECKER_ROOT_PATH . '/includes/admin/class-wpblc-broken-links-checker-admin-export.php';
			}
		}

		/**
		 * Plugin hooks.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function hooks() {
		}

		/**
		 * Activation hooks.
		 *
		 * @since   1.0.0
		 *
		 * @return void
		 */
		public static function activate() {
			$settings  = get_option( 'wpblc_broken_links_checker_settings', array() );
			$frequency = isset( $settings['scan_frequency'] ) ? $settings['scan_frequency'] : 'daily';

			if ( ! wp_next_scheduled( 'wpblc_broken_links_checker_scheduled_event' ) ) {
				wp_schedule_event( time(), $frequency, 'wpblc_broken_links_checker_scheduled_event' );
			}
		}

		/**
		 * Deactivation hooks.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public static function deactivate() {
			wp_clear_scheduled_hook( 'wpblc_broken_links_checker_scheduled_event' );
		}

		/**
		 * Uninstall hooks.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public static function uninstall() {
			include_once WPBLC_BROKEN_LINKS_CHECKER_ROOT_PATH . 'uninstall.php';
		}
	}

	// Plugin hooks.
	register_activation_hook( __FILE__, array( 'WPBLC_Broken_Links_Checker', 'activate' ) );
	register_deactivation_hook( __FILE__, array( 'WPBLC_Broken_Links_Checker', 'deactivate' ) );
	register_uninstall_hook( __FILE__, array( 'WPBLC_Broken_Links_Checker', 'uninstall' ) );

endif;

/**
 * Init plugin.
 *
 * @since 1.0.0
 */
function wpblc_broken_links_checker_init() {
	// Global for backwards compatibility.
	$GLOBALS['wpblc_broken_links_checker'] = WPBLC_Broken_Links_Checker::get_instance();
}

add_action( 'plugins_loaded', 'wpblc_broken_links_checker_init', 0 );
