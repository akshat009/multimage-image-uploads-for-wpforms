<?php
/**
 * Filesystem implementation of the storage contract.
 *
 * Owns everything about WHERE files live: the temp directory,
 * promotion into the uploads dir, and stale-file cleanup. No other
 * class builds file paths.
 *
 * @package MIUWPF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MIUWPF_Temp_Storage implements MIUWPF_Storage {

	/**
	 * Temp subfolder inside the uploads directory.
	 */
	const TMP_DIR = 'miuwpf-tmp';

	/**
	 * Stale-file threshold in seconds.
	 */
	const TTL = DAY_IN_SECONDS;

	/**
	 * Store an uploaded file under the given token.
	 *
	 * The caller (MIUWPF_Upload_Handler) has already verified the
	 * source path via is_uploaded_file() before this runs, so moving
	 * it through WP_Filesystem here does not weaken that check.
	 *
	 * @param string       $tmp_name PHP upload tmp path.
	 * @param MIUWPF_Token $token    Token identifying the file.
	 *
	 * @return bool
	 */
	public function store_upload( $tmp_name, MIUWPF_Token $token ) {

		$dir = $this->dir();

		if ( '' === $dir || ! $this->init_filesystem() ) {
			return false;
		}

		global $wp_filesystem;

		return (bool) $wp_filesystem->move( $tmp_name, $dir . '/' . $token->value(), true );
	}

	/**
	 * Whether a stored file exists for the token.
	 *
	 * @param MIUWPF_Token $token Token.
	 *
	 * @return bool
	 */
	public function exists( MIUWPF_Token $token ) {

		$path = $this->path( $token );

		return '' !== $path && is_file( $path );
	}

	/**
	 * File size in bytes for the token, 0 when missing.
	 *
	 * @param MIUWPF_Token $token Token.
	 *
	 * @return int
	 */
	public function size( MIUWPF_Token $token ) {

		return $this->exists( $token ) ? (int) filesize( $this->path( $token ) ) : 0;
	}

	/**
	 * Move a stored file into the permanent uploads directory.
	 *
	 * @param MIUWPF_Token $token Token.
	 *
	 * @return array|null Array with 'url' and 'file' keys, or null on failure.
	 */
	public function promote( MIUWPF_Token $token ) {

		$path = $this->path( $token );

		if ( '' === $path || ! is_file( $path ) || ! is_readable( $path ) ) {
			return null;
		}

		$contents = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local temp file.

		if ( false === $contents ) {
			return null;
		}

		$bits = wp_upload_bits( $token->original_filename(), null, $contents );

		wp_delete_file( $path );

		if ( ! empty( $bits['error'] ) ) {
			return null;
		}

		return [
			'url'  => $bits['url'],
			'file' => $bits['file'],
		];
	}

	/**
	 * Delete stored files older than the TTL.
	 *
	 * @return void
	 */
	public function cleanup_stale() {

		$dir = $this->dir();

		if ( '' === $dir ) {
			return;
		}

		$files = glob( $dir . '/*' );

		if ( ! is_array( $files ) ) {
			return;
		}

		foreach ( $files as $file ) {
			if (
				is_file( $file )
				&& 'index.html' !== basename( $file )
				&& ( time() - (int) filemtime( $file ) ) > self::TTL
			) {
				wp_delete_file( $file );
			}
		}
	}

	/**
	 * Absolute path for a token, empty string when the dir is unavailable.
	 *
	 * @param MIUWPF_Token $token Token.
	 *
	 * @return string
	 */
	private function path( MIUWPF_Token $token ) {

		$dir = $this->dir();

		return '' !== $dir ? $dir . '/' . $token->value() : '';
	}

	/**
	 * Initialize the global $wp_filesystem, direct-mode only — this
	 * runs on a frontend AJAX request, so there is no admin screen to
	 * fall back to for FTP/SSH credentials.
	 *
	 * @return bool
	 */
	private function init_filesystem() {

		global $wp_filesystem;

		if ( $wp_filesystem instanceof WP_Filesystem_Base ) {
			return true;
		}

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		return (bool) WP_Filesystem();
	}

	/**
	 * Temp dir path, created on demand. Empty string on failure.
	 *
	 * @return string
	 */
	private function dir() {

		$uploads = wp_upload_dir();

		if ( ! empty( $uploads['error'] ) ) {
			return '';
		}

		$dir = $uploads['basedir'] . '/' . self::TMP_DIR;

		if ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) {
			return '';
		}

		$this->maybe_write_index_stub( $dir );

		return $dir;
	}

	/**
	 * Best-effort directory-listing guard. Failure here (e.g.
	 * WP_Filesystem unavailable) should not block the temp dir from
	 * being usable, so it is not reflected in dir()'s return value.
	 *
	 * @param string $dir Temp dir path.
	 *
	 * @return void
	 */
	private function maybe_write_index_stub( $dir ) {

		$index = $dir . '/index.html';

		if ( file_exists( $index ) || ! $this->init_filesystem() ) {
			return;
		}

		global $wp_filesystem;

		if ( $wp_filesystem->is_writable( $dir ) ) {
			$wp_filesystem->put_contents( $index, '' );
		}
	}
}
