<?php
/**
 * The Reviewshake_Widgets_Assets class.
 *
 * @package Reviewshake_Widgets
 * @author  Reviewshake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Reviewshake_Widgets_Assets' ) ) :

	/**
	 * Assets.
	 *
	 * Handles front-end styles and scripts.
	 *
	 * @since 1.0.0
	 */
	class Reviewshake_Widgets_Assets {
		/**
		 * The constructor.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function __construct() {
			add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'styles' ) );
		}

		/**
		 * Enqueues frontend scripts.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function scripts() {
			/*
			 * Global front-end scripts.
			 */
			wp_enqueue_script(
				'reviewshake_widgets_scripts',
				REVIEWSHAKE_WIDGETS_ROOT_URL . 'assets/dist/js/public/reviewshake-widgets-scripts.min.js',
				array(),
				REVIEWSHAKE_WIDGETS_ROOT_URL,
				true
			);

			/*
			 * Localization variables.
			 */
			wp_localize_script(
				'reviewshake_widgets_scripts',
				'reviewshake_widgets_params',
				apply_filters(
					'reviewshake_widgets_js_params',
					array(
						'ajax_url' => admin_url( 'admin-ajax.php' ),
					)
				)
			);
		}

		/**
		 * Enqueues frontend styles.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function styles() {
			wp_enqueue_style( 'reviewshake_widgets_styles', REVIEWSHAKE_WIDGETS_ROOT_URL . 'assets/dist/css/public/reviewshake-widgets-styles.min.css', array(), REVIEWSHAKE_WIDGETS_ROOT_URL, 'all' );
		}
	}

	return new Reviewshake_Widgets_Assets();

endif;
