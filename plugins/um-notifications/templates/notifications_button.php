<?php
/**
 * Template for the UM Real-time Notifications Button
 *
 * Called from the Notifications_Shortcode->ultimatemember_notifications_button() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-notifications/notifications_button.php
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div class="um-notification-b <?php echo esc_attr( $notify_pos ); ?>" data-show-always="<?php echo esc_attr( $show_always ); ?>"<?php echo empty( $static ) ? '' : ' style="position:static;"'; ?>>
	<i class="um-icon-ios-bell"></i>
	<span class="um-notification-live-count count-<?php echo esc_attr( $new_notifications ); ?>">
		<?php echo esc_html( $new_notifications_formatted ); ?>
	</span>
</div>
