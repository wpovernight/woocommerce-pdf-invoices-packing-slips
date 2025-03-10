# PHPStan

This document provides guidelines for configuring and resolving common issues with PHPStan in this project.

---

## **Fix: Invalid Identifier Name `'./'` in `bootstrap.php`**

### **Issue**
PHPStan fails with the following error due to the `bootstrap.php` file in `vendor/`:

```
Internal error: Invalid identifier name "./"
```

This happens because the file defines critical WordPress paths using `"./"`, which PHPStan does not interpret correctly.

---

### **Solution**
Since this is a **dev-only change** and is not pushed to the repository, we can safely **modify the vendor file directly**.

#### **1️⃣ Locate the `bootstrap.php` File**
Open the file at:

```
vendor/szepeviktor/phpstan-wordpress/bootstrap.php
```

---

#### **2️⃣ Fix `'./'` Paths**
Replace all instances of `'./'` with `__DIR__` to ensure absolute paths.

##### **🔴 Original Code (Incorrect)**
```php
define('ABSPATH', './');
define('WP_PLUGIN_DIR', './');
define('WPMU_PLUGIN_DIR', './');
define('WP_LANG_DIR', './');
define('WP_CONTENT_DIR', './');
```

##### **🟢 Fixed Code (Correct)**
```php
define('ABSPATH', __DIR__);
define('WP_PLUGIN_DIR', __DIR__);
define('WPMU_PLUGIN_DIR', __DIR__);
define('WP_LANG_DIR', __DIR__);
define('WP_CONTENT_DIR', __DIR__);
```

---

#### **3️⃣ Clear PHPStan Cache**
After making the changes, clear PHPStan's cache:

```sh
vendor/bin/phpstan clear-result-cache
```

---

#### **4️⃣ Run PHPStan Again**
Test the fix by running:

```sh
composer analyze
```

---

## **Notes**

Since `vendor/` is **not committed to Git**, these changes will be lost when running `composer install` or `composer update`. If necessary, **repeat these steps** after updating dependencies.