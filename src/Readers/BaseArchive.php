<?php

namespace Kiwilan\Archive\Readers;

use DateTime;
use FilesystemIterator;
use Kiwilan\Archive\Archive;
use Kiwilan\Archive\ArchiveTemporaryDirectory;
use Kiwilan\Archive\Enums\ArchiveEnum;
use Kiwilan\Archive\Models\ArchiveItem;
use Kiwilan\Archive\Models\ArchiveStat;
use Kiwilan\Archive\Models\PdfMeta;
use Kiwilan\Archive\Processes\SevenZipProcess;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

abstract class BaseArchive
{
    protected ?string $path = null;

    protected ?string $extension = null;

    protected ?string $filename = null;

    protected ?string $basename = null;

    protected ?string $password = null;

    protected ?string $outputDirectory = null;

    protected ?ArchiveEnum $type = null;

    protected ?ArchiveStat $stat = null;

    protected ?PdfMeta $pdf = null;

    /** @var ArchiveItem[] */
    protected array $files = [];

    protected int $count = 0;

    protected ?string $tempDir = null;

    protected ?string $binaryPath = null;

    protected function __construct(
    ) {}

    /**
     * Create a new instance of Archive with path.
     */
    abstract public static function read(string $path, ?string $password = null): self;

    protected function setup(string $path): static
    {
        $this->path = $path;
        $this->extension = pathinfo($path, PATHINFO_EXTENSION);
        $this->filename = pathinfo($path, PATHINFO_FILENAME);
        $this->basename = pathinfo($path, PATHINFO_BASENAME);
        $temp = ArchiveTemporaryDirectory::make();
        $temp->clear();
        $this->outputDirectory = $temp->path();
        $this->type = ArchiveEnum::fromExtension($this->extension, Archive::getMimeType($path));

        return $this;
    }

    /**
     * Override binary path for `7z` or `rar` command.
     */
    public function overrideBinaryPath(string $path): static
    {
        $this->binaryPath = $path;

        return $this;
    }

    /**
     * Extract all files from archive.
     *
     * @return string[]
     */
    abstract public function extractAll(string $toPath): array;

    /**
     * Extract selected files from archive.
     *
     * @param  ArchiveItem[]  $files
     * @return string[]
     */
    abstract public function extract(string $toPath, array $files): array;

    /**
     * Get path to the archive.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get extension of the archive.
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * Get filename of the archive.
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * Get basename of the archive.
     */
    public function getBasename(): string
    {
        return $this->basename;
    }

    /**
     * Get `ArchiveEnum` of the archive.
     */
    public function getType(): ArchiveEnum
    {
        return $this->type;
    }

    /**
     * Get first file from archive.
     */
    public function getFirst(): ArchiveItem
    {
        return reset($this->files);
    }

    /**
     * Get last file from archive.
     */
    public function getLast(): ArchiveItem
    {
        return end($this->files);
    }

    /**
     * @deprecated Use `getFileItems()` instead.
     *
     * @return ArchiveItem[]
     */
    public function getFiles(): array
    {
        return $this->getFileItems();
    }

    /**
     * Get files from archive.
     *
     * @return ArchiveItem[]
     */
    public function getFileItems(): array
    {
        return $this->files;
    }

    /**
     * Get file from archive from path.
     *
     * @param  string  $path  Path to the file in the archive.
     */
    public function getFileItem(string $path): ?ArchiveItem
    {
        $original_path = $path;
        if (PHP_OS_FAMILY === 'Windows') {
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        }

        $files = array_filter($this->files, fn (ArchiveItem $item) => $item->getPath() === $path);

        if (count($files) > 0) {
            return reset($files);
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $files = array_filter($this->files, fn (ArchiveItem $item) => $item->getPath() === $original_path);
            if (count($files) > 0) {
                return reset($files);
            }
        }

        return null;
    }

    /**
     * Get count of files.
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * Get archive stat.
     */
    public function getStat(): ?ArchiveStat
    {
        return $this->stat;
    }

    /**
     * Get PDF metadata.
     */
    public function getPdf(): ?PdfMeta
    {
        return $this->pdf;
    }

    /**
     * Get content from file.
     *
     * @deprecated Use `getContents()` instead
     */
    abstract public function getContent(?ArchiveItem $file, bool $toBase64 = false): ?string;

    /**
     * Get content from file.
     */
    abstract public function getContents(?ArchiveItem $file, bool $toBase64 = false): ?string;

    /**
     * Get text from file.
     */
    abstract public function getText(ArchiveItem $file): ?string;

    /**
     * Find file by search to get `ArchiveItem`.
     */
    public function find(string $search, bool $skipHidden = true): ?ArchiveItem
    {
        $files = $this->findFiles($search, $skipHidden);

        if (count($files) > 0) {
            return reset($files);
        }

        return null;
    }

    /**
     * Filter files by search to get `ArchiveItem[]`.
     *
     * @return ArchiveItem[]|null
     */
    public function filter(string $search, bool $skipHidden = true): ?array
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
        $files = $this->getFileItems();

        $filtered = array_filter($files, function (ArchiveItem $file) use ($search, $skipHidden) {
            $isExtension = ! str_contains($search, '.');

            if ($skipHidden && $file->isHidden()) {
                return false;
            }

            if ($isExtension) {
                return $file->getExtension() === $search;
            } else {
                return str_contains($file->getPath(), $search);
            }
        });

        $property = 'getRootPath';
        $sort = fn ($a, $b) => strnatcmp($a->{$property}(), $b->{$property}());
        usort($filtered, $sort);

        return array_values($filtered);
    }

    protected function sortFiles()
    {
        usort($this->files, fn (ArchiveItem $a, ArchiveItem $b) => strcmp($a->getPath(), $b->getPath()));
        $this->files = array_values($this->files);
    }

    protected static function extensionImagickTest(bool $exception = true): bool
    {
        if (! extension_loaded('imagick')) {
            if ($exception) {
                throw new \Exception("'Error PDF, `imagick` extension: is not installed'\nCheck this guide https://gist.github.com/ewilan-riviere/3f4efd752905abe24fd1cd44412d9db9#imagemagick");
            }

            return false;
        }

        return true;
    }

    protected static function extensionRarTest(bool $exception = true): bool
    {
        if (! extension_loaded('rar')) {
            if ($exception) {
                throw new \Exception("'Error WinRAR, `rar` extension: is not installed'\nCheck this guide https://gist.github.com/ewilan-riviere/3f4efd752905abe24fd1cd44412d9db9#winrar");
            }

            return false;
        }

        return true;
    }

    public static function binaryP7zipTest(bool $exception = true): bool
    {
        return SevenZipProcess::test($exception);
    }

    protected function getAllFiles(string $path): array
    {
        $files = array_diff(scandir($path), ['.', '..']);

        $items = [];

        foreach ($files as $file) {
            $fullPath = $path.DIRECTORY_SEPARATOR.$file;

            if (is_dir($fullPath)) {
                $items = array_merge($items, $this->getAllFiles($fullPath));
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

    public static function pathToOsPath(string $path): string
    {
        return str_replace('/', DIRECTORY_SEPARATOR, $path);
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
        $filename = pathinfo($filename, PATHINFO_BASENAME);

        return str_starts_with($filename, '.');
    }

    public static function timestampToDateTime(int $timestamp): DateTime
    {
        $date = new \DateTime;
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

    public static function recurseRmdir(string $dir)
    {
        $exclude = ['.gitignore'];
        $it = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
        $it = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($it as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } elseif (! in_array($file->getFilename(), $exclude)) {
                unlink($file->getPathname());
            }
        }
        // rmdir($dir);
    }
}
