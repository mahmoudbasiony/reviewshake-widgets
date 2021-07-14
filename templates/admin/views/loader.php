<?php
/**
 * Settings - Admin - Views.
 *
 * @package Reviewshake_Widgets/Templates/Admin/Vendor
 * @author Reviewshake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<div class="creating-account-notice" style="display:none;">
	<?php esc_html_e( 'We are setting up your first review source. This could take up to 30 seconds.', 'reviewshake-widgets' ); ?>
</div>

<div class="loader" style="display:none;" ></div>
