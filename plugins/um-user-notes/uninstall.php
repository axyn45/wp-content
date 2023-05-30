<?php
/**
* Uninstall UM Notes
*
*/

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;


if ( ! defined( 'um_user_notes_path' ) ) {
	define( 'um_user_notes_path', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'um_user_notes_url' ) ) {
	define( 'um_user_notes_url', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'um_user_notes_plugin' ) ) {
	define( 'um_user_notes_plugin', plugin_basename( __FILE__ ) );
}

$options = get_option( 'um_options', [] );
if ( ! empty( $options['uninstall_on_delete'] ) ) {

	if ( ! class_exists( 'um_ext\um_user_notes\core\Setup' ) ) {
		require_once um_user_notes_path . 'includes/core/class-setup.php';
	}

	$user_notes_setup = new um_ext\um_user_notes\core\Setup();

	//remove settings
	foreach ( $user_notes_setup->settings_defaults as $k => $v ) {
		unset( $options[ $k ] );
	}

	unset( $options['um_user_notes_license_key'] );

	update_option( 'um_options', $options );

	$um_notes = get_posts( [
		'post_type'     => [
			'um_notes'
		],
		'numberposts'   => -1
	] );
	foreach ( $um_notes as $um_note ) {
		$attachments = get_attached_media( 'image', $um_note->ID );
		foreach ( $attachments as $attachment ){
			wp_delete_attachment( $attachment->ID, 1 );
		}
		wp_delete_post( $um_note->ID, 1 );
	}

	delete_option( 'um_user_notes_last_version_upgrade' );
	delete_option( 'um_user_notes_version' );
}