<?php
/**
 * AJAX controller for the instant pre-upload endpoint.
 *
 * Single responsibility: turn an HTTP upload request into either a
 * stored token or an error response. Storage is injected via the
 * MIUWPF_Storage contract.
 *
 * @package MIUWPF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MIUWPF_Upload_Handler {

	/**
	 * AJAX action name.
	 */
	const ACTION = 'miuwpf_upload';

	/**
	 * Nonce action name.
	 */
	const NONCE = 'miuwpf_upload';

	/**
	 * Storage backend.
	 *
	 * @var MIUWPF_Storage
	 */
	private $storage;

	/**
	 * Constructor.
	 *
	 * @param MIUWPF_Storage $storage Storage backend.
	 */
	public function __construct( MIUWPF_Storage $storage ) {
		$this->storage = $storage;
	}

	/**
	 * Attach WordPress hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_' . self::ACTION, [ $this, 'handle' ] );
		add_action( 'wp_ajax_nopriv_' . self::ACTION, [ $this, 'handle' ] );
	}

	/**
	 * Handle the upload request.
	 *
	 * @return void
	 */
	public function handle() {

		check_ajax_referer( self::NONCE, 'nonce' );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- Validated below; $_FILES cannot be unslashed.
		$file = isset( $_FILES['file'] ) ? $_FILES['file'] : null;

		if (
			empty( $file )
			|| ! isset( $file['error'], $file['tmp_name'], $file['name'], $file['size'] )
			|| UPLOAD_ERR_OK !== (int) $file['error']
			|| ! is_uploaded_file( $file['tmp_name'] )
		) {
			$this->fail( __( 'Upload failed. Please try again.', 'multimage-image-uploads-for-wpforms' ), 400 );
		}

		if ( (int) $file['size'] > MIUWPF_Field_Config::HARD_MAX_SIZE ) {
			$this->fail( __( 'File is too large.', 'multimage-image-uploads-for-wpforms' ), 400 );
		}

		$name = sanitize_file_name( wp_unslash( $file['name'] ) );

		// Content-based type check, not extension trust.
		$check = wp_check_filetype_and_ext( $file['tmp_name'], $name );
		$ext   = strtolower( (string) $check['ext'] );

		if (
			! in_array( $ext, MIUWPF_Field_Config::HARD_EXTS, true )
			|| 0 !== strpos( (string) $check['type'], 'image/' )
			|| false === wp_getimagesize( $file['tmp_name'] )
		) {
			$this->fail( __( 'Only valid images are allowed.', 'multimage-image-uploads-for-wpforms' ), 400 );
		}

		$this->storage->cleanup_stale();

		$name  = '' !== $name ? $name : 'image.' . $ext;
		$token = MIUWPF_Token::generate( $name );

		if ( ! $this->storage->store_upload( $file['tmp_name'], $token ) ) {
			$this->fail( __( 'Could not store the file.', 'multimage-image-uploads-for-wpforms' ), 500 );
		}

		wp_send_json_success( [ 'token' => $token->value() ] );
	}

	/**
	 * Send a JSON error and stop.
	 *
	 * @param string $message Message.
	 * @param int    $code    HTTP status.
	 *
	 * @return void
	 */
	private function fail( $message, $code ) {
		wp_send_json_error( [ 'message' => esc_html( $message ) ], $code );
	}
}
