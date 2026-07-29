<?php
/**
 * Tests for MIUWPF_Token.
 *
 * @package MIUWPF
 */

final class TokenTest extends MIUWPF_TestCase {

	public function test_generate_produces_a_value_matching_the_token_pattern() {

		$token = MIUWPF_Token::generate( 'My Photo.jpg' );

		$this->assertSame( 1, preg_match( MIUWPF_Token::PATTERN, $token->value() ) );
	}

	public function test_generate_prefixes_with_16_lowercase_alnum_chars() {

		$token = MIUWPF_Token::generate( 'photo.jpg' );

		$prefix = substr( $token->value(), 0, 16 );

		$this->assertSame( 16, strlen( $prefix ) );
		$this->assertSame( 1, preg_match( '/^[a-z0-9]{16}$/', $prefix ) );
		$this->assertSame( '-', $token->value()[16] );
	}

	public function test_generate_sanitizes_the_filename_portion() {

		$token = MIUWPF_Token::generate( 'My Photo?.jpg' );

		$this->assertSame( 'My-Photo.jpg', $token->original_filename() );
	}

	public function test_generate_produces_unique_prefixes_across_calls() {

		$a = MIUWPF_Token::generate( 'photo.jpg' );
		$b = MIUWPF_Token::generate( 'photo.jpg' );

		$this->assertNotSame( $a->value(), $b->value() );
	}

	public function test_from_input_accepts_a_well_formed_token() {

		$token = MIUWPF_Token::from_input( 'abcd1234abcd1234-photo.jpg' );

		$this->assertInstanceOf( MIUWPF_Token::class, $token );
		$this->assertSame( 'abcd1234abcd1234-photo.jpg', $token->value() );
	}

	public function test_from_input_strips_directory_components_via_basename() {

		$token = MIUWPF_Token::from_input( '../../etc/abcd1234abcd1234-photo.jpg' );

		$this->assertInstanceOf( MIUWPF_Token::class, $token );
		$this->assertSame( 'abcd1234abcd1234-photo.jpg', $token->value() );
	}

	public function test_from_input_returns_null_for_empty_string() {
		$this->assertNull( MIUWPF_Token::from_input( '' ) );
	}

	public function test_from_input_returns_null_when_prefix_is_too_short() {
		$this->assertNull( MIUWPF_Token::from_input( 'abc-photo.jpg' ) );
	}

	public function test_from_input_returns_null_when_extension_is_missing() {
		$this->assertNull( MIUWPF_Token::from_input( 'abcd1234abcd1234-photo' ) );
	}

	public function test_from_input_returns_null_when_prefix_separator_is_missing() {
		$this->assertNull( MIUWPF_Token::from_input( 'abcd1234abcd1234photo.jpg' ) );
	}

	/**
	 * The pattern is matched with the /i flag, so an uppercase prefix
	 * is accepted even though MIUWPF_Token::generate() never produces
	 * one. This test documents that actual (lenient) behaviour.
	 */
	public function test_from_input_accepts_uppercase_prefix_due_to_case_insensitive_pattern() {

		$token = MIUWPF_Token::from_input( 'ABCD1234ABCD1234-photo.jpg' );

		$this->assertInstanceOf( MIUWPF_Token::class, $token );
	}

	public function test_original_filename_strips_only_the_random_prefix() {

		$token = MIUWPF_Token::from_input( 'abcd1234abcd1234-my-holiday-photo.jpg' );

		$this->assertSame( 'my-holiday-photo.jpg', $token->original_filename() );
	}

	public function test_extension_is_lowercased() {

		$token = MIUWPF_Token::from_input( 'abcd1234abcd1234-photo.PNG' );

		$this->assertSame( 'png', $token->extension() );
	}
}
