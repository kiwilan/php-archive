<?php

namespace Kiwilan\Archive\Readers;

use ZipArchive;

class ReaderZipFile extends ReaderFile
{
    protected function __construct(
        protected ?int $index = null,
        protected ?int $crc = null,
        protected ?int $mtime = null,
        protected ?int $compSize = null,
        protected ?int $compMethod = null,
        protected ?int $encryptionMethod = null,
    ) {
    }

    public static function make(array $file, ZipArchive $archive): self
    {
        $self = new self();
        $self->setup($file['name']);
        $self->index = $file['index'];
        $self->crc = $file['crc'];
        $self->mtime = $file['mtime'];
        $self->compSize = $file['comp_size'];
        $self->compMethod = $file['comp_method'];
        $self->encryptionMethod = $file['encryption_method'];

        $self->isDirectory = substr($file['name'], -1) === '/';
        $self->size = $file['size'];
        $self->isEncrypted = $file['encryption_method'] !== 0;
        $self->content = $self->convertStream($archive->getStream($self->name));

        return $self;
    }

    public function index(): int
    {
        return $this->index;
    }

    public function crc(): int
    {
        return $this->crc;
    }

    public function size(): int
    {
        return $this->size;
    }

    public function mtime(): int
    {
        return $this->mtime;
    }

    public function compSize(): int
    {
        return $this->compSize;
    }

    public function compMethod(): int
    {
        return $this->compMethod;
    }

    public function encryptionMethod(): int
    {
        return $this->encryptionMethod;
    }

    public function content(bool $base64 = true): ?string
    {
        return $base64 ? base64_encode($this->content) : $this->content;
    }
}
