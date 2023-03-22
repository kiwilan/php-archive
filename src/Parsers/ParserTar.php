<?php

namespace Kiwilan\Archive\Parsers;

use Closure;
use Kiwilan\Archive\ArchiveFile;
use PharData;

class ParserTar extends ParserArchive
{
    protected $fh;

    protected function __construct(
    ) {
    }

    public static function make(ArchiveFile $file): self
    {
        $self = new self();

        return $self;
    }

    public function parse(Closure $closure): mixed
    {
        $archive = new PharData($this->file->path());
        $files = $archive->getChildren();

        foreach ($files as $file) {
            $filename = $file->getFilename();

            if (preg_match('/\.txt$/i', $filename)) {
                $file->extractTo('destination/', $filename);
            }
        }

        unset($archive);

        return null;
    }

    protected function open(): void
    {
    }

    protected function close(): void
    {
    }
}
