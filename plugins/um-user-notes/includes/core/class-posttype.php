<?php
namespace um_ext\um_user_notes\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class PostType
 *
 * @package um_ext\um_user_notes\core
 */
class PostType {


	/**
	 * PostType constructor.
	 */
	function __construct() {
		add_action( 'init', [ $this, 'um_user_notes_post_type' ] );
		add_action( 'init', [ &$this, 'add_image_size' ] );
	}


	/**
	 * Register CPT um_notes
	 */
	function um_user_notes_post_type() {
		$labels = [
			'name'                  => _x( 'Notes', 'Post Type General Name', 'um-user-notes' ),
			'singular_name'         => _x( 'Note', 'Post Type Singular Name', 'um-user-notes' ),
			'menu_name'             => __( 'Notes', 'um-user-notes' ),
			'name_admin_bar'        => __( 'Notes', 'um-user-notes' ),
			'archives'              => __( 'Item Archives', 'um-user-notes' ),
			'attributes'            => __( 'Item Attributes', 'um-user-notes' ),
			'parent_item_colon'     => __( 'Parent Item:', 'um-user-notes' ),
			'all_items'             => __( 'All Items', 'um-user-notes' ),
			'add_new_item'          => __( 'Add New Item', 'um-user-notes' ),
			'add_new'               => __( 'Add New', 'um-user-notes' ),
			'new_item'              => __( 'New Item', 'um-user-notes' ),
			'edit_item'             => __( 'Edit Item', 'um-user-notes' ),
			'update_item'           => __( 'Update Item', 'um-user-notes' ),
			'view_item'             => __( 'View Item', 'um-user-notes' ),
			'view_items'            => __( 'View Items', 'um-user-notes' ),
			'search_items'          => __( 'Search Item', 'um-user-notes' ),
			'not_found'             => __( 'Not found', 'um-user-notes' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'um-user-notes' ),
			'featured_image'        => __( 'Featured Image', 'um-user-notes' ),
			'set_featured_image'    => __( 'Set featured image', 'um-user-notes' ),
			'remove_featured_image' => __( 'Remove featured image', 'um-user-notes' ),
			'use_featured_image'    => __( 'Use as featured image', 'um-user-notes' ),
			'insert_into_item'      => __( 'Insert into item', 'um-user-notes' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'um-user-notes' ),
			'items_list'            => __( 'Items list', 'um-user-notes' ),
			'items_list_navigation' => __( 'Items list navigation', 'um-user-notes' ),
			'filter_items_list'     => __( 'Filter items list', 'um-user-notes' ),
		];

		register_post_type( 'um_notes', [
			'label'                 => __( 'Note', 'um-user-notes' ),
			'description'           => __( 'User notes', 'um-user-notes' ),
			'labels'                => $labels,
			'supports'              => [ 'title', 'editor', 'thumbnail' ],
			'hierarchical'          => false,
			'public'                => false,
			'show_ui'               => false,
			'show_in_menu'          => false,
//			'menu_position'         => 5,
			'show_in_admin_bar'     => false,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => true,
			'rewrite'               => [
				'slug'          => 'user-notes',
				'with_front'    => true,
				'pages'         => true,
				'feeds'         => false,
			],
			'show_in_rest'          => false,
		] );
	}


	/**
	 * Add thumbnail size based on setting
	 */
	function add_image_size() {
		$width = 400;
		$height = 300;

		$image_size = UM()->options()->get( 'um_user_notes_image_size' );

		if ( $image_size ) {

			$list = explode( 'x', strtolower( $image_size ) );

			if ( ! empty( $list ) ) {
				$width = intval( $list[0] );
				$height = intval( $list[1] );
			}

		}
		add_image_size( 'um_notes_thumbnail', $width, $height, true );
	}
}