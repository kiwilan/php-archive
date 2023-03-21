<?php

namespace Kiwilan\Archive\Enums;

enum ArchiveEnum: string
{
    case zip = 'zip';
    case tar = 'tar';
    case sevenZip = '7z';
    case rar = 'rar';
    // case tar_gz = 'tar.gz';
    // case tar_bz2 = 'tar.bz2';
    // case tar_xz = 'tar.xz';
    // case gzip = 'gzip';
    // case bzip2 = 'bzip2';
    // case xz = 'xz';
    // case phar = 'phar';
}
