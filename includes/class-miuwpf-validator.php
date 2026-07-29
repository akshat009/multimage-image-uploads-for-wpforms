<?php
/**
 * Submission validator for the field.
 *
 * Single responsibility: given a field config and submitted tokens,
 * return the first validation error message or null when valid.
 * Free of WPForms and HTTP concerns, so it is unit-testable.
 *
 * @package MIUWPF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MIUWPF_Validator {

	/**
	 * Storage backend.
	 *
	 * @var MIUWPF_Storage
	 */
	private $storage;

	/**
	 * Constructor.
	 *
	 * @param MIUWPF_Storage $storage Storage backend.
	 */
	public function __construct( MIUWPF_Storage $storage ) {
		$this->storage = $storage;
	}

	/**
	 * Validate submitted tokens against the field configuration.
	 *
	 * @param MIUWPF_Field_Config $config Field configuration.
	 * @param MIUWPF_Token[]      $tokens Submitted tokens.
	 *
	 * @return string|null Error message, or null when valid.
	 */
	public function validate( MIUWPF_Field_Config $config, array $tokens ) {

		if ( $config->is_required() && empty( $tokens ) ) {
			return __( 'Please upload at least one image.', 'multimage-image-uploads-for-wpforms' );
		}

		if ( empty( $tokens ) ) {
			return null;
		}

		if ( count( $tokens ) > $config->max_files() ) {
			return sprintf(
				/* translators: %d: maximum number of images. */
				__( 'You can upload a maximum of %d images.', 'multimage-image-uploads-for-wpforms' ),
				$config->max_files()
			);
		}

		foreach ( $tokens as $token ) {

			if ( ! $this->storage->exists( $token ) ) {
				return __( 'An uploaded image has expired. Please re-upload and submit again.', 'multimage-image-uploads-for-wpforms' );
			}

			if ( ! in_array( $token->extension(), $config->extensions(), true ) ) {
				return sprintf(
					/* translators: %s: comma-separated list of allowed file extensions. */
					__( 'Only these file types are allowed: %s', 'multimage-image-uploads-for-wpforms' ),
					implode( ', ', $config->extensions() )
				);
			}

			if ( $this->storage->size( $token ) > $config->max_bytes() ) {
				return sprintf(
					/* translators: %s: maximum file size in megabytes. */
					__( 'Each image must be under %s MB.', 'multimage-image-uploads-for-wpforms' ),
					$config->max_mb()
				);
			}
		}

		return null;
	}
}
