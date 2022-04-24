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

	<div class="add-widget-to-site">
		<a href="<?php echo esc_url( admin_url( 'widgets.php' ) ); ?>" class="appearance-widgets-link"><?php esc_html_e( 'Add a widget to your site?', 'reviewshake-widgets' ); ?></a>
	</div>

	<div class="reviewshake-widgets-widgets-container">
		<?php
		foreach ( $widgets as $widget ) :
			$widget_type = isset( $widget['widget_type'] ) ? $widget['widget_type'] : '';
			$widget_name = isset( $widget['name'] ) ? $widget['name'] : '';
			$updated_at  = isset( $widget['updated_at'] ) ? strtotime( sanitize_text_field( $widget['updated_at'] ) ) : 1;
			$embed       = isset( $widget['embed'] ) ? $widget['embed'] : 'https://' . $settings['account']['account_domain'] . '/widgets/' . strtolower( $widget_type ) . '.js';
			$embed      .= "?v={$updated_at}";
			?>
			<div class="reviewshake-widgets-widget section" data-widget-id="<?php echo isset( $widget['id'] ) ? esc_attr( $widget['id'] ) : 0; ?>">
				<div class="widget-header">
					<h2 class="headline">
						<?php echo esc_html( $widget_name . ' - ' . $widget_type ); ?>
					</h2>

					<div class="widget-actions" data-version="v1">
						<input type="button" class="button button-primary edit-widget" value="<?php esc_html_e( 'Edit', 'reviewshake-widgets' ); ?>" />
						<input type="button" class="button button-primary delete-widget" value="<?php esc_html_e( 'Delete', 'reviewshake-widgets' ); ?>" />
					</div>
				</div>
				<div class="widget-preview">
					<?php
						// Check it current WordPress version is greater or equal to 5.7.
					if ( reviewshake_check_wordpress_version( '5.7', '>=' ) ) {
						wp_print_script_tag(
							array(
								'type' => 'text/javascript',
								'src'  => esc_url( $embed ),
							)
						);
					} else {
						echo '<script src="' . esc_url( $embed ) . '"></script>';
					}
					?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>

