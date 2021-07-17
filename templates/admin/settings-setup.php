<?php
/**
 * Settings - Setup.
 *
 * @var array $settings - The plugin settings array
 *
 * @package Reviewshake_Widgets/Templates/Admin
 * @author  Reviewshake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// General settings.
$settings = get_option( 'reviewshake_widgets_settings', array() );

// Get the account subdomain.
$subdomain = reviewshake_check_settings( $settings, 'account', 'subdomain' ) ? esc_attr( reviewshake_check_settings( $settings, 'account', 'subdomain' ) ) : esc_attr( reviewshake_get_subdomain_from_url() );

$is_account_exists   = reviewshake_is_account_exist_in_db();
$review_sources_db   = isset( $settings['review_sources'] ) ? $settings['review_sources'] : array();
$review_source_count = isset( $review_sources_db ) ? count( $review_sources_db ) : 0;
$current_plan        = reviewshake_get_current_pricing_plan();
$review_source_limit = reviewshake_get_review_sources_limit( $current_plan );
?>

<div class="reviewshake-widgets-setup" id="reviewshake-widgets-setup">
	<div class="reviewshake-widgets-setup-wrap" id="reviewshake-widgets-setup-wrap">
		<div class="section reviewshake-widgets-review-sources-container">
			<h2 class="headline"><?php esc_html_e( 'Review Sources', 'reviewshake-widgets' ); ?></h2>
			<h2 class="setup-welcome-notice"><?php 0 === $review_source_count ? esc_html_e( 'Add your first review source to get going!', 'reviewshake-widgets' ) : esc_html_e( 'You can choose from 80+ review sources.', 'reviewshake-widgets' ); ?></h2>

			<!-- The review source section -->
			<?php
				/*
				 * List review sources for saved account.
				 */
			if ( isset( $review_sources_db ) && ! empty( $review_sources_db ) ) {
				echo '<table class="form-table review-sources-list-table review-sources-table">';
				foreach ( $review_sources_db as $source_id => $review_source ) {
					include 'views/review-sources.php';
				}
				echo '</table>';
			}
			?>

			<?php
				/*
				 * Validates user plan subscription limit.
				 */
			if ( $review_source_count < $review_source_limit ) {
				include 'views/add-review-source.php';
			} else {
				// Include upgrade account template.
				include 'views/upgrade-account.php';
			}
			?>
		</div>

		<!-- The widgets section -->
		<?php
			// Get widgets from db.
			$widgets = isset( $settings['widgets'] ) ? $settings['widgets'] : array();

			// Validate if is account exists.
		if ( $is_account_exists ) {
			// If not empty widgets then display them rather than display a form.
			if ( ! empty( $widgets ) ) {
				include 'views/widgets.php';
			} else {
				include 'views/create-edit-widget.php';
			}
		}
		?>
	</div>
</div>
