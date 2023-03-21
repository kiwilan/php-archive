<?php

namespace Kiwilan\Archive\Parsers;

use Closure;
use Kiwilan\Archive\ArchiveFile;
use Kiwilan\Archive\ArchiveUtils;
use Kiwilan\Archive\Enums\ArchiveEnum;
use Kiwilan\Archive\Readers\ReaderFile;
use Kiwilan\Archive\Readers\ReaderRarFile;
use RarArchive;

class ParserRar extends ParserArchive
{
    protected function __construct(
        protected ?RarArchive $archive = null,
    ) {
    }

    public static function make(ArchiveFile $file): self
    {
        $self = new self();

        if ($file->type() === ArchiveEnum::rar && ! extension_loaded('rar')) {
            ArchiveUtils::print('.rar file: rar extension: is not installed', 'red');
            ArchiveUtils::print('Check this guide https://gist.github.com/ewilan-riviere/3f4efd752905abe24fd1cd44412d9db9', 'red');

            return $self;
        }

        $self->file = $file;

        $i = 0;
        $self->parse(function (ReaderFile $file) use (&$i, &$self) {
            $i++;
            $self->files[$file->name()] = $file;
        });
        $self->count = $i;

        return $self;
    }

    public function open()
    {
        $this->archive = RarArchive::open($this->file->path());
    }

    public function close()
    {
        $this->archive->close();
    }

    public function parse(Closure $closure): void
    {
        $this->open();

        if ($this->archive->isBroken()) {
            throw new \Exception("Archive is broken {$this->file->path()}");
        }

        foreach ($this->archive->getEntries() as $key => $entry) {
            $reader = ReaderRarFile::make($entry);
            if ($reader->isDirectory()) {
                continue;
            }

            $closure($reader);
        }

        $this->close();
    }
}
