<?php
namespace um_ext\um_user_notes\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Profile
 * @package um_ext\um_user_notes\core
 */
class Profile {


	/**
	 * @var bool
	 */
	var $modal_inited = false;


	/**
	 * Profile constructor.
	 */
	function __construct() {

		add_filter( 'um_profile_tabs', [ $this, 'add_profile_tab' ], 802 );
		add_filter( 'um_user_profile_tabs', [ &$this, 'add_user_tab' ], 5, 1 );

		add_action( 'um_profile_content_notes_default', [ $this, 'get_notes_content' ] );
		add_action( 'um_profile_content_notes_view', [ $this, 'get_notes_content' ] );

		if ( um_is_profile_owner() ) {

			add_action( 'um_profile_content_notes_add', [ $this, 'get_add_note_form' ] );
			add_action( 'um_profile_content_notes_view', [ $this, 'get_notes_content' ] );

		}

		add_action( 'um_delete_user', [ $this, 'delete_user' ], 10, 1 );

		add_filter( 'um_notes_query_args', [ $this, 'change_query' ], 20, 2 );
		add_filter( 'um_notes_oembed', [ $this, 'oembed' ] );
	}


	/**
	 * Add notes tab on user profile
	 *
	 * @param $tabs
	 *
	 * @return mixed
	 */
	function add_profile_tab( $tabs ) {
		$tabs['notes'] = [
			'name'  => __( 'Notes', 'um-user-notes' ),
			'icon'  => 'um-faicon-sticky-note',
		];

		return $tabs;
	}


	function add_user_tab( $tabs ) {
		if ( empty( $tabs['notes'] ) ) {
			return $tabs;
		}
		if ( um_user( 'disable_notes' ) ) {
			unset( $tabs['notes'] );
			return $tabs;
		}
		if ( ! UM()->Notes()->can_view( um_profile_id() ) ) {
			unset( $tabs['notes'] );
		} else {
			if ( um_is_profile_owner() ) {
				$tabs['notes']['subnav'] = [
					'view'  => __( 'View notes', 'um-user-notes' ),
					'add'   => __( 'Add note', 'um-user-notes' )
				];
				$tabs['notes']['subnav_default'] = 'view';
			}
		}

		return $tabs;
	}


	/**
	 * Returns user profile notes tab content
	 */
	function get_notes_content() {
		UM()->Notes()->enqueue()->enqueue_scripts();

		$profile_id = um_profile_id();

		$per_page = UM()->Notes()->get_per_page( true );

		$next_page_args = [
			'post_type'         => 'um_notes',
			'author__in'        => $profile_id,
			'posts_per_page'    => $per_page + 1
		];
		$next_page_args = apply_filters( 'um_notes_query_args', $next_page_args, $profile_id );
		$next_page = new \WP_Query( $next_page_args );
		$total = $next_page->post_count;


		$args = [
			'post_type'         => 'um_notes',
			'author__in'        => $profile_id,
			'posts_per_page'    => $per_page
		];
		$args = apply_filters( 'um_notes_query_args', $args, $profile_id );
		$latest_notes = new \WP_Query( $args );

		ob_start();

		if ( $latest_notes->have_posts() ) {

			$i = 1; ?>

			<div class="um-notes-holder">

				<?php while ( $latest_notes->have_posts() ) {
					$latest_notes->the_post();
					$float = ( $i % 2 == 0 ) ? 'right' : 'left';

					UM()->get_template( 'profile/note.php', um_user_notes_plugin, [
						'float' => $float,
						'id'    => get_the_id()
					], true );

					if ( $i % 2 == 0 ) {
						echo '<div class="um-clear"></div>';
					}

					$i ++;
				} ?>

			</div>

			<?php if ( $total > $per_page ) {
				UM()->get_template( 'profile/load-more.php', um_user_notes_plugin, [], true );
			}

		} else {
			UM()->get_template( 'profile/empty.php', um_user_notes_plugin, [], true );
		} ?>

		<div class="um-clear"></div>

		<?php ob_end_flush();
	}


	/**
	 * Return note add form on user profile subtab
	 */
	function get_add_note_form() {
		UM()->Notes()->enqueue()->enqueue_scripts();

		if ( um_is_profile_owner() ) {

			UM()->get_template( 'profile/add.php', um_user_notes_plugin, [], true );

		} else {

			$this->get_notes_content();

		}
	}


