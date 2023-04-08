<?php
/**
 * Displays people who like this post in the activity wall.
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-social-activity/likes.php
 *
 * @see     https://docs.ultimatemember.com/article/1516-templates-map
 * @package um_ext\um_social_activity\templates
 * @version 2.2.8
 *
 * @var array $users
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="um-activity-modal-head um-popup-header">
	<?php esc_html_e( 'People who like this', 'um-activity' ); ?>
	<a href="javascript:void(0);" class="um-activity-modal-hide"><i class="um-icon-close"></i></a>
</div>

<div class="um-activity-modal-body um-popup-autogrow2" data-simplebar>

	<?php
	foreach ( $users as $user ) {
		um_fetch_user( $user );
		?>

		<div class="um-activity-modal-item">
			<div class="um-activity-modal-user">
				<div class="um-activity-modal-pic"><a href="<?php echo esc_url( um_user_profile_url() ); ?>"><?php echo get_avatar( $user, 80 ); ?></a></div>
				<div class="um-activity-modal-info">
					<div class="um-activity-modal-name"><a href="<?php echo esc_url( um_user_profile_url() ); ?>"><?php echo esc_html( um_user( 'display_name' ) ); ?></a></div>
					<?php do_action( 'um_activity_likes_below_name', $item_id ); ?>
				</div>
			</div>
			<div class="um-activity-modal-hook">
				<?php do_action( 'um_activity_likes_beside_name', $item_id ); ?>
			</div><div class="um-clear"></div>
		</div>

		<?php
	}
	um_reset_user();
	?>

</div>

<div class="um-popup-footer" style="height:30px">

</div>
