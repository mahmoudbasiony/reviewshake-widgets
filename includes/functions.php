<?php
/**
 * Some helper core functions.
 *
 * @package Reviewshake_Widgets
 * @author  Reviewshake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get the subdomain from the site url.
 *
 * @param string $url The site url.
 *
 * @since 1.0.0
 *
 * @return string $subdomain.
 */
function reviewshake_get_subdomain_from_url( $url = null ) {

	if ( ! $url ) {
		$url = esc_url( get_site_url() );
	}

	$regex  = '^(?:(?P<scheme>\w+)://)?';
	$regex .= '(?:(?P<login>\w+):(?P<pass>\w+)@)?';
	$regex .= '(?P<host>(?:(?P<subdomain>[\w\.]+)\.)?(?P<domain>\w+)\.(?P<extension>\w+))';
	$regex .= '(?::(?P<port>\d+))?';
	$regex .= '(?P<path>[\w/]*/(?P<file>\w+(?:\.\w+)?)?)?';
	$regex .= '(?:\?(?P<arg>[\w=&]+))?';
	$regex .= '(?:#(?P<anchor>\w+))?';
	$regex  = "!$regex!";

	preg_match( $regex, $url, $out );

	// Extracting the desired subdomain.
	if ( ! empty( $out['subdomain'] ) ) {
		$subdomain = $out['subdomain'];
	} elseif ( ! empty( $out['domain'] ) ) {
		$subdomain = $out['domain'];
	} else {
		$parse     = parse_url( $url );
		$subdomain = $parse['host'];
	}

	if ( 'www' === $subdomain ) {
		$subdomain = reviewshake_get_subdomain_from_url( str_replace( 'www.', '', $url ) );
	}

	return $subdomain;
}

/**
 * Create Reviewshake new account.
 *
 * @param string $subdomain The subdomain of the account.
 *
 * @since 1.0.0
 *
 * @return object $response_body
 */
function reviewshake_create_new_account( $subdomain ) {
	// The HTTP headers.
	$headers = array(
		'Authorization' => base64_decode( REVIEWSHAKE_WIDGETS_GENERAL_API ),
		'Content-Type'  => 'application/json',
	);

	// The POST data.
	$fields = array(
		'account' => array(
			'subdomain' => $subdomain,
		),
	);

	$response = wp_remote_post(
		'https://app.reviewshake.com/api/v2/platform/free_account',
		array(
			'method'      => 'POST',
			'timeout'     => 0,
			'redirection' => 10,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => $headers,
			'body'        => json_encode( $fields ),
			'cookies'     => array(),
		)
	);

	$rs_code = wp_remote_retrieve_response_code( $response );

	$response_body = json_decode( wp_remote_retrieve_body( $response ) );

	if ( is_object( $response_body ) ) {
		$response_body->rscode = $rs_code;
	}

	return $response_body;
}

/**
 * Get Reviewshake account status.
 *
 * @param string $email    The account email address.
 * @param string $password The account password.
 *
 * @since 1.0.0
 *
 * @return object $response_body
 */
function reviewshake_get_account_status( $email, $password = 'cnMxMjM0NTY=' ) {
	// The HTTP headers.
	$headers = array(
		'Authorization' => base64_decode( REVIEWSHAKE_WIDGETS_GENERAL_API ),
		'Content-Type'  => 'application/json',
	);

	// The Get data parameters.
	$parameters = http_build_query(
		array(
			'account' => array(
				'email'    => $email,
				'password' => base64_decode( $password ),
			),
		)
	);

	$response = wp_remote_get(
		'https://app.reviewshake.com/api/v2/platform/account_status?' . $parameters,
		array(
			'method'      => 'GET',
			'timeout'     => 0,
			'redirection' => 10,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => $headers,
			'cookies'     => array(),
		)
	);

	$rs_code = wp_remote_retrieve_response_code( $response );

	$response_body = json_decode( wp_remote_retrieve_body( $response ) );

	if ( is_object( $response_body ) ) {
		$response_body->rscode = $rs_code;
	}

	return $response_body;
}

