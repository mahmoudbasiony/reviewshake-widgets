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

$total_links = isset( $links['total'] ) ? intval( $links['total'] ) : 0;
?>

<div id="wpblc_no_broken_links_container" class="wpblc_no_broken_links_container">
	<div id="wpblc_no_broken_links" class="wpblc_no_broken_links">
		<h2><?php esc_html_e( 'Congratulations, no dead links found!', 'wpblc-broken-links-checker' ); ?></h2>
		<p>
			<?php
			/* translators: %s: total number of links */
			printf( esc_html__( 'We scanned a total of %s links on your site. All clear!', 'wpblc-broken-links-checker' ), '<span class="total-links">' . esc_html( $total_links ) . '</span>' );
			?>
		</p>
	</div>
</div>