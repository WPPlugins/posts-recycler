<?php 

defined( 'ABSPATH' ) or die( __( 'Cannot access pages directly.' , 'posts-recycler' ) ) ;


/* function to retrieve all post types, both built-in and registered, and excluding attachments */
if ( ! function_exists( 'posts_recycler_registered_types_blog' ) ) {
	
	function posts_recycler_registered_types_blog() {

	$post_types = null ;
	
	/* retrieve the list of names for registered post types */
	$output = 'names'; 

	/* operator to go along the _builtin condition */
	$operator = 'and'; 

	/* arguments to retrieve built-in post types */
	$args_post_types_builtin = array(	
										'public'   => true ,
										
										'_builtin' => true ,
										
									) ;

	/* arguments to retrieve custom post types */
	$args_post_types_custom = array(	
										'public'   => true ,
										
										'_builtin' => false ,
										
									) ;

	/* add the arguments to an array so we can loop throught them at once and return them in an array */
	$post_types_args = array( $args_post_types_builtin, $args_post_types_custom ) ;
	
	foreach ( $post_types_args as $args ) {
		
	$post_types_results = get_post_types( $args, $output, $operator ) ; 

		if ( !empty( $post_types_results ) AND is_array( $post_types_results ) ) {

			foreach ( $post_types_results as $post_type ) {
			
				/* we do not retrieve attachments since they are children of posts, and adding them causes issues */
				if ( $post_type !== 'attachment' ) {
				
				$post_types[] = $post_type ;
				
				}
			
			}	
			
		}
		
	}

	return $post_types ;	
	
	}
	
}


/* add support for custom fields so that the plugin can rotate them for default and custom post types */
if ( ! function_exists( 'posts_recycler_support_custom_fields' ) ) {

	function posts_recycler_support_custom_fields() {

	$post_type_support_custom_fields = 'custom-fields' ;

	/* get the post types */
	$post_types_registered = posts_recycler_registered_types_blog() ;

	if ( ! empty( $post_types_registered ) AND is_array( $post_types_registered ) ) {

		foreach ( $post_types_registered as $index => $post_type ) {

			if ( ! empty( $post_type ) ) {

			/* check if the post type supports custom fields */		
			$post_type_support = post_type_supports( $post_type, $post_type_support_custom_fields ) ;
					
				if ( ! $post_type_support ) {

				/* if the post type doesn't support custom fields, add support */	
				add_post_type_support( $post_type, $post_type_support_custom_fields ) ;
							
				}
						
			}

		}

	}	
		
	}

}


/* checks if wordpress version is compatible with the plugin */
if ( ! function_exists( 'posts_recycler_wordpress_check' ) ) {

	function posts_recycler_wordpress_check() {
		
	global $wp_version ;
	
	$posts_recycler_wp_requires = '4.7' ;

		/* if the wordpress versin running the plugin is smaller than the required, tell the user and exit */
		if ( version_compare( $wp_version, $posts_recycler_wp_requires, '<' ) ) {
			
		$message = sprintf(

		esc_html__( 'This plugin requires Wordpress version %1$d or higher. Please consider upgrading your Wordpress installation.', 'posts-recycler' ),
			
		$posts_recycler_wp_requires	) ;
			
		wp_die( $message ) ;

		}
		
		/* if wordpress is multisite, warn the user and exit */
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			
		$message = esc_html__( 'Sorry, this plugin has not been tested to work with Wordpress Multisite.', 'posts-recycler' ) ;
	
		wp_die( $message ) ;

		}

	}	
	
}


/* add posts_recycler_date meta to posts when published */
if ( !function_exists( 'posts_recycler_post_publish' ) ) {
	
	function posts_recycler_post_publish( $post_id ) { 
	
	$result = null ;
	
	$meta_key = 'posts_recycler_date' ;
	
	$date_now = time() ;
	
	$unique = TRUE ;
	
	$single = TRUE ;
	
	$post_meta = null ;
	
	/* get the post status of the post */
	$post_status = get_post_status( $post_id ) ;
	
	$post_meta = get_post_meta( $post_id , $meta_key, true ) ;
	
		/* if the post is published, do nothing. we don't want to update the posts_recycler_date meta and recycle the post by saving it */
		if ( $post_status === 'publish' AND ! empty( $post_meta ) ) {
		
		return ;	
		
		} else {
			
		/* if the post is not published, then add the posts_recycler_date meta to it */	
		$result = add_post_meta( $post_id, $meta_key, $date_now, $unique ) ;
			
		}
	
	return $result ;
	
	}

}


