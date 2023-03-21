<?php

namespace Kiwilan\Archive;

use Kiwilan\Archive\Enums\ArchiveEnum;

class ArchiveFile
{
    protected function __construct(
        protected string $path,
        protected ?string $name = null,
        protected ?string $extension = null,
        protected ?ArchiveEnum $type = null,
        protected ?string $size = null,
        protected ?string $date = null,
        protected ?string $time = null,
        protected ?string $permissions = null,
        protected ?string $owner = null,
        protected ?string $group = null,
        protected ?string $crc = null,
        protected ?string $compressedSize = null,
        protected ?string $uncompressedSize = null,
        protected ?string $compressionRatio = null,
        protected ?string $compressionMethod = null,
        protected ?string $encrypted = null,
        protected ?string $comment = null,
    ) {
    }

    public static function make(string $path): self
    {
        $self = new self($path);

        return $self;
    }

    public function path(): string
    {
        return $this->path;
    }
}
