<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Enqueue Notifications assets earlier
 */
function um_enqueue_feed_scripts() {
	if ( ! is_user_logged_in() || is_admin() ) {
		return;
	}

	if ( um_is_core_page( 'notifications' ) ) {
		return;
	}

	wp_enqueue_script( 'um_notifications' );
	wp_enqueue_style( 'um_notifications' );
}
add_action( 'wp_footer', 'um_enqueue_feed_scripts', -1 );


/**
 * Show Notifications Bell + Sidebar in footer
 */
function um_notification_show_feed() {
	if ( ! is_user_logged_in() || is_admin() ) {
		return;
	}

	if ( um_is_core_page( 'notifications' ) ) {
		return;
	}

	UM()->get_template( 'feed.php', um_notifications_plugin, array(), true );
}
add_action( 'wp_footer', 'um_notification_show_feed', 99999999999 );
