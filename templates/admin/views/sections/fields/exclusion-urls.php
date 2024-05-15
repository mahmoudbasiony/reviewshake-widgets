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

// Split the exclusions string into an array of URLs.
$exclusion_urlss = explode( "\n", $exclusion_urls );

// Trim whitespace from each URL.
$exclusion_urlss = array_map( 'trim', $exclusion_urlss );

// Remove any empty values.
$exclusion_urlss = array_filter( $exclusion_urlss );

?>

<textarea id="exclusions" name="wpblc_broken_links_checker_settings[exclusion_urls]" rows="5" cols="50"><?php echo esc_textarea( $exclusion_urls ); ?></textarea>
<p class="description"><?php esc_html_e( 'Enter URLs to exclude from scan, one per line. Leave empty if none.', 'wpblc-broken-links-checker' ); ?></p>