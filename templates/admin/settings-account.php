<?php
/**
 * Settings - Account.
 *
 * @package Reviewshake_Widgets/Templates/Admin
 * @author  Reviewshake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// General settings.
$settings = get_option( 'reviewshake_widgets_settings', array() );

// Is account exists and connected.
$is_account_exists = reviewshake_is_account_exist_in_db();

// Get current account subscription plan.
$current_plan = reviewshake_get_current_pricing_plan();

// Is account claimed.
$is_account_claimed = reviewshake_is_account_claimed( $current_plan );
?>

<div class="reviewshake-widgets-account section" id="reviewshake-widgets-account">
	<h2 class="headline"><?php esc_html_e( 'Reviewshake Account', 'reviewshake-widgets' ); ?></h2>

	<div class="reviewshake-widgets-account-wrap">
		<?php
			/*
			 * Validates different account statuses.
			 */
		if ( $is_account_exists && $is_account_claimed ) {
			include 'views/account-details.php';
		} elseif ( $is_account_exists && ! $is_account_claimed ) {
			include 'views/claim-account.php';
		} else {
			include 'views/connect-account-form.php';
		}
		?>
	</div>
</div>
