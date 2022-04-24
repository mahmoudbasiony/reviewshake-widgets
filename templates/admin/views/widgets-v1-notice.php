<?php
/**
 * Settings - Admin - Views.
 *
 * @var array $settings The plugin settings
 * @var array $widget   The existance widgets array
 *
 * @package Reviewshake_Widgets/Templates/Admin/Views
 * @author Reviewshake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<div class="section reviewshake-widgets-widgets" id="reviewshake-widgets-widgets">
	<h2 class="headline"><?php esc_html_e( 'Widgets', 'reviewshake-widgets' ); ?></h2>

	<div class="reviewshake-widgets-v1-notice">
		<p><?php esc_html_e( 'No available old widgets found, switch to v2 widgets version to be able to create, edit and read widgets.', 'reviewshake-widgets' ); ?></p>
	</div>
</div>
