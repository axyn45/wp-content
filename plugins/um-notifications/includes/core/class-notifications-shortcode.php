<?php
namespace um_ext\um_notifications\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Notifications_Shortcode
 * @package um_ext\um_notifications\core
 */
class Notifications_Shortcode {


	/**
	 * Notifications_Shortcode constructor.
	 */
	function __construct() {
		add_shortcode( 'ultimatemember_notifications', array( &$this, 'ultimatemember_notifications' ) );
		add_shortcode( 'ultimatemember_notifications_button', array( &$this, 'ultimatemember_notifications_button' ) );
		add_shortcode( 'ultimatemember_unread_notifications_count', array( &$this, 'ultimatemember_unread_notifications_count' ) );

		// legacy
		// @todo replace it by `ultimatemember_unread_notifications_count` in the next major version
		add_shortcode( 'ultimatemember_notification_count', array( &$this, 'ultimatemember_unread_notifications_count' ) );

		add_filter( 'wp_title', array( &$this, 'wp_title' ), 10, 2 );
		add_filter( 'wp_nav_menu_items', array( &$this, 'menu_patterns'), 10, 2 );
	}


	/**
	 * Replace patterns in nav menu
	 *
	 * @param string $items
	 * @param array $args
	 * 
	 * @return string
	 */
	public function menu_patterns( $items, $args ) {
		$pattern_array = array(
			'{um_notifications_button}',
		);

		foreach ( $pattern_array as $pattern ) {
			if ( ! preg_match( $pattern, $items ) ) {
				continue;
			}

			$value = '';
			if ( '{um_notifications_button}' === $pattern ) {
				$value = $this->ultimatemember_notifications_button(
					array(
						'static'      => true,
						'show_always' => true,
					)
				);
			}

			$items = preg_replace( '/' . $pattern . '/', $value, $items );
		}

		return $items;
	}


	/**
	 * Custom title for page
	 *
	 * @param $title
	 * @param null $sep
	 *
	 * @return string
	 */
	public function wp_title( $title, $sep = null ) {
		global $post;

		if ( ! is_user_logged_in() ) {
			return $title;
		}

		if ( isset( $post->ID ) && $post->ID == UM()->permalinks()->core['notifications'] ) {
			global $wpdb;
			$total = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*)
					FROM {$wpdb->prefix}um_notifications
					WHERE user = %d AND 
					      status = 'unread'",
					get_current_user_id()
				)
			);

			if ( $total ) {
				$title = "($total) $title";
			}
		}
		return $title;
	}


	/**
	 * Notifications list shortcode
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	public function ultimatemember_notifications( $atts = array() ) {
		if ( ! is_user_logged_in() ) {
			return '';
		}

		$atts = shortcode_atts(
			array(
				'sidebar' => 0,
			),
			$atts,
			'ultimatemember_notifications'
		);

		wp_enqueue_script( 'um_notifications' );
		wp_enqueue_style( 'um_notifications' );

		return UM()->get_template( 'notifications.php', um_notifications_plugin, array( 'sidebar' => $atts['sidebar'] ) );
	}


	/**
	 * Shortcode "Notifications button"
	 *
	 * @param array $atts
	 * @return string
	 */
	public function ultimatemember_notifications_button( $atts = array() ) {
		if ( ! is_user_logged_in() ) {
			return '';
		}

		$atts = shortcode_atts(
			array(
				'static'      => true,
				'show_always' => UM()->options()->get( 'notification_icon_visibility' ),
				'notify_pos'  => UM()->options()->get( 'notify_pos' ),
			),
			$atts,
			'ultimatemember_notifications_button'
		);

		// legacy
		if ( isset( $atts['hide_if_no_unread'] ) ) {
			$atts['show_always'] = ! $atts['hide_if_no_unread'];
		}

		$new_notifications_array = get_user_meta( get_current_user_id(), 'um_new_notifications', true );
		if ( empty( $new_notifications_array ) ) {
			$new_notifications_array = array();
		}

		$new_notifications = 0;
		if ( ! empty( $new_notifications_array ) ) {
			$log_types = array_keys( UM()->Notifications_API()->api()->get_log_types() );

			global $wpdb;
			$new_notifications = $wpdb->get_var(
				"SELECT COUNT(*)
				FROM {$wpdb->prefix}um_notifications
				WHERE type IN('" . implode( "','", $log_types ) . "') AND
				      id IN('" . implode( "','", $new_notifications_array ) . "')"
			);
		}

		if ( ! $new_notifications && ! $atts['show_always'] ) {
			return '';
		}

		$new_notifications_formatted = ( absint( $new_notifications ) > 9 ) ? __( '9+', 'um-notifications' ) : absint( $new_notifications );

		wp_enqueue_script( 'um_notifications' );
		wp_enqueue_style( 'um_notifications' );

		$output = UM()->get_template(
			'notifications_button.php',
			um_notifications_plugin,
			array(
				'notify_pos'                  => $atts['notify_pos'],
				'new_notifications'           => $new_notifications,
				'new_notifications_formatted' => $new_notifications_formatted,
				'static'                      => $atts['static'],
				'show_always'                 => $atts['show_always'],
			)
		);

		return $output;
	}


	/**
	 * Shortcode helper for getting unread count and display it everywhere
	 *
	 * @return int
	 */
	public function ultimatemember_unread_notifications_count() {
		return UM()->Notifications_API()->api()->unread_count( get_current_user_id() );
	}
}
