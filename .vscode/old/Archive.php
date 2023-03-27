<?php

namespace Kiwilan\Archive;

use Exception;
use Kiwilan\Archive\Enums\ArchiveEnum;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Archive
{
    /** @var ArchiveItem[] */
    protected array $files = [];

    protected ?ArchiveEnum $type = null;

    protected ?string $os = null;

    protected bool $isDarwin = false;

    protected int $count = 0;

    protected string $outputDir = 'vendor/php-archive';

    protected function __construct(
        protected string $path,
        protected string $extension,
    ) {
    }

    public static function make(string $path): self
    {
        Archive::p7zipBinaryExists();

        if (! file_exists($path)) {
            throw new \Exception("File does not exist: {$path}");
        }

        $self = new self($path, pathinfo($path, PATHINFO_EXTENSION));
        if ($self->extension === 'pdf') {
            throw new \Exception('Use `ArchivePdf` class for PDF files.');
        }
        $self->type = ArchiveEnum::fromExtension($self->extension);
        $self->os = PHP_OS_FAMILY; // 'Windows', 'BSD', 'Darwin', 'Solaris', 'Linux' or 'Unknown'
        $self->isDarwin = $self->os === 'Darwin';
        $self->files = $self->setFiles();
        $self->count = count($self->files);

        return $self;
    }

    /**
     * @return ArchiveItem[]
     */
    public function files(): array
    {
        return $this->files;
    }

    public function count(): int
    {
        return $this->count;
    }

    public function type(): ArchiveEnum
    {
        return $this->type;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function extension(): string
    {
        return $this->extension;
    }

    public function os(): string
    {
        return $this->os;
    }

    public function isDarwin(): bool
    {
        return $this->isDarwin;
    }

    public function contentFile(string $path, bool $toBase64 = false): ?string
    {
        return $this->content($path, $toBase64);
    }

    /**
     * @param  string  $name Find all files which contains `name` (can be a filename or extension).
     * @return ArchiveItem[]
     */
    public function findAll(string $name): array
    {
        return $this->findFiles($name);
    }

    /**
     * @param  string  $name Find first file which contains `name` (can be a filename or extension).
     */
    public function find(string $name): ?ArchiveItem
    {
        $files = $this->findFiles($name);

        return array_shift($files);
    }

    public function extractTo(string $path, ?string $file = null): bool
    {
        if (! file_exists($path)) {
            throw new \Exception("Directory does not exist: {$path}");
        }

        if ($file) {
            $this->extractFile($file, $path);
        } else {
            $this->extractAll($path);
        }

        return true;
    }

    private function extractFile(string $file, string $path): bool
    {
        $file = $this->find($file);
        if (! $file) {
            throw new \Exception("File not found: {$file}");
        }

        $this->extract($file, $path);

        return true;
    }

    private function extractAll(string $path): bool
    {
        foreach ($this->files as $file) {
            $this->extract($file, $path);
        }

        return true;
    }

    private function extract(ArchiveItem $file, string $path): bool
    {
        if ($this->type === ArchiveEnum::rar && $this->isDarwin) {
            // rar x -y tests/media/archive.rar temp/
            $output = $this->process('rar', ['x', '-y', $this->path, $path]);
        } else {
            // 7z x -y tests/media/archive.tar.gz -otemp
            if ($this->type === ArchiveEnum::tarExtended) {
                $this->process('7z', ['x', '-y', $this->path, '-otemp']);
                $this->process('7z', ['x', '-y', $this->outputDir, "-o{$path}"]);
            } else {
                $this->process('7z', ['x', '-y', $this->path, "-o{$path}"]);
            }
        }

        return true;
    }

    /**
     * @return ArchiveItem[]
     */
    private function setFiles(): array
    {
        // tar extended:
        // 7z x tests/media/archive.tar.gz -so | 7z x -aoa -si -ttar -otemp
        // 7z x tests/media/archive.tar.gz -so | 7z l -ba -si -ttar -otemp
        // rm -rf temp ; mkdir temp ; 7z x tests/media/archive.tar.gz -otemp ; 7z l -ba -slt temp ; rm -rf temp
        // tar -tf tests/media/archive.tar.gz
        $output = null;
        if ($this->type === ArchiveEnum::tarExtended) {
            $this->recurseRmdir($this->outputDir);
            mkdir($this->outputDir);
            $this->process('7z', ['x', '-y', $this->path, '-otemp']);
            $output = $this->process('7z', ['l', '-ba', '-slt', $this->outputDir]);
            $this->recurseRmdir($this->outputDir);
        } else {
            $output = $this->process('7z', ['l', '-ba', '-slt', $this->path]);
        }

        $output = explode(PHP_EOL, $output);
        array_unshift($output, '');

        $temp = [];
        foreach ($output as $string) {
            if (empty($string)) {
                $id = uniqid();
                $temp[$id] = [];
            }

            if (! empty($string) && isset($id)) {
                $temp[$id][] = $string;
            }
        }

        $files = [];
        foreach ($temp as $sublist) {
            if (! empty($sublist)) {
                $files[] = $sublist;
            }
        }

        /** @var ArchiveItem[] $items */
        $items = [];

        foreach ($files as $file) {
            $item = [];
            foreach ($file as $attr) {
                $data = explode(' = ', $attr);
                $key = array_key_exists(0, $data) ? $data[0] : null;
                $value = array_key_exists(1, $data) ? $data[1] : null;

                $key = trim($key);
                $value = trim($value);

                $item[$key] = $value;
            }

            $item = ArchiveItem::make($item, $this->path);
            $items[] = $item;
        }

        $items = array_filter($items, fn (ArchiveItem $item) => ! $item->isDirectory());
        $items = array_filter($items, fn (ArchiveItem $item) => ! $item->isHidden());

        return $items;
    }

    private function findFiles(string $search): array
    {
        $files = $this->files();

        return array_filter($files, function (ArchiveItem $file) use ($search) {
            $isExtension = ! str_contains($search, '.');
            if ($isExtension) {
                return $file->extension() === $search;
            } else {
                return str_contains($file->path(), $search);
            }
        });
    }

    private function content(string $extract, bool $toBase64 = false): string
    {
        $command = '7z';

        if (! is_dir($this->outputDir)) {
            mkdir($this->outputDir);
        }

        // rm -rf temp ; 7z x -y -otemp tests/media/archive.tar archive/cover.jpeg
        $args = ['x', '-y', "-o{$this->outputDir}", $this->path];
        if ($this->type !== ArchiveEnum::tar && $this->type !== ArchiveEnum::tarExtended) {
            $args[] = $extract;
        }

        // rm -rf temp ; unrar x -y tests/media/archive.rar archive/cover.jpeg temp/
        // rm -rf temp ; rar x -y tests/media/archive.rar archive/cover.jpeg temp/
        if ($this->type === ArchiveEnum::rar && $this->isDarwin) {
            $command = 'rar';
            $args = ['x', '-y', $this->path, $extract, $this->outputDir];
        }
        $this->process($command, $args);

        if ($this->type === ArchiveEnum::tarExtended) {
            $files = glob("{$this->outputDir}/*.tar");
            if (! empty($files)) {
                $tar = array_shift($files);
                $this->process('7z', ['x', '-y', "-o{$this->outputDir}", $tar]);
            }
        }

        $outputPath = "{$this->outputDir}/{$extract}";
        $content = null;

        if (file_exists($outputPath)) {
            $content = file_get_contents($outputPath);
        }

        if (! $content) {
            throw new \Exception('File not found');
        }

        $this->recurseRmdir($this->outputDir);

        if ($toBase64) {
            return base64_encode($content);
        }

        return $content;
    }

    /**
     * @param  string[]  $args
     */
    private function process(string $command, array $args): string
    {
        $process = new Process([$command, ...$args]);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    private function recurseRmdir(string $dir): bool
    {
        if (! file_exists($dir)) {
            return true;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            (is_dir("$dir/$file") && ! is_link("$dir/$file")) ? $this->recurseRmdir("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }

    private static function p7zipBinaryExists(): bool
    {
        $process = new Process(['7z']);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new Exception("7zip is not installed or not in the PATH. Please install 7zip and try again.\nYou can check this guide: https://gist.github.com/ewilan-riviere/85d657f9283fa6af255531d97da5d71d");
        }

        return true;
    }
}