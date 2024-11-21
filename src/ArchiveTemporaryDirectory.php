<?php

namespace Kiwilan\Archive;

use Spatie\TemporaryDirectory\TemporaryDirectory;

class ArchiveTemporaryDirectory
{
    protected function __construct(
        protected string $uuid,
        protected ?string $tempDir = null,
        protected ?string $filename = null,
    ) {}

    public static function make(?string $filename = null): self
    {
        return new self(uniqid(), $filename);
    }

    public function clear(): bool
    {
        (new TemporaryDirectory)->name($this->uuid)->force()->delete();

        return true;
    }

    public function path(): string
    {
        $temp = (new TemporaryDirectory)->name("{$this->uuid}");

        if (! $temp->exists()) {
            $temp->create();
        }

        return $temp->path();
    }
}
