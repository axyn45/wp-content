<?php 
/**
 * Template for the modal 
 *
 * Used:  Profile page
 * Call:  UM()->Notes()->profile()->add_modal();
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-notes/profile/modal.php
 *
 * @see      https://docs.ultimatemember.com/article/1516-templates-map
 * @package  um_ext\um_user_notes\templates
 * @version  1.0.5
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$url = is_user_logged_in() ? um_user_profile_url( um_profile_id() ).'?profiletab=notes' : '';
?>

<div class="um-notes-modal">
	<a href="javascript:void(0);" data-close="<?php echo esc_url( $url ); ?>" id="um_notes_modal_close">&times; <?php _e( 'Close', 'um-user-notes' ) ?></a>
	<div class="um_notes_modal_content">
		<h1 class="um_notes_modal_default">
			<i class="um-user-notes-ajax-loading"></i>
		</h1>
	</div>
</div>