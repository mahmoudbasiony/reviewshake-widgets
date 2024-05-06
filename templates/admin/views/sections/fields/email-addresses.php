<?php
/**
 * 
 */
?>

    <div style="margin-top: 10px;">
        <input type="text" id="email_addresses" name="wpblc_broken_links_checker_settings[email_addresses]" value="<?php echo esc_attr($email_addresses); ?>" <?php echo ($email_notifications === 'on' ? '' : 'disabled'); ?>>
        <p class="description"><?php _e( 'Enter email addresses, separated by commas.', '' ); ?></p>
    </div>