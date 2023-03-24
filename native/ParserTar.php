<?php

namespace Kiwilan\Archive\Parsers;

use Closure;
use Kiwilan\Archive\ArchiveFile;
use Kiwilan\Archive\Readers\ReaderFile;
use PharData;

class ParserTar extends ParserArchive
{
    protected function __construct(
        protected ?PharData $archive = null,
    ) {
    }

    public static function make(ArchiveFile $file): self
    {
        $self = new self();
        $self->file = $file;

        $self->metadata();
        $self->parse(function (ReaderFile $file) use (&$self) {
            $self->files[$file->name()] = $file;
        });

        return $self;
    }

    private function metadata()
    {
        $this->open();

        $this->count = $this->archive->count();

        $this->close();
    }

    public function parse(Closure $closure): mixed
    {
        $this->open();
        $files = $this->archive->getChildren();
        dd($files);
        dump($this);

        foreach ($files as $file) {
            $filename = $file->getFilename();
            dump($file);
            // $reader = ReaderZipFile::make($file, $this->archive);

            // if (preg_match('/\.txt$/i', $filename)) {
            //     $file->extractTo('destination/', $filename);
            // }
        }

        $this->close();

        return null;
    }

    protected function open(): void
    {
        $this->archive = new PharData($this->file->path());
    }

    protected function close(): void
    {
        if (! $this->closed) {
            unset($this->archive);
            $this->closed = true;
        }
    }
}
