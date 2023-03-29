# PHP Archive

[![php][php-version-src]][php-version-href]
[![version][version-src]][version-href]
[![downloads][downloads-src]][downloads-href]
[![license][license-src]][license-href]

[![tests][tests-src]][tests-href]
[![codecov][codecov-src]][codecov-href]

PHP package to handle archives (`.zip`, `.rar`, `.tar`, `.7z`) or `.pdf` with hybrid solution (native and with `p7zip` binary), designed to works with eBooks (`.epub`, `.cbz`, `.cbr`, `.cb7`, `.cbt`).

## Installation

You can install the package via composer:

```bash
composer require kiwilan/php-archive
```

## Usage

### Read and extract

With archive file (`.zip`, `.rar`, `.tar`, `.7z`, `epub`, `cbz`, `cbr`, `cb7`, `cbt`, `tar.gz`)

```php
$archive = Archive::test('path/to/archive.zip');

$files = $archive->files(); // ArchiveItem[]
$count = $archive->count(); // int of files count

$images = $archive->findAll('jpeg'); // ArchiveItem[] with `jpeg` extension
$metadataXml = $archive->find('metadata.xml'); // ArchiveItem of `metadata.xml` file if exists
$content = $archive->content($metadataXml); // string of `metadata.xml` file content

$paths = $archive->extract('/path/to/directory', [$metadataXml]); // string[] of extracted files paths
$paths = $archive->extractAll('/path/to/directory'); // string[] of extracted files paths

$metadata = $archive->metadata(); // Metadata of archive, useful with PDF files
```

### Create

Works only with `.zip` archives.

```php
$archive = Archive::create('path/to/archive.zip');
$archive->addFiles([
    'path/to/file1.txt',
    'path/to/file2.txt',
    'path/to/file3.txt',
]);
$archive->addFromString('test.txt', 'Hello World!');
$archive->addDirectory('path/to/directory');
$archive->save();
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
-   [spatie](https://github.com/spatie) for `spatie/package-skeleton-php`
-   [`smalot/pdfparser`](https://github.com/smalot/pdfparser) for PDF parser
-   [`7-zip`](https://www.7-zip.org/) for `p7zip` binary

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[version-src]: https://img.shields.io/packagist/v/kiwilan/php-archive.svg?style=flat-square&colorA=18181B&colorB=777BB4
[version-href]: https://packagist.org/packages/kiwilan/steward-laravel
[php-version-src]: https://img.shields.io/static/v1?style=flat-square&label=PHP&message=v8.1&color=777BB4&logo=php&logoColor=ffffff&labelColor=18181b
[php-version-href]: https://www.php.net/
[downloads-src]: https://img.shields.io/packagist/dt/kiwilan/php-archive.svg?style=flat-square&colorA=18181B&colorB=777BB4
[downloads-href]: https://packagist.org/packages/kiwilan/php-archive
[license-src]: https://img.shields.io/github/license/kiwilan/php-archive.svg?style=flat-square&colorA=18181B&colorB=777BB4
[license-href]: https://github.com/kiwilan/php-archive/blob/main/README.md
[tests-src]: https://img.shields.io/github/actions/workflow/status/kiwilan/php-archive/run-tests.yml?branch=main&label=tests&style=flat-square&colorA=18181B
[tests-href]: https://packagist.org/packages/kiwilan/php-archive
[codecov-src]: https://codecov.io/gh/kiwilan/php-archive/branch/main/graph/badge.svg?token=P9XIK2KV9G
[codecov-href]: https://codecov.io/gh/kiwilan/php-archive
