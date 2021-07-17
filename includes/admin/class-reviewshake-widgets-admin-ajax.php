<?php
/**
 * The Reviewshake_Widgets_Admin_Ajax class.
 *
 * @package Reviewshake_Widgets/Admin
 * @author  Reviewshake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Reviewshake_Widgets_Admin_Ajax' ) ) :

	/**
	 * Admin Ajax.
	 *
	 * Calls admin Ajax.
	 *
	 * @since 1.0.0
	 */
	class Reviewshake_Widgets_Admin_Ajax {
		/**
		 * The constructor.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function __construct() {
			add_action( 'wp_ajax_reviewshake_renders_widget_form', array( $this, 'renders_widget_form' ) );
			add_action( 'wp_ajax_reviewshake_renders_review_source_form', array( $this, 'renders_review_source_form' ) );

		}

		/**
		 * Renders the edit/create widget form.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function renders_widget_form() {
			// Check for nonce security.
			if ( ! wp_verify_nonce( $_POST['nonce'], 'reviewshake-nonce' ) ) {
				wp_die( esc_html__( 'Cheatin&#8217; huh?', 'reviewshake-widgets' ) );
			}

			if ( isset( $_POST ) && isset( $_POST['action'] ) && 'reviewshake_renders_widget_form' === $_POST['action'] ) {
				$widget_id = isset( $_POST['widget_id'] ) ? absint( $_POST['widget_id'] ) : 0;

				$form = reviewshake_render_widget_form( $widget_id );

				if ( ! $form ) {
					wp_die( esc_html__( 'Something went wrong! Cannot get the edit widget form', 'reviewshake-widgets' ) );
				}

				// Declare result array.
				$result = array();

				// Append result to result array.
				$result['widget_id'] = $widget_id;
				$result['form']      = $form;
				$result['status']    = 200;

				// Send the json success.
				wp_send_json_success( $result );

			}

			die();
		}

		/**
		 * Renders create review source form.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function renders_review_source_form() {
			// Check for nonce security.
			if ( ! wp_verify_nonce( $_POST['nonce'], 'reviewshake-nonce' ) ) {
				wp_die( esc_html__( 'Cheatin&#8217; huh?', 'reviewshake-widgets' ) );
			}

			if ( isset( $_POST ) && isset( $_POST['action'] ) && 'reviewshake_renders_review_source_form' === $_POST['action'] ) {
				$form = reviewshake_render_review_source_form();

				if ( ! $form ) {
					wp_die( esc_html__( 'Something went wrong! Cannot get the create review source form', 'reviewshake-widgets' ) );
				}

				// Declare result array.
				$result = array();

				// Append result to result array.
				$result['form']      = $form;
				$result['status']    = 200;

				// Send the json success.
				wp_send_json_success( $result );

			}

			die();
		}
	}

	return new Reviewshake_Widgets_Admin_Ajax();

endif;
