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

$subdomain = reviewshake_check_settings( $settings, 'account', 'account_domain' );

$current_plan_data   = reviewshake_get_current_pricing_plan();
$pricing_plan        = $current_plan_data->pricing_plan;
$review_source_limit = reviewshake_get_review_sources_limit( $current_plan_data );

?>

<div class="account-details-wrap section">
	<div class="">
		<h2 class="connected-notice"><?php esc_html_e( 'You are connected to Reviewshake:', 'reviewshake-widgets' ); ?></h2>

		<div class="subdomain-info">
			<span class="header">
				<?php esc_html_e( 'Subdomain', 'reviewshake-widgets' ); ?>
			</span>

			<span class="subdomain-data">
				<?php echo esc_html( $subdomain ); ?>
			</span>
		</div>

		<div class="current-plan">
			<span class="header">
				<?php esc_html_e( 'Plan', 'reviewshake-widgets' ); ?>
			</span>

			<span class="current-plan-data">
				<span class="pricing-plan">
					<?php echo esc_html( ucfirst( $pricing_plan ) ); ?>
				</span>

				<span class="plan-limit">
					<?php echo sprintf( '(%s %s)', esc_html( 5 < $review_source_limit ? __( 'Unlimited', 'reviewshake-widgets' ) : $review_source_limit ), esc_html__( 'review sources', 'reviewshake-widgets' ) ); ?>
				</span>

				<?php if ( 2 !== $review_source_limit || 5 !== $review_source_limit ) : ?>
					<span class="upgrade">
						<a href="<?php echo esc_url( "https://{$subdomain}/admin/billing" ); ?>"><?php esc_html_e( 'Upgrade', 'reviewshake-widgets' ); ?></a>
					</span>
				<?php endif; ?>
			</span>
		</div>
	</div>
</div>

<div class="account-links-wrap">
	<div class="setup-review-sources-widgets">
		<a href="admin.php?page=reviewshake-widgets&tab=setup">
			<input type="button" class="button button-primary setup-sources-and-widgets" value="<?php esc_attr_e( 'Setup Review Sources and Widgets', 'reviewshake-widgets' ); ?>" />
		</a>
	
	</div>

	<span class="connect-another-account">
		<a href=""><?php esc_html_e( 'Connect another account', 'reviewshake-widgets' ); ?></a>
	</span>
</div>
