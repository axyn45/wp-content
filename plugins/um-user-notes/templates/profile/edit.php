<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div>
	<form enctype="multipart/form-data" action="" method="post" id="um-user-notes-edit">

		<p style="position:relative;">

			<?php $thumbnail_id = '';
			$background = '';
			$btn_display = 'none';

			if ( $image ) {
				$background  = 'background-image:url(' . $image . ');';
				$thumbnail_id = $attachment_id;
				$btn_display = 'block';
			} ?>

			<button style="display:<?php echo esc_attr( $btn_display ); ?>;" data-id="<?php echo esc_attr( $thumbnail_id ); ?>"
					data-mode="edit" id="um_notes_clear_image" title="<?php esc_attr_e( 'Remove Photo', 'um-user-notes' ) ?>">
				&times;
			</button>

			<label class="um_notes_image_label" style="<?php echo esc_attr( $background ); ?>">
				<span data-add_photo="<?php esc_attr_e( 'Add Photo', 'um-user-notes' ) ?>"
				      data-edit_photo="<?php esc_attr_e( 'Edit Photo', 'um-user-notes' ) ?>">
					<i class="um-faicon-image"></i> <span class="um_notes_image_label_text"><?php echo $image ? __( 'Edit Photo', 'um-user-notes' ) : __( 'Add Photo', 'um-user-notes' ) ?></span>
				</span>
				<input id="um_notes_image_control" type="file" name="note_image" />
				<div class="um-clear"></div>
			</label>

		</p>

		<p>
			<input type="text" name="note_title" id="note_title" placeholder="Title" value="<?php echo esc_attr( $title ); ?>"/>

			<span class="um_notes_author_date">
				<?php echo get_avatar( um_user( 'ID' ), 15 ) ?>
				<!--<img class="user_avatar" src="<?php /*echo um_get_avatar_uri( um_profile('profile_photo'), 15 ); */?>"/>--><a class="user_profile_link" href="<?php echo esc_url( um_user_profile_url() ); ?>">
					<?php echo esc_html( um_user('display_name') ); ?></a>
				&bull;
				<?php echo date('l, F j, Y'); ?>
			</span>
		</p>

		<div>
			<?php wp_editor( esc_textarea( $content ), 'note_content_edit', array('textarea_name' => 'note_content', 'tinymce' => 0, 'media_buttons' => false) ); ?>
		</div>

		<input type="hidden" name="user_id" value="<?php echo get_current_user_id(); ?>" />
		<input type="hidden" name="thumbnail_id" value="<?php echo esc_attr( $thumbnail_id ); ?>" />
		<input type="hidden" name="post_id" value="<?php echo esc_attr( $id ); ?>" />
		<input type="hidden" name="action" value="um_notes_update" />
		<?php wp_nonce_field('um_user_notes_update_note'); ?>

		<div class="form-response"></div>
		<br />
		<p class="text-right">
			<select class="um-form-field um-select2" name="note_status" style="min-width:150px;height:36px;">
				<option value="publish" <?php selected( $status, 'publish' ) ?>><?php _e( 'Publish', 'um-user-notes' ); ?></option>
				<option value="draft" <?php selected( $status, 'draft' ) ?>><?php _e( 'Draft', 'um-user-notes' ); ?></option>
			</select>

			<?php $privacy_options = apply_filters( 'um_user_notes_privacy_options_dropdown', [
				'only_me'   => __( 'Only me', 'um-user-notes' ),
				'everyone'  => __( 'Everyone', 'um-user-notes' ),
			] ); ?>

			<select class="um-form-field um-select2" name="note_privacy" style="min-width:150px;height:36px;">
				<?php foreach ( $privacy_options as $key => $label ) { ?>
					<option value="<?php echo esc_attr( $key ) ?>" <?php selected( $privacy, $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php } ?>
			</select>

			<button type="submit" class="um-modal-btn publish" id="um_notes_update_btn">
				<?php _e( 'Update', 'um-user-notes' ); ?>
			</button>
			<button type="button" class="um-modal-btn alt" data-id="<?php echo esc_attr( $id ); ?>"
					id="um_notes_back_btn" data-nonce="<?php echo wp_create_nonce('um_user_notes_back'); ?>">
				<?php _e( 'Cancel', 'um-user-notes' ); ?>
			</button>
		</p>

	</form>

</div>