/**
 * Get Reviewshake account info.
 *
 * @param string $api_key        The api key.
 * @param string $account_domain The account subdomain.
 *
 * @since 1.0.0
 *
 * @return object $response_body
 */
function reviewshake_get_account_info( $api_key, $account_domain ) {
	// The HTTP headers.
	$headers = array(
		'Authorization' => $api_key,
		'Content-Type'  => 'application/json',
	);

	$response = wp_remote_get(
		"https://{$account_domain}/api/v2/organization",
		array(
			'method'      => 'GET',
			'timeout'     => 0,
			'redirection' => 10,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => $headers,
			'cookies'     => array(),
		)
	);

	$rs_code = wp_remote_retrieve_response_code( $response );

	$response_body = json_decode( wp_remote_retrieve_body( $response ) );

	if ( ! isset( $response_body ) || 200 !== $rs_code ) {
		$response_body = (object) array(
			'errors' => array(
				(object) array(
					'status' => $rs_code,
					'detail' => esc_html__( 'Invalid account domain!', 'reviewshake-widgets' ),
				),
			),
		);
	}

	if ( is_object( $response_body ) ) {
		$response_body->rscode         = $rs_code;
		$response_body->api_key        = $api_key;
		$response_body->account_domain = $account_domain;
	}

	return $response_body;
}

/**
 * Put free plan for newly created reviewshake account.
 *
 * @param string $api_key   The authentication token.
 * @param string $account_domain The account domain.
 *
 * @since 1.0.0
 *
 * @return object $response_body
 */
function reviewshake_turn_account_to_free_plan( $api_key, $account_domain ) {
	// The HTTP headers.
	$headers = array(
		'Authorization' => $api_key,
		'Content-Type'  => 'application/json',
	);

	$response = wp_remote_request(
		"https://{$account_domain}/api/v2/organization/free_plan",
		array(
			'method'      => 'PUT',
			'timeout'     => 0,
			'redirection' => 10,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => $headers,
			'body'        => json_encode( array() ),
			'cookies'     => array(),
		)
	);

	$rs_code = wp_remote_retrieve_response_code( $response );

	$response_body = json_decode( wp_remote_retrieve_body( $response ) );

	if ( is_object( $response_body ) ) {
		$response_body->rscode = $rs_code;
	}

	return $response_body;
}

/**
 * Lists the review sources of given account data.
 *
 * @param string $account_domain The account domain.
 * @param string $api_key        The API key.
 *
 * @since 1.0.0
 *
 * @return object $response_body
 */
function reviewshake_get_list_of_review_sources( $account_domain, $api_key ) {
	// The HTTP headers.
	$headers = array(
		'Authorization' => $api_key,
		'Content-Type'  => 'application/json',
	);

	$response = wp_remote_get(
		"https://{$account_domain}/api/v2/review_sources",
		array(
			'method'      => 'GET',
			'timeout'     => 0,
			'redirection' => 10,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => $headers,
			'cookies'     => array(),
		)
	);

	$rs_code = wp_remote_retrieve_response_code( $response );

	$response_body = json_decode( wp_remote_retrieve_body( $response ) );

	if ( is_object( $response_body ) ) {
		$response_body->rscode = $rs_code;
	}

	return $response_body;
}

/**
 * Add new review source to Reviewshake account.
 *
 * @param array $data The data.
 *
 * @since 1.0.0
 *
 * @return object $response_body
 */
