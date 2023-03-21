<?php

namespace Kiwilan\Archive;

class Archive
{
    public static function make(string $path): ArchiveFile
    {
        if (! file_exists($path)) {
            throw new \Exception("File does not exist: {$path}");
        }

        $file = ArchiveFile::make($path);

        return $file;
    }
}
