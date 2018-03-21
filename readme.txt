=== WooCommerce PDF Invoices & Packing Slips ===
Contributors: pomegranate
Donate link: https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-bundle/
Tags: woocommerce, pdf, invoices, packing slips, print, delivery notes, invoice, packing slip, export, email, bulk, automatic
Requires at least: 3.5
Tested up to: 4.9
Requires PHP: 5.3
Stable tag: 2.1.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create, print & automatically email PDF invoices & packing slips for WooCommerce orders.

== Description ==

This WooCommerce extension automatically adds a PDF invoice to the order confirmation emails sent out to your customers. Includes a basic template (additional templates are available from [WP Overnight](https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-premium-templates/)) as well as the possibility to modify/create your own templates. In addition, you can choose to download or print invoices and packing slips from the WooCommerce order admin.

= Main features =
* Automatically attach invoice PDF to WooCommerce emails of your choice
* Download the PDF invoice / packing slip from the order admin page
* Generate PDF invoices / packings slips in bulk
* **Fully customizable** HTML/CSS invoice templates
* Download invoices from the My Account page
* Sequential invoice numbers - with custom formatting
* **Available in: Czech, Dutch, English, Finnish, French, German, Hungarian, Italian, Japanese (see FAQ for adding custom fonts!), Norwegian, Polish, Romanian, Russian, Slovak, Slovenian, Spanish, Swedish & Ukrainian**

In addition to this, we offer several premium extensions:

