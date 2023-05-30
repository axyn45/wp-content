function um_notification_flush_feed() {
	jQuery('.um-notification-ajax').html('');
}


function um_notification_show_loader() {
	jQuery('.um-ajax-loading-wrap').show();
}


function um_notification_hide_loader() {
	jQuery('.um-ajax-loading-wrap').hide();
}

/**
 * AJAX request for a new notification
 */
function um_load_notifications() {
	if ( um_load_notifications.inProcess ) {
		return;
	}

	if ( um_get_notifications.inProcess ) {
		return;
	}

	if ( jQuery('.um-notification-ajax').is(':hidden') ) {
		// sidebar is hidden run only getting the count
		if ( jQuery('.um-notification-live-count').length ) {
			um_load_notifications.xhr = jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'post',
				dataType: 'json',
				data: {
					action: 'um_notification_get_new_count',
					nonce: um_scripts.nonce
				},
				beforeSend: function() {
					um_load_notifications.inProcess = true;
				},
				complete: function() {
					um_load_notifications.inProcess = false;
				},
				success: function( response ) {
					if ( response.success !== true ) {
						console.error( "UM: Request 'um_notification_get_new_count' failed.", response );
						return;
					}

					var um_notification_icon = jQuery('.um-notification-b, .header-notification-box');
					// Display a quantity of new items as a number in red
					var new_count = response.data.new_notifications;

					if ( new_count !== 0 ) {
						um_notification_icon.animate({'bottom' : '25px'}).addClass( 'has-new' )
							.find('.um-notification-live-count').attr('class', 'um-notification-live-count count-' + new_count ).html( response.data.new_notifications_formatted );
						um_notifications_recalculate_new();
						// jQuery( document ).trigger( 'um_notification_refresh_count', response.data );
						um_animate_bubble();
					} else {
						um_notification_icon.find('.um-notification-live-count').attr('class', 'um-notification-live-count count-' + 0 ).html( 0 );
						um_notifications_recalculate_new();
						if ( um_notification_icon.data('show-always') != true ) {
							um_notification_icon.animate({'bottom': '-220px'}).removeClass('has-new');
						}
						um_stop_bubble();
					}
				}
			});
		}
	} else {
		var unread = 'unread' === jQuery( '.um-notifications-filter.active').data('filter') ? 1 : 0;
		var time = jQuery('.um-notification-ajax').data('time');

		um_load_notifications.xhr = jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			dataType: 'json',
			data: {
				action: 'um_notification_check_update',
				unread: unread,
				time: time,
				nonce: um_scripts.nonce
			},
			beforeSend: function() {
				um_load_notifications.inProcess = true;
			},
			complete: function() {
				um_load_notifications.inProcess = false;
			},
			success: function( response ) {
				if ( um_notifications_filter_trigger ) {
					um_notifications_filter_trigger = false;
					um_load_notifications.inProcess = false;
					return;
				}

				var wrapper = jQuery('.um-notification-ajax');
				// update the latest response time
				wrapper.data( 'time', response.data.time );

				if ( response.data.notifications.length ) {
					var template = wp.template( 'um-notifications-list' );
					var template_content = template({
						notifications: response.data.notifications
					});

					var already_exists = 0;
					for ( var i = 0; i < response.data.notifications.length; i++ ) {
						if ( i in response.data.notifications ) {
							var already_exists_obj = jQuery('.um-notification[data-notification_id="' + response.data.notifications[ i ].id + '"');
							if ( already_exists_obj.length ) {
								already_exists_obj.remove();
								already_exists++;
							}
						}
					}

					var offset = parseInt( wrapper.data('offset') );
					var new_offset = offset + parseInt( response.data.notifications.length ) - already_exists;
					wrapper.prepend( template_content ).data( 'offset', new_offset );

					jQuery('.um-notifications-none').hide();

					um_init_new_dropdown();
					um_notifications_maybe_default_image();
				}
			}
		});
	}
}


/**
 * AJAX get notifications
 */
