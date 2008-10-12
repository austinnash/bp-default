<?php
/**
 * bp_get_nav()
 * TEMPLATE TAG
 *
 * Uses the $bp['bp_nav'] global to render out the navigation within a BuddyPress install.
 * Each component adds to this navigation array within its own [component_name]_setup_nav() function.
 * 
 * This navigation array is the top level navigation, so it contains items such as:
 *      [Blog, Profile, Messages, Groups, Friends] ...
 *
 * The function will also analyze the current component the user is in, to determine whether
 * or not to highlight a particular nav item.
 *
 * It will also compare the current user to the logged in user, if a user profile is being viewed.
 * This allows the "Friends" item to be highlighted if the users are friends. This is only if the friends
 * component is installed.
 * 
 * @package BuddyPress Core
 * @global $bp The global BuddyPress settings variable created in bp_core_setup_globals()
 * @uses friends_check_friendship() Checks to see if the logged in user is a friend of the currently viewed user.
 */
function bp_get_nav() {
	global $bp, $current_blog;
	
	/* Sort the nav by key as the array has been put together in different locations */
	$bp['bp_nav'] = bp_core_sort_nav_items( $bp['bp_nav'] );

	/* Loop through each navigation item */
	foreach( (array) $bp['bp_nav'] as $nav_item ) {
		/* If the current component matches the nav item id, then add a highlight CSS class. */
		if ( $bp['current_component'] == $nav_item['css_id'] && bp_is_home() ) {
			$selected = ' class="current"';
		} else {
			$selected = '';
		}
		
		/* If we are viewing another person (current_userid does not equal loggedin_userid)
		   then check to see if the two users are friends. if they are, add a highligh CSS class
		   to the friends nav item if it exists. */
		if ( !bp_is_home() ) {
			if ( function_exists('friends_check_friendship') ) {
				if ( friends_check_friendship( $bp['current_userid'] ) == 'is_friend' && $nav_item['css_id'] == $bp['friends']['slug'] ) {
					$selected = ' class="current"';
				} else {
					$selected = '';
				}
			}
		}
		
		/* echo out the final list item */
		echo '<li' . $selected . '><a id="' . $nav_item['css_id'] . '" href="' . $nav_item['link'] . '">' . $nav_item['name'] . '</a></li>';
	}
	
	/* Always add a log out list item to the end of the navigation */
	echo '<li><a id="wp-logout" href="' . site_url() . '/wp-login.php?action=logout">Log Out</a><li>';
}

/**
 * bp_get_options_nav()
 * TEMPLATE TAG
 *
 * Uses the $bp['bp_options_nav'] global to render out the sub navigation for the current component.
 * Each component adds to its sub navigation array within its own [component_name]_setup_nav() function.
 * 
 * This sub navigation array is the secondary level navigation, so for profile it contains:
 *      [Public, Edit Profile, Change Avatar]
 *
 * The function will also analyze the current action for the current component to determine whether
 * or not to highlight a particular sub nav item.
 * 
 * @package BuddyPress Core
 * @global $bp The global BuddyPress settings variable created in bp_core_setup_globals()
 * @uses bp_get_user_nav() Renders the navigation for a profile of a currently viewed user.
 */
function bp_get_options_nav() {
	global $bp, $is_single_group;

	/* Only render this navigation when the logged in user is looking at one of their own pages. */
	if ( bp_is_home() || $is_single_group ) {
		if ( count( $bp['bp_options_nav'][$bp['current_component']] ) < 1 )
			return false;
	
		/* Loop through each navigation item */
		foreach ( $bp['bp_options_nav'][$bp['current_component']] as $slug => $values ) {
			$title = $values['name'];
			$link = $values['link'];
			$css_id = $values['css_id'];
			
			/* If the current action or an action variable matches the nav item id, then add a highlight CSS class. */
			if ( $slug == $bp['current_action'] || in_array( $slug, $bp['action_variables'] ) ) {
				$selected = ' class="current"';
			} else {
				$selected = '';
			}
			
			/* echo out the final list item */
			echo '<li' . $selected . '><a id="' . $css_id . '" href="' . $link . '">' . $title . '</a></li>';		
		}
	} else {
		if ( !$bp['bp_users_nav'] )
			return false;

		bp_get_user_nav();
	}
}

/**
 * bp_get_user_nav()
 * TEMPLATE TAG
 *
 * Uses the $bp['bp_users_nav'] global to render out the user navigation when viewing another user other than
 * yourself.
 *
 * @package BuddyPress Core
 * @global $bp The global BuddyPress settings variable created in bp_core_setup_globals()
 */
function bp_get_user_nav() {
	global $bp;

	/* Sort the nav by key as the array has been put together in different locations */	
	$bp['bp_users_nav'] = bp_core_sort_nav_items( $bp['bp_users_nav'] );

	foreach ( $bp['bp_users_nav'] as $user_nav_item ) {	
		if ( $bp['current_component'] == $user_nav_item['css_id'] ) {
			$selected = ' class="current"';
		} else {
			$selected = '';
		}
		
		echo '<li' . $selected . '><a id="' . $user_nav_item['css_id'] . '" href="' . $user_nav_item['link'] . '">' . $user_nav_item['name'] . '</a></li>';
	}	
}

