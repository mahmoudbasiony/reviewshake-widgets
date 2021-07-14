<?php
/**
 * REST API: Reviewshake_Widgets_REST_Widgets_Controller class.
 *
 * @package Reviewshake_Widgets
 * @subpackage REST_API
 * @author  Reviewshake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'WP_REST_Controller' ) ) :

	/**
	 *
	 * Core class used to manage reviewshake widgets via the REST API.
	 *
	 * @since 1.0.0
	 *
	 * @see WP_REST_Controller
	 */
	class Reviewshake_Widgets_REST_Widgets_Controller extends WP_REST_Controller {

		/**
		 * The version of this controller's route.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $version;

		/**
		 * The plugin settings.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		private $settings;

		/**
		 * The account domain.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $account_domain;

		/**
		 * The API key.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $api_key;

		/**
		 * The constructor.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function __construct() {
			$this->version   = '1';
			$this->namespace = 'reviewshake/v' . $this->version;
			$this->rest_base = 'widgets';

			$this->settings       = get_option( 'reviewshake_widgets_settings', array() );
			$this->account_domain = reviewshake_check_settings( $this->settings, 'account', 'account_domain' );
			$this->api_key        = reviewshake_check_settings( $this->settings, 'account', 'api_key' );
		}

		/**
		 * Register the routes for the objects of the controller.
		 */
		public function register_routes() {

			register_rest_route(
				$this->namespace,
				'/' . $this->rest_base,
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_items' ),
						'permission_callback' => array( $this, 'get_items_permissions_check' ),
						'args'                => $this->get_collection_params(),
					),

					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( $this, 'create_item' ),
						'permission_callback' => array( $this, 'create_item_permissions_check' ),
						'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
					),
				)
			);

			register_rest_route(
				$this->namespace,
				'/' . $this->rest_base . '/(?P<id>\d+)',
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_item' ),
						'permission_callback' => array( $this, 'get_item_permissions_check' ),
						'args'                => $this->get_collection_params(),
					),

					array(
						'methods'             => WP_REST_Server::EDITABLE,
						'callback'            => array( $this, 'update_item' ),
						'permission_callback' => array( $this, 'update_item_permissions_check' ),
						'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
					),

					array(
						'methods'             => WP_REST_Server::DELETABLE,
						'callback'            => array( $this, 'delete_item' ),
						'permission_callback' => array( $this, 'delete_item_permissions_check' ),
					),
				)
			);

		}

		/**
		 * Check if a given request has access to get items.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 *
		 * @return WP_Error|bool
		 */
		public function get_items_permissions_check( $request ) {
			return current_user_can( 'manage_options' );

		}

		/**
		 * Get all widgets.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 *
		 * @return WP_Error|WP_REST_Response
		 */
		public function get_items( $request ) {

			$account_domain = $request->get_param( 'subdomain' );
			$api_key        = $request->get_param( 'apikey' );

			if ( ! $account_domain || ! $api_key ) {
				return new WP_Error(
					'reviewshake_error_on_list_widgets',
					esc_html__( 'List Widgets', 'reviewshake-widgets' ),
					array(
						'status' => 404,
						'detail' => esc_html__(
							'Account domain and/or API Key doesnot exists!',
							'reviewshake-widgets'
						),
					)
				);
			}

			if ( function_exists( 'reviewshake_get_list_of_widgets' ) ) {
				$widgets = reviewshake_get_list_of_widgets( $account_domain, $api_key );

				// Validate errors.
				if ( isset( $result->rscode ) && 200 !== $result->rscode ) {
					$status  = isset( $widgets->status ) ? $widgets->status : $widgets->rscode;
					$message = isset( $widgets->message ) ? $widgets->message : esc_html__( 'Something went wrong!', 'reviewshake-widgets' );

					return new WP_Error(
						'reviewshake_error_on_list_widgets',
						esc_html__( 'List Widgets', 'reviewshake-widgets' ),
						array(
							'status' => $status,
							'detail' => $message,
						)
					);
				}

				$prepared_widgets = $this->prepare_widgets_for_database( $widgets );

				/**
				 * Filters the widgets before it is inserted via the REST API.
				 *
				 * Allows modification of the widgets right before it is inserted to db.
				 * Returning a WP_Error value from the filter will short-circuit insertion and allow
				 * skipping further processing.
				 *
				 * @since 1.0.0
				 *
				 * @param array|WP_Error  $prepared_sources The prepared review sources data for insertion.
				 * @param WP_REST_Request $request          Request used to insert the comment.
				 */
				$prepared_widgets = apply_filters( 'rest_pre_insert_widgets', $prepared_widgets, $request );

				if ( is_wp_error( $prepared_widgets ) ) {
					return $prepared_widgets;
				}

				$save_widgets = reviewshake_save_settings( 'widgets', $prepared_widgets, true );

				if ( is_object( $widgets ) ) {
					$widgets->count = count( $prepared_widgets );
				}

				$response = $this->prepare_item_for_response( $widgets, $request );

				return rest_ensure_response( $response );
			}
		}

		/**
		 * Check if a given request has access to create items
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|bool
		 */
		public function create_item_permissions_check( $request ) {
			return current_user_can( 'manage_options' );
		}

		/**
		 * Create one item from the collection
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 *
		 * @since 1.0.0
		 *
		 * @return WP_Error|WP_REST_Response
		 */
		public function create_item( $request ) {
			$data = $this->prepare_item_for_database( $request );

			if ( is_wp_error( $data ) ) {
				return new WP_Error( $data->get_error_code(), $data->get_error_message(), $data->get_error_data() );
			}

			if ( function_exists( 'reviewshake_create_widget' ) ) {
				$widget = reviewshake_create_widget( $data, $this->account_domain, $this->api_key );

				// Validate errors.
				if ( isset( $widget->rscode ) && 200 !== $widget->rscode ) {
					$status         = $widget->rscode;
					$message        = isset( $widget->message ) ? $widget->message : esc_html__( 'Something went wrong!', 'reviewshake-widgets' );
					$args['status'] = $status;
					$args['detail'] = isset( $widget->errors ) ? reviewshake_get_error_messages( $widget->errors ) : esc_html__( 'Something went wrong on create widget!', 'reviewshake-widgets' );

					return new WP_Error( 'reviewshake_error_on_create_widget', $message, $args );
				}

				if ( ! isset( $widget->id ) || ! isset( $widget->embed ) ) {
					return new WP_Error(
						'reviewshake_error_on_create_widget',
						esc_html__( 'Create Widget', 'reviewshake-widgets' ),
						array(
							'status' => 400,
							'detail' => esc_html__(
								'Something went wrong! The widget ID and/or embed code is invalid.',
								'reviewshake-widgets'
							),
						)
					);
				}

				// Append to the prepared data.
				$data['id']    = reviewshake_sanitize( 'id', $widget->id );
				$data['embed'] = reviewshake_sanitize( 'embed', $widget->embed );

				$prefix               = 'widget';
				$key                  = (string) $prefix . $data['id'];
				$widget_array         = array();
				$widget_array[ $key ] = $data;

				$save_widget = reviewshake_save_settings( 'widgets', $widget_array );

				if ( ! $save_widget ) {
					return new WP_Error(
						'reviewshake_error_on_save_widget_to_db',
						esc_html__( 'Create Widget', 'reviewshake-widgets' ),
						array(
							'status' => 401,
							'detail' => esc_html__(
								'Something went wrong! The widget cannot be saved to database!',
								'reviewshake-widgets'
							),
						)
					);
				}

				$response = $this->prepare_item_for_response( $widget, $request );

				return rest_ensure_response( $response );
			}
		}

		/**
		 * Check if a given request has access to get item.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 *
		 * @return WP_Error|bool
		 */
		public function get_item_permissions_check( $request ) {
			return current_user_can( 'manage_options' );
		}

		/**
		 * Get a specific widget.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 *
		 * @return WP_Error|WP_REST_Response
		 */
		public function get_item( $request ) {
			$widget_id = $request['id'];

			$error = new WP_Error(
				'reviewshake_rest_invalid_widget_id',
				esc_html__( 'Invalid widget ID.', 'reviewshake-widgets' ),
				array(
					'status' => 404,
					'detail' => esc_html__( 'The current widget is not found on your account!', 'reviewshake-widgets' ),
				)
			);

			if ( (int) $widget_id <= 0 ) {
				return $error;
			}

			$widget = reviewshake_get_widget( (int) $widget_id, $this->account_domain, $this->api_key );

			if ( empty( $widget ) || ! isset( $widget->rscode ) || 200 !== $widget->rscode ) {
				return $error;
			}

			return new WP_REST_Response( $widget, 200 );
		}

		/**
		 * Check if a given request has access to update item
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|bool
		 */
		public function update_item_permissions_check( $request ) {
			return current_user_can( 'manage_options' );
		}

		/**
		 * Update one item from the collection.
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 *
		 * @since 1.0.0
		 *
		 * @return WP_Error|WP_REST_Response
		 */
		public function update_item( $request ) {
			$valid_check = $this->get_item( $request );

			if ( is_wp_error( $valid_check ) ) {
				return $valid_check;
			}

			$data = $this->prepare_item_for_database( $request );

			if ( is_wp_error( $data ) ) {
				return new WP_Error( $data->get_error_code(), $data->get_error_message(), $data->get_error_data() );
			}

			if ( function_exists( 'reviewshake_update_widget' ) ) {
				$widget = reviewshake_update_widget( $data, $this->account_domain, $this->api_key );

				// Validate errors.
				if ( isset( $widget->rscode ) && 200 !== $widget->rscode ) {
					$status         = $widget->rscode;
					$message        = isset( $widget->message ) ? $widget->message : esc_html__( 'Something went wrong!', 'reviewshake-widgets' );
					$args['status'] = $status;
					$args['detail'] = isset( $widget->errors ) ? reviewshake_get_error_messages( $widget->errors ) : esc_html__( 'Something went wrong on update widget!', 'reviewshake-widgets' );

					// @TODO: Handle errors.
					return new WP_Error( 'reviewshake_error_on_update_widget', $message, $args );
				}

				if ( isset( $widget->embed ) && ! empty( $widget->embed ) ) {
					$embed = reviewshake_sanitize( 'embed', $result->embed );
				} else {
					$type  = strtolower( $data['widget_type'] );
					$embed = reviewshake_sanitize( 'embed', "https://{$this->account_domain}/widgets/{$type}.js?org=1" );
				}

				$widget->embed = $embed;
				$data['embed'] = $embed;

				$prefix               = 'widget';
				$key                  = (string) $prefix . $data['id'];
				$widget_array         = array();
				$widget_array[ $key ] = $data;

				$save_widget = reviewshake_save_settings( 'widgets', $widget_array );

				if ( ! $save_widget ) {
					new WP_Error(
						'reviewshake_error_on_save_widget_to_db',
						esc_html__( 'Save Widget', 'reviewshake-widgets' ),
						array(
							'status' => 400,
							'detail' => esc_html__(
								'Something went wrong while saving widget, please try again later.',
								'reviewshake-widgets'
							),
						)
					);
				}

				$response = $this->prepare_item_for_response( $widget, $request );

				return rest_ensure_response( $response );
			}

			die();
		}

		/**
		 * Check if a given request has access to delete a specific item
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 *
		 * @since 1.0.0
		 *
		 * @return WP_Error|bool
		 */
		public function delete_item_permissions_check( $request ) {
			return current_user_can( 'manage_options' );
		}

		/**
		 * Delete one item from the collection
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 *
		 * @since 1.0.0
		 *
		 * @return WP_Error|WP_REST_Response
		 */
		public function delete_item( $request ) {
			$widget_id = $request->get_param( 'id' );

			$prefixed_id = (string) 'widget' . $widget_id;

			if ( ! $widget_id || ! isset( $this->settings['widgets'] ) || ! isset( $this->settings['widgets'][ $prefixed_id ] ) ) {
				return new WP_Error(
					'reviewshake_rest_invalid_review_widget_id',
					esc_html__( 'Delete Widget', 'reviewshake-widgets' ),
					array(
						'status' => 404,
						'detail' => esc_html__( 'Something went wrong! Invalid Widget ID.', 'reviewshake-widgets' ),
					)
				);
			}

			if ( function_exists( 'reviewshake_delete_widget' ) ) {
				$result = reviewshake_delete_widget( (int) $widget_id, $this->account_domain, $this->api_key );

				// Validate errors.
				if ( isset( $result->rscode ) && 200 !== $result->rscode ) {
					$status  = isset( $result->status ) ? $result->status : $result->rscode;
					$message = isset( $result->message ) ? $result->message : esc_html__( 'Something went wrong!', 'reviewshake-widgets' );

					return new WP_Error(
						'reviewshake_error_on_delete_widget',
						esc_html__( 'Delete Widget', 'reviewshake-widgets' ),
						array(
							'status' => $status,
							'detail' => $message,
						)
					);
				}

				$deleted = reviewshake_remove_widget( (int) $widget_id );

				$response = new WP_REST_Response();

				$response->set_data(
					array(
						'deleted' => $deleted,
						'html'    => reviewshake_renders_setup_tab_content(),
					)
				);

				return rest_ensure_response( $response );
			}
		}

		/**
		 * Prepare the widgets for create or update operation.
		 *
		 * @param object $widgets The widgets.
		 *
		 * @since 1.0.0
		 *
		 * @return WP_Error|array $prepared_item
		 */
		protected function prepare_widgets_for_database( $widgets ) {
			$prepared_item = array();

			if ( isset( $widgets->status, $widgets->body ) && 200 === $widgets->status && ! empty( $widgets->body ) ) {
				$prefix = 'widget';

				foreach ( $widgets->body as $widget ) {
					$widget_id = (string) $prefix . $widget->id;
					if ( is_object( $widget ) ) {
						foreach ( $widget as $key => $value ) {
							$prepared_item[ $widget_id ][ $key ] = reviewshake_sanitize( $key, $value );
						}
					}
				}
			}

			// Return sanitized and prepared item.
			return $prepared_item;
		}

		/**
		 * Prepare the item for create or update operation
		 *
		 * @param WP_REST_Request $request Request object.
		 * @return WP_Error|object $prepared_item.
		 */
		protected function prepare_item_for_database( $request ) {
			$prepared_item = array();

			$defaults = array(
				'display_mode'            => 0,
				'locale'                  => 'en',
				'request_button'          => false,
				'width'                   => 0,
				'height'                  => 0,
				'display_elements'        => array( 0, 1, 2, 3, 4 ),
				'background_color'        => 'rgba(255,230,230,1)',
				'review_background_color' => 'rgba(189,189,241,1)',
				'text_color'              => 'rgba(65,117,5,1)',
				'link_color'              => 'rgba(189,16,224,1)',
				'button_color'            => 'rgba(0,0,0,100)',
				'star_color'              => 'rgba(127,96,0,100)',
				'title_font_size'         => '30',
				'title_font_weight'       => 1,
				'body_font_size'          => 18,
				'styling'                 => 'simple',
				'custom_css'              => '',
				'ex_reviews_source'       => array(),
				'ex_star_rating'          => array(),
				'ex_empty_reviews'        => true,
				'max_reviews'             => '5',
				'summary_header'          => true,
				'rich_snippet'            => false,
				'rich_snippet_type'       => 'local_business',
				'rich_snippet_meta_data'  => (object) array(),
			);

			$args = array(
				'name'                    => $request->get_param( 'name' ),
				'widget_type'             => $request->get_param( 'widget_type' ),
				'display_mode'            => $request->get_param( 'display_mode' ),
				'ex_star_rating'          => json_decode( $request->get_param( 'ex_star_rating' ) ),
				'max_reviews'             => $request->get_param( 'max_reviews_per_display' ),
				'locale'                  => $request->get_param( 'locale' ),
				'background_color'        => $request->get_param( 'background_color' ),
				'review_background_color' => $request->get_param( 'review_background_color' ),
				'text_color'              => $request->get_param( 'text_color' ),
				'link_color'              => $request->get_param( 'link_color' ),
				'title_font_size'         => $request->get_param( 'title_font_size' ),
				'title_font_weight'       => $request->get_param( 'title_font_weight' ),
			);

			$parse_args = wp_parse_args( $args, $defaults );

			if ( isset( $parse_args['display_mode'] ) && 1 == $parse_args['display_mode'] ) {
				$parse_args['summary_header'] = false;
			}

			foreach ( $parse_args as $key => $value ) {
				$prepared_item[ $key ] = reviewshake_sanitize( $key, $value );
			}

			if ( isset( $request['id'] ) && ! empty( $request['id'] ) ) {
				$prepared_item['id'] = reviewshake_sanitize( 'id', $request['id'] );
			}

			if ( empty( $prepared_item['name'] ) ) {
				return new WP_Error(
					'reviewshake_error_on_create_update_widget',
					esc_html__( 'Create Widget', 'reviewshake-widgets' ),
					array(
						'status' => 400,
						'detail' => esc_html__(
							'Widget Name is a required field!',
							'reviewshake-widgets'
						),
					)
				);
			}

			if ( empty( $prepared_item['widget_type'] ) ) {
				return new WP_Error(
					'reviewshake_error_on_create_update_widget',
					esc_html__( 'Create Widget', 'reviewshake-widgets' ),
					array(
						'status' => 422,
						'detail' => esc_html__(
							'Widget Type is a required field and cannot be blank',
							'reviewshake-widgets'
						),
					)
				);
			}

			return $prepared_item;
		}

		/**
		 * Prepare the item for the REST response
		 *
		 * @param mixed           $result WordPress representation of the item.
		 * @param WP_REST_Request $request Request object.
		 *
		 * @since 1.0.0
		 *
		 * @return mixed
		 */
		public function prepare_item_for_response( $result, $request ) {
			$response = new WP_REST_Response( $result, 200 );
			return $response;
		}

		/**
		 * Get the query params for collections.
		 *
		 * @since 1.0.0
		 *
		 * @return array
		 */
		public function get_collection_params() {
			return array(
				'subdomain' => array(
					'description'       => esc_html__( 'The Reviewshake account subdomain.', 'reviewshake-widgets' ),
					'type'              => 'string',
					'default'           => $this->account_domain,
					'validate_callback' => 'rest_validate_request_arg',
					'sanitize_callback' => 'rest_sanitize_request_arg',
				),
				'apikey'    => array(
					'description'       => esc_html__( 'The Reviewshake account API key.', 'reviewshake-widgets' ),
					'type'              => 'string',
					'default'           => $this->api_key,
					'validate_callback' => 'rest_validate_request_arg',
					'sanitize_callback' => 'rest_sanitize_request_arg',
				),
			);
		}

		/**
		 * Retrieves the widgets's schema, conforming to JSON Schema.
		 *
		 * @since 1.0.0
		 *
		 * @return array $schema
		 */
		public function get_item_schema() {
			$schema = array(
				'$schema'    => 'http://json-schema.org/draft-04/schema#',
				'title'      => 'reviewshake-widgets',
				'type'       => 'object',
				'properties' => array(
					'id'        => array(
						'description' => esc_html__( 'Unique identifier for the widget.', 'reviewshake-widgets' ),
						'type'        => 'integer',
						'readonly'    => true,
					),
					'subdomain' => array(
						'description' => esc_html__( 'The Reviewshake account domain.', 'reviewshake-widgets' ),
						'type'        => 'string',
						'context'     => array( 'view' ),
						'arg_options' => array(
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
					'apikey'    => array(
						'description' => esc_html__( 'The Reviewshake account API key.', 'reviewshake-widgets' ),
						'type'        => 'string',
						'context'     => array( 'view' ),
						'arg_options' => array(
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
				),
			);

			return $schema;
		}

	}

endif;
