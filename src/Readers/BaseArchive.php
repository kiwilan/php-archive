<?php

namespace Kiwilan\Archive\Readers;

use DateTime;
use Exception;
use FilesystemIterator;
use Kiwilan\Archive\Enums\ArchiveEnum;
use Kiwilan\Archive\Models\ArchiveItem;
use Kiwilan\Archive\Models\ArchiveMetadata;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Process\Process;

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

    abstract public static function read(string $path): self;

    protected function setup(string $path): static
    {
        BaseArchive::clearOutputDirectory();

        $this->path = $path;
        $this->extension = pathinfo($path, PATHINFO_EXTENSION);
        $this->filename = pathinfo($path, PATHINFO_FILENAME);
        $this->basename = pathinfo($path, PATHINFO_BASENAME);
        $this->outputDirectory = BaseArchive::getOutputDirectory($this->basename);
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

    /**
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
        $files = $this->files();

        $filtered = array_filter($files, function (ArchiveItem $file) use ($search, $skipHidden) {
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

        $filtered = array_values($filtered);

        return $filtered;
    }

    protected function sortFiles()
    {
        usort($this->files, fn (ArchiveItem $a, ArchiveItem $b) => strcmp($a->path(), $b->path()));
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
        $process = new Process(['7z']);
        $process->run();

        if (! $process->isSuccessful()) {
            if ($exception) {
                $osFamily = PHP_OS_FAMILY;
                $isDarwin = $osFamily === 'Darwin';
                $message = "p7zip is not installed or not in the PATH. Please install p7zip and try again.\nYou can check this guide: https://gist.github.com/ewilan-riviere/85d657f9283fa6af255531d97da5d71d";

                if ($isDarwin) {
                    $message .= "\nYou have to install `rar` binary with brew on macOS.";
                }

                throw new Exception($message);
            }

            return false;
        }

        return true;
    }

    public static function clearOutputDirectory(): bool
    {
        $output = self::getOutputDirectory();
        if (! is_dir($output)) {
            mkdir($output, 0755, true);
        }
        self::recurseRmdir($output);

        return true;
    }

    public static function getOutputDirectory(?string $filename = null): string
    {
        $root = getcwd();
        if (is_dir("{$root}/vendor")) {
            $outputDirectory = "{$root}/vendor/temp";
        } else {
            $outputDirectory = "{$root}/temp";
        }

        if ($filename) {
            $filename = pathinfo($filename, PATHINFO_BASENAME);
            $outputDirectory .= DIRECTORY_SEPARATOR.$filename;
        }

        $outputDirectory = self::pathToOsPath($outputDirectory);

        return $outputDirectory;
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