/**
 * bp_has_options_avatar()
 * TEMPLATE TAG
 *
 * Check to see if there is an options avatar. An options avatar is an avatar for something
 * like a group, or a friend. Basically an avatar that appears in the sub nav options bar.
 *
 * @package BuddyPress Core
 * @global $bp The global BuddyPress settings variable created in bp_core_setup_globals()
 */
function bp_has_options_avatar() {
	global $bp;
	
	if ( $bp['bp_options_avatar'] == '' )
		return false;
	
	return true;
}

/**
 * bp_get_options_avatar()
 * TEMPLATE TAG
 *
 * Gets the avatar for the current sub nav (eg friends avatar or group avatar).
 * Does not check if there is one - so always use if ( bp_has_options_avatar() )
 *
 * @package BuddyPress Core
 * @global $bp The global BuddyPress settings variable created in bp_core_setup_globals()
 */
function bp_get_options_avatar() {
	global $bp;

	echo $bp['bp_options_avatar'];
}

function bp_get_options_title() {
	global $bp;
	
	if ( $bp['bp_options_title'] == '' )
		$bp['bp_options_title'] = __('Options', 'buddypress');
	
	echo $bp['bp_options_title'];
}

function bp_is_home() {
	global $bp;
	
	if ( !is_user_logged_in() || is_null($bp['loggedin_userid']) || is_null($bp['current_userid']) )
		return false;
	
	if ( $bp['loggedin_userid'] == $bp['current_userid'] )
		return true;

	return false;
}

function bp_comment_author_avatar() {
	global $comment;
	
	if ( function_exists('bp_core_get_avatar') ) {
		echo bp_core_get_avatar( $comment->user_id, 1 );	
	} else if ( function_exists('get_avatar') ) {
		get_avatar();
	}
}

function bp_exists( $component_name ) {
	if ( function_exists($component_name . '_install') )
		return true;
	
	return false;
}

function bp_format_time( $time, $just_date = false ) {
	$date = date( "F j, Y ", $time );
	
	if ( !$just_date ) {
		$date .= __('at', 'buddypress') . date( ' g:iA', $time );
	}
	
	return $date;
}

function bp_my_or_name( $capitalize = true, $echo = true ) {
	global $bp;
	
	$my = __('my', 'buddypress');
	
	if ( $capitalize )
		$my = ucfirst($my);
	
	if ( $bp['current_userid'] == $bp['loggedin_userid'] ) {
		if ( $echo )
			echo $my;
		else
			return $my;
	} else {
		if ( $echo )
			echo $bp['current_fullname'] . "'s";
		else
			return $bp['current_fullname'] . "'s";
	}
}

function bp_you_or_name( $capitalize = true, $echo = true ) {
	global $bp;
	
	$you = __('you haven\'t', 'buddypress');
	
	if ( $capitalize )
		$you = ucfirst($you);
	
	if ( $bp['current_userid'] == $bp['loggedin_userid'] ) {
		if ( $echo )
			echo $you;
		else
			return $you;
	} else {
		if ( $echo )
			echo $bp['current_fullname'] . " hasn't";
		else
			return $bp['current_fullname'] . " hasn't";
	}
}

function bp_your_or_name( $capitalize = true, $echo = true ) {
	global $bp;
	
	$your = __('your', 'buddypress');
	
	if ( $capitalize )
		$your = ucfirst($your);
	
	if ( $bp['current_userid'] == $bp['loggedin_userid'] ) {
		if ( $echo )
			echo $your;
		else
			return $your;
	} else {
		if ( $echo )
			echo $bp['current_fullname'] . "'s";
		else
			return $bp['current_fullname'] . "'s";
	}
}

function bp_your_or_their( $capitalize = false, $echo = false ) {
	global $bp;
	
	$your = __('your', 'buddypress');
	$their = __('their', 'buddypress');
	
	if ( $capitalize )
		$your = ucfirst($your);
	
	if ( $bp['current_userid'] == $bp['loggedin_userid'] ) {
		if ( $echo )
			echo $your;
		else
			return $your;
	} else {
		if ( $echo )
			echo $their;
		else
			return $their;
	}
}

function bp_loggedinuser_link() {
	global $bp, $current_user;
	
	if ( $link = bp_core_get_userlink( $bp['loggedin_userid'] ) ) {
		echo $link;
	} else {
		$ud = get_userdata($current_user->ID);
		echo $ud->user_login;
	}
}

/* Template functions for fetching globals, without querying the DB again
   also means we dont have to use the $bp variable in the template (looks messy) */

function bp_current_user_id() {
	global $bp;
	return $bp['current_userid'];
}

function bp_user_fullname() {
	global $bp;
	echo $bp['current_fullname'];
}

?>