<?php
/**
 * Tests for MIUWPF_Upload_Handler.
 *
 * handle()'s first gate calls PHP's built-in is_uploaded_file(),
 * which only ever returns true for a file PHP itself registered as
 * part of a genuine multipart/form-data POST. That can't be
 * fabricated from a CLI unit test (and can't be mocked either: the
 * function is called unqualified from the global namespace, so
 * there's no seam to intercept it). As a result everything past that
 * gate — the size cap, the content-type sniff, token generation, and
 * the success response — is out of reach here and belongs to manual
 * or integration testing. These tests cover every branch that is
 * reachable in-process: malformed input, a rejected PHP upload error
 * code, and the "not a genuine upload" branch (which, conveniently,
 * is exactly what a CLI process naturally hits).
 *
 * @package MIUWPF
 */

use Brain\Monkey\Actions;

final class UploadHandlerTest extends MIUWPF_TestCase {

	/** @var MIUWPF_Upload_Handler */
	private $handler;

	protected function set_up() {
		parent::set_up();

		$this->handler = new MIUWPF_Upload_Handler( new MIUWPF_Tests_Fake_Storage() );
	}

	protected function tear_down() {
		unset( $_FILES['file'] );
		parent::tear_down();
	}

	public function test_register_hooks_wires_up_both_the_priv_and_nopriv_ajax_actions() {

		Actions\expectAdded( 'wp_ajax_' . MIUWPF_Upload_Handler::ACTION )->once();
		Actions\expectAdded( 'wp_ajax_nopriv_' . MIUWPF_Upload_Handler::ACTION )->once();

		$this->handler->register_hooks();

		// Brain Monkey's expectation is verified in tear_down(); this just
		// keeps PHPUnit from flagging the test as performing no assertions.
		$this->addToAssertionCount( 1 );
	}

	public function test_handle_fails_with_400_when_no_file_was_submitted() {

		unset( $_FILES['file'] );

		$exception = $this->expect_ajax_failure();

		$this->assertSame( 400, $exception->status_code );
		$this->assertSame( 'Upload failed. Please try again.', $exception->response_data['message'] );
	}

	public function test_handle_fails_with_400_when_the_files_array_is_missing_required_keys() {

		$_FILES['file'] = [ 'name' => 'photo.jpg' ]; // No error/tmp_name/size.

		$exception = $this->expect_ajax_failure();

		$this->assertSame( 400, $exception->status_code );
		$this->assertSame( 'Upload failed. Please try again.', $exception->response_data['message'] );
	}

	public function test_handle_fails_with_400_when_php_reports_an_upload_error() {

		$_FILES['file'] = [
			'name'     => 'photo.jpg',
			'type'     => 'image/jpeg',
			'tmp_name' => $this->non_uploaded_tmp_file(),
			'error'    => UPLOAD_ERR_INI_SIZE,
			'size'     => 1000,
		];

		$exception = $this->expect_ajax_failure();

		$this->assertSame( 400, $exception->status_code );
		$this->assertSame( 'Upload failed. Please try again.', $exception->response_data['message'] );
	}

	public function test_handle_fails_with_400_for_a_file_that_php_never_registered_as_an_upload() {

		$_FILES['file'] = [
			'name'     => 'photo.jpg',
			'type'     => 'image/jpeg',
			'tmp_name' => $this->non_uploaded_tmp_file(),
			'error'    => UPLOAD_ERR_OK,
			'size'     => 1000,
		];

		$exception = $this->expect_ajax_failure();

		$this->assertSame( 400, $exception->status_code );
		$this->assertSame( 'Upload failed. Please try again.', $exception->response_data['message'] );
	}

	private function non_uploaded_tmp_file() {

		$path = sys_get_temp_dir() . '/miuwpf_test_upload_' . uniqid( '', true ) . '.jpg';
		file_put_contents( $path, 'not-a-real-http-upload' );

		return $path;
	}

	private function expect_ajax_failure() {

		try {
			$this->handler->handle();
			$this->fail( 'Expected handle() to short-circuit via wp_send_json_error().' );
		} catch ( MIUWPF_Tests_Ajax_Response_Exception $exception ) {
			$this->assertFalse( $exception->response_success );

			return $exception;
		}
	}
}
