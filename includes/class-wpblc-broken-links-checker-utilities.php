<?php
/**
 * The WPBLC_Broken_Links_Checker_Utilities class.
 *
 * @package WPBLC_Broken_Links_Checker
 * @author
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WPBLC_Broken_Links_Checker_Utilities' ) ) {
	/**
	 * Utilities.
	 *
	 * @since 1.0.0
	 */
	class WPBLC_Broken_Links_Checker_Utilities {
		/**
		 * 
		 */
		public static function getLinksFromPages( $post_types = array( 'page' ) ) {
			global $wpdb;

			$s_time = time();

			$placeholders = implode(', ', array_fill(0, count($post_types), '%s'));
			$query = $wpdb->prepare("SELECT `ID` FROM {$wpdb->prefix}posts WHERE `post_status` = 'publish' AND `post_type` IN ($placeholders)", $post_types);

			$pages = $wpdb->get_results( $query );

			$psize              = count( $pages );

			for ( $page_index = 0; $page_index < $psize; $page_index ++ ) {
				$page_id = $pages[ $page_index ]->ID;
				$base    = get_permalink( $page_id );
				$content = $wpdb->get_results( $wpdb->prepare( 'select `post_content`,`post_title` from %1sposts where `id`=%1s', array( $wpdb->prefix, $page_id ) ) );
				$title   = $content[0]->post_title;
				$content = $content[0]->post_content;

				$links      = preg_split( '/<a/', $content );
				$lsize      = count( $links );
				$hash_links = array();

				$links = preg_split( '/<a | <link/', $content );
				self::moblc_get_links( 'href', $links, $hash_links );

				$links = preg_split( '/<img | <iframe/', $content );
				self::moblc_get_links( 'src', $links, $hash_links );

				if ( count( $hash_links ) ) {
					$hashs = array_keys( $hash_links );
					$lsize = count( $hash_links );

					for ( $link_index = 0; $link_index < $lsize; $link_index ++ ) {

						$link_hash = $hashs[ $link_index ];
						$link      = $hash_links[ $link_hash ];

						if ( ! empty( $link ) && filter_var( $link, FILTER_VALIDATE_URL ) ) {
							$link  = trim( self::relative_to_absolute( $link, $base ) );
							$stime = time();

							if ( strpos( $link, '://youtube' ) !== false || strpos( $link, '://www.youtube' ) !== false ) {
								$body = wp_remote_retrieve_body( wp_remote_post( $link ) );

								if ( strpos( $body, 'Video unavailable' ) !== false || strpos( $body, 'This video isn\'t available any more' ) !== false || strpos( $body, 'Something went wrong' ) ) {
									$response = new WP_Error( '404', __( 'Video unavailable', 'wp' ) );
								} else {
									$response = wp_remote_head( $link );
								}
							} else {
								$response = wp_remote_head( $link );
							}

							$status   = self::get_response_code( $response );

							var_dump($status);
							var_dump($link);
							$ltime = time();
							$time  = ( $ltime - $stime ) . 's';
							
						}
					}
				}
				$link_index = 0;
			}

			return;
		}

		public static function get_response_code( $response ) {
			if ( is_wp_error( $response ) ) {
				return $response->get_error_code();
			}

			return wp_remote_retrieve_response_code( $response );
		}

		public static function moblc_get_links( $moblc_attr, $links, &$link_array ) {
			$lsize = count( $links );

			for ( $index = 0; $index < $lsize; $index ++ ) {
				$link = $links[ $index ];
				if ( strpos( $link, $moblc_attr ) !== false ) {
					$link = preg_replace( '/.*\s*' . $moblc_attr . "=[\"|']/sm", '', $link );
					$link = preg_replace( "/[\"|'].*/s", '', $link );
					$link = trim( $link );
					if ( strpos( $link, '/embed/' ) !== false ) {
						list( , $video_id ) = explode( '/embed/', $link );
						$link               = 'https://youtube.com/watch?v=' . $video_id;
						list( $video_id, )  = explode( '?', $video_id );
						$link               = 'https://youtube.com/watch?v=' . $video_id;
					}
					if ( ! empty( $link ) && '#' !== $link ) {
						array_push( $link_array, $link );
					}
				}
			}
		}

		/**
		 * Function for keeping track of execution time.
		 *
		 * @param mixed $s_time scanning time.
		 * @return bool
		 */
		public static function moblc_check_time( $s_time ) {
			$max_time = ini_get( 'max_execution_time' );
			if ( ( $max_time - ( time() - $s_time ) ) <= 10 ) {
				return true;
			}

			return false;
		}

		/* Function for making link relative to absolute.
		*
		* @param mixed $rel relative.
		* @param mixed $base base.
		* @return string
		*/
	   public static function relative_to_absolute( $rel, $base ) {
		   $parse_base = wp_parse_url( $base );
		   $scheme     = $parse_base['scheme'];
		   $host       = $parse_base['host'];
		   $path       = $parse_base['path'];
	   
		   if ( strpos( $rel, '//' ) === 0 ) {
			   return $scheme . ':' . $rel;
		   }
	   
		   if ( wp_parse_url( $rel, PHP_URL_SCHEME ) !== '' ) {
			   return $rel;
		   }
	   
		   if ( '#' === $rel[0] || '?' === $rel[0] ) {
			   return $base . $rel;
		   }
	   
		   $path = preg_replace( '#/[^/]*$#', '', $path );
	   
		   if ( '/' === $rel[0] ) {
			   $path = '';
		   }
		   $abs = $host . $path . '/' . $rel;
		   $abs = preg_replace( '[(/\.?/)]', '/', $abs );
		   $abs = preg_replace( '[\/(?!\.\.)[^\/]+\/(\.\.\/)+]', '/', $abs );
	   
		   return $scheme . '://' . $abs;
	   }

	   /**
		* 
	    */
		public static function get_content_to_scan( $post_types = array( 'page' ) ) {

			global $wpdb;

			$s_time = time();

			$placeholders = implode(', ', array_fill(0, count($post_types), '%s'));
			$query = $wpdb->prepare("SELECT `ID` FROM {$wpdb->prefix}posts WHERE `post_status` = 'publish' AND `post_type` IN ($placeholders)", $post_types);

			$posts = $wpdb->get_results( $query );

			$args = array(
				'post_type'      => $post_types,
				'posts_per_page' => -1,
				'fields'         => 'ids',
			);

			$post_ids = get_posts( $args );
			$links_in_db = get_option( 'wpblc_broken_links_checker_links', array() );
			$marked_fixed = isset( $links_in_db['fixed'] ) ? $links_in_db['fixed'] : array();
			$links_to_update = [];

			foreach( $post_ids as $post_id ) {
				$get_the_content = get_the_content( null, false, $post_id );

				$content = apply_filters( 'the_content', $get_the_content );

				$links = self::extract_links( $content );

				if ( ! empty( $links ) ) {
					foreach( $links as $link ) {
						$status = self::check_link( $link, $post_id );

						$link_source = self::get_link_source( $link );
						$status['link_source'] = $link_source;

						$links_to_update[$status['type']][] = $status;

						if ( isset( $marked_fixed ) && ! empty( $marked_fixed ) ) {
							$links_column = array_column($marked_fixed, 'link');
							$position = array_search($link, $links_column);

							if ( false !== $position ) {
								$links_to_update[$status['type']][$position]['marked_fixed'] = 'fixed';
							}
						}
					}
				}
			}

			return update_option( 'wpblc_broken_links_checker_links', $links_to_update );

			// echo '<pre>';
			// var_dump($links_to_update);
			// echo '</pre>';
			

			// // Get the content
			// $content = '';
			// foreach ( $posts as $post ) {

			// 	$post_id = $post->ID;
			// 	// Get the content
			// 	$get_the_content = get_the_content( null, false, $post_id );

			// 	//$content = apply_filters( 'the_content', $get_the_content );

			// 	// Extract links
			// 	$links = self::extract_links( $get_the_content );

			// 	var_dump($links);
			// }

			// Return
			//return $content;
		}
		/**
		 * Extract links from content
		 *
		 * @param [type] $content
		 * @return array
		 */
		public static function extract_links( $content ) {
			// Array that will contain our extracted links.
			$matches = [];

			// Get html link sources
			$html_link_sources = self::get_html_link_sources();
			if ( !empty( $html_link_sources ) ) {

				// Fetch the DOM once
				$htmlDom = new DOMDocument;
				@$htmlDom->loadHTML( $content );

				// Look for each source
				foreach ( $html_link_sources as $tag => $html_link_source ) {
					$links = $htmlDom->getElementsByTagName( $tag );

					// Loop through the DOMNodeList.
					if ( !empty( $links ) ) {
						foreach ( $links as $link ) {

							// Get the link in the href attribute.
							$linkHref = filter_var( $link->getAttribute( $html_link_source ), FILTER_SANITIZE_URL );

							// Add the link to our array.
							$matches[] = $linkHref;
						}
					}
				}
			}

			// Return
			return $matches;
		} // End extract_links()

		/**
		 * Get the html link sources from the html
		 *
		 * @return array
		 */
		public static function get_html_link_sources() {
			$el = [ 
				'a'      => 'href',
				'img'    => 'src',
				'iframe' => 'src'
			];
			return filter_var_array( apply_filters( 'wpblc_html_link_sources', $el ), FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		} // End get_html_link_sources()

		/**
		 * Check if a URL is broken or unsecure
		 *
		 * @param string $link
		 * @param integer $post_id
		 * @return array
		 */
		public static function check_link( $link, $post_id ) {
			// Filter the link
			$link = apply_filters( 'wpblc_link_before_prechecks', $link );

			// Assuming the link is okay
			$status = [
				'type' => 'good',
				'code' => 200,
				'text' => 'OK',
				'link' => $link,
				'post_id' => $post_id,
				'detected_at' => self::convert_timezone(),
				'marked_fixed' => ''
			];

			// Handle the filtered link if false
			if ( !$link ) {
				return [
					'type' => 'broken',
					'code' => 0,
					'text' => 'Did not pass pre-check filter',
					'link' => $link,
					'post_id' => $post_id,
					'detected_at' => self::convert_timezone(),
					'marked_fixed' => 'not-fixed',
				];

			// Handle the filtered link if in-proper array
			} elseif ( is_array( $link ) && ( !isset( $link[ 'type' ] ) || !isset( $link[ 'code' ] ) || !isset( $link[ 'text' ] ) ) ) {
				return [
					'type' => 'broken',
					'code' => 0,
					'text' => 'Did not pass pre-check filter',
					'link' => $link,
					'post_id' => $post_id,
					'detected_at' => self::convert_timezone(),
					'marked_fixed' => 'not-fixed',
				];
		
			// Return the filtered link as a status if proper array
			} elseif ( is_array( $link ) ) {
				return $link;

			// Skip null links
			} elseif ( $link && strlen( trim( $link ) ) == 0 ) {
				$status[ 'text' ] = 'Skipping null';
				return $status;
			
			// Skip if it is a hashtag / anchor link / query string
			} elseif ( $link[0] == '#' || $link[0] == '?' ) {
				$status[ 'text' ] = 'Skipping: starts with '.$link[0];
				return $status;
		
			// Skip if omitted
			}  elseif ( $link == '' ) {
				$status = [
					'type' => 'broken',
					'code' => 0,
					'text' => 'Empty link',
					'link' => $link,
					'post_id' => $post_id,
					'detected_at' => self::convert_timezone(),
					'marked_fixed' => 'not-fixed',
				];
				
			// If the match is local, easy check
			} elseif ( str_starts_with( $link, home_url() ) || str_starts_with( $link, '/' ) ) {
			
				// Check locally first
				if ( !url_to_postid( $link ) ) {

					// It may be redirected or an archive page, so let's check status anyway
					return self::check_url_status_code( $link, $post_id );
				}

			// Otherwise
			} else {

				// Skip url schemes
				foreach ( self::get_url_schemes() as $scheme ) {
					if ( str_starts_with( $link, $scheme.':' ) ) {
						$status[ 'text' ] = 'Skipping: Non-Http URL Schema';
						return $status;
					}
				}

				// Return the status
				return self::check_url_status_code( $link, $post_id );
			}

			// Return the good status
			return $status;
		} // End check_link

		/**
		 * Check a URL to see if it Exists
		 *
		 * @param string $url
		 * @param integer|null $timeout
		 * @return array
		 */
		public static function check_url_status_code( $url, $post_id = 0, $timeout = null ) {
			// Get timeout
			if ( is_null( $timeout ) ) {
				$timeout = 10;
			}

			// Add the home url
			if ( str_starts_with( $url, '/' ) ) {
				$link = home_url().$url;
			} else {
				$link = $url;
			}

			// Check if from youtube
			if ( $watch_url = self::is_youtube_link( $link ) ) {
				$link = 'https://www.youtube.com/oembed?format=json&url='.$watch_url;
			}

			// The request args
			// See https://developer.wordpress.org/reference/classes/WP_Http/request/
			$http_request_args = apply_filters( 'wpblc_http_request_args', [
				'method'      => 'HEAD',
				'timeout'     => $timeout, // How long the connection should stay open in seconds. Default 5.
				'redirection' => 5,        // Number of allowed redirects. Not supported by all transports. Default 5.
				'httpversion' => '1.1',    // Version of the HTTP protocol to use. Accepts '1.0' and '1.1'. Default '1.0'.
				'sslverify'   => true
			], $url );

			// Check the link
			$response = wp_safe_remote_get( $link, $http_request_args );
			if ( !is_wp_error( $response ) ) {
				$code = wp_remote_retrieve_response_code( $response );    
				$error = 'Unknown';
			} else {
				$code = 0;
				$error = $response->get_error_message();
			}

			// Let's make invalid URL 0 codes broken
			if ( $code === 0 && $error == 'A valid URL was not provided.' ) {
				$code = 666;
			}

			// Possible Codes
			$codes = [
				0 => $error,
				100 => 'Continue',
				101 => 'Switching Protocols',
				102 => 'Processing', // WebDAV; RFC 2518
				103 => 'Early Hints', // RFC 8297
				200 => 'OK',
				201 => 'Created',
				202 => 'Accepted',
				203 => 'Non-Authoritative Information', // since HTTP/1.1
				204 => 'No Content',
				205 => 'Reset Content',
				206 => 'Partial Content', // RFC 7233
				207 => 'Multi-Status', // WebDAV; RFC 4918
				208 => 'Already Reported', // WebDAV; RFC 5842
				226 => 'IM Used', // RFC 3229
				300 => 'Multiple Choices',
				301 => 'Moved Permanently',
				302 => 'Found', // Previously "Moved temporarily"
				303 => 'See Other', // since HTTP/1.1
				304 => 'Not Modified', // RFC 7232
				305 => 'Use Proxy', // since HTTP/1.1
				306 => 'Switch Proxy',
				307 => 'Temporary Redirect', // since HTTP/1.1
				308 => 'Permanent Redirect', // RFC 7538
				400 => 'Bad Request',
				401 => 'Unauthorized', // RFC 7235
				402 => 'Payment Required',
				403 => 'Forbidden or Unsecure',
				404 => 'Not Found',
				405 => 'Method Not Allowed',
				406 => 'Not Acceptable',
				407 => 'Proxy Authentication Required', // RFC 7235
				408 => 'Request Timeout',
				409 => 'Conflict',
				410 => 'Gone',
				411 => 'Length Required',
				412 => 'Precondition Failed', // RFC 7232
				413 => 'Payload Too Large', // RFC 7231
				414 => 'URI Too Long', // RFC 7231
				415 => 'Unsupported Media Type', // RFC 7231
				416 => 'Range Not Satisfiable', // RFC 7233
				417 => 'Expectation Failed',
				418 => 'I\'m a teapot', // RFC 2324, RFC 7168
				421 => 'Misdirected Request', // RFC 7540
				422 => 'Unprocessable Entity', // WebDAV; RFC 4918
				423 => 'Locked', // WebDAV; RFC 4918
				424 => 'Failed Dependency', // WebDAV; RFC 4918
				425 => 'Too Early', // RFC 8470
				426 => 'Upgrade Required',
				428 => 'Precondition Required', // RFC 6585
				429 => 'Too Many Requests', // RFC 6585
				431 => 'Request Header Fields Too Large', // RFC 6585
				451 => 'Unavailable For Legal Reasons', // RFC 7725
				500 => 'Internal Server Error',
				501 => 'Not Implemented',
				502 => 'Bad Gateway',
				503 => 'Service Unavailable',
				504 => 'Gateway Timeout',
				505 => 'HTTP Version Not Supported',
				506 => 'Variant Also Negotiates', // RFC 2295
				507 => 'Insufficient Storage', // WebDAV; RFC 4918
				508 => 'Loop Detected', // WebDAV; RFC 5842
				510 => 'Not Extended', // RFC 2774
				511 => 'Network Authentication Required', // RFC 6585
				
				// Unofficial codes
				103 => 'Checkpoint',
				218 => 'This is fine', // Apache Web Server
				419 => 'Page Expired', // Laravel Framework
				420 => 'Method Failure', // Spring Framework
				420 => 'Enhance Your Calm', // Twitter
				430 => 'Request Header Fields Too Large', // Shopify
				450 => 'Blocked by Windows Parental Controls', // Microsoft
				498 => 'Invalid Token', // Esri
				499 => 'Token Required', // Esri
				509 => 'Bandwidth Limit Exceeded', // Apache Web Server/cPanel
				526 => 'Invalid SSL Certificate', // Cloudflare and Cloud Foundry's gorouter
				529 => 'Site is overloaded', // Qualys in the SSLLabs
				530 => 'Site is frozen', // Pantheon web platform
				598 => 'Network read timeout error', // Informal convention
				440 => 'Login Time-out', // IIS
				449 => 'Retry With', // IIS
				451 => 'Redirect', // IIS
				444 => 'No Response', // nginx
				494 => 'Request header too large', // nginx
				495 => 'SSL Certificate Error', // nginx
				496 => 'SSL Certificate Required', // nginx
				497 => 'HTTP Request Sent to HTTPS Port', // nginx
				499 => 'Client Closed Request', // nginx
				520 => 'Web Server Returned an Unknown Error', // Cloudflare
				521 => 'Web Server Is Down', // Cloudflare
				522 => 'Connection Timed Out', // Cloudflare
				523 => 'Origin Is Unreachable', // Cloudflare
				524 => 'A Timeout Occurred', // Cloudflare
				525 => 'SSL Handshake Failed', // Cloudflare
				526 => 'Invalid SSL Certificate', // Cloudflare
				527 => 'Railgun Error', // Cloudflare
				666 => 'Invalid URL', // Non-standard code
				999 => 'Scanning Not Permitted' // Non-standard code
			];

			// Bad links
			if ( in_array( $code, self::get_bad_status_codes() ) ) {
				$type = 'broken';

			// Warnings
			} elseif ( in_array( $code, self::get_warning_status_codes() ) ) {
				$type = 'warning';

			// Good links
			} else {
				$type = 'good';
			}

			// Filter status
			$status = apply_filters( 'wpblc_status', [
				'type' => $type,
				'code' => $code,
				'text' => isset( $codes[ $code ] ) ? $codes[ $code ] : $error,
				'link' => $url,
				'post_id' => $post_id,
				'detected_at' => self::convert_timezone(),
				'marked_fixed' => 'not-fixed'
			] );

			// Return the array
			return $status;
		} // End check_url_status_code

		/**
		 * Get all the URL Schemes to ignore in the pre-check
		 * Last updated: 3/7/24
		 *
		 * @return array
		 */
		public static function get_url_schemes() {
			// Official: https://www.iana.org/assignments/uri-schemes/uri-schemes.xhtml
			$official = [ 'aaa', 'aaas', 'about', 'acap', 'acct', 'acd', 'acr', 'adiumxtra', 'adt', 'afp', 'afs', 'aim', 'amss', 'android', 'appdata', 'apt', 'ar', 'ark', 'at', 'attachment', 'aw', 'barion', 'bb', 'beshare', 'bitcoin', 'bitcoincash', 'blob', 'bolo', 'brid', 'browserext', 'cabal', 'calculator', 'callto', 'cap', 'cast', 'casts', 'chrome', 'chrome-extension', 'cid', 'coap', 'coap+tcp', 'coap+ws', 'coaps', 'coaps+tcp', 'coaps+ws', 'com-eventbrite-attendee', 'content', 'content-type', 'crid', 'cstr', 'cvs', 'dab', 'dat', 'data', 'dav', 'dhttp', 'diaspora', 'dict', 'did', 'dis', 'dlna-playcontainer', 'dlna-playsingle', 'dns', 'dntp', 'doi', 'dpp', 'drm', 'drop', 'dtmi', 'dtn', 'dvb', 'dvx', 'dweb', 'ed2k', 'eid', 'elsi', 'embedded', 'ens', 'ethereum', 'example', 'facetime', 'fax', 'feed', 'feedready', 'fido', 'file', 'filesystem', 'finger', 'first-run-pen-experience', 'fish', 'fm', 'ftp', 'fuchsia-pkg', 'geo', 'gg', 'git', 'gitoid', 'gizmoproject', 'go', 'gopher', 'graph', 'grd', 'gtalk', 'h323', 'ham', 'hcap', 'hcp', 'hxxp', 'hxxps', 'hydrazone', 'hyper', 'iax', 'icap', 'icon', 'im', 'imap', 'info', 'iotdisco', 'ipfs', 'ipn', 'ipns', 'ipp', 'ipps', 'irc', 'irc6', 'ircs', 'iris', 'iris.beep', 'iris.lwz', 'iris.xpc', 'iris.xpcs', 'isostore', 'itms', 'jabber', 'jar', 'jms', 'keyparc', 'lastfm', 'lbry', 'ldap', 'ldaps', 'leaptofrogans', 'lid', 'lorawan', 'lpa', 'lvlt', 'machineProvisioningProgressReporter', 'magnet', 'mailserver', 'mailto', 'maps', 'market', 'matrix', 'message', 'microsoft.windows.camera', 'microsoft.windows.camera.multipicker', 'microsoft.windows.camera.picker', 'mid', 'mms', 'modem', 'mongodb', 'moz', 'ms-access', 'ms-appinstaller', 'ms-browser-extension', 'ms-calculator', 'ms-drive-to', 'ms-enrollment', 'ms-excel', 'ms-eyecontrolspeech', 'ms-gamebarservices', 'ms-gamingoverlay', 'ms-getoffice', 'ms-help', 'ms-infopath', 'ms-inputapp', 'ms-launchremotedesktop', 'ms-lockscreencomponent-config', 'ms-media-stream-id', 'ms-meetnow', 'ms-mixedrealitycapture', 'ms-mobileplans', 'ms-newsandinterests', 'ms-officeapp', 'ms-people', 'ms-project', 'ms-powerpoint', 'ms-publisher', 'ms-remotedesktop', 'ms-remotedesktop-launch', 'ms-restoretabcompanion', 'ms-screenclip', 'ms-screensketch', 'ms-search', 'ms-search-repair', 'ms-secondary-screen-controller', 'ms-secondary-screen-setup', 'ms-settings', 'ms-settings-airplanemode', 'ms-settings-bluetooth', 'ms-settings-camera', 'ms-settings-cellular', 'ms-settings-cloudstorage', 'ms-settings-connectabledevices', 'ms-settings-displays-topology', 'ms-settings-emailandaccounts', 'ms-settings-language', 'ms-settings-location', 'ms-settings-lock', 'ms-settings-nfctransactions', 'ms-settings-notifications', 'ms-settings-power', 'ms-settings-privacy', 'ms-settings-proximity', 'ms-settings-screenrotation', 'ms-settings-wifi', 'ms-settings-workplace', 'ms-spd', 'ms-stickers', 'ms-sttoverlay', 'ms-transit-to', 'ms-useractivityset', 'ms-virtualtouchpad', 'ms-visio', 'ms-walk-to', 'ms-whiteboard', 'ms-whiteboard-cmd', 'ms-word', 'msnim', 'msrp', 'msrps', 'mss', 'mt', 'mtqp', 'mumble', 'mupdate', 'mvn', 'mvrp', 'mvrps', 'news', 'nfs', 'ni', 'nih', 'nntp', 'notes', 'num', 'ocf', 'oid', 'onenote', 'onenote-cmd', 'opaquelocktoken', 'openid', 'openpgp4fpr', 'otpauth', 'p1', 'pack', 'palm', 'paparazzi', 'payment', 'payto', 'pkcs11', 'platform', 'pop', 'pres', 'prospero', 'proxy', 'pwid', 'psyc', 'pttp', 'qb', 'query', 'quic-transport', 'redis', 'rediss', 'reload', 'res', 'resource', 'rmi', 'rsync', 'rtmfp', 'rtmp', 'rtsp', 'rtsps', 'rtspu', 'sarif', 'secondlife', 'secret-token', 'service', 'session', 'sftp', 'sgn', 'shc', 'shttp', 'sieve', 'simpleledger', 'simplex', 'sip', 'sips', 'skype', 'smb', 'smp', 'sms', 'smtp', 'snews', 'snmp', 'soap.beep', 'soap.beeps', 'soldat', 'spiffe', 'spotify', 'ssb', 'ssh', 'starknet', 'steam', 'stun', 'stuns', 'submit', 'svn', 'swh', 'swid', 'swidpath', 'tag', 'taler', 'teamspeak', 'tel', 'teliaeid', 'telnet', 'tftp', 'things', 'thismessage', 'tip', 'tn3270', 'tool', 'turn', 'turns', 'tv', 'udp', 'unreal', 'upt', 'urn', 'ut2004', 'uuid-in-package', 'v-event', 'vemmi', 'ventrilo', 'ves', 'videotex', 'vnc', 'view-source', 'vscode', 'vscode-insiders', 'vsls', 'w3', 'wais', 'web3', 'wcr', 'webcal', 'web+ap', 'wifi', 'wpid', 'ws', 'wss', 'wtai', 'wyciwyg', 'xcon', 'xcon-userid', 'xfire', 'xmlrpc.beep', 'xmlrpc.beeps', 'xmpp', 'xftp', 'xrcp', 'xri', 'ymsgr' ];

			// Unofficial: https://en.wikipedia.org/wiki/List_of_URI_schemes
			$unofficial = [ 'admin', 'app', 'freeplane', 'javascript', 'jdbc', 'msteams', 'ms-spd', 'odbc', 'psns', 'rdar', 's3', 'trueconf', 'slack', 'stratum', 'viber', 'zoommtg', 'zoomus' ];

			// Return them
			$all_schemes = array_unique( array_merge( $official, $unofficial ) );
			return filter_var_array( apply_filters( 'wpblc_url_schemes', $all_schemes ), FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		} // End get_url_schemes()

		/**
		 * Check if a link is on YouTube, if so return ID
		 * Does not check if the video is valid
		 *
		 * @param string $link
		 * @return boolean
		 */
		public static function is_youtube_link( $link ) {
			// The id
			$id = false;

			// Get the host
			$parse = parse_url( $link );
			if ( isset( $parse[ 'host' ] ) && isset( $parse[ 'path' ] ) ) {
				$host = $parse[ 'host' ];
				$path = $parse[ 'path' ];

				// Make sure it's on youtube
				if ( $host && in_array( $host, [ 'youtube.com', 'www.youtube.com', 'youtu.be' ] ) ) {
					
					// '/embed/'
					if ( strpos( $path, '/embed/' ) !== false ) {
						$id = str_replace( '/embed/', '', $path );
						if ( strpos( $id, '&' ) !== false ) {
							$id = substr( $id, 0, strpos( $id, '&' ) );
						}

					// '/v/'
					} elseif ( strpos( $path, '/v/' ) !== false ) {
						$id = str_replace( '/v/', '', $path );
						if ( strpos( $id, '&' ) !== false ) {
							$id = substr( $id, 0, strpos( $id, '&' ) );
						}

					// '/watch'
					} elseif ( strpos( $path, '/watch' ) !== false && isset( $parse[ 'query' ] ) ) {
						parse_str( $parse[ 'query' ], $queries );
						if ( isset( $queries[ 'v' ] ) ) {
							$id = $queries[ 'v' ];
						}
					}
				}
			}

			// If id
			if ( $id ) {

				// Create a watch url
				return 'https://www.youtube.com/watch?v='.$id;
			}

			// We got nothin'
			return false;
		} // End is_youtube_link()

		/**
		 * 
		 */
		public static function get_link_source($link) {
			$link_host = parse_url($link, PHP_URL_HOST);
			$site_host = parse_url(get_site_url(), PHP_URL_HOST);
		
			return $link_host === $site_host ? 'internal' : 'external';
		}

		/**
		 * Get the bad status codes we are using
		 *
		 * @return array
		 */
		public static function get_bad_status_codes() {
			$default_codes = [ 666, 308, 400, 404, 408 ];
			return filter_var_array( apply_filters( 'wpblc_bad_status_codes', $default_codes ), FILTER_SANITIZE_NUMBER_INT );
		} // End get_bad_status_codes()


		/**
		 * Get the warning status codes we are using
		 *
		 * @return array
		 */
		public static function get_warning_status_codes() {
			$default_codes = [ 0 ];
			$default_codes = filter_var_array( apply_filters( 'wpblc_warning_status_codes', $default_codes ), FILTER_SANITIZE_NUMBER_INT );
			if ( 1 ) {
				return $default_codes;
			} else {
				return [];
			}
		} // End get_bad_status_codes()

		/**
		 * Convert timezone
		 * 
		 * @param string $date
		 * @param string $format
		 * @param string $timezone
		 * @return string
		 */
		public static function convert_timezone( $date = null, $format = 'F j, Y g:i A', $timezone = null ) {
			// Get today as default
			if ( is_null( $date ) ) {
				$date = gmdate( 'Y-m-d H:i:s' );
			}

			// Get the date in UTC time
			$date = new DateTime( $date, new DateTimeZone( 'UTC' ) );

			// Get the timezone string
			if ( !is_null( $timezone ) ) {
				$timezone_string = $timezone;
			} else {
				$timezone_string = wp_timezone_string();
			}

			// Set the timezone to the new one
			$date->setTimezone( new DateTimeZone( $timezone_string ) );

			// Format it the way we way
			$new_date = $date->format( $format );

			// Return it
			return $new_date;
		} // End convert_timezone()
	}
}