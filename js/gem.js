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

			//code that handles multiple selected checkboxes and saves them as comma separated (value1, value2)
			//which needed to happen before the payload gets serialized
			 	var combined_checkbox_string = '', 
			 		check_box_test = 0,
			 		current_name_of_input = '';
			 $( this ).find( ':input' ).each( function( i ) {
			 	var name_of_input = '',
			 	  	name_of_input = $(this).attr('name');
			 		
			 	if($(this).attr('type') == 'checkbox' && $(this).is(':checked')){

			 		if(current_name_of_input != name_of_input)
			 		{
			 			current_name_of_input = name_of_input;
			 			check_box_test = 0;
			 		} 

			 		if(check_box_test == 0)
			 		{
			 	 		combined_checkbox_string = $("input:checkbox[name='"+name_of_input+"']:checked").map(function() {return this.value;}).get().join(', ');
			 			check_box_test = 1;
			 		}
					
					$("input[name='"+name_of_input+"']").val(combined_checkbox_string);

			 	}
			 });
			//end of multiple checkbox select code

			//code that handles multiple selected dropdowns for the date and saves them as MM dd, yy (Oct 29, 15)
			//which needed to happen before the payload gets serialized
			 	var combined_date_string = '', 
			 		date_test = 0,
			 		current_name_of_date = '';

			 		var values = [];

			 $( this ).find("[fingerprint='date']" ).each( function( i ) {
			 	var name_of_input = '',
			 	  	name_of_input = $(this).attr('name');

			 		
			 	if($(this).attr('fingerprint') == 'date'){		 	  

			 		if(current_name_of_date != name_of_input)
			 		{
			 			current_name_of_date = name_of_input;
			 			values=[];
			 			combined_date_string='';
			 		}

			 		values.push($(this).val());
					
					if(values.length == 3)
					{
						//building the value with correct formatting...  MM dd, yy (Oct 29, 15)
						combined_date_string = values[0] + ' ' + values[1] + ', ' + values[2];
					}

					$("input[name='"+name_of_input+"']").val(combined_date_string);
			 	}
			 });
			//end of multiple date dropdown select code

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
