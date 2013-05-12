/* mimi.js */
;( function( $, undefined ) {
	"use strict";

	var MadMimi = window.MadMimi || {};

	// Constants
	MadMimi.DEBUG_FLAG = true;

	MadMimi.init = function() {

		// Handles form submissions
		$( 'form.mimi-form' ).submit( function( e ) {
			e.preventDefault();
			
			var $wrapper = $( this ),
				payload = $.extend( {}, $( this ).mimiSerializeObject(), {
					action: 'mimi-submit-form'
				} ),
				invalidElements = [],
				m = MadMimi;

			// make sure to reset the invalid elements array before each check
			invalidElements = [];
			$wrapper.find( 'input.mimi-invalid' ).removeClass( 'mimi-invalid' );

			$( this ).find( ':input' ).each( function( i ) {
				if ( 
					'signup[email]' == $( this ).attr( 'name' ) 
					&& ! MadMimi.isEmail( $( this ).val() ) 
				) {
					// email not valid
					invalidElements.push( $( this ) );
					m.log( 'Email is NOT valid' );

				} else if ( $( this ).is( '.mimi-required' ) && '' == $( this ).val() ) {
					invalidElements.push( $( this ) );
					m.log( 'A required filled was not filled' );
				}
			} );

			// if there are no empty or invalid fields left...
			if ( 0 == invalidElements.length ) {
				$( this ).fadeOut( 'fast', function() {

					$.post( $wrapper.attr( 'action' ) + '.json', payload, function( response ) {

						// was the user successfully added?
						if ( response.success && response.result.new_member ) {
							$wrapper.html( MadMimi.addMessage( 'info', MadMimi.thankyou ) ).fadeIn( 'fast' );
						} else {
							$wrapper.html( MadMimi.addMessage( 'info', MadMimi.oops ) ).fadeIn( 'fast' );
						}

					}, 'jsonp' );
					
				} );
			} else {
				// there are invalid elements
				$( invalidElements ).each( function( i, el ) {
					$( this ).addClass( 'mimi-invalid' );
				} );

				var previousNotifications = $wrapper.find( '.mimi-error, .mimi-info' );

				if ( 0 != previousNotifications.length )
					previousNotifications.remove();

				$wrapper.prepend( MadMimi.addMessage( 'error', MadMimi.fix ) );
			}

		} );
	};

	MadMimi.addMessage = function( type, message ) {
		return '<p class="mimi-' + type + '"">' + message + '</p>';
	}

	MadMimi.isEmail = function ( email ) {
		var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		return regex.test( email );
	}

	MadMimi.log = function( message ) {
		if ( MadMimi.DEBUG_FLAG && window.console )
			console.log( message );
	}


	// constructor
	$( function() {
		$( MadMimi.init );
	} );

	// Helpers
	$.fn.mimiSerializeObject = function() {
		var o = {};
		var a = this.serializeArray();
		$.each(a, function() {
		   if (o[this.name]) {
			   if (!o[this.name].push) {
				   o[this.name] = [o[this.name]];
			   }
			   o[this.name].push(this.value || '');
		   } else {
			   o[this.name] = this.value || '';
		   }
		});
		return o;
	};

} )( jQuery );