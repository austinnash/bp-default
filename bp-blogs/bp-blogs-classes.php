<?php

Class BP_Blogs_Blog {
	var $id;
	var $user_id;
	var $blog_id;
	
	function bp_blogs_blog( $id = null ) {
		global $bp, $wpdb;
		
		if ( !$user_id )
			$user_id = $bp['current_userid'];

		if ( $id ) {
			$this->id = $id;
			$this->populate();
		}
	}
	
	function populate() {
		global $wpdb, $bp;
		
		$blog = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $bp['blogs']['table_name'] . " WHERE id = %d", $this->id ) );

		$this->user_id = $blog->user_id;
		$this->blog_id = $blog->blog_id;
	}
	
	function save() {
		global $wpdb, $bp;
		
		// Don't try and save if there is no user ID or blog ID set.
		if ( !$this->user_id || !$this->blog_id )
			return false;
			
		// Don't save if this blog has already been recorded for the user.
		if ( !$this->id && $this->exists() )
			return false;
		
		if ( $this->id ) {
			// Update
			$sql = $wpdb->prepare( "UPDATE " . $bp['blogs']['table_name'] . " SET user_id = %d, blog_id = %d WHERE id = %d", $this->user_id, $this->blog_id, $this->id );
		} else {
			// Save
			$sql = $wpdb->prepare( "INSERT INTO " . $bp['blogs']['table_name'] . " ( user_id, blog_id ) VALUES ( %d, %d )", $this->user_id, $this->blog_id );
		}
		
		if ( !$wpdb->query($sql) )
			return false;
		
		if ( $this->id )
			return $this->id;
		else
			return $wpdb->insert_id;
	}
	
	function exists() {
		global $bp, $wpdb;
		
		return $wpdb->get_var( $wpdb->prepare( "SELECT count(id) FROM " . $bp['blogs']['table_name'] . " WHERE user_id = %d AND blog_id = %d", $this->user_id, $this->blog_id ) );
	}
	
	/* Static Functions */
	
	function delete_blog_for_all( $blog_id ) {
		global $wpdb, $bp;
		
		return $wpdb->query( $wpdb->prepare( "DELETE FROM " . $bp['blogs']['table_name'] . " WHERE blog_id = %d", $blog_id ) );
	}
	
	function delete_blog_for_user( $blog_id, $user_id = null ) {
		global $wpdb, $bp;
		
		if ( !$user_id )
			$user_id = $bp['loggedin_userid'];

		return $wpdb->query( $wpdb->prepare( "DELETE FROM " . $bp['blogs']['table_name'] . " WHERE user_id = %d AND blog_id = %d", $user_id, $blog_id ) );
	}
	
	function delete_blogs_for_user( $user_id = null ) {
		global $wpdb, $bp;

		if ( !$user_id )
			$user_id = $bp['loggedin_userid'];

		return $wpdb->query( $wpdb->prepare( "DELETE FROM " . $bp['blogs']['table_name'] . " WHERE user_id = %d", $user_id ) );
	}
	
	function get_blogs_for_user( $user_id = null ) {
		global $bp, $wpdb;
		
		if ( !$user_id )
			$user_id = $bp['current_userid'];
			
		$blog_ids = $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM " . $bp['blogs']['table_name'] . " WHERE user_id = %d", $user_id) );
		$total_blog_count = BP_Blogs_Blog::total_blog_count( $user_id );
		
		for ( $i = 0; $i < count($blog_ids); $i++ ) {
			$blogs[] = array(
				'id' => $blog_ids[$i],
				'siteurl' => get_blog_option($blog_ids[$i], 'siteurl'),
				'title' => get_blog_option($blog_ids[$i], 'blogname'),
				'description' => get_blog_option($blog_ids[$i], 'blogdescription')
			);
		}

		return array( 'blogs' => $blogs, 'count' => $total_blog_count );
	}
	
	function total_blog_count( $user_id = null ) {
		global $bp, $wpdb;
		
		if ( !$user_id )
			$user_id = $bp['current_userid'];
		
		return $wpdb->get_var( $wpdb->prepare( "SELECT count(blog_id) FROM " . $bp['blogs']['table_name'] . " WHERE user_id = %d", $user_id) );
	}
	
	function get_all() {
		global $bp, $wpdb;
		
		return $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM " . $bp['blogs']['table_name'] ) );
	}
	
	function get_random() {
		global $bp, $wpdb;
		
		return $wpdb->get_var( $wpdb->prepare( "SELECT blog_id FROM " . $bp['blogs']['table_name'] . " ORDER BY rand() LIMIT 1" ) );
	}
	
}

