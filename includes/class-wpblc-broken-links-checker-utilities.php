<?php
/**
 * The WPBLC_Broken_Links_Checker_Utilities class.
 *
 * @package WPBLC_Broken_Links_Checker
 * @author Ilias Chelidonis
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
		 * Process the scan.
		 *
		 * @since 1.0.0
		 *
		 * @return bool True if the scan was successful, false otherwise.
		 */
		public static function process_scan() {
			// Get the settings.
			$settings         = get_option( 'wpblc_broken_links_checker_settings', array() );
			$email_enabled    = isset( $settings['email_notifications'] ) ? $settings['email_notifications'] : 'off';
			$email_addresses  = isset( $settings['email_addresses'] ) ? $settings['email_addresses'] : '';
			$number_of_links  = isset( $settings['number_of_links'] ) ? $settings['number_of_links'] : 'all';
			$set_number       = isset( $settings['set_links_number'] ) ? $settings['set_links_number'] : 0;
			$exclusion_urls   = isset( $settings['exclusion_urls'] ) ? $settings['exclusion_urls'] : '';
			$links_to_exclude = explode( "\n", $exclusion_urls );
			$links_to_exclude = array_map(
				function ( $link ) {
					return rtrim( trim( $link ), '/' );
				},
				$links_to_exclude
			);

			if ( 'all' == $number_of_links ) {
				$number_of_links = -1;
			} else {
				$number_of_links = (int) $set_number;
			}

			// Get the data to scan.
			$data_to_scan = self::get_data_to_scan( $settings );

			// Get already saved links.
			$links_in_db     = get_option( 'wpblc_broken_links_checker_links', array() );
			$marked_fixed    = isset( $links_in_db['fixed'] ) ? $links_in_db['fixed'] : array();
			$links_to_update = array();

			$count = 0;
			$break = false;

			foreach ( $data_to_scan as $single ) {
				if ( is_object( $single ) ) {
					$is_comment = true;
					$post_id    = $single->comment_ID;
					$content    = $single->comment_content;
				} else {
					$is_comment      = false;
					$post_id         = $single;
					$get_the_content = get_the_content( null, false, $post_id );

					$content = apply_filters( 'the_content', $get_the_content );
				}

				// Extract links from content.
				$links = self::extract_links( $content );

				if ( ! empty( $links ) ) {
					foreach ( $links as $link ) {
						++$count;

						if ( -1 !== $number_of_links && $count > (int) $number_of_links ) {
							$break = true;
							break;
						}

						if ( is_array( $links_to_exclude ) && in_array( rtrim( $link, '/' ), $links_to_exclude ) ) {
							continue;
						}

						// Check the link.
						$status = self::check_link( $link, $post_id, $is_comment );

						$link_source           = self::get_link_source( $link );
						$status['link_source'] = $link_source;

						if ( isset( $marked_fixed ) && ! empty( $marked_fixed ) ) {

							$fixed = array_filter(
								$marked_fixed,
								function ( $fixed_link, $index ) use ( $link ) {
									return $fixed_link['link'] == $link;
								},
								ARRAY_FILTER_USE_BOTH
							);

							if ( $fixed && is_array( $fixed ) ) {
								$index                  = key( $fixed );
								$status['marked_fixed'] = 'fixed';
								$status['detected_at']  = isset( $fixed[ $index ]['detected_at'] ) ? $fixed[ $index ]['detected_at'] : wpblc_convert_timezone();
							}
						}

						$links_to_update[ $status['type'] ][] = $status;
					}
				}

				if ( $break ) {
					break;
				}
			}

			// Send email.
			self::send_mails( $email_enabled, $email_addresses, $links_to_update['broken'] );

			$links_to_update = array_merge( array( 'fixed' => $marked_fixed ), $links_to_update );
			return update_option( 'wpblc_broken_links_checker_links', $links_to_update );
		}

		/**
		 * Get the data to scan.
		 *
		 * @param array $settings Default empty array.
		 *
		 * @since 1.0.0
		 *
		 * @return array
		 */
		public static function get_data_to_scan( $settings = array() ) {
			$scope_of_scan = isset( $settings['scope_of_scan'] ) ? $settings['scope_of_scan'] : array( 'all' );

			$scan_comment = false;
			$data_to_scan = array();

			if ( in_array( 'all', $scope_of_scan ) ) {
				$post_types   = array( 'post', 'page' );
				$scan_comment = true;
			} else {
				$post_types = $scope_of_scan;

				if ( in_array( 'comment', $scope_of_scan ) ) {
					unset( $scope_of_scan['comment'] );
					$scan_comment = true;
				}
			}

			$args = array(
				'post_type'      => $post_types,
				'posts_per_page' => -1,
				'fields'         => 'ids',
			);

			$post_ids = get_posts( $args );

			$data_to_scan = array_merge( $data_to_scan, $post_ids );

			if ( $scan_comment ) {
				$comment_args = array(
					'status' => 'approve',
				);

				$comments = get_comments( $comment_args );

				$data_to_scan = array_merge( $data_to_scan, $comments );
			}

			return $data_to_scan;
		}

		/**
		 * Extract links from content.
		 *
		 * @param string $content The content.
		 *
		 * @since 1.0.0
		 *
		 * @return array
		 */
		public static function extract_links( $content ) {
			// Array that will contain our extracted links.
			$matches = array();

			// Get html link sources.
			$html_link_sources = self::get_html_link_sources();
			if ( ! empty( $html_link_sources ) ) {

				// Fetch the DOM once.
				$html_dom = new DOMDocument();
				@$html_dom->loadHTML( $content );

				// Look for each source.
				foreach ( $html_link_sources as $tag => $html_link_source ) {
					$links = $html_dom->getElementsByTagName( $tag );

					// Loop through the DOMNodeList.
					if ( ! empty( $links ) ) {
						foreach ( $links as $link ) {

							// Get the link in the href attribute.
							$link_href = filter_var( $link->getAttribute( $html_link_source ), FILTER_SANITIZE_URL );

							// Add the link to our array.
							$matches[] = $link_href;
						}
					}
				}
			}

			return $matches;
		}

		/**
		 * Get the html link sources from the html.
		 *
		 * @since 1.0.0
		 *
		 * @return array
		 */
		public static function get_html_link_sources() {
			$el = array(
				'a'      => 'href',
				'img'    => 'src',
				'iframe' => 'src',
			);
			return filter_var_array( apply_filters( 'wpblc_html_link_sources', $el ), FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		}

		/**
		 * Check if a URL is broken or unsecure
		 *
		 * @param string  $link The link to check.
		 * @param integer $post_id The post ID.
		 * @param boolean $is_comment If the link is a comment.
		 *
		 * @since 1.0.0
		 *
		 * @return array
		 */
		public static function check_link( $link, $post_id, $is_comment ) {
			// Filter the link.
			$link = apply_filters( 'wpblc_link_before_prechecks', $link );

			// Assuming the link is okay.
			$status = array(
				'type'         => 'good',
				'code'         => 200,
				'text'         => 'OK',
				'link'         => $link,
				'ID'           => $post_id,
				'is_comment'   => $is_comment,
				'detected_at'  => wpblc_convert_timezone(),
				'marked_fixed' => '',
			);

			// Handle the filtered link if false.
			if ( ! $link ) {
				return array(
					'type'         => 'broken',
					'code'         => 0,
					'text'         => 'Did not pass pre-check filter',
					'link'         => $link,
					'ID'           => $post_id,
					'is_comment'   => $is_comment,
					'detected_at'  => wpblc_convert_timezone(),
					'marked_fixed' => 'not-fixed',
				);

				// Handle the filtered link if in-proper array.
			} elseif ( is_array( $link ) && ( ! isset( $link['type'] ) || ! isset( $link['code'] ) || ! isset( $link['text'] ) ) ) {
				return array(
					'type'         => 'broken',
					'code'         => 0,
					'text'         => 'Did not pass pre-check filter',
					'link'         => $link,
					'ID'           => $post_id,
					'is_comment'   => $is_comment,
					'detected_at'  => wpblc_convert_timezone(),
					'marked_fixed' => 'not-fixed',
				);

				// Return the filtered link as a status if proper array.
			} elseif ( is_array( $link ) ) {
				return $link;

				// Skip null links.
			} elseif ( $link && strlen( trim( $link ) ) == 0 ) {
				$status['text'] = 'Skipping null';
				return $status;

				// Skip if it is a hashtag / anchor link / query string.
			} elseif ( '#' == $link[0] || '?' == $link[0] ) {
				$status['text'] = 'Skipping: starts with ' . $link[0];
				return $status;

				// Skip if omitted.
			} elseif ( '' == $link ) {
				$status = array(
					'type'         => 'broken',
					'code'         => 0,
					'text'         => 'Empty link',
					'link'         => $link,
					'ID'           => $post_id,
					'is_comment'   => $is_comment,
					'detected_at'  => wpblc_convert_timezone(),
					'marked_fixed' => 'not-fixed',
				);

				// If the match is local, easy check.
			} elseif ( str_starts_with( $link, home_url() ) || str_starts_with( $link, '/' ) ) {

				// Check locally first.
				if ( ! url_to_postid( $link ) ) {

					// It may be redirected or an archive page, so let's check status anyway.
					return self::check_url_status_code( $link, $post_id, $is_comment );
				}

				// Otherwise.
			} else {

				// Skip url schemes.
				foreach ( self::get_url_schemes() as $scheme ) {
					if ( str_starts_with( $link, $scheme . ':' ) ) {
						$status['text'] = 'Skipping: Non-Http URL Schema';
						return $status;
					}
				}

				// Return the status.
				return self::check_url_status_code( $link, $post_id, $is_comment );
			}

			return $status;
		}

		/**
		 * Check a URL to see if it Exists
		 *
		 * @param string       $url The URL to check.
		 * @param integer      $post_id The post ID.
		 * @param boolean      $is_comment If the link is a comment.
		 * @param integer|null $timeout The timeout.
		 *
		 * @since 1.0.0
		 *
		 * @return array
		 */
		public static function check_url_status_code( $url, $post_id = 0, $is_comment, $timeout = null ) {
			// Get timeout.
			if ( is_null( $timeout ) ) {
				$timeout = 10;
			}

			// Add the home url.
			if ( str_starts_with( $url, '/' ) ) {
				$link = home_url() . $url;
			} else {
				$link = $url;
			}

			// Check if from youtube.
			$watch_url = self::is_youtube_link( $link );
			if ( $watch_url ) {
				$link = 'https://www.youtube.com/oembed?format=json&url=' . $watch_url;
			}

			// The request args.
			$http_request_args = apply_filters(
				'wpblc_http_request_args',
				array(
					'method'      => 'HEAD',
					'timeout'     => $timeout,
					'redirection' => 5,
					'httpversion' => '1.1',
					'sslverify'   => true,
				),
				$url
			);

			// Check the link.
			$response = wp_safe_remote_get( $link, $http_request_args );
			if ( ! is_wp_error( $response ) ) {
				$code  = wp_remote_retrieve_response_code( $response );
				$error = 'Unknown';
			} else {
				$code  = 0;
				$error = $response->get_error_message();
			}

			// Let's make invalid URL 0 codes broken.
			if ( 0 === $code && 'A valid URL was not provided.' == $error ) {
				$code = 666;
			}

			// Possible Codes.
			$codes = array(
				0   => $error,
				100 => 'Continue',
				101 => 'Switching Protocols',
				102 => 'Processing',
				103 => 'Early Hints',
				200 => 'OK',
				201 => 'Created',
				202 => 'Accepted',
				203 => 'Non-Authoritative Information',
				204 => 'No Content',
				205 => 'Reset Content',
				206 => 'Partial Content',
				207 => 'Multi-Status',
				208 => 'Already Reported',
				226 => 'IM Used',
				300 => 'Multiple Choices',
				301 => 'Moved Permanently',
				302 => 'Found',
				303 => 'See Other',
				304 => 'Not Modified',
				305 => 'Use Proxy',
				306 => 'Switch Proxy',
				307 => 'Temporary Redirect',
				308 => 'Permanent Redirect',
				400 => 'Bad Request',
				401 => 'Unauthorized',
				402 => 'Payment Required',
				403 => 'Forbidden or Unsecure',
				404 => 'Not Found',
				405 => 'Method Not Allowed',
				406 => 'Not Acceptable',
				407 => 'Proxy Authentication Required',
				408 => 'Request Timeout',
				409 => 'Conflict',
				410 => 'Gone',
				411 => 'Length Required',
				412 => 'Precondition Failed',
				413 => 'Payload Too Large',
				414 => 'URI Too Long',
				415 => 'Unsupported Media Type',
				416 => 'Range Not Satisfiable',
				417 => 'Expectation Failed',
				418 => 'I\'m a teapot',
				421 => 'Misdirected Request',
				422 => 'Unprocessable Entity',
				423 => 'Locked',
				424 => 'Failed Dependency',
				425 => 'Too Early',
				426 => 'Upgrade Required',
				428 => 'Precondition Required',
				429 => 'Too Many Requests',
				431 => 'Request Header Fields Too Large',
				451 => 'Unavailable For Legal Reasons',
				500 => 'Internal Server Error',
				501 => 'Not Implemented',
				502 => 'Bad Gateway',
				503 => 'Service Unavailable',
				504 => 'Gateway Timeout',
				505 => 'HTTP Version Not Supported',
				506 => 'Variant Also Negotiates',
				507 => 'Insufficient Storage',
				508 => 'Loop Detected',
				510 => 'Not Extended',
				511 => 'Network Authentication Required',

				// Unofficial codes.
				218 => 'This is fine',
				419 => 'Page Expired',
				420 => 'Method Failure',
				430 => 'Request Header Fields Too Large',
				450 => 'Blocked by Windows Parental Controls',
				498 => 'Invalid Token',
				499 => 'Token Required',
				509 => 'Bandwidth Limit Exceeded',
				526 => 'Invalid SSL Certificate',
				529 => 'Site is overloaded',
				530 => 'Site is frozen',
				598 => 'Network read timeout error',
				440 => 'Login Time-out',
				444 => 'No Response',
				494 => 'Request header too large',
				495 => 'SSL Certificate Error',
				496 => 'SSL Certificate Required',
				497 => 'HTTP Request Sent to HTTPS Port',
				520 => 'Web Server Returned an Unknown Error',
				521 => 'Web Server Is Down',
				522 => 'Connection Timed Out',
				523 => 'Origin Is Unreachable',
				524 => 'A Timeout Occurred',
				525 => 'SSL Handshake Failed',
				527 => 'Railgun Error',
				666 => 'Invalid URL',
				999 => 'Scanning Not Permitted',
			);

			// Bad links.
			if ( in_array( $code, self::get_bad_status_codes() ) ) {
				$type = 'broken';

				// Warnings.
			} elseif ( in_array( $code, self::get_warning_status_codes() ) ) {
				$type = 'warning';

				// Good links.
			} else {
				$type = 'good';
			}

			// Filter status.
			$status = apply_filters(
				'wpblc_status',
				array(
					'type'         => $type,
					'code'         => $code,
					'text'         => isset( $codes[ $code ] ) ? $codes[ $code ] : $error,
					'link'         => $url,
					'ID'           => $post_id,
					'is_comment'   => $is_comment,
					'detected_at'  => wpblc_convert_timezone(),
					'marked_fixed' => 'not-fixed',
				)
			);

			return $status;
		}

		/**
		 * Get all the URL Schemes to ignore in the pre-check.
		 *
		 * @since 1.0.0
		 *
		 * @return array
		 */
		public static function get_url_schemes() {
			// Official: https://www.iana.org/assignments/uri-schemes/uri-schemes.xhtml.
			$official = array( 'aaa', 'aaas', 'about', 'acap', 'acct', 'acd', 'acr', 'adiumxtra', 'adt', 'afp', 'afs', 'aim', 'amss', 'android', 'appdata', 'apt', 'ar', 'ark', 'at', 'attachment', 'aw', 'barion', 'bb', 'beshare', 'bitcoin', 'bitcoincash', 'blob', 'bolo', 'brid', 'browserext', 'cabal', 'calculator', 'callto', 'cap', 'cast', 'casts', 'chrome', 'chrome-extension', 'cid', 'coap', 'coap+tcp', 'coap+ws', 'coaps', 'coaps+tcp', 'coaps+ws', 'com-eventbrite-attendee', 'content', 'content-type', 'crid', 'cstr', 'cvs', 'dab', 'dat', 'data', 'dav', 'dhttp', 'diaspora', 'dict', 'did', 'dis', 'dlna-playcontainer', 'dlna-playsingle', 'dns', 'dntp', 'doi', 'dpp', 'drm', 'drop', 'dtmi', 'dtn', 'dvb', 'dvx', 'dweb', 'ed2k', 'eid', 'elsi', 'embedded', 'ens', 'ethereum', 'example', 'facetime', 'fax', 'feed', 'feedready', 'fido', 'file', 'filesystem', 'finger', 'first-run-pen-experience', 'fish', 'fm', 'ftp', 'fuchsia-pkg', 'geo', 'gg', 'git', 'gitoid', 'gizmoproject', 'go', 'gopher', 'graph', 'grd', 'gtalk', 'h323', 'ham', 'hcap', 'hcp', 'hxxp', 'hxxps', 'hydrazone', 'hyper', 'iax', 'icap', 'icon', 'im', 'imap', 'info', 'iotdisco', 'ipfs', 'ipn', 'ipns', 'ipp', 'ipps', 'irc', 'irc6', 'ircs', 'iris', 'iris.beep', 'iris.lwz', 'iris.xpc', 'iris.xpcs', 'isostore', 'itms', 'jabber', 'jar', 'jms', 'keyparc', 'lastfm', 'lbry', 'ldap', 'ldaps', 'leaptofrogans', 'lid', 'lorawan', 'lpa', 'lvlt', 'machineProvisioningProgressReporter', 'magnet', 'mailserver', 'mailto', 'maps', 'market', 'matrix', 'message', 'microsoft.windows.camera', 'microsoft.windows.camera.multipicker', 'microsoft.windows.camera.picker', 'mid', 'mms', 'modem', 'mongodb', 'moz', 'ms-access', 'ms-appinstaller', 'ms-browser-extension', 'ms-calculator', 'ms-drive-to', 'ms-enrollment', 'ms-excel', 'ms-eyecontrolspeech', 'ms-gamebarservices', 'ms-gamingoverlay', 'ms-getoffice', 'ms-help', 'ms-infopath', 'ms-inputapp', 'ms-launchremotedesktop', 'ms-lockscreencomponent-config', 'ms-media-stream-id', 'ms-meetnow', 'ms-mixedrealitycapture', 'ms-mobileplans', 'ms-newsandinterests', 'ms-officeapp', 'ms-people', 'ms-project', 'ms-powerpoint', 'ms-publisher', 'ms-remotedesktop', 'ms-remotedesktop-launch', 'ms-restoretabcompanion', 'ms-screenclip', 'ms-screensketch', 'ms-search', 'ms-search-repair', 'ms-secondary-screen-controller', 'ms-secondary-screen-setup', 'ms-settings', 'ms-settings-airplanemode', 'ms-settings-bluetooth', 'ms-settings-camera', 'ms-settings-cellular', 'ms-settings-cloudstorage', 'ms-settings-connectabledevices', 'ms-settings-displays-topology', 'ms-settings-emailandaccounts', 'ms-settings-language', 'ms-settings-location', 'ms-settings-lock', 'ms-settings-nfctransactions', 'ms-settings-notifications', 'ms-settings-power', 'ms-settings-privacy', 'ms-settings-proximity', 'ms-settings-screenrotation', 'ms-settings-wifi', 'ms-settings-workplace', 'ms-spd', 'ms-stickers', 'ms-sttoverlay', 'ms-transit-to', 'ms-useractivityset', 'ms-virtualtouchpad', 'ms-visio', 'ms-walk-to', 'ms-whiteboard', 'ms-whiteboard-cmd', 'ms-word', 'msnim', 'msrp', 'msrps', 'mss', 'mt', 'mtqp', 'mumble', 'mupdate', 'mvn', 'mvrp', 'mvrps', 'news', 'nfs', 'ni', 'nih', 'nntp', 'notes', 'num', 'ocf', 'oid', 'onenote', 'onenote-cmd', 'opaquelocktoken', 'openid', 'openpgp4fpr', 'otpauth', 'p1', 'pack', 'palm', 'paparazzi', 'payment', 'payto', 'pkcs11', 'platform', 'pop', 'pres', 'prospero', 'proxy', 'pwid', 'psyc', 'pttp', 'qb', 'query', 'quic-transport', 'redis', 'rediss', 'reload', 'res', 'resource', 'rmi', 'rsync', 'rtmfp', 'rtmp', 'rtsp', 'rtsps', 'rtspu', 'sarif', 'secondlife', 'secret-token', 'service', 'session', 'sftp', 'sgn', 'shc', 'shttp', 'sieve', 'simpleledger', 'simplex', 'sip', 'sips', 'skype', 'smb', 'smp', 'sms', 'smtp', 'snews', 'snmp', 'soap.beep', 'soap.beeps', 'soldat', 'spiffe', 'spotify', 'ssb', 'ssh', 'starknet', 'steam', 'stun', 'stuns', 'submit', 'svn', 'swh', 'swid', 'swidpath', 'tag', 'taler', 'teamspeak', 'tel', 'teliaeid', 'telnet', 'tftp', 'things', 'thismessage', 'tip', 'tn3270', 'tool', 'turn', 'turns', 'tv', 'udp', 'unreal', 'upt', 'urn', 'ut2004', 'uuid-in-package', 'v-event', 'vemmi', 'ventrilo', 'ves', 'videotex', 'vnc', 'view-source', 'vscode', 'vscode-insiders', 'vsls', 'w3', 'wais', 'web3', 'wcr', 'webcal', 'web+ap', 'wifi', 'wpid', 'ws', 'wss', 'wtai', 'wyciwyg', 'xcon', 'xcon-userid', 'xfire', 'xmlrpc.beep', 'xmlrpc.beeps', 'xmpp', 'xftp', 'xrcp', 'xri', 'ymsgr' );

			// Unofficial: https://en.wikipedia.org/wiki/List_of_URI_schemes.
			$unofficial = array( 'admin', 'app', 'freeplane', 'javascript', 'jdbc', 'msteams', 'ms-spd', 'odbc', 'psns', 'rdar', 's3', 'trueconf', 'slack', 'stratum', 'viber', 'zoommtg', 'zoomus' );

			// Return them.
			$all_schemes = array_unique( array_merge( $official, $unofficial ) );
			return filter_var_array( apply_filters( 'wpblc_url_schemes', $all_schemes ), FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		}

		/**
		 * Check if a link is on YouTube, if so return ID
		 * Does not check if the video is valid.
		 *
		 * @param string $link The link to check.
		 *
		 * @since 1.0.0
		 *
		 * @return boolean
		 */
		public static function is_youtube_link( $link ) {
			// The id.
			$id = false;

			// Get the host.
			$parse = parse_url( $link );
			if ( isset( $parse['host'] ) && isset( $parse['path'] ) ) {
				$host = $parse['host'];
				$path = $parse['path'];

				// Make sure it's on youtube.
				if ( $host && in_array( $host, array( 'youtube.com', 'www.youtube.com', 'youtu.be' ) ) ) {

					// if it's embeded video.
					if ( strpos( $path, '/embed/' ) !== false ) {
						$id = str_replace( '/embed/', '', $path );
						if ( strpos( $id, '&' ) !== false ) {
							$id = substr( $id, 0, strpos( $id, '&' ) );
						}

						// if it contains v.
					} elseif ( strpos( $path, '/v/' ) !== false ) {
						$id = str_replace( '/v/', '', $path );
						if ( strpos( $id, '&' ) !== false ) {
							$id = substr( $id, 0, strpos( $id, '&' ) );
						}

						// if it contains watch.
					} elseif ( strpos( $path, '/watch' ) !== false && isset( $parse['query'] ) ) {
						parse_str( $parse['query'], $queries );
						if ( isset( $queries['v'] ) ) {
							$id = $queries['v'];
						}
					}
				}
			}

			// validate if id.
			if ( $id ) {
				// Create a watch url.s.
				return 'https://www.youtube.com/watch?v=' . $id;
			}

			return false;
		}

		/**
		 * Get the link source is it internal or external.
		 *
		 * @param string $link The link to check.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public static function get_link_source( $link ) {
			$link_host = parse_url( $link, PHP_URL_HOST );
			$site_host = parse_url( get_site_url(), PHP_URL_HOST );

			return $link_host === $site_host ? 'internal' : 'external';
		}

		/**
		 * Get the bad status codes.
		 *
		 * @since 1.0.0
		 *
		 * @return array
		 */
		public static function get_bad_status_codes() {
			$default_codes = array( 666, 308, 400, 404, 408 );
			return filter_var_array( apply_filters( 'wpblc_bad_status_codes', $default_codes ), FILTER_SANITIZE_NUMBER_INT );
		}

		/**
		 * Get the warning status codes.
		 *
		 * @since 1.0.0
		 *
		 * @return array
		 */
		public static function get_warning_status_codes() {
			$default_codes = array( 0 );
			$default_codes = filter_var_array( apply_filters( 'wpblc_warning_status_codes', $default_codes ), FILTER_SANITIZE_NUMBER_INT );

			return $default_codes;
		}

		/**
		 * Convert timezone.
		 *
		 * @param string $date Default null.
		 * @param string $format Default 'F j, Y g:i A'.
		 * @param string $timezone Default null.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public static function convert_timezone( $date = null, $format = 'F j, Y g:i A', $timezone = null ) {
			// Get today as default.
			if ( is_null( $date ) ) {
				$date = gmdate( 'Y-m-d H:i:s' );
			}

			// Get the date in UTC time.
			$date = new DateTime( $date, new DateTimeZone( 'UTC' ) );

			// Get the timezone string.
			if ( ! is_null( $timezone ) ) {
				$timezone_string = $timezone;
			} else {
				$timezone_string = wp_timezone_string();
			}

			// Set the timezone to the new one.
			$date->setTimezone( new DateTimeZone( $timezone_string ) );

			// Format it the way we way.
			$new_date = $date->format( $format );

			return $new_date;
		}

		/**
		 * Send email.
		 *
		 * @param string $email_enabled Default 'off'.
		 * @param string $email_addresses Default ''.
		 * @param array  $broken_links Default [].
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public static function send_mails( $email_enabled = 'off', $email_addresses = '', $broken_links = array() ) {
			if ( 'on' === $email_enabled && ! empty( $email_addresses ) && ! empty( $broken_links ) ) {
				// Headers.
				$headers[] = 'From: ' . WPBLC_BROKEN_LINKS_CHECKER_PLUGIN_NAME . ' <' . get_bloginfo( 'admin_email' ) . '>';
				$headers[] = 'Content-Type: text/html; charset=UTF-8';

				// Subject.
				$subject = esc_html__( 'Broken Links Found', 'wpblc-broken-links-checker' );

				// Message.
				$message = 'The following broken links were found today on ' . esc_url( get_site_url() ) . ':<br><br>';

				$links_to_send = array();

				foreach ( $broken_links as $type => $link ) {
					if ( 'fixed' !== $link['marked_fixed'] ) {
						$links_to_send[] = 'URL: ' . $link['link'] . '<br>Status Code: ' . $link['code'] . ' - ' . $link['text'];
					}
				}

				// Verify before sending.
				if ( ! empty( $links_to_send ) ) {
					// Add links and footer.
					$message .= implode( '<br><br>', $links_to_send ) . '<br><br><em>- ' . WPBLC_BROKEN_LINKS_CHECKER_PLUGIN_NAME . ' Plugin</em>';

					// Try or log.
					if ( ! wp_mail( $email_addresses, $subject, $message, $headers ) ) {
						error_log( WPBLC_BROKEN_LINKS_CHECKER_PLUGIN_NAME . ' email could not be sent. Please check for issues with WP Mailer.' );
					}
				}
			}
		}
	}
}
