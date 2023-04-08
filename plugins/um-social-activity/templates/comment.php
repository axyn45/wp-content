<?php
/**
 * Displays a comment in the activity wall.
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-social-activity/comment.php
 *
 * @see     https://docs.ultimatemember.com/article/1516-templates-map
 * @package um_ext\um_social_activity\templates
 * @version 2.2.8
 *
 * @var array  $comments
 * @var int    $post_id
 * @var string $post_link
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

foreach ( $comments as $wall_comment ) {
	um_fetch_user( $wall_comment->user_id );

	$avatar      = get_avatar( um_user( 'ID' ), 80 );
	$likes       = get_comment_meta( $wall_comment->comment_ID, '_likes', true );
	$user_hidden = UM()->Activity_API()->api()->user_hidden_comment( $wall_comment->comment_ID );

	$wall_post   = get_post( $wall_comment->comment_post_ID );
	$can_edit    = UM()->Activity_API()->api()->can_edit_comment( $wall_comment->comment_ID, get_current_user_id() );
	$can_remove  = $can_edit || get_current_user_id() === (int) $wall_post->post_author;
	$can_comment = UM()->Activity_API()->api()->can_comment();

	$um_activity_comment_text = str_replace( "\'", "'", UM()->Activity_API()->api()->commentcontent( $wall_comment->comment_content ) );
	?>

	<div class="um-activity-commentwrap" data-comment_id="<?php echo absint( $wall_comment->comment_ID ); ?>">

		<div class="um-activity-commentl" id="commentid-<?php echo absint( $wall_comment->comment_ID ); ?>">

			<div class="um-activity-comment-avatar hidden-<?php echo esc_attr( $user_hidden ); ?>">
				<a href="<?php echo esc_url( um_user_profile_url() ); ?>"><?php echo wp_kses_post( $avatar ); ?></a>
			</div>

			<div class="um-activity-comment-hidden hidden-<?php echo esc_attr( $user_hidden ); ?>">
				<?php esc_html_e( 'Comment hidden.', 'um-activity' ); ?>
				<a href="javascript:void(0);" class="um-link">
					<?php esc_html_e( 'Show this comment', 'um-activity' ); ?>
				</a>
			</div>

			<div class="um-activity-comment-info hidden-<?php echo esc_attr( $user_hidden ); ?>">

				<div class="um-activity-comment-data">
					<span class="um-activity-comment-author-link">
						<a href="<?php echo esc_url( um_user_profile_url() ); ?>" class="um-link">
							<?php echo esc_html( um_user( 'display_name' ) ); ?>
						</a>
					</span>
					<span class="um-activity-comment-text">
						<?php echo wp_kses_post( $um_activity_comment_text ); ?>
					</span>
					<textarea id="um-activity-reply-<?php echo absint( $wall_comment->comment_ID ); ?>" class="original-content" style="display:none!important"><?php echo esc_textarea( $wall_comment->comment_content ); ?></textarea>
				</div>

				<div class="um-activity-comment-meta">
					<?php
					if ( is_user_logged_in() ) {
						if ( UM()->Activity_API()->api()->user_liked_comment( $wall_comment->comment_ID ) ) {
							?>
							<span>
								<a href="javascript:void(0);" class="um-link um-activity-comment-like active" data-like_text="<?php esc_attr_e( 'Like', 'um-activity' ); ?>" data-unlike_text="<?php esc_attr_e( 'Unlike', 'um-activity' ); ?>">
									<?php esc_html_e( 'Unlike', 'um-activity' ); ?>
								</a>
							</span>
						<?php } else { ?>
							<span>
								<a href="javascript:void(0);" class="um-link um-activity-comment-like" data-like_text="<?php esc_attr_e( 'Like', 'um-activity' ); ?>" data-unlike_text="<?php esc_attr_e( 'Unlike', 'um-activity' ); ?>">
									<?php esc_html_e( 'Like', 'um-activity' ); ?>
								</a>
							</span>
						<?php } ?>

						<span class="um-activity-comment-likes count-<?php echo absint( $likes ); ?>">
							<a href="javascript:void(0);"><i class="um-faicon-thumbs-up"></i>
								<ins class="um-activity-ajaxdata-commentlikes"><?php echo absint( $likes ); ?></ins>
							</a>
						</span>

						<?php if ( $can_comment ) { ?>
							<span>
								<a href="javascript:void(0);" class="um-link um-activity-comment-reply" data-commentid="<?php echo absint( $wall_comment->comment_ID ); ?>">
									<?php esc_html_e( 'Reply', 'um-activity' ); ?>
								</a>
							</span>
						<?php } ?>

						<?php if ( ! $user_hidden ) { ?>
							<span>
								<a href="javascript:void(0);" class="um-link um-activity-comment-hide um-tip-s" title="<?php esc_attr_e( 'Hide this comment', 'um-activity' ); ?>">
									<?php esc_html_e( 'Hide', 'um-activity' ); ?>
								</a>
							</span>
							<?php
						}
					}
					?>

					<span>
						<a href="<?php echo esc_url( UM()->Activity_API()->api()->get_comment_link( $post_link, $wall_comment->comment_ID ) ); ?>" class="um-activity-comment-permalink">
							<?php echo esc_html( UM()->Activity_API()->api()->get_comment_time( $wall_comment->comment_date ) ); ?>
						</a>
					</span>

					<?php if ( $can_edit || $can_remove ) { ?>
						<span class="um-activity-editc">
							<a href="javascript:void(0);" title="<?php esc_attr_e( 'Modify this comment', 'um-activity' ); ?>"><i class="um-icon-edit"></i></a>
							<span class="um-activity-editc-d">
								<?php if ( $can_edit ) { ?>
								<a href="javascript:void(0);" class="edit" data-commentid="<?php echo absint( $wall_comment->comment_ID ); ?>">
									<?php esc_html_e( 'Edit', 'um-activity' ); ?>
								</a>
								<?php } ?>
								<?php if ( $can_remove ) { ?>
								<a href="javascript:void(0);" class="delete" data-msg="<?php esc_attr_e( 'Are you sure you want to delete this comment?', 'um-activity' ); ?>">
									<?php esc_html_e( 'Delete', 'um-activity' ); ?>
								</a>
								<?php } ?>
							</span>
						</span>
					<?php } ?>

				</div>

			</div>

		</div>

		<?php
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$comm_num = ! empty( $_GET['wall_comment_id'] ) ? 10000 : UM()->options()->get( 'activity_init_comments_count' );

		$child = get_comments(
			array(
				'post_id' => $post_id,
				'parent'  => $wall_comment->comment_ID,
				'number'  => $comm_num,
				'offset'  => 0,
				'order'   => UM()->options()->get( 'activity_order_comment' ),
			)
		);

		$child_all = UM()->Activity_API()->api()->get_replies_number( $post_id, $wall_comment->comment_ID );
		?>

		<div class="um-activity-comment-child">

			<?php
			foreach ( $child as $commentc ) {
				um_fetch_user( $commentc->user_id );

				$t_args = array(
					'commentc'  => $commentc,
					'post_id'   => $post_id,
					'post_link' => UM()->Activity_API()->api()->get_permalink( $post_id ),
				);

				UM()->Activity_API()->shortcode()->args = $t_args;
				UM()->get_template( 'comment-reply.php', um_activity_plugin, $t_args, true );
			}

			// Do we have more comments.
			if ( $child_all > count( $child ) ) {
				$calc = $child_all - count( $child );
				if ( $calc > 1 ) {
					// translators: %s - a number of replies to load.
					$text = sprintf( __( 'load <span class="um-activity-more-count">%s</span> more replies', 'um-activity' ), $calc );
				} elseif ( 1 === $calc ) {
					// translators: %s - a one reply to load.
					$text = sprintf( __( 'load <span class="um-activity-more-count">%s</span> more reply', 'um-activity' ), $calc );
				}
				echo '<a href="javascript:void(0);" class="um-activity-ccommentload" data-load_replies="' . esc_attr__( 'load more replies', 'um-activity' ) . '" data-load_comments="' . esc_attr__( 'load more comments', 'um-activity' ) . '" data-loaded="' . absint( count( $child ) ) . '"><i class="um-icon-forward"></i><span>' . wp_kses_post( $text ) . '</span></a>';
				echo '<div class="um-activity-ccommentload-spin"></div>';
			}
			?>

		</div>
	</div>
	<?php
}

// reset um user.
um_reset_user();
