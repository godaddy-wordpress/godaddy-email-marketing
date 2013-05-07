<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Mad_Mimi_AJAX {

	public function register_actions() {
		$actions = array(
			'mimi-submit-form' => 'submit_form',
		);

		foreach ( $actions as $handle => $callback ) {
			add_action( "wp_ajax_$handle", 			array( __CLASS__, $callback ) );
			add_action( "wp_ajax_nopriv_$handle", 	array( __CLASS__, $callback ) );
		}
	}

	public function submit_form() {
		
	}
}