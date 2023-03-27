<?php

namespace Kiwilan\Archive;

use Exception;
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
        $self->outputDir = sys_get_temp_dir();
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
        // tar extended:
        // 7z x tests/media/archive.tar.gz -so | 7z x -aoa -si -ttar -otemp
        // 7z x tests/media/archive.tar.gz -so | 7z l -ba -si -ttar -otemp
        // rm -rf temp ; mkdir temp ; 7z x tests/media/archive.tar.gz -otemp ; 7z l -ba -slt temp ; rm -rf temp
        // tar -tf tests/media/archive.tar.gz
        // if ($this->type === ArchiveEnum::tarExtended) {
        //     $this->recurseRmdir($this->outputDir);
        //     mkdir($this->outputDir);
        //     $this->process('7z', ['x', '-y', $this->path, '-otemp']);
        //     $output = $this->process('7z', ['l', '-ba', '-slt', $this->outputDir]);
        //     $this->recurseRmdir($this->outputDir);
        // }
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
        // if ($this->type === ArchiveEnum::rar && $this->isDarwin) {
        //     // rar x -y tests/media/archive.rar temp/
        //     $output = $this->process('rar', ['x', '-y', $this->path, $path]);
        // } else {
        //     // 7z x -y tests/media/archive.tar.gz -otemp
        //     if ($this->type === ArchiveEnum::tarExtended) {
        //         $this->process('7z', ['x', '-y', $this->path, '-otemp']);
        //         $this->process('7z', ['x', '-y', $this->outputDir, "-o{$path}"]);
        //     }
        // }

        // Extract specific file
        // 7z e tests/media/archive.7z -otests/output file-1.md -r
        // Extract all files
        // rm -rf tests/output ; 7z x tests/media/archive.7z -otests/output -r

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
        // rm -rf temp ;  x -y -otemp tests/media/archive.tar archive/cover.jpeg
        // if ($this->type !== ArchiveEnum::tar && $this->type !== ArchiveEnum::tarExtended) {
        //     $args[] = $extract;
        // }

        // rm -rf temp ; unrar x -y tests/media/archive.rar archive/cover.jpeg temp/
        // rm -rf temp ; rar x -y tests/media/archive.rar archive/cover.jpeg temp/
        // if ($this->type === ArchiveEnum::rar && $this->isDarwin) {
        //     $command = 'rar';
        //     $args = ['x', '-y', $this->path, $extract, $this->outputDir];
        // }
        // $this->execute('7z', ['x', '-y', "-o{$this->outputDir}", $this->path]);

        $archive = pathinfo($this->path, PATHINFO_BASENAME);
        $output = "{$this->outputDir}/{$archive}";
        $filePath = "{$output}/{$file->path()}";

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
