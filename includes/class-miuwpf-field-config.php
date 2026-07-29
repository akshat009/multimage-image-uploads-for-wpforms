<?php
/**
 * Immutable per-field configuration value object.
 *
 * Centralizes defaults, hard limits, and the interpretation of raw
 * builder settings, so the rest of the code never reads the raw
 * $field array for these values.
 *
 * @package MIUWPF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MIUWPF_Field_Config {

	/**
	 * Absolute per-file size cap in bytes. Field settings can only
	 * narrow limits, never exceed this.
	 */
	const HARD_MAX_SIZE = 10485760;

	/**
	 * Absolute extension whitelist.
	 *
	 * @var string[]
	 */
	const HARD_EXTS = [ 'jpg', 'jpeg', 'png', 'gif', 'webp' ];

	/**
	 * Maximum number of files.
	 *
	 * @var int
	 */
	private $max_files;

	/**
	 * Per-file limit in megabytes (as configured).
	 *
	 * @var float
	 */
	private $max_mb;

	/**
	 * Allowed extensions.
	 *
	 * @var string[]
	 */
	private $extensions;

	/**
	 * Whether the field is required.
	 *
	 * @var bool
	 */
	private $required;

	/**
	 * Field label.
	 *
	 * @var string
	 */
	private $label;

	/**
	 * Upload button text.
	 *
	 * @var string
	 */
	private $button_text;

	/**
	 * Build from a WPForms field settings array.
	 *
	 * @param array $field Field settings.
	 *
	 * @return self
	 */
	public static function from_field( array $field ) {

		$config = new self();

		$config->max_files   = ! empty( $field['max_files'] ) ? absint( $field['max_files'] ) : 5;
		$config->max_mb      = ! empty( $field['max_size'] ) ? (float) $field['max_size'] : 5;
		$config->required    = ! empty( $field['required'] );
		$config->label       = ! empty( $field['label'] ) ? sanitize_text_field( $field['label'] ) : '';
		$config->button_text = ! empty( $field['button_text'] ) ? sanitize_text_field( $field['button_text'] ) : '';

		$raw  = ! empty( $field['extensions'] ) ? (string) $field['extensions'] : 'jpg,jpeg,png,webp';
		$exts = array_filter( array_map( 'strtolower', array_map( 'trim', explode( ',', $raw ) ) ) );
		$exts = array_values( array_intersect( $exts, self::HARD_EXTS ) );

		$config->extensions = ! empty( $exts ) ? $exts : [ 'jpg', 'jpeg', 'png', 'webp' ];

		return $config;
	}

	/**
	 * Maximum number of files.
	 *
	 * @return int
	 */
	public function max_files() {
		return $this->max_files;
	}

	/**
	 * Per-file limit in megabytes.
	 *
	 * @return float
	 */
	public function max_mb() {
		return $this->max_mb;
	}

	/**
	 * Per-file limit in bytes, capped at the hard limit.
	 *
	 * @return int
	 */
	public function max_bytes() {
		return (int) min( $this->max_mb * 1024 * 1024, self::HARD_MAX_SIZE );
	}

	/**
	 * Allowed extensions.
	 *
	 * @return string[]
	 */
	public function extensions() {
		return $this->extensions;
	}

	/**
	 * Whether the field is required.
	 *
	 * @return bool
	 */
	public function is_required() {
		return $this->required;
	}

	/**
	 * Field label with a translated fallback.
	 *
	 * @return string
	 */
	public function label() {
		return '' !== $this->label
			? $this->label
			: __( 'Images', 'multimage-image-uploads-for-wpforms' );
	}

	/**
	 * Upload button text with a translated fallback.
	 *
	 * @return string
	 */
	public function button_text() {
		return '' !== $this->button_text
			? $this->button_text
			: __( 'Upload Images', 'multimage-image-uploads-for-wpforms' );
	}
}
