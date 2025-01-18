<?php

namespace Kiwilan\Archive;

use Kiwilan\Archive\Enums\ArchiveEnum;
use Kiwilan\Archive\Readers\ArchivePdf;
use Kiwilan\Archive\Readers\ArchivePhar;
use Kiwilan\Archive\Readers\ArchiveRar;
use Kiwilan\Archive\Readers\ArchiveSevenZip;
use Kiwilan\Archive\Readers\ArchiveZip;
use Kiwilan\Archive\Readers\BaseArchive;

class Archive
{
    protected function __construct(
        protected string $path,
        protected string $extension,
        protected ArchiveEnum $type,
        protected ?string $password = null,
    ) {}

    /**
     * Read an archive from the path.
     */
    public static function read(string $path, ?string $password = null): BaseArchive
    {
        if (! file_exists($path)) {
            throw new \Exception("File {$path} not found");
        }

        $mimeType = Archive::getMimeType($path);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $type = ArchiveEnum::fromExtension($extension, $mimeType);
        $self = new self($path, $extension, $type, $password);

        /** @var BaseArchive */
        $archive = match ($self->type) {
            ArchiveEnum::zip => ArchiveZip::class,
            ArchiveEnum::phar => ArchivePhar::class,
            ArchiveEnum::sevenZip => ArchiveSevenZip::class,
            ArchiveEnum::rar => ArchiveRar::class,
            ArchiveEnum::pdf => ArchivePdf::class,
        };

        try {
            return $archive::read($self->path, $self->password);
        } catch (\Throwable $originalException) {
            if ($self->type === ArchiveEnum::zip && $extension === 'cbz') {
                try {
                    // Sometimes files with cbz extension are actually misnamed rar files
                    return ArchiveRar::read($self->path, $self->password);
                } catch (\Throwable) {
                    // If it's not a rar file, throw the original exception
                    throw $originalException;
                }
            }

            if ($self->type === ArchiveEnum::rar && $extension === 'cbr') {
                try {
                    // Sometimes files with cbr extension are actually misnamed zip files
                    return ArchiveZip::read($self->path, $self->password);
                } catch (\Throwable) {
                    // If it's not a zip file, throw the original exception
                    throw $originalException;
                }
            }

            throw $originalException;
        }
    }

    /**
     * Create an archive from contents.
     *
     * @param  string  $contents  Contents of the archive.
     * @param  string|null  $password  Password of the archive, can be null if no password.
     * @param  string|null  $extension  Extension of the archive, can be null to detect automatically mimetype.
     */
    public static function readFromString(string $contents, ?string $password = null, ?string $extension = null): BaseArchive
    {
        $file_info = new \finfo(FILEINFO_MIME_TYPE);
        $mime_type = $file_info->buffer($contents);

        $extension = match ($mime_type) {
            'application/x-bzip2' => 'bz2',
            'application/gzip' => 'gz',
            'application/x-rar' => 'rar',
            'application/epub+zip' => 'epub',
            'application/pdf' => 'pdf',
            'application/zip' => 'zip',
            'application/x-tar' => 'tar',
            'application/x-7z-compressed' => '7z',
            'application/x-cbr' => 'rar',
            'application/x-cbz' => 'cbz',
            'application/x-cbt' => 'tar',
            'application/x-cb7' => '7z',
            default => null,
        };

        if ($extension === null) {
            throw new \Exception('Archive: Error detecting extension from mime type, please add manually archive extension as third parameter of `readFromString()`.');
        }

        $path = tempnam(sys_get_temp_dir(), 'archive_');
        rename($path, $path .= ".{$extension}"); // Rename to add extension

        try {
            file_put_contents($path, $contents);
        } catch (\Throwable $th) {
            throw new \Exception('Archive: Error creating temporary file with `readFromString()`.');
        }

        return self::read($path, $password);
    }

    /**
     * Create an archive from path, allowing extensions are `zip`, `epub`, `cbz`.
     *
     * @param  string  $path  Path to the archive
     * @param  bool  $skipAllowed  Skip allowed extensions check
     *
     * @throws \Exception
     */
    public static function make(string $path, bool $skipAllowed = false): ArchiveZipCreate
    {
        return ArchiveZipCreate::make($path, $skipAllowed);
    }

    /**
     * Get path to the archive.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get extension of the archive.
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * Get type of the archive.
     */
    public function getType(): ArchiveEnum
    {
        return $this->type;
    }

    /**
     * Get mime type of the archive.
     */
    public static function getMimeType(string $path): ?string
    {
        try {
            return mime_content_type($path);
        } catch (\Throwable $th) {
            return null;
        }
    }
}
