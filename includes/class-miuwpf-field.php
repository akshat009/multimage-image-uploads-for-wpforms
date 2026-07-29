<?php
/**
 * WPForms field adapter.
 *
 * Thin adapter between the WPForms field API (Template Method:
 * WPForms calls field_display / validate / format at defined points)
 * and this plugin's injected services. Contains presentation and
 * glue only — validation rules, storage, and email logic live in
 * their own classes.
 *
 * @package MIUWPF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MIUWPF_Field extends WPForms_Field {

	/**
	 * Storage backend.
	 *
	 * @var MIUWPF_Storage
	 */
	private $storage;

	/**
	 * Validator.
	 *
	 * @var MIUWPF_Validator
	 */
	private $validator;

	/**
	 * Email attachments collector.
	 *
	 * @var MIUWPF_Email_Attachments
	 */
	private $email;

	/**
	 * Assets service.
	 *
	 * @var MIUWPF_Assets
	 */
	private $assets;

	/**
	 * Constructor with injected dependencies.
	 *
	 * @param MIUWPF_Storage           $storage   Storage backend.
	 * @param MIUWPF_Validator         $validator Validator.
	 * @param MIUWPF_Email_Attachments $email     Email collector.
	 * @param MIUWPF_Assets            $assets    Assets service.
	 */
	public function __construct(
		MIUWPF_Storage $storage,
		MIUWPF_Validator $validator,
		MIUWPF_Email_Attachments $email,
		MIUWPF_Assets $assets
	) {
		$this->storage   = $storage;
		$this->validator = $validator;
		$this->email     = $email;
		$this->assets    = $assets;

		parent::__construct();
	}

	/**
	 * Field defaults.
	 *
	 * @return void
	 */
	public function init() {
		$this->name  = esc_html__( 'Multi Image Upload', 'multimage-image-uploads-for-wpforms' );
		$this->type  = 'miuwpf_multi_image';
		$this->icon  = 'fa-picture-o';
		$this->order = 300;
		$this->group = 'standard';
	}

	/**
	 * Builder: field options panel.
	 *
	 * @param array $field Field settings.
	 *
	 * @return void
	 */
	public function field_options( $field ) {

		$this->field_option( 'basic-options', $field, [ 'markup' => 'open' ] );
		$this->field_option( 'label', $field );
		$this->field_option( 'description', $field );
		$this->field_option( 'required', $field );
		$this->field_option( 'basic-options', $field, [ 'markup' => 'close' ] );

		$this->field_option( 'advanced-options', $field, [ 'markup' => 'open' ] );
		$this->field_option( 'label_hide', $field );
		$this->text_option( $field, 'button_text', __( 'Button Text', 'multimage-image-uploads-for-wpforms' ), __( 'Text shown on the upload button.', 'multimage-image-uploads-for-wpforms' ), __( 'Upload Images', 'multimage-image-uploads-for-wpforms' ) );
		$this->number_option( $field, 'max_files', __( 'Max File Uploads', 'multimage-image-uploads-for-wpforms' ), __( 'Maximum number of images a user can upload.', 'multimage-image-uploads-for-wpforms' ), 5 );
		$this->text_option( $field, 'extensions', __( 'Allowed File Extensions', 'multimage-image-uploads-for-wpforms' ), __( 'Comma separated, e.g. jpg,jpeg,png,webp', 'multimage-image-uploads-for-wpforms' ), 'jpg,jpeg,png,webp' );
		$this->number_option( $field, 'max_size', __( 'Max File Size (MB)', 'multimage-image-uploads-for-wpforms' ), __( 'Maximum size per image in megabytes.', 'multimage-image-uploads-for-wpforms' ), 5 );
		$this->field_option( 'advanced-options', $field, [ 'markup' => 'close' ] );
	}

	/**
	 * Builder: field preview inside the form canvas.
	 *
	 * @param array $field Field settings.
	 *
	 * @return void
	 */
	public function field_preview( $field ) {

		$config = MIUWPF_Field_Config::from_field( $field );

		$this->field_preview_option( 'label', $field );

		echo '<input type="file" multiple disabled style="max-width:100%;">';
		printf(
			'<p class="note" style="margin:5px 0 0;font-size:12px;color:#777;">%s</p>',
			esc_html(
				sprintf(
					/* translators: %d: maximum number of files. */
					__( 'Multi image upload — max %d files', 'multimage-image-uploads-for-wpforms' ),
					$config->max_files()
				)
			)
		);

		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Frontend: render the field. The visible control is a styled
	 * button; the real file input is hidden and has no name attribute
	 * — only hidden token inputs are submitted.
	 *
	 * @param array $field      Field settings.
	 * @param array $deprecated Deprecated.
	 * @param array $form_data  Form data.
	 *
	 * @return void
	 */
	public function field_display( $field, $deprecated, $form_data ) {

		$this->assets->enqueue();

		$config = MIUWPF_Field_Config::from_field( $field );
		$accept = '.' . implode( ',.', $config->extensions() );

		printf(
			'<div class="miuwpf-wrap" data-name="wpforms[fields][%1$d][]" data-max="%2$d" data-size="%3$d" data-exts="%4$s">
				<input type="file" class="miuwpf-input jcf-ignore" accept="%5$s" multiple hidden>
				<button type="button" class="miuwpf-btn">
					<span class="miuwpf-btn-icon" aria-hidden="true">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M12 16V4M12 4L7 9M12 4L17 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M4 16V18C4 19.1046 4.89543 20 6 20H18C19.1046 20 20 19.1046 20 18V16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</span>
					<span class="miuwpf-btn-label">%6$s</span>
				</button>
				<p class="miuwpf-hint">%7$s</p>
				<div class="miuwpf-list"></div>
				<div class="miuwpf-tokens"></div>
			</div>',
			absint( $field['id'] ),
			absint( $config->max_files() ),
			absint( $config->max_bytes() ),
			esc_attr( implode( ',', $config->extensions() ) ),
			esc_attr( $accept ),
			esc_html( $config->button_text() ),
			esc_html__( 'or drag and drop images here', 'multimage-image-uploads-for-wpforms' )
		);
	}

	/**
	 * Validate submitted tokens (delegated).
	 *
	 * @param int   $field_id     Field ID.
	 * @param mixed $field_submit Submitted value.
	 * @param array $form_data    Form data.
	 *
	 * @return void
	 */
	public function validate( $field_id, $field_submit, $form_data ) {

		$config = MIUWPF_Field_Config::from_field( $form_data['fields'][ $field_id ] );
		$error  = $this->validator->validate( $config, $this->tokens_from_submit( $field_submit ) );

		if ( null !== $error ) {
			wpforms()->process->errors[ absint( $form_data['id'] ) ][ $field_id ] = esc_html( $error );
		}
	}

	/**
	 * Format: promote temp files, set field value, collect attachments.
	 *
	 * @param int   $field_id     Field ID.
	 * @param mixed $field_submit Submitted value.
	 * @param array $form_data    Form data.
	 *
	 * @return void
	 */
	public function format( $field_id, $field_submit, $form_data ) {

		$config = MIUWPF_Field_Config::from_field( $form_data['fields'][ $field_id ] );
		$urls   = [];
		$paths  = [];

		foreach ( $this->tokens_from_submit( $field_submit ) as $token ) {

			$result = $this->storage->promote( $token );

			if ( null !== $result ) {
				$urls[]  = $result['url'];
				$paths[] = $result['file'];
			}
		}

		$this->email->collect( $field_id, $paths );

		wpforms()->process->fields[ $field_id ] = [
			'name'  => $config->label(),
			'value' => implode( "\n", array_map( 'esc_url_raw', $urls ) ),
			'id'    => absint( $field_id ),
			'type'  => $this->type,
		];
	}

	/**
	 * Parse submitted values into unique valid tokens.
	 *
	 * @param mixed $field_submit Raw submitted value.
	 *
	 * @return MIUWPF_Token[]
	 */
	private function tokens_from_submit( $field_submit ) {

		$tokens = [];

		foreach ( (array) $field_submit as $raw ) {

			$token = MIUWPF_Token::from_input( $raw );

			if ( null !== $token && ! isset( $tokens[ $token->value() ] ) ) {
				$tokens[ $token->value() ] = $token;
			}
		}

		return array_values( $tokens );
	}

	/**
	 * Render a labeled number option row in the builder.
	 *
	 * @param array  $field   Field settings.
	 * @param string $slug    Option slug.
	 * @param string $label   Label text.
	 * @param string $tooltip Tooltip text.
	 * @param int    $default Default value.
	 *
	 * @return void
	 */
	private function number_option( $field, $slug, $label, $tooltip, $default ) {

		$lbl = $this->field_element(
			'label',
			$field,
			[
				'slug'    => $slug,
				'value'   => esc_html( $label ),
				'tooltip' => esc_html( $tooltip ),
			],
			false
		);
		$fld = $this->field_element(
			'text',
			$field,
			[
				'slug'  => $slug,
				'type'  => 'number',
				'value' => ! empty( $field[ $slug ] ) ? absint( $field[ $slug ] ) : $default,
			],
			false
		);
		$this->field_element( 'row', $field, [ 'slug' => $slug, 'content' => $lbl . $fld ] );
	}

	/**
	 * Render a labeled text option row in the builder.
	 *
	 * @param array  $field   Field settings.
	 * @param string $slug    Option slug.
	 * @param string $label   Label text.
	 * @param string $tooltip Tooltip text.
	 * @param string $default Default value.
	 *
	 * @return void
	 */
	private function text_option( $field, $slug, $label, $tooltip, $default ) {

		$lbl = $this->field_element(
			'label',
			$field,
			[
				'slug'    => $slug,
				'value'   => esc_html( $label ),
				'tooltip' => esc_html( $tooltip ),
			],
			false
		);
		$fld = $this->field_element(
			'text',
			$field,
			[
				'slug'  => $slug,
				'value' => ! empty( $field[ $slug ] ) ? sanitize_text_field( $field[ $slug ] ) : $default,
			],
			false
		);
		$this->field_element( 'row', $field, [ 'slug' => $slug, 'content' => $lbl . $fld ] );
	}
}
