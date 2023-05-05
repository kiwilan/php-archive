# Changelog

All notable changes to `php-archive` will be documented in this file.

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
