<?php if ( ! defined('ABSPATH') ) exit;

if ( $image ) { ?>
	<div class="note-image">
		<img src="<?php echo esc_attr( $image ); ?>" />
	</div>
<?php } ?>

<div style="padding:30px;">
	<h1><?php echo esc_html( $title ); ?></h1>

	<span class="um_notes_author_date">

		<a class="um_notes_author_profile_link" href="<?php echo esc_url( $profile_link ); ?>">
			<?php echo $avatar ?>
			<?php echo esc_html( $author_name ); ?>
		</a>
		&bull;
		<?php echo esc_html( $post_date ); ?>
	</span>

	<div>
		<?php
			$content = apply_filters( 'um_notes_oembed', $content );
			echo wpautop( $content );
		?>
	</div>
</div>

<?php if ( um_is_profile_owner() && UM()->Notes()->is_note_author( $id ) ) { ?>
	<p style="text-align:right;">
		<button class="um_notes_edit_note um-modal-btn" type="button"
				data-id="<?php echo esc_attr( $id ); ?>"
				data-nonce="<?php echo wp_create_nonce('um_user_notes_edit'); ?>">
			<?php _e( 'Edit', 'um-user-notes' ) ?>
		</button>

		<button class="um_notes_delete_note um-modal-btn alt" type="button"
				data-id="<?php echo esc_attr( $id ); ?>"
				data-nonce="<?php echo wp_create_nonce('um_user_notes_delete'); ?>">
			<?php _e( 'Delete', 'um-user-notes' ) ?>
		</button>
	</p>
<?php }