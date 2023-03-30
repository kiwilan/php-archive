<?php

namespace Kiwilan\Archive\Readers;

use Kiwilan\Archive\Models\ArchiveItem;
use Kiwilan\Archive\Models\ArchiveMetadata;
use PharData;
use SplFileInfo;

class ArchivePhar extends BaseArchive
{
    public static function read(string $path): self
    {
        $self = new self();
        $self->setup($path);
        $self->parse();

        return $self;
    }

    public function content(ArchiveItem $file, bool $toBase64 = false): ?string
    {
        $content = file_get_contents($file->path());

        return $toBase64 ? base64_encode($content) : $content;
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
        $paths = [];
        foreach ($files as $file) {
            $content = $this->content($file);

            $toPathFile = "{$toPath}{$file->rootPath()}";

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
        $files = $this->getFiles($toPath);

        return $files;
    }

    private function parse(): static
    {
        $phar = new PharData($this->path);
        $phar->extractTo($this->outputDirectory, null, true);

        $this->metadata = new ArchiveMetadata();
        $files = $this->getFiles($this->outputDirectory);

        foreach ($files as $item) {
            $file = new SplFileInfo($item);
            $this->files[] = $this->createItemFromSplFileInfo($file, $this->outputDirectory);
        }

        $this->sortFiles();
        $this->count = count($this->files);

        return $this;
    }
}
