<?php
namespace um_ext\um_user_notes\admin;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'um_ext\um_user_notes\admin\Admin' ) ) {


	/**
	 * Class Admin
	 *
	 * @package um_ext\um_user_notes\admin
	 */
	class Admin {


		/**
		 * Admin constructor.
		 */
		function __construct() {
			add_filter( 'um_settings_structure', [ &$this, 'extend_settings' ], 10, 1 );
			add_filter( 'um_admin_role_metaboxes', [ $this, 'um_user_notes_add_role_metabox' ], 10, 1 );
		}


		/**
		 * Additional Settings for Notes
		 *
		 * @param array $settings
		 *
		 * @return array
		 */
		function extend_settings( $settings ) {

			$settings['licenses']['fields'][] = [
				'id'        => 'um_user_notes_license_key',
				'label'     => __( 'User Notes License Key', 'um-user-notes' ),
				'item_name' => 'User Notes',
				'author'    => 'ultimatemember',
				'version'   => um_user_notes_version,
			];

			$settings['extensions']['sections']['um-user-notes'] = [
				'title'     => __( 'User Notes', 'um-user-notes' ),
				'fields'    => [
					[
						'id'            => 'um_user_notes_per_page',
						'type'          => 'text',
						'placeholder'   => '',
						'label'         => __( 'Notes per page', 'um-user-notes' ),
						'size'          => 'medium',
					],
					[
						'id'            => 'um_user_notes_image_size',
						'type'          => 'text',
						'placeholder'   => '400x300',
						'label'         => __( 'Thumbnail image size', 'um-user-notes' ),
						'size'          => 'medium',
					],
					[
						'id'            => 'um_user_notes_excerpt_length',
						'type'          => 'text',
						'placeholder'   => 'Number of charactes to display.',
						'label'         => __( 'Excerpt length', 'um-user-notes' ),
						'size'          => 'medium'
					],
					[
						'id'            => 'um_user_notes_read_more_text',
						'type'          => 'text',
						'placeholder'   => '',
						'label'         => __( 'Read more text', 'um-user-notes' ),
						'size'          => 'medium',
					],
					[
						'id'            => 'um_user_notes_load_more_text',
						'type'          => 'text',
						'placeholder'   => '',
						'label'         => __( 'Load more text', 'um-user-notes' ),
						'size'          => 'medium',
					],
				],
			];

			return $settings;
		}


		/**
		 * @param array $roles_metaboxes
		 *
		 * @return array
		 */
		function um_user_notes_add_role_metabox( $roles_metaboxes ) {
			$roles_metaboxes[] = [
				'id'        => "um-admin-form-notes{" . um_user_notes_path . "}",
				'title'     => __( 'User Notes', 'um-user-notes' ),
				'callback'  => [ UM()->metabox(), 'load_metabox_role' ],
				'screen'    => 'um_role_meta',
				'context'   => 'normal',
				'priority'  => 'default',
			];

			return $roles_metaboxes;
		}
	}
}