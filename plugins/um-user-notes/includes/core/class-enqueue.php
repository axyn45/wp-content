<?php
namespace um_ext\um_user_notes\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Enqueue
 * @package um_ext\um_user_notes\core
 */
class Enqueue {


	/**
	 * Enqueue constructor.
	 */
	function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ] );
	}


	/**
	 * Enqueue CSS/JS
	 */
	function wp_enqueue_scripts() {
		wp_register_script( 'um-user-notes', um_user_notes_url . 'assets/js/um-user-notes' . UM()->enqueue()->suffix . '.js', [ 'jquery',  'wp-util', 'wp-i18n' ], um_user_notes_version, true );

		wp_set_script_translations( 'um-user-notes', 'um-user-notes' );

		wp_register_style( 'um-user-notes', um_user_notes_url . 'assets/css/um-user-notes' . UM()->enqueue()->suffix . '.css', [], um_user_notes_version );
	}


	/**
	 * Enqueue Notes scripts
	 */
	function enqueue_scripts() {
		wp_enqueue_script( 'um-user-notes' );
		wp_enqueue_style( 'um-user-notes' );

		wp_enqueue_editor();

		// add_action( 'wp_footer', [ UM()->Notes()->profile(), 'add_modal' ], 999999 );
		call_user_func([ UM()->Notes()->profile(), 'add_modal' ]);
	}
}