<?php
/**
 * Settings - Admin - Views - Sections - Fields.
 *
 * @package WPBLC_Broken_Links_Checker/Templates/Admin/Views/Sections/Fields
 * @author Ilias Chelidonis.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<select name="wpblc_broken_links_checker_settings[scan_frequency]">
	<option value="daily" <?php selected( $scan_frequency, 'daily' ); ?>><?php esc_html_e( 'Daily', 'wpblc-broken-links-checker' ); ?></option>
	<option value="weekly" <?php selected( $scan_frequency, 'weekly' ); ?>><?php esc_html_e( 'Weekly', 'wpblc-broken-links-checker' ); ?></option>
	<option value="monthly" <?php selected( $scan_frequency, 'monthly' ); ?>><?php esc_html_e( 'Monthly', 'wpblc-broken-links-checker' ); ?></option>
</select>