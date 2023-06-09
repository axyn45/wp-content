<?php
/**
 * Displays comments in the activity post.
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-social-activity/comments.php
 *
 * @see     https://docs.ultimatemember.com/article/1516-templates-map
 * @package um_ext\um_social_activity\templates
 * @version 2.2.8
 *
 * @var int $post_id
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $post_id ) ) {
	return;
}

$comments_all = UM()->Activity_API()->api()->get_comments_number( $post_id );
if ( empty( $comments_all ) && ! is_user_logged_in() ) {
	return;
}

/**
 * Hook: um_activity_before_post_comments.
 */
do_action( 'um_activity_before_post_comments', $post_id );
?>

<div class="um-activity-comments">

	<?php if ( $comments_all > 0 || UM()->Activity_API()->api()->can_comment() ) { ?>

		<div class="um-activity-comments-loop">
			<?php
			// Comments display.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$comm_num = ! empty( $_GET['wall_comment_id'] ) ? 10000 : UM()->options()->get( 'activity_init_comments_count' );

			$wall_comments = get_comments(
				array(
					'post_id' => $post_id,
					'parent'  => 0,
					'number'  => $comm_num,
					'offset'  => 0,
					'order'   => UM()->options()->get( 'activity_order_comment' ),
				)
			);

			$t_args = array(
				'comments'  => $wall_comments,
				'post_id'   => $post_id,
				'post_link' => UM()->Activity_API()->api()->get_permalink( $post_id ),
			);

			UM()->Activity_API()->shortcode()->args = $t_args;
			UM()->get_template( 'comment.php', um_activity_plugin, $t_args, true );

			// Do we have more comments.
			if ( $comments_all > count( $wall_comments ) ) {
				$calc = $comments_all - count( $wall_comments );
				if ( $calc > 1 ) {
					// translators: %s - a number of replies to load.
					$text = sprintf( __( 'load <span class="um-activity-more-count">%s</span> more comments', 'um-activity' ), $calc );
				} elseif ( 1 === $calc ) {
					// translators: %s - a one reply to load.
					$text = sprintf( __( 'load <span class="um-activity-more-count">%s</span> more comment', 'um-activity' ), $calc );
				}
				?>

				<a href="javascript:void(0);" class="um-activity-commentload"
						data-load_replies="<?php esc_attr_e( 'load more replies', 'um-activity' ); ?>"
						data-load_comments="<?php esc_attr_e( 'load more comments', 'um-activity' ); ?>"
						data-loaded="<?php echo absint( count( $wall_comments ) ); ?>">
					<i class="um-icon-forward"></i>
					<span><?php echo wp_kses_post( $text ); ?></span>
				</a>
				<div class="um-activity-commentload-spin"></div>

			<?php } ?>

		</div>

		<?php
	}

	if ( is_user_logged_in() && UM()->Activity_API()->api()->can_comment() ) {
		?>
		<!-- hidden comment area for clone -->
		<div class="um-activity-commentl um-activity-comment-area">
			<div class="um-activity-comment-avatar">
				<?php echo get_avatar( get_current_user_id(), 80 ); ?>
			</div>
			<div class="um-activity-comment-box">
				<textarea class="um-activity-comment-textarea"
						placeholder="<?php esc_attr_e( 'Write a comment...', 'um-activity' ); ?>"
						data-replytext="<?php esc_attr_e( 'Write a reply...', 'um-activity' ); ?>"
						data-reply_to="0" ></textarea>
			</div>
			<div class="um-activity-right">
				<a href="javascript:void(0);" class="um-button um-activity-comment-post um-disabled">
					<?php esc_html_e( 'Comment', 'um-activity' ); ?>
				</a>
			</div>
			<div class="um-clear"></div>
		</div>

	<?php } ?>

</div>

<?php

/**
 * Hook: um_activity_after_post_comments.
 */
do_action( 'um_activity_after_post_comments', $post_id );
