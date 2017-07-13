<?php

defined( 'ABSPATH' ) or die( __( 'Cannot access pages directly.' , 'posts-recycler' ) );

/* only admins can access this page */
if( ! is_admin() ) {
	
die( esc_html__( 'You do not have administrative privileges to access this page' , 'posts-recycler' ) ) ;	

}

/* declaring variables that will be used later */
$css_disabled = null ;

$css_disabled_category = null ;

$posts_recycler_queue_items = null ;

$posts_recycler_html_item = null ;

$categories_html = null ;

$css_queue_empty = null ;

$timezone_dropdown = null ;

$posts_recycler_blog_categories_arg = null ;

$posts_recycler_registered_type_html_item = null ;

/* load all timezones available, which will be used by the timezone dropdown menu so the user can choose his/hers */
$timezone_options = DateTimeZone::listIdentifiers( DateTimeZone::ALL_WITH_BC ) ;

/* save present time for calculations */
$posts_recycler_now = time() ;

/* load plugin's options array */
$posts_recycler_options = get_option( 'posts_recycler_options' ) ;
//print_r( $posts_recycler_options ) ;

/* load the post types registered in the blog */
$posts_recycler_registered_types_blog = posts_recycler_registered_types_blog() ;

/* get the post types that have been saved */
$posts_recycler_post_types = isset( $posts_recycler_options['posts_recycler_post_types'] ) ? $posts_recycler_options['posts_recycler_post_types'] : array( 'post' ) ;

/* retrieve the last recycle event saved as an option during last recycle */
$posts_recycler_last_rotation = ! empty( $posts_recycler_options['posts_recycler_last_rotation'] ) ? $posts_recycler_options['posts_recycler_last_rotation'] : null ;

/* post recycler enabled status */
$posts_recycler_enabled = ! empty( $posts_recycler_options['posts_recycler_enabled'] ) ? $posts_recycler_options['posts_recycler_enabled'] : null ;

/* post recycler fixed status */
$posts_recycler_fixed = isset( $posts_recycler_options['posts_recycler_fixed'] ) ? $posts_recycler_options['posts_recycler_fixed'] : null ;

/* post recycler offset option */
$posts_recycler_offset = isset( $posts_recycler_options['posts_recycler_offset'] ) ? $posts_recycler_options['posts_recycler_offset'] : null ;

/* show queue option */
$posts_recycler_show_queue = ! empty( $posts_recycler_options['posts_recycler_show_queue'] ) ? $posts_recycler_options['posts_recycler_show_queue'] : 10 ;

/* get the time between posts option */
$posts_recycler_interval = isset( $posts_recycler_options['posts_recycler_interval'] ) ? $posts_recycler_options['posts_recycler_interval'] : null ;

/* get plugin's chosen categories */
$posts_recycler_categories = isset( $posts_recycler_options['posts_recycler_categories'] ) ? $posts_recycler_options['posts_recycler_categories'] : null ;

/* clean uninstall option */
$posts_recycler_clean_uninstall = isset( $posts_recycler_options['posts_recycler_clean_uninstall'] ) ? $posts_recycler_options['posts_recycler_clean_uninstall'] : null ;

/* current timezone saved */
$timezone_current = isset( $posts_recycler_options['posts_recycler_timezone'] ) ? $posts_recycler_options['posts_recycler_timezone'] : null ;

/* generate the html for the queued posts for recycling */
$posts_recycler_queue_data = posts_recycler_admin_queue() ;

/* count how many posts can be recycled and save the result for calculation */
$posts_recycler_queue_count = isset( $posts_recycler_queue_data ) ? $posts_recycler_queue_count = count( $posts_recycler_queue_data ) : null ;

