# Xgenious Installer
A Laravel package for installing xgenious script easily with a visual installer
<img width="941" alt="image" src="https://github.com/xgenious-official/xgenious-installer/assets/28456389/b9877021-ee8a-456d-9428-e19949f9cf6a">


## Installation

### Require this package with composer:

```shell
composer require xgenious/installer
```

### Publish the configuration file::

```shell
php artisan vendor:publish --provider="Xgenious\Installer\InstallerServiceProvider" --tag="config"
```

### Add Midleware in ```app\Http\Kernel.php``` fle
``
\Xgenious\Installer\Http\Middleware\InstallerMiddleware::class
``
```php

example
  protected $middleware = [
        /* Laravel defult middleware */
        \Xgenious\Installer\Http\Middleware\InstallerMiddleware::class
    ];
````

### Config Value Explanation

```php
# config/installer.php

return [
    'app_name' => 'Fundorex', //app name 
    'super_admin_role_id' => 3, // super admin role id
    'admin_model' => \App\Admin::class, //admin modal 
    'admin_table' => 'admins', //admin table
    'multi_tenant' => false,
    'author' => 'xgenious', // envato author username
    'product_key' => '8de1f072836b127749b7aa2b575ffc0002ade20e', //product key from xgenious license server
    'php_version' => '8.1', //minimum required php version
    'extensions' => ['BCMath', 'Ctype', 'JSON', 'Mbstring', 'OpenSSL', 'PDO', 'pdo_mysql', 'Tokenizer', 'XML', 'cURL', 'fileinfo'], //required php extensions
    'website' => 'https://xgenious.com', //author website url
    'email' => 'support@xgenious.com', //support url
    'env_example_path' => public_path('env-sample.txt'), //env-sample.txt file locaation, env will be generate based on this file contenant
    'broadcast_driver' => 'log', // default config value 
    'cache_driver' => 'file', // default config value 
    'queue_connection' => 'sync', // default config value 
    'mail_port' => '587', // default config value 
    'mail_encryption' => 'tls', // default config value 
    'model_has_roles' => true,
    'bundle_pack' => false, //if the product has bundle pack
    'bundle_pack_key' => 'dsfasd', //bundle pack product key
];
```

## Migrate from old Installer
remove ``install`` folder, and remove ``install`` folder redirection from the root ``index.php ``file

### Minimal ``.env`` File Required
here is example of minimal ``.env`` file.

```php
APP_NAME=Fundorex
APP_ENV=production
APP_KEY=base64:8e5wSFpua5CzuHhXJEaJHcpRFBR2nqLAV0zTURuXgLA=
APP_DEBUG=false
APP_URL=http://fundorex.test/
```

## Running Tests
To run the test suite for this package, follow these steps:

Ensure you have the package and its dependencies installed:
```shell
composer install
```

Copy the package's phpunit.xml.dist file to phpunit.xml:
```shell
cp phpunit.xml.dist phpunit.xml
```

Run the tests using PHPUnit:
```shell
./vendor/bin/phpunit
```
Or, if you've set up the Composer script, you can use:
```shell
composer test
```

For a coverage report, run:

```shell
./vendor/bin/phpunit --coverage-html coverage
```

This will generate an HTML coverage report in the coverage directory.

#### Notes:

The tests use an in-memory SQLite database by default. If you need to use a different database for testing, update the ``phpunit.xml`` file accordingly.

Some tests may require specific environment variables to be set. Check the ``phpunit.xml`` file and set any necessary variables in your local environment or in the ``phpunit.xml`` file.

If you encounter any issues running the tests, ensure that all dependencies are properly installed and that your PHP environment meets the package requirements.

#### Writing New Tests
When adding new features or fixing bugs, please add corresponding test cases. Place new test files in the ``tests`` directory, following the existing structure:

Unit tests go in ``tests/Unit``
Feature tests go in ``tests/Feature``

Ensure that your test class extends
`` Xgenious\Installer\Tests\TestCase.``

If you need to add new test dependencies, add them to the ``require-dev`` section of the ``composer.json`` file.


## Usages

When the .env file is not found in your Laravel application, this package will automatically display the installation wizard.
