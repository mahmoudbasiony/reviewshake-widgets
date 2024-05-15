<?php
/**
 * Settings - Admin - Views.
 *
 * @package WPBLC_Broken_Links_Checker/Templates/Admin/Views
 * @author Ilias Chelidonis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<div class="wpblc_export_csv_wrap">
	<form name="wpblc_export_csv_form" class="wpblc_export_csv_form" id="wpblc_export_csv_form" action="<?php echo admin_url('admin.php?page=wpblc-broken-links-checker&tab=scan'); ?>" method="post">
		<input type="hidden" name="action" value="wpblc_export_csv"/>
		<input type="hidden" name="nonce"
			value="<?php echo esc_attr( wp_create_nonce( 'wpblc_export_csv_nonce' ) ); ?>"/>
		<input type="submit" name="download" class="button button-primary wpblc_download" id="wpblc_download" class="button button-primary"
			value="<?php esc_html_e( 'Export Report in CSV', 'wpblc-broken-links-checker' ); ?>"/>
	</form>
</div>