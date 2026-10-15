# Multimage – Image Uploads for WPForms

![Banner](.github/readme/banner.png)

**Add a Multi Image Upload field to WPForms — including WPForms Lite — with instant AJAX uploads, per-field limits, and email attachments.**

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/multimage-image-uploads-for-wpforms)](https://wordpress.org/plugins/multimage-image-uploads-for-wpforms/)
[![WordPress Tested](https://img.shields.io/wordpress/plugin/tested/multimage-image-uploads-for-wpforms)](https://wordpress.org/plugins/multimage-image-uploads-for-wpforms/)
[![License: GPL v2+](https://img.shields.io/badge/license-GPL--2.0--or--later-blue)](https://www.gnu.org/licenses/gpl-2.0.html)

WPForms Lite doesn't ship a file upload field. Multimage adds one — a **Multi Image Upload** field that lets visitors attach multiple images to any WPForms form, with instant AJAX pre-upload so nothing is lost to form serialization.

[![Watch Multimage Walkthrough](https://img.youtube.com/vi/XG80kG8uRe0/maxresdefault.jpg)](https://www.youtube.com/watch?v=XG80kG8uRe0)

**[Try it live in your browser](https://playground.wordpress.net/?plugin=wpforms-lite&plugin=multimage-image-uploads-for-wpforms&url=/wp-admin/admin.php%3Fpage%3Dwpforms-overview)** — no install required, via WordPress Playground.

**[Live on the WordPress Plugin Directory →](https://wordpress.org/plugins/multimage-image-uploads-for-wpforms/)**

## Features

- New "Multi Image Upload" field in the WPForms builder — drag into any form
- Works with **WPForms Lite** (no Pro required)
- Works with WPForms AJAX submission on or off — images pre-upload instantly on selection, so nothing is lost to form serialization
- Per-field settings: Max File Uploads, Allowed File Extensions, Max File Size (MB)
- Selected images listed with individual remove buttons before submit
- Image URLs included in the `{all_fields}` email template
- Uploaded images attached to notification emails
- Server-side validation: content-based MIME detection, extension whitelist, size limits
- Automatic cleanup of abandoned temporary uploads after 24 hours

## Screenshots

| Front-end field | Field settings (builder) |
|---|---|
| ![Front-end field](.github/readme/screenshot-1.png) | ![Field settings](.github/readme/screenshot-3.png) |

## How it works

When a visitor selects images, each file uploads immediately to a temporary folder through a secured AJAX endpoint. Lightweight tokens travel with the form submission, and on successful submission the files move into the standard WordPress uploads directory.

## Development

This repo includes the full development setup — PHPUnit tests with [Brain Monkey](https://github.com/Brain-WP/BrainMonkey) for mocking WordPress core functions:

```bash
composer install
vendor/bin/phpunit
```

Production releases are mirrored to the [WordPress.org SVN repository](https://plugins.svn.wordpress.org/multimage-image-uploads-for-wpforms/) — `trunk/` there is the canonical shipped source.

## Other Plugins by Me

Check out my other free plugins on the WordPress Plugin Directory:

* **[Story Video Block](https://wordpress.org/plugins/story-video-block/)** — Fast video player, YouTube/Vimeo embed & video testimonials block for Gutenberg with click-to-play lazy loading.
* **[Quovex Blocks](https://wordpress.org/plugins/quovex-blocks/)** — Modern Gutenberg blocks: Filterable Query Grid, Post Carousel, Content Showcase & Notification Banner.
* **[Flash Sale Stock Guard for WooCommerce](https://wordpress.org/plugins/flash-sale-stock-guard-for-woocommerce/)** — Stop overselling during limited drops and flash sales by reserving stock at add-to-cart.
* **[Very Simple Contact Us Form](https://wordpress.org/plugins/very-simple-contact-us-form/)** — Ultra-lightweight, minimal contact form with zero bloat.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).

---

*This is an independent, third-party add-on. It is not affiliated with, endorsed by, or sponsored by WPForms or Awesome Motive, Inc. "WPForms" is a trademark of its respective owner and is used here only to describe compatibility.*
