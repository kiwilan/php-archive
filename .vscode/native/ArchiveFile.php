<?php

namespace Kiwilan\Archive;

use Closure;
use Kiwilan\Archive\Enums\ArchiveEnum;
use Kiwilan\Archive\Parsers\ParserArchive;
use Kiwilan\Archive\Parsers\ParserRar;
use Kiwilan\Archive\Parsers\ParserTar;
use Kiwilan\Archive\Parsers\ParserZip;
use Kiwilan\Archive\Readers\ReaderFile;

class ArchiveFile
{
    /** @var array<string, ReaderFile> */
    protected array $files = [];

    protected function __construct(
        protected string $path,
        protected ?string $extension = null,
        protected ?ArchiveEnum $type = null,
        protected ?string $filename = null,
        protected ?string $name = null,
        protected ?string $dirname = null,
        protected ?int $size = null,
        protected ?int $date = null,
        protected ?int $permissions = null,
        protected ?int $count = null,
        protected ?string $status = null,
        protected ?string $comment = null,
        protected ?ParserArchive $parser = null,
    ) {
    }

    public static function make(string $path): self
    {
        $self = new self($path);
        $self->extension = ArchiveUtils::getExtension($path);
        $self->type = ArchiveEnum::fromExtension($self->extension);

        $parser = match ($self->type) {
            ArchiveEnum::zip => ParserZip::make($self),
            ArchiveEnum::rar => ParserRar::make($self),
            ArchiveEnum::tar => ParserTar::make($self),
            default => null,
        };

        if (! $parser) {
            throw new \Exception("Archive type not supported: {$self->extension}");
        }

        $self->filename = pathinfo($path, PATHINFO_FILENAME);
        $self->name = pathinfo($path, PATHINFO_BASENAME);
        $self->dirname = pathinfo($path, PATHINFO_DIRNAME);
        $self->size = filesize($path);
        $self->date = filemtime($path);
        $self->permissions = fileperms($path);
        $self->count = $parser->count();
        $self->status = $parser->status();
        $self->comment = $parser->comment();
        $self->files = $parser->files();
        $self->parser = $parser;

        return $self;
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

    public function filename(): string
    {
        return $this->filename;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function dirname(): string
    {
        return $this->dirname;
    }

    public function size(): ?int
    {
        return $this->size;
    }

    public function date(): ?int
    {
        return $this->date;
    }

    public function permissions(): ?int
    {
        return $this->permissions;
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

    /**
     * @return array<string, ReaderFile>
     */
    public function files(): array
    {
        return $this->files;
    }

    /**
     * @param Closure(ReaderFile $file): mixed $closure
     */
    public function parse(Closure $closure): mixed
    {
        return $this->parser->parse($closure);
    }
}
