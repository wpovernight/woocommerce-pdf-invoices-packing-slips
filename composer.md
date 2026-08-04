# Install the plugin via Composer

Create a `composer.json` file in the root of your WordPress project:

```json
{
	"name": "alex/my-project",
	"require": {
		"composer/installers": "^2.0",
		"wpovernight/woocommerce-pdf-invoices-packing-slips": "^5.9"
	},
	"extra": {
		"installer-paths": {
			"wp-content/plugins/{$name}/": [
				"type:wordpress-plugin"
			]
		}
	},
	"config": {
		"allow-plugins": {
			"composer/installers": true
		}
	}
}
````

Run:

```bash
composer install
```

Or, if the project already has Composer set up:

```bash
composer require composer/installers:^2.0
composer require wpovernight/woocommerce-pdf-invoices-packing-slips:^5.9
```

The plugin will be installed into:

```bash
wp-content/plugins/woocommerce-pdf-invoices-packing-slips
```
