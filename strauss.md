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

3. **Regenerate the Autoloader**

   Since Strauss moves dependencies, you must regenerate the autoloader to ensure everything is correctly registered:

   ```sh
   composer dump-autoload -o
   ```

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

3. **Regenerate the Autoloader**

   Since Strauss deletes the original files when copying them, you must run:

   ```sh
   composer dump-autoload -o
   ```

## Additional Manual Fixes

Some files require manual adjustments to ensure proper prefixing when using Strauss. Below are the necessary modifications:

### **File:** `vendor/strauss/dompdf/php-font-lib/src/FontLib/Font.php`
- **Line 62:**
  - **Before:** `$class = "FontLib\$class";`
  - **After:** `$class = "WPO\IPS\Vendor\FontLib\$class";`

### **File:** `vendor/strauss/dompdf/php-font-lib/src/FontLib/TrueType/File.php`
- **Line 385:**
  - **Before:** `return $class_parts[1];`
  - **After:** `return $class_parts[4];`

- **Line 401:**
  - **Before:** `$class = "FontLib\$type\TableDirectoryEntry";`
  - **After:** `$class = "WPO\IPS\Vendor\FontLib\$type\TableDirectoryEntry";`

For more details, see the related commit: [61bf71cb90](https://github.com/wpovernight/woocommerce-pdf-invoices-packing-slips/pull/1091/commits/61bf71cb90f71c2dbd1c80b3441599821ab009bd)

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
		}
	},
	"require": {
		"dompdf/dompdf": "^3.1",
		"symfony/polyfill-mbstring": "^1.31",
		"symfony/polyfill-iconv": "^1.31",
		"sabre/xml": "^4.0"
	},
	"extra": {
		"strauss": {
			"target_directory": "vendor/strauss",
			"namespace_prefix": "WPO\\IPS\\Vendor\\",
			"classmap_prefix": "WPO_IPS_Vendor_",
			"constant_prefix": "WPO_IPS_VENDOR_",
			"packages": [
				"dompdf/dompdf",
				"sabberworm/php-css-parser",
				"sabre/xml",
				"sabre/uri",
				"masterminds/html5"
			],
			"update_call_sites": true,
			"override_autoload": {
				"dompdf/dompdf": {
					"classmap": ["."]
				},
				"dompdf/php-font-lib": {
					"classmap": ["."]
				},
				"dompdf/php-svg-lib": {
					"classmap": ["."]
				},
				"masterminds/html5": {
					"classmap": ["."]
				},
				"sabberworm/php-css-parser": {
					"classmap": ["."]
				},
				"sabre/uri": {
					"classmap": ["."]
				},
				"sabre/xml": {
					"classmap": ["."]
				}
			},
			"exclude_from_copy": {
				"packages": [],
				"namespaces": [],
				"file_patterns": []
			},
			"exclude_from_prefix": {
				"packages": [
					"symfony/polyfill-mbstring",
					"symfony/polyfill-iconv"
				],
				"namespaces": [],
				"file_patterns": []
			},
			"namespace_replacement_patterns": {},
			"delete_vendor_packages": true,
			"delete_vendor_files": true
		}
	},
	"config": {
		"platform-check": false
	}
}
```

Now, since we use the Strauss autoloader, always follow the updated installation and update steps to ensure prefixed dependencies load correctly.

