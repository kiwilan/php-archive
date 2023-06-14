# PHP Archive

[![php][php-version-src]][php-version-href]
[![version][version-src]][version-href]
[![downloads][downloads-src]][downloads-href]
[![license][license-src]][license-href]

[![tests][tests-src]][tests-href]
[![codecov][codecov-src]][codecov-href]

PHP package to handle archives (`.zip`, `.rar`, `.tar`, `.7z`) or `.pdf` with hybrid solution (native/`p7zip`), designed to works with eBooks (`.epub`, `.cbz`, `.cbr`, `.cb7`, `.cbt`).

Supports Linux, macOS and Windows.

<p align="center">
  <img src="tests/media/banner.jpg" style="width: 80%;" alt="Banner with archives picture in background and PHP archive title" />
</p>

> **Warning**
>
> For some formats (`.rar` and `.7z`) [`rar` PHP extension](https://github.com/cataphract/php-rar) or [p7zip](https://www.7-zip.org/) binary could be necessary, see [Requirements](#requirements).

## Requirements

-   **PHP version** >= _8.1_
-   **PHP extensions**:
    -   [`zip`](https://www.php.net/manual/en/book.zip.php) (native, optional) for `.ZIP`, `.EPUB`, `.CBZ` archives
    -   [`fileinfo`](https://www.php.net/manual/en/book.fileinfo.php) (native, optional) for better file detection
    -   [`rar`](https://www.php.net/manual/en/book.rar.php) (optional) for `.RAR`, `.CBR` archives
    -   [`imagick`](https://www.php.net/manual/en/book.imagick.php) (optional) for `.PDF`
    -   [`bz2`](https://www.php.net/manual/en/book.bzip2.php) (optional) for `.BZ2` archives

|           Type            | Native |                                                                                       Dependency                                                                                       |         Uses         |
| :-----------------------: | :----: | :------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------: | :------------------: |
|  `.zip`, `.epub`, `.cbz`  |   ✅   |                                                                                          N/A                                                                                           |         PHP          |
| `.tar`, `.tar.gz`, `.cbt` |   ✅   |                                                                                          N/A                                                                                           |         PHP          |
|      `.rar`, `.cbr`       |   ❌   |                                        [`rar` PHP extension](https://github.com/cataphract/php-rar) or [`p7zip`](https://www.7-zip.org/) binary                                        | PHP `rar` or `p7zip` |
|       `.7z`, `.cb7`       |   ❌   |                                                                        [`p7zip`](https://www.7-zip.org/) binary                                                                        |       `p7zip`        |
|          `.pdf`           |   ✅   |                                                Optional (for extraction) [`imagick` PHP extension](https://github.com/Imagick/imagick)                                                 |  `smalot/pdfparser`  |
|            ALL            |   ❌   | [`p7zip`](https://www.7-zip.org/) binary ([`rar` PHP extension](https://github.com/cataphract/php-rar) and [`imagick` PHP extension](https://github.com/Imagick/imagick) are optional) |                      |

> **Note**
>
> Here you can read some installation guides for dependencies
>
> -   [`p7zip` guide](https://gist.github.com/ewilan-riviere/85d657f9283fa6af255531d97da5d71d)
> -   [`rar` PHP extension guide](https://gist.github.com/ewilan-riviere/3f4efd752905abe24fd1cd44412d9db9#winrar)
> -   [`imagick` PHP extension guide](https://gist.github.com/ewilan-riviere/3f4efd752905abe24fd1cd44412d9db9#imagemagick)

> **Warning**
>
> -   **On macOS**, for `.rar` extract, you have to [install `rar` binary](https://gist.github.com/ewilan-riviere/85d657f9283fa6af255531d97da5d71d#macos) to extract files, `p7zip` not support `.rar` extraction.
> -   **On Windows**, for `.pdf` extract, [`imagick` PHP extension](https://github.com/Imagick/imagick) have to work but **my tests failed on this feature**. So to extract PDF pages I advice to use [WSL](https://learn.microsoft.com/en-us/windows/wsl/install).

If you want more information, you can read section [**About**](#about).

## Features

-   List files as `ArchiveItem` array
    -   With `files` method: list of files
    -   With `first` method: first file
    -   With `last` method: last file
    -   With `find` method: find first file that match with `path` property
    -   With `filter` method: find all files that match with `path` property
-   Content of file
    -   With `content` method: content of file as string (useful for images)
    -   With `text` method: content of text file (binaries files return `null`)
-   Extract files
    -   With `extract` method: extract files to directory
    -   With `extractAll` method: extract all files to directory
-   Stat of archive with `path`, `deviceNumber`, `inodeNumber`, `inodeProtectionMode`, `numberOfLinks`, `userId`, `groupId`, `deviceType`, `size`, `lastAccessAt`, `createdAt`, `modifiedAt`, `blockSize`, `numberOfBlocks`, `status`, `comment` properties
-   PDF metadata: `title`, `author`, `subject`, `creator`, `creationDate`, `modDate`, `pages`,
-   Count files
-   Create or edit archives, only with `.zip` format
    -   With `make` method: create or edit archive
    -   With `addFiles` method: add files to archive
    -   With `addFromString` method: add string to archive
    -   With `addDirectory` and `addDirectories` methods: add directories to archive
    -   With `save` method: save archive

## Installation

You can install the package via composer:

```bash
composer require kiwilan/php-archive
```

## Usage

### Read and extract

With archive file (`.zip`, `.rar`, `.tar`, `.7z`, `epub`, `cbz`, `cbr`, `cb7`, `cbt`, `tar.gz`, `.pdf`).

```php
$archive = Archive::read('path/to/archive.zip');

$files = $archive->files(); // ArchiveItem[]
$count = $archive->count(); // int of files count

$images = $archive->filter('jpeg'); // ArchiveItem[] with `jpeg` in their path
$metadataXml = $archive->find('metadata.xml'); // First ArchiveItem with `metadata.xml` in their path
$content = $archive->content($metadataXml); // `metadata.xml` file content

$paths = $archive->extract('/path/to/directory', [$metadataXml]); // string[] of extracted files paths
$paths = $archive->extractAll('/path/to/directory'); // string[] of extracted files paths
```

PDF files works with same API than archives but with some differences.

```php
$archive = Archive::read('path/to/file.pdf');

$pdf = $archive->pdf(); // Metadata of PDF

$content = $archive->content($archive->first()); // PDF page as image
$text = $archive->text($archive->first()); // PDF page as text
```

### Stat

From `stat` PHP function: <https://www.php.net/manual/en/function.stat.php>

> Gives information about a file

```php
$archive = Archive::read('path/to/file.zip');
$stat = $archive->stat();

$stat->path(); // Path of file
$stat->deviceNumber(); // Device number
$stat->inodeNumber(); // Inode number
$stat->inodeProtectionMode(); // Inode protection mode
$stat->numberOfLinks(); // Number of links
$stat->userId(); // User ID
$stat->groupId(); // Group ID
$stat->deviceType(); // Device type
$stat->size(); // Size of file
$stat->lastAccessAt(); // Last access time
$stat->createdAt(); // Creation time
$stat->modifiedAt(); // Last modification time
$stat->blockSize(); // Block size
$stat->numberOfBlocks(); // Number of blocks
$stat->status(); // Status
```

### Create

You can create archive with method `Archive::make` method.

Works only with `.zip` archives.

```php
$archive = Archive::make('path/to/archive.zip');
$archive->addFiles([
    'path/to/file1.txt',
    'path/to/file2.txt',
    'path/to/file3.txt',
]);
$archive->addFromString('test.txt', 'Hello World!');
$archive->addDirectory('path/to/directory');
$archive->save();
```

### Edit

You can edit archive with same method `Archive::make` method.

```php
$archive = Archive::make('path/to/archive.zip');
$archive->addFromString('test.txt', 'Hello World!');
$archive->save();
```

## Testing

```bash
composer test
```

## About

This package was inspired by [this excellent post](https://stackoverflow.com/a/39163620/11008206) on StackOverflow which make state of the art of PHP archive handling. The package [Gemorroj/Archive7z](https://github.com/Gemorroj/Archive7z) was also a good source of inspiration cause it's the only package that handle `.7z` archives with wrapper of `p7zip` fork binary. But I would to handle all main archives formats with native PHP solution it possible, and use `p7zip` binary only if native solution is not available.

State of the art of PHP archive handling:

-   `.zip` with [ZipArchive](https://www.php.net/manual/en/class.ziparchive.php)
-   `.tar` with [PharData](https://www.php.net/manual/en/class.phardata.php)
-   `.rar` with [RarArchive](https://www.php.net/manual/en/class.rararchive.php) if `rar` extension is installed
-   `.7z` can't be handled with native PHP solution

| Type | Is native |        Solution         |
| :--: | :-------: | :---------------------: |
| ZIP  |    ✅     |         Native          |
| TAR  |    ✅     |         Native          |
| RAR  |    ❌     | `rar` or `p7zip` binary |
| 7ZIP |    ❌     |     `p7zip` binary      |
| PDF  |    ❌     |   `smalot/pdfparser`    |

### Why not full wrapper of `p7zip` binary?

This solution is used by [Gemorroj/Archive7z](https://github.com/Gemorroj/Archive7z), and it works well. But another problem is the usage of the [`p7zip` fork](https://github.com/p7zip-project/p7zip) which is not the official `p7zip` binary and can be difficult to install on some systems.

PHP can handle natively some archive formats, but not all. So I choose to use native PHP solution when it's possible, and use `p7zip` binary with official version when it's not possible.

### Case of `rar`

The [`rar` PHP extension](https://github.com/cataphract/php-rar) is not installed by default on PHP, developers have to install it manually. This extension is not actively maintained and users could have some compilation problems. To install it with PHP 8.1 or 8.2, it's necessary to compile manually the extension, you could read [this guide](https://gist.github.com/ewilan-riviere/3f4efd752905abe24fd1cd44412d9db9#winrar) if you want to install it (for PHP 8.2, you will have a warning message but it's not a problem, the extension will work).

But `rar` PHP extension is a problem because it's not sure to have a compatibility with future PHP versions. So I choose to handle `rar` archives with `p7zip` binary if `rar` PHP extension is not installed.

### Case of `7zip`

PHP can't handle `.7z` archives natively, so I choose to use `p7zip` binary. You will have to install it on your system to use this package. You could read [this guide](https://gist.github.com/ewilan-riviere/85d657f9283fa6af255531d97da5d71d) if you want to install it.

### Case of `pdf`

PHP can't handle `.pdf` archives natively, so I choose to use `smalot/pdfparser` package, embedded in this package. To extract pages as images, you have to install [`imagick` extension](https://github.com/Imagick/imagick) you could read [this guide](https://gist.github.com/ewilan-riviere/3f4efd752905abe24fd1cd44412d9db9#imagemagick) if you want to install it.

### eBooks and comics

This package can handle `.epub`, `.cbz`, `.cbr`, `.cb7`, `.cbt` archives, it's depends on the extension, check [requirements](#requirements) section.

### More

Alternatives:

-   [Gemorroj/Archive7z](https://github.com/Gemorroj/Archive7z): handle many archives with [p7zip-project/p7zip](https://github.com/p7zip-project/p7zip) binary
-   [splitbrain/php-archive](https://github.com/splitbrain/php-archive): native PHP solution to handle `.zip` and `.tar` archives
-   [maennchen/ZipStream-PHP](https://github.com/maennchen/ZipStream-PHP): A fast and simple streaming zip file downloader for PHP. Using this library will save you from having to write the Zip to disk.

Documentation:

-   List files in `.7z`, `.rar` and `.tar` archives using PHP: <https://stackoverflow.com/a/39163620/11008206>
-   Compression and Archive Extensions: <https://www.php.net/manual/en/refs.compression.php>

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

-   [Kiwilan](https://github.com/kiwilan)
-   [All Contributors](../../contributors)
-   [spatie](https://github.com/spatie) for `spatie/package-skeleton-php`
-   [`smalot/pdfparser`](https://github.com/smalot/pdfparser) for PDF parser
-   [`7-zip`](https://www.7-zip.org/) for `p7zip` binary

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[<img src="https://user-images.githubusercontent.com/48261459/201463225-0a5a084e-df15-4b11-b1d2-40fafd3555cf.svg" height="120rem" width="100%" />](https://github.com/kiwilan)

[version-src]: https://img.shields.io/packagist/v/kiwilan/php-archive.svg?style=flat-square&colorA=18181B&colorB=777BB4
[version-href]: https://packagist.org/packages/kiwilan/php-archive
[php-version-src]: https://img.shields.io/static/v1?style=flat-square&label=PHP&message=v8.1&color=777BB4&logo=php&logoColor=ffffff&labelColor=18181b
[php-version-href]: https://www.php.net/
[downloads-src]: https://img.shields.io/packagist/dt/kiwilan/php-archive.svg?style=flat-square&colorA=18181B&colorB=777BB4
[downloads-href]: https://packagist.org/packages/kiwilan/php-archive
[license-src]: https://img.shields.io/github/license/kiwilan/php-archive.svg?style=flat-square&colorA=18181B&colorB=777BB4
[license-href]: https://github.com/kiwilan/php-archive/blob/main/README.md
[tests-src]: https://img.shields.io/github/actions/workflow/status/kiwilan/php-archive/run-tests.yml?branch=main&label=tests&style=flat-square&colorA=18181B
[tests-href]: https://github.com/kiwilan/php-archive/actions/workflows/run-tests.yml
[codecov-src]: https://codecov.io/gh/kiwilan/php-archive/branch/main/graph/badge.svg?token=P9XIK2KV9G
[codecov-href]: https://codecov.io/gh/kiwilan/php-archive
