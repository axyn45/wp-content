<?php
/**
 * Add a post edit Underscore template to the activity wall.
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-social-activity/edit-post.php
 *
 * @see     https://docs.ultimatemember.com/article/1516-templates-map
 * @package um_ext\um_social_activity\templates
 * @version 2.2.8
 *
 * $var string $form_id
 * @var string $hashtag
 * $var string $mode
 * $var string $template
 * @var int    $user_id
 * @var bool   $user_wall
 * @var string $wall_post
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$timestamp = time();
$nonce     = wp_create_nonce( 'um_upload_nonce-' . $timestamp );
?>

<!-- Edit Post JS Template -->
<script type="text/template" id="tmpl-um-edit-post">
	<form action="" method="post" class="um-activity-publish">
		<input type="hidden" name="action" value="um_activity_publish" />
		<input type="hidden" name="_post_id" value="{{data.post_id}}" />
		<input type="hidden" name="_wall_id" value="<?php echo esc_attr( $user_id ); ?>" />
		<input type="hidden" name="_post_img" value="{{data._photo}}" />
		<input type="hidden" name="_post_img_url" value="{{data._photo_url}}" />
		<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'um-frontend-nonce' ) ); ?>" />

		<div class="um-activity-body">

			<div class="um-activity-textarea">
				<textarea data-photoph="<?php esc_attr_e( 'Say something about this photo', 'um-activity' ); ?>"
						data-ph="<?php esc_attr_e( 'What\'s on your mind?', 'um-activity' ); ?>"
						placeholder="<?php esc_attr_e( 'What\'s on your mind?', 'um-activity' ); ?>"
						class="um-activity-textarea-elem" name="_post_content">{{{data.textarea}}}</textarea>
			</div>

			<div class="um-activity-preview">
				<span class="um-activity-preview-spn">
					<img src="{{data._photo_url}}" alt="" title="" width="" height="" />
					<span class="um-activity-img-remove">
						<i class="um-icon-close"></i>
					</span>
				</span>
			</div>

			<div class="um-clear"></div>
		</div>

		<div class="um-activity-foot">

			<div class="um-activity-left um-activity-insert">

				<?php do_action( 'um_activity_pre_insert_tools' ); ?>

				<?php 
				$allowed_image_types = array(
					"gif",
					"png",
					"jpeg",
					"jpg",
				);
				
				/**
				 * UM hook
				 *
				 * @type filter
				 * @title um_social_activity_allowed_image_types
				 * @description Filter allowed image types
				 * @input_vars
				 * [{"var":"$allowed_image_types","type":"array","desc":"Image Types"}]
				 * @change_log
				 * ["Since: 2.1.1"]
				 * @usage add_filter( 'um_social_activity_allowed_image_types', 'function_name', 10, 1 );
				 * @example
				 * <?php
				 * add_filter( 'um_social_activity_allowed_image_types', 'my_get_field', 10, 1 );
				 * function my_get_field( $data ) {
				 *     // your code here
				 *     return $data;
				 * }
				 * ?>
				 */
				$allowed_image_types = apply_filters("um_social_activity_allowed_image_types", $allowed_image_types );
				?>

				<?php if ( ! UM()->roles()->um_user_can( 'activity_photo_off' ) ) { ?>
					<a href="javascript:void(0);" class="um-activity-insert-photo um-tip-s"
							data-timestamp="<?php echo esc_attr( $timestamp ); ?>"
							data-nonce="<?php echo esc_attr( $nonce ); ?>"
							data-allowed="<?php echo implode( ",", $allowed_image_types); ?>"
							data-size-err="<?php esc_attr_e( 'Image is too large', 'um-activity' ); ?>"
							data-ext-err="<?php esc_attr_e( 'Please upload a valid image', 'um-activity' ); ?>"
							title="<?php esc_attr_e( 'Add photo', 'um-activity' ); ?>">
						<i class="um-faicon-camera"></i>
					</a>
				<?php } ?>

				<?php do_action( 'um_activity_post_insert_tools' ); ?>

				<div class="um-clear"></div>
			</div>

			<div class="um-activity-right">
				<a href="javascript:void(0);" class="um-activity-edit-cancel">
					<?php esc_html_e( 'Cancel editing', 'um-activity' ); ?>
				</a>
				<a href="javascript:void(0);" class="um-button um-activity-post um-disabled">
					<?php esc_html_e( 'Update', 'um-activity' ); ?>
				</a>
			</div>
			<div class="um-clear"></div>

		</div>
	</form>
</script>
