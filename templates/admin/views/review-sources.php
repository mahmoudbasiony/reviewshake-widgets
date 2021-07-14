<?php
/**
 * Settings - Admin - Views.
 *
 * @var string $source_id     The review source prefixed ID string
 * @var array  $review_source The review source data
 *
 * @package Reviewshake_Widgets/Templates/Admin/Vendor
 * @author Reviewshake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<tr valign="top" class="review-sources-row" data-review-source-id="<?php echo isset( $source_id ) ? esc_attr( $source_id ) : 0; ?>">
	<th class="review-sources-source-column">
		<select name="review_sources" class="review-sources" id="review-sources" disabled>
			<option value="<?php echo isset( $review_source['source_name'] ) ? esc_attr( stripslashes( $review_source['source_name'] ) ) : ''; ?>"><?php echo esc_html( stripslashes( ucfirst( $review_source['source_name'] ) ) ); ?></option>
		</select>
	</th>

	<th class="review-sources-url-column">
		<p class="review-sources-url" id="review-sources-url">
			<a><?php echo isset( $review_source['source_url'] ) ? esc_attr( $review_source['source_url'] ) : ''; ?></a>
		</p>
	</th>

	<th class="review-sources-add-column">
		<input type="button" class="button button-primary delete-review-source" value="<?php esc_html_e( 'Delete', 'reviewshake-widgets' ); ?>" />
	</th>
</tr>
