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
	 * @since   1.0.0
	 * @version 2.0.0
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
				'classname'                   => 'reviewshake_widgets_widget',
				'description'                 => __( 'Add customizable widgets to showcase reviews from Google, Facebook, Yelp and 80+ other websites', 'reviewshake-widgets' ),
				'customize_selective_refresh' => true,
				'show_instance_in_rest'       => true,
			);

			parent::__construct( 'reviewshake_widgets_widget', __( 'Reviewshake - Reviews Widget.', 'reviewshake-widgets' ), $args );
			$settings = get_option( 'reviewshake_widgets_settings', array() );

			$this->widgets         = reviewshake_rest_list_widgets();
			$this->account_domain  = reviewshake_sanitize( 'account_domain', reviewshake_check_settings( $settings, 'account', 'account_domain' ) );
			$this->api_key         = reviewshake_sanitize( 'api_key', reviewshake_check_settings( $settings, 'account', 'api_key' ) );
			$this->widgets_version = reviewshake_get_widgets_version( $settings );
		}

		/**
		 * Displays widget for the frontend.
		 *
		 * @param array  $args     The widget args.
		 * @param string $instance The widget instance.
		 *
		 * @since   1.0.0
		 * @version 2.0.0
		 *
		 * @return void
		 */
		public function widget( $args, $instance ) {
			$widget_id = ! empty( $instance['widget_id'] ) ? absint( $instance['widget_id'] ) : $this->id;
			$not_found = true;

			echo wp_kses_post( $args['before_widget'] );

			if ( $widget_id ) {

				if ( 'v2' === $this->widgets_version ) {
					// Get the widget by ID to validate it's existence.
					$widget = reviewshake_get_widget( (int) $widget_id, $this->account_domain, $this->api_key, 'v2' );

					if ( $widget && isset( $widget->data ) && isset( $widget->data->attributes ) ) {
						$not_found = false;

						$snippet_html = isset( $widget->data->attributes->snippet_html ) ? $widget->data->attributes->snippet_html : '';

						echo html_entity_decode( esc_html( $snippet_html ) );
					}
				} else {
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
					}
				}
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
		 * @since   1.0.0
		 * @version 2.0.0
		 *
		 * @return mixed
		 */
		public function form( $instance ) {
			$widget_id = isset( $instance['widget_id'] ) ? esc_attr( $instance['widget_id'] ) : '';

			if ( 'v2' === $this->widgets_version ) {
				$widgets_data = $this->widgets->data;
			} else {
				$widgets_data = $this->widgets->body;
			}

			if ( ! empty( $this->widgets ) && is_object( $this->widgets ) && isset( $widgets_data ) && ! empty( $widgets_data ) ) : ?>
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'widget_id' ) ); ?>"><?php esc_html_e( 'Choose a widget from the list', 'reviewshake-widgets' ); ?></label>
					<select name="<?php echo esc_attr( $this->get_field_name( 'widget_id' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'widget_id' ) ); ?>" class="widefat">
						<option value="0"><?php esc_html_e( '&mdash; Select &mdash;', 'reviewshake-widgets' ); ?></option>
						<?php foreach ( $widgets_data as $widget_obj ) : ?>
							<option value="<?php echo esc_attr( $widget_obj->id ); ?>"<?php selected( $widget_id, esc_attr( $widget_obj->id ) ); ?>><?php echo esc_html( ( 'v2' === $this->widgets_version && isset( $widget_obj->attributes, $widget_obj->attributes->name ) ) ? $widget_obj->attributes->name : $widget_obj->name ); ?></option>
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
