<?php
/**
 * Settings - Scan.
 *
 * @package WPBLC_Broken_Links_Checker/Templates/Admin
 * @author
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$broken_links_table = new WPBLC_Broken_Links_Checker_Admin_Links_List_Table();


if ( isset( $_GET['s'] ) ) {
	$broken_links_table->prepare_items( $_GET['s'] );
} else {
	$broken_links_table->prepare_items();
}


?>

<div class="wpblc-broken-links-checker-scan section" id="wpblc-broken-links-checker-scan">
	<h2 class="headline"><?php esc_html_e( 'Scan', 'wpblc-broken-links-checker' ); ?></h2>

	<div class="wpblc-broken-links-checker-scan-wrap">
		
		<!-- <form method="get" action="">
			<input type="hidden" name="page" value="wpblc-broken-links-checker">
			<input type="hidden" name="tab" value="links">
			<?php //$broken_links_table->search_box( __( 'Search Links', 'wpblc-broken-links-checker' ), 'links' ); ?>
		</form> -->
		<div>
			<form method="post">
				<?php $broken_links_table->display(); ?>
			</form>
		</div>

		<?php require_once WPBLC_BROKEN_LINKS_CHECKER_TEMPLATES_PATH . 'admin/views/loader.php'; ?>
	</div>
</div>
