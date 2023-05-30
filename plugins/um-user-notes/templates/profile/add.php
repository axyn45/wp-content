<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div>
	<form enctype="multipart/form-data" action="" method="post" id="um-user-notes-add">
 
		<p style="position:relative;">
			<button data-mode="add" id="um_notes_clear_image" title="<?php esc_attr_e( 'Remove Photo', 'um-user-notes' ) ?>">&times;</button>

			<label class="um_notes_image_label">
				<span data-add_photo="<?php esc_attr_e( 'Add Photo', 'um-user-notes' ) ?>"
				      data-edit_photo="<?php esc_attr_e( 'Edit Photo', 'um-user-notes' ) ?>">
					<i class="um-faicon-image"></i> <span class="um_notes_image_label_text"><?php _e( 'Add Photo', 'um-user-notes' ) ?></span>
				</span>
				<input id="um_notes_image_control" type="file" name="note_image" />
				<div class="um-clear"></div>
			</label>
		</p>

		<p>
			<input type="text" name="note_title" id="note_title" placeholder="<?php _e( 'Title', 'um-user-notes' ); ?>"/>

			<span class="um_notes_author_date">
				<a class="user_profile_link" href="<?php echo esc_url( um_user_profile_url() ); ?>">
					<?php echo get_avatar( um_user( 'ID' ), 15 ) ?>
					<?php echo esc_html( um_user('display_name') ); ?>
				</a>
				&bull;
				<?php echo date_i18n( get_option( 'date_format' ) ); ?>
			</span>
		</p>

		<div>
			<?php wp_editor( '', 'note_content', [ 'media_buttons' => false ] ); ?>
		</div>

		<input type="hidden" name="action" value="um_notes_add" />
		<input type="hidden" name="user_id" value="<?php echo get_current_user_id(); ?>"/>
		<div class="form-response"></div>
		<br />
		<p class="text-right">
			<select class="um-form-field um-select2" name="note_status" style="min-width:150px;height:36px;">
				<option value="publish"><?php _e( 'Publish', 'um-user-notes' ); ?></option>
				<option value="draft"><?php _e( 'Draft', 'um-user-notes' ); ?></option>
			</select>

			<?php $privacy_options = apply_filters( 'um_user_notes_privacy_options_dropdown', [
				'only_me'   => __( 'Only me', 'um-user-notes' ),
				'everyone'  => __( 'Everyone', 'um-user-notes' ),
			] ); ?>

			<select class="um-form-field um-select2" name="note_privacy" style="min-width:150px;height:36px;">
				<?php foreach ( $privacy_options as $key => $label ) { ?>
					<option value="<?php echo esc_attr( $key ) ?>"><?php echo esc_html( $label ); ?></option>
				<?php } ?>
			</select>

			<button type="button" class="um-modal-btn publish" id="um_notes_add_btn"><?php _e( 'Publish', 'um-user-notes' ); ?></button>
		</p>
		<?php wp_nonce_field( 'um_user_notes_add_note' ); ?>
	</form>
</div>