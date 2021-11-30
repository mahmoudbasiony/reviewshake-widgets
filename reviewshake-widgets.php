<?php
/**
 * Plugin Name: Reviewshake Widgets
 * Plugin URI:
 * Description: Add customizable widgets to showcase reviews from Google, Facebook, Yelp and 80+ other websites.
 * Version: 1.1.0
 * Author: Reviewshake
 * Author URI: https://www.reviewshake.com
 * Requires at least: 4.7.0
 * Tested up to: 5.8
 *
 * Text Domain: reviewshake-widgets
 * Domain Path: /languages/
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package Reviewshake_Widgets
 * @author  Reviewshake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/*
 * Globals constants.
 */
define( 'REVIEWSHAKE_WIDGETS_PLUGIN_VERSION', '1.1.0' );
define( 'REVIEWSHAKE_WIDGETS_MIN_PHP_VER', '5.6.0' );
define( 'REVIEWSHAKE_WIDGETS_MIN_WP_VER', '4.7.0' );
define( 'REVIEWSHAKE_WIDGETS_ROOT_PATH', dirname( __FILE__ ) );
define( 'REVIEWSHAKE_WIDGETS_ROOT_URL', plugin_dir_url( __FILE__ ) );
define( 'REVIEWSHAKE_WIDGETS_TEMPLATES_PATH', dirname( __FILE__ ) . '/templates/' );
define( 'REVIEWSHAKE_WIDGETS_GENERAL_API', 'NDRhNmJhNTJlNjcwMzg2NTM5OTg2M2U3YTFiOWE1MmNlMzJlZjAwZDg3Mzg4OTcx' );
define( 'REVIEWSHAKE_WIDGETS_GOOGLE_PLACES_API_KEY', 'QUl6YVN5QmFDRVNzMzNHck9VYzFsRXkzanhOQ3BhOWxFNWd3TkpZ' );

if ( ! class_exists( 'Reviewshake_Widgets' ) ) :

	/**
	 * The main class.
	 *
	 * @since 1.0.0
	 */
	class Reviewshake_Widgets {
		/**
		 * Plugin version.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public $version = '1.1.0';

		/**
		 * The singelton instance of Reviewshake_Widgets.
		 *
		 * @since 1.0.0
		 *
		 * @var Reviewshake_Widgets
		 */
		private static $instance = null;

		/**
		 * Returns the singelton instance of Reviewshake_Widgets.
		 *
		 * Ensures only one instance of Reviewshake_Widgets is/can be loaded.
		 *
		 * @since 1.0.0
		 *
		 * @return Reviewshake_Widgets
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

			do_action( 'reviewshake_widgets_loaded' );
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
			include_once REVIEWSHAKE_WIDGETS_ROOT_PATH . '/includes/functions.php';
			include_once REVIEWSHAKE_WIDGETS_ROOT_PATH . '/includes/class-reviewshake-widgets-widget.php';

			/*
			 * Back-end includes.
			 */
			if ( is_admin() ) {
				include_once REVIEWSHAKE_WIDGETS_ROOT_PATH . '/includes/admin/class-reviewshake-widgets-admin-notices.php';
				include_once REVIEWSHAKE_WIDGETS_ROOT_PATH . '/includes/admin/class-reviewshake-widgets-admin-assets.php';
				include_once REVIEWSHAKE_WIDGETS_ROOT_PATH . '/includes/admin/class-reviewshake-widgets-admin-ajax.php';
				include_once REVIEWSHAKE_WIDGETS_ROOT_PATH . '/includes/admin/class-reviewshake-widgets-admin-settings.php';
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
			// Filters.
			add_filter( 'plugin_action_links', array( $this, 'adds_settings_action_plugin' ), 10, 5 );

			// Actions.
			add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
			add_action( 'widgets_init', array( $this, 'register_reviewshake_widget' ) );
		}

		/**
		 * Initialize rest API controllers classes and register routes.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function rest_api_init() {
			// Includes the controller classes.
			include_once REVIEWSHAKE_WIDGETS_ROOT_PATH . '/includes/rest-api/class-reviewshake-widgets-rest-account-controller.php';
			include_once REVIEWSHAKE_WIDGETS_ROOT_PATH . '/includes/rest-api/class-reviewshake-widgets-rest-review-sources-controller.php';
			include_once REVIEWSHAKE_WIDGETS_ROOT_PATH . '/includes/rest-api/class-reviewshake-widgets-rest-widgets-controller.php';

			// Define registered controllers classes array.
			$controllers = array(
				Reviewshake_Widgets_REST_Account_Controller::class,
				Reviewshake_Widgets_REST_Review_Sources_Controller::class,
				Reviewshake_Widgets_REST_Widgets_Controller::class,
			);

			foreach ( $controllers as $controller_class ) {
				$controller = new $controller_class();

				// Register routes.
				$controller->register_routes();
			}

		}

		/**
		 * Register reviewshake widget.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function register_reviewshake_widget() {
			register_widget( 'Reviewshake_Widgets_Widget' );
		}

		/**
		 * Adds settings link to plugin action links.
		 *
		 * @param array  $actions     The plugin actions.
		 * @param string $plugin_file The plugin file Path.
		 *
		 * @since 1.0.0
		 *
		 * @return array $actions
		 */
		public function adds_settings_action_plugin( $actions, $plugin_file ) {
			static $plugin;

			if ( ! isset( $plugin ) ) {
				$plugin = plugin_basename( __FILE__ );
			}

			if ( $plugin == $plugin_file ) {
				$settings = array(
					'settings' => '<a href="' . esc_url( admin_url( 'admin.php?page=reviewshake-widgets&tab=setup' ) ) . '">' . esc_html__( 'Settings', 'reviewshake-widgets' ) . '</a>',
				);

				// Merge settings link to plugin actions link.
				$actions = array_merge( $settings, $actions );
			}

			return $actions;
		}

		/**
		 * Activation hooks.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public static function activate() {
			// Nothing to Do for Now.
		}

		/**
		 * Deactivation hooks.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public static function deactivate() {
			// Nothing to do for now.
		}

		/**
		 * Uninstall hooks.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public static function uninstall() {
			include_once REVIEWSHAKE_WIDGETS_ROOT_PATH . 'uninstall.php';
		}
	}

	// Plugin hooks.
	register_activation_hook( __FILE__, array( 'Reviewshake_Widgets', 'activate' ) );
	register_deactivation_hook( __FILE__, array( 'Reviewshake_Widgets', 'deactivate' ) );
	register_uninstall_hook( __FILE__, array( 'Reviewshake_Widgets', 'uninstall' ) );

endif;

/**
 * Init plugin.
 *
 * @since 1.0.0
 */
function reviewshake_widgets_init() {
	// Global for backwards compatibility.
	$GLOBALS['reviewshake_widgets'] = Reviewshake_Widgets::get_instance();
}

add_action( 'plugins_loaded', 'reviewshake_widgets_init', 0 );
