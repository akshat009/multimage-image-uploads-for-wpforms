<?php
/**
 * Frontend asset registration and conditional enqueueing.
 *
 * @package MIUWPF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MIUWPF_Assets {

	/**
	 * Script/style handle.
	 */
	const HANDLE = 'miuwpf-frontend';

	/**
	 * Attach WordPress hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'wp_enqueue_scripts', [ $this, 'register' ] );
	}

	/**
	 * Register (not enqueue) the assets.
	 *
	 * @return void
	 */
	public function register() {

		wp_register_style(
			self::HANDLE,
			MIUWPF_PLUGIN_URL . 'assets/css/frontend.css',
			[],
			MIUWPF_VERSION
		);

		wp_register_script(
			self::HANDLE,
			MIUWPF_PLUGIN_URL . 'assets/js/frontend.js',
			[],
			MIUWPF_VERSION,
			true
		);

		wp_localize_script(
			self::HANDLE,
			'miuwpfVars',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( MIUWPF_Upload_Handler::NONCE ),
				'i18n'    => [
					/* translators: %d: maximum number of images allowed. */
					'maxFiles'     => __( 'Maximum %d images allowed.', 'multimage-image-uploads-for-wpforms' ),
					/* translators: %s: name of the rejected file. */
					'badType'      => __( 'File type not allowed: %s', 'multimage-image-uploads-for-wpforms' ),
					/* translators: %s: name of the rejected file. */
					'tooLarge'     => __( 'Too large: %s', 'multimage-image-uploads-for-wpforms' ),
					'uploadFailed' => __( 'Upload failed. Please try again.', 'multimage-image-uploads-for-wpforms' ),
					'network'      => __( 'Upload failed. Check your connection.', 'multimage-image-uploads-for-wpforms' ),
					'uploading'    => __( 'uploading…', 'multimage-image-uploads-for-wpforms' ),
					'remove'       => __( 'Remove image', 'multimage-image-uploads-for-wpforms' ),
				],
			]
		);
	}

	/**
	 * Enqueue the registered assets (called when the field renders).
	 *
	 * @return void
	 */
	public function enqueue() {
		wp_enqueue_style( self::HANDLE );
		wp_enqueue_script( self::HANDLE );
	}
}
