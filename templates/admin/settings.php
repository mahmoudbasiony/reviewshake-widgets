<?php
/**
 * Settings.
 *
 * @package WPBLC_Broken_Links_Checker/Templates/Admin
 * @author  Ilias Chelidonis.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Available tabs.
$plugin_tabs = array( 'general', 'scan', 'help' );

// Current tab.
$plugin_tab = isset( $_GET['tab'] ) && in_array( $_GET['tab'], $plugin_tabs, true ) ? sanitize_text_field( $_GET['tab'] ) : 'general';

?>

<div class="wrap wpblc-broken-links-checker" id="wpblc-broken-links-checker">
	<nav class="nav-tab-wrapper wpblc-nav-tab-wrapper">
		<a href="admin.php?page=wpblc-broken-links-checker&tab=general" class="nav-tab <?php echo 'general' === $plugin_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Settings', 'wpblc-broken-links-checker' ); ?></a>
		<a href="admin.php?page=wpblc-broken-links-checker&tab=scan" class="nav-tab <?php echo 'scan' === $plugin_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Scan', 'wpblc-broken-links-checker' ); ?></a>
		<a href="admin.php?page=wpblc-broken-links-checker&tab=help" class="nav-tab <?php echo 'help' === $plugin_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Help', 'wpblc-broken-links-checker' ); ?></a>
	</nav>

	<div class="wpblc-broken-links-checker-inside-tabs">
		<div class="inside tab tab-content <?php echo esc_attr( $plugin_tab ); ?>" id="wpblc-tab-<?php echo esc_attr( $plugin_tab ); ?>">
			<?php require_once 'settings-' . $plugin_tab . '.php'; ?>
		</div>
	</div>
</div>