/* set a transient to be able to show an administrative notice once the plugin has been activated  */
if ( ! function_exists( 'posts_recycler_transient_activation_notice' ) ) {

	function posts_recycler_transient_activation_notice() {

	set_transient( 'posts_recycler_activation_notice', true, 5 ) ;

	}
	
}


/* show an administrative notice upon plugin activation to link to the settings page */
if ( ! function_exists( 'posts_recycler_activation_notice' ) ) {

	function posts_recycler_activation_notice() {

	/* Check transient, if available display notice */
	if ( get_transient( 'posts_recycler_activation_notice' ) ) :
	
	$settings_link = '<a href="options-general.php?page=posts-recycler">' . esc_html__( 'Click here to set-up Posts Recycler', 'posts-recycler' ) . '</a>' ;
	
	 ?>
	 
	<div class="updated notice is-dismissible">

	<p><?php esc_html_e( 'Thank you for using posts recycler!', 'posts-recycler' ) ; ?> <strong><?php esc_html_e( 'By default, the plugin is not active yet.', 'posts-recycler' ) ; ?><br /><?php echo $settings_link ; ?></strong>.</p>

	</div>

	<?php

	/* Delete transient, only display this notice once. */
	delete_transient( 'posts_recycler_activation_notice' ) ;

	endif ;

	}	
	
}


/* add plugin options function and set defaults */
if ( ! function_exists( 'posts_recycler_add_options' ) ) {
	
	function posts_recycler_add_options() {
	
	$time_now = time() ;
	
	$timezone_default = date_default_timezone_get() ;
	
	$post_types = posts_recycler_registered_types_blog() ;
	
	$categories = get_terms( array(	
	
									'taxonomy' => 'category' ,

									'hide_empty' => false ,
								
									'fields' => 'names' ,
								) 

							);	
	
	$posts_recycler_options = array(	
						
										'posts_recycler_post_types' => $post_types ,
	
										'posts_recycler_last_rotation' => $time_now ,
	
										'posts_recycler_enabled' => '' ,
										
										'posts_recycler_fixed' => '' ,
										
										'posts_recycler_offset' => '0' ,
										
										'posts_recycler_show_queue' => '10' ,
										
										'posts_recycler_interval' => '24' ,
										
										'posts_recycler_categories' => $categories ,
										
										'posts_recycler_clean_uninstall' => '1' ,
										
										'posts_recycler_timezone' => $timezone_default ,
										
									) ;
									
									
	add_option( 'posts_recycler_options', $posts_recycler_options ) ;
	
	}

}



/* add custom field support to all registered post types to be able to use the plugin */









/* register settings function */
if ( !function_exists( 'posts_recycler_register_settings' ) ) {
	
	function posts_recycler_register_settings() {
		
	register_setting( 'posts_recycler_settings_group', 'posts_recycler_options', 'posts_recycler_options_sanitize' ) ;

	}

}



/* sanitize submitted options */
if ( ! function_exists( 'posts_recycler_options_sanitize' ) ) {

	function posts_recycler_options_sanitize( $input_form ) {
	
	$input_validated = array() ;
	
	$now = time() ;
	
	if ( !empty( $input_form ) AND is_array( $input_form ) ) {
		
		//print_r( $input_form ) ;
		//exit ;
		
		foreach ( $input_form as $key => $value ) {
			
			switch( $key ) {
				
			case 'posts_recycler_post_types' :	
			
			$input_validated[$key] =  posts_recycler_post_types_validate( $value ) ; 
			
			break ;		
				
			case 'posts_recycler_last_rotation' :	
			
			$input_validated[$key] = posts_recycler_last_rotation_validate( $value ) ;
			
			break ;	

			case 'posts_recycler_enabled' :			
			
			$input_validated[$key] = intval( $value ) ;
			
			break ;	

			case 'posts_recycler_fixed' :		
			
			$input_validated[$key] = intval( $value ) ;
			
			break ;	

			case 'posts_recycler_offset' :		
			
			$input_validated[$key] = posts_recycler_offset_validate( $value ) ;
			
			break ;

			case 'posts_recycler_show_queue' :	
			
			$input_validated[$key] = posts_recycler_show_queue_validate( $value ) ;
			
			break ;	

			case 'posts_recycler_interval' :			
			
			$input_validated[$key] = posts_recycler_interval_validate( $value ) ;
			
			break ;	

			case 'posts_recycler_categories' :		
			
			$input_validated[$key] = array_map( 'intval', $value ) ;
			
			break ;	

			case 'posts_recycler_clean_uninstall' :		
			
			$input_validated[$key] = intval( $value ) ;
			
			break ;

			case 'posts_recycler_timezone' :		
			
			$input_validated[$key] = posts_recycler_timezone_validate( $value ) ;
			
			break ;				
								
			}
			
		}
		
	}
	
	return $input_validated ;
		
	}
	
}