function reviewshake_add_review_source( $data ) {
	// The HTTP headers.
	$headers = array(
		'Authorization' => $data['apikey'],
		'Content-Type'  => 'application/json',
	);

	// The POST data.
	$fields = array(
		'review_source' => array(
			'source_name' => $data['source'],
			'source_url'  => $data['sourceurl'],
		),
	);

	// Add google place ID if google is the source.
	if ( 'google' === $data['source'] ) {
		$fields['review_source']['source_id'] = $data['google_place_id'];
	}

	$response = wp_remote_post(
		'https://' . $data['subdomain'] . '/api/v2/review_sources',
		array(
			'method'      => 'POST',
			'timeout'     => 0,
			'redirection' => 10,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => $headers,
			'body'        => json_encode( $fields ),
			'cookies'     => array(),
		)
	);

	$rs_code = wp_remote_retrieve_response_code( $response );

	$response_body = json_decode( wp_remote_retrieve_body( $response ) );

	if ( is_object( $response_body ) ) {
		$response_body->rscode = $rs_code;
	}

	return $response_body;
}

/**
 * Delete review source.
 *
 * @param string $account_domain The account domain.
 * @param string $api_key        The API key.
 * @param string $source_id      The review source ID.
 *
 * @since 1.0.0
 *
 * @return object $response_body
 */
function reviewshake_delete_review_source( $account_domain, $api_key, $source_id ) {
	// The HTTP headers.
	$headers = array(
		'Authorization' => $api_key,
		'Content-Type'  => 'application/json',
	);

	$response = wp_remote_request(
		"https://{$account_domain}/api/v2/review_sources/{$source_id}",
		array(
			'method'      => 'DELETE',
			'timeout'     => 0,
			'redirection' => 10,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => $headers,
			'cookies'     => array(),
		)
	);

	$rs_code = wp_remote_retrieve_response_code( $response );

	$response_body = json_decode( wp_remote_retrieve_body( $response ) );

	if ( is_object( $response_body ) ) {
		$response_body->rscode = $rs_code;
	}

	return $response_body;
}

/**
 * Lists the widgets of given account data.
 *
 * @param string $account_domain The account bdomain.
 * @param string $api_key        The API key.
 *
 * @since 1.0.0
 *
 * @return object $response_body
 */
function reviewshake_get_list_of_widgets( $account_domain, $api_key ) {
	// The HTTP headers.
	$headers = array(
		'X-Spree-Token' => $api_key,
		'Content-Type'  => 'application/json',
	);

	$response = wp_remote_get(
		"https://{$account_domain}/api/v1/widgets",
		array(
			'method'      => 'GET',
			'timeout'     => 0,
			'redirection' => 10,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => $headers,
			'cookies'     => array(),
		)
	);

	$rs_code = wp_remote_retrieve_response_code( $response );

	$response_body = json_decode( wp_remote_retrieve_body( $response ) );

	if ( is_object( $response_body ) ) {
		$response_body->rscode = $rs_code;
	}

	return $response_body;
}

/**
 * Get widget info details from reviewshake account.
 *
 * @param int    $id             The widget ID.
 * @param string $account_domain The account subdomain.
 * @param string $api_key        The API key.
 *
 * @since 1.0.0
 *
 * @return object $response_body
 */
function reviewshake_get_widget( $id, $account_domain, $api_key ) {
	// The HTTP headers.
	$headers = array(
		'X-Spree-Token' => $api_key,
		'Content-Type'  => 'application/json',
	);

	$response = wp_remote_get(
		"https://{$account_domain}/api/v1/widgets/{$id}",
		array(
			'method'      => 'GET',
			'timeout'     => 0,
			'redirection' => 10,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => $headers,
			'cookies'     => array(),
		)
	);

	$rs_code = wp_remote_retrieve_response_code( $response );

	$response_body = json_decode( wp_remote_retrieve_body( $response ) );

	if ( is_object( $response_body ) ) {
		$response_body->rscode = $rs_code;
	}

	return $response_body;
}


/**
 * Create new widget to Reviewshake account.
 *
 * @param array  $data           The form data.
 * @param string $account_domain The account domain.
 * @param string $api_key        The API key.
 *
 * @since 1.0.0
 *
 * @return object $response_body
 */
