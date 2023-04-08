<?php
namespace um_ext\um_notifications\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Notifications_Main_API
 * @package um_ext\um_notifications\core
 */
class Notifications_Main_API {


	/**
	 * Did user enable this web notification?
	 *
	 * @param $key
	 * @param $user_id
	 *
	 * @return bool
	 */
	function user_enabled( $key, $user_id ) {
		if ( ! UM()->options()->get( 'log_' . $key ) ) {
			return false;
		}
		$prefs = get_user_meta( $user_id, '_notifications_prefs', true );
		if ( isset( $prefs[ $key ] ) && ! $prefs[ $key ] ) {
			return false;
		}

		// if all checkboxes were not selected
		if ( $prefs === array('') ) {
			return false;
		}

		return true;
	}


	/**
	 * Register notification types
	 *
	 * @return array
	 */
	function get_log_types() {
		$logs = array(
			'upgrade_role'        => array(
				'title'        => __( 'Role upgrade', 'um-notifications' ),
				'account_desc' => __( 'When my membership level is changed', 'um-notifications' ),
			),
			'comment_reply'       => array(
				'title'        => __( 'New user comment reply', 'um-notifications' ),
				'account_desc' => __( 'When a member replies to one of my comments', 'um-notifications' ),
			),
			'guest_comment_reply' => array(
				'title'        => __( 'New guest comment reply', 'um-notifications' ),
				'account_desc' => __( 'When a guest replies to one of my comments', 'um-notifications' ),
			),
			'user_comment'        => array(
				'title'        => __( 'New user comment', 'um-notifications' ),
				'account_desc' => __( 'When a member comments on my posts', 'um-notifications' ),
			),
			'guest_comment'       => array(
				'title'        => __( 'New guest comment', 'um-notifications' ),
				'account_desc' => __( 'When a guest comments on my posts', 'um-notifications' ),
			),
			'profile_view'        => array(
				'title'        => __( 'User view profile', 'um-notifications' ),
				'account_desc' => __( 'When a member views my profile', 'um-notifications' ),
			),
			'profile_view_guest'  => array(
				'title'        => __( 'Guest view profile', 'um-notifications' ),
				'account_desc' => __( 'When a guest views my profile', 'um-notifications' ),
			),
		);

		$logs = apply_filters( 'um_notifications_core_log_types', $logs );

		return $logs;
	}


