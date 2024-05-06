<?php

?>

    <input type="checkbox" id="posts" name="wpblc_broken_links_checker_settings[scope_of_scan][posts]" value="posts" <?php checked(in_array('posts', $scope_of_scan)); ?>>
    <label for="posts"><?php _e( 'Posts', 'wpblc-broken-links-checker' ); ?></label><br>
    <input type="checkbox" id="pages" name="wpblc_broken_links_checker_settings[scope_of_scan][pages]" value="pages" <?php checked(in_array('pages', $scope_of_scan)); ?>>
    <label for="pages"><?php _e( 'Pages', 'wpblc-broken-links-checker' ); ?></label><br>
    <input type="checkbox" id="comments" name="wpblc_broken_links_checker_settings[scope_of_scan][comments]" value="comments" <?php checked(in_array('comments', $scope_of_scan)); ?>>
    <label for="comments"><?php _e( 'Comments', 'wpblc-broken-links-checker' ); ?></label><br>
    <input type="checkbox" id="all" name="wpblc_broken_links_checker_settings[scope_of_scan][all]" value="all" <?php checked(in_array('all', $scope_of_scan)); ?>>
    <label for="all"><?php _e( 'All', 'wpblc-broken-links-checker' ); ?></label>