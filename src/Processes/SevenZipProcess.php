<?php

namespace Kiwilan\Archive\Processes;

use Exception;
use Kiwilan\Archive\ArchiveTemporaryDirectory;
use Kiwilan\Archive\Models\ArchiveItem;
use Kiwilan\Archive\Readers\BaseArchive;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class SevenZipProcess
{
    protected ?string $outputDir = null;

    protected bool $isRar = false;

    protected bool $isDarwin = false;

    protected function __construct(
        protected string $path,
    ) {
    }

    public static function make(string $path): self
    {
        if (! file_exists($path)) {
            throw new Exception("File does not exist: {$path}");
        }

        $self = new self($path);
        $temp = ArchiveTemporaryDirectory::make();
        $self->outputDir = $temp->path();
        $self->isDarwin = PHP_OS_FAMILY === 'Darwin';

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $rarExts = ['rar', 'cbr'];
        $self->isRar = in_array($ext, $rarExts);

        return $self;
    }

    /**
     * @param  string[]  $args
     */
    public function execute(string $command, array $args): string
    {
        BaseArchive::binaryP7zipTest();

        $process = new Process([$command, ...$args]);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    /**
     * @return ArchiveItem[]
     */
    public function list(): array
    {
        $output = $this->execute('7z', ['l', '-ba', '-slt', $this->path]);

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

            $item = ArchiveItem::fromP7zip($item, $this->path);
            $items[] = $item;
        }

        $items = array_filter($items, fn (ArchiveItem $item) => ! $item->isDirectory());
        $items = array_filter($items, fn (ArchiveItem $item) => ! $item->isHidden());

        return $items;
    }

    /**
     * @param  ArchiveItem[]  $files
     */
    public function extract(string $toPath, ?array $files = null): bool
    {
        if ($this->isRar && $this->isDarwin) {
            if ($files) {
                $list = array_map(fn (ArchiveItem $item) => $item->path(), $files);
                $this->execute('rar', ['x', '-y', $this->path, ...$list, "{$toPath}/"]);
            } else {
                $this->execute('rar', ['x', '-y', $this->path, "{$toPath}"]);
            }
        } else {
            if ($files) {
                $list = array_map(fn (ArchiveItem $item) => $item->path(), $files);
                $this->execute('7z', ['x', '-y', $this->path, "-o{$toPath}", ...$list, '-r']);
            } else {
                $this->execute('7z', ['x', '-y', $this->path, "-o{$toPath}", '-r']);
            }
        }

        return true;
    }

    public function content(ArchiveItem $file)
    {
        $archive = pathinfo($this->path, PATHINFO_BASENAME);
        $output = "{$this->outputDir}/{$archive}";
        $filePath = "{$output}/{$file->rootPath()}";
        $filePath = BaseArchive::pathToOsPath($filePath);

        if (! file_exists($filePath)) {
            mkdir($output, 0755, true);
        }

        if ($this->isRar && $this->isDarwin) {
            $this->execute('rar', ['x', '-y', $this->path, $file->path(), "{$output}/"]);
        } else {
            $this->execute('7z', ['x', '-y', $this->path, "-o{$output}", $file->path(), '-r']);
        }
        $content = file_get_contents($filePath);
        BaseArchive::recurseRmdir($this->outputDir);

        return $content;
    }
}
