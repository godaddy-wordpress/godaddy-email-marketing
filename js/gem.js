;( function( $, undefined ) {

	"use strict";

	var GEM = window.GEM || {};

	/**
	 * Constants
	 */
	GEM.DEBUG_FLAG = true;

	GEM.init = function() {

		// Handles form submissions
		$( 'form.gem-form' ).submit( function( e ) {

			e.preventDefault();

			var $wrapper = $( this ),
				$spinner = $( '.gem-spinner', $wrapper ),

				/* needed only when using WP as a proxy.
				payload = $.extend( {}, $( this ).gemSerializeObject(), {
					action: 'gem-submit-form'
				} ),
				*/

				payload = $( this ).serialize(),
				invalidElements = [],
				m = GEM;

			// make sure to clear all "invalid" elements before re-validating
			$wrapper.find( 'input.gem-invalid' ).removeClass( 'gem-invalid' );

			$( this ).find( ':input' ).each( function( i ) {

			 	if ( 'signup[email]' == $( this ).attr( 'name' ) && ! GEM.isEmail( $( this ).val() ) ) {

					// email not valid
					invalidElements.push( $( this ) );
					m.log( 'Email is NOT valid' );

				} else if ( $( this ).is( '.gem-required' ) && '' == $( this ).val() ) {
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

							var d             = response.result,
								is_suppressed = d.audience_member.suppressed;

							if ( d.has_redirect ) {
								window.location.href = d.redirect;
							}

							$wrapper.html( GEM.addMessage(
								is_suppressed ? [ 'suppressed', 'success' ] : [ 'info', 'success' ],
								is_suppressed ? GEM.thankyou_suppressed : GEM.thankyou )
							).fadeIn( 'fast' );

						} else {
							$wrapper.html( GEM.addMessage( 'info', GEM.oops ) ).fadeIn( 'fast' );
						}

					} );

				}, 'jsonp' );

			} else {

				// there are invalid elements
				$( invalidElements ).each( function( i, el ) {
					$( this ).addClass( 'gem-invalid' );
				} );

				var previousNotifications = $wrapper.find( '.gem-error, .gem-info' );

				if ( 0 != previousNotifications.length ) {
					previousNotifications.remove();
				}

				$wrapper.prepend( GEM.addMessage( 'error', GEM.fix ) );

			}

		} );
	};

	GEM.addMessage = function( type, message ) {

		var _class = [];

		if ( $.isArray( type ) ) {

			$.each( type, function( index, value ) {
				_class.push( 'gem-' + value );
			} );

		} else {
			_class.push( 'gem-' + type.toString() );
		}

		return $( '<p/>', { class: _class.join( ' ' ) } ).text( message );

	}

	GEM.isEmail = function ( email ) {
		var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		return regex.test( email );
	}

	GEM.log = function( message ) {

		if ( GEM.DEBUG_FLAG && window.console ) {
			console.log( message );
		}

	}

	/**
	 * ==== Helpers + Utilities ====
	 */
	$.fn.gemSerializeObject = function() {

		var o = {};
		var a = this.serializeArray();

		$.each( a, function() {

		   if ( o[ this.name ] ) {

			   if ( ! o[ this.name ].push ) {
				   o[ this.name ] = [ o[ this.name ] ];
			   }

			   o[ this.name ].push( this.value || '' );

		   } else {
			   o[ this.name ] = this.value || '';
		   }
		} );

		return o;

	};

	/**
	 * Constructor
	 */
	$( document ).ready( GEM.init );

} )( jQuery );
