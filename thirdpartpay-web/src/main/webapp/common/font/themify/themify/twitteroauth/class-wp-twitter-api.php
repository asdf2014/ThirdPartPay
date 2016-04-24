<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class WordPress Twitter API
 * @version 1.0.0
 */
class Wp_Twitter_Api {

	var $bearer_token,

	// Default credentials
	$args = array(
		'consumer_key'			=>	'default_consumer_key',
		'consumer_secret'		=>	'default_consumer_secret'
	),

	// Default type of the resource
	$query_args = array(
		'type'				=>	'statuses/user_timeline'
	),

	$has_error = false,
	$error_message = '',

	$bearer_token_option = 'themify_bearer_token';

	/**
	 * WordPress Twitter API Constructor
	 *
	 * @param array $args
	 */
	public function __construct( $args = array() ) {

		if ( is_array( $args ) && !empty( $args ) )
			$this->args = array_merge( $this->args, $args );

		if ( ! $this->bearer_token = get_option( $this->bearer_token_option ) )
			$this->bearer_token = $this->get_bearer_token();

	}

	/**
	 * Get the token from oauth Twitter API
	 *
	 * @return string Oauth Token
	 */
	private function get_bearer_token() {

		$bearer_token_credentials = $this->args['consumer_key'] . ':' . $this->args['consumer_secret'];
		/**
		 * Encode token credentials since Twitter requires it that way.
		 * @since 2.0.2
		 */
		$encoding = '64' . '_' . 'encode';
		$bearer_token_credentials_64 = call_user_func( 'base' . $encoding, $bearer_token_credentials );

		$args = array(
			'method'		=> 	'POST',
			'timeout'		=> 	5,
			'redirection'	=> 	5,
			'httpversion'	=> 	'1.0',
			'blocking'		=> 	true,
			'headers'		=> 	array(
				'Authorization'		=>	'Basic ' . $bearer_token_credentials_64,
				'Content-Type'		=> 	'application/x-www-form-urlencoded;charset=UTF-8',
				'Accept-Encoding'	=>	'gzip'
			),
			'body' 			=>  array( 'grant_type'		=>	'client_credentials' ),
			'cookies' 		=> 	array(),
			'sslverify' 	=> false
		);

		$response = wp_remote_post( 'https://api.twitter.com/oauth2/token', $args );

		if ( is_wp_error( $response ) || 200 != $response['response']['code'] )
			return $this->bail( apply_filters( 'themify_twitter_missing_key_message', sprintf( __( 'Error: access keys missing in <a href="%s">Themify > Settings > Twitter Settings</a>', 'themify' ), admin_url( 'admin.php?page=themify#setting-twitter_settings' ) ) ), $response );

		$result = json_decode( $response['body'] );

		update_option( $this->bearer_token_option, $result->access_token );

		return $result->access_token;

	}

	/**
	 * Query twitter's API
	 *
	 * @uses $this->get_bearer_token() to retrieve token if not working
	 *
	 * @param string $query Insert the query in the format "count=1&include_entities=true&include_rts=true&screen_name=micc1983!
	 * @param array $query_args Array of arguments: Resource type (string)
	 * @param bool $stop Stop the query to avoid infinite loop
	 *
	 * @return bool|object Return an object containing the result
	 */
	public function query( $query, $query_args = array(), $stop = false ) {

		if ( $this->has_error )
			return array( 'error_message' => $this->error_message );

		if ( is_array( $query_args ) && !empty( $query_args ) )
			$this->query_args = array_merge( $this->query_args, $query_args );

		$args = array(
			'method'		=> 	'GET',
			'timeout'		=> 	5,
			'redirection'	=> 	5,
			'httpversion'	=> 	'1.0',
			'blocking'		=> 	true,
			'headers'		=> 	array(
				'Authorization'		=>	'Bearer ' . $this->bearer_token,
				'Accept-Encoding'	=>	'gzip'
			),
			'body' 			=> 	null,
			'cookies' 		=> 	array(),
			'sslverify' 	=> false
		);

		$response = wp_remote_get( 'https://api.twitter.com/1.1/' . $this->query_args['type'] . '.json?' . $query, $args );

		if ( is_wp_error( $response ) || 200 != $response['response']['code'] ){
			if ( !$stop ){
				$this->bearer_token = $this->get_bearer_token();
				return $this->query( $query, $this->query_args, true );
			} else {
				return $this->bail( __( 'Bearer Token is good, check your query', 'themify' ), $response );
			}
		}

		$data = json_decode( $response['body'] );
		
		return $data;

	}

	/**
	 * Let's manage errors
	 *
	 * WP_DEBUG has to be set to true to show errors
	 *
	 * @param string $error_text Error message
	 * @param string $error_object Server response or wp_error
	 */
	private function bail( $error_text, $error_object = '' ) {
		if ( ! is_user_logged_in() ) return;
		// Following code is for debugging purposes
		$this->has_error = true;

		if ( is_wp_error( $error_object ) ){
			$error_text .= ' - Wp Error: ' . $error_object->get_error_message();
		} elseif ( !empty( $error_object ) && isset( $error_object['response']['message'] ) ) {
			//$error_text .= ' ( Response: ' . $error_object['response']['message'] . ' )';
		}
		$this->error_message = $error_text;

		return $error_text;
		//trigger_error( $error_text , E_USER_NOTICE );

	}

}