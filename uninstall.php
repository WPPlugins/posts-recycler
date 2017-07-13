<?php 


/* if uninstall.php is not called by WordPress, die */
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	
    die;
	
}

	global $wpdb ;
					 
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			/* check if it is a network uninstall - if so, run the uninstall functions for each blog id */
			if ( $networkwide ) {

			$old_blog = $wpdb->blogid ;

			/* Get all blog ids */
			$blogids = $wpdb->get_col( "SELECT {$wpdb->prefix}blogs.blog_id FROM {$wpdb->prefix}blogs" ) ;

				foreach ( $blogids as $blog_id ) {

				switch_to_blog( $blog_id ) ;

				delete_option( 'posts_recycler_options' ) ;
				
	$post_types = null ;
	
	/* retrieve the list of names for registered post types */
	$output = 'names'; 

	/* operator to go along the _builtin condition */
	$operator = 'and'; 

	/* arguments to retrieve built-in post types */
	$args_post_types_builtin = array(	'public'   => true,
										'_builtin' => true
									) ;

	/* arguments to retrieve custom post types */
	$args_post_types_custom = array(	'public'   => true,
										'_builtin' => false
									) ;

	/* add the arguments to an array so we can loop throught them at once and return them in an array */
	$post_types_args = array( $args_post_types_builtin, $args_post_types_custom ) ;

	foreach( $post_types_args as $args ) {
		
	$post_types_results = get_post_types( $args, $output, $operator ) ; 

		if( !empty( $post_types_results ) AND is_array( $post_types_results ) ) {

			foreach ( $post_types_results as $post_type ) {
			
				/* we do not retrieve attachments since they are children of posts, and adding them causes issues */
				if( $post_type !== 'attachment' ) {
				
				$post_types[] = $post_type ;
				
				}
			
			}	
			
		}
		
	}
	
	$meta_key = 'posts_recycler_date' ;
	
	$post_status = 'any' ;

	$query_args = array(	'post_type' => $post_types ,
		
							'post_status' => $post_status ,
								
							'posts_per_page'=> -1,
								
							'fields'        => 'ids',
								
						) ;
		
	/* get ids from all published posts */
	$results = get_posts( $query_args ) ;

	if( ! empty( $results ) ) {
		
		foreach( $results as $key => $item ) {
			
		$published_id = $item ;
			
		$delete_post_meta_result = delete_post_meta( $published_id, $meta_key ) ;
			
		}
		
	}

				}

			switch_to_blog( $old_blog ) ;

			return ;

			}   

		} 


				delete_option( 'posts_recycler_options' ) ;
				


		
	$post_types = null ;
	
	/* retrieve the list of names for registered post types */
	$output = 'names'; 

	/* operator to go along the _builtin condition */
	$operator = 'and'; 

	/* arguments to retrieve built-in post types */
	$args_post_types_builtin = array(	'public'   => true,
										'_builtin' => true
									) ;

	/* arguments to retrieve custom post types */
	$args_post_types_custom = array(	'public'   => true,
										'_builtin' => false
									) ;

	/* add the arguments to an array so we can loop throught them at once and return them in an array */
	$post_types_args = array( $args_post_types_builtin, $args_post_types_custom ) ;

	foreach( $post_types_args as $args ) {
		
	$post_types_results = get_post_types( $args, $output, $operator ) ; 

		if( !empty( $post_types_results ) AND is_array( $post_types_results ) ) {

			foreach ( $post_types_results as $post_type ) {
			
				/* we do not retrieve attachments since they are children of posts, and adding them causes issues */
				if( $post_type !== 'attachment' ) {
				
				$post_types[] = $post_type ;
				
				}
			
			}	
			
		}
		
	}
	
	$meta_key = 'posts_recycler_date' ;
	
	$post_status = 'any' ;

	$query_args = array(	'post_type' => $post_types ,
		
							'post_status' => $post_status ,
								
							'posts_per_page'=> -1,
								
							'fields'        => 'ids',
								
						) ;
		
	/* get ids from all published posts */
	$results = get_posts( $query_args ) ;

	if( ! empty( $results ) ) {
		
		foreach( $results as $key => $item ) {
			
		$published_id = $item ;
			
		$delete_post_meta_result = delete_post_meta( $published_id, $meta_key ) ;
			
		}
		
	}


