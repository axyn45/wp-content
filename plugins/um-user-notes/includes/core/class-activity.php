<?php
namespace um_ext\um_user_notes\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Integration with UM social activity
 *
 * Class Activity
 *
 * @package um_ext\um_user_notes\core
 */
class Activity {


	/**
	 * Activity constructor.
	 */
	function __construct() {
		add_action( 'um_user_notes_after_note_created', [ $this, 'um_social_activity_post' ] );
		add_action( 'um_user_notes_after_note_updated', [ $this, 'um_user_notes_after_note_updated' ] );
		add_action( 'um_user_notes_before_note_deleted', [ $this, 'um_social_activity_post_delete' ] );
		add_filter( 'um_activity_wall_args', [ $this, 'filter_social_activity' ], 30 );

		add_filter( 'um_activity_search_tpl', [ $this, 'um_activity_search_tpl' ], 10, 1 );
		add_filter( 'um_activity_replace_tpl', [ $this, 'um_activity_replace_tpl' ], 10, 2 );

		add_action( 'um_social_activity_enqueue_scripts', [ UM()->Notes()->enqueue(), 'enqueue_scripts' ] );

		add_filter( 'um_activity_global_actions', [ $this, 'add_activity_global_actions' ], 10, 1 );
	}


	/**
	 * @param int $activity
	 * @param int $note_id
	 */
	function update_activity_privacy( $activity, $note_id ) {
		$privacy = get_post_meta( $note_id , '_privacy' , true );

		if ( get_post_status( $note_id ) == 'draft' ) {
			$privacy = 'only_me';
		}

		update_post_meta( $activity , 'um_note_privacy' , $privacy  );
	}


	/**
	 * Create social activity when new note is created
	 *
	 * @param int $note_id
	 */
	function um_social_activity_post( $note_id ) {
		$note = get_post( $note_id );
		if ( $note->post_status != 'draft' ) {
			$user_id = $note->post_author;
			um_fetch_user( $user_id );

			$author_name = um_user('display_name');

			$author_profile = um_user_profile_url();

			$excerpt = substr( strip_tags( $note->post_content ) , 0 , UM()->Notes()->get_excerpt_length( true ) ) . '...';

			$activity = UM()->Activity_API()->api()->save( [
				'template'       => 'new-note',
				'custom_path'    => um_user_notes_path . '/templates/activity/new-note.php',
				'wall_id'        => $user_id,
				'related_id'     => $note_id,
				'author'         => $user_id,
				'author_name'    => $author_name,
				'author_profile' => $author_profile,
				'post_title'     => '<span class="post-title">' . $note->post_title . '</span>',
				'post_url'       => 'javascript:void(0);',
				'post_excerpt'   => '<span class="post-excerpt">' . $excerpt . '</span>',
			] );

			$this->update_activity_privacy( $activity, $note_id );
		}
	}


	/**
	 * Create social activity when note is updated.
	 *
	 * @param int $note_id
	 *
	 */
	function um_user_notes_after_note_updated( $note_id ) {
		if ( ! $note_id ) {
			return;
		}

		$note = get_post( $note_id );

		if ( $note->post_status != 'draft' ) {
			$activities = $this->get_activities( $note_id );

			if ( empty( $activities ) ) {
				do_action( 'um_user_notes_after_note_created', $note_id );
			} else {
				foreach ( $activities as $post ) {

					setup_postdata( $post );

					$note = get_post( $note_id );

					$user_id = $note->post_author;

					um_fetch_user( $user_id );

					$author_name = um_user( 'display_name' );

					$author_profile = um_user_profile_url();

					$excerpt = substr( strip_tags( $note->post_content ), 0, UM()->Notes()->get_excerpt_length( true ) ) . '...';

					$activity = UM()->Activity_API()->api()->save( [
						'template'          => 'new-note',
						'custom_path'       => um_user_notes_path . '/templates/activity/new-note.php',
						'wall_id'           => $user_id,
						'related_id'        => $note_id,
						'author'            => $user_id,
						'author_name'       => $author_name,
						'author_profile'    => $author_profile,
						'post_title'        => '<span class="post-title">' . $note->post_title . '</span>',
						'post_url'          => 'javascript:void(0);',
						'post_excerpt'      => '<span class="post-excerpt">' . $excerpt . '</span>',
					], true, $post->ID );

					$this->update_activity_privacy( $activity, $note_id );
				}
			}

		} else {
			do_action( 'um_user_notes_before_note_deleted', $note_id );
		}

		wp_reset_postdata();
	}


