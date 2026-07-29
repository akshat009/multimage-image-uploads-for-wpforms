<?php
/**
 * PHPUnit bootstrap.
 *
 * These are isolated unit tests: they do not load WordPress core.
 * WP constants/utility functions are provided by wp-stubs.php; I/O
 * and hook functions (wp_upload_dir, add_action, ...) are mocked
 * per test case with Brain Monkey.
 *
 * @package MIUWPF
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

require_once __DIR__ . '/wp-stubs.php';

$includes = dirname( __DIR__ ) . '/includes/';

require_once $includes . 'interface-miuwpf-storage.php';
require_once $includes . 'class-miuwpf-token.php';
require_once $includes . 'class-miuwpf-field-config.php';
require_once $includes . 'class-miuwpf-temp-storage.php';
require_once $includes . 'class-miuwpf-upload-handler.php';
require_once $includes . 'class-miuwpf-validator.php';
require_once $includes . 'class-miuwpf-email-attachments.php';

require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/Doubles/FakeStorage.php';
require_once __DIR__ . '/Doubles/FakeWpFilesystem.php';