	/**
	 * Return Note edit form view
	 */
	function get_edit_note_form() {
		UM()->Notes()->enqueue()->enqueue_scripts();

		if ( um_is_profile_owner() ) {

			UM()->get_template( 'profile/edit.php', um_user_notes_plugin, [], true );

		} else {

			$this->get_notes_content();

		}
	}


	/**
	 * Delete All user notes on delete
	 *
	 * @param $user_id
	 */
	function delete_user( $user_id ) {
		$user_notes = get_posts( [
			'posts_per_page'    => -1,
			'post_type'         => 'um_notes',
			'author__in'        => $user_id,
			'fields'            => 'ids',
		] );

		foreach ( $user_notes as $note_id ) {
			wp_delete_post( $note_id, true );
		}
	}


	/**
	 * Filter um_botes query
	 *
	 * @param $args
	 * @param $profile_id
	 *
	 * @return mixed
	 */
	function change_query( $args, $profile_id ) {
		$status = [ 'publish' ];

		if ( is_user_logged_in() ) {
			// user logged in
			$current_user_id = get_current_user_id();

			if ( $current_user_id != $profile_id ) {

				$options = apply_filters( 'um_user_notes_exclude_activity', [ 'only_me' ], $current_user_id );

				$args['meta_query'][] = [
					[
						'key'     => '_privacy',
						'value'   => $options,
						'compare' => 'NOT IN',
					],
				];
			} else {
				$status[] = 'draft';
			}

		} else {
			// user not logged in
			$options = apply_filters( 'um_user_notes_exclude_activity', [ 'only_me' ], null );

			$args['meta_query'][] = [
				'relation' => 'OR',
				[
					'key'     => '_privacy',
					'value'   => $options,
					'compare' => 'NOT IN',
				],
				[
					'key'     => '_privacy',
					'compare' => 'NOT EXISTS',
				],
			];
		}

		$args['post_status'] = $status;

		return $args;
	}


	/**
	 * Add note View / Edit modal to footer for easy availability
	 */
	function add_modal() {
		if ( $this->modal_inited ) {
			return;
		}

		$this->modal_inited = true;

		UM()->get_template( 'profile/modal.php', um_user_notes_plugin, [], true );
	}


	/**
	 * Video URLs oembed
	 */
	function oembed( $content ) {
		preg_match_all( "#(https?://vimeo.com)/([0-9]+)#i", $content, $matches1 );
		preg_match_all( "/(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtube\.com\/watch\?v=[\w_-]{1,11}&ab_channel=[\w_-]{1,40})/", $content, $matches2 );
		preg_match_all( "/(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtube\.com\/watch\?v=[\w_-]{1,11})|(?:http(?:s)?:\/\/)?(?:youtu\.be\/[\w_-]{1,11})/", $content, $matches3 );

		if ( isset( $matches1 ) && ! empty( $matches1[0] ) ) {
			foreach ( $matches1[0] as $key => $val ) {
				$embed_content = wp_oembed_get( $val );
				if ( $embed_content ) {
					$content = str_replace( $val, $embed_content, $content );
				}
			}
		}

		if ( isset( $matches2[0] ) && ! empty( $matches2[0] ) ) {
			foreach ( $matches2[0] as $key => $val ) {
				$embed_content = wp_oembed_get( $val );
				if ( $embed_content ) {
					$content = str_replace( $val, $embed_content, $content );
				}
			}
		}

		if ( isset( $matches3[0] ) && ! empty( $matches3[0] ) ) {
			foreach ( $matches3[0] as $key => $val ) {
				$embed_content = wp_oembed_get( $val );
				if ( $embed_content ) {
					$content = str_replace( $val, $embed_content, $content );
				}
			}
		}

		$arr_urls = wp_extract_urls( $content );

		if ( ! empty( $arr_urls ) && is_array( $arr_urls ) ) {
			foreach ( $arr_urls as $key => $url ) {
				if ( ! strstr( $url, 'vimeo' ) &&
					 ! strstr( $url, 'youtube' ) &&
					 ! strstr( $url, 'youtu.be' ) ) {
					$embed_content = wp_oembed_get( $url );
					if ( $embed_content ) {
						$content = str_replace( $url, $embed_content, $content );
					}
				}
			}
		}

		return $content;
	}
}
