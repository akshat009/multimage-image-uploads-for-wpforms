<?php
/**
 * Plugin Name:       Multimage – Image Uploads for WPForms
 * Description:       Adds a Multi Image Upload field to the WPForms builder with instant AJAX pre-upload, per-field limits, and notification email attachments.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Akshat Saxena
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       multimage-image-uploads-for-wpforms
 *
 * @package MIUWPF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MIUWPF_VERSION', '1.0.0' );
define( 'MIUWPF_PLUGIN_FILE', __FILE__ );
define( 'MIUWPF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MIUWPF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once MIUWPF_PLUGIN_DIR . 'includes/interface-miuwpf-storage.php';
require_once MIUWPF_PLUGIN_DIR . 'includes/class-miuwpf-token.php';
require_once MIUWPF_PLUGIN_DIR . 'includes/class-miuwpf-field-config.php';
require_once MIUWPF_PLUGIN_DIR . 'includes/class-miuwpf-temp-storage.php';
require_once MIUWPF_PLUGIN_DIR . 'includes/class-miuwpf-upload-handler.php';
require_once MIUWPF_PLUGIN_DIR . 'includes/class-miuwpf-validator.php';
require_once MIUWPF_PLUGIN_DIR . 'includes/class-miuwpf-email-attachments.php';
require_once MIUWPF_PLUGIN_DIR . 'includes/class-miuwpf-assets.php';
require_once MIUWPF_PLUGIN_DIR . 'includes/class-miuwpf-plugin.php';

MIUWPF_Plugin::instance()->boot();