* Create/email PDF Proforma Invoices, Credit Notes (for Refunds), email Packing Slips & more with [WooCommerce PDF Invoices & Packing Slips Professional](https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-professional/)
* Upload all invoices automatically to Dropbox with [WooCommerce PDF Invoices & Packing Slips to Dropbox](https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-dropbox/)
* Automatically send new orders or packing slips to your printer, as soon as the customer orders! [WooCommerce Automatic Order Printing](https://www.simbahosting.co.uk/s3/product/woocommerce-automatic-order-printing/?affiliates=2) (from our partners at Simba Hosting)
* More advanced & stylish templates with [WooCommerce PDF Invoices & Packing Slips Premium Templates](https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-premium-templates/)

= Fully customizable =
In addition to a number of default settings (including a custom header/logo) and several layout fields that you can use out of the box, the plugin contains HTML/CSS based templates that allow for customization & full control over the PDF output. Copy the templates to your theme folder and you don't have to worry that your customizations will be overwritten when you update the plugin.

* Insert customer header image/logo
* Modify shop data / footer / disclaimer etc. on the invoices & packing slips
* Select paper size (Letter or A4)
* Translation ready

== Installation ==

= Minimum Requirements =

* WooCommerce 2.2 or later
* WordPress 3.5 or later

= Automatic installation =
Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't even need to leave your web browser. To do an automatic install of WooCommerce PDF Invoices & Packing Slips, log in to your WordPress admin panel, navigate to the Plugins menu and click Add New.

In the search field type "WooCommerce PDF Invoices & Packing Slips" and click Search Plugins. You can install it by simply clicking Install Now. After clicking that link you will be asked if you're sure you want to install the plugin. Click yes and WordPress will automatically complete the installation. After installation has finished, click the 'activate plugin' link.

= Manual installation via the WordPress interface =
1. Download the plugin zip file to your computer
2. Go to the WordPress admin panel menu Plugins > Add New
3. Choose upload
4. Upload the plugin zip file, the plugin will now be installed
5. After installation has finished, click the 'activate plugin' link

= Manual installation via FTP =
1. Download the plugin file to your computer and unzip it
2. Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installation's wp-content/plugins/ directory.
3. Activate the plugin from the Plugins menu within the WordPress admin.

== Frequently Asked Questions ==

= Where can I find the documentation? =

[WooCommerce PDF Invoices & Packing Slips documentation](http://docs.wpovernight.com/woocommerce-pdf-invoices-packing-slips/)

= It's not working! =

Check out our step by step diagnostic instructions here: https://wordpress.org/support/topic/read-this-first-9/





= Where can I find more templates? =

Go to [wpovernight.com](https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-premium-templates/) to checkout more templates! These include templates with more tax details and product thumbnails. Need a custom templates? Contact us at support@wpovernight.com for more information.

= Can I create/send a proforma invoice or a credit note? =
This is a feature of our Professional extension, which can be found at [wpovernight.com](https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-professional/)

= Can I contribute to the code? =
You're more than welcome! This plugin is hosted on github, where you can post issues or make pull requests.
https://github.com/wpovernight/woocommerce-pdf-invoices-packing-slips

= How can I display the HTML/CSS source for debugging/developing templates? =
There's a setting on the Status tab of the settings page that allows you to toggle HTML output. Don't forget to turn if off after you're done testing!


== Screenshots ==

1. Simple invoice PDF
2. Simple packing slip PDF
3. Quickly print individual invoices or packing slips from the order list
4. Print invoices or packing slips in bulk
5. Attach invoices to any WooCommerce email
6. Set shop name, address, header logo, etc.

== Changelog ==

= 2.1.6 =
* Fix: Extended currency symbol setting for WooCommerce Currency Switcher by realmag777
* Fix: Apply WooCommerce decimal settings to tax rates with decimals
* Tweak: Pass document object to `wpo_wcpdf_email_attachment` filter

= 2.1.5 =
* Feature: Filter for number store table (wpo_wcpdf_number_store_table_name)
* Fix: prevent accessing order properties as custom field/order meta
* Fix: prevent wrong application of wpo_wcpdf_filename filter
* Fix: Improved tax rate calculation fallback

= 2.1.4 =
* Fix: WooCommerce 3.3 action buttons
* Feature: Added row classes for WooCommerce Composite Products 

= 2.1.3 =
* Fix: Fatal PHP error on My Account page.

= 2.1.2 =
* Feature: New action wpo_wcpdf_init_document
* Fix: Use title getters for my-account and backend buttons
* Fix: Legacy Premium Templates reference
* Tweak: Skip documents overview in settings, default to invoice

= 2.1.1 =
* Fix: WooCommerce Order Status & Actions Manager emails compatibility
* Feature: sort orders by invoice number column
* Tweak: pass document object to title filters
* Tweak: use title getter in template files (instead of title string)

= 2.1.0 =
* Feature: WooCommerce Order Status & Actions Manager emails compatibility
* Fix: Better url fallback for images stored in cloud
* Update: dompdf library updated to 0.8.2 - DOMDocument parser set to default again

= 2.0.15 =
* Fix: Prevent saving invoice number/date from order details page when not edited

= 2.0.14 =
* Feature: Manually resend specific order emails in WooCommerce 3.2+
* Tweak: Show full size logo preview in settings
* Tweak: Custom field fallback to underscore prefixed meta key
* Dev: added `wpo_wcpdf_before_sequential_number_increment` action

= 2.0.13 =
* Fix: Minor XSS issue on settings screens by escaping and sanitizing 'tab' & 'section' GET variables. Discovered by Detectify.
* Fix: Pakistani Rupee Symbol
* Feature: Automatically enable extended currency symbol support for currencies not supported by Open Sans
* Dev: added `wpo_wcpdf_document_number_settings` filter

= 2.0.12 =
* Option: Use different HTML parser (debug settings)

= 2.0.11 =
* Fix: Improved fonts update routine (now preserves custom fonts)
* Fix: Enable HTML5 parser by default (fixes issues with libxml)
* Tweak: Show both PHP & WP Memory limit in Status tab

= 2.0.10 =
* Fix: Set invoice number backend button
* Fix: Thumbail paths
* Tweak: Make dompdf options filterable

= 2.0.9 =
* Feature: use `[invoice_date="ymd"]` in invoice number prefix or suffix to include a specific date format in the invoice number
* Fix: Postmeta table prefix for invoice counter
* Fix: 0% tax rates

= 2.0.8 =
* Feature: Add support for Bedrock / alternative folder structures
* Dev: Filter for merged documents
* Fix: Better attributes fallback for product variations 

= 2.0.7 =
* Feature: Added button to delete legacy settings
* Feature: Option to enable font subsetting
* Fix: Invoice number sequence for databases with alternative auto_increment_increment settings
* Fix: Fallback function for MB String (mb_stripos)

= 2.0.6 =
* Feature: Improved third party invoice number filters (`wpo_wcpdf_external_invoice_number_enabled` & `wpo_wcpdf_external_invoice_number`)
* Fix: Underline position for Open Sans font
* Fix: Invoice number auto_increment for servers that restarted frequently
* Fix: Dompdf log file location (preventing open base_dir notices breaking PDF header)
* Fix: 1.6.6 Settings migration duplicates merging
* Tweak: Clear fonts folder when manually reinstalling fonts

= 2.0.5 =
* Feature: Remove temporary files (Status tab)
* Fix: Page number replacement
* Tweak: Fallback functions for MB String extension
* Tweak: Improved wpo_wcpdf_check_privs usability for my account privileges
* Legacy support: added wc_price alias for format_price method in document

= 2.0.4 =
* Fix: Apply filters for custom invoice number formatting in document too
* Fix: Parent fallback for missing dates from refunds

= 2.0.3 =
* Fix: Better support for legacy invoice number filter (`wpo_wcpdf_invoice_number` -  replaced by `wpo_wcpdf_formatted_document_number`)
* Fix: Document number formatting fallback to order date if no document date available
* Fix: Updated classmap: PSR loading didn't work on some installations
* Fix: Prevent order notes from all orders showing when document is not loaded properly in filter
* Tweak: Disable deprecation notices during email sending
* Tweak: ignore outdated language packs

= 2.0.2 =
* Fix: order notes using correct order_id
* Fix: WC3.0 deprecation notice for currency
* Fix: Avoid crashing on PHP5.2 and older
* Fix: Only use PHP MB String when present
* Fix: Remote images
* Fix: Download option

= 2.0.1 =
* Fix: PHP 5.4 issue

= 2.0.0 =
* New: Better structured & more advanced settings for documents
* New: Option to enable & disable Packing Slips or Invoices
* New: Invoice number sequence stored separately for improved speed & performance
* New: Completely rewritten codebase for more flexibility & better reliability
* New: Updated PDF library to DOMPDF 0.8
* New: PDF Library made pluggable (by using the `wpo_wcpdf_pdf_maker` filter)
* New: lots of new functions & filters to allow developers to hook into the plugin
* Changed: **$wpo_wcpdf variable is now deprecated** (legacy mode available & automatically enabled on update)
* Fix: Improved PHP 7 & 7.1 support
* Fix: Positive prices for refunds
* Fix: Use parent for attributes retrieved for product variations
* Fix: Set content type to PDF for download

= 1.6.6 =
* Feature: Facilitate downgrading from 2.0 (re-installing fonts & resetting version)
* Fix: Update currencies font (added Georgian Lari)
* Translations: Added Indonesian

== Upgrade Notice ==

= 2.1.6 =
2.X is a BIG update! Make a full site backup before upgrading!