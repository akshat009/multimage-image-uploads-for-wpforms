<?php
/**
 * Tests for MIUWPF_Validator.
 *
 * @package MIUWPF
 */

final class ValidatorTest extends MIUWPF_TestCase {

	/** @var MIUWPF_Tests_Fake_Storage */
	private $storage;

	/** @var MIUWPF_Validator */
	private $validator;

	protected function set_up() {
		parent::set_up();

		$this->storage   = new MIUWPF_Tests_Fake_Storage();
		$this->validator = new MIUWPF_Validator( $this->storage );
	}

	private function token( $filename = 'photo.jpg' ) {
		return MIUWPF_Token::from_input( 'abcd1234abcd1234-' . $filename );
	}

	public function test_required_field_with_no_tokens_returns_an_error() {

		$config = MIUWPF_Field_Config::from_field( [ 'required' => 1 ] );

		$this->assertSame(
			'Please upload at least one image.',
			$this->validator->validate( $config, [] )
		);
	}

	public function test_optional_field_with_no_tokens_is_valid() {

		$config = MIUWPF_Field_Config::from_field( [ 'required' => 0 ] );

		$this->assertNull( $this->validator->validate( $config, [] ) );
	}

	public function test_too_many_tokens_returns_an_error() {

		$config = MIUWPF_Field_Config::from_field( [ 'max_files' => 1 ] );

		$a = $this->token( 'a.jpg' );
		$b = $this->token( 'b.jpg' );
		$this->storage->put( $a->value(), 1000 );
		$this->storage->put( $b->value(), 1000 );

		$this->assertSame(
			'You can upload a maximum of 1 images.',
			$this->validator->validate( $config, [ $a, $b ] )
		);
	}

	public function test_a_token_with_no_stored_file_returns_an_expired_error() {

		$config = MIUWPF_Field_Config::from_field( [] );
		$token  = $this->token( 'ghost.jpg' );
		// Deliberately not registered in $this->storage.

		$this->assertSame(
			'An uploaded image has expired. Please re-upload and submit again.',
			$this->validator->validate( $config, [ $token ] )
		);
	}

	public function test_a_disallowed_extension_returns_an_error() {

		$config = MIUWPF_Field_Config::from_field( [ 'extensions' => 'jpg,png' ] );
		$token  = $this->token( 'photo.gif' );
		$this->storage->put( $token->value(), 1000 );

		$this->assertSame(
			'Only these file types are allowed: jpg, png',
			$this->validator->validate( $config, [ $token ] )
		);
	}

	public function test_an_oversized_file_returns_an_error() {

		$config = MIUWPF_Field_Config::from_field( [ 'max_size' => 1 ] );
		$token  = $this->token( 'photo.jpg' );
		$this->storage->put( $token->value(), 2 * 1024 * 1024 );

		$this->assertSame(
			'Each image must be under 1 MB.',
			$this->validator->validate( $config, [ $token ] )
		);
	}

	public function test_valid_tokens_within_all_limits_pass() {

		$config = MIUWPF_Field_Config::from_field(
			[
				'max_files'  => 2,
				'max_size'   => 5,
				'extensions' => 'jpg,png',
			]
		);

		$a = $this->token( 'a.jpg' );
		$b = $this->token( 'b.png' );
		$this->storage->put( $a->value(), 1024 );
		$this->storage->put( $b->value(), 2048 );

		$this->assertNull( $this->validator->validate( $config, [ $a, $b ] ) );
	}

	public function test_validation_stops_at_the_first_failing_token() {

		$config = MIUWPF_Field_Config::from_field( [ 'extensions' => 'jpg', 'max_size' => 1 ] );

		$bad_extension = $this->token( 'a.gif' );
		$too_big       = $this->token( 'b.jpg' );
		$this->storage->put( $bad_extension->value(), 1000 );
		$this->storage->put( $too_big->value(), 5 * 1024 * 1024 );

		$this->assertSame(
			'Only these file types are allowed: jpg',
			$this->validator->validate( $config, [ $bad_extension, $too_big ] )
		);
	}
}
