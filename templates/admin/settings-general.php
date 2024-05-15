<?php
/**
 * Settings - General.
 *
 * @var array $settings - The plugin settings array
 *
 * @package WPBLC_Broken_Links_Checker/Templates/Admin
 * @author Ilias Chelidonis.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// General settings.
$settings = get_option( 'wpblc_broken_links_checker_settings', array() );
?>

	<div class="wpblc-broken-links-checker-general wrap">
		<form action="options.php" method="post">
			<?php
			settings_fields('wpblc_broken_links_checker_settings');
			do_settings_sections('wpblc-broken-links-checker');
			submit_button('Save Settings');
			?>
		</form>
	</div>
