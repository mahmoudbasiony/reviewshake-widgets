<?php
/**
 * Settings - Admin - Views.
 *
 * @package Reviewshake_Widgets/Templates/Admin/Views
 * @author Reviewshake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<div class="widgets-version-wrap">
	<p class="widgets-version__title">
		<?php esc_html_e( 'Widgets version', 'reviewshake-widgets' ); ?>
	</p>

	<div class="radio-group">
		<label class="widgets-version-label__container" for="reviewshake_widgets_widgets-v2">
			<input type="radio" value="v2" name="reviewshake_widgets_widgets_version" id="reviewshake_widgets_widgets-v2" <?php echo 'v2' === $widgets_version ? 'checked' : ''; ?> />
			<span><?php esc_html_e( 'v2', 'reviewshake-widgets' ); ?></span>
		</label>
		<label class="widgets-version-label__container tooltip" for="reviewshake_widgets_widgets-v1">
			<input type="radio" value="v1" name="reviewshake_widgets_widgets_version" id="reviewshake_widgets_widgets-v1" <?php echo 'v1' === $widgets_version ? 'checked' : ''; ?> />
			<span class="">
				<?php esc_html_e( 'v1 (legacy)', 'reviewshake-widgets' ); ?>
				<span class="tooltiptext"><?php esc_html_e( 'Choose this option for widgets older than April 1st, 2022', 'reviewshake-widgets' ); ?></span>
			</span>
		</label>

		<img class="version-loader" src="<?php echo esc_url( REVIEWSHAKE_WIDGETS_ROOT_URL . 'assets/dist/images/preview-loader.svg' ); ?>" />
	</div>
</div>