/* function to validate submitted post types from admin form */
if ( ! function_exists( 'posts_recycler_post_types_validate' ) ) {
	
	function posts_recycler_post_types_validate( $post_types ) {
		
	$post_types_validated = array() ;
	
	if ( empty( $post_types ) ) {
	
	$message = esc_html__( 'You must choose a post type for the plugin to work. The default post type (post) will be used.', 'posts-recycler' ) ;
	
	$post_types_validated = array( 'post' ) ;
	
	$type = 'error' ;
		
	$number = 0 ;
	
	add_settings_error( 'posts_recycler_post_types_notice', 'posts_recycler_post_types_notice', $message, $type ) ;	

	} else {
		
	$post_types_validated = array_map( 'sanitize_text_field', $post_types ) ;	
		
	}
	
	return $post_types_validated ;
		
	}
	
}


/* function to validate the last rotation to avoid issues */
if ( ! function_exists( 'posts_recycler_last_rotation_validate') ) {
	
	function posts_recycler_last_rotation_validate( $last_rotation ) {
		
		$now = time() ;
		
		$last_rotation_validate = null ;
		
		if ( ! empty( $last_rotation )  ) {
		
		$last_rotation_validate = $last_rotation ;
		
		} 
		
	return $last_rotation_validate ;	
		
	}
	
}
	
	



/* function to validate submitted timezone */
if ( ! function_exists( 'posts_recycler_timezone_validate' ) ) {
	
	function posts_recycler_timezone_validate( $timezone = null ) {
	
	$timezone_validated = null ;
	
		if ( !empty( $timezone ) ) {
		
		$timezone_options = DateTimeZone::listIdentifiers( DateTimeZone::ALL_WITH_BC ) ;
		
		if ( in_array( $timezone, $timezone_options ) ) {
			
		$timezone_validated = $timezone ;
		
		}
			
		}
	
	return $timezone_validated ;
	
	}
	
	
}



/* function to validate posts_recycler_offset  */
if ( ! function_exists( 'posts_recycler_offset_validate' ) ) {
	
	function posts_recycler_offset_validate( $number ) {
	
	$number = intval( $number ) ;
	
	$posts_recycler_queue = posts_recycler_admin_queue() ;
	
	$queue_count = count( $posts_recycler_queue ) ;

	if ( ( $number === $queue_count ) AND ( $queue_count != 0 ) ) {
		
	$message = esc_html__( 'The queue offset you have entered is equal to the available queue items. The default offset of 0 will be used.', 'posts-recycler' ) ;
				
	$type = 'error' ;
		
	$number = 0 ;
	
	add_settings_error( 'posts_recycler_offset_validate_notice', 'posts_recycler_offset_validate_notice', $message, $type ) ;	

	}
	
	if ( $number > $queue_count ) {

	$message = esc_html__( 'The queue offset you have entered is larger than the available queue items. The default offset of 0 will be used.', 'posts-recycler' ) ;
				
	$type = 'error' ;
		
	$number = 0 ;

	add_settings_error( 'posts_recycler_offset_validate_notice', 'posts_recycler_offset_validate_notice', $message, $type ) ;	
	
	}
	
	if ( $number < 0 ) {
		
	$message = esc_html__( 'You have entered an invalid queue offset. The default offset of 0 will be used.', 'posts-recycler' ) ;
				
	$type = 'error' ;
		
	$number = 0 ;
	
	add_settings_error( 'posts_recycler_offset_validate_notice', 'posts_recycler_offset_validate_notice', $message, $type ) ;	
	
	}
	
	return $number ;
		
	}
	
}


