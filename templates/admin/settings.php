<?php
/**
 * Settings.
 *
 * @package Reviewshake_Widgets/Templates/Admin
 * @author  Reviewshake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Available tabs.
$plugin_tabs = array( 'setup', 'account' );

// Current tab.
$plugin_tab = isset( $_GET['tab'] ) && in_array( $_GET['tab'], $plugin_tabs, true ) ? sanitize_text_field( $_GET['tab'] ) : 'setup';

/**
 * Get the current account info.
 */
reviewshake_rest_get_account_info();

if ( 'setup' === $plugin_tab ) {
	/**
	 * Fetch review sources from api and save them to db.
	 */
	reviewshake_rest_list_review_sources();

	/*
	* Fetch widgets from api and save to db.
	*/
	reviewshake_rest_list_widgets();
}

?>

<div class="wrap reviewshake-widgets" id="reviewshake-widgets">
	<nav class="nav-tab-wrapper reviewshake-nav-tab-wrapper">
		<a href="admin.php?page=reviewshake-widgets&tab=setup" class="nav-tab <?php echo 'setup' === $plugin_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Setup', 'reviewshake-widgets' ); ?></a>
		<a href="admin.php?page=reviewshake-widgets&tab=account" class="nav-tab <?php echo 'account' === $plugin_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Reviewshake Account', 'reviewshake-widgets' ); ?></a>
	</nav>

	<div class="postbox<?php echo 'account' === $plugin_tab ? ' not-full-width' : ''; ?>">
		<div class="inside tab tab-content <?php echo esc_attr( $plugin_tab ); ?>" id="reviewshake-tab-<?php echo esc_attr( $plugin_tab ); ?>">
			<?php require_once 'settings-' . $plugin_tab . '.php'; ?>
			<?php require_once 'views/loader.php'; ?>
		</div>
	</div>
</div>
