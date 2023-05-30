<?php if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'UM_Notes_Functions' ) ) {


	/**
	 * Class UM_Notes_Functions
	 */
	class UM_Notes_Functions {


		/**
		 * UM_Notes_Functions constructor.
		 */
		function __construct() {
		}


		/**
		 * @return array
		 */
		function get_allowed_filetypes() {
			$allowed = [
				'image/jpeg',
				'image/png',
				'image/jpg',
				'image/gif',
			];

			return $allowed;
		}


		/**
		 * Is note author
		 *
		 * @param bool $note_id
		 * @param bool $user_id
		 *
		 * @return bool
		 */
		function is_note_author( $note_id = false, $user_id = false ) {

			if ( ! is_user_logged_in() || ! $note_id ) {
				return false;
			}

			$note = get_post( $note_id );
			if ( empty( $note ) || is_wp_error( $note ) ) {
				return false;
			}

			if ( empty( $user_id ) ) {
				$user_id = get_current_user_id();
			}

			return ( $user_id == $note->post_author );
		}


		/**
		 * Get load more text from setting
		 */
		function load_more_text() {
			$text = UM()->options()->get( 'um_user_notes_load_more_text' );
			echo apply_filters( 'um_user_notes_load_more_text', $text );
		}


		/**
		 * Get read more text from setting
		 */
		function read_more_text() {
			$text = UM()->options()->get( 'um_user_notes_read_more_text' );
			echo apply_filters( 'um_user_notes_read_more_text', $text );
		}


		/**
		 * Get number of note to display on profile
		 *
		 * @param bool $return
		 *
		 * @return string
		 */
		function get_per_page( $return = false ) {
			$number = UM()->options()->get( 'um_user_notes_per_page' );

			$per_page = apply_filters( 'um_user_notes_per_page' , intval( $number ) );

			if ( $return ) {

				return $per_page;

			} else {

				echo $per_page;
				return '';
			}
		}


		/**
		 * Get excerpt length from setting
		 *
		 * @param bool $return
		 *
		 * @return string
		 */
		function get_excerpt_length( $return = false ) {

			$number = UM()->options()->get( 'um_user_notes_excerpt_length' );

			$count = apply_filters( 'um_user_notes_excerpt_length' , intval( $number ) );

			if ( $return ) {

				return $count;

			} else {

				echo $count;
				return '';
			}

		}


		/**
		 * Check if user can view note
		 *
		 * @param int|null $note_id
		 *
		 * @return bool
		 */
		function can_view( $note_id = null ) {
			if ( ! $note_id ) {
				return false;
			}

			$user_id = null;
			if ( is_user_logged_in() ) {
				$user_id = get_current_user_id();
			}

			if ( $user_id == um_profile_id() ){
				return true;
			}

			$privacy = get_post_meta( $note_id, '_privacy', true );
			$is_author = $this->is_note_author( $note_id, $user_id );

			if ( ! $is_author ) {
				if ( $privacy == 'only_me' ) {
					return false;
				} else {
					$status = get_post_status( $note_id );
					if ( $status == 'draft' ) {
						return false;
					}
				}
			}

			if ( $privacy == 'everyone' ) {
				return true;
			}

			$custom_privacy = apply_filters( 'um_user_notes_custom_privacy', true, $privacy, $user_id );
			return $custom_privacy;
		}
	}
}