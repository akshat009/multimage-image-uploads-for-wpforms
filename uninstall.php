<?php
/**
 * Uninstall routine: remove the temporary upload directory.
 *
 * Final uploaded images in the standard uploads folders are kept,
 * since they belong to form submissions the site owner may need.
 *
 * @package MIUWPF
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$miuwpf_uploads = wp_upload_dir();

if ( empty( $miuwpf_uploads['error'] ) ) {

	$miuwpf_tmp = trailingslashit( $miuwpf_uploads['basedir'] ) . 'miuwpf-tmp';

	if ( is_dir( $miuwpf_tmp ) ) {

		$miuwpf_files = glob( $miuwpf_tmp . '/*' );

		if ( is_array( $miuwpf_files ) ) {
			foreach ( $miuwpf_files as $miuwpf_file ) {
				if ( is_file( $miuwpf_file ) ) {
					wp_delete_file( $miuwpf_file );
				}
			}
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- Removing our own empty temp dir.
		rmdir( $miuwpf_tmp );
	}
}