function reviewshake_create_widget( $data, $account_domain, $api_key ) {
	// The HTTP headers.
	$headers = array(
		'X-Spree-Token' => $api_key,
		'Content-Type'  => 'application/json',
	);

	$response = wp_remote_post(
		"https://{$account_domain}/api/v1/widgets",
		array(
			'method'      => 'POST',
			'timeout'     => 0,
			'redirection' => 10,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => $headers,
			'body'        => json_encode( $data ),
			'cookies'     => array(),
		)
	);

	$rs_code = wp_remote_retrieve_response_code( $response );

	$response_body = json_decode( wp_remote_retrieve_body( $response ) );

	if ( is_object( $response_body ) ) {
		$response_body->rscode = $rs_code;
	}

	return $response_body;
}

/**
 * Update widget.
 *
 * @param array  $data           The form data.
 * @param string $account_domain The account subdomain.
 * @param string $api_key        The API key.
 *
 * @since 1.0.0
 *
 * @return object $response_body
 */
function reviewshake_update_widget( $data, $account_domain, $api_key ) {
	// Validate widget ID.
	if ( ! isset( $data['id'] ) || 0 >= $data['id'] ) {
		return new WP_Error(
			'reviewshake_rest_invalid_widget_id',
			esc_html__( 'Invalid widget ID!', 'reviewshake-widgets' ),
			array(
				'status' => 404,
				'detail' => __( 'The current widget is not found!', 'reviewshake-widgets' ),
			)
		);
	}

	// The HTTP headers.
	$headers = array(
		'X-Spree-Token' => $api_key,
		'Content-Type'  => 'application/json',
	);

	$response = wp_remote_request(
		"https://{$account_domain}/api/v1/widgets/{$data['id']}",
		array(
			'method'      => 'PUT',
			'timeout'     => 0,
			'redirection' => 10,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => $headers,
			'body'        => json_encode( $data ),
			'cookies'     => array(),
		)
	);

	$rs_code = wp_remote_retrieve_response_code( $response );

	$response_body = json_decode( wp_remote_retrieve_body( $response ) );

	if ( is_object( $response_body ) ) {
		$response_body->rscode = $rs_code;
	}

	return $response_body;
}

/**
 * Delete Widget.
 *
 * @param int    $id             The widget ID.
 * @param string $account_domain The account subdomain.
 * @param string $api_key        The API key.
 *
 * @since 1.0.0
 *
 * @return object $response_body
 */
function reviewshake_delete_widget( $id, $account_domain, $api_key ) {
	// The HTTP headers.
	$headers = array(
		'X-Spree-Token' => $api_key,
		'Content-Type'  => 'application/json',
	);

	$response = wp_remote_request(
		"https://{$account_domain}/api/v1/widgets/{$id}",
		array(
			'method'      => 'DELETE',
			'timeout'     => 0,
			'redirection' => 10,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => $headers,
			'cookies'     => array(),
		)
	);

	$rs_code = wp_remote_retrieve_response_code( $response );

	$response_body = json_decode( wp_remote_retrieve_body( $response ) );

	if ( is_object( $response_body ) ) {
		$response_body->rscode = $rs_code;
	}

	return $response_body;
}

/**
 * Removes the review source from database.
 *
 * @param int $source_id The source ID.
 *
 * @since 1.0.0
 *
 * @return bool|WP_Error
 */
function reviewshake_remove_review_source( $source_id ) {
	$settings = get_option( 'reviewshake_widgets_settings', array() );

	if ( $source_id ) {
		$prefix = 'rs';

		$source_id = (string) $prefix . $source_id;

		if ( ! isset( $settings['review_sources'][ $source_id ] ) ) {
			return new WP_Error(
				'reviewshake_review_source_not_found',
				__( 'Review source not found!', 'reviewshake-widgets' ),
				array(
					'status' => 404,
					'detail' => __(
						'This Review source is not found in the database!',
						'reviewshake-widgets'
					),
				)
			);
		}

		// Unset review source from sources array.
		unset( $settings['review_sources'][ $source_id ] );

		return update_option( 'reviewshake_widgets_settings', $settings );
	}
}

