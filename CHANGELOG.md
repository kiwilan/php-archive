# Changelog

All notable changes to `php-archive` will be documented in this file.

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