/* generate the queued posts lists if there are any */
if( !empty( $posts_recycler_queue_data ) AND is_array( $posts_recycler_queue_data ) ) {

foreach( $posts_recycler_queue_data as $index => $item ) {

$queue_item_id = isset( $item['id'] ) ? $queue_item_id = $item['id'] : null ;

$queue_item_title = isset( $item['title'] ) ? $queue_item_title = esc_html( $item['title'] ) : null ;

$queue_item_title_attr = isset( $queue_item_title ) ? $queue_item_title_attr = esc_attr( $queue_item_title ) : null ; 

$queue_item_edit_link = isset( $item['edit_link'] ) ? $queue_item_edit_link = esc_url( $item['edit_link'] ) : null  ;

/* we break indentation because we use heredoc syntax, so the closing statement must stay at the beginning of the line */
$posts_recycler_queue_items .= <<<EOT

<li><a href="$queue_item_edit_link" title="$queue_item_title_attr">$queue_item_id</a> $queue_item_title</li>

EOT;

}
	
}


/* if the plugin has recycled a post, the posts_recycler_last_rotation is saved in $posts_recycler_last_rotation and the time for that last rotation used in our calculations  */
if( ! empty( $posts_recycler_last_rotation ) ) {

/* convert the last rotation to Year/Month/Day Hour/Minutes/Second format from saved Unix timestamp */
$posts_recycler_rotation_last = date( 'Y-m-d H:i:s', $posts_recycler_last_rotation ) ;

/* calculate the next rotation by adding the post rotation interval to the last saved rotation time */
$posts_recycler_rotation_next_seconds = $posts_recycler_last_rotation + ( $posts_recycler_interval * 3600 ) ;



/* convert the result to Year/Month/Day Hour/Minutes/Seconds format */
$posts_recycler_rotation_next = date( 'Y-m-d H:i:s', $posts_recycler_rotation_next_seconds )  ;

/* calculate the time to the next recycle event by substracting the present time from the next calculated rotation event and convert it to a human-readable format through the posts_recycler_time_convert function */
$posts_recycler_rotation_countdown_array = posts_recycler_time_convert( $posts_recycler_rotation_next_seconds - $posts_recycler_now  ) ;

if( !empty( $posts_recycler_rotation_countdown_array ) AND is_array( $posts_recycler_rotation_countdown_array ) ) {

$posts_recycler_rotation_ctd_days = isset( $posts_recycler_rotation_countdown_array['days'] ) ? $posts_recycler_rotation_countdown_array['days'] : 0 ;

$posts_recycler_rotation_ctd_hours = isset( $posts_recycler_rotation_countdown_array['hours'] ) ? $posts_recycler_rotation_countdown_array['hours'] : 0 ;

$posts_recycler_rotation_ctd_minutes = isset( $posts_recycler_rotation_countdown_array['minutes'] ) ? $posts_recycler_rotation_countdown_array['minutes'] : 0 ;

$posts_recycler_rotation_ctd_seconds = isset( $posts_recycler_rotation_countdown_array['seconds'] ) ? $posts_recycler_rotation_countdown_array['seconds'] : 0 ;

$posts_recycler_rotation_countdown_time = sprintf( esc_html__( '%1$d days, %2$d hours, %3$d minutes, %4$d seconds', 'posts-recycler' ), $posts_recycler_rotation_ctd_days, $posts_recycler_rotation_ctd_hours, $posts_recycler_rotation_ctd_minutes, $posts_recycler_rotation_ctd_seconds ) ;

}

/* strings used by the script */
$posts_recycler_rotation_last = '<strong>' . esc_html__( 'Last rotation: ' , 'posts-recycler' ) . '</strong>' . $posts_recycler_rotation_last ;

$posts_recycler_rotation_next = '<strong>' . esc_html__( 'Next rotation: ' , 'posts-recycler' ) .  '</strong>' . $posts_recycler_rotation_next ;

$posts_recycler_rotation_countdown = '<strong>' . esc_html__( 'Rotation countdown: ' , 'posts-recycler' ) . '</strong>' . $posts_recycler_rotation_countdown_time ;
	
}

/* load the blog's categories to generate recycler categories checkboxes */
$posts_recycler_blog_categories_arg = array( 'hide_empty' => 1 ) ;

$posts_recycler_blog_categories = get_categories( $posts_recycler_blog_categories_arg ) ;