/**
 * Removes widget from database.
 *
 * @param int $widget_id The widget ID.
 *
 * @since 1.0.0
 *
 * @return bool|WP_Error
 */
function reviewshake_remove_widget( $widget_id ) {
	$settings = get_option( 'reviewshake_widgets_settings', array() );

	if ( $widget_id ) {
		$prefix = 'widget';

		$widget_id = (string) $prefix . $widget_id;

		if ( ! isset( $settings['widgets'][ $widget_id ] ) ) {
			return new WP_Error(
				'reviewshake_invalid_review_widget_id',
				__( 'Invalid Widget ID', 'reviewshake-widgets' ),
				array(
					'status' => 404,
					'detail' => __(
						'This Widget is not found on the database.',
						'reviewshake-widgets'
					),
				)
			);
		}

		// Unset review source from sources array.
		unset( $settings['widgets'][ $widget_id ] );

		return update_option( 'reviewshake_widgets_settings', $settings );
	}
}

/**
 * Save data to reviewshake settings.
 *
 * @param string $settings_key The settings key to save data into.
 * @param array  $data         The data array.
 * @param bool   $override     Whether to override exist settings - Default: false.
 *
 * @since 1.0.0
 *
 * @return bool True if successful
 */
function reviewshake_save_settings( string $settings_key, $data, $override = false ) {
	$settings = get_option( 'reviewshake_widgets_settings', array() );

	if ( ! empty( $settings_key ) ) {
		switch ( $settings_key ) {
			case 'review_sources':
			case 'widgets':
				if ( empty( $data ) || $override ) {
					$settings[ $settings_key ] = array();
				}

				foreach ( $data as $key => $value ) {
					$settings[ $settings_key ][ $key ] = $value; // The value data is sanitized.
				}
				break;

			case 'account':
			case 'state':
				foreach ( $data as $key => $value ) {
					$settings[ $settings_key ][ $key ] = reviewshake_sanitize( $key, $value );
				}
				break;
			default:
				return false;
		}

		return update_option( 'reviewshake_widgets_settings', $settings );
	}

	return false;
}

/**
 * Sanitize fields to be saved to the database.
 *
 * @param string $key   The field key.
 * @param string $value The field value to be sanitized.
 *
 * @since 1.0.0
 *
 * @return string The sanitized value.
 */
function reviewshake_sanitize( $key, $value ) {
	switch ( $key ) {
		case 'email':
			return sanitize_email( $value );

		case 'account_domain':
		case 'source_url_text':
		case 'google_place_id':
			return sanitize_text_field( $value );

		case 'source_url':
		case 'embed':
			return esc_url( $value );

		case 'id':
		case 'location_id':
		case 'company_id':
		case 'client_id':
		case 'subuser_id':
		case 'display_mode':
		case 'max_reviews':
		case 'width':
		case 'height':
		case 'title_font_size':
		case 'body_font_size':
		case 'organization_id':
		case 'title_font_weight':
		case 'request_no':
		case 'sec_to_sleep':
			return absint( $value );

		case 'ex_reviews_source':
			$sanitized_value = array();
			foreach ( $value as $index => $single ) {
				$sanitized_value[ $index ] = sanitize_text_field( $single );
			}
			return $sanitized_value;

		case 'ex_star_rating':
			$sanitized_value = array();
			if ( ! empty( $value ) && is_array( $value ) ) {
				foreach ( $value as $index => $single ) {
					$sanitized_value[ $index ] = absint( $single );
				}
			}

			return (array) $sanitized_value;

		case 'use_iframe':
		case 'summary_header':
		case 'rich_snippet':
		case 'ex_empty_reviews':
		case 'request_button':
			return rest_sanitize_boolean( $value );

		case 'display_elements':
			return wp_parse_id_list( $value );

		case 'rich_snippet_meta_data':
			$sanitized_value = (object) array();
			if ( is_object( $value ) && ! empty( $value ) ) {
				foreach ( $value as $key2 => $value2 ) {
					$key2 = sanitize_text_field( $key2 );
					if ( is_object( $value2 ) && ! empty( $value2 ) ) {
						$sanitized_value->$key2 = new stdClass();
						foreach ( $value2 as $key3 => $value3 ) {
							$key3                          = sanitize_text_field( $key3 );
							$sanitized_value->$key2->$key3 = sanitize_text_field( $value3 );
						}
					}

					$sanitized_value->$key2 = sanitize_text_field( $value2 );
				}
			}
			return $sanitized_value;

		default:
			return sanitize_text_field( $value );
	}
}

