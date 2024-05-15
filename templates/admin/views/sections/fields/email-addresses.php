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

<div style="margin-top: 10px;">
	<input type="text" id="email_addresses" name="wpblc_broken_links_checker_settings[email_addresses]" value="<?php echo esc_attr( $email_addresses ); ?>" <?php echo ( 'on' === $email_notifications ? '' : 'disabled' ); ?>>
	<p class="description"><?php esc_html_e( 'Enter email addresses, separated by commas.', 'wpblc-broken-links-checker' ); ?></p>
</div>