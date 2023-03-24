<?php

namespace Kiwilan\Archive\Enums;

enum ArchiveEnum: string
{
    case zip = 'zip';
    case tar = 'tar';
    case tarExtended = 'tar_ext';
    case sevenZip = '7z';
    case rar = 'rar';

    public static function fromExtension(string $extension): self
    {
        if (str_contains($extension, 'tar')) {
            return self::tar;
        }

        if (str_contains($extension, 'gz') || str_contains($extension, 'bz2') || str_contains($extension, 'xz') || str_contains($extension, 'phar')) {
            return self::tarExtended;
        }

        $zipExts = ['zip', 'epub', 'cbz'];
        $tarExts = ['tar', 'cbt'];
        $tarExtendedExts = ['tar.gz', 'tar.bz2', 'tar.xz', 'gz', 'bz2', 'xz', 'phar'];
        $sevenZipExts = ['7z', 'cb7'];
        $rarExts = ['rar', 'cbr'];

        if (in_array($extension, $zipExts)) {
            return self::zip;
        }

        if (in_array($extension, $tarExts)) {
            return self::tar;
        }

        if (in_array($extension, $tarExtendedExts)) {
            return self::tarExtended;
        }

        if (in_array($extension, $sevenZipExts)) {
            return self::sevenZip;
        }

        if (in_array($extension, $rarExts)) {
            return self::rar;
        }

        throw new \Exception("Unknown archive type for extension: {$extension}");
    }
}