/**
 * Check settings exist and valid.
 *
 * @param array  $settings      The settings array.
 * @param string $settings_type The type of settings ['account', 'review_sources', 'widgets'].
 * @param string $key           The setting key.
 *
 * @since 1.0.0
 *
 * @return boolean Whether the setting exists or not.
 */
function reviewshake_check_settings( $settings = null, $settings_type, $key ) {
	if ( ! $settings ) {
		$settings = get_option( 'reviewshake_widgets_settings', array() );
	}

	if ( isset( $settings[ $settings_type ] ) && isset( $settings[ $settings_type ][ $key ] ) && ! empty( $settings[ $settings_type ][ $key ] ) ) {
		return $settings[ $settings_type ][ $key ];
	}

	return false;
}

/**
 * Get account creation state.
 *
 * @since 1.0.0
 *
 * @return array $state
 */
function reviewshake_get_state() {
	// Declare state array.
	$state = array();

	// Define supported keys.
	$keys = array(
		'tab',
		'account_status',
		'source_status',
		'connection_type',
		'request_type',
		'request_no',
		'started_at',
		'source_name',
		'source_url',
		'google_place_id',
	);

	// Get general settings.
	$settings = get_option( 'reviewshake_widgets_settings', array() );

	// Current tab.
	$current_tab = isset( $_GET ) && isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'setup';

	foreach ( $keys as $key ) {
		$value         = reviewshake_check_settings( $settings, 'state', $key );
		$state[ $key ] = $value ? reviewshake_sanitize( $key, $value ) : false;

		if ( 'tab' === $key ) {
			$state[ $key ] = $current_tab;
		}

		if ( 'started_at' === $key && $value ) {
			$sec_to_sleep = 0;

			$started_at = new DateTime( reviewshake_sanitize( $key, $value ) );
			$now        = new DateTime( gmdate( 'Y-m-d H:i:s' ) );
			$diff       = $now->getTimestamp() - $started_at->getTimestamp();

			if ( 20 >= $diff ) {
				$sec_to_sleep = 20 - $diff;
			}

			$state['sec_to_sleep'] = reviewshake_sanitize( 'sec_to_sleep', $sec_to_sleep );
		}
	}

	return (array) $state;
}

/**
 * Validate if account exists in database.
 *
 * @since 1.0.0
 *
 * @return bool Whether the account exists or not.
 */
function reviewshake_is_account_exist_in_db() {
	$settings = get_option( 'reviewshake_widgets_settings', array() );

	$attributes = array(
		'account_domain',
		'api_key',
	);

	foreach ( $attributes as $attribute ) {
		if ( ! reviewshake_check_settings( $settings, 'account', $attribute ) ) {
			return false;
		}
	}

	return true;
}

/**
 * Checks if pricing plan on a valid trial.
 *
 * @since 1.0.0
 *
 * @return bool Whether the trial plan valid or not.
 */
