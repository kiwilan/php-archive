<?php

namespace Kiwilan\Archive;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use ZipArchive;

class ArchiveZipCreate
{
    /**
     * @param  ArchiveFile[]  $files
     */
    protected function __construct(
        protected string $path,
        protected string $name,
        protected array $files = [],
        protected int $count = 0,
    ) {}

    /**
     * Create a new instance of ArchiveZipCreate, allowing extensions are `zip`, `epub`, `cbz`.
     *
     * @param  string  $path  Path to the archive
     * @param  bool  $skipAllowed  Skip allowed extensions check
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
     * @return ArchiveFile[]
     */
    public function getFileItems(): array
    {
        return $this->files;
    }

    /**
     * Add a new file to the archive from existing file.
     *
     * @param  string  $outputPath  Path to the file inside the archive
     * @param  string  $pathToFile  Path to the file to add
     */
    public function addFile(string $outputPath, string $pathToFile): self
    {
        $this->files[] = new ArchiveFile($outputPath, new SplFileInfo($pathToFile));
        $this->count++;

        return $this;
    }

    /**
     * Add a new file to the archive from string.
     *
     * @param  string  $outputPath  Path to the file inside the archive
     * @param  string  $content  Content of the file to add
     */
    public function addFromString(string $outputPath, string $content): self
    {
        $this->files[] = new ArchiveFile($outputPath, null, $content);
        $this->count++;

        return $this;
    }

    /**
     * Add a full directory to the archive, including subdirectories.
     *
     * @param  string  $relativeTo  Relative path to the directory inside the archive
     * @param  string  $path  Path to the directory to add
     *
     * ```php
     * $archive->addDirectory('./to/directory', '/path/to/directory');
     * ```
     */
    public function addDirectory(string $relativeTo, string $path): self
    {
        $files = $this->pathsToSplFiles($this->directoryToPaths($path, $relativeTo));
        $this->files = [...$this->files, ...$files];
        $this->count = count($this->files);

        return $this;
    }

    /**
     * Save the archive.
     */
    public function save(): bool
    {
        $zip = new ZipArchive;
        $zip->open($this->path, ZipArchive::CREATE);

        foreach ($this->files as $file) {
            $content = $file->content;
            if (! $file->isString) {
                $content = file_get_contents($file->file->getPathname());
            }
            $zip->addFromString($file->outputPath, $content);
        }

        $this->count = $zip->numFiles;

        return $zip->close();
    }

    protected function directoryToPaths(string $path, string $relativeTo): array
    {
        $files = [];
        $directory = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator($directory);

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }

            $outputPath = str_replace($path, $relativeTo, $file->getPathname());
            $files[] = $this->addFile($outputPath, $file->getPathname());
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

class ArchiveFile
{
    public function __construct(
        public string $outputPath,
        public ?SplFileInfo $file = null,
        public ?string $content = null,
        public bool $isString = false,
    ) {
        if (! $this->file) {
            $this->isString = true;
        }
    }
}
