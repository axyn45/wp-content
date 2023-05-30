=== Ultimate Member - Real-time Notifications ===
Author URI: https://ultimatemember.com/
Plugin URI: https://ultimatemember.com/extensions/real-time-notifications/
Contributors: ultimatemember, champsupertramp, nsinelnikov
Donate link:
Tags: frontend notifications, user, community
Requires at least: 5.0
Tested up to: 6.0
Stable tag: 2.3.1
License: GNU Version 2 or Any Later Version
License URI: http://www.gnu.org/licenses/gpl-3.0.txt
Requires UM core at least: 2.1.0

Add a real-time notification system to your site so users can receive updates and notifications directly on your website as they happen. This helps to increase user engagement and keep users on your site.

== Description ==

Add a real-time notification system to your site so users can receive updates and notifications directly on your website as they happen. This helps to increase user engagement and keep users on your site.

= Core Notifications =

* Notify user when user role is changed.
* Notify user when someone comments on their post
* Notify user when someone replies to one of their comments
* Notify user when another member views their profile
* Notify user when guests views their profile
* bbPress – Notify user when someone replies to them on topics they created
* bbPress – Notify user when someone replies to them on any specific topic

= Extension Notifications (Requires other extensions to be installed): =

* User Reviews – Notify user when someone leaves them a review/rating
* myCRED – Notify user when they receive points from another user via myCRED. Notify user when they receive points by completing a specific action

= Additional Features: =

* Real-time instant Ajax notifications
* Control the time delay for Ajax notifications
* Every user has a notifications page to see past notifications
* Allow user to control which notifications are turned on and which notifications they do not want to receive
* Admin settings to customize the notification setting, enable and disable notification types
* Users can delete notifications
* Shows number of notifications in browser tab e.g (5)

= Technical Requirements =

* Requires Ultimate Member v2.1.0+
* Real-time notifications require stable hosting otherwise you’ll have to increase the time delay between new notification checks

= Development * Translations =

Want to add a new language to Ultimate Member? Great! You can contribute via [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/ultimate-member).

