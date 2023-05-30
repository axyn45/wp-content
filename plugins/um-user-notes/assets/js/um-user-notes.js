jQuery( document ).ready( function($) {

	$.fn.um_serializeFiles = function() {
		var form = $(this),
			formData = new FormData(),
			formParams = form.serializeArray();

		$.each(form.find('input[type="file"]'), function(i, tag) {
			$.each($(tag)[0].files, function(i, file) {
				formData.append(tag.name, file);
			});
		});

		$.each(formParams, function(i, val) {
			formData.append(val.name, val.value);
		});

		return formData;
	};


	// Show image when file is selected
	$(document.body).on('change','#um_notes_image_control',function(e){

		var target_element = $(this).parents('.um_notes_image_label');
		var reader = new FileReader();
		reader.onload = function(e) {
			var bg = e.target.result;
			target_element.css('background-image', 'url("' + bg + '")');
			target_element.css('background-size', 'cover');
		};

		if ( this.files.length ) {
			reader.readAsDataURL(this.files[0]);
			$('#um_notes_clear_image').css('display','block');
			target_element.find('.um_notes_image_label_text').html( target_element.find('> span').data('edit_photo') );
		}

	});


	// Process form on Add Note button click
	$(document.body).on('click','#um_notes_add_btn',function(e) {
		e.preventDefault();
		var btn = $(this);

		if ( btn.hasClass( 'busy' ) ) {
			return;
		}

		tinyMCE.triggerSave();

		var btn_init = btn.html();
		var form = $('form#um-user-notes-add');
		btn.html('<i class="um-user-notes-ajax-loading"></i>').addClass('busy');


		wp.ajax.send({
			data: form.um_serializeFiles(),
			cache:false,
			contentType: false,
			processData: false,
			success: function( data ) {
				btn.html( btn_init ).removeClass('busy');

				form.trigger('reset');
				$('label.um_notes_image_label').css('background-image','none');
				$('#um_notes_clear_image').hide();

				var response_wrapper = form.find('.form-response');

				response_wrapper.html('<p style="background:green;padding:10px;color:#fff;">' + data.display + '</p>');
				response_wrapper.find('.um_note_read_more').trigger('click');
			},
			error: function( e ) {
				console.log( e );
				btn.html( btn_init ).removeClass( 'busy');
				form.find('.form-response').html('<div style="background:red;padding:10px;color:#fff;">' + e + '</div><br/>');
			}
		});
	});

	$(document.body).on('click','#um_notes_back_btn',function(e) {
		e.preventDefault();
		var btn = $(this);

		if ( btn.hasClass('busy') ) {
			return;
		}

		btn.addClass('busy').html('<i class="um-user-notes-ajax-loading"></i>');

		var body = $('body');
		var modal = body.find('.um-notes-modal');
		var modal_content = modal.find('.um_notes_modal_content');
		modal_content.html( '<h1 style="margin-top:100px;text-align:center"><i class="um-user-notes-ajax-loading"></i></h1>' );
		modal.css( 'display', 'block' );

		body.addClass( 'um_notes_overlay' );

		wp.ajax.send( 'um_notes_view', {
			data: {
				note: btn.attr( 'data-id' )
				// _nonce:nonce
			},
			success: function( data ) {
				modal_content.html( data );
			},
			error: function( e ) {
				console.log( e );
			}
		});
	});

	// Process form on Update Note button click
	$(document.body).on('click','#um_notes_update_btn',function(e) {
		e.preventDefault();

		var btn = $(this);
		if ( btn.hasClass('busy') ) {
			return;
		}

		tinyMCE.triggerSave();

		btn.addClass('busy').html('<i class="um-user-notes-ajax-loading"></i>');

		var form = $('form#um-user-notes-edit');

		wp.ajax.send( 'um_notes_update', {
			data: form.um_serializeFiles(),
			cache:false,
			contentType: false,
			processData: false,
			success: function( data ) {
				form.find('.form-response').html('<p style="background:green;padding:10px;color:#fff;">' + wp.i18n.__( 'Note successfully updated.', 'um-user-notes' ) + '</p>');

				setTimeout(function(){
					$('#um_notes_back_btn').trigger('click');
				},2000);

				$('.um-notes-modal').addClass('updated');
			},
			error: function( e ) {
				console.log( e );
				form.find('.form-response').html('<p style="background:red;padding:10px;color:#fff;">' + e + '</p>');
			}
		});
	});


	// Open modal with note details
	$(document.body).on('click','.um_note_read_more',function(e){
		e.preventDefault();
		var btn = $(this);
		var body = $('body');
		var modal = body.find('.um-notes-modal');
		var modal_content = modal.find('.um_notes_modal_content');
		modal_content.html( '<h1 style="margin-top:100px;text-align:center"><i class="um-user-notes-ajax-loading"></i></h1>' );
		modal.css( 'display', 'block' );

		body.addClass( 'um_notes_overlay' );

		wp.ajax.send( 'um_notes_view', {
			data: {
				note: btn.attr( 'data-id' )
				// _nonce:nonce
			},
			success: function( data ) {
				modal_content.html( data );
			},
			error: function( e ) {
				modal_content.html( e );
			}
		});
	});


	// Close modal
	$(document.body).on('click','#um_notes_modal_close',function(e) {
		e.preventDefault();
		var btn = $(this);
		var modal = btn.parents('.um-notes-modal');
		var modal_content = modal.find('.um_notes_modal_content');

		if ( modal.hasClass('updated') ) {
			location.reload();
		} else {
			var url = btn.data('close');
			if ( url !== '' && window.location.href !== url ) {
				document.location.href = url;
			} else {
				modal_content.html('');
				modal.css( 'display', 'none' );
				wp.editor.remove( 'note_content' );
				$('body').removeClass( 'um_notes_overlay' );
			}
		}
	} ).on( 'click', 'div.um-notes-modal', function (e) {
		var $modal = jQuery( e.target );
		if ( $modal.is( 'div.um-notes-modal' ) ) {
			$modal.find( '#um_notes_modal_close' ).trigger( 'click' );
		}
	} );
	

	// Delete note
	$(document.body).on('click','.um_notes_delete_note',function(e) {
		e.preventDefault();
		var btn = $(this);
		var btn_init = btn.html();

		if ( btn.hasClass( 'busy' ) ) {
			return;
		}


		var id = btn.attr('data-id');
		var nonce = btn.attr('data-nonce');

		if ( confirm( wp.i18n.__( 'Want to delete note?', 'um-user-notes' ) ) ) {

			btn.addClass( 'busy' );
			btn.html('<i class="um-user-notes-ajax-loading"></i>');

			wp.ajax.send( 'um_notes_delete', {
				data: {
					post_id:id,
					_nonce:nonce
				},
				success: function( data ) {
					location.reload();
				},
				error: function( e ) {
					console.log( e );
				}
			});
		}
	});


	//Edit Note
	$(document.body).on('click','.um_notes_edit_note',function(e) {
		e.preventDefault();
		var btn = $(this);
		wp.editor.remove('note_content_edit');
		if ( btn.hasClass('busy') ) {
			return;
		}

		wp.editor.remove('note_content');

		btn.addClass('busy').html('<i class="um-user-notes-ajax-loading"></i>');

		var id = btn.attr('data-id');
		var nonce = btn.attr('data-nonce');
		var modal = $('body').find('.um-notes-modal');
		var modal_content = modal.find('.um_notes_modal_content');

		modal_content.html('<h1 style="margin-top:100px;text-align:center"><i class="um-user-notes-ajax-loading large"></i></h1>');

		wp.ajax.send( 'um_notes_edit', {
			data: {
				post_id:id,
				_nonce:nonce
			},
			success: function( data ) {
				modal_content.html( data );

				wp.editor.initialize(
					'note_content_edit',
					{
						tinymce: {
							wpautop : true,
							plugins : 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
							toolbar1: 'formatselect bold italic | bullist numlist | blockquote | alignleft aligncenter alignright | link unlink | wp_more | spellchecker | fullscreen | wp_adv',
							toolbar2:'strikethrough | hr | forecolor | pastetext | removeformat | charmap | outdent | indent | undo | redo | wp_help'
						},
						quicktags: true
					}
				);
			},
			error: function( e ) {
				console.log( e );
			}
		});
	});


	$(document.body).on('click','#um_notes_clear_image',function(e){
		e.preventDefault();
		var btn = $(this);
		var id = btn.attr('data-id');
		var mode = btn.attr('data-mode');
		var form = btn.parents('form');
		$('.um_notes_image_label').css('background-image','none');
		$('.um_notes_image_label .um_notes_image_label_text').html( $('.um_notes_image_label > span').data('add_photo') );
		$('#um_notes_image_control').val(null);
		form.find('[name="thumbnail_id"]').val('');
		btn.css('display','none');
	});


	$(document.body).on('click','#um-notes-load-more-btn',function() {
		var btn = $(this);

		var per_page = btn.data('per_page');
		var profile_id = btn.data('profile');
		var nonce = btn.data('nonce');
		var page = btn.data('page');

		var btn_init = btn.html();
		btn.html('<i class="um-user-notes-ajax-loading"></i>');

		wp.ajax.send( 'um_notes_load_more', {
			data: {
				per_page : per_page,
				offset : per_page*page,
				profile : profile_id,
				_nonce : nonce
			},
			success: function( data ) {
				if ( data.loadmore !== true ) {
					btn.hide();
				}

				if ( data.html !== 'empty' ) {
					$('body').find('.um-notes-holder').append( data.html );
					btn.data('page', parseInt( page ) + 1 ).html( btn_init );
				}
			},
			error: function( e ) {
				console.log( e );
			}
		});
	});

});