function reviewshake_is_on_trial() {
	$current_plan = reviewshake_get_current_pricing_plan();

	if ( is_object( $current_plan ) && isset( $current_plan->pricing_plan ) && isset( $current_plan->claimed_at ) ) {
		$pricing_plan = $current_plan->pricing_plan;
		$claimed_at   = new DateTime( $current_plan->claimed_at );
		$data         = new DateTime( gmdate( 'Y-m-d' ) );

		$interval = $claimed_at->diff( $data )->d;

		if ( 'trial' === $pricing_plan && 14 >= $interval ) {
			return true;
		}
	}

	return false;
}

/**
 * Get the current pricing plan.
 *
 * @since 1.0.0
 *
 * @return object $current_plan
 */
function reviewshake_get_current_pricing_plan() {
	$settings = get_option( 'reviewshake_widgets_settings', array() );

	$attributes = array( 'pricing_plan', 'claimed_at' );

	$current_plan = new stdClass();
	foreach ( $attributes as $attribute ) {
		$current_plan->$attribute = reviewshake_check_settings( $settings, 'account', $attribute );
	}

	return $current_plan;
}

/**
 * Get the review sources limit for current saved plan.
 *
 * @param object $current_plan The current plan object.
 *
 * @since 1.0.0
 *
 * @return int $limit The reveiw sources limit
 */
function reviewshake_get_review_sources_limit( $current_plan ) {
	$limit = 0;

	// @TODO: Set the unlimited to 100 for now.
	$unlimited = 100;

	if ( is_object( $current_plan ) ) {
		switch ( $current_plan->pricing_plan ) {
			case 'trial':
				$limit = reviewshake_is_on_trial() ? $unlimited : 2;
				break;

			case 'free':
				$limit = 2;
				break;

			case 'small':
				$limit = 5;
				break;

			default:
				$limit = $unlimited;
				break;
		}
	}

	return $limit;
}

/**
 * Check if account is claimed.
 *
 * @param object $current_plan The current plan.
 *
 * @since 1.0.0
 *
 * @return bool Whether account is claimed or not
 */
function reviewshake_is_account_claimed( $current_plan ) {
	// @TODO: not claimed is equal to null.
	if ( isset( $current_plan->claimed_at ) && ! empty( $current_plan->claimed_at ) && null != $current_plan->claimed_at ) {
		return true;
	}

	return false;
}

/**
 * Renders add review source form.
 *
 * @since 1.0.0
 *
 * @return mixed
 */
function reviewshake_render_review_source_form() {
	ob_start();
		include REVIEWSHAKE_WIDGETS_TEMPLATES_PATH . 'admin/views/add-review-source.php';
	return ob_get_clean();
}

/**
 * Renders review sources.
 *
 * @since 1.0.0
 *
 * @return mixed
 */
function reviewshake_render_review_sources() {
	ob_start();
		include REVIEWSHAKE_WIDGETS_TEMPLATES_PATH . 'admin/views/review-sources.php';
	return ob_get_clean();
}

/**
 * Renders the setup tab content.
 *
 * @since 1.0.0
 *
 * @return mixed HTML
 */
function reviewshake_renders_setup_tab_content() {
	ob_start();
		include REVIEWSHAKE_WIDGETS_TEMPLATES_PATH . 'admin/settings-setup.php';
	return ob_get_clean();
}

/**
 * Renders the account tab content.
 *
 * @since 1.0.0
 *
 * @return mixed HTML
 */
function reviewshake_renders_account_tab_content() {
	ob_start();
		include REVIEWSHAKE_WIDGETS_TEMPLATES_PATH . 'admin/settings-account.php';
	return ob_get_clean();
}

/**
 * Renders create/edit widget form.
 *
 * @param int $widget_id The widget ID - Default: 0.
 *
 * @since 1.0.0
 *
 * @return mixed HTML
 */
