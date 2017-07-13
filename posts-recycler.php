<?php 
/*
Plugin Name: Posts Recycler
Plugin URI:  https://wordpress.org/plugins/posts-recycler
Description: Recycles posts by adding date custom fields and using them to sort, instead of modifying post dates which can affect SEO.
Version:     1.0
Author:      Adsitus
Author URI:  http://adsitus.com
License:     GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: posts-recycler
Domain Path: /languages
*/

/* deny direct access to the script file */
defined( 'ABSPATH' ) or die( __( 'Cannot access pages directly.' , 'posts-recycler' ) ) ;

/* language localization */
if( ! function_exists( 'posts_recycler_language' ) ) {

	function posts_recycler_language() {
		
	$result = load_plugin_textdomain( 'posts-recycler', false, basename( dirname( __FILE__ ) ) . '/languages/' ) ;

	}	
	
}

add_action( 'init', 'posts_recycler_language' ) ;


/* include the script's functions */
require_once( plugin_dir_path( __FILE__ ) . 'includes/functions.php' ) ;


/* add support for custom fields so that the plugin can rotate them for default and custom post types */
add_action( 'init', 'posts_recycler_support_custom_fields' );


/* add javascript and css files to the plugin's admin */
add_action( 'admin_enqueue_scripts', 'posts_recycler_admin_scripts_css' ) ;


/* add the plugin's admin menu */
add_action( 'admin_menu', 'posts_recycler_admin_options_page' ) ;


/* add link to settings from the plugin listing page */
$plugin = plugin_basename( __FILE__ ) ;

add_filter( "plugin_action_links_$plugin", 'posts_recycler_settings_link' ) ;


/* hooks used by the plugin */
register_activation_hook( __FILE__, 'posts_recycler_activation' ) ;

register_deactivation_hook( __FILE__, 'posts_recycler_deactivation' ) ;


/* show an administration notice to set-up the plugin once it's been activated */
add_action( 'admin_notices', 'posts_recycler_activation_notice' ) ;

add_action( 'network_admin_notices', 'posts_recycler_activation_notice' ) ;


/* register the settings that are used by the plugin */
add_action( 'admin_init', 'posts_recycler_register_settings' ) ; 


/* if multipress is active, action to add plugin to new blogs created */
add_action( 'wpmu_new_blog', 'posts_recycler_new_blog', 10, 6 ) ; 


/* load plugin's options and settings */
$posts_recycler_options = get_option( 'posts_recycler_options' ) ;


/* load the timezone set in the plugin's options */
$posts_recycler_timezone_option = $posts_recycler_options['posts_recycler_timezone'] ;

if( !empty( $posts_recycler_timezone_option ) ) {
	
$timezone_result = date_default_timezone_set( "$posts_recycler_timezone_option" ) ; 

}

/* load the posts_recycler_enabled option from the plugin's settings */
$posts_recycler_enabled = isset( $posts_recycler_options['posts_recycler_enabled'] ) ? intval( $posts_recycler_options['posts_recycler_enabled'] ) : null ;


/* if post recycling has been activated, run the plugin's functions */
if ( $posts_recycler_enabled === 1 AND function_exists( 'posts_recycler_rotation' ) ) {

posts_recycler_rotation() ; 

add_action( 'pre_get_posts', 'posts_recycler_orderby_meta' ) ; 

add_action( 'save_post', 'posts_recycler_post_publish', 20 ) ;

add_action( 'publish_post', 'posts_recycler_post_publish', 20 ) ;

}








