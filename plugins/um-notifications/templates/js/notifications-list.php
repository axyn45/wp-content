<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<script type="text/template" id="tmpl-um-notifications-list">
	<# _.each( data.notifications, function( notification, key ) { #>
		<div class="um-notification {{{notification.status}}}" id="notification-{{{notification.id}}}" data-notification_id="{{{notification.id}}}">
			<div class="um-notification-link" data-notification_id="{{{notification.id}}}" data-notification_uri="{{{notification.url}}}">
				<img src="{{{notification.photo}}}" data-default="{{{notification.avatar}}}" alt="" class="um-notification-photo" />
				<div class="um-notification-content">
					<div class="um-notification-content-string">{{{notification.content}}}</div>
					<span class="b2" data-time-raw="{{{notification.time}}}">
						{{{notification.icon}}}
						{{{notification.time}}}
					</span>
				</div>
			</div>

			<div class="um-notifications-buttons">
				<# if ( Object.keys( notification.dropdown_actions ).length > 0 ) { #>
					<div class="um-notification-actions">
						<a href="javascript:void(0);" class="um-notification-actions-a">
							<i class="um-faicon-ellipsis-h"></i>
						</a>
						<?php
						$parent = 1 === (int) $sidebar ? '.um-notification-live-feed' : '';
						UM()->member_directory()->dropdown_menu_js( '.um-notification-actions', 'click', 'notification', 'data-notification_id="{{{notification.id}}}" data-type="{{{notification.type}}}" data-width="{{{notification.type}}}"', $parent );
						?>
					</div>
				<# } #>
			</div>
		</div>
	<# }); #>
</script>
