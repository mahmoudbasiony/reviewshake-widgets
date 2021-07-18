<?php
/**
 * The Reviewshake_Widgets_Widget class.
 *
 * @package    Reviewshake_Widgets
 * @subpackage WP_Widget
 * @author  Reviewshake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'WP_Widget' ) ) {
	/**
	 * Widget.
	 *
	 * Extends WP_Widget class.
	 *
	 * @since 1.0.0
	 */
	class Reviewshake_Widgets_Widget extends WP_Widget {

		/**
		 * The available widgets list object.
		 *
		 * @since 1.0.0
		 * @var object
		 */
		private $widgets;

		/**
		 * The account domain.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		private $account_domain;

		/**
		 * The API key.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		private $api_key;

		/**
		 * The constructor
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function __construct() {
			$args = array(
				'classname'   => 'reviewshake_widgets_widget',
				'description' => __( 'Add customizable widgets to showcase reviews from Google, Facebook, Yelp and 80+ other websites', 'reviewshake-widgets' ),
			);

			parent::__construct( 'reviewshake_widgets_widget', __( 'Reviewshake - Reviews Widget.', 'reviewshake-widgets' ), $args );
			$settings = get_option( 'reviewshake_widgets_settings', array() );

			$this->widgets        = reviewshake_rest_list_widgets();
			$this->account_domain = reviewshake_sanitize( 'account_domain', reviewshake_check_settings( $settings, 'account', 'account_domain' ) );
			$this->api_key        = reviewshake_sanitize( 'api_key', reviewshake_check_settings( $settings, 'account', 'api_key' ) );
		}

		/**
		 * Displays widget for the frontend.
		 *
		 * @param array  $args     The widget args.
		 * @param string $instance The widget instance.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function widget( $args, $instance ) {
			$widget_id = ! empty( $instance['widget_id'] ) ? absint( $instance['widget_id'] ) : '';
			$not_found = true;

			echo wp_kses_post( $args['before_widget'] );

			if ( $widget_id ) {
				// Get the widget by ID to validate it's existence.
				$widget     = reviewshake_get_widget( (int) $widget_id, $this->account_domain, $this->api_key );
				$embed      = '';
				$updated_at = 1;
				if ( $widget && isset( $widget->body ) && ! empty( $widget->body ) ) {
					$not_found = false;

					// Get the embed code.
					if ( isset( $widget->body->embed ) && ! empty( $widget->body->embed ) ) {
						$embed = $widget->body->embed;
					} elseif ( isset( $widget->body->widget_type ) ) {
						$widget_type     = esc_attr( strtolower( $widget->body->widget_type ) );
						$organization_id = isset( $widget->body->organization_id ) ? (int) $widget->body->organization_id : '';

						$embed = "https://{$this->account_domain}/widgets/{$widget_type}.js?org={$organization_id}";
					}

					// Get the updated at timestamp to use a version.
					if ( isset( $widget->body->updated_at ) ) {
						$updated_at = strtotime( sanitize_text_field( $widget->body->updated_at ) );
					}
				}

				if ( $embed ) {
					$embed .= "?v={$updated_at}";
					echo '<script src="' . esc_url( $embed ) . '"></script>';
				}
			}

			/*
			 * Show a message if not available widget for admin users only.
			 */
			if ( current_user_can( 'manage_options' ) && $not_found ) {
				$widgets_url = admin_url( 'widgets.php' );
				$setup_url   = admin_url( 'admin.php?page=reviewshake-widgets&tab=setup' );

				/* translators: 1) string URL to setup displayed widget 2) string URL to create a new widget. */
				echo wp_kses_post( '<p>' . sprintf( __( 'Widget is not found. Go to <a href="%1$s">Widgets</a> to select a Reviewshake widget, or go to <a href="%2$s">Setup</a> to create a new one.', 'reviewshake-widgets' ), esc_url( $widgets_url ), esc_url( $setup_url ) ) . '</p>' );

			}

			echo wp_kses_post( $args['after_widget'] );
		}

		/**
		 * Handles widget updates in admin
		 *
		 * @param array $new_instance The new instance.
		 * @param array $old_instance The old instance.
		 *
		 * @since 1.0.0
		 *
		 * @return array $instance
		 */
		public function update( $new_instance, $old_instance ) {
			/* Updates widget title value */
			$instance = $old_instance;

			$instance['widget_id'] = absint( $new_instance['widget_id'] );
			return $instance;
		}

		/**
		 * Display widget form in admin.
		 *
		 * @param  array $instance widget instance.
		 *
		 * @since 1.0.0
		 *
		 * @return mixed
		 */
		public function form( $instance ) {
			$widget_id = isset( $instance['widget_id'] ) ? esc_attr( $instance['widget_id'] ) : '';

			if ( ! empty( $this->widgets ) && is_object( $this->widgets ) && isset( $this->widgets->body ) && ! empty( $this->widgets->body ) ) : ?>
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'widget_id' ) ); ?>"><?php esc_html_e( 'Choose a widget from the list', 'reviewshake-widgets' ); ?></label>
					<select name="<?php echo esc_attr( $this->get_field_name( 'widget_id' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'widget_id' ) ); ?>" class="widefat">
						<option value="0"><?php esc_html_e( '&mdash; Select &mdash;', 'reviewshake-widgets' ); ?></option>
						<?php foreach ( $this->widgets->body as $widget_obj ) : ?>
							<option value="<?php echo esc_attr( $widget_obj->id ); ?>"<?php selected( $widget_id, esc_attr( $widget_obj->id ) ); ?>><?php echo esc_attr( $widget_obj->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>

				<?php
			else :

				$setup_url = admin_url( 'admin.php?page=reviewshake-widgets&tab=setup' );

				/* translators: %s: URL to create a new widget. */
				echo wp_kses_post( '<p>' . sprintf( __( 'No widgets have been created yet. Go to <a href="%s">Setup</a> page to create widgets.', 'reviewshake-widgets' ), esc_url( $setup_url ) ) . '</p>' );

			endif;
		}
	}
}
