<?php
/**
 * Some helper core functions.
 *
 * @package WPBLC_Broken_Links_Checker
 * @author
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( version_compare( PHP_VERSION, 8.0, '<=' ) && !function_exists( 'str_starts_with' ) ) {
    function str_starts_with ( $haystack, $needle ) {
        return strpos( $haystack , $needle ) === 0;
    }
}
if ( version_compare( PHP_VERSION, 8.0, '<=' ) && !function_exists( 'str_ends_with' ) ) {
    function str_ends_with( $haystack, $needle ) {
        return $needle !== '' && substr( $haystack, -strlen( $needle ) ) === (string)$needle;
    }
} 
if ( version_compare( PHP_VERSION, 8.0, '<=' ) && !function_exists( 'str_contains' ) ) {
    function str_contains( $haystack, $needle ) {
        return $needle !== '' && mb_strpos( $haystack, $needle ) !== false;
    }
}

if ( ! function_exists( 'wpblc_get_option' ) ) {
    /**
     * Get an option from the plugin settings.
     *
     * @param string $option_name - The option name.
     * @param mixed  $default     - The default value.
     *
     * @since 1.0.0
     *
     * @return mixed
     */
    function wpblc_get_option( $option_name, $default = false ) {
        $settings = get_option( 'wpblc_broken_links_checker_settings', array() );

        if ( isset( $settings[ $option_name ] ) ) {
            return $settings[ $option_name ];
        }

        return $default;
    }
}

if ( ! function_exists( 'wpblc_get_post_or_comment_title' ) ) {

    /**
     * Get the post or comment title.
     *
     * @param array $item - The item.
     *
     * @since 1.0.0
     *
     * @return string
     */
    function wpblc_get_post_or_comment_title( $item ) {
        if ( ! isset( $item['ID'] ) ) {
            return;
        }

        $id = $item['ID'];

        if ( isset($item['is_comment']) && $item['is_comment'] ) {
            return __( 'Author: ', 'wpblc-broken-links-checker' ) . get_comment_author( $id );
        }
        return get_the_title( $id );
    }
    
}

if ( ! function_exists( 'wpblc_get_post_or_comment_link' ) ) {

    /**
     * Get the post or comment link.
     *
     * @param array $item - The item.
     *
     * @since 1.0.0
     *
     * @return string
     */
    function wpblc_get_post_or_comment_link( $item ) {
        if ( ! isset( $item['ID'] ) ) {
            return;
        }

        $id = $item['ID'];

        if ( isset($item['is_comment']) && $item['is_comment'] ) {
            return get_comment_link( $id );
        }
        return get_permalink( $id );
    }
}

if ( ! function_exists( 'wpblc_get_post_or_comment_edit_link' ) ) {

    /**
     * Get the post or comment edit link.
     *
     * @param array $item - The item.
     *
     * @since 1.0.0
     *
     * @return string
     */
    function wpblc_get_post_or_comment_edit_link( $item ) {
        if ( ! isset( $item['ID'] ) ) {
            return;
        }

        $id = $item['ID'];

        if ( isset($item['is_comment']) && $item['is_comment'] ) {
            return get_edit_comment_link( $id );
        }
        return get_edit_post_link( $id );
    }
}

if ( ! function_exists( 'wpblc_get_post_or_comment_type' ) ) {

    /**
     * Get the post or comment type.
     *
     * @param array $item - The item.
     *
     * @since 1.0.0
     *
     * @return string
     */
    function wpblc_get_post_or_comment_type( $item ) {
        if ( ! isset( $item['ID'] ) ) {
            return;
        }

        if ( isset($item['is_comment']) && $item['is_comment'] ) {
            return __( 'Comment', 'wpblc-broken-links-checker' );
        }
        return ucfirst( get_post_type( $item['ID'] ) );
    }
}

