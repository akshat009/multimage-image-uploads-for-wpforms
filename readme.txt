=== Multimage – Image Uploads for WPForms ===
Contributors: developerakshat
Tags: wpforms, file upload, image upload, multiple files, photo upload
Requires at least: 5.8
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add multiple image uploads to WPForms — including WPForms Lite — with instant AJAX uploads, per-field limits, and email attachments.

== Description ==

= Video Walkthrough =

Watch the complete walkthrough and demo of Multimage:

https://www.youtube.com/watch?v=XG80kG8uRe0

Multimage adds a new **Multi Image Upload** field to the WPForms form builder — including WPForms Lite — letting visitors attach multiple photos or images to a single form submission.

> This is an independent, third-party add-on. It is not affiliated with, endorsed by, or sponsored by WPForms or Awesome Motive, Inc. "WPForms" is a trademark of its respective owner and is used here only to describe compatibility.

**Features**

* New "Multi Image Upload" field in the builder — drag into any form
* Works with **WPForms Lite** (no Pro required)
* Works with WPForms AJAX submission **on or off** — images pre-upload instantly on selection, so nothing is lost to form serialization
* Per-field settings: Max File Uploads, Allowed File Extensions, Max File Size (MB)
* Selected images listed with individual remove buttons before submit
* Image URLs included in the `{all_fields}` email template
* Uploaded images attached to notification emails
* Server-side validation: content-based MIME detection, extension whitelist, size limits
* Automatic cleanup of abandoned temporary uploads after 24 hours
* Compatible with themes using JCF-style form styling libraries

**How it works**

When a visitor selects images, each file is uploaded immediately to a temporary folder through a secured AJAX endpoint. Lightweight tokens travel with the form submission, and on success the files are moved into the standard WordPress uploads directory.

**Requirements**

* WPForms Lite or WPForms Pro

= Check Out My Other Plugins =

If you find Multimage helpful, feel free to check out my other free WordPress plugins:

* [Story Video Block](https://wordpress.org/plugins/story-video-block/) — Fast video player, YouTube/Vimeo embed & video testimonials for Gutenberg with click-to-play lazy loading.
* [Quovex Blocks](https://wordpress.org/plugins/quovex-blocks/) — Modern Gutenberg blocks: filterable Query Grid, Post Carousel, Magazine Content Showcase & Notification Banner.
* [Flash Sale Stock Guard for WooCommerce](https://wordpress.org/plugins/flash-sale-stock-guard-for-woocommerce/) — Prevent overselling during limited drops and flash sales by reserving stock at add-to-cart.
* [Very Simple Contact Us Form](https://wordpress.org/plugins/very-simple-contact-us-form/) — Clean, fast, zero-bloat contact form for any WordPress website.

== Installation ==

1. Upload the `multimage-image-uploads-for-wpforms` folder to the `/wp-content/plugins/` directory, or install the plugin through the WordPress "Add New Plugin" screen.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Edit or create a WPForms form and drag the **Multi Image Upload** field into the form from the Fields panel.
4. Configure the field's Max File Uploads, Allowed File Extensions, and Max File Size options in the field settings panel.
5. Save and publish the form.

== Frequently Asked Questions ==

= Does this work with WPForms Lite? =

Yes. That is the primary use case — Lite has no file upload field, and this plugin adds a multi-image one.

= Where are the uploaded images stored? =

In your standard WordPress uploads directory (wp-content/uploads/year/month), after a successful form submission. Before submission they are held in a temporary folder that is cleaned automatically.

= Can users upload non-image files? =

No. The upload endpoint enforces an image-only whitelist (jpg, jpeg, png, gif, webp) with content-based MIME detection, regardless of field settings.

= Does it work with AJAX form submission enabled? =

Yes. Images are pre-uploaded on selection, so the setting does not matter.

= How many images can a visitor upload? =

Whatever you set for Max File Uploads on that field, per form. Use separate Multi Image Upload fields with different limits if you need a photo field and a gallery field on the same form.

= Do the uploaded images appear in the WordPress Media Library? =

No. Images are saved to your uploads directory but are not added as Media Library attachments, so visitor submissions never clutter your library.

= Can I use this for a photo gallery or portfolio submission form? =

Yes. Any form that needs visitors to submit multiple photos — galleries, portfolios, applications, support requests — can use the Multi Image Upload field.

== Screenshots ==

1. The Multi Image Upload field on a live WPForms form, ready for visitors to attach images.
2. The Multi Image Upload field available in the WPForms builder's Standard Fields panel, added to a form.
3. Field settings: Max File Uploads, Allowed File Extensions, and Max File Size (MB).
4. General field settings: Label, Description, and Required toggle.

== Privacy ==

This plugin stores images uploaded by visitors on your server, in the WordPress uploads directory. Uploaded images may be attached to notification emails you configured in WPForms. No data is sent to any external service.

== Changelog ==

= 1.0.0 =
* Initial release.
