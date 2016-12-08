/**
 * Dismiss the gem notice when the 'x' is clicked
 */
jQuery( document ).ready( function( $ ) {

	$( 'body' ).on( 'click', '.gem-notice .notice-dismiss', function() {

		$.post( gem_notice.ajaxurl, {
			'action': 'dismiss_gem_notice',
			'nonce' : gem_notice.ajax_nonce
		} );

	} );

} );
