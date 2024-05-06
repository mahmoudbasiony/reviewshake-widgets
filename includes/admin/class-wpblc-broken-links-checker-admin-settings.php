<?php
/**
 * The WPBLC_Broken_Links_Checker_Admin_Settings class.
 *
 * @package WPBLC_Broken_Links_Checker/Admin
 * @author
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WPBLC_Broken_Links_Checker_Admin_Settings' ) ) :

	/**
	 * Admin menus.
	 *
	 * Adds menu and sub-menus pages.
	 *
	 * @since 1.0.0
	 */
	class WPBLC_Broken_Links_Checker_Admin_Settings {

		private $settings;

		/**
		 * The constructor.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function __construct() {
			$this->settings = get_option( 'wpblc_broken_links_checker_settings', array() );

			// Actions.
			add_action( 'admin_menu', array( $this, 'menu' ) );
			add_action( 'admin_init', array( $this, 'register_settings' ) );

			// Filters.
			add_filter( 'set-screen-option', array( $this, 'set_screen_option' ), 10, 3);
		}

		/**
		 * Adds menu and sub-menus pages.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function menu() {
			$hook = add_menu_page(
				__( 'WP Broken Links Checker', 'wpblc-broken-links-checker' ),
				__( 'WP Broken Links Checker', 'wpblc-broken-links-checker' ),
				'manage_options',
				'wpblc-broken-links-checker',
				array( $this, 'menu_page' ),
				'dashicons-admin-comments'
			);

			add_action( "load-$hook", array( $this, 'screen_option' ) );
		}

		/**
		 * Registers settings.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function register_settings() {
			register_setting( 'wpblc_broken_links_checker_settings', 'wpblc_broken_links_checker_settings' );

			add_settings_section(
				'wpblc_broken_links_checker_general_settings_section',
				__( 'General', 'wpblc-broken-links-checker' ),
				null,
				'wpblc-broken-links-checker'
			);

			add_settings_section(
				'wpblc_broken_links_checker_scan_scope_section',
				__( 'Scan', 'wpblc-broken-links-checker' ),
				null,
				'wpblc-broken-links-checker'
			);

			add_settings_field(
				'scan_frequency',
				__( 'Scan Frequency', 'wpblc-broken-links-checker' ),
				array( $this, 'settings_scan_frequency' ),
				'wpblc-broken-links-checker',
				'wpblc_broken_links_checker_general_settings_section'
			);

			add_settings_field(
				'email_notifications',
				__( 'Email Notifications', 'wpblc-broken-links-checker' ),
				array( $this, 'settings_email_notifications' ),
				'wpblc-broken-links-checker',
				'wpblc_broken_links_checker_general_settings_section'
			);

			add_settings_field(
				'email_addresses',
				__( 'Email Address(es)', 'wpblc-broken-links-checker' ),
				array( $this, 'settings_email_addresses' ),
				'wpblc-broken-links-checker',
				'wpblc_broken_links_checker_general_settings_section'
			);

			add_settings_field(
				'number_of_links',
				__( 'Number of Links to Scan', 'wpblc-broken-links-checker' ),
				array( $this, 'settings_number_of_links' ),
				'wpblc-broken-links-checker',
				'wpblc_broken_links_checker_general_settings_section'
			);

			add_settings_field(
				'set_links_number',
				__( 'Set number of links', 'wpblc-broken-links-checker' ),
				array( $this, 'settings_set_links_numbers' ),
				'wpblc-broken-links-checker',
				'wpblc_broken_links_checker_general_settings_section'
			);

			add_settings_field(
				'scope_of_scan',
				__( 'Scope of Scan', 'wpblc-broken-links-checker' ),
				array( $this, 'settings_scope_of_scan' ),
				'wpblc-broken-links-checker',
				'wpblc_broken_links_checker_scan_scope_section'
			);

			add_settings_field(
				'exclusion_urls',
				__( 'Exclusions', 'wpblc-broken-links-checker' ),
				array( $this, 'settings_exclusion_urls' ),
				'wpblc-broken-links-checker',
				'wpblc_broken_links_checker_scan_scope_section'
			);
		}

		/**
		 * Sets the screen option.
		 *
		 * @since 1.0.0
		 *
		 * @param string $status
		 * @param string $option
		 * @param int    $value
		 *
		 * @return int
		 */
		public function set_screen_option($status, $option, $value) {
			if ('links_per_page' == $option) return $value;
			return $status;
		}

		/**
		 * Renders the scan frequency field.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function settings_scan_frequency() {
			$settings = get_option( 'wpblc_broken_links_checker_settings', array() );
			$scan_frequency = isset( $settings['scan_frequency'] ) ? $settings['scan_frequency'] : 'daily';

			include_once WPBLC_BROKEN_LINKS_CHECKER_TEMPLATES_PATH . 'admin/views/sections/fields/scan-frequency.php';
		}

		/**
		 * 
		 */
		public function settings_email_addresses() {
			$settings = get_option( 'wpblc_broken_links_checker_settings', array() );
			$email_notifications = isset( $settings['email_notifications'] ) ? $settings['email_notifications'] : '';
			$email_addresses = isset($settings['email_addresses']) ? $settings['email_addresses'] : '';

			include_once WPBLC_BROKEN_LINKS_CHECKER_TEMPLATES_PATH . 'admin/views/sections/fields/email-addresses.php';
		}
	
		/**
		 * Renders the email notifications field.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function settings_email_notifications() {
			$settings = get_option( 'wpblc_broken_links_checker_settings', array() );
			$email_notifications = isset( $settings['email_notifications'] ) ? $settings['email_notifications'] : '';
			$email_addresses = isset($settings['email_addresses']) ? $settings['email_addresses'] : '';

			include_once WPBLC_BROKEN_LINKS_CHECKER_TEMPLATES_PATH . 'admin/views/sections/fields/email-notifications.php';
		}

		/**
		 * Renders the number of links field.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function settings_number_of_links() {
			$settings = get_option( 'wpblc_broken_links_checker_settings', array() );
			$number_of_links = isset( $settings['number_of_links'] ) ? $settings['number_of_links'] : 'all';

			include_once WPBLC_BROKEN_LINKS_CHECKER_TEMPLATES_PATH . 'admin/views/sections/fields/number-of-links.php';
		}

		/**
		 * Renders the set links numbers field.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function settings_set_links_numbers() {
			$settings = get_option( 'wpblc_broken_links_checker_settings', array() );
			$number_of_links = isset( $settings['number_of_links'] ) ? $settings['number_of_links'] : 'all';
			$set_number = isset( $settings['set_links_number'] ) ? $settings['set_links_number'] : '';

			include_once WPBLC_BROKEN_LINKS_CHECKER_TEMPLATES_PATH . 'admin/views/sections/fields/set-links-number.php';
		}

		/**
		 * Renders the scope of scan field.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function settings_scope_of_scan() {
			$settings = get_option( 'wpblc_broken_links_checker_settings', array() );
			$scope_of_scan = isset( $settings['scope_of_scan'] ) ? $settings['scope_of_scan'] : array('all');

			include_once WPBLC_BROKEN_LINKS_CHECKER_TEMPLATES_PATH . 'admin/views/sections/fields/scope-of-scan.php';
		}

		/**
		 * Renders the exclusion urls field.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function settings_exclusion_urls() {
			$settings = get_option( 'wpblc_broken_links_checker_settings', array() );
			$exclusion_urls = isset( $settings['exclusion_urls'] ) ? $settings['exclusion_urls'] : '';

			include_once WPBLC_BROKEN_LINKS_CHECKER_TEMPLATES_PATH . 'admin/views/sections/fields/exclusion-urls.php';
		}

		/**
		 * Renders menu page content.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function menu_page() {
			include_once WPBLC_BROKEN_LINKS_CHECKER_TEMPLATES_PATH . 'admin/settings.php';
		}

		public function screen_option() {
			$option = 'per_page';
			$args   = array(
				'label'   => 'Links',
				'default' => 10,
				'option'  => 'links_per_page'
			);
		
			add_screen_option( $option, $args );
		}
	}

	return new WPBLC_Broken_Links_Checker_Admin_Settings();

endif;
