<?php
/**
 * Template for the UM Real-time Notifications sidebar
 * Used to show "Notifications" sidebar if there are notifications
 *
 * Called from the um-notifications/templates/feed.php template
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-notifications/notifications.php
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<!-- um-notifications/templates/notifications.php -->
<?php UM()->get_template( 'js/notifications-list.php', um_notifications_plugin, array( 'sidebar' => $sidebar ), true ); ?>

<div class="um-notification-shortcode">
	<?php UM()->get_template( 'notifications-header.php', um_notifications_plugin, array( 'sidebar' => $sidebar ), true ); ?>
	<div class="um-notification-ajax" data-time="<?php echo esc_attr( time() ); ?>" data-offset="0" data-per_page="10"></div>
	<div class="um-load-more-notifications">
		<span><?php esc_html_e( 'Load more', 'um-notifications' ); ?></span>
	</div>
	<div class="um-ajax-loading-wrap"><div class="um-ajax-loading"></div></div>
	<div class="um-notifications-none" style="display:none;">
		<i class="um-icon-ios-bell"></i><?php esc_html_e( 'No new notifications', 'um-notifications' ); ?>
	</div>
</div>
