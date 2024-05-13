<?php
/**
 * Settings.
 *
 * @package WPBLC_Broken_Links_Checker/Templates/Admin
 * @author  Reviewshake
 */



if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Available tabs.
$plugin_tabs = array( 'general', 'scan' );

// Current tab.
$plugin_tab = isset( $_GET['tab'] ) && in_array( $_GET['tab'], $plugin_tabs, true ) ? sanitize_text_field( $_GET['tab'] ) : 'general';


if ( 'general' === $plugin_tab ) {

}

//$response = wp_remote_retrieve_body( wp_remote_post( 'https://www.youtube.com/watch?app=desktop&v=LZkuYsFcLy8&t=0s' ) );

//var_dump(strpos( $response, 'Video unavailable' ) !== false || strpos( $response, 'Something went wrong' ) !== false);
//var_dump(is_wp_error( wp_remote_head( 'https://www.youtube.com/watch?app=desktop&v=LZkuYsFcLy8&t=0s' )) );
//var_dump( wp_remote_retrieve_body( wp_remote_post( 'https://www.youtube.com/watch?app=desktop&v=LZkuYsFcLy8&t=0s' ) ) );
//var_dump(is_wp_error( wp_safe_remote_get('https://www.youtube.com/watch?app=desktop&v=LZkuYsFcLy8&t=0s')));
//var_dump($response);
//var_dump(WPBLC_Broken_Links_Checker_Utilities::getLinksFromPages( array( 'page', 'post' ) ));

//var_dump(WPBLC_Broken_Links_Checker_Utilities::get_response_code( wp_remote_head( 'https://www.youtube.com/watch?app=desktop&v=LZkuYsFcLy8&t=0s')));
//var_dump(wp_remote_head( 'https://youtubesfs.com' ));

//WPBLC_Broken_Links_Checker_Utilities::get_content_to_scan();

// $options = get_option( 'wpblc_broken_links_checker_links', array() );
// echo '<pre>';
// var_dump($options);
// echo '</pre>';

// $marked_fixed = isset( $options['fixed'] ) ? $options['fixed'] : array();
// $links_column = array_column($marked_fixed, 'link');
// $position = array_search('https://infinuexacafs.com', $links_column);

// echo '<pre>';
// var_dump($marked_fixed);
// echo '</pre>';

// var_dump($links_column);
// var_dump($position);
// var_dump($links_column);

// var_dump(in_array( 'https://infinuexacafs.com', $marked_fixed ));
// $search_link = 'https://cocanjnknon.net/user/infinue/portfolio';
// $result = array_filter($marked_fixed, function($link, $index) use ($search_link) {
//     return $link['link'] == $search_link;
// }, ARRAY_FILTER_USE_BOTH);

// $index = key($result);
// var_dump($result);
// var_dump($index);

// $broken = $options['broken'] ?? array();
// $fixed = $options['fixed'] ?? array();

// //var_dump($fixed);


$settings = get_option( 'wpblc_broken_links_checker_settings', array() );
$number_of_links = isset( $settings['number_of_links'] ) ? $settings['number_of_links'] : 'all';
$set_number = isset( $settings['set_links_number'] ) ? $settings['set_links_number'] : 0;
$scope_of_scan = isset( $settings['scope_of_scan'] ) ? $settings['scope_of_scan'] : ['all'];
$exclusion_urls = isset( $settings['exclusion_urls'] ) ? $settings['exclusion_urls'] : '';
$links_to_exclude = explode( "\n", $exclusion_urls );
$links_to_exclude = array_map( function( $link ) {
	return rtrim( trim( $link ), '/' );
}, $links_to_exclude );

// var_dump( $links_to_exclude );
// var_dump( in_array( rtrim('https://cocanjnknon.net/user/infinue/portfolio/', '/'), $links_to_exclude ) );
// echo '<pre>';
// var_dump($test);
// echo '</pre>';

// Get the broken links.
$links = get_option( 'wpblc_broken_links_checker_links', array() );
$broken_links = isset($links['broken']) ? $links['broken'] : array();

// echo '<pre>';
// var_dump($broken_links);
// echo '</pre>';
?>

<div class="wrap wpblc-broken-links-checker" id="wpblc-broken-links-checker">
	<nav class="nav-tab-wrapper wpblc-nav-tab-wrapper">
		<a href="admin.php?page=wpblc-broken-links-checker&tab=general" class="nav-tab <?php echo 'general' === $plugin_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Settings', 'wpblc-broken-links-checker' ); ?></a>
		<a href="admin.php?page=wpblc-broken-links-checker&tab=scan" class="nav-tab <?php echo 'scan' === $plugin_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Scan', 'wpblc-broken-links-checker' ); ?></a>
	</nav>

	<div class="wpblc-broken-links-checker-inside-tabs">
		<div class="inside tab tab-content <?php echo esc_attr( $plugin_tab ); ?>" id="wpblc-tab-<?php echo esc_attr( $plugin_tab ); ?>">
			<?php require_once 'settings-' . $plugin_tab . '.php'; ?>
		</div>
	</div>
</div>
