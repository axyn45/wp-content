<?php
/**
 * Displays trending hashtags.
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-social-activity/trending.php
 *
 * @see     https://docs.ultimatemember.com/article/1516-templates-map
 * @package um_ext\um_social_activity\templates
 * @version 2.2.8
 *
 * @var array $hashtags
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( empty( $hashtags ) ) {
	return;
}
?>

<ul class="um-activity-trending">

	<?php foreach ( (array) $hashtags as $hashtag ) { ?>

	<li>
		<a href="<?php echo esc_url( add_query_arg( 'hashtag', $hashtag->slug, um_get_core_page( 'activity' ) ) ); ?>">
			#<?php echo esc_html( $hashtag->name ); ?>
		</a>
	</li>

	<?php } ?>

</ul>
