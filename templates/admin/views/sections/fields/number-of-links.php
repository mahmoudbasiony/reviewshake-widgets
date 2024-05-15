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

<input type="radio" id="all_links" name="wpblc_broken_links_checker_settings[number_of_links]" value="all" <?php checked( $number_of_links, 'all' ); ?>>
<label for="all_links"><?php esc_html_e( 'All', 'wpblc-broken-links-checker' ); ?></label><br>
<input type="radio" id="set_number" name="wpblc_broken_links_checker_settings[number_of_links]" value="set_number" <?php checked( $number_of_links, 'set_number' ); ?>>
<label for="set_number"><?php esc_html_e( 'Set number', 'wpblc-broken-links-checker' ); ?></label>