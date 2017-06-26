=== WooCommerce PDF Invoices & Packing Slips ===
Contributors: pomegranate
Donate link: https://wpovernight.com/
Tags: woocommerce, pdf, invoices, packing slips, print, delivery notes, invoice, packing slip, export, email, bulk, automatic
Requires at least: 3.5
Tested up to: 4.8
Stable tag: 1.6.6
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

* WooCommerce 2.0 or later
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

1. General settings page
2. Template settings page
3. Simple invoice PDF
4. Simple packing slip PDF

== Changelog ==

= 1.6.6 =
* **This is the last release before our [upcoming 2.0 update](https://wpovernight.com/2017/06/woocomerce-pdf-invoice-2-0-beta-testers-wanted/)**
* Feature: Facilitate downgrading from 2.0 (re-installing fonts & resetting version)
* Fix: Update currencies font (added Georgian Lari)
* Translations: Added Indonesian

= 1.6.5 =
* Fix: Duplicate invoice numbers when bulk completing orders (WC3.0)
* Fix: Hidden Invoice date when order refunded

= 1.6.4 =
* Fix: My account invoice button visibility

= 1.6.3 =
* Fix: Empty date handling
* Fix: Shipping notes on refunds (reason for refund)

= 1.6.2 =
* Fix: TM Extra Product Options compatibility (in WC3.0)
* Fix: Tax display in WC3.0

= 1.6.1 =
* Fix: Error with totals in credit notes
* Fix: Always set invoice date when invoice is create (even display is disabled in the settings)

= 1.6.0.2 =
* Fix: Don't crash with PHP 5.2 or older (5.3 or higher required, 5.6 or higher recommended)

= 1.6.0 =
* WooCommerce 3.0 Compatible
* **Requires PHP version 5.3 or higher**
* Fix: Invoice number display in mobile view
* Fix: Update formatted invoice number in order meta when number is altered
* Fix: global plugin object loading in wrapped cron methods
* Tweak: Avoid PHP7 scan false positives in DomPDF

= 1.5.39 =
* Feature: new template action hooks `wpo_wcpdf_before_document` & `wpo_wcpdf_after_document`
* Tweak: In totals, emphasize order total rather than last item
* Fix: User deprecation notices
* Translations: Updated Slovenian

= 1.5.38 =
* Fix: Thumbnail path fallback
* Fix: Edge/IE hour & minute pattern
* Fix: Skip over non-order objects
* Tweak: Let shop manager view My Account links
* Dev: added `wpo_wcpdf_before_attachment_creation` action
* Translations: Updated POT, Swedish, Dutch & Norwegian

= 1.5.37 =
* Feature: Added support for third party invoice numbers
* Feature: Enable pre-invoice number click-to-edit
* Fix: Review link for custom admins
* Fix: PHP7 compatibility
* Fix: Invoice date hour/minute pattern
* Tweak: Multisite WooCommerce check optimization

= 1.5.36 =
* Translations: Fixed Romanian (incorrect "Factură Proforma" translation for "Invoice")

= 1.5.35 =
* Translations: Fixed "Includes %s" string for WC2.6+

= 1.5.34 = 
* Fix: Document check that was introduced in 1.5.33 for disable free setting

= 1.5.33 =
* Tweak: Don't apply 'disable free' setting to packing slip attachment
* Translations: Updated Romanian

= 1.5.32 =
* Fix: Updated currency font with Indian Rupee symbol
* Translations: added Formal German (currently a copy of informal German)

= 1.5.31 =
* Feature: [invoice_day] or [order_day] in invoice number format
* Fix: Link to hide all ads when premium extensions active

= 1.5.30 =
* Feature: Enable currency font for extended currency support
* Fix: Font sync on plugin update

= 1.5.29 =
* Translations: Added Croation (Thanks Neven/Spine ICT!), updated French (Thanks Sabra!)
* Tweak: filter shop address before checking if it's empty
* Dev: added $order to `wpo_wcpdf_template_file` filter

= 1.5.28 =
* Tweak: the 'Next invoice number' is now stored separately in the database for faster and more reliable retrieval. Circumventing any caching, this should prevent duplicate invoice numbers.
* Fix: Bulk actions plugin conflicts
* Experimental: page numbers (use {{PAGE_NUM}} / {{PAGE_COUNT}} in your template)

= 1.5.27 =
* Feature: Use [invoice_year] and [invoice_month] placeholders in invoice number prefix/suffix
* Feature: WooCommerce Order Status & Actions Manager emails compatibility
* Feature: Add invoice number to WC REST API
* Fix: Allow positive 'discounts' (price corrections)
* Fix: Discounts rounding
* Translations: Updated Finnish & Portugese & POT

= 1.5.26 =
* Feature: Automatically list all emails registered in WooCommerce
* Feature: Reset invoice number yearly
* Feature: WooCommerce Chained Products compatibility
* Feature: WooCommerce Product Bundles visibility settings taken into account in invoice
* Fix: Disable PDF creation from trashed order_ids
* Tweak: Alert when no orders selected for bulk export (Props to Dartui!)
* Tweak: PDF invoice settings always under WooCommerce menu (also for premium users)
* Tweak: extra $item_id passed in row class filter
* Translations: Updated Slovenian, Spanish, Dutch & POT file

= 1.5.24 =
* Hotfix: Subscriptions renewal filter arguments

= 1.5.23 =
* Fix: WooCommerce Subscriptons 2.0 deprecation notice.
* Tweak: better qTranslate-X support
* Tweak: filter for user privileges check (wpo_wcpdf_check_privs)
* Translations: French translations fix

= 1.5.22 =
* Fix: Workaround for bug in WPML (which cleared all settings)
* Translation: fixed Polish translation for invoice

= 1.5.21 =
* Translations: Added Estionan (thanks Tanel!)
* Tweak: WC2.4 compatibility

= 1.5.20 =
* Feature: Option to 'never' display My Account invoice link
* Fix: Order total for refunds in WC2.4
* Fix: notice when no custom statuses selected for My Account display
* Tweak: Product bundles styles

= 1.5.19 =
* Fix: Invoice number search (broke other custom searches)

= 1.5.18 =
* Fix: wpo_wcpdf_item_row_class packing slip filter arguments

= 1.5.17 =
* Feature: WooCommerce Product Bundles compatibility styles
* Tweak: wpo_wcpdf_item_row_class as filter instead of action

= 1.5.16 =
* Feature: Search orders by invoice number (note: search on formatted invoice number only works for new orders)
* Feature: Formatted invoice number stored in order
* Tweak: Function parameters added to some of the filters
* Tweak: WooCommerce 2.4 compatibility
* Dev feature: action to add class to items table row (wpo_wcpdf_item_row_class)
* Translations: Swedish updated (thanks Conney!)
* Translations: Norwegian updated

= 1.5.15 =
* Fix: invoice number padding didn't work for values lower than 3
* Tweak: WPML compatibility filter
* Translations: Updated French (Thanks Nicolas!)

= 1.5.14 =
* Tweak: Invoice number & date edit fields moved to separate box on order edit page
* Translations: Updated POT & Dutch

= 1.5.13 =
* Fix: Better address comparison to determine when to display alternate address
* Tweak: Filter N/A addresses
* Tweak: Use WooCommerce function for 2.3 discounts
* Translations: Czech Updated (Thanks Ivo!)
* Translations: French (minor fixes)

= 1.5.12 =
* Translations: added Danish, Updated POT & Italian

= 1.5.11 =
* Fix: Product text attributes (now checks key too)
* Fix: Status page upload explanation typos

= 1.5.10 =
* Fix: Double check to make sure plugin doesn't attach to user emails

= 1.5.9 =
* Feature: Shorthand function to display product attributes: `$wpo_wcpdf->get_product_attribute( $attribute_name, $product )`

= 1.5.8 =
* Feature: disable invoice for free orders
* Feature: action to insert data before & after item meta
* Tweak: Added classes to sku & weight
* Tweak: Hide payment method from totals (already shown in template)
* Translations: Updated POT & Dutch

= 1.5.7 =
* Feature: Setting to show email address & phone number on invoice or packing slip (does not work on custom templates based on previous versions!)

= 1.5.6 =
* Feature: Setting to show shipping address on invoice (does not work on custom templates based on previous versions!)
* Feature: My Account invoice download setting
* Feature: several new template actions
* Tweak: WooCommerce Bookings compatibility
* Tweak: Gerenal stylesheet cleanup
* Fix: temp path check/error on settings page
* Fix: Document titles for credit notes and proforma (Pro)
* Fix: Discount including tax
* Fix: Special characters on item meta (requires WooCommerce 2.3.6)
* Translations: Missing text domain on several strings
* Translations: Updated POT & Dutch

= 1.5.5 =
* Fix: Check for incomplete line tax data (Subscriptions compatibility)
* Fix: More precise template path instructions
* Fix: duplicate stylesheet filter
* Fix: Always prefer original order's billing address for refunds (WooCommerce EU VAT Number compatibility)
* Translations: Updated German (MwSt. instead of formal Ust.)
* Translations: Updated Dutch

= 1.5.4 =
* Tweak: include plugin version in style/script includes
* Tweak: upload code cleanup
* Fix: Parent invoice number (for Credit Notes in professional extension)

= 1.5.3 =
* Feature: add original order date value to order date filter
* Feature: Work with line_tax_data when available
* Feature: pass item_id to items
* Tweak: later check for woocommerce active
* Fix: do not try to validate empty settings (Status page settings)
* Translations: Fixed Dutch typo

= 1.5.2 =
* Fix: fatal error when trying to activate with WooCommerce not installed yet.
* Tweak: indentation fix on the Simple template

= 1.5.1 =
* Fix: prevent errors when upgrading

= 1.5.0 =
* Feature: All temporary files are now stored centrally in the WP uploads folder.
* Feature: Debug settings in status panel (output errors & output to HTML)
* Feature: Compatibility filter for WooCommerce Subscriptions (prevents duplicate invoice numbers)
* Tweak: Pass order to totals filters
* Translations: Updated POT
* Translations: Updated Italian (Thanks Astrid!)
* Translations: Updated Dutch
* FAQ: instructions for placing a link on the thank you page

= 1.4.14 =
* Fix: fatal error when user registers at checkout (applies to credit notes only)
* Translations: Updated German (Thanks Dietmar!)
* Translations: Place your custom translations in wp-content/languages/woocommerce-pdf-invoices-packing-slips/wpo_wcpdf-LOCALE.mo to protect them from being overwritten by plugin updates.

= 1.4.13 =
* Feature: use separate file for all your template specific functions (template-functions.php)
* Translations: Added Slovenian (thanks gregy1403!)
* Translations: Updated Norwegian & Dutch.
* Translations: Added Japanese - needs custom font!
* FAQ: instructions on how to use custom fonts

= 1.4.12 =
* Fix: issues with post parent objects (WC2.1 and older)

= 1.4.11 =
* Small fix: bulk actions for specific i18n configurations
* Tweak: total row key used as class in Simple template

= 1.4.10 =
* Fix: Invoice not attaching
* Translations: Updated POT file
* Translations: Updated Dutch, Norwegian, Polish, Brazilian Portugese, Romanian, Russian, Slovak, Spanish & Ukrainian (Many thanks to all the translators!)
* Templates: added action hooks for easier customizations (`wpo_wcpdf_before_order_details`,  `wpo_wcpdf_after_order_details`, `wpo_wcpdf_invoice_title` & `wpo_wcpdf_packing_slip_title`)

= 1.4.9 =
* Feature: Order number and date are now displayed by default in the Simple template (invoice number and date still optional)
* Feature: Display Customer/Order notes with a simple shorthand (see FAQ)
* Translations: Added Brazilian Portugese (thanks Victor Debone!)
* Tweak: Fail more gracefully when there are errors during PDF generation
* Tweak: added template type class to template output body
* Tweak: cleaned up Simple template style.css

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
* Feature: Invoice numbers can now be given a prefix, suffix or padding on the settings page!
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

== Upgrade Notice ==

= 1.6.6 =
Important: Version 1.6 requires PHP 5.3 or higher to run!
