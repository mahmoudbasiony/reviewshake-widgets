<?php
/**
 * Settings - Admin - Views.
 *
 * @var array $settings - The plugin settings
 *
 * @package Reviewshake_Widgets/Templates/Admin/Views
 * @author Reviewshake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Get plugin settings.
$settings = get_option( 'reviewshake_widgets_settings', array() );

/**
 * Define all reviewshake supported widget types.
 */
$widget_types = array(
	'floating' => __( 'Floating', 'reviewshake-widgets' ),
	'carousel' => __( 'Carousel', 'reviewshake-widgets' ),
	'list'     => __( 'List', 'reviewshake-widgets' ),
	'grid'     => __( 'Grid', 'reviewshake-widgets' ),
);

/**
 * Define the display mode options.
 */
$display_modes = array(
	'summary'        => __( 'Summary', 'reviewshake-widgets' ),
	'review'         => __( 'Review', 'reviewshake-widgets' ),
	'summary_review' => __( 'Summary & Review', 'reviewshake-widgets' ),
);

// The maximum star rate.
$max_stars = 5;

/**
 * Set the reviewshake supported languages.
 */
$languages = array(
	'en' => __( 'English', 'reviewshake-widgets' ),
	'dk' => __( 'Danish', 'reviewshake-widgets' ),
	'fr' => __( 'French', 'reviewshake-widgets' ),
	'de' => __( 'German', 'reviewshake-widgets' ),
	'es' => __( 'Spanish', 'reviewshake-widgets' ),
	'nl' => __( 'Dutch', 'reviewshake-widgets' ),
);

/**
 * Set the font weights
 */
$font_weights = array(
	'normal' => __( 'Regular', 'reviewshake-widgets' ),
	'bold'   => __( 'Bold', 'reviewshake-widgets' ),
);

// If isset widget ID then Get the current saved widget.
$widget_id = isset( $widget_id ) ? $widget_id : '';
$prefix    = (string) 'widget' . $widget_id;
$widget    = reviewshake_check_settings( $settings, 'widgets-v2', $prefix );

/*
 * Handles widget types display options.
 */
if ( isset( $widget ) && isset( $widget['widget_type'] ) ) {
	if ( 'floating' === $widget['widget_type'] ) {
		unset( $widget_types['carousel'], $widget_types['list'], $widget_types['grid'] );
	} else {
		unset( $widget_types['floating'] );
	}
}
?>

