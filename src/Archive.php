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

    public static function read(string $path): BaseArchive
    {
        if (! file_exists($path)) {
            throw new \Exception("File {$path} not found");
        }

        $mimeType = mime_content_type($path);
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

    public static function create(string $path): ArchiveZipCreate
    {
        $archive = ArchiveZipCreate::create($path);

        return $archive;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function extension(): string
    {
        return $this->extension;
    }

    public function type(): ArchiveEnum
    {
        return $this->type;
    }
}
