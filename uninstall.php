<?php
/**
 * Uninstall routine
 *
 * @package GEM
 */

// @codeCoverageIgnoreStart

// If uninstall not called from WordPress exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

// Delete all options that contain gem.
delete_option( 'gem-valid-creds' );
delete_option( 'gem-version' );
delete_option( GEM_Settings::SLUG );

// @codeCoverageIgnoreEnd
