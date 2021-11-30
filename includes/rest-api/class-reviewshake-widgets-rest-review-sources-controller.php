<?php
/**
 * REST API: Reviewshake_Widgets_REST_Review_Sources_Controller class.
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
	 * Core class used to manage reviewshake review sources via the REST API.
	 *
	 * @since 1.0.0
	 *
	 * @see WP_REST_Controller
	 */
	class Reviewshake_Widgets_REST_Review_Sources_Controller extends WP_REST_Controller {

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
			$this->rest_base = 'review_sources';

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
		 * Get all review sources.
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
					'reviewshake_error_on_list_review_sources',
					esc_html__( 'Review Sources', 'reviewshake-widgets' ),
					array(
						'status' => 404,
						'detail' => esc_html__(
							'Account domain and/or API Key doesnot exists!',
							'reviewshake-widgets'
						),
					)
				);
			}

			if ( function_exists( 'reviewshake_get_list_of_review_sources' ) ) {
				$review_sources = reviewshake_get_list_of_review_sources( $account_domain, $api_key );

				// Validate errors.
				if ( isset( $review_sources->rscode ) && 200 !== $review_sources->rscode ) {
					$status  = isset( $review_sources->status ) ? $review_sources->status : $review_sources->rscode;
					$message = isset( $review_sources->message ) ? $review_sources->message : esc_html__( 'Something went wrong!', 'reviewshake-widgets' );

					return new WP_Error(
						'reviewshake_error_on_list_review_sources',
						esc_html__( 'Review Sources', 'reviewshake-widgets' ),
						array(
							'status' => $status,
							'detail' => $message,
						)
					);
				}

				$prepared_sources = $this->prepare_sources_for_database( $review_sources );

				/**
				 * Filters the review sources before it is inserted via the REST API.
				 *
				 * Allows modification of the review sources right before it is inserted to db.
				 * Returning a WP_Error value from the filter will short-circuit insertion and allow
				 * skipping further processing.
				 *
				 * @since 1.0.0
				 *
				 * @param array|WP_Error  $prepared_sources The prepared review sources data for insertion.
				 * @param WP_REST_Request $request          Request used to insert the comment.
				 */
				$prepared_sources = apply_filters( 'rest_pre_insert_review_sources', $prepared_sources, $request );

				if ( is_wp_error( $prepared_sources ) ) {
					return $prepared_sources;
				}

				$save_sources = reviewshake_save_settings( 'review_sources', $prepared_sources, true );

				if ( is_object( $review_sources ) ) {
					$review_sources->count = count( $prepared_sources );
				}

				$response = $this->prepare_item_for_response( $review_sources, $request );

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
		 * @return WP_Error|WP_REST_Response
		 */
		public function create_item( $request ) {
			$data = $this->prepare_item_for_request( $request );

			if ( is_wp_error( $data ) ) {
				return new WP_Error( $data->get_error_code(), $data->get_error_message(), $data->get_error_data() );
			}

			$state = array(
				'request_type'    => 'create_review_source',
				'request_no'      => 5,
				'connection_type' => 'setup',
			);

			// Set state.
			$set_state = reviewshake_save_settings( 'state', $state );

			if ( function_exists( 'reviewshake_add_review_source' ) ) {
				$review_source = reviewshake_add_review_source( $data );

				$state = array(
					'source_status'   => 'completed',
					'source_name'     => '',
					'source_url'      => '',
					'google_place_id' => '',

				);

				// Set state.
				$set_state = reviewshake_save_settings( 'state', $state );

				// Validate errors.
				if ( isset( $review_source->errors ) && is_array( $review_source->errors ) ) {
					$status  = isset( $review_source->errors[0]->status ) ? $review_source->errors[0]->status : 404;
					$message = isset( $review_source->errors[0]->detail ) ? $review_source->errors[0]->detail : esc_html__( 'Something went wrong!', 'reviewshake-widgets' );

					return new WP_Error(
						'reviewshake_error_on_add_review_source',
						esc_html__( 'Add Review Source', 'reviewshake-widgets' ),
						array(
							'status' => $status,
							'detail' => $message,
						)
					);
				}

				$prepared_source = $this->prepare_review_source_for_database( $review_source );

				/**
				 * Filters the single review source before it is inserted via the REST API.
				 *
				 * Allows modification of the review source right before it is inserted to db.
				 * Returning a WP_Error value from the filter will short-circuit insertion and allow
				 * skipping further processing.
				 *
				 * @since 1.0.0
				 *
				 * @param array|WP_Error  $prepared_source The prepared review source data for insertion.
				 * @param WP_REST_Request $request         Request used to insert the comment.
				 */
				$prepared_source = apply_filters( 'rest_pre_insert_review_source', $prepared_source, $request );

				if ( is_wp_error( $prepared_source ) || empty( $prepared_source ) ) {
					return $prepared_sources;
				}

				$save_source = reviewshake_save_settings( 'review_sources', $prepared_source );

				if ( function_exists( 'reviewshake_renders_setup_tab_content' ) ) {
					$review_source->html = reviewshake_renders_setup_tab_content();
				}

				$response = $this->prepare_item_for_response( $review_source, $request );

				return rest_ensure_response( $response );
			}
		}

		/**
		 * Check if a given request has access to get a specific item
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|bool
		 */
		public function delete_item_permissions_check( $request ) {
			return current_user_can( 'manage_options' );
		}

		/**
		 * Delete one item from the collection
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 *
		 * @return WP_Error|WP_REST_RESPONSE
		 */
		public function delete_item( $request ) {
			$source_id   = $request->get_param( 'id' );
			$prefixed_id = (string) 'rs' . $source_id;

			if ( ! $source_id || ! isset( $this->settings['review_sources'] ) || ! isset( $this->settings['review_sources'][ $prefixed_id ] ) ) {
				return new WP_Error(
					'reviewshake_rest_invalid_review_source_id',
					esc_html__( 'Invalid Review Source', 'reviewshake-widgets' ),
					array(
						'status' => 404,
						'detail' => esc_html__( 'Invalid Review Source ID', 'reviewshake-widgets' ),
					)
				);
			}

			if ( function_exists( 'reviewshake_delete_review_source' ) ) {
				$result = reviewshake_delete_review_source( $this->account_domain, $this->api_key, $source_id );

				// Validate errors.
				if ( isset( $result->rscode ) && 200 !== $result->rscode ) {
					$status  = isset( $result->status ) ? $result->status : $result->rscode;
					$message = isset( $result->message ) ? $result->message : esc_html__( 'Something went wrong!', 'reviewshake-widgets' );

					return new WP_Error(
						'reviewshake_error_on_delete_review_source',
						esc_html__( 'Review Source', 'reviewshake-widgets' ),
						array(
							'status' => $status,
							'detail' => $message,
						)
					);
				}

				// Add another error validations as continue changing API.
				if ( isset( $result->errors ) && is_array( $result->errors ) ) {
					$status  = isset( $result->errors[0]->status ) ? (int) $result->errors[0]->status : 404;
					$message = isset( $result->errors[0]->detail ) ? esc_html( $result->errors[0]->detail ) : esc_html__( 'Something went wrong!', 'reviewshake-widgets' );

					return new WP_Error(
						'reviewshake_error_on_delete_review_source',
						esc_html__( 'Review Source', 'reviewshake-widgets' ),
						array(
							'status' => $status,
							'detail' => $message,
						)
					);
				}

				$deleted = reviewshake_remove_review_source( $source_id );

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
		 * Prepare the review source to send to reviewshake API
		 *
		 * @param WP_REST_Request $request Request object.
		 *
		 * @since   1.0.0
		 * @version 1.1.0
		 *
		 * @return WP_Error|array $prepared_item
		 */
		protected function prepare_item_for_request( $request ) {

			$prepared_item = array();

			$account_domain  = $request->get_param( 'subdomain' ) ? $request->get_param( 'subdomain' ) : $this->account_domain;
			$api_key         = $request->get_param( 'apikey' ) ? $request->get_param( 'apikey' ) : $this->api_key;
			$source_name     = reviewshake_sanitize( 'source', $request->get_param( 'source' ) );
			$source_url      = 'google' !== $source_name ? reviewshake_sanitize( 'source_url', $request->get_param( 'sourceUrl' ) ) : reviewshake_sanitize( 'source_url_text', $request->get_param( 'sourceUrl' ) );
			$google_place_id = reviewshake_sanitize( 'google_place_id', $request->get_param( 'googlePlaceId' ) );

			// Set the sanitized data array.
			$prepared_item = array(
				'subdomain'       => $account_domain,
				'apikey'          => $api_key,
				'source'          => $source_name,
				'sourceurl'       => $source_url,
				'google_place_id' => $google_place_id,
			);

			if ( empty( $prepared_item['source'] ) ) {
				return new WP_Error(
					'reviewshake_error_on_create_review_source',
					esc_html__( 'Add Review Source', 'reviewshake-widgets' ),
					array(
						'status' => 422,
						'detail' => esc_html__(
							'Source name is required!',
							'reviewshake-widgets'
						),
					)
				);
			}

			if ( empty( $prepared_item['sourceurl'] ) ) {
				return new WP_Error(
					'reviewshake_error_on_create_review_source',
					esc_html__( 'Add Review Source', 'reviewshake-widgets' ),
					array(
						'status' => 422,
						'detail' => esc_html__(
							'Source URL is required and cannot be blank!',
							'reviewshake-widgets'
						),
					)
				);
			}

			if ( 'google' === $prepared_item['source'] && empty( $prepared_item['google_place_id'] ) ) {
				return new WP_Error(
					'reviewshake_error_on_create_review_source',
					esc_html__( 'Add Review Source', 'reviewshake-widgets' ),
					array(
						'status' => 422,
						'detail' => esc_html__(
							'Google Place ID is required and cannot be blank!',
							'reviewshake-widgets'
						),
					)
				);
			}

			return $prepared_item;
		}

		/**
		 * Prepare the review sources for create or update operation.
		 *
		 * @param object $review_sources The review sources.
		 *
		 * @since   1.0.0
		 * @version 1.1.0
		 *
		 * @return WP_Error|array $prepared_item
		 */
		protected function prepare_sources_for_database( $review_sources ) {
			$prepared_item = array();
			$integration   = array();

			if ( isset( $review_sources->data ) && ! empty( $review_sources->data ) ) {
				$prefix = 'rs';
				foreach ( $review_sources->data as $source ) {
					$source_id = (string) $prefix . $source->id;
					if ( is_object( $source ) ) {
						foreach ( $source as $key => $value ) {
							if ( is_object( $value ) || is_array( $value ) ) {
								foreach ( $value as $nest_key => $nest_value ) {
									if ( 'integration' === $nest_key && is_object( $nest_value ) && isset( $nest_value->data ) && is_object( $nest_value->data ) && ! empty( $nest_value->data ) ) {
										foreach ( $nest_value->data as $integration_key => $integration_value ) {
											if ( is_object( $integration_value ) && ! empty( $integration_value ) ) {
												foreach ( $integration_value as $integration_sub_key => $integration_sub_value ) {
													if ( is_object( $integration_sub_value ) && ! empty( $integration_sub_value ) ) {
														foreach ( $integration_sub_value as $sub_sub_key => $sub_sub_value ) {
															if ( ! is_object( $sub_sub_value ) ) {
																$integration[ $sub_sub_key ] = reviewshake_sanitize( $sub_sub_key, $sub_sub_value );
															}
														}
													} else {
														$integration[ $integration_sub_key ] = reviewshake_sanitize( $integration_sub_key, $integration_sub_value );
													}
												}
											} else {
												$integration[ $integration_key ] = reviewshake_sanitize( $integration_key, $integration_value );
											}
										}
									}

									$prepared_item[ $source_id ][ $nest_key ] = 'source_url' !== $nest_key ? reviewshake_sanitize( $nest_key, $nest_value ) : $nest_value;
								}
							} else {
								$prepared_item[ $source_id ][ $key ] = 'source_url' !== $key ? reviewshake_sanitize( $key, $value ) : $value;
							}
						}

						$prepared_item[ $source_id ]['source_url']  = 'google' === $prepared_item[ $source_id ]['source_name'] ? reviewshake_sanitize( 'source_url_text', $prepared_item[ $source_id ]['source_url'] ) : reviewshake_sanitize( 'source_url', $prepared_item[ $source_id ]['source_url'] );
						$prepared_item[ $source_id ]['integration'] = $integration;
					}
				}
			}

			// Return sanitized and prepared item.
			return $prepared_item;
		}

		/**
		 * Prepare the review source for create or update operation.
		 *
		 * @param object $review_source The review source object.
		 *
		 * @since   1.0.0
		 * @version 1.1.0
		 *
		 * @return WP_Error|array $prepared_item
		 */
		protected function prepare_review_source_for_database( $review_source ) {
			$prepared_item = array();
			$integration   = array();

			if ( isset( $review_source->data ) && is_object( $review_source->data ) && isset( $review_source->data->id ) ) {
				$prefix    = 'rs';
				$source_id = (string) $prefix . $review_source->data->id;

				foreach ( $review_source->data as $key => $value ) {
					if ( is_object( $value ) && ! empty( $value ) ) {
						foreach ( $value as $nest_key => $nest_value ) {
							if ( 'integration' === $nest_key && is_object( $nest_value ) && isset( $nest_value->data ) && is_object( $nest_value->data ) && ! empty( $nest_value->data ) ) {
								foreach ( $nest_value->data as $integration_key => $integration_value ) {
									if ( is_object( $integration_value ) && ! empty( $integration_value ) ) {
										foreach ( $integration_value as $integration_sub_key => $integration_sub_value ) {
											if ( is_object( $integration_sub_value ) && ! empty( $integration_sub_value ) ) {
												foreach ( $integration_sub_value as $sub_sub_key => $sub_sub_value ) {
													if ( ! is_object( $sub_sub_value ) ) {
														$integration[ $sub_sub_key ] = reviewshake_sanitize( $sub_sub_key, $sub_sub_value );
													}
												}
											} else {
												$integration[ $integration_sub_key ] = reviewshake_sanitize( $integration_sub_key, $integration_sub_value );
											}
										}
									} else {
										$integration[ $integration_key ] = reviewshake_sanitize( $integration_key, $integration_value );
									}
								}
							}

							$prepared_item[ $source_id ][ $nest_key ] = 'source_url' !== $nest_key ? reviewshake_sanitize( $nest_key, $nest_value ) : $nest_value;
						}
					} else {
						$prepared_item[ $source_id ][ $key ] = 'source_url' !== $key ? reviewshake_sanitize( $key, $value ) : $value;
					}
				}

				$prepared_item[ $source_id ]['source_url']  = 'google' === $prepared_item[ $source_id ]['source_name'] ? reviewshake_sanitize( 'source_url_text', $prepared_item[ $source_id ]['source_url'] ) : reviewshake_sanitize( 'source_url', $prepared_item[ $source_id ]['source_url'] );
				$prepared_item[ $source_id ]['integration'] = $integration;
			}

			return $prepared_item;
		}

		/**
		 * Prepare the item for the REST response
		 *
		 * @param mixed           $review_source WordPress representation of the item.
		 * @param WP_REST_Request $request Request object.
		 *
		 * @since 1.0.0
		 *
		 * @return mixed
		 */
		public function prepare_item_for_response( $review_source, $request ) {
			$response = new WP_REST_Response( $review_source, 200 );
			return $response;
		}

		/**
		 * Get the query params for collections.
		 *
		 * @since   1.0.0
		 * @version 1.1.0
		 *
		 * @return array
		 */
		public function get_collection_params() {
			return array(
				'subdomain'     => array(
					'description'       => esc_html__( 'The Reviewshake account subdomain.', 'reviewshake-widgets' ),
					'type'              => 'string',
					'default'           => $this->account_domain,
					'validate_callback' => 'rest_validate_request_arg',
					'sanitize_callback' => 'rest_sanitize_request_arg',
				),
				'apikey'        => array(
					'description'       => esc_html__( 'The Reviewshake account API key.', 'reviewshake-widgets' ),
					'type'              => 'string',
					'default'           => $this->api_key,
					'validate_callback' => 'rest_validate_request_arg',
					'sanitize_callback' => 'rest_sanitize_request_arg',
				),
				'source'        => array(
					'description'       => esc_html__( 'The Reviewshake review source name.', 'reviewshake-widgets' ),
					'type'              => 'string',
					'validate_callback' => 'rest_validate_request_arg',
					'sanitize_callback' => 'rest_sanitize_request_arg',
				),
				'sourceUrl'     => array(
					'description'       => esc_html__( 'The Reviewshake review source URL.', 'reviewshake-widgets' ),
					'type'              => 'string',
					'validate_callback' => 'rest_validate_request_arg',
					'sanitize_callback' => 'rest_sanitize_request_arg',
				),
				'googlePlaceId' => array(
					'description'       => esc_html__( 'The Reviewshake google review source ID.', 'reviewshake-widgets' ),
					'type'              => 'string',
					'validate_callback' => 'rest_validate_request_arg',
					'sanitize_callback' => 'rest_sanitize_request_arg',
				),
			);
		}

		/**
		 * Retrieves the review source's schema, conforming to JSON Schema.
		 *
		 * @since   1.0.0
		 * @version 1.1.0
		 *
		 * @return array $schema
		 */
		public function get_item_schema() {
			$schema = array(
				'$schema'    => 'http://json-schema.org/draft-04/schema#',
				'title'      => 'reviewshake-review-sources',
				'type'       => 'object',
				'properties' => array(
					'id'            => array(
						'description' => esc_html__( 'Unique identifier for the review source.', 'reviewshake-widgets' ),
						'type'        => 'integer',
						'readonly'    => true,
					),
					'subdomain'     => array(
						'description' => esc_html__( 'The Reviewshake account domain.', 'reviewshake-widgets' ),
						'type'        => 'string',
						'context'     => array( 'view' ),
						'required'    => true,
						'arg_options' => array(
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
					'apikey'        => array(
						'description' => esc_html__( 'The Reviewshake account API key.', 'reviewshake-widgets' ),
						'type'        => 'string',
						'required'    => true,
						'context'     => array( 'view' ),
						'arg_options' => array(
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
					'source'        => array(
						'description' => esc_html__( 'The Reviewshake review source name.', 'reviewshake-widgets' ),
						'type'        => 'string',
						'context'     => array( 'edit' ),
						'arg_options' => array(
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
					'sourceUrl'     => array(
						'description' => esc_html__( 'The Reviewshake review source URL.', 'reviewshake-widgets' ),
						'type'        => 'string',
						'context'     => array( 'edit' ),
						'arg_options' => array(
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
					'googlePlaceId' => array(
						'description' => esc_html__( 'The Reviewshake google review source ID.', 'reviewshake-widgets' ),
						'type'        => 'string',
						'context'     => array( 'edit' ),
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
