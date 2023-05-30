<?php
namespace um_ext\um_user_notes\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Setup
 *
 * @package um_ext\um_user_notes\core
 */
class Setup {


	/**
	 * @var array
	 */
	var $settings_defaults;


	/**
	 * Setup constructor.
	 */
	function __construct() {
		//settings defaults
		$this->settings_defaults = [
			'profile_tab_notes'             => true,
			'profile_tab_notes_privacy'     => 0,
			'um_user_notes_per_page'        => 4,
			'um_user_notes_image_size'      => '400x300',
			'um_user_notes_excerpt_length'  => 70,
			'um_user_notes_read_more_text'  => __( 'Read more', 'um-user-notes' ),
			'um_user_notes_load_more_text'  => __( 'Load more', 'um-user-notes' ),
		];
	}


	/**
	 * Set default settings function
	 */
	function set_default_settings() {
		$options = get_option( 'um_options', [] );

		foreach ( $this->settings_defaults as $key => $value ) {
			//set new options to default
			if ( ! isset( $options[ $key ] ) ) {
				$options[ $key ] = $value;
			}
		}

		update_option( 'um_options', $options );
	}


	/**
	 * Run User Notes Setup
	 */
	function run_setup() {
		$this->set_default_settings();
	}

}