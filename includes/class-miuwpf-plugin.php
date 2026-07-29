<?php
/**
 * Composition root.
 *
 * The only place where concrete classes are instantiated and wired
 * together. Every other class receives its dependencies through its
 * constructor and attaches its own hooks via register_hooks().
 *
 * @package MIUWPF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MIUWPF_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Storage service.
	 *
	 * @var MIUWPF_Storage
	 */
	private $storage;

	/**
	 * Assets service.
	 *
	 * @var MIUWPF_Assets
	 */
	private $assets;

	/**
	 * Email attachments service.
	 *
	 * @var MIUWPF_Email_Attachments
	 */
	private $email;

	/**
	 * Get the shared instance.
	 *
	 * @return self
	 */
	public static function instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Private constructor — use instance().
	 */
	private function __construct() {}

	/**
	 * Wire services and attach hooks.
	 *
	 * @return void
	 */
	public function boot() {

		$this->storage = new MIUWPF_Temp_Storage();
		$this->assets  = new MIUWPF_Assets();
		$this->email   = new MIUWPF_Email_Attachments();

		$upload_handler = new MIUWPF_Upload_Handler( $this->storage );

		$this->assets->register_hooks();
		$this->email->register_hooks();
		$upload_handler->register_hooks();

		add_action( 'wpforms_loaded', [ $this, 'register_field' ] );
	}

	/**
	 * Register the WPForms field once WPForms is available.
	 *
	 * @return void
	 */
	public function register_field() {

		if ( ! class_exists( 'WPForms_Field' ) ) {
			return;
		}

		require_once MIUWPF_PLUGIN_DIR . 'includes/class-miuwpf-field.php';

		new MIUWPF_Field(
			$this->storage,
			new MIUWPF_Validator( $this->storage ),
			$this->email,
			$this->assets
		);
	}
}
