=== Duplicate Page and Post ===
Contributors: arjunthakur, efficientninja
Tags: duplicate post, duplicate page, clone post, clone page, duplicate custom posts
Requires at least: 4.1
Tested up to: 7.1
Requires PHP: 5.6
Stable tag: 2.9.8
Version: 2.9.8
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Duplicate pages, posts and custom post types with a single click.

== Description ==

Duplicate Page and Post provides a simple way to create a clone of pages, posts and custom post types. The duplicate can be created with the post status selected in the plugin settings.

The plugin is lightweight and focused on fast, straightforward content duplication. Existing plugin settings are preserved when updating to newer versions.

= Major features of this plugin include =

* Create a clone of a particular page.
* Create a clone of a particular post.
* Create a clone of a particular custom post type (CPT).
* Option to select editor (Classic and Gutenberg).
* Option to add text before and after duplicated post and page titles.
* Option to customize the text shown for the Duplicate action.
* Option to select the status of duplicated posts.
* Option to select the redirect behavior after duplication.

== ♥️ Enjoying the Plugin? ==

[Please consider giving a review](https://wordpress.org/support/plugin/duplicate-wp-page-post/reviews/). Your feedback helps us improve the plugin and helps other WordPress users discover it.

== Installation ==

The plugin is simple to install:

 * Download duplicate-wp-page-post.zip
 * Unzip
 * Upload the duplicate-wp-page-post directory to your /wp-content/plugins directory
 * Go to the Plugins menu in WordPress and activate the plugin

== Frequently Asked Questions ==

= How do I duplicate a page or post? =

After activating the plugin, go to the Pages or Posts list. Use the **Duplicate** action for the content you want to copy. The duplicated content will be created using the status and redirect options selected in the plugin settings.

= Can I customize the title of duplicated content? =

Yes. You can use **Duplicate Post Prefix** and **Duplicate Post Suffix** to customize the title of duplicated content.

For example:

* Prefix: `Copy of` → `Copy of About Us`
* Suffix: `Copy` → `About Us -- Copy`

You can also customize the text displayed for the Duplicate action.

= Can I duplicate custom post types? =

Yes. Duplicate Page and Post supports duplicating **posts, pages, and supported custom post types**. The available content types depend on the post types registered by your WordPress site and the plugin configuration.

= Why don't I see the Duplicate button in the Gutenberg editor? =

If you are using the WordPress block editor (Gutenberg), go to **Settings → Duplicate Page and Post** and select **Gutenberg Editor** under **Select Editor**. This allows the Duplicate action to appear correctly on the edit screen.

== Upgrade Notice ==

= 2.9.8 =
Adds a title prefix setting, refreshes the plugin settings page UI, and improves settings guidance. Existing plugin settings and content are preserved.

= 2.9.7 =
Recommended update with improved security, compatibility, and WordPress.org guideline compliance. Existing plugin settings and content are preserved.

== Changelog ==

= 2.9.8 =
* Added an option to add a prefix to duplicated post and page titles.
* Refreshed the plugin settings page with a more compact WordPress-native layout, clearer descriptions, and plugin branding.

= 2.9.7 =
* Improved security through stronger input validation, nonce verification, and output escaping.
* Improved compatibility with supported WordPress versions.
* Updated the plugin text domain to match the WordPress.org plugin slug.
* Improved internationalization support with translator comments for strings containing placeholders.

= 2.9.6 =
* Security fixes for SQL injection vulnerabilities.
* Improved authorization checks when duplicating posts, pages and custom post types.
* Improved input validation and output escaping.
* Fixed an activation warning on fresh installations.
* Preserved existing plugin settings during activation and upgrades.
* Preserved existing option keys and values when saving plugin settings.
* Improved post metadata and taxonomy duplication.
* Added error handling for failed post creation.
* Improved compatibility with current WordPress versions.
* Maintained PHP 5.6-compatible plugin code syntax.

== Screenshots ==

1. **Activate the plugin** - The plugin can be activated from the WordPress Plugins page by clicking the **Activate** button.

2. **Plugin Settings** - Configure the editor, duplicate status, redirect behavior, title prefix and suffix, duplicate link text, and other plugin options from the settings page.

3. **Duplicate pages** - The **Duplicate** option is available directly from the Pages listing, making it easy to clone a page with one click.

4. **Duplicate posts** - The **Duplicate** option is available directly from the Posts listing, making it easy to clone a post with one click.