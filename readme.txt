=== Posts Recycler ===
Contributors: digitalemphasis, adsitus
Tags: post rotator, recycle, rotation
Requires at least: 4.7.3
Tested up to: 4.7.3
Stable tag: trunk
License: GPL 3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Based on digitalemphasis' "Post Rotation", "Posts Recycler" uses a Unix timestamp meta field to sort posts instead of modifying published dates. 

== Description ==
"Posts Recycler" is based on digitalemphasis' "Post Rotation" plugin https://wordpress.org/plugins/post-rotation/. The difference is that "Posts Recycler" adds a Unix timestamp custom field to all posts to sort by date, instead of modifying posts' published dates like the "Post Rotation" plugin does.

Our logic behind this approach is that modifying posts' published dates can be detrimental to search engine optimization. 

"Posts Recycler" uses pre_get_posts to modify Wordpress' main query and sort posts using the meta field and display the post categories chosen to be recycled. 

== Installation ==
1. Upload the 'posts-recycler' folder to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure the plugin through the 'Settings -> Post Recycler' administration panel.

== Frequently Asked Questions ==
Will the plugin rotate my posts after activation?
No. The plugin must be configured for rotation to work. Categories for posts to be rotated must be chosen, and the timezone the plugin will work should be set. The default timezone is your system's, which might not match your physical location.

Does this plugin work with custom posts?
Not yet. We have only tested it with regular posts.

Will this plugin work with Multisite?
Not yet.

Is this plugin available in other languages?
The plugin has been translated to Spanish (Español, es_ES). If you would like to submit a translation, please do so.

== Screenshots ==
1. Post Recycler administration

== Changelog ==

== Upgrade Notice ==
