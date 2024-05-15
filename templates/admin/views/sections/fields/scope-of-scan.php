<?php
/**
 * Settings - Admin - Views - Sections - Fields.
 *
 * @package WPBLC_Broken_Links_Checker/Templates/Admin/Views/Sections/Fields
 * @author Ilias Chelidonis.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<input type="checkbox" id="posts" name="wpblc_broken_links_checker_settings[scope_of_scan][]" value="post" <?php checked(in_array('post', $scope_of_scan)); ?>>
<label for="posts"><?php esc_html_e( 'Posts', 'wpblc-broken-links-checker' ); ?></label><br>
<input type="checkbox" id="pages" name="wpblc_broken_links_checker_settings[scope_of_scan][]" value="page" <?php checked(in_array('page', $scope_of_scan)); ?>>
<label for="pages"><?php esc_html_e( 'Pages', 'wpblc-broken-links-checker' ); ?></label><br>
<input type="checkbox" id="comments" name="wpblc_broken_links_checker_settings[scope_of_scan][]" value="comment" <?php checked(in_array('comment', $scope_of_scan)); ?>>
<label for="comments"><?php esc_html_e( 'Comments', 'wpblc-broken-links-checker' ); ?></label><br>
<input type="checkbox" id="all" name="wpblc_broken_links_checker_settings[scope_of_scan][]" value="all" <?php checked(in_array('all', $scope_of_scan)); ?>>
<label for="all"><?php esc_html_e( 'All', 'wpblc-broken-links-checker' ); ?></label>