/* loop through the blog's categories */
if( ! empty( $posts_recycler_blog_categories ) AND is_array( $posts_recycler_blog_categories ) ) {

	foreach ( $posts_recycler_blog_categories as $key => $value ) {
		
	$category_checked = null ;

	$category_id = intval( $value->term_id ) ;

	$category_name = esc_html( $value->name ) ;

		if ( is_array( $posts_recycler_categories ) && in_array( $category_id, $posts_recycler_categories ) ) {

		$category_checked = 'checked="checked"' ;

		}

	$posts_recycler_html_item .= <<<EOT

	<label for="$category_id">

	<input id="$category_id" type="checkbox" name="posts_recycler_options[posts_recycler_categories][]" value="$category_id" class="recycler_category" $category_checked />

	$category_name</label>

EOT;

	}	
	
} 


/* generate the html for the post types that are registred in the blog and can be picked */
if( ! empty( $posts_recycler_registered_types_blog ) AND is_array( $posts_recycler_registered_types_blog ) ) {
		
	foreach( $posts_recycler_registered_types_blog as $index => $registered_type_name ) {

	$post_type_checked = null ;

	$post_type_name_html = esc_html( $registered_type_name ) ;
	
	$post_type_name_attr = esc_attr( $registered_type_name ) ;

		if ( is_array( $posts_recycler_post_types ) && in_array( $registered_type_name, $posts_recycler_post_types ) ) {

		$post_type_checked = 'checked="checked"' ;

		}

	$posts_recycler_registered_type_html_item .= <<<EOT

	<label for="$post_type_name_attr">

	<input id="$post_type_name_attr" type="checkbox" name="posts_recycler_options[posts_recycler_post_types][]" value="$post_type_name_attr" class="post_types_item" $post_type_checked />

	$post_type_name_html</label>

EOT;

	}
	
}


/* setting string variables for when recycling is enabled */
if ( ( $posts_recycler_enabled === 1 ) ) { 

$rotation_status = esc_html__( 'Rotation is enabled', 'posts-recycler' ) ;

} else {

$rotation_status = esc_html__( 'Rotation is disabled', 'posts-recycler' ) ;	

$css_disabled = ' class="rotation-disabled"' ;
	
}


/* setting variables when queued posts but no categories have been chosen */
if ( empty( $posts_recycler_categories ) ) {

$rotation_status = sprintf(

esc_html__( 'Please pick which  %1$scategories to rotate%2$s', 'posts-recycler' ),
	
'<a href="options-general.php?page=posts-recycler#posts_recycler_categories_select">',
	
'</a>' ) ;

$rotation_status .= ' ' .  esc_html__( 'Rotation will only work on posts from picked categories.', 'posts-recycler'  ) ;

$css_disabled_category = ' class="rotation-disabled-category"' ;

}


/* setting variables when there are no queued posts available, rotation is enabled and categories have been chosen */
if ( empty( $posts_recycler_queue_count ) AND ( $posts_recycler_enabled === 1 ) AND ! empty( $posts_recycler_categories ) ) {
		
$rotation_status .= ' ' . esc_html__( 'but there are no posts that match the categories chosen.', 'posts-recycler' ) ;

$css_queue_empty = ' class="rotation-queue-empty"' ;
		
} 

/* if the timezones have been loaded successfuly, build the dropdown menu for the plugin */
if( !empty( $timezone_options ) AND is_array( $timezone_options ) ) : 

foreach ( $timezone_options as $value => $text )  :

$timezone_dropdown .= '<option value="'. esc_attr( 'posts_recycler_timezone', $text ) .'"' ;
 
/* if timezone has been chosen, load it */
if ( $text == $timezone_current ) {

$timezone_dropdown .= ' selected="selected"' ;	
	
} 
 
$timezone_dropdown .= '>'. $text .'</option>' ;

endforeach;

endif ;



?>

<!-- DIV WRAP -->
<div class="wrap">

<!-- FORM -->
<form method="post" action="options.php" id="posts-recycler">

<?php settings_fields( 'posts_recycler_settings_group' ); ?>

<!-- HIDDEN FIELD HOLDING posts_recycler_last_rotation -->
<input type="hidden" id="posts_recycler_last_rotation" name="posts_recycler_options[posts_recycler_last_rotation]" value="<?php print $posts_recycler_last_rotation ; ?>" />
<!-- HIDDEN FIELD HOLDING posts_recycler_last_rotation -->