If you are a developer and you need to know the list of UM Hooks, make this via our [Hooks Documentation](https://docs.ultimatemember.com/article/1324-hooks-list).

= Documentation & Support =

Got a problem or need help with Ultimate Member? Head over to our [documentation](http://docs.ultimatemember.com/) and perform a search of the knowledge base. If you can’t find a solution to your issue then you can create a topic on the [support forum](https://wordpress.org/support/plugin/um-forumwp).

== Installation ==

1. Activate the plugin
2. That's it. Go to Ultimate Member > Settings > Extensions > Private Messaging to customize plugin options
3. For more details, please visit the official [Documentation](http://docs.ultimatemember.com/article/232-notifications-setup) page.

== Changelog ==

= 2.3.1 August 17, 2022 =

* Fixed: Notify user about changing role from wp-admin > Users screen
* Fixed: Load more button display after actions for notifications in the notifications feed
* Fixed: Dropdown.js when scrolling
* Fixed: Data offset when loading notifications feed or making the actions with notification
* Fixed: Notifications and messaging integration. Adding real-time messaging notifications updating. Mark messages as read in the real time

* Templates required update:
  - account-webnotifications.php

= 2.3.0 February 9, 2022 =

* Changed: Notifications list view and async getting notifications via AJAX
* Changed: Getting new notifications count. Red dot with counter is visible only when there are new notifications
* Changed: Getting new notifications when notifications list is visible
* Changed: Notification is marked as read only after "mark as read" action or after visit the notification URL
* Added: Filter "All|Unread" to the notifications list
* Added: "Mark all as read", "Clear all" actions
* Added: Ability to disable notification-type from the notifications list. UX alternative instead of visiting to the Account > Notifications page
* Added: Pagination "Load More" for notifications list in sidebar and static list on the notifications page
* Fixed: Enabled notifications types that was disabled by hardcode
* Fixed: Extension settings structure

* Templates required update:

  - feed.php
  - notifications.php
  - notifications-header.php

* Deprecated templates:

  - no-notifications.php

* Cached and optimized/minified assets(JS/CSS) must be flushed/re-generated after upgrade

= 2.2.2 December 20, 2021 =

* Fixed: Notifications about guest actions for users (e.g. "Guest viewed your profile").
* Fixed: Empty notification photo. Display default avatar in this case.

* Templates required update:
  - notifications-list.php

= 2.2.1 August 3, 2021 =

* Added: New actions for PWA for WP integration `um_notification_after_notif_update` and `um_notification_after_notif_submission`
* Tweak: rewritten User Profile restriction settings checking when guest visits to restricted User Profile

= 2.2.0 July 20, 2021 =

* Fixed: Photo update in notifications (if notifications will be updated after changing user avatar)
* Tweak: WP5.8 widgets screen compatibility

= 2.1.9 March 11, 2021 =

* Added: `comment_excerpt` for comment's reply
* Added: the filter hook in `ajax_check_update()` callback
* Tweak: WordPress 5.7 compatibility

= 2.1.8 December 8, 2020 =

* Fixed: Notifications count with `+` symbol
* Fixed: Bad getaway for not logged in users
* Fixed: No new notifications JS

= 2.1.7 August 11, 2020 =

* Added: WPML support for the notifications
* Added: An integration with UM:JobBoardWP extension
* Fixed: Notification sound script
* Tweak: apply_shortcodes() function support

= 2.1.6: April 1, 2020 =

* Added: Notifications templates for each myCRED hook
* Optimized: Integration 3rd-party notifications
* Fixed: "Profile view" notification log

= 2.1.5: January 21, 2020 =

* Fixed: myCRED notifications

= 2.1.4: November 13, 2019 =

* Fixed: AJAX request for new notifications

= 2.1.3: November 11, 2019 =

* Added: Sanitize functions for request variables
* Added: esc_attr functions to avoid XSS vulnerabilities
* Added: ability to change templates in theme via universal method UM()->get_template()

= 2.1.2: July 18, 2019 =

* Fixed: myCRED integration
* Fixed: Uninstall process

= 2.1.1: May 14, 2019 =

* Fixed: Include notifications template in the footer
* Fixed: Update notification settings in the profile

= 2.1.0: March 29, 2019 =

* Added: Friends mentioned notification

= 2.0.9: March 29, 2019 =

* Fixed: Change role notification

= 2.0.8: January 24, 2019 =

* Fixed: Realtime notifications timer

= 2.0.7: January 24, 2019 =

* Fixed: JS/CSS enqueue

= 2.0.6: November 30, 2018 =

* Fixed: AJAX vulnerabilities
* Optimized: JS/CSS enqueue

= 2.0.5: October 3, 2018 =

* Added: An ability to customize notifications template via theme/child-theme templates
* Fixed: Notifications bell template

= 2.0.4: August 13, 2018 =

* Fixed: Native WP AJAX added

= 2.0.3: April 30, 2018 =

* Added: Loading translation from "wp-content/languages/plugins/" directory

= 2.0.2: September 11, 2017 =

* Tweak: UM2.0 compatibility

= 1.4.2: December 8, 2016 =

* Added: Page restriction
* Added: Notification page in UM Setup
* Tweak: Update translation files: EN, DE
* Fixed: Empty notification settings
* Fixed: Deduction strings
* Fixed: myCRED Notification
* Fixed: Time difference in mysql
* Fixed: Timezone issue
* Fixed: Plugin updater
* Fixed: Notification not showing if icon is disabled.
* Fixed: Medial URL protocol
* Fixed: Comment reply notification


= 1.4.1: January 5, 2016 =

* Tweak: increased check delay to improve performance
* Tweal: responsive notifications panel on all devices
* Fixed: potentially wrong php syntax
* Fixed: css issues on notifications page

= 1.4.0: December 22, 2015 =

* New: added swedish language support
* Tweak: added notification icon option
* Fixed: missing profile photos
* Fixed: sql query

= 1.3.9: December 15, 2015 =

* Fixed: sql injection vulnerability found. credits to @DaveRossow

= 1.3.8: December 15, 2015 =

* Fixed: missing avatars

= 1.3.7: December 11, 2015 =

* Initial release