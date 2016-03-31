<?php

// if uninstall not called from WordPress exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

// delete all options and transients that contain gem
delete_option( 'gem-version' );
delete_option( 'gem-settings' );
