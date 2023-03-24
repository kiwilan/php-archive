# PHP Archive

## NOT AVAILABLE YET, WORK IN PROGRESS

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kiwilan/php-archive.svg?style=flat-square)](https://packagist.org/packages/kiwilan/php-archive)
[![Tests](https://img.shields.io/github/actions/workflow/status/kiwilan/php-archive/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/kiwilan/php-archive/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/kiwilan/php-archive.svg?style=flat-square)](https://packagist.org/packages/kiwilan/php-archive)
[![codecov](https://codecov.io/gh/kiwilan/php-archive/branch/main/graph/badge.svg?token=P9XIK2KV9G)](https://codecov.io/gh/kiwilan/php-archive)

PHP package to read and extract files from archives like ZIP, RAR, TAR or PDF.

## About

This package was heavily inspired by [Gemorroj/Archive7z](https://github.com/Gemorroj/Archive7z) which is a wrapper is a wrapper of [p7zip-project/p7zip](https://github.com/p7zip-project/p7zip) a fork of `p7zip`. If you need to manage many archives, you should use `Gemorroj/Archive7z` instead. Current package is a wrapper of original `p7zip`, it's not powerful as `p7zip-project/p7zip` but easier to install.

### Why not use native PHP functions?

To handle `.zip` archives, it's easy with `ZipArchive` native class. But for other formats, it's really a pain. For `.rar` format, you need [PECL `rar`](https://github.com/cataphract/php-rar) extension which is not actively maintained. For `tar` format, you have many possibilities but it's really a pain to manage all of them, with `.gz`, `.bz2`, `.xz` and `.lzma` compression. And for `.7z` format with PHP, it's again a pain.

The binary `p7zip` is a really good solution to handle all of them. It's not a native PHP solution but it's easy to install on most of OS. This package is not an all-in-one solution but it's a good start to handle archives.

### What is the aim of this package?

I want to handle many archives to handle eBooks like `.epub` or `.cbz` for example. I need to scan files into these archives and extract some files with a good performance. I extended to `.tar` compression formats because it's really easy to handle with `p7zip`. I handle PDF metadata with `smalot/pdfparser` for eBooks which are PDF format.

### Really works on any system?

It designed to works with any system with `p7zip` installed. But for `macOS`, `p7zip` is not able to handle `.rar` extraction, you have to install third library `rar`

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

## Requirements

-   PHP >= 7.4

```bash
sudo apt-get install unace unrar zip unzip p7zip-full p7zip-rar sharutils rar uudeview mpack arj cabextract file-roller
```

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
