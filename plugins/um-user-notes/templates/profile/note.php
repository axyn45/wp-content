<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="note-block <?php echo esc_attr( $float ); ?>">
	<div class="note-block-container">

		<?php $status = get_post_status( $id );

		if ( has_post_thumbnail( $id ) ) {
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'um_notes_thumbnail' ); ?>
			<div class="um-notes-note-image">
				<img src="<?php echo esc_url( $image[0] ); ?>" alt="<?php echo get_the_title($id); ?>"/>
			</div>
		<?php } ?>

		<div>

			<strong>
				<?php if ( $status == 'draft') { ?>
					<span style="color:#2e93fa;"><em><?php echo ucfirst( $status ); ?> - </em></span>
				<?php } ?>

				<?php echo get_the_title( $id ); ?>
			</strong>

			<br/>

			<small>
				<?php echo substr( strip_tags( get_the_content( $id ) ),0, UM()->Notes()->get_excerpt_length( true ) ); ?>..
			</small>
		</div>

		<a href="javascript:void(0);" class="um_note_read_more" data-id="<?php echo esc_attr( $id ); ?>">
			<?php UM()->Notes()->read_more_text(); ?>
		</a>

	</div>
</div>