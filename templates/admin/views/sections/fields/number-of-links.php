<?php

?>

    <input type="radio" id="all_links" name="wpblc_broken_links_checker_settings[number_of_links]" value="all" <?php checked($number_of_links, 'all'); ?>>
    <label for="all_links">All</label><br>
    <input type="radio" id="set_number" name="wpblc_broken_links_checker_settings[number_of_links]" value="set_number" <?php checked($number_of_links, 'set_number'); ?>>
    <label for="set_number">Set number</label>