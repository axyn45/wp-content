<?php
/**
 * Displays the activity wall.
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-social-activity/user-wall.php
 *
 * @see     https://docs.ultimatemember.com/article/1516-templates-map
 * @package um_ext\um_social_activity\templates
 * @version 2.2.8
 *
 * @var string $hashtag
 * @var int    $offset
 * @var bool   $user_wall
 * @var int    $user_id
 * @var int    $wall_post
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = array(
	'post_type'   => 'um_activity',
	'post_status' => array( 'publish' ),
);

if ( isset( $wall_post ) && $wall_post > 0 ) {

	$args['post__in'] = array( $wall_post );

	$followed_ids = UM()->Activity_API()->api()->followed_ids();
	if ( $followed_ids ) {
		$args['meta_query'][] = array(
			'key'     => '_user_id',
			'value'   => $followed_ids,
			'compare' => 'IN',
		);
	}

	$friends_ids = UM()->Activity_API()->api()->friends_ids();
	if ( $friends_ids ) {
		$args['meta_query'][] = array(
			'key'     => '_user_id',
			'value'   => $friends_ids,
			'compare' => 'IN',
		);
	}

	$args['posts_per_page'] = 1;

} else {

	// set offset when pagination.
	// phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
	$args['posts_per_page'] = UM()->Activity_API()->api()->get_posts_per_page();
	if ( isset( $offset ) ) {
		$args['offset'] = $offset;
	}

	// If $user_wall == 0 - Loads Global Site Activity.
	// If $user_wall == 1 - Loads User Wall and $user_id.
	if ( ! empty( $user_id ) ) {
		if ( ! empty( $user_wall ) ) {
			$args['meta_query'][] = array(
				'relation' => 'OR',
				array(
					'key'     => '_wall_id',
					'value'   => $user_id,
					'compare' => '=',
				),
				array(
					'key'     => '_user_id',
					'value'   => $user_id,
					'compare' => '=',
				),
			);
		} else {
			$followed_ids = UM()->Activity_API()->api()->followed_ids();
			if ( $followed_ids ) {
				$args['meta_query'][] = array(
					'key'     => '_user_id',
					'value'   => $followed_ids,
					'compare' => 'IN',
				);
			}

			$friends_ids = UM()->Activity_API()->api()->friends_ids();
			if ( $friends_ids ) {
				$args['meta_query'][] = array(
					'key'     => '_user_id',
					'value'   => $friends_ids,
					'compare' => 'IN',
				);
			}
		}
	}

	if ( ! empty( $hashtag ) ) {
		$hashtag = str_replace( '#', '', $hashtag );
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'um_hashtag',
				'field'    => 'term_id',
				'terms'    => array( $hashtag ),
			),
		);
	}
}
// Get posts.
$args = apply_filters( 'um_activity_wall_args', $args );

$wall_posts    = array();
$wall_per_page = $args['posts_per_page'];

$wallposts = new WP_Query( $args );

wp_reset_postdata();

if ( 0 === $wallposts->found_posts ) {
	return;
}
if ( $wall_per_page > count( $wallposts->posts ) ) {
	$wall_per_page = count( $wallposts->posts );
}

while ( ! empty( $wallposts->posts ) ) {
	if ( count( $wall_posts ) >= $wall_per_page ) {
		break;
	}
	foreach ( $wallposts->posts as $wall_post ) {
		$author_id = UM()->Activity_API()->api()->get_author( $wall_post->ID );
		$can_view  = UM()->Activity_API()->api()->can_view_wall( $author_id );

		// exclude private walls.
		if ( true !== $can_view ) {
			continue;
		}

		$args['posts_per_page']--;
		$wall_posts[] = $wall_post;
	}

	if ( isset( $args['offset'] ) ) {
		$args['offset'] += count( $wallposts->posts );
	} else {
		$args['offset'] = count( $wallposts->posts );
	}

	$wallposts = new WP_Query( $args );

	wp_reset_postdata();
}

// allow onclick attribute.
if ( method_exists( 'UM_Functions', 'get_allowed_html' ) ) {
	$allowed_html = UM()->get_allowed_html( 'templates' );
} else {
	$allowed_html = wp_kses_allowed_html( 'post' );
}
if ( empty( $allowed_html['iframe'] ) ) {
	$allowed_html['iframe'] = array(
		'allow'          => true,
		'frameborder'    => true,
		'loading'        => true,
		'name'           => true,
		'referrerpolicy' => true,
		'sandbox'        => true,
		'src'            => true,
		'srcdoc'         => true,
		'title'          => true,
	);
}
$allowed_html['strong']['onclick'] = true;

foreach ( $wall_posts as $wall_post ) {
	$author_id = UM()->Activity_API()->api()->get_author( $wall_post->ID );
	$wall_id   = UM()->Activity_API()->api()->get_wall( $wall_post->ID );
	$post_link = UM()->Activity_API()->api()->get_permalink( $wall_post->ID );

	um_fetch_user( $author_id );
	?>

	<div class="um-activity-widget" id="postid-<?php echo esc_attr( $wall_post->ID ); ?>">

		<div class="um-activity-head">

			<div class="um-activity-left um-activity-author">
				<div class="um-activity-ava">
					<a href="<?php echo esc_url( um_user_profile_url( $author_id ) ); ?>">
						<?php echo get_avatar( $author_id, 80 ); ?>
					</a>
				</div>
				<div class="um-activity-author-meta">
					<div class="um-activity-author-url">
						<a href="<?php echo esc_url( um_user_profile_url() ); ?>" class="um-link">
							<?php echo esc_html( um_user( 'display_name' ) ); ?>
						</a>
						<?php
						if ( $wall_id && $wall_id !== $author_id ) {
							um_fetch_user( $wall_id );
							?>
							<i class="um-icon-forward"></i>
							<a href="<?php echo esc_url( um_user_profile_url() ); ?>" class="um-link">
								<?php echo esc_html( um_user( 'display_name' ) ); ?>
							</a>
						<?php } ?>
					</div>
					<span class="um-activity-metadata">
						<a href="<?php echo esc_url( $post_link ); ?>">
							<?php echo wp_kses( UM()->Activity_API()->api()->get_post_time( $wall_post->ID ), $allowed_html ); ?>
						</a>
					</span>
				</div>
			</div>

			<div class="um-activity-right">
				<?php if ( is_user_logged_in() ) { ?>

					<a href="javasscript:void(0);" class="um-activity-ticon um-activity-start-dialog" data-role="um-activity-tool-dialog">
						<i class="um-faicon-chevron-down"></i>
					</a>

					<div class="um-activity-dialog um-activity-tool-dialog">

						<?php if ( ( current_user_can( 'edit_users' ) || get_current_user_id() === (int) $author_id ) && 'status' === UM()->Activity_API()->api()->get_action_type( $wall_post->ID ) ) { ?>
							<a href="javascript:void(0);" class="um-activity-manage">
								<?php esc_html_e( 'Edit', 'um-activity' ); ?>
							</a>
						<?php } ?>

						<?php if ( current_user_can( 'edit_users' ) || get_current_user_id() === (int) $author_id ) { ?>
							<a href="javascript:void(0);" class="um-activity-trash"
									data-msg="<?php esc_attr_e( 'Are you sure you want to delete this post?', 'um-activity' ); ?>">
								<?php esc_html_e( 'Delete', 'um-activity' ); ?>
							</a>
						<?php } ?>

						<?php if ( get_current_user_id() !== $author_id ) { ?>
							<span class="sep"></span>
							<a href="javascript:void(0);" class="um-activity-report <?php echo UM()->Activity_API()->api()->reported( $wall_post->ID ) ? 'flagged' : ''; ?>"
									data-report="<?php esc_attr_e( 'Report', 'um-activity' ); ?>"
									data-cancel_report="<?php esc_attr_e( 'Cancel report', 'um-activity' ); ?>">
								<?php echo UM()->Activity_API()->api()->reported( $wall_post->ID, get_current_user_id() ) ? esc_html__( 'Cancel report', 'um-activity' ) : esc_html__( 'Report', 'um-activity' ); ?>
							</a>
						<?php } ?>

					</div>

				<?php } ?>
			</div>

			<div class="um-clear"></div>
		</div>

		<?php
		$has_video      = UM()->Activity_API()->api()->get_video( $wall_post->ID );
		$has_text_video = get_post_meta( $wall_post->ID, '_video_url', true );
		$has_oembed     = get_post_meta( $wall_post->ID, '_oembed', true );
		?>

		<div class="um-activity-body">
			<div class="um-activity-bodyinner<?php echo ( $has_video || $has_text_video ) ? ' has-embeded-video' : ''; ?> <?php echo $has_oembed ? ' has-oembeded' : ''; ?>">
				<div class="um-activity-bodyinner-edit">
					<textarea style="display: none;"><?php echo esc_attr( get_post_meta( $wall_post->ID, '_original_content', true ) ); ?></textarea>

					<?php
					$photo_base = get_post_meta( $wall_post->ID, '_photo', true );
					$photo_url  = UM()->Activity_API()->api()->get_download_link( $wall_post->ID, $author_id );
					?>
					<input type="hidden" name="_photo" value="<?php echo esc_attr( $photo_base ); ?>" />
					<input type="hidden" name="_photo_url" value="<?php echo esc_attr( $photo_url ); ?>" />
				</div>

				<?php
				$um_activity_post = UM()->Activity_API()->api()->get_content( $wall_post->ID, $has_video );
				$um_shared_link   = get_post_meta( $wall_post->ID, '_shared_link', true );
				$content          = $um_activity_post . $um_shared_link;
				if ( $content ) {
					?>
					<div class="um-activity-bodyinner-txt"><?php echo wp_kses( $content, $allowed_html ); ?></div>
				<?php } ?>

				<div class="um-activity-bodyinner-photo">
					<?php echo wp_kses( UM()->Activity_API()->api()->get_photo( $wall_post->ID, '', $author_id ), $allowed_html ); ?>
				</div>

				<?php if ( empty( $um_shared_link ) ) { ?>
					<div class="um-activity-bodyinner-video">
						<?php echo wp_kses( $has_video, $allowed_html ); ?>
					</div>
				<?php } ?>
			</div>

			<?php
			$likes         = UM()->Activity_API()->api()->get_likes_number( $wall_post->ID );
			$wall_comments = UM()->Activity_API()->api()->get_comments_number( $wall_post->ID );

			if ( $likes > 0 || $wall_comments > 0 ) {
				?>
				<div class="um-activity-disp">
					<div class="um-activity-left">
						<div class="um-activity-disp-likes">
							<a href="javascript:void(0);" class="um-activity-show-likes um-link" data-post_id="<?php echo absint( $wall_post->ID ); ?>">
								<span class="um-activity-post-likes"><?php echo absint( $likes ); ?></span>
								<span class="um-activity-disp-span"><?php esc_html_e( 'likes', 'um-activity' ); ?></span>
							</a>
						</div>
						<div class="um-activity-disp-comments">
							<a href="javascript:void(0);" class="um-link">
								<span class="um-activity-post-comments"><?php echo absint( $wall_comments ); ?></span>
								<span class="um-activity-disp-span"><?php esc_html_e( 'comments', 'um-activity' ); ?></span>
							</a>
						</div>
					</div>
					<div class="um-activity-faces um-activity-right">
						<?php echo wp_kses( UM()->Activity_API()->api()->get_faces( $wall_post->ID ), $allowed_html ); ?>
					</div>
					<div class="um-clear"></div>
				</div>
				<div class="um-clear"></div>
			<?php } ?>

		</div>

		<div class="um-activity-foot status" id="wallcomments-<?php echo absint( $wall_post->ID ); ?>">
			<?php if ( is_user_logged_in() ) { ?>

				<div class="um-activity-left um-activity-actions">
					<?php if ( UM()->Activity_API()->api()->user_liked( $wall_post->ID ) ) { ?>
						<div class="um-activity-like active"
								data-like_text="<?php esc_attr_e( 'Like', 'um-activity' ); ?>"
								data-unlike_text="<?php esc_attr_e( 'Unlike', 'um-activity' ); ?>">
							<a href="javascript:void(0);">
								<i class="um-faicon-thumbs-up um-active-color"></i>
								<span class=""><?php esc_html_e( 'Unlike', 'um-activity' ); ?></span>
							</a>
						</div>
					<?php } else { ?>
						<div class="um-activity-like"
								data-like_text="<?php esc_attr_e( 'Like', 'um-activity' ); ?>"
								data-unlike_text="<?php esc_attr_e( 'Unlike', 'um-activity' ); ?>">
							<a href="javascript:void(0);">
								<i class="um-faicon-thumbs-up"></i>
								<span class=""><?php esc_html_e( 'Like', 'um-activity' ); ?></span>
							</a>
						</div>
					<?php } ?>

					<?php if ( UM()->Activity_API()->api()->can_comment() ) { ?>
						<div class="um-activity-comment">
							<a href="javascript:void(0);">
								<i class="um-faicon-comment"></i>
								<span class=""><?php esc_html_e( 'Comment', 'um-activity' ); ?></span>
							</a>
						</div>
					<?php } ?>
				</div>

			<?php } else { ?>
				<div class="um-activity-left um-activity-join">
					<?php echo wp_kses( UM()->Activity_API()->api()->login_to_interact( $wall_post->ID ), $allowed_html ); ?>
				</div>
			<?php } ?>

			<div class="um-clear"></div>
		</div>

		<?php
		$t_args = array(
			'post_id' => $wall_post->ID,
		);

		UM()->Activity_API()->shortcode()->args = $t_args;
		UM()->get_template( 'comments.php', um_activity_plugin, $t_args, true );
		?>

	</div>

	<?php
}
um_reset_user();

if ( isset( $args['posts_per_page'] ) && $args['posts_per_page'] >= $wallposts->found_posts ) {
	?>

	<div class="um-activity-end"></div>

	<?php
	if ( empty( $post_link ) && ob_get_level() ) {
		ob_clean();
	}
	return;
} elseif ( empty( $wall_post ) ) {
	?>
	<div class="um-activity-load" data-offset="<?php echo isset( $args['offset'] ) ? absint( $args['offset'] ) : absint( $wall_per_page ); ?>"></div>
	<?php
}
