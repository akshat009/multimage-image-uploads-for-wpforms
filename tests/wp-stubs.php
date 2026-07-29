<?php
/**
 * Behavioural stubs for the small set of WordPress functions the
 * plugin's pure-logic classes call.
 *
 * These are real (simplified) implementations, not Brain Monkey
 * mocks, because the classes under test use them for actual string
 * processing (sanitizing, casting) rather than as seams we want to
 * vary per test. WP I/O functions (wp_upload_dir, wp_upload_bits,
 * add_action, etc.) are left for Brain Monkey to mock per test case,
 * since their behaviour needs to vary by scenario.
 *
 * @package MIUWPF
 */

if ( ! function_exists( 'absint' ) ) {
	function absint( $maybeint ) {
		return abs( (int) $maybeint );
	}
}

if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( $value ) {
		if ( is_array( $value ) ) {
			return array_map( 'wp_unslash', $value );
		}

		return is_string( $value ) ? stripslashes( $value ) : $value;
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $str ) {
		$filtered = (string) $str;
		$filtered = strip_tags( $filtered );
		$filtered = preg_replace( '/[\r\n\t ]+/', ' ', $filtered );

		return trim( $filtered );
	}
}

if ( ! function_exists( 'sanitize_file_name' ) ) {
	function sanitize_file_name( $filename ) {
		$special_chars = [ '?', '[', ']', '/', '\\', '=', '<', '>', ':', ';', ',', "'", '"', '&', '$', '#', '*', '(', ')', '|', '~', '`', '!', '{', '}', '%', '+', chr( 0 ) ];

		$filename = str_replace( $special_chars, '', (string) $filename );
		$filename = str_replace( [ '%20', '+' ], '-', $filename );
		$filename = preg_replace( '/[\r\n\t -]+/', '-', $filename );

		return trim( $filename, '.-_' );
	}
}

if ( ! function_exists( 'wp_generate_password' ) ) {
	function wp_generate_password( $length = 12, $special_chars = true, $extra_special_chars = false ) {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

		if ( $special_chars ) {
			$chars .= '!@#$%^&*()';
		}

		if ( $extra_special_chars ) {
			$chars .= '-_ []{}<>~`+=,.;:/?|';
		}

		$password = '';
		$max      = strlen( $chars ) - 1;

		for ( $i = 0; $i < $length; $i++ ) {
			$password .= $chars[ random_int( 0, $max ) ];
		}

		return $password;
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( $text, $domain = 'default' ) {
		return esc_html( $text );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_url_raw' ) ) {
	function esc_url_raw( $url ) {
		return trim( (string) $url );
	}
}

/**
 * Test double for WordPress's wp_send_json_success()/wp_send_json_error().
 *
 * The real functions echo JSON and call wp_die(), which halts the
 * request. Throwing lets tests assert on the payload without the
 * process actually terminating.
 */
class MIUWPF_Tests_Ajax_Response_Exception extends \Exception {

	/** @var bool */
	public $response_success;

	/** @var mixed */
	public $response_data;

	/** @var int|null */
	public $status_code;

	public function __construct( $success, $data, $status_code = null ) {
		$this->response_success = $success;
		$this->response_data    = $data;
		$this->status_code      = $status_code;

		parent::__construct( $success ? 'wp_send_json_success' : 'wp_send_json_error' );
	}
}

if ( ! function_exists( 'wp_send_json_success' ) ) {
	function wp_send_json_success( $data = null, $status_code = null ) {
		throw new MIUWPF_Tests_Ajax_Response_Exception( true, $data, $status_code );
	}
}

if ( ! function_exists( 'wp_send_json_error' ) ) {
	function wp_send_json_error( $data = null, $status_code = null ) {
		throw new MIUWPF_Tests_Ajax_Response_Exception( false, $data, $status_code );
	}
}

if ( ! function_exists( 'check_ajax_referer' ) ) {
	function check_ajax_referer( $action = -1, $query_arg = false, $stop = true ) {
		return 1;
	}
}
