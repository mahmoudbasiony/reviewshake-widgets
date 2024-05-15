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

<input type="checkbox" id="email_notifications" name="wpblc_broken_links_checker_settings[email_notifications]" <?php checked($email_notifications, 'on'); ?>>
<label for="email_notifications"><?php esc_html_e( 'Enable email notifications', '' ); ?></label>