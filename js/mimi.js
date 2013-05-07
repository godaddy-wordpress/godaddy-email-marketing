;( function( $, undefined ) {
	"use strict";

	var MadMimi = window.MadMimi || {};

	MadMimi.init = function() {

		$( '.mimi-form' ).submit( function( e ) {
			e.preventDefault();

			
			var wrapper = $( this ),
				payload = $.extend( {}, $( this ).mimiSerializeObject(), {
					action: 'mimi-submit-form'
				} );

			$( this ).fadeOut( 'fast', function() {


				$.post( MadMimi.ajaxurl, payload, function( response ) {

					if ( response.success && response.data.new_member ) {
						// user was successfully added
						wrapper.html( MadMimi.thankyou ).fadeIn( 'fast' );
					} else {
						wrapper.html( MadMimi.oops ).fadeIn( 'fast' );
					}

				}, 'json' );

			} );
			

			/*
			$.getJSON( $( this ).attr( 'action' ) + '.json', $( this ).serialize(), function(data, textStatus, jqXHR) {

				alert('yooo');

			} );
		*/
/*


			$.ajax( {
				url: $( this ).attr( 'action' ) + '.json',
				type: 'POST',
				crossDomain: true,
				//processData: false,
				dataType: "jsonp",
				data: $( this ).serialize(),
				contentType: "application/json",
				success:function( json ) {
					alert("Success");
				},
			} ).always(function( json ) {
				console.log(json);
			});
*/


		} );
	};


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