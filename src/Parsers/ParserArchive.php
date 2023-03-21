<?php

namespace Kiwilan\Archive\Parsers;

use Closure;
use Kiwilan\Archive\ArchiveFile;
use Kiwilan\Archive\Readers\ReaderFile;

abstract class ParserArchive
{
    /** @var array<string, ReaderFile> */
    protected array $files = [];

    protected ?string $status = null;

    protected ?string $comment = null;

    protected ?int $count = null;

    protected function __construct(
        protected ArchiveFile $file,
    ) {
    }

    public function file(): ArchiveFile
    {
        return $this->file;
    }

    public function count(): ?int
    {
        return $this->count;
    }

    public function status(): ?string
    {
        return $this->status;
    }

    public function comment(): ?string
    {
        return $this->comment;
    }

    abstract public static function make(ArchiveFile $file): self;

    abstract public function parse(Closure $closure): mixed;

    /**
     * @return array<string, ReaderFile>
     */
    public function files(): array
    {
        return $this->files;
    }
}
