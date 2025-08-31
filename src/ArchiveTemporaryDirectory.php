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
        $prefix = substr(bin2hex(random_bytes(2)), 0, 4);
        $uniq = uniqid(more_entropy: true);
        $uniq = str_replace('.', '', $uniq);
        $uuid = "archive_{$prefix}_{$uniq}";

        return new self($uuid, $filename);
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
