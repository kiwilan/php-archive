# PHP Archive

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kiwilan/php-archive.svg?style=flat-square)](https://packagist.org/packages/kiwilan/php-archive)
[![Tests](https://img.shields.io/github/actions/workflow/status/kiwilan/php-archive/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/kiwilan/php-archive/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/kiwilan/php-archive.svg?style=flat-square)](https://packagist.org/packages/kiwilan/php-archive)
[![codecov](https://codecov.io/gh/kiwilan/php-archive/branch/main/graph/badge.svg?token=P9XIK2KV9G)](https://codecov.io/gh/kiwilan/php-archive)

PHP package to read and extract files from archives like ZIP, RAR, TAR or PDF.

| Type | Native | PECL Extension |     Dependency     |
| :--: | :----: | :------------: | :----------------: |
| ZIP  |   ✅   |      N/A       |        N/A         |
| TAR  |   ❌   |      N/A       |        N/A         |
| RAR  |   ❌   |     `rar`      |        N/A         |
| PDF  |   ✅   |      N/A       | `smalot/pdfparser` |

-   <https://www.php.net/manual/en/refs.compression.php>
-   <https://github.com/splitbrain/php-archive>
-   <https://github.com/cmanley/PHP-SevenZipArchive>
-   <https://github.com/smalot/pdfparser>
-   <https://github.com/Gemorroj/Archive7z>

## Installation

You can install the package via composer:

```bash
composer require kiwilan/php-archive
```

## Usage

```php
$skeleton = new Kiwilan\Archive();
echo $skeleton->echoPhrase('Hello, Kiwilan!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [Kiwilan](https://github.com/kiwilan)
-   [All Contributors](../../contributors)
-   [Test eBook](https://deslivresencommuns.org/post/grisebouille/)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
