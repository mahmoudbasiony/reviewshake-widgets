<?php
/**
 * Settings - Admin - Views.
 *
 * @var object $current_plan The account current plan object.
 *
 * @package Reviewshake_Widgets/Templates/Admin/Vendor
 * @author Reviewshake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Check is account claimed.
$is_claimed = reviewshake_is_account_claimed( $current_plan );

?>

<table class="review-sources-upgrade-table">
	<tbody>
		<tr valign="top" class="review-sources-upgrade-row">
			<th class="review-sources-upgrade-text">
				<span><?php esc_html_e( 'Want to add more sources?', 'reviewshake-widgets' ); ?></span>
			</th>
			<th class="review-sources-upgrade-link">
				<span>
					<?php
					if ( $is_claimed ) {
						$action_link = "https://{$subdomain}.reviewshake.com/admin/billing";
						$text        = __( 'Upgrade your Reviewshake account', 'reviewshake-widgets' );

						echo '<a href="' . esc_url( $action_link ) . '" id="upgrade-setup-link">' . esc_html( $text ) . '</a>';
					} else {
						$action_link = 'admin.php?page=reviewshake-widgets&tab=account';
						$text        = __( 'Connect your Reviewshake account', 'reviewshake-widgets' );

						echo '<a href="' . esc_url( $action_link ) . '">' . esc_html( $text ) . '</a>';
					}
					?>
				</span>
			</th>
		</tr>
	</tbody>
</table>
