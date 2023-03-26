<?php

namespace Kiwilan\Archive;

use Kiwilan\Archive\Enums\ArchiveEnum;

class Archive
{
    protected function __construct(
        protected string $path,
        protected string $extension,
        protected ArchiveEnum $type,
    ) {
    }

    public static function make(string $path): BaseArchive
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
            ArchiveEnum::sevenZip => ArchivePhar::class,
            ArchiveEnum::rar => ArchivePhar::class,
            ArchiveEnum::pdf => ArchivePhar::class,
        };

        return $archive::make($self->path);
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
