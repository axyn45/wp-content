<?php
namespace um_ext\um_user_notes\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Ajax
 *
 * @package um_ext\um_user_notes\core
 */
class Ajax {


	/**
	 * Ajax constructor.
	 */
	function __construct() {
		// @Action : Add note
		add_action( 'wp_ajax_um_notes_add', [ $this, 'add' ] );
		
		// @Action : Update note
		add_action( 'wp_ajax_um_notes_update', [ $this, 'update' ] );
		
		// @Action : Delete note
		add_action( 'wp_ajax_um_notes_delete', [ $this, 'delete' ] );
		
		// @Action : Edit note
		add_action( 'wp_ajax_um_notes_edit', [ $this, 'edit' ] );
		
		// @Action : Add View
		add_action( 'wp_ajax_um_notes_view', [ $this, 'view' ] );
		add_action( 'wp_ajax_nopriv_um_notes_view', [ $this, 'view' ] );
		
		// @Action : Ajax load more
		add_action( 'wp_ajax_um_notes_load_more', [ $this, 'load' ] );
		add_action( 'wp_ajax_nopriv_um_notes_load_more', [ $this, 'load' ] );
	}


	/**
	 * @param $name
	 *
	 * @return bool
	 */
	private function check_filtetype( $name ) {
		$allowed = UM()->Notes()->get_allowed_filetypes();

		if ( isset( $_FILES[ $name ]['tmp_name'] ) && $_FILES[ $name ]['tmp_name'] != '' ) {
			if ( ! in_array( $_FILES[ $name ]['type'], $allowed ) ) {
				return false;
			}
		}

		return true;
	}


