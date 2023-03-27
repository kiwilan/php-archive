<?php

namespace Kiwilan\Archive;

use Closure;
use ZipArchive;

class ArchiveZip extends BaseArchive
{
    protected function __construct(
    ) {
    }

    public static function make(string $path): self
    {
        $self = new self();
        $self->setup($path);
        $self->parse();

        return $self;
    }

    public function extract(string $toPath, array $files): array
    {
        $paths = [];
        $this->parser(function (ArchiveItem $item, ZipArchive $archive, int $i) use (&$files, $toPath, &$paths) {
            if (in_array($item, $files)) {
                $content = $this->content($item);
                $toPathFile = "{$toPath}{$item->rootPath()}";

                if (! is_dir(dirname($toPathFile))) {
                    mkdir(dirname($toPathFile), 0755, true);
                }

                $paths[] = $toPathFile;
                file_put_contents($toPathFile, $content);
            }
        });

        return $paths;
    }

    public function extractAll(string $toPath): array
    {
        $archive = new ZipArchive();
        $archive->open($this->path);
        $archive->extractTo($toPath);

        $files = $this->getFiles($toPath);

        $archive->close();

        return $files;
    }

    public function content(ArchiveItem $file, bool $toBase64 = false): ?string
    {
        $content = $this->parser(function (ArchiveItem $item, ZipArchive $archive, int $i) use ($file) {
            if ($item->filename() === $file->filename()) {
                return $archive->getFromIndex($i);
            }

            return null;
        });

        return $toBase64 ? base64_encode($content) : $content;
    }

    public function text(ArchiveItem $file): ?string
    {
        if ($file->isImage()) {
            throw new \Exception("Error, {$file->filename()} is an image");
        }

        return $this->content($file);
    }

    private function parse(): static
    {
        $archive = new ZipArchive();
        $archive->open($this->path);

        $this->metadata = new ArchiveMetadata(
            status: "{$archive->status}",
            comment: $archive->comment,
        );

        $archive->close();

        $items = [];
        $this->parser(function (ArchiveItem $item) use (&$items) {
            $items[] = $item;
        });

        $this->files = $items;
        $this->count = count($items);

        return $this;
    }

    /**
     * @param Closure(ArchiveItem $file, ZipArchive $archive, int $i): mixed $closure
     */
    private function parser(Closure $closure): mixed
    {
        $archive = new ZipArchive();
        $archive->open($this->path);

        for ($i = 0; $i < $archive->numFiles; $i++) {
            $file = $archive->statIndex($i);
            $item = $this->createArchiveItem($file);
            if ($item->isDirectory()) {
                continue;
            }

            $res = $closure($item, $archive, $i);
            if ($res) {
                $archive->close();

                return $res;
            }
        }

        $archive->close();

        return null;
    }

    private function createArchiveItem(array $zipFile): ArchiveItem
    {
        $name = $zipFile['name'];
        $index = $zipFile['index'];
        $crc = $zipFile['crc'];
        $size = $zipFile['size'];
        $mtime = $zipFile['mtime'];
        $comp_size = $zipFile['comp_size'];
        $comp_method = $zipFile['comp_method'];
        $encryption_method = $zipFile['encryption_method'];

        $extension = pathinfo($name, PATHINFO_EXTENSION);

        $item = new ArchiveItem(
            id: base64_encode($name),
            archivePath: $this->path,

            filename: $name,
            extension: $extension,
            path: $name,
            rootPath: $name,

            sizeHuman: BaseArchive::bytesToHuman($size),
            size: $size,
            packedSize: $comp_size,

            isDirectory: BaseArchive::fileIsDirectory($name),
            isImage: BaseArchive::fileIsImage($extension),
            isHidden: BaseArchive::fileIsHidden($name),

            modified: BaseArchive::timestampToDateTime($mtime),
            created: null,
            accessed: null,

            hostOS: PHP_OS_FAMILY,
        );

        return $item;
    }
}
