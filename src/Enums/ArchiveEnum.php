<?php

namespace Kiwilan\Archive\Enums;

enum ArchiveEnum: string
{
    case zip = 'zip';
    case phar = 'phar';
    case sevenZip = '7z';
    case rar = 'rar';
    case pdf = 'pdf';

    public static function fromExtension(string $extension, ?string $mimeType = null): self
    {
        $extension = strtolower($extension);
        if (str_contains($extension, '.')) {
            $extension = pathinfo($extension, PATHINFO_EXTENSION);
        }

        $zips = ['zip', 'epub', 'cbz'];
        if (in_array($extension, $zips)) {
            return self::zip;
        }

        $phars = ['tar', 'gz', 'bz2', 'xz', 'phar', 'cbt'];
        if (in_array($extension, $phars)) {
            return self::phar;
        }

        $sevenZips = ['7z', 'cb7', '7zip'];
        if (in_array($extension, $sevenZips)) {
            return self::sevenZip;
        }

        $rars = ['rar', 'cbr'];
        if (in_array($extension, $rars)) {
            return self::rar;
        }

        $pdfs = ['pdf'];
        if (in_array($extension, $pdfs)) {
            return self::pdf;
        }

        if ($mimeType) {
            if (str_contains($mimeType, 'zip')) {
                return self::zip;
            }

            if (str_contains($mimeType, 'rar')) {
                return self::rar;
            }

            if (str_contains($mimeType, 'pdf')) {
                return self::pdf;
            }
        }

        throw new \Exception("Unknown archive type for extension: {$extension}");
    }
}