/* function to validate the quantity of admin queue items to show */
if ( ! function_exists( 'posts_recycler_show_queue_validate' ) ) {

	function posts_recycler_show_queue_validate( $number_queue ) {
		
	$number = intval( $number_queue ) ;

	if ( $number_queue <= 0 ) {
			
	$message = esc_html__( 'A queue number must be submitted. The default of 10 will be used.', 'posts-recycler' ) ;
				
	$type = 'error' ;
		
	$number = 10 ;
				
	add_settings_error( 'posts_recycler_show_queue_notice', 'posts_recycler_show_queue_notice', $message, $type ) ;
			
	}

	return $number_queue ;
		
	}	
		
}


/* validate the posts_recycler_interval submitted */
if ( ! function_exists( 'posts_recycler_interval_validate' ) ) {

	function posts_recycler_interval_validate( $interval ) {
	
	$message = null ;
	
	$type = null ;
	
	$interval = abs( $interval ) ;
	
		if ( empty( $interval ) ) {
			
		$message = esc_html__( 'The post interval must be between 1 and 999. The default of 1 has been used instead.', 'posts-recycler' ) ;
			
		$type = 'error' ;
			
		$interval = 1 ;
			
		add_settings_error( 'posts_recycler_interval_notice', 'posts_recycler_interval_notice', $message, $type ) ;
			
		}	

	return $interval ;

	}	
	
}


/* unregister plugin settings function */
if ( ! function_exists( 'posts_recycler_unregister_settings' ) ) {

	function posts_recycler_unregister_settings() {

	unregister_setting( 'posts_recycler_settings_group', 'posts_recycler_options' );	
	
	}	
	
}


/* delete plugin options function */
if ( ! function_exists( 'posts_recycler_delete_options' ) ) {

	function posts_recycler_delete_options() {
		
		$posts_recycler_options = get_option( 'posts_recycler_options' ) ;
		
		$posts_recycler_clean_uninstall = $posts_recycler_options['posts_recycler_clean_uninstall'] ;
		
		if (  $posts_recycler_clean_uninstall === 1 ) {

		delete_option( 'posts_recycler_options' ) ;
		
		}
		
	}

}


/* adds posts_recycler_date meta field to all existing posts no matter what state they're in (published, draft, pending, etc) using their creation/published date (converted to a timestamp for numberical sorting) when the plugin is activated */
if ( ! function_exists( 'posts_recycler_meta_setup' ) ) {
	
	function posts_recycler_meta_setup() {
		
		$post_type = posts_recycler_registered_types_blog() ;
		
		$post_status = 'publish' ;
		
		$meta_key = 'posts_recycler_date' ;

		$query_args = array(	
		
								'post_type' => $post_type ,
		
								'post_status' => $post_status ,
								
								'posts_per_page'=> -1 ,
								
								'fields'        => 'ids' ,
								
							) ;
		
		/* our query will only retrieve posts ids to speed up the process, then we'll retrieve the date using the ids */
		$results = get_posts( $query_args ) ;

		if ( ! empty( $results ) ) {
			
			foreach ( $results as $key => $item_id ) {
				
			$published_id = $item_id ;
			
			/* we set-up the post date as Year/Month/Day in case we want to use it to sort later on. Right now we convert it to an Unix timestamp to make ordering posts easier */
			$published_date = get_the_date( 'Ymd', $published_id ) ;
			
			$timestamp = strtotime( $published_date ) ;
				
			$add_post_meta_result = add_post_meta( $published_id, $meta_key, $timestamp, TRUE ) ;
				
			}
			
		}
		
		return $add_post_meta_result ;
		
	}

}


