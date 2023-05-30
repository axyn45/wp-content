<?php
/**
 * Template for the single bookmark
 *
 * Used:   Profile page > Bookmarks tab > folder
 * Parent: profile/bookmarks.php
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-bookmarks/profile/single-folder/bookmark-item.php
 *
 * @see      https://docs.ultimatemember.com/article/1516-templates-map
 * @package  um_ext\um_user_bookmarks\templates
 * @version  2.0.7
 *
 * @var  string $excerpt
 * @var  bool   $has_image
 * @var  string $has_image_class
 * @var  int    $id
 * @var  array  $image
 * @var  string $image_url
 * @var  string $post_link
 * @var  string $post_title
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="um-user-bookmarked-item <?php echo esc_attr( $has_image_class ); ?>">
	<div class="um-user-bookmarkss-list" href="<?php echo esc_url( $post_link ); ?>">
		<?php if ( $has_image ) { ?>
			<a href="<?php echo esc_url( $post_link ); ?>" target="_blank">
				<img class="um-user-bookmarked-post-image" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $post_title ); ?>" />
			</a>
		<?php } ?>

		<div class="um-user-bookmarks-post-content">
			<h3>
				<a href="<?php echo esc_url( $post_link ); ?>" target="_blank">
					<?php echo esc_html( $post_title ); ?>
				</a>
			</h3>

			<?php if ( ! empty( $excerpt ) && ! UM()->options()->get( 'um_user_bookmarks_page_builder' ) ) { ?>
				<p style="margin-bottom:0;"><?php echo strip_shortcodes( $excerpt ); ?>...</p>
			<?php } ?>

			<?php
			if ( is_user_logged_in() && $user_id == get_current_user_id() && $id ) { ?>
				<a href="javascript:void(0);" data-nonce="<?php echo wp_create_nonce( 'um_user_bookmarks_remove_' . $id ); ?>" data-remove_element="true" class="um-user-bookmarks-profile-remove-link" data-id="<?php echo esc_attr( $id ); ?>">
					<?php _e( 'Remove', 'um-user-bookmarks' ); ?>
				</a>
			<?php } ?>
		</div>
	</div>

	<div class="um-clear"></div>
	<hr/>
</div>
