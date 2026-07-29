<?php
/**
 * Tests for MIUWPF_Field_Config.
 *
 * @package MIUWPF
 */

final class FieldConfigTest extends MIUWPF_TestCase {

	public function test_defaults_are_applied_when_field_array_is_empty() {

		$config = MIUWPF_Field_Config::from_field( [] );

		$this->assertSame( 5, $config->max_files() );
		// The default branch is a bare int literal in the source (no float
		// cast), unlike the explicit-value branch below — so this stays int.
		$this->assertSame( 5, $config->max_mb() );
		$this->assertFalse( $config->is_required() );
		$this->assertSame( [ 'jpg', 'jpeg', 'png', 'webp' ], $config->extensions() );
		$this->assertSame( 'Images', $config->label() );
	}

	public function test_provided_values_are_read_from_the_field_array() {

		$config = MIUWPF_Field_Config::from_field(
			[
				'max_files'  => 3,
				'max_size'   => 2,
				'required'   => 1,
				'label'      => 'Upload photos',
				'extensions' => 'png,gif',
			]
		);

		$this->assertSame( 3, $config->max_files() );
		$this->assertSame( 2.0, $config->max_mb() );
		$this->assertTrue( $config->is_required() );
		$this->assertSame( 'Upload photos', $config->label() );
		$this->assertSame( [ 'png', 'gif' ], $config->extensions() );
	}

	public function test_extensions_outside_the_hard_whitelist_are_filtered_out() {

		$config = MIUWPF_Field_Config::from_field( [ 'extensions' => 'exe,php,png' ] );

		$this->assertSame( [ 'png' ], $config->extensions() );
	}

	public function test_extensions_fall_back_to_default_when_none_survive_the_whitelist() {

		$config = MIUWPF_Field_Config::from_field( [ 'extensions' => 'exe,php,svg' ] );

		$this->assertSame( [ 'jpg', 'jpeg', 'png', 'webp' ], $config->extensions() );
	}

	public function test_extensions_are_trimmed_and_lowercased() {

		$config = MIUWPF_Field_Config::from_field( [ 'extensions' => ' JPG , Gif ' ] );

		$this->assertSame( [ 'jpg', 'gif' ], $config->extensions() );
	}

	public function test_max_bytes_is_capped_at_the_hard_limit_regardless_of_configured_size() {

		$config = MIUWPF_Field_Config::from_field( [ 'max_size' => 999 ] );

		$this->assertSame( MIUWPF_Field_Config::HARD_MAX_SIZE, $config->max_bytes() );
	}

	public function test_max_bytes_reflects_configured_size_when_under_the_hard_limit() {

		$config = MIUWPF_Field_Config::from_field( [ 'max_size' => 2 ] );

		$this->assertSame( 2 * 1024 * 1024, $config->max_bytes() );
	}

	public function test_label_falls_back_to_translated_default_when_blank() {

		$config = MIUWPF_Field_Config::from_field( [ 'label' => '' ] );

		$this->assertSame( 'Images', $config->label() );
	}

	public function test_button_text_falls_back_to_translated_default_when_unset() {

		$config = MIUWPF_Field_Config::from_field( [] );

		$this->assertSame( 'Upload Images', $config->button_text() );
	}

	public function test_button_text_falls_back_to_translated_default_when_blank() {

		$config = MIUWPF_Field_Config::from_field( [ 'button_text' => '' ] );

		$this->assertSame( 'Upload Images', $config->button_text() );
	}

	public function test_button_text_reads_the_configured_value() {

		$config = MIUWPF_Field_Config::from_field( [ 'button_text' => 'Add Photos' ] );

		$this->assertSame( 'Add Photos', $config->button_text() );
	}

	public function test_button_text_is_independent_of_the_field_label() {

		$config = MIUWPF_Field_Config::from_field(
			[
				'label'       => 'Photo evidence',
				'button_text' => 'Add Photos',
			]
		);

		$this->assertSame( 'Photo evidence', $config->label() );
		$this->assertSame( 'Add Photos', $config->button_text() );
	}

	public function test_zero_max_files_falls_back_to_default() {

		// ! empty( 0 ) is false, so an explicit 0 is treated the same as "not set".
		$config = MIUWPF_Field_Config::from_field( [ 'max_files' => 0 ] );

		$this->assertSame( 5, $config->max_files() );
	}

	/**
	 * array_intersect() preserves duplicate entries from its first
	 * argument, so a repeated extension in the raw setting survives
	 * into the final list. Harmless for validation (in_array only
	 * needs one match) but documented here since it's non-obvious.
	 */
	public function test_extensions_can_contain_duplicates_when_input_repeats_a_value() {

		$config = MIUWPF_Field_Config::from_field( [ 'extensions' => 'jpg,jpg,png' ] );

		$this->assertSame( [ 'jpg', 'jpg', 'png' ], $config->extensions() );
	}
}
