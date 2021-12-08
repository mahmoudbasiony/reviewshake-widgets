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
			add_action( 'wp_ajax_reviewshake_google_places_predictions', array( $this, 'google_places_predictions' ) );
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
				$result['form']   = $form;
				$result['status'] = 200;

				// Send the json success.
				wp_send_json_success( $result );

			}

			die();
		}

		/**
		 * Gets google places predictions from google maps places API.
		 *
		 * @since 1.1.0
		 *
		 * @return void
		 */
		public function google_places_predictions() {
			// Check for nonce security.
			if ( ! wp_verify_nonce( $_GET['nonce'], 'reviewshake-nonce' ) ) {
				wp_die( esc_html__( 'Cheatin&#8217; huh?', 'reviewshake-widgets' ) );
			}

			if ( isset( $_GET ) && isset( $_GET['action'] ) && 'reviewshake_google_places_predictions' === $_GET['action'] ) {
				$input   = isset( $_GET['q'] ) ? sanitize_text_field( $_GET['q'] ) : '';
				$api_key = base64_decode( REVIEWSHAKE_WIDGETS_GOOGLE_PLACES_API_KEY );

				// The Get data parameters.
				$parameters = http_build_query(
					array(
						'input' => $input,
						'key'   => $api_key,
						'types' => 'establishment',
					)
				);

				$response = wp_remote_get(
					'https://maps.googleapis.com/maps/api/place/autocomplete/json?' . $parameters,
					array(
						'method'      => 'GET',
						'timeout'     => 0,
						'redirection' => 10,
						'httpversion' => '1.0',
						'blocking'    => true,
						'cookies'     => array(),
					)
				);

				$response_body = json_decode( wp_remote_retrieve_body( $response ) );

				// Initialize predictions array.
				$predictions = array();

				if ( isset( $response_body->status ) && 'OK' === $response_body->status ) {
					foreach ( $response_body->predictions as $prediction ) {
						if ( is_object( $prediction ) ) {
							$prediction->id   = $prediction->place_id;
							$prediction->text = $prediction->description;

							// Push prediction object into predections array.
							$predictions[] = $prediction;
						}
					}

					// Adds powered by google logo.
					if ( isset( $response_body->predictions ) && ! empty( $response_body->predictions ) && ! empty( $predictions ) ) {
						$powered_by_google = new stdClass();
						$powered_by_google->id        = 'reviewshake_powered_by_google';
						$powered_by_google->text      = '';
						$powered_by_google->disabled  = true;
						$powered_by_google->image_url = esc_url( REVIEWSHAKE_WIDGETS_ROOT_URL . 'assets/dist/images/powered_by_google.png' );

						$predictions[] = $powered_by_google;
					}
				}

				wp_send_json_success( $predictions );
			}
			die();
		}
	}

	return new Reviewshake_Widgets_Admin_Ajax();

endif;
