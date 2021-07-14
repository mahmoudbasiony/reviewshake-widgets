<?php
/**
 * Settings - Admin - Views.
 *
 * @package Reviewshake_Widgets/Templates/Admin/Vendor
 * @author Reviewshake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.

}

?>

<div class="wrap reviewshake-account-connect-wrap">
	<h2 class="connect-notice"><?php esc_html_e( 'Connect your existing Reviewshake account', 'reviewshake-widgets' ); ?></h2>

	<form method="post" class="connect-account-form" id="connect_account_form">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="account_subdomain"><?php esc_html_e( 'Subdomain', 'reviewshake-widgets' ); ?></label>
					</th>
					<td>
						<input name="subdomain" type="text" id="account_subdomain" value="" class="regular-text">
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="account_api_key"><?php esc_html_e( 'API key', 'reviewshake-widgets' ); ?></label>
					</th>
					<td>
						<input name="api_key" type="text" id="account_api_key" value="" class="regular-text">
					</td>
				</tr>
			</tbody>
		</table>

		<p class="submit">
			<input type="submit" name="connect_account" id="connect_account" class="button button-primary" value="<?php esc_html_e( 'Connect', 'reviewshake-widgets' ); ?>" />
		</p>
	</form>
</div>
