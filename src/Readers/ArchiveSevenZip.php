<?php

namespace Kiwilan\Archive\Readers;

use Kiwilan\Archive\Models\ArchiveItem;
use Kiwilan\Archive\Models\ArchiveStat;
use Kiwilan\Archive\Processes\SevenZipProcess;

class ArchiveSevenZip extends BaseArchive
{
    protected string $archiveType = '7z';

    public static function read(string $path, ?string $password = null): self
    {
        $self = new self;
        if ($password) {
            $self->password = $password;
        }
        $self->archiveType = pathinfo($path, PATHINFO_EXTENSION);
        $self->setup($path);
        $self->parse();

        return $self;
    }

    /**
     * @deprecated Use `getContents()` instead
     */
    public function getContent(?ArchiveItem $file, bool $toBase64 = false): ?string
    {
        return $this->getContents($file, $toBase64);
    }

    public function getContents(?ArchiveItem $file, bool $toBase64 = false): ?string
    {
        if (! $file) {
            return null;
        }

        $process = SevenZipProcess::make($this->path, $this->password, $this->binaryPath);
        $content = $process->content($file);

        return $toBase64
            ? base64_encode($content)
            : $content;
    }

    public function getText(ArchiveItem $file): ?string
    {
        if ($file->isImage()) {
            throw new \Exception("Error, {$file->getFilename()} is an image");
        }

        return $this->getContents($file);
    }

    public function extract(string $toPath, array $files): array
    {
        $process = SevenZipProcess::make($this->path, $this->password);
        $process->extract($toPath, $files);

        $paths = [];
        foreach ($files as $file) {
            $paths[] = $toPath.$file;
        }

        return $paths;
    }

    public function extractAll(string $toPath): array
    {
        $process = SevenZipProcess::make($this->path, $this->password);
        $process->extract($toPath);

        $paths = [];
        foreach ($this->files as $file) {
            $paths[] = $toPath.DIRECTORY_SEPARATOR.$file;
        }

        return $paths;
    }

    private function parse(): static
    {
        $process = SevenZipProcess::make($this->path, $this->password);
        $items = $process->list();

        $this->files = $items;
        $this->stat = ArchiveStat::make($this->path);
        $this->sortFiles();
        $this->count = count($items);

        return $this;
    }
}
