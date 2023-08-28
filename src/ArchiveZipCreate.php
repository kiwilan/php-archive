<?php

namespace Kiwilan\Archive;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use ZipArchive;

class ArchiveZipCreate
{
    /**
     * @param  SplFileInfo[]  $files
     * @param  array<string, string>  $strings
     */
    protected function __construct(
        protected string $path,
        protected string $name,
        protected array $files = [],
        protected array $strings = [],
        protected int $count = 0,
    ) {
    }

    /**
     * Create a new instance of ArchiveZipCreate, allowing extensions are `zip`, `epub`, `cbz`.
     *
     * @param  string  $path Path to the archive
     * @param  bool  $skipAllowed Skip allowed extensions check
     *
     * @throws \Exception
     */
    public static function make(string $path, bool $skipAllowed = false): self
    {
        $self = new self($path, pathinfo($path, PATHINFO_BASENAME));

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if (! $skipAllowed) {
            $allowedExtensions = ['zip', 'epub', 'cbz'];
            if (! in_array($extension, $allowedExtensions)) {
                $extensions = implode(', ', $allowedExtensions);
                throw new \Exception("File {$path} is not a zip file, only {$extensions} files are supported.");
            }

        }

        return $self;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @return SplFileInfo[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @return array<string, string>
     */
    public function getStrings(): array
    {
        return $this->strings;
    }

    public function addFile(string $path): self
    {
        $this->files[] = new SplFileInfo($path);
        $this->count++;

        return $this;
    }

    public function addFromString(string $filename, string $content): self
    {
        $this->strings[$filename] = $content;
        $this->count++;

        return $this;
    }

    public function addFiles(array $paths): self
    {
        foreach ($paths as $path) {
            $this->addFile($path);
        }

        return $this;
    }

    public function addDirectory(string $path): self
    {
        $files = $this->pathsToSplFiles($this->directoryToPaths($path));
        $this->files = [...$this->files, ...$files];
        $this->count = count($this->files);

        return $this;
    }

    public function addDirectories(array $paths): self
    {
        foreach ($paths as $path) {
            $this->addDirectory($path);
        }

        return $this;
    }

    public function save(): self
    {
        $zip = new ZipArchive();
        $zip->open($this->path, ZipArchive::CREATE);

        foreach ($this->files as $file) {
            $zip->addFile($file->getRealPath(), $file->getFilename());
        }

        foreach ($this->strings as $filename => $content) {
            $zip->addFromString($filename, $content);
        }

        $this->count = $zip->numFiles;

        $zip->close();

        return $this;
    }

    protected function directoryToPaths(string $path): array
    {
        $files = [];
        $directory = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator($directory);

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }
            $files[] = $file->getPathname();
        }

        return $files;
    }

    /**
     * @param  string[]|SplFileInfo[]  $paths
     * @return SplFileInfo[]
     */
    protected function pathsToSplFiles(array $paths): array
    {
        $files = [];
        foreach ($paths as $path) {
            if (is_string($path)) {
                $files[] = new SplFileInfo($path);
            } elseif ($path instanceof SplFileInfo) {
                $files[] = $path;
            }
        }

        return $files;
    }
}
