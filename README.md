# PHP Archive

## NOT AVAILABLE YET, WORK IN PROGRESS

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kiwilan/php-archive.svg?style=flat-square)](https://packagist.org/packages/kiwilan/php-archive)
[![Tests](https://img.shields.io/github/actions/workflow/status/kiwilan/php-archive/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/kiwilan/php-archive/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/kiwilan/php-archive.svg?style=flat-square)](https://packagist.org/packages/kiwilan/php-archive)
[![codecov](https://codecov.io/gh/kiwilan/php-archive/branch/main/graph/badge.svg?token=P9XIK2KV9G)](https://codecov.io/gh/kiwilan/php-archive)

PHP package to read and extract files from archives like ZIP, RAR, TAR or PDF with p7zip library. It works too with comic book archives like CBZ, CBR, CBT or CB7 or eBooks with EPUB format.

## About

This package was heavily inspired by [Gemorroj/Archive7z](https://github.com/Gemorroj/Archive7z) which is a wrapper is a wrapper of [p7zip-project/p7zip](https://github.com/p7zip-project/p7zip) a fork of `p7zip`. If you need to manage many archives, you should use `Gemorroj/Archive7z` instead. Current package is a wrapper of original `p7zip`, it's not powerful as `p7zip-project/p7zip` but easier to install.

Alternatives:

-   [Gemorroj/Archive7z](https://github.com/Gemorroj/Archive7z): handle many archives with [p7zip-project/p7zip](https://github.com/p7zip-project/p7zip) binary
-   [splitbrain/php-archive](https://github.com/splitbrain/php-archive): native PHP solution to handle `.zip` and `.tar` archives

### Why not use native PHP functions?

To handle `.zip` archives, it's easy with `ZipArchive` native class. But for other formats, it's really a pain. For `.rar` format, you need [PECL `rar`](https://github.com/cataphract/php-rar) extension which is not actively maintained. For `tar` format, you have many possibilities but it's really a pain to manage all of them, with `.gz`, `.bz2`, `.xz` and `.lzma` compression. And for `.7z` format with PHP, it's again a pain.

The binary `p7zip` is a really good solution to handle all of them. It's not a native PHP solution but it's easy to install on most of OS. This package is not an all-in-one solution but it's a good start to handle archives.

### What is the aim of this package?

I want to handle many archives to handle eBooks like `.epub` or `.cbz` for example. I need to scan files into these archives and extract some files with a good performance. I extended to `.tar` compression formats because it's really easy to handle with `p7zip`. I handle PDF metadata with `smalot/pdfparser` for eBooks which are PDF format.

### Really works on any system?

It designed to works with any system with `p7zip` installed. But for `macOS`, `p7zip` is not able to handle `.rar` extraction, you have to install third library `rar`.

## Requirements

-   PHP >= 8.1
-   `p7zip` binary, you can check [this guide](https://gist.github.com/ewilan-riviere/85d657f9283fa6af255531d97da5d71d)
-   Optional:
    -   `macOS` only: `rar` binary for `.rar` file extract method, you can check [this guide](https://gist.github.com/ewilan-riviere/85d657f9283fa6af255531d97da5d71d#macos)
    -   [`imagick` PECL extension](https://github.com/Imagick/imagick): for PDF `extract` method, you can check [this guide](https://gist.github.com/ewilan-riviere/3f4efd752905abe24fd1cd44412d9db9#imagemagick)

## Installation

You can install the package via composer:

```bash
composer require kiwilan/php-archive
```

## Usage

With archive file (`.zip`, `.rar`, `.tar`, `.7z`, `epub`, `cbz`, `cbr`, `cb7`, `cbt`, `tar.gz`)

```php
$archive = Archive::make('path/to/archive.zip');

$files = $archive->files(); // ArchiveItem[]
$count = $archive->count(); // int
$content = $archive->extractFile('archive/cover.jpeg'); // string
$images = $archive->findAll('jpeg'); // ArchiveItem[]
$specificFile = $archive->find('metadata.xml'); // ArchiveItem|null
```

With PDF file

```php
$pdf = ArchivePdf::make('path/to/file.pdf');

$files = $archive->metadata(); // PdfMetadata
$count = $archive->count(); // int
$content = $archive->extract(index: 1, format: 'png', isBase64: true ); // PDF page index 1 as PNG base64 encoded (ImageMagick required)
$text = $archive->text(); // PDF text content
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
-   [`smalot/pdfparser`](https://github.com/smalot/pdfparser)
-   [`7-zip`](https://www.7-zip.org/)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