	/**
	 * Get unread count by user ID
	 *
	 * @param int|null $user_id
	 * @return int
	 */
	function unread_count( $user_id = null ) {
		global $wpdb;

		$user_id = ! empty( $user_id ) ? $user_id : get_current_user_id();

		$count = $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*)
			FROM {$wpdb->prefix}um_notifications
			WHERE user = %d AND
				  status = 'unread'",
			$user_id
		) );

		return absint( $count );
	}


	/**
	 * Deletes a notification by its ID
	 *
	 * @param int $notification_id
	 */
	function delete_log( $notification_id ) {
		global $wpdb;

		$this->delete_notification_id_from_meta( $notification_id );

		$wpdb->delete(
			"{$wpdb->prefix}um_notifications",
			array(
				'id' => $notification_id,
			),
			array(
				'%d',
			)
		);
	}


	/**
	 * Gets icon for notification
	 *
	 * @param $type
	 *
	 * @return null|string
	 */
	function get_icon( $type ) {
		$output = null;
		switch( $type ) {
			default:
				$output = apply_filters( 'um_notifications_get_icon', $output, $type );
				break;
			case 'comment_reply':
			case 'guest_comment_reply':
				$output = '<i class="um-icon-chatboxes" style="color: #00b56c"></i>';
				break;
			case 'user_comment':
			case 'guest_comment':
				$output = '<i class="um-faicon-comment" style="color: #DB6CD2"></i>';
				break;
			case 'user_review':
				$output = '<i class="um-faicon-star" style="color: #FFD700"></i>';
				break;
			case 'profile_view':
			case 'profile_view_guest':
				$output = '<i class="um-faicon-eye" style="color: #6CB9DB"></i>';
				break;
			case 'bbpress_user_reply':
			case 'bbpress_guest_reply':
				$output = '<i class="um-faicon-comments" style="color: #67E264"></i>';
				break;
			case 'upgrade_role':
				$output = '<i class="um-faicon-exchange" style="color: #999"></i>';
				break;
		}

		return $output;
	}


	/**
	 * Saves a notification
	 *
	 * @param $user_id
	 * @param $type
	 * @param array $vars
	 */
	public function store_notification( $user_id, $type, $vars = array() ) {
		global $wpdb;

		// Check if user opted-in
		if ( ! $this->user_enabled( $type, $user_id ) ) {
			return;
		}

		if ( UM()->external_integrations()->is_wpml_active() ) {
			$content = $this->wpml_store_notification( $type, $vars );
		} else {
			$content = $this->get_notify_content( $type, $vars );
		}

		if ( $vars && isset( $vars['photo'] ) ) {
			$photo = $vars['photo'];
		} else {
			$photo = um_get_default_avatar_uri();
		}

		$url = '';
		if ( $vars && isset( $vars['notification_uri'] ) ) {
			$url = $vars['notification_uri'];
		}

		$table_name = $wpdb->prefix . 'um_notifications';

		$exclude_type = apply_filters(
			'um_notifications_exclude_types',
			array(
				/*'comment_reply',
				'new_wall_post',
				'new_wall_comment',
				'bbpress_user_reply',
				'bbpress_guest_reply',*/
			)
		);

		if ( ! empty( $content ) && ! in_array( $type, $exclude_type ) ) {
			// Try to update a similar log
			$result = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id
					FROM {$table_name}
					WHERE user = %d AND
						  type = %s AND
						  content = %s
					ORDER BY time DESC",
					$user_id,
					$type,
					$content
				)
			);

			if ( ! empty( $result ) ) {
				$wpdb->update(
					$table_name,
					array(
						'status' => 'unread',
						'time'   => date( 'Y-m-d H:i:s' ),
						'url'    => $url,
						'photo'  => $photo,
					),
					array(
						'user'    => $user_id,
						'type'    => $type,
						'content' => $content,
					)
				);

				do_action( 'um_notification_after_notif_update', $user_id, $type );

				$this->store_metadata( $user_id, $result );

				return;
			}

			$wpdb->insert(
				$table_name,
				array(
					'time'    => date( 'Y-m-d H:i:s' ),
					'user'    => $user_id,
					'status'  => 'unread',
					'photo'   => $photo,
					'type'    => $type,
					'url'     => $url,
					'content' => $content,
				)
			);

			do_action( 'um_notification_after_notif_submission', $user_id, $type );

			$this->store_metadata( $user_id, $wpdb->insert_id );
		}
	}


	/**
	 * Saves notifications to a metadata
	 *
	 * @param int $user_id
	 * @param int $notification_id
	 */
	public function store_metadata( $user_id, $notification_id ) {
		$new_notifications = get_user_meta( $user_id, 'um_new_notifications', true );
		if ( empty( $new_notifications ) ) {
			$new_notifications = array();
		}

		$new_notifications[] = $notification_id;
		$new_notifications   = array_unique( $new_notifications );
		update_user_meta( $user_id, 'um_new_notifications', $new_notifications );
	}


	/**
	 * Handler to delete notification from a metadata
	 *
	 * @param $notification_id
	 */
	public function delete_notification_id_from_meta( $notification_id ) {
		global $wpdb;
		$user_id = $wpdb->get_var(
			$wpdb->prepare(
			"SELECT user
				FROM {$wpdb->prefix}um_notifications
				WHERE id = %d",
				$notification_id
			)
		);

		if ( empty( $user_id ) ) {
			return;
		}

		$new_notifications = get_user_meta( $user_id, 'um_new_notifications', true );
		if ( empty( $new_notifications ) ) {
			return;
		}

		$key = array_search( $notification_id, $new_notifications );
		if ( false === $key ) {
			return;
		}

		unset( $new_notifications[ $key ] );
		if ( empty( $new_notifications ) ) {
			$new_notifications = '';
		}
		update_user_meta( $user_id, 'um_new_notifications', $new_notifications );
	}


	/**
	 * Saves a notification when WPML is active
	 *
	 * @param string $type
	 * @param array $vars
	 *
	 * @return string
	 */
	function wpml_store_notification( $type, $vars ) {
		global $sitepress;

		$content = array(
			''  => UM()->options()->get( 'log_' . $type . '_template' ),
		);

		$active_languages = $sitepress->get_active_languages();

		if ( ! empty( $active_languages ) ) {
			$current_lang = $sitepress->get_current_language();

			foreach ( array_keys( $active_languages ) as $language ) {
				$sitepress->switch_lang( $language );
				$content[ $language ] = $this->get_notify_content( $type, $vars );
			}

			$sitepress->switch_lang( $current_lang );
		}

		return serialize( $content );
	}


	/**
	 * Get notification content
	 *
	 * @param $type
	 * @param array $vars
	 *
	 * @return string|null
	 */
	function get_notify_content( $type, $vars = array() ) {
		$content = UM()->options()->get( 'log_' . $type . '_template' );
		$content = apply_filters( 'um_notification_modify_entry', $content, $type, $vars );
		$content = apply_filters( "um_notification_modify_entry_{$type}", $content, $vars );

		if ( $vars ) {
			foreach ( $vars as $key => $var ) {
				if ( $key == 'mycred_object' || $key == 'mycred_run_array' ) {
					continue;
				}
				$content = str_replace( '{' . $key . '}', $var, $content );
			}
		}

		// This code breaks the content. It removes words that are used multiple times.
		//$content = implode( ' ', array_unique( explode( ' ', $content ) ) );

		$content = apply_filters( 'um_notification_modify_entry_with_placeholders', $content, $type, $vars );
		$content = apply_filters( "um_notification_modify_entry_{$type}_with_placeholders", $content, $vars );
		return $content;
	}


	/**
	 * Mark as read
	 *
	 * @param int $notification_id
	 */
	function set_as_read( $notification_id ) {
		global $wpdb;

		$user_id = get_current_user_id();
		$wpdb->update(
			"{$wpdb->prefix}um_notifications",
			array(
				'status' => 'read',
			),
			array(
				'user' => $user_id,
				'id'   => $notification_id,
			),
			array(
				'%s',
			),
			array(
				'%d',
				'%d',
			)
		);
	}


	/**
	 * Delete a notification
	 */
	public function ajax_delete_log() {
		UM()->check_ajax_nonce();

		if ( empty( $_POST['notification_id'] ) ) {
			wp_send_json_error( __( 'Wrong notification ID', 'um-notifications' ) );
		}

		$this->delete_log( absint( $_POST['notification_id'] ) );

		global $wpdb;

		$time       = absint( $_POST['time'] );
		$time_where = $wpdb->prepare( ' AND time <= %s ', date( 'Y-m-d H:i:s', $time ) );

		$unread  = (bool) $_POST['unread'];
		$offset  = absint( $_POST['offset'] );
		$user_id = get_current_user_id();

		$unread_where = $unread ? " AND status = 'unread' " : '';
		$log_types    = array_keys( $this->get_log_types() );

		$notifications = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
				FROM {$wpdb->prefix}um_notifications
				WHERE type IN('" . implode( "','", $log_types ) . "') AND
				  user = %d
				  {$unread_where}
				  {$time_where}
				ORDER BY time DESC
				LIMIT 1
				OFFSET %d",
				$user_id,
				$offset - 1
			)
		);

		if ( ! empty( $notifications ) ) {
			$notifications = apply_filters( 'um_notifications_get_notifications_response', $notifications, 1, $unread, $time );
			$notifications = $this->built_notifications_template( $notifications );
		} else {
			$notifications = array();
		}

		$total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->prefix}um_notifications
				WHERE type IN('" . implode( "','", $log_types ) . "') AND
					  user = %d
					  {$unread_where}
					  {$time_where}",
				$user_id
			)
		);

		$total = ! empty( $total ) ? absint( $total ) : 0;

		$output = apply_filters(
			'um_notifications_ajax_on_load_notification',
			array(
				'notifications' => $notifications,
				'total'         => $total,
			)
		);
		wp_send_json_success( $output );
	}


	/**
	 * Delete all notification
	 */
	public function ajax_delete_all_log() {
		UM()->check_ajax_nonce();

		$log_types = array_keys( $this->get_log_types() );

		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"DELETE 
				FROM {$wpdb->prefix}um_notifications 
				WHERE type IN('" . implode( "','", $log_types ) . "') AND 
					  user = %d",
				get_current_user_id()
			)
		);

		wp_send_json_success();
	}


	/**
	 * Mark a notification as read
	 */
	public function ajax_mark_as_read() {
		UM()->check_ajax_nonce();

		if ( empty( $_POST['notification_id'] ) ) {
			wp_send_json_error( __( 'Wrong notification ID', 'um-notifications' ) );
		}

		$this->set_as_read( absint( $_POST['notification_id'] ) );

		$unread = (bool) $_POST['unread'];

		if ( ! $unread ) {
			wp_send_json_success();
		} else {
			global $wpdb;

			$time = absint( $_POST['time'] );
			$time_where = $wpdb->prepare( ' AND time <= %s ', date( 'Y-m-d H:i:s', $time ) );

			$offset   = absint( $_POST['offset'] );
			$user_id  = get_current_user_id();

			$unread_where = " AND status = 'unread' ";
			$log_types    = array_keys( $this->get_log_types() );

			$notifications = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT *
					FROM {$wpdb->prefix}um_notifications
					WHERE type IN('" . implode( "','", $log_types ) . "') AND
					  user = %d
					  {$unread_where}
					  {$time_where}
					ORDER BY time DESC
					LIMIT 1
					OFFSET %d",
					$user_id,
					$offset - 1
				)
			);

			if ( ! empty( $notifications ) ) {
				$notifications = apply_filters( 'um_notifications_get_notifications_response', $notifications, 1, $unread, $time );
				$notifications = $this->built_notifications_template( $notifications );
			} else {
				$notifications = array();
			}

			$total = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*)
					FROM {$wpdb->prefix}um_notifications
					WHERE type IN('" . implode( "','", $log_types ) . "') AND
						  user = %d
						  {$unread_where}
						  {$time_where}",
					$user_id
				)
			);

			$total = ! empty( $total ) ? absint( $total ) : 0;

			$output = apply_filters(
				'um_notifications_ajax_on_load_notification',
				array(
					'notifications' => $notifications,
					'total'         => $total,
				)
			);
			wp_send_json_success( $output );
		}
	}


	/**
	 * Mark all notifications as read
	 */
	public function ajax_mark_all_as_read() {
		UM()->check_ajax_nonce();

		global $wpdb;
		$user_id = get_current_user_id();

		$log_types = array_keys( $this->get_log_types() );

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}um_notifications 
				SET status = 'read' 
				WHERE type IN('" . implode( "','", $log_types ) . "') AND 
					  status = 'unread' AND 
					  user = %d",
				$user_id
			)
		);

		wp_send_json_success();
	}


	public function ajax_change_notifications_prefs() {
		UM()->check_ajax_nonce();

		$user_id = get_current_user_id();
		$type    = sanitize_key( $_POST['notification_type'] );
		$prefs   = get_user_meta( $user_id, '_notifications_prefs', true );

		if ( empty( $prefs ) ) {
			$prefs = UM()->Notifications_API()->api()->get_log_types();
			$prefs = array_fill_keys( array_keys( $prefs ), 1 );
		}
		$prefs[ $type ] = 0;

		update_user_meta( $user_id, '_notifications_prefs', $prefs );

		wp_send_json_success();
	}


	/**
	 * Checks for update
	 */
	public function ajax_check_update() {
		UM()->check_ajax_nonce();

		if ( ! UM()->options()->get( 'realtime_notify' ) ) {
			$output = apply_filters(
				'um_notifications_ajax_check_update_no_realtime',
				array(
					'notifications' => array(),
					'time'          => time(),
				)
			);
			wp_send_json_success( $output );
		}

		$unread = (bool) $_POST['unread'];
		$time   = absint( $_POST['time'] );

		//hard reset the new notifications because they all will be displayed after AJAX response
		update_user_meta( get_current_user_id(), 'um_new_notifications', '' );

		global $wpdb;

		$user_id      = get_current_user_id();
		$unread_where = $unread ? " status = 'unread' AND " : '';
		$log_types    = array_keys( $this->get_log_types() );

		$notifications = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
					FROM {$wpdb->prefix}um_notifications
					WHERE type IN('" . implode( "','", $log_types ) . "') AND
						  user = %d AND
						  {$unread_where}
						  time > %s
					ORDER BY time DESC",
				$user_id,
				date( 'Y-m-d H:i:s', $time )
			)
		);

		if ( ! empty( $notifications ) ) {
			$notifications = apply_filters( 'um_notifications_get_new_notifications', $notifications, $unread );
			$notifications = $this->built_notifications_template( $notifications );
		} else {
			$notifications = array();
		}

		$output = apply_filters(
			'um_notifications_ajax_check_update',
			array(
				'notifications' => $notifications,
				'time'          => time(),
			)
		);
		wp_send_json_success( $output );
	}


	/**
	 * Get notifications on load
	 */
	public function ajax_on_load_notification() {
		UM()->check_ajax_nonce();

		global $wpdb;
		// using time only for the pagination for not getting the wrong offset with newest notifications. There is the separate query for getting newest.
		$time = 0;
		$time_where = '';
		if ( isset( $_POST['time'] ) ) {
			$time = absint( $_POST['time'] );
			$time_where = $wpdb->prepare( ' AND time <= %s ', date( 'Y-m-d H:i:s', $time ) );
		}

		$unread   = (bool) $_POST['unread'];
		$offset   = absint( $_POST['offset'] );
		$per_page = absint( $_POST['per_page'] );
		$user_id  = get_current_user_id();

		//hard reset the new notifications because they all will be displayed after AJAX response
		update_user_meta( $user_id, 'um_new_notifications', '' );

		$unread_where = $unread ? " AND status = 'unread' " : '';
		$log_types    = array_keys( $this->get_log_types() );

		$notifications = $wpdb->get_results(
			$wpdb->prepare(
			"SELECT *
				FROM {$wpdb->prefix}um_notifications
				WHERE type IN('" . implode( "','", $log_types ) . "') AND
					  user = %d
					  {$unread_where}
					  {$time_where}
				ORDER BY time DESC
				LIMIT %d
				OFFSET %d",
				$user_id,
				$per_page,
				$offset
			)
		);

		if ( ! empty( $notifications ) ) {
			$notifications = apply_filters( 'um_notifications_get_notifications_response', $notifications, $per_page, $unread, $time );
			$notifications = $this->built_notifications_template( $notifications );
		} else {
			$notifications = array();
		}

		$total = $wpdb->get_var(
			$wpdb->prepare(
			"SELECT COUNT(*)
				FROM {$wpdb->prefix}um_notifications
				WHERE type IN('" . implode( "','", $log_types ) . "') AND
					  user = %d
					  {$unread_where}
					  {$time_where}",
				$user_id
			)
		);

		$total = ! empty( $total ) ? absint( $total ) : 0;

		$output = apply_filters(
			'um_notifications_ajax_on_load_notification',
			array(
				'notifications' => $notifications,
				'total'         => $total,
				'time'          => time(),
			)
		);
		wp_send_json_success( $output );
	}


	public function built_notifications_template( $notifications ) {
		$dropdown_actions = array(
			'um-read-notification'    => array(
				'title' => __( 'Mark as read', 'um-notifications' ),
			),
			'um-remove-notification'  => array(
				'title' => __( 'Remove notification', 'um-notifications' ),
			),
			'um-disable-notification' => array(
				'title' => __( 'Disable this type of notifications', 'um-notifications' ),
			),
		);

		foreach ( $notifications as &$notification ) {
			$notification->user_id = stripslashes( get_current_user_id() );
			$notification->content = stripslashes( $notification->content );
			$notification->photo   = esc_url( um_secure_media_uri( $notification->photo ) );
			$notification->avatar  = esc_url( um_secure_media_uri( um_get_default_avatar_uri() ) );
			$notification->icon    = UM()->Notifications_API()->api()->get_icon( $notification->type );
			$notification->time    = sprintf( __( '%s ago', 'um-notifications' ), human_time_diff( strtotime( $notification->time ) ) );

			$actions = $dropdown_actions;
			// hide `mark as read` action if already read
			if ( 'read' === $notification->status ) {
				unset( $actions['um-read-notification'] );
			}

			// hide `disable` action if already disabled
			if ( ! $this->user_enabled( $notification->type, get_current_user_id() ) ) {
				unset( $actions['um-disable-notification'] );
			}

			$notification->dropdown_actions = $actions;

			$notification = apply_filters( 'um_notifications_build_notification_data', $notification );
		}

		return $notifications;
	}


	/**
	 * Getting the new notifications count
	 */
	public function ajax_get_new_count() {
		UM()->check_ajax_nonce();

		if ( ! UM()->options()->get( 'realtime_notify' ) ) {
			wp_send_json_error( __( 'Real-time is disabled', 'um-notifications' ) );
		}

		$new_notifications_array = get_user_meta( get_current_user_id(), 'um_new_notifications', true );
		if ( empty( $new_notifications_array ) ) {
			$new_notifications_array = array();
		}

		$new_notifications = 0;
		if ( ! empty( $new_notifications_array ) ) {
			$log_types = array_keys( $this->get_log_types() );

			global $wpdb;
			$new_notifications = $wpdb->get_var(
				"SELECT COUNT(*)
				FROM {$wpdb->prefix}um_notifications
				WHERE type IN('" . implode( "','", $log_types ) . "') AND
					  id IN('" . implode( "','", $new_notifications_array ) . "')"
			);
		}

		$new_notifications_formatted = ( absint( $new_notifications ) > 9 ) ? __( '9+', 'um-notifications' ) : absint( $new_notifications );

		$output = apply_filters(
			'um_notifications_ajax_get_new_count',
			array(
				'new_notifications_formatted' => esc_html( $new_notifications_formatted ),
				'new_notifications'           => $new_notifications,
			)
		);

		wp_send_json_success( $output );
	}
}
