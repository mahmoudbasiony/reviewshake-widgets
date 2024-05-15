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

<input type="number" id="number_of_links" name="wpblc_broken_links_checker_settings[set_links_number]" value="<?php echo esc_attr( $set_number ); ?>" <?php echo ( 'set_number' === $number_of_links ? '' : 'disabled' ); ?>>
