# Instructions to Install and Update Composer Libraries with Strauss for PDF Invoices & Packing Slips for WooCommerce

This guide provides detailed steps to correctly install and update Composer libraries in the [PDF Invoices & Packing Slips for WooCommerce](https://github.com/wpovernight/woocommerce-pdf-invoices-packing-slips) plugin using [Strauss](https://github.com/BrianHenryIE/strauss) for class prefixing.

## Prerequisites

- PHP installed on your system.
- Composer installed on your system.
- `strauss.phar` file located in the plugin root directory.

## Steps to Install Composer Libraries

1. **Install Composer Dependencies**

   First, ensure all your dependencies are installed using Composer:

   ```sh
   composer install
   ```

2. **Run Strauss to Apply Class Prefixing**

   Use the Strauss PHAR file to prefix the classes as configured in your `composer.json`:

   ```sh
   php strauss.phar
   ```

3. **Do Not Run `composer dump-autoload` After Strauss**

   Avoid running `composer dump-autoload` after running Strauss, as it will regenerate the autoload files and potentially undo the prefixing work done by Strauss.

## Steps to Update Composer Libraries

1. **Update Composer Dependencies**

   Use Composer to update your dependencies:

   ```sh
   composer update
   ```

2. **Run Strauss to Apply Class Prefixing**

   After updating the dependencies, run Strauss again to apply the necessary class prefixing:

   ```sh
   php strauss.phar
   ```

3. **Do Not Run `composer dump-autoload` After Strauss**

   Similar to the installation step, do not run `composer dump-autoload` after running Strauss.

## Example `composer.json` Configuration

Ensure your `composer.json` is configured correctly for Strauss. Below is an example configuration:

```json
{
	"name": "wpovernight/woocommerce-pdf-invoices-packing-slips",
	"description": "PDF Invoices & Packing Slips for WooCommerce",
	"autoload": {
		"psr-4": {
			"WPO\\IPS\\": "includes/",
			"WPO\\IPS\\UBL\\": "ubl/"
		},
		"classmap": [
			"vendor/"
		]
	},
	"require": {
		"dompdf/dompdf": "^3.0",
		"symfony/polyfill-mbstring": "^1.27",
		"symfony/polyfill-iconv": "^1.27",
		"sabre/xml": "^2.2.5"
	},
	"extra": {
		"strauss": {
			"target_directory": "vendor",
			"namespace_prefix": "WPO\\IPS\\Vendor\\",
			"classmap_prefix": "WPO_IPS_Vendor_",
			"constant_prefix": "WPO_IPS_VENDOR_",
			"packages": [],
			"update_call_sites": false,
			"override_autoload": {},
			"exclude_from_copy": {
				"packages": [],
				"namespaces": [],
				"file_patterns": [
					"/^psr.*$/"
				]
			},
			"exclude_from_prefix": {
				"packages": [
					"symfony/polyfill-mbstring",
					"symfony/polyfill-iconv",
					"masterminds/html5"
				],
				"namespaces": [],
				"file_patterns": []
			},
			"namespace_replacement_patterns": {},
			"delete_vendor_packages": false,
			"delete_vendor_files": false
		}
	},
	"config": {
		"platform-check": false
	}
}

```
