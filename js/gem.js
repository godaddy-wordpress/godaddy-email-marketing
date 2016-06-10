/**
 * GoDaddy Email Marketing script.
 */
( function( $ ) {
	'use strict';

	var GEM = window.GEM || {};

	/**
	 * Constants
	 */
	GEM.DEBUG_FLAG = true;

	/**
	 * Initialization
	 */
	GEM.init = function() {

		// Handles form submissions
		$( 'form.gem-form' ).submit( function( e ) {
			var $wrapper = $( this ),
			    $spinner = $( '.gem-spinner', $wrapper ),
			    $message = $wrapper.find( '.gem-error, .gem-info' ),
			    payload,
			    invalidElements = [],
			    checkboxGroup,
			    checkboxString,
			    dateGroup,
			    dateString,
			    dateValues = [];

			e.preventDefault();

			/**
			 * Handles multiple selected checkboxes and saves them as comma separated (value1, value2)
			 * which needs to happen before the payload gets serialized.
			 */
			$( this ).find( 'input[type=checkbox]' ).each( function() {
				var checkbox = $( this ),
				    name = checkbox.data( 'name' );

				if ( checkboxGroup !== name ) {
					checkboxGroup = name;

					checkboxString = $( 'input:checkbox[data-name="' + name + '"]:checked' ).map( function() {
						return this.value;
					} ).get().join( ', ' );

					$( 'input[name="' + name + '"]' ).val( checkboxString );
				}
			} );

			/**
			 * Handles multiple selected dropdowns for the date and saves them as F d, Y (October 29, 2015)
			 * which needs to happen before the payload gets serialized.
			 */
			$( this ).find( '[fingerprint=date]' ).each( function() {
				var date = $( this ),
				    name = date.data( 'name' );

				if ( dateGroup !== name ) {
					dateGroup = name;

					// Loop over the date values.
					$( 'select[data-name="' + name + '"]' ).each( function() {
						var value = $( this ).val();
						if ( value ) {
							dateValues.push( value );
						}
					} );

					if ( 3 === dateValues.length ) {

						// Build the date string.
						dateString = dateValues[0] + ' ' + dateValues[1] + ', ' + dateValues[2];

						// Set the date value.
						$( 'input[name="' + name + '"]' ).val( dateString );
					}
				}
			} );

			// Serialize the payload.
			payload = $( this ).serialize();

			// Clear all "invalid" elements before re-validating.
			$wrapper.find( 'input.gem-invalid' ).removeClass( 'gem-invalid' );

			// Validate inputs.
			$( this ).find( '.gem-required' ).each( function() {
				var value = $( this ).val();
				if ( 'signup[email]' === $( this ).attr( 'name' ) && ! GEM.isEmail( value ) ) {

					// Invalid email.
					invalidElements.push( $( this ) );
				} else if ( '' === value && $( this ).is( 'input' ) ) {

					// Empty required field.
					invalidElements.push( $( this ) );
				} else if ( $( this ).is( 'label' ) ) {

					// Empty radio.
					if ( 'undefined' === typeof $( 'input:radio[name="' + $( this ).data( 'name' ) + '"]:checked' ).val() ) {
						invalidElements.push( $( this ) );
					}
				}
			} );

			// If there are no empty or invalid fields left.
			if ( 0 === invalidElements.length ) {

				// We're good to go! start spinnin'
				$spinner.css( 'display', 'inline-block' );

				$.post( $wrapper.attr( 'action' ) + '.json', payload, function( response ) {
					$wrapper.fadeOut( 'fast', function() {
						var isSuppressed;

						// Was the user successfully added?
						if ( response.success ) {
							isSuppressed = response.result.audience_member.suppressed;

							if ( response.result.has_redirect ) {
								window.location.href = response.result.redirect;
							}

							$wrapper.html( GEM.addMessage(
								isSuppressed ? [ 'suppressed', 'success' ] : [ 'info', 'success' ],
								isSuppressed ? GEM.thankyou_suppressed : GEM.thankyou )
							).fadeIn( 'fast' );
						} else {
							$wrapper.html( GEM.addMessage( 'info', GEM.oops ) ).fadeIn( 'fast' );
						}
					} );
				}, 'jsonp' );
			} else {
				if ( 0 !== $message.length ) {
					$message.remove();
				}

				// Invalid elements
				$( invalidElements.reverse() ).each( function() {
					var error = '';

					$( this ).addClass( 'gem-invalid' );

					if ( 'signup[email]' === $( this ).attr( 'name' ) ) {
						if ( '' === $( this ).val() ) {

							// Empty email.
							error = GEM.required.replace( '%s', $( this ).data( 'label' ) );
						} else {

							// Invalid email.
							error = GEM.email;
						}
					} else if ( $( this ).data( 'label' ) ) {

						// Empty input.
						error = GEM.required.replace( '%s', $( this ).data( 'label' ) );
					}

					if ( error ) {
						$wrapper.prepend( GEM.addMessage( 'error', error ) );
					}
				} );
			}
		} );
	};

	/**
	 * Adds error messages
	 */
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
	};

	/**
	 * Email validation
	 */
	GEM.isEmail = function( email ) {
		var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		return regex.test( email );
	};

	/**
	 * Constructor
	 */
	$( document ).ready( GEM.init );

} )( jQuery );