Class BP_Blogs_Post {
	var $id;
	var $user_id;
	var $blog_id;
	var $post_id;
	var $date_created;
	
	function bp_blogs_post( $id = null ) {
		global $bp, $wpdb;

		if ( !$user_id )
			$user_id = $bp['current_userid'];

		if ( $id ) {
			$this->id = $id;
			$this->populate();
		}
	}

	function populate() {
		global $wpdb, $bp;
		
		$post = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $bp['blogs']['table_name_blog_posts'] . " WHERE id = %d", $this->id ) );

		$this->user_id = $post->user_id;
		$this->blog_id = $post->blog_id;
		$this->post_id = $post->post_id;
		$this->date_created = $post->date_created;
	}
	
	function save() {
		global $wpdb, $bp;
		
		if ( $this->id ) {
			// Update
			$sql = $wpdb->prepare( "UPDATE " . $bp['blogs']['table_name_blog_posts'] . " SET post_id = %d, blog_id = %d, user_id = %d, date_created = FROM_UNIXTIME(%d) WHERE id = %d", $this->post_id, $this->blog_id, $this->user_id, $this->date_created, $this->id );
		} else {
			// Save
			$sql = $wpdb->prepare( "INSERT INTO " . $bp['blogs']['table_name_blog_posts'] . " ( post_id, blog_id, user_id, date_created ) VALUES ( %d, %d, %d, FROM_UNIXTIME(%d) )", $this->post_id, $this->blog_id, $this->user_id, $this->date_created );
		}
		
		if ( !$wpdb->query($sql) )
			return false;
		
		if ( $this->id )
			return $this->id;
		else
			return $wpdb->insert_id;	
	}
	
	/* Static Functions */
	
	function delete( $post_id, $blog_id, $user_id = null ) {
		global $wpdb, $bp, $current_user;
		
		if ( !$user_id )
			$user_id = $current_user->ID;

		return $wpdb->query( $wpdb->prepare( "DELETE FROM " . $bp['blogs']['table_name_blog_posts'] . " WHERE user_id = %d AND blog_id = %d AND post_id = %d", $user_id, $blog_id, $post_id ) );
	}
	
	function delete_oldest( $user_id = null ) {
		global $wpdb, $bp;
		
		if ( !$user_id )
			$user_id = $current_user->ID;
			
		return $wpdb->query( $wpdb->prepare( "DELETE FROM " . $bp['blogs']['table_name_blog_posts'] . " WHERE user_id = %d ORDER BY date_created ASC LIMIT 1", $user_id ) ); 		
	}
	
	function delete_posts_for_user( $user_id = null ) {
		global $wpdb, $bp;

		if ( !$user_id )
			$user_id = $bp['loggedin_userid'];

		return $wpdb->query( $wpdb->prepare( "DELETE FROM " . $bp['blogs']['table_name_blog_posts'] . " WHERE user_id = %d", $user_id ) );
	}
	
	function delete_posts_for_blog( $blog_id ) {
		global $wpdb, $bp;
		
		return $wpdb->query( $wpdb->prepare( "DELETE FROM " . $bp['blogs']['table_name_blog_posts'] . " WHERE blog_id = %d", $blog_id ) );
	}
	
	function get_latest_posts( $blog_id = null, $limit = 5 ) {
		global $wpdb, $bp;
		
		if ( $blog_id )
			$blog_sql = $wpdb->prepare( " WHERE blog_id = %d", $blog_id );
		
		$post_ids = $wpdb->get_results( $wpdb->prepare( "SELECT post_id, blog_id FROM " . $bp['blogs']['table_name_blog_posts'] . "$blog_sql ORDER BY date_created DESC LIMIT $limit" ) );

		for ( $i = 0; $i < count($post_ids); $i++ ) {
			$posts[$i] = BP_Blogs_Post::fetch_post_content($post_ids[$i]);
		}
		
		return $posts;
	}
	
	function get_posts_for_user( $user_id = null ) {
		global $bp, $wpdb;
		
		if ( !$user_id )
			$user_id = $bp['current_userid'];
			
		$post_ids = $wpdb->get_results( $wpdb->prepare( "SELECT post_id, blog_id FROM " . $bp['blogs']['table_name_blog_posts'] . " WHERE user_id = %d ORDER BY date_created DESC", $user_id) );
		$total_post_count = $wpdb->get_var( $wpdb->prepare( "SELECT count(post_id) FROM " . $bp['blogs']['table_name_blog_posts'] . " WHERE user_id = %d", $user_id) );
		
		for ( $i = 0; $i < count($post_ids); $i++ ) {
			$posts[$i] = BP_Blogs_Post::fetch_post_content($post_ids[$i]);
		}

		return array( 'posts' => $posts, 'count' => $total_post_count );
	}
	
	function fetch_post_content( $post_object ) {
		global $current_blog;
		
		// TODO: switch_to_blog() calls are expensive and this needs to be changed.
		switch_to_blog( $post_object->blog_id );
		$post = get_post($post_object->post_id);
		$post->blog_id = $post_object->blog_id;
		switch_to_blog( $current_blog->blog_id );

		return $post;
	}
	
	function get_total_recorded_for_user( $user_id = null ) {
		global $bp, $wpdb;
		
		if ( !$user_id )
			$user_id = $current_user->ID;

		return $wpdb->get_var( $wpdb->prepare( "SELECT count(post_id) FROM " . $bp['blogs']['table_name_blog_posts'] . " WHERE user_id = %d", $user_id ) );
	}
	
	function is_recorded( $post_id, $blog_id, $user_id = null ) {
		global $bp, $wpdb, $current_user;
		
		if ( !$user_id )
			$user_id = $current_user->ID;
		
		return $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM " . $bp['blogs']['table_name_blog_posts'] . " WHERE post_id = %d AND blog_id = %d AND user_id = %d", $post_id, $blog_id, $user_id ) );
	}
	
	function get_all() {
		global $bp, $wpdb;
		
		return $wpdb->get_col( $wpdb->prepare( "SELECT post_id, blog_id FROM " . $bp['blogs']['table_name_blog_posts'] ) );
	}
	
}

