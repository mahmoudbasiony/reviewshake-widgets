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

<div class="section reviewshake-widgets-widgets v2-widgets" id="reviewshake-widgets-widgets v2-widgets">
	<h2 class="headline"><?php esc_html_e( 'Widgets', 'reviewshake-widgets' ); ?></h2>

	<div class="add-widget-to-site">
		<a href="<?php echo esc_url( admin_url( 'widgets.php' ) ); ?>" class="appearance-widgets-link"><?php esc_html_e( 'Add a widget to your site?', 'reviewshake-widgets' ); ?></a>
	</div>

	<div class="reviewshake-widgets-widgets-container">
		<?php
		foreach ( $widgets as $widget ) :
			$widget_type  = isset( $widget['widget_type'] ) ? $widget['widget_type'] : '';
			$widget_name  = isset( $widget['name'] ) ? $widget['name'] : '';
			$snippet_html = isset( $widget['snippet_html'] ) ? $widget['snippet_html'] : '';
			$url          = isset( $widget['url'] ) ? esc_url( $widget['url'] ) : '';

			?>
			<div class="reviewshake-widgets-widget section" data-widget-id="<?php echo isset( $widget['id'] ) ? esc_attr( $widget['id'] ) : 0; ?>">
				<div class="widget-header">
					<h2 class="headline">
						<?php echo esc_html( $widget_name . ' - ' . ucfirst( $widget_type ) ); ?>
					</h2>

					<div class="widget-actions" data-version="v2">
						<input type="button" class="button button-primary edit-widget v2" value="<?php esc_html_e( 'Edit', 'reviewshake-widgets' ); ?>" />
						<input type="button" class="button button-primary delete-widget v2" value="<?php esc_html_e( 'Delete', 'reviewshake-widgets' ); ?>" />
					</div>
				</div>
				<div class="widget-preview">
					<?php echo html_entity_decode( esc_htnl( $snippet_html ) ); ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php if ( 'v1' !== $widgets_version ) : ?>
		<div class="new-widget">
			<input type="button" class="button button-primary add-new-widget" value="<?php esc_html_e( 'Add new widget', 'reviewshake-widgets' ); ?>"/>
		</div>
	<?php endif; ?>
</div>

