# Xgenious Installer
A Laravel package for installing xgenious script easily with a visual installer

## Installation

### Require this package with composer:

```shell
composer require xgenious/installer
```

### Publish the configuration file::

```shell
php artisan vendor:publish --provider="Xgenious\Installer\InstallerServiceProvider" --tag="config"
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