Class BP_Blogs_Comment {
	var $id;
	var $user_id;
	var $blog_id;
	var $comment_id;
	var $comment_post_id;
	var $date_created;
	
	function bp_blogs_comment( $id = null ) {
		global $bp, $wpdb;

		if ( !$user_id )
			$user_id = $bp['current_userid'];
			
		if ( $id ) {
			$this->id = $id;
			$this->populate( $id );
		}
	}

	function populate( $id ) {
		global $wpdb, $bp;
		
		$comment = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $bp['blogs']['table_name_blog_comments'] . " WHERE id = %d", $this->id ) );

		$this->comment_id = $comment->comment_id;
		$this->user_id = $comment->user_id;
		$this->blog_id = $comment->blog_id;
		$this->comment_post_id = $comment->comment_post_id;
		$this->date_created = $comment->date_created;
	}
	
	function save() {
		global $wpdb, $bp;
		
		if ( $this->id ) {
			// Update
			$sql = $wpdb->prepare( "UPDATE " . $bp['blogs']['table_name_blog_comments'] . " SET comment_id = %d, comment_post_id = %d, blog_id = %d, user_id = %d, date_created = FROM_UNIXTIME(%d) WHERE id = %d", $this->comment_id, $this->comment_post_id, $this->blog_id, $this->user_id, $this->date_created, $this->id );
		} else {
			// Save
			$sql = $wpdb->prepare( "INSERT INTO " . $bp['blogs']['table_name_blog_comments'] . " ( comment_id, comment_post_id, blog_id, user_id, date_created ) VALUES ( %d, %d, %d, %d, FROM_UNIXTIME(%d) )", $this->comment_id, $this->comment_post_id, $this->blog_id, $this->user_id, $this->date_created );
		}

		if ( !$wpdb->query($sql) )
			return false;
		
		if ( $this->id )
			return $this->id;
		else
			return $wpdb->insert_id;	
	}

	/* Static Functions */
	
	function delete( $comment_id, $blog_id, $user_id = null ) {
		global $wpdb, $bp, $current_user;

		if ( !$user_id )
			$user_id = $current_user->ID;
			
		return $wpdb->query( $wpdb->prepare( "DELETE FROM " . $bp['blogs']['table_name_blog_comments'] . " WHERE comment_id = %d AND blog_id = %d AND user_id = %d", $comment_id, $blog_id, $user_id ) );
	}
	
	function delete_oldest( $user_id = null ) {
		global $wpdb, $bp, $current_user;
		
		if ( !$user_id )
			$user_id = $current_user->ID;
			
		return $wpdb->query( $wpdb->prepare( "DELETE FROM " . $bp['blogs']['table_name_blog_comments'] . " WHERE user_id = %d ORDER BY date_created ASC LIMIT 1", $user_id ) ); 		
	}
	
	function delete_comments_for_user( $user_id = null ) {
		global $wpdb, $bp;

		if ( !$user_id )
			$user_id = $bp['loggedin_userid'];

		return $wpdb->query( $wpdb->prepare( "DELETE FROM " . $bp['blogs']['table_name_blog_comments'] . " WHERE user_id = %d", $user_id ) );
	}
	
	function delete_comments_for_blog( $blog_id ) {
		global $wpdb, $bp;
		
		return $wpdb->query( $wpdb->prepare( "DELETE FROM " . $bp['blogs']['table_name_blog_comments'] . " WHERE blog_id = %d", $blog_id ) );
	}
	
	function get_comments_for_user( $user_id = null ) {
		global $bp, $wpdb;

		if ( !$user_id )
			$user_id = $bp['current_userid'];
			
		$comment_ids = $wpdb->get_results( $wpdb->prepare( "SELECT comment_id, blog_id FROM " . $bp['blogs']['table_name_blog_comments'] . " WHERE user_id = %d ORDER BY date_created ASC", $user_id) );
		$total_comment_count = $wpdb->get_var( $wpdb->prepare( "SELECT count(comment_id) FROM " . $bp['blogs']['table_name_blog_comments'] . " WHERE user_id = %d", $user_id) );
		
		for ( $i = 0; $i < count($comment_ids); $i++ ) {
			$comments[$i] = BP_Blogs_Comment::fetch_comment_content($comment_ids[$i]);
		}

		return array( 'comments' => $comments, 'count' => $total_comment_count );
	}
	
	function fetch_comment_content( $comment_object ) {
		global $current_blog;
		
		switch_to_blog($comment_object->blog_id);
		$comment = get_comment($comment_object->comment_id);
		$comment->blog_id = $comment_object->blog_id;
		$comment->post = &get_post( $comment->comment_post_ID );
		switch_to_blog($current_blog->blog_id);
		
		return $comment;
	}
	
	function get_total_recorded_for_user( $user_id = null ) {
		global $bp, $wpdb, $current_user;
		
		if ( !$user_id )
			$user_id = $current_user->ID;

		return $wpdb->get_var( $wpdb->prepare( "SELECT count(comment_id) FROM " . $bp['blogs']['table_name_blog_comments'] . " WHERE user_id = %d", $user_id ) );
	}
	
	function is_recorded( $comment_id, $comment_post_id, $blog_id, $user_id = null ) {
		global $bp, $wpdb, $current_user;
		
		if ( !$user_id )
			$user_id = $current_user->ID;
		
		return $wpdb->get_var( $wpdb->prepare( "SELECT comment_id FROM " . $bp['blogs']['table_name_blog_comments'] . " WHERE comment_id = %d AND blog_id = %d AND comment_post_id = %d AND user_id = %d", $comment_id, $blog_id, $comment_post_id, $user_id ) );
	}
	
}

?>