/* deletes posts_recycler_date meta when uninstalling the plugin */
if ( !function_exists( 'posts_recycler_meta_uninstall' ) ) {
	
	function posts_recycler_meta_uninstall() {
	
	$post_type = posts_recycler_registered_types_blog() ;
	
	$meta_key = 'posts_recycler_date' ;
	
	$post_status = 'any' ;

	$query_args = array(	
	
							'post_type' => $post_type ,
		
							'post_status' => $post_status ,
								
							'posts_per_page'=> -1,
								
							'fields'        => 'ids' ,
								
						) ;
		
	/* get ids from all published posts */
	$results = get_posts( $query_args ) ;

	if ( ! empty( $results ) ) {
		
		foreach ( $results as $key => $item ) {
			
		$published_id = $item ;
			
		$delete_post_meta_result = delete_post_meta( $published_id, $meta_key ) ;
			
		}
		
	}
	
	return $delete_post_meta_result ;
	
	}

}


/* add link to settings from the plugin listing page */
if ( !function_exists( 'posts_recycler_settings_link' ) ) {

	function posts_recycler_settings_link( $links ) {

	$settings_link = '<a href="options-general.php?page=posts-recycler">' . esc_html__( 'Settings', 'posts-recycler' ) . '</a>' ;

	array_unshift( $links, $settings_link ) ;

	return $links ;

	}

}


/* functions to run when plugin is activated */
if ( ! function_exists( 'posts_recycler_activation' ) ) {
	
	function posts_recycler_activation( $networkwide ) {
	
	global $wpdb ;
					 
	if ( function_exists( 'is_multisite' ) && is_multisite() ) {

		/* check if it is a network activation - if so, run the activation function for each blog id */
		if ( $networkwide ) {

		$old_blog = $wpdb->blogid ;

		/* Get all blog ids */
		$blogids = $wpdb->get_col( "SELECT {$wpdb->prefix}blogs.blog_id FROM {$wpdb->prefix}blogs" ) ;

			foreach ( $blogids as $blog_id ) {

			switch_to_blog( $blog_id ) ;
			
			posts_recycler_wordpress_check() ;

			posts_recycler_add_options() ;
				
			posts_recycler_meta_setup() ; 
			
			posts_recycler_transient_activation_notice() ;

			}

		switch_to_blog( $old_blog ) ;

		return ;

		}   

	} 

	posts_recycler_wordpress_check() ;
	
	posts_recycler_add_options() ;
		
	posts_recycler_meta_setup() ;
	
	posts_recycler_transient_activation_notice() ;
		
	}
	
}


/* functions to run when plugin is deactivated */
if ( ! function_exists( 'posts_recycler_deactivation' ) ) {
	
	function posts_recycler_deactivation( $networkwide ) {
	
	global $wpdb ;
                 
	if ( function_exists( 'is_multisite' ) && is_multisite() ) {

		/* check if it is a network activation - if so, run the activation function for each blog id */
		if ( $networkwide ) {

		$old_blog = $wpdb->blogid ;

		/* Get all blog ids */
		$blogids = $wpdb->get_col( "SELECT {$wpdb->prefix}blogs.blog_id FROM {$wpdb->prefix}blogs" ) ;

			foreach ( $blogids as $blog_id ) {

			switch_to_blog( $blog_id ) ;

			posts_recycler_unregister_settings() ;

			}

		switch_to_blog( $old_blog ) ;

		return ;

		}   

	} 

	posts_recycler_unregister_settings() ;
		
	}
	
}


/* functions to run when plugin is deleted */
if ( ! function_exists( 'posts_recycler_uninstall' ) ) {
	
	function posts_recycler_uninstall( $networkwide ) {
	
	global $wpdb ;
					 
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			/* check if it is a network uninstall - if so, run the uninstall functions for each blog id */
			if ( $networkwide ) {

			$old_blog = $wpdb->blogid ;

			/* Get all blog ids */
			$blogids = $wpdb->get_col( "SELECT {$wpdb->prefix}blogs.blog_id FROM {$wpdb->prefix}blogs" ) ;

				foreach ( $blogids as $blog_id ) {

				switch_to_blog( $blog_id ) ;

				posts_recycler_delete_options() ;	
					
				posts_recycler_meta_uninstall() ;

				}

			switch_to_blog( $old_blog ) ;

			return ;

			}   

		} 

	posts_recycler_delete_options() ;	
		
	posts_recycler_meta_uninstall() ;
		
	}
	
}