<div class="reviewshake-widgets-create-wrap">
	<div class="reviewshake-widgets-create-container section">
		<h2 class="headline"><?php echo esc_html( ! $widget ? __( 'Create new widget', 'reviewshake-widgets' ) : __( 'Edit widget details:', 'reviewshake-widgets' ) ); ?></h2>

		<form method="post" class="create-widget-form" id="create_widget_v2_form" data-widget-id="<?php echo esc_attr( $widget_id ); ?>">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="widget_name"><?php esc_html_e( 'Name', 'reviewshake-widgets' ); ?></label>
						</th>
						<td>
							<input name="name" type="text" id="widget_name" value="<?php echo isset( $widget['name'] ) ? esc_attr( $widget['name'] ) : ''; ?>" class="regular-text">
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="widget_type"><?php esc_html_e( 'Type', 'reviewshake-widgets' ); ?></label>
						</th>
						<td>
							<select name="widget_type" id="widget_type">
								<?php foreach ( $widget_types as $key => $widget_type ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset( $widget['widget_type'] ) ? esc_attr( $widget['widget_type'] ) : '', $key ); ?>><?php echo esc_html( stripslashes( $widget_type ) ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="widget_content"><?php esc_html_e( 'Content', 'reviewshake-widgets' ); ?></label>
						</th>
						<td>
							<select name="display_mode" id="widget_content">
								<?php foreach ( $display_modes as $key => $display_mode ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset( $widget['display_mode'] ) ? esc_attr( $widget['display_mode'] ) : '', esc_attr( $key ) ); ?>><?php echo esc_html( stripslashes( $display_mode ) ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="widget_quote"><?php esc_html_e( 'Show each review in quotes', 'reviewshake-widgets' ); ?></label>
						</th>

						<td>
							<input name="display_quote" type="checkbox" id="widget_quote" <?php echo ( ( ! isset( $widget['display_elements'] ) ) || ( isset( $widget['display_elements'] ) && isset( $widget['display_elements']['quote'] ) && $widget['display_elements']['quote'] ) ) ? 'checked="checked"' : ''; ?> class="regular-text">
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="widget_v2_min_star_rating"><?php esc_html_e( 'Minimum Star Rating', 'reviewshake-widgets' ); ?></label>
						</th>
						<td>
							<div class="widget_v2_min_star_rating" id="widget_v2_min_star_rating">
								<?php for ( $i = 1; $i <= $max_stars; $i ++ ) : ?>
									<div class="star_rating <?php echo esc_attr( ( ( 1 === $i && ! isset( $widget['min_star_rating'] ) ) ? 'selected' : ( isset( $widget['min_star_rating'] ) && $widget['min_star_rating'] === $i ) ) ? 'selected' : '' ); ?>" data-star-rate="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></div>
								<?php endfor; ?>

								<input type="hidden" class="widget_ex_star_rating" name="min_star_rating" value="<?php echo isset( $widget['min_star_rating'] ) ? esc_attr( $widget['min_star_rating'] ) : '1'; ?>"/>
							</div>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="widget_language"><?php esc_html_e( 'Language', 'reviewshake-widgets' ); ?></label>
						</th>
						<td>
							<select name="locale" id="widget_language">
								<?php foreach ( $languages as $key => $value ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset( $widget['locale'] ) ? esc_attr( $widget['locale'] ) : '', $key ); ?>><?php echo esc_html( stripslashes( $value ) ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
				</tbody>
			</table>

			<table class="form-table widget_appearance_wrap">
				<tbody>
					<tr>
						<th scope="row">
							<h2 class="headline"><?php esc_html_e( 'Appearance', 'reviewshake-widgets' ); ?></h2>
						</th>
					</tr>
					<tr>
						<th scope="row">
							<label for="widget_background_color"><?php esc_html_e( 'Background Color', 'reviewshake-widgets' ); ?></label>
						</th>
						<td>
							<input name="background_color" type="text" class="color_field color-picker" data-alpha-enabled="true" id="widget_background_color" value="<?php echo isset( $widget['colors'] ) && isset( $widget['colors']['bg'] ) ? esc_attr( $widget['colors']['bg'] ) : 'rgba(255,255,255,1)'; ?>" data-default-color="rgba(255,255,255,1)">
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="widget_review_background_color"><?php esc_html_e( 'Review Background Color', 'reviewshake-widgets' ); ?></label>
						</th>
						<td>
							<input name="review_background_color" type="text" class="color_field color-picker" data-alpha-enabled="true" id="widget_review_background_color" value="<?php echo isset( $widget['colors'] ) && isset( $widget['colors']['reviewBg'] ) ? esc_attr( $widget['colors']['reviewBg'] ) : 'rgba(255,255,255,1)'; ?>" defaul-default-color="rgba(255,255,255,1)">
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="widget_text_color"><?php esc_html_e( 'Text Color', 'reviewshake-widgets' ); ?></label>
						</th>
						<td>
							<input name="text_color" type="text" class="color_field color-picker" id="widget_text_color" data-alpha-enabled="true" value="<?php echo isset( $widget['colors'] ) && isset( $widget['colors']['text'] ) ? esc_attr( $widget['colors']['text'] ) : 'rgba(39,39,39,1)'; ?>" data-default-color="rgba(39,39,39,1)">
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="widget_links_color"><?php esc_html_e( 'Links Color', 'reviewshake-widgets' ); ?></label>
						</th>
						<td>
							<input name="link_color" type="text" class="color_field color-picker" id="widget_links_color" data-alpha-enabled="true" value="<?php echo isset( $widget['colors'] ) && isset( $widget['colors']['link'] ) ? esc_attr( $widget['colors']['link'] ) : 'rgba(33,150,243,1)'; ?>" data-default-color="rgba(33,150,243,1)">
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="widget_title_font_size"><?php esc_html_e( 'Font Size', 'reviewshake-widgets' ); ?></label>
						</th>
						<td>
							<input name="title_font_size" type="number" step="1" id="widget_title_font_size" value="<?php echo isset( $widget['fonts'], $widget['fonts']['size'] ) ? esc_attr( $widget['fonts']['size'] ) : '22'; ?>" class="small-text">
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="widget_title_font_weight"><?php esc_html_e( 'Font Weight', 'reviewshake-widgets' ); ?></label>
						</th>
						<td>
							<select name="title_font_weight" id="widget_title_font_weight">
								<?php foreach ( $font_weights as $weight => $text ) : ?>
									<option value="<?php echo esc_attr( (string) $weight ); ?>" <?php selected( isset( $widget['fonts'], $widget['fonts']['weight'] ) ? esc_attr( $widget['fonts']['weight'] ) : '', (string) $weight ); ?>><?php echo esc_html( stripslashes( $text ) ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
				</tbody>
			</table>

			<p class="submit">
				<input type="submit" name="save_preview_widget" id="save_preview_widget" class="button button-primary" value="<?php esc_attr_e( 'Save and Preview Widget', 'reviewshake-widgets' ); ?>" />
				<input type="button" disabled="disabled" name="finish_widget" id="finish_widget" onClick="window.location.href=window.location.href" class="button button-primary" value="<?php esc_attr_e( 'Finish', 'reviewshake-widgets' ); ?>" />

				<?php if ( isset( $settings['widgets-v2'] ) && is_array( $settings['widgets-v2'] ) && 0 < count( $settings['widgets-v2'] ) ) : ?>
					<input type="button" name="cancel_widget" id="cancel_widget" onClick="window.location.reload();" class="button button-primary" value="<?php esc_attr_e( 'Cancel', 'reviewshake-widgets' ); ?>" />
				<?php endif; ?>
			</p>
		</form>
	</div>

	<div class="widget_live_preview_container section" id="widget_live_preview_container">
		<h2 class="headline"><?php esc_html_e( 'Preview', 'reviewshake-widgets' ); ?></h2>
		<div class="widget_preview_loader" style="display:none;">
			<img src="<?php echo esc_url( REVIEWSHAKE_WIDGETS_ROOT_URL . 'assets/dist/images/preview-loader.svg' ); ?>" />
		</div>

		<div class="widget_live_preview" id="widget_live_preview">
			<?php
			if ( $widget && isset( $widget['snippet_html'] ) ) {
				echo html_entity_decode( esc_html( $widget['snippet_html'] ) );
			}
			?>
		</div>
	</div>
</div>