function um_get_notifications( pagination ) {
	if ( um_get_notifications.inProcess ) {
		return;
	}

	if ( ! um_notifications_filter_trigger ) {
		if ( um_load_notifications.inProcess ) {
			um_load_notifications.xhr.abort();
			//return;
		}
	}

	var unread = 'unread' === jQuery('.um-notifications-filter.active').data('filter') ? 1 : 0;
	var results_wrapper = jQuery('.um-notification-ajax');

	var offset = results_wrapper.data('offset');
	var per_page = results_wrapper.data('per_page');

	var request = {
		action: 'um_get_on_load_notification',
		unread: unread,
		offset: offset,
		per_page: per_page,
		nonce: um_scripts.nonce
	};
	if ( pagination ) {
		request.time = results_wrapper.data('time');
	}

	um_get_notifications.xhr = jQuery.ajax({
		url: wp.ajax.settings.url,
		type: 'post',
		dataType: 'json',
		data: request,
		beforeSend: function () {
			um_load_notifications.inProcess = true;
			um_get_notifications.inProcess = true;
		},
		complete: function () {
			um_load_notifications.inProcess = false;
			um_get_notifications.inProcess = false;
			jQuery('.um-load-more-notifications').removeClass('disabled');
		},
		success: function ( response ) {
			jQuery('.um-ajax-loading-wrap').hide();

			var new_offset = parseInt( results_wrapper.data('offset') );

			if ( response.data.notifications.length ) {
				new_offset = new_offset + parseInt( response.data.notifications.length );

				var template = wp.template('um-notifications-list');
				var template_content = template({
					notifications: response.data.notifications
				});
				results_wrapper.append( template_content ).data( 'offset', new_offset );

				jQuery('.um-notifications-none').hide();

				um_init_new_dropdown();
				um_notifications_maybe_default_image();
			} else {
				if ( results_wrapper.find( '.um-notification' ).length === 0 ) {
					jQuery('.um-notifications-none').show();
				}
			}

			if ( ! pagination ) {
				results_wrapper.data('time', response.data.time );
			}

			if ( new_offset >= parseInt( response.data.total ) ) {
				results_wrapper.siblings( '.um-load-more-notifications' ).hide();
			} else {
				results_wrapper.siblings( '.um-load-more-notifications' ).show();
			}
		}
	});
}


/**
 *
 */
function um_animate_bubble() {
	if ( jQuery('.um-notification-b').length ) {
		jQuery('.um-notification-b').addClass('um-effect-pop');
	}
}


/**
 *
 */
function um_stop_bubble() {
	if ( jQuery('.um-notification-b').length ) {
		jQuery('.um-notification-b').removeClass('um-effect-pop');
	}
}


/**
 * Responsiveness for the sidebar
 */
function um_notification_responsive() {
	var dwidth = jQuery(window).width();
	if ( dwidth < 400 ) {
		jQuery('.um-notification-live-feed').css({'width':dwidth + 'px'});
	} else {
		jQuery('.um-notification-live-feed').css({'width':'400px'});
	}
}


/**
 * Play Notification Sound
 * @returns null
 */
function um_notification_sound( e, data ) {
	var $bell = jQuery( '.um-notification-b, .header-notification-box' );
	if ( ! $bell.length || ! $bell.hasClass( 'has-new' ) || typeof ( um_notifications.unread_count ) === 'undefined' || typeof ( data.unread_count ) === 'undefined' ) {
		return;
	}

	if ( data.unread_count > um_notifications.unread_count ) {
		var sound = new Audio( um_notifications.sound_url );
		var promise = sound.play();

		if ( promise !== undefined ) {
			promise.then( function (res) {
				console.log( 'Notification sound played!' );
			} ).catch( function (error) {
				console.log( error.message );
			} );
		}
	}
}


// Show red counter if there are new notifications
function um_notifications_recalculate_new() {
	if ( jQuery('.um-notification-live-count').length ) {
		if ( parseInt( jQuery('.um-notification-live-count').html() ) > 0 ) {
			jQuery('.um-notification-live-count').show();
		} else {
			jQuery('.um-notification-live-count').hide();
		}
	}
}


function um_notifications_maybe_default_image() {
	jQuery('.um-notification-photo').on( 'error', function() {
		jQuery(this).attr( 'src', jQuery(this).data('default') );
	});
}

function um_notifications_init_interval() {
	/* Load notifications */
	if ( parseInt( um_notifications.timer ) !== 0 ) {
		um_notifications_interval_id = setInterval( um_load_notifications, parseInt( um_notifications.timer ) );
	}
}

var um_notifications_filter_trigger = false;

var um_notifications_interval_id;
// check if browser tab is active
document.addEventListener( 'visibilitychange', function() {
	if ( document.hidden ) {
		// stop send ajax when browser tab is not active
		clearInterval( um_notifications_interval_id );
	} else {
		// send ajax when browser tab is active
		um_notifications_init_interval();
	}
});


jQuery( window ).on( 'resize', function() {
	um_notification_responsive();
});