<!-- DIV TITLE -->
<div id="title">

<h2><?php _e( 'Posts recycler', 'posts-recycler' ) ; ?></h2>

<h3<?php print $css_disabled ; print $css_disabled_category ; print $css_queue_empty ;?>><?php print $rotation_status ; ?></h3>

</div>
<!-- DIV TITLE -->

<?php if ( ( $posts_recycler_enabled === 1 ) AND ! empty( $posts_recycler_queue_count ) AND ! empty( $posts_recycler_categories ) ) : ?>

<!-- DIV QUEUE -->
<div id="queue">

<!-- DIV ROTATION STATUS -->
<div id="rotation-status">

<h3><?php _e( 'Rotation status', 'posts-recycler' ) ?></h3>

<ul>
		
<li><?php print $posts_recycler_rotation_last ; ?></li>
		
<li><?php print $posts_recycler_rotation_next ; ?></li>
		
<li><?php print $posts_recycler_rotation_countdown ; ?></li>
		
</ul>
		
</div>
<!-- DIV ROTATION STATUS -->

<h3><?php _e( 'Queued posts', 'posts-recycler' ) ?></h3>

<ul>

<?php print $posts_recycler_queue_items ; ?>

</ul>

</div>
<!-- DIV QUEUE -->

<?php endif ; ?>


<!-- DIV CONFIG -->
<div id="config-recycler">

<h3><?php _e( 'Configure recycler', 'posts-recycler' ) ?></h3>


<!-- OPTION DROPDOWN TO CHOOSE TIMEZONE -->
<label for="posts_recycler_timezone">

<select id="posts_recycler_timezone" name="posts_recycler_options[posts_recycler_timezone]">

<?php 

print $timezone_dropdown ;

?>

</select>

<?php 

/* if timezone variable is empty, warn the user */
if( empty( $timezone_current ) ) {
	
esc_html_e( 'Choose your timezone', 'posts-recycler' ) ; 
	
} else {

esc_html_e( 'Timezone set', 'posts-recycler' ) ; 
	
}

?>

</label>
<!-- OPTION DROPDOWN TO CHOOSE TIMEZONE -->


<!-- OPTION TO ENABLE POST RECYCLING -->
<label for="posts_recycler_enabled"<?php print $css_disabled ; ?>>

<input type="checkbox" id="posts_recycler_enabled" name="posts_recycler_options[posts_recycler_enabled]" value="1" <?php checked( $posts_recycler_enabled ); ?> />

<?php esc_html_e( 'Enable rotation', 'posts-recycler' ) ; ?>

</label>
<!-- OPTION TO ENABLE POST RECYCLING -->


<!-- OPTION TO SET FIXED POST RECYCLING -->
<label for="posts_recycler_fixed">

<input type="checkbox" id="posts_recycler_fixed" name="posts_recycler_options[posts_recycler_fixed]" value="1" <?php checked( $posts_recycler_fixed ); ?> />

<?php esc_html_e( 'Fixed rotation', 'posts-recycler' ) ; ?>

</label>
<!-- OPTION TO SET FIXED POST RECYCLING -->


<!-- OPTION TO SET THE TIME BETWEEN POST RECYCLE CYCLES -->
<label for="posts_recycler_interval"><?php esc_html_e( 'Recycle posts every', 'posts-recycler' ) ; ?>

<input type="text" size="3" maxlength="3" id="posts_recycler_interval" name="posts_recycler_options[posts_recycler_interval]" value="<?php echo esc_attr( $posts_recycler_interval ); ?>" />

<?php 

esc_html_e( 'hours', 'posts-recycler' ) ;

?>
</label>
<!-- OPTION TO SET THE TIME BETWEEN POST RECYCLE CYCLES -->

<!-- OPTION TO OFFSET THE POST RECYCLER QUEUE -->
<label for="posts_recycler_offset">

