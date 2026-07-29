<?php
/**
 * Collects uploaded file paths during processing and attaches them
 * to WPForms notification emails.
 *
 * @package MIUWPF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MIUWPF_Email_Attachments {

	/**
	 * Collected file paths, keyed by field ID.
	 *
	 * @var array
	 */
	private $paths = [];

	/**
	 * Attach WordPress hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_filter( 'wpforms_entry_email_atts', [ $this, 'filter_email' ], 10, 4 );
	}

	/**
	 * Collect final file paths for a field.
	 *
	 * @param int      $field_id Field ID.
	 * @param string[] $paths    Absolute file paths.
	 *
	 * @return void
	 */
	public function collect( $field_id, array $paths ) {
		$this->paths[ absint( $field_id ) ] = $paths;
	}

	/**
	 * Add collected files to the notification email.
	 *
	 * @param array $email     Email settings.
	 * @param array $fields    Entry fields.
	 * @param array $entry     Entry data.
	 * @param array $form_data Form data.
	 *
	 * @return array
	 */
	public function filter_email( $email, $fields, $entry, $form_data ) {

		if ( empty( $this->paths ) ) {
			return $email;
		}

		$attachments = [];

		foreach ( $this->paths as $field_paths ) {
			$attachments = array_merge( $attachments, $field_paths );
		}

		if ( ! empty( $attachments ) ) {
			$existing             = ! empty( $email['attachments'] ) ? (array) $email['attachments'] : [];
			$email['attachments'] = array_merge( $existing, $attachments );
		}

		return $email;
	}
}