function reviewshake_render_widget_form( $widget_id = 0 ) {
	ob_start();
		echo '<div class="reviewshake-widgets-setup-wrap">';
			include REVIEWSHAKE_WIDGETS_TEMPLATES_PATH . 'admin/views/create-edit-widget.php';
		echo '</div';
	return ob_get_clean();
}

/**
 * Renders the connect account form.
 *
 * @since 1.0.0
 *
 * @return mixed HTML
 */
function reviewshake_render_connect_account_form() {
	ob_start();
		echo '<div class="reviewshake-widgets-account-wrap">';
			include REVIEWSHAKE_WIDGETS_TEMPLATES_PATH . 'admin/views/connect-account-form.php';
		echo '</div';
	return ob_get_clean();
}

/**
 * Get the cuurent account info.
 *
 * @since 1.0.0
 *
 * @return void|bool
 */
function reviewshake_rest_get_account_info() {
	$settings       = get_option( 'reviewshake_widgets_settings', array() );
	$api_key        = reviewshake_check_settings( $settings, 'account', 'api_key' );
	$account_domain = reviewshake_check_settings( $settings, 'account', 'account_domain' );

	// Validates api key and account domain.
	if ( ! $api_key || ! $account_domain ) {
		return;
	}

	// Get account info by sending a request to Reviewshake API.
	$account = reviewshake_get_account_info( $api_key, $account_domain );

	$data = array();

	if ( isset( $account ) && isset( $account->data ) ) {
		if ( isset( $account->data->id ) ) {
			$data['id'] = $account->data->id;
		}

		if ( isset( $account->data->type ) ) {
			$data['account_type'] = $account->data->type;
		}

		if ( isset( $account->data->attributes ) && is_object( $account->data->attributes ) ) {
			foreach ( $account->data->attributes as $key => $value ) {
				$data[ $key ] = $value;
			}
		}

		if ( isset( $account->data->links ) && is_object( $account->data->links ) ) {
			foreach ( $account->data->links as $key => $value ) {
				$data[ $key ] = $value;
			}
		}

		if ( isset( $account->account_domain ) && ! empty( $account->account_domain ) ) {
			$data['account_domain'] = $account->account_domain;
		}

		if ( isset( $account->api_key ) && ! empty( $account->api_key ) ) {
			$data['api_key'] = $account->api_key;
		}

		// Save the account info.
		$save_account = reviewshake_save_settings( 'account', $data );

		return $save_account;
	}
}

/**
 * List the review sources linked to the connected account.
 *
 * @since 1.0.0
 *
 * @return object $data The review resources object
 */
function reviewshake_rest_list_review_sources() {
	$request  = new WP_REST_Request( 'GET', '/reviewshake/v1/review_sources' );
	$response = rest_do_request( $request );
	$server   = rest_get_server();
	$data     = $server->response_to_data( $response, false );

	return $data;
}

/**
 * List the widgets linked to the connected account.
 *
 * @since 1.0.0
 *
 * @return object $data The widgets response object
 */
function reviewshake_rest_list_widgets() {
	$request  = new WP_REST_Request( 'GET', '/reviewshake/v1/widgets' );
	$response = rest_do_request( $request );
	$server   = rest_get_server();
	$data     = $server->response_to_data( $response, false );

	return $data;
}

/**
 * Get error detail messages as a string message from the errors object.
 *
 * @param object $errors The errors object.
 *
 * @since 1.0.0
 *
 * @return string The error detail message
 */
function reviewshake_get_error_messages( $errors ) {
	$message = '';

	if ( is_object( $errors ) || is_array( $errors ) ) {
		foreach ( $errors as $key => $value ) {
			$message .= ucfirst( preg_replace( '/[^A-Za-z0-9\-]/', ' ', $key ) );

			if ( is_array( $value ) && ! empty( $value ) ) {
				foreach ( $value as $nest_value ) {
					$message .= ' ' . $nest_value;
				}
			}
		}
	}

	return $message;
}
