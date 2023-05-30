<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div class="um-notification-header">
	<?php if ( 1 === (int) $sidebar ) { ?>
		<div class="um-notification-header-row">
			<h4><?php esc_html_e( 'Notifications', 'um-notifications' ); ?></h4>
			<a href="javascript:void(0);" class="um-notification-i-close"><i class="um-icon-android-close"></i></a>
		</div>
	<?php } ?>
	<div class="um-notification-header-row">
		<div class="um-notifications-filters">
			<span class="um-notifications-filter active" data-filter="all"><?php esc_html_e( 'All', 'um-notifications' ); ?></span>
			<span class="um-notifications-filter" data-filter="unread"><?php esc_html_e( 'Unread', 'um-notifications' ); ?></span>
		</div>

		<a href="javascript:void(0);" class="um-notifications-options-a"><i class="um-faicon-ellipsis-h"></i></a>

		<?php
		$items = array();
		if ( 1 === (int) $sidebar ) {
			$items[] = '<a href="' . esc_url( um_get_core_page( 'notifications' ) ) . '">' . esc_html__( 'See all notifications', 'um-notifications' ) . '</a>';
		}

		$items = array_merge( $items, array(
			'<a href="javascript:void(0);" class="um-notifications-mark-all-read">' . esc_html__( 'Mark all as read', 'um-notifications' ) . '</a>',
			'<a href="javascript:void(0);" class="um-notifications-clear-all">' . esc_html__( 'Clear all notifications', 'um-notifications' ) . '</a>',
			'<a href="' . esc_url( UM()->account()->tab_link( 'webnotifications' ) ) . '">' . esc_html__( 'Notifications settings', 'um-notifications' ) . '</a>',
		) );

		$items = apply_filters( 'um_notifications_header_items', $items );
		$parent = 1 === (int) $sidebar ? '.um-notification-live-feed' : '';
		?>

		<?php UM()->member_directory()->dropdown_menu( '.um-notifications-options-a', 'click', $items, $parent ); ?>
	</div>
</div>
