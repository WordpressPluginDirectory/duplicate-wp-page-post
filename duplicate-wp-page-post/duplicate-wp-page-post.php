<?php
/*
Plugin Name: Duplicate Page and Post
Plugin URI: https://wordpress.org/plugins/duplicate-wp-page-post/
Description: Duplicate pages, posts and custom post types with a single click.
Author: Arjun Thakur
Author URI: https://profiles.wordpress.org/arjunthakur#content-plugins
Version: 2.9.8
Requires at least: 4.1
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: duplicate-wp-page-post
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! defined( 'DPP_BASE_NAME' ) ) {
	define( 'DPP_BASE_NAME', plugin_basename( __FILE__ ) );
}
if ( ! class_exists( 'dpp_wpp_page' ) ):
	class dpp_wpp_page {

		public function __construct() {
			$opt = get_option( 'dpp_wpp_page_options', array() );
			register_activation_hook( __FILE__, array( &$this, 'dpp_wpp_page_install' ) );
			add_action( 'admin_menu', array( &$this, 'dpp_page_options_page' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'dpp_settings_styles' ) );
			/* Use the plugin-specific hook late so other plugins (such as Plugin Check) cannot remove our link. */
			add_filter( 'plugin_action_links_' . DPP_BASE_NAME, array( &$this, 'dpp_settings_link' ), 999, 1 );
			add_action( 'admin_action_dt_dpp_post_as_draft', array( &$this, 'dt_dpp_post_as_draft' ) );
			add_filter( 'post_row_actions', array( &$this, 'dt_dpp_post_link' ), 10, 2 );
			add_filter( 'page_row_actions', array( &$this, 'dt_dpp_post_link' ), 10, 2 );
			if ( is_array( $opt ) && isset( $opt['dpp_posteditor'] ) && 'gutenberg' === $opt['dpp_posteditor'] ) {
				add_action( 'admin_head', array( &$this, 'dpp_wpp_button_guten' ) );
			} else {
				add_action( 'post_submitbox_misc_actions', array( &$this, 'dpp_wpp_page_custom_button' ) );
			}
			add_action( 'wp_before_admin_bar_render', array( &$this, 'dpp_wpp_page_admin_bar_link' ) );
		}

		/* Activation plugin hook. Preserve existing settings and only add missing defaults. */
		public function dpp_wpp_page_install() {
			$defaultsettings = array(
				'dpp_post_status'     => 'draft',
				'dpp_post_redirect'   => 'to_list',
				'dpp_post_suffix'     => '',
				'dpp_post_prefix'     => '',
				'dpp_posteditor'      => 'classic',
				'dpp_post_link_title' => '',
			);
			$opt = get_option( 'dpp_wpp_page_options', false );

			if ( false === $opt || ! is_array( $opt ) ) {
				add_option( 'dpp_wpp_page_options', $defaultsettings );
				return;
			}

			$updated = false;
			foreach ( $defaultsettings as $key => $value ) {
				if ( ! array_key_exists( $key, $opt ) ) {
					$opt[ $key ] = $value;
					$updated = true;
				}
			}

			if ( $updated ) {
				update_option( 'dpp_wpp_page_options', $opt );
			}
		}

		/* Page Title and Dashboard Menu (Setting options) */
		public function dpp_page_options_page() {
			add_options_page(
			__( 'Duplicate Page and Post', 'duplicate-wp-page-post' ),
			__( 'Duplicate Page and Post', 'duplicate-wp-page-post' ),
			'manage_options',
			'dpp_page_settings',
			array( &$this, 'dpp_page_settings' )
			);
		}

		/* Load settings assets only on this plugin's settings screen. */
		public function dpp_settings_styles( $hook_suffix ) {
			if ( 'settings_page_dpp_page_settings' !== $hook_suffix ) {
				return;
			}

			wp_enqueue_style(
				'dpp-settings',
				plugins_url( 'assets/css/settings.css', __FILE__ ),
				array(),
				'2.9.8'
			);

			wp_enqueue_script(
				'dpp-settings',
				plugins_url( 'assets/js/settings.js', __FILE__ ),
				array(),
				'2.9.8',
				true
			);
		}

		/* Include plugin setting file */
		public function dpp_page_settings() {
			if ( current_user_can( 'manage_options' ) ) {
				include dirname( __FILE__ ) . '/duplicate-wp-page-post-setting.php';
			}
		}

		/* Duplicate post/page/custom post. */
		public function dt_dpp_post_as_draft() {
			$post_id = isset( $_GET['post'] ) && is_scalar( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
			$action = isset( $_GET['action'] ) && is_string( $_GET['action'] ) ? sanitize_key( stripslashes_deep( $_GET['action'] ) ) : '';

			if ( 'dt_dpp_post_as_draft' !== $action || ! $post_id ) {
				wp_die( esc_html__( 'No post!', 'duplicate-wp-page-post' ) );
			}

			if ( ! check_admin_referer( 'dt-duplicate-page-' . $post_id, 'nonce', false ) ) {
				wp_die( esc_html__( 'Security check issue, Please try again.', 'duplicate-wp-page-post' ) );
			}

			$post = get_post( $post_id );
			if ( ! $post ) {
				wp_die( esc_html__( 'Error! Post not found.', 'duplicate-wp-page-post' ) );
			}

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				wp_die( esc_html__( 'You are not allowed to duplicate this post.', 'duplicate-wp-page-post' ) );
			}

			$opt = get_option( 'dpp_wpp_page_options', array() );
			if ( ! is_array( $opt ) ) {
				$opt = array();
			}

			$prefix = ! empty( $opt['dpp_post_prefix'] ) ? sanitize_text_field( $opt['dpp_post_prefix'] ) : '';
			$suffix = ! empty( $opt['dpp_post_suffix'] ) ? ' -- ' . sanitize_text_field( $opt['dpp_post_suffix'] ) : '';
			if ( '' !== $prefix ) {
				$prefix = rtrim( $prefix ) . ' ';
			}

			$allowed_statuses = array( 'draft', 'publish', 'private', 'pending' );
			$post_status     = isset( $opt['dpp_post_status'] ) ? sanitize_key( $opt['dpp_post_status'] ) : 'draft';
			if ( ! in_array( $post_status, $allowed_statuses, true ) ) {
				$post_status = 'draft';
			}

			$redirectit = isset( $opt['dpp_post_redirect'] ) ? sanitize_key( $opt['dpp_post_redirect'] ) : 'to_list';
			if ( ! in_array( $redirectit, array( 'to_list', 'to_page' ), true ) ) {
				$redirectit = 'to_list';
			}

			$current_user    = wp_get_current_user();
			$new_post_author = $current_user->ID;

			$args = array(
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_author'    => $new_post_author,
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_parent'    => $post->post_parent,
				'post_password'  => $post->post_password,
				'post_status'    => $post_status,
				'post_title'     => $prefix . $post->post_title . $suffix,
				'post_type'      => $post->post_type,
				'to_ping'        => $post->to_ping,
				'menu_order'     => $post->menu_order,
			);

			/* Prepare post data for the core API while keeping compatibility with WordPress 3.5+. */
			$slashed_args = $args;
			foreach ( $slashed_args as $key => $value ) {
				if ( is_string( $value ) ) {
					$slashed_args[ $key ] = addslashes( $value );
				}
			}

			$new_post_id = wp_insert_post( $slashed_args, true );
			if ( is_wp_error( $new_post_id ) ) {
				wp_die( esc_html__( 'Error! Post creation failed.', 'duplicate-wp-page-post' ) );
			}

			/* Preserve taxonomy relationships. */
			$taxonomies = get_object_taxonomies( $post->post_type );
			if ( ! empty( $taxonomies ) && is_array( $taxonomies ) ) {
				foreach ( $taxonomies as $taxonomy ) {
					$post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
					if ( is_wp_error( $post_terms ) ) {
						continue;
					}
					wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
				}
			}

			/* Preserve all existing post metadata without constructing raw SQL. */
			$post_meta_infos = get_post_meta( $post_id );
			if ( ! empty( $post_meta_infos ) && is_array( $post_meta_infos ) ) {
				foreach ( $post_meta_infos as $meta_key => $meta_values ) {
					if ( ! is_array( $meta_values ) ) {
						$meta_values = array( $meta_values );
					}
					foreach ( $meta_values as $meta_value ) {
						/* add_post_meta() handles serialization and database escaping. */
						add_post_meta( $new_post_id, $meta_key, $meta_value );
					}
				}
			}

			$returnpage = '';
			if ( 'post' !== $post->post_type ) {
				$returnpage = '?post_type=' . rawurlencode( $post->post_type );
			}

			if ( 'to_page' === $redirectit ) {
				$redirect_url = admin_url( 'post.php?action=edit&post=' . absint( $new_post_id ) );
			} else {
				$redirect_url = admin_url( 'edit.php' . $returnpage );
			}

			wp_safe_redirect( $redirect_url );
			exit;
		}

		/* Add link to action. */
		public function dt_dpp_post_link( $actions, $post ) {
			if ( ! current_user_can( 'edit_post', $post->ID ) ) {
				return $actions;
			}

			$opt = get_option( 'dpp_wpp_page_options', array() );
			if ( ! is_array( $opt ) ) {
				$opt = array();
			}
			$link_title  = ! empty( $opt['dpp_post_link_title'] ) ? $opt['dpp_post_link_title'] : 'Duplicate';
			$post_status = ! empty( $opt['dpp_post_status'] ) ? $opt['dpp_post_status'] : 'draft';
			$url         = add_query_arg(
				array(
					'action' => 'dt_dpp_post_as_draft',
					'post'   => absint( $post->ID ),
					'nonce'  => wp_create_nonce( 'dt-duplicate-page-' . $post->ID ),
				),
				admin_url( 'admin.php' )
			);

			$clone_title = sprintf(
				/* translators: %s: selected duplicate post status. */
				__( 'Clone this as %s', 'duplicate-wp-page-post' ),
				$post_status
			);
			$actions['dpp'] = '<a href="' . esc_url( $url ) . '" title="' . esc_attr( $clone_title ) . '" rel="permalink">' . esc_html( $link_title ) . '</a>';

			return $actions;
		}

		/* Add link to edit Post. */
		public function dpp_wpp_page_custom_button() {
			global $post;
			if ( ! $post || ! current_user_can( 'edit_post', $post->ID ) ) {
				return;
			}

			$opt = get_option( 'dpp_wpp_page_options', array() );
			if ( ! is_array( $opt ) ) {
				$opt = array();
			}
			$link_title  = ! empty( $opt['dpp_post_link_title'] ) ? $opt['dpp_post_link_title'] : 'Duplicate';
			$post_status = ! empty( $opt['dpp_post_status'] ) ? $opt['dpp_post_status'] : 'draft';
			$url         = add_query_arg(
				array(
					'action' => 'dt_dpp_post_as_draft',
					'post'   => absint( $post->ID ),
					'nonce'  => wp_create_nonce( 'dt-duplicate-page-' . $post->ID ),
				),
				admin_url( 'admin.php' )
			);

			$html  = '<div id="major-publishing-actions">';
			$html .= '<div id="export-action">';
			$duplicate_title = sprintf(
				/* translators: %s: selected duplicate post status. */
				__( 'Duplicate this as %s', 'duplicate-wp-page-post' ),
				$post_status
			);
			$html .= '<a href="' . esc_url( $url ) . '" title="' . esc_attr( $duplicate_title ) . '" rel="permalink">' . esc_html( $link_title ) . '</a>';
			$html .= '</div>';
			$html .= '</div>';
			echo wp_kses_post( $html );
		}

		/* Add the duplicate link to edit screen - Gutenberg. */
		public function dpp_wpp_button_guten() {
			global $post;
			if ( ! $post || ! current_user_can( 'edit_post', $post->ID ) ) {
				return;
			}

			$opt = get_option( 'dpp_wpp_page_options', array() );
			if ( ! is_array( $opt ) ) {
				$opt = array();
			}
			$post_status = ! empty( $opt['dpp_post_status'] ) ? $opt['dpp_post_status'] : 'draft';
			$nonce       = wp_create_nonce( 'dt-duplicate-page-' . $post->ID );
			$post_id     = absint( $post->ID );
			$duplicate_url = add_query_arg(
				array(
					'action' => 'dt_dpp_post_as_draft',
					'post'   => $post_id,
					'nonce'  => $nonce,
				),
				admin_url( 'admin.php' )
			);
			?>
			<style>
				.link_gutenberg { text-align: center; margin-top: 15px; }
				.link_gutenberg a { text-decoration: none; display: block; height: 40px; line-height: 28px; padding: 3px 12px 2px; background: #0073AA; border-radius: 3px; border-width: 1px; border-style: solid; color: #ffffff; font-size: 16px; }
				.link_gutenberg a:hover { background: #23282D; border-color: #23282D; }
			</style>
			<script>
			jQuery(function ($) {
			var dppDuplicateLink = <?php
			$duplicate_title = sprintf(
				/* translators: %s: selected duplicate post status. */
				__( 'Duplicate this as %s', 'duplicate-wp-page-post' ),
				$post_status
			);
			echo wp_json_encode( '<div class="link_gutenberg"><a href="' . esc_url( $duplicate_url ) . '" title="' . esc_attr( $duplicate_title ) . '">' . esc_html__( 'Duplicate', 'duplicate-wp-page-post' ) . '</a></div>' );
			?>;
				$('.edit-post-post-status').append(dppDuplicateLink);
			});
			</script>
			<?php
		}

		/* Click here to clone Admin Bar. */
		public function dpp_wpp_page_admin_bar_link() {
			global $wp_admin_bar;
			$current_object = get_queried_object();
			if ( empty( $current_object ) || empty( $current_object->ID ) || empty( $current_object->post_type ) ) {
				return;
			}

			if ( ! current_user_can( 'edit_post', $current_object->ID ) ) {
				return;
			}

			$post_type_object = get_post_type_object( $current_object->post_type );
			if ( ! $post_type_object || ( ! $post_type_object->show_ui && 'attachment' !== $current_object->post_type ) ) {
				return;
			}

			$opt = get_option( 'dpp_wpp_page_options', array() );
			if ( ! is_array( $opt ) ) {
				$opt = array();
			}
			$post_status = ! empty( $opt['dpp_post_status'] ) ? $opt['dpp_post_status'] : 'draft';
			$url = add_query_arg(
				array(
					'action' => 'dt_dpp_post_as_draft',
					'post'   => absint( $current_object->ID ),
					'nonce'  => wp_create_nonce( 'dt-duplicate-page-' . $current_object->ID ),
				),
				admin_url( 'admin.php' )
			);

			$clone_title = sprintf(
				/* translators: %s: selected duplicate post status. */
				__( 'Clone this as %s', 'duplicate-wp-page-post' ),
				$post_status
			);

			$wp_admin_bar->add_menu( array(
				'parent' => 'edit',
				'id'     => 'dpp_this',
				'title'  => esc_html( $clone_title ),
				'href'   => esc_url( $url ),
			) );
		}

		/* Legacy helper retained for backward compatibility, now using a safe redirect. */
		public static function dp_redirect( $url ) {
			wp_safe_redirect( $url );
			exit;
		}

		/* Plugin settings page link. */
		public function dpp_settings_link( $links ) {
			foreach ( $links as $link ) {
				if ( false !== strpos( $link, 'page=dpp_page_settings' ) ) {
					return $links;
				}
			}

			$settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=dpp_page_settings' ) ) . '">' . esc_html__( 'Settings', 'duplicate-wp-page-post' ) . '</a>';

			/* array_unshift() puts Settings before Deactivate and any other action links. */
			array_unshift( $links, $settings_link );

			return $links;
		}
	}
	new dpp_wpp_page();
endif;
