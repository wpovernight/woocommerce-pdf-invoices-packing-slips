=== Plugin Name ===
Contributors: pomegranate
Tags: woocommerce, print, pdf, bulk, packing slips, invoices, delivery notes, invoice, packing slip, export, email
Requires at least: 3.5 and WooCommerce 2.0
Tested up to: 4.0 and WooCommerce 2.2
Stable tag: 1.4.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create, print & automatically email PDF invoices & packing slips for WooCommerce orders.

== Description ==

This WooCommerce extension automatically adds a PDF invoice to the order confirmation emails sent out to your customers. Includes a basic template (additional templates are available from [WP Overnight](https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-premium-templates/)) as well as the possibility to modify/create your own templates. In addition, you can choose to download or print invoices and packing slips from the WooCommerce order admin.

= Main features =
* Automatically attach invoice PDF to order confirmation email
* Download the PDF invoice / packing slip from the order admin page
* Generate bulk PDF invoices / packings slips
* **Fully customizable** HTML/CSS invoice templates
* Users can download their invoices from the My Account page
* Sequential invoice numbers - with custom formatting
* **Available in: Czech, Dutch, English, French, German, Hungarian, Italian, Norwegian, Polish, Romanian, Russian, Slovak, Spanish, Swedish & Ukrainian**

Upload all invoices automatically to dropbox with our [WooCommerce PDF Invoices & Packing Slips to Dropbox](https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-premium-templates/) extension!

= Fully customizable =
In addition to a number of default settings (including a custom header/logo) and several layout fields that you can use out of the box, the plugin contains HTML/CSS based templates that allow for customization & full control over the PDF output. Copy the templates to your theme folder and you don't have to worry that your customizations will be overwritten when you update the plugin.

* Insert customer header image/logo
* Modify shop data / footer / disclaimer etc. on the invoices & packing slips
* Select paper size (Letter or A4)
* Translation ready

== Installation ==

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

= How do I create my own custom template? =

Copy the files from `wp-content/plugins/woocommerce-pdf-invoices-packing-slips/templates/pdf/Simple/` to `wp-content/themes/yourtheme/woocommerce/pdf/yourtemplate` and customize them there. The new template will show up as 'yourtemplate' (the folder name) in the settings panel.

= Where can I find more templates? =

Go to [wpovernight.com](https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-premium-templates/) to checkout more templates! These include templates with more tax details and product thumbnails. Need a custom templates? Contact us at support@wpovernight.com for more information.

= Can I contribute to the code? =
You're more than welcome! This plugin is hosted on github, where you can post issues or make pull requests.
https://github.com/wpovernight/woocommerce-pdf-invoices-packing-slips

= My language is not included, how can I contribute? =

This plugin is translation ready, which means that you can translate it using standard WordPress methods.

