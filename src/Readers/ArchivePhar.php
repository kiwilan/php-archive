<?php

namespace Kiwilan\Archive\Readers;

use Kiwilan\Archive\Models\ArchiveItem;
use Kiwilan\Archive\Models\ArchiveStat;
use PharData;
use SplFileInfo;

class ArchivePhar extends BaseArchive
{
    public static function read(string $path, ?string $password = null): BaseArchive
    {
        $self = new self;
        if ($password) {
            $self->password = $password;
            $self = ArchiveSevenZip::read($path, $password);

            return $self;
        }

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

        $content = file_get_contents($file->getPath());

        return $toBase64 ? base64_encode($content) : $content;
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
        $paths = [];
        foreach ($files as $file) {
            $content = $this->getContents($file);

            $toPathFile = "{$toPath}{$file->getRootPath()}";

            if (! is_dir(dirname($toPathFile))) {
                mkdir(dirname($toPathFile), 0755, true);
            }

            $paths[] = $toPathFile;
            file_put_contents($toPathFile, $content);
        }

        return $paths;
    }

    public function extractAll(string $toPath): array
    {
        $phar = new PharData($this->path);
        $phar->extractTo($toPath, null, true);
        $files = $this->getAllFiles($toPath);

        return $files;
    }

    private function parse(): static
    {
        $phar = new PharData($this->path);
        $phar->extractTo($this->outputDirectory, null, true);

        $this->stat = ArchiveStat::make($this->path);
        $files = $this->getAllFiles($this->outputDirectory);

        foreach ($files as $item) {
            $file = new SplFileInfo($item);
            $this->files[] = $this->createItemFromSplFileInfo($file, $this->outputDirectory);
        }

        $this->sortFiles();
        $this->count = count($this->files);

        return $this;
    }
}
