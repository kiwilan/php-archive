<?php

namespace Kiwilan\Archive\Enums;

enum ArchiveEnum: string
{
    /** `.zip` archive file. */
    case zip = 'zip';
    /** `.tar` archive file. */
    case tar = 'tar';
    /** `.tar` archive file with extended compression. */
    case tarExtended = 'tar_ext';
    /** `.7z` archive file. */
    case sevenZip = '7z';
    /** `.rar` archive file. */
    case rar = 'rar';

    public static function fromExtension(string $extension): self
    {
        if ($extension === 'tar') {
            return self::tar;
        }

        if (str_contains($extension, 'gz') || str_contains($extension, 'bz2') || str_contains($extension, 'xz') || str_contains($extension, 'phar')) {
            return self::tarExtended;
        }

        $zipExts = ['zip', 'epub', 'cbz'];
        $tarExts = ['tar', 'cbt'];
        $tarExtendedExts = ['tar.gz', 'tar.bz2', 'tar.xz', 'gz', 'bz2', 'xz', 'phar'];
        $sevenZipExts = ['7z', 'cb7', '7zip'];
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
