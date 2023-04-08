<?php
/**
 * Template for the account tab "Web notifications"
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-notifications/account-webnotifications.php
 *
 * Used: "Web notifications" tab in Account
 * Call: um_account_content_hook_webnotifications( $output )
 *
 * @package um_ext\um_notifications\templates
 * @version 2.3.1
 *
 * @var array   $logs
 * @var integer $user_id
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="um-field" data-key="">
	<div class="um-field-label"><strong><?php esc_html_e( 'Receiving Notifications', 'um-notifications' ); ?></strong></div>
	<div class="um-field-area">
		<input type="hidden" name="um-notifyme[]" value="1" />

		<?php
		foreach ( $logs as $key => $array ) {
			if ( ! UM()->options()->get( 'log_' . $key ) ) {
				continue;
			}

			$enabled = UM()->Notifications_API()->api()->user_enabled( $key, $user_id );
			if ( $enabled ) {
				?>

				<label class="um-field-checkbox active">
					<input type="checkbox" name="um-notifyme[<?php echo esc_attr( $key ); ?>]" value="1" checked />
					<span class="um-field-checkbox-state"><i class="um-icon-android-checkbox-outline"></i></span>
					<span class="um-field-checkbox-option"><?php echo esc_html( $array['account_desc'] ); ?></span>
				</label>

				<?php
			} else {
				?>

				<label class="um-field-checkbox">
					<input type="checkbox" name="um-notifyme[<?php echo esc_attr( $key ); ?>]" value="1" />
					<span class="um-field-checkbox-state"><i class="um-icon-android-checkbox-outline-blank"></i></span>
					<span class="um-field-checkbox-option"><?php echo esc_html( $array['account_desc'] ); ?></span>
				</label>

				<?php
			}
		}
		?>

		<div class="um-clear"></div>
	</div>
</div>