<input type="text" size="3" maxlength="3" id="posts_recycler_offset" name="posts_recycler_options[posts_recycler_offset]" value="<?php echo esc_attr( $posts_recycler_offset ); ?>" />

<?php esc_html_e( 'Offset post rotation by number of posts', 'posts-recycler' ) ; ?>.

</label>


<label for="posts_recycler_show_queue">

<input type="text" size="3" maxlength="3" id="posts_recycler_show_queue" name="posts_recycler_options[posts_recycler_show_queue]" value="<?php echo esc_attr( $posts_recycler_show_queue ); ?>" />

<?php esc_html_e( 'Number of queued posts to show in the admin', 'posts-recycler' ) ; ?>.

</label>
<!-- OPTION TO OFFSET THE POST RECYCLER QUEUE -->


<?php submit_button(); ?>

</div>
<!-- DIV CONFIG -->


<!-- DIV CATEGORIES -->
<div id="categories">

<h3 id="posts_recycler_categories_select" <?php print $css_disabled_category ; ?>><?php esc_html_e( 'Included categories', 'posts-recycler' ) ; ?></h3>

<fieldset>

<legend><?php esc_html_e( 'Selected categories to recycle' , 'posts-recycler' ) ; ?></legend>

<label for="category_select_all" class="check_all">

<input id="category_select_all" type="checkbox" name="category_select_all" value="all" />

<?php esc_html_e( 'All', 'posts-recycler' ) ; ?></label>

<?php

print $posts_recycler_html_item ;

?>

</fieldset>

<?php submit_button(); ?>

</div>
<!-- DIV CATEGORIES -->








<!-- DIV POST_TYPES -->
<div id="post_types">

<h3 id="posts_recycler_post_types_select" <?php print $css_disabled_category ; ?>><?php esc_html_e( 'Post types', 'posts-recycler' ) ; ?></h3>

<fieldset>

<legend><?php esc_html_e( 'Selected post types to recycle' , 'posts-recycler' ) ; ?></legend>

<label for="post_types_select_all" class="check_all">

<input id="post_types_select_all" type="checkbox" name="post_types_select_all" value="all" />

<?php esc_html_e( 'All', 'posts-recycler' ) ; ?></label>

<?php

print $posts_recycler_registered_type_html_item ;

?>

</fieldset>

<?php submit_button(); ?>

</div>
<!-- POST_TYPES -->
























<!--DIV UNINSTALL -->
<div id="uninstall">
<h3><?php esc_html_e( 'Clean uninstall', 'posts-recycler' ) ; ?></h3>

<input type="checkbox" id="posts_recycler_clean_uninstall" name="posts_recycler_options[posts_recycler_clean_uninstall]" value="1"<?php checked( $posts_recycler_clean_uninstall ); ?> />

<label for="posts_recycler_clean_uninstall"><?php esc_html_e( 'Delete all options from database when you delete this plugin (if you only deactivate the plugin, the options won\'t be deleted)', 'posts-recycler' ) ; ?>

</label>

<?php submit_button(); ?>

</div>
<!--DIV UNINSTALL -->




<!-- DIV FOOTER -->
<div id="footer">
<h4><?php esc_html_e( 'Do you like this plugin?', 'posts-recycler' ) ; ?></h4>

<ul>
<li>
<?php
printf(
    esc_html__( 'Please %1$srate it on the depository%2$s', 'posts-recycler' ),
    '<a href="http://wordpress.org/support/view/plugin-reviews/posts-recycler" target="_blank">',
    '</a>'
);
print ". " ;
esc_html_e( 'Thank you.', 'posts-recycler' ) ;
?>
</li>

<li>
<?php
printf(
    esc_html__( 'Posts recycler is based on %1$sPost Rotation%2$s by %3$sdigitalemphasis%4$s', 'posts-recycler' ),
    '<a href="https://wordpress.org/plugins/post-rotation/" target="_blank">' ,
    '</a>' ,
	'<a href="https://profiles.wordpress.org/digitalemphasis">' ,
	'</a>' 
);
?>
.</li>
</ul>

</div>
<!-- DIV FOOTER -->

</form>
<!-- FORM -->

</div>
<!-- DIV WRAP -->

