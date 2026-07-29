<?php
/**
 * Tests for MIUWPF_Temp_Storage.
 *
 * wp_upload_dir(), wp_mkdir_p(), wp_upload_bits(), wp_delete_file()
 * and WP_Filesystem() are mocked to operate against a real, disposable
 * temp directory (via MIUWPF_Tests_Fake_Wp_Filesystem for the move()
 * call) so these tests exercise genuine filesystem behaviour rather
 * than asserting on mock call counts.
 *
 * @package MIUWPF
 */

use Brain\Monkey\Functions;

final class TempStorageTest extends MIUWPF_TestCase {

	/** @var string */
	private $base_dir;

	/** @var string */
	private $uploads_dir;

	/** @var MIUWPF_Temp_Storage */
	private $storage;

	protected function set_up() {
		parent::set_up();

		$this->base_dir    = sys_get_temp_dir() . '/miuwpf_test_' . uniqid( '', true );
		$this->uploads_dir = $this->base_dir . '/uploads';
		mkdir( $this->uploads_dir, 0777, true );

		Functions\when( 'wp_upload_dir' )->justReturn(
			[
				'basedir' => $this->uploads_dir,
				'baseurl' => 'http://example.test/wp-content/uploads',
				'error'   => false,
			]
		);

		Functions\when( 'wp_mkdir_p' )->alias(
			function ( $target ) {
				return is_dir( $target ) || mkdir( $target, 0777, true );
			}
		);

		Functions\when( 'wp_delete_file' )->alias(
			function ( $file ) {
				if ( is_file( $file ) ) {
					unlink( $file );
				}
			}
		);

		Functions\when( 'WP_Filesystem' )->justReturn( true );

		global $wp_filesystem;
		$wp_filesystem = new MIUWPF_Tests_Fake_Wp_Filesystem();

		$this->storage = new MIUWPF_Temp_Storage();
	}

	protected function tear_down() {
		global $wp_filesystem;
		$wp_filesystem = null;

		$this->remove_dir( $this->base_dir );
		parent::tear_down();
	}

	private function remove_dir( $dir ) {

		if ( ! is_dir( $dir ) ) {
			return;
		}

		foreach ( scandir( $dir ) as $entry ) {
			if ( '.' === $entry || '..' === $entry ) {
				continue;
			}

			$path = $dir . '/' . $entry;

			is_dir( $path ) ? $this->remove_dir( $path ) : unlink( $path );
		}

		rmdir( $dir );
	}

	private function tmp_dir_path() {
		return $this->uploads_dir . '/' . MIUWPF_Temp_Storage::TMP_DIR;
	}

	private function token( $filename = 'photo.jpg' ) {
		return MIUWPF_Token::from_input( 'abcd1234abcd1234-' . $filename );
	}

	public function test_exists_creates_the_tmp_subdirectory_with_an_index_file() {

		$this->storage->exists( $this->token() );

		$this->assertDirectoryExists( $this->tmp_dir_path() );
		$this->assertFileExists( $this->tmp_dir_path() . '/index.html' );
	}

	public function test_exists_is_false_when_no_file_is_stored_for_the_token() {
		$this->assertFalse( $this->storage->exists( $this->token() ) );
	}

	public function test_exists_is_true_once_a_file_is_present_for_the_token() {

		$token = $this->token();
		mkdir( $this->tmp_dir_path(), 0777, true );
		file_put_contents( $this->tmp_dir_path() . '/' . $token->value(), 'stub-bytes' );

		$this->assertTrue( $this->storage->exists( $token ) );
	}

	public function test_size_is_zero_when_the_token_has_no_stored_file() {
		$this->assertSame( 0, $this->storage->size( $this->token() ) );
	}

	public function test_size_reflects_the_actual_stored_byte_count() {

		$token   = $this->token();
		$content = str_repeat( 'x', 12345 );
		mkdir( $this->tmp_dir_path(), 0777, true );
		file_put_contents( $this->tmp_dir_path() . '/' . $token->value(), $content );

		$this->assertSame( 12345, $this->storage->size( $token ) );
	}

	public function test_store_upload_returns_false_when_the_uploads_dir_errors() {

		Functions\when( 'wp_upload_dir' )->justReturn( [ 'error' => 'disk unavailable' ] );

		$source = $this->base_dir . '/source.jpg';
		file_put_contents( $source, 'bytes' );

		$this->assertFalse( $this->storage->store_upload( $source, $this->token() ) );
	}

