<?php
/**
 * REST API: Reviewshake_Widgets_REST_Account_Controller class.
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
	 * Core class used to manage reviewshake account via the REST API.
	 *
	 * @since 1.0.0
	 *
	 * @see WP_REST_Controller
	 */
	class Reviewshake_Widgets_REST_Account_Controller extends WP_REST_Controller {

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
		 * The account email.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $email;

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
			$this->rest_base = 'account';

			$this->settings       = get_option( 'reviewshake_widgets_settings', array() );
			$this->email          = reviewshake_check_settings( $this->settings, 'account', 'email' );
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
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( $this, 'create_item' ),
						'permission_callback' => array( $this, 'create_item_permissions_check' ),
						'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
					),

					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_item' ),
						'permission_callback' => array( $this, 'get_item_permissions_check' ),
						'args'                => $this->get_collection_params(),
					),
				)
			);

			register_rest_route(
				$this->namespace,
				'/' . $this->rest_base . '/(?P<apikey>[^/]+)/(?P<subdomain>[^/]+)',
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_current_item' ),
						'permission_callback' => array( $this, 'get_current_item_permissions_check' ),
						'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::READABLE ),
					),

					array(
						'methods'             => WP_REST_Server::EDITABLE,
						'callback'            => array( $this, 'update_item' ),
						'permission_callback' => array( $this, 'update_item_permissions_check' ),
						'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
					),
				)
			);
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
			/**
			 * Validates account exists.
			 */
			if ( $this->email && $this->account_domain ) {
				$account                              = new stdClass();
				$account->data->attributes->email     = $this->email;
				$account->data->links->account_domain = $this->account_domain;

				$response = new WP_REST_Response( $account, 200 );

				return rest_ensure_response( $response );
			}

			// Initialize state.
			$state = array(
				'source_url'      => 'google' !== $source_name ? reviewshake_sanitize( 'source_url', $request->get_param( 'sourceUrl' ) ) : reviewshake_sanitize( 'source_url_text', $request->get_param( 'sourceUrl' ) ),
				'source_name'     => reviewshake_sanitize( 'source', $request->get_param( 'source' ) ),
				'google_place_id' => reviewshake_sanitize( 'google_place_id', $request->get_param( 'googlePlaceId' ) ),
				'account_status'  => 'pending',
				'source_status'   => 'pending',
			);

			// Set state.
			$set_state = reviewshake_save_settings( 'state', $state );

			/**
			 * Create new reviewshake account.
			 */
			if ( function_exists( 'reviewshake_create_new_account' ) ) {

				// Extracts the subdomain from the site url.
				$subdomain = reviewshake_get_subdomain_from_url();

				// Create a new reviewshake account.
				$account = reviewshake_create_new_account( $subdomain );

				// Validate errors.
				if ( isset( $account->errors ) && is_array( $account->errors ) ) {
					$status  = isset( $account->errors[0]->status ) ? $account->errors[0]->status : 401;
					$message = isset( $account->errors[0]->detail ) ? $account->errors[0]->detail : esc_html__( 'Something went wrong!', 'reviewshake-widgets' );

					return new WP_Error(
						'reviewshake_error_on_create_account',
						esc_html__( 'Create Account', 'reviewshake-widgets' ),
						array(
							'status' => $status,
							'detail' => $message,
						)
					);
				}

				$prepared_account = $this->prepare_account_for_database( $account );

				/**
				 * Filters an account before it is inserted via the REST API.
				 *
				 * Allows modification of the account right before it is inserted to db.
				 * Returning a WP_Error value from the filter will short-circuit insertion and allow
				 * skipping further processing.
				 *
				 * @since 1.0.0
				 *
				 * @param array|WP_Error  $prepared_account The prepared account data for insertion.
				 * @param WP_REST_Request $request          Request used to insert the comment.
				 */
				$prepared_account = apply_filters( 'rest_pre_insert_reviewshake_account', $prepared_account, $request );

				if ( is_wp_error( $prepared_account ) ) {
					return $prepared_account;
				}

				$save_account = reviewshake_save_settings( 'account', $prepared_account );

				if ( ! $save_account ) {
					return new WP_Error(
						'reviewshake_error_on_save_account_to_db',
						esc_html__( 'Save Account', 'reviewshake-widgets' ),
						array(
							'status' => 401,
							'detail' => esc_html__(
								'Something went wrong during saving account to database!',
								'reviewshake-widgets'
							),
						)
					);
				}

				$state = array(
					'request_type'    => 'create_account',
					'started_at'      => gmdate( 'Y-m-d H:i:s' ),
					'request_no'      => 1,
					'connection_type' => 'setup',
				);

				// Set state.
				$set_state = reviewshake_save_settings( 'state', $state );

				$response = $this->prepare_item_for_response( $account, $request, 'setup_tab' );

				return rest_ensure_response( $response );
			}
		}

		/**
		 * Check if a given request has access to get a specific item
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|bool
		 */
		public function get_item_permissions_check( $request ) {
			return current_user_can( 'manage_options' );
		}

		/**
		 * Get one item from the collection
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 *
		 * @return WP_Error|WP_REST_Response
		 */
		public function get_item( $request ) {
			/**
			 * Validate if newly created account exists.
			 */
			if ( $this->api_key ) {

				$account                                   = new stdClass();
				$account->data->attributes->api_key        = $this->api_key;
				$account->data->attributes->account_domain = $this->account_domain;

				$response = new WP_REST_Response( $account, 200 );

				return rest_ensure_response( $response );
			}

			// Get request params.
			$email    = (string) $request->get_param( 'email' );
			$password = (string) $request->get_param( 'password' );

			if ( function_exists( 'reviewshake_get_account_status' ) ) {
				$account = reviewshake_get_account_status( $email, $password );

				// Validate errors.
				if ( isset( $result->errors ) && is_array( $result->errors ) && isset( $result->errors[0] ) ) {
					// Sends still creating account error.
					if ( 422 === $result->errors[0]->status && 'Account is being created.' === $result->errors[0]->detail ) {
						return new WP_Error(
							'reviewshake_error_account_being_created',
							esc_html__( 'Get Account Status', 'reviewshake-widgets' ),
							array(
								'status' => 422,
								'detail' => $result->errors[0]->detail,
							)
						);
					}

					$status  = isset( $account->errors[0]->status ) ? $account->errors[0]->status : 404;
					$message = isset( $account->errors[0]->detail ) ? $account->errors[0]->detail : esc_html__( 'Something went wrong!', 'reviewshake-widgets' );

					return new WP_Error(
						'reviewshake_error_on_get_account',
						esc_html__( 'Get Account Status', 'reviewshake-widgets' ),
						array(
							'status' => $status,
							'detail' => $message,
						)
					);
				}

				$prepared_account = $this->prepare_account_for_database( $account );

				/**
				 * Filters an account before it is inserted via the REST API.
				 *
				 * Allows modification of the account right before it is inserted to db.
				 * Returning a WP_Error value from the filter will short-circuit insertion and allow
				 * skipping further processing.
				 *
				 * @since 1.0.0
				 *
				 * @param array|WP_Error  $prepared_account The prepared account data for insertion.
				 * @param WP_REST_Request $request          Request used to insert the comment.
				 */
				$prepared_account = apply_filters( 'rest_pre_insert_reviewshake_account', $prepared_account, $request );

				if ( is_wp_error( $prepared_account ) ) {
					return $prepared_account;
				}

				$save_account = reviewshake_save_settings( 'account', $prepared_account );

				$state = array(
					'account_status'  => 'on_hold',
					'request_no'      => 2,
					'request_type'    => 'get_account_status',
					'connection_type' => 'setup',
				);

				// Set state.
				$set_state = reviewshake_save_settings( 'state', $state );

				$response = $this->prepare_item_for_response( $account, $request, 'setup_tab' );

				return rest_ensure_response( $response );
			}
		}

		/**
		 * Check if a given request has access to get a specific item
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|bool
		 */
		public function get_current_item_permissions_check( $request ) {
			return current_user_can( 'manage_options' );
		}

		/**
		 * Get account info
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 *
		 * @return WP_Error|WP_REST_Response
		 */
		public function get_current_item( $request ) {
			$subdomain      = str_replace( '.reviewshake.com', '', $request->get_param( 'subdomain' ) );
			$api_key        = $request->get_param( 'apikey' ) ? (string) $request->get_param( 'apikey' ) : $this->api_key;
			$account_domain = $subdomain ? (string) $subdomain . '.reviewshake.com' : $this->account_domain;

			if ( ! $account_domain || ! is_string( $account_domain ) ) {
				return new WP_Error(
					'reviewshake_rest_invalid_subdomain',
					esc_html__( 'Account Status', 'reviewshake-widgets' ),
					array(
						'status' => 404,
						'detail' => esc_html__( 'Invalid account domain', 'reviewshake-widgets' ),
					)
				);
			}

			if ( ! $api_key || ! is_string( $api_key ) ) {
				return new WP_Error(
					'reviewshake_rest_invalid_api_key',
					esc_html__( 'Account Status', 'reviewshake-widgets' ),
					array(
						'status' => 404,
						'detail' => esc_html__( 'Invalid API key', 'reviewshake-widgets' ),
					)
				);
			}

			if ( function_exists( 'reviewshake_get_account_info' ) ) {
				$account = reviewshake_get_account_info( $api_key, $account_domain );

				// Validate errors.
				if ( isset( $account->errors ) && is_array( $account->errors ) ) {
					$status  = isset( $account->errors[0]->status ) ? $account->errors[0]->status : 401;
					$message = isset( $account->errors[0]->detail ) ? $account->errors[0]->detail : esc_html__( 'Something went wrong!', 'reviewshake-widgets' );

					return new WP_Error(
						'reviewshake_error_on_get_account_info',
						esc_html__( 'Account Info', 'reviewshake-widgets' ),
						array(
							'status' => $status,
							'detail' => $message,
						)
					);
				}

				$prepared_account = $this->prepare_account_for_database( $account );

				/**
				 * Filters an account before it is inserted via the REST API.
				 *
				 * Allows modification of the account right before it is inserted to db.
				 * Returning a WP_Error value from the filter will short-circuit insertion and allow
				 * skipping further processing.
				 *
				 * @since 1.0.0
				 *
				 * @param array|WP_Error  $prepared_account The prepared account data for insertion.
				 * @param WP_REST_Request $request          Request used to insert the comment.
				 */
				$prepared_account = apply_filters( 'rest_pre_insert_reviewshake_account', $prepared_account, $request );

				if ( is_wp_error( $prepared_account ) ) {
					return $prepared_account;
				}

				$save_account = reviewshake_save_settings( 'account', $prepared_account );

				$state = array(
					'account_status'  => 'completed',
					'request_type'    => 'get_account_info',
					'request_no'      => 6,
					'connection_type' => 'account',
				);

				// Set state.
				$set_state = reviewshake_save_settings( 'state', $state );

				$response = $this->prepare_item_for_response( $account, $request, 'account_tab' );

				return rest_ensure_response( $response );
			}
		}

		/**
		 * Check if a given request has access to update a specific item
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|bool
		 */
		public function update_item_permissions_check( $request ) {
			return current_user_can( 'manage_options' );
		}

		/**
		 * Update one item from the collection
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|WP_REST_Response
		 */
		public function update_item( $request ) {
			/**
			 * Validate if newly created account exists.
			 */
			if ( function_exists( 'reviewshake_check_settings' ) ) {
				$pricing_plan = reviewshake_check_settings( $this->settings, 'account', 'pricing_plan' );
				$claimed_at   = reviewshake_check_settings( $this->settings, 'account', 'claimed_at' );

				if ( $pricing_plan && 'trial' !== $pricing_plan ) {
					$state = array(
						'account_status'  => 'completed',
						'request_type'    => 'add_free_plan',
						'request_no'      => 3,
						'connection_type' => 'setup',
					);

					// Set state.
					$set_state = reviewshake_save_settings( 'state', $state );

					$response = new WP_REST_Response( true, 200 );
					return rest_ensure_response( $response );
				}
			}

			$api_key        = $request->get_param( 'apikey' ) ? (string) $request->get_param( 'apikey' ) : $this->api_key;
			$account_domain = $request->get_param( 'subdomain' ) ? (string) $request->get_param( 'subdomain' ) : $this->account_domain;

			if ( function_exists( 'reviewshake_turn_account_to_free_plan' ) ) {
				$account = reviewshake_turn_account_to_free_plan( $api_key, $account_domain );

				// Validate errors.
				if ( isset( $account->errors ) && is_array( $account->errors ) ) {
					$status  = isset( $account->errors[0]->status ) ? $account->errors[0]->status : 401;
					$message = isset( $account->errors[0]->detail ) ? $account->errors[0]->detail : esc_html__( 'Something went wrong!', 'reviewshake-widgets' );

					return new WP_Error(
						'reviewshake_error_on_put_free_plan',
						esc_html__( 'Free Plan', 'reviewshake-widgets' ),
						array(
							'status' => $status,
							'detail' => $message,
						)
					);
				}

				$prepared_account = $this->prepare_account_for_database( $account );

				/**
				 * Filters an account before it is inserted via the REST API.
				 *
				 * Allows modification of the account right before it is inserted to db.
				 * Returning a WP_Error value from the filter will short-circuit insertion and allow
				 * skipping further processing.
				 *
				 * @since 1.0.0
				 *
				 * @param array|WP_Error  $prepared_account The prepared account data for insertion.
				 * @param WP_REST_Request $request          Request used to insert the comment.
				 */
				$prepared_account = apply_filters( 'rest_pre_insert_reviewshake_account', $prepared_account, $request );

				if ( is_wp_error( $prepared_account ) ) {
					return $prepared_account;
				}

				$save_account = reviewshake_save_settings( 'account', $prepared_account );

				$state = array(
					'account_status'  => 'completed',
					'request_type'    => 'add_free_plan',
					'request_no'      => 3,
					'connection_type' => 'setup',
				);

				// Set state.
				$set_state = reviewshake_save_settings( 'state', $state );

				$response = $this->prepare_item_for_response( $account, $request, 'setup_tab' );

				return rest_ensure_response( $response );
			}
		}

		/**
		 * Prepare the account for create or update operation
		 *
		 * @param object $account The reviewshake account.
		 *
		 * @return WP_Error|array $prepared_item
		 */
		protected function prepare_account_for_database( $account ) {
			$prepared_item = array();

			$properties = array(
				'email',
				'account_domain',
				'api_key',
			);

			if ( is_object( $account ) && isset( $account->data ) && ! empty( $account->data ) ) {
				foreach ( $account->data as $key => $value ) {
					if ( is_object( $value ) && ! empty( $value ) ) {
						foreach ( $value as $nest_key => $nest_value ) {
							if ( in_array( $nest_key, $properties, true ) ) {
								$this->$nest_key = reviewshake_sanitize( $nest_key, $nest_value );
							}
							$prepared_item[ $nest_key ] = reviewshake_sanitize( $nest_key, $nest_value );
						}
					} else {
						if ( in_array( $key, $properties, true ) ) {
							$this->$key = reviewshake_sanitize( $key, $value );
						}
						$prepared_item[ $key ] = reviewshake_sanitize( $key, $value );
					}
				}

				foreach ( $properties as $property ) {
					if ( isset( $account->$property ) ) {
						$prepared_item[ $property ] = reviewshake_sanitize( $property, $account->$property );
					}
				}
			}

			return $prepared_item;
		}

		/**
		 * Prepare the item for the REST response
		 *
		 * @param mixed           $account WordPress representation of the item.
		 * @param WP_REST_Request $request Request object.
		 * @param string          $tab the rendered HTML Tab -- Default: 'setup_tab'.
		 * @return mixed
		 */
		public function prepare_item_for_response( $account, $request, $tab = 'setup_tab' ) {

			if ( function_exists( "reviewshake_renders_{$tab}_content" ) ) {
				if ( 'setup_tab' === $tab ) {
					$account->html = reviewshake_renders_setup_tab_content();
				} elseif ( 'account_tab' === $tab ) {
					$account->html = reviewshake_renders_account_tab_content();
				}
			}

			return new WP_REST_Response( $account, 200 );
		}

		/**
		 * Get the query params for collections
		 *
		 * @return array
		 */
		public function get_collection_params() {
			return array(
				'email'     => array(
					'description'       => esc_html__( 'Email address for the object account.', 'reviewshake-widgets' ),
					'type'              => 'string',
					'format'            => 'email',
					'validate_callback' => 'rest_validate_request_arg',
				),
				'password'  => array(
					'description'       => esc_html__( 'The primary password for the object account. Only available after creating the account.', 'reviewshake-widgets' ),
					'type'              => 'string',
					'validate_callback' => 'rest_validate_request_arg',
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => 'cnMxMjM0NTY=',
				),
				'subdomain' => array(
					'description'       => esc_html__( 'The Reviewshake account subdomain.', 'reviewshake-widgets' ),
					'type'              => 'string',
					'validate_callback' => 'rest_validate_request_arg',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'apikey'    => array(
					'description'       => esc_html__( 'The Reviewshake account API key.', 'reviewshake-widgets' ),
					'type'              => 'string',
					'validate_callback' => 'rest_validate_request_arg',
					'sanitize_callback' => 'sanitize_text_field',
				),
			);
		}

		/**
		 * Retrieves the account's schema, conforming to JSON Schema.
		 *
		 * @since 1.0.0
		 *
		 * @return array $schema
		 */
		public function get_item_schema() {
			$schema = array(
				'$schema'    => 'http://json-schema.org/draft-04/schema#',
				'title'      => 'reviewshake-account',
				'type'       => 'object',
				'properties' => array(
					'email'     => array(
						'description' => esc_html__( 'Email address for the object account.', 'reviewshake-widgets' ),
						'type'        => 'string',
						'format'      => 'email',
						'context'     => array( 'view' ),
					),
					'password'  => array(
						'description' => esc_html__( 'The primary password for the object account. Only available after creating the account.', 'reviewshake-widgets' ),
						'type'        => 'string',
						'context'     => array( 'view' ),
						'readonly'    => true,
					),
					'subdomain' => array(
						'description' => esc_html__( 'The Reviewshake account subdomain.', 'reviewshake-widgets' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
					),
					'apikey'    => array(
						'description' => esc_html__( 'The Reviewshake account API key.', 'reviewshake-widgets' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
					),
				),
			);

			return $schema;
		}

	}

endif;
