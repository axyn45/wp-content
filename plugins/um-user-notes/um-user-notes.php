<?php
/*
Plugin Name: Ultimate Member - User Notes
Plugin URI: http://ultimatemember.com/extensions/user-notes
Description: Let users create notes via their profile.
Version: 1.0.7
Author: Ultimate Member
Author URI: http://ultimatemember.com/
Text Domain: um-user-notes
Domain Path: /languages
UM version: 2.1.8
*/

if ( ! defined('ABSPATH') ) exit;

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

$plugin_data = get_plugin_data( __FILE__ );

define( 'um_user_notes_url', plugin_dir_url( __FILE__ ) );
define( 'um_user_notes_path', plugin_dir_path( __FILE__ ) );
define( 'um_user_notes_plugin', plugin_basename( __FILE__  ) );
define( 'um_user_notes_extension', $plugin_data['Name'] );
define( 'um_user_notes_version', $plugin_data['Version'] );
define( 'um_user_notes_textdomain', 'um-user-notes' );
define( 'um_user_notes_requires', '2.1.8' );


function um_user_notes_plugins_loaded() {
	$locale = ( get_locale() != '' ) ? get_locale() : 'en_US';
	load_textdomain( um_user_notes_textdomain, WP_LANG_DIR . '/plugins/' . um_user_notes_textdomain . '-' . $locale . '.mo' );
	load_plugin_textdomain( um_user_notes_textdomain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'um_user_notes_plugins_loaded', 0 );


add_action( 'plugins_loaded', 'um_user_notes_check_dependencies', -20 );

if ( ! function_exists( 'um_user_notes_check_dependencies' ) ) {

	function um_user_notes_check_dependencies() {
		
		if ( ! defined( 'um_path' ) || ! file_exists( um_path  . 'includes/class-dependencies.php' ) ) {
			
			//UM is not installed
			function um_user_notes_dependencies() {
				
				echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-user-notes' ), um_user_notes_extension ) . '</p></div>';
				
			}

			add_action( 'admin_notices', 'um_user_notes_dependencies' );
			
		} else {

			if ( ! function_exists( 'UM' ) ) {
				require_once um_path . 'includes/class-dependencies.php';
				$is_um_active = um\is_um_active();
			} else {
				$is_um_active = UM()->dependencies()->ultimatemember_active_check();
			}

			if ( ! $is_um_active ) {
				//UM is not active
				function um_user_notes_dependencies() {
					echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-user-notes' ), um_user_notes_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_user_notes_dependencies' );

			} elseif ( true !== UM()->dependencies()->compare_versions( um_user_notes_requires, um_user_notes_version, 'user-notes', um_user_notes_extension ) ) {
				//UM old version is active
				function um_user_notes_dependencies() {
					echo '<div class="error"><p>' . UM()->dependencies()->compare_versions( um_user_notes_requires, um_user_notes_version, 'user-notes', um_user_notes_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_user_notes_dependencies' );

			} else {

				require_once um_user_notes_path . 'includes/core/um-user-notes-functions.php';
				require_once um_user_notes_path . 'includes/core/um-user-notes-init.php';

			}
		}
	}
}


register_activation_hook( um_user_notes_plugin, 'um_user_notes_activation_hook' );

if ( ! function_exists( 'um_user_notes_activation_hook' ) ) {
	function um_user_notes_activation_hook() {
		//first install
		$version = get_option( 'um_user_notes_version' );
		if ( ! $version ) {
			update_option( 'um_user_notes_last_version_upgrade', um_user_notes_version );
		}

		if ( $version != um_user_notes_version ) {
			update_option( 'um_user_notes_version', um_user_notes_version );
		}

		//run setup
		if ( ! class_exists( 'um_ext\um_user_notes\core\Setup' ) ) {
			require_once um_user_notes_path . 'includes/core/class-setup.php';
		}

		$user_notes_setup = new um_ext\um_user_notes\core\Setup();
		$user_notes_setup->run_setup();
	}
}