/* adds the plugin's options page under the Wordpress Settings, and names the function that will generate the content */
if ( ! function_exists( 'posts_recycler_admin_options_page' ) ) {
	
	function posts_recycler_admin_options_page() {
	
	/* we create a global variable to be used for admin purposes */
	global $posts_recycler_admin_options_page ;
	
	/* load the add_options_page result into the global variable to be used by the posts_recycler_admin_scripts_css function */
	$posts_recycler_admin_options_page = add_options_page( esc_html__( 'Posts recycler', 'posts-recycler' ), esc_html__( 'Posts Recycler', 'posts-recycler' ), 'manage_options', 'posts-recycler', 'posts_recycler_options_page' ) ;

	}
	
}


/* add javascript used by the plugin's admin */
if ( ! function_exists( 'posts_recycler_admin_scripts_css' ) ) {

	function posts_recycler_admin_scripts_css( $hook ) {

	global $posts_recycler_admin_options_page ;

		if ( !empty( $posts_recycler_admin_options_page ) AND ( $hook == $posts_recycler_admin_options_page ) ) {

		wp_register_script( 'posts-recycler', plugins_url( 'js/posts-recycler.js' , dirname(__FILE__) ), array( 'jquery' ), false, true ) ;

		wp_enqueue_script( 'posts-recycler' ) ;

		wp_enqueue_style( 'posts-recycler', plugins_url( 'css/posts-recycler.css' , dirname(__FILE__) ) ) ;
			
		}

	}

}


/* if multisite mode is active, function to add the plugin to newly created network blogs */
if ( ! function_exists( 'posts_recycler_new_blog' ) ) {

	function posts_recycler_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

	global $wpdb ;
	 
		if ( is_plugin_active_for_network( plugin_dir_path() . 'posts-recycler.php'  ) ) {

		$old_blog = $wpdb->blogid ;

		switch_to_blog( $blog_id ) ;

		posts_recycler_add_options() ;
				
		posts_recycler_meta_setup() ;

		switch_to_blog( $old_blog ) ;

		}

	}

}


/* generate the plugin's options page by including it */
if ( ! function_exists( 'posts_recycler_options_page' ) ) {
	
	function posts_recycler_options_page() {
	
	require_once( plugin_dir_path( __FILE__ ) . 'admin.php' ) ;	
		
	}
	
}


/* function to show the countdown to next recycle event in the admin */
if ( !function_exists( 'posts_recycler_time_convert' ) ) {

	function posts_recycler_time_convert( $seconds = null ) {
		
	$posts_recycler_time_rotation = null ;
		
		if ( ! empty( $seconds ) ) {

		$posts_recycler_seconds = $seconds % 60;
		
		$posts_recycler_minutes = floor( ( $seconds % 3600 ) / 60 ) ;
		
		$posts_recycler_hours = floor( ( $seconds % 86400 ) / 3600 ) ;
		
		$posts_recycler_days = floor( $seconds / 86400 ) ;
		
		$posts_recycler_time_rotation = array(	
		
												'days' => $posts_recycler_days ,

												'hours' => $posts_recycler_hours ,
												
												'minutes' => $posts_recycler_minutes ,
																
												'seconds' => $posts_recycler_seconds ,
	
												) ;

		return $posts_recycler_time_rotation ;
			
		}
	
	}

}


/* function to retrive the posts that will be recycled next for the admin queue */
if ( !function_exists( 'posts_recycler_admin_queue' ) ) {
	
	function posts_recycler_admin_queue() {
	
	$posts_recycler_queue_items = null ;
	
	$posts_recycler_admin_queue_html = null ;
	
	$posts_recycler_admin_queue = null ;
	
	$posts_recycler_options = get_option( 'posts_recycler_options' ) ;

	$admin_queue_limit_end = $posts_recycler_options['posts_recycler_show_queue'] ;
	
	$posts_recycler_queue_data = posts_recycler_select_posts( array( 'number_of_posts' => $admin_queue_limit_end  ) ) ;
	
//	print "the post admin queue is: " ;
//	print_r( $posts_recycler_queue_data ) ;
	
		if ( !empty( $posts_recycler_queue_data  ) AND is_array( $posts_recycler_queue_data ) ) {
			
			foreach ( $posts_recycler_queue_data as $index => $id ) {

			$title = get_the_title( $id ) ;
			
			$edit_link = get_edit_post_link( $id ) ;
			
			$posts_recycler_admin_queue[] = array(	
			
													'id' => $id ,
			
													'title' => $title ,
			
													'edit_link' => $edit_link ,
															
												) ;
			


			}

		}
		
	return $posts_recycler_admin_queue ;

	}

}