	/**
	 * Form action to update note
	 */
	function update() {
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'um_user_notes_update_note' ) ) {
			wp_send_json_error( __( 'Invalid request', 'um-user-notes' ) );
		}

		if ( ! um_is_profile_owner() ) {
			wp_send_json_error( __( 'Invalid user', 'um-user-notes' ) );
		}

		if ( ! isset( $_POST['note_title'] ) || sanitize_text_field( $_POST['note_title'] ) == '' ) {
			wp_send_json_error( __( 'Title is required', 'um-user-notes' ) );
		}
		
		if ( ! isset( $_POST['note_content'] ) || trim( $_POST['note_content'] ) == '' ) {
			wp_send_json_error( __( 'Content is required', 'um-user-notes' ) );
		}

		if ( ! isset( $_POST['note_privacy'] ) || trim( sanitize_key( $_POST['note_privacy'] ) ) == '0' ) {
			wp_send_json_error( __( 'Please select visibility option.', 'um-user-notes' ) );
		}

		$post_id = absint( $_POST['post_id'] );
		if ( ! UM()->Notes()->is_note_author( $post_id, get_current_user_id() ) ) {
			wp_send_json_error( __( 'You are not authorized to update this note.', 'um-user-notes' ) );
		}

		if ( ! $this->check_filtetype( 'note_image' ) ) {
			wp_send_json_error( sprintf( __( '%s files are not allowed', 'um-user-notes' ), $_FILES['note_image']['type'] ) );
		}

		$title = sanitize_text_field( $_POST['note_title'] );
		$content = $_POST['note_content'];
		$privacy = esc_attr( sanitize_key( $_POST['note_privacy'] ) );
		$status = sanitize_key( $_POST['note_status'] );

		$current_image = false;
		if ( has_post_thumbnail( $post_id ) ) {
			$current_image = get_post_thumbnail_id( $post_id );
		}

		$updated = wp_update_post( [
			'ID'            => $post_id,
			'post_content'  => $content,
			'post_title'    => $title,
			'post_status'   => $status,
			'filter'        => true,
		] );

		update_post_meta( $updated, '_privacy', $privacy );

		if ( sanitize_key( $_POST['thumbnail_id'] ) == '' && ( ! isset( $_FILES['note_image']['tmp_name'] ) || $_FILES['note_image']['tmp_name'] == '' ) ) {
			if ( $current_image ) {
				wp_delete_attachment( $current_image, true );
			}

			delete_post_meta( $updated,'_thumbnail_id' );
		}

		if ( isset( $_FILES['note_image']['tmp_name'] ) && $_FILES['note_image']['tmp_name'] != '' ) {
			require_once( ABSPATH . 'wp-admin/includes/image.php' );

			$uploadedfile = $_FILES['note_image'];
			$upload_overrides = [ 'test_form' => false ];

			$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
			if ( $movefile && ! isset( $movefile['error'] ) ) {

				$filename = $movefile['file'];
				$wp_upload_dir = wp_upload_dir();

				$attach_id = wp_insert_attachment( [
					'guid'              => $wp_upload_dir['url'] . '/' . basename( $filename ),
					'post_mime_type'    => $movefile['type'],
					'post_title'        => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
					'post_content'      => '',
					'post_parent'       => $updated,
					'post_author'       => get_current_user_id(),
					'post_status'       => 'inherit',
				], $filename );


				$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
				wp_update_attachment_metadata( $attach_id, $attach_data );
				
				update_post_meta( $updated,'_thumbnail_id', $attach_id );
				if ( $current_image ) {
					wp_delete_attachment( $current_image, true );
				}

			} else {

				wp_send_json_error( __( 'Problem uploading file.', 'um-user-notes' ) );

			}
		}

		if ( empty( $updated ) || is_wp_error( $updated ) ) {
			wp_send_json_error( __( 'Invalid request', 'um-user-notes' ) );
		}

		do_action( 'um_user_notes_after_note_updated', $updated );

		wp_send_json_success();
	}


	/**
	 * Form action to add note
	 */
	function add() {
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'um_user_notes_add_note' ) ) {
			wp_send_json_error( __( 'Invalid request', 'um-user-notes' ) );
		}

		if ( ! um_is_profile_owner() ) {
			wp_send_json_error( __( 'Invalid user', 'um-user-notes' ) );
		}

		if ( ! isset( $_POST['note_title'] ) || sanitize_text_field( $_POST['note_title'] ) == '' ) {
			wp_send_json_error( __( 'Title is required', 'um-user-notes' ) );
		}

		if( ! isset( $_POST['note_content'] ) || trim( $_POST['note_content'] ) == '' ) {
			wp_send_json_error( __( 'Content is required', 'um-user-notes' ) );
		}

		if ( ! isset( $_POST['note_privacy'] ) || sanitize_key( $_POST['note_privacy'] ) == '0' ) {
			wp_send_json_error( __( 'Please select visibility option.', 'um-user-notes' ) );
		}

		if ( ! $this->check_filtetype( 'note_image' ) ) {
			wp_send_json_error( sprintf( __( '%s files are not allowed', 'um-user-notes' ), $_FILES[ 'note_image' ]['type'] ) );
		}


		$title = sanitize_text_field( $_POST['note_title'] );
		$privacy = esc_attr( sanitize_key( $_POST['note_privacy'] ) );
		$content = wp_kses_post( $_POST['note_content'] );
		$status = sanitize_key( $_POST['note_status'] );
		$author = absint( $_POST['user_id'] );


		$added = wp_insert_post( [
			'post_author'   => $author,
			'post_content'  => $content,
			'post_title'    => $title,
			'post_status'   => $status,
			'post_type'     => 'um_notes',
			'filter'        => true,
		] );

		update_post_meta( $added, '_privacy', $privacy );

		if ( isset( $_FILES['note_image']['tmp_name'] ) && $_FILES['note_image']['tmp_name'] != '' ) {
			require_once( ABSPATH . 'wp-admin/includes/image.php' );

			$uploadedfile = $_FILES['note_image'];
			$upload_overrides = [ 'test_form' => false ];

			$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
			if ( $movefile && ! isset( $movefile['error'] ) ) {

				$filename = $movefile['file'];
				$wp_upload_dir = wp_upload_dir();

				$attach_id = wp_insert_attachment( [
					'guid'              => $wp_upload_dir['url'] . '/' . basename( $filename ),
					'post_mime_type'    => $movefile['type'],
					'post_title'        => preg_replace('/\.[^.]+$/', '', basename($filename)),
					'post_content'      => '',
					'post_parent'       => $added,
					'post_author'       => $author,
					'post_status'       => 'inherit',
				], $filename );

				$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
				wp_update_attachment_metadata( $attach_id, $attach_data );

				update_post_meta( $added , '_thumbnail_id', $attach_id );

			} else {

				wp_send_json_error( __( 'Problem uploading file.', 'um-user-notes' ) );

			}
		}

		if ( empty( $added ) || is_wp_error( $added ) ) {
			wp_send_json_error( __( 'Invalid request', 'um-user-notes' ) );
		}

		do_action( 'um_user_notes_after_note_created', $added );

		ob_start(); ?>

		<i class="um-faicon-check"></i>
		<a href="javascript:void(0);" class="um_note_read_more" data-id="<?php echo esc_attr( $added ) ?>">
			<?php UM()->Notes()->read_more_text() ?>
		</a>

		<?php $display_msg = ob_get_clean();

		wp_send_json_success( [ 'note_id' => $added, 'display' => $display_msg ] );
	}


	/**
	 * Form action to delete note
	 */
	function delete() {
		if ( ! wp_verify_nonce( $_POST['_nonce'], 'um_user_notes_delete' ) ) {
			wp_send_json_error( __( 'Invalid request', 'um-user-notes' ) );
		}

		if ( ! um_is_profile_owner() ) {
			wp_send_json_error( __( 'Invalid user', 'um-user-notes' ) );
		}

		$post_id = absint( $_POST['post_id'] );
		if ( ! UM()->Notes()->is_note_author( $post_id, get_current_user_id() ) ) {
			wp_send_json_error( __( 'You are not authorized to delete this note.', 'um-user-notes' ) );
		}

		if ( has_post_thumbnail( $post_id ) ) {
			$attachment_id = get_post_thumbnail_id( $post_id );
			wp_delete_attachment( $attachment_id, true );
		}

		do_action( 'um_user_notes_before_note_deleted', $post_id );

		if ( wp_delete_post( $post_id,true ) ) {
			wp_send_json_success();
		}

		wp_send_json_error( __( 'Invalid request', 'um-user-notes' ) );
	}


	/**
	 * Display note details on modal
	 */
	function view() {
		$post_id = absint( $_REQUEST['note'] );

		if ( ! UM()->Notes()->can_view( $post_id ) ) {
			wp_send_json_error( __( 'You are not authorized to view this note.', 'um-user-notes' ) );
		}

		$note = get_post( $post_id );

		um_fetch_user( $note->post_author );

		$img = false;
		if ( has_post_thumbnail( $post_id ) ) {
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'full' );
			$img = $image[0];
		}

		ob_start();

		UM()->get_template( 'profile/view.php', um_user_notes_plugin, [
			'id'            => $post_id,
			'title'         => esc_attr( $note->post_title ),
			'content'       => wp_kses_post( $note->post_content ),
			'image'         => $img,
			'avatar'        => get_avatar( um_user( 'ID' ), 15 ),
			'profile_link'  => um_user_profile_url(),
			'author_name'   => um_user( 'display_name' ),
			'post_date'     => get_the_date('', $post_id ),
		], true );

		$content = ob_get_clean();

		wp_send_json_success( $content );
	}


	/**
	 * Display note edit form on modal
	 * Receives post request
	 * Returns String (html view)
	 */
	function edit() {
		if ( ! wp_verify_nonce( $_POST['_nonce'], 'um_user_notes_edit' ) ) {
			wp_send_json_error( __( 'Invalid request', 'um-user-notes' ) );
		}

		if ( ! um_is_profile_owner() ) {
			wp_send_json_error( __( 'Invalid user', 'um-user-notes' ) );
		}

		$post_id = absint( $_POST['post_id'] );
		if ( ! UM()->Notes()->is_note_author( $post_id, get_current_user_id() ) ) {
			wp_send_json_error( __( 'You are not authorized to edit this note.', 'um-user-notes' ) );
		}

		$note = get_post( $post_id );
		if ( $note->post_author != get_current_user_id() ) {
			_e( 'You are not authorized to edit this note.', 'um-user-notes' );
			die;
		}

		$title = $note->post_title;
		$status = $note->post_status;
		$content = $note->post_content;
		$privacy = get_post_meta( $post_id , '_privacy' , true );

		$img = false;
		$attachment_id = false;
		if ( has_post_thumbnail( $post_id ) ) {
			$attachment_id = get_post_thumbnail_id( $post_id );
			$image = wp_get_attachment_image_src( $attachment_id , 'full' );
			$img = $image[0];
		}

		ob_start();

		UM()->get_template( 'profile/edit.php', um_user_notes_plugin, [
			'title'         => $title,
			'content'       => $content,
			'status'        => $status,
			'image'         => $img,
			'attachment_id' => $attachment_id,
			'id'            => $post_id,
			'privacy'       => $privacy,
		], true );

		$content = ob_get_clean();

		wp_send_json_success( $content );
	}


	/**
	 * Display more notes on profile
	 * Receives post request
	 * Returns String (html view)
	 */
	function load() {
		if ( ! wp_verify_nonce( $_POST['_nonce'], 'um_user_notes_load_more' ) ) {
			wp_send_json_error( __( 'Invalid request', 'um-user-notes' ) );
		}

		$html = 'empty';
		$show_loadmore = false;

		$per_page = absint( $_POST['per_page'] );
		$offset = intval( $_POST['offset'] );
		$profile = absint( $_POST['profile'] );

		$args = [
			'post_type'         => 'um_notes',
			'author__in'        => $profile,
			'posts_per_page'    => $per_page + 1,
			'offset'            => $offset,
		];
		$args = apply_filters( 'um_notes_query_args', $args, $profile );

		$remaining_notes = new \WP_Query( $args );
		$remaining_count = $remaining_notes->post_count;

		$args = [
			'post_type'         => 'um_notes',
			'author__in'        => $profile,
			'posts_per_page'    => $per_page,
			'offset'            => $offset,
		];
		$args = apply_filters( 'um_notes_query_args', $args, $profile );

		$latest_notes = new \WP_Query( $args );

		if ( $latest_notes->have_posts() ) {
			$i = 1;

			ob_start();

			while ( $latest_notes->have_posts() ) {
				$latest_notes->the_post();
				$float = ( $i % 2 == 0 ) ? 'right' : 'left';

				UM()->get_template( 'profile/note.php', um_user_notes_plugin, [
					'float' => $float,
					'id'    => get_the_id(),
				], true );

				if ( $i % 2 == 0 ) {
					echo '<div class="um-clear"></div>';
				}

				if ( $remaining_count > $per_page ) {
					$show_loadmore = true;
				}

				$i ++;
			}

			$html = ob_get_clean();
		}

		wp_reset_postdata();

		wp_send_json_success( [ 'html' => $html, 'loadmore' => $show_loadmore ] );
	}
}