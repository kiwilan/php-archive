<?php

namespace Kiwilan\Archive\Readers;

use Kiwilan\Archive\ArchiveUtils;

abstract class ReaderFile
{
    protected ?string $content = null;

    protected function __construct(
        protected ?string $name = null,
        protected ?string $extension = null,
        protected bool $isDirectory = false,
        protected ?int $size = null,
        protected bool $isEncrypted = false,
    ) {
    }

    protected function setup(?string $name): self
    {
        $this->name = $name;
        $this->extension = ArchiveUtils::getExtension($this->name);

        return $this;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function extension(): string
    {
        return $this->extension;
    }

    public function isDirectory(): bool
    {
        return $this->isDirectory;
    }

    public function size(): ?int
    {
        return $this->size;
    }

    public function isEncrypted(): bool
    {
        return $this->isEncrypted;
    }

    abstract public function content(bool $base64 = true): ?string;

    protected function convertStream(mixed $stream): string
    {
        if (! $stream) {
            throw new \Exception('Stream is empty.');
        }

        $content = null;

        // https://www.php.net/manual/en/rarentry.getstream.php
        while (! feof($stream)) {
            $buff = fread($stream, 8192);

            if ($buff !== false) {
                $content .= $buff;
            }
        }

        return $content;
    }
}
