<?php
/**
 * The Reviewshake_Widgets_Admin_Settings class.
 *
 * @package Reviewshake_Widgets/Admin
 * @author  Reviewshake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Reviewshake_Widgets_Admin_Settings' ) ) :

	/**
	 * Admin menus.
	 *
	 * Adds menu and sub-menus pages.
	 *
	 * @since 1.0.0
	 */
	class Reviewshake_Widgets_Admin_Settings {
		/**
		 * The constructor.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function __construct() {
			// Actions.
			add_action( 'admin_menu', array( $this, 'menu' ) );
		}

		/**
		 * Adds menu and sub-menus pages.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function menu() {
			add_menu_page(
				__( 'Reviewshake', 'reviewshake-widgets' ),
				__( 'Reviewshake', 'reviewshake-widgets' ),
				'manage_options',
				'reviewshake-widgets',
				array( $this, 'menu_page' ),
				'dashicons-admin-comments'
			);
		}

		/**
		 * Renders menu page content.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function menu_page() {
			include_once REVIEWSHAKE_WIDGETS_TEMPLATES_PATH . 'admin/settings.php';
		}

	}

	return new Reviewshake_Widgets_Admin_Settings();

endif;
