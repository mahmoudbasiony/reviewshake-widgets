<?php
/**
 * Settings - Scan.
 *
 * @package WPBLC_Broken_Links_Checker/Templates/Admin
 * @author Ilias Chelidonis.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$broken_links_table = new WPBLC_Broken_Links_Checker_Admin_Links_List_Table();
$broken_links_table->prepare_items();

?>

<div class="wpblc-broken-links-checker-scan section" id="wpblc-broken-links-checker-scan">
	<div class="wpblc-broken-links-checker-scan-header-wrap">
		<div class="wpblc-broken-links-checker-scan-headers">
			<div class="wpblc-broken-links-checker-headline">
				<h2 class="headline"><?php esc_html_e( 'Perform a scan for broken links on your WordPress site.', 'wpblc-broken-links-checker' ); ?></h2>
				<p><?php esc_html_e( 'Detect broken links, broken images, embed Youtube videos by simply clicking on the scan button.', 'wpblc-broken-links-checker' ); ?></p>
			</div>

			<div class="wpblc-broken-links-checker-scan-actions">
				<input type="button" class="button button-primary" id="wpblc-manual-scan" value="<?php esc_html_e( 'Start Manual Scan', 'wpblc-broken-links-checker' ); ?>" />
			</div>
		</div>
	</div>
	<div class="wpblc-broken-links-checker-scan-wrap">
		
		<div class="wpblc-broken-links-checker-links-table">
			<form method="get">
				<?php $broken_links_table->display(); ?>
			</form>
		</div>

		<!-- The export modal -->
		<?php require_once WPBLC_BROKEN_LINKS_CHECKER_TEMPLATES_PATH . 'admin/views/export-csv.php'; ?>

		<!-- The loader -->
		<?php require_once WPBLC_BROKEN_LINKS_CHECKER_TEMPLATES_PATH . 'admin/views/loader.php'; ?>
	</div>
</div>
