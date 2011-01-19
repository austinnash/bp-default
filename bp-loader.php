<?php
/*
Plugin Name: BuddyPress
Plugin URI: http://buddypress.org
Description: Social networking in a box. Build a social network for your company, school, sports team or niche community all based on the power and flexibility of WordPress.
Author: The BuddyPress Community
Version: 1.3-bleeding
Author URI: http://buddypress.org/community/members/
Network: true
*/

define( 'BP_VERSION', '1.3-bleeding' );
define( 'BP_DB_VERSION', 3705 );

// Define on which blog ID BuddyPress should run
if ( !defined( 'BP_ROOT_BLOG' ) )
	define( 'BP_ROOT_BLOG', 1 );

// Register BuddyPress themes contained within the bp-themes folder 
register_theme_directory( WP_PLUGIN_DIR . '/buddypress/bp-themes' ); 
	 
// Test to see whether this is a new installation or an upgraded version of BuddyPress 
$bp_db_version = get_site_option( 'bp-db-version' ); 
if ( ! $bp_db_version ) 
	$bp_db_version = get_site_option( 'bp-core-db-version' );  // BP 1.2 option name 
	 
if ( ! $bp_db_version ) {
 	// This is a new installation. Run the wizard before loading BP core files
 	define( 'BP_IS_INSTALL', true ); 
	require_once( WP_PLUGIN_DIR . '/buddypress/bp-core/admin/bp-core-update.php' );
} else {
	/***
	 * This file will load in each BuddyPress component based on which
	 * of the components have been activated on the "BuddyPress" admin menu.
	 */
	require_once( WP_PLUGIN_DIR . '/buddypress/bp-core.php' );
	$bp_deactivated = apply_filters( 'bp_deactivated_components', get_site_option( 'bp-deactivated-components' ) );

	do_action( 'bp_core_loaded' );

	// Activity Streams
	if ( !isset( $bp_deactivated['bp-activity.php'] ) && file_exists( BP_PLUGIN_DIR . '/bp-activity.php') )
		include( BP_PLUGIN_DIR . '/bp-activity.php' );

	// Blog Tracking
	if ( !isset( $bp_deactivated['bp-blogs.php'] ) && file_exists( BP_PLUGIN_DIR . '/bp-blogs.php') )
		include( BP_PLUGIN_DIR . '/bp-blogs.php' );

	// bbPress Forum Integration
	if ( !isset( $bp_deactivated['bp-forums.php'] ) && file_exists( BP_PLUGIN_DIR . '/bp-forums.php') )
		include( BP_PLUGIN_DIR . '/bp-forums.php' );

	// Friend Connections
	if ( !isset( $bp_deactivated['bp-friends.php'] ) && file_exists( BP_PLUGIN_DIR . '/bp-friends.php') )
		include( BP_PLUGIN_DIR . '/bp-friends.php' );

	// Groups Support
	if ( !isset( $bp_deactivated['bp-groups.php'] ) && file_exists( BP_PLUGIN_DIR . '/bp-groups.php') )
		include( BP_PLUGIN_DIR . '/bp-groups.php' );

	// Private Messaging
	if ( !isset( $bp_deactivated['bp-messages.php'] ) && file_exists( BP_PLUGIN_DIR . '/bp-messages.php') )
		include( BP_PLUGIN_DIR . '/bp-messages.php' );

	// Extended Profiles
	if ( !isset( $bp_deactivated['bp-xprofile.php'] ) && file_exists( BP_PLUGIN_DIR . '/bp-xprofile.php') )
		include( BP_PLUGIN_DIR . '/bp-xprofile.php' );
		
	// If this is an upgrade, load the upgrade file 
 	if ( $bp_db_version < constant( 'BP_DB_VERSION' ) ) { 
 		define( 'BP_IS_UPGRADE', true ); 
 		require_once( WP_PLUGIN_DIR . '/buddypress/bp-core/admin/bp-core-update.php' ); 
 	} 
}

/********************************************************************************
 * Custom Actions
 *
 * Functions to set up custom BuddyPress actions that all other components can
 * hook in to.
 */

/**
 * Allow plugins to include their files ahead of core filters
 */
function bp_include() {
	do_action( 'bp_include' );
}
add_action( 'bp_loaded', 'bp_include', 2 );

/**
 * Allow core components and dependent plugins to set root components
 */
function bp_setup_root_components() {
	do_action( 'bp_setup_root_components' );
}
add_action( 'bp_init', 'bp_setup_root_components', 2 );

/**
 * Allow core components and dependent plugins to set globals
 */
function bp_setup_globals() {
	do_action( 'bp_setup_globals' );
}
add_action( 'bp_init', 'bp_setup_globals', 6 );

/**
 * Allow core components and dependent plugins to set their nav
 */
function bp_setup_nav() {
	do_action( 'bp_setup_nav' );
}
add_action( 'bp_init', 'bp_setup_nav', 8 );

/**
 * Allow core components and dependent plugins to register widgets
 */
function bp_setup_widgets() {
	do_action( 'bp_register_widgets' );
}
add_action( 'bp_init', 'bp_setup_widgets', 8 );

/**
 * Allow components to initialize themselves cleanly
 */
function bp_init() {
	do_action( 'bp_init' );
}
add_action( 'init', 'bp_init' );

/**
 * Attached to plugins_loaded
 */
function bp_loaded() {
	do_action( 'bp_loaded' );
}
add_action( 'plugins_loaded', 'bp_loaded', 10 );

/** 
 * Defines BP's activation routine. 
 * 
 * Most of BP's crucial setup is handled by the setup wizard. This function takes care of some 
 * issues with incompatible legacy themes, and provides a hook for other functions to know that 
 * BP has been activated. 
 * 
 * @package BuddyPress Core 
*/ 
function bp_loader_activate() {
	// Force refresh theme roots.
	delete_site_transient( 'theme_roots' );

	if ( !function_exists( 'get_blog_option' ) )
		require ( WP_PLUGIN_DIR . '/buddypress/bp-core/bp-core-wpabstraction.php' );

	// Switch the user to the new bp-default if they are using the old
	// bp-default on activation.
	if ( 'bp-sn-parent' == get_blog_option( BP_ROOT_BLOG, 'template' ) && 'bp-default' == get_blog_option( BP_ROOT_BLOG, 'stylesheet' ) )
		switch_theme( 'bp-default', 'bp-default' );

	do_action( 'bp_loader_activate' );
}
register_activation_hook( 'buddypress/bp-loader.php', 'bp_loader_activate' );

// Deactivation Function
function bp_loader_deactivate() {
	do_action( 'bp_loader_deactivate' );
}
register_deactivation_hook( 'buddypress/bp-loader.php', 'bp_loader_deactivate' );

?>
