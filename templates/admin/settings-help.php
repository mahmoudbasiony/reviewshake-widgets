<?php
/**
 * Settings - Help.
 *
 * @package WPBLC_Broken_Links_Checker/Templates/Admin
 * @author Ilias Chelidonis.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$faq = array(
	array(
		'question' => __( 'What is the Deadlinks Checker plugin and how does it work?', 'wpblc-broken-links-checker' ),
		'answer'   => __( 'The Deadlinks Checker plugin is a tool designed for WordPress sites to identify and manage dead links (also known as broken links) across posts, pages, and comments. It automatically scans your website and reports any links that lead to non-existent or inaccessible pages, helping you maintain a cleaner and more user-friendly site.', 'wpblc-broken-links-checker' ),
	),
	array(
		'question' => __( 'How often does the Deadlinks Checker plugin scan my website for dead links?', 'wpblc-broken-links-checker' ),
		'answer'   => __( 'You can configure the frequency of the scans according to your needs. The plugin allows you to set up automated scans on a daily, weekly, or monthly basis. Additionally, you can manually initiate a scan at any time if you suspect there are new dead links due to recent changes on your site or external websites.', 'wpblc-broken-links-checker' ),
	),
	array(
		'question' => __( 'Can the Deadlinks Checker plugin fix the dead links it finds?', 'wpblc-broken-links-checker' ),
		'answer'   => __( 'While the Deadlinks Checker plugin efficiently identifies dead links, it does not automatically fix them. It provides a comprehensive report including the location of each dead link, allowing you to decide the most appropriate action—whether to update, redirect, or remove the link.', 'wpblc-broken-links-checker' ),
	),
	array(
		'question' => __( 'Is there a limit to the number of pages and comments the plugin can scan?', 'wpblc-broken-links-checker' ),
		'answer'   => __( 'The plugin is designed to handle websites of various sizes, from small blogs to larger corporate sites. However, performance may vary based on your hosting environment and the total content volume. For very large sites, we recommend performing scans during low-traffic periods to minimize any potential impact on site performance.', 'wpblc-broken-links-checker' ),
	),
	array(
		'question' => __( 'Is the plugin free?', 'wpblc-broken-links-checker' ),
		'answer'   => __( 'Yes, the plugin is 100% free to use.', 'wpblc-broken-links-checker' ),
	),
);

?>

<div class="wpblc-broken-links-checker-help section" id="wpblc-broken-links-checker-help">
	<div class="wpblc-broken-links-checker-help-wrap">
		<div class="wpblc-broken-links-checker-faq-container">
			<h2><?php esc_html_e( 'Frequently Asked Questions', 'wpblc-broken-links-checker' ); ?></h2>
			<?php foreach ( $faq as $index => $item ) : ?>
				<div class="wpblc-broken-links-checker-faq-item">
					<input type="checkbox" id="q<?php echo esc_attr( $index ); ?>">
					<label for="q<?php echo esc_attr( $index ); ?>"><span class="sign">+</span> <?php echo esc_html( $item['question'] ); ?></label>
					<div class="answer">
						<p><?php echo esc_html( $item['answer'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="wpblc-broken-links-checker-help-footer">
			<div class="read-more">
				<?php
				$url = 'https://wpdeadlinkschecker.silkwp.com';

				$read_more = sprintf(
					wp_kses(
						/* translators: %s: URL to the plugin site. */
						__( 'To read more about WP Deadlinks checker plugin visit our <a href="%s" target="_blank">plugin site</a>.', 'wpblc-broken-links-checker' ),
						array(
							'a' => array(
								'href'   => array(),
								'target' => array(),
							),
						)
					),
					esc_url( $url ),
				);
				echo wp_kses_post( $read_more );
				?>
			</div>
			<div class="need-help">
				<?php
				$url = 'https://wpdeadlinkschecker.silkwp.com/support';

				$need_help = sprintf(
					wp_kses(
						/* translators: %s: URL to the support page. */
						__( 'If you need any help, reach out to us at <a href="%s" target="_blank">support</a>.', 'wpblc-broken-links-checker' ),
						array(
							'a' => array(
								'href'   => array(),
								'target' => array(),
							),
						)
					),
					esc_url( $url ),
				);
				echo wp_kses_post( $need_help );
				?>
			</div>
		</div>
	</div>
</div>
