<?php

?>

    <input type="checkbox" id="posts" name="wpblc_broken_links_checker_settings[scope_of_scan][]" value="post" <?php checked(in_array('post', $scope_of_scan)); ?>>
    <label for="posts"><?php _e( 'Posts', 'wpblc-broken-links-checker' ); ?></label><br>
    <input type="checkbox" id="pages" name="wpblc_broken_links_checker_settings[scope_of_scan][]" value="page" <?php checked(in_array('page', $scope_of_scan)); ?>>
    <label for="pages"><?php _e( 'Pages', 'wpblc-broken-links-checker' ); ?></label><br>
    <input type="checkbox" id="comments" name="wpblc_broken_links_checker_settings[scope_of_scan][]" value="comment" <?php checked(in_array('comment', $scope_of_scan)); ?>>
    <label for="comments"><?php _e( 'Comments', 'wpblc-broken-links-checker' ); ?></label><br>
    <input type="checkbox" id="all" name="wpblc_broken_links_checker_settings[scope_of_scan][]" value="all" <?php checked(in_array('all', $scope_of_scan)); ?>>
    <label for="all"><?php _e( 'All', 'wpblc-broken-links-checker' ); ?></label>