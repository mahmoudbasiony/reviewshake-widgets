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

$claim_link = reviewshake_check_settings( $settings, 'account', 'account_domain' ) ? reviewshake_check_settings( $settings, 'account', 'account_domain' ) . '/claim/' : '';

?>

<div class="claim-account-wrap">
	<div class="claim-account-header">
		<p class="message">
			<?php esc_html_e( 'Claim your free account on Reviewshake. This will allow you to add 2 review sources. You can upgrade to a paid plan if you need more review sources by accessing Configurations -> Billing.', 'reviewshake-widgets' ); ?>
		</p>
	</div>

	<div class="account-links-wrap">
		<div class="claim-account-link">
			<input type=button class="button button-primary" id="claim-account" data-href="<?php echo $claim_link ? esc_url( $claim_link ) : ''; ?> " value="<?php esc_html_e( 'Claim Reviewshake Account', 'reviewshake-widgets' ); ?>"/>
		</div>

		<div class="connect-another-account-wrap">
			<a class="connect-another-account" href=""><?php esc_html_e( 'Connect existing account', 'reviewshake-widgets' ); ?></a>
		</div>

	</div>

	<div class="account-links-wrap claiming-in-progress-wrap" style="display:none;">

		<div class="claiming-in-progress">
			<h2 class="claiming"><?php esc_html_e( 'Claiming account in progress', 'reviewshake-widgets' ); ?></h2>
		</div>

		<div class="setup-review-sources-widgets">
			<a href="admin.php?page=reviewshake-widgets&tab=setup">
				<input type="button" class="button button-primary" value="<?php esc_html_e( 'Setup Review Sources and Widgets', 'reviewshake-widgets' ); ?>" />
			</a>
		</div>

	</div>

</div>