/* function uses pre_get_posts to modify the main query and sort posts using posts_recycler_date meta instead of the published date */
if ( !function_exists( 'posts_recycler_orderby_meta') ) {
	
	function posts_recycler_orderby_meta( $query ) {
		
	$meta_key = 'posts_recycler_date' ;
	
		/* the query will not be modified for the admin section or if we are viewing attachments (if we allow attachments to be sorted we get an error) */
		if ( $query->is_main_query() && ! is_admin() && ! is_attachment() ) {
			
		$post_categories = null ;
		
		/* retrieve plugin's options */
		$posts_recycler_options = get_option( 'posts_recycler_options' ) ;
		
		/* categories chosen to be recycled */
		$post_categories = $posts_recycler_options['posts_recycler_categories'] ;
		
		/* custom post types chosen to be recycled */
		$post_types = $posts_recycler_options['posts_recycler_post_types'] ;
		
		if ( ! empty( $post_categories ) AND is_array( $post_categories ) ) {
		
		$post_categories = implode( ',', $post_categories ) ;
		
		$query->set( 'cat', "$post_categories" ) ;
	
		}
		
		if ( ! empty( $post_types ) AND is_array( $post_types ) ) {
		
		$query->set( 'post_type', $post_types ) ;
	
		}
		
		
		/* we modify the main query by setting it to use the meta_key posts_recycler_date to sort posts */
		$query->set( 'meta_key', $meta_key ) ;	
		
		/* we use timestamps to be able to sort posts by the posts_recycler_date meta, which is why we use orderby: meta_value_num */
		$query->set( 'orderby', 'meta_value_num' );
		
		$query->set( 'order', 'DESC' ) ;

		}
	
	}
	
}


/* main function to select posts for use by the plugin */
if ( ! function_exists( 'posts_recycler_select_posts' ) ) {
	
	function posts_recycler_select_posts( $array = null ) {

	global $post ;
	
	$post_id = null ;

	$meta_key = 'posts_recycler_date' ;
	
	$posts_recycler_categories_imploded = null ;
	
	$posts_recycler_options = get_option( 'posts_recycler_options' ) ;
	
	$posts_recycler_categories = $posts_recycler_options['posts_recycler_categories'] ;
	
	$posts_types = $posts_recycler_options['posts_recycler_post_types'] ;
	
	$offset = $posts_recycler_options['posts_recycler_offset'] ;
	
	$posts_recycler_last_rotation = $posts_recycler_options['posts_recycler_last_rotation'] ;

	if ( ! empty( $posts_recycler_categories ) AND is_array( $posts_recycler_categories )  ) {
		
	$posts_recycler_categories_imploded = implode( ',', $posts_recycler_categories ) ;

	}

	$limit_start = isset( $offset ) ? $limit_start = $offset : 0 ; 

	$limit_end = isset( $array['number_of_posts'] ) ? $limit_end = $array['number_of_posts'] : 1 ;
	
	$order = isset( $array['order'] ) ? $order = $array['order'] : 'ASC' ;
	
	$compare = '<' ;
	
	
	$query_args = array(	
	
							'numberposts'		=> $limit_end ,
	
							'post_type'			=> $posts_types ,
							
							'post_status'		=> 'publish', 
							
							'cat'				=> $posts_recycler_categories_imploded ,
							
							'meta_key'			=> $meta_key ,

							'meta_query' => array(
							
												array(	'key' => $meta_key ,
												
														'value' => $posts_recycler_last_rotation ,
														
														'compare' => $compare,
														
														)
												
													),
									
									'orderby'			=> 'meta_value_num' ,
									
									'order'				=> $order ,
									
									'offset'			=> $offset ,
									
									'fields'			=> 'ids' ,
									
									'suppress_filters'	=> false ,
									
						) ;
	

	/* if the sorting submittid is for descending, remove the meta_query from the array, otherwise we would get wrong results */
	if ( isset( $order ) AND $order === 'DESC'  ) {
		
	unset( $query_args['meta_query'] ) ;
	
	} 
	

	$query = get_posts( $query_args	) ;	
	
	if ( ! empty( $query ) AND is_array( $query )  ) {
		
		foreach ( $query as $index => $id ) {
		
		$post_id[] = $id ;
			
		}
		
	}

	return $post_id ;

	}

}