	/**
	 * MIUWPF_Upload_Handler already verifies is_uploaded_file() before
	 * calling store_upload(), so this method's own job is just "move
	 * the given path into the tmp dir" — which, via WP_Filesystem, is
	 * finally testable end to end (the old move_uploaded_file()-based
	 * implementation could only ever fail in a CLI test).
	 */
	public function test_store_upload_moves_the_source_file_into_the_tmp_dir() {

		$token  = $this->token( 'photo.jpg' );
		$source = $this->base_dir . '/source.jpg';
		file_put_contents( $source, 'image-bytes' );

		$this->assertTrue( $this->storage->store_upload( $source, $token ) );
		$this->assertFileExists( $this->tmp_dir_path() . '/' . $token->value() );
		$this->assertFileDoesNotExist( $source, 'the source file should be moved, not copied' );
		$this->assertSame( 'image-bytes', file_get_contents( $this->tmp_dir_path() . '/' . $token->value() ) );
	}

	public function test_store_upload_returns_false_when_wp_filesystem_cannot_initialize() {

		Functions\when( 'WP_Filesystem' )->justReturn( false );

		$source = $this->base_dir . '/source.jpg';
		file_put_contents( $source, 'bytes' );

		$this->assertFalse( $this->storage->store_upload( $source, $this->token() ) );
	}

	public function test_promote_returns_null_when_no_file_is_stored_for_the_token() {
		$this->assertNull( $this->storage->promote( $this->token() ) );
	}

	public function test_promote_moves_the_file_and_deletes_the_temp_copy() {

		$token = $this->token( 'photo.jpg' );
		mkdir( $this->tmp_dir_path(), 0777, true );
		$tmp_path = $this->tmp_dir_path() . '/' . $token->value();
		file_put_contents( $tmp_path, 'image-bytes' );

		$final_path = $this->uploads_dir . '/photo.jpg';

		Functions\when( 'wp_upload_bits' )->alias(
			function ( $filename, $deprecated, $bits ) use ( $final_path ) {
				file_put_contents( $final_path, $bits );

				return [
					'file'  => $final_path,
					'url'   => 'http://example.test/wp-content/uploads/' . $filename,
					'error' => false,
				];
			}
		);

		$result = $this->storage->promote( $token );

		$this->assertSame( $final_path, $result['file'] );
		$this->assertSame( 'http://example.test/wp-content/uploads/photo.jpg', $result['url'] );
		$this->assertFileExists( $final_path );
		$this->assertFileDoesNotExist( $tmp_path );
	}

	public function test_promote_returns_null_and_still_cleans_up_when_wp_upload_bits_errors() {

		$token = $this->token();
		mkdir( $this->tmp_dir_path(), 0777, true );
		$tmp_path = $this->tmp_dir_path() . '/' . $token->value();
		file_put_contents( $tmp_path, 'image-bytes' );

		Functions\when( 'wp_upload_bits' )->justReturn( [ 'error' => 'destination folder is not writable' ] );

		$result = $this->storage->promote( $token );

		$this->assertNull( $result );
		$this->assertFileDoesNotExist( $tmp_path );
	}

	public function test_cleanup_stale_removes_only_files_older_than_the_ttl() {

		mkdir( $this->tmp_dir_path(), 0777, true );

		$old_file    = $this->tmp_dir_path() . '/old.tmp';
		$recent_file = $this->tmp_dir_path() . '/recent.tmp';
		$index_file  = $this->tmp_dir_path() . '/index.html';

		file_put_contents( $old_file, 'x' );
		file_put_contents( $recent_file, 'x' );
		file_put_contents( $index_file, '' );

		touch( $old_file, time() - MIUWPF_Temp_Storage::TTL - 10 );
		touch( $index_file, time() - MIUWPF_Temp_Storage::TTL - 10 );

		$this->storage->cleanup_stale();

		$this->assertFileDoesNotExist( $old_file );
		$this->assertFileExists( $recent_file );
		$this->assertFileExists( $index_file, 'index.html must never be cleaned up regardless of age' );
	}

	public function test_cleanup_stale_does_not_throw_when_the_uploads_dir_is_unavailable() {

		Functions\when( 'wp_upload_dir' )->justReturn( [ 'error' => 'disk unavailable' ] );

		$this->storage->cleanup_stale();

		$this->addToAssertionCount( 1 );
	}
}
