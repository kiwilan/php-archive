<?php

namespace Kiwilan\Archive\Readers;

use Closure;
use Kiwilan\Archive\Models\ArchiveItem;
use Kiwilan\Archive\Models\ArchiveStat;
use ZipArchive;

class ArchiveZip extends BaseArchive
{
    public static function read(string $path, ?string $password = null): self
    {
        $self = new self;
        if ($password) {
            $self->password = $password;
        }
        $self->setup($path);
        $self->parse();

        return $self;
    }

    public function extract(string $toPath, array $files): array
    {
        $paths = [];
        $this->parser(function (ArchiveItem $item, ZipArchive $archive, int $i) use (&$files, $toPath, &$paths) {
            if (in_array($item, $files)) {
                $content = $this->getContents($item);
                $toPathFile = "{$toPath}{$item->getRootPath()}";

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
        $archive = new ZipArchive;
        $archive->open($this->path);
        if ($this->password) {
            $archive->setPassword($this->password);
        }
        $archive->extractTo($toPath);

        $files = $this->getAllFiles($toPath);

        $archive->close();

        return $files;
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

        $content = $this->parser(function (ArchiveItem $item, ZipArchive $archive, int $i) use ($file) {
            if ($item->getFilename() === $file->getFilename()) {
                if ($this->password) {
                    $archive->setPassword($this->password);

                    return $archive->getFromIndex($i);
                }

                return $archive->getFromIndex($i);
            }

            return null;
        });

        return $toBase64 ? base64_encode($content) : $content;
    }

    public function getText(ArchiveItem $file): ?string
    {
        if ($file->isImage()) {
            throw new \Exception("Error, {$file->getFilename()} is an image");
        }

        return $this->getContents($file);
    }

    private function parse(): static
    {
        $archive = new ZipArchive;
        $archive->open($this->path);

        $this->stat = ArchiveStat::make($this->path);
        $this->stat->setStatus("{$archive->status}");
        $this->stat->setComment($archive->comment);

        $archive->close();

        $items = [];
        $this->parser(function (ArchiveItem $item) use (&$items) {
            $items[] = $item;
        });

        $this->files = $items;
        $this->sortFiles();
        $this->count = count($items);

        return $this;
    }

    /**
     * @param  Closure(ArchiveItem $file, ZipArchive $archive, int $i): mixed  $closure
     */
    private function parser(Closure $closure): mixed
    {
        $archive = new ZipArchive;
        $archive->open($this->path);
        if ($this->password) {
            $archive->setPassword($this->password);
        }

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

            extraInfos: [
                'index' => $index,
                'crc' => $crc,
                'comp_method' => $comp_method,
                'encryption_method' => $encryption_method,
            ],

            hostOS: PHP_OS_FAMILY,
        );

        return $item;
    }
}
