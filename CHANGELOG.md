# Changelog

All notable changes to `php-archive` will be documented in this file.

## v2.3.0 - 2024-03-20

- Add password option for ZIP, RAR and 7z files, using `read(string $path, ?string $password = null)` and `readFromString(string $contents, ?string $password = null, ?string $extension = null)` methods.
- Add new `Archive::class` method `readFromString(string $contents, ?string $password = null, ?string $extension = null)` to read an archive from a string
- When you read RAR or 7z archives with `p7zip` binary, you can set manually the path to the binary using `overrideBinaryPath(string $path)` method.
- `getFiles()` method is now deprecated. Use `getFileItems()` instead.
- New method `getFileItem(string $path)` to get a single file item.

## v2.2.0 - 2023-12-06

Drop `symfony/process` from dependencies.

## v2.1.02 - 2023-09-20

- All `getContent()` methods are now `getContents()`
- Old `getContent()` methods are deprecated and will be removed in v3.0.0

## v2.1.01 - 2023-08-30

- If PDF has no metadata, parser works with empty metadata

## v2.1.0 - 2023-08-28

Rework `Archive::make()`

- `addFile()` takes two parameters now: the `outputPath` inside archive and `pathToFile` on disk
  
- ~~`addFiles()`~~ is removed
  
- `addDirectory()` takes two parameters now: `relativeTo` path inside archive and the `path` of directory on the disk
  
  - If the `path` is `/path/to/dir` and `relativeTo` is `./dir`, the directory will be added to archive as `dir/`
  
- ~~`addDirectories()`~~ is removed
  

## v2.0.02 - 2023-08-28

- Add `skipAllowed` param to `Archive::class`

## v2.0.01 - 2023-08-28

- For `ArchiveZipCreate::class` add extensions check: `zip`, `epub`, `cbz`, add an option to skip the check.
- For `Archive::class`, convert `path()`, `extension()` and `type()` to `getPath()`, `getExtension()` and `getType()`.
- Add docblocks to `Archive::class`.

## 2.0.0 - 2023-08-08

### BREAKING CHANGES

- All simple getters have now `get` prefix. For example, `getPath()` instead of `path()`, `getFilename()` instead of `filename()`, etc. It concerns all simple getters of `BasicArchive`, `ArchiveItem`, `ArchiveStat`, `PdfMeta`, `ArchiveCreate` classes.

> Why?
All these classes have some methods like setters or actions. To be consistent and clear, all simple getters have now `get` prefix.

## 1.5.12 - 2023-07-07

- `BaseArchive` can now `findFiles` with auto sort

## 1.5.11 - 2023-07-07

- `BaseArchive`, `findFiles` can now sort natural files

## 1.5.1 - 2023-07-06

- Fix Windows temporary path issue

## 1.5.0 - 2023-07-06

- add `spatie/temporary-directory` for temporary directory management

## 1.4.02 - 2023-06-13

`ArchivePdf` disable `Exception` for `content`

## 1.4.01 - 2023-06-13

- rename `PdfMetadata` to `PdfMeta`

## 1.4.0 - 2023-06-13

- replace `ArchiveMetadata::class` with `ArchiveStat::class`: `$archive->stat()` from `stat` native function
- all PDF metadata are now stored in `PdfMetadata::class`: `$archive->pdf()`

## 1.3.02 - 2023-06-01

- `ArchiveMetadata` add `toArray` methods

## 1.3.01 - 2023-05-31

- `BaseArchive` fix for `ArchiveItem` nullable safe

## 1.3.0 - 2023-05-30

- Archive `content` allow `ArchiveItem` nullable for `$file` param

## 1.2.01 - 2023-05-05

- `ArchiveMetadata` keywords bug array fix

## 1.2.0 - 2023-03-31

- Improve documentation
- `Archive::create` is now `Archive::make`: can create or edit archives

## 1.1.0 - 2023-03-30

- improve documentation
- remove `findAndContent`
- `findAll` is now `filter`

## 1.0.02 - 2023-03-30

- Fix: `findAll` method with index

## 1.0.01 - 2023-03-30

- `sortFiles` reindex all `files`

## 1.0.0 - 2023-03-30

Rewriting of package

- Handle ZIP, TAR with native PHP
- Handle RAR with `rar` PHP extension or with `p7zip` binary if `rar` extension is not available
- Handle 7Z with `p7ip` binary

Changes in API

- static `make`: `read`
- `contentFile`: `content`
- Add `text` to get only text (useful for PDF)
- Add `extract` and `extractAll` to extract archives
- Add static `create` to create new archive (only ZIP supported)

## 0.1.1 - 2023-03-24

Change API

- `extractFile` to `contentFile` for `Archive::class`
- `extractPage` to `contentPage` for `ArchivePdf::class`
