<?php
/**
 * Storage contract for pre-uploaded images.
 *
 * Consumers depend on this abstraction, not on a concrete
 * filesystem implementation (Dependency Inversion Principle).
 *
 * @package MIUWPF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface MIUWPF_Storage {

	/**
	 * Store an uploaded file under the given token.
	 *
	 * @param string       $tmp_name PHP upload tmp path.
	 * @param MIUWPF_Token $token    Token identifying the file.
	 *
	 * @return bool
	 */
	public function store_upload( $tmp_name, MIUWPF_Token $token );

	/**
	 * Whether a stored file exists for the token.
	 *
	 * @param MIUWPF_Token $token Token.
	 *
	 * @return bool
	 */
	public function exists( MIUWPF_Token $token );

	/**
	 * File size in bytes for the token, 0 when missing.
	 *
	 * @param MIUWPF_Token $token Token.
	 *
	 * @return int
	 */
	public function size( MIUWPF_Token $token );

	/**
	 * Move a stored file into the permanent uploads directory.
	 *
	 * @param MIUWPF_Token $token Token.
	 *
	 * @return array|null Array with 'url' and 'file' keys, or null on failure.
	 */
	public function promote( MIUWPF_Token $token );

	/**
	 * Delete stale stored files.
	 *
	 * @return void
	 */
	public function cleanup_stale();
}
