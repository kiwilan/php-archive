<?php

namespace Kiwilan\Archive\Readers;

use Kiwilan\Archive\Models\ArchiveItem;
use Kiwilan\Archive\Models\ArchiveStat;
use Kiwilan\Archive\Processes\SevenZipProcess;

class ArchiveSevenZip extends BaseArchive
{
    public static function read(string $path): self
    {
        $self = new self();
        $self->setup($path);
        $self->parse();

        return $self;
    }

    public function content(?ArchiveItem $file, bool $toBase64 = false): ?string
    {
        if (! $file) {
            return null;
        }

        $process = SevenZipProcess::make($this->path);
        $content = $process->content($file);

        return $toBase64
            ? base64_encode($content)
            : $content;
    }

    public function text(ArchiveItem $file): ?string
    {
        if ($file->isImage()) {
            throw new \Exception("Error, {$file->filename()} is an image");
        }

        return $this->content($file);
    }

    public function extract(string $toPath, array $files): array
    {
        $process = SevenZipProcess::make($this->path);
        $process->extract($toPath, $files);

        $paths = [];
        foreach ($files as $file) {
            $paths[] = $toPath.$file;
        }

        return $paths;
    }

    public function extractAll(string $toPath): array
    {
        $process = SevenZipProcess::make($this->path);
        $process->extract($toPath);

        $paths = [];
        foreach ($this->files as $file) {
            $paths[] = $toPath.DIRECTORY_SEPARATOR.$file;
        }

        return $paths;
    }

    private function parse(): static
    {
        $process = SevenZipProcess::make($this->path);
        $items = $process->list();

        $this->files = $items;
        $this->stat = ArchiveStat::make($this->path);
        $this->sortFiles();
        $this->count = count($items);

        return $this;
    }
}