jQuery(document).ready(function() {
	/* Close feed window */
	jQuery(document.body).on( 'click', '.um-notification-i-close', function(e) {
		e.preventDefault();
		jQuery('.um-notification-live-feed').hide().find( '.um-notification-ajax' ).data('offset', 0).html('');
	});


	um_notifications_recalculate_new();

	um_notifications_init_interval();

	if ( jQuery('.um-notification-ajax').is(':visible') ) {
		um_get_notifications();
	}

	/* Play Notification Sound */
	if ( parseInt( um_notifications.sound ) && um_notifications.sound_url ) {
		jQuery( document ).on( 'um_notification_refresh_count', um_notification_sound );
	}


	jQuery(document.body).on( 'click', '.um-notifications-mark-all-read', function() {
		wp.ajax.send( 'um_notification_mark_all_as_read', {
			data: {
				nonce: um_scripts.nonce
			},
			success: function( data ) {
				var unread = 'unread' === jQuery( '.um-notifications-filter.active' ).data('filter');
				if ( ! unread ) {
					jQuery('.um-notification.unread').each(function () {
						jQuery(this).removeClass('unread').addClass('read');
					});
				} else {
					jQuery('.um-notification-ajax').html('').data('offset', 0);
					jQuery('.um-notifications-none').show();
					jQuery('.um-load-more-notifications').hide();
				}
			},
			error: function (data) {
				console.log(data);
			}
		});
	});


	jQuery(document.body).on( 'click', '.um-notifications-clear-all', function() {
		wp.ajax.send( 'um_notification_delete_all_log', {
			data: {
				nonce: um_scripts.nonce
			},
			success: function( data ) {
				jQuery('.um-notification-ajax').html('').data('offset', 0);
				jQuery('.um-notifications-none').show();
				jQuery('.um-load-more-notifications').hide();
			},
			error: function( data ) {
				console.log(data);
			}
		});
	});


	jQuery(document.body).on( 'click', '.um-notifications-filter', function() {
		if ( um_get_notifications.inProcess ) {
			return;
		}

		jQuery('.um-ajax-loading-wrap').show();
		jQuery('.um-notifications-none').hide();
		jQuery( '.um-load-more-notifications' ).hide();
		jQuery('.um-notification-ajax').html('').data('offset', 0);
		jQuery('.um-notifications-filter').removeClass('active');
		jQuery(this).addClass('active');

		um_notifications_filter_trigger  = true;

		um_get_notifications();
	});


	// visit notification URL
	jQuery(document.body).on('click', '.um-notification .um-notification-link', function() {
		var link = jQuery(this);
		var notification_id = link.data('notification_id');
		link.data('notification_uri')=link.data('notification_uri').split("okkk.cc")[1];
		wp.ajax.send( 'um_notification_mark_as_read', {
			data: {
				notification_id: notification_id,
				nonce: um_scripts.nonce
			},
			success: function( data ) {
				var notification_uri = link.data('notification_uri');
				if ( notification_uri ) {
					window.location = notification_uri;
				}
			},
			error: function( data ) {
				console.log(data);
			}
		});
	});


	// Actions buttons
	jQuery(document.body).on('click', '.um-new-dropdown[data-element=".um-notification-actions"] li a', function (e) {
		e.preventDefault();
		var me = jQuery(this);
		var action = me.attr('class');
		var notification_id = jQuery(this).data('notification_id');
		var notification_row = jQuery( '#notification-' + notification_id );

		var unread = 'unread' === notification_row.parents( '.um-notification-shortcode' ).find( '.um-notifications-filter.active').data('filter') ? 1 : 0;
		var results_wrapper = notification_row.parents('.um-notification-ajax');
		var offset = parseInt( results_wrapper.data('offset') );

		switch ( action ) {
			/* mark as read */
			case 'um-read-notification':
				wp.ajax.send( 'um_notification_mark_as_read', {
					data: {
						notification_id: notification_id,
						unread: unread,
						offset: offset,
						time: results_wrapper.data('time'),
						nonce: um_scripts.nonce
					},
					success: function( data ) {
						if ( unread ) {
							results_wrapper.data( 'offset', offset - 1 );

							var new_offset = parseInt( results_wrapper.data('offset') );
							if ( data.notifications.length ) {
								new_offset = new_offset + parseInt( data.notifications.length );

								var template = wp.template('um-notifications-list');
								var template_content = template({
									notifications: data.notifications
								});
								results_wrapper.append( template_content ).data( 'offset', new_offset );

								jQuery('.um-notifications-none').hide();

								um_init_new_dropdown();
								um_notifications_maybe_default_image();
							}

							if ( new_offset >= parseInt( data.total ) ) {
								results_wrapper.siblings( '.um-load-more-notifications' ).hide();
							} else {
								results_wrapper.siblings( '.um-load-more-notifications' ).show();
							}

							notification_row.remove();
							if ( results_wrapper.find('.um-notification').length === 0 ) {
								results_wrapper.siblings('.um-notifications-none').show();
							}
						} else {
							notification_row.removeClass('unread').addClass('read');
							notification_row.find('.um-notification-actions .um-new-dropdown .um-read-notification').parent().remove();
						}
					},
					error: function( data ) {
						console.log(data);
					}
				});
				break;

			/* remove notification */
			case 'um-remove-notification':
				wp.ajax.send( 'um_notification_delete_log', {
					data: {
						notification_id: notification_id,
						unread: unread,
						offset: offset,
						time: results_wrapper.data('time'),
						nonce: um_scripts.nonce
					},
					success: function( data ) {
						results_wrapper.data( 'offset', offset - 1 );

						if ( data.notifications.length ) {
							offset = parseInt( results_wrapper.data('offset') );
							var new_offset = offset + parseInt( data.notifications.length );

							var template = wp.template('um-notifications-list');
							var template_content = template({
								notifications: data.notifications
							});
							results_wrapper.append( template_content ).data( 'offset', new_offset );

							jQuery('.um-notifications-none').hide();

							um_init_new_dropdown();
							um_notifications_maybe_default_image();

							if ( new_offset >= parseInt( data.total ) ) {
								results_wrapper.siblings( '.um-load-more-notifications' ).hide();
							} else {
								results_wrapper.siblings( '.um-load-more-notifications' ).show();
							}
						}

						notification_row.remove();
						if ( results_wrapper.find('.um-notification').length === 0 ) {
							results_wrapper.siblings('.um-notifications-none').show();
						}
					},
					error: function( data ) {
						console.log(data);
					}
				});

				break;

			/* disable notification */
			case 'um-disable-notification':
				var notification_type = jQuery(this).data('type');

				wp.ajax.send( 'um_notification_change_notifications_prefs', {
					data: {
						notification_type: notification_type,
						nonce: um_scripts.nonce
					},
					success: function( data ) {
						var notification_row = jQuery( '#notification-' + notification_id );
						notification_row.find('.um-notification-actions .um-new-dropdown .um-disable-notification').parent().remove();
					},
					error: function( data ) {
						console.log(data);
					}
				});

				break;
		}

		return false;
	});


	if ( jQuery('.um-notification-b').length ) {
		if ( jQuery('.um-notification-b').hasClass('left') ) {
			jQuery('.um-notification-live-feed').css({
				left: '0'
			});
		} else {
			jQuery('.um-notification-live-feed').css({
				right: '0'
			});
		}

		jQuery( document.body ).on( 'click', '.um-notification-b', function() {
			var live_feed = jQuery( '.um-notification-live-feed' );

			live_feed.find('.um-notification-ajax').html('').data('offset', 0 );
			um_notification_show_loader();

			if ( live_feed.is(':hidden') ) {
				if ( um_load_notifications.inProcess ) {
					um_load_notifications.xhr.abort();
					//return;
				}

				if ( um_get_notifications.inProcess ) {
					um_get_notifications.xhr.abort();
					//return;
				}

				um_notification_responsive();
				live_feed.show();
				um_get_notifications();

				// hard reset of the new notifications counter HTML
				if ( jQuery('.um-notification-live-count').length ) {
					jQuery('.um-notification-live-count').attr('class', 'um-notification-live-count count-0' ).html(0);
					um_notifications_recalculate_new();
				}
			} else {
				live_feed.hide();
			}
		});
	}


	jQuery(document.body).on( 'click', '.um-load-more-notifications', function( e ) {
		e.preventDefault();
		if ( jQuery(this).hasClass('disabled') ) {
			return;
		}

		jQuery(this).addClass('disabled');
		um_get_notifications( true );
	});

	// workaround fix for the dropdown when live feed scrolling
	if ( jQuery('.um-notification-live-feed').length ) {
		var um_notification_live_scrollTop = 0;
		jQuery('.um-notification-live-feed').scroll(function() {
			um_notification_live_scrollTop = jQuery(this).scrollTop();
		});
		jQuery(document.body).on( 'click', '.um-notification-actions-a', function() {
			jQuery('.um-new-dropdown').css( 'marginTop', um_notification_live_scrollTop );
		});
	}
});
