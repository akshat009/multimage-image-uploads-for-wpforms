=== Multimage – Multiple Image & Photo Upload for WPForms ===
Contributors: developerakshat
Tags: wpforms, image upload, photo upload, attachments, addon
Requires at least: 5.8
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add multiple image and photo uploads to WPForms Lite with instant AJAX uploads, custom limits, and email attachments. No Pro required.

== Description ==

= Video Walkthrough =

Watch the complete walkthrough and demo of Multimage:

https://www.youtube.com/watch?v=XG80kG8uRe0

Multimage is a lightweight multiple image upload addon for WPForms. It adds a dedicated **Multi Image Upload** field to the WPForms form builder — built specifically for **WPForms Lite** — letting visitors attach multiple photos, screenshots, and images to a single form submission without purchasing WPForms Pro.

By default, WPForms Lite does not include an image or photo upload field. Multimage solves this completely free, providing instant photo previews, secure AJAX uploads, and automatic email attachments.

> This is an independent, third-party add-on. It is not affiliated with, endorsed by, or sponsored by WPForms or Awesome Motive, Inc. "WPForms" is a trademark of its respective owner and is used here only to describe compatibility.

**Key Features**

* **Multi Image Upload Field** — easily drag and drop into any WPForms form from the Standard Fields panel
* **Built for WPForms Lite** — accept multiple image uploads on your forms without paying for a WPForms Pro license
* **Works with AJAX on or off** — images pre-upload instantly via AJAX on selection, ensuring zero data loss during form submission
* **Individual Remove Buttons** — visitors can preview selected photos and remove unwanted images before submitting
* **Custom Field Settings** — configure Button Text, Max File Uploads, Allowed File Extensions, and Max File Size (MB) directly in the builder
* **Email Attachments** — uploaded images are attached directly to WPForms notification emails
* **Email Template Integration** — image links are automatically included in the `{all_fields}` email tag
* **Strict Security & Validation** — server-side content-based MIME validation (images only), file extension whitelist, and size limit checks
* **Automatic Cleanup** — temporary uploads from abandoned form submissions are automatically deleted after 24 hours
* **Clean Media Library** — uploaded images are saved in your WordPress uploads folder without cluttering your WordPress Media Library

**How it works**

When a visitor selects photos or images, each file uploads immediately to a temporary directory through a secure AJAX endpoint. Lightweight security tokens travel with the form submission, and upon successful validation, images are moved into your standard WordPress uploads folder (`wp-content/uploads/year/month`).

**Requirements**

* WPForms Lite (or WPForms Pro)

= Check Out My Other Plugins =

If you find Multimage helpful, feel free to check out my other free WordPress plugins:

* [Flash Sale Stock Guard for WooCommerce](https://wordpress.org/plugins/flash-sale-stock-guard-for-woocommerce/) — Prevent overselling during limited drops and flash sales by reserving stock at add-to-cart.
* [Quovex Blocks](https://wordpress.org/plugins/quovex-blocks/) — Modern Gutenberg blocks: filterable Query Grid, Post Carousel, Magazine Content Showcase & Notification Banner.
* [Story Video Block](https://wordpress.org/plugins/story-video-block/) — Fast video player, YouTube/Vimeo embed & video testimonials for Gutenberg with click-to-play lazy loading.

== Installation ==

1. Upload the `multimage-image-uploads-for-wpforms` folder to the `/wp-content/plugins/` directory, or install directly through the WordPress "Add New Plugin" screen.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Open or create any form in the WPForms builder and drag the **Multi Image Upload** field into your form.
4. Customize your field settings in the Field Options panel:
   * **Button Text:** Custom label for the upload button (e.g., "Upload Images").
   * **Max File Uploads:** Maximum number of images allowed per submission (e.g., 5).
   * **Allowed File Extensions:** Comma-separated image extensions (`jpg, jpeg, png, webp, gif`).
   * **Max File Size (MB):** Maximum upload size per image.
5. Save and publish your form.

== Frequently Asked Questions ==

= Does this work with WPForms Lite? =

Yes! In fact, that is the primary reason Multimage was created. WPForms Lite does not provide an image upload field by default, and Multimage adds multiple image uploads completely free without requiring WPForms Pro.

= Can users upload non-image files like PDFs or documents? =

No. Multimage is specifically built and secured for photos and images only. The upload endpoint strictly enforces an image-only whitelist (jpg, jpeg, png, gif, webp) with content-based MIME detection, keeping your site secure and fast.

= Where are uploaded images stored? =

In your standard WordPress uploads directory (`wp-content/uploads/year/month`) after successful form submission. Before submission, temporary files are held in a secure temporary folder and automatically purged after 24 hours if abandoned.

= Does it work with AJAX form submission enabled? =

Yes. Images are pre-uploaded on selection, so it works reliably whether WPForms AJAX form submission is enabled or disabled.

= How many images can a visitor upload? =

You decide! You can configure the **Max File Uploads** setting for each Multi Image Upload field in your form. You can also use multiple fields with different limits on the same form (e.g., one field for a profile photo and another for receipt/screenshot uploads).

= Do uploaded images appear in the WordPress Media Library? =

No. Uploaded images are stored in your uploads directory and attached to notification emails, but they are not registered as Media Library attachments so your WordPress Media Library stays completely uncluttered.

= Can I use this for photo submissions, customer receipts, or support forms? =

Yes. Any contact form that requires visitors to upload multiple images — such as customer photos, screenshot attachments for support requests, job applications, or contest entries — can use the Multi Image Upload field.

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
