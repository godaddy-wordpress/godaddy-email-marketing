/* global alert */

/**
 * GoDaddy Email Marketing admin script.
 */
( function( $ ) {
	'use strict';

	var GEMAdmin = window.GEMAdmin || {};

	/**
	 * Initialization.
	 */
	GEMAdmin.init = function() {
		GEMAdmin.tabbedNav();
		GEMAdmin.copyToClipboard();
	};

	/**
	 * Generate the tabbed navigation.
	 */
	GEMAdmin.tabbedNav = function() {
		var self = this,
			$wrap = $( '.about-wrap' );

		// Hide all panels
		$( 'div.panel', $wrap ).hide();

		$( window ).on( 'load', function() {
			var tab = self.getParameterByName( 'tab' ),
				hashTab = window.location.hash.substr( 1 );

			// Move the notices.
			$( 'div.updated, div.error, div.notice' ).not( '.gem-identity' ).appendTo( '#setting-errors' );

			if ( tab ) {
				$( '.nav-tab-wrapper a[href="#' + tab + '"]', $wrap ).click();
			} else if ( hashTab ) {
				$( '.nav-tab-wrapper a[href="#' + hashTab + '"]', $wrap ).click();
			} else {
				$( 'div.panel:not(.hidden)', $wrap ).first().show();
			}
		} );

		// Listen for the click event.
		$( '.nav-tab-wrapper a', $wrap ).on( 'click', function() {

			// Deactivate and hide all tabs & panels.
			$( '.nav-tab-wrapper a', $wrap ).removeClass( 'nav-tab-active' );
			$( 'div.panel', $wrap ).hide();

			// Activate and show the selected tab and panel.
			$( this ).addClass( 'nav-tab-active' );
			$( 'div' + $( this ).attr( 'href' ), $wrap ).show();

			return false;
		} );
	};

	/**
	 * Return a URI parameter value by name.
	 */
	GEMAdmin.getParameterByName = function( name ) {
		var regex, results;
		name = name.replace( /[\[]/, '\\[' ).replace( /[\]]/, '\\]' );
		regex = new RegExp( '[\\?&]' + name + '=([^&#]*)' );
		results = regex.exec( location.search );
		return null === results ? '' : decodeURIComponent( results[1].replace( /\+/g, ' ' ) );
	};

	/**
	 * Copy to clipboard.
	 */
	GEMAdmin.copyToClipboard = function() {
		$( '.clipboard-value' ).on( 'click', function() {
			this.select();
		} );

		$( '.copy-to-clipboard' ).on( 'click', function( event ) {
			var input = $( $( this ).data( 'copytarget' ) );

			event.preventDefault();

			// Is element selectable?
			if ( input && input.select ) {

				// Select text.
				input.click();

				try {
					document.execCommand( 'copy' );
					input.addClass( 'copied' );

					// Remove selection and focus.
					setTimeout( function() {
						input.blur().removeClass( 'copied' );
						if ( document.selection ) {
							document.selection.empty();
						} else if ( window.getSelection ) {
							window.getSelection().removeAllRanges();
						}
					}, 1500 );
				} catch ( error ) {
					alert( GEMAdmin.copyFailed );
				}
			}
		} );
	};

	/**
	 * Constructor
	 */
	$( document ).ready( GEMAdmin.init );

} )( jQuery );