/* plugin's main post rotation function */
if ( ! function_exists( 'posts_recycler_rotation' ) ) {
	
	function posts_recycler_rotation() {
		
	/* name of the meta key we use for sorting dates*/
	$meta_key = 'posts_recycler_date' ;
	
	$post_id_oldest = null ;
	
	$post_id_latest = null ;
	
	$post_date_latest = null ;
	
	$post_date_oldest_unix = null ;
	
	$post_date_latest_unix = null ;
	
	$key_moment = null ;
	
	$update_post_meta_result = null ;
	
	$posts_recycler_options = get_option( 'posts_recycler_options' ) ;
	//print_r( $posts_recycler_options ) ;
	
	/* get the post interval picked in the settings and multiply it by 3600 to convert it to seconds */
	$posts_recycler_interval = ( $posts_recycler_options['posts_recycler_interval'] ) * 3600 ;
	
	/* retrive value of the fixed recycle option */
	$posts_recycler_fixed = isset( $posts_recycler_options['posts_recycler_fixed'] ) ? $posts_recycler_options['posts_recycler_fixed'] : null ;
	
	/* retrieve the last recycle event saved as an option during last recycle */
	$posts_recycler_last_rotation = $posts_recycler_options['posts_recycler_last_rotation'] ;
	
	/* the current time to be used for calculations */
	$current_time = time() ;
	
	/* get the id for the oldest post that belongs to the chosen categories in the plugin's settings */
	$post_id_oldest_result = posts_recycler_select_posts() ;
	
	if ( !empty( $post_id_oldest_result ) AND is_array( $post_id_oldest_result ) ) {
		
	$post_id_oldest = $post_id_oldest_result[0] ;
	
	}
	
	/* get the id for the latest post that belongs to the chosen categories in the plugin's settings */
	$post_id_latest_result = posts_recycler_select_posts( array( 'order' => 'DESC'  ) ) ;
	
	if ( !empty( $post_id_latest_result ) AND is_array( $post_id_latest_result ) ) {
	
	$post_id_latest = $post_id_latest_result[0] ;

	}

	/* get the posts_recycler_date meta values for the oldest post */
	$post_date_oldest_unix = get_post_meta( $post_id_oldest, $meta_key, true ) ;

	/* get the posts_recycler_date meta values for the latest post */
	$post_date_latest_unix = get_post_meta( $post_id_latest, $meta_key, true ) ;
	
	if ( $posts_recycler_fixed === '1' && ! empty( $posts_recycler_last_rotation ) ) {
	
	$key_moment = $posts_recycler_last_rotation ;
	
	} else {

	$key_moment = $post_date_latest_unix ;
	
	}
	
	$discrepancy = $current_time - $key_moment ;
	
	if ( $discrepancy >= $posts_recycler_interval ) {

		if ( ! empty( $post_id_oldest ) ) {
			
			if ( $posts_recycler_fixed === '1' ) {
				
			$posts_recycled = $current_time + $posts_recycler_interval ;
			
			$update_post_meta_result = update_post_meta( $post_id_oldest, $meta_key, $posts_recycled ) ;
			
			$posts_recycler_options['posts_recycler_last_rotation'] = $posts_recycled ;
			
			$result_option = update_option( 'posts_recycler_options', $posts_recycler_options ) ;

			} else {

			$update_post_meta_result = update_post_meta( $post_id_oldest, $meta_key, $current_time ) ;
			
			$posts_recycler_options['posts_recycler_last_rotation'] = $current_time ;
			
			$result_option = update_option( 'posts_recycler_options', $posts_recycler_options ) ;		
						
			}
		
		}
	
	} else {

	$result_option = update_option( 'posts_recycler_options', $posts_recycler_options ) ;
	
	}
		
	}
	
}
