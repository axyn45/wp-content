<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>


<div class="um-clear"><br /></div>

<p class="um_notes_load_more_holder">
	<a href="javascript:void(0);" id="um-notes-load-more-btn" data-per_page="<?php UM()->Notes()->get_per_page(); ?>" data-page="1"
	   data-profile="<?php echo esc_attr( um_profile_id() ); ?>"
	   data-nonce="<?php echo wp_create_nonce( 'um_user_notes_load_more' ); ?>">
		<?php UM()->Notes()->load_more_text(); ?>
	</a>
</p>