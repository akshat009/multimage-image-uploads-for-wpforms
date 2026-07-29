<?php
/**
 * Minimal stand-in for $wp_filesystem, mirroring the relevant parts
 * of WP_Filesystem_Direct::move() (rename, falling back to copy +
 * delete) so tests exercise real filesystem behaviour.
 *
 * @package MIUWPF
 */

final class MIUWPF_Tests_Fake_Wp_Filesystem {

	public function move( $source, $destination, $overwrite = false ) {

		if ( ! $overwrite && file_exists( $destination ) ) {
			return false;
		}

		if ( @rename( $source, $destination ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors -- Test double.
			return true;
		}

		if ( @copy( $source, $destination ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors -- Test double.
			unlink( $source );

			return true;
		}

		return false;
	}

	public function is_writable( $path ) {
		return is_writable( $path );
	}

	public function put_contents( $file, $contents, $mode = false ) {
		return false !== file_put_contents( $file, $contents );
	}
}
