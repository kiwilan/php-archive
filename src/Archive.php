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
    ) {
    }

    /**
     * Read an archive from the path.
     */
    public static function read(string $path): BaseArchive
    {
        if (! file_exists($path)) {
            throw new \Exception("File {$path} not found");
        }

        $mimeType = Archive::getMimeType($path);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $type = ArchiveEnum::fromExtension($extension, $mimeType);
        $self = new self($path, $extension, $type);

        /** @var BaseArchive */
        $archive = match ($self->type) {
            ArchiveEnum::zip => ArchiveZip::class,
            ArchiveEnum::phar => ArchivePhar::class,
            ArchiveEnum::sevenZip => ArchiveSevenZip::class,
            ArchiveEnum::rar => ArchiveRar::class,
            ArchiveEnum::pdf => ArchivePdf::class,
        };

        return $archive::read($self->path);
    }

    /**
     * Create an archive from path, allowing extensions are `zip`, `epub`, `cbz`.
     *
     * @param  string  $path Path to the archive
     * @param  bool  $skipAllowed Skip allowed extensions check
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
