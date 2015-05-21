<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Mad_Mimi_AJAX {

	public static function register() {

		$actions = array(
			'mimi-submit-form' => 'submit_form',
		);

		foreach ( $actions as $handle => $callback ) {
			add_action( 'wp_ajax_' . $handle,     array( __CLASS__, $callback ) );
			add_action( 'wp_ajax_nopriv_$handle', array( __CLASS__, $callback ) );
		}
	}

	public static function submit_form() {

		$form_data = $_POST;

		// nonce?
		if ( ! empty( $form_data ) && isset( $form_data['form_id'] ) ) {

			$response = wp_remote_post( sprintf( 'https://madmimi.com/signups/subscribe/%d.json', $form_data['form_id'] ), array(
				'timeout' => 15,
				'body' => $form_data,
			) );

			if ( is_wp_error( $response ) || ! isset( $response['body'] ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
				wp_send_json_error();
			}

			wp_send_json_success( json_decode( wp_remote_retrieve_body( $response ) ) );

		}

		die;

	}
}