	/**
	 * Delete social activity when note is deleted.
	 *
	 * @param int $note_id
	 *
	 */
	function um_social_activity_post_delete( $note_id = 0 ) {
		if ( ! $note_id ) {
			return;
		}

		$activities = $this->get_activities( $note_id );

		if ( empty( $activities ) ) {
			return;
		}

		foreach ( $activities as $post ) {
			wp_delete_post( $post->ID );
		}
	}


	/**
	 * @param $note_id
	 *
	 * @return int[]|\WP_Post[]
	 */
	function get_activities( $note_id ) {
		$activities = get_posts( [
			'post_type'     => 'um_activity',
			'meta_query'    => [
				[
					'key'       => '_related_id',
					'value'     => $note_id,
					'compare'   => '=',
				],
				[
					'key'       => '_action',
					'value'     => 'new-note',
					'compare'   => '=',
				],
			],
		] );

		return $activities;
	}


	/**
	 * Exclude notes from wall if user does not have access to view notes
	 *
	 * @version 1.0.3
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	function filter_social_activity( $args ) {

		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();

			$exclude_args = array(
				'fields'         => 'ids',
				'posts_per_page' => -1,
				'post_type'      => 'um_activity',
				'post_status'    => 'publish',
				'meta_query'     => array(
					'relation' => 'OR',
					array( // if not me
						'relation' => 'AND',
						array(
							'key'     => 'um_note_privacy',
							'value'   => 'only_me',
							'compare' => '=',
						),
						array(
							'key'     => '_wall_id',
							'value'   => $user_id,
							'compare' => '!=',
						)
					)
				),
			);

			if ( defined( 'um_friends_version' ) ) {
				$friends_array = array();

				$friends = UM()->Friends_API()->api()->friends( $user_id );
				if ( $friends && is_array( $friends ) ) {
					foreach ( $friends as $friend ) {
						$friends_array[] = $friend['user_id1'];
						$friends_array[] = $friend['user_id2'];
					}
					$friends_array = array_unique( $friends_array ); // friends and me
				}

				if ( ! empty( $friends_array ) ) {
					$exclude_args['meta_query'][] = array( // if not my friend
						'relation' => 'AND',
						array(
							'key'     => 'um_note_privacy',
							'value'   => 'friends',
							'compare' => '=',
						),
						array(
							'key'     => '_wall_id',
							'value'   => $friends_array,
							'compare' => 'NOT IN',
						)
					);
				} else {
					$exclude_args['meta_query'][] = array(
						'key'     => 'um_note_privacy',
						'value'   => 'friends',
						'compare' => '=',
					);
				}
			}

		} else {
			// user not logged in
			$options = array( 'only_me' );
			if ( defined( 'um_friends_version' ) ) {
				$options[] = 'friends';
			}

			$exclude_args = array(
				'fields'         => 'ids',
				'posts_per_page' => -1,
				'post_type'      => 'um_activity',
				'post_status'    => 'publish',
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'     => 'um_note_privacy',
						'value'   => apply_filters( 'um_user_notes_exclude_activity', $options, null ),
						'compare' => 'IN',
					),
				),
			);
		}

		$exclude_posts = get_posts( $exclude_args );
		if ( $exclude_posts ) {
			$not_in = array();
			if ( ! empty( $args['post__not_in'] ) ) {
				$not_in = $args['post__not_in'];
			}
			$args['post__not_in'] = array_merge( $not_in, $exclude_posts );
		}

		return $args;
	}


	/**
	 * Activity options
	 *
	 * @param array $actions
	 *
	 * @return array
	 */
	function add_activity_global_actions( $actions ) {
		$actions['new-note'] = __( 'New note', 'um-user-notes' );
		return $actions;
	}


	/**
	 * New tag for activity
	 *
	 * @param $search
	 *
	 * @return array
	 */
	function um_activity_search_tpl( $search ) {
		$search[] = "{related_id}";

		return $search;
	}


	/**
	 * New tag replace for activity
	 *
	 * @param $replace
	 * @param $array
	 *
	 * @return array
	 */
	function um_activity_replace_tpl( $replace, $array ) {
		$replace[] = isset( $array['related_id'] ) ? $array['related_id'] : '';

		return $replace;
	}

}