<?php

namespace Kiwilan\Archive\Readers;

use RarEntry;

class ReaderRarFile extends ReaderFile
{
    protected function __construct(
        // private RarEntry $entry,
        protected ?int $unpackedSize = null,
        protected ?int $packedSize = null,
        protected ?int $hostOs = null,
        protected ?string $fileTime = null,
        protected ?string $crc = null,
        protected ?int $attr = null,
        protected ?int $version = null,
    ) {
    }

    public static function make(RarEntry $entry): self
    {
        $self = new self();
        $self->setup($entry->getName());
        $self->isDirectory = $entry->isDirectory();
        $self->unpackedSize = $entry->getUnpackedSize();
        $self->packedSize = $entry->getPackedSize();
        $self->hostOs = $entry->getHostOs();
        $self->fileTime = $entry->getFileTime();
        $self->crc = $entry->getCrc();
        $self->attr = $entry->getAttr();
        $self->version = $entry->getVersion();

        $self->size = $self->unpackedSize;
        $self->isEncrypted = $entry->isEncrypted();
        $self->content = $self->convertStream($entry->getStream($self->name));

        return $self;
    }

    public function isEncrypted(): bool
    {
        return $this->isEncrypted;
    }

    public function unpackedSize(): ?int
    {
        return $this->unpackedSize;
    }

    public function packedSize(): ?int
    {
        return $this->packedSize;
    }

    public function hostOs(): ?int
    {
        return $this->hostOs;
    }

    public function fileTime(): ?string
    {
        return $this->fileTime;
    }

    public function crc(): ?string
    {
        return $this->crc;
    }

    public function attr(): ?int
    {
        return $this->attr;
    }

    public function version(): ?int
    {
        return $this->version;
    }

    public function content(bool $base64 = true): ?string
    {
        return $base64 ? base64_encode($this->content) : $this->content;
    }
}
