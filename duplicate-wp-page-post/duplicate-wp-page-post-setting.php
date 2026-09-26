<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$opt = get_option( 'dpp_wpp_page_options', array() );
if ( ! is_array( $opt ) ) {
	$opt = array();
}

$defaults = array(
	'dpp_post_status'     => 'draft',
	'dpp_post_redirect'   => 'to_list',
	'dpp_post_suffix'     => '',
	'dpp_post_prefix'     => '',
	'dpp_posteditor'      => 'classic',
	'dpp_post_link_title' => '',
);
$opt = wp_parse_args( $opt, $defaults );

$settings_saved = false;

if ( isset( $_POST['submit_dpp_wpp_page'] ) ) {
	if ( ! check_admin_referer( 'dpp_page_action', 'dpp_nonce_field', false ) ) {
		wp_die( esc_html__( 'Security check failed. Please try again.', 'duplicate-wp-page-post' ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to change these settings.', 'duplicate-wp-page-post' ) );
	}

	$allowed_statuses = array( 'draft', 'publish', 'private', 'pending' );
	$post_status      = isset( $_POST['dpp_post_status'] ) && is_string( $_POST['dpp_post_status'] ) ? sanitize_key( stripslashes_deep( $_POST['dpp_post_status'] ) ) : $defaults['dpp_post_status'];
	$post_redirect    = isset( $_POST['dpp_post_redirect'] ) && is_string( $_POST['dpp_post_redirect'] ) ? sanitize_key( stripslashes_deep( $_POST['dpp_post_redirect'] ) ) : $defaults['dpp_post_redirect'];
	$post_editor      = isset( $_POST['dpp_posteditor'] ) && is_string( $_POST['dpp_posteditor'] ) ? sanitize_key( stripslashes_deep( $_POST['dpp_posteditor'] ) ) : $defaults['dpp_posteditor'];
	$post_prefix      = isset( $_POST['dpp_post_prefix'] ) && is_string( $_POST['dpp_post_prefix'] ) ? sanitize_text_field( stripslashes_deep( $_POST['dpp_post_prefix'] ) ) : $opt['dpp_post_prefix'];
	$post_suffix      = isset( $_POST['dpp_post_suffix'] ) && is_string( $_POST['dpp_post_suffix'] ) ? sanitize_text_field( stripslashes_deep( $_POST['dpp_post_suffix'] ) ) : $opt['dpp_post_suffix'];
	$link_title       = isset( $_POST['dpp_post_link_title'] ) && is_string( $_POST['dpp_post_link_title'] ) ? sanitize_text_field( stripslashes_deep( $_POST['dpp_post_link_title'] ) ) : $opt['dpp_post_link_title'];

	if ( ! in_array( $post_status, $allowed_statuses, true ) ) {
		$post_status = $defaults['dpp_post_status'];
	}
	if ( ! in_array( $post_redirect, array( 'to_list', 'to_page' ), true ) ) {
		$post_redirect = $defaults['dpp_post_redirect'];
	}
	if ( ! in_array( $post_editor, array( 'classic', 'gutenberg' ), true ) ) {
		$post_editor = $defaults['dpp_posteditor'];
	}

	/* Update only known settings; preserve every existing/unknown option. */
	$opt['dpp_post_status']     = $post_status;
	$opt['dpp_post_redirect']   = $post_redirect;
	$opt['dpp_posteditor']      = $post_editor;
	$opt['dpp_post_prefix']     = $post_prefix;
	$opt['dpp_post_suffix']     = $post_suffix;
	$opt['dpp_post_link_title'] = $link_title;

	update_option( 'dpp_wpp_page_options', $opt );
	$settings_saved = true;
}
?>
<div class="wrap dpp_page_settings">
	<div class="dpp-settings-form">
		<div class="dpp-settings-header">
			<div class="dpp-settings-brand">
				<span class="dpp-settings-icon" aria-hidden="true"></span>
				<div>
					<h1><?php esc_html_e( 'Duplicate Page and Post Settings', 'duplicate-wp-page-post' ); ?></h1>
					<p><?php esc_html_e( 'Configure how pages, posts, and custom post types are duplicated.', 'duplicate-wp-page-post' ); ?></p>
				</div>
			</div>
			<div class="dpp-settings-meta">
				<span class="dpp-settings-version"><?php esc_html_e( 'Version 2.9.8', 'duplicate-wp-page-post' ); ?></span>
				<a class="dpp-settings-review" href="https://wordpress.org/support/plugin/duplicate-wp-page-post/reviews/#new-post" target="_blank" rel="noopener noreferrer"><span class="dpp-review-heart" aria-hidden="true">♥</span> <?php esc_html_e( 'Enjoying this plugin? Rate it on WordPress.org', 'duplicate-wp-page-post' ); ?></a>
			</div>
		</div>

		<?php if ( $settings_saved ) : ?>
			<div id="dpp-settings-notice" class="dpp-settings-notice dpp-settings-notice-success" role="status" aria-live="polite">
				<span class="dpp-settings-notice-icon" aria-hidden="true">&#10003;</span>
				<span class="dpp-settings-notice-text"><?php esc_html_e( 'Changes saved.', 'duplicate-wp-page-post' ); ?></span>
				<button type="button" class="dpp-settings-notice-dismiss" aria-label="<?php esc_attr_e( 'Dismiss this notice.', 'duplicate-wp-page-post' ); ?>">&times;</button>
			</div>
		<?php endif; ?>

		<form class="dpp-settings-body" action="" method="post" name="dpp_wpp_page_form">
			<?php wp_nonce_field( 'dpp_page_action', 'dpp_nonce_field' ); ?>
			<table class="form-table">
				<tbody>
				<tr>
					<th scope="row"><label for="dpp_posteditor">Select Editor<br><em><?php esc_html_e( 'Default: Classic Editor', 'duplicate-wp-page-post' ); ?></em></label></th>
					<td>
						<select id="dpp_posteditor" name="dpp_posteditor">
							<option value="classic" <?php selected( $opt['dpp_posteditor'], 'classic' ); ?>><?php esc_html_e( 'Classic Editor', 'duplicate-wp-page-post' ); ?></option>
							<option value="gutenberg" <?php selected( $opt['dpp_posteditor'], 'gutenberg' ); ?>><?php esc_html_e( 'Gutenberg Editor', 'duplicate-wp-page-post' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Select the editor used on your site. If you are using Gutenberg, select Gutenberg Editor so the Duplicate button appears on the edit screen.', 'duplicate-wp-page-post' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dpp_post_status">Post Status<br><em><?php esc_html_e( 'Default: Draft', 'duplicate-wp-page-post' ); ?></em></label></th>
					<td>
						<select id="dpp_post_status" name="dpp_post_status">
							<option value="draft" <?php selected( $opt['dpp_post_status'], 'draft' ); ?>><?php esc_html_e( 'Draft', 'duplicate-wp-page-post' ); ?></option>
							<option value="publish" <?php selected( $opt['dpp_post_status'], 'publish' ); ?>><?php esc_html_e( 'Publish', 'duplicate-wp-page-post' ); ?></option>
							<option value="private" <?php selected( $opt['dpp_post_status'], 'private' ); ?>><?php esc_html_e( 'Private', 'duplicate-wp-page-post' ); ?></option>
							<option value="pending" <?php selected( $opt['dpp_post_status'], 'pending' ); ?>><?php esc_html_e( 'Pending', 'duplicate-wp-page-post' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Select the post status to assign to duplicated content.', 'duplicate-wp-page-post' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dpp_post_redirect"><?php esc_html_e( 'Redirect After Duplication', 'duplicate-wp-page-post' ); ?><br><em><?php esc_html_e( 'Default: To current list', 'duplicate-wp-page-post' ); ?></em></label></th>
					<td>
						<select id="dpp_post_redirect" name="dpp_post_redirect">
							<option value="to_list" <?php selected( $opt['dpp_post_redirect'], 'to_list' ); ?>><?php esc_html_e( 'All Post List', 'duplicate-wp-page-post' ); ?></option>
							<option value="to_page" <?php selected( $opt['dpp_post_redirect'], 'to_page' ); ?>><?php esc_html_e( 'Direct Edit', 'duplicate-wp-page-post' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Select where to redirect after clicking Duplicate.', 'duplicate-wp-page-post' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dpp_post_prefix">Duplicate Post Prefix<br><em><?php esc_html_e( 'Default: Empty', 'duplicate-wp-page-post' ); ?></em></label></th>
					<td>
						<input type="text" class="regular-text" value="<?php echo esc_attr( $opt['dpp_post_prefix'] ); ?>" id="dpp_post_prefix" name="dpp_post_prefix">
						<p class="description"><?php esc_html_e( 'Text to add before the original title. Example: “Copy of” → “Copy of About Us”.', 'duplicate-wp-page-post' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dpp_post_suffix">Duplicate Post Suffix<br><em><?php esc_html_e( 'Default: Empty', 'duplicate-wp-page-post' ); ?></em></label></th>
					<td>
						<input type="text" class="regular-text" value="<?php echo esc_attr( $opt['dpp_post_suffix'] ); ?>" id="dpp_post_suffix" name="dpp_post_suffix">
						<p class="description"><?php esc_html_e( 'Text to add after the original title. Example: “Copy” → “About Us -- Copy”.', 'duplicate-wp-page-post' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dpp_post_link_title">Duplicate Link Text<br><em><?php esc_html_e( 'Default: Duplicate', 'duplicate-wp-page-post' ); ?></em></label></th>
					<td>
						<input type="text" class="regular-text" value="<?php echo esc_attr( $opt['dpp_post_link_title'] ); ?>" id="dpp_post_link_title" name="dpp_post_link_title">
						<p class="description"><?php esc_html_e( 'Text shown for the Duplicate action instead of the default (Duplicate). Suggestions: Duplicate, Duplicate This, Duplicate Now, Clone This, Clone Now.', 'duplicate-wp-page-post' ); ?></p>
					</td>
				</tr>
				</tbody>
			</table>
			<p class="submit"><input type="submit" value="<?php echo esc_attr__( 'Save Settings', 'duplicate-wp-page-post' ); ?>" class="button button-primary" id="submit" name="submit_dpp_wpp_page"></p>
		</form>
	</div>
</div>
