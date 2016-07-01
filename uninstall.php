<?php
/**
 * Uninstall routine
 *
 * @package GEM
 */

// @codeCoverageIgnoreStart

// Exit if called directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

// Delete all options that start with `gem-`.
global $wpdb;
$wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE `option_name` LIKE 'gem-%';" );

// @codeCoverageIgnoreEnd
