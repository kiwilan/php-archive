<?php

namespace Kiwilan\Archive;

use DateTime;
use Kiwilan\Archive\Enums\ArchiveEnum;
use SplFileInfo;

abstract class BaseArchive
{
    protected ?string $path = null;

    protected ?string $extension = null;

    protected ?string $filename = null;

    protected ?string $basename = null;

    protected ?string $outputDirectory = null;

    protected ?ArchiveEnum $type = null;

    protected ?ArchiveMetadata $metadata = null;

    /** @var ArchiveItem[] */
    protected array $files = [];

    protected int $count = 0;

    protected function __construct(
    ) {
    }

    abstract public static function make(string $path): self;

    protected function setup(string $path): static
    {
        $this->path = $path;
        $this->extension = pathinfo($path, PATHINFO_EXTENSION);
        $this->filename = pathinfo($path, PATHINFO_FILENAME);
        $this->basename = pathinfo($path, PATHINFO_BASENAME);
        $this->outputDirectory = sys_get_temp_dir().DIRECTORY_SEPARATOR.$this->basename;
        $this->type = ArchiveEnum::fromExtension($this->extension, mime_content_type($path));

        return $this;
    }

    /**
     * @return string[]
     */
    abstract public function extractAll(string $toPath): array;

    /**
     * @param  ArchiveItem[]  $files
     * @return string[]
     */
    abstract public function extract(string $toPath, array $files): array;

    public function path(): string
    {
        return $this->path;
    }

    public function extension(): string
    {
        return $this->extension;
    }

    public function filename(): string
    {
        return $this->filename;
    }

    public function basename(): string
    {
        return $this->basename;
    }

    public function type(): ArchiveEnum
    {
        return $this->type;
    }

    public function first(): ArchiveItem
    {
        return reset($this->files);
    }

    public function last(): ArchiveItem
    {
        return end($this->files);
    }

    public function files(): array
    {
        return $this->files;
    }

    public function count(): int
    {
        return $this->count;
    }

    public function metadata(): ArchiveMetadata
    {
        return $this->metadata;
    }

    abstract public function content(ArchiveItem $file, bool $toBase64 = false): ?string;

    abstract public function text(ArchiveItem $file): ?string;

    public function find(string $search, bool $skipHidden = true): ?ArchiveItem
    {
        $files = $this->findFiles($search, $skipHidden);

        if (count($files) > 0) {
            return reset($files);
        }

        return null;
    }

    public function findAndContent(string $search, bool $skipHidden = true): ?string
    {
        $file = $this->find($search, $skipHidden);
        if (! $file) {
            return null;
        }

        $content = $this->content($file);

        return $content;
    }

    /**
     * @return ArchiveItem[]|null
     */
    public function findAll(string $search, bool $skipHidden = true): ?array
    {
        $files = $this->findFiles($search, $skipHidden);

        if (count($files) > 0) {
            return $files;
        }

        return null;
    }

    protected function convertStream(mixed $stream): string
    {
        if (! $stream) {
            throw new \Exception('Stream is empty.');
        }

        $content = null;

        // https://www.php.net/manual/en/rarentry.getstream.php
        while (! feof($stream)) {
            $buff = fread($stream, 8192);

            if ($buff !== false) {
                $content .= $buff;
            }
        }

        return $content;
    }

    /**
     * @return ArchiveItem[]
     */
    protected function findFiles(string $search, bool $skipHidden): array
    {
        $files = $this->files();

        return array_filter($files, function (ArchiveItem $file) use ($search, $skipHidden) {
            $isExtension = ! str_contains($search, '.');
            if ($skipHidden && $file->isHidden()) {
                return false;
            }
            if ($isExtension) {
                return $file->extension() === $search;
            } else {
                return str_contains($file->path(), $search);
            }
        });
    }

    protected function getFiles(string $path): array
    {
        $files = array_diff(scandir($path), ['.', '..']);

        $items = [];
        foreach ($files as $file) {
            $fullPath = $path.DIRECTORY_SEPARATOR.$file;
            if (is_dir($fullPath)) {
                $items = array_merge($items, $this->getFiles($fullPath));
            } else {
                $items[] = $fullPath;
            }
        }

        $list = [];
        foreach ($items as $item) {
            $item = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $item);
            $list[] = $item;
        }

        return $list;
    }

    protected function createItemFromSplFileInfo(SplFileInfo $file, string $output): ArchiveItem
    {
        $path = str_replace($output.DIRECTORY_SEPARATOR, '', $file->getPathname());

        return new ArchiveItem(
            id: base64_encode($file->getPathname()),
            archivePath: $this->path,

            filename: $file->getFilename(),
            extension: $file->getExtension(),
            path: $file->getPathname(),
            rootPath: $path,

            sizeHuman: BaseArchive::bytesToHuman($file->getSize()),
            size: $file->getSize(),
            packedSize: $file->getSize(),

            isDirectory: $file->isDir(),
            isImage: BaseArchive::fileIsImage($file->getExtension()),
            isHidden: BaseArchive::fileIsHidden($file->getFilename()),

            modified: BaseArchive::timestampToDateTime($file->getMTime()),
            created: BaseArchive::timestampToDateTime($file->getCTime()),
            accessed: BaseArchive::timestampToDateTime($file->getATime()),

            hostOS: PHP_OS_FAMILY,
        );
    }

    public static function fileIsImage(?string $extension): bool
    {
        return in_array($extension, [
            'jpg',
            'jpeg',
            'png',
            'gif',
            'bmp',
            'webp',
            'svg',
            'ico',
            'avif',
        ], true);
    }

    public static function fileIsDirectory(string $path): bool
    {
        if (is_dir($path)) {
            return true;
        }

        $hasExtension = str_contains($path, '.');

        if ($hasExtension) {
            return false;
        }

        return true;
    }

    public static function fileIsHidden(string $filename): bool
    {
        return str_starts_with($filename, '.');
    }

    public static function timestampToDateTime(int $timestamp): DateTime
    {
        $date = new \DateTime();
        $date->setTimestamp($timestamp);

        return $date;
    }

    public static function bytesToHuman(mixed $bytes): ?string
    {
        if (empty($bytes)) {
            return null;
        }

        if (gettype($bytes) !== 'integer' && gettype($bytes) !== 'double' && gettype($bytes) !== 'float') {
            $bytes = intval($bytes);
        }

        $size = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $floor = floor(log($bytes, 1024));
        $format = $size[$floor];

        $round = round($bytes / pow(1024, $floor), 2);

        return "{$round} {$format}";
    }
}
