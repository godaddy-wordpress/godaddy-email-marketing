/* global tb_click */
/* jscs:disable requireCamelCaseOrUpperCaseIdentifiers */

/**
 * GoDaddy Email Marketing admin script.
 */
( function( $ ) {
	'use strict';

	var GEMAdmin = {

		cache: {},

		init: function() {
			this.cacheElements();
			this.bindEvents();
		},

		cacheElements: function() {
			this.cache = {
				$window: $( window ),
				$document: $( document )
			};
		},

		bindEvents: function() {
			var self = this;

			self.cache.$window.on( 'resize', $.proxy( self.tbPosition, self ) );

			self.cache.$document.on( 'ready', function() {
				self.tabbedNav();

				$( '.about-wrap' ).on( 'click', 'a.thickbox', function() {
					tb_click.call( this );
					self.cache.$window.trigger( 'resize' );
					return false;
				} );
			} );
		},

		tabbedNav: function() {
			var self = this,
				$wrap = $( '.about-wrap' );

			// Hide all panels
			$( 'div.panel', $wrap ).hide();

			this.cache.$window.on( 'load', function() {
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
		},

		getParameterByName: function( name ) {
			var regex, results;
			name = name.replace( /[\[]/, '\\[' ).replace( /[\]]/, '\\]' );
			regex = new RegExp( '[\\?&]' + name + '=([^&#]*)' );
			results = regex.exec( location.search );
			return null === results ? '' : decodeURIComponent( results[1].replace( /\+/g, ' ' ) );
		},

		tbPosition: function() {
			var $tbWindow = $( '#TB_window' ),
				$tbFrame = $( '#TB_iframeContent' ),
				windowWidth = this.cache.$window.width(),
				newHeight = this.cache.$window.height() - ( ( 792 < windowWidth ) ? 90 : 50 ),
				newWidth = ( 792 < windowWidth ) ? 772 : windowWidth - 20;

			if ( $tbWindow.size() ) {
				$tbWindow
					.width( newWidth )
					.height( newHeight )
					.css( { 'margin-left': '-' + parseInt( ( newWidth / 2 ), 10 ) + 'px' } );

				$tbFrame.width( newWidth ).height( newHeight );

				if ( 'undefined' !== typeof document.body.style.maxWidth ) {
					$tbWindow.css( {
						'top': ( 792 < windowWidth ? 30 : 10 ) + 'px',
						'margin-top': '0'
					} );
				}
			}

			return $( 'a.thickbox' ).each( function() {
				var href = $( this ).attr( 'href' );

				if ( ! href ) {
					return;
				}

				href = href.replace( /&width=[0-9]+/g, '' );
				href = href.replace( /&height=[0-9]+/g, '' );
				href = href + '&width=' + newWidth + '&height=' + newHeight;

				$( this ).attr( 'href', href );
			} );
		}

	};

	GEMAdmin.init();

} )( jQuery );
