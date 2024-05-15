<?php
/**
 * The WPBLC_Broken_Links_Checker_Schedule class.
 *
 * @package WPBLC_Broken_Links_Checker
 * @author  Ilias Chelidonis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WPBLC_Broken_Links_Checker_Schedule' ) ) :
	/**
	 * The schedule class.
	 *
	 * Handles the schedule event.
	 *
	 * @since 1.0.0
	 */
	class WPBLC_Broken_Links_Checker_Schedule {
		/**
		 * The constructor.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function __construct() {
			// Actions.
			add_action( 'wpblc_broken_links_checker_scheduled_event', array( $this, 'schedule_event' ) );
			add_action( 'update_option', array( $this, 'on_update_option' ), 10, 3 );

			// Filters.
			add_filter( 'cron_schedules', array( $this, 'add_schedule' ) );
		}

		/**
		 * Schedule event.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function schedule_event() {
			// Process the scan.
			WPBLC_Broken_Links_Checker_Utilities::process_scan();
		}

		/**
		 * Add schedule.
		 *
		 * @param array $schedules - The schedules array.
		 *
		 * @since 1.0.0
		 *
		 * @return array
		 */
		public function add_schedule( $schedules ) {
			$schedules['monthly'] = array(
				'interval' => 2592000,
				'display'  => esc_html__( 'Monthly', 'wpblc-broken-links-checker' ),
			);

			return $schedules;
		}

		/**
		 * Update scan frequency.
		 *
		 * @param string $frequency - The frequency.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function update_scan_frequency( $frequency ) {
			// Clear the schedule.
			wp_clear_scheduled_hook( 'wpblc_broken_links_checker_scheduled_event' );

			// Schedule the event.
			if ( ! wp_next_scheduled( 'wpblc_broken_links_checker_scheduled_event' ) ) {
				wp_schedule_event( time(), $frequency, 'wpblc_broken_links_checker_scheduled_event' );
			}
		}

		/**
		 * On update option.
		 *
		 * @param string $option - The option name.
		 * @param mixed  $old_value - The old value.
		 * @param mixed  $new_value - The new value.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function on_update_option( $option, $old_value, $new_value) {
			if ( 'wpblc_broken_links_checker_settings' === $option && isset( $old_value['scan_frequency'] ) && isset( $new_value['scan_frequency'] ) ) {
				if ( $old_value['scan_frequency'] !== $new_value['scan_frequency'] ) {
					// Update the scan frequency.
					$this->update_scan_frequency( $new_value['scan_frequency'] );
				}
			}
		}
	}

	new WPBLC_Broken_Links_Checker_Schedule();

endif;