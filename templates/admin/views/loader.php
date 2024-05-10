<?php
/**
 * Settings - Admin - Views.
 *
 * @package WPBLC_Broken_Links_Checker/Templates/Admin/Views
 * @author
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$loader = WPBLC_BROKEN_LINKS_CHECKER_ROOT_URL . 'assets/dist/images/loader.gif';
?>

<div id="wpblc_stop_scan_div" class ="wpblc_none wpblc-is-scanning">
	<br>
	<h2 id="progress_message" class="wpblc_success_div"><img src="<?php echo esc_url( $loader ); ?>" height="30px" width="30px" class="wpblc_loader_margin"></img><?php esc_html_e( 'Your website is getting scanned for broken links.', 'wpblc-broken-links-checker' ); ?></h2>
	<br>
	<!-- <input type="button" name="wpblc_stop_scan" id="wpblc_stop_scan" value="Abort Scan" class="wpblc_button_danger" /> -->
</div>
