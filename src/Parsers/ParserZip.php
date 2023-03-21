<?php

namespace Kiwilan\Archive\Parsers;

use Closure;
use Kiwilan\Archive\ArchiveFile;
use Kiwilan\Archive\Readers\ReaderFile;
use Kiwilan\Archive\Readers\ReaderZipFile;
use ZipArchive;

class ParserZip extends ParserArchive
{
    protected function __construct(
        protected ?ZipArchive $archive = null,
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
        $archive = new ZipArchive();
        $archive->open($this->file->path());

        $this->count = $archive->count();
        $this->status = $archive->getStatusString();
        $this->comment = $archive->getArchiveComment();
    }

    public function open()
    {
        $this->archive = new ZipArchive();
        $this->archive->open($this->file->path());
    }

    public function close()
    {
        $this->archive->close();
    }

    public function parse(Closure $closure): mixed
    {
        $this->open();

        for ($i = 0; $i < $this->archive->numFiles; $i++) {
            $file = $this->archive->statIndex($i);
            $reader = ReaderZipFile::make($file, $this->archive);
            if ($reader->isDirectory()) {
                continue;
            }

            $res = $closure($reader);
            if ($res) {
                $this->close();

                return $res;
            }
        }

        $this->close();

        return null;
    }
}
