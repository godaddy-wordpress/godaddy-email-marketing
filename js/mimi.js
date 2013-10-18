/* mimi.js */
;( function( $, undefined ) {
	"use strict";

	var MadMimi = window.MadMimi || {};

	/**
	 * Constants
	 */
	MadMimi.DEBUG_FLAG = true;

	MadMimi.init = function() {

		// Handles form submissions
		$( 'form.mimi-form' ).submit( function( e ) {
			e.preventDefault();
			
			var $wrapper = $( this ),
				$spinner = $( '.mimi-spinner', $wrapper ),
				/* needed only when using WP as a proxy.
				payload = $.extend( {}, $( this ).mimiSerializeObject(), {
					action: 'mimi-submit-form'
				} ),
				*/
				payload = $( this ).serialize(),
				invalidElements = [],
				m = MadMimi;

			// make sure to clear all "invalid" elements before re-validating
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
				// we're good to go! start spinnin'
				$spinner.css( 'display', 'inline-block' );

				$.post( $wrapper.attr( 'action' ) + '.json', payload, function( response ) {
					
					$wrapper.fadeOut( 'fast', function() {

						// was the user successfully added?
						if ( response.success ) {

							var d = response.result,
								is_suppressed = d.audience_member.suppressed;

							if(d.has_redirect) window.location.href = d.redirect;

							$wrapper.html( MadMimi.addMessage( 
								is_suppressed ? [ 'suppressed', 'success' ] : [ 'info', 'success' ], 
								is_suppressed ? MadMimi.thankyou_suppressed : MadMimi.thankyou ) 
							).fadeIn( 'fast' );

						} else {
							$wrapper.html( MadMimi.addMessage( 'info', MadMimi.oops ) ).fadeIn( 'fast' );
						}

					} );

				}, 'jsonp' );

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
		var _class = [];

		if ( $.isArray( type ) ) {
			$.each( type, function( index, value ) {
				_class.push( 'mimi-' + value );
			} );
		} else {
			_class.push( 'mimi-' + type.toString() );
		}

		return $( '<p/>', { class: _class.join( ' ' ) } ).text( message );
	}

	MadMimi.isEmail = function ( email ) {
		var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		return regex.test( email );
	}

	MadMimi.log = function( message ) {
		if ( MadMimi.DEBUG_FLAG && window.console )
			console.log( message );
	}

	/**
	 * ==== Helpers + Utilities ====
	 */
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

	/**
	 * Constructor
	 */
	$( document ).ready( MadMimi.init );
} )( jQuery );