1. Download POEdit at (http://www.poedit.net/download.php)
2. Open POEdit
3. File > New from POT
4. Open wpo_wcpdf.pot (from `woocommerce-pdf-invoices-packing-slips/languages/`)
5. A popup will ask you for your language
6. This step is a bit tricky, configuring the plurals. Somehow the settings can't be copied from the pot. Go to Catalogue > Preferences. Then enter nplurals=2; plural=n != 1; in the custom expression field
7. Enter the translations. invoice and packing-slip now have two translation fields, single & plural. Note that this is a filename, so replace spaces with a - just to be sure!
8. Save as `wpo_wcpdf-xx_XX.po`, where you replace xx_XX with your language code & country code suffix (da_DK, pl_PL, de_DE etc.)


= How can I display the HTML/CSS source for debugging/developing templates? =
Add the following code to your theme's `functions.php`
`
add_filter( 'wpo_wcpdf_output_html', '__return_true' );
add_filter( 'wpo_wcpdf_use_path', '__return_false' );
`

= How can I display custom fields in the invoice or packing slip? =
First, you need to create a custom template following instructions from the first item in this FAQ.
Then place the following snippet where you would like the custom field to appear:

`
<?php $wpo_wcpdf->custom_field('custom_fieldname', 'Custom field:'); ?>
`

Where you replace 'custom_fieldname' with the name of the field you want to display, and 'Custom field' with the label. The plugin only displays the field when it contains data. If you also want to display the label when the field is empty, you can pass a third parameter (true), like this:

`
<?php $wpo_wcpdf->custom_field('custom_fieldname', 'Custom field:',  true); ?>
`

= How do can I modify the pdf filename? =

You can do this via a filter in your theme's `functions.php` (Some themes have a "custom functions" area in the settings).

For the export filename (from the woocommerce admin or the my account page):

`
add_filter( 'wpo_wcpdf_bulk_filename', 'my_pdf_bulk_filename', 10, 4 );
function my_pdf_bulk_filename( $filename, $order_ids, $template_name, $template_type ) {
	global $wpo_wcpdf;
	if (count($order_ids) == 1) {
		// single
		$invoice_number = $wpo_wcpdf->get_invoice_number();
		$filename = 'myshopname_' . $template_name . '-' . $invoice_number . '.pdf';
	} else {
		// multiple invoices/packing slips export
		// create your own rules/ be creative!
	}

	return $filename;
}
`

For the email attachment filename:
`
add_filter( 'wpo_wcpdf_attachment_filename', 'my_pdf_attachment_filename', 10, 3 );
function my_pdf_attachment_filename( $filename, $display_number, $order_id ) {
	//$display_number is either the order number or invoice number, according to your settings
	$filename = 'myshopname_invoice-' . $display_number . '.pdf';

	return $filename;
}
`

= Why does the download link not display on the My Account page? =
To prevent customers from prematurely creating invoices, the default setting is that a customer can only see/download an invoice from an order that already has an invoice - either created automatically for the email attachment, or manually by the shop manager. This means that ultimately the shop mananger determines whether an invoice is available to the customer. If you want to make the invoice available to everyone you can either of the following:

1. Change the email setting to attach invoices to processing and/or new order emails as well
2. Add a filter to your themes functions.php for greater control:

`
add_filter( 'wpo_wcpdf_myaccount_allowed_order_statuses', 'wpo_wcpdf_myaccount_allowed_order_statuses' );
function wpo_wcpdf_myaccount_allowed_order_statuses( $allowed_statuses ) {
	// Possible statuses : pending, failed, on-hold, processing, completed, refunded, cancelled
	$allowed_statuses = array ( 'processing', 'completed' );

	return $allowed_statuses;
}
`

= How can I get a copy of the invoice emailed to the shop manager? =
The easiest way to do this is to just tick the 'new order' box. However, this also means that an invoice will be created for all new orders, also the ones that are never completed.

Alternatively you can get a (BCC) copy of the completed order email by placing the following filter in your theme's `functions.php` (Some themes have a "custom functions" area in the settings)
Modify the name & email address to your own preferences, 

`
add_filter( 'woocommerce_email_headers', 'mycustom_headers_filter_function', 10, 2);

function mycustom_headers_filter_function( $headers, $object ) { 
	if ($object == 'customer_completed_order') { 
		$headers .= 'BCC: Your name <your@email.com>' . "\r\n"; //just repeat this line again to insert another email address in BCC
	}

	return $headers; 
}
`


= Fatal error: Allowed memory size of ######## bytes exhausted (tried to allocate ### bytes) =

This usually only happens on batch actions. PDF creation is a memory intensive job, especially if it includes several pages with images. Go to WooCommerce > System Status to check your WP Memory Limit. We recommend setting it to 128mb or more.

== Screenshots ==

1. General settings page
2. Template settings page
3. Simple invoice PDF
4. Simple packing slip PDF

== Changelog ==

= 1.4.8 =
* Translations: Added Finnish (Thanks Sami Mäkelä/Contrast.fi!)

= 1.4.7 =
* Fix: check if image file exists locally, fallback to url if not (CDN compatibility)
* Fix: make deleting invoice date possible
* Fix: correct tmp folder check on status page
* Translations: updated po/mo files
* Tweak: changed settings capability requirement to `manage_woocommerce` (was: `manage_options`)
* Tweak: better email attachment function
* Tweak: prevent footer overlap (Simple template)
* Tweak: fallback if `glob()` is not allowed on the server
* Tweak: better custom template instructions (reflects path to actual (child-)theme)

= 1.4.6 =
* HOTFIX: WooCommerce 2.2 compatibility fix
* Filter for PDF temp folder (wpo_wcpdf_tmp_path)

= 1.4.5 =
* Fix: Double date conversion for order date on invoice number filter (to avoid i18n date issues)
* Fix: Template selector reset after update
* Translations: added Norwegian (Thanks Aleksander!)

= 1.4.4 =
* Feature: Editable invoice date per order/invoice.
* Feature: HTML is now allowed in footer and other settings fields.
* Translations: Updated German (Thanks Nadine!)
* Fix: template paths are now saved relative to the site base path (ABSPATH) to prevent errors when moving to another server
* Tweak: Changed bulk action hook for better theme compatibility
* Tweak: Newlines in custom checkout fields

= 1.4.3 =
* Feature: Added function to call custom fields more easily (see FAQ)
* Feature: Change the my account button text via a filter (wpo_wcpdf_myaccount_button_text)
* Translations: Added Danish (Thanks Mads!)
* Tweak: only load PDF engine if it's not already loaded by another plugin

= 1.4.2 =
* Fix: Don't create invoice number when exporting packing slips
* Fix: Division by zero for 0 quantity items

= 1.4.1 =
* Translations: Added Polish (Thanks Mike!)
* Fix: Invoice number formatting notices in debug mode

= 1.4.0 =
* NEW Feature: Invoice numbers can now be given a prefix, suffix or padding on the settings page!
* Filter: `wpo_wcpdf_email_allowed_statuses` to attach pdf to custom order status emails
* Tweak: Sequential Order Numbers Pro compatibility
* Tweak: Filenames are now automatically sanitized to prevent issues with illegal characters

= 1.3.2 =
* Fix: error on wpo_wcpdf_email_attachment filter when $pdf_path not set

= 1.3.1 =
* Fix: invoice number was cleared when Order Actions were being used when an invoice number was not set
* Translations: Updated Slovak (Thanks Jozef!)
* Translations: Added Czech (Thanks CubiQ!)

= 1.3.0 =
* Feature: Added 'status' panel for better problem diagnosis
* Feature: Order & Cart, & total discounts can now be called separately with order_discount()
* Tweak: split create & get invoice number calls to prevent slow database calls from causing number skipping
* Translations: Added Romanian (Thanks Leonardo!)
* Translations: Added Slovak (Thanks Oleg!)

= 1.2.13 =
* Feature: added filter for custom email attachment condition (wpo_wcpdf_custom_email_condition)
* Fix: warning for tax implode

= 1.2.12 =
* Fix: hyperlink underline (was more like a strikethrough)

= 1.2.11 =
* Translations: Fixed German spelling error
* Translations: Updated Swedish (Thanks Fredrik!)

= 1.2.10 =
* Translations: Added Swedish (Thanks Jonathan!)
* Fix: Line-height issue (on some systems, the space between lines was very high)

= 1.2.9 =
* Fix: bug where 'standard' tax class would not display in some cases
* Fix: bug that caused the totals to jump for some font sizes
* Fix: WC2.1 deprecated totals function
* Fix: If multiple taxes were set up with the same name, only one would display (Simple template was not affected)

= 1.2.8 =
* Template: Body line-height defined to prevent character jumping with italic texts
* Fix: Open Sans now included in plugin package (fixes font issues for servers with allow_url_fopen disabled)

= 1.2.7 =
* Translations: POT, DE & NL updated
* Fix: Removed stray span tag in totals table

= 1.2.6 =
* Translations: Spanish update (thanks prepu!)
* Fix: More advanced checks to determine if a customer can download the invoice (including a status filter)

= 1.2.5 =
* Feature: Optional Invoice Number column for the orders listing
* Feature: Better support for international characters
* Translations: Added Russian & Ukrainian translation (thanks Oleg!)
* Translations: Updated Spanish (Thanks Manuel!) and Dutch translations & POT file
* Tweak: Memory limit notice
* Tweak: Filename name now includes invoice number (when configured in the settings)

= 1.2.4 =
* Feature: Set which type of emails you want to attach the invoice to

= 1.2.3 =
* Feature: Manually edit invoice number on the edit order screen
* Feature: Set the first (/next) invoice number on the settings screen
* Fix: Bug where invoice number would be generated twice due to slow database calls
* Fix: php strict warnings

= 1.2.2 =
* Feature: Simple template now uses Open Sans to include more international special characters
* Feature: Implemented filters for paper size & orientation ([read here](http://wordpress.org/support/topic/select-a5-paper-size-for-packing-slips?replies=5#post-5211129))
* Tweak: PDF engine updated (dompdf 0.6.0)
* Tweak: Download PDF link on the my account page is now only shown when an invoice is already created by the admin or automatically, to prevent unwanted invoice created (creating problems with european laws).

= 1.2.1 =
* Fix: shipping & fees functions didn't output correctly with the tax set to 'incl'

= 1.2.0 =
* Feature: Sequential invoice numbers (set upon invoice creation).
* Feature: Invoice date (set upon invoice creation).

= 1.1.6 =
* Feature: Hungarian translations added - thanks Joseph!
* Tweak: Better debug code.
* Tweak: Error reporting when templates not found.
* Fix: tax rate calculation for free items.

= 1.1.5 =
* Feature: German translations added - thanks Christian!
* Fix: dompdf 0.6.0 proved to be less stable, so switching back to beta3 for now.

= 1.1.4 =
* Fix: Template paths on windows servers were not properly saved (stripslashes), resulting in an empty output.

= 1.1.3 =
* Feature: PDF engine (dompdf) updated to 0.6.0 for better stability and utf-8 support.
* Tweak: Local server path is used for header image for better compatibility with server settings.
* Fix: several small bugs.

= 1.1.2 =
* Feature: Totals can now be called with simpler template functions
* Feature: Italian translations - thanks max66max!
* Tweak: improved memory performance

= 1.1.1 =
* Feature: French translations - thanks Guillaume!

= 1.1.0 =
* Feature: Fees can now also be called ex. VAT
* Feature: Invoices can now be downloaded from the My Account page
* Feature: Spanish translation & POT file included
* Fix: ternary statements that caused an error

= 1.0.1 =
* Tweak: Packing slip now displays shipping address instead of billing address
* Tweak: Variation data is now displayed by default

= 1.0.0 =
* First release
