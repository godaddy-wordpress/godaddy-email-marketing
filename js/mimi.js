;( function( $, undefined ) {
	"use strict";

	var MadMimi = window.MadMimi || {};

	MadMimi.init = function() {

		$( '.mimi-form' ).submit( function() {

			var payload = {
				action : 'mimi-submit-form',

			};

			/*
			$.post( $( this ).attr( 'action' ) + '.json', $( this ).serialize(), function( response ) {
				console.log( response );
			}, 'jsonp' );
*/
			

			$.getJSON( $( this ).attr( 'action' ) + '.json', $( this ).serialize(), function(data, textStatus, jqXHR) {

				alert('yooo');

			} );
			
			$.ajax( {
				url: $( this ).attr( 'action' ) + '.json',
				type: 'POST',
				dataType: 'jsonp',
				success:function( json ){
			         alert("Success");
			     }
			} );

			return false;

		} );
	};


	$( function() {
		$( MadMimi.init );
	} );

} )( jQuery );