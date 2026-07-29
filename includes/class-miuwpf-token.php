<?php
/**
 * Immutable upload token value object.
 *
 * A token is "{16 random chars}-{sanitized original filename}".
 * All parsing and validation of raw token input lives here, so no
 * other class ever handles an unvalidated token string.
 *
 * @package MIUWPF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MIUWPF_Token {

	/**
	 * Validation pattern for a complete token.
	 */
	const PATTERN = '/^[a-z0-9]{16}-[^\/\\\\]+\.[a-z0-9]{2,5}$/i';

	/**
	 * Token string.
	 *
	 * @var string
	 */
	private $value;

	/**
	 * Private constructor — use the named factories.
	 *
	 * @param string $value Validated token string.
	 */
	private function __construct( $value ) {
		$this->value = $value;
	}

	/**
	 * Generate a fresh token for a filename.
	 *
	 * @param string $filename Sanitized original filename.
	 *
	 * @return self
	 */
	public static function generate( $filename ) {

		$prefix = strtolower( wp_generate_password( 16, false, false ) );

		return new self( $prefix . '-' . sanitize_file_name( $filename ) );
	}

	/**
	 * Build a token from untrusted request input.
	 *
	 * @param mixed $raw Raw submitted value.
	 *
	 * @return self|null Null when the input is not a valid token.
	 */
	public static function from_input( $raw ) {

		$candidate = basename( sanitize_text_field( wp_unslash( (string) $raw ) ) );

		if ( 1 !== preg_match( self::PATTERN, $candidate ) ) {
			return null;
		}

		return new self( $candidate );
	}

	/**
	 * The token string.
	 *
	 * @return string
	 */
	public function value() {
		return $this->value;
	}

	/**
	 * Original filename with the random prefix stripped.
	 *
	 * @return string
	 */
	public function original_filename() {
		return (string) preg_replace( '/^[a-z0-9]{16}-/', '', $this->value );
	}

	/**
	 * File extension, lowercased.
	 *
	 * @return string
	 */
	public function extension() {
		return strtolower( pathinfo( $this->value, PATHINFO_EXTENSION ) );
	}
}
